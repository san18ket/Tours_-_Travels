<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Services\CompanySettingsService;
use App\Models\CompanyProfile;

echo "======================================================================\n";
echo "   DYNAMIC SIDEBAR & LOGIN PAGE BRANDING TEST SUITE\n";
echo "======================================================================\n";

$pdo = Database::getConnection();
$savedProfiles = $pdo->query("SELECT * FROM `company_profiles`")->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------------------------------------------------
// 1. SCENARIO A: Company Name is NOT available (Unconfigured / Empty DB)
// ----------------------------------------------------------------------
echo "\n--- SCENARIO 1: Company Profile is NOT available (Unconfigured) ---\n";
$pdo->exec("DELETE FROM `company_profiles`");
CompanySettingsService::clearCache();

assertCondition(!has_company_name(), "has_company_name() reports false when DB is unconfigured");
assertCondition(company_display_name() === '(company name)', "company_display_name() returns '(company name)'");

// Render Office Layout (Sidebar Header)
ob_start();
$title = 'Test Dashboard';
$content = '<p>Content</p>';
require dirname(__DIR__) . '/app/Views/layouts/office.php';
$officeHtml = ob_get_clean();

assertCondition(strpos($officeHtml, 'manaswi-logo.svg') === false, "Sidebar does NOT display static logo image");
assertCondition(strpos($officeHtml, 'brand-logo-icon-badge') !== false && strpos($officeHtml, 'fa-building') !== false, "Sidebar displays icon instead of logo");
assertCondition(strpos($officeHtml, '(company name)') !== false, "Sidebar displays '(company name)' instead of static name");
assertCondition(!preg_match('/class="brand-title"[^>]*>MANASWI<\/span>/i', $officeHtml), "Sidebar does NOT display static 'MANASWI' name");

// Render Login Page & Auth Layout
ob_start();
$title = 'Sign In';
require dirname(__DIR__) . '/app/Views/auth/login.php';
$loginCard = ob_get_clean();

ob_start();
$content = $loginCard;
require dirname(__DIR__) . '/app/Views/layouts/auth.php';
$authHtml = ob_get_clean();

assertCondition(strpos($authHtml, 'manaswi-emblem.png') === false, "Login page does NOT display static 'manaswi-emblem.png'");
assertCondition(strpos($authHtml, 'manaswi-icon.png') === false, "Login card does NOT display static 'manaswi-icon.png'");
assertCondition(strpos($authHtml, 'desktop-brand-icon-badge') !== false && strpos($authHtml, 'fa-building') !== false, "Login page displays icon instead of static emblem");
assertCondition(strpos($authHtml, 'glass-card-icon-badge') !== false, "Login card displays icon badge instead of static emblem");
assertCondition(strpos($authHtml, '(company name)') !== false, "Login page displays '(company name)' instead of static company name");
assertCondition(!preg_match('/class="desktop-brand-name"[^>]*>MANASWI<\/span>/i', $authHtml), "Login page does NOT display static 'MANASWI' text");

// ----------------------------------------------------------------------
// 2. SCENARIO B: Company Name is available, but NO custom logo uploaded
// ----------------------------------------------------------------------
echo "\n--- SCENARIO 2: Company Name configured, NO logo uploaded ---\n";
$model = new CompanyProfile();
$model->create([
    'company_name' => 'Royal Express Travels',
    'tagline'      => 'Travel in Style',
    'status'       => 'active',
    'is_primary'   => 1,
    'logo'         => null,
]);
CompanySettingsService::clearCache();

assertCondition(has_company_name(), "has_company_name() reports true");
assertCondition(!has_company_logo(), "has_company_logo() reports false");
assertCondition(company_display_name() === 'Royal Express Travels', "company_display_name() returns 'Royal Express Travels'");

// Render Office Layout
ob_start();
$content = '<p>Content</p>';
require dirname(__DIR__) . '/app/Views/layouts/office.php';
$officeHtml2 = ob_get_clean();

assertCondition(strpos($officeHtml2, 'Royal Express Travels') !== false, "Sidebar displays dynamic company name 'Royal Express Travels'");
assertCondition(strpos($officeHtml2, 'Travel in Style') !== false, "Sidebar displays dynamic tagline 'Travel in Style'");
assertCondition(strpos($officeHtml2, 'brand-logo-icon-badge') !== false, "Sidebar displays icon instead of static logo when no logo uploaded");

// Render Login Page
ob_start();
require dirname(__DIR__) . '/app/Views/auth/login.php';
$loginCard2 = ob_get_clean();

ob_start();
$content = $loginCard2;
require dirname(__DIR__) . '/app/Views/layouts/auth.php';
$authHtml2 = ob_get_clean();

assertCondition(strpos($authHtml2, 'Royal Express Travels') !== false, "Login page displays dynamic company name 'Royal Express Travels'");
assertCondition(strpos($authHtml2, 'Travel in Style') !== false, "Login page displays dynamic tagline 'Travel in Style'");
assertCondition(strpos($authHtml2, 'desktop-brand-icon-badge') !== false, "Login page displays icon instead of static logo when no logo uploaded");

// ----------------------------------------------------------------------
// 3. SCENARIO C: Company Name configured AND custom logo uploaded
// ----------------------------------------------------------------------
echo "\n--- SCENARIO 3: Company Name configured WITH custom logo ---\n";
// Create dummy physical logo
$dummyDir = dirname(__DIR__) . '/public/storage/company';
if (!is_dir($dummyDir)) {
    mkdir($dummyDir, 0755, true);
}
$dummyLogoFile = $dummyDir . '/test_brand_logo.png';
file_put_contents($dummyLogoFile, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

$pdo->exec("UPDATE `company_profiles` SET `logo` = 'company/test_brand_logo.png' WHERE `company_name` = 'Royal Express Travels'");
CompanySettingsService::clearCache();

assertCondition(has_company_logo(), "has_company_logo() reports true when file exists on disk");
$logoUrl = company_logo_url();

// Render Office Layout
ob_start();
$content = '<p>Content</p>';
require dirname(__DIR__) . '/app/Views/layouts/office.php';
$officeHtml3 = ob_get_clean();

assertCondition(strpos($officeHtml3, $logoUrl) !== false, "Sidebar header displays dynamic uploaded logo URL");
assertCondition(strpos($officeHtml3, 'Royal Express Travels') !== false, "Sidebar header displays dynamic company name");

// Render Login Page
ob_start();
require dirname(__DIR__) . '/app/Views/auth/login.php';
$loginCard3 = ob_get_clean();

ob_start();
$content = $loginCard3;
require dirname(__DIR__) . '/app/Views/layouts/auth.php';
$authHtml3 = ob_get_clean();

assertCondition(strpos($authHtml3, $logoUrl) !== false, "Login page header displays dynamic uploaded logo URL");
assertCondition(strpos($loginCard3, $logoUrl) !== false, "Login card emblem displays dynamic uploaded logo URL");

// Cleanup dummy logo
@unlink($dummyLogoFile);

// ----------------------------------------------------------------------
// 4. RESTORE ORIGINAL PROFILE
// ----------------------------------------------------------------------
$pdo->exec("DELETE FROM `company_profiles`");
foreach ($savedProfiles as $row) {
    $keys = array_keys($row);
    $cols = implode(', ', array_map(function($k){ return "`{$k}`"; }, $keys));
    $placeholders = implode(', ', array_fill(0, count($keys), '?'));
    $stmt = $pdo->prepare("INSERT INTO `company_profiles` ({$cols}) VALUES ({$placeholders})");
    $stmt->execute(array_values($row));
}
CompanySettingsService::clearCache();

echo "\n======================================================================\n";
echo "SUCCESS: ALL DYNAMIC SIDEBAR & LOGIN BRANDING TESTS PASSED 100%!\n";
echo "======================================================================\n";

function assertCondition(bool $cond, string $msg): void {
    if ($cond) {
        echo "  [PASS] {$msg}\n";
    } else {
        echo "  [FAIL] {$msg}\n";
        exit(1);
    }
}
