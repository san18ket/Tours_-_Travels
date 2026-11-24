<?php
/**
 * CUSTOMER BOOKING REFUNDS & SETTLEMENTS
 * Executive Finance Module with Strict Claim/Payment Separation & Concurrency Safety
 */

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/payments') . '" class="btn btn-outline"><i class="fa-solid fa-receipt"></i> <span>Payment History</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openIssueRefundModal()"><i class="fa-solid fa-plus"></i> <span>Issue Refund</span></button>'
    . '</div>';

$selectedStatus = $selectedStatus ?? '';
$search         = $search ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($refunds);
$currP          = $currentPage ?? 1;
?>

<div class="bookings-page-view finance-page-view" id="financeRefundsPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Refunds',
            'description' => 'Manage refund claims and customer repayments.',
            'badge'       => 'Refund Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance/dashboard')],
                ['label' => 'Refunds', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="refundsKpiGrid">
        <!-- Card 1: Total Refund Claims -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds') ?>'" title="<?= e(__('tooltip.click_to_view_all_refund_claim', 'Click to view all refund claims')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Refund Claims</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h6a3 3 0 003-3v-6a3 3 0 00-3-3h-1"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>All cancellation claims</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Pending Claims -->
        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=pending') ?>'" title="<?= e(__('tooltip.click_to_filter_pending_refund', 'Click to filter pending refund claims')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Claims</span>
                <span class="booking-kpi-icon icon-amber" style="background: rgba(199, 138, 53, 0.12); color: #C78A35;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= ($pendingCount ?? 0) > 0 ? '#C78A35' : 'var(--brand-primary)' ?>;">
                <?= number_format($pendingCount ?? 0) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= format_inr($totalPending ?? 0) ?> awaiting payment</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Processed Refunds -->
        <div class="booking-kpi-card <?= $selectedStatus === 'processed' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=processed') ?>'" title="<?= e(__('tooltip.click_to_filter_completed_proc', 'Click to filter completed processed refunds')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Processed Refunds</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= number_format($processedCount ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Settled customer repayments</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Total Refunded -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=processed') ?>'" title="<?= e(__('tooltip.total_real_treasury_funds_retu', 'Total real treasury funds returned')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Refunded</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($totalProcessed ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Real cash &amp; bank outflow</span>
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Compact, consistent with customers & finance) -->
    <form method="GET" action="<?= url('/office/finance/refunds') ?>" class="filter-bar-compact" id="refundFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_refund_booking_code_cus', 'Search refund #, booking code, customer, invoice...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/refunds' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="fbc-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput" onchange="this.form.submit()">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending (Claim only)</option>
            <option value="approved" <?= ($selectedStatus ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="processed" <?= ($selectedStatus ?? '') === 'processed' ? 'selected' : '' ?>>Processed (Paid)</option>
            <option value="rejected" <?= ($selectedStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <select name="per_page" id="perPageInput" title="<?= e(__('common.records_per_page', 'Records per page')) ?>" onchange="this.form.submit()">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <a href="<?= url('/office/finance/refunds') ?>" class="fbc-btn" title="<?= e(__('common.reset_filters', 'Reset Filters')) ?>"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Filter Drawer & Status Scroll (Exact Invoices / Customers Architecture) -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="<?= e(__('common.back_to_dashboard', 'Back to Dashboard')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Customer Refunds</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openIssueRefundModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Issue</span>
            </button>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/refunds') ?>" class="mv-search-form" id="mvRefundSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_refund_booking_customer', 'Search refund #, booking, customer...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/finance/refunds' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="<?= e(__('common.clear', 'Clear search')) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="<?= e(__('common.filter', 'Filter options')) ?>">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none; padding: 10px 12px; background: #fff; border: 1.5px solid #DCE6F2; border-radius: 12px; margin-top: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                <form method="GET" action="<?= url('/office/finance/refunds') ?>" style="display: flex; gap: 8px; flex-direction: column;">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #0C2B52;">Filter Refunds</span>
                        <a href="<?= url('/office/finance/refunds') ?>" style="font-size: 0.75rem; color: #D95757; font-weight: 600; text-decoration: none;">Reset All</a>
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Status:</label>
                        <select name="status" class="form-control" style="width: 100%; height: 38px; font-size: 0.85rem; border-radius: 8px;" onchange="this.form.submit()">
                            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
                            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending (Claim only)</option>
                            <option value="approved" <?= ($selectedStatus ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="processed" <?= ($selectedStatus ?? '') === 'processed' ? 'selected' : '' ?>>Processed (Paid)</option>
                            <option value="rejected" <?= ($selectedStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.74rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Per Page:</label>
                        <select name="per_page" class="form-control" style="width: 100%; height: 38px; font-size: 0.85rem; border-radius: 8px;" onchange="this.form.submit()">
                            <?php foreach ([10, 15, 25, 50] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 15) $p['per_page'] = $perPage;
                return url('/office/finance/refunds' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="refundsMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('pending') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'pending' ? 'active' : '' ?>">
                <span>Pending</span>
                <span class="mv-pill-cnt"><?= (int)($pendingCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('approved') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'approved' ? 'active' : '' ?>">
                <span>Approved</span>
                <span class="mv-pill-cnt"><?= (int)($approvedCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('processed') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'processed' ? 'active' : '' ?>">
                <span>Processed</span>
                <span class="mv-pill-cnt"><?= (int)($processedCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('rejected') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'rejected' ? 'active' : '' ?>">
                <span>Rejected</span>
                <span class="mv-pill-cnt"><?= (int)($rejectedCount ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. Refunds Modern Table & Mobile Card View -->
    <section class="recent-bookings-section" id="refundsMainSection">
        <div class="section-header">
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <h3 class="section-title">
                    <i class="fa-solid fa-rotate-left" style="color: var(--brand-primary); margin-right: 0.35rem;"></i>
                    Customer Refunds Ledger
                </h3>
                <span class="badge" style="background: rgba(22, 101, 52, 0.1); color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">
                    <i class="fa-solid fa-shield-halved"></i> Two-Step Claim &amp; Payment Separation
                </span>
            </div>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <!-- Modern Table for Desktop Screens (No horizontal scroll) -->
        <div class="modern-table-container">
            <table class="modern-table" id="refundsTable">
                <thead>
                    <tr>
                        <th class="table-col-code col-priority-1"><?= e(__('table.refund', 'Refund')) ?></th>
                        <th class="table-col-code col-priority-1"><?= e(__('table.booking', 'Booking')) ?></th>
                        <th class="table-col-name col-priority-1"><?= e(__('table.customer', 'Customer')) ?></th>
                        <th class="table-col-amount col-priority-1 text-end"><?= e(__('table.amount', 'Amount')) ?></th>
                        <th class="table-col-category col-priority-2" data-column-priority="secondary"><?= e(__('refund.refund_method', 'Refund Method')) ?></th>
                        <th class="table-col-status text-center col-priority-1"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="table-col-actions text-end col-priority-1"><?= e(__('common.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($refunds)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message" style="text-align: center; padding: 2.75rem 1rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700; color:#1e293b; margin-bottom:6px;">No refund claims found</h4>
                                <p class="text-muted" style="margin:0;">No customer refund requests match the selected search or status filters.</p>
                                <div style="margin-top: 1rem;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openIssueRefundModal()">
                                        <i class="fa-solid fa-plus"></i> Issue New Refund
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($refunds as $r):
                            $st = strtolower($r['status'] ?? 'pending');
                            $isProcessed = ($st === 'processed');
                            $isPending   = ($st === 'pending');
                            $isApproved  = ($st === 'approved');
                            $isRejected  = ($st === 'rejected');
                            $canEdit     = ($isPending || $isApproved);
                            $canProcess  = ($isPending || $isApproved);

                            // JSON data packet for modals
                            $jsonData = htmlspecialchars(json_encode([
                                'id'                    => (int)$r['id'],
                                'refund_number'         => $r['refund_number'],
                                'amount'                => (float)$r['amount'],
                                'amount_formatted'      => format_inr($r['amount']),
                                'refund_date'           => $r['refund_date'],
                                'refund_date_formatted' => format_date($r['refund_date']),
                                'refund_method'         => $r['refund_method'],
                                'reason'                => $r['reason'] ?? '',
                                'notes'                 => $r['notes'] ?? '',
                                'status'                => $st,
                                'status_label'          => ucfirst($st),
                                'created_at'            => $r['created_at'],
                                'created_at_formatted'  => format_date($r['created_at']),
                                'customer_name'         => $r['customer_name'] ?? 'Direct Client',
                                'customer_phone'        => $r['customer_phone'] ?? '',
                                'customer_email'        => $r['customer_email'] ?? '',
                                'booking_code'          => $r['booking_code'] ?: 'Unlinked',
                                'booking_id'            => $r['booking_db_id'] ?? null,
                                'invoice_id'            => $r['invoice_id'] ?? null,
                                'invoice_number'        => $r['invoice_number'] ?? '',
                                'invoice_total'         => (float)($r['invoice_total'] ?? 0),
                                'invoice_paid'          => (float)($r['invoice_paid'] ?? 0),
                                'processed_by_name'     => $r['processed_by_name'] ?? '',
                                'processed_at'          => $r['processed_at'] ?? '',
                                'processed_account_name'=> $r['processed_account_name'] ?? '',
                                'processed_tx_ref'      => $r['processed_tx_ref'] ?? $r['gateway_refund_id'] ?? '',
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="refund-row" id="refund-row-<?= (int)$r['id'] ?>">
                            <!-- 1. Refund column: Refund # and date underneath -->
                            <td class="table-col-code col-priority-1">
                                <span class="refund-badge-code"><?= e($r['refund_number']) ?></span>
                                <div class="refund-date-sub"><?= format_date($r['refund_date']) ?></div>
                            </td>

                            <!-- 2. Booking column -->
                            <td class="table-col-code col-priority-1">
                                <?php if (!empty($r['booking_db_id'])): ?>
                                    <a href="<?= url('/office/bookings/' . (int)$r['booking_db_id']) ?>" class="booking-link-text" title="<?= e(__('tooltip.view_booking_details', 'View booking details')) ?>">
                                        <?= e($r['booking_code']) ?>
                                    </a>
                                <?php elseif (!empty($r['booking_code'])): ?>
                                    <strong style="color: var(--vg-navy-900); font-size: 0.88rem;"><?= e($r['booking_code']) ?></strong>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.82rem;">Direct / Unlinked</span>
                                <?php endif; ?>
                                <?php if (!empty($r['invoice_number'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                        Inv: <?= e($r['invoice_number']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 3. Customer column -->
                            <td class="table-col-name col-priority-1">
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;">
                                    <?= e($r['customer_name'] ?? 'Direct Client') ?>
                                </strong>
                                <?php if (!empty($r['customer_phone']) || !empty($r['customer_email'])): ?>
                                    <div class="customer-contact-sub">
                                        <?= e($r['customer_phone'] ?: $r['customer_email']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Amount column -->
                            <td class="table-col-amount col-priority-1 text-end">
                                <strong style="font-size: 0.96rem; color: #D95757; font-family: inherit;">
                                    <?= format_inr($r['amount']) ?>
                                </strong>
                            </td>

                            <!-- 5. Refund Method column -->
                            <td class="table-col-category col-priority-2" data-column-priority="secondary">
                                <span class="refund-method-tag">
                                    <?php if ($r['refund_method'] === 'upi'): ?>
                                        <i class="fa-solid fa-mobile-screen-button"></i> UPI
                                    <?php elseif ($r['refund_method'] === 'cash'): ?>
                                        <i class="fa-solid fa-money-bill-wave"></i> Cash
                                    <?php elseif ($r['refund_method'] === 'gateway'): ?>
                                        <i class="fa-solid fa-credit-card"></i> Gateway
                                    <?php else: ?>
                                        <i class="fa-solid fa-building-columns"></i> Bank Transfer
                                    <?php endif; ?>
                                </span>
                            </td>

                            <!-- 6. Status column -->
                            <td class="table-col-status text-center col-priority-1">
                                <?php if ($st === 'processed'): ?>
                                    <span class="status-pill status-pill-processed">
                                        <i class="fa-solid fa-circle-check"></i> Processed
                                    </span>
                                <?php elseif ($st === 'approved'): ?>
                                    <span class="status-pill status-pill-approved">
                                        <i class="fa-solid fa-thumbs-up"></i> Approved
                                    </span>
                                <?php elseif ($st === 'rejected'): ?>
                                    <span class="status-pill status-pill-rejected">
                                        <i class="fa-solid fa-circle-xmark"></i> Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-pending">
                                        <i class="fa-solid fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 7. Actions column (Single Actions Menu) -->
                            <td class="table-col-actions text-end col-priority-1">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" aria-label="<?= e(__('common.actions', 'Actions')) ?>">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                        <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/finance/refunds/' . (int)$r['id']) ?>" class="action-dropdown-item" data-no-ajax="true">
                                            <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                                        </a>
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="action-dropdown-item" onclick="openEditRefundModal(<?= $jsonData ?>)">
                                                <i class="fa-solid fa-pen-to-square text-info"></i> <span>Edit Claim</span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canProcess): ?>
                                            <button type="button" class="action-dropdown-item text-success" onclick="openProcessRefundModal(<?= $jsonData ?>)">
                                                <i class="fa-solid fa-hand-holding-dollar"></i> <span>Process Refund</span>
                                            </button>
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item text-danger" onclick="openRejectRefundModal(<?= $jsonData ?>)">
                                                <i class="fa-solid fa-ban"></i> <span>Reject Claim</span>
                                            </button>
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

        <!-- Mobile Cards List: Responsive Cards Layout (Section 5) -->
        <div class="cards-grid" id="refundsMobileCards">
            <?php if (empty($refunds)): ?>
                <div class="no-data-message" style="padding: 2.5rem 1rem; grid-column: 1 / -1;">
                    <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h4 style="font-weight:700; color:#1e293b; margin-bottom:6px;">No refund claims recorded</h4>
                    <p class="text-muted" style="margin:0;">No claims found for this selection.</p>
                </div>
            <?php else: ?>
                <?php foreach ($refunds as $r):
                    $st = strtolower($r['status'] ?? 'pending');
                    $isProcessed = ($st === 'processed');
                    $isPending   = ($st === 'pending');
                    $isApproved  = ($st === 'approved');
                    $isRejected  = ($st === 'rejected');
                    $canEdit     = ($isPending || $isApproved);
                    $canProcess  = ($isPending || $isApproved);

                    $mJsonData = htmlspecialchars(json_encode([
                        'id'                    => (int)$r['id'],
                        'refund_number'         => $r['refund_number'],
                        'amount'                => (float)$r['amount'],
                        'amount_formatted'      => format_inr($r['amount']),
                        'refund_date'           => $r['refund_date'],
                        'refund_date_formatted' => format_date($r['refund_date']),
                        'refund_method'         => $r['refund_method'],
                        'reason'                => $r['reason'] ?? '',
                        'notes'                 => $r['notes'] ?? '',
                        'status'                => $st,
                        'status_label'          => ucfirst($st),
                        'created_at'            => $r['created_at'],
                        'created_at_formatted'  => format_date($r['created_at']),
                        'customer_name'         => $r['customer_name'] ?? 'Direct Client',
                        'customer_phone'        => $r['customer_phone'] ?? '',
                        'customer_email'        => $r['customer_email'] ?? '',
                        'booking_code'          => $r['booking_code'] ?: 'Unlinked',
                        'booking_id'            => $r['booking_db_id'] ?? null,
                        'invoice_id'            => $r['invoice_id'] ?? null,
                        'invoice_number'        => $r['invoice_number'] ?? '',
                        'invoice_total'         => (float)($r['invoice_total'] ?? 0),
                        'invoice_paid'          => (float)($r['invoice_paid'] ?? 0),
                        'processed_by_name'     => $r['processed_by_name'] ?? '',
                        'processed_at'          => $r['processed_at'] ?? '',
                        'processed_account_name'=> $r['processed_account_name'] ?? '',
                        'processed_tx_ref'      => $r['processed_tx_ref'] ?? $r['gateway_refund_id'] ?? '',
                    ]), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="mv-booking-card-wrapper" id="card-refund-<?= (int)$r['id'] ?>">
                    <div class="mv-booking-card" style="display: block; padding: 1.15rem;">
                        <!-- Top Row: Customer Name & Status -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="display: block; font-size: 0.98rem; color: var(--vg-navy-900);">
                                    <?= e($r['customer_name'] ?? 'Direct Client') ?>
                                </strong>
                                <span style="font-size: 0.76rem; color: var(--text-muted); font-family: monospace;">
                                    <?= e($r['refund_number']) ?> &bull; <?= e($r['booking_code'] ?: 'Unlinked') ?>
                                </span>
                            </div>
                            <div>
                                <?php if ($st === 'processed'): ?>
                                    <span class="status-pill status-pill-processed">Processed</span>
                                <?php elseif ($st === 'approved'): ?>
                                    <span class="status-pill status-pill-approved">Approved</span>
                                <?php elseif ($st === 'rejected'): ?>
                                    <span class="status-pill status-pill-rejected">Rejected</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-pending">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Mid Row: Amount and Reason -->
                        <div style="background: #F8FAFC; border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Refund Amount</span>
                                <strong style="font-size: 1.1rem; color: #D95757;"><?= format_inr($r['amount']) ?></strong>
                            </div>
                            <div style="text-align: right;">
                                <span style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Method &bull; Date</span>
                                <span style="font-size: 0.8rem; font-weight: 600; color: #334155; text-transform: uppercase;">
                                    <?= e(str_replace('_', ' ', $r['refund_method'])) ?>
                                </span>
                                <div style="font-size: 0.72rem; color: #64748B;"><?= format_date($r['refund_date']) ?></div>
                            </div>
                        </div>

                        <?php if (!empty($r['reason'])): ?>
                            <div style="font-size: 0.82rem; color: #475569; margin-bottom: 0.85rem; line-height: 1.4;">
                                <strong>Reason:</strong> <?= e($r['reason']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Bottom Actions (Single Horizontal Row) -->
                        <div class="mv-card-actions">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" aria-label="<?= e(__('common.actions', 'Actions')) ?>">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                    <span>Actions</span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <a href="<?= url('/office/finance/refunds/' . (int)$r['id']) ?>" class="action-dropdown-item view" data-no-ajax="true">
                                        <i class="fa-solid fa-eye text-primary"></i> <span>View Details</span>
                                    </a>
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="action-dropdown-item edit" onclick="openEditRefundModal(<?= $mJsonData ?>)">
                                            <i class="fa-solid fa-pen-to-square text-info"></i> <span>Edit Claim</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canProcess): ?>
                                        <button type="button" class="action-dropdown-item pay text-success" onclick="openProcessRefundModal(<?= $mJsonData ?>)">
                                            <i class="fa-solid fa-hand-holding-dollar"></i> <span>Process Refund</span>
                                        </button>
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger text-danger" onclick="openRejectRefundModal(<?= $mJsonData ?>)">
                                            <i class="fa-solid fa-ban"></i> <span>Reject Claim</span>
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

    <!-- 5. Modern Pagination -->
    <?php
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 15;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 15) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/finance/refunds?' . http_build_query($q));
        };
    ?>
    <div id="refundsPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> refund claim(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="<?= e(__('pagination.previous', 'Previous Page')) ?>" data-page="<?= max(1, $currP - 1) ?>">&#10094;</a>
                <?php
                    $rangeStart = max(1, $currP - 1);
                    $rangeEnd   = min($totalPages, $rangeStart + 2);
                    if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                        $rangeStart = max(1, $rangeEnd - 2);
                    }
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                ?>
                    <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($rangeEnd < $totalPages): ?>
                    <span class="dots">...</span>
                    <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>" data-page="<?= $totalPages ?>"><?= $totalPages ?></a>
                <?php endif; ?>
                <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="<?= e(__('pagination.next', 'Next Page')) ?>" data-page="<?= min($totalPages, $currP + 1) ?>">&#10095;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: Issue Customer Refund (Claim Creation Only - Section 7 & 8)       -->
<!-- ========================================================================= -->
<div id="issueRefundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'issueRefundModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 580px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(33, 135, 101, 0.12); color: #218765; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">Issue Customer Refund</h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.78rem; color: var(--text-muted);">Create a formal refund claim for review and statutory tracking.</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeIssueRefundModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/refunds') ?>" method="POST" id="issueRefundForm" onsubmit="return handleIssueFormSubmit(this)">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <!-- Customer / Invoice Selection -->
                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="ref_invoice_id">Linked Customer Invoice <span class="req">*</span></label>
                    <select name="invoice_id" id="ref_invoice_id" class="form-select" onchange="onInvoiceSelectionChange(this)" required>
                        <option value="">— Select Customer Invoice / Reservation —</option>
                        <?php foreach ($eligibleInvoices as $inv): ?>
                            <option value="<?= $inv['id'] ?>"
                                    data-paid="<?= $inv['paid_amount'] ?>"
                                    data-max="<?= $inv['remaining_refundable'] ?>"
                                    data-customer="<?= e($inv['customer_name'] ?? 'Customer') ?>"
                                    data-phone="<?= e($inv['customer_phone'] ?? '') ?>"
                                    data-email="<?= e($inv['customer_email'] ?? '') ?>"
                                    data-booking="<?= e($inv['booking_code'] ?? 'Booking') ?>"
                                    data-total="<?= $inv['total_amount'] ?>">
                                <?= e($inv['invoice_number']) ?> &bull; <?= e($inv['customer_name'] ?? 'Client') ?> &bull; <?= e($inv['booking_code'] ?? '') ?> (Refundable: <?= format_inr($inv['remaining_refundable']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($eligibleInvoices)): ?>
                        <div style="font-size: 0.75rem; color: #B45309; margin-top: 4px;">
                            <i class="fa-solid fa-circle-info"></i> No invoices with positive unrefunded balances found. Record a guest payment on an invoice first.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Customer Info Preview Box (Shows upon invoice selection) -->
                <div id="invoicePreviewBox" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.75rem 0.95rem; margin-bottom: 0.95rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem;">
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Customer:</span>
                            <div style="font-weight: 700; color: #0F172A;" id="pvCustomerName">—</div>
                            <div style="font-size: 0.74rem; color: #64748B;" id="pvCustomerContact">—</div>
                        </div>
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Booking / Reservation:</span>
                            <div style="font-weight: 700; color: #0F172A;" id="pvBookingCode">—</div>
                            <div style="font-size: 0.74rem; color: #166534;">
                                Max Refundable: <strong id="pvMaxRefundable">₹0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund Amount & Refund Date -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.95rem;">
                    <div class="form-group">
                        <label class="form-label" for="ref_amount">Refund Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="ref_amount" class="form-control" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>" required>
                        <span style="font-size: 0.72rem; color: var(--text-muted);" id="refAmountHint">Must be &gt; 0</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ref_date">Refund Claim Date <span class="req">*</span></label>
                        <input type="date" name="refund_date" id="ref_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Refund Method -->
                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="ref_method">Intended Refund Method <span class="req">*</span></label>
                    <select name="refund_method" id="ref_method" class="form-select" required>
                        <option value="bank_transfer">Bank Transfer (NEFT / IMPS / RTGS)</option>
                        <option value="upi">UPI (Google Pay / PhonePe / Paytm)</option>
                        <option value="cash">Counter Cash Payment</option>
                        <option value="gateway">Payment Gateway Reversal</option>
                    </select>
                </div>

                <!-- Reason for Refund -->
                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="ref_reason">Reason for Refund <span class="req">*</span></label>
                    <textarea name="reason" id="ref_reason" class="form-control" rows="2" placeholder="<?= e(__('placeholder.e_g_customer_cancellation_7_da', 'e.g. Customer cancellation 7 days prior to departure / Itinerary adjustment')) ?>" required></textarea>
                </div>

                <!-- Optional Notes -->
                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" for="ref_notes">Internal Remarks / Notes (Optional)</label>
                    <input type="text" name="notes" id="ref_notes" class="form-control" placeholder="<?= e(__('placeholder.optional_notes_for_office_reco', 'Optional notes for office record')) ?>">
                </div>

                <!-- Clear Safety Message (Section 8) -->
                <div class="safety-claim-banner">
                    <i class="fa-solid fa-circle-info" style="color: #2563EB; font-size: 1.1rem; margin-top: 1px;"></i>
                    <div style="font-size: 0.8rem; color: #1E3A8A; line-height: 1.45;">
                        <strong>This creates a refund claim only.</strong> No money will be deducted from cash or bank until the refund is processed.
                    </div>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeIssueRefundModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="btnSubmitClaim">
                    <i class="fa-solid fa-plus"></i> <span>Create Refund Claim</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: Dedicated Process Customer Refund (Real Money Outflow - Sec 13)  -->
<!-- ========================================================================= -->
<div id="processRefundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'processRefundModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 560px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(33, 135, 101, 0.12); color: #218765; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">Process Customer Refund</h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.78rem; color: var(--text-muted);">Record actual company treasury disbursement and settle claim.</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeProcessRefundModal()">&times;</button>
        </div>
        <form id="processRefundForm" method="POST" action="" onsubmit="return handleProcessFormSubmit(this)">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <!-- Clear Prominent Warning (Section 13) -->
                <div class="payment-outflow-warning">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.35rem; color: #D95757;"></i>
                        <div>
                            <div style="font-weight: 800; font-size: 1rem; color: #991B1B;" id="prWarningBanner">
                                ₹0.00 will be paid to the customer.
                            </div>
                            <div style="font-size: 0.78rem; color: #7F1D1D; margin-top: 2px;">
                                This action will record a real cash/bank outflow and deduct company account balance.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund Claim Summary Box -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.75rem 0.95rem; margin-bottom: 1rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.82rem;">
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Refund Number:</span>
                            <div style="font-weight: 700; color: var(--brand-primary); font-family: monospace;" id="prRefundNumber">—</div>
                        </div>
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Customer Name:</span>
                            <div style="font-weight: 700; color: #0F172A;" id="prCustomerName">—</div>
                        </div>
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Booking / Invoice:</span>
                            <div style="font-weight: 600; color: #334155;" id="prBookingInfo">—</div>
                        </div>
                        <div>
                            <span style="color: #64748B; font-size: 0.72rem; text-transform: uppercase;">Claim Reason:</span>
                            <div style="color: #475569; font-size: 0.78rem;" id="prReason">—</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Account (Required) -->
                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="pr_account_id">Disbursement Treasury Account <span class="req">*</span></label>
                    <select name="account_id" id="pr_account_id" class="form-select" required>
                        <option value="">— Select Company Bank / Cash Account —</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                <?= e($acc['account_name']) ?> (<?= ucfirst($acc['account_type']) ?> &bull; Available: <?= format_inr($acc['current_balance']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Actual Payment Method & Processing Date -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.95rem;">
                    <div class="form-group">
                        <label class="form-label" for="pr_payment_method">Actual Payment Mode <span class="req">*</span></label>
                        <select name="payment_method" id="pr_payment_method" class="form-select" required>
                            <option value="bank_transfer">Bank Transfer (NEFT / IMPS)</option>
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="cash">Counter Cash Disbursement</option>
                            <option value="gateway">Gateway Reversal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pr_processed_date">Processing Date <span class="req">*</span></label>
                        <input type="date" name="processed_date" id="pr_processed_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Transaction Reference / UTR -->
                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="pr_transaction_ref">Transaction Reference / UTR Number</label>
                    <input type="text" name="transaction_ref" id="pr_transaction_ref" class="form-control" placeholder="<?= e(__('placeholder.e_g_utr_20261110_8842_cheque', 'e.g. UTR-20261110-8842 / Cheque #')) ?>">
                    <span style="font-size: 0.72rem; color: var(--text-muted);">Recommended for statutory bank reconciliation</span>
                </div>

                <!-- Settlement Notes -->
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label class="form-label" for="pr_notes">Settlement Remarks (Optional)</label>
                    <input type="text" name="notes" id="pr_notes" class="form-control" placeholder="<?= e(__('placeholder.internal_finance_remarks_on_pa', 'Internal finance remarks on payout...')) ?>">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeProcessRefundModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="btnConfirmPayment" style="background: #218765; border-color: #218765;">
                    <i class="fa-solid fa-shield-check"></i> <span>Confirm Refund Payment</span>
                </button>
            </div>
        </form>
    </div>
</div>



<!-- ========================================================================= -->
<!-- MODAL 4: Edit Refund Claim (Pending/Approved Only - Section 11)           -->
<!-- ========================================================================= -->
<div id="editRefundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editRefundModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 540px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(37, 99, 235, 0.12); color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">Edit Refund Claim</h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.78rem; color: var(--text-muted);">Modify refund claim parameters before payout.</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeEditRefundModal()">&times;</button>
        </div>
        <form id="editRefundForm" method="POST" action="" onsubmit="return handleEditFormSubmit(this)">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 0.95rem; font-size: 0.82rem;">
                    Editing Claim: <strong id="edRefundNumber" style="font-family: monospace; color: var(--brand-primary);">—</strong>
                    &bull; Customer: <strong id="edCustomerName">—</strong>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.95rem;">
                    <div class="form-group">
                        <label class="form-label" for="ed_amount">Refund Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="ed_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed_refund_date">Claim Date <span class="req">*</span></label>
                        <input type="date" name="refund_date" id="ed_refund_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="ed_refund_method">Refund Mode <span class="req">*</span></label>
                    <select name="refund_method" id="ed_refund_method" class="form-select" required>
                        <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                        <option value="upi">UPI (GPay / PhonePe)</option>
                        <option value="cash">Counter Cash</option>
                        <option value="gateway">Payment Gateway Reversal</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0.95rem;">
                    <label class="form-label" for="ed_reason">Reason for Refund <span class="req">*</span></label>
                    <textarea name="reason" id="ed_reason" class="form-control" rows="2" required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label class="form-label" for="ed_notes">Internal Remarks</label>
                    <input type="text" name="notes" id="ed_notes" class="form-control" placeholder="<?= e(__('placeholder.optional_notes', 'Optional notes...')) ?>">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditRefundModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="btnSaveEdit">
                    <i class="fa-solid fa-check"></i> <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: Reject Refund Claim (Section 16 & 27)                            -->
<!-- ========================================================================= -->
<div id="rejectRefundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'rejectRefundModalBackdrop')">
    <div class="modal-dialog-custom modal-dialog-delete" style="max-width: 480px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(217, 87, 87, 0.12); color: #D95757; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #991B1B;">Reject Refund Claim</h3>
                    <p style="margin: 0.15rem 0 0 0; font-size: 0.78rem; color: var(--text-muted);">Decline this refund request. No payment will be made.</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeRejectRefundModal()">&times;</button>
        </div>
        <form id="rejectRefundForm" method="POST" action="" onsubmit="return handleRejectFormSubmit(this)">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="rejected">
            <div class="modal-body-custom">
                <p style="font-size: 0.88rem; color: #334155; margin-bottom: 0.85rem; line-height: 1.5;">
                    Are you sure you want to reject refund claim <strong id="rjRefundNumber" style="font-family: monospace;">—</strong> for <strong id="rjAmount">₹0.00</strong>?
                </p>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="rj_notes">Reason for Rejection <span class="req">*</span></label>
                    <textarea name="notes" id="rj_notes" class="form-control" rows="2" placeholder="<?= e(__('placeholder.e_g_booking_policy_does_not_al', 'e.g. Booking policy does not allow refund after departure date')) ?>" required></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeRejectRefundModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="btnConfirmReject" style="background: #DC2626; border-color: #DC2626;">
                    <i class="fa-solid fa-ban"></i> <span>Reject Claim</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- COMPONENT JAVASCRIPT & EVENT HANDLERS                                     -->
<!-- ========================================================================= -->
<script>
    // 1. Issue Refund Modal Handlers
    function openIssueRefundModal() {
        document.getElementById('issueRefundForm').reset();
        document.getElementById('invoicePreviewBox').style.display = 'none';
        document.getElementById('refAmountHint').textContent = 'Must be > 0';
        document.getElementById('issueRefundModalBackdrop').style.display = 'flex';
    }

    function closeIssueRefundModal() {
        document.getElementById('issueRefundModalBackdrop').style.display = 'none';
    }

    function onInvoiceSelectionChange(sel) {
        const opt = sel.options[sel.selectedIndex];
        const previewBox = document.getElementById('invoicePreviewBox');
        const amountInput = document.getElementById('ref_amount');
        const hint = document.getElementById('refAmountHint');

        if (opt && opt.value) {
            const max = parseFloat(opt.dataset.max || 0);
            const customer = opt.dataset.customer || 'Customer';
            const phone = opt.dataset.phone || '';
            const email = opt.dataset.email || '';
            const booking = opt.dataset.booking || 'Booking';

            document.getElementById('pvCustomerName').textContent = customer;
            document.getElementById('pvCustomerContact').textContent = phone ? phone : email;
            document.getElementById('pvBookingCode').textContent = booking;
            document.getElementById('pvMaxRefundable').textContent = '₹ ' + max.toLocaleString('en-IN', {minimumFractionDigits: 2});

            amountInput.max = max;
            amountInput.placeholder = 'Max ' + max.toFixed(2);
            hint.textContent = 'Maximum allowable refund: ₹ ' + max.toLocaleString('en-IN', {minimumFractionDigits: 2});

            previewBox.style.display = 'block';
        } else {
            previewBox.style.display = 'none';
            amountInput.removeAttribute('max');
            amountInput.placeholder = '0.00';
            hint.textContent = 'Must be > 0';
        }
    }

    function handleIssueFormSubmit(form) {
        const btn = document.getElementById('btnSubmitClaim');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Creating Claim...</span>';
        }
        return true;
    }

    // 2. Process Refund Modal Handlers (Section 13)
    let currentProcessRefundData = null;
    function openProcessRefundModal(r) {
        currentProcessRefundData = r;
        document.getElementById('processRefundForm').action = '<?= url('/office/finance/refunds') ?>/' + r.id + '/process';
        document.getElementById('prRefundNumber').textContent = r.refund_number;
        document.getElementById('prCustomerName').textContent = r.customer_name;
        document.getElementById('prBookingInfo').textContent = r.booking_code + (r.invoice_number ? ' (' + r.invoice_number + ')' : '');
        document.getElementById('prReason').textContent = r.reason || 'Customer cancellation';

        const amountStr = '₹ ' + r.amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('prWarningBanner').textContent = amountStr + ' will be paid to the customer.';

        // Default payment method if available
        const methodSelect = document.getElementById('pr_payment_method');
        if (methodSelect && r.refund_method) {
            methodSelect.value = r.refund_method;
        }

        document.getElementById('processRefundModalBackdrop').style.display = 'flex';
    }

    function closeProcessRefundModal() {
        document.getElementById('processRefundModalBackdrop').style.display = 'none';
    }

    function handleProcessFormSubmit(form) {
        const accSelect = document.getElementById('pr_account_id');
        if (!accSelect || !accSelect.value) {
            alertToast('Please select a company payment account.', 'error');
            return false;
        }

        const btn = document.getElementById('btnConfirmPayment');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Disbursing Funds...</span>';
        }
        return true;
    }

    // 3. Mobile Filter Drawer Toggle
    function toggleMobileFilter() {
        const panel = document.getElementById('mvFilterPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    // 4. Edit Refund Modal Handlers (Section 11)
    function openEditRefundModal(r) {
        document.getElementById('editRefundForm').action = '<?= url('/office/finance/refunds') ?>/' + r.id + '/edit';
        document.getElementById('edRefundNumber').textContent = r.refund_number;
        document.getElementById('edCustomerName').textContent = r.customer_name;
        document.getElementById('ed_amount').value = r.amount;
        document.getElementById('ed_refund_date').value = r.refund_date || '<?= date('Y-m-d') ?>';
        document.getElementById('ed_refund_method').value = r.refund_method || 'bank_transfer';
        document.getElementById('ed_reason').value = r.reason || '';
        document.getElementById('ed_notes').value = r.notes || '';

        document.getElementById('editRefundModalBackdrop').style.display = 'flex';
    }

    function closeEditRefundModal() {
        document.getElementById('editRefundModalBackdrop').style.display = 'none';
    }

    function handleEditFormSubmit(form) {
        const btn = document.getElementById('btnSaveEdit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving...</span>';
        }
        return true;
    }

    // 5. Reject Refund Modal Handlers
    function openRejectRefundModal(r) {
        document.getElementById('rejectRefundForm').action = '<?= url('/office/finance/refunds') ?>/' + r.id + '/status';
        document.getElementById('rjRefundNumber').textContent = r.refund_number;
        document.getElementById('rjAmount').textContent = '₹ ' + r.amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('rj_notes').value = '';

        document.getElementById('rejectRefundModalBackdrop').style.display = 'flex';
    }

    function closeRejectRefundModal() {
        document.getElementById('rejectRefundModalBackdrop').style.display = 'none';
    }

    function handleRejectFormSubmit(form) {
        const btn = document.getElementById('btnConfirmReject');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Rejecting...</span>';
        }
        return true;
    }

    // 6. Generic Modal Backdrop Click and ESC key support
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeIssueRefundModal();
            closeProcessRefundModal();
            closeEditRefundModal();
            closeRejectRefundModal();
        }
    });

    // Custom non-blocking toast helper
    function alertToast(msg, type = 'info') {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify(msg, type);
        } else if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
        } else {
            console.warn(msg);
        }
    }
</script>

<style>
/* Scoped Styling for Customer Refunds Page */
.refund-badge-code {
    display: inline-block;
    font-family: monospace;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--brand-primary);
}
.refund-date-sub {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-top: 2px;
}
.booking-link-text {
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--brand-primary);
    text-decoration: none;
}
.booking-link-text:hover {
    text-decoration: underline;
}
.customer-contact-sub {
    font-size: 0.76rem;
    color: var(--text-muted);
    margin-top: 2px;
}
.refund-method-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: capitalize;
}
.status-pill-pending {
    background: rgba(199, 138, 53, 0.12);
    color: #C78A35;
}
.status-pill-approved {
    background: rgba(37, 99, 235, 0.12);
    color: #2563EB;
}
.status-pill-processed {
    background: rgba(33, 135, 101, 0.12);
    color: #218765;
}
.status-pill-rejected {
    background: rgba(217, 87, 87, 0.12);
    color: #D95757;
}

.table-actions-group {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    justify-content: center;
}
.action-btn {
    padding: 4px 7px;
    font-size: 0.75rem;
    line-height: 1;
}
.action-btn.is-disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}
.action-btn-process {
    padding: 4px 9px;
    font-size: 0.75rem;
    line-height: 1;
    background: #218765;
    border-color: #218765;
    color: #FFF;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}
.action-btn-process:hover {
    background: #196f53;
    border-color: #196f53;
}
.action-btn-reject {
    padding: 4px 7px;
    font-size: 0.75rem;
    line-height: 1;
    color: #DC2626;
    border-color: #FECDD3;
}
.action-btn-reject:hover {
    background: #FEF2F2;
    border-color: #F87171;
}
.action-settled-label {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.74rem;
    font-weight: 700;
    color: #166534;
    background: rgba(33, 135, 101, 0.08);
    padding: 3px 8px;
    border-radius: 6px;
}
.action-rejected-label {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.74rem;
    font-weight: 700;
    color: #991B1B;
    background: rgba(217, 87, 87, 0.08);
    padding: 3px 8px;
    border-radius: 6px;
}

.safety-claim-banner {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin-top: 0.5rem;
}
.payment-outflow-warning {
    background: #FEF2F2;
    border: 1.5px solid #FCA5A5;
    border-radius: 12px;
    padding: 0.95rem 1.15rem;
    margin-bottom: 1.15rem;
}

@media (max-width: 768px) {
    #financeRefundsPage .booking-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem !important;
        margin-bottom: 1.15rem !important;
    }
    #financeRefundsPage .booking-kpi-card {
        padding: 0.85rem 0.95rem !important;
        min-height: 92px !important;
        border-radius: 14px !important;
    }
    #financeRefundsPage .modern-table-container {
        display: none !important;
    }
    #refundsMobileCards {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
    }
}
@media (min-width: 769px) {
    #refundsMobileCards {
        display: none !important;
    }
}
</style>
