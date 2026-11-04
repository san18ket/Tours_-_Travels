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
    private EmailOtpService $otpService;
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
        $this->otpService = new EmailOtpService($this->emailService);
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
        return $this->otpService->sendOtp($email, $purpose, null, $clientIp);
    }

    /**
     * Timing-safe verification of 4-digit recovery OTP
     * Generates a short-lived, one-time recovery authorization token on success.
     *
     * @return array ['success' => bool, 'message' => string, 'token' => ?string, 'error' => ?string]
     */
    public function verifyRecoveryOtp(string $email, string $purpose, string $otp, ?string $clientIp = null): array
    {
        return $this->otpService->verifyOtp($email, $purpose, $otp, $clientIp);
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
