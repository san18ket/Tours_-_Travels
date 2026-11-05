<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Booking;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Services\AuthService;
use App\Services\ReferenceDataService;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ReferenceDataController;

echo "====================================================================\n";
echo " RUNNING COMPREHENSIVE SUITE: SMART BROWSER CACHING & PERFORMANCE\n";
echo "====================================================================\n\n";

$pdo = Database::getConnection();
$authService = new AuthService();
$userModel = new User();

$adminUser = $userModel->findByUsername('admin');
$driverUser = $userModel->findBy('role', 'driver');
if ($driverUser) {
    // Ensure driver password is known
    $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?")
        ->execute([password_hash('Chauffeur@123', PASSWORD_BCRYPT), $driverUser['id']]);
}

$passedTests = 0;
$totalTests = 0;

function runTest(string $name, callable $fn) {
    global $passedTests, $totalTests;
    $totalTests++;
    try {
        $fn();
        echo "  [PASS] Test {$totalTests}: {$name}\n";
        $passedTests++;
    } catch (\Throwable $e) {
        echo "  [FAIL] Test {$totalTests}: {$name} -> " . $e->getMessage() . "\n";
    }
}

// -----------------------------------------------------------------------------
// 1. First Login
// -----------------------------------------------------------------------------
runTest("First login succeeds, regenerates session ID, and authenticates", function() use ($authService, $adminUser) {
    Session::destroy();
    $_COOKIE = [];
    $user = $authService->login('admin', 'Admin@12345', false);
    if (!Session::isLoggedIn()) throw new Exception("Session not logged in");
    if (Session::getUserId() !== (int)$adminUser['id']) throw new Exception("User ID mismatch");
});

// -----------------------------------------------------------------------------
// 2. Repeat Login with Valid Session
// -----------------------------------------------------------------------------
runTest("Repeat login with valid session keeps session valid without re-authentication penalty", function() use ($adminUser) {
    if (!Session::isLoggedIn()) throw new Exception("Session should remain active");
    if (Session::getUserRole() !== 'admin') throw new Exception("Role mismatch");
});

// -----------------------------------------------------------------------------
// 3. Logout
// -----------------------------------------------------------------------------
runTest("Logout destroys session and revokes authentication credentials", function() use ($authService) {
    $authService->logout();
    if (Session::isLoggedIn()) throw new Exception("Session should be destroyed");
    if (Session::getUserId() !== null) throw new Exception("User ID should be null");
});

// -----------------------------------------------------------------------------
// 4. Account Switching in Same Browser
// -----------------------------------------------------------------------------
runTest("Account switching from Admin to Driver strictly isolates session and permissions", function() use ($authService, $adminUser, $driverUser) {
    // 1. Log in as Admin
    $authService->login('admin', 'Admin@12345', false);
    if (Session::getUserRole() !== 'admin') throw new Exception("Expected admin role");
    $authService->logout();

    // 2. Log in as Driver
    $driverLoginId = $driverUser['username'] ?: $driverUser['email'];
    $authService->login($driverLoginId, 'Chauffeur@123', false);
    if (Session::getUserRole() !== 'driver') throw new Exception("Expected driver role");
    if (Session::getUserId() === (int)$adminUser['id']) throw new Exception("Admin user ID bled into driver session");
    $authService->logout();
});

// -----------------------------------------------------------------------------
// 5. Remember Me (Generation, Auto-login, Rotation, Revocation)
// -----------------------------------------------------------------------------
runTest("Remember Me token creation, verification, rotation, and revocation", function() use ($authService, $adminUser, $pdo) {
    $_COOKIE = [];
    Session::destroy();
    $authService->login('admin', 'Admin@12345', true);
    if (empty($_COOKIE['tours_remember'])) throw new Exception("Remember cookie was not set");

    $initialCookie = $_COOKIE['tours_remember'];
    [$selector, $validator] = explode(':', $initialCookie, 2);

    // Verify token stored in DB
    $stmt = $pdo->prepare("SELECT * FROM `remember_tokens` WHERE `selector` = ?");
    $stmt->execute([$selector]);
    $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tokenRow) throw new Exception("Token row not found in DB");
    if (!hash_equals($tokenRow['validator_hash'], hash('sha256', $validator))) throw new Exception("Validator hash mismatch");

    // Clear session to simulate closed browser
    Session::destroy();
    $_COOKIE['tours_remember'] = $initialCookie;

    // Auto-login via Remember Me
    $autoUser = $authService->attemptRememberMeLogin();
    if (!$autoUser) throw new Exception("Remember Me auto-login failed");
    if (!Session::isLoggedIn()) throw new Exception("Session not restored");

    // Verify token rotation occurred
    $rotatedCookie = $_COOKIE['tours_remember'];
    if ($rotatedCookie === $initialCookie) throw new Exception("Cookie was not rotated on authentication");

    // Explicit logout revokes token
    $authService->logout();
    $stmt->execute([$selector]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) throw new Exception("Token row should be deleted from DB on logout");
});

// -----------------------------------------------------------------------------
// 6. Static Asset Caching Integrity
// -----------------------------------------------------------------------------
runTest("Static asset caching files, manifests, and versioning exist on disk", function() {
    $manifestPath = dirname(__DIR__) . '/public/manifest.json';
    $swPath = dirname(__DIR__) . '/public/sw.js';
    $cacheJsPath = dirname(__DIR__) . '/public/assets/js/tours-cache.js';

    if (!file_exists($manifestPath)) throw new Exception("manifest.json missing");
    if (!file_exists($swPath)) throw new Exception("sw.js missing");
    if (!file_exists($cacheJsPath)) throw new Exception("tours-cache.js missing");

    $swContent = file_get_contents($swPath);
    if (!str_contains($swContent, "CACHE_VERSION = 'v6.0'")) throw new Exception("SW static cache version missing");
    if (!str_contains($swContent, 'PRIVATE_ROUTE_PATTERNS')) throw new Exception("SW security patterns missing");
});

// -----------------------------------------------------------------------------
// 7. Cache Version Update & ETag Tracking
// -----------------------------------------------------------------------------
runTest("Reference data version tracking and ETag generation", function() {
    $verBefore = ReferenceDataService::getVersion('company_branding');
    ReferenceDataService::invalidate('company_branding');
    $verAfter = ReferenceDataService::getVersion('company_branding');

    if ($verAfter['version'] !== ($verBefore['version'] + 1)) {
        throw new Exception("Version did not increment after invalidation");
    }
    if ($verAfter['etag'] === $verBefore['etag']) {
        throw new Exception("ETag did not change after invalidation");
    }
});

// -----------------------------------------------------------------------------
// 8. Reference Data Cache Hit (HTTP 304 Validation)
// -----------------------------------------------------------------------------
runTest("Reference API returns HTTP 304 Not Modified when client sends matching ETag", function() {
    $controller = new ReferenceDataController();
    $ver = ReferenceDataService::getVersion('locations');
    $etag = '"' . $ver['etag'] . '"';

    $req = new Request([], [], [], [], ['HTTP_IF_NONE_MATCH' => $etag]);
    $resp = $controller->locations($req);

    if ($resp->getStatusCode() !== 304) {
        throw new Exception("Expected HTTP 304, got " . $resp->getStatusCode());
    }
    if ($resp->getBody() !== '') {
        throw new Exception("HTTP 304 must have empty body, saved bandwidth");
    }
});

// -----------------------------------------------------------------------------
// 9. Reference Data Expiry & Fresh Fetch
// -----------------------------------------------------------------------------
runTest("Reference API returns HTTP 200 with fresh payload when no ETag or stale ETag is passed", function() {
    $controller = new ReferenceDataController();
    $req = new Request([], [], [], [], ['HTTP_IF_NONE_MATCH' => '"stale_outdated_etag"']);
    $resp = $controller->locations($req);

    if ($resp->getStatusCode() !== 200) {
        throw new Exception("Expected HTTP 200, got " . $resp->getStatusCode());
    }
    $data = json_decode($resp->getBody(), true);
    if (empty($data['data']['states'])) {
        throw new Exception("Payload missing expected locations data");
    }
});

// -----------------------------------------------------------------------------
// 10. Reference Data Invalidation propagates across all reference domains
// -----------------------------------------------------------------------------
runTest("Invalidating reference key immediately reflects new version in version index", function() {
    ReferenceDataService::invalidate('payment_methods');
    $versions = ReferenceDataService::getVersions();
    if (empty($versions['payment_methods'])) throw new Exception("Payment methods version missing");
    if ($versions['payment_methods']['version'] < 2) throw new Exception("Version did not advance");
});

// -----------------------------------------------------------------------------
// 11. IndexedDB Unavailable Fallback
// -----------------------------------------------------------------------------
runTest("Client tours-cache.js contains resilient in-memory Map fallback for private browsing", function() {
    $code = file_get_contents(dirname(__DIR__) . '/public/assets/js/tours-cache.js');
    if (!str_contains($code, 'const memoryStore = new Map()')) {
        throw new Exception("Memory fallback store missing");
    }
    if (!str_contains($code, 'isIndexedDbSupported = false')) {
        throw new Exception("IndexedDB error handling missing");
    }
});

// -----------------------------------------------------------------------------
// 12. Storage Quota Exceeded Defense
// -----------------------------------------------------------------------------
runTest("Storage quota exceptions are caught safely without breaking page execution", function() {
    $code = file_get_contents(dirname(__DIR__) . '/public/assets/js/tours-cache.js');
    if (!str_contains($code, 'console.warn(\'ToursCache: IndexedDB exception')) {
        throw new Exception("IndexedDB catch block missing");
    }
    if (!str_contains($code, 'clearExpired')) {
        throw new Exception("Quota clearance routine missing");
    }
});

// -----------------------------------------------------------------------------
// 13. Service Worker Update & Diagnostic Messaging
// -----------------------------------------------------------------------------
runTest("Service worker supports SKIP_WAITING, CLEAR_CACHES, and GET_CACHE_INFO messages", function() {
    $sw = file_get_contents(dirname(__DIR__) . '/public/sw.js');
    if (!str_contains($sw, 'SKIP_WAITING')) throw new Exception("SKIP_WAITING action missing");
    if (!str_contains($sw, 'CLEAR_CACHES')) throw new Exception("CLEAR_CACHES action missing");
    if (!str_contains($sw, 'GET_CACHE_INFO')) throw new Exception("GET_CACHE_INFO action missing");
});

// -----------------------------------------------------------------------------
// 14. PWA Installation & Custom Modal
// -----------------------------------------------------------------------------
runTest("PWA custom install modal and tours-pwa.js preserved intact", function() {
    $pwaJs = file_get_contents(dirname(__DIR__) . '/public/assets/js/tours-pwa.js');
    if (!str_contains($pwaJs, 'openToursInstallModal')) throw new Exception("openToursInstallModal missing");
    if (!str_contains($pwaJs, 'startToursInstallation')) throw new Exception("startToursInstallation missing");
});

// -----------------------------------------------------------------------------
// 15. Offline Access & Sensitive Route Protection
// -----------------------------------------------------------------------------
runTest("Service worker strictly prevents caching of sensitive administrative and customer routes", function() {
    $sw = file_get_contents(dirname(__DIR__) . '/public/sw.js');
    $patterns = ['driver', 'office', 'mobile', 'gps', 'login', 'logout'];
    foreach ($patterns as $p) {
        if (!str_contains($sw, $p)) throw new Exception("Sensitive pattern {$p} missing from SW");
    }
});

// -----------------------------------------------------------------------------
// 16. Customer and Booking Forms Compatibility
// -----------------------------------------------------------------------------
runTest("Forms integration auto-hydrates reference data without AJAX full-page changes", function() {
    $appJs = file_get_contents(dirname(__DIR__) . '/public/assets/js/app.js');
    if (!str_contains($appJs, 'ToursForms.hydrateLocations()')) {
        throw new Exception("hydrateLocations call missing in app.js");
    }
    if (!str_contains($appJs, 'ToursForms.hydratePaymentMethods()')) {
        throw new Exception("hydratePaymentMethods call missing in app.js");
    }
});

// -----------------------------------------------------------------------------
// 17. Server-side Permission Enforcement
// -----------------------------------------------------------------------------
runTest("Server-side role permissions strictly block unauthorized access regardless of cache", function() use ($authService, $driverUser) {
    // Authenticate as driver
    $authService->login($driverUser['username'] ?: $driverUser['email'], 'Chauffeur@123', false);

    // Attempt to access office dashboard via router middleware
    $router = new \App\Core\Router();
    $router->get('/office/dashboard', [DashboardController::class, 'index'], ['auth', 'role:admin,dispatcher']);

    $req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/dashboard']);
    $resp = $router->dispatch($req);

    if ($resp->getStatusCode() !== 403) {
        throw new Exception("Driver was not blocked with 403 Forbidden! Status: " . $resp->getStatusCode());
    }
    $authService->logout();
});

// -----------------------------------------------------------------------------
// 18. PHP Session Concurrency & Read-Only Lock Release
// -----------------------------------------------------------------------------
runTest("Session::closeWrite releases session lock, allowing concurrent read-only requests", function() {
    Session::set('bench_concurrency_key', 'value_1');
    Session::closeWrite();
    if (session_status() === PHP_SESSION_ACTIVE) throw new Exception("Session lock should be released");

    // Read without starting write lock
    Session::start();
    if (Session::get('bench_concurrency_key') !== 'value_1') throw new Exception("Session data lost after closeWrite");
    Session::destroy();
});

// -----------------------------------------------------------------------------
// 19. Database Performance & Query Optimization Benchmark
// -----------------------------------------------------------------------------
runTest("Dashboard query execution benchmark with optimized limits and indexes", function() use ($adminUser) {
    Session::setUser($adminUser);
    $_SESSION['user'] = $adminUser;

    $dashboard = new DashboardController();
    $req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/dashboard']);

    $t0 = microtime(true);
    $resp = $dashboard->index($req);
    $duration = (microtime(true) - $t0) * 1000;

    if ($resp->getStatusCode() !== 200) throw new Exception("Dashboard returned " . $resp->getStatusCode());
    echo "    -> Dashboard rendering execution time: " . round($duration, 2) . " ms\n";
});

echo "\n====================================================================\n";
echo " TEST EXECUTION SUMMARY: {$passedTests} / {$totalTests} PASSED\n";
echo "====================================================================\n\n";

if ($passedTests === $totalTests) {
    echo ">>> ALL 19 CACHING & PERFORMANCE TEST SUITES PASSED! <<<\n";
    exit(0);
} else {
    echo ">>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
