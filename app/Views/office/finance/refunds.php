<?php
/**
 * MANASWI TOURS & TRAVELS — CUSTOMER BOOKING REFUNDS & ADJUSTMENTS
 * Fully consistent with Customer, Employee, and Booking Reference UI Patterns
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/payments') . '" class="btn btn-outline"><i class="fa-solid fa-receipt"></i> <span>Payment History</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openRefundModal()"><i class="fa-solid fa-plus"></i> <span>Issue Refund</span></button>'
    . '</div>';

$selectedStatus = $selectedStatus ?? '';
$search         = $search ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($refunds);
$currP          = $currentPage ?? 1;
?>

<div class="bookings-page-view finance-page-view" id="financeRefundsPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Booking Refunds',
            'description' => 'Statutory audit logs for tour cancellations, security deposit reversals, and payment gateway return settlements.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Refund Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Refunds', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="refundsKpiGrid">
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Refund Requests</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h6a3 3 0 003-3v-6a3 3 0 00-3-3h-1"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll) ?></div>
            <div class="booking-kpi-sub">
                <span>All cancellation claims</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'processed' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=processed') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Settled &amp; Returned</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalProcessed) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Returned to guest bank accounts</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=pending') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Processing</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $pendingCount > 0 ? '#D95757' : 'var(--brand-primary)' ?>;">
                <?= format_inr($totalPending) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= $pendingCount ?> pending refund claims</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/refunds') ?>" class="filter-bar-compact" id="refundFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search refund #, booking code, customer..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/refunds') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review</option>
            <option value="processed" <?= ($selectedStatus ?? '') === 'processed' ? 'selected' : '' ?>>Processed &amp; Settled</option>
            <option value="rejected" <?= ($selectedStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/refunds') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Refunds Modern Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Cancellation &amp; Refund Records</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Refund #</th>
                        <th style="min-width: 130px;">Booking Code</th>
                        <th style="min-width: 180px;">Customer</th>
                        <th style="min-width: 220px;">Cancellation Reason</th>
                        <th style="width: 120px;">Method</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($refunds)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No refund claims recorded</h4>
                                <p class="text-muted" style="margin:0;">When customer bookings are cancelled, refund tickets will appear here for processing.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($refunds as $r):
                            $st = strtolower($r['status']);
                            $stClass = $st === 'processed' ? 'completed' : ($st === 'rejected' ? 'cancelled' : 'pending');
                        ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.88rem;"><?= e($r['refund_number']) ?></strong>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= format_date($r['refund_date'] ?? $r['created_at']) ?></div>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem;"><?= e($r['booking_code'] ?: 'Unlinked') ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.9rem;"><?= e($r['customer_name'] ?? 'Guest Traveler') ?></strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= e($r['customer_phone'] ?? $r['customer_email'] ?? '') ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);"><?= e($r['reason']) ?></div>
                            </td>
                            <td style="font-size: 0.82rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">
                                <?= e(str_replace('_', ' ', $r['refund_method'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1rem; color: #D95757;">
                                    <?= format_inr($r['amount']) ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $st === 'processed' ? 'background: rgba(33, 135, 101, 0.12); color: #218765;' : ($st === 'rejected' ? 'background: rgba(217, 87, 87, 0.12); color: #D95757;' : 'background: rgba(199, 138, 53, 0.12); color: #C78A35;') ?>">
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($st === 'pending'): ?>
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem;" onclick="openProcessRefundModal(<?= (int)$r['id'] ?>, '<?= addslashes(e($r['refund_number'])) ?>', <?= (float)$r['amount'] ?>)">
                                        Settle
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List -->
        <div class="cards-grid">
            <?php foreach ($refunds as $r):
                $st = strtolower($r['status']);
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($r['customer_name'] ?? 'Traveler') ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($r['refund_number']) ?> &bull; <?= e($r['booking_code'] ?: 'Unlinked') ?></span>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;"><?= e($r['reason']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: #D95757; display: block;">
                            <?= format_inr($r['amount']) ?>
                        </strong>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: <?= $st === 'processed' ? '#218765' : '#C78A35' ?>;"><?= e($st) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Issue Customer Refund (Requirement 4 & 10)                         -->
<!-- ========================================================================= -->
<div id="refundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'refundModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-rotate-left" style="color: #D95757; margin-right: 0.45rem;"></i>
                Issue Customer Refund
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeRefundModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/refunds') ?>" method="POST" id="issueRefundForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="ref_invoice_id">Linked Customer Invoice (Optional)</label>
                    <select name="invoice_id" id="ref_invoice_id" class="form-select" onchange="onRefundInvoiceSelected(this)">
                        <option value="">— Unlinked / Direct Cancellation —</option>
                        <?php foreach ($eligibleInvoices as $inv): ?>
                            <option value="<?= $inv['id'] ?>" data-paid="<?= $inv['paid_amount'] ?>">
                                <?= e($inv['invoice_number']) ?> &bull; <?= e($inv['customer_name'] ?? 'Direct') ?> &bull; Paid: <?= format_inr($inv['paid_amount']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="ref_amount">Refund Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="ref_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ref_date">Refund Date <span class="req">*</span></label>
                        <input type="date" name="refund_date" id="ref_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="ref_method">Refund Mode <span class="req">*</span></label>
                        <select name="refund_method" id="ref_method" class="form-select" required>
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI (GPay / PhonePe)</option>
                            <option value="cash">Counter Cash</option>
                            <option value="gateway_reversal">Gateway Reversal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ref_account_id">Debit Account</label>
                        <select name="account_id" id="ref_account_id" class="form-select">
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_inr($acc['current_balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="ref_reason">Reason for Refund <span class="req">*</span></label>
                    <textarea name="reason" id="ref_reason" class="form-control" rows="2" placeholder="e.g. Guest cancelled booking 7 days prior / Trip itinerary adjusted" required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="auto_process" value="1" checked>
                        <span>Immediately process and debit account balance</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeRefundModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #D95757; border-color: #D95757;"><i class="fa-solid fa-check"></i> <span>Issue Refund</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Settle / Process Pending Refund                                    -->
<!-- ========================================================================= -->
<div id="processRefundModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'processRefundModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-check-circle" style="color: #218765; margin-right: 0.45rem;"></i>
                Settle Refund: <span id="prRefundNumber"></span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeProcessRefundModal()">&times;</button>
        </div>
        <form id="processRefundForm" method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="processed">
            <div class="modal-body-custom">
                <div style="background: rgba(217, 87, 87, 0.06); padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; color: var(--vg-navy-900); font-weight: 600;">Refund Amount to Disburse:</span>
                    <strong style="color: #D95757; font-size: 1.1rem;" id="prRefundAmount"></strong>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="pr_account_id">Debit from Company Account <span class="req">*</span></label>
                    <select name="account_id" id="pr_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (Available: <?= format_inr($acc['current_balance']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeProcessRefundModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Settlement</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRefundModal() {
        document.getElementById('refundModalBackdrop').style.display = 'flex';
    }
    function closeRefundModal() {
        document.getElementById('refundModalBackdrop').style.display = 'none';
    }
    function onRefundInvoiceSelected(sel) {
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.dataset.paid) {
            document.getElementById('ref_amount').max = opt.dataset.paid;
            document.getElementById('ref_amount').placeholder = 'Max ' + parseFloat(opt.dataset.paid).toFixed(2);
        }
    }
    function openProcessRefundModal(id, refNum, amount) {
        document.getElementById('processRefundForm').action = '<?= url('/office/finance/refunds') ?>/' + id + '/status';
        document.getElementById('prRefundNumber').textContent = refNum;
        document.getElementById('prRefundAmount').textContent = '₹ ' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('processRefundModalBackdrop').style.display = 'flex';
    }
    function closeProcessRefundModal() {
        document.getElementById('processRefundModalBackdrop').style.display = 'none';
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
    max-width: 540px;
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
