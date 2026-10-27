<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Dispatch & Assign Trip
 * Modern Form UI Matching Reference Design System
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/trips/history') ?>" class="modern-back-btn" title="Back to Trip History" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('trip_form.assign_title', [], 'Dispatch & Assign Trip')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('trip_form.assign_subtitle', [], 'Allocate commercial vehicle and licensed chauffeur to booking.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="<?= url('/office/trips/history') ?>" class="modern-btn-cancel" style="height: 42px; padding: 0 18px; font-size: 0.85rem;">
                <i class="fa-solid fa-clock-rotate-left"></i> <?= e(__('trip_form.trip_history', [], 'Trip History')) ?>
            </a>
        </div>
    </div>

    <form action="<?= url('/office/trips/assign') ?>" method="POST" id="assignTripForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">
            <!-- Section 1: Reservation Manifest -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title"><?= e(__('trip_form.manifest_title', [], 'Select Confirmed Reservation')) ?></h2>
                    <p class="modern-section-sub">Choose a pending tour booking to bind vehicle and chauffeur deployment.</p>
                </div>
            </div>

            <!-- Booking Selection -->
            <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="booking_id">
                    <?= e(__('trip_form.select_booking', [], 'Reservation Manifest')) ?> <span class="req">*</span>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <select id="booking_id" name="booking_id" class="modern-select-pill" required onchange="onBookingChange(this)">
                        <option value=""><?= e(__('trip_form.choose_booking', [], 'Choose a Confirmed Tour Reservation...')) ?></option>
                        <?php if ($selectedBooking): ?>
                            <option value="<?= $selectedBooking['id'] ?>" selected data-passengers="<?= $selectedBooking['passenger_count'] ?>" data-route="<?= e($selectedBooking['pickup_address']) ?> → <?= e($selectedBooking['drop_address']) ?>" data-date="<?= format_date($selectedBooking['start_date']) ?>">
                                <?= e($selectedBooking['booking_code']) ?> — <?= e($selectedBooking['customer_name'] ?? 'Guest') ?> (<?= $selectedBooking['passenger_count'] ?> <?= e(__('common.guests', [], 'guests')) ?>) · <?= format_date($selectedBooking['start_date']) ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($pendingBookings as $b): ?>
                            <?php if ($selectedBooking && $selectedBooking['id'] == $b['id']) continue; ?>
                            <option value="<?= $b['id'] ?>" data-passengers="<?= $b['passenger_count'] ?>" data-route="<?= e($b['pickup_address']) ?> → <?= e($b['drop_address']) ?>" data-date="<?= format_date($b['start_date']) ?>">
                                <?= e($b['booking_code']) ?> — (<?= $b['passenger_count'] ?> <?= e(__('common.guests', [], 'guests')) ?>) · <?= format_date($b['start_date']) ?> (<?= e($b['pickup_address']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Booking Summary Box -->
            <div id="bookingSummaryBox" style="background: #F8FAFD; border: 1.5px solid #DCE6F2; padding: 1.25rem 1.5rem; border-radius: 16px; margin-bottom: 1.75rem; display: <?= $selectedBooking ? 'block' : 'none' ?>;">
                <div style="font-size: 0.78rem; color: #176B5B; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;"><i class="fa-solid fa-route"></i> Itinerary Route Details</div>
                <div id="summaryRoute" style="font-weight: 800; color: #0C2B52; font-size: 1.05rem; margin-top: 0.35rem;">
                    <?= $selectedBooking ? e($selectedBooking['pickup_address']) . ' → ' . e($selectedBooking['drop_address']) : '' ?>
                </div>
                <div id="summaryMeta" style="font-size: 0.88rem; color: #64748B; margin-top: 0.35rem;">
                    <?= $selectedBooking ? 'Passenger Count: <strong>' . (int)$selectedBooking['passenger_count'] . ' guests</strong> • Departure Date: <strong>' . format_date($selectedBooking['start_date']) . '</strong>' : '' ?>
                </div>
            </div>

            <!-- Section 2: Fleet & Driver Deployment -->
            <div class="modern-section-header" style="margin-top: 1.5rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Vehicle &amp; Chauffeur Assignment</h2>
                    <p class="modern-section-sub">Pair an available commercial vehicle with an on-duty verified chauffeur.</p>
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Vehicle Selection -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="vehicle_id">
                        <?= e(__('trip_form.assign_vehicle', [], 'Commercial Vehicle')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-car"></i></span>
                        <select id="vehicle_id" name="vehicle_id" class="modern-select-pill" required>
                            <option value=""><?= e(__('trip_form.choose_vehicle', [], 'Choose Vehicle...')) ?></option>
                            <?php if (empty($availableVehicles)): ?>
                                <option value="" disabled><?= e(__('trip_form.no_vehicles', [], 'No vehicles currently available')) ?></option>
                            <?php else: ?>
                                <?php foreach ($availableVehicles as $v): ?>
                                    <option value="<?= $v['id'] ?>">
                                        <?= e($v['make_model']) ?> (<?= e($v['vehicle_number']) ?>) · <?= (int)$v['seating_capacity'] ?> Seats · <?= strtoupper(e($v['fuel_type'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Driver Selection -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="driver_id">
                        <?= e(__('trip_form.assign_driver', [], 'Assigned Chauffeur / Driver')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                        <select id="driver_id" name="driver_id" class="modern-select-pill" required>
                            <option value=""><?= e(__('trip_form.choose_driver', [], 'Choose Driver...')) ?></option>
                            <?php if (empty($availableDrivers)): ?>
                                <option value="" disabled><?= e(__('trip_form.no_drivers', [], 'No drivers currently available')) ?></option>
                            <?php else: ?>
                                <?php foreach ($availableDrivers as $d): ?>
                                    <option value="<?= $d['id'] ?>">
                                        <?= e($d['name']) ?> (<?= e($d['phone']) ?>) · License: <?= e($d['license_number']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/trips/history') ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= e(__('form.cancel', [], 'Cancel')) ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-paper-plane"></i> <?= e(__('trip_form.deploy_btn', [], 'Deploy Live Trip')) ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function onBookingChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const box = document.getElementById('bookingSummaryBox');
    if (!opt || !opt.value) {
        if (box) box.style.display = 'none';
        return;
    }
    const route = opt.getAttribute('data-route') || '';
    const date = opt.getAttribute('data-date') || '';
    const pass = opt.getAttribute('data-passengers') || '1';

    const sRoute = document.getElementById('summaryRoute');
    const sMeta = document.getElementById('summaryMeta');

    if (sRoute) sRoute.textContent = route;
    if (sMeta) sMeta.innerHTML = 'Passenger Count: <strong>' + pass + ' guests</strong> • Departure Date: <strong>' + date + '</strong>';
    if (box) box.style.display = 'block';
}
</script>
