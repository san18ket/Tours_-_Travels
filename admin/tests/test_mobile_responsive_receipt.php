<?php
declare(strict_types=1);

/**
 * Test Suite: Mobile Responsive Devices Receipt View
 * Verifies mobile responsive design, breakpoints, and UI components
 * mirroring Project Management System (PMS receipt-view.php & print-receipt.php)
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\DriverTripPayment;
use App\Models\Dispatch;
use App\Models\FinanceAccount;
use App\Models\User;
use App\Controllers\FinanceController;
use App\Core\Request;

use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\Booking;

$pdo = Database::getConnection();
$failures = [];
$successCount = 0;

function assertCond(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "====================================================================\n";
echo "=== MOBILE RESPONSIVE RECEIPT VIEW TEST SUITE ===\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. Inspect PMS Reference Mobile Responsive Architecture
// -----------------------------------------------------------------------------
echo "1. Verifying Mobile Responsive Design Patterns in receipt_verify.php...\n";

$verifyContent = file_get_contents(__DIR__ . '/../app/Views/office/finance/receipt_verify.php');

// 1.1 Viewport Meta Tag
assertCond(strpos($verifyContent, 'name="viewport"') !== false, "Receipt view includes viewport meta tag for mobile devices", $failures, $successCount);
assertCond(strpos($verifyContent, 'width=device-width, initial-scale=1.0') !== false, "Viewport configured with width=device-width, initial-scale=1.0", $failures, $successCount);

// 1.2 Hero Header & Amount Responsiveness
assertCond(strpos($verifyContent, '.hero') !== false, "Contains .hero gradient band", $failures, $successCount);
assertCond(strpos($verifyContent, '.hero-amount') !== false, "Contains .hero-amount typography block", $failures, $successCount);
assertCond(strpos($verifyContent, '.hero-top-row') !== false, "Contains .hero-top-row badge container", $failures, $successCount);
assertCond(strpos($verifyContent, '.hero-verified') !== false, "Contains .hero-verified status pill", $failures, $successCount);
assertCond(strpos($verifyContent, '.hero-status-dot') !== false, "Contains .hero-status-dot animated indicator", $failures, $successCount);

// 1.3 Mobile Card & Summary Grid
assertCond(strpos($verifyContent, '.card-container') !== false, "Contains .card-container wrapper with negative top margin", $failures, $successCount);
assertCond(strpos($verifyContent, '.stat-grid') !== false, "Contains .stat-grid for 4 financial summary cards", $failures, $successCount);
assertCond(strpos($verifyContent, '.progress-bar-bg') !== false, "Contains animated .progress-bar-bg element", $failures, $successCount);
assertCond(strpos($verifyContent, '.info-row') !== false, "Contains .info-row mobile-friendly metadata items", $failures, $successCount);
assertCond(strpos($verifyContent, '.total-box') !== false, "Contains .total-box highlight container", $failures, $successCount);
assertCond(strpos($verifyContent, '.timeline') !== false, "Contains vertical connected .timeline component", $failures, $successCount);
assertCond(strpos($verifyContent, '.mobile-action-bar') !== false, "Contains .mobile-action-bar sticky bottom controls", $failures, $successCount);

// 1.4 Breakpoint Rules in receipt_verify.php
assertCond(strpos($verifyContent, '@media (min-width: 1024px)') !== false, "Contains @media (min-width: 1024px) for desktop split view", $failures, $successCount);
assertCond(strpos($verifyContent, '@media (max-width: 420px)') !== false, "Contains @media (max-width: 420px) for compact mobile phones", $failures, $successCount);

// -----------------------------------------------------------------------------
// 2. Inspect Mobile Responsiveness in driver_payment_receipt_print.php
// -----------------------------------------------------------------------------
echo "\n2. Verifying Mobile Responsiveness in driver_payment_receipt_print.php...\n";

$printContent = file_get_contents(__DIR__ . '/../app/Views/office/finance/driver_payment_receipt_print.php');

assertCond(strpos($printContent, '@media screen and (max-width: 768px)') !== false, "Contains @media screen and (max-width: 768px) mobile responsive breakpoint", $failures, $successCount);
assertCond(strpos($printContent, '@media screen and (max-width: 520px)') !== false, "Contains @media screen and (max-width: 520px) extra small breakpoint", $failures, $successCount);
assertCond(strpos($printContent, '.toolbar-main') !== false, "Toolbar includes .toolbar-main responsive container", $failures, $successCount);
assertCond(strpos($printContent, '.toolbar-meta') !== false, "Toolbar includes .toolbar-meta text wrapper", $failures, $successCount);
assertCond(strpos($printContent, '.swatches-track') !== false, "Toolbar includes .swatches-track horizontal scroll track", $failures, $successCount);
assertCond(strpos($printContent, 'tbtn-text') !== false, "Toolbar buttons include .tbtn-text responsive labels", $failures, $successCount);
assertCond(strpos($printContent, '.rc-header-wrap') !== false, "Header container tagged with .rc-header-wrap class", $failures, $successCount);
assertCond(strpos($printContent, '.rc-details-wrap') !== false, "Details container tagged with .rc-details-wrap class", $failures, $successCount);
assertCond(strpos($printContent, '.rc-summary-wrap') !== false, "Summary container tagged with .rc-summary-wrap class", $failures, $successCount);
assertCond(strpos($printContent, '.rc-footer-wrap') !== false, "Footer container tagged with .rc-footer-wrap class", $failures, $successCount);
assertCond(strpos($printContent, '.tbtn-digital') !== false, "Includes .tbtn-digital button to view mobile digital receipt", $failures, $successCount);
assertCond(strpos($printContent, '@media print') !== false, "Contains @media print to preserve A4 landscape layout when printing", $failures, $successCount);

// -----------------------------------------------------------------------------
// 3. Functional Execution & Output Verification
// -----------------------------------------------------------------------------
echo "\n3. Testing Controller Endpoints with Live Voucher Fixture...\n";

$cleanAccounts = [];
$cleanUsers = [];
$cleanDrivers = [];
$cleanVehicles = [];
$cleanBookings = [];
$cleanDispatches = [];
$cleanPayments = [];

try {
    // Create test account
    $accModel = new FinanceAccount();
    $accId = (int)$accModel->create([
        'account_name'   => 'Test Mobile Vault',
        'account_number' => 'VAULT-' . rand(1000, 9999),
        'account_type'   => 'cash',
        'current_balance'=> 25000.00,
        'opening_balance'=> 25000.00,
        'status'         => 'active',
    ]);
    $cleanAccounts[] = $accId;

    // Create admin user
    $userModel = new User();
    $adminId = (int)$userModel->create([
        'name'     => 'Test Admin Mobile',
        'username' => 'admin_mob_' . uniqid(),
        'email'    => 'admin_mob_' . rand(1000, 9999) . '@test.local',
        'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
        'role'     => 'admin',
        'status'   => 'active',
    ]);
    $cleanUsers[] = $adminId;

    // Create driver user & profile
    $driverUserId = (int)$userModel->create([
        'name'     => 'Vikram Singh Chauffeur',
        'username' => 'driver_vikram_' . uniqid(),
        'email'    => 'vikram_' . rand(1000, 9999) . '@test.local',
        'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
        'role'     => 'driver',
        'status'   => 'active',
    ]);
    $cleanUsers[] = $driverUserId;

    $driverModel = new DriverProfile();
    $driverId = (int)$driverModel->create([
        'user_id'        => $driverUserId,
        'license_number' => 'DL-MOB-' . rand(1000, 9999),
        'duty_status'    => 'available',
    ]);
    $cleanDrivers[] = $driverId;

    // Create vehicle
    $vehicleModel = new Vehicle();
    $vehicleId = (int)$vehicleModel->create([
        'vehicle_number'   => 'MH-12-MB-' . rand(1000, 9999),
        'make_model'       => 'Toyota Fortuner GR',
        'vehicle_type'     => 'suv',
        'seating_capacity' => 7,
        'fuel_type'        => 'diesel',
        'status'           => 'available',
        'odometer_km'      => 32000,
    ]);
    $cleanVehicles[] = $vehicleId;

    // Create booking
    $bookingModel = new Booking();
    $bookingId = (int)$bookingModel->create([
        'booking_code'    => 'BK-MOB-' . rand(1000, 9999),
        'pickup_address'  => 'Pune Shivaji Nagar',
        'drop_address'    => 'Mumbai Marine Drive',
        'start_date'      => date('Y-m-d'),
        'end_date'        => date('Y-m-d', strtotime('+1 day')),
        'pickup_time'     => '09:00:00',
        'passenger_count' => 3,
        'subtotal'        => 12000.00,
        'tax_amount'      => 600.00,
        'discount_amount' => 0.00,
        'total_amount'    => 12600.00,
        'status'          => 'completed',
    ]);
    $cleanBookings[] = $bookingId;

    // Create completed dispatch
    $dispatchModel = new Dispatch();
    $dispatchId = (int)$dispatchModel->create([
        'dispatch_code'        => 'DSP-MOB-' . rand(1000, 9999),
        'booking_id'           => $bookingId,
        'vehicle_id'           => $vehicleId,
        'driver_id'            => $driverId,
        'assigned_by'          => $adminId,
        'driver_trip_amount'   => 8000.00,
        'driver_paid_amount'   => 0.00,
        'driver_payment_status'=> 'due',
        'status'               => 'completed',
    ]);
    $cleanDispatches[] = $dispatchId;

    // Record voucher
    $paymentModel = new DriverTripPayment();
    $v1 = $paymentModel->recordPayment([
        'dispatch_id'    => $dispatchId,
        'driver_id'      => $driverId,
        'amount'         => 3500.00,
        'payment_date'   => date('Y-m-d'),
        'payment_method' => 'upi',
        'account_id'     => $accId,
        'created_by'     => $adminId,
        'notes'          => 'Mobile responsiveness audit voucher',
    ]);
    $cleanPayments[] = (int)$v1['id'];

    $_SESSION['user_id'] = $adminId;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Test Admin Mobile';

    $financeController = new FinanceController();

    // 3.1 Test publicVerifyReceipt
    $reqVerify = new Request();
    $respVerify = $financeController->publicVerifyReceipt($reqVerify, ['id' => (int)$v1['id']]);
    assertCond($respVerify->getStatusCode() === 200, "publicVerifyReceipt returns HTTP 200", $failures, $successCount);
    $htmlVerify = $respVerify->getContent();

    assertCond(strpos($htmlVerify, '3,500.00') !== false, "Digital receipt displays ₹3,500.00 payment amount", $failures, $successCount);
    assertCond(strpos($htmlVerify, '8,000.00') !== false, "Digital receipt displays ₹8,000.00 agreed fee", $failures, $successCount);
    assertCond(strpos($htmlVerify, '4,500.00') !== false, "Digital receipt displays ₹4,500.00 remaining balance", $failures, $successCount);
    assertCond(strpos($htmlVerify, 'Three Thousand Five Hundred') !== false, "Digital receipt includes amount in words (Indian currency)", $failures, $successCount);
    assertCond(strpos($htmlVerify, 'mobile-action-bar') !== false, "Digital receipt renders mobile sticky action bar", $failures, $successCount);

    // 3.2 Test printDriverPaymentReceipt
    $reqPrint = new Request(['stamp' => '1']);
    $respPrint = $financeController->printDriverPaymentReceipt($reqPrint, ['id' => (int)$v1['id']]);
    assertCond($respPrint->getStatusCode() === 200, "printDriverPaymentReceipt returns HTTP 200", $failures, $successCount);
    $htmlPrint = $respPrint->getContent();

    assertCond(strpos($htmlPrint, 'rc-header-wrap') !== false, "Print receipt output contains .rc-header-wrap", $failures, $successCount);
    assertCond(strpos($htmlPrint, 'rc-summary-wrap') !== false, "Print receipt output contains .rc-summary-wrap", $failures, $successCount);
    assertCond(strpos($htmlPrint, 'Digital View') !== false, "Print receipt output contains 'Digital View' button", $failures, $successCount);

} catch (\Throwable $e) {
    $failures[] = "Exception: " . $e->getMessage();
    echo "\n[EXCEPTION] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    echo "\nCleaning up test fixtures...\n";
    if (!empty($cleanAccounts)) {
        $in = implode(',', array_map('intval', $cleanAccounts));
        $pdo->exec("DELETE FROM finance_ledger WHERE account_id IN ($in)");
        $pdo->exec("DELETE FROM finance_accounts WHERE id IN ($in)");
    }
    if (!empty($cleanPayments)) {
        $in = implode(',', array_map('intval', $cleanPayments));
        $pdo->exec("DELETE FROM driver_trip_payments WHERE id IN ($in)");
    }
    if (!empty($cleanDispatches)) {
        $in = implode(',', array_map('intval', $cleanDispatches));
        $pdo->exec("DELETE FROM dispatches WHERE id IN ($in)");
    }
    if (!empty($cleanBookings)) {
        $in = implode(',', array_map('intval', $cleanBookings));
        $pdo->exec("DELETE FROM bookings WHERE id IN ($in)");
    }
    if (!empty($cleanUsers)) {
        $in = implode(',', array_map('intval', $cleanUsers));
        $pdo->exec("DELETE FROM users WHERE id IN ($in)");
    }
    echo "Cleanup finished.\n";
}

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
    echo "ALL MOBILE RESPONSIVE TESTS PASSED SUCCESSFULLY!\n";
    echo "====================================================================\n";
    exit(0);
}
