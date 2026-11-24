<?php
/**
 * Test Suite: Step-Wise Company Details Wizard Form
 * 
 * Verifies:
 * 1. 5-step wizard structure with .modern-stepper-container and .modern-step-item
 * 2. Panels #wizardPanel1 through #wizardPanel5 exist and separate sections cleanly
 * 3. Next and Previous buttons on appropriate steps
 * 4. Final step contains review dossier and submit button
 * 5. Pre-population of single authoritative company profile record
 * 6. Native POST form submission targeting /office/company/details/save
 * 7. Quick save buttons for existing record editing on intermediate steps
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
echo " TEST SUITE: STEP-WISE COMPANY DETAILS WIZARD FORM\n";
echo "====================================================================\n\n";

$detailsViewPath = __DIR__ . '/../app/Views/office/company/details.php';
$html = file_get_contents($detailsViewPath);

echo "1. Verifying Stepper Architecture...\n";
assertCondition(
    strpos($html, 'class="modern-stepper-container"') !== false,
    "Stepper container .modern-stepper-container is present",
    $failures,
    $passes
);

assertCondition(
    substr_count($html, 'class="modern-step-item') >= 5,
    "Stepper contains 5 distinct step buttons",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="stepConn1"') !== false &&
    strpos($html, 'id="stepConn2"') !== false &&
    strpos($html, 'id="stepConn3"') !== false &&
    strpos($html, 'id="stepConn4"') !== false,
    "All 4 stepper connector lines are present",
    $failures,
    $passes
);

echo "\n2. Verifying 5 Wizard Panels...\n";
assertCondition(strpos($html, 'id="wizardPanel1"') !== false, "Panel #wizardPanel1 (Company Profile) exists", $failures, $passes);
assertCondition(strpos($html, 'id="wizardPanel2"') !== false, "Panel #wizardPanel2 (Contact Details) exists", $failures, $passes);
assertCondition(strpos($html, 'id="wizardPanel3"') !== false, "Panel #wizardPanel3 (Office Address) exists", $failures, $passes);
assertCondition(strpos($html, 'id="wizardPanel4"') !== false, "Panel #wizardPanel4 (Business & Tax) exists", $failures, $passes);
assertCondition(strpos($html, 'id="wizardPanel5"') !== false, "Panel #wizardPanel5 (Documents & Review) exists", $failures, $passes);

echo "\n3. Verifying Navigation Buttons & Step 5 Documents...\n";
assertCondition(
    strpos($html, 'onclick="wizardNextStep(1)"') !== false &&
    strpos($html, 'onclick="wizardNextStep(2)"') !== false &&
    strpos($html, 'onclick="wizardNextStep(3)"') !== false &&
    strpos($html, 'onclick="wizardNextStep(4)"') !== false,
    "Step 1 through 4 have Next buttons calling wizardNextStep()",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'onclick="wizardGoToStep(1)"') !== false &&
    strpos($html, 'onclick="wizardGoToStep(2)"') !== false &&
    strpos($html, 'onclick="wizardGoToStep(3)"') !== false &&
    strpos($html, 'onclick="wizardGoToStep(4)"') !== false,
    "Step 2 through 5 have Previous buttons calling wizardGoToStep()",
    $failures,
    $passes
);

// Verify Step 1 owns company logo and Step 5 has no company logo upload box
preg_match('/<div class="modern-step-panel" id="wizardPanel1".*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $step1Match);
preg_match('/<div class="modern-step-panel" id="wizardPanel5".*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $step5Match);

$step1Html = $step1Match[0] ?? '';
$step5Html = $step5Match[0] ?? '';

assertCondition(
    strpos($step1Html, 'id="logo_input"') !== false && strpos($step1Html, 'Company Logo') !== false,
    "Step 1 (Company Profile) correctly includes the Company Logo upload box",
    $failures,
    $passes
);

assertCondition(
    strpos($step5Html, 'docLogo') === false && strpos($step5Html, 'id="logo_input"') === false,
    "Step 5 (Documents) does NOT contain redundant Company Logo upload box",
    $failures,
    $passes
);

assertCondition(
    strpos($step5Html, 'id="paid_stamp_input"') !== false &&
    strpos($step5Html, 'id="signature_input"') !== false &&
    strpos($step5Html, 'id="sig_stamp_input"') !== false,
    "Step 5 contains exactly the 3 invoice document items (Paid Stamp, Signature, Signature + Stamp)",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'grid-template-columns: repeat(3, minmax(0, 1fr));') !== false,
    ".doc-cards-grid styles use 3 balanced columns for the 3 document cards",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'id="btnSubmitCompanyDetails"') !== false,
    "Final step 5 has primary submit button #btnSubmitCompanyDetails",
    $failures,
    $passes
);

assertCondition(
    strpos($html, 'btn-quick-save') !== false,
    "Intermediate steps provide quick save option when editing existing company profile",
    $failures,
    $passes
);

echo "\n4. Verifying Single Record Concept & Pre-population...\n";
$companyModel = new CompanyProfile();
$primaryCompany = $companyModel->getPrimary();

assertCondition(
    !empty($primaryCompany),
    "Single authoritative company profile exists in database (ID: " . ($primaryCompany['id'] ?? 'none') . ")",
    $failures,
    $passes
);

assertCondition(
    strpos($html, '$primaryCompany = $company ?? $primaryCompany ?? null;') !== false,
    "View loads single primary company record",
    $failures,
    $passes
);

assertCondition(
    strpos($html, '$val = function') !== false,
    "Form inputs fallback to saved values of the single primary company record",
    $failures,
    $passes
);

echo "\n5. Verifying JavaScript Wizard Engine...\n";
assertCondition(strpos($html, 'function wizardGoToStep(targetStep)') !== false, "JavaScript defines wizardGoToStep()", $failures, $passes);
assertCondition(strpos($html, 'function wizardNextStep(currentStepNum)') !== false, "JavaScript defines wizardNextStep()", $failures, $passes);
assertCondition(strpos($html, 'function wizardValidateCurrentStep(stepNum)') !== false, "JavaScript defines wizardValidateCurrentStep()", $failures, $passes);
assertCondition(strpos($html, 'function updateStepperUI(activeStep)') !== false, "JavaScript defines updateStepperUI()", $failures, $passes);
assertCondition(strpos($html, 'function updateReviewDossier()') !== false, "JavaScript defines updateReviewDossier() for Step 5 summary", $failures, $passes);

echo "\n6. Verifying Controller saveDetails() Execution...\n";
$controller = new CompanyController();
$saveReq = new Request([], [
    'section'       => 'all',
    'native_submit' => '1',
    'company_name'  => $primaryCompany['company_name'],
    'phone'         => $primaryCompany['phone'] ?? '9876543210',
    'address'       => $primaryCompany['address'] ?? 'Test Commercial Complex',
    'city'          => $primaryCompany['city'] ?? 'Srinagar',
    'pin_code'      => $primaryCompany['pin_code'] ?? '190001',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/office/company/details/save']);

$saveResp = $controller->saveDetails($saveReq);
$headers = $saveResp->getHeaders();

assertCondition(
    $saveResp->getStatusCode() === 302 &&
    isset($headers['Location']) && str_ends_with($headers['Location'], '/office/company/details'),
    "saveDetails() executes successfully and 302 redirects back to /office/company/details",
    $failures,
    $passes
);

echo "\n====================================================================\n";
echo " WIZARD TEST RESULTS SUMMARY\n";
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

echo "\nALL STEP-WISE WIZARD TESTS PASSED SUCCESSFULLY!\n";
exit(0);
