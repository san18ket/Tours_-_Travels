<?php
namespace App\Services;

use App\Models\CompanyProfile;
use App\Core\Config;

class CompanySettingsService
{
    private static ?array $cachedProfile = null;
    private static bool $isLoaded = false;

    /**
     * Clear request-level in-memory cache (e.g. after update)
     */
    public static function clearCache(): void
    {
        self::$cachedProfile = null;
        self::$isLoaded = false;
    }

    /**
     * Load the primary company profile following strict resolution hierarchy:
     * 1. Database company_profiles record (is_primary = 1 or active)
     * 2. Environment variables (.env)
     * 3. Neutral system defaults (NO hardcoded branding)
     */
    public static function getProfile(): array
    {
        if (self::$isLoaded) {
            return self::$cachedProfile ?? self::getDefaults();
        }

        try {
            $model = new CompanyProfile();
            $dbProfile = $model->getPrimary();
            if ($dbProfile && !empty($dbProfile['company_name'])) {
                self::$cachedProfile = $dbProfile;
                self::$isLoaded = true;
                return self::$cachedProfile;
            }
        } catch (\Throwable $e) {
            // Database might not be initialized or accessible during early setup
        }

        self::$cachedProfile = self::getDefaults();
        self::$isLoaded = true;
        return self::$cachedProfile;
    }

    /**
     * Check if a real database company profile has been configured
     */
    public static function hasConfiguredProfile(): bool
    {
        $p = self::getProfile();
        return empty($p['is_fallback']) && !empty($p['company_name']) && $p['company_name'] !== 'Company name not configured';
    }

    /**
     * Get fallback default profile from environment or neutral defaults
     */
    private static function getDefaults(): array
    {
        $rawName = env('COMPANY_NAME', env('APP_NAME', Config::get('app.name', '')));
        // Never allow legacy hardcoded 'VoyageGo' as fallback
        if (empty($rawName) || str_contains(strtolower($rawName), 'voyagego')) {
            $appName = 'Company name not configured';
        } else {
            $appName = $rawName;
        }

        $tagline = env('COMPANY_TAGLINE', Config::get('app.tagline', ''));
        $email = env('COMPANY_EMAIL', env('MAIL_FROM_ADDRESS', Config::get('app.support_email', '')));
        $phone = env('COMPANY_PHONE', Config::get('app.support_phone', ''));
        $website = env('COMPANY_WEBSITE', Config::get('app.url', ''));
        $address = env('COMPANY_ADDRESS', Config::get('app.company_address', ''));

        return [
            'id'           => null,
            'company_name' => $appName,
            'tagline'      => $tagline,
            'logo'         => null,
            'email'        => $email,
            'phone'        => $phone,
            'website'      => $website,
            'address'      => $address,
            'status'       => 'active',
            'is_primary'   => 1,
            'is_fallback'  => true,
        ];
    }

    /**
     * Get active company name from database single source of truth
     */
    public static function getCompanyName(): string
    {
        $p = self::getProfile();
        $name = !empty($p['company_name']) ? (string)$p['company_name'] : '';
        if (empty($name) || str_contains(strtolower($name), 'voyagego')) {
            return 'Company name not configured';
        }
        return $name;
    }

    /**
     * Get active company tagline
     */
    public static function getTagline(): string
    {
        $p = self::getProfile();
        return !empty($p['tagline']) ? (string)$p['tagline'] : '';
    }

    /**
     * Get relative logo storage path
     */
    public static function getLogoPath(): ?string
    {
        $p = self::getProfile();
        return !empty($p['logo']) ? (string)$p['logo'] : null;
    }

    /**
     * Get absolute web URL for company logo
     */
    public static function getLogoUrl(bool $absolute = false): ?string
    {
        $path = self::getLogoPath();
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim(str_replace('\\', '/', $path), '/');
        $clean = preg_replace('#^(public/storage/|storage/|public/)#', '', $clean);

        if ($absolute) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $scheme . $host;
            return $baseUrl . url('/storage/' . $clean);
        }

        return url('/storage/' . $clean);
    }

    /**
     * Get active official company email
     */
    public static function getCompanyEmail(): string
    {
        $p = self::getProfile();
        if (!empty($p['email']) && !str_contains(strtolower($p['email']), 'voyagego')) {
            return (string)$p['email'];
        }
        $envMail = env('MAIL_FROM_ADDRESS');
        if (!empty($envMail) && !str_contains(strtolower($envMail), 'voyagego')) {
            return $envMail;
        }
        return '';
    }

    /**
     * Get active company phone
     */
    public static function getPhone(): string
    {
        $p = self::getProfile();
        return !empty($p['phone']) ? (string)$p['phone'] : '';
    }

    /**
     * Get active company website
     */
    public static function getWebsite(): string
    {
        $p = self::getProfile();
        $site = !empty($p['website']) ? (string)$p['website'] : '';
        if (str_contains(strtolower($site), 'voyagego')) {
            return '';
        }
        return $site;
    }

    /**
     * Get active company address
     */
    public static function getAddress(): string
    {
        $p = self::getProfile();
        return !empty($p['address']) ? (string)$p['address'] : '';
    }

    /**
     * Get active company status
     */
    public static function getStatus(): string
    {
        $p = self::getProfile();
        return !empty($p['status']) ? (string)$p['status'] : 'active';
    }

    /**
     * Check if company has an uploaded custom logo on disk
     */
    public static function hasLogo(): bool
    {
        $path = self::getLogoPath();
        if (empty($path)) {
            return false;
        }
        $fullPath = dirname(__DIR__, 2) . '/public/storage/' . ltrim(str_replace('\\', '/', $path), '/');
        return file_exists($fullPath);
    }
}
