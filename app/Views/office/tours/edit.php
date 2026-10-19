<div style="max-width: 840px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Edit Tour Package</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Modify package pricing, destination cities, and specifications.</p>
        </div>
        <a href="<?= url('/office/tours') ?>" class="btn btn-outline btn-sm">Back to Tours</a>
    </div>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Modify Tour: <?= e($tour['title']) ?></span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/tours/' . $tour['id']) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="title">Tour Title <span class="required-indicator">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= e($tour['title']) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category">Category <span class="required-indicator">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="heritage" <?= $tour['category'] === 'heritage' ? 'selected' : '' ?>>Heritage &amp; Culture</option>
                            <option value="hill_station" <?= $tour['category'] === 'hill_station' ? 'selected' : '' ?>>Hill Station &amp; Nature</option>
                            <option value="pilgrimage" <?= $tour['category'] === 'pilgrimage' ? 'selected' : '' ?>>Spiritual Pilgrimage</option>
                            <option value="wildlife" <?= $tour['category'] === 'wildlife' ? 'selected' : '' ?>>Wildlife Safari</option>
                            <option value="beach" <?= $tour['category'] === 'beach' ? 'selected' : '' ?>>Beach &amp; Coastal</option>
                            <option value="custom" <?= $tour['category'] === 'custom' ? 'selected' : '' ?>>Custom Corporate</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="base_price">Base Price (INR) <span class="required-indicator">*</span></label>
                        <input type="number" id="base_price" name="base_price" step="0.01" class="form-control" required value="<?= e($tour['base_price']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="duration_days">Duration Days <span class="required-indicator">*</span></label>
                        <input type="number" id="duration_days" name="duration_days" class="form-control" value="<?= (int)$tour['duration_days'] ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="duration_nights">Duration Nights <span class="required-indicator">*</span></label>
                        <input type="number" id="duration_nights" name="duration_nights" class="form-control" value="<?= (int)$tour['duration_nights'] ?>" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_passengers">Max Guests <span class="required-indicator">*</span></label>
                        <input type="number" id="max_passengers" name="max_passengers" class="form-control" value="<?= (int)$tour['max_passengers'] ?>" min="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="pickup_city">Pickup City <span class="required-indicator">*</span></label>
                        <input type="text" id="pickup_city" name="pickup_city" class="form-control" required value="<?= e($tour['pickup_city']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="destination_city">Destination City <span class="required-indicator">*</span></label>
                        <input type="text" id="destination_city" name="destination_city" class="form-control" required value="<?= e($tour['destination_city']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Overview Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= e($tour['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="inclusions">Key Inclusions (One per line)</label>
                        <textarea id="inclusions" name="inclusions" class="form-control" rows="3"><?= e($tour['inclusions'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="exclusions">Key Exclusions</label>
                        <textarea id="exclusions" name="exclusions" class="form-control" rows="3"><?= e($tour['exclusions'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Publication Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="published" <?= $tour['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $tour['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= $tour['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/tours') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
