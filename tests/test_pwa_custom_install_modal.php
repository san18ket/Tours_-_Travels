<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$testName = "Professional Custom PWA Installation Experience Test Suite";
echo "======================================================================\n";
echo "   {$testName}\n";
echo "======================================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $msg): void {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

// ----------------------------------------------------------------------
// 1. MANIFEST & SERVICE WORKER PRESERVATION
// ----------------------------------------------------------------------
echo "\n--- 1. MANIFEST & SERVICE WORKER PRESERVATION ---\n";

$manifestPath = dirname(__DIR__) . '/public/manifest.json';
assertCondition(file_exists($manifestPath), "public/manifest.json exists");
$manifest = json_decode(file_get_contents($manifestPath), true);
assertCondition(is_array($manifest), "manifest.json contains valid JSON");
assertCondition(($manifest['name'] ?? '') === 'Tours', "Manifest name is preserved as 'Tours'");
assertCondition(($manifest['display'] ?? '') === 'standalone', "Manifest display mode is 'standalone'");
assertCondition(($manifest['start_url'] ?? '') === './', "Manifest start_url is preserved");
assertCondition(count($manifest['icons'] ?? []) >= 2, "Manifest icons are configured (192px and 512px)");

$swPath = dirname(__DIR__) . '/public/sw.js';
assertCondition(file_exists($swPath), "public/sw.js exists");
$swContent = file_get_contents($swPath);
assertCondition(str_contains($swContent, 'caches.open'), "Service worker preserves offline caching");
assertCondition(str_contains($swContent, 'Connection Offline'), "Service worker preserves offline fallback screen");
assertCondition(!str_contains($swContent, "addEventListener('push'"), "Service worker does not contain push notification listeners");

// ----------------------------------------------------------------------
// 2. MODAL CSS & INDETERMINATE ANIMATIONS
// ----------------------------------------------------------------------
echo "\n--- 2. MODAL CSS & INDETERMINATE ANIMATIONS ---\n";

$cssPath = dirname(__DIR__) . '/public/assets/css/tours-pwa.css';
assertCondition(file_exists($cssPath), "public/assets/css/tours-pwa.css exists");
$cssContent = file_get_contents($cssPath);

assertCondition(str_contains($cssContent, '.pwa-install-backdrop'), "CSS defines .pwa-install-backdrop");
assertCondition(str_contains($cssContent, '.pwa-install-card'), "CSS defines .pwa-install-card");
assertCondition(str_contains($cssContent, '.pwa-progress-track'), "CSS defines .pwa-progress-track");
assertCondition(str_contains($cssContent, '.pwa-progress-bar-indeterminate'), "CSS defines .pwa-progress-bar-indeterminate");
assertCondition(str_contains($cssContent, '@keyframes pwaIndeterminateSweep'), "CSS defines @keyframes pwaIndeterminateSweep");
assertCondition(str_contains($cssContent, '.pwa-btn-install-primary'), "CSS defines .pwa-btn-install-primary");
assertCondition(str_contains($cssContent, '.pwa-success-emblem-wrap'), "CSS defines .pwa-success-emblem-wrap");
assertCondition(str_contains($cssContent, '@media (max-width: 480px)'), "CSS defines mobile @media (max-width: 480px) adjustments");

// ----------------------------------------------------------------------
// 3. JAVASCRIPT STATE MACHINE & NATIVE API
// ----------------------------------------------------------------------
echo "\n--- 3. JAVASCRIPT STATE MACHINE & NATIVE API ---\n";

$jsPath = dirname(__DIR__) . '/public/assets/js/tours-pwa.js';
assertCondition(file_exists($jsPath), "public/assets/js/tours-pwa.js exists");
$jsContent = file_get_contents($jsPath);

assertCondition(str_contains($jsContent, 'beforeinstallprompt'), "JavaScript listens for 'beforeinstallprompt'");
assertCondition(str_contains($jsContent, 'appinstalled'), "JavaScript listens for 'appinstalled'");
assertCondition(str_contains($jsContent, 'triggerToursInstall'), "JavaScript defines window.triggerToursInstall");
assertCondition(str_contains($jsContent, 'openToursInstallModal'), "JavaScript defines window.openToursInstallModal");
assertCondition(str_contains($jsContent, 'closeToursInstallModal'), "JavaScript defines window.closeToursInstallModal");
assertCondition(str_contains($jsContent, 'startToursInstallation'), "JavaScript defines window.startToursInstallation");
assertCondition(str_contains($jsContent, 'promptEvent.prompt()'), "JavaScript invokes native deferredPrompt.prompt()");
assertCondition(str_contains($jsContent, 'promptEvent.userChoice'), "JavaScript awaits promptEvent.userChoice");

// Content & flow checks
assertCondition(str_contains($jsContent, 'Install Tours'), "Stage 1: Modal title contains 'Install Tours'");
assertCondition(str_contains($jsContent, 'Install Tours &amp; Travels on your device for a convenient, dedicated app experience.') || str_contains($jsContent, 'dedicated app experience'), "Stage 1: Modal description contains installation copy");
assertCondition(str_contains($jsContent, 'Installing Tours'), "Stage 2: Progress screen title contains 'Installing Tours'");
assertCondition(str_contains($jsContent, 'Preparing your application...'), "Stage 2: Progress description contains 'Preparing your application...'");
assertCondition(str_contains($jsContent, 'Installing...'), "Stage 2: Status text contains 'Installing...'");
assertCondition(str_contains($jsContent, 'Tours Successfully Installed!'), "Stage 3: Success screen title contains 'Tours Successfully Installed!'");
assertCondition(str_contains($jsContent, 'Your application is ready. You can now open Tours directly from your device.'), "Stage 3: Success description contains readiness copy");
assertCondition(str_contains($jsContent, 'Installation was cancelled. You can try again whenever you are ready.'), "Cancellation handling contains friendly retry message");
assertCondition(str_contains($jsContent, 'Tours is already installed'), "Already installed state contains 'Tours is already installed'");
assertCondition(str_contains($jsContent, 'Add to Home Screen'), "Unsupported guide handles iOS Safari 'Add to Home Screen'");
assertCondition(str_contains($jsContent, 'Install app'), "Unsupported guide handles Android Chrome 'Install app'");

// Negative constraints (no fake percentages, no alerts)
assertCondition(!preg_match('/33%|65%|96%/', $jsContent), "JavaScript does NOT contain fake progress percentages (33%, 65%, 96%)");
assertCondition(!str_contains($jsContent, 'window.alert(') && !str_contains($jsContent, 'alert('), "JavaScript does NOT use browser alert()");
assertCondition(!str_contains($jsContent, 'window.confirm(') && !str_contains($jsContent, 'confirm('), "JavaScript does NOT use browser confirm()");

// ----------------------------------------------------------------------
// 4. NAVBAR INTEGRATION ACROSS LAYOUTS
// ----------------------------------------------------------------------
echo "\n--- 4. NAVBAR INTEGRATION ACROSS LAYOUTS ---\n";

$officeLayout = file_get_contents(dirname(__DIR__) . '/app/Views/layouts/office.php');
assertCondition(str_contains($officeLayout, 'Install Tours'), "office.php profile dropdown contains 'Install Tours'");
assertCondition(str_contains($officeLayout, 'triggerToursInstall'), "office.php invokes triggerToursInstall");
assertCondition(str_contains($officeLayout, 'pwa_install_modal.php'), "office.php includes pwa_install_modal.php component");
assertCondition(str_contains($officeLayout, 'tours-pwa.css'), "office.php links tours-pwa.css");
assertCondition(str_contains($officeLayout, 'tours-pwa.js'), "office.php loads tours-pwa.js");

$driverLayout = file_get_contents(dirname(__DIR__) . '/app/Views/layouts/driver.php');
assertCondition(str_contains($driverLayout, 'Install Tours'), "driver.php profile dropdown contains 'Install Tours'");
assertCondition(str_contains($driverLayout, 'triggerToursInstall'), "driver.php invokes triggerToursInstall");
assertCondition(str_contains($driverLayout, 'pwa_install_modal.php'), "driver.php includes pwa_install_modal.php component");

$mobileTopbar = file_get_contents(dirname(__DIR__) . '/app/Views/components/mobile_topbar.php');
assertCondition(str_contains($mobileTopbar, 'Install Tours'), "mobile_topbar.php menu contains 'Install Tours'");
assertCondition(str_contains($mobileTopbar, 'triggerToursInstall'), "mobile_topbar.php invokes triggerToursInstall");

// ----------------------------------------------------------------------
// 5. MODAL COMPONENT INTEGRITY
// ----------------------------------------------------------------------
echo "\n--- 5. MODAL COMPONENT INTEGRITY ---\n";

$compPath = dirname(__DIR__) . '/app/Views/components/pwa_install_modal.php';
assertCondition(file_exists($compPath), "pwa_install_modal.php component exists");
$compContent = file_get_contents($compPath);
assertCondition(str_contains($compContent, 'id="toursPwaInstallModal"'), "Component defines #toursPwaInstallModal backdrop");
assertCondition(str_contains($compContent, 'class="pwa-install-card"'), "Component defines .pwa-install-card");
assertCondition(str_contains($compContent, 'data-app-name='), "Component provides dynamic data-app-name attribute");
assertCondition(str_contains($compContent, 'data-logo-url='), "Component provides dynamic data-logo-url attribute");

echo "\n======================================================================\n";
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

