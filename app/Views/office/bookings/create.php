<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Create Booking Reservation
 * Master Modern Form UI Matching Reference Design System
 */
?>

<div class="modern-form-wrapper" style="max-width: 1240px;">

    <!-- Top Header Bar: Circular Back Button with Title and Subtitle -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/bookings') ?>" class="modern-back-btn" title="<?= e(__('booking.back_to_bookings', [], 'Back to Bookings')) ?>" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('booking.create_title', [], 'Create Reservation Booking')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('booking.create_subtitle', [], 'Register passenger itinerary, timetable, and calculated fare.')) ?></p>
            </div>
        </div>
    </div>

    <!-- Booking Form Full Space Workspace -->
    <form action="<?= url('/office/bookings') ?>" method="POST" id="bookingForm" class="booking-create-form" novalidate>
        <?= csrf_field() ?>
        <!-- Idempotency Token to prevent duplicate submissions -->
        <input type="hidden" name="idempotency_token" value="<?= bin2hex(random_bytes(16)) ?>">
        <input type="hidden" name="tour_id" value="">

        <div class="booking-form-grid" style="display: grid; grid-template-columns: 1fr 390px; gap: 1.75rem; align-items: start;">
            <!-- Left Main Column (Full Space Itinerary & Guest Data) -->
            <div class="booking-form-main" style="display: flex; flex-direction: column; gap: 1.75rem;">

                <!-- 1. Client Identification Card -->
                <div class="modern-form-card">
                    <div class="modern-section-header" style="margin-bottom: 1.5rem; padding-bottom: 1.25rem;">
                        <div class="modern-section-badge">
                            <i class="fa-solid fa-user-tag"></i>
                        </div>
                        <div class="modern-section-meta" style="flex: 1;">
                            <h2 class="modern-section-title"><?= e(__('card.client_identification', [], 'Client Identification')) ?></h2>
                            <p class="modern-section-sub"><?= e(__('card.client_identification_desc', [], 'Assign reservation to a verified guest or corporate client.')) ?></p>
                        </div>

                        <?php if (auth_role() !== 'customer'): ?>
                        <a href="<?= url('/office/customers/create') ?>" target="_blank" class="booking-add-cust-btn" title="Register New Customer Profile">
                            <i class="fa-solid fa-user-plus"></i> <?= e(__('booking.add_new_customer', [], 'Add Customer')) ?>
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php if (auth_role() === 'customer'): ?>
                        <input type="hidden" name="customer_id" value="<?= auth_id() ?>">
                        <div style="background: #E8F4F0; border: 1.5px solid #B2D8CF; padding: 1rem 1.25rem; border-radius: 14px; font-size: 0.9rem; color: #176B5B;">
                            Reservation will be registered under your verified client account: <strong><?= e(auth_user()['name'] ?? 'Client') ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="customer_id" style="display: flex; justify-content: space-between; align-items: center;">
                                <span><?= e(__('booking.guest_account', [], 'Guest Account')) ?> <span class="req">*</span></span>
                                <span style="font-size: 0.78rem; color: #64748B; font-weight: normal;"><?= count($customers) ?> verified guest profile<?= count($customers) === 1 ? '' : 's' ?> available</span>
                            </label>

                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                                <select id="customer_id" name="customer_id" class="modern-select-pill" required onchange="onCustomerSelect(this)">
                                    <option value=""><?= e(__('booking.select_customer', [], '— Select verified client profile —')) ?></option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>" data-name="<?= e($c['name']) ?>" data-phone="<?= e($c['phone'] ?? $c['email'] ?? '') ?>" data-code="<?= e($c['customer_code'] ?? '') ?>">
                                            <?= e($c['name']) ?> (<?= e($c['phone'] ?? $c['email'] ?? 'No contact') ?><?= !empty($c['customer_code']) ? ' &bull; ' . e($c['customer_code']) : '' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dynamic Client Identification Pill -->
                            <div id="customerSelectedInfo" style="display: none; margin-top: 1rem; padding: 0.85rem 1.25rem; border-radius: 12px; background: #E8F4F0; border: 1.5px solid #B2D8CF; font-size: 0.86rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <span style="width: 9px; height: 9px; border-radius: 50%; background: #176B5B;"></span>
                                        <strong id="custSelectedName" style="color: #0C2B52; font-size: 0.95rem;"></strong>
                                        <span id="custSelectedCode" style="color: #176B5B; font-family: monospace; font-weight: 700; padding: 2px 8px; background: #FFFFFF; border-radius: 4px; border: 1px solid #B2D8CF;"></span>
                                    </div>
                                    <div id="custSelectedPhone" style="color: #125748; font-weight: 600;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Itinerary Route & Timetable Card -->
                <div class="modern-form-card">
                    <div class="modern-section-header" style="margin-bottom: 1.5rem; padding-bottom: 1.25rem;">
                        <div class="modern-section-badge">
                            <i class="fa-solid fa-route"></i>
                        </div>
                        <div class="modern-section-meta">
                            <h2 class="modern-section-title"><?= e(__('card.itinerary_route', [], 'Itinerary Route & Schedule')) ?></h2>
                            <p class="modern-section-sub"><?= e(__('card.itinerary_route_desc', [], 'Pickup address, destination drop, journey dates, and passenger headcount.')) ?></p>
                        </div>
                    </div>

                    <!-- Origin & Destination Grid -->
                    <div class="modern-form-grid-2">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="pickup_address">
                                <?= e(__('booking.pickup_address', [], 'Pickup Address / Departure Hub')) ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" id="pickup_address" name="pickup_address" class="modern-input-pill" required placeholder="<?= e(__('booking.pickup_placeholder', [], 'e.g. Terminal 3, IGI Airport / Hotel')) ?>">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="drop_address">
                                <?= e(__('booking.drop_address', [], 'Drop Address / Final Destination')) ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-map-pin"></i></span>
                                <input type="text" id="drop_address" name="drop_address" class="modern-input-pill" required placeholder="<?= e(__('booking.drop_placeholder', [], 'e.g. The Oberoi, Jaipur / Local Sightseeing')) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Timetable 4-Column Grid -->
                    <div class="modern-form-grid-4">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_date">
                                <?= e(__('booking.departure_date', [], 'Departure Date')) ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                                <input type="date" id="start_date" name="start_date" class="modern-input-pill" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="end_date">
                                <?= e(__('booking.return_date', [], 'Return Date')) ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <input type="date" id="end_date" name="end_date" class="modern-input-pill" required value="<?= date('Y-m-d', strtotime('+4 days')) ?>">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="pickup_time">
                                <?= e(__('booking.pickup_time', [], 'Pickup Time')) ?>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-clock"></i></span>
                                <input type="time" id="pickup_time" name="pickup_time" class="modern-input-pill" value="08:00">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="passenger_count">
                                <?= e(__('booking.passenger_count', [], 'Passengers')) ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                                <input type="number" id="passenger_count" name="passenger_count" class="modern-input-pill" value="2" min="1" max="50" required onchange="calculateFare()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Special Requests & Chauffeur Instructions Card -->
                <div class="modern-form-card">
                    <div class="modern-section-header" style="margin-bottom: 1.5rem; padding-bottom: 1.25rem;">
                        <div class="modern-section-badge">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                        <div class="modern-section-meta">
                            <h2 class="modern-section-title"><?= e(__('card.chauffeur_instructions', [], 'Special Requests & Chauffeur Instructions')) ?></h2>
                            <p class="modern-section-sub"><?= e(__('card.chauffeur_instructions_desc', [], 'Flight flight numbers, luggage details, or dietary water preferences.')) ?></p>
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon" style="top: 18px;"><i class="fa-solid fa-pen-to-square"></i></span>
                            <textarea id="special_requests" name="special_requests" class="modern-textarea-pill" placeholder="<?= e(__('booking.special_requests', [], 'e.g. VIP welcome placard at airport, luggage van assistance, baby car seat requested.')) ?>"></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4. Form Action Buttons (High Visibility Confirm & Cancel) -->
                <div class="modern-form-card booking-main-actions-card">
                    <div class="booking-audit-note">
                        <span class="booking-audit-icon">
                            <i class="fa-solid fa-shield-check"></i>
                        </span>
                        <span><?= e(__('booking.audit_footnote', [], 'Verify all passenger details, itinerary route, and GST calculations before submitting.')) ?></span>
                    </div>

                    <div class="booking-main-actions">
                        <a href="<?= url('/office/bookings') ?>" class="modern-btn-cancel booking-action-cancel">
                            <i class="fa-solid fa-xmark"></i> <?= e(__('btn.cancel', [], 'Cancel')) ?>
                        </a>
                        <button type="submit" class="modern-btn-submit booking-action-confirm">
                            <i class="fa-solid fa-check"></i> <?= e(__('booking.confirm_create', [], 'Confirm & Create Reservation')) ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sticky Column: Real-Time Fare & Reservation Manifest Summary -->
            <div class="booking-form-sidebar" style="position: sticky; top: 88px; display: flex; flex-direction: column; gap: 1.25rem;">
                <div class="modern-form-card booking-manifest-card" style="border: 1.5px solid #B2D8CF; box-shadow: 0 14px 34px rgba(23, 107, 91, 0.08);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #EDF3FA;">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span style="width: 38px; height: 38px; border-radius: 12px; background: #E8F4F0; color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-calculator"></i>
                            </span>
                            <span style="font-weight: 700; font-size: 1.05rem; color: #0C2B52;"><?= e(__('card.fare_manifest', [], 'Fare Calculation')) ?></span>
                        </div>
                        <span id="manifestHeaderBadge" style="padding: 4px 12px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-weight: 700; font-size: 0.8rem; border: 1px solid #B2D8CF;">5% GST</span>
                    </div>

                    <!-- Base Fare Input -->
                    <div class="modern-form-group" style="margin-bottom: 1.25rem;">
                        <label class="modern-form-label" for="custom_subtotal">
                            <?= e(__('booking.base_fare', [], 'Base Rack Rate (₹)')) ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                            <input type="number" step="0.01" id="custom_subtotal" name="custom_subtotal" class="modern-input-pill" value="0.00" onkeyup="calculateFare()" onchange="calculateFare()" style="font-weight: 700; font-size: 1.05rem; color: #0C2B52;">
                        </div>
                    </div>

                    <!-- Promotional Discount Box -->
                    <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 14px; padding: 1.15rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.65rem;">
                            <label class="modern-form-label" for="discount_percent" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-tag" style="color: #176B5B;"></i> <?= e(__('booking.promotional_discount', [], 'Discount (%)')) ?>
                            </label>
                            <span id="discountBadge" style="padding: 2px 10px; border-radius: 50px; background: #64748B; color: #FFF; font-size: 0.72rem; font-weight: 700;"><?= e(__('booking.no_discount', [], 'No Discount')) ?></span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                            <div style="flex: 1; font-size: 0.82rem; color: #64748B;">
                                <?= e(__('booking.savings_value', [], 'Savings:')) ?> <strong id="discountDeductionPreview" style="color: #176B5B; font-weight: 700;">- ₹ 0.00</strong>
                            </div>
                            <div style="position: relative; width: 105px;">
                                <input type="number" step="0.5" min="0" max="100" id="discount_percent" name="discount_percent" class="modern-input-pill" value="0.0" onkeyup="calculateFare()" onchange="calculateFare()" style="height: 42px; font-weight: 700; text-align: right; padding-right: 2rem; padding-left: 10px; font-size: 0.95rem;">
                                <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748B; font-size: 0.85rem; pointer-events: none;">%</span>
                            </div>
                        </div>

                        <input type="hidden" id="discount_amount" name="discount_amount" value="0.00">

                        <!-- Quick Presets -->
                        <div class="booking-preset-group">
                            <span class="booking-preset-label"><?= e(__('common.presets', [], 'Presets:')) ?></span>
                            <button type="button" class="booking-preset-pill discount-pill" data-pct="0" onclick="setDiscountPercent(0)">0%</button>
                            <button type="button" class="booking-preset-pill discount-pill" data-pct="5" onclick="setDiscountPercent(5)">5%</button>
                            <button type="button" class="booking-preset-pill discount-pill" data-pct="10" onclick="setDiscountPercent(10)">10%</button>
                            <button type="button" class="booking-preset-pill discount-pill" data-pct="15" onclick="setDiscountPercent(15)">15%</button>
                        </div>
                    </div>

                    <!-- Statutory GST Box -->
                    <div style="background: #E8F4F0; border: 1.5px solid #B2D8CF; border-radius: 14px; padding: 1.15rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                            <label for="include_gst" style="display: flex; align-items: center; gap: 0.65rem; cursor: pointer; user-select: none; margin-bottom: 0;">
                                <input type="checkbox" id="include_gst" name="include_gst" value="1" checked onchange="toggleGst(this)" style="width: 18px; height: 18px; accent-color: #176B5B; cursor: pointer;">
                                <span style="font-weight: 700; font-size: 0.88rem; color: #0C2B52;"><?= e(__('booking.include_gst', [], 'Apply Statutory GST')) ?></span>
                            </label>
                            <span id="gstStatusBadge" style="padding: 2px 10px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-weight: 700; font-size: 0.72rem; border: 1px solid #B2D8CF;"><?= e(__('booking.gst_applied', [], 'GST Applied')) ?></span>
                        </div>

                        <div id="gstRateRow" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                            <label class="modern-form-label" for="gst_rate" style="font-size: 0.82rem; color: #125748; margin-bottom: 0;">
                                <?= e(__('booking.gst_rate', [], 'Tax Slab (%)')) ?>
                            </label>
                            <div style="position: relative; width: 105px;">
                                <input type="number" step="0.5" min="0" max="100" id="gst_rate" name="gst_rate" class="modern-input-pill" value="5.0" onkeyup="calculateFare()" onchange="calculateFare()" style="height: 42px; font-weight: 700; text-align: right; padding-right: 2rem; padding-left: 10px; font-size: 0.95rem; background: #FFFFFF;">
                                <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748B; font-size: 0.85rem; pointer-events: none;">%</span>
                            </div>
                        </div>

                        <!-- Presets -->
                        <div id="gstPresetsRow" class="booking-preset-group">
                            <span class="booking-preset-label gst-label"><?= e(__('common.slabs', [], 'Slabs:')) ?></span>
                            <button type="button" class="booking-preset-pill gst-pill" data-rate="5" onclick="setGstRate(5)">5% (Cab)</button>
                            <button type="button" class="booking-preset-pill gst-pill" data-rate="12" onclick="setGstRate(12)">12% (Bus)</button>
                            <button type="button" class="booking-preset-pill gst-pill" data-rate="18" onclick="setGstRate(18)">18% (Tour)</button>
                        </div>
                    </div>

                    <!-- Live Breakdown Manifest Receipt -->
                    <div style="background: #FFFFFF; border-radius: 14px; padding: 1.15rem; border: 1.5px solid #DCE6F2; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.85rem; color: #64748B;">
                            <span><?= e(__('booking.base_fare_gross', [], 'Base Fare:')) ?></span>
                            <strong id="lblSubtotal" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.85rem; color: #64748B;">
                            <span id="lblTaxTitle"><?= e(__('booking.statutory_gst_lbl', ['rate' => '5'], 'Statutory GST (5%):')) ?></span>
                            <strong id="lblTax" style="color: #0C2B52;">₹ 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; font-size: 0.85rem; color: #64748B;">
                            <span style="display: flex; align-items: center; gap: 0.35rem; color: #176B5B; font-weight: 600;">
                                <i class="fa-solid fa-tag"></i>
                                <span id="lblDiscountTitle"><?= e(__('booking.promo_discount_lbl', ['percent' => '0'], 'Discount (0%):')) ?></span>
                            </span>
                            <strong id="lblDiscountAmount" style="color: #176B5B; font-weight: 700;">- ₹ 0.00</strong>
                        </div>
                        <div style="border-top: 1.5px dashed #DCE6F2; padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: baseline;">
                            <div>
                                <div style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; font-weight: 700;"><?= e(__('card.grand_total', [], 'Grand Total')) ?></div>
                                <div id="lblGstFootnote" style="font-size: 0.68rem; color: #176B5B; font-weight: 600;"><?= e(__('booking.inclusive_gst', ['rate' => '5'], 'Inclusive of GST & Discount')) ?></div>
                            </div>
                            <div id="lblTotal" style="font-size: 1.65rem; font-weight: 800; color: #176B5B; letter-spacing: -0.02em;">₹ 0.00</div>
                        </div>
                    </div>

                    <div class="booking-sidebar-actions">
                        <a href="<?= url('/office/bookings') ?>" class="modern-btn-cancel booking-sidebar-cancel">
                            <i class="fa-solid fa-xmark"></i> <?= e(__('btn.cancel', [], 'Cancel')) ?>
                        </a>
                        <button type="submit" class="modern-btn-submit booking-sidebar-confirm">
                            <i class="fa-solid fa-check"></i> <?= e(__('booking.confirm_create', [], 'Confirm & Create')) ?>
                        </button>
                    </div>
                </div>

                <div style="padding: 1rem 1.25rem; border-radius: 14px; background: #FFFFFF; border: 1.5px solid #E6EEF8; font-size: 0.78rem; color: #64748B; line-height: 1.5;">
                    <div style="font-weight: 700; color: #0C2B52; margin-bottom: 0.25rem;"><i class="fa-solid fa-shield-halved" style="color: #176B5B;"></i> <?= e(__('booking.security_title', [], 'Audit & Compliance Verified')) ?></div>
                    <?= e(__('booking.security_desc', [], 'Every reservation generates a unique audit trace token and idempotency signature.')) ?>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Responsive and Interactive Styling -->
<style>
/* ==========================================================================
   BOOKINGS CREATE - PROFESSIONAL RESPONSIVE BUTTON & LAYOUT ENGINE
   ========================================================================== */

/* Add Customer Action Pill */
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
.booking-add-cust-btn i {
    color: #176B5B;
    transition: transform 0.2s ease, color 0.2s ease;
}
.booking-add-cust-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
    border-color: #176B5B;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(23, 107, 91, 0.22);
}
.booking-add-cust-btn:hover i {
    color: #FFFFFF !important;
    transform: rotate(90deg);
}
.booking-add-cust-btn:active {
    transform: scale(0.97);
}

/* Preset Pill Groups (Discounts & GST) */
.booking-preset-group {
    display: flex;
    gap: 0.45rem;
    margin-top: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}
.booking-preset-label {
    font-size: 0.74rem;
    color: #64748B;
    font-weight: 700;
    margin-right: 0.15rem;
    letter-spacing: 0.02em;
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
.booking-preset-pill:active {
    transform: scale(0.96);
}
.booking-preset-pill.is-active {
    background: #176B5B !important;
    color: #FFFFFF !important;
    border-color: #176B5B !important;
    box-shadow: 0 3px 10px rgba(23, 107, 91, 0.28) !important;
}

/* GST Pills specific tint */
.booking-preset-pill.gst-pill {
    border-color: #B2D8CF;
    color: #125748;
}
.booking-preset-pill.gst-pill:hover {
    background: #E8F4F0;
    border-color: #176B5B;
    color: #176B5B;
}

/* Main Form Bottom Actions Card */
.booking-main-actions-card {
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    flex-wrap: wrap;
    background: #FFFFFF;
    border-radius: 20px;
    border: 1.5px solid #E6EEF8;
    box-shadow: 0 8px 24px rgba(12, 43, 82, 0.05);
}
.booking-audit-note {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    color: #475569;
    font-size: 0.88rem;
    flex: 1 1 300px;
}
.booking-audit-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #E8F4F0;
    color: #176B5B;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.booking-main-actions {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-shrink: 0;
}
.booking-action-cancel {
    min-width: 135px;
    height: 48px;
    padding: 0 24px;
    justify-content: center;
    font-size: 0.92rem;
    font-weight: 700;
}
.booking-action-confirm {
    min-width: 240px;
    height: 48px;
    padding: 0 28px;
    justify-content: center;
    font-size: 0.96rem;
    font-weight: 800;
}

/* Sidebar Actions */
.booking-sidebar-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-top: 0.5rem;
}
.booking-sidebar-cancel {
    flex: 1;
    height: 48px;
    justify-content: center;
    padding: 0 16px;
    font-size: 0.9rem;
    font-weight: 700;
}
.booking-sidebar-confirm {
    flex: 1.8;
    height: 48px;
    justify-content: center;
    padding: 0 18px;
    font-size: 0.92rem;
    font-weight: 800;
}

/* ==========================================================================
   RESPONSIVE BREAKPOINTS FOR BOOKINGS CREATE BUTTONS
   ========================================================================== */

/* LAPTOP / SMALL DESKTOP (<= 1200px) */
@media (max-width: 1200px) {
    .booking-form-grid {
        grid-template-columns: 1fr 360px !important;
        gap: 1.25rem !important;
    }
    .booking-action-confirm {
        min-width: 210px !important;
    }
}

/* TABLET BREAKPOINT (<= 1024px) */
@media (max-width: 1024px) {
    .booking-form-grid {
        grid-template-columns: 1fr !important;
        gap: 1.25rem !important;
    }
    .booking-form-sidebar {
        position: static !important;
        width: 100% !important;
    }
    .booking-sidebar-actions {
        display: flex !important;
        flex-direction: row !important;
        gap: 0.85rem !important;
    }
    .booking-sidebar-cancel {
        flex: 1 !important;
    }
    .booking-sidebar-confirm {
        flex: 2 !important;
    }
}

/* MEDIUM TABLET & LARGE PHONE BREAKPOINT (<= 768px) */
@media (max-width: 768px) {
    .booking-main-actions-card {
        padding: 1.35rem 1.15rem !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1rem !important;
    }
    .booking-main-actions {
        width: 100% !important;
        justify-content: stretch !important;
    }
    .booking-action-cancel {
        flex: 1 !important;
        min-width: auto !important;
    }
    .booking-action-confirm {
        flex: 2 !important;
        min-width: auto !important;
    }
}

/* MOBILE PHONES (<= 640px) */
@media (max-width: 640px) {
    .modern-form-wrapper {
        padding: 0 !important;
    }
    .modern-form-card {
        padding: 1.25rem 1rem !important;
        border-radius: 16px !important;
    }
    .booking-manifest-card {
        padding: 1.25rem 1rem !important;
    }

    /* Section header wrap and Add Customer full width */
    .modern-section-header {
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
        margin-bottom: 1.25rem !important;
        padding-bottom: 1rem !important;
    }
    .booking-add-cust-btn {
        width: 100% !important;
        height: 42px !important;
        font-size: 0.88rem !important;
        justify-content: center !important;
        margin-top: 0.35rem !important;
    }

    /* Header title & back button */
    .modern-header-left {
        gap: 0.75rem !important;
    }
    .modern-back-btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        font-size: 0.95rem !important;
    }
    .modern-form-title {
        font-size: 1.22rem !important;
    }
    .modern-form-subtitle {
        font-size: 0.76rem !important;
    }

    /* Preset Pills on Mobile: Large, Touch-Friendly, Auto-Stretched */
    .booking-preset-group {
        display: flex !important;
        gap: 0.35rem !important;
        width: 100% !important;
    }
    .booking-preset-label {
        width: 100% !important;
        margin-bottom: 0.25rem !important;
    }
    .booking-preset-pill {
        flex: 1 1 auto !important;
        height: 36px !important;
        min-width: 46px !important;
        padding: 0 10px !important;
        font-size: 0.82rem !important;
        border-radius: 10px !important;
        justify-content: center !important;
    }
    .booking-preset-pill.gst-pill {
        min-width: 70px !important;
        font-size: 0.8rem !important;
    }

    /* Bottom Main Action Buttons on Mobile: Full-width Stack with Primary on Top */
    .booking-main-actions {
        display: flex !important;
        flex-direction: column-reverse !important;
        gap: 0.75rem !important;
        width: 100% !important;
    }
    .booking-action-confirm {
        width: 100% !important;
        min-width: 100% !important;
        height: 50px !important;
        font-size: 0.96rem !important;
        font-weight: 800 !important;
        border-radius: 14px !important;
        justify-content: center !important;
    }
    .booking-action-cancel {
        width: 100% !important;
        min-width: 100% !important;
        height: 46px !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        border-radius: 14px !important;
        justify-content: center !important;
    }

    /* Sidebar Actions on Mobile: Full-width Stack with Primary on Top */
    .booking-sidebar-actions {
        display: flex !important;
        flex-direction: column-reverse !important;
        gap: 0.75rem !important;
        width: 100% !important;
    }
    .booking-sidebar-confirm {
        width: 100% !important;
        min-width: 100% !important;
        height: 50px !important;
        font-size: 0.96rem !important;
        font-weight: 800 !important;
        border-radius: 14px !important;
        justify-content: center !important;
    }
    .booking-sidebar-cancel {
        width: 100% !important;
        min-width: 100% !important;
        height: 46px !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        border-radius: 14px !important;
        justify-content: center !important;
    }
}

/* EXTRA SMALL PHONES (<= 380px) */
@media (max-width: 380px) {
    .booking-preset-pill {
        font-size: 0.75rem !important;
        padding: 0 6px !important;
    }
    .booking-action-confirm,
    .booking-sidebar-confirm {
        font-size: 0.9rem !important;
    }
}
</style>

<script>
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

const VG_I18N = {
    gstApplied: <?= json_encode(__('booking.gst_applied', [], 'GST Applied')) ?>,
    gstExempt: <?= json_encode(__('booking.gst_exempt', [], 'Exempt')) ?>,
    noDiscount: <?= json_encode(__('booking.no_discount', [], 'No Discount')) ?>,
    discountApplied: <?= json_encode(__('status.active', [], 'Active')) ?>,
    statutoryGst: <?= json_encode(__('booking.statutory_gst_lbl', ['rate' => '__RATE__'], 'Statutory GST (__RATE__%):')) ?>,
    promoDiscount: <?= json_encode(__('booking.promo_discount_lbl', ['percent' => '__PCT__'], 'Discount (__PCT__%):')) ?>,
    inclusiveGst: <?= json_encode(__('booking.inclusive_gst', ['rate' => '__RATE__'], 'Inclusive of __RATE__% GST')) ?>,
    exemptNote: <?= json_encode(__('status.exempt', [], 'Exempt')) ?>
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
}

function setDiscountPercent(rate) {
    const discountInput = document.getElementById('discount_percent');
    if (discountInput) {
        discountInput.value = parseFloat(rate).toFixed(1);
    }
    calculateFare();
    updatePresetPillActiveStates();
}

function calculateFare() {
    const subtotal = parseFloat(document.getElementById('custom_subtotal').value || 0);
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
}

document.addEventListener('DOMContentLoaded', () => {
    calculateFare();
    const custSelect = document.getElementById('customer_id');
    if (custSelect && custSelect.value) {
        onCustomerSelect(custSelect);
    }
});
</script>
