<?php
/**
 * DRIVER TRIP PAYMENTS & SETTLEMENTS
 * Complete Professional Driver Payment Management & Receipt System
 */

$paymentMethod = $paymentMethod ?? '';
$receiptNumber = $receiptNumber ?? '';

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap; align-items: center;">'
    . '<button type="button" class="btn btn-primary" onclick="openGuidedPayModal()" style="font-weight: 700; height: 38px;">'
    . '<i class="fa-solid fa-plus"></i> <span>Pay a Driver</span></button>'
    . '<a href="' . url('/office/finance/driver-payments?status=due') . '" class="btn btn-outline" style="height: 38px;">'
    . '<i class="fa-solid fa-clock"></i> <span>Check Pending Payments</span></a>'
    . '<a href="' . url('/office/finance/dashboard') . '" class="btn btn-outline" style="height: 38px;">'
    . '<i class="fa-solid fa-chart-pie"></i> <span>Finance Overview</span></a>'
    . '</div>';

$makeFilterUrl = function(array $overrides = []) use ($search, $selectedDriver, $selectedStatus, $fromDate, $toDate, $paymentMethod, $receiptNumber) {
    $params = [
        'search'         => $search,
        'driver_id'      => $selectedDriver,
        'status'         => $selectedStatus,
        'from_date'      => $fromDate,
        'to_date'        => $toDate,
        'payment_method' => $paymentMethod,
        'receipt_number' => $receiptNumber,
    ];
    $merged = array_merge($params, $overrides);
    return url('/office/finance/driver-payments?' . http_build_query(array_filter($merged, fn($v) => $v !== '' && $v !== null)));
};
?>

<div class="bookings-page-view finance-page-view" id="driverPaymentsModule">
    <!-- 1. Unified Desktop Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Driver Payment Management',
            'description' => 'Find drivers, review trip earnings and previous payouts, record partial or full disbursements, and print professional receipts.',
            'badge'       => 'Driver Treasury',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance Overview', 'url' => url('/office/finance/dashboard')],
                ['label' => 'Driver Payments', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Attractive Summary Cards (4 Standard Professional Cards) -->
    <div class="booking-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
        <!-- Card 1: Total Paid to Drivers -->
        <a href="<?= url('/office/finance/driver-payments?status=paid') ?>" class="booking-kpi-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Paid to Drivers</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($kpiStats['total_paid'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Disbursed via Cash, Bank &amp; UPI</span>
            </div>
        </a>

        <!-- Card 2: Still to Pay (Remaining Due) -->
        <a href="<?= url('/office/finance/driver-payments?status=due') ?>" class="booking-kpi-card <?= ($kpiStats['total_pending'] ?? 0) > 0 ? 'is-selected' : '' ?>" style="text-decoration: none; color: inherit; display: block;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Still to Pay</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= ($kpiStats['total_pending'] ?? 0) > 0 ? '#C78A35' : 'var(--brand-primary)' ?>;"><?= format_inr($kpiStats['total_pending'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= (int)($kpiStats['total_pending_count'] ?? 0) ?> trip(s) awaiting settlement</span>
            </div>
        </a>

        <!-- Card 3: Fully Paid Trips -->
        <a href="<?= url('/office/finance/driver-payments?status=paid') ?>" class="booking-kpi-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Fully Paid Trips</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= number_format((int)($kpiStats['total_paid_count'] ?? 0)) ?></div>
            <div class="booking-kpi-sub">
                <span>100% completed settlements</span>
            </div>
        </a>

        <!-- Card 4: Trips Awaiting Payment -->
        <a href="<?= url('/office/finance/driver-payments?status=due') ?>" class="booking-kpi-card" style="text-decoration: none; color: inherit; display: block;">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Trips Awaiting Payment</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--vg-navy-900);"><?= number_format((int)($kpiStats['total_pending_count'] ?? 0)) ?></div>
            <div class="booking-kpi-sub">
                <span><?= (int)($kpiStats['total_due_count'] ?? 0) ?> unpaid &bull; <?= (int)($kpiStats['total_partial_count'] ?? 0) ?> part paid</span>
            </div>
        </a>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back to Finance">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Pay Driver</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openGuidedPayModal()" style="background: #218765; color: #FFFFFF;">
                <i class="fa-solid fa-plus"></i>
                <span>Pay</span>
            </button>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/driver-payments') ?>" class="mv-search-form" id="mvDriverPaySearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvDriverSearchInput" placeholder="Search driver, trip, receipt #..." value="<?= e($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= $makeFilterUrl(['search' => '']) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle <?= (!empty($selectedStatus) || !empty($selectedDriver) || !empty($fromDate) || !empty($toDate) || !empty($paymentMethod)) ? 'active' : '' ?>" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
                <?php if (!empty($selectedStatus)): ?><input type="hidden" name="status" value="<?= e($selectedStatus) ?>"><?php endif; ?>
                <?php if (!empty($selectedDriver)): ?><input type="hidden" name="driver_id" value="<?= e($selectedDriver) ?>"><?php endif; ?>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/driver-payments') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Payment Status:</label>
                            <select name="status" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <option value="">All Statuses</option>
                                <option value="due" <?= $selectedStatus === 'due' ? 'selected' : '' ?>>Still to Pay (Pending)</option>
                                <option value="partially_paid" <?= $selectedStatus === 'partially_paid' ? 'selected' : '' ?>>Part Payment</option>
                                <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>Fully Paid</option>
                                <option value="amount_not_specified" <?= $selectedStatus === 'amount_not_specified' ? 'selected' : '' ?>>Amount Not Specified</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Driver:</label>
                            <select name="driver_id" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <option value="">All Drivers</option>
                                <?php foreach ($drivers as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= (string)$selectedDriver === (string)$d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['name']) ?> <?= !empty($d['employee_code']) ? '(' . e($d['employee_code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">From:</label>
                                <input type="date" name="from_date" value="<?= e($fromDate) ?>" class="form-control" style="font-size: 0.82rem; height: 38px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">To:</label>
                                <input type="date" name="to_date" value="<?= e($toDate) ?>" class="form-control" style="font-size: 0.82rem; height: 38px;">
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="<?= url('/office/finance/driver-payments') ?>" class="btn btn-outline btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Status Scroll Pills -->
        <div class="mv-status-scroll">
            <a href="<?= $makeFilterUrl(['status' => '']) ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Trips</span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'due']) ?>" class="mv-pill <?= $selectedStatus === 'due' ? 'active' : '' ?>">
                <span>Still to Pay</span>
                <span class="mv-pill-cnt"><?= (int)($kpiStats['total_due_count'] ?? 0) ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'partially_paid']) ?>" class="mv-pill <?= $selectedStatus === 'partially_paid' ? 'active' : '' ?>">
                <span>Part Payment</span>
                <span class="mv-pill-cnt"><?= (int)($kpiStats['total_partial_count'] ?? 0) ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'paid']) ?>" class="mv-pill <?= $selectedStatus === 'paid' ? 'active' : '' ?>">
                <span>Fully Paid</span>
                <span class="mv-pill-cnt"><?= (int)($kpiStats['total_paid_count'] ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 4. Filter & Search Bar (Desktop) -->
    <div class="vg-card desktop-filter-card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
        <form method="GET" action="<?= url('/office/finance/driver-payments') ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <!-- Search Text -->
            <div style="flex: 2 1 220px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">Search Driver / Trip / Receipt #</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search driver name, phone, trip code, receipt #..." value="<?= e($search) ?>" style="padding-left: 2rem;">
                </div>
            </div>

            <!-- Driver Filter -->
            <div style="flex: 1 1 180px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">Driver</label>
                <select name="driver_id" class="form-select">
                    <option value="">All Drivers</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (string)$selectedDriver === (string)$d['id'] ? 'selected' : '' ?>>
                            <?= e($d['name']) ?> <?= !empty($d['employee_code']) ? '(' . e($d['employee_code']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div style="flex: 1 1 160px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">Payment Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="due" <?= $selectedStatus === 'due' ? 'selected' : '' ?>>Still to Pay (Pending)</option>
                    <option value="partially_paid" <?= $selectedStatus === 'partially_paid' ? 'selected' : '' ?>>Part Payment</option>
                    <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>Fully Paid</option>
                    <option value="amount_not_specified" <?= $selectedStatus === 'amount_not_specified' ? 'selected' : '' ?>>Amount Not Specified</option>
                </select>
            </div>

            <!-- Payment Method Filter -->
            <div style="flex: 1 1 140px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="upi" <?= $paymentMethod === 'upi' ? 'selected' : '' ?>>UPI</option>
                    <option value="bank_transfer" <?= $paymentMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="cheque" <?= $paymentMethod === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                </select>
            </div>

            <!-- Date Range From -->
            <div style="flex: 0 1 130px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">From Date</label>
                <input type="date" name="from_date" class="form-control" value="<?= e($fromDate) ?>">
            </div>

            <!-- Date Range To -->
            <div style="flex: 0 1 130px;">
                <label class="form-label" style="font-size: 0.78rem; margin-bottom: 0.3rem;">To Date</label>
                <input type="date" name="to_date" class="form-control" value="<?= e($toDate) ?>">
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.45rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fa-solid fa-filter"></i> <span>Filter</span>
                </button>
                <a href="<?= url('/office/finance/driver-payments') ?>" class="btn btn-outline" style="height: 38px;" title="Reset filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- 5. Main Settlements Table Section -->
    <section class="recent-bookings-section" id="driverPaymentsSection">
        <div class="section-header">
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <h3 class="section-title">
                    <i class="fa-solid fa-money-check-dollar" style="color: var(--brand-primary); margin-right: 0.35rem;"></i>
                    Driver Payment Records
                </h3>
                <span class="badge" style="background: rgba(22, 101, 52, 0.1); color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">
                    <i class="fa-solid fa-shield-halved"></i> Audit-Ready Treasury
                </span>
            </div>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('table.total_records', 'Total Records:')) ?></span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($trips)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="driverPaymentsTable">
                <thead>
                    <tr>
                        <th class="col-priority-1 table-col-index" data-column-priority="primary">#</th>
                        <th class="col-priority-1 table-col-name" data-column-priority="primary">Driver Name</th>
                        <th class="col-priority-3 col-optional table-col-schedule" data-column-priority="optional">Date &amp; Route</th>
                        <th class="col-priority-1 table-col-amount" data-column-priority="primary">Total Trip Amount</th>
                        <th class="col-priority-1 table-col-amount" data-column-priority="primary">Previously Paid</th>
                        <th class="col-priority-1 table-col-amount" data-column-priority="primary">Amount Paid</th>
                        <th class="col-priority-1 table-col-amount" data-column-priority="primary">Still to Pay</th>
                        <th class="col-priority-1 table-col-status" data-column-priority="primary">Status</th>
                        <th class="col-priority-1 table-col-actions" data-column-priority="primary">Actions</th>
                    </tr>
                </thead>
                <tbody id="driverPaymentsTableBody">
                    <?php if (empty($trips)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="no-data-message" style="padding: 2.5rem 1rem;">
                                    <div class="no-data-icon"><i class="fa-solid fa-receipt"></i></div>
                                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payment records found</h4>
                                    <p class="text-muted" style="margin:0;">No trip payment records match the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($trips as $i => $t):
                            $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 15) + $i + 1;
                            $tripAmountVal = $t['driver_trip_amount'] !== null ? (float)$t['driver_trip_amount'] : null;
                            $paidVal = (float)$t['driver_paid_amount'];
                            $remainingVal = $t['remaining'];
                            $status = $t['driver_payment_status'] ?? 'not_due';
                            $vouchers = $t['payments'] ?? [];
                            $hasVouchers = !empty($vouchers);
                            $latestVoucher = $hasVouchers ? $vouchers[0] : null;
                            $canPay = in_array($status, ['due', 'partially_paid'], true) && ($remainingVal !== null && $remainingVal > 0);
                        ?>
                        <tr class="settlement-row" id="row-dispatch-<?= (int)$t['id'] ?>">
                            <!-- 1. Serial Number (#) -->
                            <td class="text-center col-priority-1" data-column-priority="primary">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Driver Name -->
                            <td class="col-priority-1" data-column-priority="primary">
                                <div style="font-weight: 600; color: #0F172A; font-size: 0.88rem; display: flex; align-items: center; gap: 0.35rem;">
                                    <span><?= e($t['driver_name'] ?? 'Unassigned') ?></span>
                                    <?php if (!empty($t['employee_id'])): ?>
                                        <a href="<?= url('/office/employees/' . $t['employee_id'] . '/finance') ?>" title="View Employee Financial Profile" style="color: var(--brand-primary); font-size: 0.75rem;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    <?= !empty($t['driver_phone']) ? e($t['driver_phone']) : (!empty($t['employee_code']) ? e($t['employee_code']) : '') ?>
                                </div>
                            </td>

                            <!-- 3. Date & Route -->
                            <td class="col-priority-3 col-optional" data-column-priority="optional" style="max-width: 200px;">
                                <div style="font-size: 0.82rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($t['pickup_address'] ?? '') ?> &rarr; <?= e($t['drop_address'] ?? '') ?>">
                                    <?= e($t['pickup_address'] ?? 'Start') ?> &rarr; <?= e($t['drop_address'] ?? 'End') ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    <i class="fa-regular fa-calendar" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                    <?= !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : (!empty($t['start_time']) ? date('d M Y', strtotime($t['start_time'])) : 'N/A') ?>
                                </div>
                            </td>

                            <!-- 4. Total Trip Amount -->
                            <td class="col-priority-1" data-column-priority="primary" style="text-align: right;">
                                <?php if ($tripAmountVal !== null): ?>
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.88rem;">
                                        <?= format_inr($tripAmountVal) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge" style="background: #F1F5F9; color: #64748B; font-weight: 600; font-size: 0.75rem; padding: 3px 8px; border-radius: 6px;">
                                        Not specified
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Previously Paid -->
                            <td class="col-priority-1" data-column-priority="primary" style="text-align: right;">
                                <span style="font-size: 0.85rem; color: #475569;">
                                    <?= format_inr(max(0.0, $paidVal - ($latestVoucher ? (float)$latestVoucher['amount'] : 0.0))) ?>
                                </span>
                            </td>

                            <!-- 6. Amount Paid (Total Disbursed) -->
                            <td class="col-priority-1" data-column-priority="primary" style="text-align: right;">
                                <div style="font-weight: 700; color: #166534; font-size: 0.88rem;">
                                    <?= format_inr($paidVal) ?>
                                </div>
                                <?php if ($hasVouchers): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        <?= count($vouchers) ?> payout<?= count($vouchers) > 1 ? 's' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 7. Still to Pay (Remaining Due) -->
                            <td class="col-priority-1" data-column-priority="primary" style="text-align: right;">
                                <?php if ($remainingVal !== null): ?>
                                    <div style="font-weight: 800; font-size: 0.88rem; color: <?= $remainingVal > 0 ? '#C78A35' : '#10B981' ?>;">
                                        <?= format_inr($remainingVal) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 0.78rem; color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 8. Payment Status Badge -->
                            <td class="text-center col-priority-1" data-column-priority="primary">
                                <?php if ($status === 'paid'): ?>
                                    <span style="display:inline-block; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; background:#DCFCE7; color:#166534;">
                                        Fully Paid
                                    </span>
                                <?php elseif ($status === 'partially_paid'): ?>
                                    <span style="display:inline-block; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; background:#FEF3C7; color:#B45309;">
                                        Part Payment
                                    </span>
                                <?php elseif ($status === 'due'): ?>
                                    <span style="display:inline-block; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px; background:#EFF6FF; color:#1D4ED8;">
                                        Still to Pay
                                    </span>
                                <?php else: ?>
                                    <span style="display:inline-block; font-size:0.72rem; font-weight:600; padding:3px 9px; border-radius:999px; background:#F1F5F9; color:#64748B;">
                                        Not Specified
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Actions (Single Professional Actions Control) -->
                            <td class="col-priority-1 text-end" data-column-priority="primary" style="white-space: nowrap;">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/bookings/' . (int)($t['booking_id'] ?? $t['id'])) ?>" class="action-dropdown-item view">
                                            <i class="fa-solid fa-eye"></i> <span>View Details</span>
                                        </a>

                                        <?php if ($canPay): ?>
                                            <button type="button" class="action-dropdown-item pay" onclick="openDirectPayModal(<?= htmlspecialchars(json_encode([
                                                'dispatch_id'         => (int)$t['id'],
                                                'dispatch_code'       => $t['dispatch_code'] ?: ('#DSP-' . $t['id']),
                                                'driver_id'           => (int)$t['driver_id'],
                                                'driver_name'         => $t['driver_name'] ?? 'Driver',
                                                'driver_trip_amount'  => $tripAmountVal,
                                                'driver_paid_amount'  => $paidVal,
                                                'remaining_amount'    => $remainingVal,
                                                'trip_date'           => !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : (!empty($t['start_time']) ? date('d M Y', strtotime($t['start_time'])) : 'N/A'),
                                            ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="fa-solid fa-credit-card"></i> <span>Record Payment</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($latestVoucher): ?>
                                            <a href="<?= url('/office/finance/driver-payments/receipt/' . (int)$latestVoucher['id']) ?>" class="action-dropdown-item">
                                                <i class="fa-solid fa-file-invoice"></i> <span>View Receipt</span>
                                            </a>
                                            <button type="button" class="action-dropdown-item" onclick="openReceiptModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['dispatch_code'] ?: ('#DSP-' . $t['id']))) ?>', '<?= e(addslashes($t['driver_name'] ?? 'Driver')) ?>')">
                                                <i class="fa-solid fa-print"></i> <span>Print Receipt...</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($hasVouchers): ?>
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item" onclick="toggleVouchersRow(<?= (int)$t['id'] ?>)">
                                                <i class="fa-solid fa-receipt"></i> <span>Payment History (<?= count($vouchers) ?>)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Collapsible Vouchers History Row -->
                        <?php if ($hasVouchers): ?>
                            <tr id="vouchers-row-<?= (int)$t['id'] ?>" class="vouchers-expand-row" style="display: none; background: #F8FAFC;">
                                <td colspan="9" style="padding: 1rem 1.35rem; border-bottom: 2px solid #E2E8F0;">
                                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                                            <strong style="font-size: 0.82rem; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px;">
                                                <i class="fa-solid fa-file-invoice-dollar" style="color: #2563EB; margin-right: 4px;"></i>
                                                Payment Receipts for <?= e($t['dispatch_code'] ?: ('#DSP-' . $t['id'])) ?>
                                            </strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                                Total Paid: <strong style="color: #166534;"><?= format_inr($paidVal) ?></strong>
                                                &bull; Still to Pay: <strong style="color: <?= ($remainingVal ?? 0) > 0 ? '#B45309' : '#166534' ?>;"><?= format_inr($remainingVal ?? 0) ?></strong>
                                            </span>
                                        </div>
                                        <div class="table-responsive">
                                            <table style="width: 100%; font-size: 0.78rem; border-collapse: collapse;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid #E2E8F0; color: #64748B; text-align: left;">
                                                        <th style="padding: 6px 8px;">Receipt #</th>
                                                        <th style="padding: 6px 8px;">Date</th>
                                                        <th style="padding: 6px 8px;">Method</th>
                                                        <th style="padding: 6px 8px;">Pay From Account</th>
                                                        <th style="padding: 6px 8px;">Transaction / UTR #</th>
                                                        <th style="padding: 6px 8px; text-align: center;">Status</th>
                                                        <th style="padding: 6px 8px; text-align: right;">Amount Paid</th>
                                                        <th style="padding: 6px 8px; text-align: center;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($vouchers as $v): 
                                                        $isRev = ($v['status'] ?? '') === 'reversed';
                                                        $rNum = $v['receipt_number'] ?: $v['payment_reference'];
                                                    ?>
                                                        <tr style="border-bottom: 1px solid #F1F5F9; <?= $isRev ? 'background: #FFF1F2; opacity: 0.8;' : '' ?>">
                                                            <td style="padding: 6px 8px; font-weight: 700; font-family: monospace; color: #0F172A;">
                                                                <?= e($rNum) ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; color: #475569;">
                                                                <?= date('d M Y', strtotime($v['payment_date'])) ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-transform: capitalize; color: #475569;">
                                                                <?= e(str_replace('_', ' ', $v['payment_method'])) ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; color: #475569;">
                                                                <?= e($v['account_name'] ?? 'Company Vault') ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; font-family: monospace; color: #64748B;">
                                                                <?= e($v['transaction_ref'] ?: '—') ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center;">
                                                                <?php if ($isRev): ?>
                                                                    <span style="display:inline-block; font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 9999px; background: #FEE2E2; color: #991B1B;">
                                                                        Reversed
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span style="display:inline-block; font-size: 0.68rem; font-weight: 600; padding: 2px 7px; border-radius: 9999px; background: #DCFCE7; color: #166534;">
                                                                        Completed
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: right; font-weight: 700; color: <?= $isRev ? '#991B1B; text-decoration: line-through;' : '#166534;' ?>">
                                                                <?= format_inr($v['amount']) ?>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; white-space: nowrap;">
                                                                <a href="<?= url('/office/finance/driver-payments/receipt/' . (int)$v['id']) ?>" class="btn btn-outline btn-sm" style="padding: 2px 6px; font-size: 0.7rem; line-height: 1.2;" title="View Receipt">
                                                                    <i class="fa-solid fa-eye"></i> View
                                                                </a>
                                                                <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 6px; font-size: 0.7rem; line-height: 1.2;" onclick="openReceiptModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['dispatch_code'] ?: ('#DSP-' . $t['id']))) ?>', '<?= e(addslashes($t['driver_name'] ?? 'Driver')) ?>')" title="Print Receipt Options">
                                                                    <i class="fa-solid fa-print"></i>
                                                                </button>
                                                                <?php if (auth_role() === 'admin' && !$isRev): ?>
                                                                    <button type="button" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FCA5A5; padding: 2px 6px; font-size: 0.7rem; line-height: 1.2;" onclick="openRowReverseModal(<?= (int)$v['id'] ?>, '<?= e($rNum) ?>', <?= (float)$v['amount'] ?>)" title="Reverse payment voucher">
                                                                        <i class="fa-solid fa-rotate-left"></i> Reverse
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid" id="driverPaymentsCards">
            <?php if (empty($trips)): ?>
                <div class="no-data-message" style="grid-column: 1 / -1; padding: 2.5rem 1rem;">
                    <div class="no-data-icon"><i class="fa-solid fa-receipt"></i></div>
                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payment records found</h4>
                    <p class="text-muted" style="margin:0;">No completed trip settlements match the selected filters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($trips as $t):
                    $tripAmountVal = $t['driver_trip_amount'] !== null ? (float)$t['driver_trip_amount'] : null;
                    $paidVal = (float)$t['driver_paid_amount'];
                    $remainingVal = $t['remaining'];
                    $status = $t['driver_payment_status'] ?? 'not_due';
                    $vouchers = $t['payments'] ?? [];
                    $hasVouchers = !empty($vouchers);
                    $latestVoucher = $hasVouchers ? $vouchers[0] : null;
                    $canPay = in_array($status, ['due', 'partially_paid'], true) && ($remainingVal !== null && $remainingVal > 0);
                ?>
                <div class="mv-booking-card-wrapper" id="card-dispatch-<?= (int)$t['id'] ?>">
                    <div class="mv-booking-card" style="display: block; padding: 1rem 1.15rem;">
                        <!-- Card Top: Dispatch code + Status badge -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                            <div>
                                <a href="<?= url('/office/bookings/' . (int)($t['booking_id'] ?? $t['id'])) ?>" style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.95rem; text-decoration: none;">
                                    <?= e($t['dispatch_code'] ?: ('#DSP-' . $t['id'])) ?>
                                </a>
                                <div style="font-size: 0.74rem; color: var(--text-muted);">
                                    <?= !empty($t['booking_code']) ? 'Booking ' . e($t['booking_code']) : 'Direct Dispatch' ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($status === 'paid'): ?>
                                    <span style="display:inline-block; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px; background:#DCFCE7; color:#166534;">Fully Paid</span>
                                <?php elseif ($status === 'partially_paid'): ?>
                                    <span style="display:inline-block; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px; background:#FEF3C7; color:#B45309;">Part Payment</span>
                                <?php else: ?>
                                    <span style="display:inline-block; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px; background:#EFF6FF; color:#1D4ED8;">Still to Pay</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body: Driver info, Route, Date -->
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.82rem; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #F1F5F9;">
                            <div style="display: flex; align-items: center; gap: 0.4rem; color: #0F172A; font-weight: 600;">
                                <i class="fa-solid fa-user-tie" style="color: var(--brand-primary); width: 16px;"></i>
                                <span><?= e($t['driver_name'] ?? 'Unassigned') ?></span>
                                <?php if (!empty($t['vehicle_number'])): ?>
                                    <span style="color: var(--text-muted); font-weight: normal; font-size: 0.76rem;">(<?= e($t['vehicle_number']) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.4rem; color: #475569;">
                                <i class="fa-solid fa-route" style="color: #64748B; width: 16px; margin-top: 3px;"></i>
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($t['pickup_address'] ?? 'Start') ?> &rarr; <?= e($t['drop_address'] ?? 'End') ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); font-size: 0.76rem;">
                                <i class="fa-regular fa-calendar" style="width: 16px;"></i>
                                <span><?= !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : (!empty($t['start_time']) ? date('d M Y', strtotime($t['start_time'])) : 'N/A') ?></span>
                            </div>
                        </div>

                        <!-- Card Financials Summary: Trip Amount | Amount Paid | Still to Pay -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; background: #F8FAFC; border-radius: 8px; padding: 0.6rem 0.75rem; margin-bottom: 0.75rem; text-align: center;">
                            <div>
                                <span style="display: block; font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Total</span>
                                <strong style="font-size: 0.85rem; color: #0F172A;"><?= $tripAmountVal !== null ? format_inr($tripAmountVal) : 'N/A' ?></strong>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Paid</span>
                                <strong style="font-size: 0.85rem; color: #166534;"><?= format_inr($paidVal) ?></strong>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Still to Pay</span>
                                <strong style="font-size: 0.85rem; color: <?= ($remainingVal ?? 0) > 0 ? '#C78A35' : '#10B981' ?>;">
                                    <?= $remainingVal !== null ? format_inr($remainingVal) : '—' ?>
                                </strong>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="mv-card-actions">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" aria-label="Actions">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                    <span>Actions</span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <a href="<?= url('/office/bookings/' . (int)($t['booking_id'] ?? $t['id'])) ?>" class="action-dropdown-item view">
                                        <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                                    </a>
                                    <?php if ($canPay): ?>
                                        <button type="button" class="action-dropdown-item pay text-success" onclick="openDirectPayModal(<?= htmlspecialchars(json_encode([
                                            'dispatch_id'         => (int)$t['id'],
                                            'dispatch_code'       => $t['dispatch_code'] ?: ('#DSP-' . $t['id']),
                                            'driver_id'           => (int)$t['driver_id'],
                                            'driver_name'         => $t['driver_name'] ?? 'Driver',
                                            'driver_trip_amount'  => $tripAmountVal,
                                            'driver_paid_amount'  => $paidVal,
                                            'remaining_amount'    => $remainingVal,
                                            'trip_date'           => !empty($t['end_time']) ? date('d M Y', strtotime($t['end_time'])) : (!empty($t['start_time']) ? date('d M Y', strtotime($t['start_time'])) : 'N/A'),
                                        ]), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fa-solid fa-credit-card"></i> <span>Record Payment</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($latestVoucher): ?>
                                        <a href="<?= url('/office/finance/driver-payments/receipt/' . (int)$latestVoucher['id']) ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-file-invoice text-info"></i> <span>View Receipt</span>
                                        </a>
                                        <button type="button" class="action-dropdown-item" onclick="openReceiptModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['dispatch_code'] ?: ('#DSP-' . $t['id']))) ?>', '<?= e(addslashes($t['driver_name'] ?? 'Driver')) ?>')">
                                            <i class="fa-solid fa-print"></i> <span>Print Receipt...</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- 6. Pagination -->
    <?php
        $totRec = $totalRecords ?? count($trips);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 15;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedDriver, $selectedStatus, $fromDate, $toDate, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedDriver)) $q['driver_id'] = $selectedDriver;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($fromDate)) $q['from_date'] = $fromDate;
            if (!empty($toDate)) $q['to_date'] = $toDate;
            if (!empty($pSize) && $pSize != 15) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/finance/driver-payments?' . http_build_query($q));
        };
    ?>
    <div id="driverPaymentsPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> record(s)</div>
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
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================================
     FOUR-STEP GUIDED PAYMENT MODAL
     Step 1: Choose Driver
     Step 2: Choose Trip
     Step 3: Enter Payment
     Step 4: Confirm & Print Receipt
     ========================================================================= -->
<div class="modal-backdrop-custom" id="guidedPayModalBackdrop" style="display: none;" onclick="handleModalBackdropClick(event, 'guidedPayModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 650px;">
        <div class="modal-header-custom" style="border-bottom: 1px solid #E2E8F0;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(33, 135, 101, 0.12); color: #218765; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900);" id="guidedWizardTitle">Pay Driver</h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.78rem; color: var(--text-muted);" id="guidedWizardSub">4-step guided payment &amp; receipt issuance</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeGuidedPayModal()">&times;</button>
        </div>

        <!-- Wizard Steps Progress Bar -->
        <div style="background: #F8FAFC; padding: 0.65rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
            <div class="wizard-step-item active" id="wStepInd1" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 700; color: #218765;">
                <span style="width: 20px; height: 20px; border-radius: 50%; background: #218765; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">1</span>
                <span>Driver</span>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 0.7rem;"></i>
            <div class="wizard-step-item" id="wStepInd2" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600; color: #64748B;">
                <span style="width: 20px; height: 20px; border-radius: 50%; background: #E2E8F0; color: #64748B; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">2</span>
                <span>Trip</span>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 0.7rem;"></i>
            <div class="wizard-step-item" id="wStepInd3" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600; color: #64748B;">
                <span style="width: 20px; height: 20px; border-radius: 50%; background: #E2E8F0; color: #64748B; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">3</span>
                <span>Payment</span>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 0.7rem;"></i>
            <div class="wizard-step-item" id="wStepInd4" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600; color: #64748B;">
                <span style="width: 20px; height: 20px; border-radius: 50%; background: #E2E8F0; color: #64748B; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">4</span>
                <span>Receipt</span>
            </div>
        </div>

        <form id="guidedPayForm" onsubmit="return handleGuidedPaySubmit(event)" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="dispatch_id" id="guided_dispatch_id" value="">
            
            <div class="modal-body-custom" style="padding: 1.25rem 1.35rem; max-height: 480px; overflow-y: auto;">
                
                <!-- STEP 1: CHOOSE DRIVER -->
                <div class="wizard-step" id="wizardStep1">
                    <label class="form-label" style="font-weight: 700; color: #0F172A; margin-bottom: 0.4rem;">
                        Step 1 — Find &amp; Choose Driver
                    </label>
                    <div style="position: relative; margin-bottom: 0.85rem;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748B;"></i>
                        <input type="text" id="guidedDriverSearchInput" class="form-control" placeholder="Type driver name, mobile number or employee code..." style="padding-left: 2.2rem;" oninput="filterGuidedDrivers()">
                    </div>

                    <div id="guidedDriversList" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 280px; overflow-y: auto; padding-right: 4px;">
                        <!-- Injected dynamically by JS -->
                    </div>
                </div>

                <!-- STEP 2: CHOOSE TRIP -->
                <div class="wizard-step" id="wizardStep2" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; text-transform: uppercase;">Selected Driver:</span>
                            <div style="font-weight: 700; color: #0F172A; font-size: 0.95rem;" id="step2SelectedDriverName">—</div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="goToWizardStep(1)">Change Driver</button>
                    </div>

                    <label class="form-label" style="font-weight: 700; color: #0F172A; margin-bottom: 0.4rem;">
                        Step 2 — Select Trip with Pending Due
                    </label>

                    <div id="guidedTripsList" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 280px; overflow-y: auto;">
                        <!-- Injected dynamically by JS -->
                    </div>
                </div>

                <!-- STEP 3: ENTER PAYMENT -->
                <div class="wizard-step" id="wizardStep3" style="display: none;">
                    <!-- Trip Summary Ribbon -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.75rem 0.9rem; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; text-align: center; font-size: 0.8rem;">
                            <div>
                                <span style="font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Total Trip Earnings</span>
                                <div style="font-weight: 700; color: #0F172A;" id="step3TripTotal">—</div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Previously Paid</span>
                                <div style="font-weight: 700; color: #166534;" id="step3TripPaid">—</div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #B45309; text-transform: uppercase; font-weight: 700;">Still to Pay</span>
                                <div style="font-weight: 800; color: #B45309; font-size: 1rem;" id="step3TripRemaining">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount to Pay -->
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label class="form-label" for="guided_amount" style="font-weight: 700; color: #0F172A; margin: 0;">
                                Amount Being Paid (INR) <span class="req">*</span>
                            </label>
                            <div style="display: flex; gap: 0.35rem;">
                                <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.72rem;" onclick="setPayAmountFull()">Pay Full</button>
                                <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.72rem;" onclick="setPayAmountHalf()">50%</button>
                            </div>
                        </div>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748B; font-size: 1.1rem;">₹</span>
                            <input type="number" step="0.01" min="1" name="amount" id="guided_amount" class="form-control" required style="padding-left: 2rem; font-size: 1.15rem; font-weight: 800; color: #0F172A;" oninput="updateBalanceCalculation()">
                        </div>
                        <!-- Live Calculation Preview -->
                        <div id="liveBalanceCalc" style="font-size: 0.76rem; color: #475569; margin-top: 0.35rem; font-weight: 600;">
                            <!-- Auto-calculated by JS -->
                        </div>
                    </div>

                    <!-- Pay From Account & Payment Method -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                        <div class="form-group">
                            <label class="form-label" for="guided_account_id" style="font-weight: 600;">Pay From Account <span class="req">*</span></label>
                            <select name="account_id" id="guided_account_id" class="form-select" required>
                                <option value="">— Select Account —</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>">
                                        <?= e($acc['account_name']) ?> (<?= format_inr($acc['current_balance']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="guided_payment_method" style="font-weight: 600;">Payment Method <span class="req">*</span></label>
                            <select name="payment_method" id="guided_payment_method" class="form-select" required>
                                <option value="bank_transfer" selected>Bank Transfer (IMPS/NEFT)</option>
                                <option value="upi">UPI / GPay / PhonePe</option>
                                <option value="cash">Cash / Vault</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date & Reference / UTR # -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                        <div class="form-group">
                            <label class="form-label" for="guided_payment_date" style="font-weight: 600;">Payment Date <span class="req">*</span></label>
                            <input type="date" name="payment_date" id="guided_payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="guided_tx_ref" style="font-weight: 600;">Transaction Ref / UTR #</label>
                            <input type="text" name="transaction_ref" id="guided_tx_ref" class="form-control" placeholder="e.g. UTR12345678 or Cash memo">
                        </div>
                    </div>

                    <!-- Notes & Attachment -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.25rem;">
                        <div class="form-group">
                            <label class="form-label" for="guided_notes">Notes / Memo</label>
                            <input type="text" name="notes" id="guided_notes" class="form-control" placeholder="Optional payout notes...">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="guided_attachment">Proof Attachment</label>
                            <input type="file" name="attachment" id="guided_attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp" style="font-size: 0.78rem;">
                        </div>
                    </div>
                </div>

                <!-- STEP 4: CONFIRMATION & RECEIPT -->
                <div class="wizard-step" id="wizardStep4" style="display: none;">
                    <div id="step4ReviewView">
                        <label class="form-label" style="font-weight: 700; color: #0F172A; margin-bottom: 0.4rem;">
                            Step 4 — Review &amp; Confirm Payment
                        </label>
                        <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 1rem 1.15rem; margin-bottom: 1rem;">
                            <table style="width: 100%; font-size: 0.84rem; border-collapse: collapse;">
                                <tr>
                                    <td style="color: #64748B; width: 45%; padding: 4px 0;">Driver Name:</td>
                                    <td style="font-weight: 700; color: #0F172A; padding: 4px 0;" id="revDriverName">—</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; padding: 4px 0;">Trip Code:</td>
                                    <td style="font-weight: 700; color: #B45309; font-family: monospace; padding: 4px 0;" id="revTripCode">—</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; padding: 4px 0;">Pay From Account:</td>
                                    <td style="font-weight: 600; color: #0F172A; padding: 4px 0;" id="revAccount">—</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; padding: 4px 0;">Payment Method:</td>
                                    <td style="font-weight: 600; color: #0F172A; text-transform: capitalize; padding: 4px 0;" id="revMethod">—</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; padding: 4px 0;">Payment Date:</td>
                                    <td style="font-weight: 600; color: #0F172A; padding: 4px 0;" id="revDate">—</td>
                                </tr>
                                <tr style="border-top: 1px solid #E2E8F0;">
                                    <td style="font-weight: 700; color: #0F172A; padding: 8px 0;">Amount to Disburse:</td>
                                    <td style="font-weight: 800; color: #166534; font-size: 1.15rem; font-family: monospace; padding: 8px 0;" id="revAmount">₹0.00</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748B; padding: 4px 0;">Remaining Balance After:</td>
                                    <td style="font-weight: 700; color: #B45309; padding: 4px 0;" id="revRemainingAfter">₹0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Success View (Shown after AJAX submission) -->
                    <div id="step4SuccessView" style="display: none; text-align: center; padding: 1.5rem 1rem;">
                        <div style="width: 58px; height: 58px; border-radius: 50%; background: #DCFCE7; color: #166534; display: inline-flex; align-items: center; justify-content: center; font-size: 1.85rem; margin-bottom: 0.85rem;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h4 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin: 0 0 0.35rem 0;">Payment Successfully Recorded!</h4>
                        <p style="font-size: 0.85rem; color: #475569; margin: 0 0 1.25rem 0;" id="successMsgText">Receipt has been generated and company financial ledger updated.</p>

                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: inline-block;">
                            <span style="font-size: 0.74rem; color: #64748B; text-transform: uppercase;">Receipt Number:</span>
                            <div style="font-size: 1.15rem; font-weight: 800; font-family: monospace; color: #0F172A;" id="successReceiptNumber">—</div>
                        </div>

                        <div style="display: flex; gap: 0.65rem; justify-content: center; flex-wrap: wrap;">
                            <a href="#" target="_blank" class="btn btn-primary" id="successPrintBtn">
                                <i class="fa-solid fa-print"></i> <span>Print Receipt (A4)</span>
                            </a>
                            <a href="#" class="btn btn-outline" id="successViewBtn">
                                <i class="fa-solid fa-file-invoice"></i> <span>View Receipt</span>
                            </a>
                            <button type="button" class="btn btn-outline" onclick="closeGuidedPayModal(); window.location.reload();">
                                <span>Done</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Controls -->
            <div class="modal-footer-custom" id="guidedModalFooter" style="padding: 1rem 1.35rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #E2E8F0; background: #F8FAFC;">
                <button type="button" class="btn btn-outline" id="btnWizardBack" onclick="wizardStepBack()" style="display: none;">
                    &larr; Back
                </button>
                <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeGuidedPayModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnWizardNext" onclick="wizardStepNext()">
                        <span>Next Step &rarr;</span>
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnWizardSubmit" style="display: none; background: #166534; border-color: #166534;">
                        <i class="fa-solid fa-check"></i> <span>Confirm &amp; Record Payment</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     STANDALONE REVERSAL MODAL
     ========================================================================= -->
<div class="modal-backdrop-custom" id="rowReversalModalBackdrop" style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #FFF1F2; border-bottom: 1px solid #FECDD3;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: #991B1B;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #991B1B;">Reverse Driver Payment</h3>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeRowReverseModal()">&times;</button>
        </div>
        <form id="rowReversalForm" method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? url('/office/finance/driver-payments')) ?>">
            <div class="modal-body-custom" style="padding: 1.25rem 1.35rem;">
                <p style="font-size: 0.84rem; color: #334155; line-height: 1.45; margin-bottom: 1rem;">
                    You are reversing payment voucher <strong id="revModalVoucherRef">—</strong> for <strong id="revModalAmount">₹0.00</strong>.
                    <br><br>
                    This will credit funds back to the original company account and restore the unpaid balance on this dispatch.
                </p>
                <div class="form-group">
                    <label class="form-label" for="row_reversal_reason" style="font-weight: 700; color: #0F172A;">
                        Reversal Reason <span class="req">*</span>
                    </label>
                    <textarea name="reason" id="row_reversal_reason" class="form-control" rows="3" placeholder="Provide audit reason for reversal..." required></textarea>
                </div>
            </div>
            <div class="modal-footer-custom" style="padding: 1rem 1.35rem; display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #E2E8F0; background: #F8FAFC;">
                <button type="button" class="btn btn-outline" onclick="closeRowReverseModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" style="background: #DC2626; border-color: #DC2626; color: #FFFFFF;">
                    <i class="fa-solid fa-rotate-left"></i> <span>Confirm Reversal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     RECEIPT INSTALLMENT / VOUCHER MODAL (PMS all-payment-history style)
     ========================================================================= -->
<div id="receiptModal" class="rm-overlay" onclick="if(event.target===this)closeReceiptModal()">
    <div class="rm-dialog">
        <!-- Header -->
        <div class="rm-header">
            <div class="rm-header-text">
                <div class="rm-label">Select Payment Voucher</div>
                <div id="receiptModalTitle" class="rm-title"></div>
            </div>
            <button type="button" onclick="closeReceiptModal()" class="rm-close-btn" aria-label="Close">&times;</button>
        </div>
        <!-- Sub-header hint -->
        <div class="rm-hint">
            <i class="fa-solid fa-circle-info"></i>
            Choose a payment voucher below to print its professional receipt.
        </div>
        <!-- Installment / Voucher list -->
        <div id="receiptInstallmentList" class="rm-list"></div>
    </div>
</div>

<style>
    /* ===== RECEIPT MODAL (PMS Style) ===== */
    .rm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(6px);
        padding: 16px;
    }
    .rm-overlay.open {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .rm-dialog {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 580px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        animation: rmDialogPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes rmDialogPop {
        0% { opacity: 0; transform: scale(0.95) translateY(10px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .rm-header {
        background: linear-gradient(135deg, #10B981, #059669);
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    .rm-header-text { flex: 1; min-width: 0; }
    .rm-label {
        color: rgba(255, 255, 255, 0.85);
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-weight: 700;
    }
    .rm-title {
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rm-close-btn {
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        flex-shrink: 0;
        line-height: 1;
    }
    .rm-close-btn:hover { background: rgba(255, 255, 255, 0.32); }
    .rm-hint {
        padding: 12px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .rm-list {
        padding: 16px;
        overflow-y: auto;
        flex: 1;
    }
    .receipt-item {
        padding: 14px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 10px;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .receipt-item:hover {
        border-color: #10B981;
        background: #f0fdf4;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }
    .receipt-item.reversed {
        border-color: #fecdd3;
        background: #fff1f2;
    }
    .receipt-item:last-child { margin-bottom: 0; }
    .ri-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .ri-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }
    .ri-num {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #10B981, #059669);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 13px;
        flex-shrink: 0;
    }
    .receipt-item.reversed .ri-num {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
    }
    .ri-info { min-width: 0; }
    .ri-amount {
        font-weight: 700;
        color: #0f172a;
        font-size: 14px;
    }
    .ri-date {
        color: #64748b;
        font-size: 12px;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ri-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .ri-method-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .ri-stamp-label {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        font-size: 12px;
        color: #475569;
        font-weight: 600;
        white-space: nowrap;
        user-select: none;
    }
    .ri-stamp-label input {
        width: 15px;
        height: 15px;
        accent-color: #10B981;
        cursor: pointer;
    }
    .ri-print-btn {
        background: linear-gradient(135deg, #10B981, #059669);
        border: none;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        transition: opacity 0.2s, transform 0.15s;
    }
    .ri-print-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    @media (max-width: 480px) {
        .rm-dialog { border-radius: 12px; max-height: 95vh; }
        .rm-header { padding: 14px 16px; }
        .rm-title { font-size: 0.88rem; }
        .rm-hint { font-size: 12px; padding: 10px 16px; }
        .rm-list { padding: 12px; }
        .receipt-item { padding: 12px; }
        .ri-right { width: 100%; justify-content: flex-start; margin-top: 8px; }
    }

    .modern-table tbody tr.vouchers-expand-row:hover {
        background: #F8FAFC !important;
        transform: none !important;
        box-shadow: none !important;
    }
    .driver-card-select-item, .trip-card-select-item {
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        padding: 0.75rem 0.95rem;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #FFFFFF;
    }
    .driver-card-select-item:hover, .trip-card-select-item:hover {
        border-color: #218765;
        background: #F0FDF4;
    }
    .driver-card-select-item.selected, .trip-card-select-item.selected {
        border-color: #218765;
        background: #F0FDF4;
        box-shadow: 0 0 0 2px rgba(33, 135, 101, 0.2);
    }
</style>

<script>
    // Global state for 4-step wizard
    var currentStep = 1;
    var selectedDriverObj = null;
    var selectedTripObj = null;
    var allDriversCache = [];

    function openGuidedPayModal() {
        currentStep = 1;
        selectedDriverObj = null;
        selectedTripObj = null;
        
        document.getElementById('step4ReviewView').style.display = 'block';
        document.getElementById('step4SuccessView').style.display = 'none';
        document.getElementById('guidedModalFooter').style.display = 'flex';
        document.getElementById('guidedPayForm').reset();
        
        goToWizardStep(1);
        loadGuidedDrivers();
        
        var backdrop = document.getElementById('guidedPayModalBackdrop');
        backdrop.style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function closeGuidedPayModal() {
        document.getElementById('guidedPayModalBackdrop').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    function goToWizardStep(step) {
        currentStep = step;
        for (var i = 1; i <= 4; i++) {
            var stepEl = document.getElementById('wizardStep' + i);
            var indEl = document.getElementById('wStepInd' + i);
            if (stepEl) stepEl.style.display = (i === step) ? 'block' : 'none';
            if (indEl) {
                if (i < step) {
                    indEl.style.color = '#166534';
                    indEl.querySelector('span').style.background = '#166534';
                    indEl.querySelector('span').innerHTML = '<i class="fa-solid fa-check" style="font-size: 0.6rem;"></i>';
                } else if (i === step) {
                    indEl.style.color = '#218765';
                    indEl.querySelector('span').style.background = '#218765';
                    indEl.querySelector('span').textContent = i;
                } else {
                    indEl.style.color = '#64748B';
                    indEl.querySelector('span').style.background = '#E2E8F0';
                    indEl.querySelector('span').textContent = i;
                }
            }
        }

        var btnBack = document.getElementById('btnWizardBack');
        var btnNext = document.getElementById('btnWizardNext');
        var btnSubmit = document.getElementById('btnWizardSubmit');

        btnBack.style.display = (step > 1 && step < 4) ? 'inline-flex' : (step === 4 && document.getElementById('step4ReviewView').style.display !== 'none' ? 'inline-flex' : 'none');
        btnNext.style.display = (step < 4) ? 'inline-flex' : 'none';
        btnSubmit.style.display = (step === 4 && document.getElementById('step4ReviewView').style.display !== 'none') ? 'inline-flex' : 'none';

        if (step === 1) {
            btnNext.disabled = !selectedDriverObj;
        } else if (step === 2) {
            btnNext.disabled = !selectedTripObj;
        } else if (step === 3) {
            btnNext.disabled = false;
        }
    }

    function wizardStepBack() {
        if (currentStep > 1) {
            goToWizardStep(currentStep - 1);
        }
    }

    function wizardStepNext() {
        if (currentStep === 1) {
            if (!selectedDriverObj) {
                alert('Please select a driver first.');
                return;
            }
            goToWizardStep(2);
            loadDriverTrips(selectedDriverObj.driver_id);
        } else if (currentStep === 2) {
            if (!selectedTripObj) {
                alert('Please select a trip to pay.');
                return;
            }
            setupStep3Payment();
            goToWizardStep(3);
        } else if (currentStep === 3) {
            if (!validateStep3()) return;
            setupStep4Review();
            goToWizardStep(4);
        }
    }

    function loadGuidedDrivers() {
        var listContainer = document.getElementById('guidedDriversList');
        listContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #64748B;"><i class="fa-solid fa-spinner fa-spin"></i> Loading drivers...</div>';
        
        fetch('<?= url("/office/finance/driver-payments/api/search-drivers") ?>')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.drivers) {
                    allDriversCache = data.drivers;
                    renderGuidedDrivers(allDriversCache);
                } else {
                    listContainer.innerHTML = '<div style="padding: 1rem; color: #EF4444;">Failed to load drivers list.</div>';
                }
            })
            .catch(function(err) {
                listContainer.innerHTML = '<div style="padding: 1rem; color: #EF4444;">Error connecting to server.</div>';
            });
    }

    function filterGuidedDrivers() {
        var q = document.getElementById('guidedDriverSearchInput').value.toLowerCase().trim();
        if (!q) {
            renderGuidedDrivers(allDriversCache);
            return;
        }
        var filtered = allDriversCache.filter(function(d) {
            return (d.driver_name && d.driver_name.toLowerCase().includes(q)) ||
                   (d.driver_phone && d.driver_phone.toLowerCase().includes(q)) ||
                   (d.employee_code && d.employee_code.toLowerCase().includes(q)) ||
                   (d.license_number && d.license_number.toLowerCase().includes(q));
        });
        renderGuidedDrivers(filtered);
    }

    function renderGuidedDrivers(drivers) {
        var listContainer = document.getElementById('guidedDriversList');
        if (!drivers || drivers.length === 0) {
            listContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #64748B;">No drivers found matching your search.</div>';
            return;
        }

        var html = '';
        drivers.forEach(function(d) {
            var isSel = selectedDriverObj && selectedDriverObj.driver_id === d.driver_id;
            var dues = parseFloat(d.total_still_to_pay || 0);
            var tripsCnt = parseInt(d.pending_trips_count || 0);

            html += '<div class="driver-card-select-item ' + (isSel ? 'selected' : '') + '" onclick="selectDriverItem(' + d.driver_id + ')">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center;">' +
                            '<div style="display: flex; align-items: center; gap: 0.65rem;">' +
                                '<div style="width: 36px; height: 36px; border-radius: 50%; background: #E2E8F0; color: #0F172A; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;">' +
                                    (d.driver_name ? d.driver_name.charAt(0).toUpperCase() : 'D') +
                                '</div>' +
                                '<div>' +
                                    '<div style="font-weight: 700; color: #0F172A; font-size: 0.88rem;">' + (d.driver_name || 'Driver') + '</div>' +
                                    '<div style="font-size: 0.74rem; color: #64748B;">' +
                                        (d.employee_code ? d.employee_code + ' &bull; ' : '') + (d.driver_phone || '') +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div style="text-align: right;">' +
                                '<div style="font-weight: 800; font-size: 0.88rem; color: ' + (dues > 0 ? '#B45309' : '#166534') + ';">₹' + dues.toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div>' +
                                '<div style="font-size: 0.72rem; color: #64748B;">' + (tripsCnt > 0 ? tripsCnt + ' pending trip(s)' : 'No pending dues') + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
        });

        listContainer.innerHTML = html;
    }

    function selectDriverItem(driverId) {
        var found = allDriversCache.find(function(d) { return d.driver_id == driverId; });
        if (!found) return;
        selectedDriverObj = found;
        renderGuidedDrivers(allDriversCache);
        document.getElementById('btnWizardNext').disabled = false;
        // Auto advance to step 2 for convenience
        wizardStepNext();
    }

    function loadDriverTrips(driverId) {
        document.getElementById('step2SelectedDriverName').textContent = selectedDriverObj.driver_name + (selectedDriverObj.employee_code ? ' (' + selectedDriverObj.employee_code + ')' : '');
        var tripsContainer = document.getElementById('guidedTripsList');
        tripsContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #64748B;"><i class="fa-solid fa-spinner fa-spin"></i> Loading completed trips for driver...</div>';

        fetch('<?= url("/office/finance/driver-payments/api/driver-trips?driver_id=") ?>' + driverId)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.trips && data.trips.length > 0) {
                    renderDriverTrips(data.trips);
                } else {
                    tripsContainer.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #64748B;">' +
                        '<i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>' +
                        'No pending trip compensations for this driver.<br>All completed trips are fully settled.</div>';
                }
            })
            .catch(function(err) {
                tripsContainer.innerHTML = '<div style="padding: 1rem; color: #EF4444;">Error fetching driver trips.</div>';
            });
    }

    function renderDriverTrips(trips) {
        var container = document.getElementById('guidedTripsList');
        var html = '';
        trips.forEach(function(t) {
            var rem = parseFloat(t.remaining_amount || 0);
            var earned = parseFloat(t.driver_trip_amount || 0);
            var paid = parseFloat(t.driver_paid_amount || 0);
            var isSel = selectedTripObj && selectedTripObj.dispatch_id === t.dispatch_id;

            html += '<div class="trip-card-select-item ' + (isSel ? 'selected' : '') + '" onclick="selectTripItem(' + htmlspecialchars(JSON.stringify(t)) + ')">' +
                        '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">' +
                            '<div>' +
                                '<strong style="color: #B45309; font-family: monospace; font-size: 0.9rem;">' + (t.dispatch_code || '#' + t.dispatch_id) + '</strong>' +
                                '<span style="font-size: 0.74rem; color: #64748B; margin-left: 6px;">' + (t.booking_code ? 'BK: ' + t.booking_code : '') + '</span>' +
                            '</div>' +
                            '<div style="text-align: right;">' +
                                '<span style="font-size: 0.7rem; color: #64748B; text-transform: uppercase;">Still to Pay:</span>' +
                                '<div style="font-weight: 800; font-size: 0.95rem; color: #B45309;">₹' + rem.toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div style="font-size: 0.76rem; color: #334155; margin-bottom: 0.25rem;">' +
                            (t.pickup_address || 'Start') + ' &rarr; ' + (t.drop_address || 'End') +
                        '</div>' +
                        '<div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: #64748B; border-top: 1px dashed #E2E8F0; padding-top: 4px;">' +
                            '<span>Total: ₹' + earned.toFixed(2) + ' &bull; Paid: ₹' + paid.toFixed(2) + '</span>' +
                            '<span>' + (t.trip_date || '') + (t.vehicle_number ? ' &bull; ' + t.vehicle_number : '') + '</span>' +
                        '</div>' +
                    '</div>';
        });
        container.innerHTML = html;
    }

    function selectTripItem(trip) {
        selectedTripObj = trip;
        var items = document.querySelectorAll('.trip-card-select-item');
        items.forEach(function(el) { el.classList.remove('selected'); });
        if (event && event.currentTarget) event.currentTarget.classList.add('selected');
        document.getElementById('btnWizardNext').disabled = false;
        // Auto advance to Step 3
        wizardStepNext();
    }

    function setupStep3Payment() {
        document.getElementById('guided_dispatch_id').value = selectedTripObj.dispatch_id;
        var total = parseFloat(selectedTripObj.driver_trip_amount || 0);
        var paid = parseFloat(selectedTripObj.driver_paid_amount || 0);
        var rem = parseFloat(selectedTripObj.remaining_amount || (total - paid));

        document.getElementById('step3TripTotal').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('step3TripPaid').textContent = '₹' + paid.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('step3TripRemaining').textContent = '₹' + rem.toLocaleString('en-IN', {minimumFractionDigits: 2});

        var amtInput = document.getElementById('guided_amount');
        amtInput.value = rem.toFixed(2);
        amtInput.max = rem.toFixed(2);
        updateBalanceCalculation();
    }

    function setPayAmountFull() {
        if (!selectedTripObj) return;
        var rem = parseFloat(selectedTripObj.remaining_amount || 0);
        document.getElementById('guided_amount').value = rem.toFixed(2);
        updateBalanceCalculation();
    }

    function setPayAmountHalf() {
        if (!selectedTripObj) return;
        var rem = parseFloat(selectedTripObj.remaining_amount || 0);
        document.getElementById('guided_amount').value = (rem / 2).toFixed(2);
        updateBalanceCalculation();
    }

    function updateBalanceCalculation() {
        if (!selectedTripObj) return;
        var total = parseFloat(selectedTripObj.driver_trip_amount || 0);
        var paid = parseFloat(selectedTripObj.driver_paid_amount || 0);
        var rem = parseFloat(selectedTripObj.remaining_amount || (total - paid));
        var entered = parseFloat(document.getElementById('guided_amount').value || 0);

        var previewRemaining = Math.max(0, rem - entered);
        var previewTotalPaid = paid + entered;

        var el = document.getElementById('liveBalanceCalc');
        if (entered > rem + 0.009) {
            el.innerHTML = '<span style="color: #DC2626;"><i class="fa-solid fa-triangle-exclamation"></i> Amount exceeds remaining balance (₹' + rem.toFixed(2) + '). Overpayment is not allowed.</span>';
        } else if (entered <= 0 || isNaN(entered)) {
            el.innerHTML = '<span style="color: #64748B;">Enter a positive payment amount.</span>';
        } else {
            var statusLabel = (previewRemaining <= 0.009) ? '<span style="color: #166534;">Fully Settled</span>' : '<span style="color: #B45309;">Part Payment</span>';
            el.innerHTML = 'New Total Paid: <strong>₹' + previewTotalPaid.toFixed(2) + '</strong> &bull; Still to Pay: <strong>₹' + previewRemaining.toFixed(2) + '</strong> (' + statusLabel + ')';
        }
    }

    function validateStep3() {
        var entered = parseFloat(document.getElementById('guided_amount').value || 0);
        var rem = parseFloat(selectedTripObj.remaining_amount || 0);
        if (isNaN(entered) || entered <= 0) {
            alert('Please enter a valid disbursement amount greater than zero.');
            return false;
        }
        if (entered > (rem + 0.009)) {
            alert('Payment amount (₹' + entered.toFixed(2) + ') cannot exceed current remaining due (₹' + rem.toFixed(2) + ').');
            return false;
        }
        var acc = document.getElementById('guided_account_id').value;
        if (!acc) {
            alert('Please select an account to pay from.');
            return false;
        }
        return true;
    }

    function setupStep4Review() {
        var entered = parseFloat(document.getElementById('guided_amount').value || 0);
        var rem = parseFloat(selectedTripObj.remaining_amount || 0);
        var remAfter = Math.max(0, rem - entered);

        document.getElementById('revDriverName').textContent = selectedDriverObj.driver_name;
        document.getElementById('revTripCode').textContent = selectedTripObj.dispatch_code || '#' + selectedTripObj.dispatch_id;
        
        var accSelect = document.getElementById('guided_account_id');
        document.getElementById('revAccount').textContent = accSelect.options[accSelect.selectedIndex].text;
        
        var methSelect = document.getElementById('guided_payment_method');
        document.getElementById('revMethod').textContent = methSelect.options[methSelect.selectedIndex].text;
        
        document.getElementById('revDate').textContent = document.getElementById('guided_payment_date').value;
        document.getElementById('revAmount').textContent = '₹' + entered.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('revRemainingAfter').textContent = '₹' + remAfter.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }

    function handleGuidedPaySubmit(e) {
        e.preventDefault();
        var btn = document.getElementById('btnWizardSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Recording...';

        var formData = new FormData(document.getElementById('guidedPayForm'));

        fetch('<?= url("/office/finance/driver-payments/pay") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('step4ReviewView').style.display = 'none';
                document.getElementById('step4SuccessView').style.display = 'block';
                document.getElementById('guidedModalFooter').style.display = 'none';
                document.getElementById('successReceiptNumber').textContent = data.receipt_number;
                document.getElementById('successPrintBtn').href = data.print_url;
                document.getElementById('successViewBtn').href = data.receipt_url;
            } else {
                alert(data.message || 'Payment recording failed.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Confirm &amp; Record Payment';
            }
        })
        .catch(function(err) {
            alert('Error communicating with server.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Confirm &amp; Record Payment';
        });

        return false;
    }

    // Direct Quick Payment from Row
    function openDirectPayModal(data) {
        openGuidedPayModal();
        selectedDriverObj = {
            driver_id: data.driver_id,
            driver_name: data.driver_name,
            total_still_to_pay: data.remaining_amount
        };
        selectedTripObj = {
            dispatch_id: data.dispatch_id,
            dispatch_code: data.dispatch_code,
            driver_trip_amount: data.driver_trip_amount,
            driver_paid_amount: data.driver_paid_amount,
            remaining_amount: data.remaining_amount,
            trip_date: data.trip_date
        };
        setupStep3Payment();
        goToWizardStep(3);
    }

    function toggleVouchersRow(dispatchId) {
        var row = document.getElementById('vouchers-row-' + dispatchId);
        if (row) {
            row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
        }
    }

    function openRowReverseModal(voucherId, voucherRef, amount) {
        document.getElementById('revModalVoucherRef').textContent = voucherRef;
        document.getElementById('revModalAmount').textContent = '₹' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('rowReversalForm').action = '<?= url("/office/finance/driver-payments/reverse/") ?>' + voucherId;
        document.getElementById('rowReversalModalBackdrop').style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function closeRowReverseModal() {
        document.getElementById('rowReversalModalBackdrop').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        if (!p) return;
        p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    // ===== PMS RECEIPT MODAL LOGIC =====
    function openReceiptModal(dispatchId, dispatchCode, driverName) {
        document.querySelectorAll('.action-dropdown-menu.show, .action-dropdown-menu').forEach(function(p) {
            p.classList.remove('show');
            p.style.display = 'none';
        });
        
        var modal = document.getElementById('receiptModal');
        document.getElementById('receiptModalTitle').textContent = dispatchCode + ' — ' + driverName;
        document.getElementById('receiptInstallmentList').innerHTML = '<div style="text-align:center;padding:30px;color:#64748b;"><i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i><div style="margin-top:8px;">Loading payment vouchers...</div></div>';
        
        modal.classList.add('open');
        document.body.classList.add('modal-open');
        
        fetch('<?= url("/office/finance/driver-payments/api/dispatch-vouchers") ?>?dispatch_id=' + encodeURIComponent(dispatchId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success || !data.installments || !data.installments.length) {
                    document.getElementById('receiptInstallmentList').innerHTML = '<div style="text-align:center;padding:30px;color:#94a3b8;"><i class="fa-solid fa-inbox" style="font-size:28px;"></i><div style="margin-top:8px;font-weight:600;">No payment vouchers found for this trip.</div><p style="font-size:12px;color:#cbd5e1;margin-top:4px;">Record a payment first to generate a printable receipt.</p></div>';
                    return;
                }
                
                var methodLabels = {
                    cash: 'Cash',
                    bank_transfer: 'Bank Transfer',
                    neft: 'NEFT / RTGS',
                    upi: 'UPI',
                    cheque: 'Cheque / Draft',
                    card: 'Card'
                };
                var methodStyles = {
                    cash:          'background:#D1FAE5;color:#065F46;',
                    bank_transfer: 'background:#DBEAFE;color:#1E40AF;',
                    neft:          'background:#DBEAFE;color:#1E40AF;',
                    upi:           'background:#E0E7FF;color:#3730A3;',
                    cheque:        'background:#F3E8FF;color:#6B21A8;',
                    card:          'background:#FEF3C7;color:#92400E;'
                };
                
                var html = '';
                data.installments.forEach(function(inst, i) {
                    var isRev = (inst.status === 'reversed');
                    var mStyle = methodStyles[inst.payment_method] || 'background:#F1F5F9;color:#475569;';
                    var label = methodLabels[inst.payment_method] || (inst.payment_method ? inst.payment_method.replace(/_/g, ' ') : 'Payment');
                    var cbId = 'stamp_cb_' + inst.id;
                    var numVal = Number(inst.amount || inst.payment_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    
                    html += '<div class="receipt-item ' + (isRev ? 'reversed' : '') + '">' +
                        '<div class="ri-row">' +
                            '<div class="ri-left">' +
                                '<div class="ri-num">' + (inst.inst_no || (i + 1)) + '</div>' +
                                '<div class="ri-info">' +
                                    '<div class="ri-amount">₹' + numVal + (isRev ? ' <span style="font-size:10px;color:#dc2626;font-weight:700;">(REVERSED)</span>' : '') + '</div>' +
                                    '<div class="ri-date"><i class="fa-regular fa-calendar"></i> ' + (inst.payment_date_fmt || inst.payment_date) + ' &bull; ' + (inst.receipt_number || inst.payment_reference) + '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="ri-right">' +
                                '<span class="ri-method-badge" style="' + mStyle + '">' + label + '</span>' +
                                (!isRev ? 
                                    '<label class="ri-stamp-label" onclick="event.stopPropagation()">' +
                                        '<input type="checkbox" id="' + cbId + '" checked onclick="event.stopPropagation()"> ' +
                                        'Stamp' +
                                    '</label>' : '') +
                                '<button class="ri-print-btn" onclick="printReceipt(' + inst.id + ', ' + (!isRev ? ('document.getElementById(\'' + cbId + '\').checked') : 'false') + ')" title="Print Receipt">' +
                                    '<i class="fa-solid fa-print"></i> Print' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                });
                
                document.getElementById('receiptInstallmentList').innerHTML = html;
            })
            .catch(function(err) {
                document.getElementById('receiptInstallmentList').innerHTML = '<div style="text-align:center;padding:30px;color:#ef4444;"><i class="fa-solid fa-triangle-exclamation" style="font-size:24px;"></i><div style="margin-top:8px;">Failed to load payment vouchers.</div></div>';
            });
    }

    function closeReceiptModal() {
        var modal = document.getElementById('receiptModal');
        if (modal) modal.classList.remove('open');
        document.body.classList.remove('modal-open');
    }

    function printReceipt(voucherId, showStamp) {
        var stampParam = (showStamp === false || showStamp === 0 || showStamp === '0') ? '&stamp=0' : '&stamp=1';
        var url = '<?= url("/office/finance/driver-payments/receipt/") ?>' + voucherId + '/print?' + (stampParam.replace(/^&/, ''));
        window.open(url, '_blank');
    }
</script>
