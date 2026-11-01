<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\WebPushService;
use App\Models\PushSubscription;

echo "=== TESTING WEBPUSH SERVICE ===\n";

$service = new WebPushService();
$pubKey = $service->getVapidPublicKey();

echo "VAPID Public Key: " . substr($pubKey, 0, 15) . "... (length: " . strlen($pubKey) . ")\n";
assert(!empty($pubKey), "Public key must not be empty");
assert(strlen($pubKey) > 40, "Public key must be valid length");

$payloadJson = $service->buildPayload('Trip Assigned', 'Your chauffeur trip is ready', '/driver/trips/1');
$payload = json_decode($payloadJson, true);

echo "Payload Title: {$payload['title']}\n";
assert(str_contains($payload['title'], 'Tours'), "Title must include 'Tours' branding");
assert($payload['url'] === '/driver/trips/1', "URL must match");

// Test model
$model = new PushSubscription();
$userId = 1;
$dummyEndpoint = 'https://fcm.googleapis.com/fcm/send/test-sub-' . time();
$dummyP256dh = 'BMhR-test-p256dh-key-content-here';
$dummyAuth = 'auth-token-1234';

$subId = $model->saveSubscription($userId, $dummyEndpoint, $dummyP256dh, $dummyAuth, 'aes128gcm', 'Chrome Desktop (Windows)');
echo "Saved Subscription ID: {$subId}\n";
assert($subId > 0, "Subscription ID must be > 0");

$active = $model->getActiveForUser($userId);
echo "Active subscriptions for User #{$userId}: " . count($active) . "\n";
assert(count($active) >= 1, "Must have at least 1 active subscription");

// Revoke
$revoked = $model->revoke($subId, $userId);
echo "Revoke status: " . ($revoked ? 'OK' : 'FAIL') . "\n";
assert($revoked, "Revocation must succeed");

// Clean up test entry
$deleted = $model->deleteSubscription($subId, $userId);
echo "Cleanup deleted: " . ($deleted ? 'OK' : 'FAIL') . "\n";
assert($deleted, "Cleanup delete must succeed");

echo "=== ALL WEBPUSH SERVICE & MODEL TESTS PASSED! ===\n";
