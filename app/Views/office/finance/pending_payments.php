<?php
/**
 * MANASWI TOURS & TRAVELS — CUSTOMER PENDING PAYMENTS & RECEIVABLES
 * Exactly matching Customer, Employee, and Booking Reference UI Patterns
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/office/finance/reports/customer-dues') . '" class="btn btn-outline"><i class="fa-solid fa-clock-rotate-left"></i> <span>Aging Report</span></a>'
    . '<a href="' . url('/office/invoices/create') . '" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <span>Create Invoice</span></a>'
    . '</div>';

$selectedStatus = $selectedStatus ?? '';
$search         = $search ?? '';
$dateFrom       = $dateFrom ?? '';
$dateTo         = $dateTo ?? '';
$perPage        = $perPage ?? 15;
$totRec         = $totalRecords ?? count($invoices);
$currP          = $currentPage ?? 1;
?>

<div class="bookings-page-view finance-page-view" id="pendingPaymentsModule">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Customer Pending Payments',
            'description' => 'Real-time accounts receivable, outstanding customer balances, pending trip collections, and payment dues.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Accounts Receivable',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance', 'url' => url('/office/finance')],
                ['label' => 'Pending Payments', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="pendingKpiGrid">
        <!-- Card 1: Total Pending Dues -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Customer Dues</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_inr($totalDues) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($totRec) ?> pending invoices</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Overdue Amounts -->
        <div class="booking-kpi-card" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/reports/customer-dues') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Overdue Balance</span>
                <span class="booking-kpi-icon icon-rose" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #D95757;"><?= format_inr($overdueDues) ?></div>
            <div class="booking-kpi-sub">
                <span>Passed settlement due date</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Unpaid Invoices Count -->
        <div class="booking-kpi-card <?= $selectedStatus === 'unpaid' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments?status=unpaid') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Zero Payment Received</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= number_format($unpaidCount) ?></div>
            <div class="booking-kpi-sub">
                <span>Completely unpaid invoices</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Partially Paid Invoices Count -->
        <div class="booking-kpi-card <?= $selectedStatus === 'partial' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/pending-payments?status=partial') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Partially Paid</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #4F46E5;"><?= number_format($partialCount) ?></div>
            <div class="booking-kpi-sub">
                <span>Advance deposit received</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/pending-payments') ?>" class="filter-bar-compact" id="pendingFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by customer, phone, invoice #, booking ref..." value="<?= e($search) ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/finance/pending-payments') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Pending Dues</option>
            <option value="unpaid" <?= $selectedStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid (No Payment)</option>
            <option value="partial" <?= $selectedStatus === 'partial' ? 'selected' : '' ?>>Partially Paid (Advance Given)</option>
        </select>

        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="From Due Date">
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="form-control" style="max-width: 140px; height: 38px; font-size: 0.85rem;" title="To Due Date">

        <select name="per_page" id="perPageInput">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Apply Filters"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/pending-payments') ?>" class="fbc-btn" title="Reset Filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. Modern Table & Mobile Cards -->
    <section class="recent-bookings-section" id="pendingTableSection">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fa-solid fa-file-invoice-dollar" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Pending Customer Invoices
            </h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Invoices:</span>
                <span class="total-records-badge"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens -->
        <div class="modern-table-container">
            <table class="modern-table" id="pendingTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Customer Details</th>
                        <th style="min-width: 130px;">Booking Ref</th>
                        <th style="min-width: 120px;">Invoice #</th>
                        <th style="min-width: 120px;">Due Date</th>
                        <th style="text-align: right; width: 120px;">Total Amount</th>
                        <th style="text-align: right; width: 120px;">Received Amount</th>
                        <th style="text-align: right; width: 130px;">Remaining Due</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="10">
                            <div class="no-data-message" style="text-align: center; padding: 3rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-circle-check" style="color: #218765;"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">All Customer Payments Are Clear!</h4>
                                <p class="text-muted" style="margin:0;">There are no outstanding customer receivables matching your current filter.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $i => $inv):
                            $serial = (($currP - 1) * $perPage) + $i + 1;
                            $rem = (float)$inv['total_amount'] - (float)$inv['paid_amount'];
                            $isOverdue = !empty($inv['due_date']) && strtotime($inv['due_date']) < strtotime(date('Y-m-d'));
                            $status = strtolower($inv['payment_status'] ?? 'unpaid');
                        ?>
                        <tr>
                            <!-- 1. Serial -->
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Customer -->
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.88rem; display: block;"><?= e($inv['customer_name'] ?? 'Direct Customer') ?></strong>
                                <?php if (!empty($inv['customer_phone'])): ?>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?= e($inv['customer_phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- 3. Booking Ref -->
                            <td>
                                <?php if (!empty($inv['booking_code'])): ?>
                                    <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" style="font-weight: 700; color: var(--brand-primary); text-decoration: none; font-size: 0.84rem;">
                                        <?= e($inv['booking_code']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Invoice # -->
                            <td>
                                <a href="<?= url('/office/invoices/' . $inv['id']) ?>" style="font-weight: 600; color: #4F46E5; text-decoration: none; font-size: 0.84rem;">
                                    <?= e($inv['invoice_number']) ?>
                                </a>
                                <div style="font-size: 0.72rem; color: var(--text-muted);"><?= format_date($inv['issued_at'] ?? $inv['created_at']) ?></div>
                            </td>

                            <!-- 5. Due Date -->
                            <td>
                                <?php if (!empty($inv['due_date'])): ?>
                                    <span style="font-size: 0.84rem; font-weight: 600; color: <?= $isOverdue ? '#D95757' : 'var(--vg-navy-900)' ?>;">
                                        <?= format_date($inv['due_date']) ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <div style="font-size: 0.7rem; color: #D95757; font-weight: 700; text-transform: uppercase;">Overdue</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.82rem;">On Arrival</span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Total Amount -->
                            <td style="text-align: right; font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);">
                                <?= format_inr($inv['total_amount']) ?>
                            </td>

                            <!-- 7. Received Amount -->
                            <td style="text-align: right; font-size: 0.88rem; font-weight: 600; color: #218765;">
                                <?= format_inr($inv['paid_amount']) ?>
                            </td>

                            <!-- 8. Remaining Balance -->
                            <td style="text-align: right;">
                                <strong style="font-size: 0.98rem; color: #C78A35;">
                                    <?= format_inr($rem) ?>
                                </strong>
                            </td>

                            <!-- 9. Status -->
                            <td class="text-center">
                                <span class="badge" style="font-size: 0.74rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; <?= $status === 'partial' ? 'background: rgba(199, 138, 53, 0.12); color: #C78A35;' : 'background: rgba(217, 87, 87, 0.12); color: #D95757;' ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>

                            <!-- 10. Actions -->
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 3px 10px; font-size: 0.78rem; background: #218765; border-color: #218765;" onclick="quickPayInvoice(<?= (int)$inv['id'] ?>, '<?= addslashes(e($inv['invoice_number'])) ?>', '<?= addslashes(e($inv['customer_name'] ?? 'Direct Customer')) ?>', <?= (float)$rem ?>)">
                                    <i class="fa-solid fa-hand-holding-dollar"></i> <span>Pay</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List for Small Screens -->
        <div class="cards-grid">
            <?php foreach ($invoices as $inv):
                $rem = (float)$inv['total_amount'] - (float)$inv['paid_amount'];
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($inv['customer_name'] ?? 'Direct Customer') ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($inv['invoice_number']) ?> &bull; Due: <?= !empty($inv['due_date']) ? format_date($inv['due_date']) : 'On Arrival' ?></span>
                        <?php if (!empty($inv['booking_code'])): ?>
                            <div style="font-size: 0.75rem; color: var(--brand-primary); font-weight: 600; margin-top: 2px;"><?= e($inv['booking_code']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1rem; color: #C78A35; display: block;"><?= format_inr($rem) ?></strong>
                        <button type="button" class="btn btn-sm btn-primary" style="margin-top: 4px; padding: 2px 8px; font-size: 0.72rem; background: #218765; border-color: #218765;" onclick="quickPayInvoice(<?= (int)$inv['id'] ?>, '<?= addslashes(e($inv['invoice_number'])) ?>', '<?= addslashes(e($inv['customer_name'] ?? 'Direct Customer')) ?>', <?= (float)$rem ?>)">
                            Collect
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modern Pagination -->
    <?php
        $totalPages = $totalPages ?? 1;
        $from = $totRec > 0 ? (($currP - 1) * $perPage) + 1 : 0;
        $to   = min($currP * $perPage, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $dateFrom, $dateTo, $perPage) {
            $q = [
                'search'    => $search,
                'status'    => $selectedStatus,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'per_page'  => $perPage,
                'page'      => $p,
            ];
            return url('/office/finance/pending-payments?' . http_build_query(array_filter($q)));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> pending invoice(s)</div>
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
<!-- MODAL: Record Payment For Specific Invoice                                -->
<!-- ========================================================================= -->
<div id="quickPayModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'quickPayModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-hand-holding-dollar" style="color: #218765; margin-right: 0.45rem;"></i>
                Record Payment: <span id="quickPayInvoiceNumber"></span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeQuickPayModal()">&times;</button>
        </div>
        <form action="<?= url('/office/invoices/record-payment') ?>" method="POST" id="quickPayForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/pending-payments') ?>">
            <input type="hidden" name="invoice_id" id="qp_invoice_id" value="">
            <div class="modal-body-custom">
                <div style="background: rgba(23, 107, 91, 0.06); padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Customer</span>
                        <strong style="color: var(--vg-navy-900); font-size: 0.92rem;" id="quickPayCustomerName"></strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Remaining Due</span>
                        <strong style="color: #C78A35; font-size: 1.05rem;" id="quickPayRemainingDue"></strong>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="qp_amount">Amount Received (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="qp_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="qp_date">Payment Date <span class="req">*</span></label>
                        <input type="date" name="payment_date" id="qp_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div class="form-group">
                        <label class="form-label" for="qp_method">Payment Mode <span class="req">*</span></label>
                        <select name="payment_method" id="qp_method" class="form-select" required>
                            <option value="cash">Counter Cash</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="qp_account_id">Deposit Into Account <span class="req">*</span></label>
                        <select name="account_id" id="qp_account_id" class="form-select" required>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label class="form-label" for="qp_ref">Transaction / UTR Reference</label>
                    <input type="text" name="payment_reference" id="qp_ref" class="form-control" placeholder="UTR number or UPI ref">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="qp_notes">Notes / Remarks</label>
                    <input type="text" name="notes" id="qp_notes" class="form-control" placeholder="Optional payment remarks...">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeQuickPayModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #218765; border-color: #218765;"><i class="fa-solid fa-check"></i> <span>Confirm Settlement</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function quickPayInvoice(id, invNumber, customer, remaining) {
        document.getElementById('qp_invoice_id').value = id;
        document.getElementById('quickPayInvoiceNumber').textContent = invNumber;
        document.getElementById('quickPayCustomerName').textContent = customer;
        document.getElementById('quickPayRemainingDue').textContent = '₹ ' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('qp_amount').value = remaining.toFixed(2);
        document.getElementById('quickPayModalBackdrop').style.display = 'flex';
    }

    function closeQuickPayModal() {
        document.getElementById('quickPayModalBackdrop').style.display = 'none';
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
