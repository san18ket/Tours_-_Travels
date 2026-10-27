<?php
/**
 * Test Suite: Form Localization Across All Operational Modules
 * Verifies that switching language dynamically updates labels, buttons,
 * placeholders, and system language defaults in English, Hindi, and Marathi.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Language;

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  ✓ PASS: $label\n";
    } else {
        $failed++;
        echo "  ✗ FAIL: $label\n";
    }
}

echo "\n======================================================\n";
echo " RUNNING SUITE: Form Localization & Devanagari Script Tests\n";
echo "======================================================\n";

// Test 1: Language Dictionary Loading
Language::setLocale('en');
assertCheck(__('user_form.create_title') === 'Create User Account', '1a. EN User title');
assertCheck(__('form.preferred_language') === 'Preferred System Language', '1b. EN preferred language label');
assertCheck(__('customer_form.register_title') === 'Register Customer Profile', '1c. EN Customer register title');
assertCheck(__('employee_form.register_title') === 'Register Staff & Chauffeur Profile', '1d. EN Employee register title');
assertCheck(__('vehicle_form.register_title') === 'Register Commercial Vehicle', '1e. EN Vehicle register title');
assertCheck(__('tour_form.create_title') === 'Create New Tour Package', '1f. EN Tour package create title');
assertCheck(__('trip_form.assign_title') === 'Assign Trip Duty to Driver', '1g. EN Trip assign duty title');
assertCheck(__('invoice_form.allocate_payment') === 'Allocate Payment', '1h. EN Payment allocate title');
assertCheck(__('role_form.add_title') === 'Add Operational Role', '1i. EN Role add title');

// Test 2: Hindi Form Localization
Language::setLocale('hi');
assertCheck(__('user_form.create_title') === 'उपयोगकर्ता खाता बनाएं', '2a. HI User title in Devanagari');
assertCheck(__('form.preferred_language') === 'पसंदीदा सिस्टम भाषा', '2b. HI Preferred system language label');
assertCheck(__('customer_form.register_title') === 'ग्राहक प्रोफ़ाइल पंजीकृत करें', '2c. HI Customer register title');
assertCheck(__('employee_form.register_title') === 'स्टाफ एवं चालक प्रोफ़ाइल पंजीकृत करें', '2d. HI Employee register title');
assertCheck(__('vehicle_form.register_title') === 'वाणिज्यिक वाहन पंजीकृत करें', '2e. HI Vehicle register title');
assertCheck(__('tour_form.create_title') === 'नया टूर पैकेज बनाएं', '2f. HI Tour package create title');
assertCheck(__('trip_form.assign_title') === 'ड्राइवर को यात्रा ड्यूटी सौंपें', '2g. HI Trip assign duty title');
assertCheck(__('invoice_form.allocate_payment') === 'भुगतान रिकॉर्ड करें', '2h. HI Payment allocate title');
assertCheck(__('role_form.add_title') === 'नई परिचालन भूमिका जोड़ें', '2i. HI Role add title');
assertCheck(__('auth.show_hide_password') === 'पासवर्ड दिखाएं/छुपाएं', '2j. HI Show hide password text');

// Test 3: Marathi Form Localization
Language::setLocale('mr');
assertCheck(__('user_form.create_title') === 'वापरकर्ता खाते तयार करा', '3a. MR User title in Devanagari');
assertCheck(__('form.preferred_language') === 'पसंतीची प्रणाली भाषा', '3b. MR Preferred system language label');
assertCheck(__('customer_form.register_title') === 'ग्राहक प्रोफाइल नोंदणी करा', '3c. MR Customer register title');
assertCheck(__('employee_form.register_title') === 'कर्मचारी व चालक प्रोफाइल नोंदणी करा', '3d. MR Employee register title');
assertCheck(__('vehicle_form.register_title') === 'व्यावसायिक वाहन नोंदणी करा', '3e. MR Vehicle register title');
assertCheck(__('tour_form.create_title') === 'नवीन टूर पॅकेज तयार करा', '3f. MR Tour package create title');
assertCheck(__('trip_form.assign_title') === 'चालकास फेरी ड्युटी वाटप करा', '3g. MR Trip assign duty title');
assertCheck(__('invoice_form.allocate_payment') === 'देयक नोंदवा', '3h. MR Payment allocate title');
assertCheck(__('role_form.add_title') === 'नवीन कार्यपालन भूमिका जोडा', '3i. MR Role add title');
assertCheck(__('auth.show_hide_password') === 'पासवर्ड दाखवा/लपवा', '3j. MR Show hide password text');

// Test 4: Default Preferred Language Follows Current Locale
echo "\n======================================================\n";
echo " RUNNING SUITE: Preferred Language Dynamic Default Tests\n";
echo "======================================================\n";

Language::setLocale('hi');
$currentLocale = Language::getLocale();
assertCheck($currentLocale === 'hi', '4a. Current locale is Hindi (hi)');

// Simulate user form default language selection logic:
$defaultLanguage = $old['language'] ?? Language::getLocale();
assertCheck($defaultLanguage === 'hi', '4b. User form default language automatically selects hi when officer views in Hindi');

Language::setLocale('mr');
$currentLocale = Language::getLocale();
assertCheck($currentLocale === 'mr', '4c. Current locale is Marathi (mr)');
$defaultLanguage = $old['language'] ?? Language::getLocale();
assertCheck($defaultLanguage === 'mr', '4d. User form default language automatically selects mr when officer views in Marathi');

Language::setLocale('en');
$currentLocale = Language::getLocale();
assertCheck($currentLocale === 'en', '4e. Current locale is English (en)');
$defaultLanguage = $old['language'] ?? Language::getLocale();
assertCheck($defaultLanguage === 'en', '4f. User form default language automatically selects en when officer views in English');

// Test 5: Render user creation view under all three languages
echo "\n======================================================\n";
echo " RUNNING SUITE: View File Rendering Verification\n";
echo "======================================================\n";

function renderViewBuffer(string $viewPath, array $data = []): string {
    extract($data);
    ob_start();
    include $viewPath;
    return ob_get_clean() ?: '';
}

$mockEmployees = [
    [
        'id' => 101,
        'first_name' => 'Rajesh',
        'last_name' => 'Sharma',
        'employee_code' => 'EMP-001',
        'designation' => 'Fleet Driver',
        'department' => 'fleet_chauffeur',
        'phone' => '+91 9876543210',
        'email' => 'rajesh@voyagego.com'
    ]
];
$mockRoles = [
    ['slug' => 'admin', 'name' => 'Administrator', 'description' => 'Full access'],
    ['slug' => 'driver', 'name' => 'Chauffeur', 'description' => 'Duty portal']
];

// 5a. User Create Form in Hindi
Language::setLocale('hi');
$userCreateHtmlHi = renderViewBuffer(
    __DIR__ . '/../app/Views/office/users/create.php',
    ['employees' => $mockEmployees, 'roles' => $mockRoles, 'errors' => []]
);
assertCheck(strpos($userCreateHtmlHi, 'उपयोगकर्ता खाता बनाएं') !== false, '5a. User create rendered Hindi title');
assertCheck(strpos($userCreateHtmlHi, 'पसंदीदा सिस्टम भाषा') !== false, '5b. User create rendered Hindi preferred language label');
assertCheck(strpos($userCreateHtmlHi, 'value="hi" selected') !== false, '5c. User create automatically selected Hindi default language');

// 5b. User Create Form in Marathi
Language::setLocale('mr');
$userCreateHtmlMr = renderViewBuffer(
    __DIR__ . '/../app/Views/office/users/create.php',
    ['employees' => $mockEmployees, 'roles' => $mockRoles, 'errors' => []]
);
assertCheck(strpos($userCreateHtmlMr, 'वापरकर्ता खाते तयार करा') !== false, '5d. User create rendered Marathi title');
assertCheck(strpos($userCreateHtmlMr, 'पसंतीची प्रणाली भाषा') !== false, '5e. User create rendered Marathi preferred language label');
assertCheck(strpos($userCreateHtmlMr, 'value="mr" selected') !== false, '5f. User create automatically selected Marathi default language');

// 5c. Customer Create Form in Hindi & Marathi
Language::setLocale('hi');
$customerCreateHtmlHi = renderViewBuffer(
    __DIR__ . '/../app/Views/office/customers/create.php',
    ['errors' => [], 'old' => [], 'customerCode' => 'CUST-HI-01']
);
assertCheck(strpos($customerCreateHtmlHi, 'ग्राहक प्रोफ़ाइल पंजीकृत करें') !== false, '5g. Customer create rendered Hindi title');
assertCheck(strpos($customerCreateHtmlHi, 'मूलभूत जानकारी') !== false, '5h. Customer create rendered Hindi step 1 title');

Language::setLocale('mr');
$customerCreateHtmlMr = renderViewBuffer(
    __DIR__ . '/../app/Views/office/customers/create.php',
    ['errors' => [], 'old' => [], 'customerCode' => 'CUST-MR-01']
);
assertCheck(strpos($customerCreateHtmlMr, 'ग्राहक प्रोफाइल नोंदणी करा') !== false, '5i. Customer create rendered Marathi title');
assertCheck(strpos($customerCreateHtmlMr, 'मूलभूत माहिती') !== false, '5j. Customer create rendered Marathi step 1 title');

// 5d. Vehicle Create Form in Hindi & Marathi
Language::setLocale('hi');
$vehicleCreateHtmlHi = renderViewBuffer(
    __DIR__ . '/../app/Views/office/vehicles/create.php',
    ['errors' => [], 'old' => []]
);
assertCheck(strpos($vehicleCreateHtmlHi, 'वाणिज्यिक वाहन पंजीकृत करें') !== false, '5k. Vehicle create rendered Hindi title');

Language::setLocale('mr');
$vehicleCreateHtmlMr = renderViewBuffer(
    __DIR__ . '/../app/Views/office/vehicles/create.php',
    ['errors' => [], 'old' => []]
);
assertCheck(strpos($vehicleCreateHtmlMr, 'व्यावसायिक वाहन नोंदणी करा') !== false, '5l. Vehicle create rendered Marathi title');

// 5e. Tour Create Form in Hindi & Marathi
Language::setLocale('hi');
$tourCreateHtmlHi = renderViewBuffer(
    __DIR__ . '/../app/Views/office/tours/create.php',
    []
);
assertCheck(strpos($tourCreateHtmlHi, 'नया टूर पैकेज बनाएं') !== false, '5m. Tour create rendered Hindi title');
assertCheck(strpos($tourCreateHtmlHi, 'टूर विनिर्देश') !== false, '5n. Tour create rendered Hindi specs header');

Language::setLocale('mr');
$tourCreateHtmlMr = renderViewBuffer(
    __DIR__ . '/../app/Views/office/tours/create.php',
    []
);
assertCheck(strpos($tourCreateHtmlMr, 'नवीन टूर पॅकेज तयार करा') !== false, '5o. Tour create rendered Marathi title');
assertCheck(strpos($tourCreateHtmlMr, 'टूर वैशिष्ट्ये') !== false, '5p. Tour create rendered Marathi specs header');

// Reset to English
Language::setLocale('en');

echo "\n======================================================\n";
echo " TEST SUMMARY\n";
echo "======================================================\n";
echo " Passed: $passed\n";
echo " Failed: $failed\n";
echo " Total:  " . ($passed + $failed) . "\n\n";

if ($failed > 0) {
    exit(1);
}
echo " ALL FORM LOCALIZATION TESTS PASSED!\n";
