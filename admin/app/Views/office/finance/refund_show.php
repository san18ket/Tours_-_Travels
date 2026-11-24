<?php
/**
 * Dedicated Customer Refund Details Screen
 * Executive Finance Management Module
 */

$st = strtolower($refund['status'] ?? 'pending');
$isProcessed = ($st === 'processed');
$isPending   = ($st === 'pending');
$isApproved  = ($st === 'approved');
$isRejected  = ($st === 'rejected');
$canEdit     = ($isPending || $isApproved);
$canProcess  = ($isPending || $isApproved);

$invoiceTotal = (float)($refund['invoice_total'] ?? 0);
$invoicePaid  = (float)($refund['invoice_paid'] ?? 0);
$refundableAmount = max(0.0, $invoicePaid - ($totalRefunded ?? 0.0));
$currentUrl = url('/office/finance/refunds/' . (int)$refund['id']);

$actionsHtml = '<div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">'
    . '<a href="' . url('/office/finance/refunds') . '" class="btn btn-outline btn-sm" data-no-ajax="true">&larr; Back to Refunds</a>';

if ($canEdit) {
    $actionsHtml .= '<button type="button" class="btn btn-outline btn-sm" onclick="openEditModal()"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Claim</span></button>';
}
if ($canProcess) {
    $actionsHtml .= '<button type="button" class="btn btn-primary btn-sm" onclick="openProcessModal()" style="background:#218765; border-color:#218765;"><i class="fa-solid fa-hand-holding-dollar"></i> <span>Process Refund</span></button>';
    $actionsHtml .= '<button type="button" class="btn btn-outline btn-sm" onclick="openRejectModal()" style="color:#DC2626; border-color:#FECDD3;"><i class="fa-solid fa-ban"></i> <span>Reject</span></button>';
}

$actionsHtml .= '</div>';

$statusBadge = match($st) {
    'processed' => '<span class="status-pill status-pill-processed"><i class="fa-solid fa-circle-check"></i> Processed</span>',
    'approved'  => '<span class="status-pill status-pill-approved"><i class="fa-solid fa-thumbs-up"></i> Approved</span>',
    'rejected'  => '<span class="status-pill status-pill-rejected"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>',
    default     => '<span class="status-pill status-pill-pending"><i class="fa-solid fa-clock"></i> Pending Claim</span>',
};
?>

<?= render_page_header([
    'title'       => 'Customer Refund ' . e($refund['refund_number']),
    'subtitle'    => 'Claim Date: ' . format_date($refund['refund_date']) . ' • Customer: ' . e($refund['customer_name'] ?? 'Direct Client'),
    'breadcrumbs' => [
        ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
        ['label' => 'Finance', 'url' => url('/office/finance/dashboard')],
        ['label' => 'Refunds', 'url' => url('/office/finance/refunds')],
        ['label' => e($refund['refund_number']), 'url' => ''],
    ],
    'actions'     => $actionsHtml,
    'badge'       => ucfirst($st),
]) ?>

<!-- Top Refund Summary Ribbon -->
<div class="refund-summary-ribbon" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; padding: 1.25rem 1.5rem; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
    <div>
        <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">Refund Number</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #0C2B52; font-family: monospace;"><?= e($refund['refund_number']) ?></div>
        <div style="font-size: 0.78rem; color: #64748B;">Registered Reference</div>
    </div>
    <div>
        <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">Refund Amount</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #D95757;"><?= format_inr($refund['amount']) ?></div>
        <div style="font-size: 0.78rem; color: #64748B;">Claimed repayment</div>
    </div>
    <div>
        <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">Workflow Status</div>
        <div style="margin-top: 4px;"><?= $statusBadge ?></div>
        <div style="font-size: 0.78rem; color: #64748B; margin-top: 2px;">
            <?= $isProcessed ? 'Paid & debited from treasury' : ($isRejected ? 'Claim rejected' : 'Claim only, no money paid') ?>
        </div>
    </div>
    <div>
        <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">Refund Method</div>
        <div style="font-size: 1.05rem; font-weight: 700; color: #1E293B; margin-top: 4px; text-transform: uppercase;">
            <i class="fa-solid fa-credit-card" style="margin-right: 4px; color: #64748B;"></i>
            <?= e(str_replace('_', ' ', $refund['refund_method'])) ?>
        </div>
        <div style="font-size: 0.78rem; color: #64748B;">Disbursement channel</div>
    </div>
    <?php if (!empty($refund['gateway_refund_id'])): ?>
    <div>
        <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">Gateway Refund ID</div>
        <div style="font-size: 1.05rem; font-weight: 700; color: #2563EB; font-family: monospace; margin-top: 4px;">
            <?= e($refund['gateway_refund_id']) ?>
        </div>
        <div style="font-size: 0.78rem; color: #64748B;">Settlement Tracking ID</div>
    </div>
    <?php endif; ?>
</div>

<!-- Main Details Layout Grid -->
<div class="refund-detail-grid" style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 1.5rem; margin-bottom: 2rem;">

    <!-- Left Main Column: Refund Details, Invoice & Payments, Booking -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- 1. Processing / Settlement Status Card -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-hand-holding-dollar" style="color: <?= $isProcessed ? '#218765' : '#C78A35' ?>;"></i>
                    Processing &amp; Settlement Information
                </span>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748B;">
                    <?= $isProcessed ? 'Settled Disbursement' : 'Pending Real Outflow' ?>
                </span>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <?php if ($isProcessed): ?>
                    <div style="background: rgba(33, 135, 101, 0.08); border: 1px solid rgba(33, 135, 101, 0.25); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.85rem;">
                        <i class="fa-solid fa-circle-check" style="font-size: 1.4rem; color: #218765; margin-top: 2px;"></i>
                        <div>
                            <strong style="color: #166534; font-size: 0.95rem; display: block; margin-bottom: 2px;">Customer Repayment Successfully Processed</strong>
                            <p style="margin: 0; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                                A genuine financial outflow was recorded and debited from company accounts. Transaction reference and ledger entries are permanently sealed for audit compliance.
                            </p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem 1.25rem;">
                        <div>
                            <span class="detail-label">Processed Amount</span>
                            <span class="detail-value" style="color: #D95757; font-size: 1.15rem; font-weight: 800;"><?= format_inr($refund['amount']) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Settlement Date</span>
                            <span class="detail-value"><?= !empty($refund['processed_at']) ? format_datetime($refund['processed_at']) : format_date($refund['refund_date']) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Processed By</span>
                            <span class="detail-value"><?= e($refund['processed_by_name'] ?: 'Finance Administrator') ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Payment Account</span>
                            <span class="detail-value">
                                <?= e($refund['processed_account_name'] ?: 'Primary Account') ?>
                                <?php if (!empty($refund['processed_account_type'])): ?>
                                    <span style="font-size: 0.72rem; color: #64748B; text-transform: uppercase;">(<?= e($refund['processed_account_type']) ?>)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if (!empty($refund['processed_account_number'])): ?>
                        <div>
                            <span class="detail-label">Account Number</span>
                            <span class="detail-value" style="font-family: monospace; font-weight: 700; color: #0C2B52;">
                                <?php
                                    $rawAcc = trim((string)$refund['processed_account_number']);
                                    echo e(strlen($rawAcc) >= 4 ? ('••••••••' . substr($rawAcc, -4)) : $rawAcc);
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <span class="detail-label">Disbursement Method</span>
                            <span class="detail-value" style="text-transform: uppercase;"><?= e(str_replace('_', ' ', $refund['refund_method'])) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Transaction / UTR Reference</span>
                            <span class="detail-value" style="font-family: monospace; font-weight: 700; color: #0C2B52;">
                                <?= e($refund['processed_tx_ref'] ?: 'N/A') ?>
                            </span>
                        </div>
                        <?php if (!empty($refund['gateway_refund_id'])): ?>
                        <div>
                            <span class="detail-label">Gateway Refund ID</span>
                            <span class="detail-value" style="font-family: monospace; font-weight: 700; color: #2563EB;">
                                <?= e($refund['gateway_refund_id']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($refund['ledger_id'])): ?>
                        <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px dashed #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <div>
                                <span class="detail-label">Financial Ledger Transaction</span>
                                <span class="detail-value" style="margin-top: 2px;">
                                    <a href="<?= url('/office/finance/transactions/' . (int)$refund['ledger_id']) ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>View Finance Ledger Entry #<?= (int)$refund['ledger_id'] ?> &rarr;</span>
                                    </a>
                                </span>
                            </div>
                            <div>
                                <span class="detail-label">Ledger Outflow Amount</span>
                                <span class="detail-value" style="font-weight: 800; color: #D95757; font-size: 1.1rem;">
                                    <?= format_inr($refund['processed_ledger_amount'] ?? $refund['amount']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($isRejected): ?>
                    <div style="background: rgba(217, 87, 87, 0.08); border: 1px solid rgba(217, 87, 87, 0.25); border-radius: 12px; padding: 1rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; color: #991B1B; font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>Refund Claim Rejected</span>
                        </div>
                        <p style="margin: 0; font-size: 0.84rem; color: #475569;">
                            This claim was rejected by finance administration. No cash or bank outflow was executed.
                        </p>
                        <?php if (!empty($refund['notes'])): ?>
                            <div style="margin-top: 8px; font-size: 0.82rem; color: #334155; background: #fff; padding: 8px 12px; border-radius: 8px; border: 1px solid #FECDD3;">
                                <strong>Rejection Notes:</strong> <?= nl2br(e($refund['notes'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: 12px; padding: 1.15rem 1.25rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem; max-width: 600px;">
                            <i class="fa-solid fa-circle-exclamation" style="font-size: 1.4rem; color: #D97706; margin-top: 2px;"></i>
                            <div>
                                <strong style="color: #92400E; font-size: 0.95rem; display: block; margin-bottom: 2px;">Payment Not Yet Processed</strong>
                                <p style="margin: 0; font-size: 0.84rem; color: #78350F; line-height: 1.4;">
                                    This record is currently a <strong>pending claim only</strong>. No funds have been deducted from cash or bank accounts. To complete this payout, click <strong>Process Refund</strong> below.
                                </p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button type="button" class="btn btn-primary btn-sm" onclick="openProcessModal()" style="background: #218765; border-color: #218765; padding: 8px 14px;">
                                <i class="fa-solid fa-hand-holding-dollar"></i> <span>Process Refund Payment</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Refund Claim Details Card -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-file-invoice" style="color: var(--brand-primary, #176B5B);"></i>
                    Refund Claim Specifications
                </span>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openEditModal()" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Claim
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <span class="detail-label">Refund Amount</span>
                        <span class="detail-value" style="font-size: 1.15rem; color: #D95757; font-weight: 800;"><?= format_inr($refund['amount']) ?></span>
                    </div>
                    <div>
                        <span class="detail-label">Claim Date</span>
                        <span class="detail-value"><?= format_date($refund['refund_date']) ?></span>
                    </div>
                    <div>
                        <span class="detail-label">Preferred Refund Method</span>
                        <span class="detail-value" style="text-transform: uppercase;"><?= e(str_replace('_', ' ', $refund['refund_method'])) ?></span>
                    </div>
                    <div>
                        <span class="detail-label">Created At</span>
                        <span class="detail-value"><?= !empty($refund['created_at']) && $refund['created_at'] !== '0000-00-00 00:00:00' ? format_datetime($refund['created_at']) : format_date($refund['refund_date']) ?></span>
                    </div>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 1rem; margin-bottom: 1rem;">
                    <span class="detail-label">Refund Reason</span>
                    <div style="font-size: 0.9rem; color: #1E293B; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 4px; font-weight: 500;">
                        <?= e($refund['reason']) ?>
                    </div>
                </div>

                <?php if (!empty($refund['notes'])): ?>
                <div style="border-top: 1px solid #F1F5F9; padding-top: 1rem;">
                    <span class="detail-label">Administrative Notes</span>
                    <div style="font-size: 0.85rem; color: #475569; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 4px;">
                        <?= nl2br(e($refund['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Invoice & Payment Accounting Information -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-receipt" style="color: #2563EB;"></i>
                    Invoice &amp; Payment Ledger Information
                </span>
                <?php if (!empty($refund['invoice_id'])): ?>
                    <a href="<?= url('/office/invoices/' . (int)$refund['invoice_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                        View Invoice &rarr;
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <?php if (!empty($refund['invoice_id'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem 1.25rem; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid #F1F5F9;">
                        <div>
                            <span class="detail-label">Invoice Number</span>
                            <span class="detail-value" style="font-weight: 700; color: #2563EB;">
                                <a href="<?= url('/office/invoices/' . (int)$refund['invoice_id']) ?>" data-no-ajax="true"><?= e($refund['invoice_number']) ?></a>
                            </span>
                        </div>
                        <div>
                            <span class="detail-label">Invoice Issued</span>
                            <span class="detail-value"><?= !empty($refund['invoice_issued_at']) ? format_datetime($refund['invoice_issued_at']) : '—' ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Subtotal</span>
                            <span class="detail-value"><?= format_inr($refund['invoice_subtotal'] ?? 0) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Tax</span>
                            <span class="detail-value"><?= format_inr($refund['invoice_tax'] ?? 0) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Discount</span>
                            <span class="detail-value"><?= format_inr($refund['invoice_discount'] ?? 0) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Invoice Total</span>
                            <span class="detail-value" style="font-weight: 700;"><?= format_inr($invoiceTotal) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Amount Paid</span>
                            <span class="detail-value" style="font-weight: 700; color: #10B981;"><?= format_inr($invoicePaid) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Total Refunded</span>
                            <span class="detail-value" style="font-weight: 700; color: #D95757;"><?= format_inr($totalRefunded) ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Refundable Balance</span>
                            <span class="detail-value" style="font-weight: 800; color: <?= $refundableAmount > 0 ? '#10B981' : '#64748B' ?>;">
                                <?= format_inr($refundableAmount) ?>
                            </span>
                        </div>
                        <div>
                            <span class="detail-label">Payment Status</span>
                            <span class="detail-value" style="margin-top: 2px;">
                                <span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                                    <?= e(str_replace('_', ' ', $refund['invoice_payment_status'] ?? 'unpaid')) ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Related Customer Payments on this Invoice -->
                    <div style="margin-top: 1rem;">
                        <span class="detail-label" style="margin-bottom: 0.5rem; display: block;">Recorded Customer Payments for this Invoice:</span>
                        <?php if (empty($relatedPayments)): ?>
                            <div style="font-size: 0.82rem; color: #64748B; background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 0.75rem; border-radius: 8px; text-align: center;">
                                No discrete payment receipts linked to this invoice.
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="modern-table" style="font-size: 0.82rem; margin: 0;">
                                    <thead>
                                        <tr style="background: #F8FAFC;">
                                            <th style="padding: 6px 10px;">Payment Ref</th>
                                            <th style="padding: 6px 10px;">Date</th>
                                            <th style="padding: 6px 10px;">Method</th>
                                            <th style="padding: 6px 10px; text-align: right;">Amount</th>
                                            <th style="padding: 6px 10px; text-align: center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($relatedPayments as $pm): ?>
                                        <tr>
                                            <td style="padding: 6px 10px; font-family: monospace; font-weight: 600;"><?= e($pm['payment_reference'] ?: ('PAY#' . $pm['id'])) ?></td>
                                            <td style="padding: 6px 10px;"><?= format_date($pm['paid_at'] ?? $pm['created_at']) ?></td>
                                            <td style="padding: 6px 10px; text-transform: uppercase;"><?= e(str_replace('_', ' ', $pm['payment_method'])) ?></td>
                                            <td style="padding: 6px 10px; text-align: right; font-weight: 700; color: #10B981;"><?= format_inr($pm['amount']) ?></td>
                                            <td style="padding: 6px 10px; text-align: center;">
                                                <span class="badge" style="background: #D1FAE5; color: #065F46; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                                    <?= e($pm['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="color: #64748B; font-size: 0.88rem; padding: 0.75rem 0;">
                        No tax invoice was directly attached to this refund claim.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Booking Information Card -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-calendar-check" style="color: #0D9488;"></i>
                    Associated Booking &amp; Trip Details
                </span>
                <?php if (!empty($refund['booking_db_id'])): ?>
                    <a href="<?= url('/office/bookings/' . (int)$refund['booking_db_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;" data-no-ajax="true">
                        View Booking &rarr;
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <?php if (!empty($refund['booking_db_id']) || !empty($refund['booking_code'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem 1.25rem;">
                        <div>
                            <span class="detail-label">Booking Reference</span>
                            <span class="detail-value" style="font-weight: 700;">
                                <?php if (!empty($refund['booking_db_id'])): ?>
                                    <a href="<?= url('/office/bookings/' . (int)$refund['booking_db_id']) ?>" style="color: #0D9488; font-weight: 800;">
                                        <?= e($refund['booking_code']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= e($refund['booking_code']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div>
                            <span class="detail-label">Booking Status</span>
                            <span class="detail-value" style="margin-top: 2px;">
                                <span class="badge" style="background: #E6FFFA; color: #0D9488; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                                    <?= e($refund['booking_status'] ?? 'N/A') ?>
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="detail-label">Tour / Package</span>
                            <span class="detail-value"><?= e($refund['tour_title'] ?: 'Custom Driver Fleet Service') ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Travel Dates</span>
                            <span class="detail-value">
                                <?php if (!empty($refund['booking_start_date'])): ?>
                                    <?= format_date($refund['booking_start_date']) ?>
                                    <?php if (!empty($refund['booking_end_date']) && $refund['booking_end_date'] !== $refund['booking_start_date']): ?>
                                        to <?= format_date($refund['booking_end_date']) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </span>
                        </div>
                        <div>
                            <span class="detail-label">Pickup Location</span>
                            <span class="detail-value"><?= e($refund['pickup_address'] ?: '—') ?></span>
                        </div>
                        <div>
                            <span class="detail-label">Drop Location</span>
                            <span class="detail-value"><?= e($refund['drop_address'] ?: '—') ?></span>
                        </div>
                        <?php if (!empty($refund['passenger_count'])): ?>
                        <div>
                            <span class="detail-label">Passenger Count</span>
                            <span class="detail-value"><?= (int)$refund['passenger_count'] ?> Passengers</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($refund['driver_name']) || !empty($refund['vehicle_number'])): ?>
                        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed #E2E8F0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <span class="detail-label">Assigned Vehicle</span>
                                <span class="detail-value">
                                    <i class="fa-solid fa-van-shuttle" style="color: #64748B; margin-right: 4px;"></i>
                                    <?= e($refund['vehicle_number']) ?>
                                    <?php if (!empty($refund['vehicle_model'])): ?> (<?= e($refund['vehicle_model']) ?>)<?php endif; ?>
                                </span>
                            </div>
                            <div>
                                <span class="detail-label">Assigned Driver</span>
                                <span class="detail-value">
                                    <i class="fa-solid fa-id-badge" style="color: #64748B; margin-right: 4px;"></i>
                                    <?= e($refund['driver_name']) ?>
                                    <?php if (!empty($refund['driver_phone'])): ?> (<?= e($refund['driver_phone']) ?>)<?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="color: #64748B; font-size: 0.88rem; padding: 0.75rem 0;">
                        Direct refund claim without an active reservation reference.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Sidebar Column: Customer Info & Timeline -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- Customer Information Card -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-tie" style="color: var(--brand-primary, #176B5B);"></i>
                    Customer Profile
                </span>
                <?php if (!empty($refund['customer_id_val'])): ?>
                    <a href="<?= url('/office/customers/' . (int)$refund['customer_id_val']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem;" data-no-ajax="true">
                        Profile &rarr;
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <div style="margin-bottom: 1rem;">
                    <div style="font-size: 1.15rem; font-weight: 800; color: #0C2B52;"><?= e($refund['customer_name'] ?? 'Direct Client') ?></div>
                    <?php if (!empty($refund['customer_code'])): ?>
                        <div style="font-size: 0.78rem; color: #64748B; font-family: monospace; font-weight: 600;">ID: <?= e($refund['customer_code']) ?></div>
                    <?php elseif (!empty($refund['customer_id_val'])): ?>
                        <div style="font-size: 0.78rem; color: #64748B;">Client #<?= (int)$refund['customer_id_val'] ?></div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.85rem;">
                    <div>
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">
                            <?php if (!empty($refund['customer_phone'])): ?>
                                <a href="tel:<?= e($refund['customer_phone']) ?>" style="color: #0C2B52;"><?= e($refund['customer_phone']) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>

                    <div>
                        <span class="detail-label">Email</span>
                        <span class="detail-value">
                            <?php if (!empty($refund['customer_email'])): ?>
                                <a href="mailto:<?= e($refund['customer_email']) ?>" style="color: #0C2B52;"><?= e($refund['customer_email']) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>

                    <div>
                        <span class="detail-label">Billing / Contact Address</span>
                        <span class="detail-value" style="line-height: 1.4;">
                            <?php 
                                $addressParts = array_filter([
                                    $refund['address_line'] ?? '',
                                    $refund['city'] ?? '',
                                    $refund['state'] ?? '',
                                    $refund['postal_code'] ?? '',
                                    $refund['country'] ?? ''
                                ]);
                                echo !empty($addressParts) ? e(implode(', ', $addressParts)) : '—';
                            ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($refund['customer_id_val'])): ?>
                <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid #F1F5F9;">
                    <a href="<?= url('/office/customers/' . (int)$refund['customer_id_val']) ?>" class="btn btn-outline btn-sm" style="width: 100%; text-align: center; justify-content: center;" data-no-ajax="true">
                        <i class="fa-solid fa-address-card"></i> <span>Open Full Customer File</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Audit & Workflow Timeline Card -->
        <div class="vg-card" style="background: #FFFFFF; border: 1.5px solid #DCE6F2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(12, 43, 82, 0.04);">
            <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #E2E8F0; background: #F8FAFC;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-timeline" style="color: #6366F1;"></i>
                    Audit Timeline
                </span>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <div class="audit-timeline">
                    <!-- Step 1: Claim Created -->
                    <div class="timeline-item is-done">
                        <div class="timeline-dot" style="background: #10B981;"><i class="fa-solid fa-check" style="font-size: 9px; color: #fff;"></i></div>
                        <div class="timeline-content">
                            <strong style="font-size: 0.85rem; color: #0C2B52; display: block;">Refund Claim Lodged</strong>
                            <span style="font-size: 0.74rem; color: #64748B;">
                                <?= !empty($refund['created_at']) && $refund['created_at'] !== '0000-00-00 00:00:00' ? format_datetime($refund['created_at']) : format_date($refund['refund_date']) ?>
                            </span>
                            <div style="font-size: 0.78rem; color: #475569; margin-top: 2px;">
                                Amount: <?= format_inr($refund['amount']) ?> (No money moved)
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Approved / Under Review -->
                    <?php if ($isApproved || $isProcessed): ?>
                    <div class="timeline-item is-done">
                        <div class="timeline-dot" style="background: #2563EB;"><i class="fa-solid fa-thumbs-up" style="font-size: 9px; color: #fff;"></i></div>
                        <div class="timeline-content">
                            <strong style="font-size: 0.85rem; color: #0C2B52; display: block;">Claim Approved</strong>
                            <span style="font-size: 0.74rem; color: #64748B;">Approved for treasury settlement</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Step 3: Processed or Rejected or Pending -->
                    <?php if ($isProcessed): ?>
                    <div class="timeline-item is-done">
                        <div class="timeline-dot" style="background: #218765;"><i class="fa-solid fa-money-bill-transfer" style="font-size: 9px; color: #fff;"></i></div>
                        <div class="timeline-content">
                            <strong style="font-size: 0.85rem; color: #218765; display: block;">Settlement Completed</strong>
                            <span style="font-size: 0.74rem; color: #64748B;">
                                <?= !empty($refund['processed_at']) ? format_datetime($refund['processed_at']) : format_date($refund['refund_date']) ?>
                            </span>
                            <div style="font-size: 0.78rem; color: #334155; margin-top: 2px;">
                                Debited from <strong><?= e($refund['processed_account_name'] ?: 'Primary Account') ?></strong>
                                <?php if (!empty($refund['processed_by_name'])): ?> by <?= e($refund['processed_by_name']) ?><?php endif; ?>.
                            </div>
                        </div>
                    </div>
                    <?php elseif ($isRejected): ?>
                    <div class="timeline-item is-rejected">
                        <div class="timeline-dot" style="background: #DC2626;"><i class="fa-solid fa-xmark" style="font-size: 9px; color: #fff;"></i></div>
                        <div class="timeline-content">
                            <strong style="font-size: 0.85rem; color: #DC2626; display: block;">Claim Rejected</strong>
                            <span style="font-size: 0.74rem; color: #64748B;">Disbursement declined. No payout recorded.</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="timeline-item is-pending">
                        <div class="timeline-dot" style="background: #CBD5E1; border: 2px solid #94A3B8;"></div>
                        <div class="timeline-content">
                            <strong style="font-size: 0.85rem; color: #64748B; display: block;">Awaiting Disbursement</strong>
                            <span style="font-size: 0.74rem; color: #94A3B8;">Pending treasury debit &amp; customer payment</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: Process Customer Refund Payment                                   -->
<!-- ========================================================================= -->
<?php if ($canProcess): ?>
<div id="processModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'processModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 540px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(33, 135, 101, 0.12); color: #218765; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 class="modal-title-custom" style="margin: 0; font-size: 1.1rem; color: #0C2B52;">Process Customer Refund</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: #64748B;">Execute genuine cash or bank disbursement</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeProcessModal()">&times;</button>
        </div>

        <form method="POST" action="<?= url('/office/finance/refunds/' . (int)$refund['id'] . '/process') ?>" id="processRefundForm" onsubmit="return handleProcessSubmit(event)">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e($currentUrl) ?>">

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <!-- Real Outflow Warning Banner -->
                <div class="payment-outflow-warning" style="background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 12px; padding: 0.95rem 1.15rem; margin-bottom: 1.15rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #991B1B; font-weight: 700; font-size: 0.88rem; margin-bottom: 4px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Real Cash / Bank Outflow Notice</span>
                    </div>
                    <p style="margin: 0; font-size: 0.8rem; color: #7F1D1D; line-height: 1.4;">
                        This action will <strong>pay the customer and record a real cash/bank outflow</strong>. Money will be deducted from the selected finance account immediately.
                    </p>
                </div>

                <!-- Refund Reference Summary -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.15rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Claim Reference</span>
                        <div style="font-size: 0.92rem; font-weight: 700; color: #0C2B52; font-family: monospace;"><?= e($refund['refund_number']) ?></div>
                        <div style="font-size: 0.78rem; color: #64748B;"><?= e($refund['customer_name'] ?? 'Direct Client') ?></div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Payout Amount</span>
                        <div style="font-size: 1.25rem; font-weight: 800; color: #D95757;"><?= format_inr($refund['amount']) ?></div>
                    </div>
                </div>

                <!-- Account Selection -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Disbursement Account <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="account_id" id="proc_account_id" class="form-control" style="width: 100%; height: 42px; border-radius: 8px; font-size: 0.88rem;" required>
                        <option value="">-- Select Source Bank/Cash Account --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= (int)$acc['id'] ?>">
                                <?= e($acc['account_name']) ?> (<?= ucfirst($acc['account_type']) ?>) — Balance: <?= format_inr($acc['balance'] ?? 0) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Payment Method <span style="color: #DC2626;">*</span>
                        </label>
                        <select name="refund_method" id="proc_refund_method" class="form-control" style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;" required>
                            <option value="bank_transfer" <?= $refund['refund_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi" <?= $refund['refund_method'] === 'upi' ? 'selected' : '' ?>>UPI</option>
                            <option value="cash" <?= $refund['refund_method'] === 'cash' ? 'selected' : '' ?>>Cash Payment</option>
                            <option value="gateway" <?= $refund['refund_method'] === 'gateway' ? 'selected' : '' ?>>Payment Gateway</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Disbursement Date <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="date" name="refund_date" id="proc_refund_date" class="form-control" value="<?= date('Y-m-d') ?>" style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Transaction Reference / UTR Number
                    </label>
                    <input type="text" name="transaction_ref" id="proc_transaction_ref" class="form-control" placeholder="e.g. UTR12345678, CHQ#9901..." style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;">
                    <span style="font-size: 0.72rem; color: #64748B; margin-top: 2px; display: block;">Bank UTR or transfer acknowledgement identifier</span>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Settlement Notes (Optional)
                    </label>
                    <textarea name="notes" id="proc_notes" class="form-control" rows="2" placeholder="Audit memo for the ledger..." style="width: 100%; border-radius: 8px; font-size: 0.85rem;"></textarea>
                </div>
            </div>

            <div class="modal-footer-custom" style="padding: 1rem 1.25rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" class="btn btn-outline" onclick="closeProcessModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnConfirmProcess" style="background: #218765; border-color: #218765;">
                    <i class="fa-solid fa-check"></i> <span>Confirm Refund Payment</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODAL 2: Edit Refund Claim                                                -->
<!-- ========================================================================= -->
<?php if ($canEdit): ?>
<div id="editModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 520px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(37, 99, 235, 0.12); color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 class="modal-title-custom" style="margin: 0; font-size: 1.1rem; color: #0C2B52;">Edit Refund Claim</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: #64748B;">Modify claim details before final disbursement</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeEditModal()">&times;</button>
        </div>

        <form method="POST" action="<?= url('/office/finance/refunds/' . (int)$refund['id'] . '/edit') ?>" id="editRefundForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= e($currentUrl) ?>">

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Refund Amount (₹) <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control" value="<?= e($refund['amount']) ?>" required style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Claim Date <span style="color: #DC2626;">*</span>
                        </label>
                        <input type="date" name="refund_date" class="form-control" value="<?= e($refund['refund_date']) ?>" required style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Refund Method <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="refund_method" class="form-control" style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;" required>
                        <option value="bank_transfer" <?= $refund['refund_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="upi" <?= $refund['refund_method'] === 'upi' ? 'selected' : '' ?>>UPI</option>
                        <option value="cash" <?= $refund['refund_method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="gateway" <?= $refund['refund_method'] === 'gateway' ? 'selected' : '' ?>>Gateway</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Reason for Refund <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" name="reason" class="form-control" value="<?= e($refund['reason']) ?>" required style="width: 100%; height: 40px; border-radius: 8px; font-size: 0.88rem;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Notes (Optional)
                    </label>
                    <textarea name="notes" class="form-control" rows="2" style="width: 100%; border-radius: 8px; font-size: 0.85rem;"><?= e($refund['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="modal-footer-custom" style="padding: 1rem 1.25rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODAL 3: Reject Refund Claim                                              -->
<!-- ========================================================================= -->
<?php if ($canProcess): ?>
<div id="rejectModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'rejectModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(217, 87, 87, 0.12); color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h3 class="modal-title-custom" style="margin: 0; font-size: 1.1rem; color: #991B1B;">Reject Refund Claim</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: #64748B;">Declines customer refund claim</p>
                </div>
            </div>
            <button type="button" class="modal-close-custom" onclick="closeRejectModal()">&times;</button>
        </div>

        <form method="POST" action="<?= url('/office/finance/refunds/' . (int)$refund['id'] . '/status') ?>" id="rejectRefundForm">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="redirect_to" value="<?= e($currentUrl) ?>">

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1rem;">
                    Are you sure you want to reject claim <strong><?= e($refund['refund_number']) ?></strong> for <strong><?= format_inr($refund['amount']) ?></strong>?
                </p>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Rejection Reason / Notes <span style="color: #DC2626;">*</span>
                    </label>
                    <textarea name="notes" class="form-control" rows="3" required placeholder="Explain why this refund claim is being rejected..." style="width: 100%; border-radius: 8px; font-size: 0.85rem;"></textarea>
                </div>
            </div>

            <div class="modal-footer-custom" style="padding: 1rem 1.25rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #DC2626; border-color: #DC2626;">
                    <i class="fa-solid fa-ban"></i> <span>Confirm Rejection</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
.detail-label {
    display: block;
    font-size: 0.74rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}
.detail-value {
    display: block;
    font-size: 0.92rem;
    font-weight: 600;
    color: #0F172A;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.status-pill-pending {
    background: rgba(199, 138, 53, 0.12);
    color: #C78A35;
}
.status-pill-approved {
    background: rgba(37, 99, 235, 0.12);
    color: #2563EB;
}
.status-pill-processed {
    background: rgba(33, 135, 101, 0.12);
    color: #218765;
}
.status-pill-rejected {
    background: rgba(217, 87, 87, 0.12);
    color: #D95757;
}

.audit-timeline {
    position: relative;
    padding-left: 20px;
}
.audit-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: #E2E8F0;
}
.timeline-item {
    position: relative;
    margin-bottom: 1.25rem;
}
.timeline-item:last-child {
    margin-bottom: 0;
}
.timeline-dot {
    position: absolute;
    left: -20px;
    top: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 0 3px #FFFFFF;
}

@media (max-width: 900px) {
    .refund-detail-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function openProcessModal() {
    const el = document.getElementById('processModalBackdrop');
    if (el) el.style.display = 'flex';
}
function closeProcessModal() {
    const el = document.getElementById('processModalBackdrop');
    if (el) el.style.display = 'none';
}
function openEditModal() {
    const el = document.getElementById('editModalBackdrop');
    if (el) el.style.display = 'flex';
}
function closeEditModal() {
    const el = document.getElementById('editModalBackdrop');
    if (el) el.style.display = 'none';
}
function openRejectModal() {
    const el = document.getElementById('rejectModalBackdrop');
    if (el) el.style.display = 'flex';
}
function closeRejectModal() {
    const el = document.getElementById('rejectModalBackdrop');
    if (el) el.style.display = 'none';
}

function handleModalBackdropClick(e, backdropId) {
    if (e.target && e.target.id === backdropId) {
        document.getElementById(backdropId).style.display = 'none';
    }
}

function handleProcessSubmit(e) {
    const btn = document.getElementById('btnConfirmProcess');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Payment...';
    }
    return true;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProcessModal();
        closeEditModal();
        closeRejectModal();
    }
});
</script>

