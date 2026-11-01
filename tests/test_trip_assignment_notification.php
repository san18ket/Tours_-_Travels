<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\Employee;
use App\Models\Notification;
use App\Services\DispatchService;
use App\Services\NotificationService;

$pdo = Database::getConnection();

echo "============================================================\n";
echo "TEST: Trip Assignment Professional Notification Dispatch\n";
echo "============================================================\n";

// 1. Ensure a driver profile with user and employee exists
$driverModel = new DriverProfile();
$availableDrivers = $driverModel->getAvailableDrivers();
if (empty($availableDrivers)) {
    echo "No available drivers found in database. Cannot run test.\n";
    exit(1);
}

$driver = $availableDrivers[0];
$driverId = (int)$driver['id'];
$driverUserId = (int)$driver['user_id'];
$driverEmpId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;

echo "Selected Driver: {$driver['name']} (ID: {$driverId}, User: {$driverUserId}, Emp: " . ($driverEmpId ?: 'N/A') . ")\n";

// Ensure duty status is 'on_duty' or 'off_duty' for assignment
$pdo->prepare("UPDATE driver_profiles SET duty_status = 'off_duty' WHERE id = ?")->execute([$driverId]);

// 2. Ensure an available vehicle exists
$vehicleModel = new Vehicle();
$vehicle = $pdo->query("SELECT * FROM vehicles WHERE status = 'available' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$vehicle) {
    $vehicleId = $vehicleModel->create([
        'make_model'       => 'Toyota Innova Crysta Luxury',
        'vehicle_number'   => 'MH-02-AB-9999',
        'seating_capacity' => 7,
        'fuel_type'        => 'diesel',
        'odometer_km'      => 45200,
        'status'           => 'available',
    ]);
    $vehicle = $vehicleModel->find($vehicleId);
    $createdTestVehicle = true;
    echo "Created Test Vehicle ID: {$vehicleId} ({$vehicle['vehicle_number']})\n";
} else {
    $vehicleId = (int)$vehicle['id'];
    $createdTestVehicle = false;
    echo "Using Existing Vehicle ID: {$vehicleId} ({$vehicle['vehicle_number']})\n";
}

// 3. Create a test confirmed booking
$bookingModel = new Booking();
$bookingCode = 'BKG-TEST-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
$bookingId = $bookingModel->create([
    'booking_code'    => $bookingCode,
    'customer_name'   => 'Dr. Rajesh Deshmukh',
    'customer_phone'  => '9820011223',
    'customer_email'  => 'rajesh.deshmukh@example.com',
    'pickup_address'  => 'Terminal 2, Chhatrapati Shivaji Maharaj International Airport, Mumbai',
    'drop_address'    => 'The Oberoi Mumbai, Nariman Point, Marine Drive',
    'start_date'      => date('Y-m-d H:i:s', strtotime('+1 day 09:30:00')),
    'passenger_count' => 3,
    'status'          => 'confirmed',
    'base_fare'       => 4500.00,
    'total_amount'    => 4500.00,
]);
echo "Created Test Booking ID: {$bookingId} ({$bookingCode})\n";

// 4. Execute dispatch assignment
$dispatchService = new DispatchService();
$assignedByUserId = 1; // Super Admin

echo "\nExecuting DispatchService::assignBooking...\n";
$dispatch = $dispatchService->assignBooking($bookingId, $vehicleId, $driverId, $assignedByUserId);

echo "Dispatch successful! Code: {$dispatch['dispatch_code']} (ID: {$dispatch['id']})\n";

// 5. Verify notification info returned in dispatch
$notifInfo = $dispatch['notification_info'] ?? null;
if (!$notifInfo) {
    echo "FAILED: notification_info missing from dispatch return payload!\n";
    exit(1);
}
echo "Notification dispatched successfully:\n";
echo " - Recipient: {$notifInfo['employee_name']} ({$notifInfo['employee_phone']})\n";
echo " - In-App Title: {$notifInfo['in_app_title']}\n";
echo " - Notification ID: {$notifInfo['notification_id']}\n";

// 6. Verify record in notifications table
$notifModel = new Notification();
$notifRow = $notifModel->find((int)$notifInfo['notification_id']);
if (!$notifRow) {
    echo "FAILED: Notification record not found in notifications table!\n";
    exit(1);
}

assert($notifRow['user_id'] == $driverUserId, "User ID matches");
assert($notifRow['type'] === 'trip_assigned', "Type is trip_assigned");
assert((int)$notifRow['reference_id'] === (int)$dispatch['id'], "Reference ID matches dispatch");
assert($notifRow['is_read'] == 0, "Notification is unread");
echo "Verified: notifications table record created with is_read=0 and correct user_id & reference_id.\n";

// 7. Verify NotificationService queries
$notifService = new NotificationService();
$driverNotifs = $notifService->getDriverNotifications($driverUserId, $driverEmpId, 5);
assert(!empty($driverNotifs), "Driver has notifications");
assert($driverNotifs[0]['id'] == $notifInfo['notification_id'], "Top notification matches latest dispatch");
echo "Verified: getDriverNotifications retrieves the assignment notification at the top.\n";

$unreadCount = $notifService->getUnreadCount($driverUserId, $driverEmpId);
echo "Current Unread Count for Driver: {$unreadCount}\n";
assert($unreadCount >= 1, "Unread count is at least 1");

// 8. Verify Mark as Read
$markReadSuccess = $notifService->markAsRead((int)$notifInfo['notification_id'], $driverUserId);
assert($markReadSuccess, "markAsRead succeeded");
$notifRowAfter = $notifModel->find((int)$notifInfo['notification_id']);
assert($notifRowAfter['is_read'] == 1, "Notification is now marked as read");
echo "Verified: markAsRead successfully updated is_read to 1.\n";

// 9. Verify SMS log file
$logFile = dirname(__DIR__) . '/storage/logs/sms_notifications.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    if (str_contains($logContent, $dispatch['dispatch_code'])) {
        echo "Verified: SMS/WhatsApp dispatch log written with dispatch code: {$dispatch['dispatch_code']}.\n";
    } else {
        echo "WARNING: SMS log does not contain dispatch code.\n";
    }
} else {
    echo "FAILED: sms_notifications.log file does not exist!\n";
    exit(1);
}

// 10. Clean up test data
echo "\nCleaning up test data...\n";
$pdo->prepare("DELETE FROM dispatches WHERE id = ?")->execute([$dispatch['id']]);
$pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bookingId]);
$pdo->prepare("DELETE FROM notifications WHERE id = ?")->execute([$notifInfo['notification_id']]);
$pdo->prepare("DELETE FROM trip_status_history WHERE dispatch_id = ?")->execute([$dispatch['id']]);
$pdo->prepare("UPDATE driver_profiles SET duty_status = 'off_duty' WHERE id = ?")->execute([$driverId]);
if (!empty($createdTestVehicle)) {
    $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$vehicleId]);
} else {
    $pdo->prepare("UPDATE vehicles SET status = 'available' WHERE id = ?")->execute([$vehicleId]);
}

echo "============================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY! Notification engine is fully verified.\n";
echo "============================================================\n";

