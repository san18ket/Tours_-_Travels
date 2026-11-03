<?php
/**
 * MANASWI TOURS & TRAVELS — STAFF SALARY & PAYROLL REPORT
 * Exact Reference UI System with Print & Export Support
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print Report</span></button>'
    . '<a href="' . url('/office/finance/reports/salary?' . http_build_query(array_filter(['year' => $year, 'month' => $month, 'export' => 'csv']))) . '" class="btn btn-primary"><i class="fa-solid fa-download"></i> <span>Export CSV</span></a>'
    . '</div>';

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<div class="bookings-page-view finance-page-view" id="salaryReportView">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Staff Salary & Payroll Report',
            'description' => 'Annual and monthly salary cost audit, statutory allowances, advance deductions, and payment disbursals across departments.',
            'image'       => 'header-employees.jpg',
            'badge'       => 'Payroll Report',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Reports', 'url' => url('/office/finance/reports')],
                ['label' => 'Salary Report', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Top KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Net Payroll</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_inr($totalNetSalary) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($rows) ?> payslip records</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Disbursed Salary</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalPaid) ?></div>
            <div class="booking-kpi-sub">
                <span>Paid via cash / bank</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Payouts</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $totalPending > 0 ? '#D95757' : 'var(--brand-primary)' ?>;"><?= format_inr($totalPending) ?></div>
            <div class="booking-kpi-sub">
                <span>Awaiting disbursal</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Advance Deductions</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($totalDeductions) ?></div>
            <div class="booking-kpi-sub">
                <span>Salary advances recovered</span>
            </div>
        </div>
    </div>

    <!-- 3. Period Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/reports/salary') ?>" class="filter-bar-compact" style="margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--vg-navy-900);">
            <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary);"></i>
            <span>Payroll Period:</span>
        </div>

        <select name="year" class="form-select" style="max-width: 130px; height: 38px; font-size: 0.85rem;">
            <?php foreach ([date('Y'), date('Y') - 1, date('Y') - 2] as $yr): ?>
                <option value="<?= $yr ?>" <?= $year == $yr ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
        </select>

        <select name="month" class="form-select" style="max-width: 150px; height: 38px; font-size: 0.85rem;">
            <option value="">Full Year</option>
            <?php foreach ($months as $num => $mName): ?>
                <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $mName ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn"><i class="fa-solid fa-filter"></i> <span>Filter</span></button>
            <a href="<?= url('/office/finance/reports/salary') ?>" class="fbc-btn"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Modern Table of Salary Records -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-table-list" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Payroll Disbursals List (<?= $month > 0 ? ($months[$month] . ' ') : '' ?><?= $year ?>)
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Payslips:</span>
                <span class="total-records-badge"><?= number_format(count($rows)) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 120px;">Month / Year</th>
                        <th style="min-width: 180px;">Employee</th>
                        <th style="min-width: 130px;">Department</th>
                        <th style="text-align: right; width: 110px;">Basic</th>
                        <th style="text-align: right; width: 110px;">Allowances</th>
                        <th style="text-align: right; width: 110px;">Deductions</th>
                        <th style="text-align: right; width: 130px;">Net Salary</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th style="min-width: 130px;">Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 2.5rem;">
                            No payroll items recorded for this period.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r):
                            $mText = date('M Y', mktime(0, 0, 0, (int)$r['payroll_month'], 10, (int)$r['payroll_year']));
                            $isPaid = $r['payment_status'] === 'paid';
                        ?>
                        <tr>
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $i + 1 ?></div>
                            </td>
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--vg-navy-900);"><?= $mText ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($r['employee_name']) ?></strong>
                                <span style="font-size: 0.74rem; color: var(--text-muted);"><?= e($r['employee_code']) ?></span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?= e($r['department_name'] ?? 'Operations') ?>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= format_inr($r['basic_salary']) ?>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: #218765;">
                                + <?= format_inr($r['allowances']) ?>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: #D95757;">
                                - <?= format_inr($r['deductions']) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: var(--brand-primary);">
                                    <?= format_inr($r['net_salary']) ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $isPaid ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                    <?= ucfirst($r['payment_status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= !empty($r['payment_date']) ? format_date($r['payment_date']) : 'Pending' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
