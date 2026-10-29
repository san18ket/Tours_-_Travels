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

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Interactive like Customers) -->
    <div class="booking-kpi-grid" id="tripsKpiGrid">
        <!-- Card 1: Total Dispatches -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all trip dispatches">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Dispatches</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totalRecords ?? count($trips)) ?>"><?= number_format($totalAll ?? $totalRecords ?? count($trips)) ?></div>
            <div class="booking-kpi-sub">
                <span>All recorded dispatches</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active Deployments -->
        <div class="booking-kpi-card <?= in_array($selectedStatus ?? '', ['assigned', 'driver_accepted', 'trip_started']) ? 'is-selected' : '' ?>" data-status="assigned" role="button" tabindex="0" title="Click to filter active deployments">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Deployments</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalActive ?? 0) ?>"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Assigned &amp; en-route</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Completed Trips -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'completed' ? 'is-selected' : '' ?>" data-status="completed" role="button" tabindex="0" title="Click to filter completed trips">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Completed Trips</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalCompleted ?? 0) ?>"><?= number_format($totalCompleted ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Closed with verified odometer</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Cancelled Trips -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'cancelled' ? 'is-selected' : '' ?>" data-status="cancelled" role="button" tabindex="0" title="Click to filter cancelled dispatches">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cancelled Trips</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalCancelled ?? 0) ?>"><?= number_format($totalCancelled ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Aborted or reassigned</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Customers Architecture) -->
    <form method="GET" action="<?= url('/office/trips/history') ?>" class="filter-bar-compact" id="tripFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search dispatch code, booking code, plate, chauffeur..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="assigned" <?= ($selectedStatus ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned (Pending Driver)</option>
            <option value="driver_accepted" <?= ($selectedStatus ?? '') === 'driver_accepted' ? 'selected' : '' ?>>Driver Accepted</option>
            <option value="trip_started" <?= ($selectedStatus ?? '') === 'trip_started' ? 'selected' : '' ?>>In Transit / Started</option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
            <?php if (!empty($search) || !empty($selectedStatus) || !empty($fromDate) || !empty($toDate)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Trip Operations</h2>
            </div>
            <a href="<?= url('/office/trips/assign') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Assign Trip</span>
            </a>
        </div>

        <!-- Search Bar + Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/trips/history') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search dispatch, driver, vehicle..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Filter Panel (Mobile Drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 25, 50, 100] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Filter Pills -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $fromDate, $toDate, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($fromDate)) $p['from_date'] = $fromDate;
                if (!empty($toDate)) $p['to_date'] = $toDate;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/trips/history' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="tripsMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalRecords ?? count($trips)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('assigned') ?>" class="mv-pill <?= in_array($currStatus, ['assigned', 'driver_accepted', 'trip_started']) ? 'active' : '' ?>" data-status="assigned" data-no-ajax="true">
                <span>Active</span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('completed') ?>" class="mv-pill <?= $currStatus === 'completed' ? 'active' : '' ?>" data-status="completed" data-no-ajax="true">
                <span>Completed</span>
                <span class="mv-pill-cnt"><?= (int)($totalCompleted ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('cancelled') ?>" class="mv-pill <?= $currStatus === 'cancelled' ? 'active' : '' ?>" data-status="cancelled" data-no-ajax="true">
                <span>Cancelled</span>
                <span class="mv-pill-cnt"><?= (int)($totalCancelled ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Trips Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="tripsSection">
        <div class="section-header">
            <h3 class="section-title">All Trip Dispatches</h3>
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
                        <th style="min-width: 170px;">Dispatch &amp; Booking</th>
                        <th style="min-width: 200px;">Passenger / Client</th>
                        <th style="min-width: 180px;">Chauffeur &amp; Phone</th>
                        <th style="min-width: 160px;">Assigned Vehicle</th>
                        <th class="text-center" style="min-width: 150px;">Odometer Start / End</th>
                        <th class="text-center" style="min-width: 120px;">Status</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
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
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:ui-monospace, monospace;letter-spacing:0.02em;font-weight:700;color:var(--brand-primary, #176B5B);">
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
                                    <span class="client-name" style="font-weight:700;color:#0F172A;"><?= e($d['customer_name'] ?? 'Guest Traveler') ?></span>
                                    <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                        <?= format_date($d['start_date']) ?> &rarr; <?= format_date($d['end_date']) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;">
                                <?= e($d['driver_name']) ?>
                            </div>
                            <?php if (!empty($d['driver_phone'])): ?>
                                <small class="text-muted d-block" style="font-size:12px;margin-top:2px;">
                                    <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($d['driver_phone']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0F172A; font-size: 13px;">
                                <?= e($d['make_model']) ?>
                            </div>
                            <small style="font-family:ui-monospace, monospace;color:var(--brand-primary, #176B5B);font-weight:600;font-size:11px;">
                                <?= e($d['vehicle_number']) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;color:#0F172A;font-size:13px;">
                                <?= $d['start_odometer'] ? number_format((int)$d['start_odometer']) . ' km' : '—' ?>
                            </div>
                            <small class="text-muted" style="font-size:11px;">
                                End: <?= $d['end_odometer'] ? number_format((int)$d['end_odometer']) . ' km' : 'In transit' ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="tripToggleWrap-<?= (int)$d['id'] ?>">
                                <button type="button" 
                                        class="cust-status-toggle <?= in_array($status, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started']) ? 'is-active' : '' ?> <?= $status === 'completed' ? 'is-completed' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= in_array($status, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started']) ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$d['id'] ?>"
                                        data-code="<?= addslashes(e($d['dispatch_code'])) ?>"
                                        onclick="toggleTripStatus(<?= (int)$d['id'] ?>, this, event)"
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
                        <td class="text-center">
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

        <!-- Mobile Touch Cards List -->
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
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar" style="background:#E8F7F0;color:#176B5B;">
                            <i class="fa-solid fa-route" style="font-size:22px;"></i>
                        </div>
                    </div>
                    <div class="mv-details">
                        <div class="mv-code"><?= e($d['dispatch_code']) ?> &bull; #<?= e($d['booking_code']) ?></div>
                        <div class="mv-tour-title" title="<?= e($d['customer_name'] ?? 'Guest') ?>">
                            <?= e($d['customer_name'] ?? 'Guest Traveler') ?>
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
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.stopPropagation()">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= in_array($status, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started']) ? 'is-active' : '' ?> <?= $status === 'completed' ? 'is-completed' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= in_array($status, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started']) ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$d['id'] ?>"
                                    data-code="<?= addslashes(e($d['dispatch_code'])) ?>"
                                    onclick="toggleTripStatus(<?= (int)$d['id'] ?>, this, event)"
                                    title="Toggle status">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb"></span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price" style="font-size: 11px; color: #16443B; font-weight: 700; margin-top: 4px;">
                            #<?= e($d['booking_code']) ?>
                        </div>
                    </div>
                </a>
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

    <!-- Modern Pagination (Exact Customers Architecture) -->
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
    <div id="tripsPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> trip(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page" data-page="<?= max(1, $currP - 1) ?>" data-no-ajax="true">&#10094;</a>
                <?php
                    $rangeStart = max(1, $currP - 1);
                    $rangeEnd   = min($totalPages, $rangeStart + 2);
                    if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                        $rangeStart = max(1, $rangeEnd - 2);
                    }
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                ?>
                    <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>" data-page="<?= $i ?>" data-no-ajax="true"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($rangeEnd < $totalPages): ?>
                    <span class="dots">...</span>
                    <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>" data-page="<?= $totalPages ?>" data-no-ajax="true"><?= $totalPages ?></a>
                <?php endif; ?>
                <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page" data-page="<?= min($totalPages, $currP + 1) ?>" data-no-ajax="true">&#10095;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Trips History Live AJAX Filter Engine (Matches Customers Model)           */
    /* ========================================================================= */
    (function initTripAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/trips/history') ?>';

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

        function filterTripsAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('tripsSection');
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

                const newSection = doc.getElementById('tripsSection');
                const currSection = document.getElementById('tripsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('tripsPaginationWrapper');
                const currPagination = document.getElementById('tripsPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('tripsKpiGrid');
                const currKpi = document.getElementById('tripsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('tripsMvStatusScroll');
                const currMvPills = document.getElementById('tripsMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                bindDynamicEvents();
                try {
                    window.history.pushState({ page: page, url: targetUrl }, '', targetUrl);
                } catch(e) {}
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Trips AJAX filter error:', err);
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

        // Search inputs with debounce
        const sInput = document.getElementById('searchInput');
        if (sInput) {
            sInput.addEventListener('input', function() {
                const val = this.value;
                const mInput = document.getElementById('mvSearchInput');
                if (mInput && mInput.value !== val) mInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterTripsAjax(1), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterTripsAjax(1);
                }
            });
        }

        const mInput = document.getElementById('mvSearchInput');
        if (mInput) {
            mInput.addEventListener('input', function() {
                const val = this.value;
                if (sInput && sInput.value !== val) sInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterTripsAjax(1), 280);
            });
            mInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterTripsAjax(1);
                }
            });
        }

        // Clear buttons
        function clearSearch() {
            if (sInput) sInput.value = '';
            if (mInput) mInput.value = '';
            filterTripsAjax(1);
        }
        const sClear = document.getElementById('searchClearBtn');
        if (sClear) sClear.addEventListener('click', clearSearch);
        const mClear = document.getElementById('mvSearchClearBtn');
        if (mClear) mClear.addEventListener('click', clearSearch);

        // Dropdowns
        const stInput = document.getElementById('statusInput');
        if (stInput) stInput.addEventListener('change', () => filterTripsAjax(1));
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) ppInput.addEventListener('change', () => filterTripsAjax(1));
        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) mvPpSelect.addEventListener('change', () => {
            if (ppInput) ppInput.value = mvPpSelect.value;
            filterTripsAjax(1);
        });

        // Apply & Refresh
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) applyBtn.addEventListener('click', () => filterTripsAjax(1));
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterTripsAjax(1);
        });

        // Live Dynamic KPI Cards and Mobile Pills Syncer (AJAX Real-Time Update)
        function updateTripMetrics(metrics) {
            if (!metrics) return;
            const mapping = {
                '': metrics.total,
                'assigned': metrics.active,
                'completed': metrics.completed,
                'cancelled': metrics.cancelled
            };
            const grid = document.getElementById('tripsKpiGrid');
            if (grid) {
                Object.keys(mapping).forEach(st => {
                    const card = grid.querySelector(`.booking-kpi-card[data-status="${st}"]`);
                    if (card) {
                        const valEl = card.querySelector('.booking-kpi-val');
                        if (valEl && mapping[st] !== undefined) {
                            valEl.setAttribute('data-count', mapping[st]);
                        }
                    }
                });
                animateKpiNumbers(grid);
            }
            const scrollWrap = document.getElementById('tripsMvStatusScroll');
            if (scrollWrap) {
                Object.keys(mapping).forEach(st => {
                    const pill = scrollWrap.querySelector(`.mv-pill[data-status="${st}"]`);
                    if (pill) {
                        const cntEl = pill.querySelector('.mv-pill-cnt');
                        if (cntEl && mapping[st] !== undefined) {
                            cntEl.textContent = (parseInt(mapping[st], 10) || 0).toLocaleString();
                        }
                    }
                });
            }
            const stInput = document.getElementById('statusInput');
            const currentFilter = (stInput ? stInput.value : '').trim();
            const totalPill = document.getElementById('totalRecordsPill');
            if (totalPill) {
                let relevantCount = metrics.total;
                if (currentFilter && mapping[currentFilter] !== undefined) {
                    relevantCount = mapping[currentFilter];
                }
                totalPill.textContent = (parseInt(relevantCount, 10) || 0).toLocaleString();
            }
        }

        // Event delegation for dynamic clicks (KPI cards, Pills, Clear, Pagination)
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#tripsKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#tripsKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#tripsMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterTripsAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                } else {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                    if (stInput) stInput.value = '';
                }
                filterTripsAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#tripsMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#tripsMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#tripsKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st);
                });

                filterTripsAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#tripsPaginationWrapper .pagination a');
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
                filterTripsAjax(targetPage);

                const sec = document.getElementById('tripsSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#tripsKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterTripsAjax(1);
        });

        window.updateTripMetrics = updateTripMetrics;
        window.filterTripsAjax = filterTripsAjax;
    })();

    // Action popups
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

    function toggleTripStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        if (btn.classList.contains('is-completed')) {
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            alert('Completed journeys have finalized odometer & trip manifests and cannot be modified.');
            return;
        }

        if (!btn.classList.contains('is-active')) {
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            alert('This trip assignment has already been cancelled.');
            return;
        }

        if (!confirm('Are you sure you want to abort/cancel this active dispatch? This will return the vehicle and chauffeur back to the available pool.')) {
            return;
        }

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/trips/') ?>' + id + '/toggle-status';
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
                'csrf_token': csrfToken
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(j => { throw new Error(j.message || ('HTTP ' + res.status)); });
            }
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                // Update all matching toggle buttons (table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-checked', 'false');
                    b.title = 'Click to toggle status (currently Cancelled)';
                });

                // Update table status pill & dot
                const wrap = document.getElementById('tripToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    if (pill) pill.className = 'cust-toggle-pill cancelled';
                    if (dot) dot.className = 'toggle-pulse-dot cancelled';
                    if (text) text.textContent = data.label;
                }

                // Update mobile cards
                document.querySelectorAll('.mv-booking-card-wrapper').forEach(card => {
                    const mBtn = card.querySelector(`.cust-status-toggle[data-id="${id}"]`);
                    if (mBtn) {
                        const mText = card.querySelector('.mv-toggle-label-text');
                        if (mText) {
                            mText.className = 'mv-toggle-label-text cancelled';
                            mText.textContent = data.label;
                        }
                    }
                });

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateTripMetrics === 'function') {
                    window.updateTripMetrics(data.metrics);
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            alert(err.message || 'Error updating trip status');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });
</script>

<style>
/* Status Toggle Switch (Executive Standards matching Customers) */
.cust-toggle-cell {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
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
.cust-status-toggle.is-completed .toggle-slider-track {
    background: #059669;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35);
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
.cust-status-toggle.is-active .toggle-slider-thumb,
.cust-status-toggle.is-completed .toggle-slider-thumb {
    transform: translateX(20px);
}
.cust-status-toggle:active:not(.is-loading) .toggle-slider-thumb {
    width: 21px;
}
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
.cust-status-toggle.sm.is-active .toggle-slider-thumb,
.cust-status-toggle.sm.is-completed .toggle-slider-thumb {
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
    background: #DCFCE7;
    color: #15803D;
}
.cust-toggle-pill.in-progress, .cust-toggle-pill.assigned {
    background: #DBEAFE;
    color: #16443B;
}
.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}
.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.toggle-pulse-dot.completed {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}
.toggle-pulse-dot.in-progress, .toggle-pulse-dot.assigned {
    background: #218765;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}
.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}
.mv-status-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.mv-toggle-label-text {
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: capitalize;
}
.mv-toggle-label-text.completed {
    color: #10B981;
}
.mv-toggle-label-text.in-progress, .mv-toggle-label-text.assigned {
    color: #176B5B;
}
.mv-toggle-label-text.cancelled {
    color: #64748B;
}
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
}
.fbc-search-clear:hover, .mv-search-clear:hover {
    color: #EF4444;
}
.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 3px 8px;
    display: inline-block;
    font-size: 0.82rem;
}
</style>
