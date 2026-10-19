<!-- Driver Duty Console -->
<div class="driver-duty-card">
    <div class="driver-duty-header">
        <div>
            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:#94A3B8;">Chauffeur Duty Status</div>
            <div style="font-size:1.4rem; font-weight:800; color:#FFF; display:flex; align-items:center; gap:0.5rem;">
                <?php if ($driver['duty_status'] === 'on_duty'): ?>
                    <span class="badge badge-success" style="font-size:0.85rem;"><span class="pulse-indicator"></span> ON DUTY</span>
                <?php elseif ($driver['duty_status'] === 'on_trip'): ?>
                    <span class="badge badge-info" style="font-size:0.85rem;"><span class="pulse-indicator"></span> ON ACTIVE TRIP</span>
                <?php else: ?>
                    <span class="badge badge-neutral" style="font-size:0.85rem;">OFF DUTY</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($driver['duty_status'] !== 'on_trip'): ?>
            <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="btn <?= $driver['duty_status'] === 'on_duty' ? 'btn-danger' : 'btn-success' ?> btn-sm">
                    <?= $driver['duty_status'] === 'on_duty' ? 'Go Off Duty' : 'Go On Duty' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Live Telemetry Status -->
    <div style="background:rgba(255,255,255,0.08); padding:0.75rem 1rem; border-radius:10px; display:flex; align-items:center; justify-content:space-between; font-size:0.82rem;">
        <div style="display:flex; align-items:center; gap:0.5rem;">
            <span id="gpsRadarIndicator" class="pulse-indicator" style="display:none;"></span>
            <span id="gpsStatusText" style="color:#CBD5E1;">Standby (Trip Not Started)</span>
        </div>
        <div style="color:#94A3B8;">
            Synced: <span id="lastGpsSyncTime"><?= !empty($driver['last_gps_at']) ? format_datetime($driver['last_gps_at'], 'H:i:s') : '—' ?></span>
        </div>
    </div>
</div>

<!-- Active Trip Assignment -->
<?php if ($activeTrip): ?>
    <div class="active-trip-box">
        <div class="active-trip-header">
            <div>
                <span style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; color:#94A3B8;">Active Duty Assignment</span>
                <div style="font-size:1.1rem; font-weight:800; color:#FFF;"><?= e($activeTrip['dispatch_code']) ?></div>
            </div>
            <span class="badge <?= $activeTrip['status'] === 'trip_started' ? 'badge-success' : 'badge-warning' ?>">
                <?= e(ucfirst(str_replace('_', ' ', $activeTrip['status']))) ?>
            </span>
        </div>

        <div style="padding: 1.25rem;">
            <!-- Guest & Route Info -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid #E2E8F0;">
                <div>
                    <div style="font-size:0.78rem; text-transform:uppercase; color:#64748B; font-weight:700;">Guest Passenger</div>
                    <div style="font-size:1.05rem; font-weight:700; color:var(--navy-900);"><?= e($activeTrip['customer_name']) ?></div>
                    <div style="font-size:0.85rem; color:#64748B;"><?= (int)$activeTrip['passenger_count'] ?> Guests</div>
                </div>
                <div>
                    <a href="tel:<?= e($activeTrip['customer_phone']) ?>" class="btn btn-primary btn-sm" style="border-radius:20px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <span>Call Guest</span>
                    </a>
                </div>
            </div>

            <!-- Route Timeline -->
            <div class="location-timeline">
                <div class="location-point">
                    <div style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:#008DDA;">Pickup Point</div>
                    <div style="font-size:0.95rem; font-weight:700; color:var(--navy-900);"><?= e($activeTrip['pickup_address']) ?></div>
                    <div style="font-size:0.8rem; color:#64748B;">Date: <?= format_date($activeTrip['start_date']) ?> at <?= e($activeTrip['pickup_time']) ?></div>
                </div>
                <div class="location-point destination">
                    <div style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:#10B981;">Destination</div>
                    <div style="font-size:0.95rem; font-weight:700; color:var(--navy-900);"><?= e($activeTrip['drop_address']) ?></div>
                    <div style="font-size:0.8rem; color:#64748B;">Return Date: <?= format_date($activeTrip['end_date']) ?></div>
                </div>
            </div>

            <!-- Vehicle Info -->
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:0.85rem 1rem; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:0.75rem; text-transform:uppercase; color:#64748B; font-weight:700;">Vehicle Assigned</div>
                    <div style="font-size:0.95rem; font-weight:700; color:var(--navy-900);"><?= e($activeTrip['make_model']) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.95rem; font-weight:700; color:#008DDA;"><code><?= e($activeTrip['vehicle_number']) ?></code></div>
                    <div style="font-size:0.75rem; color:#64748B;">Current: <?= number_format((int)$activeTrip['current_vehicle_odometer']) ?> km</div>
                </div>
            </div>

            <!-- Actions based on Trip State -->
            <?php if ($activeTrip['status'] === 'assigned'): ?>
                <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/accept') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem; padding:0.85rem;">
                        ✓ Accept Trip Assignment
                    </button>
                </form>
            <?php elseif ($activeTrip['status'] === 'driver_accepted' || $activeTrip['status'] === 'en_route_pickup'): ?>
                <!-- Start Trip Form with Odometer -->
                <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/start') ?>" method="POST" onsubmit="return confirm('Confirm start of trip? Live GPS telemetry will begin transmitting.')">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="start_odometer">Current Odometer Reading (km) *</label>
                        <input type="number" id="start_odometer" name="start_odometer" class="form-control" required value="<?= (int)$activeTrip['current_vehicle_odometer'] ?>" min="<?= (int)$activeTrip['current_vehicle_odometer'] ?>" style="font-size:1.1rem; font-weight:bold;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="driver_notes">Pre-Trip Inspection Remarks</label>
                        <input type="text" id="driver_notes" name="driver_notes" class="form-control" placeholder="Vehicle clean, tire pressure checked">
                    </div>
                    <button type="submit" class="btn btn-navy btn-block" style="font-size:1rem; padding:0.85rem;">
                        🚀 Start Trip & Activate Telemetry
                    </button>
                </form>
            <?php elseif ($activeTrip['status'] === 'trip_started'): ?>
                <!-- Complete Trip Form with End Odometer -->
                <div style="background:#ECFDF5; border:1px solid #A7F3D0; padding:0.85rem; border-radius:8px; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem; font-size:0.88rem; color:#065F46;">
                    <span class="pulse-indicator"></span>
                    <span><strong>Trip in Progress:</strong> Telemetry is actively transmitting to dispatch.</span>
                </div>

                <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/complete') ?>" method="POST" onsubmit="return confirm('Complete this trip assignment and return vehicle to available fleet?')">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="end_odometer">Final Odometer Reading (km) *</label>
                        <input type="number" id="end_odometer" name="end_odometer" class="form-control" required value="<?= (int)($activeTrip['start_odometer'] ?? $activeTrip['current_vehicle_odometer']) + 20 ?>" min="<?= (int)($activeTrip['start_odometer'] ?? 0) ?>" style="font-size:1.1rem; font-weight:bold;">
                        <small style="color:#64748B;">Trip started at: <?= (int)$activeTrip['start_odometer'] ?> km</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="driver_notes">Closing Remarks / Toll Expenses</label>
                        <textarea id="driver_notes" name="driver_notes" class="form-control" rows="2" placeholder="Guest safely dropped off. Fastag toll logged."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block" style="font-size:1rem; padding:0.85rem;">
                        🏁 Complete Trip Duty
                    </button>
                </form>

                <script>
                // Activate GPS telemetry when trip is in progress
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.VoyageTelemetry) {
                        window.VoyageTelemetry.start('<?= url('/api/gps/update') ?>');
                    }
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- No Active Assignment Empty State -->
    <div class="card">
        <div class="card-body" style="text-align:center; padding:3rem 1.5rem;">
            <div style="font-size:3.5rem; margin-bottom:1rem;">☕</div>
            <h3 style="color:var(--navy-900); font-size:1.2rem; margin-bottom:0.5rem;">No Active Duty Assigned</h3>
            <p style="color:var(--text-secondary); font-size:0.92rem; max-width:360px; margin:0 auto;">
                You are currently on standby. Make sure your duty switch above is set to <strong>ON DUTY</strong> to receive upcoming dispatch deployments.
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Duties Summary -->
<div class="card">
    <div class="card-header">
        <span class="card-title" style="font-size:0.95rem;">Recently Completed Duties</span>
        <a href="<?= url('/driver/history') ?>" class="btn btn-outline btn-sm">Full History</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($recentCompleted)): ?>
            <div style="text-align:center; padding:1.5rem; color:#94A3B8; font-size:0.85rem;">No prior completed duties found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <?php foreach ($recentCompleted as $rc): ?>
                            <tr>
                                <td>
                                    <strong><?= e($rc['booking_code']) ?></strong>
                                    <div style="font-size:0.75rem; color:#64748B;"><?= e($rc['customer_name']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:0.82rem; color:#475569;"><?= e($rc['pickup_address']) ?> → <?= e($rc['drop_address']) ?></div>
                                </td>
                                <td><span class="badge badge-success">Completed</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
