<?php
/**
 * MANASWI TOURS & TRAVELS — FINANCIAL INTELLIGENCE & REPORTS HUB
 * Exact Reference UI System
 */
?>

<div class="bookings-page-view finance-page-view" id="financialReportsHub">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Financial Reports & Analytics',
            'description' => 'Comprehensive accounting statements, operational profit & loss, customer aging dues, income receipts, and expense audits.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Executive Reports',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/finance') . '" class="btn btn-outline"><i class="fa-solid fa-gauge-high"></i> <span>Finance Overview</span></a>',
        ]) ?>
    </div>

    <!-- 2. Current Month Snapshot KPI Grid -->
    <div class="booking-kpi-grid" id="reportsKpiGrid">
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/income') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">This Month Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($monthIncome) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Collections in <?= e($currentMonth) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/expenses') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">This Month Outflow</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($monthTotalOutflow) ?></div>
            <div class="booking-kpi-sub">
                <span>Expenses &amp; salaries (<?= e($currentMonth) ?>)</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card is-selected" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/profit-loss') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Month Net Cash Surplus</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $monthNetSurplus >= 0 ? 'var(--brand-primary)' : '#D95757' ?>;"><?= format_inr($monthNetSurplus) ?></div>
            <div class="booking-kpi-sub">
                <span>Income minus cash outflow</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/customer-dues') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Customer Dues</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($totalCustomerDues) ?></div>
            <div class="booking-kpi-sub">
                <span>Outstanding receivables</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. Report Catalog Grid (The 6 Main Reports) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <!-- Report 1: Income Report -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(33, 135, 101, 0.12); color: #218765; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <span class="badge" style="background: rgba(33, 135, 101, 0.1); color: #218765; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Revenue</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">1. Income Report</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Complete breakdown of money received from tour packages, vehicle bookings, payment modes (Cash, UPI, Bank), and deposit accounts.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/income') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">View Report</a>
                <a href="<?= url('/office/finance/reports/income?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Report 2: Expense Report -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(217, 87, 87, 0.12); color: #D95757; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <span class="badge" style="background: rgba(217, 87, 87, 0.1); color: #D95757; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Expenditures</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">2. Expense Report</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Audit log of operational expenditures including fleet fuel, vehicle maintenance, office bills, vendor payments, and category grouping.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/expenses') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">View Report</a>
                <a href="<?= url('/office/finance/reports/expenses?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Report 3: Profit & Loss Statement -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px; border: 1.5px solid rgba(23, 107, 91, 0.25);">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(23, 107, 91, 0.15); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <span class="badge" style="background: rgba(23, 107, 91, 0.12); color: var(--brand-primary); font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Executive P&amp;L</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">3. Profit &amp; Loss Statement</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Clear distinction between invoiced revenue, realized cash collections, operational costs, and actual business net profit margins.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/profit-loss') ?>" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center;">View Statement</a>
                <a href="<?= url('/office/finance/reports/profit-loss?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Report 4: Customer Dues & Aging -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(199, 138, 53, 0.12); color: #C78A35; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <span class="badge" style="background: rgba(199, 138, 53, 0.1); color: #C78A35; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Receivables</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">4. Customer Dues Report</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Aging analysis of outstanding balances (0-15, 16-30, 31-60, 60+ days overdue), contact info, and pending travel collections.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/customer-dues') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">View Report</a>
                <a href="<?= url('/office/finance/reports/customer-dues?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Report 5: Staff Salary Report -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(79, 70, 229, 0.12); color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4F46E5; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Payroll</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">5. Staff Salary Report</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Annual &amp; monthly payroll cost breakdown across departments, employee basic salary, allowances, advance deductions, and payout status.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/salary') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">View Report</a>
                <a href="<?= url('/office/finance/reports/salary?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>

        <!-- Report 6: Expense Category Report -->
        <div class="vg-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: 16px;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(22, 68, 59, 0.12); color: var(--vg-navy-900); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <span class="badge" style="background: rgba(22, 68, 59, 0.08); color: var(--vg-navy-900); font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Spend Share</span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.35rem 0;">6. Expense Category Report</h3>
                <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 1.25rem 0;">
                    Percentage distribution of operational costs by category (Fuel, Vehicle Maintenance, Rent, Marketing) to identify budget optimizations.
                </p>
            </div>
            <div style="display: flex; gap: 0.65rem;">
                <a href="<?= url('/office/finance/reports/expense-categories') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">View Report</a>
                <a href="<?= url('/office/finance/reports/expense-categories?export=csv') ?>" class="btn btn-outline btn-sm" title="Export CSV"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>
    </div>
</div>
