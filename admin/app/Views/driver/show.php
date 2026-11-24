<?php
/**
 * Trip Details and Trip Workflow Console — Dedicated Driver Portal
 */
$status = $trip['status'] ?? 'assigned';
$hasActiveActions = in_array($status, ['assigned', 'driver_accepted', 'trip_started', 'unable_to_accept'], true);
?>

<?php if ($hasActiveActions): ?>
<!-- Immediate Trip Actions (when actionable) -->
<div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
    <?php if ($status === 'assigned'): ?>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/accept') ?>" method="POST" style="margin:0;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check" style="margin-right: 5px;"></i> Accept Assignment
            </button>
        </form>
        <button type="button" class="btn btn-outline" style="color: #EF4444; border-color: #EF4444;" onclick="openModal('declineTripModal')">
            <i class="fa-solid fa-xmark" style="margin-right: 5px;"></i> Unable to Accept
        </button>
    <?php elseif ($status === 'unable_to_accept'): ?>
        <span class="badge badge-danger" style="font-size: 0.85rem; padding: 0.45rem 0.95rem;">
            <i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Assignment Declined
        </span>
    <?php elseif ($status === 'driver_accepted'): ?>
        <button type="button" class="btn btn-primary" onclick="openStartTripModal()">
            <i class="fa-solid fa-play" style="margin-right: 5px;"></i> Start Trip &amp; Inspection
        </button>
    <?php elseif ($status === 'trip_started'): ?>
        <button type="button" class="btn btn-success" onclick="openCompleteTripModal()">
            <i class="fa-solid fa-flag-checkered" style="margin-right: 5px;"></i> Complete Trip
        </button>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($status === 'trip_started'): ?>
<!-- Active Mission Control Banner -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #FFF; border: 1px solid #334155; box-shadow: var(--vg-shadow-md);">
    <div class="card-body" style="padding: 1.25rem 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); border: 2px solid #10B981; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #10B981; position: relative;">
                    <i class="fa-solid fa-satellite-dish"></i>
                    <span style="position: absolute; top: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; background: #10B981; border: 2px solid #0F172A;"></span>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; background: #10B981; color: #FFF; padding: 2px 8px; border-radius: 4px; letter-spacing: 0.05em;">
                            Mission Live
                        </span>
                        <span style="font-size: 0.85rem; color: #94A3B8;">GPS Telemetry Active</span>
                    </div>
                    <div style="font-size: 1.15rem; font-weight: 800; margin-top: 2px;">
                        Trip commenced at <?= !empty($trip['start_time']) ? date('h:i A', strtotime($trip['start_time'])) : 'Just now' ?> &bull; Start: <?= number_format((int)($trip['start_odometer'] ?? 0)) ?> KM
                    </div>
                </div>
            </div>

            <!-- Elapsed Journey Timer & Action -->
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="text-align: right;">
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: #94A3B8; font-weight: 700;">Journey Time Elapsed</div>
                    <div id="liveTripTimer" style="font-size: 1.35rem; font-weight: 800; font-family: ui-monospace, monospace; color: #38BDF8;">
                        00:00:00
                    </div>
                </div>
                <button type="button" class="btn btn-success" onclick="openCompleteTripModal()" style="font-weight: 700; padding: 0.6rem 1.25rem; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);">
                    <i class="fa-solid fa-flag-checkered" style="margin-right: 6px;"></i> Complete Trip
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const startTimeStr = "<?= $trip['start_time'] ?? '' ?>";
    if (!startTimeStr) return;
    const startTs = new Date(startTimeStr.replace(/-/g, '/')).getTime();
    function updateTimer() {
        const now = new Date().getTime();
        const diffMs = Math.max(0, now - startTs);
        const hrs = Math.floor(diffMs / 3600000);
        const mins = Math.floor((diffMs % 3600000) / 60000);
        const secs = Math.floor((diffMs % 60000) / 1000);
        const pad = (n) => String(n).padStart(2, '0');
        const timerEl = document.getElementById('liveTripTimer');
        if (timerEl) {
            timerEl.textContent = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
        }
    }
    updateTimer();
    setInterval(updateTimer, 1000);
})();
</script>
<?php endif; ?>

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
                    <strong style="color: var(--vg-navy-950);"><?= e($trip['tour_title'] ?? 'Custom Driver Service') ?></strong>
                </div>
                <div style="grid-column: span 2; border-top: 1px dashed var(--vg-pearl-200); padding-top: 0.5rem; margin-top: 0.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--vg-text-muted); font-size: 0.8rem; font-weight: 600;">Driver Trip Compensation:</span>
                    <strong style="color: #176B5B; font-size: 1.05rem; font-weight: 800;"><?= ($trip['driver_trip_amount'] !== null && $trip['driver_trip_amount'] !== '') ? format_inr((float)$trip['driver_trip_amount']) : 'Amount Not Specified' ?></strong>
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
                        <strong>Driver Notes:</strong> <?= nl2br(e($trip['driver_notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
    $dtAmount = $trip['driver_trip_amount'] !== null ? (float)$trip['driver_trip_amount'] : null;
    $dtPaid = (float)($trip['driver_paid_amount'] ?? 0);
    $dtRemaining = ($dtAmount !== null) ? round(max(0.0, $dtAmount - $dtPaid), 2) : null;
    $dtStatus = $trip['driver_payment_status'] ?? 'not_due';
?>

<!-- Driver Compensation & Settlement Card (Section 21) -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
            <i class="fa-solid fa-wallet" style="color: var(--vg-blue-500); margin-right: 6px;"></i> Driver Trip Compensation &amp; Settlement
        </h2>
        <div>
            <?= render_status_badge($dtStatus, 'driver_payment') ?>
        </div>
    </div>
    <div class="card-body" style="padding: 1.35rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 1rem; margin-bottom: 1rem;">
            <div>
                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase;">Driver Trip Amount</span>
                <strong style="color: var(--vg-navy-950); font-size: 1.15rem;">
                    <?= $dtAmount !== null ? format_inr($dtAmount) : '<span style="font-size: 0.88rem; color: #64748B;">Amount Not Specified</span>' ?>
                </strong>
            </div>
            <div>
                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase;">Total Paid Amount</span>
                <strong style="color: #166534; font-size: 1.15rem;">
                    <?= format_inr($dtPaid) ?>
                </strong>
            </div>
            <div>
                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase;">Remaining Due</span>
                <strong style="color: <?= ($dtRemaining !== null && $dtRemaining > 0) ? '#C78A35' : '#10B981' ?>; font-size: 1.15rem;">
                    <?= $dtRemaining !== null ? format_inr($dtRemaining) : '—' ?>
                </strong>
            </div>
            <div>
                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase;">Payment Status</span>
                <div style="margin-top: 4px;">
                    <?= render_status_badge($dtStatus, 'driver_payment') ?>
                </div>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
            <div style="margin-top: 1rem; border-top: 1px solid var(--vg-pearl-200); padding-top: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-navy-900); margin-bottom: 0.65rem;">
                    <i class="fa-solid fa-receipt" style="color: var(--vg-blue-500); margin-right: 4px;"></i> Payment Vouchers (<?= count($payments) ?>)
                </div>
                <div class="modern-table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th class="col-priority-1"><?= e(__('table.voucher_ref', 'Voucher Ref')) ?></th>
                                <th class="col-priority-2"><?= e(__('table.date', 'Date')) ?></th>
                                <th class="col-priority-2"><?= e(__('table.method', 'Method')) ?></th>
                                <th class="col-priority-3 col-optional">Transaction / UTR #</th>
                                <th class="col-priority-1 text-end" style="text-align: right;"><?= e(__('table.paid_amount', 'Paid Amount')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pv): ?>
                                <tr>
                                    <td class="col-priority-1" style="font-weight: 700; color: var(--vg-navy-950);"><?= e($pv['payment_reference']) ?></td>
                                    <td class="col-priority-2" style="color: #475569;"><?= date('d M Y', strtotime($pv['payment_date'])) ?></td>
                                    <td class="col-priority-2" style="text-transform: capitalize; color: #475569;"><?= e(str_replace('_', ' ', $pv['payment_method'])) ?></td>
                                    <td class="col-priority-3 col-optional" style="font-family: monospace; color: #64748B;"><?= e($pv['transaction_ref'] ?: '—') ?></td>
                                    <td class="col-priority-1 text-end" style="text-align: right; font-weight: 700; color: #166534;"><?= format_inr($pv['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Vehicle Inspection Evidence Gallery -->
<?php if (!empty($evidence['all'])): ?>
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
            <i class="fa-solid fa-camera-retro" style="color: var(--vg-blue-500); margin-right: 6px;"></i> Vehicle Inspection Evidence (<?= count($evidence['all']) ?> Photos)
        </h2>
        <span style="font-size: 0.78rem; color: #10B981; font-weight: 700; background: #ECFDF5; padding: 3px 8px; border-radius: 4px; border: 1px solid #A7F3D0;">
            <i class="fa-solid fa-shield-check" style="margin-right: 4px;"></i> Verified Audit
        </span>
    </div>
    <div class="card-body" style="padding: 1.35rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($evidence['all'] as $item): ?>
                <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; background: #F8FAFC; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="position: relative;">
                        <a href="<?= e($item['url']) ?>" target="_blank" onclick="openDriverLightbox('<?= e($item['url']) ?>', '<?= e($item['label']) ?>'); return false;">
                            <img src="<?= e($item['url']) ?>" alt="<?= e($item['label']) ?>" style="width: 100%; height: 140px; object-fit: cover; display: block;">
                            <div style="position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,0.7); color: #FFF; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> View
                            </div>
                        </a>
                    </div>
                    <div style="padding: 0.65rem 0.85rem;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: #0F172A; margin-bottom: 2px;">
                            <?= e($item['label']) ?>
                        </div>
                        <div style="font-size: 0.72rem; color: #64748B;">
                            <?= date('d M, h:i A', strtotime($item['uploaded_at'])) ?>
                            <?php if (!empty($item['odometer_reading'])): ?>
                                &bull; <strong><?= number_format((int)$item['odometer_reading']) ?> km</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

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

<!-- Start Trip Modal (Pre-Trip Inspection with Photos & GPS) -->
<div id="startTripModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.75); z-index:9999; align-items:center; justify-content:center; padding:1rem; overflow-y:auto;">
    <div class="card" style="max-width: 520px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg); max-height: 92vh; display: flex; flex-direction: column;">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF; flex-shrink: 0;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-clipboard-check" style="color:#10B981; margin-right:6px;"></i> Pre-Trip Inspection &amp; Start</h3>
            <button type="button" onclick="closeModal('startTripModal')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/start') ?>" method="POST" enctype="multipart/form-data" id="startTripForm" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
            <?= csrf_field() ?>
            <input type="hidden" name="latitude" id="start_lat" value="">
            <input type="hidden" name="longitude" id="start_lng" value="">

            <div class="card-body" style="padding: 1.25rem; overflow-y: auto;">
                <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1rem;">
                    Please perform vehicle inspection and enter starting meter reading to begin journey for <strong><?= e($trip['booking_code']) ?></strong>.
                </p>

                <!-- GPS Location Permission Status Indicator -->
                <div id="startGpsStatusBox" style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 1rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-location-crosshairs" style="color: #2563EB;"></i>
                        <span id="startGpsStatusText" style="color: #475569;">Detecting GPS location...</span>
                    </div>
                    <button type="button" onclick="acquireStartGps()" style="font-size: 0.72rem; padding: 2px 6px; background: #FFF; border: 1px solid #CBD5E1; border-radius: 4px; cursor: pointer;">
                        <i class="fa-solid fa-arrows-rotate"></i> Refresh
                    </button>
                </div>

                <!-- Starting Odometer Reading -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="start_odo_val" style="font-weight: 700;">Starting Odometer Reading (km) <span class="text-danger">*</span></label>
                    <input type="number" id="start_odo_val" name="start_odometer" class="form-control" value="<?= (int)($trip['current_vehicle_odometer'] ?? 0) ?>" min="<?= (int)($trip['current_vehicle_odometer'] ?? 0) ?>" required style="font-size: 1.1rem; font-weight: 700;">
                    <span class="form-hint" style="color: #64748B;">Vehicle last recorded reading: <strong><?= number_format((int)($trip['current_vehicle_odometer'] ?? 0)) ?> km</strong> (must be &ge; this value)</span>
                </div>

                <!-- Inspection Photos: Meter & Vehicle Exterior -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #0F172A; margin-bottom: 0.65rem; text-transform: uppercase;">
                        Inspection Photos (Camera / File)
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <!-- Odometer Photo -->
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">
                                Meter Reading Photo
                            </label>
                            <input type="file" name="start_odometer_photo" id="start_odo_file" accept="image/*" capture="environment" onchange="previewImg(this, 'start_odo_prev', 'start_odo_base64')" style="display: none;">
                            <input type="hidden" name="start_odometer_base64" id="start_odo_base64" value="">
                            <button type="button" onclick="document.getElementById('start_odo_file').click()" class="btn btn-outline btn-sm" style="width: 100%; border: 1px dashed #94A3B8; padding: 0.5rem 0.25rem; font-size: 0.78rem;">
                                <i class="fa-solid fa-camera" style="margin-right: 4px;"></i> Snap Meter
                            </button>
                            <img id="start_odo_prev" src="" alt="Meter Preview" style="display: none; width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-top: 4px; border: 1px solid #CBD5E1;">
                        </div>

                        <!-- Vehicle Photo -->
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">
                                Vehicle Condition Photo
                            </label>
                            <input type="file" name="start_vehicle_photo" id="start_veh_file" accept="image/*" capture="environment" onchange="previewImg(this, 'start_veh_prev', 'start_veh_base64')" style="display: none;">
                            <input type="hidden" name="start_vehicle_base64" id="start_veh_base64" value="">
                            <button type="button" onclick="document.getElementById('start_veh_file').click()" class="btn btn-outline btn-sm" style="width: 100%; border: 1px dashed #94A3B8; padding: 0.5rem 0.25rem; font-size: 0.78rem;">
                                <i class="fa-solid fa-car" style="margin-right: 4px;"></i> Snap Vehicle
                            </button>
                            <img id="start_veh_prev" src="" alt="Vehicle Preview" style="display: none; width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-top: 4px; border: 1px solid #CBD5E1;">
                        </div>
                    </div>
                </div>

                <!-- Departure Notes -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="start_notes_val">Departure Notes (Optional)</label>
                    <textarea id="start_notes_val" name="driver_notes" class="form-control" rows="2" placeholder="<?= e(__('placeholder.e_g_fuel_tank_full_vehicle_ins', 'e.g. Fuel tank full, vehicle inspected & sanitized, passenger onboard...')) ?>"></textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.25rem; flex-shrink: 0;">
                <button type="button" class="btn btn-outline" onclick="closeModal('startTripModal')"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="btnConfirmStartTrip">
                    <i class="fa-solid fa-play" style="margin-right:4px;"></i> Confirm &amp; Start Trip
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Trip Modal (Ending Inspection, End Meter, Ending Photos) -->
<div id="completeTripModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.75); z-index:9999; align-items:center; justify-content:center; padding:1rem; overflow-y:auto;">
    <div class="card" style="max-width: 520px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg); max-height: 92vh; display: flex; flex-direction: column;">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:var(--vg-navy-950); color:#FFF; flex-shrink: 0;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-flag-checkered" style="color:#F59E0B; margin-right:6px;"></i> Complete Trip &amp; Arrival Inspection</h3>
            <button type="button" onclick="closeModal('completeTripModal')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/complete') ?>" method="POST" enctype="multipart/form-data" id="completeTripForm" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
            <?= csrf_field() ?>
            <input type="hidden" name="latitude" id="end_lat" value="">
            <input type="hidden" name="longitude" id="end_lng" value="">

            <div class="card-body" style="padding: 1.25rem; overflow-y: auto;">
                <p style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-bottom: 1rem;">
                    Ending journey for <strong><?= e($trip['booking_code']) ?></strong>. Record the final vehicle odometer reading.
                </p>

                <!-- GPS Location Status -->
                <div id="endGpsStatusBox" style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 1rem; font-size: 0.8rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-location-crosshairs" style="color: #2563EB;"></i>
                        <span id="endGpsStatusText" style="color: #475569;">Detecting destination GPS fix...</span>
                    </div>
                    <button type="button" onclick="acquireEndGps()" style="font-size: 0.72rem; padding: 2px 6px; background: #FFF; border: 1px solid #CBD5E1; border-radius: 4px; cursor: pointer;">
                        <i class="fa-solid fa-arrows-rotate"></i> Refresh
                    </button>
                </div>

                <!-- Ending Odometer Reading -->
                <?php $startReading = (int)($trip['start_odometer'] ?? $trip['current_vehicle_odometer'] ?? 0); ?>
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label class="form-label" for="end_odo_val" style="font-weight: 700;">Final Ending Odometer Reading (km) <span class="text-danger">*</span></label>
                    <input type="number" id="end_odo_val" name="end_odometer" class="form-control" value="<?= $startReading ?>" min="<?= $startReading ?>" required oninput="calculateTripDistance(this.value, <?= $startReading ?>)" style="font-size: 1.1rem; font-weight: 700;">
                    <span class="form-hint" style="color: #64748B;">Trip started at: <strong><?= number_format($startReading) ?> km</strong></span>
                </div>

                <!-- Live Computed Distance Badge -->
                <div id="computedDistanceBox" style="background: #DEF7EC; border: 1px solid #BCF0DA; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8rem; color: #03543F; font-weight: 600;">Computed Distance:</span>
                    <strong id="computedDistanceValue" style="font-size: 0.95rem; color: #03543F;">0 KM driven</strong>
                </div>

                <!-- Arrival Inspection Photos -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #0F172A; margin-bottom: 0.65rem; text-transform: uppercase;">
                        Arrival Inspection Photos
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <!-- End Odometer Photo -->
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">
                                Final Meter Photo
                            </label>
                            <input type="file" name="end_odometer_photo" id="end_odo_file" accept="image/*" capture="environment" onchange="previewImg(this, 'end_odo_prev', 'end_odo_base64')" style="display: none;">
                            <input type="hidden" name="end_odometer_base64" id="end_odo_base64" value="">
                            <button type="button" onclick="document.getElementById('end_odo_file').click()" class="btn btn-outline btn-sm" style="width: 100%; border: 1px dashed #94A3B8; padding: 0.5rem 0.25rem; font-size: 0.78rem;">
                                <i class="fa-solid fa-camera" style="margin-right: 4px;"></i> Snap Final Meter
                            </button>
                            <img id="end_odo_prev" src="" alt="End Meter Preview" style="display: none; width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-top: 4px; border: 1px solid #CBD5E1;">
                        </div>

                        <!-- End Vehicle Photo -->
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">
                                Returned Vehicle Photo
                            </label>
                            <input type="file" name="end_vehicle_photo" id="end_veh_file" accept="image/*" capture="environment" onchange="previewImg(this, 'end_veh_prev', 'end_veh_base64')" style="display: none;">
                            <input type="hidden" name="end_vehicle_base64" id="end_veh_base64" value="">
                            <button type="button" onclick="document.getElementById('end_veh_file').click()" class="btn btn-outline btn-sm" style="width: 100%; border: 1px dashed #94A3B8; padding: 0.5rem 0.25rem; font-size: 0.78rem;">
                                <i class="fa-solid fa-car" style="margin-right: 4px;"></i> Snap Returned Car
                            </button>
                            <img id="end_veh_prev" src="" alt="End Vehicle Preview" style="display: none; width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-top: 4px; border: 1px solid #CBD5E1;">
                        </div>
                    </div>
                </div>

                <!-- Completion Notes -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="end_notes_val">Completion Notes &amp; Trip Feedback</label>
                    <textarea id="end_notes_val" name="driver_notes" class="form-control" rows="2" placeholder="<?= e(__('placeholder.e_g_passenger_dropped_safely_t', 'e.g. Passenger dropped safely, toll paid, vehicle parked in yard...')) ?>"></textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.25rem; flex-shrink: 0;">
                <button type="button" class="btn btn-outline" onclick="closeModal('completeTripModal')"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-success" id="btnConfirmCompleteTrip">
                    <i class="fa-solid fa-flag-checkered" style="margin-right:4px;"></i> Complete Trip Now
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Unable to Accept Modal -->
<div id="declineTripModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(7,15,30,0.7); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid var(--vg-pearl-200); box-shadow: var(--vg-shadow-lg);">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; background:#991B1B; color:#FFF;">
            <h3 style="margin:0; font-size:1.1rem; font-weight:700;"><i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i> Unable to Accept Duty</h3>
            <button type="button" onclick="closeModal('declineTripModal')" style="background:none; border:none; color:#FFF; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= url('/driver/trips/' . $trip['id'] . '/unable-to-accept') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="card-body" style="padding: 1.5rem;">
                <p style="font-size: 0.88rem; color: var(--vg-text-secondary); margin-bottom: 1.25rem;">
                    Please select or specify a reason so dispatch operations can promptly reassign trip <strong><?= e($trip['booking_code']) ?></strong> to another driver.
                </p>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="decline_preset">Reason Category</label>
                    <select id="decline_preset" class="form-control" onchange="if(this.value !== 'Other') { document.getElementById('decline_reason_custom').value = this.value; }" style="margin-bottom: 0.5rem;">
                        <option value="Schedule conflict">Schedule conflict</option>
                        <option value="Vehicle maintenance required">Vehicle maintenance required</option>
                        <option value="Health or personal emergency">Health or personal emergency</option>
                        <option value="Outside permitted operational area">Outside permitted operational area</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                    <textarea id="decline_reason_custom" name="reason" class="form-control" rows="3" placeholder="<?= e(__('placeholder.provide_additional_details', 'Provide additional details...')) ?>">Schedule conflict</textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:0.5rem; background:var(--vg-pearl-50); border-top:1px solid var(--vg-pearl-200); padding:0.85rem 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('declineTripModal')"><?= e(__('common.cancel', 'Cancel')) ?></button>
                <button type="submit" class="btn btn-danger" style="background:#EF4444; border-color:#EF4444;"><i class="fa-solid fa-xmark" style="margin-right:4px;"></i> Confirm &amp; Decline</button>
            </div>
        </form>
    </div>
</div>

<!-- Driver Lightbox Modal -->
<div id="driverLightbox" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; padding:1.5rem;" onclick="closeDriverLightbox()">
    <div style="position:relative; max-width:800px; width:100%; text-align:center;" onclick="event.stopPropagation()">
        <button type="button" onclick="closeDriverLightbox()" style="position:absolute; top:-36px; right:0; background:none; border:none; color:#FFF; font-size:1.8rem; cursor:pointer;">&times;</button>
        <div id="driverLightboxTitle" style="color:#FFF; font-weight:700; margin-bottom:8px; font-size:0.95rem;"></div>
        <img id="driverLightboxImg" src="" alt="Inspection Evidence" style="max-width:100%; max-height:80vh; border-radius:6px; object-fit:contain;">
    </div>
</div>

<script>
function openStartTripModal() {
    openModal('startTripModal');
    acquireStartGps();
}

function openCompleteTripModal() {
    openModal('completeTripModal');
    acquireEndGps();
    const endInput = document.getElementById('end_odo_val');
    if (endInput) {
        calculateTripDistance(endInput.value, <?= (int)($trip['start_odometer'] ?? 0) ?>);
    }
}

function acquireStartGps() {
    const statusText = document.getElementById('startGpsStatusText');
    if (!navigator.geolocation) {
        if (statusText) statusText.textContent = 'Geolocation not supported by browser.';
        return;
    }
    if (statusText) statusText.textContent = 'Acquiring GPS fix...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('start_lat').value = pos.coords.latitude.toFixed(7);
        document.getElementById('start_lng').value = pos.coords.longitude.toFixed(7);
        if (statusText) {
            statusText.innerHTML = '<strong style="color:#059669;">GPS Fix Acquired:</strong> ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
        }
    }, function(err) {
        if (statusText) {
            statusText.textContent = 'GPS fix unavailable (' + (err.message || 'permission denied') + ')';
        }
    }, { enableHighAccuracy: true, timeout: 10000 });
}

function acquireEndGps() {
    const statusText = document.getElementById('endGpsStatusText');
    if (!navigator.geolocation) {
        if (statusText) statusText.textContent = 'Geolocation not supported by browser.';
        return;
    }
    if (statusText) statusText.textContent = 'Acquiring GPS fix...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('end_lat').value = pos.coords.latitude.toFixed(7);
        document.getElementById('end_lng').value = pos.coords.longitude.toFixed(7);
        if (statusText) {
            statusText.innerHTML = '<strong style="color:#059669;">GPS Fix Acquired:</strong> ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
        }
    }, function(err) {
        if (statusText) {
            statusText.textContent = 'GPS fix unavailable (' + (err.message || 'permission denied') + ')';
        }
    }, { enableHighAccuracy: true, timeout: 10000 });
}

function calculateTripDistance(endVal, startVal) {
    const end = parseInt(endVal, 10) || 0;
    const start = parseInt(startVal, 10) || 0;
    const diff = Math.max(0, end - start);
    const box = document.getElementById('computedDistanceValue');
    if (box) {
        box.textContent = diff.toLocaleString() + ' KM driven';
    }
}

function previewImg(input, targetId, base64InputId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(targetId);
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            if (base64InputId) {
                const base64Input = document.getElementById(base64InputId);
                if (base64Input) {
                    base64Input.value = e.target.result;
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openDriverLightbox(url, title) {
    const box = document.getElementById('driverLightbox');
    const img = document.getElementById('driverLightboxImg');
    const titleEl = document.getElementById('driverLightboxTitle');
    img.src = url;
    titleEl.textContent = title || 'Evidence Photo';
    box.style.display = 'flex';
}

function closeDriverLightbox() {
    document.getElementById('driverLightbox').style.display = 'none';
}
</script>
