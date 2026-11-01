<?php
/**
 * MANASWI TOURS & TRAVELS — EMPLOYEE ADVANCE LOANS & RECOVERIES
 * Architecture identical to office/finance/transactions
 * Standardized Schema: # | Date | Employee | Reason / Purpose | Amount | Recovered | Status | Actions
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
                <span>Approved</span>
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
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table" id="advancesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 110px;">Date</th>
                        <th style="min-width: 180px;">Employee</th>
                        <th style="min-width: 140px;">Employee Role</th>
                        <th style="min-width: 220px;">Reason / Purpose</th>
                        <th style="text-align: right; width: 130px;">Amount</th>
                        <th style="text-align: right; width: 130px;">Recovered</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-center" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advances)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="no-data-message" style="text-align: center; padding: 2.5rem;">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No advance requests found</h4>
                                <p class="text-muted" style="margin:0;">Register an advance loan using the button above.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($advances as $idx => $adv):
                            $rowNum = ($currentPage - 1) * $perPage + $idx + 1;
                            $st = strtolower($adv['status'] ?? 'pending');
                            if ($st === 'approved') {
                                $statusClass = 'completed';
                                $statusText = 'APPROVED';
                                $isApproved = true;
                            } elseif ($st === 'recovered') {
                                $statusClass = 'completed';
                                $statusText = 'RECOVERED';
                                $isApproved = true;
                            } elseif ($st === 'rejected') {
                                $statusClass = 'cancelled';
                                $statusText = 'REJECTED';
                                $isApproved = false;
                            } else {
                                $statusClass = 'pending';
                                $statusText = 'PENDING';
                                $isApproved = false;
                            }

                            // Role resolution matching office/employees architecture
                            $role = !empty($adv['linked_role']) ? $adv['linked_role'] : (!empty($adv['designation']) ? $adv['designation'] : ($adv['department'] === 'fleet_chauffeur' ? 'driver' : strtolower($adv['department'] ?? 'staff')));
                            $roleText = ucwords(str_replace('_', ' ', $role));
                        ?>
                        <tr id="advRow-<?= (int)$adv['id'] ?>">
                            <!-- 1. # Index (Serial No.) -->
                            <td class="text-center" style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                <?= $rowNum ?>
                            </td>

                            <!-- 2. Date -->
                            <td style="font-size: 0.82rem; color: var(--text-muted); white-space: nowrap;">
                                <div style="font-weight: 600; color: #1E293B;"><?= format_date($adv['requested_date']) ?></div>
                            </td>

                            <!-- 3. Employee (Show ONLY employee name) -->
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px; background: rgba(23, 107, 91, 0.08); color: #176B5B; font-size: 0.8rem; flex-shrink: 0;">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </span>
                                    <a href="<?= url('/office/finance/advances/' . $adv['id']) ?>" style="color: inherit; text-decoration: none;" class="tx-account-link" title="View Advance Details for <?= e($adv['employee_name']) ?>">
                                        <?= e($adv['employee_name']) ?>
                                    </a>
                                </div>
                            </td>

                            <!-- 4. Employee Role (Matching office/employees) -->
                            <td>
                                <div class="emp-role-cell">
                                    <span class="emp-role-pill" style="display: inline-block; background: rgba(23, 107, 91,0.08); color: #16443B; border: 1px solid rgba(23, 107, 91,0.2); border-radius: 6px; padding: 4px 12px; font-size: 12.5px; font-weight: 700; text-transform: capitalize;">
                                        <?= e($roleText) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- 5. Reason / Purpose -->
                            <td>
                                <div style="font-size: 0.85rem; color: var(--vg-navy-900);"><?= e($adv['reason']) ?></div>
                            </td>

                            <!-- 5. Amount -->
                            <td style="text-align: right; white-space: nowrap;">
                                <strong style="font-size: 0.95rem; font-weight: 800; color: var(--vg-navy-900);">
                                    <?= format_currency((float)$adv['amount']) ?>
                                </strong>
                            </td>

                            <!-- 6. Recovered -->
                            <td style="text-align: right; font-size: 0.9rem; color: var(--status-success); font-weight: 700; white-space: nowrap;">
                                <?= format_currency((float)$adv['recovered_amount']) ?>
                            </td>

                            <!-- 7. Status (Interactive Toggle & Dropdown matching office/finance/transactions) -->
                            <td class="text-center">
                                <div class="cust-toggle-cell" id="advToggleWrap-<?= (int)$adv['id'] ?>">
                                    <button type="button" 
                                            class="cust-status-toggle <?= $statusClass ?><?= $isApproved ? ' is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isApproved ? 'true' : 'false' ?>" 
                                            data-id="<?= (int)$adv['id'] ?>"
                                            data-status="<?= $statusClass ?>"
                                            data-ref="<?= addslashes(e($adv['advance_code'])) ?>"
                                            onclick="toggleAdvanceStatus(<?= (int)$adv['id'] ?>, this, event)"
                                            title="Click to cycle status: Pending &rarr; Approved &rarr; Recovered &rarr; Rejected (Current: <?= $statusText ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <div class="status-pill-dropdown-wrap">
                                        <button type="button" 
                                                class="cust-toggle-pill <?= $statusClass ?>" 
                                                onclick="openAdvStatusDropdown(<?= (int)$adv['id'] ?>, this, event)"
                                                title="Click to select status (Approved / Pending / Recovered / Rejected)">
                                            <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                            <span class="toggle-label-text"><?= $statusText ?></span>
                                            <i class="fa-solid fa-chevron-down pill-chevron"></i>
                                        </button>
                                        <div class="tx-status-dropdown-menu" id="advStatusDropdown-<?= (int)$adv['id'] ?>">
                                            <button type="button" class="tx-status-item <?= $st === 'approved' ? 'active' : '' ?>" data-status="approved" onclick="setAdvanceDirectStatus(<?= (int)$adv['id'] ?>, 'approved', event)">
                                                <span class="status-menu-dot completed"></span>
                                                <span>Approved</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $st === 'pending' ? 'active' : '' ?>" data-status="pending" onclick="setAdvanceDirectStatus(<?= (int)$adv['id'] ?>, 'pending', event)">
                                                <span class="status-menu-dot pending"></span>
                                                <span>Pending</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $st === 'recovered' ? 'active' : '' ?>" data-status="recovered" onclick="setAdvanceDirectStatus(<?= (int)$adv['id'] ?>, 'recovered', event)">
                                                <span class="status-menu-dot completed"></span>
                                                <span>Recovered</span>
                                            </button>
                                            <button type="button" class="tx-status-item <?= $st === 'rejected' ? 'active' : '' ?>" data-status="rejected" onclick="setAdvanceDirectStatus(<?= (int)$adv['id'] ?>, 'rejected', event)">
                                                <span class="status-menu-dot cancelled"></span>
                                                <span>Rejected</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 8. Actions (View, Edit, Delete only, exactly like office/finance/transactions) -->
                            <td class="text-center">
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <a href="<?= url('/office/finance/advances/' . $adv['id']) ?>" class="action-btn-table view" title="View Advance Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button type="button" class="action-btn-table edit" title="Edit Advance Loan" onclick="openEditModal(<?= htmlspecialchars(json_encode($adv), ENT_QUOTES, 'UTF-8') ?>, event)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="action-btn-table delete" title="Delete Advance" onclick="openDeleteModal(<?= (int)$adv['id'] ?>, '<?= addslashes(e($adv['advance_code'])) ?>', <?= (float)$adv['amount'] ?>, '<?= addslashes(e($adv['employee_name'])) ?>', event)">
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

        <!-- Mobile Cards List (Matching office/finance/transactions architecture) -->
        <div class="cards-grid">
            <?php foreach ($advances as $idx => $adv):
                $st = strtolower($adv['status'] ?? 'pending');
                if ($st === 'approved') {
                    $statusClass = 'completed';
                    $statusText = 'Approved';
                    $isApproved = true;
                } elseif ($st === 'recovered') {
                    $statusClass = 'completed';
                    $statusText = 'Recovered';
                    $isApproved = true;
                } elseif ($st === 'rejected') {
                    $statusClass = 'cancelled';
                    $statusText = 'Rejected';
                    $isApproved = false;
                } else {
                    $statusClass = 'pending';
                    $statusText = 'Pending';
                    $isApproved = false;
                }
                $role = !empty($adv['linked_role']) ? $adv['linked_role'] : (!empty($adv['designation']) ? $adv['designation'] : ($adv['department'] === 'fleet_chauffeur' ? 'driver' : strtolower($adv['department'] ?? 'staff')));
                $roleText = ucwords(str_replace('_', ' ', $role));
            ?>
            <div class="mv-booking-card-wrapper" id="mvAdvCard-<?= (int)$adv['id'] ?>">
                <a href="<?= url('/office/finance/advances/' . $adv['id']) ?>" class="mv-booking-card">
                    <!-- Left Thumbnail Avatar -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-tx-avatar debit">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>

                    <!-- Middle Details -->
                    <div class="mv-details">
                        <div class="mv-code-row">
                            <span class="mv-code"><?= e($adv['advance_code']) ?></span>
                            <span class="emp-role-pill" style="display: inline-block; background: rgba(23, 107, 91,0.08); color: #16443B; border: 1px solid rgba(23, 107, 91,0.2); border-radius: 5px; padding: 2px 8px; font-size: 11px; font-weight: 700; text-transform: capitalize;">
                                <?= e($roleText) ?>
                            </span>
                        </div>
                        <div class="mv-tour-title" title="<?= e($adv['employee_name']) ?>"><?= e($adv['employee_name']) ?></div>
                        <div class="mv-passenger">
                            <i class="fa-regular fa-calendar"></i>
                            <span><?= format_date($adv['requested_date']) ?></span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-regular fa-note-sticky"></i>
                            <span><?= e($adv['reason']) ?></span>
                        </div>
                    </div>

                    <!-- Right Column: Status Toggle on Top, Amount & Recovered on Bottom -->
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.preventDefault(); event.stopPropagation();">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $statusClass ?><?= $isApproved ? ' is-active' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $isApproved ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$adv['id'] ?>"
                                    data-status="<?= $statusClass ?>"
                                    data-ref="<?= addslashes(e($adv['advance_code'])) ?>"
                                    onclick="toggleAdvanceStatus(<?= (int)$adv['id'] ?>, this, event)"
                                    title="Click to cycle status: <?= $statusText ?>">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb">
                                        <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                    </span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>" onclick="toggleAdvanceStatus(<?= (int)$adv['id'] ?>, this.previousElementSibling, event)"><?= $statusText ?></span>
                        </div>

                        <div class="mv-tx-amounts">
                            <div class="mv-price debit">
                                <?= format_currency((float)$adv['amount']) ?>
                            </div>
                            <div class="mv-bal-sub">
                                Rec: <?= format_currency((float)$adv['recovered_amount']) ?>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action in Corner (View, Edit, Delete only) -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/finance/advances/' . $adv['id']) ?>" class="action-btn-table view" title="View Advance Details">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <button type="button" class="action-btn-table edit" title="Edit Advance Loan" onclick="openEditModal(<?= htmlspecialchars(json_encode($adv), ENT_QUOTES, 'UTF-8') ?>, event)">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button type="button" class="action-btn-table delete" title="Delete Advance" onclick="openDeleteModal(<?= (int)$adv['id'] ?>, '<?= addslashes(e($adv['advance_code'])) ?>', <?= (float)$adv['amount'] ?>, '<?= addslashes(e($adv['employee_name'])) ?>', event)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Modern Pagination Matching office/finance/transactions -->
        <?php
            $totRec = $totalRecords ?? count($advances);
            $currP = $currentPage ?? 1;
            $pSize = $perPage ?? 15;
            $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
            $to   = min($currP * $pSize, $totRec);
        ?>
        <div class="modern-pagination">
            <div class="pagination-info">
                Showing <strong><?= number_format($from) ?></strong> to <strong><?= number_format($to) ?></strong> of <strong><?= number_format($totRec) ?></strong> advances
            </div>
            <div class="pagination-controls">
                <?php
                    $buildPageUrl = function(int $p) use ($search, $selectedStatus, $perPage) {
                        $params = [];
                        if (!empty($search)) $params['search'] = $search;
                        if (!empty($selectedStatus)) $params['status'] = $selectedStatus;
                        if (!empty($perPage) && $perPage != 15) $params['per_page'] = $perPage;
                        $params['page'] = $p;
                        return url('/office/finance/advances?' . http_build_query($params));
                    };
                ?>
                <a href="<?= $currP > 1 ? $buildPageUrl($currP - 1) : 'javascript:void(0)' ?>"
                   class="pagination-btn <?= $currP <= 1 ? 'disabled' : '' ?>"
                   title="Previous Page">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <?php
                    $tPages = $totalPages ?? 1;
                    $startP = max(1, $currP - 2);
                    $endP = min($tPages, $currP + 2);
                    if ($startP > 1) {
                        echo '<a href="' . $buildPageUrl(1) . '" class="pagination-btn">1</a>';
                        if ($startP > 2) echo '<span class="pagination-ellipsis">&hellip;</span>';
                    }
                    for ($p = $startP; $p <= $endP; $p++) {
                        $active = ($p === $currP) ? ' active' : '';
                        echo '<a href="' . $buildPageUrl($p) . '" class="pagination-btn' . $active . '">' . $p . '</a>';
                    }
                    if ($endP < $tPages) {
                        if ($endP < $tPages - 1) echo '<span class="pagination-ellipsis">&hellip;</span>';
                        echo '<a href="' . $buildPageUrl($tPages) . '" class="pagination-btn">' . $tPages . '</a>';
                    }
                ?>

                <a href="<?= $currP < $tPages ? $buildPageUrl($currP + 1) : 'javascript:void(0)' ?>"
                   class="pagination-btn <?= $currP >= $tPages ? 'disabled' : '' ?>"
                   title="Next Page">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
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
                    <label class="form-label" for="adv_employee_id">Employee / Staff Member <span class="req">*</span></label>
                    <select name="employee_id" id="adv_employee_id" class="form-select" required>
                        <option value="">— Select staff member —</option>
                        <?php foreach ($employees as $emp): 
                            $empDisplayName = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')) ?: ($emp['name'] ?? 'Employee');
                        ?>
                            <option value="<?= $emp['id'] ?>"><?= e($empDisplayName) ?> (<?= e($emp['employee_code']) ?> • <?= e($emp['designation'] ?? 'Staff') ?>)</option>
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
                    <textarea name="reason" id="adv_reason" class="form-control" rows="2" placeholder="e.g. Festival advance, medical emergency, educational fees..." required></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeAdvanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <span>Submit Advance</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Edit Staff Advance                                                 -->
<!-- ========================================================================= -->
<div id="editAdvanceModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'editAdvanceModalBackdrop')">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-pen-to-square" style="color: #C78A35;"></i>
                <span>Edit Staff Advance Loan</span>
            </h3>
            <button type="button" class="modal-close-custom" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="" method="POST" id="editAdvanceForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/advances') ?>">
            <div class="modal-body-custom">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Employee</label>
                    <input type="text" id="edit_adv_employee" class="form-control" readonly style="background: #F8FAFC; color: #475569; font-weight: 600;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_adv_amount">Amount (INR) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="edit_adv_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_adv_date">Request Date <span class="req">*</span></label>
                        <input type="date" name="requested_date" id="edit_adv_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_adv_status">Status</label>
                    <select name="status" id="edit_adv_status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="recovered">Recovered</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="edit_adv_reason">Purpose / Emergency Reason <span class="req">*</span></label>
                    <textarea name="reason" id="edit_adv_reason" class="form-control" rows="2" required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_adv_notes">Internal Audit Notes</label>
                    <textarea name="notes" id="edit_adv_notes" class="form-control" rows="2" placeholder="Approval or deduction comments..."></textarea>
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
<!-- MODAL: Delete Staff Advance                                               -->
<!-- ========================================================================= -->
<div id="deleteAdvanceModalBackdrop" class="modal-backdrop-custom" style="display: none;" onclick="handleModalBackdropClick(event, 'deleteAdvanceModalBackdrop')">
    <div class="modal-dialog-custom modal-dialog-delete">
        <div class="modal-header-custom delete-header">
            <div class="delete-icon-pulse">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #DC2626;">Delete Staff Advance</h3>
            <button type="button" class="modal-close-custom" onclick="closeDeleteModal()">&times;</button>
        </div>
        <form action="" method="POST" id="deleteAdvanceForm">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('/office/finance/advances') ?>">
            <div class="modal-body-custom">
                <p style="margin: 0 0 1rem 0; color: #334155; font-size: 0.92rem; line-height: 1.5;">
                    Are you sure you want to delete advance loan <strong id="deleteAdvanceCode" style="color: #0F172A; font-family: monospace;"></strong> for <strong id="deleteAdvanceEmployee"></strong>?
                </p>
                <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; gap: 0.65rem;">
                    <i class="fa-solid fa-circle-exclamation" style="color: #DC2626; margin-top: 2px;"></i>
                    <div style="font-size: 0.82rem; color: #991B1B; line-height: 1.4;">
                        <strong>Warning:</strong> This permanently removes the advance record from payroll deduction schedules.
                    </div>
                </div>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="deleteConfirmCheckbox" onchange="toggleDeleteConfirmBtn()">
                    <span>I understand that this action is irreversible.</span>
                </label>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" id="deleteAdvanceSubmitBtn" disabled>
                    <i class="fa-solid fa-trash-can"></i> <span>Delete Advance</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. New Advance Modal Handlers
    function openAdvanceModal() {
        document.getElementById('advanceModalBackdrop').style.display = 'flex';
    }
    function closeAdvanceModal() {
        document.getElementById('advanceModalBackdrop').style.display = 'none';
    }

    // 2. Edit Advance Modal Handlers
    function openEditModal(adv, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        const form = document.getElementById('editAdvanceForm');
        form.action = '<?= url('/office/finance/advances/') ?>' + adv.id;

        document.getElementById('edit_adv_employee').value = (adv.employee_name || 'Staff') + ' (' + (adv.employee_code || '') + ')';
        document.getElementById('edit_adv_amount').value = parseFloat(adv.amount || 0).toFixed(2);
        document.getElementById('edit_adv_date').value = adv.requested_date || '<?= date('Y-m-d') ?>';
        document.getElementById('edit_adv_status').value = (adv.status || 'pending').toLowerCase();
        document.getElementById('edit_adv_reason').value = adv.reason || '';
        document.getElementById('edit_adv_notes').value = adv.notes || '';

        document.getElementById('editAdvanceModalBackdrop').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('editAdvanceModalBackdrop').style.display = 'none';
    }

    // 3. Delete Advance Modal Handlers
    function openDeleteModal(id, code, amount, empName, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        const form = document.getElementById('deleteAdvanceForm');
        form.action = '<?= url('/office/finance/advances/') ?>' + id + '/delete';

        document.getElementById('deleteAdvanceCode').textContent = code + ' (₹' + parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
        document.getElementById('deleteAdvanceEmployee').textContent = empName;

        const chk = document.getElementById('deleteConfirmCheckbox');
        const btn = document.getElementById('deleteAdvanceSubmitBtn');
        if (chk) chk.checked = false;
        if (btn) btn.disabled = true;

        document.getElementById('deleteAdvanceModalBackdrop').style.display = 'flex';
    }
    function closeDeleteModal() {
        document.getElementById('deleteAdvanceModalBackdrop').style.display = 'none';
    }
    function toggleDeleteConfirmBtn() {
        const chk = document.getElementById('deleteConfirmCheckbox');
        const btn = document.getElementById('deleteAdvanceSubmitBtn');
        if (chk && btn) {
            btn.disabled = !chk.checked;
        }
    }

    // 4. Backdrop click dismiss
    function handleModalBackdropClick(e, backdropId) {
        if (e.target && e.target.id === backdropId) {
            document.getElementById(backdropId).style.display = 'none';
        }
    }

    // 5. Actions Popup (Desktop table & Mobile cards)
    function toggleActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }
    function toggleCardActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }

    // 6. Interactive Status Switch & Dropdown (Matching office/finance/transactions)
    function closeAllAdvStatusDropdowns() {
        document.querySelectorAll('.tx-status-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    }

    function openAdvStatusDropdown(id, pillBtn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('advStatusDropdown-' + id);
        if (!menu) return;
        const wasOpen = menu.classList.contains('open');
        closeAllAdvStatusDropdowns();
        if (!wasOpen) {
            menu.classList.add('open');
        }
    }

    function toggleAdvanceStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/finance/advances/') ?>' + id + '/toggle-status';
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
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data && data.success) {
                applyAdvStatusUI(id, data.status);
            } else {
                alert(data.message || 'Could not update advance status.');
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            console.error('Toggle status error:', err);
            alert('A network error occurred while toggling status.');
        });
    }

    function setAdvanceDirectStatus(id, targetStatus, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        closeAllAdvStatusDropdowns();
        if (!id) return;

        const url = '<?= url('/office/finance/advances/') ?>' + id + '/toggle-status';
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
                applyAdvStatusUI(id, data.status);
            } else {
                alert(data.message || 'Could not update advance status.');
            }
        })
        .catch(err => {
            console.error('Direct status error:', err);
            alert('A network error occurred while updating status.');
        });
    }

    function applyAdvStatusUI(id, newStatus) {
        const st = (newStatus || 'pending').toLowerCase();
        const isApproved = (st === 'approved' || st === 'recovered');

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

        // A. Update Desktop Table Row
        const row = document.getElementById('advRow-' + id);
        if (row) {
            const toggleBtn = row.querySelector('.cust-status-toggle');
            if (toggleBtn) {
                toggleBtn.className = 'cust-status-toggle ' + statusClass + (isApproved ? ' is-active' : '');
                toggleBtn.setAttribute('aria-checked', isApproved ? 'true' : 'false');
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
            row.querySelectorAll('.tx-status-item').forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                item.classList.toggle('active', itemStatus === st);
            });
        }

        // B. Update Mobile Card
        const card = document.getElementById('mvAdvCard-' + id);
        if (card) {
            const mToggleBtn = card.querySelector('.cust-status-toggle');
            if (mToggleBtn) {
                mToggleBtn.className = 'cust-status-toggle sm ' + statusClass + (isApproved ? ' is-active' : '');
                mToggleBtn.setAttribute('aria-checked', isApproved ? 'true' : 'false');
                mToggleBtn.setAttribute('data-status', statusClass);
            }
            const mText = card.querySelector('.mv-toggle-label-text');
            if (mText) {
                mText.className = 'mv-toggle-label-text ' + statusClass;
                mText.textContent = statusText.charAt(0).toUpperCase() + statusText.slice(1).toLowerCase();
            }
        }
    }

    // Close popups on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
        if (!e.target.closest('.status-pill-dropdown-wrap')) {
            closeAllAdvStatusDropdowns();
        }
    });
</script>

<style>
/* =========================================================================
   STATUS SWITCH, TOGGLE PILL & DROPDOWN (1:1 Match to office/finance/transactions)
   ========================================================================= */
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

/* Status Pills & Pulse Dots */
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

/* Status Dropdown Menu */
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

/* Action Buttons Popup */
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

/* Delete modal specific */
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

/* Employee Role Pill (Matching office/employees) */
.emp-role-cell {
    display: flex;
    align-items: center;
}
.emp-role-pill {
    display: inline-block;
    background: rgba(23, 107, 91, 0.08);
    color: #16443B;
    border: 1px solid rgba(23, 107, 91, 0.2);
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 12.5px;
    font-weight: 700;
    text-transform: capitalize;
}
</style>
