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
                <a href="<?= url('/driver/dashboard') ?>" class="brand-logo" title="VoyageGo Chauffeur Console">
                    <div class="brand-icon">VG</div>
                    <div class="brand-name">
                        <span class="brand-title">VoyageGo</span>
                        <span class="brand-subtitle" style="color: var(--vg-gold-400); font-weight: 700; letter-spacing: 0.05em; font-size: 0.7rem;"><?= e(__('driver.portal_subtitle')) ?></span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="<?= e(__('sidebar.close_navigation')) ?>">&times;</button>
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
            <!-- Dedicated Driver Navbar -->
            <header class="topbar">
                <div class="topbar-left" style="display:flex; align-items:center; gap:0.75rem;">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebarCollapse()" aria-label="<?= e(__('btn.toggle_sidebar')) ?>" title="<?= e(__('btn.toggle_sidebar')) ?>">
                        <svg class="toggle-icon-sidebar" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="toggle-arrow" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <svg class="toggle-icon-mobile" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="topbar-title" style="font-weight:700; font-size:1.15rem; color: var(--vg-navy-950);">
                        <?= e($title ?? __('driver.console_title')) ?>
                    </div>
                </div>

                <div class="topbar-actions" style="display:flex; align-items:center; gap:0.75rem;">
                    <!-- Language Selector Dropdown -->
                    <?php require dirname(__DIR__) . '/components/language_switcher.php'; ?>

                    <!-- Duty Status Indicator & Quick Toggle -->
                    <?php
                        $dutyStatus = $driver['duty_status'] ?? 'off_duty';
                        $dutyBadgeClass = match($dutyStatus) {
                            'on_duty'   => 'badge-success',
                            'on_trip'   => 'badge-primary',
                            'off_duty'  => 'badge-warning',
                            default     => 'badge-secondary'
                        };
                        $dutyLabel = match($dutyStatus) {
                            'on_duty'   => __('status.on_duty'),
                            'on_trip'   => __('status.on_trip'),
                            'off_duty'  => __('status.off_duty'),
                            default     => ucfirst($dutyStatus)
                        };
                    ?>
                    <span class="badge <?= $dutyBadgeClass ?>" style="padding: 0.4rem 0.75rem; font-size:0.8rem; display:inline-flex; align-items:center; gap:0.4rem;">
                        <span class="pulse-indicator" style="<?= $dutyStatus === 'off_duty' ? 'animation:none; background:#94A3B8;' : '' ?>"></span>
                        <?= e($dutyLabel) ?>
                    </span>

                    <?php if ($dutyStatus !== 'on_trip'): ?>
                    <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin:0; display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm <?= $dutyStatus === 'on_duty' ? 'btn-outline' : 'btn-success' ?>" style="font-size:0.75rem; padding:0.35rem 0.65rem;" title="<?= e(__('btn.go_on_duty')) ?>">
                            <?= e($dutyStatus === 'on_duty' ? __('btn.go_off_duty') : __('btn.go_on_duty')) ?>
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Telemetry Live Pulse Indicator -->
                    <span id="gpsRadarIndicator" class="badge badge-info" style="display:none; font-size:0.75rem;">
                        <i class="fa-solid fa-satellite-dish fa-spin" style="margin-right:4px;"></i> <?= e(__('status.gps_live')) ?>
                    </span>

                    <!-- Driver Profile Chip with Dropdown -->
                    <div class="topbar-user-profile" id="topbarUserProfile" onclick="toggleUserDropdown(event)" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="Driver Profile Menu">
                        <div class="topbar-avatar" style="background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); color:#FFF;">
                            <?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?>
                        </div>
                        <div class="topbar-user-meta">
                            <span class="topbar-user-name"><?= e(explode(' ', $user['name'] ?? 'Driver')[0]) ?></span>
                            <span class="topbar-user-role"><?= e(__('role.licensed_chauffeur')) ?></span>
                        </div>
                        <svg class="topbar-user-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"></path>
                        </svg>

                        <!-- Driver Profile Dropdown Menu -->
                        <div class="topbar-user-menu" id="topbarUserMenu" role="menu" aria-labelledby="topbarUserProfile" onclick="event.stopPropagation()">
                            <div class="topbar-menu-user-head">
                                <div class="topbar-avatar" style="width:42px; height:42px; font-size:1.1rem; background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); color:#FFF;">
                                    <?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?>
                                </div>
                                <div class="topbar-menu-head-meta">
                                    <strong class="topbar-menu-name"><?= e($user['name'] ?? 'Driver') ?></strong>
                                    <span class="topbar-menu-role-pill"><?= e(__('role.licensed_chauffeur')) ?></span>
                                    <span class="topbar-menu-email"><?= e($user['phone'] ?? $user['username'] ?? 'Chauffeur Console') ?></span>
                                </div>
                            </div>
                            <div class="topbar-menu-divider"></div>
                            <a href="<?= url('/driver/profile') ?>" class="topbar-menu-item" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                                    <i class="fa-solid fa-user-circle"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title"><?= e(__('sidebar.my_profile')) ?></span>
                                    <span class="topbar-menu-sub">Chauffeur details &amp; vehicle</span>
                                </div>
                                <i class="fa-solid fa-chevron-right topbar-menu-arrow"></i>
                            </a>
                            <div class="topbar-menu-divider"></div>
                            <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0; width:100%;" onsubmit="var b = this.querySelector('button'); if(b){ b.disabled = true; b.style.opacity = '0.7'; var t = b.querySelector('.topbar-menu-title'); if(t) t.textContent = 'Signing out...'; }">
                                <?= csrf_field() ?>
                                <button type="submit" class="topbar-menu-item topbar-logout-item" role="menuitem">
                                    <div class="topbar-menu-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    </div>
                                    <div class="topbar-menu-text">
                                        <span class="topbar-menu-title" style="color: #ef4444;"><?= e(__('sidebar.logout')) ?></span>
                                        <span class="topbar-menu-sub">End driving shift session</span>
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
        </div>
    </div>

    <!-- Mobile Adaptive Bottom Navigation (Chauffeur Quick Dock) -->
    <nav class="bottom-nav" aria-label="Driver Mobile Navigation">
        <div class="bottom-nav-inner">
            <a href="<?= url('/driver/dashboard') ?>" class="bottom-nav-item <?= $isDashboardActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span><?= e(__('sidebar.dashboard')) ?></span>
            </a>
            <a href="<?= url('/driver/trips') ?>" class="bottom-nav-item <?= $isTripsActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span><?= e(__('nav.my_trips')) ?></span>
            </a>
            <a href="<?= url('/driver/vehicle') ?>" class="bottom-nav-item <?= $isVehicleActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span><?= e(__('sidebar.my_vehicle')) ?></span>
            </a>
            <a href="<?= url('/driver/location') ?>" class="bottom-nav-item <?= $isLocationActive ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span><?= e(__('sidebar.live_location')) ?></span>
            </a>
            <button type="button" class="bottom-nav-item" onclick="toggleSidebar()" style="background:none;border:none;cursor:pointer;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span><?= e(__('sidebar.menu')) ?></span>
            </button>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>?v=2.2"></script>
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
    </script>
</body>
</html>
