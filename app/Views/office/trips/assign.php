<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Dispatch & Assign Trip
 * Modern Luxury Form UI Matching Customers Create Architecture
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional Header Card (Matching Stepper Container Layout without Steps) -->
    <div class="modern-header-centered-wrap">
        <div class="modern-header-centered-card">
            <div class="modern-header-icon-pill">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <div class="modern-header-content">
                <h1 class="modern-header-title"><?= e(__('trip_form.assign_title', 'Assign Trip Duty to Driver')) ?></h1>
                <p class="modern-header-subtitle"><?= e(__('trip_form.assign_subtitle', 'Deploy active chauffeur and commercial vehicle to an itinerary reservation manifest.')) ?></p>
            </div>
        </div>
    </div>

    <!-- Master Form Container -->
    <form action="<?= url('/office/trips/assign') ?>" method="POST" id="assignTripForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">
            <!-- Section 1: Reservation Manifest -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title"><?= e(__('trip_form.manifest_title', 'Dispatch Assignment Manifest')) ?></h2>
                    <p class="modern-section-sub"><?= e(__('trip_form.manifest_summary', 'Choose a tour booking to bind commercial vehicle and chauffeur deployment.')) ?></p>
                </div>
            </div>

            <!-- Booking Selection -->
            <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="booking_id">
                    <?= e(__('trip_form.select_booking', 'Select Reservation Itinerary')) ?> <span class="req">*</span>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <select id="booking_id" name="booking_id" class="modern-select-pill" required onchange="onBookingChange(this)">
                        <option value=""><?= e(__('trip_form.choose_booking', 'Choose Pending / Confirmed Booking...')) ?></option>
                        <?php if ($selectedBooking): ?>
                            <option value="<?= (int)$selectedBooking['id'] ?>" selected 
                                    data-passengers="<?= (int)$selectedBooking['passenger_count'] ?>" 
                                    data-route="<?= e($selectedBooking['pickup_address']) ?> → <?= e($selectedBooking['drop_address']) ?>" 
                                    data-date="<?= format_date($selectedBooking['start_date']) ?>">
                                <?= e($selectedBooking['booking_code']) ?> — <?= e($selectedBooking['customer_name'] ?? 'Guest') ?> (<?= (int)$selectedBooking['passenger_count'] ?> <?= e(__('common.guests', 'guests')) ?>) · <?= format_date($selectedBooking['start_date']) ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($pendingBookings as $b): ?>
                            <?php if ($selectedBooking && $selectedBooking['id'] == $b['id']) continue; ?>
                            <option value="<?= (int)$b['id'] ?>" 
                                    data-passengers="<?= (int)$b['passenger_count'] ?>" 
                                    data-route="<?= e($b['pickup_address']) ?> → <?= e($b['drop_address']) ?>" 
                                    data-date="<?= format_date($b['start_date']) ?>">
                                <?= e($b['booking_code']) ?> — (<?= (int)$b['passenger_count'] ?> <?= e(__('common.guests', 'guests')) ?>) · <?= format_date($b['start_date']) ?> (<?= e($b['pickup_address']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Modern Luxury Booking Route Summary Box -->
            <div id="bookingSummaryBox" class="trip-manifest-summary-card" style="display: <?= $selectedBooking ? 'block' : 'none' ?>;">
                <div class="trip-summary-header">
                    <span class="trip-summary-badge"><i class="fa-solid fa-route"></i> <?= e(__('trip_form.manifest_summary', 'Itinerary Route Details')) ?></span>
                    <span class="trip-summary-status"><i class="fa-solid fa-circle-check"></i> <?= e(__('status.confirmed', 'Confirmed Reservation')) ?></span>
                </div>
                <div id="summaryRoute" class="trip-summary-route">
                    <?= $selectedBooking ? e($selectedBooking['pickup_address']) . ' → ' . e($selectedBooking['drop_address']) : '' ?>
                </div>
                <div id="summaryMeta" class="trip-summary-meta">
                    <?php if ($selectedBooking): ?>
                        <span class="trip-summary-meta-item"><i class="fa-solid fa-users"></i> <?= e(__('dashboard.guests_count', 'Passenger Count')) ?>: <strong><?= (int)$selectedBooking['passenger_count'] ?> guests</strong></span>
                        <span class="trip-summary-meta-dot">•</span>
                        <span class="trip-summary-meta-item"><i class="fa-solid fa-calendar-day"></i> <?= e(__('table.date', 'Departure Date')) ?>: <strong><?= format_date($selectedBooking['start_date']) ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 2: Fleet & Driver Deployment -->
            <div class="modern-section-header" style="margin-top: 2rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title"><?= e(__('trip_form.deployment_badge', 'Vehicle & Chauffeur Assignment')) ?></h2>
                    <p class="modern-section-sub"><?= e(__('trip_form.driver_hint', 'Pair an available commercial vehicle with an on-duty verified chauffeur.')) ?></p>
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Vehicle Selection -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="vehicle_id">
                        <?= e(__('trip_form.assign_vehicle', 'Assigned Commercial Vehicle')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-car"></i></span>
                        <select id="vehicle_id" name="vehicle_id" class="modern-select-pill" required>
                            <option value=""><?= e(__('trip_form.choose_vehicle', 'Select Ready Fleet Vehicle...')) ?></option>
                            <?php if (empty($availableVehicles)): ?>
                                <option value="" disabled><?= e(__('trip_form.no_vehicles', 'No vehicles currently available')) ?></option>
                            <?php else: ?>
                                <?php foreach ($availableVehicles as $v): ?>
                                    <option value="<?= (int)$v['id'] ?>">
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
                        <?= e(__('trip_form.assign_driver', 'Assigned Chauffeur')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                        <select id="driver_id" name="driver_id" class="modern-select-pill" required>
                            <option value=""><?= e(__('trip_form.choose_driver', 'Select Active Chauffeur...')) ?></option>
                            <?php if (empty($availableDrivers)): ?>
                                <option value="" disabled><?= e(__('trip_form.no_drivers', 'No drivers currently available')) ?></option>
                            <?php else: ?>
                                <?php foreach ($availableDrivers as $d): ?>
                                    <option value="<?= (int)$d['id'] ?>">
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
                    <i class="fa-solid fa-xmark"></i> <?= e(__('form.cancel', 'Cancel')) ?>
                </a>
                <button type="submit" class="modern-btn-submit" id="btnDeployTrip">
                    <i class="fa-solid fa-paper-plane"></i> <?= e(__('trip_form.deploy_btn', 'Deploy Trip Assignment')) ?>
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
    if (sMeta) {
        sMeta.innerHTML = '<span class="trip-summary-meta-item"><i class="fa-solid fa-users"></i> Passenger Count: <strong>' + pass + ' guests</strong></span>' +
                          '<span class="trip-summary-meta-dot">•</span>' +
                          '<span class="trip-summary-meta-item"><i class="fa-solid fa-calendar-day"></i> Departure Date: <strong>' + date + '</strong></span>';
    }
    if (box) box.style.display = 'block';
}
</script>

<style>
/* Full-Width Workspace Form Wrapper & Master Card */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem;
}

.modern-form-card {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 2.25rem 2.5rem;
    box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05);
}

/* Centered Professional Header (Matching Stepper Card Layout without Steps) */
.modern-header-centered-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
    padding: 0;
    width: 100%;
}

.modern-header-centered-card {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 1.15rem 2rem;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.15rem;
    text-align: center;
}

.modern-header-icon-pill {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 14px;
    background: #E8F4F0;
    color: #176B5B;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: inset 0 0 0 1px rgba(23, 107, 91, 0.18);
}

.modern-header-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.modern-header-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    line-height: 1.25;
    letter-spacing: -0.02em;
}

.modern-header-subtitle {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0.25rem 0 0 0;
    font-weight: 500;
}

/* Section Header inside Card */
.modern-section-header {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1.5px solid #F1F5F9;
}

.modern-section-badge {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 14px;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: inset 0 0 0 1px rgba(23, 107, 91, 0.15);
}

.modern-section-meta {
    display: flex;
    flex-direction: column;
}

.modern-section-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    line-height: 1.3;
}

.modern-section-sub {
    font-size: 0.85rem;
    color: #64748B;
    margin: 0.25rem 0 0 0;
}

/* Modern Pill Inputs & Selects */
.modern-form-group {
    display: flex;
    flex-direction: column;
}

.modern-form-label {
    display: flex;
    align-items: center;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0C2B52;
    margin-bottom: 0.5rem;
}

.modern-form-label .req {
    color: #EF4444;
    margin-left: 3px;
    font-weight: 700;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.modern-input-icon {
    position: absolute;
    left: 1.15rem;
    color: #176B5B;
    font-size: 1rem;
    pointer-events: none;
    z-index: 2;
}

.modern-select-pill {
    width: 100%;
    height: 48px;
    padding: 0 1.25rem 0 2.85rem;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 9999px;
    font-size: 0.92rem;
    font-weight: 600;
    color: #0C2B52;
    transition: all 0.2s ease;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1.15rem center;
    background-size: 16px;
    cursor: pointer;
}

.modern-select-pill:focus {
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.14);
}

/* 2-Column Responsive Form Grid */
.modern-form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Manifest Route Summary Box */
.trip-manifest-summary-card {
    background: linear-gradient(135deg, #F8FAFD 0%, #F1F6FA 100%);
    border: 1.5px solid #B2D8CF;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.04);
}

.trip-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}

.trip-summary-badge {
    font-size: 0.78rem;
    color: #176B5B;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.trip-summary-status {
    font-size: 0.78rem;
    color: #059669;
    font-weight: 700;
    background: #E8F5E9;
    padding: 3px 10px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.trip-summary-route {
    font-weight: 800;
    color: #0C2B52;
    font-size: 1.05rem;
    line-height: 1.4;
    margin-top: 0.25rem;
}

.trip-summary-meta {
    font-size: 0.88rem;
    color: #64748B;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.trip-summary-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.trip-summary-meta-dot {
    color: #CBD5E1;
}

/* Modern Form Action Buttons */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2.25rem;
    padding-top: 1.75rem;
    border-top: 1.5px solid #F1F5F9;
}

.modern-btn-cancel {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 0.75rem 1.75rem;
    border-radius: 9999px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.modern-btn-cancel:hover {
    background: #F8FAFC;
    border-color: #94A3B8;
    color: #1E293B;
}

.modern-btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    padding: 0.75rem 2.25rem;
    border-radius: 9999px;
    font-size: 0.95rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
}

.modern-btn-submit:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .modern-form-wrapper {
        padding: 0 0 2rem;
    }
    .modern-header-centered-card {
        padding: 1rem 1.25rem;
        flex-direction: column;
        text-align: center;
        gap: 0.65rem;
        border-radius: 18px;
    }
    .modern-header-content {
        align-items: center;
        text-align: center;
    }
    .modern-form-card {
        padding: 1.5rem 1.25rem;
        border-radius: 16px;
    }
    .modern-form-grid-2 {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    .modern-form-actions {
        flex-direction: column-reverse;
        width: 100%;
        gap: 0.75rem;
    }
    .modern-btn-cancel,
    .modern-btn-submit {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .modern-header-title {
        font-size: 1.1rem;
    }
    .modern-header-subtitle {
        font-size: 0.8rem;
    }
    .modern-section-header {
        gap: 0.85rem;
        margin-bottom: 1.25rem;
    }
    .modern-section-badge {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 1.05rem;
    }
    .modern-section-title {
        font-size: 1.02rem;
    }
    .trip-manifest-summary-card {
        padding: 1rem 1.15rem;
    }
    .trip-summary-route {
        font-size: 0.95rem;
    }
}
</style>
