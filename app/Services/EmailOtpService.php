<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Session;
use App\Core\RateLimiter;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use PDO;
use Throwable;

/**
 * Reusable Email OTP Verification Service
 * Handles secure 4-digit OTP generation, hashing, transmission, cooldown,
 * rate limiting, and verification for customer and employee registrations.
 */
class EmailOtpService
{
    public const FLOW_CUSTOMER = 'customer';
    public const FLOW_EMPLOYEE = 'employee';

    public const OTP_EXPIRY_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    /**
     * Send or resend a 4-digit verification code to the target email
     *
     * @return array ['success' => bool, 'message' => string, 'cooldown' => int, 'error' => ?string]
     */
    public function sendOtp(string $email, string $flow, ?string $recipientName = null, ?string $ip = null): array
    {
        $email = strtolower(trim($email));
        $flow = strtolower(trim($flow));

        // 1. Validate Flow
        if (!in_array($flow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            return [
                'success'  => false,
                'message'  => 'Invalid registration flow specified.',
                'cooldown' => 0,
                'error'    => 'Invalid registration flow.'
            ];
        }

        // 2. Validate Email Format
        if (empty($email)) {
            return [
                'success'  => false,
                'message'  => 'Email address is required.',
                'cooldown' => 0,
                'error'    => 'Email address is required.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success'  => false,
                'message'  => 'Please provide a valid email address format.',
                'cooldown' => 0,
                'error'    => 'Invalid email address.'
            ];
        }

        // 3. Reject non-routable internal local domains
        if (str_ends_with($email, '@internal.local') || str_ends_with($email, '@voyagego.internal')) {
            return [
                'success'  => false,
                'message'  => 'Internal domains cannot receive verification emails.',
                'cooldown' => 0,
                'error'    => 'Internal domain not supported.'
            ];
        }

        // 4. Duplicate Email Business Rules Check
        $duplicateError = $this->checkDuplicateEmail($email, $flow);
        if ($duplicateError !== null) {
            return [
                'success'  => false,
                'message'  => $duplicateError,
                'cooldown' => 0,
                'error'    => $duplicateError
            ];
        }

        $pdo = Database::getConnection();

        // 5. Check Resend Cooldown (60 Seconds)
        $stmt = $pdo->prepare("SELECT id, resend_available_at, TIMESTAMPDIFF(SECOND, NOW(), resend_available_at) as remaining_seconds
                               FROM `email_verifications`
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' AND `resend_available_at` > NOW()
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $flow]);
        $activeCooldown = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($activeCooldown && (int)$activeCooldown['remaining_seconds'] > 0) {
            $rem = (int)$activeCooldown['remaining_seconds'];
            return [
                'success'  => false,
                'message'  => "Please wait {$rem} second(s) before requesting a new code.",
                'cooldown' => $rem,
                'error'    => 'Cooldown active.'
            ];
        }

        // 6. Rate Limiting Protection (Max 6 requests per IP/Email within 10 minutes)
        $clientIp = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $rateLimitKey = "email_otp_send:" . md5("{$clientIp}_{$email}_{$flow}");
        if (RateLimiter::tooManyAttempts($rateLimitKey, 6)) {
            $waitSec = RateLimiter::availableIn($rateLimitKey);
            return [
                'success'  => false,
                'message'  => "Too many OTP requests. Please wait {$waitSec} seconds before trying again.",
                'cooldown' => $waitSec,
                'error'    => 'Rate limit exceeded.'
            ];
        }
        RateLimiter::hit($rateLimitKey, 600);

        // 7. Invalidate any existing pending/verified OTPs for this flow and email
        $invalStmt = $pdo->prepare("UPDATE `email_verifications` 
                                    SET `status` = 'invalidated' 
                                    WHERE `email` = ? AND `flow` = ? AND `status` IN ('pending', 'verified')");
        $invalStmt->execute([$email, $flow]);

        // Also clear existing session verification for this flow
        Session::remove("email_verified_{$flow}");

        // 8. Generate Secure 4-Digit OTP using PHP random_int()
        $rawOtp = sprintf('%04d', random_int(1000, 9999));
        $otpHash = password_hash($rawOtp, PASSWORD_BCRYPT);
        $verificationToken = bin2hex(random_bytes(32));

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));
        $resendAvailableAt = date('Y-m-d H:i:s', strtotime('+' . self::RESEND_COOLDOWN_SECONDS . ' seconds'));

        // 9. Store record in database
        $insertStmt = $pdo->prepare("INSERT INTO `email_verifications` 
            (`email`, `flow`, `otp_hash`, `attempts`, `max_attempts`, `resend_available_at`, `expires_at`, `status`, `verification_token`, `ip_address`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, 0, ?, ?, ?, 'pending', ?, ?, NOW(), NOW())");
        $insertStmt->execute([
            $email,
            $flow,
            $otpHash,
            self::MAX_ATTEMPTS,
            $resendAvailableAt,
            $expiresAt,
            $verificationToken,
            $clientIp
        ]);
        $verificationId = (int)$pdo->lastInsertId();

        // 10. Send Professional OTP Email via existing EmailService
        $companyName = CompanySettingsService::getCompanyName();
        $flowLabel = $flow === self::FLOW_EMPLOYEE ? 'Employee Registration' : 'Customer Registration';
        $greetingLead = !empty($recipientName)
            ? "Hello " . htmlspecialchars($recipientName) . ", please use the 4-digit verification code below to complete your {$companyName} {$flowLabel}."
            : "Please use the 4-digit verification code below to verify your email address for your {$companyName} {$flowLabel}.";

        $subject = "Your Verification Code: {$rawOtp} — {$companyName}";

        try {
            $htmlBody = $this->emailService->renderTemplate('auth/otp', [
                'subject'        => $subject,
                'previewText'    => "Your 4-digit verification code is {$rawOtp}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.",
                'category'       => 'EMAIL VERIFICATION',
                'heading'        => 'Verify your email address',
                'subheading'     => "Enter the 4-digit verification code sent to {$email} to proceed.",
                'recipientName'  => $recipientName,
                'recipientEmail' => $email,
                'greetingLead'   => $greetingLead,
                'otp'            => $rawOtp,
                'flow'           => $flow,
            ]);

            $textBody = "Your 4-digit verification code for {$companyName} ({$flowLabel}) is: {$rawOtp}\n\nThis code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity note: Do not share this code with anyone.";

            // Cooldown 0 so PHPMailer sends fresh code each time
            $dispatchResult = $this->emailService->send(
                $email,
                $recipientName,
                $subject,
                $htmlBody,
                $textBody,
                'email_verification_otp',
                'email_verification',
                $verificationId,
                [],
                [],
                0
            );

            if (empty($dispatchResult['success'])) {
                // Invalidate verification record on transmission failure
                $pdo->prepare("UPDATE `email_verifications` SET `status` = 'invalidated' WHERE `id` = ?")->execute([$verificationId]);
                $cleanError = $dispatchResult['error'] ?? 'SMTP delivery failure.';
                Logger::error("EmailOtpService: Failed to dispatch OTP to {$email}: {$cleanError}");

                return [
                    'success'  => false,
                    'message'  => 'Failed to deliver verification code to your email. Please verify your email address and try again.',
                    'cooldown' => 0,
                    'error'    => $cleanError
                ];
            }
        } catch (Throwable $e) {
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'invalidated' WHERE `id` = ?")->execute([$verificationId]);
            Logger::error("EmailOtpService: Exception dispatching OTP to {$email}: " . $e->getMessage());

            return [
                'success'  => false,
                'message'  => 'Email delivery system error. Please try again shortly.',
                'cooldown' => 0,
                'error'    => $e->getMessage()
            ];
        }

        // Run opportunistic background cleanup of old expired records
        $this->cleanupExpiredRecords();

        return [
            'success'  => true,
            'message'  => "A 4-digit verification code has been sent to {$email}.",
            'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            'error'    => null
        ];
    }

    /**
     * Verify the 4-digit OTP provided by user
     *
     * @return array ['success' => bool, 'message' => string, 'token' => ?string, 'error' => ?string]
     */
    public function verifyOtp(string $email, string $flow, string $otp): array
    {
        $email = strtolower(trim($email));
        $flow = strtolower(trim($flow));
        $otp = trim($otp);

        // 1. Validate Input
        if (!preg_match('/^\d{4}$/', $otp)) {
            return [
                'success' => false,
                'message' => 'Please enter a valid 4-digit numeric verification code.',
                'token'   => null,
                'error'   => 'Invalid code format.'
            ];
        }

        $pdo = Database::getConnection();

        // 2. Fetch Active Pending Record
        $stmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' 
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $flow]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return [
                'success' => false,
                'message' => 'No active verification request found. Please request a new verification code.',
                'token'   => null,
                'error'   => 'Record not found.'
            ];
        }

        // 3. Check Expiry (5 Minutes)
        if (strtotime($record['expires_at']) < time()) {
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'invalidated' WHERE `id` = ?")->execute([$record['id']]);
            return [
                'success' => false,
                'message' => 'The verification code has expired. Please request a new code.',
                'token'   => null,
                'error'   => 'Code expired.'
            ];
        }

        // 4. Check Maximum Attempts (5 Wrong Attempts)
        if ((int)$record['attempts'] >= (int)$record['max_attempts']) {
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'invalidated' WHERE `id` = ?")->execute([$record['id']]);
            return [
                'success' => false,
                'message' => 'Maximum verification attempts exceeded. Please request a new verification code.',
                'token'   => null,
                'error'   => 'Max attempts reached.'
            ];
        }

        // 5. Verify Hash Timing-Safe
        if (!password_verify($otp, $record['otp_hash'])) {
            $newAttempts = (int)$record['attempts'] + 1;
            $remaining = (int)$record['max_attempts'] - $newAttempts;

            if ($remaining <= 0) {
                $pdo->prepare("UPDATE `email_verifications` SET `attempts` = ?, `status` = 'invalidated' WHERE `id` = ?")
                    ->execute([$newAttempts, $record['id']]);

                return [
                    'success' => false,
                    'message' => 'Incorrect verification code. Maximum attempts exceeded. Please request a new code.',
                    'token'   => null,
                    'error'   => 'Max attempts exceeded.'
                ];
            }

            $pdo->prepare("UPDATE `email_verifications` SET `attempts` = ? WHERE `id` = ?")
                ->execute([$newAttempts, $record['id']]);

            return [
                'success' => false,
                'message' => "Incorrect verification code. {$remaining} attempt(s) remaining.",
                'token'   => null,
                'error'   => 'Incorrect code.'
            ];
        }

        // 6. Successful Verification: Mark Verified
        $pdo->prepare("UPDATE `email_verifications` 
                       SET `status` = 'verified', `verified_at` = NOW() 
                       WHERE `id` = ?")
            ->execute([$record['id']]);

        // Store verification token in session
        Session::set("email_verified_{$flow}", [
            'email'       => $email,
            'token'       => $record['verification_token'],
            'verified_at' => time()
        ]);

        Logger::info("EmailOtpService: Verified {$flow} email successfully: {$email}");

        return [
            'success' => true,
            'message' => 'Email address verified successfully!',
            'token'   => $record['verification_token'],
            'error'   => null
        ];
    }

    /**
     * Check if an email is successfully verified and eligible for form save
     */
    public function isEmailVerified(string $email, string $flow, ?string $token = null): bool
    {
        $email = strtolower(trim($email));
        $flow = strtolower(trim($flow));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // 1. Session verification check (Fast & secure in-memory check)
        $sessionData = Session::get("email_verified_{$flow}");
        if (empty($sessionData) || ($sessionData['email'] ?? '') !== $email) {
            // If session is absent, continue to database check below
        }

        // 2. Database verification check (Authoritative record)
        $pdo = Database::getConnection();
        // Allow a 45-minute window after verification for staff to finish filling the rest of the form
        $sql = "SELECT id, verification_token, verified_at, expires_at 
                FROM `email_verifications` 
                WHERE `email` = ? AND `flow` = ? AND `status` = 'verified' 
                  AND `verified_at` >= DATE_SUB(NOW(), INTERVAL 45 MINUTE)
                ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $flow]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return false;
        }

        // If client provided a verification token, verify it matches
        if (!empty($token)) {
            if (!hash_equals((string)$record['verification_token'], (string)$token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Consume the verification upon successful customer or employee creation (One-time use)
     */
    public function consumeVerification(string $email, string $flow): bool
    {
        $email = strtolower(trim($email));
        $flow = strtolower(trim($flow));

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `email_verifications` 
                               SET `status` = 'consumed', `consumed_at` = NOW() 
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'verified'");
        $stmt->execute([$email, $flow]);

        Session::remove("email_verified_{$flow}");
        return true;
    }

    /**
     * Explicitly invalidate verification (e.g. user modified email in the input)
     */
    public function invalidateVerification(string $email, string $flow): void
    {
        $email = strtolower(trim($email));
        $flow = strtolower(trim($flow));

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `email_verifications` 
                               SET `status` = 'invalidated' 
                               WHERE `email` = ? AND `flow` = ? AND `status` IN ('pending', 'verified')");
        $stmt->execute([$email, $flow]);

        Session::remove("email_verified_{$flow}");
    }

    /**
     * Check if an email is already associated with an existing record according to project business rules
     */
    public function checkDuplicateEmail(string $email, string $flow): ?string
    {
        $email = strtolower(trim($email));

        if ($flow === self::FLOW_CUSTOMER) {
            $customerModel = new Customer();
            $existing = $customerModel->findBy('email', $email);
            if ($existing) {
                return "A customer record with the email '{$email}' already exists in the system.";
            }
        } elseif ($flow === self::FLOW_EMPLOYEE) {
            $employeeModel = new Employee();
            $existingEmp = $employeeModel->findBy('email', $email);
            if ($existingEmp) {
                return "An employee with the email '{$email}' is already registered.";
            }

            $userModel = new User();
            $existingUser = $userModel->findBy('email', $email);
            if ($existingUser) {
                return "A user account with the email '{$email}' already exists.";
            }
        }

        return null;
    }

    /**
     * Clean up expired or consumed verification records older than 24 hours
     */
    public function cleanupExpiredRecords(): void
    {
        try {
            $pdo = Database::getConnection();
            $pdo->exec("DELETE FROM `email_verifications` 
                        WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                          AND `status` IN ('consumed', 'invalidated')");

            $pdo->exec("UPDATE `email_verifications` 
                        SET `status` = 'invalidated' 
                        WHERE `expires_at` < NOW() AND `status` = 'pending'");
        } catch (Throwable $e) {
            // Silent cleanup error to not interrupt flow
        }
    }
}

