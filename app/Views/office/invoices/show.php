<?php 
    $balance = max(0.0, (float)$invoice['total_amount'] - (float)$invoice['paid_amount']);
?>
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.55rem; font-weight: 800; color: var(--vg-navy-900);">Tax Invoice <?= e($invoice['invoice_number']) ?></h1>
            <?= render_status_badge($invoice['payment_status']) ?>
        </div>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Issued on <?= format_datetime($invoice['issued_at']) ?></p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/invoices') ?>" class="btn btn-outline btn-sm">Back to Ledger</a>
        <a href="<?= url('/office/invoices/' . $invoice['id'] . '/print') ?>" target="_blank" class="btn btn-navy btn-sm">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Print Official Invoice</span>
        </a>
    </div>
</div>

<!-- Immutability Security Banner -->
<div style="background: var(--vg-gold-50); border: 1px solid var(--vg-gold-400); border-radius: var(--vg-radius-md); padding: 0.95rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.88rem; color: var(--vg-navy-900); box-shadow: var(--vg-shadow-sm);">
    <svg width="22" height="22" fill="none" stroke="var(--vg-gold-600)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
    <span><strong>Statutory Tax Sealed:</strong> This document is immutable under accounting compliance. Financial line items are permanently finalized.</span>
</div>

<div class="vg-detail-grid">
    <!-- Left Column: Invoice Line Items & Payment History -->
    <div>
        <!-- Itemized Breakdown -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Tax Invoice Statement</span>
                <span style="font-size: 0.85rem; color: var(--vg-text-muted);">Due: <?= format_date($invoice['due_date']) ?></span>
            </div>
            <div class="card-body">
                <div class="vg-split-2" style="margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--vg-pearl-200);">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted);">Billed To (Guest)</div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900);"><?= e($invoice['customer_name']) ?></div>
                        <div style="font-size: 0.88rem; color: var(--vg-text-muted);"><?= e($invoice['customer_phone'] ?? '—') ?></div>
                        <div style="font-size: 0.88rem; color: var(--vg-text-muted);"><?= e($invoice['customer_email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted);">Reservation Associated</div>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--vg-blue-500);">
                            <a href="<?= url('/office/bookings/' . $invoice['booking_id']) ?>"><?= e($invoice['booking_code']) ?></a>
                        </div>
                        <div style="font-size: 0.88rem; color: var(--vg-navy-900); font-weight: 600;"><?= e($invoice['tour_title'] ?? 'Custom Fleet Chauffeur Service') ?></div>
                        <div style="font-size: 0.82rem; color: var(--vg-text-muted);"><?= format_date($invoice['start_date']) ?> to <?= format_date($invoice['end_date']) ?></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="text-align: right;">Amount (INR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);">Tour Transportation &amp; Fleet Service</strong>
                                    <div style="font-size: 0.8rem; color: var(--vg-text-muted);"><?= e($invoice['pickup_address']) ?> &rarr; <?= e($invoice['drop_address']) ?></div>
                                </td>
                                <td style="text-align: right; font-weight: 600; color:var(--vg-navy-900);"><?= format_currency((float)$invoice['subtotal']) ?></td>
                            </tr>
                            <?php if ((float)$invoice['discount_amount'] > 0): ?>
                            <tr>
                                <td style="color: var(--vg-success);">Special Promotional Discount Applied</td>
                                <td style="text-align: right; color: var(--vg-success); font-weight: 600;">- <?= format_currency((float)$invoice['discount_amount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>Goods &amp; Services Tax (GST @ <?= e($invoice['tax_rate']) ?>%)</td>
                                <td style="text-align: right; font-weight: 600; color:var(--vg-navy-900);"><?= format_currency((float)$invoice['tax_amount']) ?></td>
                            </tr>
                            <tr style="background: var(--vg-pearl-50);">
                                <td><strong style="font-size: 1.15rem; color: var(--vg-navy-900);">Final Invoiced Total</strong></td>
                                <td style="text-align: right;"><strong style="font-size: 1.25rem; color: var(--vg-blue-500);"><?= format_currency((float)$invoice['total_amount']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recorded Payments Ledger -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Recorded Payments (<?= count($payments) ?>)</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($payments)): ?>
                    <div style="text-align: center; padding: 2.5rem; color: var(--vg-text-muted);">No payments recorded for this invoice yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payment Ref</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Transaction ID</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><code><?= e($p['payment_reference']) ?></code></td>
                                        <td><span class="badge badge-neutral"><?= strtoupper(e($p['payment_method'])) ?></span></td>
                                        <td><strong style="color:var(--vg-success);"><?= format_currency((float)$p['amount']) ?></strong></td>
                                        <td><code><?= e($p['transaction_id'] ?? '—') ?></code></td>
                                        <td><?= format_datetime($p['paid_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Balance Summary & Payment Processing Form -->
    <div>
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Account Summary</span>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Total Invoiced:</span>
                    <strong style="color: var(--vg-navy-900);"><?= format_currency((float)$invoice['total_amount']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                    <span style="color: var(--vg-text-muted);">Paid to Date:</span>
                    <strong style="color: var(--vg-success);"><?= format_currency((float)$invoice['paid_amount']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.85rem; border-top: 2px solid var(--vg-pearl-200); font-size: 1.2rem; font-weight: 800; color: var(--vg-navy-900); margin-top: 0.5rem;">
                    <span>Outstanding Due:</span>
                    <span style="color: <?= $balance > 0 ? 'var(--vg-danger)' : 'var(--vg-success)' ?>;">
                        <?= format_currency($balance) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($balance > 0 && in_array(auth_role(), ['admin', 'dispatcher'], true)): ?>
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Allocate Payment</span>
            </div>
            <div class="card-body">
                <form action="<?= url('/office/invoices/' . $invoice['id'] . '/payment') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="idempotency_key" value="<?= bin2hex(random_bytes(16)) ?>">

                    <div class="form-group">
                        <label class="form-label" for="amount">Payment Amount (INR) *</label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="1" max="<?= $balance ?>" value="<?= $balance ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            <option value="upi">UPI / QR Code</option>
                            <option value="net_banking">Net Banking / NEFT</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cash">Cash Settlement</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="transaction_id">Transaction / UTR Number</label>
                        <input type="text" id="transaction_id" name="transaction_id" class="form-control" placeholder="e.g. UPI-20261019-9831">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Internal Remarks</label>
                        <input type="text" id="notes" name="notes" class="form-control" placeholder="Optional allocation memo">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Record Payment
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
