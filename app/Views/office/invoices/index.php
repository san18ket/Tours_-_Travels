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
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
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
                        <th style="width: 18%;">Invoice & Issued Date</th>
                        <th style="width: 15%;">Booking Manifest</th>
                        <th style="width: 22%;">Client Name & Account</th>
                        <th class="text-center" style="width: 14%;">Total / Paid</th>
                        <th class="text-center" style="width: 12%;">Balance Due</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
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
                                <small class="text-muted" style="font-size:11px;">
                                    Issued <?= format_date($inv['issued_at']) ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($inv['booking_id']) && !empty($inv['booking_code'])): ?>
                                <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" style="font-weight:700;color:var(--brand-primary, #176B5B);text-decoration:none;">
                                    #<?= e($inv['booking_code']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px; color:#64748B;">—</span>
                            <?php endif; ?>
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
                        <td>
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-btn-table view" title="View Full Invoice">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="action-btn-table dispatch" title="Print Tax Invoice">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
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
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No invoices found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
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

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/invoices/' . $inv['id']) ?>" class="action-btn-table view" title="View Invoice">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="<?= url('/office/invoices/' . $inv['id'] . '/print') ?>" target="_blank" class="action-btn-table dispatch" title="Print Invoice">
                            <i class="fa-solid fa-print"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($invoices)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No invoices found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
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
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
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
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                    if (st) st.value = '';
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
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });
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
</style>
