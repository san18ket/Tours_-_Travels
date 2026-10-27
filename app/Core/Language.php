<?php
namespace App\Core;

use PDO;
use Throwable;

class Language
{
    public const DEFAULT_LOCALE = 'en';

    public const SUPPORTED = [
        'en' => [
            'code'      => 'en',
            'name'      => 'English',
            'native'    => 'English',
            'short'     => 'EN',
            'flag'      => 'EN',
            'direction' => 'ltr',
        ],
        'hi' => [
            'code'      => 'hi',
            'name'      => 'Hindi',
            'native'    => 'हिन्दी',
            'short'     => 'HI',
            'flag'      => 'HI',
            'direction' => 'ltr',
        ],
        'mr' => [
            'code'      => 'mr',
            'name'      => 'Marathi',
            'native'    => 'मराठी',
            'short'     => 'MR',
            'flag'      => 'MR',
            'direction' => 'ltr',
        ],
    ];

    public const COOKIE_NAME = 'voyagego_locale';
    public const COOKIE_LIFETIME = 31536000; // 365 days

    /**
     * @var string|null Active locale cache
     */
    private static ?string $currentLocale = null;

    /**
     * @var array<string, array<string, string>> Cached dictionary arrays
     */
    private static array $dictionaries = [];

    /**
     * Initialize language resolution
     */
    public static function init(): string
    {
        // 1. Authenticated user's saved language preference, if supported
        $authId = Session::getUserId();
        if ($authId) {
            $userLocale = self::getUserSavedLocale($authId);
            if ($userLocale && self::isSupported($userLocale)) {
                self::$currentLocale = $userLocale;
                Session::set('locale', $userLocale);
                self::setCookie($userLocale);
                return self::$currentLocale;
            }
        }

        // 2. Valid session preference
        if (Session::has('locale')) {
            $sessLocale = (string)Session::get('locale');
            if (self::isSupported($sessLocale)) {
                self::$currentLocale = $sessLocale;
                return self::$currentLocale;
            }
        }

        // 3. Validated language cookie for returning visitors
        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            $cookieLocale = (string)$_COOKIE[self::COOKIE_NAME];
            if (self::isSupported($cookieLocale)) {
                self::$currentLocale = $cookieLocale;
                Session::set('locale', $cookieLocale);
                return self::$currentLocale;
            }
        }

        // 4. English default fallback
        self::$currentLocale = self::DEFAULT_LOCALE;
        Session::set('locale', self::DEFAULT_LOCALE);
        return self::$currentLocale;
    }

    /**
     * Retrieve active locale code (e.g. 'en', 'hi', 'mr')
     */
    public static function getLocale(): string
    {
        if (self::$currentLocale === null) {
            return self::init();
        }
        return self::$currentLocale;
    }

    /**
     * Check if a locale code is supported
     */
    public static function isSupported(?string $locale): bool
    {
        return !empty($locale) && isset(self::SUPPORTED[$locale]);
    }

    /**
     * Retrieve supported languages metadata
     */
    public static function getSupported(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Get details of active language
     */
    public static function getCurrentLanguage(): array
    {
        $loc = self::getLocale();
        return self::SUPPORTED[$loc] ?? self::SUPPORTED[self::DEFAULT_LOCALE];
    }

    /**
     * Set active locale, with optional cookie and DB persistence
     */
    public static function setLocale(string $locale, bool $persistCookie = true, ?int $userId = null): bool
    {
        if (!self::isSupported($locale)) {
            return false;
        }

        self::$currentLocale = $locale;
        Session::set('locale', $locale);

        // Update user session array if exists
        $user = Session::getUser();
        if ($user && is_array($user)) {
            $user['language'] = $locale;
            Session::set('user', $user);
        }

        // Persist cookie
        if ($persistCookie) {
            self::setCookie($locale);
        }

        // Persist in DB for authenticated user
        $targetUserId = $userId ?? Session::getUserId();
        if ($targetUserId) {
            self::saveUserLocale($targetUserId, $locale);
        }

        return true;
    }

    /**
     * Safe persistent cookie setter
     */
    public static function setCookie(string $locale): void
    {
        if (!self::isSupported($locale) || headers_sent()) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $cookieDomain = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $parsedHost = explode(':', $_SERVER['HTTP_HOST'])[0];
            if ($parsedHost !== 'localhost' && !filter_var($parsedHost, FILTER_VALIDATE_IP)) {
                $cookieDomain = $parsedHost;
            }
        }

        setcookie(self::COOKIE_NAME, $locale, [
            'expires'  => time() + self::COOKIE_LIFETIME,
            'path'     => '/',
            'domain'   => $cookieDomain,
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE_NAME] = $locale;
    }

    /**
     * Save user locale into users database table
     */
    public static function saveUserLocale(int $userId, string $locale): bool
    {
        if (!self::isSupported($locale)) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            // Check if column exists first to avoid fatal query error if migration hasn't run
            $stmt = $pdo->prepare("UPDATE `users` SET `language` = ? WHERE `id` = ?");
            return $stmt->execute([$locale, $userId]);
        } catch (Throwable $e) {
            Logger::error("Failed to persist user language preference: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Query user language preference from database
     */
    public static function getUserSavedLocale(int $userId): ?string
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT `language` FROM `users` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$userId]);
            $lang = $stmt->fetchColumn();
            return $lang ? (string)$lang : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Load locale dictionary safely
     */
    public static function loadDictionary(string $locale): array
    {
        if (!self::isSupported($locale)) {
            $locale = self::DEFAULT_LOCALE;
        }

        if (isset(self::$dictionaries[$locale])) {
            return self::$dictionaries[$locale];
        }

        $filepath = dirname(__DIR__) . '/Languages/' . $locale . '.php';
        if (file_exists($filepath)) {
            $data = require $filepath;
            if (is_array($data)) {
                self::$dictionaries[$locale] = $data;
                return self::$dictionaries[$locale];
            }
        }

        self::$dictionaries[$locale] = [];
        return self::$dictionaries[$locale];
    }

    /**
     * Translate a key with optional fallback to English and variable interpolation
     *
     * @param string $key Dictionary key (e.g., 'nav.dashboard' or 'status.completed')
     * @param array<string, mixed> $replace Replacements e.g. ['name' => 'Aditya'] for :name or {name}
     * @param string|null $locale Specific locale to translate to, or null for current
     * @return string
     */
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $targetLocale = ($locale !== null && self::isSupported($locale)) ? $locale : self::getLocale();
        $targetDict = self::loadDictionary($targetLocale);

        $translation = null;
        if (isset($targetDict[$key])) {
            $translation = $targetDict[$key];
        } else {
            // Fallback to English dictionary if not in current locale
            if ($targetLocale !== self::DEFAULT_LOCALE) {
                $enDict = self::loadDictionary(self::DEFAULT_LOCALE);
                if (isset($enDict[$key])) {
                    $translation = $enDict[$key];
                }
            }
        }

        if ($translation === null) {
            $translation = $key;
        }

        if (!empty($replace)) {
            // Sort by placeholder key length descending so longer keys (e.g. :total) are replaced before shorter substrings (e.g. :to)
            uksort($replace, static function ($a, $b) {
                return strlen((string)$b) <=> strlen((string)$a);
            });
            foreach ($replace as $placeholder => $value) {
                $translation = str_replace(
                    [':' . $placeholder, '{' . $placeholder . '}'],
                    (string)$value,
                    $translation
                );
            }
        }

        return $translation;
    }

    /**
     * Retrieve essential UI translations formatted safely for client JavaScript usage
     *
     * @return array<string, string>
     */
    public static function getJsTranslations(): array
    {
        $keys = [
            'js.offline_banner',
            'js.radar_active',
            'js.sync_instant',
            'js.syncing',
            'js.synced',
            'js.sync_failed',
            'js.live_tracking_enabled',
            'js.location_paused',
            'js.switched_language',
            'js.network_restored',
            'js.confirm_delete',
            'js.loading',
            'js.no_records',
            'status.gps_live',
            'status.on_duty',
            'status.off_duty',
            'status.on_trip',
            'status.system_operational',
        ];

        $translations = [];
        foreach ($keys as $k) {
            $translations[$k] = self::get($k);
        }
        return $translations;
    }
}
