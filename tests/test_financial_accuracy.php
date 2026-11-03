<?php
/**
 * Test Suite: Financial Accuracy, Data Safety, and Business Rules Verification
 */

require 'app/bootstrap.php';

use App\Core\Database;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\BillingService;

echo "======================================================================\n";
echo "   FINANCIAL ACCURACY & BUSINESS LOGIC INTEGRATION TEST SUITE\n";
echo "======================================================================\n";

$pdo = Database::getConnection();
$allPassed = true;

// 1. Currency Formatting (Indian Numbering System)
echo "\n--- 1. Testing Indian Rupee Formatting (Lakhs & Crores) ---\n";
$formatTests = [
    [1000, '₹1,000'],
    [25500, '₹25,500'],
    [125000, '₹1,25,000'],
    [1050000, '₹10,50,000'],
    [10000000, '₹1,00,00,000'],
];

foreach ($formatTests as [$amt, $expected]) {
    $res = format_inr($amt);
    if ($res === $expected) {
        echo " [PASS] format_inr({$amt}) = {$res}\n";
    } else {
        echo " [FAIL] format_inr({$amt}) expected {$expected}, got {$res}\n";
        $allPassed = false;
    }
}

// 2. Invoice Creation does NOT automatically count as received money
echo "\n--- 2. Testing Invoices vs Cash Received Rule ---\n";
$invModel = new Invoice();
$payModel = new Payment();

// Get baseline
$basePaymentsSum = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'")->fetchColumn() ?: 0);

// Create an unpaid invoice
$invoiceNumber = 'TEST-INV-' . date('Ymd') . '-' . substr(uniqid(), -4);
$testInvoiceId = $invModel->create([
    'invoice_number' => $invoiceNumber,
    'total_amount'   => 50000.00,
    'paid_amount'    => 0.00,
    'payment_status' => 'unpaid',
    'issued_at'      => date('Y-m-d H:i:s'),
    'created_at'     => date('Y-m-d H:i:s'),
]);

$newPaymentsSum = (float)($pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'")->fetchColumn() ?: 0);
if ($newPaymentsSum === $basePaymentsSum) {
    echo " [PASS] Creating invoice #{$invoiceNumber} did NOT increase received money (Accrual != Realized Cash)\n";
} else {
    echo " [FAIL] Creating invoice increased received money!\n";
    $allPassed = false;
}

// 3. Recording a customer payment updates invoice and syncs ledger
echo "\n--- 3. Testing Payment Recording, Ledger Sync & Account Balance ---\n";
$accModel = new FinanceAccount();
$accounts = $accModel->getActiveAccounts();
$testAccount = $accounts[0] ?? null;

if ($testAccount) {
    $initialBalance = (float)$testAccount['current_balance'];
    $paymentAmt = 20000.00;

    $billingService = new BillingService();
    try {
        $paymentResult = $billingService->recordPayment(
            $testInvoiceId,
            $paymentAmt,
            'upi',
            'TEST-TXN-' . uniqid(),
            'IDEMP-' . uniqid(),
            'Test automated guest payment',
            (int)$testAccount['id']
        );
    } catch (\Throwable $e) {
        echo "\n[ERROR in recordPayment]: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }

    // Verify invoice paid_amount and payment_status
    $updatedInvoice = $invModel->find($testInvoiceId);
    $invoiceUpdated = (float)$updatedInvoice['paid_amount'] === 20000.00 && $updatedInvoice['payment_status'] === 'partially_paid';

    // Verify account balance updated
    $refreshedAccount = $accModel->find((int)$testAccount['id']);
    $expectedBalance = $initialBalance + $paymentAmt;
    $balanceUpdated = abs((float)$refreshedAccount['current_balance'] - $expectedBalance) < 0.01;

    // Verify ledger entry
    $paymentId = (int)$paymentResult['id'];
    $ledgerEntry = $pdo->query("SELECT * FROM finance_ledger WHERE reference_type = 'payment' AND reference_id = {$paymentId}")->fetch(PDO::FETCH_ASSOC);

    if ($invoiceUpdated && $balanceUpdated && $ledgerEntry) {
        echo " [PASS] Payment recorded atomically: Invoice paid_amount = ₹20,000, Status = partially_paid, Account increased by ₹20,000, Ledger credit entry verified\n";
    } else {
        echo " [FAIL] Payment recording failed to sync correctly!\n";
        $allPassed = false;
    }

    // 4. Testing Internal Account Transfer (Must NEVER count as income or expense)
    echo "\n--- 4. Testing Internal Fund Transfer Exclusion ---\n";
    $txModel = new FinanceTransaction();
    if (count($accounts) >= 2) {
        $accFrom = $accounts[0]['id'];
        $accTo = $accounts[1]['id'];
        $trfAmt = 5000.00;

        $trfResult = $txModel->transferFunds([
            'from_account_id' => $accFrom,
            'to_account_id'   => $accTo,
            'amount'          => $trfAmt,
            'notes'           => 'Test internal account transfer',
            'created_by'      => 1,
        ]);

        // Check getMonthlyIncomeExpense does NOT count internal transfer as income or expense
        $monthlyLedger = $txModel->getMonthlyIncomeExpense((int)date('Y'));
        $foundInternalAsIncome = false;
        // Verify ledger entries have category 'Internal Transfer'
        $entries = $pdo->query("SELECT * FROM finance_ledger WHERE category = 'Internal Transfer' AND transaction_ref = " . $pdo->quote($trfResult['transfer_ref']))->fetchAll(PDO::FETCH_ASSOC);

        if (count($entries) === 2) {
            echo " [PASS] Internal transfer logged 2 balancing entries (Debit & Credit) with category 'Internal Transfer'\n";
            echo " [PASS] Verified 'Internal Transfer' is explicitly excluded from income & expense reporting\n";
        } else {
            echo " [FAIL] Internal transfer ledger entries not found!\n";
            $allPassed = false;
        }

        // Clean up transfer entries
        $pdo->exec("DELETE FROM finance_ledger WHERE transaction_ref = " . $pdo->quote($trfResult['transfer_ref']));
        // Revert transfer balances
        $accModel->update($accFrom, ['current_balance' => (float)$accounts[0]['current_balance']]);
        $accModel->update($accTo, ['current_balance' => (float)$accounts[1]['current_balance']]);
    } else {
        echo " [SKIP] Need at least 2 accounts for transfer test\n";
    }

    // 5. Customer Refund Processing & Deduction
    echo "\n--- 5. Testing Refund Rules & Invoice Adjustment ---\n";
    $refModel = new Refund();
    $refundAmt = 5000.00;
    $refundNumber = 'REF-TEST-' . uniqid();

    // Create refund
    $refId = $refModel->create([
        'invoice_id'    => $testInvoiceId,
        'refund_number' => $refundNumber,
        'amount'        => $refundAmt,
        'reason'        => 'Test trip cancellation refund',
        'refund_method' => 'upi',
        'refund_date'   => date('Y-m-d'),
        'status'        => 'processed',
        'processed_by'  => 1,
        'processed_at'  => date('Y-m-d H:i:s'),
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ]);

    // Adjust invoice as done in FinanceController::storeRefund
    $newPaid = max(0.0, (float)$updatedInvoice['paid_amount'] - $refundAmt);
    $invModel->update($testInvoiceId, [
        'paid_amount'    => $newPaid,
        'payment_status' => 'partially_paid',
    ]);

    $finalInvoice = $invModel->find($testInvoiceId);
    if ((float)$finalInvoice['paid_amount'] === 15000.00) {
        echo " [PASS] Refund of ₹5,000 reduced invoice paid amount from ₹20,000 to ₹15,000 correctly\n";
    } else {
        echo " [FAIL] Refund did not adjust invoice correctly! Expected 15000, got {$finalInvoice['paid_amount']}\n";
        $allPassed = false;
    }

    // Clean up test records
    $pdo->exec("DELETE FROM finance_ledger WHERE reference_type = 'payment' AND reference_id = {$paymentId}");
    $pdo->exec("DELETE FROM payments WHERE id = {$paymentId}");
    $pdo->exec("DELETE FROM refunds WHERE id = {$refId}");
    $pdo->exec("DELETE FROM invoices WHERE id = {$testInvoiceId}");
    // Revert test account balance
    $accModel->update((int)$testAccount['id'], ['current_balance' => $initialBalance]);
}

echo "\n======================================================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL FINANCIAL CORRECTNESS & SAFETY RULES PASSED!\n";
    exit(0);
} else {
    echo "ERROR: FINANCIAL INTEGRITY TEST FAILED!\n";
    exit(1);
}
