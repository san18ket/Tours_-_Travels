<?php
/**
 * DEDICATED RECORD CUSTOMER PAYMENT VIEW
 * Canonical Office Design System
 */
$isOverdue = !empty($invoice['due_date']) && strtotime($invoice['due_date']) < strtotime(date('Y-m-d'));
$customerName = $invoice['customer_name'] ?? 'Direct Customer';
$customerPhone = $invoice['customer_phone'] ?? '';
$customerCode = $invoice['customer_code'] ?? '';

$headerActions = '<div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">'
    . '<a href="' . url('/office/finance/pending-payments/' . $invoice['id']) . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> <span>View Details</span></a>'
    . '<a href="' . url('/office/finance/pending-payments') . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-list-check"></i> <span>All Dues</span></a>'
    . '</div>';
?>

<div class="bookings-page-view finance-page-view" id="pendingPaymentPayModule">
    <!-- 1. Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Record Payment: ' . e($invoice['invoice_number']),
            'description' => 'Post a customer settlement transaction against invoice balance. Updates ledger, account cash flow, and booking status atomically.',
            'badge'       => 'Pending Due: ' . format_inr($remainingDue),
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Pending Payments', 'url' => url('/office/finance/pending-payments')],
                ['label' => e($invoice['invoice_number']), 'url' => url('/office/finance/pending-payments/' . $invoice['id'])],
                ['label' => 'Record Payment', 'url' => ''],
            ],
            'actions'     => $headerActions,
        ]) ?>
    </div>

    <!-- Mobile Top Navigation Strip -->
    <div class="mv-bookings-header" style="margin-bottom: 1rem;">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance/pending-payments/' . $invoice['id']) ?>" class="mv-back-btn" title="Back to Details">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title" style="font-size: 1.05rem;">Record Payment</h2>
            </div>
            <a href="<?= url('/office/finance/pending-payments') ?>" class="mv-add-btn" style="background: rgba(22, 68, 59, 0.08); color: var(--brand-primary);">
                <i class="fa-solid fa-list-check"></i>
                <span>Dues</span>
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start;" class="pay-responsive-grid">
        <!-- LEFT: Payment Form Container -->
        <div class="card shadow-sm" style="background: #ffffff; border-radius: 14px; border: 1px solid rgba(22, 68, 59, 0.08); overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-receipt" style="color: #218765;"></i>
                    Payment Disbursement Details
                </h3>
                <span class="badge" style="background: rgba(33, 135, 101, 0.12); color: #218765; font-weight: 700; font-size: 0.76rem; padding: 4px 10px; border-radius: 6px;">
                    Secure Transaction
                </span>
            </div>

            <form action="<?= url('/office/finance/pending-payments/' . $invoice['id'] . '/pay') ?>" method="POST" id="recordPaymentForm" style="padding: 1.5rem;" onsubmit="return validatePaymentForm(event)">
                <?= csrf_field() ?>
                <input type="hidden" name="idempotency_key" value="<?= e($idempotencyKey) ?>">
                <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">

                <!-- Client-side error banner -->
                <div id="formAlertBox" style="display: none; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.86rem;"></div>

                <!-- 1. Amount & Quick Fill -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label class="form-label" for="amountInput" style="font-weight: 700; color: var(--vg-navy-900); margin: 0;">
                            Payment Amount (₹) <span class="req" style="color: #D95757;">*</span>
                        </label>
                        <div style="display: flex; gap: 0.4rem;">
                            <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.74rem; padding: 2px 8px; border-radius: 4px;" onclick="setAmount(<?= (float)$remainingDue ?>)">
                                Full (₹<?= number_format($remainingDue, 2) ?>)
                            </button>
                            <?php if ($remainingDue > 500): ?>
                            <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.74rem; padding: 2px 8px; border-radius: 4px;" onclick="setAmount(<?= round($remainingDue / 2, 2) ?>)">
                                50% (₹<?= number_format(round($remainingDue / 2, 2), 2) ?>)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; font-size: 1.15rem; color: #218765;">₹</span>
                        <input type="number" 
                               name="amount" 
                               id="amountInput" 
                               step="0.01" 
                               min="0.01" 
                               max="<?= number_format($remainingDue, 2, '.', '') ?>" 
                               value="<?= number_format($remainingDue, 2, '.', '') ?>" 
                               class="form-control" 
                               style="padding-left: 2rem; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); height: 46px; border-radius: 10px;" 
                               required>
                    </div>
                    <span style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 4px;">
                        Remaining balance limit: <strong>₹<?= number_format($remainingDue, 2) ?></strong>. Partial settlements are supported.
                    </span>
                </div>

                <!-- 2. Payment Date & Method -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="paymentDateInput" style="font-weight: 600; color: var(--vg-navy-900);">
                            Payment Date <span class="req" style="color: #D95757;">*</span>
                        </label>
                        <input type="date" 
                               name="payment_date" 
                               id="paymentDateInput" 
                               class="form-control" 
                               value="<?= date('Y-m-d') ?>" 
                               max="<?= date('Y-m-d') ?>" 
                               style="height: 42px; border-radius: 8px; font-size: 0.88rem;" 
                               required>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="paymentMethodSelect" style="font-weight: 600; color: var(--vg-navy-900);">
                            Payment Mode <span class="req" style="color: #D95757;">*</span>
                        </label>
                        <select name="payment_method" id="paymentMethodSelect" class="form-select" style="height: 42px; border-radius: 8px; font-size: 0.88rem;" required>
                            <option value="cash">Counter Cash</option>
                            <option value="upi" selected>UPI (GPay / PhonePe / Paytm / QR)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT / RTGS / IMPS)</option>
                            <option value="net_banking">Net Banking</option>
                            <option value="card">Credit / Debit Card</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Deposit Into Account -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="accountIdSelect" style="font-weight: 600; color: var(--vg-navy-900);">
                        Deposit Account <span class="req" style="color: #D95757;">*</span>
                    </label>
                    <select name="account_id" id="accountIdSelect" class="form-select" style="height: 42px; border-radius: 8px; font-size: 0.88rem;" required>
                        <?php if (empty($accounts)): ?>
                            <option value="">No Active Financial Accounts Configured</option>
                        <?php else: ?>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" <?= !empty($acc['is_primary']) ? 'selected' : '' ?>>
                                    <?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type']) ?>) &mdash; Current Balance: <?= format_inr((float)($acc['current_balance'] ?? 0)) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 3px;">
                        Funds will be automatically credited to this ledger balance upon confirmation.
                    </span>
                </div>

                <!-- 4. Reference / Transaction Number -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="paymentReferenceInput" style="font-weight: 600; color: var(--vg-navy-900);">
                        UTR / Transaction Reference Number
                    </label>
                    <input type="text" 
                           name="payment_reference" 
                           id="paymentReferenceInput" 
                           class="form-control" 
                           placeholder="e.g. UPI Ref 302910283, Bank UTR SBIN000123" 
                           style="height: 42px; border-radius: 8px; font-size: 0.88rem;">
                    <span style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 3px;">
                        Recommended for UPI, NEFT, and Card transactions for reconciliation audits.
                    </span>
                </div>

                <!-- 5. Notes / Remarks -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="notesInput" style="font-weight: 600; color: var(--vg-navy-900);">
                        Internal Settlement Notes / Remarks
                    </label>
                    <textarea name="notes" 
                              id="notesInput" 
                              class="form-control" 
                              rows="2" 
                              placeholder="e.g. Paid in cash at counter, verified by office accounts desk..." 
                              style="border-radius: 8px; font-size: 0.88rem;"></textarea>
                </div>

                <!-- Submit & Actions Strip -->
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 1.25rem;">
                    <a href="<?= url('/office/finance/pending-payments/' . $invoice['id']) ?>" class="btn btn-outline" style="border-radius: 8px;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitPaymentBtn" style="background: #218765; border-color: #218765; padding: 0.65rem 1.75rem; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-check" id="btnIcon"></i>
                        <span id="btnText">Confirm & Record Payment</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT: Context & Settlement Summary Card -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Invoice Context Card -->
            <div class="card shadow-sm" style="background: #ffffff; border-radius: 14px; border: 1px solid rgba(22, 68, 59, 0.08); padding: 1.25rem;">
                <div style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem;">
                    Settlement Invoice
                </div>
                <div style="font-size: 1.15rem; font-weight: 800; color: #4F46E5; margin-bottom: 0.25rem;">
                    <?= e($invoice['invoice_number']) ?>
                </div>
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Issued Date: <?= format_date($invoice['issued_at'] ?? $invoice['created_at']) ?>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.6rem; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.86rem;">
                        <span style="color: var(--text-muted);">Invoice Total:</span>
                        <strong style="color: var(--vg-navy-900);"><?= format_inr((float)$invoice['total_amount']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.86rem;">
                        <span style="color: var(--text-muted);">Already Received:</span>
                        <strong style="color: #218765;"><?= format_inr((float)$invoice['paid_amount']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.95rem; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 0.6rem;">
                        <span style="font-weight: 700; color: #855314;">Remaining Balance:</span>
                        <strong style="color: #C78A35; font-size: 1.15rem;" id="summaryRemainingDue"><?= format_inr($remainingDue) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.84rem; margin-top: 0.25rem;">
                        <span style="color: var(--text-muted);">Due Date:</span>
                        <span style="font-weight: 600; color: <?= $isOverdue ? '#D95757' : 'var(--vg-navy-900)' ?>;">
                            <?= !empty($invoice['due_date']) ? format_date($invoice['due_date']) : 'On Arrival' ?>
                            <?= $isOverdue ? '<span style="font-size: 0.72rem; color: #D95757; font-weight: 700; margin-left: 3px;">(OVERDUE)</span>' : '' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer Card -->
            <div class="card shadow-sm" style="background: #ffffff; border-radius: 14px; border: 1px solid rgba(22, 68, 59, 0.08); padding: 1.25rem;">
                <div style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem;">
                    Billed Customer
                </div>
                <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.2rem;">
                    <?= e($customerName) ?>
                </div>
                <?php if (!empty($customerPhone)): ?>
                    <div style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                        <i class="fa-solid fa-phone" style="font-size: 0.75rem; margin-right: 0.25rem;"></i> <?= e($customerPhone) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($invoice['booking_code'])): ?>
                    <div style="margin-top: 0.6rem; padding-top: 0.6rem; border-top: 1px solid rgba(0,0,0,0.06); font-size: 0.82rem;">
                        <span style="color: var(--text-muted);">Linked Booking:</span>
                        <a href="<?= url('/office/bookings/' . $invoice['booking_id']) ?>" style="font-weight: 700; color: var(--brand-primary); text-decoration: none; margin-left: 0.3rem;">
                            <?= e($invoice['booking_code']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ledger Impact Note -->
            <div style="background: rgba(33, 135, 101, 0.06); border: 1px solid rgba(33, 135, 101, 0.18); border-radius: 12px; padding: 1rem; font-size: 0.82rem; color: #16443B; line-height: 1.45;">
                <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-shield-check" style="color: #218765;"></i>
                    Automated Ledger Posting
                </div>
                Submitting this payment updates the customer invoice status, increases the chosen deposit account balance, and generates an audit log automatically.
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .pay-responsive-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
    const maxRemaining = <?= (float)$remainingDue ?>;

    function setAmount(val) {
        const inp = document.getElementById('amountInput');
        if (inp) {
            inp.value = parseFloat(val).toFixed(2);
            validateAmountLive();
        }
    }

    function showFormError(msg) {
        const box = document.getElementById('formAlertBox');
        if (box) {
            box.style.display = 'block';
            box.style.background = 'rgba(217, 87, 87, 0.12)';
            box.style.border = '1px solid rgba(217, 87, 87, 0.25)';
            box.style.color = '#D95757';
            box.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="margin-right: 0.4rem;"></i> ' + msg;
        }
        if (window.vgNotify && typeof window.vgNotify.error === 'function') {
            window.vgNotify.error(msg);
        }
    }

    function clearFormError() {
        const box = document.getElementById('formAlertBox');
        if (box) {
            box.style.display = 'none';
            box.textContent = '';
        }
    }

    function validateAmountLive() {
        const inp = document.getElementById('amountInput');
        if (!inp) return true;
        const val = parseFloat(inp.value);

        if (isNaN(val) || val <= 0) {
            showFormError('Please enter a valid payment amount greater than ₹0.00.');
            return false;
        }

        if (val > maxRemaining + 0.01) {
            showFormError('Payment amount (₹' + val.toFixed(2) + ') cannot exceed the remaining due amount of ₹' + maxRemaining.toFixed(2) + '.');
            return false;
        }

        clearFormError();
        return true;
    }

    document.getElementById('amountInput')?.addEventListener('input', validateAmountLive);

    function validatePaymentForm(e) {
        if (!validateAmountLive()) {
            e.preventDefault();
            return false;
        }

        const accSelect = document.getElementById('accountIdSelect');
        if (accSelect && !accSelect.value) {
            showFormError('Please select a valid deposit account.');
            e.preventDefault();
            return false;
        }

        // Disable submit button to prevent double settlement
        const submitBtn = document.getElementById('submitPaymentBtn');
        const btnIcon = document.getElementById('btnIcon');
        const btnText = document.getElementById('btnText');
        if (submitBtn) {
            submitBtn.disabled = true;
            if (btnIcon) {
                btnIcon.className = 'fa-solid fa-circle-notch fa-spin';
            }
            if (btnText) {
                btnText.textContent = 'Processing Settlement...';
            }
        }

        return true;
    }
</script>

