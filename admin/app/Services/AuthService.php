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
            'id'            => $user['id'],
            'name'          => $user['name'],
            'username'      => $user['username'] ?? '',
            'email'         => $user['email'],
            'role'          => $user['role'],
            'language'      => \App\Core\Language::getLocale(),
            'profile_photo' => $this->resolveUserProfilePhoto((int)$user['id']),
        ]);

        // Session-only authentication: persistent remember tokens are disabled.
        // Always clear any legacy remember cookie upon login.
        $this->clearRememberCookie();

        $this->auditLog->record('auth_login_success', 'users', $user['id'], null, ['username' => $user['username'], 'email' => $user['email']], $user['id']);
        Logger::info("User logged in successfully: {$user['username']} ({$user['email']}) [Role: {$user['role']}]");

        return $user;
    }

    /**
     * Legacy Remember Me token helper (Deprecated: Session-only authentication policy enforced)
     */
    public function createRememberToken(int $userId): string
    {
        $this->clearRememberCookie();
        return '';
    }

    /**
     * Attempt automated authentication via Remember Me cookie
     * (Disabled: Session-only authentication policy enforced. Clears and revokes any legacy cookie).
     */
    public function attemptRememberMeLogin(?string $rawCookie = null): ?array
    {
        $this->revokeRememberToken($rawCookie);
        return null;
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
        // Session-only authentication policy: persistent cookies are suppressed
        $this->clearRememberCookie();
    }

    public function clearRememberCookie(): void
    {
        $options = $this->getCookieOptions(time() - 42000);
        if (!headers_sent()) {
            setcookie('tours_remember', '', $options);
            if (!empty($options['domain'])) {
                $hostOnly = $options;
                $hostOnly['domain'] = '';
                setcookie('tours_remember', '', $hostOnly);
            }
        }
        unset($_COOKIE['tours_remember']);
    }

    public function resolveUserProfilePhoto(int $userId): ?string
    {
        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("SELECT profile_photo FROM `employees` WHERE user_id = ? AND profile_photo IS NOT NULL AND profile_photo != '' LIMIT 1");
            $stmt->execute([$userId]);
            $photo = $stmt->fetchColumn();
            if ($photo) {
                return (string)$photo;
            }

            $stmt2 = $pdo->prepare("SELECT profile_photo FROM `customers` WHERE user_id = ? AND profile_photo IS NOT NULL AND profile_photo != '' LIMIT 1");
            $stmt2->execute([$userId]);
            $photo = $stmt2->fetchColumn();
            if ($photo) {
                return (string)$photo;
            }
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }
        return null;
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
