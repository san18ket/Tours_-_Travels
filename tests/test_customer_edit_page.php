<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\Customer;
use App\Models\User;
use App\Core\Session;

// Mock admin session
$userModel = new User();
$adminUser = $userModel->findBy('role', 'admin');
if ($adminUser) {
    Session::set('user_id', (int)$adminUser['id']);
    Session::set('user_role', 'admin');
}

$customerModel = new Customer();
$customer = $customerModel->find(2);

if (!$customer) {
    echo "FAIL: Customer 2 not found in database!\n";
    exit(1);
}

echo "Testing Customer 2 Edit View Rendering...\n";

// Render view
$old = [];
$errors = [];
ob_start();
include dirname(__DIR__) . '/app/Views/office/customers/edit.php';
$html = ob_get_clean();

$requiredSnippets = [
    'enctype="multipart/form-data"' => 'Form multipart enctype',
    'id="editCustomerForm"' => 'Edit customer form ID',
    'id="avatarDropZone"' => 'Avatar dropzone',
    'name="profile_photo"' => 'Profile photo file input',
    'id="profile_photo_base64"' => 'Profile photo base64 input',
    'id="remove_profile_photo"' => 'Remove profile photo tracking input',
    'id="avatarPreviewImg"' => 'Avatar preview image',
    'id="avatarDefaultIcon"' => 'Avatar default icon',
    'id="btnRemoveAvatar"' => 'Remove avatar button',
    'name="title"' => 'Honorific title / salutation select',
    'name="name"' => 'Customer full name input',
    'name="status"' => 'Account status select',
    'name="date_of_birth"' => 'Date of birth input',
    'name="gender"' => 'Gender select',
    'name="nationality"' => 'Nationality input',
    'name="phone"' => 'Primary phone input',
    'name="alternate_phone"' => 'Alternate phone input',
    'name="email"' => 'Email address input',
    'name="emergency_contact_name"' => 'Emergency contact name input',
    'name="emergency_contact_phone"' => 'Emergency contact phone input',
    'name="address_line"' => 'Address line input',
    'name="city"' => 'City input',
    'name="state"' => 'State input',
    'name="country"' => 'Country input',
    'name="postal_code"' => 'Postal code input',
    'name="id_type"' => 'ID document type select',
    'name="id_number"' => 'ID document number input',
    'name="id_expiry"' => 'ID document expiry input',
    'id="documentDropzone"' => 'Document upload dropzone',
    'id="id_document_file"' => 'Document file input',
    'id="id_document_file_base64"' => 'Document file base64 input',
    'id="remove_id_document"' => 'Remove document tracking input',
    'id="dropzoneEmptyState"' => 'Dropzone empty state',
    'id="dropzonePreviewState"' => 'Dropzone populated preview state',
    'name="notes"' => 'Internal operational notes input',
    'name="marketing_consent"' => 'Marketing consent checkbox',
    'id="docPreviewModal"' => 'Document Lightbox Preview Modal',
    'openDocPreviewModal' => 'Lightbox open handler script',
    'onDocumentTypeChange' => 'Document metadata switcher handler script',
    'handleProfilePhotoChange' => 'Profile photo handler script',
    'handleDocumentFileChange' => 'Document file handler script'
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

// Verify customer 2's specific data appears in pre-filled fields
$cust2Values = [
    'Anushka Sharma' => 'Customer Name pre-filled',
    '8799845232' => 'Customer Phone pre-filled',
    'sanket@gmail.com' => 'Customer Email pre-filled',
    'New Bhangeaon Khaperkheda Near Sai mandir' => 'Customer Address pre-filled',
    'Nagpur' => 'Customer City pre-filled',
    'Mahārāshtra' => 'Customer State pre-filled',
    '441102' => 'Customer Postal Code pre-filled',
    'VG-CUST-0002' => 'Customer Code badge rendered'
];

foreach ($cust2Values as $val => $label) {
    if (strpos($html, $val) === false) {
        echo "[FAIL] {$label} (expected: '{$val}')\n";
        $failed = true;
    } else {
        echo "[PASS] {$label}\n";
    }
}

// Test with customer that has existing profile_photo and id_document_file
echo "\nTesting Customer with Existing Photo & Document Preview...\n";
$mockCustomer = array_merge($customer, [
    'profile_photo' => 'customers/avatars/test_avatar.jpg',
    'id_document_file' => 'customers/documents/test_id.pdf',
    'id_type' => 'passport',
    'id_number' => 'Z9876543',
    'id_expiry' => '2030-05-20',
    'notes' => 'VIP Customer'
]);

ob_start();
$customer = $mockCustomer;
include dirname(__DIR__) . '/app/Views/office/customers/edit.php';
$mockHtml = ob_get_clean();

if (strpos($mockHtml, 'test_avatar.jpg') !== false && strpos($mockHtml, 'test_id.pdf') !== false) {
    echo "[PASS] Existing avatar and document file correctly rendered in edit preview state.\n";
} else {
    echo "[FAIL] Existing avatar or document file not rendered properly.\n";
    $failed = true;
}

// Test Controller update action handling
echo "\nTesting CustomerController::edit and update...\n";
$controller = new \App\Controllers\CustomerController();
$request = new \App\Core\Request();

$response = $controller->edit($request, ['id' => 2]);
if ($response->getStatusCode() === 200) {
    echo "[PASS] CustomerController::edit returns HTTP 200\n";
} else {
    echo "[FAIL] CustomerController::edit returned status " . $response->getStatusCode() . "\n";
    $failed = true;
}

if ($failed) {
    echo "\n=== TEST SUITE FAILED ===\n";
    exit(1);
} else {
    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
    exit(0);
}
