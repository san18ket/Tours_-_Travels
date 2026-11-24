<?php
/**
 * Office Trip Evidence & Inspection Console
 * Displays pre-trip and post-trip vehicle inspection photos, meter readings, GPS route, and audit trail
 */
?>

<div class="content-wrapper" style="padding: 1.5rem; max-width: 1400px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #64748B; margin-bottom: 0.35rem;">
                <a href="<?= url('/office/trips/history') ?>" style="color: var(--brand-primary, #176B5B); text-decoration: none;">
                    &larr; Trip Operations History
                </a>
                <span>/</span>
                <span style="font-family: ui-monospace, monospace; font-weight: 600;"><?= e($dispatch['dispatch_code']) ?></span>
                <span>/</span>
                <span><?= e(__('trip.inspection_evidence', 'Inspection Evidence')) ?></span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-camera-retro" style="color: var(--brand-primary, #176B5B);"></i>
                Trip Inspection &amp; Telemetry Evidence
            </h1>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <?php
                $st = strtolower($dispatch['status'] ?? 'assigned');
                $badgeClass = match($st) {
                    'completed'    => 'background: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;',
                    'trip_started' => 'background: #E1EFFE; color: #1E429F; border: 1px solid #B4C6FC;',
                    'driver_accepted' => 'background: #EDEBFE; color: #5521B5; border: 1px solid #DCD7FE;',
                    'cancelled'    => 'background: #FDE8E8; color: #9B1C1C; border: 1px solid #F8B4B4;',
                    default        => 'background: #FEF08A; color: #713F12; border: 1px solid #FDE047;'
                };
            ?>
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; <?= $badgeClass ?>">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>
                <?= e(dispatch_status_label($st)) ?>
            </span>
            <a href="<?= url('/office/bookings/' . $dispatch['booking_id']) ?>" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.45rem 0.95rem; border-radius: 6px; border: 1px solid #CBD5E1; color: #334155; text-decoration: none;">
                <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> View Booking #<?= e($booking['booking_code'] ?? $dispatch['booking_id']) ?>
            </a>
            <a href="<?= url('/office/trips/history') ?>" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.45rem 0.95rem; border-radius: 6px; background: #F1F5F9; color: #475569; text-decoration: none;">
                &larr; <?= e(__('common.back', 'Back to History')) ?>
            </a>
        </div>
    </div>

    <!-- Quick Stats Summary Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Vehicle Card -->
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748B; font-weight: 700; margin-bottom: 4px;">
                <i class="fa-solid fa-car" style="color: var(--brand-primary, #176B5B); margin-right: 4px;"></i> Assigned Fleet
            </div>
            <div style="font-weight: 800; font-size: 1.1rem; color: #0F172A;">
                <?= e($vehicle['vehicle_number'] ?? '—') ?>
            </div>
            <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                <?= e($vehicle['make_model'] ?? 'Fleet Unit') ?> (<?= ucfirst(e($vehicle['vehicle_type'] ?? 'car')) ?>)
            </div>
        </div>

        <!-- Driver Card -->
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748B; font-weight: 700; margin-bottom: 4px;">
                <i class="fa-solid fa-id-badge" style="color: #2563EB; margin-right: 4px;"></i> Assigned Driver
            </div>
            <div style="font-weight: 800; font-size: 1.1rem; color: #0F172A;">
                <?= e($driver['driver_name'] ?? $driver['name'] ?? 'Driver #' . $dispatch['driver_id']) ?>
            </div>
            <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                Lic: <?= e($driver['license_number'] ?? 'Verified') ?> &bull; Contact: <?= e($driver['phone'] ?? $driver['emergency_contact'] ?? '—') ?>
            </div>
        </div>

        <!-- Odometer Comparison -->
        <?php
            $startKm = (int)($dispatch['start_odometer'] ?? 0);
            $endKm = (int)($dispatch['end_odometer'] ?? 0);
            $distKm = ($endKm >= $startKm && $startKm > 0) ? ($endKm - $startKm) : null;
        ?>
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748B; font-weight: 700; margin-bottom: 4px;">
                <i class="fa-solid fa-gauge-high" style="color: #D97706; margin-right: 4px;"></i> Verified Distance
            </div>
            <div style="font-weight: 800; font-size: 1.1rem; color: #0F172A;">
                <?= $distKm !== null ? number_format($distKm) . ' KM' : ($st === 'trip_started' ? 'In Progress' : '—') ?>
            </div>
            <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                Start: <?= $startKm > 0 ? number_format($startKm) . ' km' : '—' ?> &bull; End: <?= $endKm > 0 ? number_format($endKm) . ' km' : 'Pending' ?>
            </div>
        </div>

        <!-- Telemetry Points -->
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748B; font-weight: 700; margin-bottom: 4px;">
                <i class="fa-solid fa-satellite-dish" style="color: #059669; margin-right: 4px;"></i> GPS Telemetry
            </div>
            <div style="font-weight: 800; font-size: 1.1rem; color: #0F172A;">
                <?= count($gpsTrail ?? []) ?> Waypoints
            </div>
            <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                <?= count($gpsTrail ?? []) > 0 ? 'Live coordinates tracked' : 'No telemetry points logged' ?>
            </div>
        </div>
    </div>

    <!-- Dual-Column Evidence Grid: Pre-Trip vs Post-Trip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- 1. PRE-TRIP VEHICLE INSPECTION CARD -->
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
            <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #DEF7EC; color: #03543F; font-size: 0.85rem; font-weight: 800;">
                        1
                    </span>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0F172A;"><?= e(__('trip.pre_trip_inspection', 'Pre-Trip Departure Inspection')) ?></h3>
                </div>
                <?php if (!empty($dispatch['start_time'])): ?>
                    <span style="font-size: 0.78rem; font-weight: 600; color: #059669; background: #ECFDF5; padding: 3px 8px; border-radius: 4px; border: 1px solid #A7F3D0;">
                        <?= date('d M Y, h:i A', strtotime($dispatch['start_time'])) ?>
                    </span>
                <?php else: ?>
                    <span style="font-size: 0.78rem; color: #94A3B8;"><?= e(__('trip.not_started_yet', 'Not Started Yet')) ?></span>
                <?php endif; ?>
            </div>

            <div style="padding: 1.25rem;">
                <!-- Meter Reading Metric -->
                <div style="background: #F1F5F9; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;"><?= e(__('trip.starting_odo_reading', 'Starting Odometer Reading')) ?></div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #0F172A; font-family: ui-monospace, monospace;">
                            <?= $startKm > 0 ? number_format($startKm) . ' KM' : '<span style="color:#94A3B8;font-size:1rem;">' . e(__('trip.not_recorded', 'Not recorded')) . '</span>' ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-play" style="font-size: 1.5rem; color: #10B981; opacity: 0.75;"></i>
                </div>

                <!-- Photos Section -->
                <div style="margin-bottom: 1.25rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">
                        Departure Photos &amp; Verification Evidence
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <!-- Start Odometer Photo -->
                        <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #F8FAFC;">
                            <div style="padding: 6px 10px; font-size: 0.75rem; font-weight: 700; color: #475569; background: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                <i class="fa-solid fa-gauge" style="margin-right: 4px; color: #D97706;"></i> Meter Photo
                            </div>
                            <?php if (!empty($evidence['start_odometer'])): ?>
                                <a href="<?= e($evidence['start_odometer']['url']) ?>" target="_blank" onclick="openLightbox('<?= e($evidence['start_odometer']['url']) ?>', 'Starting Meter Reading Photo'); return false;" style="display: block; position: relative;">
                                    <img src="<?= e($evidence['start_odometer']['url']) ?>" alt="Starting Meter" style="width: 100%; height: 160px; object-fit: cover; display: block;">
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #FFF; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> View
                                    </div>
                                </a>
                                <div style="padding: 6px 8px; font-size: 0.72rem; color: #64748B;">
                                    Captured: <?= date('h:i A', strtotime($evidence['start_odometer']['uploaded_at'])) ?>
                                </div>
                            <?php else: ?>
                                <div style="height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94A3B8; text-align: center; padding: 1rem;">
                                    <i class="fa-regular fa-image" style="font-size: 2rem; margin-bottom: 6px;"></i>
                                    <span style="font-size: 0.78rem;"><?= e(__('trip.no_meter_photo', 'No meter photo captured')) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Start Vehicle Photo -->
                        <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #F8FAFC;">
                            <div style="padding: 6px 10px; font-size: 0.75rem; font-weight: 700; color: #475569; background: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                <i class="fa-solid fa-car-side" style="margin-right: 4px; color: var(--brand-primary, #176B5B);"></i> Vehicle Condition
                            </div>
                            <?php if (!empty($evidence['start_vehicle'])): ?>
                                <a href="<?= e($evidence['start_vehicle']['url']) ?>" target="_blank" onclick="openLightbox('<?= e($evidence['start_vehicle']['url']) ?>', 'Starting Vehicle Inspection Photo'); return false;" style="display: block; position: relative;">
                                    <img src="<?= e($evidence['start_vehicle']['url']) ?>" alt="Starting Vehicle" style="width: 100%; height: 160px; object-fit: cover; display: block;">
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #FFF; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> View
                                    </div>
                                </a>
                                <div style="padding: 6px 8px; font-size: 0.72rem; color: #64748B;">
                                    Captured: <?= date('h:i A', strtotime($evidence['start_vehicle']['uploaded_at'])) ?>
                                </div>
                            <?php else: ?>
                                <div style="height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94A3B8; text-align: center; padding: 1rem;">
                                    <i class="fa-regular fa-image" style="font-size: 2rem; margin-bottom: 6px;"></i>
                                    <span style="font-size: 0.78rem;">No vehicle photo captured</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Departure Notes -->
                <?php if (!empty($dispatch['driver_notes'])): ?>
                    <div style="background: #F8FAFC; border-left: 3px solid var(--brand-primary, #176B5B); padding: 0.75rem 1rem; border-radius: 0 6px 6px 0; font-size: 0.83rem;">
                        <span style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px;">Driver Departure Notes:</span>
                        <div style="color: #475569; white-space: pre-wrap;"><?= e($dispatch['driver_notes']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. POST-TRIP VEHICLE INSPECTION CARD -->
        <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
            <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #DEF7EC; color: #03543F; font-size: 0.85rem; font-weight: 800;">
                        2
                    </span>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0F172A;"><?= e(__('trip.post_trip_inspection', 'Post-Trip Arrival Inspection')) ?></h3>
                </div>
                <?php if (!empty($dispatch['end_time'])): ?>
                    <span style="font-size: 0.78rem; font-weight: 600; color: #059669; background: #ECFDF5; padding: 3px 8px; border-radius: 4px; border: 1px solid #A7F3D0;">
                        <?= date('d M Y, h:i A', strtotime($dispatch['end_time'])) ?>
                    </span>
                <?php else: ?>
                    <span style="font-size: 0.78rem; color: #D97706; background: #FEF3C7; padding: 3px 8px; border-radius: 4px; border: 1px solid #FCD34D;">
                        <?= $st === 'trip_started' ? e(__('common.in_transit', 'In Progress')) : e(__('trip.pending_arrival', 'Pending Completion')) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div style="padding: 1.25rem;">
                <!-- Meter Reading Metric -->
                <div style="background: #F1F5F9; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;"><?= e(__('trip.ending_odo_reading', 'Ending Odometer Reading')) ?></div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #0F172A; font-family: ui-monospace, monospace;">
                            <?= $endKm > 0 ? number_format($endKm) . ' KM' : '<span style="color:#94A3B8;font-size:1rem;">' . e(__('trip.pending_arrival', 'Pending arrival')) . '</span>' ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-flag-checkered" style="font-size: 1.5rem; color: #059669; opacity: 0.75;"></i>
                </div>

                <!-- Photos Section -->
                <div style="margin-bottom: 1.25rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">
                        Arrival Photos &amp; Inspection Evidence
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <!-- End Odometer Photo -->
                        <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #F8FAFC;">
                            <div style="padding: 6px 10px; font-size: 0.75rem; font-weight: 700; color: #475569; background: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                <i class="fa-solid fa-gauge" style="margin-right: 4px; color: #D97706;"></i> Final Meter Photo
                            </div>
                            <?php if (!empty($evidence['end_odometer'])): ?>
                                <a href="<?= e($evidence['end_odometer']['url']) ?>" target="_blank" onclick="openLightbox('<?= e($evidence['end_odometer']['url']) ?>', 'Final Meter Reading Photo'); return false;" style="display: block; position: relative;">
                                    <img src="<?= e($evidence['end_odometer']['url']) ?>" alt="Final Meter" style="width: 100%; height: 160px; object-fit: cover; display: block;">
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #FFF; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> View
                                    </div>
                                </a>
                                <div style="padding: 6px 8px; font-size: 0.72rem; color: #64748B;">
                                    Captured: <?= date('h:i A', strtotime($evidence['end_odometer']['uploaded_at'])) ?>
                                </div>
                            <?php else: ?>
                                <div style="height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94A3B8; text-align: center; padding: 1rem;">
                                    <i class="fa-regular fa-image" style="font-size: 2rem; margin-bottom: 6px;"></i>
                                    <span style="font-size: 0.78rem;"><?= e(__('trip.no_final_meter_photo', 'No final meter photo')) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- End Vehicle Photo -->
                        <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #F8FAFC;">
                            <div style="padding: 6px 10px; font-size: 0.75rem; font-weight: 700; color: #475569; background: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                <i class="fa-solid fa-car-side" style="margin-right: 4px; color: var(--brand-primary, #176B5B);"></i> Returned Condition
                            </div>
                            <?php if (!empty($evidence['end_vehicle'])): ?>
                                <a href="<?= e($evidence['end_vehicle']['url']) ?>" target="_blank" onclick="openLightbox('<?= e($evidence['end_vehicle']['url']) ?>', 'Final Vehicle Condition Photo'); return false;" style="display: block; position: relative;">
                                    <img src="<?= e($evidence['end_vehicle']['url']) ?>" alt="Final Vehicle" style="width: 100%; height: 160px; object-fit: cover; display: block;">
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #FFF; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> View
                                    </div>
                                </a>
                                <div style="padding: 6px 8px; font-size: 0.72rem; color: #64748B;">
                                    Captured: <?= date('h:i A', strtotime($evidence['end_vehicle']['uploaded_at'])) ?>
                                </div>
                            <?php else: ?>
                                <div style="height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94A3B8; text-align: center; padding: 1rem;">
                                    <i class="fa-regular fa-image" style="font-size: 2rem; margin-bottom: 6px;"></i>
                                    <span style="font-size: 0.78rem;"><?= e(__('trip.no_arrival_vehicle_photo', 'No arrival vehicle photo')) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Distance Calculation Summary -->
                <?php if ($distKm !== null): ?>
                    <div style="background: #DEF7EC; border: 1px solid #BCF0DA; border-radius: 6px; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;">
                        <span style="color: #03543F; font-weight: 700;"><?= e(__('trip.verified_distance', 'Verified Journey Distance:')) ?></span>
                        <strong style="color: #03543F; font-size: 1rem;"><?= number_format($distKm) ?> <?= e(__('trip.km_travelled', 'KM travelled')) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- GPS Trail & Waypoints Map -->
    <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 2rem;">
        <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-map-location-dot" style="color: #2563EB;"></i>
                GPS Journey Route &amp; Breadcrumb Telemetry
            </h3>
            <span style="font-size: 0.82rem; color: #64748B;">
                <?= count($gpsTrail ?? []) ?> Logged Coordinates
            </span>
        </div>

        <div style="padding: 1.25rem;">
            <?php if (!empty($gpsTrail)): ?>
                <!-- OpenStreetMap container -->
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                
                <div id="tripEvidenceMap" style="width: 100%; height: 380px; border-radius: 8px; border: 1px solid #CBD5E1;"></div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const rawPoints = <?= json_encode($gpsTrail) ?>;
                    if (!rawPoints || rawPoints.length === 0) return;

                    const validPoints = rawPoints
                        .map(p => [parseFloat(p.latitude), parseFloat(p.longitude)])
                        .filter(p => !isNaN(p[0]) && !isNaN(p[1]) && (p[0] !== 0 || p[1] !== 0));

                    if (validPoints.length === 0) return;

                    const map = L.map('tripEvidenceMap').setView(validPoints[0], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // Polyline route
                    const routeLine = L.polyline(validPoints, {
                        color: '#176B5B',
                        weight: 5,
                        opacity: 0.85,
                        smoothFactor: 1
                    }).addTo(map);

                    // Start marker (Green)
                    L.circleMarker(validPoints[0], {
                        radius: 8,
                        fillColor: '#10B981',
                        color: '#FFFFFF',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 1
                    }).bindPopup('<b>Departure Location</b><br>' + (rawPoints[0].recorded_at || '')).addTo(map);

                    // End marker (Red)
                    if (validPoints.length > 1) {
                        const lastIdx = validPoints.length - 1;
                        L.circleMarker(validPoints[lastIdx], {
                            radius: 8,
                            fillColor: '#EF4444',
                            color: '#FFFFFF',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 1
                        }).bindPopup('<b>Arrival / Latest Location</b><br>' + (rawPoints[lastIdx].recorded_at || '')).addTo(map);
                    }

                    map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
                });
                </script>
            <?php else: ?>
                <div style="padding: 2.5rem; text-align: center; color: #94A3B8; background: #F8FAFC; border-radius: 8px; border: 1px dashed #CBD5E1;">
                    <i class="fa-solid fa-map-pin" style="font-size: 2.5rem; margin-bottom: 0.5rem; color: #CBD5E1;"></i>
                    <p style="margin: 0; font-size: 0.95rem; font-weight: 600; color: #64748B;"><?= e(__('trip.no_gps_breadcrumbs', 'No GPS breadcrumbs logged for this dispatch yet.')) ?></p>
                    <p style="margin: 4px 0 0; font-size: 0.82rem;">Telemetry begins automatically when the driver starts the trip via the Driver Portal or Mobile App.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Photo Inspection -->
<div id="evidenceLightbox" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; padding:1.5rem;" onclick="closeLightbox()">
    <div style="position:relative; max-width:900px; width:100%; max-height:90vh; text-align:center;" onclick="event.stopPropagation()">
        <button type="button" onclick="closeLightbox()" style="position:absolute; top:-36px; right:0; background:none; border:none; color:#FFF; font-size:1.8rem; cursor:pointer;">&times;</button>
        <div id="lightboxTitle" style="color:#FFF; font-weight:700; margin-bottom:10px; font-size:1rem;"></div>
        <img id="lightboxImg" src="" alt="Evidence Full" style="max-width:100%; max-height:80vh; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.5); object-fit:contain;">
    </div>
</div>

<script>
function openLightbox(url, title) {
    const box = document.getElementById('evidenceLightbox');
    const img = document.getElementById('lightboxImg');
    const titleEl = document.getElementById('lightboxTitle');
    img.src = url;
    titleEl.textContent = title || 'Inspection Photo';
    box.style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('evidenceLightbox').style.display = 'none';
}
</script>

