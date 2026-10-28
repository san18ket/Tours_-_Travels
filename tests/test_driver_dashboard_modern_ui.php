<?php
/**
 * Test: Driver Dashboard Modern UI Matching Reference Image
 * Validates layout components, responsive rules, KPI metrics, active trip timeline,
 * quick action tiles, recent notifications, vehicle specs, and modals.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Models\User;
use App\Models\DriverProfile;

function assertCondition($cond, $msg) {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "======================================================\n";
echo " RUNNING SUITE: Driver Dashboard Modern UI Console (/driver/dashboard)\n";
echo "======================================================\n";

// 1. Setup authenticated driver session
Session::start();
$userModel = new User();
$driverModel = new DriverProfile();

$allProfiles = $driverModel->all();
if (!empty($allProfiles)) {
    $prof = $allProfiles[0];
    $testDriverUser = $userModel->find($prof['user_id']);
    $driverId = (int)$prof['user_id'];
    $driverName = $testDriverUser['name'] ?? 'Ramesh K.';
} else {
    $driverId = 17;
    $driverName = 'Ramesh K.';
}

Session::set('user_id', $driverId);
Session::set('user_role', 'driver');
Session::set('user', [
    'id'   => $driverId,
    'name' => $driverName,
    'role' => 'driver',
]);

// 2. Dispatch GET /driver/dashboard
$router = require APP_ROOT . '/app/routes.php';
$req = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/driver/dashboard'
]);

$res = $router->dispatch($req);
$html = $res->getBody();

echo "\n--- 1. Testing Route Dispatch & Master Layout Shell ---\n";
assertCondition($res->getStatusCode() === 200, "GET /driver/dashboard returns HTTP 200 OK");
assertCondition(strpos($html, 'id="sidebar"') !== false, "Sidebar navigation shell is preserved");
assertCondition(strpos($html, 'topbar-user-profile') !== false, "Top navbar profile chip is preserved");
assertCondition(strpos($html, 'topbar-search-form') !== false, "Desktop top navbar omnisearch form is present (matching owner dashboard)");
assertCondition(strpos($html, 'toggleAppFullscreen()') !== false, "Desktop top navbar fullscreen toggle button is present");
assertCondition(strpos($html, 'mv-dash-topbar') !== false, "Mobile top navbar component is present");
assertCondition(strpos($html, 'notched-bottom-nav') !== false, "Curved notched bottom navigation is present (matching owner dashboard)");
assertCondition(strpos($html, 'bottom-nav-fab-btn') !== false, "Center elevated notch FAB button is present");

echo "\n--- 2. Testing Top Hero Section (Greeting Banner & Shift Card) ---\n";
assertCondition(strpos($html, 'driver-greeting-banner') !== false, "Driver greeting banner card is present");
assertCondition(strpos($html, 'driver-avatar-ring') !== false, "Chauffeur avatar with blue ring is present");
assertCondition(strpos($html, 'driver-journey-tagline') !== false, "Journey tagline 'Drive Safe • Keep Smiling...' is present");
assertCondition(strpos($html, 'driver-banner-slogan') !== false, "Handwritten script slogan 'Together We Travel Further' is present");
assertCondition(strpos($html, 'driver-swoosh-svg') !== false, "Flight loop swoosh vector is present");
assertCondition(strpos($html, 'driver-banner-bus-backdrop') !== false, "Scenic bus highway backdrop is present");

assertCondition(strpos($html, 'driver-shift-card') !== false, "Duty shift card is present");
assertCondition(strpos($html, 'shift-duty-indicator') !== false, "Duty status indicator with pulse dot is present");
assertCondition(strpos($html, 'shift-datetime-group') !== false, "Shift live clock and date group is present");
assertCondition(strpos($html, 'btn-shift-action') !== false, "Shift action toggle button is present");

echo "\n--- 3. Testing 4 KPI Statistics Cards ---\n";
assertCondition(strpos($html, 'driver-kpi-grid') !== false, "4-column KPI grid is present");
assertCondition(strpos($html, "Today's Trip") !== false, "KPI 1: Today's Trip card present");
assertCondition(strpos($html, "(In Progress)") !== false, "KPI 1: (In Progress) status present");
assertCondition(strpos($html, "Total Trips") !== false, "KPI 2: Total Trips card present");
assertCondition(strpos($html, "Total Km") !== false, "KPI 3: Total Km card present");
assertCondition(strpos($html, "On-Time Rate") !== false, "KPI 4: On-Time Rate card present");

echo "\n--- 4. Testing Active Trip Card & Route Timeline ---\n";
assertCondition(strpos($html, 'driver-active-trip-card') !== false, "Active trip card container is present");
assertCondition(strpos($html, 'Active Trip') !== false, "Active Trip pill badge is present");
assertCondition(strpos($html, 'active-trip-route-title') !== false, "Route title element is present");
assertCondition(strpos($html, 'route-vertical-timeline') !== false, "Vertical route timeline stepper is present");
assertCondition(strpos($html, 'Mumbai') !== false, "Timeline Stop 1 (Mumbai) is present");
assertCondition(strpos($html, 'Departed') !== false, "Stop 1 Departed badge is present");
assertCondition(strpos($html, 'Panvel') !== false, "Timeline Stop 2 (Panvel) is present");
assertCondition(strpos($html, 'Reached') !== false, "Stop 2 Reached badge is present");
assertCondition(strpos($html, 'Lonavala') !== false, "Timeline Stop 3 (Lonavala) is present");
assertCondition(strpos($html, 'Upcoming') !== false, "Stop 3 Upcoming badge is present");
assertCondition(strpos($html, 'active-trip-bus-visual-wrap') !== false, "Bus cutout visual container is present");
assertCondition(strpos($html, 'btn-view-route-pill') !== false, "View Route button pill is present");
assertCondition(strpos($html, 'active-trip-telemetry-strip') !== false, "Bottom telemetry strip is present");
assertCondition(strpos($html, 'Current Location') !== false, "Current Location telemetry field is present");
assertCondition(strpos($html, 'ETA to Next Stop') !== false, "ETA to Next Stop telemetry field is present");

echo "\n--- 5. Testing Quick Actions & Right Stack ---\n";
assertCondition(strpos($html, 'driver-quick-actions-card') !== false, "Quick Actions card is present");
assertCondition(strpos($html, 'View Trip Details') !== false, "Quick action 1: View Trip Details is present");
assertCondition(strpos($html, 'Update Location') !== false, "Quick action 2: Update Location is present");
assertCondition(strpos($html, 'Contact Manager') !== false, "Quick action 3: Contact Manager is present");
assertCondition(strpos($html, 'Emergency') !== false, "Quick action 4: Emergency SOS is present");

assertCondition(strpos($html, 'driver-notif-card') !== false, "Recent Notifications card is present");
assertCondition(strpos($html, 'Trip started successfully') !== false, "Notification 1 is present");
assertCondition(strpos($html, 'Passenger count updated') !== false, "Notification 2 is present");
assertCondition(strpos($html, 'Next stop approaching') !== false, "Notification 3 is present");
assertCondition(strpos($html, 'New message from manager') !== false, "Notification 4 is present");

assertCondition(strpos($html, 'driver-vehicle-card') !== false, "Vehicle Information card is present");
assertCondition(strpos($html, 'veh-thumb-wrap') !== false, "Vehicle thumbnail wrap is present");
assertCondition(strpos($html, 'Tata Starbus') !== false, "Vehicle Make is displayed");
assertCondition(strpos($html, 'AC Coach') !== false, "Vehicle Type is displayed");
assertCondition(strpos($html, 'Capacity') !== false, "Vehicle Capacity is displayed");

assertCondition(strpos($html, 'driver-safe-banner') !== false, "Drive Safe banner is present");
assertCondition(strpos($html, 'Drive Safe') !== false, "Drive Safe title is present");
assertCondition(strpos($html, 'Your journey matters') !== false, "Your journey matters tagline is present");

echo "\n--- 6. Testing Responsive CSS Breakpoints ---\n";
assertCondition(strpos($html, '@media (max-width: 1280px)') !== false, "Desktop LG breakpoint (1280px) defined");
assertCondition(strpos($html, '@media (max-width: 1024px)') !== false, "Tablet breakpoint (1024px) defined");
assertCondition(strpos($html, '@media (max-width: 768px)') !== false, "Small tablet / Mobile breakpoint (768px) defined");
assertCondition(strpos($html, '@media (max-width: 540px)') !== false, "Compact phone breakpoint (540px) defined");

echo "\n--- 7. Testing Interactive Modals ---\n";
assertCondition(strpos($html, 'contactManagerModal') !== false, "Contact Manager modal dialog is present");
assertCondition(strpos($html, 'emergencyModal') !== false, "Emergency SOS modal dialog is present");
assertCondition(strpos($html, '+91 98765 43210') !== false, "Central hotline number +91 98765 43210 is present in modals");
assertCondition(strpos($html, 'tel:112') !== false, "National emergency dialer link 112 is present");
assertCondition(strpos($html, 'tel:108') !== false, "Highway ambulance dialer link 108 is present");

echo "\n============================================\n";
echo "TEST RESULTS: All assertions passed successfully!\n";
echo "============================================\n";
