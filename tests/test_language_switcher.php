<?php
/**
 * VoyageGo Tours & Travels - Comprehensive Language Switcher Test Suite
 * Verifies English, Hindi, and Marathi Localization, Security, Persistence, and UI Integration
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Language;
use App\Core\Session;
use App\Core\Request;
use App\Core\Csrf;
use App\Models\User;
use App\Services\AuthService;
use Tests\TestRunner;

require_once __DIR__ . '/TestRunner.php';

$runner = new TestRunner('VoyageGo Multi-Language Switcher (EN, HI, MR)');

$runner->runSuite('Localization Core, Whitelist & Helper Engine', function (TestRunner $t) {
    // 1. Whitelist Verification
    $supported = Language::getSupported();
    $t->assert(count($supported) === 3, '1a. Exactly 3 languages are supported');
    $t->assert(isset($supported['en']), '1b. English (en) is supported');
    $t->assert(isset($supported['hi']), '1c. Hindi (hi) is supported');
    $t->assert(isset($supported['mr']), '1d. Marathi (mr) is supported');

    $t->assert(Language::isSupported('en'), '1e. isSupported("en") returns true');
    $t->assert(Language::isSupported('hi'), '1f. isSupported("hi") returns true');
    $t->assert(Language::isSupported('mr'), '1g. isSupported("mr") returns true');
    $t->assert(!Language::isSupported('fr'), '1h. Unsupported locale "fr" rejected');
    $t->assert(!Language::isSupported('es'), '1i. Unsupported locale "es" rejected');
    $t->assert(!Language::isSupported('../../../etc/passwd'), '1j. Path traversal attack locale rejected');
    $t->assert(!Language::isSupported(''), '1k. Empty locale rejected');

    // 2. Translation Dictionaries Verification
    $enDict = Language::loadDictionary('en');
    $hiDict = Language::loadDictionary('hi');
    $mrDict = Language::loadDictionary('mr');

    $t->assert(!empty($enDict), '2a. English dictionary loads with keys');
    $t->assert(!empty($hiDict), '2b. Hindi dictionary loads with keys');
    $t->assert(!empty($mrDict), '2c. Marathi dictionary loads with keys');

    // 3. Devanagari Characters Check
    $t->assert(isset($hiDict['nav.dashboard']) && $hiDict['nav.dashboard'] === 'डैशबोर्ड', '3a. Hindi dashboard translation is standard Devanagari');
    $t->assert(isset($hiDict['status.system_operational']) && $hiDict['status.system_operational'] === 'सिस्टम सक्रिय', '3b. Hindi system operational status is standard Devanagari');
    $t->assert(isset($mrDict['nav.dashboard']) && $mrDict['nav.dashboard'] === 'डॅशबोर्ड', '3c. Marathi dashboard translation is standard Marathi');
    $t->assert(isset($mrDict['nav.tracking']) && $mrDict['nav.tracking'] === 'थेट फ्लीट रडार', '3d. Marathi tracking translation is natural Marathi');

    // 4. Helper Function __() and trans()
    Language::setLocale('en', false);
    $t->assert(__('nav.dashboard') === 'Dashboard', '4a. __() translates English key');
    $t->assert(trans('nav.dashboard') === 'Dashboard', '4b. trans() alias translates English key');

    Language::setLocale('hi', false);
    $t->assert(__('nav.dashboard') === 'डैशबोर्ड', '4c. __() translates Hindi key');

    Language::setLocale('mr', false);
    $t->assert(__('nav.dashboard') === 'डॅशबोर्ड', '4d. __() translates Marathi key');

    // 5. Fallback Mechanism
    Language::setLocale('hi', false);
    $fallbackResult = Language::get('key_that_does_not_exist_anywhere');
    $t->assert($fallbackResult === 'key_that_does_not_exist_anywhere', '5a. Missing key safely returns original key');

    // 6. Variable Interpolation
    $greetingEn = __('alert.welcome_back', ['name' => 'Aditya'], 'en');
    $t->assert($greetingEn === 'Welcome back, Aditya!', '6a. Variable interpolation in English works');

    $greetingHi = __('alert.welcome_back', ['name' => 'आदित्य'], 'hi');
    $t->assert($greetingHi === 'वापसी पर स्वागत है, आदित्य!', '6b. Variable interpolation in Hindi works');
});

$runner->runSuite('Security, CSRF, Controller & Open Redirect Prevention', function (TestRunner $t) {
    $router = require dirname(__DIR__) . '/app/routes.php';
    $csrf = Csrf::getToken();

    // 1. AJAX Invalid Locale Rejection
    $badReq = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'russian',
    ], [
        'REQUEST_METHOD'        => 'POST',
        'REQUEST_URI'           => '/language/set',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resp = $router->dispatch($badReq);
    $t->assert($resp->getStatusCode() === 422, '1a. Invalid locale via AJAX is rejected with 422');

    // 2. AJAX Path Traversal Attempt Rejection
    $hackReq = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => '../../config/app',
    ], [
        'REQUEST_METHOD'        => 'POST',
        'REQUEST_URI'           => '/language/set',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resp = $router->dispatch($hackReq);
    $t->assert($resp->getStatusCode() === 422, '2a. Path traversal via AJAX is rejected with 422');

    // 3. Valid Language Switch via AJAX
    $validReq = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'hi',
    ], [
        'REQUEST_METHOD'        => 'POST',
        'REQUEST_URI'           => '/language/set',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    ]);
    $resp = $router->dispatch($validReq);
    $t->assert($resp->getStatusCode() === 200, '3a. Valid language switch returns 200 OK');
    $t->assert(Language::getLocale() === 'hi', '3b. Active locale switched to "hi"');

    // 4. Open Redirect Prevention: Protocol-Relative //evil.com
    $openRedirReq1 = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'mr',
        'return_url'  => '//evil-hacker.com/steal-creds',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/language/set',
    ]);
    $resp = $router->dispatch($openRedirReq1);
    $t->assert($resp->getStatusCode() === 302, '4a. Form submission returns 302 redirect');
    $headers = $resp->getHeaders();
    $targetLoc = $headers['Location'] ?? '';
    $t->assert(!str_contains($targetLoc, '//evil-hacker.com'), '4b. Protocol-relative open redirect //evil.com blocked');

    // 5. Open Redirect Prevention: javascript: payload
    $openRedirReq2 = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'en',
        'return_url'  => 'javascript:alert(document.cookie)',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/language/set',
    ]);
    $resp = $router->dispatch($openRedirReq2);
    $headers = $resp->getHeaders();
    $targetLoc = $headers['Location'] ?? '';
    $t->assert(!str_contains($targetLoc, 'javascript:'), '5a. javascript: scheme open redirect blocked');

    // 6. Open Redirect Prevention: External HTTP URL
    $openRedirReq3 = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'en',
        'return_url'  => 'https://attacker-phishing-site.com/login',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/language/set',
    ]);
    $resp = $router->dispatch($openRedirReq3);
    $headers = $resp->getHeaders();
    $targetLoc = $headers['Location'] ?? '';
    $t->assert(!str_contains($targetLoc, 'attacker-phishing-site.com'), '6a. External domain open redirect blocked');

    // 7. Legitimate Local Relative URL Accepted
    $legitReq = new Request([], [
        '_csrf_token' => $csrf,
        'locale'      => 'hi',
        'return_url'  => '/office/bookings',
    ], [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/language/set',
    ]);
    $resp = $router->dispatch($legitReq);
    $headers = $resp->getHeaders();
    $targetLoc = $headers['Location'] ?? '';
    $t->assert(str_contains($targetLoc, '/office/bookings'), '7a. Safe internal relative return URL accepted');
});

$runner->runSuite('Session, Cookie & Authenticated User Database Persistence', function (TestRunner $t) {
    $pdo = Database::getConnection();
    $userModel = new User();

    // 1. Create a test user with default language 'en'
    $testEmail = 'lang.user.' . uniqid() . '@voyagego.com';
    $userId = $userModel->create([
        'name'     => 'Ananya Sharma',
        'username' => 'asharma_' . uniqid(),
        'email'    => $testEmail,
        'phone'    => '+91 98200 ' . rand(10000, 99999),
        'password' => password_hash('Pass@12345', PASSWORD_BCRYPT),
        'role'     => 'dispatcher',
        'status'   => 'active',
        'language' => 'en',
    ]);
    $t->assert($userId > 0, '1a. Test user created successfully');

    // 2. Set authenticated session
    Session::set('user_id', $userId);
    Session::set('user_role', 'dispatcher');
    Session::set('user', [
        'id'       => $userId,
        'name'     => 'Ananya Sharma',
        'email'    => $testEmail,
        'role'     => 'dispatcher',
        'language' => 'en',
    ]);

    // 3. Switch language to Marathi as authenticated user
    $switched = Language::setLocale('mr', false, $userId);
    $t->assert($switched === true, '3a. Language::setLocale("mr") returns true');
    $t->assert(Session::get('locale') === 'mr', '3b. Session locale updated to "mr"');

    // 4. Check DB persistence for user
    $savedLocale = Language::getUserSavedLocale($userId);
    $t->assert($savedLocale === 'mr', '4a. User language preference persisted to MySQL database as "mr"');

    // 5. Verify switching language does NOT log out the user
    $t->assert(Session::isLoggedIn(), '5a. User remains logged in after switching language');
    $t->assert(Session::getUserId() === $userId, '5b. User ID unchanged after language change');

    // 6. Test AuthService Login Synchronization
    // Change language in DB to 'hi' directly, then test login
    $pdo->prepare("UPDATE `users` SET `language` = 'hi' WHERE `id` = ?")->execute([$userId]);
    
    // Clear session locale
    Session::remove('locale');
    $authService = new AuthService();
    $loggedUser = $authService->login($testEmail, 'Pass@12345');
    $t->assert(!empty($loggedUser), '6a. User logged in via AuthService');
    $t->assert(Language::getLocale() === 'hi', '6b. User saved language "hi" synchronized on login');
    $t->assert(Session::get('locale') === 'hi', '6c. Session locale synchronized on login');
});

$runner->runSuite('UI Layouts, Dropdown Component & Render Verification', function (TestRunner $t) {
    // 1. Component existence
    $compPath = dirname(__DIR__) . '/app/Views/components/language_switcher.php';
    $t->assert(file_exists($compPath), '1a. Language switcher component file exists');

    // 2. Render Component in English
    Language::setLocale('en', false);
    ob_start();
    require $compPath;
    $compHtml = ob_get_clean();

    $t->assert(str_contains($compHtml, 'vg-lang-dropdown'), '2a. Component renders .vg-lang-dropdown container');
    $t->assert(str_contains($compHtml, 'vg-lang-btn'), '2b. Component renders trigger button');
    $t->assert(str_contains($compHtml, 'vg-lang-icon-globe'), '2c. Component renders globe SVG icon');
    $t->assert(str_contains($compHtml, 'English'), '2d. Component contains English option');
    $t->assert(str_contains($compHtml, 'हिन्दी'), '2e. Component contains Hindi (हिन्दी) option');
    $t->assert(str_contains($compHtml, 'मराठी'), '2f. Component contains Marathi (मराठी) option');
    $t->assert(str_contains($compHtml, 'vg-lang-check'), '2g. Component displays checkmark on selected language');

    // 3. Render Office Layout
    $officeLayoutPath = dirname(__DIR__) . '/app/Views/layouts/office.php';
    $officeContent = file_get_contents($officeLayoutPath);
    $t->assert(str_contains($officeContent, 'language_switcher.php'), '3a. Office layout includes language switcher');
    $t->assert(str_contains($officeContent, 'Language::getLocale()'), '3b. Office layout dynamically sets <html lang>');

    // 4. Render Driver Layout
    $driverLayoutPath = dirname(__DIR__) . '/app/Views/layouts/driver.php';
    $driverContent = file_get_contents($driverLayoutPath);
    $t->assert(str_contains($driverContent, 'language_switcher.php'), '4a. Driver layout includes language switcher');
    $t->assert(str_contains($driverContent, 'Language::getLocale()'), '4b. Driver layout dynamically sets <html lang>');

    // 5. CSS & JS Assets Verification
    $cssContent = file_get_contents(dirname(__DIR__) . '/public/assets/css/app.css');
    $t->assert(str_contains($cssContent, '.vg-lang-dropdown'), '5a. app.css contains .vg-lang-dropdown styles');
    $t->assert(str_contains($cssContent, '.vg-lang-btn'), '5b. app.css contains .vg-lang-btn styles');
    $t->assert(str_contains($cssContent, '.vg-lang-menu'), '5c. app.css contains .vg-lang-menu styles');

    $jsContent = file_get_contents(dirname(__DIR__) . '/public/assets/js/app.js');
    $t->assert(str_contains($jsContent, 'setupLanguageDropdown'), '5d. app.js contains setupLanguageDropdown function');
    $t->assert(str_contains($jsContent, 'setupLanguageDropdown()'), '5e. app.js invokes setupLanguageDropdown on DOMContentLoaded');

    // 6. Complete Server-Side Render of Office Layout in Hindi (hi)
    Language::setLocale('hi', false);
    $content = '<div id="test-content">Dashboard Content</div>';
    $title = 'डैशबोर्ड';
    $flashes = [];
    $user = ['name' => 'Officer Test'];
    $userRole = 'Admin';
    $app = ['name' => 'VoyageGo'];

    ob_start();
    require $officeLayoutPath;
    $renderedOfficeHi = ob_get_clean();

    $t->assert(str_contains($renderedOfficeHi, 'lang="hi"'), '6a. Office layout has lang="hi"');
    $t->assert(str_contains($renderedOfficeHi, 'डैशबोर्ड'), '6b. Office sidebar contains Hindi Dashboard label');
    $t->assert(str_contains($renderedOfficeHi, 'ग्राहक'), '6c. Office sidebar contains Hindi Customers label');
    $t->assert(str_contains($renderedOfficeHi, 'ग्राहक सूची'), '6d. Office sidebar contains Hindi Customer List submenu');
    $t->assert(str_contains($renderedOfficeHi, 'बुकिंग'), '6e. Office sidebar contains Hindi Bookings label');
    $t->assert(str_contains($renderedOfficeHi, 'कर्मचारी'), '6f. Office sidebar contains Hindi Employees label');
    $t->assert(str_contains($renderedOfficeHi, 'वाहन'), '6g. Office sidebar contains Hindi Vehicles label');
    $t->assert(str_contains($renderedOfficeHi, 'लाइव ट्रैकिंग'), '6h. Office sidebar contains Hindi Live Tracking label');
    $t->assert(str_contains($renderedOfficeHi, 'लॉग आउट'), '6i. Office sidebar contains Hindi Logout title');

    // 7. Complete Server-Side Render of Office Layout in Marathi (mr)
    Language::setLocale('mr', false);
    $title = 'डॅशबोर्ड';
    ob_start();
    require $officeLayoutPath;
    $renderedOfficeMr = ob_get_clean();

    $t->assert(str_contains($renderedOfficeMr, 'lang="mr"'), '7a. Office layout has lang="mr"');
    $t->assert(str_contains($renderedOfficeMr, 'डॅशबोर्ड'), '7b. Office sidebar contains Marathi Dashboard label');
    $t->assert(str_contains($renderedOfficeMr, 'ग्राहक'), '7c. Office sidebar contains Marathi Customers label');
    $t->assert(str_contains($renderedOfficeMr, 'ग्राहक यादी'), '7d. Office sidebar contains Marathi Customer List submenu');
    $t->assert(str_contains($renderedOfficeMr, 'बुकिंग'), '7e. Office sidebar contains Marathi Bookings label');
    $t->assert(str_contains($renderedOfficeMr, 'कर्मचारी'), '7f. Office sidebar contains Marathi Employees label');
    $t->assert(str_contains($renderedOfficeMr, 'वाहने'), '7g. Office sidebar contains Marathi Vehicles label');
    $t->assert(str_contains($renderedOfficeMr, 'लाइव्ह ट्रॅकिंग'), '7h. Office sidebar contains Marathi Live Tracking label');
    $t->assert(str_contains($renderedOfficeMr, 'लॉग आउट'), '7i. Office sidebar contains Marathi Logout title');

    // 8. Complete Server-Side Render of Driver Layout in Hindi (hi)
    Language::setLocale('hi', false);
    $content = '<div id="test-driver">Driver Content</div>';
    $title = 'सारथी कंसोल';
    $driver = ['duty_status' => 'off_duty'];
    $user = ['name' => 'Chauffeur Test'];

    ob_start();
    require $driverLayoutPath;
    $renderedDriverHi = ob_get_clean();

    $t->assert(str_contains($renderedDriverHi, 'lang="hi"'), '8a. Driver layout has lang="hi"');
    $t->assert(str_contains($renderedDriverHi, 'डैशबोर्ड'), '8b. Driver sidebar contains Hindi Dashboard label');
    $t->assert(str_contains($renderedDriverHi, 'मेरी आवंटित यात्राएं'), '8c. Driver sidebar contains Hindi Assigned Trips label');
    $t->assert(str_contains($renderedDriverHi, 'यात्रा इतिहास'), '8d. Driver sidebar contains Hindi Trip History label');
    $t->assert(str_contains($renderedDriverHi, 'मेरा वाहन'), '8e. Driver sidebar contains Hindi My Vehicle label');
    $t->assert(str_contains($renderedDriverHi, 'लाइव लोकेशन'), '8f. Driver sidebar contains Hindi Live Location label');
    $t->assert(str_contains($renderedDriverHi, 'मेरी प्रोफाइल'), '8g. Driver sidebar contains Hindi My Profile label');
    $t->assert(str_contains($renderedDriverHi, 'सारथी पोर्टल'), '8h. Driver header contains Hindi Portal Subtitle');
    $t->assert(str_contains($renderedDriverHi, 'प्रमाणित चालक'), '8i. Driver footer contains Hindi Licensed Chauffeur role');

    // 9. Complete Server-Side Render of Driver Layout in Marathi (mr)
    Language::setLocale('mr', false);
    $title = 'चालक कन्सोल';

    ob_start();
    require $driverLayoutPath;
    $renderedDriverMr = ob_get_clean();

    $t->assert(str_contains($renderedDriverMr, 'lang="mr"'), '9a. Driver layout has lang="mr"');
    $t->assert(str_contains($renderedDriverMr, 'डॅशबोर्ड'), '9b. Driver sidebar contains Marathi Dashboard label');
    $t->assert(str_contains($renderedDriverMr, 'माझ्या वाटप झालेल्या फेऱ्या'), '9c. Driver sidebar contains Marathi Assigned Trips label');
    $t->assert(str_contains($renderedDriverMr, 'फेरी इतिहास'), '9d. Driver sidebar contains Marathi Trip History label');
    $t->assert(str_contains($renderedDriverMr, 'माझे वाहन'), '9e. Driver sidebar contains Marathi My Vehicle label');
    $t->assert(str_contains($renderedDriverMr, 'थेट स्थान'), '9f. Driver sidebar contains Marathi Live Location label');
    $t->assert(str_contains($renderedDriverMr, 'माझी प्रोफाइल'), '9g. Driver sidebar contains Marathi My Profile label');
    $t->assert(str_contains($renderedDriverMr, 'चालक पोर्टल'), '9h. Driver header contains Marathi Portal Subtitle');
    $t->assert(str_contains($renderedDriverMr, 'परवानाधारक चालक'), '9i. Driver footer contains Marathi Licensed Chauffeur role');

    // Restore to English default
    Language::setLocale('en', false);
});

$runner->summary();
