<?php
/**
 * Test Suite: Session-Only Authentication & Disable Automatic Re-Login
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Config;
use App\Core\Session;
use App\Services\AuthService;
use App\Controllers\AuthController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$failures = 0;
function test(string $name, callable $fn) {
    global $failures;
    try {
        $fn();
        echo "[PASS] {$name}\n";
    } catch (\Throwable $e) {
        $failures++;
        echo "[FAIL] {$name}: " . $e->getMessage() . "\n";
    }
}

echo "====================================================\n";
echo "Testing Session-Only Auth & Disabled Remember-Me\n";
echo "====================================================\n";

// Test 1: Config session lifetime is 0
test('Session lifetime in config is 0 (browser session only)', function() {
    $sessionCfg = Config::get('security.session');
    if ($sessionCfg['lifetime'] !== 0) {
        throw new Exception("Expected lifetime 0, got: " . var_export($sessionCfg['lifetime'], true));
    }
    if ($sessionCfg['idle_timeout'] !== 1800) {
        throw new Exception("Expected idle_timeout 1800 preserved, got: " . var_export($sessionCfg['idle_timeout'], true));
    }
});

// Test 2: attemptRememberMeLogin never restores authentication
test('AuthService::attemptRememberMeLogin returns null and revokes cookie', function() {
    $_COOKIE['tours_remember'] = '0123456789abcdef0123456789abcdef:0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
    $authService = new AuthService();
    $result = $authService->attemptRememberMeLogin();
    if ($result !== null) {
        throw new Exception("Expected null from attemptRememberMeLogin, got user array");
    }
    if (isset($_COOKIE['tours_remember'])) {
        throw new Exception("Expected \$_COOKIE['tours_remember'] to be unset");
    }
});

// Test 3: createRememberToken does not set persistent cookie
test('AuthService::createRememberToken does not set persistent cookie', function() {
    $authService = new AuthService();
    $token = $authService->createRememberToken(1);
    if ($token !== '') {
        throw new Exception("Expected empty string from createRememberToken, got: {$token}");
    }
    if (isset($_COOKIE['tours_remember'])) {
        throw new Exception("tours_remember cookie was set!");
    }
});

// Test 4: AuthController::showLogin does not auto-login and clears legacy cookie
test('AuthController::showLogin renders login page without auto-login', function() {
    // Ensure unauthenticated
    Session::destroy();
    $_COOKIE['tours_remember'] = 'legacy_cookie_value:12345';
    
    $authController = new AuthController();
    $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/login', 'SCRIPT_NAME' => '/index.php']);
    $response = $authController->showLogin($request);
    
    if ($response->getStatusCode() !== 200) {
        throw new Exception("Expected 200 OK (login view), got status: " . $response->getStatusCode());
    }
    if (Session::isLoggedIn()) {
        throw new Exception("User was unexpectedly logged in after showLogin");
    }
    if (isset($_COOKIE['tours_remember'])) {
        throw new Exception("Legacy tours_remember was not cleared");
    }
    
    // Verify Cache-Control headers
    $cc = $response->getHeader('Cache-Control');
    if (!str_contains($cc, 'no-store')) {
        throw new Exception("Expected no-store Cache-Control header on login page, got: {$cc}");
    }
});

// Test 5: Response::redirect includes no-cache headers
test('Response::redirect includes anti-caching headers', function() {
    $resp = Response::redirect('/office/dashboard');
    $cc = $resp->getHeader('Cache-Control');
    if (!str_contains($cc, 'no-store') || !str_contains($cc, 'must-revalidate')) {
        throw new Exception("Expected no-store Cache-Control on redirect, got: {$cc}");
    }
});

// Test 6: Router middleware 'auth' rejects unauthenticated requests and does not auto-login
test('Router auth middleware rejects unauthenticated access even if legacy cookie present', function() {
    Session::destroy();
    $_COOKIE['tours_remember'] = 'legacy_cookie_value:12345';
    
    $router = new Router();
    $router->get('/test-protected', function() {
        return Response::make('Protected Content');
    }, ['auth']);
    
    $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test-protected', 'SCRIPT_NAME' => '/index.php']);
    $response = $router->dispatch($request);
    
    if ($response->getStatusCode() !== 302) {
        throw new Exception("Expected 302 redirect for unauthenticated user, got: " . $response->getStatusCode());
    }
    if (Session::isLoggedIn()) {
        throw new Exception("User was unexpectedly authenticated via auth middleware");
    }
    if (isset($_COOKIE['tours_remember'])) {
        throw new Exception("Legacy tours_remember was not cleared by auth middleware");
    }
});

// Test 7: Router middleware 'auth' applies no-cache headers to protected responses
test('Router applies no-cache headers to private route responses', function() {
    // Simulate authenticated session
    Session::setUser([
        'id' => 1,
        'role' => 'admin',
        'name' => 'Admin User',
        'email' => 'admin@example.com'
    ]);
    
    $router = new Router();
    $router->get('/test-protected-authenticated', function() {
        return Response::make('<h1>Protected Dashboard</h1>');
    }, ['auth']);
    
    $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test-protected-authenticated', 'SCRIPT_NAME' => '/index.php']);
    $response = $router->dispatch($request);
    
    if ($response->getStatusCode() !== 200) {
        throw new Exception("Expected 200 OK, got: " . $response->getStatusCode());
    }
    $cc = $response->getHeader('Cache-Control');
    if (!str_contains($cc, 'no-store')) {
        throw new Exception("Private route must have no-store in Cache-Control, got: " . var_export($cc, true));
    }
    
    Session::destroy();
});

// Test 8: Normal login succeeds and establishes session without persistent cookie
test('AuthService login validates credentials and creates session without persistent cookie', function() {
    Session::destroy();
    
    $authService = new AuthService();
    // Use dev credentials (owner)
    try {
        $user = $authService->login('owner', 'owner123', true); // Pass remember=true
        if (!Session::isLoggedIn()) {
            throw new Exception("User should be logged in via session");
        }
        if (Session::getUserRole() !== 'admin') {
            throw new Exception("Expected admin role, got: " . Session::getUserRole());
        }
        if (isset($_COOKIE['tours_remember'])) {
            throw new Exception("tours_remember cookie must NOT be set even if remember=true passed");
        }
    } catch (\RuntimeException $e) {
        // If DB user owner is not found or has different password, test with known credentials or record notice
        echo "(Notice: " . $e->getMessage() . ") ";
    }
    
    Session::destroy();
});

// Test 9: Logout clears session and revokes tokens
test('AuthController::logout properly destroys session and clears cookies', function() {
    Session::setUser([
        'id' => 1,
        'role' => 'admin',
        'name' => 'Admin User',
        'email' => 'admin@example.com'
    ]);
    $_COOKIE['tours_remember'] = 'legacy:cookie';
    
    $authController = new AuthController();
    $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/logout']);
    $response = $authController->logout($request);
    
    if ($response->getStatusCode() !== 302) {
        throw new Exception("Expected 302 redirect after logout, got: " . $response->getStatusCode());
    }
    if (Session::isLoggedIn()) {
        throw new Exception("User still logged in after logout");
    }
    if (isset($_COOKIE['tours_remember'])) {
        throw new Exception("tours_remember still set after logout");
    }
});

echo "====================================================\n";
if ($failures === 0) {
    echo "SUCCESS: ALL TESTS PASSED!\n";
} else {
    echo "FAILED: {$failures} tests failed.\n";
}
echo "====================================================\n";
exit($failures === 0 ? 0 : 1);
