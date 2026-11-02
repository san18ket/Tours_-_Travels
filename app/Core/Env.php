<?php
namespace App\Core;

/**
 * Secure Environment Variables Loader
 * Lightweight, zero-dependency .env parser supporting quoted values, comments, and type casting
 */
class Env
{
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load(?string $filePath = null): void
    {
        if ($filePath === null) {
            $filePath = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }

            // Must contain key=value
            $equalPos = strpos($line, '=');
            if ($equalPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $equalPos));
            $value = trim(substr($line, $equalPos + 1));

            if ($key === '') {
                continue;
            }

            // Parse quoted values and inline comments
            $parsedValue = self::parseValue($value);

            // Store in $_ENV, $_SERVER, and putenv
            $_ENV[$key] = $parsedValue;
            $_SERVER[$key] = $parsedValue;

            if (is_scalar($parsedValue)) {
                putenv("{$key}={$parsedValue}");
            }
        }

        self::$loaded = true;
    }

    /**
     * Parse value with quotation and type detection
     */
    private static function parseValue(string $value): mixed
    {
        if ($value === '') {
            return '';
        }

        // Check if enclosed in double quotes
        if (str_starts_with($value, '"') && str_ends_with($value, '"') && strlen($value) >= 2) {
            $inner = substr($value, 1, -1);
            return str_replace(['\\"', '\\n', '\\r', '\\t'], ['"', "\n", "\r", "\t"], $inner);
        }

        // Check if enclosed in single quotes
        if (str_starts_with($value, "'") && str_ends_with($value, "'") && strlen($value) >= 2) {
            $inner = substr($value, 1, -1);
            return str_replace("\\'", "'", $inner);
        }

        // If not quoted, strip any trailing comment
        $commentPos = strpos($value, '#');
        if ($commentPos !== false) {
            $value = trim(substr($value, 0, $commentPos));
        }

        // Case-insensitive literal matching
        $lower = strtolower($value);
        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }
        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }
        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }
        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }

        return $value;
    }

    /**
     * Get an environment variable with default fallback
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }

        return $default;
    }

    /**
     * Check if .env has been loaded
     */
    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
