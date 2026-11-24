<?php
/**
 * Office Notifications Hub — Dedicated Operations & Duty Alerts Center
 * Follows the canonical Office design system (Forest Teal #176B5B + Warm Ivory #F7F8F4)
 * Responsive Architecture: Desktop Modern PMS Standard + Mobile App Cards (exact match to office/bookings)
 */

$currStatus  = strtolower($status ?? '');
$totRec      = (int)($totalRecords ?? count($notifications));
$currP       = (int)($page ?? 1);
$pSize       = (int)($perPage ?? 10);
$unreadCount = (int)($unreadNotifCount ?? ($kpis['total_unread'] ?? 0));
$searchVal   = trim((string)($search ?? ''));

$buildFilterUrl = function(array $overrides = []) use ($searchVal, $currStatus, $pSize) {
    $q = [];
    $s  = array_key_exists('search', $overrides) ? $overrides['search'] : $searchVal;
    $st = array_key_exists('status', $overrides) ? $overrides['status'] : $currStatus;
    $pp = array_key_exists('per_page', $overrides) ? $overrides['per_page'] : $pSize;
    $pg = $overrides['page'] ?? null;

    if (!empty($s))  $q['search']   = $s;
    if (!empty($st)) $q['status']   = $st;
    if (!empty($pp) && $pp != 10)   $q['per_page'] = $pp;
    if (!empty($pg) && $pg > 1)     $q['page']     = $pg;

    return url('/office/notifications' . (!empty($q) ? '?' . http_build_query($q) : ''));
};
?>

<div class="bookings-page-view office-notifications-page">
    <!-- ====================================================================
         1. DESKTOP ONLY: Canonical Page Header (Screens >= 769px)
         ==================================================================== -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Notifications & Operation Alerts',
            'description' => 'Monitor real-time driver responses, tour bookings, billing events, and system telemetry.',
            'badge'       => 'Live Activity',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Notifications', 'url' => ''],
            ],
            'actions'     => '
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <a href="' . url('/office/notification-settings') . '" class="btn btn-outline" style="border-radius: 10px; gap: 0.45rem;">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                    ' . ($unreadCount > 0 ? '
                    <button type="button" class="btn btn-primary" onclick="markAllNotificationsPageRead()" style="border-radius: 10px; gap: 0.45rem;">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Mark All Read</span>
                    </button>
                    ' : '') . '
                </div>
            ',
        ]) ?>
    </div>

    <!-- ====================================================================
         2. DESKTOP ONLY: Top KPI Summary Cards (Hero Section - Screens >= 769px)
         ==================================================================== -->
    <div class="booking-kpi-grid notif-kpi-grid" id="notifKpiGrid">
        <a href="<?= $buildFilterUrl(['status' => '', 'page' => 1]) ?>" class="booking-kpi-card <?= empty($currStatus) ? 'is-selected' : '' ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Notifications</span>
                <span class="booking-kpi-icon icon-teal">
                    <i class="fa-solid fa-bell"></i>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($kpis['total_all'] ?? $totRec) ?></div>
            <div class="booking-kpi-sub">
                <span>All recorded activity</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="<?= $buildFilterUrl(['status' => 'unread', 'page' => 1]) ?>" class="booking-kpi-card <?= $currStatus === 'unread' ? 'is-selected' : '' ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Unread Alerts</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <i class="fa-solid fa-envelope"></i>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;" id="kpiUnreadVal"><?= number_format($kpis['total_unread'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background: #D95757;"></span>
                <span>Requires attention</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="<?= $buildFilterUrl(['status' => 'trips', 'page' => 1]) ?>" class="booking-kpi-card <?= in_array($currStatus, ['trips', 'trip_assigned']) ? 'is-selected' : '' ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Trip Duties &amp; Drivers</span>
                <span class="booking-kpi-icon icon-emerald">
                    <i class="fa-solid fa-bus"></i>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($kpis['total_trips'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Driver assignments</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="<?= $buildFilterUrl(['status' => 'read', 'page' => 1]) ?>" class="booking-kpi-card <?= $currStatus === 'read' ? 'is-selected' : '' ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Archived / Read</span>
                <span class="booking-kpi-icon" style="background: rgba(114, 130, 122, 0.12); color: #72827A;">
                    <i class="fa-solid fa-check-circle"></i>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($kpis['total_read'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Already reviewed</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>
    </div>

    <!-- ====================================================================
         3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture)
         ==================================================================== -->
    <form method="GET" action="<?= url('/office/notifications') ?>" class="filter-bar-compact" id="notifDesktopFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_by_title_route_passenge', 'Search by title, route, passenger, dispatch code...')) ?>" value="<?= e($searchVal) ?>" autocomplete="off">
            <?php if (!empty($searchVal)): ?>
                <a href="<?= $buildFilterUrl(['search' => '']) ?>" class="fbc-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput" onchange="this.form.submit()">
            <option value="" <?= empty($currStatus) ? 'selected' : '' ?>>All Alerts (<?= number_format($kpis['total_all'] ?? $totRec) ?>)</option>
            <option value="unread" <?= $currStatus === 'unread' ? 'selected' : '' ?>>Unread (<?= (int)($kpis['total_unread'] ?? 0) ?>)</option>
            <option value="trips" <?= in_array($currStatus, ['trips', 'trip_assigned']) ? 'selected' : '' ?>>Trip Duties (<?= (int)($kpis['total_trips'] ?? 0) ?>)</option>
            <option value="bookings" <?= $currStatus === 'bookings' ? 'selected' : '' ?>>Bookings</option>
            <option value="finance" <?= $currStatus === 'finance' ? 'selected' : '' ?>>Finance</option>
            <option value="read" <?= $currStatus === 'read' ? 'selected' : '' ?>>Read / Archived (<?= (int)($kpis['total_read'] ?? 0) ?>)</option>
        </select>

        <select name="per_page" id="perPageInput" onchange="this.form.submit()" title="<?= e(__('common.records_per_page', 'Records per page')) ?>">
            <?php foreach ([10, 25, 50] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($pSize == $opt) ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="<?= e(__('tooltip.search', 'Search')) ?>"><i class="fa-solid fa-arrow-right"></i></button>
            <a href="<?= url('/office/notifications') ?>" class="fbc-btn" title="<?= e(__('btn.refresh', 'Refresh')) ?>"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($searchVal) || !empty($currStatus)): ?>
                <a href="<?= url('/office/notifications') ?>" class="fbc-btn fbc-btn-clear" title="<?= e(__('common.reset_filters', 'Clear Filters')) ?>">
                    <i class="fa-solid fa-xmark"></i> <span>Clear</span>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ====================================================================
         4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll (Exact Bookings)
         ==================================================================== -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="<?= e(__('common.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Notifications</h2>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <?php if ($unreadCount > 0): ?>
                    <button type="button" class="mv-add-btn" onclick="markAllNotificationsPageRead()" title="<?= e(__('tooltip.mark_all_read', 'Mark All Read')) ?>">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Mark All Read</span>
                    </button>
                <?php else: ?>
                    <a href="<?= url('/office/notification-settings') ?>" class="mv-add-btn" style="background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; box-shadow: none;">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar + Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/notifications') ?>" class="mv-search-form" id="mvNotifSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_alerts_routes_dispatche', 'Search alerts, routes, dispatches...')) ?>" value="<?= e($searchVal) ?>" autocomplete="off">
                    <?php if (!empty($searchVal)): ?>
                        <a href="<?= $buildFilterUrl(['search' => '']) ?>" class="mv-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (!empty($currStatus)): ?>
                    <input type="hidden" name="status" value="<?= e($currStatus) ?>">
                <?php endif; ?>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="<?= e(__('common.filter', 'Filter options')) ?>">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Filter Panel (Mobile Drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvStatusSelect">Category / Type:</label>
                    <select name="status" id="mvStatusSelect" onchange="window.location.href = '<?= url('/office/notifications?status=') ?>' + encodeURIComponent(this.value) + '<?= !empty($searchVal) ? '&search=' . urlencode($searchVal) : '' ?>'">
                        <option value="" <?= empty($currStatus) ? 'selected' : '' ?>>All Activity</option>
                        <option value="unread" <?= $currStatus === 'unread' ? 'selected' : '' ?>>Unread</option>
                        <option value="trips" <?= in_array($currStatus, ['trips', 'trip_assigned']) ? 'selected' : '' ?>>Trip Duties</option>
                        <option value="bookings" <?= $currStatus === 'bookings' ? 'selected' : '' ?>>Bookings</option>
                        <option value="finance" <?= $currStatus === 'finance' ? 'selected' : '' ?>>Finance</option>
                        <option value="read" <?= $currStatus === 'read' ? 'selected' : '' ?>>Read</option>
                    </select>
                </div>
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect" onchange="window.location.href = '<?= $buildFilterUrl(['per_page' => 'PLACEHOLDER']) ?>'.replace('PLACEHOLDER', this.value)">
                        <?php foreach ([10, 25, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $pSize == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Filter Pills -->
        <div class="mv-status-scroll" id="notifMvStatusScroll">
            <a href="<?= $buildFilterUrl(['status' => '', 'page' => 1]) ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= number_format($kpis['total_all'] ?? $totRec) ?></span>
            </a>
            <a href="<?= $buildFilterUrl(['status' => 'unread', 'page' => 1]) ?>" class="mv-pill <?= $currStatus === 'unread' ? 'active' : '' ?>">
                <span>Unread</span>
                <span class="mv-pill-cnt" style="<?= !empty($kpis['total_unread']) ? 'background: #D95757; color: #fff;' : '' ?>"><?= number_format($kpis['total_unread'] ?? 0) ?></span>
            </a>
            <a href="<?= $buildFilterUrl(['status' => 'trips', 'page' => 1]) ?>" class="mv-pill <?= in_array($currStatus, ['trips', 'trip_assigned']) ? 'active' : '' ?>">
                <span>Trips</span>
                <span class="mv-pill-cnt"><?= number_format($kpis['total_trips'] ?? 0) ?></span>
            </a>
            <a href="<?= $buildFilterUrl(['status' => 'bookings', 'page' => 1]) ?>" class="mv-pill <?= $currStatus === 'bookings' ? 'active' : '' ?>">
                <span>Bookings</span>
            </a>
            <a href="<?= $buildFilterUrl(['status' => 'finance', 'page' => 1]) ?>" class="mv-pill <?= $currStatus === 'finance' ? 'active' : '' ?>">
                <span>Finance</span>
            </a>
            <a href="<?= $buildFilterUrl(['status' => 'read', 'page' => 1]) ?>" class="mv-pill <?= $currStatus === 'read' ? 'active' : '' ?>">
                <span>Read</span>
                <span class="mv-pill-cnt"><?= number_format($kpis['total_read'] ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- ====================================================================
         5. ALL NOTIFICATIONS SECTION: Desktop List + Mobile App Cards
         ==================================================================== -->
    <section class="recent-bookings-section" id="notificationsSection">
        <div class="section-header">
            <h3 class="section-title">All Activity &amp; Alerts</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- 5A. DESKTOP ONLY: Activity Feed List (Screens >= 769px) -->
        <div class="office-notif-desktop-list" id="officeNotifDesktopList">
            <?php if (empty($notifications)): ?>
                <div class="card" style="padding: 3rem 1.5rem; text-align: center; border-radius: 16px; border: 1px solid rgba(22,68,59,0.08);">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(23,107,91,0.08); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                        <i class="fa-regular fa-bell-slash"></i>
                    </div>
                    <h3 style="margin: 0 0 0.4rem; color: #20352F; font-size: 1.2rem; font-weight: 700;">No Notifications Found</h3>
                    <p style="margin: 0; color: #72827A; font-size: 0.92rem;">
                        <?php if (!empty($searchVal) || !empty($currStatus)): ?>
                            No activity records matched your filter criteria. <a href="<?= url('/office/notifications') ?>" style="color: #176B5B; font-weight: 600;">Clear filters</a>
                        <?php else: ?>
                            You're all caught up! When new trip dispatches, bookings, or payments occur, alerts will appear here.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <?php foreach ($notifications as $n):
                        $isUnread = empty($n['is_read']);
                        $refType = $n['reference_type'] ?? '';
                        $refId = $n['reference_id'] ?? null;

                        // Target link
                        $targetUrl = null;
                        $targetLabel = null;
                        if ($refType === 'dispatches' && $refId) {
                            $targetUrl = url('/office/trips/assign');
                            $targetLabel = 'View Trip Dispatch';
                        } elseif ($refType === 'bookings' && $refId) {
                            $targetUrl = url('/office/bookings/' . $refId);
                            $targetLabel = 'View Booking';
                        } elseif ($refType === 'invoices' && $refId) {
                            $targetUrl = url('/office/invoices/' . $refId);
                            $targetLabel = 'View Invoice';
                        } elseif ($refType === 'customers' && $refId) {
                            $targetUrl = url('/office/customers/' . $refId);
                            $targetLabel = 'View Customer';
                        }

                        // Icon resolution
                        $iconClass = 'fa-bell';
                        $iconBg = 'rgba(23,107,91,0.1)';
                        $iconColor = '#176B5B';
                        if (str_starts_with($n['type'], 'trip_')) {
                            $iconClass = 'fa-bus';
                            $iconBg = 'rgba(33, 135, 101, 0.12)';
                            $iconColor = '#218765';
                        } elseif (str_starts_with($n['type'], 'booking_')) {
                            $iconClass = 'fa-calendar-check';
                            $iconBg = 'rgba(59, 130, 246, 0.12)';
                            $iconColor = '#3B82F6';
                        } elseif (str_starts_with($n['type'], 'payment_') || str_starts_with($n['type'], 'finance_')) {
                            $iconClass = 'fa-file-invoice-dollar';
                            $iconBg = 'rgba(199, 138, 53, 0.12)';
                            $iconColor = '#C78A35';
                        }
                    ?>
                        <div class="card office-notif-card <?= $isUnread ? 'is-unread' : '' ?>" id="officeNotifCard_<?= (int)$n['id'] ?>" style="border-radius: 14px; border: 1px solid <?= $isUnread ? 'rgba(23, 107, 91, 0.35)' : 'rgba(22, 68, 59, 0.08)' ?>; transition: all 0.2s ease;">
                            <div style="padding: 1.15rem 1.25rem; display: flex; gap: 1rem; align-items: flex-start; justify-content: space-between; flex-wrap: wrap;">
                                <div style="display: flex; gap: 1rem; align-items: flex-start; flex: 1; min-width: 240px;">
                                    <div style="width: 42px; height: 42px; border-radius: 12px; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; margin-top: 2px;">
                                        <i class="fa-solid <?= $iconClass ?>"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #20352F;">
                                                <?= e($n['title']) ?>
                                            </h4>
                                            <?php if ($isUnread): ?>
                                                <span class="badge" style="background: rgba(217,87,87,0.12); color: #D95757; font-weight: 700; font-size: 0.72rem; padding: 0.2rem 0.55rem; border-radius: 6px;">Unread</span>
                                            <?php endif; ?>
                                            <span style="color: #72827A; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                                <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i>
                                                <?= time_ago($n['created_at']) ?> (<?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>)
                                            </span>
                                        </div>
                                        <p style="margin: 0 0 0.65rem; color: #53685F; font-size: 0.92rem; line-height: 1.55; white-space: pre-line;">
                                            <?= e($n['message']) ?>
                                        </p>
                                        <?php if ($targetUrl): ?>
                                            <div style="margin-top: 0.5rem;">
                                                <a href="<?= $targetUrl ?>" class="btn btn-sm btn-outline" style="border-radius: 8px; font-size: 0.82rem; gap: 0.4rem; padding: 0.3rem 0.75rem; color: #176B5B; border-color: rgba(23,107,91,0.25);">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                    <span><?= e($targetLabel) ?></span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 2px;">
                                    <?php if ($isUnread): ?>
                                        <button type="button" class="btn btn-sm btn-outline" onclick="markPageNotifRead(<?= (int)$n['id'] ?>)" title="<?= e(__('tooltip.mark_as_read', 'Mark as read')) ?>" style="border-radius: 8px; font-size: 0.8rem; padding: 0.35rem 0.75rem; color: #218765; border-color: rgba(33, 135, 101, 0.3);">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Mark Read</span>
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #72827A; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.5rem;">
                                            <i class="fa-solid fa-check-double" style="color: #218765;"></i> Read
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 5B. MOBILE ONLY: Mobile Cards Grid (Screens <= 768px, Exact Bookings Architecture) -->
        <div class="cards-grid" id="notificationsCardsGrid">
            <?php foreach ($notifications as $n):
                $isUnread = empty($n['is_read']);
                $refType = $n['reference_type'] ?? '';
                $refId = $n['reference_id'] ?? null;

                // Target link
                $targetUrl = null;
                $targetLabel = null;
                if ($refType === 'dispatches' && $refId) {
                    $targetUrl = url('/office/trips/assign');
                    $targetLabel = 'View Trip Dispatch';
                } elseif ($refType === 'bookings' && $refId) {
                    $targetUrl = url('/office/bookings/' . $refId);
                    $targetLabel = 'View Booking';
                } elseif ($refType === 'invoices' && $refId) {
                    $targetUrl = url('/office/invoices/' . $refId);
                    $targetLabel = 'View Invoice';
                } elseif ($refType === 'customers' && $refId) {
                    $targetUrl = url('/office/customers/' . $refId);
                    $targetLabel = 'View Customer';
                }

                // Icon resolution
                $iconClass = 'fa-bell';
                $iconBg = 'rgba(23,107,91,0.1)';
                $iconColor = '#176B5B';
                if (str_starts_with($n['type'], 'trip_')) {
                    $iconClass = 'fa-bus';
                    $iconBg = 'rgba(33, 135, 101, 0.12)';
                    $iconColor = '#218765';
                } elseif (str_starts_with($n['type'], 'booking_')) {
                    $iconClass = 'fa-calendar-check';
                    $iconBg = 'rgba(59, 130, 246, 0.12)';
                    $iconColor = '#3B82F6';
                } elseif (str_starts_with($n['type'], 'payment_') || str_starts_with($n['type'], 'finance_')) {
                    $iconClass = 'fa-file-invoice-dollar';
                    $iconBg = 'rgba(199, 138, 53, 0.12)';
                    $iconColor = '#C78A35';
                }
            ?>
            <div class="mv-booking-card-wrapper <?= $isUnread ? 'is-unread' : '' ?>" id="officeNotifMobileCard_<?= (int)$n['id'] ?>">
                <div class="mv-booking-card">
                    <!-- 1. Left Thumbnail / Icon Wrap -->
                    <div class="mv-thumb-wrap" style="background: <?= $iconBg ?>; color: <?= $iconColor ?>;">
                        <i class="fa-solid <?= $iconClass ?>" style="font-size: 1.35rem;"></i>
                    </div>

                    <!-- 2. Middle Details -->
                    <div class="mv-details">
                        <div class="mv-tour-title" title="<?= e($n['title']) ?>">
                            <?= e($n['title']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-clock"></i>
                            <span><?= time_ago($n['created_at']) ?> &bull; <?= date('d M, h:i A', strtotime($n['created_at'])) ?></span>
                        </div>
                        <div class="mv-passenger mv-address" style="color: #475569; margin-top: 3px;">
                            <span><?= e($n['message']) ?></span>
                        </div>
                        <?php if (!empty($refType) && !empty($refId)): ?>
                        <div class="mv-vehicle" style="color: #176B5B; font-weight: 600; margin-top: 2px;">
                            <i class="fa-solid fa-link"></i>
                            <span><?= ucfirst($refType) ?> #<?= (int)$refId ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 3. Right Column: Status pill badge -->
                    <div class="mv-right-col">
                        <span class="cust-toggle-pill <?= $isUnread ? 'pending' : 'completed' ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 999px;">
                            <span class="toggle-pulse-dot <?= $isUnread ? 'pending' : 'completed' ?>"></span>
                            <span class="toggle-label-text"><?= $isUnread ? 'Unread' : 'Read' ?></span>
                        </span>
                    </div>
                </div>

                <!-- 4. Mobile Card Action Row (Integrated at the bottom of the card) -->
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                        </button>
                        <div class="action-dropdown-menu">
                            <?php if ($targetUrl): ?>
                                <a href="<?= $targetUrl ?>" class="action-dropdown-item view">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span><?= e($targetLabel) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <button type="button" class="action-dropdown-item edit" onclick="markPageNotifRead(<?= (int)$n['id'] ?>, event)">
                                    <i class="fa-solid fa-check"></i> <span>Mark as Read</span>
                                </button>
                            <?php else: ?>
                                <div class="action-dropdown-item" style="opacity: 0.6; cursor: default;">
                                    <i class="fa-solid fa-check-double" style="color: #218765;"></i> <span>Already Read</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($notifications)): ?>
                <div class="no-data-message" style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:2.5rem 1.5rem;text-align:center;">
                    <div class="no-data-icon" style="font-size:2rem;color:#94a3b8;margin-bottom:8px;"><i class="fa-regular fa-bell-slash"></i></div>
                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No notifications found</h4>
                    <p class="text-muted" style="margin:0;font-size:0.9rem;">Try adjusting your search or category filters.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 6. Responsive Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="modern-pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.85rem; color: #72827A;">
                    Showing <strong><?= (($currP - 1) * $pSize) + 1 ?></strong> to <strong><?= min($currP * $pSize, $totRec) ?></strong> of <strong><?= number_format($totRec) ?></strong> alerts
                </div>
                <div class="pagination-pills" style="display: flex; gap: 0.35rem;">
                    <?php if ($currP > 1): ?>
                        <a href="<?= $buildFilterUrl(['page' => $currP - 1]) ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $currP - 2); $i <= min($totalPages, $currP + 2); $i++): ?>
                        <a href="<?= $buildFilterUrl(['page' => $i]) ?>" class="btn btn-sm <?= $i === $currP ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 8px; min-width: 34px;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($currP < $totalPages): ?>
                        <a href="<?= $buildFilterUrl(['page' => $currP + 1]) ?>" class="btn btn-sm btn-outline" style="border-radius: 8px;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
/* ==========================================================================
   Responsive Architecture: Desktop vs Mobile Display Rules
   ========================================================================== */
@media (min-width: 769px) {
    .office-notifications-page .mv-bookings-header {
        display: none !important;
    }
    .office-notifications-page .cards-grid {
        display: none !important;
    }
    .office-notifications-page .desktop-header-wrapper,
    .office-notifications-page .booking-kpi-grid,
    .office-notifications-page .filter-bar-compact,
    .office-notifications-page .office-notif-desktop-list {
        display: block !important;
    }
    .office-notifications-page .booking-kpi-grid {
        display: grid !important;
    }
    .office-notifications-page .filter-bar-compact {
        display: flex !important;
    }
}

@media (max-width: 768px) {
    /* Hide all Desktop & Hero elements completely on mobile */
    .office-notifications-page .desktop-header-wrapper,
    .office-notifications-page .booking-kpi-grid,
    .office-notifications-page .filter-bar-compact,
    .office-notifications-page .office-notif-desktop-list {
        display: none !important;
    }
    /* Show Mobile View App Bar, Filter & Cards */
    .office-notifications-page .mv-bookings-header {
        display: block !important;
        margin-bottom: 12px;
    }
    .office-notifications-page .cards-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
    }
}
</style>

<script>
function toggleMobileFilter() {
    var p = document.getElementById('mvFilterPanel');
    var b = document.getElementById('mvFilterToggleBtn');
    if (p) {
        var isHidden = p.style.display === 'none' || p.style.display === '';
        p.style.display = isHidden ? 'block' : 'none';
        if (b) b.classList.toggle('active', isHidden);
    }
}

function toggleCardActions(btn, e) {
    if (window.OfficeActionMenu && typeof window.OfficeActionMenu.toggle === 'function') {
        window.OfficeActionMenu.toggle(btn, e);
        return;
    }
    if (e) e.stopPropagation();
    var wrap = btn.closest('.action-dropdown-wrap');
    if (!wrap) return;
    var menu = wrap.querySelector('.action-dropdown-menu');
    if (!menu) return;
    var isOpen = menu.classList.contains('open') || menu.style.display === 'block';
    document.querySelectorAll('.action-dropdown-menu.open, .action-dropdown-menu[style*="display: block"]').forEach(function(m) {
        m.classList.remove('open');
        m.style.display = 'none';
    });
    if (!isOpen) {
        menu.classList.add('open');
        menu.style.display = 'block';
    }
}

async function markPageNotifRead(id, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    try {
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var res = await fetch('<?= url('/office/notifications/') ?>' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        var data = await res.json();
        if (data.success) {
            // Update Desktop Card
            var dCard = document.getElementById('officeNotifCard_' + id);
            if (dCard) {
                dCard.classList.remove('is-unread');
                dCard.style.borderColor = 'rgba(22, 68, 59, 0.08)';
                var bWrap = dCard.querySelector('.badge');
                if (bWrap) bWrap.remove();
                var btn = dCard.querySelector('button');
                if (btn) {
                    btn.outerHTML = '<span style="color: #72827A; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.5rem;"><i class="fa-solid fa-check-double" style="color: #218765;"></i> Read</span>';
                }
            }

            // Update Mobile Card
            var mCard = document.getElementById('officeNotifMobileCard_' + id);
            if (mCard) {
                mCard.classList.remove('is-unread');
                var pill = mCard.querySelector('.cust-toggle-pill');
                if (pill) {
                    pill.className = 'cust-toggle-pill completed';
                    pill.innerHTML = '<span class="toggle-pulse-dot completed"></span><span class="toggle-label-text">Read</span>';
                }
                var editBtn = mCard.querySelector('.action-dropdown-item.edit');
                if (editBtn) {
                    editBtn.outerHTML = '<div class="action-dropdown-item" style="opacity: 0.6; cursor: default;"><i class="fa-solid fa-check-double" style="color: #218765;"></i> <span>Already Read</span></div>';
                }
            }

            if (typeof updateGlobalNotifCount === 'function') {
                updateGlobalNotifCount(data.unread_count);
            }
            var kpi = document.getElementById('kpiUnreadVal');
            if (kpi) kpi.textContent = data.unread_count;
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Notification marked as read', 'success');
            }
        }
    } catch (err) {
        console.error('Error marking notification read:', err);
    }
}

async function markAllNotificationsPageRead() {
    try {
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var res = await fetch('<?= url('/office/notifications/mark-all-read') ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        var data = await res.json();
        if (data.success) {
            // Update Desktop Cards
            document.querySelectorAll('.office-notif-card.is-unread').forEach(function(card) {
                card.classList.remove('is-unread');
                card.style.borderColor = 'rgba(22, 68, 59, 0.08)';
                var bWrap = card.querySelector('.badge');
                if (bWrap) bWrap.remove();
                var btn = card.querySelector('button');
                if (btn) {
                    btn.outerHTML = '<span style="color: #72827A; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.5rem;"><i class="fa-solid fa-check-double" style="color: #218765;"></i> Read</span>';
                }
            });

            // Update Mobile Cards
            document.querySelectorAll('.mv-booking-card-wrapper.is-unread').forEach(function(mCard) {
                mCard.classList.remove('is-unread');
                var pill = mCard.querySelector('.cust-toggle-pill');
                if (pill) {
                    pill.className = 'cust-toggle-pill completed';
                    pill.innerHTML = '<span class="toggle-pulse-dot completed"></span><span class="toggle-label-text">Read</span>';
                }
                var editBtn = mCard.querySelector('.action-dropdown-item.edit');
                if (editBtn) {
                    editBtn.outerHTML = '<div class="action-dropdown-item" style="opacity: 0.6; cursor: default;"><i class="fa-solid fa-check-double" style="color: #218765;"></i> <span>Already Read</span></div>';
                }
            });

            if (typeof updateGlobalNotifCount === 'function') {
                updateGlobalNotifCount(0);
            }
            var kpi = document.getElementById('kpiUnreadVal');
            if (kpi) kpi.textContent = '0';
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('All notifications marked as read', 'success');
            }
        }
    } catch (err) {
        console.error('Error marking all notifications read:', err);
    }
}
</script>
