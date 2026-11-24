<?php
/**
 * CUSTOMER PENDING PAYMENTS & RECEIVABLES
 * Canonical Office Design System & Shared Responsive Architecture
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/reports/customer-dues') . '" class="btn btn-outline"><i class="fa-solid fa-clock-rotate-left"></i> <span>Aging Report</span></a>'
    . '<a href="' . url('/office/invoices/create') . '" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <span>Create Invoice</span></a>'
    . '</div>';

$selectedStatus = $selectedStatus ?? '';
$search         = $search ?? '';
$dateFrom       = $dateFrom ?? '';
$dateTo         = $dateTo ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($invoices);
$currP          = $currentPage ?? 1;

$makeFilterUrl = function(array $overrides = []) use ($search, $selectedStatus, $dateFrom, $dateTo, $perPage) {
    $params = [
        'search'    => $search,
        'status'    => $selectedStatus,
        'date_from' => $dateFrom,
        'date_to'   => $dateTo,
        'per_page'  => $perPage,
    ];
    $merged = array_merge($params, $overrides);
    return url('/office/finance/pending-payments?' . http_build_query(array_filter($merged, fn($v) => $v !== '' && $v !== null)));
};
?>

<div class="bookings-page-view finance-page-view" id="pendingPaymentsModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Pending Payments',
            'description' => 'Real-time accounts receivable, outstanding customer balances, pending trip collections, and payment dues.',
            'badge'       => 'Accounts Receivable',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Pending Payments', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="pendingKpiGrid">
        <!-- Card 1: Total Pending Dues -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Customer Dues</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($totalDues) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totRec) ?> pending invoices</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Overdue Amounts -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/customer-dues') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Overdue Balance</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($overdueDues) ?></div>
            <div class="booking-kpi-sub">
                <span>Passed settlement due date</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Unpaid Invoices Count -->
        <div class="booking-kpi-card <?= $selectedStatus === 'unpaid' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments?status=unpaid') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Zero Payment Received</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= number_format($unpaidCount) ?></div>
            <div class="booking-kpi-sub">
                <span>Completely unpaid invoices</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Partially Paid Invoices Count -->
        <div class="booking-kpi-card <?= $selectedStatus === 'partial' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments?status=partial') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Partially Paid</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= number_format($partialCount) ?></div>
            <div class="booking-kpi-sub">
                <span>Advance deposit received</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back to Finance">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Pending Receivables</h2>
            </div>
            <a href="<?= url('/office/finance/reports/customer-dues') ?>" class="mv-add-btn" style="background: rgba(22, 68, 59, 0.08); color: var(--brand-primary);">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Aging</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/pending-payments') ?>" class="mv-search-form" id="mvPendingSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search customer, phone, invoice #..." value="<?= e($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= $makeFilterUrl(['search' => '']) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle <?= (!empty($selectedStatus) || !empty($dateFrom) || !empty($dateTo)) ? 'active' : '' ?>" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
                <?php if (!empty($selectedStatus)): ?><input type="hidden" name="status" value="<?= e($selectedStatus) ?>"><?php endif; ?>
                <?php if (!empty($dateFrom)): ?><input type="hidden" name="date_from" value="<?= e($dateFrom) ?>"><?php endif; ?>
                <?php if (!empty($dateTo)): ?><input type="hidden" name="date_to" value="<?= e($dateTo) ?>"><?php endif; ?>
                <?php if ($perPage != 15): ?><input type="hidden" name="per_page" value="<?= (int)$perPage ?>"><?php endif; ?>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/pending-payments') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Status:</label>
                            <select name="status" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Pending Dues</option>
                                <option value="unpaid" <?= $selectedStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid (No Payment)</option>
                                <option value="partial" <?= $selectedStatus === 'partial' ? 'selected' : '' ?>>Partially Paid (Advance Given)</option>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Due From:</label>
                                <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="font-size: 0.82rem; height: 38px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Due To:</label>
                                <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="font-size: 0.82rem; height: 38px;">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Per Page:</label>
                            <select name="per_page" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <?php foreach ([10, 15, 25, 50] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="<?= url('/office/finance/pending-payments') ?>" class="btn btn-outline btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Scroll Pills -->
        <div class="mv-status-scroll">
            <a href="<?= $makeFilterUrl(['status' => '']) ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Pending</span>
                <span class="mv-pill-cnt"><?= (int)$totRec ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'unpaid']) ?>" class="mv-pill <?= $selectedStatus === 'unpaid' ? 'active' : '' ?>">
                <span>Unpaid</span>
                <span class="mv-pill-cnt"><?= (int)($unpaidCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'partial']) ?>" class="mv-pill <?= $selectedStatus === 'partial' ? 'active' : '' ?>">
                <span>Partial</span>
                <span class="mv-pill-cnt"><?= (int)($partialCount ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 4. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/pending-payments') ?>" class="filter-bar-compact" id="pendingFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search customer, phone, invoice #..." value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= $makeFilterUrl(['search' => '']) ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Pending Dues</option>
            <option value="unpaid" <?= $selectedStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid (No Payment)</option>
            <option value="partial" <?= $selectedStatus === 'partial' ? 'selected' : '' ?>>Partially Paid (Advance Given)</option>
        </select>

        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="From Due Date">
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="To Due Date">

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <a href="<?= url('/office/finance/pending-payments') ?>" class="fbc-btn" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 5. Modern Table & Mobile Cards -->
    <section class="recent-bookings-section" id="pendingTableSection">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-file-invoice-dollar" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Pending Customer Invoices
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Invoices:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Booking Ref removed, Priority order matched) -->
        <div class="modern-table-container">
            <table class="modern-table" id="pendingTable">
                <thead>
                    <tr>
                        <th class="table-col-index text-center col-priority-1">#</th>
                        <th class="table-col-name col-priority-1">Customer Details</th>
                        <th class="table-col-code col-priority-1">Invoice #</th>
                        <th class="table-col-date col-priority-2" data-column-priority="secondary">Due Date</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Total Amount</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Received Amount</th>
                        <th class="table-col-amount col-priority-1 text-end">Remaining Due</th>
                        <th class="table-col-status text-center col-priority-1">Status</th>
                        <th class="table-col-actions text-end col-priority-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-circle-check" style="color: #218765;"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">All Customer Payments Are Clear!</h4>
                                <p class="text-muted" style="margin:0;">There are no outstanding customer receivables matching your current filter.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $i => $inv):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $rem = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                            $isOverdue = !empty($inv['due_date']) && strtotime($inv['due_date']) < strtotime(date('Y-m-d'));
                            $status = strtolower($inv['payment_status'] ?? 'unpaid');
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="table-col-index text-center col-priority-1">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Customer -->
                            <td class="table-col-name col-priority-1">
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($inv['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($inv['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?= e($inv['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 3. Invoice # -->
                            <td class="table-col-code col-priority-1">
                                <a href="<?= url('/office/finance/pending-payments/' . $inv['id']) ?>" style="font-weight: 600; color: #4F46E5; text-decoration: none; font-size: 0.84rem;">
                                    <?= e($inv['invoice_number']) ?>
                                </a>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= format_date($inv['issued_at'] ?? $inv['created_at']) ?></div>
                            </td>

                            <!-- 4. Due Date -->
                            <td class="table-col-date col-priority-2" data-column-priority="secondary">
                                <?php if (!empty($inv['due_date'])): ?>
                                    <span style="font-size: 0.84rem; font-weight: 600; color: <?= $isOverdue ? '#D95757' : 'var(--vg-navy-900)' ?>;">
                                        <?= format_date($inv['due_date']) ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <div style="font-size: 0.7rem; color: #D95757; font-weight: 700; text-transform: uppercase;">Overdue</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.82rem;">On Arrival</span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Total Amount -->
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_inr((float)$inv['total_amount']) ?>
                            </td>

                            <!-- 6. Received Amount -->
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.88rem; font-weight: 600; color: #218765;">
                                <?= format_inr((float)$inv['paid_amount']) ?>
                            </td>

                            <!-- 7. Remaining Balance Due -->
                            <td class="table-col-amount col-priority-1 text-end">
                                <strong style="font-size: 0.98rem; color: #C78A35;">
                                    <?= format_inr($rem) ?>
                                </strong>
                            </td>

                            <!-- 8. Status -->
                            <td class="table-col-status text-center col-priority-1">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $status === 'partial' ? 'background: rgba(199, 138, 53, 0.12); color: #C78A35;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>

                            <!-- 9. Actions (One Single Professional Actions Control) -->
                            <td class="table-col-actions text-end col-priority-1" style="white-space: nowrap;">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActionDropdown(this, event)" title="Actions" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/finance/pending-payments/' . $inv['id']) ?>" class="action-dropdown-item view">
                                            <i class="fa-solid fa-eye"></i> <span>View Details</span>
                                        </a>
                                        <a href="<?= url('/office/finance/pending-payments/' . $inv['id'] . '/pay') ?>" class="action-dropdown-item pay">
                                            <i class="fa-solid fa-credit-card"></i> <span>Record Payment</span>
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-file-invoice"></i> <span>View Invoice</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens (Canonical Self-Contained Cards) -->
        <div class="cards-grid">
            <?php foreach ($invoices as $inv):
                $rem = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                $isOverdue = !empty($inv['due_date']) && strtotime($inv['due_date']) < strtotime(date('Y-m-d'));
                $status = strtolower($inv['payment_status'] ?? 'unpaid');
                $statusBadgeClass = match($status) {
                    'paid' => 'mv-status-paid',
                    'partial' => 'mv-status-partial',
                    default => 'mv-status-unpaid'
                };
                $statusText = $status === 'partial' ? 'Partial' : 'Unpaid';
            ?>
            <div class="mv-record-card">
                <!-- 1. Card Header: Invoice # on Left, Status Badge on Right -->
                <div class="mv-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="<?= url('/office/finance/pending-payments/' . $inv['id']) ?>" class="mv-card-id" title="View Invoice <?= e($inv['invoice_number']) ?>">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: var(--brand-primary, #176B5B);"></i>
                        <span><?= e($inv['invoice_number']) ?></span>
                    </a>
                    <span class="mv-status-badge <?= $statusBadgeClass ?>"><?= $statusText ?></span>
                </div>

                <!-- 2. Main Customer Information -->
                <div class="mv-card-body">
                    <div class="mv-card-primary-title"><?= e($inv['customer_name'] ?? 'Direct Customer') ?></div>
                    <?php if (!empty($inv['customer_phone'])): ?>
                        <div class="mv-card-meta-row">
                            <i class="fa-solid fa-phone"></i>
                            <a href="tel:<?= e($inv['customer_phone']) ?>" class="mv-card-meta-link"><?= e($inv['customer_phone']) ?></a>
                        </div>
                    <?php endif; ?>

                    <!-- 3. Due Date & Outstanding Balance -->
                    <div class="mv-card-meta-row">
                        <i class="fa-regular fa-calendar-days"></i>
                        <span>Due: <?= !empty($inv['due_date']) ? format_date($inv['due_date']) : 'On Arrival' ?></span>
                        <?php if ($isOverdue): ?>
                            <span class="mv-card-overdue-tag">Overdue</span>
                        <?php endif; ?>
                    </div>
                    <div class="mv-card-due-highlight">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>Remaining Due: <strong><?= format_inr($rem) ?></strong></span>
                    </div>

                    <!-- 4. Linked Booking Reference -->
                    <?php if (!empty($inv['booking_code'])): ?>
                        <div class="mv-card-meta-row">
                            <i class="fa-solid fa-ticket"></i>
                            <span>Booking: </span>
                            <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" class="mv-card-ref-link" title="Linked Booking #<?= e($inv['booking_code']) ?>">
                                #<?= e($inv['booking_code']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 5. Footer: Total Amount -->
                <div class="mv-card-footer">
                    <div class="mv-card-total-box">
                        <span class="mv-card-total-lbl">Invoice Total</span>
                        <span class="mv-card-total-val"><?= format_inr((float)$inv['total_amount']) ?></span>
                    </div>
                </div>

                <!-- 6. Mobile Card Actions (Single Horizontal Row) -->
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" aria-haspopup="true" aria-expanded="false" title="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                        </button>
                        <div class="action-dropdown-menu">
                            <a href="<?= url('/office/finance/pending-payments/' . $inv['id']) ?>" class="action-dropdown-item view">
                                <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                            </a>
                            <a href="<?= url('/office/finance/pending-payments/' . $inv['id'] . '/pay') ?>" class="action-dropdown-item pay">
                                <i class="fa-solid fa-credit-card text-success"></i> <span>Record Payment</span>
                            </a>
                            <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-dropdown-item">
                                <i class="fa-solid fa-file-invoice text-info"></i> <span>View Invoice</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modern Pagination -->
    <?php
        $totalPages = $totalPages ?? 1;
        $from = $totRec > 0 ? (($currP - 1) * $perPage) + 1 : 0;
        $to   = min($currP * $perPage, $totRec);
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> pending invoice(s)</div>
        <div class="pagination">
            <a href="<?= $makeFilterUrl(['page' => max(1, $currP - 1)]) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous">&#10094;</a>
            <?php
                $start = max(1, $currP - 2);
                $end = min($totalPages, $currP + 2);
                for ($p = $start; $p <= $end; $p++):
            ?>
                <a href="<?= $makeFilterUrl(['page' => $p]) ?>" class="<?= $p === $currP ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <a href="<?= $makeFilterUrl(['page' => min($totalPages, $currP + 1)]) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        if (!p) return;
        p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    }
</script>
