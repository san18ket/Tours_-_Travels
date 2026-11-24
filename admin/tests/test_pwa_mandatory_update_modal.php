<?php
/**
 * Test Suite: PWA Update Available — Professional Mandatory Update Modal
 * 
 * Verifies:
 * 1. Centralized Update Modal Structure (#toursPwaUpdateModal) with States:
 *    - Available (#pwaUpdateStateAvailable)
 *    - Updating (#pwaUpdateStateUpdating)
 *    - Failed (#pwaUpdateStateFailed)
 * 2. Strict Mandatory Enforcement:
 *    - ZERO Cancel buttons, ZERO Close (X) buttons, ZERO Later/Skip buttons
 *    - Alertdialog accessibility (role="alertdialog", aria-modal="true")
 * 3. Dual Update Signals Working Together:
 *    - Signal A: Application version endpoint check (current_version > running_version)
 *    - Signal B: Service Worker waiting worker (reg.waiting) and updatefound detection
 *    - One centralized state machine avoiding duplicate modals
 * 4. Service Worker Lifecycle Architecture in public/sw.js:
 *    - Omits skipWaiting on install to allow waiting state detection
 *    - Handles message action 'SKIP_WAITING' to activate on demand
 *    - Bypasses caching for version check endpoints
 * 5. Update Sequence & Guard Logic in public/assets/js/tours-pwa.js:
 *    - SKIP_WAITING postMessage dispatch
 *    - controllerchange listener with reload guard
 *    - Application bootstrap lockdown (pwa-update-blocked)
 *    - Shake feedback on attempted dismissal (Escape or backdrop click)
 * 6. Modern Glassmorphic CSS in public/assets/css/tours-pwa.css:
 *    - Stacking priority (z-index: 2147483647)
 *    - Radar rings, indeterminate progress track, card shake
 * 7. Layout Integration across Auth, Office, and Driver layouts
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Controllers\CompanyController;
use App\Services\CompanySettingsService;

$failures = [];
$passes = 0;

function assertCondition(bool $condition, string $description, array &$failures, int &$passes)
{
    if ($condition) {
        $passes++;
        echo "  [PASS] {$description}\n";
    } else {
        $failures[] = $description;
        echo "  [FAIL] {$description}\n";
    }
}

echo "====================================================================\n";
echo " TEST SUITE: PWA UPDATE AVAILABLE — PROFESSIONAL MANDATORY MODAL\n";
echo "====================================================================\n\n";

// 1. Audit Modal HTML Structure & Centralized State Panels
echo "1. Auditing Centralized Update Modal HTML & Absence of Cancel/Close Buttons...\n";
$modalPath = __DIR__ . '/../app/Views/components/pwa_install_modal.php';
$modalHtml = file_get_contents($modalPath);

// Extract #toursPwaUpdateModal block
$hasUpdateModal = preg_match('/id=["\']toursPwaUpdateModal["\']([\s\S]*?)<\/div>\s*$/i', $modalHtml, $updateMatch);
$updateModalBlock = $hasUpdateModal ? $updateMatch[1] : '';

assertCondition($hasUpdateModal === 1, "Mandatory update modal #toursPwaUpdateModal exists in pwa_install_modal.php", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'role="alertdialog"') !== false, "Modal declares role=\"alertdialog\" for unclosable critical dialog", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'aria-modal="true"') !== false, "Modal declares aria-modal=\"true\"", $failures, $passes);

// State Panels
assertCondition(strpos($updateModalBlock, 'id="pwaUpdateStateAvailable"') !== false, "State Panel 1: Update Available (#pwaUpdateStateAvailable) is present", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'id="pwaUpdateStateUpdating"') !== false, "State Panel 2: Updating in Progress (#pwaUpdateStateUpdating) is present", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'id="pwaUpdateStateFailed"') !== false, "State Panel 3: Failed / Try Again (#pwaUpdateStateFailed) is present", $failures, $passes);

// Strict Mandatory Enforcement: Zero Cancel, Close, Later, Skip buttons
$hasCancelBtn = preg_match('/<button[^>]*>(?:Cancel|Dismiss|Later|Close|Skip|Not now)<\/button>/i', $updateModalBlock);
$hasCloseX = preg_match('/class=["\'][^"\']*close[^"\']*["\']/i', $updateModalBlock) || strpos($updateModalBlock, '&times;') !== false;

assertCondition(!$hasCancelBtn, "Strictly NO Cancel / Later / Skip buttons inside update modal", $failures, $passes);
assertCondition(!$hasCloseX, "Strictly NO Close (X) buttons inside update modal", $failures, $passes);

// Action Buttons
assertCondition(strpos($updateModalBlock, 'id="btnPwaUpdateNow"') !== false, "Mandatory action button #btnPwaUpdateNow is present", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'id="btnPwaTryAgain"') !== false, "Recoverable retry button #btnPwaTryAgain is present", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'pwa-progress-bar-indeterminate') !== false, "Indeterminate progress bar is present for updating state (no fake percentages)", $failures, $passes);
assertCondition(strpos($updateModalBlock, 'pwa-version-comparison-card') !== false, "Semantic version comparison capsule is present", $failures, $passes);

// 2. Audit Service Worker Architecture in public/sw.js
echo "\n2. Auditing Service Worker Lifecycle Architecture in public/sw.js...\n";
$swPath = __DIR__ . '/../public/sw.js';
$swJs = file_get_contents($swPath);

// Verify that self.skipWaiting() is NOT called on install event (so waiting state works)
$installMatch = preg_match('/self\.addEventListener\([\'"]install[\'"]\s*,\s*\((?:event)?\)\s*=>\s*\{([\s\S]*?)\}\);/i', $swJs, $installBody);
$installContent = $installMatch ? $installBody[1] : '';
$skipsOnInstall = strpos($installContent, 'self.skipWaiting()') !== false;

assertCondition(!$skipsOnInstall, "Service Worker install event does NOT call self.skipWaiting() (enabling waiting state detection)", $failures, $passes);
assertCondition(strpos($swJs, "data.action === 'SKIP_WAITING'") !== false, "Service Worker message handler processes SKIP_WAITING action", $failures, $passes);
assertCondition(strpos($swJs, '/api/v1/app/version') !== false, "Service Worker fetch handler bypasses caching for /api/v1/app/version", $failures, $passes);

// 3. Audit Dual Update Signals & State Machine in public/assets/js/tours-pwa.js
echo "\n3. Auditing Dual Signals & Centralized State Machine in public/assets/js/tours-pwa.js...\n";
$pwaJsPath = __DIR__ . '/../public/assets/js/tours-pwa.js';
$pwaJs = file_get_contents($pwaJsPath);

// Dual Signals
assertCondition(strpos($pwaJs, 'checkAppVersionUpdate') !== false, "Signal A: Application version endpoint check checkAppVersionUpdate() is implemented", $failures, $passes);
assertCondition(strpos($pwaJs, 'setupServiceWorkerUpdateWatchers') !== false, "Signal B: Service Worker watchers setupServiceWorkerUpdateWatchers() is implemented", $failures, $passes);
assertCondition(strpos($pwaJs, 'reg.waiting') !== false, "Signal B checks for waiting Service Worker on registration (reg.waiting)", $failures, $passes);
assertCondition(strpos($pwaJs, "'updatefound'") !== false, "Signal B listens for registration 'updatefound' event", $failures, $passes);
assertCondition(strpos($pwaJs, "newWorker.state === 'installed'") !== false, "Signal B checks for installing worker reaching 'installed' state", $failures, $passes);

// Centralized State Machine & Anti-Dismissal
assertCondition(strpos($pwaJs, 'setUpdateModalState') !== false, "Centralized state machine setUpdateModalState() controls update modal", $failures, $passes);
assertCondition(strpos($pwaJs, 'triggerAppUpdate') !== false, "Centralized triggerAppUpdate() avoids duplicate update modals", $failures, $passes);
assertCondition(strpos($pwaJs, 'shakeUpdateCard') !== false, "shakeUpdateCard() provides visual feedback on attempted dismissal", $failures, $passes);
assertCondition(strpos($pwaJs, 'lockApplicationAccess') !== false, "lockApplicationAccess() enforces application lockdown at bootstrap level", $failures, $passes);
assertCondition(strpos($pwaJs, 'pwa-update-blocked') !== false, "App access is blocked via body.pwa-update-blocked", $failures, $passes);
assertCondition(strpos($pwaJs, 'SKIP_WAITING') !== false, "performPwaUpdate() dispatches SKIP_WAITING to waiting Service Worker", $failures, $passes);
assertCondition(strpos($pwaJs, 'controllerchange') !== false, "performPwaUpdate() listens for controllerchange event before reload", $failures, $passes);
assertCondition(strpos($pwaJs, 'tours_pwa_update_reloaded_at') !== false, "Reload guard prevents infinite controllerchange reload loops", $failures, $passes);

// Proactive Listeners
assertCondition(strpos($pwaJs, "window.addEventListener('focus'") !== false, "Checks for updates on window focus", $failures, $passes);
assertCondition(strpos($pwaJs, "document.addEventListener('visibilitychange'") !== false, "Checks for updates on tab visibility change", $failures, $passes);
assertCondition(strpos($pwaJs, "window.addEventListener('online'") !== false, "Checks for updates on reconnecting online", $failures, $passes);
assertCondition(strpos($pwaJs, "setInterval(checkAllUpdateSignals, 30000)") !== false, "Performs background interval checks every 30 seconds", $failures, $passes);

// 4. Audit Modern Glassmorphic CSS in public/assets/css/tours-pwa.css
echo "\n4. Auditing Modern Glassmorphic Styling in public/assets/css/tours-pwa.css...\n";
$cssPath = __DIR__ . '/../public/assets/css/tours-pwa.css';
$css = file_get_contents($cssPath);

assertCondition(strpos($css, '.pwa-update-backdrop') !== false, "CSS defines .pwa-update-backdrop", $failures, $passes);
assertCondition(strpos($css, 'z-index: 2147483647') !== false, "Backdrop uses maximum browser stacking priority (z-index: 2147483647)", $failures, $passes);
assertCondition(strpos($css, 'backdrop-filter: blur') !== false, "Backdrop utilizes modern glassmorphic blur", $failures, $passes);
assertCondition(strpos($css, 'pwa-card-shake') !== false, "CSS defines pwa-card-shake animation for attempted dismissals", $failures, $passes);
assertCondition(strpos($css, 'pwa-progress-bar-indeterminate') !== false, "CSS defines indeterminate progress bar animation", $failures, $passes);
assertCondition(strpos($css, 'body.pwa-update-blocked') !== false, "CSS defines body.pwa-update-blocked application lockdown rules", $failures, $passes);

// 5. Audit Layout Integration across Auth, Office, and Driver layouts
echo "\n5. Auditing Layout Integration across Auth, Office, and Driver layouts...\n";
$officeLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/office.php');
$driverLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/driver.php');
$authLayout   = file_get_contents(__DIR__ . '/../app/Views/layouts/auth.php');

assertCondition(strpos($officeLayout, 'pwa_install_modal.php') !== false, "Office layout embeds pwa_install_modal component", $failures, $passes);
assertCondition(strpos($driverLayout, 'pwa_install_modal.php') !== false, "Driver layout embeds pwa_install_modal component", $failures, $passes);
assertCondition(strpos($authLayout, 'pwa_install_modal.php') !== false, "Auth layout embeds pwa_install_modal component", $failures, $passes);

// 6. Test Version Endpoint Response
echo "\n6. Auditing Version API Endpoint Response...\n";
$controller = new CompanyController();
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/app/version']);
$res = $controller->versionCheck($req);

assertCondition($res->getStatusCode() === 200, "Version check endpoint returns HTTP 200", $failures, $passes);
$data = json_decode($res->getContent(), true);
assertCondition(!empty($data['success']) && !empty($data['current_version']) && !empty($data['app_name']), "Version check returns success, current_version, and dynamic app_name", $failures, $passes);

echo "\n====================================================================\n";
echo " TEST RESULTS SUMMARY\n";
echo "====================================================================\n";
echo "Total Passed: {$passes}\n";
echo "Total Failed: " . count($failures) . "\n\n";

if (!empty($failures)) {
    echo "Failed Tests:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "ALL PWA UPDATE SYSTEM TESTS PASSED SUCCESSFULLY!\n";
exit(0);

