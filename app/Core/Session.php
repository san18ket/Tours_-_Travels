<?php
namespace App\Core;

class Session
{
    private static bool $started = false;

    /**
     * Start secure session with configured flags
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $sessionConfig = Config::get('security.session');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_name($sessionConfig['name'] ?? 'VOYAGEGO_SESSID');

        $cookieDomain = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $parsedHost = explode(':', $_SERVER['HTTP_HOST'])[0];
            if ($parsedHost !== 'localhost' && !filter_var($parsedHost, FILTER_VALIDATE_IP)) {
                $cookieDomain = $parsedHost;
            }
        }

        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] ?? 7200,
            'path'     => '/',
            'domain'   => $cookieDomain,
            'secure'   => (bool)($sessionConfig['cookie_secure'] ?? false),
            'httponly' => true,
            'samesite' => $sessionConfig['cookie_samesite'] ?? 'Lax',
        ]);

        session_start();
        self::$started = true;

        // Check for session idle timeout
        $idleTimeout = $sessionConfig['idle_timeout'] ?? 1800;
        $now = time();

        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity'] > $idleTimeout)) {
            self::destroy();
            session_start();
            self::setFlash('warning', 'Your session has expired due to inactivity. Please log in again.');
        }

        $_SESSION['_last_activity'] = $now;
    }

    /**
     * Regenerate session ID to prevent session fixation attacks
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
            self::$started = false;
        }
    }

    /**
     * Flash messages for single-request notifications
     */
    public static function setFlash(string $type, mixed $message): void
    {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlash(string $type): mixed
    {
        self::start();
        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function getAllFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserRole(): ?string
    {
        return self::get('user_role');
    }

    public static function getUser(): ?array
    {
        return self::get('user');
    }

    public static function setUser(array $user): void
    {
        self::start();
        if (isset($user['id'])) {
            self::set('user_id', (int)$user['id']);
        }
        if (isset($user['role'])) {
            self::set('user_role', $user['role']);
        }
        self::set('user', $user);
    }
}
