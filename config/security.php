<?php
/**
 * Security Configuration
 */

return [
    'session' => [
        'name'            => 'VOYAGEGO_SESSID',
        'lifetime'        => 7200, // 2 hours
        'idle_timeout'    => 1800, // 30 minutes idle timeout
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax', // 'Lax' or 'Strict'
        'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443),
    ],

    'csrf' => [
        'token_name'  => '_csrf_token',
        'header_name' => 'X-CSRF-TOKEN',
    ],

    'rate_limits' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'api' => [
            'max_attempts' => 120,
            'decay_minutes' => 1,
        ],
        'gps_submissions' => [
            'max_attempts' => 60, // allow 1 per second per driver
            'decay_minutes' => 1,
        ],
    ],

    'uploads' => [
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_mimes' => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ],
    ],

    // Security headers sent on every response
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'SAMEORIGIN',
        'X-XSS-Protection'       => '1; mode=block',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self';",
    ],
];
