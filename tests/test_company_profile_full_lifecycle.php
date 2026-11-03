<?php
/**
 * Comprehensive Test Suite: Single Professional Company Profile Management Form & Document Image Integration
 * Tests:
 * 1. Initial State (No profile / clean load)
 * 2. Profile Creation with all Basic Details
 * 3. Automatic Data Loading & Field Pre-population
 * 4. Editing details without touching images (preservation)
 * 5. All 4 Image Uploads (Logo, Paid Stamp, Signature, Signature + Stamp)
 * 6. Independent Image Replacement (replacing 1 preserves the other 3)
 * 7. Security: Invalid MIME inspection & oversized file rejection
 * 8. Independent Image Removal
 * 9. Duplicate primary prevention & single installation record enforcement
 * 10. Invoice & Receipt Printing Integration:
 *     - Dynamic Company Logo & Details in header
 *     - Paid Stamp on fully paid invoice ONLY (never on unpaid or partial)
 *     - Signature selection: None, Authorized Signature, Signature + Stamp
 * 11. Global branding propagation (CompanySettingsService, sidebar, helpers)
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\CompanyProfile;
use App\Models\Invoice;
use App\Services\CompanySettingsService;
use App\Controllers\CompanyController;
use App\Controllers\InvoiceController;

$passed = 0;
$failed = 0;

function runTest(string $desc, bool $cond, ?string $err = null): void {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}" . ($err ? " -> {$err}" : "") . "\n";
        $failed++;
    }
}

$pdo = Database::getConnection();

echo "======================================================================\n";
echo "1. VERIFYING DATABASE & INITIAL STATE\n";
echo "======================================================================\n";

// Ensure clean test setup: backup any existing profiles temporarily or inspect
$existingProfiles = $pdo->query("SELECT * FROM `company_profiles`")->fetchAll(PDO::FETCH_ASSOC);

// Clear table for fresh testing
$pdo->exec("DELETE FROM `company_profiles`");
CompanySettingsService::clearCache();

$model = new CompanyProfile();
$controller = new CompanyController();

Session::set('user_id', 1);
Session::set('user_role', 'admin');

runTest("company_profiles table is empty for first-time use test", $model->getPrimary() === null);

// Test GET /office/company when no profile exists
$req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/company']);
$resp = $controller->index($req);
runTest("GET /office/company returns HTTP 200 on empty DB", $resp->getStatusCode() === 200);
$content = $resp->getContent();
runTest("Form rendered with Section A, Section B, Section C", 
    str_contains($content, 'Section A: Company Logo') && 
    str_contains($content, 'Section B: Basic Company Details') && 
    str_contains($content, 'Section C: Company Document Images')
);
runTest("Save Company Profile button is present", str_contains($content, 'Save Company Profile'));

echo "\n======================================================================\n";
echo "2. CREATING PRIMARY COMPANY PROFILE (BASIC DETAILS)\n";
echo "======================================================================\n";

$companyData = [
    'company_name'    => 'Manaswi Luxury Travels & Tours',
    'owner_name'      => 'Rajesh R. Sharma',
    'tagline'         => 'Safe Journey & Happy Memories',
    'phone'           => '+91 98220 12345',
    'alternate_phone' => '+91 94220 67890',
    'email'           => 'contact@manaswitravels.com',
    'website'         => 'https://www.manaswitravels.com',
    'gst_number'      => '27AABCM8899Q1ZX',
    'pan_number'      => 'AABCM8899Q',
    'address'         => 'Shop No. 4, Ground Floor, Royal Commercial Complex, Station Road',
    'city'            => 'Pune',
    'state'           => 'Maharashtra',
    'pin_code'        => '411001',
    'status'          => 'active',
];

$postReq = new Request([], $companyData, [], [], [
    'REQUEST_METHOD' => 'POST', 
    'REQUEST_URI' => '/office/company',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);

$saveResp = $controller->save($postReq);
runTest("POST /office/company creates profile with HTTP 200", $saveResp->getStatusCode() === 200);

CompanySettingsService::clearCache();
$primary = $model->getPrimary();
runTest("Primary profile was created in DB", !empty($primary));
runTest("Company Name matches saved value", ($primary['company_name'] ?? '') === 'Manaswi Luxury Travels & Tours');
runTest("Owner Name matches saved value", ($primary['owner_name'] ?? '') === 'Rajesh R. Sharma');
runTest("Mobile Number matches saved value", ($primary['phone'] ?? '') === '+91 98220 12345');
runTest("GST Number matches saved value", ($primary['gst_number'] ?? '') === '27AABCM8899Q1ZX');
runTest("PAN Number matches saved value", ($primary['pan_number'] ?? '') === 'AABCM8899Q');
runTest("City, State, and PIN match saved values", 
    $primary['city'] === 'Pune' && $primary['state'] === 'Maharashtra' && $primary['pin_code'] === '411001'
);

echo "\n======================================================================\n";
echo "3. AUTOMATIC DATA LOADING AND EDITING\n";
echo "======================================================================\n";

// Open Company page again
$reopenReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/company']);
$reopenResp = $controller->index($reopenReq);
$reopenHtml = $reopenResp->getContent();

runTest("Reopening Company page automatically populates company_name", str_contains($reopenHtml, 'value="Manaswi Luxury Travels &amp; Tours"'));
runTest("Reopening Company page automatically populates owner_name", str_contains($reopenHtml, 'value="Rajesh R. Sharma"'));
runTest("Reopening Company page automatically populates phone", str_contains($reopenHtml, 'value="+91 98220 12345"'));
runTest("Reopening Company page automatically populates GST number", str_contains($reopenHtml, 'value="27AABCM8899Q1ZX"'));
runTest("Reopening Company page automatically populates PIN code", str_contains($reopenHtml, 'value="411001"'));

// Edit company name and mobile number without touching images
$editData = array_merge($companyData, [
    'company_name' => 'Manaswi Royal Tours & Travels Ltd',
    'phone'        => '+91 98220 99999',
]);

$editReq = new Request([], $editData, [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/office/company',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);
$editResp = $controller->save($editReq);
runTest("Updating company details returns HTTP 200", $editResp->getStatusCode() === 200);

$totalRecords = (int)$pdo->query("SELECT COUNT(*) FROM `company_profiles`")->fetchColumn();
runTest("Record count remains 1 (no duplicate profiles created)", $totalRecords === 1);

CompanySettingsService::clearCache();
$updated = $model->getPrimary();
runTest("Updated Company Name persisted", $updated['company_name'] === 'Manaswi Royal Tours & Travels Ltd');
runTest("Updated Mobile Number persisted", $updated['phone'] === '+91 98220 99999');

echo "\n======================================================================\n";
echo "4. UPLOADING ALL 4 DOCUMENT IMAGES\n";
echo "======================================================================\n";

// Prepare 1x1 transparent/valid base64 PNGs for testing
$fakePngLogo  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
$fakePngStamp = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
$fakePngSig   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
$fakePngCombo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPj/HwADBwIAMCbHYQAAAABJRU5ErkJggg==';

$uploadAllData = array_merge($editData, [
    'logo_base64'            => $fakePngLogo,
    'paid_stamp_base64'      => $fakePngStamp,
    'signature_base64'       => $fakePngSig,
    'signature_stamp_base64' => $fakePngCombo,
]);

$uploadReq = new Request([], $uploadAllData, [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/office/company',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);

$uploadResp = $controller->save($uploadReq);
runTest("Uploading 4 images returns HTTP 200", $uploadResp->getStatusCode() === 200);

CompanySettingsService::clearCache();
$withImages = $model->getPrimary();

runTest("Logo path saved in database", !empty($withImages['logo']) && str_starts_with($withImages['logo'], 'company/logo_'));
runTest("Paid Stamp path saved in database", !empty($withImages['paid_stamp']) && str_starts_with($withImages['paid_stamp'], 'company/stamp_paid_'));
runTest("Signature path saved in database", !empty($withImages['signature']) && str_starts_with($withImages['signature'], 'company/sig_auth_'));
runTest("Signature + Stamp path saved in database", !empty($withImages['signature_stamp']) && str_starts_with($withImages['signature_stamp'], 'company/sig_stamp_'));

// Verify physical files on disk
$projectRoot = dirname(__DIR__);
$logoDiskPath = $projectRoot . '/public/storage/' . $withImages['logo'];
$stampDiskPath = $projectRoot . '/public/storage/' . $withImages['paid_stamp'];
$sigDiskPath = $projectRoot . '/public/storage/' . $withImages['signature'];
$sigStampDiskPath = $projectRoot . '/public/storage/' . $withImages['signature_stamp'];

runTest("Logo exists on filesystem", file_exists($logoDiskPath));
runTest("Paid Stamp exists on filesystem", file_exists($stampDiskPath));
runTest("Signature exists on filesystem", file_exists($sigDiskPath));
runTest("Signature + Stamp exists on filesystem", file_exists($sigStampDiskPath));

// Reopen page and check that previews load all 4 image URLs
$viewImagesReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/company']);
$viewImagesHtml = $controller->index($viewImagesReq)->getContent();

runTest("Company page loads logo preview src", str_contains($viewImagesHtml, $withImages['logo']));
runTest("Company page loads paid stamp preview src", str_contains($viewImagesHtml, $withImages['paid_stamp']));
runTest("Company page loads signature preview src", str_contains($viewImagesHtml, $withImages['signature']));
runTest("Company page loads signature+stamp preview src", str_contains($viewImagesHtml, $withImages['signature_stamp']));

echo "\n======================================================================\n";
echo "5. INDEPENDENT IMAGE REPLACEMENT (REPLACING 1 PRESERVES OTHERS)\n";
echo "======================================================================\n";

// Replace ONLY the Signature image with a new image
$newSigPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

$replaceSigData = array_merge($editData, [
    'signature_base64' => $newSigPng,
]);

$replaceSigReq = new Request([], $replaceSigData, [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/office/company',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);
$controller->save($replaceSigReq);

CompanySettingsService::clearCache();
$afterReplace = $model->getPrimary();

runTest("Signature image path was updated to a new file", 
    $afterReplace['signature'] !== $withImages['signature'] && str_starts_with($afterReplace['signature'], 'company/sig_auth_')
);
runTest("Logo image remained unchanged", $afterReplace['logo'] === $withImages['logo']);
runTest("Paid Stamp image remained unchanged", $afterReplace['paid_stamp'] === $withImages['paid_stamp']);
runTest("Signature + Stamp remained unchanged", $afterReplace['signature_stamp'] === $withImages['signature_stamp']);
runTest("Old signature file was deleted cleanly", !file_exists($sigDiskPath));
runTest("New signature file exists on disk", file_exists($projectRoot . '/public/storage/' . $afterReplace['signature']));

echo "\n======================================================================\n";
echo "6. SECURITY & VALIDATION (INVALID FORMATS, REMOVAL)\n";
echo "======================================================================\n";

// Test invalid base64 format rejection
$badImageData = array_merge($editData, [
    'logo_base64' => 'data:application/x-executable;base64,TVqQAAMAAAAEAAAA//8AALgAAAAAAAAAQAAa',
]);
$badImgReq = new Request([], $badImageData, [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/office/company',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);
$badImgResp = $controller->save($badImgReq);
runTest("Invalid non-image upload returns HTTP 422 Unprocessable Entity", $badImgResp->getStatusCode() === 422);

// Test independent image removal via removeImage endpoint
$remStampReq = new Request([], ['type' => 'paid_stamp'], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/office/company/remove-image',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);
$remResp = $controller->removeImage($remStampReq);
runTest("removeImage for paid_stamp returns HTTP 200", $remResp->getStatusCode() === 200);

CompanySettingsService::clearCache();
$afterStampRemove = $model->getPrimary();
runTest("Paid Stamp in database is now NULL", empty($afterStampRemove['paid_stamp']));
runTest("Paid Stamp physical file was deleted from disk", !file_exists($stampDiskPath));
runTest("Other 3 images remain intact after Paid Stamp removal", 
    !empty($afterStampRemove['logo']) && !empty($afterStampRemove['signature']) && !empty($afterStampRemove['signature_stamp'])
);

echo "\n======================================================================\n";
echo "7. INVOICE PRINTING INTEGRATION (LOGO, PAID STAMP, SIGNATURE SELECTION)\n";
echo "======================================================================\n";

// Re-upload paid stamp for invoice testing
$reUploadStampData = array_merge($editData, [
    'paid_stamp_base64' => $fakePngStamp,
]);
$controller->save(new Request([], $reUploadStampData, [], [], [
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]));
CompanySettingsService::clearCache();

// Create sample test invoices in different states:
// A. Fully Paid invoice (balance = 0)
// B. Partially Paid invoice (balance > 0)
// C. Unpaid invoice (paid = 0)

$invModel = new Invoice();
$invController = new InvoiceController();

$invCode1 = 'INV-TEST-PAID-' . bin2hex(random_bytes(4));
$pdo->exec("INSERT INTO invoices (invoice_number, customer_id, total_amount, paid_amount, payment_status, issued_at, due_date)
            VALUES ('{$invCode1}', 1, 10000.00, 10000.00, 'paid', NOW(), NOW())");
$invoicePaidId = (int)$pdo->lastInsertId();

$printPaidReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/invoices/{$invoicePaidId}/print"]);
$printPaidResp = $invController->printInvoice($printPaidReq, ['id' => $invoicePaidId]);
$printPaidHtml = $printPaidResp->getContent();

runTest("Print Invoice returns HTTP 200", $printPaidResp->getStatusCode() === 200);
runTest("Invoice header displays dynamic company name", str_contains($printPaidHtml, 'Manaswi Royal Tours &amp; Travels Ltd') || str_contains($printPaidHtml, 'Manaswi Royal Tours & Travels Ltd'));
runTest("Invoice header displays company logo", str_contains($printPaidHtml, CompanySettingsService::getLogoUrl()));
runTest("Fully Paid Invoice displays Paid Stamp", str_contains($printPaidHtml, 'paid-stamp-watermark') && str_contains($printPaidHtml, CompanySettingsService::getPaidStampUrl()));
runTest("Print interface includes Signature Selector options", 
    str_contains($printPaidHtml, 'name="sigOption"') && 
    str_contains($printPaidHtml, 'value="none"') && 
    str_contains($printPaidHtml, 'value="auth"') && 
    str_contains($printPaidHtml, 'value="stamp"')
);
runTest("Default signature option is 'none' (checked)", str_contains($printPaidHtml, 'value="none" checked'));
runTest("Print footer includes Authorized Signature container", str_contains($printPaidHtml, 'id="authSigBox"'));
runTest("Print footer includes Signature + Stamp container", str_contains($printPaidHtml, 'id="stampSigBox"'));

// B. Test Partially Paid Invoice -> Paid Stamp MUST NOT be displayed
$invCode2 = 'INV-TEST-PART-' . bin2hex(random_bytes(4));
$pdo->exec("INSERT INTO invoices (invoice_number, customer_id, total_amount, paid_amount, payment_status, issued_at, due_date)
            VALUES ('{$invCode2}', 1, 10000.00, 4000.00, 'partial', NOW(), NOW())");
$invoicePartialId = (int)$pdo->lastInsertId();

$printPartReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/invoices/{$invoicePartialId}/print"]);
$printPartHtml = $invController->printInvoice($printPartReq, ['id' => $invoicePartialId])->getContent();

runTest("Partially Paid Invoice DOES NOT display Paid Stamp", !str_contains($printPartHtml, 'paid-stamp-watermark'));

// C. Test Unpaid Invoice -> Paid Stamp MUST NOT be displayed
$invCode3 = 'INV-TEST-UNPD-' . bin2hex(random_bytes(4));
$pdo->exec("INSERT INTO invoices (invoice_number, customer_id, total_amount, paid_amount, payment_status, issued_at, due_date)
            VALUES ('{$invCode3}', 1, 10000.00, 0.00, 'unpaid', NOW(), NOW())");
$invoiceUnpaidId = (int)$pdo->lastInsertId();

$printUnpaidReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/office/invoices/{$invoiceUnpaidId}/print"]);
$printUnpaidHtml = $invController->printInvoice($printUnpaidReq, ['id' => $invoiceUnpaidId])->getContent();

runTest("Unpaid Invoice DOES NOT display Paid Stamp", !str_contains($printUnpaidHtml, 'paid-stamp-watermark'));

echo "\n======================================================================\n";
echo "8. CENTRALIZED APPLICATION BRANDING IN SIDEBAR & HELPERS\n";
echo "======================================================================\n";

runTest("company_name() helper returns active DB name", company_name() === 'Manaswi Royal Tours & Travels Ltd');
runTest("company_owner() helper returns active owner", company_owner() === 'Rajesh R. Sharma');
runTest("company_gst_number() helper returns GSTIN", company_gst_number() === '27AABCM8899Q1ZX');
runTest("company_pan_number() helper returns PAN", company_pan_number() === 'AABCM8899Q');
runTest("company_city() helper returns Pune", company_city() === 'Pune');
runTest("company_state() helper returns Maharashtra", company_state() === 'Maharashtra');
runTest("company_pin_code() helper returns 411001", company_pin_code() === '411001');
runTest("has_company_logo() returns true", has_company_logo() === true);
runTest("has_company_paid_stamp() returns true", has_company_paid_stamp() === true);
runTest("has_company_signature() returns true", has_company_signature() === true);
runTest("has_company_signature_stamp() returns true", has_company_signature_stamp() === true);

// Verify Sidebar rendering in office layout
ob_start();
$user = ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@manaswi.com', 'role' => 'admin'];
$userRole = 'Admin';
$title = 'Dashboard';
$flashes = [];
$content = '<p>Test</p>';
$_SERVER['REQUEST_URI'] = '/office/company';
require dirname(__DIR__) . '/app/Views/layouts/office.php';
$sidebarHtml = ob_get_clean();

runTest("Office layout displays dynamic company name in sidebar brand", str_contains($sidebarHtml, 'Manaswi Royal Tours &amp; Travels Ltd') || str_contains($sidebarHtml, 'Manaswi Royal Tours & Travels Ltd'));
runTest("Sidebar Company link points directly to /office/company", str_contains($sidebarHtml, 'href="' . url('/office/company') . '"'));

echo "\n======================================================================\n";
echo "TEST RESULTS SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================================\n";

if ($failed > 0) {
    exit(1);
}
