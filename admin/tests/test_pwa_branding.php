<?php
/**
 * Comprehensive Automated Verification for Professional PWA App Branding & Installability
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Models\CompanyProfile;
use App\Services\CompanySettingsService;

echo "=========================================================\n";
echo "PWA BRANDING & INSTALLABILITY COMPREHENSIVE VERIFICATION\n";
echo "=========================================================\n\n";

$companyModel = new CompanyProfile();
$company = $companyModel->getPrimary();

if (!$company) {
    echo "ERROR: Primary company profile not found!\n";
    exit(1);
}

$origLogo = $company['logo'] ?? null;
$origSource = $company['app_icon_source'] ?? 'company_logo';
$origAppIcon = $company['app_icon'] ?? null;

echo "Initial State:\n";
echo "Company Name: " . ($company['company_name'] ?? 'N/A') . "\n";
echo "Company Logo: " . ($origLogo ?? 'NONE') . "\n";
echo "App Icon Source: " . ($origSource ?? 'N/A') . "\n";
echo "App Icon: " . ($origAppIcon ?? 'NONE') . "\n\n";

// TEST 1: Company Logo Mode Single Source
echo "--- TEST 1: Company Logo Mode PWA (Single Source) ---\n";
// Create a temporary company logo if none exists so test has a deterministic source
$createdTempLogo = false;
$tempLogoPath = dirname(__DIR__) . '/public/storage/company/logo/company-logo.png';
if (empty($origLogo) || !file_exists(dirname(__DIR__) . '/public/storage/' . $origLogo)) {
    if (!is_dir(dirname($tempLogoPath))) {
        @mkdir(dirname($tempLogoPath), 0777, true);
    }
    $cLogo = imagecreatetruecolor(256, 256);
    $blue = imagecolorallocate($cLogo, 30, 62, 98);
    imagefilledrectangle($cLogo, 0, 0, 256, 256, $blue);
    imagepng($cLogo, $tempLogoPath);
    imagedestroy($cLogo);
    $companyModel->update((int)$company['id'], [
        'logo' => 'company/logo/company-logo.png',
    ]);
    $createdTempLogo = true;
}

$companyModel->update((int)$company['id'], [
    'app_icon_source' => 'company_logo',
    'app_icon'        => null,
]);
CompanySettingsService::clearCache();
CompanySettingsService::syncPwaIcons();

$manifest = CompanySettingsService::getManifestData();
assert(!empty($manifest['name']), "Manifest name is not empty");
assert(!empty($manifest['icons']), "Manifest icons are not empty");
assert(count($manifest['icons']) >= 2, "Manifest has at least 2 icon variants");

$pwa192File = dirname(__DIR__) . '/public/storage/company/pwa/pwa_icon_192.png';
$pwa512File = dirname(__DIR__) . '/public/storage/company/pwa/pwa_icon_512.png';
$legacy192 = dirname(__DIR__) . '/public/storage/company/pwa_icon_192.png';
$legacy512 = dirname(__DIR__) . '/public/storage/company/pwa_icon_512.png';

// In single source architecture, duplicate permanent 192/512 files must NOT exist
assert(!file_exists($pwa192File), "Permanent duplicate pwa_icon_192.png does not exist in company/pwa/");
assert(!file_exists($pwa512File), "Permanent duplicate pwa_icon_512.png does not exist in company/pwa/");
assert(!file_exists($legacy192), "Obsolete root-level pwa_icon_192.png does not exist in company/");
assert(!file_exists($legacy512), "Obsolete root-level pwa_icon_512.png does not exist in company/");

echo "✓ Company Logo mode successfully uses single source without duplicate disk copies.\n";
echo "✓ Manifest resolved dynamically with company name: '{$manifest['name']}'\n\n";

// TEST 2: Custom App Icon Mode (Single Source: company/pwa/app-icon.png)
echo "--- TEST 2: Custom App Icon Mode (Single Source: company/pwa/app-icon.png) ---\n";
$pwaDir = dirname(__DIR__) . '/public/storage/company/pwa';
if (!is_dir($pwaDir)) {
    @mkdir($pwaDir, 0777, true);
}
$customImgPath = $pwaDir . '/app-icon.png';
$testCanvas = imagecreatetruecolor(512, 512);
$gold = imagecolorallocate($testCanvas, 214, 183, 122);
imagefilledrectangle($testCanvas, 0, 0, 512, 512, $gold);
imagepng($testCanvas, $customImgPath);
imagedestroy($testCanvas);

$companyModel->update((int)$company['id'], [
    'app_icon_source' => 'custom',
    'app_icon'        => 'company/pwa/app-icon.png',
]);
CompanySettingsService::clearCache();
CompanySettingsService::syncPwaIcons();

assert(CompanySettingsService::getAppIconSource() === 'custom', "App icon source is custom");
assert(CompanySettingsService::hasCustomAppIcon() === true, "Has custom app icon on disk");
assert(count(glob($pwaDir . '/*')) === 1, "Exactly ONE persistent App Logo exists in company/pwa/");

$manifest2 = CompanySettingsService::getManifestData();
assert(str_contains($manifest2['icons'][0]['src'], 'company/pwa/app-icon.png'), "Manifest icon references single source app-icon.png");
echo "✓ Custom App Icon mode uses single source company/pwa/app-icon.png (count = 1).\n\n";

// TEST 3: Removal / Reset Behavior
echo "--- TEST 3: Removal / Reset Behavior ---\n";
$currentCompany = $companyModel->getPrimary();
$logoBefore = !empty($currentCompany['logo']) ? dirname(__DIR__) . '/public/storage/' . $currentCompany['logo'] : null;
if ($logoBefore) {
    assert(file_exists($logoBefore), "Company logo exists before custom icon removal");
}

// Simulate removal:
@unlink($customImgPath);
$companyModel->update((int)$company['id'], [
    'app_icon_source' => 'company_logo',
    'app_icon'        => null,
]);
CompanySettingsService::clearCache();
CompanySettingsService::syncPwaIcons();

if ($logoBefore) {
    assert(file_exists($logoBefore), "Company logo MUST STILL EXIST after custom app icon removal");
}
assert(CompanySettingsService::getAppIconSource() === 'company_logo', "Reverted to company_logo mode");
assert(count(glob($pwaDir . '/*')) === 0, "company/pwa/ directory is completely clean after app icon removal (0 files)");

echo "✓ Custom icon removal safely reverted to Company Logo without touching original company logo.\n\n";

// Cleanup temp logo if created
if ($createdTempLogo && file_exists($tempLogoPath)) {
    @unlink($tempLogoPath);
    $companyModel->update((int)$company['id'], [
        'logo' => $origLogo,
    ]);
}

// TEST 4: Manifest File On Disk
echo "--- TEST 4: Static public/manifest.json on Disk ---\n";
$diskManifestContent = file_get_contents(dirname(__DIR__) . '/public/manifest.json');
$diskManifest = json_decode($diskManifestContent, true);

assert(!empty($diskManifest['name']), "Disk manifest has dynamic name");
assert($diskManifest['display'] === 'standalone', "Display is standalone");
assert(!empty($diskManifest['icons']), "Disk manifest has icons");
echo "✓ public/manifest.json on disk is synchronized and valid JSON.\n\n";

// TEST 5: Office Company View Rendering
echo "--- TEST 5: Office Company View Rendering ---\n";
$view = new \App\Core\View();
$renderedHtml = $view->render('office/company/index', [
    'company'        => $company,
    'primaryCompany' => $company,
    'old'            => [],
    'errors'         => [],
    'success'        => null,
], 'layouts/office');

assert(str_contains($renderedHtml, 'PWA App Icon'), "View contains PWA App Icon section");
assert(str_contains($renderedHtml, 'sourceCompanyLogo'), "View contains Company Logo radio");
assert(str_contains($renderedHtml, 'sourceCustom'), "View contains Custom App Icon radio");
assert(str_contains($renderedHtml, 'pwaPreviewImg'), "View contains PWA Preview image");
echo "✓ /office/company view rendered successfully (" . strlen($renderedHtml) . " bytes).\n\n";

// RESTORE original state
$companyModel->update((int)$company['id'], [
    'app_icon_source' => $origSource,
    'app_icon'        => $origAppIcon,
]);
CompanySettingsService::clearCache();
CompanySettingsService::syncPwaIcons();

echo "=========================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY! PWA BRANDING IS FULLY FUNCTIONAL.\n";
echo "=========================================================\n";
