<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Create Tour Package
 * Master Modern Form UI Matching Reference Design System
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/tours') ?>" class="modern-back-btn" title="Back to Holiday Packages" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('tour_form.create_title', [], 'Create New Tour Package')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('tour_form.create_subtitle', [], 'Publish destination package with itinerary, hotel stays, and fare rules.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.88rem; font-weight: 700;">
                <i class="fa-solid fa-map-location-dot"></i> Tour Itinerary Ops
            </span>
        </div>
    </div>

    <form action="<?= url('/office/tours') ?>" method="POST" id="tourForm" novalidate>
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
                    <input type="text" id="title" name="title" class="modern-input-pill" required placeholder="<?= e(__('tour_form.title_placeholder', [], 'e.g. Royal Rajasthan Golden Triangle Experience')) ?>">
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
                            <option value="heritage"><?= e(__('tour_form.cat_heritage', [], 'Heritage & Palaces')) ?></option>
                            <option value="hill_station"><?= e(__('tour_form.cat_hill', [], 'Hill Station & Nature')) ?></option>
                            <option value="pilgrimage"><?= e(__('tour_form.cat_pilgrimage', [], 'Pilgrimage & Sacred')) ?></option>
                            <option value="wildlife"><?= e(__('tour_form.cat_wildlife', [], 'Wildlife & Safari')) ?></option>
                            <option value="beach"><?= e(__('tour_form.cat_beach', [], 'Coastal & Beach')) ?></option>
                            <option value="custom"><?= e(__('tour_form.cat_custom', [], 'Custom Tailored Circuit')) ?></option>
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
                        <input type="number" id="base_price" name="base_price" step="0.01" class="modern-input-pill" required placeholder="18500.00">
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
                        <input type="number" id="duration_days" name="duration_days" class="modern-input-pill" value="3" min="1" required>
                    </div>
                </div>

                <!-- Duration Nights -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="duration_nights">
                        <?= e(__('tour_form.duration_nights', [], 'Duration (Nights)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-moon"></i></span>
                        <input type="number" id="duration_nights" name="duration_nights" class="modern-input-pill" value="2" min="0" required>
                    </div>
                </div>

                <!-- Max Passengers -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="max_passengers">
                        <?= e(__('tour_form.max_passengers', [], 'Max Guests (Capacity)')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                        <input type="number" id="max_passengers" name="max_passengers" class="modern-input-pill" value="6" min="1" required>
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
                        <input type="text" id="pickup_city" name="pickup_city" class="modern-input-pill" required placeholder="e.g. New Delhi">
                    </div>
                </div>

                <!-- Destination City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="destination_city">
                        <?= e(__('tour_form.destination_city', [], 'Primary Destination City')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-flag-checkered"></i></span>
                        <input type="text" id="destination_city" name="destination_city" class="modern-input-pill" required placeholder="e.g. Jaipur">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="modern-form-group" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="description"><?= e(__('tour_form.description', [], 'Tour Overview & Highlights')) ?></label>
                <div class="modern-input-wrapper is-textarea">
                    <span class="modern-input-icon"><i class="fa-solid fa-align-left"></i></span>
                    <textarea id="description" name="description" class="modern-textarea-pill" placeholder="<?= e(__('tour_form.description_placeholder', [], 'Describe sightseeing itinerary, monuments, scenic stops, and meal arrangements...')) ?>"></textarea>
                </div>
            </div>

            <!-- Inclusions & Exclusions -->
            <div class="modern-form-grid-2">
                <div class="modern-form-group">
                    <label class="modern-form-label" for="inclusions"><?= e(__('tour_form.inclusions', [], 'Package Inclusions')) ?></label>
                    <div class="modern-input-wrapper is-textarea">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <textarea id="inclusions" name="inclusions" class="modern-textarea-pill" placeholder="<?= e(__('tour_form.inclusions_placeholder', [], 'e.g. AC vehicle, driver allowance, toll taxes, fuel, state permits...')) ?>"></textarea>
                    </div>
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="exclusions"><?= e(__('tour_form.exclusions', [], 'Package Exclusions')) ?></label>
                    <div class="modern-input-wrapper is-textarea">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-xmark"></i></span>
                        <textarea id="exclusions" name="exclusions" class="modern-textarea-pill" placeholder="<?= e(__('tour_form.exclusions_placeholder', [], 'e.g. Monument entry tickets, personal laundry, tips, GST 5%...')) ?>"></textarea>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/tours') ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= e(__('form.cancel', [], 'Cancel')) ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-check"></i> <?= e(__('tour_form.create_btn', [], 'Create Tour Package')) ?>
                </button>
            </div>
        </div>
    </form>
</div>
