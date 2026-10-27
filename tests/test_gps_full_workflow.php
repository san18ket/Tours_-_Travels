<?php
/**
 * Comprehensive GPS Telemetry & Driver Location Workflow Verification Suite
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\User;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Dispatch;
use App\Models\GpsLog;
use App\Services\DispatchService;
use App\Services\BookingService;
use App\Services\GpsTrackingService;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description, ?string $details = null) {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

echo "\n======================================================\n";
echo " TEST SUITE: Real GPS Telemetry, Buttons & Subdirectory Resolution\n";
echo "======================================================\n\n";

// -----------------------------------------------------------------------------
// 1. Subdirectory URL Resolution Verification
// -----------------------------------------------------------------------------
echo "--- 1. Subdirectory URL Helper & Meta Verification ---\n";
$_SERVER['SCRIPT_NAME'] = '/voyagego/public/index.php';
$subUrl = url('/api/gps/update');
assertTest($subUrl === '/voyagego/public/api/gps/update', '1a. url() helper correctly prefixes subdirectory path', "Got: {$subUrl}");

$_SERVER['SCRIPT_NAME'] = '/index.php';
$rootUrl = url('/api/gps/update');
assertTest($rootUrl === '/api/gps/update', '1b. url() helper correctly produces root-relative URL when not in subfolder', "Got: {$rootUrl}");

// -----------------------------------------------------------------------------
// 2. Setup Test Driver, Vehicle & Dispatches
// -----------------------------------------------------------------------------
echo "\n--- 2. Setting Up Test Driver & Fleet Context ---\n";
$pdo = Database::getConnection();
$userModel = new User();
$driverModel = new DriverProfile();
$vehicleModel = new Vehicle();
$gpsLogModel = new GpsLog();
$dispatchService = new DispatchService();
$bookingService = new BookingService();
$router = require __DIR__ . '/../app/routes.php';

$testEmail = 'gps.test.driver.' . uniqid() . '@voyagego.com';
$userId = $userModel->create([
    'name'     => 'Jonathan Archer',
    'username' => 'jarcher_' . uniqid(),
    'email'    => $testEmail,
    'phone'    => '+91 98777 ' . rand(10000, 99999),
    'password' => password_hash('Chauffeur@123', PASSWORD_BCRYPT),
    'role'     => 'driver',
    'status'   => 'active',
]);

$driverProfileId = $driverModel->create([
    'user_id'          => $userId,
    'license_number'   => 'DL-MH-TEST-' . rand(100000, 999999),
    'license_expiry'   => date('Y-m-d', strtotime('+2 years')),
    'experience_years' => 4,
    'duty_status'      => 'on_duty',
    'rating'           => 4.90,
    'current_latitude' => null, // Fresh driver without previous fix
    'current_longitude'=> null,
    'last_gps_at'      => null,
]);

Session::start();
Session::set('user_id', $userId);
Session::set('user_role', 'driver');
Session::set('user', $userModel->find($userId));
$csrf = Csrf::getToken();

assertTest($driverProfileId > 0, "2a. Chauffeur profile created with ID #{$driverProfileId}");

// -----------------------------------------------------------------------------
// 3. Fresh Driver (No Coordinates) Location Console View Test
// -----------------------------------------------------------------------------
echo "\n--- 3. Location Console DOM & Initialization Order Verification ---\n";
$locReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/location']);
$locRes = $router->dispatch($locReq);
$locHtml = (string)$locRes->getBody();

assertTest($locRes->getStatusCode() === 200, '3a. /driver/location returns 200 OK for chauffeur');
assertTest(str_contains($locHtml, 'window.toggleTelemetrySharing = function'), '3b. toggleTelemetrySharing registered globally before DOM/Leaflet init');
assertTest(str_contains($locHtml, 'window.requestSingleGpsUpdate = function'), '3c. requestSingleGpsUpdate registered globally before DOM/Leaflet init');
assertTest(str_contains($locHtml, 'GPS_UPDATE_URL = "' . url('/api/gps/update') . '"') || str_contains($locHtml, 'GPS_UPDATE_URL = ' . json_encode(url('/api/gps/update'))), '3d. Page injects dynamic PHP url() for GPS_UPDATE_URL');
assertTest(str_contains($locHtml, 'renderMapOfflineNotice'), '3e. Leaflet try/catch fallback notice mechanism implemented');
assertTest(!str_contains($locHtml, '19.0760'), '3f. No fake Mumbai latitude presented for unlocated chauffeur');
assertTest(!str_contains($locHtml, '72.8777'), '3g. No fake Mumbai longitude presented for unlocated chauffeur');
assertTest(str_contains($locHtml, 'Awaiting browser GPS lock'), '3h. Visual "Awaiting browser GPS lock" notice displayed');

// -----------------------------------------------------------------------------
// 4. API GPS Update: On-Duty Standby Policy (Without Active Trip)
// -----------------------------------------------------------------------------
echo "\n--- 4. Telemetry Endpoint: On-Duty Standby Policy ---\n";
$standbyLat = 18.9401;
$standbyLng = 72.8345;
$standbyReq = new Request([], [
    'latitude'        => $standbyLat,
    'longitude'       => $standbyLng,
    'speed_kmh'       => 12.5,
    'heading_degrees' => 180,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$standbyRes = $router->dispatch($standbyReq);
assertTest($standbyRes->getStatusCode() === 200, '4a. /api/gps/update returns 200 OK while on-duty without active trip');
$standbyData = json_decode((string)$standbyRes->getBody(), true);
assertTest(($standbyData['success'] ?? false) === true, '4b. Response contains success: true');
assertTest(($standbyData['data']['policy'] ?? '') === 'on_duty_standby', '4c. Telemetry policy recognized as "on_duty_standby"');
assertTest(array_key_exists('dispatch_id', $standbyData['data']) && $standbyData['data']['dispatch_id'] === null, '4d. dispatch_id is null (does not invent false active dispatch)');

// Verify database updates
$freshProfile = $driverModel->find($driverProfileId);
assertTest(abs((float)$freshProfile['current_latitude'] - $standbyLat) < 0.0001, '4e. driver_profiles.current_latitude updated correctly');
assertTest(abs((float)$freshProfile['current_longitude'] - $standbyLng) < 0.0001, '4f. driver_profiles.current_longitude updated correctly');
assertTest(!empty($freshProfile['last_gps_at']), '4g. driver_profiles.last_gps_at updated with current timestamp');

$latestLog = $gpsLogModel->getLatestForDriver($driverProfileId, 1);
assertTest(!empty($latestLog), '4h. gps_logs table logged breadcrumb');
assertTest($latestLog[0]['dispatch_id'] === null, '4i. gps_logs table record has dispatch_id = NULL for standby fix');
assertTest(abs((float)$latestLog[0]['latitude'] - $standbyLat) < 0.0001, '4j. gps_logs latitude matches payload');

// -----------------------------------------------------------------------------
// 5. API GPS Update: Active Trip Policy (Assigned Trip in Progress)
// -----------------------------------------------------------------------------
echo "\n--- 5. Telemetry Endpoint: Active Trip Policy ---\n";
$vehId = $vehicleModel->create([
    'vehicle_number'      => 'MH-03-TR-' . rand(1000, 9999),
    'make_model'          => 'Toyota Innova Crysta',
    'vehicle_type'        => 'suv',
    'seating_capacity'    => 6,
    'fuel_type'           => 'diesel',
    'status'              => 'available',
    'odometer_km'         => 34200,
    'registration_expiry' => date('Y-m-d', strtotime('+1 year')),
    'insurance_expiry'    => date('Y-m-d', strtotime('+1 year')),
]);

$existingCust = $pdo->query("SELECT id FROM `customers` ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$testCustId = $existingCust ? (int)$existingCust['id'] : (int)(new \App\Models\Customer())->create([
    'customer_code' => 'VG-CUST-GPS-' . substr(uniqid(), 0, 8),
    'first_name'    => 'GPS Test',
    'last_name'     => 'Passenger',
    'phone'         => '+9198222' . rand(10000, 99999),
    'email'         => 'gpstest' . uniqid() . '@example.com',
    'status'        => 'active'
]);

$booking = $bookingService->createBooking([
    'customer_profile_id' => $testCustId,
    'pickup_address'      => 'Marine Drive, Mumbai',
    'drop_address'        => 'Chhatrapati Shivaji Maharaj Terminus',
    'start_date'          => date('Y-m-d'),
    'end_date'            => date('Y-m-d'),
    'passenger_count'     => 3,
    'custom_subtotal'     => 2200.0,
    'idempotency_token'   => bin2hex(random_bytes(16)),
]);

$dispatch = $dispatchService->assignBooking((int)$booking['id'], $vehId, $driverProfileId, 1);
assertTest(!empty($dispatch['id']), "5a. Trip assigned with Dispatch #{$dispatch['id']}");

$tripLat = 18.9512;
$tripLng = 72.8290;
$tripReq = new Request([], [
    'latitude'        => $tripLat,
    'longitude'       => $tripLng,
    'speed_kmh'       => 35.0,
    'heading_degrees' => 90,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$tripRes = $router->dispatch($tripReq);
assertTest($tripRes->getStatusCode() === 200, '5b. /api/gps/update returns 200 OK during active trip assignment');
$tripData = json_decode((string)$tripRes->getBody(), true);
assertTest(($tripData['data']['policy'] ?? '') === 'active_trip', '5c. Telemetry policy recognized as "active_trip"');
assertTest((int)($tripData['data']['dispatch_id'] ?? 0) === (int)$dispatch['id'], '5d. dispatch_id linked accurately to active dispatch');

$tripLog = $gpsLogModel->getLatestForDriver($driverProfileId, 1);
assertTest((int)$tripLog[0]['dispatch_id'] === (int)$dispatch['id'], '5e. gps_logs table logs active dispatch_id');

// -----------------------------------------------------------------------------
// 6. Chauffeur Privacy Enforcement: Off-Duty Rejection
// -----------------------------------------------------------------------------
echo "\n--- 6. Chauffeur Privacy Enforcement (Off-Duty Rejection) ---\n";
// Cancel/complete dispatch so driver can go off duty
$pdo->exec("UPDATE `dispatches` SET `status` = 'completed' WHERE `id` = {$dispatch['id']}");
$driverModel->update($driverProfileId, ['duty_status' => 'off_duty']);

$offDutyReq = new Request([], [
    'latitude'  => 18.9600,
    'longitude' => 72.8200,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$offDutyRes = $router->dispatch($offDutyReq);
assertTest($offDutyRes->getStatusCode() === 422, '6a. Location transmission while off-duty firmly rejected with 422 Unprocessable Entity');
$offDutyData = json_decode((string)$offDutyRes->getBody(), true);
assertTest(str_contains(strtolower($offDutyData['error'] ?? ''), 'off duty'), '6b. Clear privacy error message explains chauffeur is off-duty');

// Verify coordinates were NOT overwritten while off-duty
$unmodifiedProfile = $driverModel->find($driverProfileId);
assertTest(abs((float)$unmodifiedProfile['current_latitude'] - $tripLat) < 0.0001, '6c. Off-duty coordinates were NOT saved (privacy maintained)');

// Check location view displays the off-duty alert & toggle action
$offDutyViewReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/location']);
$offDutyViewRes = $router->dispatch($offDutyViewReq);
$offDutyViewHtml = (string)$offDutyViewRes->getBody();
assertTest(str_contains($offDutyViewHtml, 'offDutyWarningCard'), '6d. Dedicated off-duty privacy card rendered');
assertTest(str_contains($offDutyViewHtml, '/driver/duty/toggle'), '6e. Direct action to switch to On Duty provided');

// -----------------------------------------------------------------------------
// 7. Security & Parameter Validation
// -----------------------------------------------------------------------------
echo "\n--- 7. Security Controls & Coordinate Boundary Tests ---\n";
$driverModel->update($driverProfileId, ['duty_status' => 'on_duty']);

// Tampered CSRF
$badCsrfReq = new Request([], [
    'latitude'  => 18.9400,
    'longitude' => 72.8300,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => 'forged_csrf_token_123',
]);
$badCsrfRes = $router->dispatch($badCsrfReq);
assertTest($badCsrfRes->getStatusCode() === 403, '7a. Invalid/forged CSRF token rejected with 403 Forbidden');

// Missing Coordinates
$missingCoordsReq = new Request([], [
    'speed_kmh' => 20.0,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$missingCoordsRes = $router->dispatch($missingCoordsReq);
assertTest($missingCoordsRes->getStatusCode() === 422, '7b. Missing coordinates rejected with 422');

// Null Island (0, 0)
$nullIslandReq = new Request([], [
    'latitude'  => 0.0,
    'longitude' => 0.0,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$nullIslandRes = $router->dispatch($nullIslandReq);
assertTest($nullIslandRes->getStatusCode() === 422, '7c. Null Island (0, 0) rejected with 422');

// Out of bounds coordinates (Latitude > 90)
$outOfBoundsReq = new Request([], [
    'latitude'  => 99.5000,
    'longitude' => 72.8300,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$outOfBoundsRes = $router->dispatch($outOfBoundsReq);
assertTest($outOfBoundsRes->getStatusCode() === 422, '7d. Out of bounds latitude (> 90) rejected with 422');

// -----------------------------------------------------------------------------
// 8. Office Fleet Tracker Real-Time Synchronization Check
// -----------------------------------------------------------------------------
echo "\n--- 8. Central Fleet Dispatch Tracker (/api/fleet/live) ---\n";
$adminUser = $userModel->findBy('role', 'admin');
Session::set('user_id', $adminUser['id']);
Session::set('user_role', 'admin');
Session::set('user', $adminUser);

$fleetReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/fleet/live']);
$fleetRes = $router->dispatch($fleetReq);
assertTest($fleetRes->getStatusCode() === 200, '8a. Office live fleet tracker returns 200 OK');
$fleetData = json_decode((string)$fleetRes->getBody(), true);
assertTest(!empty($fleetData['fleet']), '8b. Live fleet array populated');

$foundChauffeur = false;
foreach ($fleetData['fleet'] as $f) {
    if ((int)$f['driver_id'] === $driverProfileId) {
        $foundChauffeur = true;
        assertTest(abs((float)$f['current_latitude'] - $tripLat) < 0.001, '8c. Chauffeur latest coordinates visible in office dispatch tracker');
        break;
    }
}
assertTest($foundChauffeur, "8d. Driver profile #{$driverProfileId} actively tracked in central fleet display");

// -----------------------------------------------------------------------------
// 9. Accuracy Persistence & Telemetry Accuracy Handling
// -----------------------------------------------------------------------------
echo "\n--- 9. Telemetry Accuracy Persistence & Validation ---\n";
// Re-authenticate as the test driver and set to on_duty
Session::set('user_id', $userId);
Session::set('user_role', 'driver');
Session::set('user', $userModel->find($userId));
$driverModel->update($driverProfileId, ['duty_status' => 'on_duty']);

$accReq = new Request([], [
    'latitude'  => 18.5204,
    'longitude' => 73.8567,
    'accuracy'  => 14.8,
    'speed_kmh' => 25.5,
], [
    'REQUEST_METHOD'    => 'POST',
    'REQUEST_URI'       => '/api/gps/update',
    'CONTENT_TYPE'      => 'application/json',
    'HTTP_X_CSRF_TOKEN' => $csrf,
]);
$accRes = $router->dispatch($accReq);
assertTest($accRes->getStatusCode() === 200, '9a. /api/gps/update accepts accuracy parameter');
$accData = json_decode((string)$accRes->getBody(), true);
assertTest(($accData['data']['accuracy'] ?? null) == 14.8, '9b. API returns accuracy in payload', "Got: " . ($accData['data']['accuracy'] ?? 'null'));

$latestLog = $gpsLogModel->getLatestForDriver($driverProfileId, 1);
assertTest(!empty($latestLog), '9c. Latest GPS log record retrieved');
assertTest(isset($latestLog[0]['accuracy_m']) && abs((float)$latestLog[0]['accuracy_m'] - 14.8) < 0.01, '9d. accuracy_m column saved 14.80 in database', "Got: " . ($latestLog[0]['accuracy_m'] ?? 'null'));

// -----------------------------------------------------------------------------
// 10. CSP, Local Leaflet Assets & PWA Verification
// -----------------------------------------------------------------------------
echo "\n--- 10. CSP, Local Leaflet & PWA Files Verification ---\n";
$locHtml = (string)$router->dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/driver/location']))->getBody();
assertTest(str_contains($locHtml, 'leaflet/leaflet.css'), '10a. Location view loads Leaflet CSS locally from assets');
assertTest(str_contains($locHtml, 'leaflet/leaflet.js'), '10b. Location view loads Leaflet JS locally from assets');
assertTest(str_contains($locHtml, 'L.Icon.Default.imagePath'), '10c. Leaflet marker asset image path configured locally');
assertTest(file_exists(__DIR__ . '/../public/assets/leaflet/leaflet.css'), '10d. Local leaflet.css exists in public/assets/leaflet');
assertTest(file_exists(__DIR__ . '/../public/assets/leaflet/leaflet.js'), '10e. Local leaflet.js exists in public/assets/leaflet');
assertTest(file_exists(__DIR__ . '/../public/assets/leaflet/images/marker-icon.png'), '10f. Local marker-icon.png exists');
assertTest(file_exists(__DIR__ . '/../public/sw.js'), '10g. Service worker sw.js exists in public/');
assertTest(file_exists(__DIR__ . '/../public/manifest.json'), '10h. manifest.json exists in public/');
assertTest(file_exists(__DIR__ . '/../public/assets/icons/icon-192.png'), '10i. icon-192.png exists in public/assets/icons');
assertTest(file_exists(__DIR__ . '/../public/assets/icons/icon-512.png'), '10j. icon-512.png exists in public/assets/icons');

$secConfig = require __DIR__ . '/../config/security.php';
$csp = $secConfig['headers']['Content-Security-Policy'] ?? '';
assertTest(str_contains($csp, 'tile.openstreetmap.org'), '10k. CSP allows OpenStreetMap tile servers in img-src');
assertTest(!str_contains($csp, "'unsafe-eval'"), '10l. CSP does NOT contain unsafe-eval');

echo "\n======================================================\n";
echo " TEST SUMMARY: Total: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}\n";
echo "======================================================\n\n";

exit($failed > 0 ? 1 : 0);
