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
 * Reusable Centralized Email OTP Verification Service
 * Handles secure 4-digit OTP generation, hashing, transmission, cooldown,
 * rate limiting, and verification for customer and employee registrations,
 * as well as account recovery (forgot username & forgot password).
 */
class EmailOtpService
{
    public const FLOW_CUSTOMER = 'customer';
    public const FLOW_EMPLOYEE = 'employee';
    public const FLOW_RECOVERY_USERNAME = 'recovery_username';
    public const FLOW_RECOVERY_PASSWORD = 'recovery_password';

    public const OTP_EXPIRY_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;
    public const AUTH_TOKEN_EXPIRY_MINUTES = 10;

    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    /**
     * Normalize flow or purpose to canonical supported constants.
     * Returns null if flow is unrecognized/unsupported.
     */
    public static function normalizeFlow(string $flow): ?string
    {
        $flow = strtolower(trim($flow));
        $canonicalMap = [
            'customer'              => self::FLOW_CUSTOMER,
            'customer_registration' => self::FLOW_CUSTOMER,
            'employee'              => self::FLOW_EMPLOYEE,
            'employee_registration' => self::FLOW_EMPLOYEE,
            'recovery_username'     => self::FLOW_RECOVERY_USERNAME,
            'forgot_username'       => self::FLOW_RECOVERY_USERNAME,
            'username'              => self::FLOW_RECOVERY_USERNAME,
            'recovery_password'     => self::FLOW_RECOVERY_PASSWORD,
            'forgot_password'       => self::FLOW_RECOVERY_PASSWORD,
            'password'              => self::FLOW_RECOVERY_PASSWORD,
        ];

        return $canonicalMap[$flow] ?? null;
    }

    /**
     * Send or resend a 4-digit verification code to the target email
     *
     * @return array ['success' => bool, 'message' => string, 'cooldown' => int, 'error' => ?string]
     */
    public function sendOtp(string $email, string $flow, ?string $recipientName = null, ?string $ip = null): array
    {
        $email = strtolower(trim($email));
        $originalFlow = trim($flow);
        $canonicalFlow = self::normalizeFlow($originalFlow);

        // 1. Validate Flow / Purpose
        if ($canonicalFlow === null) {
            return [
                'success'  => false,
                'message'  => "Unsupported OTP verification purpose: '{$originalFlow}'.",
                'cooldown' => 0,
                'error'    => 'Unsupported OTP purpose'
            ];
        }

        // 2. Validate Email Format
        if (empty($email)) {
            $msg = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
                ? 'Please enter your registered email address.'
                : 'Email address is required.';
            return [
                'success'  => false,
                'message'  => $msg,
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

        // 4. Duplicate Email / Account Verification Business Rules
        $targetUser = null;
        if ($canonicalFlow === self::FLOW_CUSTOMER) {
            $customerModel = new Customer();
            $existing = $customerModel->findBy('email', $email);
            if ($existing) {
                return [
                    'success'  => false,
                    'message'  => "A customer record with the email '{$email}' already exists in the system.",
                    'cooldown' => 0,
                    'error'    => "A customer record with the email '{$email}' already exists in the system."
                ];
            }
        } elseif ($canonicalFlow === self::FLOW_EMPLOYEE) {
            $employeeModel = new Employee();
            $existingEmp = $employeeModel->findBy('email', $email);
            if ($existingEmp) {
                return [
                    'success'  => false,
                    'message'  => "An employee with the email '{$email}' is already registered.",
                    'cooldown' => 0,
                    'error'    => "An employee with the email '{$email}' is already registered."
                ];
            }

            $userModel = new User();
            $existingUser = $userModel->findBy('email', $email);
            if ($existingUser) {
                return [
                    'success'  => false,
                    'message'  => "A user account with the email '{$email}' already exists.",
                    'cooldown' => 0,
                    'error'    => "A user account with the email '{$email}' already exists."
                ];
            }
        } elseif ($canonicalFlow === self::FLOW_RECOVERY_USERNAME || $canonicalFlow === self::FLOW_RECOVERY_PASSWORD) {
            $pdo = Database::getConnection();
            $userStmt = $pdo->prepare("SELECT id, name, username, email, role, status 
                                       FROM `users` 
                                       WHERE LOWER(TRIM(`email`)) = ?");
            $userStmt->execute([$email]);
            $matchingUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($matchingUsers)) {
                Logger::info("EmailOtpService: Recovery attempt for unregistered email: {$email} [Purpose: {$canonicalFlow}]");
                return [
                    'success'  => false,
                    'message'  => "We couldn't find an account associated with this email address. Please check your email and try again.",
                    'cooldown' => 0,
                    'error'    => 'Email Address Not Registered'
                ];
            }

            if (count($matchingUsers) > 1) {
                Logger::warning("EmailOtpService: Ambiguous recovery stopped: multiple accounts share email '{$email}'");
                return [
                    'success'  => false,
                    'message'  => "We couldn't verify a unique account for this email. Please contact the administrator.",
                    'cooldown' => 0,
                    'error'    => 'Duplicate account email'
                ];
            }

            $targetUser = $matchingUsers[0];

            if (empty($targetUser['email']) || !filter_var($targetUser['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success'  => false,
                    'message'  => "We couldn't find an account associated with this email address. Please check your email and try again.",
                    'cooldown' => 0,
                    'error'    => 'Invalid account email'
                ];
            }

            if ($targetUser['status'] === 'inactive') {
                Logger::warning("EmailOtpService: Recovery attempt stopped for inactive account [User ID: {$targetUser['id']}]");
                return [
                    'success'  => false,
                    'message'  => "Your account is currently inactive. Please contact the administrator for assistance.",
                    'cooldown' => 0,
                    'error'    => 'Account inactive'
                ];
            }

            if ($targetUser['status'] === 'suspended') {
                Logger::warning("EmailOtpService: Recovery attempt stopped for suspended account [User ID: {$targetUser['id']}]");
                return [
                    'success'  => false,
                    'message'  => "Your account is currently unavailable. Please contact the administrator.",
                    'cooldown' => 0,
                    'error'    => 'Account suspended'
                ];
            }

            if ($targetUser['status'] !== 'active') {
                Logger::warning("EmailOtpService: Recovery attempt stopped for non-active account [Status: {$targetUser['status']}, User ID: {$targetUser['id']}]");
                return [
                    'success'  => false,
                    'message'  => "Your account is currently unavailable. Please contact the administrator.",
                    'cooldown' => 0,
                    'error'    => 'Account unavailable'
                ];
            }

            if (empty($recipientName)) {
                $recipientName = !empty($targetUser['name']) ? $targetUser['name'] : $targetUser['username'];
            }
        }

        $pdo = Database::getConnection();

        // 5. Check Resend Cooldown (60 Seconds) across active pending requests
        $stmt = $pdo->prepare("SELECT id, resend_available_at, TIMESTAMPDIFF(SECOND, NOW(), resend_available_at) as remaining_seconds
                               FROM `email_verifications`
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' AND `resend_available_at` > NOW()
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $canonicalFlow]);
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

        // 6. Rate Limiting Protection (Max 5 requests per IP/Email within 10 minutes)
        $clientIp = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $rateLimitPrefix = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
            ? 'recovery_otp_send:'
            : 'email_otp_send:';
        $rateLimitKey = $rateLimitPrefix . md5("{$clientIp}_{$email}_{$canonicalFlow}");
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $waitSec = RateLimiter::availableIn($rateLimitKey);
            return [
                'success'  => false,
                'message'  => "Too many OTP requests. Please wait {$waitSec} seconds before trying again.",
                'cooldown' => $waitSec,
                'error'    => 'Rate limit exceeded.'
            ];
        }
        RateLimiter::hit($rateLimitKey, 600);

        // 7. Invalidate any existing draft/pending/verified OTPs for this flow and email
        $invalStmt = $pdo->prepare("UPDATE `email_verifications` 
                                    SET `status` = 'invalidated' 
                                    WHERE `email` = ? AND `flow` = ? AND `status` IN ('draft', 'pending', 'verified')");
        $invalStmt->execute([$email, $canonicalFlow]);

        // Clear existing session verification for this flow
        if (in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            Session::remove("email_verified_{$canonicalFlow}");
        } else {
            Session::remove("recovery_auth_{$canonicalFlow}");
        }

        // 8. Generate Secure 4-Digit OTP using PHP random_int()
        $rawOtp = sprintf('%04d', random_int(1000, 9999));
        $otpHash = password_hash($rawOtp, PASSWORD_BCRYPT);
        $verificationToken = bin2hex(random_bytes(32));

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));
        $resendAvailableAt = date('Y-m-d H:i:s', strtotime('+' . self::RESEND_COOLDOWN_SECONDS . ' seconds'));
        $userId = $targetUser ? (int)$targetUser['id'] : null;

        // 9. Store record in database with 'draft' status (promoted to 'pending' ONLY after confirmed SMTP dispatch)
        $insertStmt = $pdo->prepare("INSERT INTO `email_verifications` 
            (`email`, `flow`, `user_id`, `otp_hash`, `attempts`, `max_attempts`, `resend_available_at`, `expires_at`, `status`, `verification_token`, `ip_address`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, 0, ?, ?, ?, 'draft', ?, ?, NOW(), NOW())");
        $insertStmt->execute([
            $email,
            $canonicalFlow,
            $userId,
            $otpHash,
            self::MAX_ATTEMPTS,
            $resendAvailableAt,
            $expiresAt,
            $verificationToken,
            $clientIp
        ]);
        $verificationId = (int)$pdo->lastInsertId();

        // 10. Template Selection & Content Formulation
        $companyName = CompanySettingsService::getCompanyName();

        if ($canonicalFlow === self::FLOW_CUSTOMER || $canonicalFlow === self::FLOW_EMPLOYEE) {
            $flowLabel = $canonicalFlow === self::FLOW_EMPLOYEE ? 'Employee Registration' : 'Customer Registration';
            $greetingLead = !empty($recipientName)
                ? "Hello " . htmlspecialchars($recipientName) . ", please use the 4-digit verification code below to complete your {$companyName} {$flowLabel}."
                : "Please use the 4-digit verification code below to verify your email address for your {$companyName} {$flowLabel}.";

            $subject = "Your Verification Code: {$rawOtp} — {$companyName}";
            $templateName = 'auth/otp';
            $category = 'EMAIL VERIFICATION';
            $heading = 'Verify your email address';
            $subheading = "Enter the 4-digit verification code sent to {$email} to proceed.";
            $emailType = 'email_verification_otp';
            $textBody = "Your 4-digit verification code for {$companyName} ({$flowLabel}) is: {$rawOtp}\n\nThis code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity note: Do not share this code with anyone.";
        } elseif ($canonicalFlow === self::FLOW_RECOVERY_USERNAME) {
            $subject = "Your account username recovery code";
            $templateName = 'auth/recovery-username-otp';
            $category = 'ACCOUNT SECURITY';
            $heading = 'Recover your username';
            $subheading = "Use this 4-digit code to view your registered account username.";
            $greetingLead = "Hello " . htmlspecialchars($recipientName) . ", we received a request to recover your {$companyName} account username.";
            $emailType = 'account_recovery_otp';
            $textBody = "Your account username recovery code for {$companyName} is: {$rawOtp}\n\nThis verification code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity warning: Never share this code with anyone.\nIf you did not request this, please disregard this email.";
        } else { // FLOW_RECOVERY_PASSWORD
            $subject = "Your password reset verification code";
            $templateName = 'auth/recovery-password-otp';
            $category = 'ACCOUNT SECURITY';
            $heading = 'Reset your account password';
            $subheading = "Use this 4-digit code to securely verify your identity and set a new password.";
            $greetingLead = "Hello " . htmlspecialchars($recipientName) . ", we received a request to reset your password for your {$companyName} account.";
            $emailType = 'account_recovery_otp';
            $textBody = "Your password reset verification code for {$companyName} is: {$rawOtp}\n\nThis verification code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity warning: Never share this code with anyone. This code authorizes creating a new password.\nIf you did not request this, please disregard this email.";
        }

        try {
            $htmlBody = $this->emailService->renderTemplate($templateName, [
                'subject'        => $subject,
                'previewText'    => "Your 4-digit code is {$rawOtp}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.",
                'category'       => $category,
                'heading'        => $heading,
                'subheading'     => $subheading,
                'recipientName'  => $recipientName,
                'recipientEmail' => $email,
                'greetingLead'   => $greetingLead,
                'otp'            => $rawOtp,
                'flow'           => $canonicalFlow,
                'purpose'        => $canonicalFlow,
            ]);

            // Dispatch immediately via SMTP pipeline (cooldown 0 to ensure fresh code dispatch)
            $dispatchResult = $this->emailService->send(
                $email,
                $recipientName,
                $subject,
                $htmlBody,
                $textBody,
                $emailType,
                'email_verification',
                $verificationId,
                [],
                [],
                0
            );

            if (empty($dispatchResult['success']) || !empty($dispatchResult['duplicate'])) {
                // Invalidate verification record on transmission failure - never activate unusable OTP
                $pdo->prepare("UPDATE `email_verifications` SET `status` = 'failed', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);
                $cleanError = $dispatchResult['error'] ?? 'SMTP delivery failure.';
                Logger::error("EmailOtpService: Failed to dispatch OTP to {$email}: {$cleanError}");

                $failureMessage = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
                    ? "We couldn't send the verification code right now. Please try again shortly."
                    : "Failed to deliver verification code to your email. Please verify your email address and try again.";

                return [
                    'success'  => false,
                    'message'  => $failureMessage,
                    'cooldown' => 0,
                    'error'    => $cleanError
                ];
            }

            // Confirmed transmission: Promote status from 'draft' to 'pending'
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'pending', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);

        } catch (Throwable $e) {
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'failed', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);
            Logger::error("EmailOtpService: Exception dispatching OTP to {$email}: " . $e->getMessage());

            $failureMessage = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
                ? "We couldn't send the verification code right now. Please try again shortly."
                : "Email delivery system error. Please try again shortly.";

            return [
                'success'  => false,
                'message'  => $failureMessage,
                'cooldown' => 0,
                'error'    => $e->getMessage()
            ];
        }

        // Opportunistic cleanup of expired records
        $this->cleanupExpiredRecords();

        $successMessage = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
            ? "A 4-digit verification code has been sent to your registered email address."
            : "A 4-digit verification code has been sent to {$email}.";

        return [
            'success'  => true,
            'message'  => $successMessage,
            'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            'error'    => null
        ];
    }

    /**
     * Verify the 4-digit OTP provided by user
     *
     * @return array ['success' => bool, 'message' => string, 'token' => ?string, 'user_id' => ?int, 'error' => ?string]
     */
    public function verifyOtp(string $email, string $flow, string $otp, ?string $clientIp = null): array
    {
        $email = strtolower(trim($email));
        $canonicalFlow = self::normalizeFlow($flow);
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

        if ($canonicalFlow === null) {
            return [
                'success' => false,
                'message' => 'Invalid or unsupported verification flow specified.',
                'token'   => null,
                'error'   => 'Unsupported OTP purpose'
            ];
        }

        $pdo = Database::getConnection();

        // 2. Fetch Active Pending Record
        $stmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' 
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $canonicalFlow]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            $invalStmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                                        WHERE `email` = ? AND `flow` = ? 
                                        ORDER BY id DESC LIMIT 1");
            $invalStmt->execute([$email, $canonicalFlow]);
            $lastRecord = $invalStmt->fetch(PDO::FETCH_ASSOC);
            if ($lastRecord && (int)$lastRecord['attempts'] >= (int)$lastRecord['max_attempts']) {
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts exceeded. Please request a new verification code.',
                    'token'   => null,
                    'error'   => 'Max attempts reached.'
                ];
            }

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

        // 6. Successful Verification: Mark Verified & Update Expiry
        $token = !empty($record['verification_token']) ? $record['verification_token'] : bin2hex(random_bytes(32));
        $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+' . self::AUTH_TOKEN_EXPIRY_MINUTES . ' minutes'));

        $pdo->prepare("UPDATE `email_verifications` 
                       SET `status` = 'verified', 
                           `verified_at` = NOW(), 
                           `verification_token` = ?, 
                           `token_expires_at` = ? 
                       WHERE `id` = ?")
            ->execute([$token, $tokenExpiresAt, $record['id']]);

        // Session Binding
        if (in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            Session::set("email_verified_{$canonicalFlow}", [
                'email'       => $email,
                'token'       => $token,
                'verified_at' => time()
            ]);
        } else {
            Session::set("recovery_auth_{$canonicalFlow}", [
                'token'      => $token,
                'user_id'    => (int)($record['user_id'] ?? 0),
                'email'      => $email,
                'expires_at' => time() + (self::AUTH_TOKEN_EXPIRY_MINUTES * 60)
            ]);
        }

        Logger::info("EmailOtpService: Verified {$canonicalFlow} email successfully: {$email}");

        $successMsg = in_array($canonicalFlow, [self::FLOW_RECOVERY_USERNAME, self::FLOW_RECOVERY_PASSWORD], true)
            ? 'Email verification successful.'
            : 'Email address verified successfully!';

        return [
            'success' => true,
            'message' => $successMsg,
            'token'   => $token,
            'user_id' => !empty($record['user_id']) ? (int)$record['user_id'] : null,
            'error'   => null
        ];
    }

    /**
     * Check if an email is successfully verified and eligible for form save
     */
    public function isEmailVerified(string $email, string $flow, ?string $token = null): bool
    {
        $email = strtolower(trim($email));
        $canonicalFlow = self::normalizeFlow($flow);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || $canonicalFlow === null) {
            return false;
        }

        // 1. Session verification check
        if (in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            $sessionData = Session::get("email_verified_{$canonicalFlow}");
            if (!empty($sessionData) && ($sessionData['email'] ?? '') === $email) {
                if (empty($token) || hash_equals((string)($sessionData['token'] ?? ''), (string)$token)) {
                    return true;
                }
            }
        } else {
            $sessionData = Session::get("recovery_auth_{$canonicalFlow}");
            if (!empty($sessionData) && ($sessionData['email'] ?? '') === $email) {
                if (empty($token) || hash_equals((string)($sessionData['token'] ?? ''), (string)$token)) {
                    return true;
                }
            }
        }

        // 2. Database verification check (Authoritative record)
        $pdo = Database::getConnection();
        $intervalMinutes = in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true) ? 45 : 10;

        $sql = "SELECT id, verification_token, verified_at, expires_at 
                FROM `email_verifications` 
                WHERE `email` = ? AND `flow` = ? AND `status` = 'verified' 
                  AND `verified_at` >= DATE_SUB(NOW(), INTERVAL {$intervalMinutes} MINUTE)
                ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $canonicalFlow]);
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
     * Consume the verification upon successful form action (One-time use)
     */
    public function consumeVerification(string $email, string $flow): bool
    {
        $email = strtolower(trim($email));
        $canonicalFlow = self::normalizeFlow($flow) ?? $flow;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `email_verifications` 
                               SET `status` = 'consumed', `consumed_at` = NOW() 
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'verified'");
        $stmt->execute([$email, $canonicalFlow]);

        if (in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            Session::remove("email_verified_{$canonicalFlow}");
        } else {
            Session::remove("recovery_auth_{$canonicalFlow}");
        }
        return true;
    }

    /**
     * Explicitly invalidate verification (e.g. user modified email in the input)
     */
    public function invalidateVerification(string $email, string $flow): void
    {
        $email = strtolower(trim($email));
        $canonicalFlow = self::normalizeFlow($flow) ?? $flow;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `email_verifications` 
                               SET `status` = 'invalidated' 
                               WHERE `email` = ? AND `flow` = ? AND `status` IN ('draft', 'pending', 'verified')");
        $stmt->execute([$email, $canonicalFlow]);

        if (in_array($canonicalFlow, [self::FLOW_CUSTOMER, self::FLOW_EMPLOYEE], true)) {
            Session::remove("email_verified_{$canonicalFlow}");
        } else {
            Session::remove("recovery_auth_{$canonicalFlow}");
        }
    }

    /**
     * Check if an email is already associated with an existing record according to project business rules
     */
    public function checkDuplicateEmail(string $email, string $flow): ?string
    {
        $email = strtolower(trim($email));
        $canonicalFlow = self::normalizeFlow($flow);

        if ($canonicalFlow === self::FLOW_CUSTOMER) {
            $customerModel = new Customer();
            $existing = $customerModel->findBy('email', $email);
            if ($existing) {
                return "A customer record with the email '{$email}' already exists in the system.";
            }
        } elseif ($canonicalFlow === self::FLOW_EMPLOYEE) {
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
                          AND `status` IN ('consumed', 'invalidated', 'failed', 'draft')");

            $pdo->exec("UPDATE `email_verifications` 
                        SET `status` = 'invalidated' 
                        WHERE `expires_at` < NOW() AND `status` IN ('pending', 'draft')");
        } catch (Throwable $e) {
            // Silent cleanup error to not interrupt flow
        }
    }
}
