<?php
/**
 * My Assigned Trips View — Dedicated Driver Portal
 * Executive UI Styled after Owner Dashboard Customer Directory (app/Views/office/customers/index.php)
 */

$currStatus = strtolower($status ?? '');
$totRec     = (int)($total ?? count($trips));
$currP      = (int)($page ?? 1);
$pSize      = (int)($perPage ?? 10);
$from       = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to         = min($currP * $pSize, $totRec);

$buildFilterUrl = function(array $overrides = []) use ($search, $status, $date, $pSize) {
    $q = [];
    $s  = array_key_exists('search', $overrides) ? $overrides['search'] : $search;
    $st = array_key_exists('status', $overrides) ? $overrides['status'] : $status;
    $dt = array_key_exists('date', $overrides) ? $overrides['date'] : $date;
    $pp = array_key_exists('per_page', $overrides) ? $overrides['per_page'] : $pSize;
    $pg = $overrides['page'] ?? null;

    if (!empty($s))  $q['search']   = $s;
    if (!empty($st)) $q['status']   = $st;
    if (!empty($dt)) $q['date']     = $dt;
    if (!empty($pp) && $pp != 10)   $q['per_page'] = $pp;
    if (!empty($pg) && $pg > 1)     $q['page']     = $pg;

    return url('/driver/trips' . (!empty($q) ? '?' . http_build_query($q) : ''));
};
?>

<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Exact Customer Directory Architecture) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('sidebar.assigned_trips', 'My Assigned Trips'),
            'description' => __('driver.current_assignment', 'Active transit operations, client pickups, and scheduled fleet dispatches assigned to your chauffeur profile.'),
            'image'       => 'header-dispatch.jpg',
            'badge'       => 'Active Duty',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => __('sidebar.assigned_trips', 'Assigned Trips'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/driver/history') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-clock-rotate-left" style="margin-right:6px;"></i><span>Trip History</span></a>' .
                             '<a href="' . url('/driver/dashboard') . '" class="btn btn-primary" style="margin-left:8px; border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Console</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Customer Directory Architecture) -->
    <div class="booking-kpi-grid" id="driverTripsKpiGrid">
        <!-- Card 1: Total Assigned Trips -->
        <div class="booking-kpi-card <?= empty($currStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all active assignments">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Assigned</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totRec) ?>"><?= number_format($totalAll ?? $totRec) ?></div>
            <div class="booking-kpi-sub">
                <span>All active chauffeur manifests</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Awaiting Acceptance -->
        <div class="booking-kpi-card <?= $currStatus === 'assigned' ? 'is-selected' : '' ?>" data-status="assigned" role="button" tabindex="0" title="Click to filter newly assigned trips awaiting acceptance">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Awaiting Acceptance</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAssigned ?? 0) ?>"><?= number_format($totalAssigned ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Requires chauffeur confirmation</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Accepted & Ready -->
        <div class="booking-kpi-card <?= $currStatus === 'driver_accepted' ? 'is-selected' : '' ?>" data-status="driver_accepted" role="button" tabindex="0" title="Click to filter accepted trips ready for departure">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Accepted &amp; Ready</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAccepted ?? 0) ?>"><?= number_format($totalAccepted ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot" style="background:#10B981;"></span>
                <span>Staged for passenger pickup</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: In Transit / Active -->
        <div class="booking-kpi-card <?= $currStatus === 'trip_started' ? 'is-selected' : '' ?>" data-status="trip_started" role="button" tabindex="0" title="Click to filter live in-transit trips">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">In Transit</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalStarted ?? 0) ?>"><?= number_format($totalStarted ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Live in-transit missions</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Customer Directory Architecture) -->
    <form method="GET" action="<?= url('/driver/trips') ?>" class="filter-bar-compact" id="driverTripsFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search trips by booking code, passenger, route, vehicle..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($currStatus) ? 'selected' : '' ?>>All Statuses</option>
            <option value="assigned" <?= $currStatus === 'assigned' ? 'selected' : '' ?>>Awaiting Acceptance</option>
            <option value="driver_accepted" <?= $currStatus === 'driver_accepted' ? 'selected' : '' ?>>Accepted &amp; Ready</option>
            <option value="trip_started" <?= $currStatus === 'trip_started' ? 'selected' : '' ?>>In Transit</option>
        </select>

        <input type="date" name="date" id="dateInput" class="form-control" value="<?= e($date ?? '') ?>" style="font-size:0.85rem; padding: 0.5rem 0.75rem; border: 1.5px solid #CBD5E1; border-radius: 10px;" title="Filter by Pickup Date">

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $pSize == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Apply Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($status) || !empty($date)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Assigned Trips</h2>
            </div>
            <a href="<?= url('/driver/history') ?>" class="mv-add-btn">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>History</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/driver/trips') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search by code, guest, route..." value="<?= e($search ?? '') ?>" autocomplete="off">
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
                <div class="mv-filter-row" style="margin-bottom: 0.75rem;">
                    <label for="mvDateInput" style="font-weight: 600; font-size: 0.82rem; color: #475569;">Date:</label>
                    <input type="date" name="date" id="mvDateInput" class="form-control" value="<?= e($date ?? '') ?>" style="font-size: 0.85rem; padding: 0.4rem 0.65rem;">
                </div>
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $pSize == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeStatusUrl = function(string $st) use ($search, $pSize, $date) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($date)) $p['date'] = $date;
                if (!empty($pSize) && $pSize != 10) $p['per_page'] = $pSize;
                return url('/driver/trips' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="driverTripsMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span>All Active</span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? $totRec) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('assigned') ?>" class="mv-pill <?= $currStatus === 'assigned' ? 'active' : '' ?>" data-status="assigned" data-no-ajax="true">
                <span>Awaiting</span>
                <span class="mv-pill-cnt"><?= (int)($totalAssigned ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('driver_accepted') ?>" class="mv-pill <?= $currStatus === 'driver_accepted' ? 'active' : '' ?>" data-status="driver_accepted" data-no-ajax="true">
                <span>Accepted</span>
                <span class="mv-pill-cnt"><?= (int)($totalAccepted ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('trip_started') ?>" class="mv-pill <?= $currStatus === 'trip_started' ? 'active' : '' ?>" data-status="trip_started" data-no-ajax="true">
                <span>In Transit</span>
                <span class="mv-pill-cnt"><?= (int)($totalStarted ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Assigned Trips Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="tripsSection">
        <div class="section-header">
            <h3 class="section-title">All Assigned Trips</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Strict 8-Column Executive Layout matching Customer Directory) -->
        <div class="modern-table-container">
            <table class="modern-table" id="tripsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th class="text-center" style="width: 80px;">Profile</th>
                        <th style="min-width: 220px;">Trip Reference</th>
                        <th style="min-width: 170px;">Passenger / Contact</th>
                        <th style="min-width: 240px;">Route Itinerary</th>
                        <th style="min-width: 160px;">Assigned Fleet</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tripsTableBody">
                    <?php if (empty($trips)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trips assigned yet</h4>
                                <p class="text-muted" style="margin:0;">No assigned journeys match your current search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($trips as $i => $t):
                            $serial = (($currP - 1) * $pSize) + $i + 1;
                            $st = strtolower($t['status'] ?? 'assigned');
                            $statusPillClass = match($st) {
                                'assigned'        => 'warning',
                                'driver_accepted' => 'info',
                                'trip_started'    => 'completed',
                                default           => 'cancelled'
                            };
                            $statusLabel = match($st) {
                                'assigned'        => 'Awaiting',
                                'driver_accepted' => 'Accepted',
                                'trip_started'    => 'In Transit',
                                default           => ucfirst(str_replace('_', ' ', $st))
                            };
                            $initial = strtoupper(mb_substr($t['customer_name'] ?? 'G', 0, 1));
                        ?>
                        <tr>
                            <!-- 1. Serial Number (#) -->
                            <td class="text-center">
                                <div class="cust-serial-num"><?= $serial ?></div>
                            </td>

                            <!-- 2. Profile Image / Monogram -->
                            <td class="text-center">
                                <div class="cust-table-avatar-wrap">
                                    <div class="cust-table-monogram"><?= e($initial) ?></div>
                                </div>
                            </td>

                            <!-- 3. Trip Reference & Schedule -->
                            <td>
                                <div class="cust-client-details-cell">
                                    <div class="cust-name-row">
                                        <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="cust-full-name" title="View trip itinerary details">
                                            <?= e($t['booking_code']) ?>
                                        </a>
                                        <span class="cust-code-pill"><?= e($t['dispatch_code']) ?></span>
                                    </div>
                                    <div class="cust-email-row">
                                        <i class="fa-solid fa-calendar-day cust-email-icon"></i>
                                        <span><?= format_date($t['start_date']) ?> &bull; <?= e($t['pickup_time'] ? date('h:i A', strtotime($t['pickup_time'])) : 'Scheduled') ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Passenger / Mobile No. -->
                            <td>
                                <div class="cust-phone-cell">
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-user" style="color: #64748B; font-size: 0.76rem;"></i>
                                        <span><?= e($t['customer_name'] ?? 'VIP Guest') ?></span>
                                    </div>
                                    <?php if (!empty($t['customer_phone'])): ?>
                                        <a href="tel:<?= e($t['customer_phone']) ?>" class="cust-primary-phone" title="Call passenger">
                                            <i class="fa-solid fa-phone cust-phone-icon"></i>
                                            <span><?= e($t['customer_phone']) ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- 5. Route Itinerary (From & To) -->
                            <td>
                                <div class="cust-address-cell">
                                    <div class="cust-address-line" title="<?= e($t['pickup_address']) ?>">
                                        <span style="color: #10B981; font-weight: 900; margin-right: 4px;">●</span>
                                        <span><strong>From:</strong> <?= e($t['pickup_address']) ?></span>
                                    </div>
                                    <div class="cust-address-sub" title="<?= e($t['drop_address']) ?>">
                                        <span style="color: #EF4444; font-weight: 900; margin-right: 4px;">●</span>
                                        <span><strong>To:</strong> <?= e($t['drop_address']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Assigned Fleet -->
                            <td>
                                <div class="cust-bookings-cell" style="align-items: flex-start;">
                                    <div style="font-weight: 800; color: #16443B; font-size: 0.92rem; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-car" style="color: #176B5B; font-size: 0.8rem;"></i>
                                        <span><?= e($t['vehicle_number']) ?></span>
                                    </div>
                                    <span class="cust-bookings-lbl" style="text-transform: none; font-size: 0.74rem;"><?= e($t['make_model']) ?></span>
                                </div>
                            </td>

                            <!-- 7. Status Pill with pulse dot -->
                            <td class="text-center">
                                <span class="cust-toggle-pill <?= $statusPillClass ?>">
                                    <span class="toggle-pulse-dot <?= $statusPillClass ?>"></span>
                                    <span class="toggle-label-text"><?= $statusLabel ?></span>
                                </span>
                            </td>

                            <!-- 8. Actions Menu (3-Dots Popup) -->
                            <td>
                                <div class="table-action-buttons">
                                    <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="action-btns-popup">
                                        <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="action-btn-table view" title="View Itinerary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($st === 'assigned'): ?>
                                            <form action="<?= url('/driver/trips/' . $t['id'] . '/accept') ?>" method="POST" style="margin:0; display:inline;" data-no-ajax="true">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="action-btn-table dispatch" style="border:none; cursor:pointer;" title="Accept Mission">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!empty($t['customer_phone'])): ?>
                                            <a href="tel:<?= e($t['customer_phone']) ?>" class="action-btn-table edit" title="Call Passenger">
                                                <i class="fa-solid fa-phone"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Booking Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="tripsCards">
            <?php if (empty($trips)): ?>
                <div class="no-data-message" style="background:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0; padding:2rem 1rem;">
                    <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trips assigned yet</h4>
                    <p class="text-muted" style="margin:0;">No assigned journeys match your current search or filter parameters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($trips as $t):
                    $st = strtolower($t['status'] ?? 'assigned');
                    $statusPillClass = match($st) {
                        'assigned'        => 'warning',
                        'driver_accepted' => 'info',
                        'trip_started'    => 'completed',
                        default           => 'cancelled'
                    };
                    $statusLabel = match($st) {
                        'assigned'        => 'Awaiting',
                        'driver_accepted' => 'Accepted',
                        'trip_started'    => 'In Transit',
                        default           => ucfirst(str_replace('_', ' ', $st))
                    };
                    $initial = strtoupper(mb_substr($t['customer_name'] ?? 'G', 0, 1));
                ?>
                <div class="mv-booking-card-wrapper">
                    <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="mv-booking-card">
                        <!-- Left: Avatar Monogram -->
                        <div class="mv-thumb-wrap">
                            <div class="mv-thumb-avatar">
                                <?= e($initial) ?>
                            </div>
                        </div>

                        <!-- Middle: Details -->
                        <div class="mv-details">
                            <div class="mv-tour-title" title="<?= e($t['booking_code']) ?>">
                                <?= e($t['booking_code']) ?>
                                <span class="cust-code-pill" style="margin-left: 4px;"><?= e($t['dispatch_code']) ?></span>
                            </div>
                            <div class="mv-date-duration">
                                <i class="fa-solid fa-user"></i>
                                <span><?= e($t['customer_name'] ?? 'Guest') ?></span>
                                <?php if (!empty($t['customer_phone'])): ?>
                                    &bull; <span><?= e($t['customer_phone']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mv-passenger" style="color:#059669;">
                                <i class="fa-solid fa-location-dot" style="color:#10B981;"></i>
                                <span>From: <?= e($t['pickup_address']) ?></span>
                            </div>
                            <div class="mv-passenger" style="color:#DC2626;">
                                <i class="fa-solid fa-location-dot" style="color:#EF4444;"></i>
                                <span>To: <?= e($t['drop_address']) ?></span>
                            </div>
                            <div class="mv-vehicle">
                                <i class="fa-solid fa-car"></i>
                                <span><?= e($t['vehicle_number']) ?> &bull; <?= format_date($t['start_date']) ?></span>
                            </div>
                        </div>

                        <!-- Right: Status Pill & Time -->
                        <div class="mv-right-col">
                            <span class="cust-toggle-pill <?= $statusPillClass ?>" style="padding:2px 7px;">
                                <span class="toggle-pulse-dot <?= $statusPillClass ?>"></span>
                                <span class="toggle-label-text"><?= $statusLabel ?></span>
                            </span>
                            <div class="mv-price"><?= e($t['pickup_time'] ? date('h:i A', strtotime($t['pickup_time'])) : 'Scheduled') ?></div>
                        </div>
                    </a>

                    <!-- 3-Dots Quick Action Popup -->
                    <div class="mv-card-actions">
                        <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="card-btns-popup">
                            <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="action-btn-table view" title="View Itinerary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <?php if ($st === 'assigned'): ?>
                                <form action="<?= url('/driver/trips/' . $t['id'] . '/accept') ?>" method="POST" style="margin:0; display:inline;" data-no-ajax="true">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="action-btn-table dispatch" style="border:none; cursor:pointer;" title="Accept">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if (!empty($t['customer_phone'])): ?>
                                <a href="tel:<?= e($t['customer_phone']) ?>" class="action-btn-table edit" title="Call">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Customer Directory Architecture) -->
    <?php
        $buildPageUrl = function(int $p) use ($search, $status, $date, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($status)) $q['status'] = $status;
            if (!empty($date))   $q['date']   = $date;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/driver/trips?' . http_build_query($q));
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
    /* Complete Live AJAX Filter Engine (Without Page Reload)                     */
    /* ========================================================================= */
    (function initDriverTripsAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/driver/trips') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const dateInput = document.getElementById('dateInput');
            const mvDateInput = document.getElementById('mvDateInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const dtVal = (dateInput ? dateInput.value : (mvDateInput ? mvDateInput.value : '')).trim();
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (dtVal) p.set('date', dtVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, dateVal: dtVal, perPageVal: ppVal, pageNum: page };
        }

        function filterTripsAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) {
                abortCtrl.abort();
            }
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

                // 1. Update Trips Section (Table, mobile cards, empty state)
                const newSection = doc.getElementById('tripsSection');
                const currSection = document.getElementById('tripsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                // 2. Update Pagination Wrapper
                const newPagination = doc.getElementById('tripsPaginationWrapper');
                const currPagination = document.getElementById('tripsPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                // 3. Update Top KPI Summary Grid
                const newKpi = doc.getElementById('driverTripsKpiGrid');
                const currKpi = document.getElementById('driverTripsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                // 4. Update Mobile Status Scroll Pills
                const newMvPills = doc.getElementById('driverTripsMvStatusScroll');
                const currMvPills = document.getElementById('driverTripsMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                // 5. Update Clear Button in filter bar
                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                try {
                    window.history.replaceState({ path: targetUrl }, '', targetUrl);
                } catch (e) {}
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Trips AJAX filter failed:', err);
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
            const vals = container.querySelectorAll('.booking-kpi-val[data-count]');
            vals.forEach(el => {
                const target = parseInt(el.getAttribute('data-count'), 10) || 0;
                const duration = 300;
                const startTime = performance.now();
                function step(now) {
                    const progress = Math.min((now - startTime) / duration, 1);
                    const current = Math.round(progress * target);
                    el.textContent = current.toLocaleString();
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        el.textContent = target.toLocaleString();
                    }
                }
                requestAnimationFrame(step);
            });
        }

        // Live Debounced Search
        const searchInput = document.getElementById('searchInput');
        const mvSearchInput = document.getElementById('mvSearchInput');

        function handleSearchInput(val) {
            if (searchInput && searchInput.value !== val) searchInput.value = val;
            if (mvSearchInput && mvSearchInput.value !== val) mvSearchInput.value = val;

            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
            if (searchClearBtn) searchClearBtn.style.display = val ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = val ? 'inline-flex' : 'none';

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterTripsAjax(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }
        if (mvSearchInput) {
            mvSearchInput.addEventListener('input', (e) => handleSearchInput(e.target.value));
        }

        // Search Clear buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('#searchClearBtn') || e.target.closest('#mvSearchClearBtn')) {
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                const searchClearBtn = document.getElementById('searchClearBtn');
                const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
                if (searchClearBtn) searchClearBtn.style.display = 'none';
                if (mvSearchClearBtn) mvSearchClearBtn.style.display = 'none';
                filterTripsAjax(1);
            }
        });

        // Dropdown changes
        const statusInput = document.getElementById('statusInput');
        const dateInput = document.getElementById('dateInput');
        const perPageInput = document.getElementById('perPageInput');
        const mvPerPageSelect = document.getElementById('mvPerPageSelect');

        if (statusInput) statusInput.addEventListener('change', () => filterTripsAjax(1));
        if (dateInput) dateInput.addEventListener('change', () => filterTripsAjax(1));
        if (perPageInput) {
            perPageInput.addEventListener('change', () => {
                if (mvPerPageSelect) mvPerPageSelect.value = perPageInput.value;
                filterTripsAjax(1);
            });
        }
        if (mvPerPageSelect) {
            mvPerPageSelect.addEventListener('change', () => {
                if (perPageInput) perPageInput.value = mvPerPageSelect.value;
                filterTripsAjax(1);
            });
        }

        // Apply and Refresh buttons
        const applyBtn = document.getElementById('filterApplyBtn');
        const refreshBtn = document.getElementById('refreshBtn');

        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterTripsAjax(1);
            });
        }
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (dateInput) dateInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                filterTripsAjax(1);
            });
        }

        // KPI Card click delegation
        document.addEventListener('click', function(e) {
            const card = e.target.closest('.booking-kpi-card');
            if (card && card.closest('#driverTripsKpiGrid')) {
                const targetStatus = card.getAttribute('data-status') ?? '';
                if (statusInput) statusInput.value = targetStatus;
                filterTripsAjax(1);
            }
        });

        // Mobile Status Pills click delegation
        document.addEventListener('click', function(e) {
            const pill = e.target.closest('.mv-pill');
            if (pill && pill.closest('#driverTripsMvStatusScroll')) {
                e.preventDefault();
                const targetStatus = pill.getAttribute('data-status') ?? '';
                if (statusInput) statusInput.value = targetStatus;
                filterTripsAjax(1);
            }
        });

        // Pagination delegation
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#tripsPaginationWrapper .pagination a[data-page]');
            if (pageLink && !pageLink.classList.contains('disabled')) {
                e.preventDefault();
                const p = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
                filterTripsAjax(p);
                const section = document.getElementById('tripsSection');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        // Clear Filters button delegation
        document.addEventListener('click', function(e) {
            if (e.target.closest('#filterClearBtn')) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (dateInput) dateInput.value = '';
                filterTripsAjax(1);
            }
        });
    })();

    /* ========================================================================= */
    /* 3-Dots Action Popups Toggle & Mobile Filters                              */
    /* ========================================================================= */
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
/* ========================================================================= */
/* Sleek AJAX Filtering Animations & Loading State                           */
/* ========================================================================= */
.recent-bookings-section {
    position: relative;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), filter 0.22s ease;
}

.recent-bookings-section.is-filtering {
    opacity: 0.42;
    pointer-events: none;
    filter: grayscale(0.2);
}

.fbc-search {
    position: relative;
    display: flex;
    align-items: center;
}

.fbc-search-clear {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border-radius: 50%;
    transition: color 0.15s ease, background-color 0.15s ease;
}

.fbc-search-clear:hover {
    color: #0F172A;
    background: rgba(0, 0, 0, 0.06);
}

.mv-search-input-box {
    position: relative;
    display: flex;
    align-items: center;
}

.mv-search-clear {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

/* ========================================================================= */
/* Top KPI Summary Cards (Matching Driver History Design)                    */
/* ========================================================================= */
.booking-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.booking-kpi-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.booking-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #CBD5E1;
}

.booking-kpi-card.is-selected {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.booking-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.booking-kpi-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.booking-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-teal    { background: #ECFDF5; color: #059669; }
.icon-emerald { background: #E0F2FE; color: #0284C7; }
.icon-amber   { background: #FEF3C7; color: #D97706; }
.icon-navy    { background: #F1F5F9; color: #475569; }

.booking-kpi-val {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.booking-kpi-sub {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #64748B;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #F59E0B;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25);
}

.kpi-filter-tag {
    color: #94A3B8;
    font-size: 0.75rem;
}

/* ========================================================================= */
/* Status Pills with Glowing Pulse Dots                                      */
/* ========================================================================= */
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.warning {
    background: #FEF3C7;
    color: #D97706;
}

.cust-toggle-pill.info {
    background: #E0F2FE;
    color: #0284C7;
}

.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.toggle-pulse-dot.completed {
    background: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
}

.toggle-pulse-dot.warning {
    background: #D97706;
    box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.25);
}

.toggle-pulse-dot.info {
    background: #0284C7;
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}

/* ========================================================================= */
/* Executive PMS 8-Column Layout Styling (Exact Customer Directory Match)    */
/* ========================================================================= */
.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    font-size: 0.88rem;
    padding: 3px 6px;
}

/* 1. Profile Avatar & Monogram */
.cust-table-avatar-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 44px;
    height: 44px;
}

.cust-table-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.18);
    background: #E8F4F0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cust-table-avatar-img:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.3);
}

.cust-table-monogram {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    text-transform: uppercase;
}

/* Mobile Thumb Avatar */
.mv-thumb-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 1.05rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.2);
}

/* 2. Client Details Cell */
.cust-client-details-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cust-name-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cust-full-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0C2B52;
    text-decoration: none;
    transition: color 0.15s ease;
}
.cust-full-name:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-code-pill {
    display: inline-flex;
    align-items: center;
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 1px 7px;
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.cust-email-row {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #64748B;
}

.cust-email-icon {
    font-size: 0.78rem;
    color: #94A3B8;
}

/* 3. Mobile Number Cell */
.cust-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.cust-primary-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    transition: color 0.15s ease;
}
.cust-primary-phone:hover {
    color: #176B5B;
}

.cust-phone-icon {
    font-size: 0.76rem;
    color: #176B5B;
}

/* 4. Bookings Cell */
.cust-bookings-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cust-bookings-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* 5. Address Column Cell */
.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #334155;
    max-width: 280px;
}

.cust-address-line {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    font-weight: 600;
    color: #0F172A;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}

.cust-address-sub {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.3;
}

@media (max-width: 1024px) {
    .booking-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container {
        display: none !important;
    }
    .mv-bookings-header {
        display: block !important;
    }
    .cards-grid {
        display: flex !important;
        flex-direction: column;
        gap: 12px;
    }
}

@media (min-width: 769px) {
    .mv-bookings-header,
    .cards-grid {
        display: none !important;
    }
    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container {
        display: block !important;
    }
}
</style>
