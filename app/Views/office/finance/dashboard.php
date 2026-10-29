<?php
/**
 * MANASWI TOURS & TRAVELS — FINANCE & TREASURY COMMAND DASHBOARD
 * Palette: Forest Teal (#176B5B) + Warm Ivory (#F7F8F4) + Champagne Sand (#D6B77A)
 * Aesthetic: Light Glassmorphism + Responsive Desktop PMS / Native Mobile Cards
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="openTransferModal()"><i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer Funds</span></button>'
    . '<button type="button" class="btn btn-primary" onclick="openTransactionModal()"><i class="fa-solid fa-plus"></i> <span>Record Transaction</span></button>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="financeModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Treasury & Financial Command',
            'description' => 'Real-time liquidity monitoring, immutable double-entry ledger, bank reconciliation, and automated operational cashflow.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Treasury Active',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="financeKpiGrid">
        <!-- KPI 1: Liquid Capital -->
        <div class="booking-kpi-card is-selected" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Liquid Capital (Cash &amp; Bank)</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_currency($totalLiquid) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($accounts) ?> active accounts &amp; vaults</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- KPI 2: Current Month Inflow -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions?type=credit') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Monthly Inflow (Credits)</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($currentMonthIncome) ?></div>
            <div class="booking-kpi-sub">
                <span>Collections &amp; revenues (<?= date('M Y') ?>)</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- KPI 3: Current Month Outflow -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Monthly Outflow (Debits)</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-danger);"><?= format_currency($currentMonthExpense) ?></div>
            <div class="booking-kpi-sub">
                <span>Fleet, salaries &amp; overheads</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- KPI 4: Outstanding Receivables -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/invoices?status=unpaid') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Outstanding Invoices</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $receivables > 0 ? '#C78A35' : 'var(--brand-primary)' ?>;"><?= format_currency($receivables) ?></div>
            <div class="booking-kpi-sub">
                <span>Pending guest settlements</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar, Quick Actions & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Finance &amp; Treasury</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openTransactionModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Record Tx</span>
            </button>
        </div>

        <!-- Mobile Quick Stats Cards Scroll -->
        <div class="mv-status-scroll" style="margin-bottom: 0.85rem;">
            <a href="<?= url('/office/finance') ?>" class="mv-pill active">
                <span>Liquid: <?= format_currency($totalLiquid) ?></span>
            </a>
            <a href="<?= url('/office/finance/accounts') ?>" class="mv-pill">
                <span><?= count($accounts) ?> Accounts</span>
            </a>
            <a href="<?= url('/office/finance/expenses') ?>" class="mv-pill">
                <span>Expenses: <?= format_currency($currentMonthExpense) ?></span>
            </a>
            <a href="<?= url('/office/finance/payroll') ?>" class="mv-pill">
                <span>Payroll</span>
            </a>
            <a href="<?= url('/office/invoices') ?>" class="mv-pill">
                <span>GST Invoices</span>
            </a>
        </div>
    </div>

    <!-- 4. Interactive Cash Flow Analytics Chart & Account Vaults Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="finance-analytics-grid">
        <!-- Left: Annual Inflow vs Outflow Chart -->
        <div class="vg-card" style="padding: 1.25rem;">
            <div class="card-header" style="border: none; padding: 0 0 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">
                        <i class="fa-solid fa-chart-line text-success" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                        Cash Flow Analysis (<?= $currentYear ?>)
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">Monthly collections versus operational expenditures</p>
                </div>
                <div style="display: flex; gap: 0.75rem; font-size: 0.82rem; font-weight: 600;">
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: #218765;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #218765;"></span> Inflow
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: #D95757;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #D95757;"></span> Outflow
                    </span>
                </div>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>

        <!-- Right: Active Accounts & Liquidity Vaults -->
        <div class="vg-card" style="padding: 1.25rem;">
            <div class="card-header" style="border: none; padding: 0 0 0.85rem 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title" style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">
                    <i class="fa-solid fa-vault" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                    Treasury Vaults
                </h3>
                <a href="<?= url('/office/finance/accounts') ?>" style="font-size: 0.82rem; color: var(--brand-primary); font-weight: 600; text-decoration: none;">View All &rarr;</a>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($accounts as $acc): 
                    $typeClass = $acc['account_type'] === 'cash' ? 'kpi-gold' : ($acc['account_type'] === 'gateway' ? 'kpi-teal' : 'kpi-green');
                    $iconClass = $acc['account_type'] === 'cash' ? 'fa-wallet' : ($acc['account_type'] === 'gateway' ? 'fa-qrcode' : 'fa-building-columns');
                ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-radius: 12px; background: var(--page-background); border: 1px solid rgba(22, 68, 59, 0.08);">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(23, 107, 91, 0.12); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                            <i class="fa-solid <?= $iconClass ?>"></i>
                        </div>
                        <div>
                            <strong style="font-size: 0.88rem; color: var(--vg-navy-900); display: block;"><?= e($acc['account_name']) ?></strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;"><?= e($acc['account_type']) ?><?= !empty($acc['account_number']) ? ' •• ' . substr($acc['account_number'], -4) : '' ?></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 0.95rem; color: var(--vg-navy-900);"><?= format_currency((float)$acc['current_balance']) ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(22, 68, 59, 0.08); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Total Liquid Reserve</span>
                <strong style="font-size: 1.1rem; color: var(--brand-primary); font-weight: 800;"><?= format_currency($totalLiquid) ?></strong>
            </div>
        </div>
    </div>

    <!-- 5. Recent Immutable Ledger Activity -->
    <section class="recent-bookings-section" id="financeRecentSection">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-list-check" style="color: var(--brand-primary); margin-right: 0.5rem;"></i>
                Recent Ledger Transactions
            </h3>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="<?= url('/office/finance/transactions') ?>" class="btn btn-outline btn-sm">Full Ledger &rarr;</a>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Timestamp</th>
                        <th style="min-width: 180px;">Account</th>
                        <th style="width: 100px;" class="text-center">Type</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="min-width: 200px;">Reference / Notes</th>
                        <th style="text-align: right; width: 140px;">Amount</th>
                        <th style="text-align: right; width: 140px;">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No transactions recorded yet</h4>
                                <p class="text-muted" style="margin:0;">Use the "Record Transaction" button to create an opening ledger entry.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx):
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
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($tx['category']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($tx['transaction_ref'])): ?>
                                    <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 6px; border-radius: 4px;"><?= e($tx['transaction_ref']) ?></code>
                                <?php endif; ?>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= e($tx['notes'] ?? '—') ?></div>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: <?= $isCredit ? 'var(--status-success)' : 'var(--status-danger)' ?>;">
                                    <?= $isCredit ? '+' : '-' ?> <?= format_currency((float)$tx['amount']) ?>
                                </strong>
                            </td>
                            <td style="text-align: right; font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_currency((float)$tx['balance_after']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens -->
        <div class="cards-grid">
            <?php foreach ($recentTransactions as $tx):
                $isCredit = $tx['transaction_type'] === 'credit';
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: <?= $isCredit ? 'rgba(33, 135, 101, 0.12)' : 'rgba(217, 87, 87, 0.12)' ?>; color: <?= $isCredit ? '#218765' : '#D95757' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid <?= $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right' ?>"></i>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($tx['category']) ?></strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($tx['account_name']) ?> • <?= format_date($tx['transacted_at']) ?></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1rem; color: <?= $isCredit ? 'var(--status-success)' : 'var(--status-danger)' ?>; display: block;">
                            <?= $isCredit ? '+' : '-' ?> <?= format_currency((float)$tx['amount']) ?>
                        </strong>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Bal: <?= format_currency((float)$tx['balance_after']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/dashboard') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Transaction Type <span class="req">*</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #218765; cursor: pointer; background: rgba(33, 135, 101, 0.06);">
                            <input type="radio" name="transaction_type" value="credit" checked>
                            <span style="font-weight: 700; color: #218765;"><i class="fa-solid fa-arrow-down"></i> Credit (Income / Inflow)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #D95757; cursor: pointer; background: rgba(217, 87, 87, 0.06);">
                            <input type="radio" name="transaction_type" value="debit">
                            <span style="font-weight: 700; color: #D95757;"><i class="fa-solid fa-arrow-up"></i> Debit (Expense / Outflow)</span>
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
                        <input type="text" name="category" id="tx_category" class="form-control" list="categoryOptions" placeholder="Select or type..." required>
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
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Commit Transaction</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Internal Fund Transfer                                             -->
<!-- ========================================================================= -->
<div id="transferModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'transferModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Internal Account Fund Transfer
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeTransferModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transfer') ?>" method="POST" id="fundTransferForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/dashboard') ?>">
            <div class="modal-body-custom">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="from_account_id">From Account (Source) <span class="req">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            <option value="">— Select source —</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_currency((float)$acc['current_balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="to_account_id">To Account (Destination) <span class="req">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select" required>
                            <option value="">— Select destination —</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="trf_amount">Transfer Amount (INR) <span class="req">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="trf_amount" class="form-control" placeholder="0.00" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="trf_notes">Transfer Remarks</label>
                    <input type="text" name="notes" id="trf_notes" class="form-control" placeholder="Reason for inter-account rebalancing...">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Execute Transfer</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize Chart.js Cashflow chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('cashFlowChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($monthLabels) ?>,
                    datasets: [
                        {
                            label: 'Monthly Inflow (Credits)',
                            data: <?= json_encode($incomeData) ?>,
                            borderColor: '#218765',
                            backgroundColor: 'rgba(33, 135, 101, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#218765',
                            pointRadius: 4,
                        },
                        {
                            label: 'Monthly Outflow (Debits)',
                            data: <?= json_encode($expenseData) ?>,
                            borderColor: '#D95757',
                            backgroundColor: 'rgba(217, 87, 87, 0.05)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#D95757',
                            pointRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹ ' + Number(context.raw).toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(22, 68, 59, 0.06)' },
                            ticks: {
                                callback: function(val) {
                                    return '₹ ' + (val >= 1000 ? (val/1000) + 'k' : val);
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });

    function openTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'flex';
    }

    function closeTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'none';
    }

    function openTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'flex';
    }

    function closeTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'none';
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

<style>
/* Scoped Modal System Matching Existing Theme */
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
    line-height: 1;
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

@media (max-width: 992px) {
    .finance-analytics-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

