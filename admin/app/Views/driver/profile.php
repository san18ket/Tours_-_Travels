<?php
/**
 * Driver Profile Console View — Dedicated Driver Portal
 * Professional Driver Profile, Credentials, Avatar Upload & Security
 * Native Mobile App Experience & Executive Desktop UI
 */

$userPhoto = $driver['profile_photo'] ?? null;
$avatarUrl = !empty($userPhoto) ? customer_avatar_url($userPhoto) : null;
$initials  = strtoupper(mb_substr($driver['name'] ?? 'D', 0, 1));
$dutyStatus = strtolower($driver['duty_status'] ?? 'off_duty');

// License expiry calculation
$licExpiry = $driver['license_expiry'] ?? null;
$isLicExpired = !empty($licExpiry) && $licExpiry < date('Y-m-d');
$isLicExpiring = !empty($licExpiry) && !$isLicExpired && $licExpiry <= date('Y-m-d', strtotime('+30 days'));

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/driver/dashboard') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Console</span></a>'
    . '<a href="' . url('/driver/vehicle') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-car" style="margin-right:6px;"></i><span>Assigned Fleet</span></a>'
    . '</div>';
?>

<div class="bookings-page-view driver-portal-container" id="driverProfilePage">

    <!-- 1. DESKTOP ONLY: Executive Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Driver Profile &amp; Credentials',
            'description' => 'Manage your personal profile, authenticated contact details, verified licensing, and account security.',
            'badge'       => 'Verified Driver',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Profile & Security', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <!-- 2. MOBILE ONLY: Native Mobile App Top Bar -->
    <div class="driver-app-topbar">
        <div class="driver-app-topbar-left">
            <a href="<?= url('/driver/dashboard') ?>" class="driver-app-back-btn" title="<?= e(__('common.back_to_dashboard', 'Back to Dashboard')) ?>" aria-label="<?= e(__('common.back', 'Back')) ?>">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="driver-app-topbar-title-wrap">
                <h1 class="driver-app-topbar-title">Driver Profile</h1>
                <span class="driver-app-topbar-sub"><?= e(company_name()) ?></span>
            </div>
        </div>
        <div class="driver-app-topbar-right">
            <a href="<?= url('/driver/vehicle') ?>" class="driver-app-action-pill" title="<?= e(__('booking.assigned_vehicle', 'Assigned Fleet Vehicle')) ?>">
                <i class="fa-solid fa-car"></i>
                <span>Fleet</span>
            </a>
        </div>
    </div>

    <!-- 3. DRIVER PROFILE HERO CARD (Mobile App Hero & Desktop Identity) -->
    <div class="driver-hero-card">
        <div class="driver-hero-card-inner">
            <!-- Avatar with Instant Preview & Camera Action Badge -->
            <div class="driver-hero-avatar-wrap">
                <div id="avatarPreviewContainer" class="driver-hero-avatar" onclick="triggerPhotoSelect()" title="<?= e(__('tooltip.tap_to_upload_profile_photo', 'Tap to upload profile photo')) ?>">
                    <?php if (!empty($avatarUrl)): ?>
                        <img id="avatarPreviewImg" src="<?= e($avatarUrl) ?>" alt="<?= e($driver['name'] ?? 'Driver') ?>" onerror="this.style.display='none'; document.getElementById('avatarMonogramFallback').style.display='flex';">
                        <div id="avatarMonogramFallback" class="driver-avatar-monogram" style="display: none;">
                            <?= e($initials) ?>
                        </div>
                    <?php else: ?>
                        <img id="avatarPreviewImg" src="" alt="" style="display: none;">
                        <div id="avatarMonogramFallback" class="driver-avatar-monogram">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Floating Camera Action Badge -->
                <button type="button" class="driver-avatar-camera-btn" onclick="triggerPhotoSelect()" title="<?= e(__('tooltip.change_profile_photo', 'Change Profile Photo')) ?>" aria-label="<?= e(__('btn.change_photo', 'Change photo')) ?>">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            <!-- Driver Identity Info -->
            <div class="driver-hero-info">
                <div class="driver-hero-badge-row">
                    <span class="driver-verified-badge">
                        <i class="fa-solid fa-circle-check"></i> Verified Driver
                    </span>
                    <span class="driver-duty-status-pill status-<?= $dutyStatus ?>">
                        <span class="pulse-indicator"></span> <?= strtoupper(str_replace('_', ' ', $dutyStatus)) ?>
                    </span>
                </div>

                <h2 class="driver-hero-name"><?= e($driver['name']) ?></h2>

                <div class="driver-hero-meta-row">
                    <span class="driver-hero-meta-item">
                        <i class="fa-solid fa-user-circle"></i> @<?= e($driver['username'] ?? 'driver') ?>
                    </span>
                    <?php if (!empty($driver['employee_code'])): ?>
                        <span class="driver-hero-meta-dot">•</span>
                        <span class="driver-hero-code-badge">
                            <i class="fa-solid fa-id-badge"></i> <?= e($driver['employee_code']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- App Quick Stats Bar -->
                <div class="driver-hero-stats-row">
                    <div class="hero-stat-pill">
                        <i class="fa-solid fa-star stat-star-icon"></i>
                        <span><strong><?= number_format((float)($driver['rating'] ?? 5.0), 1) ?></strong> Rating</span>
                    </div>
                    <div class="hero-stat-pill">
                        <i class="fa-solid fa-award stat-award-icon"></i>
                        <span><strong><?= (int)($driver['experience_years'] ?? 1) ?> Yrs</strong> Exp</span>
                    </div>
                    <?php if (!empty($licExpiry)): ?>
                        <div class="hero-stat-pill">
                            <i class="fa-solid fa-id-card stat-lic-icon" style="color: <?= $isLicExpired ? '#EF4444' : ($isLicExpiring ? '#F59E0B' : '#10B981') ?>;"></i>
                            <span>License: <strong><?= $isLicExpired ? 'Expired' : ($isLicExpiring ? 'Expiring' : 'Active') ?></strong></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MOBILE ONLY: Segmented Tab Bar Switcher (Credentials / Contact Info / Security) -->
    <div class="driver-app-tabs-wrap">
        <div class="driver-app-tabs">
            <button type="button" class="driver-app-tab active" data-tab="tab-credentials" onclick="switchProfileTab('tab-credentials')">
                <i class="fa-solid fa-id-card-clip"></i>
                <span>Credentials</span>
            </button>
            <button type="button" class="driver-app-tab" data-tab="tab-contact" onclick="switchProfileTab('tab-contact')">
                <i class="fa-solid fa-user-pen"></i>
                <span>Contact Info</span>
            </button>
            <button type="button" class="driver-app-tab" data-tab="tab-security" onclick="switchProfileTab('tab-security')">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Security</span>
            </button>
        </div>
    </div>

    <!-- 5. MAIN CONTENT LAYOUT (Two-Column Desktop / App-Tabbed Mobile) -->
    <div class="driver-profile-grid">

        <!-- Column A: Credentials & Security -->
        <div class="driver-profile-col driver-profile-col-left">

            <!-- SECTION 1: VERIFIED PROFESSIONAL CREDENTIALS -->
            <div class="driver-app-card profile-section-panel" id="panel-tab-credentials">
                <div class="driver-card-header">
                    <div class="driver-card-header-icon icon-emerald">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div class="driver-card-header-meta">
                        <h3 class="driver-card-title">Verified Professional Credentials</h3>
                        <p class="driver-card-subtitle">Official commercial licensing &amp; corporate fleet records</p>
                    </div>
                </div>

                <div class="driver-card-body">
                    <!-- Credentials Grid Cards -->
                    <div class="credentials-spec-grid">
                        <!-- License Number Card -->
                        <div class="credential-item-card">
                            <div class="credential-item-head">
                                <span class="credential-label">Driving License Number</span>
                                <span class="credential-icon-pill icon-blue"><i class="fa-solid fa-id-card"></i></span>
                            </div>
                            <div class="credential-value credential-mono">
                                <?= e($driver['license_number'] ?? 'N/A') ?>
                            </div>
                            <div class="credential-footer">
                                <span class="badge-verified-soft"><i class="fa-solid fa-circle-check"></i> Government Verified</span>
                            </div>
                        </div>

                        <!-- License Expiry Card -->
                        <div class="credential-item-card">
                            <div class="credential-item-head">
                                <span class="credential-label">License Expiry</span>
                                <span class="credential-icon-pill <?= $isLicExpired ? 'icon-red' : ($isLicExpiring ? 'icon-amber' : 'icon-emerald') ?>">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </span>
                            </div>
                            <div class="credential-value" style="color: <?= $isLicExpired ? '#EF4444' : ($isLicExpiring ? '#D97706' : '#10B981') ?>;">
                                <?= format_date($licExpiry) ?>
                            </div>
                            <div class="credential-footer">
                                <?php if ($isLicExpired): ?>
                                    <span class="lic-status-badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Expired • Action Needed</span>
                                <?php elseif ($isLicExpiring): ?>
                                    <span class="lic-status-badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Renewal Due Soon</span>
                                <?php else: ?>
                                    <span class="lic-status-badge badge-success"><i class="fa-solid fa-shield-check"></i> Compliant &amp; Active</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Experience Card -->
                        <div class="credential-item-card">
                            <div class="credential-item-head">
                                <span class="credential-label">Driving Experience</span>
                                <span class="credential-icon-pill icon-teal"><i class="fa-solid fa-route"></i></span>
                            </div>
                            <div class="credential-value">
                                <?= (int)($driver['experience_years'] ?? 1) ?> Years
                            </div>
                            <div class="credential-footer">
                                <span class="credential-subtext">Commercial passenger transit</span>
                            </div>
                        </div>

                        <!-- Driver Rating Card -->
                        <div class="credential-item-card">
                            <div class="credential-item-head">
                                <span class="credential-label">Driver Rating</span>
                                <span class="credential-icon-pill icon-amber"><i class="fa-solid fa-star"></i></span>
                            </div>
                            <div class="credential-value star-val">
                                <i class="fa-solid fa-star"></i>
                                <span><?= number_format((float)($driver['rating'] ?? 5.0), 1) ?> <small>/ 5.0</small></span>
                            </div>
                            <div class="credential-footer">
                                <span class="credential-subtext">Guest feedback satisfaction</span>
                            </div>
                        </div>
                    </div>

                    <!-- Corporate Employment Record Sheet -->
                    <?php if (!empty($driver['employee_code'])): ?>
                        <div class="employment-sheet">
                            <div class="employment-sheet-title">
                                <i class="fa-solid fa-building-user"></i> Corporate Employment Record
                            </div>
                            <div class="employment-details-list">
                                <div class="employment-detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-barcode"></i> Employee Code</span>
                                    <span class="detail-val-code"><?= e($driver['employee_code']) ?></span>
                                </div>
                                <div class="employment-detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-sitemap"></i> Department</span>
                                    <span class="detail-val"><?= e(ucfirst($driver['department'] ?? 'Operations')) ?></span>
                                </div>
                                <div class="employment-detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-briefcase"></i> Designation</span>
                                    <span class="detail-val"><?= e($driver['designation'] ?? 'Driver') ?></span>
                                </div>
                                <div class="employment-detail-row">
                                    <span class="detail-label"><i class="fa-solid fa-calendar-day"></i> Joining Date</span>
                                    <span class="detail-val"><?= format_date($driver['joining_date']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION 3: ACCOUNT SECURITY & PASSWORD -->
            <div class="driver-app-card profile-section-panel" id="panel-tab-security">
                <div class="driver-card-header">
                    <div class="driver-card-header-icon icon-blue">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="driver-card-header-meta">
                        <h3 class="driver-card-title">Account Security &amp; Password</h3>
                        <p class="driver-card-subtitle">Keep your driver login credentials authenticated &amp; secure</p>
                    </div>
                </div>

                <div class="driver-card-body">
                    <form action="<?= url('/driver/profile/password') ?>" method="POST" id="driverPasswordForm">
                        <?= csrf_field() ?>

                        <!-- Current Password -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="current_password">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="app-input-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" id="current_password" name="current_password" class="app-form-input" required placeholder="<?= e(__('placeholder.enter_current_password', 'Enter current password')) ?>" autocomplete="current-password">
                                <button type="button" class="app-pw-toggle-btn" onclick="togglePasswordVisibility('current_password', this)" aria-label="<?= e(__('common.view', 'Toggle password visibility')) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Grid -->
                        <div class="app-form-grid-2">
                            <div class="app-form-group">
                                <label class="app-form-label" for="new_password">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="app-input-wrap">
                                    <span class="app-input-icon"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" id="new_password" name="new_password" class="app-form-input" required minlength="8" placeholder="<?= e(__('placeholder.minimum_8_chars', 'Minimum 8 chars')) ?>" autocomplete="new-password" oninput="checkPasswordMatch()">
                                    <button type="button" class="app-pw-toggle-btn" onclick="togglePasswordVisibility('new_password', this)" aria-label="<?= e(__('common.view', 'Toggle password visibility')) ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="app-form-group">
                                <label class="app-form-label" for="confirm_password">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="app-input-wrap">
                                    <span class="app-input-icon"><i class="fa-solid fa-check-double"></i></span>
                                    <input type="password" id="confirm_password" name="confirm_password" class="app-form-input" required minlength="8" placeholder="<?= e(__('placeholder.repeat_new_password', 'Repeat new password')) ?>" autocomplete="new-password" oninput="checkPasswordMatch()">
                                    <button type="button" class="app-pw-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="<?= e(__('common.view', 'Toggle password visibility')) ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="passwordMatchMessage" class="pw-match-msg" style="display: none;"></div>

                        <button type="submit" class="btn-app-primary" id="btnUpdatePassword">
                            <i class="fa-solid fa-shield-halved"></i> <span>Update Password Securely</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Column B: Personal Contact Details & Avatar Upload Form -->
        <div class="driver-profile-col driver-profile-col-right">

            <!-- SECTION 2: PERSONAL CONTACT INFORMATION -->
            <div class="driver-app-card profile-section-panel" id="panel-tab-contact">
                <div class="driver-card-header">
                    <div class="driver-card-header-icon icon-emerald">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div class="driver-card-header-meta">
                        <h3 class="driver-card-title">Personal Contact Information</h3>
                        <p class="driver-card-subtitle">Authenticated driver mobile contacts &amp; residence details</p>
                    </div>
                </div>

                <div class="driver-card-body">
                    <form action="<?= url('/driver/profile') ?>" method="POST" enctype="multipart/form-data" id="driverProfileForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="remove_profile_photo" id="removeProfilePhotoInput" value="0">
                        <input type="hidden" name="profile_photo_base64" id="profilePhotoBase64" value="">

                        <!-- Hidden File Input triggered by Avatar Click or Button -->
                        <input type="file" name="profile_photo" id="profilePhotoFileInput" accept="image/jpeg,image/png,image/webp" style="display: none;">

                        <!-- Mobile Avatar Action Box -->
                        <div class="app-avatar-action-box">
                            <div class="app-avatar-action-left">
                                <span class="app-avatar-box-title"><i class="fa-solid fa-image"></i> Profile Avatar Photo</span>
                                <span class="app-avatar-box-sub">Supports JPG, PNG, WEBP (Max 3MB). Preview updates immediately.</span>
                            </div>
                            <div class="app-avatar-action-right">
                                <button type="button" class="btn-app-secondary btn-sm" onclick="triggerPhotoSelect()">
                                    <i class="fa-solid fa-camera"></i> Change Photo
                                </button>
                                <?php if (!empty($avatarUrl)): ?>
                                    <button type="button" id="removePhotoBtn" class="btn-app-danger btn-sm" onclick="handleRemovePhoto()" title="<?= e(__('tooltip.remove_current_photo', 'Remove current photo')) ?>">
                                        <i class="fa-solid fa-trash-can"></i> Remove
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email (Read-Only Official Account) -->
                        <div class="app-form-group">
                            <label class="app-form-label">
                                Official Email (Fleet Username)
                            </label>
                            <div class="app-input-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" class="app-form-input app-input-readonly" value="<?= e($driver['email']) ?>" readonly>
                                <span class="app-input-suffix-badge"><i class="fa-solid fa-lock"></i> Locked</span>
                            </div>
                            <span class="app-field-help">Managed by corporate fleet admin. Cannot be edited from driver portal.</span>
                        </div>

                        <!-- Primary Mobile Number -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="driver_phone">
                                Primary Mobile Number <span class="text-danger">*</span>
                            </label>
                            <div class="app-input-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" id="driver_phone" name="phone" class="app-form-input" value="<?= e($driver['phone'] ?? $driver['employee_phone'] ?? '') ?>" required placeholder="<?= e(__('placeholder.e_g_91_9876543210', 'e.g. +91 9876543210')) ?>" autocomplete="tel">
                            </div>
                            <span class="app-field-help">Used for client duty dispatches, passenger contact, and live SMS updates.</span>
                        </div>

                        <!-- Alternate Contact Phone -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="driver_alt_phone">
                                Alternate Contact Phone
                            </label>
                            <div class="app-input-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                                <input type="tel" id="driver_alt_phone" name="alternate_phone" class="app-form-input" value="<?= e($driver['alternate_phone'] ?? '') ?>" placeholder="<?= e(__('placeholder.optional_secondary_contact_num', 'Optional secondary contact number')) ?>" autocomplete="tel">
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="emergency_contact">
                                Emergency Contact (Name &amp; Phone)
                            </label>
                            <div class="app-input-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-kit-medical"></i></span>
                                <input type="text" id="emergency_contact" name="emergency_contact" class="app-form-input" value="<?= e($driver['emergency_contact'] ?? $driver['employee_emergency_contact'] ?? '') ?>" placeholder="<?= e(__('placeholder.e_g_spouse_brother_91_98765432', 'e.g. Spouse / Brother (+91 9876543211)')) ?>">
                            </div>
                            <span class="app-field-help">Fleet dispatch will contact this person in case of roadside or health emergency.</span>
                        </div>

                        <!-- Residential Address -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="driver_address">
                                Residential Address
                            </label>
                            <div class="app-input-wrap app-textarea-wrap">
                                <span class="app-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                                <textarea id="driver_address" name="address" class="app-form-input app-textarea" rows="3" placeholder="<?= e(__('placeholder.full_residential_street_addres', 'Full residential street address, landmark and city...')) ?>"><?= e($driver['address'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Save Profile Changes Button -->
                        <button type="submit" class="btn-app-primary" id="btnSaveProfile">
                            <i class="fa-solid fa-floppy-disk"></i> <span>Save Profile Changes</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Client-Side Avatar Preview & Mobile Tabs JavaScript -->
<script>
function triggerPhotoSelect() {
    const fileInput = document.getElementById('profilePhotoFileInput');
    if (fileInput) {
        fileInput.click();
    }
}

function switchProfileTab(tabId) {
    if (window.innerWidth > 991) {
        return; // Desktop shows all sections simultaneously in 2 columns
    }
    
    // Update active tab button
    document.querySelectorAll('.driver-app-tab').forEach(btn => {
        if (btn.getAttribute('data-tab') === tabId) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update active panel
    document.querySelectorAll('.profile-section-panel').forEach(panel => {
        if (panel.id === 'panel-' + tabId) {
            panel.classList.add('tab-visible');
            panel.style.display = 'block';
        } else {
            panel.classList.remove('tab-visible');
            panel.style.display = 'none';
        }
    });

    // Smooth scroll to top of section on mobile
    const target = document.getElementById('panel-' + tabId);
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    }
}

function checkPasswordMatch() {
    const pw = document.getElementById('new_password');
    const cpw = document.getElementById('confirm_password');
    const msg = document.getElementById('passwordMatchMessage');
    if (!pw || !cpw || !msg) return;

    if (!cpw.value) {
        msg.style.display = 'none';
        return;
    }

    if (pw.value === cpw.value) {
        msg.style.display = 'flex';
        msg.className = 'pw-match-msg match-success';
        msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Passwords match perfectly';
    } else {
        msg.style.display = 'flex';
        msg.className = 'pw-match-msg match-error';
        msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Passwords do not match';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('profilePhotoFileInput');
    const previewImg = document.getElementById('avatarPreviewImg');
    const fallbackBox = document.getElementById('avatarMonogramFallback');
    const removeInput = document.getElementById('removeProfilePhotoInput');
    const removeBtn = document.getElementById('removePhotoBtn');
    const base64Input = document.getElementById('profilePhotoBase64');

    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Validate file size (max 3MB)
            if (file.size > 3 * 1024 * 1024) {
                alert('Profile photo size must be less than 3MB.');
                fileInput.value = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid JPG, PNG, or WEBP image.');
                fileInput.value = '';
                return;
            }

            if (removeInput) removeInput.value = '0';

            // Immediate Client-Side Preview via FileReader
            const reader = new FileReader();
            reader.onload = function (evt) {
                if (previewImg) {
                    previewImg.src = evt.target.result;
                    previewImg.style.display = 'block';
                }
                if (fallbackBox) {
                    fallbackBox.style.display = 'none';
                }
                if (base64Input) {
                    base64Input.value = evt.target.result;
                }
                if (removeBtn) {
                    removeBtn.style.display = 'inline-flex';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    window.handleRemovePhoto = function () {
        if (!confirm('Are you sure you want to remove your profile photo?')) return;
        if (removeInput) removeInput.value = '1';
        if (base64Input) base64Input.value = '';
        if (fileInput) fileInput.value = '';
        if (previewImg) {
            previewImg.src = '';
            previewImg.style.display = 'none';
        }
        if (fallbackBox) {
            fallbackBox.style.display = 'flex';
        }
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    };

    // Initialize tabs for mobile view
    function initMobileTabs() {
        if (window.innerWidth <= 991) {
            const activeTab = document.querySelector('.driver-app-tab.active');
            const tabId = activeTab ? activeTab.getAttribute('data-tab') : 'tab-credentials';
            switchProfileTab(tabId);
        } else {
            // On desktop, ensure all panels are visible
            document.querySelectorAll('.profile-section-panel').forEach(p => {
                p.style.display = 'block';
            });
        }
    }

    initMobileTabs();
    window.addEventListener('resize', initMobileTabs);

    // Form submit states
    const profileForm = document.getElementById('driverProfileForm');
    const saveBtn = document.getElementById('btnSaveProfile');
    if (profileForm && saveBtn) {
        profileForm.addEventListener('submit', function () {
            saveBtn.style.pointerEvents = 'none';
            saveBtn.style.opacity = '0.85';
            saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Saving Changes...</span>';
        });
    }

    const passwordForm = document.getElementById('driverPasswordForm');
    const pwBtn = document.getElementById('btnUpdatePassword');
    if (passwordForm && pwBtn) {
        passwordForm.addEventListener('submit', function () {
            pwBtn.style.pointerEvents = 'none';
            pwBtn.style.opacity = '0.85';
            pwBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Updating Password...</span>';
        });
    }
});
</script>

<style>
/* ==========================================================================
   DRIVER PORTAL PROFILE DESIGN SYSTEM (Mobile App & Executive Desktop)
   ========================================================================== */

.driver-portal-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0 0 2.5rem;
    box-sizing: border-box;
}

/* 1. Mobile App Top Bar */
.driver-app-topbar {
    display: none;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0 1rem;
    width: 100%;
    box-sizing: border-box;
}

.driver-app-topbar-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.driver-app-back-btn {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 10px;
    background: #F1F5F9;
    color: #0F172A;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #E2E8F0;
}

.driver-app-back-btn:active {
    background: #E2E8F0;
    transform: scale(0.96);
}

.driver-app-topbar-title-wrap {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.driver-app-topbar-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.driver-app-topbar-sub {
    font-size: 0.75rem;
    color: #64748B;
    font-weight: 600;
    margin-top: 1px;
}

.driver-app-action-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    color: #176B5B;
    padding: 0.4rem 0.8rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.driver-app-action-pill:active {
    background: #E8F4F0;
    border-color: #176B5B;
}

/* 2. Driver Profile Hero Card */
.driver-hero-card {
    width: 100%;
    background: linear-gradient(135deg, #0A1E19 0%, #153E34 50%, #0F172A 100%);
    border-radius: 20px;
    color: #FFFFFF;
    padding: 1.75rem 2rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
}

.driver-hero-card::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0) 70%);
    pointer-events: none;
}

.driver-hero-card-inner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

.driver-hero-avatar-wrap {
    position: relative;
    width: 96px;
    height: 96px;
    min-width: 96px;
    flex-shrink: 0;
}

.driver-hero-avatar {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    overflow: hidden;
    border: 3.5px solid #10B981;
    background: #1E293B;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.driver-hero-avatar:hover {
    transform: scale(1.03);
}

.driver-hero-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.driver-avatar-monogram {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.3rem;
    font-weight: 800;
    color: #FFFFFF;
    background: linear-gradient(135deg, #176B5B 0%, #0F172A 100%);
}

.driver-avatar-camera-btn {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #10B981;
    color: #FFFFFF;
    border: 2.5px solid #0F172A;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    transition: all 0.2s ease;
}

.driver-avatar-camera-btn:hover {
    background: #059669;
    transform: scale(1.1);
}

.driver-hero-info {
    flex: 1;
    min-width: 0;
}

.driver-hero-badge-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}

.driver-verified-badge {
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #34D399;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.driver-duty-status-pill {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 9999px;
    letter-spacing: 0.04em;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.driver-duty-status-pill.status-on_duty {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid #10B981;
    color: #34D399;
}

.driver-duty-status-pill.status-on_trip {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid #3B82F6;
    color: #93C5FD;
}

.driver-duty-status-pill.status-off_duty {
    background: rgba(148, 163, 184, 0.2);
    border: 1px solid #94A3B8;
    color: #E2E8F0;
}

.driver-hero-name {
    font-size: 1.55rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0.15rem 0 0.35rem;
    letter-spacing: -0.02em;
    line-height: 1.25;
    word-break: break-word;
}

.driver-hero-meta-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #CBD5E1;
    margin-bottom: 0.85rem;
}

.driver-hero-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.driver-hero-meta-dot {
    color: rgba(255, 255, 255, 0.3);
}

.driver-hero-code-badge {
    background: rgba(255, 255, 255, 0.15);
    color: #FFFFFF;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.driver-hero-stats-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.hero-stat-pill {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.78rem;
    color: #F8FAFC;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.stat-star-icon {
    color: #FBBF24;
}

.stat-award-icon {
    color: #34D399;
}

/* 3. Mobile Segmented App Tabs */
.driver-app-tabs-wrap {
    display: none;
    margin-bottom: 1.25rem;
    width: 100%;
}

.driver-app-tabs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    background: #F1F5F9;
    padding: 4px;
    border-radius: 14px;
    gap: 4px;
    border: 1px solid #E2E8F0;
    width: 100%;
    box-sizing: border-box;
}

.driver-app-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.65rem 0.5rem;
    background: transparent;
    border: none;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748B;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.driver-app-tab.active {
    background: #FFFFFF;
    color: #0F172A;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

.driver-app-tab.active i {
    color: #176B5B;
}

/* 4. Desktop Two-Column Layout */
.driver-profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
    width: 100%;
    box-sizing: border-box;
}

.driver-profile-col {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    min-width: 0;
}

/* 5. Modern App Cards */
.driver-app-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    box-sizing: border-box;
    width: 100%;
}

.driver-card-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #F1F5F9;
}

.driver-card-header-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.icon-emerald {
    background: #ECFDF5;
    color: #10B981;
}

.icon-blue {
    background: #EFF6FF;
    color: #2563EB;
}

.icon-teal {
    background: #F0FDFA;
    color: #0D9488;
}

.icon-amber {
    background: #FFFBEB;
    color: #D97706;
}

.icon-red {
    background: #FEF2F2;
    color: #EF4444;
}

.driver-card-header-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.driver-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    line-height: 1.25;
}

.driver-card-subtitle {
    font-size: 0.8rem;
    color: #64748B;
    margin: 2px 0 0 0;
}

.driver-card-body {
    padding: 1.5rem;
    box-sizing: border-box;
}

/* 6. Credentials Spec Grid */
.credentials-spec-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.credential-item-card {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 105px;
    box-sizing: border-box;
}

.credential-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.credential-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    line-height: 1.2;
}

.credential-icon-pill {
    width: 26px;
    height: 26px;
    min-width: 26px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
}

.credential-value {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0.4rem 0 0.2rem;
    line-height: 1.25;
}

.credential-mono {
    font-family: monospace;
    letter-spacing: 0.04em;
    word-break: break-all;
}

.star-val {
    color: #D97706;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.star-val small {
    font-size: 0.75rem;
    color: #94A3B8;
    font-weight: 600;
}

.credential-footer {
    margin-top: 0.25rem;
}

.badge-verified-soft {
    font-size: 0.7rem;
    font-weight: 700;
    color: #059669;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.lic-status-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.credential-subtext {
    font-size: 0.72rem;
    color: #94A3B8;
}

/* Employment Sheet */
.employment-sheet {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1.15rem;
    box-sizing: border-box;
}

.employment-sheet-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748B;
    margin-bottom: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.employment-details-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.employment-detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed #E2E8F0;
    font-size: 0.84rem;
}

.employment-detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-label {
    color: #64748B;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.detail-val {
    font-weight: 700;
    color: #0F172A;
}

.detail-val-code {
    font-weight: 800;
    color: #176B5B;
    background: #E8F4F0;
    padding: 2px 8px;
    border-radius: 6px;
    font-family: monospace;
}

/* 7. App Form Inputs & Floating Controls */
.app-form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.15rem;
    width: 100%;
    box-sizing: border-box;
}

.app-form-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
}

.app-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
}

.app-input-icon {
    position: absolute;
    left: 1rem;
    color: #176B5B;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
}

.app-form-input {
    width: 100%;
    height: 48px;
    padding: 0 1rem 0 2.65rem;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 600;
    color: #0F172A;
    transition: all 0.2s ease;
    outline: none;
    box-sizing: border-box;
}

.app-form-input:focus {
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.14);
}

.app-input-readonly {
    background: #F1F5F9;
    color: #64748B;
    cursor: not-allowed;
    border-color: #E2E8F0;
}

.app-input-suffix-badge {
    position: absolute;
    right: 0.85rem;
    background: #E2E8F0;
    color: #64748B;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.app-textarea-wrap {
    align-items: flex-start;
}

.app-textarea-wrap .app-input-icon {
    top: 1rem;
}

.app-textarea {
    height: auto;
    min-height: 84px;
    padding-top: 0.75rem;
    resize: vertical;
}

.app-field-help {
    font-size: 0.74rem;
    color: #64748B;
    margin: 0.35rem 0 0 0.25rem;
    line-height: 1.35;
}

.app-form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.app-pw-toggle-btn {
    position: absolute;
    right: 0.75rem;
    background: transparent;
    border: none;
    color: #94A3B8;
    padding: 6px;
    cursor: pointer;
    font-size: 0.95rem;
    border-radius: 6px;
    transition: color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-pw-toggle-btn:hover {
    color: #0F172A;
}

/* Avatar Management Box */
.app-avatar-action-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    box-sizing: border-box;
}

.app-avatar-action-left {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.app-avatar-box-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: #0F172A;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.app-avatar-box-sub {
    font-size: 0.72rem;
    color: #64748B;
    margin-top: 2px;
}

.app-avatar-action-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Primary App Buttons */
.btn-app-primary {
    width: 100%;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    margin-top: 0.5rem;
    box-sizing: border-box;
}

.btn-app-primary:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
}

.btn-app-primary:active {
    transform: scale(0.98);
}

.btn-app-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0F172A;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-app-secondary:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.btn-app-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #EF4444;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-app-danger:hover {
    background: #FEE2E2;
}

.pw-match-msg {
    font-size: 0.78rem;
    font-weight: 700;
    margin-bottom: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
}

.match-success {
    background: #ECFDF5;
    color: #059669;
}

.match-error {
    background: #FEF2F2;
    color: #EF4444;
}

/* ==========================================================================
   MOBILE RESPONSIVENESS & NATIVE APP POLISH
   ========================================================================== */
@media (max-width: 991px) {
    /* Reveal App Topbar & Segmented Tabs on Mobile & Tablet */
    .driver-app-topbar {
        display: flex;
    }

    .driver-app-tabs-wrap {
        display: block;
    }

    /* Single column layout */
    .driver-profile-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    /* Hero Card Mobile Styling */
    .driver-hero-card {
        padding: 1.35rem 1.15rem;
        border-radius: 18px;
        margin-bottom: 1.15rem;
    }

    .driver-hero-card-inner {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .driver-hero-avatar-wrap {
        margin: 0 auto;
    }

    .driver-hero-badge-row {
        justify-content: center;
    }

    .driver-hero-name {
        font-size: 1.35rem;
    }

    .driver-hero-meta-row {
        justify-content: center;
    }

    .driver-hero-stats-row {
        justify-content: center;
    }

    .hero-stat-pill {
        font-size: 0.74rem;
        padding: 0.3rem 0.65rem;
    }

    /* Enforce 16px font-size to prevent iOS Safari auto-zoom */
    .app-form-input,
    .app-textarea {
        font-size: 16px;
        height: 50px;
    }

    .app-form-grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .credentials-spec-grid {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }

    .driver-card-header {
        padding: 1.15rem 1.15rem;
    }

    .driver-card-body {
        padding: 1.15rem;
    }

    /* Profile panel tab behavior */
    .profile-section-panel {
        display: none;
    }

    .profile-section-panel.tab-visible {
        display: block;
        animation: appFadeIn 0.22s ease-out;
    }
}

@keyframes appFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
    .driver-portal-container {
        padding-bottom: 4.5rem; /* Extra clearance for mobile bottom navbar */
    }

    .driver-hero-card {
        padding: 1.15rem 0.95rem;
    }

    .driver-hero-avatar-wrap {
        width: 86px;
        height: 86px;
        min-width: 86px;
    }

    .driver-hero-avatar {
        width: 86px;
        height: 86px;
    }

    .driver-hero-name {
        font-size: 1.25rem;
    }

    .driver-app-tab {
        padding: 0.55rem 0.3rem;
        font-size: 0.75rem;
        gap: 4px;
    }

    .driver-card-title {
        font-size: 1rem;
    }

    .credential-value {
        font-size: 1.05rem;
    }
}
</style>
