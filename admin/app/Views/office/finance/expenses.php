<?php
/**
 * OPERATING & FLEET EXPENSES
 * Matching Customers / Invoices Architecture & Aesthetics
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/expense-categories') . '" class="btn btn-outline"><i class="fa-solid fa-tags"></i> <span>Expense Categories</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openExpenseModal()"><i class="fa-solid fa-plus"></i> <span>Add Expense</span></button>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="financeExpensesPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Operating & Fleet Expenses',
            'description' => 'Comprehensive expense auditing for bulk fleet fuel, vehicle maintenance, driver daily allowances, office rent, and utility disbursements.',
            'badge'       => 'Disbursement Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Expenses', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="expensesKpiGrid">
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Recorded Expenses</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll) ?></div>
            <div class="booking-kpi-sub">
                <span>All vouchers recorded</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'paid' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses?status=paid') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Settled Disbursements</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($totalPaid) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Verified paid from vaults</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedCategory === 'fuel_bulk' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses?category=fuel_bulk') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Fleet Fuel (Diesel)</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_currency($fuelTotal) ?></div>
            <div class="booking-kpi-sub">
                <span>Fleet fueling expenditures</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses?status=pending') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Approvals</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $pendingCount > 0 ? '#D95757' : 'var(--brand-primary)' ?>;">
                <?= format_currency($totalPending) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $pendingCount ?> vouchers awaiting review</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/expenses') ?>" class="filter-bar-compact" id="expenseFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_by_voucher_title_vendor', 'Search by voucher #, title, vendor...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/expenses') ?>'" title="<?= e(__('common.clear', 'Clear search')) ?>">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="paid" <?= ($selectedStatus ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="approved" <?= ($selectedStatus ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review</option>
            <option value="rejected" <?= ($selectedStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <select name="category" id="categoryInput">
            <option value="">All Categories</option>
            <?php foreach ($categories as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= ($selectedCategory ?? '') === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="per_page" id="perPageInput" title="<?= e(__('common.records_per_page', 'Records per page')) ?>">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <a href="<?= url('/office/finance/expenses') ?>" class="fbc-btn" title="<?= e(__('common.reset_filters', 'Reset Filters')) ?>"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="<?= e(__('common.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Operating Expenses</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openExpenseModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Add Exp</span>
            </button>
        </div>

        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/expenses') ?>" class="mv-search-form">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" placeholder="<?= e(__('placeholder.search_title_vendor', 'Search title, vendor...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
                </div>
            </form>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/finance/expenses') ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Vouchers</span>
                <span class="mv-pill-cnt"><?= number_format($totalAll) ?></span>
            </a>
            <a href="<?= url('/office/finance/expenses?status=paid') ?>" class="mv-pill <?= $selectedStatus === 'paid' ? 'active' : '' ?>">
                <span>Paid</span>
            </a>
            <a href="<?= url('/office/finance/expenses?status=pending') ?>" class="mv-pill <?= $selectedStatus === 'pending' ? 'active' : '' ?>">
                <span>Pending</span>
                <span class="mv-pill-cnt"><?= $pendingCount ?></span>
            </a>
            <a href="<?= url('/office/finance/expenses?category=fuel_bulk') ?>" class="mv-pill <?= $selectedCategory === 'fuel_bulk' ? 'active' : '' ?>">
                <span>Fuel</span>
            </a>
        </div>
    </div>

    <!-- 5. Modern Table Section -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Expense Vouchers Manifest</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="col-priority-1 table-col-code"><?= e(__('table.voucher', 'Voucher')) ?></th>
                        <th class="col-priority-2 table-col-date" data-column-priority="secondary"><?= e(__('table.date', 'Date')) ?></th>
                        <th class="col-priority-2 table-col-category" data-column-priority="secondary"><?= e(__('table.category', 'Category')) ?></th>
                        <th class="col-priority-1 table-col-name">Description &amp; Payee</th>
                        <th class="col-priority-3 col-optional table-col-name" data-column-priority="optional"><?= e(__('table.account', 'Account')) ?></th>
                        <th class="col-priority-1 table-col-status"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="col-priority-1 table-col-amount"><?= e(__('table.amount', 'Amount')) ?></th>
                        <th class="col-priority-1 table-col-actions"><?= e(__('common.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No expenses recorded</h4>
                                <p class="text-muted" style="margin:0;">Use the "Add Expense" button to log an operational payment voucher.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $e):
                            $st = strtolower($e['status']);
                            $stClass = $st === 'paid' ? 'completed' : ($st === 'approved' ? 'completed' : ($st === 'rejected' ? 'cancelled' : 'pending'));
                            $catLabel = $categories[$e['category']] ?? ucwords(str_replace('_', ' ', $e['category']));
                        ?>
                        <tr>
                            <td class="col-priority-1">
                                <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.88rem;"><?= e($e['expense_code']) ?></strong>
                            </td>
                            <td class="col-priority-2" data-column-priority="secondary" style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= format_date($e['expense_date']) ?>
                            </td>
                            <td class="col-priority-2" data-column-priority="secondary">
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900); display: block;"><?= e($catLabel) ?></span>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);"><?= e(str_replace('_', ' ', $e['payment_method'])) ?></span>
                            </td>
                            <td class="col-priority-1">
                                <strong class="table-cell-truncate" style="color: var(--vg-navy-900); font-size: 0.9rem;" title="<?= e($e['title']) ?>"><?= e($e['title']) ?></strong>
                                <?php if (!empty($e['vendor_name'])): ?>
                                    <div class="table-cell-truncate" style="font-size: 0.8rem; color: var(--text-muted);" title="Vendor: <?= e($e['vendor_name']) ?>">Vendor: <?= e($e['vendor_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-priority-3 col-optional" data-column-priority="optional">
                                <span style="font-size: 0.85rem; color: var(--vg-navy-900); font-weight: 600;"><?= e($e['account_name'] ?? 'Direct Vault') ?></span>
                            </td>
                            <td class="text-center col-priority-1">
                                <span class="cust-toggle-pill <?= $stClass ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $stClass ?>"></span>
                                    <span><?= strtoupper($st) ?></span>
                                </span>
                            </td>
                            <td class="col-priority-1" style="text-align: right;">
                                <strong style="font-size: 1rem; color: var(--status-danger);">
                                    <?= format_currency((float)$e['amount']) ?>
                                </strong>
                            </td>
                            <td class="text-end col-priority-1" style="white-space: nowrap;">
                                <?php if ($st === 'pending'): ?>
                                    <div class="action-dropdown-wrap">
                                        <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('common.actions', 'Actions')) ?>" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i> <span>Actions</span>
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="paid">
                                                <input type="hidden" name="account_id" value="<?= $accounts[0]['id'] ?? 1 ?>">
                                                <button type="submit" class="action-dropdown-item pay">
                                                    <i class="fa-solid fa-circle-check"></i> <span>Record Payment</span>
                                                </button>
                                            </form>
                                            <div class="action-dropdown-divider"></div>
                                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="action-dropdown-item danger">
                                                    <i class="fa-solid fa-circle-xmark"></i> <span>Reject</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.78rem;">Settled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid">
            <?php foreach ($expenses as $e):
                $st = strtolower($e['status']);
                $catLabel = $categories[$e['category']] ?? ucwords(str_replace('_', ' ', $e['category']));
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(217, 87, 87, 0.12); color: #D95757; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($e['title']) ?></strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($catLabel) ?> • <?= format_date($e['expense_date']) ?></span>
                            <div style="font-size: 0.75rem; color: var(--brand-primary); font-family: monospace;"><?= e($e['expense_code']) ?></div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: var(--status-danger); display: block;">
                            <?= format_currency((float)$e['amount']) ?>
                        </strong>
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: <?= $st === 'paid' ? '#218765' : '#C78A35' ?>;"><?= e($st) ?></span>
                    </div>
                </div>
                <?php if ($st === 'pending'): ?>
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <div class="action-dropdown-menu">
                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0; display: inline-flex;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="paid">
                                <input type="hidden" name="account_id" value="<?= $accounts[0]['id'] ?? 1 ?>">
                                <button type="submit" class="action-dropdown-item pay">
                                    <i class="fa-solid fa-circle-check"></i> <span>Record Payment</span>
                                </button>
                            </form>
                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0; display: inline-flex;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="action-dropdown-item danger">
                                    <i class="fa-solid fa-circle-xmark"></i> <span>Reject</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Modern Pagination (Canonical Project Standard) -->
        <?php
            $totRec = $totalRecords ?? count($expenses);
            $currP = $currentPage ?? 1;
            $pSize = $perPage ?? 15;
            $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
            $to   = min($currP * $pSize, $totRec);
            $buildPageUrl = function(int $p) use ($search, $selectedStatus, $selectedCategory, $pSize) {
                $q = [];
                if (!empty($search)) $q['search'] = $search;
                if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
                if (!empty($selectedCategory)) $q['category'] = $selectedCategory;
                if (!empty($pSize) && $pSize != 15) $q['per_page'] = $pSize;
                $q['page'] = $p;
                return url('/office/finance/expenses?' . http_build_query($q));
            };
        ?>
        <div id="expensesPaginationWrapper">
            <?php if (($totalPages ?? 1) > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> expense(s)</div>
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
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Add Expense                                                        -->
<!-- ========================================================================= -->
<div id="expenseModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'expenseModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-receipt" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Log Operating Expense Voucher
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeExpenseModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/expenses') ?>" method="POST" id="createExpenseForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_category">Expense Category <span class="req">*</span></label>
                        <select name="category" id="exp_category" class="form-select" required>
                            <?php foreach ($categories as $k => $lbl): ?>
                                <option value="<?= $k ?>"><?= e($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_date">Expense Date <span class="req">*</span></label>
                        <input type="date" name="expense_date" id="exp_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="exp_title">Voucher Title / Purpose <span class="req">*</span></label>
                    <input type="text" name="title" id="exp_title" class="form-control" placeholder="<?= e(__('placeholder.e_g_bulk_diesel_for_bus_mh_12', 'e.g. Bulk Diesel for Bus #MH-12-AB-1234')) ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="exp_amount" class="form-control" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_vendor">Vendor / Payee Name</label>
                        <input type="text" name="vendor_name" id="exp_vendor" class="form-control" placeholder="<?= e(__('placeholder.e_g_indian_oil_corporation', 'e.g. Indian Oil Corporation')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_account">Disburse From Account</label>
                        <select name="account_id" id="exp_account" class="form-select">
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_currency((float)$acc['current_balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_status">Payment Status</label>
                        <select name="status" id="exp_status" class="form-select">
                            <option value="paid">Paid Immediately (Debit Ledger)</option>
                            <option value="pending">Pending Approval Voucher</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="exp_notes">Internal Remarks</label>
                    <textarea name="notes" id="exp_notes" class="form-control" rows="2" placeholder="<?= e(__('placeholder.odometer_reading_fuel_quantity', 'Odometer reading, fuel quantity, or approval notes...')) ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeExpenseModal()"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Expense Voucher</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExpenseModal() {
        document.getElementById('expenseModalBackdrop').style.display = 'flex';
    }
    function closeExpenseModal() {
        document.getElementById('expenseModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
    function toggleActions(btn, e) {
        if (window.OfficeActionMenu && typeof window.OfficeActionMenu.toggle === 'function') {
            window.OfficeActionMenu.toggle(btn, e);
            return;
        }
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons')) {
            document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });
</script>


