<div style="max-width: 840px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Create New Tour Package</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Define luxury package specifications, inclusions, pricing, and itinerary.</p>
        </div>
        <a href="<?= url('/office/tours') ?>" class="btn btn-outline btn-sm">Back to Tours</a>
    </div>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Tour Specifications</span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/tours') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="title">Tour Title <span class="required-indicator">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Royal Rajasthan Palaces Expedition">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category">Category Classification <span class="required-indicator">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="heritage">Heritage &amp; Culture</option>
                            <option value="hill_station">Hill Station &amp; Nature</option>
                            <option value="pilgrimage">Spiritual Pilgrimage</option>
                            <option value="wildlife">Wildlife Safari</option>
                            <option value="beach">Beach &amp; Coastal</option>
                            <option value="custom">Custom Corporate</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="base_price">Base Fare Price (INR) <span class="required-indicator">*</span></label>
                        <input type="number" id="base_price" name="base_price" step="0.01" class="form-control" required placeholder="18500.00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="duration_days">Duration Days <span class="required-indicator">*</span></label>
                        <input type="number" id="duration_days" name="duration_days" class="form-control" value="3" min="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="duration_nights">Duration Nights <span class="required-indicator">*</span></label>
                        <input type="number" id="duration_nights" name="duration_nights" class="form-control" value="2" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_passengers">Max Guests <span class="required-indicator">*</span></label>
                        <input type="number" id="max_passengers" name="max_passengers" class="form-control" value="6" min="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="pickup_city">Pickup City <span class="required-indicator">*</span></label>
                        <input type="text" id="pickup_city" name="pickup_city" class="form-control" required placeholder="New Delhi">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="destination_city">Destination City <span class="required-indicator">*</span></label>
                        <input type="text" id="destination_city" name="destination_city" class="form-control" required placeholder="Jaipur">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Overview Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Curated travel highlights and itinerary description..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="inclusions">Key Inclusions (One per line)</label>
                        <textarea id="inclusions" name="inclusions" class="form-control" rows="3" placeholder="Luxury AC Private Vehicle&#10;Chauffeur Daily Allowance&#10;All State Border &amp; Toll Taxes"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="exclusions">Key Exclusions</label>
                        <textarea id="exclusions" name="exclusions" class="form-control" rows="3" placeholder="Personal shopping expenses&#10;Monument entry tickets"></textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/tours') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Tour Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
