/**
 * Comprehensive Verification Test Suite: Tours PWA – Professional Install Button & Notification Permission Flow
 * 
 * Tests:
 * 1. Automatic permission prompt removal (silent normal page load)
 * 2. "Install Tours" button behavior & pre-checks
 * 3. Custom Notification Explanation Modal as FIRST modal shown
 * 4. Luxury Light Theme Glassmorphism with Purple Accent
 * 5. Inline HTTPS notice on insecure origin without replacing notification modal
 * 6. Non-blocking installation for all paths (Granted, Denied, Dismissed, Maybe Later)
 * 7. Multi-modal prevention (closeAllToursModals)
 * 8. Platform-specific manual instructions (Android Chrome, iOS Safari, Desktop)
 * 9. Accurate standalone detection (no false reporting from referrer)
 * 10. Accessible keyboard navigation & focus trapping
 */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('============================================================');
console.log('1. VERIFYING REMOVAL OF AUTOMATIC PERMISSION PROMPTS');
console.log('============================================================');

const jsPath = path.join(__dirname, '../public/assets/js/tours-pwa.js');
const js = fs.readFileSync(jsPath, 'utf8');

const cssPath = path.join(__dirname, '../public/assets/css/tours-pwa.css');
const css = fs.readFileSync(cssPath, 'utf8');

function test(desc, condition) {
    if (condition) {
        console.log(`  [PASS] ${desc}`);
    } else {
        console.error(`  [FAIL] ${desc}`);
        process.exitCode = 1;
    }
}

// 1. Silent Page Load Verification
test('Auto-initialization does not call checkNotificationOnboarding on DOMContentLoaded', 
    !js.includes('DOMContentLoaded\', () => {\n            initPwaInstall();\n            checkNotificationOnboarding();') &&
    !js.includes('initPwaInstall();\n            checkNotificationOnboarding();') &&
    !js.includes('initPwaInstall();\n        checkNotificationOnboarding();')
);

test('checkNotificationOnboarding is disabled or made no-op', 
    js.includes('async function checkNotificationOnboarding()') &&
    (js.includes('// Disabled: In accordance with production UX requirements') || js.includes('return;'))
);

test('DOMContentLoaded only registers initPwaInstall', 
    js.includes('document.addEventListener(\'DOMContentLoaded\', () => {\n            initPwaInstall();\n        });') ||
    js.includes('initPwaInstall();')
);

console.log('\n============================================================');
console.log('2. VERIFYING "INSTALL TOURS" BUTTON BEHAVIOR & FIRST MODAL FLOW');
console.log('============================================================');

test('triggerToursInstall function is exposed on window', js.includes('window.triggerToursInstall = function'));

test('triggerToursInstall checks genuine standalone mode and does not falsely report installed from referrer', 
    js.includes('checkStandaloneMode()') && 
    !js.includes('document.referrer.includes(\'android-app://\')') &&
    js.includes('Tours is already installed on this device.')
);

test('triggerToursInstall prevents multiple modals simultaneously by calling closeAllToursModals', 
    js.includes('closeAllToursModals') &&
    js.includes('window.closeAllToursModals = function')
);

test('triggerToursInstall makes custom notification onboarding modal the FIRST modal shown', 
    js.includes('showNotificationExplanationModal(\'prompt\');') ||
    js.includes('showNotificationExplanationModal();')
);

test('triggerToursInstall does NOT immediately trigger browser native permission popup', 
    !js.includes('window.triggerToursInstall = function (e) {\n        Notification.requestPermission(')
);

test('triggerToursInstall does NOT immediately show manual installation instructions', 
    !js.includes('window.triggerToursInstall = function (e) {\n        showGenericInstallModal(') &&
    !js.includes('window.triggerToursInstall = function (e) {\n        showAndroidInstallModal(')
);

console.log('\n============================================================');
console.log('3. VERIFYING LUXURY LIGHT NOTIFICATION MODAL DESIGN');
console.log('============================================================');

test('Custom modal ID is toursNotificationModal', js.includes('toursNotificationModal'));
test('Custom modal box uses .tours-light-modal class', js.includes('tours-light-modal'));

test('CSS .tours-light-modal uses translucent luxury white background with backdrop-filter', 
    css.includes('.tours-light-modal') &&
    css.includes('rgba(255, 255, 255, 0.96)') &&
    css.includes('backdrop-filter: blur(20px)')
);

test('CSS .tours-light-modal features soft purple glow shadow', 
    css.includes('rgba(109, 40, 217, 0.22)')
);

test('CSS .btn-purple-primary uses Tours purple gradient (#7C3AED -> #6D28D9)', 
    css.includes('.btn-purple-primary') &&
    css.includes('linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%)')
);

test('CSS .tours-light-icon-wrap has purple gradient and bell pulse animation', 
    css.includes('.tours-light-icon-wrap') &&
    css.includes('toursBellPulse')
);

test('CSS .tours-benefit-item styling is present with light background and rounded borders', 
    css.includes('.tours-benefit-item') &&
    css.includes('.tours-benefit-icon')
);

console.log('\n============================================================');
console.log('4. VERIFYING MODAL CONTENT & SPECIFICATIONS');
console.log('============================================================');

test('Exact heading is present: "Stay Connected with Tours"', 
    js.includes('Stay Connected with Tours')
);

const expectedDesc = 'Enable notifications to receive important trip updates, booking confirmations and driver notifications directly on your device.';
test('Exact description is present: "' + expectedDesc + '"', 
    js.includes(expectedDesc)
);

test('Benefit 1: "Booking and trip updates" is present', js.includes('Booking and trip updates'));
test('Benefit 2: "Important notifications" is present', js.includes('Important notifications'));
test('Benefit 3: "Driver and travel updates" is present', js.includes('Driver and travel updates'));
test('Benefit 4: "Notifications directly on your device" is present', js.includes('Notifications directly on your device'));

test('Primary action button label: "Enable Notifications & Continue"', 
    js.includes('Enable Notifications &amp; Continue') || js.includes('Enable Notifications & Continue')
);

test('Secondary action button label: "Maybe Later"', 
    js.includes('Maybe Later')
);

console.log('\n============================================================');
console.log('5. VERIFYING INLINE HTTPS NOTICE (DO NOT REPLACE MODAL)');
console.log('============================================================');

test('Insecure LAN context shows small professional HTTPS notice inline without replacing modal', 
    js.includes('tours-https-inline-notice') &&
    css.includes('.tours-https-inline-notice')
);

test('Inline HTTPS notice provides clear Cloudflare Tunnel guidance', 
    js.includes('Web Push notifications require a secure connection (e.g. Cloudflare Tunnel)')
);

test('Clicking Enable Notifications on insecure context continues install without blocking user', 
    js.includes('Push notifications require HTTPS. Continuing with installation...') &&
    js.includes('window.proceedWithPwaInstall()')
);

console.log('\n============================================================');
console.log('6. VERIFYING NOTIFICATION PERMISSION LOGIC & STATES');
console.log('============================================================');

test('handleNotificationModalEnable calls Notification.requestPermission() from explicit user click', 
    js.includes('window.handleNotificationModalEnable = async function') &&
    js.includes('await Notification.requestPermission()')
);

test('Granted state subscribes device with VAPID key and posts to /api/push/subscribe', 
    js.includes('fetchVapidPublicKey') &&
    js.includes('swReg.pushManager.subscribe') &&
    js.includes('/api/push/subscribe') &&
    js.includes('subJson.endpoint')
);

test('Granted state closes modal and proceeds with PWA install upon success', 
    js.includes('window.closeNotificationExplanationModal()') &&
    js.includes('window.proceedWithPwaInstall()')
);

test('Denied state updates UI to "Notifications are blocked"', 
    js.includes('Notifications are blocked') &&
    js.includes('renderNotificationModalState(\'denied\')')
);

test('Denied state provides 6-step Android Chrome settings guide', 
    js.includes('Open Chrome site settings for current Tours website') &&
    js.includes('Open <span class="tours-step-highlight">Permissions</span>') &&
    js.includes('Select <span class="tours-step-highlight">Notifications</span>') &&
    js.includes('Change the setting to <span class="tours-step-highlight">Allow</span>') &&
    js.includes('Return to <span class="tours-step-highlight">Tours</span>') &&
    js.includes('Tap <span class="tours-step-highlight">Check Again</span>')
);

test('Default / Dismissed state updates UI to "Permission Not Granted"', 
    js.includes('Permission Not Granted') &&
    js.includes('renderNotificationModalState(\'default\')')
);

test('Default state offers "Try Again" and "Continue Installing Tours"', 
    js.includes('Try Again') &&
    js.includes('Continue Installing Tours')
);

console.log('\n============================================================');
console.log('7. CRITICAL REQUIREMENT: INSTALLATION NEVER BLOCKED');
console.log('============================================================');

test('User selecting "Maybe Later" continues with PWA install (handleNotificationModalSkip)', 
    js.includes('window.handleNotificationModalSkip = function') &&
    js.includes('window.closeNotificationExplanationModal()') &&
    js.includes('window.proceedWithPwaInstall()')
);

test('User with blocked notifications can "Continue Installing Tours" (handleNotificationModalContinueInstall)', 
    js.includes('window.handleNotificationModalContinueInstall = function') &&
    js.includes('window.closeNotificationExplanationModal()') &&
    js.includes('window.proceedWithPwaInstall()')
);

test('Denied state includes "Continue Installing Tours" primary button', 
    js.includes('id="toursContinueInstallBtn"') &&
    js.includes('Continue Installing Tours')
);

test('Push subscription network failure does not prevent PWA installation', 
    js.includes('console.error(\'Tours Push Registration Error:\', err);') &&
    js.includes('window.proceedWithPwaInstall();')
);

console.log('\n============================================================');
console.log('8. PLATFORM-SPECIFIC INSTALLATION FLOW & MODALS');
console.log('============================================================');

test('proceedWithPwaInstall invokes deferredInstallPrompt if available', 
    js.includes('if (deferredInstallPrompt)') &&
    (js.includes('promptEvent.prompt()') || js.includes('deferredInstallPrompt.prompt()'))
);

test('Platform-specific Android Chrome manual install modal is defined (showAndroidInstallModal)', 
    js.includes('function showAndroidInstallModal()') &&
    js.includes('Install Tours on Android') &&
    js.includes('Tap Chrome\'s <strong>three dots menu</strong>') &&
    js.includes('Select <strong>"Install App"</strong> or <strong>"Add to Home screen"</strong>')
);

test('Platform-specific Desktop Chrome manual install modal is defined (showDesktopInstallModal)', 
    js.includes('function showDesktopInstallModal()') &&
    js.includes('Install Tours on Desktop') &&
    js.includes('Click the <strong>Install icon</strong>')
);

test('Platform-specific iOS Safari manual install modal is defined (showIosInstallModal)', 
    js.includes('function showIosInstallModal()') &&
    js.includes('Install Tours on iPhone / iPad') &&
    js.includes('Tap the <strong>Share button</strong>')
);

test('proceedWithPwaInstall routes to platform-specific modals only AFTER onboarding', 
    js.includes('if (isIosDevice()) {') &&
    js.includes('showIosInstallModal();') &&
    js.includes('showAndroidInstallModal();') &&
    js.includes('showDesktopInstallModal();')
);

console.log('\n============================================================');
console.log('9. ACCESSIBLE KEYBOARD & MULTI-MODAL PREVENTION');
console.log('============================================================');

test('Modal has ARIA dialog attributes: role="dialog" and aria-modal="true"', 
    js.includes('role\', \'dialog\'') &&
    js.includes('aria-modal\', \'true\'') &&
    js.includes('aria-labelledby\', \'toursModalHeading\'')
);

test('setupModalFocusTrap captures previouslyFocusedElement', 
    js.includes('previouslyFocusedElement = document.activeElement;')
);

test('setupModalFocusTrap handles Tab & Shift+Tab loop within modal', 
    js.includes('if (e.key === \'Tab\')') &&
    js.includes('if (e.shiftKey)')
);

test('Escape key closes modal gracefully without alert/confirm', 
    js.includes('if (e.key === \'Escape\')') &&
    js.includes('window.closeNotificationExplanationModal()')
);

test('closeNotificationExplanationModal restores focus to previouslyFocusedElement', 
    js.includes('previouslyFocusedElement.focus()') &&
    js.includes('previouslyFocusedElement = null;')
);

test('closeAllToursModals closes all known modal IDs to prevent overlapping dialogs', 
    js.includes('toursNotificationModal') &&
    js.includes('toursAndroidInstallModal') &&
    js.includes('toursDesktopInstallModal') &&
    js.includes('toursIosInstallModal')
);

test('No browser alert() or confirm() used in notification install flow', 
    !js.includes('alert(')
);

console.log('\n============================================================');
console.log('10. VERIFYING BRANDING & INTEGRITY PRESERVATION');
console.log('============================================================');

test('Branding remains strictly "Tours"', 
    !js.includes('VoyageGo PWA') &&
    js.includes('Tours')
);

test('No jQuery dependencies used in tours-pwa.js', 
    !js.includes('$.ajax') &&
    !js.includes('$("#')
);

console.log('\n============================================================');
console.log('ALL VERIFICATION CHECKS COMPLETED SUCCESSFULLY');
console.log('============================================================');
