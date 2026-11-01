<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Core\Session;

// Mock admin session
$userModel = new User();
$adminUser = $userModel->findBy('role', 'admin');
if ($adminUser) {
    Session::set('user_id', (int)$adminUser['id']);
    Session::set('user_role', 'admin');
}

$empModel = new Employee();
$employee = $empModel->findWithDetails(2);

if (!$employee) {
    echo "[FAIL] Employee 2 not found in database!\n";
    exit(1);
}

echo "Testing Employee 2 Edit View Rendering...\n";

// Render view
$old = [];
$errors = [];
$roleModel = new Role();
$roles = $roleModel->all();
$unlinkedUsers = $userModel->where("`status` = 'active'", [], 'name ASC');

ob_start();
include dirname(__DIR__) . '/app/Views/office/employees/edit.php';
$html = ob_get_clean();

$requiredSnippets = [
    'enctype="multipart/form-data"' => 'Form multipart enctype',
    'id="editEmployeeForm"' => 'Edit employee form ID',
    'id="avatarDropZone"' => 'Avatar dropzone',
    'name="profile_photo"' => 'Profile photo file input',
    'id="profile_photo_base64"' => 'Profile photo base64 input',
    'id="remove_profile_photo"' => 'Remove profile photo tracking input',
    'id="avatarPreviewImg"' => 'Avatar preview image',
    'id="avatarDefaultIcon"' => 'Avatar default icon',
    'id="btnRemoveAvatar"' => 'Remove avatar button',
    'name="first_name"' => 'First name input',
    'name="last_name"' => 'Last name input',
    'name="phone"' => 'Primary phone input',
    'name="email"' => 'Corporate email input',
    'name="date_of_birth"' => 'Date of birth input',
    'name="gender"' => 'Gender select',
    'name="nationality"' => 'Nationality input',
    'name="alternate_phone"' => 'Alternate phone input',
    'name="emergency_contact_name"' => 'Emergency contact name input',
    'name="emergency_contact_phone"' => 'Emergency contact phone input',
    'name="role"' => 'Role select',
    'name="department"' => 'Department select',
    'name="designation"' => 'Designation input',
    'name="employment_type"' => 'Employment type select',
    'name="joining_date"' => 'Date of joining input',
    'name="status"' => 'Roster status select',
    'name="user_id"' => 'Linked system account select',
    'name="address_line"' => 'Address line input',
    'name="city"' => 'City input',
    'name="state"' => 'State input',
    'name="country"' => 'Country input',
    'name="postal_code"' => 'Postal code input',
    'name="license_number"' => 'Driving license number input',
    'name="license_expiry"' => 'License expiry date input',
    'name="experience_years"' => 'Experience years input',
    'id="licenseDropzone"' => 'Driving license upload dropzone',
    'id="license_document_file"' => 'License document file input',
    'id="license_document_file_base64"' => 'License document base64 input',
    'id="remove_license_document"' => 'Remove license document tracking input',
    'id="licenseDropzoneEmpty"' => 'License dropzone empty state',
    'id="licenseDropzonePreview"' => 'License dropzone preview state',
    'name="id_type"' => 'ID document type select',
    'name="id_number"' => 'ID document number input',
    'name="id_expiry"' => 'ID document expiry input',
    'id="documentDropzone"' => 'ID document upload dropzone',
    'id="id_document_file"' => 'ID document file input',
    'id="id_document_file_base64"' => 'ID document base64 input',
    'id="remove_id_document"' => 'Remove ID document tracking input',
    'id="dropzoneEmptyState"' => 'ID document dropzone empty state',
    'id="dropzonePreviewState"' => 'ID document dropzone preview state',
    'name="notes"' => 'Internal operational notes input',
    'id="docPreviewModal"' => 'Document Lightbox Preview Modal',
    'openDocPreviewModal' => 'Lightbox open handler script',
    'onDocumentTypeChange' => 'Document metadata switcher handler script',
    'handleProfilePhotoChange' => 'Profile photo handler script',
    'handleLicenseFileChange' => 'License file handler script',
    'handleDocumentFileChange' => 'ID document file handler script'
];

$failed = false;
foreach ($requiredSnippets as $needle => $label) {
    if (strpos($html, $needle) === false) {
        echo "[FAIL] Missing {$label} (needle: {$needle})\n";
        $failed = true;
    } else {
        echo "[PASS] {$label}\n";
    }
}

// Verify employee 2's specific data appears in pre-filled fields
$emp2Values = [
    $employee['first_name'] => 'Employee First Name pre-filled',
    $employee['last_name'] => 'Employee Last Name pre-filled',
    $employee['phone'] => 'Employee Phone pre-filled',
    $employee['email'] => 'Employee Email pre-filled',
    $employee['employee_code'] => 'Employee Code badge rendered'
];

if (!empty($employee['license_number'])) {
    $emp2Values[$employee['license_number']] = 'Employee License Number pre-filled';
}

foreach ($emp2Values as $val => $label) {
    if (strpos($html, (string)$val) === false) {
        echo "[FAIL] {$label} (expected: '{$val}')\n";
        $failed = true;
    } else {
        echo "[PASS] {$label}\n";
    }
}

// Test with employee that has existing avatar, license scan, and ID scan
echo "\nTesting Employee with Existing Photo & Document Previews...\n";
$mockEmployee = array_merge($employee, [
    'profile_photo' => 'employees/avatars/test_staff_avatar.jpg',
    'license_document_file' => 'employees/licenses/test_license_scan.pdf',
    'id_document_file' => 'employees/documents/test_id_scan.png',
    'id_type' => 'pan_card',
    'id_number' => 'ABCDE1234F',
    'id_expiry' => '2032-08-15',
    'notes' => 'Senior VIP Fleet Chauffeur'
]);

ob_start();
$employee = $mockEmployee;
include dirname(__DIR__) . '/app/Views/office/employees/edit.php';
$mockHtml = ob_get_clean();

if (strpos($mockHtml, 'test_staff_avatar.jpg') !== false && 
    strpos($mockHtml, 'test_license_scan.pdf') !== false && 
    strpos($mockHtml, 'test_id_scan.png') !== false) {
    echo "[PASS] Existing avatar, license file, and ID file correctly rendered in edit preview state.\n";
} else {
    echo "[FAIL] Existing files not rendered properly in preview state.\n";
    $failed = true;
}

// Test Controller edit action handling
echo "\nTesting EmployeeController::edit and update...\n";
$controller = new \App\Controllers\EmployeeController();
$request = new \App\Core\Request();

$response = $controller->edit($request, ['id' => 2]);
if ($response->getStatusCode() === 200) {
    echo "[PASS] EmployeeController::edit returns HTTP 200\n";
} else {
    echo "[FAIL] EmployeeController::edit returned status " . $response->getStatusCode() . "\n";
    $failed = true;
}

// Test submitting update via POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'first_name' => 'virat',
    'last_name' => 'kholi',
    'phone' => '8799845236',
    'email' => 'virat@gmail.com',
    'department' => 'fleet_chauffeur',
    'designation' => 'Chauffeur / Driver',
    'employment_type' => 'full_time',
    'joining_date' => '2026-11-01',
    'status' => 'active',
    'license_number' => 'MH-01-2026-8877665',
    'license_expiry' => '2030-11-01',
    'experience_years' => '6',
    'id_type' => 'aadhaar',
    'id_number' => '9988 7766 5544',
    'notes' => 'Senior Lead Chauffeur - updated via automated test',
    'csrf_token' => \App\Core\Csrf::getToken()
];

$postRequest = new \App\Core\Request();
$updateResponse = $controller->update($postRequest, ['id' => 2]);
if ($updateResponse->getStatusCode() === 302) {
    echo "[PASS] EmployeeController::update redirected successfully (HTTP 302)\n";
    $updatedEmp = $empModel->findWithDetails(2);
    if ($updatedEmp['license_number'] === 'MH-01-2026-8877665' && 
        $updatedEmp['id_number'] === '9988 7766 5544' && 
        $updatedEmp['notes'] === 'Senior Lead Chauffeur - updated via automated test') {
        echo "[PASS] Employee 2 fields license_number, id_number, and notes verified in database!\n";
    } else {
        echo "[FAIL] Employee 2 database fields did not match expected updated values.\n";
        $failed = true;
    }
} else {
    echo "[FAIL] EmployeeController::update returned status " . $updateResponse->getStatusCode() . "\n";
    $failed = true;
}

if ($failed) {
    echo "\n=== TEST SUITE FAILED ===\n";
    exit(1);
} else {
    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
    exit(0);
}
