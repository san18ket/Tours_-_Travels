/**
 * Tours Progressive Web App (PWA) & Web Push Notification Client
 * Vanilla JavaScript implementation for cross-device support (Android, iOS, Windows, macOS)
 */

(function () {
    'use strict';

    // Application state
    let deferredInstallPrompt = null;
    let isAppStandalone = false;
    let vapidPublicKeyCached = null;

    // Helper: Retrieve Base URL
    function getBaseUrl() {
        const meta = document.querySelector('meta[name="base-url"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content').replace(/\/$/, '');
        }
        return '';
    }

    // Helper: Build absolute/relative URL
    function appUrl(path) {
        const base = getBaseUrl();
        const cleanPath = path.startsWith('/') ? path : '/' + path;
        return base + cleanPath;
    }

    // Helper: Retrieve CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const input = document.querySelector('input[name="_csrf_token"], input[name="csrf_token"]');
        if (input && input.value) {
            return input.value;
        }
        return '';
    }

    // Helper: Base64URL to Uint8Array for VAPID applicationServerKey
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Helper: Detect iOS Device
    function isIosDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent) && !window.MSStream;
    }

    // Helper: Detect Android Device
    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }

    // Helper: Detect if App is Running Standalone (Installed)
    // Only returns true if genuinely running in standalone/fullscreen display mode,
    // avoiding false positives from external app referrers.
    function checkStandaloneMode() {
        return Boolean(
            (window.matchMedia && (
                window.matchMedia('(display-mode: standalone)').matches ||
                window.matchMedia('(display-mode: fullscreen)').matches ||
                window.matchMedia('(display-mode: minimal-ui)').matches
            )) ||
            window.navigator.standalone === true
        );
    }

    // Helper: Comprehensive Secure Context & Environment Analysis
    function getSecurityContext() {
        const protocol = window.location.protocol;
        const hostname = window.location.hostname;
        const isHttps = protocol === 'https:';
        const isLocalhost = Boolean(
            hostname === 'localhost' ||
            hostname === '127.0.0.1' ||
            hostname === '[::1]'
        );
        const isLanIp = Boolean(
            hostname.match(/^192\.168\./) ||
            hostname.match(/^10\./) ||
            hostname.match(/^172\.(1[6-9]|2[0-9]|3[0-1])\./) ||
            hostname.endsWith('.local')
        );

        // Security requirement:
        // Plain HTTP over LAN IP (e.g. http://192.168.1.11) is strictly NOT a secure context.
        // Even if a browser flag or polyfill is present, we enforce genuine secure contexts:
        // Either native HTTPS (e.g. Cloudflare Tunnel, domain SSL) or local loopback (localhost).
        let isSecure = false;
        if (typeof window.isSecureContext === 'boolean') {
            isSecure = window.isSecureContext && (isHttps || isLocalhost) && !(!isHttps && isLanIp);
        } else {
            isSecure = isHttps || isLocalhost;
        }

        const hasServiceWorker = 'serviceWorker' in navigator;
        const hasPushManager = 'PushManager' in window;
        const hasNotification = 'Notification' in window;
        const permission = hasNotification ? Notification.permission : 'unsupported';

        return {
            isSecure,
            isHttps,
            isLocalhost,
            isLanIp,
            hasServiceWorker,
            hasPushManager,
            hasNotification,
            permission
        };
    }

    // Helper: Detect if Origin is a Secure Context (HTTPS or localhost)
    function isSecureContextOrigin() {
        return getSecurityContext().isSecure;
    }

    // Helper: Detect Browser Web Push & Service Worker Support
    function isPushSupported() {
        const ctx = getSecurityContext();
        return ctx.hasNotification && ctx.hasServiceWorker && ctx.hasPushManager;
    }

    // ==========================================================================
    // 1. PWA INSTALLATION MANAGEMENT
    // ==========================================================================

    function initPwaInstall() {
        isAppStandalone = checkStandaloneMode();

        // Listen for Chromium installation prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            updateInstallUi(true);
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            isAppStandalone = true;
            updateInstallUi(false, true);
            showToursToast('Tours installed successfully! You can launch it directly from your home screen or apps menu.', 'success');
        });

        // Initial UI evaluation
        if (isAppStandalone) {
            updateInstallUi(false, true);
        } else if (isIosDevice()) {
            // iOS Safari does not fire beforeinstallprompt, but users can install via Share Sheet
            updateInstallUi(true, false, true);
        }
    }

    function updateInstallUi(available, installed = false, isIos = false) {
        const installBtns = document.querySelectorAll('.tours-install-action, .tours-install-btn');
        installBtns.forEach((btn) => {
            if (installed) {
                btn.style.display = 'flex';
                btn.classList.add('is-installed');
                const label = btn.querySelector('.install-btn-label');
                if (label) label.textContent = 'Tours Installed';
                btn.setAttribute('title', 'Tours is already installed on this device');
                btn.setAttribute('aria-disabled', 'true');
            } else if (available) {
                btn.style.display = 'flex';
                btn.classList.remove('is-installed');
                const label = btn.querySelector('.install-btn-label');
                if (label) label.textContent = 'Install Tours';
                btn.removeAttribute('aria-disabled');
            } else {
                // Keep visible in profile dropdown so users can see installation instructions if needed
                btn.style.display = 'flex';
            }
        });
    }

    // Central modal manager: close all PWA modals to prevent simultaneous overlapping modals
    window.closeAllToursModals = function () {
        const modalIds = [
            'toursNotificationModal',
            'toursIosInstallModal',
            'toursAndroidInstallModal',
            'toursDesktopInstallModal',
            'toursGenericInstallModal',
            'toursNotifDeniedModal',
            'toursOnboardingModal'
        ];
        modalIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
            }
        });
        if (typeof teardownModalFocusTrap === 'function') {
            teardownModalFocusTrap();
        }
    };

    window.closeToursModal = function (modalId) {
        if (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        } else {
            window.closeAllToursModals();
        }
        if (typeof teardownModalFocusTrap === 'function') {
            teardownModalFocusTrap();
        }
        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
            previouslyFocusedElement = null;
        }
    };

    // Native / Supported PWA installation execution
    // CRITICAL: Invoked ONLY after the notification onboarding flow has completed or was skipped.
    window.proceedWithPwaInstall = function () {
        // Prevent multiple modals from opening simultaneously
        window.closeAllToursModals();

        // 1. Check genuine standalone mode (do not falsely report installed)
        if (checkStandaloneMode()) {
            isAppStandalone = true;
            showToursToast('Tours is already installed on this device.', 'info');
            return;
        }

        // 2. If beforeinstallprompt is available, invoke the saved deferred install prompt
        if (deferredInstallPrompt) {
            const promptEvent = deferredInstallPrompt;
            deferredInstallPrompt = null;
            promptEvent.prompt();
            promptEvent.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    showToursToast('Installing Tours...', 'info');
                }
            }).catch((err) => {
                console.warn('PWA install prompt error:', err);
            });
            return;
        }

        // 3. If beforeinstallprompt is unavailable:
        // Show platform-specific manual installation instructions only AFTER the notification onboarding flow
        if (isIosDevice()) {
            showIosInstallModal();
        } else if (isAndroidDevice()) {
            showAndroidInstallModal();
        } else {
            showDesktopInstallModal();
        }
    };

    // Triggered when user clicks "Install Tours" in profile dropdown or mobile menu
    window.triggerToursInstall = function (e) {
        if (e) e.preventDefault();

        // 1. Check whether application is already installed in genuine standalone mode
        if (checkStandaloneMode()) {
            isAppStandalone = true;
            showToursToast('Tours is already installed on this device.', 'info');
            return;
        }

        // 2. Prevent multiple modals from opening simultaneously
        window.closeAllToursModals();

        // 3. The custom notification onboarding modal is ALWAYS the FIRST modal shown!
        // Do not immediately show manual installation instructions.
        // Do not immediately trigger browser's native permission popup.
        showNotificationExplanationModal('prompt');
    };

    // Platform-specific manual installation modal: Android Chrome
    function showAndroidInstallModal() {
        window.closeAllToursModals();
        let modal = document.getElementById('toursAndroidInstallModal');
        const isSecure = isSecureContextOrigin();
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursAndroidInstallModal';
            modal.className = 'tours-modal-backdrop';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'toursAndroidModalHeading');
            document.body.appendChild(modal);

            modal.addEventListener('click', (ev) => {
                if (ev.target === modal) {
                    closeToursModal('toursAndroidInstallModal');
                }
            });
        }

        modal.innerHTML = `
            <div class="tours-light-modal">
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="closeToursModal('toursAndroidInstallModal')" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); box-shadow: 0 10px 22px -4px rgba(16, 185, 129, 0.38);" aria-hidden="true">
                        <i class="fa-brands fa-android"></i>
                    </div>
                    <h3 id="toursAndroidModalHeading">Install Tours on Android</h3>
                    <p>Add Tours to your Home Screen for fast, fullscreen access</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-light-blocked-box" style="background: rgba(248, 250, 252, 0.95); border-color: rgba(226, 232, 240, 0.9); color: #334155;">
                        <strong style="color: #0F172A;"><i class="fa-solid fa-mobile-screen" style="color: #7C3AED;"></i> How to Install from Chrome:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">1</span>
                                <span>Tap Chrome's <strong>three dots menu</strong> (<i class="fa-solid fa-ellipsis-vertical"></i>) in top-right corner.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">2</span>
                                <span>Select <strong>"Install App"</strong> or <strong>"Add to Home screen"</strong>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">3</span>
                                <span>Tap <strong>Install</strong> or <strong>Add</strong> to confirm.</span>
                            </li>
                        </ol>
                    </div>
                    ${!isSecure ? `
                        <div class="tours-https-inline-notice">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div class="tours-https-notice-text">
                                <strong>HTTPS Connection Notice:</strong>
                                <span>Chrome provides automated one-tap prompts and Web Push when connected via HTTPS (e.g. Cloudflare Tunnel). You can still install Tours via the menu above.</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursAndroidInstallModal')">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof setupModalFocusTrap === 'function') {
            setupModalFocusTrap(modal);
        }
    }

    // Platform-specific manual installation modal: Desktop Chrome / Edge
    function showDesktopInstallModal() {
        window.closeAllToursModals();
        let modal = document.getElementById('toursDesktopInstallModal');
        const isSecure = isSecureContextOrigin();
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursDesktopInstallModal';
            modal.className = 'tours-modal-backdrop';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'toursDesktopModalHeading');
            document.body.appendChild(modal);

            modal.addEventListener('click', (ev) => {
                if (ev.target === modal) {
                    closeToursModal('toursDesktopInstallModal');
                }
            });
        }

        modal.innerHTML = `
            <div class="tours-light-modal">
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="closeToursModal('toursDesktopInstallModal')" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%); box-shadow: 0 10px 22px -4px rgba(59, 130, 246, 0.38);" aria-hidden="true">
                        <i class="fa-solid fa-laptop"></i>
                    </div>
                    <h3 id="toursDesktopModalHeading">Install Tours on Desktop</h3>
                    <p>Install Tours for a standalone desktop app experience</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-light-blocked-box" style="background: rgba(248, 250, 252, 0.95); border-color: rgba(226, 232, 240, 0.9); color: #334155;">
                        <strong style="color: #0F172A;"><i class="fa-solid fa-laptop" style="color: #7C3AED;"></i> How to Install on Desktop:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">1</span>
                                <span>Click the <strong>Install icon</strong> (<i class="fa-solid fa-download"></i>) on the right side of the address bar.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">2</span>
                                <span>Or click Chrome's <strong>three dots menu</strong> (<i class="fa-solid fa-ellipsis-vertical"></i>) &rarr; <strong>"Install Tours..."</strong>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">3</span>
                                <span>Click <strong>Install</strong> to add Tours to your applications.</span>
                            </li>
                        </ol>
                    </div>
                    ${!isSecure ? `
                        <div class="tours-https-inline-notice">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div class="tours-https-notice-text">
                                <strong>HTTPS Connection Notice:</strong>
                                <span>Chrome enables address bar install buttons and Web Push when connected via HTTPS or localhost.</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursDesktopInstallModal')">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof setupModalFocusTrap === 'function') {
            setupModalFocusTrap(modal);
        }
    }

    // Platform-specific manual installation modal: iOS Safari
    function showIosInstallModal() {
        window.closeAllToursModals();
        let modal = document.getElementById('toursIosInstallModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursIosInstallModal';
            modal.className = 'tours-modal-backdrop';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'toursIosModalHeading');
            document.body.appendChild(modal);

            modal.addEventListener('click', (ev) => {
                if (ev.target === modal) {
                    closeToursModal('toursIosInstallModal');
                }
            });
        }

        modal.innerHTML = `
            <div class="tours-light-modal">
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="closeToursModal('toursIosInstallModal')" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); box-shadow: 0 10px 22px -4px rgba(14, 165, 233, 0.38);" aria-hidden="true">
                        <i class="fa-brands fa-apple"></i>
                    </div>
                    <h3 id="toursIosModalHeading">Install Tours on iPhone / iPad</h3>
                    <p>Follow these quick steps to install Tours directly onto your iOS Home Screen</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-light-blocked-box" style="background: rgba(248, 250, 252, 0.95); border-color: rgba(226, 232, 240, 0.9); color: #334155;">
                        <strong style="color: #0F172A;"><i class="fa-solid fa-mobile-screen" style="color: #7C3AED;"></i> How to Install from Safari:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">1</span>
                                <span>Tap the <strong>Share button</strong> (<i class="fa-solid fa-arrow-up-from-bracket"></i>) in Safari toolbar at bottom of screen.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">2</span>
                                <span>Scroll down and select <strong>"Add to Home Screen"</strong> (<i class="fa-regular fa-square-plus"></i>).</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">3</span>
                                <span>Confirm name <strong>Tours</strong> and tap <strong>Add</strong> in top right corner.</span>
                            </li>
                        </ol>
                    </div>
                    <div class="tours-https-inline-notice" style="background: rgba(238, 242, 255, 0.85); border-color: rgba(199, 210, 254, 0.9); color: #3730A3;">
                        <i class="fa-solid fa-bell" style="color: #6366F1;"></i>
                        <div class="tours-https-notice-text">
                            <strong style="color: #312E81;">iOS Notification Support:</strong>
                            <span>Web Push notifications on iOS (16.4+) require adding Tours to Home Screen and launching it from the Home Screen icon.</span>
                        </div>
                    </div>
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursIosInstallModal')">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        if (typeof setupModalFocusTrap === 'function') {
            setupModalFocusTrap(modal);
        }
    }

    // Generic fallback: routes to the platform-tailored modal
    function showGenericInstallModal() {
        if (isAndroidDevice()) {
            showAndroidInstallModal();
        } else if (isIosDevice()) {
            showIosInstallModal();
        } else {
            showDesktopInstallModal();
        }
    }

    // ==========================================================================
    // 2. WEB PUSH NOTIFICATION PERMISSION & REGISTRATION
    // ==========================================================================

    async function fetchVapidPublicKey() {
        if (vapidPublicKeyCached) {
            return vapidPublicKeyCached;
        }

        const res = await fetch(appUrl('/api/push/vapid-key'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await res.json();
        if (data.success && data.public_key) {
            vapidPublicKeyCached = data.public_key;
            return vapidPublicKeyCached;
        }
        throw new Error(data.error || 'Unable to retrieve VAPID public key');
    }

    // Primary entry point for enabling push notifications
    window.enableToursPushNotifications = async function (btnElement) {
        if (!isSecureContextOrigin()) {
            showToursToast('Secure connection required. Push notifications require HTTPS.', 'warning');
            return false;
        }

        if (!isPushSupported()) {
            showToursToast('Push notifications are not supported in this browser.', 'warning');
            return false;
        }

        // On iOS, Web Push is only supported if app is added to Home Screen (iOS 16.4+)
        if (isIosDevice() && !checkStandaloneMode()) {
            showIosInstallModal();
            return false;
        }

        if (Notification.permission === 'denied') {
            showPermissionDeniedModal();
            return false;
        }

        let originalBtnHtml = '';
        if (btnElement) {
            originalBtnHtml = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Enabling...';
        }

        try {
            // 1. Request permission
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                showPermissionDeniedModal();
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalBtnHtml;
                }
                return false;
            }

            // 2. Ensure Service Worker ready
            const swReg = await navigator.serviceWorker.ready;

            // 3. Fetch VAPID key
            const vapidKey = await fetchVapidPublicKey();
            const convertedVapidKey = urlBase64ToUint8Array(vapidKey);

            // 4. Create Push Subscription
            let subscription = await swReg.pushManager.getSubscription();
            if (!subscription) {
                subscription = await swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            const subJson = subscription.toJSON();

            // 5. Detect friendly device name
            const deviceName = detectDeviceLabel();

            // 6. Send subscription to PHP backend
            const csrfToken = getCsrfToken();
            const registerRes = await fetch(appUrl('/api/push/subscribe'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    endpoint: subJson.endpoint,
                    keys: subJson.keys,
                    content_encoding: 'aes128gcm',
                    device_name: deviceName,
                    _csrf_token: csrfToken
                })
            });

            const result = await registerRes.json();
            if (!result.success) {
                throw new Error(result.error || 'Server rejected push registration');
            }

            showToursToast('Push notifications enabled for Tours! You will receive live trip and booking alerts.', 'success');

            // Dispatch global event for UI reactiveness
            window.dispatchEvent(new CustomEvent('tours:push-enabled', { detail: { subscription: subJson } }));

            // Refresh notification settings UI if on the settings page
            if (window.refreshDeviceList) {
                window.refreshDeviceList();
            }

            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="fa-solid fa-check"></i> Enabled';
                btnElement.classList.remove('btn-primary');
                btnElement.classList.add('btn-success');
            }

            return true;
        } catch (err) {
            console.error('Tours Push Subscription Error:', err);
            showToursToast('Failed to enable notifications: ' + err.message, 'error');
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalBtnHtml;
            }
            return false;
        }
    };

    // Disable push notifications for this device
    window.disableToursPushNotifications = async function (btnElement) {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        let originalBtnHtml = '';
        if (btnElement) {
            originalBtnHtml = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Disabling...';
        }

        try {
            const swReg = await navigator.serviceWorker.ready;
            const subscription = await swReg.pushManager.getSubscription();

            if (subscription) {
                const endpoint = subscription.endpoint;
                await subscription.unsubscribe();

                // Notify backend
                const csrfToken = getCsrfToken();
                await fetch(appUrl('/api/push/unsubscribe'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        endpoint: endpoint,
                        _csrf_token: csrfToken
                    })
                });
            }

            showToursToast('Notifications disabled for this device.', 'info');
            window.dispatchEvent(new CustomEvent('tours:push-disabled'));

            if (window.refreshDeviceList) {
                window.refreshDeviceList();
            }

            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="fa-regular fa-bell"></i> Enable Notifications';
                btnElement.classList.remove('btn-outline-danger');
                btnElement.classList.add('btn-primary');
            }
        } catch (err) {
            console.error('Error disabling push notifications:', err);
            showToursToast('Error disabling notifications: ' + err.message, 'error');
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalBtnHtml;
            }
        }
    };

    // Send Test Push Notification
    window.sendTestPushNotification = async function (btnElement) {
        let originalHtml = '';
        if (btnElement) {
            originalHtml = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending Test...';
        }

        try {
            const csrfToken = getCsrfToken();
            const res = await fetch(appUrl('/api/push/test'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ _csrf_token: csrfToken })
            });

            const data = await res.json();
            if (data.success) {
                showToursToast(data.message || 'Test notification delivered successfully!', 'success');
            } else {
                showToursToast(data.error || 'Failed to send test notification', 'error');
            }
        } catch (err) {
            showToursToast('Test notification error: ' + err.message, 'error');
        } finally {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
        }
    };

    // Remove Registered Device
    window.removePushDevice = async function (deviceId, rowElement) {
        if (!confirm('Are you sure you want to remove this device from receiving Tours notifications?')) {
            return;
        }

        try {
            const csrfToken = getCsrfToken();
            const res = await fetch(appUrl('/api/push/devices/' + deviceId + '/remove'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ _csrf_token: csrfToken })
            });

            const data = await res.json();
            if (data.success) {
                showToursToast('Device removed successfully.', 'success');
                if (rowElement) {
                    rowElement.style.opacity = '0.3';
                    setTimeout(() => rowElement.remove(), 300);
                } else if (window.refreshDeviceList) {
                    window.refreshDeviceList();
                }
            } else {
                showToursToast(data.error || 'Failed to remove device', 'error');
            }
        } catch (err) {
            showToursToast('Failed to remove device: ' + err.message, 'error');
        }
    };

    function showPermissionDeniedModal() {
        if (typeof window.showNotificationExplanationModal === 'function') {
            window.showNotificationExplanationModal('denied');
            return;
        }
        let modal = document.getElementById('toursNotifDeniedModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursNotifDeniedModal';
            modal.className = 'tours-modal-backdrop';
            modal.innerHTML = `
                <div class="tours-modal-box">
                    <div class="tours-modal-header" style="background: rgba(239, 68, 68, 0.15); border-bottom-color: rgba(239, 68, 68, 0.35);">
                        <div class="tours-modal-icon" style="background: #DC2626; color: #FFF;">
                            <i class="fa-solid fa-bell-slash"></i>
                        </div>
                        <div class="tours-modal-head-text">
                            <h3>Enable Notifications</h3>
                            <p>Notification permission is blocked in your browser</p>
                        </div>
                        <button type="button" class="tours-modal-close" onclick="closeToursModal('toursNotifDeniedModal')" aria-label="Close">&times;</button>
                    </div>
                    <div class="tours-modal-body">
                        <div class="blocked-permission-card">
                            <strong><i class="fa-solid fa-circle-exclamation"></i> Notifications Blocked</strong>
                            <p>Notifications are blocked for Tours. Enable notifications in your browser or device settings to receive important trip updates.</p>
                        </div>

                        <div class="tours-step-guide">
                            <div class="tours-step-item">
                                <div class="tours-step-num">1</div>
                                <div class="tours-step-text">Open Chrome site settings for current Tours website (tap the <i class="fa-solid fa-sliders"></i> tune/lock icon in address bar).</div>
                            </div>
                            <div class="tours-step-item">
                                <div class="tours-step-num">2</div>
                                <div class="tours-step-text">Open <span class="tours-step-highlight">Permissions</span>.</div>
                            </div>
                            <div class="tours-step-item">
                                <div class="tours-step-num">3</div>
                                <div class="tours-step-text">Select <span class="tours-step-highlight">Notifications</span>.</div>
                            </div>
                            <div class="tours-step-item">
                                <div class="tours-step-num">4</div>
                                <div class="tours-step-text">Change the setting to <span class="tours-step-highlight">Allow</span>.</div>
                            </div>
                            <div class="tours-step-item">
                                <div class="tours-step-num">5</div>
                                <div class="tours-step-text">Return to <span class="tours-step-highlight">Tours</span>.</div>
                            </div>
                            <div class="tours-step-item">
                                <div class="tours-step-num">6</div>
                                <div class="tours-step-text">Tap <span class="tours-step-highlight">Check Again</span>.</div>
                            </div>
                        </div>

                        <p style="color: #94A3B8; font-size: clamp(0.72rem, 2vw, 0.78rem); margin: 0.5rem 0 0; line-height: 1.35; font-style: italic;">
                            <i class="fa-solid fa-circle-info"></i> Browser security prevents web pages from altering your device settings directly. Please change the toggle above, then tap Check Again.
                        </p>
                    </div>
                    <div class="tours-modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeToursModal('toursNotifDeniedModal')">Dismiss</button>
                        <button type="button" class="btn btn-primary" onclick="window.retryNotificationOnboarding(this)"><i class="fa-solid fa-arrows-rotate"></i> Check Again</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
    }

    function detectDeviceLabel() {
        const ua = navigator.userAgent;
        let browser = 'Browser';
        if (ua.includes('Edg/')) browser = 'Edge';
        else if (ua.includes('Chrome/')) browser = 'Chrome';
        else if (ua.includes('Firefox/')) browser = 'Firefox';
        else if (ua.includes('Safari/') && !ua.includes('Chrome/')) browser = 'Safari';

        let os = 'Device';
        if (ua.includes('Windows NT 10.0')) os = 'Windows 10/11';
        else if (ua.includes('Windows')) os = 'Windows';
        else if (ua.includes('Android')) os = 'Android';
        else if (ua.includes('iPhone')) os = 'iPhone';
        else if (ua.includes('iPad')) os = 'iPad';
        else if (ua.includes('Macintosh') || ua.includes('Mac OS')) os = 'macOS';
        else if (ua.includes('Linux')) os = 'Linux';

        return `${browser} on ${os}`;
    }

    // ==========================================================================
    // 3. TOAST NOTIFICATIONS HELPER
    // ==========================================================================
    window.showToursToast = function (message, type = 'info') {
        let container = document.getElementById('toursToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toursToastContainer';
            container.className = 'tours-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `tours-toast-item tours-toast-${type}`;

        const icons = {
            success: '<i class="fa-solid fa-circle-check" style="color: #10B981;"></i>',
            error: '<i class="fa-solid fa-circle-exclamation" style="color: #EF4444;"></i>',
            warning: '<i class="fa-solid fa-triangle-exclamation" style="color: #F59E0B;"></i>',
            info: '<i class="fa-solid fa-bell" style="color: #176B5B;"></i>'
        };

        toast.innerHTML = `
            <div class="toast-icon-wrap">${icons[type] || icons.info}</div>
            <div class="toast-content-wrap">
                <span class="toast-title">Tours Alert</span>
                <span class="toast-msg">${escapeHtml(message)}</span>
            </div>
            <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">&times;</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('is-fadeout');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==========================================================================
    // 3.5. TOURS PROFESSIONAL NOTIFICATION EXPLANATION & INSTALL FLOW
    // ==========================================================================
    let previouslyFocusedElement = null;
    let modalKeydownHandler = null;

    // Open professional custom notification explanation modal
    // Note: Does not immediately trigger browser's native notification popup!
    window.showNotificationExplanationModal = function (initialState = 'prompt') {
        window.closeAllToursModals();
        let state = initialState;
        if (!state) {
            state = 'prompt';
        }

        let modal = document.getElementById('toursNotificationModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursNotificationModal';
            modal.className = 'tours-modal-backdrop';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'toursModalHeading');
            modal.setAttribute('aria-describedby', 'toursModalDesc');
            modal.innerHTML = '<div class="tours-light-modal" id="toursNotificationModalBox"></div>';
            document.body.appendChild(modal);

            // Backdrop click closes modal
            modal.addEventListener('click', (ev) => {
                if (ev.target === modal) {
                    window.closeNotificationExplanationModal();
                }
            });
        }

        renderNotificationModalState(state);
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        setupModalFocusTrap(modal);
    };

    function renderNotificationModalState(state, errorMessage = null) {
        const box = document.getElementById('toursNotificationModalBox');
        if (!box) return;

        if (state === 'denied') {
            box.innerHTML = `
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="window.closeNotificationExplanationModal()" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); box-shadow: 0 10px 22px -4px rgba(220, 38, 38, 0.38);" aria-hidden="true">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <h3 id="toursModalHeading">Notifications are blocked</h3>
                    <p id="toursModalDesc">Notifications are blocked for Tours. Enable notifications in your browser or device settings to receive important trip updates.</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-light-blocked-box">
                        <strong><i class="fa-solid fa-circle-exclamation"></i> Notifications Blocked</strong>
                        <p>Notifications are blocked for Tours. Enable notifications in your browser or device settings to receive important trip updates.</p>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">1</span>
                                <span>Open Chrome site settings for current Tours website (tap the <i class="fa-solid fa-sliders"></i> tune/lock icon in address bar).</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">2</span>
                                <span>Open <span class="tours-step-highlight">Permissions</span>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">3</span>
                                <span>Select <span class="tours-step-highlight">Notifications</span>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">4</span>
                                <span>Change the setting to <span class="tours-step-highlight">Allow</span>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">5</span>
                                <span>Return to <span class="tours-step-highlight">Tours</span>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple">6</span>
                                <span>Tap <span class="tours-step-highlight">Check Again</span>.</span>
                            </li>
                        </ol>
                    </div>
                    <p style="color: #64748B; font-size: clamp(0.72rem, 2vw, 0.78rem); margin: 0.35rem 0 0; line-height: 1.35; font-style: italic;">
                        <i class="fa-solid fa-circle-info"></i> Browser security prevents web pages from altering your device settings directly. You can still install Tours now without notifications.
                    </p>
                </div>
                <div class="tours-light-footer">
                    <button type="button" id="toursContinueInstallBtn" class="btn-purple-primary" onclick="window.handleNotificationModalContinueInstall()">
                        <i class="fa-solid fa-download"></i>
                        <span>Continue Installing Tours</span>
                    </button>
                    <button type="button" class="btn-purple-secondary" onclick="window.closeNotificationExplanationModal()">
                        <span>Close</span>
                    </button>
                </div>
            `;
        } else if (state === 'default') {
            box.innerHTML = `
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="window.closeNotificationExplanationModal()" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); box-shadow: 0 10px 22px -4px rgba(217, 119, 6, 0.38);" aria-hidden="true">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h3 id="toursModalHeading">Permission Not Granted</h3>
                    <p id="toursModalDesc">Notifications were not enabled. You can try again to stay updated on trips, or continue installing Tours without notifications.</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-benefits-list">
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></div>
                            <span>Booking and trip updates</span>
                        </div>
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></div>
                            <span>Important notifications</span>
                        </div>
                    </div>
                </div>
                <div class="tours-light-footer">
                    <button type="button" id="toursRetryNotifBtn" class="btn-purple-primary" onclick="window.handleNotificationModalEnable(this)">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Try Again</span>
                    </button>
                    <button type="button" id="toursContinueInstallBtn2" class="btn-purple-secondary" onclick="window.handleNotificationModalContinueInstall()">
                        <i class="fa-solid fa-download"></i>
                        <span>Continue Installing Tours</span>
                    </button>
                </div>
            `;
        } else if (state === 'insecure') {
            box.innerHTML = `
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="window.closeNotificationExplanationModal()" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); box-shadow: 0 10px 22px -4px rgba(217, 119, 6, 0.38);" aria-hidden="true">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h3 id="toursModalHeading">Secure Connection Required</h3>
                    <p id="toursModalDesc">Web Push requires a verified secure HTTPS context</p>
                </div>
                <div class="tours-light-body">
                    <div class="insecure-context-card" style="background: rgba(254, 243, 199, 0.95); border: 1px solid rgba(251, 191, 36, 0.8); border-radius: 12px; padding: 0.85rem 1rem; color: #92400E; margin-bottom: 0.6rem;">
                        <strong><i class="fa-solid fa-shield-halved"></i> HTTPS Required</strong>
                        <p style="margin: 0.25rem 0 0; font-size: 0.84rem; color: #78350F; line-height: 1.4;">
                            <strong>Secure connection required. Push notifications require HTTPS. Please access Tours through a secure HTTPS address.</strong>
                        </p>
                    </div>
                    <div class="tunnel-guide-box" style="background: rgba(248, 250, 252, 0.95); border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 12px; padding: 0.85rem 1rem; color: #334155; font-size: 0.82rem;">
                        <strong style="color: #0F172A;"><i class="fa-solid fa-cloud" style="color: #7C3AED;"></i> Access via Cloudflare Tunnel (HTTPS)</strong>
                        <p style="margin: 0.3rem 0; line-height: 1.4;">To enable Web Push on this device, run a Cloudflare Tunnel on your host PC:</p>
                        <div class="tunnel-cmd" style="background: #0F172A; color: #38BDF8; font-family: monospace; padding: 0.4rem 0.65rem; border-radius: 6px; font-size: 0.8rem; word-break: break-all;">cloudflared tunnel --url http://localhost:80</div>
                        <p style="margin: 0.35rem 0 0; line-height: 1.35; color: #64748B;">Open the generated <code>https://*.trycloudflare.com</code> address for full push support.</p>
                    </div>
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="window.handleNotificationModalContinueInstall()">
                        <i class="fa-solid fa-download"></i>
                        <span>Continue Installing Tours</span>
                    </button>
                    <button type="button" class="btn-purple-secondary" onclick="window.closeNotificationExplanationModal()">
                        <span>Close</span>
                    </button>
                </div>
            `;
        } else if (state === 'unsupported') {
            box.innerHTML = `
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="window.closeNotificationExplanationModal()" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" style="background: linear-gradient(135deg, #64748B 0%, #475569 100%);" aria-hidden="true">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 id="toursModalHeading">Push Not Supported</h3>
                    <p id="toursModalDesc">Your browser does not support Web Push notifications</p>
                </div>
                <div class="tours-light-body">
                    <p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin: 0 0 0.5rem;">
                        Push notifications are not supported in this browser environment. You can still install Tours and use all other features offline.
                    </p>
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="window.handleNotificationModalContinueInstall()">
                        <i class="fa-solid fa-download"></i>
                        <span>Continue Installing Tours</span>
                    </button>
                    <button type="button" class="btn-purple-secondary" onclick="window.closeNotificationExplanationModal()">
                        <span>Close</span>
                    </button>
                </div>
            `;
        } else {
            // Default: 'prompt' state
            // Requirement 9: Show small, professional HTTPS notice inline if running on insecure LAN IP.
            // Do NOT replace the notification onboarding modal with an HTTPS error screen!
            const isSecure = isSecureContextOrigin();
            box.innerHTML = `
                <div class="tours-light-header">
                    <button type="button" class="tours-light-close" onclick="window.closeNotificationExplanationModal()" aria-label="Close modal">&times;</button>
                    <div class="tours-light-icon-wrap" aria-hidden="true">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h3 id="toursModalHeading">Stay Connected with Tours</h3>
                    <p id="toursModalDesc">Enable notifications to receive important trip updates, booking confirmations and driver notifications directly on your device.</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-benefits-list">
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></div>
                            <span>Booking and trip updates</span>
                        </div>
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></div>
                            <span>Important notifications</span>
                        </div>
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-car"></i></div>
                            <span>Driver and travel updates</span>
                        </div>
                        <div class="tours-benefit-item">
                            <div class="tours-benefit-icon" aria-hidden="true"><i class="fa-solid fa-mobile-screen"></i></div>
                            <span>Notifications directly on your device</span>
                        </div>
                    </div>
                    ${!isSecure ? `
                        <div class="tours-https-inline-notice">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div class="tours-https-notice-text">
                                <strong>HTTPS Connection Notice:</strong>
                                <span>Web Push notifications require a secure connection (e.g. Cloudflare Tunnel). You can still install Tours on this device now.</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="tours-light-footer">
                    <button type="button" id="toursEnableNotifBtn" class="btn-purple-primary" onclick="window.handleNotificationModalEnable(this)">
                        <i class="fa-solid fa-bell"></i>
                        <span>Enable Notifications &amp; Continue</span>
                    </button>
                    <button type="button" id="toursSkipNotifBtn" class="btn-purple-secondary" onclick="window.handleNotificationModalSkip()">
                        <span>Maybe Later</span>
                    </button>
                </div>
            `;
        }
    }

    // Modal Focus & Keyboard Management
    function setupModalFocusTrap(modal) {
        previouslyFocusedElement = document.activeElement;
        teardownModalFocusTrap();

        const focusableSelector = 'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        setTimeout(() => {
            const primary = modal.querySelector('.btn-purple-primary') || modal.querySelector(focusableSelector);
            if (primary && typeof primary.focus === 'function') {
                primary.focus();
            }
        }, 50);

        modalKeydownHandler = function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                window.closeNotificationExplanationModal();
                return;
            }

            if (e.key === 'Tab') {
                const focusables = Array.from(modal.querySelectorAll(focusableSelector));
                if (focusables.length === 0) return;

                const first = focusables[0];
                const last = focusables[focusables.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        };

        document.addEventListener('keydown', modalKeydownHandler);
    }

    function teardownModalFocusTrap() {
        if (modalKeydownHandler) {
            document.removeEventListener('keydown', modalKeydownHandler);
            modalKeydownHandler = null;
        }
    }

    // Explicit interaction from custom modal
    window.handleNotificationModalEnable = async function (btn) {
        const secCtx = getSecurityContext();

        // If context is insecure, Web Push cannot be enabled over plain HTTP, but install continues!
        if (!secCtx.isSecure) {
            showToursToast('Push notifications require HTTPS. Continuing with installation...', 'info');
            window.closeNotificationExplanationModal();
            window.proceedWithPwaInstall();
            return;
        }

        // If browser does not support push/serviceWorker
        if (!secCtx.hasNotification || !secCtx.hasServiceWorker || !secCtx.hasPushManager) {
            showToursToast('Push notifications are not supported in this browser. Continuing with installation...', 'info');
            window.closeNotificationExplanationModal();
            window.proceedWithPwaInstall();
            return;
        }

        if (Notification.permission === 'denied') {
            renderNotificationModalState('denied');
            return;
        }

        let originalHtml = '';
        if (btn) {
            originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Requesting Permission...';
        }

        try {
            // Explicitly requested directly from this user interaction
            const permission = await Notification.requestPermission();

            if (permission === 'denied') {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                renderNotificationModalState('denied');
                return;
            }

            if (permission !== 'granted') {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                renderNotificationModalState('default');
                return;
            }

            // Permission is granted! Ensure Service Worker is ready
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Subscribing Device...';
            }

            let swReg;
            try {
                swReg = await Promise.race([
                    navigator.serviceWorker.ready,
                    new Promise((_, reject) => setTimeout(() => reject(new Error('Service worker activation timed out')), 6000))
                ]);
            } catch (swErr) {
                swReg = await navigator.serviceWorker.register(appUrl('/sw.js'), { scope: appUrl('/') });
                await navigator.serviceWorker.ready;
            }

            const vapidKey = await fetchVapidPublicKey();
            const convertedVapidKey = urlBase64ToUint8Array(vapidKey);

            let subscription = await swReg.pushManager.getSubscription();
            if (!subscription) {
                subscription = await swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            const subJson = subscription.toJSON();
            const deviceName = detectDeviceLabel();
            const csrfToken = getCsrfToken();

            const registerRes = await fetch(appUrl('/api/push/subscribe'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    endpoint: subJson.endpoint,
                    keys: subJson.keys,
                    content_encoding: 'aes128gcm',
                    device_name: deviceName,
                    _csrf_token: csrfToken
                })
            });

            const result = await registerRes.json();
            if (!result.success) {
                throw new Error(result.error || 'Server rejected push registration');
            }

            // Verified subscription in database
            showToursToast('Push notifications successfully activated for Tours!', 'success');
            window.dispatchEvent(new CustomEvent('tours:push-enabled', { detail: { subscription: subJson } }));

            if (window.refreshDeviceList) {
                window.refreshDeviceList();
            }

            // Close modal & proceed with installation
            window.closeNotificationExplanationModal();
            window.proceedWithPwaInstall();
        } catch (err) {
            console.error('Tours Push Registration Error:', err);
            showToursToast('Push subscription note: ' + err.message, 'warning');
            // Never block PWA install if push subscription encounters a glitch
            window.closeNotificationExplanationModal();
            window.proceedWithPwaInstall();
        } finally {
            if (btn) {
                btn.disabled = false;
            }
        }
    };

    window.handleNotificationModalSkip = function () {
        window.closeNotificationExplanationModal();
        window.proceedWithPwaInstall();
    };

    window.handleNotificationModalContinueInstall = function () {
        window.closeNotificationExplanationModal();
        window.proceedWithPwaInstall();
    };

    window.closeNotificationExplanationModal = function () {
        const modal = document.getElementById('toursNotificationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        teardownModalFocusTrap();
        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
            previouslyFocusedElement = null;
        }
    };

    // Compatibility helpers and stubs
    async function checkNotificationOnboarding() {
        // Disabled: In accordance with production UX requirements, automatic notification onboarding
        // prompts, modals, and permission requests have been completely REMOVED from page load.
        return;
    }

    window.showOnboardingModal = function (state) {
        window.showNotificationExplanationModal(state);
    };

    function renderOnboardingState(state, errorMessage = null) {
        renderNotificationModalState(state, errorMessage);
    }

    window.retryNotificationOnboarding = async function (btn) {
        if (!isSecureContextOrigin()) {
            renderNotificationModalState('insecure');
            return false;
        }

        if (!isPushSupported()) {
            renderNotificationModalState('unsupported');
            return false;
        }

        if (Notification.permission === 'denied') {
            renderNotificationModalState('denied');
            return false;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Checking Status...';
        }

        try {
            if (Notification.permission === 'default') {
                const perm = await Notification.requestPermission();
                if (perm !== 'granted') {
                    renderNotificationModalState(perm === 'denied' ? 'denied' : 'default');
                    return false;
                }
            }

            let swReg = await navigator.serviceWorker.ready;
            const csrfToken = getCsrfToken();
            const vapidKey = await fetchVapidPublicKey();
            const convertedVapidKey = urlBase64ToUint8Array(vapidKey);

            let subscription = await swReg.pushManager.getSubscription();
            if (!subscription) {
                subscription = await swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });
            }

            const subJson = subscription.toJSON();
            const deviceName = detectDeviceLabel();

            const registerRes = await fetch(appUrl('/api/push/subscribe'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    endpoint: subJson.endpoint,
                    keys: subJson.keys,
                    content_encoding: 'aes128gcm',
                    device_name: deviceName,
                    _csrf_token: csrfToken
                })
            });

            const result = await registerRes.json();
            if (!result.success) {
                throw new Error(result.error || 'Server rejected push registration');
            }

            renderOnboardingState('success');
            showToursToast('Push notifications successfully activated for Tours!', 'success');
            window.dispatchEvent(new CustomEvent('tours:push-enabled', { detail: { subscription: subJson } }));

            if (window.refreshDeviceList) {
                window.refreshDeviceList();
            }

            window.closeNotificationExplanationModal();
            return true;
        } catch (err) {
            console.error('Tours Onboarding Retry Error:', err);
            showToursToast('Registration error: ' + err.message, 'error');
            return false;
        } finally {
            if (btn) {
                btn.disabled = false;
            }
        }
    };

    // Recheck notification permission when user returns from settings (only if modal is open)
    function handleLifecycleVisibilityCheck() {
        const modal = document.getElementById('toursNotificationModal') || document.getElementById('toursOnboardingModal');
        if (!modal || modal.style.display !== 'flex') {
            return;
        }

        const secCtx = getSecurityContext();
        if (!secCtx.isSecure) {
            return;
        }

        if (secCtx.permission === 'granted') {
            const btn = document.getElementById('toursEnableNotifBtn') || document.getElementById('toursRetryNotifBtn');
            window.retryNotificationOnboarding(btn);
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            handleLifecycleVisibilityCheck();
        }
    });
    window.addEventListener('focus', handleLifecycleVisibilityCheck);
    window.addEventListener('pageshow', handleLifecycleVisibilityCheck);

    // ==========================================================================
    // 4. AUTO INITIALIZATION
    // ==========================================================================
    // NOTE: In strict accordance with UX requirements, automatic notification onboarding
    // prompts, modals, and permission requests have been completely REMOVED from page load.
    // Normal website usage remains silent and uninterrupted.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initPwaInstall();
        });
    } else {
        initPwaInstall();
    }

})();
