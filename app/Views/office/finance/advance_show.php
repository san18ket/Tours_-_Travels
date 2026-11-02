<?php
/**
 * MANASWI Tours & Travels — Staff Advance Loan Voucher Details View
 * Architecturally identical to Transaction Voucher View (office/finance/transactions/1) with Brand Green Theme
 */

$adv = $advance;
$st = strtolower($adv['status'] ?? 'pending');
if (!in_array($st, ['pending', 'approved', 'recovered', 'rejected'], true)) {
    $st = 'pending';
}
$isApproved = ($st === 'approved');
$isPending = ($st === 'pending');
$isRecovered = ($st === 'recovered');
$isRejected = ($st === 'rejected');

$statusClass = ($st === 'approved' || $st === 'recovered') ? 'completed' : ($st === 'rejected' ? 'cancelled' : 'pending');
$statusText = strtoupper($st);

$refCode = $adv['advance_code'] ?: ('ADV-' . str_pad($adv['id'], 5, '0', STR_PAD_LEFT));
$empName = $adv['employee_name'] ?? 'Staff Member';
$empCode = $adv['employee_code'] ?? 'EMP';
$empDesignation = $adv['designation'] ?? 'Staff';
$empDept = $adv['department'] ?? 'Operations';

$amount = (float)($adv['amount'] ?? 0);
$recovered = (float)($adv['recovered_amount'] ?? 0);
$outstanding = max(0.0, $amount - $recovered);
$recoveryPercent = $amount > 0 ? min(100, round(($recovered / $amount) * 100)) : 0;

$approverDisplay = $adv['approver_name'] ?? (!empty($adv['approved_at']) ? 'Authorized Supervisor' : 'Pending Approval');
?>

<div class="transaction-view-root advance-view-root">
    <!-- 1. Top App Bar / Header Actions (Clean layout matching office/finance/transactions/1) -->
    <div class="view-header-bar">
        <a href="<?= url('/office/finance/advances') ?>" class="view-header-btn back-btn" title="Back to Staff Advances" aria-label="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="view-header-right">
            <button type="button" class="view-header-btn" onclick="window.print()" title="Print Advance Voucher" aria-label="Print Voucher">
                <i class="fa-solid fa-print"></i>
            </button>
            <button type="button" class="view-header-btn edit-btn" onclick="openEditAdvModal()" title="Edit Advance Loan" aria-label="Edit Advance">
                <i class="fa-solid fa-pen"></i>
            </button>
            <div class="view-more-menu-wrapper">
                <button type="button" class="view-header-btn more-btn" id="btnAdvMore" onclick="toggleMoreMenu(event)" title="More Options" aria-label="More Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div id="advMoreMenu" class="view-dropdown-menu" style="display: none;">
                    <a href="javascript:void(0)" onclick="openEditAdvModal()" class="view-dropdown-item">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Advance Details
                    </a>
                    <a href="javascript:void(0)" onclick="window.print()" class="view-dropdown-item">
                        <i class="fa-solid fa-print"></i> Print Advance Voucher
                    </a>
                    <a href="<?= url('/office/finance/advances') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-hand-holding-dollar"></i> Staff Advances Ledger
                    </a>
                    <a href="<?= url('/office/finance/payroll') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Payroll Recovery Runs
                    </a>
                    <div class="view-dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="openDeleteAdvModal()" class="view-dropdown-item danger">
                        <i class="fa-regular fa-trash-can"></i> Delete Advance Record
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Hero Advance Card (Scenic Artwork matching office/finance/transactions/1) -->
    <div class="view-hero-card">
        <div class="view-hero-scenery" style="background-image: url('<?= asset('images/customer-scenic-bus.png') ?>');" aria-hidden="true"></div>
        <div class="view-hero-overlay" aria-hidden="true"></div>

        <div class="view-hero-content">
            <!-- Left Icon Avatar -->
            <div class="view-avatar-wrap">
                <div class="view-avatar-initials debit">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="view-avatar-badge" title="Verified Staff Advance Loan Ledger">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- Middle Identity Details -->
            <div class="view-hero-info">
                <div class="view-hero-name-row">
                    <h2 class="view-hero-name"><?= e($refCode) ?></h2>

                    <!-- Live Status Interactive Dropdown Control (Approved, Pending, Recovered, Rejected) -->
                    <div class="hero-status-toggle-wrapper" id="heroStatusToggleWrapper" title="Change Advance Status">
                        <div class="tx-status-interactive-wrap">
                            <button type="button" 
                                    id="heroStatusBadgeBtn"
                                    class="tx-hero-status-btn <?= $statusClass ?>"
                                    onclick="toggleHeroStatusMenu(event)"
                                    title="Click to change status (currently <?= $statusText ?>)">
                                <span class="status-pulse-dot <?= $statusClass ?>"></span>
                                <span class="status-label-text" id="heroStatusLabelText"><?= $statusText ?></span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px; opacity: 0.85;"></i>
                            </button>
                            <div id="heroStatusDropdownMenu" class="tx-hero-status-menu" style="display: none;">
                                <button type="button" class="tx-status-item <?= $isApproved ? 'active' : '' ?>" onclick="setAdvStatusDirect('approved', event)">
                                    <span class="status-pulse-dot completed"></span>
                                    <span>Approved</span>
                                    <?php if ($isApproved): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                                <button type="button" class="tx-status-item <?= $isPending ? 'active' : '' ?>" onclick="setAdvStatusDirect('pending', event)">
                                    <span class="status-pulse-dot pending"></span>
                                    <span>Pending</span>
                                    <?php if ($isPending): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                                <button type="button" class="tx-status-item <?= $isRecovered ? 'active' : '' ?>" onclick="setAdvStatusDirect('recovered', event)">
                                    <span class="status-pulse-dot completed"></span>
                                    <span>Recovered</span>
                                    <?php if ($isRecovered): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                                <button type="button" class="tx-status-item <?= $isRejected ? 'active' : '' ?>" onclick="setAdvStatusDirect('rejected', event)">
                                    <span class="status-pulse-dot cancelled"></span>
                                    <span>Rejected</span>
                                    <?php if ($isRejected): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <span class="view-hero-code-pill debit">STAFF LOAN &amp; ADVANCE</span>
                </div>

                <div class="view-hero-meta-list">
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-user-tie"></i>
                        <span><?= e($empName) ?> (<?= e($empCode) ?> • <?= e($empDesignation) ?>)</span>
                    </div>

                    <div class="view-hero-meta-item">
                        <i class="fa-regular fa-calendar"></i>
                        <span>Requested: <?= format_date($adv['requested_date']) ?></span>
                    </div>

                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Approver: <?= e($approverDisplay) ?></span>
                    </div>

                    <?php if (!empty($adv['approved_at'])): ?>
                    <div class="view-hero-meta-item">
                        <i class="fa-regular fa-clock"></i>
                        <span>Approved: <?= format_datetime($adv['approved_at']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Quick Action Pills (Matching office/finance/transactions/1) -->
                <div class="view-hero-quick-actions">
                    <button type="button" onclick="window.print()" class="hero-qa-pill qa-print" title="Print Advance">
                        <i class="fa-solid fa-print"></i>
                        <span>Print</span>
                    </button>
                    <button type="button" onclick="openEditAdvModal()" class="hero-qa-pill qa-edit" title="Edit Advance Loan">
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                    </button>
                    <a href="<?= url('/office/finance/advances') ?>" class="hero-qa-pill qa-ledger" title="All Staff Advances">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Advances</span>
                    </a>
                    <a href="<?= url('/office/finance/payroll') ?>" class="hero-qa-pill" title="Payroll Run">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Payroll</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. 4 KPI Metric Cards (Matching office/finance/transactions/1 with Green Theme) -->
    <div class="view-kpi-grid">
        <!-- KPI 1: Advance Amount -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap amber">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="kpi-trend neutral">
                    <i class="fa-solid fa-hand-holding-dollar"></i> LOAN
                </div>
            </div>
            <div class="kpi-label">Advance Principal</div>
            <div class="kpi-value text-currency" style="color: #0F172A;">
                <?= format_currency($amount) ?>
            </div>
            <div class="kpi-footer-note">Disbursed staff advance amount</div>
        </div>

        <!-- KPI 2: Recovered Amount -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="kpi-trend up">
                    <i class="fa-solid fa-chart-line"></i> <?= $recoveryPercent ?>%
                </div>
            </div>
            <div class="kpi-label">Recovered To Date</div>
            <div class="kpi-value text-currency" style="color: #218765;">
                <?= format_currency($recovered) ?>
            </div>
            <div class="kpi-footer-note">Deducted via payroll salary runs</div>
        </div>

        <!-- KPI 3: Outstanding Balance -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap amber">
                    <i class="fa-solid fa-scale-unbalanced"></i>
                </div>
                <span class="kpi-tag-recent"><?= $outstanding > 0 ? 'Active' : 'Settled' ?></span>
            </div>
            <div class="kpi-label">Remaining Balance</div>
            <div class="kpi-value" style="color: <?= $outstanding > 0 ? '#C78A35' : '#218765' ?>;">
                <?= format_currency($outstanding) ?>
            </div>
            <div class="kpi-footer-note">Scheduled for monthly deduction</div>
        </div>

        <!-- KPI 4: Settlement Status -->
        <div class="view-kpi-card" onclick="switchTab('audit')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span class="kpi-tag-recent">Audit</span>
            </div>
            <div class="kpi-label">Settlement Status</div>
            <div class="kpi-value" id="kpiStatusValue" style="font-size: 1.15rem;">
                <span class="cust-toggle-pill <?= $statusClass ?>">
                    <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                    <span class="status-txt-kpi"><?= $statusText ?></span>
                </span>
            </div>
            <div class="kpi-footer-note">Payroll ledger verified</div>
        </div>
    </div>

    <!-- 4. Horizontal Navigation Pill Tabs (Smooth Swipe on Mobile) -->
    <div class="view-nav-tabs-wrapper">
        <div class="view-nav-tabs" role="tablist">
            <button type="button" class="view-nav-pill active" data-tab="overview" onclick="switchTab('overview')" role="tab" aria-selected="true">
                <i class="fa-solid fa-circle-info"></i>
                <span>Loan Overview</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="employee" onclick="switchTab('employee')" role="tab" aria-selected="false">
                <i class="fa-solid fa-user-tie"></i>
                <span>Staff Profile</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="audit" onclick="switchTab('audit')" role="tab" aria-selected="false">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Payroll &amp; Audit Trail</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: OVERVIEW
         ========================================================================= -->
    <div id="tabContent-overview" class="view-tab-pane active" role="tabpanel">
        <div class="view-layout-split">
            <!-- Left Column: Primary Advance Information & Remarks -->
            <div class="view-col-main">
                <!-- 1. Advance Loan Information Card -->
                <div class="view-section-card card-tx-info">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <h3 class="section-title">Advance Loan Voucher Information</h3>
                        </div>
                        <button type="button" onclick="openEditAdvModal()" class="section-header-link-btn" title="Edit advance details">
                            <i class="fa-solid fa-pen"></i> <span>Edit</span>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <!-- Row 1 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Advance Voucher ID</span>
                                <span class="view-kv-value bold monospace" style="font-size: 0.95rem; color: #176B5B;">
                                    <?= e($refCode) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Operational Status</span>
                                <div class="view-kv-value">
                                    <div class="tx-status-interactive-wrap">
                                        <button type="button" 
                                                id="inlineStatusBadgeBtn"
                                                class="tx-hero-status-btn <?= $statusClass ?>"
                                                onclick="toggleInlineStatusMenu(event)"
                                                title="Click to change status">
                                            <span class="status-pulse-dot <?= $statusClass ?>"></span>
                                            <span class="status-label-text" id="inlineStatusLabelText"><?= $statusText ?></span>
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.68rem; margin-left: 4px; opacity: 0.85;"></i>
                                        </button>
                                        <div id="inlineStatusDropdownMenu" class="tx-hero-status-menu" style="display: none;">
                                            <button type="button" class="tx-status-item <?= $isApproved ? 'active' : '' ?>" onclick="setAdvStatusDirect('approved', event)">
                                                <span class="status-pulse-dot completed"></span>
                                                <span>Approved</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $isPending ? 'active' : '' ?>" onclick="setAdvStatusDirect('pending', event)">
                                                <span class="status-pulse-dot pending"></span>
                                                <span>Pending</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $isRecovered ? 'active' : '' ?>" onclick="setAdvStatusDirect('recovered', event)">
                                                <span class="status-pulse-dot completed"></span>
                                                <span>Recovered</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $isRejected ? 'active' : '' ?>" onclick="setAdvStatusDirect('rejected', event)">
                                                <span class="status-pulse-dot cancelled"></span>
                                                <span>Rejected</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Staff Member</span>
                                <span class="view-kv-value bold" style="color: #176B5B;">
                                    <i class="fa-solid fa-user-tie" style="margin-right: 5px;"></i>
                                    <?= e($empName) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Staff Code &amp; Designation</span>
                                <span class="view-kv-value bold">
                                    <?= e($empCode) ?> • <?= e($empDesignation) ?>
                                    <span style="font-size: 0.75rem; color: #64748B; font-weight: 600;">(<?= e($empDept) ?>)</span>
                                </span>
                            </div>

                            <!-- Row 3 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Total Principal Amount</span>
                                <span class="view-kv-value bold" style="font-size: 1.05rem; color: #0F172A;">
                                    <?= format_currency($amount) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Recovered Amount</span>
                                <span class="view-kv-value bold" style="color: #218765;">
                                    <?= format_currency($recovered) ?> (<?= $recoveryPercent ?>%)
                                </span>
                            </div>

                            <!-- Row 4 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Remaining Recovery Balance</span>
                                <span class="view-kv-value bold" style="color: <?= $outstanding > 0 ? '#C78A35' : '#218765' ?>;">
                                    <?= format_currency($outstanding) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Requested Date</span>
                                <span class="view-kv-value"><?= format_date($adv['requested_date']) ?></span>
                            </div>

                            <!-- Row 5 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Approving Authority</span>
                                <span class="view-kv-value bold"><?= e($approverDisplay) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Approval Timestamp</span>
                                <span class="view-kv-value"><?= !empty($adv['approved_at']) ? format_datetime($adv['approved_at']) : 'Pending Approval' ?></span>
                            </div>

                            <!-- Row 6 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Creation Timestamp</span>
                                <span class="view-kv-value"><?= !empty($adv['created_at']) && $adv['created_at'] !== '0000-00-00 00:00:00' ? format_datetime($adv['created_at']) : format_date($adv['requested_date']) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Last Ledger Audit Update</span>
                                <span class="view-kv-value"><?= !empty($adv['updated_at']) && $adv['updated_at'] !== '0000-00-00 00:00:00' ? format_datetime($adv['updated_at']) : '—' ?></span>
                            </div>
                        </div>

                        <!-- Policy & Double-Entry Reassurance Box -->
                        <div class="view-account-audit-box">
                            <div class="account-audit-label">
                                <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i>
                                <span>Automated Payroll Recovery Protocol</span>
                            </div>
                            <div class="account-audit-text">
                                Approved staff advances are automatically scheduled for monthly salary deduction during standard payroll processing (up to 15% of gross earnings per cycle until fully recovered).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Purpose & Internal Audit Notes Card -->
                <div class="view-section-card card-tx-notes">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-regular fa-clipboard"></i>
                            </div>
                            <h3 class="section-title">Emergency Purpose &amp; Internal Audit Notes</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div style="margin-bottom: 1rem;">
                            <span class="view-kv-label" style="display: block; margin-bottom: 0.35rem;">Stated Advance Purpose:</span>
                            <div class="tx-full-notes-content" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem;">
                                <p style="margin: 0; font-size: 0.92rem; color: #1E293B; line-height: 1.6; white-space: pre-wrap; font-weight: 600;"><?= e($adv['reason']) ?></p>
                            </div>
                        </div>

                        <?php if (!empty($adv['notes'])): ?>
                            <div>
                                <span class="view-kv-label" style="display: block; margin-bottom: 0.35rem;">Internal Comments &amp; Notes:</span>
                                <div class="tx-full-notes-content" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 0.85rem 1rem;">
                                    <p style="margin: 0; font-size: 0.88rem; color: #92400E; line-height: 1.5; white-space: pre-wrap;"><?= e($adv['notes']) ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 1rem; color: var(--text-muted);">
                                <div style="font-size: 0.82rem;">No supervisor remarks recorded. You can add internal notes by clicking <strong>Edit</strong>.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Staff Profile Card, Linked Journal Context & Quick Actions -->
            <div class="view-col-side">
                <!-- 3. Employee Profile Node Card -->
                <div class="view-section-card card-account-side">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            <h3 class="section-title">Beneficiary Staff Node</h3>
                        </div>
                        <button type="button" onclick="switchTab('employee')" class="section-header-link" title="Open staff profile">
                            <span>Full Profile</span> <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div class="vault-node-box">
                            <div class="vault-node-top">
                                <div class="vault-node-icon">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                                <div class="vault-node-info">
                                    <h4 class="vault-node-title"><?= e($empName) ?></h4>
                                    <span class="vault-node-sub"><?= e($empCode) ?> • <?= e($empDesignation) ?></span>
                                </div>
                            </div>

                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-building"></i> Department</span>
                                <span class="vm-val"><?= e($empDept) ?></span>
                            </div>
                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-phone"></i> Contact Phone</span>
                                <span class="vm-val monospace"><?= !empty($adv['employee_phone']) ? e($adv['employee_phone']) : '—' ?></span>
                            </div>
                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-envelope"></i> Email Address</span>
                                <span class="vm-val"><?= !empty($adv['employee_email']) ? e($adv['employee_email']) : '—' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Linked Journal Disbursement Voucher Card (If disbursed) -->
                <?php if (!empty($linkedTransaction)): ?>
                <div class="view-section-card card-linked-ref">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge amber">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <h3 class="section-title">Linked General Ledger Payout</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div class="linked-audit-item">
                            <div class="lai-title">
                                <i class="fa-solid fa-building-columns" style="color: #176B5B;"></i> 
                                Voucher: <strong><?= e($linkedTransaction['transaction_ref'] ?: ('TX-#' . $linkedTransaction['id'])) ?></strong>
                            </div>
                            <div class="lai-detail">Treasury Account: <strong><?= e($linkedTransaction['account_name'] ?? 'Bank Vault') ?></strong></div>
                            <div class="lai-detail">Disbursement Amount: <strong><?= format_currency((float)$linkedTransaction['amount']) ?></strong></div>
                            <div class="lai-detail">Transacted Date: <strong><?= format_datetime($linkedTransaction['transacted_at']) ?></strong></div>
                            <div style="margin-top: 8px;">
                                <a href="<?= url('/office/finance/transactions/' . $linkedTransaction['id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                    View Ledger Voucher <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 5. Quick Actions Grid (Direct match with office/finance/transactions/1) -->
                <div class="view-section-card card-quick-actions">
                    <div class="section-card-header compact">
                        <h4 class="quick-actions-title">Quick Actions</h4>
                    </div>
                    <div class="section-card-body pt-0">
                        <div class="quick-actions-grid">
                            <button type="button" class="quick-action-tile" onclick="openEditAdvModal()" title="Edit Advance Loan">
                                <div class="qa-icon-wrap purple">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </div>
                                <span class="qa-text">Edit Details</span>
                            </button>

                            <button type="button" class="quick-action-tile" onclick="switchTab('employee')" title="Staff Member Info">
                                <div class="qa-icon-wrap green">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                                <span class="qa-text">Staff Profile</span>
                            </button>

                            <button type="button" class="quick-action-tile" onclick="window.print()" title="Print Advance Voucher">
                                <div class="qa-icon-wrap cyan">
                                    <i class="fa-solid fa-print"></i>
                                </div>
                                <span class="qa-text">Print Slip</span>
                            </button>

                            <a href="<?= url('/office/finance/advances') ?>" class="quick-action-tile" title="Staff Advances Ledger">
                                <div class="qa-icon-wrap amber">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <span class="qa-text">Loan Ledger</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: EMPLOYEE PROFILE
         ========================================================================= -->
    <div id="tabContent-employee" class="view-tab-pane" role="tabpanel" style="display: none;">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h3 class="section-title">Staff Member Profile Specifications</h3>
                </div>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Full Name</span>
                        <span class="view-kv-value bold"><?= e($empName) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Staff Code</span>
                        <span class="view-kv-value bold monospace"><?= e($empCode) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Designation</span>
                        <span class="view-kv-value"><?= e($empDesignation) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Department</span>
                        <span class="view-kv-value bold"><?= e($empDept) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Contact Phone</span>
                        <span class="view-kv-value monospace"><?= !empty($adv['employee_phone']) ? e($adv['employee_phone']) : '—' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Email Address</span>
                        <span class="view-kv-value"><?= !empty($adv['employee_email']) ? e($adv['employee_email']) : '—' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Joining Date</span>
                        <span class="view-kv-value"><?= !empty($adv['joining_date']) ? format_date($adv['joining_date']) : '—' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Total Outstanding Advances</span>
                        <span class="view-kv-value bold" style="color: #C78A35;"><?= format_currency($outstanding) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: PAYROLL DEDUCTIONS & AUDIT TRAIL
         ========================================================================= -->
    <div id="tabContent-audit" class="view-tab-pane" role="tabpanel" style="display: none;">
        <!-- Payroll Deduction Ledger -->
        <div class="view-section-card" style="margin-bottom: 1.25rem;">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3 class="section-title">Payroll Advance Deduction Ledger</h3>
                </div>
                <a href="<?= url('/office/finance/payroll') ?>" class="section-header-link">
                    <span>Payroll Manifest</span> <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
            <div class="section-card-body">
                <?php if (!empty($deductions)): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Payroll Run</th>
                                <th>Period</th>
                                <th style="text-align: right;">Advance Deducted</th>
                                <th style="text-align: right;">Net Payout</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deductions as $d): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #176B5B;">
                                    <?= e($d['payroll_code'] ?? ('PAY-' . $d['id'])) ?>
                                </td>
                                <td>
                                    <?= date('F', mktime(0, 0, 0, $d['month'], 10)) ?> <?= e($d['year']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #218765;">
                                    <?= format_currency((float)$d['advance_deducted']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    <?= format_currency((float)$d['net_salary']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="cust-toggle-pill completed"><?= strtoupper($d['payroll_status'] ?? 'PAID') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                    <i class="fa-regular fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.4;"></i>
                    <div style="font-size: 0.92rem; font-weight: 700; color: #1E293B;">No salary deductions processed yet</div>
                    <p style="font-size: 0.82rem; margin: 4px 0 0 0;">Once payroll is generated, automatic recovery deductions will appear here.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Technical Specs & Audit Trail -->
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <h3 class="section-title">Technical Specifications &amp; Audit Trail</h3>
                </div>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Primary Advance Record ID</span>
                        <span class="view-kv-value bold">#<?= (int)$adv['id'] ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Advance Code</span>
                        <span class="view-kv-value bold monospace"><?= e($adv['advance_code']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">System Insertion Timestamp</span>
                        <span class="view-kv-value"><?= !empty($adv['created_at']) && $adv['created_at'] !== '0000-00-00 00:00:00' ? format_datetime($adv['created_at']) : format_date($adv['requested_date']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Requested Date</span>
                        <span class="view-kv-value"><?= format_date($adv['requested_date']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Approved By User</span>
                        <span class="view-kv-value bold"><?= e($approverDisplay) ?> <?= !empty($adv['approved_by']) ? '(ID: #' . (int)$adv['approved_by'] . ')' : '' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Approved Timestamp</span>
                        <span class="view-kv-value"><?= !empty($adv['approved_at']) ? format_datetime($adv['approved_at']) : '—' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Floating Quick Action Button (FAB) matching transactions/1 -->
<div class="mobile-view-fab-wrapper">
    <button type="button" class="mobile-view-fab" onclick="toggleFabMenu(event)" title="Quick Actions" aria-label="Quick Actions">
        <i class="fa-solid fa-bolt" id="fabIcon"></i>
    </button>
    <div class="mobile-fab-menu" id="mobileFabMenu" style="display: none;">
        <button type="button" class="mobile-fab-item" onclick="openEditAdvModal()">
            <span class="fab-item-label">Edit Advance</span>
            <span class="fab-item-icon primary"><i class="fa-solid fa-pen-to-square"></i></span>
        </button>
        <button type="button" class="mobile-fab-item" onclick="window.print()">
            <span class="fab-item-label">Print Slip</span>
            <span class="fab-item-icon green"><i class="fa-solid fa-print"></i></span>
        </button>
        <a href="<?= url('/office/finance/advances') ?>" class="mobile-fab-item">
            <span class="fab-item-label">Staff Advances</span>
            <span class="fab-item-icon amber"><i class="fa-solid fa-hand-holding-dollar"></i></span>
        </a>
        <a href="<?= url('/office/finance/payroll') ?>" class="mobile-fab-item">
            <span class="fab-item-label">Payroll Runs</span>
            <span class="fab-item-icon purple"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT MODAL                                                                -->
<!-- ========================================================================= -->
<div id="editAdvModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editAdvModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge amber"><i class="fa-solid fa-pen-to-square"></i></span>
                <span>Edit Staff Advance Loan</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditAdvModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/advances/' . $adv['id']) ?>" method="POST" id="editAdvForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/advances/' . $adv['id']) ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Employee</label>
                    <input type="text" class="form-control" value="<?= e($empName) ?> (<?= e($empCode) ?> • <?= e($empDesignation) ?>)" readonly style="background: #F8FAFC; color: #475569; font-weight: 600;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_amount">Advance Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="edit_amount" class="form-control" value="<?= (float)$adv['amount'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_requested_date">Request Date <span class="req">*</span></label>
                        <input type="date" name="requested_date" id="edit_requested_date" class="form-control" value="<?= e($adv['requested_date']) ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_status">Operational Status</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="pending" <?= $st === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="approved" <?= $st === 'approved' ? 'selected' : '' ?>>Approved &amp; Active</option>
                        <option value="recovered" <?= $st === 'recovered' ? 'selected' : '' ?>>Fully Recovered</option>
                        <option value="rejected" <?= $st === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_reason">Purpose / Emergency Reason <span class="req">*</span></label>
                    <textarea name="reason" id="edit_reason" class="form-control" rows="2" required><?= e($adv['reason']) ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_notes">Internal Audit Remarks</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="Approval or payroll comments..."><?= e($adv['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditAdvModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Changes</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DELETE MODAL                                                              -->
<!-- ========================================================================= -->
<div id="deleteAdvModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'deleteAdvModalBackdrop')">
    <div class="modal-dialog-custom modal-dialog-delete">
        <div class="modal-header-custom delete-header">
            <div class="delete-icon-pulse">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #DC2626;">Delete Staff Advance</h3>
            <button type="button" class="modal-close-custom" onclick="closeDeleteAdvModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/advances/' . $adv['id'] . '/delete') ?>" method="POST" id="deleteAdvForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/advances') ?>">
            <div class="modal-body-custom">
                <p style="margin: 0 0 1rem 0; color: #334155; font-size: 0.92rem; line-height: 1.5;">
                    Are you sure you want to delete advance loan <strong style="color: #0F172A; font-family: monospace;"><?= e($refCode) ?></strong> for <strong><?= e($empName) ?></strong> (<?= format_currency($amount) ?>)?
                </p>
                <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; gap: 0.65rem;">
                    <i class="fa-solid fa-circle-exclamation" style="color: #DC2626; margin-top: 2px;"></i>
                    <div style="font-size: 0.82rem; color: #991B1B; line-height: 1.4;">
                        <strong>Warning:</strong> This permanently removes the advance record from payroll deduction schedules.
                    </div>
                </div>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="deleteAdvConfirmChk" onchange="toggleDeleteAdvBtn()">
                    <span>I understand that this action cannot be undone.</span>
                </label>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeDeleteAdvModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" id="deleteAdvSubmitBtn" disabled>
                    <i class="fa-solid fa-trash-can"></i> <span>Delete Advance</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. Tab Switching
    function switchTab(tabId) {
        document.querySelectorAll('.view-nav-pill').forEach(btn => {
            const isActive = btn.getAttribute('data-tab') === tabId;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            if (pane.id === 'tabContent-' + tabId) {
                pane.style.display = 'block';
                pane.classList.add('active');
            } else {
                pane.style.display = 'none';
                pane.classList.remove('active');
            }
        });
    }

    // 2. More Menu
    function toggleMoreMenu(e) {
        e.stopPropagation();
        const menu = document.getElementById('advMoreMenu');
        if (menu) menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
    }

    // 3. Status Dropdowns
    function toggleHeroStatusMenu(e) {
        e.stopPropagation();
        const menu = document.getElementById('heroStatusDropdownMenu');
        if (menu) menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
    }
    function toggleInlineStatusMenu(e) {
        e.stopPropagation();
        const menu = document.getElementById('inlineStatusDropdownMenu');
        if (menu) menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
    }

    function closeAllStatusMenus() {
        const hm = document.getElementById('heroStatusDropdownMenu');
        if (hm) hm.style.display = 'none';
        const im = document.getElementById('inlineStatusDropdownMenu');
        if (im) im.style.display = 'none';
        const mm = document.getElementById('advMoreMenu');
        if (mm) mm.style.display = 'none';
        const fm = document.getElementById('mobileFabMenu');
        if (fm) fm.style.display = 'none';
    }

    // 4. Direct Status Update via AJAX
    function setAdvStatusDirect(targetStatus, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        closeAllStatusMenus();

        const url = '<?= url('/office/finance/advances/' . $adv['id']) ?>/toggle-status';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                '_csrf_token': csrfToken,
                'status': targetStatus
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                applyStatusUpdate(data.status);
            } else {
                alert(data.message || 'Could not update status.');
            }
        })
        .catch(err => {
            console.error('Error updating status:', err);
            alert('A network error occurred.');
        });
    }

    function applyStatusUpdate(newStatus) {
        const st = (newStatus || 'pending').toLowerCase();
        let statusClass = 'pending';
        let statusText = 'PENDING';
        if (st === 'approved') {
            statusClass = 'completed';
            statusText = 'APPROVED';
        } else if (st === 'recovered') {
            statusClass = 'completed';
            statusText = 'RECOVERED';
        } else if (st === 'rejected') {
            statusClass = 'cancelled';
            statusText = 'REJECTED';
        } else {
            statusClass = 'pending';
            statusText = 'PENDING';
        }

        // Hero badge
        const heroBtn = document.getElementById('heroStatusBadgeBtn');
        if (heroBtn) {
            heroBtn.className = 'tx-hero-status-btn ' + statusClass;
            const dot = heroBtn.querySelector('.status-pulse-dot');
            if (dot) dot.className = 'status-pulse-dot ' + statusClass;
            const txt = document.getElementById('heroStatusLabelText');
            if (txt) txt.textContent = statusText;
        }

        // Inline badge
        const inlineBtn = document.getElementById('inlineStatusBadgeBtn');
        if (inlineBtn) {
            inlineBtn.className = 'tx-hero-status-btn ' + statusClass;
            const dot = inlineBtn.querySelector('.status-pulse-dot');
            if (dot) dot.className = 'status-pulse-dot ' + statusClass;
            const txt = document.getElementById('inlineStatusLabelText');
            if (txt) txt.textContent = statusText;
        }

        // KPI card status
        const kpiPill = document.querySelector('#kpiStatusValue .cust-toggle-pill');
        if (kpiPill) {
            kpiPill.className = 'cust-toggle-pill ' + statusClass;
            const dot = kpiPill.querySelector('.toggle-pulse-dot');
            if (dot) dot.className = 'toggle-pulse-dot ' + statusClass;
            const txt = kpiPill.querySelector('.status-txt-kpi');
            if (txt) txt.textContent = statusText;
        }
    }

    // 5. FAB Menu
    function toggleFabMenu(e) {
        e.stopPropagation();
        const menu = document.getElementById('mobileFabMenu');
        if (menu) menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'flex' : 'none';
    }

    // 6. Modals
    function openEditAdvModal() {
        closeAllStatusMenus();
        document.getElementById('editAdvModalBackdrop').style.display = 'flex';
    }
    function closeEditAdvModal() {
        document.getElementById('editAdvModalBackdrop').style.display = 'none';
    }

    function openDeleteAdvModal() {
        closeAllStatusMenus();
        const chk = document.getElementById('deleteAdvConfirmChk');
        const btn = document.getElementById('deleteAdvSubmitBtn');
        if (chk) chk.checked = false;
        if (btn) btn.disabled = true;
        document.getElementById('deleteAdvModalBackdrop').style.display = 'flex';
    }
    function closeDeleteAdvModal() {
        document.getElementById('deleteAdvModalBackdrop').style.display = 'none';
    }
    function toggleDeleteAdvBtn() {
        const chk = document.getElementById('deleteAdvConfirmChk');
        const btn = document.getElementById('deleteAdvSubmitBtn');
        if (chk && btn) btn.disabled = !chk.checked;
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }

    // Dismiss popups on click outside
    document.addEventListener('click', function(e) {
        closeAllStatusMenus();
    });
</script>

<style>
/* ========================================================================= */
/* SCOPED VIEW STYLES (Exact match to office/finance/transactions/1)         */
/* ========================================================================= */
.advance-view-root, .transaction-view-root {
    --cv-primary-green: #176B5B;
    --cv-dark-green: #16443B;
    --cv-accent-green: #218765;
    --cv-navy-900: #0F172A;
    --cv-slate-800: #1E293B;
    --cv-slate-600: #475569;
    --cv-slate-400: #94A3B8;
    --cv-border-light: #E2E8F0;
    --cv-bg-light: #F8FAFC;
    --cv-radius-card: 18px;
    --cv-card-border: rgba(22, 68, 59, 0.12);

    max-width: 1320px;
    margin: 0 auto;
    padding: 1.25rem 1rem 5rem 1rem;
    box-sizing: border-box;
    font-family: inherit;
}

/* 1. Header Bar */
.view-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 40;
    margin-bottom: 1rem;
}
.view-header-right {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}
.view-header-btn {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    color: var(--cv-slate-600);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
    text-decoration: none;
}
.view-header-btn:hover {
    background: #E8F7F0;
    color: var(--cv-primary-green);
    border-color: rgba(23, 107, 91, 0.3);
    transform: translateY(-1px);
}
.view-more-menu-wrapper {
    position: relative;
}
.view-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    min-width: 220px;
    padding: 6px;
    z-index: 1050;
    animation: fadeInDropdown 0.18s ease-out;
}
@keyframes fadeInDropdown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.view-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 0.85rem;
    color: #334155;
    font-size: 0.86rem;
    font-weight: 500;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.15s ease;
}
.view-dropdown-item:hover {
    background: #F1F5F9;
    color: var(--cv-primary-green);
}
.view-dropdown-item.danger {
    color: #DC2626;
}
.view-dropdown-item.danger:hover {
    background: #FEF2F2;
    color: #991B1B;
}
.view-dropdown-divider {
    height: 1px;
    background: #F1F5F9;
    margin: 4px 0;
}

/* 2. Hero Card */
.view-hero-card {
    position: relative;
    background: #FFFFFF;
    border-radius: var(--cv-radius-card);
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 4px 20px rgba(22, 68, 59, 0.08);
    overflow: hidden;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.25rem;
}
.view-hero-scenery {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 45%;
    max-width: 480px;
    background-size: cover;
    background-position: center right;
    background-repeat: no-repeat;
    opacity: 0.85;
    pointer-events: none;
    z-index: 1;
}
.view-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, #FFFFFF 0%, rgba(255, 255, 255, 0.96) 45%, rgba(255, 255, 255, 0.65) 75%, rgba(255, 255, 255, 0.15) 100%);
    z-index: 2;
    pointer-events: none;
}
.view-hero-content {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.view-avatar-wrap {
    position: relative;
    width: 86px;
    height: 86px;
    border-radius: 22px;
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(22, 68, 59, 0.18);
}
.view-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
    color: #FFFFFF;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
}
.view-avatar-initials.debit {
    background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
}
.view-avatar-badge {
    position: absolute;
    bottom: -3px;
    right: -3px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #FFFFFF;
    color: #218765;
    border: 2px solid #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}
.view-hero-info {
    flex: 1;
    min-width: 0;
}
.view-hero-name-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}
.view-hero-name {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    letter-spacing: -0.01em;
}
.view-hero-code-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 9999px;
    background: #E8F7F0;
    color: #176B5B;
    font-size: 0.75rem;
    font-weight: 700;
}
.view-hero-code-pill.debit {
    background: #FEE2E2;
    color: #DC2626;
}
.tx-status-interactive-wrap {
    position: relative;
    display: inline-block;
}
.tx-hero-status-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 9999px;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: 0.76rem;
    font-weight: 700;
    transition: all 0.15s ease;
}
.tx-hero-status-btn.completed {
    background: #E8F7F0;
    border-color: rgba(33, 135, 101, 0.3);
    color: #218765;
}
.tx-hero-status-btn.pending {
    background: #FEF3C7;
    border-color: rgba(217, 119, 6, 0.3);
    color: #B45309;
}
.tx-hero-status-btn.cancelled {
    background: #FEE2E2;
    border-color: rgba(220, 38, 38, 0.3);
    color: #DC2626;
}
.tx-hero-status-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.tx-hero-status-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    padding: 5px;
    min-width: 150px;
    z-index: 1060;
}
.tx-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 7px 10px;
    background: transparent;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #1E293B;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
}
.tx-status-item:hover {
    background: #F8FAFC;
}
.tx-status-item.active {
    background: #E8F7F0;
    color: #176B5B;
    font-weight: 700;
}
.tx-status-item .check-ico {
    margin-left: auto;
    font-size: 0.75rem;
    color: #176B5B;
}
.status-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.status-pulse-dot.completed {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}
.status-pulse-dot.pending {
    background: #F59E0B;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25);
}
.status-pulse-dot.cancelled {
    background: #EF4444;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
}
.view-hero-meta-list {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    color: var(--cv-slate-600);
    font-size: 0.84rem;
    margin-bottom: 0.65rem;
}
.view-hero-meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.view-hero-meta-item i {
    color: var(--cv-primary-green);
    font-size: 0.85rem;
}
.view-hero-quick-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.hero-qa-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 9999px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: var(--cv-slate-800);
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.15s ease;
}
.hero-qa-pill:hover {
    background: #E8F7F0;
    color: var(--cv-primary-green);
    border-color: rgba(23, 107, 91, 0.25);
    transform: translateY(-1px);
}

/* 3. KPI Grid */
.view-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.view-kpi-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid var(--cv-card-border);
    padding: 1.15rem 1.1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    cursor: pointer;
    transition: all 0.2s ease;
}
.view-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(22, 68, 59, 0.08);
    border-color: rgba(23, 107, 91, 0.25);
}
.kpi-card-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.55rem;
}
.kpi-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}
.kpi-icon-wrap.green {
    background: #E8F7F0;
    color: #176B5B;
}
.kpi-icon-wrap.amber {
    background: #FEF3C7;
    color: #B45309;
}
.kpi-trend {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.kpi-trend.up {
    background: #E8F7F0;
    color: #218765;
}
.kpi-trend.neutral {
    background: #F1F5F9;
    color: #64748B;
}
.kpi-tag-recent {
    font-size: 0.72rem;
    font-weight: 700;
    background: #F1F5F9;
    color: #475569;
    padding: 2px 7px;
    border-radius: 9999px;
}
.kpi-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.2rem;
}
.kpi-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.01em;
    line-height: 1.2;
}
.kpi-footer-note {
    font-size: 0.72rem;
    color: #94A3B8;
    margin-top: 0.35rem;
}

/* 4. Tabs */
.view-nav-tabs-wrapper {
    margin-bottom: 1.25rem;
}
.view-nav-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 5px;
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: 14px;
    overflow-x: auto;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
.view-nav-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.15rem;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #64748B;
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.18s ease;
}
.view-nav-pill:hover {
    color: var(--cv-primary-green);
    background: #F8FAFC;
}
.view-nav-pill.active {
    background: #E8F7F0;
    color: #176B5B;
    font-weight: 700;
}

/* 5. Split Content Layout */
.view-layout-split {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 1.25rem;
    align-items: start;
}
.view-col-main, .view-col-side {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.view-section-card {
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: var(--cv-radius-card);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}
.section-card-header {
    padding: 1.1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #F1F5F9;
}
.section-card-header.compact {
    padding: 0.9rem 1.25rem;
}
.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}
.section-icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}
.section-icon-badge.green {
    background: #E8F7F0;
    color: #176B5B;
}
.section-icon-badge.amber {
    background: #FEF3C7;
    color: #B45309;
}
.section-title {
    font-size: 0.96rem;
    font-weight: 700;
    color: var(--cv-navy-900);
    margin: 0;
}
.section-header-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--cv-primary-green);
    background: #E8F7F0;
    border: none;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.section-header-link-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
}
.section-header-link {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--cv-primary-green);
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.section-header-link:hover {
    text-decoration: underline;
}
.section-card-body {
    padding: 1.25rem;
}
.view-kv-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.25rem;
}
.view-kv-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}
.view-kv-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.view-kv-value {
    font-size: 0.92rem;
    color: #1E293B;
    word-break: break-word;
}
.view-kv-value.bold {
    font-weight: 700;
    color: #0F172A;
}
.view-kv-value.monospace, .monospace {
    font-family: monospace;
}
.view-account-audit-box {
    margin-top: 1.1rem;
    padding-top: 1rem;
    border-top: 1px solid #F1F5F9;
}
.account-audit-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.account-audit-text {
    font-size: 0.85rem;
    color: var(--cv-slate-600);
    line-height: 1.5;
}

/* Vault Node Box */
.vault-node-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1.15rem;
}
.vault-node-top {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #E2E8F0;
}
.vault-node-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #E8F7F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.vault-node-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0F172A;
}
.vault-node-sub {
    font-size: 0.78rem;
    color: #64748B;
}
.vault-metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.84rem;
    padding: 0.45rem 0;
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}
.vault-metric-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.vm-label {
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.vm-val {
    font-weight: 600;
    color: #0F172A;
}
.vm-val.bold {
    font-weight: 800;
}

/* Linked Audit Box */
.linked-audit-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 1rem;
}
.lai-title {
    font-size: 0.9rem;
    color: #0F172A;
    margin-bottom: 0.35rem;
}
.lai-detail {
    font-size: 0.82rem;
    color: #475569;
    margin-bottom: 0.25rem;
}

/* Quick Actions Grid */
.quick-actions-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
}
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}
.quick-action-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.1rem 0.75rem;
    border-radius: 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    color: var(--cv-slate-800);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}
.quick-action-tile:hover {
    background: #FFFFFF;
    border-color: var(--cv-primary-green);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(22, 68, 59, 0.08);
}
.qa-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.qa-icon-wrap.purple { background: #EDE9FE; color: #7C3AED; }
.qa-icon-wrap.green  { background: #E8F7F0; color: #176B5B; }
.qa-icon-wrap.cyan   { background: #CFFAFE; color: #0891B2; }
.qa-icon-wrap.amber  { background: #FEF3C7; color: #D97706; }
.qa-text {
    font-size: 0.78rem;
    font-weight: 700;
    color: #334155;
}

/* Status Pill in KPI */
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #176B5B;
    border: 1px solid rgba(23, 107, 91, 0.2);
}
.cust-toggle-pill.pending {
    background: #FEF3C7;
    color: #B45309;
    border: 1px solid rgba(217, 119, 6, 0.25);
}
.cust-toggle-pill.cancelled {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid rgba(220, 38, 38, 0.2);
}
.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.toggle-pulse-dot.completed {
    background: #176B5B;
    box-shadow: 0 0 0 2px rgba(23, 107, 91, 0.25);
}
.toggle-pulse-dot.pending {
    background: #F59E0B;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25);
}
.toggle-pulse-dot.cancelled {
    background: #DC2626;
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.25);
}

/* Modals */
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
.modal-dialog-custom.modal-dialog-delete {
    max-width: 460px;
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
.modal-icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}
.modal-icon-badge.amber { background: #FEF3C7; color: #D97706; }
.btn-danger {
    background: #DC2626;
    color: #FFFFFF;
    border: 1px solid #DC2626;
    border-radius: 10px;
    padding: 0.55rem 1.15rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-danger:hover:not(:disabled) {
    background: #B91C1C;
}
.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.modal-header-custom.delete-header {
    background: #FEF2F2;
    border-bottom: 1px solid #FEE2E2;
}
.delete-icon-pulse {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #FEE2E2;
    color: #DC2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-right: 0.65rem;
}

/* Mobile FAB */
.mobile-view-fab-wrapper {
    display: none;
}
@media (max-width: 768px) {
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    .view-layout-split {
        grid-template-columns: 1fr;
    }
    .view-kv-grid {
        grid-template-columns: 1fr;
    }
    .view-hero-card {
        padding: 1.25rem;
    }
    .view-hero-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .view-hero-scenery {
        display: none;
    }
    .view-hero-quick-actions {
        display: none;
    }
    .mobile-view-fab-wrapper {
        display: block;
        position: fixed;
        bottom: 24px;
        right: 20px;
        z-index: 999;
    }
    .mobile-view-fab {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #176B5B;
        color: #FFFFFF;
        border: none;
        box-shadow: 0 8px 24px rgba(23, 107, 91, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
    }
    .mobile-fab-menu {
        position: absolute;
        bottom: 64px;
        right: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-end;
    }
    .mobile-fab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: none;
        text-decoration: none;
        cursor: pointer;
    }
    .fab-item-label {
        background: #0F172A;
        color: #FFFFFF;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .fab-item-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #FFFFFF;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .fab-item-icon.primary { color: #176B5B; }
    .fab-item-icon.green   { color: #218765; }
    .fab-item-icon.amber   { color: #D97706; }
    .fab-item-icon.purple  { color: #7C3AED; }
}
</style>
