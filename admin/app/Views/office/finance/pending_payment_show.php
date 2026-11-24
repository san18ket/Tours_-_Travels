<?php
/**
 * DEDICATED CUSTOMER PENDING PAYMENT & RECEIVABLE DETAILS
 * Canonical Office Design System
 */
$isOverdue = !empty($invoice['due_date']) && strtotime($invoice['due_date']) < strtotime(date('Y-m-d'));
$status = strtolower($invoice['payment_status'] ?? 'unpaid');
$statusLabel = match($status) {
    'paid' => 'Paid',
    'partially_paid' => 'Partially Paid',
    default => 'Unpaid'
};

$headerActions = '<div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">'
    . '<a href="' . url('/office/finance/pending-payments') . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> <span>Back to Dues</span></a>'
    . '<a href="' . url('/office/invoices/' . $invoice['id']) . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-invoice"></i> <span>Tax Invoice</span></a>';

if ($remainingDue > 0.01) {
    $headerActions .= '<a href="' . url('/office/finance/pending-payments/' . $invoice['id'] . '/pay') . '" class="btn btn-primary btn-sm" style="background:#218765; border-color:#218765;"><i class="fa-solid fa-hand-holding-dollar"></i> <span>Record Payment</span></a>';
}
$headerActions .= '</div>';
?>

<div class="bookings-page-view finance-page-view" id="pendingPaymentShowModule">
    <!-- 1. Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Pending Receivable: ' . e($invoice['invoice_number']),
            'description' => 'Customer account receivable settlement ledger, linked trip context, and payment disbursement tracking.',
            'badge'       => $statusLabel,
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Pending Payments', 'url' => url('/office/finance/pending-payments')],
                ['label' => e($invoice['invoice_number']), 'url' => ''],
            ],
            'actions'     => $headerActions,
        ]) ?>
    </div>

    <!-- 2. Financial Summary Cards -->
    <div class="booking-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem;">
        <!-- Card 1: Total Invoice Amount -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Invoice Total</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--vg-navy-900);"><?= format_inr((float)$invoice['total_amount']) ?></div>
            <div class="booking-kpi-sub">
                <span>Total billed customer fare</span>
            </div>
        </div>

        <!-- Card 2: Received Amount -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Already Received</span>
                <span class="booking-kpi-icon icon-teal" style="background: rgba(33, 135, 101, 0.12); color: #218765;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr((float)$invoice['paid_amount']) ?></div>
            <div class="booking-kpi-sub">
                <span><?= count($payments) ?> payment installment(s)</span>
            </div>
        </div>

        <!-- Card 3: Remaining Due (Hero Outflow) -->
        <div class="booking-kpi-card is-selected" style="border-color: rgba(199, 138, 53, 0.4);">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label" style="color: #855314; font-weight: 700;">Remaining Balance Due</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35; font-size: 1.55rem; font-weight: 800;"><?= format_inr($remainingDue) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background: #C78A35;"></span>
                <span><?= $remainingDue > 0.01 ? 'Outstanding receivable' : 'Fully settled' ?></span>
            </div>
        </div>

        <!-- Card 4: Due Date -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Settlement Due Date</span>
                <span class="booking-kpi-icon <?= $isOverdue ? 'icon-rose' : 'icon-teal' ?>" style="<?= $isOverdue ? 'background: rgba(217, 87, 87, 0.12); color: #D95757;' : '' ?>">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $isOverdue ? '#D95757' : 'var(--vg-navy-900)' ?>; font-size: 1.15rem;">
                <?= !empty($invoice['due_date']) ? format_date($invoice['due_date']) : 'On Arrival' ?>
            </div>
            <div class="booking-kpi-sub">
                <?php if ($isOverdue): ?>
                    <span style="color: #D95757; font-weight: 700;">Overdue settlement window</span>
                <?php else: ?>
                    <span>Issued <?= format_date($invoice['issued_at'] ?? $invoice['created_at']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 3. Details Two-Column Grid: Customer Profile & Trip Booking Context -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
        <!-- Left: Customer Information Card -->
        <div style="background: #ffffff; border-radius: 14px; border: 1px solid rgba(22, 68, 59, 0.08); padding: 1.25rem 1.5rem; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 0.65rem;">
                <h4 style="margin: 0; font-size: 0.98rem; font-weight: 700; color: var(--vg-navy-900);">
                    <i class="fa-solid fa-user" style="color: var(--brand-primary); margin-right: 0.4rem;"></i>
                    Customer Profile
                </h4>
                <?php if (!empty($invoice['customer_profile_id'])): ?>
                    <a href="<?= url('/office/customers/' . $invoice['customer_profile_id']) ?>" style="font-size: 0.78rem; font-weight: 600; color: var(--brand-primary); text-decoration: none;">
                        View Profile <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.88rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Customer Name:</span>
                    <strong style="color: var(--vg-navy-900);"><?= e($invoice['customer_name'] ?? 'Direct Client') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Customer Identifier:</span>
                    <code><?= e($invoice['customer_code'] ?? ($customer['customer_code'] ?? 'CUST-' . ($invoice['customer_profile_id'] ?: $invoice['customer_id']))) ?></code>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Phone Number:</span>
                    <span><?= !empty($invoice['customer_phone']) ? e($invoice['customer_phone']) : '—' ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Email Address:</span>
                    <span><?= !empty($invoice['customer_email']) ? e($invoice['customer_email']) : '—' ?></span>
                </div>
                <?php if (!empty($customer['address']) || !empty($customer['city'])): ?>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Location / City:</span>
                    <span><?= e(trim(($customer['address'] ?? '') . ', ' . ($customer['city'] ?? ''), ', ')) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Linked Trip & Booking Information -->
        <div style="background: #ffffff; border-radius: 14px; border: 1px solid rgba(22, 68, 59, 0.08); padding: 1.25rem 1.5rem; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 0.65rem;">
                <h4 style="margin: 0; font-size: 0.98rem; font-weight: 700; color: var(--vg-navy-900);">
                    <i class="fa-solid fa-route" style="color: var(--brand-primary); margin-right: 0.4rem;"></i>
                    Travel &amp; Booking Context
                </h4>
                <?php if (!empty($invoice['booking_id'])): ?>
                    <a href="<?= url('/office/bookings/' . $invoice['booking_id']) ?>" style="font-size: 0.78rem; font-weight: 600; color: var(--brand-primary); text-decoration: none;">
                        Booking Details <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.88rem;">
                <?php if (!empty($invoice['booking_code']) || !empty($booking['booking_code'])): ?>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Booking Reference:</span>
                    <strong style="color: var(--brand-primary); font-family: monospace; font-size: 0.92rem;">
                        <?= e($invoice['booking_code'] ?? $booking['booking_code']) ?>
                    </strong>
                </div>
                <?php endif; ?>

                <?php if (!empty($invoice['tour_title'])): ?>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Package / Tour:</span>
                    <strong><?= e($invoice['tour_title']) ?></strong>
                </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Pickup Route:</span>
                    <span><?= !empty($invoice['pickup_address']) ? e($invoice['pickup_address']) : (!empty($booking['pickup_address']) ? e($booking['pickup_address']) : 'Direct Booking') ?></span>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Destination / Drop:</span>
                    <span><?= !empty($invoice['drop_address']) ? e($invoice['drop_address']) : (!empty($booking['drop_address']) ? e($booking['drop_address']) : '—') ?></span>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Trip Timeline:</span>
                    <span>
                        <?= !empty($invoice['start_date']) ? format_date($invoice['start_date']) : (!empty($booking['start_date']) ? format_date($booking['start_date']) : '—') ?>
                        <?= !empty($booking['pickup_time']) ? ' at ' . e($booking['pickup_time']) : '' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Payment History & Installment Ledger -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Received Payment History (<?= count($payments) ?> Record<?= count($payments) === 1 ? '' : 's' ?>)
            </h3>
            <?php if ($remainingDue > 0.01): ?>
                <a href="<?= url('/office/finance/pending-payments/' . $invoice['id'] . '/pay') ?>" class="btn btn-primary btn-sm" style="background: #218765; border-color: #218765;">
                    <i class="fa-solid fa-hand-holding-dollar"></i> <span>Record New Payment</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center col-priority-1" style="width: 45px;">#</th>
                        <th class="col-priority-1" style="width: 140px;">Payment Ref</th>
                        <th class="col-priority-1" style="width: 140px;">Received At</th>
                        <th class="col-priority-2" data-column-priority="secondary">Mode / Channel</th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional">Deposited Account</th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional">Transaction UTR</th>
                        <th class="col-priority-1" style="text-align: right; width: 140px;">Amount Received</th>
                        <th class="text-center col-priority-1" style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-solid fa-clock" style="color: #C78A35;"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No Installments Received Yet</h4>
                                <p class="text-muted" style="margin:0 0 1rem 0;">Zero advance payments have been posted against this invoice balance.</p>
                                <a href="<?= url('/office/finance/pending-payments/' . $invoice['id'] . '/pay') ?>" class="btn btn-primary btn-sm" style="background: #218765; border-color: #218765;">
                                    <i class="fa-solid fa-hand-holding-dollar"></i> <span>Record Settlement Payment</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($payments as $idx => $p): 
                        $pmStatus = strtolower($p['status'] ?? 'success');
                    ?>
                    <tr>
                        <td class="text-center col-priority-1"><?= $idx + 1 ?></td>
                        <td class="col-priority-1">
                            <strong style="color: var(--brand-primary); font-family: monospace; font-size: 0.85rem;"><?= e($p['payment_reference']) ?></strong>
                        </td>
                        <td class="col-priority-1" style="font-size: 0.84rem;">
                            <?= format_datetime($p['paid_at'] ?? $p['created_at']) ?>
                        </td>
                        <td class="col-priority-2" data-column-priority="secondary" style="font-size: 0.84rem; text-transform: uppercase;">
                            <?= e(str_replace('_', ' ', $p['payment_method'])) ?>
                        </td>
                        <td class="col-priority-3 col-optional" data-column-priority="optional" style="font-size: 0.84rem;">
                            <?= e($p['account_name'] ?? 'Vault Account') ?>
                        </td>
                        <td class="col-priority-3 col-optional" data-column-priority="optional" style="font-size: 0.82rem; font-family: monospace; color: var(--text-muted);">
                            <?= !empty($p['transaction_id']) ? e($p['transaction_id']) : '—' ?>
                        </td>
                        <td class="col-priority-1" style="text-align: right;">
                            <strong style="font-size: 0.95rem; color: #218765;">
                                <?= format_inr((float)$p['amount']) ?>
                            </strong>
                        </td>
                        <td class="text-center col-priority-1">
                            <span class="badge" style="font-size: 0.72rem; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: rgba(33, 135, 101, 0.12); color: #218765;">
                                <?= ucfirst($pmStatus) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

