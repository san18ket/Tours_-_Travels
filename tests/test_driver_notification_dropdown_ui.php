<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use App\Models\DriverProfile;
use App\Models\Notification;

echo "============================================================\n";
echo "TEST: Driver Navbar Notification Box Popover UI (Ref Image)\n";
echo "============================================================\n";

$pdo = Database::getConnection();

// 1. Resolve Driver user
$driverUser = $pdo->query("SELECT u.id, u.name, u.role FROM users u JOIN driver_profiles dp ON u.id = dp.user_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$driverUser) {
    echo "No driver user found. Test skipped.\n";
    exit(0);
}

Session::start();
$_SESSION['user_id'] = $driverUser['id'];
$_SESSION['role'] = 'driver';

// 2. Ensure at least one test notification exists for this driver
$notifModel = new Notification();
$testNotifId = $notifModel->create([
    'user_id'        => $driverUser['id'],
    'title'          => 'Test Duty Assignment DSP-TEST-001',
    'message'        => 'Test trip assignment for driver dropdown verification',
    'type'           => 'trip_assigned',
    'reference_type' => 'dispatches',
    'reference_id'   => 9999,
    'channel'        => 'in_app',
    'data'           => json_encode([
        'dispatch_code'  => 'DSP-261101-ABCD',
        'pickup_address' => 'Mumbai International Airport T2',
        'drop_address'   => 'Taj Lands End, Bandra',
        'reporting_time' => '02 Nov 2026, 09:30 AM',
        'vehicle_name'   => 'Toyota Innova Crysta',
        'vehicle_number' => 'MH-02-EE-4501',
    ]),
    'is_read'        => 0,
]);
echo "Created test unread notification ID: {$testNotifId}\n";

// 3. Render Driver Dashboard
ob_start();
try {
    $request = new \App\Core\Request();
    $controller = new \App\Controllers\DriverPortalController();
    $response = $controller->dashboard($request);
    $response->send();
    $html = ob_get_clean();
} catch (\Throwable $e) {
    ob_end_clean();
    echo "FAILED to render driver dashboard: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Assertions for Notification Dropdown matching Ref Image
$checks = [
    'topbar-notif-wrap wrapper'          => 'topbar-notif-wrap',
    'toggleNotifDropdown button action'  => 'onclick="toggleNotifDropdown(event)"',
    'topbar-notif-box card'              => 'topbar-notif-box',
    'Header title Duty & Trip Alerts'   => 'Duty &amp; Trip Alerts',
    'Header red badge dropdownHeaderBadge' => 'dropdownHeaderBadge',
    'Section 1 Red NEW DUTY ASSIGNMENTS' => 'NEW DUTY ASSIGNMENTS',
    'Green Action Button (Refresh/Accept)'=> 'notif-btn-green',
    'Red Dismiss Button (Cross)'         => 'notif-btn-red',
    'Dismiss function dismissDropdownNotif' => 'dismissDropdownNotif(',
    'Footer View All Trips link'         => 'View All Trips &rarr;',
    'Global CSS rules for notif box'     => '.topbar-notif-box',
];

$allPassed = true;
foreach ($checks as $label => $needle) {
    if (str_contains($html, $needle)) {
        echo "  [PASS] {$label}\n";
    } else {
        echo "  [FAIL] {$label} (Missing '{$needle}')\n";
        $allPassed = false;
    }
}

// 5. Clean up test notification
$pdo->prepare("DELETE FROM notifications WHERE id = ?")->execute([$testNotifId]);
echo "Cleaned up test notification.\n";

if ($allPassed) {
    echo "\n============================================================\n";
    echo "ALL 11 CHECKS PASSED! Navbar notification dropdown is verified.\n";
    echo "============================================================\n";
    exit(0);
} else {
    echo "\nSome checks failed.\n";
    exit(1);
}
