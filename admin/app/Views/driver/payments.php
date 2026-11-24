<?php
/**
 * Driver Payment History View — Dedicated Driver Portal
 * Professional Financial Vouchers, Disbursement Tracking & Ledger
 */

$currP      = $page ?? 1;
$totRec     = $totalRecords ?? 0;
$pSize      = $perPage ?? 10;
$totalPages = $totalPages ?? 1;
$search     = $search ?? '';
$fromDate   = $fromDate ?? '';
$toDate     = $toDate ?? '';

$totalPaid     = (float)($financialSummary['total_paid'] ?? 0);
$totalPending  = (float)($financialSummary['total_pending'] ?? 0);
$totalEarned   = (float)($financialSummary['total_earned'] ?? 0);
$latestAmt     = !empty($latestPayment['amount']) ? (float)$latestPayment['amount'] : 0.0;
$latestDate    = !empty($latestPayment['payment_date']) ? format_date($latestPayment['payment_date']) : null;

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/driver/earnings') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-list-check" style="margin-right:6px;"></i><span>Trip Dues</span></a>'
    . '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-route" style="margin-right:6px;"></i><span>Assigned Trips</span></a>'
    . '</div>';
?>

<div class="bookings-page-view driver-portal-view driver-portal-container">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'My Payment History',
            'description' => 'Review official payment vouchers, transaction references, and settlement records disbursed by the office.',
            'badge'       => 'Financial Vouchers',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Payment History', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Payment KPI Summary Cards -->
    <div class="booking-kpi-grid" id="paymentsKpiGrid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1.5rem;">
        <!-- Card 1: Total Paid -->
        <div class="booking-kpi-card" title="<?= e(__('tooltip.total_compensation_successfull', 'Total compensation successfully paid')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Paid Amount</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #10B981;">
                <?= format_currency($totalPaid) ?>
            </div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background:#10B981;"></span>
                <span>Received via bank &amp; cash</span>
            </div>
        </div>

        <!-- Card 2: Pending Balance Due -->
        <div class="booking-kpi-card <?= $totalPending > 0 ? 'is-selected' : '' ?>" title="<?= e(__('tooltip.outstanding_compensation_await', 'Outstanding compensation awaiting office settlement')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Balance</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $totalPending > 0 ? '#D97706' : '#10B981' ?>;">
                <?= format_currency($totalPending) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $totalPending > 0 ? 'Awaiting settlement from office' : 'All trip dues settled' ?></span>
            </div>
        </div>

        <!-- Card 3: Partially Paid Trips -->
        <div class="booking-kpi-card" title="<?= e(__('tooltip.completed_trips_with_partial_p', 'Completed trips with partial payments received')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Partially Paid</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary, #176B5B);">
                <?= number_format($partiallyPaidCount) ?> <span style="font-size:0.85rem; font-weight:600; color:#64748B;">trip(s)</span>
            </div>
            <div class="booking-kpi-sub">
                <span>Trips with partial disbursements</span>
            </div>
        </div>

        <!-- Card 4: Latest Payment -->
        <div class="booking-kpi-card" title="<?= e(__('tooltip.most_recent_payment_received', 'Most recent payment received')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Latest Payment</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #0F172A;">
                <?= $latestAmt > 0 ? format_currency($latestAmt) : '—' ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $latestDate ? 'Disbursed on ' . $latestDate : 'No payments yet' ?></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/driver/payments') ?>" class="filter-bar-compact" id="paymentsFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_by_voucher_ref_utr_trip', 'Search by voucher ref, UTR, trip code, booking...')) ?>" value="<?= e($search) ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="<?= e(__('common.clear', 'Clear search')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="fbc-date-wrap">
            <input type="date" name="from_date" id="fromDateInput" class="fbc-date-input" value="<?= e($fromDate) ?>" title="<?= e(__('tooltip.start_payment_date', 'Start payment date')) ?>">
        </div>

        <div class="fbc-date-wrap">
            <input type="date" name="to_date" id="toDateInput" class="fbc-date-input" value="<?= e($toDate) ?>" title="<?= e(__('tooltip.end_payment_date', 'End payment date')) ?>">
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
            <?php if (!empty($search) || !empty($fromDate) || !empty($toDate)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="<?= e(__('common.reset_filters', 'Clear Filters')) ?>" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Quick Status Pills (Exact match to /driver/earnings) -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="<?= e(__('common.back_to_dashboard', 'Back to Dashboard')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Payment History</h2>
            </div>
            <a href="<?= url('/driver/earnings') ?>" class="mv-add-btn">
                <i class="fa-solid fa-wallet"></i>
                <span>Earnings</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/driver/payments') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_by_voucher_ref_utr_trip', 'Search by voucher ref, UTR, trip...')) ?>" value="<?= e($search) ?>" autocomplete="off">
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
        <div class="mv-status-scroll" id="paymentsMvStatusScroll">
            <div class="mv-pill active">
                <i class="fa-solid fa-receipt"></i>
                <span>Vouchers</span>
                <span class="mv-pill-cnt"><?= number_format($totRec) ?></span>
            </div>
            <div class="mv-pill">
                <i class="fa-solid fa-circle-check" style="color: #10B981;"></i>
                <span>Paid: <?= format_currency($totalPaid) ?></span>
            </div>
            <?php if ($totalPending > 0): ?>
                <div class="mv-pill">
                    <i class="fa-solid fa-hourglass-half" style="color: #D97706;"></i>
                    <span>Due: <?= format_currency($totalPending) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Payment Vouchers Table (Desktop) & Cards (Mobile) -->
    <section class="recent-bookings-section" id="paymentsSection">
        <div class="section-header">
            <h3 class="section-title">Official Payment Vouchers</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Vouchers:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th class="text-center col-priority-1" style="width: 50px;">#</th>
                        <th class="col-priority-1" style="width: 140px;"><?= e(__('table.payment_date', 'Payment Date')) ?></th>
                        <th class="col-priority-2" data-column-priority="secondary">Receipt # / Voucher</th>
                        <th class="col-priority-1"><?= e(__('table.trip_assignment', 'Trip Assignment')) ?></th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional" style="width: 140px;"><?= e(__('table.payment_method', 'Payment Method')) ?></th>
                        <th class="col-priority-1 text-end" style="width: 140px; text-align: right;"><?= e(__('table.paid_amount', 'Paid Amount')) ?></th>
                        <th class="text-center col-priority-1" style="width: 110px;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center col-priority-1" style="width: 100px;"><?= e(__('common.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="padding: 3rem 1.5rem; text-align:center;">
                                <div class="no-data-icon" style="font-size:2.5rem; color:#94A3B8; margin-bottom:0.75rem;"><i class="fa-solid fa-receipt"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payment vouchers recorded</h4>
                                <p class="text-muted" style="margin:0;">Official payment receipts will appear here once finance settles trip dues.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $i => $p):
                            $serial  = (($currP - 1) * $pSize) + $i + 1;
                            $amt     = (float)($p['amount'] ?? 0);
                            $pStatus = strtolower($p['status'] ?? 'paid');
                        ?>
                        <tr>
                            <!-- 1. Serial Number -->
                            <td class="text-center col-priority-1">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Payment Date -->
                            <td class="col-priority-1">
                                <div style="font-weight: 700; color: #1E293B; font-size: 0.86rem;">
                                    <?= format_date($p['payment_date']) ?>
                                </div>
                                <div style="font-size: 0.72rem; color: #64748B;">
                                    <?= !empty($p['created_at']) ? date('h:i A', strtotime($p['created_at'])) : 'Paid' ?>
                                </div>
                            </td>

                            <!-- 3. Receipt # / Voucher Ref -->
                            <td class="col-priority-2" data-column-priority="secondary">
                                <a href="<?= url('/driver/payments/receipt/' . (int)$p['id']) ?>" style="font-family: monospace; font-weight: 700; color: var(--brand-primary); text-decoration: none;">
                                    <?= e($p['receipt_number'] ?: $p['payment_reference']) ?>
                                </a>
                            </td>

                            <!-- 4. Trip Assignment -->
                            <td class="col-priority-1">
                                <?php if (!empty($p['pickup_address']) && !empty($p['drop_address'])): ?>
                                    <div style="font-weight: 600; font-size: 0.84rem; color: #1E293B;" title="<?= e($p['pickup_address']) ?> &rarr; <?= e($p['drop_address']) ?>">
                                        <?= e($p['pickup_address']) ?> &rarr; <?= e($p['drop_address']) ?>
                                    </div>
                                <?php elseif (!empty($p['pickup_address'])): ?>
                                    <div style="font-weight: 600; font-size: 0.84rem; color: #1E293B;">
                                        <?= e($p['pickup_address']) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #94A3B8; font-size: 0.8rem;">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Payment Method -->
                            <td class="col-priority-3 col-optional" data-column-priority="optional">
                                <div style="font-weight: 600; font-size: 0.84rem; color: #334155; text-transform: capitalize; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-credit-card" style="color: var(--brand-primary, #176B5B); font-size: 0.76rem;"></i>
                                    <span><?= e(str_replace('_', ' ', $p['payment_method'])) ?></span>
                                </div>
                                <?php if (!empty($p['account_name'])): ?>
                                    <span style="font-size: 0.72rem; color: #64748B;"><?= e($p['account_name']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Paid Amount -->
                            <td class="col-priority-1 text-end" style="text-align: right; font-weight: 800; font-size: 0.92rem; color: #10B981;">
                                <?= format_currency($amt) ?>
                            </td>

                            <!-- 7. Status Badge -->
                            <td class="text-center col-priority-1">
                                <?= render_status_badge($pStatus, 'payment') ?>
                            </td>

                            <!-- 8. Actions (One Single Professional Actions Control) -->
                            <td class="text-center col-priority-1" style="white-space: nowrap;">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/driver/payments/receipt/' . (int)$p['id']) ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-file-invoice text-info"></i> <span>View Receipt</span>
                                        </a>
                                        <a href="<?= url('/driver/payments/receipt/' . (int)$p['id'] . '/print') ?>" target="_blank" class="action-dropdown-item">
                                            <i class="fa-solid fa-print"></i> <span>Print Receipt</span>
                                        </a>
                                        <?php if (!empty($p['dispatch_id'])): ?>
                                            <div class="action-dropdown-divider"></div>
                                            <a href="<?= url('/driver/trips/' . $p['dispatch_id']) ?>" class="action-dropdown-item view">
                                                <i class="fa-solid fa-eye text-primary"></i> <span>View Trip Details</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Payment Vouchers Cards List (Exact UI Match to /driver/earnings) -->
        <div class="cards-grid" id="paymentsCards">
            <?php if (empty($payments)): ?>
                <div class="no-data-message" style="background:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0; padding:2rem 1rem; text-align:center;">
                    <div class="no-data-icon" style="font-size:2rem; color:#94A3B8; margin-bottom:0.5rem;"><i class="fa-solid fa-receipt"></i></div>
                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:4px;">No vouchers recorded</h4>
                    <p class="text-muted" style="margin:0; font-size:0.85rem;">No payment vouchers match your filter parameters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($payments as $p):
                    $amt       = (float)($p['amount'] ?? 0);
                    $pStatus   = strtolower($p['status'] ?? 'paid');
                    $refCode   = !empty($p['receipt_number']) ? $p['receipt_number'] : ($p['payment_reference'] ?? ('VCH-' . $p['id']));
                    $dspCode   = $p['dispatch_code'] ?: (!empty($p['dispatch_id']) ? ('#DSP-' . $p['dispatch_id']) : null);
                    $payMethod = str_replace('_', ' ', $p['payment_method'] ?? 'bank_transfer');
                ?>
                <div class="mv-booking-card-wrapper">
                    <a href="<?= url('/driver/payments/receipt/' . $p['id']) ?>" class="mv-booking-card" style="text-decoration:none;">
                        <!-- Left: Avatar / Monogram Box -->
                        <div class="mv-thumb-wrap is-avatar">
                            <div class="mv-thumb-avatar" style="background: #DEF7EC; color: #03543F;">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>

                        <!-- Middle: Details -->
                        <div class="mv-details">
                            <div class="mv-tour-title" title="<?= e($refCode) ?>">
                                <span style="font-family: monospace; font-weight: 800; color: #0f172a;"><?= e($refCode) ?></span>
                                <?php if (!empty($dspCode)): ?>
                                    <span class="cust-code-pill" style="margin-left: 4px;"><?= e($dspCode) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mv-date-duration">
                                <i class="fa-solid fa-calendar-day"></i>
                                <span><?= format_date($p['payment_date']) ?> &bull; <?= e(ucwords($payMethod)) ?></span>
                            </div>
                            <?php if (!empty($p['account_name']) || !empty($p['transaction_ref'])): ?>
                                <div class="mv-date-duration" style="color: #64748B;">
                                    <i class="fa-solid fa-building-columns"></i>
                                    <span><?= !empty($p['account_name']) ? e($p['account_name']) : 'Account' ?><?= !empty($p['transaction_ref']) ? ' &bull; UTR: ' . e($p['transaction_ref']) : '' ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['pickup_address'])): ?>
                                <div class="mv-passenger" style="color:#059669;">
                                    <i class="fa-solid fa-location-dot" style="color:#10B981;"></i>
                                    <span>From: <?= e($p['pickup_address']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['drop_address'])): ?>
                                <div class="mv-passenger" style="color:#DC2626;">
                                    <i class="fa-solid fa-location-dot" style="color:#EF4444;"></i>
                                    <span>To: <?= e($p['drop_address']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Right: Status Badge & View Button -->
                        <div class="mv-right-col">
                            <?= render_status_badge($pStatus, 'payment') ?>
                            <span class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.72rem; border-radius: 6px; margin-top: auto; color:#176B5B; border-color:#176B5B;">Receipt &rarr;</span>
                        </div>
                    </a>

                    <!-- Dedicated Full-Width Financial Strip (Exact /driver/earnings design) -->
                    <div class="mv-earnings-strip">
                        <div class="mv-earn-stats-group">
                            <div class="mv-earn-stat">
                                <span class="mv-earn-label">Date</span>
                                <span class="mv-earn-val"><?= date('d M Y', strtotime($p['payment_date'])) ?></span>
                            </div>
                            <div class="mv-earn-stat">
                                <span class="mv-earn-label">Method</span>
                                <span class="mv-earn-val" style="font-size: 0.82rem; text-transform: capitalize; color: #475569;"><?= e($payMethod) ?></span>
                            </div>
                            <div class="mv-earn-stat is-due-box is-settled">
                                <span class="mv-earn-label" style="color: #03543F;">Paid Amount</span>
                                <span class="mv-earn-val is-due-amount" style="color: #046C4E;">
                                    <?= format_currency($amt) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mv-card-actions">
                            <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="card-btns-popup">
                                <a href="<?= url('/driver/payments/receipt/' . $p['id']) ?>" class="action-btn-table view" title="<?= e(__('tooltip.view_receipt', 'View Receipt')) ?>">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </a>
                                <a href="<?= url('/driver/payments/receipt/' . $p['id'] . '/print') ?>" target="_blank" class="action-btn-table edit" style="color:#0284c7;" title="<?= e(__('tooltip.print_receipt', 'Print Receipt')) ?>">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <?php if (!empty($p['dispatch_id'])): ?>
                                    <a href="<?= url('/driver/trips/' . $p['dispatch_id']) ?>" class="action-btn-table view" style="color:#10b981;" title="<?= e(__('tooltip.view_trip_details', 'View Trip Details')) ?>">
                                        <i class="fa-solid fa-route"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= url('/driver/earnings') ?>" class="action-btn-table edit" style="color:#176B5B;" title="<?= e(__('tooltip.trip_earnings', 'Trip Earnings')) ?>">
                                    <i class="fa-solid fa-wallet"></i>
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
        $buildPageUrl = function(int $p) use ($search, $fromDate, $toDate, $pSize) {
            $q = [];
            if (!empty($search))   $q['search']    = $search;
            if (!empty($fromDate)) $q['from_date'] = $fromDate;
            if (!empty($toDate))   $q['to_date']   = $toDate;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/driver/payments?' . http_build_query($q));
        };
    ?>
    <div class="pagination-wrapper" id="paymentsPaginationWrapper">
        <div class="pagination-info" style="font-size: 0.85rem; color: #64748B;">
            Showing <strong><?= min($totRec, (($currP - 1) * $pSize) + 1) ?></strong>
            to <strong><?= min($totRec, $currP * $pSize) ?></strong>
            of <strong><?= number_format($totRec) ?></strong> payment vouchers
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currP > 1): ?>
                    <a href="<?= $buildPageUrl($currP - 1) ?>" data-page="<?= $currP - 1 ?>" title="<?= e(__('tooltip.previous_page', 'Previous page')) ?>">&larr;</a>
                <?php else: ?>
                    <a class="disabled" title="<?= e(__('tooltip.previous_page', 'Previous page')) ?>">&larr;</a>
                <?php endif; ?>

                <?php
                    $startP = max(1, $currP - 2);
                    $endP   = min($totalPages, $currP + 2);
                    if ($startP > 1) {
                        echo '<a href="' . $buildPageUrl(1) . '" data-page="1">1</a>';
                        if ($startP > 2) echo '<span class="dots">&hellip;</span>';
                    }
                    for ($p = $startP; $p <= $endP; $p++) {
                        $activeClass = ($p == $currP) ? 'active' : '';
                        echo '<a href="' . $buildPageUrl($p) . '" class="' . $activeClass . '" data-page="' . $p . '">' . $p . '</a>';
                    }
                    if ($endP < $totalPages) {
                        if ($endP < $totalPages - 1) echo '<span class="dots">&hellip;</span>';
                        echo '<a href="' . $buildPageUrl($totalPages) . '" data-page="' . $totalPages . '">' . $totalPages . '</a>';
                    }
                ?>

                <?php if ($currP < $totalPages): ?>
                    <a href="<?= $buildPageUrl($currP + 1) ?>" data-page="<?= $currP + 1 ?>" title="<?= e(__('tooltip.next_page', 'Next page')) ?>">&rarr;</a>
                <?php else: ?>
                    <a class="disabled" title="<?= e(__('tooltip.next_page', 'Next page')) ?>">&rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Driver Payment History Live AJAX Search & Filter Engine                   */
    /* ========================================================================= */
    (function initDriverPaymentsAjaxFilter() {
        let searchTimer = null;
        let activeController = null;

        function filterPaymentsAjax(page = 1) {
            const searchVal   = (document.getElementById('searchInput')?.value || document.getElementById('mvSearchInput')?.value || '').trim();
            const fromDateVal = (document.getElementById('fromDateInput')?.value || document.getElementById('mvFromDateInput')?.value || '').trim();
            const toDateVal   = (document.getElementById('toDateInput')?.value || document.getElementById('mvToDateInput')?.value || '').trim();
            const perPageVal  = document.getElementById('perPageInput')?.value || document.getElementById('mvPerPageSelect')?.value || '10';

            const params = new URLSearchParams();
            if (searchVal)   params.set('search', searchVal);
            if (fromDateVal) params.set('from_date', fromDateVal);
            if (toDateVal)   params.set('to_date', toDateVal);
            if (perPageVal && perPageVal !== '10') params.set('per_page', perPageVal);
            if (page > 1)    params.set('page', page);

            const queryString = params.toString();
            const targetUrl = '<?= url('/driver/payments') ?>' + (queryString ? '?' + queryString : '');

            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            const section = document.getElementById('paymentsSection');
            if (section) section.classList.add('is-filtering');

            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            fetch(targetUrl, {
                signal: activeController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update Payments Section (Table, mobile cards, empty state)
                const newSection = doc.getElementById('paymentsSection');
                const currSection = document.getElementById('paymentsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                // 2. Update Pagination Wrapper
                const newPagination = doc.getElementById('paymentsPaginationWrapper');
                const currPagination = document.getElementById('paymentsPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                // 3. Update Top KPI Summary Grid
                const newKpi = doc.getElementById('paymentsKpiGrid');
                const currKpi = document.getElementById('paymentsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                }

                // 4. Update Mobile Status Scroll Pills
                const newMvPills = doc.getElementById('paymentsMvStatusScroll');
                const currMvPills = document.getElementById('paymentsMvStatusScroll');
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
                    console.error('Payments AJAX filter failed:', err);
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
                filterPaymentsAjax(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }
        if (mvSearchInput) {
            mvSearchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }

        // Form Submit listeners
        const filterForm = document.getElementById('paymentsFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterPaymentsAjax(1);
            });
        }
        const mvForm = document.getElementById('mvSearchForm');
        if (mvForm) {
            mvForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterPaymentsAjax(1);
            });
        }

        // Date & Per-page changes sync
        const fromDateInput = document.getElementById('fromDateInput');
        const mvFromDateInput = document.getElementById('mvFromDateInput');
        const toDateInput = document.getElementById('toDateInput');
        const mvToDateInput = document.getElementById('mvToDateInput');
        const perPageInput = document.getElementById('perPageInput');
        const mvPerPageSelect = document.getElementById('mvPerPageSelect');

        if (fromDateInput) {
            fromDateInput.addEventListener('change', () => {
                if (mvFromDateInput) mvFromDateInput.value = fromDateInput.value;
                filterPaymentsAjax(1);
            });
        }
        if (mvFromDateInput) {
            mvFromDateInput.addEventListener('change', () => {
                if (fromDateInput) fromDateInput.value = mvFromDateInput.value;
                filterPaymentsAjax(1);
            });
        }
        if (toDateInput) {
            toDateInput.addEventListener('change', () => {
                if (mvToDateInput) mvToDateInput.value = toDateInput.value;
                filterPaymentsAjax(1);
            });
        }
        if (mvToDateInput) {
            mvToDateInput.addEventListener('change', () => {
                if (toDateInput) toDateInput.value = mvToDateInput.value;
                filterPaymentsAjax(1);
            });
        }
        if (perPageInput) {
            perPageInput.addEventListener('change', () => {
                if (mvPerPageSelect) mvPerPageSelect.value = perPageInput.value;
                filterPaymentsAjax(1);
            });
        }
        if (mvPerPageSelect) {
            mvPerPageSelect.addEventListener('change', () => {
                if (perPageInput) perPageInput.value = mvPerPageSelect.value;
                filterPaymentsAjax(1);
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
                filterPaymentsAjax(1);
            });
        }
        if (mvFilterResetBtn) {
            mvFilterResetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (fromDateInput) fromDateInput.value = '';
                if (mvFromDateInput) mvFromDateInput.value = '';
                if (toDateInput) toDateInput.value = '';
                if (mvToDateInput) mvToDateInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                toggleMobileFilter();
                filterPaymentsAjax(1);
            });
        }
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (fromDateInput) fromDateInput.value = '';
                if (mvFromDateInput) mvFromDateInput.value = '';
                if (toDateInput) toDateInput.value = '';
                if (mvToDateInput) mvToDateInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                filterPaymentsAjax(1);
            });
        }

        // Global Event Delegation for clicks
        document.addEventListener('click', function(e) {
            // 1. Clear Filters Button
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn');
            if (clearBtn) {
                e.preventDefault();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn') {
                    if (searchInput) searchInput.value = '';
                    if (mvSearchInput) mvSearchInput.value = '';
                } else {
                    if (searchInput) searchInput.value = '';
                    if (mvSearchInput) mvSearchInput.value = '';
                    if (fromDateInput) fromDateInput.value = '';
                    if (mvFromDateInput) mvFromDateInput.value = '';
                    if (toDateInput) toDateInput.value = '';
                    if (mvToDateInput) mvToDateInput.value = '';
                    if (perPageInput) perPageInput.value = '10';
                    if (mvPerPageSelect) mvPerPageSelect.value = '10';
                }
                filterPaymentsAjax(1);
                return;
            }

            // 2. Pagination Links
            const pageLink = e.target.closest('#paymentsPaginationWrapper .pagination a[data-page]');
            if (pageLink && !pageLink.classList.contains('disabled') && !pageLink.classList.contains('active')) {
                e.preventDefault();
                const p = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
                filterPaymentsAjax(p);
                const section = document.getElementById('paymentsSection');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
        });
    })();

    /* ========================================================================= */
    /* 3-Dots Action Popups Toggle & Mobile Filters                              */
    /* ========================================================================= */
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
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .action-btns-popup.show, .card-btns-popup.open, .card-btns-popup.show').forEach(p => {
                p.classList.remove('open', 'show');
            });
        }
    });
</script>

<style>
/* ========================================================================= */
/* Driver Payments Scoped Responsive Rules (Matching /driver/earnings)       */
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
    background: #176B5B;
    color: #fff;
    border-color: #176B5B;
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

.mv-thumb-wrap.is-avatar {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
}

.mv-thumb-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: 700;
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

.mv-date-duration, .mv-passenger {
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

.mv-card-actions {
    position: relative !important;
    bottom: auto !important;
    right: auto !important;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    margin-left: 6px;
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

/* Responsive display switches matching /driver/earnings */
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
