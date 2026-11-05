<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Models\User;
use App\Services\AuthService;
use App\Controllers\AuthController;

echo "======================================================\n";
echo " RUNNING SUITE: Secure Remember Me & Session Security\n";
echo "======================================================\n";

$pdo = Database::getConnection();
$userModel = new User();
$authService = new AuthService();

// Ensure test user exists
$testUser = $userModel->findByUsername('admin');
if (!$testUser) {
    echo "  [FAIL] Admin user not found\n";
    exit(1);
}

// Test 1: Login without Remember Me does not issue remember cookie
$_COOKIE = [];
Session::destroy();
$loginNoRemember = $authService->login('admin', 'Admin@12345', false);
assert(Session::isLoggedIn(), "Session is logged in");
assert(!isset($_COOKIE['tours_remember']), "Remember cookie is not set when remember=false");
echo "  [PASS] Login without remember me does not issue persistent cookie\n";

// Test 2: Login with Remember Me issues valid split token
Session::destroy();
$_COOKIE = [];
$loginRemember = $authService->login('admin', 'Admin@12345', true);
assert(Session::isLoggedIn(), "Session is logged in");
assert(!empty($_COOKIE['tours_remember']), "Remember cookie is set when remember=true");
$rawCookie = $_COOKIE['tours_remember'];
[$selector, $validator] = explode(':', $rawCookie, 2);
assert(strlen($selector) === 32, "Selector is 32 hex chars");
assert(strlen($validator) === 64, "Validator is 64 hex chars");

$stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE selector = ?");
$stmt->execute([$selector]);
$dbToken = $stmt->fetch(PDO::FETCH_ASSOC);
assert(!empty($dbToken), "Token found in database");
assert(hash_equals($dbToken['validator_hash'], hash('sha256', $validator)), "Validator hash matches SHA-256");
echo "  [PASS] Login with remember me creates valid split-token in DB & secure cookie\n";

// Test 3: Session destroyed, auto-login via Remember Me cookie succeeds
Session::destroy();
assert(!Session::isLoggedIn(), "Session is cleared");
$_COOKIE['tours_remember'] = $rawCookie;

$autoUser = $authService->attemptRememberMeLogin();
assert($autoUser !== null, "Remember me auto-login returned user");
assert((int)$autoUser['id'] === (int)$testUser['id'], "User ID matches");
assert(Session::isLoggedIn(), "Session is restored");
echo "  [PASS] Closed browser / destroyed session auto-authenticates via remember cookie\n";

// Test 4: Token rotation occurs on remember login
$newCookie = $_COOKIE['tours_remember'];
assert($newCookie !== $rawCookie, "Cookie value changed due to validator rotation");
[$newSelector, $newValidator] = explode(':', $newCookie, 2);
assert($newSelector === $selector, "Selector remains consistent for session device");
assert($newValidator !== $validator, "Validator was rotated to prevent replay attacks");

$stmt->execute([$selector]);
$updatedDbToken = $stmt->fetch(PDO::FETCH_ASSOC);
assert(hash_equals($updatedDbToken['validator_hash'], hash('sha256', $newValidator)), "DB validator hash updated to new validator");
echo "  [PASS] Remember token validator rotates on authentication (Replay Attack Defense)\n";

// Test 5: Old validator within grace window (<=60s) is accepted for concurrent requests
Session::destroy();
$_COOKIE['tours_remember'] = $rawCookie; // Old cookie during grace period
$concurrentResult = $authService->attemptRememberMeLogin();
assert($concurrentResult !== null, "Concurrent in-flight request within grace window is safely accepted");
echo "  [PASS] Concurrency grace window allows in-flight parallel request without session drop\n";

// Test 5b: Old validator replayed after grace window (>60s) is thwarted and rejected
$pdo->prepare("UPDATE `remember_tokens` SET `rotated_at` = DATE_SUB(NOW(), INTERVAL 90 SECOND) WHERE `selector` = ?")
    ->execute([$selector]);
Session::destroy();
$_COOKIE['tours_remember'] = $rawCookie;
$replayResult = $authService->attemptRememberMeLogin();
assert($replayResult === null, "Replay attack outside grace window was thwarted and rejected");
echo "  [PASS] Stolen/old rotated token outside grace window is rejected and revokes token safely\n";

// Test 6: Explicit logout revokes token and clears cookie
Session::destroy();
$_COOKIE = [];
$logoutLogin = $authService->login('admin', 'Admin@12345', true);
assert(Session::isLoggedIn(), "Logged in before logout test");
assert(!empty($_COOKIE['tours_remember']), "Remember cookie issued for logout test");
$activeCookie = $_COOKIE['tours_remember'];
[$activeSelector] = explode(':', $activeCookie, 2);

$authService->logout();
assert(!Session::isLoggedIn(), "Session destroyed on logout");
assert(!isset($_COOKIE['tours_remember']), "Cookie cleared on logout");

$stmt->execute([$activeSelector]);
$revokedToken = $stmt->fetch(PDO::FETCH_ASSOC);
assert(empty($revokedToken), "Token record deleted from database on logout");
echo "  [PASS] Explicit logout revokes token from DB and removes cookie\n";

echo "\n============================================\n";
echo "TEST RESULTS: All Remember Me & Session assertions passed!\n";
echo "============================================\n";
