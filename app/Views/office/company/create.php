<?php
/**
 * VoyageGo Tours & Travels — Master Add Company Profile Form
 * Exact implementation matching office/customers/create.php layout, stepper, form UI & responsiveness
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional 4-Step Stepper Header (Exact Match to office/customers/create.php) -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label"><?= e(__('company.step1_title', [], 'Company Profile')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label"><?= e(__('company.step2_title', [], 'Contact Details')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label"><?= e(__('company.step3_title', [], 'Location Details')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label"><?= e(__('company.step4_title', [], 'Review & Save')) ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Active Primary Profile Guidance Banner (If primary profile already exists) -->
    <?php if (!empty($primaryCompany)): ?>
    <div style="background: #EFF6FF; border: 1.5px solid #BFDBFE; border-radius: 18px; padding: 1.25rem 1.5rem; margin-bottom: 1.75rem; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
        <div style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; gap: 0.85rem; align-items: center;">
                <div style="font-size: 1.6rem; color: #2563EB; line-height: 1;"><i class="fa-solid fa-circle-info"></i></div>
                <div>
                    <h4 style="font-size: 1rem; font-weight: 700; color: #1E3A8A; margin: 0 0 0.25rem 0;">
                        Primary Installation Profile Configured: <code><?= e($primaryCompany['company_name']) ?></code>
                    </h4>
                    <p style="font-size: 0.88rem; color: #3B82F6; margin: 0;">
                        This installation already has an active primary profile. You can either edit the active profile or initialize an alternate profile below.
                    </p>
                </div>
            </div>
            <a href="<?= url('/office/company/' . $primaryCompany['id'] . '/edit') ?>" class="btn btn-sm btn-primary" style="white-space: nowrap; font-size: 0.88rem; padding: 0.5rem 1.15rem; font-weight: 700; border-radius: 8px;">
                <i class="fa-solid fa-pen-to-square"></i> Edit Current Profile &rarr;
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: 'Please review the highlighted fields.') ?>;
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
    <form action="<?= url('/office/company') ?>" method="POST" id="createCompanyForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">

            <!-- ========================================================================= -->
            <!-- STEP 1: Company Profile & Brand Logo                                      -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel1">

                <!-- Professional Logo Upload Component (Matching office/customers/create) -->
                <div class="customer-avatar-upload-card">
                    <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('logo').click()" title="Click to upload company logo">
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleLogoChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="logo_base64" id="logo_base64" value="">
                        <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">

                        <div class="avatar-default-icon" id="logoDefaultIcon">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <img id="logoPreviewImg" src="" alt="Company Logo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <div class="avatar-camera-badge" title="Upload Logo">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="customer-avatar-meta">
                        <div class="customer-avatar-title-row">
                            <span class="customer-avatar-label"><?= e(__('company.logo', [], 'Official Company Logo')) ?></span>
                            <span class="modern-badge-subtle">Recommended</span>
                        </div>
                        <p class="customer-avatar-help">
                            Upload your official company brand logo or emblem. Supported formats: PNG, JPG, JPEG, WEBP (Max 5MB). Saved to designated avatars storage.
                        </p>
                        <div class="customer-avatar-actions">
                            <button type="button" class="customer-avatar-btn-upload" onclick="document.getElementById('logo').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Logo
                            </button>
                            <button type="button" class="customer-avatar-btn-remove" id="btnRemoveLogo" style="display: none;" onclick="clearLogoSelection()">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                        </div>
                        <?php if (!empty($errors['logo'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['logo'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 1: Company Name (2 Columns), Tagline / Slogan (2 Columns) -->
                <div class="modern-form-grid-3">
                    <!-- Company Name -->
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="company_name">
                            <?= e(__('company.name', [], 'Official Company / Brand Name')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-building"></i></span>
                            <input type="text" id="company_name" name="company_name" class="modern-input-pill" required placeholder="<?= e(__('company.name_placeholder', [], 'e.g. MANASWI Tours & Travels')) ?>" value="<?= e($old['company_name'] ?? '') ?>" autocomplete="organization">
                        </div>
                        <?php if (!empty($errors['company_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['company_name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Operational Status -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="status">
                            <?= e(__('company.status', [], 'Operational Status')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-toggle-on"></i></span>
                            <select id="status" name="status" class="modern-select-pill" required>
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active Operating</option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive / Dormant</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Tagline / Slogan (Full Width) -->
                <div class="modern-form-grid-3" style="margin-top: 0.5rem;">
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="tagline">
                            <?= e(__('company.tagline', [], 'Company Tagline / Slogan')) ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-quote-left"></i></span>
                            <input type="text" id="tagline" name="tagline" class="modern-input-pill" placeholder="<?= e(__('company.tagline_placeholder', [], 'e.g. Tours Every Where — Premium Journeys')) ?>" value="<?= e($old['tagline'] ?? '') ?>">
                        </div>
                        <small style="color: #64748B; font-size: 0.78rem; margin-top: 4px; display: block;">Displays below the company name across the topbar and master email templates.</small>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="installation_role">
                            <?= e(__('company.installation_role', [], 'Installation Profile Role')) ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-star"></i></span>
                            <input type="text" id="installation_role" class="modern-input-pill" value="Primary Profile (Active)" readonly style="background: #F8FAFC; color: #176B5B; font-weight: 700; cursor: not-allowed;">
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/company') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: Contact Details                                                   -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel2" style="display: none;">
                <div class="modern-form-grid-3">
                    <!-- Email Address -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="email">
                            <?= e(__('company.email', [], 'Official Company Email')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="modern-input-pill" required placeholder="e.g. contact@yourcompany.com" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Contact Phone -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="phone">
                            <?= e(__('company.phone', [], 'Primary Contact Phone')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="phone" name="phone" class="modern-input-pill" required placeholder="e.g. +91 98765 43210" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel">
                        </div>
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Website URL -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="website">
                            <?= e(__('company.website', [], 'Official Website URL')) ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                            <input type="url" id="website" name="website" class="modern-input-pill" placeholder="e.g. https://www.yourcompany.com" value="<?= e($old['website'] ?? '') ?>">
                        </div>
                        <?php if (!empty($errors['website'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['website'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modern-form-grid-3" style="margin-top: 0.5rem;">
                    <!-- Alternate / Support Phone -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="alternate_phone">Support Hotline / Toll-Free</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                            <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" placeholder="e.g. 1800-123-4567" value="<?= e($old['alternate_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Dispatch Emergency Desk -->
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="emergency_phone">Dispatch &amp; Emergency Helpline</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-headset"></i></span>
                            <input type="tel" id="emergency_phone" name="emergency_phone" class="modern-input-pill" placeholder="e.g. +91 70201 23835 (24/7 Operations Duty)" value="<?= e($old['emergency_phone'] ?? '') ?>">
                        </div>
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
            <!-- STEP 3: Location Details                                                  -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel3" style="display: none;">
                <!-- Row 1: Address (2 Columns), City (1 Column) -->
                <div class="modern-form-grid-3">
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="address_line">
                            <?= e(__('company.address', [], 'Headquarters / Office Street Address')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="address_line" name="address_line" class="modern-input-pill" required placeholder="Building, Street, Landmark (e.g. Near Sai Mandir, Khaperkheda)" value="<?= e($old['address_line'] ?? $old['address'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="city">
                            City / District <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                            <input type="text" id="city" name="city" class="modern-input-pill" required placeholder="e.g. Nagpur" value="<?= e($old['city'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Row 2: State, Country, Postal Code (3 Columns) -->
                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="state">
                            State / Province <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                            <input type="text" id="state" name="state" class="modern-input-pill" required placeholder="e.g. Maharashtra" value="<?= e($old['state'] ?? 'Maharashtra') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="country">
                            Country <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-earth-americas"></i></span>
                            <input type="text" id="country" name="country" class="modern-input-pill" required placeholder="e.g. India" value="<?= e($old['country'] ?? 'India') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="postal_code">
                            Postal PIN Code <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="postal_code" name="postal_code" class="modern-input-pill" required placeholder="e.g. 441102" value="<?= e($old['postal_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Hidden consolidated address fallback -->
                <input type="hidden" name="address" id="fullAddressField" value="<?= e($old['address'] ?? '') ?>">

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
            <!-- STEP 4: Review & Save                                                     -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel4" style="display: none;">
                <!-- Live Executive Review Dossier Container (Exact match to office/customers/create) -->
                <div class="review-dossier-wrapper">

                    <!-- Profile Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container">
                            <div class="review-avatar-monogram" id="sumLogoMonogram">
                                <span id="sumAvatarInitial"><i class="fa-solid fa-building"></i></span>
                            </div>
                            <img id="sumLogoImg" src="" alt="Company Logo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-id-badge"></i>
                                    <code>VG-COMP-PRIMARY</code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item" id="sumHeroTagline">
                                    <i class="fa-solid fa-quote-left"></i> <span id="sumTaglineText">—</span>
                                </span>
                                <span class="review-status-pill is-active" id="sumStatusPill">
                                    <i class="fa-solid fa-circle-check"></i> Active Profile
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Comprehensive Information Section Cards -->
                    <div class="review-sections-grid">

                        <!-- Card 1: Company Identity -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-building"></i> Company Identity
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Company / Brand Name</span>
                                    <span class="review-value font-bold" id="sumCardName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Tagline / Slogan</span>
                                    <span class="review-value" id="sumCardTagline">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Brand Logo</span>
                                    <span class="review-value highlight-teal" id="sumCardLogoStatus">Default Brand Icon</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Operating Status</span>
                                    <span class="review-value capitalize" id="sumCardStatus">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Contact Details -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-address-book"></i> Contact Details
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Official Email</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardEmail">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Primary Phone</span>
                                    <span class="review-value" id="sumCardPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Official Website</span>
                                    <span class="review-value" id="sumCardWebsite">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Support Phone</span>
                                    <span class="review-value" id="sumCardAltPhone">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Location Details -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-map-location-dot"></i> Location &amp; Headquarters
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Office Address</span>
                                    <span class="review-value" id="sumCardAddress">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">City &amp; PIN Code</span>
                                    <span class="review-value" id="sumCardCityPin">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">State &amp; Country</span>
                                    <span class="review-value" id="sumCardStateCountry">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: System Integration -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-sliders"></i> Dynamic Branding Integration
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Sidebar &amp; Navigation</span>
                                    <span class="review-value highlight-teal"><i class="fa-solid fa-check"></i> Dynamic Logo &amp; Name</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Email Notification System</span>
                                    <span class="review-value highlight-teal"><i class="fa-solid fa-check"></i> Branded Templates</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Invoices &amp; Receipts</span>
                                    <span class="review-value highlight-teal"><i class="fa-solid fa-check"></i> Company Letterhead</span>
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
                    <button type="submit" class="modern-btn-submit" id="btnSubmitCompany">
                        <i class="fa-solid fa-circle-check"></i> <span id="btnSubmitCompanyText">Confirm &amp; Initialize Company</span>
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
// Wizard State & Navigation (4-Step Process Matching customers/create)
let currentStep = 1;
const totalSteps = 4;
let logoDataUrl = null;

function updateStepperUI(activeStep) {
    currentStep = activeStep;

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

    if (activeStep === 4) {
        populateSummaryDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    const targetPanel = document.getElementById('wizardPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateStepperUI(targetStep);

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
    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            field.classList.add('is-invalid', 'vg-input-error');
            isValid = false;
        } else {
            field.classList.remove('is-invalid', 'vg-input-error');
        }
    });
    return isValid;
}

function wizardNextStep(current) {
    if (!wizardValidateCurrentStep(current)) {
        return;
    }
    wizardGoToStep(current + 1);
}

function populateSummaryDossier() {
    const companyName = (document.getElementById('company_name')?.value || '').trim() || '(Company Name)';
    const tagline = (document.getElementById('tagline')?.value || '').trim() || '(Tagline / Slogan)';
    const email = (document.getElementById('email')?.value || '').trim() || '—';
    const phone = (document.getElementById('phone')?.value || '').trim() || '—';
    const altPhone = (document.getElementById('alternate_phone')?.value || '').trim() || '—';
    const website = (document.getElementById('website')?.value || '').trim() || '—';
    const status = document.getElementById('status')?.value || 'active';

    const addrLine = (document.getElementById('address_line')?.value || '').trim();
    const city = (document.getElementById('city')?.value || '').trim();
    const state = (document.getElementById('state')?.value || '').trim();
    const country = (document.getElementById('country')?.value || '').trim();
    const pin = (document.getElementById('postal_code')?.value || '').trim();

    // Consolidate full address
    const addressParts = [addrLine, city, state, country, pin].filter(Boolean);
    const fullAddress = addressParts.join(', ') || '—';
    const fullAddrHidden = document.getElementById('fullAddressField');
    if (fullAddrHidden) fullAddrHidden.value = fullAddress;

    // Hero updates
    const heroName = document.getElementById('sumHeroName');
    if (heroName) heroName.textContent = companyName;

    const heroTagline = document.getElementById('sumHeroTagline');
    if (heroTagline) heroTagline.innerHTML = '<i class="fa-solid fa-quote-left" style="opacity: 0.7; margin-right: 4px;"></i> ' + tagline;

    const statusPill = document.getElementById('sumStatusPill');
    if (statusPill) {
        if (status === 'active') {
            statusPill.innerHTML = '<i class="fa-solid fa-circle-check"></i> Active Profile';
            statusPill.style.background = '#10B981';
        } else {
            statusPill.innerHTML = '<i class="fa-solid fa-circle-pause"></i> Inactive Profile';
            statusPill.style.background = '#94A3B8';
        }
    }

    // Logo Preview
    const sumImg = document.getElementById('sumLogoImg');
    const sumIcon = document.getElementById('sumLogoMonogram');
    const sumStatus = document.getElementById('sumCardLogoStatus');
    if (logoDataUrl && sumImg && sumIcon) {
        sumImg.src = logoDataUrl;
        sumImg.style.display = 'block';
        sumIcon.style.display = 'none';
        if (sumStatus) sumStatus.textContent = 'Custom Logo Attached';
    } else if (sumImg && sumIcon) {
        sumImg.style.display = 'none';
        sumIcon.style.display = 'flex';
        if (sumStatus) sumStatus.textContent = 'Default Brand Icon';
    }

    // Card fields
    const cName = document.getElementById('sumCardName');
    if (cName) cName.textContent = companyName;

    const cTag = document.getElementById('sumCardTagline');
    if (cTag) cTag.textContent = tagline;

    const cEmail = document.getElementById('sumCardEmail');
    if (cEmail) cEmail.textContent = email;

    const cPhone = document.getElementById('sumCardPhone');
    if (cPhone) cPhone.textContent = phone;

    const cAlt = document.getElementById('sumCardAltPhone');
    if (cAlt) cAlt.textContent = altPhone;

    const cWeb = document.getElementById('sumCardWebsite');
    if (cWeb) cWeb.textContent = website;

    const cAddr = document.getElementById('sumCardAddress');
    if (cAddr) cAddr.textContent = addrLine || '—';

    const cCityPin = document.getElementById('sumCardCityPin');
    if (cCityPin) cCityPin.textContent = (city ? city : '') + (pin ? ' - ' + pin : '');

    const cStateCountry = document.getElementById('sumCardStateCountry');
    if (cStateCountry) cStateCountry.textContent = (state ? state : '') + (country ? ', ' + country : '');

    const cStat = document.getElementById('sumCardStatus');
    if (cStat) {
        cStat.textContent = status === 'active' ? 'Active Operating' : 'Inactive Dormant';
        cStat.style.color = status === 'active' ? '#10B981' : '#64748B';
    }
}

// Logo File Handler (Base64 + File Mirroring)
function handleLogoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Logo file size must be less than 5MB.', 'error');
        } else {
            alert('Logo file size must be less than 5MB.');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        logoDataUrl = e.target.result;
        const previewImg = document.getElementById('logoPreviewImg');
        const defaultIcon = document.getElementById('logoDefaultIcon');
        const removeBtn = document.getElementById('btnRemoveLogo');
        const base64Input = document.getElementById('logo_base64');
        const mirrorInput = document.getElementById('profile_photo_base64');

        if (previewImg) {
            previewImg.src = logoDataUrl;
            previewImg.style.display = 'block';
        }
        if (defaultIcon) defaultIcon.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'inline-flex';
        if (base64Input) base64Input.value = logoDataUrl;
        if (mirrorInput) mirrorInput.value = logoDataUrl;
    };
    reader.readAsDataURL(file);
}

function clearLogoSelection() {
    logoDataUrl = null;
    const input = document.getElementById('logo');
    const previewImg = document.getElementById('logoPreviewImg');
    const defaultIcon = document.getElementById('logoDefaultIcon');
    const removeBtn = document.getElementById('btnRemoveLogo');
    const base64Input = document.getElementById('logo_base64');
    const mirrorInput = document.getElementById('profile_photo_base64');

    if (input) input.value = '';
    if (base64Input) base64Input.value = '';
    if (mirrorInput) mirrorInput.value = '';
    if (previewImg) {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
    if (defaultIcon) defaultIcon.style.display = 'flex';
    if (removeBtn) removeBtn.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createCompanyForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSubmitCompany');
            const btnText = document.getElementById('btnSubmitCompanyText');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                if (btnText) btnText.textContent = 'Saving Profile...';
            }
        });
    }
});
</script>

<style>
/* Exact styling matching office/customers/create.php */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem;
}

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

/* Master Form Card */
.modern-form-card {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border-radius: 24px;
    border: 1.5px solid #E6EEF8;
    box-shadow: 0 12px 36px rgba(12, 43, 82, 0.04), 0 2px 8px rgba(12, 43, 82, 0.02);
    padding: 2.5rem 2.75rem;
    position: relative;
}

/* Avatar Upload Card */
.customer-avatar-upload-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem 1.5rem;
    background: #F8FAFD;
    border: 1.5px dashed #CBD5E1;
    border-radius: 20px;
    margin-bottom: 2rem;
    transition: all 0.2s ease;
}

.customer-avatar-upload-card:hover {
    border-color: #176B5B;
    background: #F0FDF4;
}

.customer-avatar-preview-wrap {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 2px solid #CBD5E1;
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(12, 43, 82, 0.08);
}

.avatar-default-icon {
    font-size: 2.2rem;
    color: #94A3B8;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-camera-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    border: 2px solid #FFFFFF;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.customer-avatar-meta {
    flex: 1;
}

.customer-avatar-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}

.customer-avatar-label {
    font-size: 1rem;
    font-weight: 700;
    color: #0C2B52;
}

.modern-badge-subtle {
    font-size: 0.72rem;
    padding: 2px 8px;
    border-radius: 6px;
    background: #E2E8F0;
    color: #475569;
    font-weight: 600;
}

.customer-avatar-help {
    font-size: 0.84rem;
    color: #64748B;
    margin: 0 0 0.75rem 0;
    line-height: 1.45;
}

.customer-avatar-actions {
    display: flex;
    gap: 0.65rem;
}

.customer-avatar-btn-upload {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 1rem;
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.customer-avatar-btn-upload:hover {
    background: #125246;
    transform: translateY(-1px);
}

.customer-avatar-btn-remove {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
    border-radius: 8px;
    padding: 0.45rem 1rem;
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.customer-avatar-btn-remove:hover {
    background: #FCA5A5;
}

/* Grids and inputs */
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.modern-col-span-2 {
    grid-column: span 2;
}

.modern-form-group {
    display: flex;
    flex-direction: column;
}

.modern-form-label {
    font-size: 0.86rem;
    font-weight: 700;
    color: #0C2B52;
    margin-bottom: 0.45rem;
}

.modern-form-label .req {
    color: #DC2626;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.modern-input-icon {
    position: absolute;
    left: 1.1rem;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
}

.modern-input-pill,
.modern-select-pill {
    width: 100%;
    padding: 0.72rem 1.15rem 0.72rem 2.85rem;
    background: #F8FAFD;
    border: 1.5px solid #CBD5E1;
    border-radius: 50px;
    font-size: 0.92rem;
    color: #0C2B52;
    transition: all 0.2s ease;
    outline: none;
}

.modern-input-pill:focus,
.modern-select-pill:focus {
    background: #FFFFFF;
    border-color: #176B5B;
    box-shadow: 0 0 0 3.5px rgba(23, 107, 91, 0.15);
}

.modern-field-error {
    color: #DC2626;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.35rem;
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #E2E8F0;
}

.modern-btn-cancel,
.modern-btn-prev {
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #CBD5E1;
    border-radius: 50px;
    padding: 0.65rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.modern-btn-cancel:hover,
.modern-btn-prev:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.modern-btn-next,
.modern-btn-submit {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 50px;
    padding: 0.65rem 1.85rem;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    transition: all 0.2s ease;
}

.modern-btn-next:hover,
.modern-btn-submit:hover {
    background: #125246;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.32);
}

/* Review Dossier Styles */
.review-dossier-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-hero-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem 1.75rem;
    background: linear-gradient(135deg, #071F50 0%, #0F3775 100%);
    border-radius: 20px;
    color: #FFFFFF;
    box-shadow: 0 8px 24px rgba(7, 31, 80, 0.15);
}

.review-avatar-container {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 3px solid rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.review-avatar-monogram {
    font-size: 2rem;
    color: #176B5B;
}

.review-hero-meta {
    flex: 1;
}

.review-hero-title-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.4rem;
    flex-wrap: wrap;
}

.review-hero-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0;
}

.review-code-badge {
    background: rgba(255, 255, 255, 0.15);
    padding: 2px 10px;
    border-radius: 6px;
    font-size: 0.82rem;
}

.review-hero-tags {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.86rem;
    opacity: 0.95;
}

.review-status-pill.is-active {
    background: #10B981;
    color: #FFFFFF;
    padding: 2px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
}

.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.review-section-card {
    background: #F8FAFD;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
}

.review-card-header {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0C2B52;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #E2E8F0;
    padding-bottom: 0.5rem;
}

.review-card-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.review-info-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.review-label {
    color: #64748B;
    font-weight: 500;
}

.review-value {
    color: #0C2B52;
    font-weight: 600;
    text-align: right;
}

.highlight-teal {
    color: #176B5B !important;
}

/* Responsive queries matching customers/create */
@media (max-width: 991px) {
    .modern-form-card {
        padding: 1.75rem 1.25rem;
        border-radius: 20px;
    }
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
    }
    .modern-col-span-2 {
        grid-column: span 1;
    }
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
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
</style>
