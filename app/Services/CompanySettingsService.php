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
     * Check if a valid, non-fallback company name is configured and available
     */
    public static function hasCompanyName(): bool
    {
        $p = self::getProfile();
        if (!empty($p['is_fallback'])) {
            return false;
        }
        $name = trim((string)($p['company_name'] ?? ''));
        return $name !== '' && $name !== 'Company name not configured' && strtolower($name) !== '(company name)';
    }

    /**
     * Get display company name with neutral placeholder '(company name)' when unconfigured
     */
    public static function getDisplayName(): string
    {
        return self::hasCompanyName() ? self::getCompanyName() : '(company name)';
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
            'id'              => null,
            'company_name'    => $appName,
            'owner_name'      => env('COMPANY_OWNER', ''),
            'tagline'         => $tagline,
            'primary_color'   => env('THEME_PRIMARY_COLOR', '#176B5B'),
            'secondary_color' => env('THEME_SECONDARY_COLOR', '#16443B'),
            'accent_color'    => env('THEME_ACCENT_COLOR', '#D6B77A'),
            'logo'            => null,
            'paid_stamp'      => null,
            'signature'       => null,
            'signature_stamp' => null,
            'email'           => $email,
            'phone'           => $phone,
            'alternate_phone' => env('COMPANY_ALT_PHONE', ''),
            'website'         => $website,
            'gst_number'      => env('COMPANY_GSTIN', ''),
            'pan_number'      => env('COMPANY_PAN', ''),
            'address'         => $address,
            'city'            => env('COMPANY_CITY', ''),
            'state'           => env('COMPANY_STATE', ''),
            'pin_code'        => env('COMPANY_PIN', ''),
            'status'          => 'active',
            'is_primary'      => 1,
            'is_fallback'     => true,
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
     * Get active owner/contact person name
     */
    public static function getOwnerName(): string
    {
        $p = self::getProfile();
        return !empty($p['owner_name']) ? (string)$p['owner_name'] : '';
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
     * Get active brand theme colors for dynamic website and email branding
     */
    public static function getThemeColors(): array
    {
        $p = self::getProfile();
        $primary = !empty($p['primary_color']) ? (string)$p['primary_color'] : env('THEME_PRIMARY_COLOR', '#176B5B');
        $secondary = !empty($p['secondary_color']) ? (string)$p['secondary_color'] : env('THEME_SECONDARY_COLOR', '#16443B');
        $accent = !empty($p['accent_color']) ? (string)$p['accent_color'] : env('THEME_ACCENT_COLOR', '#D6B77A');

        return [
            'primary'   => $primary,
            'secondary' => $secondary,
            'accent'    => $accent,
            'bg_light'  => '#F7F8F4',
            'surface'   => '#FFFFFF',
            'text_dark' => '#20352F',
            'text_muted'=> '#64748B',
            'border'    => '#E2E8F0',
        ];
    }

    public static function getPrimaryColor(): string
    {
        return self::getThemeColors()['primary'];
    }

    public static function getSecondaryColor(): string
    {
        return self::getThemeColors()['secondary'];
    }

    public static function getAccentColor(): string
    {
        return self::getThemeColors()['accent'];
    }

    /**
     * Helper to resolve web URL for any company storage image
     */
    public static function formatImageUrl(?string $path, bool $absolute = false): ?string
    {
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
     * Check if a specific image file exists physically on disk
     */
    public static function fileExistsInStorage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        $clean = preg_replace('#^(public/storage/|storage/|public/)#', '', $clean);
        $fullPath = dirname(__DIR__, 2) . '/public/storage/' . $clean;
        return file_exists($fullPath) && is_file($fullPath);
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
     * Get absolute or relative web URL for company logo
     */
    public static function getLogoUrl(bool $absolute = false): ?string
    {
        return self::formatImageUrl(self::getLogoPath(), $absolute);
    }

    /**
     * Check if company has an uploaded custom logo on disk
     */
    public static function hasLogo(): bool
    {
        return self::fileExistsInStorage(self::getLogoPath());
    }

    /**
     * Get Paid Stamp storage path
     */
    public static function getPaidStampPath(): ?string
    {
        $p = self::getProfile();
        return !empty($p['paid_stamp']) ? (string)$p['paid_stamp'] : null;
    }

    /**
     * Get Paid Stamp Web URL
     */
    public static function getPaidStampUrl(bool $absolute = false): ?string
    {
        return self::formatImageUrl(self::getPaidStampPath(), $absolute);
    }

    /**
     * Check if Paid Stamp is configured and file exists
     */
    public static function hasPaidStamp(): bool
    {
        return self::fileExistsInStorage(self::getPaidStampPath());
    }

    /**
     * Get Authorized Signature storage path
     */
    public static function getSignaturePath(): ?string
    {
        $p = self::getProfile();
        return !empty($p['signature']) ? (string)$p['signature'] : null;
    }

    /**
     * Get Authorized Signature Web URL
     */
    public static function getSignatureUrl(bool $absolute = false): ?string
    {
        return self::formatImageUrl(self::getSignaturePath(), $absolute);
    }

    /**
     * Check if Authorized Signature is configured and file exists
     */
    public static function hasSignature(): bool
    {
        return self::fileExistsInStorage(self::getSignaturePath());
    }

    /**
     * Get Signature + Stamp storage path
     */
    public static function getSignatureStampPath(): ?string
    {
        $p = self::getProfile();
        return !empty($p['signature_stamp']) ? (string)$p['signature_stamp'] : null;
    }

    /**
     * Get Signature + Stamp Web URL
     */
    public static function getSignatureStampUrl(bool $absolute = false): ?string
    {
        return self::formatImageUrl(self::getSignatureStampPath(), $absolute);
    }

    /**
     * Check if Signature + Stamp is configured and file exists
     */
    public static function hasSignatureStamp(): bool
    {
        return self::fileExistsInStorage(self::getSignatureStampPath());
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
     * Get active alternate mobile number
     */
    public static function getAlternatePhone(): string
    {
        $p = self::getProfile();
        return !empty($p['alternate_phone']) ? (string)$p['alternate_phone'] : '';
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
     * Get active company GST number
     */
    public static function getGstNumber(): string
    {
        $p = self::getProfile();
        return !empty($p['gst_number']) ? (string)$p['gst_number'] : '';
    }

    /**
     * Get active company PAN number
     */
    public static function getPanNumber(): string
    {
        $p = self::getProfile();
        return !empty($p['pan_number']) ? (string)$p['pan_number'] : '';
    }

    /**
     * Get active company office address line
     */
    public static function getAddress(): string
    {
        $p = self::getProfile();
        return !empty($p['address']) ? (string)$p['address'] : '';
    }

    /**
     * Get active company city
     */
    public static function getCity(): string
    {
        $p = self::getProfile();
        return !empty($p['city']) ? (string)$p['city'] : '';
    }

    /**
     * Get active company state
     */
    public static function getState(): string
    {
        $p = self::getProfile();
        return !empty($p['state']) ? (string)$p['state'] : '';
    }

    /**
     * Get active company pin code
     */
    public static function getPinCode(): string
    {
        $p = self::getProfile();
        return !empty($p['pin_code']) ? (string)$p['pin_code'] : '';
    }

    /**
     * Formatted full address string with city, state, and pin code
     */
    public static function getFullAddress(): string
    {
        $parts = [];
        $addr = self::getAddress();
        if (!empty($addr)) {
            $parts[] = $addr;
        }
        $city = self::getCity();
        if (!empty($city)) {
            $parts[] = $city;
        }
        $state = self::getState();
        $pin = self::getPinCode();
        if (!empty($state) && !empty($pin)) {
            $parts[] = "{$state} - {$pin}";
        } elseif (!empty($state)) {
            $parts[] = $state;
        } elseif (!empty($pin)) {
            $parts[] = "PIN: {$pin}";
        }

        return implode(', ', $parts);
    }

    /**
     * Get active company status
     */
    public static function getStatus(): string
    {
        $p = self::getProfile();
        return !empty($p['status']) ? (string)$p['status'] : 'active';
    }
}
