<?php
namespace App\Services;

use App\Repositories\EmailLogRepository;
use App\Models\EmailLog;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Core\Logger;
use Throwable;

/**
 * Centralized Application Email Service
 * Coordinates all transactional and operational emails across Tours & Travels.
 * Uses a single standardized direct SMTP pipeline with dynamic company branding,
 * website theme-matching, idempotency protection, and comprehensive audit logging.
 */
class EmailService
{
    private EmailTransportService $transport;
    private EmailTemplateService $templateService;
    private EmailLogService $logService;
    private EmailLogRepository $repository;
    private EmailRetryService $retryService;

    /**
     * Synchronous dispatch mode flag.
     * When false (default), non-critical business notifications are stored as 'pending'
     * and the CLI background worker is triggered asynchronously, enabling lightning-fast
     * form response times (<100ms) without waiting for slow SMTP SSL network round-trips.
     * When true (or for OTPs/Recovery), emails are transmitted synchronously.
     */
    public static bool $syncMode = false;

    /**
     * High-priority email types that always require immediate synchronous delivery
     */
    public const SYNCHRONOUS_TYPES = [
        'email_verification_otp',
        'account_recovery_otp',
        'username_recovery',
        'diagnostic_test',
        'smtp_test',
    ];

    /**
     * Resolves the real PHP CLI executable binary.
     * Prevents fatal invocation of the Apache webserver binary (httpd.exe) when running under mod_php.
     */
    public static function getPhpCliBinary(): string
    {
        if (!empty($_ENV['PHP_CLI_PATH']) && file_exists($_ENV['PHP_CLI_PATH'])) {
            return $_ENV['PHP_CLI_PATH'];
        }
        if (!empty(getenv('PHP_CLI_PATH')) && file_exists(getenv('PHP_CLI_PATH'))) {
            return getenv('PHP_CLI_PATH');
        }

        $currentBinary = PHP_BINARY;
        if (!empty($currentBinary) && file_exists($currentBinary)) {
            $basename = strtolower(basename($currentBinary));
            if (!str_contains($basename, 'httpd') && !str_contains($basename, 'apache')) {
                return $currentBinary;
            }
            // If current binary is apache/httpd, look for sibling php directory in XAMPP
            $apacheDir = dirname($currentBinary);
            $xamppRoot = dirname($apacheDir);
            $candidate = $xamppRoot . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'php.exe';
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $commonWindowsPaths = [
            'E:\\xampp\\php\\php.exe',
            'C:\\xampp\\php\\php.exe',
            'D:\\xampp\\php\\php.exe',
            'C:\\Program Files\\PHP\\php.exe',
        ];

        foreach ($commonWindowsPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $which = @trim((string) shell_exec('where php 2>NUL'));
            if (!empty($which)) {
                $lines = explode("\n", str_replace("\r", "", $which));
                if (!empty($lines[0]) && file_exists($lines[0])) {
                    return $lines[0];
                }
            }
        } else {
            $which = @trim((string) shell_exec('which php 2>/dev/null'));
            if (!empty($which) && file_exists($which)) {
                return $which;
            }
        }

        return 'php';
    }

    /**
     * Trigger background CLI worker non-blockingly without delaying the HTTP response
     */
    public static function dispatchBackgroundWorker(): void
    {
        try {
            $phpBinary = self::getPhpCliBinary();
            $workerScript = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'email_worker.php';
            if (!file_exists($workerScript)) {
                return;
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $descriptors = [
                    0 => ['file', 'NUL', 'r'],
                    1 => ['file', 'NUL', 'w'],
                    2 => ['file', 'NUL', 'w'],
                ];
                $pipes = [];
                proc_open(
                    "\"{$phpBinary}\" \"{$workerScript}\" --once",
                    $descriptors,
                    $pipes,
                    dirname($workerScript),
                    null,
                    ['bypass_shell' => true]
                );
            } else {
                exec("\"{$phpBinary}\" \"{$workerScript}\" --once > /dev/null 2>&1 &");
            }
        } catch (Throwable $e) {
            Logger::warning("EmailService::dispatchBackgroundWorker spawn failed: " . $e->getMessage());
        }
    }

    public function __construct(
        ?EmailTransportService $transport = null,
        ?EmailTemplateService $templateService = null,
        ?EmailLogService $logService = null,
        ?EmailLogRepository $repository = null,
        ?EmailRetryService $retryService = null
    ) {
        $this->transport = $transport ?? new EmailTransportService();
        $this->templateService = $templateService ?? new EmailTemplateService();
        $this->logService = $logService ?? new EmailLogService();
        $this->repository = $repository ?? new EmailLogRepository();
        $this->retryService = $retryService ?? new EmailRetryService($this->repository, $this->transport, $this->logService, $this->templateService);
    }

    /**
     * Master centralized email dispatch pipeline
     *
     * @param string $toEmail Recipient email address
     * @param string|null $toName Recipient display name
     * @param string $subject Email subject line
     * @param string $htmlBody Rendered HTML content
     * @param string|null $textBody Optional plain text alternative
     * @param string $emailType Unique event category
     * @param string|null $referenceType Related database entity (e.g. 'booking', 'customer')
     * @param int|null $referenceId Related entity record ID
     * @param array $cc Carbon copy recipient emails
     * @param array $bcc Blind carbon copy recipient emails
     * @param int $cooldownMinutes 0 means permanent single-dispatch for entity, >0 is cooldown window
     * @param bool $forceSync Force synchronous delivery regardless of email type
     * @return array ['success' => bool, 'status' => string, 'message' => string, 'duplicate' => bool, 'log_id' => ?int, 'message_id' => ?string, 'error' => ?string]
     */
    public function send(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        string $emailType = 'general',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $cc = [],
        array $bcc = [],
        int $cooldownMinutes = 0,
        bool $forceSync = false
    ): array {
        $toEmail = trim($toEmail);
        $toName = $toName ? trim($toName) : null;

        // 1. Recipient Validation
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL) ||
            str_ends_with(strtolower($toEmail), '@internal.local') ||
            str_ends_with(strtolower($toEmail), '@voyagego.internal')) {
            $msg = "Invalid recipient email address: '{$toEmail}'";
            Logger::warning("EmailService: {$msg} [Type: {$emailType}]");

            $logId = $this->logService->recordAttempt(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'failed',
                $msg,
                null,
                null,
                $htmlBody,
                $textBody ?: EmailTemplateService::htmlToPlainText($htmlBody),
                $cc,
                $bcc
            );

            return [
                'success'    => false,
                'status'     => 'failed',
                'message'    => $msg,
                'duplicate'  => false,
                'log_id'     => $logId,
                'message_id' => null,
                'error'      => $msg
            ];
        }

        // 2. Duplicate Prevention / Idempotency Check
        $idempotencyHash = EmailLogService::makeHash($emailType, $referenceType, $referenceId, $toEmail);

        if ($this->repository->hasDuplicateHash($idempotencyHash, $cooldownMinutes)) {
            Logger::info("EmailService: Duplicate email prevented by hash for {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail}");
            return [
                'success'    => true,
                'status'     => 'skipped',
                'message'    => 'Email already dispatched (duplicate prevented).',
                'duplicate'  => true,
                'log_id'     => null,
                'message_id' => null,
                'error'      => null
            ];
        }

        if ($cooldownMinutes === 0 && !empty($referenceType) && !empty($referenceId) &&
            !in_array($emailType, ['email_verification_otp', 'account_recovery_otp'], true)) {
            if ($this->repository->hasSentForEntity($emailType, $referenceType, $referenceId)) {
                Logger::info("EmailService: Duplicate prevented by entity reference for {$emailType} [{$referenceType}:{$referenceId}]");
                return [
                    'success'    => true,
                    'status'     => 'skipped',
                    'message'    => 'Email already dispatched (duplicate prevented).',
                    'duplicate'  => true,
                    'log_id'     => null,
                    'message_id' => null,
                    'error'      => null
                ];
            }
        }

        // 3. Determine Execution Mode (Synchronous vs Non-Blocking Background Dispatch)
        $isSync = $forceSync
            || self::$syncMode === true
            || in_array($emailType, self::SYNCHRONOUS_TYPES, true);

        if (!$isSync) {
            // Asynchronous Safe Dispatch: Record pending outbox entry & trigger background worker
            $logId = $this->logService->recordAttempt(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'pending',
                null,
                $idempotencyHash,
                null,
                $htmlBody,
                $textBody ?: EmailTemplateService::htmlToPlainText($htmlBody),
                $cc,
                $bcc
            );

            // Trigger non-blocking worker spawn to immediately process outbox
            self::dispatchBackgroundWorker();

            Logger::info("EmailService: Scheduled {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail} via background outbox (Log #{$logId})");

            return [
                'success'    => true,
                'status'     => 'pending',
                'message'    => 'Email scheduled for immediate delivery.',
                'duplicate'  => false,
                'log_id'     => $logId,
                'message_id' => null,
                'error'      => null
            ];
        }

        // 4. Synchronous Direct SMTP Transmission (for OTPs, recovery & synchronous test runs)
        try {
            $messageId = $this->transport->send($toEmail, $toName, $subject, $htmlBody, $textBody, $cc, $bcc);

            // Record successful dispatch
            $logId = $this->logService->recordAttempt(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'sent',
                null,
                $idempotencyHash,
                $messageId,
                $htmlBody,
                $textBody ?: EmailTemplateService::htmlToPlainText($htmlBody),
                $cc,
                $bcc
            );

            Logger::info("EmailService: Successfully dispatched {$emailType} [{$referenceType}:{$referenceId}] to {$toEmail} (MsgID: {$messageId})");

            return [
                'success'    => true,
                'status'     => 'sent',
                'message'    => 'Email transmitted successfully.',
                'duplicate'  => false,
                'log_id'     => $logId,
                'message_id' => $messageId,
                'error'      => null
            ];

        } catch (Throwable $e) {
            $cleanError = EmailLogService::sanitizeErrorMessage($e->getMessage());
            Logger::error("EmailService: Transmission failure for {$emailType} to {$toEmail}: {$cleanError}");

            $logId = $this->logService->recordAttempt(
                $toEmail,
                $toName,
                $emailType,
                $subject,
                $referenceType,
                $referenceId,
                'failed',
                $cleanError,
                $idempotencyHash,
                null,
                $htmlBody,
                $textBody ?: EmailTemplateService::htmlToPlainText($htmlBody),
                $cc,
                $bcc
            );

            return [
                'success'    => false,
                'status'     => 'failed',
                'message'    => 'Email transmission failed.',
                'duplicate'  => false,
                'log_id'     => $logId,
                'message_id' => null,
                'error'      => $cleanError
            ];
        }
    }

    /**
     * Render an email template using the centralized template service
     */
    public function renderTemplate(string $templateName, array $data = []): string
    {
        return $this->templateService->render($templateName, $data);
    }

    /**
     * Retry a previously failed email record
     */
    public function retryFailedEmail(int $logId): array
    {
        return $this->retryService->retry($logId);
    }

    /**
     * Send email directly through centralized SMTP transport pipeline
     */
    public function sendDirect(string $toEmail, ?string $toName, string $subject, string $htmlBody, ?string $textBody = null, string $emailType = 'general', ?string $referenceType = null, ?int $referenceId = null, array $cc = [], array $bcc = [], int $cooldownMinutes = 0): array
    {
        return $this->send($toEmail, $toName, $subject, $htmlBody, $textBody, $emailType, $referenceType, $referenceId, $cc, $bcc, $cooldownMinutes);
    }

    /**
     * Process an existing queued or outbox email record (e.g. from CLI email worker)
     */
    public function processQueuedEmail(array $logItem): array
    {
        $logId = (int)($logItem['id'] ?? 0);
        if ($logId <= 0) {
            return ['success' => false, 'error' => 'Invalid email record ID'];
        }
        return $this->retryService->retry($logId);
    }

    // =========================================================================
    // SPECIFIC APPLICATION EVENT NOTIFICATION METHODS
    // =========================================================================

    /**
     * 1. Registration OTP (Customer / Employee Onboarding)
     */
    public function sendRegistrationOtp(string $email, string $otp, string $flow = 'customer', ?string $recipientName = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        $flowLabel = $flow === 'employee' ? 'Employee Registration' : 'Customer Registration';
        $subject = "Your Verification Code: {$otp} — {$company}";

        $greetingLead = !empty($recipientName)
            ? "Hello " . htmlspecialchars($recipientName) . ", please use the 4-digit verification code below to complete your {$company} {$flowLabel}."
            : "Please use the 4-digit verification code below to verify your email address for your {$company} {$flowLabel}.";

        $html = $this->renderTemplate('auth/otp', [
            'subject'        => $subject,
            'previewText'    => "Your 4-digit verification code is {$otp}. Valid for 5 minutes.",
            'category'       => 'EMAIL VERIFICATION',
            'heading'        => 'Verify your email address',
            'subheading'     => "Enter the 4-digit code sent to {$email} to proceed.",
            'recipientName'  => $recipientName,
            'recipientEmail' => $email,
            'greetingLead'   => $greetingLead,
            'otp'            => $otp,
            'flow'           => $flow,
        ]);

        $text = "Your 4-digit verification code for {$company} ({$flowLabel}) is: {$otp}\n\nThis code expires in 5 minutes.\nSecurity note: Do not share this code with anyone.";

        return $this->send($email, $recipientName, $subject, $html, $text, 'email_verification_otp', 'email_verification', null, [], [], 0);
    }

    /**
     * 2. Password Recovery OTP
     */
    public function sendPasswordRecoveryOtp(string $email, string $otp, ?string $recipientName = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        $subject = "Password Reset Verification Code — {$company}";

        $html = $this->renderTemplate('auth/recovery-password-otp', [
            'subject'        => $subject,
            'previewText'    => "Your 4-digit password reset code is {$otp}. Valid for 5 minutes.",
            'category'       => 'ACCOUNT SECURITY',
            'heading'        => 'Reset your account password',
            'subheading'     => 'Use this 4-digit code to securely verify your identity and set a new password.',
            'recipientName'  => $recipientName,
            'recipientEmail' => $email,
            'greetingLead'   => "Hello " . htmlspecialchars($recipientName ?? 'Valued User') . ", we received a request to reset the password for your {$company} account.",
            'otp'            => $otp,
            'purpose'        => 'recovery_password',
        ]);

        $text = "Your password reset verification code for {$company} is: {$otp}\n\nThis verification code expires in 5 minutes.\nSecurity warning: Never share this code with anyone.\nIf you did not request this, please disregard this email.";

        return $this->send($email, $recipientName, $subject, $html, $text, 'account_recovery_otp', 'email_verification', null, [], [], 0);
    }

    /**
     * 3. Username Recovery (Private Email Delivery — Never displayed on webpage)
     */
    public function sendUsernameRecovery(array $user): array
    {
        $company = CompanySettingsService::getCompanyName();
        $email = strtolower(trim((string)($user['email'] ?? '')));
        $name = !empty($user['name']) ? $user['name'] : ($user['username'] ?? 'User');
        $username = $user['username'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'User account does not have a valid email address.'];
        }

        $subject = "Your Username Recovery Request — {$company}";

        $html = $this->renderTemplate('auth/username-recovered', [
            'subject'        => $subject,
            'previewText'    => "Your account username has been successfully recovered.",
            'category'       => 'ACCOUNT SECURITY',
            'heading'        => 'Your username has been recovered',
            'subheading'     => 'We received a verified request to recover your account login username.',
            'recipientName'  => $name,
            'recipientEmail' => $email,
            'username'       => $username,
            'greetingLead'   => "Hello " . htmlspecialchars($name) . ", we received a request to recover your {$company} account username.",
        ]);

        $text = "Hello {$name},\n\nWe received a request to recover your {$company} account username.\n\nYour verified username is: {$username}\n\nIf you did not request this, please contact our support team immediately.\nFor your security, never share your password or OTP with anyone.\n\nRegards,\n{$company}";

        return $this->send($email, $name, $subject, $html, $text, 'username_recovery', 'users', (int)($user['id'] ?? 0), [], [], 0);
    }

    /**
     * 4. Customer Welcome Onboarding
     */
    public function sendCustomerWelcome(array $customer): array
    {
        $company = CompanySettingsService::getCompanyName();
        $email = trim((string)($customer['email'] ?? ''));
        $name = trim((string)($customer['name'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Welcome to {$company} — Your Account is Ready";
        $html = $this->renderTemplate('customer/welcome', [
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Thank you for registering with {$company}. Your customer profile is active.",
            'category'    => 'CUSTOMER ONBOARDING',
            'heading'     => "Welcome to {$company}",
            'subheading'  => 'Your customer account has been established successfully.',
        ]);

        return $this->send($email, $name, $subject, $html, null, 'customer_welcome', 'customer', (int)($customer['id'] ?? 0));
    }

    /**
     * 5. Employee Welcome Onboarding
     */
    public function sendEmployeeWelcome(array $employee): array
    {
        $company = CompanySettingsService::getCompanyName();
        $email = trim((string)($employee['email'] ?? ''));
        $name = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Employee has no valid email address.'];
        }

        $subject = "Welcome to the Team — {$company}";
        $html = $this->renderTemplate('employee/welcome', [
            'employee'    => $employee,
            'subject'     => $subject,
            'previewText' => "Your employee profile has been created at {$company}.",
            'category'    => 'STAFF ONBOARDING',
            'heading'     => "Welcome to the Team, " . htmlspecialchars($employee['first_name'] ?? 'Colleague') . "!",
            'subheading'  => "Your staff profile has been activated.",
        ]);

        return $this->send($email, $name, $subject, $html, null, 'employee_welcome', 'employee', (int)($employee['id'] ?? 0));
    }

    /**
     * 6. Booking Created (Received / Pending)
     */
    public function sendBookingCreated(array $booking): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Booking Received: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/created', [
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your reservation {$booking['booking_code']} has been received and is pending confirmation.",
            'category'    => 'BOOKING RESERVATION',
            'heading'     => 'Booking Request Received',
            'subheading'  => "Reservation #{$booking['booking_code']} is under operational review.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'booking_created', 'booking', (int)$booking['id']);
    }

    /**
     * 7. Booking Confirmation
     */
    public function sendBookingConfirmation(array $booking): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Booking Confirmed: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/confirmed', [
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Great news! Your booking {$booking['booking_code']} has been confirmed.",
            'category'    => 'CONFIRMED RESERVATION',
            'heading'     => 'Booking Confirmed!',
            'subheading'  => "Your travel booking #{$booking['booking_code']} is locked in and ready.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'booking_confirmed', 'booking', (int)$booking['id']);
    }

    // Alias for backward compatibility
    public function sendBookingConfirmed(array $booking): array
    {
        return $this->sendBookingConfirmation($booking);
    }

    /**
     * 8. Booking Cancellation
     */
    public function sendBookingCancellation(array $booking, ?string $reason = null): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Booking Cancellation: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('booking/cancelled', [
            'booking'     => $booking,
            'customer'    => $customer,
            'reason'      => $reason,
            'subject'     => $subject,
            'previewText' => "Your reservation {$booking['booking_code']} has been cancelled.",
            'category'    => 'BOOKING UPDATE',
            'heading'     => 'Booking Cancelled',
            'subheading'  => "Reservation #{$booking['booking_code']} has been cancelled.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'booking_cancelled', 'booking', (int)$booking['id']);
    }

    // Alias for backward compatibility
    public function sendBookingCancelled(array $booking, ?string $reason = null): array
    {
        return $this->sendBookingCancellation($booking, $reason);
    }

    /**
     * 9. Driver Assignment (Notifies both Driver and Customer)
     */
    public function sendDriverAssignment(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $driverUser = $this->resolveDriverUserDetails($driverProfile);

        $results = ['driver' => null, 'customer' => null];

        // 1. Send duty notification to Driver
        if (!empty($driverUser['email']) && filter_var($driverUser['email'], FILTER_VALIDATE_EMAIL)) {
            $subjectDriver = "Duty Assignment: Trip #{$booking['booking_code']} — {$company}";
            $htmlDriver = $this->renderTemplate('driver/trip-assigned', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => array_merge($driverProfile, $driverUser),
                'customer'    => $customer,
                'subject'     => $subjectDriver,
                'previewText' => "New trip assigned to you for " . ($booking['start_date'] ?? ($booking['pickup_date'] ?? 'today')) . ".",
                'category'    => 'CHAUFFEUR DUTY DISPATCH',
                'heading'     => 'New Trip Duty Assigned',
                'subheading'  => "Please review your schedule and report 30 minutes prior to pickup.",
            ]);

            $results['driver'] = $this->send(
                $driverUser['email'],
                $driverUser['name'] ?? null,
                $subjectDriver,
                $htmlDriver,
                null,
                'driver_assigned_driver',
                'dispatch',
                (int)$dispatch['id']
            );
        }

        // 2. Send chauffeur details to Customer
        if (!empty($customer['email']) && filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
            $subjectCustomer = "Chauffeur & Fleet Assigned: {$booking['booking_code']} — {$company}";
            $htmlCustomer = $this->renderTemplate('booking/driver-assigned', [
                'dispatch'    => $dispatch,
                'booking'     => $booking,
                'vehicle'     => $vehicle,
                'driver'      => array_merge($driverProfile, $driverUser),
                'customer'    => $customer,
                'subject'     => $subjectCustomer,
                'previewText' => "Chauffeur and vehicle have been assigned for booking {$booking['booking_code']}.",
                'category'    => 'TRIP OPERATIONS',
                'heading'     => 'Your Chauffeur Has Been Assigned',
                'subheading'  => "Chauffeur details for your upcoming trip #{$booking['booking_code']}.",
            ]);

            $results['customer'] = $this->send(
                $customer['email'],
                $customer['name'] ?? null,
                $subjectCustomer,
                $htmlCustomer,
                null,
                'driver_assigned_customer',
                'dispatch',
                (int)$dispatch['id']
            );
        }

        return $results;
    }

    // Alias for backward compatibility
    public function sendDriverAssigned(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        return $this->sendDriverAssignment($dispatch, $booking, $vehicle, $driverProfile);
    }

    /**
     * 10. Payment Receipt / Confirmation
     */
    public function sendPaymentReceipt(array $payment, ?array $invoice = null, ?array $booking = null): array
    {
        $company = CompanySettingsService::getCompanyName();

        if ($invoice && !empty($invoice['booking_code']) && !isset($invoice['invoice_number'])) {
            $booking = $invoice;
            $invoice = null;
        }

        if (!$invoice && !empty($payment['invoice_id'])) {
            $invoice = (new Invoice())->find((int)$payment['invoice_id']);
        }
        if (!$booking && !empty($invoice['booking_id'])) {
            $booking = (new Booking())->find((int)$invoice['booking_id']);
        }

        $customer = $booking ? $this->resolveCustomerDetails($booking) : [];
        if (empty($customer['email']) && !empty($invoice['customer_id'])) {
            $c = (new Customer())->find((int)$invoice['customer_id']);
            if ($c) {
                $customer = ['name' => $c['name'], 'email' => $c['email'], 'phone' => $c['phone']];
            }
        }
        if (empty($customer['email'])) {
            if (!empty($payment['customer_email'])) {
                $customer = ['name' => $payment['customer_name'] ?? 'Customer', 'email' => $payment['customer_email'], 'phone' => $payment['customer_phone'] ?? ''];
            } elseif (!empty($payment['email'])) {
                $customer = ['name' => $payment['name'] ?? 'Customer', 'email' => $payment['email'], 'phone' => $payment['phone'] ?? ''];
            }
        }

        $email = trim((string)($customer['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $amountFormatted = format_currency($payment['amount'] ?? 0);
        $subject = "Payment Receipt: " . ($payment['payment_code'] ?? ($payment['receipt_number'] ?? 'Confirmed')) . " — {$company}";

        $html = $this->renderTemplate('payment/successful', [
            'payment'     => $payment,
            'invoice'     => $invoice,
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Thank you! We received your payment of {$amountFormatted}.",
            'category'    => 'PAYMENT RECEIPT',
            'heading'     => 'Payment Received Successfully',
            'subheading'  => "Thank you! Your payment of {$amountFormatted} has been credited.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'payment_successful', 'payment', (int)($payment['id'] ?? 0));
    }

    // Alias for backward compatibility
    public function sendPaymentSuccessful(array $payment, ?array $invoice = null, ?array $booking = null): array
    {
        return $this->sendPaymentReceipt($payment, $invoice, $booking);
    }

    /**
     * 11. Invoice Notification
     */
    public function sendInvoiceNotification(array $invoice, ?array $booking = null, ?array $customer = null): array
    {
        $company = CompanySettingsService::getCompanyName();

        if (!$booking && !empty($invoice['booking_id'])) {
            $booking = (new Booking())->find((int)$invoice['booking_id']);
        }

        if (!$customer && $booking) {
            $customer = $this->resolveCustomerDetails($booking);
        } elseif (!$customer && !empty($invoice['customer_id'])) {
            $c = (new Customer())->find((int)$invoice['customer_id']);
            if ($c) {
                $customer = ['name' => $c['name'], 'email' => $c['email'], 'phone' => $c['phone']];
            }
        }
        if (!$customer || empty($customer['email'])) {
            if (!empty($invoice['customer_email'])) {
                $customer = ['name' => $invoice['customer_name'] ?? 'Customer', 'email' => $invoice['customer_email'], 'phone' => $invoice['customer_phone'] ?? ''];
            } elseif (!empty($invoice['email'])) {
                $customer = ['name' => $invoice['name'] ?? 'Customer', 'email' => $invoice['email'], 'phone' => $invoice['phone'] ?? ''];
            }
        }

        $email = trim((string)($customer['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Tax Invoice: {$invoice['invoice_number']} — {$company}";
        $html = $this->renderTemplate('finance/invoice-notification', [
            'invoice'     => $invoice,
            'booking'     => $booking,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your tax invoice {$invoice['invoice_number']} from {$company} is available.",
            'category'    => 'TAX INVOICE',
            'heading'     => "Tax Invoice #{$invoice['invoice_number']}",
            'subheading'  => 'Here is the itemized tax invoice copy for your reservation.',
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'invoice_notification', 'invoice', (int)($invoice['id'] ?? 0));
    }

    /**
     * 12. Refund Notification
     */
    public function sendRefundNotification(array $refund, ?array $invoice = null, ?array $customer = null): array
    {
        $company = CompanySettingsService::getCompanyName();

        if (!$invoice && !empty($refund['invoice_id'])) {
            $invoice = (new Invoice())->find((int)$refund['invoice_id']);
        }

        if (!$customer && !empty($invoice['customer_id'])) {
            $c = (new Customer())->find((int)$invoice['customer_id']);
            if ($c) {
                $customer = ['name' => $c['name'], 'email' => $c['email'], 'phone' => $c['phone']];
            }
        } elseif (!$customer && !empty($refund['booking_id'])) {
            $b = (new Booking())->find((int)$refund['booking_id']);
            if ($b) {
                $customer = $this->resolveCustomerDetails($b);
            }
        }
        if (!$customer || empty($customer['email'])) {
            if (!empty($refund['customer_email'])) {
                $customer = ['name' => $refund['customer_name'] ?? 'Customer', 'email' => $refund['customer_email'], 'phone' => $refund['customer_phone'] ?? ''];
            } elseif (!empty($refund['email'])) {
                $customer = ['name' => $refund['name'] ?? 'Customer', 'email' => $refund['email'], 'phone' => $refund['phone'] ?? ''];
            }
        }

        $email = trim((string)($customer['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $amountFormatted = format_currency($refund['amount'] ?? 0);
        $subject = "Refund Processed: {$refund['refund_number']} — {$company}";

        $html = $this->renderTemplate('finance/refund-confirmation', [
            'refund'      => $refund,
            'invoice'     => $invoice,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your refund of {$amountFormatted} for {$refund['refund_number']} has been processed.",
            'category'    => 'REFUND PROCESSED',
            'heading'     => 'Refund Confirmation',
            'subheading'  => "Your refund of {$amountFormatted} has been issued successfully.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'refund_processed', 'refund', (int)($refund['id'] ?? 0));
    }

    /**
     * 13. Upcoming Trip Reminder
     */
    public function sendTripReminder(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Trip Reminder: Tomorrow's Travel — {$company}";
        $html = $this->renderTemplate('trip/reminder', [
            'dispatch'    => $dispatch,
            'booking'     => $booking,
            'vehicle'     => $vehicle,
            'driver'      => $driverProfile,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Reminder for your upcoming trip #{$booking['booking_code']} tomorrow.",
            'category'    => 'TRIP REMINDER',
            'heading'     => "Ready for Tomorrow's Trip?",
            'subheading'  => "Here is a quick reminder of your itinerary details for booking #{$booking['booking_code']}.",
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'trip_reminder', 'dispatch', (int)$dispatch['id'], [], [], 60);
    }

    /**
     * 14. Trip Started Notification
     */
    public function sendTripStarted(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Trip Underway: {$booking['booking_code']} — {$company}";
        $html = $this->renderTemplate('trip/started', [
            'dispatch'    => $dispatch,
            'booking'     => $booking,
            'vehicle'     => $vehicle,
            'driver'      => $driverProfile,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "Your chauffeur has commenced the trip. Have a safe journey!",
            'category'    => 'LIVE TRIP STATUS',
            'heading'     => 'Your Trip is Underway',
            'subheading'  => 'Chauffeur has started the journey. Live tracking is active.',
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'trip_started', 'dispatch', (int)$dispatch['id']);
    }

    /**
     * 15. Trip Completed Notification
     */
    public function sendTripCompleted(array $dispatch, array $booking, array $vehicle, array $driverProfile): array
    {
        $company = CompanySettingsService::getCompanyName();
        $customer = $this->resolveCustomerDetails($booking);
        $email = trim((string)($customer['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Customer has no valid email address.'];
        }

        $subject = "Trip Completed: Thank You for Choosing {$company}";
        $html = $this->renderTemplate('trip/completed', [
            'dispatch'    => $dispatch,
            'booking'     => $booking,
            'vehicle'     => $vehicle,
            'driver'      => $driverProfile,
            'customer'    => $customer,
            'subject'     => $subject,
            'previewText' => "We hope you enjoyed your journey. Here is your trip summary.",
            'category'    => 'TRIP SUMMARY',
            'heading'     => 'Trip Completed Successfully',
            'subheading'  => 'Thank you for traveling with us. We hope you had a pleasant experience.',
        ]);

        return $this->send($email, $customer['name'] ?? null, $subject, $html, null, 'trip_completed', 'dispatch', (int)$dispatch['id']);
    }

    /**
     * 16. Diagnostic Test Email
     */
    public function sendTestEmail(string $recipientEmail): array
    {
        $recipientEmail = trim($recipientEmail);
        $cfg = EmailSettingsService::getTransportConfig();
        $company = CompanySettingsService::getCompanyName();

        $subject = "SMTP Diagnostic Test — {$company}";
        $html = $this->renderTemplate('test', [
            'recipientEmail' => $recipientEmail,
            'smtpHost'       => $cfg['host'],
            'smtpPort'       => $cfg['port'],
            'sender'         => $cfg['from_address'],
            'subject'        => $subject,
            'previewText'    => "This is a diagnostic SMTP test email dispatched from {$company}.",
            'category'       => 'SYSTEM DIAGNOSTICS',
            'heading'        => 'SMTP Diagnostic Test Successful',
            'subheading'     => 'Your mail transport connection is operational and delivering responsive HTML messages.',
        ]);

        return $this->send($recipientEmail, null, $subject, $html, null, 'test', 'system_diagnostic', null, [], [], 0);
    }

    // =========================================================================
    // BACKWARD-COMPATIBLE GRACEFUL QUEUE METHOD FALLBACKS
    // Ensures any legacy call to queue*() triggers direct delivery cleanly
    // =========================================================================

    public function queue(string $toEmail, ?string $toName, string $subject, string $htmlBody, ?string $textBody = null, string $emailType = 'general', ?string $referenceType = null, ?int $referenceId = null, array $cc = [], array $bcc = [], int $cooldownMinutes = 0): ?int
    {
        $res = $this->send($toEmail, $toName, $subject, $htmlBody, $textBody, $emailType, $referenceType, $referenceId, $cc, $bcc, $cooldownMinutes);
        return $res['log_id'] ?? null;
    }

    public function queueCustomerWelcome(array $customer): ?int
    {
        $res = $this->sendCustomerWelcome($customer);
        return $res['log_id'] ?? null;
    }

    public function queueEmployeeWelcome(array $employee): ?int
    {
        $res = $this->sendEmployeeWelcome($employee);
        return $res['log_id'] ?? null;
    }

    public function queueBookingCreated(array $booking): ?int
    {
        $res = $this->sendBookingCreated($booking);
        return $res['log_id'] ?? null;
    }

    public function queueBookingConfirmed(array $booking): ?int
    {
        $res = $this->sendBookingConfirmed($booking);
        return $res['log_id'] ?? null;
    }

    public function queueBookingCancelled(array $booking, ?string $reason = null): ?int
    {
        $res = $this->sendBookingCancelled($booking, $reason);
        return $res['log_id'] ?? null;
    }

    public function queuePaymentSuccessful(array $payment, ?array $invoice = null, ?array $booking = null): ?int
    {
        $res = $this->sendPaymentSuccessful($payment, $invoice, $booking);
        return $res['log_id'] ?? null;
    }

    public function queueDriverAssigned(array $dispatch, array $booking, array $vehicle, array $driverProfile): ?int
    {
        $res = $this->sendDriverAssignment($dispatch, $booking, $vehicle, $driverProfile);
        return $res['driver']['log_id'] ?? ($res['customer']['log_id'] ?? null);
    }

    public function queueTripStarted(array $dispatch, array $booking, array $vehicle, array $driverProfile): ?int
    {
        $res = $this->sendTripStarted($dispatch, $booking, $vehicle, $driverProfile);
        return $res['log_id'] ?? null;
    }

    public function queueTripCompleted(array $dispatch, array $booking, array $vehicle, array $driverProfile): ?int
    {
        $res = $this->sendTripCompleted($dispatch, $booking, $vehicle, $driverProfile);
        return $res['log_id'] ?? null;
    }

    // =========================================================================
    // INTERNAL RECIPIENT RESOLVERS
    // =========================================================================

    private function resolveCustomerDetails(array $booking): array
    {
        $customer = [
            'name'  => $booking['customer_name'] ?? 'Valued Customer',
            'email' => $booking['customer_email'] ?? '',
            'phone' => $booking['customer_phone'] ?? '',
        ];

        if ((empty($customer['email']) || str_ends_with(strtolower($customer['email']), '@internal.local')) && !empty($booking['customer_profile_id'])) {
            $custModel = new Customer();
            $custRow = $custModel->find((int)$booking['customer_profile_id']);
            if ($custRow && !empty($custRow['email']) && !str_ends_with(strtolower($custRow['email']), '@internal.local')) {
                $customer['name'] = $custRow['name'] ?? $customer['name'];
                $customer['email'] = $custRow['email'];
                $customer['phone'] = $custRow['phone'] ?? $customer['phone'];
            }
        }

        return $customer;
    }

    private function resolveDriverUserDetails(array $driverProfile): array
    {
        $driver = [
            'name'  => $driverProfile['name'] ?? 'Driver',
            'email' => $driverProfile['email'] ?? '',
            'phone' => $driverProfile['phone'] ?? '',
        ];

        if (!empty($driverProfile['user_id'])) {
            $user = (new \App\Models\User())->find((int)$driverProfile['user_id']);
            if ($user) {
                $driver['name'] = $user['name'] ?? $driver['name'];
                $driver['email'] = $user['email'] ?? $driver['email'];
                $driver['phone'] = $user['phone'] ?? $driver['phone'];
            }
        }

        if (empty($driver['email']) && !empty($driverProfile['employee_id'])) {
            $emp = (new Employee())->find((int)$driverProfile['employee_id']);
            if ($emp && !empty($emp['email'])) {
                $driver['name'] = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
                $driver['email'] = $emp['email'];
                $driver['phone'] = $emp['phone'] ?? $driver['phone'];
            }
        }

        return $driver;
    }
}
