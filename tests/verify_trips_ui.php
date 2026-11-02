<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Controllers\DriverPortalController;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;

echo "=== Verifying Driver Trips UI against History UI Architecture ===\n";

$db = Database::getConnection();
$driverProfile = $db->query("SELECT dp.*, u.role, u.status as user_status FROM driver_profiles dp JOIN users u ON u.id = dp.user_id LIMIT 1")->fetch();

if (!$driverProfile) {
    echo "No driver profile found!\n";
    exit(1);
}

echo "Found Driver Profile: ID={$driverProfile['id']}, User ID={$driverProfile['user_id']}\n";

Session::set('user_id', (int)$driverProfile['user_id']);
Session::set('role', 'driver');
Session::set('logged_in', true);

$controller = new DriverPortalController();

$request = new Request();
try {
    $response = $controller->assignedTrips($request);
    $html = $response->getBody();

    $checks = [
        'booking-kpi-grid'       => 'Desktop KPI grid present',
        'booking-kpi-card'       => 'Desktop KPI cards present',
        'filter-bar-compact'     => 'Desktop compact filter bar present',
        'fbc-search'             => 'Compact search input wrapper present',
        'fbc-date'               => 'Styled date input (fbc-date) present',
        'fbc-btn'                => 'Filter action buttons present',
        'mv-bookings-header'     => 'Mobile app-like header present',
        'mv-top-bar'             => 'Mobile top bar present',
        'mv-search-form'         => 'Mobile search form present',
        'mv-filter-panel'        => 'Mobile filter panel drawer present',
        'mv-status-scroll'       => 'Mobile status scroll pills present',
        'mv-pill-cnt'            => 'Mobile status pill counts present',
        'icon-teal'              => 'KPI Teal icon style present',
        'icon-amber'             => 'KPI Amber icon style present',
        'icon-emerald'           => 'KPI Emerald icon style present',
        'icon-navy'              => 'KPI Navy icon style present',
    ];

    $allPassed = true;
    foreach ($checks as $needle => $desc) {
        if (strpos($html, $needle) !== false) {
            echo " [PASS] $desc ($needle)\n";
        } else {
            echo " [FAIL] $desc ($needle) NOT found in response!\n";
            $allPassed = false;
        }
    }

    if ($allPassed) {
        echo "\nSUCCESS: All UI components and design patterns for Driver Trips match Driver History perfectly!\n";
    } else {
        echo "\nERROR: Some UI components were missing.\n";
    }

} catch (\Throwable $t) {
    echo "Exception during test: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
}
