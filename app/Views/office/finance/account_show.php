<?php
/**
 * MANASWI Tours & Travels — Treasury Account & Vault Details View
 * Architecturally identical to Customer Profile View (office/customers/show.php) with Brand Green Theme
 */

$isBank = $account['account_type'] === 'bank';
$isCash = $account['account_type'] === 'cash';
$isGateway = $account['account_type'] === 'gateway';

$icon = $isCash ? 'fa-wallet' : ($isBank ? 'fa-building-columns' : 'fa-qrcode');
$typeName = $isCash ? 'Cash Vault' : ($isBank ? 'Bank Current Account' : 'Payment Gateway & UPI');

$status = strtolower($account['status'] ?? 'active');
$isActive = ($status === 'active');
$statusText = strtoupper($status);

$initial = strtoupper(mb_substr($account['account_name'] ?? 'A', 0, 1));
$recentTransactions = $account['recent_transactions'] ?? [];
$recentTransfers = $account['recent_transfers'] ?? [];
?>

<div class="account-view-root">
    <!-- 1. Top App Bar / Header Actions (Clean layout matching office/customers/show.php) -->
    <div class="view-header-bar">
        <a href="<?= url('/office/finance/accounts') ?>" class="view-header-btn back-btn" title="Back to Accounts" aria-label="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="view-header-right">
            <button type="button" class="view-header-btn" onclick="openTransferModal(<?= (int)$account['id'] ?>)" title="Transfer Funds" aria-label="Transfer Funds">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </button>
            <button type="button" class="view-header-btn edit-btn" onclick="openEditAccountModal()" title="Edit Account" aria-label="Edit Account">
                <i class="fa-solid fa-pen"></i>
            </button>
            <div class="view-more-menu-wrapper">
                <button type="button" class="view-header-btn more-btn" id="btnAccountMore" onclick="toggleMoreMenu(event)" title="More Options" aria-label="More Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div id="accountMoreMenu" class="view-dropdown-menu" style="display: none;">
                    <a href="javascript:void(0)" onclick="openEditAccountModal()" class="view-dropdown-item">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Account Details
                    </a>
                    <a href="javascript:void(0)" onclick="openTransferModal(<?= (int)$account['id'] ?>)" class="view-dropdown-item">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer Funds
                    </a>
                    <a href="<?= url('/office/finance/transactions?account_id=' . $account['id']) ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-book-open"></i> View Full Passbook
                    </a>
                    <a href="javascript:void(0)" onclick="window.print()" class="view-dropdown-item">
                        <i class="fa-solid fa-print"></i> Print Account Statement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Hero Account Card (Scenic Artwork matching office/customers/show.php) -->
    <div class="view-hero-card">
        <div class="view-hero-scenery" style="background-image: url('<?= asset('images/customer-scenic-bus.png') ?>');" aria-hidden="true"></div>
        <div class="view-hero-overlay" aria-hidden="true"></div>

        <div class="view-hero-content">
            <!-- Left Monogram / Icon Avatar -->
            <div class="view-avatar-wrap">
                <div class="view-avatar-initials">
                    <i class="fa-solid <?= $icon ?>"></i>
                </div>
                <div class="view-avatar-badge" title="Verified Treasury Vault">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- Middle Identity Details -->
            <div class="view-hero-info">
                <div class="view-hero-name-row">
                    <h2 class="view-hero-name"><?= e($account['account_name']) ?></h2>
                    
                    <!-- Live Status Toggle Switch Control -->
                    <div class="hero-status-toggle-wrapper" id="heroStatusToggleWrapper" title="Toggle Account Status">
                        <button type="button" 
                                id="heroStatusToggleBtn"
                                class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                role="switch" 
                                aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                data-id="<?= (int)$account['id'] ?>"
                                data-name="<?= addslashes(e($account['account_name'])) ?>"
                                onclick="toggleAccountViewStatus(<?= (int)$account['id'] ?>, this, event)"
                                title="Click to toggle status (currently <?= ucfirst($status) ?>)">
                            <span class="toggle-slider-track">
                                <span class="toggle-slider-thumb">
                                    <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </span>
                            </span>
                        </button>
                        <span id="heroStatusPill" class="view-hero-status-pill <?= $isActive ? 'active' : 'inactive' ?>">
                            <span class="status-pulse-dot"></span>
                            <span class="status-label-text" id="heroStatusLabelText"><?= $statusText ?></span>
                        </span>
                    </div>

                    <span class="view-hero-code-pill"><?= $typeName ?></span>
                </div>

                <div class="view-hero-meta-list">
                    <?php if (!empty($account['bank_name'])): ?>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-landmark"></i>
                        <span><?= e($account['bank_name']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($account['account_number'])): ?>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>A/C: <?= e($account['account_number']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($account['ifsc_code'])): ?>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-hashtag"></i>
                        <span>IFSC: <?= e($account['ifsc_code']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="view-hero-meta-item">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Opened: <?= format_date($account['created_at']) ?></span>
                    </div>
                </div>

                <!-- Mobile Quick Contact / Action Pills (Matching office/customers/show.php) -->
                <div class="view-hero-quick-actions">
                    <button type="button" onclick="openTransferModal(<?= (int)$account['id'] ?>)" class="hero-qa-pill qa-transfer" title="Transfer Funds">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        <span>Transfer</span>
                    </button>
                    <button type="button" onclick="switchTab('ledger')" class="hero-qa-pill qa-passbook" title="View Passbook">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Passbook</span>
                    </button>
                    <button type="button" onclick="openEditAccountModal()" class="hero-qa-pill qa-edit" title="Edit Vault Details">
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                    </button>
                    <a href="<?= url('/office/finance/expenses') ?>" class="hero-qa-pill qa-expense" title="Record Expense">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Expense</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. 4 KPI Metric Cards (Matching office/customers/show.php with Green Theme) -->
    <div class="view-kpi-grid">
        <!-- KPI 1: Available Balance -->
        <div class="view-kpi-card" onclick="switchTab('ledger')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="kpi-trend up">
                    <i class="fa-solid fa-shield-halved"></i> Available
                </div>
            </div>
            <div class="kpi-label">Available Balance</div>
            <div class="kpi-value text-currency" style="color: #218765;"><?= format_currency((float)$account['current_balance']) ?></div>
            <div class="kpi-footer-note">Current liquid reserves</div>
        </div>

        <!-- KPI 2: Total Inflows -->
        <div class="view-kpi-card" onclick="switchTab('ledger')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-arrow-down-left"></i>
                </div>
                <div class="kpi-trend up">
                    <i class="fa-solid fa-arrow-down"></i> Inflow
                </div>
            </div>
            <div class="kpi-label">Total Inflows (Credits)</div>
            <div class="kpi-value text-currency" style="color: #176B5B;"><?= format_currency((float)$account['total_inflow']) ?></div>
            <div class="kpi-footer-note">Receipts &amp; collections</div>
        </div>

        <!-- KPI 3: Total Outflows -->
        <div class="view-kpi-card" onclick="switchTab('ledger')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap amber">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
                <div class="kpi-trend neutral">
                    <i class="fa-solid fa-arrow-up"></i> Outflow
                </div>
            </div>
            <div class="kpi-label">Total Outflows (Debits)</div>
            <div class="kpi-value text-currency" style="color: #C78A35;"><?= format_currency((float)$account['total_outflow']) ?></div>
            <div class="kpi-footer-note">Expenses &amp; payouts</div>
        </div>

        <!-- KPI 4: Total Transactions -->
        <div class="view-kpi-card" onclick="switchTab('ledger')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap green">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span class="kpi-tag-recent">Activity</span>
            </div>
            <div class="kpi-label">Total Transactions</div>
            <div class="kpi-value"><?= (int)$account['total_transactions'] ?></div>
            <div class="kpi-footer-note">Posted ledger entries</div>
        </div>
    </div>

    <!-- 4. Horizontal Navigation Pill Tabs (Smooth Swipe on Mobile) -->
    <div class="view-nav-tabs-wrapper">
        <div class="view-nav-tabs" role="tablist">
            <button type="button" class="view-nav-pill active" data-tab="overview" onclick="switchTab('overview')" role="tab" aria-selected="true">
                <i class="fa-solid fa-circle-info"></i>
                <span>Overview</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="ledger" onclick="switchTab('ledger')" role="tab" aria-selected="false">
                <i class="fa-solid fa-book-open"></i>
                <span>Passbook Ledger</span>
                <span class="pill-count"><?= count($recentTransactions) ?></span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="transfers" onclick="switchTab('transfers')" role="tab" aria-selected="false">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                <span>Fund Transfers</span>
                <span class="pill-count"><?= count($recentTransfers) ?></span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="specs" onclick="switchTab('specs')" role="tab" aria-selected="false">
                <i class="fa-solid fa-sliders"></i>
                <span>Vault Specifications</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: OVERVIEW
         ========================================================================= -->
    <div id="tabContent-overview" class="view-tab-pane active" role="tabpanel">
        <div class="view-layout-split">
            <!-- Left Column: Primary Account Details -->
            <div class="view-col-main">
                <!-- 1. Institutional Account Information Card -->
                <div class="view-section-card card-institution">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                            <h3 class="section-title">Institutional Account Information</h3>
                        </div>
                        <button type="button" onclick="openEditAccountModal()" class="section-header-link-btn" title="Edit account details">
                            <i class="fa-solid fa-pen"></i> <span>Edit</span>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <!-- Row 1 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Account / Vault Name</span>
                                <span class="view-kv-value bold"><?= e($account['account_name']) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Classification</span>
                                <span class="view-kv-value bold"><?= $typeName ?></span>
                            </div>

                            <!-- Row 2 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Banking Institution</span>
                                <span class="view-kv-value">
                                    <?= !empty($account['bank_name']) ? e($account['bank_name']) : '<em class="text-muted">Internal Drawer</em>' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Account Number</span>
                                <span class="view-kv-value" style="font-family: monospace; font-weight: 700;">
                                    <?= !empty($account['account_number']) ? e($account['account_number']) : '<em class="text-muted">N/A</em>' ?>
                                </span>
                            </div>

                            <!-- Row 3 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">IFSC Code</span>
                                <span class="view-kv-value" style="font-family: monospace; font-weight: 700; color: #176B5B;">
                                    <?= !empty($account['ifsc_code']) ? e($account['ifsc_code']) : '<em class="text-muted">N/A</em>' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Opening Balance</span>
                                <span class="view-kv-value bold"><?= format_currency((float)$account['opening_balance']) ?></span>
                            </div>

                            <!-- Row 4 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Operational Status</span>
                                <span class="view-kv-value">
                                    <span class="cust-toggle-pill <?= $isActive ? 'completed' : 'cancelled' ?>">
                                        <span class="toggle-pulse-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                                        <span><?= $statusText ?></span>
                                    </span>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Creation Timestamp</span>
                                <span class="view-kv-value"><?= format_datetime($account['created_at']) ?></span>
                            </div>
                        </div>

                        <!-- Policy & Double-Entry Settlement Reassurance Box (Matching office/customers/show.php:485-493) -->
                        <div class="view-account-audit-box">
                            <div class="account-audit-label">
                                <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i>
                                <span>Treasury Vault Compliance &amp; Settlement Policy</span>
                            </div>
                            <div class="account-audit-text">
                                This account operates under automated double-entry verification. All debits, credit collections, and inter-vault transfers maintain cryptographically indexed audit trails with immutable timestamps.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Activity & Quick Actions (Matching office/customers/show.php) -->
            <div class="view-col-side">
                <!-- 2. Latest Transactions Card (Direct match with Recent Bookings in office/customers/show.php:574-658) -->
                <div class="view-section-card card-latest-tx">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge green">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                            <h3 class="section-title">Latest Transactions</h3>
                            <span class="section-badge-counter <?= empty($recentTransactions) ? 'zero' : '' ?>"><?= count($recentTransactions) ?></span>
                        </div>
                        <?php if (!empty($recentTransactions)): ?>
                            <a href="javascript:void(0)" onclick="switchTab('ledger')" class="section-header-link" title="View all transactions in Ledger">
                                <span>View All</span> <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="section-card-body p-0">
                        <?php if (empty($recentTransactions)): ?>
                            <!-- Empty State matching office/customers/show.php:633-656 -->
                            <div class="recent-tx-empty-state">
                                <div class="empty-state-icon-wrap">
                                    <div class="empty-icon-glow"></div>
                                    <div class="empty-icon-badge">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                </div>
                                <h4 class="empty-state-heading">No Transactions Yet</h4>
                                <p class="empty-state-desc">
                                    There are currently no credit or debit vouchers recorded for this treasury vault.
                                </p>
                                <div class="empty-state-actions">
                                    <button type="button" class="btn-create-tx-primary" onclick="openTransferModal(<?= (int)$account['id'] ?>)">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        <span>Transfer Funds</span>
                                    </button>
                                </div>
                                <div class="empty-state-meta-hint">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Account active &amp; ready for financial operations</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Card-based list matching office/customers/show.php recent-bookings-list -->
                            <div class="recent-tx-list">
                                <?php foreach (array_slice($recentTransactions, 0, 5) as $tx): 
                                    $isCredit = $tx['transaction_type'] === 'credit';
                                ?>
                                <div class="recent-tx-card" onclick="switchTab('ledger')" role="button" tabindex="0" title="Click to view details in Passbook Ledger">
                                    <div class="tx-thumb-icon-wrap <?= $isCredit ? 'credit' : 'debit' ?>">
                                        <i class="fa-solid <?= $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right-from-square' ?>"></i>
                                    </div>
                                    <div class="tx-card-mid">
                                        <div class="tx-card-header-mini">
                                            <span class="tx-mini-code <?= $isCredit ? 'credit' : 'debit' ?>">
                                                <?= $isCredit ? 'CREDIT' : 'DEBIT' ?>
                                            </span>
                                            <span class="tx-category-badge">
                                                <?= e($tx['category'] ?? 'General') ?>
                                            </span>
                                        </div>
                                        <h4 class="tx-card-title" title="<?= e($tx['description'] ?? 'Ledger Entry') ?>">
                                            <?= e($tx['description'] ?? 'Ledger Entry') ?>
                                        </h4>
                                        <div class="tx-card-meta">
                                            <span class="meta-date">
                                                <i class="fa-regular fa-clock"></i> <?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?>
                                            </span>
                                            <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                                <span class="meta-ref">
                                                    <i class="fa-solid fa-hashtag"></i> <?= e($tx['reference_type'] ?? 'ref') ?> #<?= e($tx['reference_id'] ?? '') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="tx-card-right">
                                        <div class="tx-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                                            <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                        </div>
                                        <?php if (isset($tx['balance_after'])): ?>
                                            <span class="tx-balance-after">
                                                Bal: <?= format_currency((float)$tx['balance_after']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tx-card-arrow">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($recentTransactions) > 5): ?>
                                <div class="recent-tx-footer">
                                    <a href="javascript:void(0)" onclick="switchTab('ledger')" class="recent-view-more-btn">
                                        View All <?= count($recentTransactions) ?> Transactions in Ledger <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. Quick Actions Grid (Direct 1:1 match with office/customers/show.php:660-696) -->
                <div class="view-section-card card-quick-actions">
                    <div class="section-card-header compact">
                        <h4 class="quick-actions-title">Quick Actions</h4>
                    </div>
                    <div class="section-card-body pt-0">
                        <div class="quick-actions-grid">
                            <button type="button" class="quick-action-tile" onclick="openTransferModal(<?= (int)$account['id'] ?>)" title="Transfer Funds">
                                <div class="qa-icon-wrap green">
                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                </div>
                                <span class="qa-text">Transfer</span>
                            </button>

                            <button type="button" class="quick-action-tile" onclick="openEditAccountModal()" title="Edit Account Details">
                                <div class="qa-icon-wrap purple">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </div>
                                <span class="qa-text">Edit Details</span>
                            </button>

                            <button type="button" class="quick-action-tile" onclick="switchTab('ledger')" title="View Passbook Ledger">
                                <div class="qa-icon-wrap cyan">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <span class="qa-text">Passbook</span>
                            </button>

                            <a href="<?= url('/office/finance/expenses') ?>" class="quick-action-tile" title="Record Operating Expense">
                                <div class="qa-icon-wrap amber">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <span class="qa-text">Expense</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: LEDGER / PASSBOOK
         ========================================================================= -->
    <div id="tabContent-ledger" class="view-tab-pane" role="tabpanel" style="display: none;">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 class="section-title">Treasury Passbook &amp; Transaction Ledger</h3>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="<?= url('/office/finance/transactions?account_id=' . $account['id']) ?>" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-filter"></i> <span>Detailed Filter</span>
                    </a>
                </div>
            </div>
            <div class="section-card-body" style="padding: 0;">
                <?php if (empty($recentTransactions)): ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                        <i class="fa-solid fa-book" style="font-size: 2.2rem; margin-bottom: 0.5rem; opacity: 0.4;"></i>
                        <h4 style="font-weight: 700; color: #0F172A; margin-bottom: 4px;">No ledger transactions</h4>
                        <p style="margin: 0;">No credit or debit entries have been posted to this account yet.</p>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table (Screen Width >= 769px) -->
                    <div class="desktop-ledger-table">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="modern-table" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="width: 140px;">Date &amp; Time</th>
                                        <th>Description &amp; Reference</th>
                                        <th style="width: 130px;">Category</th>
                                        <th style="width: 100px;" class="text-center">Type</th>
                                        <th style="text-align: right; width: 140px;">Amount</th>
                                        <th style="text-align: right; width: 140px;">Balance After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $idx => $tx): 
                                        $isCredit = $tx['transaction_type'] === 'credit';
                                    ?>
                                    <tr>
                                        <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                            <?= $idx + 1 ?>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                            <?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #0F172A; font-size: 0.9rem;">
                                                <?= e($tx['description'] ?? 'Ledger Transaction') ?>
                                            </div>
                                            <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 1px;">
                                                    Ref: <?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-neutral" style="font-size: 0.75rem;">
                                                <?= e($tx['category'] ?? 'General') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                                <?= $isCredit ? '+ CREDIT' : '- DEBIT' ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                            <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #0F172A; font-size: 0.9rem;">
                                            <?= isset($tx['balance_after']) ? format_currency((float)$tx['balance_after']) : '—' ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards (Screen Width <= 768px - Native App Feel matching office/customers) -->
                    <div class="mobile-ledger-cards-list">
                        <?php foreach ($recentTransactions as $idx => $tx): 
                            $isCredit = $tx['transaction_type'] === 'credit';
                        ?>
                        <div class="mb-account-card">
                            <div class="mb-card-header">
                                <div class="mb-card-date">
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?= format_datetime($tx['transacted_at'] ?? $tx['created_at']) ?></span>
                                </div>
                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+ CREDIT' : '- DEBIT' ?>
                                </span>
                            </div>
                            <div class="mb-card-body">
                                <h4 class="mb-card-title"><?= e($tx['description'] ?? 'Ledger Transaction') ?></h4>
                                <div class="mb-card-meta">
                                    <span class="badge badge-neutral" style="font-size: 0.72rem; padding: 2px 7px;"><?= e($tx['category'] ?? 'General') ?></span>
                                    <?php if (!empty($tx['reference_type']) || !empty($tx['reference_id'])): ?>
                                        <span class="mb-card-ref">Ref: <?= e($tx['reference_type'] ?? 'direct') ?> #<?= e($tx['reference_id'] ?? '') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-card-footer">
                                <div class="mb-card-balance">
                                    <span class="label">After:</span>
                                    <span class="val"><?= isset($tx['balance_after']) ? format_currency((float)$tx['balance_after']) : '—' ?></span>
                                </div>
                                <div class="mb-card-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: FUND TRANSFERS
         ========================================================================= -->
    <div id="tabContent-transfers" class="view-tab-pane" role="tabpanel" style="display: none;">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <h3 class="section-title">Internal Fund Transfers</h3>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openTransferModal(<?= (int)$account['id'] ?>)">
                    <i class="fa-solid fa-plus"></i> <span>New Transfer</span>
                </button>
            </div>
            <div class="section-card-body" style="padding: 0;">
                <?php if (empty($recentTransfers)): ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                        <i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 2.2rem; margin-bottom: 0.5rem; opacity: 0.4;"></i>
                        <h4 style="font-weight: 700; color: #0F172A; margin-bottom: 4px;">No inter-vault transfers</h4>
                        <p style="margin: 0;">No funds have been transferred to or from this account yet.</p>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table (Screen Width >= 769px) -->
                    <div class="desktop-transfers-table">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="modern-table" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="width: 140px;">Date &amp; Time</th>
                                        <th>Direction &amp; Account</th>
                                        <th>Notes</th>
                                        <th style="text-align: right; width: 150px;">Transfer Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransfers as $idx => $trf): 
                                        $isCredit = $trf['transaction_type'] === 'credit';
                                    ?>
                                    <tr>
                                        <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                            <?= $idx + 1 ?>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted); white-space: nowrap;">
                                            <?= format_datetime($trf['transacted_at'] ?? $trf['created_at']) ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                                    <?= $isCredit ? 'Received From' : 'Transferred To' ?>
                                                </span>
                                                <strong style="color: #0F172A; font-size: 0.9rem;">
                                                    <?= e($trf['related_account_name'] ?? 'Other Vault') ?>
                                                </strong>
                                            </div>
                                        </td>
                                        <td style="font-size: 0.84rem; color: var(--text-muted);">
                                            <?= e($trf['description'] ?? 'Internal Transfer') ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                            <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$trf['amount']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards (Screen Width <= 768px - Native App Feel matching office/customers) -->
                    <div class="mobile-transfers-cards-list">
                        <?php foreach ($recentTransfers as $idx => $trf): 
                            $isCredit = $trf['transaction_type'] === 'credit';
                        ?>
                        <div class="mb-account-card">
                            <div class="mb-card-header">
                                <div class="mb-card-date">
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?= format_datetime($trf['transacted_at'] ?? $trf['created_at']) ?></span>
                                </div>
                                <span class="account-tx-type-badge <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? 'Received From' : 'Transferred To' ?>
                                </span>
                            </div>
                            <div class="mb-card-body">
                                <h4 class="mb-card-title"><?= e($trf['related_account_name'] ?? 'Other Vault') ?></h4>
                                <?php if (!empty($trf['description'])): ?>
                                    <p class="mb-card-notes"><?= e($trf['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-card-footer">
                                <span class="text-muted" style="font-size: 0.75rem;">Transfer Amount</span>
                                <div class="mb-card-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$trf['amount']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: SPECIFICATIONS
         ========================================================================= -->
    <div id="tabContent-specs" class="view-tab-pane" role="tabpanel" style="display: none;">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge green">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <h3 class="section-title">Vault Technical Specifications &amp; Audit Trail</h3>
                </div>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Account Primary ID</span>
                        <span class="view-kv-value bold">#<?= (int)$account['id'] ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Vault Type Key</span>
                        <span class="view-kv-value bold" style="font-family: monospace;"><?= e($account['account_type']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">System Created At</span>
                        <span class="view-kv-value"><?= format_datetime($account['created_at']) ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Last Modified At</span>
                        <span class="view-kv-value"><?= !empty($account['updated_at']) ? format_datetime($account['updated_at']) : 'Never' ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Bank Branch / IFSC</span>
                        <span class="view-kv-value bold" style="font-family: monospace;"><?= e($account['ifsc_code'] ?? 'None / Not Applicable') ?></span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Reconciliation Policy</span>
                        <span class="view-kv-value">Standard Double-Entry Ledger</span>
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
        <button type="button" class="mobile-fab-item" onclick="openTransferModal(<?= (int)$account['id'] ?>)">
            <span class="fab-item-label">Transfer Funds</span>
            <span class="fab-item-icon green"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
        </button>
        <button type="button" class="mobile-fab-item" onclick="openEditAccountModal()">
            <span class="fab-item-label">Edit Account</span>
            <span class="fab-item-icon primary"><i class="fa-solid fa-pen-to-square"></i></span>
        </button>
        <a href="javascript:void(0)" onclick="switchTab('ledger'); toggleFabMenu();" class="mobile-fab-item">
            <span class="fab-item-label">Passbook Ledger</span>
            <span class="fab-item-icon amber"><i class="fa-solid fa-book-open"></i></span>
        </a>
        <a href="<?= url('/office/finance/expenses') ?>" class="mobile-fab-item">
            <span class="fab-item-label">Record Expense</span>
            <span class="fab-item-icon purple"><i class="fa-solid fa-receipt"></i></span>
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Edit Financial Account                                             -->
<!-- ========================================================================= -->
<div id="editAccountModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editAccountModalBackdrop')">
    <div class="modal-dialog-custom" onclick="event.stopPropagation()">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0F172A;">
                <i class="fa-solid fa-pen-to-square" style="color: #176B5B; margin-right: 0.45rem;"></i>
                Edit Treasury Account
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditAccountModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/accounts/' . $account['id']) ?>" method="POST" id="editAccountForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/accounts/' . $account['id']) ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_acc_name">Account / Vault Name <span class="req">*</span></label>
                    <input type="text" name="account_name" id="edit_acc_name" class="form-control" value="<?= e($account['account_name']) ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_acc_type">Account Classification <span class="req">*</span></label>
                    <select name="account_type" id="edit_acc_type" class="form-select" onchange="toggleEditBankFields(this.value)">
                        <option value="bank" <?= $account['account_type'] === 'bank' ? 'selected' : '' ?>>Commercial Bank Current Account</option>
                        <option value="cash" <?= $account['account_type'] === 'cash' ? 'selected' : '' ?>>Counter Cash / Petty Vault</option>
                        <option value="gateway" <?= $account['account_type'] === 'gateway' ? 'selected' : '' ?>>Payment Gateway (Razorpay/Stripe/UPI)</option>
                    </select>
                </div>

                <div id="editBankFields" style="display: <?= $isBank ? 'block' : 'none' ?>;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="edit_acc_bank">Bank Institution</label>
                            <input type="text" name="bank_name" id="edit_acc_bank" class="form-control" value="<?= e($account['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edit_acc_num">Account Number</label>
                            <input type="text" name="account_number" id="edit_acc_num" class="form-control" value="<?= e($account['account_number'] ?? '') ?>" placeholder="A/C Number">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="edit_acc_ifsc">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="edit_acc_ifsc" class="form-control" value="<?= e($account['ifsc_code'] ?? '') ?>" placeholder="e.g. HDFC0001234">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_acc_status">Status</label>
                    <select name="status" id="edit_acc_status" class="form-select">
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Changes</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Internal Fund Transfer                                             -->
<!-- ========================================================================= -->
<div id="transferModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'transferModalBackdrop')">
    <div class="modal-dialog-custom" onclick="event.stopPropagation()">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0F172A;">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color: #176B5B; margin-right: 0.45rem;"></i>
                Internal Account Fund Transfer
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeTransferModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transfer') ?>" method="POST" id="fundTransferForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/accounts/' . $account['id']) ?>">
            <div class="modal-body-custom">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="from_account_id">From Account (Source) <span class="req">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            <option value="">— Select source —</option>
                            <?php foreach ($activeAccounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $account['id'] ? 'selected' : '' ?>>
                                    <?= e($acc['account_name']) ?> (<?= format_currency((float)$acc['current_balance']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="to_account_id">To Account (Destination) <span class="req">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select" required>
                            <option value="">— Select destination —</option>
                            <?php foreach ($activeAccounts as $acc): ?>
                                <?php if ($acc['id'] != $account['id']): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="trf_amount">Transfer Amount (INR) <span class="req">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="trf_amount" class="form-control" placeholder="0.00" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="trf_notes">Transfer Remarks</label>
                    <input type="text" name="notes" id="trf_notes" class="form-control" placeholder="Reason for inter-account rebalancing...">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Execute Transfer</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Tab Switcher                                                              */
    /* ========================================================================= */
    function switchTab(tabName) {
        document.querySelectorAll('.view-nav-pill').forEach(pill => {
            const isActive = pill.getAttribute('data-tab') === tabName;
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            pane.style.display = (pane.id === 'tabContent-' + tabName) ? 'block' : 'none';
        });

        // Scroll active tab pill smoothly into view on mobile
        const activePill = document.querySelector('.view-nav-pill.active');
        if (activePill && window.innerWidth <= 768) {
            activePill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    /* ========================================================================= */
    /* Header Dropdown Menu & Mobile Floating Action Button (FAB)                */
    /* ========================================================================= */
    function toggleMoreMenu(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('accountMoreMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    function toggleFabMenu(e) {
        if (e) e.stopPropagation();
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            const isOpen = (fabMenu.style.display !== 'none');
            fabMenu.style.display = isOpen ? 'none' : 'flex';
            if (fabIcon) {
                fabIcon.className = isOpen ? 'fa-solid fa-bolt' : 'fa-solid fa-xmark';
            }
        }
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu && !e.target.closest('.view-more-menu-wrapper')) {
            menu.style.display = 'none';
        }

        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu && !e.target.closest('.mobile-view-fab-wrapper')) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditAccountModal();
            closeTransferModal();
            const menu = document.getElementById('accountMoreMenu');
            if (menu) menu.style.display = 'none';
            const fabMenu = document.getElementById('mobileFabMenu');
            const fabIcon = document.getElementById('fabIcon');
            if (fabMenu) {
                fabMenu.style.display = 'none';
                if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
            }
        }
    });

    /* ========================================================================= */
    /* Live Status Toggle Switch                                                 */
    /* ========================================================================= */
    function toggleAccountViewStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'inactive' : 'active';

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/finance/accounts/') ?>' + id + '/toggle-status';
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
                'csrf_token': csrfToken,
                'status': targetStatus
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                const isNowActive = (data.status === 'active');
                const statusTxt = isNowActive ? 'ACTIVE' : 'INACTIVE';

                if (isNowActive) {
                    btn.classList.add('is-active');
                    btn.setAttribute('aria-checked', 'true');
                    btn.title = 'Click to toggle status (currently Active)';
                } else {
                    btn.classList.remove('is-active');
                    btn.setAttribute('aria-checked', 'false');
                    btn.title = 'Click to toggle status (currently Inactive)';
                }

                const pill = document.getElementById('heroStatusPill');
                const text = document.getElementById('heroStatusLabelText');
                if (pill) {
                    pill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
                }
                if (text) {
                    text.textContent = statusTxt;
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Account status set to ${statusTxt}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to toggle status.', 'error');
                } else {
                    alert(data.message || 'Failed to toggle status.');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            console.error('Status toggle error:', err);
            alert('Failed to update status.');
        });
    }

    /* ========================================================================= */
    /* Edit Account Modal                                                        */
    /* ========================================================================= */
    function openEditAccountModal() {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
        const modal = document.getElementById('editAccountModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeEditAccountModal() {
        const modal = document.getElementById('editAccountModalBackdrop');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    function toggleEditBankFields(type) {
        const fields = document.getElementById('editBankFields');
        if (fields) fields.style.display = (type === 'bank') ? 'block' : 'none';
    }

    /* ========================================================================= */
    /* Transfer Modal                                                            */
    /* ========================================================================= */
    function openTransferModal(fromId) {
        const menu = document.getElementById('accountMoreMenu');
        if (menu) menu.style.display = 'none';
        const fabMenu = document.getElementById('mobileFabMenu');
        const fabIcon = document.getElementById('fabIcon');
        if (fabMenu) {
            fabMenu.style.display = 'none';
            if (fabIcon) fabIcon.className = 'fa-solid fa-bolt';
        }
        if (fromId) {
            const selectEl = document.getElementById('from_account_id');
            if (selectEl) selectEl.value = fromId;
        }
        const modal = document.getElementById('transferModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeTransferModal() {
        const modal = document.getElementById('transferModalBackdrop');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            const modal = document.getElementById(backdropId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    }
</script>

<!-- =========================================================================
     STYLESHEET: PURE BRAND GREEN THEME MATCHING SHOW.PHP ARCHITECTURE
     ========================================================================= -->
<style>

/* ========================================================================= */
/* Status Toggle Switch Component (Matching office/customers)                */
/* ========================================================================= */
.cust-toggle-cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.cust-status-toggle {
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease;
}

.cust-status-toggle:hover {
    transform: scale(1.06);
}

.cust-status-toggle:active {
    transform: scale(0.94);
}

.toggle-slider-track {
    width: 44px;
    height: 24px;
    border-radius: 9999px;
    background: #CBD5E1;
    position: relative;
    display: block;
    transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}

.cust-status-toggle.is-active .toggle-slider-track {
    background: #10B981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}

.toggle-slider-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    position: absolute;
    left: 3px;
    top: 3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}

.cust-status-toggle:active:not(.is-loading) .toggle-slider-thumb {
    width: 21px;
}

.toggle-spinner {
    font-size: 8px;
    color: #059669;
    line-height: 1;
}

.cust-status-toggle:not(.is-active) .toggle-spinner {
    color: #64748B;
}

.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.toggle-pulse-dot.completed {
    background: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}

:root {
    --cv-bg-start: #E8F7F0;
    --cv-bg-end: #F8FAFC;
    --cv-card-bg: #FFFFFF;
    --cv-card-border: rgba(226, 232, 240, 0.90);
    --cv-shadow: 0 4px 20px rgba(23, 107, 91, 0.06), 0 1px 3px rgba(0, 0, 0, 0.02);
    --cv-shadow-hover: 0 10px 25px rgba(23, 107, 91, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
    --cv-primary-green: #176B5B;
    --cv-primary-dark: #16443B;
    --cv-accent-green: #218765;
    --cv-navy-900: #0F172A;
    --cv-slate-800: #1E293B;
    --cv-slate-600: #475569;
    --cv-slate-400: #94A3B8;
    --cv-radius-card: 20px;
    --cv-radius-pill: 9999px;
}

.account-view-root {
    max-width: 1220px;
    margin: 0 auto;
    padding: 1.25rem 1rem 3rem 1rem;
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--cv-slate-800);
    -webkit-font-smoothing: antialiased;
}

/* 1. Header Bar (Matching office/customers/show.php) */
.view-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.85rem;
    gap: 0.65rem;
    position: relative;
    z-index: 50;
}

.view-header-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: var(--cv-primary-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
}

.view-header-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 107, 91, 0.16);
    border-color: rgba(23, 107, 91, 0.25);
    color: var(--cv-primary-dark);
}

.view-header-btn:active {
    transform: scale(0.94);
}

.view-header-right {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-shrink: 0;
}

/* Dropdown Menu */
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
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.14);
    min-width: 220px;
    padding: 0.5rem;
    z-index: 1070;
}

.view-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.85rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--cv-slate-800);
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.15s ease;
}

.view-dropdown-item:hover {
    background: #E8F7F0;
    color: var(--cv-primary-green);
}

/* 2. Hero Card (Matching office/customers/show.php scenic artwork) */
.view-hero-card {
    background: #FFFFFF;
    border-radius: var(--cv-radius-card);
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 8px 30px -8px rgba(23, 107, 91, 0.10), 0 2px 6px rgba(0, 0, 0, 0.02);
    position: relative;
    overflow: hidden;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
}

.view-hero-scenery {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 48%;
    max-width: 460px;
    background-size: cover;
    background-position: right center;
    background-repeat: no-repeat;
    opacity: 0.85;
    pointer-events: none;
    z-index: 1;
}

.view-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.88) 55%, rgba(255, 255, 255, 0.25) 100%);
    pointer-events: none;
    z-index: 2;
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
    border-radius: 20px;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.18);
    border: 3px solid #FFFFFF;
}

.view-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 17px;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
}

.view-avatar-badge {
    position: absolute;
    bottom: -4px;
    right: -4px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    border: 2px solid #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
}

.view-hero-info {
    flex: 1;
    min-width: 0;
}

.view-hero-name-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.view-hero-name {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    line-height: 1.2;
}

.hero-status-toggle-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.view-hero-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 3px 9px;
    border-radius: var(--cv-radius-pill);
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.view-hero-status-pill.active {
    background: #E8F7F0;
    color: #176B5B;
    border: 1px solid rgba(23, 107, 91, 0.25);
}

.view-hero-status-pill.inactive {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.status-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.view-hero-code-pill {
    padding: 3px 9px;
    border-radius: 8px;
    background: #F1F5F9;
    color: var(--cv-slate-600);
    font-size: 0.74rem;
    font-weight: 700;
}

.view-hero-meta-list {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.84rem;
    color: var(--cv-slate-600);
    margin-bottom: 0.85rem;
}

.view-hero-meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.view-hero-meta-item i {
    color: var(--cv-primary-green);
    font-size: 0.82rem;
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
    border-radius: var(--cv-radius-pill);
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    background: #FFFFFF;
    transition: all 0.15s ease;
    -webkit-tap-highlight-color: transparent;
}

.hero-qa-pill.qa-transfer {
    background: #E8F7F0;
    color: #176B5B;
    border-color: rgba(23, 107, 91, 0.22);
}
.hero-qa-pill.qa-transfer:hover {
    background: #176B5B;
    color: #FFFFFF;
}

.hero-qa-pill.qa-passbook {
    background: #E0F2FE;
    color: #0284C7;
    border-color: #BAE6FD;
}
.hero-qa-pill.qa-passbook:hover {
    background: #0284C7;
    color: #FFFFFF;
}

.hero-qa-pill.qa-edit {
    background: #F1F5F9;
    color: var(--cv-slate-800);
    border-color: #E2E8F0;
}
.hero-qa-pill.qa-edit:hover {
    background: #E2E8F0;
}

.hero-qa-pill.qa-expense {
    background: #FEF3C7;
    color: #B45309;
    border-color: #FDE68A;
}
.hero-qa-pill.qa-expense:hover {
    background: #B45309;
    color: #FFFFFF;
}

/* 3. 4 KPI Grid (Matching office/customers/show.php) */
.view-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}

.view-kpi-card {
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: 16px;
    padding: 1.15rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    -webkit-tap-highlight-color: transparent;
}

.view-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--cv-shadow-hover);
    border-color: rgba(23, 107, 91, 0.25);
}

.view-kpi-card:active {
    transform: scale(0.98);
}

.kpi-card-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.45rem;
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
    color: var(--cv-primary-green);
}

.kpi-icon-wrap.amber {
    background: #FEF3C7;
    color: #D97706;
}

.kpi-trend {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 999px;
}

.kpi-trend.up {
    background: #E8F7F0;
    color: #176B5B;
}

.kpi-trend.neutral {
    background: #F1F5F9;
    color: var(--cv-slate-600);
}

.kpi-tag-recent {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 999px;
    background: #E8F7F0;
    color: #176B5B;
}

.kpi-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
    margin-bottom: 0.25rem;
}

.kpi-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    line-height: 1.15;
    margin-bottom: 0.25rem;
}

.kpi-footer-note {
    font-size: 0.72rem;
    color: var(--cv-slate-400);
}

/* 4. Navigation Tabs */
.view-nav-tabs-wrapper {
    margin-bottom: 1.25rem;
    border-bottom: 1px solid #E2E8F0;
}

.view-nav-tabs {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.view-nav-tabs::-webkit-scrollbar {
    display: none;
}

.view-nav-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1.15rem;
    border-radius: var(--cv-radius-pill);
    background: transparent;
    border: 1px solid transparent;
    color: var(--cv-slate-600);
    font-size: 0.86rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}

.view-nav-pill:hover {
    background: #F1F5F9;
    color: var(--cv-navy-900);
}

.view-nav-pill.active {
    background: #176B5B;
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.22);
}

.pill-count {
    padding: 1px 6px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.12);
    font-size: 0.72rem;
    font-weight: 800;
}

.view-nav-pill.active .pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #FFFFFF;
}

/* 5. Section Cards & Split Layout (Matching office/customers/show.php architecture) */
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
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.view-section-card:hover {
    border-color: rgba(23, 107, 91, 0.2);
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.06);
}

.section-card-header {
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #F1F5F9;
}

.section-card-header.compact {
    padding: 0.85rem 1.25rem;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.section-icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    flex-shrink: 0;
}

.section-icon-badge.green {
    background: #E8F7F0;
    color: var(--cv-primary-green);
}

.section-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    letter-spacing: -0.01em;
}

.section-header-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--cv-primary-green);
    background: transparent;
    border: none;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
    -webkit-tap-highlight-color: transparent;
    transition: color 0.15s ease;
}

.section-header-link-btn:hover {
    color: var(--cv-primary-dark);
}

.section-header-link {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--cv-primary-green);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: color 0.15s ease;
}

.section-header-link:hover {
    color: var(--cv-primary-dark);
}

.section-card-body {
    padding: 1.25rem;
}

/* ========================================================================= */
/* SECTION 1: Institutional Account Information Grid                         */
/* ========================================================================= */
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
    color: var(--cv-slate-400);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.view-kv-value {
    font-size: 0.92rem;
    color: var(--cv-slate-800);
    word-break: break-word;
    overflow-wrap: anywhere;
}

.view-kv-value.bold {
    font-weight: 700;
    color: var(--cv-navy-900);
}

/* ========================================================================= */
/* SECTION 2: Compliance & Policy Reassurance Box                            */
/* ========================================================================= */
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

/* ========================================================================= */
/* SECTION 3: Latest Transactions List (Matching customers/show.php)         */
/* ========================================================================= */
.section-badge-counter {
    background: #E8F7F0;
    color: var(--cv-primary-green);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 999px;
    margin-left: 0.35rem;
}

.section-badge-counter.zero {
    background: #F1F5F9;
    color: var(--cv-slate-400);
}

.recent-tx-list {
    display: flex;
    flex-direction: column;
}

.recent-tx-card {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1.25rem;
    border-bottom: 1px solid #F1F5F9;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    color: inherit;
    -webkit-tap-highlight-color: transparent;
}

.recent-tx-card:last-child {
    border-bottom: none;
}

.recent-tx-card:hover {
    background: #F8FAFC;
}

.recent-tx-card:active {
    background: #F1F5F9;
}

.tx-thumb-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.tx-thumb-icon-wrap.credit {
    background: #E8F7F0;
    color: #176B5B;
    box-shadow: 0 2px 6px rgba(23, 107, 91, 0.12);
}

.tx-thumb-icon-wrap.debit {
    background: #FEF2F2;
    color: #DC2626;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.1);
}

.tx-card-mid {
    flex: 1;
    min-width: 0;
}

.tx-card-header-mini {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}

.tx-mini-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 4px;
    letter-spacing: 0.02em;
}

.tx-mini-code.credit {
    background: #E8F7F0;
    color: #176B5B;
}

.tx-mini-code.debit {
    background: #FEE2E2;
    color: #DC2626;
}

.tx-category-badge {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--cv-slate-600);
    background: #F1F5F9;
    padding: 1px 6px;
    border-radius: 4px;
}

.tx-card-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--cv-navy-900);
    margin: 0 0 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tx-card-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.74rem;
    color: #64748B;
}

.tx-card-meta i {
    color: var(--cv-primary-green);
    font-size: 0.72rem;
}

.tx-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.2rem;
    flex-shrink: 0;
}

.tx-amount {
    font-size: 0.95rem;
    font-weight: 800;
    white-space: nowrap;
}

.tx-amount.credit {
    color: #218765;
}

.tx-amount.debit {
    color: #DC2626;
}

.tx-balance-after {
    font-size: 0.72rem;
    color: #94A3B8;
    white-space: nowrap;
}

.tx-card-arrow {
    color: #CBD5E1;
    font-size: 0.82rem;
    margin-left: 0.25rem;
    flex-shrink: 0;
    transition: transform 0.15s ease;
}

.recent-tx-card:hover .tx-card-arrow {
    transform: translateX(3px);
    color: var(--cv-primary-green);
}

.recent-tx-footer {
    padding: 0.75rem 1.25rem;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
    text-align: center;
}

.recent-view-more-btn {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--cv-primary-green);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.recent-view-more-btn:hover {
    color: var(--cv-primary-dark);
}

/* Empty State for Transactions matching customers/show.php:2486-2599 */
.recent-tx-empty-state {
    padding: 2.25rem 1.5rem 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.empty-state-icon-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.85rem;
}

.empty-icon-glow {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(23, 107, 91, 0.18) 0%, rgba(23, 107, 91, 0) 70%);
    animation: emptyGlowPulse 3s ease-in-out infinite;
}

@keyframes emptyGlowPulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.15); opacity: 1; }
}

.empty-icon-badge {
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: linear-gradient(135deg, #E8F7F0 0%, #D1EFE2 100%);
    border: 1px solid rgba(23, 107, 91, 0.25);
    color: var(--cv-primary-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.08);
}

.empty-state-heading {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0 0 0.4rem 0;
    letter-spacing: -0.01em;
}

.empty-state-desc {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0 0 1.25rem 0;
    line-height: 1.55;
    max-width: 290px;
}

.empty-state-actions {
    display: flex;
    justify-content: center;
    width: 100%;
}

.btn-create-tx-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.65rem 1.25rem;
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    -webkit-tap-highlight-color: transparent;
}

.btn-create-tx-primary:hover {
    color: #FFFFFF;
    background: linear-gradient(135deg, #16443B 0%, #0F2D27 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.35);
}

.btn-create-tx-primary:active {
    transform: scale(0.97);
}

.empty-state-meta-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1.15rem;
    padding: 0.35rem 0.85rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: var(--cv-radius-pill);
    font-size: 0.72rem;
    color: #64748B;
    font-weight: 600;
}

.empty-state-meta-hint i {
    color: #218765;
}

/* ========================================================================= */
/* SECTION 4: Quick Actions 4-tile Grid (Direct match with customers/show.php)*/
/* ========================================================================= */
.quick-actions-title {
    font-size: 0.85rem;
    font-weight: 700;
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
    color: var(--cv-slate-800);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    -webkit-tap-highlight-color: transparent;
    width: 100%;
    box-sizing: border-box;
}

.quick-action-tile:hover {
    background: #FFFFFF;
    border-color: rgba(23, 107, 91, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.1);
}

.quick-action-tile:active {
    transform: scale(0.95);
}

.qa-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.qa-icon-wrap.green {
    background: #E8F7F0;
    color: #176B5B;
}

.qa-icon-wrap.purple {
    background: #F3E8FF;
    color: #9333EA;
}

.qa-icon-wrap.cyan {
    background: #E0F2FE;
    color: #0284C7;
}

.qa-icon-wrap.amber {
    background: #FEF3C7;
    color: #D97706;
}

.qa-text {
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--cv-navy-900);
    text-align: center;
    line-height: 1.2;
    white-space: nowrap;
}

/* ========================================================================= */
/* Custom Modal System (Scoped for Accounts View)                            */
/* ========================================================================= */
.modal-backdrop-custom {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 35, 30, 0.55) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999 !important;
    padding: 1rem;
    box-sizing: border-box;
}

.modal-dialog-custom {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(15, 35, 30, 0.35);
    border: 1px solid rgba(23, 107, 91, 0.18);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalSlideInAnim 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    z-index: 100000;
}

@keyframes modalSlideInAnim {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header-custom {
    padding: 1.15rem 1.35rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(22, 68, 59, 0.08);
    background: #FFFFFF;
    flex-shrink: 0;
}

.modal-close-custom {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #F1F5F9;
    border: none;
    font-size: 1.4rem;
    line-height: 1;
    color: #64748B;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    -webkit-tap-highlight-color: transparent;
}

.modal-close-custom:hover {
    background: #E2E8F0;
    color: #0F172A;
    transform: scale(1.05);
}

.modal-body-custom {
    padding: 1.25rem 1.35rem;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    flex: 1;
}

.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: #F8FAFC;
    flex-shrink: 0;
}

/* ========================================================================= */
/* Native Mobile Cards for Passbook & Transfers (Matching office/customers)  */
/* ========================================================================= */
.desktop-ledger-table,
.desktop-transfers-table {
    display: block;
}

.mobile-ledger-cards-list,
.mobile-transfers-cards-list {
    display: none;
}

.mb-account-card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.15s ease;
}

.mb-account-card:hover {
    border-color: rgba(23, 107, 91, 0.3);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.08);
}

.mb-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.mb-card-date {
    font-size: 0.74rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.mb-card-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mb-card-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
    line-height: 1.3;
}

.mb-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 2px;
}

.mb-card-ref {
    font-size: 0.72rem;
    color: #64748B;
}

.mb-card-notes {
    font-size: 0.8rem;
    color: #475569;
    margin: 0;
}

.mb-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #F1F5F9;
    margin-top: 2px;
}

.mb-card-balance {
    font-size: 0.76rem;
    color: #64748B;
}

.mb-card-balance .label {
    margin-right: 2px;
}

.mb-card-balance .val {
    font-weight: 700;
    color: #0F172A;
}

.mb-card-amount {
    font-size: 1.05rem;
    font-weight: 800;
}

.mb-card-amount.credit {
    color: #218765;
}

.mb-card-amount.debit {
    color: #DC2626;
}

/* ========================================================================= */
/* Mobile Floating Quick Action Button (FAB)                                 */
/* ========================================================================= */
.mobile-view-fab-wrapper {
    display: none;
}

@media (max-width: 768px) {
    .mobile-view-fab-wrapper {
        display: block !important;
        position: fixed !important;
        right: 18px !important;
        bottom: 85px !important; /* Clears mobile notched bottom nav */
        z-index: 1060 !important;
    }

    .mobile-view-fab {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
        color: #FFFFFF;
        border: none;
        box-shadow: 0 6px 20px rgba(23, 107, 91, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        -webkit-tap-highlight-color: transparent;
    }

    .mobile-view-fab:active {
        transform: scale(0.92);
    }

    .mobile-fab-menu {
        position: absolute;
        right: 0;
        bottom: calc(100% + 12px);
        display: flex;
        flex-direction: column;
        gap: 9px;
        align-items: flex-end;
        animation: fabSlideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fabSlideUp {
        from { opacity: 0; transform: translateY(12px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .mobile-fab-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        padding: 0;
        -webkit-tap-highlight-color: transparent;
    }

    .fab-item-label {
        background: #FFFFFF;
        color: #0F172A;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 5px 11px;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        white-space: nowrap;
        border: 1px solid #E2E8F0;
    }

    .fab-item-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 0.95rem;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        flex-shrink: 0;
    }

    .fab-item-icon.green {
        background: #218765;
    }
    .fab-item-icon.primary {
        background: #176B5B;
    }
    .fab-item-icon.amber {
        background: #C78A35;
    }
    .fab-item-icon.purple {
        background: #8B5CF6;
    }
}

/* ========================================================================= */
/* FAITHFUL RESPONSIVENESS ACROSS ALL DEVICES (Matching customers/show.php)  */
/* ========================================================================= */

/* Tablet Breakpoint (769px to 1023px) */
@media (min-width: 769px) and (max-width: 1023px) {
    .view-layout-split {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .view-col-main, .view-col-side {
        gap: 1rem;
    }

    .view-hero-content {
        gap: 1.15rem;
    }

    .view-hero-name {
        font-size: 1.35rem;
    }

    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
}

/* Mobile Screens (<= 768px matching office/customers/show.php:3498-3802) */
@media (max-width: 768px) {
    .account-view-root {
        padding: 0.65rem 0.65rem 95px 0.65rem !important;
        /* 95px clearance for mobile docked bottom navbar */
    }

    /* 1. Header Bar on Mobile */
    .view-header-bar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        position: relative !important;
        z-index: 50 !important;
        margin-bottom: 0.85rem !important;
        gap: 0.5rem !important;
    }

    .view-header-right {
        display: flex !important;
        align-items: center !important;
        gap: 0.45rem !important;
        flex-shrink: 0 !important;
    }

    .view-header-btn {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        font-size: 0.88rem !important;
    }

    .view-dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        top: calc(100% + 8px) !important;
        z-index: 1070 !important;
        min-width: 210px !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2) !important;
    }

    /* 2. Hero Card on Mobile */
    .view-hero-card {
        padding: 1.1rem 1rem !important;
        border-radius: 18px !important;
        margin-bottom: 0.85rem !important;
    }

    .view-hero-scenery {
        width: 50% !important;
        max-width: 220px !important;
        opacity: 0.75 !important;
        background-position: right center !important;
    }

    .view-hero-overlay {
        background: linear-gradient(90deg, #FFFFFF 0%, rgba(255, 255, 255, 0.95) 55%, rgba(255, 255, 255, 0.45) 80%, rgba(255, 255, 255, 0.05) 100%) !important;
    }

    .view-hero-content {
        display: flex !important;
        align-items: center !important;
        gap: 0.85rem !important;
    }

    .view-avatar-wrap {
        width: 74px !important;
        height: 74px !important;
        border-radius: 18px !important;
        flex-shrink: 0 !important;
    }

    .view-avatar-initials {
        font-size: 1.75rem !important;
        border-radius: 16px !important;
    }

    .view-avatar-badge {
        width: 24px !important;
        height: 24px !important;
        font-size: 0.65rem !important;
    }

    .view-hero-name {
        font-size: 1.2rem !important;
    }

    .hero-status-toggle-wrapper {
        padding: 2px 7px 2px 3px !important;
        gap: 6px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-track {
        width: 38px !important;
        height: 22px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-thumb {
        width: 16px !important;
        height: 16px !important;
    }

    .hero-status-toggle-wrapper .cust-status-toggle.is-active .toggle-slider-thumb {
        transform: translateX(16px) !important;
    }

    .view-hero-meta-list {
        gap: 0.45rem !important;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .view-hero-meta-item {
        font-size: 0.8rem !important;
    }

    .view-hero-quick-actions {
        margin-top: 0.65rem !important;
        gap: 0.35rem !important;
        display: flex !important;
        flex-wrap: wrap !important;
    }

    .hero-qa-pill {
        padding: 0.32rem 0.65rem !important;
        font-size: 0.72rem !important;
    }

    /* 3. KPI Grid on Mobile (2x2 grid matching customers/show.php:3616-3653) */
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem !important;
        margin-bottom: 0.85rem !important;
    }

    .view-kpi-card {
        padding: 0.85rem 0.75rem !important;
        border-radius: 16px !important;
    }

    .kpi-icon-wrap {
        width: 32px !important;
        height: 32px !important;
        font-size: 0.85rem !important;
        border-radius: 8px !important;
    }

    .kpi-label {
        font-size: 0.7rem !important;
    }

    .kpi-value {
        font-size: 1.2rem !important;
    }

    .kpi-footer-note {
        font-size: 0.68rem !important;
    }

    /* 4. Horizontal Pill Tabs */
    .view-nav-tabs-wrapper {
        margin-bottom: 0.85rem !important;
    }

    .view-nav-tabs {
        padding: 4px !important;
        gap: 4px !important;
        scrollbar-width: none !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .view-nav-tabs::-webkit-scrollbar {
        display: none !important;
    }

    .view-nav-pill {
        padding: 0.55rem 0.95rem !important;
        font-size: 0.82rem !important;
        gap: 0.4rem !important;
    }

    /* 5. Natural Section Flow & Ordering on Mobile (Matching office/customers/show.php:3671-3678) */
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

    .card-institution,
    .card-latest-tx,
    .card-quick-actions {
        margin-top: 0 !important;
    }

    .view-section-card {
        border-radius: 16px !important;
    }

    .section-card-header {
        padding: 0.9rem 1rem !important;
    }

    .section-card-header.compact {
        padding: 0.8rem 1rem !important;
    }

    .section-card-body {
        padding: 1rem !important;
    }

    /* Responsive Section 1: Key-Value Grid */
    .view-kv-grid {
        grid-template-columns: 1fr 1fr !important;
        row-gap: 0.85rem !important;
        column-gap: 0.75rem !important;
    }

    .view-kv-label {
        font-size: 0.68rem !important;
    }

    .view-kv-value {
        font-size: 0.86rem !important;
    }



    /* Responsive Section 3: Latest Transactions List */
    .recent-tx-card {
        padding: 0.75rem 1rem !important;
        gap: 0.7rem !important;
    }

    .tx-thumb-icon-wrap {
        width: 38px !important;
        height: 38px !important;
        font-size: 0.88rem !important;
        border-radius: 10px !important;
    }

    .tx-card-title {
        font-size: 0.85rem !important;
    }

    .tx-card-meta {
        gap: 0.45rem !important;
        font-size: 0.7rem !important;
    }

    .tx-amount {
        font-size: 0.88rem !important;
    }

    .tx-balance-after {
        font-size: 0.68rem !important;
    }

    /* Responsive Section 4: Quick Actions 4-tile Grid */
    .quick-actions-grid {
        gap: 0.5rem !important;
    }

    .quick-action-tile {
        padding: 0.75rem 0.35rem !important;
        border-radius: 12px !important;
    }

    .qa-icon-wrap {
        width: 34px !important;
        height: 34px !important;
        font-size: 0.95rem !important;
    }

    .qa-text {
        font-size: 0.68rem !important;
    }

    /* Hide wide desktop tables and show native mobile cards */
    .desktop-ledger-table,
    .desktop-transfers-table {
        display: none !important;
    }

    .mobile-ledger-cards-list,
    .mobile-transfers-cards-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        padding: 0.85rem !important;
    }

    /* Modals on Mobile */
    .modal-backdrop-custom {
        padding: 0.75rem !important;
    }

    .modal-dialog-custom {
        max-width: 100% !important;
        max-height: 94vh !important;
        border-radius: 16px !important;
    }

    .modal-body-custom {
        padding: 1rem !important;
        max-height: 70vh !important;
    }

    .modal-footer-custom {
        padding: 0.85rem 1rem !important;
    }
}

/* Small Screens (width <= 400px - Matching office/customers/show.php:3805-3823) */
@media (max-width: 400px) {
    .view-kv-grid {
        grid-template-columns: 1fr 1fr !important;
        column-gap: 0.5rem !important;
        row-gap: 0.75rem !important;
    }

    .view-hero-scenery {
        width: 42% !important;
        max-width: 170px !important;
    }

    .kpi-value {
        font-size: 1.1rem !important;
    }

    .kpi-value.text-compact {
        font-size: 0.85rem !important;
    }

    .view-avatar-wrap {
        width: 68px !important;
        height: 68px !important;
    }

    .view-avatar-initials {
        font-size: 1.6rem !important;
    }



    /* Small Screen Latest Transactions */
    .recent-tx-card {
        padding: 0.65rem 0.75rem !important;
        gap: 0.55rem !important;
    }

    .tx-thumb-icon-wrap {
        width: 34px !important;
        height: 34px !important;
        font-size: 0.82rem !important;
    }

    .tx-card-title {
        font-size: 0.82rem !important;
    }

    .tx-amount {
        font-size: 0.82rem !important;
    }

    .tx-card-arrow {
        display: none !important;
    }

    /* Small Screen Quick Actions 4-tile grid */
    .quick-actions-grid {
        gap: 0.35rem !important;
    }

    .quick-action-tile {
        padding: 0.65rem 0.2rem !important;
        border-radius: 10px !important;
    }

    .qa-icon-wrap {
        width: 30px !important;
        height: 30px !important;
        font-size: 0.82rem !important;
        border-radius: 8px !important;
    }

    .qa-text {
        font-size: 0.62rem !important;
    }
}

/* Large Screens (width >= 1024px - Matching office/customers/show.php:3825-3860) */
@media (min-width: 1024px) {
    .account-view-root {
        padding: 1.5rem 1.5rem 4rem 1.5rem;
    }

    .view-hero-card {
        padding: 1.75rem 2rem;
    }

    .view-hero-content {
        max-width: 65%;
        gap: 1.75rem;
    }

    .view-avatar-wrap {
        width: 92px;
        height: 92px;
    }

    .view-hero-name {
        font-size: 1.55rem;
    }

    .view-kpi-card {
        padding: 1.25rem 1.25rem;
    }

    .view-kpi-grid {
        gap: 1.25rem;
    }

    .view-layout-split {
        grid-template-columns: 1.15fr 0.85fr;
        gap: 1.5rem;
    }

    .view-col-main, .view-col-side {
        gap: 1.5rem;
    }
}

</style>
