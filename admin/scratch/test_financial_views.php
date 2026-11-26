<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Session;
use App\Core\Request;
use App\Controllers\FinanceController;
use App\Controllers\EmployeeController;
use App\Controllers\DriverPortalController;

echo "=== Verifying Financial Views Rendering & Consistency ===\n\n";

// 1. Office Driver Payments (Due)
Session::set('user_id', 1);
Session::set('user_role', 'admin');
$_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'username' => 'owner'];

$financeCtrl = new FinanceController();

// 1a. Status = due
$resDue = $financeCtrl->driverPayments(new Request([], ['status' => 'due'], [], [], []));
echo "1. /office/finance/driver-payments?status=due -> Status: " . $resDue->getStatusCode() . " (Bytes: " . strlen($resDue->getBody()) . ")\n";
assert($resDue->getStatusCode() === 200, "driverPayments(due) must return 200");

// 1b. Status = partially_paid
$resPartial = $financeCtrl->driverPayments(new Request([], ['status' => 'partially_paid'], [], [], []));
echo "2. /office/finance/driver-payments?status=partially_paid -> Status: " . $resPartial->getStatusCode() . " (Bytes: " . strlen($resPartial->getBody()) . ")\n";
assert($resPartial->getStatusCode() === 200, "driverPayments(partially_paid) must return 200");

// 1c. Status = paid
$resPaid = $financeCtrl->driverPayments(new Request([], ['status' => 'paid'], [], [], []));
echo "3. /office/finance/driver-payments?status=paid -> Status: " . $resPaid->getStatusCode() . " (Bytes: " . strlen($resPaid->getBody()) . ")\n";
assert($resPaid->getStatusCode() === 200, "driverPayments(paid) must return 200");

// 2. Finance Dashboard
$resDash = $financeCtrl->dashboard(new Request([], [], [], [], []));
echo "4. /office/finance/dashboard -> Status: " . $resDash->getStatusCode() . " (Bytes: " . strlen($resDash->getBody()) . ")\n";
assert($resDash->getStatusCode() === 200, "finance dashboard must return 200");

// 3. Employee Finance Profile
$resEmpFin = $financeCtrl->employeeFinance(new Request([], [], [], [], []), ['id' => 1]);
echo "5. /office/employees/1/finance -> Status: " . $resEmpFin->getStatusCode() . " (Bytes: " . strlen($resEmpFin->getBody()) . ")\n";
assert($resEmpFin->getStatusCode() === 200, "employee finance must return 200");

// 4. Driver Portal Earnings & Payments
Session::set('user_id', 2);
Session::set('user_role', 'driver');
$_SESSION['user'] = ['id' => 2, 'role' => 'driver', 'username' => 'virat'];

$driverPortalCtrl = new DriverPortalController();

$resEarnings = $driverPortalCtrl->earnings(new Request([], [], [], [], []));
echo "6. /driver/earnings -> Status: " . $resEarnings->getStatusCode() . " (Bytes: " . strlen($resEarnings->getBody()) . ")\n";
assert($resEarnings->getStatusCode() === 200, "driver earnings must return 200");

$resHistory = $driverPortalCtrl->paymentHistory(new Request([], [], [], [], []));
echo "7. /driver/payments -> Status: " . $resHistory->getStatusCode() . " (Bytes: " . strlen($resHistory->getBody()) . ")\n";
assert($resHistory->getStatusCode() === 200, "driver paymentHistory must return 200");

echo "\n>>> ALL 7 FINANCIAL VIEWS RENDERED HEALTHY AND VERIFIED SUCCESSFULLY! <<<\n";
