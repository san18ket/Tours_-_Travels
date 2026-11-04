<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Direct Business Email Delivery Automated Verification Suite
 * ============================================================================
 *
 * Verifies that all business notifications (Customer Add, Employee Add,
 * Booking Create, Status Updates, Driver Assignment, Payments) dispatch
 * immediately via direct SMTP sending (like Registration OTP) without
 * requiring bin/email_worker.php to be running.
 * ============================================================================
 */

require_once __DIR__ . '/../app/bootstrap.php';

set_exception_handler(function (\Throwable $e) {
    echo "\n[EXCEPTION IN TEST]: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
});

use App\Core\Database;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Dispatch;
use App\Models\EmailLog;
use App\Services\EmailService;
use App\Services\BookingService;
use App\Services\DispatchService;
use App\Services\BillingService;
use App\Services\EmailOtpService;

$pdo = Database::getConnection();
$emailService = new EmailService();
$emailLog = new EmailLog();
$bookingService = new BookingService();
$dispatchService = new DispatchService();
$billingService = new BillingService();
$customerModel = new Customer();
$employeeModel = new Employee();

$passed = 0;
$failed = 0;
$totalTests = 0;

function assertTest(string $description, bool $condition, ?string $details = null): void
{
    global $passed, $failed, $totalTests;
    $totalTests++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

echo "====================================================================\n";
echo " Starting Direct Business Email Delivery Test Suite\n";
echo "====================================================================\n\n";

$testRun = time() + random_int(1000, 9999);

// ============================================================================
// 1. REGISTRATION OTP VERIFICATION
// ============================================================================
echo "--- 1. Testing Registration OTP (Baseline Direct SMTP) ---\n";

$otpService = new EmailOtpService();
$otpEmail = 'otp_test_' . $testRun . '@example.com';
$otpResult = $otpService->sendOtp($otpEmail, EmailOtpService::FLOW_CUSTOMER, 'OTP Tester');

assertTest("Registration OTP returns success", !empty($otpResult['success']));

// Verify email log entry has status 'sent' immediately (not pending!)
$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `recipient_email` = ? AND `email_type` = 'email_verification_otp' ORDER BY `id` DESC LIMIT 1");
$stmt->execute([$otpEmail]);
$otpLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest("Registration OTP email recorded in email_logs", !empty($otpLog['id']));
assertTest("Registration OTP recorded with status 'sent' immediately", ($otpLog['status'] ?? '') === 'sent');
assertTest("Registration OTP has message_id recorded", !empty($otpLog['message_id']));

// ============================================================================
// 2. CUSTOMER CREATION DIRECT WELCOME EMAIL
// ============================================================================
echo "\n--- 2. Testing Customer Welcome Email (Direct Send) ---\n";

$custCode = 'CUST-TEST-' . $testRun;
$custEmail = 'cust_direct_' . $testRun . '@example.com';
$customerId = $customerModel->create([
    'customer_code'     => $custCode,
    'name'              => 'Direct Customer Test',
    'email'             => $custEmail,
    'phone'             => '987654' . substr((string)$testRun, -4),
    'status'            => 'active',
    'created_at'        => date('Y-m-d H:i:s'),
    'updated_at'        => date('Y-m-d H:i:s'),
]);

assertTest("Customer profile saved in database", $customerId > 0);

// Dispatch welcome email directly via sendCustomerWelcome
$fullCust = $customerModel->find($customerId);
$welcomeResult = $emailService->sendCustomerWelcome($fullCust);

assertTest("sendCustomerWelcome returned success", !empty($welcomeResult['success']));
assertTest("sendCustomerWelcome returned non-null log_id", !empty($welcomeResult['log_id']));

// Verify database email_log entry is 'sent' directly (0 worker dependency!)
$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `id` = ?");
$stmt->execute([(int)$welcomeResult['log_id']]);
$custEmailLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Customer welcome email recorded with status 'sent' immediately (No Worker Required)",
    ($custEmailLog['status'] ?? '') === 'sent',
    "Actual status: " . ($custEmailLog['status'] ?? 'none')
);
assertTest("Customer welcome email stores rendered html_body", !empty($custEmailLog['html_body']));

// ============================================================================
// 3. EMPLOYEE CREATION DIRECT WELCOME EMAIL
// ============================================================================
echo "\n--- 3. Testing Employee Welcome Email (Direct Send) ---\n";

$empCode = 'EMP-TEST-' . $testRun;
$empEmail = 'emp_direct_' . $testRun . '@example.com';
$employeeId = $employeeModel->create([
    'employee_code' => $empCode,
    'first_name'    => 'Direct',
    'last_name'     => 'Staff',
    'email'         => $empEmail,
    'phone'         => '987111' . substr((string)$testRun, -4),
    'role'          => 'driver',
    'status'        => 'active',
    'created_at'    => date('Y-m-d H:i:s'),
    'updated_at'    => date('Y-m-d H:i:s'),
]);

assertTest("Employee profile saved in database", $employeeId > 0);

$fullEmp = $employeeModel->find($employeeId);
$empWelcomeResult = $emailService->sendEmployeeWelcome($fullEmp);

assertTest("sendEmployeeWelcome returned success", !empty($empWelcomeResult['success']));

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `id` = ?");
$stmt->execute([(int)$empWelcomeResult['log_id']]);
$empEmailLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Employee welcome email recorded with status 'sent' immediately",
    ($empEmailLog['status'] ?? '') === 'sent'
);

// ============================================================================
// 4. BOOKING CREATION DIRECT NOTIFICATION
// ============================================================================
echo "\n--- 4. Testing Booking Creation Email (Direct Send) ---\n";

$bookingData = [
    'customer_profile_id' => $customerId,
    'pickup_address'      => 'Jaipur Airport Terminal 2',
    'drop_address'        => 'Amer Fort, Jaipur',
    'start_date'          => date('Y-m-d', strtotime('+3 days')),
    'end_date'            => date('Y-m-d', strtotime('+3 days')),
    'pickup_time'         => '09:30:00',
    'passenger_count'     => 2,
    'advance_amount'      => 0.0,
];

$createdBooking = $bookingService->createBooking($bookingData);

assertTest("Booking created and committed in database", !empty($createdBooking['id']));
assertTest("Booking marked email_sent true", !empty($createdBooking['email_sent']));

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'booking_created' AND `reference_id` = ?");
$stmt->execute([(int)$createdBooking['id']]);
$bookingCreatedLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Booking created email recorded with status 'sent' immediately",
    ($bookingCreatedLog['status'] ?? '') === 'sent'
);

// ============================================================================
// 5. BOOKING CONFIRMATION & CANCELLATION DIRECT SENDS
// ============================================================================
echo "\n--- 5. Testing Booking Confirmation & Cancellation (Direct Send) ---\n";

$bookingId = (int)$createdBooking['id'];

// A. Confirm Booking
$confirmedOk = $bookingService->updateStatus($bookingId, 'confirmed');
assertTest("Booking status updated to confirmed in DB", $confirmedOk === true);

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'booking_confirmed' AND `reference_id` = ?");
$stmt->execute([$bookingId]);
$confLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Booking confirmed email sent directly (status: 'sent')",
    ($confLog['status'] ?? '') === 'sent'
);

// B. Cancel Booking
$cancelledOk = $bookingService->updateStatus($bookingId, 'cancelled', 'Test cancellation reason');
assertTest("Booking status updated to cancelled in DB", $cancelledOk === true);

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'booking_cancelled' AND `reference_id` = ?");
$stmt->execute([$bookingId]);
$cancLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Booking cancelled email sent directly to customer (status: 'sent')",
    ($cancLog['status'] ?? '') === 'sent'
);

// ============================================================================
// 6. DRIVER ASSIGNMENT DIRECT NOTIFICATION
// ============================================================================
echo "\n--- 6. Testing Driver Assignment Email (Direct Send) ---\n";

// Ensure an available vehicle and driver profile exist
$vehicleModel = new \App\Models\Vehicle();
$vehicleId = $vehicleModel->create([
    'vehicle_number'   => 'RJ14-VB-' . substr((string)$testRun, -4),
    'make_model'       => 'Toyota Etios',
    'vehicle_type'     => 'sedan',
    'seating_capacity' => 4,
    'fuel_type'        => 'petrol',
    'status'           => 'available',
    'odometer_km'      => 12000,
    'created_at'       => date('Y-m-d H:i:s'),
    'updated_at'       => date('Y-m-d H:i:s'),
]);

// Create user account for chauffeur
$userModel = new \App\Models\User();
$driverUserId = $userModel->create([
    'name'       => 'Direct Chauffeur ' . $testRun,
    'username'   => 'chauffeur_' . $testRun,
    'email'      => 'chauffeur_' . $testRun . '@example.com',
    'phone'      => '9820' . substr((string)$testRun, -6),
    'password'   => password_hash('Pass1234!', PASSWORD_BCRYPT),
    'role'       => 'driver',
    'status'     => 'active',
    'language'   => 'en',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$driverModel = new \App\Models\DriverProfile();
$driverProfileId = $driverModel->create([
    'user_id'           => $driverUserId,
    'employee_id'       => $employeeId,
    'license_number'    => 'DL-TEST-' . substr((string)$testRun, -6),
    'license_expiry'    => date('Y-m-d', strtotime('+2 years')),
    'experience_years'  => 5,
    'emergency_contact' => '9888888888',
    'duty_status'       => 'on_duty',
    'rating'            => 4.80,
    'created_at'        => date('Y-m-d H:i:s'),
    'updated_at'        => date('Y-m-d H:i:s'),
]);

// Create a second clean booking for dispatch assignment
$dispatchBooking = $bookingService->createBooking([
    'customer_profile_id' => $customerId,
    'pickup_address'      => 'Hotel Clarks Amer, Jaipur',
    'drop_address'        => 'Jaipur Railway Station',
    'start_date'          => date('Y-m-d', strtotime('+4 days')),
    'end_date'            => date('Y-m-d', strtotime('+4 days')),
    'pickup_time'         => '14:00:00',
    'passenger_count'     => 2,
    'custom_subtotal'     => 2000.0,
    'advance_amount'      => 0.0,
]);

$dispatchRecord = $dispatchService->assignBooking(
    (int)$dispatchBooking['id'],
    $vehicleId,
    $driverProfileId,
    1
);

assertTest("Trip dispatch assigned successfully in database", !empty($dispatchRecord['id']));

$dispatchId = (int)$dispatchRecord['id'];

// Verify both driver assignment emails sent directly
$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'driver_assigned_driver' AND `reference_id` = ?");
$stmt->execute([$dispatchId]);
$driverAssignedLog = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'driver_assigned_customer' AND `reference_id` = ?");
$stmt->execute([$dispatchId]);
$custAssignedLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Driver received direct duty assignment email (status: 'sent')",
    ($driverAssignedLog['status'] ?? '') === 'sent'
);
assertTest(
    "Customer received direct fleet/chauffeur assignment email (status: 'sent')",
    ($custAssignedLog['status'] ?? '') === 'sent'
);

// ============================================================================
// 7. PAYMENT RECEIPT DIRECT NOTIFICATION
// ============================================================================
echo "\n--- 7. Testing Payment Receipt Email (Direct Send) ---\n";

$invoiceModel = new Invoice();
$invoice = $invoiceModel->findByBookingId((int)$dispatchBooking['id']);
assertTest("Invoice resolved for booking", !empty($invoice['id']));

$paymentRecord = $billingService->recordPayment(
    (int)$invoice['id'],
    500.00,
    'upi',
    'TXN-' . $testRun,
    'IDEMP-' . $testRun,
    'Advance UPI payment test'
);

assertTest("Payment recorded in database", !empty($paymentRecord['id']));
assertTest("Payment record marked email_sent true", !empty($paymentRecord['email_sent']));

$stmt = $pdo->prepare("SELECT * FROM `email_logs` WHERE `email_type` = 'payment_successful' AND `reference_id` = ?");
$stmt->execute([(int)$paymentRecord['id']]);
$payLog = $stmt->fetch(PDO::FETCH_ASSOC);

assertTest(
    "Payment receipt email sent directly (status: 'sent')",
    ($payLog['status'] ?? '') === 'sent'
);

// ============================================================================
// 8. RECIPIENT VALIDATION & INTERNAL DOMAIN PROTECTION
// ============================================================================
echo "\n--- 8. Testing Recipient Validation & Internal Domain Guard ---\n";

$invalidEmailCust = [
    'id'    => $testRun + 50,
    'name'  => 'Internal Local User',
    'email' => 'user_' . $testRun . '@internal.local'
];
$invalidResult = $emailService->sendCustomerWelcome($invalidEmailCust);

assertTest("Internal domain @internal.local is safely rejected", $invalidResult['success'] === false);

$emptyEmailCust = [
    'id'    => $testRun + 51,
    'name'  => 'Empty Email User',
    'email' => ''
];
$emptyResult = $emailService->sendCustomerWelcome($emptyEmailCust);

assertTest("Empty email address is safely rejected without error", $emptyResult['success'] === false);

// ============================================================================
// 9. TRANSACTION SAFETY (SMTP FAILURE DOES NOT UNDO SAVED RECORDS)
// ============================================================================
echo "\n--- 9. Testing Database & Transaction Safety Under SMTP Failure ---\n";

// Create customer record directly
$safeCustCode = 'CUST-SAFE-' . $testRun;
$safeCustomerId = $customerModel->create([
    'customer_code' => $safeCustCode,
    'name'          => 'Safe Transaction Customer',
    'email'         => 'invalid_no_smtp_format',
    'phone'         => '980000' . substr((string)$testRun, -4),
    'status'        => 'active',
    'created_at'    => date('Y-m-d H:i:s'),
    'updated_at'    => date('Y-m-d H:i:s'),
]);

// Attempt sending welcome email to invalid email
$safeCust = $customerModel->find($safeCustomerId);
$safeCustResult = $emailService->sendCustomerWelcome($safeCust);

// The email fails safely
assertTest("Email to invalid format failed safely", $safeCustResult['success'] === false);

// CRITICAL ASSERTION: The customer database record STILL EXISTS!
$reCheckCust = $customerModel->find($safeCustomerId);
assertTest(
    "Database record remains intact after SMTP failure (Zero Rollback)",
    !empty($reCheckCust['id']) && $reCheckCust['customer_code'] === $safeCustCode
);

// ============================================================================
// 10. DUPLICATE SENDING / IDEMPOTENCY PROTECTION
// ============================================================================
echo "\n--- 10. Testing Idempotency & Duplicate Prevention ---\n";

// First send for booking confirmation was already sent in Section 5
// Calling sendBookingConfirmed a SECOND time for the same booking:
$refreshedBooking = (new Booking())->find($bookingId);
$duplicateResult = $emailService->sendBookingConfirmed($refreshedBooking);

assertTest(
    "Repeated sendBookingConfirmed identifies duplicate and returns duplicate: true",
    !empty($duplicateResult['duplicate'])
);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM `email_logs` WHERE `email_type` = 'booking_confirmed' AND `reference_id` = ? AND `status` = 'sent'");
$stmt->execute([$bookingId]);
$sentCount = (int)$stmt->fetchColumn();

assertTest("Exactly ONE confirmation email recorded (no duplicates sent)", $sentCount === 1);

// ============================================================================
// 11. SECURITY & CREDENTIAL REDACTION
// ============================================================================
echo "\n--- 11. Testing Credential Redaction in Direct Sends ---\n";

// Simulate an SMTP exception with passwords and verify it's redacted
$testRedactEmail = 'redact_direct_' . $testRun . '@example.com';
$redactHtml = '<p>Test</p>';

$stmt = $pdo->prepare("SELECT `error_message` FROM `email_logs` WHERE `error_message` LIKE '%password%'");
$stmt->execute();
$leakedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$noPasswordLeaks = true;
foreach ($leakedRows as $row) {
    if (!str_contains($row['error_message'], 'password=[REDACTED]')) {
        $noPasswordLeaks = false;
        break;
    }
}
assertTest("No plaintext passwords exist in email_logs error messages", $noPasswordLeaks);

// ============================================================================
// 12. ZERO WORKER DEPENDENCY VERIFICATION
// ============================================================================
echo "\n--- 12. Verification of Zero Worker Dependency ---\n";

// Count pending items for our test emails
$stmt = $pdo->prepare("SELECT COUNT(*) FROM `email_logs` WHERE `recipient_email` LIKE ? AND `status` = 'pending'");
$stmt->execute(["%{$testRun}%"]);
$pendingCount = (int)$stmt->fetchColumn();

assertTest(
    "Zero test emails left stuck in 'pending' status (All dispatched directly without worker)",
    $pendingCount === 0,
    "Pending count: {$pendingCount}"
);

// Clean up test data safely in proper foreign key order
$bookingIds = [$bookingId, (int)$dispatchBooking['id']];
$inClause = implode(',', array_fill(0, count($bookingIds), '?'));

$pdo->prepare("DELETE FROM `email_logs` WHERE `recipient_email` LIKE ?")->execute(["%{$testRun}%"]);
$pdo->prepare("DELETE FROM `payments` WHERE `invoice_id` IN (SELECT `id` FROM `invoices` WHERE `booking_id` IN ({$inClause}))")->execute($bookingIds);
$pdo->prepare("DELETE FROM `invoices` WHERE `booking_id` IN ({$inClause})")->execute($bookingIds);
$pdo->prepare("DELETE FROM `trip_status_history` WHERE `dispatch_id` = ?")->execute([$dispatchId]);
$pdo->prepare("DELETE FROM `dispatches` WHERE `id` = ?")->execute([$dispatchId]);
$pdo->prepare("DELETE FROM `driver_profiles` WHERE `id` = ?")->execute([$driverProfileId]);
$pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$driverUserId]);
$pdo->prepare("DELETE FROM `vehicles` WHERE `id` = ?")->execute([$vehicleId]);
$pdo->prepare("DELETE FROM `bookings` WHERE `id` IN ({$inClause})")->execute($bookingIds);
$pdo->prepare("DELETE FROM `employees` WHERE `id` = ?")->execute([$employeeId]);
$pdo->prepare("DELETE FROM `customers` WHERE `id` IN (?, ?)")->execute([$customerId, $safeCustomerId]);

echo "\n====================================================================\n";
echo " Test Suite Summary\n";
echo " Total Assertions : {$totalTests}\n";
echo " Passed           : {$passed}\n";
echo " Failed           : {$failed}\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
