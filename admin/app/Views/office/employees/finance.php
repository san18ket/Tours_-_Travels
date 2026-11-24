<?php
/**
 * EMPLOYEE FINANCIAL STATEMENT & PROFILE
 * Segregated Monthly Salary + Driver Trip Earnings + Unified Disbursements Ledger
 */

$empFullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
$empCode     = $employee['employee_code'] ?? ('EMP-' . str_pad($employee['id'], 4, '0', STR_PAD_LEFT));
$department  = ucfirst(str_replace('_', ' ', $employee['department'] ?? 'staff'));
$designation = $employee['designation'] ?? $department;

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/employees/' . $employee['id']) . '" class="btn btn-outline"><i class="fa-solid fa-user"></i> <span>Employee Profile</span></a>'
    . '<a href="' . url('/office/finance/payslips') . '" class="btn btn-outline"><i class="fa-solid fa-file-invoice"></i> <span>Payslips Directory</span></a>'
    . ($isDriver ? '<a href="' . url('/office/finance/driver-payments?driver_id=' . ($driverProfile['id'] ?? '')) . '" class="btn btn-primary"><i class="fa-solid fa-id-card-clip"></i> <span>Driver Settlements</span></a>' : '')
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="employeeFinanceModule">
    <!-- 1. Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Financial Statement: ' . $empFullName,
            'description' => 'Comprehensive financial statement uniting monthly payroll salary, driver trip compensation, advance deductions, and disbursement records.',
            'badge'       => $isDriver ? 'Driver Financials' : 'Staff Financials',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Employees', 'url' => url('/office/employees')],
                ['label' => $empFullName, 'url' => url('/office/employees/' . $employee['id'])],
                ['label' => 'Financial Statement', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. Employee Profile Strip Card -->
    <div class="vg-card" style="padding: 1.15rem 1.35rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0B192C, #1E3E62); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800;">
                <?= strtoupper(substr($employee['first_name'] ?? 'E', 0, 1) . substr($employee['last_name'] ?? '', 0, 1)) ?>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--vg-navy-900);"><?= e($empFullName) ?></h3>
                    <span class="badge" style="background: rgba(23, 107, 91, 0.1); color: var(--brand-primary); font-weight: 700; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px;">
                        <?= e($empCode) ?>
                    </span>
                    <?php if ($isDriver): ?>
                        <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563EB; font-weight: 700; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px;">
                            <i class="fa-solid fa-steering-wheel"></i> Driver
                        </span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    <?= e($designation) ?> &bull; <?= e($department) ?> &bull; Phone: <?= e($employee['phone'] ?? 'N/A') ?>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <div style="text-align: right;">
                <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Bank Account:</span>
                <div style="font-weight: 700; font-size: 0.85rem; color: #0F172A;">
                    <?= !empty($employee['bank_name']) ? e($employee['bank_name']) . ' (' . e(substr($employee['account_number'] ?? '****', -4)) . ')' : 'Cash / Not provided' ?>
                </div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Employment Status:</span>
                <div>
                    <span class="badge" style="background: rgba(33, 135, 101, 0.12); color: #218765; font-weight: 700; font-size: 0.75rem; padding: 2px 8px; border-radius: 6px;">
                        <?= ucfirst($employee['status'] ?? 'Active') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Financial KPI Summary Grid (5 Core Indicators) -->
    <div class="booking-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <!-- Card 1: Monthly Base Net Salary -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('employee.monthly_salary', 'Monthly Salary')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($monthlySalary) ?></div>
            <div class="booking-kpi-sub">
                <span>Base payroll structure</span>
            </div>
        </div>

        <!-- Card 2: Driver Trip Earnings -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('employee.trip_earnings', 'Trip Earnings')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;">
                <?= $isDriver ? format_inr($tripEarnings) : 'N/A' ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $isDriver ? ((int)($tripSummary['completed_trips'] ?? 0) . ' completed trips') : 'Non-driver employee' ?></span>
            </div>
        </div>

        <!-- Card 3: Total Combined Earnings -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('employee.total_earnings', 'Total Earnings')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalEarnings) ?></div>
            <div class="booking-kpi-sub">
                <span>Salary roll + trip compensation</span>
            </div>
        </div>

        <!-- Card 4: Total Disbursed (Paid) -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('employee.total_paid_amount', 'Total Paid Amount')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalPaid) ?></div>
            <div class="booking-kpi-sub">
                <span>Paid via company cash/bank</span>
            </div>
        </div>

        <!-- Card 5: Total Outstanding Due -->
        <div class="booking-kpi-card <?= $totalRemaining > 0 ? 'is-selected' : '' ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('employee.remaining_due', 'Total Remaining Due')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $totalRemaining > 0 ? '#C78A35' : '#10B981' ?>;">
                <?= format_inr($totalRemaining) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Sal: <?= format_inr($totalSalaryPending) ?> &bull; Trip: <?= format_inr($tripPending) ?></span>
            </div>
        </div>
    </div>

    <!-- 4. Unified Disbursements & Transactions Ledger -->
    <div class="vg-card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
        <div style="padding: 1.15rem 1.35rem; border-bottom: 1px solid rgba(23, 107, 91, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900);">
                    <i class="fa-solid fa-list-check" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                    Unified Financial Disbursement History
                </h3>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    Unified chronological ledger of monthly salary payouts, driver trip payments, and salary advance deductions.
                </p>
            </div>
            <span class="badge" style="background: #F1F5F9; color: #475569; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem;">
                <?= count($historyLedger) ?> Recorded Transactions
            </span>
        </div>

        <div class="table-responsive modern-table-container">
            <table class="modern-table data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; text-align: left; font-size: 0.78rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th class="col-priority-1" style="padding: 0.85rem 1.15rem;">Date</th>
                        <th class="col-priority-1" style="padding: 0.85rem 1rem;">Transaction Reference</th>
                        <th class="col-priority-1" style="padding: 0.85rem 1rem;">Category / Type</th>
                        <th class="col-priority-2" style="padding: 0.85rem 1rem;">Payment Method</th>
                        <th class="col-priority-3 col-optional" style="padding: 0.85rem 1rem;">Bank / UTR Ref</th>
                        <th class="col-priority-1 text-end" style="padding: 0.85rem 1rem; text-align: right;">Amount</th>
                        <th class="col-priority-2 text-center" style="padding: 0.85rem 1.15rem; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historyLedger)): ?>
                        <tr>
                            <td colspan="7" style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-muted);">
                                <i class="fa-regular fa-folder-open" style="font-size: 2rem; color: #94A3B8; margin-bottom: 0.5rem; display: block;"></i>
                                <h4 style="margin: 0; font-size: 0.95rem; color: #0F172A;"><?= e(__('employee.disbursement_records', 'No disbursement records')) ?></h4>
                                <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem;">No financial payouts or advance transactions have been recorded for this employee yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historyLedger as $row): ?>
                            <?php
                                $type = $row['transaction_type'] ?? 'salary';
                                $badgeStyle = match ($type) {
                                    'salary'  => 'background: rgba(37, 99, 235, 0.1); color: #2563EB;',
                                    'trip'    => 'background: rgba(22, 101, 52, 0.1); color: #166534;',
                                    'advance' => 'background: rgba(180, 83, 9, 0.1); color: #B45309;',
                                    default   => 'background: #F1F5F9; color: #475569;'
                                };
                            ?>
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                <!-- Date -->
                                <td class="col-priority-1" style="padding: 0.85rem 1.15rem; font-size: 0.84rem; color: #334155; font-weight: 600;">
                                    <?= !empty($row['payment_date']) ? date('d M Y', strtotime($row['payment_date'])) : '—' ?>
                                </td>

                                <!-- Reference -->
                                <td class="col-priority-1" style="padding: 0.85rem 1rem; font-size: 0.85rem; font-weight: 700; color: #0F172A;">
                                    <?= e($row['reference'] ?: '—') ?>
                                    <?php if (!empty($row['dispatch_code'])): ?>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">
                                            Trip: <?= e($row['dispatch_code']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Category / Type (Section 18: Never confuse salary with trip) -->
                                <td class="col-priority-1" style="padding: 0.85rem 1rem;">
                                    <span class="badge" style="<?= $badgeStyle ?> font-weight: 700; font-size: 0.74rem; padding: 3px 8px; border-radius: 6px;">
                                        <?= e($row['type_label']) ?>
                                    </span>
                                </td>

                                <!-- Payment Method -->
                                <td class="col-priority-2" style="padding: 0.85rem 1rem; font-size: 0.82rem; color: #475569; text-transform: capitalize;">
                                    <?= e(str_replace('_', ' ', $row['payment_method'] ?? 'N/A')) ?>
                                </td>

                                <!-- Bank / UTR Ref -->
                                <td class="col-priority-3 col-optional" style="padding: 0.85rem 1rem; font-size: 0.8rem; font-family: monospace; color: #64748B;">
                                    <?= e($row['transaction_id'] ?: '—') ?>
                                </td>

                                <!-- Amount -->
                                <td class="col-priority-1 text-end" style="padding: 0.85rem 1rem; text-align: right; font-weight: 800; font-size: 0.92rem; color: #0F172A;">
                                    <?= format_inr($row['amount']) ?>
                                </td>

                                <!-- Status -->
                                <td class="col-priority-2 text-center" style="padding: 0.85rem 1.15rem; text-align: center;">
                                    <span class="badge" style="background: rgba(33, 135, 101, 0.12); color: #218765; font-weight: 700; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                        <?= ucfirst($row['status'] ?? 'paid') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Driver Completed Trips Section (If Driver) -->
    <?php if ($isDriver): ?>
        <div class="vg-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.15rem 1.35rem; border-bottom: 1px solid rgba(23, 107, 91, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900);">
                        <i class="fa-solid fa-route" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                        Driver Completed Dispatches &amp; Trip Dues
                    </h3>
                    <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                        Recent completed duties showing trip compensations and settlement status.
                    </p>
                </div>
                <a href="<?= url('/office/finance/driver-payments?driver_id=' . ($driverProfile['id'] ?? '')) ?>" class="btn btn-outline btn-sm">
                    View in Driver Payments &rarr;
                </a>
            </div>

            <div class="table-responsive modern-table-container">
                <table class="modern-table data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; text-align: left; font-size: 0.78rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th class="col-priority-1" style="padding: 0.85rem 1.15rem;">Dispatch Code</th>
                            <th class="col-priority-1" style="padding: 0.85rem 1rem;">Route &amp; Completed Date</th>
                            <th class="col-priority-3 col-optional" style="padding: 0.85rem 1rem;">Vehicle</th>
                            <th class="col-priority-2 text-end" style="padding: 0.85rem 1rem; text-align: right;">Driver Trip Amount</th>
                            <th class="col-priority-2 text-end" style="padding: 0.85rem 1rem; text-align: right;">Paid</th>
                            <th class="col-priority-1 text-end" style="padding: 0.85rem 1rem; text-align: right;">Remaining</th>
                            <th class="col-priority-1 text-center" style="padding: 0.85rem 1.15rem; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($driverTrips)): ?>
                            <tr>
                                <td colspan="7" style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-muted);">
                                    <p style="margin: 0; font-size: 0.85rem;"><?= e(__('employee.no_completed_dispatches', 'No completed dispatches recorded for this driver yet.')) ?></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($driverTrips as $dt): ?>
                                <?php
                                    $earned = $dt['driver_trip_amount'] !== null ? (float)$dt['driver_trip_amount'] : null;
                                    $paid   = (float)$dt['driver_paid_amount'];
                                    $rem    = ($earned !== null) ? round(max(0.0, $earned - $paid), 2) : null;
                                    $pStat  = $dt['driver_payment_status'] ?? 'not_due';
                                ?>
                                <tr style="border-bottom: 1px solid #F1F5F9;">
                                    <td class="col-priority-1" style="padding: 0.85rem 1.15rem; font-weight: 700; color: #0F172A; font-size: 0.85rem;">
                                        <a href="<?= url('/office/bookings/' . (int)($dt['booking_id'] ?? $dt['id'])) ?>" style="color: inherit; text-decoration: none;">
                                            <?= e($dt['dispatch_code'] ?: ('#DSP-' . $dt['id'])) ?>
                                        </a>
                                    </td>
                                    <td class="col-priority-1" style="padding: 0.85rem 1rem; font-size: 0.82rem; color: #334155;">
                                        <div><?= e($dt['pickup_address'] ?? 'Start') ?> &rarr; <?= e($dt['drop_address'] ?? 'End') ?></div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                            <?= !empty($dt['end_time']) ? date('d M Y, h:i A', strtotime($dt['end_time'])) : (!empty($dt['start_time']) ? date('d M Y', strtotime($dt['start_time'])) : 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="col-priority-3 col-optional" style="padding: 0.85rem 1rem; font-size: 0.82rem; color: #475569;">
                                        <?= e($dt['vehicle_number'] ?? 'Vehicle N/A') ?>
                                    </td>
                                    <td class="col-priority-2 text-end" style="padding: 0.85rem 1rem; text-align: right; font-weight: 700; font-size: 0.88rem; color: #0F172A;">
                                        <?= $earned !== null ? format_inr($earned) : '<span style="color:#64748B; font-weight: normal; font-size:0.75rem;">Not specified</span>' ?>
                                    </td>
                                    <td class="col-priority-2 text-end" style="padding: 0.85rem 1rem; text-align: right; font-weight: 700; font-size: 0.88rem; color: #166534;">
                                        <?= format_inr($paid) ?>
                                    </td>
                                    <td class="col-priority-1 text-end" style="padding: 0.85rem 1rem; text-align: right; font-weight: 800; font-size: 0.88rem; color: <?= ($rem !== null && $rem > 0) ? '#C78A35' : '#10B981' ?>;">
                                        <?= $rem !== null ? format_inr($rem) : '—' ?>
                                    </td>
                                    <td class="col-priority-1 text-center" style="padding: 0.85rem 1.15rem; text-align: center;">
                                        <?= render_status_badge($pStat, 'driver_payment') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

