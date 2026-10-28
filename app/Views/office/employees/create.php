<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Modern Employee Registration Form
 * Professional 5-step wizard matching customers/create architecture
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional 5-Step Stepper Header -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Personal Identity</span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Role &amp; Contract</span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">License &amp; Documents</span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label">Credentials</span>
                </button>
                <div class="modern-step-connector" id="stepConn4"></div>

                <!-- Step 5 -->
                <button type="button" class="modern-step-item" data-step-target="5" onclick="wizardGoToStep(5)">
                    <span class="modern-step-circle">5</span>
                    <span class="modern-step-label">Review &amp; Submit</span>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: __('form.please_review_errors')) ?>;
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
    <form action="<?= url('/office/employees') ?>" method="POST" id="createEmployeeForm" enctype="multipart/form-data" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <!-- Default status saved as active -->
        <input type="hidden" name="status" value="active">

        <!-- Anti-autofill decoy fields to defeat aggressive browser password pre-filling -->
        <input type="text" name="fake_username_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="off">
        <input type="password" name="fake_password_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="new-password">

        <div class="modern-form-card">

            <!-- ========================================================================= -->
            <!-- STEP 1: Personal Details, Photo & Address                                -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel1">

                <!-- Professional Profile Photo Upload Component (Exact Customers Architecture) -->
                <div class="customer-avatar-upload-card">
                    <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('profile_photo').click()" title="Click to upload staff photo">
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleProfilePhotoChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">
                        <div class="avatar-default-icon" id="avatarDefaultIcon">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <img id="avatarPreviewImg" src="" alt="Profile Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <div class="avatar-camera-badge" title="Upload Photo">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="customer-avatar-meta">
                        <div class="customer-avatar-title-row">
                            <span class="customer-avatar-label">Staff Profile Photo</span>
                            <span class="modern-badge-subtle">Optional</span>
                        </div>
                        <p class="customer-avatar-help">Upload a clean front-facing corporate headshot or portrait. Supported: JPG, PNG, WEBP (Max 3MB).</p>
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

                <!-- Row 1: First Name, Last Name, Primary Phone, Corporate Email -->
                <div class="customer-name-row">
                    <!-- First Name -->
                    <div class="modern-form-group name-group">
                        <label class="modern-form-label" for="first_name">
                            First Name <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="first_name" name="first_name" class="modern-input-pill" required placeholder="e.g. Vikramaditya" value="<?= e($old['first_name'] ?? '') ?>" autocomplete="given-name" oninput="syncUsernameSuggestion()">
                        </div>
                        <?php if (!empty($errors['first_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['first_name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name -->
                    <div class="modern-form-group name-group">
                        <label class="modern-form-label" for="last_name">
                            Last Name <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-tag"></i></span>
                            <input type="text" id="last_name" name="last_name" class="modern-input-pill" required placeholder="e.g. Rathore" value="<?= e($old['last_name'] ?? '') ?>" autocomplete="family-name" oninput="syncUsernameSuggestion()">
                        </div>
                        <?php if (!empty($errors['last_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['last_name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Primary Mobile Number -->
                    <div class="modern-form-group phone-group">
                        <label class="modern-form-label" for="phone">
                            Mobile Number <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="phone" name="phone" class="modern-input-pill" required placeholder="e.g. +91 9876543210" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel">
                        </div>
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Corporate Email Address -->
                    <div class="modern-form-group email-group">
                        <label class="modern-form-label" for="email">
                            Corporate Email
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="modern-input-pill" placeholder="e.g. v.rathore@manaswitours.com" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 2: Date of Birth, Gender, Nationality (3 Columns) -->
                <div class="modern-form-grid-3">
                    <!-- Date of Birth -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="date_of_birth">
                            Date of Birth
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="modern-input-pill" value="<?= e($old['date_of_birth'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="gender">
                            Gender
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-venus-mars"></i></span>
                            <select id="gender" name="gender" class="modern-select-pill">
                                <option value="" <?= empty($old['gender']) ? 'selected' : '' ?>>Select Gender</option>
                                <option value="male" <?= ($old['gender'] ?? 'male') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                <option value="prefer_not_to_say" <?= ($old['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nationality -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="nationality">
                            Nationality
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                            <input type="text" id="nationality" name="nationality" class="modern-input-pill" placeholder="e.g. Indian" value="<?= e($old['nationality'] ?? 'Indian') ?>">
                        </div>
                    </div>
                </div>

                <!-- Row 3 & 4: Exact Address Part from customers/create -->
                <!-- Row 3: Address (2 Columns), City (1 Column) -->
                <div class="modern-form-grid-3">
                    <!-- Address Line / Street -->
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="address_line">
                            Address <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="address_line" name="address_line" class="modern-input-pill" required placeholder="Street address, apartment, suite..." value="<?= e($old['address_line'] ?? $old['address'] ?? '') ?>">
                        </div>
                        <?php if (!empty($errors['address_line'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['address_line'][0]) ?></div>
                        <?php endif; ?>
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
                        <?php if (!empty($errors['city'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['city'][0]) ?></div>
                        <?php endif; ?>
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
                        <?php if (!empty($errors['state'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['state'][0]) ?></div>
                        <?php endif; ?>
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
                        <?php if (!empty($errors['country'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['country'][0]) ?></div>
                        <?php endif; ?>
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
                        <?php if (!empty($errors['postal_code'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['postal_code'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/employees') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: Role & Contract                                                   -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel2" style="display: none;">

                <!-- Row 1: Role, Employment Type, Date of Joining -->
                <div class="modern-form-grid-3">
                    <!-- Clean Role Dropdown (Default: Driver) -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="role">
                            Role <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                            <select id="role" name="role" class="modern-select-pill" required onchange="onRoleChanged(this.value)">
                                <option value="driver" <?= ($old['role'] ?? 'driver') === 'driver' ? 'selected' : '' ?> data-name="Chauffeur / Driver">🚗 Driver / Chauffeur</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <?php if (in_array(strtolower($r['slug'] ?? ''), ['driver', 'chauffeur'])) continue; ?>
                                        <option value="<?= e($r['slug']) ?>" <?= ($old['role'] ?? '') === $r['slug'] ? 'selected' : '' ?> data-name="<?= e($r['name']) ?>">
                                            <?= e($r['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="dispatcher" <?= ($old['role'] ?? '') === 'dispatcher' ? 'selected' : '' ?> data-name="Fleet Dispatcher">Fleet Dispatcher</option>
                                    <option value="manager" <?= ($old['role'] ?? '') === 'manager' ? 'selected' : '' ?> data-name="Operations Manager">Operations Manager</option>
                                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?> data-name="System Administrator">System Administrator</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Employment Contract Type -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="employment_type">
                            Employment Contract Type <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-file-contract"></i></span>
                            <select id="employment_type" name="employment_type" class="modern-select-pill" required>
                                <option value="full_time" <?= ($old['employment_type'] ?? 'full_time') === 'full_time' ? 'selected' : '' ?>>Full-Time Regular</option>
                                <option value="part_time" <?= ($old['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part-Time Staff</option>
                                <option value="contract" <?= ($old['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Commercial Contract</option>
                                <option value="probation" <?= ($old['employment_type'] ?? '') === 'probation' ? 'selected' : '' ?>>Probationary Period</option>
                            </select>
                        </div>
                    </div>

                    <!-- Joining Date -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="joining_date">
                            Date of Joining <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="joining_date" name="joining_date" class="modern-input-pill" required value="<?= e($old['joining_date'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Alternate Phone & Emergency Contacts -->
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
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="modern-input-pill" placeholder="e.g. Rajesh Rathore (Brother)" value="<?= e($old['emergency_contact_name'] ?? '') ?>">
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
            <!-- STEP 3: Driving License & Compliance Documents                           -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel3" style="display: none;">

                <!-- SECTION 3A: Commercial Driving License Verification -->
                <div id="chauffeurLicenseSection" style="background: #F0FDF8; border: 1.5px solid #A7F3D0; padding: 1.5rem; border-radius: 18px; margin-bottom: 1.75rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-weight: 700; color: #16443B; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-id-card-clip" style="color: #176B5B;"></i> Commercial Driving License Verification
                        </span>
                        <span style="padding: 4px 12px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-size: 0.78rem; font-weight: 700; border: 1px solid #A7F3D0;">
                            Fleet Operational Requirement
                        </span>
                    </div>

                    <!-- Row 1: Driving License Number, Expiry Date, Experience in Years -->
                    <div class="modern-form-grid-3" style="margin-bottom: 1.25rem;">
                        <!-- Driving License Number -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="license_number">
                                Driving Licence <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                                <input type="text" id="license_number" name="license_number" class="modern-input-pill" value="<?= e($old['license_number'] ?? '') ?>" placeholder="e.g. DL-0420110098765" style="font-family: monospace; text-transform: uppercase;">
                            </div>
                        </div>

                        <!-- License Expiry Date -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="license_expiry">
                                License Expiry Date <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <input type="date" id="license_expiry" name="license_expiry" class="modern-input-pill" value="<?= e($old['license_expiry'] ?? date('Y-m-d', strtotime('+3 years'))) ?>">
                            </div>
                        </div>

                        <!-- Experience Years -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="experience_years">
                                <?= e(__('employee_form.experience_years', [], 'Experience (Years)')) ?>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-award"></i></span>
                                <input type="number" id="experience_years" name="experience_years" class="modern-input-pill" value="<?= e($old['experience_years'] ?? '5') ?>" min="0" max="50">
                            </div>
                        </div>
                    </div>

                    <!-- Driving License Document Upload Dropzone -->
                    <div class="document-upload-container">
                        <div class="document-upload-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <label class="modern-form-label" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: #176B5B;"></i>
                                <span>Upload Driving Licence Document / Scan</span>
                            </label>
                            <span class="modern-badge" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                                Commercial Heavy / Light Vehicle (LMV / HMV)
                            </span>
                        </div>

                        <!-- Interactive Dropzone for License Document -->
                        <div class="document-dropzone" id="licenseDropzone" onclick="document.getElementById('license_document_file').click()">
                            <input type="file" id="license_document_file" name="license_document_file" accept="image/png,image/jpeg,image/jpg,image/webp,application/pdf" style="display: none;" onchange="handleLicenseFileChange(this)" onclick="event.stopPropagation()">
                            <input type="hidden" name="license_document_file_base64" id="license_document_file_base64" value="">
                            <input type="hidden" name="license_document_file_name" id="license_document_file_name" value="">

                            <!-- Empty State Inside Dropzone -->
                            <div id="licenseDropzoneEmpty">
                                <div class="document-dropzone-halo">
                                    <i class="fa-solid fa-id-card" style="color: #176B5B;"></i>
                                </div>
                                <div class="document-dropzone-prompt">
                                    <strong>Click to upload</strong> or drag and drop your driving licence copy
                                </div>
                                <div class="document-dropzone-sub">
                                    Supported formats: PNG, JPG, JPEG, WEBP or PDF (Max 5MB)
                                </div>
                            </div>

                            <!-- Populated / Preview State Inside Dropzone -->
                            <div id="licenseDropzonePreview" style="display: none;">
                                <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                                    <div class="doc-preview-thumb-box" onclick="openDocPreviewModal('license')" style="cursor: pointer;" title="Click to view full preview">
                                        <img id="licensePreviewImage" src="" alt="License preview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        <div id="licensePreviewPdfIcon" style="display: none; font-size: 2.2rem; color: #EF4444;">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                    </div>
                                    <div class="doc-preview-meta">
                                        <div class="doc-preview-name" id="licensePreviewName">driving_license.jpg</div>
                                        <div class="doc-preview-size" id="licensePreviewSize">0 KB</div>
                                        <div class="doc-preview-status">
                                            <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Driving Licence Attached &amp; Verified
                                        </div>
                                    </div>
                                    <div class="doc-preview-actions">
                                        <button type="button" class="btn btn-sm btn-doc-view-preview" onclick="openDocPreviewModal('license')">
                                            <i class="fa-solid fa-eye"></i> Preview
                                        </button>
                                        <button type="button" class="btn btn-sm" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-weight: 700; border-radius: 8px; padding: 0.35rem 0.85rem;" onclick="document.getElementById('license_document_file').click()">
                                            <i class="fa-solid fa-arrows-rotate"></i> Change
                                        </button>
                                        <button type="button" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; font-weight: 700; border-radius: 8px; padding: 0.35rem 0.85rem;" onclick="clearLicenseUpload()">
                                            <i class="fa-solid fa-trash-can"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3B: Identification & Additional Document Upload Dropdown (Exact Customers Step 3) -->
                <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; padding: 1.5rem; border-radius: 18px; margin-bottom: 1.75rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-weight: 700; color: #1e293b; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-passport" style="color: #176B5B;"></i> Government Identification &amp; Compliance Documents
                        </span>
                        <span class="modern-badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                            Official Identity Dossier
                        </span>
                    </div>

                    <div class="modern-form-grid-3" style="margin-bottom: 1.25rem;">
                        <!-- Document Type Dropdown (Exact Customers Architecture) -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="id_type">ID Document Type</label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                                <select id="id_type" name="id_type" class="modern-select-pill" onchange="onDocumentTypeChange(this.value)">
                                    <option value="aadhaar" selected>Aadhaar Card</option>
                                    <option value="pan_card">PAN Card</option>
                                    <option value="passport">Passport</option>
                                    <option value="police_verification">Police Verification Certificate</option>
                                    <option value="voter_id">Voter ID Card</option>
                                    <option value="other">Other Official Certificate</option>
                                </select>
                            </div>
                        </div>

                        <!-- Document Number -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="id_number" id="lblDocNumber">Aadhaar Card Number</label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                                <input type="text" id="id_number" name="id_number" class="modern-input-pill" placeholder="e.g. 5432 1234 5678" value="<?= e($old['id_number'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Document Expiry Date -->
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="id_expiry">Document Expiry Date</label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <input type="date" id="id_expiry" name="id_expiry" class="modern-input-pill" value="<?= e($old['id_expiry'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Document Upload Dropzone -->
                    <div class="document-upload-container">
                        <div class="document-upload-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <label class="modern-form-label" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: #176B5B;"></i>
                                <span id="docUploadBoxTitle">Upload Aadhaar Card Scan / Image</span>
                            </label>
                            <span id="docTypeBadge" class="modern-badge" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                                UIDAI 12-Digit Verification
                            </span>
                        </div>

                        <!-- Interactive Dropzone for ID Document -->
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
                                    <div class="doc-preview-thumb-box" onclick="openDocPreviewModal('id')" style="cursor: pointer;" title="Click to view full preview">
                                        <img id="docPreviewImage" src="" alt="Document preview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        <div id="docPreviewPdfIcon" style="display: none; font-size: 2.2rem; color: #EF4444;">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                    </div>
                                    <div class="doc-preview-meta">
                                        <div class="doc-preview-name" id="docPreviewName">document_scan.jpg</div>
                                        <div class="doc-preview-size" id="docPreviewSize">0 KB</div>
                                        <div class="doc-preview-status">
                                            <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Document Attached &amp; Ready
                                        </div>
                                    </div>
                                    <div class="doc-preview-actions">
                                        <button type="button" class="btn btn-sm btn-doc-view-preview" onclick="openDocPreviewModal('id')">
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
                </div>

                <!-- Internal Notes -->
                <div class="modern-form-group" style="margin-bottom: 0;">
                    <label class="modern-form-label" for="notes">Special Operational / Verification Notes</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                        <input type="text" id="notes" name="notes" class="modern-input-pill" placeholder="e.g. VIP chauffeur, seasoned mountain route experience, background verified" value="<?= e($old['notes'] ?? '') ?>">
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
            <!-- STEP 4: Access Credentials                                                -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel4" style="display: none;">

                <!-- System Login Credentials Card -->
                <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 18px; padding: 1.5rem; margin-bottom: 1.75rem;">
                    <label for="create_account" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0 0 1rem 0;">
                        <div>
                            <span style="font-size: 1rem; font-weight: 700; color: #0C2B52; display: block;">Create System Login Account</span>
                            <span style="font-size: 0.82rem; color: #64748B; display: block; margin-top: 2px;">Allow this employee to sign in to the operational portal &amp; driver mobile app</span>
                        </div>
                        <input type="checkbox" id="create_account" name="create_account" value="1" <?= (!isset($old['create_account']) || !empty($old['create_account']) || !empty($old['username'])) ? 'checked' : '' ?> onchange="toggleAccountFields(this.checked)" style="width: 20px; height: 20px; accent-color: #176B5B; cursor: pointer;">
                    </label>

                    <div id="accountFieldsContainer" style="<?= (!isset($old['create_account']) || !empty($old['create_account']) || !empty($old['username'])) ? 'display: block;' : 'display: none;' ?>">
                        <div style="background: #FFFFFF; border: 1.5px solid #E6EEF8; border-radius: 14px; padding: 1.25rem; margin-top: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                                <span style="font-weight: 700; font-size: 0.92rem; color: #0C2B52;"><i class="fa-solid fa-lock" style="color: #176B5B;"></i> User Authentication Credentials</span>
                                <button type="button" class="customer-avatar-btn-upload" onclick="generateCredentials()" style="padding: 6px 14px; font-size: 0.8rem; background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF;">
                                    <span>⚡ Auto-Generate</span>
                                </button>
                            </div>

                            <div class="modern-form-group" style="margin-bottom: 1.25rem;">
                                <label class="modern-form-label" for="username">Username <span class="req">*</span></label>
                                <div class="modern-input-wrapper">
                                    <span class="modern-input-icon"><i class="fa-solid fa-at"></i></span>
                                    <input type="text" id="username" name="username" class="modern-input-pill" value="<?= e($old['username'] ?? '') ?>" placeholder="e.g. v.rathore" pattern="^[a-zA-Z0-9_.]+$" style="font-family: monospace;">
                                </div>
                                <small class="modern-field-hint">Unique alphanumeric username for office / driver app sign-in.</small>
                            </div>

                            <div class="modern-form-grid-2">
                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="password">Password <span class="req">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" id="password" name="password" class="modern-input-pill" minlength="6" placeholder="Min. 6 characters" autocomplete="new-password">
                                    </div>
                                </div>

                                <div class="modern-form-group">
                                    <label class="modern-form-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <span class="modern-input-icon"><i class="fa-solid fa-shield-check"></i></span>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="modern-input-pill" minlength="6" placeholder="Re-enter password" autocomplete="new-password">
                                    </div>
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
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(4)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 5: Review & Submit                                                   -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel5" style="display: none;">

                <!-- Live Executive Dossier Container (Exact Customers Architecture) -->
                <div class="review-dossier-wrapper">
                    <!-- Profile Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container">
                            <div class="review-avatar-monogram" id="sumAvatarMonogram">
                                <span id="sumAvatarInitial">E</span>
                            </div>
                            <img id="sumAvatarImg" src="" alt="Staff Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-id-badge"></i>
                                    <code><?= e($employeeCode ?? 'VG-EMP-AUTO') ?></code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item" id="sumTagRole">
                                    <i class="fa-solid fa-briefcase"></i> <span id="sumTagRoleText">Driver / Chauffeur</span>
                                </span>
                                <span class="review-tag-item" id="sumTagContract">
                                    <i class="fa-solid fa-file-contract"></i> <span id="sumTagContractText">Full-Time</span>
                                </span>
                                <span class="review-status-pill is-active" id="sumStatusPill">
                                    <i class="fa-solid fa-circle-check"></i> Active on Roster
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
                                    <span class="review-label">Date of Birth</span>
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
                                    <span class="review-label">Corporate Email</span>
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

                        <!-- Card 3: Role & Contract Details -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-briefcase"></i> Role &amp; Contract Details
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Assigned Role</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardRole">Driver / Chauffeur</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Contract Type</span>
                                    <span class="review-value" id="sumCardContract">Full-Time</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Date of Joining</span>
                                    <span class="review-value font-semibold" id="sumCardJoiningDate"><?= date('M d, Y') ?></span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Emergency Contact</span>
                                    <span class="review-value" id="sumCardEmergContact">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Emergency Phone</span>
                                    <span class="review-value font-bold highlight-teal" id="sumCardEmergPhone">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Compliance & Both Documents Rich Previews -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <i class="fa-solid fa-file-shield"></i> Compliance &amp; Verification Documents
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Driving Licence No.</span>
                                    <span class="review-value font-mono font-bold" id="sumCardLicenseNo">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">License Expiry</span>
                                    <span class="review-value" id="sumCardLicenseExpiry">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Driving Experience</span>
                                    <span class="review-value" id="sumCardExperience">5 Years</span>
                                </div>

                                <!-- Rich Preview Card for Driving License Document (Matching Customers Architecture) -->
                                <div class="review-info-row review-doc-row" style="flex-direction: column; align-items: stretch; gap: 0.45rem;">
                                    <span class="review-label">Driving Licence Attached Document</span>
                                    <div id="sumCardLicenseDoc" style="width: 100%;">
                                        <span class="review-doc-badge empty"><i class="fa-solid fa-circle-xmark"></i> Not attached (Optional)</span>
                                    </div>
                                </div>

                                <div class="review-info-row" style="margin-top: 0.4rem; padding-top: 0.65rem; border-top: 1px dashed #E2E8F0;">
                                    <span class="review-label">ID Document Type</span>
                                    <span class="review-value font-semibold" id="sumCardDocType">Aadhaar Card</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">ID Document No.</span>
                                    <span class="review-value font-mono" id="sumCardDocNumber">—</span>
                                </div>

                                <!-- Rich Preview Card for ID Document (Matching Customers Architecture) -->
                                <div class="review-info-row review-doc-row" style="flex-direction: column; align-items: stretch; gap: 0.45rem;">
                                    <span class="review-label">Government ID Attached Document</span>
                                    <div id="sumCardIdDoc" style="width: 100%;">
                                        <span class="review-doc-badge empty"><i class="fa-solid fa-circle-xmark"></i> Not attached (Optional)</span>
                                    </div>
                                </div>

                                <div class="review-info-row" style="margin-top: 0.4rem; padding-top: 0.65rem; border-top: 1px dashed #E2E8F0;">
                                    <span class="review-label">Internal Notes</span>
                                    <span class="review-value" id="sumCardNotes">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(4)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="modern-btn-submit" id="btnSubmitEmployee">
                        <i class="fa-solid fa-check"></i> <span id="btnSubmitEmployeeText">Register Employee Profile</span>
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- Professional Document Lightbox Preview Modal -->
    <div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
        <div class="doc-lightbox-container" onclick="event.stopPropagation()">
            <div class="doc-lightbox-header">
                <div class="doc-lightbox-title-box">
                    <i class="fa-solid fa-file-shield doc-lightbox-icon"></i>
                    <div>
                        <h3 class="doc-lightbox-title" id="docLightboxTitle">Document Preview</h3>
                        <p class="doc-lightbox-subtitle" id="docLightboxSubtitle">Identity &amp; Compliance Verification Attachment</p>
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

<!-- ========================================================================= -->
<!-- JAVASCRIPT: 5-Step Stepper, Dynamic Dropzones & Live Dossier Population   -->
<!-- ========================================================================= -->
<script>
let currentWizardStep = 1;
const totalSteps = 5;

let avatarDataUrl = null;

let licenseDocDataUrl = null;
let licenseDocMeta = null;
let licenseDocIsPdf = false;
let licenseDocFileName = '';

let idDocDataUrl = null;
let idDocMeta = null;
let idDocIsPdf = false;
let idDocFileName = '';

function updateStepperUI(activeStep) {
    currentWizardStep = activeStep;

    // Update Step items
    const stepItems = document.querySelectorAll('.modern-step-item');
    stepItems.forEach(item => {
        const stepNum = parseInt(item.getAttribute('data-step-target'), 10);
        if (stepNum === activeStep) {
            item.classList.add('active');
            item.classList.remove('completed');
        } else if (stepNum < activeStep) {
            item.classList.add('completed');
            item.classList.remove('active');
        } else {
            item.classList.remove('active', 'completed');
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

    // When on step 5, update the comprehensive live summary dossier
    if (activeStep === 5) {
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

    const isImg = file.type.startsWith('image/') || /\.(png|jpe?g|webp|gif|bmp)$/i.test(file.name);
    if (!isImg) {
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

// Role Change Handler (Step 2)
function onRoleChanged(roleVal) {
    roleVal = (roleVal || '').toLowerCase();
    const chauffeurSection = document.getElementById('chauffeurLicenseSection');
    if (chauffeurSection) {
        chauffeurSection.style.display = 'block'; // Always accessible for verified transport operations
    }
    syncUsernameSuggestion();
}

// Driving License File Upload Handlers (Step 3)
function handleLicenseFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    licenseDocFileName = file.name;

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Driving license scan exceeds 5MB size limit', 'error');
        }
        input.value = '';
        return;
    }

    const sizeKb = (file.size / 1024).toFixed(1);
    const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

    licenseDocMeta = {
        name: file.name,
        size: sizeStr,
        type: file.type
    };

    const isImg = file.type.startsWith('image/') || /\.(png|jpe?g|webp|gif|bmp)$/i.test(file.name);
    const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
    licenseDocIsPdf = isPdf;

    const emptyState = document.getElementById('licenseDropzoneEmpty');
    const previewState = document.getElementById('licenseDropzonePreview');
    const nameLabel = document.getElementById('licensePreviewName');
    const sizeLabel = document.getElementById('licensePreviewSize');
    const previewImg = document.getElementById('licensePreviewImage');
    const pdfIcon = document.getElementById('licensePreviewPdfIcon');

    if (nameLabel) nameLabel.textContent = file.name;
    if (sizeLabel) sizeLabel.textContent = sizeStr;

    // Show preview container immediately
    if (emptyState) emptyState.style.display = 'none';
    if (previewState) previewState.style.display = 'block';

    const reader = new FileReader();
    reader.onload = function(e) {
        licenseDocDataUrl = e.target.result;
        const b64 = document.getElementById('license_document_file_base64');
        const nameInput = document.getElementById('license_document_file_name');
        if (b64) b64.value = licenseDocDataUrl;
        if (nameInput) nameInput.value = licenseDocFileName;

        if (isImg) {
            if (previewImg) {
                previewImg.src = licenseDocDataUrl;
                previewImg.style.display = 'block';
            }
            if (pdfIcon) pdfIcon.style.display = 'none';
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
            if (pdfIcon) pdfIcon.style.display = 'flex';
        }
    };
    reader.readAsDataURL(file);
}

function clearLicenseUpload() {
    const input = document.getElementById('license_document_file');
    if (input) input.value = '';
    const b64 = document.getElementById('license_document_file_base64');
    const nameInput = document.getElementById('license_document_file_name');
    if (b64) b64.value = '';
    if (nameInput) nameInput.value = '';
    licenseDocDataUrl = null;
    licenseDocMeta = null;
    licenseDocIsPdf = false;
    licenseDocFileName = '';

    const emptyState = document.getElementById('licenseDropzoneEmpty');
    const previewState = document.getElementById('licenseDropzonePreview');
    const previewImg = document.getElementById('licensePreviewImage');
    if (previewImg) previewImg.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// ID Document Type Dynamic Text & Dropzone Handlers (Step 3)
const documentTypeConfigs = {
    aadhaar: {
        label: 'Aadhaar Card Number',
        placeholder: 'e.g. 5432 1234 5678',
        boxTitle: 'Upload Aadhaar Card Scan / Image',
        badge: 'UIDAI 12-Digit Verification'
    },
    pan_card: {
        label: 'PAN Card Number',
        placeholder: 'e.g. ABCDE1234F',
        boxTitle: 'Upload PAN Card Scan / Image',
        badge: 'Income Tax Dept Format'
    },
    passport: {
        label: 'Passport Number',
        placeholder: 'e.g. M1234567',
        boxTitle: 'Upload Passport Identification Page',
        badge: 'International Travel Document'
    },
    police_verification: {
        label: 'Verification Reference Number',
        placeholder: 'e.g. POL-VER-2026-894',
        boxTitle: 'Upload Police Verification Certificate',
        badge: 'Official Background Clearance'
    },
    voter_id: {
        label: 'Voter ID (EPIC) Number',
        placeholder: 'e.g. XYZ1234567',
        boxTitle: 'Upload Voter ID Card Scan',
        badge: 'Election Commission of India'
    },
    other: {
        label: 'Document Identification Number',
        placeholder: 'e.g. DOC-987654321',
        boxTitle: 'Upload Official Identification Document',
        badge: 'Government Certified Document'
    }
};

function onDocumentTypeChange(docType) {
    const cfg = documentTypeConfigs[docType] || documentTypeConfigs.other;
    const lblNumber = document.getElementById('lblDocNumber');
    const inputNumber = document.getElementById('id_number');
    const boxTitle = document.getElementById('docUploadBoxTitle');
    const badge = document.getElementById('docTypeBadge');

    if (lblNumber) lblNumber.textContent = cfg.label;
    if (inputNumber) inputNumber.placeholder = cfg.placeholder;
    if (boxTitle) boxTitle.textContent = cfg.boxTitle;
    if (badge) badge.textContent = cfg.badge;
}

function handleDocumentFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    idDocFileName = file.name;

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Document scan size exceeds 5MB limit', 'error');
        }
        input.value = '';
        return;
    }

    const sizeKb = (file.size / 1024).toFixed(1);
    const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

    idDocMeta = {
        name: file.name,
        size: sizeStr,
        type: file.type
    };

    const isImg = file.type.startsWith('image/') || /\.(png|jpe?g|webp|gif|bmp)$/i.test(file.name);
    const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
    idDocIsPdf = isPdf;

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const previewImg = document.getElementById('docPreviewImage');
    const pdfIcon = document.getElementById('docPreviewPdfIcon');
    const nameLabel = document.getElementById('docPreviewName');
    const sizeLabel = document.getElementById('docPreviewSize');

    if (nameLabel) nameLabel.textContent = file.name;
    if (sizeLabel) sizeLabel.textContent = sizeStr;

    // Show preview container immediately
    if (emptyState) emptyState.style.display = 'none';
    if (previewState) previewState.style.display = 'block';

    const reader = new FileReader();
    reader.onload = function(e) {
        idDocDataUrl = e.target.result;
        const b64 = document.getElementById('id_document_file_base64');
        const nameInput = document.getElementById('id_document_file_name');
        if (b64) b64.value = idDocDataUrl;
        if (nameInput) nameInput.value = idDocFileName;

        if (isImg) {
            if (previewImg) {
                previewImg.src = idDocDataUrl;
                previewImg.style.display = 'block';
            }
            if (pdfIcon) pdfIcon.style.display = 'none';
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
            if (pdfIcon) pdfIcon.style.display = 'flex';
        }
    };
    reader.readAsDataURL(file);
}

function clearDocumentUpload() {
    const input = document.getElementById('id_document_file');
    if (input) input.value = '';
    const b64 = document.getElementById('id_document_file_base64');
    const nameInput = document.getElementById('id_document_file_name');
    if (b64) b64.value = '';
    if (nameInput) nameInput.value = '';
    idDocDataUrl = null;
    idDocMeta = null;
    idDocIsPdf = false;
    idDocFileName = '';

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const previewImg = document.getElementById('docPreviewImage');
    if (previewImg) previewImg.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// Lightbox Preview Modal (Supports both 'license' and 'id' documents)
function openDocPreviewModal(type = 'id') {
    const modal = document.getElementById('docPreviewModal');
    const img = document.getElementById('docLightboxImg');
    const pdf = document.getElementById('docLightboxPdf');
    const pdfName = document.getElementById('docLightboxPdfName');
    const pdfOpenBtn = document.getElementById('docLightboxPdfOpenBtn');
    const downloadBtn = document.getElementById('docLightboxDownloadBtn');
    const title = document.getElementById('docLightboxTitle');
    const sub = document.getElementById('docLightboxSubtitle');

    const isLic = (type === 'license');
    const dataUrl = isLic ? licenseDocDataUrl : idDocDataUrl;
    const isPdf = isLic ? licenseDocIsPdf : idDocIsPdf;
    const fileName = isLic ? (licenseDocFileName || 'driving_license.pdf') : (idDocFileName || 'document.pdf');

    if (!dataUrl) return;

    if (title) title.textContent = isLic ? 'Commercial Driving Licence Scan' : 'Identity Verification Document';
    if (sub) sub.textContent = isLic ? 'Official Transport Authority Verification' : 'Attached Identity Certificate';

    if (downloadBtn) {
        downloadBtn.href = dataUrl;
        downloadBtn.download = fileName;
    }

    if (isPdf) {
        if (img) img.style.display = 'none';
        if (pdf) pdf.style.display = 'flex';
        if (pdfName) pdfName.textContent = fileName;
        if (pdfOpenBtn) pdfOpenBtn.href = dataUrl;
    } else {
        if (pdf) pdf.style.display = 'none';
        if (img) {
            img.src = dataUrl;
            img.style.display = 'block';
        }
    }

    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDocPreviewModal(e) {
    if (e && e.target && e.target.id !== 'docPreviewModal' && !e.target.closest('.doc-lightbox-close')) return;
    const modal = document.getElementById('docPreviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Credentials Auto-Generation & Username Suggestions (Step 4)
function syncUsernameSuggestion() {
    const fn = (document.getElementById('first_name')?.value || '').trim();
    const ln = (document.getElementById('last_name')?.value || '').trim();
    const uInput = document.getElementById('username');
    if (uInput && !uInput.dataset.manual) {
        let slug = (fn + '.' + ln).toLowerCase().replace(/[^a-z0-9.]+/g, '').replace(/^\.+|\.+$/g, '');
        if (slug) uInput.value = slug;
    }
}

function generateCredentials() {
    const fn = (document.getElementById('first_name')?.value || 'staff').toLowerCase().replace(/[^a-z0-9]/g, '');
    const randNum = Math.floor(100 + Math.random() * 900);
    const uInput = document.getElementById('username');
    const pInput = document.getElementById('password');
    const pConf = document.getElementById('password_confirmation');

    if (uInput) {
        uInput.value = `${fn}.${randNum}`;
        uInput.dataset.manual = 'true';
    }

    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#%';
    let generatedPass = 'Manaswi@';
    for (let i = 0; i < 4; i++) {
        generatedPass += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    if (pInput) {
        pInput.value = generatedPass;
        pInput.type = 'text';
    }
    if (pConf) {
        pConf.value = generatedPass;
        pConf.type = 'text';
    }

    if (typeof window.vgNotify === 'function') {
        window.vgNotify('Generated secure password: ' + generatedPass, 'info');
    }
}

function toggleAccountFields(isChecked) {
    const box = document.getElementById('accountFieldsContainer');
    if (box) box.style.display = isChecked ? 'block' : 'none';
}

// Live Summary Dossier Population (Step 5) with Rich Previews for BOTH Documents
function populateSummaryDossier() {
    const fn = document.getElementById('first_name')?.value?.trim() || '';
    const ln = document.getElementById('last_name')?.value?.trim() || '';
    const fullName = `${fn} ${ln}`.trim() || '—';

    // Hero
    const heroName = document.getElementById('sumHeroName');
    if (heroName) heroName.textContent = fullName;

    const initial = fn ? fn.charAt(0).toUpperCase() : 'E';
    const sumInitial = document.getElementById('sumAvatarInitial');
    if (sumInitial) sumInitial.textContent = initial;

    const heroImg = document.getElementById('sumAvatarImg');
    const heroMono = document.getElementById('sumAvatarMonogram');
    if (avatarDataUrl && heroImg && heroMono) {
        heroImg.src = avatarDataUrl;
        heroImg.style.display = 'block';
        heroMono.style.display = 'none';
    } else if (heroImg && heroMono) {
        heroImg.style.display = 'none';
        heroMono.style.display = 'flex';
    }

    const roleSelect = document.getElementById('role');
    const roleOpt = roleSelect ? roleSelect.options[roleSelect.selectedIndex] : null;
    const roleText = roleOpt ? (roleOpt.getAttribute('data-name') || roleOpt.text) : 'Driver / Chauffeur';
    const tagRole = document.getElementById('sumTagRoleText');
    if (tagRole) tagRole.textContent = roleText;

    const contractSelect = document.getElementById('employment_type');
    const contractText = contractSelect ? contractSelect.options[contractSelect.selectedIndex].text : 'Full-Time';
    const tagContract = document.getElementById('sumTagContractText');
    if (tagContract) tagContract.textContent = contractText;

    // Card 1
    const cardName = document.getElementById('sumCardName');
    if (cardName) cardName.textContent = fullName;

    const dobVal = document.getElementById('date_of_birth')?.value;
    const cardDob = document.getElementById('sumCardDob');
    if (cardDob) cardDob.textContent = dobVal || 'Not Specified';

    const genderVal = document.getElementById('gender')?.value;
    const cardGender = document.getElementById('sumCardGender');
    if (cardGender) cardGender.textContent = genderVal || 'Not Specified';

    const natVal = document.getElementById('nationality')?.value || 'Indian';
    const cardNat = document.getElementById('sumCardNationality');
    if (cardNat) cardNat.textContent = natVal;

    // Card 2
    const phoneVal = document.getElementById('phone')?.value || '—';
    const cardPhone = document.getElementById('sumCardPhone');
    if (cardPhone) cardPhone.textContent = phoneVal;

    const emailVal = document.getElementById('email')?.value || '—';
    const cardEmail = document.getElementById('sumCardEmail');
    if (cardEmail) cardEmail.textContent = emailVal;

    const altPhoneVal = document.getElementById('alternate_phone')?.value || '—';
    const cardAltPhone = document.getElementById('sumCardAltPhone');
    if (cardAltPhone) cardAltPhone.textContent = altPhoneVal;

    const addrVal = document.getElementById('address_line')?.value || '—';
    const cardAddr = document.getElementById('sumCardAddress');
    if (cardAddr) cardAddr.textContent = addrVal;

    const city = document.getElementById('city')?.value || '';
    const state = document.getElementById('state')?.value || '';
    const pin = document.getElementById('postal_code')?.value || '';
    const country = document.getElementById('country')?.value || 'India';
    const cardCityStatePin = document.getElementById('sumCardCityStatePin');
    if (cardCityStatePin) cardCityStatePin.textContent = `${city}, ${state} ${pin} (${country})`.replace(/^, /, '').trim() || '—';

    // Card 3
    const cardRole = document.getElementById('sumCardRole');
    if (cardRole) cardRole.textContent = roleText;

    const cardContract = document.getElementById('sumCardContract');
    if (cardContract) cardContract.textContent = contractText;

    const joiningVal = document.getElementById('joining_date')?.value || '—';
    const cardJoining = document.getElementById('sumCardJoiningDate');
    if (cardJoining) cardJoining.textContent = joiningVal;

    const emergName = document.getElementById('emergency_contact_name')?.value || '—';
    const cardEmergContact = document.getElementById('sumCardEmergContact');
    if (cardEmergContact) cardEmergContact.textContent = emergName;

    const emergPhone = document.getElementById('emergency_contact_phone')?.value || '—';
    const cardEmergPhone = document.getElementById('sumCardEmergPhone');
    if (cardEmergPhone) cardEmergPhone.textContent = emergPhone;

    // Card 4: Compliance Details & Rich Document Cards
    const licNo = document.getElementById('license_number')?.value?.toUpperCase() || '—';
    const cardLicNo = document.getElementById('sumCardLicenseNo');
    if (cardLicNo) cardLicNo.textContent = licNo;

    const licExp = document.getElementById('license_expiry')?.value || '—';
    const cardLicExp = document.getElementById('sumCardLicenseExpiry');
    if (cardLicExp) cardLicExp.textContent = licExp;

    const expYrs = document.getElementById('experience_years')?.value || '5';
    const cardExp = document.getElementById('sumCardExperience');
    if (cardExp) cardExp.textContent = `${expYrs} Years Commercial`;

    // 1. Rich Preview Card for Driving License Document
    const cardLicDoc = document.getElementById('sumCardLicenseDoc');
    if (cardLicDoc) {
        if (licenseDocDataUrl || (licenseDocMeta && licenseDocMeta.name)) {
            const isImg = !licenseDocIsPdf;
            const licFileName = (licenseDocMeta && licenseDocMeta.name) ? licenseDocMeta.name : (licenseDocFileName || 'driving_license.jpg');
            const licSize = (licenseDocMeta && licenseDocMeta.size) ? licenseDocMeta.size : 'Document scan';
            cardLicDoc.innerHTML = `
                <div class="step4-doc-preview-card">
                    <div class="step4-doc-thumb-wrap" onclick="openDocPreviewModal('license')" title="Click to view full preview">
                        ${isImg && licenseDocDataUrl
                            ? `<img src="${licenseDocDataUrl}" alt="Driving Licence scan" class="step4-doc-thumb-img">`
                            : `<div class="step4-doc-pdf-icon"><i class="fa-solid fa-file-pdf"></i></div>`
                        }
                        <div class="step4-doc-thumb-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                    </div>
                    <div class="step4-doc-details">
                        <div class="step4-doc-filename" title="${licFileName}">${licFileName}</div>
                        <div class="step4-doc-sub">${licSize} • <span style="color: #059669; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> Driving Licence Attached &amp; Ready</span></div>
                        <button type="button" class="btn-step4-preview" onclick="openDocPreviewModal('license')">
                            <i class="fa-solid fa-eye"></i> Preview Licence
                        </button>
                    </div>
                </div>
            `;
        } else {
            cardLicDoc.innerHTML = `
                <span class="review-doc-badge empty">
                    <i class="fa-solid fa-circle-info"></i> Not attached (Optional)
                </span>
            `;
        }
    }

    const docTypeSelect = document.getElementById('id_type');
    const docTypeText = docTypeSelect ? docTypeSelect.options[docTypeSelect.selectedIndex].text : 'Aadhaar Card';
    const cardDocType = document.getElementById('sumCardDocType');
    if (cardDocType) cardDocType.textContent = docTypeText;

    const docNo = document.getElementById('id_number')?.value || '—';
    const cardDocNo = document.getElementById('sumCardDocNumber');
    if (cardDocNo) cardDocNo.textContent = docNo;

    // 2. Rich Preview Card for Government ID Document
    const cardIdDoc = document.getElementById('sumCardIdDoc');
    if (cardIdDoc) {
        if (idDocDataUrl || (idDocMeta && idDocMeta.name)) {
            const isImg = !idDocIsPdf;
            const docName = (idDocMeta && idDocMeta.name) ? idDocMeta.name : (idDocFileName || 'document.jpg');
            const docSize = (idDocMeta && idDocMeta.size) ? idDocMeta.size : 'Document scan';
            cardIdDoc.innerHTML = `
                <div class="step4-doc-preview-card">
                    <div class="step4-doc-thumb-wrap" onclick="openDocPreviewModal('id')" title="Click to view full preview">
                        ${isImg && idDocDataUrl
                            ? `<img src="${idDocDataUrl}" alt="Identity scan" class="step4-doc-thumb-img">`
                            : `<div class="step4-doc-pdf-icon"><i class="fa-solid fa-file-pdf"></i></div>`
                        }
                        <div class="step4-doc-thumb-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                    </div>
                    <div class="step4-doc-details">
                        <div class="step4-doc-filename" title="${docName}">${docName}</div>
                        <div class="step4-doc-sub">${docSize} • <span style="color: #059669; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> ${docTypeText} Attached &amp; Ready</span></div>
                        <button type="button" class="btn-step4-preview" onclick="openDocPreviewModal('id')">
                            <i class="fa-solid fa-eye"></i> Preview Document
                        </button>
                    </div>
                </div>
            `;
        } else {
            cardIdDoc.innerHTML = `
                <span class="review-doc-badge empty">
                    <i class="fa-solid fa-circle-info"></i> Not attached (Optional)
                </span>
            `;
        }
    }

    const notesVal = document.getElementById('notes')?.value || '—';
    const cardNotes = document.getElementById('sumCardNotes');
    if (cardNotes) cardNotes.textContent = notesVal;
}

// Drag & Drop event registration and initializers
document.addEventListener('DOMContentLoaded', function() {
    wizardGoToStep(1);

    // 1. Profile Photo drag & drop
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

    // 2. Driving License Dropzone drag & drop
    const licDropzone = document.getElementById('licenseDropzone');
    if (licDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            licDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                licDropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            licDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                licDropzone.classList.remove('is-dragover');
            });
        });
        licDropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                const input = document.getElementById('license_document_file');
                if (input) {
                    input.files = files;
                    handleLicenseFileChange(input);
                }
            }
        });
    }

    // 3. ID Document Dropzone drag & drop
    const idDropzone = document.getElementById('documentDropzone');
    if (idDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            idDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                idDropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            idDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                idDropzone.classList.remove('is-dragover');
            });
        });
        idDropzone.addEventListener('drop', (e) => {
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

    // Form submission UI spinner feedback
    const empForm = document.getElementById('createEmployeeForm');
    const submitBtn = document.getElementById('btnSubmitEmployee');
    if (empForm && submitBtn) {
        empForm.addEventListener('submit', function() {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.85';
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Registering Staff Profile...</span>';
        });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDocPreviewModal();
        }
    });
});
</script>

<style>
/* ========================================================================= */
/* Complete Luxury Modern Form Styling (Exact Customers Standard)            */
/* ========================================================================= */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem;
}

/* Top Utility Bar */
.customer-create-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    padding: 0 0.5rem;
}
.create-topbar-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.create-topbar-back-btn:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
    transform: translateX(-2px);
}
.create-topbar-admin-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
}
.create-topbar-admin-btn:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: #FFFFFF;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

/* Centered Stepper Header */
.modern-stepper-header-centered {
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    align-items: center;
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

@media (max-width: 768px) {
    .modern-form-wrapper {
        padding: 0 0 2rem;
    }
    .modern-stepper-header-centered {
        margin-bottom: 1.5rem;
    }
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

/* Form Card */
.modern-form-card {
    background: #FFFFFF;
    border-radius: 24px;
    padding: 2.25rem;
    border: 1.5px solid #E2E8F0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}
.modern-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    border-bottom: 1.5px solid #F1F5F9;
}
.modern-section-badge {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #E8F4F0 0%, #D4ECE4 100%);
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.12);
}
.modern-section-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0 0 2px 0;
    letter-spacing: -0.01em;
}
.modern-section-sub {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0;
}

/* Avatar Upload Card */
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
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.customer-avatar-preview-wrap:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.2);
}
.avatar-default-icon {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #176B5B;
    font-size: 2.2rem;
}
.avatar-camera-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    border: 2px solid #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
}
.customer-avatar-meta {
    flex: 1;
}
.customer-avatar-title-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 0.25rem;
}
.customer-avatar-label {
    font-size: 0.98rem;
    font-weight: 700;
    color: #0F172A;
}
.modern-badge-subtle {
    background: #E2E8F0;
    color: #475569;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 50px;
}
.customer-avatar-help {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0 0 0.75rem 0;
    line-height: 1.45;
}
.customer-avatar-actions {
    display: flex;
    gap: 0.65rem;
}
.customer-avatar-btn-upload, .customer-avatar-btn-remove {
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.customer-avatar-btn-upload {
    background: #176B5B;
    color: #FFFFFF;
}
.customer-avatar-btn-upload:hover {
    background: #16443B;
}
.customer-avatar-btn-remove {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}
.customer-avatar-btn-remove:hover {
    background: #FCA5A5;
}

/* Grids & Inputs */
.customer-name-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}
.modern-form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
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
    color: #1E293B;
    margin-bottom: 0.45rem;
    display: block;
}
.modern-form-label .req {
    color: #EF4444;
    margin-left: 2px;
}
.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.modern-input-icon {
    position: absolute;
    left: 14px;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
}
.modern-input-pill, .modern-select-pill {
    width: 100%;
    height: 46px;
    border-radius: 12px;
    border: 1.5px solid #CBD5E1;
    background: #FFFFFF;
    color: #0F172A;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 0 14px 0 42px;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.modern-input-pill:focus, .modern-select-pill:focus {
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15);
}
.modern-select-pill {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748B'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 16px;
    padding-right: 36px;
    cursor: pointer;
}
.modern-field-hint {
    font-size: 0.76rem;
    color: #64748B;
    margin-top: 0.35rem;
}
.modern-field-error {
    font-size: 0.78rem;
    color: #DC2626;
    margin-top: 0.35rem;
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
}

/* Document Dropzone */
.document-dropzone {
    border: 2px dashed #CBD5E1;
    border-radius: 16px;
    background: #F8FAFC;
    padding: 1.75rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.22s ease;
}
.document-dropzone:hover, .document-dropzone.is-dragover {
    border-color: #176B5B;
    background: #F0FDF8;
}
.document-dropzone-halo {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin: 0 auto 0.75rem;
}
.document-dropzone-prompt {
    font-size: 0.95rem;
    color: #1E293B;
    margin-bottom: 0.3rem;
}
.document-dropzone-prompt strong {
    color: #176B5B;
}
.document-dropzone-sub {
    font-size: 0.8rem;
    color: #64748B;
}

/* Dropzone Preview State */
.doc-preview-wrapper {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    text-align: left;
}
.doc-preview-thumb-box {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}
.doc-thumb-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.doc-preview-thumb-box:hover .doc-thumb-hover-overlay {
    opacity: 1;
}
.doc-preview-meta {
    flex: 1;
}
.doc-preview-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    margin-bottom: 2px;
    word-break: break-all;
}
.doc-preview-size {
    font-size: 0.78rem;
    color: #64748B;
    margin-bottom: 4px;
}
.doc-preview-status {
    font-size: 0.78rem;
    font-weight: 700;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 5px;
}
.doc-preview-actions {
    display: flex;
    gap: 0.5rem;
}
.btn-doc-view-preview {
    background: #176B5B;
    color: #FFFFFF;
    font-weight: 700;
    border-radius: 8px;
    padding: 0.35rem 0.85rem;
    border: none;
    cursor: pointer;
}
.btn-doc-view-preview:hover {
    background: #16443B;
}

/* Actions Bar */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 2.25rem;
    padding-top: 1.5rem;
    border-top: 1.5px solid #F1F5F9;
}
.modern-btn-next, .modern-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    border: none;
    padding: 12px 28px;
    border-radius: 9999px;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.28);
    transition: all 0.2s ease;
}
.modern-btn-next:hover, .modern-btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.38);
}
.modern-btn-prev, .modern-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 12px 24px;
    border-radius: 9999px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}
.modern-btn-prev:hover, .modern-btn-cancel:hover {
    background: #F8FAFC;
    color: #1E293B;
    border-color: #94A3B8;
}

/* Review Dossier (Step 5) */
.review-dossier-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}
.review-hero-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #F8FAFD 0%, #F1F6FA 100%);
    border: 1.5px solid #DCE6F2;
    border-radius: 20px;
    padding: 1.5rem;
}
.review-avatar-container {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    border: 3px solid #176B5B;
    background: #E8F4F0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.15);
}
.review-avatar-monogram {
    font-size: 2.2rem;
    font-weight: 900;
    color: #176B5B;
}
.review-hero-title-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 0.4rem;
    flex-wrap: wrap;
}
.review-hero-name {
    font-size: 1.4rem;
    font-weight: 900;
    color: #0F172A;
    margin: 0;
}
.review-code-badge {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.82rem;
    color: #475569;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.review-hero-tags {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.review-tag-item {
    font-size: 0.82rem;
    font-weight: 700;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.review-status-pill {
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.review-status-pill.is-active {
    background: #DCFCE7;
    color: #15803D;
}
.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}
.review-section-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
}
.review-card-header {
    background: #F8FAFC;
    padding: 0.9rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 800;
    color: #1E293B;
    border-bottom: 1.5px solid #E2E8F0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.review-card-header i {
    color: #176B5B;
}
.review-card-rows {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
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
}
.review-value {
    color: #0F172A;
    font-weight: 700;
    text-align: right;
}
.review-value.font-bold {
    font-weight: 800;
}
.review-value.highlight-teal {
    color: #176B5B;
}
.review-value.capitalize {
    text-transform: capitalize;
}
.review-value.font-mono {
    font-family: monospace;
}
.review-doc-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
}
.review-doc-badge.attached {
    background: #DCFCE7;
    color: #15803D;
    border: 1px solid #BBF7D0;
}
.review-doc-badge.empty {
    background: #F1F5F9;
    color: #94A3B8;
}

/* Rich Document Preview Cards in Step 5 (Exact Match to Customers Architecture) */
.step4-doc-preview-card {
    display: flex;
    align-items: center;
    gap: 1.1rem;
    background: #F8FAFD;
    border: 1.5px solid #B2D8CF;
    border-radius: 12px;
    padding: 0.85rem 1.15rem;
    margin-top: 0.35rem;
    box-shadow: 0 3px 12px rgba(23, 107, 91, 0.06);
    text-align: left;
    transition: all 0.2s ease;
}
.step4-doc-preview-card:hover {
    border-color: #176B5B;
    box-shadow: 0 5px 16px rgba(23, 107, 91, 0.12);
}
.step4-doc-thumb-wrap {
    width: 62px;
    height: 62px;
    border-radius: 10px;
    border: 1.5px solid #CBD5E1;
    overflow: hidden;
    background: #FFFFFF;
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.step4-doc-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.25s ease;
}
.step4-doc-thumb-wrap:hover .step4-doc-thumb-img {
    transform: scale(1.08);
}
.step4-doc-pdf-icon {
    font-size: 2.1rem;
    color: #EF4444;
}
.step4-doc-thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(12, 43, 82, 0.5);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    opacity: 0;
    transition: opacity 0.2s ease;
    border-radius: 8px;
}
.step4-doc-thumb-wrap:hover .step4-doc-thumb-overlay {
    opacity: 1;
}
.step4-doc-details {
    flex: 1;
    min-width: 0;
}
.step4-doc-filename {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0C2B52;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.step4-doc-sub {
    font-size: 0.78rem;
    color: #64748B;
    margin: 2px 0 6px 0;
}
.btn-step4-preview {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}
.btn-step4-preview:hover {
    background: #176B5B;
    color: #FFFFFF;
}

/* Lightbox Modal */
.doc-lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(11, 25, 44, 0.75);
    backdrop-filter: blur(4px);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.doc-lightbox-container {
    background: #FFFFFF;
    border-radius: 20px;
    width: 100%;
    max-width: 800px;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.doc-lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
}
.doc-lightbox-title-box {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.doc-lightbox-icon {
    font-size: 1.5rem;
    color: #A7F3D0;
}
.doc-lightbox-title {
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0;
}
.doc-lightbox-subtitle {
    font-size: 0.78rem;
    color: #A7F3D0;
    margin: 0;
}
.doc-lightbox-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.doc-lightbox-btn-action, .doc-lightbox-close {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #FFFFFF;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.18s ease;
}
.doc-lightbox-btn-action:hover, .doc-lightbox-close:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #FFFFFF;
}
.doc-lightbox-body {
    padding: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F8FAFC;
    min-height: 250px;
}

/* Mobile Responsive */
@media (max-width: 900px) {
    .customer-name-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 640px) {
    .modern-form-card {
        padding: 1.25rem;
        border-radius: 18px;
    }
    .customer-avatar-upload-card {
        flex-direction: column;
        text-align: center;
    }
    .customer-avatar-actions {
        justify-content: center;
    }
    .customer-name-row, .modern-form-grid-3, .modern-form-grid-2 {
        grid-template-columns: 1fr;
    }
    .modern-col-span-2 {
        grid-column: span 1;
    }
    .modern-stepper-track {
        overflow-x: auto;
    }
    .modern-step-label {
        font-size: 0.72rem;
    }
    .review-hero-banner {
        flex-direction: column;
        text-align: center;
    }
    .review-hero-title-row {
        justify-content: center;
    }
    .review-hero-tags {
        justify-content: center;
    }
    .doc-preview-wrapper {
        flex-direction: column;
        text-align: center;
    }
    .doc-preview-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>
