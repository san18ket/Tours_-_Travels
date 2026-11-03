<?php
/**
 * MANASWI TOURS & TRAVELS — FINANCE OVERVIEW DASHBOARD
 * Consistent with Customer, Employee, and Booking Reference UI Patterns
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/invoices/create') . '" class="btn btn-outline"><i class="fa-solid fa-file-invoice"></i> <span>Create Invoice</span></a>'
    . '<button type="button" class="btn btn-outline" onclick="openPaymentModal()"><i class="fa-solid fa-money-bill-wave"></i> <span>Record Payment</span></button>'
    . '<button type="button" class="btn btn-primary" onclick="openExpenseModal()"><i class="fa-solid fa-plus"></i> <span>Add Expense</span></button>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="financeModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Finance Overview & Treasury',
            'description' => 'Real-time liquidity, cashflow analytics, customer payments, business expenses, and payroll overview for Manaswi Travels.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Live Financials',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance Overview', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (The 5 Core Indicators) -->
    <div class="booking-kpi-grid" id="financeKpiGrid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
        <!-- Card 1: Total Money Received -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments') ?>'" title="Click to view all customer payment history">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Money Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalReceived) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Actual customer collections</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Total Business Expenses -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/expenses') ?>'" title="Click to view all business expenses">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Business Expenses</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($totalExpenses) ?></div>
            <div class="booking-kpi-sub">
                <span>Fleet, fuel &amp; staff salaries</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Available Cash/Bank Balance -->
        <div class="booking-kpi-card is-selected" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts') ?>'" title="Click to view cash and bank accounts">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Available Cash &amp; Bank</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($availableBalance) ?></div>
            <div class="booking-kpi-sub">
                <span>Live balance across <?= count($accounts) ?> vaults</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Customer Pending Payments -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments') ?>'" title="Click to view pending customer receivables">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Customer Pending Dues</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $customerPendingPayments > 0 ? '#C78A35' : 'var(--brand-primary)' ?>;"><?= format_inr($customerPendingPayments) ?></div>
            <div class="booking-kpi-sub">
                <span>Unsettled invoice balances</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 5: Staff Salary Pending -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payslips') ?>'" title="Click to view pending staff salary disbursals">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Staff Salary Pending</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $staffSalaryPending > 0 ? '#4F46E5' : 'var(--brand-primary)' ?>;"><?= format_inr($staffSalaryPending) ?></div>
            <div class="booking-kpi-sub">
                <span>Pending payroll payout</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. Quick Action Buttons Row (Requirement 3) -->
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #ffffff; border-radius: 14px; border: 1px solid rgba(23, 107, 91, 0.1); box-shadow: 0 2px 8px rgba(0,0,0,0.03); align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-bolt" style="color: #C78A35;"></i>
            <strong style="font-size: 0.92rem; color: var(--vg-navy-900);">Finance Quick Actions:</strong>
        </div>
        <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline btn-sm" onclick="openExpenseModal()">
                <i class="fa-solid fa-receipt"></i> <span>Add Expense</span>
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="openPaymentModal()">
                <i class="fa-solid fa-hand-holding-dollar"></i> <span>Record Customer Payment</span>
            </button>
            <a href="<?= url('/office/invoices/create') ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-file-invoice-dollar"></i> <span>Create Invoice</span>
            </a>
            <a href="<?= url('/office/finance/payslips?status=pending') ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-users-line"></i> <span>Pay Staff Salary</span>
            </a>
            <button type="button" class="btn btn-outline btn-sm" onclick="openTransferModal()">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Internal Transfer</span>
            </button>
            <a href="<?= url('/office/finance/reports') ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-chart-pie"></i> <span>View Reports</span>
            </a>
        </div>
    </div>

    <!-- 4. Cash Flow Analytics Chart & Active Accounts Preview -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="finance-analytics-grid">
        <!-- Monthly Inflow vs Outflow Chart -->
        <div class="vg-card" style="padding: 1.25rem;">
            <div class="card-header" style="border: none; padding: 0 0 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title" style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">
                        <i class="fa-solid fa-chart-line" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                        Monthly Income vs Business Expenses (<?= $currentYear ?>)
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">Actual money received vs operational expenditures &amp; salaries (Internal transfers excluded)</p>
                </div>
                <div style="display: flex; gap: 0.75rem; font-size: 0.82rem; font-weight: 600;">
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: #218765;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #218765;"></span> Received
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: #D95757;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: #D95757;"></span> Expenses
                    </span>
                </div>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>

        <!-- Cash & Bank Accounts Summary -->
        <div class="vg-card" style="padding: 1.25rem;">
            <div class="card-header" style="border: none; padding: 0 0 0.85rem 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title" style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">
                    <i class="fa-solid fa-vault" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                    Accounts Liquidity
                </h3>
                <a href="<?= url('/office/finance/accounts') ?>" style="font-size: 0.82rem; color: var(--brand-primary); font-weight: 600; text-decoration: none;">Manage &rarr;</a>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <?php foreach (array_slice($accounts, 0, 4) as $acc): 
                    $iconClass = $acc['account_type'] === 'cash' ? 'fa-wallet' : ($acc['account_type'] === 'upi' ? 'fa-qrcode' : 'fa-building-columns');
                ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.85rem; border-radius: 10px; background: var(--page-background); border: 1px solid rgba(22, 68, 59, 0.08);">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(23, 107, 91, 0.12); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fa-solid <?= $iconClass ?>"></i>
                        </div>
                        <div>
                            <strong style="font-size: 0.86rem; color: var(--vg-navy-900); display: block;"><?= e($acc['account_name']) ?></strong>
                            <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;"><?= e($acc['account_type']) ?></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 0.92rem; color: var(--vg-navy-900);"><?= format_inr($acc['current_balance']) ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid rgba(22, 68, 59, 0.08); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">Total Liquid Balance</span>
                <strong style="font-size: 1.05rem; color: var(--brand-primary); font-weight: 800;"><?= format_inr($availableBalance) ?></strong>
            </div>
        </div>
    </div>

    <!-- 5. Recent Financial Activity (4 Grids: Payments, Expenses, Upcoming Salaries, Recent Refunds) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="finance-activity-grid">
        <!-- 5A. Recent Customer Payments -->
        <section class="recent-bookings-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa-solid fa-money-check-dollar" style="color: #218765; margin-right: 0.4rem;"></i>
                    Recent Customer Payments
                </h3>
                <a href="<?= url('/office/finance/payments') ?>" class="btn btn-outline btn-sm">View All &rarr;</a>
            </div>
            <div class="modern-table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer &amp; Booking</th>
                            <th>Method</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">No customer payments recorded yet.</td></tr>
                        <?php else: foreach ($recentPayments as $p): 
                            $pDate = $p['paid_at'] ?? $p['payment_date'] ?? $p['created_at'];
                        ?>
                        <tr>
                            <td style="font-size: 0.82rem; color: var(--text-muted);"><?= !empty($pDate) ? format_date($pDate) : '—' ?></td>
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--vg-navy-900); display: block;"><?= e($p['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($p['booking_code'])): ?>
                                    <span style="font-size: 0.75rem; color: var(--brand-primary); font-weight: 600;"><?= e($p['booking_code']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.75rem; text-transform: uppercase; background: rgba(33, 135, 101, 0.1); color: #218765; padding: 3px 7px; border-radius: 6px;">
                                    <?= e($p['payment_method']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: #218765; font-size: 0.92rem;">+ <?= format_inr($p['amount']) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 5B. Recent Business Expenses -->
        <section class="recent-bookings-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa-solid fa-receipt" style="color: #D95757; margin-right: 0.4rem;"></i>
                    Recent Business Expenses
                </h3>
                <a href="<?= url('/office/finance/expenses') ?>" class="btn btn-outline btn-sm">View All &rarr;</a>
            </div>
            <div class="modern-table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Expense Title</th>
                            <th>Category</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentExpenses)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">No business expenses recorded yet.</td></tr>
                        <?php else: foreach ($recentExpenses as $exp): ?>
                        <tr>
                            <td style="font-size: 0.82rem; color: var(--text-muted);"><?= format_date($exp['expense_date']) ?></td>
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--vg-navy-900); display: block;"><?= e($exp['title']) ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= e($exp['vendor_name'] ?? 'Direct Vendor') ?></span>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.75rem; background: rgba(22, 68, 59, 0.08); color: var(--vg-navy-900); padding: 3px 7px; border-radius: 6px;">
                                    <?= e($exp['category']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: #D95757; font-size: 0.92rem;">- <?= format_inr($exp['amount']) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem;" class="finance-activity-grid">
        <!-- 5C. Upcoming / Pending Salary Payments -->
        <section class="recent-bookings-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa-solid fa-users" style="color: #4F46E5; margin-right: 0.4rem;"></i>
                    Pending Staff Salaries
                </h3>
                <a href="<?= url('/office/finance/payslips?status=pending') ?>" class="btn btn-outline btn-sm">All Payslips &rarr;</a>
            </div>
            <div class="modern-table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Month</th>
                            <th style="text-align: right;">Net Salary</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcomingSalaries)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">All employee salaries are up to date.</td></tr>
                        <?php else: foreach ($upcomingSalaries as $sal): ?>
                        <tr>
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--vg-navy-900); display: block;"><?= e($sal['employee_name']) ?></strong>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= e($sal['department_name'] ?? 'Staff') ?></span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--vg-navy-900); font-weight: 600;">
                                <?= date('M Y', mktime(0, 0, 0, (int)($sal['month'] ?? $sal['payroll_month'] ?? 1), 10, (int)($sal['year'] ?? $sal['payroll_year'] ?? date('Y')))) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: #4F46E5; font-size: 0.92rem;"><?= format_inr($sal['net_salary']) ?></strong>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/office/finance/payslips/' . $sal['id']) ?>" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 5D. Recent Customer Refunds -->
        <section class="recent-bookings-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa-solid fa-rotate-left" style="color: #C78A35; margin-right: 0.4rem;"></i>
                    Recent Customer Refunds
                </h3>
                <a href="<?= url('/office/finance/refunds') ?>" class="btn btn-outline btn-sm">Manage &rarr;</a>
            </div>
            <div class="modern-table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Refund #</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRefunds)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">No refunds recorded.</td></tr>
                        <?php else: foreach ($recentRefunds as $ref): 
                            $isProcessed = $ref['status'] === 'processed';
                        ?>
                        <tr>
                            <td>
                                <strong style="font-size: 0.84rem; color: var(--brand-primary); display: block;"><?= e($ref['refund_number']) ?></strong>
                                <span style="font-size: 0.72rem; color: var(--text-muted);"><?= format_date($ref['created_at']) ?></span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= e($ref['customer_name'] ?? 'Direct Customer') ?>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.72rem; background: <?= $isProcessed ? 'rgba(33, 135, 101, 0.12)' : 'rgba(199, 138, 53, 0.12)' ?>; color: <?= $isProcessed ? '#218765' : '#C78A35' ?>; padding: 2px 6px; border-radius: 4px;">
                                    <?= ucfirst($ref['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: #D95757; font-size: 0.92rem;"><?= format_inr($ref['amount']) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Add Expense (Requirement 5)                                        -->
<!-- ========================================================================= -->
<div id="expenseModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'expenseModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-receipt" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Record Business Expense
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeExpenseModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/expenses') ?>" method="POST" id="addExpenseForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/dashboard') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="exp_title">Expense Title <span class="req">*</span></label>
                    <input type="text" name="title" id="exp_title" class="form-control" placeholder="e.g. Innova Diesel Refill / Office Internet" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_category">Expense Category <span class="req">*</span></label>
                        <select name="category" id="exp_category" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="exp_amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="exp_date">Expense Date <span class="req">*</span></label>
                        <input type="date" name="expense_date" id="exp_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="exp_method">Payment Method <span class="req">*</span></label>
                        <select name="payment_method" id="exp_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI (GPay/PhonePe)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="card">Company Debit/Credit Card</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="exp_account_id">Paid From Account <span class="req">*</span></label>
                    <select name="account_id" id="exp_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (Available: <?= format_inr($acc['current_balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="exp_description">Description / Notes</label>
                    <textarea name="description" id="exp_description" class="form-control" rows="2" placeholder="Vehicle number, trip reference, bill details..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeExpenseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Expense</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Record Customer Payment (Requirement 4)                            -->
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
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/dashboard') ?>">
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
                        <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control" placeholder="0.00" required>
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
                    <input type="text" name="payment_reference" id="pay_reference" class="form-control" placeholder="UTR number, UPI ref, or cheque #">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="pay_notes">Notes / Receipt Remarks</label>
                    <input type="text" name="notes" id="pay_notes" class="form-control" placeholder="Payment received confirmation...">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Payment</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Internal Fund Transfer (Requirement 7)                             -->
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
            <div class="modal-body-custom">
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: rgba(23, 107, 91, 0.06); border-radius: 8px;">
                    <i class="fa-solid fa-circle-info" style="color: var(--brand-primary); margin-right: 0.35rem;"></i>
                    <strong>Important Rule:</strong> Internal transfers rebalance funds between company accounts. They are <strong>never</strong> counted as business income or expenses.
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="from_account_id">From Account (Source) <span class="req">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            <option value="">— Select source —</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_inr($acc['current_balance']) ?>)</option>
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
                    <input type="text" name="notes" id="trf_notes" class="form-control" placeholder="e.g. Bank cash deposit, petty cash replenishment...">
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
                            label: 'Customer Collections',
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
                            label: 'Expenses & Salaries',
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

    function openExpenseModal() {
        document.getElementById('expenseModalBackdrop').style.display = 'flex';
    }
    function closeExpenseModal() {
        document.getElementById('expenseModalBackdrop').style.display = 'none';
    }
    function openPaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'flex';
    }
    function closePaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'none';
    }
    function openTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'flex';
    }
    function closeTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'none';
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

<style>
/* Modals styling matching reference standard */
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
    .finance-analytics-grid, .finance-activity-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
