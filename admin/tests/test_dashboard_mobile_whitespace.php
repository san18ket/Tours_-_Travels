<?php
/**
 * Test Suite: Mobile Dashboard Bottom Whitespace Fix Verification
 * Tests the fix for unwanted empty whitespace between Recent Bookings and Footer on mobile devices.
 */

$testCount = 0;
$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $message): void {
    global $testCount, $passCount, $failCount;
    $testCount++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$message}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$message}\n";
    }
}

echo "====================================================================\n";
echo "1. VERIFYING DASHBOARD TEMPLATE MOBILE CSS (dashboard.php)\n";
echo "====================================================================\n";

$dashboardFile = __DIR__ . '/../app/Views/office/dashboard.php';
assertCondition(file_exists($dashboardFile), 'dashboard.php exists');

$dashboardContent = file_get_contents($dashboardFile);

// Check that 5rem padding-bottom was removed from .db-container
assertCondition(
    !preg_match('/\.db-container\s*\{[^}]*padding:[^;]*(?<![\d\.])5rem\b/i', $dashboardContent),
    'dashboard.php does NOT contain 5rem bottom padding on .db-container'
);

// Check that 0 padding-bottom is used for .db-container on mobile (< 992px)
assertCondition(
    (bool)preg_match('/@media\s*\(max-width:\s*991\.98px\)\s*\{[^}]*\.db-container\s*\{[^}]*padding:\s*0\.85rem\s+0\.85rem\s+0\s*!important/s', $dashboardContent),
    'dashboard.php sets .db-container padding to 0.85rem 0.85rem 0 !important at max-width: 991.98px'
);

// Check small mobile breakpoint (<= 380px)
assertCondition(
    (bool)preg_match('/@media\s*\(max-width:\s*380px\)\s*\{[^}]*\.db-container\s*\{[^}]*padding:\s*0\.65rem\s+0\.65rem\s+0\s*!important/s', $dashboardContent),
    'dashboard.php sets .db-container padding to 0.65rem 0.65rem 0 !important at max-width: 380px'
);

// Check that natural 12-16px (1rem) spacing is defined before footer
assertCondition(
    strpos($dashboardContent, 'body.is-dashboard-page .main-content') !== false,
    'dashboard.php includes scoped mobile rules for body.is-dashboard-page .main-content'
);
assertCondition(
    strpos($dashboardContent, '.main-content:has(.db-container)') !== false,
    'dashboard.php includes modern :has selector for .main-content:has(.db-container)'
);

// Check that footer bottom clearance is defined for bottom nav
assertCondition(
    strpos($dashboardContent, 'calc(64px + 16px + env(safe-area-inset-bottom, 0px))') !== false,
    'dashboard.php provides exact bottom-nav clearance padding on mobile footer'
);

// Verify NO negative margins are used in dashboard styles
assertCondition(
    !preg_match('/margin-(top|bottom)\s*:\s*-\s*[0-9]/i', $dashboardContent),
    'dashboard.php does NOT use negative margins'
);


echo "\n====================================================================\n";
echo "2. VERIFYING GLOBAL CSS RULES & RESTRICTIONS (app.css)\n";
echo "====================================================================\n";

$appCssFile = __DIR__ . '/../public/assets/css/app.css';
assertCondition(file_exists($appCssFile), 'app.css exists');

$appCssContent = file_get_contents($appCssFile);

// Verify footer is NOT hidden (display: none !important removed for dashboard footer)
assertCondition(
    !preg_match('/body\.mobile-dashboard-active\s+\.app-footer[^{]*\{[^}]*display:\s*none/i', $appCssContent),
    'app.css does NOT hide .app-footer via body.mobile-dashboard-active'
);
assertCondition(
    !preg_match('/\.main-wrapper:has\(\.mv-dashboard-view\)\s+\.app-footer[^{]*\{[^}]*display:\s*none/i', $appCssContent),
    'app.css does NOT hide .app-footer via .main-wrapper:has(.mv-dashboard-view)'
);

// Verify mobile dashboard footer clearance exists in app.css
assertCondition(
    strpos($appCssContent, '.dashboard-app-footer') !== false,
    'app.css styles .dashboard-app-footer for natural layout & clearance'
);

// Verify NO negative margins on dashboard footer in app.css
assertCondition(
    !preg_match('/\.dashboard-app-footer[^{]*\{[^}]*margin-top:\s*-[0-9]/i', $appCssContent),
    'app.css does NOT use negative margins on .dashboard-app-footer'
);


echo "\n====================================================================\n";
echo "3. VERIFYING LAYOUT TEMPLATE (office.php)\n";
echo "====================================================================\n";

$layoutFile = __DIR__ . '/../app/Views/layouts/office.php';
assertCondition(file_exists($layoutFile), 'office.php exists');

$layoutContent = file_get_contents($layoutFile);

// Verify $isDashboardActive computation in <head>
assertCondition(
    strpos($layoutContent, '$isDashboardActive = ($currPath === \'/office/dashboard\'') !== false,
    'office.php computes $isDashboardActive'
);

// Verify is-dashboard-page class added to body
assertCondition(
    strpos($layoutContent, 'is-dashboard-page') !== false,
    'office.php adds is-dashboard-page class to <body> when on dashboard'
);

// Verify dashboard-main-content class added to main
assertCondition(
    strpos($layoutContent, 'dashboard-main-content') !== false,
    'office.php adds dashboard-main-content class to <main> when on dashboard'
);

// Verify dashboard-app-footer class added to footer
assertCondition(
    strpos($layoutContent, 'dashboard-app-footer') !== false,
    'office.php adds dashboard-app-footer class to <footer> when on dashboard'
);

// Verify footer exists and is not removed
assertCondition(
    strpos($layoutContent, '<footer class="app-footer') !== false,
    'office.php retains <footer class="app-footer">'
);

// Verify bottom navigation exists
assertCondition(
    strpos($layoutContent, '<nav class="bottom-nav notched-bottom-nav"') !== false,
    'office.php retains <nav class="bottom-nav notched-bottom-nav">'
);


echo "\n====================================================================\n";
echo "4. VERIFYING DESKTOP PRESERVATION\n";
echo "====================================================================\n";

// Desktop .app-footer retains margin-top: auto
assertCondition(
    (bool)preg_match('/\.app-footer\s*\{[^}]*margin-top:\s*auto/s', $appCssContent),
    'Desktop .app-footer retains margin-top: auto'
);

// Desktop .bottom-nav is hidden
assertCondition(
    (bool)preg_match('/\.bottom-nav\s*\{[^}]*display:\s*none/s', $appCssContent),
    'Desktop .bottom-nav is hidden (display: none)'
);

// Desktop .main-content has 1.5rem 2rem 2.5rem padding
assertCondition(
    (bool)preg_match('/\.main-content\s*\{[^}]*padding:\s*1\.5rem\s+2rem\s+2\.5rem/s', $appCssContent),
    'Desktop .main-content retains standard padding (1.5rem 2rem 2.5rem)'
);


echo "\n====================================================================\n";
echo "5. VERIFYING CONTROLLER & LAYOUT RENDERING WITH MOCK DATA\n";
echo "====================================================================\n";

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';

\App\Core\Session::start();
\App\Core\Session::set('user_id', 1);
\App\Core\Session::set('user_role', 'admin');
\App\Core\Session::set('user_name', 'Admin');
\App\Core\Session::set('csrf_token', 'test_token');
$_SERVER['REQUEST_URI'] = '/office/dashboard';
$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    $ctrl = new \App\Controllers\DashboardController();
    $req = new \App\Core\Request();
    $res = $ctrl->index($req);
    $html = $res->getContent();

    assertCondition(!empty($html), 'DashboardController::index renders successfully');
    assertCondition(strpos($html, 'is-dashboard-page') !== false, 'Rendered HTML contains class "is-dashboard-page" on <body>');
    assertCondition(strpos($html, 'dashboard-main-content') !== false, 'Rendered HTML contains class "dashboard-main-content" on <main>');
    assertCondition(strpos($html, 'dashboard-app-footer') !== false, 'Rendered HTML contains class "dashboard-app-footer" on <footer>');
    assertCondition(strpos($html, 'db-recent-card') !== false, 'Rendered HTML contains Recent Bookings card (.db-recent-card)');
    assertCondition(strpos($html, 'app-footer') !== false, 'Rendered HTML contains footer (.app-footer)');
    assertCondition(strpos($html, 'notched-bottom-nav') !== false, 'Rendered HTML contains mobile bottom nav (.notched-bottom-nav)');
    assertCondition(strpos($html, 'All rights reserved') !== false, 'Rendered HTML footer contains copyright text');
} catch (\Throwable $e) {
    assertCondition(false, 'Render failed with error: ' . $e->getMessage());
}


echo "\n====================================================================\n";
echo "SUMMARY: Passed: {$passCount}, Failed: {$failCount}\n";
echo "====================================================================\n";

exit($failCount > 0 ? 1 : 0);
