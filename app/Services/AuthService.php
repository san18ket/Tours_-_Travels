<?php
namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use App\Core\Session;
use App\Core\Logger;
use RuntimeException;

class AuthService
{
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->userModel = new User();
        $this->auditLog = new AuditLog();
    }

    public function login(string $identifier, string $password, bool $remember = false): array
    {
        $identifier = trim($identifier);
        $user = $this->userModel->findByUsernameOrEmail($identifier);

        if (!$user) {
            Logger::security("Login attempt with unknown username/email: {$identifier}");
            throw new RuntimeException('Invalid credentials provided.');
        }

        if ($user['status'] !== 'active') {
            Logger::security("Login attempt for deactivated user: {$identifier}");
            throw new RuntimeException('Your account is currently disabled. Please contact the administrator.');
        }

        $pwdValid = password_verify($password, $user['password']);

        if (!$pwdValid) {
            Logger::security("Failed password attempt for user: {$identifier}");
            throw new RuntimeException('Invalid credentials provided.');
        }

        // Regenerate session to prevent session fixation
        Session::regenerate();

        // Store user state
        Session::set('user_id', (int)$user['id']);
        Session::set('user_role', $user['role']);
        
        // Synchronize user language preference
        $userLocale = !empty($user['language']) ? (string)$user['language'] : null;
        if ($userLocale && \App\Core\Language::isSupported($userLocale)) {
            \App\Core\Language::setLocale($userLocale, true, (int)$user['id']);
        } else {
            $currentLocale = \App\Core\Language::getLocale();
            \App\Core\Language::saveUserLocale((int)$user['id'], $currentLocale);
        }

        Session::set('user', [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'username' => $user['username'] ?? '',
            'email'    => $user['email'],
            'role'     => $user['role'],
            'language' => \App\Core\Language::getLocale(),
        ]);

        if ($remember) {
            $this->createRememberToken((int)$user['id']);
        }

        $this->auditLog->record('auth_login_success', 'users', $user['id'], null, ['username' => $user['username'], 'email' => $user['email']], $user['id']);
        Logger::info("User logged in successfully: {$user['username']} ({$user['email']}) [Role: {$user['role']}]");

        return $user;
    }

    /**
     * Create a secure, revocable Remember Me persistent token with split-token architecture
     */
    public function createRememberToken(int $userId): string
    {
        $selector = bin2hex(random_bytes(16)); // 32 hex chars
        $validator = bin2hex(random_bytes(32)); // 64 hex chars
        $validatorHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 86400); // 30 days

        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("INSERT INTO `remember_tokens` (`user_id`, `selector`, `validator_hash`, `expires_at`, `created_at`) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);

            $cookieValue = "{$selector}:{$validator}";
            $this->setRememberCookie($cookieValue, time() + 30 * 86400);

            return $cookieValue;
        } catch (\Throwable $e) {
            Logger::error("Failed to issue remember token for user {$userId}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Attempt automated authentication via Remember Me cookie
     */
    public function attemptRememberMeLogin(?string $rawCookie = null): ?array
    {
        $rawCookie = $rawCookie ?? ($_COOKIE['tours_remember'] ?? null);
        if (!$rawCookie || !is_string($rawCookie) || !str_contains($rawCookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $rawCookie, 2);
        if (strlen($selector) !== 32 || strlen($validator) !== 64 || !ctype_xdigit($selector) || !ctype_xdigit($validator)) {
            $this->clearRememberCookie();
            return null;
        }

        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM `remember_tokens` WHERE `selector` = ? AND `expires_at` > NOW() LIMIT 1");
            $stmt->execute([$selector]);
            $tokenRecord = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$tokenRecord) {
                $this->clearRememberCookie();
                return null;
            }

            $now = time();
            $tokenExpiresAt = strtotime($tokenRecord['expires_at'] ?? '1970-01-01');
            if ($tokenExpiresAt <= $now) {
                // Expired token cannot restore session; grace window cannot extend past original expiry
                $this->revokeRememberToken($rawCookie);
                $this->clearRememberCookie();
                return null;
            }

            // Verify validator in constant time using SHA-256 hash
            $actualHash = hash('sha256', $validator);
            $matchesPrimary = hash_equals($tokenRecord['validator_hash'], $actualHash);
            $prevHash = $tokenRecord['prev_validator_hash'] ?? null;
            $matchesPrevious = ($prevHash !== null && $prevHash !== '' && hash_equals($prevHash, $actualHash));

            if (!$matchesPrimary) {
                // Strict 60-second concurrency grace window for in-flight parallel requests
                // A future timestamp must NEVER be accepted as inside the grace window
                $rotatedAt = !empty($tokenRecord['rotated_at']) ? strtotime($tokenRecord['rotated_at']) : 0;
                $inGraceWindow = ($rotatedAt > 0 && $rotatedAt <= $now && ($now - $rotatedAt) <= 60);

                if ($matchesPrevious && $inGraceWindow) {
                    $user = $this->userModel->find((int)$tokenRecord['user_id']);
                    if (!$user || ($user['status'] ?? '') !== 'active') {
                        $this->revokeRememberToken($rawCookie);
                        $this->clearRememberCookie();
                        return null;
                    }

                    Session::regenerate();
                    Session::set('user_id', (int)$user['id']);
                    Session::set('user_role', $user['role']);
                    Session::set('user', [
                        'id'       => $user['id'],
                        'name'     => $user['name'],
                        'username' => $user['username'] ?? '',
                        'email'    => $user['email'],
                        'role'     => $user['role'],
                        'language' => $user['language'] ?? \App\Core\Language::getLocale(),
                    ]);

                    Logger::info("User authenticated via Remember Me grace window: {$user['username']} (ID: {$user['id']})");
                    return $user;
                }

                // Invalid token, future timestamp, or grace period expired: revoke ONLY this selector token to prevent DoS against user's other devices
                Logger::security("Remember Me token validator mismatch for selector {$selector}. Revoking compromised token ID {$tokenRecord['id']}.");
                try {
                    $deleteStmt = $pdo->prepare("DELETE FROM `remember_tokens` WHERE `id` = ?");
                    $deleteStmt->execute([$tokenRecord['id']]);
                } catch (\Throwable $e) {
                    Logger::error("Failed to revoke compromised remember token: " . $e->getMessage());
                }
                $this->clearRememberCookie();
                return null;
            }

            // Retrieve and verify user status
            $user = $this->userModel->find((int)$tokenRecord['user_id']);
            if (!$user || ($user['status'] ?? '') !== 'active') {
                $this->revokeRememberToken($rawCookie);
                $this->clearRememberCookie();
                return null;
            }

            // Regenerate session to prevent fixation
            Session::regenerate();

            // Set authenticated session state
            Session::set('user_id', (int)$user['id']);
            Session::set('user_role', $user['role']);
            Session::set('user', [
                'id'       => $user['id'],
                'name'     => $user['name'],
                'username' => $user['username'] ?? '',
                'email'    => $user['email'],
                'role'     => $user['role'],
                'language' => $user['language'] ?? \App\Core\Language::getLocale(),
            ]);

            // Atomic Concurrency-Safe Token Rotation:
            // Conditional update ensures only one simultaneous request successfully rotates the validator
            $newValidator = bin2hex(random_bytes(32));
            $newValidatorHash = hash('sha256', $newValidator);
            $prevValidatorHash = $tokenRecord['validator_hash'];
            $newExpiresAt = date('Y-m-d H:i:s', $now + 30 * 86400);

            try {
                $updateStmt = $pdo->prepare(
                    "UPDATE `remember_tokens` 
                     SET `validator_hash` = ?, 
                         `prev_validator_hash` = ?, 
                         `rotated_at` = NOW(), 
                         `expires_at` = ?, 
                         `last_used_at` = NOW() 
                     WHERE `id` = ? AND `validator_hash` = ?"
                );
                $updateStmt->execute([$newValidatorHash, $prevValidatorHash, $newExpiresAt, $tokenRecord['id'], $tokenRecord['validator_hash']]);

                if ($updateStmt->rowCount() === 1) {
                    // Atomic rotation succeeded: issue new cookie
                    $newCookieVal = "{$selector}:{$newValidator}";
                    $this->setRememberCookie($newCookieVal, $now + 30 * 86400);
                } else {
                    // Another simultaneous request rotated this validator right before us:
                    // Reload record to confirm it was rotated into prev_validator_hash within grace window
                    $recheck = $pdo->prepare("SELECT * FROM `remember_tokens` WHERE `id` = ?");
                    $recheck->execute([$tokenRecord['id']]);
                    $reloaded = $recheck->fetch(\PDO::FETCH_ASSOC);

                    $relRotatedAt = !empty($reloaded['rotated_at']) ? strtotime($reloaded['rotated_at']) : 0;
                    $relPrevHash = $reloaded['prev_validator_hash'] ?? '';

                    if ($reloaded && hash_equals($relPrevHash, $actualHash) && $relRotatedAt > 0 && $relRotatedAt <= $now && ($now - $relRotatedAt) <= 60) {
                        Logger::info("Simultaneous remember-me request authenticated safely during concurrent rotation: {$user['username']}");
                    } else {
                        Logger::warning("Remember Me rotation race condition detected for selector {$selector}");
                    }
                }
            } catch (\Throwable $e) {
                Logger::error("Database failure during remember token rotation: " . $e->getMessage());
                // In case of DB failure, keep existing cookie so client is not prematurely logged out
            }

            $this->auditLog->record('auth_remember_login', 'users', $user['id'], null, ['selector' => $selector], $user['id']);
            Logger::info("User auto-authenticated via Remember Me: {$user['username']} (ID: {$user['id']})");

            return $user;
        } catch (\Throwable $e) {
            Logger::error("Remember Me login failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Revoke single Remember Me token from database
     */
    public function revokeRememberToken(?string $rawCookie = null): void
    {
        $rawCookie = $rawCookie ?? ($_COOKIE['tours_remember'] ?? null);
        if (!$rawCookie || !is_string($rawCookie) || !str_contains($rawCookie, ':')) {
            $this->clearRememberCookie();
            return;
        }

        [$selector] = explode(':', $rawCookie, 2);
        if (strlen($selector) === 32 && ctype_xdigit($selector)) {
            try {
                $pdo = \App\Core\Database::getConnection();
                $stmt = $pdo->prepare("DELETE FROM `remember_tokens` WHERE `selector` = ?");
                $stmt->execute([$selector]);
            } catch (\Throwable $e) {
                Logger::error("Failed to revoke remember token: " . $e->getMessage());
            }
        }
        $this->clearRememberCookie();
    }

    /**
     * Revoke all remember tokens for a specific user (e.g. on password change or breach detection)
     */
    public function revokeAllUserRememberTokens(int $userId): void
    {
        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM `remember_tokens` WHERE `user_id` = ?");
            $stmt->execute([$userId]);
        } catch (\Throwable $e) {
            Logger::error("Failed to revoke all user remember tokens: " . $e->getMessage());
        }
    }

    /**
     * Consistent cookie options matching creation and deletion attributes
     */
    private function getCookieOptions(int $expires): array
    {
        $sessionConfig = \App\Core\Config::get('security.session', []);
        $isHttps = (bool)($sessionConfig['cookie_secure'] ?? false)
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $cookieDomain = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $parsedHost = explode(':', $_SERVER['HTTP_HOST'])[0];
            if ($parsedHost !== 'localhost' && !filter_var($parsedHost, FILTER_VALIDATE_IP)) {
                $cookieDomain = $parsedHost;
            }
        }

        return [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => $cookieDomain,
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $sessionConfig['cookie_samesite'] ?? 'Lax',
        ];
    }

    private function setRememberCookie(string $value, int $expires): void
    {
        $options = $this->getCookieOptions($expires);
        if (!headers_sent()) {
            setcookie('tours_remember', $value, $options);
        }
        $_COOKIE['tours_remember'] = $value;
    }

    private function clearRememberCookie(): void
    {
        $options = $this->getCookieOptions(time() - 42000);
        if (!headers_sent()) {
            setcookie('tours_remember', '', $options);
        }
        unset($_COOKIE['tours_remember']);
    }

    public function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId) {
            $this->auditLog->record('auth_logout', 'users', $userId, null, null, $userId);
        }
        $this->revokeRememberToken();
        Session::destroy();
    }
}
