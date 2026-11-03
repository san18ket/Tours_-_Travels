<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Modern Company Profile Management Form
 * Exact implementation matching office/customers/create.php layout, 4-step stepper, pill inputs, review dossier, and responsiveness
 */

use App\Core\Session;

$company = $company ?? $primaryCompany ?? [];
$isExisting = !empty($company) && !empty($company['id']);

// Image URLs from saved profile
$logoUrl = !empty($company['logo']) ? storage_url($company['logo']) : null;
$paidStampUrl = !empty($company['paid_stamp']) ? storage_url($company['paid_stamp']) : null;
$signatureUrl = !empty($company['signature']) ? storage_url($company['signature']) : null;
$sigStampUrl = !empty($company['signature_stamp']) ? storage_url($company['signature_stamp']) : null;

// Form values with fallback to old input or company record
$val = function(string $key, string $default = '') use ($company, $old): string {
    if (isset($old[$key])) {
        return (string)$old[$key];
    }
    return isset($company[$key]) ? (string)$company[$key] : $default;
};
?>

<div class="modern-form-wrapper">
    <!-- Reference: <?= url('/office/company/create') ?> -->

    <!-- Centered Professional 4-Step Stepper Header (Exact Match to office/customers/create.php) -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Brand &amp; Identity</span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Contact &amp; Address</span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Document Images</span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label">Review &amp; Save</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Flash Messages / Alerts -->
    <?php 
    $successMsg = $success ?? Session::getFlash('success');
    if (!empty($successMsg)): 
    ?>
        <div class="alert alert-success" style="background: #ECFDF5; border: 1.5px solid #A7F3D0; border-radius: 16px; padding: 1.15rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.08);">
            <div style="display: flex; align-items: center; gap: 0.85rem; color: #065F46; font-size: 0.95rem; font-weight: 600;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <span><?= e($successMsg) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:#065F46; cursor:pointer; font-size:1.1rem;"><i class="fa-solid fa-xmark"></i></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.08);">
            <div style="display: flex; gap: 0.85rem; align-items: flex-start; color: #991B1B;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; margin-top: 2px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div style="flex: 1;">
                    <h4 style="font-size: 1rem; font-weight: 700; margin: 0 0 0.35rem 0; color: #991B1B;">Please correct the highlighted issues:</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.5;">
                        <?php foreach ($errors as $fieldKey => $fieldErrs): ?>
                            <?php foreach ((array)$fieldErrs as $errMsg): ?>
                                <li><?= e($errMsg) ?></li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Master Form Container -->
    <form action="<?= url('/office/company') ?>" method="POST" id="companyProfileForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php if ($isExisting): ?>
            <input type="hidden" name="id" value="<?= e($company['id']) ?>">
        <?php endif; ?>

        <!-- Hidden Removal Flags for the 4 Images -->
        <input type="hidden" name="remove_logo" id="remove_logo" value="0">
        <input type="hidden" name="remove_paid_stamp" id="remove_paid_stamp" value="0">
        <input type="hidden" name="remove_signature" id="remove_signature" value="0">
        <input type="hidden" name="remove_signature_stamp" id="remove_signature_stamp" value="0">

        <div class="modern-form-card">

            <!-- ========================================================================= -->
            <!-- STEP 1: Brand & Identity                                                  -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel1">

                <!-- Professional Company Logo Upload Component (Exact Match to customers/create avatar card) -->
                <div class="customer-avatar-upload-card">
                    <div class="customer-avatar-preview-wrap" id="logoDropZone" onclick="document.getElementById('logo_input').click()" title="Click to upload company logo">
                        <input type="file" id="logo_input" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleLogoChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="logo_base64" id="logo_base64" value="">
                        
                        <div class="avatar-default-icon" id="logoDefaultIcon" style="<?= !empty($logoUrl) ? 'display: none;' : '' ?>">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <img id="logoPreviewImg" src="<?= !empty($logoUrl) ? e($logoUrl) : '' ?>" alt="Company Logo" style="<?= !empty($logoUrl) ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: contain; border-radius: 50%; padding: 4px; background: #FFFFFF;">
                        
                        <div class="avatar-camera-badge" title="Upload Logo">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <span id="logoRemovedBadge" style="display: none; position: absolute; top: 0; right: 0; background: #EF4444; color: #FFF; font-size: 0.65rem; font-weight: 800; padding: 2px 7px; border-radius: 50px; z-index: 5;">REMOVED</span>
                    </div>

                    <div class="customer-avatar-meta">
                        <div class="customer-avatar-title-row">
                            <span class="customer-avatar-label">Section A: Company Logo</span>
                            <span class="modern-badge-subtle"><?= !empty($logoUrl) ? 'Uploaded' : 'Recommended' ?></span>
                        </div>
                        <p class="customer-avatar-help">Upload high resolution company logo. Used across invoices, receipts, navigation sidebar, login screen, and email templates. Supported: PNG, JPG, WEBP (Max 5MB).</p>
                        <div class="customer-avatar-actions">
                            <button type="button" class="customer-avatar-btn-upload" onclick="document.getElementById('logo_input').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> <span id="logoUploadBtnText"><?= !empty($logoUrl) ? 'Change Logo' : 'Upload Logo' ?></span>
                            </button>
                            <button type="button" class="customer-avatar-btn-remove" id="btnRemoveLogo" style="<?= empty($logoUrl) ? 'display: none;' : 'display: inline-flex;' ?>" onclick="markImageRemoval('logo')">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                            <button type="button" class="customer-avatar-btn-undo" id="btnUndoLogo" style="display: none; background: #DBEAFE; color: #1E40AF; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer;" onclick="undoImageRemoval('logo', '<?= e($logoUrl ?? '') ?>')">
                                <i class="fa-solid fa-rotate-left"></i> Undo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section B: Basic Company Details Heading -->
                <div style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; margin: 1.5rem 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-building-circle-check" style="color: #6366F1;"></i> Section B: Basic Company Details
                </div>

                <!-- Row 1: Company Name, Owner/Contact Person, Tagline -->
                <div class="modern-form-grid-3">
                    <!-- Company Name -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="company_name">
                            Company Name <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-building"></i></span>
                            <input type="text" id="company_name" name="company_name" class="modern-input-pill <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>" required placeholder="e.g. Manaswi Tours &amp; Travels" value="<?= e($val('company_name')) ?>" maxlength="150" autocomplete="organization">
                        </div>
                        <?php if (isset($errors['company_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['company_name']) ? $errors['company_name'][0] : $errors['company_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Owner / Contact Person -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="owner_name">
                            Owner / Contact Person
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-tie"></i></span>
                            <input type="text" id="owner_name" name="owner_name" class="modern-input-pill" placeholder="e.g. Rajesh Sharma" value="<?= e($val('owner_name')) ?>" maxlength="150" autocomplete="name">
                        </div>
                    </div>

                    <!-- Company Tagline -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="tagline">
                            Company Tagline
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-quote-left"></i></span>
                            <input type="text" id="tagline" name="tagline" class="modern-input-pill" placeholder="e.g. Safe Journey &bull; Happy Memories" value="<?= e($val('tagline')) ?>" maxlength="255">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Status, GST Number, PAN Number -->
                <div class="modern-form-grid-3">
                    <!-- Profile Status -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="status">
                            Profile Status <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-toggle-on"></i></span>
                            <select id="status" name="status" class="modern-select-pill">
                                <option value="active" <?= $val('status', 'active') === 'active' ? 'selected' : '' ?>>Active Profile</option>
                                <option value="inactive" <?= $val('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- GST Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="gst_number">
                            GST Number (GSTIN)
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-file-invoice"></i></span>
                            <input type="text" id="gst_number" name="gst_number" class="modern-input-pill uppercase" placeholder="e.g. 27AABCM8899Q1ZX" value="<?= e($val('gst_number')) ?>" maxlength="50" style="text-transform: uppercase;">
                        </div>
                        <?php if (isset($errors['gst_number'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['gst_number']) ? $errors['gst_number'][0] : $errors['gst_number']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- PAN Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="pan_number">
                            PAN Number
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <input type="text" id="pan_number" name="pan_number" class="modern-input-pill uppercase" placeholder="e.g. AABCM8899Q" value="<?= e($val('pan_number')) ?>" maxlength="50" style="text-transform: uppercase;">
                        </div>
                        <?php if (isset($errors['pan_number'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['pan_number']) ? $errors['pan_number'][0] : $errors['pan_number']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/dashboard') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: Contact & Address                                                 -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel2" style="display: none;">

                <!-- Row 1: Mobile Number, Alternate Mobile, Email Address -->
                <div class="modern-form-grid-3">
                    <!-- Mobile Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="phone">
                            Mobile Number <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="phone" name="phone" class="modern-input-pill <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" required placeholder="e.g. +91 98220 12345" value="<?= e($val('phone')) ?>" maxlength="30" autocomplete="tel">
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['phone']) ? $errors['phone'][0] : $errors['phone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Alternate Mobile -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="alternate_phone">
                            Alternate Mobile Number
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                            <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" placeholder="e.g. +91 94220 67890" value="<?= e($val('alternate_phone')) ?>" maxlength="30">
                        </div>
                        <?php if (isset($errors['alternate_phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['alternate_phone']) ? $errors['alternate_phone'][0] : $errors['alternate_phone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email Address -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="email">
                            Official Email Address
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="modern-input-pill <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="contact@manaswitravels.com" value="<?= e($val('email')) ?>" maxlength="150" autocomplete="email">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 2: Website, City, State -->
                <div class="modern-form-grid-3">
                    <!-- Website URL -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="website">
                            Website URL
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                            <input type="url" id="website" name="website" class="modern-input-pill" placeholder="https://www.manaswitravels.com" value="<?= e($val('website')) ?>" maxlength="255">
                        </div>
                        <?php if (isset($errors['website'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['website']) ? $errors['website'][0] : $errors['website']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- City -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="city">
                            City <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                            <input type="text" id="city" name="city" class="modern-input-pill <?= isset($errors['city']) ? 'is-invalid' : '' ?>" required placeholder="e.g. Pune" value="<?= e($val('city')) ?>" maxlength="100">
                        </div>
                        <?php if (isset($errors['city'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['city']) ? $errors['city'][0] : $errors['city']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- State -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="state">
                            State <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                            <input type="text" id="state" name="state" class="modern-input-pill <?= isset($errors['state']) ? 'is-invalid' : '' ?>" required placeholder="e.g. Maharashtra" value="<?= e($val('state')) ?>" maxlength="100">
                        </div>
                        <?php if (isset($errors['state'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['state']) ? $errors['state'][0] : $errors['state']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 3: Office Address (2 cols) & PIN Code (1 col) -->
                <div class="modern-form-grid-3">
                    <!-- Office Address -->
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="address">
                            Office Address <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="address" name="address" class="modern-input-pill <?= isset($errors['address']) ? 'is-invalid' : '' ?>" required placeholder="e.g. Shop No. 4, Ground Floor, Royal Commercial Complex, Station Road" value="<?= e($val('address')) ?>">
                        </div>
                        <?php if (isset($errors['address'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['address']) ? $errors['address'][0] : $errors['address']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- PIN Code -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="pin_code">
                            PIN Code <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="pin_code" name="pin_code" class="modern-input-pill <?= isset($errors['pin_code']) ? 'is-invalid' : '' ?>" required placeholder="e.g. 411001" value="<?= e($val('pin_code')) ?>" maxlength="6" pattern="[1-9][0-9]{5}">
                        </div>
                        <?php if (isset($errors['pin_code'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e(is_array($errors['pin_code']) ? $errors['pin_code'][0] : $errors['pin_code']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bottom Actions -->
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
            <!-- STEP 3: Document Images (Stamps & Signatures)                             -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel3" style="display: none;">

                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #0C2B52; margin: 0 0 0.35rem 0;">Section C: Company Document Images</h3>
                    <p style="font-size: 0.86rem; color: #64748B; margin: 0;">Provide 3 distinct images for formal billing. Each document image has its own independent preview and upload controls.</p>
                </div>

                <!-- 3 Modern Document Cards matching customer dropzone styling -->
                <div class="modern-form-grid-3">

                    <!-- 1. PAID STAMP IMAGE CARD -->
                    <div class="doc-upload-box" style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 18px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-shadow: 0 4px 14px rgba(12, 43, 82, 0.04);">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="document-dropzone-halo" style="width: 42px; height: 42px; font-size: 1.15rem; margin: 0; background: #ECFDF5; color: #059669;">
                                        <i class="fa-solid fa-stamp"></i>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #0C2B52;">1. Paid Stamp</span>
                                </div>
                                <span class="modern-badge-subtle" style="background: #D1FAE5; color: #065F46;">Paid Invoices</span>
                            </div>
                            <p style="font-size: 0.8rem; color: #64748B; margin-bottom: 1rem; min-height: 38px;">
                                Company-approved PAID stamp image. Automatically applied to invoice receipts only when fully paid.
                            </p>

                            <!-- Dropzone Preview Area -->
                            <div class="document-dropzone" id="dropzonePaidStamp" style="padding: 1.25rem 1rem; min-height: 140px; display: flex; align-items: center; justify-content: center;" onclick="document.getElementById('paid_stamp_input').click()">
                                <input type="file" id="paid_stamp_input" name="paid_stamp" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocImageChange(this, 'paid_stamp')" onclick="event.stopPropagation()">
                                <input type="hidden" name="paid_stamp_base64" id="paid_stamp_base64" value="">

                                <img id="paidStampPreviewImg" src="<?= !empty($paidStampUrl) ? e($paidStampUrl) : '' ?>" alt="Paid Stamp" style="<?= !empty($paidStampUrl) ? 'display: block;' : 'display: none;' ?> max-width: 90%; max-height: 110px; object-fit: contain;">

                                <div id="paidStampPlaceholder" style="<?= !empty($paidStampUrl) ? 'display: none;' : 'display: block;' ?> text-align: center;">
                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.8rem; color: #10B981; margin-bottom: 0.35rem;"></i>
                                    <div class="document-dropzone-prompt" style="font-size: 0.82rem; margin: 0;">Click or drop <strong>PAID stamp</strong></div>
                                </div>
                                <span id="paidStampRemovedBadge" style="display: none; position: absolute; top: 10px; right: 10px; background: #EF4444; color: #FFF; font-size: 0.65rem; font-weight: 800; padding: 2px 7px; border-radius: 50px;">REMOVED</span>
                            </div>
                        </div>

                        <!-- Card Buttons -->
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('paid_stamp_input').click()" style="flex: 1; font-weight: 700; border-radius: 8px;">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> <span id="paidStampBtnText"><?= !empty($paidStampUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="removePaidStampBtn" class="btn btn-outline btn-sm" onclick="markImageRemoval('paid_stamp')" style="color: #DC2626; border-color: #FCA5A5; border-radius: 8px; <?= empty($paidStampUrl) ? 'display: none;' : 'display: inline-flex;' ?>" title="Remove Stamp">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="undoPaidStampBtn" class="btn btn-outline btn-sm" onclick="undoImageRemoval('paid_stamp', '<?= e($paidStampUrl ?? '') ?>')" style="color: #2563EB; border-color: #BFDBFE; border-radius: 8px; display: none;">
                                <i class="fa-solid fa-rotate-left"></i> Undo
                            </button>
                        </div>
                    </div>

                    <!-- 2. AUTHORIZED SIGNATURE IMAGE CARD -->
                    <div class="doc-upload-box" style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 18px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-shadow: 0 4px 14px rgba(12, 43, 82, 0.04);">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="document-dropzone-halo" style="width: 42px; height: 42px; font-size: 1.15rem; margin: 0; background: #EFF6FF; color: #2563EB;">
                                        <i class="fa-solid fa-signature"></i>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #0C2B52;">2. Authorized Sign</span>
                                </div>
                                <span class="modern-badge-subtle" style="background: #DBEAFE; color: #1E40AF;">Proprietor</span>
                            </div>
                            <p style="font-size: 0.8rem; color: #64748B; margin-bottom: 1rem; min-height: 38px;">
                                Signature of business owner / manager. Selectable on invoice print for formal authorization.
                            </p>

                            <!-- Dropzone Preview Area -->
                            <div class="document-dropzone" id="dropzoneSignature" style="padding: 1.25rem 1rem; min-height: 140px; display: flex; align-items: center; justify-content: center;" onclick="document.getElementById('signature_input').click()">
                                <input type="file" id="signature_input" name="signature" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocImageChange(this, 'signature')" onclick="event.stopPropagation()">
                                <input type="hidden" name="signature_base64" id="signature_base64" value="">

                                <img id="signaturePreviewImg" src="<?= !empty($signatureUrl) ? e($signatureUrl) : '' ?>" alt="Authorized Signature" style="<?= !empty($signatureUrl) ? 'display: block;' : 'display: none;' ?> max-width: 90%; max-height: 110px; object-fit: contain;">

                                <div id="signaturePlaceholder" style="<?= !empty($signatureUrl) ? 'display: none;' : 'display: block;' ?> text-align: center;">
                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.8rem; color: #2563EB; margin-bottom: 0.35rem;"></i>
                                    <div class="document-dropzone-prompt" style="font-size: 0.82rem; margin: 0;">Click or drop <strong>signature</strong></div>
                                </div>
                                <span id="signatureRemovedBadge" style="display: none; position: absolute; top: 10px; right: 10px; background: #EF4444; color: #FFF; font-size: 0.65rem; font-weight: 800; padding: 2px 7px; border-radius: 50px;">REMOVED</span>
                            </div>
                        </div>

                        <!-- Card Buttons -->
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('signature_input').click()" style="flex: 1; font-weight: 700; border-radius: 8px;">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> <span id="signatureBtnText"><?= !empty($signatureUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="removeSignatureBtn" class="btn btn-outline btn-sm" onclick="markImageRemoval('signature')" style="color: #DC2626; border-color: #FCA5A5; border-radius: 8px; <?= empty($signatureUrl) ? 'display: none;' : 'display: inline-flex;' ?>" title="Remove Signature">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="undoSignatureBtn" class="btn btn-outline btn-sm" onclick="undoImageRemoval('signature', '<?= e($signatureUrl ?? '') ?>')" style="color: #2563EB; border-color: #BFDBFE; border-radius: 8px; display: none;">
                                <i class="fa-solid fa-rotate-left"></i> Undo
                            </button>
                        </div>
                    </div>

                    <!-- 3. SIGNATURE + COMPANY STAMP IMAGE CARD -->
                    <div class="doc-upload-box" style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 18px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-shadow: 0 4px 14px rgba(12, 43, 82, 0.04);">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="document-dropzone-halo" style="width: 42px; height: 42px; font-size: 1.15rem; margin: 0; background: #F5F3FF; color: #7C3AED;">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </div>
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #0C2B52;">3. Sign + Stamp</span>
                                </div>
                                <span class="modern-badge-subtle" style="background: #EDE9FE; color: #6D28D9;">Combined Seal</span>
                            </div>
                            <p style="font-size: 0.8rem; color: #64748B; margin-bottom: 1rem; min-height: 38px;">
                                Combined proprietor signature and official company rubber seal for statutory compliance.
                            </p>

                            <!-- Dropzone Preview Area -->
                            <div class="document-dropzone" id="dropzoneSigStamp" style="padding: 1.25rem 1rem; min-height: 140px; display: flex; align-items: center; justify-content: center;" onclick="document.getElementById('signature_stamp_input').click()">
                                <input type="file" id="signature_stamp_input" name="signature_stamp" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocImageChange(this, 'signature_stamp')" onclick="event.stopPropagation()">
                                <input type="hidden" name="signature_stamp_base64" id="signature_stamp_base64" value="">

                                <img id="sigStampPreviewImg" src="<?= !empty($sigStampUrl) ? e($sigStampUrl) : '' ?>" alt="Signature &amp; Stamp" style="<?= !empty($sigStampUrl) ? 'display: block;' : 'display: none;' ?> max-width: 90%; max-height: 110px; object-fit: contain;">

                                <div id="sigStampPlaceholder" style="<?= !empty($sigStampUrl) ? 'display: none;' : 'display: block;' ?> text-align: center;">
                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.8rem; color: #7C3AED; margin-bottom: 0.35rem;"></i>
                                    <div class="document-dropzone-prompt" style="font-size: 0.82rem; margin: 0;">Click or drop <strong>seal + sign</strong></div>
                                </div>
                                <span id="sigStampRemovedBadge" style="display: none; position: absolute; top: 10px; right: 10px; background: #EF4444; color: #FFF; font-size: 0.65rem; font-weight: 800; padding: 2px 7px; border-radius: 50px;">REMOVED</span>
                            </div>
                        </div>

                        <!-- Card Buttons -->
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('signature_stamp_input').click()" style="flex: 1; font-weight: 700; border-radius: 8px;">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> <span id="sigStampBtnText"><?= !empty($sigStampUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="removeSigStampBtn" class="btn btn-outline btn-sm" onclick="markImageRemoval('signature_stamp')" style="color: #DC2626; border-color: #FCA5A5; border-radius: 8px; <?= empty($sigStampUrl) ? 'display: none;' : 'display: inline-flex;' ?>" title="Remove Seal">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="undoSigStampBtn" class="btn btn-outline btn-sm" onclick="undoImageRemoval('signature_stamp', '<?= e($sigStampUrl ?? '') ?>')" style="color: #2563EB; border-color: #BFDBFE; border-radius: 8px; display: none;">
                                <i class="fa-solid fa-rotate-left"></i> Undo
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Bottom Actions -->
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
            <!-- STEP 4: Review & Save (Exact Match to customers/create Review Dossier)    -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel4" style="display: none;">

                <!-- Live Executive Dossier Container -->
                <div class="review-dossier-wrapper">

                    <!-- Profile Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container" style="background: #FFFFFF;">
                            <div class="review-avatar-monogram" id="sumAvatarMonogram" style="<?= !empty($logoUrl) ? 'display: none;' : '' ?> background: linear-gradient(135deg, #176B5B 0%, #0C2B52 100%); width: 100%; height: 100%; border-radius: 50%;">
                                <span id="sumAvatarInitial">C</span>
                            </div>
                            <img id="sumAvatarImg" src="<?= !empty($logoUrl) ? e($logoUrl) : '' ?>" alt="Company Logo" style="<?= !empty($logoUrl) ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: contain; border-radius: 50%; padding: 4px;">
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <code>PRIMARY PROFILE</code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item" id="sumTagOwner">
                                    <i class="fa-solid fa-user-tie"></i> <span id="sumTagOwnerText">Owner: —</span>
                                </span>
                                <span class="review-tag-item" id="sumTagTagline">
                                    <i class="fa-solid fa-quote-left"></i> <span id="sumTagTaglineText">Safe Journey &bull; Happy Memories</span>
                                </span>
                                <span class="review-status-pill is-active" id="sumStatusPill">
                                    <i class="fa-solid fa-circle-check"></i> Active Profile
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Comprehensive Review Cards Grid -->
                    <div class="review-sections-grid">

                        <!-- Card 1: Company Identity -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-building"></i> Company Identity
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Company Name</span>
                                    <span class="review-value font-bold" id="sumCardCompanyName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Owner / Contact</span>
                                    <span class="review-value" id="sumCardOwnerName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Tagline</span>
                                    <span class="review-value" id="sumCardTagline">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Profile Status</span>
                                    <span class="review-value capitalize" id="sumCardStatus">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Contact & Registered Office -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-map-location-dot"></i> Contact &amp; Registered Office
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Primary Mobile</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Alternate Mobile</span>
                                    <span class="review-value" id="sumCardAltPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Official Email</span>
                                    <span class="review-value" id="sumCardEmail">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Website URL</span>
                                    <span class="review-value" id="sumCardWebsite">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Office Address</span>
                                    <span class="review-value" id="sumCardAddress">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">City, State &amp; PIN</span>
                                    <span class="review-value font-bold" id="sumCardCityStatePin">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Statutory & Tax Credentials -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Statutory &amp; Tax Credentials
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">GST Number (GSTIN)</span>
                                    <span class="review-value font-mono uppercase" id="sumCardGst">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">PAN Number</span>
                                    <span class="review-value font-mono uppercase" id="sumCardPan">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Installation Role</span>
                                    <span class="review-value" style="color: #059669; font-weight: 700;">
                                        <i class="fa-solid fa-circle-check"></i> Single Primary Source of Truth
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Document Images & Print Seals -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-stamp"></i> Document Images &amp; Seals
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Company Brand Logo</span>
                                    <span id="sumCardBadgeLogo" class="review-doc-badge empty">
                                        <i class="fa-solid fa-circle-info"></i> Not Uploaded
                                    </span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">PAID Stamp Image</span>
                                    <span id="sumCardBadgePaidStamp" class="review-doc-badge empty">
                                        <i class="fa-solid fa-circle-info"></i> Not Uploaded
                                    </span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Authorized Signature</span>
                                    <span id="sumCardBadgeSignature" class="review-doc-badge empty">
                                        <i class="fa-solid fa-circle-info"></i> Not Uploaded
                                    </span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Signature + Stamp</span>
                                    <span id="sumCardBadgeSigStamp" class="review-doc-badge empty">
                                        <i class="fa-solid fa-circle-info"></i> Not Uploaded
                                    </span>
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
                    <button type="submit" class="modern-btn-submit" id="btnSaveCompanyProfile">
                        <i class="fa-solid fa-check"></i> <span id="btnSaveCompanyProfileText">Save Company Profile</span>
                    </button>
                </div>
            </div>

        </div>

    </form>
</div>

<!-- ========================================================================= -->
<!-- EXACT STYLING FROM office/customers/create.php                            -->
<!-- ========================================================================= -->
<style>
/* Master Container & Header */
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
    transition: box-shadow 0.2s ease;
}

/* Avatar Upload Card */
.customer-avatar-upload-card {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    background: #F8FAFD;
    border: 1.5px solid #DCE6F2;
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 16px rgba(12, 43, 82, 0.03);
}

.customer-avatar-preview-wrap {
    width: 96px;
    height: 96px;
    min-width: 96px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 3px solid #176B5B;
    position: relative;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.customer-avatar-preview-wrap:hover {
    transform: scale(1.04);
    box-shadow: 0 8px 24px rgba(23, 107, 91, 0.25);
}

.avatar-default-icon {
    font-size: 2.5rem;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-camera-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    border: 2.5px solid #FFFFFF;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.customer-avatar-meta {
    flex: 1;
}

.customer-avatar-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.35rem;
}

.customer-avatar-label {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0C2B52;
}

.modern-badge-subtle {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    background: #E8F4F0;
    color: #176B5B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.customer-avatar-help {
    font-size: 0.85rem;
    color: #64748B;
    margin: 0 0 0.85rem 0;
    line-height: 1.45;
}

.customer-avatar-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.customer-avatar-btn-upload {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 10px;
    padding: 0.55rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.25);
    transition: all 0.2s ease;
}

.customer-avatar-btn-upload:hover {
    background: #125246;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(23, 107, 91, 0.35);
}

.customer-avatar-btn-remove {
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FECACA;
    border-radius: 10px;
    padding: 0.55rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.customer-avatar-btn-remove:hover {
    background: #FCA5A5;
    color: #7F1D1D;
}

/* Grids & Form Controls */
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.modern-col-span-2 {
    grid-column: span 2;
}

.modern-form-group {
    display: flex;
    flex-direction: column;
}

.modern-form-label {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0C2B52;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.modern-form-label .req {
    color: #EF4444;
    font-size: 1rem;
    line-height: 1;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.modern-input-icon {
    position: absolute;
    left: 1.15rem;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 1;
    transition: color 0.2s ease;
}

.modern-input-pill,
.modern-select-pill {
    width: 100%;
    height: 48px;
    background: #F8FAFD;
    border: 1.5px solid #DCE6F2;
    border-radius: 50px;
    padding: 0 1.25rem 0 2.85rem;
    font-size: 0.92rem;
    color: #0C2B52;
    font-weight: 600;
    outline: none;
    transition: all 0.2s ease;
    box-shadow: inset 0 2px 4px rgba(12, 43, 82, 0.02);
}

.modern-input-pill:focus,
.modern-select-pill:focus {
    background: #FFFFFF;
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15);
}

.modern-input-wrapper:focus-within .modern-input-icon {
    color: #176B5B;
}

.modern-field-error {
    color: #EF4444;
    font-size: 0.78rem;
    font-weight: 600;
    margin-top: 0.35rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Document Dropzones */
.document-dropzone {
    border: 2px dashed #B2D8CF;
    border-radius: 18px;
    background: #F8FAFD;
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

.document-dropzone:hover {
    border-color: #176B5B;
    background: #E8F4F0;
    box-shadow: 0 8px 24px rgba(23, 107, 91, 0.1);
    transform: translateY(-1px);
}

.document-dropzone-halo {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #E8F4F0;
    color: #176B5B;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0.85rem;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.12);
    transition: transform 0.25s ease;
}

.document-dropzone-prompt {
    font-size: 0.95rem;
    color: #0C2B52;
    margin-bottom: 0.35rem;
}

.document-dropzone-prompt strong {
    color: #176B5B;
    text-decoration: underline;
}

/* Review Dossier (Step 4) */
.review-dossier-wrapper {
    background: #F8FAFD;
    border: 1.5px solid #DCE6F2;
    border-radius: 20px;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 6px 20px rgba(12, 43, 82, 0.04);
}

.review-hero-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 1.5px solid #E2E8F0;
    flex-wrap: wrap;
}

.review-avatar-container {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid #176B5B;
    background: #FFFFFF;
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.2);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.review-avatar-monogram {
    font-size: 2.2rem;
    font-weight: 800;
    color: #FFFFFF;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.review-hero-meta {
    flex: 1;
    min-width: 260px;
}

.review-hero-title-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.review-hero-name {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    letter-spacing: -0.01em;
}

.review-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 50px;
    padding: 3px 12px;
    font-size: 0.82rem;
    font-weight: 700;
}

.review-hero-tags {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.review-tag-item {
    font-size: 0.82rem;
    color: #64748B;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    padding: 3px 10px;
    border-radius: 8px;
}

.review-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 50px;
}

.review-status-pill.is-active {
    background: #E8F8F0;
    color: #059669;
    border: 1px solid #A7F3D0;
}

.review-status-pill.is-inactive {
    background: #F1F5F9;
    color: #64748B;
    border: 1px solid #CBD5E1;
}

.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.review-section-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 2px 8px rgba(11, 25, 44, 0.02);
}

.review-card-header {
    font-size: 0.88rem;
    font-weight: 800;
    color: #0C2B52;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    margin-bottom: 0.85rem;
    border-bottom: 1.5px solid #F1F5F9;
}

.review-card-header i {
    color: #176B5B;
    font-size: 0.95rem;
}

.review-card-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.review-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    font-size: 0.86rem;
}

.review-label {
    color: #64748B;
    font-weight: 600;
    flex-shrink: 0;
    max-width: 45%;
}

.review-value {
    color: #0C2B52;
    text-align: right;
    word-break: break-word;
    font-weight: 500;
}

.review-value.font-bold {
    font-weight: 700;
}

.review-value.highlight-teal {
    color: #176B5B;
}

.review-value.font-mono {
    font-family: monospace;
    font-weight: 700;
}

.review-value.uppercase {
    text-transform: uppercase;
}

.review-value.capitalize {
    text-transform: capitalize;
}

.review-doc-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    padding: 2px 10px;
    border-radius: 6px;
}

.review-doc-badge.attached {
    background: #E8F8F0;
    color: #059669;
    border: 1px solid #A7F3D0;
    font-weight: 600;
}

.review-doc-badge.empty {
    background: #F1F5F9;
    color: #94A3B8;
    border: 1px solid #E2E8F0;
}

/* Bottom Wizard Actions (Exact Match to customers/create) */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1.5px solid #EDF3FA;
    gap: 1rem;
}

.modern-btn-cancel,
.modern-btn-prev {
    background: #FFFFFF;
    color: #475569;
    border: 1.5px solid #CBD5E1;
    border-radius: 50px;
    padding: 0.75rem 1.75rem;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.modern-btn-cancel:hover,
.modern-btn-prev:hover {
    background: #F1F5F9;
    color: #0F172A;
    border-color: #94A3B8;
    transform: translateY(-1px);
}

.modern-btn-next,
.modern-btn-submit {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 50px;
    padding: 0.75rem 2.25rem;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.3);
    transition: all 0.2s ease;
    margin-left: auto;
}

.modern-btn-next:hover,
.modern-btn-submit:hover {
    background: #125246;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.4);
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
    }
    .modern-col-span-2 {
        grid-column: span 1;
    }
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
    .modern-form-card {
        padding: 1.75rem 1.25rem;
        border-radius: 18px;
    }
}
</style>

<!-- Wizard Script & Real-Time Dossier Sync -->
<script>
let currentStep = 1;
const totalSteps = 4;

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
            window.vgNotify('Please fill in all required company details marked with *', 'error');
        } else {
            alert('Please fill in all required fields marked with *');
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

// Logo Upload Handler
function handleLogoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        alert('Logo exceeds maximum size of 5MB');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        const b64 = document.getElementById('logo_base64');
        if (b64) b64.value = dataUrl;

        const previewImg = document.getElementById('logoPreviewImg');
        const defaultIcon = document.getElementById('logoDefaultIcon');
        const btnRemove = document.getElementById('btnRemoveLogo');
        const btnUndo = document.getElementById('btnUndoLogo');
        const removedBadge = document.getElementById('logoRemovedBadge');
        const flag = document.getElementById('remove_logo');

        if (previewImg) {
            previewImg.src = dataUrl;
            previewImg.style.display = 'block';
            previewImg.style.opacity = '1';
        }
        if (defaultIcon) defaultIcon.style.display = 'none';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (removedBadge) removedBadge.style.display = 'none';
        if (flag) flag.value = '0';

        document.getElementById('logoUploadBtnText').textContent = 'Change Logo';
    };
    reader.readAsDataURL(file);
}

// Document Images Upload Handler (Paid Stamp, Signature, Signature + Stamp)
function handleDocImageChange(input, type) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        alert('Image exceeds maximum size of 5MB');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        const b64Input = document.getElementById(type + '_base64');
        if (b64Input) b64Input.value = dataUrl;

        let imgId = '', phId = '', btnTextId = '', removeBtnId = '', undoBtnId = '', badgeId = '';
        if (type === 'paid_stamp') {
            imgId = 'paidStampPreviewImg'; phId = 'paidStampPlaceholder'; btnTextId = 'paidStampBtnText';
            removeBtnId = 'removePaidStampBtn'; undoBtnId = 'undoPaidStampBtn'; badgeId = 'paidStampRemovedBadge';
        } else if (type === 'signature') {
            imgId = 'signaturePreviewImg'; phId = 'signaturePlaceholder'; btnTextId = 'signatureBtnText';
            removeBtnId = 'removeSignatureBtn'; undoBtnId = 'undoSignatureBtn'; badgeId = 'signatureRemovedBadge';
        } else if (type === 'signature_stamp') {
            imgId = 'sigStampPreviewImg'; phId = 'sigStampPlaceholder'; btnTextId = 'sigStampBtnText';
            removeBtnId = 'removeSigStampBtn'; undoBtnId = 'undoSigStampBtn'; badgeId = 'sigStampRemovedBadge';
        }

        const img = document.getElementById(imgId);
        const ph = document.getElementById(phId);
        const btnText = document.getElementById(btnTextId);
        const removeBtn = document.getElementById(removeBtnId);
        const undoBtn = document.getElementById(undoBtnId);
        const badge = document.getElementById(badgeId);
        const flag = document.getElementById('remove_' + type);

        if (img) {
            img.src = dataUrl;
            img.style.display = 'block';
            img.style.opacity = '1';
        }
        if (ph) ph.style.display = 'none';
        if (btnText) btnText.textContent = 'Replace';
        if (removeBtn) removeBtn.style.display = 'inline-flex';
        if (undoBtn) undoBtn.style.display = 'none';
        if (badge) badge.style.display = 'none';
        if (flag) flag.value = '0';
    };
    reader.readAsDataURL(file);
}

// Mark Image for Removal
function markImageRemoval(type) {
    const flag = document.getElementById('remove_' + type);
    if (flag) flag.value = '1';

    if (type === 'logo') {
        const img = document.getElementById('logoPreviewImg');
        const badge = document.getElementById('logoRemovedBadge');
        const btnRemove = document.getElementById('btnRemoveLogo');
        const btnUndo = document.getElementById('btnUndoLogo');
        if (img) img.style.opacity = '0.2';
        if (badge) badge.style.display = 'inline-block';
        if (btnRemove) btnRemove.style.display = 'none';
        if (btnUndo) btnUndo.style.display = 'inline-flex';
    } else {
        let imgId = '', badgeId = '', removeBtnId = '', undoBtnId = '';
        if (type === 'paid_stamp') {
            imgId = 'paidStampPreviewImg'; badgeId = 'paidStampRemovedBadge';
            removeBtnId = 'removePaidStampBtn'; undoBtnId = 'undoPaidStampBtn';
        } else if (type === 'signature') {
            imgId = 'signaturePreviewImg'; badgeId = 'signatureRemovedBadge';
            removeBtnId = 'removeSignatureBtn'; undoBtnId = 'undoSignatureBtn';
        } else if (type === 'signature_stamp') {
            imgId = 'sigStampPreviewImg'; badgeId = 'sigStampRemovedBadge';
            removeBtnId = 'removeSigStampBtn'; undoBtnId = 'undoSigStampBtn';
        }
        const img = document.getElementById(imgId);
        const badge = document.getElementById(badgeId);
        const removeBtn = document.getElementById(removeBtnId);
        const undoBtn = document.getElementById(undoBtnId);

        if (img) img.style.opacity = '0.2';
        if (badge) badge.style.display = 'inline-block';
        if (removeBtn) removeBtn.style.display = 'none';
        if (undoBtn) undoBtn.style.display = 'inline-flex';
    }
}

// Undo Image Removal
function undoImageRemoval(type, originalSrc) {
    const flag = document.getElementById('remove_' + type);
    if (flag) flag.value = '0';

    if (type === 'logo') {
        const img = document.getElementById('logoPreviewImg');
        const def = document.getElementById('logoDefaultIcon');
        const badge = document.getElementById('logoRemovedBadge');
        const btnRemove = document.getElementById('btnRemoveLogo');
        const btnUndo = document.getElementById('btnUndoLogo');

        if (badge) badge.style.display = 'none';
        if (originalSrc) {
            if (img) { img.src = originalSrc; img.style.opacity = '1'; img.style.display = 'block'; }
            if (def) def.style.display = 'none';
            if (btnRemove) btnRemove.style.display = 'inline-flex';
        } else {
            if (img) { img.src = ''; img.style.display = 'none'; }
            if (def) def.style.display = 'flex';
            if (btnRemove) btnRemove.style.display = 'none';
        }
        if (btnUndo) btnUndo.style.display = 'none';
    } else {
        let imgId = '', phId = '', badgeId = '', removeBtnId = '', undoBtnId = '';
        if (type === 'paid_stamp') {
            imgId = 'paidStampPreviewImg'; phId = 'paidStampPlaceholder'; badgeId = 'paidStampRemovedBadge';
            removeBtnId = 'removePaidStampBtn'; undoBtnId = 'undoPaidStampBtn';
        } else if (type === 'signature') {
            imgId = 'signaturePreviewImg'; phId = 'signaturePlaceholder'; badgeId = 'signatureRemovedBadge';
            removeBtnId = 'removeSignatureBtn'; undoBtnId = 'undoSignatureBtn';
        } else if (type === 'signature_stamp') {
            imgId = 'sigStampPreviewImg'; phId = 'sigStampPlaceholder'; badgeId = 'sigStampRemovedBadge';
            removeBtnId = 'removeSigStampBtn'; undoBtnId = 'undoSigStampBtn';
        }
        const img = document.getElementById(imgId);
        const ph = document.getElementById(phId);
        const badge = document.getElementById(badgeId);
        const removeBtn = document.getElementById(removeBtnId);
        const undoBtn = document.getElementById(undoBtnId);

        if (badge) badge.style.display = 'none';
        if (originalSrc) {
            if (img) { img.src = originalSrc; img.style.opacity = '1'; img.style.display = 'block'; }
            if (ph) ph.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'inline-flex';
        } else {
            if (img) { img.src = ''; img.style.display = 'none'; }
            if (ph) ph.style.display = 'block';
            if (removeBtn) removeBtn.style.display = 'none';
        }
        if (undoBtn) undoBtn.style.display = 'none';
    }
}

// Populate Live Summary Dossier (Step 4)
function populateSummaryDossier() {
    const compName = (document.getElementById('company_name').value || '').trim() || 'Company Name';
    const ownerName = (document.getElementById('owner_name').value || '').trim() || '—';
    const tagline = (document.getElementById('tagline').value || '').trim() || '—';
    const statusVal = document.getElementById('status').value || 'active';
    const phone = (document.getElementById('phone').value || '').trim() || '—';
    const altPhone = (document.getElementById('alternate_phone').value || '').trim() || '—';
    const email = (document.getElementById('email').value || '').trim() || '—';
    const website = (document.getElementById('website').value || '').trim() || '—';
    const address = (document.getElementById('address').value || '').trim() || '—';
    const city = (document.getElementById('city').value || '').trim() || '';
    const state = (document.getElementById('state').value || '').trim() || '';
    const pin = (document.getElementById('pin_code').value || '').trim() || '';
    const gst = (document.getElementById('gst_number').value || '').trim() || '—';
    const pan = (document.getElementById('pan_number').value || '').trim() || '—';

    // Hero updates
    const heroName = document.getElementById('sumHeroName');
    const heroOwner = document.getElementById('sumTagOwnerText');
    const heroTagline = document.getElementById('sumTagTaglineText');
    const initial = document.getElementById('sumAvatarInitial');
    const statusPill = document.getElementById('sumStatusPill');

    if (heroName) heroName.textContent = compName;
    if (heroOwner) heroOwner.textContent = 'Owner: ' + ownerName;
    if (heroTagline) heroTagline.textContent = tagline;
    if (initial) initial.textContent = compName.charAt(0).toUpperCase();

    if (statusPill) {
        if (statusVal === 'active') {
            statusPill.className = 'review-status-pill is-active';
            statusPill.innerHTML = '<i class="fa-solid fa-circle-check"></i> Active Profile';
        } else {
            statusPill.className = 'review-status-pill is-inactive';
            statusPill.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Inactive';
        }
    }

    // Logo in hero
    const logoImg = document.getElementById('logoPreviewImg');
    const sumAvatarImg = document.getElementById('sumAvatarImg');
    const sumAvatarMonogram = document.getElementById('sumAvatarMonogram');
    const removeLogoFlag = document.getElementById('remove_logo').value === '1';

    if (logoImg && logoImg.src && logoImg.src !== '' && logoImg.style.display !== 'none' && !removeLogoFlag) {
        if (sumAvatarImg) { sumAvatarImg.src = logoImg.src; sumAvatarImg.style.display = 'block'; }
        if (sumAvatarMonogram) sumAvatarMonogram.style.display = 'none';
    } else {
        if (sumAvatarImg) { sumAvatarImg.src = ''; sumAvatarImg.style.display = 'none'; }
        if (sumAvatarMonogram) sumAvatarMonogram.style.display = 'flex';
    }

    // Card 1
    const cardCompName = document.getElementById('sumCardCompanyName');
    const cardOwner = document.getElementById('sumCardOwnerName');
    const cardTag = document.getElementById('sumCardTagline');
    const cardStatus = document.getElementById('sumCardStatus');

    if (cardCompName) cardCompName.textContent = compName;
    if (cardOwner) cardOwner.textContent = ownerName;
    if (cardTag) cardTag.textContent = tagline;
    if (cardStatus) cardStatus.textContent = statusVal === 'active' ? 'Active Profile' : 'Inactive';

    // Card 2
    const cardPhone = document.getElementById('sumCardPhone');
    const cardAltPhone = document.getElementById('sumCardAltPhone');
    const cardEmail = document.getElementById('sumCardEmail');
    const cardWeb = document.getElementById('sumCardWebsite');
    const cardAddr = document.getElementById('sumCardAddress');
    const cardCityStatePin = document.getElementById('sumCardCityStatePin');

    if (cardPhone) cardPhone.textContent = phone;
    if (cardAltPhone) cardAltPhone.textContent = altPhone;
    if (cardEmail) cardEmail.textContent = email;
    if (cardWeb) cardWeb.textContent = website;
    if (cardAddr) cardAddr.textContent = address;

    let cityParts = [];
    if (city) cityParts.push(city);
    if (state && pin) cityParts.push(state + ' - ' + pin);
    else if (state) cityParts.push(state);
    else if (pin) cityParts.push(pin);
    if (cardCityStatePin) cardCityStatePin.textContent = cityParts.join(', ') || '—';

    // Card 3
    const cardGst = document.getElementById('sumCardGst');
    const cardPan = document.getElementById('sumCardPan');
    if (cardGst) cardGst.textContent = gst;
    if (cardPan) cardPan.textContent = pan;

    // Card 4: Document Image Badges
    const badgeMap = [
        { flagId: 'remove_logo', imgId: 'logoPreviewImg', badgeId: 'sumCardBadgeLogo', name: 'Brand Logo' },
        { flagId: 'remove_paid_stamp', imgId: 'paidStampPreviewImg', badgeId: 'sumCardBadgePaidStamp', name: 'Paid Stamp' },
        { flagId: 'remove_signature', imgId: 'signaturePreviewImg', badgeId: 'sumCardBadgeSignature', name: 'Authorized Sign' },
        { flagId: 'remove_signature_stamp', imgId: 'sigStampPreviewImg', badgeId: 'sumCardBadgeSigStamp', name: 'Sign + Stamp' },
    ];

    badgeMap.forEach(item => {
        const badgeEl = document.getElementById(item.badgeId);
        const imgEl = document.getElementById(item.imgId);
        const isRemoved = document.getElementById(item.flagId).value === '1';

        if (badgeEl) {
            if (imgEl && imgEl.src && imgEl.src !== '' && imgEl.style.display !== 'none' && !isRemoved) {
                badgeEl.className = 'review-doc-badge attached';
                badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Attached &amp; Active';
            } else {
                badgeEl.className = 'review-doc-badge empty';
                badgeEl.innerHTML = '<i class="fa-solid fa-circle-info"></i> Not Uploaded';
            }
        }
    });
}

// Double submit prevention
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('companyProfileForm');
    const btn = document.getElementById('btnSaveCompanyProfile');
    const btnText = document.getElementById('btnSaveCompanyProfileText');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            btn.disabled = true;
            if (btnText) btnText.textContent = 'Saving Profile...';
        });
    }
});
</script>
