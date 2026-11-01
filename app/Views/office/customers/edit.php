<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit Customer Profile
 * Complete Modern Form UI Matching office/customers/create with Full Profile Photo & Document Upload
 */

$hasExistingAvatar = !empty($customer['profile_photo']);
$existingAvatarUrl = $hasExistingAvatar ? customer_avatar_url($customer['profile_photo']) : '';

$hasExistingDoc = !empty($customer['id_document_file']);
$existingDocUrl = $hasExistingDoc ? customer_document_url($customer['id_document_file']) : '';
$existingDocName = $hasExistingDoc ? basename($customer['id_document_file']) : '';
$isExistingDocPdf = $hasExistingDoc && str_ends_with(strtolower($customer['id_document_file']), '.pdf');

$currentDocType = $old['id_type'] ?? $customer['id_type'] ?? 'aadhaar';
if (empty($currentDocType)) {
    $currentDocType = 'aadhaar';
}
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="modern-back-btn" title="Back to Customer Profile" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('customer.edit_title', ['name' => $customer['name']], 'Edit Customer Profile')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('customer.edit_subtitle', [], 'Update verified client details, identity documents, contact, and address.')) ?></p>
            </div>
        </div>

        <div class="modern-header-right" style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="customer-code-badge-header">
                <i class="fa-solid fa-id-badge"></i> <?= e($customer['customer_code']) ?>
            </span>
            <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="btn-view-profile-header">
                <i class="fa-solid fa-eye"></i> View Profile
            </a>
        </div>
    </div>

    <!-- Error Summary Banner (If Validation Failed) -->
    <?php if (!empty($errors)): ?>
    <div class="modern-form-error-banner">
        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
            <div style="font-size: 1.4rem; color: #DC2626; line-height: 1;"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div style="flex: 1;">
                <h3 style="font-size: 0.96rem; font-weight: 700; color: #991B1B; margin: 0 0 0.35rem 0;">Please correct the highlighted errors before saving:</h3>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #B91C1C;">
                    <?php foreach ($errors as $fieldErrors): ?>
                        <?php foreach ((array)$fieldErrors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Master Form Container -->
    <form action="<?= url('/office/customers/' . $customer['id']) ?>" method="POST" id="editCustomerForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <!-- Hidden Tracking Inputs for Removals & Base64 Payload Fallbacks -->
        <input type="hidden" name="remove_profile_photo" id="remove_profile_photo" value="0">
        <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">
        <input type="hidden" name="remove_id_document" id="remove_id_document" value="0">
        <input type="hidden" name="id_document_file_base64" id="id_document_file_base64" value="">
        <input type="hidden" name="id_document_file_name" id="id_document_file_name" value="">

        <div class="modern-form-card" style="margin-bottom: 2rem;">

            <!-- ========================================================================= -->
            <!-- 1. Customer Profile Photo Upload Component (Matching create.php)          -->
            <!-- ========================================================================= -->
            <div class="customer-avatar-upload-card">
                <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('profile_photo').click()" title="Click to upload profile photo">
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleProfilePhotoChange(this)" onclick="event.stopPropagation()">
                    
                    <div class="avatar-default-icon" id="avatarDefaultIcon" style="<?= $hasExistingAvatar ? 'display: none;' : 'display: flex;' ?>">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    
                    <img id="avatarPreviewImg" src="<?= $hasExistingAvatar ? e($existingAvatarUrl) : '' ?>" alt="Profile Photo" style="<?= $hasExistingAvatar ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    
                    <div class="avatar-camera-badge" title="Upload Photo">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <div class="customer-avatar-meta">
                    <div class="customer-avatar-title-row">
                        <span class="customer-avatar-label">Customer Profile Photo</span>
                        <?php if ($hasExistingAvatar): ?>
                            <span class="modern-badge-subtle badge-success-subtle"><i class="fa-solid fa-circle-check"></i> Photo Saved</span>
                        <?php else: ?>
                            <span class="modern-badge-subtle">Optional</span>
                        <?php endif; ?>
                    </div>
                    <p class="customer-avatar-help">Upload a clear front-facing headshot or passport photo. Supported: JPG, PNG, WEBP (Max 3MB).</p>
                    <div class="customer-avatar-actions">
                        <button type="button" class="customer-avatar-btn-upload" onclick="document.getElementById('profile_photo').click()">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> <?= $hasExistingAvatar ? 'Change Photo' : 'Upload Photo' ?>
                        </button>
                        <button type="button" class="customer-avatar-btn-remove" id="btnRemoveAvatar" style="<?= $hasExistingAvatar ? 'display: inline-flex;' : 'display: none;' ?>" onclick="clearProfilePhoto()">
                            <i class="fa-solid fa-trash-can"></i> Remove Photo
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 2. Personal Details Section                                               -->
            <!-- ========================================================================= -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Personal Details</h2>
                    <p class="modern-section-sub">Customer full legal name, gender, salutation, date of birth, and account status.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Salutation / Title -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="title">
                        Salutation <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-signature"></i></span>
                        <select id="title" name="title" class="modern-select-pill">
                            <option value="">Select Salutation</option>
                            <?php 
                            $selectedTitle = $old['title'] ?? $customer['title'] ?? '';
                            $titles = [
                                'Mr'   => 'Mr.',
                                'Mrs'  => 'Mrs.',
                                'Ms'   => 'Ms.',
                                'Dr'   => 'Dr.',
                                'Prof' => 'Prof.'
                            ];
                            foreach ($titles as $val => $lbl): ?>
                                <option value="<?= $val ?>" <?= $selectedTitle === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($errors['title'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['title'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Full Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="name">
                        Full Name <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="name" name="name" class="modern-input-pill" required placeholder="e.g. John Doe" value="<?= e($old['name'] ?? $customer['name'] ?? '') ?>" autocomplete="name">
                    </div>
                    <?php if (!empty($errors['name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Account Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status">
                        Account Status <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <select id="status" name="status" class="modern-select-pill" required>
                            <?php $selectedStatus = $old['status'] ?? $customer['status'] ?? 'active'; ?>
                            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active Profile</option>
                            <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive (Archived)</option>
                        </select>
                    </div>
                    <?php if (!empty($errors['status'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['status'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Date of Birth -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="date_of_birth">
                        Date of Birth
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="modern-input-pill" value="<?= e($old['date_of_birth'] ?? $customer['date_of_birth'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['date_of_birth'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['date_of_birth'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Gender -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="gender">
                        Gender
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-venus-mars"></i></span>
                        <select id="gender" name="gender" class="modern-select-pill">
                            <?php $selectedGender = $old['gender'] ?? $customer['gender'] ?? ''; ?>
                            <option value="" <?= empty($selectedGender) ? 'selected' : '' ?>>Select Gender</option>
                            <option value="male" <?= $selectedGender === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $selectedGender === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $selectedGender === 'other' ? 'selected' : '' ?>>Other</option>
                            <option value="non_binary" <?= $selectedGender === 'non_binary' ? 'selected' : '' ?>>Non-Binary</option>
                            <option value="prefer_not_to_say" <?= $selectedGender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                        </select>
                    </div>
                </div>

                <!-- Nationality / Citizenship -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="country_display">
                        Nationality / Citizenship
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                        <input type="text" id="nationality" name="nationality" class="modern-input-pill" placeholder="e.g. Indian" value="<?= e($old['nationality'] ?? $customer['country'] ?? 'Indian') ?>">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 3. Contact Information Section                                            -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Contact Information</h2>
                    <p class="modern-section-sub">Primary phone, alternate numbers, email address, and emergency contact details.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Primary Mobile -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone">
                        Primary Mobile Number <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" id="phone" name="phone" class="modern-input-pill" required placeholder="e.g. +91 9876543210" value="<?= e($old['phone'] ?? $customer['phone'] ?? '') ?>" autocomplete="tel">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Alternate Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="alternate_phone">
                        Alternate Phone
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" placeholder="e.g. +91 9123456789" value="<?= e($old['alternate_phone'] ?? $customer['alternate_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Email Address -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email">
                        Email Address
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill" placeholder="e.g. john@example.com" value="<?= e($old['email'] ?? $customer['email'] ?? '') ?>" autocomplete="email">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Emergency Contact Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="emergency_contact_name">
                        Emergency Contact Name
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="modern-input-pill" placeholder="e.g. Jane Doe" value="<?= e($old['emergency_contact_name'] ?? '') ?>">
                    </div>
                </div>

                <!-- Emergency Contact Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="emergency_contact_phone">
                        Emergency Phone
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-flip"></i></span>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="modern-input-pill" placeholder="e.g. +91 9988776655" value="<?= e($old['emergency_contact_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Alternate Reachability Tip -->
                <div class="modern-form-group" style="display: flex; flex-direction: column; justify-content: flex-end;">
                    <div class="contact-reachability-hint">
                        <i class="fa-solid fa-circle-info" style="color: #176B5B;"></i>
                        <span>SMS &amp; WhatsApp trip updates will be sent to the Primary Mobile Number.</span>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 4. Residential & Billing Address Section                                  -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Residential &amp; Billing Address</h2>
                    <p class="modern-section-sub">Full postal address used for bookings, tours, pickup directions, and automated GST billing.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Address -->
                <div class="modern-form-group modern-col-span-2">
                    <label class="modern-form-label" for="address_line">
                        Street Address / Landmark
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" id="address_line" name="address_line" class="modern-input-pill" placeholder="Street address, apartment, suite..." value="<?= e($old['address_line'] ?? $customer['address_line'] ?? '') ?>">
                    </div>
                </div>

                <!-- City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="city">
                        City
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                        <input type="text" id="city" name="city" class="modern-input-pill" placeholder="e.g. Mumbai" value="<?= e($old['city'] ?? $customer['city'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- State -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="state">
                        State
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                        <input type="text" id="state" name="state" class="modern-input-pill" placeholder="e.g. Maharashtra" value="<?= e($old['state'] ?? $customer['state'] ?? '') ?>">
                    </div>
                </div>

                <!-- Country -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="country">
                        Country
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-earth-americas"></i></span>
                        <input type="text" id="country" name="country" class="modern-input-pill" placeholder="e.g. India" value="<?= e($old['country'] ?? $customer['country'] ?? 'India') ?>">
                    </div>
                </div>

                <!-- Postal Code -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="postal_code">
                        Postal Code
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="postal_code" name="postal_code" class="modern-input-pill" placeholder="e.g. 400001" value="<?= e($old['postal_code'] ?? $customer['postal_code'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 5. Identification & Verification Documents (Matching create.php)          -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Identification &amp; Verification Documents</h2>
                    <p class="modern-section-sub">Official government identification proof, document numbering, expiry date, and secure document upload.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- ID Document Type -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="id_type">
                        ID Document Type
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                        <select id="id_type" name="id_type" class="modern-select-pill" onchange="onDocumentTypeChange(this.value)">
                            <option value="aadhaar" <?= $currentDocType === 'aadhaar' ? 'selected' : '' ?>>Aadhaar Card</option>
                            <option value="passport" <?= $currentDocType === 'passport' ? 'selected' : '' ?>>Passport</option>
                            <option value="driving_license" <?= $currentDocType === 'driving_license' ? 'selected' : '' ?>>Driving License</option>
                            <option value="voter_id" <?= $currentDocType === 'voter_id' ? 'selected' : '' ?>>Voter ID</option>
                            <option value="other" <?= $currentDocType === 'other' ? 'selected' : '' ?>>Other Official ID</option>
                        </select>
                    </div>
                </div>

                <!-- ID Document Number -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="id_number" id="lblDocNumber">
                        Aadhaar Card Number
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="id_number" name="id_number" class="modern-input-pill" placeholder="e.g. 5432 1234 5678" value="<?= e($old['id_number'] ?? $customer['id_number'] ?? '') ?>">
                    </div>
                </div>

                <!-- Document Expiry Date -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="id_expiry">
                        Document Expiry Date
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                        <input type="date" id="id_expiry" name="id_expiry" class="modern-input-pill" value="<?= e($old['id_expiry'] ?? $customer['id_expiry'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- DYNAMIC PROFESSIONAL DOCUMENT UPLOAD BOX (Matching create.php) -->
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

                    <!-- Empty State Inside Dropzone (Visible when no file exists or removed) -->
                    <div id="dropzoneEmptyState" style="<?= $hasExistingDoc ? 'display: none;' : 'display: block;' ?>">
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

                    <!-- Populated / Preview State Inside Dropzone (Visible when existing doc or new upload) -->
                    <div id="dropzonePreviewState" style="<?= $hasExistingDoc ? 'display: block;' : 'display: none;' ?>">
                        <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                            <div class="doc-preview-thumb-box" onclick="openDocPreviewModal()" style="cursor: pointer;" title="Click to view full preview">
                                <img id="docPreviewImage" src="<?= ($hasExistingDoc && !$isExistingDocPdf) ? e($existingDocUrl) : '' ?>" alt="Document preview" style="<?= ($hasExistingDoc && !$isExistingDocPdf) ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                <div id="docPreviewPdfIcon" style="<?= ($hasExistingDoc && $isExistingDocPdf) ? 'display: block;' : 'display: none;' ?> font-size: 2.2rem; color: #EF4444;">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                            </div>
                            <div class="doc-preview-meta">
                                <div class="doc-preview-name" id="docPreviewName"><?= $hasExistingDoc ? e($existingDocName) : 'document_scan.jpg' ?></div>
                                <div class="doc-preview-size" id="docPreviewSize"><?= $hasExistingDoc ? 'Verified Document on File' : '0 KB' ?></div>
                                <div class="doc-preview-status">
                                    <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Document Attached &amp; Ready
                                </div>
                            </div>
                            <div class="doc-preview-actions">
                                <button type="button" class="btn btn-sm btn-doc-view-preview" onclick="openDocPreviewModal()">
                                    <i class="fa-solid fa-eye"></i> Preview
                                </button>
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

            <!-- ========================================================================= -->
            <!-- 6. Special Operational Notes & Preferences                                -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Operational Notes &amp; Communication Preferences</h2>
                    <p class="modern-section-sub">Internal office observations, VIP tags, driver recommendations, and digital receipt consent.</p>
                </div>
            </div>

            <div class="modern-form-group">
                <label class="modern-form-label" for="notes">
                    Special Operational / Internal Notes
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                    <input type="text" id="notes" name="notes" class="modern-input-pill" placeholder="e.g. VIP client, preferred driver, morning departures, corporate account" value="<?= e($old['notes'] ?? $customer['notes'] ?? '') ?>">
                </div>
            </div>

            <!-- Marketing Preferences Card -->
            <div class="marketing-consent-card">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin: 0;">
                    <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" <?= (!empty($old['marketing_consent']) || (!isset($old['marketing_consent']) && !empty($customer['marketing_consent']))) ? 'checked' : '' ?> style="width: 20px; height: 20px; cursor: pointer; accent-color: #176B5B;">
                    <div>
                        <span style="font-size: 0.92rem; font-weight: 700; color: #0C2B52; display: block;">Send Tour Updates &amp; Digital Receipts</span>
                        <span style="font-size: 0.8rem; color: #64748B; display: block;">Client has consented to receive automated booking confirmations, WhatsApp itinerary cards, and digital tax invoices.</span>
                    </div>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Cancel &amp; Discard
                </a>
                <button type="submit" class="modern-btn-submit" id="btnSubmitEditCustomer">
                    <i class="fa-solid fa-check"></i> <span id="btnSubmitText"><?= __('form.save_changes', [], 'Save Changes') ?></span>
                </button>
            </div>
        </div>
    </form>

    <!-- Professional Document Lightbox Preview Modal (Matching create.php) -->
    <div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
        <div class="doc-lightbox-container" onclick="event.stopPropagation()">
            <div class="doc-lightbox-header">
                <div class="doc-lightbox-title-box">
                    <i class="fa-solid fa-file-shield doc-lightbox-icon"></i>
                    <div>
                        <h3 class="doc-lightbox-title" id="docLightboxTitle">Document Preview</h3>
                        <p class="doc-lightbox-subtitle" id="docLightboxSubtitle">Identity Verification Attachment</p>
                    </div>
                </div>
                <div class="doc-lightbox-actions">
                    <a id="docLightboxDownloadBtn" href="#" download class="doc-lightbox-btn-action" title="Download Document">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button" class="doc-lightbox-close" onclick="closeDocPreviewModal()" aria-label="Close Preview">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="doc-lightbox-body" id="docLightboxBody">
                <img id="docLightboxImg" src="" alt="Full document preview" style="display: none; max-width: 100%; max-height: 72vh; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); object-fit: contain;">
                <div id="docLightboxPdf" style="display: none; width: 100%; min-height: 280px; flex-direction: column; align-items: center; justify-content: center; background: #FFFFFF; border-radius: 12px; border: 1.5px dashed #CBD5E1; padding: 2.5rem 1.5rem; text-align: center;">
                    <i class="fa-solid fa-file-pdf" style="font-size: 3.5rem; color: #EF4444; margin-bottom: 1rem;"></i>
                    <h4 style="font-size: 1.15rem; font-weight: 700; color: #0C2B52; margin-bottom: 0.35rem;" id="docLightboxPdfName">document.pdf</h4>
                    <p style="color: #64748B; font-size: 0.88rem; margin-bottom: 1.25rem;">PDF Document attached and verified for official profile verification.</p>
                    <div style="display: flex; gap: 0.75rem; justify-content: center;">
                        <a id="docLightboxPdfOpenBtn" href="#" target="_blank" class="btn" style="background: #176B5B; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Customer Edit State
const hasExistingAvatar = <?= $hasExistingAvatar ? 'true' : 'false' ?>;
const existingAvatarUrl = <?= json_encode($existingAvatarUrl) ?>;
let isAvatarRemoved = false;
let avatarDataUrl = null;

const hasExistingDoc = <?= $hasExistingDoc ? 'true' : 'false' ?>;
const existingDocUrl = <?= json_encode($existingDocUrl) ?>;
const existingDocName = <?= json_encode($existingDocName) ?>;
const isExistingDocPdf = <?= $isExistingDocPdf ? 'true' : 'false' ?>;
let isDocRemoved = false;
let uploadedDocumentMeta = null;
let uploadedDocumentDataUrl = null;

// Dynamic Document Metadata (Matching create.php)
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
    if (inputNumber && (!inputNumber.value || inputNumber.value.trim() === '')) {
        inputNumber.placeholder = meta.placeholder;
    }
    const emptyState = document.getElementById('dropzoneEmptyState');
    if (helpEl && emptyState && emptyState.style.display !== 'none') {
        helpEl.textContent = meta.help;
    }
}

// Profile Photo Handlers
function handleProfilePhotoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please select a valid image file (PNG, JPG, WEBP)', 'error');
        } else {
            alert('Please select a valid image file (PNG, JPG, WEBP)');
        }
        input.value = '';
        return;
    }

    if (file.size > 3 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Profile photo size exceeds 3MB limit', 'error');
        } else {
            alert('Profile photo size exceeds 3MB limit');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        avatarDataUrl = e.target.result;
        const b64Input = document.getElementById('profile_photo_base64');
        if (b64Input) b64Input.value = avatarDataUrl;

        const removeInput = document.getElementById('remove_profile_photo');
        if (removeInput) removeInput.value = '0';
        isAvatarRemoved = false;

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

    const removeInput = document.getElementById('remove_profile_photo');
    if (removeInput) removeInput.value = '1';

    avatarDataUrl = null;
    isAvatarRemoved = true;

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

// Document Upload Handlers
function handleDocumentFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Document file size exceeds 5MB limit', 'error');
        } else {
            alert('Document file size exceeds 5MB limit');
        }
        input.value = '';
        return;
    }

    const removeInput = document.getElementById('remove_id_document');
    if (removeInput) removeInput.value = '0';
    isDocRemoved = false;

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

    const reader = new FileReader();
    reader.onload = function(e) {
        uploadedDocumentDataUrl = e.target.result;
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

    const removeInput = document.getElementById('remove_id_document');
    if (removeInput) removeInput.value = '1';

    uploadedDocumentMeta = null;
    uploadedDocumentDataUrl = null;
    isDocRemoved = true;

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const imgEl = document.getElementById('docPreviewImage');

    if (imgEl) imgEl.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// Professional Document Lightbox Modal Handlers
function openDocPreviewModal() {
    let docUrl = uploadedDocumentDataUrl;
    let docName = uploadedDocumentMeta ? uploadedDocumentMeta.name : null;
    let isPdf = uploadedDocumentMeta ? (!uploadedDocumentMeta.type.startsWith('image/')) : false;
    let sizeText = uploadedDocumentMeta ? uploadedDocumentMeta.size : '';

    if (!docUrl && hasExistingDoc && !isDocRemoved) {
        docUrl = existingDocUrl;
        docName = existingDocName;
        isPdf = isExistingDocPdf;
        sizeText = 'Verified Document on File';
    }

    if (!docUrl) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('No document uploaded to preview', 'info');
        } else {
            alert('No document uploaded to preview');
        }
        return;
    }

    const modal = document.getElementById('docPreviewModal');
    const imgEl = document.getElementById('docLightboxImg');
    const pdfEl = document.getElementById('docLightboxPdf');
    const titleEl = document.getElementById('docLightboxTitle');
    const subEl = document.getElementById('docLightboxSubtitle');
    const dlBtn = document.getElementById('docLightboxDownloadBtn');
    const pdfOpenBtn = document.getElementById('docLightboxPdfOpenBtn');
    const pdfNameEl = document.getElementById('docLightboxPdfName');

    const fileName = docName || 'document_preview';

    if (titleEl) titleEl.textContent = fileName;
    if (subEl) subEl.textContent = (sizeText ? sizeText + ' • ' : '') + 'Verified Identity Attachment';
    if (dlBtn) {
        dlBtn.href = docUrl || '#';
        dlBtn.download = fileName;
    }

    if (!isPdf) {
        if (imgEl) {
            imgEl.src = docUrl;
            imgEl.style.display = 'block';
        }
        if (pdfEl) pdfEl.style.display = 'none';
    } else {
        if (imgEl) imgEl.style.display = 'none';
        if (pdfEl) pdfEl.style.display = 'flex';
        if (pdfNameEl) pdfNameEl.textContent = fileName;
        if (pdfOpenBtn) pdfOpenBtn.href = docUrl || '#';
    }

    if (modal) modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDocPreviewModal(e) {
    if (e && e.target && e.target !== e.currentTarget && !e.target.closest('.doc-lightbox-close')) {
        return;
    }
    const modal = document.getElementById('docPreviewModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocPreviewModal();
    }
});

// Drag & Drop Initializers
document.addEventListener('DOMContentLoaded', function() {
    // Initial document type sync
    const initialDocType = document.getElementById('id_type');
    if (initialDocType) {
        onDocumentTypeChange(initialDocType.value);
    }

    // Avatar Drag & Drop
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

    // Document Dropzone Drag & Drop
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

    // Form submission state
    const editForm = document.getElementById('editCustomerForm');
    const submitBtn = document.getElementById('btnSubmitEditCustomer');
    const submitText = document.getElementById('btnSubmitText');
    if (editForm && submitBtn) {
        editForm.addEventListener('submit', function() {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.85';
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving Changes...</span>';
        });
    }
});
</script>

<style>
/* Modern Form Layout & Headers */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem;
}

.modern-form-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding: 0 0.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.modern-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.modern-back-btn:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
    transform: translateX(-3px);
}

.modern-form-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0 0 2px 0;
    letter-spacing: -0.01em;
}

.modern-form-subtitle {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0;
}

.customer-code-badge-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 18px;
    border-radius: 50px;
    background: #E8F4F0;
    color: #176B5B;
    border: 1.5px solid #B2D8CF;
    font-size: 0.88rem;
    font-weight: 800;
    box-shadow: 0 2px 6px rgba(23, 107, 91, 0.08);
}

.btn-view-profile-header {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.btn-view-profile-header:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
}

.modern-form-error-banner {
    background: #FEF2F2;
    border: 1.5px solid #FECACA;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.08);
}

/* Master Form Card */
.modern-form-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 6px 24px rgba(12, 43, 82, 0.04);
}

/* Section Headers */
.modern-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 0.85rem;
    margin-bottom: 1.5rem;
    border-bottom: 1.5px solid #F1F5F9;
}

.modern-section-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.modern-section-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0 0 2px 0;
}

.modern-section-sub {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0;
}

/* ========================================================================= */
/* 1. Customer Avatar Component (Matching create.php)                        */
/* ========================================================================= */
.customer-avatar-upload-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #F8FAFD 0%, #F1F6FA 100%);
    border: 1.5px solid #DCE6F2;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 2rem;
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
.badge-success-subtle {
    background: #E8F8F0;
    color: #059669;
    border: 1px solid #A7F3D0;
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
/* Form Grids & Inputs                                                       */
/* ========================================================================= */
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}
.modern-col-span-2 {
    grid-column: span 2;
}

@media (max-width: 900px) {
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .modern-col-span-2 {
        grid-column: span 1;
    }
}

.modern-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.modern-form-label {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0C2B52;
}
.modern-form-label .req {
    color: #EF4444;
    font-weight: 800;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.modern-input-icon {
    position: absolute;
    left: 1rem;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
    transition: color 0.2s ease;
}

.modern-input-pill,
.modern-select-pill {
    width: 100%;
    height: 48px;
    background: #F8FAFD;
    border: 1.5px solid #CBD5E1;
    border-radius: 12px;
    padding: 0 1rem 0 2.85rem;
    font-size: 0.92rem;
    color: #0C2B52;
    outline: none;
    transition: all 0.2s ease;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
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
    font-size: 0.78rem;
    font-weight: 600;
    color: #DC2626;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 3px;
}

.contact-reachability-hint {
    background: #F1F8F6;
    border: 1px solid #B2D8CF;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.8rem;
    color: #176B5B;
    font-weight: 600;
    min-height: 48px;
}

/* ========================================================================= */
/* Document Upload Dropzone (Matching create.php)                            */
/* ========================================================================= */
.document-dropzone {
    border: 2px dashed #B2D8CF;
    border-radius: 18px;
    background: #F8FAFD;
    padding: 1.75rem 1.5rem;
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
    position: relative;
}
.doc-thumb-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(12, 43, 82, 0.55);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    border-radius: 10px;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.doc-preview-thumb-box:hover .doc-thumb-hover-overlay {
    opacity: 1;
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

.btn-doc-view-preview {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    font-weight: 700;
    border-radius: 8px;
    padding: 0.38rem 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-doc-view-preview:hover {
    background: #125246;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.25);
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

/* Marketing & Consent Card */
.marketing-consent-card {
    background: #F8FAFD;
    border: 1.5px solid #DCE6F2;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-top: 1.25rem;
    margin-bottom: 2rem;
}

/* Modern Form Actions */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1.5px solid #F1F5F9;
}

.modern-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.65rem 1.4rem;
    border-radius: 12px;
    background: #F1F5F9;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}
.modern-btn-cancel:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.modern-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.65rem 1.85rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 0.92rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    transition: all 0.2s ease;
}
.modern-btn-submit:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
}

/* ========================================================================= */
/* Document Lightbox Modal (Matching create.php)                             */
/* ========================================================================= */
.doc-lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(12, 43, 82, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    animation: docFadeIn 0.2s ease-out;
}
@keyframes docFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.doc-lightbox-container {
    background: #FFFFFF;
    border-radius: 18px;
    box-shadow: 0 25px 50px -12px rgba(12, 43, 82, 0.35);
    max-width: 820px;
    width: 100%;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1.5px solid #E2E8F0;
    animation: docZoomIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes docZoomIn {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.doc-lightbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.5rem;
    border-bottom: 1.5px solid #F1F5F9;
    background: #F8FAFD;
}
.doc-lightbox-title-box {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.doc-lightbox-icon {
    font-size: 1.5rem;
    color: #176B5B;
}
.doc-lightbox-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    max-width: 480px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.doc-lightbox-subtitle {
    font-size: 0.8rem;
    color: #64748B;
    margin: 2px 0 0 0;
}
.doc-lightbox-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.doc-lightbox-btn-action {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    text-decoration: none;
    cursor: pointer;
}
.doc-lightbox-btn-action:hover {
    background: #E8F4F0;
    border-color: #176B5B;
    color: #176B5B;
}
.doc-lightbox-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.doc-lightbox-close:hover {
    background: #FEE2E2;
    border-color: #FECACA;
    color: #B91C1C;
    transform: rotate(90deg);
}
.doc-lightbox-body {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    background: #F1F5F9;
    min-height: 300px;
}
</style>
