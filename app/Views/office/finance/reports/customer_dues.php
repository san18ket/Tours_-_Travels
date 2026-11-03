<?php
/**
 * MANASWI TOURS & TRAVELS — CUSTOMER PENDING DUES & AGING REPORT
 * Exact Reference UI System with Aging Buckets, Print & Export Support
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Report</span></button>'
    . '<a href="' . url('/office/finance/reports/customer-dues?export=csv') . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="customerDuesReportView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Pending Dues & Aging Report',
            'description' => 'Accounts receivable aging analysis, categorized into 0-15, 16-30, 31-60, and 60+ days overdue buckets for aggressive recovery.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Aging Analysis',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Customer Dues', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Aging Bucket Summary Cards -->
    <div class="booking-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
        <!-- Total Outstanding -->
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Outstanding Dues</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($totalDues) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($rows) ?> unsettled customer invoices</span>
            </div>
        </div>

        <!-- 0-15 Days -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Current (0&ndash;15 Days)</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($bucketCurrent) ?></div>
            <div class="booking-kpi-sub">
                <span>Recent bookings &amp; normal credit</span>
            </div>
        </div>

        <!-- 16-30 Days -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Notice (16&ndash;30 Days)</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= format_inr($bucket16to30) ?></div>
            <div class="booking-kpi-sub">
                <span>Follow-up reminder stage</span>
            </div>
        </div>

        <!-- 31-60 Days -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Overdue (31&ndash;60 Days)</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($bucket31to60) ?></div>
            <div class="booking-kpi-sub">
                <span>Urgent settlement needed</span>
            </div>
        </div>

        <!-- 60+ Days -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Severely Overdue (60+ Days)</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(185, 28, 28, 0.15); color: #B91C1C;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #B91C1C;"><?= format_inr($bucketOver60) ?></div>
            <div class="booking-kpi-sub">
                <span>High default risk bucket</span>
            </div>
        </div>
    </div>

    <!-- 3. Overdue Invoices Modern Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-list-check" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Customer Aging Receivables Schedule
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Pending Invoices:</span>
                <span class="total-records-badge"><?= number_format(count($rows)) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Customer Name &amp; Contact</th>
                        <th style="min-width: 130px;">Booking Code</th>
                        <th style="min-width: 120px;">Invoice #</th>
                        <th style="min-width: 120px;">Due Date</th>
                        <th class="text-center" style="min-width: 130px;">Aging Bucket</th>
                        <th style="text-align: right; width: 120px;">Billed Amount</th>
                        <th style="text-align: right; width: 120px;">Received</th>
                        <th style="text-align: right; width: 140px;">Pending Balance</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 2.5rem;">
                            All customer invoices are cleared! No outstanding dues.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r):
                            $days = (int)$r['days_overdue'];
                            $bucketClass = $days <= 15 ? 'kpi-teal' : ($days <= 30 ? 'kpi-blue' : ($days <= 60 ? 'kpi-gold' : 'kpi-red'));
                            $bucketLabel = $days <= 15 ? '0-15 Days' : ($days <= 30 ? '16-30 Days' : ($days <= 60 ? '31-60 Days' : '60+ Days Overdue'));
                            $badgeStyle = $days <= 15 ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : ($days <= 30 ? 'background: rgba(79, 70, 229, 0.12); color: #4F46E5;' : ($days <= 60 ? 'background: rgba(199, 138, 53, 0.12); color: #C78A35;' : 'background: rgba(185, 28, 28, 0.15); color: #B91C1C;'));
                        ?>
                        <tr>
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $i + 1 ?></div>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($r['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($r['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?= e($r['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: var(--brand-primary); font-size: 0.85rem;"><?= e($r['booking_code'] ?? '—') ?></strong>
                            </td>
                            <td>
                                <a href="<?= url('/office/invoices/' . $r['id']) ?>" style="font-weight: 600; color: #4F46E5; text-decoration: none; font-size: 0.84rem;">
                                    <?= e($r['invoice_number']) ?>
                                </a>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= format_date($r['invoice_date']) ?></div>
                            </td>
                            <td>
                                <span style="font-size: 0.84rem; font-weight: 600; color: <?= $days > 0 ? '#D95757' : 'var(--vg-navy-900)' ?>;">
                                    <?= !empty($r['due_date']) ? format_date($r['due_date']) : 'On Arrival' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $badgeStyle ?>">
                                    <?= $bucketLabel ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_inr($r['total_amount']) ?>
                            </td>
                            <td style="text-align: right; font-size: 0.88rem; font-weight: 600; color: #218765;">
                                <?= format_inr($r['paid_amount']) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.98rem; color: #C78A35;">
                                    <?= format_inr($r['remaining_balance']) ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/office/finance/pending-payments?search=' . urlencode($r['invoice_number'])) ?>" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                    Collect
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
