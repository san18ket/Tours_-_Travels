<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit Holiday Tour Package
 * Master Modern Form UI Matching Reference Design System
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar: Circular Back Button, Title, Subtitle, and Rack Rate Badge -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/tours') ?>" class="modern-back-btn" title="<?= e(__('tour_form.back_to_tours', [], 'Back to Holiday Packages')) ?>" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('tour.edit_title', ['name' => $tour['name']], 'Edit Tour Package')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('tour.edit_subtitle', [], 'Update destination itinerary, pricing, and hotel stays.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.95rem; font-weight: 800; border: 1px solid #B2D8CF;">
                <i class="fa-solid fa-indian-rupee-sign"></i> <?= number_format((float)$tour['base_price'], 2) ?>
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

    <form action="<?= url('/office/tours/' . $tour['id']) ?>" method="POST" id="editTourForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">
            <!-- Section 1: Tour Specifications -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-suitcase-rolling"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title"><?= e(__('tour_form.tour_specs', [], 'Tour Package Specifications')) ?></h2>
                    <p class="modern-section-sub">Package title, tourism category, and base rack rate.</p>
                </div>
            </div>

            <!-- Title -->
            <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="title">
                    <?= e(__('tour_form.title', [], 'Tour Package Title')) ?> <span class="req">*</span>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-heading"></i></span>
                    <input type="text" id="title" name="title" class="modern-input-pill" required value="<?= e($tour['title']) ?>">
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Category -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="category">
                        <?= e(__('tour_form.category', [], 'Package Category')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-layer-group"></i></span>
                        <select id="category" name="category" class="modern-select-pill" required>
                            <option value="heritage" <?= $tour['category'] === 'heritage' ? 'selected' : '' ?>><?= e(__('tour_form.cat_heritage', [], 'Heritage & Palaces')) ?></option>
                            <option value="hill_station" <?= $tour['category'] === 'hill_station' ? 'selected' : '' ?>><?= e(__('tour_form.cat_hill', [], 'Hill Station & Nature')) ?></option>
                            <option value="pilgrimage" <?= $tour['category'] === 'pilgrimage' ? 'selected' : '' ?>><?= e(__('tour_form.cat_pilgrimage', [], 'Pilgrimage & Sacred')) ?></option>
                            <option value="wildlife" <?= $tour['category'] === 'wildlife' ? 'selected' : '' ?>><?= e(__('tour_form.cat_wildlife', [], 'Wildlife & Safari')) ?></option>
                            <option value="beach" <?= $tour['category'] === 'beach' ? 'selected' : '' ?>><?= e(__('tour_form.cat_beach', [], 'Coastal & Beach')) ?></option>
                            <option value="custom" <?= $tour['category'] === 'custom' ? 'selected' : '' ?>><?= e(__('tour_form.cat_custom', [], 'Custom Tailored Circuit')) ?></option>
                        </select>
                    </div>
                </div>

                <!-- Base Price -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="base_price">
                        <?= e(__('tour_form.base_price', [], 'Base Rate Per Group / Pax (₹)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                        <input type="number" id="base_price" name="base_price" step="0.01" class="modern-input-pill" required value="<?= e($tour['base_price']) ?>">
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Duration Days -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="duration_days">
                        <?= e(__('tour_form.duration_days', [], 'Duration (Days)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-sun"></i></span>
                        <input type="number" id="duration_days" name="duration_days" class="modern-input-pill" value="<?= (int)$tour['duration_days'] ?>" min="1" required>
                    </div>
                </div>

                <!-- Duration Nights -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="duration_nights">
                        <?= e(__('tour_form.duration_nights', [], 'Duration (Nights)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-moon"></i></span>
                        <input type="number" id="duration_nights" name="duration_nights" class="modern-input-pill" value="<?= (int)$tour['duration_nights'] ?>" min="0" required>
                    </div>
                </div>

                <!-- Max Passengers -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="max_passengers">
                        <?= e(__('tour_form.max_passengers', [], 'Max Guests (Capacity)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                        <input type="number" id="max_passengers" name="max_passengers" class="modern-input-pill" value="<?= (int)$tour['max_passengers'] ?>" min="1" required>
                    </div>
                </div>
            </div>

            <!-- Section 2: Circuit Route & Itinerary -->
            <div class="modern-section-header" style="margin-top: 1.5rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Route &amp; Circuit Cities</h2>
                    <p class="modern-section-sub">Origin departure city and final circuit destination.</p>
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Pickup City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="pickup_city">
                        <?= e(__('tour_form.pickup_city', [], 'Departure / Pickup City')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" id="pickup_city" name="pickup_city" class="modern-input-pill" required value="<?= e($tour['pickup_city']) ?>">
                    </div>
                </div>

                <!-- Destination City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="destination_city">
                        <?= e(__('tour_form.destination_city', [], 'Destination City')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-map-pin"></i></span>
                        <input type="text" id="destination_city" name="destination_city" class="modern-input-pill" required value="<?= e($tour['destination_city']) ?>">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="description">
                    <?= e(__('tour_form.description', [], 'Tour Overview & Highlights')) ?>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon" style="top: 18px;"><i class="fa-solid fa-paragraph"></i></span>
                    <textarea id="description" name="description" class="modern-textarea-pill" placeholder="Highlight primary destinations, sightseeing spots, and cultural experiences"><?= e($tour['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="modern-form-grid-2">
                <!-- Inclusions -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="inclusions">
                        <?= e(__('tour_form.inclusions', [], 'Package Inclusions')) ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon" style="top: 18px;"><i class="fa-solid fa-circle-check"></i></span>
                        <textarea id="inclusions" name="inclusions" class="modern-textarea-pill" placeholder="e.g. AC vehicle with commercial chauffeur, highway toll, state tax, mineral water"><?= e($tour['inclusions'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Exclusions -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="exclusions">
                        <?= e(__('tour_form.exclusions', [], 'Package Exclusions')) ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon" style="top: 18px;"><i class="fa-solid fa-circle-xmark"></i></span>
                        <textarea id="exclusions" name="exclusions" class="modern-textarea-pill" placeholder="e.g. Monument entry tickets, personal laundry, guide fees"><?= e($tour['exclusions'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Package Status -->
            <div class="modern-form-group" style="margin-top: 1.5rem; margin-bottom: 2rem;">
                <label class="modern-form-label" for="status">
                    <?= e(__('form.status', [], 'Publication Status')) ?>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-toggle-on"></i></span>
                    <select id="status" name="status" class="modern-select-pill">
                        <option value="published" <?= $tour['status'] === 'published' ? 'selected' : '' ?>><?= e(__('tours.badge_published', [], 'Published (Available for Booking)')) ?></option>
                        <option value="draft" <?= $tour['status'] === 'draft' ? 'selected' : '' ?>><?= e(__('common.pending', [], 'Draft')) ?></option>
                        <option value="archived" <?= $tour['status'] === 'archived' ? 'selected' : '' ?>><?= e(__('common.inactive', [], 'Archived')) ?></option>
                    </select>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/tours') ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= __('form.cancel', [], 'Cancel') ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> <?= __('form.save_changes', [], 'Save Tour Changes') ?>
                </button>
            </div>
        </div>
    </form>
</div>
