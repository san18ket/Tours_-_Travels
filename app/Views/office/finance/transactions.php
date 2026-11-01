<?php
/**
 * MANASWI TOURS & TRAVELS — GENERAL LEDGER & TRANSACTIONS
 * Exact Dual-Architecture (Desktop PMS Modern Table + Mobile App Cards)
 * Standardized 9-Column Schema: # | Account | Type | Category | Amount | Balance After | Status | Timestamp | Actions
 */
$actionsHtml = '<button type="button" class="btn btn-primary" onclick="openTransactionModal()"><i class="fa-solid fa-plus"></i> <span>Record Transaction</span></button>';
?>

<div class="bookings-page-view finance-page-view" id="financeTransactionsPage">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'General Ledger & Transactions',
            'description' => 'Real-time double-entry transaction journal, audit references, running balances, and bank reconciliation entries.',
            'image'       => 'header-invoices.jpg',
            'badge'       => 'Double-Entry Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Finance & Treasury', 'url' => url('/office/finance')],
                ['label' => 'General Ledger', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards -->
    <div class="booking-kpi-grid" id="transactionsKpiGrid">
        <!-- Card 1: Total Volume -->
        <div class="booking-kpi-card <?= empty($selectedType) ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Ledger Entries</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll) ?></div>
            <div class="booking-kpi-sub">
                <span>All recorded audit entries</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Total Credits (Inflow) -->
        <div class="booking-kpi-card <?= $selectedType === 'credit' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions?type=credit') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Credits (Inflow)</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #218765;"><?= format_currency($totalCredits) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Revenue &amp; collections</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Total Debits (Outflow) -->
        <div class="booking-kpi-card <?= $selectedType === 'debit' ? 'is-selected' : '' ?>" role="button" tabindex="0" onclick="location.href='<?= url('/office/finance/transactions?type=debit') ?>'">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Debits (Outflow)</span>
                <span class="booking-kpi-icon" style="background: rgba(220, 38, 38, 0.12); color: #DC2626;">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: #DC2626;"><?= format_currency($totalDebits) ?></div>
            <div class="booking-kpi-sub">
                <span>Operating disbursements</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Net Balance -->
        <div class="booking-kpi-card" role="button" tabindex="0">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Net Surplus / Cashflow</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color: <?= $netBalance >= 0 ? '#176B5B' : '#DC2626' ?>;">
                <?= format_currency($netBalance) ?>
            </div>
            <div class="booking-kpi-sub">
                <span>Inflow minus outflow surplus</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar -->
    <form method="GET" action="<?= url('/office/finance/transactions') ?>" class="filter-bar-compact" id="txFilterForm">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search reference, notes, category, account..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="fbc-search-clear" onclick="location.href='<?= url('/office/finance/transactions') ?>'" title="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <select name="type" id="typeInput">
            <option value="" <?= empty($selectedType) ? 'selected' : '' ?>>All Types</option>
            <option value="credit" <?= $selectedType === 'credit' ? 'selected' : '' ?>>Credits (Inflow)</option>
            <option value="debit" <?= $selectedType === 'debit' ? 'selected' : '' ?>>Debits (Outflow)</option>
        </select>

        <select name="account_id" id="accountInput">
            <option value="">All Accounts</option>
            <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= ($selectedAccount ?? 0) == $acc['id'] ? 'selected' : '' ?>>
                    <?= e($acc['account_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Status</option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/finance/transactions') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll (Exact office/customers Architecture) -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">General Ledger</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openTransactionModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Record Tx</span>
            </button>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/finance/transactions') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search reference, notes, category..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/finance/transactions') ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel (Exact office/customers drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/finance/transactions') ?>">
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?= e($search) ?>">
                    <?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvTypeSelect">Type:</label>
                        <select name="type" id="mvTypeSelect">
                            <option value="">All Types</option>
                            <option value="credit" <?= ($selectedType ?? '') === 'credit' ? 'selected' : '' ?>>Credit (+ Inflow)</option>
                            <option value="debit" <?= ($selectedType ?? '') === 'debit' ? 'selected' : '' ?>>Debit (- Outflow)</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvAccountSelect">Account:</label>
                        <select name="account_id" id="mvAccountSelect">
                            <option value="">All Accounts</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" <?= ($selectedAccount ?? 0) == $acc['id'] ? 'selected' : '' ?>>
                                    <?= e($acc['account_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvStatusSelect">Status:</label>
                        <select name="status" id="mvStatusSelect">
                            <option value="">All Statuses</option>
                            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect">
                            <?php foreach ([10, 15, 25, 50, 100] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($perPage ?? 15) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center;">
                            <i class="fa-solid fa-filter"></i> Apply
                        </button>
                        <a href="<?= url('/office/finance/transactions') ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center; text-decoration: none;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeFilterUrl = function(?string $t, ?string $st) use ($search, $selectedAccount, $perPage) {
                $p = [];
                if (!empty($search)) $p['search'] = $search;
                if (!empty($selectedAccount)) $p['account_id'] = $selectedAccount;
                if (!empty($t)) $p['type'] = $t;
                if (!empty($st)) $p['status'] = $st;
                if (!empty($perPage) && $perPage != 15) $p['per_page'] = $perPage;
                return url('/office/finance/transactions' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeFilterUrl(null, null) ?>" class="mv-pill <?= empty($selectedType) && empty($selectedStatus) ? 'active' : '' ?>">
                <span>All Entries</span>
                <span class="mv-pill-cnt"><?= number_format($totalAll) ?></span>
            </a>
            <a href="<?= $makeFilterUrl('credit', null) ?>" class="mv-pill <?= ($selectedType ?? '') === 'credit' ? 'active' : '' ?>">
                <span>Credits (+)</span>
            </a>
            <a href="<?= $makeFilterUrl('debit', null) ?>" class="mv-pill <?= ($selectedType ?? '') === 'debit' ? 'active' : '' ?>">
                <span>Debits (-)</span>
            </a>
            <a href="<?= $makeFilterUrl(null, 'completed') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'completed' ? 'active' : '' ?>">
                <span>Completed</span>
            </a>
            <a href="<?= $makeFilterUrl(null, 'pending') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'pending' ? 'active' : '' ?>">
                <span>Pending</span>
            </a>
            <a href="<?= $makeFilterUrl(null, 'cancelled') ?>" class="mv-pill <?= ($selectedStatus ?? '') === 'cancelled' ? 'active' : '' ?>">
                <span>Cancelled</span>
            </a>
        </div>
    </div>

    <!-- 5. Transaction Ledger Section: Desktop Table + Mobile Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">Journal Manifest</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens - EXACT 9-COLUMN SCHEMA -->
        <div class="modern-table-container">
            <table class="modern-table" id="transactionsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 200px;">Account</th>
                        <th class="text-center" style="width: 110px;">Type</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="text-align: right; width: 140px;">Amount</th>
                        <th style="text-align: right; width: 140px;">Balance After</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th style="width: 150px;">Timestamp</th>
                        <th class="text-center" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="no-data-message" style="text-align: center; padding: 3rem 1.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No transactions found</h4>
                                <p class="text-muted" style="margin: 0;">Try adjusting your search filters or record a new transaction.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $idx => $tx):
                            $isCredit = $tx['transaction_type'] === 'credit';
                            $rowNum = ($currentPage - 1) * $perPage + $idx + 1;
                            $st = strtolower($tx['status'] ?? 'completed');
                            if ($st === 'pending') {
                                $statusClass = 'pending';
                                $statusText = 'PENDING';
                            } elseif ($st === 'cancelled') {
                                $statusClass = 'cancelled';
                                $statusText = 'CANCELLED';
                            } else {
                                $statusClass = 'completed';
                                $statusText = 'COMPLETED';
                            }
                            $isCompleted = ($statusClass === 'completed');
                        ?>
                        <tr id="txRow-<?= (int)$tx['id'] ?>">
                            <!-- 1. # Index -->
                            <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                <?= $rowNum ?>
                            </td>

                            <!-- 2. Account: Only show Bank Name -->
                            <td>
                                <?php $bankDisplayName = !empty($tx['bank_name']) ? $tx['bank_name'] : (!empty($tx['account_name']) ? $tx['account_name'] : 'Internal Vault'); ?>
                                <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; background: rgba(23, 107, 91, 0.08); color: #176B5B; font-size: 0.8rem; flex-shrink: 0;">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </span>
                                    <a href="<?= url('/office/finance/transactions/' . $tx['id']) ?>" style="color: inherit; text-decoration: none;" class="tx-account-link" title="View Transaction Details for <?= e($bankDisplayName) ?>">
                                        <?= e($bankDisplayName) ?>
                                    </a>
                                </div>
                            </td>

                            <!-- 3. Type -->
                            <td class="text-center">
                                <span class="tx-type-pill <?= $isCredit ? 'credit' : 'debit' ?>">
                                    <i class="fa-solid <?= $isCredit ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                                    <span><?= $isCredit ? 'CREDIT' : 'DEBIT' ?></span>
                                </span>
                            </td>

                            <!-- 4. Category -->
                            <td>
                                <div style="font-size: 0.86rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($tx['category']) ?></div>
                                <?php if (!empty($tx['payment_method'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 1px;">
                                        <?= e(str_replace('_', ' ', $tx['payment_method'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Amount -->
                            <td style="text-align: right; white-space: nowrap;">
                                <strong style="font-size: 0.95rem; font-weight: 800; color: <?= $isCredit ? '#218765' : '#DC2626' ?>;">
                                    <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                                </strong>
                            </td>

                            <!-- 6. Balance After -->
                            <td style="text-align: right; font-size: 0.9rem; font-weight: 700; color: var(--vg-navy-900); white-space: nowrap;">
                                <?= format_currency((float)$tx['balance_after']) ?>
                            </td>

                            <!-- 7. Status (3-State Toggle & Interactive Dropdown: Completed, Pending, Cancelled) -->
                            <td class="text-center">
                                <div class="cust-toggle-cell" id="txToggleWrap-<?= (int)$tx['id'] ?>">
                                    <button type="button" 
                                            class="cust-status-toggle <?= $statusClass ?><?= $isCompleted ? ' is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isCompleted ? 'true' : 'false' ?>" 
                                            data-id="<?= (int)$tx['id'] ?>"
                                            data-status="<?= $statusClass ?>"
                                            data-ref="<?= addslashes(e($tx['transaction_ref'] ?: ('TX-#' . $tx['id']))) ?>"
                                            onclick="toggleTransactionStatus(<?= (int)$tx['id'] ?>, this, event)"
                                            title="Click to cycle status: Completed &rarr; Pending &rarr; Cancelled (Current: <?= $statusText ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <div class="status-pill-dropdown-wrap">
                                        <button type="button" 
                                                class="cust-toggle-pill <?= $statusClass ?>" 
                                                onclick="openTxStatusDropdown(<?= (int)$tx['id'] ?>, this, event)"
                                                title="Click to select status (Completed / Pending / Cancelled)">
                                            <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                            <span class="toggle-label-text"><?= $statusText ?></span>
                                            <i class="fa-solid fa-chevron-down pill-chevron"></i>
                                        </button>
                                        <div class="tx-status-dropdown-menu" id="txStatusDropdown-<?= (int)$tx['id'] ?>">
                                            <button type="button" class="tx-status-item <?= $statusClass === 'completed' ? 'active' : '' ?>" data-status="completed" onclick="setTransactionDirectStatus(<?= (int)$tx['id'] ?>, 'completed', event)">
                                                <span class="status-menu-dot completed"></span>
                                                <span>Completed</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $statusClass === 'pending' ? 'active' : '' ?>" data-status="pending" onclick="setTransactionDirectStatus(<?= (int)$tx['id'] ?>, 'pending', event)">
                                                <span class="status-menu-dot pending"></span>
                                                <span>Pending</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $statusClass === 'cancelled' ? 'active' : '' ?>" data-status="cancelled" onclick="setTransactionDirectStatus(<?= (int)$tx['id'] ?>, 'cancelled', event)">
                                                <span class="status-menu-dot cancelled"></span>
                                                <span>Cancelled</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 8. Timestamp -->
                            <td style="font-size: 0.82rem; color: var(--text-muted); white-space: nowrap;">
                                <div style="font-weight: 600; color: #1E293B;"><?= date('d M Y', strtotime($tx['transacted_at'])) ?></div>
                                <div style="font-size: 0.74rem; color: var(--text-muted);"><?= date('h:i A', strtotime($tx['transacted_at'])) ?></div>
                            </td>

                            <!-- 9. Actions (Floating Popup with View, Edit, Delete like other pages) -->
                            <td class="text-center">
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <a href="<?= url('/office/finance/transactions/' . $tx['id']) ?>" class="action-btn-table view" title="View Transaction Voucher Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button type="button" class="action-btn-table edit" title="Edit Transaction Voucher" onclick="openEditModal(<?= htmlspecialchars(json_encode($tx), ENT_QUOTES, 'UTF-8') ?>, event)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="action-btn-table delete" title="Delete Transaction" onclick="openDeleteModal(<?= (int)$tx['id'] ?>, '<?= addslashes(e($tx['transaction_ref'] ?: ('Voucher #' . $tx['id']))) ?>', <?= (float)$tx['amount'] ?>, '<?= $tx['transaction_type'] ?>', event)">
                                            <i class="fa-solid fa-trash-can"></i>
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

        <!-- Mobile App Cards (Exact Match to office/customers Architecture) -->
        <div class="cards-grid">
            <?php foreach ($transactions as $idx => $tx):
                $isCredit = $tx['transaction_type'] === 'credit';
                $st = strtolower($tx['status'] ?? 'completed');
                if ($st === 'pending') {
                    $statusClass = 'pending';
                    $statusText = 'Pending';
                } elseif ($st === 'cancelled') {
                    $statusClass = 'cancelled';
                    $statusText = 'Cancelled';
                } else {
                    $statusClass = 'completed';
                    $statusText = 'Completed';
                }
                $isCompleted = ($statusClass === 'completed');
            ?>
            <div class="mv-booking-card-wrapper" id="mvTxCard-<?= (int)$tx['id'] ?>">
                <a href="<?= url('/office/finance/transactions/' . $tx['id']) ?>" class="mv-booking-card">
                    <!-- 1. Left Thumbnail Avatar (Matching office/customers) -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-tx-avatar <?= $isCredit ? 'credit' : 'debit' ?>">
                            <i class="fa-solid <?= $isCredit ? 'fa-arrow-down-left' : 'fa-arrow-up-right-from-square' ?>"></i>
                        </div>
                    </div>

                    <!-- 2. Middle Details (Code, Category, Account, Date, Method) -->
                    <div class="mv-details">
                        <div class="mv-code-row">
                            <span class="mv-code"><?= !empty($tx['transaction_ref']) ? e($tx['transaction_ref']) : ('TX-#' . $tx['id']) ?></span>
                            <span class="mv-tx-badge <?= $isCredit ? 'credit' : 'debit' ?>"><?= $isCredit ? 'CREDIT' : 'DEBIT' ?></span>
                        </div>
                        <div class="mv-tour-title" title="<?= e($tx['category']) ?>"><?= e($tx['category']) ?></div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-building-columns"></i>
                            <span><?= e(!empty($tx['bank_name']) ? $tx['bank_name'] : $tx['account_name']) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-regular fa-clock"></i>
                            <span><?= date('d M Y, h:i A', strtotime($tx['transacted_at'])) ?></span>
                        </div>
                        <?php if (!empty($tx['payment_method'])): ?>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-credit-card"></i>
                            <span><?= strtoupper(str_replace('_', ' ', $tx['payment_method'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 3. Right Column: Status Toggle on Top, Amount & Balance on Bottom -->
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.preventDefault(); event.stopPropagation();">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $statusClass ?><?= $isCompleted ? ' is-active' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $isCompleted ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$tx['id'] ?>"
                                    data-status="<?= $statusClass ?>"
                                    data-ref="<?= addslashes(e($tx['transaction_ref'] ?: ('TX-#' . $tx['id']))) ?>"
                                    onclick="toggleTransactionStatus(<?= (int)$tx['id'] ?>, this, event)"
                                    title="Click to cycle status: Completed &rarr; Pending &rarr; Cancelled (Current: <?= $statusText ?>)">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb">
                                        <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                    </span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>" onclick="toggleTransactionStatus(<?= (int)$tx['id'] ?>, this.previousElementSibling, event)"><?= $statusText ?></span>
                        </div>

                        <div class="mv-tx-amounts">
                            <div class="mv-price <?= $isCredit ? 'credit' : 'debit' ?>">
                                <?= $isCredit ? '+' : '-' ?><?= format_currency((float)$tx['amount']) ?>
                            </div>
                            <div class="mv-bal-sub">
                                Bal: <?= format_currency((float)$tx['balance_after']) ?>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action in Corner -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/finance/transactions/' . $tx['id']) ?>" class="action-btn-table view" title="View Transaction Voucher Details">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <button type="button" class="action-btn-table edit" title="Edit Voucher" onclick="openEditModal(<?= htmlspecialchars(json_encode($tx), ENT_QUOTES, 'UTF-8') ?>, event)">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button type="button" class="action-btn-table delete" title="Delete Voucher" onclick="openDeleteModal(<?= (int)$tx['id'] ?>, '<?= addslashes(e($tx['transaction_ref'] ?: ('Voucher #' . $tx['id']))) ?>', <?= (float)$tx['amount'] ?>, '<?= $tx['transaction_type'] ?>', event)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?>
            <div class="no-data-message" style="text-align: center; padding: 2.5rem 1rem;">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No transactions found</h4>
                <p class="text-muted" style="margin: 0;">Try adjusting your search filters or record a new transaction.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Modern Pagination Matching office/customers Architecture -->
        <?php
            $totRec = $totalRecords ?? count($transactions);
            $currP = $currentPage ?? 1;
            $pSize = $perPage ?? 15;
            $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
            $to   = min($currP * $pSize, $totRec);
            $buildPageUrl = function(int $p) use ($search, $selectedType, $selectedAccount, $selectedStatus, $pSize) {
                $q = [];
                if (!empty($search)) $q['search'] = $search;
                if (!empty($selectedType)) $q['type'] = $selectedType;
                if (!empty($selectedAccount)) $q['account_id'] = $selectedAccount;
                if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
                if (!empty($pSize) && $pSize != 15) $q['per_page'] = $pSize;
                $q['page'] = $p;
                return url('/office/finance/transactions?' . http_build_query($q));
            };
        ?>
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper" style="margin-top: 1.5rem;">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= number_format($totRec) ?></span> transaction(s)</div>
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
    </section>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: Record Transaction                                               -->
<!-- ========================================================================= -->
<div id="txModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'txModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge green"><i class="fa-solid fa-receipt"></i></span>
                Record General Ledger Transaction
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeTransactionModal()">&times;</button>
        </div>
        <form action="<?= url('/office/finance/transactions') ?>" method="POST" id="recordTxForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/transactions') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Transaction Type <span class="req">*</span></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #218765; cursor: pointer; background: rgba(33, 135, 101, 0.06);">
                            <input type="radio" name="transaction_type" value="credit" checked>
                            <span style="font-weight: 700; color: #218765;"><i class="fa-solid fa-arrow-down"></i> Credit (Income)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 10px; border: 1.5px solid #DC2626; cursor: pointer; background: rgba(220, 38, 38, 0.06);">
                            <input type="radio" name="transaction_type" value="debit">
                            <span style="font-weight: 700; color: #DC2626;"><i class="fa-solid fa-arrow-up"></i> Debit (Expense)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="tx_account_id">Target Account / Vault <span class="req">*</span></label>
                    <select name="account_id" id="tx_account_id" class="form-select" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                <?= e($acc['account_name']) ?> (<?= strtoupper($acc['account_type'] ?? 'Account') ?>) — Available: <?= format_currency((float)($acc['current_balance'] ?? $acc['balance'] ?? 0)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="tx_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="tx_amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tx_category">Category <span class="req">*</span></label>
                        <input type="text" name="category" id="tx_category" class="form-control" list="categoryOptions" placeholder="Category name..." required>
                        <datalist id="categoryOptions">
                            <option value="Customer Payment">
                            <option value="Tour Revenue">
                            <option value="Fuel Expense">
                            <option value="Vehicle Maintenance">
                            <option value="Driver Daily Bata">
                            <option value="Office Overheads">
                            <option value="Owner Capital Infusion">
                            <option value="Bank Interest">
                        </datalist>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="tx_method">Payment Mode</label>
                        <select name="payment_method" id="tx_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="cash">Counter Cash</option>
                            <option value="card">Card Payment</option>
                            <option value="cheque">Bank Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tx_ref">Transaction Ref / Cheque #</label>
                        <input type="text" name="transaction_ref" id="tx_ref" class="form-control" placeholder="UTR / Ref Number">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="tx_date">Transaction Date &amp; Time</label>
                    <input type="datetime-local" name="transacted_at" id="tx_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="tx_notes">Internal Remarks / Description</label>
                    <textarea name="notes" id="tx_notes" class="form-control" rows="2" placeholder="Audit note regarding this financial entry..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeTransactionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Commit Entry</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: View Transaction Voucher (Comprehensive Details)                 -->
<!-- ========================================================================= -->
<div id="viewTxModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'viewTxModalBackdrop')">
    <div class="modal-dialog-custom modal-dialog-lg">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge green"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span id="viewTxHeaderTitle">Transaction Voucher Details</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body-custom" id="viewTxModalBody">
            <!-- Voucher Hero Amount Box -->
            <div class="tx-voucher-hero-box" id="viewTxHeroBox">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="tx-hero-type-label" id="viewTxTypeLabel">CREDIT TRANSACTION</span>
                    <span id="viewTxStatusPill" class="cust-toggle-pill completed">
                        <span class="toggle-pulse-dot completed"></span>
                        <span id="viewTxStatusText">COMPLETED</span>
                    </span>
                </div>
                <div class="tx-hero-amount" id="viewTxAmount">₹0.00</div>
                <div class="tx-hero-sub" id="viewTxHeroSub">Account: State Bank Vault</div>
            </div>

            <!-- Key-Value Detail Grid -->
            <div class="tx-detail-grid">
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Voucher Reference / Ref #</span>
                    <span class="tx-detail-value bold" id="viewTxRef">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Category</span>
                    <span class="tx-detail-value bold" id="viewTxCategory">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Payment Mode</span>
                    <span class="tx-detail-value" id="viewTxMethod">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Running Balance After</span>
                    <span class="tx-detail-value bold" id="viewTxBalAfter">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Target Account</span>
                    <span class="tx-detail-value bold" id="viewTxAccount">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Bank Institution / IFSC</span>
                    <span class="tx-detail-value" id="viewTxBank">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">Transacted At</span>
                    <span class="tx-detail-value" id="viewTxDate">—</span>
                </div>
                <div class="tx-detail-item">
                    <span class="tx-detail-label">System Log Timestamp</span>
                    <span class="tx-detail-value" id="viewTxCreatedAt">—</span>
                </div>
            </div>

            <!-- Remarks / Notes Box -->
            <div class="tx-notes-box" id="viewTxNotesBox">
                <div class="tx-notes-label"><i class="fa-regular fa-note-sticky"></i> Remarks &amp; Audit Notes</div>
                <div class="tx-notes-text" id="viewTxNotesText">No remarks attached to this ledger transaction.</div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> <span>Print</span></button>
            <button type="button" class="btn btn-outline" id="viewTxEditBtn"><i class="fa-solid fa-pen-to-square"></i> <span>Edit</span></button>
            <button type="button" class="btn btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: Edit Transaction Voucher                                         -->
<!-- ========================================================================= -->
<div id="editTxModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editTxModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge amber"><i class="fa-solid fa-pen-to-square"></i></span>
                <span>Edit Transaction Voucher</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="" method="POST" id="editTxForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/transactions') ?>">
            <div class="modal-body-custom">
                <div class="edit-locked-alert">
                    <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i>
                    <span>Account, Amount &amp; Type are locked to preserve double-entry audit balance. You can update Category, Method, Ref, Date, Notes &amp; Status below.</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_tx_category">Category <span class="req">*</span></label>
                        <input type="text" name="category" id="edit_tx_category" class="form-control" list="categoryOptions" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_tx_method">Payment Mode</label>
                        <select name="payment_method" id="edit_tx_method" class="form-select">
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                            <option value="cash">Counter Cash</option>
                            <option value="card">Card Payment</option>
                            <option value="cheque">Bank Cheque</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_tx_ref">Transaction Ref / Cheque #</label>
                        <input type="text" name="transaction_ref" id="edit_tx_ref" class="form-control" placeholder="UTR / Ref Number">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_tx_status">Status</label>
                        <select name="status" id="edit_tx_status" class="form-select">
                            <option value="completed">Completed (Funds Cleared)</option>
                            <option value="pending">Pending (Under Clearance)</option>
                            <option value="cancelled">Cancelled (Void)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_tx_date">Transaction Date &amp; Time</label>
                    <input type="datetime-local" name="transacted_at" id="edit_tx_date" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_tx_notes">Remarks / Audit Description</label>
                    <textarea name="notes" id="edit_tx_notes" class="form-control" rows="3" placeholder="Notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Save Changes</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: Delete Transaction Voucher Confirmation                          -->
<!-- ========================================================================= -->
<div id="deleteTxModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'deleteTxModalBackdrop')">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #FEF2F2; border-bottom: 1px solid #FEE2E2;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #991B1B; display: flex; align-items: center; gap: 0.5rem;">
                <span class="modal-icon-badge red"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <span>Delete Transaction Voucher</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body-custom" style="padding: 1.5rem;">
            <p style="font-size: 0.95rem; color: #334155; margin-bottom: 1rem; line-height: 1.5;">
                Are you sure you want to delete <strong id="deleteTxVoucherName" style="color: #0F172A;">this transaction</strong>?
            </p>

            <div style="background: #FFF5F5; border: 1.5px solid #FED7D7; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #9B2C2C; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> Automatic Ledger Reversal
                </div>
                <div style="font-size: 0.82rem; color: #742A2A; line-height: 1.45;">
                    Deleting this entry will reverse its credit/debit effect from the associated account balance to preserve accounting accuracy.
                </div>
            </div>

            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.85rem; color: #475569; user-select: none;">
                <input type="checkbox" id="deleteConfirmCheckbox" onchange="toggleDeleteConfirmBtn()" style="margin-top: 3px; accent-color: #DC2626; width: 16px; height: 16px;">
                <span>I confirm that I want to delete this transaction voucher permanently.</span>
            </label>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="deleteTxSubmitBtn" disabled onclick="confirmDeleteFinal()">
                <i class="fa-solid fa-trash-can"></i> <span id="deleteTxSubmitBtnText">Delete Voucher</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CLIENT SCRIPTS & INTERACTIONS                                             -->
<!-- ========================================================================= -->
<script>
    // 1. Action Popups (Desktop Table & Mobile Cards)
    function toggleActions(btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) {
            popup.classList.add('open');
        }
    }

    function toggleCardActions(btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) {
            popup.classList.add('open');
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

    // 2. Status Management & 3-State AJAX (Completed, Pending, Cancelled)
    function applyTxStatusUI(id, status) {
        const st = (status || 'completed').toLowerCase();
        const isCompleted = (st === 'completed' || st === 'active');
        const isPending = (st === 'pending');
        const isCancelled = (st === 'cancelled');

        const statusClass = isPending ? 'pending' : (isCancelled ? 'cancelled' : 'completed');
        const statusText = isPending ? 'PENDING' : (isCancelled ? 'CANCELLED' : 'COMPLETED');

        // A. Update Desktop Table Row
        const row = document.getElementById('txRow-' + id);
        if (row) {
            const toggleBtn = row.querySelector('.cust-status-toggle');
            if (toggleBtn) {
                toggleBtn.className = 'cust-status-toggle ' + statusClass + (isCompleted ? ' is-active' : '');
                toggleBtn.setAttribute('aria-checked', isCompleted ? 'true' : 'false');
                toggleBtn.setAttribute('data-status', statusClass);
                toggleBtn.title = 'Current status: ' + statusText + '. Click to cycle status.';
            }
            const pill = row.querySelector('.cust-toggle-pill');
            if (pill) {
                pill.className = 'cust-toggle-pill ' + statusClass;
            }
            const dot = row.querySelector('.toggle-pulse-dot');
            if (dot) {
                dot.className = 'toggle-pulse-dot ' + statusClass;
            }
            const text = row.querySelector('.toggle-label-text');
            if (text) {
                text.textContent = statusText;
            }
            // Update active item in dropdown
            row.querySelectorAll('.tx-status-item').forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                item.classList.toggle('active', itemStatus === statusClass);
            });
        }

        // B. Update Mobile Card
        const card = document.getElementById('mvTxCard-' + id);
        if (card) {
            const mToggleBtn = card.querySelector('.cust-status-toggle');
            if (mToggleBtn) {
                mToggleBtn.className = 'cust-status-toggle sm ' + statusClass + (isCompleted ? ' is-active' : '');
                mToggleBtn.setAttribute('aria-checked', isCompleted ? 'true' : 'false');
                mToggleBtn.setAttribute('data-status', statusClass);
            }
            const mText = card.querySelector('.mv-toggle-label-text');
            if (mText) {
                mText.className = 'mv-toggle-label-text ' + statusClass;
                mText.textContent = statusText.charAt(0).toUpperCase() + statusText.slice(1).toLowerCase();
            }
        }
    }

    function toggleTransactionStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/finance/transactions/') ?>' + id + '/toggle-status';
        const csrfToken = '<?= csrf_token() ?>';

        // Leaving status empty triggers automatic sequence cycle:
        // completed -> pending -> cancelled -> completed
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
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data && data.success) {
                applyTxStatusUI(id, data.status);
            } else {
                alert(data.message || 'Could not update transaction status.');
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            console.error('Toggle status error:', err);
            alert('A network error occurred while toggling status.');
        });
    }

    function setTransactionDirectStatus(id, targetStatus, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        closeAllTxStatusDropdowns();
        if (!id) return;

        const url = '<?= url('/office/finance/transactions/') ?>' + id + '/toggle-status';
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
                applyTxStatusUI(id, data.status);
            } else {
                alert(data.message || 'Could not update transaction status.');
            }
        })
        .catch(err => {
            console.error('Direct status error:', err);
            alert('A network error occurred while updating status.');
        });
    }

    function openTxStatusDropdown(id, pillBtn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('txStatusDropdown-' + id);
        if (!menu) return;
        const wasOpen = menu.classList.contains('open');
        closeAllTxStatusDropdowns();
        if (!wasOpen) {
            menu.classList.add('open');
        }
    }

    function closeAllTxStatusDropdowns() {
        document.querySelectorAll('.tx-status-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.status-pill-dropdown-wrap')) {
            closeAllTxStatusDropdowns();
        }
    });

    // 2.1 Mobile Filter Panel Drawer
    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        const b = document.getElementById('mvFilterToggleBtn');
        if (p) {
            const isHidden = p.style.display === 'none' || p.style.display === '';
            p.style.display = isHidden ? 'block' : 'none';
            if (b) b.classList.toggle('active', isHidden);
        }
    }

    document.addEventListener('click', function(e) {
        const p = document.getElementById('mvFilterPanel');
        const b = document.getElementById('mvFilterToggleBtn');
        if (p && !p.contains(e.target) && b && !b.contains(e.target)) {
            p.style.display = 'none';
            if (b) b.classList.remove('active');
        }
    });

    // 3. Modal Handlers: Record Modal
    function openTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'flex';
    }
    function closeTransactionModal() {
        document.getElementById('txModalBackdrop').style.display = 'none';
    }

    // 4. Modal Handlers: View Voucher Modal
    let activeViewingTx = null;
    function openViewModal(tx, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        activeViewingTx = tx;
        const isCredit = (tx.transaction_type === 'credit');
        const st = (tx.status || 'completed').toLowerCase();
        const isCompleted = (st === 'completed' || st === 'active');

        document.getElementById('viewTxHeaderTitle').textContent = 'Voucher ' + (tx.transaction_ref ? ('(' + tx.transaction_ref + ')') : ('#TX-' + tx.id));
        document.getElementById('viewTxTypeLabel').textContent = isCredit ? 'CREDIT TRANSACTION (INFLOW)' : 'DEBIT TRANSACTION (OUTFLOW)';
        
        const heroBox = document.getElementById('viewTxHeroBox');
        if (heroBox) {
            heroBox.style.background = isCredit 
                ? 'linear-gradient(135deg, #176B5B 0%, #16443B 100%)' 
                : 'linear-gradient(135deg, #991B1B 0%, #7F1D1D 100%)';
        }

        const amtEl = document.getElementById('viewTxAmount');
        if (amtEl) {
            amtEl.textContent = (isCredit ? '+ ' : '- ') + '₹' + parseFloat(tx.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        const subEl = document.getElementById('viewTxHeroSub');
        if (subEl) {
            subEl.textContent = 'Account: ' + (tx.account_name || 'Treasury Account');
        }

        const statusPill = document.getElementById('viewTxStatusPill');
        const statusText = document.getElementById('viewTxStatusText');
        if (statusPill && statusText) {
            statusPill.className = 'cust-toggle-pill ' + (isCompleted ? 'completed' : 'cancelled');
            statusText.textContent = isCompleted ? 'COMPLETED' : 'CANCELLED';
        }

        document.getElementById('viewTxRef').textContent = tx.transaction_ref || 'N/A';
        document.getElementById('viewTxCategory').textContent = tx.category || 'General';
        document.getElementById('viewTxMethod').textContent = (tx.payment_method || 'bank_transfer').replace(/_/g, ' ').toUpperCase();
        document.getElementById('viewTxBalAfter').textContent = tx.balance_after ? ('₹' + parseFloat(tx.balance_after).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})) : '—';
        document.getElementById('viewTxAccount').textContent = tx.account_name || '—';
        document.getElementById('viewTxBank').textContent = (tx.bank_name || 'Internal Vault') + (tx.ifsc_code ? (' (' + tx.ifsc_code + ')') : '');
        document.getElementById('viewTxDate').textContent = tx.transacted_at || '—';
        document.getElementById('viewTxCreatedAt').textContent = tx.created_at || '—';
        document.getElementById('viewTxNotesText').textContent = tx.notes || 'No internal remarks attached to this ledger transaction.';

        const editBtn = document.getElementById('viewTxEditBtn');
        if (editBtn) {
            editBtn.onclick = function() {
                closeViewModal();
                openEditModal(activeViewingTx);
            };
        }

        document.getElementById('viewTxModalBackdrop').style.display = 'flex';
    }

    function closeViewModal() {
        document.getElementById('viewTxModalBackdrop').style.display = 'none';
        activeViewingTx = null;
    }

    // 5. Modal Handlers: Edit Voucher Modal
    function openEditModal(tx, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        const form = document.getElementById('editTxForm');
        form.action = '<?= url('/office/finance/transactions/') ?>' + tx.id;

        document.getElementById('edit_tx_category').value = tx.category || '';
        document.getElementById('edit_tx_method').value = tx.payment_method || 'bank_transfer';
        document.getElementById('edit_tx_ref').value = tx.transaction_ref || '';
        document.getElementById('edit_tx_status').value = (tx.status || 'completed').toLowerCase();
        
        if (tx.transacted_at) {
            const dt = new Date(tx.transacted_at);
            if (!isNaN(dt.getTime())) {
                const pad = num => String(num).padStart(2, '0');
                const val = dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()) + 'T' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
                document.getElementById('edit_tx_date').value = val;
            }
        }
        document.getElementById('edit_tx_notes').value = tx.notes || '';

        document.getElementById('editTxModalBackdrop').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editTxModalBackdrop').style.display = 'none';
    }

    // 6. Modal Handlers: Delete Voucher Modal
    let currentDeleteTxId = null;
    function openDeleteModal(id, ref, amount, type, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        currentDeleteTxId = id;
        document.getElementById('deleteTxVoucherName').textContent = ref + ' (' + (type === 'credit' ? '+' : '-') + '₹' + parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
        
        const chk = document.getElementById('deleteConfirmCheckbox');
        const btn = document.getElementById('deleteTxSubmitBtn');
        if (chk) chk.checked = false;
        if (btn) btn.disabled = true;

        document.getElementById('deleteTxModalBackdrop').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteTxModalBackdrop').style.display = 'none';
        currentDeleteTxId = null;
    }

    function toggleDeleteConfirmBtn() {
        const chk = document.getElementById('deleteConfirmCheckbox');
        const btn = document.getElementById('deleteTxSubmitBtn');
        if (chk && btn) {
            btn.disabled = !chk.checked;
        }
    }

    function confirmDeleteFinal() {
        if (!currentDeleteTxId) return;

        const btn = document.getElementById('deleteTxSubmitBtn');
        const btnText = document.getElementById('deleteTxSubmitBtnText');
        if (btn) btn.disabled = true;
        if (btnText) btnText.textContent = 'Deleting...';

        const url = '<?= url('/office/finance/transactions/') ?>' + currentDeleteTxId + '/delete';
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
                // Smoothly remove row from table and card from mobile
                const row = document.getElementById('txRow-' + currentDeleteTxId);
                const card = document.getElementById('mvTxCard-' + currentDeleteTxId);
                if (row) row.remove();
                if (card) card.remove();

                closeDeleteModal();
                // Optionally reload to update KPI counts
                window.location.reload();
            } else {
                alert(data.message || 'Failed to delete transaction.');
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = 'Delete Voucher';
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            alert('A network error occurred while deleting the transaction.');
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'Delete Voucher';
        });
    }

    // Modal backdrop click dismissal
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }
</script>

<style>
/* 1. Transaction Type Badges */
.tx-type-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
}
.tx-type-pill.credit {
    background: #E8F7F0;
    color: #218765;
}
.tx-type-pill.debit {
    background: #FEE2E2;
    color: #DC2626;
}

/* 2. Modal Custom Styles */
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
    max-width: 540px;
    overflow: hidden;
    animation: modalSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-dialog-custom.modal-dialog-lg {
    max-width: 620px;
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
    max-height: 75vh;
    overflow-y: auto;
}
.modal-footer-custom {
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    border-top: 1px solid rgba(22, 68, 59, 0.08);
    background: var(--page-background, #F8FAFC);
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
.modal-icon-badge.green {
    background: #E8F7F0;
    color: #176B5B;
}
.modal-icon-badge.amber {
    background: #FEF3C7;
    color: #B45309;
}
.modal-icon-badge.red {
    background: #FEE2E2;
    color: #DC2626;
}

/* 3. Voucher Hero Box in View Modal */
.tx-voucher-hero-box {
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.22);
}
.tx-hero-type-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.9;
}
.tx-hero-amount {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.15;
    margin: 0.35rem 0;
    letter-spacing: -0.02em;
}
.tx-hero-sub {
    font-size: 0.82rem;
    opacity: 0.88;
}

/* 4. Voucher Details Grid */
.tx-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}
.tx-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.tx-detail-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.tx-detail-value {
    font-size: 0.88rem;
    color: #1E293B;
    word-break: break-word;
}
.tx-detail-value.bold {
    font-weight: 700;
    color: #0F172A;
}

/* 5. Remarks / Notes Box */
.tx-notes-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1rem;
}
.tx-notes-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.tx-notes-text {
    font-size: 0.85rem;
    color: #334155;
    line-height: 1.45;
}

/* 6. Edit Locked Alert */
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

/* 7. Action Button Hover Tweaks */
.action-btn-table.view {
    color: #176B5B;
}
.action-btn-table.view:hover {
    background-color: #E8F7F0;
    color: #16443B;
}
.action-btn-table.edit {
    color: #C78A35;
}
.action-btn-table.edit:hover {
    background-color: #FEF3C7;
    color: #92400E;
}
.action-btn-table.delete {
    color: #DC2626;
}
.action-btn-table.delete:hover {
    background-color: #FEE2E2;
    color: #991B1B;
}

/* 8. Mobile Card Action 3-dots Position */
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

/* 9. 3-State Toggle Switch (Completed, Pending, Cancelled) */
.cust-toggle-cell {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    user-select: none;
}
.cust-status-toggle {
    position: relative;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-block;
    vertical-align: middle;
}
.cust-status-toggle .toggle-slider-track {
    display: block;
    width: 44px;
    height: 24px;
    border-radius: 9999px;
    background: #CBD5E1;
    position: relative;
    transition: background-color 0.25s ease, box-shadow 0.25s ease;
}
.cust-status-toggle.completed .toggle-slider-track,
.cust-status-toggle.is-active .toggle-slider-track {
    background: #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.35);
}
.cust-status-toggle.pending .toggle-slider-track {
    background: #F59E0B;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.35);
}
.cust-status-toggle.cancelled .toggle-slider-track {
    background: #E2E8F0;
    border: 1px solid #CBD5E1;
}
.cust-status-toggle .toggle-slider-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    position: absolute;
    left: 3px;
    top: 3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cust-status-toggle.completed .toggle-slider-thumb,
.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}
.cust-status-toggle.pending .toggle-slider-thumb {
    transform: translateX(10px);
}
.cust-status-toggle.cancelled .toggle-slider-thumb {
    transform: translateX(0px);
}

/* Small toggle for mobile cards */
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
.cust-status-toggle.sm.completed .toggle-slider-thumb,
.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}
.cust-status-toggle.sm.pending .toggle-slider-thumb {
    transform: translateX(8px);
}
.cust-status-toggle.sm.cancelled .toggle-slider-thumb {
    transform: translateX(0px);
}

/* 10. Status Pills & Pulse Dots */
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    background: transparent;
}
.cust-toggle-pill .pill-chevron {
    font-size: 0.65rem;
    opacity: 0.65;
    transition: transform 0.2s ease;
}
.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #176B5B;
    border-color: rgba(23, 107, 91, 0.2);
}
.cust-toggle-pill.pending {
    background: #FEF3C7;
    color: #B45309;
    border-color: rgba(217, 119, 6, 0.25);
}
.cust-toggle-pill.cancelled {
    background: #FEE2E2;
    color: #DC2626;
    border-color: rgba(220, 38, 38, 0.2);
}
.cust-toggle-pill:hover {
    filter: brightness(0.96);
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
    transition: all 0.2s ease;
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

/* 11. Status Dropdown Menu */
.status-pill-dropdown-wrap {
    position: relative;
    display: inline-block;
}
.tx-status-dropdown-menu {
    position: absolute;
    top: calc(100% + 5px);
    left: 50%;
    transform: translateX(-50%);
    background: #FFFFFF;
    border: 1px solid rgba(22, 68, 59, 0.15);
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(22, 68, 59, 0.15);
    padding: 4px;
    min-width: 130px;
    z-index: 1000;
    display: none;
}
.tx-status-dropdown-menu.open {
    display: flex;
    flex-direction: column;
    animation: txDropFade 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes txDropFade {
    from { opacity: 0; transform: translate(-50%, -6px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}
.tx-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 6px 10px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 0.76rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    text-align: left;
    transition: background-color 0.15s ease;
}
.tx-status-item:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.tx-status-item.active {
    background: rgba(23, 107, 91, 0.08);
    color: #176B5B;
    font-weight: 700;
}
.status-menu-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.status-menu-dot.completed { background: #176B5B; }
.status-menu-dot.pending { background: #F59E0B; }
.status-menu-dot.cancelled { background: #DC2626; }

/* =========================================================================
   12. MOBILE APP RESPONSIVE SYSTEM (EXACT MATCH TO office/customers)
   ========================================================================= */

/* Desktop Defaults (Screens >= 769px: Mobile Elements Completely Hidden) */
.mv-bookings-header {
    display: none !important;
}
.bookings-page-view.finance-page-view .cards-grid {
    display: none !important;
}

/* Tablet Screens (769px to 991px) */
@media (max-width: 991px) and (min-width: 769px) {
    .booking-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }
    .filter-bar-compact {
        flex-wrap: wrap !important;
        gap: 10px !important;
    }
    .fbc-search {
        min-width: 240px;
        flex: 1 1 auto;
    }
    .modern-table-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
}

/* Mobile Screens (Screens <= 768px: Switch from Desktop to Mobile App UI) */
@media (max-width: 768px) {
    .bookings-page-view.finance-page-view {
        padding-bottom: 76px;
    }

    /* Hide Desktop Views */
    .bookings-page-view.finance-page-view .desktop-header-wrapper,
    .bookings-page-view.finance-page-view .booking-kpi-grid,
    .bookings-page-view.finance-page-view .filter-bar-compact,
    .bookings-page-view.finance-page-view .section-header,
    .bookings-page-view.finance-page-view .modern-table-container {
        display: none !important;
    }

    /* Show Mobile Specific App Bar & Controls */
    .bookings-page-view.finance-page-view .mv-bookings-header {
        display: block !important;
        margin-bottom: 12px;
    }

    /* Show Mobile Cards Grid */
    .bookings-page-view.finance-page-view .cards-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
    }

    /* Top App Bar */
    .mv-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 4px 0 12px 0;
    }
    .mv-top-bar-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mv-back-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        transition: all 0.15s ease;
    }
    .mv-back-btn:hover {
        background: #f8fafc;
        color: #176B5B;
    }
    .mv-page-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1;
    }
    .mv-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 9999px;
        background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
        color: #ffffff;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(23, 107, 91, 0.32);
        border: none;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .mv-add-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(23, 107, 91, 0.42);
        color: #ffffff;
    }

    /* Mobile Search & Filter */
    .mv-search-bar-wrap {
        margin-bottom: 12px;
    }
    .mv-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        margin: 0;
    }
    .mv-search-input-box {
        flex: 1;
        min-width: 0;
        position: relative;
        display: flex;
        align-items: center;
    }
    .mv-search-ico {
        position: absolute;
        left: 14px;
        color: #94a3b8;
        font-size: 13.5px;
        pointer-events: none;
    }
    .mv-search-input-box input {
        width: 100%;
        height: 44px;
        padding: 0 36px 0 40px;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        font-size: 13.5px;
        font-weight: 500;
        color: #1e293b;
        outline: none;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }
    .mv-search-input-box input:focus {
        background: #ffffff;
        border-color: #176B5B;
        box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.12);
    }
    .mv-search-clear {
        position: absolute;
        right: 12px;
        color: #94a3b8;
        font-size: 13px;
        text-decoration: none;
        padding: 4px;
    }
    .mv-search-clear:hover {
        color: #ef4444;
    }
    .mv-filter-toggle {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 15px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .mv-filter-toggle:hover,
    .mv-filter-toggle.active {
        background: #f1f5f9;
        color: #176B5B;
        border-color: #cbd5e1;
    }
    .mv-filter-panel {
        background: #ffffff;
        border-radius: 14px;
        padding: 12px 14px;
        margin-top: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    .mv-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }
    .mv-filter-row:last-child {
        margin-bottom: 0;
    }
    .mv-filter-row label {
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
    }
    .mv-filter-row select {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        color: #1e293b;
        background: #f8fafc;
    }

    /* Horizontal Scroll Status Pills */
    .mv-status-scroll {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
        margin-bottom: 14px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .mv-status-scroll::-webkit-scrollbar {
        display: none;
    }
    .mv-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9999px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        flex-shrink: 0;
    }
    .mv-pill:hover {
        border-color: #cbd5e1;
        color: #1e293b;
    }
    .mv-pill-cnt {
        font-size: 11px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
    }
    .mv-pill.active {
        background: #176B5B !important;
        border-color: #176B5B !important;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(23, 107, 91, 0.25);
    }
    .mv-pill.active .mv-pill-cnt {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Mobile Transaction Cards (Exact office/customers Architecture) */
    .mv-booking-card-wrapper {
        position: relative;
        width: 100%;
    }
    .mv-booking-card {
        display: flex;
        align-items: stretch;
        gap: 12px;
        padding: 12px 14px;
        padding-right: 36px;
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
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        align-self: flex-start;
    }
    .mv-tx-avatar {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .mv-tx-avatar.credit {
        background: #E8F7F0;
        color: #218765;
        border: 1.5px solid rgba(33, 135, 101, 0.25);
    }
    .mv-tx-avatar.debit {
        background: #FEE2E2;
        color: #DC2626;
        border: 1.5px solid rgba(220, 38, 38, 0.25);
    }
    .mv-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 2.5px;
    }
    .mv-code-row {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .mv-code {
        font-size: 11px;
        font-weight: 700;
        color: #176B5B;
        letter-spacing: -0.01em;
        font-family: monospace;
        background: rgba(23, 107, 91, 0.08);
        padding: 1px 6px;
        border-radius: 4px;
    }
    .mv-tx-badge {
        font-size: 9.5px;
        font-weight: 800;
        padding: 1px 5px;
        border-radius: 4px;
        letter-spacing: 0.02em;
    }
    .mv-tx-badge.credit {
        background: #E8F7F0;
        color: #218765;
    }
    .mv-tx-badge.debit {
        background: #FEE2E2;
        color: #DC2626;
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
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        gap: 6px;
        padding: 2px 6px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }
    .mv-toggle-label-text {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: capitalize;
    }
    .mv-toggle-label-text.completed {
        color: #176B5B;
    }
    .mv-toggle-label-text.pending {
        color: #B45309;
    }
    .mv-toggle-label-text.cancelled {
        color: #DC2626;
    }
    .mv-tx-amounts {
        text-align: right;
        margin-top: auto;
    }
    .mv-price {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
    }
    .mv-price.credit {
        color: #218765;
    }
    .mv-price.debit {
        color: #DC2626;
    }
    .mv-bal-sub {
        font-size: 0.72rem;
        color: #64748B;
        font-weight: 600;
        margin-top: 2px;
        white-space: nowrap;
    }
}

/* Extra Compact Phones (<= 480px) */
@media (max-width: 480px) {
    .mv-booking-card {
        padding: 10px 12px !important;
        padding-right: 32px !important;
        gap: 10px !important;
    }
    .mv-thumb-wrap, .mv-tx-avatar {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        font-size: 1.05rem !important;
    }
    .mv-price {
        font-size: 0.98rem !important;
    }
    .mv-tour-title {
        font-size: 13px !important;
    }
}

/* Modals on Mobile Devices */
@media (max-width: 640px) {
    .modal-dialog-custom {
        width: calc(100% - 24px) !important;
        max-width: 440px !important;
        margin: 0 auto !important;
    }
}
</style>
