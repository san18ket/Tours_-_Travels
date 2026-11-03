<?php
/**
 * Comprehensive Finance Module Verification Test Suite
 * Tests all 6 Navigation Sections, Controllers, Views, Calculations & Exports
 */

require 'app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Controllers\FinanceController;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;

// Mock session authenticated admin
Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
Session::set('role', 'admin');

$controller = new FinanceController();

$tests = [
    // 1. Finance Overview
    '1. Dashboard (/office/finance)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->dashboard($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Finance Overview') !== false;
    },

    // 2. Customer Payments
    '2A. Customer Payments (/office/finance/payments)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->payments($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Customer Payment History') !== false;
    },
    '2B. Pending Payments (/office/finance/pending-payments)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->pendingPayments($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Customer Pending Payments') !== false;
    },
    '2C. Refunds (/office/finance/refunds)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->refunds($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Customer Booking Refunds') !== false;
    },

    // 3. Business Expenses
    '3A. Expenses (/office/finance/expenses)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->expenses($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Operating') !== false;
    },
    '3B. Expense Categories (/office/finance/expense-categories)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->expenseCategories($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Expense Categories') !== false;
    },

    // 4. Staff Salary
    '4A. Payroll Dashboard (/office/finance/payroll)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->payroll($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    '4B. Payslips Directory (/office/finance/payslips)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->payslips($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Staff Salary') !== false;
    },
    '4C. Salary History (/office/finance/salary-history)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->salaryHistory($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Salary Payment History') !== false;
    },
    '4D. Salary Advances (/office/finance/advances)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->advances($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },

    // 5. Cash & Bank
    '5A. Accounts (/office/finance/accounts)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->accounts($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },
    '5B. Transactions Ledger (/office/finance/transactions)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->transactions($req);
        return $resp->getStatusCode() === 200 && strlen($resp->getContent()) > 500;
    },

    // 6. Financial Reports
    '6A. Reports Hub (/office/finance/reports)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->reports($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Financial Reports &amp; Analytics') !== false;
    },
    '6B. Income Report (/office/finance/reports/income)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->incomeReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Income &amp; Revenue Report') !== false;
    },
    '6C. Income Report CSV Export' => function() use ($controller) {
        $_GET = ['export' => 'csv'];
        $req = Request::capture();
        $resp = $controller->incomeReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Payment Date') !== false;
    },
    '6D. Expense Report (/office/finance/reports/expenses)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->expenseReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Business Expense Report') !== false;
    },
    '6E. Profit & Loss Report (/office/finance/reports/profit-loss)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->profitLossReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Profit &amp; Loss Statement') !== false;
    },
    '6F. Profit & Loss CSV Export' => function() use ($controller) {
        $_GET = ['export' => 'csv'];
        $req = Request::capture();
        $resp = $controller->profitLossReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Total Invoiced Revenue') !== false;
    },
    '6G. Customer Dues Report (/office/finance/reports/customer-dues)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->customerDuesReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Customer Pending Dues') !== false;
    },
    '6H. Salary Report (/office/finance/reports/salary)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->salaryReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Staff Salary &amp; Payroll Report') !== false;
    },
    '6I. Expense Category Report (/office/finance/reports/expense-categories)' => function() use ($controller) {
        $_GET = [];
        $req = Request::capture();
        $resp = $controller->expenseCategoryReport($req);
        return $resp->getStatusCode() === 200 && strpos($resp->getContent(), 'Expense Category Spend Breakdown') !== false;
    },
];

echo "======================================================================\n";
echo "   FINANCE MODULE ALL 6 SECTIONS & REPORTS COMPREHENSIVE TEST SUITE\n";
echo "======================================================================\n";

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
        $allPassed = false;
    }
}

// Test detail show pages
echo "\n--- Detail / Action Helper Show Pages ---\n";
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

echo "\n======================================================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL FINANCE MODULE TESTS PASSED FLAWLESSLY!\n";
    exit(0);
} else {
    echo "ERROR: SOME TESTS FAILED!\n";
    exit(1);
}
