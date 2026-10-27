<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved Exactly on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('sidebar.bookings', 'Reservation Bookings'),
            'description' => __('nav.bookings', 'Manage luxury tour reservations, passenger manifests, route itineraries, and dispatch assignments.'),
            'image'       => 'header-bookings.jpg',
            'badge'       => 'Live Manifests',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.bookings', 'Bookings'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/bookings/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.create_booking', 'Create Booking')) . '</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Directly Below Header) -->
    <?php
        $totalCount     = $stats['total'] ?? count($bookings);
        $activeCount    = $stats['active'] ?? 0;
        $pendingCount   = $stats['pending'] ?? 0;
        $completedCount = $stats['completed'] ?? 0;
        $cancelledCount = $stats['cancelled'] ?? 0;
    ?>
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Reservations -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('table.total_bookings', 'Total Bookings')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All recorded manifests')) ?></span>
            </div>
        </div>

        <!-- Card 2: Active & En Route -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.active', 'Active & En Route')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($activeCount) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.in_progress', 'Confirmed & transit')) ?></span>
            </div>
        </div>

        <!-- Card 3: Pending Dispatch -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.pending_dispatch', 'Pending Dispatch')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($pendingCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.awaiting_chauffeur', 'Needs fleet assignment')) ?></span>
            </div>
        </div>

        <!-- Card 4: Completed Journeys -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.completed_tours', 'Completed Tours')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($completedCount) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.successfully_closed', 'Closed itineraries')) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact PMS Design) -->
    <form method="GET" action="<?= url('/office/bookings') ?>" class="filter-bar-compact" id="bookingFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="q" id="searchInput" placeholder="Search bookings by ref, customer, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="status" id="statusInput" onchange="document.getElementById('bookingFilterForm').submit()">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>><?= e(__('filter.all_status', 'All Status')) ?></option>
            <option value="pending" <?= ($selectedStatus ?? '') === 'pending' ? 'selected' : '' ?>><?= e(__('status.pending', 'Pending')) ?></option>
            <option value="confirmed" <?= ($selectedStatus ?? '') === 'confirmed' ? 'selected' : '' ?>><?= e(__('status.confirmed', 'Confirmed')) ?></option>
            <option value="assigned" <?= ($selectedStatus ?? '') === 'assigned' ? 'selected' : '' ?>><?= e(__('status.assigned', 'Assigned')) ?></option>
            <option value="in_progress" <?= ($selectedStatus ?? '') === 'in_progress' ? 'selected' : '' ?>><?= e(__('status.in_progress', 'In Progress')) ?></option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' ? 'selected' : '' ?>><?= e(__('status.completed', 'Completed')) ?></option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' ? 'selected' : '' ?>><?= e(__('status.cancelled', 'Cancelled')) ?></option>
        </select>

        <select name="sort" id="sortInput" onchange="document.getElementById('bookingFilterForm').submit()">
            <option value="created_desc" <?= ($sort ?? 'created_desc') === 'created_desc' ? 'selected' : '' ?>><?= e(__('filter.newest_first', 'Newest First')) ?></option>
            <option value="created_asc"  <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>><?= e(__('filter.oldest_first', 'Oldest First')) ?></option>
            <option value="client_asc"   <?= ($sort ?? '') === 'client_asc' ? 'selected' : '' ?>><?= e(__('filter.customer_az', 'Customer A → Z')) ?></option>
            <option value="client_desc"  <?= ($sort ?? '') === 'client_desc' ? 'selected' : '' ?>><?= e(__('filter.customer_za', 'Customer Z → A')) ?></option>
            <option value="date_asc"     <?= ($sort ?? '') === 'date_asc' ? 'selected' : '' ?>><?= e(__('filter.travel_date_asc', 'Travel Date ↑')) ?></option>
            <option value="date_desc"    <?= ($sort ?? '') === 'date_desc' ? 'selected' : '' ?>><?= e(__('filter.travel_date_desc', 'Travel Date ↓')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('bookingFilterForm').submit()">
            <?php foreach ([5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <a href="<?= url('/office/bookings') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedStatus) || (!empty($sort) && $sort !== 'created_desc')): ?>
                <a href="<?= url('/office/bookings') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- =========================================================================
         4. MOBILE ONLY: Exact Screen 3 ("Bookings") from mobileview.jpeg
         ========================================================================= -->
    <div class="mv-bookings-header">
        <!-- Top App Bar: ← Bookings      + New Booking -->
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

        <!-- Search Bar + Filter Sliders Button -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/bookings') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="q" id="mvSearchInput" placeholder="Search by name, booking no..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/bookings' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
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

            <!-- Collapsible Filter Panel (Mobile Drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/bookings') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvSortSelect">Sort Bookings:</label>
                        <select name="sort" id="mvSortSelect" onchange="this.form.submit()">
                            <option value="created_desc" <?= ($sort ?? 'created_desc') === 'created_desc' ? 'selected' : '' ?>>Newest First</option>
                            <option value="created_asc"  <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="date_asc"     <?= ($sort ?? '') === 'date_asc' ? 'selected' : '' ?>>Travel Date ↑</option>
                            <option value="date_desc"    <?= ($sort ?? '') === 'date_desc' ? 'selected' : '' ?>>Travel Date ↓</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect" onchange="this.form.submit()">
                            <?php foreach ([10, 20, 30, 50] as $opt): ?>
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

        <!-- Horizontal Scrollable Status Filter Pills: All | Confirmed | Pending | Cancelled -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $sort, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['q'] = $search;
                if (!empty($sort) && $sort !== 'created_desc') $p['sort'] = $sort;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/bookings' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)$totalCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('confirmed') ?>" class="mv-pill <?= in_array($currStatus, ['confirmed', 'in_progress', 'assigned']) ? 'active' : '' ?>">
                <span>Confirmed</span>
                <span class="mv-pill-cnt"><?= (int)$activeCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('pending') ?>" class="mv-pill <?= $currStatus === 'pending' ? 'active' : '' ?>">
                <span>Pending</span>
                <span class="mv-pill-cnt"><?= (int)$pendingCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('cancelled') ?>" class="mv-pill <?= $currStatus === 'cancelled' ? 'active' : '' ?>">
                <span>Cancelled</span>
                <span class="mv-pill-cnt"><?= (int)$cancelledCount ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Bookings Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">All Bookings</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($bookings)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th class="text-center" style="width: 25%;"><?= e(__('table.booking_and_tour', 'Booking & Tour')) ?></th>
                        <th style="width: 22%;"><?= e(__('table.passenger_client', 'Passenger / Client')) ?></th>
                        <th class="text-center" style="width: 20%;"><?= e(__('table.travel_schedule', 'Travel Schedule')) ?></th>
                        <th class="text-center" style="width: 14%;"><?= e(__('table.total_fare', 'Total Fare')) ?></th>
                        <th class="text-center" style="width: 12%;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 55px;"><?= e(__('table.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <?php foreach ($bookings as $i => $b):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = $b['status'] ?? 'pending';
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                        $statusClass = match($status) {
                            'completed' => 'completed',
                            'confirmed', 'in_progress' => 'in-progress',
                            'assigned' => 'assigned',
                            'cancelled' => 'cancelled',
                            default => 'pending'
                        };
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td class="text-center">
                            <div class="project-name" style="font-family:monospace;letter-spacing:-0.01em;">
                                #<?= e($b['booking_code']) ?>
                            </div>
                            <div style="margin-top: 3px;">
                                <span style="display:inline-block;background:rgba(23,107,91,0.08);color:#16443B;border:1px solid rgba(23,107,91,0.15);border-radius:6px;padding:2px 8px;font-size:11.5px;font-weight:600;">
                                    <?= e($b['tour_title'] ?? 'Custom Route') ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="client-cell">
                                <div class="client-info">
                                    <span class="client-name"><?= e($b['customer_name']) ?></span>
                                    <?php if (!empty($b['customer_phone'])): ?>
                                        <small class="text-muted d-block" style="font-size:12px;margin-top:2px;">
                                            <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($b['customer_phone']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="due-date" style="white-space:nowrap;">
                                <?= format_date($b['start_date']) ?>
                                <span style="color:#94a3b8;margin:0 2px;">&rarr;</span>
                                <?= format_date($b['end_date']) ?>
                            </span>
                            <small class="text-muted d-block" style="font-size:11px;margin-top:2px;">
                                <i class="fa-solid fa-users" style="font-size:10px;"></i> <?= (int)$b['passenger_count'] ?> Guests
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:800;color:var(--brand-secondary, #16443B);font-size:13.5px;">
                                <?= format_currency((float)$b['total_amount']) ?>
                            </div>
                            <div style="margin-top:3px;">
                                <?= render_status_badge($b['payment_status'] ?? 'unpaid') ?>
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
                                    <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="action-btn-table view" title="View Manifest">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?= url('/office/bookings/' . $b['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Booking">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= url('/office/dispatch/create?booking_id=' . $b['id']) ?>" class="action-btn-table dispatch" title="Dispatch Assignment">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No bookings found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- =========================================================================
             Mobile Booking Cards List (Exact Screen 3 Design from mobileview.jpeg)
             ========================================================================= -->
        <div class="cards-grid" id="bookingsCards">
            <?php
            $presetImgs = [
                'destination-goa.jpg',
                'destination-manali.jpg',
                'destination-dubai.jpg',
                'destination-kerala.jpg',
                'destination-rajasthan.jpg',
                'explore-bus.jpg'
            ];
            foreach ($bookings as $idx => $b):
                $status = $b['status'] ?? 'pending';
                $statusText = ucfirst(str_replace('_', ' ', $status));
                $statusClass = match($status) {
                    'completed' => 'completed',
                    'confirmed', 'in_progress' => 'confirmed',
                    'assigned' => 'assigned',
                    'cancelled' => 'cancelled',
                    default => 'pending'
                };

                // Map thumbnail image from tour title or hash
                $titleLower = strtolower($b['tour_title'] ?? '');
                if (str_contains($titleLower, 'manali') || str_contains($titleLower, 'himachal')) {
                    $cardImg = 'destination-manali.jpg';
                } elseif (str_contains($titleLower, 'dubai')) {
                    $cardImg = 'destination-dubai.jpg';
                } elseif (str_contains($titleLower, 'kerala')) {
                    $cardImg = 'destination-kerala.jpg';
                } elseif (str_contains($titleLower, 'rajasthan') || str_contains($titleLower, 'jaipur') || str_contains($titleLower, 'udaipur')) {
                    $cardImg = 'destination-rajasthan.jpg';
                } elseif (str_contains($titleLower, 'goa')) {
                    $cardImg = 'destination-goa.jpg';
                } else {
                    $cardImg = $presetImgs[($b['id'] ?? $idx) % count($presetImgs)];
                }

                // Calculate duration and dates
                $startDate = !empty($b['start_date']) ? new DateTime($b['start_date']) : null;
                $endDate   = !empty($b['end_date'])   ? new DateTime($b['end_date'])   : null;
                $durationDays = ($startDate && $endDate) ? max(1, $startDate->diff($endDate)->days + 1) : 1;
                $formattedDate = $startDate ? $startDate->format('d M Y') : 'Flexible Date';
                $durationText  = $durationDays . ' Day' . ($durationDays > 1 ? 's' : '');
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="mv-booking-card">
                    <!-- 1. Left: 68x68 rounded destination thumbnail -->
                    <div class="mv-thumb-wrap">
                        <img src="<?= asset('images/' . $cardImg) ?>" alt="<?= e($b['tour_title'] ?? 'Tour') ?>" class="mv-thumb-img" loading="lazy">
                    </div>

                    <!-- 2. Middle: Code, Tour Title, Date • Duration, Passenger, Vehicle -->
                    <div class="mv-details">
                        <div class="mv-code">#<?= e($b['booking_code']) ?></div>
                        <div class="mv-tour-title" title="<?= e($b['tour_title'] ?? 'Custom Route Itinerary') ?>">
                            <?= e($b['tour_title'] ?? 'Custom Route Itinerary') ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span><?= $formattedDate ?> • <?= $durationText ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-regular fa-user"></i>
                            <span><?= e($b['customer_name']) ?></span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-van-shuttle"></i>
                            <span><?= !empty($b['vehicle_type']) ? e($b['vehicle_type']) : ((int)$b['passenger_count'] . ' Guests') ?></span>
                        </div>
                    </div>

                    <!-- 3. Right: Status pill top, Total Fare bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price"><?= format_currency((float)$b['total_amount']) ?></div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="action-btn-table view" title="View Manifest">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="<?= url('/office/bookings/' . $b['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Booking">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="<?= url('/office/dispatch/create?booking_id=' . $b['id']) ?>" class="action-btn-table dispatch" title="Dispatch Assignment">
                            <i class="fa-solid fa-paper-plane"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($bookings)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No bookings found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact PMS Pagination Architecture) -->
    <?php
        $from = $totalRecords > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $to   = min($currentPage * $perPage, $totalRecords);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $sort, $perPage) {
            $q = [];
            if (!empty($search)) $q['q'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($sort) && $sort !== 'created_desc') $q['sort'] = $sort;
            if (!empty($perPage) && $perPage != 10) $q['per_page'] = $perPage;
            $q['page'] = $p;
            return url('/office/bookings?' . http_build_query($q));
        };
    ?>
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totalRecords ?></span> booking(s)</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currentPage - 1)) ?>" class="<?= $currentPage <= 1 ? 'disabled' : '' ?>" title="Previous Page">&#10094;</a>
            <?php
                $rangeStart = max(1, $currentPage - 1);
                $rangeEnd   = min($totalPages, $rangeStart + 2);
                if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                    $rangeStart = max(1, $rangeEnd - 2);
                }
                for ($i = $rangeStart; $i <= $rangeEnd; $i++):
            ?>
                <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($rangeEnd < $totalPages): ?>
                <span class="dots">...</span>
                <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currentPage === $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currentPage + 1)) ?>" class="<?= $currentPage >= $totalPages ? 'disabled' : '' ?>" title="Next Page">&#10095;</a>
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
