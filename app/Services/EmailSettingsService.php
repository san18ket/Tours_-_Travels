<?php
namespace App\Services;

use App\Core\Config;

/**
 * Authoritative Email Settings & Configuration Service
 * Reads and validates SMTP settings from .env and config/mail.php.
 * Masks credentials for UI display and provides DNS deliverability guidance.
 */
class EmailSettingsService
{
    /**
     * Get authoritative SMTP configuration for sending (raw credentials)
     */
    public static function getTransportConfig(): array
    {
        $smtpUsername = (string)Config::get('mail.username', env('MAIL_USERNAME', ''));
        $configuredFrom = (string)Config::get('mail.from.address', env('MAIL_FROM_ADDRESS', ''));
        $fromAddress = (!empty($configuredFrom) && filter_var($configuredFrom, FILTER_VALIDATE_EMAIL))
            ? $configuredFrom
            : ($smtpUsername ?: 'noreply@example.com');

        // Gmail compliance: If sending via Gmail SMTP, From address must match authenticated Gmail user
        $host = (string)Config::get('mail.host', env('MAIL_HOST', 'smtp.gmail.com'));
        if (str_contains(strtolower($host), 'gmail.com') && !empty($smtpUsername)) {
            $fromAddress = $smtpUsername;
        }

        $fromName = (string)Config::get('mail.from.name', env('MAIL_FROM_NAME', CompanySettingsService::getCompanyName()));
        $replyToAddress = (string)Config::get('mail.reply_to.address', env('MAIL_REPLY_TO_ADDRESS', CompanySettingsService::getCompanyEmail()));
        $replyToName = (string)Config::get('mail.reply_to.name', env('MAIL_REPLY_TO_NAME', $fromName));

        return [
            'host'              => $host,
            'port'              => (int)Config::get('mail.port', env('MAIL_PORT', 465)),
            'encryption'        => strtolower((string)Config::get('mail.encryption', env('MAIL_ENCRYPTION', 'ssl'))),
            'auth'              => (bool)Config::get('mail.auth', true),
            'username'          => $smtpUsername,
            'password'          => (string)Config::get('mail.password', env('MAIL_PASSWORD', '')),
            'timeout'           => (int)Config::get('mail.timeout', 15),
            'from_address'      => $fromAddress,
            'from_name'         => $fromName,
            'reply_to_address'  => $replyToAddress,
            'reply_to_name'     => $replyToName,
            'admin_address'     => (string)Config::get('mail.admin.address', env('MAIL_ADMIN_ADDRESS', '')),
            'verify_peer'       => (bool)Config::get('mail.verify_peer', true),
        ];
    }

    /**
     * Get sanitized SMTP settings for UI display (passwords never exposed)
     */
    public static function getDisplaySettings(): array
    {
        $cfg = self::getTransportConfig();
        $hasPassword = !empty($cfg['password']);
        $hasUsername = !empty($cfg['username']);

        return [
            'host'              => $cfg['host'],
            'port'              => $cfg['port'],
            'encryption'        => $cfg['encryption'],
            'auth'              => $cfg['auth'],
            'username'          => self::maskEmail($cfg['username']),
            'username_raw'      => $cfg['username'],
            'has_password'      => $hasPassword,
            'password_masked'   => $hasPassword ? '••••••••••••••••' : 'Not configured',
            'from_address'      => $cfg['from_address'],
            'from_name'         => $cfg['from_name'],
            'reply_to_address'  => $cfg['reply_to_address'],
            'reply_to_name'     => $cfg['reply_to_name'],
            'admin_address'     => $cfg['admin_address'],
            'is_configured'     => $hasUsername && $hasPassword,
            'driver'            => 'smtp',
        ];
    }

    /**
     * Test raw TCP/TLS socket connection to the SMTP server without sending email
     *
     * @return array ['success' => bool, 'latency_ms' => int, 'message' => string, 'details' => ?string]
     */
    public static function testConnection(): array
    {
        $cfg = self::getTransportConfig();
        $host = $cfg['host'];
        $port = $cfg['port'];
        $timeout = min(10, $cfg['timeout']);

        $targetHost = $host;
        if ($cfg['encryption'] === 'ssl' && $port === 465) {
            $targetHost = 'ssl://' . $host;
        }

        $startTime = microtime(true);
        $errNo = 0;
        $errStr = '';

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => $cfg['verify_peer'],
                'verify_peer_name'  => $cfg['verify_peer'],
                'allow_self_signed' => !$cfg['verify_peer'],
            ]
        ]);

        $socket = @stream_socket_client(
            "{$targetHost}:{$port}",
            $errNo,
            $errStr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        $latencyMs = (int)round((microtime(true) - $startTime) * 1000);

        if (!$socket) {
            $msg = "Unable to connect to {$host}:{$port} ({$errNo}: {$errStr})";
            return [
                'success'    => false,
                'latency_ms' => $latencyMs,
                'message'    => $msg,
                'details'    => $errStr ?: 'Connection timed out or host unreachable.'
            ];
        }

        // Read SMTP greeting banner (e.g. 220 smtp.gmail.com ...)
        stream_set_timeout($socket, $timeout);
        $banner = fgets($socket, 512);
        fclose($socket);

        if ($banner && str_starts_with(trim($banner), '220')) {
            return [
                'success'    => true,
                'latency_ms' => $latencyMs,
                'message'    => "SMTP server responded successfully in {$latencyMs}ms.",
                'details'    => trim($banner)
            ];
        }

        return [
            'success'    => true,
            'latency_ms' => $latencyMs,
            'message'    => "TCP socket connected to {$host}:{$port} in {$latencyMs}ms.",
            'details'    => $banner ? trim($banner) : 'Connected (No banner returned)'
        ];
    }

    /**
     * Get domain deliverability recommendations (SPF, DKIM, DMARC)
     */
    public static function getDeliverabilityGuidance(): array
    {
        $cfg = self::getTransportConfig();
        $fromEmail = $cfg['from_address'];
        $domain = '';
        if (str_contains($fromEmail, '@')) {
            $domain = substr(strrchr($fromEmail, '@'), 1);
        }

        $isGmail = str_contains(strtolower($cfg['host']), 'gmail.com') || str_contains(strtolower($domain), 'gmail.com');

        return [
            'domain'   => $domain,
            'is_gmail' => $isGmail,
            'spf'      => [
                'type'        => 'TXT',
                'record_type' => 'TXT',
                'host'        => '@ (or root domain)',
                'name'        => '@ (or root domain)',
                'value'       => $isGmail ? 'v=spf1 include:_spf.google.com ~all' : 'v=spf1 include:your-mail-server.com ~all',
                'description' => 'Sender Policy Framework specifies which mail servers are authorized to send email on behalf of your domain.'
            ],
            'dkim'     => [
                'type'        => 'TXT',
                'record_type' => 'TXT',
                'host'        => 'google._domainkey (for Google Workspace) or your selector',
                'name'        => 'google._domainkey (for Google Workspace) or your selector',
                'value'       => 'v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY',
                'description' => 'DomainKeys Identified Mail adds a cryptographic signature to outgoing messages, proving the email was not forged.'
            ],
            'dmarc'    => [
                'type'        => 'TXT',
                'record_type' => 'TXT',
                'host'        => '_dmarc',
                'name'        => '_dmarc',
                'value'       => 'v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@' . ($domain ?: 'yourdomain.com'),
                'description' => 'Domain-based Message Authentication, Reporting and Conformance aligns SPF and DKIM and instructs receiving servers how to handle failures.'
            ]
        ];
    }

    /**
     * Mask email address for security
     */
    public static function maskEmail(?string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return 'Not configured';
        }

        $parts = explode('@', $email, 2);
        $user = $parts[0];
        $domain = $parts[1];

        $len = strlen($user);
        if ($len <= 2) {
            $maskedUser = $user[0] . '*';
        } elseif ($len <= 4) {
            $maskedUser = $user[0] . str_repeat('*', $len - 2) . $user[$len - 1];
        } else {
            $maskedUser = substr($user, 0, 2) . str_repeat('*', max(3, $len - 4)) . substr($user, -2);
        }

        return $maskedUser . '@' . $domain;
    }
}
