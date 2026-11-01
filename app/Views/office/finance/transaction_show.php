<?php
/**
 * MANASWI Tours & Travels — Transaction Voucher Details View
 * Architecturally identical to Customer Profile View (office/customers/show.php) with Brand Green Theme
 */

$tx = $transaction;
$isCredit = ($tx['transaction_type'] === 'credit');

$status = strtolower($tx['status'] ?? 'completed');
if (!in_array($status, ['completed', 'pending', 'cancelled'], true)) {
    $status = 'completed';
}
$isCompleted = ($status === 'completed');
$isPending = ($status === 'pending');
$isCancelled = ($status === 'cancelled');

$statusClass = $status;
$statusText = strtoupper($status);

$refCode = $tx['transaction_ref'] ?: ('TX-' . str_pad($tx['id'], 5, '0', STR_PAD_LEFT));
$typeName = $isCredit ? 'Credit Inflow (Receipt)' : 'Debit Outflow (Disbursement)';
$typeShort = $isCredit ? 'CREDIT' : 'DEBIT';
$typeIcon = $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right-from-square';

$paymentMethodDisplay = strtoupper(str_replace('_', ' ', $tx['payment_method'] ?? 'bank_transfer'));
$creatorDisplay = $tx['creator_name'] ?? 'System Administrator';

$initial = strtoupper(mb_substr($tx['category'] ?? 'T', 0, 1));
?>

<div class="transaction-view-root">
    <!-- 1. Top App Bar / Header Actions (Clean layout matching office/customers/show.php) -->
    <div class="view-header-bar">
        <a href="<?= url('/office/finance/transactions') ?>" class="view-header-btn back-btn" title="Back to General Ledger" aria-label="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="view-header-right">
            <button type="button" class="view-header-btn" onclick="window.print()" title="Print Voucher" aria-label="Print Voucher">
                <i class="fa-solid fa-print"></i>
            </button>
            <button type="button" class="view-header-btn edit-btn" onclick="openEditTxModal()" title="Edit Voucher" aria-label="Edit Voucher">
                <i class="fa-solid fa-pen"></i>
            </button>
            <div class="view-more-menu-wrapper">
                <button type="button" class="view-header-btn more-btn" id="btnTxMore" onclick="toggleMoreMenu(event)" title="More Options" aria-label="More Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div id="txMoreMenu" class="view-dropdown-menu" style="display: none;">
                    <a href="javascript:void(0)" onclick="openEditTxModal()" class="view-dropdown-item">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Voucher Details
                    </a>
                    <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-vault"></i> View Vault Details
                    </a>
                    <a href="javascript:void(0)" onclick="window.print()" class="view-dropdown-item">
                        <i class="fa-solid fa-print"></i> Print Voucher Slip
                    </a>
                    <a href="<?= url('/office/finance/transactions') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-book-open"></i> Full General Ledger
                    </a>
                    <div class="view-dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="openDeleteTxModal()" class="view-dropdown-item danger">
                        <i class="fa-regular fa-trash-can"></i> Delete Voucher Entry
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Hero Voucher Card (Scenic Mountains Artwork matching office/customers/show.php) -->
    <div class="view-hero-card">
        <div class="view-hero-scenery" style="background-image: url('<?= asset('images/customer-scenic-bus.png') ?>');" aria-hidden="true"></div>
        <div class="view-hero-overlay" aria-hidden="true"></div>

        <div class="view-hero-content">
            <!-- Left Icon Avatar -->
            <div class="view-avatar-wrap">
                <div class="view-avatar-initials <?= $isCredit ? 'credit' : 'debit' ?>">
                    <i class="fa-solid <?= $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right-from-square' ?>"></i>
                </div>
                <div class="view-avatar-badge" title="Verified Double-Entry Ledger Record">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- Middle Identity Details -->
            <div class="view-hero-info">
                <div class="view-hero-name-row">
                    <h2 class="view-hero-name"><?= e($refCode) ?></h2>
                    
                    <!-- Live Status 3-State Dropdown Control (Completed, Pending, Cancelled) -->
                    <div class="hero-status-toggle-wrapper" id="heroStatusToggleWrapper" title="Change Transaction Status">
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
                                <button type="button" class="tx-status-item <?= $isCompleted ? 'active' : '' ?>" onclick="setTxStatusDirect('completed', event)">
                                    <span class="status-pulse-dot completed"></span>
                                    <span>Completed</span>
                                    <?php if ($isCompleted): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                                <button type="button" class="tx-status-item <?= $isPending ? 'active' : '' ?>" onclick="setTxStatusDirect('pending', event)">
                                    <span class="status-pulse-dot pending"></span>
                                    <span>Pending</span>
                                    <?php if ($isPending): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                                <button type="button" class="tx-status-item <?= $isCancelled ? 'active' : '' ?>" onclick="setTxStatusDirect('cancelled', event)">
                                    <span class="status-pulse-dot cancelled"></span>
                                    <span>Cancelled</span>
                                    <?php if ($isCancelled): ?><i class="fa-solid fa-check check-ico"></i><?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <span class="view-hero-code-pill <?= $isCredit ? 'credit' : 'debit' ?>"><?= $typeName ?></span>
                </div>

                <div class="view-hero-meta-list">
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-vault"></i>
                        <span><?= e($tx['account_name']) ?> (<?= !empty($tx['bank_name']) ? e($tx['bank_name']) : strtoupper($tx['account_type']) ?>)</span>
                    </div>

                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span><?= e($paymentMethodDisplay) ?></span>
                    </div>

                    <div class="view-hero-meta-item">
                        <i class="fa-regular fa-clock"></i>
                        <span>Transacted: <?= format_datetime($tx['transacted_at']) ?></span>
                    </div>

                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-user-check"></i>
                        <span>By: <?= e($creatorDisplay) ?></span>
                    </div>
                </div>

                <!-- Mobile Quick Action Pills (Matching office/customers/show.php) -->
                <div class="view-hero-quick-actions">
                    <button type="button" onclick="window.print()" class="hero-qa-pill qa-print" title="Print Voucher">
                        <i class="fa-solid fa-print"></i>
                        <span>Print</span>
                    </button>
                    <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="hero-qa-pill qa-vault" title="View Vault">
                        <i class="fa-solid fa-vault"></i>
                        <span>Vault</span>
                    </a>
                    <button type="button" onclick="openEditTxModal()" class="hero-qa-pill qa-edit" title="Edit Voucher">
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                    </button>
                    <a href="<?= url('/office/finance/transactions') ?>" class="hero-qa-pill qa-ledger" title="General Ledger">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Ledger</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. 4 KPI Metric Cards (Matching office/customers/show.php with Green Theme) -->
    <div class="view-kpi-grid">
        <!-- KPI 1: Transaction Amount -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap <?= $isCredit ? 'green' : 'amber' ?>">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="kpi-trend <?= $isCredit ? 'up' : 'neutral' ?>">
                    <i class="fa-solid <?= $isCredit ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i> <?= $typeShort ?>
                </div>
            </div>
            <div class="kpi-label">Voucher Amount</div>
            <div class="kpi-value text-currency" style="color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                <?= ($isCredit ? '+' : '-') . format_currency((float)$tx['amount']) ?>
            </div>
            <div class="kpi-footer-note">Posted transaction value</div>
        </div>

        <!-- KPI 2: Running Balance After -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div class="kpi-trend up">
                    <i class="fa-solid fa-circle-check"></i> Reconciled
                </div>
            </div>
            <div class="kpi-label">Balance Post-Posting</div>
            <div class="kpi-value text-currency" style="color: #176B5B;">
                <?= format_currency((float)$tx['balance_after']) ?>
            </div>
            <div class="kpi-footer-note">Vault liquid balance</div>
        </div>

        <!-- KPI 3: Category & Payment Mode -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <span class="kpi-tag-recent"><?= e($paymentMethodDisplay) ?></span>
            </div>
            <div class="kpi-label">Accounting Category</div>
            <div class="kpi-value" style="font-size: 1.15rem;"><?= e($tx['category']) ?></div>
            <div class="kpi-footer-note">General Ledger chart code</div>
        </div>

        <!-- KPI 4: Audit Status -->
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
            <div class="kpi-footer-note">Double-entry verified</div>
        </div>
    </div>

    <!-- 4. Horizontal Navigation Pill Tabs (Smooth Swipe on Mobile) -->
    <div class="view-nav-tabs-wrapper">
        <div class="view-nav-tabs" role="tablist">
            <button type="button" class="view-nav-pill active" data-tab="overview" onclick="switchTab('overview')" role="tab" aria-selected="true">
                <i class="fa-solid fa-circle-info"></i>
                <span>Voucher Overview</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="account" onclick="switchTab('account')" role="tab" aria-selected="false">
                <i class="fa-solid fa-building-columns"></i>
                <span>Vault Account</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="audit" onclick="switchTab('audit')" role="tab" aria-selected="false">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Audit &amp; Compliance</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: OVERVIEW
         ========================================================================= -->
    <div id="tabContent-overview" class="view-tab-pane active" role="tabpanel">
        <div class="view-layout-split">
            <!-- Left Column: Primary Voucher Information & Remarks -->
            <div class="view-col-main">
                <!-- 1. Transaction Voucher Information Card -->
                <div class="view-section-card card-tx-info">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <h3 class="section-title">Transaction Voucher Information</h3>
                        </div>
                        <button type="button" onclick="openEditTxModal()" class="section-header-link-btn" title="Edit voucher details">
                            <i class="fa-solid fa-pen"></i> <span>Edit</span>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <!-- Row 1: Identification -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Voucher Identifier</span>
                                <span class="view-kv-value bold" style="font-family: monospace; font-size: 0.95rem; color: #176B5B;">
                                    <?= e($refCode) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Voucher Type</span>
                                <span class="view-kv-value bold">
                                    <span class="tx-type-pill <?= $isCredit ? 'credit' : 'debit' ?>">
                                        <i class="fa-solid <?= $isCredit ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                                        <span><?= $typeName ?></span>
                                    </span>
                                </span>
                            </div>

                            <!-- Row 2: Bank & Account Details -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Banking Institution (Bank)</span>
                                <span class="view-kv-value bold" style="color: #176B5B;">
                                    <i class="fa-solid fa-building-columns" style="margin-right: 5px;"></i>
                                    <?= !empty($tx['bank_name']) ? e($tx['bank_name']) : (!empty($tx['account_name']) ? e($tx['account_name']) : 'Internal Cash Drawer') ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Target Account</span>
                                <span class="view-kv-value bold">
                                    <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" style="color: inherit; text-decoration: underline;" title="View Vault Details">
                                        <?= e($tx['account_name']) ?>
                                    </a>
                                    <span style="font-size: 0.75rem; color: #64748B; font-weight: 600;">(<?= strtoupper($tx['account_type'] ?? 'Account') ?>)</span>
                                </span>
                            </div>

                            <div class="view-kv-item">
                                <span class="view-kv-label">Account Number</span>
                                <span class="view-kv-value monospace bold"><?= !empty($tx['account_number']) ? e($tx['account_number']) : 'Internal Drawer' ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">IFSC Code / Branch</span>
                                <span class="view-kv-value monospace bold" style="color: #176B5B;"><?= !empty($tx['ifsc_code']) ? e($tx['ifsc_code']) : 'N/A' ?></span>
                            </div>

                            <!-- Row 2 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Ledger Category</span>
                                <span class="view-kv-value bold"><?= e($tx['category']) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Payment Mode</span>
                                <span class="view-kv-value" style="font-weight: 600;">
                                    <?= e($paymentMethodDisplay) ?>
                                </span>
                            </div>

                            <!-- Row 3 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Gross Amount</span>
                                <span class="view-kv-value bold" style="font-size: 1.05rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                    <?= ($isCredit ? '+' : '-') . format_currency((float)$tx['amount']) ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Running Balance After</span>
                                <span class="view-kv-value bold"><?= format_currency((float)$tx['balance_after']) ?></span>
                            </div>

                            <!-- Row 4 -->
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
                                            <button type="button" class="tx-status-item <?= $isCompleted ? 'active' : '' ?>" onclick="setTxStatusDirect('completed', event)">
                                                <span class="status-pulse-dot completed"></span>
                                                <span>Completed</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $isPending ? 'active' : '' ?>" onclick="setTxStatusDirect('pending', event)">
                                                <span class="status-pulse-dot pending"></span>
                                                <span>Pending</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $isCancelled ? 'active' : '' ?>" onclick="setTxStatusDirect('cancelled', event)">
                                                <span class="status-pulse-dot cancelled"></span>
                                                <span>Cancelled</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Transacted Timestamp</span>
                                <span class="view-kv-value"><?= format_datetime($tx['transacted_at']) ?></span>
                            </div>

                            <!-- Row 5 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Recorded In Ledger By</span>
                                <span class="view-kv-value"><?= e($creatorDisplay) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Creation Timestamp</span>
                                <span class="view-kv-value"><?= format_datetime($tx['created_at']) ?></span>
                            </div>
                        </div>

                        <!-- Policy & Double-Entry Reassurance Box (Matching office/customers/show.php) -->
                        <div class="view-account-audit-box">
                            <div class="account-audit-label">
                                <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i>
                                <span>Double-Entry Reconciled Voucher</span>
                            </div>
                            <div class="account-audit-text">
                                This voucher is backed by an atomic double-entry journal commit. If marked <strong>COMPLETED</strong>, funds are active in the treasury vault balance. If marked <strong>PENDING</strong> or <strong>CANCELLED</strong>, the transaction remains preserved for audit reconciliation while excluding from active liquidity.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Remarks & Internal Audit Notes Card -->
                <div class="view-section-card card-tx-notes">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-regular fa-clipboard"></i>
                            </div>
                            <h3 class="section-title">Remarks &amp; Audit Notes</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <?php if (!empty($tx['notes'])): ?>
                            <div class="tx-full-notes-content">
                                <p style="margin: 0; font-size: 0.92rem; color: #1E293B; line-height: 1.6; white-space: pre-wrap;"><?= e($tx['notes']) ?></p>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 1.5rem 1rem; color: var(--text-muted);">
                                <i class="fa-regular fa-comment-dots" style="font-size: 1.8rem; margin-bottom: 0.35rem; opacity: 0.4;"></i>
                                <div style="font-size: 0.88rem; font-weight: 600;">No internal remarks recorded</div>
                                <p style="font-size: 0.8rem; margin: 2px 0 0 0;">You can add remarks anytime by clicking <strong>Edit</strong>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Account Card, Linked Context & Quick Actions (Matching office/customers/show.php) -->
            <div class="view-col-side">
                <!-- 3. Target Vault & Banking Institution Card -->
                <div class="view-section-card card-account-side">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <h3 class="section-title">Treasury Account Node</h3>
                        </div>
                        <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="section-header-link" title="Open vault passbook">
                            <span>Open Vault</span> <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="section-card-body">
                        <div class="vault-node-box">
                            <div class="vault-node-top">
                                <div class="vault-node-icon">
                                    <i class="fa-solid <?= ($tx['account_type'] ?? '') === 'cash' ? 'fa-wallet' : (($tx['account_type'] ?? '') === 'gateway' ? 'fa-qrcode' : 'fa-building-columns') ?>"></i>
                                </div>
                                <div class="vault-node-info">
                                    <h4 class="vault-node-title"><?= e($tx['account_name']) ?></h4>
                                    <span class="vault-node-sub"><?= !empty($tx['bank_name']) ? e($tx['bank_name']) : strtoupper($tx['account_type']) ?></span>
                                </div>
                            </div>

                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-hashtag"></i> Account / Vault No.</span>
                                <span class="vm-val monospace"><?= !empty($tx['account_number']) ? e($tx['account_number']) : 'Internal Drawer' ?></span>
                            </div>
                            <?php if (!empty($tx['ifsc_code'])): ?>
                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-landmark"></i> IFSC Code</span>
                                <span class="vm-val monospace" style="color: #176B5B;"><?= e($tx['ifsc_code']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="vault-metric-row">
                                <span class="vm-label"><i class="fa-solid fa-wallet"></i> Current Vault Balance</span>
                                <span class="vm-val bold" style="color: #218765;"><?= isset($account['current_balance']) ? format_currency((float)$account['current_balance']) : '—' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Linked Reference / Audit Context Card (If linked to booking or business expense) -->
                <?php if (!empty($linkedData) || !empty($tx['reference_type'])): ?>
                <div class="view-section-card card-linked-ref">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge amber">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <h3 class="section-title">Linked Audit Reference</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <?php if ($tx['reference_type'] === 'booking' && !empty($linkedData)): ?>
                            <div class="linked-audit-item">
                                <div class="lai-title"><i class="fa-regular fa-calendar-check" style="color: #176B5B;"></i> Travel Booking: <strong><?= e($linkedData['booking_code']) ?></strong></div>
                                <div class="lai-detail">Customer: <strong><?= e($linkedData['customer_name'] ?? 'Guest') ?></strong> (<?= e($linkedData['customer_phone'] ?? '—') ?>)</div>
                                <div class="lai-detail">Booking Amount: <strong><?= format_currency((float)$linkedData['total_amount']) ?></strong></div>
                                <div style="margin-top: 8px;">
                                    <a href="<?= url('/office/bookings/' . $tx['reference_id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                        View Linked Booking <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php elseif ($tx['reference_type'] === 'business_expense' && !empty($linkedData)): ?>
                            <div class="linked-audit-item">
                                <div class="lai-title"><i class="fa-solid fa-receipt" style="color: #DC2626;"></i> Expense Voucher: <strong><?= e($linkedData['expense_code']) ?></strong></div>
                                <div class="lai-detail">Vendor: <strong><?= e($linkedData['vendor_name'] ?? 'General') ?></strong></div>
                                <div class="lai-detail">Expense Total: <strong><?= format_currency((float)$linkedData['amount']) ?></strong></div>
                                <div style="margin-top: 8px;">
                                    <a href="<?= url('/office/finance/expenses') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                        View Expense Manifest <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="linked-audit-item">
                                <div class="lai-title"><i class="fa-solid fa-tag"></i> General Reference: <strong><?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? $tx['id']) ?></strong></div>
                                <div class="lai-detail">This voucher was posted directly into the treasury journal.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 5. Quick Actions Grid (Direct 1:1 match with office/customers/show.php:660-696) -->
                <div class="view-section-card card-quick-actions">
                    <div class="section-card-header compact">
                        <h4 class="quick-actions-title">Quick Actions</h4>
                    </div>
                    <div class="section-card-body pt-0">
                        <div class="quick-actions-grid">
                            <button type="button" class="quick-action-tile" onclick="openEditTxModal()" title="Edit Voucher">
                                <div class="qa-icon-wrap purple">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </div>
                                <span class="qa-text">Edit Details</span>
                            </button>

                            <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="quick-action-tile" title="View Target Vault">
                                <div class="qa-icon-wrap green">
                                    <i class="fa-solid fa-vault"></i>
                                </div>
                                <span class="qa-text">Vault Node</span>
                            </a>

                            <button type="button" class="quick-action-tile" onclick="window.print()" title="Print Voucher">
                                <div class="qa-icon-wrap cyan">
                                    <i class="fa-solid fa-print"></i>
                                </div>
                                <span class="qa-text">Print Slip</span>
                            </button>

                            <a href="<?= url('/office/finance/transactions') ?>" class="quick-action-tile" title="All Transactions">
                                <div class="qa-icon-wrap amber">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <span class="qa-text">Ledger</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: ACCOUNT & VAULT DETAILS
         ========================================================================= -->
    <div id="tabContent-account" class="view-tab-pane" role="tabpanel" style="display: none;">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h3 class="section-title">Treasury Vault Node Specifications</h3>
                </div>
                <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Open Full Vault Profile</span>
                </a>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Account Name</span>
                        <span class="view-kv-value bold"><?= e($tx['account_name']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Account Classification</span>
                        <span class="view-kv-value bold"><?= strtoupper($tx['account_type']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Banking Institution</span>
                        <span class="view-kv-value"><?= !empty($tx['bank_name']) ? e($tx['bank_name']) : 'Internal Cash Drawer' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Account Number</span>
                        <span class="view-kv-value bold monospace"><?= !empty($tx['account_number']) ? e($tx['account_number']) : 'N/A' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Bank Branch / IFSC Code</span>
                        <span class="view-kv-value bold monospace" style="color: #176B5B;"><?= !empty($tx['ifsc_code']) ? e($tx['ifsc_code']) : 'N/A' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Vault Balance Post Transaction</span>
                        <span class="view-kv-value bold"><?= format_currency((float)$tx['balance_after']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: AUDIT & COMPLIANCE
         ========================================================================= -->
    <div id="tabContent-audit" class="view-tab-pane" role="tabpanel" style="display: none;">
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
                        <span class="view-kv-label">Primary Journal ID</span>
                        <span class="view-kv-value bold">#<?= (int)$tx['id'] ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Transaction Type Code</span>
                        <span class="view-kv-value bold" style="font-family: monospace;"><?= e($tx['transaction_type']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">System Insertion Timestamp</span>
                        <span class="view-kv-value"><?= format_datetime($tx['created_at']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Transacted Timestamp</span>
                        <span class="view-kv-value"><?= format_datetime($tx['transacted_at']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Created By User ID</span>
                        <span class="view-kv-value bold"><?= e($creatorDisplay) ?> (ID: #<?= (int)($tx['created_by'] ?? 1) ?>)</span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Settlement Engine</span>
                        <span class="view-kv-value">Atomic Double-Entry Integrity Protocol</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Floating Quick Action Button (FAB) matching modern mobile apps -->
<div class="mobile-view-fab-wrapper">
    <button type="button" class="mobile-view-fab" onclick="toggleFabMenu(event)" title="Quick Actions" aria-label="Quick Actions">
        <i class="fa-solid fa-bolt" id="fabIcon"></i>
    </button>
    <div class="mobile-fab-menu" id="mobileFabMenu" style="display: none;">
        <button type="button" class="mobile-fab-item" onclick="openEditTxModal()">
            <span class="fab-item-label">Edit Voucher</span>
            <span class="fab-item-icon primary"><i class="fa-solid fa-pen-to-square"></i></span>
        </button>
        <button type="button" class="mobile-fab-item" onclick="window.print()">
            <span class="fab-item-label">Print Slip</span>
            <span class="fab-item-icon green"><i class="fa-solid fa-print"></i></span>
        </button>
        <a href="<?= url('/office/finance/accounts/' . $tx['account_id']) ?>" class="mobile-fab-item">
            <span class="fab-item-label">Vault Details</span>
            <span class="fab-item-icon amber"><i class="fa-solid fa-vault"></i></span>
        </a>
        <a href="<?= url('/office/finance/transactions') ?>" class="mobile-fab-item">
            <span class="fab-item-label">General Ledger</span>
            <span class="fab-item-icon purple"><i class="fa-solid fa-book-open"></i></span>
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT MODAL                                                                -->
<!-- ========================================================================= -->
<div id="editTxModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editTxModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge amber"><i class="fa-solid fa-pen-to-square"></i></span>
                <span>Edit Transaction Voucher</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditTxModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transactions/' . $tx['id']) ?>" method="POST" id="editTxForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/transactions/' . $tx['id']) ?>">
            <div class="modal-body-custom">
                <div class="edit-locked-alert">
                    <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i>
                    <span>Vault, Amount &amp; Type are locked to preserve accounting integrity. You can update Category, Method, Ref, Date, Status &amp; Notes.</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_category">Category <span class="req">*</span></label>
                        <input type="text" name="category" id="edit_category" class="form-control" value="<?= e($tx['category']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_payment_method">Payment Mode</label>
                        <select name="payment_method" id="edit_payment_method" class="form-select">
                            <?php foreach (['bank_transfer' => 'Bank Transfer (NEFT/IMPS)', 'upi' => 'UPI (GPay/PhonePe)', 'cash' => 'Counter Cash', 'card' => 'Card Payment', 'cheque' => 'Bank Cheque'] as $k => $lbl): ?>
                                <option value="<?= $k ?>" <?= ($tx['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_ref">Transaction Ref / Cheque #</label>
                        <input type="text" name="transaction_ref" id="edit_ref" class="form-control" value="<?= e($tx['transaction_ref'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_status">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed (Active)</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending (Awaiting Clearance)</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled (Void)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_date">Transaction Date &amp; Time</label>
                    <?php 
                        $dtFormatted = '';
                        if (!empty($tx['transacted_at'])) {
                            $ts = strtotime($tx['transacted_at']);
                            if ($ts) $dtFormatted = date('Y-m-d\TH:i', $ts);
                        }
                    ?>
                    <input type="datetime-local" name="transacted_at" id="edit_date" class="form-control" value="<?= $dtFormatted ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_notes">Remarks / Audit Description</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="3"><?= e($tx['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditTxModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Changes</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DELETE CONFIRMATION MODAL                                                 -->
<!-- ========================================================================= -->
<div id="deleteTxModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'deleteTxModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #FEF2F2; border-bottom: 1px solid #FEE2E2;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #991B1B; display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge red"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <span>Delete Transaction Voucher</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeDeleteTxModal()">&times;</button>
        </div>
        <div class="modal-body-custom" style="padding: 1.5rem;">
            <p style="font-size: 0.95rem; color: #334155; margin-bottom: 1rem; line-height: 1.5;">
                Are you sure you want to delete voucher <strong style="color: #0F172A;"><?= e($refCode) ?></strong> (<?= ($isCredit ? '+' : '-') . format_currency((float)$tx['amount']) ?>)?
            </p>

            <div style="background: #FFF5F5; border: 1.5px solid #FED7D7; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #9B2C2C; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> Automatic Ledger Reversal
                </div>
                <div style="font-size: 0.82rem; color: #742A2A; line-height: 1.45;">
                    Deleting this entry will reverse its effect from the associated vault balance to preserve accounting accuracy. This action cannot be undone.
                </div>
            </div>

            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.85rem; color: #475569; user-select: none;">
                <input type="checkbox" id="delCheck" onchange="document.getElementById('confirmDelBtn').disabled = !this.checked;" style="margin-top: 3px; accent-color: #DC2626; width: 16px; height: 16px;">
                <span>I confirm that I want to delete this transaction voucher permanently.</span>
            </label>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-outline" onclick="closeDeleteTxModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDelBtn" disabled onclick="executeDeleteTx()">
                <i class="fa-solid fa-trash-can"></i> <span>Delete Voucher</span>
            </button>
        </div>
    </div>
</div>

<script>
    // Tab switching
    function switchTab(tabId) {
        document.querySelectorAll('.view-nav-pill').forEach(pill => {
            const isActive = (pill.getAttribute('data-tab') === tabId);
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            pane.classList.remove('active');
            pane.style.display = 'none';
        });
        const target = document.getElementById('tabContent-' + tabId);
        if (target) {
            target.classList.add('active');
            target.style.display = 'block';
        }
    }

    // Header more menu
    function toggleMoreMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('txMoreMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    // Hero 3-status dropdown
    function toggleHeroStatusMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('heroStatusDropdownMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    function toggleInlineStatusMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('inlineStatusDropdownMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.view-more-menu-wrapper')) {
            const m = document.getElementById('txMoreMenu');
            if (m) m.style.display = 'none';
        }
        if (!e.target.closest('#heroStatusToggleWrapper')) {
            const m = document.getElementById('heroStatusDropdownMenu');
            if (m) m.style.display = 'none';
        }
        if (!e.target.closest('.tx-status-interactive-wrap')) {
            const m = document.getElementById('inlineStatusDropdownMenu');
            if (m) m.style.display = 'none';
        }
    });

    // Set Status via AJAX (Completed, Pending, Cancelled)
    function setTxStatusDirect(targetStatus, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const url = '<?= url('/office/finance/transactions/' . $tx['id'] . '/toggle-status') ?>';
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
                window.location.reload();
            } else {
                alert(data.message || 'Could not update status.');
            }
        })
        .catch(err => {
            console.error('Status update error:', err);
            alert('A network error occurred while updating status.');
        });
    }

    // Edit modal
    function openEditTxModal() {
        document.getElementById('editTxModalBackdrop').style.display = 'flex';
        const menu = document.getElementById('txMoreMenu');
        if (menu) menu.style.display = 'none';
    }
    function closeEditTxModal() {
        document.getElementById('editTxModalBackdrop').style.display = 'none';
    }

    // Delete modal
    function openDeleteTxModal() {
        document.getElementById('deleteTxModalBackdrop').style.display = 'flex';
        const menu = document.getElementById('txMoreMenu');
        if (menu) menu.style.display = 'none';
    }
    function closeDeleteTxModal() {
        document.getElementById('deleteTxModalBackdrop').style.display = 'none';
    }

    function executeDeleteTx() {
        const btn = document.getElementById('confirmDelBtn');
        if (btn) btn.disabled = true;

        const url = '<?= url('/office/finance/transactions/' . $tx['id'] . '/delete') ?>';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                '_csrf_token': csrfToken
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                window.location.href = '<?= url('/office/finance/transactions') ?>';
            } else {
                alert(data.message || 'Could not delete transaction.');
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            alert('A network error occurred.');
            if (btn) btn.disabled = false;
        });
    }

    // Mobile FAB
    function toggleFabMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('mobileFabMenu');
        const icon = document.getElementById('fabIcon');
        if (!menu) return;

        const isHidden = (menu.style.display === 'none' || menu.style.display === '');
        menu.style.display = isHidden ? 'flex' : 'none';
        if (icon) {
            icon.className = isHidden ? 'fa-solid fa-xmark' : 'fa-solid fa-bolt';
        }
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

<style>
/* ========================================================================= */
/* SCOPED VIEW STYLES (Matching office/customers/show.php with Green Theme)   */
/* ========================================================================= */
.transaction-view-root {
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

/* 2. Hero Voucher Card */
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

/* Interactive Status 3-Option Button & Menu */
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

/* 3. KPI Metric Cards (Matching office/customers/show.php) */
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

/* 4. Horizontal Pill Tabs */
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

/* Key-Value Grid */
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

/* Compliance Box */
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

/* Quick Actions Grid (Matching office/customers/show.php:2601-2640) */
.quick-actions-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.quick-action-tile {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 0.95rem 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.18s ease;
}

.quick-action-tile:hover {
    background: #FFFFFF;
    border-color: rgba(23, 107, 91, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.1);
}

.qa-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}

.qa-icon-wrap.green {
    background: #E8F7F0;
    color: #176B5B;
}

.qa-icon-wrap.purple {
    background: #EDE9FE;
    color: #7C3AED;
}

.qa-icon-wrap.cyan {
    background: #CFFAFE;
    color: #0891B2;
}

.qa-icon-wrap.amber {
    background: #FEF3C7;
    color: #B45309;
}

.qa-text {
    font-size: 0.72rem;
    font-weight: 700;
    color: #334155;
    text-align: center;
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
    background: #FFFFFF;
    border-radius: 18px;
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
    color: #64748B;
    cursor: pointer;
}

.modal-body-custom {
    padding: 1.25rem 1.35rem;
    max-height: 75vh;
    overflow-y: auto;
}

.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: #F8FAFC;
}

.modal-icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.modal-icon-badge.amber {
    background: #FEF3C7;
    color: #B45309;
}

.modal-icon-badge.red {
    background: #FEE2E2;
    color: #DC2626;
}

.edit-locked-alert {
    background: #E8F7F0;
    border: 1px solid rgba(23, 107, 91, 0.2);
    border-radius: 10px;
    padding: 0.75rem 0.95rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #16443B;
    line-height: 1.4;
}

/* Mobile FAB */
.mobile-view-fab-wrapper {
    display: none;
}

@media (max-width: 991.98px) {
    .mobile-view-fab-wrapper {
        display: block;
        position: fixed;
        right: 1.25rem;
        bottom: 5.5rem;
        z-index: 999;
    }

    .mobile-view-fab {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #176B5B;
        color: #FFFFFF;
        border: none;
        box-shadow: 0 6px 20px rgba(23, 107, 91, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
    }

    .mobile-fab-menu {
        position: absolute;
        bottom: calc(100% + 12px);
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
        cursor: pointer;
        text-decoration: none;
    }

    .fab-item-label {
        background: #FFFFFF;
        color: #0F172A;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
        white-space: nowrap;
    }

    .fab-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .fab-item-icon.primary { background: #176B5B; }
    .fab-item-icon.green { background: #10B981; }
    .fab-item-icon.amber { background: #F59E0B; }
    .fab-item-icon.purple { background: #8B5CF6; }
}

/* ========================================================================= */
/* RESPONSIVENESS (Matching office/customers/30 breakpoint rules)             */
/* ========================================================================= */
@media (min-width: 769px) and (max-width: 1023px) {
    .view-layout-split {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
}

@media (max-width: 768px) {
    .transaction-view-root {
        padding: 0.65rem 0.65rem 95px 0.65rem !important;
    }

    .view-hero-card {
        padding: 1.1rem 1rem !important;
        border-radius: 18px !important;
        margin-bottom: 0.85rem !important;
    }

    .view-hero-scenery {
        width: 50% !important;
        max-width: 220px !important;
        opacity: 0.75 !important;
    }

    .view-hero-content {
        gap: 0.85rem !important;
    }

    .view-avatar-wrap {
        width: 74px !important;
        height: 74px !important;
        border-radius: 18px !important;
    }

    .view-avatar-initials {
        font-size: 1.6rem !important;
    }

    .view-hero-name {
        font-size: 1.2rem !important;
    }

    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem !important;
        margin-bottom: 0.85rem !important;
    }

    .view-kpi-card {
        padding: 0.85rem 0.75rem !important;
        border-radius: 16px !important;
    }

    .kpi-value {
        font-size: 1.15rem !important;
    }

    .view-layout-split {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
    }

    .view-col-main, .view-col-side {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
    }

    .view-kv-grid {
        grid-template-columns: 1fr 1fr !important;
        row-gap: 0.85rem !important;
        column-gap: 0.75rem !important;
    }

    .quick-actions-grid {
        gap: 0.5rem !important;
    }

    .quick-action-tile {
        padding: 0.75rem 0.35rem !important;
    }
}

@media (max-width: 400px) {
    .view-kv-grid {
        grid-template-columns: 1fr 1fr !important;
        column-gap: 0.5rem !important;
    }

    .kpi-value {
        font-size: 1.05rem !important;
    }

    .quick-actions-grid {
        gap: 0.35rem !important;
    }
}
</style>

