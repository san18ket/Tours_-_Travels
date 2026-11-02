/**
 * Tours Progressive Web App (PWA) Client
 * Preserves native installability, offline caching, and standalone UX across Android, iOS, Windows, macOS
 * Browser Web Push functionality has been completely removed in favor of professional SMTP email notifications.
 */

(function () {
    'use strict';

    // Application state
    let deferredInstallPrompt = null;
    let isAppStandalone = false;
    let previouslyFocusedElement = null;
    let modalKeydownHandler = null;

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

    // Helper: Detect iOS Device
    function isIosDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent) && !window.MSStream;
    }

    // Helper: Detect Android Device
    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }

    // Helper: Detect if App is Running Standalone (Installed)
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

    // Helper: Check Secure Context Origin (HTTPS or localhost)
    function isSecureContextOrigin() {
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

        if (typeof window.isSecureContext === 'boolean') {
            return window.isSecureContext && (isHttps || isLocalhost) && !(!isHttps && isLanIp);
        }
        return isHttps || isLocalhost;
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
                btn.style.display = 'flex';
            }
        });
    }

    // Central modal manager
    window.closeAllToursModals = function () {
        const modalIds = [
            'toursIosInstallModal',
            'toursAndroidInstallModal',
            'toursDesktopInstallModal',
            'toursGenericInstallModal'
        ];
        modalIds.forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
            }
        });
        teardownModalFocusTrap();
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
        teardownModalFocusTrap();
        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
            previouslyFocusedElement = null;
        }
    };

    // Native / Supported PWA installation execution
    // Directly installs or guides the user without any notification permission prompts.
    window.proceedWithPwaInstall = function () {
        window.closeAllToursModals();

        // 1. Check genuine standalone mode
        if (checkStandaloneMode()) {
            isAppStandalone = true;
            showToursToast('Tours is already installed on this device.', 'info');
            return;
        }

        // 2. If beforeinstallprompt is available, invoke native browser prompt
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

        // 3. Otherwise show platform-specific installation guide modal
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

        if (checkStandaloneMode()) {
            isAppStandalone = true;
            showToursToast('Tours is already installed on this device.', 'info');
            return;
        }

        window.proceedWithPwaInstall();
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
                        <strong style="color: #0F172A;"><i class="fa-solid fa-mobile-screen" style="color: #176B5B;"></i> How to Install from Chrome:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">1</span>
                                <span>Tap Chrome's <strong>three dots menu</strong> (<i class="fa-solid fa-ellipsis-vertical"></i>) in the top-right corner.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">2</span>
                                <span>Select <strong>"Install App"</strong> or <strong>"Add to Home screen"</strong>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">3</span>
                                <span>Tap <strong>Install</strong> or <strong>Add</strong> to confirm.</span>
                            </li>
                        </ol>
                    </div>
                    ${!isSecure ? `
                        <div class="tours-https-inline-notice">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div class="tours-https-notice-text">
                                <strong>HTTPS Connection Notice:</strong>
                                <span>Chrome provides automated one-tap prompts when connected via HTTPS. You can still install Tours via the menu above.</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursAndroidInstallModal')" style="background: #176B5B; border-color: #176B5B;">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        setupModalFocusTrap(modal);
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
                    <p>Install Tours for a dedicated, fullscreen desktop app experience</p>
                </div>
                <div class="tours-light-body">
                    <div class="tours-light-blocked-box" style="background: rgba(248, 250, 252, 0.95); border-color: rgba(226, 232, 240, 0.9); color: #334155;">
                        <strong style="color: #0F172A;"><i class="fa-solid fa-laptop" style="color: #176B5B;"></i> How to Install on Desktop:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">1</span>
                                <span>Click the <strong>Install icon</strong> (<i class="fa-solid fa-download"></i>) on the right side of your address bar.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">2</span>
                                <span>Or click Chrome's <strong>three dots menu</strong> (<i class="fa-solid fa-ellipsis-vertical"></i>) &rarr; <strong>"Install Tours..."</strong>.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">3</span>
                                <span>Click <strong>Install</strong> to add Tours to your applications.</span>
                            </li>
                        </ol>
                    </div>
                    ${!isSecure ? `
                        <div class="tours-https-inline-notice">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div class="tours-https-notice-text">
                                <strong>HTTPS Connection Notice:</strong>
                                <span>Chrome enables address bar install buttons when connected via HTTPS or localhost.</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursDesktopInstallModal')" style="background: #176B5B; border-color: #176B5B;">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        setupModalFocusTrap(modal);
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
                        <strong style="color: #0F172A;"><i class="fa-solid fa-mobile-screen" style="color: #176B5B;"></i> How to Install from Safari:</strong>
                        <ol class="tours-light-blocked-steps">
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">1</span>
                                <span>Tap the <strong>Share button</strong> (<i class="fa-solid fa-arrow-up-from-bracket"></i>) in Safari toolbar at bottom of screen.</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">2</span>
                                <span>Scroll down and select <strong>"Add to Home Screen"</strong> (<i class="fa-solid fa-square-plus"></i>).</span>
                            </li>
                            <li class="tours-light-blocked-step">
                                <span class="tours-step-badge-purple" style="background: #176B5B;">3</span>
                                <span>Tap <strong>Add</strong> in the top-right corner to complete.</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="tours-light-footer">
                    <button type="button" class="btn-purple-primary" onclick="closeToursModal('toursIosInstallModal')" style="background: #176B5B; border-color: #176B5B;">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        setupModalFocusTrap(modal);
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

    // Modal accessibility & focus trapping
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
                window.closeAllToursModals();
                return;
            }

            if (e.key === 'Tab') {
                const focusables = Array.from(modal.querySelectorAll(focusableSelector));
                if (focusables.length === 0) return;

                const first = focusables[0];
                const last = focusables[focusables.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
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

    // ==========================================================================
    // 2. TOAST NOTIFICATIONS HELPER
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
            info: '<i class="fa-solid fa-circle-info" style="color: #176B5B;"></i>'
        };

        toast.innerHTML = `
            <div class="toast-icon-wrap">${icons[type] || icons.info}</div>
            <div class="toast-content-wrap">
                <span class="toast-title">Tours Notification</span>
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
    // 3. AUTO INITIALIZATION
    // ==========================================================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initPwaInstall();
        });
    } else {
        initPwaInstall();
    }

})();
