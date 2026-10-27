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

    public function login(string $identifier, string $password): array
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

        if (!password_verify($password, $user['password'])) {
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

        $this->auditLog->record('auth_login_success', 'users', $user['id'], null, ['username' => $user['username'], 'email' => $user['email']], $user['id']);
        Logger::info("User logged in successfully: {$user['username']} ({$user['email']}) [Role: {$user['role']}]");

        return $user;
    }

    public function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId) {
            $this->auditLog->record('auth_logout', 'users', $userId, null, null, $userId);
        }
        Session::destroy();
    }
}
