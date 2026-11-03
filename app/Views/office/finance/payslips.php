<?php
/**
 * MANASWI TOURS & TRAVELS — STAFF SALARY & PAYSLIPS DIRECTORY
 * Fully matching Reference UI System
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/salary-history') . '" class="btn btn-outline"><i class="fa-solid fa-clock-rotate-left"></i> <span>Salary History</span></a>'
    . '<a href="' . url('/office/finance/payroll') . '" class="btn btn-primary"><i class="fa-solid fa-calculator"></i> <span>Payroll Cycles</span></a>'
    . '</div>';

$selectedMonth  = $selectedMonth ?? 0;
$selectedYear   = $selectedYear ?? 0;
$selectedStatus = $selectedStatus ?? '';
$search         = $search ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($payslips);
$currP          = $currentPage ?? 1;

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<div class="bookings-page-view finance-page-view" id="payslipsDirectoryPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Staff Salary & Payslips',
            'description' => 'Employee salary breakdown, statutory allowances, advance deductions, payslip vouchers, and disbursal records.',
            'image'       => 'header-employees.jpg',
            'badge'       => 'Salary Payouts',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Staff Salary', 'url' => url('/office/finance/payroll')],
                ['label' => 'Payslips', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="payslipsKpiGrid">
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payslips') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Salary Roll</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_inr($totalDisbursable) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totalEmployees) ?> staff on payroll</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'paid' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payslips?status=paid') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Disbursed Salary</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalPaid) ?></div>
            <div class="booking-kpi-sub">
                <span>Successfully settled payments</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payslips?status=pending') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Payouts</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $totalPending > 0 ? '#D95757' : 'var(--brand-primary)' ?>;">
                <?= format_inr($totalPending) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Awaiting monthly payment</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/payslips') ?>" class="filter-bar-compact" id="payslipFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by employee name, code, designation..." value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/payslips') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="month" id="monthInput">
            <option value="">All Months</option>
            <?php foreach ($months as $num => $mName): ?>
                <option value="<?= $num ?>" <?= $selectedMonth == $num ? 'selected' : '' ?>><?= $mName ?></option>
            <?php endforeach; ?>
        </select>

        <select name="year" id="yearInput">
            <option value="">All Years</option>
            <?php foreach ([date('Y'), date('Y') - 1, date('Y') - 2] as $yr): ?>
                <option value="<?= $yr ?>" <?= $selectedYear == $yr ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending Payment</option>
            <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>Paid &amp; Settled</option>
        </select>

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Apply Filters"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/payslips') ?>" class="fbc-btn" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Modern Table & Mobile Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-file-invoice" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Staff Monthly Payslips
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Payslips:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Employee</th>
                        <th style="min-width: 120px;">Salary Month</th>
                        <th style="text-align: right; width: 110px;">Basic</th>
                        <th style="text-align: right; width: 110px;">Allowances</th>
                        <th style="text-align: right; width: 110px;">Deductions</th>
                        <th style="text-align: right; width: 120px;">Net Salary</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payslips)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payslips found</h4>
                                <p class="text-muted" style="margin:0;">No payroll items match your active filters. Generate payroll to create new payslips.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payslips as $i => $item):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $isPaid = $item['payment_status'] === 'paid';
                            $mVal = (int)($item['month'] ?? $item['payroll_month'] ?? 1);
                            $yVal = (int)($item['year'] ?? $item['payroll_year'] ?? date('Y'));
                            $monthText = date('M Y', mktime(0, 0, 0, $mVal, 10, $yVal));
                            $allowances = $item['allowances'] ?? $item['allowances_total'] ?? 0;
                            $deductions = $item['deductions'] ?? $item['deductions_total'] ?? 0;
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Employee -->
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($item['employee_name']) ?></strong>
                                <span style="font-size: 0.74rem; color: var(--text-muted);"><?= e($item['employee_code']) ?> &bull; <?= e($item['department_name'] ?? $item['department'] ?? 'Staff') ?></span>
                            </td>

                            <!-- 3. Month -->
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--vg-navy-900);"><?= $monthText ?></strong>
                            </td>

                            <!-- 4. Basic -->
                            <td style="text-align: right; font-size: 0.85rem; color: var(--vg-navy-900);">
                                <?= format_inr($item['basic_salary']) ?>
                            </td>

                            <!-- 5. Allowances -->
                            <td style="text-align: right; font-size: 0.85rem; color: #218765;">
                                + <?= format_inr($allowances) ?>
                            </td>

                            <!-- 6. Deductions -->
                            <td style="text-align: right; font-size: 0.85rem; color: #D95757;">
                                - <?= format_inr($deductions) ?>
                            </td>

                            <!-- 7. Net Salary -->
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: var(--brand-primary);">
                                    <?= format_inr($item['net_salary']) ?>
                                </strong>
                            </td>

                            <!-- 8. Status -->
                            <td class="text-center">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $isPaid ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                    <?= ucfirst($item['payment_status']) ?>
                                </span>
                            </td>

                            <!-- 9. Actions -->
                            <td class="text-center">
                                <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                    <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="btn btn-outline btn-sm" style="padding: 3px 8px;" title="View & Print Payslip">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <?php if (!$isPaid): ?>
                                    <button type="button" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size: 0.75rem; background: #218765; border-color: #218765;" onclick="openDisburseModal(<?= (int)$item['id'] ?>, '<?= addslashes(e($item['employee_name'])) ?>', '<?= $monthText ?>', <?= (float)$item['net_salary'] ?>)" title="Mark as Paid">
                                        <i class="fa-solid fa-hand-holding-dollar"></i> Pay
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List -->
        <div class="cards-grid">
            <?php foreach ($payslips as $item):
                $isPaid = $item['payment_status'] === 'paid';
                $mVal = (int)($item['month'] ?? $item['payroll_month'] ?? 1);
                $yVal = (int)($item['year'] ?? $item['payroll_year'] ?? date('Y'));
                $monthText = date('M Y', mktime(0, 0, 0, $mVal, 10, $yVal));
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($item['employee_name']) ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= $monthText ?> &bull; <?= e($item['employee_code']) ?></span>
                        <div style="font-size: 0.75rem; color: <?= $isPaid ? '#218765' : '#D95757' ?>; font-weight: 700; margin-top: 2px; text-transform: uppercase;">
                            <?= e($item['payment_status']) ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1rem; color: var(--brand-primary); display: block;"><?= format_inr($item['net_salary']) ?></strong>
                        <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="btn btn-sm btn-outline" style="margin-top: 4px; padding: 2px 8px; font-size: 0.72rem;">
                            View Slip
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modern Pagination -->
    <?php
        $totalPages = $totalPages ?? 1;
        $from = $totRec > 0 ? (($currP - 1) * $perPage) + 1 : 0;
        $to   = min($currP * $perPage, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedMonth, $selectedYear, $selectedStatus, $perPage) {
            $q = [
                'search'   => $search,
                'month'    => $selectedMonth,
                'year'     => $selectedYear,
                'status'   => $selectedStatus,
                'per_page' => $perPage,
                'page'     => $p,
            ];
            return url('/office/finance/payslips?' . http_build_query(array_filter($q)));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> payslip(s)</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous">&#10094;</a>
            <?php
                $start = max(1, $currP - 2);
                $end = min($totalPages, $currP + 2);
                for ($p = $start; $p <= $end; $p++):
            ?>
                <a href="<?= $buildPageUrl($p) ?>" class="<?= $p === $currP ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Disburse / Pay Salary                                              -->
<!-- ========================================================================= -->
<div id="disburseModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'disburseModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-hand-holding-dollar" style="color: #218765; margin-right: 0.45rem;"></i>
                Pay Salary: <span id="disburseEmpName"></span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeDisburseModal()">&times;</button>
        </div>
        <form id="disburseForm" action="" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/payslips') ?>">
            <div class="modal-body-custom">
                <div style="background: rgba(23, 107, 91, 0.06); padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Salary Month</span>
                        <strong style="color: var(--vg-navy-900); font-size: 0.92rem;" id="disburseCycle"></strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Net Salary Payable</span>
                        <strong style="color: #218765; font-size: 1.05rem;" id="disburseAmount"></strong>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="disb_date">Disbursal Date <span class="req">*</span></label>
                        <input type="date" name="payment_date" id="disb_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="disb_method">Payment Mode <span class="req">*</span></label>
                        <select name="payment_method" id="disb_method" class="form-select" required>
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI (GPay / PhonePe)</option>
                            <option value="cash">Counter Cash</option>
                            <option value="cheque">Bank Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="disb_account_id">Debit from Company Account <span class="req">*</span></label>
                    <select name="account_id" id="disb_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (Available: <?= format_inr($acc['current_balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeDisburseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Salary Payout</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDisburseModal(id, empName, cycle, amount) {
        document.getElementById('disburseForm').action = '<?= url('/office/finance/payslips') ?>/' + id + '/pay';
        document.getElementById('disburseEmpName').textContent = empName;
        document.getElementById('disburseCycle').textContent = cycle;
        document.getElementById('disburseAmount').textContent = '₹ ' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('disburseModalBackdrop').style.display = 'flex';
    }
    function closeDisburseModal() {
        document.getElementById('disburseModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

<style>
.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 35, 30, 0.45);
    backdrop-filter: blur(8px);
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
    max-width: 520px;
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
</style>
