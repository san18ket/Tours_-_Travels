<?php
/**
 * Professional SMTP Email Configuration
 * Gmail SSL SMTP Settings (Port 465) & TLS (Port 587)
 */

$smtpUsername = env('MAIL_USERNAME', '');
$configuredFromAddress = env('MAIL_FROM_ADDRESS', '');
$effectiveFromAddress = !empty($configuredFromAddress) ? $configuredFromAddress : $smtpUsername;

return [
    'driver'     => env('MAIL_DRIVER', 'smtp'),
    'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
    'port'       => (int)env('MAIL_PORT', 465),
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'auth'       => (bool)env('MAIL_AUTH', true),
    'verify_peer'=> (bool)env('MAIL_VERIFY_PEER', true),

    // Configurable Sender Credentials
    'username'   => $smtpUsername,
    'password'   => env('MAIL_PASSWORD', ''),

    // Configurable Sender Identity:
    // Default From address MUST be the authenticated SMTP username or an authorized alias
    'from'       => [
        'address' => $effectiveFromAddress ?: 'noreply@example.com',
        'name'    => class_exists('\App\Services\CompanySettingsService') ? \App\Services\CompanySettingsService::getCompanyName() : env('MAIL_FROM_NAME', 'Company name not configured'),
    ],

    // Reply-To Identity: By default routes back to Company Profile official email or MAIL_REPLY_TO
    'reply_to'   => [
        'address' => env('MAIL_REPLY_TO', class_exists('\App\Services\CompanySettingsService') ? \App\Services\CompanySettingsService::getCompanyEmail() : ''),
        'name'    => class_exists('\App\Services\CompanySettingsService') ? \App\Services\CompanySettingsService::getCompanyName() : '',
    ],

    // Operations / Administrator Recipient
    'admin_address' => env('MAIL_ADMIN_ADDRESS', class_exists('\App\Services\CompanySettingsService') ? \App\Services\CompanySettingsService::getCompanyEmail() : $smtpUsername),

    // Timeout in seconds
    'timeout'    => 15,
];
