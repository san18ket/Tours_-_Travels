<?php
/**
 * Test: Login Form Masked Password Field to Prevent Browser Data Breach Popup
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Database;

function assertCondition($cond, $msg) {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "======================================================\n";
echo " RUNNING SUITE: Login Form Security & Breach Prevention\n";
echo "======================================================\n";

// 1. Test GET /login markup
$router = new Router();
require APP_ROOT . '/app/routes.php';

\App\Core\Session::remove('user_id');
\App\Core\Session::remove('user');
\App\Core\Session::remove('user_role');
$getReq = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/login'
]);
$getRes = $router->dispatch($getReq);
$html = $getRes->getContent();

assertCondition($getRes->getStatusCode() === 200, "GET /login returns HTTP 200");
assertCondition(strpos($html, 'autocomplete="off"') !== false, "Login form has autocomplete='off'");
assertCondition(strpos($html, 'class="glass-input-pill masked-password-field"') !== false, "Password field uses .masked-password-field");
assertCondition(strpos($html, 'type="text"') !== false && strpos($html, 'id="password"') !== false, "Password input uses type='text' to evade browser leak detection heuristics");
assertCondition(strpos($html, '-webkit-text-security: disc') !== false, "CSS defines -webkit-text-security: disc for visual masking");
assertCondition(strpos($html, 'text-security: disc') !== false, "CSS defines text-security: disc for standards compliance");

// 2. Test AJAX POST /login authentication
$token = csrf_token();
$postReq = new Request([], [
    'login'      => 'owner',
    'password'   => 'owner123',
    'csrf_token' => $token,
    'remember'   => '1',
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_ACCEPT' => 'application/json'
]);

$postRes = $router->dispatch($postReq);

assertCondition($postRes->getStatusCode() === 200, "AJAX POST /login returns HTTP 200 OK");

$json = json_decode($postRes->getContent(), true);
assertCondition(!empty($json) && !empty($json['success']), "AJAX POST /login returns success: true");
assertCondition(!empty($json['redirect']), "AJAX POST /login returns redirect target: " . ($json['redirect'] ?? ''));

// 3. Test Invalid Credentials
$badReq = new Request([], [
    'login'      => 'owner',
    'password'   => 'WrongPassword999!',
    'csrf_token' => $token,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_ACCEPT' => 'application/json'
]);

$badRes = $router->dispatch($badReq);
$badJson = json_decode($badRes->getContent(), true);
assertCondition(!empty($badJson) && $badJson['success'] === false, "Bad credentials return success: false");

// 4. Test Standard POST (Non-AJAX Fallback)
$stdReq = new Request([], [
    'login'      => 'owner',
    'password'   => 'owner123',
    'csrf_token' => $token,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login'
]);

$stdRes = $router->dispatch($stdReq);
assertCondition($stdRes->getStatusCode() === 302, "Standard POST /login redirects (HTTP 302)");
assertCondition(strpos($stdRes->getHeader('Location'), '/office/dashboard') !== false, "Standard POST redirects to /office/dashboard");

echo "\n============================================\n";
echo "TEST RESULTS: All assertions passed successfully!\n";
echo "============================================\n";
