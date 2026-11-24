<?php
/**
 * My Assigned Fleet Vehicle View — Dedicated Driver Portal
 * Professional Vehicle Profile, Fleet Compliance & Telemetry aligned with Driver/Office Design System
 */

$isActiveAssignment = (bool)($vehicle['is_active_assignment'] ?? false);
$fleetStatus        = strtolower($vehicle['status'] ?? 'available');
$vehicleNumber      = $vehicle['vehicle_number'] ?? 'Not Assigned';
$makeModel          = $vehicle['make_model'] ?? 'Fleet Transport';
$vehicleType        = ucfirst($vehicle['vehicle_type'] ?? 'Luxury');
$seatingCapacity    = (int)($vehicle['seating_capacity'] ?? 4);
$fuelType           = ucfirst($vehicle['fuel_type'] ?? 'Diesel');
$odometerKm         = (int)($vehicle['odometer_km'] ?? 0);
$vehiclePhoto       = $vehicle['vehicle_photo'] ?? null;
$dispatchCode       = $vehicle['dispatch_code'] ?? null;
$bookingCode        = $vehicle['booking_code'] ?? null;
$pickupAddress      = $vehicle['pickup_address'] ?? null;
$dropAddress        = $vehicle['drop_address'] ?? null;

// Helper to determine document compliance badge
$getDocStatus = function(?string $expiryDate): array {
    if (empty($expiryDate) || $expiryDate === '0000-00-00') {
        return ['label' => 'Not On File', 'class' => 'badge-secondary', 'is_expired' => false, 'icon' => 'fa-circle-question'];
    }
    $today = date('Y-m-d');
    $warningThreshold = date('Y-m-d', strtotime('+30 days'));
    if ($expiryDate < $today) {
        return ['label' => 'Expired', 'class' => 'badge-danger', 'is_expired' => true, 'icon' => 'fa-circle-xmark'];
    }
    if ($expiryDate <= $warningThreshold) {
        return ['label' => 'Expiring Soon', 'class' => 'badge-warning', 'is_expired' => false, 'icon' => 'fa-triangle-exclamation'];
    }
    return ['label' => 'Active', 'class' => 'badge-success', 'is_expired' => false, 'icon' => 'fa-circle-check'];
};

$regStatus    = $getDocStatus($vehicle['registration_expiry'] ?? null);
$insStatus    = $getDocStatus($vehicle['insurance_expiry'] ?? null);
$permitStatus = $getDocStatus($vehicle['permit_expiry'] ?? null);
$fitStatus    = $getDocStatus($vehicle['fitness_expiry'] ?? null);

// Calculate overall compliance health
$totalDocs = 4;
$activeDocsCount = 0;
$hasExpired = false;
$hasWarning = false;

foreach ([$regStatus, $insStatus, $permitStatus, $fitStatus] as $st) {
    if ($st['label'] === 'Active') {
        $activeDocsCount++;
    } elseif ($st['is_expired']) {
        $hasExpired = true;
    } elseif ($st['label'] === 'Expiring Soon') {
        $hasWarning = true;
    }
}

if ($hasExpired) {
    $complianceHealth = ['label' => 'Action Needed', 'badge' => 'badge-danger', 'sub' => '1+ documents expired', 'color' => '#EF4444'];
} elseif ($hasWarning) {
    $complianceHealth = ['label' => 'Expiring Soon', 'badge' => 'badge-warning', 'sub' => 'Renewal due in 30 days', 'color' => '#D97706'];
} elseif ($activeDocsCount === $totalDocs) {
    $complianceHealth = ['label' => 'Fully Compliant', 'badge' => 'badge-success', 'sub' => 'All 4/4 documents active', 'color' => '#10B981'];
} else {
    $complianceHealth = ['label' => "{$activeDocsCount}/{$totalDocs} Active", 'badge' => 'badge-info', 'sub' => 'Some files pending', 'color' => '#176B5B'];
}

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/driver/dashboard') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Dashboard</span></a>'
    . '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-route" style="margin-right:6px;"></i><span>Assigned Trips</span></a>'
    . '</div>';
?>

<div class="bookings-page-view driver-portal-view driver-portal-container">
    <!-- 1. DESKTOP ONLY: Unified Executive Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Assigned Fleet Vehicle',
            'description' => 'Complete technical specifications, regulatory compliance, and operational telemetry for your assigned transport.',
            'badge'       => !empty($vehicle) ? ($isActiveAssignment ? 'Active Duty Fleet' : 'Last Assigned') : 'Fleet Standby',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Assigned Vehicle', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top 4 KPI Summary Cards Grid -->
    <?php if (!empty($vehicle)): ?>
        <div class="booking-kpi-grid" id="vehicleKpiGrid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1.5rem;">
            <!-- Card 1: Assigned Vehicle -->
            <div class="booking-kpi-card" title="<?= e(__('tooltip.assigned_vehicle_registration', 'Assigned vehicle registration and model')) ?>">
                <div class="booking-kpi-top">
                    <span class="booking-kpi-label">Assigned Vehicle</span>
                    <span class="booking-kpi-icon icon-teal">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </span>
                </div>
                <div class="booking-kpi-val" style="color: var(--brand-primary, #176B5B); font-size: 1.35rem;">
                    <?= e($vehicleNumber) ?>
                </div>
                <div class="booking-kpi-sub">
                    <span><?= e($makeModel) ?> &bull; <?= e(strtoupper($vehicleType)) ?></span>
                </div>
            </div>

            <!-- Card 2: Current Odometer -->
            <div class="booking-kpi-card" title="<?= e(__('tooltip.latest_verified_odometer_readi', 'Latest verified odometer reading')) ?>">
                <div class="booking-kpi-top">
                    <span class="booking-kpi-label">Current Odometer</span>
                    <span class="booking-kpi-icon icon-amber">
                        <i class="fa-solid fa-gauge-high"></i>
                    </span>
                </div>
                <div class="booking-kpi-val" style="color: #D97706; font-size: 1.35rem;">
                    <?= number_format($odometerKm) ?> km
                </div>
                <div class="booking-kpi-sub">
                    <span>Authorized meter reading</span>
                </div>
            </div>

            <!-- Card 3: Assignment Duty Status -->
            <div class="booking-kpi-card" title="<?= e(__('tooltip.current_assignment_status_of_t', 'Current assignment status of this transport')) ?>">
                <div class="booking-kpi-top">
                    <span class="booking-kpi-label">Assignment Duty</span>
                    <span class="booking-kpi-icon icon-emerald">
                        <i class="fa-solid fa-route"></i>
                    </span>
                </div>
                <div class="booking-kpi-val" style="color: <?= $isActiveAssignment ? '#10B981' : '#0F172A' ?>; font-size: 1.35rem;">
                    <?= $isActiveAssignment ? 'Active Duty' : 'Standby Fleet' ?>
                </div>
                <div class="booking-kpi-sub">
                    <?php if ($isActiveAssignment && !empty($dispatchCode)): ?>
                        <span class="pulse-dot" style="background:#10B981; box-shadow:0 0 0 2px rgba(16,185,129,0.25); display:inline-block; width:6px; height:6px; border-radius:50%; margin-right:4px;"></span>
                        <span><?= e($dispatchCode) ?></span>
                    <?php else: ?>
                        <span>Available for dispatch</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 4: Compliance Status -->
            <div class="booking-kpi-card" title="<?= e(__('tooltip.overall_regulatory_compliance', 'Overall regulatory compliance health')) ?>">
                <div class="booking-kpi-top">
                    <span class="booking-kpi-label">Compliance Health</span>
                    <span class="booking-kpi-icon icon-navy">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </div>
                <div class="booking-kpi-val" style="color: <?= $complianceHealth['color'] ?>; font-size: 1.35rem;">
                    <?= e($complianceHealth['label']) ?>
                </div>
                <div class="booking-kpi-sub">
                    <span><?= e($complianceHealth['sub']) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. MOBILE ONLY: Top App Bar & Quick Status Pills Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/driver/dashboard') ?>" class="mv-back-btn" title="<?= e(__('common.back_to_dashboard', 'Back to Dashboard')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Assigned Vehicle</h2>
            </div>
            <a href="<?= url('/driver/trips') ?>" class="mv-add-btn">
                <i class="fa-solid fa-route"></i>
                <span>Trips</span>
            </a>
        </div>

        <?php if (!empty($vehicle)): ?>
            <!-- Mobile Horizontal Status Scroll Pills -->
            <div class="mv-status-scroll" id="vehicleMvStatusScroll">
                <div class="mv-pill active">
                    <i class="fa-solid fa-car"></i>
                    <span><?= e($vehicleNumber) ?></span>
                </div>
                <div class="mv-pill">
                    <span class="pulse-dot" style="background: <?= $isActiveAssignment ? '#10B981' : '#94A3B8' ?>; display:inline-block; width:6px; height:6px; border-radius:50%;"></span>
                    <span><?= $isActiveAssignment ? 'Active Duty' : 'Standby' ?></span>
                </div>
                <div class="mv-pill">
                    <i class="fa-solid fa-gauge-high" style="color:#D97706;"></i>
                    <span><?= number_format($odometerKm) ?> km</span>
                </div>
                <div class="mv-pill">
                    <i class="fa-solid fa-shield-check" style="color:#10B981;"></i>
                    <span><?= e($complianceHealth['label']) ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 4. MAIN VEHICLE CONTENT -->
    <?php if (empty($vehicle)): ?>
        <!-- Professional Empty State -->
        <div class="driver-empty-card" style="background: #FFFFFF; border: 2px dashed #CBD5E1; border-radius: 16px; padding: 4rem 1.5rem; text-align: center; margin-top: 1rem;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(23, 107, 91, 0.1); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.25rem;">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #0F172A; margin: 0 0 0.5rem;">
                No Fleet Vehicle Assigned Currently
            </h2>
            <p style="font-size: 0.92rem; color: #64748B; max-width: 500px; margin: 0 auto 1.75rem; line-height: 1.6;">
                You currently have no fleet vehicle assigned to your driver profile. When fleet operations assigns a dispatch or journey, vehicle specifications, documents, and telemetry will appear here automatically.
            </p>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?= url('/driver/dashboard') ?>" class="btn btn-outline" style="border-radius: 10px; font-weight: 700;">
                    <i class="fa-solid fa-gauge" style="margin-right: 6px;"></i> Console Dashboard
                </a>
                <a href="<?= url('/driver/trips') ?>" class="btn btn-primary" style="border-radius: 10px; font-weight: 700;">
                    <i class="fa-solid fa-route" style="margin-right: 6px;"></i> View Assigned Trips
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Vehicle Content Grid (LG 2-Column / Mobile 1-Column) -->
        <div class="driver-vehicle-grid">
            <!-- Left Main Column: Hero Banner + Specs + Active Mission Route -->
            <div class="driver-vehicle-main">
                <!-- Vehicle Hero Profile Card -->
                <div class="driver-vehicle-hero-card">
                    <!-- Luxury Forest Teal Gradient Banner -->
                    <div class="driver-vehicle-hero-banner">
                        <div class="driver-vehicle-hero-top">
                            <div class="driver-vehicle-hero-info">
                                <div class="driver-vehicle-avatar-box">
                                    <i class="fa-solid fa-van-shuttle"></i>
                                </div>
                                <div>
                                    <div class="driver-vehicle-subhead">
                                        Assigned Driver Fleet
                                    </div>
                                    <h1 class="driver-vehicle-plate">
                                        <?= e($vehicleNumber) ?>
                                    </h1>
                                    <div class="driver-vehicle-tags">
                                        <span class="driver-vehicle-tag-make"><?= e($makeModel) ?></span>
                                        <span class="driver-vehicle-dot">&bull;</span>
                                        <span class="driver-vehicle-tag-type"><?= strtoupper($vehicleType) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="driver-vehicle-badge-wrap">
                                <span class="badge <?= $isActiveAssignment ? 'badge-success' : 'badge-navy' ?>" style="font-size: 0.82rem; padding: 0.45rem 0.9rem; border-radius: 8px; font-weight: 700;">
                                    <span class="pulse-indicator" style="background: <?= $isActiveAssignment ? '#10B981' : '#94A3B8' ?>;"></span>
                                    <?= $isActiveAssignment ? 'Active Assignment' : 'Standby Transport' ?>
                                </span>
                                <?php if (!empty($dispatchCode)): ?>
                                    <div style="font-size: 0.78rem; color: #94A3B8; margin-top: 4px;">
                                        Linked Dispatch: <strong style="color: #FFF;"><?= e($dispatchCode) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Hero Glass Bottom Stats Strip -->
                        <div class="driver-vehicle-hero-strip">
                            <div class="driver-v-strip-item">
                                <span class="driver-v-strip-lbl">Capacity</span>
                                <span class="driver-v-strip-val"><?= $seatingCapacity ?> Seats</span>
                            </div>
                            <div class="driver-v-strip-item">
                                <span class="driver-v-strip-lbl">Powertrain</span>
                                <span class="driver-v-strip-val"><?= e($fuelType) ?></span>
                            </div>
                            <div class="driver-v-strip-item">
                                <span class="driver-v-strip-lbl">Odometer</span>
                                <span class="driver-v-strip-val"><?= number_format($odometerKm) ?> km</span>
                            </div>
                            <div class="driver-v-strip-item">
                                <span class="driver-v-strip-lbl">Fleet Status</span>
                                <span class="driver-v-strip-val" style="color: #34D399;"><?= ucfirst($fleetStatus) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Specifications Card -->
                <div class="driver-vehicle-section-card">
                    <div class="driver-vehicle-section-head">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="driver-section-icon icon-teal">
                                <i class="fa-solid fa-sliders"></i>
                            </span>
                            <div>
                                <h3 class="driver-section-title">Technical Specifications</h3>
                                <p class="driver-section-desc">Authorized fleet transport parameters verified in registry</p>
                            </div>
                        </div>
                    </div>

                    <div class="driver-vehicle-specs-grid">
                        <!-- Registration Number -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Registration Plate</span>
                                <strong class="driver-spec-val"><?= e($vehicleNumber) ?></strong>
                            </div>
                        </div>

                        <!-- Make & Model -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Make &amp; Model</span>
                                <strong class="driver-spec-val"><?= e($makeModel) ?></strong>
                            </div>
                        </div>

                        <!-- Seating Capacity -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Seating Capacity</span>
                                <strong class="driver-spec-val"><?= $seatingCapacity ?> Guests + Driver</strong>
                            </div>
                        </div>

                        <!-- Fuel & Powertrain -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-gas-pump"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Fuel / Powertrain</span>
                                <strong class="driver-spec-val"><?= e($fuelType) ?></strong>
                            </div>
                        </div>

                        <!-- Current Odometer -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-gauge-high"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Fleet Meter Reading</span>
                                <strong class="driver-spec-val"><?= number_format($odometerKm) ?> km</strong>
                            </div>
                        </div>

                        <!-- Vehicle Class -->
                        <div class="driver-spec-box">
                            <div class="driver-spec-icon-box">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <div class="driver-spec-content">
                                <span class="driver-spec-lbl">Vehicle Category</span>
                                <strong class="driver-spec-val"><?= e($vehicleType) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Mission Route Card (When Active Assignment Exists) -->
                <?php if (!empty($pickupAddress) && !empty($dropAddress)): ?>
                    <div class="driver-vehicle-section-card">
                        <div class="driver-vehicle-section-head">
                            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="driver-section-icon icon-emerald">
                                        <i class="fa-solid fa-route"></i>
                                    </span>
                                    <div>
                                        <h3 class="driver-section-title">Active Mission Route</h3>
                                        <p class="driver-section-desc">Current operational routing for this transport</p>
                                    </div>
                                </div>
                                <?php if (!empty($bookingCode)): ?>
                                    <span class="cust-code-pill" style="font-size: 0.82rem; padding: 4px 10px;"><?= e($bookingCode) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="driver-mission-route-box">
                            <!-- Visual Route Nodes -->
                            <div class="driver-route-nodes">
                                <div class="driver-route-node is-pickup">
                                    <div class="driver-node-indicator is-pickup"></div>
                                    <div class="driver-node-info">
                                        <span class="driver-node-lbl is-pickup">Pickup Location</span>
                                        <strong class="driver-node-address"><?= e($pickupAddress) ?></strong>
                                    </div>
                                </div>

                                <div class="driver-route-node is-drop">
                                    <div class="driver-node-indicator is-drop"></div>
                                    <div class="driver-node-info">
                                        <span class="driver-node-lbl is-drop">Destination</span>
                                        <strong class="driver-node-address"><?= e($dropAddress) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar Column: Compliance Documents + Pre-Trip Checklist -->
            <div class="driver-vehicle-side">
                <!-- Safety & Regulatory Compliance Card -->
                <div class="driver-vehicle-section-card">
                    <div class="driver-vehicle-section-head">
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="driver-section-icon icon-teal">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <div>
                                    <h3 class="driver-section-title">Compliance Documents</h3>
                                    <p class="driver-section-desc">Safety &amp; regulatory verification</p>
                                </div>
                            </div>
                            <span class="badge <?= $complianceHealth['badge'] ?>" style="font-size: 0.72rem; padding: 3px 7px;">
                                <?= $activeDocsCount ?>/<?= $totalDocs ?>
                            </span>
                        </div>
                    </div>

                    <div class="driver-compliance-list">
                        <!-- 1. Registration Certificate -->
                        <div class="driver-compliance-item">
                            <div class="driver-comp-left">
                                <div class="driver-comp-icon">
                                    <i class="fa-solid fa-file-contract"></i>
                                </div>
                                <div class="driver-comp-meta">
                                    <span class="driver-comp-title">Registration Certificate (RC)</span>
                                    <span class="driver-comp-expiry">
                                        Expires: <strong><?= !empty($vehicle['registration_expiry']) ? format_date($vehicle['registration_expiry']) : 'Not Specified' ?></strong>
                                    </span>
                                </div>
                            </div>
                            <span class="badge <?= $regStatus['class'] ?>"><?= $regStatus['label'] ?></span>
                        </div>

                        <!-- 2. Commercial Insurance -->
                        <div class="driver-compliance-item">
                            <div class="driver-comp-left">
                                <div class="driver-comp-icon">
                                    <i class="fa-solid fa-file-shield"></i>
                                </div>
                                <div class="driver-comp-meta">
                                    <span class="driver-comp-title">Commercial Insurance</span>
                                    <span class="driver-comp-expiry">
                                        Expires: <strong><?= !empty($vehicle['insurance_expiry']) ? format_date($vehicle['insurance_expiry']) : 'Not Specified' ?></strong>
                                    </span>
                                </div>
                            </div>
                            <span class="badge <?= $insStatus['class'] ?>"><?= $insStatus['label'] ?></span>
                        </div>

                        <!-- 3. Tourist Permit -->
                        <div class="driver-compliance-item">
                            <div class="driver-comp-left">
                                <div class="driver-comp-icon">
                                    <i class="fa-solid fa-passport"></i>
                                </div>
                                <div class="driver-comp-meta">
                                    <span class="driver-comp-title">Tourist Commercial Permit</span>
                                    <span class="driver-comp-expiry">
                                        Expires: <strong><?= !empty($vehicle['permit_expiry']) ? format_date($vehicle['permit_expiry']) : 'Not Specified' ?></strong>
                                    </span>
                                </div>
                            </div>
                            <span class="badge <?= $permitStatus['class'] ?>"><?= $permitStatus['label'] ?></span>
                        </div>

                        <!-- 4. Fitness Certificate -->
                        <div class="driver-compliance-item">
                            <div class="driver-comp-left">
                                <div class="driver-comp-icon">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <div class="driver-comp-meta">
                                    <span class="driver-comp-title">Fitness Certificate</span>
                                    <span class="driver-comp-expiry">
                                        Expires: <strong><?= !empty($vehicle['fitness_expiry']) ? format_date($vehicle['fitness_expiry']) : 'Not Specified' ?></strong>
                                    </span>
                                </div>
                            </div>
                            <span class="badge <?= $fitStatus['class'] ?>"><?= $fitStatus['label'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Driver Pre-Trip Inspection Card -->
                <div class="driver-vehicle-section-card">
                    <div class="driver-vehicle-section-head">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="driver-section-icon icon-amber">
                                <i class="fa-solid fa-list-check"></i>
                            </span>
                            <div>
                                <h3 class="driver-section-title">Pre-Trip Inspection Protocol</h3>
                                <p class="driver-section-desc">Mandatory checks before every departure</p>
                            </div>
                        </div>
                    </div>

                    <div class="driver-checklist-items">
                        <div class="driver-checklist-item">
                            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 0.95rem;"></i>
                            <div>
                                <strong style="color: #0F172A; font-size: 0.85rem; display: block;">Meter Reading &amp; Fuel</strong>
                                <span style="font-size: 0.76rem; color: #64748B;">Verify odometer matches app reading &amp; tank is fueled</span>
                            </div>
                        </div>
                        <div class="driver-checklist-item">
                            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 0.95rem;"></i>
                            <div>
                                <strong style="color: #0F172A; font-size: 0.85rem; display: block;">Vehicle Photos &amp; Condition</strong>
                                <span style="font-size: 0.76rem; color: #64748B;">Capture clean starting inspection photo in driver console</span>
                            </div>
                        </div>
                        <div class="driver-checklist-item">
                            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 0.95rem;"></i>
                            <div>
                                <strong style="color: #0F172A; font-size: 0.85rem; display: block;">Tires &amp; Safety Equipment</strong>
                                <span style="font-size: 0.76rem; color: #64748B;">Check tire pressure, spare wheel &amp; first aid emergency kit</span>
                            </div>
                        </div>
                        <div class="driver-checklist-item">
                            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 0.95rem;"></i>
                            <div>
                                <strong style="color: #0F172A; font-size: 0.85rem; display: block;">Sanitized Passenger Cabin</strong>
                                <span style="font-size: 0.76rem; color: #64748B;">Ensure interior AC, seat belts, and cleanliness are VIP ready</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support & Quick Links Card -->
                <div class="driver-vehicle-section-card" style="background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.85rem;">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(23, 107, 91, 0.12); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <strong style="color: #0F172A; font-size: 0.92rem; display: block;">Fleet Operations Support</strong>
                            <span style="font-size: 0.75rem; color: #64748B;">Need maintenance or vehicle assistance?</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.65rem;">
                        <a href="<?= url('/driver/trips') ?>" class="btn btn-primary btn-sm" style="flex: 1; text-align: center; border-radius: 8px;">
                            <i class="fa-solid fa-route" style="margin-right: 4px;"></i> My Trips
                        </a>
                        <a href="<?= url('/driver/dashboard') ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center; border-radius: 8px;">
                            <i class="fa-solid fa-gauge" style="margin-right: 4px;"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* ========================================================================= */
/* Driver Vehicle Scoped Design System & Responsive Rules                    */
/* ========================================================================= */

.driver-vehicle-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 992px) {
    .driver-vehicle-grid {
        grid-template-columns: 1.85fr 1.15fr;
        gap: 1.5rem;
        align-items: start;
    }
}

/* 1. Hero Card & Luxury Banner */
.driver-vehicle-hero-card {
    background: #FFFFFF;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid #E2E8F0;
    margin-bottom: 1.5rem;
}

.driver-vehicle-hero-banner {
    background: linear-gradient(135deg, #0F172A 0%, #16443B 55%, #176B5B 100%);
    color: #FFFFFF;
    padding: 1.75rem 2rem;
    position: relative;
    border-bottom: 3px solid #10B981;
}

.driver-vehicle-hero-top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
}

.driver-vehicle-hero-info {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.driver-vehicle-avatar-box {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.12);
    border: 2px solid rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #FFFFFF;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
}

.driver-vehicle-subhead {
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #34D399;
    font-weight: 800;
}

.driver-vehicle-plate {
    font-size: 1.85rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0.2rem 0;
    letter-spacing: -0.01em;
    line-height: 1.15;
}

.driver-vehicle-tags {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 0.88rem;
    color: #CBD5E1;
}

.driver-vehicle-tag-type {
    background: rgba(255, 255, 255, 0.15);
    color: #FFFFFF;
    font-size: 0.72rem;
    padding: 2px 7px;
    border-radius: 4px;
    font-weight: 700;
}

.driver-vehicle-badge-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.35rem;
}

.driver-vehicle-hero-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.driver-v-strip-item {
    display: flex;
    flex-direction: column;
}

.driver-v-strip-lbl {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94A3B8;
    font-weight: 700;
    margin-bottom: 2px;
}

.driver-v-strip-val {
    font-size: 0.95rem;
    font-weight: 800;
    color: #FFFFFF;
}

/* 2. Section Cards & Specs */
.driver-vehicle-section-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
}

.driver-vehicle-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.driver-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.driver-section-icon.icon-teal {
    background: rgba(23, 107, 91, 0.12);
    color: #176B5B;
}

.driver-section-icon.icon-amber {
    background: rgba(217, 119, 6, 0.12);
    color: #D97706;
}

.driver-section-icon.icon-emerald {
    background: rgba(16, 185, 129, 0.12);
    color: #10B981;
}

.driver-section-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    line-height: 1.2;
}

.driver-section-desc {
    font-size: 0.78rem;
    color: #64748B;
    margin: 2px 0 0 0;
}

.driver-vehicle-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.85rem;
}

.driver-spec-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.95rem 1.15rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.driver-spec-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #176B5B;
    flex-shrink: 0;
}

.driver-spec-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.driver-spec-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.driver-spec-val {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 3. Active Mission Route */
.driver-mission-route-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 1.15rem 1.35rem;
}

.driver-route-nodes {
    position: relative;
    padding-left: 2rem;
}

.driver-route-nodes::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 14px;
    bottom: 14px;
    width: 2px;
    background: #CBD5E1;
}

.driver-route-node {
    position: relative;
}

.driver-route-node.is-pickup {
    margin-bottom: 1.25rem;
}

.driver-node-indicator {
    position: absolute;
    left: -2rem;
    top: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid #FFFFFF;
    box-shadow: 0 0 0 1px #CBD5E1;
}

.driver-node-indicator.is-pickup {
    background: #10B981;
    box-shadow: 0 0 0 1px #10B981;
}

.driver-node-indicator.is-drop {
    background: #EF4444;
    box-shadow: 0 0 0 1px #EF4444;
}

.driver-node-lbl {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
}

.driver-node-lbl.is-pickup {
    color: #10B981;
}

.driver-node-lbl.is-drop {
    color: #EF4444;
}

.driver-node-address {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0F172A;
    display: block;
    margin-top: 2px;
    line-height: 1.35;
}

/* 4. Compliance Documents List */
.driver-compliance-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.driver-compliance-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.driver-comp-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.driver-comp-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: #176B5B;
    flex-shrink: 0;
}

.driver-comp-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.driver-comp-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.driver-comp-expiry {
    font-size: 0.74rem;
    color: #64748B;
    margin-top: 1px;
}

.driver-comp-expiry strong {
    color: #334155;
}

/* 5. Pre-Trip Checklist */
.driver-checklist-items {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.driver-checklist-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding-bottom: 0.65rem;
    border-bottom: 1px dashed #E2E8F0;
}

.driver-checklist-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

/* 6. Mobile & Desktop Display Breakpoints */
.mv-bookings-header {
    display: none;
    margin-bottom: 12px;
}

.mv-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 0 12px 0;
}

.mv-top-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mv-back-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #F1F5F9;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.mv-page-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.mv-add-btn {
    padding: 0.45rem 0.85rem;
    background: linear-gradient(135deg, #176B5B 0%, #125748 100%);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
    background: #176B5B;
    color: #fff;
    border-color: #176B5B;
}

@media (max-width: 768px) {
    .desktop-header-wrapper,
    .booking-kpi-grid {
        display: none !important;
    }
    .mv-bookings-header {
        display: block !important;
    }
    .driver-vehicle-hero-banner {
        padding: 1.25rem;
    }
    .driver-vehicle-hero-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .driver-vehicle-badge-wrap {
        align-items: flex-start;
    }
    .driver-vehicle-plate {
        font-size: 1.45rem;
    }
    .driver-vehicle-avatar-box {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        border-radius: 12px;
    }
    .driver-vehicle-hero-strip {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
    }
    .driver-vehicle-specs-grid {
        grid-template-columns: 1fr;
        gap: 0.65rem;
    }
    .driver-vehicle-section-card {
        padding: 1.15rem;
        margin-bottom: 1rem;
    }
}

@media (min-width: 769px) {
    .mv-bookings-header {
        display: none !important;
    }
    .desktop-header-wrapper {
        display: block !important;
    }
    .booking-kpi-grid {
        display: grid !important;
    }
}
</style>
