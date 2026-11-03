<?php
/**
 * Test Suite: Booking Creation -> Invoice Generation -> Advance Payment -> Remaining Payment -> Finance Ledger
 */

require 'app/bootstrap.php';

use App\Core\Database;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\BillingService;
use App\Services\BookingService;

echo "======================================================================\n";
echo "   BOOKING INVOICE & ADVANCE PAYMENT COMPLETE LIFECYCLE TEST SUITE\n";
echo "======================================================================\n";

$pdo = Database::getConnection();
$allPassed = true;

function assertCondition(bool $condition, string $passMsg, string $failMsg, &$allPassed): void {
    if ($condition) {
        echo " [PASS] {$passMsg}\n";
    } else {
        echo " [FAIL] {$failMsg}\n";
        $allPassed = false;
    }
}

// Mock EmailService to avoid SMTP socket timeout during tests
class TestMockEmailService extends \App\Services\EmailService {
    public function sendBookingCreated(array $booking): array {
        return ['success' => true, 'message' => 'mocked'];
    }
    public function sendPaymentSuccessful(array $payment, ?array $invoice = null, ?array $booking = null): array {
        return ['success' => true, 'message' => 'mocked'];
    }
    public function sendInvoiceIssued(array $invoice, ?array $booking = null): array {
        return ['success' => true, 'message' => 'mocked'];
    }
}

$bookingModel = new Booking();
$invoiceModel = new Invoice();
$paymentModel = new Payment();
$billingService = new BillingService();
$mockEmailService = new TestMockEmailService();
$billingService->emailService = $mockEmailService;
$bookingService = new BookingService($bookingModel, new \App\Models\AuditLog(), $mockEmailService, $billingService);

// Retrieve active cash or bank account for testing
$accountModel = new FinanceAccount();
$activeAccounts = $accountModel->getActiveAccounts();
if (empty($activeAccounts)) {
    // Create one test account if none exists
    $testAccountId = $accountModel->create([
        'account_name'    => 'Test Current Bank Account',
        'account_type'    => 'bank',
        'account_number'  => 'TEST-BANK-001',
        'bank_name'       => 'HDFC Bank',
        'current_balance' => 50000.00,
        'is_active'       => 1,
    ]);
    $activeAccount = $accountModel->find($testAccountId);
} else {
    $activeAccount = $activeAccounts[0];
}
$testAccountId = (int)$activeAccount['id'];

// Get a valid customer user id
$stmtUser = $pdo->query("SELECT id FROM users LIMIT 1");
$testUserId = (int)($stmtUser->fetchColumn() ?: 1);

// -----------------------------------------------------------------------------
// Test 1: Booking with Advance = 0 (Unpaid)
// -----------------------------------------------------------------------------
echo "\n--- 1. Testing Advance = 0 (Booking + Invoice Generated, Unpaid) ---\n";
$bookingData1 = [
    'customer_id'     => $testUserId,
    'pickup_address'  => 'Mumbai International Airport T2',
    'drop_address'    => 'Taj Mahal Palace, Colaba',
    'start_date'      => date('Y-m-d', strtotime('+2 days')),
    'end_date'        => date('Y-m-d', strtotime('+3 days')),
    'passenger_count' => 2,
    'custom_subtotal' => 5000.00,
    'discount_amount' => 0.00,
    'gst_rate'        => 5.0,
    'include_gst'     => 1,
    'advance_amount'  => 0.00,
    'idempotency_token' => 'TEST-ADV-ZERO-' . bin2hex(random_bytes(6)),
];

$b1 = $bookingService->createBooking($bookingData1);
assertCondition(!empty($b1['id']), "Booking #{$b1['booking_code']} created successfully", "Failed to create Booking 1", $allPassed);

$inv1 = $invoiceModel->findBy('booking_id', $b1['id']);
assertCondition(!empty($inv1), "Invoice #{$inv1['invoice_number']} automatically generated for Booking", "No invoice generated for Booking 1", $allPassed);
assertCondition((float)$inv1['total_amount'] === 5250.00, "Invoice total is ₹5,250.00 (₹5,000 + 5% GST)", "Expected 5250.00, got {$inv1['total_amount']}", $allPassed);
assertCondition((float)$inv1['paid_amount'] === 0.00, "Invoice paid amount is ₹0.00", "Expected paid_amount 0.00, got {$inv1['paid_amount']}", $allPassed);
assertCondition(strtolower($inv1['payment_status']) === 'unpaid', "Invoice status is 'unpaid'", "Expected 'unpaid', got {$inv1['payment_status']}", $allPassed);

$payments1 = $paymentModel->getPaymentsByInvoice($inv1['id']);
assertCondition(count($payments1) === 0, "Zero payment records created for Advance = 0", "Found " . count($payments1) . " payments for advance = 0", $allPassed);

// -----------------------------------------------------------------------------
// Test 2: Booking with Partial Advance (e.g. ₹5,000 on ₹10,500 total)
// -----------------------------------------------------------------------------
echo "\n--- 2. Testing Partial Advance (₹5,000 on ₹10,500 Total) ---\n";
$accBefore = (new FinanceAccount())->find($testAccountId);
$balBefore = (float)$accBefore['current_balance'];

$bookingData2 = [
    'customer_id'     => $testUserId,
    'pickup_address'  => 'Pune Railway Station',
    'drop_address'    => 'Lonavala Hill Resort',
    'start_date'      => date('Y-m-d', strtotime('+4 days')),
    'end_date'        => date('Y-m-d', strtotime('+5 days')),
    'passenger_count' => 3,
    'custom_subtotal' => 10000.00,
    'discount_amount' => 0.00,
    'gst_rate'        => 5.0,
    'include_gst'     => 1,
    'advance_amount'  => 5000.00,
    'payment_method'  => 'upi',
    'account_id'      => $testAccountId,
    'transaction_id'  => 'UPI-ADV-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
    'payment_notes'   => '50% upfront deposit received via Google Pay',
    'received_date'   => date('Y-m-d'),
    'idempotency_token' => 'TEST-ADV-PARTIAL-' . bin2hex(random_bytes(6)),
];

$b2 = $bookingService->createBooking($bookingData2);
assertCondition(!empty($b2['id']), "Booking #{$b2['booking_code']} created with partial advance", "Failed to create Booking 2", $allPassed);

$inv2 = $invoiceModel->findBy('booking_id', $b2['id']);
assertCondition(!empty($inv2), "Invoice #{$inv2['invoice_number']} automatically generated", "No invoice generated for Booking 2", $allPassed);
assertCondition((float)$inv2['total_amount'] === 10500.00, "Invoice total is ₹10,500.00 (₹10,000 + 5% GST)", "Expected 10500.00, got {$inv2['total_amount']}", $allPassed);
assertCondition((float)$inv2['paid_amount'] === 5000.00, "Invoice paid amount is ₹5,000.00", "Expected paid_amount 5000.00, got {$inv2['paid_amount']}", $allPassed);
assertCondition(in_array(strtolower($inv2['payment_status']), ['partial', 'partially_paid']), "Invoice status is 'partially_paid'", "Expected 'partially_paid', got {$inv2['payment_status']}", $allPassed);

$payments2 = $paymentModel->getPaymentsByInvoice($inv2['id']);
assertCondition(count($payments2) === 1, "Exactly 1 payment record created for advance", "Expected 1 payment, found " . count($payments2), $allPassed);
assertCondition((float)$payments2[0]['amount'] === 5000.00, "Payment amount is ₹5,000.00", "Expected 5000.00, got {$payments2[0]['amount']}", $allPassed);
assertCondition($payments2[0]['payment_method'] === 'upi', "Payment method is 'upi'", "Expected 'upi', got {$payments2[0]['payment_method']}", $allPassed);

// Ledger & Account check
$accAfter = (new FinanceAccount())->find($testAccountId);
$balAfter = (float)$accAfter['current_balance'];
$balDiff = round($balAfter - $balBefore, 2);
assertCondition($balDiff === 5000.00, "Finance account balance increased by exactly ₹5,000.00 (Old: {$balBefore}, New: {$balAfter})", "Balance delta mismatch: expected 5000.00, got {$balDiff}", $allPassed);

$stmtLedger = $pdo->prepare("SELECT * FROM finance_ledger WHERE account_id = ? AND transaction_type = 'credit' ORDER BY id DESC LIMIT 1");
$stmtLedger->execute([$testAccountId]);
$ledgerEntry = $stmtLedger->fetch();
assertCondition(!empty($ledgerEntry) && (float)$ledgerEntry['amount'] === 5000.00, "Finance ledger credited with ₹5,000.00", "Ledger entry missing or amount incorrect", $allPassed);

// -----------------------------------------------------------------------------
// Test 3: Booking with 100% Full Payment Upfront
// -----------------------------------------------------------------------------
echo "\n--- 3. Testing Full Advance (100% Upfront Payment) ---\n";
$accBefore3 = (new FinanceAccount())->find($testAccountId);
$balBefore3 = (float)$accBefore3['current_balance'];

$bookingData3 = [
    'customer_id'     => $testUserId,
    'pickup_address'  => 'Bangalore Airport',
    'drop_address'    => 'Electronic City Phase 1',
    'start_date'      => date('Y-m-d', strtotime('+1 day')),
    'end_date'        => date('Y-m-d', strtotime('+1 day')),
    'passenger_count' => 1,
    'custom_subtotal' => 2000.00,
    'discount_amount' => 0.00,
    'include_gst'     => 0, // Tax exempt
    'advance_amount'  => 2000.00,
    'payment_method'  => 'bank_transfer',
    'account_id'      => $testAccountId,
    'transaction_id'  => 'NEFT-FULL-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
    'payment_notes'   => '100% full advance payment via NEFT transfer',
    'received_date'   => date('Y-m-d'),
    'idempotency_token' => 'TEST-ADV-FULL-' . bin2hex(random_bytes(6)),
];

$b3 = $bookingService->createBooking($bookingData3);
assertCondition(!empty($b3['id']), "Booking #{$b3['booking_code']} created with 100% advance", "Failed to create Booking 3", $allPassed);

$inv3 = $invoiceModel->findBy('booking_id', $b3['id']);
assertCondition(!empty($inv3), "Invoice #{$inv3['invoice_number']} automatically generated", "No invoice generated for Booking 3", $allPassed);
assertCondition((float)$inv3['total_amount'] === 2000.00, "Invoice total is ₹2,000.00", "Expected 2000.00, got {$inv3['total_amount']}", $allPassed);
assertCondition((float)$inv3['paid_amount'] === 2000.00, "Invoice paid amount is ₹2,000.00", "Expected 2000.00, got {$inv3['paid_amount']}", $allPassed);
assertCondition(strtolower($inv3['payment_status']) === 'paid', "Invoice status is 'paid' (Fully Paid)", "Expected 'paid', got {$inv3['payment_status']}", $allPassed);

$accAfter3 = (new FinanceAccount())->find($testAccountId);
$balDiff3 = round((float)$accAfter3['current_balance'] - $balBefore3, 2);
assertCondition($balDiff3 === 2000.00, "Account balance increased by ₹2,000.00", "Expected 2000.00 increase, got {$balDiff3}", $allPassed);

// -----------------------------------------------------------------------------
// Test 4: Remaining Payment via BillingService / Invoice Show
// -----------------------------------------------------------------------------
echo "\n--- 4. Testing Remaining Payment on Partial Invoice ---\n";
// Booking 2 has total ₹10,500, paid ₹5,000, remaining ₹5,500
$accBefore4 = (new FinanceAccount())->find($testAccountId);
$balBefore4 = (float)$accBefore4['current_balance'];

$remPayment = $billingService->recordPayment(
    (int)$inv2['id'],
    5500.00,
    'card',
    'CARD-FINAL-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
    'PAY-REM-' . bin2hex(random_bytes(8)),
    'Customer cleared the remaining balance',
    $testAccountId,
    date('Y-m-d')
);

assertCondition(!empty($remPayment['id']), "Remaining payment of ₹5,500.00 recorded successfully", "Failed to record remaining payment", $allPassed);

$inv2Updated = $invoiceModel->find($inv2['id']);
assertCondition((float)$inv2Updated['paid_amount'] === 10500.00, "Invoice paid_amount updated to full ₹10,500.00", "Expected 10500.00, got {$inv2Updated['paid_amount']}", $allPassed);
assertCondition(strtolower($inv2Updated['payment_status']) === 'paid', "Invoice status updated to 'paid'", "Expected 'paid', got {$inv2Updated['payment_status']}", $allPassed);

$paymentsAfter = $paymentModel->getPaymentsByInvoice($inv2['id']);
assertCondition(count($paymentsAfter) === 2, "Invoice now has exactly 2 payments (Advance + Remaining)", "Expected 2 payments, found " . count($paymentsAfter), $allPassed);

$sumPayments = array_sum(array_column($paymentsAfter, 'amount'));
assertCondition($sumPayments == 10500.00, "Sum of all payments equals total invoice amount ₹10,500.00", "Payment sum mismatch: {$sumPayments}", $allPassed);

$accAfter4 = (new FinanceAccount())->find($testAccountId);
$balDiff4 = round((float)$accAfter4['current_balance'] - $balBefore4, 2);
assertCondition($balDiff4 === 5500.00, "Account balance increased by remaining ₹5,500.00", "Expected 5500.00, got {$balDiff4}", $allPassed);

// -----------------------------------------------------------------------------
// Test 5: Overpayment & Negative Amount Protection
// -----------------------------------------------------------------------------
echo "\n--- 5. Testing Overpayment & Safety Checks ---\n";
$overpayBlocked = false;
try {
    // Try paying on invoice 2 which is now fully paid (remaining = 0)
    $billingService->recordPayment(
        (int)$inv2['id'],
        500.00,
        'cash'
    );
} catch (\Throwable $e) {
    $overpayBlocked = true;
    echo " [INFO] Overpayment blocked with message: " . $e->getMessage() . "\n";
}
assertCondition($overpayBlocked, "Prevented payment exceeding remaining balance on invoice", "Overpayment was not blocked!", $allPassed);

$negBlocked = false;
try {
    $billingService->recordPayment(
        (int)$inv1['id'],
        -100.00,
        'cash'
    );
} catch (\Throwable $e) {
    $negBlocked = true;
}
assertCondition($negBlocked, "Prevented negative payment amount", "Negative payment was not blocked!", $allPassed);

// -----------------------------------------------------------------------------
// Test 6: Idempotent Invoice Generation (No Duplicates)
// -----------------------------------------------------------------------------
echo "\n--- 6. Testing Idempotent Invoice Generation (Duplicate Prevention) ---\n";
$inv1Recheck = $billingService->generateInvoice($b1['id']);
assertCondition((int)$inv1Recheck['id'] === (int)$inv1['id'], "Second call to generateInvoice returns existing invoice #{$inv1['invoice_number']}", "Created duplicate invoice", $allPassed);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE booking_id = ?");
$stmtCount->execute([$b1['id']]);
$invCount = (int)$stmtCount->fetchColumn();
assertCondition($invCount === 1, "Exactly 1 invoice exists for booking in database", "Found {$invCount} duplicate invoices", $allPassed);

// -----------------------------------------------------------------------------
// Test 7: Legacy Booking Without Invoice -> Safe Generate Invoice
// -----------------------------------------------------------------------------
echo "\n--- 7. Testing Legacy Booking Invoice Generation ---\n";
// Insert a raw booking without an invoice
$stmtRaw = $pdo->prepare("
    INSERT INTO bookings (booking_code, customer_id, pickup_address, drop_address, start_date, end_date, subtotal, tax_amount, total_amount, status)
    VALUES (?, ?, 'Legacy St', 'Legacy Ave', '2026-10-01', '2026-10-02', 3000.00, 150.00, 3150.00, 'completed')
");
$rawCode = 'VG-LEGACY-' . bin2hex(random_bytes(3));
$stmtRaw->execute([$rawCode, $testUserId]);
$legacyBookingId = (int)$pdo->lastInsertId();

$legacyInv = $billingService->generateInvoice($legacyBookingId);
assertCondition(!empty($legacyInv['id']), "Generated invoice #{$legacyInv['invoice_number']} for legacy booking", "Failed to generate legacy invoice", $allPassed);
assertCondition((float)$legacyInv['total_amount'] === 3150.00, "Legacy invoice total is ₹3,150.00", "Total mismatch", $allPassed);
assertCondition((float)$legacyInv['paid_amount'] === 0.00, "Legacy invoice paid_amount is ₹0.00", "Paid mismatch", $allPassed);

// -----------------------------------------------------------------------------
// Test 8: End-to-End Financial Integrity Reconciliation
// -----------------------------------------------------------------------------
echo "\n--- 8. Testing End-to-End Financial Integrity Reconciliation ---\n";
$stmtPayments = $pdo->prepare("
    SELECT p.id, p.payment_reference, p.amount, p.invoice_id
    FROM payments p
    WHERE p.invoice_id IN (?, ?, ?)
");
$stmtPayments->execute([$inv1['id'], $inv2['id'], $inv3['id']]);
$testPayments = $stmtPayments->fetchAll();

assertCondition(count($testPayments) === 3, "Exactly 3 payments recorded across test scenarios (1 in Test 2, 1 in Test 3, 1 in Test 4)", "Found " . count($testPayments) . " payments", $allPassed);

$paymentIds = array_column($testPayments, 'id');
$placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
$stmtLedgers = $pdo->prepare("
    SELECT id, amount, reference_type, reference_id, balance_after
    FROM finance_ledger
    WHERE reference_type = 'payment' AND reference_id IN ({$placeholders})
");
$stmtLedgers->execute($paymentIds);
$testLedgers = $stmtLedgers->fetchAll();

assertCondition(count($testLedgers) === count($testPayments), "Every payment has a corresponding double-entry ledger record (" . count($testLedgers) . " / " . count($testPayments) . ")", "Ledger count (" . count($testLedgers) . ") does not match payment count (" . count($testPayments) . ")", $allPassed);

echo "\n======================================================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL 8 BOOKING, INVOICE & ADVANCE LIFECYCLE TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED!\n";
    exit(1);
}
