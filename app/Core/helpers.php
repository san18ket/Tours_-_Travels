<?php
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return \App\Core\Env::get($key, $default);
    }
}

use App\Core\View;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Config;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrfField')) {
    function csrfField(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_meta')) {
    function csrf_meta(): string
    {
        return Csrf::meta();
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Session::getUser();
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::getUser();
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return Session::getUserId();
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string
    {
        return Session::getUserRole();
    }
}

if (!function_exists('format_inr')) {
    /**
     * Format an amount using the Indian numbering system (Lakhs and Crores grouping)
     * e.g. 1000 -> ₹1,000, 25500 -> ₹25,500, 125000 -> ₹1,25,000, 1050000 -> ₹10,50,000
     */
    function format_inr(float|int|string|null $amount, bool $showDecimals = false): string
    {
        if ($amount === null || $amount === '') {
            return '₹0';
        }
        $num = (float)$amount;
        $isNeg = $num < 0;
        $num = abs($num);
        $formattedDec = '';
        if ($showDecimals) {
            $formattedDec = '.' . sprintf('%02d', round(($num - floor($num)) * 100));
        }
        $intStr = (string)(int)floor($num);
        if (strlen($intStr) > 3) {
            $last3 = substr($intStr, -3);
            $rest = substr($intStr, 0, -3);
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $intFormatted = $rest . ',' . $last3;
        } else {
            $intFormatted = $intStr;
        }
        return ($isNeg ? '-' : '') . '₹' . $intFormatted . $formattedDec;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Reusable currency formatting helper displaying consistent Indian Rupee format
     */
    function format_currency(float|int|string|null $amount, bool $showDecimals = false): string
    {
        return format_inr($amount, $showDecimals);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $datetime, string $format = 'd M Y, h:i A'): string
    {
        if (empty($datetime)) {
            return '—';
        }
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (empty($date)) {
            return '—';
        }
        return date($format, strtotime($date));
    }
}

if (!function_exists('is_https')) {
    function is_https(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $visitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
            if (isset($visitor['scheme']) && $visitor['scheme'] === 'https') {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($base === '.' || $base === '') {
            $base = '';
        } elseif (!str_starts_with($base, '/')) {
            $base = '/' . $base;
        }
        $cleanPath = '/' . ltrim($path, '/');

        // Prevent double prefixing if $cleanPath already starts with $base
        if ($base !== '' && ($cleanPath === $base || str_starts_with($cleanPath, $base . '/'))) {
            return $cleanPath;
        }

        return ($base === '' || $base === '/') ? $cleanPath : $base . $cleanPath;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'assets/')) {
            $clean = substr($clean, 7);
        }
        return url('/assets/' . $clean);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): \App\Core\Response
    {
        $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : url($path);
        return \App\Core\Response::redirect($target, $status);
    }
}

if (!function_exists('render_status_badge')) {
    function render_status_badge(string $status, string $context = 'general'): string
    {
        $statusLower = strtolower(trim($status));
        $badgeClass = 'badge-neutral';

        $transKey = 'status.' . $statusLower;
        $label = class_exists('\App\Core\Language') ? \App\Core\Language::get($transKey) : $transKey;
        if ($label === $transKey) {
            $label = ucwords(str_replace('_', ' ', $statusLower));
        }

        switch ($statusLower) {
            case 'confirmed':
            case 'completed':
            case 'available':
            case 'active':
            case 'paid':
            case 'on_duty':
            case 'success':
            case 'published':
                $badgeClass = 'badge-success';
                break;
            case 'pending':
            case 'partially_paid':
            case 'partial':
            case 'assigned':
            case 'driver_accepted':
            case 'unassigned':
            case 'draft':
                $badgeClass = 'badge-warning';
                break;
            case 'on_trip':
            case 'trip_started':
            case 'en_route_pickup':
            case 'in_progress':
                $badgeClass = 'badge-info';
                break;
            case 'cancelled':
            case 'inactive':
            case 'unpaid':
            case 'failed':
            case 'retired':
            case 'terminated':
                $badgeClass = 'badge-danger';
                break;
            case 'maintenance':
            case 'on_leave':
                $badgeClass = 'badge-gold';
                break;
            case 'admin':
            case 'dispatcher':
                $badgeClass = 'badge-navy';
                break;
            case 'off_duty':
            case 'archived':
                $badgeClass = 'badge-neutral';
                break;
        }

        return '<span class="badge ' . e($badgeClass) . '"><span class="badge-dot"></span>' . e($label) . '</span>';
    }
}

if (!function_exists('render_pagination')) {
    function render_pagination(int $currentPage, int $totalPages, int $totalRecords, int $perPage, string $baseUrl = '', array $queryParams = []): string
    {
        if ($totalRecords <= 0) {
            return '';
        }

        $from = (($currentPage - 1) * $perPage) + 1;
        $to = min($currentPage * $perPage, $totalRecords);

        $buildUrl = function(int $page) use ($baseUrl, $queryParams, $perPage) {
            $params = array_merge($queryParams, ['page' => $page, 'per_page' => $perPage]);
            $qs = http_build_query($params);
            return e($baseUrl . ($qs ? '?' . $qs : ''));
        };

        $html = '<div class="pagination-wrapper">';
        $showingText = class_exists('\App\Core\Language') ? \App\Core\Language::get('pagination.showing', [
            'from' => '<strong>' . $from . '</strong>',
            'to' => '<strong>' . $to . '</strong>',
            'total' => '<strong>' . $totalRecords . '</strong>'
        ]) : 'pagination.showing';
        if ($showingText === 'pagination.showing') {
            $showingText = 'Showing <strong>' . $from . '</strong> to <strong>' . $to . '</strong> of <strong>' . $totalRecords . '</strong> entries';
        }
        $html .= '<div class="pagination-info">' . $showingText . '</div>';
        
        $html .= '<div class="pagination-controls">';
        
        $prevTitle = class_exists('\App\Core\Language') ? \App\Core\Language::get('pagination.previous') : 'Previous Page';
        $nextTitle = class_exists('\App\Core\Language') ? \App\Core\Language::get('pagination.next') : 'Next Page';

        // Prev button
        if ($currentPage > 1) {
            $html .= '<a href="' . $buildUrl($currentPage - 1) . '" class="pagination-btn" title="' . e($prevTitle) . '">&lsaquo;</a>';
        } else {
            $html .= '<span class="pagination-btn disabled">&lsaquo;</span>';
        }

        // Page numbers
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);

        if ($startPage > 1) {
            $html .= '<a href="' . $buildUrl(1) . '" class="pagination-btn">1</a>';
            if ($startPage > 2) {
                $html .= '<span class="pagination-btn disabled">...</span>';
            }
        }

        for ($p = $startPage; $p <= $endPage; $p++) {
            if ($p === $currentPage) {
                $html .= '<span class="pagination-btn active">' . $p . '</span>';
            } else {
                $html .= '<a href="' . $buildUrl($p) . '" class="pagination-btn">' . $p . '</a>';
            }
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<span class="pagination-btn disabled">...</span>';
            }
            $html .= '<a href="' . $buildUrl($totalPages) . '" class="pagination-btn">' . $totalPages . '</a>';
        }

        // Next button
        if ($currentPage < $totalPages) {
            $html .= '<a href="' . $buildUrl($currentPage + 1) . '" class="pagination-btn" title="' . e($nextTitle) . '">&rsaquo;</a>';
        } else {
            $html .= '<span class="pagination-btn disabled">&rsaquo;</span>';
        }

        $html .= '</div>'; // pagination-controls
        $html .= '</div>'; // pagination-wrapper

        return $html;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        $role = auth_role();
        if (empty($role)) {
            return false;
        }
        if (in_array(strtolower($role), ['admin', 'owner', 'superadmin'], true)) {
            return true;
        }

        static $permCache = [];
        $cacheKey = $role . ':' . $permission;
        if (isset($permCache[$cacheKey])) {
            return $permCache[$cacheKey];
        }

        try {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM `role_permissions` rp 
                JOIN `roles` r ON r.id = rp.role_id 
                JOIN `permissions` p ON p.id = rp.permission_id 
                WHERE r.slug = ? AND (p.name = ? OR p.name = 'all_access')
            ");
            $stmt->execute([$role, $permission]);
            $has = (int)$stmt->fetchColumn() > 0;
            $permCache[$cacheKey] = $has;
            return $has;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('__')) {
    /**
     * Translate key using active or specified locale
     *
     * @param string $key
     * @param array<string, mixed>|string $replace Replacements array or string fallback default
     * @param string|null $locale
     * @return string
     */
    function __(string $key, array|string $replace = [], ?string $locale = null): string
    {
        if (is_string($replace)) {
            $val = \App\Core\Language::get($key, [], $locale);
            return $val !== $key ? $val : $replace;
        }
        return \App\Core\Language::get($key, $replace, $locale);
    }
}

if (!function_exists('trans')) {
    /**
     * Alias for __() translation helper
     *
     * @param string $key
     * @param array<string, mixed>|string $replace Replacements array or string fallback default
     * @param string|null $locale
     * @return string
     */
    function trans(string $key, array|string $replace = [], ?string $locale = null): string
    {
        if (is_string($replace)) {
            $val = \App\Core\Language::get($key, [], $locale);
            return $val !== $key ? $val : $replace;
        }
        return \App\Core\Language::get($key, $replace, $locale);
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value using dot notation
     */
    function config(string $key, mixed $default = null): mixed
    {
        return \App\Core\Config::get($key, $default);
    }
}

if (!function_exists('render_page_header')) {
    /**
     * Render the shared travel-themed page header component
     *
     * @param array<string, mixed> $config
     * @return string
     */
    function render_page_header(array $config): string
    {
        extract($config);
        ob_start();
        require dirname(__DIR__) . '/Views/components/page_header.php';
        return (string)ob_get_clean();
    }
}

if (!function_exists('storage_url')) {
    /**
     * Resolve public web URL for uploaded media or document stored in public/storage
     */
    function storage_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $clean = ltrim(str_replace('\\', '/', $path), '/');
        $clean = preg_replace('#^(public/storage/|storage/|public/assets/uploads/|assets/uploads/)#', '', $clean);

        return url('/storage/' . $clean);
    }
}

if (!function_exists('customer_avatar_url')) {
    /**
     * Resolve public URL for customer or employee profile avatar
     */
    function customer_avatar_url(?string $path): ?string
    {
        return storage_url($path);
    }
}

if (!function_exists('customer_document_url')) {
    /**
     * Resolve public URL for customer, employee, or vehicle verification document
     */
    function customer_document_url(?string $path): ?string
    {
        return storage_url($path);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Human-friendly relative time difference
     */
    function time_ago(?string $datetime): string
    {
        if (empty($datetime)) {
            return '';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return '';
        }
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = max(1, (int)floor($diff / 60));
            return "{$mins}m ago";
        } elseif ($diff < 86400) {
            $hours = (int)floor($diff / 3600);
            return "{$hours}h ago";
        } elseif ($diff < 604800) {
            $days = (int)floor($diff / 86400);
            return "{$days}d ago";
        } else {
            return date('d M', $timestamp);
        }
    }
}






if (!function_exists('company_name')) {
    function company_name(): string
    {
        return \App\Services\CompanySettingsService::getCompanyName();
    }
}

if (!function_exists('has_company_name')) {
    function has_company_name(): bool
    {
        return \App\Services\CompanySettingsService::hasCompanyName();
    }
}

if (!function_exists('company_display_name')) {
    function company_display_name(): string
    {
        return \App\Services\CompanySettingsService::getDisplayName();
    }
}

if (!function_exists('company_tagline')) {
    function company_tagline(): string
    {
        return \App\Services\CompanySettingsService::getTagline();
    }
}

if (!function_exists('company_email')) {
    function company_email(): string
    {
        return \App\Services\CompanySettingsService::getCompanyEmail();
    }
}

if (!function_exists('company_phone')) {
    function company_phone(): string
    {
        return \App\Services\CompanySettingsService::getPhone();
    }
}

if (!function_exists('company_website')) {
    function company_website(): string
    {
        return \App\Services\CompanySettingsService::getWebsite();
    }
}

if (!function_exists('company_address')) {
    function company_address(): string
    {
        return \App\Services\CompanySettingsService::getAddress();
    }
}

if (!function_exists('company_logo')) {
    function company_logo(bool $absolute = false): ?string
    {
        return \App\Services\CompanySettingsService::getLogoUrl($absolute);
    }
}

if (!function_exists('company_logo_url')) {
    function company_logo_url(bool $absolute = false): ?string
    {
        return \App\Services\CompanySettingsService::getLogoUrl($absolute);
    }
}
if (!function_exists('has_company_logo')) {
    function has_company_logo(): bool
    {
        return \App\Services\CompanySettingsService::hasLogo();
    }
}

if (!function_exists('has_configured_company')) {
    function has_configured_company(): bool
    {
        return \App\Services\CompanySettingsService::hasConfiguredProfile();
    }
}

if (!function_exists('company_owner')) {
    function company_owner(): string
    {
        return \App\Services\CompanySettingsService::getOwnerName();
    }
}

if (!function_exists('company_alternate_phone')) {
    function company_alternate_phone(): string
    {
        return \App\Services\CompanySettingsService::getAlternatePhone();
    }
}

if (!function_exists('company_gst_number')) {
    function company_gst_number(): string
    {
        return \App\Services\CompanySettingsService::getGstNumber();
    }
}

if (!function_exists('company_pan_number')) {
    function company_pan_number(): string
    {
        return \App\Services\CompanySettingsService::getPanNumber();
    }
}

if (!function_exists('company_city')) {
    function company_city(): string
    {
        return \App\Services\CompanySettingsService::getCity();
    }
}

if (!function_exists('company_state')) {
    function company_state(): string
    {
        return \App\Services\CompanySettingsService::getState();
    }
}

if (!function_exists('company_pin_code')) {
    function company_pin_code(): string
    {
        return \App\Services\CompanySettingsService::getPinCode();
    }
}

if (!function_exists('company_full_address')) {
    function company_full_address(): string
    {
        return \App\Services\CompanySettingsService::getFullAddress();
    }
}

if (!function_exists('company_paid_stamp_url')) {
    function company_paid_stamp_url(bool $absolute = false): ?string
    {
        return \App\Services\CompanySettingsService::getPaidStampUrl($absolute);
    }
}

if (!function_exists('has_company_paid_stamp')) {
    function has_company_paid_stamp(): bool
    {
        return \App\Services\CompanySettingsService::hasPaidStamp();
    }
}

if (!function_exists('company_signature_url')) {
    function company_signature_url(bool $absolute = false): ?string
    {
        return \App\Services\CompanySettingsService::getSignatureUrl($absolute);
    }
}

if (!function_exists('has_company_signature')) {
    function has_company_signature(): bool
    {
        return \App\Services\CompanySettingsService::hasSignature();
    }
}

if (!function_exists('company_signature_stamp_url')) {
    function company_signature_stamp_url(bool $absolute = false): ?string
    {
        return \App\Services\CompanySettingsService::getSignatureStampUrl($absolute);
    }
}

if (!function_exists('has_company_signature_stamp')) {
    function has_company_signature_stamp(): bool
    {
        return \App\Services\CompanySettingsService::hasSignatureStamp();
    }
}

