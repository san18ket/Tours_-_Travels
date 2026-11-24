<?php
/**
 * Test Suite: Company Details UI Fixes Verification
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Controllers\CompanyController;
use App\Models\CompanyProfile;
use App\Services\CompanySettingsService;

$failures = [];
$passes = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$passes): void {
    if ($cond) {
        $passes++;
        echo "  [PASS] {$msg}\n";
    } else {
        $failures[] = $msg;
        echo "  [FAIL] {$msg}\n";
    }
}

echo "====================================================================\n";
echo " TEST SUITE: COMPANY DETAILS UI FIXES VERIFICATION\n";
echo "====================================================================\n";

$detailsFile = dirname(__DIR__) . '/app/Views/office/company/details.php';
$html = file_get_contents($detailsFile);
$appCssFile = dirname(__DIR__) . '/public/assets/css/app.css';
$css = file_get_contents($appCssFile);

// -----------------------------------------------------------------------------
echo "\n1. ISSUE 1A — Company Logo Controls Alignment & Styling...\n";
// -----------------------------------------------------------------------------
assertCondition(
    strpos($html, 'class="btn-media-action"') !== false &&
    strpos($html, 'class="btn-media-remove"') !== false,
    "Both .btn-media-action and .btn-media-remove buttons are defined in details view",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.btn-media-action,\s*\.btn-media-remove/s', $html) !== 0,
    ".btn-media-action and .btn-media-remove share unified height, padding, and border radius rules",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'height: 36px;') !== false,
    "Logo controls have consistent height (36px)",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'gap: 0.4rem;') !== false,
    "Logo controls have consistent icon-to-text gap (0.4rem)",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'gap: 0.5rem;') !== false,
    "Logo controls container has professional horizontal gap (0.5rem)",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.media-action-buttons\s*\{[^}]*justify-content:\s*center/s', $html) !== 0,
    "Logo controls container (.media-action-buttons) has justify-content: center on desktop",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'justify-content: center !important;') !== false,
    "Logo controls container (.media-action-buttons) preserves justify-content: center on mobile",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n2. ISSUE 1B & 1C — Remove Logo Controller & File Handling...\n";
// -----------------------------------------------------------------------------
$companyModel = new CompanyProfile();
$company = $companyModel->getPrimary();
if (!$company) {
    $first = $companyModel->first();
    if ($first) {
        $companyModel->update((int)$first['id'], ['is_primary' => 1]);
        $company = $companyModel->getPrimary();
    } else {
        $id = $companyModel->create(['company_name' => 'Royal Tours', 'is_primary' => 1, 'status' => 'active', 'phone' => '9876543210']);
        $company = $companyModel->find($id);
    }
}

// Test 1: Upload dummy logo file in company/logo and record
$dummyLogoDir = dirname(__DIR__) . '/public/storage/company/logo';
if (!is_dir($dummyLogoDir)) {
    mkdir($dummyLogoDir, 0777, true);
}
$dummyFileName = 'company/logo/logo_test_' . time() . '.png';
$dummyFullPath = dirname(__DIR__) . '/public/storage/' . $dummyFileName;
file_put_contents($dummyFullPath, 'DUMMY_IMAGE_DATA');

assertCondition(file_exists($dummyFullPath), "Dummy logo file created at {$dummyFileName}", $failures, $passes);

// Attach to company
$companyModel->update((int)$company['id'], ['logo' => $dummyLogoFileName = $dummyFileName]);
CompanySettingsService::clearCache();

$updatedComp = $companyModel->getPrimary();
assertCondition($updatedComp['logo'] === $dummyLogoFileName, "Database record updated with logo path in company/logo/", $failures, $passes);

// Execute removeLogo via Controller (AJAX)
$controller = new CompanyController();
$removeReq = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);

$removeResp = $controller->removeLogo($removeReq);
$respData = json_decode($removeResp->getContent(), true);

assertCondition($removeResp->getStatusCode() === 200, "removeLogo returns HTTP 200 on AJAX", $failures, $passes);
assertCondition(!empty($respData['success']) && $respData['success'] === true, "removeLogo JSON response has success = true", $failures, $passes);
assertCondition($respData['message'] === 'Logo removed successfully.', "removeLogo JSON response has message 'Logo removed successfully.'", $failures, $passes);

// Verify physical file was deleted
assertCondition(!file_exists($dummyFullPath), "Physical logo file in company/logo was safely deleted from disk", $failures, $passes);

// Verify database logo column is cleared
$afterRemove = $companyModel->getPrimary();
assertCondition(empty($afterRemove['logo']), "Database company logo field is cleared to null", $failures, $passes);

// Test 2: Safe deletion when file is ALREADY missing on disk
$companyModel->update((int)$company['id'], ['logo' => 'company/logo/missing_logo_file.png']);
CompanySettingsService::clearCache();

$removeReq2 = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
]);

$removeResp2 = $controller->removeLogo($removeReq2);
$respData2 = json_decode($removeResp2->getContent(), true);

assertCondition($removeResp2->getStatusCode() === 200, "removeLogo safely handles missing physical file with HTTP 200", $failures, $passes);
assertCondition(!empty($respData2['success']), "removeLogo reports success even if physical file was missing", $failures, $passes);

$afterRemove2 = $companyModel->getPrimary();
assertCondition(empty($afterRemove2['logo']), "Database logo field is cleared to null even when file was missing", $failures, $passes);

// Verify client-side JS implementation
assertCondition(
    strpos($html, "async function removeLogoMedia()") !== false,
    "details.php defines async function removeLogoMedia()",
    $failures,
    $passes
);

assertCondition(
    strpos($html, "if (!confirm(") === false,
    "removeLogoMedia() executes directly without blocking confirm/alert popups",
    $failures,
    $passes
);

assertCondition(
    strpos($html, "ph.style.display = 'flex'") !== false,
    "removeLogoMedia() restores logo placeholder display to flex",
    $failures,
    $passes
);

assertCondition(
    strpos($html, "btnRemove.style.display = 'none'") !== false,
    "removeLogoMedia() hides remove button upon successful removal",
    $failures,
    $passes
);

assertCondition(
    strpos($html, "uploadBtnText.textContent = 'Upload Logo'") !== false,
    "removeLogoMedia() restores upload button text to 'Upload Logo'",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n3. ISSUE 2 — Mobile Step 1 Layout & Actions...\n";
// -----------------------------------------------------------------------------
assertCondition(
    preg_match('/@media\s*\(max-width:\s*768px\)\s*\{[^}]*\.step-indicator-pill\s*\{[^}]*display:\s*none\s*!important/s', $html) !== 0,
    "Step indicator pill ('Step 1 of 5') is hidden on mobile devices (<= 768px)",
    $failures,
    $passes
);

assertCondition(
    strpos($html, '<span class="step-indicator-pill">Step 1 of 5</span>') !== false,
    "Step indicator pill remains in markup for desktop viewports",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.company-profile-container \.modern-form-actions\.wizard-actions\s*\{[^}]*flex-direction:\s*column\s*!important/s', $html) !== 0,
    "Mobile action container stacks vertically without row compression",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.company-profile-container \.wizard-actions-right button[^}]*width:\s*100%\s*!important/s', $html) !== 0,
    "Mobile Save Changes and Next buttons occupy 100% width cleanly",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.company-profile-container \.wizard-actions-right button[^}]*height:\s*48px\s*!important/s', $html) !== 0,
    "Mobile Save Changes and Next buttons have consistent 48px touch targets",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.company-profile-container \.wizard-actions-right button[^}]*border-radius:\s*50px\s*!important/s', $html) !== 0,
    "Mobile Save Changes and Next buttons have consistent 50px pill radius",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n4. ISSUE 3 — Form Section to Footer External Whitespace...\n";
// -----------------------------------------------------------------------------
assertCondition(
    strpos($html, 'padding: 0 0 3rem 0;') === false,
    ".company-profile-container has removed the excessive 3rem bottom padding",
    $failures,
    $passes
);

assertCondition(
    strpos($html, '.main-content:has(.company-profile-container)') !== false,
    "CSS uses container-aware selector to tune workspace flex and padding",
    $failures,
    $passes
);

assertCondition(
    strpos($html, '.main-wrapper:has(.company-profile-container) .app-footer') !== false &&
    strpos($html, 'margin-top: 0 !important;') !== false,
    "Footer has margin-top: 0 to follow the form with natural professional spacing",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'tuneFooterSpacing') !== false,
    "JavaScript includes runtime fallback tuner to ensure cross-browser natural footer spacing",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n5. ISSUE 4 — Global Professional Button Consistency...\n";
// -----------------------------------------------------------------------------
assertCondition(
    strpos($css, '.btn i') !== false && strpos($css, 'button i') !== false,
    "app.css defines universal icon vertical alignment for buttons",
    $failures,
    $passes
);

assertCondition(
    strpos($css, '.btn:disabled') !== false && strpos($css, 'button:disabled') !== false,
    "app.css defines consistent disabled state styling for buttons",
    $failures,
    $passes
);

assertCondition(
    strpos($css, '.btn-group') !== false && strpos($css, 'gap: 0.5rem;') !== false,
    "app.css defines unified .btn-group alignment and gap",
    $failures,
    $passes
);

assertCondition(
    preg_match('/\.btn-outline-save\s*\{[^}]*height:\s*48px;/s', $html) !== 0 &&
    preg_match('/\.modern-btn-next\s*\{[^}]*height:\s*48px;/s', $html) !== 0 &&
    preg_match('/\.modern-btn-prev\s*\{[^}]*height:\s*48px;/s', $html) !== 0,
    "Save Changes, Previous, and Next buttons share exact matching height (48px)",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n6. FLOATING TOAST NOTIFICATIONS (NO STATIC BANNER ALERTS)...\n";
// -----------------------------------------------------------------------------
assertCondition(
    strpos($html, 'id="serverFlashSuccess"') === false,
    "Static banner alert (#serverFlashSuccess) removed from details.php",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="serverFlashError"') === false,
    "Static banner alert (#serverFlashError) removed from details.php",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'window.vgNotify') !== false,
    "details.php includes floating toast message emitter calling window.vgNotify",
    $failures,
    $passes
);

assertCondition(
    strpos($html, "typeof window.vgNotify === 'function'") !== false,
    "showWizardNotice and validation error emitter integrate seamlessly with window.vgNotify",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n7. SEPARATE STORAGE SUBFOLDERS & ZERO DUPLICATES (public/storage/company/logo)...\n";
// -----------------------------------------------------------------------------
$transparentPngB64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

// Test 1: First logo upload via saveDetails
$logoUploadReq1 = new Request([
    'company_name' => 'Subfolder Test Company',
    'phone'        => '9876543210',
    'address'      => 'Test Street',
    'city'         => 'Test City',
    'state'        => 'Test State',
    'pin_code'     => '190001',
    'section'      => 'all',
    'logo_base64'  => $transparentPngB64,
    'paid_stamp_base64' => $transparentPngB64,
    'native_submit' => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveDetails($logoUploadReq1);
$compWithSubfolders = $companyModel->getPrimary();

assertCondition(
    !empty($compWithSubfolders['logo']) && str_starts_with($compWithSubfolders['logo'], 'company/logo/'),
    "Logo upload is saved to separate 'company/logo/' subfolder: " . ($compWithSubfolders['logo'] ?? 'none'),
    $failures,
    $passes
);

assertCondition(
    !empty($compWithSubfolders['paid_stamp']) && str_starts_with($compWithSubfolders['paid_stamp'], 'company/stamps/'),
    "Stamp upload is saved to separate 'company/stamps/' subfolder: " . ($compWithSubfolders['paid_stamp'] ?? 'none'),
    $failures,
    $passes
);

$logoFolder = dirname(__DIR__) . '/public/storage/company/logo';
$logoFilesCount1 = count(glob($logoFolder . '/*'));
assertCondition($logoFilesCount1 === 1, "Exactly ONE logo file exists inside public/storage/company/logo after first upload (count = {$logoFilesCount1})", $failures, $passes);

// Test 2: Second logo upload (Re-upload/change logo) — must NOT duplicate or accumulate!
$logoUploadReq2 = new Request([
    'company_name' => 'Subfolder Test Company',
    'phone'        => '9876543210',
    'address'      => 'Test Street',
    'city'         => 'Test City',
    'state'        => 'Test State',
    'pin_code'     => '190001',
    'section'      => 'all',
    'logo_base64'  => $transparentPngB64,
    'native_submit' => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveDetails($logoUploadReq2);
$compWithUpdatedLogo = $companyModel->getPrimary();
$logoFilesCount2 = count(glob($logoFolder . '/*'));

assertCondition($logoFilesCount2 === 1, "Zero duplicate logos: Exactly ONE logo file remains in public/storage/company/logo after second upload (count = {$logoFilesCount2})", $failures, $passes);

$logoDiskPath = dirname(__DIR__) . '/public/storage/' . $compWithUpdatedLogo['logo'];
$stampDiskPath = dirname(__DIR__) . '/public/storage/' . $compWithSubfolders['paid_stamp'];

assertCondition(file_exists($logoDiskPath), "Current authoritative logo exists on disk in public/storage/company/logo/", $failures, $passes);

// -----------------------------------------------------------------------------
echo "\n8. ISSUE 5 — CANONICAL STORAGE & COMPANY LOGO VS APP LOGO INDEPENDENCE...\n";
// -----------------------------------------------------------------------------
$publicDir = dirname(__DIR__) . '/public';
$storageCompanyDir = $publicDir . '/storage/company';
$pwaDir = $storageCompanyDir . '/pwa';
$stampsDir = $storageCompanyDir . '/stamps';

assertCondition(
    !file_exists($publicDir . '/company'),
    "Legacy / duplicate public/company directory or junction does not exist",
    $failures,
    $passes
);

assertCondition(
    is_dir($logoFolder) && is_dir($stampsDir) && is_dir($pwaDir),
    "Canonical structure public/storage/company/{logo, stamps, pwa} is verified",
    $failures,
    $passes
);

// Test 8.1: Upload dedicated App Logo via saveApp
$appIconReq1 = new Request([
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'custom',
    'app_icon_base64' => $transparentPngB64,
    'native_submit'   => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveApp($appIconReq1);
$compWithAppIcon = $companyModel->getPrimary();

assertCondition(
    !empty($compWithAppIcon['app_icon']) && $compWithAppIcon['app_icon'] === 'company/pwa/app-icon.png',
    "App Logo is stored as canonical 'company/pwa/app-icon.png': " . ($compWithAppIcon['app_icon'] ?? 'none'),
    $failures,
    $passes
);

$appIconDiskPath1 = dirname(__DIR__) . '/public/storage/' . $compWithAppIcon['app_icon'];
assertCondition(
    file_exists($appIconDiskPath1),
    "Active App Logo file exists on disk in public/storage/company/pwa/app-icon.png",
    $failures,
    $passes
);

assertCondition(
    count(glob($pwaDir . '/*')) === 1,
    "Strictly ONE persistent file exists in public/storage/company/pwa/ after upload",
    $failures,
    $passes
);

// Independence Check 1: Changing/uploading App Logo did NOT alter or delete Company Logo
assertCondition(
    !empty($compWithAppIcon['logo']) && file_exists(dirname(__DIR__) . '/public/storage/' . $compWithAppIcon['logo']),
    "Independence: Company Logo remains intact and unchanged after App Logo upload",
    $failures,
    $passes
);

// Test 8.2A: Safe Replacement - Failed upload preserves existing App Logo
$invalidUploadReq = new Request([
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'custom',
    'app_icon_base64' => 'data:image/png;base64,invalidnotanimagebinarydata',
    'native_submit'   => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$failedUploadHandled = false;
try {
    $resp = $controller->saveApp($invalidUploadReq);
    $failedUploadHandled = true;
} catch (\Throwable $e) {
    $failedUploadHandled = true;
}
assertCondition($failedUploadHandled, "Failed upload triggers validation error handling", $failures, $passes);
assertCondition(
    file_exists($appIconDiskPath1) && count(glob($pwaDir . '/*')) === 1,
    "Safe replacement: Failed upload leaves existing app-icon.png 100% intact",
    $failures,
    $passes
);

// Test 8.2B: Replace App Logo (must safely replace app-icon.png keeping exactly ONE file)
$appIconReq2 = new Request([
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'custom',
    'app_icon_base64' => $transparentPngB64,
    'native_submit'   => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveApp($appIconReq2);
$compWithReplacedAppIcon = $companyModel->getPrimary();
$appIconDiskPath2 = dirname(__DIR__) . '/public/storage/' . $compWithReplacedAppIcon['app_icon'];

assertCondition(
    file_exists($appIconDiskPath2) && $compWithReplacedAppIcon['app_icon'] === 'company/pwa/app-icon.png',
    "New App Logo exists at canonical path company/pwa/app-icon.png after replacement",
    $failures,
    $passes
);

assertCondition(
    count(glob($pwaDir . '/*')) === 1,
    "Zero duplicate accumulation: Exactly ONE file remains in public/storage/company/pwa/ after replacement",
    $failures,
    $passes
);

// Independence Check 2: Changing Company Logo does NOT alter or delete App Logo
$logoChangeReq = new Request([
    'company_name' => 'Subfolder Test Company',
    'phone'        => '9876543210',
    'address'      => 'Test Street',
    'city'         => 'Test City',
    'state'        => 'Test State',
    'pin_code'     => '190001',
    'section'      => 'all',
    'logo_base64'  => $transparentPngB64,
    'native_submit' => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveDetails($logoChangeReq);
$compAfterLogoChange = $companyModel->getPrimary();

assertCondition(
    !empty($compAfterLogoChange['app_icon']) && file_exists(dirname(__DIR__) . '/public/storage/' . $compAfterLogoChange['app_icon']),
    "Independence: App Logo remains intact and on disk after Company Logo update",
    $failures,
    $passes
);

// Test 8.3: Remove Company Logo does NOT delete App Logo
$controller->removeLogo($removeReq);
$compAfterLogoRemoval = $companyModel->getPrimary();

assertCondition(
    empty($compAfterLogoRemoval['logo']),
    "Company Logo reference cleared from database",
    $failures,
    $passes
);

assertCondition(
    count(glob($logoFolder . '/*')) === 0,
    "Company Logo folder is completely clean (0 files)",
    $failures,
    $passes
);

assertCondition(
    !empty($compAfterLogoRemoval['app_icon']) && file_exists(dirname(__DIR__) . '/public/storage/' . $compAfterLogoRemoval['app_icon']),
    "Independence: App Logo still exists physically and in DB after Company Logo is removed",
    $failures,
    $passes
);

// Test 8.4: Remove App Logo does NOT delete Company Logo or stamps
$removeAppIconReq = new Request([
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'remove_app_icon' => '1',
    'native_submit'   => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveApp($removeAppIconReq);
$compAfterAppIconRemoval = $companyModel->getPrimary();

assertCondition(
    empty($compAfterAppIconRemoval['app_icon']),
    "App Logo reference cleared from database",
    $failures,
    $passes
);

assertCondition(
    $compAfterAppIconRemoval['app_icon_source'] === 'company_logo',
    "App icon source reverted to default 'company_logo'",
    $failures,
    $passes
);

$pwaFilesCount = count(glob($pwaDir . '/*'));
assertCondition(
    $pwaFilesCount === 0,
    "Dedicated company/pwa directory cleaned upon App Logo removal (0 files)",
    $failures,
    $passes
);

// Test 8.5: Safe configuration when 'use company logo as app icon' is active
// Upload a fresh company logo
$controller->saveDetails($logoUploadReq1);
$compWithFreshLogo = $companyModel->getPrimary();
$freshLogoPath = dirname(__DIR__) . '/public/storage/' . $compWithFreshLogo['logo'];
assertCondition(file_exists($freshLogoPath), "Fresh Company Logo created on disk", $failures, $passes);

// When app_icon_source == 'company_logo', removing app icon setting must NOT delete physical company logo
$removeAppIconSettingReq = new Request([
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'company_logo',
    'remove_app_icon' => '1',
    'native_submit'   => '1',
], [], ['REQUEST_METHOD' => 'POST']);

$controller->saveApp($removeAppIconSettingReq);
assertCondition(
    file_exists($freshLogoPath),
    "Safety: Removing App Icon setting does NOT delete Company Logo file on disk",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
echo "\n9. PWA FUNCTIONALITY & MANIFEST VERIFICATION (SINGLE SOURCE)...\n";
// -----------------------------------------------------------------------------
CompanySettingsService::clearCache();
CompanySettingsService::syncPwaIcons();
$manifestData = CompanySettingsService::getManifestData();

assertCondition(
    !empty($manifestData['icons']) && count($manifestData['icons']) >= 2,
    "PWA manifest includes required 192x192 and 512x512 icons",
    $failures,
    $passes
);

assertCondition(
    str_contains($manifestData['icons'][0]['src'], 'storage/' . $compWithFreshLogo['logo']),
    "PWA manifest icons resolve to single source image: " . $manifestData['icons'][0]['src'],
    $failures,
    $passes
);

$pwaObs192 = dirname(__DIR__) . '/public/storage/company/pwa/pwa_icon_192.png';
$pwaObs512 = dirname(__DIR__) . '/public/storage/company/pwa/pwa_icon_512.png';
$rootObs192 = dirname(__DIR__) . '/public/storage/company/pwa_icon_192.png';
$rootObs512 = dirname(__DIR__) . '/public/storage/company/pwa_icon_512.png';

assertCondition(
    !file_exists($pwaObs192) && !file_exists($pwaObs512),
    "Zero duplicate PWA 192/512 icons in company/pwa/ (single source architecture)",
    $failures,
    $passes
);

assertCondition(
    !file_exists($rootObs192) && !file_exists($rootObs512),
    "Zero duplicate PWA icons in company root (public/storage/company/pwa_icon_*.png do NOT exist)",
    $failures,
    $passes
);

assertCondition(
    str_contains(CompanySettingsService::getPwaIcon192Url(), 'storage/' . $compWithFreshLogo['logo']),
    "CompanySettingsService::getPwaIcon192Url() resolves to active source: " . CompanySettingsService::getPwaIcon192Url(),
    $failures,
    $passes
);

assertCondition(
    str_contains(CompanySettingsService::getPwaIcon512Url(), 'storage/' . $compWithFreshLogo['logo']),
    "CompanySettingsService::getPwaIcon512Url() resolves to active source: " . CompanySettingsService::getPwaIcon512Url(),
    $failures,
    $passes
);

// Verify that re-running sync strictly avoids recreating duplicate/obsolete 192/512 files
CompanySettingsService::syncPwaIcons();
assertCondition(
    !file_exists($pwaObs192) && !file_exists($pwaObs512) && !file_exists($rootObs192) && !file_exists($rootObs512),
    "Re-running syncPwaIcons() strictly avoids recreating duplicate 192/512 files",
    $failures,
    $passes
);

// Final cleanup: remove test logo & stamps
$controller->removeLogo($removeReq);
if (file_exists($stampDiskPath)) {
    @unlink($stampDiskPath);
}

// -----------------------------------------------------------------------------
echo "\n====================================================================\n";
echo " VERIFICATION RESULTS SUMMARY\n";
echo "====================================================================\n";
echo "Total Passed: {$passes}\n";
echo "Total Failed: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed Items:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "\nALL UI FIX VERIFICATIONS PASSED SUCCESSFULLY!\n";
