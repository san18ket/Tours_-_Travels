<?php
/**
 * Test Driver Notification Features:
 * 1. Notification Dropdown UI without right icon buttons.
 * 2. Notification rows click directly to trip details page.
 * 3. Mobile bottom bar routes to /driver/notifications.
 * 4. Dedicated notifications page (/driver/notifications) renders KPI cards, filters, and notification cards.
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Controllers\DriverPortalController;
use App\Core\Request;
use App\Core\Session;

echo "=== STARTING NOTIFICATION FEATURE VERIFICATION ===" . PHP_EOL;

// 1. Check layout driver.php file contents for requirements
$layoutContent = file_get_contents(__DIR__ . '/../app/Views/layouts/driver.php');

// A. Verify no green or red icon buttons in notification items
if (strpos($layoutContent, 'notif-btn-green') !== false || strpos($layoutContent, 'notif-btn-red') !== false) {
    echo "[FAIL] Found green/red action button classes in layouts/driver.php!" . PHP_EOL;
    exit(1);
} else {
    echo "[PASS] Both action buttons (green & red) are completely removed from notification box." . PHP_EOL;
}

// B. Verify notification rows are clickable links with targetUrl
if (strpos($layoutContent, '<a href="<?= $targetUrl ?>" class="notif-box-item') !== false &&
    strpos($layoutContent, 'onclick="markNotifReadAsync(') !== false) {
    echo "[PASS] Notification box items are direct clickable <a> links with markNotifReadAsync." . PHP_EOL;
} else {
    echo "[FAIL] Notification box items are not rendered as direct clickable links!" . PHP_EOL;
    exit(1);
}

// C. Verify mobile bottom navbar notification link points to /driver/notifications
if (strpos($layoutContent, 'href="<?= url(\'/driver/notifications\') ?>" class="bottom-nav-item nav-notif-item') !== false) {
    echo "[PASS] Mobile bottom navbar notification item points to /driver/notifications." . PHP_EOL;
} else {
    echo "[FAIL] Mobile bottom navbar notification item does not point to /driver/notifications!" . PHP_EOL;
    exit(1);
}

// 2. Test controller action notificationsPage
// Find or create a test driver user
$db = Database::getInstance();
$driverUser = $db->query("SELECT u.*, e.id as employee_id FROM users u LEFT JOIN employees e ON e.user_id = u.id WHERE u.role = 'driver' AND u.status = 'active' LIMIT 1")->fetch();

if (!$driverUser) {
    echo "[WARN] No driver user found in DB, skipping live session test." . PHP_EOL;
} else {
    echo "[INFO] Testing with Driver User ID: {$driverUser['id']}, Name: {$driverUser['name']}" . PHP_EOL;
    
    // Set up session
    $_SESSION['user_id'] = $driverUser['id'];
    $_SESSION['role'] = 'driver';
    $_SESSION['username'] = $driverUser['username'] ?? 'driver';
    
    // Add a test notification if none exist
    $notifService = new NotificationService();
    $unreadCount = $notifService->getUnreadCount($driverUser['id'], $driverUser['employee_id']);
    
    if ($unreadCount === 0) {
        $notifService->notifyDriverTripAssigned($driverUser['id'], $driverUser['employee_id'], [
            'dispatch_id' => 9999,
            'dispatch_code' => 'DSP-TEST-999',
            'pickup_address' => 'Pune Airport, Lohegaon',
            'drop_address' => 'JW Marriott, Senapati Bapat Road',
            'reporting_time' => date('d M Y, h:i A', strtotime('+2 hours')),
            'vehicle_name' => 'Toyota Innova Crysta',
            'vehicle_number' => 'MH 12 AB 1234',
            'customer_name' => 'Rajesh Sharma',
            'customer_phone' => '+91 98765 43210',
            'passenger_count' => 2,
        ]);
        echo "[INFO] Created sample test notification." . PHP_EOL;
    }
    
    // Invoke notificationsPage controller
    $controller = new DriverPortalController();
    $req = new Request();
    $response = $controller->notificationsPage($req);
    
    $html = $response->getBody();
    
    // Check page elements
    $checks = [
        'Duty Notifications & Alerts' => 'Page Header Title',
        'Total Notifications' => 'KPI Total Card',
        'Unread Alerts' => 'KPI Unread Card',
        'Duty Dispatches' => 'KPI Dispatches Card',
        'Read / Archived' => 'KPI Read Card',
        'driverNotifFilterForm' => 'Filter Form',
        'Search by trip code' => 'Search Input',
        'Review Trip Duty Details' => 'Trip Details Action Button',
    ];
    
    foreach ($checks as $needle => $label) {
        if (strpos($html, $needle) !== false) {
            echo "[PASS] $label is present in rendered HTML." . PHP_EOL;
        } else {
            echo "[FAIL] $label ('$needle') is missing from rendered HTML!" . PHP_EOL;
            exit(1);
        }
    }
}

echo "=== ALL NOTIFICATION FEATURE TESTS PASSED SUCCESSFULLY! ===" . PHP_EOL;
