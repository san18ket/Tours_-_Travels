<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\EmployeeController;
use App\Core\Request;
use App\Core\Session;
use App\Models\Employee;
use App\Models\DriverProfile;

echo "====================================================================\n";
echo "RUNNING TEST: Employees Modern 6-Col Table, Address & Status Toggle\n";
echo "====================================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

// 1. Setup Admin session
Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

// 2. Test GET /office/employees view rendering
echo "\n1. Verifying View Structure in GET /office/employees...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/employees';
$_GET = [];
$_POST = [];

$controller = new EmployeeController();
$req = new Request();
$response = $controller->index($req);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "office/employees returns HTTP 200");

// Check the columns in table header
assertCondition(preg_match('/<th[^>]*>#<\/th>/i', $html) === 1, "Table has column 1: '#'");
assertCondition(strpos($html, '>Profile</th>') !== false, "Table has column 2: 'Profile' before Employee Details");
assertCondition(strpos($html, 'Employee Details') !== false, "Table has column 3: 'Employee Details'");
assertCondition(strpos($html, 'Employee &amp; Role') !== false || strpos($html, 'Employee & Role') !== false, "Table has column 4: 'Employee & Role'");
assertCondition(strpos($html, '<th style="min-width: 200px;">Address</th>') !== false, "Table has column 5: 'Address'");
assertCondition(strpos($html, '<th class="text-center" style="width: 130px;">Status</th>') !== false, "Table has column 6: 'Status'");
assertCondition(strpos($html, '<th class="text-center" style="width: 80px;">Action</th>') !== false, "Table has column 7: 'Action'");

// Verify Employee Details shows name, email, mobile
assertCondition(strpos($html, 'cust-name-row') !== false, "Employee Details includes customer-style name row");
assertCondition(strpos($html, 'cust-email-row') !== false, "Employee Details includes email row");
assertCondition(strpos($html, 'cust-phone-row') !== false, "Employee Details includes mobile phone row");

// Verify Profile column contains avatar wrap
assertCondition(strpos($html, 'cust-table-avatar-wrap') !== false, "Profile column contains .cust-table-avatar-wrap");

// Verify Employee & Role shows role pill
assertCondition(strpos($html, 'emp-role-pill') !== false, "Employee & Role column contains .emp-role-pill showing role");

// Verify removed individual separate columns
assertCondition(strpos($html, '<th style="min-width: 140px;">Staff Code</th>') === false, "Old standalone 'Staff Code' column header is REMOVED");
assertCondition(strpos($html, '<th style="min-width: 170px;">Department</th>') === false, "Old standalone 'Department' column header is REMOVED");
assertCondition(strpos($html, '<th style="min-width: 180px;">Contact Information</th>') === false, "Old standalone 'Contact Information' column header is REMOVED");

// Check address cell elements
assertCondition(strpos($html, 'cust-address-cell') !== false, "Contains .cust-address-cell matching customer styling");
assertCondition(strpos($html, 'cust-address-line') !== false || strpos($html, 'cust-no-address') !== false, "Contains address line or empty address placeholder");

// Check status toggle button elements
assertCondition(strpos($html, 'class="cust-status-toggle') !== false, "Contains .cust-status-toggle component in table");
assertCondition(strpos($html, 'toggle-slider-track') !== false, "Contains .toggle-slider-track");
assertCondition(strpos($html, 'toggle-slider-thumb') !== false, "Contains .toggle-slider-thumb");
assertCondition(strpos($html, 'toggleEmployeeStatus') !== false, "Contains toggleEmployeeStatus handler");

// Check Top KPI Cards & Live Metric update hooks
assertCondition(strpos($html, 'id="employeesKpiGrid"') !== false, "Contains #employeesKpiGrid");
assertCondition(strpos($html, 'data-kpi="total"') !== false, "Card 1 has data-kpi='total'");
assertCondition(strpos($html, 'data-kpi="active"') !== false, "Card 2 has data-kpi='active'");
assertCondition(strpos($html, 'data-kpi="on_leave"') !== false, "Card 3 has data-kpi='on_leave'");
assertCondition(strpos($html, 'data-kpi="chauffeurs"') !== false, "Card 4 has data-kpi='chauffeurs'");

assertCondition(strpos($html, 'updateEmployeeMetrics') !== false, "Contains updateEmployeeMetrics function");
assertCondition(strpos($html, 'filterEmployeesAjax') !== false, "Contains filterEmployeesAjax function");
assertCondition(strpos($html, 'initEmployeeAjaxFilter') !== false, "Contains initEmployeeAjaxFilter function");

// 3. Test POST /office/employees/{id}/toggle-status
echo "\n2. Verifying AJAX Status Toggle Functionality & Top Cards Metric payload...\n";

// Create a test employee
$empModel = new Employee();
$uniqueCode = 'TEST-' . rand(1000, 9999);
$testPhone = '+91 97' . rand(10000000, 99999999);
$userModel = new App\Models\User();
$testUserId = $userModel->create([
    'name'     => 'Toggle Tester',
    'username' => 'toggle.' . rand(1000, 9999),
    'email'    => 'toggle' . rand(100, 999) . '@test.corp',
    'password' => password_hash('Pass@123', PASSWORD_BCRYPT),
    'role'     => 'driver',
    'status'   => 'active',
]);

$empId = $empModel->create([
    'user_id'         => $testUserId,
    'employee_code'   => $uniqueCode,
    'first_name'      => 'Toggle',
    'last_name'       => 'Tester',
    'email'           => 'toggle' . rand(100, 999) . '@test.corp',
    'phone'           => $testPhone,
    'department'      => 'fleet_chauffeur',
    'designation'     => 'Chauffeur / Driver',
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'status'          => 'active',
    'address'         => '42 Palm Avenue, Bandra West, Mumbai - 400050',
    'address_line'    => '42 Palm Avenue, Bandra West',
    'city'            => 'Mumbai',
    'state'           => 'Maharashtra',
    'postal_code'     => '400050',
]);

$driverModel = new DriverProfile();
$driverId = $driverModel->create([
    'employee_id'     => $empId,
    'user_id'         => $testUserId,
    'license_number'  => 'DL-TOGGLE-' . rand(100, 999),
    'duty_status'     => 'on_duty',
]);

assertCondition($empId > 0, "Created temporary employee for toggle verification (ID: {$empId})");

// Step 3a: Toggle from 'active' to 'on_leave'
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST = [
    'status' => 'on_leave',
    'csrf_token' => csrf_token(),
];

$toggleReq = new Request();
$toggleResp = $controller->toggleStatus($toggleReq, ['id' => $empId]);
$json = json_decode((string)$toggleResp->getContent(), true);

assertCondition($toggleResp->getStatusCode() === 200, "toggleStatus returns HTTP 200 on AJAX");
assertCondition(!empty($json['success']) && $json['success'] === true, "toggleStatus response JSON success === true");
assertCondition($json['status'] === 'on_leave', "Employee status toggled to 'on_leave'");
assertCondition($json['is_active'] === false, "is_active flag is false");
assertCondition($json['label'] === 'On Leave', "label is 'On Leave'");
assertCondition(isset($json['metrics']['total']) && isset($json['metrics']['active']) && isset($json['metrics']['on_leave']), "Metrics payload contains total, active, and on_leave counts for top cards");

// Verify in database
$updatedEmp = $empModel->find($empId);
assertCondition($updatedEmp['status'] === 'on_leave', "Database record status is updated to 'on_leave'");

$updatedDriver = $driverModel->find($driverId);
assertCondition($updatedDriver['duty_status'] === 'off_duty', "Linked driver_profiles duty_status synced to 'off_duty'");

// Step 3b: Toggle back from 'on_leave' to 'active'
$_POST['status'] = 'active';
$toggleReq2 = new Request();
$toggleResp2 = $controller->toggleStatus($toggleReq2, ['id' => $empId]);
$json2 = json_decode((string)$toggleResp2->getContent(), true);

assertCondition($json2['status'] === 'active', "Employee status toggled back to 'active'");
assertCondition($json2['is_active'] === true, "is_active flag is true");
assertCondition($json2['label'] === 'Active', "label is 'Active'");

$updatedEmp2 = $empModel->find($empId);
assertCondition($updatedEmp2['status'] === 'active', "Database record status is updated back to 'active'");

$updatedDriver2 = $driverModel->find($driverId);
assertCondition($updatedDriver2['duty_status'] === 'on_duty', "Linked driver_profiles duty_status synced back to 'on_duty'");

// Clean up
$pdo = App\Core\Database::getConnection();
$pdo->exec("DELETE FROM `driver_profiles` WHERE id = " . (int)$driverId);
$pdo->exec("DELETE FROM `employees` WHERE id = " . (int)$empId);
if (!empty($testUserId)) {
    $pdo->exec("DELETE FROM `users` WHERE id = " . (int)$testUserId);
}
echo "  [INFO] Cleaned up temporary test employee, driver profile, and user.\n";

echo "\n====================================================================\n";
echo "SUMMARY: {$passed} / " . ($passed + $failed) . " assertions passed.\n";
echo "====================================================================\n";
