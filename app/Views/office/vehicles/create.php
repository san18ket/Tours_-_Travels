<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Register Commercial Vehicle</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Register a commercial vehicle into the active fleet with seating capacity and compliance tracking.</p>
        </div>
        <a href="<?= url('/office/vehicles') ?>" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Vehicles</span>
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
            <div style="font-weight: 700; margin-bottom: 0.25rem;">Please review the input errors below:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem;">
                <?php foreach ($errors as $field => $errList): ?>
                    <?php foreach ((array)$errList as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Commercial Vehicle Specifications</span>
            <span class="badge badge-navy">Fleet Ready</span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/vehicles') ?>" method="POST" id="vehicleForm">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_number">Registration Plate Number <span class="required-indicator">*</span></label>
                        <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" required value="<?= e($old['vehicle_number'] ?? '') ?>" placeholder="e.g. DL 01 AB 1234" style="text-transform: uppercase; font-family: monospace; font-weight: 700;">
                        <small style="color: var(--vg-text-muted); font-size: 0.78rem;">Official commercial registration plate.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="make_model">Vehicle Make &amp; Model <span class="required-indicator">*</span></label>
                        <input type="text" id="make_model" name="make_model" class="form-control" required value="<?= e($old['make_model'] ?? '') ?>" placeholder="e.g. Toyota Innova Crysta ZX">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_type">Classification Type <span class="required-indicator">*</span></label>
                        <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="sedan" <?= ($old['vehicle_type'] ?? '') === 'sedan' ? 'selected' : '' ?>>Luxury Sedan (Mercedes E-Class, Camry)</option>
                            <option value="suv" <?= ($old['vehicle_type'] ?? 'suv') === 'suv' ? 'selected' : '' ?>>Premium SUV (Innova Crysta, Fortuner)</option>
                            <option value="tempo_traveller" <?= ($old['vehicle_type'] ?? '') === 'tempo_traveller' ? 'selected' : '' ?>>Tempo Traveller (12-16 Seater)</option>
                            <option value="luxury_bus" <?= ($old['vehicle_type'] ?? '') === 'luxury_bus' ? 'selected' : '' ?>>Luxury Bus / Volvo Coach</option>
                            <option value="van" <?= ($old['vehicle_type'] ?? '') === 'van' ? 'selected' : '' ?>>Executive Van</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="seating_capacity">Passenger Seating Capacity <span class="required-indicator">*</span></label>
                        <input type="number" id="seating_capacity" name="seating_capacity" class="form-control" required value="<?= e($old['seating_capacity'] ?? '6') ?>" min="1" max="60">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="fuel_type">Fuel Classification <span class="required-indicator">*</span></label>
                        <select id="fuel_type" name="fuel_type" class="form-select" required>
                            <option value="diesel" <?= ($old['fuel_type'] ?? 'diesel') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="petrol" <?= ($old['fuel_type'] ?? '') === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                            <option value="cng" <?= ($old['fuel_type'] ?? '') === 'cng' ? 'selected' : '' ?>>CNG</option>
                            <option value="electric" <?= ($old['fuel_type'] ?? '') === 'electric' ? 'selected' : '' ?>>Electric (EV)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="background: var(--vg-pearl-100); padding: 1.25rem; border-radius: var(--vg-radius-md); border: 1px solid var(--vg-pearl-200); margin-bottom: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="odometer_km">Initial Odometer Reading (km) <span class="required-indicator">*</span></label>
                        <input type="number" id="odometer_km" name="odometer_km" class="form-control" required value="<?= e($old['odometer_km'] ?? '0') ?>" min="0" style="font-weight: 700; font-size: 1.1rem;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="registration_expiry">Registration Certificate Expiry</label>
                        <input type="date" id="registration_expiry" name="registration_expiry" class="form-control" value="<?= e($old['registration_expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="insurance_expiry">Commercial Insurance Expiry</label>
                        <input type="date" id="insurance_expiry" name="insurance_expiry" class="form-control" value="<?= e($old['insurance_expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="<?= url('/office/vehicles') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-navy">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Register Vehicle</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
