<?php
/**
 * Trip Details and Trip Workflow Console — Dedicated Driver Portal
 */
?>

<!-- Header & Back Navigation -->
<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--vg-text-muted); margin-bottom: 0.35rem;">
            <a href="<?= url('/driver/trips') ?>" style="color: var(--vg-blue-600); text-decoration: none;">&larr; My Assigned Trips</a>
            <span>/</span>
            <span>Manifest <?= e($trip['booking_code']) ?></span>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-950); margin: 0; letter-spacing: -0.02em;">
            Trip Itinerary: <?= e($trip['booking_code']) ?>
        </h1>
    </div>

    <!-- Status Badge & Immediate Actions -->
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <?php
            $status = $trip['status'];
            $badge = match($status) {
                'assigned'         => 'badge-warning',
                'driver_accepted'  => 'badge-info',
                'trip_started'     => 'badge-primary',
                'completed'        => 'badge-success',
                'cancelled'        => 'badge-danger',
                default            => 'badge-secondary'
            };
        ?>
        <span class="badge <?= $badge ?>" style="font-size: 0.85rem; padding: 0.45rem 0.95rem;">
            <?= strtoupper(str_replace('_', ' ', $status)) ?>
        </span>

        <?php if ($status === 'assigned'): ?>
            <form action="<?= url('/driver/trips/' . $trip['id'] . '/accept') ?>" method="POST" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-check" style="margin-right: 5px;"></i> Accept Assignment
                </button>
            </form>
        <?php elseif ($status === 'driver_accepted'): ?>
            <button type="button" class="btn btn-primary" onclick="openModal('startTripModal')">
                <i class="fa-solid fa-play" style="margin-right: 5px;"></i> Start Trip
            </button>
        <?php elseif ($status === 'trip_started'): ?>
            <button type="button" class="btn btn-success" onclick="openModal('completeTripModal')">
                <i class="fa-solid fa-flag-checkered" style="margin-right: 5px;"></i> Mark Completed
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Main Details Grid -->
<div class="vg-split-2" style="margin-bottom: 1.5rem;">
    <!-- Route & Passenger Details -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm);">
        <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem;">
            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
                <i class="fa-solid fa-route" style="color: var(--vg-blue-500); margin-right: 6px;"></i> Route &amp; Passenger Manifest
            </h2>
        </div>
        <div class="card-body" style="padding: 1.35rem;">
            <!-- Pickup & Drop Timeline -->
            <div style="position: relative; padding-left: 2rem; margin-bottom: 1.25rem;">
                <div style="position: absolute; left: 8px; top: 12px; bottom: 12px; width: 2px; background: var(--vg-pearl-300);"></div>

                <!-- Point A -->
                <div style="position: relative; margin-bottom: 1.25rem;">
                    <div style="position: absolute; left: -2rem; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #10B981; border: 3px solid #FFF; box-shadow: 0 0 0 1px #10B981;"></div>
                    <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #10B981;">Pickup Location</div>
                    <div style="font-weight: 700; font-size: 1rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e($trip['pickup_address']) ?>
                    </div>
                </div>

                <!-- Point B -->
                <div style="position: relative;">
                    <div style="position: absolute; left: -2rem; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #EF4444; border: 3px solid #FFF; box-shadow: 0 0 0 1px #EF4444;"></div>
                    <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #EF4444;">Destination</div>
                    <div style="font-weight: 700; font-size: 1rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e($trip['drop_address']) ?>
                    </div>
                </div>
            </div>

            <!-- Schedule Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem; font-size: 0.85rem; margin-bottom: 1.25rem;">
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Scheduled Date</span>
                    <strong style="color: var(--vg-navy-950);"><?= format_date($trip['start_date']) ?></strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Scheduled Pickup Time</span>
                    <strong style="color: var(--vg-navy-950);"><?= e($trip['pickup_time'] ? date('h:i A', strtotime($trip['pickup_time'])) : 'As Agreed') ?></strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Passenger Count</span>
                    <strong style="color: var(--vg-navy-950);"><?= (int)($trip['passenger_count'] ?? 1) ?> Guests</strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Package / Tour</span>
                    <strong style="color: var(--vg-navy-950);"><?= e($trip['tour_title'] ?? 'Custom Chauffeur Service') ?></strong>
                </div>
            </div>

            <!-- Passenger Contact -->
            <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.5rem;">
                    Passenger Details
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-weight: 700; font-size: 1.05rem; color: var(--vg-navy-950);">
                            <?= e($trip['customer_name'] ?? 'VIP Guest') ?>
                        </div>
                    </div>
                    <?php if (!empty($trip['customer_phone'])): ?>
                        <a href="tel:<?= e($trip['customer_phone']) ?>" class="btn btn-outline btn-sm" style="font-weight: 700; color: var(--vg-blue-600);">
                            <i class="fa-solid fa-phone" style="margin-right: 4px;"></i> Call Passenger
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Special Instructions -->
            <?php if (!empty($trip['special_requests'])): ?>
                <div style="margin-top: 1rem; background: var(--vg-gold-50); border: 1px solid var(--vg-gold-400); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-gold-600); margin-bottom: 0.25rem;">
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i> Special Instructions
                    </div>
                    <div style="font-size: 0.85rem; color: var(--vg-text-primary);">
                        <?= nl2br(e($trip['special_requests'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assigned Vehicle & Odometer Readings -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm);">
        <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem;">
            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
                <i class="fa-solid fa-car-side" style="color: var(--vg-gold-500); margin-right: 6px;"></i> Assigned Fleet Vehicle
            </h2>
        </div>
        <div class="card-body" style="padding: 1.35rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                <div style="width: 56px; height: 56px; border-radius: var(--vg-radius-md); background: var(--vg-pearl-100); border: 1px solid var(--vg-pearl-200); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--vg-navy-900);">
                    <i class="fa-solid fa-van-shuttle"></i>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--vg-navy-950);">
                        <?= e($trip['vehicle_number']) ?>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                        <?= e($trip['make_model']) ?> • <?= e(ucfirst($trip['vehicle_type'] ?? 'Luxury')) ?>
                    </div>
                </div>
            </div>

            <!-- Vehicle Specs -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem; font-size: 0.85rem; margin-bottom: 1.25rem;">
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Fuel Type</span>
                    <strong><?= e(ucfirst($trip['fuel_type'] ?? 'Diesel')) ?></strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Seating Capacity</span>
                    <strong><?= (int)($trip['seating_capacity'] ?? 4) ?> Seats</strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Vehicle Odometer</span>
                    <strong><?= number_format((int)($trip['current_vehicle_odometer'] ?? 0)) ?> km</strong>
                </div>
                <div>
                    <span style="color: var(--vg-text-muted); display: block; font-size: 0.75rem;">Assigned By</span>
                    <strong><?= e($trip['assigner_name'] ?? 'Operations') ?></strong>
                </div>
            </div>

            <!-- Odometer & Trip Execution Log -->
            <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.75rem;">
                    Trip Telemetry &amp; Execution
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                    <div style="background: var(--vg-pearl-50); padding: 0.75rem; border-radius: var(--vg-radius-sm);">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">Start Odometer</span>
                        <strong style="font-size: 1.05rem; color: var(--vg-navy-950);">
                            <?= $trip['start_odometer'] ? number_format((int)$trip['start_odometer']) . ' km' : '—' ?>
                        </strong>
                        <div style="font-size: 0.72rem; color: var(--vg-text-muted); margin-top: 2px;">
                            <?= $trip['start_time'] ? format_datetime($trip['start_time']) : 'Not started' ?>
                        </div>
                    </div>

                    <div style="background: var(--vg-pearl-50); padding: 0.75rem; border-radius: var(--vg-radius-sm);">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">End Odometer</span>
                        <strong style="font-size: 1.05rem; color: var(--vg-navy-950);">
                            <?= $trip['end_odometer'] ? number_format((int)$trip['end_odometer']) . ' km' : '—' ?>
                        </strong>
                        <div style="font-size: 0.72rem; color: var(--vg-text-muted); margin-top: 2px;">
                            <?= $trip['end_time'] ? format_datetime($trip['end_time']) : 'In progress' ?>
                        </div>
                    </div>
                </div>

                <?php if ($trip['start_odometer'] && $trip['end_odometer']): ?>
                    <div style="margin-top: 0.75rem; background: var(--vg-success-bg); border: 1px solid var(--vg-success-border); border-radius: var(--vg-radius-sm); padding: 0.75rem; font-size: 0.85rem; color: var(--vg-success-text); display: flex; justify-content: space-between; align-items: center;">
                        <span>Total Journey Distance:</span>
                        <strong style="font-size: 1rem;"><?= number_format((int)$trip['end_odometer'] - (int)$trip['start_odometer']) ?> km</strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($trip['driver_notes'])): ?>
                    <div style="margin-top: 0.75rem; font-size: 0.82rem; color: var(--vg-text-secondary); background: var(--vg-pearl-100); padding: 0.65rem 0.85rem; border-radius: var(--vg-radius-sm);">
                        <strong>Chauffeur Notes:</strong> <?= nl2br(e($trip['driver_notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Status Transition Audit Trail -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--vg-navy-600); margin-right: 6px;"></i> Journey Status Audit Trail
        </h2>
    </div>
    <div class="card-body" style="padding: 1.35rem;">
        <?php if (empty($statusHistory)): ?>
            <div style="color: var(--vg-text-muted); font-size: 0.85rem; text-align: center; padding: 1rem;">
                Initial assignment recorded. Further status updates will be logged here.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <?php foreach ($statusHistory as $hist): ?>
                    <div style="display: flex; align-items: flex-start; gap: 1rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--vg-pearl-100);">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--vg-pearl-200); color: var(--vg-navy-900); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; margin-top: 2px;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: 700; font-size: 0.88rem; color: var(--vg-navy-950);">
                                    <?= strtoupper(str_replace('_', ' ', $hist['to_status'])) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    <?= format_datetime($hist['created_at']) ?>
                                </div>
                            </div>
                            <?php if (!empty($hist['notes'])): ?>
                                <div style="font-size: 0.8rem; color: var(--vg-text-muted); margin-top: 2px;">
                                    <?= e($hist['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Start Trip Modal -->
<div id="startTripModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.7); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg);">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-play" style="color:var(--vg-blue-400); margin-right:6px;"></i> Commence Journey</h3>
            <button type="button" onclick="closeModal('startTripModal')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/start') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="card-body" style="padding: 1.5rem;">
                <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1.25rem;">
                    Starting journey for <strong><?= e($trip['booking_code']) ?></strong>. Enter starting odometer reading to activate live GPS telemetry.
                </p>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="start_odo_val">Starting Odometer Reading (km) <span class="text-danger">*</span></label>
                    <input type="number" id="start_odo_val" name="start_odometer" class="form-control" value="<?= (int)($trip['current_vehicle_odometer'] ?? 0) ?>" min="<?= (int)($trip['current_vehicle_odometer'] ?? 0) ?>" required>
                    <span class="form-hint">Vehicle last recorded: <?= number_format((int)($trip['current_vehicle_odometer'] ?? 0)) ?> km</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="start_notes_val">Departure Notes (Optional)</label>
                    <textarea id="start_notes_val" name="driver_notes" class="form-control" rows="2" placeholder="e.g. Fuel full, vehicle sanitized, passenger onboard..."></textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('startTripModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play" style="margin-right:4px;"></i> Start Trip Now</button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Trip Modal -->
<div id="completeTripModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.7); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg);">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-flag-checkered" style="color:var(--vg-gold-400); margin-right:6px;"></i> Complete Trip</h3>
            <button type="button" onclick="closeModal('completeTripModal')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/complete') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="card-body" style="padding: 1.5rem;">
                <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1.25rem;">
                    Ending journey for <strong><?= e($trip['booking_code']) ?></strong>. Record the final vehicle odometer reading.
                </p>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="end_odo_val">Ending Odometer Reading (km) <span class="text-danger">*</span></label>
                    <input type="number" id="end_odo_val" name="end_odometer" class="form-control" value="<?= (int)($trip['start_odometer'] ?? $trip['current_vehicle_odometer'] ?? 0) ?>" min="<?= (int)($trip['start_odometer'] ?? 0) ?>" required>
                    <span class="form-hint">Trip started at: <?= number_format((int)($trip['start_odometer'] ?? 0)) ?> km</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="end_notes_val">Completion Notes &amp; Trip Feedback</label>
                    <textarea id="end_notes_val" name="driver_notes" class="form-control" rows="2" placeholder="e.g. Passenger dropped safely, toll paid, vehicle parked..."></textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('completeTripModal')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-flag-checkered" style="margin-right:4px;"></i> Mark Completed</button>
            </div>
        </form>
    </div>
</div>
