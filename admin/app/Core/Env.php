<?php
namespace App\Core;

/**
 * Secure Environment Variables Loader
 * Lightweight, zero-dependency .env parser supporting quoted values, comments, and type casting
 */
class Env
{
    private static bool $loaded = false;
    private static ?string $loadedPath = null;

    /**
     * Load environment variables from project-root .env file
     */
    public static function load(?string $filePath = null): void
    {
        if (self::$loaded && $filePath === null) {
            return;
        }

        if ($filePath === null) {
            // Priority 1: Project root alongside app/, config/, public/
            $projectRoot = dirname(__DIR__, 2);
            $filePath = $projectRoot . DIRECTORY_SEPARATOR . '.env';

            // Priority 2: Alternative server roots (e.g. if DOCUMENT_ROOT is public/ or project root)
            if (!file_exists($filePath)) {
                $candidates = [
                    $projectRoot . '/.env',
                ];
                if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                    $candidates[] = dirname($docRoot) . DIRECTORY_SEPARATOR . '.env';
                    $candidates[] = dirname($docRoot) . '/.env';
                    $candidates[] = $docRoot . DIRECTORY_SEPARATOR . '.env';
                    $candidates[] = $docRoot . '/.env';
                }
                foreach ($candidates as $candidate) {
                    if (file_exists($candidate) && is_readable($candidate) && !is_dir($candidate) && basename($candidate) === '.env') {
                        $filePath = $candidate;
                        break;
                    }
                }
            }
        }

        // Strictly verify that the file exists, is readable, and is .env (never .env.example)
        if (!file_exists($filePath) || !is_readable($filePath) || is_dir($filePath) || basename($filePath) !== '.env') {
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
        self::$loadedPath = realpath($filePath) ?: $filePath;
    }

    /**
     * Force reload environment variables (e.g. after environment switch)
     */
    public static function reload(?string $filePath = null): void
    {
        self::$loaded = false;
        self::$loadedPath = null;
        self::load($filePath);
    }

    /**
     * Get path of the active loaded .env file
     */
    public static function getLoadedPath(): ?string
    {
        return self::$loadedPath;
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
     * Check if the application is running in production mode
     */
    public static function isProduction(): bool
    {
        $env = strtolower((string)self::get('APP_ENV', 'development'));
        return in_array($env, ['production', 'prod', 'live'], true);
    }

    /**
     * Central configuration validation
     * Validates required environment variables based on runtime environment (development vs production).
     *
     * @return array List of non-fatal warnings
     * @throws \RuntimeException On missing required production configuration
     */
    public static function validate(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        $warnings = [];
        $isProduction = self::isProduction();

        if ($isProduction) {
            // 1. Strict database validation in production
            $missingDb = [];
            $dbHost = self::get('DB_HOST');
            $dbName = self::get('DB_DATABASE');
            $dbUser = self::get('DB_USERNAME');
            $dbPass = self::get('DB_PASSWORD');

            if ($dbHost === null || trim((string)$dbHost) === '') {
                $missingDb[] = 'DB_HOST';
            }
            if ($dbName === null || trim((string)$dbName) === '') {
                $missingDb[] = 'DB_DATABASE';
            }
            if ($dbUser === null || trim((string)$dbUser) === '') {
                $missingDb[] = 'DB_USERNAME';
            }
            // In production, database password must be explicitly set and non-empty
            if ($dbPass === null || (string)$dbPass === '') {
                $missingDb[] = 'DB_PASSWORD';
            }

            if (!empty($missingDb)) {
                $isSetupRoute = (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/setup')) 
                    || (isset($_SERVER['argv']) && in_array('--setup-test', $_SERVER['argv'], true));
                if (!$isSetupRoute) {
                    throw new \RuntimeException(
                        'Production Environment Error: Required database configuration is missing in .env (' . implode(', ', $missingDb) . '). Please configure these values on the production server.'
                    );
                } else {
                    $warnings[] = 'Production database configuration missing: ' . implode(', ', $missingDb);
                }
            }

            // 2. Production Debug Mode check
            $debug = self::get('APP_DEBUG');
            if ($debug === true || $debug === 'true' || $debug === '1') {
                $warnings[] = 'APP_DEBUG is enabled in production environment. For security, set APP_DEBUG=false.';
            }

            // 3. Production Mail configuration check
            $mailHost = self::get('MAIL_HOST');
            $mailUser = self::get('MAIL_USERNAME');
            $mailPass = self::get('MAIL_PASSWORD');
            if (empty($mailHost) || empty($mailUser) || empty($mailPass)) {
                $warnings[] = 'Production SMTP settings incomplete: MAIL_HOST, MAIL_USERNAME, or MAIL_PASSWORD is empty.';
            }
        }

        return $warnings;
    }

    /**
     * Check if .env has been loaded
     */
    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
