<?php
/**
 * MANASWI TOURS & TRAVELS — EXPENSE CATEGORY SPEND REPORT
 * Exact Reference UI System with Print & Export Support
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Report</span></button>'
    . '<a href="' . url('/office/finance/reports/expense-categories?' . http_build_query(array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']))) . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="expenseCatReportView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Expense Category Spend Breakdown',
            'description' => 'Proportional spending analysis across business operational heads to highlight cost drivers and budget distribution.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Cost Distribution',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Category Report', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Top KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cumulative Spend</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($grandTotal) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Total categorized expenditures</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Categories</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= count($rows) ?></div>
            <div class="booking-kpi-sub">
                <span>Categories with recorded activity</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Report Interval</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.15rem; color: var(--vg-navy-900);">
                <?= date('d M', strtotime($startDate)) ?> &ndash; <?= date('d M Y', strtotime($endDate)) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Selected date filter range</span>
            </div>
        </div>
    </div>

    <!-- 3. Period Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/reports/expense-categories') ?>" class="filter-bar-compact" style="margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
            <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary);"></i>
            <span>Date Range:</span>
        </div>

        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>
        <span style="color: var(--text-muted); font-size: 0.85rem;">to</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn"><i class="fa-solid fa-filter"></i> <span>Filter</span></button>
            <a href="<?= url('/office/finance/reports/expense-categories') ?>" class="fbc-btn"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Visual Spend Share Distribution Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <?php foreach ($rows as $r): 
            $pct = (float)$r['percentage'];
        ?>
        <div class="vg-card" style="padding: 1rem 1.25rem; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <strong style="font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($r['category']) ?></strong>
                <span class="badge" style="background: rgba(23, 107, 91, 0.1); color: var(--brand-primary); font-size: 0.76rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;"><?= $pct ?>%</span>
            </div>
            <div style="font-size: 1.1rem; font-weight: 800; color: #D95757; margin-bottom: 0.5rem;">
                <?= format_inr($r['total_amount']) ?>
            </div>
            <div style="background: rgba(22, 68, 59, 0.08); height: 6px; border-radius: 3px; overflow: hidden;">
                <div style="background: var(--brand-primary); height: 100%; width: <?= $pct ?>%;"></div>
            </div>
            <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 0.4rem;">
                <?= number_format($r['voucher_count']) ?> voucher(s) recorded
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 5. Modern Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-chart-pie" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Category Cost Analysis Table
            </h3>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 220px;">Category Name</th>
                        <th class="text-center" style="width: 130px;">Voucher Count</th>
                        <th style="text-align: right; width: 160px;">Total Amount</th>
                        <th style="text-align: right; width: 140px;">Share of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 2.5rem;">
                            No business expenses recorded in this period.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $i + 1 ?></div>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.92rem;"><?= e($r['category']) ?></strong>
                            </td>
                            <td class="text-center" style="font-weight: 600; font-size: 0.88rem; color: var(--vg-navy-900);">
                                <?= number_format($r['voucher_count']) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: #D95757;">
                                    <?= format_inr($r['total_amount']) ?>
                                </strong>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--brand-primary); font-size: 0.92rem;">
                                <?= $r['percentage'] ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
