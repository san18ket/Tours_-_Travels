<?php
/**
 * MANASWI TOURS & TRAVELS — BANK ACCOUNTS & TREASURY VAULTS
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
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
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

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
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

    <!-- 4. MOBILE ONLY: Top App Bar, Search & Status Scroll -->
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

        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/accounts') ?>" class="mv-search-form">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" placeholder="Search account, bank, A/C #..." value="<?= e($search ?? '') ?>" autocomplete="off">
                </div>
            </form>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/finance/accounts') ?>" class="mv-pill <?= empty($selectedType) ? 'active' : '' ?>">
                <span>All Vaults (<?= number_format($totalAll) ?>)</span>
            </a>
            <a href="<?= url('/office/finance/accounts?type=bank') ?>" class="mv-pill <?= $selectedType === 'bank' ? 'active' : '' ?>">
                <span>Banks (<?= $bankCount ?>)</span>
            </a>
            <a href="<?= url('/office/finance/accounts?type=cash') ?>" class="mv-pill <?= $selectedType === 'cash' ? 'active' : '' ?>">
                <span>Cash (<?= $cashCount ?>)</span>
            </a>
            <a href="<?= url('/office/finance/accounts?type=gateway') ?>" class="mv-pill <?= $selectedType === 'gateway' ? 'active' : '' ?>">
                <span>Gateways (<?= $gatewayCount ?>)</span>
            </a>
            <button type="button" class="mv-pill" onclick="openTransferModal()">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer</span>
            </button>
        </div>
    </div>

    <!-- 5. Accounts Section: Modern Table (Desktop / LG) + Mobile Cards (< 992px) -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Treasury Accounts Manifest</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Accounts:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Desktop / LG Devices) -->
        <div class="modern-table-container">
            <table class="modern-table" id="accountsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="min-width: 220px;">Account &amp; Institution</th>
                        <th style="min-width: 200px;">Account / Vault Details</th>
                        <th style="width: 140px;">Type</th>
                        <th style="text-align: right; width: 160px;">Available Balance</th>
                        <th style="text-align: right; width: 140px;">Opening Balance</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Ledger Activity</th>
                        <th style="text-align: right; width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="9">
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
                            $status = strtolower($acc['status']);
                            $isActive = $status === 'active';
                        ?>
                        <tr>
                            <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                <?= $from + $idx ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                                        <i class="fa-solid <?= $icon ?>"></i>
                                    </div>
                                    <div>
                                        <strong style="color: var(--vg-navy-900); font-size: 0.92rem; display: block;">
                                            <?= e($acc['account_name']) ?>
                                        </strong>
                                        <?php if (!empty($acc['bank_name'])): ?>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                                <i class="fa-solid fa-landmark" style="margin-right: 3px;"></i> <?= e($acc['bank_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                                <?= $typeName ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($acc['account_number'])): ?>
                                    <div style="font-family: monospace; font-size: 0.88rem; font-weight: 700; color: var(--vg-navy-900);">
                                        <?= e($acc['account_number']) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.82rem; font-style: italic;">Internal Treasury ID #<?= $acc['id'] ?></span>
                                <?php endif; ?>

                                <?php if (!empty($acc['ifsc_code'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--brand-primary); font-weight: 600; text-transform: uppercase; margin-top: 2px;">
                                        IFSC: <?= e($acc['ifsc_code']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 8px;">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                    <span><?= $typeName ?></span>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1.05rem; font-weight: 800; color: #218765; display: block;">
                                    <?= format_currency((float)$acc['current_balance']) ?>
                                </strong>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: var(--text-muted);">
                                <?= format_currency((float)$acc['opening_balance']) ?>
                            </td>
                            <td class="text-center">
                                <span class="cust-toggle-pill <?= $isActive ? 'completed' : 'cancelled' ?>" style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">
                                    <span class="toggle-pulse-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                                    <span><?= strtoupper($acc['status']) ?></span>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral" style="font-size: 0.78rem; font-weight: 600; padding: 4px 8px;">
                                    <i class="fa-solid fa-receipt" style="margin-right: 3px;"></i> <?= (int)($acc['tx_count'] ?? 0) ?> txs
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.45rem; justify-content: flex-end;">
                                    <a href="<?= url('/office/finance/transactions?account_id=' . $acc['id']) ?>" class="btn btn-outline btn-sm" title="View Ledger Passbook">
                                        <i class="fa-solid fa-list-check"></i> <span>Passbook</span>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openTransferModal(<?= $acc['id'] ?>)" title="Transfer Funds">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile App Cards (Only shown on small screens < 992px, hidden on LG devices via CSS) -->
        <div class="cards-grid">
            <?php foreach ($accounts as $acc): 
                $isBank = $acc['account_type'] === 'bank';
                $isCash = $acc['account_type'] === 'cash';
                $icon = $isCash ? 'fa-wallet' : ($isBank ? 'fa-building-columns' : 'fa-qrcode');
                $badgeBg = $isCash ? 'rgba(199, 138, 53, 0.12)' : ($isBank ? 'rgba(33, 135, 101, 0.12)' : 'rgba(23, 107, 91, 0.12)');
                $badgeColor = $isCash ? '#C78A35' : ($isBank ? '#218765' : '#176B5B');
                $isActive = strtolower($acc['status']) === 'active';
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card" style="padding: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.95rem; color: var(--vg-navy-900); display: block;"><?= e($acc['account_name']) ?></strong>
                                <span style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;"><?= e($acc['account_type']) ?></span>
                            </div>
                        </div>
                        <span class="cust-toggle-pill <?= $isActive ? 'completed' : 'cancelled' ?>" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 999px;">
                            <span class="toggle-pulse-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                            <span><?= strtoupper($acc['status']) ?></span>
                        </span>
                    </div>

                    <div style="margin-bottom: 0.85rem; padding: 0.75rem 1rem; border-radius: 10px; background: var(--page-background); border: 1px solid rgba(22, 68, 59, 0.08); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Balance:</span>
                        <strong style="font-size: 1.35rem; font-weight: 800; color: #218765;"><?= format_currency((float)$acc['current_balance']) ?></strong>
                    </div>

                    <?php if ($isBank && (!empty($acc['account_number']) || !empty($acc['bank_name']))): ?>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 0.85rem; padding: 0 0.25rem;">
                        <?php if (!empty($acc['bank_name'])): ?>
                            <div><strong>Bank:</strong> <?= e($acc['bank_name']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($acc['account_number'])): ?>
                            <div><strong>A/C No:</strong> <?= e($acc['account_number']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($acc['ifsc_code'])): ?>
                            <div><strong>IFSC:</strong> <?= e($acc['ifsc_code']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 0.5rem; border-top: 1px solid rgba(22, 68, 59, 0.08); padding-top: 0.75rem;">
                        <a href="<?= url('/office/finance/transactions?account_id=' . $acc['id']) ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center; justify-content: center;">
                            <i class="fa-solid fa-list-check"></i> <span>Passbook</span>
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openTransferModal(<?= $acc['id'] ?>)" style="flex: 1; text-align: center; justify-content: center;">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i> <span>Transfer</span>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
    function openAccountModal() {
        document.getElementById('accountModalBackdrop').style.display = 'flex';
    }
    function closeAccountModal() {
        document.getElementById('accountModalBackdrop').style.display = 'none';
    }
    function openTransferModal(fromId) {
        if (fromId) {
            const selectEl = document.getElementById('from_account_id');
            if (selectEl) selectEl.value = fromId;
        }
        document.getElementById('transferModalBackdrop').style.display = 'flex';
    }
    function closeTransferModal() {
        document.getElementById('transferModalBackdrop').style.display = 'none';
    }
    function toggleBankFields(type) {
        document.getElementById('bankFields').style.display = (type === 'bank') ? 'block' : 'none';
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
