<?php
namespace App\Core;

class Csrf
{
    /**
     * Generate or return existing CSRF token
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Verify token using timing-safe string comparison
     */
    public static function validate(?string $token): bool
    {
        Session::start();
        $sessionToken = Session::get('_csrf_token');
        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Helper to render hidden HTML input
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Helper to render meta tag for AJAX requests
     */
    public static function meta(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
