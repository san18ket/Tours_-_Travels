<?php
/**
 * MANASWI TOURS & TRAVELS — OFFICIAL EMPLOYEE SALARY SLIP
 * Corporate Formatted Payslip Receipt (Print & PDF Compatible)
 */
$monthName = date('F Y', mktime(0, 0, 0, (int)$payslip['month'], 10, (int)$payslip['year']));
$isPaid = $payslip['payment_status'] === 'paid';
?>

<div class="bookings-page-view finance-page-view" style="max-width: 900px; margin: 0 auto;">
    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="<?= url('/office/finance/payroll/' . $payslip['payroll_id']) ?>" class="btn btn-outline btn-sm">
            &larr; Back to Payroll Batch
        </a>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="fa-solid fa-print"></i> <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Corporate Payslip Document Card -->
    <div class="vg-card" style="padding: 2.5rem; background: #ffffff; border: 1.5px solid rgba(22, 68, 59, 0.12); box-shadow: 0 10px 30px rgba(22, 68, 59, 0.08); border-radius: var(--vg-radius-lg, 18px);" id="printablePayslip">
        <!-- Corporate Letterhead -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 2px solid var(--brand-primary); margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="<?= asset('images/manaswi-logo.svg') ?>" alt="MANASWI Logo" style="height: 52px; width: auto;" onerror="this.style.display='none'">
                <div>
                    <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-900); margin: 0; letter-spacing: -0.5px;">MANASWI TOURS &amp; TRAVELS</h1>
                    <div style="font-size: 0.85rem; color: var(--brand-primary); font-weight: 700;">Safe Journey • Happy Memories</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Fleet Operations &amp; Commercial Transport Logistics</div>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1.15rem; font-weight: 800; color: var(--brand-primary); text-transform: uppercase;">SALARY SLIP</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--vg-navy-900); margin: 2px 0;"><?= e($monthName) ?></div>
                <div style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">Ref: <?= e($payslip['payslip_number']) ?></div>
                <span class="cust-toggle-pill <?= $isPaid ? 'completed' : 'pending' ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; margin-top: 4px;">
                    <span class="toggle-pulse-dot <?= $isPaid ? 'completed' : 'pending' ?>"></span>
                    <span><?= strtoupper($payslip['payment_status']) ?></span>
                </span>
            </div>
        </div>

        <!-- Employee Summary Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; padding: 1.15rem; background: var(--page-background); border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(22, 68, 59, 0.08);">
            <div>
                <table style="width: 100%; font-size: 0.86rem; border-collapse: collapse;">
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0; width: 120px;">Staff Name:</td>
                        <td style="font-weight: 700; color: var(--vg-navy-900); padding: 3px 0;"><?= e($payslip['employee_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Employee ID:</td>
                        <td style="font-weight: 700; color: var(--brand-primary); padding: 3px 0;"><?= e($payslip['employee_code']) ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Designation:</td>
                        <td style="color: var(--vg-navy-900); padding: 3px 0;"><?= e($payslip['designation']) ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Department:</td>
                        <td style="color: var(--vg-navy-900); text-transform: capitalize; padding: 3px 0;"><?= e(str_replace('_', ' ', $payslip['department'])) ?></td>
                    </tr>
                </table>
            </div>
            <div>
                <table style="width: 100%; font-size: 0.86rem; border-collapse: collapse;">
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0; width: 120px;">Disbursal Mode:</td>
                        <td style="font-weight: 600; color: var(--vg-navy-900); padding: 3px 0;"><?= e($payslip['payment_method'] ?? 'Direct Bank Transfer') ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Payment Ref:</td>
                        <td style="font-weight: 600; color: var(--vg-navy-900); padding: 3px 0;"><?= e($payslip['payment_ref'] ?? 'Pending') ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Staff Mobile:</td>
                        <td style="color: var(--vg-navy-900); padding: 3px 0;"><?= e($payslip['employee_phone'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); padding: 3px 0;">Payment Date:</td>
                        <td style="color: var(--vg-navy-900); padding: 3px 0;"><?= $isPaid ? format_date($payslip['paid_at'] ?: date('Y-m-d')) : 'Pending Cycle Run' ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Itemized Earnings & Deductions Tables -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <!-- Left: Gross Earnings -->
            <div>
                <table class="table" style="width: 100%; font-size: 0.88rem; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(33, 135, 101, 0.1); border-bottom: 2px solid #218765;">
                            <th style="padding: 8px 10px; text-align: left; color: #165A43;">Earnings Component</th>
                            <th style="padding: 8px 10px; text-align: right; color: #165A43;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Basic Salary</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); font-weight: 600;"><?= format_currency((float)$payslip['basic_salary']) ?></td>
                        </tr>
                        <?php if ((float)($payslip['hra'] ?? 0) > 0): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">House Rent Allowance (HRA)</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); font-weight: 600;"><?= format_currency((float)$payslip['hra']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ((float)($payslip['transport_allowance'] ?? 0) > 0): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Transport / Travel Allowance</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); font-weight: 600;"><?= format_currency((float)$payslip['transport_allowance']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ((float)$payslip['allowances_total'] > 0 && empty($payslip['hra'])): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Operational Allowances &amp; Bata</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); font-weight: 600;"><?= format_currency((float)$payslip['allowances_total']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr style="background: var(--page-background); font-weight: 800;">
                            <td style="padding: 10px; color: var(--vg-navy-900);">Total Gross Earnings</td>
                            <td style="padding: 10px; text-align: right; color: var(--brand-primary);"><?= format_currency((float)$payslip['gross_salary']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right: Deductions -->
            <div>
                <table class="table" style="width: 100%; font-size: 0.88rem; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(217, 87, 87, 0.1); border-bottom: 2px solid #D95757;">
                            <th style="padding: 8px 10px; text-align: left; color: #9E2D2D;">Deductions &amp; Recoveries</th>
                            <th style="padding: 8px 10px; text-align: right; color: #9E2D2D;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ((float)($payslip['pf_deduction'] ?? 0) > 0): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Provident Fund (EPF)</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); color: var(--status-danger);"><?= format_currency((float)$payslip['pf_deduction']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ((float)($payslip['professional_tax'] ?? 0) > 0): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Professional Tax</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); color: var(--status-danger);"><?= format_currency((float)$payslip['professional_tax']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ((float)$payslip['deductions_total'] > 0 && empty($payslip['pf_deduction'])): ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Statutory Deductions</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); color: var(--status-danger);"><?= format_currency((float)$payslip['deductions_total']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid rgba(22, 68, 59, 0.08);">Staff Advance Recovery</td>
                            <td style="padding: 8px 10px; text-align: right; border-bottom: 1px solid rgba(22, 68, 59, 0.08); color: #C78A35; font-weight: 600;">
                                <?= (float)$payslip['advance_deducted'] > 0 ? format_currency((float)$payslip['advance_deducted']) : '₹ 0.00' ?>
                            </td>
                        </tr>
                        <tr style="background: var(--page-background); font-weight: 800;">
                            <td style="padding: 10px; color: var(--vg-navy-900);">Total Deductions</td>
                            <td style="padding: 10px; text-align: right; color: var(--status-danger);">
                                <?= format_currency((float)$payslip['deductions_total'] + (float)$payslip['advance_deducted']) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Net Payable Salary Banner -->
        <div style="background: #E8F4F0; border: 2px solid #B2D8CF; border-radius: 14px; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <div style="font-size: 0.85rem; text-transform: uppercase; font-weight: 700; color: var(--brand-primary); letter-spacing: 0.5px;">Net Take-Home Salary</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Direct credit to employee bank account</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1.85rem; font-weight: 900; color: #165A43;">
                    <?= format_currency((float)$payslip['net_salary']) ?>
                </div>
            </div>
        </div>

        <!-- Signatures & Statutory Footer -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; padding-top: 2rem; border-top: 1px dashed rgba(22, 68, 59, 0.2); font-size: 0.82rem; color: var(--text-muted);">
            <div>
                <div style="height: 40px;"></div>
                <div style="border-top: 1px solid #94a3b8; width: 180px; padding-top: 4px; font-weight: 600; color: var(--vg-navy-900);">Employee Signature</div>
            </div>
            <div style="text-align: right;">
                <div style="height: 40px; display: flex; align-items: flex-end; justify-content: flex-end;">
                    <span style="font-family: cursive; font-size: 1.1rem; color: var(--brand-primary);">Manaswi Accounts</span>
                </div>
                <div style="border-top: 1px solid #94a3b8; width: 180px; margin-left: auto; padding-top: 4px; font-weight: 600; color: var(--vg-navy-900);">Authorized Signatory</div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print,
    .sidebar,
    .topbar,
    .bottom-nav,
    .app-footer,
    .desktop-header-wrapper {
        display: none !important;
    }
    body, html {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .main-wrapper, .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    #printablePayslip {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>
