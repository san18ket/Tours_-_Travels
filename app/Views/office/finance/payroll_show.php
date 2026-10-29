<?php
/**
 * MANASWI TOURS & TRAVELS — PAYROLL BATCH DETAILS & DISBURSAL
 */
$monthName = date('F Y', mktime(0, 0, 0, (int)$payroll['month'], 10, (int)$payroll['year']));
$isPaid = $payroll['status'] === 'paid';

$headerActions = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/payroll') . '" class="btn btn-outline">&larr; Back to Batches</a>';

if (!$isPaid) {
    $headerActions .= '<button type="button" class="btn btn-primary" onclick="openDisburseModal()"><i class="fa-solid fa-money-bill-wave"></i> <span>Disburse Entire Batch</span></button>';
}
$headerActions .= '</div>';
?>

<div class="bookings-page-view finance-page-view" id="payrollShowPage">
    <!-- 1. Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => "Payroll Cycle: {$monthName}",
            'description' => "Itemized compensation breakdown for batch {$payroll['payroll_code']}. Total Net Payout: " . format_currency((float)$payroll['total_net']),
            'image'       => 'header-employees.jpg',
            'badge'       => strtoupper($payroll['status']),
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Payroll', 'url' => url('/office/finance/payroll')],
                ['label' => $payroll['payroll_code'], 'url' => ''],
            ],
            'actions'     => $headerActions,
        ]) ?>
    </div>

    <!-- 2. KPI Summary Cards -->
    <div class="booking-kpi-grid">
        <div class="booking-kpi-card is-selected" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Gross Earnings</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_currency((float)$payroll['total_gross']) ?></div>
            <div class="booking-kpi-sub">
                <span>Base pay &amp; allowances</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Deductions</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-danger);">- <?= format_currency((float)$payroll['total_deductions']) ?></div>
            <div class="booking-kpi-sub">
                <span>Statutory &amp; advance recoveries</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Net Payable Amount</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success); font-weight: 800;"><?= format_currency((float)$payroll['total_net']) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= count($payroll['items'] ?? []) ?> active staff payouts</span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Batch Status</span>
                <span class="booking-kpi-icon <?= $isPaid ? 'icon-emerald' : 'icon-amber' ?>">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="font-size: 1.35rem; text-transform: uppercase; color: <?= $isPaid ? '#218765' : '#C78A35' ?>;">
                <?= e($payroll['status']) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $isPaid ? 'Settled on ' . format_date($payroll['payment_date']) : 'Pending disbursal' ?></span>
            </div>
        </div>
    </div>

    <!-- 3. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance/payroll') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title"><?= e($payroll['payroll_code']) ?></h2>
            </div>
            <?php if (!$isPaid): ?>
                <button type="button" class="mv-add-btn" onclick="openDisburseModal()">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Disburse</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Employee Payslips Manifest -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Itemized Staff Payslips (<?= count($payroll['items'] ?? []) ?>)</h3>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Payslip #</th>
                        <th style="min-width: 200px;">Staff Member</th>
                        <th style="min-width: 140px;">Department</th>
                        <th style="text-align: right; width: 120px;">Basic Pay</th>
                        <th style="text-align: right; width: 120px;">Allowances</th>
                        <th style="text-align: right; width: 120px;">Deductions</th>
                        <th style="text-align: right; width: 120px;">Advance Dec.</th>
                        <th style="text-align: right; width: 130px;">Net Salary</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payroll['items'] as $item):
                        $st = strtolower($item['payment_status']);
                    ?>
                    <tr>
                        <td>
                            <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.85rem;"><?= e($item['payslip_number']) ?></strong>
                        </td>
                        <td>
                            <strong style="color: var(--vg-navy-900); font-size: 0.9rem;"><?= e($item['employee_name']) ?></strong>
                            <div style="font-size: 0.78rem; color: var(--text-muted);"><?= e($item['employee_code']) ?> • <?= e($item['designation']) ?></div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); text-transform: capitalize;"><?= e(str_replace('_', ' ', $item['department'])) ?></span>
                        </td>
                        <td style="text-align: right; font-size: 0.88rem; color: var(--vg-navy-900);">
                            <?= format_currency((float)$item['basic_salary']) ?>
                        </td>
                        <td style="text-align: right; font-size: 0.88rem; color: var(--status-success);">
                            + <?= format_currency((float)$item['allowances_total']) ?>
                        </td>
                        <td style="text-align: right; font-size: 0.88rem; color: var(--status-danger);">
                            - <?= format_currency((float)$item['deductions_total']) ?>
                        </td>
                        <td style="text-align: right; font-size: 0.88rem; color: #C78A35;">
                            <?= (float)$item['advance_deducted'] > 0 ? '- ' . format_currency((float)$item['advance_deducted']) : '—' ?>
                        </td>
                        <td style="text-align: right;">
                            <strong style="font-size: 0.98rem; color: var(--brand-primary); font-weight: 800;">
                                <?= format_currency((float)$item['net_salary']) ?>
                            </strong>
                        </td>
                        <td class="text-center">
                            <span class="cust-toggle-pill <?= $st === 'paid' ? 'completed' : 'pending' ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">
                                <span class="toggle-pulse-dot <?= $st === 'paid' ? 'completed' : 'pending' ?>"></span>
                                <span><?= strtoupper($st) ?></span>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="btn btn-outline btn-sm" title="View Payslip Statement">
                                <i class="fa-solid fa-file-invoice"></i> Slip
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid">
            <?php foreach ($payroll['items'] as $item): ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/finance/payslips/' . $item['id']) ?>" class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($item['employee_name']) ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($item['designation']) ?> • <?= e($item['payslip_number']) ?></span>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: var(--brand-primary); display: block;">
                            <?= format_currency((float)$item['net_salary']) ?>
                        </strong>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: <?= $item['payment_status'] === 'paid' ? '#218765' : '#C78A35' ?>;"><?= e($item['payment_status']) ?></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Disburse Batch Payout                                              -->
<!-- ========================================================================= -->
<div id="disburseModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'disburseModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-money-bill-wave" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Disburse Entire Payroll Batch
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeDisburseModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/payroll/' . $payroll['id'] . '/pay') ?>" method="POST" id="disburseForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div style="background: rgba(33, 135, 101, 0.08); border: 1.5px solid rgba(33, 135, 101, 0.25); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">Total Net Salary Disbursal:</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #218765; margin: 0.25rem 0;"><?= format_currency((float)$payroll['total_net']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= count($payroll['items']) ?> employee bank transfers will be marked as paid.</div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="disburse_acc">Disburse From Bank Account <span class="req">*</span></label>
                    <select name="account_id" id="disburse_acc" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_currency((float)$acc['current_balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="disburse_ref">Bank UTR / Disbursal Batch Reference</label>
                    <input type="text" name="payment_ref" id="disburse_ref" class="form-control" value="BATCH-SAL-<?= date('Ymd') ?>" required>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeDisburseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> <span>Authorize Bank Disbursal</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDisburseModal() {
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
    -webkit-backdrop-filter: blur(8px);
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

