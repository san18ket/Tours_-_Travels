<?php
/**
 * Test Suite: Professional App Details UI & Default Inherit / Custom Overrides
 * 
 * Verifies:
 * 1. Toggle switch 'Use Company Name as App Name' (chk_same_as_company) is removed.
 * 2. Radio cards 'Use Company Logo' / 'Use Different App Icon' (sourceCompanyLogo, sourceCustom) are removed.
 * 3. Default state uses Company Name and Company Logo.
 * 4. Custom App Name override correctly sets app_name_source = 'custom' and saves app_name.
 * 5. Clearing App Name automatically defaults back to Company Name (app_name_source = 'company_name', app_name = null).
 * 6. PWA App Icon upload and revert workflows cleanly switch between custom icon and default Company Logo.
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
echo " TEST SUITE: PROFESSIONAL APP DETAILS UI & DEFAULT INHERITANCE\n";
echo "====================================================================\n\n";

$appViewPath = __DIR__ . '/../app/Views/office/company/app.php';
$html = file_get_contents($appViewPath);

echo "1. Verifying Removal of Redundant Controls & Cluttered Text...\n";
assertCondition(
    strpos($html, 'chk_same_as_company') === false,
    "Switch toggle 'chk_same_as_company' has been completely removed",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="sourceCompanyLogo"') === false && strpos($html, 'id="sourceCustom"') === false,
    "Radio cards #sourceCompanyLogo and #sourceCustom have been completely removed",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'Custom App Icon (Active)') === false &&
    strpos($html, 'Application Home Screen Icon') === false &&
    strpos($html, 'By default, your application automatically uses your Company Logo, properly centered and padded') === false &&
    strpos($html, 'When a custom icon is active, it directly powers your web app manifest') === false,
    "Cluttered text and redundant descriptions have been removed from PWA App Icon section",
    $failures,
    $passes
);

echo "\n2. Verifying Professional Step-Wise Wizard UI Structure...\n";
assertCondition(
    strpos($html, 'class="modern-stepper-container"') !== false &&
    strpos($html, 'class="modern-stepper-track"') !== false,
    "Modern step-wise stepper navigation header is present",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="wizardPanel1"') !== false &&
    strpos($html, 'id="wizardPanel2"') !== false,
    "All 2 Step Wizard panels (#wizardPanel1 and #wizardPanel2) are present",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'name="app_icon"') !== false &&
    strpos($html, 'name="app_name"') !== false &&
    strpos($html, 'name="app_version"') !== false,
    "Step 1 contains PWA app icon upload, App Name input, and App Version input",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'mockupIconImg') !== false &&
    strpos($html, 'mockupAppTitle') !== false &&
    strpos($html, 'id="btnSaveApp"') !== false,
    "Step 2 contains live PWA home screen preview mockup and Save button",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="appNameBadge"') !== false,
    "Dynamic App Name identity badge #appNameBadge is present",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="btnResetAppName"') !== false,
    "Revert to Company Name action button #btnResetAppName is present",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'avatar-preview-container') !== false,
    "Clean upload preview container (.avatar-preview-container) matching company/details is present",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="btnRemoveAppIcon"') !== false && strpos($html, 'id="btnUndoAppIcon"') !== false,
    "Revert and Undo actions for PWA App Icon are present",
    $failures,
    $passes
);

echo "\n3. Verifying JavaScript Live Feedback Handlers...\n";
assertCondition(
    strpos($html, 'function onAppNameInput(') !== false,
    "JavaScript defines live onAppNameInput() handler",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'function resetToCompanyName(') !== false,
    "JavaScript defines resetToCompanyName() handler",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'function handleAppIconChange(') !== false,
    "JavaScript defines handleAppIconChange() file preview handler",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'function removeAppIconMedia(') !== false,
    "JavaScript defines removeAppIconMedia() to revert to company logo",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'function compareSemVer(') !== false &&
    strpos($html, 'Version cannot be decreased') !== false,
    "JavaScript defines compareSemVer() and enforces non-decreasing App Version validation",
    $failures,
    $passes
);

echo "\n4. Verifying Controller Save Logic: Custom App Name vs Default Company Name...\n";
$controller = new CompanyController();
$model = new CompanyProfile();
$company = $model->getPrimary();

// Test A: Set custom app name
$reqCustom = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name'        => 'Custom Explorer Pro',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);

$resCustom = $controller->saveApp($reqCustom);
$updated = $model->getPrimary();

assertCondition(
    $resCustom->getStatusCode() === 302 &&
    $updated['app_name_source'] === 'custom' &&
    $updated['app_name'] === 'Custom Explorer Pro',
    "Entering a custom App Name sets app_name_source = 'custom' and saves 'Custom Explorer Pro'",
    $failures,
    $passes
);

// Test B: Clear app name (empty string) -> should automatically default back to company_name
$reqDefault = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name'        => '',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);

$resDefault = $controller->saveApp($reqDefault);
$updated2 = $model->getPrimary();

assertCondition(
    $resDefault->getStatusCode() === 302 &&
    $updated2['app_name_source'] === 'company_name' &&
    empty($updated2['app_name']),
    "Clearing App Name automatically defaults back to company_name with null custom name",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::getEffectiveAppName() === $updated2['company_name'],
    "CompanySettingsService::getEffectiveAppName() returns company name (" . $updated2['company_name'] . ") by default",
    $failures,
    $passes
);

// Test C: PWA Icon default vs revert
$reqRevertIcon = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name'        => '',
    'app_version'     => '2.4.0',
    'remove_app_icon' => '1',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);

$resRevertIcon = $controller->saveApp($reqRevertIcon);
$updated3 = $model->getPrimary();

assertCondition(
    $updated3['app_icon_source'] === 'company_logo' &&
    empty($updated3['app_icon']),
    "Reverting app icon sets app_icon_source = 'company_logo' and clears app_icon",
    $failures,
    $passes
);

// Test D: Rejecting version decrease
$reqBump = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name'        => '',
    'app_version'     => '2.4.1',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);
$controller->saveApp($reqBump);

$reqDecrease = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name'        => '',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);
$resDecrease = $controller->saveApp($reqDecrease);
$flashErr = \App\Core\Session::getFlash('errors');

assertCondition(
    !empty($flashErr['app_version']) &&
    CompanySettingsService::getAppVersion() === '2.4.1',
    "Attempting to decrease App Version (from 2.4.1 to 2.4.0) is rejected with error message",
    $failures,
    $passes
);

// Reset back to 2.4.0 directly via model
$model->update((int)$company['id'], ['app_version' => '2.4.0', 'app_name' => null, 'app_name_source' => 'company_name']);
CompanySettingsService::clearCache();

echo "\n====================================================================\n";
echo " TEST RESULTS SUMMARY\n";
echo "====================================================================\n";
echo "Total Passed: {$passes}\n";
echo "Total Failed: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed Tests:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "\nALL PROFESSIONAL APP DETAILS TESTS PASSED SUCCESSFULLY!\n";
exit(0);
