<?php
/**
 * Test Suite: AJAX Language Switcher & Live DOM Translation
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Language;
use App\Core\Session;
use App\Core\Request;
use App\Core\Database;
use App\Controllers\LanguageController;

class AjaxLanguageSwitcherTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function assert(bool $condition, string $description): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✓ PASS: {$description}\n";
        } else {
            $this->failed++;
            echo "  ✗ FAIL: {$description}\n";
        }
    }

    public function run(): void
    {
        echo "\n======================================================\n";
        echo " RUNNING SUITE: AJAX Endpoint & JSON Contract Verification\n";
        echo "======================================================\n";

        $controller = new LanguageController();

        // 1. Test AJAX switch to Hindi
        Language::setLocale('en', false);
        $reqHi = new Request([], ['locale' => 'hi'], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $resHi = $controller->setLanguage($reqHi);

        $this->assert($resHi->getStatusCode() === 200, '1a. AJAX POST /language/set (hi) returns 200 OK');
        $this->assert(str_contains($resHi->getHeader('Content-Type') ?? '', 'application/json'), '1b. Content-Type is application/json');

        $bodyHi = json_decode($resHi->getBody(), true);
        $this->assert($bodyHi['success'] === true, '1c. Response success is true');
        $this->assert($bodyHi['locale'] === 'hi', '1d. Response locale is "hi"');
        $this->assert($bodyHi['name'] === 'Hindi', '1e. Response name is "Hindi"');
        $this->assert($bodyHi['native'] === 'हिन्दी', '1f. Response native is "हिन्दी"');
        $this->assert(str_contains($bodyHi['message'], 'सफलतापूर्वक'), '1g. Response message contains Hindi success text');
        $this->assert(Language::getLocale() === 'hi', '1h. Active session locale updated to "hi"');

        // 2. Test AJAX switch to Marathi
        $reqMr = new Request([], ['locale' => 'mr'], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $resMr = $controller->setLanguage($reqMr);

        $this->assert($resMr->getStatusCode() === 200, '2a. AJAX POST /language/set (mr) returns 200 OK');
        $bodyMr = json_decode($resMr->getBody(), true);
        $this->assert($bodyMr['success'] === true, '2b. Response success is true');
        $this->assert($bodyMr['locale'] === 'mr', '2c. Response locale is "mr"');
        $this->assert($bodyMr['name'] === 'Marathi', '2d. Response name is "Marathi"');
        $this->assert($bodyMr['native'] === 'मराठी', '2e. Response native is "मराठी"');
        $this->assert(str_contains($bodyMr['message'], 'यशस्वीरीत्या'), '2f. Response message contains Marathi success text');
        $this->assert(Language::getLocale() === 'mr', '2g. Active session locale updated to "mr"');

        // 3. Test AJAX switch to English
        $reqEn = new Request([], ['locale' => 'en'], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $resEn = $controller->setLanguage($reqEn);

        $this->assert($resEn->getStatusCode() === 200, '3a. AJAX POST /language/set (en) returns 200 OK');
        $bodyEn = json_decode($resEn->getBody(), true);
        $this->assert($bodyEn['success'] === true, '3b. Response success is true');
        $this->assert($bodyEn['locale'] === 'en', '3c. Response locale is "en"');
        $this->assert(str_contains(strtolower($bodyEn['message']), 'changed successfully') || str_contains(strtolower($bodyEn['message']), 'switched successfully'), '3d. Response message contains English text');
        $this->assert(Language::getLocale() === 'en', '3e. Active session locale updated to "en"');

        // 4. Test Invalid locale rejection via AJAX
        $reqInvalid = new Request([], ['locale' => 'french'], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json'
        ]);
        $resInvalid = $controller->setLanguage($reqInvalid);
        $this->assert($resInvalid->getStatusCode() === 422, '4a. Unsupported locale via AJAX returns 422');
        $bodyInvalid = json_decode($resInvalid->getBody(), true);
        $this->assert($bodyInvalid['success'] === false, '4b. Invalid response success is false');

        echo "\n======================================================\n";
        echo " RUNNING SUITE: Component Markup & Client Script Verification\n";
        echo "======================================================\n";

        Language::setLocale('en', false);
        ob_start();
        require dirname(__DIR__) . '/app/Views/components/language_switcher.php';
        $html = ob_get_clean();

        $this->assert(str_contains($html, 'vg-lang-spinner'), '5a. Component includes micro-spinner element');
        $this->assert(str_contains($html, 'vg-lang-fading'), '5b. Component includes smooth cross-fade transition styles');
        $this->assert(str_contains($html, 'vg-lang-toast'), '5c. Component includes floating toast notification styles');
        $this->assert(str_contains($html, 'vgToggleLanguageDropdown'), '5d. Script defines vgToggleLanguageDropdown');
        $this->assert(str_contains($html, 'vgHandleLanguageSelect'), '5e. Script defines vgHandleLanguageSelect');
        $this->assert(str_contains($html, 'vgHandleLanguageSubmit'), '5f. Script defines vgHandleLanguageSubmit');
        $this->assert(str_contains($html, 'vgSwitchLanguage'), '5g. Script defines vgSwitchLanguage AJAX orchestrator');
        $this->assert(str_contains($html, 'vgApplyPageTranslations'), '5h. Script defines vgApplyPageTranslations live DOM updater');
        $this->assert(str_contains($html, 'vgShowLanguageToast'), '5i. Script defines vgShowLanguageToast');
        $this->assert(str_contains($html, 'onsubmit="vgHandleLanguageSubmit(this, event)"'), '5j. Forms intercept submit event via AJAX');
        $this->assert(str_contains($html, 'onclick="vgHandleLanguageSelect('), '5k. Buttons intercept click event via AJAX');

        echo "\n======================================================\n";
        echo " RUNNING SUITE: Post-AJAX HTML Re-render Simulation\n";
        echo "======================================================\n";

        // Switch to Marathi via session
        Language::setLocale('mr', false);

        // Office layout render in Marathi
        ob_start();
        $title = 'चाचणी डॅशबोर्ड';
        $content = '<div class="test">सामग्री</div>';
        $flashes = [];
        $user = ['name' => 'Officer Test'];
        $userRole = 'Admin';
        $app = ['name' => 'VoyageGo'];
        require dirname(__DIR__) . '/app/Views/layouts/office.php';
        $officeHtml = ob_get_clean();

        $this->assert(str_contains($officeHtml, 'lang="mr"'), '6a. Re-rendered page has lang="mr"');
        $this->assert(str_contains($officeHtml, 'डॅशबोर्ड'), '6b. Re-rendered sidebar has Marathi Dashboard');
        $this->assert(str_contains($officeHtml, 'ग्राहक'), '6c. Re-rendered sidebar has Marathi Customers');
        $this->assert(str_contains($officeHtml, 'कर्मचारी'), '6d. Re-rendered sidebar has Marathi Employees');
        $this->assert(str_contains($officeHtml, 'वाहने'), '6e. Re-rendered sidebar has Marathi Vehicles');

        // Reset to English
        Language::setLocale('en', false);

        echo "\n======================================================\n";
        echo " TEST EXECUTION SUMMARY\n";
        echo "======================================================\n";
        echo " Passed: {$this->passed}\n";
        echo " Failed: {$this->failed}\n";
        echo " Total:  " . ($this->passed + $this->failed) . "\n\n";

        if ($this->failed > 0) {
            echo " SOME TESTS FAILED.\n";
            exit(1);
        } else {
            echo " ALL AJAX LANGUAGE SWITCHER TESTS PASSED!\n\n";
        }
    }
}

$suite = new AjaxLanguageSwitcherTest();
$suite->run();
