<?php
/**
 * Test Driver Notification Features:
 * 1. Notification Dropdown UI without right icon buttons.
 * 2. Notification rows click directly to trip details page.
 * 3. Mobile bottom bar routes to /driver/notifications.
 * 4. Dedicated notifications page (/driver/notifications) renders KPI cards, filters, and notification cards.
 */

require_once __DIR__ . '/../app/bootstrap.php';

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
$db = Database::getConnection();
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
        $notifModel = new Notification();
        $payload = [
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
        ];
        $notifModel->create([
            'user_id' => $driverUser['id'],
            'employee_id' => $driverUser['employee_id'],
            'title' => 'New Trip Duty Assigned: DSP-TEST-999',
            'message' => 'You have been assigned to trip duty DSP-TEST-999.',
            'type' => 'trip_assigned',
            'reference_type' => 'dispatches',
            'reference_id' => 9999,
            'channel' => 'in_app',
            'data' => json_encode($payload),
            'is_read' => 0,
        ]);
        echo "[INFO] Created sample test notification." . PHP_EOL;
    }
    
    // Invoke notificationsPage controller
    $controller = new DriverPortalController();
    $req = new Request();
    try {
        $response = $controller->notificationsPage($req);
        $html = $response->getBody();
    } catch (\Throwable $e) {
        echo "Exception in notificationsPage: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
        exit(1);
    }
    
    // Check page elements
    $mainPos = strpos($html, '<main');
    echo "MAIN PREVIEW:" . PHP_EOL . substr($html, $mainPos, 1200) . PHP_EOL;
    $checks = [
        'Duty Notifications &amp; Alerts' => 'Card Header Title',
        'notif-ref-card' => 'Reference Card Container',
        'driverNotifFilterForm' => 'Filter Form',
        'Search by trip code' => 'Search Input',
        'NEW DUTY ASSIGNMENTS' => 'Reference Image Red Section Title',
        'notif-ref-item' => 'Clickable Notification Item',
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

    // Test markNotificationRead AJAX call
    $notifRow = $db->query("SELECT id FROM notifications WHERE user_id = {$driverUser['id']} OR employee_id = " . ($driverUser['employee_id'] ? $driverUser['employee_id'] : -1) . " ORDER BY id DESC LIMIT 1")->fetch();
    if ($notifRow) {
        $readReq = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $readResp = $controller->markNotificationRead($readReq, ['id' => (string)$notifRow['id']]);
        echo "[INFO] Mark read status: " . $readResp->getStatusCode() . ", Body: " . $readResp->getBody() . PHP_EOL;
        if ($readResp->getStatusCode() === 200 && strpos($readResp->getBody(), '"success":true') !== false) {
            echo "[PASS] markNotificationRead returns success JSON." . PHP_EOL;
        } else {
            echo "[FAIL] markNotificationRead did not return success!" . PHP_EOL;
            exit(1);
        }
    }

echo "=== ALL NOTIFICATION FEATURE TESTS PASSED SUCCESSFULLY! ===" . PHP_EOL;
