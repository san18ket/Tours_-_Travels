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
    <div class="booking-kpi-grid">
        <!-- Card 1: Invoiced Volume -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.invoiced_volume', 'Invoiced Volume')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= format_currency($totalInvoiced ?? 0.0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= number_format($totalAll ?? count($invoices)) ?> total invoices issued</span>
            </div>
        </div>

        <!-- Card 2: Collections Received -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.collections_received', 'Collections Received')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" style="color:#10b981;"><?= format_currency($totalPaid ?? 0.0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Verified ledger credits</span>
            </div>
        </div>

        <!-- Card 3: Outstanding Receivables -->
        <div class="booking-kpi-card">
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
                <span>Pending client reconciliation</span>
            </div>
        </div>

        <!-- Card 4: Settled Invoices -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Paid In Full</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($paidCount ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= number_format($unpaidCount ?? 0) ?> awaiting full payment</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/invoices') ?>" class="filter-bar-compact" id="invoiceFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by invoice #, booking code, client name, email..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="status" id="statusInput" onchange="document.getElementById('invoiceFilterForm').submit()">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="paid" <?= ($selectedStatus ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="partial" <?= ($selectedStatus ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
            <option value="unpaid" <?= ($selectedStatus ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('invoiceFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/invoices') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/invoices') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span>Clear</span></a>
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
            <form method="GET" action="<?= url('/office/invoices') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search invoice, client..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/invoices' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($selectedStatus)): ?>
                        <input type="hidden" name="status" value="<?= e($selectedStatus) ?>">
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/invoices') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($selectedStatus)): ?>
                        <input type="hidden" name="status" value="<?= e($selectedStatus) ?>">
                    <?php endif; ?>
                </form>
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
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? count($invoices)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('paid') ?>" class="mv-pill <?= $currStatus === 'paid' ? 'active' : '' ?>">
                <span>Paid</span>
                <span class="mv-pill-cnt"><?= (int)($paidCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('unpaid') ?>" class="mv-pill <?= $currStatus === 'unpaid' ? 'active' : '' ?>">
                <span>Unpaid</span>
                <span class="mv-pill-cnt"><?= (int)($unpaidCount ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('partial') ?>" class="mv-pill <?= $currStatus === 'partial' ? 'active' : '' ?>">
                <span>Partial</span>
            </a>
        </div>
    </div>

    <!-- 5. All Invoices Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
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
                            <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" style="font-weight:700;color:var(--brand-primary, #176B5B);text-decoration:none;">
                                #<?= e($inv['booking_code']) ?>
                            </a>
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
                                    <a href="<?= url('/office/bookings/' . $inv['booking_id']) ?>" class="action-btn-table edit" title="View Linked Booking">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </a>
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
                            <span>Booking: #<?= e($inv['booking_code']) ?></span>
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
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> invoice(s)</div>
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

<script>
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
