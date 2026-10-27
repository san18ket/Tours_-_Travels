<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\CustomerController;
use App\Core\Request;
use App\Core\Session;
use App\Models\Customer;
use App\Models\User;

$testName = "Customer Status Toggle & Dynamic AJAX Top Cards Verification";
echo "======================================================\n";
echo " RUNNING SUITE: {$testName}\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  ✓ PASS: {$label}\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: {$label}\n";
        $failed++;
    }
}

// 1. Setup admin session
$userModel = new User();
$adminUser = $userModel->findBy('role', 'admin');
if (!$adminUser) {
    echo "  ✗ FAIL: No admin user found\n";
    exit(1);
}

Session::set('user_id', (int)$adminUser['id']);
Session::set('user_role', 'admin');
Session::set('user_name', $adminUser['name']);

$customerModel = new Customer();
$customerController = new CustomerController();

// Create or find a test customer for toggling
$testPhone = '9999900011';
$existing = $customerModel->findBy('phone', $testPhone);
if ($existing) {
    $customerId = (int)$existing['id'];
    $customerModel->update($customerId, ['status' => 'active']);
} else {
    $customerId = $customerModel->create([
        'customer_code'   => $customerModel->generateCustomerCode(),
        'name'            => 'Test Dynamic Card Customer',
        'phone'           => $testPhone,
        'email'           => 'dynamic.cards@test.com',
        'status'          => 'active',
        'city'            => 'Mumbai',
        'state'           => 'Maharashtra',
        'address_line'    => '123 Marine Drive',
        'created_at'      => date('Y-m-d H:i:s'),
        'updated_at'      => date('Y-m-d H:i:s'),
    ]);
}

assertTest($customerId > 0, "Test customer created/loaded with ID {$customerId}");

// 2. Test toggleStatus via AJAX to Inactive
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST = [
    'status' => 'inactive',
    '_csrf_token' => csrf_token()
];

$req = new Request();
$response = $customerController->toggleStatus($req, (string)$customerId);

assertTest($response->getStatusCode() === 200, "AJAX toggleStatus returns HTTP 200");
$json = json_decode($response->getContent(), true);

assertTest(!empty($json['success']) && $json['success'] === true, "AJAX response contains success = true");
assertTest(($json['status'] ?? '') === 'inactive', "AJAX response status reflects new status 'inactive'");
assertTest(isset($json['metrics']), "AJAX response contains 'metrics' object");
assertTest(isset($json['metrics']['total']), "Metrics contains 'total' count: " . ($json['metrics']['total'] ?? 0));
assertTest(isset($json['metrics']['active']), "Metrics contains 'active' count: " . ($json['metrics']['active'] ?? 0));
assertTest(isset($json['metrics']['inactive']), "Metrics contains 'inactive' count: " . ($json['metrics']['inactive'] ?? 0));
assertTest(isset($json['metrics']['with_bookings']), "Metrics contains 'with_bookings' count: " . ($json['metrics']['with_bookings'] ?? 0));

$inactiveCountBefore = (int)$json['metrics']['inactive'];
$activeCountBefore = (int)$json['metrics']['active'];

// 3. Test toggleStatus via AJAX back to Active
$_POST['status'] = 'active';
$req2 = new Request();
$response2 = $customerController->toggleStatus($req2, (string)$customerId);
$json2 = json_decode($response2->getContent(), true);

assertTest(!empty($json2['success']) && $json2['success'] === true, "AJAX toggle back to active succeeded");
assertTest(($json2['status'] ?? '') === 'active', "AJAX response status reflects 'active'");
assertTest((int)$json2['metrics']['active'] === $activeCountBefore + 1, "Active metric count incremented by 1 dynamically without refresh");
assertTest((int)$json2['metrics']['inactive'] === $inactiveCountBefore - 1, "Inactive metric count decremented by 1 dynamically without refresh");

// 4. Test Customer Directory rendering with metrics & cards
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_GET = [];

$reqIndex = new Request();
$indexResponse = $customerController->index($reqIndex);
$html = $indexResponse->getContent();

assertTest(strpos($html, 'id="customersKpiGrid"') !== false, "Index view renders #customersKpiGrid");
assertTest(strpos($html, 'data-status="active"') !== false, "Index view renders data-status='active' KPI card");
assertTest(strpos($html, 'data-status="with_bookings"') !== false, "Index view renders data-status='with_bookings' KPI card");
assertTest(strpos($html, 'data-status="inactive"') !== false, "Index view renders data-status='inactive' KPI card");
assertTest(strpos($html, 'id="customersMvStatusScroll"') !== false, "Index view renders mobile status pills container");
assertTest(strpos($html, 'updateCustomerMetrics') !== false, "Index view includes updateCustomerMetrics synchronization function");
assertTest(strpos($html, 'window.updateCustomerMetrics = updateCustomerMetrics') !== false, "Index view globally exposes updateCustomerMetrics");

// 5. Test AJAX filtering with status = active
$_GET = ['status' => 'active'];
$reqFilterActive = new Request();
$activeHtml = $customerController->index($reqFilterActive)->getContent();
assertTest(strpos($activeHtml, 'data-status="active" role="button" tabindex="0"') !== false, "Active KPI card rendered on active filter request");

// 6. Test AJAX filtering with status = inactive
$_GET = ['status' => 'inactive'];
$reqFilterInactive = new Request();
$inactiveHtml = $customerController->index($reqFilterInactive)->getContent();
assertTest(strpos($inactiveHtml, 'data-status="inactive" role="button" tabindex="0"') !== false, "Inactive KPI card rendered on inactive filter request");

// Clean up test customer
$customerModel->delete($customerId);

echo "\n======================================================\n";
echo " TEST EXECUTION SUMMARY\n";
echo "======================================================\n";
echo " Passed: {$passed}\n";
echo " Failed: {$failed}\n";
echo " Total:  " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo " ALL CUSTOMER AJAX STATUS TOGGLE & TOP CARDS TESTS PASSED!\n";
    exit(0);
} else {
    echo " SOME TESTS FAILED.\n";
    exit(1);
}
