<?php
namespace App\Core;

class Logger
{
    private static array $redactKeys = [
        'password', 'password_confirmation', 'secret', 'token', '_csrf_token', 
        'api_key', 'auth_token', 'credit_card', 'cvv'
    ];

    public static function log(string $level, string $message, array $context = [], string $channel = 'app'): void
    {
        $logDir = Config::get('paths.logs', dirname(dirname(__DIR__)) . '/storage/logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $date = date('Y-m-d');
        $logFile = $logDir . "/{$channel}-{$date}.log";

        $sanitizedContext = self::redactSensitiveData($context);
        
        $entry = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($sanitizedContext) ? json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::log('INFO', $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::log('WARNING', $message, $context, $channel);
    }

    public static function error(string $message, array $context = [], string $channel = 'error'): void
    {
        self::log('ERROR', $message, $context, $channel);
    }

    public static function security(string $message, array $context = []): void
    {
        self::log('SECURITY', $message, $context, 'security');
    }

    public static function audit(string $message, array $context = []): void
    {
        self::log('AUDIT', $message, $context, 'audit');
    }

    private static function redactSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::redactSensitiveData($value);
            } elseif (in_array(strtolower((string)$key), self::$redactKeys, true)) {
                $data[$key] = '********';
            }
        }
        return $data;
    }
}
