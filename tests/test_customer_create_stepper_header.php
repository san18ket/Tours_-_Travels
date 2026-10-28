<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\CustomerController;
use App\Core\Request;
use App\Core\Session;
use App\Models\Customer;
use App\Models\User;

$testName = "Customer Create Centered Professional Stepper Suite";
echo "======================================================\n";
echo " RUNNING SUITE: {$testName}\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

// 1. Setup admin session
$userModel = new User();
$adminUser = $userModel->findBy('role', 'admin');
if (!$adminUser) {
    echo "  [FAIL] No admin user found\n";
    exit(1);
}

Session::set('user_id', (int)$adminUser['id']);
Session::set('user_role', 'admin');
Session::set('user_name', $adminUser['name']);

$createFile = dirname(__DIR__) . '/app/Views/office/customers/create.php';
assertCheck(file_exists($createFile), "customers/create.php view exists");
$content = file_get_contents($createFile);

echo "\n--- 1. Testing Removal of Left Side & Top Bar Back Buttons ---\n";
assertCheck(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $content), "Top bar back button element is removed");
assertCheck(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $content), "Top bar admin panel button element is removed");
assertCheck(strpos($content, 'class="modern-section-header-left"') === false, "Left section .modern-section-header-left is completely removed");
assertCheck(strpos($content, 'id="wizardHeaderBadge"') === false, "Left badge #wizardHeaderBadge is completely removed");
assertCheck(strpos($content, 'id="wizardHeaderTitle"') === false, "Left title #wizardHeaderTitle is completely removed");
assertCheck(strpos($content, 'id="wizardHeaderSub"') === false, "Left subtitle #wizardHeaderSub is completely removed");

echo "\n--- 2. Testing Centered Professional Stepper Header Structure ---\n";
assertCheck(strpos($content, 'class="modern-stepper-header-centered"') !== false, "Centered stepper header .modern-stepper-header-centered is present");
assertCheck(strpos($content, 'class="modern-stepper-container"') !== false, "Stepper container .modern-stepper-container is present");
assertCheck(strpos($content, 'class="modern-stepper-track"') !== false, "Stepper track .modern-stepper-track is present");

echo "\n--- 3. Testing 4-Step Stepper Items & Connectors ---\n";
assertCheck(strpos($content, 'data-step-target="1"') !== false, "Step 1 target button exists");
assertCheck(strpos($content, 'data-step-target="2"') !== false, "Step 2 target button exists");
assertCheck(strpos($content, 'data-step-target="3"') !== false, "Step 3 target button exists");
assertCheck(strpos($content, 'data-step-target="4"') !== false, "Step 4 target button exists");

assertCheck(strpos($content, 'id="stepConn1"') !== false, "Connector #stepConn1 exists");
assertCheck(strpos($content, 'id="stepConn2"') !== false, "Connector #stepConn2 exists");
assertCheck(strpos($content, 'id="stepConn3"') !== false, "Connector #stepConn3 exists");

echo "\n--- 4. Testing JavaScript Navigation Functions ---\n";
assertCheck(strpos($content, 'function updateStepperUI(activeStep)') !== false, "updateStepperUI function exists");
assertCheck(strpos($content, 'function wizardGoToStep(targetStep)') !== false, "wizardGoToStep function exists");
assertCheck(strpos($content, 'function wizardNextStep(currentStepNum)') !== false, "wizardNextStep function exists");

echo "\n--- 5. Testing Responsive CSS for Centered Stepper ---\n";
assertCheck(strpos($content, '.modern-stepper-header-centered {') !== false, "CSS rule for .modern-stepper-header-centered defined");
assertCheck(strpos($content, '.modern-stepper-container {') !== false, "CSS rule for .modern-stepper-container defined");
assertCheck(strpos($content, '.modern-stepper-track {') !== false, "CSS rule for .modern-stepper-track defined");
assertCheck(strpos($content, '@media (max-width: 768px)') !== false, "CSS @media (max-width: 768px) defined for tablets");
assertCheck(strpos($content, '@media (max-width: 480px)') !== false, "CSS @media (max-width: 480px) defined for compact phones");
assertCheck(strpos($content, '.modern-step-item:not(.active) .modern-step-label') !== false, "CSS hides inactive labels on small phones to prevent overflow");

echo "\n--- 6. Testing End-to-End Customer Form Submission ---\n";
$customerModel = new Customer();
$uniquePhone = '98' . mt_rand(10000000, 99999999);
$testCode = $customerModel->generateCustomerCode();

$postData = [
    'customer_code' => $testCode,
    'name' => 'Centered Stepper Test Customer',
    'phone' => $uniquePhone,
    'email' => 'centered.stepper@example.com',
    'address' => '505 Boulevard Center',
    'city' => 'Pune',
    'state' => 'Maharashtra',
    'pincode' => '411001',
    'country' => 'India',
    'status' => 'active'
];

$newId = $customerModel->create($postData);
assertCheck($newId > 0, "Successfully created customer record with ID {$newId}");

$fetched = $customerModel->find($newId);
assertCheck($fetched !== null, "Created customer record fetched successfully");
assertCheck($fetched['phone'] === $uniquePhone, "Customer phone saved correctly");
assertCheck($fetched['name'] === 'Centered Stepper Test Customer', "Customer name saved correctly");

// Cleanup test record
$customerModel->delete($newId);
$deleted = $customerModel->find($newId);
assertCheck($deleted === null, "Test customer record cleaned up cleanly");

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
