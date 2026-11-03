<?php
/**
 * MANASWI TOURS & TRAVELS — SALARY PAYMENT HISTORY
 * Exactly matching Reference UI System
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/payslips') . '" class="btn btn-outline"><i class="fa-solid fa-file-invoice"></i> <span>All Payslips</span></a>'
    . '<a href="' . url('/office/finance/payroll') . '" class="btn btn-primary"><i class="fa-solid fa-calculator"></i> <span>Payroll Cycles</span></a>'
    . '</div>';

$selectedMethod = $selectedMethod ?? '';
$search         = $search ?? '';
$dateFrom       = $dateFrom ?? '';
$dateTo         = $dateTo ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($history);
$currP          = $currentPage ?? 1;
?>

<div class="bookings-page-view finance-page-view" id="salaryHistoryPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Salary Payment History',
            'description' => 'Complete audit archive of all settled staff salaries, bank transfers, payout vouchers, and disbursal dates.',
            'image'       => 'header-employees.jpg',
            'badge'       => 'Settled Payouts',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Staff Salary', 'url' => url('/office/finance/payroll')],
                ['label' => 'Salary History', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="salaryHistKpiGrid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Disbursed</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalDisbursed) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totRec) ?> salary disbursal vouchers</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">This Month Disbursals</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($thisMonthDisbursed) ?></div>
            <div class="booking-kpi-sub">
                <span>Disbursed in <?= date('F Y') ?></span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Average Payout Amount</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= format_inr($avgSalary) ?></div>
            <div class="booking-kpi-sub">
                <span>Mean salary per employee</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/salary-history') ?>" class="filter-bar-compact" id="salaryHistoryFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by employee name, code..." value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/salary-history') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="method" id="methodInput">
            <option value="" <?= empty($selectedMethod) ? 'selected' : '' ?>>All Disbursal Modes</option>
            <option value="bank_transfer" <?= $selectedMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer (NEFT/IMPS)</option>
            <option value="upi" <?= $selectedMethod === 'upi' ? 'selected' : '' ?>>UPI</option>
            <option value="cash" <?= $selectedMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="cheque" <?= $selectedMethod === 'cheque' ? 'selected' : '' ?>>Bank Cheque</option>
        </select>

        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="From Disbursal Date">
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="To Disbursal Date">

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Apply Filters"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/salary-history') ?>" class="fbc-btn" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Modern Table & Mobile Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Salary Disbursal Records
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Vouchers:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 120px;">Payment Date</th>
                        <th style="min-width: 180px;">Employee</th>
                        <th style="min-width: 120px;">Salary Cycle</th>
                        <th style="min-width: 130px;">Disbursal Mode</th>
                        <th style="min-width: 150px;">Paid From Account</th>
                        <th style="text-align: right; width: 130px;">Net Salary Paid</th>
                        <th class="text-center" style="width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No salary payment records found</h4>
                                <p class="text-muted" style="margin:0;">No paid payslips match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($history as $i => $row):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $mVal = (int)($row['month'] ?? $row['payroll_month'] ?? 1);
                            $yVal = (int)($row['year'] ?? $row['payroll_year'] ?? date('Y'));
                            $monthText = date('M Y', mktime(0, 0, 0, $mVal, 10, $yVal));
                            $payDate = $row['paid_at'] ?? $row['payment_date'] ?? $row['created_at'];
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Date -->
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.86rem;"><?= !empty($payDate) ? format_date($payDate) : '—' ?></strong>
                            </td>

                            <!-- 3. Employee -->
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($row['employee_name']) ?></strong>
                                <span style="font-size: 0.74rem; color: var(--text-muted);"><?= e($row['employee_code']) ?> &bull; <?= e($row['department_name'] ?? 'Staff') ?></span>
                            </td>

                            <!-- 4. Cycle -->
                            <td>
                                <strong style="font-size: 0.86rem; color: var(--brand-primary);"><?= $monthText ?></strong>
                            </td>

                            <!-- 5. Mode -->
                            <td>
                                <span class="badge" style="font-size: 0.74rem; text-transform: uppercase; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: rgba(33, 135, 101, 0.12); color: #218765;">
                                    <?= e($row['payment_method'] ?? 'bank_transfer') ?>
                                </span>
                            </td>

                            <!-- 6. Account -->
                            <td>
                                <div style="font-size: 0.84rem; color: var(--vg-navy-900); font-weight: 600;">
                                    <?= e($row['account_name'] ?? 'General Treasury') ?>
                                </div>
                            </td>

                            <!-- 7. Net Amount -->
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: #218765;">
                                    <?= format_inr($row['net_salary']) ?>
                                </strong>
                            </td>

                            <!-- 8. Action -->
                            <td class="text-center">
                                <a href="<?= url('/office/finance/payslips/' . $row['id']) ?>" class="btn btn-outline btn-sm" style="padding: 3px 8px;" title="View Payslip">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid">
            <?php foreach ($history as $row):
                $mVal = (int)($row['month'] ?? $row['payroll_month'] ?? 1);
                $yVal = (int)($row['year'] ?? $row['payroll_year'] ?? date('Y'));
                $monthText = date('M Y', mktime(0, 0, 0, $mVal, 10, $yVal));
                $payDate = $row['paid_at'] ?? $row['payment_date'] ?? $row['created_at'];
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($row['employee_name']) ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= !empty($payDate) ? format_date($payDate) : '—' ?> &bull; <?= $monthText ?></span>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1rem; color: #218765; display: block;"><?= format_inr($row['net_salary']) ?></strong>
                        <a href="<?= url('/office/finance/payslips/' . $row['id']) ?>" class="btn btn-sm btn-outline" style="margin-top: 4px; padding: 2px 8px; font-size: 0.72rem;">
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
        $buildPageUrl = function(int $p) use ($search, $selectedMethod, $dateFrom, $dateTo, $perPage) {
            $q = [
                'search'    => $search,
                'method'    => $selectedMethod,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'per_page'  => $perPage,
                'page'      => $p,
            ];
            return url('/office/finance/salary-history?' . http_build_query(array_filter($q)));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> salary record(s)</div>
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
