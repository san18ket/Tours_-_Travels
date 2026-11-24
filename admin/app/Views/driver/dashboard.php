<?php
/**
 * Driver Command Dashboard — Dedicated Driver Portal
 * Executive Operational Architecture matching Owner Dashboard standards
 * Direct alignment with database records, authenticated profile, and responsive layout
 */

$driverName = trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''));
if (empty($driverName)) {
    $driverName = $driver['name'] ?? ($user['name'] ?? 'Driver');
}

$driverInitial = strtoupper(substr($driverName, 0, 1));
$driverPhoto = !empty($driver['profile_photo']) ? customer_avatar_url($driver['profile_photo']) : null;
$employeeCode = $driver['employee_code'] ?? null;

// Duty status resolution
$dutyStatus = $driver['duty_status'] ?? 'off_duty';
$isOnDuty = in_array($dutyStatus, ['on_duty', 'on_trip'], true);
$dutyText = match($dutyStatus) {
    'on_duty'  => 'On Duty',
    'on_trip'  => 'On Trip',
    'off_duty' => 'Off Duty',
    default    => 'Off Duty',
};

// Greeting by time of day
$currentHour = (int)date('H');
$greetingText = ($currentHour < 12) ? 'Good Morning,' : (($currentHour < 17) ? 'Good Afternoon,' : 'Good Evening,');

// Determine prominent trip: active trip first, otherwise first upcoming assigned trip
$featuredTrip = !empty($activeTrip) ? $activeTrip : (!empty($assignedTrips) ? $assignedTrips[0] : null);
$isFeaturedActive = !empty($activeTrip);

// Real KPI numbers
$kpiTotal = (int)($kpiStats['total_trips'] ?? 0);
$kpiAwaiting = (int)($kpiStats['awaiting_response'] ?? 0);
$kpiAccepted = (int)($kpiStats['accepted'] ?? 0);
$kpiActive = (int)($kpiStats['active'] ?? 0);
$kpiCompleted = (int)($kpiStats['completed'] ?? 0);
$kpiPending = (float)($kpiStats['pending_earnings'] ?? 0);
?>

<div class="bookings-page-view driver-portal-view">

    <!-- 1. Top Hero Section: Greeting Banner (Left) + Duty Shift Card (Right) -->
    <div class="driver-hero-grid">

        <!-- Wide Greeting Card -->
        <div class="driver-greeting-banner">
            <div class="driver-greeting-left">
                <!-- Driver Avatar Ring with Fallback -->
                <div class="driver-avatar-ring">
                    <?php if (!empty($driverPhoto)): ?>
                        <img src="<?= e($driverPhoto) ?>" alt="<?= e($driverName) ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div class="driver-avatar-initials" style="display:none;"><?= e($driverInitial) ?></div>
                    <?php else: ?>
                        <div class="driver-avatar-initials"><?= e($driverInitial) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Driver Greeting Meta -->
                <div class="driver-greeting-meta">
                    <span class="driver-greeting-salute"><?= e($greetingText) ?></span>
                    <h1 class="driver-name-title" title="<?= e($driverName) ?>"><?= e($driverName) ?></h1>
                    <p class="driver-journey-tagline">
                        <?php if ($employeeCode): ?>
                            <span class="badge badge-neutral" style="font-size: 0.72rem; padding: 2px 7px;"><?= e($employeeCode) ?></span>
                        <?php endif; ?>
                        <span><?= e(__('role.licensed_driver', 'Licensed Driver')) ?></span>
                        &bull;
                        <span><?= e(company_display_name()) ?> Fleet</span>
                    </p>
                </div>
            </div>

            <!-- Scenic Highway Cutout Backdrop (Right) -->
            <div class="driver-banner-bus-backdrop">
                <img src="<?= asset('images/hero-bus.jpg') ?>" alt="Tour Coach on Scenic Road" class="driver-backdrop-img">
            </div>
        </div>

        <!-- Shift & Duty Status Card (Right) -->
        <div class="driver-shift-card">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                <div class="shift-duty-indicator <?= $dutyStatus === 'on_trip' ? 'on-trip' : ($isOnDuty ? 'on-duty' : 'off-duty') ?>">
                    <span class="shift-pulse-dot"></span>
                    <span><?= e($dutyText) ?></span>
                </div>
                <span style="font-size: 0.75rem; color: #64748B; font-weight: 600;">Shift Control</span>
            </div>

            <div class="shift-datetime-group">
                <div class="shift-datetime-item">
                    <i class="fa-regular fa-clock"></i>
                    <span><?= date('h:i A') ?></span>
                </div>
                <div class="shift-datetime-item">
                    <i class="fa-regular fa-calendar"></i>
                    <span><?= date('d M Y') ?></span>
                </div>
            </div>

            <!-- Shift Toggle Action Form -->
            <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin: 0; width: 100%;">
                <?= csrf_field() ?>
                <?php if ($dutyStatus === 'on_trip'): ?>
                    <button type="button" class="btn-shift-action btn-shift-on-trip" disabled title="<?= e(__('driver.cannot_switch_active_trip', 'Cannot switch status while executing an active trip')) ?>">
                        <i class="fa-solid fa-route"></i>
                        <span>On Trip &bull; Shift Active</span>
                    </button>
                <?php elseif ($isOnDuty): ?>
                    <button type="submit" class="btn-shift-action btn-shift-end" title="<?= e(__('driver.switch_off_duty', 'Switch to off-duty status')) ?>">
                        <i class="fa-solid fa-power-off"></i>
                        <span>End Duty Shift</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn-shift-action btn-shift-start" title="<?= e(__('driver.switch_on_duty', 'Switch to on-duty status')) ?>">
                        <i class="fa-solid fa-play"></i>
                        <span>Start Duty Shift</span>
                    </button>
                <?php endif; ?>
            </form>
        </div>

    </div>

    <!-- 2. Row of 6 Real Operational KPI Cards -->
    <div class="driver-kpi-grid">

        <!-- KPI 1: Total Trips -->
        <a href="<?= url('/driver/history') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_trip_history', 'View all completed and historical trips')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Total Dispatches</span>
                <span class="driver-kpi-icon-pill icon-teal">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
            </div>
            <div class="driver-kpi-val"><?= number_format($kpiTotal) ?></div>
            <div class="driver-kpi-sub">
                <span>All assigned &amp; completed</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

        <!-- KPI 2: Awaiting Response -->
        <a href="<?= url('/driver/trips?status=assigned') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_assigned_trips', 'View newly assigned trips requiring response')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Awaiting Response</span>
                <span class="driver-kpi-icon-pill icon-amber">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
            </div>
            <div class="driver-kpi-val" style="color: <?= $kpiAwaiting > 0 ? '#D97706' : '#0F172A' ?>;"><?= number_format($kpiAwaiting) ?></div>
            <div class="driver-kpi-sub">
                <span><?= $kpiAwaiting > 0 ? 'Requires action' : 'None pending' ?></span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

        <!-- KPI 3: Accepted Trips -->
        <a href="<?= url('/driver/trips?status=driver_accepted') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_accepted_trips', 'View accepted trips scheduled for pickup')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Accepted Trips</span>
                <span class="driver-kpi-icon-pill icon-blue">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="driver-kpi-val"><?= number_format($kpiAccepted) ?></div>
            <div class="driver-kpi-sub">
                <span>Confirmed for pickup</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

        <!-- KPI 4: Active / En Route -->
        <a href="<?= url('/driver/trips?status=trip_started') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_active_trips', 'View active in-progress journeys')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Active Journeys</span>
                <span class="driver-kpi-icon-pill icon-green">
                    <i class="fa-solid fa-car-side"></i>
                </span>
            </div>
            <div class="driver-kpi-val" style="color: <?= $kpiActive > 0 ? '#166534' : '#0F172A' ?>;"><?= number_format($kpiActive) ?></div>
            <div class="driver-kpi-sub">
                <span>In-progress transit</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

        <!-- KPI 5: Completed Trips -->
        <a href="<?= url('/driver/history?status=completed') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_completed_trips', 'View completed missions archive')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Completed Trips</span>
                <span class="driver-kpi-icon-pill icon-teal">
                    <i class="fa-solid fa-flag-checkered"></i>
                </span>
            </div>
            <div class="driver-kpi-val"><?= number_format($kpiCompleted) ?></div>
            <div class="driver-kpi-sub">
                <span>Missions accomplished</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

        <!-- KPI 6: Pending Earnings -->
        <a href="<?= url('/driver/earnings') ?>" class="driver-kpi-card" title="<?= e(__('driver.view_pending_settlements', 'View pending compensation settlements')) ?>">
            <div class="driver-kpi-top">
                <span class="driver-kpi-label">Pending Payout</span>
                <span class="driver-kpi-icon-pill icon-purple">
                    <i class="fa-solid fa-wallet"></i>
                </span>
            </div>
            <div class="driver-kpi-val" style="color: <?= $kpiPending > 0 ? '#7E22CE' : '#166534' ?>;">
                <?= format_inr($kpiPending) ?>
            </div>
            <div class="driver-kpi-sub">
                <span><?= $kpiPending > 0 ? 'Pending disbursement' : 'All dues settled' ?></span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
            </div>
        </a>

    </div>

    <!-- 3. Main Dashboard Body: Left Prominent Assignment + Right Summary & Activity -->
    <div class="driver-dashboard-grid">

        <!-- Left Column: Prominent Current / Next Assignment -->
        <div>
            <?php if (!empty($featuredTrip)): ?>
                <?php
                    $fStatus = strtolower($featuredTrip['status'] ?? 'assigned');
                    $fPickup = $featuredTrip['pickup_address'] ?? 'Origin';
                    $fDrop   = $featuredTrip['drop_address'] ?? 'Destination';
                    $fDate   = !empty($featuredTrip['start_date']) ? format_date($featuredTrip['start_date']) : (!empty($featuredTrip['created_at']) ? format_date($featuredTrip['created_at']) : '—');
                    $fTime   = !empty($featuredTrip['pickup_time']) ? date('h:i A', strtotime($featuredTrip['pickup_time'])) : (!empty($featuredTrip['reporting_time']) ? date('h:i A', strtotime($featuredTrip['reporting_time'])) : 'Scheduled');
                    $fVehNum = $featuredTrip['vehicle_number'] ?? 'Assigned Vehicle';
                    $fMake   = $featuredTrip['make_model'] ?? '';
                    $fAmount = $featuredTrip['driver_trip_amount'] !== null ? format_inr($featuredTrip['driver_trip_amount']) : null;
                ?>
                <div class="driver-assignment-card">
                    <!-- Top Row: Badge & Codes -->
                    <div class="assignment-top-row">
                        <div class="assignment-badge-group">
                            <span class="assignment-pill-tag">
                                <i class="fa-solid <?= $isFeaturedActive ? 'fa-route' : 'fa-clock' ?>"></i>
                                <?= $isFeaturedActive ? 'Current Active Mission' : 'Next Scheduled Assignment' ?>
                            </span>
                            <span class="assignment-booking-code" title="<?= e(__('driver.booking_ref_code', 'Booking Reference Code')) ?>">
                                <?= e($featuredTrip['booking_code'] ?? $featuredTrip['dispatch_code']) ?>
                            </span>
                        </div>
                        <div>
                            <?= render_status_badge($fStatus, 'dispatch') ?>
                        </div>
                    </div>

                    <!-- Middle: Origin & Destination Route Box -->
                    <div class="assignment-route-grid">
                        <div class="route-stop-box">
                            <div class="route-stop-icon pickup">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="route-stop-meta">
                                <div class="route-stop-role">Pickup Origin</div>
                                <div class="route-stop-title" title="<?= e($fPickup) ?>"><?= e($fPickup) ?></div>
                            </div>
                        </div>

                        <div class="route-stop-box">
                            <div class="route-stop-icon drop">
                                <i class="fa-solid fa-flag"></i>
                            </div>
                            <div class="route-stop-meta">
                                <div class="route-stop-role">Drop Destination</div>
                                <div class="route-stop-title" title="<?= e($fDrop) ?>"><?= e($fDrop) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Specs & Contextual Action Buttons -->
                    <div class="assignment-specs-row">
                        <div class="assignment-spec-item" title="<?= e(__('driver.reporting_date_time', 'Reporting Date & Time')) ?>">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span><?= e($fDate) ?> &bull; <?= e($fTime) ?></span>
                        </div>

                        <div class="assignment-spec-item" title="<?= e(__('booking.assigned_vehicle', 'Assigned Fleet Vehicle')) ?>">
                            <i class="fa-solid fa-bus"></i>
                            <span><?= e($fVehNum) ?> <?= !empty($fMake) ? '(' . e($fMake) . ')' : '' ?></span>
                        </div>

                        <?php if ($fAmount !== null): ?>
                            <div class="assignment-spec-item" title="<?= e(__('booking.driver_trip_amount', 'Your Driver Trip Compensation')) ?>">
                                <i class="fa-solid fa-money-bill-wave" style="color: #10B981;"></i>
                                <span style="color: #166534; font-weight: 700;">Payout: <?= e($fAmount) ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Contextual Actions -->
                        <div class="assignment-actions-row">
                            <?php if ($fStatus === 'assigned'): ?>
                                <form action="<?= url('/driver/trips/' . $featuredTrip['id'] . '/accept') ?>" method="POST" style="margin: 0; display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary btn-sm" style="background:#176B5B; border-color:#176B5B; font-weight:700;">
                                        <i class="fa-solid fa-check"></i> <span>Accept Duty</span>
                                    </button>
                                </form>
                                <a href="<?= url('/driver/trips/' . $featuredTrip['id']) ?>" class="btn btn-outline btn-sm">
                                    <span>View Details</span>
                                </a>
                            <?php elseif ($fStatus === 'driver_accepted'): ?>
                                <a href="<?= url('/driver/trips/' . $featuredTrip['id']) ?>" class="btn btn-primary btn-sm" style="background:#10B981; border-color:#10B981; font-weight:700;">
                                    <i class="fa-solid fa-play"></i> <span>Start Trip</span>
                                </a>
                                <a href="<?= url('/driver/trips/' . $featuredTrip['id']) ?>" class="btn btn-outline btn-sm">
                                    <span>View Details</span>
                                </a>
                            <?php elseif (in_array($fStatus, ['en_route_pickup', 'trip_started'], true)): ?>
                                <a href="<?= url('/driver/trips/' . $featuredTrip['id']) ?>" class="btn btn-primary btn-sm" style="background:#2563EB; border-color:#2563EB; font-weight:700;">
                                    <i class="fa-solid fa-flag-checkered"></i> <span>Complete Trip</span>
                                </a>
                                <a href="<?= url('/driver/location') ?>" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-satellite-dish"></i> <span>Live GPS</span>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('/driver/trips/' . $featuredTrip['id']) ?>" class="btn btn-primary btn-sm">
                                    <span>View Trip Summary</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Clean Operational Empty State -->
                <div class="driver-empty-card">
                    <div class="driver-empty-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <h3 class="driver-empty-title">No Active Trip Assignment</h3>
                    <p class="driver-empty-desc">
                        You currently have no active or pending dispatches assigned to your profile. When operations schedules a new passenger mission, it will appear here instantly.
                    </p>
                    <div style="display: flex; gap: 0.65rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?= url('/driver/trips') ?>" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-list-check"></i> <span>Check Assigned Trips</span>
                        </a>
                        <a href="<?= url('/driver/history') ?>" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-clock-rotate-left"></i> <span>View Trip Archive</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upcoming Assigned Trips Table / List -->
            <div class="driver-card">
                <div class="driver-card-header">
                    <h3 class="driver-card-title">
                        <i class="fa-solid fa-calendar-days" style="color: var(--brand-primary, #176B5B);"></i>
                        <span>Recent &amp; Upcoming Assignments</span>
                    </h3>
                    <a href="<?= url('/driver/trips') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                        View All (<?= (int)$kpiTotal ?>) &rarr;
                    </a>
                </div>
                <div class="driver-card-body" style="padding: 0;">
                    <?php if (!empty($assignedTrips)): ?>
                        <div class="modern-table-container" style="box-shadow: none; border: none; border-radius: 0; margin-bottom: 0;">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th class="col-priority-1"><?= e(__('table.dispatch_booking', 'Dispatch / Booking')) ?></th>
                                        <th class="col-priority-1"><?= e(__('table.route', 'Route')) ?></th>
                                        <th class="col-priority-2" data-column-priority="secondary">Date &amp; Time</th>
                                        <th class="col-priority-3 col-optional" data-column-priority="optional"><?= e(__('table.vehicle', 'Vehicle')) ?></th>
                                        <th class="text-center col-priority-1"><?= e(__('table.status', 'Status')) ?></th>
                                        <th class="text-center col-priority-1"><?= e(__('table.action', 'Action')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedTrips as $t): ?>
                                        <tr>
                                            <td class="col-priority-1">
                                                <strong style="color: #0F172A;"><?= e($t['dispatch_code']) ?></strong>
                                                <div style="font-size: 0.75rem; color: #64748B;"><?= e($t['booking_code']) ?></div>
                                            </td>
                                            <td class="col-priority-1">
                                                <div style="font-weight: 600; color: #1E293B; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= e($t['pickup_address']) ?> &rarr; <?= e($t['drop_address']) ?>
                                                </div>
                                            </td>
                                            <td class="col-priority-2" data-column-priority="secondary">
                                                <div style="font-size: 0.85rem; font-weight: 600;"><?= format_date($t['start_date']) ?></div>
                                                <div style="font-size: 0.75rem; color: #64748B;"><?= !empty($t['reporting_time']) ? date('h:i A', strtotime($t['reporting_time'])) : 'Scheduled' ?></div>
                                            </td>
                                            <td class="col-priority-3 col-optional" data-column-priority="optional">
                                                <span class="badge badge-neutral" style="font-weight: 700;"><?= e($t['vehicle_number']) ?></span>
                                            </td>
                                            <td class="text-center col-priority-1">
                                                <?= render_status_badge($t['status'], 'dispatch') ?>
                                            </td>
                                            <td class="text-center col-priority-1">
                                                <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.76rem;">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="padding: 2.5rem 1.5rem; text-align: center; color: #64748B;">
                            <i class="fa-regular fa-calendar-check" style="font-size: 1.8rem; color: #CBD5E1; margin-bottom: 0.5rem; display: block;"></i>
                            <div style="font-weight: 700; color: #334155;">No pending trip assignments</div>
                            <div style="font-size: 0.82rem; margin-top: 2px;">Your roster is currently clear.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Financial Summary + Recent Activity -->
        <div>
            <!-- Financial Summary Card -->
            <div class="driver-card">
                <div class="driver-card-header">
                    <h3 class="driver-card-title">
                        <i class="fa-solid fa-wallet" style="color: #10B981;"></i>
                        <span>Trip Compensation</span>
                    </h3>
                    <a href="<?= url('/driver/earnings') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                        Statement &rarr;
                    </a>
                </div>
                <div class="driver-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1.25rem;">
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 0.85rem;">
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 2px;">Total Earned</span>
                            <strong style="font-size: 1.25rem; font-weight: 800; color: #0F172A;"><?= format_inr($financialSummary['total_earned'] ?? 0) ?></strong>
                            <div style="font-size: 0.7rem; color: #94A3B8; margin-top: 2px;">Across <?= (int)($financialSummary['completed_trips'] ?? 0) ?> trips</div>
                        </div>

                        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 0.85rem;">
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #166534; display: block; margin-bottom: 2px;">Paid Amount</span>
                            <strong style="font-size: 1.25rem; font-weight: 800; color: #166534;"><?= format_inr($financialSummary['total_paid'] ?? 0) ?></strong>
                            <div style="font-size: 0.7rem; color: #166534; opacity: 0.8; margin-top: 2px;">Paid to your account</div>
                        </div>
                    </div>

                    <div style="background: <?= ($financialSummary['total_pending'] ?? 0) > 0 ? '#FFFBEB' : '#F8FAFC' ?>; border: 1px solid <?= ($financialSummary['total_pending'] ?? 0) > 0 ? '#FDE68A' : '#E2E8F0' ?>; border-radius: 12px; padding: 0.85rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.15rem;">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: <?= ($financialSummary['total_pending'] ?? 0) > 0 ? '#B45309' : '#64748B' ?>; display: block;">Pending Balance Due</span>
                            <strong style="font-size: 1.2rem; font-weight: 800; color: <?= ($financialSummary['total_pending'] ?? 0) > 0 ? '#B45309' : '#10B981' ?>;">
                                <?= format_inr($financialSummary['total_pending'] ?? 0) ?>
                            </strong>
                        </div>
                        <div>
                            <span class="badge <?= ($financialSummary['total_pending'] ?? 0) > 0 ? 'badge-warning' : 'badge-success' ?>">
                                <?= ($financialSummary['total_pending'] ?? 0) > 0 ? 'Awaiting Payout' : 'Fully Settled' ?>
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= url('/driver/earnings') ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">
                            <i class="fa-solid fa-list-check" style="margin-right: 4px;"></i> Trip Dues
                        </a>
                        <a href="<?= url('/driver/payments') ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">
                            <i class="fa-solid fa-receipt" style="margin-right: 4px;"></i> Vouchers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Dispatch Alerts -->
            <div class="driver-card">
                <div class="driver-card-header">
                    <h3 class="driver-card-title">
                        <i class="fa-solid fa-bell" style="color: #D97706;"></i>
                        <span>Operational Activity</span>
                    </h3>
                    <a href="<?= url('/driver/notifications') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                        All &rarr;
                    </a>
                </div>
                <div class="driver-card-body" style="padding: 0.75rem 1.25rem;">
                    <?php if (!empty($notifications)): ?>
                        <div class="driver-activity-list">
                            <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
                                <?php
                                    $payload = $notif['payload'] ?? [];
                                    $nTitle = !empty($payload['dispatch_code']) ? $payload['dispatch_code'] . ' (' . ($payload['pickup_address'] ?? 'Pickup') . ')' : $notif['title'];
                                    $nType = $notif['type'] ?? 'general';
                                    $iconClass = match($nType) {
                                        'trip_assigned' => 'icon-amber',
                                        'trip_started'  => 'icon-green',
                                        'trip_completed'=> 'icon-teal',
                                        default         => 'icon-blue',
                                    };
                                    $iconFa = match($nType) {
                                        'trip_assigned' => 'fa-route',
                                        'trip_started'  => 'fa-play',
                                        'trip_completed'=> 'fa-check',
                                        default         => 'fa-bell',
                                    };
                                ?>
                                <div class="driver-activity-item">
                                    <div class="driver-activity-icon <?= $iconClass ?>">
                                        <i class="fa-solid <?= $iconFa ?>"></i>
                                    </div>
                                    <div class="driver-activity-meta">
                                        <div class="driver-activity-title" title="<?= e($nTitle) ?>"><?= e($nTitle) ?></div>
                                        <div class="driver-activity-sub">
                                            <span><?= time_ago($notif['created_at']) ?></span>
                                            <?php if (empty($notif['is_read'])): ?>
                                                <span class="badge badge-danger" style="font-size: 0.65rem; padding: 1px 5px;">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 1.75rem 1rem; text-align: center; color: #94A3B8; font-size: 0.85rem;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 0.35rem; display: block; color: #CBD5E1;"></i>
                            No recent activity notifications.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Fleet Vehicle Overview -->
            <?php if (!empty($currentVehicle)): ?>
                <div class="driver-card">
                    <div class="driver-card-header">
                        <h3 class="driver-card-title">
                            <i class="fa-solid fa-car-side" style="color: var(--brand-primary, #176B5B);"></i>
                            <span>Assigned Fleet Vehicle</span>
                        </h3>
                        <a href="<?= url('/driver/vehicle') ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                            Specs &rarr;
                        </a>
                    </div>
                    <div class="driver-card-body" style="padding: 1.15rem 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <strong style="font-size: 1.1rem; color: #0F172A; display: block; letter-spacing: 0.02em;">
                                    <?= e($currentVehicle['vehicle_number']) ?>
                                </strong>
                                <span style="font-size: 0.82rem; color: #64748B;">
                                    <?= e($currentVehicle['make_model']) ?> &bull; <?= e(ucfirst(str_replace('_', ' ', $currentVehicle['vehicle_type'] ?? 'Coach'))) ?>
                                </span>
                            </div>
                            <span class="badge <?= ($currentVehicle['is_active_assignment'] ?? false) ? 'badge-success' : 'badge-neutral' ?>">
                                <?= ($currentVehicle['is_active_assignment'] ?? false) ? 'Active Duty' : 'Assigned' ?>
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: #64748B; display: flex; gap: 1rem;">
                            <span><i class="fa-solid fa-users" style="color: #94A3B8; margin-right: 3px;"></i> <?= (int)($currentVehicle['seating_capacity'] ?? 0) ?> Seats</span>
                            <span><i class="fa-solid fa-gas-pump" style="color: #94A3B8; margin-right: 3px;"></i> <?= ucfirst($currentVehicle['fuel_type'] ?? 'Diesel') ?></span>
                            <span><i class="fa-solid fa-gauge" style="color: #94A3B8; margin-right: 3px;"></i> <?= number_format((int)($currentVehicle['odometer_km'] ?? 0)) ?> km</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
