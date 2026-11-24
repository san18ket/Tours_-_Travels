<!DOCTYPE html>
<html lang="<?= e(\App\Core\Language::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <meta name="base-url" content="<?= url('/') ?>">
    <meta name="user-id" content="<?= auth_id() ?: 'guest' ?>">
    <meta name="sw-url" content="<?= url('/sw.js') ?>">
    <meta name="gps-update-url" content="<?= url('/api/gps/update') ?>">
    <title><?= e($title ?? 'Driver Console') ?> - <?= e(company_name()) ?></title>
    <?php
        $cssVer = @filemtime(dirname(__DIR__, 3) . '/public/assets/css/app.css') ?: time();
        $pwaCssVer = @filemtime(dirname(__DIR__, 3) . '/public/assets/css/tours-pwa.css') ?: time();
    ?>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= asset('css/tours-pwa.css') ?>?v=<?= $pwaCssVer ?>">
    <!-- Favicon & PWA Branding Metadata -->
    <link rel="icon" type="image/png" href="<?= e(pwa_icon_url()) ?>">
    <link rel="apple-touch-icon" href="<?= e(pwa_icon_url()) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= e(pwa_app_name()) ?>">
    <meta name="application-name" content="<?= e(pwa_app_name()) ?>">
    <meta name="app-version" content="<?= e(app_version()) ?>">
    <meta name="theme-color" content="<?= e(company_primary_color()) ?>">
    <link rel="manifest" href="<?= url('/manifest.json') ?>?v=<?= pwa_version() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        window.VoyageTranslations = <?= json_encode(\App\Core\Language::getJsTranslations(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
    <style>
    /* ==========================================================================
       NOTIFICATION DROPDOWN UI (Pixel-Perfect Match to Reference Image)
       ========================================================================== */
    .topbar-notif-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .notif-bell-btn {
        position: relative;
        cursor: pointer;
        background: transparent;
        border: none;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .topbar-notif-box {
        position: absolute;
        top: calc(100% + 12px);
        right: -10px;
        width: 360px;
        max-width: calc(100vw - 24px);
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.04);
        display: none;
        flex-direction: column;
        z-index: 10005;
        animation: vgFadeDown 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
        cursor: default;
        overflow: hidden;
    }

    .topbar-notif-wrap.is-open .topbar-notif-box {
        display: flex;
    }

    /* Dropdown Header */
    .notif-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.95rem 1.15rem 0.85rem;
        border-bottom: 1px solid #F1F5F9;
        background: #FFFFFF;
    }

    .notif-box-head-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notif-box-head-icon {
        color: #0F172A;
    }

    .notif-box-head-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .notif-box-head-badge {
        background: #EF4444;
        color: #FFFFFF;
        font-size: 0.68rem;
        font-weight: 800;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        line-height: 1;
    }

    /* Dropdown Body */
    .notif-box-body {
        max-height: 380px;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0.35rem 0 0.5rem;
    }

    /* Section Headers (Matches Reference EXPIRED and EXPIRING SOON) */
    .notif-box-section-title {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.75px;
        text-transform: uppercase;
        padding: 0.65rem 1.15rem 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .notif-box-section-title.notif-sec-red {
        color: #EF4444;
    }

    .notif-box-section-title.notif-sec-amber {
        color: #D97706;
    }

    /* Notification Items */
    .notif-box-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.15rem;
        border-bottom: 1px solid #F8FAFC;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .notif-box-item:hover {
        background: #F1F5F9;
    }

    .notif-box-item.is-unread {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.15) 0%, rgba(23, 107, 91, 0.04) 100%);
    }

    .notif-box-item.is-unread:hover {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.25) 0%, rgba(23, 107, 91, 0.08) 100%);
    }

    .notif-box-item-head {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .notif-item-unread-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #EF4444;
        flex-shrink: 0;
    }

    .notif-box-item-arrow {
        margin-left: auto;
        color: #94A3B8;
        font-size: 0.75rem;
        transition: transform 0.18s ease, color 0.18s ease;
        flex-shrink: 0;
    }

    .notif-box-item:hover .notif-box-item-arrow {
        color: #176B5B;
        transform: translateX(3px);
    }

    .notif-box-item:last-child {
        border-bottom: none;
    }

    /* Round Left Icons (Matches Ref Globe Icons in Red and Orange) */
    .notif-box-item-icon {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .notif-box-icon-red {
        background: #FEE2E2;
        color: #EF4444;
    }

    .notif-box-icon-amber {
        background: #FEF3C7;
        color: #D97706;
    }

    /* Info Meta */
    .notif-box-item-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
        line-height: 1.25;
    }

    .notif-box-item-name {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1E293B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }

    .notif-box-item-sub {
        font-size: 0.7rem;
        color: #64748B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
        margin-top: 2px;
    }

    /* Empty State */
    .notif-box-empty {
        padding: 2.25rem 1rem;
        text-align: center;
    }

    .notif-box-empty-icon {
        font-size: 2rem;
        color: #CBD5E1;
        margin-bottom: 0.4rem;
    }

    .notif-box-empty-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #334155;
    }

    .notif-box-empty-sub {
        font-size: 0.72rem;
        color: #94A3B8;
        margin-top: 2px;
    }

    /* Dropdown Footer (Matches Reference View All Domains ->) */
    .notif-box-footer {
        padding: 0.8rem 1.15rem;
        text-align: center;
        border-top: 1px solid #F1F5F9;
        background: #FAFAFA;
    }

    .notif-box-footer-link {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748B;
        text-decoration: none;
        transition: color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .notif-box-footer-link:hover {
        color: #0F172A;
        text-decoration: underline;
    }
    </style>
</head>
<body>
    <div id="offlineBanner" class="offline-banner" style="display: none;" role="alert" aria-live="polite">
        <div class="offline-banner-content">
            <span class="offline-banner-icon" aria-hidden="true">⚠️</span>
            <span id="offlineBannerText"><?= e(trim(str_replace(['⚠️', '⚠'], '', __('js.offline_banner')))) ?></span>
        </div>
        <button type="button" class="offline-banner-close" onclick="document.getElementById('offlineBanner').style.display='none'" aria-label="<?= e(__('common.dismiss', 'Dismiss')) ?>">&times;</button>
    </div>

    <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="closeSidebar()" aria-hidden="true"></div>

    <div class="app-wrapper">
        <!-- Luxury Dedicated Driver Navigation Sidebar -->
        <aside class="sidebar" id="sidebar" aria-label="<?= e(__('sidebar.menu')) ?>">
            <div class="sidebar-header">
                <a href="<?= url('/driver/dashboard') ?>" class="brand-logo" data-no-ajax="true" title="<?= e(company_display_name()) ?>">
                    <?php if (has_company_logo()): ?>
                        <img src="<?= e(company_logo_url()) ?>" alt="<?= e(company_display_name()) ?>" class="brand-logo-img" style="object-fit: contain;">
                    <?php else: ?>
                        <div class="brand-logo-icon-badge">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="brand-name">
                        <span class="brand-title"><?= e(company_display_name()) ?></span>
                        <span class="brand-portal-label" style="display:block; font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--vg-gold-400, #D6B77A); margin-top: 1px;">DRIVER PORTAL</span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="<?= e(__('sidebar.close_navigation', 'Close Navigation')) ?>">&times;</button>
            </div>

            <nav class="sidebar-nav">
                <?php
                    $reqUri = $_SERVER['REQUEST_URI'] ?? '/';
                    $path = parse_url($reqUri, PHP_URL_PATH) ?? '/';
                    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                    $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
                    if ($baseDir !== '' && str_starts_with($path, $baseDir)) {
                        $path = substr($path, strlen($baseDir));
                    }
                    $currPath = '/' . trim($path, '/');
                    if ($currPath === '') {
                        $currPath = '/';
                    }

                    $isPrefix = function(string $prefix) use ($currPath): bool {
                        return $currPath === $prefix || str_starts_with($currPath, rtrim($prefix, '/') . '/');
                    };

                    $isDashboardActive = ($currPath === '/driver' || $currPath === '/driver/dashboard');
                    $isTripsActive     = $isPrefix('/driver/trips');
                    $isEarningsActive  = $isPrefix('/driver/earnings');
                    $isPaymentsActive  = $isPrefix('/driver/payments');
                    $isNotifActive     = $isPrefix('/driver/notifications');
                    $isHistoryActive   = $isPrefix('/driver/history');
                    $isVehicleActive   = $isPrefix('/driver/vehicle');
                    $isLocationActive  = $isPrefix('/driver/location');
                    $isProfileActive   = $isPrefix('/driver/profile');
                ?>

                <!-- 1. Dashboard -->
                <div class="sidebar-category" id="cat-driver-dashboard">
                    <a href="<?= url('/driver/dashboard') ?>" class="category-header direct-link <?= $isDashboardActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-dashboard-header" data-title="<?= e(__('sidebar.dashboard', 'Dashboard')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.dashboard', 'Dashboard')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isDashboardActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.dashboard', 'Dashboard')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 2. My Assigned Trips -->
                <div class="sidebar-category" id="cat-driver-trips">
                    <a href="<?= url('/driver/trips') ?>" class="category-header direct-link <?= $isTripsActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-trips-header" data-title="<?= e(__('sidebar.assigned_trips', 'Assigned Trips')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.assigned_trips', 'Assigned Trips')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTripsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.assigned_trips', 'Assigned Trips')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 2b. Trip Earnings -->
                <div class="sidebar-category" id="cat-driver-earnings">
                    <a href="<?= url('/driver/earnings') ?>" class="category-header direct-link <?= $isEarningsActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-earnings-header" data-title="<?= e(__('sidebar.trip_earnings', 'Trip Earnings')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.trip_earnings', 'Trip Earnings')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isEarningsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.trip_earnings', 'Trip Earnings')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 2c. Payment History -->
                <div class="sidebar-category" id="cat-driver-payments">
                    <a href="<?= url('/driver/payments') ?>" class="category-header direct-link <?= $isPaymentsActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-payments-header" data-title="<?= e(__('sidebar.payment_history', 'Payment History')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.payment_history', 'Payment History')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isPaymentsActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.payment_history', 'Payment History')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 3. Notifications & Alerts -->
                <div class="sidebar-category" id="cat-driver-notifications">
                    <a href="<?= url('/driver/notifications') ?>" class="category-header direct-link <?= $isNotifActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-notifications-header" data-title="<?= e(__('nav.notifications', 'Notifications')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('nav.notifications', 'Notifications')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if (($curNotifCount ?? 0) > 0): ?>
                                <span class="badge-dot" style="background:#EF4444;" title="<?= e(__('driver.unread_alerts', 'Unread alerts')) ?>"></span>
                            <?php endif; ?>
                            <?php if ($isNotifActive): ?>
                                <span class="active-dot" title="<?= e(__('nav.notifications', 'Notifications')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 3b. Trip History -->
                <div class="sidebar-category" id="cat-driver-history">
                    <a href="<?= url('/driver/history') ?>" class="category-header direct-link <?= $isHistoryActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-history-header" data-title="<?= e(__('sidebar.trip_history', 'Trip History')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.trip_history', 'Trip History')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isHistoryActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.trip_history', 'Trip History')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 4. My Vehicle -->
                <div class="sidebar-category" id="cat-driver-vehicle">
                    <a href="<?= url('/driver/vehicle') ?>" class="category-header direct-link <?= $isVehicleActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-vehicle-header" data-title="<?= e(__('sidebar.my_vehicle', 'My Vehicle')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.my_vehicle', 'My Vehicle')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isVehicleActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.my_vehicle', 'My Vehicle')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 5. Live Location / Location Sharing -->
                <div class="sidebar-category" id="cat-driver-location">
                    <a href="<?= url('/driver/location') ?>" class="category-header direct-link <?= $isLocationActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-location-header" data-title="<?= e(__('sidebar.live_location', 'Live Location')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.live_location', 'Live Location')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <span class="badge-dot" style="background:#10B981;" title="<?= e(__('driver.gps_enabled', 'GPS Enabled')) ?>"></span>
                            <?php if ($isLocationActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.live_location', 'Live Location')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 6. My Profile -->
                <div class="sidebar-category" id="cat-driver-profile">
                    <a href="<?= url('/driver/profile') ?>" class="category-header direct-link <?= $isProfileActive ? 'has-active' : '' ?>" data-no-ajax="true" id="cat-driver-profile-header" data-title="<?= e(__('sidebar.my_profile', 'My Profile')) ?>" style="text-decoration: none; width: 100%;">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.my_profile', 'My Profile')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isProfileActive): ?>
                                <span class="active-dot" title="<?= e(__('sidebar.my_profile', 'My Profile')) ?>"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <?php
                    $sidebarAvatarPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : (!empty($driver['profile_photo']) ? $driver['profile_photo'] : null);
                    $displayName = !empty($driver['name']) ? $driver['name'] : ($user['name'] ?? 'Driver');
                ?>
                <a href="<?= url('/driver/profile') ?>" class="user-badge" data-no-ajax="true" style="text-decoration:none;">
                    <div class="user-avatar" style="overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); color:#FFF;">
                        <?php if (!empty($sidebarAvatarPhoto)): ?>
                            <img src="<?= e(customer_avatar_url($sidebarAvatarPhoto)) ?>" alt="<?= e($displayName) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <span style="display: none;"><?= strtoupper(substr($displayName, 0, 1)) ?></span>
                        <?php else: ?>
                            <?= strtoupper(substr($displayName, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= e($displayName) ?></div>
                        <div class="user-role" style="color: var(--vg-gold-400); font-weight: 600;"><?= e(__('role.licensed_driver', 'Licensed Driver')) ?></div>
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

                    <!-- Notifications Button with Dropdown Box (Exact UI Matching Reference Image) -->
                    <?php
                        $curUId = \App\Core\Session::getUserId();
                        if (!isset($notifications) || !isset($unreadNotifCount)) {
                            if ($curUId) {
                                $notifService = new \App\Services\NotificationService();
                                if (!isset($notifications)) {
                                    $notifications = $notifService->getDriverNotifications($curUId, null, 10);
                                }
                                if (!isset($unreadNotifCount)) {
                                    $unreadNotifCount = $notifService->getUnreadCount($curUId, null);
                                }
                            } else {
                                $notifications = [];
                                $unreadNotifCount = 0;
                            }
                        }
                        $curNotifCount = (int)($unreadNotifCount ?? 0);
                        $curBadgeDisplay = $curNotifCount > 0 ? 'inline-flex' : 'none';
                        $curBadgeText = $curNotifCount > 99 ? '99+' : $curNotifCount;

                        // Split into Urgent (Red Section) and Upcoming/Recent (Amber Section) matching Reference Image
                        $urgentList = [];
                        $upcomingList = [];
                        foreach ($notifications as $n) {
                            if (empty($n['is_read']) || ($n['type'] ?? '') === 'trip_assigned') {
                                $urgentList[] = $n;
                            } else {
                                $upcomingList[] = $n;
                            }
                        }
                    ?>
                    <div class="topbar-notif-wrap" id="topbarNotifWrap">
                        <button type="button" class="topbar-icon-btn notif-bell-btn" id="topbarNotifBtn" onclick="toggleNotifDropdown(event)" title="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-label="<?= e(__('nav.notifications', 'Notifications')) ?>" aria-haspopup="true" aria-expanded="false" style="text-decoration: none; cursor: pointer;">
                            <span class="notif-badge-target">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="nav-badge-pill" id="driverTopNotifBadge" data-count="<?= $curNotifCount ?>" style="display: <?= $curBadgeDisplay ?>;" <?= $curNotifCount > 0 ? 'title="' . $curNotifCount . ' unread notifications"' : '' ?>><?= $curNotifCount > 0 ? $curBadgeText : '' ?></span>
                            </span>
                        </button>

                        <!-- Notification Dropdown Popover (Exact Pixel-Perfect Match to Reference Image) -->
                        <div class="topbar-notif-box" id="topbarNotifBox" onclick="event.stopPropagation()">
                            <!-- Header: Alert Icon + Title + Red Badge -->
                            <div class="notif-box-head">
                                <div class="notif-box-head-left">
                                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="notif-box-head-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <h4 class="notif-box-head-title">Duty &amp; Trip Alerts</h4>
                                </div>
                                <span class="notif-box-head-badge" id="dropdownHeaderBadge" data-count="<?= $curNotifCount ?>" style="display: <?= $curBadgeDisplay ?>;"><?= $curNotifCount > 0 ? $curBadgeText : '' ?></span>
                            </div>

                            <!-- Scrollable Notification Feed -->
                            <div class="notif-box-body">
                                <?php if (!empty($urgentList)): ?>
                                    <!-- Section 1: Red Header -->
                                    <div class="notif-box-section-title notif-sec-red">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        <span><?= e(__('driver.new_duty_assignments', 'NEW DUTY ASSIGNMENTS')) ?></span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($urgentList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $isUnread = empty($n['is_read']);
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Pickup') . ' → ' . ($payload['drop_address'] ?? 'Drop') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                                $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');
                                            ?>
                                            <a href="<?= $targetUrl ?>" class="notif-box-item <?= $isUnread ? 'is-unread' : '' ?>" id="notif-box-item-<?= (int)$n['id'] ?>" onclick="markNotifReadAsync(<?= (int)$n['id'] ?>)">
                                                <div class="notif-box-item-icon notif-box-icon-red">
                                                    <i class="fa-solid fa-route"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <div class="notif-box-item-head">
                                                        <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                        <?php if ($isUnread): ?>
                                                            <span class="notif-item-unread-dot" title="<?= e(__('notification.unread_alert', 'Unread Alert')) ?>"></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-arrow">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($upcomingList)): ?>
                                    <!-- Section 2: Amber Header -->
                                    <div class="notif-box-section-title notif-sec-amber">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span><?= e(__('driver.upcoming_recent_alerts', 'UPCOMING & RECENT ALERTS')) ?></span>
                                    </div>
                                    <div class="notif-box-section-items">
                                        <?php foreach ($upcomingList as $n): ?>
                                            <?php
                                                $payload = $n['payload'] ?? [];
                                                $title = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Trip') . ')' : $n['title'];
                                                $sub = !empty($payload['reporting_time']) ? 'Duty: ' . $payload['reporting_time'] : time_ago($n['created_at']);
                                                $dispatchId = $payload['dispatch_id'] ?? $n['reference_id'] ?? null;
                                                $targetUrl = $dispatchId ? url('/driver/trips/' . $dispatchId) : url('/driver/notifications');
                                            ?>
                                            <a href="<?= $targetUrl ?>" class="notif-box-item" id="notif-box-item-<?= (int)$n['id'] ?>" onclick="markNotifReadAsync(<?= (int)$n['id'] ?>)">
                                                <div class="notif-box-item-icon notif-box-icon-amber">
                                                    <i class="fa-solid fa-clock"></i>
                                                </div>
                                                <div class="notif-box-item-info">
                                                    <span class="notif-box-item-name" title="<?= e($title) ?>"><?= e($title) ?></span>
                                                    <span class="notif-box-item-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
                                                </div>
                                                <div class="notif-box-item-arrow">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($urgentList) && empty($upcomingList)): ?>
                                    <div class="notif-box-empty">
                                        <div class="notif-box-empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                                        <div class="notif-box-empty-title"><?= e(__('notification.all_caught_up', 'All caught up!')) ?></div>
                                        <div class="notif-box-empty-sub"><?= e(__('driver.no_active_alerts', 'No active alerts. New duty dispatches will appear here.')) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: View All Link -->
                            <div class="notif-box-footer">
                                <a href="<?= url('/driver/notifications') ?>" class="notif-box-footer-link">
                                    <?= e(__('notification.view_all', 'View All Notifications')) ?> &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Profile Chip with Dropdown -->
                    <?php
                        $topbarAvatarPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : (!empty($driver['profile_photo']) ? $driver['profile_photo'] : null);
                        $topbarDisplayName = !empty($driver['name']) ? $driver['name'] : ($user['name'] ?? 'Driver');
                    ?>
                    <div class="topbar-user-profile" id="topbarUserProfile" onclick="toggleUserDropdown(event)" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="<?= e(__('topbar.view_profile', 'Driver Profile Menu')) ?>">
                        <div class="topbar-avatar-wrap">
                            <?php if (!empty($topbarAvatarPhoto)): ?>
                                <img src="<?= e(customer_avatar_url($topbarAvatarPhoto)) ?>" class="topbar-avatar-img" alt="<?= e($topbarDisplayName) ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($topbarDisplayName, 0, 1)) ?></div>
                            <?php else: ?>
                                <div class="topbar-avatar"><?= strtoupper(substr($topbarDisplayName, 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="topbar-user-meta">
                            <span class="topbar-user-name"><?= e($topbarDisplayName) ?></span>
                            <span class="topbar-user-role"><?= e(__('role.driver', 'Driver')) ?></span>
                        </div>
                        <svg class="topbar-user-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"></path>
                        </svg>

                        <!-- Driver Profile Dropdown Menu -->
                        <div class="topbar-user-menu" id="topbarUserMenu" role="menu" aria-labelledby="topbarUserProfile" onclick="event.stopPropagation()">
                            <div class="topbar-menu-user-head">
                                <div class="topbar-menu-head-avatar">
                                    <?php if (!empty($topbarAvatarPhoto)): ?>
                                        <img src="<?= e(customer_avatar_url($topbarAvatarPhoto)) ?>" alt="<?= e($topbarDisplayName) ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                        <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($topbarDisplayName, 0, 1)) ?></div>
                                    <?php else: ?>
                                        <div class="topbar-avatar"><?= strtoupper(substr($topbarDisplayName, 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="topbar-menu-head-meta">
                                    <strong class="topbar-menu-name"><?= e($topbarDisplayName) ?></strong>
                                    <span class="topbar-menu-role-pill"><?= e(__('role.driver', 'Driver')) ?></span>
                                    <span class="topbar-menu-email"><?= e($user['phone'] ?? $user['email'] ?? $user['username'] ?? '') ?></span>
                                </div>
                            </div>
                            <div class="topbar-menu-divider"></div>
                            <a href="<?= url('/driver/profile') ?>" class="topbar-menu-item" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                                    <i class="fa-solid fa-user-circle"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title"><?= e(__('topbar.view_profile', 'View Profile')) ?></span>
                                    <span class="topbar-menu-sub"><?= e(__('topbar.driver_profile_sub', 'Driver details & vehicle')) ?></span>
                                </div>
                                <i class="fa-solid fa-chevron-right topbar-menu-arrow"></i>
                            </a>
                            <a href="<?= url('/driver/earnings') ?>" class="topbar-menu-item" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title">Earnings &amp; Payouts</span>
                                    <span class="topbar-menu-sub">Trip dues and disbursements</span>
                                </div>
                                <i class="fa-solid fa-chevron-right topbar-menu-arrow"></i>
                            </a>
                            <button type="button" class="topbar-menu-item tours-install-action tours-install-btn" role="menuitem" onclick="triggerToursInstall(event)" style="background:none; border:none; width:100%; text-align:left; cursor:pointer;">
                                <div class="topbar-menu-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                                    <i class="fa-solid fa-cloud-arrow-down"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title install-btn-label"><?= __('pwa.install_app_name', ['name' => e(pwa_app_name())]) ?></span>
                                    <span class="topbar-menu-sub"><?= e(__('sidebar.install_app_sub', 'Launch as standalone app')) ?></span>
                                </div>
                                <i class="fa-solid fa-download topbar-menu-arrow"></i>
                            </button>
                            <a href="<?= url('/driver/notification-settings') ?>" class="topbar-menu-item" data-no-ajax="true" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title"><?= e(__('sidebar.notification_settings', 'Notification Settings')) ?></span>
                                    <span class="topbar-menu-sub"><?= e(__('sidebar.web_push_alerts_sub', 'Web push alerts & devices')) ?></span>
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
                    &copy; <?= date('Y') ?> <strong><?= e(company_display_name()) ?></strong>. <?= e(__('footer.all_rights_reserved', 'All rights reserved.')) ?>
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
            <a href="<?= url('/driver/dashboard') ?>" class="bottom-nav-item <?= $isDashboardActive ? 'active' : '' ?>" data-no-ajax="true">
                <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span><?= e(__('sidebar.dashboard', 'Home')) ?></span>
            </a>
            <a href="<?= url('/driver/trips') ?>" class="bottom-nav-item <?= $isTripsActive ? 'active' : '' ?>" data-no-ajax="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span><?= e(__('sidebar.trips', 'Trips')) ?></span>
            </a>

            <!-- Center slot spacer (Ensures left 2 items and right 2 items are 100% symmetric) -->
            <div class="bottom-nav-center-slot" aria-hidden="true"></div>

            <a href="<?= url('/driver/location') ?>" class="bottom-nav-item <?= $isLocationActive ? 'active' : '' ?>" data-no-ajax="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span><?= e(__('sidebar.live_location', 'Tracking')) ?></span>
            </a>
            <a href="<?= url('/driver/notifications') ?>" class="bottom-nav-item nav-notif-item <?= $isNotifActive ? 'active' : '' ?>" data-no-ajax="true">
                <span class="notif-badge-target">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="nav-badge-pill" id="driverBottomNotifBadge" data-count="<?= $curNotifCount ?>" style="display: <?= $curBadgeDisplay ?>;" <?= $curNotifCount > 0 ? 'title="' . $curNotifCount . ' unread notifications"' : '' ?>><?= $curNotifCount > 0 ? $curBadgeText : '' ?></span>
                </span>
                <span><?= e(__('nav.notifications', 'Notifications')) ?></span>
            </a>
        </div>
    </nav>

    <!-- Professional Custom PWA Installation Modal -->
    <?php require dirname(__DIR__) . '/components/pwa_install_modal.php'; ?>

    <script src="<?= asset('js/tours-cache.js') ?>?v=1.0"></script>
    <script src="<?= asset('js/app.js') ?>?v=3.1"></script>
    <script src="<?= asset('js/table-cards.js') ?>?v=2.0"></script>
    <script src="<?= asset('js/tours-pwa.js') ?>?v=5.1"></script>
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

        // Topbar Notification Popover (Ref Image UI)
        function markNotifReadAsync(id) {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('<?= url('/driver/notifications') ?>/' + id + '/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(function() {});
}

function toggleNotifDropdown(e) {
            if (e) {
                e.stopPropagation();
            }
            var wrap = document.getElementById('topbarNotifWrap');
            if (!wrap) return;
            var wasOpen = wrap.classList.contains('is-open');

            // Close other topbar menus
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

            if (wasOpen) {
                wrap.classList.remove('is-open');
            } else {
                wrap.classList.add('is-open');
            }
        }

        function dismissDropdownNotif(id, e) {
            if (e) {
                e.stopPropagation();
            }
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('<?= url('/driver/notifications') ?>/' + id + '/read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    var item = document.getElementById('notif-box-item-' + id);
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(20px)';
                        setTimeout(function() {
                            item.remove();
                            checkDropdownEmpty();
                        }, 200);
                    }
                    var dbItem = document.getElementById('notif-item-' + id);
                    if (dbItem) {
                        dbItem.classList.remove('is-unread');
                        var newBadge = dbItem.querySelector('.notif-pill-new');
                        if (newBadge) newBadge.remove();
                        var dismissBtn = dbItem.querySelector('.notif-btn-dismiss');
                        if (dismissBtn) dismissBtn.remove();
                    }
                    updateAllNotifBadges(data.unread_count);
                }
            })
            .catch(function(err) { console.error('Dismiss notification failed:', err); });
        }

        function formatDriverNotifBadgeText(count) {
            var c = parseInt(count, 10);
            if (isNaN(c) || c <= 0) return '';
            return c > 99 ? '99+' : String(c);
        }

        function updateAllNotifBadges(count) {
            var c = parseInt(count, 10);
            if (isNaN(c) || c < 0) c = 0;
            var topBadge = document.getElementById('driverTopNotifBadge');
            var headerBadge = document.getElementById('dropdownHeaderBadge');
            var bottomBadge = document.getElementById('driverBottomNotifBadge');
            var countPill = document.getElementById('unreadNotifCountPill');
            var formatted = formatDriverNotifBadgeText(c);

            [topBadge, bottomBadge].forEach(function(el) {
                if (!el) return;
                el.setAttribute('data-count', c);
                if (c > 0) {
                    el.textContent = formatted;
                    el.setAttribute('title', c + ' unread notifications');
                    el.style.display = 'inline-flex';
                } else {
                    el.textContent = '';
                    el.removeAttribute('title');
                    el.style.display = 'none';
                }
            });

            if (headerBadge) {
                headerBadge.setAttribute('data-count', c);
                if (c > 0) {
                    headerBadge.textContent = formatted;
                    headerBadge.style.display = 'inline-flex';
                } else {
                    headerBadge.textContent = '0';
                    headerBadge.style.display = 'none';
                }
            }

            if (countPill) {
                if (c > 0) {
                    countPill.textContent = c + ' unread';
                    countPill.style.display = 'inline-block';
                } else {
                    countPill.remove();
                }
            }
        }

        function checkDropdownEmpty() {
            var remaining = document.querySelectorAll('.notif-box-item');
            if (!remaining || remaining.length === 0) {
                var body = document.querySelector('.notif-box-body');
                if (body) {
                    body.innerHTML = '<div class="notif-box-empty"><div class="notif-box-empty-icon"><i class="fa-regular fa-bell-slash"></i></div><div class="notif-box-empty-title">All caught up!</div><div class="notif-box-empty-sub">No active alerts. New duty dispatches will appear here.</div></div>';
                }
            }
        }

        document.addEventListener('click', function(e) {
            var wrap = document.getElementById('topbarNotifWrap');
            if (wrap && !wrap.contains(e.target)) {
                wrap.classList.remove('is-open');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var wrap = document.getElementById('topbarNotifWrap');
                if (wrap) wrap.classList.remove('is-open');
            }
        });

        // BFCache revalidation protection: force server-side route guard execution on browser history navigation
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
