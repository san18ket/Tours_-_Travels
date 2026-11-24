<?php
/**
 * PAYROLL & COMPENSATION DISBURSALS
 */
$actionsHtml = '<button type="button" class="btn btn-primary" onclick="openPayrollModal()"><i class="fa-solid fa-calculator"></i> <span>Run Monthly Payroll</span></button>';
?>

<div class="bookings-page-view finance-page-view" id="financePayrollPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Payroll & Compensation Disbursals',
            'description' => 'Automated monthly payroll batches, commercial driver allowances, advance loan recoveries, and compliant payslip generation.',
            'badge'       => 'Payroll System',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Payroll', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="payrollKpiGrid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Payroll Batches</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= $batchCount ?></div>
            <div class="booking-kpi-sub">
                <span>Monthly salary cycles</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Staff &amp; Drivers</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= count($employees) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Active compensation rosters</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Paid Amount to Date</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_currency($totalDisbursed) ?></div>
            <div class="booking-kpi-sub">
                <span>Cumulative net payouts</span>
            </div>
        </div>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Payroll System</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openPayrollModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Run Payroll</span>
            </button>
        </div>
    </div>

    <!-- 4. Payroll Batches Table & Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Monthly Payroll Run Batches</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Batches:</span>
                <span class="total-records-badge"><?= $batchCount ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="table-col-code col-priority-1">Batch Code</th>
                        <th class="table-col-schedule col-priority-1">Cycle Period</th>
                        <th class="table-col-index text-center col-priority-2" data-column-priority="secondary">Employees</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Gross Total</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Deductions</th>
                        <th class="table-col-amount col-priority-1 text-end">Net Payout</th>
                        <th class="table-col-status text-center col-priority-1">Status</th>
                        <th class="table-col-actions text-end col-priority-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payrolls)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payroll batches generated yet</h4>
                                <p class="text-muted" style="margin:0;">Click "Run Monthly Payroll" to compute salary slips for all active employees.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payrolls as $p):
                            $monthName = date('F Y', mktime(0, 0, 0, (int)$p['month'], 10, (int)$p['year']));
                            $st = strtolower($p['status']);
                            $stClass = $st === 'paid' ? 'completed' : ($st === 'approved' ? 'completed' : 'pending');
                        ?>
                        <tr>
                            <td class="table-col-code col-priority-1">
                                <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.9rem;"><?= e($p['payroll_code']) ?></strong>
                            </td>
                            <td class="table-col-schedule col-priority-1">
                                <strong style="color: var(--vg-navy-900); font-size: 0.9rem;"><?= e($monthName) ?></strong>
                                <?php if (!empty($p['payment_date'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= format_date($p['payment_date']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="table-col-index text-center col-priority-2" data-column-priority="secondary">
                                <span style="font-weight: 700; font-size: 0.92rem; color: var(--vg-navy-900);"><?= (int)$p['total_employees'] ?></span> Staff
                            </td>
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.88rem; color: var(--text-secondary);">
                                <?= format_currency((float)$p['total_gross']) ?>
                            </td>
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.88rem; color: var(--status-danger);">
                                - <?= format_currency((float)$p['total_deductions']) ?>
                            </td>
                            <td class="table-col-amount col-priority-1 text-end">
                                <strong style="font-size: 1.05rem; color: var(--brand-primary); font-weight: 800;">
                                    <?= format_currency((float)$p['total_net']) ?>
                                </strong>
                            </td>
                            <td class="table-col-status text-center col-priority-1">
                                <span class="cust-toggle-pill <?= $stClass ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $stClass ?>"></span>
                                    <span><?= strtoupper($st) ?></span>
                                </span>
                            </td>
                            <td class="table-col-actions text-end col-priority-1">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" aria-label="Actions">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                        <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/finance/payroll/' . $p['id']) ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-eye text-primary"></i> <span>View Payslips</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid">
            <?php foreach ($payrolls as $p):
                $monthName = date('F Y', mktime(0, 0, 0, (int)$p['month'], 10, (int)$p['year']));
                $st = strtolower($p['status']);
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px;">
                    <a href="<?= url('/office/finance/payroll/' . $p['id']) ?>" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; min-width: 0; flex: 1;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(23, 107, 91, 0.12); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="fa-solid fa-money-check-dollar"></i>
                        </div>
                        <div style="min-width: 0;">
                            <strong style="display: block; font-size: 0.95rem; color: var(--vg-navy-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($monthName) ?></strong>
                            <span style="font-size: 0.78rem; color: var(--text-muted);"><?= (int)$p['total_employees'] ?> Employees • <?= e($p['payroll_code']) ?></span>
                        </div>
                    </a>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-left: 0.5rem;">
                        <div style="text-align: right;">
                            <strong style="font-size: 1.05rem; color: var(--brand-primary); display: block;">
                                <?= format_currency((float)$p['total_net']) ?>
                            </strong>
                            <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: <?= $st === 'paid' ? '#218765' : '#C78A35' ?>;"><?= e($st) ?></span>
                        </div>
                    </div>
                </div>
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <div class="action-dropdown-menu">
                            <a href="<?= url('/office/finance/payroll/' . $p['id']) ?>" class="action-dropdown-item view">
                                <i class="fa-solid fa-eye text-primary"></i> <span>View Payslips</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Run Payroll Batch                                                  -->
<!-- ========================================================================= -->
<div id="payrollModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'payrollModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-calculator" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Run Monthly Salary Payroll Cycle
            </h3>
            <button type="button" class="modal-close-custom" onclick="closePayrollModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/payroll/generate') ?>" method="POST" id="generatePayrollForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <p style="font-size: 0.88rem; color: var(--text-secondary); margin-top: 0; margin-bottom: 1.25rem;">
                    This will automatically calculate monthly basic salaries, allowances, statutory deductions, and recover approved employee advance loans for all <strong><?= count($employees) ?> active staff members &amp; drivers</strong>.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="pr_month">Salary Month <span class="req">*</span></label>
                        <select name="month" id="pr_month" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == $currentMonth ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pr_year">Year <span class="req">*</span></label>
                        <select name="year" id="pr_year" class="form-select" required>
                            <?php foreach ([$currentYear - 1, $currentYear, $currentYear + 1] as $y): ?>
                                <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="background: rgba(23, 107, 91, 0.08); padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid rgba(23, 107, 91, 0.2); font-size: 0.85rem; color: var(--vg-navy-900);">
                    <i class="fa-solid fa-shield-halved" style="color: var(--brand-primary); margin-right: 0.35rem;"></i>
                    Payroll will be initially drafted in review status. You can inspect itemized payslips before committing bank disbursals.
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closePayrollModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play"></i> <span>Generate Draft Batch</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayrollModal() {
        document.getElementById('payrollModalBackdrop').style.display = 'flex';
    }
    function closePayrollModal() {
        document.getElementById('payrollModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>


