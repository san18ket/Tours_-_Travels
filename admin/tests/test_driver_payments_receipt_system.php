<?php
declare(strict_types=1);

/**
 * Test Suite: Driver Payment Management & Professional Receipt System
 * Covers all scenarios required by Master Prompt Section 15
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\DriverTripPayment;
use App\Models\Dispatch;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Controllers\FinanceController;
use App\Controllers\DriverPortalController;
use App\Core\Request;

$pdo = Database::getConnection();
$failures = [];
$successCount = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "====================================================================\n";
echo "=== DRIVER PAYMENT MANAGEMENT & RECEIPT SYSTEM TEST SUITE ===\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. Verify Database Schema for driver_trip_payments table
// -----------------------------------------------------------------------------
echo "1. Verifying Database Schema for `driver_trip_payments`...\n";
$stmt = $pdo->query("DESCRIBE `driver_trip_payments`");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

$expectedCols = [
    'id', 'dispatch_id', 'driver_id', 'amount', 'payment_date', 'payment_method',
    'payment_reference', 'account_id', 'status', 'created_by', 'notes',
    'receipt_number', 'trip_amount_snapshot', 'previous_paid_amount',
    'total_paid_after', 'remaining_after', 'attachment_file',
    'reversed_by', 'reversed_at', 'reversal_reason', 'created_at', 'updated_at'
];

foreach ($expectedCols as $col) {
    assertCondition(in_array($col, $cols, true), "Column `{$col}` exists in `driver_trip_payments`", $failures, $successCount);
}

// -----------------------------------------------------------------------------
// 2. Receipt Number Generation Format
// -----------------------------------------------------------------------------
echo "\n2. Testing Receipt Number Generator Format...\n";
$paymentModel = new DriverTripPayment();
$sampleReceipt = $paymentModel->generateReceiptNumber(2026);
assertCondition(
    (bool)preg_match('/^DPR-2026-\d{5}$/', $sampleReceipt),
    "Receipt number matches format DPR-YYYY-XXXXX (Got: {$sampleReceipt})",
    $failures,
    $successCount
);

// -----------------------------------------------------------------------------
// 3. Setup Test Fixtures
// -----------------------------------------------------------------------------
echo "\n3. Setting up Test Fixtures...\n";
$cleanPayments = [];
$cleanDispatches = [];
$cleanBookings = [];
$cleanVehicles = [];
$cleanDrivers = [];
$cleanUsers = [];
$cleanAccounts = [];
$cleanTransactions = [];

try {
    // 3.1 Finance Accounts: Cash, Bank, UPI
    $accModel = new FinanceAccount();
    $cashAccId = (int)$accModel->create([
        'account_name'   => 'Test Cash Register',
        'account_number' => 'CASH-TEST-' . rand(100, 999),
        'account_type'   => 'cash',
        'current_balance'=> 50000.00,
        'opening_balance'=> 50000.00,
        'status'         => 'active',
    ]);
    $cleanAccounts[] = $cashAccId;

    $bankAccId = (int)$accModel->create([
        'account_name'   => 'Test Current Bank',
        'account_number' => 'BANK-TEST-' . rand(100, 999),
        'account_type'   => 'bank',
        'current_balance'=> 100000.00,
        'opening_balance'=> 100000.00,
        'status'         => 'active',
    ]);
    $cleanAccounts[] = $bankAccId;

    $upiAccId = (int)$accModel->create([
        'account_name'   => 'Test UPI Wallet',
        'account_number' => 'UPI-TEST-' . rand(100, 999),
        'account_type'   => 'digital_wallet',
        'current_balance'=> 25000.00,
        'opening_balance'=> 25000.00,
        'status'         => 'active',
    ]);
    $cleanAccounts[] = $upiAccId;

    assertCondition($cashAccId > 0 && $bankAccId > 0 && $upiAccId > 0, "Created Cash, Bank and UPI test finance accounts", $failures, $successCount);

    // 3.2 Create Admin User (for created_by and reversed_by)
    $userModel = new User();
    $adminUserId = (int)$userModel->create([
        'name'          => 'Test Admin Payer',
        'username'      => 'admin_payer_' . uniqid(),
        'email'         => 'admin_payer_' . rand(1000, 9999) . '@test.local',
        'password'      => password_hash('Secret123!', PASSWORD_BCRYPT),
        'role'          => 'admin',
        'status'        => 'active'
    ]);
    $cleanUsers[] = $adminUserId;

    // 3.3 Create Driver 1 User & Driver Profile
    $driver1UserId = (int)$userModel->create([
        'name'          => 'Rajesh Kumar Chauffeur',
        'username'      => 'driver_rajesh_' . uniqid(),
        'email'         => 'rajesh_' . rand(1000, 9999) . '@test.local',
        'password'      => password_hash('Secret123!', PASSWORD_BCRYPT),
        'role'          => 'driver',
        'phone'         => '9876543210',
        'status'        => 'active'
    ]);
    $cleanUsers[] = $driver1UserId;

    $driverModel = new DriverProfile();
    $driver1Id = (int)$driverModel->create([
        'user_id'        => $driver1UserId,
        'license_number' => 'DL-TEST-' . rand(1000, 9999),
        'duty_status'    => 'available',
        'rating'         => 4.90,
    ]);
    $cleanDrivers[] = $driver1Id;

    // 3.4 Create Driver 2 User & Profile (for unauthorized access testing)
    $driver2UserId = (int)$userModel->create([
        'name'          => 'Sunil Sharma Driver',
        'username'      => 'driver_sunil_' . uniqid(),
        'email'         => 'sunil_' . rand(1000, 9999) . '@test.local',
        'password'      => password_hash('Secret123!', PASSWORD_BCRYPT),
        'role'          => 'driver',
        'phone'         => '9876543211',
        'status'        => 'active'
    ]);
    $cleanUsers[] = $driver2UserId;

    $driver2Id = (int)$driverModel->create([
        'user_id'        => $driver2UserId,
        'license_number' => 'DL-TEST-OTHER-' . rand(1000, 9999),
        'duty_status'    => 'available',
        'rating'         => 4.80,
    ]);
    $cleanDrivers[] = $driver2Id;

    // 3.5 Create Vehicle
    $vehicleModel = new Vehicle();
    $vehicleId = (int)$vehicleModel->create([
        'vehicle_number'   => 'MH-14-DP-' . rand(1000, 9999),
        'make_model'       => 'Innova Crysta VX',
        'vehicle_type'     => 'suv',
        'seating_capacity' => 7,
        'fuel_type'        => 'diesel',
        'status'           => 'available',
        'odometer_km'      => 45200,
    ]);
    $cleanVehicles[] = $vehicleId;

    // 3.6 Create Booking
    $bookingModel = new Booking();
    $bookingId = (int)$bookingModel->create([
        'booking_code'    => 'BK-DP-' . strtoupper(bin2hex(random_bytes(3))),
        'pickup_address'  => 'Pune Station',
        'drop_address'    => 'Mumbai Airport T2',
        'start_date'      => date('Y-m-d'),
        'end_date'        => date('Y-m-d', strtotime('+1 day')),
        'pickup_time'     => '08:00:00',
        'passenger_count' => 4,
        'subtotal'        => 12000.00,
        'tax_amount'      => 600.00,
        'discount_amount' => 0.00,
        'total_amount'    => 12600.00,
        'status'          => 'confirmed',
    ]);
    $cleanBookings[] = $bookingId;

    // 3.7 Create Dispatch (Trip) with driver_trip_amount = 5000.00
    $dispatchModel = new Dispatch();
    $dispatchId = (int)$dispatchModel->create([
        'dispatch_code'        => 'DSP-DP-' . strtoupper(bin2hex(random_bytes(3))),
        'booking_id'           => $bookingId,
        'vehicle_id'           => $vehicleId,
        'driver_id'            => $driver1Id,
        'assigned_by'          => $adminUserId,
        'driver_trip_amount'   => 5000.00,
        'driver_paid_amount'   => 0.00,
        'driver_payment_status'=> 'not_due',
        'status'               => 'completed',
    ]);
    $cleanDispatches[] = $dispatchId;

    assertCondition($dispatchId > 0, "Created completed test trip with driver_trip_amount = 5000.00", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 4. Test Partial Driver Payment #1 (₹2000 out of ₹5000)
    // -------------------------------------------------------------------------
    echo "\n4. Recording Partial Driver Payment #1 (₹2000 / ₹5000)...\n";
    $initialCashBal = (float)$accModel->find($cashAccId)['current_balance'];

    $p1 = $paymentModel->recordPayment([
        'dispatch_id'       => $dispatchId,
        'driver_id'         => $driver1Id,
        'amount'            => 2000.00,
        'payment_date'      => date('Y-m-d'),
        'payment_method'    => 'cash',
        'account_id'        => $cashAccId,
        'created_by'        => $adminUserId,
        'notes'             => 'Initial trip advance',
        'payment_reference' => 'ADV-01'
    ]);
    $cleanPayments[] = (int)$p1['id'];

    assertCondition(!empty($p1['receipt_number']), "Receipt number generated: {$p1['receipt_number']}", $failures, $successCount);
    assertCondition((float)$p1['trip_amount_snapshot'] === 5000.00, "Snapshot trip_amount is 5000.00", $failures, $successCount);
    assertCondition((float)$p1['previous_paid_amount'] === 0.00, "Previous paid amount is 0.00", $failures, $successCount);
    assertCondition((float)$p1['total_paid_after'] === 2000.00, "Total paid after is 2000.00", $failures, $successCount);
    assertCondition((float)$p1['remaining_after'] === 3000.00, "Remaining after is 3000.00", $failures, $successCount);

    // Check dispatch record updated
    $dspAfter1 = $dispatchModel->find($dispatchId);
    assertCondition((float)$dspAfter1['driver_paid_amount'] === 2000.00, "Dispatch driver_paid_amount updated to 2000.00", $failures, $successCount);
    assertCondition($dspAfter1['driver_payment_status'] === 'partially_paid', "Dispatch payment_status is 'partially_paid'", $failures, $successCount);

    // Check cash account balance debited
    $cashBalAfter1 = (float)$accModel->find($cashAccId)['current_balance'];
    assertCondition(round($cashBalAfter1, 2) === round($initialCashBal - 2000.00, 2), "Cash account balance debited by ₹2000 (was {$initialCashBal}, now {$cashBalAfter1})", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 5. Test Partial Driver Payment #2 (₹1500 out of remaining ₹3000)
    // -------------------------------------------------------------------------
    echo "\n5. Recording Partial Driver Payment #2 (₹1500 / ₹3000 remaining)...\n";
    $initialBankBal = (float)$accModel->find($bankAccId)['current_balance'];

    $p2 = $paymentModel->recordPayment([
        'dispatch_id'       => $dispatchId,
        'driver_id'         => $driver1Id,
        'amount'            => 1500.00,
        'payment_date'      => date('Y-m-d'),
        'payment_method'    => 'bank_transfer',
        'account_id'        => $bankAccId,
        'created_by'        => $adminUserId,
        'notes'             => 'Part payment mid-trip',
        'payment_reference' => 'TXN-BANK-101'
    ]);
    $cleanPayments[] = (int)$p2['id'];

    assertCondition(!empty($p2['receipt_number']) && $p2['receipt_number'] !== $p1['receipt_number'], "Unique second receipt generated: {$p2['receipt_number']}", $failures, $successCount);
    assertCondition((float)$p2['previous_paid_amount'] === 2000.00, "P2 previous_paid_amount is 2000.00", $failures, $successCount);
    assertCondition((float)$p2['total_paid_after'] === 3500.00, "P2 total_paid_after is 3500.00", $failures, $successCount);
    assertCondition((float)$p2['remaining_after'] === 1500.00, "P2 remaining_after is 1500.00", $failures, $successCount);

    $bankBalAfter2 = (float)$accModel->find($bankAccId)['current_balance'];
    assertCondition(round($bankBalAfter2, 2) === round($initialBankBal - 1500.00, 2), "Bank account balance debited by ₹1500", $failures, $successCount);

    $dspAfter2 = $dispatchModel->find($dispatchId);
    assertCondition((float)$dspAfter2['driver_paid_amount'] === 3500.00, "Dispatch driver_paid_amount is 3500.00", $failures, $successCount);
    assertCondition($dspAfter2['driver_payment_status'] === 'partially_paid', "Dispatch payment_status remains 'partially_paid'", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 6. Test Overpayment Rejection (Remaining: 1500, Attempting: 2000)
    // -------------------------------------------------------------------------
    echo "\n6. Testing Overpayment Protection (Remaining: ₹1500, Attempting: ₹2000)...\n";
    $overpayRejected = false;
    try {
        $paymentModel->recordPayment([
            'dispatch_id'       => $dispatchId,
            'driver_id'         => $driver1Id,
            'amount'            => 2000.00,
            'payment_date'      => date('Y-m-d'),
            'payment_method'    => 'cash',
            'account_id'        => $cashAccId,
            'created_by'        => $adminUserId,
        ]);
    } catch (\Throwable $e) {
        $overpayRejected = true;
        echo "   (Caught expected exception: " . $e->getMessage() . ")\n";
    }
    assertCondition($overpayRejected, "Overpayment (₹2000 > ₹1500 remaining) successfully rejected with Exception", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 7. Test Zero and Negative Payment Rejection
    // -------------------------------------------------------------------------
    echo "\n7. Testing Zero and Negative Amount Rejection...\n";
    $zeroRejected = false;
    try {
        $paymentModel->recordPayment([
            'dispatch_id'       => $dispatchId,
            'driver_id'         => $driver1Id,
            'amount'            => 0.00,
            'payment_date'      => date('Y-m-d'),
            'payment_method'    => 'cash',
            'account_id'        => $cashAccId,
            'created_by'        => $adminUserId,
        ]);
    } catch (\Throwable $e) {
        $zeroRejected = true;
    }
    assertCondition($zeroRejected, "Zero amount payment rejected", $failures, $successCount);

    $negRejected = false;
    try {
        $paymentModel->recordPayment([
            'dispatch_id'       => $dispatchId,
            'driver_id'         => $driver1Id,
            'amount'            => -500.00,
            'payment_date'      => date('Y-m-d'),
            'payment_method'    => 'cash',
            'account_id'        => $cashAccId,
            'created_by'        => $adminUserId,
        ]);
    } catch (\Throwable $e) {
        $negRejected = true;
    }
    assertCondition($negRejected, "Negative amount payment rejected", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 8. Test Final Payment (₹1500) via UPI & Transition to 'paid'
    // -------------------------------------------------------------------------
    echo "\n8. Recording Final Payment via UPI (₹1500 to complete trip)...\n";
    $initialUpiBal = (float)$accModel->find($upiAccId)['current_balance'];

    $p3 = $paymentModel->recordPayment([
        'dispatch_id'       => $dispatchId,
        'driver_id'         => $driver1Id,
        'amount'            => 1500.00,
        'payment_date'      => date('Y-m-d'),
        'payment_method'    => 'upi',
        'account_id'        => $upiAccId,
        'created_by'        => $adminUserId,
        'notes'             => 'Final settlement via GPay',
        'payment_reference' => 'UPI-REF-9988'
    ]);
    $cleanPayments[] = (int)$p3['id'];

    assertCondition((float)$p3['total_paid_after'] === 5000.00, "P3 total_paid_after is exactly 5000.00", $failures, $successCount);
    assertCondition((float)$p3['remaining_after'] === 0.00, "P3 remaining_after is 0.00", $failures, $successCount);

    $upiBalAfter3 = (float)$accModel->find($upiAccId)['current_balance'];
    assertCondition(round($upiBalAfter3, 2) === round($initialUpiBal - 1500.00, 2), "UPI account balance debited by ₹1500", $failures, $successCount);

    $dspAfter3 = $dispatchModel->find($dispatchId);
    assertCondition((float)$dspAfter3['driver_paid_amount'] === 5000.00, "Dispatch driver_paid_amount reaches 5000.00", $failures, $successCount);
    assertCondition($dspAfter3['driver_payment_status'] === 'paid', "Dispatch payment_status transitioned to 'paid'", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 9. Reversal Process: Reason Requirement & Fund Restoration
    // -------------------------------------------------------------------------
    echo "\n9. Testing Payment Reversal Flow on Payment #3 (₹1500 UPI)...\n";

    // 9.1 Require reason
    $noReasonRejected = false;
    try {
        $paymentModel->reversePayment((int)$p3['id'], '   ', $adminUserId);
    } catch (\Throwable $e) {
        $noReasonRejected = true;
    }
    assertCondition($noReasonRejected, "Reversal without valid reason rejected", $failures, $successCount);

    // 9.2 Execute valid reversal
    $reversalReason = 'Accidental duplicate entry - driver paid cash instead';
    $revSuccess = $paymentModel->reversePayment((int)$p3['id'], $reversalReason, $adminUserId);
    assertCondition(!empty($revSuccess['success']), "Reversal completed successfully", $failures, $successCount);

    // 9.3 Verify payment record status & audit fields
    $revPayment = $paymentModel->find((int)$p3['id']);
    assertCondition($revPayment['status'] === 'reversed', "Payment status changed to 'reversed'", $failures, $successCount);
    assertCondition((int)$revPayment['reversed_by'] === $adminUserId, "reversed_by logged as admin user ID", $failures, $successCount);
    assertCondition(!empty($revPayment['reversed_at']), "reversed_at timestamp populated", $failures, $successCount);
    assertCondition($revPayment['reversal_reason'] === $reversalReason, "reversal_reason recorded correctly", $failures, $successCount);

    // 9.4 Verify UPI account balance restored
    $upiBalAfterRev = (float)$accModel->find($upiAccId)['current_balance'];
    assertCondition(round($upiBalAfterRev, 2) === round($initialUpiBal, 2), "UPI account balance restored back to ₹{$initialUpiBal}", $failures, $successCount);

    // 9.5 Verify dispatch status & paid amount reverted
    $dspAfterRev = $dispatchModel->find($dispatchId);
    assertCondition((float)$dspAfterRev['driver_paid_amount'] === 3500.00, "Dispatch driver_paid_amount reverted from 5000.00 back to 3500.00", $failures, $successCount);
    assertCondition($dspAfterRev['driver_payment_status'] === 'partially_paid', "Dispatch payment_status reverted from 'paid' back to 'partially_paid'", $failures, $successCount);

    // 9.6 Cannot reverse already reversed payment
    $doubleRevRejected = false;
    try {
        $paymentModel->reversePayment((int)$p3['id'], 'Try reverse again', $adminUserId);
    } catch (\Throwable $e) {
        $doubleRevRejected = true;
    }
    assertCondition($doubleRevRejected, "Cannot reverse an already reversed payment", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 10. Historical Data Immutability on Earlier Receipts
    // -------------------------------------------------------------------------
    echo "\n10. Testing Immutability of Historical Snapshots on Receipt #1...\n";
    $receipt1Details = $paymentModel->findWithFullReceiptDetails((int)$p1['id']);
    assertCondition(!empty($receipt1Details), "Loaded full receipt details for Receipt #1", $failures, $successCount);
    assertCondition((float)$receipt1Details['trip_amount_snapshot'] === 5000.00, "Receipt #1 snapshot trip amount remains 5000.00", $failures, $successCount);
    assertCondition((float)$receipt1Details['previous_paid_amount'] === 0.00, "Receipt #1 previous paid remains 0.00", $failures, $successCount);
    assertCondition((float)$receipt1Details['total_paid_after'] === 2000.00, "Receipt #1 total paid after remains 2000.00", $failures, $successCount);
    assertCondition((float)$receipt1Details['remaining_after'] === 3000.00, "Receipt #1 remaining after remains 3000.00", $failures, $successCount);
    assertCondition(!empty($receipt1Details['driver_name']), "Driver name present: {$receipt1Details['driver_name']}", $failures, $successCount);
    assertCondition(!empty($receipt1Details['vehicle_number']), "Vehicle number present: {$receipt1Details['vehicle_number']}", $failures, $successCount);

    // Lookup by receipt number string
    $byCode = $paymentModel->findByReceiptNumber($receipt1Details['receipt_number']);
    assertCondition(!empty($byCode) && (int)$byCode['id'] === (int)$p1['id'], "findByReceiptNumber successfully retrieves receipt by string code", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 11. Office Receipt Views (HTML & Standalone Print A4)
    // -------------------------------------------------------------------------
    echo "\n11. Testing Office Receipt Layout & Print Rendering...\n";

    // Simulate Admin Session
    $_SESSION['user_id'] = $adminUserId;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Test Admin Payer';

    $financeController = new FinanceController();
    $req = new Request();

    // 11.1 Office View Receipt HTML
    $respView = $financeController->viewDriverPaymentReceipt($req, ['id' => (int)$p1['id']]);
    $viewHtml = $respView->getContent();

    assertCondition(strpos($viewHtml, $receipt1Details['receipt_number']) !== false, "Office view contains receipt number {$receipt1Details['receipt_number']}", $failures, $successCount);
    assertCondition(strpos($viewHtml, 'Rajesh Kumar Chauffeur') !== false, "Office view contains driver name", $failures, $successCount);
    assertCondition(strpos($viewHtml, '2,000') !== false, "Office view contains payment amount", $failures, $successCount);
    assertCondition(strpos($viewHtml, 'Still to Pay') !== false || strpos($viewHtml, 'Balance') !== false, "Office view contains balance summary", $failures, $successCount);

    // 11.2 Office Standalone Printable Receipt
    $respPrint = $financeController->printDriverPaymentReceipt($req, ['id' => (int)$p1['id']]);
    $printHtml = $respPrint->getContent();

    assertCondition(strpos($printHtml, '<!DOCTYPE html>') !== false, "Print receipt is standalone HTML document", $failures, $successCount);
    assertCondition(strpos($printHtml, '@page') !== false, "Print receipt contains CSS @page A4 directives", $failures, $successCount);
    assertCondition(stripos($printHtml, 'driver payment voucher') !== false, "Print receipt title is Driver Payment Voucher", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Customer Bill') === false && strpos($printHtml, 'Tax Invoice') === false, "Print receipt does NOT contain customer billing labels", $failures, $successCount);
    assertCondition(stripos($printHtml, '.print()') !== false || stripos($printHtml, 'triggerPrint') !== false, "Print receipt includes print trigger", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Payment Date:') !== false, "Print receipt displays Payment Date", $failures, $successCount);

    // Verify completely removed sections: NO signatures, NO recorded by, NO user names
    assertCondition(strpos($printHtml, 'Recorded By') === false, "Print receipt does NOT contain 'Recorded By'", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Authorized Signatory') === false, "Print receipt does NOT contain 'Authorized Signatory'", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Driver Signature') === false, "Print receipt does NOT contain 'Driver Signature'", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Driver Acknowledgement') === false, "Print receipt does NOT contain 'Driver Acknowledgement'", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Received with thanks') === false, "Print receipt does NOT contain 'Received with thanks'", $failures, $successCount);
    assertCondition(strpos($printHtml, 'signature-section') === false, "Print receipt does NOT contain 'signature-section' CSS block", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Test Admin Payer') === false, "Print receipt does NOT expose office user/admin name", $failures, $successCount);

    // Verify PAID stamp is displayed on completed payment
    assertCondition(strpos($printHtml, company_paid_stamp_url()) !== false, "Print receipt displays existing company PAID stamp", $failures, $successCount);
    assertCondition(stripos($printHtml, 'PARTIAL PAYMENT') !== false, "Partial payment displays 'Partial Payment' badge", $failures, $successCount);
    assertCondition(strpos($printHtml, 'Trip Fully Settled') === false, "Partial payment is NOT marked 'Trip Fully Settled'", $failures, $successCount);

    // 11.3 Standalone Printable Receipt for REVERSED voucher
    $respRevPrint = $financeController->printDriverPaymentReceipt($req, ['id' => (int)$p3['id']]);
    $revPrintHtml = $respRevPrint->getContent();

    assertCondition(strpos($revPrintHtml, 'REVERSED') !== false, "Reversed print receipt includes 'REVERSED' watermark / banner", $failures, $successCount);
    assertCondition(strpos($revPrintHtml, htmlspecialchars($reversalReason)) !== false || strpos($revPrintHtml, 'Accidental duplicate') !== false, "Reversed print receipt shows reversal reason", $failures, $successCount);
    assertCondition(strpos($revPrintHtml, company_paid_stamp_url()) === false, "Reversed print receipt does NOT display company PAID stamp", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 12. Driver Portal Receipt View & Ownership Security
    // -------------------------------------------------------------------------
    echo "\n12. Testing Driver Portal Receipt View & Ownership Security...\n";

    $driverController = new DriverPortalController();

    // 12.1 Authorized Driver (Rajesh Kumar, driver_id = $driver1Id, user_id = $driver1UserId)
    $_SESSION['user_id'] = $driver1UserId;
    $_SESSION['user_role'] = 'driver';
    $_SESSION['user_name'] = 'Rajesh Kumar Chauffeur';

    $respDrvView = $driverController->viewReceipt($req, ['id' => (int)$p1['id']]);
    $driverReceiptHtml = $respDrvView->getContent();

    assertCondition(strpos($driverReceiptHtml, $receipt1Details['receipt_number']) !== false, "Authorized driver can view own receipt", $failures, $successCount);
    assertCondition(strpos($driverReceiptHtml, 'Rajesh Kumar Chauffeur') !== false, "Driver receipt shows driver details", $failures, $successCount);

    // Driver print receipt
    $respDrvPrint = $driverController->printReceipt($req, ['id' => (int)$p1['id']]);
    $driverPrintHtml = $respDrvPrint->getContent();
    assertCondition(strpos($driverPrintHtml, $receipt1Details['receipt_number']) !== false, "Authorized driver can print own receipt", $failures, $successCount);

    // 12.2 Unauthorized Driver (Sunil Sharma, driver_id = $driver2Id, user_id = $driver2UserId)
    $_SESSION['user_id'] = $driver2UserId;
    $_SESSION['user_role'] = 'driver';
    $_SESSION['user_name'] = 'Sunil Sharma Driver';

    $unauthBlocked = false;
    try {
        $respUnauth = $driverController->viewReceipt($req, ['id' => (int)$p1['id']]);
        $unauthOutput = $respUnauth->getContent();
        if ($respUnauth->getStatusCode() === 302 || strpos($unauthOutput, $receipt1Details['receipt_number']) === false) {
            $unauthBlocked = true;
        }
    } catch (\Throwable $e) {
        $unauthBlocked = true;
    }
    assertCondition($unauthBlocked, "Driver 2 blocked from viewing Driver 1's payment receipt", $failures, $successCount);

    // Unauthorized print receipt check (returns HTTP 403)
    $respUnauthPrint = $driverController->printReceipt($req, ['id' => (int)$p1['id']]);
    assertCondition($respUnauthPrint->getStatusCode() === 403, "Driver 2 print attempt returns HTTP 403 Forbidden", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 13. AJAX APIs: Driver Search & Eligible Trips
    // -------------------------------------------------------------------------
    echo "\n13. Testing AJAX APIs for 4-Step Payment Wizard...\n";

    // 13.1 apiSearchDrivers
    $driversWithDues = $paymentModel->searchDriversWithDues('Rajesh');
    assertCondition(!empty($driversWithDues), "searchDriversWithDues finds driver with pending dues", $failures, $successCount);
    $foundRajesh = false;
    foreach ($driversWithDues as $d) {
        if ((int)$d['id'] === $driver1Id) {
            $foundRajesh = true;
            assertCondition((float)$d['total_due'] === 1500.00, "Driver total_due reflects accurate remaining balance (₹1500.00)", $failures, $successCount);
            break;
        }
    }
    assertCondition($foundRajesh, "Found Rajesh in searchDriversWithDues results", $failures, $successCount);

    // 13.2 getEligibleTripsForDriver
    $eligibleTrips = $paymentModel->getEligibleTripsForDriver($driver1Id);
    assertCondition(!empty($eligibleTrips), "getEligibleTripsForDriver returns eligible trip", $failures, $successCount);
    $tripMatch = false;
    foreach ($eligibleTrips as $t) {
        if ((int)$t['id'] === $dispatchId) {
            $tripMatch = true;
            assertCondition((float)$t['driver_trip_amount'] === 5000.00, "Eligible trip amount is 5000.00", $failures, $successCount);
            assertCondition((float)$t['driver_paid_amount'] === 3500.00, "Eligible trip paid amount is 3500.00", $failures, $successCount);
            assertCondition((float)$t['still_to_pay'] === 1500.00, "Eligible trip still_to_pay is 1500.00", $failures, $successCount);
            break;
        }
    }
    assertCondition($tripMatch, "Test dispatch is present in getEligibleTripsForDriver with correct still_to_pay", $failures, $successCount);

    // 13.3 apiDispatchVouchers (AJAX installments endpoint mirroring get-installments-ajax.php)
    echo "\n13.3 Testing apiDispatchVouchers AJAX Endpoint...\n";
    $reqVouchers = new Request(['dispatch_id' => $dispatchId]);
    $respVouchers = $financeController->apiDispatchVouchers($reqVouchers);
    assertCondition($respVouchers->getStatusCode() === 200, "apiDispatchVouchers returns HTTP 200", $failures, $successCount);
    $vouchersJson = json_decode($respVouchers->getContent(), true);
    assertCondition(!empty($vouchersJson['success']), "apiDispatchVouchers success is true", $failures, $successCount);
    assertCondition(!empty($vouchersJson['installments']) && count($vouchersJson['installments']) >= 2, "apiDispatchVouchers returns installments list", $failures, $successCount);
    assertCondition($vouchersJson['driver_name'] === 'Rajesh Kumar Chauffeur', "apiDispatchVouchers returns correct driver name", $failures, $successCount);

    // 13.4 publicVerifyReceipt (Digital Receipt Verification view mirroring receipt-view.php)
    echo "\n13.4 Testing Public Digital Receipt Verification View (QR Destination)...\n";
    $reqVerify = new Request();
    $respVerify = $financeController->publicVerifyReceipt($reqVerify, ['id' => (int)$p1['id']]);
    assertCondition($respVerify->getStatusCode() === 200, "publicVerifyReceipt returns HTTP 200", $failures, $successCount);
    $verifyHtml = $respVerify->getContent();
    assertCondition(strpos($verifyHtml, 'Digital Receipt Verified') !== false, "Public verify page contains verification header", $failures, $successCount);
    assertCondition(strpos($verifyHtml, $receipt1Details['receipt_number']) !== false, "Public verify page contains receipt number", $failures, $successCount);
    assertCondition(strpos($verifyHtml, 'Rajesh Kumar Chauffeur') !== false, "Public verify page contains driver name", $failures, $successCount);
    assertCondition(strpos($verifyHtml, 'All Vouchers for this Dispatch') !== false, "Public verify page displays voucher timeline", $failures, $successCount);

    // 13.5 Print Receipt PMS Elements (8 Theme Swatches, #receiptData, dynamic QR, buildReceipt)
    echo "\n13.5 Testing Print Receipt PMS Interactive Elements & QR...\n";
    assertCondition(strpos($printHtml, 'class="swatch"') !== false, "Print receipt contains 8 interactive color swatches", $failures, $successCount);
    assertCondition(strpos($printHtml, 'data-color="#10B981"') !== false, "Print receipt has Emerald as active theme swatch", $failures, $successCount);
    assertCondition(strpos($printHtml, 'id="receiptData"') !== false, "Print receipt contains #receiptData element", $failures, $successCount);
    assertCondition(strpos($printHtml, 'qrcode.min.js') !== false, "Print receipt includes qrcode.min.js", $failures, $successCount);
    assertCondition(strpos($printHtml, 'id="qrContainer"') !== false, "Print receipt contains #qrContainer for dynamic QR rendering", $failures, $successCount);
    assertCondition(strpos($printHtml, 'buildReceipt') !== false, "Print receipt contains buildReceipt dynamic compiler", $failures, $successCount);

    // 13.6 Stamp toggle parameter (?stamp=0 vs ?stamp=1)
    echo "\n13.6 Testing Stamp Toggle Parameter (&stamp=0)...\n";
    $_SESSION['user_id'] = $adminUserId;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Test Admin Payer';
    $reqNoStamp = new Request(['stamp' => '0']);
    $respNoStamp = $financeController->printDriverPaymentReceipt($reqNoStamp, ['id' => (int)$p1['id']]);
    $noStampHtml = $respNoStamp->getContent();
    assertCondition(strpos($noStampHtml, 'data-show-stamp="0"') !== false, "Print receipt with stamp=0 sets data-show-stamp to 0", $failures, $successCount);
    assertCondition(strpos($noStampHtml, 'data-stamp=""') !== false, "Print receipt with stamp=0 omits stamp URL", $failures, $successCount);

    // 13.7 Receipt Modal in driver_payments.php
    echo "\n13.7 Testing Installment Selection Modal (#receiptModal) in driver_payments.php...\n";
    $_SESSION['user_id'] = $adminUserId;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Test Admin Payer';
    $reqDp = new Request();
    $respDp = $financeController->driverPayments($reqDp);
    $dpHtml = $respDp->getContent();
    assertCondition(strpos($dpHtml, 'id="receiptModal"') !== false, "driver_payments contains #receiptModal dialog", $failures, $successCount);
    assertCondition(strpos($dpHtml, 'id="receiptInstallmentList"') !== false, "driver_payments contains #receiptInstallmentList container", $failures, $successCount);
    assertCondition(strpos($dpHtml, 'openReceiptModal') !== false, "driver_payments contains openReceiptModal JavaScript function", $failures, $successCount);
    assertCondition(strpos($dpHtml, 'printReceipt') !== false, "driver_payments contains printReceipt JavaScript function", $failures, $successCount);

    // -------------------------------------------------------------------------
    // 14. Atomic Rollback Verification on Exception
    // -------------------------------------------------------------------------
    echo "\n14. Testing Atomic Rollback on Failure...\n";
    $balBeforeFail = (float)$accModel->find($cashAccId)['current_balance'];
    $rollbackWorked = false;

    try {
        // Attempt payment with invalid account_id to trigger failure
        $paymentModel->recordPayment([
            'dispatch_id'       => $dispatchId,
            'driver_id'         => $driver1Id,
            'amount'            => 500.00,
            'payment_date'      => date('Y-m-d'),
            'payment_method'    => 'cash',
            'account_id'        => 999999999, // non-existent account
            'created_by'        => $adminUserId,
        ]);
    } catch (\Throwable $e) {
        $rollbackWorked = true;
    }

    $balAfterFail = (float)$accModel->find($cashAccId)['current_balance'];
    assertCondition($rollbackWorked, "Invalid transaction threw exception", $failures, $successCount);
    assertCondition(round($balBeforeFail, 2) === round($balAfterFail, 2), "Cash account balance unchanged after rolled-back transaction", $failures, $successCount);

} catch (\Throwable $ex) {
    $failures[] = "Fatal test failure: " . $ex->getMessage() . " at " . $ex->getFile() . ":" . $ex->getLine();
    echo "\n[EXCEPTION] " . $ex->getMessage() . "\n" . $ex->getTraceAsString() . "\n";
} finally {
    // -------------------------------------------------------------------------
    // 15. Teardown Test Fixtures
    // -------------------------------------------------------------------------
    echo "\n15. Cleaning up Test Fixtures...\n";
    try {
        // Remove ledger entries for test accounts
        if (!empty($cleanAccounts)) {
            $inAccs = implode(',', array_map('intval', $cleanAccounts));
            $pdo->exec("DELETE FROM finance_ledger WHERE account_id IN ($inAccs)");
        }
        if (!empty($cleanDispatches)) {
            $inDsp = implode(',', array_map('intval', $cleanDispatches));
            $pdo->exec("DELETE FROM driver_trip_payments WHERE dispatch_id IN ($inDsp)");
        }
        if (!empty($cleanPayments)) {
            $inPay = implode(',', array_map('intval', $cleanPayments));
            $pdo->exec("DELETE FROM driver_trip_payments WHERE id IN ($inPay)");
        }
        if (!empty($cleanDispatches)) {
            $inDsp = implode(',', array_map('intval', $cleanDispatches));
            $pdo->exec("DELETE FROM dispatches WHERE id IN ($inDsp)");
        }
        if (!empty($cleanBookings)) {
            $inBk = implode(',', array_map('intval', $cleanBookings));
            $pdo->exec("DELETE FROM bookings WHERE id IN ($inBk)");
        }
        if (!empty($cleanVehicles)) {
            $inVeh = implode(',', array_map('intval', $cleanVehicles));
            $pdo->exec("DELETE FROM vehicles WHERE id IN ($inVeh)");
        }
        if (!empty($cleanDrivers)) {
            $inDrv = implode(',', array_map('intval', $cleanDrivers));
            $pdo->exec("DELETE FROM driver_profiles WHERE id IN ($inDrv)");
        }
        if (!empty($cleanAccounts)) {
            $inAccs = implode(',', array_map('intval', $cleanAccounts));
            $pdo->exec("DELETE FROM finance_accounts WHERE id IN ($inAccs)");
        }
        if (!empty($cleanUsers)) {
            $inUsr = implode(',', array_map('intval', $cleanUsers));
            $pdo->exec("DELETE FROM users WHERE id IN ($inUsr)");
        }
        echo "   Teardown completed cleanly.\n";
    } catch (\Throwable $t) {
        echo "   Teardown warning: " . $t->getMessage() . "\n";
    }
}

// -----------------------------------------------------------------------------
// Final Summary
// -----------------------------------------------------------------------------
echo "\n====================================================================\n";
echo "SUMMARY: {$successCount} PASSED, " . count($failures) . " FAILED\n";
if (!empty($failures)) {
    echo "Failures:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    echo "====================================================================\n";
    exit(1);
} else {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    echo "====================================================================\n";
    exit(0);
}
