<?php
/**
 * Test Fixed Top and Bottom Navbars on Responsive Devices for Driver and Owner Portals
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\User;
use App\Controllers\DriverPortalController;
use App\Controllers\DashboardController;
use App\Core\Request;
use App\Core\Session;

echo "=== VERIFYING FIXED TOP & BOTTOM NAVBARS ===" . PHP_EOL;

// 1. Verify CSS rules in public/assets/css/app.css
$css = file_get_contents(__DIR__ . '/../public/assets/css/app.css');

$cssChecks = [
    'Fixed Mobile Topbar position' => ['pattern' => '/\.mv-dash-topbar\s*\{[^}]*position:\s*fixed\s*!important/s', 'label' => 'Mobile Topbar is fixed'],
    'Fixed Mobile Topbar top'      => ['pattern' => '/\.mv-dash-topbar\s*\{[^}]*top:\s*0\s*!important/s', 'label' => 'Mobile Topbar top is 0'],
    'Fixed Mobile Topbar z-index'  => ['pattern' => '/\.mv-dash-topbar\s*\{[^}]*z-index:\s*1000\s*!important/s', 'label' => 'Mobile Topbar z-index is 1000'],
    'Fixed Bottom Nav position'    => ['pattern' => '/\.notched-bottom-nav\s*\{[^}]*position:\s*fixed\s*!important/s', 'label' => 'Bottom Nav is fixed'],
    'Fixed Bottom Nav bottom'      => ['pattern' => '/\.notched-bottom-nav\s*\{[^}]*bottom:\s*0\s*!important/s', 'label' => 'Bottom Nav bottom is 0'],
    'Fixed Bottom Nav z-index'     => ['pattern' => '/\.notched-bottom-nav\s*\{[^}]*z-index:\s*990\s*!important/s', 'label' => 'Bottom Nav z-index is 990'],
    'Main content top clearance'   => ['pattern' => '/padding-top:\s*calc\(60px\s*\+\s*14px\)\s*!important/s', 'label' => 'Main content top clearance matches 60px header'],
    'Main content bottom clearance'=> ['pattern' => '/padding-bottom:\s*calc\(64px\s*\+\s*32px/s', 'label' => 'Main content bottom clearance matches 64px dock'],
];

foreach ($cssChecks as $name => $check) {
    if (preg_match($check['pattern'], $css)) {
        echo "[PASS] {$check['label']}" . PHP_EOL;
    } else {
        echo "[FAIL] Check failed: {$check['label']}" . PHP_EOL;
        exit(1);
    }
}

// 2. Test Driver Portal Dashboard Page
$db = Database::getConnection();
$driverUser = $db->query("SELECT u.*, e.id as employee_id FROM users u LEFT JOIN employees e ON e.user_id = u.id WHERE u.role = 'driver' AND u.status = 'active' LIMIT 1")->fetch();

if ($driverUser) {
    Session::set('user_id', $driverUser['id']);
    Session::set('role', 'driver');
    Session::set('username', $driverUser['username'] ?? 'driver');

    $driverCtrl = new DriverPortalController();
    $resp = $driverCtrl->dashboard(new Request());
    if ($resp->getStatusCode() === 200 && strpos($resp->getBody(), 'mv-dash-topbar') !== false && strpos($resp->getBody(), 'notched-bottom-nav') !== false) {
        echo "[PASS] Driver Dashboard rendered 200 OK with both fixed mobile topbar & bottom nav." . PHP_EOL;
    } else {
        echo "[FAIL] Driver Dashboard failed to render properly!" . PHP_EOL;
        exit(1);
    }

    $respNotif = $driverCtrl->notificationsPage(new Request());
    if ($respNotif->getStatusCode() === 200 && strpos($respNotif->getBody(), 'mv-dash-topbar') !== false && strpos($respNotif->getBody(), 'notched-bottom-nav') !== false) {
        echo "[PASS] Driver Notifications Page rendered 200 OK with both fixed mobile topbar & bottom nav." . PHP_EOL;
    } else {
        echo "[FAIL] Driver Notifications Page failed to render properly!" . PHP_EOL;
        exit(1);
    }
}

// 3. Test Owner / Office Dashboard Page
$ownerUser = $db->query("SELECT * FROM users WHERE role IN ('super_admin', 'admin', 'owner') AND status = 'active' LIMIT 1")->fetch();

if ($ownerUser) {
    Session::set('user_id', $ownerUser['id']);
    Session::set('role', $ownerUser['role']);
    Session::set('username', $ownerUser['username'] ?? 'admin');

    $dashCtrl = new DashboardController();
    $respOwner = $dashCtrl->index(new Request());
    if ($respOwner->getStatusCode() === 200 && strpos($respOwner->getBody(), 'mv-dash-topbar') !== false && strpos($respOwner->getBody(), 'notched-bottom-nav') !== false) {
        echo "[PASS] Owner/Office Dashboard rendered 200 OK with both fixed mobile topbar & bottom nav." . PHP_EOL;
    } else {
        echo "[FAIL] Owner Dashboard failed to render properly!" . PHP_EOL;
        exit(1);
    }
}

echo "=== ALL FIXED NAVBAR VERIFICATION TESTS PASSED SUCCESSFULLY! ===" . PHP_EOL;
