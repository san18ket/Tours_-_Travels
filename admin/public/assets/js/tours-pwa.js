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
                window.matchMedia('(display-mode: minimal-ui)').matches ||
                window.matchMedia('(display-mode: window-controls-overlay)').matches
            )) ||
            window.navigator.standalone === true ||
            (document.referrer && document.referrer.includes('android-app://')) ||
            (window.location && window.location.search && window.location.search.includes('source=pwa'))
        );
    }

    function isInstalledPwaRunning() {
        return isAppStandalone || checkStandaloneMode();
    }
    window.isInstalledPwaRunning = isInstalledPwaRunning;

    // Helper: Retrieve Dynamic App Name from DOM or Meta
    function getEffectiveAppName() {
        const modal = document.getElementById('toursPwaInstallModal');
        if (modal && modal.getAttribute('data-app-name') && modal.getAttribute('data-app-name').trim() !== '') {
            return modal.getAttribute('data-app-name').trim();
        }
        const appMeta = document.querySelector('meta[name="application-name"]');
        if (appMeta && appMeta.getAttribute('content') && appMeta.getAttribute('content').trim() !== '') {
            return appMeta.getAttribute('content').trim();
        }
        const appleMeta = document.querySelector('meta[name="apple-mobile-web-app-title"]');
        if (appleMeta && appleMeta.getAttribute('content') && appleMeta.getAttribute('content').trim() !== '') {
            return appleMeta.getAttribute('content').trim();
        }
        return 'Tours & Travels';
    }

    // Helper: Retrieve Dynamic App Logo URL from DOM or Meta
    function getEffectiveAppLogo() {
        const modal = document.getElementById('toursPwaInstallModal');
        if (modal && modal.getAttribute('data-logo-url') && modal.getAttribute('data-logo-url').trim() !== '') {
            return modal.getAttribute('data-logo-url').trim();
        }
        const iconLink = document.querySelector('link[rel="apple-touch-icon"]') || document.querySelector('link[rel="icon"]');
        if (iconLink && iconLink.getAttribute('href')) {
            return iconLink.getAttribute('href');
        }
        return appUrl('assets/icons/icon-192.png');
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
            }).catch(() => { });
        }

        // Listen for Chromium beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            updateInstallUi(true, false);

            // If the modal is currently open in 'ready' state, refresh it so "Install Now" is immediately active
            const modal = document.getElementById('toursPwaInstallModal');
            if (modal && modal.classList.contains('is-open') && currentModalState === 'ready') {
                renderCustomModalState('ready');
            }
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            const appName = getEffectiveAppName();
            deferredInstallPrompt = null;
            isAppStandalone = true;
            isAppInstalled = true;
            setInstalledPwaVersion(getRunningAppVersion());
            updateInstallUi(false, true);

            closeToursInstallModal();
            showToursToast(`${appName} installed successfully! You can launch it directly from your device.`, 'success');
        });

        // Initial UI evaluation
        if (isAppStandalone || isAppInstalled) {
            updateInstallUi(false, true);
        } else if (isIosDevice()) {
            updateInstallUi(true, false, true);
        }

        // Mandatory version check for PWA gate
        checkAppVersionUpdate();
    }

    function updateInstallUi(available, installed = false, isIos = false) {
        const appName = getEffectiveAppName();
        const installBtns = document.querySelectorAll('.tours-install-action, .tours-install-btn');
        installBtns.forEach((btn) => {
            if (installed) {
                btn.style.display = 'flex';
                btn.classList.add('is-installed');
                const label = btn.querySelector('.install-btn-label');
                if (label) label.textContent = `${appName} Installed`;
                btn.setAttribute('title', `${appName} is already installed on this device`);
                btn.setAttribute('aria-disabled', 'true');
            } else if (available) {
                btn.style.display = 'flex';
                btn.classList.remove('is-installed');
                const label = btn.querySelector('.install-btn-label');
                if (label) label.textContent = `Install ${appName}`;
                btn.removeAttribute('aria-disabled');
            } else {
                btn.style.display = 'flex';
                btn.classList.remove('is-installed');
                const label = btn.querySelector('.install-btn-label');
                if (label && !label.textContent.trim()) {
                    label.textContent = `Install ${appName}`;
                }
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

            // Dynamic app name & icon from meta tags or defaults
            modal.setAttribute('data-app-name', getEffectiveAppName());
            modal.setAttribute('data-logo-url', getEffectiveAppLogo());
            modal.setAttribute('data-has-custom-logo', '1');

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
            const hasNativePrompt = Boolean(deferredInstallPrompt);
            const isIos = isIosDevice();

            let noticeHtml = '';
            if (options.cancelledMessage) {
                noticeHtml = `
                    <div class="pwa-notice-box" role="alert">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>${escapeHtml(options.cancelledMessage)}</span>
                    </div>
                `;
            } else if (!hasNativePrompt && !isIos) {
                noticeHtml = `
                    <div class="pwa-notice-box" role="note" style="background: rgba(243, 244, 246, 0.95); border: 1px solid #E2E8F0; color: #475569;">
                        <i class="fa-solid fa-circle-info" style="color: #64748B;"></i>
                        <span>Installation is not currently available from this browser session.</span>
                    </div>
                `;
            } else if (isIos) {
                noticeHtml = `
                    <div class="pwa-notice-box" role="note" style="background: rgba(243, 244, 246, 0.95); border: 1px solid #E2E8F0; color: #475569;">
                        <i class="fa-brands fa-apple" style="color: #334155; font-size: 1.15rem;"></i>
                        <span>iOS Safari supports adding to Home Screen via the browser share menu.</span>
                    </div>
                `;
            }

            let primaryBtnHtml = '';
            if (hasNativePrompt) {
                primaryBtnHtml = `
                    <button type="button" class="pwa-btn-install-primary" id="btnInstallPwaNow" onclick="startToursInstallation()">
                        <i class="fa-solid fa-download"></i>
                        <span>${options.cancelledMessage ? 'Try Again' : 'Install Now'}</span>
                    </button>
                `;
            } else {
                primaryBtnHtml = `
                    <button type="button" class="pwa-btn-install-primary" id="btnInstallPwaNow" onclick="renderCustomModalState('instructions')">
                        <i class="fa-solid fa-book-open"></i>
                        <span>View Installation Steps</span>
                    </button>
                `;
            }

            contentContainer.innerHTML = `
                <div class="pwa-hero-header">
                    <div class="pwa-hero-emblem-wrap">
                        ${renderLogoHtml()}
                    </div>
                    <span class="pwa-app-badge"><i class="fa-solid fa-certificate"></i> Official Web App</span>
                    <h3 class="pwa-modal-title" id="pwaModalTitle">Install ${escapeHtml(appName)}</h3>
                    <p class="pwa-modal-desc">Install ${escapeHtml(appName)} on your device for a convenient, dedicated app experience.</p>
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
                    ${primaryBtnHtml}
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

                    <h3 class="pwa-modal-title" id="pwaModalTitle">Installing ${escapeHtml(appName)}</h3>
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

                    <h3 class="pwa-modal-title" id="pwaModalTitle">${escapeHtml(appName)} Successfully Installed!</h3>
                    <p class="pwa-modal-desc" style="max-width: 380px; margin: 0 auto 1.5rem;">
                        Your application is ready. You can now open ${escapeHtml(appName)} directly from your device.
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
                    <h3 class="pwa-modal-title" id="pwaModalTitle">${escapeHtml(appName)} is already installed</h3>
                    <p class="pwa-modal-desc">${escapeHtml(appName)} is already installed on this device. You can open and launch it anytime from your desktop applications or home screen.</p>
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
            let platformTitle = `Install ${appName} on Your Device`;
            let platformDesc = `Follow these simple instructions to add ${appName} to your device:`;
            let stepsHtml = '';

            if (isIosDevice()) {
                platformTitle = `Install ${appName} on iPhone & iPad`;
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
                platformTitle = `Install ${appName} on Android`;
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
                platformTitle = `Install ${appName} on Desktop`;
                platformDesc = `Add ${appName} to your desktop applications:`;
                stepsHtml = `
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">1</span>
                        <div class="pwa-step-content">
                            Click your browser's menu (<i class="fa-solid fa-ellipsis-vertical" style="color: #176B5B;"></i>) in the top-right corner.
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">2</span>
                        <div class="pwa-step-content">
                            Select <strong>"Cast, save, and share"</strong> (or <strong>"More tools"</strong>) &rarr; <strong>"Create shortcut..."</strong> (or <strong>"Install ${escapeHtml(appName)}"</strong>).
                        </div>
                    </div>
                    <div class="pwa-step-row">
                        <span class="pwa-step-badge">3</span>
                        <div class="pwa-step-content">
                            Check <strong>"Open as window"</strong> and click <strong>Create</strong> to launch ${escapeHtml(appName)} as a standalone application.
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
                    <button type="button" class="pwa-btn-secondary" onclick="renderCustomModalState('ready')">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="pwa-btn-install-primary" onclick="closeToursInstallModal()">
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
    function openToursInstallModal() {
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
    }
    window.openToursInstallModal = openToursInstallModal;

    // Close Custom Installation Modal
    function closeToursInstallModal() {
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
    }
    window.closeToursInstallModal = closeToursInstallModal;

    // Start PWA Installation
    async function startToursInstallation() {
        // 1. If already installed
        if (checkStandaloneMode() || isAppInstalled) {
            renderCustomModalState('installed');
            return;
        }

        // 2. If a valid deferred installation event exists
        if (deferredInstallPrompt) {
            const promptEvent = deferredInstallPrompt;
            // Clear prompt immediately so it cannot be reused
            deferredInstallPrompt = null;
            updateInstallUi(false, false);

            try {
                // Invoke browser's native PWA installation dialog
                await promptEvent.prompt();

                // Wait for user choice
                const choiceResult = await promptEvent.userChoice;

                if (choiceResult && choiceResult.outcome === 'accepted') {
                    // Accepted: do not fake installation success or show fake progress.
                    // Wait for the browser's genuine 'appinstalled' event.
                    closeToursInstallModal();
                } else {
                    // Dismissed:
                    // Return to install UI with professional message
                    renderCustomModalState('ready', {
                        cancelledMessage: 'Installation was cancelled. You can try again later.'
                    });
                    showToursToast('Installation was cancelled. You can try again later.', 'info');
                }
            } catch (err) {
                console.warn('[PWA] Native installation prompt failed:', err);
                renderCustomModalState('ready', {
                    cancelledMessage: 'Installation could not be initiated. You can follow manual steps below.'
                });
            }
            return;
        }

        // 3. Fallback: Native prompt unavailable in this session
        renderCustomModalState('instructions');
    }
    window.startToursInstallation = startToursInstallation;

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
    function showToursToast(message, type = 'info') {
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
                <span class="toast-title">Notification</span>
                <span class="toast-msg">${escapeHtml(message)}</span>
            </div>
            <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">&times;</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('is-fadeout');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }
    window.showToursToast = showToursToast;
    window.rebuildToursPwaModalContent = function () {
        const modal = document.getElementById('toursPwaInstallModal');
        if (modal && modal.style.display !== 'none' && currentModalState) {
            renderCustomModalState(currentModalState);
        }
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
    // 5. APPLICATION UPDATE GATE & CENTRALIZED STATE MACHINE
    // ==========================================================================

    const STORAGE_KEY_INSTALLED_VER = 'tours_pwa_installed_version';
    const LEGACY_STORAGE_KEY_INSTALLED_VER = 'tours_app_installed_version';

    // Retrieve the persistent installed PWA version from client storage
    function getInstalledPwaVersion() {
        try {
            const v1 = localStorage.getItem(STORAGE_KEY_INSTALLED_VER);
            if (v1 && v1.trim() !== '') return v1.trim();
            const v2 = localStorage.getItem(LEGACY_STORAGE_KEY_INSTALLED_VER);
            if (v2 && v2.trim() !== '') return v2.trim();
        } catch (e) {
            console.debug('[PWA] Storage read error:', e);
        }
        return null;
    }
    window.getInstalledPwaVersion = getInstalledPwaVersion;

    // Persist verified installed version to client storage
    function setInstalledPwaVersion(ver) {
        if (!ver) return;
        const clean = String(ver).replace(/^v/i, '').trim();
        try {
            localStorage.setItem(STORAGE_KEY_INSTALLED_VER, clean);
            localStorage.setItem(LEGACY_STORAGE_KEY_INSTALLED_VER, clean);
        } catch (e) {
            console.debug('[PWA] Storage write error:', e);
        }
    }
    window.setInstalledPwaVersion = setInstalledPwaVersion;

    // Compare two semantic versions: returns 1 if v1 > v2, -1 if v1 < v2, 0 if equal
    function compareSemanticVersions(v1, v2) {
        if (!v1 && !v2) return 0;
        if (!v1) return -1;
        if (!v2) return 1;

        const parse = (v) => {
            const clean = String(v).replace(/^v/i, '').trim();
            const base = clean.split('-')[0];
            const parts = base.split('.');
            return [
                parseInt(parts[0], 10) || 0,
                parseInt(parts[1], 10) || 0,
                parseInt(parts[2], 10) || 0
            ];
        };
        const p1 = parse(v1);
        const p2 = parse(v2);
        for (let i = 0; i < 3; i++) {
            if (p1[i] > p2[i]) return 1;
            if (p1[i] < p2[i]) return -1;
        }
        return 0;
    }
    window.compareSemanticVersions = compareSemanticVersions;

    // Retrieve active/installed app version
    function getRunningAppVersion() {
        const installed = getInstalledPwaVersion();
        if (installed) return installed;

        const metaVer = document.querySelector('meta[name="app-version"]');
        if (metaVer && metaVer.getAttribute('content') && metaVer.getAttribute('content').trim()) {
            return metaVer.getAttribute('content').trim();
        }
        const modal = document.getElementById('toursPwaUpdateModal') || document.getElementById('toursPwaInstallModal');
        if (modal && modal.getAttribute('data-app-version') && modal.getAttribute('data-app-version').trim()) {
            return modal.getAttribute('data-app-version').trim();
        }
        return '2.4.0';
    }
    window.getRunningAppVersion = getRunningAppVersion;

    // Helper: Logo URL resolution
    function getEffectiveLogoUrl() {
        return getEffectiveAppLogo();
    }
    window.getEffectiveLogoUrl = getEffectiveLogoUrl;

    // Centralized update state
    let updateState = 'IDLE'; // 'IDLE' | 'CHECKING' | 'AVAILABLE' | 'UPDATING' | 'FAILED' | 'VERIFYING'
    let latestVersionPayload = null;
    let isUpdateBlocked = false;
    let updateTimeoutTimer = null;

    // Shake update card when user tries to dismiss (backdrop click or Escape key)
    function shakeUpdateCard() {
        const card = document.getElementById('pwaUpdateCard');
        if (card) {
            card.classList.remove('pwa-card-shake');
            void card.offsetWidth; // Force CSS reflow
            card.classList.add('pwa-card-shake');
            setTimeout(() => {
                card.classList.remove('pwa-card-shake');
            }, 500);
        }
    }
    window.shakeUpdateCard = shakeUpdateCard;

    // Switch Update Modal UI state
    function setUpdateModalState(state, data = null) {
        updateState = state;
        const modal = document.getElementById('toursPwaUpdateModal');
        if (!modal) return;

        const panelAvailable = document.getElementById('pwaUpdateStateAvailable');
        const panelUpdating = document.getElementById('pwaUpdateStateUpdating');
        const panelFailed = document.getElementById('pwaUpdateStateFailed');

        if (state === 'AVAILABLE' || state === 'REQUIRED') {
            // Requirement 4: Mandatory update modal should appear ONLY in installed PWA standalone mode
            if (!isInstalledPwaRunning()) {
                console.debug('[PWA] Update modal suppressed in standard browser mode.');
                return;
            }

            isUpdateBlocked = true;
            closeToursInstallModal(); // Dismiss standard install modal if active

            // Hydrate dynamic branding and version numbers
            const appName = (data && data.app_name) ? data.app_name : (modal.getAttribute('data-app-name') || getEffectiveAppName());
            const iconUrl = (data && data.pwa_icon_url) ? data.pwa_icon_url : (modal.getAttribute('data-logo-url') || getEffectiveLogoUrl());
            const installedVer = (data && data.installed_version) ? data.installed_version : (getInstalledPwaVersion() || getRunningAppVersion());
            const reqVer = (data && (data.minimum_version || data.current_version)) ? (data.minimum_version || data.current_version) : installedVer;

            const appNameEl = document.getElementById('pwaUpdateAppName');
            const iconEl = document.getElementById('pwaUpdateHeroIcon');
            const newVerEl = document.getElementById('pwaUpdateNewVer');
            const curVerEl = document.getElementById('pwaUpdateCurrentVer');
            const reqVerEl = document.getElementById('pwaUpdateRequiredVer');

            if (appNameEl) appNameEl.textContent = appName;
            if (iconEl && iconUrl) iconEl.src = iconUrl;
            if (newVerEl) newVerEl.textContent = 'v' + reqVer;
            if (curVerEl) curVerEl.textContent = 'v' + installedVer;
            if (reqVerEl) reqVerEl.textContent = 'v' + reqVer;

            if (panelAvailable) panelAvailable.style.display = 'block';
            if (panelUpdating) panelUpdating.style.display = 'none';
            if (panelFailed) panelFailed.style.display = 'none';

            modal.style.display = 'flex';
            requestAnimationFrame(() => modal.classList.add('is-open'));

            lockApplicationAccess();

            // Focus primary update button
            setTimeout(() => {
                const btn = document.getElementById('btnPwaUpdateNow');
                if (btn && typeof btn.focus === 'function') btn.focus();
            }, 100);

        } else if (state === 'UPDATING') {
            isUpdateBlocked = true;
            if (panelAvailable) panelAvailable.style.display = 'none';
            if (panelUpdating) panelUpdating.style.display = 'block';
            if (panelFailed) panelFailed.style.display = 'none';

            const appName = (latestVersionPayload && latestVersionPayload.app_name) ? latestVersionPayload.app_name : getEffectiveAppName();
            const updatingNameEl = document.getElementById('pwaUpdatingAppName');
            if (updatingNameEl) updatingNameEl.textContent = appName;

            lockApplicationAccess();

        } else if (state === 'FAILED') {
            isUpdateBlocked = true;
            if (panelAvailable) panelAvailable.style.display = 'none';
            if (panelUpdating) panelUpdating.style.display = 'none';
            if (panelFailed) panelFailed.style.display = 'block';

            lockApplicationAccess();

            // Focus try again button
            setTimeout(() => {
                const btn = document.getElementById('btnPwaTryAgain');
                if (btn && typeof btn.focus === 'function') btn.focus();
            }, 100);

        } else if (state === 'IDLE') {
            isUpdateBlocked = false;
            modal.classList.remove('is-open');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
            unlockApplicationAccess();
        }
    }
    window.setUpdateModalState = setUpdateModalState;

    // Strict Application Lockdown: Disables interaction with dashboard, forms, navigation
    function lockApplicationAccess() {
        document.body.classList.add('pwa-update-blocked');
        document.documentElement.classList.add('pwa-update-blocked');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';

        const modal = document.getElementById('toursPwaUpdateModal');
        if (!modal) return;

        // Backdrop click handler: Shakes card to signal mandatory requirement
        modal.onclick = function (e) {
            if (e.target === modal) {
                e.preventDefault();
                e.stopPropagation();
                shakeUpdateCard();
            }
        };

        // Trap keyboard shortcuts and escape key
        if (!modal.hasAttribute('data-lock-bound')) {
            modal.setAttribute('data-lock-bound', '1');

            window.addEventListener('keydown', (e) => {
                if (!isUpdateBlocked || !modal.classList.contains('is-open')) return;

                if (e.key === 'Escape') {
                    e.preventDefault();
                    e.stopPropagation();
                    shakeUpdateCard();
                    return;
                }

                if (e.key === 'Tab') {
                    // Retain focus within active update modal button
                    const activeBtn = modal.querySelector('button:not([disabled])');
                    if (activeBtn) {
                        e.preventDefault();
                        activeBtn.focus();
                    }
                }
            }, true);

            // Intercept document clicks outside update modal in capture phase
            document.addEventListener('click', (e) => {
                if (isUpdateBlocked && !e.target.closest('#toursPwaUpdateModal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    shakeUpdateCard();
                }
            }, true);

            // Intercept form submissions while update is pending
            document.addEventListener('submit', (e) => {
                if (isUpdateBlocked) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        }
    }

    function unlockApplicationAccess() {
        document.body.classList.remove('pwa-update-blocked');
        document.documentElement.classList.remove('pwa-update-blocked');
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    // Unified trigger: ensures ONE centralized modal even if multiple signals fire
    function triggerAppUpdate(payload = null) {
        // Requirement 4: Mandatory update modal should appear ONLY in installed PWA standalone mode
        if (!isInstalledPwaRunning()) {
            console.debug('[PWA] Update trigger ignored in normal browser mode.');
            return;
        }

        if (updateState === 'UPDATING') return; // Do not interrupt ongoing update
        if (payload) latestVersionPayload = payload;

        const installedVer = (payload && payload.installed_version) 
            ? payload.installed_version 
            : (getInstalledPwaVersion() || getRunningAppVersion());
        const requiredVer = (payload && (payload.minimum_version || payload.current_version))
            ? (payload.minimum_version || payload.current_version)
            : installedVer;

        // Requirement 3: If both versions are equal or installed is newer, NEVER show popup
        if (compareSemanticVersions(installedVer, requiredVer) >= 0) {
            console.debug('[PWA] Version check: installed ' + installedVer + ' is up to date with required ' + requiredVer + '. Suppressing update popup.');
            return;
        }

        const data = latestVersionPayload || {
            app_name: getEffectiveAppName(),
            pwa_icon_url: getEffectiveLogoUrl(),
            current_version: requiredVer,
            minimum_version: requiredVer,
            installed_version: installedVer
        };
        data.installed_version = installedVer;

        setUpdateModalState('AVAILABLE', data);
    }
    window.showPwaUpdateRequiredGate = function (currentVer, data) {
        triggerAppUpdate(data);
    };

    // ==========================================================================
    // SIGNAL A: VERSION ENDPOINT CHECK
    // ==========================================================================
    async function checkAppVersionUpdate() {
        // Requirement 4: Normal browser must never show mandatory update popup
        if (!isInstalledPwaRunning()) {
            return;
        }

        try {
            // Check if completing post-update verification after restart
            const pendingTarget = sessionStorage.getItem('tours_pwa_updating_target_version');

            const endpoint = appUrl('api/v1/app/version') + '?_t=' + Date.now();
            const res = await fetch(endpoint, {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data || !data.success) return;

            latestVersionPayload = data;

            // If we just reloaded after an update, confirm and persist target version
            if (pendingTarget) {
                setInstalledPwaVersion(pendingTarget || data.current_version);
                sessionStorage.removeItem('tours_pwa_updating_target_version');
                setUpdateModalState('IDLE');
                return;
            }

            let installedVer = getInstalledPwaVersion();
            const requiredVer = data.minimum_version || data.current_version;

            // Requirement 3: On first PWA launch, initialize installed version using current server version
            if (!installedVer) {
                setInstalledPwaVersion(data.current_version);
                return;
            }

            // Requirement 3: Compare saved installed version against server minimum required version
            if (compareSemanticVersions(installedVer, requiredVer) < 0) {
                data.installed_version = installedVer;
                triggerAppUpdate(data);
            } else {
                // If versions are equal, never show popup, ensure IDLE state
                if (updateState === 'AVAILABLE' || updateState === 'REQUIRED') {
                    setUpdateModalState('IDLE');
                }
            }
        } catch (err) {
            console.debug('[PWA] Version check deferred (offline or network):', err);
        }
    }
    window.checkAppVersionUpdate = checkAppVersionUpdate;

    // ==========================================================================
    // SIGNAL B: SERVICE WORKER LIFECYCLE WATCHERS (WAITING & UPDATEFOUND)
    // ==========================================================================
    function setupServiceWorkerUpdateWatchers() {
        if (!('serviceWorker' in navigator)) return;

        const inspectRegistration = (reg) => {
            if (!reg) return;

            // Signal B1: A waiting worker already exists at startup/bootstrap
            if (reg.waiting) {
                console.info('[PWA] Waiting Service Worker detected.');
                onServiceWorkerUpdateDetected(reg);
            }

            // Signal B2: Service Worker detects update in progress
            if (!reg._hasToursUpdateListener) {
                reg._hasToursUpdateListener = true;
                reg.addEventListener('updatefound', () => {
                    const newWorker = reg.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        // When new worker finishes installing and enters waiting state
                        if (newWorker.state === 'installed' && (navigator.serviceWorker.controller || reg.waiting || reg.active)) {
                            console.info('[PWA] New Service Worker reached installed state and is waiting.');
                            onServiceWorkerUpdateDetected(reg);
                        }
                    });
                });
            }
        };

        navigator.serviceWorker.getRegistration().then(inspectRegistration).catch(() => {});
        if (navigator.serviceWorker.getRegistrations) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                (regs || []).forEach(inspectRegistration);
            }).catch(() => {});
        }
        if (navigator.serviceWorker.ready) {
            navigator.serviceWorker.ready.then(inspectRegistration).catch(() => {});
        }
    }
    window.setupServiceWorkerUpdateWatchers = setupServiceWorkerUpdateWatchers;

    // Handles Service Worker update detected signal
    async function onServiceWorkerUpdateDetected(registration) {
        // Requirement 4: Normal browser mode: silently activate without modal or interruption
        if (!isInstalledPwaRunning()) {
            console.debug('[PWA] Normal browser mode: silently activating waiting Service Worker.');
            if (registration && registration.waiting) {
                registration.waiting.postMessage({ action: 'SKIP_WAITING' });
            }
            return;
        }

        // Requirement 5: A waiting Service Worker alone must NOT trigger mandatory update popup.
        // Before showing popup:
        // 1. Fetch /api/v1/app/version
        // 2. Read previously installed version
        // 3. Compare with minimum_version
        // 4. Show popup ONLY if installed version is older
        try {
            const endpoint = appUrl('api/v1/app/version') + '?_t=' + Date.now();
            const res = await fetch(endpoint, {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) {
                console.debug('[PWA] Version check response not ok. Deferred.');
                return;
            }

            const data = await res.json();
            if (!data || !data.success) return;

            latestVersionPayload = data;

            let installedVer = getInstalledPwaVersion();
            const requiredVer = data.minimum_version || data.current_version;

            // On first PWA launch, initialize installed version using current server version
            if (!installedVer) {
                setInstalledPwaVersion(data.current_version);
                if (registration && registration.waiting) {
                    registration.waiting.postMessage({ action: 'SKIP_WAITING' });
                }
                return;
            }

            // Only show update modal if installed version is strictly older than required version
            if (compareSemanticVersions(installedVer, requiredVer) < 0) {
                data.installed_version = installedVer;
                triggerAppUpdate(data);
            } else {
                // If versions are equal: do NOT display popup, do NOT block access, handle SW refresh silently
                console.info('[PWA] Service Worker refreshed with matching version (' + installedVer + '). Silently activating.');
                if (registration && registration.waiting) {
                    registration.waiting.postMessage({ action: 'SKIP_WAITING' });
                }
            }
        } catch (e) {
            console.debug('[PWA] Service worker update check deferred (network unavailable):', e);
            // If API temporarily unavailable, do NOT display a false update popup!
        }
    }

    // Check all update signals (both version API and Service Worker)
    function checkAllUpdateSignals() {
        if (!isInstalledPwaRunning()) {
            return;
        }
        checkAppVersionUpdate();
        setupServiceWorkerUpdateWatchers();
    }
    window.checkAllUpdateSignals = checkAllUpdateSignals;

    // ==========================================================================
    // PERFORM PWA UPDATE (ACTIVATE NEW WORKER, PURGE CACHES & VERIFY)
    // ==========================================================================
    async function performPwaUpdate() {
        if (updateTimeoutTimer) {
            clearTimeout(updateTimeoutTimer);
            updateTimeoutTimer = null;
        }

        setUpdateModalState('UPDATING');

        // Infinite reload guard: ensure at least 4 seconds between update reloads
        const now = Date.now();
        const lastReload = parseInt(sessionStorage.getItem('tours_pwa_update_reloaded_at') || '0', 10);
        if (now - lastReload < 4000) {
            console.warn('[PWA] Rapid reload detected. Deferring reload.');
            return;
        }

        // Safety timeout: If update cannot complete within 9 seconds, show failed state
        updateTimeoutTimer = setTimeout(() => {
            console.warn('[PWA] Update timed out. Switching to failed state.');
            setUpdateModalState('FAILED');
        }, 9000);

        try {
            // Save target version we are updating to for post-reload verification
            const targetVer = (latestVersionPayload && (latestVersionPayload.minimum_version || latestVersionPayload.current_version))
                ? (latestVersionPayload.minimum_version || latestVersionPayload.current_version)
                : getRunningAppVersion();
            sessionStorage.setItem('tours_pwa_updating_target_version', targetVer);

            // If ServiceWorker supported in browser
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.getRegistration();

                // Setup one-time controllerchange listener
                let isReloading = false;
                const onControllerChange = () => {
                    if (isReloading) return;
                    isReloading = true;
                    if (updateTimeoutTimer) clearTimeout(updateTimeoutTimer);
                    navigator.serviceWorker.removeEventListener('controllerchange', onControllerChange);
                    sessionStorage.setItem('tours_pwa_update_reloaded_at', String(Date.now()));
                    setInstalledPwaVersion(targetVer);
                    window.location.reload(true);
                };
                navigator.serviceWorker.addEventListener('controllerchange', onControllerChange);

                // Case 1: A worker is already in waiting state
                let waitingWorker = (reg && reg.waiting) ? reg.waiting : null;
                if (!waitingWorker && navigator.serviceWorker.getRegistrations) {
                    try {
                        const allRegs = await navigator.serviceWorker.getRegistrations();
                        for (const r of allRegs) {
                            if (r && r.waiting) {
                                waitingWorker = r.waiting;
                                break;
                            }
                        }
                    } catch (e) {}
                }

                if (waitingWorker) {
                    waitingWorker.postMessage({ action: 'SKIP_WAITING' });
                    return;
                }

                // Case 2: Check for updates via registration.update()
                if (reg) {
                    await reg.update();
                    if (reg.waiting) {
                        reg.waiting.postMessage({ action: 'SKIP_WAITING' });
                        return;
                    }
                }
            }

            // Fallback: If no service worker change detected or SW unsupported,
            // purge obsolete caches and reload to obtain latest server assets
            if ('caches' in window) {
                try {
                    const keys = await caches.keys();
                    await Promise.all(
                        keys.filter(k => k.startsWith('tours-')).map(k => caches.delete(k))
                    );
                } catch (e) { }
            }

            if (updateTimeoutTimer) clearTimeout(updateTimeoutTimer);
            sessionStorage.setItem('tours_pwa_update_reloaded_at', String(Date.now()));
            setInstalledPwaVersion(targetVer);
            window.location.reload(true);

        } catch (err) {
            console.error('[PWA] Update execution error:', err);
            if (updateTimeoutTimer) clearTimeout(updateTimeoutTimer);
            setUpdateModalState('FAILED');
        }
    }
    window.performPwaUpdate = performPwaUpdate;

    // ==========================================================================
    // PROACTIVE UPDATE DETECTION WATCHERS
    // ==========================================================================
    let lastUpdateCheckTime = 0;
    function throttledCheckAllUpdateSignals() {
        const now = Date.now();
        if (now - lastUpdateCheckTime < 5000) return;
        lastUpdateCheckTime = now;
        checkAllUpdateSignals();
    }

    function setupProactiveUpdateWatchers() {
        // 1. Check on window / tab focus
        window.addEventListener('focus', () => {
            throttledCheckAllUpdateSignals();
        });

        // 2. Check when page visibility changes (user switches back to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                throttledCheckAllUpdateSignals();
            }
        });

        // 3. Check when network connection is restored
        window.addEventListener('online', () => {
            throttledCheckAllUpdateSignals();
        });

        // 4. Regular background interval check (every 30 seconds)
        setInterval(checkAllUpdateSignals, 30000);

        // 5. Service Worker update lifecycle watchers
        setupServiceWorkerUpdateWatchers();
    }

    // ==========================================================================
    // 6. AUTO INITIALIZATION
    // ==========================================================================
    function onInit() {
        initPwaInstall();
        setupProactiveUpdateWatchers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onInit);
    } else {
        onInit();
    }

})();
