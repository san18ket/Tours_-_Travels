<?php
/**
 * MANASWI TOURS & TRAVELS — PROFIT & LOSS STATEMENT (P&L)
 * Simple, accurate, and easy to understand for travel business owners
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Statement</span></button>'
    . '<a href="' . url('/office/finance/reports/profit-loss?' . http_build_query(array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']))) . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="profitLossView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Profit & Loss Statement (P&L)',
            'description' => 'Comprehensive financial performance statement showing earned revenue, realized customer cashflow, operating costs, and net margin.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Financial Statement',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Profit & Loss', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Top KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <!-- 1. Invoiced Revenue -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Invoiced (Billed)</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($invoicedRevenue) ?></div>
            <div class="booking-kpi-sub">
                <span>Total amount billed to guests</span>
            </div>
        </div>

        <!-- 2. Realized Cash Collected -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Realized Money Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($realizedRevenue) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Actual cash/bank collections</span>
            </div>
        </div>

        <!-- 3. Total Outflow -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Costs &amp; Outflow</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($totalCosts) ?></div>
            <div class="booking-kpi-sub">
                <span>Expenses, salaries &amp; refunds</span>
            </div>
        </div>

        <!-- 4. Net Cash Surplus -->
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Net Cash Surplus</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $netCashSurplus >= 0 ? '#218765' : '#D95757' ?>;"><?= format_inr($netCashSurplus) ?></div>
            <div class="booking-kpi-sub">
                <span>Cash margin: <?= round($cashMargin, 1) ?>%</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. Date Range Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/reports/profit-loss') ?>" class="filter-bar-compact" style="margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
            <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary);"></i>
            <span>Statement Period:</span>
        </div>

        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>
        <span style="color: var(--text-muted); font-size: 0.85rem;">to</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn"><i class="fa-solid fa-filter"></i> <span>Generate Statement</span></button>
            <a href="<?= url('/office/finance/reports/profit-loss') ?>" class="fbc-btn"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Clear Explanation for Business Owners -->
    <div style="background: rgba(23, 107, 91, 0.05); border: 1px solid rgba(23, 107, 91, 0.15); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <i class="fa-solid fa-circle-question" style="color: var(--brand-primary); font-size: 1.25rem; margin-top: 2px;"></i>
            <div>
                <strong style="color: var(--vg-navy-900); font-size: 0.92rem; display: block; margin-bottom: 0.25rem;">Understanding Your Profit Numbers:</strong>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                    &bull; <strong>Realized Cash Surplus (<?= format_inr($netCashSurplus) ?>):</strong> The actual net money left in your hands and bank accounts after paying fuel, vehicle maintenance, staff salaries, and refunds.<br>
                    &bull; <strong>Accounting Profit (<?= format_inr($netAccrualProfit) ?>):</strong> Includes pending guest balances that haven't been collected yet.
                </p>
            </div>
        </div>
    </div>

    <!-- 5. Formatted P&L Data Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-scale-balanced" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Profit &amp; Loss Breakdown (<?= format_date($startDate) ?> &ndash; <?= format_date($endDate) ?>)
            </h3>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="min-width: 250px;">Account Head / Operational Activity</th>
                        <th style="min-width: 300px;">Business Explanation</th>
                        <th style="text-align: right; width: 180px;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- REVENUE SECTION -->
                    <tr style="background: rgba(33, 135, 101, 0.05);">
                        <td colspan="3"><strong style="color: #218765; text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.5px;"><i class="fa-solid fa-arrow-down-long"></i> 1. Operating Revenue &amp; Inflows</strong></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;"><strong>Total Invoiced Revenue (Accrual)</strong></td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Total trip package bills issued to customers during this period</td>
                        <td style="text-align: right; font-weight: 600; color: var(--vg-navy-900);"><?= format_inr($invoicedRevenue) ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;"><strong>Realized Customer Collections (Cash)</strong></td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Actual money received into company cash counter and bank vaults</td>
                        <td style="text-align: right; font-weight: 700; color: #218765;">+ <?= format_inr($realizedRevenue) ?></td>
                    </tr>

                    <!-- EXPENSES SECTION -->
                    <tr style="background: rgba(217, 87, 87, 0.05);">
                        <td colspan="3"><strong style="color: #D95757; text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.5px;"><i class="fa-solid fa-arrow-up-long"></i> 2. Operating Expenditures &amp; Outflows</strong></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Business Expenses (Fleet, Fuel, Office)</td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Vehicle maintenance, diesel refills, office rent, internet, and marketing</td>
                        <td style="text-align: right; color: #D95757;">- <?= format_inr($operatingExpenses) ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Staff Salaries Disbursed</td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Net monthly salaries paid to drivers, tour guides, and office staff</td>
                        <td style="text-align: right; color: #D95757;">- <?= format_inr($salaryExpenses) ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Customer Booking Refunds</td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Money returned to customers for trip cancellations</td>
                        <td style="text-align: right; color: #D95757;">- <?= format_inr($refundsPaid) ?></td>
                    </tr>
                    <tr style="border-top: 1.5px solid rgba(22, 68, 59, 0.15);">
                        <td style="padding-left: 2rem;"><strong>Total Operating Outflow</strong></td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Sum of business expenses, employee salaries, and refunds</td>
                        <td style="text-align: right; font-weight: 700; color: #D95757;">- <?= format_inr($totalCosts) ?></td>
                    </tr>

                    <!-- BOTTOM LINE -->
                    <tr style="background: var(--page-background-secondary); border-top: 2px solid var(--brand-primary);">
                        <td><strong style="font-size: 1rem; color: var(--vg-navy-900);">Net Realized Cash Surplus</strong></td>
                        <td style="font-size: 0.86rem; color: var(--vg-navy-900);">Cash in Hand generated after settling all expenses (Cash margin: <?= round($cashMargin, 1) ?>%)</td>
                        <td style="text-align: right;"><strong style="font-size: 1.15rem; color: <?= $netCashSurplus >= 0 ? '#218765' : '#D95757' ?>;"><?= format_inr($netCashSurplus) ?></strong></td>
                    </tr>
                    <tr style="background: #ffffff;">
                        <td><strong style="font-size: 0.95rem; color: var(--vg-navy-900);">Net Accounting Profit (Accrual)</strong></td>
                        <td style="font-size: 0.84rem; color: var(--text-muted);">Invoiced revenue minus total costs (Accrual margin: <?= round($accrualMargin, 1) ?>%)</td>
                        <td style="text-align: right;"><strong style="font-size: 1.05rem; color: <?= $netAccrualProfit >= 0 ? 'var(--brand-primary)' : '#D95757' ?>;"><?= format_inr($netAccrualProfit) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
