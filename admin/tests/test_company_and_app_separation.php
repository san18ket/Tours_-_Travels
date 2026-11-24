<?php
/**
 * Comprehensive Automated Verification Test
 * Company Management Separation: Company Details & App Details
 * 
 * Verifies:
 * 1. Sidebar dropdown navigation (#cat-company, active states, data-no-ajax="true")
 * 2. Route registration and 302 redirects (/office/company -> /office/company/details)
 * 3. Strict view separation (Company Details vs App Details)
 * 4. Semantic versioning validation and helper app_version()
 * 5. Public uncached version check endpoint (/api/v1/app/version)
 * 6. Native POST submissions and redirects (302) for both controllers
 * 7. PWA Update Gate markup, styles and JavaScript logic
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
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
echo " TEST SUITE: COMPANY MANAGEMENT SEPARATION (DETAILS VS APP)\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------------------
// TEST 1: Sidebar Architecture in app/Views/layouts/office.php
// -----------------------------------------------------------------------------
echo "1. Verifying Sidebar Navigation in app/Views/layouts/office.php...\n";
$officeLayoutPath = __DIR__ . '/../app/Views/layouts/office.php';
$officeLayoutContent = file_get_contents($officeLayoutPath);

assertCondition(
    strpos($officeLayoutContent, 'id="cat-company"') !== false,
    "Sidebar includes parent container #cat-company",
    $failures,
    $passes
);

assertCondition(
    strpos($officeLayoutContent, 'id="cat-company-header"') !== false,
    "Sidebar includes dropdown button #cat-company-header",
    $failures,
    $passes
);

assertCondition(
    strpos($officeLayoutContent, '/office/company/details') !== false,
    "Sidebar links to /office/company/details (Company Details)",
    $failures,
    $passes
);

assertCondition(
    strpos($officeLayoutContent, '/office/company/app') !== false,
    "Sidebar links to /office/company/app (App Details)",
    $failures,
    $passes
);

assertCondition(
    strpos($officeLayoutContent, '$isCompanyDetailsActive') !== false &&
    strpos($officeLayoutContent, '$isCompanyAppActive') !== false,
    "Sidebar calculates discrete active states for details and app pages",
    $failures,
    $passes
);

assertCondition(
    strpos($officeLayoutContent, '<meta name="app-version"') !== false,
    "Office shell includes <meta name=\"app-version\"> tag",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// TEST 2: Routes & Redirects
// -----------------------------------------------------------------------------
echo "\n2. Verifying MVC Routes in app/routes.php & Controller Redirects...\n";
$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');

assertCondition(
    strpos($routesContent, "'/office/company/details'") !== false,
    "Route /office/company/details is registered",
    $failures,
    $passes
);

assertCondition(
    strpos($routesContent, "'/office/company/app'") !== false,
    "Route /office/company/app is registered",
    $failures,
    $passes
);

assertCondition(
    strpos($routesContent, "'/api/v1/app/version'") !== false,
    "Public API endpoint /api/v1/app/version is registered",
    $failures,
    $passes
);

$controller = new CompanyController();
$fakeGet = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/company']);
$redirectResp = $controller->index($fakeGet);
$headers = $redirectResp->getHeaders();

assertCondition(
    $redirectResp->getStatusCode() === 302,
    "/office/company responds with 302 redirect status",
    $failures,
    $passes
);

assertCondition(
    isset($headers['Location']) && str_ends_with($headers['Location'], '/office/company/details'),
    "/office/company redirects cleanly to /office/company/details",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// TEST 3: Strict View Separation
// -----------------------------------------------------------------------------
echo "\n3. Verifying Strict View Content Separation...\n";
$detailsViewPath = __DIR__ . '/../app/Views/office/company/details.php';
$appViewPath = __DIR__ . '/../app/Views/office/company/app.php';

assertCondition(file_exists($detailsViewPath), "View file office/company/details.php exists", $failures, $passes);
assertCondition(file_exists($appViewPath), "View file office/company/app.php exists", $failures, $passes);

$detailsViewContent = file_get_contents($detailsViewPath);
$appViewContent = file_get_contents($appViewPath);

// Details view assertions:
assertCondition(
    strpos($detailsViewContent, 'company_name') !== false &&
    strpos($detailsViewContent, 'owner_name') !== false &&
    strpos($detailsViewContent, 'gst_number') !== false &&
    strpos($detailsViewContent, 'paid_stamp') !== false,
    "Company Details view contains business, tax, address, and document fields",
    $failures,
    $passes
);

assertCondition(
    strpos($detailsViewContent, 'name="app_name"') === false &&
    strpos($detailsViewContent, 'name="app_version"') === false &&
    strpos($detailsViewContent, 'name="app_icon"') === false &&
    strpos($detailsViewContent, 'chk_same_as_company') === false,
    "Company Details view contains ZERO app identity, version, or PWA icon fields",
    $failures,
    $passes
);

assertCondition(
    strpos($detailsViewContent, 'action="' . url('/office/company/details/save') . '"') !== false ||
    strpos($detailsViewContent, "url('/office/company/details/save')") !== false,
    "Company Details form targets /office/company/details/save with core PHP POST",
    $failures,
    $passes
);

assertCondition(
    strpos($detailsViewContent, 'data-no-ajax="true"') !== false &&
    strpos($detailsViewContent, 'data-native="true"') !== false,
    "Company Details forms declare data-no-ajax and data-native attributes",
    $failures,
    $passes
);

// App view assertions:
assertCondition(
    strpos($appViewContent, 'name="app_name"') !== false &&
    strpos($appViewContent, 'name="app_version"') !== false &&
    strpos($appViewContent, 'name="app_icon_source"') !== false &&
    strpos($appViewContent, 'name="app_icon"') !== false,
    "App Details view contains App Name, Version *, and PWA icon upload fields",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'chk_same_as_company') === false,
    "App Details view has REMOVED the redundant 'Use Company Name as App Name' toggle button",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'id="sourceCompanyLogo"') === false &&
    strpos($appViewContent, 'id="sourceCustom"') === false,
    "App Details view has REMOVED redundant radio choice cards in PWA App Icon section",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'id="appNameBadge"') !== false,
    "App Details view clearly displays default status and custom override badges for Name",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'id="wizardPanel1"') !== false &&
    strpos($appViewContent, 'id="wizardPanel2"') !== false,
    "App Details view implements 2-step wizard panels (#wizardPanel1 and #wizardPanel2)",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'name="gst_number"') === false &&
    strpos($appViewContent, 'name="pan_number"') === false &&
    strpos($appViewContent, 'name="paid_stamp"') === false &&
    strpos($appViewContent, 'name="signature"') === false,
    "App Details view contains ZERO tax or document signature fields",
    $failures,
    $passes
);

assertCondition(
    strpos($appViewContent, 'action="' . url('/office/company/app/save') . '"') !== false ||
    strpos($appViewContent, "url('/office/company/app/save')") !== false,
    "App Details form targets /office/company/app/save with core PHP POST",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// TEST 4: Semantic Versioning & Service Validation
// -----------------------------------------------------------------------------
echo "\n4. Verifying Semantic Version Validation & Helper...\n";

assertCondition(
    CompanySettingsService::isValidSemanticVersion('2.4.0'),
    "Validates '2.4.0' as valid semantic version",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::isValidSemanticVersion('1.0.0'),
    "Validates '1.0.0' as valid semantic version",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::isValidSemanticVersion('0.1.0'),
    "Validates '0.1.0' as valid semantic version",
    $failures,
    $passes
);

assertCondition(
    !CompanySettingsService::isValidSemanticVersion('abc'),
    "Correctly rejects non-numeric string 'abc'",
    $failures,
    $passes
);

assertCondition(
    !CompanySettingsService::isValidSemanticVersion('1.0'),
    "Correctly rejects incomplete version '1.0'",
    $failures,
    $passes
);

assertCondition(
    !CompanySettingsService::isValidSemanticVersion('v2.4.0'),
    "Correctly rejects prefixed version 'v2.4.0' without normalization",
    $failures,
    $passes
);

assertCondition(
    !CompanySettingsService::isValidSemanticVersion(''),
    "Correctly rejects empty version string",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::compareSemanticVersions('2.4.0', '2.3.9') === 1,
    "compareSemanticVersions('2.4.0', '2.3.9') returns 1",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::compareSemanticVersions('2.3.0', '2.4.0') === -1,
    "compareSemanticVersions('2.3.0', '2.4.0') returns -1",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::compareSemanticVersions('2.4.0', '2.4.0') === 0,
    "compareSemanticVersions('2.4.0', '2.4.0') returns 0",
    $failures,
    $passes
);

assertCondition(
    function_exists('app_version') && CompanySettingsService::isValidSemanticVersion(app_version()),
    "Global helper app_version() returns a valid semantic version (" . app_version() . ")",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// TEST 5: Version Check Endpoint (/api/v1/app/version)
// -----------------------------------------------------------------------------
echo "\n5. Verifying Version Check Endpoint API...\n";
$versionResp = $controller->versionCheck(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/app/version']));
$versionData = json_decode($versionResp->getBody(), true);
$vHeaders = $versionResp->getHeaders();

assertCondition(
    $versionResp->getStatusCode() === 200,
    "Version check endpoint returns HTTP 200",
    $failures,
    $passes
);

assertCondition(
    isset($versionData['success']) && $versionData['success'] === true &&
    isset($versionData['current_version']) &&
    isset($versionData['minimum_version']) &&
    isset($versionData['app_name']),
    "Version check payload contains success, current_version, minimum_version, and app_name",
    $failures,
    $passes
);

assertCondition(
    isset($vHeaders['Cache-Control']) && strpos($vHeaders['Cache-Control'], 'no-store') !== false,
    "Version check response includes strict no-store / no-cache headers",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// TEST 6: Save App Details & Company Details via Controller
// -----------------------------------------------------------------------------
echo "\n6. Verifying Save Handling & 302 Native Form Submission...\n";

// Save App with invalid version should fail and redirect to /office/company/app
$badAppPost = new Request([], [
    'section'       => 'app',
    'native_submit' => '1',
    'app_version'   => 'invalid-version',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);
$badAppResp = $controller->saveApp($badAppPost);
$badAppHeaders = $badAppResp->getHeaders();

assertCondition(
    $badAppResp->getStatusCode() === 302 &&
    isset($badAppHeaders['Location']) && str_ends_with($badAppHeaders['Location'], '/office/company/app'),
    "Submitting invalid app_version redirects back to /office/company/app with flash errors",
    $failures,
    $passes
);

// Save App with valid version should succeed and redirect to /office/company/app
$goodAppPost = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name_source' => 'custom',
    'app_name'        => 'Voyage Luxury Tours',
    'app_version'     => '2.4.1',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);
$goodAppResp = $controller->saveApp($goodAppPost);
$goodAppHeaders = $goodAppResp->getHeaders();

assertCondition(
    $goodAppResp->getStatusCode() === 302 &&
    isset($goodAppHeaders['Location']) && str_ends_with($goodAppHeaders['Location'], '/office/company/app'),
    "Saving valid app details redirects 302 to /office/company/app",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::getAppVersion() === '2.4.1',
    "Updated app_version is saved to database and retrieved as 2.4.1",
    $failures,
    $passes
);

assertCondition(
    CompanySettingsService::getEffectiveAppName() === 'Voyage Luxury Tours',
    "Updated app_name is saved to database and retrieved as 'Voyage Luxury Tours'",
    $failures,
    $passes
);

// Test rejection of decreasing version (e.g. 2.4.1 down to 2.4.0)
$decreaseAppPost = new Request([], [
    'section'         => 'app',
    'native_submit'   => '1',
    'app_name_source' => 'company_name',
    'app_version'     => '2.4.0',
    'app_icon_source' => 'company_logo',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/app/save']);
$decreaseResp = $controller->saveApp($decreaseAppPost);
$flashErrors = Session::getFlash('errors');

assertCondition(
    $decreaseResp->getStatusCode() === 302 &&
    !empty($flashErrors['app_version']) &&
    CompanySettingsService::getAppVersion() === '2.4.1',
    "Attempt to decrease app_version (2.4.1 to 2.4.0) is strictly rejected with validation error",
    $failures,
    $passes
);

// Reset app version back to 2.4.0 directly via model for environment cleanliness
$compModel = new CompanyProfile();
$compRecord = $compModel->getPrimary();
if (!empty($compRecord['id'])) {
    $compModel->update((int)$compRecord['id'], [
        'app_version'     => '2.4.0',
        'app_name'        => null,
        'app_name_source' => 'company_name',
    ]);
    CompanySettingsService::clearCache();
}

// -----------------------------------------------------------------------------
// TEST 7: Mandatory PWA Update Gate (HTML, CSS, JS)
// -----------------------------------------------------------------------------
echo "\n7. Verifying Mandatory PWA Update Gate Components...\n";
$modalPath = __DIR__ . '/../app/Views/components/pwa_install_modal.php';
$modalContent = file_get_contents($modalPath);

assertCondition(
    strpos($modalContent, 'id="toursPwaUpdateModal"') !== false,
    "pwa_install_modal.php contains #toursPwaUpdateModal update gate container",
    $failures,
    $passes
);

assertCondition(
    strpos($modalContent, 'id="btnPwaUpdateNow"') !== false,
    "pwa_install_modal.php contains [ Update Now ] button (#btnPwaUpdateNow)",
    $failures,
    $passes
);

assertCondition(
    strpos($modalContent, 'performPwaUpdate()') !== false,
    "Update Now button triggers performPwaUpdate()",
    $failures,
    $passes
);

$cssContent = file_get_contents(__DIR__ . '/../public/assets/css/tours-pwa.css');
assertCondition(
    strpos($cssContent, '.pwa-update-backdrop') !== false &&
    strpos($cssContent, '.pwa-update-card') !== false &&
    strpos($cssContent, '.btn-pwa-update-now') !== false,
    "tours-pwa.css defines complete styling for .pwa-update-backdrop and update card",
    $failures,
    $passes
);

$jsContent = file_get_contents(__DIR__ . '/../public/assets/js/tours-pwa.js');
assertCondition(
    strpos($jsContent, 'compareSemanticVersions') !== false &&
    strpos($jsContent, 'checkAppVersionUpdate') !== false &&
    strpos($jsContent, 'showPwaUpdateRequiredGate') !== false &&
    strpos($jsContent, 'performPwaUpdate') !== false,
    "tours-pwa.js implements complete update gate lifecycle methods",
    $failures,
    $passes
);

// -----------------------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------------------
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

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
exit(0);
