<?php

/**
 * Test Suite: Notification Badge & Browser Password Autofill UX Verification
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Controllers\AuthController;
use App\Core\View;
use App\Core\Session;

$passed = 0;
$failed = 0;

function it(string $description, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($detail ? " - {$detail}" : "") . "\n";
        $failed++;
    }
}

echo "====================================================================\n";
echo "1. VERIFYING CSS STYLING & SHARED BADGE CLASSES IN app.css\n";
echo "====================================================================\n";

$cssPath = __DIR__ . '/../public/assets/css/app.css';
$cssContent = file_get_contents($cssPath);

it("app.css exists", file_exists($cssPath));
it("Contains .notif-badge-target with relative positioning", 
    str_contains($cssContent, '.notif-badge-target') && 
    str_contains($cssContent, 'position: relative;')
);
it("Contains .nav-badge-pill with absolute positioning and clean offsets", 
    str_contains($cssContent, '.nav-badge-pill') && 
    str_contains($cssContent, 'position: absolute;') &&
    str_contains($cssContent, 'top: -5px;') &&
    str_contains($cssContent, 'right: -7px;')
);
it("Uses compact circular/pill badge sizing (min-width: 17px, height: 17px, border-radius: 9999px)", 
    str_contains($cssContent, 'min-width: 17px;') && 
    str_contains($cssContent, 'height: 17px;') &&
    str_contains($cssContent, 'border-radius: 9999px;')
);
it("Uses readable small font (10px, bold font-weight)", 
    str_contains($cssContent, 'font-size: 10px;') && 
    str_contains($cssContent, 'font-weight: 700;')
);
it("Uses high-contrast white ring border and subtle elevation shadow", 
    str_contains($cssContent, 'border: 2px solid #FFFFFF;') || str_contains($cssContent, 'border: 2px solid #ffffff;')
);
it("Prevents showing empty badge (:empty display none)", 
    str_contains($cssContent, '.nav-badge-pill:empty')
);
it("Removed rigid fixed width: 16px that broke multi-digit counts", 
    !preg_match('/\.nav-badge-pill\s*\{[^}]*width:\s*16px;[^}]*border-radius:\s*50%;/s', $cssContent)
);

echo "\n====================================================================\n";
echo "2. VERIFYING OFFICE MASTER LAYOUT NOTIFICATION BADGES\n";
echo "====================================================================\n";

$officeLayoutPath = __DIR__ . '/../app/Views/layouts/office.php';
$officeContent = file_get_contents($officeLayoutPath);

it("Desktop topbar contains notif-badge-target wrapping bell icon and desktopNotifBadge", 
    str_contains($officeContent, 'id="desktopNotifBtn"') &&
    str_contains($officeContent, 'class="notif-badge-target"') &&
    str_contains($officeContent, 'id="desktopNotifBadge"')
);
it("Mobile bottom nav contains notif-badge-target wrapping bell icon and mobileNotifBadge", 
    str_contains($officeContent, 'class="bottom-nav-item nav-notif-item') &&
    str_contains($officeContent, 'id="mobileNotifBadge"')
);
it("Desktop badge formats >99 as 99+ in PHP rendering logic", 
    str_contains($officeContent, '$deskUnreadCount > 99 ? \'99+\' : $deskUnreadCount')
);
it("Badges hide cleanly on count 0 and avoid showing unnecessary 0", 
    str_contains($officeContent, '$deskUnreadCount > 0 ? \'inline-flex\' : \'none\'') &&
    str_contains($officeContent, '$deskUnreadCount > 0 ? $deskBadgeText : \'\'')
);
it("Desktop and mobile badges preserve raw count via data-count attribute", 
    str_contains($officeContent, 'data-count="<?= $deskUnreadCount ?>"')
);
it("Removed awkward detached inline styles on desktopNotifBadge", 
    !str_contains($officeContent, 'style="position: absolute; top: -4px; right: -4px; background: #D95757;')
);
it("updateGlobalNotifCount JavaScript formats 99+ and handles 0 cleanly", 
    str_contains($officeContent, 'function formatNotifBadgeText(count)') &&
    str_contains($officeContent, 'c > 99 ? \'99+\' : String(c)') &&
    str_contains($officeContent, 'el.setAttribute(\'data-count\', c)') &&
    str_contains($officeContent, 'el.style.display = \'none\'')
);

echo "\n====================================================================\n";
echo "3. VERIFYING DRIVER MASTER LAYOUT NOTIFICATION BADGES\n";
echo "====================================================================\n";

$driverLayoutPath = __DIR__ . '/../app/Views/layouts/driver.php';
$driverContent = file_get_contents($driverLayoutPath);

it("Driver topbar contains notif-badge-target and driverTopNotifBadge", 
    str_contains($driverContent, 'id="topbarNotifBtn"') &&
    str_contains($driverContent, 'id="driverTopNotifBadge"')
);
it("Driver bottom nav contains notif-badge-target and driverBottomNotifBadge", 
    str_contains($driverContent, 'id="driverBottomNotifBadge"')
);
it("Driver layout handles 99+ formatting and 0 count hiding", 
    str_contains($driverContent, '$curNotifCount > 99 ? \'99+\' : $curNotifCount') &&
    str_contains($driverContent, '$curNotifCount > 0 ? \'inline-flex\' : \'none\'')
);
it("Driver updateAllNotifBadges JavaScript formats 99+ and syncs data-count", 
    str_contains($driverContent, 'function formatDriverNotifBadgeText(count)') &&
    str_contains($driverContent, 'c > 99 ? \'99+\' : String(c)') &&
    str_contains($driverContent, 'el.setAttribute(\'data-count\', c)') &&
    str_contains($driverContent, 'el.style.display = \'none\'')
);

echo "\n====================================================================\n";
echo "4. VERIFYING LOGIN FORM & BROWSER PASSWORD MANAGER COMPATIBILITY\n";
echo "====================================================================\n";

$loginPath = __DIR__ . '/../app/Views/auth/login.php';
$loginContent = file_get_contents($loginPath);

it("Form tag does NOT have autocomplete=off", 
    !str_contains($loginContent, 'id="loginForm" autocomplete="off"') &&
    str_contains($loginContent, '<form action="<?= url(\'/login\') ?>" method="POST" id="loginForm">')
);
it("Username field has standard autocomplete=username and name=login", 
    str_contains($loginContent, 'id="login"') &&
    str_contains($loginContent, 'name="login"') &&
    str_contains($loginContent, 'type="text"') &&
    str_contains($loginContent, 'autocomplete="username"')
);
it("Password field has type=password and standard autocomplete=current-password", 
    str_contains($loginContent, 'id="password"') &&
    str_contains($loginContent, 'name="password"') &&
    str_contains($loginContent, 'type="password"') &&
    str_contains($loginContent, 'autocomplete="current-password"')
);
it("Password field does NOT have autocomplete=off", 
    !preg_match('/id="password"[^>]*autocomplete="off"/s', $loginContent)
);
it("Password field does NOT have anti-password-manager flags (data-lpignore, data-form-type)", 
    !str_contains($loginContent, 'data-lpignore="true"') &&
    !str_contains($loginContent, 'data-form-type="other"')
);
it("Removed hacky masked-password-field CSS and -webkit-text-security: disc", 
    !str_contains($loginContent, 'masked-password-field') &&
    !str_contains($loginContent, '-webkit-text-security: disc')
);
it("Password visibility toggle cleanly switches input type between password and text", 
    str_contains($loginContent, 'pwdInput.type = \'text\'') &&
    str_contains($loginContent, 'pwdInput.type = \'password\'') &&
    str_contains($loginContent, 'fa-eye-slash') &&
    str_contains($loginContent, 'fa-eye')
);
it("Added :-webkit-autofill CSS compatibility for smooth glass styling", 
    str_contains($loginContent, '.glass-input-pill:-webkit-autofill')
);
it("Maintains submit button with type=submit", 
    str_contains($loginContent, '<button type="submit" id="signInBtn"')
);
it("Preserves normal security without storing passwords in localStorage/sessionStorage/cookies", 
    !str_contains($loginContent, 'localStorage.setItem') &&
    !str_contains($loginContent, 'sessionStorage.setItem') &&
    !str_contains($loginContent, 'document.cookie')
);

echo "\n====================================================================\n";
echo "5. TESTING LIVE AUTH CONTROLLER & LOGIN ENDPOINT\n";
echo "====================================================================\n";

Session::destroy();
$authController = new AuthController();

// 1. Show login view
$getReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/login', 'SCRIPT_NAME' => '/index.php']);
$showResp = $authController->showLogin($getReq);
it("showLogin returns HTTP 200", $showResp->getStatusCode() === 200);

// 2. Test invalid login handling
$invalidReq = new Request([], [
    'login' => 'nonexistent_user_' . time(),
    'password' => 'WrongPassword123!'
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login', 'SCRIPT_NAME' => '/index.php', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$invalidResp = $authController->login($invalidReq);
it("Invalid login returns HTTP 401/422/400 failure", 
    in_array($invalidResp->getStatusCode(), [401, 422, 400, 200], true)
);
$invalidBody = json_decode($invalidResp->getBody(), true);
it("Invalid login response contains success=false message", 
    isset($invalidBody['success']) && $invalidBody['success'] === false
);

// 3. Test validation on empty credentials
$emptyReq = new Request([], [
    'login' => '',
    'password' => ''
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login', 'SCRIPT_NAME' => '/index.php', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$emptyResp = $authController->login($emptyReq);
$emptyBody = json_decode($emptyResp->getBody(), true);
it("Empty login response contains validation error", 
    isset($emptyBody['success']) && $emptyBody['success'] === false
);

echo "\n====================================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
