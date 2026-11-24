<?php
/**
 * Create Booking Reservation
 * Professional 4-step wizard matching customers/create architecture & design system
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional 5-Step Stepper Header -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label"><?= e(__('booking.client_guests', [], 'Client & Guests')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label"><?= e(__('booking.itinerary_route', [], 'Itinerary & Route')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label"><?= e(__('booking.trip_charges', [], 'Trip Charges')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label"><?= e(__('finance.payment', [], 'Payment')) ?> (<?= e(__('common.optional', [], 'Optional')) ?>)</span>
                </button>
                <div class="modern-step-connector" id="stepConn4"></div>

                <!-- Step 5 -->
                <button type="button" class="modern-step-item" data-step-target="5" onclick="wizardGoToStep(5)">
                    <span class="modern-step-circle">5</span>
                    <span class="modern-step-label"><?= e(__('booking.review_confirm', [], 'Review & Confirm')) ?></span>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: __('form.please_review_errors')) ?>;
                var emit = function() {
                    if (typeof window.vgNotify === 'function') {
                        window.vgNotify(firstErr, 'error');
                    } else {
                        setTimeout(emit, 40);
                    }
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', emit);
                } else {
                    emit();
                }
            })();
        </script>
    <?php endif; ?>

    <!-- Master Form Container -->
    <form action="<?= url('/office/bookings') ?>" method="POST" id="createBookingForm" novalidate>
        <?= csrf_field() ?>
        <!-- Idempotency Token to prevent duplicate submissions -->
        <input type="hidden" name="idempotency_token" value="<?= bin2hex(random_bytes(16)) ?>">
        <input type="hidden" name="tour_id" value="">
        <input type="hidden" id="discount_amount" name="discount_amount" value="0.00">

        <!-- ========================================================================= -->
        <!-- STEP 1: Client Identification & Passengers                                -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel1">
            <div class="modern-form-card">
                <!-- Section Header -->
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-user-tag"></i>
                    </div>
                    <div class="modern-section-meta" style="flex: 1;">
                        <h2 class="modern-section-title"><?= e(__('booking.client_identification', [], 'Client Identification')) ?></h2>
                        <p class="modern-section-sub">Assign reservation to a verified guest or corporate client and set passenger count.</p>
                    </div>

                    <?php if (auth_role() !== 'customer'): ?>
                    <a href="<?= url('/office/customers/create') ?>" target="_blank" class="booking-add-cust-btn" title="<?= e(__('tooltip.register_new_customer_profile', 'Register New Customer Profile')) ?>">
                        <i class="fa-solid fa-user-plus"></i> <?= e(__('btn.add_customer', [], 'Add Customer')) ?>
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (auth_role() === 'customer'): ?>
                    <input type="hidden" name="customer_id" id="customer_id" value="<?= auth_id() ?>">
                    <div style="background: #E8F4F0; border: 1.5px solid #B2D8CF; padding: 1.15rem 1.35rem; border-radius: 16px; font-size: 0.92rem; color: #176B5B; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
                            <div>
                                Reservation will be registered under your verified client account: <strong><?= e(auth_user()['name'] ?? 'Client') ?></strong>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                        <label class="modern-form-label" for="customer_id" style="display: flex; justify-content: space-between; align-items: center;">
                            <span><?= e(__('booking.guest_account', [], 'Guest Account')) ?> <span class="req">*</span></span>
                            <span style="font-size: 0.78rem; color: #64748B; font-weight: normal;"><?= count($customers) ?> verified guest profile<?= count($customers) === 1 ? '' : 's' ?> available</span>
                        </label>

                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                            <select id="customer_id" name="customer_id" class="modern-select-pill" required onchange="onCustomerSelect(this); syncReviewData();">
                                <option value="">— Select verified client profile —</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-name="<?= e($c['name']) ?>" data-phone="<?= e($c['phone'] ?? $c['email'] ?? '') ?>" data-code="<?= e($c['customer_code'] ?? '') ?>">
                                        <?= e($c['name']) ?> (<?= e($c['phone'] ?? $c['email'] ?? 'No contact') ?><?= !empty($c['customer_code']) ? ' &bull; ' . e($c['customer_code']) : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!empty($errors['customer_id'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['customer_id'][0]) ?></div>
                        <?php endif; ?>

                        <!-- Dynamic Client Identification Card -->
                        <div id="customerSelectedInfo" style="display: none; margin-top: 1rem; padding: 1rem 1.35rem; border-radius: 14px; background: #E8F4F0; border: 1.5px solid #B2D8CF; font-size: 0.88rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #176B5B;"></span>
                                    <strong id="custSelectedName" style="color: #0C2B52; font-size: 1rem;"></strong>
                                    <span id="custSelectedCode" style="color: #176B5B; font-family: monospace; font-weight: 700; padding: 3px 10px; background: #FFFFFF; border-radius: 6px; border: 1px solid #B2D8CF; font-size: 0.82rem;"></span>
                                </div>
                                <div id="custSelectedPhone" style="color: #125748; font-weight: 700;"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="modern-form-grid-2">
                    <!-- Passenger Headcount -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="passenger_count">
                            <?= e(__('booking.passenger_headcount', [], 'Passenger Headcount')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-users-line"></i></span>
                            <input type="number" id="passenger_count" name="passenger_count" class="modern-input-pill" value="2" min="1" max="50" required oninput="syncReviewData()" onchange="calculateFare(); syncReviewData();">
                        </div>
                        <?php if (!empty($errors['passenger_count'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['passenger_count'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Client Account Type / Tier Indicator -->
                    <div class="modern-form-group">
                        <label class="modern-form-label">
                            <?= e(__('booking.reservation_priority', [], 'Reservation Priority')) ?>
                        </label>
                        <div style="display: flex; align-items: center; height: 46px; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 0 14px; gap: 8px;">
                            <i class="fa-solid fa-star" style="color: #F59E0B;"></i>
                            <span style="font-weight: 700; color: #0C2B52; font-size: 0.9rem;"><?= e(__('booking.standard_fleet_roster', [], 'Standard Fleet Roster')) ?></span>
                            <span style="margin-left: auto; font-size: 0.74rem; background: #E8F4F0; color: #176B5B; padding: 2px 8px; border-radius: 50px; font-weight: 700;"><?= e(__('booking.instant_confirmation', [], 'Instant Confirmation')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/bookings') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> <?= e(__('common.cancel', [], 'Cancel')) ?>
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        <?= e(__('form.next', [], 'Next')) ?> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: Itinerary Route & Timetable                                       -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel2" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title"><?= e(__('booking.itinerary_schedule', [], 'Itinerary Route & Schedule')) ?></h2>
                        <p class="modern-section-sub">Trip category, pickup address, destination drop, journey dates, and driver instructions.</p>
                    </div>
                </div>

                <!-- Trip Category Selection -->
                <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                    <label class="modern-form-label" for="trip_category_id">
                        <?= e(__('booking.trip_category', [], 'Trip Category')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-tag"></i></span>
                        <select id="trip_category_id" name="trip_category_id" class="modern-input-pill" required onchange="syncReviewData()" style="cursor: pointer; appearance: auto; -webkit-appearance: auto;">
                            <option value=""><?= e(__('booking.select_trip_category', [], '-- Select Trip Category --')) ?></option>
                            <?php if (!empty($tripCategories)): ?>
                                <?php foreach ($tripCategories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>" <?= (isset($_POST['trip_category_id']) && (int)$_POST['trip_category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php if (!empty($errors['trip_category_id'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['trip_category_id'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Origin & Destination Grid -->
                <div class="modern-form-grid-2">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="pickup_address">
                            <?= e(__('booking.departure_hub', [], 'Pickup Address / Departure Hub')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="pickup_address" name="pickup_address" class="modern-input-pill" required placeholder="<?= e(__('placeholder.e_g_terminal_3_igi_airport_hot', 'e.g. Terminal 3, IGI Airport / Hotel')) ?>" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['pickup_address'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['pickup_address'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="drop_address">
                            <?= e(__('booking.destination_drop', [], 'Drop Address / Final Destination')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-map-pin"></i></span>
                            <input type="text" id="drop_address" name="drop_address" class="modern-input-pill" required placeholder="<?= e(__('placeholder.e_g_the_oberoi_jaipur_local_si', 'e.g. The Oberoi, Jaipur / Local Sightseeing')) ?>" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['drop_address'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['drop_address'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Schedule Timetable 3-Column Grid -->
                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="start_date">
                            Departure Date <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="start_date" name="start_date" class="modern-input-pill" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" onchange="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['start_date'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['start_date'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="end_date">
                            Return Date <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <input type="date" id="end_date" name="end_date" class="modern-input-pill" required value="<?= date('Y-m-d', strtotime('+4 days')) ?>" onchange="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['end_date'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['end_date'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="pickup_time">
                            Pickup Time
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-clock"></i></span>
                            <input type="time" id="pickup_time" name="pickup_time" class="modern-input-pill" value="09:00" onchange="syncReviewData()">
                        </div>
                    </div>
                </div>

                <!-- Special Requests & Driver Instructions -->
                <div class="modern-form-group" style="margin-top: 0.5rem;">
                    <label class="modern-form-label" for="special_requests">
                        Special Requests &amp; Driver Instructions
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                        <input type="text" id="special_requests" name="special_requests" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_vip_welcome_placard_at_air', 'e.g. VIP welcome placard at airport, luggage van assistance, infant car seat requested')) ?>" oninput="syncReviewData()">
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(1)">
                        <i class="fa-solid fa-arrow-left"></i> <?= e(__('form.previous', [], 'Previous')) ?>
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(2)">
                        <?= e(__('form.next', [], 'Next')) ?> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 3: Fare Calculation & Billing                                        -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel3" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div class="modern-section-meta" style="flex: 1;">
                        <h2 class="modern-section-title"><?= e(__('booking.trip_charges', [], 'Trip Charges & Fare Calculation')) ?></h2>
                        <p class="modern-section-sub">Distance, daily vehicle hire, halting, extra charges, discounts, and statutory GST breakdown.</p>
                    </div>
                    <span id="manifestHeaderBadge" class="modern-badge doc-category-badge">5% GST</span>
                </div>

                <div class="fare-step-layout" style="display: grid; grid-template-columns: 1fr 380px; gap: 1.75rem; align-items: start;">
                    <!-- Left: Fare Inputs & Presets -->
                    <div class="fare-inputs-column" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <!-- Hidden Form Elements for Central Calculation & Persistence -->
                        <input type="hidden" id="custom_subtotal" name="custom_subtotal" value="0.00">
                        <input type="hidden" id="discount_amount" name="discount_amount" value="0.00">
                        <input type="hidden" id="billing_items_json" name="billing_items_json" value="[]">

                        <!-- Itemized Category A: Distance Charges -->
                        <div class="billing-card" style="background:#FFFFFF; border:1.5px solid #CBD5E1; border-radius:16px; padding:1.25rem; box-shadow:0 2px 8px rgba(12,43,82,0.04);">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem; border-bottom:1px solid #F1F5F9; padding-bottom:0.6rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="width:32px; height:32px; border-radius:8px; background:#E8F4F0; color:#176B5B; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                        <i class="fa-solid fa-road"></i>
                                    </span>
                                    <div>
                                        <h4 style="margin:0; font-size:0.95rem; font-weight:800; color:#0C2B52;">A. Distance Charges</h4>
                                        <span style="font-size:0.75rem; color:#64748B;">Total KM × Rate Per KM, or enter the lump-sum distance fee</span>
                                    </div>
                                </div>
                                <span id="distBadge" style="font-size:0.75rem; font-weight:700; color:#176B5B; background:#E8F4F0; padding:3px 10px; border-radius:6px;">₹ 0.00</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.85rem;">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_distance_km">Total KM</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-gauge-high"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_distance_km" name="billing_distance_km" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_75_00', 'e.g. 75.00')) ?>" oninput="onDistanceKmOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_rate_per_km">Rate / KM (₹)</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_rate_per_km" name="billing_rate_per_km" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_11_20', 'e.g. 11.20')) ?>" oninput="onDistanceKmOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px; font-weight:800;" for="billing_distance_amount">Distance Charge (₹) <span class="req">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_distance_amount" name="billing_distance_amount" class="modern-input-pill" value="0.00" style="font-weight:700; color:#0C2B52;" oninput="onDistanceAmountDirectChanged()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itemized Category B: Daily Vehicle Charges -->
                        <div class="billing-card" style="background:#FFFFFF; border:1.5px solid #CBD5E1; border-radius:16px; padding:1.25rem; box-shadow:0 2px 8px rgba(12,43,82,0.04);">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem; border-bottom:1px solid #F1F5F9; padding-bottom:0.6rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="width:32px; height:32px; border-radius:8px; background:#E8F4F0; color:#176B5B; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </span>
                                    <div>
                                        <h4 style="margin:0; font-size:0.95rem; font-weight:800; color:#0C2B52;">B. Daily Vehicle Charges</h4>
                                        <span style="font-size:0.75rem; color:#64748B;">Journey duration in days × Daily rate (e.g. 1 Day @ ₹1,300)</span>
                                    </div>
                                </div>
                                <span id="dailyBadge" style="font-size:0.75rem; font-weight:700; color:#176B5B; background:#E8F4F0; padding:3px 10px; border-radius:6px;">₹ 0.00</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.85rem;">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_daily_days">Days</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-clock"></i></span>
                                        <input type="number" step="1" min="0" id="billing_daily_days" name="billing_daily_days" class="modern-input-pill" value="1" placeholder="1" oninput="onDailyDaysOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_rate_per_day">Rate / Day (₹)</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_rate_per_day" name="billing_rate_per_day" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_1300_00', 'e.g. 1300.00')) ?>" oninput="onDailyDaysOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px; font-weight:800;" for="billing_daily_amount">Daily Charge (₹) <span class="req">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_daily_amount" name="billing_daily_amount" class="modern-input-pill" value="0.00" style="font-weight:700; color:#0C2B52;" oninput="onDailyAmountDirectChanged()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itemized Category C: Halting Charges (Optional) -->
                        <div class="billing-card" style="background:#FFFFFF; border:1.5px solid #CBD5E1; border-radius:16px; padding:1.25rem; box-shadow:0 2px 8px rgba(12,43,82,0.04);">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem; border-bottom:1px solid #F1F5F9; padding-bottom:0.6rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="width:32px; height:32px; border-radius:8px; background:#F1F5F9; color:#475569; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                        <i class="fa-solid fa-moon"></i>
                                    </span>
                                    <div>
                                        <h4 style="margin:0; font-size:0.95rem; font-weight:800; color:#0C2B52;">C. Halting Charges <span style="font-weight:400; color:#94A3B8; font-size:0.75rem;">(Optional)</span></h4>
                                        <span style="font-size:0.75rem; color:#64748B;">Night halt detention, outstation sleepover or driver waiting</span>
                                    </div>
                                </div>
                                <span id="haltBadge" style="font-size:0.75rem; font-weight:700; color:#64748B; background:#F1F5F9; padding:3px 10px; border-radius:6px;">₹ 0.00</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.85rem;">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_halting_days">Halting Nights / Days</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-bed"></i></span>
                                        <input type="number" step="1" min="0" id="billing_halting_days" name="billing_halting_days" class="modern-input-pill" value="0" placeholder="0" oninput="onHaltingDaysOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px;" for="billing_halting_rate">Rate / Halt (₹)</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_halting_rate" name="billing_halting_rate" class="modern-input-pill" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>" oninput="onHaltingDaysOrRateChanged()">
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" style="font-size:0.8rem; margin-bottom:4px; font-weight:800;" for="billing_halting_amount">Halting Charge (₹)</label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                        <input type="number" step="0.01" min="0" id="billing_halting_amount" name="billing_halting_amount" class="modern-input-pill" value="0.00" style="font-weight:700; color:#0C2B52;" oninput="onHaltingAmountDirectChanged()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itemized Category D: Extra Charges (If Any) -->
                        <div class="billing-card" style="background:#FFFFFF; border:1.5px solid #CBD5E1; border-radius:16px; padding:1.25rem; box-shadow:0 2px 8px rgba(12,43,82,0.04);">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem; border-bottom:1px solid #F1F5F9; padding-bottom:0.6rem; flex-wrap:wrap; gap:0.5rem;">
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span style="width:32px; height:32px; border-radius:8px; background:#E8F4F0; color:#176B5B; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                        <i class="fa-solid fa-receipt"></i>
                                    </span>
                                    <div>
                                        <h4 style="margin:0; font-size:0.95rem; font-weight:800; color:#0C2B52;">D. Extra Charges (If Any)</h4>
                                        <span style="font-size:0.75rem; color:#64748B;">Add optional expenses like parking, toll, permit, extra driver, or custom expenses.</span>
                                    </div>
                                </div>
                                <span id="extraChargesBadge" style="font-size:0.75rem; font-weight:700; color:#176B5B; background:#E8F4F0; padding:3px 10px; border-radius:6px;">₹ 0.00</span>
                            </div>

                            <!-- Quick Charge Options (Editable shortcuts, not mandatory, never fixed prices) -->
                            <div class="extra-charges-shortcuts" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.85rem;">
                                <span style="font-size:0.75rem; font-weight:700; color:#64748B;">Quick Options:</span>
                                <button type="button" class="booking-preset-pill extra-opt-pill" onclick="selectQuickExtraOption('Parking')"><i class="fa-solid fa-square-parking" style="margin-right:4px;"></i> Parking</button>
                                <button type="button" class="booking-preset-pill extra-opt-pill" onclick="selectQuickExtraOption('Toll')"><i class="fa-solid fa-road-barrier" style="margin-right:4px;"></i> Toll</button>
                                <button type="button" class="booking-preset-pill extra-opt-pill" onclick="selectQuickExtraOption('Permit')"><i class="fa-solid fa-id-card" style="margin-right:4px;"></i> Permit</button>
                                <button type="button" class="booking-preset-pill extra-opt-pill" onclick="selectQuickExtraOption('Extra Driver')"><i class="fa-solid fa-user-plus" style="margin-right:4px;"></i> Extra Driver</button>
                                <button type="button" class="booking-preset-pill extra-opt-pill" onclick="selectQuickExtraOption('Other')"><i class="fa-solid fa-plus" style="margin-right:4px;"></i> Other</button>
                            </div>

                            <!-- Custom Charge Entry Form -->
                            <div class="extra-charge-form-box" style="background:#F8FAFD; border:1px solid #CBD5E1; border-radius:14px; padding:0.9rem 1rem; margin-bottom:1rem;">
                                <div style="font-size:0.8rem; font-weight:700; color:#0C2B52; margin-bottom:0.65rem; display:flex; align-items:center; justify-content:space-between;">
                                    <span id="extraFormModeTitle" style="display:flex; align-items:center; gap:6px;">
                                        <i class="fa-solid fa-circle-plus" style="color:#176B5B;"></i> Add Extra Charge
                                    </span>
                                    <button type="button" id="btnCancelEditExtra" onclick="cancelEditExtraCharge()" style="display:none; background:transparent; border:none; color:#EF4444; font-size:0.75rem; font-weight:700; cursor:pointer;">
                                        <i class="fa-solid fa-xmark"></i> <?= e(__('common.cancel', [], 'Cancel')) ?> Edit
                                    </button>
                                </div>
                                <div class="extra-charge-inputs-grid" style="display:grid; grid-template-columns: 2fr 1fr 1.2fr 1.2fr auto; gap:0.65rem; align-items:end;">
                                    <div>
                                        <label class="modern-form-label" style="font-size:0.75rem; color:#64748B; margin-bottom:3px;" for="extra_charge_name">Charge Name</label>
                                        <input type="text" id="extra_charge_name" class="modern-input-pill" style="height:38px; font-size:0.85rem; padding:0 12px;" placeholder="<?= e(__('placeholder.e_g_parking_airport_toll_food', 'e.g. Parking, Airport Toll, Food...')) ?>">
                                    </div>
                                    <div>
                                        <label class="modern-form-label" style="font-size:0.75rem; color:#64748B; margin-bottom:3px;" for="extra_charge_qty">Quantity</label>
                                        <input type="number" step="1" min="1" id="extra_charge_qty" class="modern-input-pill" style="height:38px; font-size:0.85rem; padding:0 8px; text-align:center;" value="1" oninput="onExtraQtyOrPriceChange()">
                                    </div>
                                    <div>
                                        <label class="modern-form-label" style="font-size:0.75rem; color:#64748B; margin-bottom:3px;" for="extra_charge_price">Price (₹)</label>
                                        <input type="number" step="0.01" min="0" id="extra_charge_price" class="modern-input-pill" style="height:38px; font-size:0.85rem; padding:0 10px;" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>" oninput="onExtraQtyOrPriceChange()">
                                    </div>
                                    <div>
                                        <label class="modern-form-label" style="font-size:0.75rem; color:#64748B; margin-bottom:3px; font-weight:700;" for="extra_charge_total">Total Amount (₹)</label>
                                        <input type="number" step="0.01" min="0" id="extra_charge_total" class="modern-input-pill" style="height:38px; font-size:0.85rem; padding:0 10px; font-weight:700; color:#0C2B52;" placeholder="<?= e(__('placeholder.0_00', '0.00')) ?>">
                                    </div>
                                    <div>
                                        <button type="button" id="btnSubmitExtraCharge" class="modern-btn-next" style="height:38px; padding:0 16px; font-size:0.85rem; border-radius:10px; margin-left:0; white-space:nowrap;" onclick="submitExtraCharge()">
                                            <i class="fa-solid fa-plus"></i> <span id="btnSubmitExtraText">Add Charge</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- If empty: shows No extra charges added. -->
                            <div id="noExtraChargesNotice" style="padding:1rem; text-align:center; color:#64748B; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:12px; font-size:0.86rem; margin-bottom:0.85rem;">
                                <i class="fa-regular fa-clipboard" style="margin-right:6px; color:#94A3B8;"></i> No extra charges added.
                            </div>

                            <!-- Dynamic Charges Table -->
                            <div id="extraChargesTableWrapper" style="display:none; margin-bottom:0.85rem; overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:0.84rem;">
                                    <thead>
                                        <tr style="background:#F1F5F9; border-bottom:1px solid #CBD5E1; color:#334155;">
                                            <th style="padding:8px 12px; text-align:left; font-weight:700;"><?= e(__('booking.charge_name', 'Charge Name')) ?></th>
                                            <th style="padding:8px 12px; text-align:center; font-weight:700; width:75px;"><?= e(__('booking.quantity', 'Qty')) ?></th>
                                            <th style="padding:8px 12px; text-align:right; font-weight:700; width:100px;">Price (₹)</th>
                                            <th style="padding:8px 12px; text-align:right; font-weight:700; width:110px;">Total (₹)</th>
                                            <th style="padding:8px 12px; text-align:center; font-weight:700; width:85px;"><?= e(__('common.actions', 'Actions')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="extraChargesTableBody">
                                        <!-- Rendered dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Prominent Total Extra Charges Row -->
                            <div id="extraChargesTotalRow" style="display:none; align-items:center; justify-content:space-between; background:#E8F4F0; border:1px solid #B2D8CF; border-radius:10px; padding:0.6rem 1rem;">
                                <span style="font-size:0.82rem; font-weight:700; color:#176B5B;"><?= e(__('booking.extra_charges', [], 'Total Extra Charges:')) ?></span>
                                <strong id="lblExtraChargesTotalDisplay" style="font-size:0.95rem; font-weight:800; color:#176B5B;">₹ 0.00</strong>
                            </div>
                        </div>

                        <!-- Promotional Discount Box -->
                        <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 16px; padding: 1.25rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <label class="modern-form-label" for="discount_percent" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fa-solid fa-tag" style="color: #176B5B;"></i> Promotional Discount (%)
                                </label>
                                <span id="discountBadge" style="padding: 2px 10px; border-radius: 50px; background: #64748B; color: #FFF; font-size: 0.72rem; font-weight: 700;"><?= e(__('booking.no_discount', [], 'No Discount')) ?></span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="flex: 1; font-size: 0.85rem; color: #64748B;">
                                    Estimated Savings: <strong id="discountDeductionPreview" style="color: #176B5B; font-weight: 700;">- ₹ 0.00</strong>
                                </div>
                                <div style="position: relative; width: 110px;">
                                    <input type="number" step="0.5" min="0" max="100" id="discount_percent" name="discount_percent" class="modern-input-pill" value="0.0" onkeyup="calculateFare(); syncReviewData();" onchange="calculateFare(); syncReviewData();" style="height: 42px; font-weight: 700; text-align: right; padding-right: 2rem; padding-left: 10px; font-size: 0.95rem;">
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748B; font-size: 0.85rem; pointer-events: none;">%</span>
                                </div>
                            </div>

                            <!-- Quick Presets -->
                            <div class="booking-preset-group" style="margin-top: 0.85rem;">
                                <span class="booking-preset-label">Presets:</span>
                                <button type="button" class="booking-preset-pill discount-pill" data-pct="0" onclick="setDiscountPercent(0)">0%</button>
                                <button type="button" class="booking-preset-pill discount-pill" data-pct="5" onclick="setDiscountPercent(5)">5%</button>
                                <button type="button" class="booking-preset-pill discount-pill" data-pct="10" onclick="setDiscountPercent(10)">10%</button>
                                <button type="button" class="booking-preset-pill discount-pill" data-pct="15" onclick="setDiscountPercent(15)">15%</button>
                            </div>
                        </div>

                        <!-- Statutory GST Box -->
                        <div style="background: #E8F4F0; border: 1.5px solid #B2D8CF; border-radius: 16px; padding: 1.25rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <label for="include_gst" style="display: flex; align-items: center; gap: 0.65rem; cursor: pointer; user-select: none; margin-bottom: 0;">
                                    <input type="checkbox" id="include_gst" name="include_gst" value="1" checked onchange="toggleGst(this); syncReviewData();" style="width: 18px; height: 18px; accent-color: #176B5B; cursor: pointer;">
                                    <span style="font-weight: 700; font-size: 0.9rem; color: #0C2B52;"><?= e(__('booking.include_gst', [], 'Apply Statutory GST')) ?></span>
                                </label>
                                <span id="gstStatusBadge" style="padding: 2px 10px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-weight: 700; font-size: 0.72rem; border: 1px solid #B2D8CF;"><?= e(__('booking.gst_applied', [], 'GST Applied')) ?></span>
                            </div>

                            <div id="gstRateRow" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <label class="modern-form-label" for="gst_rate" style="font-size: 0.82rem; color: #125748; margin-bottom: 0;">
                                    Tax Slab (%)
                                </label>
                                <div style="position: relative; width: 110px;">
                                    <input type="number" step="0.5" min="0" max="100" id="gst_rate" name="gst_rate" class="modern-input-pill" value="5.0" onkeyup="calculateFare(); syncReviewData();" onchange="calculateFare(); syncReviewData();" style="height: 42px; font-weight: 700; text-align: right; padding-right: 2rem; padding-left: 10px; font-size: 0.95rem; background: #FFFFFF;">
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748B; font-size: 0.85rem; pointer-events: none;">%</span>
                                </div>
                            </div>

                            <!-- Presets -->
                            <div id="gstPresetsRow" class="booking-preset-group" style="margin-top: 0.85rem;">
                                <span class="booking-preset-label gst-label">Slabs:</span>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="0" onclick="setGstRate(0)"><?= e(__('tax.exempt', '0% (Exempt)')) ?></button>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="5" onclick="setGstRate(5)"><?= e(__('tax.cab_5', '5% (Cab)')) ?></button>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="12" onclick="setGstRate(12)"><?= e(__('tax.bus_12', '12% (Bus)')) ?></button>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="18" onclick="setGstRate(18)"><?= e(__('tax.tour_18', '18% (Tour)')) ?></button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live Breakdown Manifest Receipt -->
                    <div class="fare-receipt-card" style="background: #FFFFFF; border-radius: 18px; padding: 1.5rem; border: 1.5px solid #B2D8CF; box-shadow: 0 10px 30px rgba(23, 107, 91, 0.08); position: sticky; top: 20px;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #EDF3FA;">
                            <span style="width: 36px; height: 36px; border-radius: 10px; background: #E8F4F0; color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                                <i class="fa-solid fa-receipt"></i>
                            </span>
                            <span style="font-weight: 800; font-size: 1.05rem; color: #0C2B52;"><?= e(__('booking.live_fare_manifest', [], 'Live Fare Manifest')) ?></span>
                        </div>

                        <!-- Itemized Lines in Manifest -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; font-size: 0.85rem; color: #64748B;">
                            <span>Distance Charges:</span>
                            <strong id="lblDistCharge" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; font-size: 0.85rem; color: #64748B;">
                            <span>Daily Charges:</span>
                            <strong id="lblDailyCharge" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div id="rowHaltCharge" style="display: none; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; font-size: 0.85rem; color: #64748B;">
                            <span>Halting Charges:</span>
                            <strong id="lblHaltCharge" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div id="rowOtherCharge" style="display: none; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; font-size: 0.85rem; color: #64748B;">
                            <span>Other Charges:</span>
                            <strong id="lblOtherCharge" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; margin-bottom: 0.75rem; font-size: 0.88rem; color: #0C2B52; font-weight:700; border-top: 1px dashed #DCE6F2; padding-top: 0.65rem;">
                            <span>Trip Subtotal:</span>
                            <strong id="lblSubtotal" style="color: #0C2B52; font-size: 1rem;">₹ 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.88rem; color: #64748B;">
                            <span id="lblTaxTitle">Statutory GST (5%):</span>
                            <strong id="lblTax" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.88rem; color: #64748B;">
                            <span style="display: flex; align-items: center; gap: 0.35rem; color: #176B5B; font-weight: 600;">
                                <i class="fa-solid fa-tag"></i>
                                <span id="lblDiscountTitle">Discount (0%):</span>
                            </span>
                            <strong id="lblDiscountAmount" style="color: #176B5B; font-weight: 700;">- ₹ 0.00</strong>
                        </div>

                        <div style="border-top: 1.5px dashed #DCE6F2; padding-top: 1rem; display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.25rem;">
                            <div>
                                <div style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; font-weight: 800;"><?= e(__('booking.grand_total', [], 'Grand Total')) ?></div>
                                <div id="lblGstFootnote" style="font-size: 0.7rem; color: #176B5B; font-weight: 600;"><?= e(__('booking.inclusive_gst_discount', [], 'Inclusive of GST & Discount')) ?></div>
                            </div>
                            <div id="lblTotal" style="font-size: 1.75rem; font-weight: 800; color: #176B5B; letter-spacing: -0.02em;">₹ 0.00</div>
                        </div>

                        <div style="padding: 0.85rem 1rem; border-radius: 12px; background: #F8FAFD; border: 1px solid #E2E8F0; font-size: 0.78rem; color: #64748B; line-height: 1.45;">
                            <i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i> Every reservation generates a unique audit trace token and idempotency signature.
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                        <i class="fa-solid fa-arrow-left"></i> <?= e(__('form.previous', [], 'Previous')) ?>
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(3)">
                        <?= e(__('form.next', [], 'Next')) ?> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 4: Customer Payment (Optional)                                       -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel4" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge" style="background: #E8F4F0; color: #176B5B;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="modern-section-meta" style="flex: 1;">
                        <h2 class="modern-section-title">Customer Payment (Optional)</h2>
                        <p class="modern-section-sub">Has the customer paid any money now? If not, you can skip this step and collect payment later.</p>
                    </div>
                    <span id="livePaymentBadge" class="modern-badge" style="background: #FEF3C7; color: #92400E; font-weight: 700; font-size: 0.75rem; padding: 4px 12px; border-radius: 50px;">Unpaid (₹0.00 Received)</span>
                </div>

                <!-- Two Simple Choices: Advance vs Pay Later -->
                <div class="payment-choice-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                    <!-- Choice A: <?= e(__('booking.advance_paid', [], 'Customer Paid an Advance')) ?> -->
                    <div class="payment-choice-card is-selected" id="choiceCardAdvance" onclick="selectPaymentChoice('advance')">
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                            <div class="payment-choice-radio">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <h4 class="payment-choice-title"><?= e(__('booking.advance_paid', [], 'Customer Paid an Advance')) ?></h4>
                                    <span class="payment-choice-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                                </div>
                                <p class="payment-choice-desc">Record money received now via Cash, UPI, Card, or Bank transfer.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Choice B: <?= e(__('booking.pay_later', [], 'Customer Will Pay Later')) ?> -->
                    <div class="payment-choice-card" id="choiceCardLater" onclick="selectPaymentChoice('later')">
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                            <div class="payment-choice-radio">
                                <i class="fa-regular fa-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <h4 class="payment-choice-title"><?= e(__('booking.pay_later', [], 'Customer Will Pay Later')) ?></h4>
                                    <span class="payment-choice-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                                </div>
                                <p class="payment-choice-desc">Skip payment collection now and create this reservation with zero advance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reassuring Banner when '<?= e(__('booking.pay_later', [], 'Customer Will Pay Later')) ?>' is chosen -->
                <div id="payLaterContainer" style="display: none; background: #FEF3C7; border: 1.5px solid #FCD34D; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; color: #92400E;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <span style="width: 42px; height: 42px; border-radius: 12px; background: #FDE68A; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 800; color: #92400E;">Payment will be collected later.</h4>
                            <p style="margin: 0 0 1rem; font-size: 0.88rem; line-height: 1.5; color: #78350F;">
                                You can safely confirm this booking with ₹0.00 advance. The booking status will be marked <strong>Unpaid</strong>, and an invoice will be generated. You can record payment receipts at any time during or after the trip.
                            </p>
                            <button type="button" class="skip-payment-action-btn" onclick="wizardGoToStep(5)">
                                Continue to Review &amp; Confirm <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Advance Payment Details Container (shown when '<?= e(__('booking.advance_paid', [], 'Customer Paid an Advance')) ?>' is active) -->
                <div id="advancePaymentContainer" style="display: block;">
                    <!-- Live Calculation Ribbon -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #F8FAFD; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1rem 1.25rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Booking Amount</div>
                            <div id="lblPayTotal" style="font-size: 1.15rem; font-weight: 800; color: #0C2B52;">₹ 0.00</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Money Received</div>
                            <div id="lblPayReceived" style="font-size: 1.15rem; font-weight: 800; color: #176B5B;">₹ 0.00</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Remaining Amount</div>
                            <div id="lblPayRemaining" style="font-size: 1.15rem; font-weight: 800; color: #D97706;">₹ 0.00</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Payment Status</div>
                            <div id="lblPayStatus" style="margin-top: 2px;"><span class="modern-badge" style="background: #FEF3C7; color: #92400E; font-weight: 700; font-size: 0.8rem; padding: 3px 10px; border-radius: 50px;">Unpaid</span></div>
                        </div>
                    </div>

                    <!-- Client validation error notice -->
                    <div id="advanceAlertError" style="display: none; margin-bottom: 1rem; padding: 0.75rem 1rem; background: #FEE2E2; border: 1px solid #F87171; border-radius: 10px; color: #991B1B; font-size: 0.85rem; font-weight: 600;">
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.4rem;"></i>
                        Advance payment cannot be greater than the booking amount.
                    </div>

                    <!-- Advance Inputs Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; align-items: start;">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="advance_amount">
                                Advance Amount Received (₹) <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                <input type="number" step="0.01" min="0" id="advance_amount" name="advance_amount" class="modern-input-pill" value="0.00" onkeyup="calculateAdvance(); syncReviewData();" onchange="calculateAdvance(); syncReviewData();" style="font-weight: 700; font-size: 1.05rem; color: #176B5B;">
                            </div>
                            <div style="margin-top: 0.35rem; font-size: 0.78rem; color: #64748B;">Enter the actual advance amount received from the customer.</div>

                            <!-- Quick Presets -->
                            <div class="booking-preset-group" style="margin-top: 0.65rem;">
                                <span class="booking-preset-label">Presets:</span>
                                <button type="button" class="booking-preset-pill adv-pill is-active" data-ratio="0" onclick="setAdvancePreset(0)"><?= e(__('fare.zero_later', '₹0 (Later)')) ?></button>
                                <button type="button" class="booking-preset-pill adv-pill" data-ratio="0.25" onclick="setAdvancePreset(0.25)">25%</button>
                                <button type="button" class="booking-preset-pill adv-pill" data-ratio="0.50" onclick="setAdvancePreset(0.50)">50%</button>
                                <button type="button" class="booking-preset-pill adv-pill" data-ratio="1.00" onclick="setAdvancePreset(1.00)"><?= e(__('fare.full_100', '100% (Full)')) ?></button>
                            </div>
                        </div>

                        <!-- Advance Payment Details Section -->
                        <div id="advanceDetailsSection" style="display: flex; flex-direction: column; gap: 1rem; transition: opacity 0.2s ease;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="payment_method">Payment Method</label>
                                    <select id="payment_method" name="payment_method" class="modern-input-pill" onchange="syncReviewData()">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                                        <option value="bank_transfer">Bank Transfer / NEFT / RTGS</option>
                                        <option value="card">Credit / Debit Card</option>
                                    </select>
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="received_date">Payment Date</label>
                                    <input type="date" id="received_date" name="received_date" class="modern-input-pill" value="<?= date('Y-m-d') ?>" onchange="syncReviewData()">
                                </div>
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="account_id">Received Into Account (Cash / Bank)</label>
                                <select id="account_id" name="account_id" class="modern-input-pill" onchange="syncReviewData()">
                                    <?php if (!empty($accounts)): ?>
                                        <?php foreach ($accounts as $acc): ?>
                                            <option value="<?= e($acc['id']) ?>">
                                                <?= e($acc['account_name']) ?> (<?= e(ucfirst(str_replace('_', ' ', $acc['account_type'] ?? 'bank'))) ?> - Bal: <?= format_currency((float)($acc['current_balance'] ?? 0)) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value=""><?= e(__('booking.default_business_account', [], 'Default Business Account')) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="transaction_id">Ref / UTR / Cheque (Optional)</label>
                                    <input type="text" id="transaction_id" name="transaction_id" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_upi_987654_or_cheque', 'e.g. UPI-987654 or Cheque #')) ?>" onchange="syncReviewData()">
                                </div>
                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="payment_notes">Notes / Remarks (Optional)</label>
                                    <input type="text" id="payment_notes" name="payment_notes" class="modern-input-pill" placeholder="<?= e(__('placeholder.e_g_booking_advance_received', 'e.g. Booking advance received')) ?>" onchange="syncReviewData()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Navigation Actions for Step 4 -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(3)">
                        <i class="fa-solid fa-arrow-left"></i> <?= e(__('form.previous', [], 'Previous')) ?>
                    </button>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-left: auto;">
                        <button type="button" class="modern-btn-cancel" id="btnSkipPayment" onclick="skipPaymentToReview()">
                            Skip Payment <i class="fa-solid fa-forward-step"></i>
                        </button>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(4)">
                            <?= e(__('form.next', [], 'Next')) ?> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 5: Review & Confirm                                                  -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel5" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge" style="background: #E8F8F0; color: #10B981;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title"><?= e(__('booking.review_confirm', [], 'Review & Confirm')) ?></h2>
                        <p class="modern-section-sub">Comprehensive reservation dossier verification prior to confirming passenger booking.</p>
                    </div>
                </div>

                <!-- Live Executive Dossier Container -->
                <div class="review-dossier-wrapper">

                    <!-- Reservation Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container">
                            <div class="review-avatar-monogram">
                                <i class="fa-solid fa-ticket" style="font-size: 2.2rem; color: #176B5B;"></i>
                            </div>
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroCustomerName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-route"></i>
                                    <code id="sumHeroRoute">—</code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-users"></i> <span id="sumTagPassengers">2 Passengers</span>
                                </span>
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-calendar-days"></i> <span id="sumTagDates">—</span>
                                </span>
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-indian-rupee-sign"></i> <span id="sumTagTotal">₹ 0.00</span>
                                </span>
                                <span class="review-status-pill is-active">
                                    <i class="fa-solid fa-circle-check"></i> Ready to Reserve
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Comprehensive Information Section Cards Grid -->
                    <div class="review-sections-grid">

                        <!-- Card 1: Client Information -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-user-tag"></i> Client Details</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(1)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('customer_form.full_name', [], 'Guest Legal Name')) ?></span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardClientName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('customer_form.customer_code', [], 'Client Code')) ?></span>
                                    <span class="review-value font-mono" id="sumCardClientCode">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('customer.alternate_reachability', [], 'Contact Reachability')) ?></span>
                                    <span class="review-value" id="sumCardClientContact">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.passenger_headcount', [], 'Passenger Headcount')) ?></span>
                                    <span class="review-value font-bold" id="sumCardPassengers">2 Guests</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Itinerary & Schedule -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-route"></i> Itinerary &amp; Schedule</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(2)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.trip_category', [], 'Trip Category')) ?></span>
                                    <span class="review-value font-semibold" id="sumCardTripCategory" style="color:var(--brand-primary, #176B5B);">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.pickup_address', [], 'Pickup Hub')) ?></span>
                                    <span class="review-value font-semibold" id="sumCardPickup">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.drop_address', [], 'Destination Drop')) ?></span>
                                    <span class="review-value font-semibold" id="sumCardDrop">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.journey_timetable', [], 'Journey Timetable')) ?></span>
                                    <span class="review-value" id="sumCardJourneyDates">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.pickup_time', [], 'Pickup Time')) ?></span>
                                    <span class="review-value font-mono font-bold" id="sumCardPickupTime">09:00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.special_remarks', [], 'Special Remarks')) ?></span>
                                    <span class="review-value" id="sumCardSpecialRequests">Standard dispatch</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Fare & Trip Charges Breakdown -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-calculator"></i> Fare &amp; Trip Charges</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(3)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.distance_charges', [], 'Distance Charges')) ?></span>
                                    <span class="review-value font-bold" id="sumCardDistFare">₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.daily_charges', [], 'Daily Charges')) ?></span>
                                    <span class="review-value font-bold" id="sumCardDailyFare">₹ 0.00</span>
                                </div>
                                <div class="review-info-row" id="sumCardHaltRow" style="display: none;">
                                    <span class="review-label"><?= e(__('booking.halting_charges', [], 'Halting Charges')) ?></span>
                                    <span class="review-value font-bold" id="sumCardHaltFare">₹ 0.00</span>
                                </div>
                                <!-- Extra charges section -->
                                <div id="sumCardExtraChargesWrapper" style="display: none; flex-direction: column; gap: 4px; border-top: 1px dashed #E2E8F0; padding-top: 0.4rem; margin-top: 0.2rem;">
                                    <div style="font-size: 0.78rem; font-weight: 700; color: #0C2B52;">Itemized Extra Charges:</div>
                                    <div id="sumCardExtraChargesList" style="display: flex; flex-direction: column; gap: 3px; padding-left: 6px; border-left: 2px solid #B2D8CF;">
                                        <!-- Rendered dynamically -->
                                    </div>
                                </div>
                                <div class="review-info-row" id="sumCardOtherRow" style="display: none;">
                                    <span class="review-label"><?= e(__('booking.extra_charges', [], 'Total Extra Charges')) ?></span>
                                    <span class="review-value font-bold" id="sumCardOtherFare">₹ 0.00</span>
                                </div>
                                <div class="review-info-row" style="border-top: 1px dashed #DCE6F2; padding-top: 0.4rem; margin-top: 0.4rem;">
                                    <span class="review-label"><?= e(__('booking.base_tour_subtotal', [], 'Trip Subtotal')) ?></span>
                                    <span class="review-value font-bold" id="sumCardBaseFare">₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.promotional_discount', [], 'Promotional Discount')) ?></span>
                                    <span class="review-value" style="color: #176B5B;" id="sumCardDiscount">- ₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label"><?= e(__('booking.include_gst', [], 'Statutory GST')) ?></span>
                                    <span class="review-value" id="sumCardGst">+ ₹ 0.00</span>
                                </div>
                                <div class="review-info-row" style="border-top: 1.5px solid #E2E8F0; padding-top: 0.5rem; margin-top: 0.3rem;">
                                    <span class="review-label" style="font-weight: 800; color: #0C2B52;"><?= e(__('booking.grand_total', [], 'Grand Total')) ?></span>
                                    <span class="review-value font-bold highlight-teal" style="font-size: 1.15rem;" id="sumCardGrandTotal">₹ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Payment & Settlement Details -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-wallet"></i> Payment Details</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(4)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <!-- Reassuring Banner if Payment Skipped / Later -->
                                <div id="sumCardPayLaterBanner" style="display: none; background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 10px; padding: 0.75rem 0.9rem; color: #92400E; font-size: 0.82rem; align-items: flex-start; gap: 8px;">
                                    <i class="fa-solid fa-clock" style="font-size: 1.1rem; color: #D97706; margin-top: 2px;"></i>
                                    <div>
                                        <strong style="display: block; margin-bottom: 2px; color: #92400E;">Payment will be collected later.</strong>
                                        <span style="font-size: 0.76rem; color: #78350F; line-height: 1.4;">Zero advance collected now. Full balance of <span id="sumCardPendingTotal" style="font-weight: 700;">₹ 0.00</span> will be billed after dispatch.</span>
                                    </div>
                                </div>

                                <div class="review-info-row">
                                    <span class="review-label">Money Received</span>
                                    <span class="review-value font-bold" style="color: #176B5B;" id="sumCardAdvance">₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Remaining Balance</span>
                                    <span class="review-value font-bold" style="color: #D97706;" id="sumCardRemaining">₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Payment Status</span>
                                    <span class="review-value" id="sumCardPaymentStatus"><span class="modern-badge" style="background:#FEF3C7; color:#92400E; font-size: 0.75rem; padding: 2px 8px; border-radius: 50px;">Unpaid</span></span>
                                </div>
                                <div class="review-info-row" id="sumCardMethodRow" style="display: none;">
                                    <span class="review-label">Payment Method</span>
                                    <span class="review-value capitalize" id="sumCardMethod">—</span>
                                </div>
                                <div class="review-info-row" id="sumCardAccountRow" style="display: none;">
                                    <span class="review-label">Deposited Account</span>
                                    <span class="review-value" id="sumCardAccount">—</span>
                                </div>
                                <div class="review-info-row" id="sumCardRefRow" style="display: none;">
                                    <span class="review-label">Reference / UTR</span>
                                    <span class="review-value font-mono" id="sumCardRef">—</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Actions for Step 5 -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(4)">
                        <i class="fa-solid fa-arrow-left"></i> <?= e(__('form.previous', [], 'Previous')) ?>
                    </button>
                    <button type="submit" class="modern-btn-submit" id="btnSubmitBooking">
                        <i class="fa-solid fa-check"></i> <span id="btnSubmitBookingText">Confirm &amp; Create Reservation</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
// Wizard State & Navigation (5-Step Streamlined Process matching customers/create)
let currentStep = 1;
const totalSteps = 5;

function updateStepperUI(activeStep) {
    currentStep = activeStep;

    // Update Step items
    const stepButtons = document.querySelectorAll('.modern-step-item');
    stepButtons.forEach((btn, index) => {
        const stepNum = index + 1;
        btn.classList.remove('active', 'completed');
        if (stepNum === activeStep) {
            btn.classList.add('active');
        } else if (stepNum < activeStep) {
            btn.classList.add('completed');
        }
    });

    // Update connector lines
    for (let i = 1; i < totalSteps; i++) {
        const conn = document.getElementById('stepConn' + i);
        if (conn) {
            if (i < activeStep) {
                conn.classList.add('completed');
            } else {
                conn.classList.remove('completed');
            }
        }
    }

    // When on step 5, populate the comprehensive live summary dossier
    if (activeStep === 5) {
        populateSummaryDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target panel
    const targetPanel = document.getElementById('wizardPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateStepperUI(targetStep);

    // Scroll to top smoothly
    const wrapper = document.querySelector('.modern-form-wrapper');
    if (wrapper) {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(stepNum) {
    const panel = document.getElementById('wizardPanel' + stepNum);
    if (!panel) return true;

    if (window.VoyageValidation && typeof window.VoyageValidation.validateContainer === 'function') {
        const isValid = window.VoyageValidation.validateContainer(panel);
        if (!isValid) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Please fill in all required fields marked with *', 'error');
            }
            return false;
        }
        return true;
    }

    const requiredFields = panel.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    let firstInvalid = null;

    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            isValid = false;
            field.style.borderColor = '#EF4444';
            field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
            if (!firstInvalid) firstInvalid = field;

            const onInput = function() {
                if (field.value.trim() !== '') {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                    field.removeEventListener('input', onInput);
                    field.removeEventListener('change', onInput);
                }
            };
            field.addEventListener('input', onInput);
            field.addEventListener('change', onInput);
        } else {
            field.style.borderColor = '';
            field.style.boxShadow = '';
        }
    });

    if (!isValid && firstInvalid) {
        firstInvalid.focus();
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please fill in all required fields marked with *', 'error');
        }
        return false;
    }

    // Step 4: Validate Customer Payment
    if (stepNum === 4) {
        const total = getGrandTotal();
        const advInput = document.getElementById('advance_amount');
        const adv = parseFloat(advInput?.value || 0) || 0;

        if (adv < 0) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Advance payment cannot be a negative amount.', 'error');
            } else {
                alert('Advance payment cannot be a negative amount.');
            }
            if (advInput) {
                advInput.focus();
                advInput.style.borderColor = '#EF4444';
            }
            return false;
        }

        if (adv > total + 0.001) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Advance payment cannot be greater than the booking amount.', 'error');
            } else {
                alert('Advance payment cannot be greater than the booking amount.');
            }
            if (advInput) {
                advInput.focus();
                advInput.style.borderColor = '#EF4444';
            }
            return false;
        }
    }

    return true;
}

function wizardNextStep(currentStepNum) {
    if (!wizardValidateCurrentStep(currentStepNum)) {
        return;
    }
    wizardGoToStep(currentStepNum + 1);
}

// Client Selection Handler
function onCustomerSelect(select) {
    const opt = select.options[select.selectedIndex];
    const info = document.getElementById('customerSelectedInfo');
    if (!opt || !opt.value) {
        if (info) info.style.display = 'none';
        return;
    }
    const name = opt.getAttribute('data-name') || '';
    const code = opt.getAttribute('data-code') || '';
    const phone = opt.getAttribute('data-phone') || '';

    const nameEl = document.getElementById('custSelectedName');
    const codeEl = document.getElementById('custSelectedCode');
    const phoneEl = document.getElementById('custSelectedPhone');

    if (nameEl) nameEl.textContent = name;
    if (codeEl) codeEl.textContent = code ? 'Code: ' + code : '';
    if (phoneEl) phoneEl.textContent = phone ? 'Contact: ' + phone : '';
    if (info) info.style.display = 'block';
}

// Live Summary Dossier Synchronizer for Step 5
function populateSummaryDossier() {
    const custSelect = document.getElementById('customer_id');
    let clientName = '—';
    let clientCode = '—';
    let clientContact = '—';

    if (custSelect) {
        if (custSelect.tagName === 'SELECT') {
            const opt = custSelect.options[custSelect.selectedIndex];
            if (opt && opt.value) {
                clientName = opt.getAttribute('data-name') || opt.text;
                clientCode = opt.getAttribute('data-code') || '—';
                clientContact = opt.getAttribute('data-phone') || '—';
            }
        } else if (custSelect.tagName === 'INPUT') {
            clientName = <?= json_encode(auth_user()['name'] ?? 'Client') ?>;
            clientCode = 'CLIENT-PORTAL';
            clientContact = <?= json_encode(auth_user()['phone'] ?? auth_user()['email'] ?? 'Verified') ?>;
        }
    }

    const passengers = (document.getElementById('passenger_count')?.value || '2').trim();
    const pickup = (document.getElementById('pickup_address')?.value || '').trim();
    const drop = (document.getElementById('drop_address')?.value || '').trim();
    const startDate = document.getElementById('start_date')?.value || '';
    const endDate = document.getElementById('end_date')?.value || '';
    const pickupTime = document.getElementById('pickup_time')?.value || '09:00';
    const requests = (document.getElementById('special_requests')?.value || '').trim();

    const subtotalText = document.getElementById('lblSubtotal')?.innerText || '₹ 0.00';
    const taxText = document.getElementById('lblTax')?.innerText || '₹ 0.00';
    const discountText = document.getElementById('lblDiscountAmount')?.innerText || '- ₹ 0.00';
    const totalText = document.getElementById('lblTotal')?.innerText || '₹ 0.00';

    // Hero banner
    const heroNameEl = document.getElementById('sumHeroCustomerName');
    if (heroNameEl) heroNameEl.textContent = clientName !== '—' ? clientName : 'Guest Reservation';

    const heroRouteEl = document.getElementById('sumHeroRoute');
    if (heroRouteEl) {
        if (pickup && drop) {
            heroRouteEl.textContent = (pickup.split(',')[0].trim()) + ' ➔ ' + (drop.split(',')[0].trim());
        } else {
            heroRouteEl.textContent = 'Custom Itinerary Route';
        }
    }

    const tagPassEl = document.getElementById('sumTagPassengers');
    if (tagPassEl) tagPassEl.textContent = passengers + (parseInt(passengers, 10) === 1 ? ' Passenger' : ' Passengers');

    const tagDatesEl = document.getElementById('sumTagDates');
    if (tagDatesEl) {
        if (startDate && endDate) {
            tagDatesEl.textContent = formatDisplayDate(startDate) + ' to ' + formatDisplayDate(endDate);
        } else if (startDate) {
            tagDatesEl.textContent = formatDisplayDate(startDate);
        } else {
            tagDatesEl.textContent = 'Dates Pending';
        }
    }

    const tagTotalEl = document.getElementById('sumTagTotal');
    if (tagTotalEl) tagTotalEl.textContent = totalText;

    // Card 1: Client Details
    const cardClientNameEl = document.getElementById('sumCardClientName');
    if (cardClientNameEl) cardClientNameEl.textContent = clientName;

    const cardClientCodeEl = document.getElementById('sumCardClientCode');
    if (cardClientCodeEl) cardClientCodeEl.textContent = clientCode;

    const cardClientContactEl = document.getElementById('sumCardClientContact');
    if (cardClientContactEl) cardClientContactEl.textContent = clientContact;

    const cardPassengersEl = document.getElementById('sumCardPassengers');
    if (cardPassengersEl) cardPassengersEl.textContent = passengers + ' Guests';

    // Card 2: Itinerary & Schedule
    const cardCatEl = document.getElementById('sumCardTripCategory');
    const catSelect = document.getElementById('trip_category_id');
    if (cardCatEl && catSelect) {
        cardCatEl.textContent = (catSelect.selectedIndex > 0) ? catSelect.options[catSelect.selectedIndex].text : '—';
    }

    const cardPickupEl = document.getElementById('sumCardPickup');
    if (cardPickupEl) cardPickupEl.textContent = pickup || '—';

    const cardDropEl = document.getElementById('sumCardDrop');
    if (cardDropEl) cardDropEl.textContent = drop || '—';

    const cardDatesEl = document.getElementById('sumCardJourneyDates');
    if (cardDatesEl) {
        if (startDate && endDate) {
            cardDatesEl.textContent = formatDisplayDate(startDate) + ' – ' + formatDisplayDate(endDate);
        } else {
            cardDatesEl.textContent = '—';
        }
    }

    const cardTimeEl = document.getElementById('sumCardPickupTime');
    if (cardTimeEl) cardTimeEl.textContent = pickupTime || '09:00';

    const cardReqEl = document.getElementById('sumCardSpecialRequests');
    if (cardReqEl) cardReqEl.textContent = requests || 'None specified';

    // Card 3: Fare & Trip Charges (Itemized Breakdown)
    const distAmt = parseFloat(document.getElementById('billing_distance_amount')?.value || 0) || 0;
    const distKm = parseFloat(document.getElementById('billing_distance_km')?.value || 0) || 0;
    const dailyAmt = parseFloat(document.getElementById('billing_daily_amount')?.value || 0) || 0;
    const dailyDays = parseFloat(document.getElementById('billing_daily_days')?.value || 0) || 0;
    const haltAmt = parseFloat(document.getElementById('billing_halting_amount')?.value || 0) || 0;
    const extraSum = extraChargesList.reduce((acc, x) => acc + (parseFloat(x.total || 0) || 0), 0);

    const cardDistEl = document.getElementById('sumCardDistFare');
    if (cardDistEl) cardDistEl.textContent = '₹ ' + distAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + (distKm > 0 ? ' (' + distKm + ' KM)' : '');

    const cardDailyEl = document.getElementById('sumCardDailyFare');
    if (cardDailyEl) cardDailyEl.textContent = '₹ ' + dailyAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + (dailyDays > 0 ? ' (' + dailyDays + 'D)' : '');

    const cardHaltRow = document.getElementById('sumCardHaltRow');
    const cardHaltEl = document.getElementById('sumCardHaltFare');
    if (cardHaltRow && cardHaltEl) {
        if (haltAmt > 0) {
            cardHaltRow.style.display = 'flex';
            cardHaltEl.textContent = '₹ ' + haltAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            cardHaltRow.style.display = 'none';
        }
    }

    // Card 3: Itemized Extra Charges
    const cardExtraWrapper = document.getElementById('sumCardExtraChargesWrapper');
    const cardExtraList = document.getElementById('sumCardExtraChargesList');
    const cardOtherRow = document.getElementById('sumCardOtherRow');
    const cardOtherEl = document.getElementById('sumCardOtherFare');

    if (extraChargesList.length > 0) {
        if (cardExtraWrapper) cardExtraWrapper.style.display = 'flex';
        if (cardOtherRow) cardOtherRow.style.display = 'flex';
        if (cardOtherEl) cardOtherEl.textContent = '₹ ' + extraSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (cardExtraList) {
            cardExtraList.innerHTML = extraChargesList.map(item => {
                const tot = parseFloat(item.total || 0) || 0;
                const priceFormatted = item.price !== null && item.price !== '' ? '₹ ' + parseFloat(item.price).toFixed(2) : '';
                const qtyText = item.quantity > 1 ? `${item.quantity} × ${priceFormatted}` : priceFormatted;
                return `
                    <div style="display:flex; justify-content:space-between; align-items:baseline; font-size:0.78rem; color:#475569;">
                        <span>• ${escapeHtml(item.name)}${qtyText ? ' (' + qtyText + ')' : ''}</span>
                        <span style="font-weight:700; color:#0C2B52;">₹ ${tot.toFixed(2)}</span>
                    </div>
                `;
            }).join('');
        }
    } else {
        if (cardExtraWrapper) cardExtraWrapper.style.display = 'none';
        if (cardOtherRow) cardOtherRow.style.display = 'none';
        if (cardExtraList) cardExtraList.innerHTML = '';
    }

    const cardBaseFareEl = document.getElementById('sumCardBaseFare');
    if (cardBaseFareEl) cardBaseFareEl.textContent = subtotalText;

    const cardDiscountEl = document.getElementById('sumCardDiscount');
    if (cardDiscountEl) cardDiscountEl.textContent = discountText;

    const cardGstEl = document.getElementById('sumCardGst');
    if (cardGstEl) cardGstEl.textContent = taxText;

    const cardTotalEl = document.getElementById('sumCardGrandTotal');
    if (cardTotalEl) cardTotalEl.textContent = totalText;

    // Card 4: Payment Summary
    const advInput = document.getElementById('advance_amount');
    const advanceVal = parseFloat(advInput ? advInput.value : 0) || 0;
    const cardAdvanceEl = document.getElementById('sumCardAdvance');
    if (cardAdvanceEl) cardAdvanceEl.textContent = '₹ ' + advanceVal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const totalNum = parseFloat((totalText.replace(/[^\d.]/g, '') || 0));
    const remainingVal = Math.max(0, Math.round((totalNum - advanceVal) * 100) / 100);
    const cardRemEl = document.getElementById('sumCardRemaining');
    if (cardRemEl) cardRemEl.textContent = '₹ ' + remainingVal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const cardStatusEl = document.getElementById('sumCardPaymentStatus');
    if (cardStatusEl) {
        if (advanceVal <= 0.001) {
            cardStatusEl.innerHTML = '<span class="modern-badge" style="background:#FEF3C7; color:#92400E; font-size:0.75rem; padding:2px 8px; border-radius:50px;">Unpaid</span>';
        } else if (remainingVal <= 0.001 && totalNum > 0) {
            cardStatusEl.innerHTML = '<span class="modern-badge" style="background:#D1FAE5; color:#065F46; font-size:0.75rem; padding:2px 8px; border-radius:50px;">Paid</span>';
        } else {
            cardStatusEl.innerHTML = '<span class="modern-badge" style="background:#DBEAFE; color:#1E40AF; font-size:0.75rem; padding:2px 8px; border-radius:50px;">Partially Paid</span>';
        }
    }

    const payLaterBanner = document.getElementById('sumCardPayLaterBanner');
    const pendingTotalEl = document.getElementById('sumCardPendingTotal');
    const methodRow = document.getElementById('sumCardMethodRow');
    const methodEl = document.getElementById('sumCardMethod');
    const cardAccRow = document.getElementById('sumCardAccountRow');
    const cardAccEl = document.getElementById('sumCardAccount');
    const refRow = document.getElementById('sumCardRefRow');
    const refEl = document.getElementById('sumCardRef');

    if (advanceVal <= 0.001) {
        if (payLaterBanner) payLaterBanner.style.display = 'flex';
        if (pendingTotalEl) pendingTotalEl.textContent = totalText;
        if (methodRow) methodRow.style.display = 'none';
        if (cardAccRow) cardAccRow.style.display = 'none';
        if (refRow) refRow.style.display = 'none';
    } else {
        if (payLaterBanner) payLaterBanner.style.display = 'none';

        // Payment method
        const methodSelect = document.getElementById('payment_method');
        if (methodRow && methodEl && methodSelect) {
            methodEl.textContent = methodSelect.options[methodSelect.selectedIndex]?.text || 'Cash';
            methodRow.style.display = 'flex';
        }

        // Account
        const accSelect = document.getElementById('account_id');
        if (cardAccRow && cardAccEl && accSelect) {
            const selOpt = accSelect.options[accSelect.selectedIndex];
            cardAccEl.textContent = selOpt ? selOpt.textContent.trim() : 'Default Business Account';
            cardAccRow.style.display = 'flex';
        }

        // Reference / UTR
        const refVal = (document.getElementById('transaction_id')?.value || '').trim();
        if (refRow && refEl) {
            if (refVal) {
                refEl.textContent = refVal;
                refRow.style.display = 'flex';
            } else {
                refRow.style.display = 'none';
            }
        }
    }
}

function syncReviewData() {
    if (currentStep === 5) {
        populateSummaryDossier();
    }
}

function formatDisplayDate(dStr) {
    if (!dStr) return '—';
    try {
        const parts = dStr.split('-');
        if (parts.length === 3) {
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    } catch (e) {}
    return dStr;
}

// =========================================================================
// Itemized Trip Billing Engine
// =========================================================================
let extraChargesList = [];
let editingExtraId = null;

function onDistanceKmOrRateChanged() {
    const km = parseFloat(document.getElementById('billing_distance_km')?.value || 0) || 0;
    const rateVal = document.getElementById('billing_rate_per_km')?.value;
    const rate = (rateVal !== '' && rateVal !== null && !isNaN(parseFloat(rateVal))) ? parseFloat(rateVal) : null;
    const amtInput = document.getElementById('billing_distance_amount');
    if (km > 0 && rate !== null && rate > 0 && amtInput) {
        amtInput.value = (Math.round(km * rate * 100) / 100).toFixed(2);
    }
    syncItemizedBilling();
}

function onDistanceAmountDirectChanged() {
    syncItemizedBilling();
}

function onDailyDaysOrRateChanged() {
    const days = parseFloat(document.getElementById('billing_daily_days')?.value || 0) || 0;
    const rateVal = document.getElementById('billing_rate_per_day')?.value;
    const rate = (rateVal !== '' && rateVal !== null && !isNaN(parseFloat(rateVal))) ? parseFloat(rateVal) : null;
    const amtInput = document.getElementById('billing_daily_amount');
    if (days > 0 && rate !== null && rate > 0 && amtInput) {
        amtInput.value = (Math.round(days * rate * 100) / 100).toFixed(2);
    }
    syncItemizedBilling();
}

function onDailyAmountDirectChanged() {
    syncItemizedBilling();
}

function onHaltingDaysOrRateChanged() {
    const days = parseFloat(document.getElementById('billing_halting_days')?.value || 0) || 0;
    const rateVal = document.getElementById('billing_halting_rate')?.value;
    const rate = (rateVal !== '' && rateVal !== null && !isNaN(parseFloat(rateVal))) ? parseFloat(rateVal) : null;
    const amtInput = document.getElementById('billing_halting_amount');
    if (days > 0 && rate !== null && rate > 0 && amtInput) {
        amtInput.value = (Math.round(days * rate * 100) / 100).toFixed(2);
    }
    syncItemizedBilling();
}

function onHaltingAmountDirectChanged() {
    syncItemizedBilling();
}

// Extra Charges Helpers
function selectQuickExtraOption(optionName) {
    document.querySelectorAll('.extra-opt-pill').forEach(btn => btn.classList.remove('is-active'));

    const nameInput = document.getElementById('extra_charge_name');
    const qtyInput = document.getElementById('extra_charge_qty');
    const priceInput = document.getElementById('extra_charge_price');
    const totalInput = document.getElementById('extra_charge_total');

    if (optionName === 'Other') {
        if (nameInput) nameInput.value = '';
        if (qtyInput) qtyInput.value = '1';
        if (priceInput) priceInput.value = '';
        if (totalInput) totalInput.value = '';
        nameInput?.focus();
    } else {
        if (nameInput) nameInput.value = optionName;
        if (qtyInput) qtyInput.value = '1';
        if (priceInput) priceInput.value = '';
        if (totalInput) totalInput.value = '';
        priceInput?.focus();
    }

    if (window.event && window.event.currentTarget) {
        window.event.currentTarget.classList.add('is-active');
    }
}

function onExtraQtyOrPriceChange() {
    const qty = Math.max(1, parseFloat(document.getElementById('extra_charge_qty')?.value || 1) || 1);
    const priceVal = document.getElementById('extra_charge_price')?.value;
    const price = (priceVal !== '' && priceVal !== null && !isNaN(parseFloat(priceVal))) ? parseFloat(priceVal) : null;
    const totalInput = document.getElementById('extra_charge_total');

    if (price !== null && price >= 0 && totalInput) {
        totalInput.value = (Math.round(qty * price * 100) / 100).toFixed(2);
    }
}

function submitExtraCharge() {
    const nameInput = document.getElementById('extra_charge_name');
    const qtyInput = document.getElementById('extra_charge_qty');
    const priceInput = document.getElementById('extra_charge_price');
    const totalInput = document.getElementById('extra_charge_total');

    const name = (nameInput?.value || '').trim();
    if (!name) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please enter a charge name.', 'error');
        } else {
            alert('Please enter a charge name.');
        }
        nameInput?.focus();
        return;
    }

    const qty = Math.max(1, parseFloat(qtyInput?.value || 1) || 1);
    const priceVal = priceInput?.value;
    const price = (priceVal !== '' && priceVal !== null && !isNaN(parseFloat(priceVal))) ? parseFloat(priceVal) : null;
    let total = parseFloat(totalInput?.value || 0) || 0;

    if (total <= 0 && price !== null && price > 0) {
        total = Math.round(qty * price * 100) / 100;
        if (totalInput) totalInput.value = total.toFixed(2);
    }

    if (total <= 0) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please enter a valid price or total amount greater than ₹0.', 'error');
        } else {
            alert('Please enter a valid price or total amount greater than ₹0.');
        }
        if (priceInput && (!priceVal || parseFloat(priceVal) <= 0)) {
            priceInput.focus();
        } else {
            totalInput?.focus();
        }
        return;
    }

    if (editingExtraId) {
        // Edit mode
        const idx = extraChargesList.findIndex(item => item.id === editingExtraId);
        if (idx !== -1) {
            extraChargesList[idx].name = name;
            extraChargesList[idx].quantity = qty;
            extraChargesList[idx].price = price;
            extraChargesList[idx].total = total;
        }
        cancelEditExtraCharge();
    } else {
        // Add mode
        extraChargesList.push({
            id: 'extra_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
            name: name,
            quantity: qty,
            price: price,
            total: total
        });

        // Reset inputs
        if (nameInput) nameInput.value = '';
        if (qtyInput) qtyInput.value = '1';
        if (priceInput) priceInput.value = '';
        if (totalInput) totalInput.value = '';
        document.querySelectorAll('.extra-opt-pill').forEach(btn => btn.classList.remove('is-active'));
    }

    renderExtraChargesTable();
    syncItemizedBilling();
}

function editExtraCharge(id) {
    const item = extraChargesList.find(x => x.id === id);
    if (!item) return;

    editingExtraId = id;
    const nameInput = document.getElementById('extra_charge_name');
    const qtyInput = document.getElementById('extra_charge_qty');
    const priceInput = document.getElementById('extra_charge_price');
    const totalInput = document.getElementById('extra_charge_total');
    const btnSubmitText = document.getElementById('btnSubmitExtraText');
    const btnCancel = document.getElementById('btnCancelEditExtra');
    const formTitle = document.getElementById('extraFormModeTitle');

    if (nameInput) nameInput.value = item.name;
    if (qtyInput) qtyInput.value = item.quantity;
    if (priceInput) priceInput.value = item.price !== null ? parseFloat(item.price).toFixed(2) : '';
    if (totalInput) totalInput.value = parseFloat(item.total).toFixed(2);

    if (btnSubmitText) btnSubmitText.textContent = 'Update';
    if (btnCancel) btnCancel.style.display = 'inline-block';
    if (formTitle) formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square" style="color:#D97706;"></i> Edit Extra Charge';

    nameInput?.focus();
}

function cancelEditExtraCharge() {
    editingExtraId = null;
    const nameInput = document.getElementById('extra_charge_name');
    const qtyInput = document.getElementById('extra_charge_qty');
    const priceInput = document.getElementById('extra_charge_price');
    const totalInput = document.getElementById('extra_charge_total');
    const btnSubmitText = document.getElementById('btnSubmitExtraText');
    const btnCancel = document.getElementById('btnCancelEditExtra');
    const formTitle = document.getElementById('extraFormModeTitle');

    if (nameInput) nameInput.value = '';
    if (qtyInput) qtyInput.value = '1';
    if (priceInput) priceInput.value = '';
    if (totalInput) totalInput.value = '';

    if (btnSubmitText) btnSubmitText.textContent = 'Add Charge';
    if (btnCancel) btnCancel.style.display = 'none';
    if (formTitle) formTitle.innerHTML = '<i class="fa-solid fa-circle-plus" style="color:#176B5B;"></i> Add Extra Charge';

    document.querySelectorAll('.extra-opt-pill').forEach(btn => btn.classList.remove('is-active'));
}

function deleteExtraCharge(id) {
    if (editingExtraId === id) {
        cancelEditExtraCharge();
    }
    extraChargesList = extraChargesList.filter(item => item.id !== id);
    renderExtraChargesTable();
    syncItemizedBilling();
}

function renderExtraChargesTable() {
    const wrapper = document.getElementById('extraChargesTableWrapper');
    const tbody = document.getElementById('extraChargesTableBody');
    const notice = document.getElementById('noExtraChargesNotice');
    const totalRow = document.getElementById('extraChargesTotalRow');
    const totalDisplay = document.getElementById('lblExtraChargesTotalDisplay');

    if (extraChargesList.length === 0) {
        if (wrapper) wrapper.style.display = 'none';
        if (totalRow) totalRow.style.display = 'none';
        if (notice) notice.style.display = 'block';
        if (tbody) tbody.innerHTML = '';
        return;
    }

    if (notice) notice.style.display = 'none';
    if (wrapper) wrapper.style.display = 'block';
    if (totalRow) totalRow.style.display = 'flex';

    let sum = 0;
    if (tbody) {
        tbody.innerHTML = extraChargesList.map(item => {
            const tot = parseFloat(item.total || 0) || 0;
            sum += tot;
            const priceText = item.price !== null && item.price !== '' ? '₹ ' + parseFloat(item.price).toFixed(2) : '—';
            return `
                <tr style="border-bottom:1px solid #F1F5F9;">
                    <td style="padding:8px 12px; font-weight:600; color:#0C2B52;">${escapeHtml(item.name)}</td>
                    <td style="padding:8px 12px; text-align:center; color:#64748B;">${item.quantity}</td>
                    <td style="padding:8px 12px; text-align:right; color:#64748B;">${priceText}</td>
                    <td style="padding:8px 12px; text-align:right; font-weight:700; color:#176B5B;">₹ ${tot.toFixed(2)}</td>
                    <td style="padding:8px 12px; text-align:center;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <button type="button" class="extra-charge-btn-edit" onclick="editExtraCharge('${item.id}')" title="<?= e(__('tooltip.edit_charge', 'Edit charge')) ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="extra-charge-btn-del" onclick="deleteExtraCharge('${item.id}')" title="<?= e(__('tooltip.delete_charge', 'Delete charge')) ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    if (totalDisplay) {
        totalDisplay.textContent = '₹ ' + sum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
    });
}

function syncItemizedBilling() {
    let items = [];
    let order = 1;

    // A. Distance
    const distKm = parseFloat(document.getElementById('billing_distance_km')?.value || 0) || 0;
    const distRateVal = document.getElementById('billing_rate_per_km')?.value;
    const distRate = (distRateVal !== '' && distRateVal !== null && !isNaN(parseFloat(distRateVal))) ? parseFloat(distRateVal) : null;
    const distAmount = parseFloat(document.getElementById('billing_distance_amount')?.value || 0) || 0;

    const distBadge = document.getElementById('distBadge');
    if (distBadge) distBadge.innerText = '₹ ' + distAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const lblDistCharge = document.getElementById('lblDistCharge');
    if (lblDistCharge) lblDistCharge.innerText = '₹ ' + distAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + (distKm > 0 ? ' (' + distKm + ' KM)' : '');

    if (distAmount > 0 || distKm > 0) {
        items.push({
            charge_category: 'distance',
            description: 'Distance Charges' + (distKm > 0 ? ' (' + distKm.toFixed(2) + ' KM)' : ''),
            quantity: distKm > 0 ? distKm : 1,
            unit: 'KM',
            unit_rate: distRate,
            line_total: distAmount,
            is_taxable: 1,
            display_order: order++
        });
    }

    // B. Daily
    const dailyDays = parseFloat(document.getElementById('billing_daily_days')?.value || 0) || 0;
    const dailyRateVal = document.getElementById('billing_rate_per_day')?.value;
    const dailyRate = (dailyRateVal !== '' && dailyRateVal !== null && !isNaN(parseFloat(dailyRateVal))) ? parseFloat(dailyRateVal) : null;
    const dailyAmount = parseFloat(document.getElementById('billing_daily_amount')?.value || 0) || 0;

    const dailyBadge = document.getElementById('dailyBadge');
    if (dailyBadge) dailyBadge.innerText = '₹ ' + dailyAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const lblDailyCharge = document.getElementById('lblDailyCharge');
    if (lblDailyCharge) lblDailyCharge.innerText = '₹ ' + dailyAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + (dailyDays > 0 ? ' (' + dailyDays + (dailyDays === 1 ? ' Day' : ' Days') + ')' : '');

    if (dailyAmount > 0 || dailyDays > 0) {
        items.push({
            charge_category: 'daily',
            description: 'Daily Vehicle Charges (' + dailyDays + (dailyDays === 1 ? ' Day' : ' Days') + ')',
            quantity: dailyDays > 0 ? dailyDays : 1,
            unit: 'Days',
            unit_rate: dailyRate,
            line_total: dailyAmount,
            is_taxable: 1,
            display_order: order++
        });
    }

    // C. Halting
    const haltDays = parseFloat(document.getElementById('billing_halting_days')?.value || 0) || 0;
    const haltRateVal = document.getElementById('billing_halting_rate')?.value;
    const haltRate = (haltRateVal !== '' && haltRateVal !== null && !isNaN(parseFloat(haltRateVal))) ? parseFloat(haltRateVal) : null;
    const haltAmount = parseFloat(document.getElementById('billing_halting_amount')?.value || 0) || 0;

    const haltBadge = document.getElementById('haltBadge');
    if (haltBadge) haltBadge.innerText = '₹ ' + haltAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const lblHaltCharge = document.getElementById('lblHaltCharge');
    if (lblHaltCharge) lblHaltCharge.innerText = '₹ ' + haltAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const rowHaltCharge = document.getElementById('rowHaltCharge');
    if (rowHaltCharge) rowHaltCharge.style.display = haltAmount > 0 ? 'flex' : 'none';

    if (haltAmount > 0) {
        items.push({
            charge_category: 'halting',
            description: 'Halting Charges (' + haltDays + (haltDays === 1 ? ' Night' : ' Nights') + ')',
            quantity: haltDays > 0 ? haltDays : 1,
            unit: 'Nights',
            unit_rate: haltRate,
            line_total: haltAmount,
            is_taxable: 1,
            display_order: order++
        });
    }

    // D. Extra charges
    let extraSum = 0;
    extraChargesList.forEach(item => {
        const tot = parseFloat(item.total || 0) || 0;
        extraSum += tot;
        items.push({
            charge_category: 'other',
            description: item.name || 'Extra Charge',
            quantity: item.quantity || 1,
            unit: 'Unit',
            unit_rate: item.price !== null && item.price !== '' ? parseFloat(item.price) : null,
            line_total: tot,
            is_taxable: 1,
            display_order: order++
        });
    });

    const extraBadge = document.getElementById('extraChargesBadge');
    if (extraBadge) extraBadge.innerText = '₹ ' + extraSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const lblOtherCharge = document.getElementById('lblOtherCharge');
    if (lblOtherCharge) lblOtherCharge.innerText = '₹ ' + extraSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const rowOtherCharge = document.getElementById('rowOtherCharge');
    if (rowOtherCharge) rowOtherCharge.style.display = extraSum > 0 ? 'flex' : 'none';

    // Calculate Total Subtotal
    const calculatedSubtotal = Math.round((distAmount + dailyAmount + haltAmount + extraSum) * 100) / 100;

    // Sync hidden subtotal input and json input
    const subtotalInput = document.getElementById('custom_subtotal');
    if (subtotalInput) subtotalInput.value = calculatedSubtotal.toFixed(2);

    const jsonInput = document.getElementById('billing_items_json');
    if (jsonInput) jsonInput.value = JSON.stringify(items);

    calculateFare();
    syncReviewData();
}

// =========================================================================
// Payment Choice Selection Engine (Advance vs Pay Later)
// =========================================================================
let paymentChoice = 'advance';

function selectPaymentChoice(choice) {
    paymentChoice = choice;
    const cardAdv = document.getElementById('choiceCardAdvance');
    const cardLater = document.getElementById('choiceCardLater');
    const containerAdv = document.getElementById('advancePaymentContainer');
    const containerLater = document.getElementById('payLaterContainer');
    const advInput = document.getElementById('advance_amount');

    if (choice === 'later') {
        if (cardAdv) {
            cardAdv.classList.remove('is-selected');
            const radio = cardAdv.querySelector('.payment-choice-radio');
            if (radio) radio.innerHTML = '<i class="fa-regular fa-circle"></i>';
        }
        if (cardLater) {
            cardLater.classList.add('is-selected');
            const radio = cardLater.querySelector('.payment-choice-radio');
            if (radio) radio.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
        }
        if (containerAdv) containerAdv.style.display = 'none';
        if (containerLater) containerLater.style.display = 'block';

        if (advInput) advInput.value = '0.00';
        calculateAdvance();
        syncReviewData();
    } else {
        if (cardLater) {
            cardLater.classList.remove('is-selected');
            const radio = cardLater.querySelector('.payment-choice-radio');
            if (radio) radio.innerHTML = '<i class="fa-regular fa-circle"></i>';
        }
        if (cardAdv) {
            cardAdv.classList.add('is-selected');
            const radio = cardAdv.querySelector('.payment-choice-radio');
            if (radio) radio.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
        }
        if (containerLater) containerLater.style.display = 'none';
        if (containerAdv) containerAdv.style.display = 'block';

        calculateAdvance();
        syncReviewData();
        advInput?.focus();
    }
}

function skipPaymentToReview() {
    selectPaymentChoice('later');
    wizardGoToStep(5);
}

// Fare Calculation Engine
const VG_I18N = {
    gstApplied: '<?= e(__('booking.gst_applied', [], 'GST Applied')) ?>',
    gstExempt: 'Exempt',
    noDiscount: '<?= e(__('booking.no_discount', [], 'No Discount')) ?>',
    statutoryGst: 'Statutory GST (__RATE__%):',
    promoDiscount: 'Discount (__PCT__%):',
    inclusiveGst: 'Inclusive of __RATE__% GST',
    exemptNote: 'Exempt'
};

function toggleGst(checkbox) {
    const isIncluded = checkbox.checked;
    const gstRateInput = document.getElementById('gst_rate');
    const gstRateRow = document.getElementById('gstRateRow');
    const gstPresetsRow = document.getElementById('gstPresetsRow');
    const badge = document.getElementById('gstStatusBadge');

    if (isIncluded) {
        if (gstRateInput) gstRateInput.disabled = false;
        if (gstRateRow) {
            gstRateRow.style.opacity = '1';
            gstRateRow.style.pointerEvents = 'auto';
        }
        if (gstPresetsRow) {
            gstPresetsRow.style.opacity = '1';
            gstPresetsRow.style.pointerEvents = 'auto';
        }
        if (badge) {
            badge.style.background = '#FFFFFF';
            badge.style.color = '#176B5B';
            badge.style.border = '1px solid #B2D8CF';
            badge.innerText = VG_I18N.gstApplied;
        }
    } else {
        if (gstRateInput) gstRateInput.disabled = true;
        if (gstRateRow) {
            gstRateRow.style.opacity = '0.35';
            gstRateRow.style.pointerEvents = 'none';
        }
        if (gstPresetsRow) {
            gstPresetsRow.style.opacity = '0.35';
            gstPresetsRow.style.pointerEvents = 'none';
        }
        if (badge) {
            badge.style.background = '#64748B';
            badge.style.color = '#FFF';
            badge.style.border = 'none';
            badge.innerText = VG_I18N.gstExempt;
        }
    }
    calculateFare();
    syncReviewData();
}

function updatePresetPillActiveStates() {
    const discountInput = document.getElementById('discount_percent');
    const curDiscount = parseFloat(discountInput ? discountInput.value : 0) || 0;
    document.querySelectorAll('.booking-preset-pill.discount-pill').forEach(btn => {
        const val = parseFloat(btn.getAttribute('data-pct'));
        if (Math.abs(val - curDiscount) < 0.01) {
            btn.classList.add('is-active');
        } else {
            btn.classList.remove('is-active');
        }
    });

    const includeGstCheckbox = document.getElementById('include_gst');
    const isGstOn = includeGstCheckbox ? includeGstCheckbox.checked : true;
    const gstRateInput = document.getElementById('gst_rate');
    const curGst = parseFloat(gstRateInput ? gstRateInput.value : 0) || 0;
    document.querySelectorAll('.booking-preset-pill.gst-pill').forEach(btn => {
        const val = parseFloat(btn.getAttribute('data-rate'));
        if (isGstOn && Math.abs(val - curGst) < 0.01) {
            btn.classList.add('is-active');
        } else {
            btn.classList.remove('is-active');
        }
    });
}

function setGstRate(rate) {
    const includeGstCheckbox = document.getElementById('include_gst');
    const gstRateInput = document.getElementById('gst_rate');
    if (includeGstCheckbox && !includeGstCheckbox.checked) {
        includeGstCheckbox.checked = true;
        toggleGst(includeGstCheckbox);
    }
    if (gstRateInput) {
        gstRateInput.value = parseFloat(rate).toFixed(1);
    }
    calculateFare();
    updatePresetPillActiveStates();
    syncReviewData();
}

function setDiscountPercent(rate) {
    const discountInput = document.getElementById('discount_percent');
    if (discountInput) {
        discountInput.value = parseFloat(rate).toFixed(1);
    }
    calculateFare();
    updatePresetPillActiveStates();
    syncReviewData();
}

function calculateFare() {
    const subtotal = parseFloat(document.getElementById('custom_subtotal')?.value || 0);
    updatePresetPillActiveStates();

    // Read discount percentage
    const discountPercentInput = document.getElementById('discount_percent');
    const discountPercent = Math.max(0, Math.min(100, parseFloat(discountPercentInput ? discountPercentInput.value : 0) || 0));

    // Calculate discount amount in INR
    const discountAmount = Math.round(((subtotal * discountPercent) / 100) * 100) / 100;

    // Sync hidden discount_amount input for backend persistence
    const discountAmountHidden = document.getElementById('discount_amount');
    if (discountAmountHidden) {
        discountAmountHidden.value = discountAmount.toFixed(2);
    }

    // Update discount badge and savings preview
    const discountBadge = document.getElementById('discountBadge');
    const discountPreview = document.getElementById('discountDeductionPreview');
    if (discountBadge) {
        if (discountPercent > 0) {
            discountBadge.style.background = '#E8F4F0';
            discountBadge.style.color = '#176B5B';
            discountBadge.style.border = '1px solid #B2D8CF';
            discountBadge.innerText = discountPercent + '% Applied';
        } else {
            discountBadge.style.background = '#64748B';
            discountBadge.style.color = '#FFF';
            discountBadge.style.border = 'none';
            discountBadge.innerText = VG_I18N.noDiscount;
        }
    }
    if (discountPreview) {
        discountPreview.innerText = '- ₹ ' + discountAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    const taxable = Math.max(0, subtotal - discountAmount);

    const includeGstCheckbox = document.getElementById('include_gst');
    const isIncluded = includeGstCheckbox ? includeGstCheckbox.checked : true;
    const gstRateInput = document.getElementById('gst_rate');
    const rate = isIncluded ? Math.max(0, parseFloat(gstRateInput ? gstRateInput.value : 5) || 0) : 0;

    const tax = isIncluded ? Math.round(((taxable * rate) / 100) * 100) / 100 : 0.00;
    const total = Math.round((taxable + tax) * 100) / 100;

    const lblSubtotal = document.getElementById('lblSubtotal');
    const lblTax = document.getElementById('lblTax');
    const lblTaxTitle = document.getElementById('lblTaxTitle');
    const lblDiscountTitle = document.getElementById('lblDiscountTitle');
    const lblDiscountAmount = document.getElementById('lblDiscountAmount');
    const lblTotal = document.getElementById('lblTotal');
    const headerBadge = document.getElementById('manifestHeaderBadge');
    const footnote = document.getElementById('lblGstFootnote');

    if (lblSubtotal) lblSubtotal.innerText = '₹ ' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (lblTax) lblTax.innerText = isIncluded ? '+ ₹ ' + tax.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '₹ 0.00 (' + VG_I18N.exemptNote + ')';
    if (lblTaxTitle) lblTaxTitle.innerText = isIncluded ? VG_I18N.statutoryGst.replace('__RATE__', rate) : VG_I18N.statutoryGst.replace('__RATE__', '0') + ' (' + VG_I18N.exemptNote + ')';

    if (lblDiscountTitle) {
        lblDiscountTitle.innerText = VG_I18N.promoDiscount.replace('__PCT__', discountPercent);
    }
    if (lblDiscountAmount) {
        lblDiscountAmount.innerText = discountAmount > 0
            ? '- ₹ ' + discountAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            : '₹ 0.00';
    }

    if (lblTotal) lblTotal.innerText = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (headerBadge) {
        let badgeText = isIncluded ? rate + '% GST' : VG_I18N.gstExempt;
        if (discountPercent > 0) {
            badgeText += ' • ' + discountPercent + '% OFF';
        }
        headerBadge.innerText = badgeText;
    }
    if (footnote) {
        let fnText = isIncluded ? VG_I18N.inclusiveGst.replace('__RATE__', rate) : VG_I18N.gstExempt;
        if (discountPercent > 0) {
            fnText += ' & ' + discountPercent + '%';
        }
        footnote.innerText = fnText;
    }

    calculateAdvance();
}

function getGrandTotal() {
    const lblTotal = document.getElementById('lblTotal');
    if (!lblTotal) return 0;
    const cleaned = lblTotal.innerText.replace(/[^\d.]/g, '');
    return parseFloat(cleaned) || 0;
}

function setAdvancePreset(ratio) {
    const total = getGrandTotal();
    const advInput = document.getElementById('advance_amount');
    if (advInput) {
        advInput.value = (Math.round(total * ratio * 100) / 100).toFixed(2);
    }
    document.querySelectorAll('.booking-preset-pill.adv-pill').forEach(btn => {
        const r = parseFloat(btn.getAttribute('data-ratio'));
        if (Math.abs(r - ratio) < 0.01) {
            btn.classList.add('is-active');
        } else {
            btn.classList.remove('is-active');
        }
    });
    calculateAdvance();
    syncReviewData();
}

function calculateAdvance() {
    const total = getGrandTotal();
    const advInput = document.getElementById('advance_amount');
    let advance = parseFloat(advInput ? advInput.value : 0) || 0;
    if (advance < 0) {
        advance = 0;
        if (advInput) advInput.value = '0.00';
    }

    const errBox = document.getElementById('advanceAlertError');
    const nextBtnStep4 = document.querySelector('#wizardPanel4 .modern-btn-next');
    const submitBtn = document.getElementById('btnSubmitBooking');

    // Overpayment check
    const isOverpaid = advance > (total + 0.001);
    if (isOverpaid) {
        if (errBox) errBox.style.display = 'block';
        if (advInput) {
            advInput.style.borderColor = '#EF4444';
            advInput.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
        }
        if (nextBtnStep4) nextBtnStep4.disabled = true;
        if (submitBtn) submitBtn.disabled = true;
    } else {
        if (errBox) errBox.style.display = 'none';
        if (advInput) {
            advInput.style.borderColor = '';
            advInput.style.boxShadow = '';
        }
        if (nextBtnStep4) nextBtnStep4.disabled = false;
        if (submitBtn) submitBtn.disabled = false;
    }

    const remaining = Math.max(0, Math.round((total - advance) * 100) / 100);

    const lblPayTotal = document.getElementById('lblPayTotal');
    const lblPayReceived = document.getElementById('lblPayReceived');
    const lblPayRemaining = document.getElementById('lblPayRemaining');
    const lblPayStatus = document.getElementById('lblPayStatus');
    const livePaymentBadge = document.getElementById('livePaymentBadge');
    const detailsSection = document.getElementById('advanceDetailsSection');

    if (lblPayTotal) lblPayTotal.innerText = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (lblPayReceived) lblPayReceived.innerText = '₹ ' + advance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (lblPayRemaining) lblPayRemaining.innerText = '₹ ' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    let statusBadgeHtml = '<span class="modern-badge" style="background:#FEF3C7; color:#92400E; font-weight:700; font-size:0.8rem; padding:3px 10px; border-radius:50px;">Unpaid</span>';
    let topBadgeHtml = 'Unpaid (₹0.00 Received)';
    let topBadgeStyle = 'background: #FEF3C7; color: #92400E; font-weight: 700; font-size: 0.75rem; padding: 4px 12px; border-radius: 50px;';

    if (advance > 0.001) {
        if (remaining <= 0.001 && total > 0) {
            statusBadgeHtml = '<span class="modern-badge" style="background:#D1FAE5; color:#065F46; font-weight:700; font-size:0.8rem; padding:3px 10px; border-radius:50px;">Paid (Full)</span>';
            topBadgeHtml = 'Paid (Full Advance)';
            topBadgeStyle = 'background: #D1FAE5; color: #065F46; font-weight: 700; font-size: 0.75rem; padding: 4px 12px; border-radius: 50px;';
        } else {
            statusBadgeHtml = '<span class="modern-badge" style="background:#DBEAFE; color:#1E40AF; font-weight:700; font-size:0.8rem; padding:3px 10px; border-radius:50px;">Partially Paid</span>';
            topBadgeHtml = 'Partially Paid (₹' + advance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
            topBadgeStyle = 'background: #DBEAFE; color: #1E40AF; font-weight: 700; font-size: 0.75rem; padding: 4px 12px; border-radius: 50px;';
        }
    }

    if (lblPayStatus) lblPayStatus.innerHTML = statusBadgeHtml;
    if (livePaymentBadge) {
        livePaymentBadge.innerText = topBadgeHtml;
        livePaymentBadge.setAttribute('style', topBadgeStyle);
    }

    if (detailsSection) {
        if (advance > 0.001) {
            detailsSection.style.opacity = '1';
            detailsSection.style.pointerEvents = 'auto';
        } else {
            detailsSection.style.opacity = '0.7';
        }
    }

    // Update active state on preset pills
    document.querySelectorAll('.booking-preset-pill.adv-pill').forEach(btn => {
        const r = parseFloat(btn.getAttribute('data-ratio'));
        const targetVal = Math.round(total * r * 100) / 100;
        if (Math.abs(targetVal - advance) < 0.01) {
            btn.classList.add('is-active');
        } else {
            btn.classList.remove('is-active');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    wizardGoToStep(1);
    renderExtraChargesTable();
    syncItemizedBilling();
    const custSelect = document.getElementById('customer_id');
    if (custSelect && custSelect.value) {
        onCustomerSelect(custSelect);
    }

    // Form submission feedback & double-submit protection
    const form = document.getElementById('createBookingForm');
    const submitBtn = document.getElementById('btnSubmitBooking');
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.85';
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Confirming Reservation...</span>';
        });
    }
});
</script>

<style>
/* ========================================================================= */
/* Centered Modern Stepper Header                                            */
/* ========================================================================= */
.modern-stepper-header-centered {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 0 0.5rem;
}
.modern-stepper-container {
    width: 100%;
    max-width: 860px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 1rem 1.75rem;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
}
.modern-stepper-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}
.modern-step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.35rem 0.6rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    z-index: 2;
    color: #64748B;
}
.modern-step-item:hover {
    color: #0C2B52;
}
.modern-step-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    font-weight: 700;
    border: 2px solid #CBD5E1;
    margin-bottom: 0.4rem;
    transition: all 0.25s ease;
}
.modern-step-label {
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
}
.modern-step-item.active .modern-step-circle {
    background: #176B5B;
    border-color: #176B5B;
    color: #FFFFFF;
    box-shadow: 0 0 0 4px rgba(23, 107, 91, 0.18);
    transform: scale(1.08);
}
.modern-step-item.active .modern-step-label {
    color: #176B5B;
    font-weight: 700;
}
.modern-step-item.completed .modern-step-circle {
    background: #E8F4F0;
    border-color: #176B5B;
    color: #176B5B;
}
.modern-step-item.completed .modern-step-label {
    color: #176B5B;
}
.modern-step-connector {
    flex: 1;
    height: 3px;
    background: #E2E8F0;
    margin: -1.2rem 0.5rem 0 0.5rem;
    transition: background 0.3s ease;
    border-radius: 4px;
}
.modern-step-connector.completed {
    background: #176B5B;
}

@media (max-width: 640px) {
    .modern-stepper-container {
        padding: 0.85rem 0.75rem;
    }
    .modern-step-label {
        font-size: 0.72rem;
    }
    .modern-step-circle {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }
}

/* ========================================================================= */
/* Form Cards & Section Headers                                             */
/* ========================================================================= */
.modern-form-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.25rem 1rem 3rem 1rem;
}
.modern-form-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 2rem 2.25rem;
    box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05);
}
@media (max-width: 768px) {
    .modern-form-card {
        padding: 1.5rem 1.25rem;
        border-radius: 16px;
    }
}

.modern-section-header {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #F1F5F9;
}
.modern-section-badge {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.modern-section-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0 0 0.2rem 0;
    letter-spacing: -0.3px;
}
.modern-section-sub {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0;
}

/* Add Customer Button */
.booking-add-cust-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    height: 38px;
    padding: 0 16px;
    background: #E8F4F0;
    border: 1.5px solid #B2D8CF;
    border-radius: 50px;
    color: #176B5B;
    font-size: 0.84rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 6px rgba(23, 107, 91, 0.06);
}
.booking-add-cust-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
    border-color: #176B5B;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(23, 107, 91, 0.22);
}

/* ========================================================================= */
/* Grid Layouts & Input Styling                                              */
/* ========================================================================= */
.modern-form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.35rem 1.5rem;
    margin-bottom: 1.35rem;
}
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.35rem 1.5rem;
    margin-bottom: 1.35rem;
}
@media (max-width: 860px) {
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 680px) {
    .modern-form-grid-2 {
        grid-template-columns: 1fr;
    }
}

.modern-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}
.modern-form-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0C2B52;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.req {
    color: #EF4444;
    font-weight: 800;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.modern-input-icon {
    position: absolute;
    left: 14px;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 1;
}
.modern-input-pill,
.modern-select-pill {
    width: 100%;
    padding: 10px 14px 10px 42px;
    background: #F8FAFC;
    border: 1.5px solid #CBD5E1;
    border-radius: 12px;
    font-size: 0.92rem;
    color: #0C2B52;
    font-weight: 500;
    transition: all 0.2s ease;
    outline: none;
}
.modern-input-pill:focus,
.modern-select-pill:focus {
    background: #FFFFFF;
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15);
}
.modern-input-pill:focus + .modern-input-icon,
.modern-input-wrapper:focus-within .modern-input-icon {
    color: #176B5B;
}
.modern-field-error {
    font-size: 0.78rem;
    color: #EF4444;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.2rem;
}

.doc-category-badge {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
}

/* Preset Pill Groups (Discounts & GST) */
.booking-preset-group {
    display: flex;
    gap: 0.45rem;
    align-items: center;
    flex-wrap: wrap;
}
.booking-preset-label {
    font-size: 0.74rem;
    color: #64748B;
    font-weight: 700;
    margin-right: 0.15rem;
}
.booking-preset-label.gst-label {
    color: #125748;
}
.booking-preset-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    min-width: 44px;
    padding: 0 11px;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 8px;
    border: 1.5px solid #CBD5E1;
    background: #FFFFFF;
    color: #334155;
    cursor: pointer;
    line-height: 1;
    user-select: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.booking-preset-pill:hover {
    border-color: #176B5B;
    color: #176B5B;
    background: #F0FDF4;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(23, 107, 91, 0.16);
}
.booking-preset-pill.is-active {
    background: #176B5B !important;
    color: #FFFFFF !important;
    border-color: #176B5B !important;
    box-shadow: 0 3px 10px rgba(23, 107, 91, 0.28) !important;
}
.booking-preset-pill.gst-pill {
    border-color: #B2D8CF;
    color: #125748;
}
.booking-preset-pill.gst-pill:hover {
    background: #E8F4F0;
    border-color: #176B5B;
    color: #176B5B;
}

@media (max-width: 860px) {
    .fare-step-layout {
        grid-template-columns: 1fr !important;
    }
}

/* ========================================================================= */
/* Step 4: Review & Reserve Executive Dossier                                 */
/* ========================================================================= */
.review-dossier-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Hero banner */
.review-hero-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #0C2B52 0%, #176B5B 100%);
    border-radius: 18px;
    padding: 1.5rem 1.75rem;
    color: #FFFFFF;
    box-shadow: 0 8px 24px rgba(12, 43, 82, 0.15);
}
.review-avatar-container {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.8);
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}
.review-avatar-monogram {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: #E8F4F0;
}
.review-hero-meta {
    flex: 1;
}
.review-hero-title-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}
.review-hero-name {
    font-size: 1.45rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.3px;
}
.review-code-badge {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #FFFFFF;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.review-code-badge code {
    font-family: monospace;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.review-hero-tags {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.review-tag-item {
    background: rgba(255, 255, 255, 0.12);
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #E2E8F0;
}
.review-status-pill {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.review-status-pill.is-active {
    background: #10B981;
    color: #FFFFFF;
}

@media (max-width: 640px) {
    .review-hero-banner {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.25rem;
    }
    .review-hero-title-row {
        justify-content: center;
    }
    .review-hero-tags {
        justify-content: center;
    }
}

/* 3 Section Cards Grid */
.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
@media (max-width: 900px) {
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
}

.review-section-card {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
}
.review-card-header {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0C2B52;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    margin-bottom: 0.85rem;
    border-bottom: 1px solid #E2E8F0;
}
.review-card-header div {
    display: flex;
    align-items: center;
    gap: 8px;
}
.review-card-header i {
    color: #176B5B;
}
.review-edit-jump-btn {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}
.review-edit-jump-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
}
.review-card-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    font-size: 0.85rem;
}
.review-info-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.75rem;
}
.review-label {
    color: #64748B;
    font-weight: 600;
    font-size: 0.8rem;
}
.review-value {
    color: #0C2B52;
    font-weight: 600;
    text-align: right;
}
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.font-mono { font-family: monospace; }
.capitalize { text-transform: capitalize; }
.highlight-teal { color: #176B5B; }

/* ========================================================================= */
/* Modern Form Action Buttons                                                */
/* ========================================================================= */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #F1F5F9;
    gap: 1rem;
}
.modern-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #64748B;
    padding: 10px 22px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}
.modern-btn-cancel:hover {
    background: #F8FAFC;
    color: #0C2B52;
    border-color: #94A3B8;
}
.modern-btn-prev {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 10px 22px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.modern-btn-prev:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
}
.modern-btn-next {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #176B5B;
    border: 1.5px solid #176B5B;
    color: #FFFFFF;
    padding: 10px 26px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: auto;
}
.modern-btn-next:hover {
    background: #10B981;
    border-color: #10B981;
    transform: translateX(2px);
}
.modern-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    border: none;
    color: #FFFFFF;
    padding: 11px 28px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    margin-left: auto;
}
.modern-btn-submit:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

@media (max-width: 640px) {
    .modern-form-actions {
        flex-wrap: wrap;
    }
    .modern-btn-cancel, .modern-btn-prev, .modern-btn-next, .modern-btn-submit {
        width: 100%;
        justify-content: center;
    }
    .modern-btn-next, .modern-btn-submit {
        margin-left: 0;
    }
}

/* ========================================================================= */
/* Payment Choice Cards & Custom Extra Charges Styles                       */
/* ========================================================================= */
.payment-choice-card {
    background: #FFFFFF;
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    user-select: none;
}
.payment-choice-card:hover {
    border-color: #B2D8CF;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.08);
}
.payment-choice-card.is-selected {
    border-color: #176B5B;
    background: #F0FDF4;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15), 0 4px 16px rgba(23, 107, 91, 0.08);
}
.payment-choice-radio {
    font-size: 1.25rem;
    color: #94A3B8;
    margin-top: 2px;
    transition: color 0.2s ease;
}
.payment-choice-card.is-selected .payment-choice-radio {
    color: #176B5B;
}
.payment-choice-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #0C2B52;
}
.payment-choice-card.is-selected .payment-choice-title {
    color: #176B5B;
}
.payment-choice-desc {
    margin: 4px 0 0;
    font-size: 0.82rem;
    color: #64748B;
    line-height: 1.45;
}
.payment-choice-icon {
    font-size: 1.35rem;
    color: #94A3B8;
    transition: color 0.2s ease;
}
.payment-choice-card.is-selected .payment-choice-icon {
    color: #176B5B;
}

.skip-payment-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #D97706;
    color: #FFFFFF;
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.25);
}
.skip-payment-action-btn:hover {
    background: #B45309;
    transform: translateX(2px);
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
}

.extra-charge-btn-edit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #E8F4F0;
    border: 1px solid #B2D8CF;
    color: #176B5B;
    border-radius: 6px;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.extra-charge-btn-edit:hover {
    background: #176B5B;
    color: #FFFFFF;
    border-color: #176B5B;
}
.extra-charge-btn-del {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #FEE2E2;
    border: 1px solid #FCA5A5;
    color: #DC2626;
    border-radius: 6px;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.extra-charge-btn-del:hover {
    background: #DC2626;
    color: #FFFFFF;
    border-color: #DC2626;
}

@media (max-width: 768px) {
    .payment-choice-grid {
        grid-template-columns: 1fr !important;
    }
    .extra-charge-inputs-grid {
        grid-template-columns: 1fr 1fr !important;
    }
    .extra-charge-inputs-grid > div:first-child {
        grid-column: span 2;
    }
    .extra-charge-inputs-grid > div:last-child {
        grid-column: span 2;
    }
    .extra-charge-inputs-grid > div:last-child button {
        width: 100%;
    }
}

@media (max-width: 520px) {
    .modern-stepper-track {
        gap: 2px;
    }
    .modern-step-label {
        display: none;
    }
    .modern-step-circle {
        width: 32px;
        height: 32px;
        font-size: 0.82rem;
        margin-bottom: 0;
    }
    .modern-step-connector {
        margin: 0 4px;
    }
}
</style>
