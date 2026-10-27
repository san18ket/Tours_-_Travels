/**
 * VoyageGo Tours & Travels - Core Client JavaScript
 * Vanilla JS with PWA integration, Telemetry, and Offline handling
 */

(function () {
    'use strict';

    // Global Client Translation Helper
    function __trans(key, fallback) {
        if (window.VoyageTranslations && typeof window.VoyageTranslations[key] === 'string') {
            return window.VoyageTranslations[key];
        }
        return fallback !== undefined ? fallback : key;
    }
    window.__trans = __trans;

    // Global CSRF Token Retrieval
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Network status listener
    function setupNetworkListener() {
        const offlineBanner = document.getElementById('offlineBanner');
        if (!offlineBanner) return;

        // Ensure banner is hidden by default on initialization
        offlineBanner.style.display = 'none';

        const isLocalHost = Boolean(
            window.location.hostname === 'localhost' ||
            window.location.hostname === '127.0.0.1' ||
            window.location.hostname === '[::1]' ||
            window.location.hostname.match(/^192\.168\./) ||
            window.location.hostname.match(/^10\./) ||
            window.location.hostname.match(/^172\.(1[6-9]|2[0-9]|3[0-1])\./) ||
            window.location.hostname.endsWith('.local') ||
            window.location.protocol === 'file:'
        );

        function showOffline() {
            const bannerText = document.getElementById('offlineBannerText');
            let message = __trans('js.offline_banner', 'Offline Mode: Live telemetry synchronization paused. Changes will sync once connection returns.');
            message = message.replace(/^[⚠️⚠\s]+/, '');
            if (bannerText) {
                bannerText.textContent = message;
            } else {
                offlineBanner.innerText = '⚠️ ' + message;
            }
            offlineBanner.style.display = 'flex';
        }

        function hideOffline() {
            offlineBanner.style.display = 'none';
        }

        // On localhost/intranet environments, navigator.onLine can be false if there is no WAN probe
        // even though local server & telemetry are active. Only probe if not on localhost.
        if (!isLocalHost && navigator.onLine === false) {
            fetch(window.location.href, { method: 'HEAD', cache: 'no-store' })
                .then(function () { hideOffline(); })
                .catch(function () { showOffline(); });
        }

        window.addEventListener('online', function () {
            hideOffline();
        });

        window.addEventListener('offline', function () {
            fetch(window.location.href, { method: 'HEAD', cache: 'no-store' })
                .then(function () { hideOffline(); })
                .catch(function () { showOffline(); });
        });
    }

    // Auto dismiss alert flash messages after 5 seconds
    function setupAlertAutoDismiss() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        });
    }

    // Global CSRF Token Retrieval
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const input = document.querySelector('input[name="_csrf_token"], input[name="csrf_token"], input[name="_token"]');
        return input ? input.value : '';
    }
    window.getCsrfToken = getCsrfToken;

    // Subdirectory-aware GPS API Endpoint Resolver
    function getGpsUpdateUrl() {
        const meta = document.querySelector('meta[name="gps-update-url"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const baseMeta = document.querySelector('meta[name="base-url"]');
        if (baseMeta && baseMeta.getAttribute('content')) {
            const base = baseMeta.getAttribute('content').replace(/\/$/, '');
            return (base ? base : '') + '/api/gps/update';
        }
        if (window.VoyageConfig && window.VoyageConfig.gpsUpdateUrl) {
            return window.VoyageConfig.gpsUpdateUrl;
        }
        return '/api/gps/update';
    }
    window.getGpsUpdateUrl = getGpsUpdateUrl;

    // Driver Live GPS Telemetry Engine
    window.VoyageTelemetry = {
        watchId: null,
        isTracking: false,
        updateUrl: null,
        lastSuccessfulSync: null,
        lastCoords: null,
        highAccuracyMode: true,

        // Callbacks
        onRawFix: null,
        onServerConfirmed: null,
        onError: null,
        onStateChange: null,

        getResolvedUrl: function () {
            return this.updateUrl || getGpsUpdateUrl();
        },

        start: function (updateUrl, options) {
            if (updateUrl) {
                this.updateUrl = updateUrl;
            } else if (!this.updateUrl) {
                this.updateUrl = getGpsUpdateUrl();
            }

            // If already tracking, clear the old watcher first
            if (this.isTracking && this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }

            // 1. Browser Geolocation Support Check
            if (!navigator.geolocation) {
                const msg = 'Geolocation is not supported by this browser or device.';
                this.notifyError(msg, 'unsupported');
                return false;
            }

            // 2. HTTPS / Secure Context Check
            if (window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                const msg = 'Geolocation requires a secure connection (HTTPS). Current connection is insecure (HTTP).';
                this.notifyError(msg, 'insecure_context');
                return false;
            }

            this.isTracking = true;
            this.highAccuracyMode = options?.enableHighAccuracy !== undefined ? options.enableHighAccuracy : true;
            this.notifyState(true);

            const radarIndicator = document.getElementById('gpsRadarIndicator');
            const statusText = document.getElementById('gpsStatusText');
            if (radarIndicator) radarIndicator.style.display = 'inline-block';
            if (statusText) statusText.innerText = this.highAccuracyMode ? 'Acquiring Satellite GPS Signal...' : 'Acquiring Network Location...';

            this.startWatcher(options);
            return true;
        },

        startWatcher: function (options) {
            const highAcc = this.highAccuracyMode;
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.lastCoords = position.coords;
                    this.notifyRawFix(position.coords);
                    this.sendLocation(position.coords).catch(() => {});
                },
                (error) => {
                    this.handleGeoError(error, options);
                },
                {
                    enableHighAccuracy: highAcc,
                    maximumAge: options?.maximumAge ?? (highAcc ? 3000 : 30000),
                    timeout: options?.timeout ?? (highAcc ? 12000 : 20000)
                }
            );
        },

        sendLocation: function (coords, overrideUrl) {
            const token = getCsrfToken();
            const targetUrl = overrideUrl || this.getResolvedUrl();
            const payload = {
                latitude: Number(coords.latitude.toFixed(8)),
                longitude: Number(coords.longitude.toFixed(8)),
                accuracy: coords.accuracy != null ? Number(coords.accuracy.toFixed(1)) : null,
                speed_kmh: coords.speed != null ? Number((coords.speed * 3.6).toFixed(1)) : 0.0,
                heading_degrees: coords.heading != null && !isNaN(coords.heading) ? Number(coords.heading.toFixed(1)) : null,
                _csrf_token: token
            };

            return fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
                .then(async (res) => {
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = { success: false, error: 'Server returned non-JSON response (HTTP ' + res.status + ')' };
                    }

                    if (!res.ok || data.success !== true) {
                        const errorMsg = data.error || ('Server rejected telemetry update (HTTP ' + res.status + ')');
                        throw new Error(errorMsg);
                    }

                    // Strict confirmation: only update when the server explicitly confirms!
                    this.lastSuccessfulSync = new Date();
                    const lastSync = document.getElementById('lastGpsSyncTime');
                    if (lastSync) {
                        lastSync.innerText = data.data?.formatted_time || this.lastSuccessfulSync.toLocaleTimeString();
                    }

                    const statusText = document.getElementById('gpsStatusText');
                    if (statusText && this.isTracking) {
                        statusText.innerText = 'Live GPS Streaming (' + (data.data?.policy === 'active_trip' ? 'Trip ' + (data.data?.booking_code || '') : 'On Duty') + ')';
                    }

                    this.notifyServerConfirmed(coords, data);
                    return data;
                })
                .catch((err) => {
                    console.warn('Telemetry transmission error:', err.message);
                    this.notifyError(err.message, 'api_error', err);
                    throw err;
                });
        },

        handleGeoError: function (error, options) {
            let msg = 'Could not acquire GPS fix: ' + (error.message || 'Unknown GPS error');
            let type = 'geolocation_error';

            if (error.code === 1) { // PERMISSION_DENIED
                msg = 'GPS permission denied. Please allow location access in your browser address bar/settings.';
                type = 'permission_denied';
                this.stop(); // Stop polling when user explicitly denies
            } else if (error.code === 2) { // POSITION_UNAVAILABLE
                msg = 'GPS signal unavailable. Ensure device location hardware/services are enabled.';
                type = 'position_unavailable';
            } else if (error.code === 3) { // TIMEOUT
                // Adaptive Fallback: If high-accuracy satellite lock timed out (common on desktop/indoor), switch to network
                if (this.highAccuracyMode) {
                    console.warn('High-accuracy GPS timed out. Switching to standard network/Wi-Fi positioning...');
                    this.highAccuracyMode = false;
                    const statusText = document.getElementById('gpsStatusText');
                    if (statusText) statusText.innerText = 'Satellite timeout. Acquiring Wi-Fi / Network location...';
                    if (this.isTracking && this.watchId !== null) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.startWatcher(options);
                    }
                    return;
                }
                msg = 'Location request timed out. Please check Windows location services or retry.';
                type = 'timeout';
            }

            const statusText = document.getElementById('gpsStatusText');
            if (statusText) statusText.innerText = msg;

            this.notifyError(msg, type, error);
        },

        stop: function () {
            if (this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }
            this.isTracking = false;
            this.highAccuracyMode = true;

            const radarIndicator = document.getElementById('gpsRadarIndicator');
            const statusText = document.getElementById('gpsStatusText');
            if (radarIndicator) radarIndicator.style.display = 'none';
            if (statusText) statusText.innerText = 'Standby (Sharing Stopped)';

            this.notifyState(false);
        },

        notifyRawFix: function (coords) {
            if (typeof this.onRawFix === 'function') {
                try { this.onRawFix(coords); } catch (e) { console.error(e); }
            }
            window.dispatchEvent(new CustomEvent('voyage:gps-raw', { detail: { coords } }));
        },

        notifyServerConfirmed: function (coords, data) {
            if (typeof this.onServerConfirmed === 'function') {
                try { this.onServerConfirmed(coords, data); } catch (e) { console.error(e); }
            }
            window.dispatchEvent(new CustomEvent('voyage:gps-confirmed', { detail: { coords, data } }));
        },

        notifyError: function (message, type, raw) {
            if (typeof this.onError === 'function') {
                try { this.onError(message, type, raw); } catch (e) { console.error(e); }
            }
            window.dispatchEvent(new CustomEvent('voyage:gps-error', { detail: { message, type, raw } }));
        },

        notifyState: function (isTracking) {
            if (typeof this.onStateChange === 'function') {
                try { this.onStateChange(isTracking); } catch (e) { console.error(e); }
            }
            window.dispatchEvent(new CustomEvent('voyage:gps-state', { detail: { isTracking } }));
        }
    };

    // PWA Service Worker Registration with dynamic subdirectory resolution & explicit scope
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            const swMeta = document.querySelector('meta[name="sw-url"]');
            let swUrl = swMeta ? swMeta.getAttribute('content') : null;
            let baseUrl = '';

            const baseMeta = document.querySelector('meta[name="base-url"]');
            if (baseMeta) {
                baseUrl = baseMeta.getAttribute('content').replace(/\/$/, '');
            } else {
                // Auto-detect subdirectory from app.js script source if base-url meta is omitted
                const scriptEl = document.querySelector('script[src*="app.js"]');
                if (scriptEl) {
                    try {
                        const parsedUrl = new URL(scriptEl.src, window.location.origin);
                        const assetIdx = parsedUrl.pathname.indexOf('/assets/');
                        if (assetIdx !== -1) {
                            baseUrl = parsedUrl.pathname.substring(0, assetIdx);
                        }
                    } catch (e) {
                        // ignore
                    }
                }
            }

            if (!swUrl) {
                swUrl = baseUrl ? (baseUrl + '/sw.js') : '/sw.js';
            }

            const regOptions = baseUrl ? { scope: baseUrl + '/' } : {};

            navigator.serviceWorker.register(swUrl, regOptions)
                .then(reg => {
                    console.log('ServiceWorker registered successfully with scope:', reg.scope);
                })
                .catch(err => {
                    console.warn('ServiceWorker registration skipped or unsupported in context:', err);
                });
        });
    }

    // Modal Helper
    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // Close modal when clicking outside backdrop
    window.addEventListener('click', function (e) {
        if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
            e.target.style.display = 'none';
        }
    });

    // Mobile Sidebar Drawer Toggle
    window.toggleSidebar = function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar) {
            sidebar.classList.toggle('active');
            if (backdrop) {
                backdrop.classList.toggle('active');
            }
        }
    };

    window.closeSidebar = function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if (backdrop) {
            backdrop.classList.remove('active');
        }
        if (toggleBtn && window.innerWidth <= 900) {
            toggleBtn.classList.remove('is-collapsed');
        }
    };

    // Auto-close sidebar on responsive devices when any navigation link is clicked
    document.addEventListener('click', function (e) {
        const link = e.target.closest('#sidebar a[href], .sidebar a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:')) return;

        // If sidebar is active or viewport is mobile/tablet, close drawer smoothly
        const sidebar = document.getElementById('sidebar');
        if (sidebar && (sidebar.classList.contains('active') || window.innerWidth <= 1024)) {
            window.closeSidebar();
        }
    });

    // =========================================================================
    // Professional Single-Dropdown Accordion Engine
    // Default: All dropdowns closed.
    // Accordion Rule: If one dropdown is open and user clicks another, first close
    // the currently open dropdown, and after that open the clicked dropdown.
    // =========================================================================
    let isAccordionTransitioning = false;

    function openDropdown(catId) {
        const header = document.getElementById(catId + '-header');
        const items = document.getElementById(catId + '-items');
        if (!header || !items) return;

        header.setAttribute('aria-expanded', 'true');
        header.classList.add('open');
        items.classList.add('expanded');
    }

    function closeDropdown(catId) {
        const header = document.getElementById(catId + '-header');
        const items = document.getElementById(catId + '-items');
        if (!header || !items) return;

        header.setAttribute('aria-expanded', 'false');
        header.classList.remove('open');
        items.classList.remove('expanded');
    }

    window.toggleCategory = function (categoryId) {
        if (isAccordionTransitioning) return;

        const sidebar = document.getElementById('sidebar');
        // If sidebar is collapsed (icon-only mode), clicking a category expands the sidebar to reveal options
        if (sidebar && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            localStorage.setItem('vg_sidebar_collapsed', 'false');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            if (toggleBtn) toggleBtn.classList.remove('is-collapsed');
            openDropdown(categoryId);
            return;
        }

        const targetHeader = document.getElementById(categoryId + '-header');
        const targetItems = document.getElementById(categoryId + '-items');
        if (!targetHeader || !targetItems) return;

        const isCurrentlyExpanded = targetHeader.getAttribute('aria-expanded') === 'true' || targetItems.classList.contains('expanded');

        // Locate any currently open dropdown (different from clicked)
        const allHeaders = document.querySelectorAll('.category-header');
        let currentlyOpenId = null;

        allHeaders.forEach(header => {
            const hId = header.id.replace('-header', '');
            if (hId !== categoryId && (header.getAttribute('aria-expanded') === 'true' || header.classList.contains('open'))) {
                currentlyOpenId = hId;
            }
        });

        if (isCurrentlyExpanded) {
            // Dropdown is already open: toggle it closed
            closeDropdown(categoryId);
        } else {
            // Dropdown needs to open
            if (currentlyOpenId) {
                // FIRST: close the open dropdown
                isAccordionTransitioning = true;
                closeDropdown(currentlyOpenId);

                // AFTER THAT: open the clicked dropdown after close animation starts
                setTimeout(() => {
                    openDropdown(categoryId);
                    isAccordionTransitioning = false;
                }, 220); // 220ms smooth sequence
            } else {
                // No open dropdown: open immediately
                openDropdown(categoryId);
            }
        }
    };

    // Professional Sidebar Collapse Engine:
    // On Desktop (>900px): collapses sidebar into an icon-only mini-rail.
    // On Mobile (<=900px): toggles off-canvas drawer navigation.
    window.toggleSidebarCollapse = function () {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (!sidebar) return;

        if (window.innerWidth <= 900) {
            const isNowActive = sidebar.classList.toggle('active');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (backdrop) backdrop.classList.toggle('active', isNowActive);
            if (toggleBtn) toggleBtn.classList.toggle('is-collapsed', isNowActive);
        } else {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('vg_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            if (toggleBtn) {
                toggleBtn.classList.toggle('is-collapsed', isCollapsed);
            }
        }
    };

    // Aliases for backward compatibility
    window.toggleDesktopSidebar = window.toggleSidebarCollapse;

    // Initialize Sidebar State:
    // When any dropdown list page is open, keep that current page dropdown open.
    // Close all other dropdowns.
    function initSidebarState() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (sidebar && window.innerWidth > 900) {
            const savedCollapsed = localStorage.getItem('vg_sidebar_collapsed');
            if (savedCollapsed === 'true') {
                sidebar.classList.add('collapsed');
                if (toggleBtn) {
                    toggleBtn.classList.add('is-collapsed');
                }
            }
        }

        // Detect if the current page belongs to a dropdown category
        let activeCatId = null;
        const activeLink = document.querySelector('.sidebar-nav .nav-link.active');
        if (activeLink) {
            const activeItems = activeLink.closest('.sidebar-category-items');
            if (activeItems && activeItems.id) {
                activeCatId = activeItems.id.replace('-items', '');
            }
        }

        if (!activeCatId) {
            const activeHeader = document.querySelector('.category-header.has-active, .category-header.open');
            if (activeHeader && activeHeader.id) {
                activeCatId = activeHeader.id.replace('-header', '');
            }
        }

        if (!activeCatId) {
            const activeItems = document.querySelector('.sidebar-category-items.expanded');
            if (activeItems && activeItems.id) {
                activeCatId = activeItems.id.replace('-items', '');
            }
        }

        // Keep current page dropdown open; ensure all other dropdowns are closed
        document.querySelectorAll('.category-header').forEach(header => {
            const catId = header.id.replace('-header', '');
            if (activeCatId && catId === activeCatId) {
                openDropdown(catId);
            } else {
                closeDropdown(catId);
            }
        });
    }

    // Accessible Language Switcher Dropdown Interaction & Keyboard Navigation
    function setupLanguageDropdown() {
        if (!window.vgToggleLanguageDropdown) {
            window.vgToggleLanguageDropdown = function (btn, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const dropdown = btn ? btn.closest('.vg-lang-dropdown') : null;
                if (!dropdown) return;
                const wasOpen = dropdown.classList.contains('is-open');

                // Close all language dropdowns
                document.querySelectorAll('.vg-lang-dropdown.is-open').forEach(d => {
                    d.classList.remove('is-open');
                    const b = d.querySelector('.vg-lang-btn');
                    if (b) b.setAttribute('aria-expanded', 'false');
                });

                // If not open before, open now
                if (!wasOpen) {
                    dropdown.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            };
        }

        if (window.__vgLangDropdownAppBound) return;
        window.__vgLangDropdownAppBound = true;

        // Global delegated click handler for language dropdown buttons and click-outside
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.vg-lang-btn');
            const dropdown = trigger ? trigger.closest('.vg-lang-dropdown') : null;

            if (trigger && dropdown) {
                window.vgToggleLanguageDropdown(trigger, e);
                return;
            }

            // Click outside closes all language dropdowns
            if (!e.target.closest('.vg-lang-dropdown')) {
                document.querySelectorAll('.vg-lang-dropdown.is-open').forEach(d => {
                    d.classList.remove('is-open');
                    const btn = d.querySelector('.vg-lang-btn');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                });
            }
        });

        // Global Keyboard Navigation for Language Switchers
        document.addEventListener('keydown', (e) => {
            const openDropdown = document.querySelector('.vg-lang-dropdown.is-open');

            // Escape closes dropdown and returns focus to button
            if (e.key === 'Escape' && openDropdown) {
                e.preventDefault();
                openDropdown.classList.remove('is-open');
                const trigger = openDropdown.querySelector('.vg-lang-btn');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                }
                return;
            }

            // Arrow down or Enter/Space on closed button opens dropdown
            const focusedTrigger = document.activeElement ? document.activeElement.closest('.vg-lang-btn') : null;
            if (focusedTrigger && !openDropdown) {
                if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.vgToggleLanguageDropdown(focusedTrigger, e);
                    const dd = focusedTrigger.closest('.vg-lang-dropdown');
                    if (dd) {
                        const firstItem = dd.querySelector('.vg-lang-item');
                        if (firstItem) firstItem.focus();
                    }
                }
                return;
            }

            // Navigation within open menu
            if (openDropdown && (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Home' || e.key === 'End')) {
                const items = Array.from(openDropdown.querySelectorAll('.vg-lang-item'));
                if (!items.length) return;
                const currentIndex = items.indexOf(document.activeElement);

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = (currentIndex + 1) % items.length;
                    items[next].focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = (currentIndex - 1 + items.length) % items.length;
                    items[prev].focus();
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    items[0].focus();
                } else if (e.key === 'End') {
                    e.preventDefault();
                    items[items.length - 1].focus();
                }
            }
        });
    }

    // =========================================================================
    // VoyageGo Full AJAX SPA & Form Engine
    // All buttons, forms, table filters, actions & navigation operate via AJAX
    // =========================================================================
    const VoyageAjax = {
        progressBarEl: null,
        progressTimer: null,
        isNavigating: false,
        initialized: false,

        init: function () {
            if (this.initialized) return;
            this.initialized = true;
            this.setupTopProgressBar();
            this.setupFormSubmissions();
            this.setupLinkNavigation();
            this.setupActionButtons();
            this.setupPopState();
            this.scanAndToastFlashes();
        },

        // 1. Top Loading Progress Bar
        setupTopProgressBar: function () {
            if (document.getElementById('vg-top-progress-bar')) {
                this.progressBarEl = document.getElementById('vg-top-progress-bar');
                return;
            }
            const bar = document.createElement('div');
            bar.id = 'vg-top-progress-bar';
            document.body.appendChild(bar);
            this.progressBarEl = bar;
        },

        startProgress: function () {
            if (!this.progressBarEl) this.setupTopProgressBar();
            const bar = this.progressBarEl;
            if (!bar) return;
            clearTimeout(this.progressTimer);
            bar.style.transition = 'width 0.2s ease-out, opacity 0.2s ease';
            bar.style.opacity = '1';
            bar.style.width = '25%';

            this.progressTimer = setTimeout(() => {
                if (bar) bar.style.width = '70%';
            }, 180);
        },

        finishProgress: function () {
            const bar = this.progressBarEl;
            if (!bar) return;
            clearTimeout(this.progressTimer);
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => {
                    bar.style.width = '0%';
                }, 300);
            }, 200);
        },

        // 2. Button Loading State
        setButtonLoading: function (btn, isLoading) {
            if (!btn) return;
            if (isLoading) {
                if (!btn.dataset.vgOriginalHtml) {
                    btn.dataset.vgOriginalHtml = btn.innerHTML;
                }
                btn.disabled = true;
                btn.classList.add('is-loading');
                btn.setAttribute('aria-busy', 'true');

                if (!btn.querySelector('.vg-btn-spinner')) {
                    const spinner = document.createElement('span');
                    spinner.className = 'vg-btn-spinner';
                    spinner.setAttribute('aria-hidden', 'true');
                    spinner.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-linecap="round"></circle></svg>';
                    btn.insertBefore(spinner, btn.firstChild);
                }
            } else {
                if (btn.dataset.vgOriginalHtml) {
                    btn.innerHTML = btn.dataset.vgOriginalHtml;
                    delete btn.dataset.vgOriginalHtml;
                }
                btn.disabled = false;
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
            }
        },

        // 3. Universal Floating Message System (3-Second Auto-Dismiss with Visual Countdown)
        toast: function (message, type = 'success', duration = 3000) {
            if (!message) return;
            window.vgNotify(message, type, duration);
        },

        // Helper: Check if URL triggers file download / export
        isDownloadUrl: function (url) {
            if (!url) return false;
            const clean = url.toLowerCase().split('?')[0];
            return (
                clean.includes('/download') ||
                clean.includes('/export') ||
                clean.endsWith('.pdf') ||
                clean.endsWith('.sql') ||
                clean.endsWith('.zip') ||
                clean.endsWith('.csv') ||
                clean.endsWith('.xlsx')
            );
        },

        // 4. Form Submissions Interception
        setupFormSubmissions: function () {
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                if (form.getAttribute('data-no-ajax') === 'true' || form.dataset.noAjax === 'true') return;
                if (form.classList.contains('vg-lang-form')) return; // handled by language component

                const action = form.getAttribute('action') || window.location.href;
                if (action.includes('/logout') || (form.action && form.action.includes('/logout'))) return; // Native core PHP logout
                if (this.isDownloadUrl(action)) return;

                // Respect custom onsubmit function if registered
                if (typeof form.onsubmit === 'function') {
                    try {
                        const result = form.onsubmit(e);
                        if (result === false) return;
                    } catch (err) {
                        console.warn('Form onsubmit check failed:', err);
                    }
                }

                // Universal constraint validation without browser popup alerts
                if (window.VoyageValidation && !window.VoyageValidation.validateForm(form)) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                this.submitForm(form, e.submitter);
            });
        },

        submitForm: function (form, submitter) {
            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !window.confirm(confirmMsg)) return;

            const btn = submitter || form.querySelector('button[type="submit"], input[type="submit"], button:not([type="button"])');
            this.setButtonLoading(btn, true);
            this.startProgress();

            const action = form.getAttribute('action') || window.location.href;
            const method = (form.getAttribute('method') || 'GET').toUpperCase();

            // GET forms (search, filter, pagination queries)
            if (method === 'GET') {
                const formData = new FormData(form);
                const searchParams = new URLSearchParams();
                for (const [key, val] of formData.entries()) {
                    if (val !== '') searchParams.append(key, val);
                }
                const cleanBase = action.split('?')[0];
                const queryString = searchParams.toString();
                const targetUrl = queryString ? `${cleanBase}?${queryString}` : cleanBase;

                this.navigate(targetUrl, { pushState: true, submitterBtn: btn });
                return;
            }

            // POST forms (data mutations, creates, edits, actions)
            const formData = new FormData(form);
            if (submitter && submitter.name && !formData.has(submitter.name)) {
                formData.append(submitter.name, submitter.value || '');
            }

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'text/html,application/xhtml+xml,application/json'
                }
            })
            .then(async (response) => {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    this.handleJsonResult(data, form, response);
                } else {
                    const htmlText = await response.text();
                    this.applyHtml(htmlText, response.url || action, true);
                }
            })
            .catch((err) => {
                console.error('VoyageGo Form AJAX error:', err);
                this.toast('Request failed. Please check network connection.', 'error');
            })
            .finally(() => {
                this.setButtonLoading(btn, false);
                this.finishProgress();
            });
        },

        handleJsonResult: function (data, form, response) {
            // Only toast if not redirecting, as destination page delivers the persistent session flash
            if (data.message && !data.redirect) {
                this.toast(data.message, data.success ? 'success' : 'error');
            }

            if (data.redirect) {
                this.navigate(data.redirect, { pushState: true });
                return;
            }

            if (data.errors && typeof data.errors === 'object') {
                Object.keys(data.errors).forEach(field => {
                    const el = form ? (form.querySelector(`[name="${field}"]`) || form.querySelector(`#${field}`)) : null;
                    if (el) {
                        el.classList.add('is-invalid');
                        const errorMsg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        let feedback = el.parentElement.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.style.color = '#EF4444';
                            feedback.style.fontSize = '0.8rem';
                            feedback.style.marginTop = '0.25rem';
                            el.parentElement.appendChild(feedback);
                        }
                        feedback.textContent = errorMsg;
                    }
                });
                const firstErr = Object.values(data.errors)[0];
                const msg = Array.isArray(firstErr) ? firstErr[0] : (typeof firstErr === 'string' ? firstErr : 'Validation failed.');
                this.toast(msg, 'error');
            }
        },

        // 5. Link Navigation Interception (SPA)
        setupLinkNavigation: function () {
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                // Handle data-confirm links
                const confirmMsg = link.getAttribute('data-confirm');
                if (confirmMsg && !window.confirm(confirmMsg)) {
                    e.preventDefault();
                    return;
                }

                if (this.shouldInterceptLink(link, e)) {
                    e.preventDefault();
                    this.navigate(link.href, { pushState: true });
                }
            });
        },

        shouldInterceptLink: function (link, e) {
            if (e.button !== 0) return false; // left-click only
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false; // allow opening in new tab
            if (link.target === '_blank') return false;
            if (link.hasAttribute('download')) return false;
            if (link.getAttribute('data-no-ajax') === 'true' || link.dataset.noAjax === 'true') return false;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || href.includes('/logout')) {
                return false;
            }

            if (this.isDownloadUrl(link.href)) return false;

            try {
                const url = new URL(link.href, window.location.origin);
                return url.origin === window.location.origin;
            } catch (err) {
                return false;
            }
        },

        // 6. Action Buttons Setup (Buttons with data-ajax-action or custom triggers)
        setupActionButtons: function () {
            document.addEventListener('click', (e) => {
                const actionBtn = e.target.closest('[data-ajax-action]');
                if (!actionBtn) return;

                const confirmMsg = actionBtn.getAttribute('data-confirm');
                if (confirmMsg && !window.confirm(confirmMsg)) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                const actionUrl = actionBtn.getAttribute('data-ajax-action');
                const method = (actionBtn.getAttribute('data-method') || 'POST').toUpperCase();

                this.setButtonLoading(actionBtn, true);
                this.startProgress();

                const formData = new FormData();
                formData.append('_token', getCsrfToken());

                fetch(actionUrl, {
                    method: method,
                    body: (method === 'POST' || method === 'PUT') ? formData : null,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'text/html,application/xhtml+xml,application/json'
                    }
                })
                .then(async (response) => {
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const data = await response.json();
                        this.handleJsonResult(data, null, response);
                    } else {
                        const htmlText = await response.text();
                        this.applyHtml(htmlText, response.url || window.location.href, true);
                    }
                })
                .catch((err) => {
                    console.error('VoyageGo Action AJAX error:', err);
                    this.toast('Action could not be completed.', 'error');
                })
                .finally(() => {
                    this.setButtonLoading(actionBtn, false);
                    this.finishProgress();
                });
            });
        },

        // 7. Navigation Engine (Fetch & Live DOM Swap)
        navigate: function (url, options = { pushState: true, submitterBtn: null }) {
            if (this.isNavigating && !options.submitterBtn) return;
            this.isNavigating = true;
            this.startProgress();

            const fadeTargets = document.querySelectorAll('#sidebar, .topbar, .main-content, .bottom-nav, .card');
            fadeTargets.forEach(el => el.classList.add('vg-page-fading'));

            fetch(url, {
                cache: 'no-cache',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async (res) => {
                if (!res.ok && res.status >= 500) {
                    throw new Error(`Server returned HTTP ${res.status}`);
                }
                const finalUrl = res.url || url;
                const htmlText = await res.text();
                this.applyHtml(htmlText, finalUrl, options.pushState !== false);
            })
            .catch((err) => {
                console.error('VoyageGo Navigation Error:', err);
                this.toast('Failed to load page. Please try again.', 'error');
            })
            .finally(() => {
                this.isNavigating = false;
                if (options.submitterBtn) {
                    this.setButtonLoading(options.submitterBtn, false);
                }
                this.finishProgress();
                fadeTargets.forEach(el => el.classList.remove('vg-page-fading'));
            });
        },

        // 8. Live DOM Swap & Script Execution
        applyHtml: function (htmlText, url, pushState = true) {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(htmlText, 'text/html');

            // Check if layout changed (e.g. auth vs dashboard)
            const currentHasSidebar = !!document.getElementById('sidebar');
            const newHasSidebar = !!newDoc.getElementById('sidebar');

            if (currentHasSidebar !== newHasSidebar) {
                document.body.className = newDoc.body.className;
                document.body.setAttribute('style', newDoc.body.getAttribute('style') || '');
                document.body.innerHTML = newDoc.body.innerHTML;
                this.executeScripts(document.body);
            } else {
                const newSidebar = newDoc.querySelector('#sidebar');
                const currSidebar = document.querySelector('#sidebar');
                if (newSidebar && currSidebar) {
                    currSidebar.innerHTML = newSidebar.innerHTML;
                    this.executeScripts(currSidebar);
                }

                const newTopbar = newDoc.querySelector('.topbar');
                const currTopbar = document.querySelector('.topbar');
                if (newTopbar && currTopbar) {
                    currTopbar.innerHTML = newTopbar.innerHTML;
                    this.executeScripts(currTopbar);
                }

                const newMain = newDoc.querySelector('.main-content');
                const currMain = document.querySelector('.main-content');
                if (newMain && currMain) {
                    currMain.innerHTML = newMain.innerHTML;
                    this.executeScripts(currMain);
                } else {
                    const newCard = newDoc.querySelector('.card');
                    const currCard = document.querySelector('.card');
                    if (newCard && currCard) {
                        currCard.innerHTML = newCard.innerHTML;
                        this.executeScripts(currCard);
                    }
                }

                const newBottom = newDoc.querySelector('.bottom-nav');
                const currBottom = document.querySelector('.bottom-nav');
                if (newBottom && currBottom) {
                    currBottom.innerHTML = newBottom.innerHTML;
                    this.executeScripts(currBottom);
                }
            }

            // Sync document title and HTML lang attribute
            if (newDoc.title) document.title = newDoc.title;
            if (newDoc.documentElement && newDoc.documentElement.lang) {
                document.documentElement.lang = newDoc.documentElement.lang;
            }

            // Update CSRF token if new page has meta token
            const newCsrf = newDoc.querySelector('meta[name="csrf-token"]');
            if (newCsrf && newCsrf.getAttribute('content')) {
                const curCsrf = document.querySelector('meta[name="csrf-token"]');
                if (curCsrf) curCsrf.setAttribute('content', newCsrf.getAttribute('content'));
            }

            // Sync window.VoyageTranslations if present
            newDoc.querySelectorAll('script').forEach(s => {
                if (s.textContent.includes('window.VoyageTranslations')) {
                    try {
                        const m = s.textContent.match(/window\.VoyageTranslations\s*=\s*(\{[\s\S]*?\});/);
                        if (m && m[1]) {
                            window.VoyageTranslations = JSON.parse(m[1]);
                        }
                    } catch (e) {}
                }
            });

            // Update browser history
            if (pushState && url && url !== window.location.href) {
                window.history.pushState({}, '', url);
            }

            // Scan and toast any flash alerts in the new content
            this.scanAndToastFlashes();

            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Re-initialize core UI systems
            setupAlertAutoDismiss();
            initSidebarState();
            setupLanguageDropdown();

            // Broadcast page load event
            try {
                window.dispatchEvent(new CustomEvent('voyagego:pageLoaded', { detail: { url } }));
            } catch (e) {}
        },

        // Helper: Execute inline scripts in newly swapped DOM containers
        executeScripts: function (container) {
            if (!container) return;
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                if (oldScript.src) {
                    if (document.querySelector(`script[src="${oldScript.src}"]`)) return;
                }
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        },

        // Helper: Extract flash alerts from HTML and display modern toasts
        scanAndToastFlashes: function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.dataset.vgToasted === 'true') return;
                alert.dataset.vgToasted = 'true';
                const text = alert.textContent.replace(/×/g, '').trim();
                if (!text) return;

                let type = 'info';
                if (alert.classList.contains('alert-success')) type = 'success';
                else if (alert.classList.contains('alert-danger') || alert.classList.contains('alert-error')) type = 'error';
                else if (alert.classList.contains('alert-warning')) type = 'warning';

                this.toast(text, type);
            });
        },

        // 9. Browser History PopState
        setupPopState: function () {
            window.addEventListener('popstate', () => {
                this.navigate(window.location.href, { pushState: false });
            });
        }
    };

    window.VoyageAjax = VoyageAjax;

    // =========================================================================
    // Modern Luxury Floating Message System
    // Automatically dismissed after 3 seconds with visual countdown progress bar
    // =========================================================================
    window.vgNotify = function (message, type = 'success', duration = 3000) {
        if (!message) return;

        let container = document.getElementById('vgFloatingContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'vgFloatingContainer';
            container.className = 'vg-floating-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }

        // Prevent duplicate toasts from displaying simultaneously
        const existingCards = container.querySelectorAll('.vg-floating-msg-text');
        for (let i = 0; i < existingCards.length; i++) {
            if (existingCards[i].textContent.trim() === String(message).trim()) {
                return; // Suppress duplicate notification
            }
        }

        const safeType = (type === 'danger' || type === 'error') ? 'error' : (['warning', 'info'].includes(type) ? type : 'success');

        // Status tag badge
        let tag = 'SUCCESS';
        if (safeType === 'error') tag = 'ERROR';
        else if (safeType === 'warning') tag = 'WARNING';
        else if (safeType === 'info') tag = 'NOTICE';

        // Custom crisp SVGs
        let iconSvg = '';
        if (safeType === 'success') {
            iconSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>';
        } else if (safeType === 'warning') {
            iconSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
        } else if (safeType === 'error') {
            iconSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
        } else {
            iconSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }

        const card = document.createElement('div');
        card.className = `vg-floating-msg is-${safeType}`;
        card.setAttribute('role', 'alert');

        card.innerHTML = `
            <div class="vg-floating-msg-header">
                <div class="vg-floating-msg-icon-badge">${iconSvg}</div>
                <div class="vg-floating-msg-title-wrap">
                    <span class="vg-floating-msg-tag">${tag}</span>
                    <div class="vg-floating-msg-text"></div>
                </div>
                <button type="button" class="vg-floating-msg-close" aria-label="Dismiss">&times;</button>
            </div>
            <div class="vg-floating-msg-progress">
                <div class="vg-floating-msg-progress-bar"></div>
            </div>
        `;
        card.querySelector('.vg-floating-msg-text').textContent = message;

        const progressBar = card.querySelector('.vg-floating-msg-progress-bar');
        const closeBtn = card.querySelector('.vg-floating-msg-close');

        let dismissTimer = null;
        let remainingTime = duration;
        let startTime = null;

        const dismiss = () => {
            if (dismissTimer) clearTimeout(dismissTimer);
            card.classList.remove('is-active');
            card.classList.add('is-dismissing');
            setTimeout(() => {
                if (card.parentElement) card.remove();
            }, 250);
        };

        const startTimer = () => {
            startTime = Date.now();
            progressBar.style.transition = `width ${remainingTime}ms linear`;
            progressBar.style.width = '0%';
            dismissTimer = setTimeout(() => {
                dismiss();
            }, remainingTime);
        };

        const pauseTimer = () => {
            if (!dismissTimer) return;
            clearTimeout(dismissTimer);
            dismissTimer = null;
            const elapsed = Date.now() - startTime;
            remainingTime = Math.max(200, remainingTime - elapsed);
            const computedWidth = (remainingTime / duration) * 100;
            progressBar.style.transition = 'none';
            progressBar.style.width = `${computedWidth}%`;
        };

        const resumeTimer = () => {
            if (dismissTimer) return;
            startTimer();
        };

        closeBtn.onclick = (e) => {
            e.stopPropagation();
            dismiss();
        };

        card.addEventListener('mouseenter', pauseTimer);
        card.addEventListener('mouseleave', resumeTimer);

        container.appendChild(card);

        // Animate in and start countdown
        requestAnimationFrame(() => {
            card.classList.add('is-active');
            startTimer();
        });
    };

    window.vgToast = function (msg, type, dur = 3000) { window.vgNotify(msg, type, dur); };

    // =========================================================================
    // Universal Form Validation Engine
    // Professional red border on required inputs without alert boxes
    // Live removal of red border as soon as user enters/selects value
    // =========================================================================
    const VoyageValidation = {
        markError: function(input) {
            if (!input) return;
            input.classList.add('is-invalid', 'vg-input-error');
            input.setAttribute('aria-invalid', 'true');
            input.style.borderColor = '#EF4444';
            input.style.boxShadow = '0 0 0 3.5px rgba(239, 68, 68, 0.16)';
            
            // Subtle tactile shake animation
            input.classList.remove('vg-input-shake');
            void input.offsetWidth; // trigger reflow
            input.classList.add('vg-input-shake');
            setTimeout(() => input.classList.remove('vg-input-shake'), 350);

            this.bindClearListener(input);
        },

        clearError: function(input) {
            if (!input) return;
            input.classList.remove('is-invalid', 'vg-input-error', 'vg-input-shake');
            input.removeAttribute('aria-invalid');
            input.style.borderColor = '';
            input.style.boxShadow = '';
        },

        isInputFilled: function(input) {
            if (!input) return false;
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.name) {
                    try {
                        const group = document.querySelectorAll(`input[name="${CSS.escape(input.name)}"]`);
                        return Array.from(group).some(r => r.checked);
                    } catch (e) {
                        return input.checked;
                    }
                }
                return input.checked;
            }
            if (input.tagName === 'SELECT') {
                return input.value !== '' && input.value !== null;
            }
            return input.value.trim().length > 0;
        },

        bindClearListener: function(input) {
            if (input._hasClearValidationListener) return;
            input._hasClearValidationListener = true;

            const handleInput = () => {
                if (this.isInputFilled(input)) {
                    this.clearError(input);
                }
            };

            input.addEventListener('input', handleInput);
            input.addEventListener('change', handleInput);
            input.addEventListener('keyup', handleInput);
            input.addEventListener('paste', () => setTimeout(handleInput, 10));
        },

        validateContainer: function(container) {
            if (!container) return true;
            const requiredFields = container.querySelectorAll('input[required], select[required], textarea[required], [data-required="true"]');
            let isValid = true;
            let firstInvalid = null;

            requiredFields.forEach(field => {
                // Ignore disabled fields or hidden elements
                if (field.disabled || field.type === 'hidden') return;
                if (field.offsetParent === null && field.type !== 'file') return;

                if (!this.isInputFilled(field)) {
                    isValid = false;
                    this.markError(field);
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                } else {
                    this.clearError(field);
                }
            });

            if (!isValid && firstInvalid) {
                firstInvalid.focus();
                return false;
            }

            return true;
        },

        validateForm: function(form) {
            if (!form) return true;
            return this.validateContainer(form);
        },

        init: function() {
            // Apply novalidate to all forms to suppress browser popup alerts
            document.querySelectorAll('form').forEach(form => {
                form.setAttribute('novalidate', 'true');
            });

            // Delegate live input clearing: as soon as user types or changes an invalid input, clear red border
            document.addEventListener('input', (e) => {
                const target = e.target;
                if (target && (target.classList.contains('is-invalid') || target.classList.contains('vg-input-error') || target.style.borderColor === 'rgb(239, 68, 68)' || target.style.borderColor === '#EF4444')) {
                    if (this.isInputFilled(target)) {
                        this.clearError(target);
                    }
                }
            }, true);

            document.addEventListener('change', (e) => {
                const target = e.target;
                if (target && (target.classList.contains('is-invalid') || target.classList.contains('vg-input-error') || target.style.borderColor === 'rgb(239, 68, 68)' || target.style.borderColor === '#EF4444')) {
                    if (this.isInputFilled(target)) {
                        this.clearError(target);
                    }
                }
            }, true);

            // Intercept submit on all forms globally (in capturing phase)
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                if (form.hasAttribute('data-no-validate') || form.dataset.noValidate === 'true') return;
                
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
        }
    };

    window.VoyageValidation = VoyageValidation;

    function initVoyageGoApp() {
        setupNetworkListener();
        setupAlertAutoDismiss();
        initSidebarState();
        setupLanguageDropdown();
        VoyageValidation.init();
        VoyageAjax.init();
    }

    // Initialize on DOM Ready or immediately if DOM is already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setupLanguageDropdown();
            initVoyageGoApp();
        });
    } else {
        setupLanguageDropdown();
        initVoyageGoApp();
    }
})();


