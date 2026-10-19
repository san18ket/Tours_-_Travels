/**
 * VoyageGo Tours & Travels - Core Client JavaScript
 * Vanilla JS with PWA integration, Telemetry, and Offline handling
 */

(function () {
    'use strict';

    // Global CSRF Token Retrieval
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Network status listener
    function setupNetworkListener() {
        const offlineBanner = document.getElementById('offlineBanner');
        if (!offlineBanner) return;

        function updateOnlineStatus() {
            if (navigator.onLine) {
                offlineBanner.style.display = 'none';
            } else {
                offlineBanner.style.display = 'block';
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
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

    // Driver Live GPS Telemetry Engine
    window.VoyageTelemetry = {
        watchId: null,
        isTracking: false,
        updateUrl: '/api/gps/update',

        start: function (updateUrl) {
            if (this.isTracking) return;
            if (updateUrl) this.updateUrl = updateUrl;

            const radarIndicator = document.getElementById('gpsRadarIndicator');
            const statusText = document.getElementById('gpsStatusText');

            if (!navigator.geolocation) {
                if (statusText) statusText.innerText = 'GPS not supported on device';
                return;
            }

            this.isTracking = true;
            if (radarIndicator) radarIndicator.style.display = 'inline-block';
            if (statusText) statusText.innerText = 'Telemetry Active (Live Sync)';

            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.sendLocation(position.coords);
                },
                (error) => {
                    console.warn('GPS Warning:', error.message);
                    if (statusText) statusText.innerText = 'Acquiring GPS Signal...';
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 5000,
                    timeout: 10000
                }
            );
        },

        sendLocation: function (coords) {
            const token = getCsrfToken();
            const payload = {
                latitude: coords.latitude,
                longitude: coords.longitude,
                speed_kmh: coords.speed ? (coords.speed * 3.6).toFixed(1) : 0.0,
                heading_degrees: coords.heading || null,
            };

            fetch(this.updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                const lastSync = document.getElementById('lastGpsSyncTime');
                if (lastSync) {
                    const now = new Date();
                    lastSync.innerText = now.toLocaleTimeString();
                }
            })
            .catch(err => {
                console.warn('Telemetry transmission delayed:', err);
            });
        },

        stop: function () {
            if (this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }
            this.isTracking = false;
            const radarIndicator = document.getElementById('gpsRadarIndicator');
            const statusText = document.getElementById('gpsStatusText');
            if (radarIndicator) radarIndicator.style.display = 'none';
            if (statusText) statusText.innerText = 'Standby (Trip Not Started)';
        }
    };

    // PWA Service Worker Registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('ServiceWorker registered:', reg.scope))
                .catch(err => console.log('ServiceWorker registration skipped:', err));
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

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        setupNetworkListener();
        setupAlertAutoDismiss();
        initSidebarState();
    });
})();


