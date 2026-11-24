<?php
/**
 * Comprehensive Automated Verification for Simplified Professional Company Profile,
 * 5 Independent Logical Sections, and Live App Name Updates
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Controllers\CompanyController;
use App\Models\CompanyProfile;
use App\Services\CompanySettingsService;

$pdo = Database::getConnection();
$companyModel = new CompanyProfile();

echo "====================================================================\n";
echo "SIMPLIFIED COMPANY PROFILE & LIVE APP NAME UPDATE TEST SUITE\n";
echo "====================================================================\n\n";

// Backup original primary company data to restore at teardown
$originalCompany = $companyModel->getPrimary();
if (!$originalCompany) {
    echo "FATAL: Primary company profile not found in database.\n";
    exit(1);
}

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $label, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$label}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$label}\n";
        if ($details !== '') {
            echo "         Details: {$details}\n";
        }
    }
}

try {
    // -------------------------------------------------------------
    // GROUP 1: SCHEMA & MODEL AUDIT
    // -------------------------------------------------------------
    echo "--- GROUP 1: Database Schema & Model Fillable Attributes ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `company_profiles` WHERE Field IN ('app_name_source', 'app_name')");
    $dbCols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($dbCols, 'Field');

    assertTest(in_array('app_name_source', $colNames, true), "Column 'app_name_source' exists in company_profiles");
    assertTest(in_array('app_name', $colNames, true), "Column 'app_name' exists in company_profiles");

    $modelReflection = new ReflectionClass($companyModel);
    $fillableProp = $modelReflection->getProperty('fillable');
    $fillableProp->setAccessible(true);
    $fillable = $fillableProp->getValue($companyModel);

    assertTest(in_array('app_name_source', $fillable, true), "'app_name_source' is present in CompanyProfile fillable attributes");
    assertTest(in_array('app_name', $fillable, true), "'app_name' is present in CompanyProfile fillable attributes");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 2: COMPANY SETTINGS SERVICE & HELPER FUNCTIONS
    // -------------------------------------------------------------
    echo "--- GROUP 2: CompanySettingsService & App Name Methods ---\n";
    CompanySettingsService::clearCache();

    assertTest(method_exists(CompanySettingsService::class, 'getAppNameSource'), "CompanySettingsService::getAppNameSource() exists");
    assertTest(method_exists(CompanySettingsService::class, 'isSameAsCompanyName'), "CompanySettingsService::isSameAsCompanyName() exists");
    assertTest(method_exists(CompanySettingsService::class, 'getCustomAppName'), "CompanySettingsService::getCustomAppName() exists");
    assertTest(method_exists(CompanySettingsService::class, 'getEffectiveAppName'), "CompanySettingsService::getEffectiveAppName() exists");
    assertTest(function_exists('pwa_app_name'), "Helper function pwa_app_name() exists");
    assertTest(function_exists('pwa_app_name_source'), "Helper function pwa_app_name_source() exists");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 3: APP NAME SOURCE & DERIVATION LOGIC
    // -------------------------------------------------------------
    echo "--- GROUP 3: App Name Derivation & Real-time Mirroring ---\n";
    $companyId = (int)$originalCompany['id'];

    // Scenario A: Same as Company Name (ON)
    $companyModel->update($companyId, [
        'company_name'    => 'Kashmir Horizon Travels',
        'app_name_source' => 'company_name',
        'app_name'        => null,
    ]);
    CompanySettingsService::clearCache();

    assertTest(CompanySettingsService::getAppNameSource() === 'company_name', "App name source is 'company_name'");
    assertTest(CompanySettingsService::isSameAsCompanyName() === true, "isSameAsCompanyName() returns true");
    assertTest(CompanySettingsService::getEffectiveAppName() === 'Kashmir Horizon Travels', "Effective app name derived from company name");
    assertTest(pwa_app_name() === 'Kashmir Horizon Travels', "pwa_app_name() matches effective app name");

    // Change company name -> effective app name must update automatically without touching app_name column
    $companyModel->update($companyId, [
        'company_name' => 'Kashmir Horizon Voyages Pvt. Ltd.',
    ]);
    CompanySettingsService::clearCache();

    assertTest(CompanySettingsService::getEffectiveAppName() === 'Kashmir Horizon Voyages Pvt. Ltd.', "Changing company_name automatically updates effective app name (no stale duplicate string)");

    // Scenario B: Custom App Name (OFF)
    $companyModel->update($companyId, [
        'company_name'    => 'Kashmir Horizon Voyages Pvt. Ltd.',
        'app_name_source' => 'custom',
        'app_name'        => 'Horizon App',
    ]);
    CompanySettingsService::clearCache();

    assertTest(CompanySettingsService::getAppNameSource() === 'custom', "App name source is 'custom'");
    assertTest(CompanySettingsService::isSameAsCompanyName() === false, "isSameAsCompanyName() returns false");
    assertTest(CompanySettingsService::getCustomAppName() === 'Horizon App', "getCustomAppName() returns custom string");
    assertTest(CompanySettingsService::getEffectiveAppName() === 'Horizon App', "getEffectiveAppName() returns custom app name");
    assertTest(pwa_app_name() === 'Horizon App', "pwa_app_name() returns custom app name");

    // Updating company name when custom mode is active must PRESERVE custom app name
    $companyModel->update($companyId, [
        'company_name' => 'Apex Horizon Global Travels',
    ]);
    CompanySettingsService::clearCache();

    assertTest(CompanySettingsService::getEffectiveAppName() === 'Horizon App', "Updating company name preserves custom app name");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 4: SECTION-BASED EDITING & EFFECTIVE APP NAME AJAX RESPONSE
    // -------------------------------------------------------------
    echo "--- GROUP 4: Independent Section Saves & AJAX Responses ---\n";
    $controller = new CompanyController();

    // Baseline: establish a known full profile
    $baselineData = [
        'company_name'    => 'Imperial Himalayan Tours',
        'app_name_source' => 'company_name',
        'app_name'        => null,
        'owner_name'      => 'Aamir Khan',
        'tagline'         => 'Luxury Alpine Expeditions',
        'primary_color'   => '#176B5B',
        'secondary_color' => '#16443B',
        'accent_color'    => '#D6B77A',
        'app_icon_source' => 'company_logo',
        'email'           => 'concierge@imperialtours.test',
        'phone'           => '9988776655',
        'alternate_phone' => '8877665544',
        'website'         => 'https://imperialtours.test',
        'address'         => 'Suite 401, Boulevard Heights',
        'city'            => 'Srinagar',
        'state'           => 'Jammu and Kashmir',
        'pin_code'        => '190001',
        'gst_number'      => '01ABCDE1234F1Z5',
        'pan_number'      => 'ABCDE1234F',
        'status'          => 'active',
        'is_primary'      => 1,
    ];
    $companyModel->update($companyId, $baselineData);
    CompanySettingsService::clearCache();

    // TEST 4.1: Edit Brand & App Identity Section ONLY
    $brandPayload = [
        'section'         => 'brand_identity',
        'company_name'    => 'Imperial Royal Fleet',
        'app_name_source' => 'custom',
        'app_name'        => 'Royal Fleet Go',
        'app_icon_source' => 'company_logo',
    ];
    $reqBrand = new Request([], $brandPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resBrand = $controller->save($reqBrand);

    assertTest($resBrand->getStatusCode() === 200, "Saving brand_identity section returns HTTP 200 (does not demand phone/address)");
    $jsonBrand = json_decode($resBrand->getContent(), true);
    assertTest(($jsonBrand['success'] ?? false) === true, "Brand identity save response has success = true");
    assertTest(($jsonBrand['effective_app_name'] ?? '') === 'Royal Fleet Go', "Save response exposes effective_app_name = 'Royal Fleet Go'");
    assertTest(($jsonBrand['app_name_source'] ?? '') === 'custom', "Save response exposes app_name_source = 'custom'");

    // Verify DB state after brand_identity update
    $updatedProfile = $companyModel->getPrimary();
    assertTest($updatedProfile['company_name'] === 'Imperial Royal Fleet', "DB company_name updated to 'Imperial Royal Fleet'");
    assertTest($updatedProfile['app_name_source'] === 'custom', "DB app_name_source updated to 'custom'");
    assertTest($updatedProfile['app_name'] === 'Royal Fleet Go', "DB app_name updated to 'Royal Fleet Go'");
    // Verify PRESERVATION of other sections:
    assertTest($updatedProfile['phone'] === '9988776655', "Contact phone was preserved without data loss");
    assertTest($updatedProfile['address'] === 'Suite 401, Boulevard Heights', "Contact address was preserved without data loss");
    assertTest($updatedProfile['pin_code'] === '190001', "PIN code was preserved without data loss");
    assertTest($updatedProfile['owner_name'] === 'Aamir Khan', "Owner name was preserved without data loss");
    assertTest($updatedProfile['gst_number'] === '01ABCDE1234F1Z5', "GSTIN was preserved without data loss");

    // Verify Manifest sync
    $manifestPath = dirname(__DIR__) . '/public/manifest.json';
    assertTest(file_exists($manifestPath), "public/manifest.json exists on disk");
    $manifestContent = json_decode(file_get_contents($manifestPath), true);
    assertTest(($manifestContent['name'] ?? '') === 'Royal Fleet Go', "public/manifest.json dynamically synced with new effective app name 'Royal Fleet Go'");

    // TEST 4.2: Edit Contact Information Section ONLY
    $contactPayload = [
        'section'         => 'contact_info',
        'owner_name'      => 'Nadeem Khan',
        'phone'           => '9123456780',
        'alternate_phone' => '9876543210',
        'email'           => 'support@royalfleet.test',
        'website'         => 'https://royalfleet.test',
    ];
    $reqContact = new Request([], $contactPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resContact = $controller->save($reqContact);

    assertTest($resContact->getStatusCode() === 200, "Saving contact_info section returns HTTP 200 (does not demand address or logo)");
    $jsonContact = json_decode($resContact->getContent(), true);
    assertTest(($jsonContact['success'] ?? false) === true, "Contact Information save response has success = true");

    $updatedProfile2 = $companyModel->getPrimary();
    assertTest($updatedProfile2['owner_name'] === 'Nadeem Khan', "Owner name updated to 'Nadeem Khan'");
    assertTest($updatedProfile2['phone'] === '9123456780', "Contact phone updated to '9123456780'");
    assertTest($updatedProfile2['company_name'] === 'Imperial Royal Fleet', "Company name remained 'Imperial Royal Fleet'");
    assertTest($updatedProfile2['app_name'] === 'Royal Fleet Go', "App name remained 'Royal Fleet Go'");
    assertTest($updatedProfile2['address'] === 'Suite 401, Boulevard Heights', "Office Address remained preserved");

    // TEST 4.3: Edit Office Address Section ONLY
    $addressPayload = [
        'section'  => 'office_address',
        'address'  => '77 Residency Road, Civil Lines',
        'city'     => 'Jammu',
        'state'    => 'Jammu and Kashmir',
        'pin_code' => '180001',
    ];
    $reqAddress = new Request([], $addressPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resAddress = $controller->save($reqAddress);

    assertTest($resAddress->getStatusCode() === 200, "Saving office_address section returns HTTP 200 (does not demand company name or phone)");
    $jsonAddress = json_decode($resAddress->getContent(), true);
    assertTest(($jsonAddress['success'] ?? false) === true, "Office Address save response has success = true");

    $updatedProfile3 = $companyModel->getPrimary();
    assertTest($updatedProfile3['address'] === '77 Residency Road, Civil Lines', "Address updated");
    assertTest($updatedProfile3['city'] === 'Jammu', "City updated to 'Jammu'");
    assertTest($updatedProfile3['pin_code'] === '180001', "PIN code updated to '180001'");
    assertTest($updatedProfile3['phone'] === '9123456780', "Phone remained preserved");
    assertTest($updatedProfile3['company_name'] === 'Imperial Royal Fleet', "Company name remained preserved");

    // TEST 4.4: Edit Business & Tax Section ONLY
    $taxPayload = [
        'section'    => 'business_tax',
        'tagline'    => 'The Ultimate Alpine Journey',
        'gst_number' => '01ABCDE9999F1Z9',
        'pan_number' => 'ABCDE9999F',
        'status'     => 'active',
    ];
    $reqTax = new Request([], $taxPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resTax = $controller->save($reqTax);

    assertTest($resTax->getStatusCode() === 200, "Saving business_tax section returns HTTP 200");
    $jsonTax = json_decode($resTax->getContent(), true);
    assertTest(($jsonTax['success'] ?? false) === true, "Business & Tax save response has success = true");

    $updatedProfile4 = $companyModel->getPrimary();
    assertTest($updatedProfile4['tagline'] === 'The Ultimate Alpine Journey', "Tagline updated");
    assertTest($updatedProfile4['gst_number'] === '01ABCDE9999F1Z9', "GSTIN updated");
    assertTest($updatedProfile4['pan_number'] === 'ABCDE9999F', "PAN updated");
    assertTest($updatedProfile4['company_name'] === 'Imperial Royal Fleet', "Company name preserved");

    // TEST 4.5: Validation Safeguards Per Section
    // Invalid pin code in office_address
    $invalidAddressPayload = [
        'section'  => 'office_address',
        'address'  => '77 Residency Road',
        'city'     => 'Jammu',
        'pin_code' => '123', // Invalid: not 6 digits
    ];
    $reqInvalidAddress = new Request([], $invalidAddressPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resInvalidAddress = $controller->save($reqInvalidAddress);
    assertTest($resInvalidAddress->getStatusCode() === 422, "Invalid PIN code rejected with HTTP 422");
    $jsonInvalidAddress = json_decode($resInvalidAddress->getContent(), true);
    assertTest(!empty($jsonInvalidAddress['errors']['pin_code']), "Error specifically flagged for 'pin_code'");

    // Missing app_name when app_name_source = 'custom' in brand_identity
    $invalidBrandPayload = [
        'section'         => 'brand_identity',
        'company_name'    => 'New Horizon Cabs',
        'app_name_source' => 'custom',
        'app_name'        => '', // Empty custom app name is illegal
    ];
    $reqInvalidBrand = new Request([], $invalidBrandPayload, [
        'REQUEST_METHOD'        => 'POST',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resInvalidBrand = $controller->save($reqInvalidBrand);
    assertTest($resInvalidBrand->getStatusCode() === 422, "Empty custom app name rejected with HTTP 422");
    $jsonInvalidBrand = json_decode($resInvalidBrand->getContent(), true);
    assertTest(!empty($jsonInvalidBrand['errors']['app_name']), "Error specifically flagged for 'app_name'");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 5: SINGLE-PAGE COMPANY PROFILE VIEW RENDERING
    // -------------------------------------------------------------
    echo "--- GROUP 5: Single-Page View Rendering (5 Distinct Section Cards) ---\n";
    $reqView = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $resView = $controller->index($reqView);
    $html = $resView->getContent();

    // 5.1: Clean Profile Header
    assertTest(strpos($html, 'profile-executive-header') !== false, "View renders clean profile header");
    assertTest(strpos($html, 'id="headerCompanyName"') !== false, "Header displays company name");
    assertTest(strpos($html, 'data-live-app-name') !== false, "Header displays live app name with data-live-app-name attribute");
    assertTest(strpos($html, 'id="headerStatusBadge"') !== false, "Header displays company status pill");

    // 5.2: 5 Independent Logical Section Cards
    assertTest(strpos($html, 'id="card_brand_identity"') !== false, "View renders Card 1: Brand & App Identity");
    assertTest(strpos($html, 'id="card_contact_info"') !== false, "View renders Card 2: Contact Information");
    assertTest(strpos($html, 'id="card_office_address"') !== false, "View renders Card 3: Office Address");
    assertTest(strpos($html, 'id="card_business_tax"') !== false, "View renders Card 4: Business & Tax Information");
    assertTest(strpos($html, 'id="card_documents"') !== false, "View renders Card 5: Invoice & Document Branding");

    // 5.3: Section-level Save Buttons & Inputs
    assertTest(substr_count($html, 'btn-save-section') >= 5, "Each of the 5 sections has its own Save Changes button");
    assertTest(strpos($html, 'name="section" value="brand_identity"') !== false, "Brand Identity form has section=brand_identity");
    assertTest(strpos($html, 'name="section" value="contact_info"') !== false, "Contact Info form has section=contact_info");
    assertTest(strpos($html, 'name="section" value="office_address"') !== false, "Office Address form has section=office_address");
    assertTest(strpos($html, 'name="section" value="business_tax"') !== false, "Business Tax form has section=business_tax");
    assertTest(strpos($html, 'name="section" value="documents"') !== false, "Documents form has section=documents");

    // 5.4: Brand & App Identity UI Elements
    assertTest(strpos($html, 'Use Company Name as App Name') !== false, "Card 1 contains 'Use Company Name as App Name' switch");
    assertTest(strpos($html, 'id="chk_same_as_company"') !== false, "Card 1 contains same-as-company checkbox");
    assertTest(strpos($html, 'id="sourceCompanyLogo"') !== false, "Card 1 contains 'Use Company Logo' PWA option");
    assertTest(strpos($html, 'id="sourceCustom"') !== false, "Card 1 contains 'Use Different App Icon' PWA option");

    // 5.5: Removal of Old Multi-Step Wizard
    assertTest(strpos($html, 'modern-stepper-track') === false, "Old wizard stepper track is removed from view");
    assertTest(strpos($html, 'wizardGoToStep') === false, "Old wizard step navigation is removed from view");
    assertTest(strpos($html, 'modern-step-item') === false, "Old wizard step items are removed from view");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 6: LIVE APP BRANDING UPDATER & LAYOUT HOOKS
    // -------------------------------------------------------------
    echo "--- GROUP 6: Live App Branding Updater & Layout Hooks ---\n";
    $appJsPath = dirname(__DIR__) . '/public/assets/js/app.js';
    $appJsCode = file_get_contents($appJsPath);
    assertTest(strpos($appJsCode, 'window.updateLiveAppBranding = function') !== false, "window.updateLiveAppBranding is defined in public/assets/js/app.js");

    $officeLayoutPath = dirname(__DIR__) . '/app/Views/layouts/office.php';
    $officeLayoutCode = file_get_contents($officeLayoutPath);
    assertTest(strpos($officeLayoutCode, 'data-live-app-name') !== false, "Desktop sidebar navigation has data-live-app-name attribute");

    $mobileTopbarPath = dirname(__DIR__) . '/app/Views/components/mobile_topbar.php';
    $mobileTopbarCode = file_get_contents($mobileTopbarPath);
    assertTest(strpos($mobileTopbarCode, 'data-live-app-name') !== false, "Mobile topbar navigation has data-live-app-name attribute");

    $pwaJsPath = dirname(__DIR__) . '/public/assets/js/tours-pwa.js';
    $pwaJsCode = file_get_contents($pwaJsPath);
    assertTest(strpos($pwaJsCode, 'window.rebuildToursPwaModalContent') !== false, "PWA modal exposes rebuildToursPwaModalContent for live updates");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 7: CODE QUALITY & ZERO ALERT()/CONFIRM()
    // -------------------------------------------------------------
    echo "--- GROUP 7: Code Quality & Zero alert() / confirm() Dialogs ---\n";
    $viewPath = dirname(__DIR__) . '/app/Views/office/company/index.php';
    $viewCode = file_get_contents($viewPath);

    assertTest(strpos($viewCode, 'alert(') === false, "Zero alert() calls in office/company/index.php");
    assertTest(strpos($viewCode, 'confirm(') === false, "Zero confirm() calls in office/company/index.php");
    assertTest(strpos($pwaJsCode, 'alert(') === false, "Zero alert() calls in tours-pwa.js");
    assertTest(strpos($pwaJsCode, 'confirm(') === false, "Zero confirm() calls in tours-pwa.js");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 8: STANDARD MVC POST -> REDIRECT (302) -> GET (Fresh DB state)
    // -------------------------------------------------------------
    echo "--- GROUP 8: Standard MVC POST -> Redirect (302) -> Fresh GET Flow ---\n";
    // Baseline state: App Name = "Tushar"
    $companyModel->update($companyId, [
        'company_name'    => 'Tushar Tours & Travels',
        'app_name_source' => 'custom',
        'app_name'        => 'Tushar',
    ]);
    CompanySettingsService::clearCache();
    assertTest(CompanySettingsService::getEffectiveAppName() === 'Tushar', "Baseline: Current App Name is 'Tushar'");

    // User changes App Name to "Himanshu" and submits standard HTML POST
    $normalPostPayload = [
        'section'         => 'brand_identity',
        'company_name'    => 'Tushar Tours & Travels',
        'app_name_source' => 'custom',
        'app_name'        => 'Himanshu',
        'app_icon_source' => 'company_logo',
    ];
    $reqNormalPost = new Request([], $normalPostPayload, [
        'REQUEST_METHOD' => 'POST',
        // Notice: NO HTTP_X_REQUESTED_WITH! Pure browser form submission
    ]);
    $resNormalPost = $controller->save($reqNormalPost);

    // 1. Verify 302 Redirect
    assertTest($resNormalPost->getStatusCode() === 302, "Standard POST returns HTTP 302 Redirect");
    assertTest(strpos($resNormalPost->getHeader('Location'), '/office/company') !== false, "Redirect header Location targets /office/company");

    // 2. Verify Session Flash
    $flashesInSession = \App\Core\Session::getAllFlashes();
    assertTest(!empty($flashesInSession['success']), "Session flash 'success' is set on redirect");
    assertTest(strpos($flashesInSession['success'], 'Brand & App Identity') !== false, "Success message confirms 'Brand & App Identity updated successfully.'");

    // 3. Verify Database was updated
    $dbAfterSave = $companyModel->getPrimary();
    assertTest($dbAfterSave['app_name'] === 'Himanshu', "Database app_name updated to 'Himanshu'");

    // 4. Verify Fresh GET /office/company immediately renders 'Himanshu'
    CompanySettingsService::clearCache();
    $reqFreshGet = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $resFreshGet = $controller->index($reqFreshGet);
    $freshHtml = $resFreshGet->getContent();

    assertTest($resFreshGet->getStatusCode() === 200, "Fresh GET /office/company returns HTTP 200");
    assertTest(strpos($freshHtml, 'Himanshu') !== false, "Fresh GET immediately renders new App Name 'Himanshu'");

    // 5. Verify same-as-company ON scenario with normal POST
    $companyModel->update($companyId, [
        'company_name'    => 'Himanshu Travels Worldwide',
        'app_name_source' => 'company_name',
        'app_name'        => null,
    ]);
    CompanySettingsService::clearCache();
    $reqFreshGet2 = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $resFreshGet2 = $controller->index($reqFreshGet2);
    $freshHtml2 = $resFreshGet2->getContent();
    assertTest(strpos($freshHtml2, 'Himanshu Travels Worldwide') !== false, "Same-as-company ON: effective app name mirrors 'Himanshu Travels Worldwide'");

    // 6. Verify Normal POST Validation Error -> Redirects with errors
    $invalidPostPayload = [
        'section'  => 'office_address',
        'address'  => 'Test Address',
        'city'     => 'Test City',
        'pin_code' => '99', // Invalid PIN
    ];
    $reqInvalidPost = new Request([], $invalidPostPayload, [
        'REQUEST_METHOD' => 'POST',
    ]);
    $resInvalidPost = $controller->save($reqInvalidPost);
    assertTest($resInvalidPost->getStatusCode() === 302, "Validation failure on normal POST returns HTTP 302 Redirect");
    $errorsInSession = \App\Core\Session::getFlash('errors');
    assertTest(!empty($errorsInSession['pin_code']), "Errors flashed in session for 'pin_code'");
    $oldInSession = \App\Core\Session::getFlash('old');
    assertTest(($oldInSession['pin_code'] ?? '') === '99', "Old input preserved in session across redirect");
    echo "\n";

    // -------------------------------------------------------------
    // GROUP 9: VIEW SOURCE & AJAX INTERCEPTION REMOVAL AUDIT
    // -------------------------------------------------------------
    echo "--- GROUP 9: View Source & AJAX Removal Audit ---\n";
    assertTest(strpos($viewCode, "url('/office/company/save')") !== false, "Forms submit to /office/company/save in view template");
    assertTest(strpos($html, 'action="' . url('/office/company/save') . '"') !== false, "Rendered HTML forms submit to " . url('/office/company/save'));
    assertTest(strpos($viewCode, 'preventDefault') === false, "Zero preventDefault in office/company/index.php");
    assertTest(strpos($viewCode, 'fetch(') === false, "Zero fetch() form interception in office/company/index.php");
    assertTest(strpos($viewCode, 'XMLHttpRequest') === false, "Zero XMLHttpRequest in office/company/index.php");
    assertTest(strpos($viewCode, 'location.reload') === false, "Zero location.reload calls in office/company/index.php");
    echo "\n";

} catch (\Throwable $e) {
    echo "UNEXPECTED EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failCount++;
} finally {
    // Teardown: restore initial database record cleanly
    echo "--- CLEANUP & TEARDOWN ---\n";
    $cleanRestoreData = [
        'company_name'    => $originalCompany['company_name'],
        'app_name_source' => $originalCompany['app_name_source'] ?? 'company_name',
        'app_name'        => $originalCompany['app_name'] ?? null,
        'owner_name'      => $originalCompany['owner_name'] ?? null,
        'tagline'         => $originalCompany['tagline'] ?? null,
        'primary_color'   => $originalCompany['primary_color'] ?? '#176B5B',
        'secondary_color' => $originalCompany['secondary_color'] ?? '#16443B',
        'accent_color'    => $originalCompany['accent_color'] ?? '#D6B77A',
        'logo'            => $originalCompany['logo'] ?? null,
        'app_icon_source' => $originalCompany['app_icon_source'] ?? 'company_logo',
        'app_icon'        => $originalCompany['app_icon'] ?? null,
        'paid_stamp'      => $originalCompany['paid_stamp'] ?? null,
        'signature'       => $originalCompany['signature'] ?? null,
        'signature_stamp' => $originalCompany['signature_stamp'] ?? null,
        'email'           => $originalCompany['email'] ?? null,
        'phone'           => $originalCompany['phone'] ?? null,
        'alternate_phone' => $originalCompany['alternate_phone'] ?? null,
        'website'         => $originalCompany['website'] ?? null,
        'address'         => $originalCompany['address'] ?? null,
        'city'            => $originalCompany['city'] ?? null,
        'state'           => $originalCompany['state'] ?? null,
        'pin_code'        => $originalCompany['pin_code'] ?? null,
        'gst_number'      => $originalCompany['gst_number'] ?? null,
        'pan_number'      => $originalCompany['pan_number'] ?? null,
        'status'          => $originalCompany['status'] ?? 'active',
        'is_primary'      => 1,
    ];
    $companyModel->update((int)$originalCompany['id'], $cleanRestoreData);
    CompanySettingsService::clearCache();
    CompanySettingsService::syncPwaIcons();
    echo "✓ Original company profile restored cleanly.\n";
    echo "✓ PWA icons and manifest resynced.\n\n";
}

echo "====================================================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
