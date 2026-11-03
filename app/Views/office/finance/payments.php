<?php
/**
 * MANASWI TOURS & TRAVELS — CUSTOMER PAYMENT HISTORY
 * Exactly matching Customer, Employee, and Booking Reference UI Patterns
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/invoices') . '" class="btn btn-outline"><i class="fa-solid fa-file-invoice"></i> <span>Manage Invoices</span></a>'
    . '<button type="button" class="btn btn-primary" onclick="openPaymentModal()"><i class="fa-solid fa-plus"></i> <span>Record Payment</span></button>'
    . '</div>';

$selectedMethod = $selectedMethod ?? '';
$search         = $search ?? '';
$dateFrom       = $dateFrom ?? '';
$dateTo         = $dateTo ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($payments);
$currP          = $currentPage ?? 1;
?>

<div class="bookings-page-view finance-page-view" id="customerPaymentsModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Payment History',
            'description' => 'Real-time record of all monetary receipts, guest deposits, tour settlements, and payment method channels.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Payment Receipts',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Payment History', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="paymentsKpiGrid">
        <!-- Card 1: Total Money Received -->
        <div class="booking-kpi-card <?= empty($selectedMethod) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Received</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_inr($totalCollected) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totRec) ?> recorded transactions</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: UPI Collections -->
        <div class="booking-kpi-card <?= $selectedMethod === 'upi' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=upi') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">UPI Collections</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_inr($upiCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>GPay, PhonePe, Paytm QR</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Counter Cash Collections -->
        <div class="booking-kpi-card <?= $selectedMethod === 'cash' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=cash') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cash Collections</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($cashCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>Direct counter desk cash</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Bank & Card Collections -->
        <div class="booking-kpi-card <?= in_array($selectedMethod, ['bank_transfer', 'card']) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/payments?method=bank_transfer') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Bank &amp; Card Transfer</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= format_inr($bankCollected) ?></div>
            <div class="booking-kpi-sub">
                <span>NEFT, RTGS, IMPS &amp; POS</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/payments') ?>" class="filter-bar-compact" id="paymentsFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by customer, booking ref, invoice #, transaction UTR..." value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/payments') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="method" id="methodInput">
            <option value="" <?= empty($selectedMethod) ? 'selected' : '' ?>>All Payment Methods</option>
            <option value="cash" <?= $selectedMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="upi" <?= $selectedMethod === 'upi' ? 'selected' : '' ?>>UPI (GPay / PhonePe / Paytm)</option>
            <option value="bank_transfer" <?= $selectedMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer (NEFT/IMPS)</option>
            <option value="card" <?= $selectedMethod === 'card' ? 'selected' : '' ?>>Debit / Credit Card</option>
            <option value="cheque" <?= $selectedMethod === 'cheque' ? 'selected' : '' ?>>Bank Cheque</option>
        </select>

        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="From Date">
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="To Date">

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Apply Filters"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/payments') ?>" class="fbc-btn" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Payment Receipts Modern Table & Mobile Cards -->
    <section class="recent-bookings-section" id="paymentsTableSection">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-receipt" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Customer Payment Records
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 120px;">Payment Date</th>
                        <th style="min-width: 180px;">Customer Name</th>
                        <th style="min-width: 130px;">Booking Ref</th>
                        <th style="min-width: 120px;">Invoice #</th>
                        <th style="min-width: 140px;">Payment Method</th>
                        <th style="min-width: 150px;">Deposit Account</th>
                        <th style="min-width: 150px;">Transaction Ref</th>
                        <th style="text-align: right; width: 130px;">Amount Received</th>
                        <th class="text-center" style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="10">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No payment receipts found</h4>
                                <p class="text-muted" style="margin:0;">No payments match your active search or date filter.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $i => $p):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $m = strtolower($p['payment_method'] ?? 'cash');
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Payment Date -->
                            <td>
                                <div style="font-weight: 600; color: var(--vg-navy-900); font-size: 0.86rem;"><?= format_date($p['payment_date']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= format_time($p['created_at']) ?></div>
                            </td>

                            <!-- 3. Customer Name -->
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($p['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($p['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?= e($p['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Booking Code -->
                            <td>
                                <?php if (!empty($p['booking_code'])): ?>
                                    <a href="<?= url('/office/bookings/' . $p['booking_id']) ?>" style="font-weight: 700; color: var(--brand-primary); text-decoration: none; font-size: 0.84rem;">
                                        <?= e($p['booking_code']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Invoice # -->
                            <td>
                                <?php if (!empty($p['invoice_number'])): ?>
                                    <a href="<?= url('/office/invoices/' . $p['invoice_id']) ?>" style="font-weight: 600; color: #4F46E5; text-decoration: none; font-size: 0.84rem;">
                                        <?= e($p['invoice_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Payment Method -->
                            <td>
                                <span class="badge" style="font-size: 0.74rem; text-transform: uppercase; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: rgba(33, 135, 101, 0.12); color: #218765;">
                                    <?= e($p['payment_method']) ?>
                                </span>
                            </td>

                            <!-- 7. Deposit Account -->
                            <td>
                                <div style="font-size: 0.84rem; color: var(--vg-navy-900); font-weight: 600;">
                                    <?= e($p['account_name'] ?? 'General Treasury') ?>
                                </div>
                            </td>

                            <!-- 8. Transaction Reference -->
                            <td>
                                <?php if (!empty($p['payment_reference'])): ?>
                                    <code style="font-size: 0.78rem; background: var(--page-background-secondary); padding: 2px 6px; border-radius: 4px;"><?= e($p['payment_reference']) ?></code>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">PAY-<?= str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Amount -->
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: #218765;">
                                    + <?= format_inr($p['amount']) ?>
                                </strong>
                            </td>

                            <!-- 10. Actions -->
                            <td class="text-center">
                                <div style="display: flex; gap: 0.35rem; justify-content: center;">
                                    <?php if (!empty($p['invoice_id'])): ?>
                                    <a href="<?= url('/office/invoices/' . $p['invoice_id']) ?>" class="btn btn-outline btn-sm" style="padding: 3px 8px;" title="View Invoice">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens -->
        <div class="cards-grid">
            <?php foreach ($payments as $p): ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($p['customer_name'] ?? 'Direct Customer') ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= format_date($p['payment_date']) ?> &bull; <?= strtoupper($p['payment_method']) ?></span>
                        <?php if (!empty($p['booking_code'])): ?>
                            <div style="font-size: 0.75rem; color: var(--brand-primary); font-weight: 600; margin-top: 2px;"><?= e($p['booking_code']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1rem; color: #218765; display: block;">+ <?= format_inr($p['amount']) ?></strong>
                        <span style="font-size: 0.74rem; color: var(--text-muted);"><?= e($p['invoice_number'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Customers & Bookings Pattern) -->
    <?php
        $totalPages = $totalPages ?? 1;
        $from = $totRec > 0 ? (($currP - 1) * $perPage) + 1 : 0;
        $to   = min($currP * $perPage, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedMethod, $dateFrom, $dateTo, $perPage) {
            $q = [
                'search'    => $search,
                'method'    => $selectedMethod,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'per_page'  => $perPage,
                'page'      => $p,
            ];
            return url('/office/finance/payments?' . http_build_query(array_filter($q)));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> payment receipts</div>
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
<!-- MODAL: Record Customer Payment                                            -->
<!-- ========================================================================= -->
<div id="paymentModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'paymentModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-money-bill-wave" style="color: #218765; margin-right: 0.45rem;"></i>
                Record Customer Payment
            </h3>
            <button type="button" class="modal-close-custom" onclick="closePaymentModal()">&times;</button>
        </div>
        <form action="<?= url('/office/invoices/record-payment') ?>" method="POST" id="recordPaymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/payments') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="pay_invoice_id">Select Customer Invoice <span class="req">*</span></label>
                    <select name="invoice_id" id="pay_invoice_id" class="form-select" onchange="onInvoiceSelected(this)" required>
                        <option value="">— Select Pending Invoice —</option>
                        <?php foreach ($pendingInvoices as $inv): 
                            $bal = (float)$inv['total_amount'] - (float)$inv['paid_amount'];
                        ?>
                            <option value="<?= $inv['id'] ?>" data-balance="<?= $bal ?>" data-booking="<?= e($inv['booking_code'] ?? '') ?>">
                                <?= e($inv['invoice_number']) ?> &bull; <?= e($inv['customer_name'] ?? 'Direct') ?> &bull; Pending: <?= format_inr($bal) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="pay_amount">Amount Received (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pay_date">Payment Date <span class="req">*</span></label>
                        <input type="date" name="payment_date" id="pay_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="pay_method">Payment Mode <span class="req">*</span></label>
                        <select name="payment_method" id="pay_method" class="form-select" required>
                            <option value="cash">Counter Cash</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pay_account_id">Deposit Into Account <span class="req">*</span></label>
                        <select name="account_id" id="pay_account_id" class="form-select" required>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="pay_reference">Transaction / UTR Reference</label>
                    <input type="text" name="payment_reference" id="pay_reference" class="form-control" placeholder="UTR number, UPI ref, or cheque #">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="pay_notes">Notes / Receipt Remarks</label>
                    <input type="text" name="notes" id="pay_notes" class="form-control" placeholder="Payment received confirmation...">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Payment</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'flex';
    }
    function closePaymentModal() {
        document.getElementById('paymentModalBackdrop').style.display = 'none';
    }
    function onInvoiceSelected(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (opt && opt.dataset.balance) {
            document.getElementById('pay_amount').value = parseFloat(opt.dataset.balance).toFixed(2);
        }
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
