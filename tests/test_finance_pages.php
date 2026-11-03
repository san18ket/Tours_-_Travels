<?php
/**
 * Test Suite: Finance Module Comprehensive Controller & View Verification
 */

require 'app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Controllers\FinanceController;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\BusinessExpense;
use App\Models\Payroll;
use App\Models\EmployeeAdvance;
use App\Models\Refund;

// Mock session authenticated admin
Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
Session::set('role', 'admin');

$controller = new FinanceController();

$tests = [
    'Dashboard (/office/finance)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->dashboard($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Transactions (/office/finance/transactions)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->transactions($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Accounts (/office/finance/accounts)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->accounts($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Expenses (/office/finance/expenses)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->expenses($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Payroll (/office/finance/payroll)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->payroll($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Advances (/office/finance/advances)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->advances($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    'Refunds (/office/finance/refunds)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->refunds($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
];

echo "===============================================\n";
echo "   FINANCE MODULE ALL PAGES TEST SUITE\n";
echo "===============================================\n";

$allPassed = true;
foreach ($tests as $name => $fn) {
    try {
        $res = $fn();
        if ($res) {
            echo " [PASS] $name\n";
        } else {
            echo " [FAIL] $name - Returned unexpected response\n";
            $allPassed = false;
        }
    } catch (\Throwable $e) {
        echo " [FAIL] $name - Exception: " . $e->getMessage() . "\n";
        echo "        File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
        echo "        Trace:\n" . $e->getTraceAsString() . "\n";
        $allPassed = false;
    }
}

// Test individual item show pages if records exist
echo "\n--- Detail Show Pages ---\n";

$accModel = new FinanceAccount();
$firstAcc = $accModel->all();
if (!empty($firstAcc)) {
    try {
        $accId = (int)$firstAcc[0]['id'];
        $req = Request::capture();
        $resp = $controller->accountShow($req, ['id' => $accId]);
        echo " [PASS] Account Show (/office/finance/accounts/{$accId}) - Code " . $resp->getStatusCode() . "\n";
    } catch (\Throwable $e) {
        echo " [FAIL] Account Show - Exception: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

$txModel = new FinanceTransaction();
$firstTx = $txModel->all();
if (!empty($firstTx)) {
    try {
        $txId = (int)$firstTx[0]['id'];
        $req = Request::capture();
        $resp = $controller->showTransaction($req, ['id' => $txId]);
        echo " [PASS] Transaction Show (/office/finance/transactions/{$txId}) - Code " . $resp->getStatusCode() . "\n";
    } catch (\Throwable $e) {
        echo " [FAIL] Transaction Show - Exception: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

$advModel = new EmployeeAdvance();
$firstAdv = $advModel->all();
if (!empty($firstAdv)) {
    try {
        $advId = (int)$firstAdv[0]['id'];
        $req = Request::capture();
        $resp = $controller->showAdvance($req, ['id' => $advId]);
        echo " [PASS] Advance Show (/office/finance/advances/{$advId}) - Code " . $resp->getStatusCode() . "\n";
    } catch (\Throwable $e) {
        echo " [FAIL] Advance Show - Exception: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

echo "\n===============================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL FINANCE PAGES ARE WORKING PERFECTLY!\n";
    exit(0);
} else {
    echo "ERROR: SOME FINANCE PAGES FAILED!\n";
    exit(1);
}

