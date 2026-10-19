<div class="booking-create-container" style="width: 100%;">
    <!-- Top Action Bar -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
                <span class="badge badge-navy" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">Reservation Ops</span>
                <span class="badge badge-success" style="font-size: 0.72rem;"><span class="pulse-indicator"></span> 5% GST Live Engine</span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0;">Create Reservation Booking</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.92rem; margin: 0.35rem 0 0 0;">Deploy luxury chauffeured transit, assign registered clientele, and configure route itinerary.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="<?= url('/office/bookings') ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Bookings</span>
            </a>
        </div>
    </div>

    <!-- Booking Form Full Space Workspace -->
    <form action="<?= url('/office/bookings') ?>" method="POST" id="bookingForm" class="booking-create-form">
        <?= csrf_field() ?>
        <!-- Idempotency Token to prevent duplicate submissions -->
        <input type="hidden" name="idempotency_token" value="<?= bin2hex(random_bytes(16)) ?>">
        <input type="hidden" name="tour_id" value="">

        <div class="booking-form-grid" style="display: grid; grid-template-columns: 1fr 380px; gap: 1.75rem; align-items: start;">
            <!-- Left Main Column (Full Space Itinerary & Guest Data) -->
            <div class="booking-form-main" style="display: flex; flex-direction: column; gap: 1.75rem;">

                <!-- 1. Client Identification Card -->
                <div class="vg-card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span style="width: 34px; height: 34px; border-radius: 8px; background: rgba(2, 132, 199, 0.1); color: var(--vg-blue-600); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <div>
                                <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Client &amp; Guest Identification</span>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted);">Assign the primary passenger manifest account</div>
                            </div>
                        </div>

                        <?php if (auth_role() !== 'customer'): ?>
                        <a href="<?= url('/office/customers/create') ?>" target="_blank" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; border-radius: 8px; font-weight: 600;" title="Register New Customer Profile">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span>+ Add New Customer</span>
                        </a>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <?php if (auth_role() === 'customer'): ?>
                            <input type="hidden" name="customer_id" value="<?= auth_id() ?>">
                            <div class="alert alert-info" style="margin-bottom: 0;">
                                Reservation will be registered under your verified client account: <strong><?= e(auth_user()['name'] ?? 'Client') ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="customer_id" style="font-weight: 600; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span>Guest / Customer Account <span class="required-indicator">*</span></span>
                                    <span style="font-size: 0.78rem; color: var(--vg-text-muted); font-weight: normal;"><?= count($customers) ?> verified guest profile<?= count($customers) === 1 ? '' : 's' ?> available</span>
                                </label>

                                <div class="customer-input-group" style="display: flex; gap: 0.75rem; align-items: center;">
                                    <div style="position: relative; flex: 1;">
                                        <select id="customer_id" name="customer_id" class="form-select" required onchange="onCustomerSelect(this)" style="height: 48px; padding-left: 2.6rem; font-size: 0.92rem;">
                                            <option value="">Select Registered Customer Profile...</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= $c['id'] ?>" data-name="<?= e($c['name']) ?>" data-phone="<?= e($c['phone'] ?? $c['email'] ?? '') ?>" data-code="<?= e($c['customer_code'] ?? '') ?>">
                                                    <?= e($c['name']) ?> (<?= e($c['phone'] ?? $c['email'] ?? 'No contact') ?><?= !empty($c['customer_code']) ? ' · ' . e($c['customer_code']) : '' ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span style="position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </span>
                                    </div>

                                    
                                </div>

                                <!-- Dynamic Client Identification Pill -->
                                <div id="customerSelectedInfo" style="display: none; margin-top: 0.85rem; padding: 0.75rem 1rem; border-radius: 8px; background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.2); font-size: 0.84rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
                                            <strong id="custSelectedName" style="color: var(--vg-navy-900);"></strong>
                                            <span id="custSelectedCode" style="color: var(--vg-blue-600); font-family: monospace; font-weight: 600; padding: 0.15rem 0.4rem; background: rgba(2, 132, 199, 0.1); border-radius: 4px;"></span>
                                        </div>
                                        <div id="custSelectedPhone" style="color: var(--vg-text-secondary);"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Itinerary Route & Timetable Card -->
                <div class="vg-card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span style="width: 34px; height: 34px; border-radius: 8px; background: rgba(217, 119, 6, 0.1); color: var(--vg-gold-600); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </span>
                            <div>
                                <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Itinerary Route &amp; Timetable</span>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted);">Configure transit locations, schedule dates, and passenger count</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Origin & Destination Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="route-address-grid">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="pickup_address">
                                    <span style="display: flex; align-items: center; gap: 0.4rem;">
                                        <svg width="15" height="15" fill="none" stroke="#10B981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Pickup Address &amp; Origin <span class="required-indicator">*</span>
                                    </span>
                                </label>
                                <input type="text" id="pickup_address" name="pickup_address" class="form-control" required placeholder="e.g. Aerocity Terminal 3 / The Oberoi Hotel, New Delhi" style="height: 46px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="drop_address">
                                    <span style="display: flex; align-items: center; gap: 0.4rem;">
                                        <svg width="15" height="15" fill="none" stroke="#EF4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Drop-off Address &amp; Destination <span class="required-indicator">*</span>
                                    </span>
                                </label>
                                <input type="text" id="drop_address" name="drop_address" class="form-control" required placeholder="e.g. Palace Road, Oberoi Rajvilas, Jaipur" style="height: 46px;">
                            </div>
                        </div>

                        <!-- Schedule Timetable 4-Column Grid -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem;" class="schedule-details-grid">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="start_date">Departure Date <span class="required-indicator">*</span></label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="height: 46px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="end_date">Return Date <span class="required-indicator">*</span></label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+4 days')) ?>" style="height: 46px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="pickup_time">Pickup Time</label>
                                <input type="time" id="pickup_time" name="pickup_time" class="form-control" value="08:00" style="height: 46px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="passenger_count">Passenger Count <span class="required-indicator">*</span></label>
                                <input type="number" id="passenger_count" name="passenger_count" class="form-control" value="2" min="1" max="50" required onchange="calculateFare()" style="height: 46px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Special Requests & Chauffeur Instructions Card -->
                <div class="vg-card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span style="width: 34px; height: 34px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            </span>
                            <div>
                                <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Special Requests &amp; Chauffeur Instructions</span>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted);">VIP protocol, flight tracking numbers, child safety seats, or luggage requirements</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-group" style="margin-bottom: 0;">
                            <textarea id="special_requests" name="special_requests" class="form-control" rows="3" placeholder="e.g. VIP guest arrival flight AI-302. Multilingual chauffeur requested, bottled mineral water, 3 large travel luggage bags..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sticky Column: Real-Time Fare & Reservation Manifest Summary -->
            <div class="booking-form-sidebar" style="position: sticky; top: 88px; display: flex; flex-direction: column; gap: 1.25rem;">
                <div class="vg-card" style="box-shadow: 0 14px 34px rgba(7, 15, 30, 0.08); border: 1px solid var(--vg-pearl-300);">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--vg-navy-950) 0%, var(--vg-navy-900) 100%); color: #FFF; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="18" height="18" fill="none" stroke="var(--vg-gold-400)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span style="font-weight: 700; font-size: 0.95rem; color: #FFF;">Fare &amp; Billing Manifest</span>
                            </div>
                            <span class="badge badge-gold" id="manifestHeaderBadge" style="font-size: 0.7rem;">5% GST</span>
                        </div>
                    </div>

                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" for="custom_subtotal" style="font-weight: 600; font-size: 0.82rem;">Base Fare (INR)</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--vg-navy-700);">₹</span>
                                <input type="number" step="0.01" id="custom_subtotal" name="custom_subtotal" class="form-control" value="0.00" onkeyup="calculateFare()" onchange="calculateFare()" style="padding-left: 1.85rem; height: 46px; font-weight: 700; font-size: 1.05rem; color: var(--vg-navy-900);">
                            </div>
                        </div>

                        <!-- Professional Promotional Discount (%) Configuration Box -->
                        <div class="discount-config-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.22); border-radius: 10px; padding: 1rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <label class="form-label" for="discount_percent" style="font-weight: 700; font-size: 0.88rem; color: var(--vg-navy-900); margin-bottom: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <svg width="15" height="15" fill="none" stroke="#10B981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M17 17h.01M7 17L17 7"/></svg>
                                    <span>Promotional Discount (%)</span>
                                </label>
                                <span id="discountBadge" class="badge" style="font-size: 0.68rem; padding: 0.2rem 0.55rem; background: #64748B; color: #FFF; letter-spacing: 0.04em;">NO DISCOUNT</span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="flex: 1; font-size: 0.8rem; color: var(--vg-text-secondary);">
                                    Savings Value: <strong id="discountDeductionPreview" style="color: #10B981; font-weight: 700;">- ₹ 0.00</strong>
                                </div>
                                <div style="position: relative; width: 110px;">
                                    <input type="number" step="0.5" min="0" max="100" id="discount_percent" name="discount_percent" class="form-control" value="0.0" onkeyup="calculateFare()" onchange="calculateFare()" style="height: 38px; font-weight: 700; text-align: right; padding-right: 2rem; font-size: 0.95rem; color: var(--vg-navy-900);">
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--vg-text-muted); font-size: 0.85rem; pointer-events: none;">%</span>
                                </div>
                            </div>

                            <!-- Hidden field keeping computed discount_amount for seamless backend compatibility -->
                            <input type="hidden" id="discount_amount" name="discount_amount" value="0.00">

                            <!-- Standard Promotional Discount Quick Presets -->
                            <div style="display: flex; gap: 0.35rem; margin-top: 0.65rem; align-items: center; flex-wrap: wrap;">
                                <span style="font-size: 0.7rem; color: var(--vg-text-muted); font-weight: 600;">Presets:</span>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setDiscountPercent(0)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">0% (None)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setDiscountPercent(5)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">5% (VIP)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setDiscountPercent(10)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">10% (Corp)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setDiscountPercent(15)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">15% (Special)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setDiscountPercent(20)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">20% (Partner)</button>
                            </div>
                        </div>

                        <!-- Professional Statutory GST Configuration Box -->
                        <div class="gst-config-box" style="background: rgba(2, 132, 199, 0.04); border: 1px solid rgba(2, 132, 199, 0.18); border-radius: 10px; padding: 1rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <label for="include_gst" style="display: flex; align-items: center; gap: 0.65rem; cursor: pointer; user-select: none; margin-bottom: 0;">
                                    <input type="checkbox" id="include_gst" name="include_gst" value="1" checked onchange="toggleGst(this)" style="width: 18px; height: 18px; accent-color: var(--vg-blue-600); cursor: pointer; border-radius: 4px;">
                                    <span style="font-weight: 700; font-size: 0.88rem; color: var(--vg-navy-900);">Include Statutory GST</span>
                                </label>
                                <span id="gstStatusBadge" class="badge badge-success" style="font-size: 0.68rem; padding: 0.2rem 0.55rem; letter-spacing: 0.04em;">GST APPLIED</span>
                            </div>

                            <div id="gstRateRow" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; transition: all 0.2s ease;">
                                <label class="form-label" for="gst_rate" style="font-size: 0.8rem; color: var(--vg-text-secondary); margin-bottom: 0; font-weight: 600;">
                                    GST Rate (%):
                                </label>
                                <div style="position: relative; width: 110px;">
                                    <input type="number" step="0.5" min="0" max="100" id="gst_rate" name="gst_rate" class="form-control" value="5.0" onkeyup="calculateFare()" onchange="calculateFare()" style="height: 38px; font-weight: 700; text-align: right; padding-right: 2rem; font-size: 0.95rem; color: var(--vg-navy-900);">
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--vg-text-muted); font-size: 0.85rem; pointer-events: none;">%</span>
                                </div>
                            </div>

                            <!-- Standard Transport GST Quick Presets -->
                            <div id="gstPresetsRow" style="display: flex; gap: 0.35rem; margin-top: 0.65rem; align-items: center; flex-wrap: wrap;">
                                <span style="font-size: 0.7rem; color: var(--vg-text-muted); font-weight: 600;">Presets:</span>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setGstRate(5)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">5% (Cab)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setGstRate(12)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">12% (Bus)</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setGstRate(18)" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; min-height: 22px; border-radius: 4px; font-weight: 600;">18% (Tour)</button>
                            </div>
                        </div>

                        <!-- Live Breakdown Manifest Receipt -->
                        <div style="background: var(--vg-pearl-100); border-radius: 10px; padding: 1.15rem; border: 1px solid var(--vg-pearl-200); margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.85rem; color: var(--vg-text-secondary);">
                                <span>Base Fare (Gross)</span>
                                <strong id="lblSubtotal" style="color: var(--vg-navy-900);">₹ 0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.85rem; color: var(--vg-text-secondary);">
                                <span id="lblTaxTitle">Statutory 5% GST</span>
                                <strong id="lblTax" style="color: var(--vg-navy-900);">₹ 0.00</strong>
                            </div>
                            <!-- Directly AFTER Statutory 5% GST: Discount % with amount -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; font-size: 0.85rem; color: var(--vg-text-secondary);">
                                <span style="display: flex; align-items: center; gap: 0.35rem; color: #059669; font-weight: 600;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M17 17h.01M7 17L17 7"/></svg>
                                    <span id="lblDiscountTitle">Promotional Discount (0%)</span>
                                </span>
                                <strong id="lblDiscountAmount" style="color: #059669; font-weight: 700;">- ₹ 0.00</strong>
                            </div>
                            <div style="border-top: 1px dashed var(--vg-pearl-300); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: baseline;">
                                <div>
                                    <div style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-text-muted); font-weight: 700;">Grand Total</div>
                                    <div id="lblGstFootnote" style="font-size: 0.68rem; color: var(--vg-text-muted);">Inclusive of 5% GST</div>
                                </div>
                                <div id="lblTotal" style="font-size: 1.65rem; font-weight: 800; color: var(--vg-blue-600); letter-spacing: -0.02em;">₹ 0.00</div>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-weight: 700; font-size: 0.96rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Confirm &amp; Create Reservation</span>
                            </button>
                            <a href="<?= url('/office/bookings') ?>" class="btn btn-outline" style="width: 100%; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem; border-radius: 8px; background: rgba(7, 15, 30, 0.03); border: 1px solid var(--vg-pearl-200); font-size: 0.76rem; color: var(--vg-text-muted); line-height: 1.5;">
                    <div style="font-weight: 700; color: var(--vg-navy-800); margin-bottom: 0.25rem;">🔒 Cryptographic Security</div>
                    This reservation manifest is guarded by an idempotent token preventing duplicate submissions. Chauffeur dispatch duty can be deployed immediately after creation.
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Responsive and Interactive Styling -->
<style>
@media (max-width: 1024px) {
    .booking-form-grid {
        grid-template-columns: 1fr !important;
    }
    .booking-form-sidebar {
        position: static !important;
    }
}
@media (max-width: 640px) {
    .schedule-details-grid {
        grid-template-columns: 1fr 1fr !important;
    }
    .route-address-grid {
        grid-template-columns: 1fr !important;
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
            badge.className = 'badge badge-success';
            badge.style.background = '';
            badge.style.color = '';
            badge.innerText = 'GST APPLIED';
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
            badge.className = 'badge';
            badge.style.background = '#64748B';
            badge.style.color = '#FFF';
            badge.innerText = 'EXEMPT';
        }
    }
    calculateFare();
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
}

function setDiscountPercent(rate) {
    const discountInput = document.getElementById('discount_percent');
    if (discountInput) {
        discountInput.value = parseFloat(rate).toFixed(1);
    }
    calculateFare();
}

function calculateFare() {
    const subtotal = parseFloat(document.getElementById('custom_subtotal').value || 0);

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
            discountBadge.className = 'badge badge-success';
            discountBadge.style.background = '';
            discountBadge.style.color = '';
            discountBadge.innerText = discountPercent + '% APPLIED';
        } else {
            discountBadge.className = 'badge';
            discountBadge.style.background = '#64748B';
            discountBadge.style.color = '#FFF';
            discountBadge.innerText = 'NO DISCOUNT';
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
    if (lblTax) lblTax.innerText = isIncluded ? '+ ₹ ' + tax.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '₹ 0.00 (Exempt)';
    if (lblTaxTitle) lblTaxTitle.innerText = isIncluded ? 'Statutory ' + rate + '% GST' : 'Statutory GST (Exempt)';

    if (lblDiscountTitle) {
        lblDiscountTitle.innerText = 'Promotional Discount (' + discountPercent + '%)';
    }
    if (lblDiscountAmount) {
        lblDiscountAmount.innerText = discountAmount > 0
            ? '- ₹ ' + discountAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            : '₹ 0.00';
    }

    if (lblTotal) lblTotal.innerText = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (headerBadge) {
        let badgeText = isIncluded ? rate + '% GST' : 'GST Exempt';
        if (discountPercent > 0) {
            badgeText += ' • ' + discountPercent + '% OFF';
        }
        headerBadge.innerText = badgeText;
        headerBadge.className = isIncluded ? 'badge badge-gold' : 'badge badge-navy';
    }
    if (footnote) {
        let fnText = isIncluded ? 'Inclusive of ' + rate + '% GST' : 'Tax Exempt Booking';
        if (discountPercent > 0) {
            fnText += ' & ' + discountPercent + '% Discount';
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
