<?php
/**
 * Driver Command Dashboard View — VoyageGo Luxury Transport Suite
 */
?>

<!-- Chauffeur Welcome Banner -->
<div class="card" style="background: linear-gradient(135deg, var(--vg-navy-950) 0%, var(--vg-navy-900) 50%, var(--vg-navy-850) 100%); color: var(--vg-text-light); border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 1.5rem; position: relative; overflow: hidden; box-shadow: var(--vg-shadow-lg);">
    <div style="position: absolute; right: -20px; top: -20px; font-size: 8rem; opacity: 0.04; color: #FFF; pointer-events: none;">
        <i class="fa-solid fa-compass"></i>
    </div>
    <div class="card-body" style="padding: 1.5rem 1.75rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 54px; height: 54px; border-radius: var(--vg-radius-md); background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-700)); border: 2px solid var(--vg-gold-500); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #FFF; box-shadow: var(--vg-shadow-gold);">
                    <?= strtoupper(substr($user['name'] ?? 'D', 0, 1)) ?>
                </div>
                <div>
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--vg-gold-400); font-weight: 700;">
                        <?= e(__('driver.console_title')) ?>
                    </div>
                    <h1 style="font-size: 1.4rem; font-weight: 800; margin: 0.2rem 0; color: #FFF; letter-spacing: -0.02em;">
                        <?= e(__('alert.welcome_back', ['name' => $user['name'] ?? 'Chauffeur'])) ?>
                    </h1>
                    <div style="font-size: 0.82rem; color: #94A3B8; display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;">
                        <span><i class="fa-solid fa-id-card" style="margin-right: 4px; color: var(--vg-gold-400);"></i> License: <?= e($driver['license_number'] ?? 'Verified') ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-star" style="margin-right: 4px; color: #F59E0B;"></i> Rating: <?= number_format((float)($driver['rating'] ?? 5.0), 1) ?> / 5.0</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <?php
                    $duty = $driver['duty_status'] ?? 'off_duty';
                ?>
                <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin: 0;">
                    <?= csrf_field() ?>
                    <?php if ($duty === 'on_trip'): ?>
                        <button type="button" class="btn btn-primary btn-sm" disabled style="opacity: 0.9; cursor: not-allowed;">
                            <span class="pulse-indicator"></span> <?= e(__('status.on_trip')) ?>
                        </button>
                    <?php elseif ($duty === 'on_duty'): ?>
                        <button type="submit" class="btn btn-warning btn-sm" style="font-weight: 600;">
                            <i class="fa-solid fa-power-off" style="margin-right: 5px;"></i> <?= e(__('btn.go_off_duty')) ?>
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success btn-sm" style="font-weight: 600;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 5px;"></i> <?= e(__('btn.go_on_duty')) ?>
                        </button>
                    <?php endif; ?>
                </form>
                <a href="<?= url('/driver/trips') ?>" class="btn btn-outline btn-sm" style="color: #FFF; border-color: rgba(255,255,255,0.25);">
                    <i class="fa-solid fa-calendar-check" style="margin-right: 5px;"></i> <?= e(__('nav.my_trips')) ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 4 Statistics Overview Cards -->
<div class="grid-cols-4" style="gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- Active Assignment -->
    <div class="card" style="border-left: 4px solid var(--vg-blue-500); box-shadow: var(--vg-shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); letter-spacing: 0.05em;">
                        <?= e(__('dashboard.active_duty')) ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-950); margin-top: 0.2rem;">
                        <?= (int)($stats['active_trips'] ?? 0) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: var(--vg-radius-md); background: var(--vg-blue-50); color: var(--vg-blue-600); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 0.5rem;">
                <?= !empty($activeTrip) ? '1 trip executing now' : e(__('driver.no_active_trip')) ?>
            </div>
        </div>
    </div>

    <!-- Today's Assignments -->
    <div class="card" style="border-left: 4px solid var(--vg-gold-500); box-shadow: var(--vg-shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); letter-spacing: 0.05em;">
                        <?= e(__('dashboard.todays_trips')) ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-950); margin-top: 0.2rem;">
                        <?= (int)($stats['today_trips'] ?? 0) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: var(--vg-radius-md); background: var(--vg-gold-100); color: var(--vg-gold-600); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 0.5rem;">
                <?= e(__('table.date')) ?>: <?= date('d M Y') ?>
            </div>
        </div>
    </div>

    <!-- Completed Trips -->
    <div class="card" style="border-left: 4px solid var(--vg-success); box-shadow: var(--vg-shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); letter-spacing: 0.05em;">
                        <?= e(__('dashboard.completed_journeys')) ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-950); margin-top: 0.2rem;">
                        <?= (int)($stats['completed_trips'] ?? 0) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: var(--vg-radius-md); background: var(--vg-success-bg); color: var(--vg-success-text); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 0.5rem;">
                <?= e(__('dashboard.completed_trips')) ?>
            </div>
        </div>
    </div>

    <!-- Total Distance Travelled -->
    <div class="card" style="border-left: 4px solid var(--vg-navy-700); box-shadow: var(--vg-shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); letter-spacing: 0.05em;">
                        <?= e(__('dashboard.distance_logged')) ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-950); margin-top: 0.2rem;">
                        <?= number_format((int)($stats['total_km'] ?? 0)) ?> <span style="font-size: 0.9rem; font-weight: 600; color: var(--vg-text-muted);">km</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: var(--vg-radius-md); background: var(--vg-pearl-100); color: var(--vg-navy-900); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--vg-text-muted); margin-top: 0.5rem;">
                <?= e(__('driver.vehicle_odometer')) ?>
            </div>
        </div>
    </div>
</div>

<!-- Active Trip Card / Highlight -->
<?php if (!empty($activeTrip)): ?>
    <div class="card" style="border: 2px solid var(--vg-blue-500); box-shadow: 0 8px 24px rgba(2, 132, 199, 0.12); margin-bottom: 1.5rem; overflow: hidden;">
        <div class="card-header" style="background: linear-gradient(135deg, var(--vg-navy-900), var(--vg-navy-850)); color: #FFF; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="pulse-indicator"></span>
                <span style="font-weight: 800; font-size: 1.05rem; letter-spacing: -0.01em;">CURRENT ACTIVE TRIP — <?= e($activeTrip['booking_code']) ?></span>
                <span class="badge badge-warning" style="font-size: 0.75rem;"><?= strtoupper(str_replace('_', ' ', $activeTrip['status'])) ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <a href="<?= url('/driver/trips/' . $activeTrip['id']) ?>" class="btn btn-outline btn-sm" style="color: #FFF; border-color: rgba(255,255,255,0.3); font-size: 0.8rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 4px;"></i> View Full Details
                </a>
            </div>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            <div class="vg-split-2" style="margin-bottom: 1.25rem;">
                <!-- Route Itinerary -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1.15rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.75rem;">
                        <i class="fa-solid fa-map-location-dot" style="color: var(--vg-blue-500); margin-right: 4px;"></i> Route & Schedule
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0; margin-top: 2px;">A</div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Pickup Location</div>
                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--vg-navy-950);"><?= e($activeTrip['pickup_address']) ?></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0; margin-top: 2px;">B</div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Drop Destination</div>
                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--vg-navy-950);"><?= e($activeTrip['drop_address']) ?></div>
                        </div>
                    </div>
                    <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem; display: flex; gap: 1.5rem; font-size: 0.85rem;">
                        <div>
                            <span style="color: var(--vg-text-muted);">Date:</span> <strong><?= format_date($activeTrip['start_date']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--vg-text-muted);">Time:</span> <strong><?= e($activeTrip['pickup_time'] ? date('h:i A', strtotime($activeTrip['pickup_time'])) : 'Scheduled') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Customer & Vehicle Info -->
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1.15rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.75rem;">
                        <i class="fa-solid fa-user-tie" style="color: var(--vg-gold-500); margin-right: 4px;"></i> Customer & Fleet Assigned
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Passenger Name</div>
                            <div style="font-weight: 700; color: var(--vg-navy-950); font-size: 0.95rem;"><?= e($activeTrip['customer_name'] ?? 'VIP Guest') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Contact Phone</div>
                            <?php if (!empty($activeTrip['customer_phone'])): ?>
                                <a href="tel:<?= e($activeTrip['customer_phone']) ?>" style="font-weight: 700; color: var(--vg-blue-600); text-decoration: none; font-size: 0.95rem;">
                                    <i class="fa-solid fa-phone" style="font-size: 0.8rem; margin-right: 3px;"></i> <?= e($activeTrip['customer_phone']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: var(--vg-text-muted);">Via Concierge</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Assigned Vehicle</div>
                            <div style="font-weight: 700; color: var(--vg-navy-900);"><?= e($activeTrip['vehicle_number']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);"><?= e($activeTrip['make_model']) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Vehicle Odometer</div>
                            <div style="font-weight: 700; color: var(--vg-navy-900);"><?= number_format((int)($activeTrip['current_vehicle_odometer'] ?? 0)) ?> km</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip Action Trigger Buttons -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--vg-pearl-200); padding-top: 1rem;">
                <?php if ($activeTrip['status'] === 'assigned'): ?>
                    <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/accept') ?>" method="POST" style="margin:0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success" style="padding: 0.6rem 1.25rem;">
                            <i class="fa-solid fa-check" style="margin-right: 6px;"></i> Accept Assignment
                        </button>
                    </form>
                <?php elseif ($activeTrip['status'] === 'driver_accepted'): ?>
                    <button type="button" class="btn btn-primary" onclick="openModal('startTripModal_<?= $activeTrip['id'] ?>')" style="padding: 0.6rem 1.25rem;">
                        <i class="fa-solid fa-play" style="margin-right: 6px;"></i> Start Trip (Record Odometer)
                    </button>
                <?php elseif ($activeTrip['status'] === 'trip_started'): ?>
                    <button type="button" class="btn btn-success" onclick="openModal('completeTripModal_<?= $activeTrip['id'] ?>')" style="padding: 0.6rem 1.25rem;">
                        <i class="fa-solid fa-flag-checkered" style="margin-right: 6px;"></i> Complete Trip (Final Odometer)
                    </button>
                <?php endif; ?>
                <a href="<?= url('/driver/trips/' . $activeTrip['id']) ?>" class="btn btn-outline" style="padding: 0.6rem 1.25rem;">
                    Full Itinerary Details
                </a>
            </div>
        </div>
    </div>

    <!-- Start Trip Modal -->
    <div id="startTripModal_<?= $activeTrip['id'] ?>" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.7); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
        <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg);">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-play" style="color:var(--vg-blue-400); margin-right:6px;"></i> Commence Journey</h3>
                <button type="button" onclick="closeModal('startTripModal_<?= $activeTrip['id'] ?>')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
            </div>
            <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/start') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body" style="padding: 1.5rem;">
                    <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1.25rem;">
                        Starting journey for <strong><?= e($activeTrip['booking_code']) ?></strong>. Enter starting odometer reading to activate live GPS telemetry.
                    </p>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="start_odo_<?= $activeTrip['id'] ?>">Starting Odometer Reading (km) <span class="text-danger">*</span></label>
                        <input type="number" id="start_odo_<?= $activeTrip['id'] ?>" name="start_odometer" class="form-control" value="<?= (int)($activeTrip['current_vehicle_odometer'] ?? 0) ?>" min="<?= (int)($activeTrip['current_vehicle_odometer'] ?? 0) ?>" required>
                        <span class="form-hint">Vehicle last recorded: <?= number_format((int)($activeTrip['current_vehicle_odometer'] ?? 0)) ?> km</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="notes_start_<?= $activeTrip['id'] ?>">Departure Notes (Optional)</label>
                        <textarea id="notes_start_<?= $activeTrip['id'] ?>" name="driver_notes" class="form-control" rows="2" placeholder="e.g. Fuel full, vehicle sanitized, passenger onboard..."></textarea>
                    </div>
                </div>
                <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('startTripModal_<?= $activeTrip['id'] ?>')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play" style="margin-right:4px;"></i> Start Trip Now</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Trip Modal -->
    <div id="completeTripModal_<?= $activeTrip['id'] ?>" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.7); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
        <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg);">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-flag-checkered" style="color:var(--vg-gold-400); margin-right:6px;"></i> Complete Trip</h3>
                <button type="button" onclick="closeModal('completeTripModal_<?= $activeTrip['id'] ?>')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
            </div>
            <form action="<?= url('/driver/trips/' . $activeTrip['id'] . '/complete') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body" style="padding: 1.5rem;">
                    <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1.25rem;">
                        Ending journey for <strong><?= e($activeTrip['booking_code']) ?></strong>. Record the final vehicle odometer reading.
                    </p>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="end_odo_<?= $activeTrip['id'] ?>">Ending Odometer Reading (km) <span class="text-danger">*</span></label>
                        <input type="number" id="end_odo_<?= $activeTrip['id'] ?>" name="end_odometer" class="form-control" value="<?= (int)($activeTrip['start_odometer'] ?? $activeTrip['current_vehicle_odometer'] ?? 0) ?>" min="<?= (int)($activeTrip['start_odometer'] ?? 0) ?>" required>
                        <span class="form-hint">Trip started at: <?= number_format((int)($activeTrip['start_odometer'] ?? 0)) ?> km</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="notes_end_<?= $activeTrip['id'] ?>">Completion Notes &amp; Trip Feedback</label>
                        <textarea id="notes_end_<?= $activeTrip['id'] ?>" name="driver_notes" class="form-control" rows="2" placeholder="e.g. Passenger dropped safely, toll paid, vehicle parked..."></textarea>
                    </div>
                </div>
                <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('completeTripModal_<?= $activeTrip['id'] ?>')">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-flag-checkered" style="margin-right:4px;"></i> Mark Completed</button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Professional Empty State When No Active Trips -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem; border: 1px dashed var(--vg-pearl-300);">
        <div class="card-body" style="padding: 2.5rem 1.5rem; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--vg-pearl-100); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--vg-navy-950); margin: 0 0 0.35rem;">
                No trips assigned yet
            </h2>
            <p style="font-size: 0.9rem; color: var(--vg-text-muted); max-width: 440px; margin: 0 auto 1.25rem;">
                Assigned journeys and route manifests will appear here once dispatched by office operations. Please keep your duty status set to <strong style="color:var(--vg-success);">On Duty</strong>.
            </p>
            <div style="display: flex; justify-content: center; gap: 0.75rem;">
                <a href="<?= url('/driver/dashboard') ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrows-rotate" style="margin-right: 4px;"></i> Refresh Console
                </a>
                <a href="<?= url('/driver/history') ?>" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-clock-rotate-left" style="margin-right: 4px;"></i> View Past Trip History
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 2-Column Split: Upcoming Assignments & Assigned Vehicle -->
<div class="vg-split-2">
    <!-- Assigned Trips Summary -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm);">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; padding: 1.15rem 1.35rem;">
            <div style="font-weight: 700; font-size: 1.05rem; color: var(--vg-navy-950);">
                <i class="fa-solid fa-clipboard-list" style="color: var(--vg-blue-500); margin-right: 6px;"></i> <?= e(__('driver.assigned_manifests')) ?>
            </div>
            <a href="<?= url('/driver/trips') ?>" style="font-size: 0.8rem; font-weight: 600; color: var(--vg-blue-600); text-decoration: none;">
                <?= e(__('driver.view_all_trips')) ?> &rarr;
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($assignedTrips)): ?>
                <div style="padding: 2rem 1.5rem; text-align: center; color: var(--vg-text-muted); font-size: 0.85rem;">
                    <?= e(__('driver.no_pending_schedule')) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" style="margin: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th><?= e(__('table.booking_ref')) ?></th>
                                <th><?= e(__('table.tour')) ?></th>
                                <th><?= e(__('table.date')) ?></th>
                                <th><?= e(__('table.status')) ?></th>
                                <th style="text-align: right;"><?= e(__('table.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedTrips as $t): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/driver/trips/' . $t['id']) ?>" style="font-weight: 700; color: var(--vg-blue-600); text-decoration: none;">
                                            <?= e($t['booking_code']) ?>
                                        </a>
                                        <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= e($t['dispatch_code']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($t['pickup_address']) ?> &rarr; <?= e($t['drop_address']) ?>">
                                            <?= e($t['pickup_address']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--vg-text-muted); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            &rarr; <?= e($t['drop_address']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.82rem;"><?= format_date($t['start_date']) ?></div>
                                        <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= e($t['pickup_time'] ? date('h:i A', strtotime($t['pickup_time'])) : '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 0.7rem;">
                                            <?= strtoupper(str_replace('_', ' ', $t['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= url('/driver/trips/' . $t['id']) ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                            <?= e(__('btn.view')) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assigned Vehicle & Telemetry Status -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm);">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; padding: 1.15rem 1.35rem;">
            <div style="font-weight: 700; font-size: 1.05rem; color: var(--vg-navy-950);">
                <i class="fa-solid fa-car" style="color: var(--vg-gold-500); margin-right: 6px;"></i> <?= e(__('driver.assigned_vehicle')) ?>
            </div>
            <a href="<?= url('/driver/vehicle') ?>" style="font-size: 0.8rem; font-weight: 600; color: var(--vg-blue-600); text-decoration: none;">
                <?= e(__('driver.vehicle_specs')) ?> &rarr;
            </a>
        </div>
        <div class="card-body" style="padding: 1.35rem;">
            <?php if (!empty($currentVehicle)): ?>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 52px; height: 52px; border-radius: var(--vg-radius-md); background: var(--vg-pearl-100); border: 1px solid var(--vg-pearl-200); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--vg-navy-900);">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.15rem; font-weight: 800; color: var(--vg-navy-950); letter-spacing: -0.01em;">
                            <?= e($currentVehicle['vehicle_number']) ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                            <?= e($currentVehicle['make_model']) ?> • <?= e(ucfirst($currentVehicle['vehicle_type'] ?? 'Luxury')) ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem; font-size: 0.82rem; margin-bottom: 1rem;">
                    <div>
                        <span style="color: var(--vg-text-muted);">Fuel:</span> <strong><?= e(ucfirst($currentVehicle['fuel_type'] ?? 'Diesel')) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--vg-text-muted);"><?= e(__('table.capacity')) ?>:</span> <strong><?= (int)($currentVehicle['seating_capacity'] ?? 4) ?> Guests</strong>
                    </div>
                    <div>
                        <span style="color: var(--vg-text-muted);"><?= e(__('driver.vehicle_odometer')) ?>:</span> <strong><?= number_format((int)($currentVehicle['odometer_km'] ?? 0)) ?> km</strong>
                    </div>
                    <div>
                        <span style="color: var(--vg-text-muted);"><?= e(__('table.status')) ?>:</span> <span class="badge badge-success" style="font-size:0.68rem;"><?= strtoupper($currentVehicle['status'] ?? 'Available') ?></span>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <a href="<?= url('/driver/location') ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">
                        <i class="fa-solid fa-location-crosshairs" style="margin-right: 4px;"></i> <?= e(__('driver.location_sync')) ?>
                    </a>
                    <a href="<?= url('/driver/vehicle') ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">
                        <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> <?= e(__('driver.fleet_profile')) ?>
                    </a>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 1.5rem 1rem; color: var(--vg-text-muted);">
                    <i class="fa-solid fa-car-side" style="font-size: 2.2rem; color: var(--vg-pearl-300); margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 700; color: var(--vg-navy-950); margin-bottom: 0.25rem;"><?= e(__('driver.no_fleet_assigned')) ?></div>
                    <div style="font-size: 0.82rem;"><?= e(__('driver.vehicle_linked_auto')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
