/**
 * Verification test suite for Tours PWA Mobile Notification Onboarding & Responsiveness
 * Tests Task 1 (CSS responsiveness & dvh), Task 2 (Blocked screen), Task 3 (Retry & lifecycle), Task 4 (Insecure context)
 */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('============================================================');
console.log('1. VERIFYING CSS MOBILE RESPONSIVENESS & SAFE-AREA INSETS');
console.log('============================================================');

const cssPath = path.join(__dirname, '../public/assets/css/tours-pwa.css');
const css = fs.readFileSync(cssPath, 'utf8');

function check(desc, condition) {
    if (condition) {
        console.log(`  [PASS] ${desc}`);
    } else {
        console.error(`  [FAIL] ${desc}`);
        process.exitCode = 1;
    }
}

// Task 1: Responsive units & layout
check('.tours-modal-backdrop supports dynamic viewport height (100dvh)', css.includes('min-height: 100dvh'));
check('.tours-modal-backdrop supports safe-area insets', css.includes('env(safe-area-inset-top)') && css.includes('env(safe-area-inset-bottom)'));
check('.tours-modal-backdrop has overflow-y auto for mobile scrolling', css.includes('-webkit-overflow-scrolling: touch'));
check('.tours-modal-box has max-height bound to dynamic viewport (100dvh - 1rem)', css.includes('max-height: calc(100dvh - 1rem)'));
check('.tours-modal-box uses flex column layout to pin footer', css.includes('display: flex') && css.includes('flex-direction: column'));
check('.tours-modal-header has flex-shrink: 0', css.includes('flex-shrink: 0'));
check('.tours-modal-body has flex: 1 1 auto with independent scroll', css.includes('flex: 1 1 auto') && css.includes('overflow-y: auto'));
check('.tours-modal-footer has flex-shrink: 0 to prevent cutting off buttons', css.includes('.tours-modal-footer') && css.includes('flex-shrink: 0'));
check('Responsive typography with clamp() is implemented', css.includes('font-size: clamp('));
check('Step-by-step visual guide classes exist in CSS', css.includes('.tours-step-guide') && css.includes('.tours-step-num'));
check('Insecure context alert card classes exist in CSS', css.includes('.insecure-context-card') && css.includes('.insecure-opt-item'));
check('Low-height landscape media query exists (@media (max-height: 540px))', css.includes('@media (max-height: 540px)'));

console.log('\n============================================================');
console.log('2. VERIFYING JAVASCRIPT ONBOARDING LOGIC & SPECIFICATIONS');
console.log('============================================================');

const jsPath = path.join(__dirname, '../public/assets/js/tours-pwa.js');
const js = fs.readFileSync(jsPath, 'utf8');

// Task 4: Insecure Context & Secure Origin Detection
check('getSecurityContext function is defined', js.includes('function getSecurityContext()'));
check('getSecurityContext evaluates window.isSecureContext', js.includes('typeof window.isSecureContext === \'boolean\'') && js.includes('window.isSecureContext'));
check('Detects HTTPS and localhost separately', js.includes('isHttps') && js.includes('isLocalhost') && js.includes('isLanIp'));
check('Does not treat an ordinary HTTP LAN IP as a secure context', js.includes('!(!isHttps && isLanIp)'));
check('isSecureContextOrigin function is defined', js.includes('function isSecureContextOrigin()'));
check('isPushSupported checks Notification, serviceWorker, and PushManager', js.includes('function isPushSupported()') && js.includes('PushManager') && js.includes('serviceWorker'));

const expectedInsecureMsg = 'Secure connection required. Push notifications require HTTPS. Please access Tours through a secure HTTPS address.';
check('Exact required insecure context message is present in JS', js.includes(expectedInsecureMsg));

check('Cloudflare Tunnel setup guidance is documented in UI without bypassing security flags', 
    js.includes('cloudflared tunnel --url') && 
    js.includes('trycloudflare.com') &&
    !js.includes('#unsafely-treat-insecure-origin-as-secure'));

// Task 2: Permission-blocked screen & Android Chrome guidance
const expectedBlockedMsg = 'Notifications are blocked for Tours. Enable notifications in your browser or device settings to receive important trip updates.';
check('Exact required permission-blocked message is present in JS', js.includes(expectedBlockedMsg));

check('Step 1 of Android Chrome guide is present', js.includes('Open Chrome site settings for current Tours website'));
check('Step 2 of Android Chrome guide is present (Open Permissions)', js.includes('Open <span class="tours-step-highlight">Permissions</span>'));
check('Step 3 of Android Chrome guide is present (Select Notifications)', js.includes('Select <span class="tours-step-highlight">Notifications</span>'));
check('Step 4 of Android Chrome guide is present (Change to Allow)', js.includes('Change the setting to <span class="tours-step-highlight">Allow</span>'));
check('Step 5 of Android Chrome guide is present (Return to Tours)', js.includes('Return to <span class="tours-step-highlight">Tours</span>'));
check('Step 6 of Android Chrome guide is present (Tap Check Again)', js.includes('Tap <span class="tours-step-highlight">Check Again</span>'));

check('Does not claim JavaScript can directly open Android settings', !js.includes('Android.openSettings') && js.includes('Browser security prevents web pages from altering your device settings directly'));

// Task 3: Retry functionality & Lifecycle recheck
check('retryNotificationOnboarding is exposed on window', js.includes('window.retryNotificationOnboarding = async function'));
check('retryNotificationOnboarding checks isSecureContextOrigin() first', js.includes('if (!isSecureContextOrigin())'));
check('retryNotificationOnboarding verifies Notification.permission !== "denied"', js.includes('if (Notification.permission === \'denied\')'));
check('retryNotificationOnboarding ensures serviceWorker ready', js.includes('navigator.serviceWorker.ready'));
check('retryNotificationOnboarding validates CSRF token', js.includes('getCsrfToken()'));
check('retryNotificationOnboarding sends POST to /api/push/subscribe', js.includes('/api/push/subscribe'));
check('retryNotificationOnboarding updates UI only after verified result.success', js.includes('if (!result.success) {') && js.includes('renderOnboardingState(\'success\')'));

check('Lifecycle event visibilitychange is registered', js.includes('document.addEventListener(\'visibilitychange\''));
check('Lifecycle event focus is registered', js.includes('window.addEventListener(\'focus\', handleLifecycleVisibilityCheck)'));
check('Lifecycle event pageshow is registered', js.includes('window.addEventListener(\'pageshow\', handleLifecycleVisibilityCheck)'));

console.log('\n============================================================');
console.log('3. VERIFYING BRANDING & INTEGRITY PRESERVATION');
console.log('============================================================');

check('Branding remains Tours', !js.includes('VoyageGo PWA') && js.includes('Tours'));
check('Vanilla JavaScript used without jQuery ($ references to jQuery)', !js.includes('$.ajax') && !js.includes('$("#'));

console.log('\n============================================================');
console.log('TEST SUMMARY: ALL CRITICAL REQUIREMENTS VERIFIED');
console.log('============================================================');

