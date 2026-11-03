/**
 * Tours Progressive Web App (PWA) Client
 * Professional Custom Installation Modal & State Management
 * Preserves native installability, offline caching, and standalone UX across Android, iOS, Windows, macOS
 * Browser Web Push functionality has been completely removed in favor of professional SMTP email notifications.
 */

(function () {
    'use strict';

    // Application state
    let deferredInstallPrompt = null;
    let isAppStandalone = false;
    let isAppInstalled = false;
    let previouslyFocusedElement = null;
    let modalKeydownHandler = null;
    let currentModalState = 'ready';

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

    // Helper: Detect iOS Device (iPhone, iPad, iPod)
    function isIosDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent) && !window.MSStream;
    }

    // Helper: Detect Android Device
    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }

    // Helper: Detect if App is Running Standalone (Already Installed)
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
    // 1. PWA INSTALLATION EVENT MANAGEMENT
    // ==========================================================================

    function initPwaInstall() {
        isAppStandalone = checkStandaloneMode();
        if (isAppStandalone) {
            isAppInstalled = true;
        }

        // Check navigator.getInstalledRelatedApps if supported
        if (navigator.getInstalledRelatedApps && typeof navigator.getInstalledRelatedApps === 'function') {
            navigator.getInstalledRelatedApps().then((apps) => {
                if (apps && apps.length > 0) {
                    isAppInstalled = true;
                    updateInstallUi(false, true);
                }
            }).catch(() => {});
        }

        // Listen for Chromium beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            updateInstallUi(true, false);
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            isAppStandalone = true;
            isAppInstalled = true;
            updateInstallUi(false, true);
            renderCustomModalState('success');
            showToursToast('Tours installed successfully! You can launch it directly from your device.', 'success');
        });

        // Initial UI evaluation
        if (isAppStandalone || isAppInstalled) {
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

    // ==========================================================================
    // 2. CUSTOM PWA INSTALLATION MODAL & STATE MANAGEMENT
    // ==========================================================================

    // Ensure modal container exists in DOM
    function ensureCustomInstallModal() {
        let modal = document.getElementById('toursPwaInstallModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toursPwaInstallModal';
            modal.className = 'pwa-install-backdrop';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'pwaModalTitle');
            modal.style.display = 'none';

            // Default app name & icon
            modal.setAttribute('data-app-name', 'Tours & Travels');
            modal.setAttribute('data-logo-url', appUrl('assets/icons/icon-192.png'));
            modal.setAttribute('data-has-custom-logo', '0');

            modal.innerHTML = `
                <div class="pwa-install-card" id="pwaModalCard">
                    <button type="button" class="pwa-card-close" onclick="closeToursInstallModal()" aria-label="Close modal">&times;</button>
                    <div id="pwaModalDynamicContent"></div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Close on backdrop click (outside card)
        if (!modal.dataset.hasBackdropListener) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal && currentModalState !== 'progress') {
                    closeToursInstallModal();
                }
            });
            modal.dataset.hasBackdropListener = 'true';
        }

        return modal;
    }

    // Close any active user profile dropdown in navbar
    function closeNavbarProfileDropdown() {
        const topbarProfile = document.getElementById('topbarUserProfile');
        if (topbarProfile) {
            topbarProfile.classList.remove('is-open');
        }
        const mvProfileMenu = document.getElementById('mvProfileMenu');
        if (mvProfileMenu) {
            mvProfileMenu.classList.remove('is-open');
        }
    }

    // Render specific state into the modal
    function renderCustomModalState(state, options = {}) {
        const modal = ensureCustomInstallModal();
        const contentContainer = document.getElementById('pwaModalDynamicContent');
        if (!contentContainer) return;

        currentModalState = state;

        const appName = modal.getAttribute('data-app-name') || 'Tours & Travels';
        const logoUrl = modal.getAttribute('data-logo-url') || appUrl('assets/icons/icon-192.png');
        const hasCustomLogo = modal.getAttribute('data-has-custom-logo') === '1';

        // Logo markup helper
        const renderLogoHtml = (extraClasses = '') => {
            if (hasCustomLogo && logoUrl) {
                return `<img src="${escapeHtml(logoUrl)}" alt="${escapeHtml(appName)}" class="pwa-hero-emblem ${extraClasses}">`;
            }
            return `
                <div class="pwa-hero-emblem-icon ${extraClasses}">
                    <i class="fa-solid fa-building"></i>
                </div>
            `;
        };

        if (state === 'ready') {
            // STAGE ONE: Ready to Install
            const noticeHtml = options.cancelledMessage ? `
                <div class="pwa-notice-box" role="alert">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>${escapeHtml(options.cancelledMessage)}</span>
                </div>
            ` : '';

            contentContainer.innerHTML = `
                <div class="pwa-hero-header">
                    <div class="pwa-hero-emblem-wrap">
                        ${renderLogoHtml()}
                    </div>
                    <span class="pwa-app-badge"><i class="fa-solid fa-certificate"></i> Official Web App</span>
                    <h3 class="pwa-modal-title" id="pwaModalTitle">Install Tours</h3>
                    <p class="pwa-modal-desc">Install Tours &amp; Travels on your device for a convenient, dedicated app experience.</p>
                </div>

                ${noticeHtml}

                <div class="pwa-benefits-list">
                    <div class="pwa-benefit-item">
                        <div class="pwa-benefit-icon">
                            <i class="fa-solid fa-house-laptop"></i>
                        </div>
                        <div class="pwa-benefit-text">
                            <div class="pwa-benefit-title">Home Screen &amp; Desktop</div>
                            <div class="pwa-benefit-desc">Open the application quickly from your desktop or home screen.</div>
                        </div>
                    </div>

                    <div class="pwa-benefit-item">
                        <div class="pwa-benefit-icon">
                            <i class="fa-solid fa-window-maximize"></i>
                        </div>
                        <div class="pwa-benefit-text">
                            <div class="pwa-benefit-title">Dedicated App Window</div>
                            <div class="pwa-benefit-desc">Launch it in a dedicated app window where supported.</div>
                        </div>
                    </div>

                    <div class="pwa-benefit-item">
                        <div class="pwa-benefit-icon">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="pwa-benefit-text">
                            <div class="pwa-benefit-title">Offline Capabilities</div>
                            <div class="pwa-benefit-desc">Access supported offline features and ultra-fast cached loading.</div>
                        </div>
                    </div>

                    <div class="pwa-benefit-item">
                        <div class="pwa-benefit-icon">
                            <i class="fa-solid fa-shield-heart"></i>
                        </div>
                        <div class="pwa-benefit-text">
                            <div class="pwa-benefit-title">Convenient Experience</div>
                            <div class="pwa-benefit-desc">Enjoy a smooth, distraction-free application experience.</div>
                        </div>
                    </div>
                </div>

                <div class="pwa-btn-group">
                    <button type="button" class="pwa-btn-secondary" onclick="closeToursInstallModal()">
                        Cancel
                    </button>
                    <button type="button" class="pwa-btn-install-primary" id="btnInstallPwaNow" onclick="startToursInstallation()">
                        <i class="fa-solid fa-download"></i>
                        <span>${options.cancelledMessage ? 'Try Again' : 'Install Now'}</span>
                    </button>
                </div>
            `;
        } else if (state === 'progress') {
            // STAGE TWO: Installation Progress Screen
            contentContainer.innerHTML = `
                <div class="pwa-progress-view">
                    <div class="pwa-progress-pulse-wrap">
                        <div class="pwa-pulse-ring"></div>
                        ${renderLogoHtml()}
                    </div>

                    <h3 class="pwa-modal-title" id="pwaModalTitle">Installing Tours</h3>
                    <p class="pwa-modal-desc">Preparing your application...</p>

                    <!-- Animated Indeterminate Progress Bar (No fake percentages) -->
                    <div class="pwa-progress-track" role="progressbar" aria-label="Installing application">
                        <div class="pwa-progress-bar-indeterminate"></div>
                    </div>

                    <div class="pwa-progress-status-row">
                        <div class="pwa-status-left">
                            <span class="pwa-mini-spinner"></span>
                            <span>Installing...</span>
                        </div>
                        <div>Awaiting browser confirmation...</div>
                    </div>
                </div>
            `;
        } else if (state === 'success') {
            // STAGE THREE: Successful Installation
            contentContainer.innerHTML = `
                <div class="pwa-success-view">
                    <div class="pwa-success-emblem-wrap" aria-hidden="true">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <div class="pwa-hero-emblem-wrap" style="width: 58px; height: 58px; margin: 0 auto 0.75rem;">
                        ${renderLogoHtml()}
                    </div>

                    <h3 class="pwa-modal-title" id="pwaModalTitle">Tours Successfully Installed!</h3>
                    <p class="pwa-modal-desc" style="max-width: 380px; margin: 0 auto 1.5rem;">
                        Your application is ready. You can now open Tours directly from your device.
                    </p>

                    <button type="button" class="pwa-btn-install-primary" style="width: 100%;" onclick="closeToursInstallModal()">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Done</span>
                    </button>
                </div>
            `;
        } else if (state === 'installed') {
            // ALREADY INSTALLED STATE
            contentContainer.innerHTML = `
                <div class="pwa-hero-header">
                    <div class="pwa-hero-emblem-wrap">
                        ${renderLogoHtml()}
                        <span style="position: absolute; bottom: -4px; right: -4px; background: #10B981; color: #FFF; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; border: 2px solid #FFF;">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </div>
                    <span class="pwa-app-badge" style="background: #D1FAE5; color: #065F46; border-color: #6EE7B7;">
                        <i class="fa-solid fa-circle-check"></i> App Ready
                    </span>
                    <h3 class="pwa-modal-title" id="pwaModalTitle">Tours is already installed</h3>
                    <p class="pwa-modal-desc">Tours &amp; Travels is already installed on this device. You can open and launch it anytime from your desktop applications or home screen.</p>
                </div>

                <div class="pwa-btn-group" style="justify-content: center;">
                    <button type="button" class="pwa-btn-install-primary" style="width: 100%;" onclick="closeToursInstallModal()">
                        <i class="fa-solid fa-check"></i>
                        <span>Got It</span>
                    </button>
                </div>
            `;
        } else if (state === 'instructions') {
            // UNSUPPORTED / MANUAL INSTRUCTIONS
            let platformTitle = 'Install Tours on Your Device';
            let platformDesc = 'Follow these simple instructions to add Tours to your device:';
            let stepsHtml = '';

            if (isIosDevice()) {
                platformTitle = 'Install Tours on iPhone & iPad';
                platformDesc = 'Follow these quick steps in Apple Safari:';
                stepsHtml = `
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">1</span>
                        <div class="pwa-step-content">
                            Tap the <strong>Share button</strong> (<i class="fa-solid fa-arrow-up-from-bracket" style="color: #176B5B;"></i>) in Safari's bottom toolbar.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">2</span>
                        <div class="pwa-step-content">
                            Scroll down and select <strong>"Add to Home Screen"</strong> (<i class="fa-solid fa-square-plus" style="color: #176B5B;"></i>).
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">3</span>
                        <div class="pwa-step-content">
                            Tap <strong>Add</strong> in the top-right corner to complete.
                        </div>
                    </div>
                `;
            } else if (isAndroidDevice()) {
                platformTitle = 'Install Tours on Android';
                platformDesc = 'Follow these quick steps in Google Chrome:';
                stepsHtml = `
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">1</span>
                        <div class="pwa-step-content">
                            Tap Chrome's <strong>three dots menu</strong> (<i class="fa-solid fa-ellipsis-vertical" style="color: #176B5B;"></i>) in the top-right corner.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">2</span>
                        <div class="pwa-step-content">
                            Select <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong>.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">3</span>
                        <div class="pwa-step-content">
                            Tap <strong>Install</strong> or <strong>Add</strong> to confirm.
                        </div>
                    </div>
                `;
            } else {
                platformTitle = 'Install Tours on Desktop';
                platformDesc = 'Add Tours to your desktop applications:';
                stepsHtml = `
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">1</span>
                        <div class="pwa-step-content">
                            Look for the <strong>Install icon</strong> (<i class="fa-solid fa-download" style="color: #176B5B;"></i>) on the right side of your browser address bar.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">2</span>
                        <div class="pwa-step-content">
                            Or click the browser menu (<i class="fa-solid fa-ellipsis-vertical" style="color: #176B5B;"></i>) &rarr; <strong>"Install Tours..."</strong>.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">3</span>
                        <div class="pwa-step-content">
                            Click <strong>Install</strong> to add Tours to your applications.
                        </div>
                    </div>
                `;
            }

            contentContainer.innerHTML = `
                <div class="pwa-hero-header">
                    <div class="pwa-hero-emblem-wrap">
                        ${renderLogoHtml()}
                    </div>
                    <h3 class="pwa-modal-title" id="pwaModalTitle">${escapeHtml(platformTitle)}</h3>
                    <p class="pwa-modal-desc">${escapeHtml(platformDesc)}</p>
                </div>

                <div class="pwa-manual-steps">
                    ${stepsHtml}
                </div>

                <div class="pwa-btn-group">
                    <button type="button" class="pwa-btn-install-primary" style="width: 100%;" onclick="closeToursInstallModal()">
                        <i class="fa-solid fa-check"></i>
                        <span>Understood</span>
                    </button>
                </div>
            `;
        }

        // Focus primary action button smoothly
        setTimeout(() => {
            const btnPrimary = contentContainer.querySelector('.pwa-btn-install-primary');
            if (btnPrimary && typeof btnPrimary.focus === 'function') {
                btnPrimary.focus();
            }
        }, 50);
    }

    // Open Custom Installation Modal
    window.openToursInstallModal = function () {
        closeNavbarProfileDropdown();
        const modal = ensureCustomInstallModal();

        // Check if already installed
        if (checkStandaloneMode() || isAppInstalled) {
            renderCustomModalState('installed');
        } else {
            renderCustomModalState('ready');
        }

        modal.style.display = 'flex';
        // Trigger smooth CSS opacity transition
        requestAnimationFrame(() => {
            modal.classList.add('is-open');
        });
        modal.setAttribute('aria-hidden', 'false');

        setupModalFocusTrap(modal);
    };

    // Close Custom Installation Modal
    window.closeToursInstallModal = function () {
        const modal = document.getElementById('toursPwaInstallModal');
        if (modal) {
            modal.classList.remove('is-open');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }, 200);
        }
        teardownModalFocusTrap();

        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
            previouslyFocusedElement = null;
        }
    };

    // Start PWA Installation
    window.startToursInstallation = function () {
        // 1. If already installed
        if (checkStandaloneMode() || isAppInstalled) {
            renderCustomModalState('installed');
            return;
        }

        // 2. If native beforeinstallprompt is available
        if (deferredInstallPrompt) {
            // Show Stage Two: Progress Screen immediately
            renderCustomModalState('progress');

            const promptEvent = deferredInstallPrompt;
            deferredInstallPrompt = null;

            // Trigger native prompt
            promptEvent.prompt();

            promptEvent.userChoice.then((choiceResult) => {
                if (choiceResult && choiceResult.outcome === 'accepted') {
                    // Browser installation accepted
                    isAppInstalled = true;
                    setTimeout(() => {
                        renderCustomModalState('success');
                        updateInstallUi(false, true);
                    }, 400);
                } else {
                    // User cancelled / dismissed native prompt
                    renderCustomModalState('ready', {
                        cancelledMessage: 'Installation was cancelled. You can try again whenever you are ready.'
                    });
                }
            }).catch((err) => {
                console.warn('PWA install prompt error:', err);
                renderCustomModalState('instructions');
            });
            return;
        }

        // 3. Fallback: Show platform instructions
        renderCustomModalState('instructions');
    };

    // Primary Entry Point: Triggered from Navbar Profile Dropdown
    window.triggerToursInstall = function (e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        window.openToursInstallModal();
    };

    // Backward-compatible aliases
    window.proceedWithPwaInstall = function () {
        window.startToursInstallation();
    };

    window.closeAllToursModals = function () {
        window.closeToursInstallModal();
    };

    window.closeToursModal = function () {
        window.closeToursInstallModal();
    };

    // ==========================================================================
    // 3. ACCESSIBILITY & FOCUS TRAP
    // ==========================================================================

    function setupModalFocusTrap(modal) {
        previouslyFocusedElement = document.activeElement;
        teardownModalFocusTrap();

        const focusableSelector = 'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        modalKeydownHandler = function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                if (currentModalState !== 'progress') {
                    window.closeToursInstallModal();
                }
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
    // 4. TOAST NOTIFICATIONS HELPER
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
    // 5. AUTO INITIALIZATION
    // ==========================================================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initPwaInstall();
        });
    } else {
        initPwaInstall();
    }

})();
