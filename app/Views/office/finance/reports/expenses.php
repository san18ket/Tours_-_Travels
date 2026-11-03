<?php
/**
 * MANASWI TOURS & TRAVELS — BUSINESS EXPENSE REPORT
 * Exact Reference UI System with Print & Export Support
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Report</span></button>'
    . '<a href="' . url('/office/finance/reports/expenses?' . http_build_query(array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'category' => $category, 'export' => 'csv']))) . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="expenseReportView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Business Expense Report',
            'description' => 'Comprehensive statement of company expenditures, fleet operational costs, vehicle fuel, office overheads, and vendor payments.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Expense Audit',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Expense Report', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Top KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Outflow</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($totalExpenses) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($rows) ?> recorded expense vouchers</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Report Period</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.15rem; color: var(--vg-navy-900);">
                <?= date('d M', strtotime($startDate)) ?> &ndash; <?= date('d M Y', strtotime($endDate)) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Active date filter interval</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Filter Head</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.15rem; color: #C78A35;">
                <?= !empty($category) ? e($category) : 'All Categories' ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= count($categoryBreakdown) ?> categories with spend</span>
            </div>
        </div>
    </div>

    <!-- 3. Date & Category Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/reports/expenses') ?>" class="filter-bar-compact" style="margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
            <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary);"></i>
            <span>Date Range:</span>
        </div>

        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>
        <span style="color: var(--text-muted); font-size: 0.85rem;">to</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>

        <select name="category" class="form-select" style="max-width: 200px; height: 38px; font-size: 0.85rem;">
            <option value="">All Categories</option>
            <?php foreach ($allCategories as $cat): ?>
                <option value="<?= e($cat['name']) ?>" <?= strtolower($category) === strtolower($cat['name']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn"><i class="fa-solid fa-filter"></i> <span>Filter</span></button>
            <a href="<?= url('/office/finance/reports/expenses') ?>" class="fbc-btn"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Category Breakdown Summary Bar -->
    <?php if (!empty($categoryBreakdown)): ?>
    <div style="background: #ffffff; border-radius: 14px; padding: 1.25rem; border: 1px solid rgba(22, 68, 59, 0.08); margin-bottom: 1.5rem;">
        <h4 style="font-size: 0.92rem; font-weight: 700; color: var(--vg-navy-900); margin: 0 0 0.85rem 0;">
            <i class="fa-solid fa-chart-column" style="color: var(--brand-primary); margin-right: 0.35rem;"></i>
            Category Spend Distribution
        </h4>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
            <?php foreach ($categoryBreakdown as $cName => $cAmt): 
                $pct = $totalExpenses > 0 ? round(($cAmt / $totalExpenses) * 100, 1) : 0;
            ?>
            <div style="flex: 1; min-width: 180px; background: var(--page-background); padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid rgba(22, 68, 59, 0.06);">
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;">
                    <span><?= e($cName) ?></span>
                    <strong><?= $pct ?>%</strong>
                </div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #D95757;"><?= format_inr($cAmt) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 5. Modern Table of Expenses -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-table-list" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Recorded Expense Vouchers
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Vouchers:</span>
                <span class="total-records-badge"><?= number_format(count($rows)) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 120px;">Expense Date</th>
                        <th style="min-width: 120px;">Voucher #</th>
                        <th style="min-width: 200px;">Expense Title</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="min-width: 150px;">Vendor / Payee</th>
                        <th style="min-width: 130px;">Method</th>
                        <th style="min-width: 140px;">Paid From Account</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 2.5rem;">
                            No business expenses recorded in this date range.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $exp): ?>
                        <tr>
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $i + 1 ?></div>
                            </td>
                            <td style="font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_date($exp['expense_date']) ?>
                            </td>
                            <td>
                                <strong style="font-family: monospace; font-size: 0.84rem; color: var(--brand-primary);"><?= e($exp['expense_code'] ?: ('EXP-' . $exp['id'])) ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($exp['title']) ?></strong>
                                <?php if (!empty($exp['description'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><?= e($exp['description']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.74rem; background: rgba(22, 68, 59, 0.08); color: var(--vg-navy-900); padding: 3px 8px; border-radius: 6px;">
                                    <?= e($exp['category']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= e($exp['vendor_name'] ?? 'Direct Vendor') ?>
                            </td>
                            <td style="font-size: 0.82rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                                <?= e($exp['payment_method']) ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= e($exp['account_name'] ?? 'General Treasury') ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: #D95757;">
                                    - <?= format_inr($exp['amount']) ?>
                                </strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
