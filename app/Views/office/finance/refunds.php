<?php
/**
 * MANASWI TOURS & TRAVELS — CUSTOMER BOOKING REFUNDS & ADJUSTMENTS
 */
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
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Refunds', 'url' => ''],
            ],
            'actions'     => null,
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
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($totalProcessed) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Returned to guest bank accounts</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/refunds?status=pending') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Processing</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $pendingCount > 0 ? '#D95757' : 'var(--brand-primary)' ?>;">
                <?= format_currency($totalPending) ?>
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
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/refunds') ?>'" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
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

    <!-- 4. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Booking Refunds</h2>
            </div>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/finance/refunds') ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Claims (<?= number_format($totalAll) ?>)</span>
            </a>
            <a href="<?= url('/office/finance/refunds?status=processed') ?>" class="mv-pill <?= $selectedStatus === 'processed' ? 'active' : '' ?>">
                <span>Processed</span>
            </a>
            <a href="<?= url('/office/finance/refunds?status=pending') ?>" class="mv-pill <?= $selectedStatus === 'pending' ? 'active' : '' ?>">
                <span>Pending (<?= $pendingCount ?>)</span>
            </a>
        </div>
    </div>

    <!-- 5. Refunds Modern Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Cancellation &amp; Refund Claims</h3>
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
                        <th style="min-width: 140px;">Booking Code</th>
                        <th style="min-width: 200px;">Customer</th>
                        <th style="min-width: 220px;">Cancellation Reason</th>
                        <th style="width: 120px;">Method</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
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
                                <strong style="font-size: 1rem; color: var(--status-danger);">
                                    <?= format_currency((float)$r['amount']) ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="cust-toggle-pill <?= $stClass ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $stClass ?>"></span>
                                    <span><?= strtoupper($st) ?></span>
                                </span>
                            </td>
                            <td>
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <?php if ($st === 'pending'): ?>
                                            <form action="<?= url('/office/finance/refunds/' . $r['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="processed">
                                                <input type="hidden" name="account_id" value="<?= $accounts[0]['id'] ?? 1 ?>">
                                                <button type="submit" class="action-btn-table" style="color: #218765; width: 100%; border: none; background: none; text-align: left; padding: 0.5rem 0.75rem;">
                                                    <i class="fa-solid fa-check"></i> <span>Process Refund</span>
                                                </button>
                                            </form>
                                            <form action="<?= url('/office/finance/refunds/' . $r['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="action-btn-table delete" style="color: #D95757; width: 100%; border: none; background: none; text-align: left; padding: 0.5rem 0.75rem;">
                                                    <i class="fa-solid fa-xmark"></i> <span>Reject</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div style="font-size: 0.75rem; padding: 0.5rem 0.75rem; color: var(--text-muted);"><?= ucfirst($st) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($r['refund_number']) ?> • <?= e($r['booking_code'] ?: 'Unlinked') ?></span>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;"><?= e($r['reason']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: var(--status-danger); display: block;">
                            <?= format_currency((float)$r['amount']) ?>
                        </strong>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: <?= $st === 'processed' ? '#218765' : '#C78A35' ?>;"><?= e($st) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
    function toggleActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons')) {
            document.querySelectorAll('.action-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });
</script>

