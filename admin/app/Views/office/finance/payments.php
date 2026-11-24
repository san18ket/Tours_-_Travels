<?php
/**
 * CUSTOMER PAYMENT HISTORY
 * Exactly matching Customer, Employee, and Booking Reference UI Patterns
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/invoices') . '" class="btn btn-outline"><i class="fa-solid fa-file-invoice"></i> <span>Manage Invoices</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openPaymentModal()"><i class="fa-solid fa-plus"></i> <span>Record Payment</span></button>'
    . '</div>';

$selectedMethod = $selectedMethod ?? '';
$search         = $search ?? '';
$dateFrom       = $dateFrom ?? '';
$dateTo         = $dateTo ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($payments);
$currP          = $currentPage ?? 1;

$makeFilterUrl = function(array $overrides = []) use ($search, $selectedMethod, $dateFrom, $dateTo, $perPage) {
    $params = [
        'search'    => $search,
        'method'    => $selectedMethod,
        'date_from' => $dateFrom,
        'date_to'   => $dateTo,
        'per_page'  => $perPage,
    ];
    $merged = array_merge($params, $overrides);
    return url('/office/finance/payments?' . http_build_query(array_filter($merged, fn($v) => $v !== '' && $v !== null)));
};
?>

<div class="bookings-page-view finance-page-view" id="customerPaymentsModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Payment History',
            'description' => 'Real-time record of all monetary receipts, guest deposits, tour settlements, and payment method channels.',
            'badge'       => 'Payment Receipts',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Payment History', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="paymentsKpiGrid">
        <!-- Card 1: Total Money Received -->
        <div class="booking-kpi-card <?= empty($selectedMethod) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalCollected) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totRec) ?> recorded transactions</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: UPI Collections -->
        <div class="booking-kpi-card <?= $selectedMethod === 'upi' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=upi') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">UPI Collections</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($upiCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>GPay, PhonePe, Paytm QR</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Counter Cash Collections -->
        <div class="booking-kpi-card <?= $selectedMethod === 'cash' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=cash') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cash Collections</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($cashCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>Direct counter desk cash</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Bank & Card Collections -->
        <div class="booking-kpi-card <?= in_array($selectedMethod, ['bank_transfer', 'card']) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=bank_transfer') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Bank &amp; Card Transfer</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= format_inr($bankCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>NEFT, RTGS, IMPS &amp; POS</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar, Search, Drawer & Method Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="<?= e(__('tooltip.back_to_finance', 'Back to Finance')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Customer Receipts</h2>
            </div>
            <a href="<?= url('/office/finance/pending-payments') ?>" class="mv-add-btn" style="background: rgba(22, 68, 59, 0.08); color: var(--brand-primary);">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Pending</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/payments') ?>" class="mv-search-form" id="mvPaymentsSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_customer_utr_invoice', 'Search customer, UTR, invoice #...')) ?>" value="<?= e($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= $makeFilterUrl(['search' => '']) ?>" class="mv-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle <?= (!empty($selectedMethod) || !empty($dateFrom) || !empty($dateTo)) ? 'active' : '' ?>" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="<?= e(__('common.filter', 'Filter options')) ?>">
                    <i class="fa-solid fa-sliders"></i>
                </button>
                <?php if (!empty($selectedMethod)): ?><input type="hidden" name="method" value="<?= e($selectedMethod) ?>"><?php endif; ?>
                <?php if (!empty($dateFrom)): ?><input type="hidden" name="date_from" value="<?= e($dateFrom) ?>"><?php endif; ?>
                <?php if (!empty($dateTo)): ?><input type="hidden" name="date_to" value="<?= e($dateTo) ?>"><?php endif; ?>
                <?php if ($perPage != 15): ?><input type="hidden" name="per_page" value="<?= (int)$perPage ?>"><?php endif; ?>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/payments') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Method:</label>
                            <select name="method" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <option value="" <?= empty($selectedMethod) ? 'selected' : '' ?>>All Methods</option>
                                <option value="cash" <?= $selectedMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="upi" <?= $selectedMethod === 'upi' ? 'selected' : '' ?>>UPI (GPay / PhonePe / Paytm)</option>
                                <option value="bank_transfer" <?= $selectedMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer (NEFT/IMPS)</option>
                                <option value="card" <?= $selectedMethod === 'card' ? 'selected' : '' ?>>Debit / Credit Card</option>
                                <option value="cheque" <?= $selectedMethod === 'cheque' ? 'selected' : '' ?>>Bank Cheque</option>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">From:</label>
                                <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="font-size: 0.82rem; height: 38px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">To:</label>
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
                        <a href="<?= url('/office/finance/payments') ?>" class="btn btn-outline btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm"><?= e(__('btn.filter_apply', 'Apply Filters')) ?></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Method Scroll Pills -->
        <div class="mv-status-scroll">
            <a href="<?= $makeFilterUrl(['method' => '']) ?>" class="mv-pill <?= empty($selectedMethod) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)$totRec ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['method' => 'upi']) ?>" class="mv-pill <?= $selectedMethod === 'upi' ? 'active' : '' ?>">
                <span>UPI</span>
            </a>
            <a href="<?= $makeFilterUrl(['method' => 'cash']) ?>" class="mv-pill <?= $selectedMethod === 'cash' ? 'active' : '' ?>">
                <span>Cash</span>
            </a>
            <a href="<?= $makeFilterUrl(['method' => 'bank_transfer']) ?>" class="mv-pill <?= $selectedMethod === 'bank_transfer' ? 'active' : '' ?>">
                <span>Bank</span>
            </a>
        </div>
    </div>

    <!-- 4. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/payments') ?>" class="filter-bar-compact" id="paymentsFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_by_customer_booking_ref', 'Search by customer, booking ref, invoice #, transaction UTR...')) ?>" value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/payments') ?>" class="fbc-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="method" id="methodInput">
            <option value="" <?= empty($selectedMethod) ? 'selected' : '' ?>>All Payment Methods</option>
            <option value="cash" <?= $selectedMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="upi" <?= $selectedMethod === 'upi' ? 'selected' : '' ?>>UPI (GPay / PhonePe / Paytm)</option>
            <option value="bank_transfer" <?= $selectedMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer (NEFT/IMPS)</option>
            <option value="card" <?= $selectedMethod === 'card' ? 'selected' : '' ?>>Debit / Credit Card</option>
            <option value="cheque" <?= $selectedMethod === 'cheque' ? 'selected' : '' ?>>Bank Cheque</option>
        </select>

        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="<?= e(__('tooltip.from_date', 'From Date')) ?>">
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="<?= e(__('tooltip.to_date', 'To Date')) ?>">

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <a href="<?= url('/office/finance/payments') ?>" class="fbc-btn" title="<?= e(__('common.reset_filters', 'Reset Filters')) ?>"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Payment Receipts Modern Table & Mobile Cards -->
    <section class="recent-bookings-section" id="paymentsTableSection">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-receipt" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Customer Payment Records
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th class="table-col-index text-center col-priority-1">#</th>
                        <th class="table-col-schedule col-priority-1"><?= e(__('table.payment_date', 'Payment Date')) ?></th>
                        <th class="table-col-name col-priority-1"><?= e(__('table.customer_name', 'Customer Name')) ?></th>
                        <th class="table-col-code col-priority-2" data-column-priority="secondary"><?= e(__('table.booking_ref', 'Booking Ref')) ?></th>
                        <th class="table-col-code col-priority-3 col-optional" data-column-priority="optional">Invoice #</th>
                        <th class="table-col-category col-priority-2" data-column-priority="secondary"><?= e(__('table.payment_method', 'Payment Method')) ?></th>
                        <th class="table-col-name col-priority-3 col-optional" data-column-priority="optional"><?= e(__('table.deposit_account', 'Deposit Account')) ?></th>
                        <th class="table-col-ref col-priority-3 col-optional" data-column-priority="optional"><?= e(__('table.transaction_ref', 'Transaction Ref')) ?></th>
                        <th class="table-col-amount col-priority-1 text-end"><?= e(__('table.amount_received', 'Amount Received')) ?></th>
                        <th class="table-col-actions text-end col-priority-1"><?= e(__('common.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="10">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payment receipts found</h4>
                                <p class="text-muted" style="margin:0;">No payments match your active search or date filter.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $i => $p):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $m = strtolower($p['payment_method'] ?? 'cash');
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="table-col-index text-center col-priority-1">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Payment Date -->
                            <td class="table-col-schedule col-priority-1">
                                <?php $payDate = $p['payment_date'] ?? $p['paid_at'] ?? $p['created_at'] ?? null; ?>
                                <div style="font-weight: 600; color: var(--vg-navy-900); font-size: 0.86rem;"><?= format_date($payDate) ?></div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= !empty($p['created_at']) ? date('h:i A', strtotime($p['created_at'])) : '' ?></div>
                            </td>

                            <!-- 3. Customer Name -->
                            <td class="table-col-name col-priority-1">
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($p['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($p['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?= e($p['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Booking Code -->
                            <td class="table-col-code col-priority-2" data-column-priority="secondary">
                                <?php if (!empty($p['booking_code'])): ?>
                                    <a href="<?= url('/office/bookings/' . $p['booking_id']) ?>" style="font-weight: 700; color: var(--brand-primary); text-decoration: none; font-size: 0.84rem;">
                                        <?= e($p['booking_code']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Invoice # -->
                            <td class="table-col-code col-priority-3 col-optional" data-column-priority="optional">
                                <?php if (!empty($p['invoice_number'])): ?>
                                    <a href="<?= url('/office/invoices/' . $p['invoice_id']) ?>" style="font-weight: 600; color: #4F46E5; text-decoration: none; font-size: 0.84rem;">
                                        <?= e($p['invoice_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Payment Method -->
                            <td class="table-col-category col-priority-2" data-column-priority="secondary">
                                <span class="badge" style="font-size: 0.74rem; text-transform: uppercase; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: rgba(33, 135, 101, 0.12); color: #218765;">
                                    <?= e($p['payment_method']) ?>
                                </span>
                            </td>

                            <!-- 7. Deposit Account -->
                            <td class="table-col-name col-priority-3 col-optional" data-column-priority="optional">
                                <div style="font-size: 0.84rem; color: var(--vg-navy-900); font-weight: 600;">
                                    <?= e($p['account_name'] ?? 'General Treasury') ?>
                                </div>
                            </td>

                            <!-- 8. Transaction Reference -->
                            <td class="table-col-ref col-priority-3 col-optional" data-column-priority="optional">
                                <?php if (!empty($p['payment_reference'])): ?>
                                    <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 6px; border-radius: 4px;"><?= e($p['payment_reference']) ?></code>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">PAY-<?= str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Amount -->
                            <td class="table-col-amount col-priority-1 text-end">
                                <strong style="font-size: 0.95rem; color: #218765;">
                                    + <?= format_inr($p['amount']) ?>
                                </strong>
                            </td>

                            <!-- 10. Actions -->
                            <td class="table-col-actions text-end col-priority-1" style="white-space: nowrap;">
                                <?php if (!empty($p['invoice_id'])): ?>
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/invoices/' . $p['invoice_id']) ?>" class="action-dropdown-item view">
                                            <i class="fa-solid fa-file-invoice"></i> <span>View Invoice</span>
                                        </a>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size: 0.78rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens (Canonical Invoices Design) -->
        <div class="cards-grid">
            <?php foreach ($payments as $p): ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card">
                    <!-- Left: Monogram Icon -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar" style="background: rgba(33, 135, 101, 0.12); color: #218765;">
                            <i class="fa-solid fa-receipt" style="font-size:20px;"></i>
                        </div>
                    </div>

                    <!-- Middle: Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= !empty($p['payment_reference']) ? e($p['payment_reference']) : ('PAY-' . str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT)) ?></div>
                        <div class="mv-tour-title" title="<?= e($p['customer_name'] ?? 'Direct Customer') ?>">
                            <?= e($p['customer_name'] ?? 'Direct Customer') ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span><?= format_date($p['payment_date'] ?? $p['paid_at'] ?? $p['created_at'] ?? null) ?> &bull; <?= strtoupper($p['payment_method']) ?></span>
                        </div>
                        <?php if (!empty($p['booking_code'])): ?>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-ticket"></i>
                            <span>Booking: #<?= e($p['booking_code']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($p['account_name'])): ?>
                        <div class="mv-vehicle" style="color: var(--text-muted);">
                            <i class="fa-solid fa-building-columns"></i>
                            <span><?= e($p['account_name']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Amount -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-paid" style="background: rgba(33, 135, 101, 0.12); color: #218765;">Received</span>
                        <div class="mv-price" style="color: #218765;">+ <?= format_inr((float)$p['amount']) ?></div>
                    </div>
                </div>

                <?php if (!empty($p['invoice_id'])): ?>
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                        </button>
                        <div class="action-dropdown-menu">
                            <a href="<?= url('/office/invoices/' . $p['invoice_id']) ?>" class="action-dropdown-item view">
                                <i class="fa-solid fa-file-invoice"></i> <span>View Invoice</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Customers & Bookings Pattern) -->
    <?php
        $totalPages = $totalPages ?? 1;
        $from = $totRec > 0 ? (($currP - 1) * $perPage) + 1 : 0;
        $to   = min($currP * $perPage, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedMethod, $dateFrom, $dateTo, $perPage) {
            $q = [
                'search'    => $search,
                'method'    => $selectedMethod,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'per_page'  => $perPage,
                'page'      => $p,
            ];
            return url('/office/finance/payments?' . http_build_query(array_filter($q)));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> payment receipts</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="<?= e(__('tooltip.previous', 'Previous')) ?>">&#10094;</a>
            <?php
                $start = max(1, $currP - 2);
                $end = min($totalPages, $currP + 2);
                for ($p = $start; $p <= $end; $p++):
            ?>
                <a href="<?= $buildPageUrl($p) ?>" class="<?= $p === $currP ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="<?= e(__('tooltip.next', 'Next')) ?>">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Record Customer Payment                                            -->
<!-- ========================================================================= -->
<div id="paymentModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'paymentModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-money-bill-wave" style="color: #218765; margin-right: 0.45rem;"></i>
                Record Customer Payment
            </h3>
            <button type="button" class="modal-close-custom" onclick="closePaymentModal()">&times;</button>
        </div>
        <form action="<?= url('/office/invoices/record-payment') ?>" method="POST" id="recordPaymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/payments') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="pay_invoice_id">Select Customer Invoice <span class="req">*</span></label>
                    <select name="invoice_id" id="pay_invoice_id" class="form-select" onchange="onInvoiceSelected(this)" required>
                        <option value="">— Select Pending Invoice —</option>
                        <?php foreach ($pendingInvoices as $inv): 
                            $bal = (float)$inv['total_amount'] - (float)$inv['paid_amount'];
                        ?>
                            <option value="<?= $inv['id'] ?>" data-balance="<?= $bal ?>" data-booking="<?= e($inv['booking_code'] ?? '') ?>">
                                <?= e($inv['invoice_number']) ?> &bull; <?= e($inv['customer_name'] ?? 'Direct') ?> &bull; Pending: <?= format_inr($bal) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="pay_amount">Amount Received (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pay_date">Payment Date <span class="req">*</span></label>
                        <input type="date" name="payment_date" id="pay_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="pay_method">Payment Mode <span class="req">*</span></label>
                        <select name="payment_method" id="pay_method" class="form-select" required>
                            <option value="cash">Counter Cash</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pay_account_id">Deposit Into Account <span class="req">*</span></label>
                        <select name="account_id" id="pay_account_id" class="form-select" required>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="pay_reference">Transaction / UTR Reference</label>
                    <input type="text" name="payment_reference" id="pay_reference" class="form-control" placeholder="<?= e(__('placeholder.utr_number_upi_ref_or_cheque', 'UTR number, UPI ref, or cheque #')) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="pay_notes">Notes / Receipt Remarks</label>
                    <input type="text" name="notes" id="pay_notes" class="form-control" placeholder="<?= e(__('placeholder.payment_received_confirmation', 'Payment received confirmation...')) ?>">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closePaymentModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Payment</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        if (!p) return;
        p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    }
    function openPaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'flex';
    }
    function closePaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'none';
    }
    function onInvoiceSelected(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (opt && opt.dataset.balance) {
            document.getElementById('pay_amount').value = parseFloat(opt.dataset.balance).toFixed(2);
        }
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>


