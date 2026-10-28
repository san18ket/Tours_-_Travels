<?php
/**
 * Test: Employee Filter Section Department Removal, Delete Action Button & 2-Step Delete Modals
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Controllers\EmployeeController;
use App\Core\Request;
use App\Core\Database;
use App\Models\Employee;
use App\Models\User;
use App\Models\DriverProfile;

$pdo = Database::getConnection();

function assertCondition($condition, $message) {
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "RUNNING TEST: Employee Filter Clean & 2-Step Delete Modal System\n";
echo "====================================================================\n\n";

// 1. Verify View Structure in GET /office/employees
echo "1. Verifying View Structure in GET /office/employees...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/employees';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$request = new Request();
$controller = new EmployeeController();
$response = $controller->index($request);
$html = (string)$response->getContent();

assertCondition($response->getStatusCode() === 200, "office/employees returns HTTP 200");

// Department dropdown removed
assertCondition(strpos($html, 'id="deptInput"') === false, "Desktop department dropdown (id='deptInput') is REMOVED from filter section");
assertCondition(strpos($html, 'id="mvDeptSelect"') === false, "Mobile department dropdown (id='mvDeptSelect') is REMOVED from filter section");
assertCondition(strpos($html, '<select name="department"') === false, "<select name='department'> is completely absent from filter section");

// Delete button in action popup
assertCondition(strpos($html, 'class="action-btn-table delete"') !== false, "Table action menu contains .action-btn-table.delete button");
assertCondition(strpos($html, 'openDeleteModal(') !== false, "Delete button triggers openDeleteModal() handler");
assertCondition(strpos($html, 'fa-trash-can') !== false, "Delete button displays fa-trash-can icon");

// 2-Step Delete Modals
assertCondition(strpos($html, 'id="deleteModalBackdrop"') !== false, "Contains #deleteModalBackdrop container");
assertCondition(strpos($html, 'id="deleteStep1Modal"') !== false, "Contains Modal 1: #deleteStep1Modal");
assertCondition(strpos($html, 'id="deleteStep1Checkbox"') !== false, "Modal 1 contains confirmation checkbox #deleteStep1Checkbox");
assertCondition(strpos($html, 'id="deleteStep1Btn"') !== false, "Modal 1 contains proceed button #deleteStep1Btn");

assertCondition(strpos($html, 'id="deleteStep2Modal"') !== false, "Contains Modal 2: #deleteStep2Modal");
assertCondition(strpos($html, 'id="delModalTitle2"') !== false, "Modal 2 contains title 'Are you sure?'");
assertCondition(strpos($html, 'id="deleteStep2Checkbox"') !== false, "Modal 2 contains final confirmation checkbox #deleteStep2Checkbox");
assertCondition(strpos($html, 'id="deleteConfirmFinalBtn"') !== false, "Modal 2 contains final action button #deleteConfirmFinalBtn");

// JavaScript handlers
assertCondition(strpos($html, 'function openDeleteModal') !== false, "Contains openDeleteModal JS function");
assertCondition(strpos($html, 'function closeDeleteModal') !== false, "Contains closeDeleteModal JS function");
assertCondition(strpos($html, 'function goToStep2Modal') !== false, "Contains goToStep2Modal JS function");
assertCondition(strpos($html, 'function confirmDeleteFinal') !== false, "Contains confirmDeleteFinal JS function");
assertCondition(strpos($html, '.vg-del-backdrop') !== false, "Contains .vg-del-backdrop modal styling");
assertCondition(strpos($html, '.vg-del-modal') !== false, "Contains .vg-del-modal card styling");

// 2. Functional AJAX Delete Verification
echo "\n2. Verifying AJAX Employee Deletion & Cascade Clean...\n";

// Create test user
$userModel = new User();
$testEmail = 'del_emp_' . time() . '@example.com';
$testUsername = 'del_user_' . time();
$testUserId = $userModel->create([
    'username'   => $testUsername,
    'name'       => 'Test Del Staff',
    'email'      => $testEmail,
    'password'   => password_hash('Secret123!', PASSWORD_BCRYPT),
    'role'       => 'driver',
    'status'     => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

// Create test employee
$empModel = new Employee();
$testEmpCode = 'EMP-DEL-' . time();
$testEmpId = $empModel->create([
    'employee_code'   => $testEmpCode,
    'user_id'         => $testUserId,
    'first_name'      => 'DeleteMe',
    'last_name'       => 'Chauffeur',
    'phone'           => '+91 99999 ' . rand(10000, 99999),
    'email'           => $testEmail,
    'department'      => 'fleet_chauffeur',
    'designation'     => 'Senior Chauffeur',
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'status'          => 'active',
    'created_at'      => date('Y-m-d H:i:s'),
    'updated_at'      => date('Y-m-d H:i:s')
]);
echo "  [PASS] Created temporary test employee ID: {$testEmpId} with User ID: {$testUserId}\n";

// Create linked driver profile
$driverModel = new DriverProfile();
$testDriverId = $driverModel->create([
    'user_id'          => $testUserId,
    'employee_id'      => $testEmpId,
    'license_number'   => 'DL-DEL-' . time(),
    'license_expiry'   => date('Y-m-d', strtotime('+3 years')),
    'experience_years' => 5,
    'duty_status'      => 'off_duty',
    'rating'           => 5.0,
    'created_at'       => date('Y-m-d H:i:s'),
    'updated_at'       => date('Y-m-d H:i:s')
]);
echo "  [PASS] Created temporary linked driver profile ID: {$testDriverId}\n";

// Send AJAX Delete request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = "/office/employees/{$testEmpId}/delete";
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST['_csrf_token'] = csrf_token();
$_POST['csrf_token'] = csrf_token();

$delReq = new Request();
$delResp = $controller->delete($delReq, ['id' => $testEmpId]);

assertCondition($delResp->getStatusCode() === 200, "Employee delete endpoint returns HTTP 200 on AJAX");
$delJson = json_decode((string)$delResp->getContent(), true);
assertCondition(!empty($delJson['success']) && $delJson['success'] === true, "Delete response JSON success === true");
assertCondition(!empty($delJson['message']) && strpos($delJson['message'], 'DeleteMe Chauffeur') !== false, "Response message confirms employee deletion");
assertCondition(isset($delJson['metrics']['total']), "Response contains updated metrics payload for KPI cards");

// Verify DB: Employee record should be removed
$foundEmp = $empModel->find($testEmpId);
assertCondition(empty($foundEmp), "Employee record removed from database");

// Verify DB: Driver profile should be removed
$foundDriver = $driverModel->find($testDriverId);
assertCondition(empty($foundDriver), "Linked driver profile removed from database");

// Verify DB: Linked non-admin user should be removed
$foundUser = $userModel->find($testUserId);
assertCondition(empty($foundUser), "Linked driver user account removed from database");

echo "\n====================================================================\n";
echo "SUMMARY: All assertions passed successfully!\n";
echo "====================================================================\n";
