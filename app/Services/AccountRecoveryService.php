<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Session;
use App\Core\RateLimiter;
use App\Models\User;
use App\Models\AuditLog;
use PDO;
use Throwable;

/**
 * Reusable Account Recovery Service
 * Handles secure 4-digit OTP generation, hashing, rate limiting, expiry,
 * timing-safe verification, short-lived recovery authorizations, username recovery,
 * and secure password resetting with session/token revocation.
 */
class AccountRecoveryService
{
    public const FLOW_USERNAME = 'recovery_username';
    public const FLOW_PASSWORD = 'recovery_password';

    public const OTP_EXPIRY_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;
    public const AUTH_TOKEN_EXPIRY_MINUTES = 10;

    private EmailService $emailService;
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
        $this->userModel = new User();
        $this->auditLog = new AuditLog();
    }

    /**
     * Send or resend 4-digit recovery OTP to registered email address
     * Preserves privacy: unregistered and registered emails receive the identical message.
     *
     * @return array ['success' => bool, 'message' => string, 'cooldown' => int, 'error' => ?string]
     */
    public function sendRecoveryOtp(string $email, string $purpose, ?string $clientIp = null): array
    {
        $email = strtolower(trim($email));
        $purpose = strtolower(trim($purpose));
        $ip = $clientIp ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        // Standard privacy-preserving response
        $genericMessage = "If an account is associated with this email, a verification code will be sent.";

        // 1. Validate Purpose
        if (!in_array($purpose, [self::FLOW_USERNAME, self::FLOW_PASSWORD], true)) {
            return [
                'success'  => false,
                'message'  => 'Invalid recovery purpose requested.',
                'cooldown' => 0,
                'error'    => 'Invalid purpose.'
            ];
        }

        // 2. Validate Email Format
        if (empty($email)) {
            return [
                'success'  => false,
                'message'  => 'Please enter your registered email address.',
                'cooldown' => 0,
                'error'    => 'Email address is required.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success'  => false,
                'message'  => 'Please enter a valid email address format.',
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

        // 4. Rate Limiting Protection (Max 5 requests per IP+Email within 10 minutes)
        $rateLimitKey = "recovery_otp_send:" . md5("{$ip}_{$email}_{$purpose}");
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

        $pdo = Database::getConnection();

        // 5. Check Resend Cooldown (60 Seconds) across all requests for this email + purpose
        $stmt = $pdo->prepare("SELECT id, resend_available_at, TIMESTAMPDIFF(SECOND, NOW(), resend_available_at) as remaining_seconds
                               FROM `email_verifications`
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' AND `resend_available_at` > NOW()
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $purpose]);
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

        // 6. Look up User Account in actual login 'users' table
        $userStmt = $pdo->prepare("SELECT id, name, username, email, role, status 
                                   FROM `users` 
                                   WHERE LOWER(TRIM(`email`)) = ?");
        $userStmt->execute([$email]);
        $matchingUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        // Case A: Email Not Registered
        if (empty($matchingUsers)) {
            Logger::info("AccountRecoveryService: Recovery attempt for unregistered email: {$email} [Purpose: {$purpose}]");
            return [
                'success'  => false,
                'message'  => "We couldn't find an account associated with this email address. Please check your email and try again.",
                'cooldown' => 0,
                'error'    => 'Email Address Not Registered'
            ];
        }

        // Case B: Duplicate Email linked to multiple accounts (Ambiguous recovery prevented)
        if (count($matchingUsers) > 1) {
            Logger::warning("AccountRecoveryService: Ambiguous recovery stopped: multiple accounts (" . count($matchingUsers) . ") share email '{$email}' [Purpose: {$purpose}]");
            return [
                'success'  => false,
                'message'  => "We couldn't verify a unique account for this email. Please contact the administrator.",
                'cooldown' => 0,
                'error'    => 'Duplicate account email'
            ];
        }

        $targetUser = $matchingUsers[0];

        // Case C: Missing or invalid email on the matched account
        if (empty($targetUser['email']) || !filter_var($targetUser['email'], FILTER_VALIDATE_EMAIL)) {
            Logger::warning("AccountRecoveryService: Account has missing or invalid email [User ID: {$targetUser['id']}]");
            return [
                'success'  => false,
                'message'  => "We couldn't find an account associated with this email address. Please check your email and try again.",
                'cooldown' => 0,
                'error'    => 'Invalid account email'
            ];
        }

        // Case D: Account status checks (active, inactive, suspended, or other non-active)
        if ($targetUser['status'] === 'inactive') {
            Logger::warning("AccountRecoveryService: Recovery attempt stopped for inactive account [User ID: {$targetUser['id']}, Email: {$email}]");
            return [
                'success'  => false,
                'message'  => "Your account is currently inactive. Please contact the administrator for assistance.",
                'cooldown' => 0,
                'error'    => 'Account inactive'
            ];
        }

        if ($targetUser['status'] === 'suspended') {
            Logger::warning("AccountRecoveryService: Recovery attempt stopped for suspended account [User ID: {$targetUser['id']}, Email: {$email}]");
            return [
                'success'  => false,
                'message'  => "Your account is currently unavailable. Please contact the administrator.",
                'cooldown' => 0,
                'error'    => 'Account suspended'
            ];
        }

        if ($targetUser['status'] !== 'active') {
            Logger::warning("AccountRecoveryService: Recovery attempt stopped for non-active account [Status: {$targetUser['status']}, User ID: {$targetUser['id']}]");
            return [
                'success'  => false,
                'message'  => "Your account is currently unavailable. Please contact the administrator.",
                'cooldown' => 0,
                'error'    => 'Account unavailable'
            ];
        }

        // 7. Invalidate any existing draft/pending/verified recovery OTPs for this flow and email
        $invalStmt = $pdo->prepare("UPDATE `email_verifications` 
                                    SET `status` = 'invalidated' 
                                    WHERE `email` = ? AND `flow` = ? AND `status` IN ('draft', 'pending', 'verified')");
        $invalStmt->execute([$email, $purpose]);

        // Also clear existing session recovery token for this purpose
        Session::remove("recovery_auth_{$purpose}");

        // 8. Generate Secure 4-Digit OTP using PHP random_int()
        $rawOtp = sprintf('%04d', random_int(1000, 9999));
        $otpHash = password_hash($rawOtp, PASSWORD_BCRYPT);

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));
        $resendAvailableAt = date('Y-m-d H:i:s', strtotime('+' . self::RESEND_COOLDOWN_SECONDS . ' seconds'));

        // 9. Store record in database with 'draft' status (activated only upon confirmed SMTP dispatch)
        $insertStmt = $pdo->prepare("INSERT INTO `email_verifications` 
            (`email`, `flow`, `user_id`, `otp_hash`, `attempts`, `max_attempts`, `resend_available_at`, `expires_at`, `status`, `ip_address`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, 0, ?, ?, ?, 'draft', ?, NOW(), NOW())");
        $insertStmt->execute([
            $email,
            $purpose,
            $targetUser['id'],
            $otpHash,
            self::MAX_ATTEMPTS,
            $resendAvailableAt,
            $expiresAt,
            $ip
        ]);
        $verificationId = (int)$pdo->lastInsertId();

        // 10. Render Professional Recovery Email Template via existing EmailService
        $companyName = CompanySettingsService::getCompanyName();
        $recipientName = !empty($targetUser['name']) ? $targetUser['name'] : $targetUser['username'];

        if ($purpose === self::FLOW_USERNAME) {
            $subject = "Your account username recovery code";
            $previewText = "Your 4-digit username recovery code is {$rawOtp}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.";
            $templateName = 'auth/recovery-username-otp';
            $heading = 'Recover your username';
            $subheading = "Use this 4-digit code to view your registered account username.";
            $greetingLead = "Hello " . htmlspecialchars($recipientName) . ", we received a request to recover your {$companyName} account username.";
            $textBody = "Your account username recovery code for {$companyName} is: {$rawOtp}\n\nThis verification code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity warning: Never share this code with anyone.\nIf you did not request this, please disregard this email.";
        } else {
            $subject = "Your password reset verification code";
            $previewText = "Your 4-digit password reset code is {$rawOtp}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.";
            $templateName = 'auth/recovery-password-otp';
            $heading = 'Reset your account password';
            $subheading = "Use this 4-digit code to securely verify your identity and set a new password.";
            $greetingLead = "Hello " . htmlspecialchars($recipientName) . ", we received a request to reset your password for your {$companyName} account.";
            $textBody = "Your password reset verification code for {$companyName} is: {$rawOtp}\n\nThis verification code expires in " . self::OTP_EXPIRY_MINUTES . " minutes.\nSecurity warning: Never share this code with anyone. This code authorizes creating a new password.\nIf you did not request this, please disregard this email.";
        }

        try {
            $htmlBody = $this->emailService->renderTemplate($templateName, [
                'subject'        => $subject,
                'previewText'    => $previewText,
                'category'       => 'ACCOUNT SECURITY',
                'heading'        => $heading,
                'subheading'     => $subheading,
                'recipientName'  => $recipientName,
                'recipientEmail' => $email,
                'greetingLead'   => $greetingLead,
                'otp'            => $rawOtp,
                'purpose'        => $purpose,
            ]);

            // Dispatch via PHPMailer with cooldown=0 for immediate fresh code
            $dispatchResult = $this->emailService->send(
                $email,
                $recipientName,
                $subject,
                $htmlBody,
                $textBody,
                'account_recovery_otp',
                'email_verification',
                (int)$verificationId,
                [],
                [],
                0
            );

            if (empty($dispatchResult['success']) || !empty($dispatchResult['duplicate'])) {
                // Mark record 'failed' on transmission failure - never activate unusable OTP
                $pdo->prepare("UPDATE `email_verifications` SET `status` = 'failed', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);
                $cleanError = $dispatchResult['error'] ?? 'SMTP delivery failure or duplicate suppression.';
                Logger::error("AccountRecoveryService: Failed to dispatch recovery OTP to {$email}: {$cleanError}");

                return [
                    'success'  => false,
                    'message'  => "We couldn't send the verification code right now. Please try again shortly.",
                    'cooldown' => 0,
                    'error'    => 'SMTP delivery failure'
                ];
            }

            // Activate OTP from 'draft' to 'pending' ONLY after successful dispatch
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'pending', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);

        } catch (Throwable $e) {
            $pdo->prepare("UPDATE `email_verifications` SET `status` = 'failed', `updated_at` = NOW() WHERE `id` = ?")->execute([$verificationId]);
            Logger::error("AccountRecoveryService: Exception sending recovery OTP to {$email}: " . $e->getMessage());

            return [
                'success'  => false,
                'message'  => "We couldn't send the verification code right now. Please try again shortly.",
                'cooldown' => 0,
                'error'    => 'SMTP delivery failure'
            ];
        }

        // Opportunistic background cleanup of old records
        $this->cleanupExpiredRecords();

        return [
            'success'  => true,
            'message'  => "A 4-digit verification code has been sent to your registered email address.",
            'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            'error'    => null
        ];
    }

    /**
     * Timing-safe verification of 4-digit recovery OTP
     * Generates a short-lived, one-time recovery authorization token on success.
     *
     * @return array ['success' => bool, 'message' => string, 'token' => ?string, 'error' => ?string]
     */
    public function verifyRecoveryOtp(string $email, string $purpose, string $otp, ?string $clientIp = null): array
    {
        $email = strtolower(trim($email));
        $purpose = strtolower(trim($purpose));
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

        if (!in_array($purpose, [self::FLOW_USERNAME, self::FLOW_PASSWORD], true)) {
            return [
                'success' => false,
                'message' => 'Invalid recovery purpose specified.',
                'token'   => null,
                'error'   => 'Invalid purpose.'
            ];
        }

        $pdo = Database::getConnection();

        // 2. Fetch Active Pending Record
        $stmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                               WHERE `email` = ? AND `flow` = ? AND `status` = 'pending' 
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $purpose]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            $invalStmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                                        WHERE `email` = ? AND `flow` = ? 
                                        ORDER BY id DESC LIMIT 1");
            $invalStmt->execute([$email, $purpose]);
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

        // 6. Successful Verification: Generate short-lived recovery authorization token
        $recoveryToken = bin2hex(random_bytes(32)); // 64 hex characters
        $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+' . self::AUTH_TOKEN_EXPIRY_MINUTES . ' minutes'));

        $pdo->prepare("UPDATE `email_verifications` 
                       SET `status` = 'verified', 
                           `verified_at` = NOW(), 
                           `verification_token` = ?, 
                           `token_expires_at` = ? 
                       WHERE `id` = ?")
            ->execute([$recoveryToken, $tokenExpiresAt, $record['id']]);

        // Store recovery authorization in server session for double-binding
        Session::set("recovery_auth_{$purpose}", [
            'token'      => $recoveryToken,
            'user_id'    => (int)$record['user_id'],
            'email'      => $email,
            'expires_at' => time() + (self::AUTH_TOKEN_EXPIRY_MINUTES * 60)
        ]);

        Logger::info("AccountRecoveryService: Verified {$purpose} OTP successfully for email {$email} (User ID: {$record['user_id']})");

        return [
            'success' => true,
            'message' => 'Email verification successful.',
            'token'   => $recoveryToken,
            'error'   => null
        ];
    }

    /**
     * Validate a short-lived recovery authorization token
     * Checks database record, session binding, and user active status.
     *
     * @return array|null ['record' => array, 'user' => array] on success, null on invalid
     */
    public function validateRecoveryAuthorization(string $token, string $purpose): ?array
    {
        $token = trim($token);
        $purpose = strtolower(trim($purpose));

        if (empty($token) || strlen($token) !== 64 || !ctype_xdigit($token)) {
            return null;
        }

        // 1. Session verification check
        $sessionAuth = Session::get("recovery_auth_{$purpose}");
        if (empty($sessionAuth) || empty($sessionAuth['token']) || !hash_equals((string)$sessionAuth['token'], $token)) {
            // If session expired or mismatched, reject immediately
            return null;
        }

        if (($sessionAuth['expires_at'] ?? 0) < time()) {
            return null;
        }

        // 2. Database verification check
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `email_verifications` 
                               WHERE `verification_token` = ? 
                                 AND `flow` = ? 
                                 AND `status` = 'verified' 
                                 AND `token_expires_at` > NOW() 
                                 AND `user_id` IS NOT NULL 
                               ORDER BY id DESC LIMIT 1");
        $stmt->execute([$token, $purpose]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return null;
        }

        // 3. User account status check
        $user = $this->userModel->find((int)$record['user_id']);
        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        return [
            'record' => $record,
            'user'   => $user
        ];
    }

    /**
     * Send recovered username PRIVATELY through verified email
     * The username is NEVER returned to the webpage, JS, or JSON.
     *
     * @return array ['success' => bool, 'sent' => bool, 'message' => string]
     */
    public function recoverUsername(string $token): array
    {
        $validated = $this->validateRecoveryAuthorization($token, self::FLOW_USERNAME);
        if (!$validated) {
            return [
                'success' => false,
                'sent'    => false,
                'message' => 'Your recovery authorization is invalid or has expired. Please start over.'
            ];
        }

        $record = $validated['record'];
        $user = $validated['user'];

        // Consume the recovery authorization (One-time use)
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE `email_verifications` 
                       SET `status` = 'consumed', `consumed_at` = NOW() 
                       WHERE `id` = ?")
            ->execute([$record['id']]);

        Session::remove("recovery_auth_" . self::FLOW_USERNAME);

        // Send username PRIVATELY via email
        $emailResult = $this->emailService->sendUsernameRecovery($user);
        $emailSent = !empty($emailResult['success']);

        // Record security audit event (without logging OTP or sensitive secrets)
        $this->auditLog->record(
            'auth_username_recovered',
            'users',
            $user['id'],
            null,
            ['email' => $user['email'], 'email_dispatched' => $emailSent],
            $user['id']
        );

        Logger::security("Account username recovery completed via email OTP. Private email dispatched to: {$user['email']}");

        if ($emailSent) {
            $msg = 'Your username recovery request has been completed. If your account was verified, your username has been sent to your registered email address. Please check your inbox.';
        } else {
            $msg = 'Your account was verified, but we were unable to deliver the username email at this moment. You can retry from the recovery hub or contact support.';
        }

        return [
            'success' => true,
            'sent'    => $emailSent,
            'message' => $msg
        ];
    }

    /**
     * Reset account password upon presentation of valid recovery authorization
     * Strictly verifies strength, mismatch, updates password hash, revokes sessions/tokens.
     *
     * @return array ['success' => bool, 'message' => string, 'error' => ?string]
     */
    public function resetPassword(string $token, string $newPassword, string $confirmPassword): array
    {
        $validated = $this->validateRecoveryAuthorization($token, self::FLOW_PASSWORD);
        if (!$validated) {
            return [
                'success' => false,
                'message' => 'Your password reset session is invalid or has expired. Please request a new verification code.',
                'error'   => 'Invalid authorization token.'
            ];
        }

        $record = $validated['record'];
        $user = $validated['user'];

        // 1. Password mismatch validation
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'The entered passwords do not match. Please re-enter both fields.',
                'error'   => 'Password mismatch.'
            ];
        }

        // 2. Password Strength Validation (Minimum 8 chars, letters and digits)
        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long.',
                'error'   => 'Password too short.'
            ];
        }

        if (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            return [
                'success' => false,
                'message' => 'Password must contain at least one letter and one number.',
                'error'   => 'Password complexity insufficient.'
            ];
        }

        // 3. Hash password using PHP password_hash() with cost 12
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // 4. Update user password securely in database
        $pdo = Database::getConnection();
        $this->userModel->updatePassword((int)$user['id'], $newHash);

        // 5. Consume the recovery authorization
        $pdo->prepare("UPDATE `email_verifications` 
                       SET `status` = 'consumed', `consumed_at` = NOW() 
                       WHERE `id` = ?")
            ->execute([$record['id']]);

        Session::remove("recovery_auth_" . self::FLOW_PASSWORD);

        // 6. Invalidate relevant mobile devices & sessions where supported
        try {
            // Invalidate driver mobile device access tokens
            $pdo->prepare("UPDATE `driver_mobile_devices` 
                           SET `is_revoked` = 1 
                           WHERE `driver_id` IN (SELECT id FROM `driver_profiles` WHERE `user_id` = ?)")
                ->execute([(int)$user['id']]);

            // Invalidate any other active OTPs for this user
            $pdo->prepare("UPDATE `email_verifications` 
                           SET `status` = 'invalidated' 
                           WHERE `email` = ? AND `status` = 'pending'")
                ->execute([$user['email']]);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryService: Error revoking secondary tokens: " . $e->getMessage());
        }

        // 7. Regenerate session to destroy any stale recovery data
        Session::destroy();
        Session::start();

        // 8. Record audit log without logging passwords or OTPs
        $this->auditLog->record(
            'auth_password_reset',
            'users',
            $user['id'],
            null,
            ['email' => $user['email'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'],
            $user['id']
        );

        Logger::security("User password reset successfully via email OTP: {$user['username']} ({$user['email']})");

        return [
            'success' => true,
            'message' => 'Your password has been updated. You can now log in using your new password.',
            'error'   => null
        ];
    }

    /**
     * Opportunistic cleanup of expired or consumed recovery records older than 24 hours
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
            // Silent cleanup error to not interrupt primary flow
        }
    }
}
