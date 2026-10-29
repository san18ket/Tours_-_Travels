<?php
/**
 * MANASWI TOURS & TRAVELS — GENERAL LEDGER & TRANSACTIONS
 * Exact Dual-Architecture (Desktop PMS Modern Table + Mobile App Cards)
 */
$actionsHtml = '<button type="button" class="btn btn-primary" onclick="openTransactionModal()"><i class="fa-solid fa-plus"></i> <span>Record Transaction</span></button>';
?>

<div class="bookings-page-view finance-page-view" id="financeTransactionsPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'General Ledger & Transactions',
            'description' => 'Real-time double-entry transaction journal, audit references, running balances, and bank reconciliation entries.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Double-Entry Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'General Ledger', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="transactionsKpiGrid">
        <!-- Card 1: Total Volume -->
        <div class="booking-kpi-card <?= empty($selectedType) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Ledger Entries</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll) ?></div>
            <div class="booking-kpi-sub">
                <span>All recorded audit entries</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Total Credits (Inflow) -->
        <div class="booking-kpi-card <?= $selectedType === 'credit' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions?type=credit') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Credits (Inflow)</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($totalCredits) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Revenue &amp; collections</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Total Debits (Outflow) -->
        <div class="booking-kpi-card <?= $selectedType === 'debit' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions?type=debit') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Debits (Outflow)</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-danger);"><?= format_currency($totalDebits) ?></div>
            <div class="booking-kpi-sub">
                <span>Operating disbursements</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Net Balance -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Net Surplus / Cashflow</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $netBalance >= 0 ? 'var(--brand-primary)' : 'var(--status-danger)' ?>;">
                <?= format_currency($netBalance) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Inflow minus outflow surplus</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/transactions') ?>" class="filter-bar-compact" id="txFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by reference, notes, category, account..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/transactions') ?>'" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <select name="type" id="typeInput">
            <option value="" <?= empty($selectedType) ? 'selected' : '' ?>>All Transaction Types</option>
            <option value="credit" <?= $selectedType === 'credit' ? 'selected' : '' ?>>Credits (Inflow)</option>
            <option value="debit" <?= $selectedType === 'debit' ? 'selected' : '' ?>>Debits (Outflow)</option>
        </select>

        <select name="account_id" id="accountInput">
            <option value="">All Accounts</option>
            <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= ($selectedAccount ?? 0) == $acc['id'] ? 'selected' : '' ?>>
                    <?= e($acc['account_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/transactions') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">General Ledger</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openTransactionModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Record Tx</span>
            </button>
        </div>

        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/transactions') ?>" class="mv-search-form">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" placeholder="Search reference, notes..." value="<?= e($search ?? '') ?>" autocomplete="off">
                </div>
            </form>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/finance/transactions') ?>" class="mv-pill <?= empty($selectedType) ? 'active' : '' ?>">
                <span>All Entries</span>
                <span class="mv-pill-cnt"><?= number_format($totalAll) ?></span>
            </a>
            <a href="<?= url('/office/finance/transactions?type=credit') ?>" class="mv-pill <?= $selectedType === 'credit' ? 'active' : '' ?>">
                <span>Credits (+)</span>
            </a>
            <a href="<?= url('/office/finance/transactions?type=debit') ?>" class="mv-pill <?= $selectedType === 'debit' ? 'active' : '' ?>">
                <span>Debits (-)</span>
            </a>
        </div>
    </div>

    <!-- 5. Transaction Ledger Section: Desktop Table + Mobile Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Journal Manifest</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 145px;">Timestamp</th>
                        <th style="min-width: 180px;">Account</th>
                        <th style="width: 100px;" class="text-center">Type</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="min-width: 220px;">Reference / Notes</th>
                        <th style="width: 120px;">Method</th>
                        <th style="text-align: right; width: 140px;">Amount</th>
                        <th style="text-align: right; width: 140px;">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No transactions found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search filters or record a new entry.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx):
                            $isCredit = $tx['transaction_type'] === 'credit';
                        ?>
                        <tr>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= format_datetime($tx['transacted_at']) ?>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem;"><?= e($tx['account_name']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;"><?= e($tx['account_type']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="cust-toggle-pill <?= $isCredit ? 'completed' : 'cancelled' ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $isCredit ? 'completed' : 'cancelled' ?>"></span>
                                    <span><?= strtoupper($tx['transaction_type']) ?></span>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($tx['category']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($tx['transaction_ref'])): ?>
                                    <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 6px; border-radius: 4px;"><?= e($tx['transaction_ref']) ?></code>
                                <?php endif; ?>
                                <div style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 2px;"><?= e($tx['notes'] ?? '—') ?></div>
                            </td>
                            <td style="font-size: 0.82rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                                <?= e(str_replace('_', ' ', $tx['payment_method'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.98rem; color: <?= $isCredit ? 'var(--status-success)' : 'var(--status-danger)' ?>;">
                                    <?= $isCredit ? '+' : '-' ?> <?= format_currency((float)$tx['amount']) ?>
                                </strong>
                            </td>
                            <td style="text-align: right; font-size: 0.9rem; font-weight: 700; color: var(--vg-navy-900);">
                                <?= format_currency((float)$tx['balance_after']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile App Cards -->
        <div class="cards-grid">
            <?php foreach ($transactions as $tx):
                $isCredit = $tx['transaction_type'] === 'credit';
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: <?= $isCredit ? 'rgba(33, 135, 101, 0.12)' : 'rgba(217, 87, 87, 0.12)' ?>; color: <?= $isCredit ? '#218765' : '#D95757' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid <?= $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right' ?>"></i>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($tx['category']) ?></strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($tx['account_name']) ?> • <?= format_date($tx['transacted_at']) ?></span>
                            <?php if (!empty($tx['transaction_ref'])): ?>
                                <div style="font-size: 0.75rem; color: var(--brand-primary); font-family: monospace;"><?= e($tx['transaction_ref']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: <?= $isCredit ? 'var(--status-success)' : 'var(--status-danger)' ?>; display: block;">
                            <?= $isCredit ? '+' : '-' ?> <?= format_currency((float)$tx['amount']) ?>
                        </strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">Bal: <?= format_currency((float)$tx['balance_after']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalRecords) ?> total records)
            </div>
            <div style="display: flex; gap: 0.35rem;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= url('/office/finance/transactions?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '&type=' . $selectedType . '&account_id=' . $selectedAccount . '&per_page=' . $perPage) ?>" class="btn btn-outline btn-sm">&larr; Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= url('/office/finance/transactions?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '&type=' . $selectedType . '&account_id=' . $selectedAccount . '&per_page=' . $perPage) ?>" class="btn btn-outline btn-sm">Next &rarr;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Record Transaction                                                 -->
<!-- ========================================================================= -->
<div id="txModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'txModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-receipt" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Record General Ledger Transaction
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeTransactionModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transactions') ?>" method="POST" id="recordTxForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/transactions') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Transaction Type <span class="req">*</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #218765; cursor: pointer; background: rgba(33, 135, 101, 0.06);">
                            <input type="radio" name="transaction_type" value="credit" checked>
                            <span style="font-weight: 700; color: #218765;"><i class="fa-solid fa-arrow-down"></i> Credit (Income)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #D95757; cursor: pointer; background: rgba(217, 87, 87, 0.06);">
                            <input type="radio" name="transaction_type" value="debit">
                            <span style="font-weight: 700; color: #D95757;"><i class="fa-solid fa-arrow-up"></i> Debit (Expense)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="tx_account_id">Target Account / Vault <span class="req">*</span></label>
                    <select name="account_id" id="tx_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                <?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type']) ?>) — Available: <?= format_currency((float)$acc['current_balance']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="tx_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="tx_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tx_category">Category <span class="req">*</span></label>
                        <input type="text" name="category" id="tx_category" class="form-control" list="categoryOptions" placeholder="Category name..." required>
                        <datalist id="categoryOptions">
                            <option value="Customer Payment">
                            <option value="Tour Revenue">
                            <option value="Fuel Expense">
                            <option value="Vehicle Maintenance">
                            <option value="Driver Daily Bata">
                            <option value="Office Overheads">
                            <option value="Owner Capital Infusion">
                            <option value="Bank Interest">
                        </datalist>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="tx_method">Payment Mode</label>
                        <select name="payment_method" id="tx_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="cash">Counter Cash</option>
                            <option value="card">Card Payment</option>
                            <option value="cheque">Bank Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tx_ref">Transaction Ref / Cheque #</label>
                        <input type="text" name="transaction_ref" id="tx_ref" class="form-control" placeholder="UTR / Ref Number">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="tx_notes">Internal Remarks / Description</label>
                    <textarea name="notes" id="tx_notes" class="form-control" rows="2" placeholder="Audit note regarding this financial entry..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeTransactionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Commit Entry</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'flex';
    }
    function closeTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
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

