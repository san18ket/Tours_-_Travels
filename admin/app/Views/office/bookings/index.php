<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved Exactly on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('sidebar.bookings', 'Reservation Bookings'),
            'description' => __('nav.bookings', 'Manage luxury tour reservations, passenger manifests, route itineraries, and dispatch assignments.'),
            'badge'       => 'Live Manifests',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.bookings', 'Bookings'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/bookings/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.create_booking', 'Create Booking')) . '</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Interactive like Customers) -->
    <?php
        $selectedStatus   = $selectedStatus ?? ($status ?? '');
        $selectedCategory = $selectedCategory ?? ($trip_category_id ?? '');
        $tripCategories   = $tripCategories ?? [];
        $search           = $search ?? '';
        $sort             = $sort ?? 'created_desc';
        $perPage          = $perPage ?? 10;
        $totalCount       = $stats['total'] ?? count($bookings);
        $activeCount      = $stats['active'] ?? 0;
        $pendingCount     = $stats['pending'] ?? 0;
        $completedCount   = $stats['completed'] ?? 0;
        $cancelledCount   = $stats['cancelled'] ?? 0;
    ?>
    <div class="booking-kpi-grid" id="bookingsKpiGrid">
        <!-- Card 1: Total Reservations -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="<?= e(__('tooltip.click_to_view_all_bookings', 'Click to view all bookings')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('table.total_bookings', 'Total Bookings')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)$totalCount ?>"><?= number_format($totalCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All recorded manifests')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active & Transit -->
        <div class="booking-kpi-card <?= in_array($selectedStatus ?? '', ['active', 'confirmed']) ? 'is-selected' : '' ?>" data-status="active" role="button" tabindex="0" title="<?= e(__('tooltip.click_to_filter_active_transit', 'Click to filter active & transit bookings')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.active', 'Active & Transit')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)$activeCount ?>"><?= number_format($activeCount) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.in_progress', 'Confirmed & transit')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Pending Dispatch -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'pending' ? 'is-selected' : '' ?>" data-status="pending" role="button" tabindex="0" title="<?= e(__('tooltip.click_to_filter_pending_reserv', 'Click to filter pending reservations')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.pending_dispatch', 'Pending Dispatch')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)$pendingCount ?>"><?= number_format($pendingCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.awaiting_driver', 'Needs fleet assignment')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Completed Journeys -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'completed' ? 'is-selected' : '' ?>" data-status="completed" role="button" tabindex="0" title="<?= e(__('tooltip.click_to_filter_completed_tour', 'Click to filter completed tours')) ?>">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.completed_tours', 'Completed Tours')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)$completedCount ?>"><?= number_format($completedCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.successfully_closed', 'Closed itineraries')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Customers Architecture) -->
    <form method="GET" action="<?= url('/office/bookings') ?>" class="filter-bar-compact" id="bookingFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="<?= e(__('placeholder.search_bookings_by_ref_custome', 'Search bookings by ref, customer, phone, tour...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="<?= e(__('common.clear', 'Clear search')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>><?= e(__('filter.all_status', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>><?= e(__('status.active_all', 'Active & Transit')) ?></option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>><?= e(__('status.pending', 'Pending')) ?></option>
            <option value="confirmed" <?= ($selectedStatus ?? '') === 'confirmed' ? 'selected' : '' ?>><?= e(__('status.confirmed', 'Confirmed')) ?></option>
            <option value="assigned" <?= ($selectedStatus ?? '') === 'assigned' ? 'selected' : '' ?>><?= e(__('status.assigned', 'Assigned')) ?></option>
            <option value="in_progress" <?= ($selectedStatus ?? '') === 'in_progress' ? 'selected' : '' ?>><?= e(__('status.in_progress', 'In Progress')) ?></option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>><?= e(__('status.completed', 'Completed')) ?></option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>><?= e(__('status.cancelled', 'Cancelled')) ?></option>
        </select>

        <select name="trip_category_id" id="tripCategoryInput" title="<?= e(__('tooltip.filter_by_trip_category', 'Filter by Trip Category')) ?>">
            <option value="" <?= empty($selectedCategory) ? 'selected' : '' ?>>All Categories</option>
            <?php if (!empty($tripCategories)): ?>
                <?php foreach ($tripCategories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= ((int)($selectedCategory ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <select name="sort" id="sortInput">
            <option value="created_desc" <?= ($sort ?? 'created_desc') === 'created_desc' ? 'selected' : '' ?>><?= e(__('filter.newest_first', 'Newest First')) ?></option>
            <option value="created_asc"  <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>><?= e(__('filter.oldest_first', 'Oldest First')) ?></option>
            <option value="client_asc"   <?= ($sort ?? '') === 'client_asc' ? 'selected' : '' ?>><?= e(__('filter.customer_az', 'Customer A → Z')) ?></option>
            <option value="client_desc"  <?= ($sort ?? '') === 'client_desc' ? 'selected' : '' ?>><?= e(__('filter.customer_za', 'Customer Z → A')) ?></option>
            <option value="date_asc"     <?= ($sort ?? '') === 'date_asc' ? 'selected' : '' ?>><?= e(__('filter.travel_date_asc', 'Travel Date ↑')) ?></option>
            <option value="date_desc"    <?= ($sort ?? '') === 'date_desc' ? 'selected' : '' ?>><?= e(__('filter.travel_date_desc', 'Travel Date ↓')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="<?= e(__('common.records_per_page', 'Records per page')) ?>">
            <?php foreach ([5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="refreshBtn" title="<?= e(__('btn.refresh', 'Refresh')) ?>" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus) || !empty($selectedCategory) || (!empty($sort) && $sort !== 'created_desc')): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="<?= e(__('common.reset_filters', 'Clear Filters')) ?>" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="<?= e(__('btn.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title"><?= e(__('sidebar.bookings', 'Bookings')) ?></h2>
            </div>
            <a href="<?= url('/office/bookings/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span><?= e(__('dashboard.act_new_booking', 'New Booking')) ?></span>
            </a>
        </div>

        <!-- Search Bar + Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/bookings') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="<?= e(__('placeholder.search_by_name_ref_tour', 'Search by name, ref, tour...')) ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="<?= e(__('common.clear', 'Clear search')) ?>">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="<?= e(__('common.filter', 'Filter options')) ?>">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Filter Panel (Mobile Drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvCategorySelect"><?= e(__('trip_category.category_singular', 'Trip Category')) ?>:</label>
                    <select name="trip_category_id" id="mvCategorySelect">
                        <option value="" <?= empty($selectedCategory) ? 'selected' : '' ?>><?= e(__('status.all', 'All Categories')) ?></option>
                        <?php if (!empty($tripCategories)): ?>
                            <?php foreach ($tripCategories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= ((int)($selectedCategory ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mv-filter-row">
                    <label for="mvSortSelect"><?= e(__('booking.sort_bookings', 'Sort Bookings:')) ?></label>
                    <select name="sort" id="mvSortSelect">
                        <option value="created_desc" <?= ($sort ?? 'created_desc') === 'created_desc' ? 'selected' : '' ?>><?= e(__('booking.newest_first', 'Newest First')) ?></option>
                        <option value="created_asc"  <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>><?= e(__('booking.oldest_first', 'Oldest First')) ?></option>
                        <option value="date_asc"     <?= ($sort ?? '') === 'date_asc' ? 'selected' : '' ?>><?= e(__('booking.travel_date_asc', 'Travel Date ↑')) ?></option>
                        <option value="date_desc"    <?= ($sort ?? '') === 'date_desc' ? 'selected' : '' ?>><?= e(__('booking.travel_date_desc', 'Travel Date ↓')) ?></option>
                    </select>
                </div>
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect"><?= e(__('common.per_page_label', 'Per Page:')) ?></label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> <?= e(__('common.per_page', 'per page')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Filter Pills -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $sort, $perPage, $selectedCategory) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($selectedCategory)) $p['trip_category_id'] = $selectedCategory;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($sort) && $sort !== 'created_desc') $p['sort'] = $sort;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/bookings' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="bookingsMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span><?= e(__('common.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)$totalCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= in_array($currStatus, ['active', 'confirmed', 'in_progress', 'assigned']) ? 'active' : '' ?>" data-status="active" data-no-ajax="true">
                <span><?= e(__('status.active', 'Active')) ?></span>
                <span class="mv-pill-cnt"><?= (int)$activeCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('pending') ?>" class="mv-pill <?= $currStatus === 'pending' ? 'active' : '' ?>" data-status="pending" data-no-ajax="true">
                <span><?= e(__('status.pending', 'Pending')) ?></span>
                <span class="mv-pill-cnt"><?= (int)$pendingCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('completed') ?>" class="mv-pill <?= $currStatus === 'completed' ? 'active' : '' ?>" data-status="completed" data-no-ajax="true">
                <span><?= e(__('status.completed', 'Completed')) ?></span>
                <span class="mv-pill-cnt"><?= (int)$completedCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('cancelled') ?>" class="mv-pill <?= $currStatus === 'cancelled' ? 'active' : '' ?>" data-status="cancelled" data-no-ajax="true">
                <span><?= e(__('status.cancelled', 'Cancelled')) ?></span>
                <span class="mv-pill-cnt"><?= (int)$cancelledCount ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Bookings Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="bookingsSection">
        <div class="section-header">
            <h3 class="section-title"><?= e(__('booking.all_bookings', 'All Bookings')) ?></h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('common.total_records', 'Total Records:')) ?></span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($bookings)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th class="col-priority-1 table-col-index text-center" data-column-priority="primary">#</th>
                        <th class="col-priority-1 table-col-name" data-column-priority="primary"><?= e(__('table.customer', 'Customer')) ?></th>
                        <th class="col-priority-2 table-col-schedule" data-column-priority="secondary"><?= e(__('table.travel_dates', 'Travel Dates')) ?></th>
                        <th class="col-priority-2 table-col-route table-col-from" data-column-priority="secondary"><?= e(__('table.from', 'From')) ?></th>
                        <th class="col-priority-2 table-col-route table-col-to" data-column-priority="secondary"><?= e(__('table.to', 'To')) ?></th>
                        <th class="col-priority-1 table-col-amount text-right" data-column-priority="primary" style="text-align: right;"><?= e(__('table.total_fare', 'Total Fare')) ?></th>
                        <th class="col-priority-1 table-col-status text-center" data-column-priority="primary" style="text-align: center;"><?= e(__('table.booking_status', 'Booking Status')) ?></th>
                        <th class="col-priority-1 table-col-actions text-right" data-column-priority="primary" style="text-align: right;"><?= e(__('table.action', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <?php foreach ($bookings as $i => $b):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($b['status'] ?? 'pending');
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                        $statusClass = match($status) {
                            'completed' => 'completed',
                            'confirmed', 'in_progress' => 'in-progress',
                            'assigned' => 'assigned',
                            'cancelled' => 'cancelled',
                            default => 'pending'
                        };
                        $travelDatesStr = format_date($b['start_date']);
                        if (!empty($b['end_date']) && $b['end_date'] !== $b['start_date']) {
                            $travelDatesStr .= ' – ' . format_date($b['end_date']);
                        }
                    ?>
                    <tr class="table-col-code-row" data-id="<?= (int)$b['id'] ?>" data-code="<?= e($b['booking_code']) ?>">
                        <!-- 1. Serial Number (#) -->
                        <td class="col-priority-1 table-col-index text-center" data-column-priority="primary">
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>

                        <!-- 2. Customer Name (Single-line link with tooltip) -->
                        <td class="col-priority-1 table-col-name" data-column-priority="primary">
                            <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="table-cell-link table-cell-truncate" title="<?= e($b['customer_name'] ?? 'Guest Traveler') ?>" style="font-weight: 700; color: #0F172A; text-decoration: none;">
                                <?= e($b['customer_name'] ?? 'Guest Traveler') ?>
                            </a>
                        </td>

                        <!-- 3. Travel Dates (Single line) -->
                        <td class="col-priority-2 table-col-schedule" data-column-priority="secondary">
                            <span class="table-cell-truncate due-date" title="<?= e($travelDatesStr) ?>" style="font-weight: 600; color: #334155;">
                                <?= e($travelDatesStr) ?>
                            </span>
                        </td>

                        <!-- 4. From (Starting place) -->
                        <td class="col-priority-2 table-col-route table-col-from" data-column-priority="secondary">
                            <span class="table-cell-truncate" title="<?= e($b['pickup_address'] ?? '—') ?>" style="color: #334155;">
                                <?= e($b['pickup_address'] ?? '—') ?>
                            </span>
                        </td>

                        <!-- 5. To (Destination) -->
                        <td class="col-priority-2 table-col-route table-col-to" data-column-priority="secondary">
                            <span class="table-cell-truncate" title="<?= e($b['drop_address'] ?? '—') ?>" style="color: #334155;">
                                <?= e($b['drop_address'] ?? '—') ?>
                            </span>
                        </td>

                        <!-- 6. Total Fare (Right-aligned single line currency) -->
                        <td class="col-priority-1 table-col-amount text-right" data-column-priority="primary" style="text-align: right;">
                            <span class="table-cell-amount font-mono" style="font-weight: 800; color: #0F172A;">
                                <?= format_currency((float)$b['total_amount']) ?>
                            </span>
                        </td>

                        <!-- 7. Booking Status (Professional Status Pill) -->
                        <td class="col-priority-1 table-col-status text-center" data-column-priority="primary" style="text-align: center;">
                            <div class="cust-toggle-cell" id="bookingToggleWrap-<?= (int)$b['id'] ?>" style="justify-content: center;">
                                <span class="cust-toggle-pill <?= $statusClass ?>">
                                    <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                    <span class="toggle-label-text"><?= $statusText ?></span>
                                </span>
                            </div>
                        </td>

                        <!-- 8. Actions (Single three-dot menu) -->
                        <td class="col-priority-1 table-col-actions text-right" data-column-priority="primary" style="white-space: nowrap; text-align: right;">
                            <div class="action-dropdown-wrap">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-label="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                                </button>
                                <div class="action-dropdown-menu">
                                    <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="action-dropdown-item view">
                                        <i class="fa-solid fa-eye"></i> <span><?= e(__('booking.view_booking', 'View Booking')) ?></span>
                                    </a>
                                    <a href="<?= url('/office/bookings/' . $b['id'] . '/edit') ?>" class="action-dropdown-item edit">
                                        <i class="fa-solid fa-pen-to-square"></i> <span><?= e(__('booking.edit_booking', 'Edit Booking')) ?></span>
                                    </a>
                                    <?php if (in_array($status, ['assigned', 'in_progress'])): ?>
                                        <a href="<?= url('/office/trips/assign?booking_id=' . $b['id']) ?>" class="action-dropdown-item dispatch">
                                            <i class="fa-solid fa-car-side"></i> <span><?= e(__('btn.reassign_trip', 'Reassign Trip')) ?></span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= url('/office/trips/assign?booking_id=' . $b['id']) ?>" class="action-dropdown-item dispatch">
                                            <i class="fa-solid fa-car-side"></i> <span><?= e(__('sidebar.assign_trip', 'Assign Trip')) ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger" onclick="openDeleteModal(<?= (int)$b['id'] ?>, '<?= addslashes(e($b['booking_code'])) ?>', event)">
                                        <i class="fa-solid fa-trash-can"></i> <span><?= e(__('booking.delete_booking', 'Delete Booking')) ?></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;"><?= e(__('booking.no_bookings_found', 'No bookings found')) ?></h4>
                                <p class="text-muted" style="margin:0;"><?= e(__('common.try_adjusting_filters', 'Try adjusting your search or filter parameters.')) ?></p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Touch Cards List -->
        <div class="cards-grid" id="bookingsCards">
            <?php
            foreach ($bookings as $idx => $b):
                $status = strtolower($b['status'] ?? 'pending');
                $statusText = ucfirst(str_replace('_', ' ', $status));
                $statusClass = match($status) {
                    'completed' => 'completed',
                    'confirmed', 'in_progress' => 'confirmed',
                    'assigned' => 'assigned',
                    'cancelled' => 'cancelled',
                    default => 'pending'
                };

                $cardImg = 'hero-bus.jpg';
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="mv-booking-card">
                    <div class="mv-thumb-wrap">
                        <img src="<?= asset('images/' . $cardImg) ?>" alt="<?= e($b['tour_title'] ?? 'Tour') ?>" class="mv-thumb-img" onerror="this.src='<?= asset('images/hero-bus.jpg') ?>'">
                    </div>
                    <div class="mv-details">
                        <div class="mv-tour-title" title="<?= e($b['tour_title'] ?? 'Custom Route') ?>">
                            <?= e($b['tour_title'] ?? 'Custom Route') ?>
                        </div>
                        <?php if (!empty($b['trip_category_name'])): ?>
                        <div style="margin: 3px 0 2px;">
                            <span style="background: rgba(30, 58, 138, 0.08); color: #1e3a8a; border: 1px solid rgba(30, 58, 138, 0.18); font-size: 10.5px; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                <i class="fa-solid fa-tag" style="font-size: 9px;"></i> <?= e($b['trip_category_name']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-calendar"></i>
                            <span><?= format_date($b['start_date']) ?><?= !empty($b['end_date']) && $b['end_date'] !== $b['start_date'] ? ' &rarr; ' . format_date($b['end_date']) : '' ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-regular fa-user"></i>
                            <span><?= e($b['customer_name'] ?? 'Guest Traveler') ?> &bull; <?= (int)$b['passenger_count'] ?> Pax</span>
                        </div>
                        <?php if (!empty($b['pickup_address']) && !empty($b['drop_address'])): ?>
                        <div class="mv-vehicle" style="color: #475569;" title="<?= e($b['pickup_address']) ?> → <?= e($b['drop_address']) ?>">
                            <i class="fa-solid fa-route"></i>
                            <span><?= e($b['pickup_address']) ?> &rarr; <?= e($b['drop_address']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-hashtag"></i>
                            <span><?= e($b['booking_code']) ?></span>
                        </div>
                    </div>
                    <div class="mv-right-col">
                        <span class="cust-toggle-pill <?= $statusClass ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 999px;">
                            <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                            <span class="toggle-label-text"><?= $statusText ?></span>
                        </span>
                        <div class="mv-price" style="margin-top: 4px;"><?= format_currency((float)$b['total_amount']) ?></div>
                    </div>
                </a>
                <!-- Mobile Card Action Buttons (One Single Actions Control) -->
                <div class="mv-card-actions">
                    <div class="action-dropdown-wrap">
                        <button type="button" class="action-toggle-btn" onclick="toggleCardActions(this, event)" title="<?= e(__('table.action', 'Actions')) ?>" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i> <span><?= e(__('table.action', 'Actions')) ?></span>
                        </button>
                        <div class="action-dropdown-menu">
                            <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="action-dropdown-item view">
                                <i class="fa-solid fa-eye"></i> <span><?= e(__('booking.view_booking', 'View Booking')) ?></span>
                            </a>
                            <a href="<?= url('/office/bookings/' . $b['id'] . '/edit') ?>" class="action-dropdown-item edit">
                                <i class="fa-solid fa-pen-to-square"></i> <span><?= e(__('booking.edit_booking', 'Edit Booking')) ?></span>
                            </a>
                            <?php if (in_array($status, ['assigned', 'in_progress'])): ?>
                                <a href="<?= url('/office/trips/assign?booking_id=' . $b['id']) ?>" class="action-dropdown-item dispatch">
                                    <i class="fa-solid fa-car-side"></i> <span><?= e(__('btn.reassign_trip', 'Reassign Trip')) ?></span>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('/office/trips/assign?booking_id=' . $b['id']) ?>" class="action-dropdown-item dispatch">
                                    <i class="fa-solid fa-car-side"></i> <span><?= e(__('sidebar.assign_trip', 'Assign Trip')) ?></span>
                                </a>
                            <?php endif; ?>
                            <div class="action-dropdown-divider"></div>
                            <button type="button" class="action-dropdown-item danger" onclick="openDeleteModal(<?= (int)$b['id'] ?>, '<?= addslashes(e($b['booking_code'])) ?>', event)">
                                <i class="fa-solid fa-trash-can"></i> <span><?= e(__('booking.delete_booking', 'Delete Booking')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($bookings)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;"><?= e(__('booking.no_bookings_found', 'No bookings found')) ?></h4>
                <p class="text-muted" style="margin:0;"><?= e(__('common.try_adjusting_filters', 'Try adjusting your search or filter parameters.')) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Customers Architecture) -->
    <?php
        $from = $totalRecords > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $to   = min($currentPage * $perPage, $totalRecords);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $sort, $perPage, $selectedCategory) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($selectedCategory)) $q['trip_category_id'] = $selectedCategory;
            if (!empty($sort) && $sort !== 'created_desc') $q['sort'] = $sort;
            if (!empty($perPage) && $perPage != 10) $q['per_page'] = $perPage;
            $q['page'] = $p;
            return url('/office/bookings?' . http_build_query($q));
        };
    ?>
    <div id="bookingsPaginationWrapper">
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totalRecords ?></span> booking(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currentPage - 1)) ?>" class="<?= $currentPage <= 1 ? 'disabled' : '' ?>" title="<?= e(__('pagination.previous', 'Previous Page')) ?>" data-page="<?= max(1, $currentPage - 1) ?>" data-no-ajax="true">&#10094;</a>
                <?php
                    $rangeStart = max(1, $currentPage - 1);
                    $rangeEnd   = min($totalPages, $rangeStart + 2);
                    if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                        $rangeStart = max(1, $rangeEnd - 2);
                    }
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                ?>
                    <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currentPage ? 'active' : '' ?>" data-page="<?= $i ?>" data-no-ajax="true"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($rangeEnd < $totalPages): ?>
                    <span class="dots">...</span>
                    <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currentPage === $totalPages ? 'active' : '' ?>" data-page="<?= $totalPages ?>" data-no-ajax="true"><?= $totalPages ?></a>
                <?php endif; ?>
                <a href="<?= $buildPageUrl(min($totalPages, $currentPage + 1)) ?>" class="<?= $currentPage >= $totalPages ? 'disabled' : '' ?>" title="<?= e(__('pagination.next', 'Next Page')) ?>" data-page="<?= min($totalPages, $currentPage + 1) ?>" data-no-ajax="true">&#10095;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========================================================================= -->
    <!-- DELETE CONFIRMATION MODALS (Matches design in delete.jpeg)                -->
    <!-- ========================================================================= -->
    <div id="deleteModalBackdrop" class="vg-del-backdrop" style="display: none;" onclick="handleBackdropClick(event)">
        <!-- MODAL 1: Initial Delete with Checkbox (Matching delete.jpeg Top Card) -->
        <div class="vg-del-modal" id="deleteStep1Modal" role="dialog" aria-modal="true" aria-labelledby="delModalTitle1">
            <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="<?= e(__('common.close_modal', 'Close dialog')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="vg-del-body">
                <!-- Left glowing icon matching delete.jpeg top modal -->
                <div class="vg-del-icon-halo">
                    <div class="vg-del-icon-circle">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                </div>

                <!-- Right text content -->
                <div class="vg-del-content">
                    <h3 class="vg-del-title" id="delModalTitle1"><?= e(__('btn.delete', 'Delete')) ?></h3>
                    <p class="vg-del-desc" id="delModalDesc1">
                        <?= e(__('modal.delete_item_perm', 'This item will be permanently deleted and cannot be recovered.')) ?>
                    </p>

                    <!-- Checkbox requirement -->
                    <div class="vg-del-checkbox-wrap" id="delCheckboxContainer">
                        <label class="vg-del-checkbox-label">
                            <input type="checkbox" id="deleteStep1Checkbox" class="vg-del-checkbox-input" onchange="toggleStep1DeleteBtn()">
                            <span class="vg-del-checkbox-box">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="vg-del-checkbox-text">
                                <?= e(__('modal.confirm_perm_booking', 'I understand this booking will be permanently deleted.')) ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action buttons (Cancel & Delete pill buttons matching delete.jpeg) -->
            <div class="vg-del-actions">
                <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()"><?= e(__('btn.cancel', 'Cancel')) ?></button>
                <button type="button" class="vg-del-btn-delete disabled" id="deleteStep1Btn" onclick="goToStep2Modal()">
                    <i class="fa-solid fa-trash-can"></i>
                    <span><?= e(__('btn.delete', 'Delete')) ?></span>
                </button>
            </div>
        </div>

        <!-- MODAL 2: "Are you sure?" Final Confirmation (Matching delete.jpeg Bottom Card) -->
        <div class="vg-del-modal" id="deleteStep2Modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="delModalTitle2">
            <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="<?= e(__('btn.close', 'Close dialog')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="vg-del-body">
                <!-- Left glowing triangle warning icon matching delete.jpeg bottom modal -->
                <div class="vg-del-icon-halo warning">
                    <div class="vg-del-warning-triangle">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" class="vg-del-triangle-svg">
                            <path d="M10.29 3.86L1.82 18.54A2 2 0 0 0 3.53 21.5h16.94a2 2 0 0 0 1.71-2.96L13.71 3.86a2 2 0 0 0-3.42 0z" fill="url(#vgDelTriangleGrad)"/>
                            <line x1="12" y1="9" x2="12" y2="13.5" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round"/>
                            <circle cx="12" cy="17.2" r="1.3" fill="#FFFFFF"/>
                            <defs>
                                <linearGradient id="vgDelTriangleGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FF4D6D"/>
                                    <stop offset="1" stop-color="#EF233C"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>

                <!-- Right text content -->
                <div class="vg-del-content">
                    <h3 class="vg-del-title" id="delModalTitle2"><?= e(__('common.are_you_sure', 'Are you sure?')) ?></h3>
                    <p class="vg-del-desc" id="delModalDesc2">
                        <?= e(__('modal.delete_undone', 'You are about to delete this item. This action cannot be undone.')) ?>
                    </p>

                    <!-- Checkbox requirement in Modal 2 -->
                    <div class="vg-del-checkbox-wrap" id="delCheckboxContainer2">
                        <label class="vg-del-checkbox-label">
                            <input type="checkbox" id="deleteStep2Checkbox" class="vg-del-checkbox-input" onchange="toggleStep2DeleteBtn()">
                            <span class="vg-del-checkbox-box">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="vg-del-checkbox-text">
                                <?= e(__('modal.confirm_sure_booking', 'Yes, I am completely sure and want to delete this booking.')) ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action buttons matching delete.jpeg bottom modal -->
            <div class="vg-del-actions">
                <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()"><?= e(__('btn.cancel', 'Cancel')) ?></button>
                <button type="button" class="vg-del-btn-delete disabled" id="deleteConfirmFinalBtn" onclick="confirmDeleteFinal()">
                    <i class="fa-solid fa-trash-can"></i>
                    <span id="deleteFinalBtnText"><?= e(__('btn.delete', 'Delete')) ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden form for standard POST fallback -->
    <form id="deleteBookingForm" method="POST" style="display: none;">
        <?= csrf_field() ?>
    </form>
</div>

<script>
    /* ========================================================================= */
    /* Bookings Directory Live AJAX Filter Engine (Matches Customers Architecture)*/
    /* ========================================================================= */
    (function initBookingAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/bookings') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const tripCategoryInput = document.getElementById('tripCategoryInput');
            const mvCategorySelect = document.getElementById('mvCategorySelect');
            const sortInput = document.getElementById('sortInput');
            const mvSortSelect = document.getElementById('mvSortSelect');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const catVal = tripCategoryInput ? tripCategoryInput.value : (mvCategorySelect ? mvCategorySelect.value : '');
            const sortVal = sortInput ? sortInput.value : (mvSortSelect ? mvSortSelect.value : 'created_desc');
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (catVal) p.set('trip_category_id', catVal);
            if (sortVal && sortVal !== 'created_desc') p.set('sort', sortVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, catVal, sortVal, perPageVal: ppVal, pageNum: page };
        }

        function filterBookingsAjax(page = 1) {
            const { params, searchVal, statusVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('bookingsSection');
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

                const newSection = doc.getElementById('bookingsSection');
                const currSection = document.getElementById('bookingsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('bookingsPaginationWrapper');
                const currPagination = document.getElementById('bookingsPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('bookingsKpiGrid');
                const currKpi = document.getElementById('bookingsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('bookingsMvStatusScroll');
                const currMvPills = document.getElementById('bookingsMvStatusScroll');
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
                    console.error('Bookings AJAX filter error:', err);
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
                searchTimer = setTimeout(() => filterBookingsAjax(1), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterBookingsAjax(1);
                }
            });
        }

        const mInput = document.getElementById('mvSearchInput');
        if (mInput) {
            mInput.addEventListener('input', function() {
                const val = this.value;
                if (sInput && sInput.value !== val) sInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterBookingsAjax(1), 280);
            });
            mInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterBookingsAjax(1);
                }
            });
        }

        // Clear buttons
        function clearSearch() {
            if (sInput) sInput.value = '';
            if (mInput) mInput.value = '';
            filterBookingsAjax(1);
        }
        const sClear = document.getElementById('searchClearBtn');
        if (sClear) sClear.addEventListener('click', clearSearch);
        const mClear = document.getElementById('mvSearchClearBtn');
        if (mClear) mClear.addEventListener('click', clearSearch);

        // Dropdown changes
        const stInput = document.getElementById('statusInput');
        if (stInput) stInput.addEventListener('change', () => filterBookingsAjax(1));
        const tcInput = document.getElementById('tripCategoryInput');
        const mvTcSelect = document.getElementById('mvCategorySelect');
        if (tcInput) tcInput.addEventListener('change', () => {
            if (mvTcSelect) mvTcSelect.value = tcInput.value;
            filterBookingsAjax(1);
        });
        if (mvTcSelect) mvTcSelect.addEventListener('change', () => {
            if (tcInput) tcInput.value = mvTcSelect.value;
            filterBookingsAjax(1);
        });
        const sortInput = document.getElementById('sortInput');
        if (sortInput) sortInput.addEventListener('change', () => filterBookingsAjax(1));
        const mvSortSelect = document.getElementById('mvSortSelect');
        if (mvSortSelect) mvSortSelect.addEventListener('change', () => {
            if (sortInput) sortInput.value = mvSortSelect.value;
            filterBookingsAjax(1);
        });
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) ppInput.addEventListener('change', () => filterBookingsAjax(1));
        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) mvPpSelect.addEventListener('change', () => {
            if (ppInput) ppInput.value = mvPpSelect.value;
            filterBookingsAjax(1);
        });

        // Filter Apply & Refresh Buttons
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) applyBtn.addEventListener('click', () => filterBookingsAjax(1));
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterBookingsAjax(1);
        });

        // Live Dynamic KPI Cards and Mobile Pills Syncer (AJAX Real-Time Update)
        function updateBookingMetrics(metrics) {
            if (!metrics) return;
            const mapping = {
                '': metrics.total,
                'active': metrics.active ?? metrics.confirmed,
                'confirmed': metrics.confirmed ?? metrics.active,
                'pending': metrics.pending,
                'completed': metrics.completed,
                'cancelled': metrics.cancelled
            };
            const grid = document.getElementById('bookingsKpiGrid');
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
            const scrollWrap = document.getElementById('bookingsMvStatusScroll');
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
            const kpiCard = e.target.closest('#bookingsKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#bookingsKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#bookingsMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterBookingsAjax(1);
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
                    const tcIn = document.getElementById('tripCategoryInput');
                    if (tcIn) tcIn.value = '';
                    const mvTcIn = document.getElementById('mvCategorySelect');
                    if (mvTcIn) mvTcIn.value = '';
                    if (sortInput) sortInput.value = 'created_desc';
                    if (mvSortSelect) mvSortSelect.value = 'created_desc';
                }
                filterBookingsAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#bookingsMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#bookingsMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#bookingsKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st);
                });

                filterBookingsAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#bookingsPaginationWrapper .pagination a');
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
                filterBookingsAjax(targetPage);

                const sec = document.getElementById('bookingsSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#bookingsKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterBookingsAjax(1);
        });

        window.updateBookingMetrics = updateBookingMetrics;
        window.filterBookingsAjax = filterBookingsAjax;
    })();

    // Action popups (Delegated to Authoritative OfficeActionMenu with fallback)
    function toggleActions(btn, e) {
        if (window.OfficeActionMenu && typeof window.OfficeActionMenu.toggle === 'function') {
            window.OfficeActionMenu.toggle(btn, e);
            return;
        }
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }

    function toggleCardActions(btn, e) {
        if (window.OfficeActionMenu && typeof window.OfficeActionMenu.toggle === 'function') {
            window.OfficeActionMenu.toggle(btn, e);
            return;
        }
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

    function toggleBookingStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'pending' : 'confirmed';

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/bookings/') ?>' + id + '/toggle-status';
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
            if (!res.ok) {
                return res.json().then(j => { throw new Error(j.message || ('HTTP ' + res.status)); });
            }
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                const isNowConfirmed = (data.status === 'confirmed');

                // Update all matching toggle buttons (table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowConfirmed) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle status (currently Confirmed)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle status (currently Pending)';
                    }
                });

                // Update table status pill & dot
                const wrap = document.getElementById('bookingToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    const pillClass = isNowConfirmed ? 'completed' : 'pending';
                    if (pill) pill.className = 'cust-toggle-pill ' + pillClass;
                    if (dot) dot.className = 'toggle-pulse-dot ' + pillClass;
                    if (text) text.textContent = data.label;
                }

                // Update mobile cards
                document.querySelectorAll('.mv-booking-card-wrapper').forEach(card => {
                    const mBtn = card.querySelector(`.cust-status-toggle[data-id="${id}"]`);
                    if (mBtn) {
                        const mText = card.querySelector('.mv-toggle-label-text');
                        if (mText) {
                            mText.className = 'mv-toggle-label-text ' + (isNowConfirmed ? 'completed' : 'pending');
                            mText.textContent = data.label;
                        }
                    }
                });

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateBookingMetrics === 'function') {
                    window.updateBookingMetrics(data.metrics);
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Booking status updated to ${data.label}`, 'success');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error toggling booking status', 'error');
            } else {
                console.error(err.message || 'Error toggling booking status');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

    /* ========================================================================= */
    /* Two-Step Delete Modals (Matching customer/index design)                   */
    /* ========================================================================= */
    let currentDeleteId = null;
    let currentDeleteName = '';

    function openDeleteModal(id, name, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        currentDeleteId = id;
        currentDeleteName = name || 'this booking';

        const backdrop = document.getElementById('deleteModalBackdrop');
        const step1 = document.getElementById('deleteStep1Modal');
        const step2 = document.getElementById('deleteStep2Modal');
        const chk = document.getElementById('deleteStep1Checkbox');
        const btn1 = document.getElementById('deleteStep1Btn');
        const chkWrap = document.getElementById('delCheckboxContainer');
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');
        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        const finalBtnText = document.getElementById('deleteFinalBtnText');

        if (chk) chk.checked = false;
        if (btn1) {
            if (window.innerWidth <= 640) {
                btn1.classList.remove('disabled');
            } else {
                btn1.classList.add('disabled');
            }
        }
        if (chkWrap) chkWrap.classList.remove('shake-error');
        if (chk2) chk2.checked = false;
        if (chkWrap2) chkWrap2.classList.remove('shake-error');
        if (finalBtn) {
            finalBtn.disabled = false;
            finalBtn.style.opacity = '';
            if (window.innerWidth <= 640) {
                finalBtn.classList.remove('disabled');
            } else {
                finalBtn.classList.add('disabled');
            }
        }
        if (finalBtnText) finalBtnText.textContent = 'Delete';

        const desc1 = document.getElementById('delModalDesc1');
        if (desc1) {
            desc1.textContent = 'This item will be permanently deleted and cannot be recovered.';
        }
        const desc2 = document.getElementById('delModalDesc2');
        if (desc2) {
            desc2.textContent = 'You are about to delete this item. This action cannot be undone.';
        }

        if (step1) step1.style.display = 'block';
        if (step2) step2.style.display = 'none';

        if (backdrop) {
            backdrop.style.display = 'flex';
            setTimeout(() => {
                backdrop.classList.add('active');
            }, 10);
        }
    }

    function closeDeleteModal() {
        const backdrop = document.getElementById('deleteModalBackdrop');
        if (!backdrop) return;
        backdrop.classList.remove('active');
        setTimeout(() => {
            backdrop.style.display = 'none';
            const step1 = document.getElementById('deleteStep1Modal');
            const step2 = document.getElementById('deleteStep2Modal');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
            currentDeleteId = null;
            currentDeleteName = '';
        }, 220);
    }

    function handleBackdropClick(e) {
        if (e.target && e.target.id === 'deleteModalBackdrop') {
            closeDeleteModal();
        }
    }

    function toggleStep1DeleteBtn() {
        const chk = document.getElementById('deleteStep1Checkbox');
        const btn = document.getElementById('deleteStep1Btn');
        const chkWrap = document.getElementById('delCheckboxContainer');
        if (chkWrap) chkWrap.classList.remove('shake-error');

        if (chk && btn) {
            if (chk.checked) {
                btn.classList.remove('disabled');
            } else {
                btn.classList.add('disabled');
            }
        }
    }

    function toggleStep2DeleteBtn() {
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const btn2 = document.getElementById('deleteConfirmFinalBtn');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');
        if (chkWrap2) chkWrap2.classList.remove('shake-error');

        if (chk2 && btn2) {
            if (chk2.checked) {
                btn2.classList.remove('disabled');
            } else {
                btn2.classList.add('disabled');
            }
        }
    }

    function goToStep2Modal() {
        const chk = document.getElementById('deleteStep1Checkbox');
        const chkWrap = document.getElementById('delCheckboxContainer');

        if (window.innerWidth > 640 && (!chk || !chk.checked)) {
            if (chkWrap) {
                chkWrap.classList.add('shake-error');
                setTimeout(() => chkWrap.classList.remove('shake-error'), 450);
            }
            return;
        }

        const step1 = document.getElementById('deleteStep1Modal');
        const step2 = document.getElementById('deleteStep2Modal');
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        if (chk2) chk2.checked = false;
        if (finalBtn) {
            if (window.innerWidth <= 640) {
                finalBtn.classList.remove('disabled');
            } else {
                finalBtn.classList.add('disabled');
            }
        }

        if (step1 && step2) {
            step1.style.display = 'none';
            step2.style.display = 'block';
        }
    }

    function confirmDeleteFinal() {
        if (!currentDeleteId) return;

        const chk2 = document.getElementById('deleteStep2Checkbox');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');

        if (window.innerWidth > 640 && chk2 && !chk2.checked) {
            if (chkWrap2) {
                chkWrap2.classList.add('shake-error');
                setTimeout(() => chkWrap2.classList.remove('shake-error'), 450);
            }
            return;
        }

        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        const btnText = document.getElementById('deleteFinalBtnText');
        if (finalBtn) {
            finalBtn.disabled = true;
            finalBtn.style.opacity = '0.75';
            if (btnText) btnText.textContent = 'Deleting...';
        }

        const deleteUrl = '<?= url('/office/bookings/') ?>' + currentDeleteId + '/delete';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(deleteUrl, {
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
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Booking deleted successfully.', 'success');
                }
                if (data.metrics && typeof window.updateBookingMetrics === 'function') {
                    window.updateBookingMetrics(data.metrics);
                }
                if (typeof window.filterBookingsAjax === 'function') {
                    const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                    window.filterBookingsAjax(pg, { skipPushState: true });
                } else {
                    window.location.reload();
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to delete booking.', 'error');
                } else {
                    console.error(data.message || 'Failed to delete booking.');
                }
                closeDeleteModal();
            }
        })
        .catch(err => {
            // Fallback to standard form post
            const form = document.getElementById('deleteBookingForm');
            if (form) {
                form.action = deleteUrl;
                form.submit();
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify('An error occurred while deleting booking manifest.', 'error');
                } else {
                    console.error('An error occurred while deleting booking manifest.');
                }
                closeDeleteModal();
            }
        });
    }

    // Support keyboard ESC to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const backdrop = document.getElementById('deleteModalBackdrop');
            if (backdrop && backdrop.style.display !== 'none') {
                closeDeleteModal();
            }
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
.cust-toggle-pill.completed, .cust-toggle-pill.confirmed {
    background: #DCFCE7;
    color: #15803D;
}
.cust-toggle-pill.assigned, .cust-toggle-pill.in-progress, .cust-toggle-pill.in_progress {
    background: #DBEAFE;
    color: #16443B;
}
.cust-toggle-pill.pending {
    background: #FEF3C7;
    color: #B45309;
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
.toggle-pulse-dot.completed, .toggle-pulse-dot.confirmed {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}
.toggle-pulse-dot.assigned, .toggle-pulse-dot.in-progress, .toggle-pulse-dot.in_progress {
    background: #218765;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}
.toggle-pulse-dot.pending {
    background: #F59E0B;
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
.mv-toggle-label-text.completed, .mv-toggle-label-text.confirmed {
    color: #10B981;
}
.mv-toggle-label-text.assigned, .mv-toggle-label-text.in-progress, .mv-toggle-label-text.in_progress {
    color: #176B5B;
}
.mv-toggle-label-text.pending {
    color: #D97706;
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

/* Table Delete Action Button */
.action-btn-table.delete {
    color: #EF4444;
    background-color: #FEF2F2;
    border: 1px solid #FECACA;
}
.action-btn-table.delete:hover {
    background-color: #DC2626 !important;
    color: #FFFFFF !important;
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35);
}

/* Card-Specific KPI Selected Themes matching Customers */
.booking-kpi-card[data-status=""].is-selected {
    border-color: #0D9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.16), 0 10px 24px rgba(13, 148, 136, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #F0FDFA 100%);
}
.booking-kpi-card[data-status=""].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #0D9488;
    font-weight: 700;
}

.booking-kpi-card[data-status="active"].is-selected {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16), 0 10px 24px rgba(16, 185, 129, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #ECFDF5 100%);
}
.booking-kpi-card[data-status="active"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #10B981;
    font-weight: 700;
}

.booking-kpi-card[data-status="pending"].is-selected {
    border-color: #F59E0B;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.16), 0 10px 24px rgba(245, 158, 11, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFBEB 100%);
}
.booking-kpi-card[data-status="pending"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #F59E0B;
    font-weight: 700;
}

.booking-kpi-card[data-status="completed"].is-selected {
    border-color: #218765;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16), 0 10px 24px rgba(59, 130, 246, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #E8F7F0 100%);
}
.booking-kpi-card[data-status="completed"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #218765;
    font-weight: 700;
}

/* ========================================================================= */
/* Two-Step Delete Confirmation Modals (Matching delete.jpeg)                */
/* ========================================================================= */
.vg-del-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

.vg-del-backdrop.active {
    opacity: 1;
}

.vg-del-modal {
    position: relative;
    width: 100%;
    max-width: 530px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.96) 65%, rgba(240, 247, 255, 0.94) 100%);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border-radius: 26px;
    border: 1.5px solid rgba(255, 255, 255, 0.95);
    box-shadow: 
        0 24px 50px -12px rgba(15, 23, 42, 0.24),
        0 8px 18px -4px rgba(15, 23, 42, 0.12),
        0 0 0 1px rgba(226, 232, 240, 0.6) inset;
    padding: 34px 38px 30px 38px;
    transform: scale(0.93) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease;
    overflow: hidden;
}

.vg-del-modal::before {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -40px;
    width: 340px;
    height: 180px;
    background: radial-gradient(ellipse at center, rgba(186, 230, 253, 0.5) 0%, rgba(224, 242, 254, 0.25) 55%, transparent 75%);
    pointer-events: none;
    border-radius: 50%;
    z-index: 0;
}

.vg-del-backdrop.active .vg-del-modal {
    transform: scale(1) translateY(0);
}

.vg-del-close-btn {
    position: absolute;
    top: 20px;
    right: 22px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #64748B;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 3;
}

.vg-del-close-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.vg-del-body {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    gap: 22px;
}

.vg-del-icon-halo {
    position: relative;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(254, 205, 211, 0.95) 0%, rgba(255, 228, 230, 0.6) 55%, rgba(255, 241, 242, 0) 100%);
    box-shadow: 0 0 26px rgba(244, 63, 94, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.vg-del-icon-halo.warning {
    box-shadow: 0 0 28px rgba(244, 63, 94, 0.4);
}

.vg-del-icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(145deg, #FF4D6D 0%, #EF233C 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 1.3rem;
    box-shadow: 0 6px 16px rgba(239, 35, 60, 0.42);
}

.vg-del-warning-triangle {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 6px 14px rgba(239, 35, 60, 0.45));
}

.vg-del-content {
    flex: 1;
    min-width: 0;
}

.vg-del-title {
    font-size: 1.62rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 6px 0;
    line-height: 1.2;
    letter-spacing: -0.01em;
}

.vg-del-desc {
    font-size: 0.95rem;
    color: #64748B;
    line-height: 1.5;
    margin: 0 0 16px 0;
}

.vg-del-checkbox-wrap {
    margin-top: 14px;
    padding: 10px 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.vg-del-checkbox-wrap:hover {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.vg-del-checkbox-wrap.shake-error {
    border-color: #EF4444;
    background: #FEF2F2;
    animation: vgDelShake 0.4s ease;
}

@keyframes vgDelShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
}

.vg-del-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
    margin: 0;
}

.vg-del-checkbox-input {
    display: none;
}

.vg-del-checkbox-box {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 1.8px solid #94A3B8;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 0.72rem;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.vg-del-checkbox-input:checked + .vg-del-checkbox-box {
    background: #EF4444;
    border-color: #EF4444;
}

.vg-del-checkbox-text {
    font-size: 0.88rem;
    color: #334155;
    font-weight: 500;
}

.vg-del-actions {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 14px;
    margin-top: 26px;
}

.vg-del-btn-cancel {
    border-radius: 9999px;
    padding: 10px 26px;
    border: 1.5px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.vg-del-btn-cancel:hover {
    background: #F8FAFC;
    border-color: #94A3B8;
    color: #0F172A;
}

.vg-del-btn-delete {
    border-radius: 9999px;
    padding: 10px 28px;
    border: none;
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    color: #FFFFFF;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.42);
    transition: all 0.2s ease;
}

.vg-del-btn-delete:hover:not(.disabled) {
    background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.52);
    transform: translateY(-1px);
}

.vg-del-btn-delete.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}

@media (max-width: 640px) {
    .vg-del-backdrop {
        padding: 16px !important;
        background: rgba(15, 23, 42, 0.55) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }
    .vg-del-modal {
        width: calc(100% - 24px) !important;
        max-width: 370px !important;
        margin: 0 auto !important;
        padding: 24px 20px 20px 20px !important;
        border-radius: 22px !important;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.96) 0%, rgba(240, 247, 255, 0.92) 55%, rgba(224, 242, 254, 0.88) 100%) !important;
        backdrop-filter: blur(24px) !important;
        -webkit-backdrop-filter: blur(24px) !important;
        border: 1px solid rgba(255, 255, 255, 0.85) !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.6) inset !important;
        overflow: hidden !important;
    }
    .vg-del-modal::before {
        content: '' !important;
        position: absolute !important;
        bottom: -35px !important;
        left: -25px !important;
        width: 220px !important;
        height: 110px !important;
        background: radial-gradient(ellipse at center, rgba(186, 230, 253, 0.65) 0%, rgba(219, 234, 254, 0.35) 50%, transparent 75%) !important;
        border-radius: 50% !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }
    .vg-del-close-btn {
        top: 14px !important;
        right: 14px !important;
        width: 28px !important;
        height: 28px !important;
        font-size: 0.95rem !important;
        color: #64748B !important;
    }
    .vg-del-body {
        gap: 14px !important;
        align-items: flex-start !important;
    }
    .vg-del-icon-halo {
        width: 54px !important;
        height: 54px !important;
        flex-shrink: 0 !important;
        background: radial-gradient(circle, rgba(254, 205, 211, 0.95) 0%, rgba(255, 228, 230, 0.55) 60%, rgba(255, 241, 242, 0) 100%) !important;
        box-shadow: 0 0 20px rgba(244, 63, 94, 0.3) !important;
    }
    .vg-del-icon-circle {
        width: 42px !important;
        height: 42px !important;
        font-size: 1.15rem !important;
        background: linear-gradient(145deg, #FF4D6D 0%, #EF233C 100%) !important;
        box-shadow: 0 4px 12px rgba(239, 35, 60, 0.35) !important;
    }
    .vg-del-icon-halo.warning {
        background: radial-gradient(circle, rgba(254, 205, 211, 0.9) 0%, rgba(255, 228, 230, 0.5) 60%, rgba(255, 241, 242, 0) 100%) !important;
        box-shadow: 0 0 22px rgba(244, 63, 94, 0.35) !important;
    }
    .vg-del-warning-triangle {
        width: 42px !important;
        height: 42px !important;
        filter: drop-shadow(0 4px 10px rgba(239, 35, 60, 0.4)) !important;
    }
    .vg-del-warning-triangle svg {
        width: 28px !important;
        height: 28px !important;
    }
    .vg-del-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: #0F172A !important;
        margin: 0 0 4px 0 !important;
        line-height: 1.2 !important;
    }
    .vg-del-desc {
        font-size: 0.85rem !important;
        color: #475569 !important;
        line-height: 1.35 !important;
        margin: 0 !important;
    }
    /* Checkbox requirement styled compactly for mobile matching lg device */
    .vg-del-checkbox-wrap {
        display: block !important;
        margin-top: 10px !important;
        padding: 8px 10px !important;
        background: rgba(248, 250, 252, 0.9) !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
    }
    .vg-del-checkbox-wrap:hover {
        background: #F1F5F9 !important;
        border-color: #94A3B8 !important;
    }
    .vg-del-checkbox-wrap.shake-error {
        border-color: #EF4444 !important;
        background: #FEF2F2 !important;
    }
    .vg-del-checkbox-label {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        margin: 0 !important;
    }
    .vg-del-checkbox-box {
        width: 18px !important;
        height: 18px !important;
        border-radius: 5px !important;
        font-size: 0.68rem !important;
        flex-shrink: 0 !important;
    }
    .vg-del-checkbox-text {
        font-size: 0.78rem !important;
        color: #334155 !important;
        line-height: 1.3 !important;
        font-weight: 500 !important;
    }
    .vg-del-actions {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 10px !important;
        margin-top: 16px !important;
    }
    .vg-del-btn-cancel {
        padding: 7px 20px !important;
        font-size: 0.88rem !important;
        border-radius: 9999px !important;
        background: rgba(255, 255, 255, 0.8) !important;
        border: 1.5px solid rgba(203, 213, 225, 0.8) !important;
        color: #334155 !important;
        font-weight: 600 !important;
    }
    .vg-del-btn-delete {
        padding: 7px 20px !important;
        font-size: 0.88rem !important;
        border-radius: 9999px !important;
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
        color: #FFFFFF !important;
        border: none !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45) !important;
        gap: 6px !important;
        opacity: 1 !important;
        cursor: pointer !important;
    }
    .vg-del-btn-delete i {
        font-size: 0.82rem !important;
    }
    .vg-del-btn-delete.disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
}
</style>
