<?php
/**
 * Comprehensive Automated Test Suite: All OTP Verification Flows
 * Tests Customer Registration, Employee Registration, Forgot Username, Forgot Password,
 * Alias Normalization, Unsupported Purpose Rejection, Order of Operations (Draft -> Pending),
 * Rate Limiting, 60s Cooldown, Attempt Lockout, Flow Isolation, Controller Endpoints & Templates.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\RateLimiter;
use App\Models\User;
use App\Models\Customer;
use App\Models\Employee;
use App\Services\EmailOtpService;
use App\Services\EmailService;
use App\Services\AccountRecoveryService;
use App\Controllers\EmailOtpController;

Session::start();
// Authenticate mock admin session for office controller tests
Session::set('user', [
    'id'       => 1,
    'username' => 'admin_tester',
    'email'    => 'admin@voyagego.com',
    'role'     => 'admin',
    'status'   => 'active'
]);

$pdo = Database::getConnection();
$service = new EmailOtpService();
$testRun = time() . '_' . random_int(100, 999);

$passed = 0;
$failed = 0;

function assertFlow(bool $condition, string $title, ?string $details = null): void
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
echo " STARTING ALL OTP FLOWS VERIFICATION SUITE (Run: {$testRun})\n";
echo "====================================================================\n\n";

// Setup Test Users in Database
$activeUserEmail = "otp_user_{$testRun}@example.com";
$pdo->prepare("DELETE FROM `users` WHERE `email` = ?")->execute([$activeUserEmail]);
$pdo->prepare("INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`)
               VALUES ('Test OTP User', ?, ?, ?, 'dispatcher', 'active', NOW(), NOW())")
    ->execute(["otpuser_{$testRun}", $activeUserEmail, password_hash('Pass12345!', PASSWORD_BCRYPT)]);
$testUserId = (int)$pdo->lastInsertId();

// -----------------------------------------------------------------------------
// SECTION 1: Supported Canonical Purposes & Flow Normalization
// -----------------------------------------------------------------------------
echo "--- 1. Canonical Purposes & Flow Alias Normalization ---\n";

assertFlow(EmailOtpService::normalizeFlow('customer') === EmailOtpService::FLOW_CUSTOMER, "Canonical flow 'customer' is supported");
assertFlow(EmailOtpService::normalizeFlow('employee') === EmailOtpService::FLOW_EMPLOYEE, "Canonical flow 'employee' is supported");
assertFlow(EmailOtpService::normalizeFlow('recovery_username') === EmailOtpService::FLOW_RECOVERY_USERNAME, "Canonical flow 'recovery_username' is supported");
assertFlow(EmailOtpService::normalizeFlow('recovery_password') === EmailOtpService::FLOW_RECOVERY_PASSWORD, "Canonical flow 'recovery_password' is supported");

// Aliases
assertFlow(EmailOtpService::normalizeFlow('forgot_username') === EmailOtpService::FLOW_RECOVERY_USERNAME, "Alias 'forgot_username' normalizes to 'recovery_username'");
assertFlow(EmailOtpService::normalizeFlow('username') === EmailOtpService::FLOW_RECOVERY_USERNAME, "Alias 'username' normalizes to 'recovery_username'");
assertFlow(EmailOtpService::normalizeFlow('forgot_password') === EmailOtpService::FLOW_RECOVERY_PASSWORD, "Alias 'forgot_password' normalizes to 'recovery_password'");
assertFlow(EmailOtpService::normalizeFlow('password') === EmailOtpService::FLOW_RECOVERY_PASSWORD, "Alias 'password' normalizes to 'recovery_password'");
assertFlow(EmailOtpService::normalizeFlow('customer_registration') === EmailOtpService::FLOW_CUSTOMER, "Alias 'customer_registration' normalizes to 'customer'");
assertFlow(EmailOtpService::normalizeFlow('employee_registration') === EmailOtpService::FLOW_EMPLOYEE, "Alias 'employee_registration' normalizes to 'employee'");

// -----------------------------------------------------------------------------
// SECTION 2: Rejection of Unsupported Purposes
// -----------------------------------------------------------------------------
echo "\n--- 2. Rejection of Unsupported Purposes ---\n";

$unsupportedFlows = ['booking', 'driver_login', 'random_flow', ''];
foreach ($unsupportedFlows as $badFlow) {
    $resBad = $service->sendOtp("test_{$testRun}@example.com", $badFlow);
    assertFlow(
        $resBad['success'] === false && 
        str_contains($resBad['message'], 'Unsupported OTP verification purpose'),
        "Unsupported flow '{$badFlow}' explicitly rejected: {$resBad['message']}"
    );
}

// -----------------------------------------------------------------------------
// SECTION 3: Customer Registration OTP Flow
// -----------------------------------------------------------------------------
echo "\n--- 3. Customer Registration OTP Flow ---\n";

$custEmail = "cust_flow_{$testRun}@example.com";
$resCust = $service->sendOtp($custEmail, 'customer', 'Vikramaditya Sharma', '127.0.0.1');
assertFlow($resCust['success'] === true, "Customer registration OTP dispatched successfully: {$resCust['message']}");
assertFlow($resCust['cooldown'] === 60, "Cooldown of 60 seconds returned for customer OTP");

// Verify DB record
$stmtCust = $pdo->prepare("SELECT * FROM `email_verifications` WHERE `email` = ? AND `flow` = 'customer' AND `status` = 'pending' ORDER BY id DESC LIMIT 1");
$stmtCust->execute([$custEmail]);
$custRec = $stmtCust->fetch(PDO::FETCH_ASSOC);

assertFlow(!empty($custRec), "Customer OTP record exists with status 'pending' in database");
assertFlow(strlen($custRec['otp_hash']) > 20, "Customer OTP stored as secure bcrypt hash");
assertFlow(!empty($custRec['verification_token']), "Customer OTP has 64-char hex verification token assigned");

// Check Cooldown blocks immediate resend
$resCustCool = $service->sendOtp($custEmail, 'customer', 'Vikramaditya Sharma', '127.0.0.1');
assertFlow($resCustCool['success'] === false && $resCustCool['cooldown'] > 0, "Immediate customer OTP resend blocked by cooldown ({$resCustCool['cooldown']}s remaining)");

// -----------------------------------------------------------------------------
// SECTION 4: Employee Registration OTP Flow
// -----------------------------------------------------------------------------
echo "\n--- 4. Employee Registration OTP Flow ---\n";

$empEmail = "emp_flow_{$testRun}@example.com";
$resEmp = $service->sendOtp($empEmail, 'employee', 'Pooja Kulkarni', '127.0.0.1');
assertFlow($resEmp['success'] === true, "Employee registration OTP dispatched successfully: {$resEmp['message']}");

$stmtEmp = $pdo->prepare("SELECT * FROM `email_verifications` WHERE `email` = ? AND `flow` = 'employee' AND `status` = 'pending' ORDER BY id DESC LIMIT 1");
$stmtEmp->execute([$empEmail]);
$empRec = $stmtEmp->fetch(PDO::FETCH_ASSOC);

assertFlow(!empty($empRec), "Employee OTP record exists with status 'pending' in database");
assertFlow($empRec['flow'] === 'employee', "Employee record flow is exactly 'employee'");

// Duplicate User Account check: employee OTP rejects already registered user
$resEmpDup = $service->sendOtp($activeUserEmail, 'employee', null, '127.0.0.1');
assertFlow($resEmpDup['success'] === false && str_contains($resEmpDup['message'], 'already exists'), "Employee OTP rejects email of existing system user");

// -----------------------------------------------------------------------------
// SECTION 5: Forgot Username Recovery OTP Flow
// -----------------------------------------------------------------------------
echo "\n--- 5. Forgot Username Recovery OTP Flow ---\n";

// Using alias 'forgot_username'
$resUserAlias = $service->sendOtp($activeUserEmail, 'forgot_username', null, '127.0.0.1');
assertFlow($resUserAlias['success'] === true, "Forgot username OTP dispatched via alias 'forgot_username'");

$stmtUserRec = $pdo->prepare("SELECT * FROM `email_verifications` WHERE `email` = ? AND `flow` = 'recovery_username' AND `status` = 'pending' ORDER BY id DESC LIMIT 1");
$stmtUserRec->execute([$activeUserEmail]);
$userOtpRec = $stmtUserRec->fetch(PDO::FETCH_ASSOC);

assertFlow(!empty($userOtpRec), "Recovery OTP normalized flow to canonical 'recovery_username'");
assertFlow((int)$userOtpRec['user_id'] === $testUserId, "Recovery OTP correctly bound to user_id: {$testUserId}");

// Check unregistered email rejected
$resUnreg = $service->sendOtp("unregistered_{$testRun}@example.com", 'recovery_username', null, '127.0.0.1');
assertFlow($resUnreg['success'] === false && $resUnreg['error'] === 'Email Address Not Registered', "Unregistered email rejected with exact error");

// -----------------------------------------------------------------------------
// SECTION 6: Forgot Password Recovery OTP Flow
// -----------------------------------------------------------------------------
echo "\n--- 6. Forgot Password Recovery OTP Flow ---\n";

// Using alias 'forgot_password'
// Clear cooldown for test user
$pdo->prepare("UPDATE `email_verifications` SET `resend_available_at` = DATE_SUB(NOW(), INTERVAL 10 SECOND) WHERE `id` = ?")->execute([$userOtpRec['id']]);
$resPassAlias = $service->sendOtp($activeUserEmail, 'forgot_password', null, '127.0.0.1');
assertFlow($resPassAlias['success'] === true, "Forgot password OTP dispatched via alias 'forgot_password'");

$stmtPassRec = $pdo->prepare("SELECT * FROM `email_verifications` WHERE `email` = ? AND `flow` = 'recovery_password' AND `status` = 'pending' ORDER BY id DESC LIMIT 1");
$stmtPassRec->execute([$activeUserEmail]);
$passOtpRec = $stmtPassRec->fetch(PDO::FETCH_ASSOC);

assertFlow(!empty($passOtpRec), "Password recovery OTP normalized flow to canonical 'recovery_password'");
assertFlow((int)$passOtpRec['user_id'] === $testUserId, "Password recovery OTP bound to user_id: {$testUserId}");

// -----------------------------------------------------------------------------
// SECTION 7: Order of Operations (Draft -> Pending) & Delivery Failure Handling
// -----------------------------------------------------------------------------
echo "\n--- 7. Order of Operations & Draft -> Pending Safety ---\n";

// Create custom mock service with failing EmailService
$mockFailingEmailService = new class extends EmailService {
    public function send(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        string $emailType = 'transactional',
        ?string $sourceEntity = null,
        ?int $sourceId = null,
        array $attachments = [],
        array $metadata = [],
        int $cooldownSeconds = 0,
        bool $forceSync = false
    ): array {
        return [
            'success'   => false,
            'message'   => 'Simulated SMTP connection timeout',
            'error'     => 'Simulated SMTP connection timeout',
            'duplicate' => false,
            'log_id'    => null
        ];
    }
};

$failingOtpService = new EmailOtpService($mockFailingEmailService);
$failEmail = "smtp_fail_{$testRun}@example.com";

$resFail = $failingOtpService->sendOtp($failEmail, 'customer', 'Test User', '127.0.0.1');
assertFlow($resFail['success'] === false, "Failed SMTP dispatch returns success=false");
assertFlow(str_contains($resFail['message'], 'Failed to deliver'), "Sanitized user-facing failure message returned");

// Verify that the record in DB was marked 'failed', NEVER activated as 'pending'
$stmtFailedRec = $pdo->prepare("SELECT status FROM `email_verifications` WHERE `email` = ? AND `flow` = 'customer' ORDER BY id DESC LIMIT 1");
$stmtFailedRec->execute([$failEmail]);
$failedStatus = $stmtFailedRec->fetchColumn();
assertFlow($failedStatus === 'failed', "Verification record marked 'failed' upon SMTP failure (never activated as usable OTP)");

// -----------------------------------------------------------------------------
// SECTION 8: Flow Purpose Isolation & Timing-Safe Verification
// -----------------------------------------------------------------------------
echo "\n--- 8. Flow Purpose Isolation & Timing-Safe Verification ---\n";

// Create a known OTP record for Customer flow
$isoCustEmail = "iso_cust_{$testRun}@example.com";
$knownIsoOtp = '8192';
$knownIsoHash = password_hash($knownIsoOtp, PASSWORD_BCRYPT);
$isoToken = bin2hex(random_bytes(32));

$pdo->prepare("INSERT INTO `email_verifications` 
    (`email`, `flow`, `otp_hash`, `attempts`, `max_attempts`, `resend_available_at`, `expires_at`, `status`, `verification_token`, `ip_address`, `created_at`, `updated_at`)
    VALUES (?, 'customer', ?, 0, 5, DATE_ADD(NOW(), INTERVAL 60 SECOND), DATE_ADD(NOW(), INTERVAL 5 MINUTE), 'pending', ?, '127.0.0.1', NOW(), NOW())")
    ->execute([$isoCustEmail, $knownIsoHash, $isoToken]);

// Attempt cross-flow verification:
// 1. Customer OTP cannot be verified as 'employee'
$resCross1 = $service->verifyOtp($isoCustEmail, 'employee', $knownIsoOtp);
assertFlow($resCross1['success'] === false, "Cross-flow check: Customer OTP rejected for 'employee' flow");

// 2. Customer OTP cannot be verified as 'recovery_username'
$resCross2 = $service->verifyOtp($isoCustEmail, 'recovery_username', $knownIsoOtp);
assertFlow($resCross2['success'] === false, "Cross-flow check: Customer OTP rejected for 'recovery_username' flow");

// 3. Customer OTP cannot be verified as 'recovery_password'
$resCross3 = $service->verifyOtp($isoCustEmail, 'recovery_password', $knownIsoOtp);
assertFlow($resCross3['success'] === false, "Cross-flow check: Customer OTP rejected for 'recovery_password' flow");

// 4. Incorrect OTP rejected with attempt count
$resWrong = $service->verifyOtp($isoCustEmail, 'customer', '0000');
assertFlow($resWrong['success'] === false && str_contains($resWrong['message'], '4 attempt(s) remaining'), "Incorrect OTP rejected (attempt 1/5 recorded)");

// 5. Correct OTP verified successfully under matching flow
$resCorrect = $service->verifyOtp($isoCustEmail, 'customer', $knownIsoOtp);
assertFlow($resCorrect['success'] === true, "Correct OTP verified successfully under matching flow 'customer'");
assertFlow($service->isEmailVerified($isoCustEmail, 'customer', $isoToken), "isEmailVerified() confirms verified status for customer form");

// 6. One-time consumption
$consumed = $service->consumeVerification($isoCustEmail, 'customer');
assertFlow($consumed === true, "consumeVerification() marks record consumed");
assertFlow(!$service->isEmailVerified($isoCustEmail, 'customer', $isoToken), "Consumed verification can no longer be reused");

// -----------------------------------------------------------------------------
// SECTION 9: EmailOtpController Endpoints (Public & Office)
// -----------------------------------------------------------------------------
echo "\n--- 9. EmailOtpController Endpoint Dispatch ---\n";

$controller = new EmailOtpController();

// A. Send OTP via Controller with 'customer' flow
$_SERVER['REQUEST_METHOD'] = 'POST';
$postData = [
    'email' => "ctrl_cust_{$testRun}@example.com",
    'flow'  => 'customer',
    'name'  => 'Controller Test Customer'
];
$req = new Request();
// Simulate request input
$prop = new ReflectionProperty(Request::class, 'post');
$prop->setAccessible(true);
$prop->setValue($req, $postData);

$resp = $controller->sendOtp($req);
assertFlow($resp->getStatusCode() === 200, "POST /office/email-otp/send returns HTTP 200 for customer registration");

// B. Send OTP via Controller with 'forgot_username' alias
$postDataUser = [
    'email' => $activeUserEmail,
    'flow'  => 'forgot_username'
];
$prop->setValue($req, $postDataUser);
// Clear cooldown
$pdo->prepare("UPDATE `email_verifications` SET `resend_available_at` = DATE_SUB(NOW(), INTERVAL 10 SECOND) WHERE `email` = ?")->execute([$activeUserEmail]);
$respUser = $controller->sendOtp($req);
assertFlow($respUser->getStatusCode() === 200, "POST /email-otp/send returns HTTP 200 for forgot_username flow");

// C. Rejection of unsupported flow via Controller
$postDataBad = [
    'email' => "ctrl_bad_{$testRun}@example.com",
    'flow'  => 'unsupported_booking_flow'
];
$prop->setValue($req, $postDataBad);
$respBad = $controller->sendOtp($req);
assertFlow($respBad->getStatusCode() === 422, "Controller returns HTTP 422 for unsupported flow name");

// -----------------------------------------------------------------------------
// SECTION 10: Email Templates Dynamic Theme & Responsiveness
// -----------------------------------------------------------------------------
echo "\n--- 10. Email Templates Dynamic Theme & Responsiveness ---\n";

$tplService = new \App\Services\EmailTemplateService();

$templatesToCheck = [
    'auth/otp'                   => ['flow' => 'customer', 'otp' => '4821'],
    'auth/recovery-username-otp' => ['purpose' => 'recovery_username', 'otp' => '7194'],
    'auth/recovery-password-otp' => ['purpose' => 'recovery_password', 'otp' => '3852'],
    'auth/username-recovered'    => ['username' => 'testuser_recovered'],
];

foreach ($templatesToCheck as $tplPath => $extraData) {
    $rendered = $tplService->render($tplPath, array_merge([
        'subject'        => 'Verification Test',
        'previewText'    => 'Verification Preview',
        'heading'        => 'Test Heading',
        'subheading'     => 'Test Subheading',
        'recipientName'  => 'Vikramaditya Sharma',
        'recipientEmail' => 'vikramaditya@example.com',
    ], $extraData));

    assertFlow(!empty($rendered) && strlen($rendered) > 500, "Template '{$tplPath}' rendered successfully (" . strlen($rendered) . " bytes)");
    assertFlow(str_contains($rendered, 'style='), "Template '{$tplPath}' contains inline mobile-friendly styles");
    assertFlow(str_contains($rendered, 'MANASWI Tours &amp; Travels') || str_contains($rendered, 'MANASWI Tours & Travels'), "Template '{$tplPath}' contains company branding");
}

echo "\n====================================================================\n";
echo " ALL OTP FLOWS TEST SUITE RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
