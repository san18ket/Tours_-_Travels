<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Invoices & Billing Ledger</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Tax invoices, payment reconciliation, balance tracking, and immutable ledger integrity.</p>
    </div>
</div>

<?php
    $totalInvoiced = 0.0;
    $totalPaid = 0.0;
    foreach ($invoices as $inv) {
        $totalInvoiced += (float)$inv['total_amount'];
        $totalPaid += (float)$inv['paid_amount'];
    }
    $totalOutstanding = max(0.0, $totalInvoiced - $totalPaid);
?>

<!-- Financial Summary KPI Mini-Strip -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background:#FFFFFF; border:1px solid var(--vg-pearl-200); border-radius:var(--vg-radius-md); padding:1rem 1.25rem; box-shadow:var(--vg-shadow-sm);">
        <div style="font-size:0.75rem; font-weight:700; color:var(--vg-text-muted); text-transform:uppercase;">Invoiced Volume</div>
        <div style="font-size:1.35rem; font-weight:800; color:var(--vg-navy-900);"><?= format_currency($totalInvoiced) ?></div>
    </div>
    <div style="background:#FFFFFF; border:1px solid var(--vg-pearl-200); border-radius:var(--vg-radius-md); padding:1rem 1.25rem; box-shadow:var(--vg-shadow-sm);">
        <div style="font-size:0.75rem; font-weight:700; color:var(--vg-success); text-transform:uppercase;">Collections Received</div>
        <div style="font-size:1.35rem; font-weight:800; color:var(--vg-success);"><?= format_currency($totalPaid) ?></div>
    </div>
    <div style="background:#FFFFFF; border:1px solid var(--vg-pearl-200); border-radius:var(--vg-radius-md); padding:1rem 1.25rem; box-shadow:var(--vg-shadow-sm);">
        <div style="font-size:0.75rem; font-weight:700; color:var(--vg-danger); text-transform:uppercase;">Outstanding Balance</div>
        <div style="font-size:1.35rem; font-weight:800; color:var(--vg-danger);"><?= format_currency($totalOutstanding) ?></div>
    </div>
</div>

<div class="vg-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <span class="card-title">Tax Invoices (<?= count($invoices) ?>)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($invoices)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="empty-state-title">No Invoices Issued Yet</div>
                <p class="empty-state-desc">Generate immutable tax invoices directly from confirmed reservation manifests.</p>
                <a href="<?= url('/office/bookings') ?>" class="btn btn-primary btn-sm">Go to Bookings</a>
            </div>
        <?php else: ?>
            <!-- Desktop Data Grid -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Booking Ref</th>
                            <th>Client Name</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Balance</th>
                            <th>Payment Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                            <?php 
                                $balance = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                            ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><a href="<?= url('/office/invoices/' . $inv['id']) ?>"><?= e($inv['invoice_number']) ?></a></strong>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);">Issued: <?= format_date($inv['issued_at']) ?></div>
                                </td>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>"><?= e($inv['booking_code']) ?></a></strong>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--vg-navy-900);"><?= e($inv['customer_name']) ?></div>
                                    <div style="font-size:0.75rem; color:var(--vg-text-muted);"><?= e($inv['customer_email']) ?></div>
                                </td>
                                <td><strong style="color:var(--vg-navy-900);"><?= format_currency((float)$inv['total_amount']) ?></strong></td>
                                <td style="color:var(--vg-success); font-weight:600;"><?= format_currency((float)$inv['paid_amount']) ?></td>
                                <td style="color:<?= $balance > 0 ? 'var(--vg-danger)' : 'var(--vg-text-muted)' ?>; font-weight:700;">
                                    <?= format_currency($balance) ?>
                                </td>
                                <td>
                                    <?= render_status_badge($inv['payment_status']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display:inline-flex; gap:0.5rem; justify-content:flex-end;">
                                        <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="btn btn-outline btn-sm">Inspect</a>
                                        <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="btn btn-navy btn-sm" title="Print Tax Invoice">Print</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($invoices as $inv): ?>
                    <?php 
                        $balance = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                    ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($inv['invoice_number']) ?></span>
                                <div style="font-size:0.72rem; color:var(--vg-text-muted);">Ref: <?= e($inv['booking_code']) ?></div>
                            </div>
                            <div>
                                <?= render_status_badge($inv['payment_status']) ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Client</span>
                                <span class="vg-card-value"><?= e($inv['customer_name']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Total Invoiced</span>
                                <span class="vg-card-value" style="color:var(--vg-navy-900);"><?= format_currency((float)$inv['total_amount']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Paid Amount</span>
                                <span class="vg-card-value" style="color:var(--vg-success);"><?= format_currency((float)$inv['paid_amount']) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Balance Due</span>
                                <span class="vg-card-value" style="color:<?= $balance > 0 ? 'var(--vg-danger)' : 'var(--vg-text-muted)' ?>;"><?= format_currency($balance) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="btn btn-outline btn-sm">Inspect</a>
                            <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="btn btn-navy btn-sm">Print</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
