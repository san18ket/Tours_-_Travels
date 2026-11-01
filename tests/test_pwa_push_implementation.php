<?php
/**
 * Test Suite: Tours PWA & Web Push Notification Implementation
 * Verifies PWA configuration, VAPID, Database, Model, Controller, Service, and Notification Integration
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Config;
use App\Models\PushSubscription;
use App\Models\Notification;
use App\Models\User;
use App\Services\WebPushService;
use App\Services\NotificationService;
use App\Services\DispatchService;
use App\Controllers\PushSubscriptionController;

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "\n============================================================\n";
echo "1. VERIFYING PWA MANIFEST & BRANDING\n";
echo "============================================================\n";

$manifestPath = dirname(__DIR__) . '/public/manifest.json';
assertTest("manifest.json file exists", file_exists($manifestPath));

$manifest = json_decode(file_get_contents($manifestPath), true);
assertTest("Manifest JSON is valid", is_array($manifest));
assertTest("Application name is 'Tours'", ($manifest['name'] ?? '') === 'Tours');
assertTest("Short name is 'Tours'", ($manifest['short_name'] ?? '') === 'Tours');
assertTest("Display mode is standalone", ($manifest['display'] ?? '') === 'standalone');
assertTest("Start URL is './'", ($manifest['start_url'] ?? '') === './');
assertTest("Scope is './'", ($manifest['scope'] ?? '') === './');
assertTest("Icons configured with 192x192 and 512x512", count($manifest['icons'] ?? []) >= 2);

$icon192 = dirname(__DIR__) . '/public/' . ($manifest['icons'][0]['src'] ?? '');
$icon512 = dirname(__DIR__) . '/public/' . ($manifest['icons'][1]['src'] ?? '');
assertTest("Icon 192px file exists on disk", file_exists($icon192));
assertTest("Icon 512px file exists on disk", file_exists($icon512));

echo "\n============================================================\n";
echo "2. VERIFYING SERVICE WORKER (sw.js)\n";
echo "============================================================\n";

$swPath = dirname(__DIR__) . '/public/sw.js';
assertTest("sw.js file exists", file_exists($swPath));
$swContent = file_get_contents($swPath);

assertTest("Service Worker contains 'push' event listener", str_contains($swContent, "addEventListener('push'"));
assertTest("Service Worker contains 'notificationclick' event listener", str_contains($swContent, "addEventListener('notificationclick'"));
assertTest("Service Worker contains 'notificationclose' event listener", str_contains($swContent, "addEventListener('notificationclose'"));
assertTest("Service Worker excludes private routes from cache", str_contains($swContent, "PRIVATE_ROUTE_PATTERNS"));
assertTest("Service Worker targets Tours offline shell", str_contains($swContent, "Offline - Tours"));
assertTest("Service Worker uses scoped cache purging", str_contains($swContent, "tours-shell-"));

echo "\n============================================================\n";
echo "3. VERIFYING DATABASE MIGRATION 031 & SCHEMA\n";
echo "============================================================\n";

$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT migration FROM `schema_migrations` WHERE `migration` = '031_create_push_subscriptions_table.php'");
assertTest("Migration 031 is recorded in schema_migrations", (bool)$stmt->fetchColumn());

$colsStmt = $pdo->query("SHOW COLUMNS FROM `push_subscriptions`");
$columns = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

$expectedCols = ['id', 'user_id', 'endpoint', 'endpoint_hash', 'p256dh', 'auth', 'content_encoding', 'device_name', 'last_used_at', 'revoked_at', 'created_at', 'updated_at'];
foreach ($expectedCols as $col) {
    assertTest("Column '{$col}' exists in push_subscriptions", in_array($col, $columns, true));
}

echo "\n============================================================\n";
echo "4. VERIFYING PushSubscription MODEL\n";
echo "============================================================\n";

$subModel = new PushSubscription();
$testUserId = 1; // Admin user
$testEndpoint = 'https://fcm.googleapis.com/fcm/send/test-token-' . uniqid();
$testP256dh = 'test-p256dh-key-' . bin2hex(random_bytes(16));
$testAuth = 'test-auth-secret-' . bin2hex(random_bytes(8));
$testDevice = 'Test Chrome on Windows 11';

$subId = $subModel->saveSubscription($testUserId, $testEndpoint, $testP256dh, $testAuth, 'aes128gcm', $testDevice);
assertTest("PushSubscription::saveSubscription returns ID > 0", $subId > 0);

$activeList = $subModel->getActiveForUser($testUserId);
assertTest("PushSubscription::getActiveForUser finds saved subscription", !empty($activeList));

$found = false;
foreach ($activeList as $item) {
    if ((int)$item['id'] === $subId && $item['device_name'] === $testDevice) {
        $found = true;
        break;
    }
}
assertTest("Subscription details match saved parameters", $found);

// Test idempotent update (resubscribing same endpoint)
$updatedSubId = $subModel->saveSubscription($testUserId, $testEndpoint, $testP256dh, $testAuth, 'aes128gcm', 'Updated Device Name');
assertTest("Resubscribing same endpoint updates existing record (same ID)", $updatedSubId === $subId);

// Test revoke
$revoked = $subModel->revoke($subId, $testUserId);
assertTest("PushSubscription::revoke marks subscription as revoked", $revoked);

$activeAfterRevoke = $subModel->getActiveForUser($testUserId);
$stillActive = false;
foreach ($activeAfterRevoke as $item) {
    if ((int)$item['id'] === $subId) {
        $stillActive = true;
        break;
    }
}
assertTest("Revoked subscription is excluded from getActiveForUser", !$stillActive);

// Test permanent delete
$deleted = $subModel->deleteSubscription($subId, $testUserId);
assertTest("PushSubscription::deleteSubscription removes record", $deleted);

echo "\n============================================================\n";
echo "5. VERIFYING WebPushService & VAPID KEYS\n";
echo "============================================================\n";

$pushService = new WebPushService();
$pubKey = $pushService->getVapidPublicKey();
assertTest("VAPID Public Key is loaded", !empty($pubKey));
assertTest("VAPID Public Key has valid uncompressed EC length (>60 chars)", strlen($pubKey) > 60);

$payloadJson = $pushService->buildPayload('New Trip', 'Pickup at Airport', '/driver/trips/99');
$payload = json_decode($payloadJson, true);
assertTest("Notification payload contains 'Tours' branding", str_contains($payload['title'], 'Tours'));
assertTest("Notification payload contains target URL", $payload['url'] === '/driver/trips/99');
assertTest("Notification payload contains default icon and badge", !empty($payload['icon']) && !empty($payload['badge']));

echo "\n============================================================\n";
echo "6. VERIFYING PushSubscriptionController API\n";
echo "============================================================\n";

$controller = new PushSubscriptionController();

// Simulate authenticated session for test user
$userModel = new User();
$testUser = $userModel->find(1);
Session::set('user_id', 1);
Session::set('user', $testUser);
Session::set('role', $testUser['role'] ?? 'admin');

// 6.1 Test getVapidPublicKey
$reqKey = new Request();
$resKey = $controller->getVapidPublicKey($reqKey);
$dataKey = json_decode($resKey->getContent(), true);
assertTest("Controller::getVapidPublicKey returns success JSON", ($dataKey['success'] ?? false) === true);
assertTest("Controller::getVapidPublicKey returns public_key", !empty($dataKey['public_key']));

// 6.2 Test register
$apiEndpoint = 'https://fcm.googleapis.com/fcm/send/api-unit-test-' . uniqid();
$reqReg = new Request([], [
    'endpoint' => $apiEndpoint,
    'p256dh'   => 'BCz4WfS...fake...',
    'auth'     => 'fakeAuthKey123',
    'device_name' => 'Edge on Windows'
], ['REQUEST_METHOD' => 'POST']);
$resReg = $controller->register($reqReg);
$dataReg = json_decode($resReg->getContent(), true);
assertTest("Controller::register returns success JSON", ($dataReg['success'] ?? false) === true);
$createdSubId = (int)($dataReg['subscription_id'] ?? 0);
assertTest("Controller::register returned valid subscription_id", $createdSubId > 0);

// 6.3 Test status
$reqStatus = new Request();
$resStatus = $controller->status($reqStatus);
$dataStatus = json_decode($resStatus->getContent(), true);
assertTest("Controller::status indicates is_subscribed = true", ($dataStatus['is_subscribed'] ?? false) === true);
assertTest("Controller::status reports device_count >= 1", ($dataStatus['device_count'] ?? 0) >= 1);

// 6.4 Test listDevices
$reqList = new Request();
$resList = $controller->listDevices($reqList);
$dataList = json_decode($resList->getContent(), true);
assertTest("Controller::listDevices returns array of devices", is_array($dataList['devices'] ?? null));

// 6.5 Test unauthorized remove device (User 999 attempting to remove User 1's device)
Session::set('user_id', 999);
$resBadRemove = $controller->removeDevice(new Request(), ['id' => $createdSubId]);
assertTest("Cross-user device removal is rejected (HTTP 404/403)", $resBadRemove->getStatusCode() === 404);

// 6.6 Test authorized remove device
Session::set('user_id', 1);
$resGoodRemove = $controller->removeDevice(new Request(), ['id' => $createdSubId]);
$dataGoodRemove = json_decode($resGoodRemove->getContent(), true);
assertTest("Authorized device removal succeeds", ($dataGoodRemove['success'] ?? false) === true);

echo "\n============================================================\n";
echo "7. VERIFYING LAYOUTS & PROFILE DROPDOWN INTEGRATION\n";
echo "============================================================\n";

$officeLayout = file_get_contents(dirname(__DIR__) . '/app/Views/layouts/office.php');
$driverLayout = file_get_contents(dirname(__DIR__) . '/app/Views/layouts/driver.php');
$mobileTopbar = file_get_contents(dirname(__DIR__) . '/app/Views/components/mobile_topbar.php');

assertTest("office.php includes tours-pwa.css", str_contains($officeLayout, 'tours-pwa.css'));
assertTest("office.php includes tours-pwa.js", str_contains($officeLayout, 'tours-pwa.js'));
assertTest("office.php dropdown includes 'Install Tours'", str_contains($officeLayout, 'Install Tours'));
assertTest("office.php dropdown includes 'Notification Settings'", str_contains($officeLayout, 'Notification Settings'));

assertTest("driver.php includes tours-pwa.css", str_contains($driverLayout, 'tours-pwa.css'));
assertTest("driver.php includes tours-pwa.js", str_contains($driverLayout, 'tours-pwa.js'));
assertTest("driver.php dropdown includes 'Install Tours'", str_contains($driverLayout, 'Install Tours'));
assertTest("driver.php dropdown includes 'Notification Settings'", str_contains($driverLayout, 'Notification Settings'));

assertTest("mobile_topbar.php includes 'Install Tours'", str_contains($mobileTopbar, 'Install Tours'));
assertTest("mobile_topbar.php includes 'Notification Settings'", str_contains($mobileTopbar, 'Notification Settings'));

echo "\n============================================================\n";
echo "8. VERIFYING NOTIFICATION SERVICE INTEGRATION\n";
echo "============================================================\n";

$notifServiceCode = file_get_contents(dirname(__DIR__) . '/app/Services/NotificationService.php');
assertTest("NotificationService uses WebPushService", str_contains($notifServiceCode, 'WebPushService'));
assertTest("notifyDriverTripAssigned dispatches Web Push", str_contains($notifServiceCode, 'webPushService->sendToUser'));
assertTest("NotificationService contains notifyDriverTripCancelled", str_contains($notifServiceCode, 'notifyDriverTripCancelled'));
assertTest("NotificationService contains notifyOfficeDriverAction", str_contains($notifServiceCode, 'notifyOfficeDriverAction'));

$dispatchServiceCode = file_get_contents(dirname(__DIR__) . '/app/Services/DispatchService.php');
assertTest("DispatchService notifies office on driver acceptance", str_contains($dispatchServiceCode, "notifyOfficeDriverAction('accepted'"));
assertTest("DispatchService notifies office on trip start", str_contains($dispatchServiceCode, "notifyOfficeDriverAction('started'"));
assertTest("DispatchService notifies office on trip completion", str_contains($dispatchServiceCode, "notifyOfficeDriverAction('completed'"));
assertTest("DispatchService notifies driver on trip cancellation", str_contains($dispatchServiceCode, "notifyDriverTripCancelled"));

echo "\n============================================================\n";
echo "TEST SUMMARY:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "============================================================\n\n";

exit($failed > 0 ? 1 : 0);
