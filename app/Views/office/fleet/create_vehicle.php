<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Register Fleet Vehicle
 * Master Modern Form UI Matching Reference Design System
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar: Circular Back Button, Title, Subtitle, and Fleet Tag -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/fleet') ?>" class="modern-back-btn" title="<?= e(__('vehicle_form.back_to_vehicles', [], 'Back to Fleet Directory')) ?>" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('vehicle_form.register_title', [], 'Register Commercial Vehicle')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('vehicle_form.register_subtitle', [], 'Add verified commercial vehicle to operational fleet inventory.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.88rem; font-weight: 700; border: 1px solid #B2D8CF;">
                <i class="fa-solid fa-car"></i> Fleet Registration
            </span>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: __('form.please_review_errors', [], 'Please check the form for errors.')) ?>;
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

    <form action="<?= url('/office/fleet/vehicles') ?>" method="POST" id="fleetVehicleForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">
            <!-- Section 1: Vehicle Specifications -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title"><?= __('vehicle_form.specifications', [], 'Vehicle Specifications') ?></h2>
                    <p class="modern-section-sub">Official registration plate number, make &amp; model name, and body classification.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Registration Number -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="vehicle_number">
                        <?= __('vehicle_form.registration_plate', [], 'Registration Plate') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                        <input type="text" id="vehicle_number" name="vehicle_number" class="modern-input-pill" required placeholder="e.g. DL 01 AB 1234" style="text-transform: uppercase; font-family: monospace;">
                    </div>
                </div>

                <!-- Make & Model -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="make_model">
                        <?= __('vehicle_form.make_model', [], 'Make & Model') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-car"></i></span>
                        <input type="text" id="make_model" name="make_model" class="modern-input-pill" required placeholder="e.g. Toyota Innova Crysta 2.4">
                    </div>
                </div>

                <!-- Classification -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="vehicle_type">
                        <?= __('vehicle_form.classification', [], 'Body Classification') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-shapes"></i></span>
                        <select id="vehicle_type" name="vehicle_type" class="modern-select-pill" required>
                            <option value="suv"><?= __('vehicle_form.cat_suv', [], 'SUV (Innova, Ertiga)') ?></option>
                            <option value="sedan"><?= __('vehicle_form.cat_sedan', [], 'Sedan (Dzire, Etios)') ?></option>
                            <option value="tempo_traveller"><?= __('vehicle_form.cat_tempo', [], 'Tempo Traveller') ?></option>
                            <option value="luxury_bus"><?= __('vehicle_form.cat_bus', [], 'Luxury Bus / Coach') ?></option>
                            <option value="van"><?= __('vehicle_form.cat_van', [], 'Omni / Minivan') ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Fuel Type -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="fuel_type">
                        <?= __('vehicle_form.fuel_type', [], 'Fuel Type') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-gas-pump"></i></span>
                        <select id="fuel_type" name="fuel_type" class="modern-select-pill" required>
                            <option value="diesel"><?= __('vehicle_form.fuel_diesel', [], 'Diesel') ?></option>
                            <option value="petrol"><?= __('vehicle_form.fuel_petrol', [], 'Petrol') ?></option>
                            <option value="cng"><?= __('vehicle_form.fuel_cng', [], 'CNG') ?></option>
                            <option value="electric"><?= __('vehicle_form.fuel_electric', [], 'Electric (EV)') ?></option>
                        </select>
                    </div>
                </div>

                <!-- Seating Capacity -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="seating_capacity">
                        <?= __('vehicle_form.seating_capacity', [], 'Seating Capacity') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                        <input type="number" id="seating_capacity" name="seating_capacity" class="modern-input-pill" value="6" min="1" max="60" required>
                    </div>
                </div>

                <!-- Initial Odometer -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="odometer_km">
                        <?= __('vehicle_form.initial_odometer', [], 'Odometer Reading (KM)') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-gauge-high"></i></span>
                        <input type="number" id="odometer_km" name="odometer_km" class="modern-input-pill" value="0" min="0" required>
                    </div>
                </div>
            </div>

            <!-- Section 2: Compliance & Insurance Validity -->
            <div class="modern-section-header" style="margin-top: 2rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Compliance &amp; Documents</h2>
                    <p class="modern-section-sub">Registration certificate and commercial insurance validity dates.</p>
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Registration Expiry -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="registration_expiry">
                        <?= __('vehicle_form.rc_expiry', [], 'RC Expiry Date') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                        <input type="date" id="registration_expiry" name="registration_expiry" class="modern-input-pill">
                    </div>
                </div>

                <!-- Insurance Expiry -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="insurance_expiry">
                        <?= __('vehicle_form.insurance_expiry', [], 'Insurance Expiry Date') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-shield-virus"></i></span>
                        <input type="date" id="insurance_expiry" name="insurance_expiry" class="modern-input-pill">
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/fleet') ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= __('form.cancel', [], 'Cancel') ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-check"></i> <?= __('vehicle_form.register_btn', [], 'Register Vehicle') ?>
                </button>
            </div>
        </div>
    </form>
</div>
