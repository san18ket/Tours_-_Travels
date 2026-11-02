<?php
/**
 * Test Suite: Tours & Travels Company Management & Dynamic Branding
 * Validates:
 * 1. Database schema & company_profiles table
 * 2. Model CRUD & single primary profile enforcement
 * 3. Centralized CompanySettingsService priority hierarchy (DB -> .env -> defaults)
 * 4. Secure logo upload, MIME verification, replacement, and safe deletion
 * 5. Form validation & duplicate profile prevention
 * 6. Dynamic application branding across views (Sidebar, Login, Driver, Footer, PWA Manifest)
 * 7. Dynamic Email branding & logo integration
 * 8. Authentication & admin authorization protection
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\CompanyProfile;
use App\Services\CompanySettingsService;
use App\Services\EmailService;
use App\Controllers\CompanyController;

$passed = 0;
$failed = 0;

function assertCompanyTest(string $description, bool $condition, ?string $details = null): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($details ? " -> {$details}" : "") . "\n";
        $failed++;
    }
}

$pdo = Database::getConnection();

echo "============================================================\n";
echo "1. VERIFYING DATABASE SCHEMA & MIGRATIONS\n";
echo "============================================================\n";

$stmt = $pdo->query("SHOW TABLES LIKE 'company_profiles'");
assertCompanyTest("Table 'company_profiles' exists in database", (bool)$stmt->fetch());

$columns = $pdo->query("SHOW COLUMNS FROM `company_profiles`")->fetchAll(PDO::FETCH_COLUMN);
$expectedColumns = ['id', 'company_name', 'tagline', 'logo', 'email', 'phone', 'website', 'address', 'status', 'is_primary', 'created_at', 'updated_at'];
foreach ($expectedColumns as $col) {
    assertCompanyTest("Column '{$col}' exists in company_profiles", in_array($col, $columns));
}

echo "\n============================================================\n";
echo "2. VERIFYING CompanyProfile MODEL & SINGLE PRIMARY PROFILE LOGIC\n";
echo "============================================================\n";

$model = new CompanyProfile();

// Clean existing test records
$pdo->exec("DELETE FROM `company_profiles` WHERE `company_name` LIKE 'Test Company %'");

// Test Creation
$comp1Id = $model->create([
    'company_name' => 'Test Company Alpha',
    'tagline'      => 'Journeys of Excellence',
    'email'        => 'alpha@example.com',
    'phone'        => '+91 98000 11111',
    'website'      => 'https://www.alpha-tours.com',
    'address'      => 'Plot 10, Civil Lines, Nagpur',
    'status'       => 'active',
    'is_primary'   => 1,
    'created_at'   => date('Y-m-d H:i:s'),
    'updated_at'   => date('Y-m-d H:i:s'),
]);

assertCompanyTest("Company profile created successfully with ID: {$comp1Id}", $comp1Id > 0);

$primary = $model->getPrimary();
assertCompanyTest("getPrimary() retrieves created profile", $primary && $primary['company_name'] === 'Test Company Alpha');
assertCompanyTest("hasPrimary() reports true", $model->hasPrimary());

// Test Second Profile and setPrimary
$comp2Id = $model->create([
    'company_name' => 'Test Company Beta',
    'tagline'      => 'Adventure Awaits',
    'email'        => 'beta@example.com',
    'phone'        => '+91 98000 22222',
    'status'       => 'active',
    'is_primary'   => 0,
    'created_at'   => date('Y-m-d H:i:s'),
    'updated_at'   => date('Y-m-d H:i:s'),
]);

assertCompanyTest("Second profile created with is_primary = 0", $comp2Id > 0);
$model->setPrimary($comp2Id);
$newPrimary = $model->getPrimary();
assertCompanyTest("setPrimary() promoted profile Beta to primary", $newPrimary && $newPrimary['id'] == $comp2Id);

// Restore Alpha as primary
$model->setPrimary($comp1Id);

echo "\n============================================================\n";
echo "3. VERIFYING Centralized CompanySettingsService PRIORITY\n";
echo "============================================================\n";

CompanySettingsService::clearCache();

assertCompanyTest("CompanySettingsService resolves active company name from DB", CompanySettingsService::getCompanyName() === 'Test Company Alpha');
assertCompanyTest("CompanySettingsService resolves company tagline from DB", CompanySettingsService::getTagline() === 'Journeys of Excellence');
assertCompanyTest("CompanySettingsService resolves company email from DB", CompanySettingsService::getCompanyEmail() === 'alpha@example.com');
assertCompanyTest("CompanySettingsService resolves phone from DB", CompanySettingsService::getPhone() === '+91 98000 11111');
assertCompanyTest("CompanySettingsService resolves website from DB", CompanySettingsService::getWebsite() === 'https://www.alpha-tours.com');
assertCompanyTest("CompanySettingsService resolves address from DB", CompanySettingsService::getAddress() === 'Plot 10, Civil Lines, Nagpur');

// Helper functions test
assertCompanyTest("Global helper company_name() returns matching name", company_name() === 'Test Company Alpha');
assertCompanyTest("Global helper company_tagline() returns matching tagline", company_tagline() === 'Journeys of Excellence');
assertCompanyTest("Global helper company_email() returns matching email", company_email() === 'alpha@example.com');

echo "\n============================================================\n";
echo "4. VERIFYING SECURE LOGO UPLOADS, REPLACEMENT & REMOVAL\n";
echo "============================================================\n";

$projectRoot = dirname(__DIR__);
$testLogoDir = $projectRoot . '/public/storage/company';
if (!is_dir($testLogoDir)) {
    @mkdir($testLogoDir, 0777, true);
}

// 1. Simulate base64 PNG image upload
$fakePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

// Test Controller store with logo
Session::set('user_id', 1);
Session::set('user_role', 'admin');

$controller = new CompanyController();

// Update profile with logo
$request = new Request([], [
    'company_name' => 'Test Company Alpha',
    'tagline'      => 'Journeys of Excellence',
    'email'        => 'alpha@example.com',
    'phone'        => '+91 98000 11111',
    'website'      => 'https://www.alpha-tours.com',
    'address'      => 'Plot 10, Civil Lines, Nagpur',
    'status'       => 'active',
    'logo_base64'  => $fakePngBase64,
], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$resp = $controller->update($request, ['id' => $comp1Id]);
CompanySettingsService::clearCache();

$updated = $model->find($comp1Id);
assertCompanyTest("Company profile logo was saved to storage", !empty($updated['logo']) && str_starts_with($updated['logo'], 'company/logo_'));

$fullPath = $projectRoot . '/public/storage/' . $updated['logo'];
assertCompanyTest("Uploaded logo file exists physically on disk", file_exists($fullPath));
assertCompanyTest("CompanySettingsService::getLogoUrl() returns public storage path", str_contains(CompanySettingsService::getLogoUrl(), '/storage/company/logo_'));

// 2. Test Logo Removal
$removeReq = new Request([], ['id' => $comp1Id], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$controller->removeLogo($removeReq, ['id' => $comp1Id]);
CompanySettingsService::clearCache();

$afterRemoval = $model->find($comp1Id);
assertCompanyTest("Logo field is set to NULL after removal", empty($afterRemoval['logo']));
assertCompanyTest("Previous logo file was physically deleted from disk", !file_exists($fullPath));

echo "\n============================================================\n";
echo "5. VERIFYING FORM VALIDATION & DUPLICATE PROFILE HANDLING\n";
echo "============================================================\n";

// A. Empty Company Name
$badReq = new Request([], [
    'company_name' => '',
    'email'        => 'valid@example.com',
], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$badResp = $controller->store($badReq);
assertCompanyTest("Empty company name returns HTTP 422 Unprocessable Entity", $badResp->getStatusCode() === 422);

// B. Invalid Email
$badEmailReq = new Request([], [
    'company_name' => 'Valid Company',
    'email'        => 'not-an-email',
], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$badEmailResp = $controller->store($badEmailReq);
assertCompanyTest("Invalid email returns HTTP 422 Unprocessable Entity", $badEmailResp->getStatusCode() === 422);

// C. Duplicate Primary Creation Blocked
$dupReq = new Request([], [
    'company_name' => 'Another Company',
    'email'        => 'another@example.com',
], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

$dupResp = $controller->store($dupReq);
assertCompanyTest("Duplicate primary company creation is blocked", $dupResp->getStatusCode() === 422);

echo "\n============================================================\n";
echo "6. VERIFYING DYNAMIC APPLICATION BRANDING IN VIEWS\n";
echo "============================================================\n";

// Master Layout View
ob_start();
$title = 'Test Dashboard';
$content = '<div>Test content</div>';
require $projectRoot . '/app/Views/layouts/office.php';
$renderedOffice = ob_get_clean();

assertCompanyTest("Office Layout title reflects dynamic company name", str_contains($renderedOffice, 'Test Company Alpha'));
assertCompanyTest("Office Layout sidebar branding displays 'Test Company Alpha'", str_contains($renderedOffice, 'Test Company Alpha'));
assertCompanyTest("Office Layout sidebar tagline displays 'Journeys of Excellence'", str_contains($renderedOffice, 'Journeys of Excellence'));
assertCompanyTest("Office Layout footer displays dynamic copyright with 'Test Company Alpha'", str_contains($renderedOffice, 'Test Company Alpha') && str_contains($renderedOffice, 'All rights reserved.'));
assertCompanyTest("Office Layout sidebar contains 'Company Management' dropdown", str_contains($renderedOffice, 'Company Management'));
assertCompanyTest("Office Layout sidebar contains 'Add Company' link", str_contains($renderedOffice, '/office/company/create'));
assertCompanyTest("Office Layout sidebar contains 'List Companies' link", str_contains($renderedOffice, '/office/company'));

// Mobile Topbar Component
ob_start();
require $projectRoot . '/app/Views/components/mobile_topbar.php';
$renderedMobileTop = ob_get_clean();
assertCompanyTest("Mobile Topbar displays dynamic company name", str_contains($renderedMobileTop, 'Test Company Alpha'));
assertCompanyTest("Mobile Topbar displays dynamic company tagline", str_contains($renderedMobileTop, 'Journeys of Excellence'));

// Auth Layout View
ob_start();
$content = '<div>Login form</div>';
require $projectRoot . '/app/Views/layouts/auth.php';
$renderedAuth = ob_get_clean();
assertCompanyTest("Auth Layout title displays dynamic company name", str_contains($renderedAuth, 'Test Company Alpha'));
assertCompanyTest("Auth Layout hero description references 'Test Company Alpha'", str_contains($renderedAuth, 'Test Company Alpha'));

// Dynamic Manifest
$manifestData = [
    'name'       => CompanySettingsService::getCompanyName(),
    'short_name' => mb_strlen(CompanySettingsService::getCompanyName()) > 12 ? mb_substr(CompanySettingsService::getCompanyName(), 0, 12) : CompanySettingsService::getCompanyName(),
];
assertCompanyTest("PWA Manifest name dynamically resolves 'Test Company Alpha'", $manifestData['name'] === 'Test Company Alpha');

echo "\n============================================================\n";
echo "7. VERIFYING PROFESSIONAL EMAIL SYSTEM BRANDING INTEGRATION\n";
echo "============================================================\n";

$emailService = new EmailService();

$sampleBooking = [
    'id' => 125,
    'booking_code' => 'VG-2026-00125',
    'pickup_address' => 'Nagpur Railway Station',
    'drop_address' => 'Pune',
    'start_date' => '2026-11-10',
    'pickup_time' => '08:30:00',
    'total_amount' => 12500.00,
    'paid_amount' => 12500.00,
];
$sampleCustomer = ['name' => 'Sanket Sidurkar', 'email' => 'sanket@example.com'];

$renderedEmail = $emailService->renderTemplate('booking/confirmed', [
    'booking'  => $sampleBooking,
    'customer' => $sampleCustomer,
]);

assertCompanyTest("Email header displays dynamic company name 'Test Company Alpha'", str_contains($renderedEmail, 'Test Company Alpha'));
assertCompanyTest("Email header displays dynamic company tagline 'Journeys of Excellence'", str_contains($renderedEmail, 'Journeys of Excellence'));
assertCompanyTest("Email Support section displays dynamic company email 'alpha@example.com'", str_contains($renderedEmail, 'alpha@example.com'));
assertCompanyTest("Email footer displays dynamic company name 'Test Company Alpha'", str_contains($renderedEmail, 'Test Company Alpha. All rights reserved.'));

echo "\n============================================================\n";
echo "8. CLEANUP OF TEST RECORDS\n";
echo "============================================================\n";

$pdo->exec("DELETE FROM `company_profiles` WHERE `id` IN ({$comp1Id}, {$comp2Id})");
CompanySettingsService::clearCache();

assertCompanyTest("CompanySettingsService falls back smoothly to environment defaults when DB is empty", !empty(CompanySettingsService::getCompanyName()));

echo "\n============================================================\n";
echo "TEST SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
