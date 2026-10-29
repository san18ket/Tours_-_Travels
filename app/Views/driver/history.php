<?php
/**
 * Driver Trip History View — Dedicated Driver Portal
 * Executive UI Architecture following Owner Dashboard Customer Directory standard (app/Views/office/customers/index.php)
 */

$search         = $search ?? '';
$selectedStatus = $selectedStatus ?? $status ?? '';
$currStatus     = strtolower($selectedStatus);
$totRec         = (int)($totalHistoryAll ?? $totalAll ?? $totalCustomers ?? $total ?? count($history ?? $trips ?? []));
$currP          = (int)($currentPage ?? $page ?? 1);
$pSize          = (int)($perPage ?? 10);
$from           = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
$to             = min($currP * $pSize, $totRec);

$buildPageUrl = function(int $p) use ($search, $selectedStatus, $pSize) {
    $q = [];
    if (!empty($search)) $q['search'] = $search;
    if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
    if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
    $q['page'] = $p;
    return url('/driver/history?' . http_build_query($q));
};
?>

<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Exact Executive Customer Directory Architecture) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Trip History & Archives',
            'description' => 'Comprehensive verified digital ledger of completed chauffeur missions, passenger transit manifests, route itineraries, and odometer telemetry.',
            'image'       => 'header-dispatch.jpg',
            'badge'       => 'Archive Ledger',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Trip History', 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-calendar-check" style="margin-right:6px;"></i><span>Active Trips</span></a>' .
                             '<a href="' . url('/driver/dashboard') . '" class="btn btn-outline" style="margin-left:8px; border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Console</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact 4-Card Executive Grid) -->
    <div class="booking-kpi-grid" id="customersKpiGrid">
        <!-- Card 1: Total History Records -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all archived journeys">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total History</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalHistoryAll ?? $totalAll ?? $totRec) ?>"><?= number_format($totalHistoryAll ?? $totalAll ?? $totRec) ?></div>
            <div class="booking-kpi-sub">
                <span>All past logged journeys</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Completed Journeys -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'completed' || ($selectedStatus ?? '') === 'active' ? 'is-selected' : '' ?>" data-status="completed" role="button" tabindex="0" title="Click to filter completed journeys">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Completed Missions</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalCompleted ?? $totalActive ?? 0) ?>"><?= number_format($totalCompleted ?? $totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Successfully executed</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Total Distance Driven -->
        <div class="booking-kpi-card" data-status="" role="button" tabindex="0" title="Total distance driven across completed missions">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Distance Driven</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalDistanceKm ?? 0) ?>"><?= number_format($totalDistanceKm ?? 0) ?> <span style="font-size:0.95rem; font-weight:700; color:#64748B;">km</span></div>
            <div class="booking-kpi-sub">
                <span>Verified telemetry logs</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-road"></i></span>
            </div>
        </div>

        <!-- Card 4: Cancelled Records -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'cancelled' || ($selectedStatus ?? '') === 'inactive' ? 'is-selected' : '' ?>" data-status="cancelled" role="button" tabindex="0" title="Click to filter cancelled trip records">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Cancelled Records</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalCancelled ?? $totalInactive ?? 0) ?>"><?= number_format($totalCancelled ?? $totalInactive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Voided or aborted trips</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Architecture from Customer Directory) -->
    <form method="GET" action="<?= url('/driver/history') ?>" class="filter-bar-compact" id="customerFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by booking code, route, passenger, vehicle..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>>All Past Statuses</option>
            <option value="completed" <?= ($selectedStatus ?? '') === 'completed' || ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($selectedStatus ?? '') === 'cancelled' || ($selectedStatus ?? '') === 'inactive' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="<?= e(__('btn.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Trip History</h2>
            </div>
            <a href="<?= url('/driver/trips') ?>" class="mv-add-btn">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Active Trips</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/driver/history') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
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
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/driver/history' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="customersMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span>All Past</span>
                <span class="mv-pill-cnt"><?= (int)($totalHistoryAll ?? $totalAll ?? $totRec) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('completed') ?>" class="mv-pill <?= $currStatus === 'completed' || $currStatus === 'active' ? 'active' : '' ?>" data-status="completed" data-no-ajax="true">
                <span>Completed</span>
                <span class="mv-pill-cnt"><?= (int)($totalCompleted ?? $totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('cancelled') ?>" class="mv-pill <?= $currStatus === 'cancelled' || $currStatus === 'inactive' ? 'active' : '' ?>" data-status="cancelled" data-no-ajax="true">
                <span>Cancelled</span>
                <span class="mv-pill-cnt"><?= (int)($totalCancelled ?? $totalInactive ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All History Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="customersSection">
        <div class="section-header">
            <h3 class="section-title">Archived Transit Manifests</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totRec) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Strict 8-Column Executive Layout matching Customer Directory) -->
        <div class="modern-table-container">
            <table class="modern-table" id="customersTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th class="text-center" style="width: 80px;">Guest</th>
                        <th style="min-width: 220px;">Trip &amp; Passenger</th>
                        <th style="min-width: 160px;">Contact</th>
                        <th style="min-width: 240px;">Route Itinerary</th>
                        <th class="text-center" style="width: 140px;">Assigned Fleet</th>
                        <th class="text-center" style="width: 140px;">Status &amp; Telemetry</th>
                        <th class="text-center" style="width: 75px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <?php foreach (($history ?? []) as $i => $c):
                        $serial = (($currP - 1) * $pSize) + $i + 1;
                        $st = strtolower($c['status'] ?? 'completed');
                        $isActive = ($st === 'completed');
                        $statusClass = $isActive ? 'completed' : 'cancelled';
                        $statusText = match($st) {
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            default     => ucfirst($st)
                        };
                        $custName = $c['customer_name'] ?? 'VIP Guest';
                        $initial = strtoupper(mb_substr($custName, 0, 1));
                        $avatarUrl = !empty($c['customer_avatar']) ? customer_avatar_url($c['customer_avatar']) : null;
                        $bookingCode = $c['booking_code'] ?? '';
                        $dispatchCode = $c['dispatch_code'] ?? '';
                        $phone = $c['customer_phone'] ?? '';
                        $email = $c['customer_email'] ?? '';
                        $pickupAddress = $c['pickup_address'] ?? 'Pickup point';
                        $dropAddress = $c['drop_address'] ?? 'Drop point';
                        $vehicleNumber = $c['vehicle_number'] ?? 'Fleet';
                        $makeModel = $c['make_model'] ?? 'Vehicle';
                        $startOdo = (int)($c['start_odometer'] ?? 0);
                        $endOdo   = (int)($c['end_odometer'] ?? 0);
                        $diff     = ($endOdo >= $startOdo && $startOdo > 0) ? ($endOdo - $startOdo) : 0;
                    ?>
                    <tr>
                        <!-- 1. Serial Number (#) -->
                        <td class="text-center">
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>

                        <!-- 2. Profile Image / Monogram -->
                        <td class="text-center">
                            <div class="cust-table-avatar-wrap">
                                <?php if (!empty($avatarUrl)): ?>
                                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($custName) ?>" class="cust-table-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="cust-table-monogram" style="display: none;"><?= e($initial) ?></div>
                                <?php else: ?>
                                    <div class="cust-table-monogram"><?= e($initial) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Trip & Passenger Details -->
                        <td>
                            <div class="cust-client-details-cell">
                                <div class="cust-name-row">
                                    <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="cust-full-name" title="View trip manifest">
                                        <?= e($custName) ?>
                                    </a>
                                    <span class="cust-code-pill" title="Booking Reference Code"><?= e($bookingCode) ?></span>
                                </div>
                                <div class="cust-email-row">
                                    <i class="fa-solid fa-hashtag" style="font-size:0.75rem; color:#94A3B8;"></i>
                                    <span class="cust-dispatch-tag"><?= e($dispatchCode) ?></span>
                                    <span class="cust-date-tag">&bull; <?= format_date($c['start_date'] ?? date('Y-m-d')) ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- 4. Contact & Passengers -->
                        <td>
                            <div class="cust-phone-cell">
                                <?php if (!empty($phone)): ?>
                                    <a href="tel:<?= e($phone) ?>" class="cust-primary-phone" title="Call passenger">
                                        <i class="fa-solid fa-phone cust-phone-icon"></i>
                                        <span><?= e($phone) ?></span>
                                    </a>
                                <?php else: ?>
                                    <span class="cust-no-email">No phone on record</span>
                                <?php endif; ?>
                                <div class="cust-alt-phone" title="Passenger Capacity">
                                    <i class="fa-solid fa-users" style="font-size:0.75rem; color:#64748B;"></i>
                                    <span><?= (int)($c['passenger_count'] ?? 1) ?> Passenger<?= ((int)($c['passenger_count'] ?? 1) > 1) ? 's' : '' ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- 5. Route Itinerary -->
                        <td>
                            <div class="cust-address-cell">
                                <div class="cust-address-line" title="Pickup: <?= e($pickupAddress) ?>">
                                    <i class="fa-solid fa-location-dot" style="color: #10B981; font-size: 0.82rem; flex-shrink: 0;"></i>
                                    <span><?= e($pickupAddress) ?></span>
                                </div>
                                <div class="cust-address-sub" title="Destination: <?= e($dropAddress) ?>">
                                    <i class="fa-solid fa-flag-checkered" style="color: #EF4444; font-size: 0.76rem; flex-shrink: 0;"></i>
                                    <span><?= e($dropAddress) ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- 6. Assigned Fleet -->
                        <td class="text-center">
                            <div class="cust-bookings-cell">
                                <span class="fleet-plate-badge"><?= e($vehicleNumber) ?></span>
                                <span class="cust-bookings-lbl"><?= e($makeModel) ?></span>
                            </div>
                        </td>

                        <!-- 7. Status & Telemetry Badge -->
                        <td class="text-center">
                            <div class="history-status-cell">
                                <span class="history-status-pill <?= $statusClass ?>">
                                    <span class="history-pulse-dot <?= $statusClass ?>"></span>
                                    <?= $statusText ?>
                                </span>
                                <?php if ($diff > 0): ?>
                                    <span class="telemetry-distance-pill" title="Verified trip distance">
                                        <i class="fa-solid fa-road"></i> <?= number_format($diff) ?> km
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 8. Actions Menu -->
                        <td class="text-center">
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="action-btn-table view" title="View Full Trip Manifest">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (!empty($phone)): ?>
                                        <a href="tel:<?= e($phone) ?>" class="action-btn-table edit" title="Call Passenger">
                                            <i class="fa-solid fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trip history records found</h4>
                                <p class="text-muted" style="margin:0;">Once you accept and complete assigned journeys, your verified telemetry and itinerary archive will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Booking Cards List (Exact Responsive Design) -->
        <div class="cards-grid" id="customersCards">
            <?php foreach (($history ?? []) as $c):
                $st = strtolower($c['status'] ?? 'completed');
                $isActive = ($st === 'completed');
                $statusClass = $isActive ? 'completed' : 'cancelled';
                $statusText = match($st) {
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    default     => ucfirst($st)
                };
                $custName = $c['customer_name'] ?? 'VIP Guest';
                $initial = strtoupper(mb_substr($custName, 0, 1));
                $avatarUrl = !empty($c['customer_avatar']) ? customer_avatar_url($c['customer_avatar']) : null;
                $bookingCode = $c['booking_code'] ?? '';
                $phone = $c['customer_phone'] ?? '';
                $startOdo = (int)($c['start_odometer'] ?? 0);
                $endOdo   = (int)($c['end_odometer'] ?? 0);
                $diff     = ($endOdo >= $startOdo && $startOdo > 0) ? ($endOdo - $startOdo) : 0;
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="mv-booking-card">
                    <!-- Left: Avatar Image or Monogram -->
                    <div class="mv-thumb-wrap">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= e($avatarUrl) ?>" alt="<?= e($custName) ?>" class="mv-thumb-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="mv-thumb-avatar" style="display: none;"><?= e($initial) ?></div>
                        <?php else: ?>
                            <div class="mv-thumb-avatar"><?= e($initial) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Middle: Details -->
                    <div class="mv-details">
                        <div class="mv-tour-title" title="<?= e($custName) ?>">
                            <?= e($custName) ?>
                        </div>
                        <div class="mv-passenger">
                            <span class="cust-code-pill"><?= e($bookingCode) ?></span>
                            <span style="color:#94A3B8;">&bull;</span>
                            <span style="font-size:0.75rem; color:#64748B; font-weight:600;"><?= e($c['dispatch_code'] ?? '') ?></span>
                        </div>
                        <?php if (!empty($phone)): ?>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-phone" style="font-size:0.75rem; color:#0EA5E9;"></i>
                            <span><?= e($phone) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-passenger" style="color: #475569;">
                            <i class="fa-solid fa-location-dot" style="color: #10B981;"></i>
                            <span><?= e($c['pickup_address'] ?? '') ?></span>
                        </div>
                        <div class="mv-passenger" style="color: #475569;">
                            <i class="fa-solid fa-flag-checkered" style="color: #EF4444;"></i>
                            <span><?= e($c['drop_address'] ?? '') ?></span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-car" style="color:#64748B;"></i>
                            <span><?= e($c['vehicle_number'] ?? '') ?> &bull; <?= e($c['make_model'] ?? 'Fleet') ?></span>
                        </div>
                    </div>

                    <!-- Right: Status, Telemetry & Date -->
                    <div class="mv-right-col">
                        <span class="history-status-pill sm <?= $statusClass ?>">
                            <span class="history-pulse-dot <?= $statusClass ?>"></span>
                            <?= $statusText ?>
                        </span>
                        <?php if ($diff > 0): ?>
                            <span class="telemetry-distance-pill sm">
                                <?= number_format($diff) ?> km
                            </span>
                        <?php endif; ?>
                        <div class="mv-price"><?= format_date($c['start_date'] ?? date('Y-m-d')) ?></div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/driver/trips/' . $c['id']) ?>" class="action-btn-table view" title="View Full Itinerary">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if (!empty($phone)): ?>
                            <a href="tel:<?= e($phone) ?>" class="action-btn-table edit" title="Call Passenger">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No trip history records found</h4>
                <p class="text-muted" style="margin:0;">Once you accept and complete assigned journeys, your verified telemetry and itinerary archive will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Architecture from Customer Directory) -->
    <div id="customersPaginationWrapper">
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> archived record(s)</div>
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
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Complete Driver History Live AJAX Filter Engine (Without Page Reload)    */
    /* ========================================================================= */
    (function initCustomerAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/driver/history') ?>';

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

        function filterCustomersAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) {
                abortCtrl.abort();
            }
            abortCtrl = new AbortController();

            const section = document.getElementById('customersSection');
            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'block' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = searchVal ? 'block' : 'none';

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

                // 1. Update KPI grid
                const newKpis = doc.getElementById('customersKpiGrid');
                const currKpis = document.getElementById('customersKpiGrid');
                if (newKpis && currKpis) {
                    currKpis.innerHTML = newKpis.innerHTML;
                }

                // 2. Update Total Records Pill
                const newPill = doc.getElementById('totalRecordsPill');
                const currPill = document.getElementById('totalRecordsPill');
                if (newPill && currPill) {
                    currPill.textContent = newPill.textContent;
                }

                // 3. Update Desktop Table Body
                const newTbody = doc.getElementById('customersTableBody');
                const currTbody = document.getElementById('customersTableBody');
                if (newTbody && currTbody) {
                    currTbody.innerHTML = newTbody.innerHTML;
                }

                // 4. Update Mobile Cards
                const newCards = doc.getElementById('customersCards');
                const currCards = document.getElementById('customersCards');
                if (newCards && currCards) {
                    currCards.innerHTML = newCards.innerHTML;
                }

                // 5. Update Pagination Wrapper
                const newPagin = doc.getElementById('customersPaginationWrapper');
                const currPagin = document.getElementById('customersPaginationWrapper');
                if (newPagin && currPagin) {
                    currPagin.innerHTML = newPagin.innerHTML;
                }

                // 6. Update Clear Filters Button
                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                // 7. Update Mobile Status Pills
                const newScroll = doc.getElementById('customersMvStatusScroll');
                const currScroll = document.getElementById('customersMvStatusScroll');
                if (newScroll && currScroll) {
                    currScroll.innerHTML = newScroll.innerHTML;
                }

                // 8. Sync URL bar without full page reload
                window.history.replaceState({ path: targetUrl }, '', targetUrl);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('AJAX Driver history filter error:', err);
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        // Search Input Debouncing
        const searchInput = document.getElementById('searchInput');
        const mvSearchInput = document.getElementById('mvSearchInput');

        function onSearchInput(val) {
            if (searchInput && searchInput.value !== val) searchInput.value = val;
            if (mvSearchInput && mvSearchInput.value !== val) mvSearchInput.value = val;

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterCustomersAjax(1);
            }, 300);
        }

        if (searchInput) {
            searchInput.addEventListener('input', e => onSearchInput(e.target.value));
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        if (mvSearchInput) {
            mvSearchInput.addEventListener('input', e => onSearchInput(e.target.value));
            mvSearchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        // Clear Search buttons
        function clearSearch() {
            if (searchInput) searchInput.value = '';
            if (mvSearchInput) mvSearchInput.value = '';
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
            if (searchClearBtn) searchClearBtn.style.display = 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = 'none';
            filterCustomersAjax(1);
        }

        const searchClearBtn = document.getElementById('searchClearBtn');
        if (searchClearBtn) searchClearBtn.addEventListener('click', clearSearch);
        const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
        if (mvSearchClearBtn) mvSearchClearBtn.addEventListener('click', clearSearch);

        // Status Select Dropdown
        const statusInput = document.getElementById('statusInput');
        if (statusInput) {
            statusInput.addEventListener('change', () => filterCustomersAjax(1));
        }

        // Per Page Dropdown (Desktop & Mobile)
        const perPageInput = document.getElementById('perPageInput');
        const mvPerPageSelect = document.getElementById('mvPerPageSelect');
        if (perPageInput) {
            perPageInput.addEventListener('change', () => {
                if (mvPerPageSelect) mvPerPageSelect.value = perPageInput.value;
                filterCustomersAjax(1);
            });
        }
        if (mvPerPageSelect) {
            mvPerPageSelect.addEventListener('change', () => {
                if (perPageInput) perPageInput.value = mvPerPageSelect.value;
                filterCustomersAjax(1);
            });
        }

        // Apply Button
        const filterApplyBtn = document.getElementById('filterApplyBtn');
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => filterCustomersAjax(1));
        }

        // Refresh Button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => filterCustomersAjax(1));
        }

        // Global Event Delegation for Dynamic Elements (KPI Cards, Clear Button, Pagination, Status Pills)
        document.addEventListener('click', function(e) {
            // 1. KPI Card Click
            const kpiCard = e.target.closest('#customersKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                const st = kpiCard.getAttribute('data-status') || '';
                if (statusInput) statusInput.value = st;
                filterCustomersAjax(1);
                return;
            }

            // 2. Clear Filters Button
            const clearBtn = e.target.closest('#filterClearBtn');
            if (clearBtn) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (mvSearchInput) mvSearchInput.value = '';
                if (statusInput) statusInput.value = '';
                if (perPageInput) perPageInput.value = '10';
                if (mvPerPageSelect) mvPerPageSelect.value = '10';
                filterCustomersAjax(1);
                return;
            }

            // 3. Mobile Status Pills
            const statusPill = e.target.closest('#customersMvStatusScroll .mv-pill');
            if (statusPill) {
                e.preventDefault();
                const st = statusPill.getAttribute('data-status') || '';
                if (statusInput) statusInput.value = st;
                filterCustomersAjax(1);
                return;
            }

            // 4. Pagination Links
            const paginLink = e.target.closest('#customersPaginationWrapper .pagination a[data-page]');
            if (paginLink) {
                e.preventDefault();
                if (paginLink.classList.contains('disabled') || paginLink.classList.contains('active')) return;
                const p = parseInt(paginLink.getAttribute('data-page'), 10) || 1;
                filterCustomersAjax(p);
                return;
            }
        });
    })();

    /* Toggle Mobile Filter Drawer */
    function toggleMobileFilter() {
        const panel = document.getElementById('mvFilterPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    /* Desktop Actions Popup */
    function toggleActions(btn, event) {
        event.stopPropagation();
        const popup = btn.nextElementSibling;
        const allPopups = document.querySelectorAll('.action-btns-popup.show');
        allPopups.forEach(p => {
            if (p !== popup) p.classList.remove('show');
        });
        if (popup) {
            popup.classList.toggle('show');
        }
    }

    /* Mobile Cards Action Popup */
    function toggleCardActions(btn, event) {
        event.stopPropagation();
        const popup = btn.nextElementSibling;
        const allPopups = document.querySelectorAll('.card-btns-popup.show');
        allPopups.forEach(p => {
            if (p !== popup) p.classList.remove('show');
        });
        if (popup) {
            popup.classList.toggle('show');
        }
    }

    // Close popups on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.show, .card-btns-popup.show').forEach(p => {
                p.classList.remove('show');
            });
        }
    });
</script>

<style>
/* ========================================================================= */
/* Executive Chauffeur Trip History UI & Layout Styles                       */
/* ========================================================================= */

.bookings-page-view {
    padding: 1.5rem;
    max-width: 1600px;
    margin: 0 auto;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
}

/* KPI Summary Grid */
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

.icon-teal { background: #ECFDF5; color: #059669; }
.icon-emerald { background: #E0F2FE; color: #0284C7; }
.icon-amber { background: #FEF3C7; color: #D97706; }
.icon-navy { background: #F1F5F9; color: #475569; }

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
    background: #10B981;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.kpi-filter-tag {
    color: #94A3B8;
    font-size: 0.75rem;
}

/* Compact Filter Bar */
.filter-bar-compact {
    display: flex;
    align-items: center;
    background: #ffffff;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    gap: 0.75rem;
    flex-wrap: wrap;
}

.fbc-search {
    position: relative;
    flex: 1;
    min-width: 260px;
}

.fbc-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: 0.9rem;
}

.fbc-search input {
    width: 100%;
    padding: 0.55rem 2.25rem 0.55rem 2.25rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    outline: none;
    color: #1E293B;
    transition: all 0.15s ease;
}

.fbc-search input:focus {
    border-color: #0EA5E9;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
}

.fbc-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    font-size: 0.85rem;
}

.filter-bar-compact select {
    padding: 0.55rem 1rem;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 0.88rem;
    color: #1E293B;
    background-color: #fff;
    cursor: pointer;
    outline: none;
}

.filter-bar-compact select:focus {
    border-color: #0EA5E9;
}

.fbc-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fbc-btn {
    padding: 0.55rem 1rem;
    border-radius: 8px;
    border: 1px solid #CBD5E1;
    background: #F8FAFC;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.fbc-btn:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.fbc-btn-clear {
    background: #FEE2E2;
    color: #DC2626;
    border-color: #FECACA;
}

.fbc-btn-clear:hover {
    background: #FCA5A5;
    border-color: #F87171;
}

/* Modern PMS Table Container */
.recent-bookings-section {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
    position: relative;
}

.recent-bookings-section.is-filtering {
    opacity: 0.6;
    pointer-events: none;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #E2E8F0;
}

.section-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.total-records-box {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #64748B;
}

.total-records-badge {
    background: #0F172A;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.8rem;
}

.modern-table-container {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}

.modern-table thead th {
    background: #F8FAFC;
    color: #475569;
    font-weight: 700;
    padding: 0.85rem 1rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #E2E8F0;
}

.modern-table tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #F1F5F9;
    color: #334155;
    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: #F8FAFC;
}

/* Table Cells */
.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    font-size: 0.82rem;
}

.cust-table-avatar-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-table-avatar-img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.cust-table-monogram {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #E2E8F0;
    color: #1E293B;
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-client-details-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cust-name-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.cust-full-name {
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    font-size: 0.92rem;
}

.cust-full-name:hover {
    color: #0EA5E9;
}

.cust-code-pill {
    background: #EFF6FF;
    color: #1D4ED8;
    border: 1px solid #DBEAFE;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.02em;
}

.cust-email-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: #64748B;
}

.cust-dispatch-tag {
    font-weight: 600;
    color: #475569;
}

.cust-date-tag {
    color: #94A3B8;
}

.cust-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.84rem;
}

.cust-primary-phone {
    font-weight: 600;
    color: #1E293B;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cust-primary-phone:hover {
    color: #0EA5E9;
}

.cust-phone-icon {
    font-size: 0.75rem;
    color: #0EA5E9;
}

.cust-alt-phone {
    font-size: 0.76rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cust-no-email {
    color: #94A3B8;
}

.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.82rem;
}

.cust-address-line {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #1E293B;
    font-weight: 500;
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cust-address-sub {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748B;
    font-size: 0.78rem;
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cust-bookings-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}

.fleet-plate-badge {
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    color: #0F172A;
    font-weight: 800;
    font-size: 0.82rem;
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.04em;
}

.cust-bookings-lbl {
    font-size: 0.73rem;
    color: #64748B;
    font-weight: 500;
}

/* Status & Telemetry */
.history-status-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.history-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.history-status-pill.completed {
    background: #ECFDF5;
    color: #059669;
    border: 1px solid #A7F3D0;
}

.history-status-pill.cancelled {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
}

.history-status-pill.sm {
    padding: 2px 6px;
    font-size: 0.7rem;
}

.history-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.history-pulse-dot.completed {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}

.history-pulse-dot.cancelled {
    background: #EF4444;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
}

.telemetry-distance-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #FEF3C7;
    color: #B45309;
    border: 1px solid #FDE68A;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
}

.telemetry-distance-pill.sm {
    font-size: 0.68rem;
    padding: 1px 5px;
}

/* Actions Menu */
.table-action-buttons {
    position: relative;
    display: flex;
    justify-content: center;
}

.action-toggle-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    color: #475569;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.action-toggle-btn:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.action-btns-popup {
    position: absolute;
    right: 0;
    top: 100%;
    background: #ffffff;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    display: none;
    flex-direction: row;
    gap: 4px;
    padding: 6px;
    z-index: 50;
}

.action-btns-popup.show {
    display: flex;
}

.action-btn-table {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.8rem;
    color: #475569;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    transition: all 0.15s ease;
}

.action-btn-table:hover {
    background: #0EA5E9;
    color: #fff;
    border-color: #0EA5E9;
}

/* Empty State */
.no-data-message {
    padding: 3rem 1.5rem;
    text-align: center;
}

.no-data-icon {
    font-size: 2.5rem;
    color: #94A3B8;
    margin-bottom: 0.75rem;
}

/* Modern Pagination Wrapper */
.pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.pagination-info {
    font-size: 0.85rem;
    color: #64748B;
}

.pagination-info span {
    font-weight: 700;
    color: #0F172A;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}

.pagination a:hover:not(.disabled) {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.pagination a.active {
    background: #0EA5E9;
    color: #fff;
    border-color: #0EA5E9;
}

.pagination a.disabled {
    color: #CBD5E1;
    pointer-events: none;
}

.pagination .dots {
    padding: 0 4px;
    color: #94A3B8;
}

/* Responsive Mobile Layout */
.mv-bookings-header, .cards-grid {
    display: none;
}

@media (max-width: 991px) {
    .booking-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .bookings-page-view {
        padding: 0.75rem;
    }

    .desktop-header-wrapper,
    .booking-kpi-grid,
    .filter-bar-compact,
    .modern-table-container,
    .section-header {
        display: none !important;
    }

    .mv-bookings-header {
        display: block;
        margin-bottom: 1rem;
    }

    .mv-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .mv-top-bar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mv-back-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1E293B;
        text-decoration: none;
    }

    .mv-page-title {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
        color: #0F172A;
    }

    .mv-add-btn {
        background: #0EA5E9;
        color: #fff;
        padding: 0.45rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mv-search-form {
        display: flex;
        gap: 6px;
        margin-bottom: 0.75rem;
    }

    .mv-search-input-box {
        position: relative;
        flex: 1;
    }

    .mv-search-ico {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 0.85rem;
    }

    .mv-search-input-box input {
        width: 100%;
        padding: 0.5rem 2rem 0.5rem 2rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .mv-search-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94A3B8;
    }

    .mv-filter-toggle {
        padding: 0.5rem 0.75rem;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
    }

    .mv-filter-panel {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .mv-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mv-filter-row select {
        padding: 0.35rem 0.65rem;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
    }

    .mv-status-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .mv-status-scroll::-webkit-scrollbar {
        display: none;
    }

    .mv-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .mv-pill.active {
        background: #0EA5E9;
        color: #fff;
        border-color: #0EA5E9;
    }

    .mv-pill-cnt {
        background: rgba(0,0,0,0.06);
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
    }

    .mv-pill.active .mv-pill-cnt {
        background: rgba(255,255,255,0.25);
    }

    .cards-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.75rem;
    }

    .mv-booking-card-wrapper {
        position: relative;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .mv-booking-card {
        display: flex;
        align-items: flex-start;
        padding: 0.85rem;
        text-decoration: none;
        gap: 10px;
    }

    .mv-thumb-wrap {
        flex-shrink: 0;
    }

    .mv-thumb-avatar-img, .mv-thumb-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mv-thumb-avatar {
        background: #E2E8F0;
        color: #334155;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mv-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .mv-tour-title {
        font-weight: 700;
        color: #0F172A;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-date-duration, .mv-passenger, .mv-vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mv-right-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }

    .mv-price {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .mv-card-actions {
        position: absolute;
        bottom: 8px;
        right: 8px;
    }

    .mv-action-dots-btn {
        background: transparent;
        border: none;
        color: #94A3B8;
        font-size: 0.95rem;
        cursor: pointer;
        padding: 4px;
    }

    .card-btns-popup {
        position: absolute;
        bottom: 100%;
        right: 0;
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        gap: 4px;
        padding: 6px;
        z-index: 50;
    }

    .card-btns-popup.show {
        display: flex;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
        align-items: center;
    }
}
</style>
