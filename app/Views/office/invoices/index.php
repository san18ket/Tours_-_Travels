<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('invoices.title', 'Invoices & Billing Ledger'),
            'description' => __('invoices.subtitle', 'Official GST tax invoices, payment reconciliation, balance tracking, and immutable ledger integrity.'),
            'image'       => 'header-invoices.jpg',
            'badge'       => 'GST Billing Engine',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.billing', 'Billing'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/bookings') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><span>' . e(__('dispatch.go_to_bookings', 'View Bookings')) . '</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid" id="invoicesKpiGrid">
        <!-- Card 1: Invoiced Volume -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all issued invoices">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.invoiced_volume', 'Invoiced Volume')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_currency($totalInvoiced ?? 0.0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= number_format($totalAll ?? count($invoices)) ?> total invoices issued</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Collections Received -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'paid' ? 'is-selected' : '' ?>" data-status="paid" role="button" tabindex="0" title="Click to filter paid invoices">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.collections_received', 'Collections Received')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color:#10b981;"><?= format_currency($totalPaid ?? 0.0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= number_format($paidCount ?? 0) ?> verified paid in full</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Outstanding Receivables -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'unpaid' ? 'is-selected' : '' ?>" data-status="unpaid" role="button" tabindex="0" title="Click to filter unpaid receivables">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.outstanding_balance', 'Outstanding Balance')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color:<?= ($totalOutstanding ?? 0.0) > 0 ? '#dc2626' : '#16443B' ?>;">
                <?= format_currency($totalOutstanding ?? 0.0) ?>
            </div>
            <div class="booking-kpi-sub">
                <span><?= number_format($unpaidCount ?? 0) ?> pending collection</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Partial Settlements -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'partial' ? 'is-selected' : '' ?>" data-status="partial" role="button" tabindex="0" title="Click to filter partially paid invoices">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Partial Payments</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($partialCount ?? 0) ?>"><?= number_format($partialCount ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Partially settled balances</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/invoices') ?>" class="filter-bar-compact" id="invoiceFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by invoice #, booking code, client name, email..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="paid" <?= ($selectedStatus ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="partial" <?= ($selectedStatus ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
            <option value="unpaid" <?= ($selectedStatus ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
        </select>

        <div class="fbc-date-group" style="display: flex; align-items: center; gap: 6px;">
            <input type="date" name="date_from" id="dateFromInput" value="<?= e($dateFrom ?? '') ?>" class="fbc-input-date" title="Invoice Date From" style="height: 38px; border: 1.5px solid #DCE6F2; border-radius: 10px; padding: 0 8px; font-size: 0.82rem; color: #0C2B52; background: #fff;">
            <span style="font-size: 11px; color: #94A3B8; font-weight: 600;">to</span>
            <input type="date" name="date_to" id="dateToInput" value="<?= e($dateTo ?? '') ?>" class="fbc-input-date" title="Invoice Date To" style="height: 38px; border: 1.5px solid #DCE6F2; border-radius: 10px; padding: 0 8px; font-size: 0.82rem; color: #0C2B52; background: #fff;">
        </div>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus) || !empty($dateFrom) || !empty($dateTo)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Tax Invoices</h2>
            </div>
            <a href="<?= url('/office/bookings') ?>" class="mv-add-btn">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/invoices') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search invoice, client..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 25, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/invoices' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="invoicesMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? count($invoices)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('paid') ?>" class="mv-pill <?= $currStatus === 'paid' ? 'active' : '' ?>" data-status="paid">
                <span>Paid</span>
                <span class="mv-pill-cnt"><?= (int)($paidCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('unpaid') ?>" class="mv-pill <?= $currStatus === 'unpaid' ? 'active' : '' ?>" data-status="unpaid">
                <span>Unpaid</span>
                <span class="mv-pill-cnt"><?= (int)($unpaidCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('partial') ?>" class="mv-pill <?= $currStatus === 'partial' ? 'active' : '' ?>" data-status="partial">
                <span>Partial</span>
                <span class="mv-pill-cnt"><?= (int)($partialCount ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Invoices Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="invoicesSection">
        <div class="section-header">
            <h3 class="section-title">All Billing Invoices</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($invoices)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="invoicesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 24%;">Invoice & Issued Date</th>
                        <th style="width: 26%;">Client Name & Account</th>
                        <th class="text-center" style="width: 16%;">Total / Paid</th>
                        <th class="text-center" style="width: 14%;">Balance Due</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center" style="width: 58px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoicesTableBody">
                    <?php foreach ($invoices as $i => $inv):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $balance = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                        $status = strtolower($inv['payment_status'] ?? 'unpaid');
                        $statusClass = match($status) {
                            'paid' => 'completed',
                            'partial' => 'in-progress',
                            default => 'cancelled'
                        };
                        $statusText = ucfirst($status);
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:monospace;letter-spacing:-0.01em;">
                                <?= e($inv['invoice_number']) ?>
                            </div>
                            <div style="margin-top: 3px;">
                                <small class="text-muted" style="font-size:11.5px;">
                                    <?= format_date($inv['issued_at'] ?? $inv['created_at']) ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <div class="client-cell">
                                <div class="client-info">
                                    <span class="client-name"><?= e($inv['customer_name'] ?? 'Client') ?></span>
                                    <?php if (!empty($inv['customer_email'])): ?>
                                        <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                            <?= e($inv['customer_email']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:800;color:var(--brand-secondary, #16443B);font-size:13.5px;">
                                <?= format_currency((float)$inv['total_amount']) ?>
                            </div>
                            <small style="color:#10b981;font-weight:600;font-size:11px;">
                                Rec'd <?= format_currency((float)$inv['paid_amount']) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;font-size:13px;color:<?= $balance > 0 ? '#dc2626' : '#64748b' ?>;">
                                <?= format_currency($balance) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="status-cell">
                                <span class="status-dot <?= $statusClass ?>"></span>
                                <span class="status-badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <!-- View Full Invoice -->
                                    <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-btn-table view" title="View Full Invoice">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <!-- Print Tax Invoice -->
                                    <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="action-btn-table dispatch" title="Print Tax Invoice">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <!-- Record Payment (Only when balance > 0.001) -->
                                    <?php if ($balance > 0.001): ?>
                                    <button type="button" class="action-btn-table pay" title="Record Payment" onclick='openRecordPaymentModal(<?= htmlspecialchars(json_encode([
                                        'id' => (int)$inv['id'],
                                        'invoice_number' => $inv['invoice_number'],
                                        'customer_name' => $inv['customer_name'] ?? 'Customer',
                                        'total_amount' => (float)$inv['total_amount'],
                                        'paid_amount' => (float)$inv['paid_amount'],
                                    ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                        <i class="fa-solid fa-credit-card"></i>
                                    </button>
                                    <?php endif; ?>
                                    <!-- View Linked Booking -->
                                    <?php if (!empty($inv['booking_id'])): ?>
                                    <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" class="action-btn-table edit" title="View Linked Booking">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message" style="padding: 2.5rem 1.5rem; text-align: center;">
                                <div class="no-data-icon" style="font-size: 2.2rem; color: #94A3B8; margin-bottom: 0.5rem;"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No invoices found</h4>
                                <p class="text-muted" style="margin: 0 0 1rem 0; font-size: 0.88rem;">Create a booking to generate an invoice automatically.</p>
                                <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.15rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem;">
                                    <i class="fa-solid fa-plus"></i> Create New Booking
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="invoicesCards">
            <?php foreach ($invoices as $inv):
                $balance = max(0.0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                $status = strtolower($inv['payment_status'] ?? 'unpaid');
                $statusClass = match($status) {
                    'paid' => 'paid',
                    'partial' => 'partial',
                    default => 'unpaid'
                };
                $statusText = ucfirst($status);
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="mv-booking-card">
                    <!-- Left: Invoice Monogram Icon -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <i class="fa-solid fa-file-invoice-dollar" style="font-size:22px;"></i>
                        </div>
                    </div>

                    <!-- Middle: Invoice Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($inv['invoice_number']) ?></div>
                        <div class="mv-tour-title" title="<?= e($inv['customer_name'] ?? 'Client') ?>">
                            <?= e($inv['customer_name'] ?? 'Client') ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>Issued <?= format_date($inv['issued_at']) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-ticket"></i>
                            <span>Booking: <?= !empty($inv['booking_code']) ? '#' . e($inv['booking_code']) : '—' ?></span>
                        </div>
                        <div class="mv-vehicle" style="color:<?= $balance > 0 ? '#dc2626' : '#10b981' ?>;">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Due: <?= format_currency($balance) ?></span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Total amount bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price"><?= format_currency((float)$inv['total_amount']) ?></div>
                    </div>
                </a>

                <!-- Dedicated Mobile Card Action Strip -->
                <div class="mv-card-actions-bar">
                    <div class="mv-card-actions-bar-left">
                        <?php if (!empty($inv['booking_id']) && !empty($inv['booking_code'])): ?>
                            <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" class="mv-card-ref-link" title="Linked Booking #<?= e($inv['booking_code']) ?>">
                                <i class="fa-solid fa-ticket"></i> #<?= e($inv['booking_code']) ?>
                            </a>
                        <?php else: ?>
                            <span class="mv-card-ref-text"><i class="fa-regular fa-file-lines"></i> Direct Invoice</span>
                        <?php endif; ?>
                    </div>
                    <div class="mv-card-actions-bar-right">
                        <!-- Floating Action Button Menu for Mobile -->
                        <div class="mv-card-more-actions">
                            <button type="button" class="action-toggle-btn mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <div class="card-btns-popup">
                                <!-- View Full Invoice -->
                                <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-btn-table view" title="View Full Invoice">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <!-- Print Tax Invoice -->
                                <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="action-btn-table dispatch" title="Print Tax Invoice">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <!-- Record Payment (Only when balance > 0.001) -->
                                <?php if ($balance > 0.001): ?>
                                <button type="button" class="action-btn-table pay" title="Record Payment" onclick='openRecordPaymentModal(<?= htmlspecialchars(json_encode([
                                    'id' => (int)$inv['id'],
                                    'invoice_number' => $inv['invoice_number'],
                                    'customer_name' => $inv['customer_name'] ?? 'Customer',
                                    'total_amount' => (float)$inv['total_amount'],
                                    'paid_amount' => (float)$inv['paid_amount'],
                                ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                    <i class="fa-solid fa-credit-card"></i>
                                </button>
                                <?php endif; ?>
                                <!-- View Linked Booking (if present) -->
                                <?php if (!empty($inv['booking_id'])): ?>
                                <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" class="action-btn-table edit" title="View Linked Booking">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($invoices)): ?>
            <div class="no-data-message" style="padding: 2.5rem 1.5rem; text-align: center;">
                <div class="no-data-icon" style="font-size: 2.2rem; color: #94A3B8; margin-bottom: 0.5rem;"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">No invoices found</h4>
                <p class="text-muted" style="margin: 0 0 1rem 0; font-size: 0.88rem;">Create a booking to generate an invoice automatically.</p>
                <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.15rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem;">
                    <i class="fa-solid fa-plus"></i> Create New Booking
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalRecords ?? count($invoices);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/invoices?' . http_build_query($q));
        };
    ?>
    <div id="invoicesPaginationWrapper">
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> invoice(s)</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" data-page="<?= max(1, $currP - 1) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page">&#10094;</a>
            <?php
                $rangeStart = max(1, $currP - 1);
                $rangeEnd   = min($totalPages, $rangeStart + 2);
                if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                    $rangeStart = max(1, $rangeEnd - 2);
                }
                for ($i = $rangeStart; $i <= $rangeEnd; $i++):
            ?>
                <a href="<?= $buildPageUrl($i) ?>" data-page="<?= $i ?>" class="<?= $i === $currP ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($rangeEnd < $totalPages): ?>
                <span class="dots">...</span>
                <a href="<?= $buildPageUrl($totalPages) ?>" data-page="<?= $totalPages ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" data-page="<?= min($totalPages, $currP + 1) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
    </div>

    <!-- Record Payment Modal -->
    <div id="recordPaymentModal" class="record-payment-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(12, 43, 82, 0.45); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
        <div class="record-payment-modal-card" style="background: #FFFFFF; border-radius: 18px; width: 100%; max-width: 520px; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2); border: 1.5px solid #DCE6F2; overflow: hidden;">
            <!-- Header -->
            <div style="background: #0C2B52; color: #FFFFFF; padding: 1.15rem 1.4rem; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #FFFFFF;">Record Customer Payment</h3>
                        <div style="font-size: 0.8rem; color: #93C5FD;" id="rpmInvoiceTitle">Invoice #</div>
                    </div>
                </div>
                <button type="button" onclick="closeRecordPaymentModal()" style="background: none; border: none; color: #FFFFFF; font-size: 1.2rem; cursor: pointer; padding: 4px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Summary Ribbon -->
            <div style="background: #F8FAFD; border-bottom: 1px solid #EDF3FA; padding: 0.85rem 1.25rem; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; text-align: center;">
                <div>
                    <div style="font-size: 0.7rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Booking Amount</div>
                    <div id="rpmTotalAmount" style="font-size: 0.95rem; font-weight: 800; color: #0C2B52;">₹ 0.00</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Money Received</div>
                    <div id="rpmPaidAmount" style="font-size: 0.95rem; font-weight: 800; color: #10B981;">₹ 0.00</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Remaining Amount</div>
                    <div id="rpmRemainingAmount" style="font-size: 0.95rem; font-weight: 800; color: #DC2626;">₹ 0.00</div>
                </div>
            </div>

            <!-- Form Body -->
            <form id="rpmForm" method="POST" action="" style="padding: 1.25rem 1.4rem; display: flex; flex-direction: column; gap: 0.9rem;">
                <?= csrf_field() ?>
                <input type="hidden" name="idempotency_key" value="<?= bin2hex(random_bytes(16)) ?>">
                <input type="hidden" name="redirect_to" value="<?= url('/office/invoices') ?>">

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="rpmAmount" style="font-size: 0.85rem; font-weight: 700; color: #0C2B52; margin-bottom: 4px;">Amount Received (₹) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="rpmAmount" name="amount" class="form-control" required oninput="updateRpmRemaining()" style="font-weight: 700; font-size: 1.05rem; color: #176B5B;">
                    <div id="rpmNewRemainingNotice" style="margin-top: 4px; font-size: 0.8rem; color: #10B981; font-weight: 600;">
                        Remaining after payment: <span id="rpmNewRemaining">₹ 0.00</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="rpmPaymentMethod" style="font-size: 0.82rem; margin-bottom: 4px;">Payment Method <span style="color:#dc2626;">*</span></label>
                        <select id="rpmPaymentMethod" name="payment_method" class="form-select" required style="font-size: 0.88rem;">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="bank_transfer">Bank Transfer / NEFT / RTGS</option>
                            <option value="card">Credit / Debit Card</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="rpmReceivedDate" style="font-size: 0.82rem; margin-bottom: 4px;">Payment Date</label>
                        <input type="date" id="rpmReceivedDate" name="received_date" class="form-control" value="<?= date('Y-m-d') ?>" style="font-size: 0.88rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="rpmAccountId" style="font-size: 0.82rem; margin-bottom: 4px;">Received Into Account (Cash / Bank)</label>
                    <select id="rpmAccountId" name="account_id" class="form-select" style="font-size: 0.88rem;">
                        <?php if (!empty($accounts)): ?>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= e($acc['id']) ?>">
                                    <?= e($acc['account_name']) ?> (<?= e(ucfirst(str_replace('_', ' ', $acc['account_type'] ?? 'bank'))) ?> - Bal: <?= format_currency((float)($acc['current_balance'] ?? 0)) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Default Business Account</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="rpmTransactionId" style="font-size: 0.82rem; margin-bottom: 4px;">Ref / UTR / Cheque</label>
                        <input type="text" id="rpmTransactionId" name="transaction_id" class="form-control" placeholder="e.g. UPI-987654" style="font-size: 0.88rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="rpmNotes" style="font-size: 0.82rem; margin-bottom: 4px;">Notes / Remarks</label>
                        <input type="text" id="rpmNotes" name="notes" class="form-control" placeholder="e.g. Remaining payment" style="font-size: 0.88rem;">
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #EDF3FA;">
                    <button type="button" onclick="closeRecordPaymentModal()" class="btn btn-outline" style="border-radius: 8px; padding: 0.5rem 1rem;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 700; padding: 0.5rem 1.25rem;">
                        <i class="fa-solid fa-check" style="margin-right: 5px;"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Invoices Directory Live AJAX Filter Engine (Matches Customers Architecture) */
    /* ========================================================================= */
    (function initInvoiceAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/invoices') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const dateFromInput = document.getElementById('dateFromInput');
            const dateToInput = document.getElementById('dateToInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const dfVal = dateFromInput ? dateFromInput.value : '';
            const dtVal = dateToInput ? dateToInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (dfVal) p.set('date_from', dfVal);
            if (dtVal) p.set('date_to', dtVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, dateFromVal: dfVal, dateToVal: dtVal, perPageVal: ppVal, pageNum: page };
        }

        function filterInvoicesAjax(page = 1, options = {}) {
            const { params, searchVal, statusVal, perPageVal, pageNum } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('invoicesSection');
            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';

            fetch(targetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml'
                },
                signal: abortCtrl.signal
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newSection = doc.getElementById('invoicesSection');
                const currSection = document.getElementById('invoicesSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('invoicesPaginationWrapper');
                const currPagination = document.getElementById('invoicesPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('invoicesKpiGrid');
                const currKpi = document.getElementById('invoicesKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('invoicesMvStatusScroll');
                const currMvPills = document.getElementById('invoicesMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                const searchInput = document.getElementById('searchInput');
                const mvSearchInput = document.getElementById('mvSearchInput');
                if (searchInput && searchInput.value !== searchVal) searchInput.value = searchVal;
                if (mvSearchInput && mvSearchInput.value !== searchVal) mvSearchInput.value = searchVal;

                const perPageInput = document.getElementById('perPageInput');
                const mvPerPageSelect = document.getElementById('mvPerPageSelect');
                if (perPageInput && perPageInput.value !== perPageVal) perPageInput.value = perPageVal;
                if (mvPerPageSelect && mvPerPageSelect.value !== perPageVal) mvPerPageSelect.value = perPageVal;

                if (!options.skipPushState) {
                    if (options.isTyping) {
                        window.history.replaceState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    } else {
                        window.history.pushState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Invoice Filter AJAX Error:', err);
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        function animateKpiNumbers(container) {
            if (!container) return;
            container.querySelectorAll('.booking-kpi-val[data-count]').forEach(el => {
                const target = parseInt(el.getAttribute('data-count'), 10) || 0;
                el.textContent = target.toLocaleString();
            });
        }

        // Live Search with Debounce (Desktop)
        const sInput = document.getElementById('searchInput');
        if (sInput) {
            sInput.addEventListener('input', function() {
                const val = this.value;
                const mInput = document.getElementById('mvSearchInput');
                if (mInput && mInput.value !== val) mInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterInvoicesAjax(1, { isTyping: true }), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterInvoicesAjax(1);
                }
            });
        }

        // Live Search with Debounce (Mobile)
        const mvSInput = document.getElementById('mvSearchInput');
        if (mvSInput) {
            mvSInput.addEventListener('input', function() {
                const val = this.value;
                const dInput = document.getElementById('searchInput');
                if (dInput && dInput.value !== val) dInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterInvoicesAjax(1, { isTyping: true }), 280);
            });
            mvSInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterInvoicesAjax(1);
                }
            });
        }

        // Status Select Dropdown
        const stInput = document.getElementById('statusInput');
        if (stInput) {
            stInput.addEventListener('change', () => filterInvoicesAjax(1));
        }

        // Date Inputs
        const dfInput = document.getElementById('dateFromInput');
        if (dfInput) dfInput.addEventListener('change', () => filterInvoicesAjax(1));
        const dtInput = document.getElementById('dateToInput');
        if (dtInput) dtInput.addEventListener('change', () => filterInvoicesAjax(1));

        // Per Page Dropdowns
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) {
            ppInput.addEventListener('change', function() {
                const mvSelect = document.getElementById('mvPerPageSelect');
                if (mvSelect) mvSelect.value = this.value;
                filterInvoicesAjax(1);
            });
        }

        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) {
            mvPpSelect.addEventListener('change', function() {
                const dSelect = document.getElementById('perPageInput');
                if (dSelect) dSelect.value = this.value;
                filterInvoicesAjax(1);
            });
        }

        // Apply Button Click
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterInvoicesAjax(1);
            });
        }

        // Refresh Button Click
        const refBtn = document.getElementById('refreshBtn');
        if (refBtn) {
            refBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const s = document.getElementById('searchInput');
                const ms = document.getElementById('mvSearchInput');
                const st = document.getElementById('statusInput');
                const pp = document.getElementById('perPageInput');
                const mpp = document.getElementById('mvPerPageSelect');
                if (s) s.value = '';
                if (ms) ms.value = '';
                if (st) st.value = '';
                if (pp) pp.value = '10';
                if (mpp) mpp.value = '10';
                filterInvoicesAjax(1);
            });
        }

        // Document-level event delegation
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#invoicesKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#invoicesKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#invoicesMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterInvoicesAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                } else {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    const st = document.getElementById('statusInput');
                    const df = document.getElementById('dateFromInput');
                    const dt = document.getElementById('dateToInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                    if (st) st.value = '';
                    if (df) df.value = '';
                    if (dt) dt.value = '';
                }
                filterInvoicesAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#invoicesMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#invoicesMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#invoicesKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st);
                });

                filterInvoicesAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#invoicesPaginationWrapper .pagination a');
            if (pageLink && !pageLink.classList.contains('disabled')) {
                e.preventDefault();
                e.stopPropagation();
                const pageAttr = pageLink.getAttribute('data-page');
                let targetPage = 1;
                if (pageAttr) {
                    targetPage = parseInt(pageAttr, 10) || 1;
                } else {
                    const href = pageLink.getAttribute('href') || '';
                    const match = href.match(/page=(\d+)/);
                    if (match) targetPage = parseInt(match[1], 10) || 1;
                }
                filterInvoicesAjax(targetPage);

                const sec = document.getElementById('invoicesSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#invoicesKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterInvoicesAjax(1, { skipPushState: true });
        });

        window.filterInvoicesAjax = filterInvoicesAjax;
    })();

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
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions') && !e.target.closest('.mv-card-more-actions') && !e.target.closest('#recordPaymentModal')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
        if (e.target.id === 'recordPaymentModal') {
            closeRecordPaymentModal();
        }
    });

    let currentModalMax = 0;

    function openRecordPaymentModal(inv) {
        const modal = document.getElementById('recordPaymentModal');
        if (!modal) return;
        const total = parseFloat(inv.total_amount) || 0;
        const paid = parseFloat(inv.paid_amount) || 0;
        const remaining = Math.max(0, Math.round((total - paid) * 100) / 100);
        currentModalMax = remaining;

        document.getElementById('rpmForm').action = '<?= url('/office/invoices') ?>/' + inv.id + '/payment';
        document.getElementById('rpmInvoiceTitle').textContent = 'Invoice ' + inv.invoice_number + ' • ' + (inv.customer_name || 'Customer');
        document.getElementById('rpmTotalAmount').textContent = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('rpmPaidAmount').textContent = '₹ ' + paid.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('rpmRemainingAmount').textContent = '₹ ' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const amountInput = document.getElementById('rpmAmount');
        if (amountInput) {
            amountInput.value = remaining.toFixed(2);
            amountInput.max = remaining.toFixed(2);
        }
        updateRpmRemaining();
        modal.style.display = 'flex';
    }

    function closeRecordPaymentModal() {
        const modal = document.getElementById('recordPaymentModal');
        if (modal) modal.style.display = 'none';
    }

    function updateRpmRemaining() {
        const amountInput = document.getElementById('rpmAmount');
        const entered = parseFloat(amountInput ? amountInput.value : 0) || 0;
        const newRemaining = Math.max(0, Math.round((currentModalMax - entered) * 100) / 100);
        const remLabel = document.getElementById('rpmNewRemaining');
        const notice = document.getElementById('rpmNewRemainingNotice');
        if (remLabel) {
            remLabel.textContent = '₹ ' + newRemaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        if (notice) {
            if (entered > currentModalMax + 0.001) {
                notice.style.color = '#dc2626';
                notice.innerHTML = 'Amount cannot exceed remaining balance of ₹ ' + currentModalMax.toFixed(2);
            } else {
                notice.style.color = '#10B981';
                notice.innerHTML = 'Remaining after payment: <span id="rpmNewRemaining">₹ ' + newRemaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span>';
            }
        }
    }
</script>

<style>
/* Synchronized Executive Aesthetics matching Customers */
.booking-kpi-card {
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.booking-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(23, 107, 91, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
    border-color: rgba(23, 107, 91, 0.25);
}
.booking-kpi-card.is-selected {
    border-color: #176B5B;
    background: linear-gradient(180deg, #FFFFFF 0%, #E8F7F0 100%);
    box-shadow: 0 4px 18px rgba(23, 107, 91, 0.18);
}
.booking-kpi-sub {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.kpi-filter-tag {
    font-size: 0.72rem;
    color: #94A3B8;
    transition: transform 0.2s ease, color 0.2s ease;
}
.booking-kpi-card:hover .kpi-filter-tag,
.booking-kpi-card.is-selected .kpi-filter-tag {
    color: #176B5B;
    transform: translateX(3px);
}
.recent-bookings-section.is-filtering {
    opacity: 0.6;
    pointer-events: none;
    transition: opacity 0.15s ease;
}
.fbc-search {
    position: relative;
    display: flex;
    align-items: center;
}
.fbc-search-clear, .mv-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    line-height: 1;
}
.fbc-search-clear:hover, .mv-search-clear:hover {
    color: #0F172A;
}

/* ========================================================================= */
/* Floating Action Button & Popup Menu Styling (PMS Standard)                */
/* ========================================================================= */
.table-action-buttons {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.table-action-buttons .action-toggle-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 0;
}

.table-action-buttons .action-toggle-btn:hover {
    background: #eef2f6;
    color: #0f172a;
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.22);
    border-color: rgba(23, 107, 91, 0.3);
}

.table-action-buttons .action-btns-popup {
    display: flex;
    position: absolute;
    top: 50%;
    right: calc(100% + 8px);
    left: auto;
    transform: translateY(-50%) scale(0.75);
    transform-origin: right center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 6px 10px;
    gap: 6px;
    align-items: center;
    z-index: 9999;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.table-action-buttons .action-btns-popup.open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(-50%) scale(1);
}

.action-btn-table.pay {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.action-btn-table.pay:hover {
    background-color: #10b981 !important;
    color: #ffffff !important;
    border-color: #059669;
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
}

/* ========================================================================= */
/* Mobile Invoice Cards Clean Footer Action Strip                            */
/* ========================================================================= */
#invoicesCards .mv-booking-card-wrapper {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: visible;
}

#invoicesCards .mv-booking-card {
    border: none;
    box-shadow: none;
    border-radius: 16px 16px 0 0;
    padding-right: 14px;
    padding-bottom: 10px;
    background: transparent;
}

#invoicesCards .mv-card-actions-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 14px 10px 14px;
    background: #fafcfa;
    border-top: 1px dashed #e2e8f0;
    border-radius: 0 0 16px 16px;
}

#invoicesCards .mv-card-actions-bar-left {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: #64748b;
    min-width: 0;
    overflow: hidden;
}

#invoicesCards .mv-card-ref-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    color: var(--brand-primary, #176B5B);
    background: #e8f7f0;
    padding: 3px 8px;
    border-radius: 6px;
    text-decoration: none;
    white-space: nowrap;
}

#invoicesCards .mv-card-ref-link:hover {
    background: #d1fae5;
}

#invoicesCards .mv-card-ref-text {
    font-size: 11px;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

#invoicesCards .mv-card-actions-bar-right {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

#invoicesCards .mv-card-actions-bar-right .action-btn-table,
#invoicesCards .mv-card-actions-bar-right .action-toggle-btn {
    width: 34px;
    height: 34px;
    min-width: 34px;
    min-height: 34px;
    border-radius: 8px;
    font-size: 13px;
    margin: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#invoicesCards .mv-card-actions-bar-right .action-toggle-btn {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

#invoicesCards .mv-card-actions-bar-right .action-toggle-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

#invoicesCards .mv-card-more-actions {
    position: relative;
    display: inline-flex;
    align-items: center;
}

#invoicesCards .mv-card-more-actions .card-btns-popup {
    display: flex;
    position: absolute;
    bottom: calc(100% + 8px);
    right: 0;
    top: auto;
    transform: scale(0.75);
    transform-origin: bottom right;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 6px 10px;
    gap: 6px;
    align-items: center;
    z-index: 9999;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

#invoicesCards .mv-card-more-actions .card-btns-popup.open {
    opacity: 1;
    pointer-events: auto;
    transform: scale(1);
}
</style>
