<div style="max-width: 760px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Register Commercial Vehicle</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Add a new vehicle to the operational fleet inventory with compliance tracking.</p>
        </div>
        <a href="<?= url('/office/fleet') ?>" class="btn btn-outline btn-sm">Back to Fleet</a>
    </div>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Commercial Vehicle Profile</span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/fleet/vehicles') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_number">Vehicle Registration Number <span class="required-indicator">*</span></label>
                        <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" required placeholder="e.g. DL 01 AB 1234">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="make_model">Make &amp; Model <span class="required-indicator">*</span></label>
                        <input type="text" id="make_model" name="make_model" class="form-control" required placeholder="e.g. Toyota Innova Crysta 2.4">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_type">Vehicle Classification <span class="required-indicator">*</span></label>
                        <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                            <option value="suv">SUV (6-7 Seats)</option>
                            <option value="sedan">Executive Sedan (4 Seats)</option>
                            <option value="tempo_traveller">Tempo Traveller (12-16 Seats)</option>
                            <option value="luxury_bus">Luxury Coach Bus (35-45 Seats)</option>
                            <option value="van">Urban Minivan (8-10 Seats)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="fuel_type">Fuel Type <span class="required-indicator">*</span></label>
                        <select id="fuel_type" name="fuel_type" class="form-select" required>
                            <option value="diesel">Diesel</option>
                            <option value="petrol">Petrol</option>
                            <option value="cng">CNG</option>
                            <option value="electric">Electric (EV)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="seating_capacity">Seating Capacity (Excl. Chauffeur) <span class="required-indicator">*</span></label>
                        <input type="number" id="seating_capacity" name="seating_capacity" class="form-control" value="6" min="1" max="60" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="odometer_km">Initial Odometer Reading (km) <span class="required-indicator">*</span></label>
                        <input type="number" id="odometer_km" name="odometer_km" class="form-control" value="0" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="registration_expiry">Registration Certificate (RC) Expiry</label>
                        <input type="date" id="registration_expiry" name="registration_expiry" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="insurance_expiry">Commercial Insurance Expiry</label>
                        <input type="date" id="insurance_expiry" name="insurance_expiry" class="form-control">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/fleet') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save &amp; Register Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
