<?php
/**
 * Test Suite: Company Profile Layout Structure & Responsive Grid Architecture
 */

require __DIR__ . '/../app/bootstrap.php';

function assertTest(bool $condition, string $description): void {
    if ($condition) {
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "COMPANY PROFILE LAYOUT STRUCTURE & RESPONSIVE ARCHITECTURE TESTS\n";
echo "====================================================================\n\n";

$viewPath = dirname(__DIR__) . '/app/Views/office/company/index.php';
$viewContent = file_get_contents($viewPath);

// --- TEST GROUP 1: Parent Container & Full-Width Architecture ---
echo "--- GROUP 1: Parent Container & Full-Width Architecture ---\n";
assertTest(strpos($viewContent, 'max-width: 1100px') === false, "Eliminated arbitrary max-width: 1100px constraint");
assertTest(strpos($viewContent, 'width: 100%;') !== false, "Container sets width: 100%");
assertTest(strpos($viewContent, 'max-width: 100%;') !== false, "Container sets max-width: 100%");
assertTest(strpos($viewContent, 'box-sizing: border-box;') !== false, "Container uses box-sizing: border-box");
assertTest(strpos($viewContent, 'margin-left: -') === false, "Zero negative left margin hacks");
assertTest(strpos($viewContent, 'margin-right: -') === false, "Zero negative right margin hacks");
assertTest(preg_match('/\.company-profile-container\s*\{[^}]*transform:/', $viewContent) === 0, "Zero transform alignment hacks on company container");
echo "\n";

// --- TEST GROUP 2: Brand & App Identity Desktop Grid ---
echo "--- GROUP 2: Brand & App Identity Desktop Grid ---\n";
assertTest(strpos($viewContent, 'brand-identity-top') !== false, "Card 1 defines .brand-identity-top horizontal desktop layout");
assertTest(strpos($viewContent, 'brand-logo-panel') !== false, "Card 1 defines .brand-logo-panel for company logo preview");
assertTest(strpos($viewContent, 'brand-names-panel') !== false, "Card 1 defines .brand-names-panel for 2-column company name & app name");
assertTest(strpos($viewContent, 'brand-pwa-panel') !== false, "Card 1 defines .brand-pwa-panel for PWA icon row");
assertTest(strpos($viewContent, 'brand-pwa-grid') !== false, "Card 1 defines .brand-pwa-grid for 2-column PWA choices & preview");
assertTest(strpos($viewContent, 'object-fit: contain;') !== false, "Logo and icon previews preserve aspect ratio with object-fit: contain");
assertTest(strpos($viewContent, 'height: 110px;') !== false, "Logo preview container has compact proportional height");
echo "\n";

// --- TEST GROUP 3: Two-Column Section Grid (Contact Info + Business Tax) ---
echo "--- GROUP 3: Two-Column Section Grid (Contact Info + Business Tax) ---\n";
assertTest(strpos($viewContent, 'profile-two-col-grid') !== false, "Defines .profile-two-col-grid container");
assertTest(strpos($viewContent, 'grid-template-columns: 1fr 1fr;') !== false, "Two-column grid defines 1fr 1fr side-by-side columns");
assertTest(strpos($viewContent, 'id="card_contact_info"') !== false, "Contact Info card exists in grid");
assertTest(strpos($viewContent, 'id="card_business_tax"') !== false, "Business & Tax card exists in grid");

// Check that Contact Info and Business Tax are nested inside profile-two-col-grid
$posTwoCol = strpos($viewContent, 'class="profile-two-col-grid"');
$posContact = strpos($viewContent, 'id="card_contact_info"');
$posTax = strpos($viewContent, 'id="card_business_tax"');
$posAddress = strpos($viewContent, 'id="card_office_address"');

assertTest($posTwoCol < $posContact && $posContact < $posTax, "Contact Info and Business Tax are placed side-by-side in two-column grid");
assertTest($posTax < $posAddress, "Office Address card is placed full-width after the two-column grid");
echo "\n";

// --- TEST GROUP 4: Full-Width Sections (Office Address & Document Branding) ---
echo "--- GROUP 4: Full-Width Sections (Office Address & Document Branding) ---\n";
assertTest(strpos($viewContent, 'id="card_office_address"') !== false, "Office Address card exists");
assertTest(strpos($viewContent, 'three-column-form-grid') !== false, "City, State, PIN in responsive 3-column row");
assertTest(strpos($viewContent, 'id="card_documents"') !== false, "Invoice & Document Branding card exists");
assertTest(strpos($viewContent, 'doc-cards-grid') !== false, "Defines .doc-cards-grid");
assertTest(strpos($viewContent, 'grid-template-columns: repeat(4, 1fr);') !== false, "Documents grid defines 4 equal desktop columns");
echo "\n";

// --- TEST GROUP 5: Responsive Breakpoints ---
echo "--- GROUP 5: Responsive Breakpoints ---\n";
assertTest(strpos($viewContent, '@media (max-width: 1200px)') !== false, "Defines breakpoint for large tablet/small desktop (1200px)");
assertTest(strpos($viewContent, '@media (max-width: 992px)') !== false, "Defines breakpoint for tablet (992px)");
assertTest(strpos($viewContent, '@media (max-width: 768px)') !== false, "Defines breakpoint for mobile devices (768px)");
assertTest(strpos($viewContent, 'grid-template-columns: 1fr;') !== false, "Mobile stacks columns cleanly into 1fr");
echo "\n";

// --- TEST GROUP 6: Preservation of Normal POST & Zero AJAX ---
echo "--- GROUP 6: Preservation of Normal POST & Zero AJAX ---\n";
assertTest(strpos($viewContent, 'action="<?= url(\'/office/company/save\') ?>"') !== false, "All forms submit to /office/company/save");
assertTest(strpos($viewContent, 'fetch(') === false, "Zero fetch() calls in view");
assertTest(strpos($viewContent, 'XMLHttpRequest') === false, "Zero XMLHttpRequest calls in view");
assertTest(strpos($viewContent, 'location.reload') === false, "Zero location.reload calls in view");
assertTest(strpos($viewContent, 'preventDefault') === false, "Zero preventDefault calls in view");
assertTest(strpos($viewContent, 'alert(') === false, "Zero alert() calls in view");
assertTest(strpos($viewContent, 'confirm(') === false, "Zero confirm() calls in view");
echo "\n";

// --- TEST GROUP 7: Native Core PHP Form Submission & Page Reload Flow ---
echo "--- GROUP 7: Native Core PHP Form Submission & Page Reload Flow ---\n";
assertTest(substr_count($viewContent, 'data-no-ajax="true"') >= 10, "Forms and buttons bypass app.js SPA interceptor with data-no-ajax='true'");
assertTest(substr_count($viewContent, 'data-native="true"') >= 5, "All 5 section forms declare data-native='true'");
assertTest(substr_count($viewContent, 'name="native_submit" value="1"') >= 5, "All 5 section forms include native_submit flag");

// Test controller redirect behavior with native_submit
$controller = new \App\Controllers\CompanyController();
$reqNative = new \App\Core\Request([], [
    'section'         => 'brand_identity',
    'company_name'    => 'Reload Verification Travel Ltd',
    'app_name_source' => 'company_name',
    'native_submit'   => '1',
    'csrf_token'      => csrf_token(),
], [
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', // Even with AJAX header, native_submit MUST force 302 redirect
]);
$resNative = $controller->save($reqNative);
assertTest($resNative->getStatusCode() === 302, "native_submit forces HTTP 302 redirect for full browser reload");
assertTest(strpos($resNative->getHeader('Location'), '/office/company') !== false, "Redirect targets /office/company to trigger fresh GET");
echo "\n";

echo "====================================================================\n";
echo "ALL 31 LAYOUT ARCHITECTURE & NATIVE RELOAD ASSERTIONS PASSED!\n";
echo "====================================================================\n";
