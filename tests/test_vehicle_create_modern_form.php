<?php
/**
 * Test Suite: Modern Vehicles Create Form Verification
 * Tests the 4-step wizard form, removal of Classification Type,
 * file upload handling, and automatic classification derivation.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Models\Vehicle;
use App\Models\AuditLog;
use App\Controllers\VehicleController;
use App\Core\Request;

Session::start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$pdo = Database::getConnection();
$vehicleModel = new Vehicle();

$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $message, &$passed, &$failed): void
{
    if ($cond) {
        echo "[PASS] {$message}\n";
        $passed++;
    } else {
        echo "[FAIL] {$message}\n";
        $failed++;
    }
}

echo "=== STARTING VEHICLE CREATE MODERN FORM TESTS ===\n\n";

// ---------------------------------------------------------
// 1. Test GET /office/vehicles/create rendering
// ---------------------------------------------------------
echo "--- Testing Form View & Structure ---\n";

ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/vehicles/create';
$controller = new VehicleController();
$response = $controller->create(new Request());
$renderedHtml = ob_get_clean();

// Check if content is in response or buffer
$content = $response instanceof \App\Core\Response ? $response->getContent() : (!empty($renderedHtml) ? $renderedHtml : (string)$response);

// 1.1 Check 4 Stepper steps
assertCondition(strpos($content, 'Vehicle Specifications') !== false, "Step 1 'Vehicle Specifications' exists in stepper", $passed, $failed);
assertCondition(strpos($content, 'Operations &amp; Odometer') !== false || strpos($content, 'Operations & Odometer') !== false, "Step 2 'Operations & Odometer' exists in stepper", $passed, $failed);
assertCondition(strpos($content, 'Documents &amp; Compliance') !== false || strpos($content, 'Documents & Compliance') !== false, "Step 3 'Documents & Compliance' exists in stepper", $passed, $failed);
assertCondition(strpos($content, 'Review &amp; Save') !== false || strpos($content, 'Review & Save') !== false, "Step 4 'Review & Save' exists in stepper", $passed, $failed);

// 1.2 Verify Classification Type / vehicle_type is COMPLETELY ABSENT from form
$hasClassificationTypeLabel = stripos($content, 'Classification Type') !== false || stripos($content, 'Vehicle Classification') !== false;
assertCondition(!$hasClassificationTypeLabel, "Label 'Classification Type' or 'Vehicle Classification' is NOT present", $passed, $failed);

$hasVehicleTypeSelect = preg_match('/<select[^>]*name=["\']vehicle_type["\']/i', $content);
assertCondition(!$hasVehicleTypeSelect, "Select element with name='vehicle_type' is NOT present in form", $passed, $failed);

// 1.3 Verify top navigation bar (back btn and admin panel btn) was removed
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $content), "Top bar back button HTML element was removed", $passed, $failed);
assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $content), "Top bar admin panel button HTML element was removed", $passed, $failed);

// 1.4 Verify required vehicle specification fields
assertCondition(strpos($content, 'id="vehicle_number"') !== false, "Field 'vehicle_number' is present", $passed, $failed);
assertCondition(strpos($content, 'id="make_model"') !== false, "Field 'make_model' is present", $passed, $failed);
assertCondition(strpos($content, 'id="seating_capacity"') !== false, "Field 'seating_capacity' is present", $passed, $failed);
assertCondition(strpos($content, 'id="fuel_type"') !== false, "Field 'fuel_type' is present", $passed, $failed);

// 1.5 Verify photo upload component
assertCondition(strpos($content, 'id="vehicle_photo"') !== false, "Photo file input 'vehicle_photo' is present", $passed, $failed);
assertCondition(strpos($content, 'id="vehicle_photo_base64"') !== false, "Hidden input 'vehicle_photo_base64' is present", $passed, $failed);
assertCondition(strpos($content, 'avatar-camera-badge') !== false, "Photo camera badge is present", $passed, $failed);

// 1.6 Verify document dropzone & inputs
assertCondition(strpos($content, 'id="rc_document_file"') !== false, "Document file input 'rc_document_file' is present", $passed, $failed);
assertCondition(strpos($content, 'id="rc_document_file_base64"') !== false, "Hidden input 'rc_document_file_base64' is present", $passed, $failed);
assertCondition(strpos($content, 'id="documentDropzone"') !== false, "Dropzone container 'documentDropzone' is present", $passed, $failed);

// 1.7 Verify Review Step cards & Jump Edit buttons
assertCondition(strpos($content, 'id="wizardPanel4"') !== false, "Step 4 Review panel is present", $passed, $failed);
assertCondition(strpos($content, 'review-hero-banner') !== false, "Executive Hero banner is present", $passed, $failed);
assertCondition(strpos($content, 'review-edit-jump-btn') !== false, "Review cards have quick jump edit buttons", $passed, $failed);
assertCondition(strpos($content, 'id="docPreviewModal"') !== false, "Document lightbox preview modal is present", $passed, $failed);

// ---------------------------------------------------------
// 2. Test POST /office/vehicles storage WITHOUT vehicle_type
// ---------------------------------------------------------
echo "\n--- Testing Vehicle Registration (POST) without vehicle_type ---\n";

$testPlate = 'MH 14 TEST ' . rand(1000, 9999);
$testMakeModel = 'Mahindra XUV700 AX7L';

// Create a small 1x1 transparent PNG data URI for vehicle photo
$samplePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

// Create a sample text/pdf data URI for document
$samplePdfBase64 = 'data:application/pdf;base64,JVBERi0xLjQKJeLjz9MKMSAwIG9iajw8L1R5cGUvQ2F0YWxvZy9QYWdlcyAyIDAgUj4+ZW5kb2JqCjIgMCBvYmo8PC9UeXBlL1BhZ2VzL0tpZHNbMyAwIFJdL0NvdW50IDE+PmVuZG9iagozIDAgb2JqPDwvVHlwZS9QYWdlL1BhcmVudCAyIDAgUi9NZWRpYUJveFswIDAgMzAwIDE0NF0+PmVuZG9iagp4cmVmCjAgNAowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDAwMTggMDAwMDAgbiAKMDAwMDAwMDA2NiAwMDAwMCBuIAowMDAwMDAwMTIyIDAwMDAwIG4gCnRyYWlsZXIKPDwvU2l6ZSA0L1Jvb3QgMSAwIFI+PgpzdGFydHhyZWYKMTc4CiUlRU9GCg==';

$postPayload = [
    'vehicle_number'          => $testPlate,
    'make_model'              => $testMakeModel,
    'seating_capacity'        => '7',
    'fuel_type'               => 'diesel',
    'odometer_km'             => '12500',
    'registration_expiry'     => date('Y-m-d', strtotime('+1 year')),
    'insurance_expiry'        => date('Y-m-d', strtotime('+1 year')),
    'document_type'           => 'rc_book',
    'vehicle_photo_base64'    => $samplePngBase64,
    'rc_document_file_base64' => $samplePdfBase64,
    'rc_document_file_name'   => 'xuv_rc_book.pdf',
    // NOTICE: 'vehicle_type' is completely omitted!
];

$storeRequest = new Request($postPayload);
$storeResponse = $controller->store($storeRequest);

// Check if vehicle was created in database
$savedVehicle = $vehicleModel->findBy('vehicle_number', strtoupper(trim($testPlate)));
assertCondition(!empty($savedVehicle), "Vehicle was successfully inserted into database without explicit vehicle_type", $passed, $failed);

if (!empty($savedVehicle)) {
    // Check automatic inference of vehicle_type
    assertCondition($savedVehicle['vehicle_type'] === 'suv', "Vehicle classification defaulted/inferred to 'suv' based on seating capacity 7 (Actual: {$savedVehicle['vehicle_type']})", $passed, $failed);

    // Check seating capacity
    assertCondition((int)$savedVehicle['seating_capacity'] === 7, "Seating capacity saved correctly as 7", $passed, $failed);

    // Check fuel type
    assertCondition($savedVehicle['fuel_type'] === 'diesel', "Fuel type saved correctly as diesel", $passed, $failed);

    // Check odometer
    assertCondition((int)$savedVehicle['odometer_km'] === 12500, "Odometer saved correctly as 12500", $passed, $failed);

    // Check vehicle photo path
    assertCondition(!empty($savedVehicle['vehicle_photo']), "Vehicle photo path was stored ({$savedVehicle['vehicle_photo']})", $passed, $failed);
    if (!empty($savedVehicle['vehicle_photo'])) {
        $photoDisk = dirname(__DIR__) . '/storage/private_uploads/' . $savedVehicle['vehicle_photo'];
        assertCondition(file_exists($photoDisk), "Vehicle photo physical file exists on disk ({$photoDisk})", $passed, $failed);
    }

    // Check RC document path
    assertCondition(!empty($savedVehicle['rc_document_file']), "RC document file path was stored ({$savedVehicle['rc_document_file']})", $passed, $failed);
    if (!empty($savedVehicle['rc_document_file'])) {
        $docDisk = dirname(__DIR__) . '/storage/private_uploads/' . $savedVehicle['rc_document_file'];
        assertCondition(file_exists($docDisk), "RC document physical file exists on disk ({$docDisk})", $passed, $failed);
    }

    // Check document type
    assertCondition($savedVehicle['document_type'] === 'rc_book', "Document type saved as 'rc_book'", $passed, $failed);

    // ---------------------------------------------------------
    // 3. Test Vehicle Deletion & File Cleanup
    // ---------------------------------------------------------
    echo "\n--- Testing Vehicle Deletion & File Cleanup ---\n";

    $deleteRequest = new Request([], [], ['id' => $savedVehicle['id']]);
    $deleteResponse = $controller->delete($deleteRequest, ['id' => $savedVehicle['id']]);

    $deletedCheck = $vehicleModel->find($savedVehicle['id']);
    assertCondition(empty($deletedCheck), "Vehicle record was cleanly deleted from database", $passed, $failed);

    // Check that physical files were cleaned up
    if (!empty($savedVehicle['vehicle_photo'])) {
        $photoDisk = dirname(__DIR__) . '/storage/private_uploads/' . $savedVehicle['vehicle_photo'];
        assertCondition(!file_exists($photoDisk), "Vehicle photo file was cleaned up on vehicle deletion", $passed, $failed);
    }
    if (!empty($savedVehicle['rc_document_file'])) {
        $docDisk = dirname(__DIR__) . '/storage/private_uploads/' . $savedVehicle['rc_document_file'];
        assertCondition(!file_exists($docDisk), "RC document file was cleaned up on vehicle deletion", $passed, $failed);
    }
}

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
