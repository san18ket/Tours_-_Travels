<?php
/**
 * Automated Verification Script: Multi-Language Switching & Full View Translation
 */
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Core\Language;

$errors = [];
$passes = [];

function assertContainsString($needle, $haystack, $testName) {
    global $errors, $passes;
    if (strpos($haystack, $needle) !== false) {
        $passes[] = "[PASS] $testName: Found '$needle'";
        return true;
    } else {
        $errors[] = "[FAIL] $testName: FAILED to find '$needle'";
        return false;
    }
}

echo "========================================================\n";
echo "   TOURS & TRAVELS — LANGUAGE VERIFICATION SUITE       \n";
echo "========================================================\n";

// 1. Test Language::setLocale and Language::getLocale
echo "\n--- 1. Testing Core Language Switching ---\n";
foreach (['en', 'hi', 'mr'] as $loc) {
    Language::setLocale($loc);
    if (Language::getLocale() === $loc) {
        $passes[] = "[PASS] Language::setLocale('$loc') correctly set active locale.";
    } else {
        $errors[] = "[FAIL] Language::setLocale('$loc') returned '" . Language::getLocale() . "'";
    }
}

// 2. Test translations across all 3 languages
echo "\n--- 2. Testing Helper __() Translation Output ---\n";

// Test vehicle create keys
Language::setLocale('en');
assertContainsString('Vehicle Specifications', __('vehicle.specifications', [], 'Vehicle Specifications'), 'EN vehicle spec');
Language::setLocale('hi');
assertContainsString('वाहन विनिर्देश', __('vehicle.specifications', [], 'Vehicle Specifications'), 'HI vehicle spec');
Language::setLocale('mr');
assertContainsString('वाहनाचे तपशील', __('vehicle.specifications', [], 'Vehicle Specifications'), 'MR vehicle spec');

// Test employee create keys
Language::setLocale('en');
assertContainsString('Personal Identity', __('employee.personal_identity', [], 'Personal Identity'), 'EN employee identity');
Language::setLocale('hi');
assertContainsString('व्यक्तिगत पहचान', __('employee.personal_identity', [], 'Personal Identity'), 'HI employee identity');
Language::setLocale('mr');
assertContainsString('वैयक्तिक ओळख', __('employee.personal_identity', [], 'Personal Identity'), 'MR employee identity');

// Test employee role & contract
Language::setLocale('en');
assertContainsString('Role & Contract', __('employee.role_contract', [], 'Role & Contract'), 'EN employee role');
Language::setLocale('hi');
assertContainsString('पद और अनुबंध', __('employee.role_contract', [], 'Role & Contract'), 'HI employee role');
Language::setLocale('mr');
assertContainsString('पद आणि करार', __('employee.role_contract', [], 'Role & Contract'), 'MR employee role');

// Test employee compliance & docs
Language::setLocale('en');
assertContainsString('License & Documents', __('employee.license_docs', [], 'License & Documents'), 'EN employee docs');
Language::setLocale('hi');
assertContainsString('लाइसेंस और दस्तावेज़', __('employee.license_docs', [], 'License & Documents'), 'HI employee docs');
Language::setLocale('mr');
assertContainsString('परवाना आणि दस्तऐवज', __('employee.license_docs', [], 'License & Documents'), 'MR employee docs');

// Test employee show KPI & tab keys
Language::setLocale('en');
assertContainsString('Fleet operations assigned', __('employee.fleet_operations_assigned', [], 'Fleet operations assigned'), 'EN employee operations');
Language::setLocale('hi');
assertContainsString('फ्लीट परिचालन सौंपे गए', __('employee.fleet_operations_assigned', [], 'Fleet operations assigned'), 'HI employee operations');
Language::setLocale('mr');
assertContainsString('फ्लीट ऑपरेशन्स नियुक्त केले', __('employee.fleet_operations_assigned', [], 'Fleet operations assigned'), 'MR employee operations');

// 3. Test View Rendering Simulation for vehicles/create.php
echo "\n--- 3. Testing View Render: office/vehicles/create.php ---\n";
function renderVehicleCreate($locale) {
    Language::setLocale($locale);
    $categories = [
        ['category_key' => 'sedan', 'category_name' => 'Sedan', 'seating_capacity' => 4, 'luggage_capacity' => 3, 'fuel_type' => 'Petrol'],
        ['category_key' => 'suv', 'category_name' => 'SUV', 'seating_capacity' => 7, 'luggage_capacity' => 5, 'fuel_type' => 'Diesel'],
    ];
    $drivers = [
        ['id' => 1, 'first_name' => 'Rajesh', 'last_name' => 'Kumar', 'phone' => '9876543210']
    ];
    $old = [];
    $errors = [];
    
    ob_start();
    include APP_ROOT . '/app/Views/office/vehicles/create.php';
    return ob_get_clean();
}

$hiVehHtml = renderVehicleCreate('hi');
assertContainsString('वाहन विनिर्देश', $hiVehHtml, 'HI vehicles/create step 1');
assertContainsString('संचालन और ओडोमीटर', $hiVehHtml, 'HI vehicles/create step 2');
assertContainsString('दस्तावेज़ और अनुपालन', $hiVehHtml, 'HI vehicles/create step 3');
assertContainsString('समीक्षा एवं सहेजें', $hiVehHtml, 'HI vehicles/create step 4');

$mrVehHtml = renderVehicleCreate('mr');
assertContainsString('वाहनाचे तपशील', $mrVehHtml, 'MR vehicles/create step 1');
assertContainsString('ऑपरेशन्स आणि ओडोमीटर', $mrVehHtml, 'MR vehicles/create step 2');
assertContainsString('दस्तऐवज आणि कायदेशीर पूर्तता', $mrVehHtml, 'MR vehicles/create step 3');
assertContainsString('पुनरावलोकन व जतन', $mrVehHtml, 'MR vehicles/create step 4');

// 4. Test View Rendering Simulation for employees/create.php
echo "\n--- 4. Testing View Render: office/employees/create.php ---\n";
function renderEmployeeCreate($locale) {
    Language::setLocale($locale);
    $old = [];
    $errors = [];
    $roles = [
        ['slug' => 'admin', 'name' => 'Administrator'],
        ['slug' => 'dispatcher', 'name' => 'Dispatcher'],
        ['slug' => 'driver', 'name' => 'Driver'],
    ];
    $employeeCode = 'EMP-0099';
    $unlinkedUsers = [];
    
    ob_start();
    include APP_ROOT . '/app/Views/office/employees/create.php';
    return ob_get_clean();
}

$hiEmpHtml = renderEmployeeCreate('hi');
assertContainsString('व्यक्तिगत पहचान', $hiEmpHtml, 'HI employees/create step 1');
assertContainsString('पद और अनुबंध', $hiEmpHtml, 'HI employees/create step 2');
assertContainsString('लाइसेंस और दस्तावेज़', $hiEmpHtml, 'HI employees/create step 3');
assertContainsString('क्रेडेंशियल और एक्सेस', $hiEmpHtml, 'HI employees/create step 4');
assertContainsString('समीक्षा करें और जमा करें', $hiEmpHtml, 'HI employees/create step 5');

$mrEmpHtml = renderEmployeeCreate('mr');
assertContainsString('वैयक्तिक ओळख', $mrEmpHtml, 'MR employees/create step 1');
assertContainsString('पद आणि करार', $mrEmpHtml, 'MR employees/create step 2');
assertContainsString('परवाना आणि दस्तऐवज', $mrEmpHtml, 'MR employees/create step 3');
assertContainsString('लॉगिन आणि प्रवेश', $mrEmpHtml, 'MR employees/create step 4');
assertContainsString('तपासा आणि सबमिट करा', $mrEmpHtml, 'MR employees/create step 5');

// 5. Test View Rendering Simulation for employees/show.php
echo "\n--- 5. Testing View Render: office/employees/show.php ---\n";
function renderEmployeeShow($locale) {
    Language::setLocale($locale);
    $employee = [
        'id' => 1,
        'first_name' => 'Vikram',
        'last_name' => 'Shinde',
        'employee_code' => 'EMP-0001',
        'phone' => '9876543210',
        'email' => 'vikram@example.com',
        'department' => 'fleet_chauffeur',
        'designation' => 'Driver',
        'role' => 'driver',
        'employment_type' => 'full_time',
        'status' => 'active',
        'duty_status' => 'on_duty',
        'license_number' => 'MH-14-2019-001234',
        'joining_date' => '2023-01-15',
    ];
    $dispatches = [];
    
    ob_start();
    include APP_ROOT . '/app/Views/office/employees/show.php';
    return ob_get_clean();
}

$hiShowHtml = renderEmployeeShow('hi');
assertContainsString('फ्लीट परिचालन सौंपे गए', $hiShowHtml, 'HI employees/show KPI');
assertContainsString('डिस्पैच के लिए तैयार', $hiShowHtml, 'HI employees/show ready for dispatch');
assertContainsString('कानूनी पूरा नाम', $hiShowHtml, 'HI employees/show legal full name');
assertContainsString('अनुपालन दस्तावेज़ और डिजिटल स्कैन', $hiShowHtml, 'HI employees/show compliance docs');

$mrShowHtml = renderEmployeeShow('mr');
assertContainsString('फ्लीट ऑपरेशन्स नियुक्त केले', $mrShowHtml, 'MR employees/show KPI');
assertContainsString('डिस्पॅचसाठी सज्ज', $mrShowHtml, 'MR employees/show ready for dispatch');
assertContainsString('कायदेशीर पूर्ण नाव', $mrShowHtml, 'MR employees/show legal full name');
assertContainsString('अनुपालन कागदपत्रे आणि डिजिटल स्कॅन', $mrShowHtml, 'MR employees/show compliance docs');

// 6. Output Results
echo "\n========================================================\n";
echo "SUMMARY:\n";
echo "  Total Tests Run: " . (count($passes) + count($errors)) . "\n";
echo "  Passed: " . count($passes) . "\n";
echo "  Failed: " . count($errors) . "\n";

if (empty($errors)) {
    echo "\n>>> ALL MULTI-LANGUAGE VERIFICATION TESTS PASSED (100%)! <<<\n";
    exit(0);
} else {
    echo "\nERRORS ENCOUNTERED:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    exit(1);
}
