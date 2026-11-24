<?php
/**
 * STAFF SALARY & PAYSLIPS DIRECTORY
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

$makeFilterUrl = function(array $overrides = []) use ($search, $selectedMonth, $selectedYear, $selectedStatus, $perPage) {
    $params = [
        'search'   => $search,
        'month'    => $selectedMonth,
        'year'     => $selectedYear,
        'status'   => $selectedStatus,
        'per_page' => $perPage,
    ];
    $merged = array_merge($params, $overrides);
    return url('/office/finance/payslips?' . http_build_query(array_filter($merged, fn($v) => $v !== '' && $v !== null && $v !== 0 && $v !== '0')));
};
?>

<div class="bookings-page-view finance-page-view" id="payslipsDirectoryPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Staff Salary & Payslips',
            'description' => 'Employee salary breakdown, statutory allowances, advance deductions, payslip vouchers, and disbursal records.',
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

    <!-- 3. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back to Finance">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Staff Payslips</h2>
            </div>
            <a href="<?= url('/office/finance/payroll') ?>" class="mv-add-btn" style="background: rgba(22, 68, 59, 0.08); color: var(--brand-primary);">
                <i class="fa-solid fa-calculator"></i>
                <span>Cycles</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/payslips') ?>" class="mv-search-form" id="mvPayslipSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search staff, code, role..." value="<?= e($search) ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= $makeFilterUrl(['search' => '']) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle <?= (!empty($selectedStatus) || !empty($selectedMonth) || !empty($selectedYear)) ? 'active' : '' ?>" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
                <?php if (!empty($selectedStatus)): ?><input type="hidden" name="status" value="<?= e($selectedStatus) ?>"><?php endif; ?>
                <?php if (!empty($selectedMonth)): ?><input type="hidden" name="month" value="<?= (int)$selectedMonth ?>"><?php endif; ?>
                <?php if (!empty($selectedYear)): ?><input type="hidden" name="year" value="<?= (int)$selectedYear ?>"><?php endif; ?>
                <?php if ($perPage != 15): ?><input type="hidden" name="per_page" value="<?= (int)$perPage ?>"><?php endif; ?>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/payslips') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Status:</label>
                            <select name="status" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
                                <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending Payment</option>
                                <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>Paid &amp; Settled</option>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Month:</label>
                                <select name="month" class="form-select" style="font-size: 0.82rem; height: 38px;">
                                    <option value="">All Months</option>
                                    <?php foreach ($months as $num => $mName): ?>
                                        <option value="<?= $num ?>" <?= $selectedMonth == $num ? 'selected' : '' ?>><?= $mName ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Year:</label>
                                <select name="year" class="form-select" style="font-size: 0.82rem; height: 38px;">
                                    <option value="">All Years</option>
                                    <?php foreach ([date('Y'), date('Y') - 1, date('Y') - 2] as $yr): ?>
                                        <option value="<?= $yr ?>" <?= $selectedYear == $yr ? 'selected' : '' ?>><?= $yr ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 4px; color: var(--vg-navy-900);">Per Page:</label>
                            <select name="per_page" class="form-select" style="font-size: 0.85rem; height: 38px;">
                                <?php foreach ([10, 15, 25, 50] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="<?= url('/office/finance/payslips') ?>" class="btn btn-outline btn-sm">Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Status Scroll Pills -->
        <div class="mv-status-scroll">
            <a href="<?= $makeFilterUrl(['status' => '']) ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)$totRec ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'pending']) ?>" class="mv-pill <?= $selectedStatus === 'pending' ? 'active' : '' ?>">
                <span>Pending</span>
                <span class="mv-pill-cnt"><?= (int)($totalPending > 0 ? 1 : 0) ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'paid']) ?>" class="mv-pill <?= $selectedStatus === 'paid' ? 'active' : '' ?>">
                <span>Paid</span>
            </a>
        </div>
    </div>

    <!-- 4. DESKTOP ONLY: Compact Filter Bar -->
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
                        <th class="table-col-index text-center col-priority-1">#</th>
                        <th class="table-col-name col-priority-1">Employee</th>
                        <th class="table-col-schedule col-priority-1">Month</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Basic</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Allowances</th>
                        <th class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional">Deductions</th>
                        <th class="table-col-amount col-priority-1 text-end">Monthly Salary</th>
                        <th class="table-col-amount col-priority-2 text-end" data-column-priority="secondary">Trip Earnings</th>
                        <th class="table-col-status text-center col-priority-1">Salary Status</th>
                        <th class="table-col-actions text-end col-priority-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payslips)): ?>
                    <tr>
                        <td colspan="10">
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
                            $isDriver = !empty($item['is_driver']);
                            $tripSum = $item['trip_summary'] ?? null;
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="table-col-index text-center col-priority-1">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Employee -->
                            <td class="table-col-name col-priority-1">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <strong style="color: var(--vg-navy-900); font-size: 0.88rem;"><?= e($item['employee_name']) ?></strong>
                                    <?php if ($isDriver): ?>
                                        <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563EB; font-size: 0.68rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;" title="Driver">
                                            <i class="fa-solid fa-id-badge"></i> Driver
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 1px;">
                                    <?= e($item['employee_code']) ?> &bull; <?= e($item['department_name'] ?? $item['department'] ?? 'Staff') ?>
                                </span>
                            </td>

                            <!-- 3. Month -->
                            <td class="table-col-schedule col-priority-1">
                                <strong style="font-size: 0.85rem; color: var(--vg-navy-900);"><?= $monthText ?></strong>
                            </td>

                            <!-- 4. Basic -->
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.84rem; color: var(--vg-navy-900);">
                                <?= format_inr($item['basic_salary']) ?>
                            </td>

                            <!-- 5. Allowances -->
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.84rem; color: #218765;">
                                + <?= format_inr($allowances) ?>
                            </td>

                            <!-- 6. Deductions -->
                            <td class="table-col-amount col-priority-3 col-optional text-end" data-column-priority="optional" style="font-size: 0.84rem; color: #D95757;">
                                - <?= format_inr($deductions) ?>
                            </td>

                            <!-- 7. Net Monthly Salary -->
                            <td class="table-col-amount col-priority-1 text-end">
                                <strong style="font-size: 0.92rem; color: var(--brand-primary);">
                                    <?= format_inr($item['net_salary']) ?>
                                </strong>
                            </td>

                            <!-- 8. Trip Earnings (Section 17: Separate from base salary) -->
                            <td class="table-col-amount col-priority-2 text-end" data-column-priority="secondary">
                                <?php if ($isDriver && $tripSum): ?>
                                    <div style="font-size: 0.86rem; font-weight: 700; color: #0F172A;">
                                        <?= format_inr($tripSum['total_earned']) ?>
                                    </div>
                                    <div style="font-size: 0.7rem; color: <?= $tripSum['total_pending'] > 0 ? '#C78A35' : '#10B981' ?>;">
                                        <?= $tripSum['total_pending'] > 0 ? 'Due: ' . format_inr($tripSum['total_pending']) : 'All Settled' ?>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Salary Status -->
                            <td class="table-col-status text-center col-priority-1">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $isPaid ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                    <?= ucfirst($item['payment_status']) ?>
                                </span>
                            </td>

                            <!-- 10. Actions (Single Actions Menu) -->
                            <td class="table-col-actions text-end col-priority-1">
                                <div class="action-dropdown-wrap">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" aria-label="Actions">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                        <span>Actions</span>
                                    </button>
                                    <div class="action-dropdown-menu">
                                        <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-print text-primary"></i> <span>Print Payslip</span>
                                        </a>
                                        <a href="<?= url('/office/employees/' . $item['employee_id'] . '/finance') ?>" class="action-dropdown-item">
                                            <i class="fa-solid fa-chart-line text-info"></i> <span>Financial Profile</span>
                                        </a>
                                        <?php if (!$isPaid): ?>
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item text-success" onclick="openDisburseModal(<?= (int)$item['id'] ?>, '<?= addslashes(e($item['employee_name'])) ?>', '<?= $monthText ?>', <?= (float)$item['net_salary'] ?>)">
                                                <i class="fa-solid fa-credit-card"></i> <span>Disburse Salary</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Canonical Design) -->
        <div class="cards-grid">
            <?php foreach ($payslips as $item):
                $isPaid = $item['payment_status'] === 'paid';
                $mVal = (int)($item['month'] ?? $item['payroll_month'] ?? 1);
                $yVal = (int)($item['year'] ?? $item['payroll_year'] ?? date('Y'));
                $monthText = date('M Y', mktime(0, 0, 0, $mVal, 10, $yVal));
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card">
                    <!-- Left: Monogram Icon -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar" style="background: rgba(22, 68, 59, 0.08); color: var(--brand-primary);">
                            <i class="fa-solid fa-file-invoice" style="font-size:20px;"></i>
                        </div>
                    </div>

                    <!-- Middle: Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($item['employee_code'] ?? 'EMP') ?></div>
                        <div class="mv-tour-title" title="<?= e($item['employee_name']) ?>">
                            <?= e($item['employee_name']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span><?= $monthText ?> &bull; <?= e($item['designation'] ?? 'Staff') ?></span>
                        </div>
                        <div class="mv-vehicle" style="color: <?= $isPaid ? '#218765' : '#D95757' ?>; font-weight: 700;">
                            <i class="fa-solid fa-circle-dot" style="font-size: 0.65rem;"></i>
                            <span><?= strtoupper($item['payment_status']) ?></span>
                        </div>
                    </div>

                    <!-- Right: Amount -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge <?= $isPaid ? 'mv-status-paid' : 'mv-status-unpaid' ?>">
                            <?= ucfirst($item['payment_status']) ?>
                        </span>
                        <div class="mv-price" style="color: var(--brand-primary);"><?= format_inr($item['net_salary']) ?></div>
                    </div>
                </div>

                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" aria-label="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                            <span>Actions</span>
                        </button>
                        <div class="action-dropdown-menu">
                            <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="action-dropdown-item">
                                <i class="fa-solid fa-print text-primary"></i> <span>Print Payslip</span>
                            </a>
                            <a href="<?= url('/office/employees/' . $item['employee_id'] . '/finance') ?>" class="action-dropdown-item">
                                <i class="fa-solid fa-chart-line text-info"></i> <span>Financial Profile</span>
                            </a>
                            <?php if (!$isPaid): ?>
                                <div class="action-dropdown-divider"></div>
                                <button type="button" class="action-dropdown-item text-success" onclick="openDisburseModal(<?= (int)$item['id'] ?>, '<?= addslashes(e($item['employee_name'])) ?>', '<?= $monthText ?>', <?= (float)$item['net_salary'] ?>)">
                                    <i class="fa-solid fa-credit-card"></i> <span>Disburse Salary</span>
                                </button>
                            <?php endif; ?>
                        </div>
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
    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        if (!p) return;
        p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    }
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


