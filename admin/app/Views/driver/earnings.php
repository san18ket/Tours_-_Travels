<?php
/**
 * DRIVER TRIP EARNINGS VIEW — Dedicated Driver Portal
 * Professional Driver Compensation Ledger aligned with Owner/Office Portal Design System
 * Features mobile-first executive UI, search bar, collapsible filter drawer, status pills, and AJAX live filtering.
 */

$search    = $search ?? '';
$status    = $status ?? '';
$currStatus = strtolower($status);
$fromDate  = $fromDate ?? '';
$toDate    = $toDate ?? '';
$totRec    = (int)($total ?? count($trips ?? []));
$currP     = (int)($page ?? 1);
$pSize     = (int)($perPage ?? 10);
$from      = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to        = min($currP * $pSize, $totRec);

$totalEarned     = (float)($financialSummary['total_earned'] ?? 0);
$totalPaid       = (float)($financialSummary['total_paid'] ?? 0);
$totalPending    = (float)($financialSummary['total_pending'] ?? 0);
$thisMonthEarned = (float)($financialSummary['this_month_earned'] ?? 0);

$totalEarningsAll = (int)($totalEarningsAll ?? $totRec);
$totalDue         = (int)($totalDue ?? 0);
$totalPartial     = (int)($totalPartial ?? 0);
$totalPaidCount   = (int)($totalPaidCount ?? $totalPaid ?? 0);

$buildFilterUrl = function(array $overrides = []) use ($search, $status, $fromDate, $toDate, $pSize) {
    $q = [];
    $s  = array_key_exists('search', $overrides) ? $overrides['search'] : $search;
    $st = array_key_exists('status', $overrides) ? $overrides['status'] : $status;
    $fd = array_key_exists('from_date', $overrides) ? $overrides['from_date'] : $fromDate;
    $td = array_key_exists('to_date', $overrides) ? $overrides['to_date'] : $toDate;
    $pp = array_key_exists('per_page', $overrides) ? $overrides['per_page'] : $pSize;
    $pg = $overrides['page'] ?? null;

    if (!empty($s))  $q['search']    = $s;
    if (!empty($st)) $q['status']    = $st;
    if (!empty($fd)) $q['from_date'] = $fd;
    if (!empty($td)) $q['to_date']   = $td;
    if (!empty($pp) && $pp != 10)    $q['per_page'] = $pp;
    if (!empty($pg) && $pg > 1)      $q['page']     = $pg;

    return url('/driver/earnings' . (!empty($q) ? '?' . http_build_query($q) : ''));
};

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/driver/payments') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-receipt" style="margin-right:6px;"></i><span>Payment Vouchers</span></a>'
    . '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-route" style="margin-right:6px;"></i><span>Assigned Trips</span></a>'
    . '</div>';
?>

<div class="bookings-page-view driver-portal-view driver-portal-container">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'My Trip Earnings',
            'description' => 'Review your trip-specific compensation, disbursed payments, and outstanding dues for completed dispatches.',
            'badge'       => 'Driver Compensation',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Trip Earnings', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="earningsKpiGrid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1.5rem;">
        <!-- Card 1: Total Lifetime Earnings -->
        <div class="booking-kpi-card <?= empty($status) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="<?= e(__('tooltip.total_trip_fees_from_completed', 'Total trip fees from completed journeys')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Earnings</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary, #176B5B);">
                <?= $totalEarned > 0 ? format_currency($totalEarned) : '₹0.00' ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Across <?= (int)($financialSummary['completed_trips'] ?? 0) ?> completed trips</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Total Paid Amount -->
        <div class="booking-kpi-card <?= $currStatus === 'paid' ? 'is-selected' : '' ?>" data-status="paid" role="button" tabindex="0" title="<?= e(__('tooltip.total_compensation_successfull', 'Total compensation successfully disbursed to you')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #10B981;">
                <?= format_currency($totalPaid) ?>
            </div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background:#10B981; box-shadow:0 0 0 2px rgba(16,185,129,0.25);"></span>
                <span>Paid to your account</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Pending Balance Due -->
        <div class="booking-kpi-card <?= $currStatus === 'due' ? 'is-selected' : '' ?>" data-status="due" role="button" tabindex="0" title="<?= e(__('tooltip.outstanding_compensation_await', 'Outstanding compensation awaiting office settlement')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Payout</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $totalPending > 0 ? '#D97706' : '#10B981' ?>;">
                <?= format_currency($totalPending) ?>
            </div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background:<?= $totalPending > 0 ? '#D97706' : '#10B981' ?>;"></span>
                <span><?= $totalPending > 0 ? 'Awaiting office settlement' : 'All dues settled' ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: This Month -->
        <div class="booking-kpi-card" title="<?= e(__('tooltip.trip_earnings_for_current_cale', 'Trip earnings for current calendar month')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">This Month</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #0F172A;">
                <?= format_currency($thisMonthEarned) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= date('F Y') ?> earned</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/driver/earnings') ?>" class="filter-bar-compact" id="earningsFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_earnings_by_dispatch_co', 'Search earnings by dispatch code, route, vehicle, passenger...')) ?>" value="<?= e($search) ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="<?= e(__('common.clear', 'Clear search')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput" title="<?= e(__('tooltip.filter_by_driver_payment_statu', 'Filter by driver payment status')) ?>">
            <option value="" <?= empty($status) ? 'selected' : '' ?>>All Payment Statuses</option>
            <option value="due" <?= $currStatus === 'due' ? 'selected' : '' ?>>Payment Due (Pending)</option>
            <option value="partially_paid" <?= in_array($currStatus, ['partially_paid', 'partial'], true) ? 'selected' : '' ?>>Partially Paid</option>
            <option value="paid" <?= in_array($currStatus, ['paid', 'fully_paid'], true) ? 'selected' : '' ?>>Fully Paid</option>
            <option value="not_due" <?= $currStatus === 'not_due' ? 'selected' : '' ?>>Not Due</option>
        </select>

        <div class="fbc-date-wrap">
            <input type="date" name="from_date" id="fromDateInput" class="fbc-date-input" value="<?= e($fromDate) ?>" title="<?= e(__('tooltip.start_completed_date', 'Start completed date')) ?>">
        </div>

        <div class="fbc-date-wrap">
            <input type="date" name="to_date" id="toDateInput" class="fbc-date-input" value="<?= e($toDate) ?>" title="<?= e(__('tooltip.end_completed_date', 'End completed date')) ?>">
        </div>

        <select name="per_page" id="perPageInput" title="<?= e(__('common.records_per_page', 'Records per page')) ?>">
            <?php foreach ([10, 20, 30, 50] as $opt): ?>
                <option value="<?= $opt ?>" <?= $pSize == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="refreshBtn" title="<?= e(__('btn.refresh', 'Refresh')) ?>" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($status) || !empty($fromDate) || !empty($toDate)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="<?= e(__('common.reset_filters', 'Clear Filters')) ?>" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll (Exact UI match to /driver/trips) -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="<?= e(__('common.back_to_dashboard', 'Back to Dashboard')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Trip Earnings</h2>
            </div>
            <a href="<?= url('/driver/payments') ?>" class="mv-add-btn">
                <i class="fa-solid fa-receipt"></i>
                <span>Vouchers</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/driver/earnings') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_by_dispatch_route_vehic', 'Search by dispatch, route, vehicle...')) ?>" value="<?= e($search) ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="<?= e(__('common.clear', 'Clear search')) ?>">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="<?= e(__('common.filter', 'Filter options')) ?>">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvStatusSelect" style="font-weight: 600; font-size: 0.82rem; color: #475569;">Status:</label>
                    <select name="status" id="mvStatusSelect" style="font-size: 0.85rem; padding: 0.4rem 0.65rem; border: 1px solid #CBD5E1; border-radius: 6px; width: 60%;">
                        <option value="" <?= empty($status) ? 'selected' : '' ?>>All Statuses</option>
                        <option value="due" <?= $currStatus === 'due' ? 'selected' : '' ?>>Pending Due</option>
                        <option value="partially_paid" <?= in_array($currStatus, ['partially_paid', 'partial'], true) ? 'selected' : '' ?>>Partially Paid</option>
                        <option value="paid" <?= in_array($currStatus, ['paid', 'fully_paid'], true) ? 'selected' : '' ?>>Fully Paid</option>
                        <option value="not_due" <?= $currStatus === 'not_due' ? 'selected' : '' ?>>Not Due</option>
                    </select>
                </div>
                <div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvFromDateInput" style="font-weight: 600; font-size: 0.82rem; color: #475569;">From Date:</label>
                    <input type="date" name="from_date" id="mvFromDateInput" class="form-control" value="<?= e($fromDate) ?>" style="font-size: 0.85rem; padding: 0.4rem 0.65rem; width: 60%;">
                </div>
                <div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvToDateInput" style="font-weight: 600; font-size: 0.82rem; color: #475569;">To Date:</label>
                    <input type="date" name="to_date" id="mvToDateInput" class="form-control" value="<?= e($toDate) ?>" style="font-size: 0.85rem; padding: 0.4rem 0.65rem; width: 60%;">
                </div>
                <div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvPerPageSelect" style="font-weight: 600; font-size: 0.82rem; color: #475569;">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect" style="font-size: 0.85rem; padding: 0.4rem 0.65rem; border: 1px solid #CBD5E1; border-radius: 6px; width: 60%;">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $pSize == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:0.75rem; padding-top:0.5rem; border-top:1px solid #F1F5F9;">
                    <button type="button" class="btn btn-secondary btn-sm" id="mvFilterResetBtn" style="font-size:0.78rem; padding:4px 10px;"><?= e(__('btn.reset', 'Reset')) ?></button>
                    <button type="button" class="btn btn-primary btn-sm" id="mvFilterApplyBtn" style="font-size:0.78rem; padding:4px 12px;"><?= e(__('btn.filter_apply', 'Apply')) ?></button>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeStatusUrl = function(string $st) use ($search, $fromDate, $toDate, $pSize) {
                $p = [];
                if (!empty($st))       $p['status']    = $st;
                if (!empty($search))   $p['search']    = $search;
                if (!empty($fromDate)) $p['from_date'] = $fromDate;
                if (!empty($toDate))   $p['to_date']   = $toDate;
                if (!empty($pSize) && $pSize != 10) $p['per_page'] = $pSize;
                return url('/driver/earnings' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="earningsMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span>All Dues</span>
                <span class="mv-pill-cnt"><?= (int)($totalEarningsAll ?? $totRec) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('due') ?>" class="mv-pill <?= $currStatus === 'due' ? 'active' : '' ?>" data-status="due" data-no-ajax="true">
                <span>Pending</span>
                <span class="mv-pill-cnt"><?= (int)($totalDue ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('partially_paid') ?>" class="mv-pill <?= in_array($currStatus, ['partially_paid', 'partial'], true) ? 'active' : '' ?>" data-status="partially_paid" data-no-ajax="true">
                <span>Partial</span>
                <span class="mv-pill-cnt"><?= (int)($totalPartial ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('paid') ?>" class="mv-pill <?= in_array($currStatus, ['paid', 'fully_paid'], true) ? 'active' : '' ?>" data-status="paid" data-no-ajax="true">
                <span>Paid</span>
                <span class="mv-pill-cnt"><?= (int)($totalPaid ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. Completed Trips Earnings Ledger Table & Mobile Cards -->
    <section class="recent-bookings-section" id="earningsSection">
        <div class="section-header">
            <h3 class="section-title">Completed Trip Earnings Ledger</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Matching Customer Directory Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="earningsTable">
                <thead>
                    <tr>
                        <th class="text-center col-priority-1" style="width: 50px;">#</th>
                        <th class="col-priority-1"><?= e(__('table.dispatch_booking', 'Dispatch / Booking')) ?></th>
                        <th class="col-priority-2" data-column-priority="secondary"><?= e(__('table.route_itinerary', 'Route Itinerary')) ?></th>
                        <th class="col-priority-2" data-column-priority="secondary" style="width: 140px;"><?= e(__('table.completed_date', 'Completed Date')) ?></th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional"><?= e(__('booking.assigned_vehicle', 'Assigned Vehicle')) ?></th>
                        <th class="col-priority-1" style="text-align: right; width: 120px;"><?= e(__('table.trip_fee', 'Trip Fee')) ?></th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional" style="text-align: right; width: 120px;"><?= e(__('table.received', 'Received')) ?></th>
                        <th class="col-priority-1" style="text-align: right; width: 120px;"><?= e(__('table.remaining_due', 'Remaining Due')) ?></th>
                        <th class="text-center col-priority-1" style="width: 120px;"><?= e(__('status.payment_status', 'Payment Status')) ?></th>
                        <th class="text-center col-priority-1" style="width: 80px;"><?= e(__('table.action', 'Action')) ?></th>
                    </tr>
                </thead>
                <tbody id="earningsTableBody">
                    <?php if (empty($trips)): ?>
                    <tr>
                        <td colspan="10">
                            <div class="no-data-message" style="padding: 3rem 1.5rem; text-align:center;">
                                <div class="no-data-icon" style="font-size:2.5rem; color:#94A3B8; margin-bottom:0.75rem;"><i class="fa-solid fa-wallet"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No earnings records found</h4>
                                <p class="text-muted" style="margin:0;">Completed dispatches and their compensations will appear here once finalized.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($trips as $i => $t):
                            $serial    = (($currP - 1) * $pSize) + $i + 1;
                            $earned    = $t['driver_trip_amount'] !== null ? (float)$t['driver_trip_amount'] : null;
                            $paid      = (float)$t['driver_paid_amount'];
                            $remaining = $t['remaining'];
                            $payStatus = $t['driver_payment_status'] ?? 'not_due';
                        ?>
                        <tr>
                            <!-- 1. Serial Number -->
                            <td class="text-center col-priority-1">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Dispatch / Booking -->
                            <td class="col-priority-1">
                                <div class="cust-client-details-cell">
                                    <div class="cust-name-row">
                                        <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="cust-full-name" title="<?= e(__('tooltip.view_trip_details', 'View Trip Details')) ?>">
                                            <?= e($t['dispatch_code'] ?: ('#DSP-' . $t['id'])) ?>
                                        </a>
                                    </div>
                                    <div class="cust-email-row" style="margin-top:2px;">
                                        <span class="cust-code-pill" style="margin:0; font-size:0.72rem;"><?= e($t['booking_code']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Route Itinerary -->
                            <td class="col-priority-2" data-column-priority="secondary">
                                <div class="cust-address-cell">
                                    <div class="cust-address-line" title="<?= e($t['pickup_address']) ?>">
                                        <span style="color: #10B981; font-weight: 900; margin-right: 4px;">●</span>
                                        <span><?= e($t['pickup_address']) ?></span>
                                    </div>
                                    <div class="cust-address-sub" title="<?= e($t['drop_address']) ?>">
                                        <span style="color: #EF4444; font-weight: 900; margin-right: 4px;">●</span>
                                        <span><?= e($t['drop_address']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Completed Date -->
                            <td class="col-priority-2" data-column-priority="secondary">
                                <div style="font-size: 0.82rem; font-weight: 600; color: #1E293B;">
                                    <?= !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : format_date($t['start_date']) ?>
                                </div>
                                <div style="font-size: 0.72rem; color: #64748B;">
                                    <?= !empty($t['end_time']) ? date('h:i A', strtotime($t['end_time'])) : 'Finalized' ?>
                                </div>
                            </td>

                            <!-- 5. Vehicle -->
                            <td class="col-priority-3 col-optional" data-column-priority="optional">
                                <div style="font-weight: 700; color: #16443B; font-size: 0.86rem; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-car" style="color: #176B5B; font-size: 0.76rem;"></i>
                                    <span><?= e($t['vehicle_number']) ?></span>
                                </div>
                                <span style="font-size: 0.72rem; color: #64748B;"><?= e($t['make_model']) ?></span>
                            </td>

                            <!-- 6. Trip Fee -->
                            <td class="col-priority-1" style="text-align: right; font-weight: 700; font-size: 0.9rem; color: #0F172A;">
                                <?php if ($earned !== null): ?>
                                    <?= format_currency($earned) ?>
                                <?php else: ?>
                                    <span class="badge" style="background: #F1F5F9; color: #64748B; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                        Not specified
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 7. Received (Paid) -->
                            <td class="col-priority-3 col-optional" data-column-priority="optional" style="text-align: right; font-weight: 700; font-size: 0.9rem; color: #10B981;">
                                <?= format_currency($paid) ?>
                            </td>

                            <!-- 8. Remaining Due -->
                            <td class="col-priority-1" style="text-align: right; font-weight: 800; font-size: 0.9rem; color: <?= ($remaining !== null && $remaining > 0) ? '#D97706' : '#10B981' ?>;">
                                <?= $remaining !== null ? format_currency($remaining) : '—' ?>
                            </td>

                            <!-- 9. Payment Status Badge -->
                            <td class="text-center col-priority-1">
                                <?= render_status_badge($payStatus, 'driver_payment') ?>
                            </td>

                            <!-- 10. Action -->
                            <td class="text-center col-priority-1">
                                <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="btn btn-outline btn-sm" style="border-radius: 8px; padding: 4px 10px; font-size: 0.76rem; font-weight: 700;" title="<?= e(__('tooltip.view_trip_details', 'View Trip Details')) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Earnings Cards List (Matching /driver/trips UI) -->
        <div class="cards-grid" id="earningsCards">
            <?php if (empty($trips)): ?>
                <div class="no-data-message" style="background:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0; padding:2rem 1rem; text-align:center;">
                    <div class="no-data-icon" style="font-size:2rem; color:#94A3B8; margin-bottom:0.5rem;"><i class="fa-solid fa-wallet"></i></div>
                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:4px;">No earnings records</h4>
                    <p class="text-muted" style="margin:0; font-size:0.85rem;">No completed dispatches match your search or filter parameters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($trips as $t):
                    $earned    = $t['driver_trip_amount'] !== null ? (float)$t['driver_trip_amount'] : null;
                    $paid      = (float)$t['driver_paid_amount'];
                    $remaining = $t['remaining'];
                    $payStatus = $t['driver_payment_status'] ?? 'not_due';
                    $initial   = strtoupper(mb_substr($t['customer_name'] ?? 'C', 0, 1));
                    $avatarUrl = customer_avatar_url($t['customer_avatar'] ?? null);
                ?>
                <div class="mv-booking-card-wrapper">
                    <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="mv-booking-card" style="text-decoration:none;">
                        <!-- Left: Avatar / Monogram -->
                        <div class="mv-thumb-wrap is-avatar">
                            <?php if (!empty($avatarUrl)): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="<?= e($t['customer_name'] ?? 'Passenger') ?>" class="mv-thumb-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div class="mv-thumb-avatar" style="display: none;"><?= e($initial) ?></div>
                            <?php else: ?>
                                <div class="mv-thumb-avatar" style="background: <?= ($remaining !== null && $remaining > 0) ? '#FEF3C7; color: #D97706;' : '#DEF7EC; color: #03543F;' ?>">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Middle: Details -->
                        <div class="mv-details">
                            <div class="mv-tour-title" title="<?= e($t['dispatch_code'] ?: ('#DSP-' . $t['id'])) ?>">
                                <?= e($t['dispatch_code'] ?: ('#DSP-' . $t['id'])) ?>
                                <span class="cust-code-pill" style="margin-left: 4px;"><?= e($t['booking_code']) ?></span>
                            </div>
                            <div class="mv-date-duration">
                                <i class="fa-solid fa-car"></i>
                                <span><?= e($t['vehicle_number']) ?> &bull; <?= !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : format_date($t['start_date']) ?></span>
                            </div>
                            <?php if (!empty($t['customer_name'])): ?>
                                <div class="mv-date-duration" style="color: #64748B;">
                                    <i class="fa-solid fa-user"></i>
                                    <span><?= e($t['customer_name']) ?><?= !empty($t['customer_phone']) ? ' &bull; ' . e($t['customer_phone']) : '' ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="mv-passenger" style="color:#059669;">
                                <i class="fa-solid fa-location-dot" style="color:#10B981;"></i>
                                <span>From: <?= e($t['pickup_address']) ?></span>
                            </div>
                            <div class="mv-passenger" style="color:#DC2626;">
                                <i class="fa-solid fa-location-dot" style="color:#EF4444;"></i>
                                <span>To: <?= e($t['drop_address']) ?></span>
                            </div>
                        </div>

                        <!-- Right: Status Badge & View Button -->
                        <div class="mv-right-col">
                            <?= render_status_badge($payStatus, 'driver_payment') ?>
                            <span class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.72rem; border-radius: 6px; margin-top: auto;">View &rarr;</span>
                        </div>
                    </a>

                    <!-- Dedicated Full-Width Financial Strip -->
                    <div class="mv-earnings-strip">
                        <div class="mv-earn-stats-group">
                            <div class="mv-earn-stat">
                                <span class="mv-earn-label">Trip Fee</span>
                                <span class="mv-earn-val"><?= $earned !== null ? format_currency($earned) : 'N/A' ?></span>
                            </div>
                            <div class="mv-earn-stat">
                                <span class="mv-earn-label">Received</span>
                                <span class="mv-earn-val is-paid"><?= format_currency($paid) ?></span>
                            </div>
                            <div class="mv-earn-stat is-due-box <?= ($remaining !== null && $remaining > 0) ? 'has-due' : 'is-settled' ?>">
                                <span class="mv-earn-label"><?= ($remaining !== null && $remaining > 0) ? 'Due Amount' : 'Balance' ?></span>
                                <span class="mv-earn-val is-due-amount">
                                    <?= $remaining !== null ? format_currency($remaining) : '₹0.00' ?>
                                </span>
                            </div>
                        </div>
                        <div class="mv-card-actions">
                            <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="card-btns-popup">
                                <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="action-btn-table view" title="<?= e(__('tooltip.view_trip_details', 'View Trip Details')) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?= url('/driver/payments') ?>" class="action-btn-table edit" style="color:#176B5B;" title="<?= e(__('tooltip.payment_vouchers', 'Payment Vouchers')) ?>">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- 6. Modern Pagination (Exact Customer Directory & Driver Trips Architecture) -->
    <?php
        $buildPageUrl = function(int $p) use ($search, $status, $fromDate, $toDate, $pSize) {
            $q = [];
            if (!empty($search))   $q['search']    = $search;
            if (!empty($status))   $q['status']    = $status;
            if (!empty($fromDate)) $q['from_date'] = $fromDate;
            if (!empty($toDate))   $q['to_date']   = $toDate;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/driver/earnings?' . http_build_query($q));
        };
    ?>
    <div id="earningsPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> record(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="<?= e(__('pagination.previous', 'Previous Page')) ?>" data-page="<?= max(1, $currP - 1) ?>" data-no-ajax="true">&#10094;</a>
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
                <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="<?= e(__('pagination.next', 'Next Page')) ?>" data-page="<?= min($totalPages, $currP + 1) ?>" data-no-ajax="true">&#10095;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Driver Earnings Live AJAX Filter Engine (Matching /driver/trips)          */
    /* ========================================================================= */
    (function initDriverEarningsAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/driver/earnings') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const mvStatusSelect = document.getElementById('mvStatusSelect');
            const fromDateInput = document.getElementById('fromDateInput');
            const mvFromDateInput = document.getElementById('mvFromDateInput');
            const toDateInput = document.getElementById('toDateInput');
            const mvToDateInput = document.getElementById('mvToDateInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = (statusInput ? statusInput.value : (mvStatusSelect ? mvStatusSelect.value : '')).trim();
            const fdVal = (fromDateInput ? fromDateInput.value : (mvFromDateInput ? mvFromDateInput.value : '')).trim();
            const tdVal = (toDateInput ? toDateInput.value : (mvToDateInput ? mvToDateInput.value : '')).trim();
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (fdVal) p.set('from_date', fdVal);
            if (tdVal) p.set('to_date', tdVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, fromDateVal: fdVal, toDateVal: tdVal, perPageVal: ppVal, pageNum: page };
        }

        function filterEarningsAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) {
                abortCtrl.abort();
            }
            abortCtrl = new AbortController();

            const section = document.getElementById('earningsSection');
            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';

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

                // 1. Update Earnings Section (Table, mobile cards, empty state)
                const newSection = doc.getElementById('earningsSection');
                const currSection = document.getElementById('earningsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                // 2. Update Pagination Wrapper
                const newPagination = doc.getElementById('earningsPaginationWrapper');
                const currPagination = document.getElementById('earningsPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                // 3. Update Top KPI Summary Grid
                const newKpi = doc.getElementById('earningsKpiGrid');
                const currKpi = document.getElementById('earningsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                }

                // 4. Update Mobile Status Scroll Pills
                const newMvPills = doc.getElementById('earningsMvStatusScroll');
                const currMvPills = document.getElementById('earningsMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                // 5. Update Clear Button in filter bar
                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                try {
                    window.history.replaceState({ path: targetUrl }, '', targetUrl);
                } catch (e) {}
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Earnings AJAX filter failed:', err);
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        // Live Debounced Search
        const searchInput = document.getElementById('searchInput');
        const mvSearchInput = document.getElementById('mvSearchInput');

        function handleSearchInput(val) {
            if (searchInput && searchInput.value !== val) searchInput.value = val;
            if (mvSearchInput && mvSearchInput.value !== val) mvSearchInput.value = val;

            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
            if (searchClearBtn) searchClearBtn.style.display = val ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = val ? 'inline-flex' : 'none';

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterEarningsAjax(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }
        if (mvSearchInput) {
            mvSearchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }

        // Form Submit listener
        const filterForm = document.getElementById('earningsFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterEarningsAjax(1);
            });
        }
        const mvForm = document.getElementById('mvSearchForm');
        if (mvForm) {
            mvForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterEarningsAjax(1);
            });
        }

        // Dropdown & Date changes sync
        const statusInput = document.getElementById('statusInput');
        const mvStatusSelect = document.getElementById('mvStatusSelect');
        const fromDateInput = document.getElementById('fromDateInput');
        const mvFromDateInput = document.getElementById('mvFromDateInput');
        const toDateInput = document.getElementById('toDateInput');
        const mvToDateInput = document.getElementById('mvToDateInput');
        const perPageInput = document.getElementById('perPageInput');
        const mvPerPageSelect = document.getElementById('mvPerPageSelect');

        if (statusInput) {
            statusInput.addEventListener('change', () => {
                if (mvStatusSelect) mvStatusSelect.value = statusInput.value;
                filterEarningsAjax(1);
            });
        }
        if (mvStatusSelect) {
            mvStatusSelect.addEventListener('change', () => {
                if (statusInput) statusInput.value = mvStatusSelect.value;
                filterEarningsAjax(1);
            });
        }
        if (fromDateInput) {
            fromDateInput.addEventListener('change', () => {
                if (mvFromDateInput) mvFromDateInput.value = fromDateInput.value;
                filterEarningsAjax(1);
            });
        }
        if (mvFromDateInput) {
            mvFromDateInput.addEventListener('change', () => {
                if (fromDateInput) fromDateInput.value = mvFromDateInput.value;
                filterEarningsAjax(1);
            });
        }
        if (toDateInput) {
            toDateInput.addEventListener('change', () => {
                if (mvToDateInput) mvToDateInput.value = toDateInput.value;
                filterEarningsAjax(1);
            });
        }
        if (mvToDateInput) {
            mvToDateInput.addEventListener('change', () => {
                if (toDateInput) toDateInput.value = mvToDateInput.value;
                filterEarningsAjax(1);
            });
        }
        if (perPageInput) {
            perPageInput.addEventListener('change', () => {
                if (mvPerPageSelect) mvPerPageSelect.value = perPageInput.value;
                filterEarningsAjax(1);
            });
        }
        if (mvPerPageSelect) {
            mvPerPageSelect.addEventListener('change', () => {
                if (perPageInput) perPageInput.value = mvPerPageSelect.value;
                filterEarningsAjax(1);
            });
        }

        // Apply and Reset buttons
        const mvFilterApplyBtn = document.getElementById('mvFilterApplyBtn');
        const mvFilterResetBtn = document.getElementById('mvFilterResetBtn');
        const refreshBtn = document.getElementById('refreshBtn');

        if (mvFilterApplyBtn) {
            mvFilterApplyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                toggleMobileFilter();
                filterEarningsAjax(1);
            });
        }
        if (mvFilterResetBtn) {
            mvFilterResetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (mvStatusSelect) mvStatusSelect.value = '';
                if (fromDateInput) fromDateInput.value = '';
                if (mvFromDateInput) mvFromDateInput.value = '';
                if (toDateInput) toDateInput.value = '';
                if (mvToDateInput) mvToDateInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                toggleMobileFilter();
                filterEarningsAjax(1);
            });
        }
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (mvStatusSelect) mvStatusSelect.value = '';
                if (fromDateInput) fromDateInput.value = '';
                if (mvFromDateInput) mvFromDateInput.value = '';
                if (toDateInput) toDateInput.value = '';
                if (mvToDateInput) mvToDateInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                filterEarningsAjax(1);
            });
        }

        // Global Event Delegation
        document.addEventListener('click', function(e) {
            // 1. KPI Card Click
            const card = e.target.closest('.booking-kpi-card');
            if (card && card.closest('#earningsKpiGrid')) {
                e.preventDefault();
                const targetStatus = card.getAttribute('data-status') ?? '';
                if (statusInput) statusInput.value = targetStatus;
                if (mvStatusSelect) mvStatusSelect.value = targetStatus;
                filterEarningsAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('.mv-pill');
            if (pill && pill.closest('#earningsMvStatusScroll')) {
                e.preventDefault();
                const targetStatus = pill.getAttribute('data-status') ?? '';
                if (statusInput) statusInput.value = targetStatus;
                if (mvStatusSelect) mvStatusSelect.value = targetStatus;
                filterEarningsAjax(1);
                return;
            }

            // 3. Clear Filters Button
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn');
            if (clearBtn) {
                e.preventDefault();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn') {
                    if (searchInput) searchInput.value = '';
                    if (mvSearchInput) mvSearchInput.value = '';
                } else {
                    if (searchInput) searchInput.value = '';
                    if (mvSearchInput) mvSearchInput.value = '';
                    if (statusInput) statusInput.value = '';
                    if (mvStatusSelect) mvStatusSelect.value = '';
                    if (fromDateInput) fromDateInput.value = '';
                    if (mvFromDateInput) mvFromDateInput.value = '';
                    if (toDateInput) toDateInput.value = '';
                    if (mvToDateInput) mvToDateInput.value = '';
                    if (perPageInput) perPageInput.value = '10';
                    if (mvPerPageSelect) mvPerPageSelect.value = '10';
                }
                filterEarningsAjax(1);
                return;
            }

            // 4. Pagination Links
            const pageLink = e.target.closest('#earningsPaginationWrapper .pagination a[data-page]');
            if (pageLink && !pageLink.classList.contains('disabled') && !pageLink.classList.contains('active')) {
                e.preventDefault();
                const p = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
                filterEarningsAjax(p);
                const section = document.getElementById('earningsSection');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
        });
    })();

    /* ========================================================================= */
    /* 3-Dots Action Popups Toggle & Mobile Filters                              */
    /* ========================================================================= */
    function toggleActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        if (!popup) return;
        const isOpen = popup.classList.contains('open') || popup.classList.contains('show');
        document.querySelectorAll('.action-btns-popup.open, .action-btns-popup.show, .card-btns-popup.open, .card-btns-popup.show').forEach(p => {
            p.classList.remove('open', 'show');
        });
        if (!isOpen) {
            popup.classList.add('open', 'show');
        }
    }

    function toggleCardActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        if (!popup) return;
        const isOpen = popup.classList.contains('open') || popup.classList.contains('show');
        document.querySelectorAll('.action-btns-popup.open, .action-btns-popup.show, .card-btns-popup.open, .card-btns-popup.show').forEach(p => {
            p.classList.remove('open', 'show');
        });
        if (!isOpen) {
            popup.classList.add('open', 'show');
        }
    }

    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        const b = document.getElementById('mvFilterToggleBtn');
        if (p) {
            const isHidden = p.style.display === 'none' || p.style.display === '';
            p.style.display = isHidden ? 'block' : 'none';
            if (b) b.classList.toggle('active', isHidden);
        }
    }

    // Close action popups when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions') && !e.target.closest('.mv-earn-actions-wrap')) {
            document.querySelectorAll('.action-btns-popup.open, .action-btns-popup.show, .card-btns-popup.open, .card-btns-popup.show').forEach(p => {
                p.classList.remove('open', 'show');
            });
        }
    });
</script>

<style>
/* ========================================================================= */
/* Driver Earnings Scoped Responsive Rules (Matching /driver/trips)          */
/* ========================================================================= */
.mv-bookings-header {
    display: none;
    margin-bottom: 12px;
}

.mv-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 0 12px 0;
}

.mv-top-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mv-back-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #F1F5F9;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.mv-page-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.mv-add-btn {
    padding: 0.45rem 0.85rem;
    background: linear-gradient(135deg, #176B5B 0%, #125748 100%);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.mv-search-bar-wrap {
    margin-bottom: 10px;
}

.mv-search-form {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
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
    outline: none;
    background: #F8FAFC;
}

.mv-search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
}

.mv-filter-toggle {
    padding: 0.5rem 0.75rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    background: #fff;
    color: #334155;
    cursor: pointer;
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

.mv-card-actions {
    position: absolute;
    bottom: 8px;
    right: 8px;
}

/* Dedicated Financial Strip across bottom of card */
.mv-earnings-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
    padding: 8px 12px;
    gap: 8px;
}

.mv-earn-stats-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
    justify-content: space-between;
}

.mv-earn-stat {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.mv-earn-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #64748B;
    line-height: 1.1;
    margin-bottom: 2px;
    white-space: nowrap;
}

.mv-earn-val {
    font-size: 0.88rem;
    font-weight: 800;
    color: #0F172A;
    white-space: nowrap;
    line-height: 1.2;
}

.mv-earn-val.is-paid {
    color: #10B981;
}

.mv-earn-stat.is-due-box {
    padding: 3px 8px;
    border-radius: 6px;
    flex-shrink: 0;
}

.mv-earn-stat.has-due {
    background: #FEF3C7;
    border: 1px solid #FDE68A;
}

.mv-earn-stat.has-due .mv-earn-label {
    color: #92400E;
    font-weight: 800;
}

.mv-earn-stat.has-due .mv-earn-val.is-due-amount {
    color: #B45309;
    font-weight: 800;
}

.mv-earn-stat.is-settled {
    background: #DEF7EC;
    border: 1px solid #BCF0DA;
}

.mv-earn-stat.is-settled .mv-earn-label {
    color: #03543F;
}

.mv-earn-stat.is-settled .mv-earn-val.is-due-amount {
    color: #046C4E;
}

.mv-earnings-strip .mv-card-actions,
.mv-earn-actions-wrap.mv-card-actions {
    position: relative !important;
    bottom: auto !important;
    right: auto !important;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    margin-left: 6px;
}

@media (max-width: 380px) {
    .mv-earnings-strip {
        flex-wrap: wrap;
        padding: 8px 10px;
        gap: 6px 8px;
    }
    .mv-earn-stats-group {
        gap: 8px;
        flex-wrap: wrap;
    }
    .mv-earn-stat.is-due-box {
        flex-grow: 1;
    }
}

.mv-action-dots-btn {
    width: 32px;
    height: 32px;
    min-width: 32px;
    min-height: 32px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    color: #64748B;
    border-radius: 9px;
    font-size: 13.5px;
    cursor: pointer;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.mv-action-dots-btn:hover {
    background: #FFFFFF;
    color: #176B5B;
    border-color: #CBD5E1;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.mv-card-actions.open .mv-action-dots-btn,
.mv-card-actions.is-open .mv-action-dots-btn,
.mv-action-dots-btn:active {
    background: #176B5B !important;
    color: #FFFFFF !important;
    border-color: #176B5B !important;
}

.card-btns-popup {
    position: absolute;
    bottom: calc(100% + 6px);
    right: 0;
    top: auto;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.14), 0 4px 6px -2px rgba(15, 23, 42, 0.05);
    display: none;
    gap: 6px;
    padding: 6px 8px;
    z-index: 50;
    align-items: center;
    transform-origin: bottom right;
}

.card-btns-popup.open,
.card-btns-popup.show {
    display: flex !important;
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination a {
    min-width: 34px;
    height: 34px;
    padding: 0 8px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.15s ease;
}

.pagination a:hover:not(.disabled):not(.active) {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.pagination a.active {
    background: #0F172A;
    color: #fff;
    border-color: #0F172A;
}

.pagination a.disabled {
    color: #CBD5E1;
    cursor: not-allowed;
    border-color: #E2E8F0;
}

.pagination .dots {
    color: #94A3B8;
    padding: 0 4px;
}

/* Responsive display switches matching /driver/trips */
@media (max-width: 768px) {
    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container {
        display: none !important;
    }
    .mv-bookings-header {
        display: block !important;
    }
    .cards-grid {
        display: flex !important;
        flex-direction: column;
        gap: 12px;
    }
    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
        align-items: center;
    }
}

@media (min-width: 769px) {
    .mv-bookings-header,
    .cards-grid {
        display: none !important;
    }
    .desktop-header-wrapper {
        display: block !important;
    }
    .booking-kpi-grid {
        display: grid !important;
    }
    .filter-bar-compact {
        display: flex !important;
    }
    .modern-table-container {
        display: block !important;
    }
}
</style>
