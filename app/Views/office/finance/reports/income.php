<?php
/**
 * MANASWI TOURS & TRAVELS — INCOME & REVENUE REPORT
 * Exact Reference UI System with Print & Export Support
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Report</span></button>'
    . '<a href="' . url('/office/finance/reports/income?' . http_build_query(array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'method' => $method, 'export' => 'csv']))) . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="incomeReportView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Income & Revenue Report',
            'description' => 'Detailed audit log of customer collections, tour settlements, payment methods, and cash inflows for the selected period.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Revenue Statement',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Income Report', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Top KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Realized Revenue</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalIncome) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($rows) ?> collected transactions</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">UPI Collections</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($upiTotal) ?></div>
            <div class="booking-kpi-sub">
                <span>GPay, PhonePe, Paytm QR</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cash Receipts</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($cashTotal) ?></div>
            <div class="booking-kpi-sub">
                <span>Counter cash collection</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Bank &amp; Card Transfer</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= format_inr($bankTotal) ?></div>
            <div class="booking-kpi-sub">
                <span>NEFT, IMPS &amp; POS swipe</span>
            </div>
        </div>
    </div>

    <!-- 3. Date Filter Bar (Hidden on Print) -->
    <form method="GET" action="<?= url('/office/finance/reports/income') ?>" class="filter-bar-compact" style="margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
            <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary);"></i>
            <span>Date Range:</span>
        </div>

        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>
        <span style="color: var(--text-muted); font-size: 0.85rem;">to</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="form-control" style="max-width: 150px; height: 38px; font-size: 0.85rem;" required>

        <select name="method" class="form-select" style="max-width: 160px; height: 38px; font-size: 0.85rem;">
            <option value="" <?= empty($method) ? 'selected' : '' ?>>All Methods</option>
            <option value="cash" <?= $method === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="upi" <?= $method === 'upi' ? 'selected' : '' ?>>UPI</option>
            <option value="bank_transfer" <?= $method === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
            <option value="card" <?= $method === 'card' ? 'selected' : '' ?>>Card</option>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn"><i class="fa-solid fa-filter"></i> <span>Filter</span></button>
            <a href="<?= url('/office/finance/reports/income') ?>" class="fbc-btn"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Modern Table of Income Transactions -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-table-list" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Received Customer Payments (<?= format_date($startDate) ?> to <?= format_date($endDate) ?>)
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Receipts:</span>
                <span class="total-records-badge"><?= number_format(count($rows)) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 120px;">Payment Date</th>
                        <th style="min-width: 180px;">Customer Details</th>
                        <th style="min-width: 130px;">Booking Code</th>
                        <th style="min-width: 120px;">Invoice #</th>
                        <th style="min-width: 130px;">Payment Method</th>
                        <th style="min-width: 140px;">Deposit Account</th>
                        <th style="text-align: right; width: 140px;">Amount Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 2.5rem;">
                            No customer payments recorded in this date range.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $i + 1 ?></div>
                            </td>
                            <td style="font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_date($r['payment_date']) ?>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($r['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($r['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><?= e($r['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: var(--brand-primary); font-size: 0.85rem;"><?= e($r['booking_code'] ?? '—') ?></strong>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #4F46E5; font-weight: 600;"><?= e($r['invoice_number'] ?? '—') ?></span>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.74rem; text-transform: uppercase; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: rgba(33, 135, 101, 0.12); color: #218765;">
                                    <?= e($r['payment_method']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= e($r['account_name'] ?? 'Default Treasury') ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: #218765;">
                                    + <?= format_inr($r['amount']) ?>
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
