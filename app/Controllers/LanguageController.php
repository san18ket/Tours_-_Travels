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
     * Handle POST /language/set
     */
    public function setLanguage(Request $request): Response
    {
        $locale = trim((string)$request->post('locale', ''));
        $returnUrl = (string)$request->post('return_url', '');

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
     */
    private function sanitizeReturnUrl(string $url): string
    {
        $url = trim($url);

        // Fallback default based on role
        $fallback = url('/');
        if (Session::isLoggedIn()) {
            $role = Session::getUserRole();
            $fallback = ($role === 'driver') ? url('/driver/dashboard') : url('/office/dashboard');
        }

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
            $appHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $parsed = parse_url($url);
            if (empty($parsed['host']) || strtolower($parsed['host']) !== strtolower($appHost)) {
                Logger::security("Open redirect prevented in language switcher: " . substr($url, 0, 100));
                return $fallback;
            }
            return $url;
        }

        // Must be a relative path starting with /
        if (str_starts_with($url, '/')) {
            return $url;
        }

        return $fallback;
    }
}
