<?php
declare(strict_types=1);

/**
 * Test Suite: Complete Professional Driver Trip Lifecycle, Vehicle Inspection, GPS Tracking & Admin Monitoring
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Dispatch;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\TripEvidence;
use App\Services\DispatchService;
use App\Services\TripEvidenceService;
use App\Controllers\DispatchController;
use App\Controllers\DriverPortalController;
use App\Controllers\MobileApiController;
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

echo "=== TRIP LIFECYCLE, EVIDENCE & TELEMETRY TEST SUITE ===\n\n";

// -----------------------------------------------------------------------------
// 1. Verify Database Schema: trip_evidence table
// -----------------------------------------------------------------------------
echo "1. Verifying Database Schema for trip_evidence...\n";
$stmt = $pdo->query("SHOW TABLES LIKE 'trip_evidence'");
$tableExists = (bool)$stmt->fetchColumn();
assertCondition($tableExists, "Table `trip_evidence` exists in database", $failures, $successCount);

$stmt = $pdo->query("DESCRIBE `trip_evidence`");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
$requiredCols = ['id', 'dispatch_id', 'driver_id', 'evidence_type', 'file_path', 'original_filename', 'mime_type', 'file_size', 'odometer_reading', 'captured_at', 'uploaded_at', 'latitude', 'longitude', 'notes', 'created_at', 'updated_at'];
foreach ($requiredCols as $col) {
    assertCondition(in_array($col, $cols, true), "Column `{$col}` exists in `trip_evidence`", $failures, $successCount);
}

// -----------------------------------------------------------------------------
// 2. Setup Test Fixtures (Customer, Vehicle, Driver, Booking, Dispatch)
// -----------------------------------------------------------------------------
echo "\n2. Setting up Test Fixtures...\n";
// Track IDs for clean teardown
$cleanupDispatchIds = [];
$cleanupBookingIds = [];
$cleanupVehicleIds = [];
$cleanupDriverIds = [];
$cleanupUserIds = [];

// Create test vehicle
$vehicleModel = new Vehicle();
$vehicleId = (int)$vehicleModel->create([
    'vehicle_number'   => 'MH-12-TEST-' . rand(1000, 9999),
    'make_model'       => 'Toyota Innova Crysta',
    'vehicle_type'     => 'suv',
    'seating_capacity' => 6,
    'fuel_type'        => 'diesel',
    'status'           => 'available',
    'odometer_km'      => 45000,
]);
$cleanupVehicleIds[] = $vehicleId;
assertCondition($vehicleId > 0, "Test vehicle created (ID: {$vehicleId}, Odo: 45,000 km)", $failures, $successCount);

// Create test user for driver
$testDriverEmail = 'driver_test_' . uniqid() . '@example.com';
$testDriverUsername = 'driver_' . uniqid();
$stmt = $pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, 'hash', 'driver', 'active', NOW(), NOW())");
$stmt->execute(['Test Driver Chauffeur', $testDriverUsername, $testDriverEmail]);
$driverUserId = (int)$pdo->lastInsertId();
$cleanupUserIds[] = $driverUserId;

$driverModel = new DriverProfile();
$driverProfileId = (int)$driverModel->create([
    'user_id'          => $driverUserId,
    'license_number'   => 'DL-TEST-' . rand(1000, 9999),
    'license_expiry'   => date('Y-m-d', strtotime('+3 years')),
    'experience_years' => 5,
    'duty_status'      => 'on_duty',
    'rating'           => 5.0,
]);
$cleanupDriverIds[] = $driverProfileId;
assertCondition($driverProfileId > 0, "Test driver profile created (ID: {$driverProfileId})", $failures, $successCount);

// Another driver for IDOR / authorization mismatch tests
$otherUsername = 'other_' . uniqid();
$stmt->execute(['Other Driver', $otherUsername, 'other_' . uniqid() . '@example.com']);
$otherUserId = (int)$pdo->lastInsertId();
$cleanupUserIds[] = $otherUserId;

$otherDriverId = (int)$driverModel->create([
    'user_id'          => $otherUserId,
    'license_number'   => 'DL-OTHER-' . rand(1000, 9999),
    'license_expiry'   => date('Y-m-d', strtotime('+2 years')),
    'experience_years' => 2,
    'duty_status'      => 'on_duty',
]);
$cleanupDriverIds[] = $otherDriverId;

// Create test booking
$bookingModel = new Booking();
$bookingCode = 'BK-TEST-' . date('ymd') . '-' . rand(100, 999);
$bookingId = (int)$bookingModel->create([
    'booking_code'    => $bookingCode,
    'customer_name'   => 'Corporate VIP Guest',
    'customer_phone'  => '9876543210',
    'pickup_location' => 'Pune Airport (PNQ)',
    'drop_location'   => 'JW Marriott Senapati Bapat Road',
    'start_date'      => date('Y-m-d'),
    'pickup_time'     => '14:30:00',
    'passenger_count' => 2,
    'status'          => 'confirmed',
    'created_at'      => date('Y-m-d H:i:s'),
    'updated_at'      => date('Y-m-d H:i:s'),
]);
$cleanupBookingIds[] = $bookingId;
assertCondition($bookingId > 0, "Test booking created (Code: {$bookingCode})", $failures, $successCount);

// Create test dispatch assignment
$dispatchService = new DispatchService();
$dispatch = $dispatchService->assignBooking($bookingId, $vehicleId, $driverProfileId, 1, 1500.00);
$dispatchId = (int)$dispatch['id'];
$cleanupDispatchIds[] = $dispatchId;
assertCondition($dispatchId > 0, "Dispatch assignment created (ID: {$dispatchId}, Status: assigned)", $failures, $successCount);

// -----------------------------------------------------------------------------
// 3. Driver Review & Acceptance (Must NOT Start Trip)
// -----------------------------------------------------------------------------
echo "\n3. Testing Driver Acceptance Workflow...\n";
// IDOR check: other driver cannot accept
$declinedUnauthorized = false;
try {
    $dispatchService->driverAccept($dispatchId, $otherDriverId);
} catch (\Throwable $e) {
    $declinedUnauthorized = true;
}
assertCondition($declinedUnauthorized, "Unauthorized driver cannot accept another driver's trip assignment", $failures, $successCount);

// Valid driver accepts
$acceptResult = $dispatchService->driverAccept($dispatchId, $driverProfileId);
assertCondition($acceptResult === true, "Authorized driver accepts trip assignment", $failures, $successCount);

$dispatchModel = new Dispatch();
$dFresh = $dispatchModel->find($dispatchId);
assertCondition($dFresh['status'] === 'driver_accepted', "Dispatch status transitions to 'driver_accepted'", $failures, $successCount);
assertCondition(empty($dFresh['start_time']), "Acceptance does NOT set start_time (start_time is null)", $failures, $successCount);
assertCondition(empty($dFresh['start_odometer']), "Acceptance does NOT set start_odometer", $failures, $successCount);

$bFresh = $bookingModel->find($bookingId);
assertCondition($bFresh['status'] === 'assigned', "Booking remains 'assigned', NOT 'in_progress'", $failures, $successCount);

// -----------------------------------------------------------------------------
// 4. Pre-Trip Vehicle Inspection & Start Odometer Validation
// -----------------------------------------------------------------------------
echo "\n4. Testing Pre-Trip Vehicle Inspection & Start Validation...\n";

// Test A: Start odometer < vehicle current odometer (45,000 km) -> Must Fail
$odoTooLowFailed = false;
try {
    $dispatchService->startTrip($dispatchId, $driverProfileId, 44999);
} catch (\RuntimeException $e) {
    $odoTooLowFailed = true;
    echo "   [Caught Expected Exception]: " . $e->getMessage() . "\n";
}
assertCondition($odoTooLowFailed, "Start odometer lower than vehicle current reading (44,999 < 45,000) is rejected", $failures, $successCount);

// Test B: Invalid evidence MIME type rejection
$evidenceService = new TripEvidenceService();
$invalidMimeFailed = false;
try {
    $evidenceService->storeEvidence(
        $dispatchId,
        $driverProfileId,
        'start_vehicle',
        'data:text/plain;base64,' . base64_encode('malicious executable code')
    );
} catch (\InvalidArgumentException $e) {
    $invalidMimeFailed = true;
    echo "   [Caught Expected Exception]: " . $e->getMessage() . "\n";
}
assertCondition($invalidMimeFailed, "Disallowed MIME type in evidence upload is strictly rejected", $failures, $successCount);

// Test C: Valid Base64 inspection image upload
$test1pxJpeg = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
$startVehEvidence = $evidenceService->storeEvidence(
    $dispatchId,
    $driverProfileId,
    'start_vehicle',
    $test1pxJpeg,
    45010,
    18.5204,
    73.8567,
    'Vehicle clean, no visible exterior scratches.'
);
assertCondition(!empty($startVehEvidence['id']), "Valid pre-trip vehicle condition photo stored (ID: {$startVehEvidence['id']})", $failures, $successCount);
assertCondition(file_exists(dirname(__DIR__) . '/public/storage/' . $startVehEvidence['file_path']), "Evidence image physically saved on disk in public/storage", $failures, $successCount);

// Test D: Start meter photo
$startMeterEvidence = $evidenceService->storeEvidence(
    $dispatchId,
    $driverProfileId,
    'start_odometer',
    $test1pxJpeg,
    45010,
    18.5204,
    73.8567,
    'Odometer snapshot: 45,010 km'
);
assertCondition(!empty($startMeterEvidence['id']), "Valid pre-trip meter reading photo stored (ID: {$startMeterEvidence['id']})", $failures, $successCount);

// -----------------------------------------------------------------------------
// 5. Driver Commences Journey (Start Trip) & GPS Auto-Activation
// -----------------------------------------------------------------------------
echo "\n5. Testing Start Trip & GPS Telemetry Auto-Activation...\n";
$startSuccess = $dispatchService->startTrip($dispatchId, $driverProfileId, 45010, 'Passenger on board, departure on schedule');
assertCondition($startSuccess === true, "Trip successfully started at 45,010 km", $failures, $successCount);

$dStarted = $dispatchModel->find($dispatchId);
assertCondition($dStarted['status'] === 'trip_started', "Dispatch status is 'trip_started'", $failures, $successCount);
assertCondition((int)$dStarted['start_odometer'] === 45010, "Authoritative start_odometer recorded as 45,010 km", $failures, $successCount);
assertCondition(!empty($dStarted['start_time']), "Authoritative server start_time recorded: " . $dStarted['start_time'], $failures, $successCount);

$vStarted = $vehicleModel->find($vehicleId);
assertCondition($vStarted['status'] === 'on_trip', "Vehicle status changed to 'on_trip'", $failures, $successCount);

$drStarted = $driverModel->find($driverProfileId);
assertCondition($drStarted['duty_status'] === 'on_trip', "Driver duty_status changed to 'on_trip'", $failures, $successCount);

$bStarted = $bookingModel->find($bookingId);
assertCondition($bStarted['status'] === 'in_progress', "Booking status changed to 'in_progress'", $failures, $successCount);

// Verify tracking session auto-created
$stmt = $pdo->prepare("SELECT * FROM `tracking_sessions` WHERE `dispatch_id` = ? AND `status` = 'active'");
$stmt->execute([$dispatchId]);
$activeSession = $stmt->fetch(PDO::FETCH_ASSOC);
assertCondition((bool)$activeSession, "Active tracking session automatically activated for dispatch", $failures, $successCount);
assertCondition(!empty($activeSession['session_uuid']), "Tracking session has valid session_uuid: " . ($activeSession['session_uuid'] ?? ''), $failures, $successCount);

// Log GPS breadcrumb points
$stmtGps = $pdo->prepare("INSERT INTO `gps_logs` (`dispatch_id`, `driver_id`, `latitude`, `longitude`, `speed_kmh`, `recorded_at`, `created_at`) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
$stmtGps->execute([$dispatchId, $driverProfileId, 18.52043, 73.85674, 35.5]);
$stmtGps->execute([$dispatchId, $driverProfileId, 18.53050, 73.86010, 42.0]);
$stmtGps->execute([$dispatchId, $driverProfileId, 18.54120, 73.87250, 28.0]);
echo "   Logged 3 telemetry GPS waypoints for dispatch.\n";

// -----------------------------------------------------------------------------
// 6. Post-Trip Inspection & End Odometer Validation
// -----------------------------------------------------------------------------
echo "\n6. Testing Post-Trip Inspection & Trip Completion Validation...\n";

// Test A: End odometer < start odometer (45,005 < 45,010) -> Must Fail
$endOdoTooLowFailed = false;
try {
    $dispatchService->completeTrip($dispatchId, $driverProfileId, 45005);
} catch (\RuntimeException $e) {
    $endOdoTooLowFailed = true;
    echo "   [Caught Expected Exception]: " . $e->getMessage() . "\n";
}
assertCondition($endOdoTooLowFailed, "End odometer lower than start reading (45,005 < 45,010) is rejected", $failures, $successCount);

// Test B: IDOR protection: unauthorized driver cannot complete trip
$declinedOtherComplete = false;
try {
    $dispatchService->completeTrip($dispatchId, $otherDriverId, 45040);
} catch (\Throwable $e) {
    $declinedOtherComplete = true;
}
assertCondition($declinedOtherComplete, "Unauthorized driver cannot complete another driver's trip", $failures, $successCount);

// Test C: Store post-trip evidence (End Odometer & Returned Vehicle)
$endOdoEvidence = $evidenceService->storeEvidence(
    $dispatchId,
    $driverProfileId,
    'end_odometer',
    $test1pxJpeg,
    45040,
    18.5412,
    73.8725,
    'Final meter reading at JW Marriott: 45,040 km'
);
assertCondition(!empty($endOdoEvidence['id']), "Post-trip meter photo saved (ID: {$endOdoEvidence['id']})", $failures, $successCount);

$endVehEvidence = $evidenceService->storeEvidence(
    $dispatchId,
    $driverProfileId,
    'end_vehicle',
    $test1pxJpeg,
    45040,
    18.5412,
    73.8725,
    'Returned vehicle condition inspected: clean, no damage.'
);
assertCondition(!empty($endVehEvidence['id']), "Post-trip vehicle condition photo saved (ID: {$endVehEvidence['id']})", $failures, $successCount);

// Test D: Valid Trip Completion (End Odo = 45,040 km, Distance = 30 km)
$completeSuccess = $dispatchService->completeTrip($dispatchId, $driverProfileId, 45040, 'Passenger dropped at JW Marriott lobby. Toll paid.');
assertCondition($completeSuccess === true, "Trip successfully completed at 45,040 km", $failures, $successCount);

$dCompleted = $dispatchModel->find($dispatchId);
assertCondition($dCompleted['status'] === 'completed', "Dispatch status is 'completed'", $failures, $successCount);
assertCondition((int)$dCompleted['end_odometer'] === 45040, "Authoritative end_odometer recorded as 45,040 km", $failures, $successCount);
assertCondition(!empty($dCompleted['end_time']), "Authoritative end_time recorded: " . $dCompleted['end_time'], $failures, $successCount);

// Verify Vehicle updated and released
$vCompleted = $vehicleModel->find($vehicleId);
assertCondition($vCompleted['status'] === 'available', "Vehicle released back to 'available'", $failures, $successCount);
assertCondition((int)$vCompleted['odometer_km'] === 45040, "Vehicle odometer updated to 45,040 km", $failures, $successCount);

// Verify Driver released
$drCompleted = $driverModel->find($driverProfileId);
assertCondition($drCompleted['duty_status'] === 'on_duty', "Driver released back to 'on_duty'", $failures, $successCount);

// Verify Booking completed
$bCompleted = $bookingModel->find($bookingId);
assertCondition($bCompleted['status'] === 'completed', "Booking status updated to 'completed'", $failures, $successCount);

// Verify GPS tracking session auto-closed
$stmt = $pdo->prepare("SELECT * FROM `tracking_sessions` WHERE `id` = ?");
$stmt->execute([$activeSession['id']]);
$closedSession = $stmt->fetch(PDO::FETCH_ASSOC);
assertCondition($closedSession['status'] === 'completed', "Tracking session status set to 'completed'", $failures, $successCount);
assertCondition(!empty($closedSession['ended_at']), "Tracking session ended_at recorded: " . ($closedSession['ended_at'] ?? ''), $failures, $successCount);

// -----------------------------------------------------------------------------
// 7. Grouped Evidence & URL Formatting Verification
// -----------------------------------------------------------------------------
echo "\n7. Testing Grouped Evidence Output...\n";
$grouped = $evidenceService->getGroupedEvidence($dispatchId);
assertCondition(!empty($grouped['start_odometer']), "Grouped evidence contains 'start_odometer'", $failures, $successCount);
assertCondition(!empty($grouped['start_vehicle']), "Grouped evidence contains 'start_vehicle'", $failures, $successCount);
assertCondition(!empty($grouped['end_odometer']), "Grouped evidence contains 'end_odometer'", $failures, $successCount);
assertCondition(!empty($grouped['end_vehicle']), "Grouped evidence contains 'end_vehicle'", $failures, $successCount);
assertCondition(count($grouped['all']) === 4, "Total 4 inspection photos recorded for dispatch", $failures, $successCount);
assertCondition(str_starts_with($grouped['start_odometer']['url'], 'http') || str_starts_with($grouped['start_odometer']['url'], '/'), "Evidence URL is properly web-formatted: " . $grouped['start_odometer']['url'], $failures, $successCount);

// -----------------------------------------------------------------------------
// 8. Office Admin Trip Evidence & Telemetry Endpoint
// -----------------------------------------------------------------------------
echo "\n8. Testing Office Admin Inspection Evidence Endpoint...\n";
$dispatchController = new DispatchController();

// 8A. Web Page HTML View (even under VoyageAjax X-Requested-With)
$webReq = new Request([], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);
$webRes = $dispatchController->tripEvidence($webReq, ['id' => $dispatchId]);
assertCondition($webRes->getStatusCode() === 200, "Office tripEvidence Web view returns HTTP 200", $failures, $successCount);
assertCondition(str_contains($webRes->getContent(), 'Trip Inspection &amp; Telemetry Evidence'), "Office tripEvidence Web view renders HTML content", $failures, $successCount);

// 8B. Explicit JSON API View (format=json)
$req = new Request(['format' => 'json'], [], [], [], ['HTTP_ACCEPT' => 'application/json']);
$evidenceResponse = $dispatchController->tripEvidence($req, ['id' => $dispatchId]);
assertCondition($evidenceResponse->getStatusCode() === 200, "Office tripEvidence JSON returns HTTP 200", $failures, $successCount);

$jsonBody = json_decode($evidenceResponse->getContent(), true);
assertCondition(($jsonBody['success'] ?? false) === true, "Office tripEvidence JSON success is true", $failures, $successCount);
assertCondition(!empty($jsonBody['evidence']['start_odometer']), "Office JSON includes start_odometer evidence", $failures, $successCount);
assertCondition(!empty($jsonBody['evidence']['end_odometer']), "Office JSON includes end_odometer evidence", $failures, $successCount);
assertCondition(count($jsonBody['gps_trail']) === 3, "Office JSON includes 3 GPS waypoints in trail", $failures, $successCount);

// -----------------------------------------------------------------------------
// 9. Mobile API Trip Details & Evidence Inclusion
// -----------------------------------------------------------------------------
echo "\n9. Testing Mobile API v1 Trip Details & Evidence...\n";
$mobileController = new MobileApiController();
$mReq = new Request([], [], [], [], []);
$mReq->setAttribute('auth_driver_id', $driverProfileId);
$mTripRes = $mobileController->trip($mReq, ['id' => $dispatchId]);
assertCondition($mTripRes->getStatusCode() === 200, "Mobile trip endpoint returns HTTP 200", $failures, $successCount);
$mTripData = json_decode($mTripRes->getContent(), true);
assertCondition(($mTripData['success'] ?? false) === true, "Mobile trip response success is true", $failures, $successCount);
assertCondition(!empty($mTripData['trip']['evidence']['start_odometer']), "Mobile trip response includes inspection evidence gallery", $failures, $successCount);

// -----------------------------------------------------------------------------
// Clean up test records
// -----------------------------------------------------------------------------
foreach ($cleanupDispatchIds as $id) {
    $pdo->prepare("DELETE FROM `gps_logs` WHERE `dispatch_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `tracking_sessions` WHERE `dispatch_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `trip_evidence` WHERE `dispatch_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `trip_status_history` WHERE `dispatch_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `driver_trip_payments` WHERE `dispatch_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$id]);
}
foreach ($cleanupBookingIds as $id) {
    $pdo->prepare("DELETE FROM `bookings` WHERE `id` = ?")->execute([$id]);
}
foreach ($cleanupDriverIds as $id) {
    $pdo->prepare("DELETE FROM `tracking_sessions` WHERE `driver_id` = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$id]);
}
foreach ($cleanupUserIds as $id) {
    $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$id]);
}
foreach ($cleanupVehicleIds as $id) {
    $pdo->prepare("DELETE FROM `vehicles` WHERE `id` = ?")->execute([$id]);
}

// Remove test files created in public/storage
foreach ([$startVehEvidence, $startMeterEvidence, $endOdoEvidence, $endVehEvidence] as $ev) {
    if (!empty($ev['file_path'])) {
        $p = dirname(__DIR__) . '/public/storage/' . $ev['file_path'];
        if (file_exists($p)) {
            @unlink($p);
        }
    }
}

echo "\n============================================\n";
echo "LIFECYCLE TEST RESULTS: {$successCount} PASSED, " . count($failures) . " FAILED\n";
echo "============================================\n";

if (!empty($failures)) {
    echo "\nFailures encountered:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

exit(0);
