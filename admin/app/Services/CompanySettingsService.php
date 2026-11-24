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
        $appName = !empty($rawName) ? $rawName : '(company name)';

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
            'app_name_source' => 'company_name',
            'app_name'        => null,
            'app_version'     => env('APP_VERSION', '2.4.0'),
            'app_icon_source' => 'company_logo',
            'app_icon'        => null,
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
        if (empty($name) || $name === 'Company name not configured') {
            return '(company name)';
        }
        return $name;
    }

    /**
     * Get configured App Name source ('company_name' or 'custom')
     */
    public static function getAppNameSource(): string
    {
        $p = self::getProfile();
        $source = $p['app_name_source'] ?? 'company_name';
        return in_array($source, ['company_name', 'custom'], true) ? $source : 'company_name';
    }

    /**
     * Check if App Name is configured to match Company Name automatically
     */
    public static function isSameAsCompanyName(): bool
    {
        return self::getAppNameSource() === 'company_name';
    }

    /**
     * Get raw custom app name (or empty string if not configured)
     */
    public static function getCustomAppName(): string
    {
        $p = self::getProfile();
        return trim((string)($p['app_name'] ?? ''));
    }

    /**
     * Get effective App Name:
     * - If source is 'custom' and app_name is provided: returns custom app_name
     * - Otherwise: automatically derived from Company Name
     */
    public static function getEffectiveAppName(): string
    {
        if (self::getAppNameSource() === 'custom') {
            $custom = self::getCustomAppName();
            if ($custom !== '') {
                return $custom;
            }
        }
        return self::hasCompanyName() ? self::getCompanyName() : 'Tours & Travels';
    }

    /**
     * Get authoritative App Version (semantic versioning MAJOR.MINOR.PATCH)
     */
    public static function getAppVersion(): string
    {
        $p = self::getProfile();
        $ver = trim((string)($p['app_version'] ?? ''));
        if ($ver !== '' && self::isValidSemanticVersion($ver)) {
            return $ver;
        }
        return env('APP_VERSION', '2.4.0');
    }

    /**
     * Get minimum supported App Version for mandatory update checks
     */
    public static function getMinimumAppVersion(): string
    {
        return self::getAppVersion();
    }

    /**
     * Validate semantic version string (MAJOR.MINOR.PATCH)
     * e.g. 1.0.0, 2.4.0, 10.2.1
     * Rejects: abc, 1, 1.2, v2, version 2, blank
     */
    public static function isValidSemanticVersion(string $version): bool
    {
        return (bool)preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', trim($version));
    }

    /**
     * Compare two semantic versions: returns -1 if v1 < v2, 0 if v1 == v2, 1 if v1 > v2
     */
    public static function compareSemanticVersions(string $v1, string $v2): int
    {
        return version_compare(trim($v1), trim($v2));
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
     * Get configured PWA App Icon source ('company_logo' or 'custom')
     */
    public static function getAppIconSource(): string
    {
        $p = self::getProfile();
        $source = !empty($p['app_icon_source']) ? (string)$p['app_icon_source'] : 'company_logo';
        return in_array($source, ['company_logo', 'custom'], true) ? $source : 'company_logo';
    }

    /**
     * Get relative custom app icon storage path (Canonical: company/pwa/app-icon.<ext>)
     */
    public static function getCustomAppIconPath(): ?string
    {
        $p = self::getProfile();
        if (!empty($p['app_icon'])) {
            $path = (string)$p['app_icon'];
            if (self::fileExistsInStorage($path)) {
                return $path;
            }
        }
        // Canonical disk fallback check: public/storage/company/pwa/app-icon.<ext>
        $projectRoot = dirname(__DIR__, 2);
        $pwaDir = $projectRoot . '/public/storage/company/pwa';
        if (is_dir($pwaDir)) {
            $matches = glob($pwaDir . '/app-icon.*');
            if ($matches && is_file($matches[0])) {
                return 'company/pwa/' . basename($matches[0]);
            }
        }
        return !empty($p['app_icon']) ? (string)$p['app_icon'] : null;
    }

    /**
     * Get absolute or relative web URL for custom app icon
     */
    public static function getCustomAppIconUrl(bool $absolute = false): ?string
    {
        return self::formatImageUrl(self::getCustomAppIconPath(), $absolute);
    }

    /**
     * Check if company has an uploaded custom app icon on disk
     */
    public static function hasCustomAppIcon(): bool
    {
        return self::fileExistsInStorage(self::getCustomAppIconPath());
    }

    /**
     * Get the relative storage path of the active source image for PWA icon generation
     */
    public static function getActiveAppIconSourcePath(): ?string
    {
        if (self::getAppIconSource() === 'custom' && self::hasCustomAppIcon()) {
            return self::getCustomAppIconPath();
        }
        if (self::hasLogo()) {
            return self::getLogoPath();
        }
        return null;
    }

    /**
     * Get relative storage path for 192x192 PWA icon representation (Single Source: company/pwa/app-icon.<ext>)
     */
    public static function getPwaIcon192Path(): string
    {
        return self::getActiveAppIconSourcePath() ?? 'company/pwa/app-icon.png';
    }

    /**
     * Get relative storage path for 512x512 PWA icon representation (Single Source: company/pwa/app-icon.<ext>)
     */
    public static function getPwaIcon512Path(): string
    {
        return self::getActiveAppIconSourcePath() ?? 'company/pwa/app-icon.png';
    }

    /**
     * Get deterministic version hash / timestamp for PWA icon cache busting
     */
    public static function getPwaIconVersion(): string
    {
        $source = self::getActiveAppIconSourcePath();
        if ($source) {
            $cleanPath = ltrim(str_replace('\\', '/', $source), '/');
            $cleanPath = preg_replace('#^(public/storage/|storage/|public/)#', '', $cleanPath);
            $fullPath = dirname(__DIR__, 2) . '/public/storage/' . $cleanPath;
            if (file_exists($fullPath)) {
                return (string)filemtime($fullPath);
            }
        }
        $p = self::getProfile();
        if (!empty($p['updated_at'])) {
            return (string)strtotime($p['updated_at']);
        }
        return '1.0';
    }

    /**
     * Get Web URL for 192x192 PWA icon (Single Source: company/pwa/app-icon.<ext>)
     */
    public static function getPwaIcon192Url(bool $absolute = false): string
    {
        $source = self::getActiveAppIconSourcePath();
        if ($source && self::fileExistsInStorage($source)) {
            return self::formatImageUrl($source, $absolute) . '?v=' . self::getPwaIconVersion();
        }
        return asset('icons/icon-192.png');
    }

    /**
     * Get Web URL for 512x512 PWA icon (Single Source: company/pwa/app-icon.<ext>)
     */
    public static function getPwaIcon512Url(bool $absolute = false): string
    {
        $source = self::getActiveAppIconSourcePath();
        if ($source && self::fileExistsInStorage($source)) {
            return self::formatImageUrl($source, $absolute) . '?v=' . self::getPwaIconVersion();
        }
        return asset('icons/icon-512.png');
    }

    /**
     * Get preview Web URL for current PWA application icon in settings & modal
     */
    public static function getPwaIconPreviewUrl(): string
    {
        $source = self::getActiveAppIconSourcePath();
        if ($source && self::fileExistsInStorage($source)) {
            return self::formatImageUrl($source) . '?v=' . self::getPwaIconVersion();
        }
        return asset('icons/icon-192.png');
    }

    /**
     * Check if PWA has a configured logo or custom icon
     */
    public static function hasPwaAppIcon(): bool
    {
        if (self::getAppIconSource() === 'custom') {
            return self::hasCustomAppIcon();
        }
        return self::hasLogo();
    }

    /**
     * Validate source image for PWA icon usage without permanently storing duplicate 192/512 files
     */
    public static function generatePwaIconsFromSource(?string $sourceRelPath): bool
    {
        if (empty($sourceRelPath)) {
            return false;
        }
        return self::fileExistsInStorage($sourceRelPath);
    }

    /**
     * Synchronize PWA manifest and clean up any obsolete duplicate PWA icons
     */
    public static function syncPwaIcons(): void
    {
        // Clean up any legacy or duplicate PWA icons to ensure single canonical storage (app-icon.<ext>)
        $projectRoot = dirname(__DIR__, 2);
        $legacyFiles = [
            $projectRoot . '/public/storage/company/pwa_icon_192.png',
            $projectRoot . '/public/storage/company/pwa_icon_512.png',
            $projectRoot . '/public/storage/company/pwa/pwa_icon_192.png',
            $projectRoot . '/public/storage/company/pwa/pwa_icon_512.png',
        ];
        foreach ($legacyFiles as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }

        self::updateManifestFile();
    }

    /**
     * Construct dynamic PWA Web App Manifest data array
     */
    public static function getManifestData(): array
    {
        $appName = self::getEffectiveAppName();
        $shortName = mb_strlen($appName) > 20 ? mb_substr($appName, 0, 18) . '..' : $appName;
        $tagline = self::getTagline();
        $desc = !empty($tagline) ? $tagline : ($appName . ' Fleet Operations, Booking & Driver Suite');

        $primaryColor = self::getPrimaryColor();
        $ver = self::getPwaIconVersion();

        $sourcePath = self::getActiveAppIconSourcePath();
        if (!empty($sourcePath) && self::fileExistsInStorage($sourcePath)) {
            $manifestIcon = 'storage/' . ltrim(str_replace('\\', '/', $sourcePath), '/') . '?v=' . $ver;
            $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : (($ext === 'webp') ? 'image/webp' : 'image/png');
        } else {
            $manifestIcon = 'assets/icons/icon-192.png';
            $mime = 'image/png';
        }

        return [
            'name'             => $appName,
            'short_name'       => $shortName,
            'description'      => $desc,
            'start_url'        => './',
            'scope'            => './',
            'display'          => 'standalone',
            'background_color' => '#0B192C',
            'theme_color'      => $primaryColor,
            'orientation'      => 'any',
            'icons'            => [
                [
                    'src'     => $manifestIcon,
                    'sizes'   => '192x192',
                    'type'    => $mime,
                    'purpose' => 'any',
                ],
                [
                    'src'     => $manifestIcon,
                    'sizes'   => '192x192',
                    'type'    => $mime,
                    'purpose' => 'maskable',
                ],
                [
                    'src'     => $manifestIcon,
                    'sizes'   => '512x512',
                    'type'    => $mime,
                    'purpose' => 'any',
                ],
                [
                    'src'     => $manifestIcon,
                    'sizes'   => '512x512',
                    'type'    => $mime,
                    'purpose' => 'maskable',
                ],
            ],
        ];
    }

    /**
     * Write updated dynamic manifest to public/manifest.json ensuring static web server requests match DB
     */
    public static function updateManifestFile(): void
    {
        try {
            $manifestPath = dirname(__DIR__, 2) . '/public/manifest.json';
            $data = self::getManifestData();
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                @file_put_contents($manifestPath, $json);
            }
        } catch (\Throwable $e) {
            // Silently handle if filesystem is locked
        }
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
        if (!empty($p['email'])) {
            return (string)$p['email'];
        }
        $envMail = env('MAIL_FROM_ADDRESS');
        if (!empty($envMail)) {
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
        return !empty($p['website']) ? (string)$p['website'] : '';
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
