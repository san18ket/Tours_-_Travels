<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Comprehensive Verification Suite for Email Subsystem Rebuild
 * Covers: Architecture, Direct SMTP, Dynamic Branding, Privacy, OTP Isolation,
 * Business Triggers, Idempotency, Outbox Retries, and Office Controller Views.
 * ============================================================================
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Services\EmailSettingsService;
use App\Services\EmailTransportService;
use App\Services\EmailTemplateService;
use App\Services\EmailService;
use App\Services\EmailRetryService;
use App\Services\CompanySettingsService;
use App\Services\AccountRecoveryService;
use App\Services\EmailOtpService;
use App\Repositories\EmailLogRepository;
use App\Controllers\EmailController;

class EmailSubsystemRebuildTest
{
    private \PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$message}\n";
        } else {
            $this->failed++;
            $this->errors[] = $message;
            echo "  [FAIL] {$message}\n";
        }
    }

    public function runAll(): void
    {
        echo "\n====================================================================\n";
        echo " STARTING EMAIL SUBSYSTEM REBUILD VERIFICATION SUITE\n";
        echo "====================================================================\n\n";

        $this->testSmtpConfigurationAndMasking();
        $this->testDynamicBrandingAndTemplateEngine();
        $this->testUsernameRecoveryPrivacy();
        $this->testOtpPurposeSeparation();
        $this->testCentralizedBusinessEmailDispatches();
        $this->testDuplicateSuppressionAndIdempotency();
        $this->testManualRetryPipeline();
        $this->testEmailManagementController();

        echo "\n====================================================================\n";
        echo " TEST RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "====================================================================\n\n";

        if ($this->failed > 0) {
            echo "Failed Assertions:\n";
            foreach ($this->errors as $err) {
                echo " - {$err}\n";
            }
            exit(1);
        }

        echo "All email rebuild tests PASSED successfully!\n\n";
    }

    /**
     * 1. Test SMTP Settings, Credential Redaction & Socket Diagnostics
     */
    private function testSmtpConfigurationAndMasking(): void
    {
        echo "--- 1. SMTP Configuration & Masking ---\n";

        $settings = EmailSettingsService::getDisplaySettings();
        $this->assert(!empty($settings['host']), "SMTP Host is configured ({$settings['host']})");
        $this->assert(!empty($settings['port']), "SMTP Port is configured ({$settings['port']})");
        $this->assert(!empty($settings['encryption']), "SMTP Encryption is configured ({$settings['encryption']})");
        $this->assert(str_contains($settings['password_masked'], '••'), "SMTP password is masked in display settings");

        $guidance = EmailSettingsService::getDeliverabilityGuidance();
        $this->assert(!empty($guidance['domain']), "Deliverability guidance resolved domain: {$guidance['domain']}");
        $this->assert(!empty($guidance['spf']['value']), "SPF record guidance is provided");
        $this->assert(!empty($guidance['dkim']['host']), "DKIM record guidance is provided");
        $this->assert(!empty($guidance['dmarc']['value']), "DMARC policy guidance is provided");
    }

    /**
     * 2. Test Dynamic Branding, Color Injection & All 17 Templates
     */
    private function testDynamicBrandingAndTemplateEngine(): void
    {
        echo "\n--- 2. Dynamic Branding & Mobile-First Templates ---\n";

        $theme = CompanySettingsService::getThemeColors();
        $this->assert(!empty($theme['primary']), "Dynamic primary theme color resolved: {$theme['primary']}");
        $this->assert(!empty($theme['secondary']), "Dynamic secondary theme color resolved: {$theme['secondary']}");
        $this->assert(!empty($theme['accent']), "Dynamic accent color resolved: {$theme['accent']}");

        $templateService = new EmailTemplateService();
        $registered = EmailTemplateService::getRegisteredTemplates();
        $this->assert(count($registered) >= 17, "At least 17 templates registered in template service (Found: " . count($registered) . ")");

        $allRenderedOk = true;
        foreach ($registered as $tplKey => $meta) {
            $sample = $templateService->getSampleDataForTemplate($tplKey);
            $html = $templateService->render($tplKey, $sample);

            if (empty($html) || !str_contains($html, '600px') || !str_contains($html, '</html>')) {
                $allRenderedOk = false;
                $this->assert(false, "Template '{$tplKey}' failed structural validation");
            }

            // Check dynamic brand injection
            if (!str_contains(strtolower($html), strtolower($theme['primary'])) && !str_contains($html, '#176B5B') && !str_contains($html, '#176b5b')) {
                $allRenderedOk = false;
                $this->assert(false, "Template '{$tplKey}' does not contain dynamic primary brand color");
            }
        }

        if ($allRenderedOk) {
            $this->assert(true, "All 17 templates rendered successfully with dynamic theme colors & responsive layout");
        }

        // Test plain-text generator
        $sampleHtml = '<p>Hello <strong>World</strong>!</p><a href="https://example.com">Visit site</a>';
        $plain = $templateService->htmlToPlainText($sampleHtml);
        $this->assert(str_contains($plain, 'Hello World!') && str_contains($plain, 'https://example.com'), "HTML to plain text generator produces clean readable fallback");
    }

    /**
     * 3. Test Username Recovery Privacy (Phase 9 strict requirement)
     */
    private function testUsernameRecoveryPrivacy(): void
    {
        echo "\n--- 3. Strict Username Recovery Privacy (Phase 9) ---\n";

        // Setup test user
        $testEmail = 'user_rec_' . time() . '@example.com';
        $testUsername = 'priv_user_' . time();
        $initialHash = password_hash('Secret123', PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO users (name, username, email, password, role, status, created_at, updated_at) VALUES ('Priv User', ?, ?, ?, 'customer', 'active', NOW(), NOW())");
        $stmt->execute([$testUsername, $testEmail, $initialHash]);
        $userId = (int)$this->pdo->lastInsertId();

        $recoveryService = new AccountRecoveryService();
        $sendRes = $recoveryService->sendRecoveryOtp($testEmail, AccountRecoveryService::FLOW_USERNAME, '127.0.0.1');
        $this->assert(!empty($sendRes['success']), "Initiated username recovery OTP for test user");

        // Set known OTP hash
        $knownOtp = '7429';
        $knownHash = password_hash($knownOtp, PASSWORD_BCRYPT);
        $this->pdo->prepare("UPDATE email_verifications SET otp_hash = ? WHERE email = ? AND flow = 'recovery_username'")->execute([$knownHash, $testEmail]);

        // Verify OTP to get authorized recovery token
        $verifyRes = $recoveryService->verifyRecoveryOtp($testEmail, AccountRecoveryService::FLOW_USERNAME, $knownOtp, '127.0.0.1');
        $this->assert(!empty($verifyRes['success']), "Recovery OTP verified successfully");
        $token = $verifyRes['token'] ?? '';
        $this->assert(!empty($token), "Received authorized recovery token");

        // Verify username recovery execution
        $completeResult = $recoveryService->recoverUsername($token);
        $this->assert(!empty($completeResult['success']), "Username recovery executed successfully");

        // CRITICAL PRIVACY CHECK: username must NEVER be in the response array!
        $this->assert(!isset($completeResult['username']), "CRITICAL: completeResult does NOT contain 'username' (zero web/DOM leak)");
        $this->assert(!str_contains(json_encode($completeResult), $testUsername), "CRITICAL: completeResult JSON does NOT contain username string");

        // Verify that EmailService recorded the username recovery log
        $stmtLog = $this->pdo->prepare("SELECT * FROM email_logs WHERE recipient_email = ? AND (email_type = 'username_recovery' OR email_type = 'username_recovered') ORDER BY id DESC LIMIT 1");
        $stmtLog->execute([$testEmail]);
        $emailLog = $stmtLog->fetch(\PDO::FETCH_ASSOC);
        $this->assert(!empty($emailLog), "Email log recorded with type 'username_recovery' for private email dispatch");
    }

    /**
     * 4. Test OTP Purpose Separation (Phase 10 non-interchangeable purposes)
     */
    private function testOtpPurposeSeparation(): void
    {
        echo "\n--- 4. OTP Purpose Isolation (Phase 10) ---\n";

        $otpService = new EmailOtpService();
        $email = 'otp_iso_' . time() . '@example.com';

        // Dispatched customer registration OTP
        $sendRes = $otpService->sendOtp($email, EmailOtpService::FLOW_CUSTOMER);
        $this->assert(!empty($sendRes['success']), "Dispatched customer registration OTP");

        // Set known OTP hash
        $knownOtp = '8152';
        $knownHash = password_hash($knownOtp, PASSWORD_BCRYPT);
        $this->pdo->prepare("UPDATE email_verifications SET otp_hash = ? WHERE email = ? AND flow = 'customer'")->execute([$knownHash, $email]);

        // 1. Attempt verification as employee -> MUST FAIL
        $empRes = $otpService->verifyOtp($email, EmailOtpService::FLOW_EMPLOYEE, $knownOtp);
        $this->assert(empty($empRes['success']), "Customer OTP rejected when verifying as 'employee'");

        // 2. Attempt verification as recovery_password -> MUST FAIL
        $pwdRes = $otpService->verifyOtp($email, 'recovery_password', $knownOtp);
        $this->assert(empty($pwdRes['success']), "Customer OTP rejected when verifying as 'recovery_password'");

        // 3. Attempt verification as recovery_username -> MUST FAIL
        $usrRes = $otpService->verifyOtp($email, 'recovery_username', $knownOtp);
        $this->assert(empty($usrRes['success']), "Customer OTP rejected when verifying as 'recovery_username'");

        // 4. Verify as customer -> MUST SUCCEED
        $custRes = $otpService->verifyOtp($email, EmailOtpService::FLOW_CUSTOMER, $knownOtp);
        $this->assert(!empty($custRes['success']), "Customer OTP verified successfully with matching purpose 'customer'");
    }

    /**
     * 5. Test Centralized Business Email Dispatches (Phase 4 & 5)
     */
    private function testCentralizedBusinessEmailDispatches(): void
    {
        echo "\n--- 5. Centralized Business Notifications Dispatches ---\n";

        $emailService = new EmailService();

        // 1. Diagnostic Test Email
        $testRes = $emailService->sendTestEmail('test_runner@example.com');
        $this->assert(!empty($testRes['success']), "sendTestEmail dispatches successfully");

        // 2. Customer Welcome
        $customer = [
            'id' => 9901,
            'name' => 'John Customer',
            'email' => 'customer_test@example.com',
            'phone' => '+91 9876543210',
            'city' => 'Mumbai'
        ];
        $custRes = $emailService->sendCustomerWelcome($customer);
        $this->assert(!empty($custRes['success']), "sendCustomerWelcome dispatches successfully");

        // 3. Employee Welcome
        $employee = [
            'id' => 9902,
            'name' => 'Jane Employee',
            'email' => 'employee_test@example.com',
            'department' => 'Operations',
            'position' => 'Senior Dispatcher'
        ];
        $empRes = $emailService->sendEmployeeWelcome($employee);
        $this->assert(!empty($empRes['success']), "sendEmployeeWelcome dispatches successfully");

        // 4. Booking Confirmation
        $booking = [
            'id' => 9903,
            'booking_code' => 'BK-TEST-9903',
            'customer_name' => 'Alice Walker',
            'customer_email' => 'alice_test@example.com',
            'pickup_location' => 'Airport Terminal 2',
            'dropoff_location' => 'Grand Hyatt',
            'pickup_date' => date('Y-m-d', strtotime('+2 days')),
            'pickup_time' => '10:00:00',
            'total_amount' => 3500.00,
            'paid_amount' => 1000.00,
            'status' => 'confirmed'
        ];
        $bkConfRes = $emailService->sendBookingConfirmation($booking);
        $this->assert(!empty($bkConfRes['success']), "sendBookingConfirmation dispatches successfully");

        // 5. Booking Cancellation
        $bkCancRes = $emailService->sendBookingCancellation($booking, 'Customer requested itinerary reschedule');
        $this->assert(!empty($bkCancRes['success']), "sendBookingCancellation dispatches successfully");

        // 6. Driver Assignment
        $dispatch = ['id' => 9904, 'booking_id' => 9903];
        $vehicle = ['registration_number' => 'MH-02-EE-1234', 'make' => 'Toyota', 'model' => 'Innova Crysta'];
        $driver = ['id' => 9905, 'full_name' => 'Rajesh Sharma', 'driver_name' => 'Rajesh Sharma', 'email' => 'driver_test@example.com', 'phone' => '+91 9988776655'];
        $drvRes = $emailService->sendDriverAssignment($dispatch, $booking, $vehicle, $driver);
        $this->assert(!empty($drvRes['driver']['success']) && !empty($drvRes['customer']['success']), "sendDriverAssignment dispatches to both driver and customer");

        // 7. Payment Receipt
        $payment = [
            'id' => 9906,
            'receipt_number' => 'RCP-TEST-9906',
            'amount' => 2500.00,
            'payment_method' => 'UPI',
            'payment_date' => date('Y-m-d H:i:s'),
            'transaction_id' => 'TXN9906XYZ',
            'customer_name' => 'Alice Walker',
            'customer_email' => 'alice_test@example.com'
        ];
        $payRes = $emailService->sendPaymentReceipt($payment, null, $booking);
        $this->assert(!empty($payRes['success']), "sendPaymentReceipt dispatches successfully");

        // 8. Invoice Notification
        $invoice = [
            'id' => 9907,
            'invoice_number' => 'INV-TEST-9907',
            'customer_name' => 'Alice Walker',
            'customer_email' => 'alice_test@example.com',
            'total_amount' => 3500.00,
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'issued'
        ];
        $invRes = $emailService->sendInvoiceNotification($invoice);
        $this->assert(!empty($invRes['success']), "sendInvoiceNotification dispatches successfully");

        // 9. Refund Notification
        $refund = [
            'id' => 9908,
            'refund_number' => 'RFD-TEST-9908',
            'customer_name' => 'Alice Walker',
            'customer_email' => 'alice_test@example.com',
            'amount' => 1000.00,
            'refund_method' => 'Original Source',
            'status' => 'processed',
            'reason' => 'Advance refund on booking cancellation'
        ];
        $refRes = $emailService->sendRefundNotification($refund);
        $this->assert(!empty($refRes['success']), "sendRefundNotification dispatches successfully");
    }

    /**
     * 6. Test Idempotency & Duplicate Email Suppression (Phase 12)
     */
    private function testDuplicateSuppressionAndIdempotency(): void
    {
        echo "\n--- 6. Idempotency & Duplicate Suppression ---\n";

        $emailService = new EmailService();
        $targetEmail = 'idem_test_' . time() . '@example.com';
        $subject = 'Idempotency Verification ' . time();
        $body = '<p>Test Idempotency Content</p>';

        // 1st dispatch with 15-minute cooldown
        $res1 = $emailService->sendDirect($targetEmail, 'Idem Tester', $subject, $body, null, 'idempotency_test', null, null, [], [], 15);
        $this->assert(!empty($res1['success']), "First dispatch succeeded");

        // 2nd immediate dispatch with same subject & recipient -> must be suppressed as duplicate
        $res2 = $emailService->sendDirect($targetEmail, 'Idem Tester', $subject, $body, null, 'idempotency_test', null, null, [], [], 15);
        $this->assert(!empty($res2['duplicate']), "Second identical dispatch was suppressed as duplicate (idempotency guard)");
        $this->assert(($res2['status'] ?? '') === 'skipped', "Duplicate dispatch recorded with status 'skipped'");
    }

    /**
     * 7. Test Manual Retry Pipeline (Phase 13)
     */
    private function testManualRetryPipeline(): void
    {
        echo "\n--- 7. Manual Retry Flow ---\n";

        $repo = new EmailLogRepository();
        $retryService = new EmailRetryService($repo);

        // Insert a simulated failed email log
        $stmt = $this->pdo->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, email_type, status, attempt_count, max_attempts, html_body, error_message, created_at) VALUES ('retry_test@example.com', 'Retry Tester', 'Retry Subject', 'test', 'failed', 1, 5, '<p>Retry HTML</p>', 'Simulated connection failure', NOW())");
        $stmt->execute();
        $logId = (int)$this->pdo->lastInsertId();

        $this->assert($logId > 0, "Created test failed email log #{$logId}");

        // Attempt retry
        $retryRes = $retryService->retry($logId);
        $this->assert(!empty($retryRes['success']), "Manual retry succeeded for log #{$logId}");

        // Check updated record in DB
        $updated = $repo->find($logId);
        $this->assert($updated['status'] === 'sent', "Email log status updated to 'sent' after retry");
        $this->assert((int)$updated['attempt_count'] === 2, "Attempt count incremented from 1 to 2");
    }

    /**
     * 8. Test Email Management Controller & Views (Phase 14)
     */
    private function testEmailManagementController(): void
    {
        echo "\n--- 8. Email Controller & Management Views ---\n";

        Session::start();
        $adminUser = [
            'id' => 1,
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ];
        Session::setUser($adminUser);

        $controller = new EmailController();

        // 1. Dashboard Overview
        $reqIndex = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $resIndex = $controller->index($reqIndex);
        $this->assert($resIndex->getStatusCode() === 200, "GET /office/emails returns HTTP 200");
        $this->assert(str_contains($resIndex->getBody(), 'Email Management'), "Overview body contains title 'Email Management'");
        $this->assert(str_contains($resIndex->getBody(), 'Active SMTP Transport'), "Overview body renders active SMTP card");

        // 2. Audit History
        $reqHist = new Request(['status' => 'all'], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $resHist = $controller->history($reqHist);
        $this->assert($resHist->getStatusCode() === 200, "GET /office/emails/history returns HTTP 200");
        $this->assert(str_contains($resHist->getBody(), 'Email Audit History'), "History view renders audit table");

        // 3. Settings & Diagnostics
        $reqSett = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $resSett = $controller->settings($reqSett);
        $this->assert($resSett->getStatusCode() === 200, "GET /office/emails/settings returns HTTP 200");
        $this->assert(str_contains($resSett->getBody(), 'Domain Deliverability'), "Settings view renders SPF/DKIM/DMARC section");

        // 4. Template Previewer
        $reqPrev = new Request(['template' => 'booking/confirmed'], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $resPrev = $controller->preview($reqPrev);
        $this->assert($resPrev->getStatusCode() === 200, "GET /office/emails/preview returns HTTP 200");
        $this->assert(str_contains($resPrev->getBody(), 'Template Previews'), "Preview view renders responsive previewer");

        // 5. Template Raw HTML Render
        $reqRender = new Request(['template' => 'booking/confirmed'], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $resRender = $controller->previewRender($reqRender);
        $this->assert($resRender->getStatusCode() === 200, "GET /office/emails/preview/render returns HTTP 200");
        $this->assert(str_contains($resRender->getBody(), 'CONFIRMED RESERVATION'), "Rendered template contains confirmed reservation heading");
    }
}

// Execute suite
try {
    $test = new EmailSubsystemRebuildTest();
    $test->runAll();
} catch (\Throwable $e) {
    echo "\n[EXCEPTION CAUGHT]: " . $e->getMessage() . "\n";
    echo "In " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
