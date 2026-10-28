<?php
/**
 * Test: Customer & Employee View Header Alignment
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/bootstrap.php';

use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;
use App\Core\Request;
use App\Models\Customer;
use App\Models\Employee;

function assertCheck($cond, $desc) {
    if ($cond) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        exit(1);
    }
}

echo "====================================================================\n";
echo "TEST: Customer & Employee View Header and Hero Alignment\n";
echo "====================================================================\n\n";

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

// 1. Test Customer View (ID 3 or first existing)
$custModel = new Customer();
$cust = $custModel->find(3) ?: $custModel->first();
$custId = $cust ? (int)$cust['id'] : 3;

echo "1. Checking Customer View (ID {$custId})...\n";
$_SERVER['REQUEST_URI'] = "/office/customers/{$custId}";
$req = new Request();
$custCtrl = new CustomerController();
$custRes = $custCtrl->show($req, ['id' => $custId]);
$custHtml = (string)$custRes->getContent();

assertCheck($custRes->getStatusCode() === 200, "Customer view returns 200");
assertCheck(strpos($custHtml, 'class="view-header-bar"') !== false, "Customer has .view-header-bar");
assertCheck(strpos($custHtml, 'class="view-header-title"') === false, "Customer Details title removed from header");
assertCheck(strpos($custHtml, 'back-btn') === false, "Customer back button removed from header");
assertCheck(strpos($custHtml, 'Customer Information &amp; Travel History') === false, "Customer subtitle removed from header");
assertCheck(strpos($custHtml, 'btnCustomerMore') !== false, "Customer action buttons retained (#btnCustomerMore)");
assertCheck(strpos($custHtml, 'class="view-hero-card"') !== false, "Customer view-hero-card present");
assertCheck(strpos($custHtml, 'class="view-hero-scenery"') !== false, "Customer view-hero-scenery present");

// 2. Test Employee View (ID 9 or first existing)
$empModel = new Employee();
$emp = $empModel->find(9) ?: $empModel->first();
$empId = $emp ? (int)$emp['id'] : 9;

echo "\n2. Checking Employee View (ID {$empId})...\n";
$_SERVER['REQUEST_URI'] = "/office/employees/{$empId}";
$empCtrl = new EmployeeController();
$empRes = $empCtrl->show($req, ['id' => $empId]);
$empHtml = (string)$empRes->getContent();

assertCheck($empRes->getStatusCode() === 200, "Employee view returns 200");
assertCheck(strpos($empHtml, 'class="view-header-bar"') !== false, "Employee has .view-header-bar");
assertCheck(strpos($empHtml, 'Staff Details') === false, "Staff Details title removed from header");
assertCheck(strpos($empHtml, 'back-btn') === false, "Employee back button removed from header");
assertCheck(strpos($empHtml, 'Employee Information, Professional Credentials &amp; Roster History') === false, "Employee subtitle removed from header");
assertCheck(strpos($empHtml, 'btnEmployeeMore') !== false, "Employee action buttons retained (#btnEmployeeMore)");
assertCheck(strpos($empHtml, 'class="view-hero-card"') !== false, "Employee view-hero-card present");
assertCheck(strpos($empHtml, 'class="view-hero-scenery"') !== false, "Employee view-hero-scenery present");
assertCheck(strpos($empHtml, 'customer-scenic-bus.png') !== false, "Employee has scenic bus background image matching customer view");

echo "\n====================================================================\n";
echo "SUCCESS: Both Customer & Employee Views verified perfectly!\n";
echo "====================================================================\n";
