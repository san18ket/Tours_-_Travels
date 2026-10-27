<?php
/**
 * Test Suite: Mobile API Authentication, Driver Devices & Offline GPS Telemetry Queue
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Models\User;
use App\Models\DriverProfile;

$passed = 0;
$failed = 0;

function assertMobile(bool $cond, string $desc, ?string $detail = null): void {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "  [PASS] {$desc}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$desc}" . ($detail ? " -- {$detail}" : "") . "\n";
    }
}

echo "\n======================================================\n";
echo " TEST SUITE: Mobile Driver API & Offline GPS Pipeline\n";
echo "======================================================\n\n";

$router = require __DIR__ . '/../app/routes.php';
$pdo = Database::getConnection();

// 1. Setup a Test Chauffeur Account
$userModel = new User();
$driverModel = new DriverProfile();

$testUsername = 'mobiledriver_' . bin2hex(random_bytes(3));
$testPassword = 'DriverSecret123!';
$now = date('Y-m-d H:i:s');

$userId = $userModel->create([
    'name'       => 'Sanjay Kumar (Mobile QA)',
    'username'   => $testUsername,
    'email'      => $testUsername . '@voyagego.internal',
    'phone'      => '+91 98765 43210',
    'password'   => password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 10]),
    'role'       => 'driver',
    'status'     => 'active',
    'created_at' => $now,
    'updated_at' => $now,
]);

$driverProfileId = $driverModel->create([
    'user_id'           => $userId,
    'license_number'    => 'DL-TEST-' . bin2hex(random_bytes(4)),
    'license_expiry'    => date('Y-m-d', strtotime('+3 years')),
    'experience_years'  => 6,
    'emergency_contact' => '+91 98111 22233',
    'duty_status'       => 'on_duty',
    'rating'            => 4.95,
    'current_latitude'  => 28.5355,
    'current_longitude' => 77.3910,
    'last_gps_at'       => $now,
    'created_at'        => $now,
    'updated_at'        => $now,
]);

assertMobile($userId > 0 && $driverProfileId > 0, "1. Test chauffeur account created (Driver Profile #{$driverProfileId})");

// 2. Test Login Validation Failures
$reqEmpty = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/auth/login',
    'CONTENT_TYPE'   => 'application/json'
]);
$resEmpty = $router->dispatch($reqEmpty);
assertMobile($resEmpty->getStatusCode() === 422, "2a. Empty login credentials rejected with 422");

$reqBadPass = new Request([], [
    'login'       => $testUsername,
    'password'    => 'wrong_password_here',
    'device_uuid' => 'qa-pixel-uuid-001'
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/auth/login',
    'CONTENT_TYPE'   => 'application/json'
]);
$resBadPass = $router->dispatch($reqBadPass);
assertMobile($resBadPass->getStatusCode() === 401, "2b. Incorrect chauffeur password rejected with 401");

// 3. Test Successful Login & Device Registration
$deviceUuid = 'dev-uuid-' . bin2hex(random_bytes(6));
$reqLogin = new Request([], [
    'login'       => $testUsername,
    'password'    => $testPassword,
    'device_uuid' => $deviceUuid,
    'device_name' => 'Google Pixel 8 Pro',
    'platform'    => 'android',
    'os_version'  => 'Android 14',
    'app_version' => '1.2.0'
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/auth/login',
    'CONTENT_TYPE'   => 'application/json'
]);
$resLogin = $router->dispatch($reqLogin);
assertMobile($resLogin->getStatusCode() === 200, "3a. Mobile login succeeds with 200 OK");
$loginJson = json_decode($resLogin->getContent(), true);

$accessToken = $loginJson['auth']['access_token'] ?? null;
$refreshToken = $loginJson['auth']['refresh_token'] ?? null;

assertMobile(!empty($accessToken), "3b. Cryptographic Bearer access token issued");
assertMobile(!empty($refreshToken), "3c. Revocable refresh token issued");
assertMobile(($loginJson['driver']['id'] ?? 0) === $driverProfileId, "3d. Driver profile matches authenticated user");

// Verify driver_mobile_devices DB record
$checkDev = $pdo->prepare("SELECT * FROM `driver_mobile_devices` WHERE `device_uuid` = ? AND `driver_id` = ?");
$checkDev->execute([$deviceUuid, $driverProfileId]);
$deviceRow = $checkDev->fetch(PDO::FETCH_ASSOC);
assertMobile((bool)$deviceRow, "3e. Device registered in driver_mobile_devices table");

// 4. Test Protected Route without Token
$reqNoToken = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/api/mobile/v1/driver/me',
    'HTTP_ACCEPT'    => 'application/json'
]);
$resNoToken = $router->dispatch($reqNoToken);
assertMobile($resNoToken->getStatusCode() === 401, "4a. Protected route without Bearer token rejected with 401");

// 5. Test Protected Route with Valid Bearer Token
$reqMe = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/api/mobile/v1/driver/me',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resMe = $router->dispatch($reqMe);
assertMobile($resMe->getStatusCode() === 200, "5a. GET /api/mobile/v1/driver/me returns 200 OK with valid token");
$meJson = json_decode($resMe->getContent(), true);
assertMobile(($meJson['driver']['name'] ?? '') === 'Sanjay Kumar (Mobile QA)', "5b. Returns chauffeur profile data");

// 6. Test GET /api/mobile/v1/driver/trips
$reqTrips = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/api/mobile/v1/driver/trips',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resTrips = $router->dispatch($reqTrips);
assertMobile($resTrips->getStatusCode() === 200, "6a. GET /api/mobile/v1/driver/trips returns 200 OK");

// 7. Test Tracking Lifecycle: Start & Status
$reqStart = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/tracking/start',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resStart = $router->dispatch($reqStart);
assertMobile($resStart->getStatusCode() === 200, "7a. POST /api/mobile/v1/tracking/start returns 200 OK");
$startJson = json_decode($resStart->getContent(), true);
assertMobile(!empty($startJson['session']['session_uuid']), "7b. Tracking session created with UUID");

// 8. Test Single GPS Fix
$reqSingleGps = new Request([], [
    'latitude'        => 28.5360,
    'longitude'       => 77.3920,
    'speed_kmh'       => 35.5,
    'heading_degrees' => 140.0,
    'accuracy_m'      => 8.2,
    'battery_level'   => 88,
    'recorded_at'     => date('Y-m-d H:i:s')
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/gps',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resSingleGps = $router->dispatch($reqSingleGps);
assertMobile($resSingleGps->getStatusCode() === 200, "8a. POST /api/mobile/v1/gps single fix returns 200 OK");

// 9. Test Offline GPS Queue Batch Ingestion
$t1 = date('Y-m-d H:i:s', time() + 5);
$t2 = date('Y-m-d H:i:s', time() + 10);
$t3 = date('Y-m-d H:i:s', time() + 15);

$reqBatchGps = new Request([], [
    'locations' => [
        [
            'point_id'        => 'offline_pt_01',
            'latitude'        => 28.5365,
            'longitude'       => 77.3925,
            'speed_kmh'       => 40.0,
            'accuracy_m'      => 6.5,
            'timestamp'       => $t1
        ],
        [
            'point_id'        => 'offline_pt_02',
            'latitude'        => 28.5370,
            'longitude'       => 77.3930,
            'speed_kmh'       => 42.1,
            'accuracy_m'      => 5.8,
            'timestamp'       => $t2
        ],
        [
            'point_id'        => 'offline_pt_03',
            'latitude'        => 28.5380,
            'longitude'       => 77.3940,
            'speed_kmh'       => 38.0,
            'accuracy_m'      => 5.2,
            'timestamp'       => $t3
        ]
    ]
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/gps',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resBatchGps = $router->dispatch($reqBatchGps);
assertMobile($resBatchGps->getStatusCode() === 200, "9a. POST /api/mobile/v1/gps batch sync returns 200 OK");
$batchJson = json_decode($resBatchGps->getContent(), true);
assertMobile(($batchJson['processed_count'] ?? 0) === 3, "9b. All 3 offline queue points ingested");
assertMobile(count($batchJson['synced_ids'] ?? []) === 3, "9c. Returns exact synced IDs for local device queue clearance");

// Check driver profile reflects latest point coordinates (28.5380, 77.3940)
$updatedDrv = $driverModel->find($driverProfileId);
assertMobile(abs((float)$updatedDrv['current_latitude'] - 28.5380) < 0.0001, "9d. Driver current latitude accurately reflects latest point from queue");

// 10. Stop Tracking Session
$reqStop = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/tracking/stop',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
]);
$resStop = $router->dispatch($reqStop);
assertMobile($resStop->getStatusCode() === 200, "10a. POST /api/mobile/v1/tracking/stop completes session with 200 OK");

// 11. Refresh Token Rotation
$reqRefresh = new Request([], [
    'refresh_token' => $refreshToken
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/auth/refresh',
    'CONTENT_TYPE'   => 'application/json'
]);
$resRefresh = $router->dispatch($reqRefresh);
assertMobile($resRefresh->getStatusCode() === 200, "11a. POST /api/mobile/v1/auth/refresh returns 200 OK");
$refreshJson = json_decode($resRefresh->getContent(), true);
$newAccessToken = $refreshJson['auth']['access_token'] ?? null;
assertMobile(!empty($newAccessToken) && $newAccessToken !== $accessToken, "11b. Rotates to new Bearer token");

// 12. Logout & Token Revocation
$reqLogout = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/mobile/v1/auth/logout',
    'HTTP_ACCEPT'    => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $newAccessToken
]);
$resLogout = $router->dispatch($reqLogout);
assertMobile($resLogout->getStatusCode() === 200, "12a. POST /api/mobile/v1/auth/logout revokes credentials with 200 OK");

// 13. Verify Revoked Token is Rejected
$resAfterRevoke = $router->dispatch($reqLogout);
assertMobile($resAfterRevoke->getStatusCode() === 401, "13a. Request with revoked credentials rejected with 401");

echo "\n======================================================\n";
echo " MOBILE SUITE SUMMARY: Total: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}\n";
echo "======================================================\n";

if ($failed > 0) {
    exit(1);
}
