<?php
/**
 * Driver Trip History View — Dedicated Driver Portal
 * Exact Visual, Structural & Interactive Match of Owner Dashboard Customer Directory (app/Views/office/customers/index.php)
 */

$search         = $search ?? '';
$selectedStatus = $selectedStatus ?? $status ?? '';
$currStatus     = strtolower($selectedStatus);
$totRec         = (int)($totalCustomers ?? $totalAll ?? $totalHistoryAll ?? $total ?? count($history ?? $trips ?? $customers ?? []));
$currP          = (int)($currentPage ?? $page ?? 1);
$pSize          = (int)($perPage ?? 10);
$from           = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to             = min($currP * $pSize, $totRec);

$buildPageUrl = function(int $p) use ($search, $selectedStatus, $pSize) {
    $q = [];
    if (!empty($search)) $q['search'] = $search;
    if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
    if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
    $q['page'] = $p;
    return url('/driver/history?' . http_build_query($q));
};
?>

<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Exact Customer Directory Architecture) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('sidebar.customers', 'Customer Directory'),
            'description' => __('nav.customers', 'Verified guest profiles, corporate client accounts, contact history, and reservation statistics.'),
            'image'       => 'header-customers.jpg',
            'badge'       => 'Verified Clients',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => __('sidebar.trip_history', 'Trip History'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-calendar-check" style="margin-right:6px;"></i><span>Active Trips</span></a>' .
                             '<a href="' . url('/driver/dashboard') . '" class="btn btn-outline" style="margin-left:8px; border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Dashboard</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid" id="customersKpiGrid">
        <!-- Card 1: Total Clients -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all registered customers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_clients', 'Total Clients')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totRec) ?>"><?= number_format($totalAll ?? $totRec) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered guest accounts')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active Profiles -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'active' || ($selectedStatus ?? '') === 'completed' ? 'is-selected' : '' ?>" data-status="active" role="button" tabindex="0" title="Click to filter verified active customers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.active_profiles', 'Active Profiles')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalActive ?? $totalCompleted ?? 0) ?>"><?= number_format($totalActive ?? $totalCompleted ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'Verified & in good standing')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Booking Guests -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'with_bookings' ? 'is-selected' : '' ?>" data-status="with_bookings" role="button" tabindex="0" title="Click to filter active travelers with reservations">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('table.bookings', 'Active Travelers')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalWithBookings ?? $totalDistanceKm ?? 0) ?>"><?= number_format($totalWithBookings ?? $totalDistanceKm ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Guests with past reservations')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Inactive Accounts -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'inactive' || ($selectedStatus ?? '') === 'cancelled' ? 'is-selected' : '' ?>" data-status="inactive" role="button" tabindex="0" title="Click to filter dormant inactive clients">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.inactive_accounts', 'Inactive Accounts')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalInactive ?? $totalCancelled ?? 0) ?>"><?= number_format($totalInactive ?? $totalCancelled ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.archived', 'Dormant or paused clients')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/driver/history') ?>" class="filter-bar-compact" id="customerFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search customers by code, name, phone, email, city..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>><?= e(__('filter.all_status', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' || ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>><?= e(__('status.active', 'Active')) ?></option>
            <option value="with_bookings" <?= ($selectedStatus ?? '') === 'with_bookings' ? 'selected' : '' ?>><?= e(__('table.bookings', 'Active Travelers')) ?></option>
            <option value="inactive" <?= ($selectedStatus ?? '') === 'inactive' || ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>><?= e(__('status.inactive', 'Inactive')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="<?= e(__('btn.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title"><?= e(__('sidebar.customers', 'Customers')) ?></h2>
            </div>
            <a href="<?= url('/driver/trips') ?>" class="mv-add-btn">
                <i class="fa-solid fa-calendar-check"></i>
                <span><?= e(__('sidebar.my_trips', 'Trips')) ?></span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/driver/history') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search by name, code, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/driver/history' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="customersMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span><?= e(__('filter.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? $totRec) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' || $currStatus === 'completed' ? 'active' : '' ?>" data-status="active" data-no-ajax="true">
                <span><?= e(__('status.active', 'Active')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? $totalCompleted ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('with_bookings') ?>" class="mv-pill <?= $currStatus === 'with_bookings' ? 'active' : '' ?>" data-status="with_bookings" data-no-ajax="true">
                <span><?= e(__('table.bookings', 'Travelers')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalWithBookings ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' || $currStatus === 'cancelled' ? 'active' : '' ?>" data-status="inactive" data-no-ajax="true">
                <span><?= e(__('status.inactive', 'Inactive')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalInactive ?? $totalCancelled ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Customers Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="customersSection">
        <div class="section-header">
            <h3 class="section-title"><?= e(__('sidebar.customers', 'All Customers')) ?></h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('table.total_records', 'Total Records:')) ?></span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Strict 8-Column Executive Layout matching Customer Directory) -->
        <div class="modern-table-container">
            <table class="modern-table" id="customersTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th class="text-center" style="width: 80px;"><?= e(__('table.profile', 'Profile')) ?></th>
                        <th style="min-width: 220px;"><?= e(__('table.client_details', 'Client Details')) ?></th>
                        <th style="min-width: 160px;"><?= e(__('table.mobile_no', 'Mobile No.')) ?></th>
                        <th style="min-width: 200px;"><?= e(__('table.address', 'Address')) ?></th>
                        <th class="text-center" style="width: 100px;"><?= e(__('sidebar.bookings', 'Bookings')) ?></th>
                        <th class="text-center" style="width: 110px;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 75px;"><?= e(__('table.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <?php foreach (($history ?? []) as $i => $c):
                        $serial = (($currP - 1) * $pSize) + $i + 1;
                        $st = strtolower($c['status'] ?? 'completed');
                        $isActive = in_array($st, ['completed', 'active']);
                        $statusClass = $isActive ? 'completed' : 'cancelled';
                        $statusText = match($st) {
                            'completed'       => 'Completed',
                            'cancelled'       => 'Cancelled',
                            'driver_accepted' => 'Accepted',
                            'trip_started'    => 'In Transit',
                            'assigned'        => 'Assigned',
                            default           => ucfirst($st)
                        };
                        $custName = $c['customer_name'] ?? 'VIP Guest';
                        $initial = strtoupper(mb_substr($custName, 0, 1));
                        $avatarUrl = !empty($c['customer_avatar']) ? customer_avatar_url($c['customer_avatar']) : null;
                        $bookingCode = $c['booking_code'] ?? '';
                        $dispatchCode = $c['dispatch_code'] ?? '';
                        $phone = $c['customer_phone'] ?? '';
                        $email = $c['customer_email'] ?? '';
                        $pickupAddress = $c['pickup_address'] ?? 'Pickup point';
                        $dropAddress = $c['drop_address'] ?? 'Drop point';
                        $vehicleNumber = $c['vehicle_number'] ?? 'Fleet';
                        $makeModel = $c['make_model'] ?? 'Vehicle';
                    ?>
                    <tr>
                        <!-- 1. Serial Number (#) -->
                        <td class="text-center">
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>

                        <!-- 2. Profile Image / Monogram -->
                        <td class="text-center">
                            <div class="cust-table-avatar-wrap">
                                <?php if (!empty($avatarUrl)): ?>
                                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($custName) ?>" class="cust-table-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="cust-table-monogram" style="display: none;"><?= e($initial) ?></div>
                                <?php else: ?>
                                    <div class="cust-table-monogram"><?= e($initial) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Client Details (Salutation, Full Name, and Email - Code Removed) -->
                        <td>
                            <div class="cust-client-details-cell">
                                <div class="cust-name-row">
                                    <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="cust-full-name" title="View customer profile">
                                        <?= e($custName) ?>
                                    </a>
                                    <span class="cust-code-pill"><?= e($bookingCode) ?></span>
                                </div>
                                <div class="cust-email-row">
                                    <i class="fa-regular fa-envelope cust-email-icon"></i>
                                    <?php if (!empty($email)): ?>
                                        <a href="mailto:<?= e($email) ?>" class="cust-email-link"><?= e($email) ?></a>
                                    <?php else: ?>
                                        <span class="cust-no-email"><?= e($dispatchCode) ?> &bull; <?= format_date($c['start_date'] ?? date('Y-m-d')) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- 4. Mobile No. (Primary + Optional Alternate) -->
                        <td>
                            <div class="cust-phone-cell">
                                <?php if (!empty($phone)): ?>
                                    <a href="tel:<?= e($phone) ?>" class="cust-primary-phone" title="Call customer">
                                        <i class="fa-solid fa-phone cust-phone-icon"></i>
                                        <span><?= e($phone) ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="cust-no-email">No phone on record</span>
                                <?php endif; ?>
                                <?php if (!empty($dispatchCode)): ?>
                                    <div class="cust-alt-phone" title="Dispatch Reference">
                                        <i class="fa-solid fa-hashtag"></i> <?= e($dispatchCode) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 5. Address (Street, City, State, Pincode) -->
                        <td>
                            <div class="cust-address-cell">
                                <div class="cust-address-line" title="<?= e($pickupAddress) ?>">
                                    <i class="fa-solid fa-location-dot cust-address-icon"></i>
                                    <span><?= e($pickupAddress) ?></span>
                                </div>
                                <div class="cust-address-sub" title="<?= e($dropAddress) ?>">
                                    <i class="fa-solid fa-flag-checkered" style="font-size:0.72rem; color:#64748B;"></i>
                                    <span><?= e($dropAddress) ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- 6. Bookings Count -->
                        <td class="text-center">
                            <div class="cust-bookings-cell">
                                <span class="cust-bookings-num" style="font-size:0.95rem;"><?= e($vehicleNumber) ?></span>
                                <span class="cust-bookings-lbl"><?= e($makeModel) ?></span>
                            </div>
                        </td>

                        <!-- 7. Status Toggle Switch -->
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="custToggleWrap-<?= (int)$c['id'] ?>">
                                <button type="button" 
                                        class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$c['id'] ?>"
                                        data-name="<?= addslashes(e($custName)) ?>"
                                        title="Status: <?= $statusText ?>">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb">
                                            <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                        </span>
                                    </span>
                                </button>
                                <span class="cust-toggle-pill <?= $statusClass ?>">
                                    <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                    <span class="toggle-label-text"><?= $statusText ?></span>
                                </span>
                            </div>
                        </td>

                        <!-- 8. Actions Menu (View, Edit, Dispatch, Delete) -->
                        <td class="text-center">
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="action-btn-table view" title="View Profile & Bookings">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (!empty($phone)): ?>
                                        <a href="tel:<?= e($phone) ?>" class="action-btn-table edit" title="Call Customer">
                                            <i class="fa-solid fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No customers found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Booking Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="customersCards">
            <?php foreach (($history ?? []) as $c):
                $st = strtolower($c['status'] ?? 'completed');
                $isActive = in_array($st, ['completed', 'active']);
                $statusClass = $isActive ? 'active' : 'inactive';
                $statusText = match($st) {
                    'completed'       => 'Completed',
                    'cancelled'       => 'Cancelled',
                    'driver_accepted' => 'Accepted',
                    'trip_started'    => 'In Transit',
                    'assigned'        => 'Assigned',
                    default           => ucfirst($st)
                };
                $custName = $c['customer_name'] ?? 'VIP Guest';
                $initial = strtoupper(mb_substr($custName, 0, 1));
                $avatarUrl = !empty($c['customer_avatar']) ? customer_avatar_url($c['customer_avatar']) : null;
                $bookingCode = $c['booking_code'] ?? '';
                $phone = $c['customer_phone'] ?? '';
                $email = $c['customer_email'] ?? '';
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="mv-booking-card">
                    <!-- Left: Avatar Image or Monogram -->
                    <div class="mv-thumb-wrap">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= e($avatarUrl) ?>" alt="<?= e($custName) ?>" class="mv-thumb-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="mv-thumb-avatar" style="display: none;">
                                <?= e($initial) ?>
                            </div>
                        <?php else: ?>
                            <div class="mv-thumb-avatar">
                                <?= e($initial) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Middle: Name, Phone, Email, Address -->
                    <div class="mv-details">
                        <div class="mv-tour-title" title="<?= e($custName) ?>">
                            <?= e($custName) ?>
                        </div>
                        <?php if (!empty($phone)): ?>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e($phone) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-ticket"></i>
                            <span><?= e($bookingCode) ?> &bull; <?= e($c['dispatch_code'] ?? '') ?></span>
                        </div>
                        <div class="mv-passenger" style="color: #64748B;">
                            <i class="fa-solid fa-location-dot" style="color: #EF4444;"></i>
                            <span><?= e($c['pickup_address'] ?? '') ?> &rarr; <?= e($c['drop_address'] ?? '') ?></span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-car"></i>
                            <span><?= e($c['vehicle_number'] ?? '') ?> &bull; <?= e($c['make_model'] ?? 'Fleet') ?></span>
                        </div>
                    </div>

                    <!-- Right: Status toggle top, Total bookings bottom -->
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.stopPropagation()">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$c['id'] ?>"
                                    title="Toggle status">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb"></span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price"><?= format_date($c['start_date'] ?? date('Y-m-d')) ?></div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="action-btn-table view" title="View Profile">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if (!empty($phone)): ?>
                            <a href="tel:<?= e($phone) ?>" class="action-btn-table edit" title="Call">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No customers found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <div id="customersPaginationWrapper">
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> customer(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page" data-page="<?= max(1, $currP - 1) ?>" data-no-ajax="true">&#10094;</a>
                <?php
                    $rangeStart = max(1, $currP - 1);
                    $rangeEnd   = min($totalPages, $rangeStart + 2);
                    if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                        $rangeStart = max(1, $rangeEnd - 2);
                    }
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                ?>
                    <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>" data-page="<?= $i ?>" data-no-ajax="true"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($rangeEnd < $totalPages): ?>
                    <span class="dots">...</span>
                    <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>" data-page="<?= $totalPages ?>" data-no-ajax="true"><?= $totalPages ?></a>
                <?php endif; ?>
                <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page" data-page="<?= min($totalPages, $currP + 1) ?>" data-no-ajax="true">&#10095;</a>
            </div>
        </div>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Complete Customer Directory Live AJAX Filter Engine (Without Page Reload) */
    /* ========================================================================= */
    (function initCustomerAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/driver/history') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterCustomersAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) {
                abortCtrl.abort();
            }
            abortCtrl = new AbortController();

            const section = document.getElementById('customersSection');
            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'block' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = searchVal ? 'block' : 'none';

            fetch(targetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml'
                },
                signal: abortCtrl.signal
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update KPI grid
                const newKpis = doc.getElementById('customersKpiGrid');
                const currKpis = document.getElementById('customersKpiGrid');
                if (newKpis && currKpis) {
                    currKpis.innerHTML = newKpis.innerHTML;
                }

                // 2. Update Total Records Pill
                const newPill = doc.getElementById('totalRecordsPill');
                const currPill = document.getElementById('totalRecordsPill');
                if (newPill && currPill) {
                    currPill.textContent = newPill.textContent;
                }

                // 3. Update Desktop Table Body
                const newTbody = doc.getElementById('customersTableBody');
                const currTbody = document.getElementById('customersTableBody');
                if (newTbody && currTbody) {
                    currTbody.innerHTML = newTbody.innerHTML;
                }

                // 4. Update Mobile Cards
                const newCards = doc.getElementById('customersCards');
                const currCards = document.getElementById('customersCards');
                if (newCards && currCards) {
                    currCards.innerHTML = newCards.innerHTML;
                }

                // 5. Update Pagination Wrapper
                const newPagin = doc.getElementById('customersPaginationWrapper');
                const currPagin = document.getElementById('customersPaginationWrapper');
                if (newPagin && currPagin) {
                    currPagin.innerHTML = newPagin.innerHTML;
                }

                // 6. Update Clear Filters Button
                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                // 7. Update Mobile Status Pills
                const newScroll = doc.getElementById('customersMvStatusScroll');
                const currScroll = document.getElementById('customersMvStatusScroll');
                if (newScroll && currScroll) {
                    currScroll.innerHTML = newScroll.innerHTML;
                }

                // 8. Sync URL bar without full page reload
                window.history.replaceState({ path: targetUrl }, '', targetUrl);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('AJAX Customer filter error:', err);
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        // Search Input Debouncing
        const searchInput = document.getElementById('searchInput');
        const mvSearchInput = document.getElementById('mvSearchInput');

        function onSearchInput(val) {
            if (searchInput && searchInput.value !== val) searchInput.value = val;
            if (mvSearchInput && mvSearchInput.value !== val) mvSearchInput.value = val;

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterCustomersAjax(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener('input', e => onSearchInput(e.target.value));
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        if (mvSearchInput) {
            mvSearchInput.addEventListener('input', e => onSearchInput(e.target.value));
            mvSearchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        // Clear Search buttons
        function clearSearch() {
            if (searchInput) searchInput.value = '';
            if (mvSearchInput) mvSearchInput.value = '';
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
            if (searchClearBtn) searchClearBtn.style.display = 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = 'none';
            filterCustomersAjax(1);
        }

        const searchClearBtn = document.getElementById('searchClearBtn');
        if (searchClearBtn) searchClearBtn.addEventListener('click', clearSearch);
        const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
        if (mvSearchClearBtn) mvSearchClearBtn.addEventListener('click', clearSearch);

        // Status Select Dropdown
        const statusInput = document.getElementById('statusInput');
        if (statusInput) {
            statusInput.addEventListener('change', () => filterCustomersAjax(1));
        }

        // Per Page Dropdown (Desktop & Mobile)
        const perPageInput = document.getElementById('perPageInput');
        const mvPerPageSelect = document.getElementById('mvPerPageSelect');
        if (perPageInput) {
            perPageInput.addEventListener('change', () => {
                if (mvPerPageSelect) mvPerPageSelect.value = perPageInput.value;
                filterCustomersAjax(1);
            });
        }
        if (mvPerPageSelect) {
            mvPerPageSelect.addEventListener('change', () => {
                if (perPageInput) perPageInput.value = mvPerPageSelect.value;
                filterCustomersAjax(1);
            });
        }

        // Apply Button
        const filterApplyBtn = document.getElementById('filterApplyBtn');
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => filterCustomersAjax(1));
        }

        // Refresh Button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => filterCustomersAjax(1));
        }

        // Global Event Delegation for Dynamic Elements (KPI Cards, Clear Button, Pagination, Status Pills)
        document.addEventListener('click', function(e) {
            // 1. KPI Card Click
            const kpiCard = e.target.closest('#customersKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                const st = kpiCard.getAttribute('data-status') || '';
                if (statusInput) statusInput.value = st;
                filterCustomersAjax(1);
                return;
            }

            // 2. Clear Filters Button
            const clearBtn = e.target.closest('#filterClearBtn');
            if (clearBtn) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                filterCustomersAjax(1);
                return;
            }

            // 3. Mobile Status Pills
            const statusPill = e.target.closest('#customersMvStatusScroll .mv-pill');
            if (statusPill) {
                e.preventDefault();
                const st = statusPill.getAttribute('data-status') || '';
                if (statusInput) statusInput.value = st;
                filterCustomersAjax(1);
                return;
            }

            // 4. Pagination Links
            const paginLink = e.target.closest('#customersPaginationWrapper .pagination a[data-page]');
            if (paginLink) {
                e.preventDefault();
                if (paginLink.classList.contains('disabled') || paginLink.classList.contains('active')) return;
                const p = parseInt(paginLink.getAttribute('data-page'), 10) || 1;
                filterCustomersAjax(p);
                return;
            }
        });
    })();

    /* Toggle Mobile Filter Drawer */
    function toggleMobileFilter() {
        const panel = document.getElementById('mvFilterPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    /* Desktop Actions Popup */
    function toggleActions(btn, event) {
        event.stopPropagation();
        const popup = btn.nextElementSibling;
        const allPopups = document.querySelectorAll('.action-btns-popup.show');
        allPopups.forEach(p => {
            if (p !== popup) p.classList.remove('show');
        });
        if (popup) {
            popup.classList.toggle('show');
        }
    }

    /* Mobile Cards Action Popup */
    function toggleCardActions(btn, event) {
        event.stopPropagation();
        const popup = btn.nextElementSibling;
        const allPopups = document.querySelectorAll('.card-btns-popup.show');
        allPopups.forEach(p => {
            if (p !== popup) p.classList.remove('show');
        });
        if (popup) {
            popup.classList.toggle('show');
        }
    }

    // Close popups on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.show, .card-btns-popup.show').forEach(p => {
                p.classList.remove('show');
            });
        }
    });
</script>

<style>
/* ========================================================================= */
/* Exact CSS Styles Directly from app/Views/office/customers/index.php       */
/* ========================================================================= */

.bookings-page-view {
    padding: 1.5rem;
    max-width: 1600px;
    margin: 0 auto;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
}

/* 1. Sleek Modern Table Container & Gradient Thead */
.modern-table-container {
    display: block !important;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    padding: 16px 20px;
    overflow-x: auto;
    overflow-y: visible;
    border: 1px solid rgba(22, 68, 59, 0.08);
    margin-bottom: 24px;
    width: 100%;
    box-sizing: border-box;
}

.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
    min-width: 100%;
}

.modern-table thead {
    background: linear-gradient(135deg, #176B5B, #16443B) !important;
}

.modern-table thead tr th:first-child {
    border-top-left-radius: 10px !important;
    border-bottom-left-radius: 10px !important;
}

.modern-table thead tr th:last-child {
    border-top-right-radius: 10px !important;
    border-bottom-right-radius: 10px !important;
}

/* Ensure ALL Table Headers are Crisp, White, and 100% Visible */
.modern-table th,
.modern-table thead th {
    padding: 14px 14px !important;
    text-align: left;
    color: #FFFFFF !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    border: none !important;
    white-space: nowrap !important;
    background: transparent !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25) !important;
}

.modern-table th.text-center,
.modern-table thead th.text-center {
    text-align: center !important;
}

.modern-table tbody tr {
    transition: all 0.25s ease;
    border-radius: 8px;
}

.modern-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(23, 107, 91, 0.04), rgba(214, 183, 122, 0.05));
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(22, 68, 59, 0.08);
}

.modern-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    vertical-align: middle;
    font-size: 13.5px;
    color: #334155;
}

/* 2. Top KPI Cards Grid */
.booking-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.booking-kpi-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 18px 20px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                border-color 0.22s ease,
                background-color 0.22s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.booking-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(15, 23, 42, 0.09), 0 4px 10px -2px rgba(15, 23, 42, 0.04);
    border-color: #CBD5E1;
}

.booking-kpi-card:active {
    transform: scale(0.985);
}

.booking-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.booking-kpi-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.booking-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-teal { background: #ECFDF5; color: #059669; }
.icon-emerald { background: #E0F2FE; color: #0284C7; }
.icon-amber { background: #FEF3C7; color: #D97706; }
.icon-navy { background: #F1F5F9; color: #475569; }

.booking-kpi-val {
    font-size: 26px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
    margin: 8px 0 4px;
    letter-spacing: -0.02em;
    transition: color 0.2s ease;
}

.booking-kpi-sub {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #64748B;
}

.booking-kpi-card .kpi-filter-tag {
    margin-left: auto;
    font-size: 11px;
    color: #94A3B8;
    opacity: 0;
    transform: translateX(-4px);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.booking-kpi-card:hover .kpi-filter-tag {
    opacity: 1;
    transform: translateX(0);
    color: #0284c7;
}

.booking-kpi-card[data-status=""].is-selected {
    border-color: #0D9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.16), 0 10px 24px rgba(13, 148, 136, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #F0FDFA 100%);
}

.booking-kpi-card[data-status="active"].is-selected {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16), 0 10px 24px rgba(16, 185, 129, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #ECFDF5 100%);
}

.booking-kpi-card[data-status="with_bookings"].is-selected {
    border-color: #F59E0B;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.16), 0 10px 24px rgba(245, 158, 11, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFBEB 100%);
}

.booking-kpi-card[data-status="inactive"].is-selected {
    border-color: #64748B;
    box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.16), 0 10px 24px rgba(100, 116, 139, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10B981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

/* 3. Compact Filter Bar */
.filter-bar-compact {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    gap: 0.75rem;
    flex-wrap: wrap;
}

.fbc-search {
    position: relative;
    flex: 1;
    min-width: 240px;
    display: flex;
    align-items: center;
}

.fbc-search-icon {
    position: absolute;
    left: 12px;
    color: #94A3B8;
    font-size: 0.9rem;
}

.fbc-search input {
    width: 100%;
    padding: 0.55rem 2.25rem 0.55rem 2.25rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    outline: none;
    color: #1E293B;
    transition: all 0.15s ease;
}

.fbc-search input:focus {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.fbc-search-clear {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border-radius: 50%;
    transition: color 0.15s ease, background-color 0.15s ease;
}

.fbc-search-clear:hover {
    color: #0F172A;
    background: rgba(0, 0, 0, 0.06);
}

.filter-bar-compact select {
    padding: 0.55rem 1rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    color: #1E293B;
    background-color: #fff;
    cursor: pointer;
    outline: none;
}

.filter-bar-compact select:focus {
    border-color: #0EA5E9;
}

.fbc-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fbc-btn {
    padding: 0.55rem 1rem;
    border-radius: 8px;
    border: 1px solid #CBD5E1;
    background: #F8FAFC;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.fbc-btn:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.fbc-btn-clear {
    background: #FEE2E2;
    color: #DC2626;
    border-color: #FECACA;
}

.fbc-btn-clear:hover {
    background: #FCA5A5;
    border-color: #F87171;
}

/* 4. Customer Directory Cells */
.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    font-size: 0.88rem;
    padding: 3px 6px;
}

.cust-table-avatar-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 44px;
    height: 44px;
}

.cust-table-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.18);
    background: #E8F4F0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cust-table-monogram {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    text-transform: uppercase;
}

.cust-client-details-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cust-name-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cust-full-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0C2B52;
    text-decoration: none;
    transition: color 0.15s ease;
}

.cust-full-name:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-code-pill {
    display: inline-flex;
    align-items: center;
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 1px 7px;
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.cust-email-row {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #64748B;
}

.cust-email-icon {
    font-size: 0.78rem;
    color: #94A3B8;
}

.cust-email-link {
    color: #475569;
    text-decoration: none;
    transition: color 0.15s ease;
}

.cust-email-link:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-no-email {
    color: #94A3B8;
    font-style: italic;
    font-size: 0.78rem;
}

.cust-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.cust-primary-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    transition: color 0.15s ease;
}

.cust-primary-phone:hover {
    color: #176B5B;
}

.cust-phone-icon {
    font-size: 0.76rem;
    color: #176B5B;
}

.cust-alt-phone {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #334155;
    max-width: 250px;
}

.cust-address-line {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    font-weight: 600;
    color: #0F172A;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}

.cust-address-icon {
    font-size: 0.78rem;
    color: #EF4444;
    margin-top: 3px;
    flex-shrink: 0;
}

.cust-address-sub {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.3;
}

.cust-bookings-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cust-bookings-num {
    font-size: 1.05rem;
    font-weight: 800;
    color: #16443B;
    line-height: 1.1;
}

.cust-bookings-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* 5. Status Toggle Switch Component */
.cust-toggle-cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.cust-status-toggle {
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease;
}

.cust-status-toggle:hover {
    transform: scale(1.06);
}

.toggle-slider-track {
    width: 44px;
    height: 24px;
    border-radius: 9999px;
    background: #CBD5E1;
    position: relative;
    display: block;
    transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}

.cust-status-toggle.is-active .toggle-slider-track {
    background: #10B981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}

.toggle-slider-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    position: absolute;
    left: 3px;
    top: 3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}

.cust-status-toggle.sm .toggle-slider-track {
    width: 36px;
    height: 20px;
}

.cust-status-toggle.sm .toggle-slider-thumb {
    width: 14px;
    height: 14px;
    left: 3px;
    top: 3px;
}

.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}

.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.toggle-pulse-dot.completed {
    background: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}

/* 6. Action Popup Menu */
.table-action-buttons {
    position: relative;
    display: flex;
    justify-content: center;
}

.action-toggle-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    color: #475569;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-toggle-btn:hover {
    background: #E2E8F0;
}

.action-btns-popup {
    position: absolute;
    right: 0;
    top: 100%;
    background: #ffffff;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    display: none;
    flex-direction: row;
    gap: 4px;
    padding: 6px;
    z-index: 50;
}

.action-btns-popup.show {
    display: flex;
}

.action-btn-table {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.8rem;
    color: #475569;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}

.action-btn-table:hover {
    background: #0EA5E9;
    color: #fff;
    border-color: #0EA5E9;
}

/* 7. Modern Pagination Wrapper */
.pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.pagination-info {
    font-size: 0.85rem;
    color: #64748B;
}

.pagination-info span {
    font-weight: 700;
    color: #0F172A;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    border: 1px solid transparent;
}

.pagination a:hover:not(.disabled) {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.pagination a.active {
    background: #0EA5E9;
    color: #fff;
    border-color: #0EA5E9;
}

.pagination a.disabled {
    color: #CBD5E1;
    pointer-events: none;
}

.pagination .dots {
    padding: 0 4px;
    color: #94A3B8;
}

/* 8. Empty State */
.no-data-message {
    padding: 3rem 1.5rem;
    text-align: center;
}

.no-data-icon {
    font-size: 2.5rem;
    color: #94A3B8;
    margin-bottom: 0.75rem;
}

/* 9. Mobile Responsive Layout */
.mv-bookings-header, .cards-grid {
    display: none;
}

@media (max-width: 991px) {
    .booking-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .bookings-page-view {
        padding: 0.75rem;
    }

    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container,
    .section-header {
        display: none !important;
    }

    .mv-bookings-header {
        display: block;
        margin-bottom: 1rem;
    }

    .mv-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .mv-top-bar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mv-back-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1E293B;
        text-decoration: none;
    }

    .mv-page-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
        color: #0F172A;
    }

    .mv-add-btn {
        background: #0EA5E9;
        color: #fff;
        padding: 0.45rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mv-search-form {
        display: flex;
        gap: 6px;
        margin-bottom: 0.75rem;
    }

    .mv-search-input-box {
        position: relative;
        flex: 1;
    }

    .mv-search-ico {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 0.85rem;
    }

    .mv-search-input-box input {
        width: 100%;
        padding: 0.5rem 2rem 0.5rem 2rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .mv-search-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94A3B8;
    }

    .mv-filter-toggle {
        padding: 0.5rem 0.75rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
    }

    .mv-filter-panel {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .mv-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mv-filter-row select {
        padding: 0.35rem 0.65rem;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
    }

    .mv-status-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .mv-status-scroll::-webkit-scrollbar {
        display: none;
    }

    .mv-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .mv-pill.active {
        background: #0EA5E9;
        color: #fff;
        border-color: #0EA5E9;
    }

    .mv-pill-cnt {
        background: rgba(0,0,0,0.06);
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
    }

    .mv-pill.active .mv-pill-cnt {
        background: rgba(255,255,255,0.25);
    }

    .cards-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.75rem;
    }

    .mv-booking-card-wrapper {
        position: relative;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .mv-booking-card {
        display: flex;
        align-items: flex-start;
        padding: 0.85rem;
        text-decoration: none;
        gap: 10px;
    }

    .mv-thumb-wrap {
        flex-shrink: 0;
    }

    .mv-thumb-avatar-img, .mv-thumb-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mv-thumb-avatar {
        background: #E2E8F0;
        color: #334155;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mv-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .mv-tour-title {
        font-weight: 700;
        color: #0F172A;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-date-duration, .mv-passenger, .mv-vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-right-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }

    .mv-status-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .mv-toggle-label-text {
        font-size: 0.72rem;
        font-weight: 700;
    }

    .mv-toggle-label-text.active { color: #059669; }
    .mv-toggle-label-text.inactive { color: #DC2626; }

    .mv-price {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .mv-card-actions {
        position: absolute;
        bottom: 8px;
        right: 8px;
    }

    .mv-action-dots-btn {
        background: transparent;
        border: none;
        color: #94A3B8;
        font-size: 0.95rem;
        cursor: pointer;
        padding: 4px;
    }

    .card-btns-popup {
        position: absolute;
        bottom: 100%;
        right: 0;
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        gap: 4px;
        padding: 6px;
        z-index: 50;
    }

    .card-btns-popup.show {
        display: flex;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
        align-items: center;
    }
}
</style>
