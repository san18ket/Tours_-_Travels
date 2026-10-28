<!DOCTYPE html>
<html lang="<?= e(\App\Core\Language::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <meta name="base-url" content="<?= url('/') ?>">
    <meta name="sw-url" content="<?= url('/sw.js') ?>">
    <meta name="gps-update-url" content="<?= url('/api/gps/update') ?>">
    <title><?= e($title ?? 'Chauffeur Console') ?> - <?= e($app['name'] ?? 'VoyageGo') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= @filemtime(dirname(__DIR__, 2) . '/public/assets/css/app.css') ?: '2.2' ?>">
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="theme-color" content="#0B192C">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
        window.VoyageTranslations = <?= json_encode(\App\Core\Language::getJsTranslations(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
</head>
<body>
    <div id="offlineBanner" class="offline-banner" style="display: none;" role="alert" aria-live="polite">
        <div class="offline-banner-content">
            <span class="offline-banner-icon" aria-hidden="true">⚠️</span>
            <span id="offlineBannerText"><?= e(trim(str_replace(['⚠️', '⚠'], '', __('js.offline_banner')))) ?></span>
        </div>
        <button type="button" class="offline-banner-close" onclick="document.getElementById('offlineBanner').style.display='none'" aria-label="Dismiss">&times;</button>
    </div>

    <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="closeSidebar()" aria-hidden="true"></div>

    <div class="app-wrapper">
        <!-- Luxury Dedicated Driver Navigation Sidebar -->
        <aside class="sidebar" id="sidebar" aria-label="<?= e(__('sidebar.menu')) ?>">
            <div class="sidebar-header">
                <a href="<?= url('/driver/dashboard') ?>" class="brand-logo" title="MANASWI Tours & Travels - Chauffeur Console">
                    <img src="<?= asset('images/manaswi-logo.svg') ?>" alt="Manaswi Logo" class="brand-logo-img">
                    <div class="brand-name">
                        <span class="brand-title">MANASWI</span>
                        <span class="brand-subtitle-main"><?= e(__('app.tours_travels', 'Tours & Travels')) ?></span>
                        <span class="brand-tagline"><?= e(__('app.safe_journey_tagline', 'Safe Journey • Happy Memories')) ?></span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="<?= e(__('sidebar.close_navigation', 'Close Navigation')) ?>">&times;</button>
            </div>

            <nav class="sidebar-nav">
                <?php
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    $isDashboardActive = str_contains($uri, '/driver/dashboard') || $uri === '/driver' || $uri === '/driver/';
                    $isTripsActive     = str_contains($uri, '/driver/trips');
                    $isHistoryActive   = str_contains($uri, '/driver/history');
                    $isVehicleActive   = str_contains($uri, '/driver/vehicle');
                    $isLocationActive  = str_contains($uri, '/driver/location');
                    $isProfileActive   = str_contains($uri, '/driver/profile');
                ?>

                <!-- 1. Dashboard -->
                <div class="sidebar-category" id="cat-driver-dashboard">
                    <a href="<?= url('/driver/dashboard') ?>" class="category-header direct-link <?= $isDashboardActive ? 'has-active' : '' ?>" id="cat-driver-dashboard-header" data-title="Dashboard" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.dashboard')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isDashboardActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.dashboard')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 2. My Assigned Trips -->
                <div class="sidebar-category" id="cat-driver-trips">
                    <a href="<?= url('/driver/trips') ?>" class="category-header direct-link <?= $isTripsActive ? 'has-active' : '' ?>" id="cat-driver-trips-header" data-title="Assigned Trips" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.assigned_trips')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTripsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.assigned_trips')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 3. Trip History -->
                <div class="sidebar-category" id="cat-driver-history">
                    <a href="<?= url('/driver/history') ?>" class="category-header direct-link <?= $isHistoryActive ? 'has-active' : '' ?>" id="cat-driver-history-header" data-title="Trip History" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.trip_history')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isHistoryActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.trip_history')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 4. My Vehicle -->
                <div class="sidebar-category" id="cat-driver-vehicle">
                    <a href="<?= url('/driver/vehicle') ?>" class="category-header direct-link <?= $isVehicleActive ? 'has-active' : '' ?>" id="cat-driver-vehicle-header" data-title="My Vehicle" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.my_vehicle')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isVehicleActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.my_vehicle')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 5. Live Location / Location Sharing -->
                <div class="sidebar-category" id="cat-driver-location">
                    <a href="<?= url('/driver/location') ?>" class="category-header direct-link <?= $isLocationActive ? 'has-active' : '' ?>" id="cat-driver-location-header" data-title="Live Location" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.live_location')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <span class="badge-dot" style="background:#10B981;" title="GPS Enabled"></span>
                            <?php if ($isLocationActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.live_location')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 6. My Profile -->
                <div class="sidebar-category" id="cat-driver-profile">
                    <a href="<?= url('/driver/profile') ?>" class="category-header direct-link <?= $isProfileActive ? 'has-active' : '' ?>" id="cat-driver-profile-header" data-title="My Profile" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.my_profile')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isProfileActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.my_profile')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="<?= url('/driver/profile') ?>" class="user-badge" style="text-decoration:none;">
                    <div class="user-avatar" style="background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); color:#FFF;">
                        <?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= e($user['name'] ?? 'Chauffeur') ?></div>
                        <div class="user-role" style="color: var(--vg-gold-400); font-weight: 600;"><?= e(__('role.licensed_chauffeur')) ?></div>
                    </div>
                </a>
                <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" title="<?= e(__('sidebar.logout')) ?>" aria-label="<?= e(__('sidebar.logout')) ?>" style="color:#FFF;border-color:rgba(255,255,255,0.2);min-height:36px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Mobile Top Navbar (Matches mobile_dashbord.jpeg in LG Screen Forest Teal theme - across all pages) -->
            <?php require dirname(__DIR__) . '/components/mobile_topbar.php'; ?>

            <!-- Frosted Glass Topbar (Exact UI like Owner Dashboard) -->
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebarCollapse()" aria-label="<?= e(__('btn.toggle_sidebar', 'Toggle Navigation')) ?>" title="<?= e(__('btn.toggle_sidebar', 'Toggle Navigation')) ?>">
                        <svg class="toggle-icon-sidebar" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="toggle-arrow" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <svg class="toggle-icon-mobile" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <!-- Omnisearch Bar (Matches Reference Image & Owner Dashboard) -->
                    <form action="<?= url('/driver/trips') ?>" method="GET" class="topbar-search-form">
                        <svg class="topbar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="q" class="topbar-search-input" placeholder="<?= e(__('topbar.search_driver_placeholder', 'Search trips, routes, or anything...')) ?>" autocomplete="off">
                    </form>
                </div>

                <div class="topbar-actions">
                    <!-- Change Language Icon Dropdown (Professional Multi-Language Switcher) -->
                    <?php require dirname(__DIR__) . '/components/language_switcher.php'; ?>

                    <!-- Fullscreen Toggle Button -->
                    <button type="button" class="topbar-icon-btn" title="<?= e(__('topbar.fullscreen', 'Toggle Fullscreen')) ?>" aria-label="<?= e(__('topbar.fullscreen', 'Toggle Fullscreen')) ?>" onclick="toggleAppFullscreen()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </button>

                    <!-- Notifications Button with Badge 3 (Matches both Reference Image and Owner UI) -->
                    <a href="<?= url('/driver/dashboard') ?>#notifications" class="topbar-icon-btn" title="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-label="<?= e(__('nav.notifications', 'Notifications')) ?>" style="position: relative; text-decoration: none;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="nav-badge-pill" style="position: absolute; top: -4px; right: -4px; background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 0 2px #fff;">3</span>
                    </a>

                    <!-- Driver Profile Chip with Dropdown -->
                    <div class="topbar-user-profile" id="topbarUserProfile" onclick="toggleUserDropdown(event)" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="<?= e(__('topbar.view_profile', 'Driver Profile Menu')) ?>">
                        <div class="topbar-avatar-wrap">
                            <img src="<?= asset('images/admin_avatar.jpg') ?>" class="topbar-avatar-img" alt="<?= e($user['name'] ?? 'Driver') ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?></div>
                        </div>
                        <div class="topbar-user-meta">
                            <span class="topbar-user-name"><?= e($user['name'] ?? 'Driver') ?></span>
                            <span class="topbar-user-role"><?= e(__('role.driver', 'Driver')) ?></span>
                        </div>
                        <svg class="topbar-user-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"></path>
                        </svg>

                        <!-- Driver Profile Dropdown Menu -->
                        <div class="topbar-user-menu" id="topbarUserMenu" role="menu" aria-labelledby="topbarUserProfile" onclick="event.stopPropagation()">
                            <div class="topbar-menu-user-head">
                                <div class="topbar-menu-head-avatar">
                                    <img src="<?= asset('images/admin_avatar.jpg') ?>" alt="<?= e($user['name'] ?? 'Driver') ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?></div>
                                </div>
                                <div class="topbar-menu-head-meta">
                                    <strong class="topbar-menu-name"><?= e($user['name'] ?? 'Driver') ?></strong>
                                    <span class="topbar-menu-role-pill"><?= e(__('role.driver', 'Driver')) ?></span>
                                    <span class="topbar-menu-email"><?= e($user['phone'] ?? $user['email'] ?? $user['username'] ?? 'driver@manaswi.com') ?></span>
                                </div>
                            </div>
                            <div class="topbar-menu-divider"></div>
                            <a href="<?= url('/driver/profile') ?>" class="topbar-menu-item" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                                    <i class="fa-solid fa-user-circle"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title"><?= e(__('topbar.view_profile', 'View Profile')) ?></span>
                                    <span class="topbar-menu-sub"><?= e(__('topbar.driver_profile_sub', 'Chauffeur details & vehicle')) ?></span>
                                </div>
                                <i class="fa-solid fa-chevron-right topbar-menu-arrow"></i>
                            </a>
                            <div class="topbar-menu-divider"></div>
                            <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0; width:100%;" onsubmit="var b = this.querySelector('button'); if(b){ b.disabled = true; b.style.opacity = '0.7'; var t = b.querySelector('.topbar-menu-title'); if(t) t.textContent = '<?= e(__('topbar.sign_out_desc', 'Signing out...')) ?>'; }">
                                <?= csrf_field() ?>
                                <button type="submit" class="topbar-menu-item topbar-logout-item" role="menuitem">
                                    <div class="topbar-menu-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    </div>
                                    <div class="topbar-menu-text">
                                        <span class="topbar-menu-title" style="color: #ef4444;"><?= e(__('sidebar.logout', 'Logout')) ?></span>
                                        <span class="topbar-menu-sub"><?= e(__('topbar.sign_out_driver_desc', 'End driving shift session')) ?></span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="main-content">
                <!-- Modern Floating Notifications Emitter -->
                <?php $activeFlashes = !empty($flashes) ? $flashes : \App\Core\Session::getAllFlashes(); ?>
                <?php if (!empty($activeFlashes)): ?>
                <script>
                    (function() {
                        var f = <?= json_encode($activeFlashes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                        var emit = function() {
                            if (typeof window.vgNotify === 'function') {
                                for (var k in f) {
                                    if (f.hasOwnProperty(k)) window.vgNotify(f[k], k);
                                }
                            } else {
                                setTimeout(emit, 40);
                            }
                        };
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', emit);
                        } else {
                            emit();
                        }
                    })();
                </script>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <!-- Branded Clean Light Footer -->
            <footer class="app-footer">
                <div>
                    &copy; <?= date('Y') ?> <strong>MANASWI <?= e(__('app.tours_travels', 'Tours & Travels')) ?></strong>. <?= e(__('footer.all_rights_reserved', 'All rights reserved.')) ?>
                </div>
                <div>
                    <a href="#privacy"><?= e(__('footer.privacy', 'Privacy Policy')) ?></a>
                    <a href="#terms"><?= e(__('footer.terms', 'Terms & Conditions')) ?></a>
                    <a href="#help"><?= e(__('footer.help', 'Help & Support')) ?></a>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Adaptive Bottom Navigation (Curved Notch Design matching Owner Dashboard) -->
    <nav class="bottom-nav notched-bottom-nav" aria-label="<?= e(__('sidebar.driver_mobile_menu', 'Driver Mobile Navigation')) ?>">
        <div class="notched-nav-bg" aria-hidden="true">
            <svg viewBox="0 0 375 64" preserveAspectRatio="none">
                <path d="M 0,0 L 142,0 C 153,0 159,8 165,19 C 172,32 179,35 187.5,35 C 196,35 203,32 210,19 C 216,8 222,0 233,0 L 375,0 L 375,64 L 0,64 Z" fill="#ffffff" />
            </svg>
        </div>

        <!-- Center Elevated Notch FAB Button (Positioned at exact 50% mathematical center) -->
        <button type="button" class="bottom-nav-fab-btn" onclick="toggleSidebar()" aria-label="<?= e(__('sidebar.menu', 'Open Navigation Menu')) ?>" title="<?= e(__('sidebar.menu', 'Open Navigation Menu')) ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="bottom-nav-inner">
            <a href="<?= url('/driver/dashboard') ?>" class="bottom-nav-item <?= $isDashboardActive ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span><?= e(__('sidebar.dashboard', 'Home')) ?></span>
            </a>
            <a href="<?= url('/driver/trips') ?>" class="bottom-nav-item <?= $isTripsActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span><?= e(__('sidebar.trips', 'Trips')) ?></span>
            </a>

            <!-- Center slot spacer (Ensures left 2 items and right 2 items are 100% symmetric) -->
            <div class="bottom-nav-center-slot" aria-hidden="true"></div>

            <a href="<?= url('/driver/location') ?>" class="bottom-nav-item <?= $isLocationActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span><?= e(__('sidebar.live_location', 'Tracking')) ?></span>
            </a>
            <a href="<?= url('/driver/dashboard') ?>#notifications" class="bottom-nav-item nav-notif-item">
                <div style="position: relative; display: inline-flex;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="nav-badge-pill">3</span>
                </div>
                <span><?= e(__('nav.notifications', 'Notifications')) ?></span>
            </a>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>?v=3.0"></script>
    <script>
        function toggleUserDropdown(e) {
            if (e) {
                e.stopPropagation();
            }
            var chip = document.getElementById('topbarUserProfile');
            if (!chip) return;
            var wasOpen = chip.classList.contains('is-open');

            // Close language dropdown if open
            var langDrop = document.getElementById('vgLanguageDropdown');
            if (langDrop) {
                langDrop.classList.remove('is-open');
                var langBtn = langDrop.querySelector('.vg-lang-btn');
                if (langBtn) langBtn.setAttribute('aria-expanded', 'false');
            }

            if (wasOpen) {
                chip.classList.remove('is-open');
                chip.setAttribute('aria-expanded', 'false');
            } else {
                chip.classList.add('is-open');
                chip.setAttribute('aria-expanded', 'true');
            }
        }

        document.addEventListener('click', function(e) {
            var chip = document.getElementById('topbarUserProfile');
            if (chip && !chip.contains(e.target)) {
                chip.classList.remove('is-open');
                chip.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var chip = document.getElementById('topbarUserProfile');
                if (chip) {
                    chip.classList.remove('is-open');
                    chip.setAttribute('aria-expanded', 'false');
                }
                var langDrop = document.getElementById('vgLanguageDropdown');
                if (langDrop) {
                    langDrop.classList.remove('is-open');
                    var langBtn = langDrop.querySelector('.vg-lang-btn');
                    if (langBtn) langBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });

        function toggleAppFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function(err) {});
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        // Mobile Topbar Dropdown Toggles (Active Across All Pages)
        function toggleMvLang(e) {
            if (e) e.stopPropagation();
            var pDrop = document.getElementById('mvProfileDropdown');
            var lDrop = document.getElementById('mvLangDropdown');
            if (pDrop) pDrop.classList.remove('is-open');
            if (lDrop) lDrop.classList.toggle('is-open');
        }

        function toggleMvProfile(e) {
            if (e) e.stopPropagation();
            var lDrop = document.getElementById('mvLangDropdown');
            var pDrop = document.getElementById('mvProfileDropdown');
            if (lDrop) lDrop.classList.remove('is-open');
            if (pDrop) pDrop.classList.toggle('is-open');
        }

        document.addEventListener('click', function(e) {
            var lDrop = document.getElementById('mvLangDropdown');
            var pDrop = document.getElementById('mvProfileDropdown');
            if (lDrop && !lDrop.contains(e.target)) lDrop.classList.remove('is-open');
            if (pDrop && !pDrop.contains(e.target)) pDrop.classList.remove('is-open');
        });
    </script>
</body>
</html>
