<?php
declare(strict_types=1);

/**
 * Test Suite: Driver Vehicle Page Theming for Desktop (LG) and Mobile Devices
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Controllers\DriverPortalController;
use App\Core\Request;

$pdo = Database::getConnection();
$failures = [];
$successCount = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "=== DRIVER VEHICLE PAGE THEME TEST SUITE ===\n\n";

// -----------------------------------------------------------------------------
// 1. Static View Code Verification
// -----------------------------------------------------------------------------
echo "1. Verifying View Code in app/Views/driver/vehicle.php...\n";
$viewCode = file_get_contents(__DIR__ . '/../app/Views/driver/vehicle.php');

assertCondition(str_contains($viewCode, 'class="desktop-header-wrapper"'), "Vehicle view contains .desktop-header-wrapper", $failures, $successCount);
assertCondition(str_contains($viewCode, 'id="vehicleKpiGrid"'), "Vehicle view contains #vehicleKpiGrid for LG desktop", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="mv-bookings-header"'), "Vehicle view contains .mv-bookings-header for mobile devices", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="mv-top-bar"'), "Vehicle view contains .mv-top-bar", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="mv-back-btn"'), "Vehicle view contains .mv-back-btn", $failures, $successCount);
assertCondition(str_contains($viewCode, 'id="vehicleMvStatusScroll"'), "Vehicle view contains #vehicleMvStatusScroll horizontal pills", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="driver-vehicle-grid"'), "Vehicle view contains .driver-vehicle-grid layout", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="driver-vehicle-hero-banner"'), "Vehicle view contains Forest Teal .driver-vehicle-hero-banner", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="driver-vehicle-specs-grid"'), "Vehicle view contains .driver-vehicle-specs-grid", $failures, $successCount);
assertCondition(str_contains($viewCode, 'class="driver-compliance-list"'), "Vehicle view contains .driver-compliance-list", $failures, $successCount);
assertCondition(str_contains($viewCode, 'Pre-Trip Inspection Protocol'), "Vehicle view contains Pre-Trip Inspection Protocol", $failures, $successCount);
assertCondition(str_contains($viewCode, '@media (max-width: 768px)'), "Vehicle view defines mobile media query", $failures, $successCount);
assertCondition(str_contains($viewCode, '@media (min-width: 769px)'), "Vehicle view defines LG desktop media query", $failures, $successCount);

// -----------------------------------------------------------------------------
// 2. Controller Execution & Render Verification
// -----------------------------------------------------------------------------
echo "\n2. Testing DriverPortalController::vehicle() Render...\n";

// Authenticate as a driver fixture
$stmtDriver = $pdo->query("SELECT dp.*, u.id as user_id, u.name as user_name FROM driver_profiles dp JOIN users u ON dp.user_id = u.id LIMIT 1");
$driver = $stmtDriver->fetch(\PDO::FETCH_ASSOC);

if ($driver) {
    $_SESSION['user_id'] = (int)$driver['user_id'];
    $_SESSION['user_role'] = 'driver';
    $_SESSION['user_name'] = $driver['user_name'];

    $controller = new DriverPortalController();
    $req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET']);
    $res = $controller->vehicle($req);

    assertCondition($res->getStatusCode() === 200, "Driver vehicle endpoint returns HTTP 200", $failures, $successCount);

    $html = $res->getContent();
    assertCondition(str_contains($html, 'Assigned Fleet Vehicle'), "Rendered HTML contains title 'Assigned Fleet Vehicle'", $failures, $successCount);
    assertCondition(str_contains($html, 'desktop-header-wrapper'), "Rendered HTML contains desktop header", $failures, $successCount);
    assertCondition(str_contains($html, 'mv-bookings-header'), "Rendered HTML contains mobile top bar header", $failures, $successCount);
    assertCondition(str_contains($html, 'driver-vehicle-hero-banner') || str_contains($html, 'driver-empty-card'), "Rendered HTML contains vehicle hero banner or empty state", $failures, $successCount);
} else {
    echo " [WARN] No driver fixture found to test live controller render.\n";
}

echo "\n============================================\n";
echo "TEST RESULTS: {$successCount} PASSED, " . count($failures) . " FAILED\n";
echo "============================================\n";

if (!empty($failures)) {
    exit(1);
}

