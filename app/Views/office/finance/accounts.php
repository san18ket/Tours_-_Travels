<?php
/**
 * MANASWI TOURS & TRAVELS — BANK ACCOUNTS & TREASURY VAULTS
 * Fully responsive: Executive 7-column PMS table on Desktop + Native App Cards on Mobile
 */
$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<button type="button" class="btn btn-outline" onclick="openTransferModal()"><i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer Funds</span></button>'
    . '<button type="button" class="btn btn-primary" onclick="openAccountModal()"><i class="fa-solid fa-plus"></i> <span>Add Account</span></button>'
    . '</div>';

$selectedType = $selectedType ?? '';
$selectedStatus = $selectedStatus ?? '';
$search = $search ?? '';
$totRec = $totalRecords ?? count($accounts);
$currP = $currentPage ?? 1;
$pSize = $perPage ?? 10;
$from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to   = min($currP * $pSize, $totRec);

$makeFilterUrl = function(array $newParams) use ($search, $selectedType, $selectedStatus, $pSize) {
    $q = [];
    if (!empty($search)) $q['search'] = $search;
    if (!empty($selectedType)) $q['type'] = $selectedType;
    if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
    if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
    foreach ($newParams as $k => $v) {
        if ($v === '' || $v === null) {
            unset($q[$k]);
        } else {
            $q[$k] = $v;
        }
    }
    return url('/office/finance/accounts' . (!empty($q) ? '?' . http_build_query($q) : ''));
};

$buildPageUrl = function(int $p) use ($search, $selectedType, $selectedStatus, $pSize) {
    $q = [];
    if (!empty($search)) $q['search'] = $search;
    if (!empty($selectedType)) $q['type'] = $selectedType;
    if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
    if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
    $q['page'] = $p;
    return url('/office/finance/accounts?' . http_build_query($q));
};
?>

<div class="bookings-page-view finance-page-view" id="financeAccountsPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Hidden on Mobile) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Bank Accounts & Cash Vaults',
            'description' => 'Manage commercial current accounts, petty cash vaults, UPI gateways, and inter-account fund reallocations.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Treasury Vaults',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'Bank Accounts', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Hidden on Mobile) -->
    <div class="booking-kpi-grid" id="accountsKpiGrid">
        <div class="booking-kpi-card <?= empty($selectedType) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Liquid Reserve</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-primary);"><?= format_currency($totalBalance) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= $accountCount ?> active treasury vaults</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedType === 'bank' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts?type=bank') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Bank Accounts</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--status-success);"><?= format_currency($bankBalance) ?></div>
            <div class="booking-kpi-sub">
                <span><?= $bankCount ?> current &amp; escrow balances</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedType === 'cash' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts?type=cash') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cash in Hand</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #C78A35;"><?= format_currency($cashBalance) ?></div>
            <div class="booking-kpi-sub">
                <span><?= $cashCount ?> counter &amp; driver cash drawers</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <div class="booking-kpi-card <?= $selectedType === 'gateway' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/accounts?type=gateway') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Gateways &amp; UPI</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: var(--brand-secondary);"><?= format_currency($gatewayBalance) ?></div>
            <div class="booking-kpi-sub">
                <span><?= $gatewayCount ?> digital gateway accounts</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Application Architecture) -->
    <form method="GET" action="<?= url('/office/finance/accounts') ?>" class="filter-bar-compact" id="accountsFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by account name, bank, account #, IFSC..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/accounts') ?>'" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <select name="type" id="typeInput" onchange="this.form.submit()">
            <option value="" <?= empty($selectedType) ? 'selected' : '' ?>>All Account Types</option>
            <option value="bank" <?= $selectedType === 'bank' ? 'selected' : '' ?>>Commercial Bank Accounts</option>
            <option value="cash" <?= $selectedType === 'cash' ? 'selected' : '' ?>>Counter Cash Drawers</option>
            <option value="gateway" <?= $selectedType === 'gateway' ? 'selected' : '' ?>>Payment Gateways &amp; UPI</option>
        </select>

        <select name="status" id="statusInput" onchange="this.form.submit()">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active Accounts</option>
            <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive Accounts</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="this.form.submit()">
            <?php foreach ([10, 15, 25, 50] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/accounts') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search & Horizontal Status Scroll (Exactly Like office/customers) -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/finance') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Accounts &amp; Banks</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openAccountModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Add Account</span>
            </button>
        </div>

        <!-- Mobile Search Bar & Filter Toggle Button -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/accounts') ?>" class="mv-search-form" id="mvAccountsSearchForm">
                <?php if (!empty($selectedType)): ?>
                    <input type="hidden" name="type" value="<?= e($selectedType) ?>">
                <?php endif; ?>
                <?php if (!empty($selectedStatus)): ?>
                    <input type="hidden" name="status" value="<?= e($selectedStatus) ?>">
                <?php endif; ?>
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvAccountsSearchInput" placeholder="Search account, bank, A/C #..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <button type="button" class="mv-search-clear" onclick="location.href='<?= $makeFilterUrl(['search' => '']) ?>'" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/accounts') ?>">
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?= e($search) ?>">
                    <?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvTypeSelect">Type:</label>
                        <select name="type" id="mvTypeSelect" onchange="this.form.submit()">
                            <option value="" <?= empty($selectedType) ? 'selected' : '' ?>>All Account Types</option>
                            <option value="bank" <?= $selectedType === 'bank' ? 'selected' : '' ?>>Bank Accounts</option>
                            <option value="cash" <?= $selectedType === 'cash' ? 'selected' : '' ?>>Cash Drawers</option>
                            <option value="gateway" <?= $selectedType === 'gateway' ? 'selected' : '' ?>>Gateways &amp; UPI</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvStatusSelect">Status:</label>
                        <select name="status" id="mvStatusSelect" onchange="this.form.submit()">
                            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
                            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
                            <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect" onchange="this.form.submit()">
                            <?php foreach ([10, 15, 25, 50] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center;">Apply Filter</button>
                        <a href="<?= url('/office/finance/accounts') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <div class="mv-status-scroll" id="accountsMvStatusScroll">
            <a href="<?= $makeFilterUrl(['type' => '', 'status' => '']) ?>" class="mv-pill <?= empty($selectedType) && empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Vaults</span>
                <span class="mv-pill-cnt"><?= number_format($totalAll ?? count($accounts)) ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['type' => 'bank', 'status' => '']) ?>" class="mv-pill <?= $selectedType === 'bank' ? 'active' : '' ?>">
                <span>Banks</span>
                <span class="mv-pill-cnt"><?= $bankCount ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['type' => 'cash', 'status' => '']) ?>" class="mv-pill <?= $selectedType === 'cash' ? 'active' : '' ?>">
                <span>Cash</span>
                <span class="mv-pill-cnt"><?= $cashCount ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['type' => 'gateway', 'status' => '']) ?>" class="mv-pill <?= $selectedType === 'gateway' ? 'active' : '' ?>">
                <span>Gateways</span>
                <span class="mv-pill-cnt"><?= $gatewayCount ?></span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'active']) ?>" class="mv-pill <?= $selectedStatus === 'active' ? 'active' : '' ?>">
                <span>Active</span>
            </a>
            <a href="<?= $makeFilterUrl(['status' => 'inactive']) ?>" class="mv-pill <?= $selectedStatus === 'inactive' ? 'active' : '' ?>">
                <span>Inactive</span>
            </a>
            <button type="button" class="mv-pill" onclick="openTransferModal()">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer</span>
            </button>
        </div>
    </div>

    <!-- 5. Accounts Section: Modern Table (Desktop / LG) + Native Mobile Cards (< 992px) -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Treasury Accounts Manifest</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Accounts:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Desktop / LG Devices) — STRICT 7-COLUMN SCHEMA -->
        <div class="modern-table-container">
            <table class="modern-table" id="accountsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 250px;">Account &amp; Institution</th>
                        <th style="width: 140px;">Type</th>
                        <th style="text-align: right; width: 160px;">Available Balance</th>
                        <th style="text-align: right; width: 140px;">Opening Balance</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-center" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message" style="text-align: center; padding: 2.75rem 1rem;">
                                <div class="no-data-icon"><i class="fa-solid fa-building-columns"></i></div>
                                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No treasury accounts found</h4>
                                <p class="text-muted" style="margin: 0;">Try adjusting your search criteria or register a new commercial account.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $idx => $acc):
                            $isBank = $acc['account_type'] === 'bank';
                            $isCash = $acc['account_type'] === 'cash';
                            $isGateway = $acc['account_type'] === 'gateway';
                            $icon = $isCash ? 'fa-wallet' : ($isBank ? 'fa-building-columns' : 'fa-qrcode');
                            $badgeBg = $isCash ? 'rgba(199, 138, 53, 0.12)' : ($isBank ? 'rgba(33, 135, 101, 0.12)' : 'rgba(23, 107, 91, 0.12)');
                            $badgeColor = $isCash ? '#C78A35' : ($isBank ? '#218765' : '#176B5B');
                            $typeName = $isCash ? 'Cash Vault' : ($isBank ? 'Bank Account' : 'Payment Gateway');
                            $status = strtolower($acc['status'] ?? 'active');
                            $isActive = ($status === 'active');
                            $statusClass = $isActive ? 'completed' : 'cancelled';
                            $statusText = $isActive ? 'Active' : 'Inactive';
                        ?>
                        <tr>
                            <!-- 1. # (Serial) -->
                            <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                <?= $from + $idx ?>
                            </td>

                            <!-- 2. Account & Institution -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0;">
                                        <i class="fa-solid <?= $icon ?>"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <a href="<?= url('/office/finance/accounts/' . $acc['id']) ?>" style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.94rem; display: block; text-decoration: none;" class="account-title-link" title="View account details">
                                            <?= e($acc['account_name']) ?>
                                        </a>
                                        <?php if (!empty($acc['bank_name']) || !empty($acc['account_number'])): ?>
                                            <span style="font-size: 0.78rem; color: var(--text-muted); display: block; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?php if (!empty($acc['bank_name'])): ?><?= e($acc['bank_name']) ?><?php endif; ?>
                                                <?php if (!empty($acc['bank_name']) && !empty($acc['account_number'])): ?> &bull; <?php endif; ?>
                                                <?php if (!empty($acc['account_number'])): ?>A/C <?= e($acc['account_number']) ?><?php endif; ?>
                                                <?php if (!empty($acc['ifsc_code'])): ?> (IFSC: <?= e($acc['ifsc_code']) ?>)<?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.78rem; color: var(--text-muted); display: block; margin-top: 1px;">
                                                <?= $typeName ?> &bull; Treasury #<?= $acc['id'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Type -->
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 8px;">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                    <span><?= $typeName ?></span>
                                </span>
                            </td>

                            <!-- 4. Available Balance -->
                            <td style="text-align: right;">
                                <strong style="font-size: 1.05rem; font-weight: 800; color: #218765; display: block;">
                                    <?= format_currency((float)$acc['current_balance']) ?>
                                </strong>
                            </td>

                            <!-- 5. Opening Balance -->
                            <td style="text-align: right; font-size: 0.88rem; color: var(--text-muted); font-weight: 600;">
                                <?= format_currency((float)$acc['opening_balance']) ?>
                            </td>

                            <!-- 6. Status (Toggle Switch like office/customers) -->
                            <td class="text-center">
                                <div class="cust-toggle-cell" id="accountToggleWrap-<?= (int)$acc['id'] ?>">
                                    <button type="button" 
                                            class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                            data-id="<?= (int)$acc['id'] ?>"
                                            data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                            onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                            title="Click to toggle status (currently <?= $statusText ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <span class="cust-toggle-pill <?= $statusClass ?>">
                                        <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                        <span class="toggle-label-text"><?= $statusText ?></span>
                                    </span>
                                </div>
                            </td>

                            <!-- 7. Actions (Floating Popup with View, Edit, Passbook, Transfer) -->
                            <td class="text-center">
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <a href="<?= url('/office/finance/accounts/' . $acc['id']) ?>" class="action-btn-table view" title="View Account Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button type="button" class="action-btn-table edit" title="Edit Account" onclick="openEditAccountModal(<?= htmlspecialchars(json_encode($acc), ENT_QUOTES, 'UTF-8') ?>, event)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="<?= url('/office/finance/transactions?account_id=' . $acc['id']) ?>" class="action-btn-table passbook" title="Passbook / Ledger">
                                            <i class="fa-solid fa-book-open"></i>
                                        </a>
                                        <button type="button" class="action-btn-table transfer" title="Transfer Funds" onclick="openTransferModal(<?= (int)$acc['id'] ?>, event)">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile App Cards (Screen 3 Native Architecture matching office/customers) -->
        <div class="cards-grid" id="accountsCards">
            <?php if (empty($accounts)): ?>
            <div class="no-data-message" style="text-align: center; padding: 2.5rem 1rem; background: #fff; border-radius: 16px; border: 1px solid #edf2f7;">
                <div class="no-data-icon"><i class="fa-solid fa-building-columns"></i></div>
                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No treasury accounts found</h4>
                <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Try adjusting your search criteria or register a new commercial account.</p>
            </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): 
                    $isBank = $acc['account_type'] === 'bank';
                    $isCash = $acc['account_type'] === 'cash';
                    $icon = $isCash ? 'fa-wallet' : ($isBank ? 'fa-building-columns' : 'fa-qrcode');
                    $badgeBg = $isCash ? 'rgba(199, 138, 53, 0.12)' : ($isBank ? 'rgba(33, 135, 101, 0.12)' : 'rgba(23, 107, 91, 0.12)');
                    $badgeColor = $isCash ? '#C78A35' : ($isBank ? '#218765' : '#176B5B');
                    $typeName = $isCash ? 'Cash Vault' : ($isBank ? 'Bank Account' : 'Payment Gateway');
                    $status = strtolower($acc['status'] ?? 'active');
                    $isActive = ($status === 'active');
                    $statusClass = $isActive ? 'completed' : 'cancelled';
                    $statusText = $isActive ? 'Active' : 'Inactive';
                ?>
                <div class="mv-booking-card-wrapper">
                    <a href="<?= url('/office/finance/accounts/' . $acc['id']) ?>" class="mv-booking-card">
                        <!-- Left: Avatar Icon Badge -->
                        <div class="mv-thumb-wrap">
                            <div class="mv-thumb-avatar" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>;">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                        </div>

                        <!-- Middle: Details -->
                        <div class="mv-details">
                            <div class="mv-tour-title" title="<?= e($acc['account_name']) ?>">
                                <?= e($acc['account_name']) ?>
                            </div>
                            <div class="mv-date-duration">
                                <?php if (!empty($acc['bank_name'])): ?>
                                    <i class="fa-solid fa-landmark"></i>
                                    <span><?= e($acc['bank_name']) ?><?= !empty($acc['account_number']) ? ' &bull; A/C ' . e($acc['account_number']) : '' ?></span>
                                <?php else: ?>
                                    <i class="fa-solid fa-vault"></i>
                                    <span><?= $typeName ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($acc['ifsc_code'])): ?>
                            <div class="mv-passenger" style="font-family: monospace; font-size: 11px; font-weight: 600; color: var(--brand-primary);">
                                <i class="fa-solid fa-hashtag"></i>
                                <span>IFSC: <?= e($acc['ifsc_code']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="mv-vehicle">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>Opening: <?= format_currency((float)$acc['opening_balance']) ?></span>
                            </div>
                        </div>

                        <!-- Right: Status Toggle Switch + Balance -->
                        <div class="mv-right-col">
                            <div class="mv-status-toggle-wrap" onclick="event.stopPropagation();">
                                <button type="button" 
                                        class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$acc['id'] ?>"
                                        data-name="<?= addslashes(e($acc['account_name'])) ?>"
                                        onclick="toggleAccountStatus(<?= (int)$acc['id'] ?>, this, event)"
                                        title="Click to toggle status (currently <?= $statusText ?>)">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb">
                                            <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                        </span>
                                    </span>
                                </button>
                                <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                            <div class="mv-price" style="color: #218765; font-weight: 800; font-size: 14.5px;">
                                <?= format_currency((float)$acc['current_balance']) ?>
                            </div>
                        </div>
                    </a>

                    <!-- 3-Dots Quick Action Popup (Corner) -->
                    <div class="mv-card-actions">
                        <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="card-btns-popup">
                            <a href="<?= url('/office/finance/accounts/' . $acc['id']) ?>" class="action-btn-table view" title="View Account Details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button type="button" class="action-btn-table edit" title="Edit Account" onclick="openEditAccountModal(<?= htmlspecialchars(json_encode($acc), ENT_QUOTES, 'UTF-8') ?>, event)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <a href="<?= url('/office/finance/transactions?account_id=' . $acc['id']) ?>" class="action-btn-table passbook" title="Passbook / Ledger">
                                <i class="fa-solid fa-book-open"></i>
                            </a>
                            <button type="button" class="action-btn-table transfer" title="Transfer Funds" onclick="openTransferModal(<?= (int)$acc['id'] ?>, event)">
                                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 6. Modern Pagination Controls (Exact Match to Project Design) -->
        <div id="accountsPaginationWrapper">
            <?php if (($totalPages ?? 1) > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> account(s)
                </div>
                <div class="pagination">
                    <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page">&#10094;</a>
                    <?php
                        $rangeStart = max(1, $currP - 1);
                        $rangeEnd   = min($totalPages, $rangeStart + 2);
                        if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                            $rangeStart = max(1, $rangeEnd - 2);
                        }
                        for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                    ?>
                        <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($rangeEnd < $totalPages): ?>
                        <span class="dots">...</span>
                        <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>
                    <?php endif; ?>
                    <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page">&#10095;</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Add Financial Account                                              -->
<!-- ========================================================================= -->
<div id="accountModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'accountModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-plus-circle" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Create Treasury Account
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeAccountModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/accounts') ?>" method="POST" id="createAccountForm">
            <?= csrf_field() ?>
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="acc_name">Account / Vault Name <span class="req">*</span></label>
                    <input type="text" name="account_name" id="acc_name" class="form-control" placeholder="e.g. HDFC Current A/C or Cash Vault Main" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="acc_type">Account Classification <span class="req">*</span></label>
                    <select name="account_type" id="acc_type" class="form-select" onchange="toggleBankFields(this.value)">
                        <option value="bank">Commercial Bank Current Account</option>
                        <option value="cash">Counter Cash / Petty Vault</option>
                        <option value="gateway">Payment Gateway (Razorpay/Stripe/UPI)</option>
                    </select>
                </div>

                <div id="bankFields" style="display: block;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="acc_bank">Bank Institution</label>
                            <input type="text" name="bank_name" id="acc_bank" class="form-control" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="acc_num">Account Number</label>
                            <input type="text" name="account_number" id="acc_num" class="form-control" placeholder="A/C Number">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="acc_ifsc">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="acc_ifsc" class="form-control" placeholder="e.g. HDFC0001234">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="acc_open_bal">Opening Ledger Balance (INR)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" id="acc_open_bal" class="form-control" placeholder="0.00" value="0.00">
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Create Account</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Edit Financial Account                                             -->
<!-- ========================================================================= -->
<div id="editAccountModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editAccountModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Edit Treasury Account
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditAccountModal()">&times;</button>
        </div>
        <form action="" method="POST" id="editAccountForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/accounts') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_acc_name">Account / Vault Name <span class="req">*</span></label>
                    <input type="text" name="account_name" id="edit_acc_name" class="form-control" placeholder="Account Name" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_acc_type">Account Classification <span class="req">*</span></label>
                    <select name="account_type" id="edit_acc_type" class="form-select" onchange="toggleEditBankFields(this.value)">
                        <option value="bank">Commercial Bank Current Account</option>
                        <option value="cash">Counter Cash / Petty Vault</option>
                        <option value="gateway">Payment Gateway (Razorpay/Stripe/UPI)</option>
                    </select>
                </div>

                <div id="editBankFields" style="display: block;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="edit_acc_bank">Bank Institution</label>
                            <input type="text" name="bank_name" id="edit_acc_bank" class="form-control" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edit_acc_num">Account Number</label>
                            <input type="text" name="account_number" id="edit_acc_num" class="form-control" placeholder="A/C Number">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="edit_acc_ifsc">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="edit_acc_ifsc" class="form-control" placeholder="e.g. HDFC0001234">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_acc_status">Status</label>
                    <select name="status" id="edit_acc_status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900);">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color: var(--brand-primary); margin-right: 0.45rem;"></i>
                Internal Account Fund Transfer
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeTransferModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transfer') ?>" method="POST" id="fundTransferForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/accounts') ?>">
            <div class="modal-body-custom">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="from_account_id">From Account (Source) <span class="req">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select" required>
                            <option value="">— Select source —</option>
                            <?php foreach (($activeAccounts ?? $accounts) as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?> (<?= format_currency((float)$acc['current_balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="to_account_id">To Account (Destination) <span class="req">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select" required>
                            <option value="">— Select destination —</option>
                            <?php foreach (($activeAccounts ?? $accounts) as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?></option>
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
    /* Floating Action Buttons Menu & Card Popup Handlers                        */
    /* ========================================================================= */
    function toggleActions(btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const popup = btn.nextElementSibling;
        const isOpen = popup && popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (popup && !isOpen) {
            popup.classList.add('open');
        }
    }

    function toggleCardActions(btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const popup = btn.nextElementSibling;
        const isOpen = popup && popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (popup && !isOpen) {
            popup.classList.add('open');
        }
    }

    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        const b = document.getElementById('mvFilterToggleBtn');
        if (p) {
            const isHidden = (p.style.display === 'none' || p.style.display === '');
            p.style.display = isHidden ? 'block' : 'none';
            if (b) b.classList.toggle('active', isHidden);
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

    /* ========================================================================= */
    /* Account Status AJAX Toggle Switch                                         */
    /* ========================================================================= */
    function toggleAccountStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'inactive' : 'active';
        const accName = btn.getAttribute('data-name') || 'Account';

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
                const statusTxt = isNowActive ? 'Active' : 'Inactive';

                // Update all matching toggle buttons (desktop table & mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowActive) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle status (currently Active)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle status (currently Inactive)';
                    }
                });

                // Update desktop table pills
                const wrap = document.getElementById('accountToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    if (pill) {
                        pill.className = 'cust-toggle-pill ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (dot) {
                        dot.className = 'toggle-pulse-dot ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (text) {
                        text.textContent = statusTxt;
                    }
                }

                // Update mobile card toggle labels
                document.querySelectorAll(`.mv-booking-card-wrapper .cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    const cardWrap = b.closest('.mv-booking-card-wrapper');
                    if (cardWrap) {
                        const lbl = cardWrap.querySelector('.mv-toggle-label-text');
                        if (lbl) {
                            lbl.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'cancelled');
                            lbl.textContent = statusTxt;
                        }
                    }
                });

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Account '${accName}' set to ${statusTxt}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to update account status.', 'error');
                } else {
                    alert(data.message || 'Failed to update account status.');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            console.error('Account toggle error:', err);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('An error occurred while updating status.', 'error');
            } else {
                alert('An error occurred while updating status.');
            }
        });
    }

    /* ========================================================================= */
    /* Add Account Modal Handlers                                                */
    /* ========================================================================= */
    function openAccountModal() {
        document.getElementById('accountModalBackdrop').style.display = 'flex';
    }
    function closeAccountModal() {
        document.getElementById('accountModalBackdrop').style.display = 'none';
    }
    function toggleBankFields(type) {
        document.getElementById('bankFields').style.display = (type === 'bank') ? 'block' : 'none';
    }

    /* ========================================================================= */
    /* Edit Account Modal Handlers                                               */
    /* ========================================================================= */
    function openEditAccountModal(account, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!account || !account.id) return;

        const form = document.getElementById('editAccountForm');
        form.action = '<?= url('/office/finance/accounts/') ?>' + account.id;

        document.getElementById('edit_acc_name').value = account.account_name || '';
        document.getElementById('edit_acc_type').value = account.account_type || 'bank';
        document.getElementById('edit_acc_bank').value = account.bank_name || '';
        document.getElementById('edit_acc_num').value = account.account_number || '';
        document.getElementById('edit_acc_ifsc').value = account.ifsc_code || '';
        document.getElementById('edit_acc_status').value = account.status || 'active';

        toggleEditBankFields(account.account_type || 'bank');
        document.getElementById('editAccountModalBackdrop').style.display = 'flex';
    }
    function closeEditAccountModal() {
        document.getElementById('editAccountModalBackdrop').style.display = 'none';
    }
    function toggleEditBankFields(type) {
        document.getElementById('editBankFields').style.display = (type === 'bank') ? 'block' : 'none';
    }

    /* ========================================================================= */
    /* Transfer Modal Handlers                                                   */
    /* ========================================================================= */
    function openTransferModal(fromId, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (fromId) {
            const selectEl = document.getElementById('from_account_id');
            if (selectEl) selectEl.value = fromId;
        }
        document.getElementById('transferModalBackdrop').style.display = 'flex';
    }
    function closeTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'none';
    }

    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

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

/* Small toggle size (for mobile cards) */
.cust-status-toggle.sm .toggle-slider-track {
    width: 36px;
    height: 20px;
}

.cust-status-toggle.sm .toggle-slider-thumb {
    width: 14px;
    height: 14px;
    left: 3px;
    top: 3px;
}

.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}

.toggle-spinner {
    font-size: 8px;
    color: #059669;
    line-height: 1;
}

.cust-status-toggle:not(.is-active) .toggle-spinner {
    color: #64748B;
}

.toggle-shake {
    animation: toggleShakeAnim 0.35s ease;
}

@keyframes toggleShakeAnim {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
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

/* ========================================================================= */
/* STRICT RESPONSIVE ARCHITECTURE: DESKTOP VS MOBILE                        */
/* ========================================================================= */

/* 1. Large Screens (>= 992px): Desktop View */
@media (min-width: 992px) {
    #financeAccountsPage .mv-bookings-header {
        display: none !important;
    }
    #financeAccountsPage .cards-grid {
        display: none !important;
    }
    #financeAccountsPage .desktop-header-wrapper {
        display: block !important;
    }
    #financeAccountsPage .booking-kpi-grid {
        display: grid !important;
    }
    #financeAccountsPage .filter-bar-compact {
        display: flex !important;
    }
    #financeAccountsPage .section-header {
        display: flex !important;
    }
    #financeAccountsPage .modern-table-container {
        display: block !important;
    }
}

/* 2. Mobile & Tablet (< 992px): Native Mobile App View */
@media (max-width: 991.98px) {
    #financeAccountsPage {
        padding-bottom: 76px;
    }
    #financeAccountsPage .desktop-header-wrapper,
    #financeAccountsPage .booking-kpi-grid,
    #financeAccountsPage .filter-bar-compact,
    #financeAccountsPage .section-header,
    #financeAccountsPage .modern-table-container {
        display: none !important;
    }
    #financeAccountsPage .mv-bookings-header {
        display: block !important;
        margin-bottom: 12px;
    }
    #financeAccountsPage .cards-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
    }
}

/* Mobile App Card Layout matching office/customers */
.mv-booking-card-wrapper {
    position: relative;
    width: 100%;
}

.mv-booking-card {
    display: flex;
    align-items: stretch;
    gap: 12px;
    padding: 12px 14px;
    padding-right: 38px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    text-decoration: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
    width: 100%;
}

.mv-booking-card:active {
    transform: scale(0.99);
    background: #fbfcfe;
}

.mv-thumb-wrap {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    align-self: flex-start;
}

.mv-thumb-avatar {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.15rem;
    border-radius: 12px;
}

.mv-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 2.5px;
}

.mv-tour-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.mv-date-duration {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mv-date-duration i {
    font-size: 10px;
    color: #94a3b8;
}

.mv-passenger {
    font-size: 11.5px;
    color: #334155;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 5px;
}

.mv-passenger i {
    font-size: 10px;
    color: #94a3b8;
}

.mv-vehicle {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mv-vehicle i {
    font-size: 10px;
    color: #94a3b8;
}

.mv-right-col {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
    flex-shrink: 0;
    padding: 1px 0;
}

.mv-status-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 6px;
    border-radius: 9999px;
    background: rgba(248, 250, 252, 0.9);
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.mv-toggle-label-text {
    font-size: 0.7rem;
    font-weight: 700;
}

.mv-toggle-label-text.completed {
    color: #059669;
}

.mv-toggle-label-text.cancelled {
    color: #64748B;
}

/* 3-Dots Quick Action in Corner */
.mv-card-actions {
    position: absolute;
    top: 10px;
    right: 8px;
    z-index: 5;
}

.mv-action-dots-btn {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #94a3b8;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13.5px;
    transition: all 0.15s;
}

.mv-action-dots-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.mv-card-actions .card-btns-popup {
    right: 0;
    top: calc(100% + 4px);
    bottom: auto;
    transform-origin: top right;
}

/* Action button table styling */
.action-btn-table.view {
    color: #176B5B;
}
.action-btn-table.view:hover {
    background-color: #E8F7F0;
    color: #16443B;
}
.action-btn-table.edit {
    color: var(--brand-primary);
}
.action-btn-table.edit:hover {
    background-color: #E8F7F0;
}
.action-btn-table.passbook {
    color: #C78A35;
}
.action-btn-table.passbook:hover {
    background-color: #FEF3C7;
    color: #92400E;
}
.action-btn-table.transfer {
    color: #218765;
}
.action-btn-table.transfer:hover {
    background-color: #E8F7F0;
    color: #16443B;
}

/* Modal styling */
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
@media (max-width: 575.98px) {
    .modal-dialog-custom {
        max-width: 95vw;
        border-radius: 14px;
    }
    .modal-body-custom {
        max-height: 70vh;
        overflow-y: auto;
    }
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
