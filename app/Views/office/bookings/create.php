<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Create Booking Reservation
 * Professional 4-step wizard matching customers/create architecture & design system
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional 4-Step Stepper Header -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Client &amp; Guests</span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Itinerary &amp; Route</span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Fare &amp; Billing</span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label">Review &amp; Reserve</span>
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

        <div class="modern-form-card">

            <!-- ========================================================================= -->
            <!-- STEP 1: Client Identification & Passengers                                -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel1">

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
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="modern-form-label" for="customer_id" style="margin-bottom: 0;">
                                Guest Account <span class="req">*</span>
                            </label>
                            <a href="<?= url('/office/customers/create') ?>" target="_blank" class="booking-add-cust-btn" style="padding: 4px 12px; font-size: 0.8rem;" title="Register New Customer Profile">
                                <i class="fa-solid fa-user-plus"></i> Add Customer
                            </a>
                        </div>

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
                            Passenger Headcount <span class="req">*</span>
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
                            Reservation Priority
                        </label>
                        <div style="display: flex; align-items: center; height: 46px; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 0 14px; gap: 8px;">
                            <i class="fa-solid fa-star" style="color: #F59E0B;"></i>
                            <span style="font-weight: 700; color: #0C2B52; font-size: 0.9rem;">Standard Fleet Roster</span>
                            <span style="margin-left: auto; font-size: 0.74rem; background: #E8F4F0; color: #176B5B; padding: 2px 8px; border-radius: 50px; font-weight: 700;">Instant Confirmation</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/bookings') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: Itinerary Route & Timetable                                       -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel2" style="display: none;">

                <!-- Origin & Destination Grid -->
                <div class="modern-form-grid-2">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="pickup_address">
                            Pickup Address / Departure Hub <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="pickup_address" name="pickup_address" class="modern-input-pill" required placeholder="e.g. Terminal 3, IGI Airport / Hotel" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['pickup_address'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['pickup_address'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="drop_address">
                            Drop Address / Final Destination <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-map-pin"></i></span>
                            <input type="text" id="drop_address" name="drop_address" class="modern-input-pill" required placeholder="e.g. The Oberoi, Jaipur / Local Sightseeing" oninput="syncReviewData()">
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

                <!-- Special Requests & Chauffeur Instructions -->
                <div class="modern-form-group" style="margin-top: 0.5rem;">
                    <label class="modern-form-label" for="special_requests">
                        Special Requests &amp; Chauffeur Instructions
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                        <input type="text" id="special_requests" name="special_requests" class="modern-input-pill" placeholder="e.g. VIP welcome placard at airport, luggage van assistance, infant car seat requested" oninput="syncReviewData()">
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(1)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(2)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 3: Fare Calculation & Billing                                        -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel3" style="display: none;">

                <div class="fare-step-layout" style="display: grid; grid-template-columns: 1fr 380px; gap: 1.75rem; align-items: start;">
                    <!-- Left: Fare Inputs & Presets -->
                    <div class="fare-inputs-column" style="display: flex; flex-direction: column; gap: 1.35rem;">
                        <!-- Base Fare Input -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="custom_subtotal">
                                Base Rack Rate (₹) <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                <input type="number" step="0.01" id="custom_subtotal" name="custom_subtotal" class="modern-input-pill" value="0.00" onkeyup="calculateFare(); syncReviewData();" onchange="calculateFare(); syncReviewData();" style="font-weight: 700; font-size: 1.1rem; color: #0C2B52;">
                            </div>
                        </div>

                        <!-- Promotional Discount Box -->
                        <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 16px; padding: 1.25rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <label class="modern-form-label" for="discount_percent" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fa-solid fa-tag" style="color: #176B5B;"></i> Promotional Discount (%)
                                </label>
                                <span id="discountBadge" style="padding: 2px 10px; border-radius: 50px; background: #64748B; color: #FFF; font-size: 0.72rem; font-weight: 700;">No Discount</span>
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
                                    <span style="font-weight: 700; font-size: 0.9rem; color: #0C2B52;">Apply Statutory GST</span>
                                </label>
                                <span id="gstStatusBadge" style="padding: 2px 10px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-weight: 700; font-size: 0.72rem; border: 1px solid #B2D8CF;">GST Applied</span>
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
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="5" onclick="setGstRate(5)">5% (Cab)</button>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="12" onclick="setGstRate(12)">12% (Bus)</button>
                                <button type="button" class="booking-preset-pill gst-pill" data-rate="18" onclick="setGstRate(18)">18% (Tour)</button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live Breakdown Manifest Receipt -->
                    <div class="fare-receipt-card" style="background: #FFFFFF; border-radius: 18px; padding: 1.5rem; border: 1.5px solid #B2D8CF; box-shadow: 0 10px 30px rgba(23, 107, 91, 0.08);">
                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #EDF3FA;">
                            <span style="width: 36px; height: 36px; border-radius: 10px; background: #E8F4F0; color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                                <i class="fa-solid fa-receipt"></i>
                            </span>
                            <span style="font-weight: 800; font-size: 1.05rem; color: #0C2B52;">Live Fare Manifest</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.88rem; color: #64748B;">
                            <span>Base Rack Rate:</span>
                            <strong id="lblSubtotal" style="color: #0C2B52;">₹ 0.00</strong>
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
                                <div style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; font-weight: 800;">Grand Total</div>
                                <div id="lblGstFootnote" style="font-size: 0.7rem; color: #176B5B; font-weight: 600;">Inclusive of GST &amp; Discount</div>
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
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(3)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 4: Review & Reserve                                                  -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel4" style="display: none;">

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

                    <!-- 3 Comprehensive Information Section Cards -->
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
                                    <span class="review-label">Guest Legal Name</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardClientName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Client Code</span>
                                    <span class="review-value font-mono" id="sumCardClientCode">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Contact Reachability</span>
                                    <span class="review-value" id="sumCardClientContact">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Passenger Headcount</span>
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
                                    <span class="review-label">Pickup Hub</span>
                                    <span class="review-value font-semibold" id="sumCardPickup">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Destination Drop</span>
                                    <span class="review-value font-semibold" id="sumCardDrop">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Journey Timetable</span>
                                    <span class="review-value" id="sumCardJourneyDates">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Pickup Time</span>
                                    <span class="review-value font-mono font-bold" id="sumCardPickupTime">09:00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Special Remarks</span>
                                    <span class="review-value" id="sumCardSpecialRequests">Standard dispatch</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Fare & Billing Manifest -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-calculator"></i> Fare &amp; Taxes</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(3)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Base Rack Rate</span>
                                    <span class="review-value font-bold" id="sumCardBaseFare">₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Promotional Discount</span>
                                    <span class="review-value" style="color: #176B5B;" id="sumCardDiscount">- ₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Statutory GST</span>
                                    <span class="review-value" id="sumCardGst">+ ₹ 0.00</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Grand Total</span>
                                    <span class="review-value font-bold highlight-teal" style="font-size: 1.15rem;" id="sumCardGrandTotal">₹ 0.00</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(3)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
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
// Wizard State & Navigation (4-Step Streamlined Process matching customers/create)
let currentStep = 1;
const totalSteps = 4;

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

    // When on step 4, populate the comprehensive live summary dossier
    if (activeStep === 4) {
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

// Live Summary Dossier Synchronizer for Step 4
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

    // Card 3: Fare & Taxes
    const cardBaseFareEl = document.getElementById('sumCardBaseFare');
    if (cardBaseFareEl) cardBaseFareEl.textContent = subtotalText;

    const cardDiscountEl = document.getElementById('sumCardDiscount');
    if (cardDiscountEl) cardDiscountEl.textContent = discountText;

    const cardGstEl = document.getElementById('sumCardGst');
    if (cardGstEl) cardGstEl.textContent = taxText;

    const cardTotalEl = document.getElementById('sumCardGrandTotal');
    if (cardTotalEl) cardTotalEl.textContent = totalText;
}

function syncReviewData() {
    if (currentStep === 4) {
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

// Fare Calculation Engine
const VG_I18N = {
    gstApplied: 'GST Applied',
    gstExempt: 'Exempt',
    noDiscount: 'No Discount',
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
}

document.addEventListener('DOMContentLoaded', () => {
    wizardGoToStep(1);
    calculateFare();
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
    margin-bottom: 2rem;
    padding: 0;
    width: 100%;
}
.modern-stepper-container {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 1.1rem 2rem;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
}
.modern-stepper-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    width: 100%;
    max-width: 960px;
    margin: 0 auto;
}
.modern-step-item {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer;
    padding: 0.35rem 0.6rem !important;
    border-radius: 12px !important;
    transition: all 0.2s ease !important;
    z-index: 2;
    color: #64748B !important;
    outline: none !important;
    text-decoration: none !important;
}
.modern-step-item:hover {
    color: #0C2B52 !important;
    transform: translateY(-1px);
}
.modern-step-circle {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    border-radius: 50% !important;
    background: #FFFFFF !important;
    color: #64748B !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    border: 2px solid #CBD5E1 !important;
    margin-bottom: 0.45rem !important;
    transition: all 0.25s ease !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
}
.modern-step-label {
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    margin-top: 0 !important;
    white-space: nowrap !important;
    transition: color 0.2s ease !important;
}
.modern-step-item.active .modern-step-circle {
    background: #176B5B !important;
    border-color: #176B5B !important;
    color: #FFFFFF !important;
    box-shadow: 0 0 0 4px rgba(23, 107, 91, 0.18), 0 4px 12px rgba(23, 107, 91, 0.25) !important;
    transform: scale(1.08) !important;
}
.modern-step-item.active .modern-step-label {
    color: #176B5B !important;
    font-weight: 700 !important;
}
.modern-step-item.completed .modern-step-circle {
    background: #10B981 !important;
    border-color: #10B981 !important;
    color: #FFFFFF !important;
}
.modern-step-item.completed .modern-step-label {
    color: #10B981 !important;
}
.modern-step-connector {
    flex: 1 !important;
    height: 3px !important;
    background: #E2E8F0 !important;
    margin: -1.2rem 0.5rem 0 0.5rem !important;
    transition: background 0.3s ease !important;
    border-radius: 4px !important;
}
.modern-step-connector.completed {
    background: #10B981 !important;
}

@media (max-width: 768px) {
    .modern-stepper-header-centered {
        margin-bottom: 1.5rem;
    }
    .modern-stepper-container {
        padding: 0.85rem 1rem;
        border-radius: 18px;
    }
    .modern-step-circle {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        font-size: 0.88rem !important;
        margin-bottom: 0.35rem !important;
    }
    .modern-step-label {
        font-size: 0.76rem !important;
    }
    .modern-step-connector {
        margin: -1.1rem 0.35rem 0 0.35rem !important;
    }
}

@media (max-width: 480px) {
    .modern-stepper-container {
        padding: 0.75rem 0.65rem;
        border-radius: 16px;
    }
    .modern-step-item {
        padding: 0.2rem 0.35rem !important;
    }
    .modern-step-circle {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 0.82rem !important;
        margin-bottom: 0.25rem !important;
    }
    .modern-step-connector {
        margin: -0.95rem 0.25rem 0 0.25rem !important;
    }
    .modern-step-item:not(.active) .modern-step-label {
        display: none !important;
    }
    .modern-step-item.active .modern-step-label {
        display: inline-block !important;
        font-size: 0.72rem !important;
    }
}

/* ========================================================================= */
/* Form Cards & Section Headers                                             */
/* ========================================================================= */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem 0;
}
.modern-form-card {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 2rem 2.25rem;
    box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05);
}
@media (max-width: 768px) {
    .modern-form-wrapper {
        padding: 0 0 2rem;
    }
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
</style>
