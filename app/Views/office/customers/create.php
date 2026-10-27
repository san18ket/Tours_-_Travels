<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Modern Customer Registration Form
 * Pixel-perfect implementation matching reference UI for desktop (lg) and responsive mobile (sm)
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header: Back Button, Title, Subtitle, and 4-Step Stepper -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/customers') ?>" class="modern-back-btn" title="<?= e(__('customer_form.back_to_customers', [], 'Back to Customers')) ?>" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('customer_form.register_title', [], 'Register Customer Profile')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('customer_form.register_subtitle', [], 'Create a verified guest profile for reservations.')) ?></p>
            </div>
        </div>

        <!-- 4-Step Stepper Track -->
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label"><?= e(__('customer_form.step1_title', [], 'Personal Details')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label"><?= e(__('customer_form.step2_title', [], 'Contact Details')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label"><?= e(__('customer_form.documents', [], 'Documents')) ?></span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label"><?= e(__('customer_form.step4_title', [], 'Review & Save')) ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Duplicate Warning Banner (If Triggered by Backend) -->
    <?php if (!empty($duplicates)): ?>
    <div style="background: #FFFBEB; border: 1.5px solid #FCD34D; border-radius: 18px; padding: 1.25rem 1.5rem; margin-bottom: 1.75rem; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.08);">
        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="font-size: 1.5rem; color: #D97706; line-height: 1;">⚠️</div>
            <div style="flex: 1;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #92400E; margin: 0 0 0.4rem 0;"><?= __('customer_form.dup_detected', [], 'Potential Duplicate Customer Record Detected') ?></h3>
                <p style="font-size: 0.88rem; color: #78350F; margin: 0 0 0.85rem 0;">
                    <?= __('customer_form.dup_desc', [], 'A customer with a matching phone number or email is already registered:') ?>
                </p>
                <div style="background: #FFF; border: 1px solid #FDE68A; border-radius: 10px; padding: 0.75rem 1rem; margin-bottom: 0.85rem;">
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: #0C2B52;">
                        <?php foreach ($duplicates as $dup): ?>
                            <li style="margin-bottom: 0.35rem;">
                                <strong><?= e($dup['name']) ?></strong> (<code><?= e($dup['customer_code']) ?></code>) &bull; <?= e($dup['phone']) ?> <?= !empty($dup['email']) ? '&bull; ' . e($dup['email']) : '' ?>
                                <a href="<?= url('/office/customers/' . $dup['id']) ?>" target="_blank" style="color: #176B5B; font-weight: 600; margin-left: 0.5rem; text-decoration: underline;">View Profile &rarr;</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; font-weight: 600; color: #92400E; cursor: pointer;">
                    <input type="checkbox" name="confirm_duplicate" value="1" form="createCustomerForm" id="confirmDuplicateCheckbox" style="width: 17px; height: 17px; cursor: pointer;">
                    <span><?= __('customer_form.dup_confirm', [], 'I confirm this is a new customer and wish to continue registering.') ?></span>
                </label>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Master Form Container -->
    <form action="<?= url('/office/customers') ?>" method="POST" id="createCustomerForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="target_entity" value="customer">

        <!-- ========================================================================= -->
        <!-- STEP 1: Personal Details & Profile Photo                                 -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel1">
            <div class="modern-form-card">
                <!-- Section Header -->
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Personal Details</h2>
                        <p class="modern-section-sub">Basic customer identity, profile photo, and personal information.</p>
                    </div>
                </div>

                <!-- Professional Profile Photo Upload Component -->
                <div class="customer-avatar-upload-card">
                    <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('profile_photo').click()" title="Click to upload profile photo">
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleProfilePhotoChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">
                        <div class="avatar-default-icon" id="avatarDefaultIcon">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <img id="avatarPreviewImg" src="" alt="Profile Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <div class="avatar-camera-badge" title="Upload Photo">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="customer-avatar-meta">
                        <div class="customer-avatar-title-row">
                            <span class="customer-avatar-label">Customer Profile Photo</span>
                            <span class="modern-badge-subtle">Optional</span>
                        </div>
                        <p class="customer-avatar-help">Upload a clear front-facing headshot or passport photo. Supported: JPG, PNG, WEBP (Max 3MB).</p>
                        <div class="customer-avatar-actions">
                            <button type="button" class="customer-avatar-btn-upload" onclick="document.getElementById('profile_photo').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Photo
                            </button>
                            <button type="button" class="customer-avatar-btn-remove" id="btnRemoveAvatar" style="display: none;" onclick="clearProfilePhoto()">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Salutation, Full Name, Email Address, Mobile Number (Responsive Layout) -->
                <div class="customer-name-row">
                    <!-- Honorific Salutation (Positioned before Full Name) -->
                    <div class="modern-form-group salutation-group">
                        <label class="modern-form-label" for="title">
                            <?= e(__('customer_form.salutation', [], 'Honorific Salutation')) ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-signature"></i></span>
                            <select id="title" name="title" class="modern-select-pill">
                                <option value="" <?= empty($old['title']) ? 'selected' : '' ?>><?= e(__('customer_form.salutation_none', [], 'Select')) ?></option>
                                <option value="Mr" <?= ($old['title'] ?? '') === 'Mr' ? 'selected' : '' ?>><?= e(__('customer_form.title_mr', [], 'Mr.')) ?></option>
                                <option value="Mrs" <?= ($old['title'] ?? '') === 'Mrs' ? 'selected' : '' ?>><?= e(__('customer_form.title_mrs', [], 'Mrs.')) ?></option>
                                <option value="Ms" <?= ($old['title'] ?? '') === 'Ms' ? 'selected' : '' ?>><?= e(__('customer_form.title_ms', [], 'Ms.')) ?></option>
                                <option value="Dr" <?= ($old['title'] ?? '') === 'Dr' ? 'selected' : '' ?>><?= e(__('customer_form.title_dr', [], 'Dr.')) ?></option>
                                <option value="Prof" <?= ($old['title'] ?? '') === 'Prof' ? 'selected' : '' ?>><?= e(__('customer_form.title_prof', [], 'Prof.')) ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="modern-form-group name-group">
                        <label class="modern-form-label" for="name">
                            <?= e(__('customer_form.full_name', [], 'Full Name')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="name" name="name" class="modern-input-pill" required placeholder="<?= e(__('customer_form.full_name_placeholder', [], 'e.g. John Doe')) ?>" value="<?= e($old['name'] ?? '') ?>" autocomplete="name">
                        </div>
                        <?php if (!empty($errors['name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email Address -->
                    <div class="modern-form-group email-group">
                        <label class="modern-form-label" for="email">
                            <?= e(__('customer_form.email', [], 'Email Address')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="modern-input-pill" required placeholder="<?= e(__('customer_form.email_placeholder', [], 'e.g. john@example.com')) ?>" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Number -->
                    <div class="modern-form-group phone-group">
                        <label class="modern-form-label" for="phone">
                            <?= e(__('customer_form.primary_phone', [], 'Mobile Number')) ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="phone" name="phone" class="modern-input-pill" required placeholder="<?= e(__('customer_form.primary_phone_placeholder', [], 'e.g. +91 9876543210')) ?>" value="<?= e($old['phone'] ?? $old['primary_mobile'] ?? '') ?>" autocomplete="tel">
                        </div>
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 2: Date of Birth, Gender, Nationality (3 Columns) -->
                <div class="modern-form-grid-3">
                    <!-- Date of Birth -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="date_of_birth">
                            Date of Birth <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="modern-input-pill" required value="<?= e($old['date_of_birth'] ?? '') ?>">
                        </div>
                        <?php if (!empty($errors['date_of_birth'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['date_of_birth'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Gender -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="gender">
                            Gender <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-venus-mars"></i></span>
                            <select id="gender" name="gender" class="modern-select-pill" required>
                                <option value="" <?= empty($old['gender']) ? 'selected' : '' ?>>Select Gender</option>
                                <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                <option value="non_binary" <?= ($old['gender'] ?? '') === 'non_binary' ? 'selected' : '' ?>>Non-Binary</option>
                                <option value="prefer_not_to_say" <?= ($old['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nationality -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="nationality">
                            Nationality <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                            <input type="text" id="nationality" name="nationality" class="modern-input-pill" required placeholder="e.g. Indian" value="<?= e($old['nationality'] ?? 'Indian') ?>">
                        </div>
                    </div>
                </div>

                <!-- Row 3: Address (2 Columns), City (1 Column) -->
                <div class="modern-form-grid-3">
                    <!-- Address -->
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="address_line">
                            Address <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="address_line" name="address_line" class="modern-input-pill" required placeholder="Street address, apartment, suite..." value="<?= e($old['address_line'] ?? $old['address'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- City -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="city">
                            City <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                            <input type="text" id="city" name="city" class="modern-input-pill" required placeholder="e.g. Mumbai" value="<?= e($old['city'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Row 4: State, Country, Postal Code (3 Columns) -->
                <div class="modern-form-grid-3">
                    <!-- State -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="state">
                            State <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                            <input type="text" id="state" name="state" class="modern-input-pill" required placeholder="e.g. Maharashtra" value="<?= e($old['state'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="country">
                            Country <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-earth-americas"></i></span>
                            <input type="text" id="country" name="country" class="modern-input-pill" required placeholder="e.g. India" value="<?= e($old['country'] ?? 'India') ?>">
                        </div>
                    </div>

                    <!-- Postal Code -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="postal_code">
                            Postal Code <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="postal_code" name="postal_code" class="modern-input-pill" required placeholder="e.g. 400001" value="<?= e($old['postal_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/customers') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: Contact Details                                                   -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel2" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Contact Details</h2>
                        <p class="modern-section-sub">Alternate phone and emergency contact details for tour safety.</p>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="alternate_phone">Alternate Phone</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                            <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" placeholder="e.g. +91 9123456789" value="<?= e($old['alternate_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="emergency_contact_name">Emergency Contact Name</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="modern-input-pill" placeholder="e.g. Jane Doe" value="<?= e($old['emergency_contact_name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="emergency_contact_phone">Emergency Phone</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone-flip"></i></span>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="modern-input-pill" placeholder="e.g. +91 9988776655" value="<?= e($old['emergency_contact_phone'] ?? '') ?>">
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
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 3: Identification & Documents                                        -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel3" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Identification & Documents</h2>
                        <p class="modern-section-sub">Government-issued ID details, document scans, and record verification notes.</p>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_type">ID Document Type</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <select id="id_type" name="id_type" class="modern-select-pill" onchange="onDocumentTypeChange(this.value)">
                                <option value="aadhaar" selected>Aadhaar Card</option>
                                <option value="passport">Passport</option>
                                <option value="driving_license">Driving License</option>
                                <option value="voter_id">Voter ID</option>
                                <option value="other">Other Official ID</option>
                            </select>
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_number" id="lblDocNumber">Aadhaar Card Number</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="id_number" name="id_number" class="modern-input-pill" placeholder="e.g. 5432 1234 5678" value="<?= e($old['id_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_expiry">Document Expiry Date</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <input type="date" id="id_expiry" name="id_expiry" class="modern-input-pill" value="<?= e($old['id_expiry'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- DYNAMIC PROFESSIONAL DOCUMENT UPLOAD BOX -->
                <div class="document-upload-container" style="margin-bottom: 1.5rem;">
                    <div class="document-upload-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                        <label class="modern-form-label" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-cloud-arrow-up" style="color: #176B5B;"></i>
                            <span id="docUploadBoxTitle">Upload Aadhaar Card Scan / Image</span>
                        </label>
                        <span id="docTypeBadge" class="modern-badge" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                            UIDAI 12-Digit Verification
                        </span>
                    </div>

                    <!-- Modern Interactive Dropzone -->
                    <div class="document-dropzone" id="documentDropzone" onclick="document.getElementById('id_document_file').click()">
                        <input type="file" id="id_document_file" name="id_document_file" accept="image/png,image/jpeg,image/jpg,image/webp,application/pdf" style="display: none;" onchange="handleDocumentFileChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="id_document_file_base64" id="id_document_file_base64" value="">
                        <input type="hidden" name="id_document_file_name" id="id_document_file_name" value="">

                        <!-- Empty State Inside Dropzone -->
                        <div id="dropzoneEmptyState">
                            <div class="document-dropzone-halo">
                                <i class="fa-solid fa-id-card-clip" id="docDropzoneIcon"></i>
                            </div>
                            <div class="document-dropzone-prompt">
                                <strong>Click to upload</strong> or drag and drop your document scan
                            </div>
                            <div class="document-dropzone-sub" id="docDropzoneHelp">
                                Supported formats: PNG, JPG, JPEG, WEBP or PDF (Max 5MB)
                            </div>
                        </div>

                        <!-- Populated / Preview State Inside Dropzone -->
                        <div id="dropzonePreviewState" style="display: none;">
                            <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                                <div class="doc-preview-thumb-box">
                                    <img id="docPreviewImage" src="" alt="Document preview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    <div id="docPreviewPdfIcon" style="display: none; font-size: 2.2rem; color: #EF4444;">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                </div>
                                <div class="doc-preview-meta">
                                    <div class="doc-preview-name" id="docPreviewName">document_scan.jpg</div>
                                    <div class="doc-preview-size" id="docPreviewSize">0 KB</div>
                                    <div class="doc-preview-status">
                                        <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Document Attached &amp; Ready
                                    </div>
                                </div>
                                <div class="doc-preview-actions">
                                    <button type="button" class="btn btn-sm" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-weight: 700; border-radius: 8px; padding: 0.35rem 0.85rem;" onclick="document.getElementById('id_document_file').click()">
                                        <i class="fa-solid fa-arrows-rotate"></i> Change
                                    </button>
                                    <button type="button" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; font-weight: 700; border-radius: 8px; padding: 0.35rem 0.85rem;" onclick="clearDocumentUpload()">
                                        <i class="fa-solid fa-trash-can"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-form-group" style="margin-bottom: 0;">
                    <label class="modern-form-label" for="notes">Special Operational / Internal Notes</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                        <input type="text" id="notes" name="notes" class="modern-input-pill" placeholder="e.g. VIP client, referred by travel partner, prefers morning departures" value="<?= e($old['notes'] ?? '') ?>">
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(3)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 4: Review & Save                                                     -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardPanel4" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge" style="background: #E8F8F0; color: #10B981;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Review &amp; Save</h2>
                        <p class="modern-section-sub">Comprehensive profile verification prior to saving customer record.</p>
                    </div>
                </div>

                <!-- Live Executive Dossier Container -->
                <div class="review-dossier-wrapper">

                    <!-- Profile Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container">
                            <div class="review-avatar-monogram" id="sumAvatarMonogram">
                                <span id="sumAvatarInitial">C</span>
                            </div>
                            <img id="sumAvatarImg" src="" alt="Customer Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-id-badge"></i>
                                    <code><?= e($customerCode ?? 'VG-CUST-AUTO') ?></code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item" id="sumTagGenderNationality">
                                    <i class="fa-solid fa-user-tag"></i> <span id="sumTagGenderText">—</span>
                                </span>
                                <span class="review-tag-item" id="sumTagDobAge">
                                    <i class="fa-solid fa-cake-candles"></i> <span id="sumTagDobText">—</span>
                                </span>
                                <span class="review-status-pill is-active" id="sumStatusPill">
                                    <i class="fa-solid fa-circle-check"></i> Active Profile
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Comprehensive Information Section Cards -->
                    <div class="review-sections-grid">

                        <!-- Card 1: Personal Identity -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-id-card-clip"></i> Personal Identity
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Full Legal Name</span>
                                    <span class="review-value font-bold" id="sumCardName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Salutation</span>
                                    <span class="review-value" id="sumCardSalutation">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Date of Birth &amp; Age</span>
                                    <span class="review-value" id="sumCardDob">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Gender</span>
                                    <span class="review-value capitalize" id="sumCardGender">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Nationality</span>
                                    <span class="review-value" id="sumCardNationality">Indian</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Contact & Residential Address -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-map-location-dot"></i> Contact &amp; Residential Address
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Primary Mobile</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Email Address</span>
                                    <span class="review-value" id="sumCardEmail">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Alternate Phone</span>
                                    <span class="review-value" id="sumCardAltPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Street Address</span>
                                    <span class="review-value" id="sumCardAddress">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">City, State &amp; PIN</span>
                                    <span class="review-value" id="sumCardCityStatePin">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Emergency Contact Details -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-shield-halved"></i> Emergency Contact Details
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Emergency Contact</span>
                                    <span class="review-value font-semibold" id="sumCardEmergName">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Emergency Phone</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardEmergPhone">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Emergency Protocol</span>
                                    <span class="review-value" style="color: #059669; font-weight: 600;">
                                        <i class="fa-solid fa-circle-check"></i> On-file for Passenger Safety
                                    </span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Alternate Reachability</span>
                                    <span class="review-value" id="sumCardAltReachability">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Verification Documents & Notes -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-file-shield"></i> Verification Documents &amp; Notes
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">ID Document Type</span>
                                    <span class="review-value font-semibold" id="sumCardDocType">Aadhaar Card</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Document Number</span>
                                    <span class="review-value font-mono" id="sumCardDocNumber">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Document Expiry</span>
                                    <span class="review-value" id="sumCardDocExpiry">Lifetime / No Expiry</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Attached Scan</span>
                                    <span class="review-value" id="sumCardDocFile">
                                        <span class="review-doc-badge empty"><i class="fa-solid fa-circle-xmark"></i> Not attached</span>
                                    </span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Internal Notes</span>
                                    <span class="review-value" id="sumCardNotes">—</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Hidden Defaults: Active Profile and Updates Enabled -->
                <input type="hidden" name="status" value="active">
                <input type="hidden" name="marketing_consent" value="1">

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(3)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="modern-btn-submit" id="btnSubmitCustomer">
                        <i class="fa-solid fa-check"></i> Create Customer Profile
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
// Wizard State & Navigation (4-Step Streamlined Process)
let currentStep = 1;
const totalSteps = 4;
let avatarDataUrl = null;
let uploadedDocumentMeta = null;

function updateStepperUI(activeStep) {
    currentStep = activeStep;

    // Update Step items
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

    // Update connector lines
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

    // When on step 4, update the comprehensive live summary
    if (activeStep === 4) {
        populateSummaryDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target panel
    const targetPanel = document.getElementById('wizardPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateStepperUI(targetStep);

    // Scroll to top smoothly
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
            window.vgNotify('Please fill in all required fields marked with *', 'error');
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

// Profile Photo Handlers (Step 1)
function handleProfilePhotoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please select a valid image file (PNG, JPG, WEBP)', 'error');
        }
        input.value = '';
        return;
    }

    if (file.size > 3 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Profile photo size exceeds 3MB limit', 'error');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        avatarDataUrl = e.target.result;
        const b64Input = document.getElementById('profile_photo_base64');
        if (b64Input) b64Input.value = avatarDataUrl;

        const previewImg = document.getElementById('avatarPreviewImg');
        const defaultIcon = document.getElementById('avatarDefaultIcon');
        const btnRemove = document.getElementById('btnRemoveAvatar');

        if (previewImg) {
            previewImg.src = avatarDataUrl;
            previewImg.style.display = 'block';
        }
        if (defaultIcon) defaultIcon.style.display = 'none';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
    };
    reader.readAsDataURL(file);
}

function clearProfilePhoto() {
    const input = document.getElementById('profile_photo');
    if (input) input.value = '';
    const b64Input = document.getElementById('profile_photo_base64');
    if (b64Input) b64Input.value = '';
    avatarDataUrl = null;

    const previewImg = document.getElementById('avatarPreviewImg');
    const defaultIcon = document.getElementById('avatarDefaultIcon');
    const btnRemove = document.getElementById('btnRemoveAvatar');

    if (previewImg) {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
    if (defaultIcon) defaultIcon.style.display = 'flex';
    if (btnRemove) btnRemove.style.display = 'none';
}

// Calculate age from date of birth string
function calculateAge(dobString) {
    if (!dobString) return null;
    const birthDate = new Date(dobString);
    if (isNaN(birthDate.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age >= 0 ? age : null;
}

// Format date into human-readable representation
function formatDobString(dobString) {
    if (!dobString) return '—';
    const parts = dobString.split('-');
    if (parts.length === 3) {
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const y = parts[0];
        const m = parseInt(parts[1], 10) - 1;
        const d = parseInt(parts[2], 10);
        return d + ' ' + (months[m] || parts[1]) + ' ' + y;
    }
    return dobString;
}

// Comprehensive Review & Save Populator (Step 4)
function populateSummaryDossier() {
    // 1. Gather all inputs
    const title = document.getElementById('title')?.value || '';
    const rawName = document.getElementById('name')?.value || '';
    const fullName = title ? (title + '. ' + rawName) : (rawName || '—');
    const email = document.getElementById('email')?.value || '—';
    const phone = document.getElementById('phone')?.value || '—';
    const altPhone = document.getElementById('alternate_phone')?.value || '—';
    const dob = document.getElementById('date_of_birth')?.value || '';
    const gender = document.getElementById('gender')?.value || '—';
    const nationality = document.getElementById('nationality')?.value || 'Indian';

    const address = document.getElementById('address_line')?.value || '';
    const city = document.getElementById('city')?.value || '';
    const state = document.getElementById('state')?.value || '';
    const postal = document.getElementById('postal_code')?.value || '';
    const country = document.getElementById('country')?.value || 'India';

    const emergName = document.getElementById('emergency_contact_name')?.value || '—';
    const emergPhone = document.getElementById('emergency_contact_phone')?.value || '—';

    const idTypeSelect = document.getElementById('id_type');
    const idTypeLabel = idTypeSelect?.options[idTypeSelect.selectedIndex]?.text || 'Aadhaar Card';
    const idNumber = document.getElementById('id_number')?.value || '—';
    const idExpiry = document.getElementById('id_expiry')?.value || '';
    const notes = document.getElementById('notes')?.value || '—';

    // 2. Format derived values
    const age = calculateAge(dob);
    const dobFormatted = formatDobString(dob);
    const dobWithAge = dob ? (dobFormatted + (age !== null ? ' (' + age + ' yrs)' : '')) : '—';
    const cityStatePin = [city, state, postal, country].filter(Boolean).join(', ') || '—';
    const expiryFormatted = idExpiry ? formatDobString(idExpiry) : 'Lifetime / No Expiry';

    // 3. Hero Avatar & Monogram
    const heroNameEl = document.getElementById('sumHeroName');
    const avatarImg = document.getElementById('sumAvatarImg');
    const avatarMono = document.getElementById('sumAvatarMonogram');
    const avatarInitial = document.getElementById('sumAvatarInitial');

    if (heroNameEl) heroNameEl.textContent = fullName;
    const initialChar = (rawName.trim().charAt(0) || 'C').toUpperCase();
    if (avatarInitial) avatarInitial.textContent = initialChar;

    if (avatarDataUrl) {
        if (avatarImg) {
            avatarImg.src = avatarDataUrl;
            avatarImg.style.display = 'block';
        }
        if (avatarMono) avatarMono.style.display = 'none';
    } else {
        if (avatarImg) {
            avatarImg.src = '';
            avatarImg.style.display = 'none';
        }
        if (avatarMono) avatarMono.style.display = 'flex';
    }

    // Hero Meta Tags
    const tagGender = document.getElementById('sumTagGenderText');
    if (tagGender) tagGender.textContent = (gender !== '—' ? gender.toUpperCase() : 'NOT SPECIFIED') + ' • ' + nationality;

    const tagDob = document.getElementById('sumTagDobText');
    if (tagDob) tagDob.textContent = dobWithAge;

    // Card 1: Personal Identity
    const sumCardName = document.getElementById('sumCardName');
    const sumCardSalutation = document.getElementById('sumCardSalutation');
    const sumCardDob = document.getElementById('sumCardDob');
    const sumCardGender = document.getElementById('sumCardGender');
    const sumCardNationality = document.getElementById('sumCardNationality');

    if (sumCardName) sumCardName.textContent = fullName;
    if (sumCardSalutation) sumCardSalutation.textContent = title ? (title + '.') : 'None';
    if (sumCardDob) sumCardDob.textContent = dobWithAge;
    if (sumCardGender) sumCardGender.textContent = gender;
    if (sumCardNationality) sumCardNationality.textContent = nationality;

    // Card 2: Contact & Address
    const sumCardPhone = document.getElementById('sumCardPhone');
    const sumCardEmail = document.getElementById('sumCardEmail');
    const sumCardAltPhone = document.getElementById('sumCardAltPhone');
    const sumCardAddress = document.getElementById('sumCardAddress');
    const sumCardCityStatePin = document.getElementById('sumCardCityStatePin');

    if (sumCardPhone) sumCardPhone.textContent = phone;
    if (sumCardEmail) sumCardEmail.textContent = email;
    if (sumCardAltPhone) sumCardAltPhone.textContent = altPhone;
    if (sumCardAddress) sumCardAddress.textContent = address || '—';
    if (sumCardCityStatePin) sumCardCityStatePin.textContent = cityStatePin;

    // Card 3: Emergency Contact Details
    const sumCardEmergName = document.getElementById('sumCardEmergName');
    const sumCardEmergPhone = document.getElementById('sumCardEmergPhone');
    const sumCardAltReach = document.getElementById('sumCardAltReachability');

    if (sumCardEmergName) sumCardEmergName.textContent = emergName;
    if (sumCardEmergPhone) sumCardEmergPhone.textContent = emergPhone;
    if (sumCardAltReach) sumCardAltReach.textContent = (altPhone && altPhone !== '—') ? altPhone : 'Primary phone only';

    // Card 4: Verification Documents & Notes
    const sumCardDocType = document.getElementById('sumCardDocType');
    const sumCardDocNumber = document.getElementById('sumCardDocNumber');
    const sumCardDocExpiry = document.getElementById('sumCardDocExpiry');
    const sumCardDocFile = document.getElementById('sumCardDocFile');
    const sumCardNotes = document.getElementById('sumCardNotes');

    if (sumCardDocType) sumCardDocType.textContent = idTypeLabel;
    if (sumCardDocNumber) sumCardDocNumber.textContent = idNumber;
    if (sumCardDocExpiry) sumCardDocExpiry.textContent = expiryFormatted;
    if (sumCardNotes) sumCardNotes.textContent = notes;

    if (sumCardDocFile) {
        if (uploadedDocumentMeta) {
            sumCardDocFile.innerHTML = `
                <span class="review-doc-badge attached">
                    <i class="fa-solid fa-file-circle-check"></i>
                    <strong>${uploadedDocumentMeta.name}</strong> (${uploadedDocumentMeta.size})
                </span>
            `;
        } else {
            sumCardDocFile.innerHTML = `
                <span class="review-doc-badge empty">
                    <i class="fa-solid fa-circle-xmark"></i> Not attached (optional)
                </span>
            `;
        }
    }
}

// Dynamic Document Metadata and Custom Upload Box Controller (Step 3)
const docTypeMetadata = {
    aadhaar: {
        title: 'Upload Aadhaar Card Scan / Image',
        badge: 'UIDAI 12-Digit Verification',
        icon: 'fa-id-card-clip',
        numberLabel: 'Aadhaar Card Number',
        placeholder: 'e.g. 5432 1234 5678',
        help: 'Upload front & back view of your official Aadhaar Card or e-Aadhaar PDF'
    },
    passport: {
        title: 'Upload Passport Front Bio-Data Page',
        badge: 'International Travel Document',
        icon: 'fa-passport',
        numberLabel: 'Passport Document Number',
        placeholder: 'e.g. A1234567',
        help: 'Upload clear photo of Passport front bio page with photo and signature'
    },
    driving_license: {
        title: 'Upload Driving License Card Scan',
        badge: 'RTO Official Transport License',
        icon: 'fa-id-badge',
        numberLabel: 'Driving License Number',
        placeholder: 'e.g. MH-01-2023-1234567',
        help: 'Upload front side of valid Driving License showing category and expiry'
    },
    voter_id: {
        title: 'Upload Election Voter ID (EPIC) Scan',
        badge: 'Election Commission of India',
        icon: 'fa-check-to-slot',
        numberLabel: 'Voter ID (EPIC) Number',
        placeholder: 'e.g. ABC1234567',
        help: 'Upload front side of official Voter Identity Card'
    },
    other: {
        title: 'Upload Official Identity Verification Document',
        badge: 'Government / Corporate ID',
        icon: 'fa-file-shield',
        numberLabel: 'ID Document Number',
        placeholder: 'e.g. ID-REG-87421',
        help: 'Upload clear photo or PDF of government-issued identity proof'
    }
};

function onDocumentTypeChange(docType) {
    const meta = docTypeMetadata[docType] || docTypeMetadata.other;
    const titleEl = document.getElementById('docUploadBoxTitle');
    const badgeEl = document.getElementById('docTypeBadge');
    const iconEl = document.getElementById('docDropzoneIcon');
    const lblNumber = document.getElementById('lblDocNumber');
    const inputNumber = document.getElementById('id_number');
    const helpEl = document.getElementById('docDropzoneHelp');

    if (titleEl) titleEl.textContent = meta.title;
    if (badgeEl) badgeEl.textContent = meta.badge;
    if (iconEl) iconEl.className = 'fa-solid ' + meta.icon;
    if (lblNumber) lblNumber.textContent = meta.numberLabel;
    if (inputNumber) inputNumber.placeholder = meta.placeholder;
    if (helpEl && document.getElementById('dropzoneEmptyState').style.display !== 'none') {
        helpEl.textContent = meta.help;
    }
}

function handleDocumentFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Document file size exceeds 5MB limit', 'error');
        }
        input.value = '';
        return;
    }

    const sizeKb = (file.size / 1024).toFixed(1);
    const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

    uploadedDocumentMeta = {
        name: file.name,
        size: sizeStr,
        type: file.type
    };

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const nameEl = document.getElementById('docPreviewName');
    const sizeEl = document.getElementById('docPreviewSize');
    const imgEl = document.getElementById('docPreviewImage');
    const pdfIconEl = document.getElementById('docPreviewPdfIcon');

    if (nameEl) nameEl.textContent = file.name;
    if (sizeEl) sizeEl.textContent = sizeStr;

    // Read file as Base64 for guaranteed submission
    const reader = new FileReader();
    reader.onload = function(e) {
        const b64Input = document.getElementById('id_document_file_base64');
        if (b64Input) b64Input.value = e.target.result;
        const nameInput = document.getElementById('id_document_file_name');
        if (nameInput) nameInput.value = file.name;

        if (file.type.startsWith('image/')) {
            if (imgEl) {
                imgEl.src = e.target.result;
                imgEl.style.display = 'block';
            }
            if (pdfIconEl) pdfIconEl.style.display = 'none';
        } else {
            if (imgEl) imgEl.style.display = 'none';
            if (pdfIconEl) pdfIconEl.style.display = 'block';
        }
    };
    reader.readAsDataURL(file);

    if (emptyState) emptyState.style.display = 'none';
    if (previewState) previewState.style.display = 'block';
}

function clearDocumentUpload() {
    const input = document.getElementById('id_document_file');
    if (input) input.value = '';
    const b64Input = document.getElementById('id_document_file_base64');
    if (b64Input) b64Input.value = '';
    const nameInput = document.getElementById('id_document_file_name');
    if (nameInput) nameInput.value = '';
    uploadedDocumentMeta = null;

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const imgEl = document.getElementById('docPreviewImage');

    if (imgEl) imgEl.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// Drag & drop event registration and initializers
document.addEventListener('DOMContentLoaded', function() {
    wizardGoToStep(1);

    // Profile Photo drag & drop
    const avatarDrop = document.getElementById('avatarDropZone');
    if (avatarDrop) {
        ['dragenter', 'dragover'].forEach(eventName => {
            avatarDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                avatarDrop.style.borderColor = '#10B981';
                avatarDrop.style.transform = 'scale(1.05)';
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            avatarDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                avatarDrop.style.borderColor = '#176B5B';
                avatarDrop.style.transform = '';
            });
        });
        avatarDrop.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                const input = document.getElementById('profile_photo');
                if (input) {
                    input.files = files;
                    handleProfilePhotoChange(input);
                }
            }
        });
    }

    // Document Dropzone drag & drop
    const dropzone = document.getElementById('documentDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                const input = document.getElementById('id_document_file');
                if (input) {
                    input.files = files;
                    handleDocumentFileChange(input);
                }
            }
        });
    }

    const initialDocType = document.getElementById('id_type');
    if (initialDocType) {
        onDocumentTypeChange(initialDocType.value);
    }
});
</script>

<style>
/* ========================================================================= */
/* 1. Profile Photo Upload Component (Step 1)                                */
/* ========================================================================= */
.customer-avatar-upload-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #F8FAFD 0%, #F1F6FA 100%);
    border: 1.5px solid #DCE6F2;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 14px rgba(11, 25, 44, 0.03);
}

.customer-avatar-preview-wrap {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 3px solid #176B5B;
    background: #E8F4F0;
    position: relative;
    cursor: pointer;
    overflow: visible;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.18);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.customer-avatar-preview-wrap:hover {
    transform: scale(1.04);
    box-shadow: 0 8px 22px rgba(23, 107, 91, 0.25);
    border-color: #10B981;
}

.avatar-default-icon {
    font-size: 2.5rem;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.avatar-camera-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    border: 2.5px solid #FFFFFF;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    transition: background 0.2s ease, transform 0.2s ease;
}
.customer-avatar-preview-wrap:hover .avatar-camera-badge {
    background: #10B981;
    transform: scale(1.1);
}

.customer-avatar-meta {
    flex: 1;
    min-width: 0;
}
.customer-avatar-title-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.25rem;
}
.customer-avatar-label {
    font-size: 1rem;
    font-weight: 700;
    color: #0C2B52;
}
.modern-badge-subtle {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: #E2E8F0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 20px;
}
.customer-avatar-help {
    font-size: 0.84rem;
    color: #64748B;
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
}
.customer-avatar-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.customer-avatar-btn-upload {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 9px;
    padding: 0.45rem 1rem;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.2);
}
.customer-avatar-btn-upload:hover {
    background: #13584A;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.3);
}
.customer-avatar-btn-remove {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FECACA;
    border-radius: 9px;
    padding: 0.45rem 0.9rem;
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

@media (max-width: 576px) {
    .customer-avatar-upload-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
        padding: 1.25rem 1rem;
    }
    .customer-avatar-title-row {
        justify-content: center;
    }
    .customer-avatar-actions {
        justify-content: center;
    }
}

/* ========================================================================= */
/* 2. Customer Create Name & Identity Row (Step 1)                           */
/* ========================================================================= */
.customer-name-row {
    display: grid;
    grid-template-columns: 145px 1.4fr 1.3fr 1.3fr;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1100px) {
    .customer-name-row {
        grid-template-columns: 140px 1fr;
    }
    .customer-name-row .email-group,
    .customer-name-row .phone-group {
        grid-column: span 1;
    }
}
@media (max-width: 640px) {
    .customer-name-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

/* ========================================================================= */
/* 3. Document Upload Dropzone (Step 3)                                      */
/* ========================================================================= */
.document-dropzone {
    border: 2px dashed #B2D8CF;
    border-radius: 18px;
    background: #F8FAFD;
    padding: 2rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}
.document-dropzone:hover,
.document-dropzone.is-dragover {
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
.document-dropzone:hover .document-dropzone-halo {
    transform: scale(1.08);
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
.document-dropzone-sub {
    font-size: 0.8rem;
    color: #64748B;
}

.doc-preview-wrapper {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #FFFFFF;
    border: 1.5px solid #B2D8CF;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    text-align: left;
    box-shadow: 0 4px 16px rgba(23, 107, 91, 0.08);
}
.doc-preview-thumb-box {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.doc-preview-meta {
    flex: 1;
    min-width: 0;
}
.doc-preview-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0C2B52;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.doc-preview-size {
    font-size: 0.78rem;
    color: #64748B;
    margin-bottom: 4px;
}
.doc-preview-status {
    font-size: 0.78rem;
    font-weight: 600;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 5px;
}
.doc-preview-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
@media (max-width: 576px) {
    .doc-preview-wrapper {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }
    .doc-preview-actions {
        justify-content: stretch;
    }
    .doc-preview-actions .btn {
        flex: 1;
    }
}

/* ========================================================================= */
/* 4. Executive Live Dossier & Review Cards (Step 4)                          */
/* ========================================================================= */
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
    background: linear-gradient(135deg, #176B5B 0%, #0C2B52 100%);
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
.review-code-badge code {
    font-family: inherit;
    font-weight: 800;
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

/* 4-Section Grid */
.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}
@media (max-width: 900px) {
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
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
.review-value.font-semibold {
    font-weight: 600;
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
</style>
