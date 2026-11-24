<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Language;
use App\Core\Session;
use App\Core\Logger;

class LanguageController extends Controller
{
    /**
     * Handle POST /language/set, GET /language/set, and GET /language/{locale}
     */
    public function setLanguage(Request $request, array $params = []): Response
    {
        $locale = trim((string)($params['locale'] ?? $request->post('locale') ?? $request->get('locale') ?? ''));
        $returnUrl = (string)($request->post('return_url') ?? $request->get('return_url') ?? $_SERVER['HTTP_REFERER'] ?? '');

        // 1. Validate locale against whitelist
        if (!Language::isSupported($locale)) {
            Logger::warning("Invalid language selection attempt: {$locale}");
            if ($request->isAjax()) {
                return $this->json([
                    'success' => false,
                    'error'   => 'Invalid language code requested. Supported codes are: en, hi, mr.'
                ], 422);
            }
            Session::setFlash('danger', 'The selected language is not supported.');
            return $this->redirect($this->sanitizeReturnUrl($returnUrl));
        }

        // 2. Set locale in Language core (updates Session, Cookie, and DB for authenticated user)
        $userId = Session::getUserId();
        Language::setLocale($locale, true, $userId);

        $langInfo = Language::getSupported()[$locale] ?? ['name' => $locale, 'native' => $locale];
        $msg = match($locale) {
            'hi' => "भाषा सफलतापूर्वक बदल दी गई: {$langInfo['native']}",
            'mr' => "भाषा यशस्वीरीत्या बदलली आहे: {$langInfo['native']}",
            default => "Language changed successfully to {$langInfo['name']}."
        };

        $targetUrl = $this->sanitizeReturnUrl($returnUrl);
        Session::setFlash('success', $msg);

        // 3. Return JSON if AJAX request
        if ($request->isAjax()) {
            return $this->json([
                'success'  => true,
                'locale'   => $locale,
                'name'     => $langInfo['name'],
                'native'   => $langInfo['native'],
                'message'  => $msg,
                'redirect' => $targetUrl
            ]);
        }

        return $this->redirect($targetUrl);
    }

    /**
     * Sanitize return URL to strictly prevent Open Redirect vulnerabilities
     * while guaranteeing proper redirects across subfolder deployments.
     */
    private function sanitizeReturnUrl(string $url): string
    {
        $url = trim($url);
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . $host;

        // Fallback default based on role
        $fallbackPath = url('/');
        if (Session::isLoggedIn()) {
            $role = Session::getUserRole();
            $fallbackPath = ($role === 'driver') ? url('/driver/dashboard') : url('/office/dashboard');
        }
        $fallback = (str_starts_with($fallbackPath, 'http://') || str_starts_with($fallbackPath, 'https://'))
            ? $fallbackPath
            : ($baseUrl . $fallbackPath);

        if (empty($url)) {
            return $fallback;
        }

        // Prevent protocol-relative URL exploits like "//evil.com"
        if (str_starts_with($url, '//') || str_starts_with($url, '\\\\')) {
            return $fallback;
        }

        // Prevent javascript: or data: exploits
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            return $fallback;
        }

        // Check if URL is fully qualified external URL
        if (preg_match('/^https?:\/\//i', $url)) {
            $parsed = parse_url($url);
            $parsedHost = explode(':', $parsed['host'] ?? '')[0];
            $appHost = explode(':', $host)[0];
            if (empty($parsedHost) || strtolower($parsedHost) !== strtolower($appHost)) {
                Logger::security("Open redirect prevented in language switcher: " . substr($url, 0, 100));
                return $fallback;
            }
            return $url;
        }

        // Relative path starting with /: resolve safely and prepend base URL
        if (str_starts_with($url, '/')) {
            $resolved = url($url);
            return (str_starts_with($resolved, 'http://') || str_starts_with($resolved, 'https://'))
                ? $resolved
                : ($baseUrl . $resolved);
        }

        return $fallback;
    }
}
