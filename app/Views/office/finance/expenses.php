<?php
/**
 * MANASWI TOURS & TRAVELS — OPERATING & FLEET EXPENSES
 * Matching Customers / Invoices Architecture & Aesthetics
 */
$actionsHtml = '<button type="button" class="btn btn-primary" onclick="openExpenseModal()"><i class="fa-solid fa-plus"></i> <span>Add Expense</span></button>';
?>

<div class="bookings-page-view finance-page-view" id="financeExpensesPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Operating & Fleet Expenses',
            'description' => 'Comprehensive expense auditing for bulk fleet fuel, vehicle maintenance, driver daily allowances, office rent, and utility disbursements.',
            'image'       => 'header-invoices.jpg',
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
            <input type="text" name="search" id="searchInput" placeholder="Search by voucher #, title, vendor..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/expenses') ?>'" title="Clear search">
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

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/expenses') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
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
                    <input type="text" name="search" placeholder="Search title, vendor..." value="<?= e($search ?? '') ?>" autocomplete="off">
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
                        <th style="width: 130px;">Voucher #</th>
                        <th style="width: 110px;">Date</th>
                        <th style="min-width: 160px;">Category</th>
                        <th style="min-width: 200px;">Title &amp; Vendor</th>
                        <th style="min-width: 160px;">Disbursed From</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
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
                            <td>
                                <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.88rem;"><?= e($e['expense_code']) ?></strong>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= format_date($e['expense_date']) ?>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900); display: block;"><?= e($catLabel) ?></span>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);"><?= e(str_replace('_', ' ', $e['payment_method'])) ?></span>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.9rem;"><?= e($e['title']) ?></strong>
                                <?php if (!empty($e['vendor_name'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Vendor: <?= e($e['vendor_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--vg-navy-900); font-weight: 600;"><?= e($e['account_name'] ?? 'Direct Vault') ?></span>
                            </td>
                            <td class="text-center">
                                <span class="cust-toggle-pill <?= $stClass ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $stClass ?>"></span>
                                    <span><?= strtoupper($st) ?></span>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1rem; color: var(--status-danger);">
                                    <?= format_currency((float)$e['amount']) ?>
                                </strong>
                            </td>
                            <td>
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <?php if ($st === 'pending'): ?>
                                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="paid">
                                                <input type="hidden" name="account_id" value="<?= $accounts[0]['id'] ?? 1 ?>">
                                                <button type="submit" class="action-btn-table" style="color: #218765; width: 100%; border: none; background: none; text-align: left; padding: 0.5rem 0.75rem;" title="Approve & Mark Paid">
                                                    <i class="fa-solid fa-circle-check"></i> <span>Pay Now</span>
                                                </button>
                                            </form>
                                            <form action="<?= url('/office/finance/expenses/' . $e['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="action-btn-table delete" style="color: #D95757; width: 100%; border: none; background: none; text-align: left; padding: 0.5rem 0.75rem;" title="Reject Voucher">
                                                    <i class="fa-solid fa-circle-xmark"></i> <span>Reject</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div style="font-size: 0.75rem; padding: 0.5rem 0.75rem; color: var(--text-muted);">Voucher <?= ucfirst($st) ?></div>
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
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalRecords) ?> total records)
            </div>
            <div style="display: flex; gap: 0.35rem;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= url('/office/finance/expenses?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '&status=' . $selectedStatus . '&category=' . $selectedCategory . '&per_page=' . $perPage) ?>" class="btn btn-outline btn-sm">&larr; Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= url('/office/finance/expenses?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '&status=' . $selectedStatus . '&category=' . $selectedCategory . '&per_page=' . $perPage) ?>" class="btn btn-outline btn-sm">Next &rarr;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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
                    <input type="text" name="title" id="exp_title" class="form-control" placeholder="e.g. Bulk Diesel for Bus #MH-12-AB-1234" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="exp_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_vendor">Vendor / Payee Name</label>
                        <input type="text" name="vendor_name" id="exp_vendor" class="form-control" placeholder="e.g. Indian Oil Corporation">
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
                    <textarea name="notes" id="exp_notes" class="form-control" rows="2" placeholder="Odometer reading, fuel quantity, or approval notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeExpenseModal()">Cancel</button>
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

<style>
.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 35, 30, 0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}
.modal-dialog-custom {
    background: #ffffff;
    border-radius: var(--vg-radius-lg, 18px);
    box-shadow: 0 20px 45px rgba(22, 68, 59, 0.22);
    border: 1px solid rgba(23, 107, 91, 0.15);
    width: 100%;
    max-width: 540px;
    overflow: hidden;
    animation: modalSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(18px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header-custom {
    padding: 1.15rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(22, 68, 59, 0.08);
}
.modal-close-custom {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    color: var(--text-muted);
    cursor: pointer;
}
.modal-body-custom {
    padding: 1.25rem 1.35rem;
}
.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: var(--page-background);
}
</style>

