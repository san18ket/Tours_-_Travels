<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Trip Operations & Deployment History',
            'description' => 'Comprehensive audit log of commercial dispatches, chauffeur duty states, and odometer verifications.',
            'image'       => 'header-dispatch.jpg',
            'badge'       => 'Dispatch Operations',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Trip Operations', 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/trips/assign') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>Assign Trip to Driver</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Dispatches -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Dispatches</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalRecords ?? count($trips)) ?></div>
            <div class="booking-kpi-sub">
                <span>All dispatch logs recorded</span>
            </div>
        </div>

        <!-- Card 2: Active Deployments -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Deployments</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Assigned &amp; en-route</span>
            </div>
        </div>

        <!-- Card 3: Completed Trips -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Completed Trips</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalCompleted ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Closed with verified odo</span>
            </div>
        </div>

        <!-- Card 4: Cancelled Trips -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cancelled Trips</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalCancelled ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Aborted or reassigned</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/trips/history') ?>" class="filter-bar-compact" id="tripFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search dispatch code, booking code, plate, chauffeur..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="status" id="statusInput" onchange="document.getElementById('tripFilterForm').submit()">
            <option value="">All Statuses</option>
            <option value="assigned" <?= ($selectedStatus ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned (Pending Driver)</option>
            <option value="driver_accepted" <?= ($selectedStatus ?? '') === 'driver_accepted' ? 'selected' : '' ?>>Driver Accepted</option>
            <option value="trip_started" <?= ($selectedStatus ?? '') === 'trip_started' ? 'selected' : '' ?>>In Transit / Started</option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('tripFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/trips/history') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedStatus) || !empty($fromDate) || !empty($toDate)): ?>
                <a href="<?= url('/office/trips/history') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span>Clear</span></a>
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
                <h2 class="mv-page-title">Trip History</h2>
            </div>
            <a href="<?= url('/office/trips/assign') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Assign Trip</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/trips/history') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search dispatch, driver..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/trips/history' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
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
                <form method="GET" action="<?= url('/office/trips/history') ?>">
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
                return url('/office/trips/history' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalRecords ?? count($trips)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('trip_started') ?>" class="mv-pill <?= in_array($currStatus, ['assigned', 'driver_accepted', 'trip_started']) ? 'active' : '' ?>">
                <span>Active</span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('completed') ?>" class="mv-pill <?= $currStatus === 'completed' ? 'active' : '' ?>">
                <span>Completed</span>
                <span class="mv-pill-cnt"><?= (int)($totalCompleted ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('cancelled') ?>" class="mv-pill <?= $currStatus === 'cancelled' ? 'active' : '' ?>">
                <span>Cancelled</span>
            </a>
        </div>
    </div>

    <!-- 5. All Trips Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">All Trip Deployments</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($trips)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="tripsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 20%;">Dispatch &amp; Booking</th>
                        <th style="width: 20%;">Passenger / Client</th>
                        <th style="width: 18%;">Chauffeur &amp; Phone</th>
                        <th style="width: 16%;">Assigned Vehicle</th>
                        <th class="text-center" style="width: 14%;">Odometer Start/End</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tripsTableBody">
                    <?php foreach ($trips as $i => $d):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($d['status'] ?? 'assigned');
                        $statusClass = match($status) {
                            'completed' => 'completed',
                            'trip_started', 'driver_accepted', 'en_route_pickup' => 'in-progress',
                            'assigned' => 'assigned',
                            'cancelled' => 'cancelled',
                            default => 'pending'
                        };
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:monospace;letter-spacing:-0.01em;">
                                <?= e($d['dispatch_code']) ?>
                            </div>
                            <div style="margin-top: 3px;">
                                <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" style="font-size:12px;font-weight:700;color:var(--brand-primary, #176B5B);text-decoration:none;">
                                    #<?= e($d['booking_code']) ?>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="client-cell">
                                <div class="client-info">
                                    <span class="client-name"><?= e($d['customer_name'] ?? 'Guest') ?></span>
                                    <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                        <?= format_date($d['start_date']) ?> &rarr; <?= format_date($d['end_date']) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 13.5px;">
                                <?= e($d['driver_name']) ?>
                            </div>
                            <?php if (!empty($d['driver_phone'])): ?>
                                <small class="text-muted d-block" style="font-size:12px;margin-top:2px;">
                                    <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($d['driver_phone']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                                <?= e($d['make_model']) ?>
                            </div>
                            <small style="font-family:monospace;color:var(--brand-primary, #176B5B);font-weight:600;font-size:11px;">
                                <?= e($d['vehicle_number']) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                <?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' km' : '—' ?>
                            </div>
                            <small class="text-muted" style="font-size:11px;">
                                End: <?= $d['end_odometer'] ? number_format((int)$d['end_odometer']) . ' km' : 'In transit' ?>
                            </small>
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
                                    <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="action-btn-table view" title="View Reservation Manifest">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?= url('/office/trips/assign?booking_id=' . $d['booking_id']) ?>" class="action-btn-table dispatch" title="Reassign Dispatch">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($trips)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trip deployments recorded</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="tripsCards">
            <?php foreach ($trips as $d):
                $status = strtolower($d['status'] ?? 'assigned');
                $statusClass = match($status) {
                    'completed' => 'completed',
                    'trip_started', 'driver_accepted' => 'confirmed',
                    'assigned' => 'assigned',
                    'cancelled' => 'cancelled',
                    default => 'pending'
                };
                $statusText = ucfirst(str_replace('_', ' ', $status));
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="mv-booking-card">
                    <!-- Left: Trip Monogram Icon -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <i class="fa-solid fa-route" style="font-size:22px;"></i>
                        </div>
                    </div>

                    <!-- Middle: Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($d['dispatch_code']) ?> &bull; #<?= e($d['booking_code']) ?></div>
                        <div class="mv-tour-title" title="<?= e($d['customer_name'] ?? 'Guest') ?>">
                            <?= e($d['customer_name'] ?? 'Guest') ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-user-tie"></i>
                            <span><?= e($d['driver_name']) ?> &bull; <?= e($d['driver_phone']) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-car"></i>
                            <span><?= e($d['make_model']) ?> (<?= e($d['vehicle_number']) ?>)</span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span><?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' km' : '—' ?> &rarr; <?= $d['end_odometer'] ? number_format((int)$d['end_odometer']) . ' km' : 'Active' ?></span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Booking code bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price" style="font-size: 11px; color: #16443B; font-weight: 700;">
                            #<?= e($d['booking_code']) ?>
                        </div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/bookings/' . $d['booking_id']) ?>" class="action-btn-table view" title="View Booking">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="<?= url('/office/trips/assign?booking_id=' . $d['booking_id']) ?>" class="action-btn-table dispatch" title="Reassign">
                            <i class="fa-solid fa-paper-plane"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($trips)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trip deployments found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalRecords ?? count($trips);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $fromDate, $toDate, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($fromDate)) $q['from_date'] = $fromDate;
            if (!empty($toDate)) $q['to_date'] = $toDate;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/trips/history?' . http_build_query($q));
        };
    ?>
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> trip(s)</div>
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
