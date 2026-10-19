<div style="max-width: 860px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Assign Trip Duty to Driver</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Deploy active chauffeur and commercial vehicle to an itinerary reservation manifest.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= url('/office/trips/history') ?>" class="btn btn-outline btn-sm">Trip History</a>
            <a href="<?= url('/office/bookings') ?>" class="btn btn-outline btn-sm">Reservation Manifests</a>
        </div>
    </div>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Dispatch Assignment Manifest</span>
            <span class="badge badge-navy">Real-Time Duty Deployment</span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/trips/assign') ?>" method="POST" id="assignTripForm">
                <?= csrf_field() ?>

                <!-- Booking Selection -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="booking_id">Select Reservation Itinerary <span class="required-indicator">*</span></label>
                    <select id="booking_id" name="booking_id" class="form-select" required onchange="onBookingChange(this)">
                        <option value="">Choose Pending / Confirmed Booking</option>
                        <?php if ($selectedBooking): ?>
                            <option value="<?= $selectedBooking['id'] ?>" selected data-passengers="<?= $selectedBooking['passenger_count'] ?>" data-route="<?= e($selectedBooking['pickup_address']) ?> → <?= e($selectedBooking['drop_address']) ?>" data-date="<?= format_date($selectedBooking['start_date']) ?>">
                                <?= e($selectedBooking['booking_code']) ?> — <?= e($selectedBooking['customer_name'] ?? 'Guest') ?> (<?= $selectedBooking['passenger_count'] ?> Guests) · <?= format_date($selectedBooking['start_date']) ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($pendingBookings as $b): ?>
                            <?php if ($selectedBooking && $selectedBooking['id'] == $b['id']) continue; ?>
                            <option value="<?= $b['id'] ?>" data-passengers="<?= $b['passenger_count'] ?>" data-route="<?= e($b['pickup_address']) ?> → <?= e($b['drop_address']) ?>" data-date="<?= format_date($b['start_date']) ?>">
                                <?= e($b['booking_code']) ?> — (<?= $b['passenger_count'] ?> Guests) · <?= format_date($b['start_date']) ?> (<?= e($b['pickup_address']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="bookingSummaryBox" style="background: var(--vg-pearl-100); border: 1px solid var(--vg-pearl-200); padding: 1rem 1.25rem; border-radius: var(--vg-radius-md); margin-bottom: 1.5rem; display: <?= $selectedBooking ? 'block' : 'none' ?>;">
                    <div style="font-size: 0.82rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Manifest Itinerary Summary</div>
                    <div id="summaryRoute" style="font-weight: 700; color: var(--vg-navy-900); font-size: 1rem; margin-top: 0.25rem;">
                        <?= $selectedBooking ? e($selectedBooking['pickup_address']) . ' → ' . e($selectedBooking['drop_address']) : '' ?>
                    </div>
                    <div id="summaryMeta" style="font-size: 0.85rem; color: var(--vg-text-secondary); margin-top: 0.25rem;">
                        <?= $selectedBooking ? 'Passenger Count: <strong>' . (int)$selectedBooking['passenger_count'] . ' Guests</strong> • Start Date: <strong>' . format_date($selectedBooking['start_date']) . '</strong>' : '' ?>
                    </div>
                </div>

                <!-- Vehicle Selection -->
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="vehicle_id">Assigned Commercial Vehicle <span class="required-indicator">*</span></label>
                        <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                            <option value="">Select Ready Fleet Vehicle</option>
                            <?php if (empty($availableVehicles)): ?>
                                <option value="" disabled>⚠️ No vehicles currently marked as available</option>
                            <?php else: ?>
                                <?php foreach ($availableVehicles as $v): ?>
                                    <option value="<?= $v['id'] ?>">
                                        <?= e($v['make_model']) ?> (<?= e($v['vehicle_number']) ?>) · <?= (int)$v['seating_capacity'] ?> Seats · <?= strtoupper(e($v['fuel_type'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color: var(--vg-text-muted); font-size: 0.78rem;">Only vehicles with 'Available' status and adequate capacity are eligible.</small>
                    </div>

                    <!-- Driver Selection -->
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="driver_id">Assigned Chauffeur <span class="required-indicator">*</span></label>
                        <select id="driver_id" name="driver_id" class="form-select" required>
                            <option value="">Select Active Chauffeur</option>
                            <?php if (empty($availableDrivers)): ?>
                                <option value="" disabled>⚠️ No drivers currently available on duty</option>
                            <?php else: ?>
                                <?php foreach ($availableDrivers as $d): ?>
                                    <option value="<?= $d['id'] ?>">
                                        <?= e($d['name']) ?> (<?= e($d['phone']) ?>) · License: <?= e($d['license_number']) ?> · <?= ucfirst(str_replace('_', ' ', $d['duty_status'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color: var(--vg-text-muted); font-size: 0.78rem;">Chauffeur must be on duty and not currently assigned to an active trip.</small>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/trips/history') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-navy">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>Deploy Trip Assignment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function onBookingChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const box = document.getElementById('bookingSummaryBox');
    const route = document.getElementById('summaryRoute');
    const meta = document.getElementById('summaryMeta');

    if (!opt.value) {
        box.style.display = 'none';
        return;
    }

    const routeText = opt.getAttribute('data-route') || 'Route details';
    const passengers = opt.getAttribute('data-passengers') || '1';
    const dateText = opt.getAttribute('data-date') || '';

    route.innerText = routeText;
    meta.innerHTML = 'Passenger Count: <strong>' + passengers + ' Guests</strong> • Start Date: <strong>' + dateText + '</strong>';
    box.style.display = 'block';
}
</script>
