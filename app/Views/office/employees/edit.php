<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit Employee / Staff Profile
 * Complete Modern Form UI Matching office/employees/create with Full Profile Photo & Documents Upload
 */

$empFullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
$initials = strtoupper(mb_substr($employee['first_name'] ?? 'E', 0, 1) . mb_substr($employee['last_name'] ?? '', 0, 1));
$empCode = $employee['employee_code'] ?? ('VG-EMP-' . str_pad((string)$employee['id'], 4, '0', STR_PAD_LEFT));

$hasExistingAvatar = !empty($employee['profile_photo']);
$existingAvatarUrl = $hasExistingAvatar ? customer_avatar_url($employee['profile_photo']) : '';

$hasExistingLicenseDoc = !empty($employee['license_document_file']) || !empty($employee['driver_license_doc']);
$existingLicenseDocFile = $employee['license_document_file'] ?? ($employee['driver_license_doc'] ?? '');
$existingLicenseDocUrl = $hasExistingLicenseDoc ? customer_document_url($existingLicenseDocFile) : '';
$existingLicenseDocName = $hasExistingLicenseDoc ? basename($existingLicenseDocFile) : '';
$isExistingLicensePdf = $hasExistingLicenseDoc && str_ends_with(strtolower($existingLicenseDocFile), '.pdf');

$hasExistingIdDoc = !empty($employee['id_document_file']);
$existingIdDocUrl = $hasExistingIdDoc ? customer_document_url($employee['id_document_file']) : '';
$existingIdDocName = $hasExistingIdDoc ? basename($employee['id_document_file']) : '';
$isExistingIdPdf = $hasExistingIdDoc && str_ends_with(strtolower($employee['id_document_file']), '.pdf');

$currentDocType = $old['id_type'] ?? $employee['id_type'] ?? 'aadhaar';
if (empty($currentDocType)) {
    $currentDocType = 'aadhaar';
}

$currentRole = $old['role'] ?? ($employee['linked_role'] ?? ($employee['department'] === 'fleet_chauffeur' ? 'driver' : 'operations'));
$currentStatus = $old['status'] ?? $employee['status'] ?? 'active';
$currentDept = $old['department'] ?? $employee['department'] ?? 'fleet_chauffeur';
$currentEmpType = $old['employment_type'] ?? $employee['employment_type'] ?? 'full_time';
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="modern-back-btn" title="Back to Employee Profile" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('emp.edit_title', ['name' => $empFullName], 'Edit Staff Profile')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('emp.edit_subtitle', [], 'Update verified staff credentials, departmental role, license compliance, and documents.')) ?></p>
            </div>
        </div>

        <div class="modern-header-right" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span class="customer-code-badge-header">
                <i class="fa-solid fa-id-badge"></i> <?= e($empCode) ?>
            </span>
            <span class="status-pill-header <?= $currentStatus === 'active' ? 'status-active' : ($currentStatus === 'on_leave' ? 'status-leave' : 'status-terminated') ?>">
                <i class="fa-solid fa-circle-dot"></i> <?= ucfirst(e($currentStatus)) ?> Staff
            </span>
            <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="btn-view-profile-header">
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
    <form action="<?= url('/office/employees/' . $employee['id']) ?>" method="POST" id="editEmployeeForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <!-- Hidden Tracking Inputs for Removals & Base64 Payload Fallbacks -->
        <input type="hidden" name="remove_profile_photo" id="remove_profile_photo" value="0">
        <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">

        <input type="hidden" name="remove_license_document" id="remove_license_document" value="0">
        <input type="hidden" name="license_document_file_base64" id="license_document_file_base64" value="">
        <input type="hidden" name="license_document_file_name" id="license_document_file_name" value="">

        <input type="hidden" name="remove_id_document" id="remove_id_document" value="0">
        <input type="hidden" name="id_document_file_base64" id="id_document_file_base64" value="">
        <input type="hidden" name="id_document_file_name" id="id_document_file_name" value="">

        <div class="modern-form-card" style="margin-bottom: 2rem;">

            <!-- ========================================================================= -->
            <!-- 1. Staff Profile Photo Upload Component (Matching create.php)             -->
            <!-- ========================================================================= -->
            <div class="customer-avatar-upload-card">
                <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('profile_photo').click()" title="Click to upload staff photo">
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleProfilePhotoChange(this)" onclick="event.stopPropagation()">
                    
                    <div class="avatar-default-icon" id="avatarDefaultIcon" style="<?= $hasExistingAvatar ? 'display: none;' : 'display: flex;' ?>">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    
                    <img id="avatarPreviewImg" src="<?= $hasExistingAvatar ? e($existingAvatarUrl) : '' ?>" alt="Profile Photo" style="<?= $hasExistingAvatar ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    
                    <div class="avatar-camera-badge" title="Upload Photo">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <div class="customer-avatar-meta">
                    <div class="customer-avatar-title-row">
                        <span class="customer-avatar-label">Staff Profile Photo</span>
                        <?php if ($hasExistingAvatar): ?>
                            <span class="modern-badge-subtle badge-success-subtle"><i class="fa-solid fa-circle-check"></i> Photo Saved</span>
                        <?php else: ?>
                            <span class="modern-badge-subtle">Optional</span>
                        <?php endif; ?>
                    </div>
                    <p class="customer-avatar-help">Upload a clean front-facing corporate headshot or portrait. Supported: JPG, PNG, WEBP (Max 3MB).</p>
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
            <!-- 2. Personal & Contact Identity Section                                    -->
            <!-- ========================================================================= -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Personal &amp; Contact Details</h2>
                    <p class="modern-section-sub">Staff legal full name, communication numbers, corporate email, and emergency contact details.</p>
                </div>
            </div>

            <div class="modern-form-grid-4">
                <!-- First Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="first_name">
                        First Name <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="first_name" name="first_name" class="modern-input-pill" required placeholder="e.g. Vikramaditya" value="<?= e($old['first_name'] ?? $employee['first_name'] ?? '') ?>" autocomplete="given-name">
                    </div>
                    <?php if (!empty($errors['first_name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['first_name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="last_name">
                        Last Name <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-tag"></i></span>
                        <input type="text" id="last_name" name="last_name" class="modern-input-pill" required placeholder="e.g. Rathore" value="<?= e($old['last_name'] ?? $employee['last_name'] ?? '') ?>" autocomplete="family-name">
                    </div>
                    <?php if (!empty($errors['last_name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['last_name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Primary Mobile Number -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone">
                        Primary Mobile <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" id="phone" name="phone" class="modern-input-pill" required placeholder="e.g. +91 9876543210" value="<?= e($old['phone'] ?? $employee['phone'] ?? '') ?>" autocomplete="tel">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Corporate Email Address -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email">
                        Corporate Email
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill" placeholder="e.g. v.rathore@manaswitours.com" value="<?= e($old['email'] ?? $employee['email'] ?? '') ?>" autocomplete="email">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
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
                        <input type="date" id="date_of_birth" name="date_of_birth" class="modern-input-pill" value="<?= e($old['date_of_birth'] ?? $employee['date_of_birth'] ?? '') ?>">
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
                            <?php $selectedGender = $old['gender'] ?? $employee['gender'] ?? ''; ?>
                            <option value="" <?= empty($selectedGender) ? 'selected' : '' ?>>Select Gender</option>
                            <option value="male" <?= $selectedGender === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $selectedGender === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $selectedGender === 'other' ? 'selected' : '' ?>>Other</option>
                            <option value="prefer_not_to_say" <?= $selectedGender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                        </select>
                    </div>
                </div>

                <!-- Nationality -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="nationality">
                        Nationality / Citizenship
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-globe"></i></span>
                        <input type="text" id="nationality" name="nationality" class="modern-input-pill" placeholder="e.g. Indian" value="<?= e($old['nationality'] ?? $employee['nationality'] ?? 'Indian') ?>">
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Alternate Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="alternate_phone">
                        Alternate Phone
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" placeholder="e.g. +91 9123456789" value="<?= e($old['alternate_phone'] ?? $employee['alternate_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Emergency Contact Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="emergency_contact_name">
                        Emergency Contact Name
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="modern-input-pill" placeholder="e.g. Rajesh Rathore (Brother)" value="<?= e($old['emergency_contact_name'] ?? $employee['emergency_contact_name'] ?? $employee['emergency_contact'] ?? '') ?>">
                    </div>
                </div>

                <!-- Emergency Contact Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="emergency_contact_phone">
                        Emergency Phone
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-flip"></i></span>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="modern-input-pill" placeholder="e.g. +91 9988776655" value="<?= e($old['emergency_contact_phone'] ?? $employee['emergency_contact_phone'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 3. Department & Employment Role Section                                   -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Department &amp; Employment Details</h2>
                    <p class="modern-section-sub">Operational division, designation, employment contract, joining date, and linked system credentials.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Role Selector -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="role">
                        Role <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                        <select id="role" name="role" class="modern-select-pill" onchange="onRoleChanged(this.value)">
                            <option value="driver" <?= $currentRole === 'driver' ? 'selected' : '' ?>>🚗 Driver / Chauffeur</option>
                            <option value="dispatcher" <?= $currentRole === 'dispatcher' ? 'selected' : '' ?>>Dispatch Controller</option>
                            <option value="manager" <?= $currentRole === 'manager' ? 'selected' : '' ?>>Operations Manager</option>
                            <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>System Administrator</option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $r): ?>
                                    <?php if (in_array(strtolower($r['slug'] ?? ''), ['driver', 'chauffeur', 'dispatcher', 'manager', 'admin'])) continue; ?>
                                    <option value="<?= e($r['slug']) ?>" <?= $currentRole === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Department -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="department">
                        Department <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-building"></i></span>
                        <select id="department" name="department" class="modern-select-pill" required>
                            <option value="fleet_chauffeur" <?= $currentDept === 'fleet_chauffeur' ? 'selected' : '' ?>>Fleet Operations &amp; Chauffeurs</option>
                            <option value="dispatch" <?= $currentDept === 'dispatch' ? 'selected' : '' ?>>Dispatch Control Center</option>
                            <option value="operations" <?= $currentDept === 'operations' ? 'selected' : '' ?>>Tours &amp; Operations</option>
                            <option value="management" <?= $currentDept === 'management' ? 'selected' : '' ?>>Executive Management</option>
                            <option value="administration" <?= $currentDept === 'administration' ? 'selected' : '' ?>>HR &amp; Administration</option>
                            <option value="maintenance" <?= $currentDept === 'maintenance' ? 'selected' : '' ?>>Fleet Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Designation -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="designation">
                        Designation / Title <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-card-clip"></i></span>
                        <input type="text" id="designation" name="designation" class="modern-input-pill" required value="<?= e($old['designation'] ?? $employee['designation'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Employment Contract Type -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="employment_type">
                        Employment Contract Type <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-file-contract"></i></span>
                        <select id="employment_type" name="employment_type" class="modern-select-pill" required>
                            <option value="full_time" <?= $currentEmpType === 'full_time' ? 'selected' : '' ?>>Full-Time Regular</option>
                            <option value="part_time" <?= $currentEmpType === 'part_time' ? 'selected' : '' ?>>Part-Time Staff</option>
                            <option value="contract" <?= $currentEmpType === 'contract' ? 'selected' : '' ?>>Commercial Contract</option>
                            <option value="probation" <?= $currentEmpType === 'probation' ? 'selected' : '' ?>>Probationary Period</option>
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
                        <input type="date" id="joining_date" name="joining_date" class="modern-input-pill" required value="<?= e($old['joining_date'] ?? $employee['joining_date'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <!-- Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status">
                        Roster Status <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <select id="status" name="status" class="modern-select-pill" required>
                            <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active on Roster</option>
                            <option value="on_leave" <?= $currentStatus === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="terminated" <?= $currentStatus === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Linked System Login Account Row -->
            <div class="modern-form-group" style="margin-bottom: 0.5rem;">
                <label class="modern-form-label" for="user_id">
                    Linked System Login Account
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-user-lock"></i></span>
                    <select id="user_id" name="user_id" class="modern-select-pill">
                        <option value="">No Linked System Account (Staff Directory Record Only)</option>
                        <?php if (!empty($employee['user_id'])): ?>
                            <option value="<?= (int)$employee['user_id'] ?>" selected>
                                Currently Linked: <?= e($employee['linked_username'] ?? 'User #' . $employee['user_id']) ?> &bull; Role: <?= ucfirst(e($employee['linked_role'] ?? 'staff')) ?> (Active)
                            </option>
                        <?php endif; ?>
                        <?php if (!empty($unlinkedUsers)): ?>
                            <?php foreach ($unlinkedUsers as $u): ?>
                                <?php if (!empty($employee['user_id']) && (int)$employee['user_id'] === (int)$u['id']) continue; ?>
                                <option value="<?= $u['id'] ?>" <?= (!empty($old['user_id']) && (int)$old['user_id'] === (int)$u['id']) ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?> (@<?= e($u['username']) ?> &bull; Role: <?= ucfirst(e($u['role'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <small class="modern-field-hint">Connect an existing system authentication account to give this staff access to the Dispatcher or Driver Mobile portal.</small>
            </div>

            <!-- ========================================================================= -->
            <!-- 4. Residential & Official Address Section                                  -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Residential &amp; Official Address</h2>
                    <p class="modern-section-sub">Physical address for official payroll documentation, dispatch routing, and emergency services.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Address Line -->
                <div class="modern-form-group modern-col-span-2">
                    <label class="modern-form-label" for="address_line">
                        Street Address / Landmark
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" id="address_line" name="address_line" class="modern-input-pill" placeholder="Street address, apartment, suite..." value="<?= e($old['address_line'] ?? $employee['address_line'] ?? $employee['address'] ?? '') ?>">
                    </div>
                </div>

                <!-- City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="city">
                        City
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                        <input type="text" id="city" name="city" class="modern-input-pill" placeholder="e.g. Mumbai" value="<?= e($old['city'] ?? $employee['city'] ?? '') ?>">
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
                        <input type="text" id="state" name="state" class="modern-input-pill" placeholder="e.g. Maharashtra" value="<?= e($old['state'] ?? $employee['state'] ?? '') ?>">
                    </div>
                </div>

                <!-- Country -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="country">
                        Country
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-earth-americas"></i></span>
                        <input type="text" id="country" name="country" class="modern-input-pill" placeholder="e.g. India" value="<?= e($old['country'] ?? $employee['country'] ?? 'India') ?>">
                    </div>
                </div>

                <!-- Postal Code -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="postal_code">
                        Postal Code
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="postal_code" name="postal_code" class="modern-input-pill" placeholder="e.g. 400001" value="<?= e($old['postal_code'] ?? $employee['postal_code'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 5. Commercial Driving License Verification Section                        -->
            <!-- ========================================================================= -->
            <div id="chauffeurLicenseSection" style="background: #F0FDF8; border: 1.5px solid #A7F3D0; padding: 1.5rem; border-radius: 18px; margin-top: 1.75rem; margin-bottom: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-weight: 700; color: #16443B; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-id-card-clip" style="color: #176B5B;"></i> Commercial Driving License Verification
                    </span>
                    <span style="padding: 4px 12px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-size: 0.78rem; font-weight: 700; border: 1px solid #A7F3D0;">
                        Fleet Operational Requirement
                    </span>
                </div>

                <div class="modern-form-grid-3" style="margin-bottom: 1.25rem;">
                    <!-- Driving License Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="license_number">
                            Driving Licence Number
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                            <input type="text" id="license_number" name="license_number" class="modern-input-pill" value="<?= e($old['license_number'] ?? $employee['license_number'] ?? '') ?>" placeholder="e.g. DL-0420110098765" style="font-family: monospace; text-transform: uppercase;">
                        </div>
                    </div>

                    <!-- License Expiry Date -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="license_expiry">
                            License Expiry Date
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <input type="date" id="license_expiry" name="license_expiry" class="modern-input-pill" value="<?= e($old['license_expiry'] ?? $employee['license_expiry'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Experience Years -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="experience_years">
                            Experience (Years)
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-award"></i></span>
                            <input type="number" id="experience_years" name="experience_years" class="modern-input-pill" value="<?= e($old['experience_years'] ?? $employee['experience_years'] ?? '5') ?>" min="0" max="50">
                        </div>
                    </div>
                </div>

                <!-- Driving License Document Upload Dropzone (Matching create.php) -->
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

                        <!-- Empty State Inside Dropzone -->
                        <div id="licenseDropzoneEmpty" style="<?= $hasExistingLicenseDoc ? 'display: none;' : 'display: block;' ?>">
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
                        <div id="licenseDropzonePreview" style="<?= $hasExistingLicenseDoc ? 'display: block;' : 'display: none;' ?>">
                            <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                                <div class="doc-preview-thumb-box" onclick="openDocPreviewModal('license')" style="cursor: pointer;" title="Click to view full preview">
                                    <img id="licensePreviewImage" src="<?= ($hasExistingLicenseDoc && !$isExistingLicensePdf) ? e($existingLicenseDocUrl) : '' ?>" alt="License preview" style="<?= ($hasExistingLicenseDoc && !$isExistingLicensePdf) ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    <div id="licensePreviewPdfIcon" style="<?= ($hasExistingLicenseDoc && $isExistingLicensePdf) ? 'display: block;' : 'display: none;' ?> font-size: 2.2rem; color: #EF4444;">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                </div>
                                <div class="doc-preview-meta">
                                    <div class="doc-preview-name" id="licensePreviewName"><?= $hasExistingLicenseDoc ? e($existingLicenseDocName) : 'driving_license.jpg' ?></div>
                                    <div class="doc-preview-size" id="licensePreviewSize"><?= $hasExistingLicenseDoc ? 'Verified License on File' : '0 KB' ?></div>
                                    <div class="doc-preview-status">
                                        <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Driving Licence Attached &amp; Ready
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

            <!-- ========================================================================= -->
            <!-- 6. Government Identification & Compliance Documents (Matching create.php) -->
            <!-- ========================================================================= -->
            <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; padding: 1.5rem; border-radius: 18px; margin-bottom: 1.75rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-weight: 700; color: #0C2B52; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-passport" style="color: #176B5B;"></i> Government Identification &amp; Compliance Documents
                    </span>
                    <span class="modern-badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;">
                        Official Identity Dossier
                    </span>
                </div>

                <div class="modern-form-grid-3" style="margin-bottom: 1.25rem;">
                    <!-- Document Type Dropdown (Matching create.php) -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_type">ID Document Type</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <select id="id_type" name="id_type" class="modern-select-pill" onchange="onDocumentTypeChange(this.value)">
                                <option value="aadhaar" <?= $currentDocType === 'aadhaar' ? 'selected' : '' ?>>Aadhaar Card</option>
                                <option value="pan_card" <?= $currentDocType === 'pan_card' ? 'selected' : '' ?>>PAN Card</option>
                                <option value="passport" <?= $currentDocType === 'passport' ? 'selected' : '' ?>>Passport</option>
                                <option value="police_verification" <?= $currentDocType === 'police_verification' ? 'selected' : '' ?>>Police Verification Certificate</option>
                                <option value="voter_id" <?= $currentDocType === 'voter_id' ? 'selected' : '' ?>>Voter ID Card</option>
                                <option value="other" <?= $currentDocType === 'other' ? 'selected' : '' ?>>Other Official Certificate</option>
                            </select>
                        </div>
                    </div>

                    <!-- Document Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_number" id="lblDocNumber">Aadhaar Card Number</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="id_number" name="id_number" class="modern-input-pill" placeholder="e.g. 5432 1234 5678" value="<?= e($old['id_number'] ?? $employee['id_number'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Document Expiry Date -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="id_expiry">Document Expiry Date</label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <input type="date" id="id_expiry" name="id_expiry" class="modern-input-pill" value="<?= e($old['id_expiry'] ?? $employee['id_expiry'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Dynamic ID Document Upload Dropzone -->
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

                        <!-- Empty State Inside Dropzone -->
                        <div id="dropzoneEmptyState" style="<?= $hasExistingIdDoc ? 'display: none;' : 'display: block;' ?>">
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
                        <div id="dropzonePreviewState" style="<?= $hasExistingIdDoc ? 'display: block;' : 'display: none;' ?>">
                            <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                                <div class="doc-preview-thumb-box" onclick="openDocPreviewModal('id')" style="cursor: pointer;" title="Click to view full preview">
                                    <img id="docPreviewImage" src="<?= ($hasExistingIdDoc && !$isExistingIdPdf) ? e($existingIdDocUrl) : '' ?>" alt="Document preview" style="<?= ($hasExistingIdDoc && !$isExistingIdPdf) ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    <div id="docPreviewPdfIcon" style="<?= ($hasExistingIdDoc && $isExistingIdPdf) ? 'display: block;' : 'display: none;' ?> font-size: 2.2rem; color: #EF4444;">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                </div>
                                <div class="doc-preview-meta">
                                    <div class="doc-preview-name" id="docPreviewName"><?= $hasExistingIdDoc ? e($existingIdDocName) : 'document_scan.jpg' ?></div>
                                    <div class="doc-preview-size" id="docPreviewSize"><?= $hasExistingIdDoc ? 'Verified Document on File' : '0 KB' ?></div>
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

            <!-- ========================================================================= -->
            <!-- 7. Special Operational Notes                                              -->
            <!-- ========================================================================= -->
            <div class="modern-section-header" style="margin-top: 1.75rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Special Operational / Verification Notes</h2>
                    <p class="modern-section-sub">Internal office observations, background check remarks, driver skills, and emergency dispatch alerts.</p>
                </div>
            </div>

            <div class="modern-form-group" style="margin-bottom: 2rem;">
                <label class="modern-form-label" for="notes">
                    Special Operational / Verification Notes
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                    <input type="text" id="notes" name="notes" class="modern-input-pill" placeholder="e.g. VIP chauffeur, seasoned mountain route experience, background verified" value="<?= e($old['notes'] ?? $employee['notes'] ?? '') ?>">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Cancel &amp; Discard
                </a>
                <button type="submit" class="modern-btn-submit" id="btnSubmitEditEmployee">
                    <i class="fa-solid fa-check"></i> <span id="btnSubmitText"><?= __('employee_form.save_changes', [], 'Save Changes') ?></span>
                </button>
            </div>
        </div>
    </form>

    <!-- Professional Document Lightbox Preview Modal (Supports License & ID documents) -->
    <div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
        <div class="doc-lightbox-container" onclick="event.stopPropagation()">
            <div class="doc-lightbox-header">
                <div class="doc-lightbox-title-box">
                    <i class="fa-solid fa-file-shield doc-lightbox-icon"></i>
                    <div>
                        <h3 class="doc-lightbox-title" id="docLightboxTitle">Document Preview</h3>
                        <p class="doc-lightbox-subtitle" id="docLightboxSubtitle">Compliance Document Attachment</p>
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
                    <p style="color: #64748B; font-size: 0.88rem; margin-bottom: 1.25rem;">PDF Document attached and verified for official staff profile verification.</p>
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
// Employee Edit State
const hasExistingAvatar = <?= $hasExistingAvatar ? 'true' : 'false' ?>;
const existingAvatarUrl = <?= json_encode($existingAvatarUrl) ?>;
let isAvatarRemoved = false;
let avatarDataUrl = null;

const hasExistingLicenseDoc = <?= $hasExistingLicenseDoc ? 'true' : 'false' ?>;
const existingLicenseDocUrl = <?= json_encode($existingLicenseDocUrl) ?>;
const existingLicenseDocName = <?= json_encode($existingLicenseDocName) ?>;
const isExistingLicensePdf = <?= $isExistingLicensePdf ? 'true' : 'false' ?>;
let isLicenseRemoved = false;
let uploadedLicenseMeta = null;
let uploadedLicenseDataUrl = null;

const hasExistingIdDoc = <?= $hasExistingIdDoc ? 'true' : 'false' ?>;
const existingIdDocUrl = <?= json_encode($existingIdDocUrl) ?>;
const existingIdDocName = <?= json_encode($existingIdDocName) ?>;
const isExistingIdPdf = <?= $isExistingIdPdf ? 'true' : 'false' ?>;
let isIdRemoved = false;
let uploadedIdMeta = null;
let uploadedIdDataUrl = null;

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
    pan_card: {
        title: 'Upload PAN Card Scan',
        badge: 'Income Tax Department of India',
        icon: 'fa-credit-card',
        numberLabel: 'PAN Card Number',
        placeholder: 'e.g. ABCDE1234F',
        help: 'Upload clear scan of official Permanent Account Number (PAN) Card'
    },
    passport: {
        title: 'Upload Passport Front Bio-Data Page',
        badge: 'International Travel Document',
        icon: 'fa-passport',
        numberLabel: 'Passport Document Number',
        placeholder: 'e.g. A1234567',
        help: 'Upload clear photo of Passport front bio page with photo and signature'
    },
    police_verification: {
        title: 'Upload Police Verification Certificate',
        badge: 'Law Enforcement Clearance',
        icon: 'fa-shield-halved',
        numberLabel: 'Certificate Reference Number',
        placeholder: 'e.g. PV-2026-88910',
        help: 'Upload state police character & background verification clearance certificate'
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

// Role Change Handler
function onRoleChanged(roleSlug) {
    const chauffeurSection = document.getElementById('chauffeurLicenseSection');
    const deptSelect = document.getElementById('department');
    const desigInput = document.getElementById('designation');

    if (roleSlug === 'driver' || roleSlug === 'chauffeur') {
        if (chauffeurSection) chauffeurSection.style.display = 'block';
        if (deptSelect) deptSelect.value = 'fleet_chauffeur';
        if (desigInput && (!desigInput.value || desigInput.value.includes('Executive'))) {
            desigInput.value = 'Chauffeur / Driver';
        }
    } else {
        if (roleSlug === 'dispatcher') {
            if (deptSelect) deptSelect.value = 'dispatch';
            if (desigInput && (!desigInput.value || desigInput.value.includes('Chauffeur'))) {
                desigInput.value = 'Fleet Dispatcher';
            }
        } else if (roleSlug === 'manager') {
            if (deptSelect) deptSelect.value = 'management';
            if (desigInput && (!desigInput.value || desigInput.value.includes('Chauffeur'))) {
                desigInput.value = 'Operations Manager';
            }
        } else if (roleSlug === 'admin') {
            if (deptSelect) deptSelect.value = 'administration';
            if (desigInput && (!desigInput.value || desigInput.value.includes('Chauffeur'))) {
                desigInput.value = 'System Administrator';
            }
        }
    }
}

// Profile Photo Handlers
function handleProfilePhotoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file (PNG, JPG, WEBP)');
        input.value = '';
        return;
    }

    if (file.size > 3 * 1024 * 1024) {
        alert('Profile photo size exceeds 3MB limit');
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

// Driving License File Handlers
function handleLicenseFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        alert('License document file size exceeds 5MB limit');
        input.value = '';
        return;
    }

    const removeInput = document.getElementById('remove_license_document');
    if (removeInput) removeInput.value = '0';
    isLicenseRemoved = false;

    const sizeKb = (file.size / 1024).toFixed(1);
    const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

    uploadedLicenseMeta = {
        name: file.name,
        size: sizeStr,
        type: file.type
    };

    const emptyState = document.getElementById('licenseDropzoneEmpty');
    const previewState = document.getElementById('licenseDropzonePreview');
    const nameEl = document.getElementById('licensePreviewName');
    const sizeEl = document.getElementById('licensePreviewSize');
    const imgEl = document.getElementById('licensePreviewImage');
    const pdfIconEl = document.getElementById('licensePreviewPdfIcon');

    if (nameEl) nameEl.textContent = file.name;
    if (sizeEl) sizeEl.textContent = sizeStr;

    const reader = new FileReader();
    reader.onload = function(e) {
        uploadedLicenseDataUrl = e.target.result;
        const b64Input = document.getElementById('license_document_file_base64');
        if (b64Input) b64Input.value = e.target.result;
        const nameInput = document.getElementById('license_document_file_name');
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

function clearLicenseUpload() {
    const input = document.getElementById('license_document_file');
    if (input) input.value = '';
    const b64Input = document.getElementById('license_document_file_base64');
    if (b64Input) b64Input.value = '';
    const nameInput = document.getElementById('license_document_file_name');
    if (nameInput) nameInput.value = '';

    const removeInput = document.getElementById('remove_license_document');
    if (removeInput) removeInput.value = '1';

    uploadedLicenseMeta = null;
    uploadedLicenseDataUrl = null;
    isLicenseRemoved = true;

    const emptyState = document.getElementById('licenseDropzoneEmpty');
    const previewState = document.getElementById('licenseDropzonePreview');
    const imgEl = document.getElementById('licensePreviewImage');

    if (imgEl) imgEl.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// ID Document Handlers
function handleDocumentFileChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        alert('ID document file size exceeds 5MB limit');
        input.value = '';
        return;
    }

    const removeInput = document.getElementById('remove_id_document');
    if (removeInput) removeInput.value = '0';
    isIdRemoved = false;

    const sizeKb = (file.size / 1024).toFixed(1);
    const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

    uploadedIdMeta = {
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
        uploadedIdDataUrl = e.target.result;
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

    uploadedIdMeta = null;
    uploadedIdDataUrl = null;
    isIdRemoved = true;

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const imgEl = document.getElementById('docPreviewImage');

    if (imgEl) imgEl.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';
}

// Professional Document Lightbox Modal Handlers (Unified for License & ID)
function openDocPreviewModal(kind) {
    let docUrl = null;
    let docName = null;
    let isPdf = false;
    let sizeText = '';

    if (kind === 'license') {
        docUrl = uploadedLicenseDataUrl;
        docName = uploadedLicenseMeta ? uploadedLicenseMeta.name : null;
        isPdf = uploadedLicenseMeta ? (!uploadedLicenseMeta.type.startsWith('image/')) : false;
        sizeText = uploadedLicenseMeta ? uploadedLicenseMeta.size : '';

        if (!docUrl && hasExistingLicenseDoc && !isLicenseRemoved) {
            docUrl = existingLicenseDocUrl;
            docName = existingLicenseDocName;
            isPdf = isExistingLicensePdf;
            sizeText = 'Verified License on File';
        }
    } else {
        docUrl = uploadedIdDataUrl;
        docName = uploadedIdMeta ? uploadedIdMeta.name : null;
        isPdf = uploadedIdMeta ? (!uploadedIdMeta.type.startsWith('image/')) : false;
        sizeText = uploadedIdMeta ? uploadedIdMeta.size : '';

        if (!docUrl && hasExistingIdDoc && !isIdRemoved) {
            docUrl = existingIdDocUrl;
            docName = existingIdDocName;
            isPdf = isExistingIdPdf;
            sizeText = 'Verified Document on File';
        }
    }

    if (!docUrl) {
        alert('No document uploaded to preview');
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

    const fileName = docName || (kind === 'license' ? 'driving_license' : 'document_preview');

    if (titleEl) titleEl.textContent = fileName;
    if (subEl) subEl.textContent = (sizeText ? sizeText + ' • ' : '') + (kind === 'license' ? 'Commercial License Attachment' : 'Identity Verification Attachment');
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

    // License Dropzone Drag & Drop
    const licDrop = document.getElementById('licenseDropzone');
    if (licDrop) {
        ['dragenter', 'dragover'].forEach(eventName => {
            licDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                licDrop.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            licDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                licDrop.classList.remove('is-dragover');
            });
        });
        licDrop.addEventListener('drop', (e) => {
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

    // ID Dropzone Drag & Drop
    const idDrop = document.getElementById('documentDropzone');
    if (idDrop) {
        ['dragenter', 'dragover'].forEach(eventName => {
            idDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                idDrop.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            idDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                idDrop.classList.remove('is-dragover');
            });
        });
        idDrop.addEventListener('drop', (e) => {
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
    const editForm = document.getElementById('editEmployeeForm');
    const submitBtn = document.getElementById('btnSubmitEditEmployee');
    if (editForm && submitBtn) {
        editForm.addEventListener('submit', function() {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.85';
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving Staff Profile...</span>';
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

.status-pill-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.84rem;
    font-weight: 700;
}
.status-active {
    background: #E8F8F0;
    color: #059669;
    border: 1px solid #A7F3D0;
}
.status-leave {
    background: #FFFBEB;
    color: #D97706;
    border: 1px solid #FDE68A;
}
.status-terminated {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
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
/* 1. Staff Avatar Component (Matching create.php)                           */
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
.modern-form-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 1100px) {
    .modern-form-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .modern-form-grid-4 {
        grid-template-columns: 1fr;
    }
}

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

.modern-field-hint {
    font-size: 0.78rem;
    color: #64748B;
    margin-top: 3px;
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
