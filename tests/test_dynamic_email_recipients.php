<?php
/**
 * Test Suite: Dynamic Employee & Customer Email Recipient Integration
 * Comprehensive automated verification for VoyageGo Tours & Travels
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\EmailLog;
use App\Services\EmailService;
use App\Services\BookingService;
use App\Services\DispatchService;
use App\Services\BillingService;
use App\Controllers\EmployeeController;
use App\Controllers\CustomerController;

Session::start();
Session::set('user_id', 1);
Session::set('role', 'admin');

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, ?string $details = null): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}" . ($details ? " -> {$details}" : "") . "\n";
        $failed++;
    }
}

$pdo = Database::getConnection();
$emailService = new EmailService();
$emailLogModel = new EmailLog();
$userModel = new User();
$employeeModel = new Employee();
$customerModel = new Customer();
$driverModel = new DriverProfile();
$bookingModel = new Booking();

echo "============================================================\n";
echo "1. EMPLOYEE REGISTRATION & WELCOME EMAIL (DYNAMIC RECIPIENT)\n";
echo "============================================================\n";

$testEmpEmail = 'test.employee.' . time() . '@example.com';
$testEmpUsername = 'emp_' . time();

// Register employee via EmployeeController store
$employeeController = new EmployeeController();
$empReq = new Request([], [
    'first_name'      => 'Rajesh',
    'last_name'       => 'Verma',
    'phone'           => '9876543210',
    'email'           => $testEmpEmail,
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'role'            => 'driver',
    'status'          => 'active',
    'license_number'  => 'DL-TEST-' . rand(1000, 9999),
    'create_account'  => '1',
    'username'        => $testEmpUsername,
    'password'        => 'Password@123',
    'password_confirmation' => 'Password@123',
], ['REQUEST_METHOD' => 'POST']);

$response = $employeeController->store($empReq);
$createdEmp = $employeeModel->findBy('email', $testEmpEmail);

assertTest("Employee registered with actual email '{$testEmpEmail}'", !empty($createdEmp));
assertTest("Employee record stores exact email address", ($createdEmp['email'] ?? '') === $testEmpEmail);

$createdUser = $userModel->findBy('username', $testEmpUsername);
assertTest("Linked user account created", !empty($createdUser));
assertTest("Linked user account has synchronized email '{$testEmpEmail}'", ($createdUser['email'] ?? '') === $testEmpEmail);
assertTest("No fake '@voyagego.internal' generated for user", !str_contains($createdUser['email'] ?? '', '@voyagego.internal'));
assertTest("Driver profile linked to employee record", !empty($driverModel->findBy('employee_id', (int)$createdEmp['id'])));

// Check email log for welcome email
$logs = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'employee_welcome' ORDER BY id DESC LIMIT 1");
$logs->execute([$testEmpEmail]);
$empWelcomeLog = $logs->fetch(PDO::FETCH_ASSOC);

assertTest("Welcome email logged for employee '{$testEmpEmail}'", !empty($empWelcomeLog));
assertTest("Welcome email subject references employee code", str_contains($empWelcomeLog['subject'] ?? '', $createdEmp['employee_code']));
assertTest("Welcome email recipient name is 'Rajesh Verma'", ($empWelcomeLog['to_name'] ?? '') === 'Rajesh Verma');

echo "\n============================================================\n";
echo "2. CUSTOMER REGISTRATION & WELCOME EMAIL (DYNAMIC RECIPIENT)\n";
echo "============================================================\n";
$pdo->exec("DELETE FROM customers WHERE phone = '9812345678' OR email LIKE 'test.customer.%'");

$testCustEmail = 'test.customer.' . time() . '@example.com';
$customerController = new CustomerController();

$custReq = new Request([], [
    'title'  => 'Mr',
    'name'   => 'Vikram Malhotra',
    'phone'  => '9812345678',
    'email'  => $testCustEmail,
    'city'   => 'Jaipur',
    'status' => 'active',
], ['REQUEST_METHOD' => 'POST']);

$response = $customerController->store($custReq);
$createdCust = $customerModel->findBy('email', $testCustEmail);

assertTest("Customer registered with actual email '{$testCustEmail}'", !empty($createdCust));
assertTest("Customer record stores exact email address", ($createdCust['email'] ?? '') === $testCustEmail);
assertTest("Customer reference code generated", !empty($createdCust['customer_code']));

// Check email log for customer welcome email
$logs = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'customer_welcome' ORDER BY id DESC LIMIT 1");
$logs->execute([$testCustEmail]);
$custWelcomeLog = $logs->fetch(PDO::FETCH_ASSOC);

assertTest("Welcome email logged for customer '{$testCustEmail}'", !empty($custWelcomeLog));
assertTest("Customer welcome email recipient name is 'Vikram Malhotra'", ($custWelcomeLog['to_name'] ?? '') === 'Vikram Malhotra');
assertTest("Customer welcome email subject references customer code", str_contains($custWelcomeLog['subject'] ?? '', $createdCust['customer_code']));

echo "\n============================================================\n";
echo "3. DRIVER ASSIGNMENT DISPATCH (ONLY ASSIGNED DRIVER & CUSTOMER)\n";
echo "============================================================\n";

// Register second employee (should NOT receive first driver's dispatch email)
$unrelatedEmpEmail = 'unrelated.driver.' . time() . '@example.com';
$unrelatedEmpId = $employeeModel->create([
    'employee_code'   => $employeeModel->generateEmployeeCode(),
    'first_name'      => 'Suresh',
    'last_name'       => 'Kumar',
    'phone'           => '9822222222',
    'email'           => $unrelatedEmpEmail,
    'department'      => 'fleet_chauffeur',
    'designation'     => 'Chauffeur / Driver',
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'status'          => 'active',
]);

// Find or create test vehicle
$vehicleModel = new Vehicle();
$vehicle = $vehicleModel->findBy('status', 'available');
if (!$vehicle) {
    $vId = $vehicleModel->create([
        'vehicle_number' => 'RJ14-TEST-' . rand(100, 999),
        'make_model'     => 'Toyota Innova Crysta',
        'seating_capacity'=> 7,
        'fuel_type'      => 'diesel',
        'status'         => 'available',
    ]);
    $vehicle = $vehicleModel->find($vId);
}

// Create a booking for test customer
$bookingService = new BookingService();
$booking = $bookingService->createBooking([
    'customer_profile_id' => $createdCust['id'],
    'pickup_address'      => 'Amer Fort, Jaipur',
    'drop_address'        => 'City Palace, Jaipur',
    'start_date'          => date('Y-m-d', strtotime('+2 days')),
    'end_date'            => date('Y-m-d', strtotime('+3 days')),
    'passenger_count'     => 2,
    'custom_subtotal'     => 4500.00,
]);

assertTest("Booking created with dynamic customer profile ID", (int)$booking['customer_profile_id'] === (int)$createdCust['id']);

// Check that booking_created email went to the customer
$bkgCreatedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'booking_created' AND reference_id = ?");
$bkgCreatedLog->execute([$testCustEmail, $booking['id']]);
assertTest("Booking created email dispatched dynamically to booking customer '{$testCustEmail}'", !empty($bkgCreatedLog->fetch()));

// Assign trip to the assigned driver
$dispatchService = new DispatchService();
$driverProfile = $driverModel->findBy('employee_id', (int)$createdEmp['id']);
$dispatch = $dispatchService->assignBooking((int)$booking['id'], (int)$driverProfile['id'], (int)$vehicle['id']);

assertTest("Dispatch created successfully", !empty($dispatch['id']));

// Verify driver assigned email dispatched to assigned driver's employee email
$driverAssignedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'driver_assigned_driver' AND reference_id = ?");
$driverAssignedLog->execute([$testEmpEmail, $dispatch['id']]);
assertTest("Driver assignment email dispatched to assigned chauffeur '{$testEmpEmail}'", !empty($driverAssignedLog->fetch()));

// Verify customer received driver details email
$custDriverLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'driver_assigned_customer' AND reference_id = ?");
$custDriverLog->execute([$testCustEmail, $dispatch['id']]);
assertTest("Chauffeur & vehicle assigned email dispatched to customer '{$testCustEmail}'", !empty($custDriverLog->fetch()));

// Verify unrelated driver DID NOT receive this email
$unrelatedCheck = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND reference_id = ?");
$unrelatedCheck->execute([$unrelatedEmpEmail, $dispatch['id']]);
assertTest("Unrelated employee '{$unrelatedEmpEmail}' received NO dispatch email", empty($unrelatedCheck->fetch()));

echo "\n============================================================\n";
echo "4. BOOKING CONFIRMATION & CANCELLATION DYNAMIC RECIPIENTS\n";
echo "============================================================\n";

// Confirm booking
$bookingService->updateStatus((int)$booking['id'], 'confirmed');
$bkgConfirmedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'booking_confirmed' AND reference_id = ?");
$bkgConfirmedLog->execute([$testCustEmail, $booking['id']]);
assertTest("Booking confirmed email dispatched dynamically to customer '{$testCustEmail}'", !empty($bkgConfirmedLog->fetch()));

// Create another booking to test cancellation
$booking2 = $bookingService->createBooking([
    'customer_profile_id' => $createdCust['id'],
    'pickup_address'      => 'Hawa Mahal, Jaipur',
    'drop_address'        => 'Airport Terminal 2, Jaipur',
    'start_date'          => date('Y-m-d', strtotime('+5 days')),
    'end_date'            => date('Y-m-d', strtotime('+6 days')),
    'passenger_count'     => 1,
    'custom_subtotal'     => 2200.00,
]);
$bookingService->updateStatus((int)$booking2['id'], 'cancelled', 'Client schedule changed');

$bkgCancelLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'booking_cancelled' AND reference_id = ?");
$bkgCancelLog->execute([$testCustEmail, $booking2['id']]);
assertTest("Booking cancelled email dispatched dynamically to customer '{$testCustEmail}'", !empty($bkgCancelLog->fetch()));

echo "\n============================================================\n";
echo "5. PAYMENT SUCCESS RECEIPT DYNAMIC RECIPIENT\n";
echo "============================================================\n";
$pdo->exec("DELETE FROM payments WHERE idempotency_key = 'Full payment received via card gateway'");

$billingService = new BillingService();
$invoice = $billingService->generateInvoice((int)$booking['id']);
assertTest("Invoice generated with customer_profile_id", (int)$invoice['customer_profile_id'] === (int)$createdCust['id']);

$payment = $billingService->recordPayment(
    (int)$invoice['id'],
    (float)$invoice['total_amount'],
    'online_card',
    'TXN-' . time(),
    'Full payment received via card gateway'
);

$paymentLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'payment_successful' AND reference_id = ?");
$paymentLog->execute([$testCustEmail, $payment['id']]);
assertTest("Payment receipt email dispatched to customer '{$testCustEmail}'", !empty($paymentLog->fetch()));

echo "\n============================================================\n";
echo "6. TRIP OPERATIONS (REMINDER, STARTED, COMPLETED)\n";
echo "============================================================\n";

// Trip Reminder
$reminderResults = $dispatchService->sendTripReminder((int)$dispatch['id']);
$reminderDriverLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'trip_reminder_driver' AND reference_id = ?");
$reminderDriverLog->execute([$testEmpEmail, $dispatch['id']]);
assertTest("Trip reminder email dispatched to assigned driver '{$testEmpEmail}'", !empty($reminderDriverLog->fetch()));

$reminderCustLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'trip_reminder_customer' AND reference_id = ?");
$reminderCustLog->execute([$testCustEmail, $dispatch['id']]);
assertTest("Trip reminder email dispatched to customer '{$testCustEmail}'", !empty($reminderCustLog->fetch()));

// Trip Started
$dispatchService->startTrip((int)$dispatch['id'], (int)$driverProfile['id'], 10500);
$tripStartedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'trip_started' AND reference_id = ?");
$tripStartedLog->execute([$testCustEmail, $dispatch['id']]);
assertTest("Trip started email dispatched to customer '{$testCustEmail}'", !empty($tripStartedLog->fetch()));

// Trip Completed
$dispatchService->completeTrip((int)$dispatch['id'], (int)$driverProfile['id'], 10560);
$tripCompletedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'trip_completed' AND reference_id = ?");
$tripCompletedLog->execute([$testCustEmail, $dispatch['id']]);
assertTest("Trip completed email dispatched to customer '{$testCustEmail}'", !empty($tripCompletedLog->fetch()));

echo "\n============================================================\n";
echo "7. EMAIL CONSISTENCY & ATOMIC SYNCHRONIZATION ON UPDATE\n";
echo "============================================================\n";

$newEmpEmail = 'updated.emp.' . time() . '@example.com';
$updateReq = new Request([], [
    'first_name'      => 'Rajesh',
    'last_name'       => 'Verma',
    'phone'           => '9876543210',
    'email'           => $newEmpEmail,
    'department'      => 'fleet_chauffeur',
    'designation'     => 'Senior Executive Chauffeur',
    'employment_type' => 'full_time',
    'joining_date'    => $createdEmp['joining_date'],
    'status'          => 'active',
    'user_id'         => (string)$createdUser['id'],
], ['REQUEST_METHOD' => 'POST']);

$employeeController->update($updateReq, ['id' => $createdEmp['id']]);

$refreshedEmp = $employeeModel->find((int)$createdEmp['id']);
$refreshedUser = $userModel->find((int)$createdUser['id']);

assertTest("Employee record email updated to '{$newEmpEmail}'", ($refreshedEmp['email'] ?? '') === $newEmpEmail);
assertTest("Linked user account email synchronized to '{$newEmpEmail}'", ($refreshedUser['email'] ?? '') === $newEmpEmail);
assertTest("Employee and User emails are completely identical", $refreshedEmp['email'] === $refreshedUser['email']);

// Test customer email update
$newCustEmail = 'updated.cust.' . time() . '@example.com';
$custUpdateReq = new Request([], [
    'title'  => 'Mr',
    'name'   => 'Vikram Malhotra',
    'phone'  => '9812345678',
    'email'  => $newCustEmail,
    'city'   => 'Jaipur',
    'status' => 'active',
], ['REQUEST_METHOD' => 'POST']);

$customerController->update($custUpdateReq, ['id' => $createdCust['id']]);
$refreshedCust = $customerModel->find((int)$createdCust['id']);
assertTest("Customer profile email updated to '{$newCustEmail}'", ($refreshedCust['email'] ?? '') === $newCustEmail);

// Create another booking and verify it resolves the newly updated customer email
$booking3 = $bookingService->createBooking([
    'customer_profile_id' => $createdCust['id'],
    'pickup_address'      => 'Jantar Mantar, Jaipur',
    'drop_address'        => 'Nahargarh Fort, Jaipur',
    'start_date'          => date('Y-m-d', strtotime('+10 days')),
    'end_date'            => date('Y-m-d', strtotime('+11 days')),
    'passenger_count'     => 2,
    'custom_subtotal'     => 3000.00,
]);

$newBookingLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'booking_created' AND reference_id = ?");
$newBookingLog->execute([$newCustEmail, $booking3['id']]);
assertTest("Future booking uses updated customer email '{$newCustEmail}'", !empty($newBookingLog->fetch()));

// Verify updated driver receives future trip assignment at the new email address
$vehicle2 = $vehicleModel->findBy('status', 'available');
if (!$vehicle2) {
    $vehicle2 = $vehicle;
}
$dispatch2 = $dispatchService->assignBooking((int)$booking3['id'], (int)$driverProfile['id'], (int)$vehicle2['id']);

$newDriverAssignedLog = $pdo->prepare("SELECT * FROM email_logs WHERE to_email = ? AND email_type = 'driver_assigned_driver' AND reference_id = ?");
$newDriverAssignedLog->execute([$newEmpEmail, $dispatch2['id']]);
assertTest("Future trip assignment uses updated driver email '{$newEmpEmail}'", !empty($newDriverAssignedLog->fetch()));

echo "\n============================================================\n";
echo "8. MISSING & INVALID EMAIL HANDLING (NO SMTP ATTEMPTS, SAFE SKIP)\n";
echo "============================================================\n";

// Validation rejection: employee registration without email
$empNoEmailReq = new Request([], [
    'first_name'      => 'Test',
    'last_name'       => 'NoEmail',
    'phone'           => '9800000000',
    'email'           => '',
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'role'            => 'driver',
    'status'          => 'active',
], ['REQUEST_METHOD' => 'POST']);
$employeeController->store($empNoEmailReq);
$empErrors = Session::getFlash('form_errors') ?? [];
assertTest("Employee registration without email fails validation", !empty($empErrors['email']));

// Validation rejection: employee registration with invalid email format
$empInvalidEmailReq = new Request([], [
    'first_name'      => 'Test',
    'last_name'       => 'BadEmail',
    'phone'           => '9800000000',
    'email'           => 'invalid-email-format',
    'employment_type' => 'full_time',
    'joining_date'    => date('Y-m-d'),
    'role'            => 'driver',
    'status'          => 'active',
], ['REQUEST_METHOD' => 'POST']);
$employeeController->store($empInvalidEmailReq);
$empBadErrors = Session::getFlash('form_errors') ?? [];
assertTest("Employee registration with invalid email format fails validation", !empty($empBadErrors['email']));

// Validation rejection: customer registration without email
$custNoEmailReq = new Request([], [
    'title'  => 'Mr',
    'name'   => 'Test NoEmail',
    'phone'  => '9800000001',
    'email'  => '',
    'status' => 'active',
], ['REQUEST_METHOD' => 'POST']);
$customerController->store($custNoEmailReq);
$custErrors = Session::getFlash('form_errors') ?? [];
assertTest("Customer registration without email fails validation", !empty($custErrors['email']));

// Legacy record with missing email skips safely without breaking operations
$fakeLegacyBooking = [
    'id'                  => 999991,
    'booking_code'        => 'VG-LEGACY-001',
    'customer_profile_id' => null,
    'customer_id'         => null,
    'customer_name'       => 'Legacy Guest',
    'customer_email'      => '',
    'customer_phone'      => '9999999999',
    'pickup_address'      => 'A',
    'drop_address'        => 'B',
    'start_date'          => date('Y-m-d'),
    'end_date'            => date('Y-m-d'),
    'passenger_count'     => 1,
    'total_amount'        => 1000.00,
];

$skipResult = $emailService->sendBookingCreated($fakeLegacyBooking);
assertTest("Booking notification with empty email safely skips delivery", $skipResult['success'] === false);
assertTest("Safe skip returns clean message", str_contains($skipResult['message'], 'no valid email'));

// Legacy fake email (@voyagego.internal) skips delivery
$fakeInternalBooking = array_merge($fakeLegacyBooking, [
    'id'             => 999992,
    'customer_email' => 'driver123@voyagego.internal',
]);
$skipFakeResult = $emailService->sendBookingCreated($fakeInternalBooking);
assertTest("Notification to '@voyagego.internal' is rejected and skips delivery", $skipFakeResult['success'] === false);

echo "\n============================================================\n";
echo "9. IDEMPOTENCY & DUPLICATE EVENT PREVENTION\n";
echo "============================================================\n";

// Attempt duplicate confirmation for booking
$dupResult = $emailService->sendBookingConfirmed($booking);
assertTest("Duplicate event is prevented by idempotency protection", $dupResult['duplicate'] === true);

echo "\n============================================================\n";
echo "TEST RESULTS SUMMARY\n";
echo "============================================================\n";
echo "Total Passed: {$passed}\n";
echo "Total Failed: {$failed}\n";

if ($failed === 0) {
    echo "\n>>> ALL DYNAMIC EMAIL RECIPIENT TESTS PASSED SUCCESSFULLY! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
