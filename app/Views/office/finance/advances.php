<?php
/**
 * MANASWI TOURS & TRAVELS — EMPLOYEE ADVANCE LOANS & RECOVERIES
 */
$actionsHtml = '<button type="button" class="btn btn-primary" onclick="openAdvanceModal()"><i class="fa-solid fa-hand-holding-dollar"></i> <span>Issue Staff Advance</span></button>';
?>

<div class="bookings-page-view finance-page-view" id="financeAdvancesPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Staff Advance Loans & Recoveries',
            'description' => 'Manage employee cash advances, emergency loan requests, and automated salary payroll recovery schedules.',
            'image'       => 'header-employees.jpg',
            'badge'       => 'Loan Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Staff Advances', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="advancesKpiGrid">
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/advances') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Advances Issued</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_currency($totalIssued) ?></div>
            <div class="booking-kpi-sub">
                <span><?= number_format($totalAll) ?> advance records</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'recovered' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/advances?status=recovered') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Recovered via Payroll</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($totalRecovered) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Recovered in salary runs</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Outstanding Recovery</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_currency($pendingBalance) ?></div>
            <div class="booking-kpi-sub">
                <span>Active loans in recovery</span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedStatus === 'pending' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/advances?status=pending') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Pending Requests</span>
                <span class="booking-kpi-icon" style="background: rgba(217, 87, 87, 0.12); color: #D95757;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $pendingCount > 0 ? '#D95757' : 'var(--brand-primary)' ?>;">
                <?= $pendingCount ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Awaiting supervisor approval</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/advances') ?>" class="filter-bar-compact" id="advanceFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by advance code, employee name, code..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/advances') ?>'" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review</option>
            <option value="approved" <?= ($selectedStatus ?? '') === 'approved' ? 'selected' : '' ?>>Approved &amp; Active</option>
            <option value="recovered" <?= ($selectedStatus ?? '') === 'recovered' ? 'selected' : '' ?>>Fully Recovered</option>
            <option value="rejected" <?= ($selectedStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/advances') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Staff Advances</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openAdvanceModal()">
                <i class="fa-solid fa-plus"></i>
                <span>New Advance</span>
            </button>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/finance/advances') ?>" class="mv-pill <?= empty($selectedStatus) ? 'active' : '' ?>">
                <span>All (<?= number_format($totalAll) ?>)</span>
            </a>
            <a href="<?= url('/office/finance/advances?status=approved') ?>" class="mv-pill <?= $selectedStatus === 'approved' ? 'active' : '' ?>">
                <span>Active</span>
            </a>
            <a href="<?= url('/office/finance/advances?status=pending') ?>" class="mv-pill <?= $selectedStatus === 'pending' ? 'active' : '' ?>">
                <span>Pending (<?= $pendingCount ?>)</span>
            </a>
            <a href="<?= url('/office/finance/advances?status=recovered') ?>" class="mv-pill <?= $selectedStatus === 'recovered' ? 'active' : '' ?>">
                <span>Recovered</span>
            </a>
        </div>
    </div>

    <!-- 5. Advances Modern Table -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Advance Ledgers Manifest</h3>
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
                        <th style="width: 130px;">Advance #</th>
                        <th style="width: 110px;">Date</th>
                        <th style="min-width: 200px;">Employee</th>
                        <th style="min-width: 220px;">Reason / Purpose</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                        <th style="text-align: right; width: 130px;">Recovered</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advances)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No advance requests found</h4>
                                <p class="text-muted" style="margin:0;">Register an advance loan using the button above.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($advances as $adv):
                            $st = strtolower($adv['status']);
                            $stClass = $st === 'recovered' ? 'completed' : ($st === 'approved' ? 'completed' : ($st === 'rejected' ? 'cancelled' : 'pending'));
                        ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; color: var(--brand-primary); font-size: 0.88rem;"><?= e($adv['advance_code']) ?></strong>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                <?= format_date($adv['requested_date']) ?>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.9rem;"><?= e($adv['employee_name']) ?></strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?= e($adv['employee_code']) ?> • <?= e($adv['designation']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; color: var(--vg-navy-900);"><?= e($adv['reason']) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.95rem; color: var(--vg-navy-900);">
                                    <?= format_currency((float)$adv['amount']) ?>
                                </strong>
                            </td>
                            <td style="text-align: right; font-size: 0.88rem; color: var(--status-success); font-weight: 600;">
                                <?= format_currency((float)$adv['recovered_amount']) ?>
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
                                            <form action="<?= url('/office/finance/advances/' . $adv['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="approved">
                                                <input type="hidden" name="account_id" value="<?= $accounts[0]['id'] ?? 1 ?>">
                                                <button type="submit" class="action-btn-table" style="color: #218765; width: 100%; border: none; background: none; text-align: left; padding: 0.5rem 0.75rem;">
                                                    <i class="fa-solid fa-check"></i> <span>Approve &amp; Disburse</span>
                                                </button>
                                            </form>
                                            <form action="<?= url('/office/finance/advances/' . $adv['id'] . '/status') ?>" method="POST" style="margin: 0;">
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
            <?php foreach ($advances as $adv):
                $st = strtolower($adv['status']);
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--vg-navy-900);"><?= e($adv['employee_name']) ?></strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= e($adv['advance_code']) ?> • <?= format_date($adv['requested_date']) ?></span>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;"><?= e($adv['reason']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.05rem; color: var(--vg-navy-900); display: block;">
                            <?= format_currency((float)$adv['amount']) ?>
                        </strong>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: <?= $st === 'recovered' || $st === 'approved' ? '#218765' : '#C78A35' ?>;"><?= e($st) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Issue Staff Advance                                                -->
<!-- ========================================================================= -->
<div id="advanceModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'advanceModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-hand-holding-dollar" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Issue Staff Advance Loan
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeAdvanceModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/advances') ?>" method="POST" id="createAdvanceForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="adv_employee_id">Employee / Chauffeur <span class="req">*</span></label>
                    <select name="employee_id" id="adv_employee_id" class="form-select" required>
                        <option value="">— Select staff member —</option>
                        <?php foreach ($employees as $emp): 
                            $empDisplayName = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) ?: ($emp['name'] ?? 'Employee');
                        ?>
                            <option value="<?= $emp['id'] ?>"><?= e($empDisplayName) ?> (<?= e($emp['employee_code']) ?> • <?= e($emp['designation']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="adv_amount">Advance Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="adv_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="adv_date">Request Date <span class="req">*</span></label>
                        <input type="date" name="requested_date" id="adv_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="adv_reason">Purpose / Emergency Reason <span class="req">*</span></label>
                    <textarea name="reason" id="adv_reason" class="form-control" rows="2" placeholder="e.g. Family medical emergency, festival advance..." required></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeAdvanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Submit Advance</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdvanceModal() {
        document.getElementById('advanceModalBackdrop').style.display = 'flex';
    }
    function closeAdvanceModal() {
        document.getElementById('advanceModalBackdrop').style.display = 'none';
    }
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
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

<style>
.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 35, 30, 0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
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
    max-width: 520px;
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
