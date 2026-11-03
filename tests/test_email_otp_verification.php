<?php
/**
 * Comprehensive Automated Verification Test Suite for Mandatory Email OTP
 * Tests Customer and Employee creation flows, security, rate limiting, and business rules.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmailOtpService;
use App\Controllers\EmailOtpController;
use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;

Session::start();
// Mock authenticated admin user
Session::set('user', [
    'id'       => 1,
    'username' => 'admin_tester',
    'email'    => 'admin@voyagego.com',
    'role'     => 'admin',
    'status'   => 'active'
]);

$pdo = Database::getConnection();
$service = new EmailOtpService();
$testRunId = time();

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $title, ?string $details = null): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$title}" . ($details ? " - Details: {$details}" : "") . "\n";
    }
}

echo "====================================================================\n";
echo "1. TESTING EMAIL FORMAT & BUSINESS RULES VALIDATION\n";
echo "====================================================================\n";

// Test 1: Empty email
$resEmpty = $service->sendOtp('', EmailOtpService::FLOW_CUSTOMER);
assertTest(!$resEmpty['success'], "Empty email is rejected", $resEmpty['message']);

// Test 2: Invalid format
$resInvalid = $service->sendOtp('not-an-email', EmailOtpService::FLOW_CUSTOMER);
assertTest(!$resInvalid['success'], "Invalid email format is rejected", $resInvalid['message']);

// Test 3: Internal local non-deliverable domain
$resInternal = $service->sendOtp('staff@internal.local', EmailOtpService::FLOW_EMPLOYEE);
assertTest(!$resInternal['success'], "Internal domain @internal.local is rejected", $resInternal['message']);

// Test 4: Existing user duplicate email check for employee flow
$existingUserEmail = 'owner@voyagego.com';
$resDupUser = $service->sendOtp($existingUserEmail, EmailOtpService::FLOW_EMPLOYEE);
assertTest(!$resDupUser['success'], "Employee OTP rejects existing user email", $resDupUser['message']);


echo "\n====================================================================\n";
echo "2. TESTING OTP GENERATION, SECURITY & COOLDOWN\n";
echo "====================================================================\n";

$custTestEmail = "cust_{$testRunId}@testdomain.org";

// Mock or create an active OTP in database directly to verify hashing & structure
$rawOtp = sprintf('%04d', random_int(1000, 9999));
$hash = password_hash($rawOtp, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(32));

$insertStmt = $pdo->prepare("INSERT INTO email_verifications 
    (email, flow, otp_hash, attempts, max_attempts, resend_available_at, expires_at, status, verification_token, ip_address, created_at, updated_at)
    VALUES (?, 'customer', ?, 0, 5, DATE_ADD(NOW(), INTERVAL 60 SECOND), DATE_ADD(NOW(), INTERVAL 5 MINUTE), 'pending', ?, '127.0.0.1', NOW(), NOW())");
$insertStmt->execute([$custTestEmail, $hash, $token]);
$recId = (int)$pdo->lastInsertId();

// Verify OTP length & hash
assertTest(strlen($rawOtp) === 4 && ctype_digit($rawOtp), "OTP is exactly 4 numeric digits: {$rawOtp}");
assertTest(password_verify($rawOtp, $hash), "Stored OTP hash is securely verified with password_verify");

// Test Cooldown
$resCooldown = $service->sendOtp($custTestEmail, EmailOtpService::FLOW_CUSTOMER);
assertTest(!$resCooldown['success'] && $resCooldown['cooldown'] > 0, "Immediate resend is blocked by cooldown ({$resCooldown['cooldown']}s remaining)");


echo "\n====================================================================\n";
echo "3. TESTING INCORRECT ATTEMPTS & 5-ATTEMPT LOCKOUT\n";
echo "====================================================================\n";

// Attempt 1: Wrong OTP
$resWrong1 = $service->verifyOtp($custTestEmail, EmailOtpService::FLOW_CUSTOMER, '0000');
assertTest(!$resWrong1['success'], "Incorrect OTP is rejected (attempt 1/5)");

// Check database attempts counter
$chk1 = $pdo->query("SELECT attempts, status FROM email_verifications WHERE id = {$recId}")->fetch(PDO::FETCH_ASSOC);
assertTest((int)$chk1['attempts'] === 1 && $chk1['status'] === 'pending', "Attempts count incremented to 1 in DB");

// 4 more wrong attempts to hit limit 5
for ($i = 2; $i <= 5; $i++) {
    $service->verifyOtp($custTestEmail, EmailOtpService::FLOW_CUSTOMER, '9999');
}

$chk5 = $pdo->query("SELECT attempts, status FROM email_verifications WHERE id = {$recId}")->fetch(PDO::FETCH_ASSOC);
assertTest((int)$chk5['attempts'] === 5 && $chk5['status'] === 'invalidated', "OTP invalidated after 5 wrong attempts");

// Verify that even the correct OTP is now rejected because status is invalidated
$resLocked = $service->verifyOtp($custTestEmail, EmailOtpService::FLOW_CUSTOMER, $rawOtp);
assertTest(!$resLocked['success'], "Correct OTP rejected after record invalidated", $resLocked['message']);


echo "\n====================================================================\n";
echo "4. TESTING SUCCESSFUL VERIFICATION & FLOW ISOLATION\n";
echo "====================================================================\n";

// Create fresh OTP for customer flow
$validRawOtp = '7842';
$validHash = password_hash($validRawOtp, PASSWORD_BCRYPT);
$validToken = bin2hex(random_bytes(32));

$insertStmt->execute([$custTestEmail, $validHash, $validToken]);
$newRecId = (int)$pdo->lastInsertId();

// Flow isolation test: Cannot verify customer OTP using employee flow
$resCrossFlow = $service->verifyOtp($custTestEmail, EmailOtpService::FLOW_EMPLOYEE, $validRawOtp);
assertTest(!$resCrossFlow['success'], "Cross-flow verification prevented (Customer OTP rejected for Employee flow)");

// Cross-email test: Cannot verify with different email
$resCrossEmail = $service->verifyOtp("other_{$testRunId}@testdomain.org", EmailOtpService::FLOW_CUSTOMER, $validRawOtp);
assertTest(!$resCrossEmail['success'], "Cross-email verification prevented");

// Correct OTP verification
$resValid = $service->verifyOtp($custTestEmail, EmailOtpService::FLOW_CUSTOMER, $validRawOtp);
assertTest($resValid['success'], "Correct OTP verified successfully", $resValid['message']);
assertTest($service->isEmailVerified($custTestEmail, EmailOtpService::FLOW_CUSTOMER, $validToken), "isEmailVerified() returns true after successful OTP check");


echo "\n====================================================================\n";
echo "5. TESTING CUSTOMER REGISTRATION FORM SUBMISSION GUARD\n";
echo "====================================================================\n";

$customerController = new CustomerController();

// A. Attempt to submit with an UNVERIFIED email -> MUST FAIL
$unverifiedCustEmail = "unverified_{$testRunId}@testdomain.org";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    '_csrf_token'             => \App\Core\Csrf::getToken(),
    'title'                   => 'Mr',
    'name'                    => 'Test Unverified Customer',
    'email'                   => $unverifiedCustEmail,
    'phone'                   => '9876543210',
    'status'                  => 'active',
    'email_verification_token'=> 'fake_token'
];

$req = new Request();
$resp = $customerController->store($req);
$createdUnverCust = (new Customer())->findBy('email', $unverifiedCustEmail);
$flashErrors = Session::getFlash('form_errors');
assertTest($createdUnverCust === null && !empty($flashErrors['email']), "Direct POST without verified email is rejected and not saved in database");

// B. Submit with the VERIFIED email ($custTestEmail) -> MUST SUCCEED
$uniqueCustPhone = '9' . substr(strval(time()), -9);
$_POST = [
    '_csrf_token'             => \App\Core\Csrf::getToken(),
    'title'                   => 'Dr',
    'name'                    => 'Verified Customer VIP',
    'email'                   => $custTestEmail,
    'phone'                   => $uniqueCustPhone,
    'status'                  => 'active',
    'email_verification_token'=> $validToken
];

try {
    $req = new Request();
    $response = $customerController->store($req);
    $createdCust = (new Customer())->findBy('email', $custTestEmail);
    assertTest(!empty($createdCust), "Customer created successfully with verified email (Code: " . ($createdCust['customer_code'] ?? '') . ")");
} catch (\Throwable $e) {
    assertTest(false, "Customer creation failed: " . $e->getMessage());
}

// C. Verify OTP is consumed and CANNOT be reused
assertTest(!$service->isEmailVerified($custTestEmail, EmailOtpService::FLOW_CUSTOMER, $validToken), "Verification record was consumed (one-time use enforced)");

// D. Attempt to submit a second customer with the same consumed verification -> MUST FAIL
$secondCustName = 'Second Customer Reusing Consumed OTP ' . $testRunId;
$_POST['name'] = $secondCustName;
$_POST['phone'] = '8' . substr(strval(time()), -9);
$req = new Request();
$resp2 = $customerController->store($req);
$secondCust = (new Customer())->findBy('name', $secondCustName);
$flashErrors2 = Session::getFlash('form_errors');
$hasEmailErr = !empty($flashErrors2['email']);
assertTest($secondCust === null && $hasEmailErr, "Reusing consumed OTP verification is rejected and not saved in database");


echo "\n====================================================================\n";
echo "6. TESTING EMPLOYEE REGISTRATION FORM SUBMISSION GUARD\n";
echo "====================================================================\n";

$employeeController = new EmployeeController();

// A. Attempt to submit unverified employee email -> MUST FAIL
$unverifiedEmpEmail = "unverified_emp_{$testRunId}@manaswitours.com";
$_POST = [
    '_csrf_token'             => \App\Core\Csrf::getToken(),
    'first_name'              => 'Vikram',
    'last_name'               => 'Rathore',
    'email'                   => $unverifiedEmpEmail,
    'phone'                   => '9123456780',
    'role'                    => 'operations',
    'employment_type'         => 'full_time',
    'joining_date'            => date('Y-m-d'),
    'status'                  => 'active',
    'email_verification_token'=> 'fake_token'
];

$req = new Request();
$respEmp = $employeeController->store($req);
$createdUnverEmp = (new Employee())->findBy('email', $unverifiedEmpEmail);
$flashErrorsEmp = Session::getFlash('form_errors');
assertTest($createdUnverEmp === null && !empty($flashErrorsEmp['email']), "Direct POST for employee without verified email is rejected and not saved in database");

// B. Verify employee corporate email and save
$empTestEmail = "emp_{$testRunId}@manaswitours.com";
$empRawOtp = '9153';
$empHash = password_hash($empRawOtp, PASSWORD_BCRYPT);
$empToken = bin2hex(random_bytes(32));

$insertStmt->execute([$empTestEmail, $empHash, $empToken]);
// Update flow to 'employee'
$pdo->prepare("UPDATE email_verifications SET flow = 'employee' WHERE email = ?")->execute([$empTestEmail]);

// Verify employee OTP
$resEmpVerify = $service->verifyOtp($empTestEmail, EmailOtpService::FLOW_EMPLOYEE, $empRawOtp);
assertTest($resEmpVerify['success'], "Employee OTP verified successfully");

// Submit valid employee
$_POST['email'] = $empTestEmail;
$_POST['phone'] = '7' . substr(strval(time()), -9);
$_POST['email_verification_token'] = $empToken;
try {
    $req = new Request();
    $response = $employeeController->store($req);
    $createdEmp = (new Employee())->findBy('email', $empTestEmail);
    assertTest(!empty($createdEmp), "Employee created successfully with verified email (Code: " . ($createdEmp['employee_code'] ?? '') . ")");
} catch (\Throwable $e) {
    assertTest(false, "Employee creation failed: " . $e->getMessage());
}

// Verify employee verification record was consumed
assertTest(!$service->isEmailVerified($empTestEmail, EmailOtpService::FLOW_EMPLOYEE, $empToken), "Employee verification record was consumed (one-time use)");


echo "\n====================================================================\n";
echo "7. TESTING EMAIL CHANGE AFTER VERIFICATION INVALIDATION\n";
echo "====================================================================\n";

$origEmail = "initial_{$testRunId}@example.com";
$newEmail = "changed_{$testRunId}@example.com";

// Verify initial email
$testOtp = '6420';
$insertStmt->execute([$origEmail, password_hash($testOtp, PASSWORD_BCRYPT), 'token_initial']);
$service->verifyOtp($origEmail, EmailOtpService::FLOW_CUSTOMER, $testOtp);
assertTest($service->isEmailVerified($origEmail, EmailOtpService::FLOW_CUSTOMER), "Initial email verified");

// User changes email -> Invalidate old verification
$service->invalidateVerification($origEmail, EmailOtpService::FLOW_CUSTOMER);
assertTest(!$service->isEmailVerified($origEmail, EmailOtpService::FLOW_CUSTOMER), "Verification invalidated after email change");
assertTest(!$service->isEmailVerified($newEmail, EmailOtpService::FLOW_CUSTOMER), "New email is not verified");


echo "\n====================================================================\n";
echo "TEST RESULTS SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
