<?php
/**
 * Modern Company Profile & Settings
 * Single-page section-based management with instant live branding updates.
 *
 * Sections:
 * 1. Executive Profile Header
 * 2. Brand & App Identity (Company Logo, Company Name, App Name, Same-as-Company toggle, PWA Icon)
 * 3. Two-Column Grid:
 *    - Contact Information (Owner, Primary Phone, Alt Phone, Email, Website)
 *    - Business & Tax Information (Tagline, GSTIN, PAN, Operational Status)
 * 4. Office Address (Address, City, State, PIN Code)
 * 5. Invoice & Document Branding (Company Logo, Paid Stamp, Signature, Signature + Stamp)
 */

$primaryCompany = $company ?? $primaryCompany ?? null;
$isExisting = !empty($primaryCompany);

// Image URLs from saved profile
$logoUrl = !empty($primaryCompany['logo']) ? storage_url($primaryCompany['logo']) : null;
$appIconUrl = !empty($primaryCompany['app_icon']) ? storage_url($primaryCompany['app_icon']) : null;
$paidStampUrl = !empty($primaryCompany['paid_stamp']) ? storage_url($primaryCompany['paid_stamp']) : null;
$signatureUrl = !empty($primaryCompany['signature']) ? storage_url($primaryCompany['signature']) : null;
$sigStampUrl = !empty($primaryCompany['signature_stamp']) ? storage_url($primaryCompany['signature_stamp']) : null;

// Form values helper with fallback to old input or company record
$val = function(string $key, string $default = '') use ($primaryCompany, $old): string {
    if (isset($old[$key])) {
        return (string)$old[$key];
    }
    return isset($primaryCompany[$key]) ? (string)$primaryCompany[$key] : $default;
};

// Form error display helpers
$fieldError = function(string $key) use ($errors): string {
    if (!empty($errors[$key])) {
        $msg = is_array($errors[$key]) ? $errors[$key][0] : $errors[$key];
        return '<div class="field-error-msg" style="display: block;">' . e($msg) . '</div>';
    }
    return '<div class="field-error-msg" data-field="' . e($key) . '"></div>';
};

$inputClass = function(string $key, string $baseClass = 'form-control') use ($errors): string {
    return $baseClass . (!empty($errors[$key]) ? ' is-invalid' : '');
};

// Brand & App Identity state
$companyNameVal = $val('company_name', '');
$appNameSource = $val('app_name_source', 'company_name');
$isSameAsCompany = ($appNameSource === 'company_name');
$customAppName = $val('app_name', '');
$effectiveAppName = $isSameAsCompany 
    ? (!empty($companyNameVal) ? $companyNameVal : 'Tours & Travels')
    : (!empty($customAppName) ? $customAppName : $companyNameVal);

// PWA App Icon State
$appIconSource = $val('app_icon_source', 'company_logo');
$pwaPreviewUrl = \App\Services\CompanySettingsService::getPwaIconPreviewUrl();
$statusVal = $val('status', 'active');
?>

<div class="company-profile-container">

    <!-- ========================================================================= -->
    <!-- 1. CLEAN COMPANY PROFILE HEADER                                           -->
    <!-- ========================================================================= -->
    <div class="profile-executive-header">
        <div class="profile-header-main">
            <!-- Company Logo / Monogram -->
            <div class="profile-header-avatar">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= e($logoUrl) ?>" alt="Company Logo" class="profile-avatar-img" id="headerLogoImg">
                <?php else: ?>
                    <div class="profile-avatar-monogram" id="headerLogoMonogram">
                        <span><?= strtoupper(substr($companyNameVal ?: 'C', 0, 1)) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Title & Status & Meta -->
            <div class="profile-header-meta">
                <div class="profile-title-row">
                    <h1 class="profile-company-name" id="headerCompanyName">
                        <?= e(!empty($companyNameVal) ? $companyNameVal : __('company.new_company_title', 'Company Profile')) ?>
                    </h1>
                    <span class="profile-status-pill <?= $statusVal === 'active' ? 'is-active' : 'is-inactive' ?>" id="headerStatusBadge">
                        <span class="status-dot"></span>
                        <span id="headerStatusText"><?= ucfirst($statusVal) ?></span>
                    </span>
                </div>

                <div class="profile-tags-row">
                    <div class="profile-tag-item">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>App Name:</span>
                        <strong class="highlight-app-name" data-live-app-name><?= e($effectiveAppName) ?></strong>
                    </div>
                    <?php if (!empty($val('owner_name'))): ?>
                        <div class="profile-tag-item">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>Owner:</span>
                            <strong id="headerOwnerName"><?= e($val('owner_name')) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($val('city'))): ?>
                        <div class="profile-tag-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>Location:</span>
                            <strong><?= e($val('city')) ?><?= !empty($val('state')) ? ', ' . e($val('state')) : '' ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <p class="profile-header-desc">
                    <?= __('company.header_desc', 'Manage your company identity, application branding, contact details and official documents.') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Flash Messages (for direct GET / server-side redirects) -->
    <?php $successMsg = $success ?? \App\Core\Session::getFlash('success'); ?>
    <?php if (!empty($successMsg)): ?>
        <div class="profile-alert alert-success" id="serverFlashSuccess">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= e($successMsg) ?></span>
            <button type="button" class="alert-close-btn" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="profile-alert alert-danger" id="serverFlashError">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong><?= __('common.please_correct_errors', 'Please correct the errors below:') ?></strong>
                <ul style="margin: 0.35rem 0 0 1.2rem; padding: 0;">
                    <?php foreach ($errors as $fieldErrors): ?>
                        <?php foreach ((array)$fieldErrors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button type="button" class="alert-close-btn" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- 2. SECTION 1: BRAND & APP IDENTITY                                        -->
    <!-- ========================================================================= -->
    <div class="profile-section-card" id="card_brand_identity">
        <form action="<?= url('/office/company/save') ?>" method="POST" enctype="multipart/form-data" class="company-section-form" data-section="brand_identity" data-no-ajax="true" data-native="true" novalidate>
            <?= csrf_field() ?>
            <?php if ($isExisting): ?>
                <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
            <?php endif; ?>
            <input type="hidden" name="section" value="brand_identity">
            <input type="hidden" name="native_submit" value="1">
            <input type="hidden" name="remove_logo" id="flag_remove_logo" value="0">
            <input type="hidden" name="remove_app_icon" id="flag_remove_app_icon" value="0">

            <div class="profile-section-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                        <i class="fa-solid fa-palette"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Brand &amp; App Identity</h2>
                        <p class="section-subtitle">Company logo, application name, and progressive web app (PWA) installation icons.</p>
                    </div>
                </div>
            </div>

            <div class="profile-section-body">
                <!-- Top Row: Logo (Left) | Company Name & App Name Grid (Right) -->
                <div class="brand-identity-top">
                    <!-- Left: Company Logo Box -->
                    <div class="brand-logo-panel">
                        <label class="control-label font-bold">
                            Company Logo
                        </label>
                        <div class="avatar-preview-container" onclick="document.getElementById('logo_input').click()" title="<?= e(__('tooltip.click_to_upload_company_logo', 'Click to upload company logo')) ?>">
                            <input type="file" id="logo_input" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleLogoChange(this)" onclick="event.stopPropagation()">
                            <div class="avatar-icon-placeholder" id="logoPlaceholder" style="<?= !empty($logoUrl) ? 'display: none;' : 'display: flex;' ?>">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <img id="logoPreviewImg" src="<?= !empty($logoUrl) ? e($logoUrl) : '' ?>" alt="Logo Preview" class="avatar-image-preview" style="<?= !empty($logoUrl) ? 'display: block;' : 'display: none;' ?>">
                            <div class="avatar-overlay-badge"><i class="fa-solid fa-camera"></i></div>
                            <span id="logoRemovedPill" class="media-removed-pill" style="display: none;">REMOVED</span>
                        </div>
                        <div class="media-action-buttons">
                            <button type="button" class="btn-media-action" onclick="document.getElementById('logo_input').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                <span id="logoUploadBtnText"><?= !empty($logoUrl) ? 'Change Logo' : 'Upload Logo' ?></span>
                            </button>
                            <button type="button" id="btnRemoveLogo" class="btn-media-remove" onclick="removeLogoMedia()" style="<?= empty($logoUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                            <button type="button" id="btnUndoLogo" class="btn-media-undo" onclick="undoLogoRemoval('<?= e($logoUrl ?? '') ?>')" style="display: none;">
                                <i class="fa-solid fa-rotate-left"></i> Undo
                            </button>
                        </div>
                        <p class="media-helper-note">Square or rectangular logo (PNG, JPG, WebP up to 5MB). Transparent background recommended.</p>
                        <?= $fieldError('logo') ?>
                    </div>

                    <!-- Right: Company Name & App Name 2-Column Grid -->
                    <div class="brand-names-panel">
                        <!-- Company Name Column -->
                        <div class="form-group">
                            <label for="company_name_input" class="control-label font-bold">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="company_name_input" name="company_name" value="<?= e($companyNameVal) ?>" class="<?= $inputClass('company_name', 'form-control form-control-lg font-bold') ?>" required placeholder="<?= e(__('placeholder.e_g_royal_tours_amp_travels_pv', 'e.g. Royal Tours &amp; Travels Pvt. Ltd.')) ?>" oninput="onCompanyNameChanged(this.value)">
                            <p class="form-helper-text">Official legal or trade business name used across documents, invoices, and contracts.</p>
                            <?= $fieldError('company_name') ?>

                            <!-- Use Company Name as App Name Toggle -->
                            <div class="app-name-toggle-card">
                                <label class="switch-container">
                                    <input type="checkbox" id="chk_same_as_company" <?= $isSameAsCompany ? 'checked' : '' ?> onchange="onSameAsCompanyToggle(this.checked)">
                                    <span class="switch-slider"></span>
                                </label>
                                <div class="toggle-text-wrap">
                                    <label for="chk_same_as_company" class="toggle-main-label">Use Company Name as App Name</label>
                                    <span class="toggle-sub-label">Automatically keep your installed application name in sync with your Company Name</span>
                                </div>
                            </div>
                            <input type="hidden" name="app_name_source" id="app_name_source_input" value="<?= e($appNameSource) ?>">
                        </div>

                        <!-- App Name Column -->
                        <div class="form-group">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem; min-height: 24px;">
                                <label for="app_name_input" class="control-label font-bold" style="margin-bottom: 0;">
                                    App Name <span class="text-danger">*</span>
                                </label>
                                <span class="badge-derived" id="badgeDerivedAppName" style="<?= $isSameAsCompany ? 'display: inline-flex;' : 'display: none;' ?>">
                                    <i class="fa-solid fa-link"></i> Synchronized with Company Name
                                </span>
                            </div>
                            <input type="text" id="app_name_input" name="app_name" value="<?= e($isSameAsCompany ? $companyNameVal : $customAppName) ?>" class="<?= $inputClass('app_name', 'form-control form-control-lg' . ($isSameAsCompany ? ' is-derived' : '')) ?>" <?= $isSameAsCompany ? 'readonly' : '' ?> required placeholder="<?= e(__('placeholder.e_g_royal_tours', 'e.g. Royal Tours')) ?>">
                            <p class="form-helper-text">
                                The concise name displayed under your app icon on mobile home screens, desktop taskbars, and browser installation prompts.
                            </p>
                            <?= $fieldError('app_name') ?>
                        </div>
                    </div>
                </div>

                <!-- Subtle Section Divider -->
                <div class="brand-section-divider"></div>

                <!-- Bottom Row: PWA App Icon Configuration -->
                <div class="brand-pwa-panel">
                    <div style="margin-bottom: 0.25rem;">
                        <label class="control-label font-bold" style="font-size: 0.95rem; margin-bottom: 0.15rem;">
                            PWA App Icon
                        </label>
                        <p class="section-subtitle">Choose whether your progressive web app uses your existing company logo or a custom dedicated app icon.</p>
                    </div>

                    <div class="brand-pwa-grid">
                        <!-- Left: Radio Cards -->
                        <div class="pwa-source-choice-group">
                            <label class="pwa-radio-card <?= $appIconSource === 'company_logo' ? 'is-selected' : '' ?>" id="labelSourceLogo">
                                <input type="radio" name="app_icon_source" value="company_logo" id="sourceCompanyLogo" <?= $appIconSource === 'company_logo' ? 'checked' : '' ?> onchange="onAppIconSourceToggle('company_logo')">
                                <div class="radio-card-content">
                                    <span class="radio-card-title"><i class="fa-solid fa-building"></i> Use Company Logo</span>
                                    <span class="radio-card-desc">Auto-pads into clean 192×192 &amp; 512×512 icons without distortion</span>
                                </div>
                            </label>

                            <label class="pwa-radio-card <?= $appIconSource === 'custom' ? 'is-selected' : '' ?>" id="labelSourceCustom">
                                <input type="radio" name="app_icon_source" value="custom" id="sourceCustom" <?= $appIconSource === 'custom' ? 'checked' : '' ?> onchange="onAppIconSourceToggle('custom')">
                                <div class="radio-card-content">
                                    <span class="radio-card-title"><i class="fa-solid fa-shapes"></i> Use Different App Icon</span>
                                    <span class="radio-card-desc">Upload a dedicated square 512×512 icon for mobile &amp; desktop</span>
                                </div>
                            </label>
                        </div>

                        <!-- Right: PWA Icon Preview Box & Controls -->
                        <div class="pwa-preview-row">
                            <div class="pwa-icon-preview-box" id="pwaIconDropZone" onclick="if(document.getElementById('sourceCustom').checked){document.getElementById('app_icon_input').click();}" title="<?= e(__('tooltip.active_pwa_icon_preview', 'Active PWA Icon Preview')) ?>">
                                <input type="file" id="app_icon_input" name="app_icon" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleAppIconChange(this)" onclick="event.stopPropagation()">
                                <img id="pwaPreviewImg" src="<?= e($pwaPreviewUrl) ?>" alt="PWA Icon Preview" class="pwa-preview-img">
                                <span id="appIconRemovedPill" class="media-removed-pill" style="display: none;">RESET</span>
                            </div>
                            <div class="pwa-preview-meta">
                                <span class="pwa-active-badge" id="pwaActiveSourceBadge">
                                    <?= $appIconSource === 'custom' ? 'Custom App Icon' : 'Generated from Company Logo' ?>
                                </span>
                                <div class="pwa-meta-controls" id="customIconControls" style="<?= $appIconSource === 'custom' ? 'display: flex;' : 'display: none;' ?>">
                                    <button type="button" class="btn-media-action" onclick="document.getElementById('app_icon_input').click()">
                                        <i class="fa-solid fa-upload"></i> <span id="appIconUploadBtnText"><?= !empty($appIconUrl) ? 'Change Icon' : 'Upload Icon' ?></span>
                                    </button>
                                    <button type="button" id="btnRemoveAppIcon" class="btn-media-remove" onclick="removeAppIconMedia()" style="<?= empty($appIconUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                        <i class="fa-solid fa-trash-can"></i> Revert
                                    </button>
                                    <button type="button" id="btnUndoAppIcon" class="btn-media-undo" onclick="undoAppIconRemoval('<?= e($appIconUrl ?? '') ?>')" style="display: none;">
                                        <i class="fa-solid fa-rotate-left"></i> Undo
                                    </button>
                                </div>
                                <p class="media-helper-note" style="margin-top: 0.35rem;">
                                    Applied dynamically to web manifest, home screen shortcuts, and native install prompts.
                                </p>
                                <?= $fieldError('app_icon') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-section-footer">
                <button type="submit" class="btn btn-primary btn-save-section" data-no-ajax="true">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. TWO-COLUMN RESPONSIVE GRID: CONTACT INFO & BUSINESS TAX                -->
    <!-- ========================================================================= -->
    <div class="profile-two-col-grid">

        <!-- Card 2: Contact Information -->
        <div class="profile-section-card" id="card_contact_info">
            <form action="<?= url('/office/company/save') ?>" method="POST" class="company-section-form" data-section="contact_info" data-no-ajax="true" data-native="true" novalidate>
                <?= csrf_field() ?>
                <?php if ($isExisting): ?>
                    <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
                <?php endif; ?>
                <input type="hidden" name="section" value="contact_info">
                <input type="hidden" name="native_submit" value="1">

                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Contact Information</h2>
                            <p class="section-subtitle">Primary mobile, emergency contact numbers, email address and official website.</p>
                        </div>
                    </div>
                </div>

                <div class="profile-section-body">
                    <div class="two-column-form-grid">
                        <div class="form-group">
                            <label for="owner_name_input" class="control-label font-bold">Owner / Contact Person</label>
                            <input type="text" id="owner_name_input" name="owner_name" value="<?= e($val('owner_name')) ?>" class="<?= $inputClass('owner_name') ?>" placeholder="<?= e(__('placeholder.e_g_farooq_ahmad_director', 'e.g. Farooq Ahmad / Director')) ?>">
                            <?= $fieldError('owner_name') ?>
                        </div>

                        <div class="form-group">
                            <label for="phone_input" class="control-label font-bold">Primary Mobile <span class="text-danger">*</span></label>
                            <input type="tel" id="phone_input" name="phone" value="<?= e($val('phone')) ?>" class="<?= $inputClass('phone', 'form-control font-bold') ?>" required placeholder="<?= e(__('placeholder.e_g_91_98765_43210', 'e.g. +91 98765 43210')) ?>">
                            <?= $fieldError('phone') ?>
                        </div>

                        <div class="form-group">
                            <label for="alt_phone_input" class="control-label font-bold">Alternate Mobile</label>
                            <input type="tel" id="alt_phone_input" name="alternate_phone" value="<?= e($val('alternate_phone')) ?>" class="<?= $inputClass('alternate_phone') ?>" placeholder="<?= e(__('placeholder.e_g_91_98765_00000', 'e.g. +91 98765 00000')) ?>">
                            <?= $fieldError('alternate_phone') ?>
                        </div>

                        <div class="form-group">
                            <label for="email_input" class="control-label font-bold">Official Email</label>
                            <input type="email" id="email_input" name="email" value="<?= e($val('email')) ?>" class="<?= $inputClass('email') ?>" placeholder="<?= e(__('placeholder.e_g_concierge_royaltours_com', 'e.g. concierge@royaltours.com')) ?>">
                            <?= $fieldError('email') ?>
                        </div>

                        <div class="form-group full-width-span">
                            <label for="website_input" class="control-label font-bold">Company Website</label>
                            <input type="url" id="website_input" name="website" value="<?= e($val('website')) ?>" class="<?= $inputClass('website') ?>" placeholder="<?= e(__('placeholder.e_g_https_www_royaltours_com', 'e.g. https://www.royaltours.com')) ?>">
                            <?= $fieldError('website') ?>
                        </div>
                    </div>
                </div>

                <div class="profile-section-footer">
                    <button type="submit" class="btn btn-primary btn-save-section" data-no-ajax="true">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 4: Business & Tax Information -->
        <div class="profile-section-card" id="card_business_tax">
            <form action="<?= url('/office/company/save') ?>" method="POST" class="company-section-form" data-section="business_tax" data-no-ajax="true" data-native="true" novalidate>
                <?= csrf_field() ?>
                <?php if ($isExisting): ?>
                    <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
                <?php endif; ?>
                <input type="hidden" name="section" value="business_tax">
                <input type="hidden" name="native_submit" value="1">

                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Business &amp; Tax Information</h2>
                            <p class="section-subtitle">Tax registration identification numbers, business motto, and operational profile state.</p>
                        </div>
                    </div>
                </div>

                <div class="profile-section-body">
                    <div class="two-column-form-grid">
                        <div class="form-group full-width-span">
                            <label for="tagline_input" class="control-label font-bold">Company Tagline</label>
                            <input type="text" id="tagline_input" name="tagline" value="<?= e($val('tagline')) ?>" class="<?= $inputClass('tagline') ?>" placeholder="<?= e(__('placeholder.e_g_luxury_journeys_exceptiona', 'e.g. Luxury Journeys, Exceptional Memories')) ?>">
                            <p class="form-helper-text">Short slogan shown on letterheads and email headers.</p>
                            <?= $fieldError('tagline') ?>
                        </div>

                        <div class="form-group">
                            <label for="gst_number_input" class="control-label font-bold">GST Number (GSTIN)</label>
                            <input type="text" id="gst_number_input" name="gst_number" value="<?= e($val('gst_number')) ?>" class="<?= $inputClass('gst_number', 'form-control font-mono uppercase') ?>" maxlength="15" placeholder="<?= e(__('placeholder.e_g_01abcde1234f1z5', 'e.g. 01ABCDE1234F1Z5')) ?>">
                            <p class="form-helper-text">15-digit Goods and Services Tax Identification Number.</p>
                            <?= $fieldError('gst_number') ?>
                        </div>

                        <div class="form-group">
                            <label for="pan_number_input" class="control-label font-bold">PAN Number</label>
                            <input type="text" id="pan_number_input" name="pan_number" value="<?= e($val('pan_number')) ?>" class="<?= $inputClass('pan_number', 'form-control font-mono uppercase') ?>" maxlength="10" placeholder="<?= e(__('placeholder.e_g_abcde1234f', 'e.g. ABCDE1234F')) ?>">
                            <p class="form-helper-text">10-character Permanent Account Number.</p>
                            <?= $fieldError('pan_number') ?>
                        </div>

                        <div class="form-group full-width-span">
                            <label for="status_select" class="control-label font-bold">Company Status</label>
                            <select id="status_select" name="status" class="<?= $inputClass('status', 'form-select form-control') ?>">
                                <option value="active" <?= $statusVal === 'active' ? 'selected' : '' ?>>Active (Company profile is active and live)</option>
                                <option value="inactive" <?= $statusVal === 'inactive' ? 'selected' : '' ?>>Inactive (Suspended / Under Maintenance)</option>
                            </select>
                            <?= $fieldError('status') ?>
                        </div>
                    </div>
                </div>

                <div class="profile-section-footer">
                    <button type="submit" class="btn btn-primary btn-save-section" data-no-ajax="true">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. SECTION 3: OFFICE ADDRESS                                              -->
    <!-- ========================================================================= -->
    <div class="profile-section-card" id="card_office_address">
        <form action="<?= url('/office/company/save') ?>" method="POST" class="company-section-form" data-section="office_address" data-no-ajax="true" data-native="true" novalidate>
            <?= csrf_field() ?>
            <?php if ($isExisting): ?>
                <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
            <?php endif; ?>
            <input type="hidden" name="section" value="office_address">
            <input type="hidden" name="native_submit" value="1">

            <div class="profile-section-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Office Address</h2>
                        <p class="section-subtitle">Registered commercial headquarters printed on invoices, duty slips, and customer vouchers.</p>
                    </div>
                </div>
            </div>

            <div class="profile-section-body">
                <div class="form-group">
                    <label for="address_input" class="control-label font-bold">Office Address <span class="text-danger">*</span></label>
                    <textarea id="address_input" name="address" rows="2" class="<?= $inputClass('address') ?>" required placeholder="<?= e(__('placeholder.suite_office_no_building_name', 'Suite / Office No, Building Name, Street, Landmark')) ?>"><?= e($val('address')) ?></textarea>
                    <?= $fieldError('address') ?>
                </div>

                <div class="three-column-form-grid" style="margin-top: 1rem;">
                    <div class="form-group">
                        <label for="city_input" class="control-label font-bold">City <span class="text-danger">*</span></label>
                        <input type="text" id="city_input" name="city" value="<?= e($val('city')) ?>" class="<?= $inputClass('city') ?>" required placeholder="<?= e(__('placeholder.e_g_srinagar', 'e.g. Srinagar')) ?>">
                        <?= $fieldError('city') ?>
                    </div>

                    <div class="form-group">
                        <label for="state_input" class="control-label font-bold">State / UT</label>
                        <input type="text" id="state_input" name="state" value="<?= e($val('state')) ?>" class="<?= $inputClass('state') ?>" placeholder="<?= e(__('placeholder.e_g_jammu_and_kashmir', 'e.g. Jammu and Kashmir')) ?>">
                        <?= $fieldError('state') ?>
                    </div>

                    <div class="form-group">
                        <label for="pin_code_input" class="control-label font-bold">PIN Code <span class="text-danger">*</span></label>
                        <input type="text" id="pin_code_input" name="pin_code" value="<?= e($val('pin_code')) ?>" class="<?= $inputClass('pin_code', 'form-control font-mono font-bold') ?>" maxlength="6" required placeholder="<?= e(__('placeholder.e_g_190001', 'e.g. 190001')) ?>">
                        <?= $fieldError('pin_code') ?>
                    </div>
                </div>
            </div>

            <div class="profile-section-footer">
                <button type="submit" class="btn btn-primary btn-save-section" data-no-ajax="true">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. SECTION 5: INVOICE & DOCUMENT BRANDING                                 -->
    <!-- ========================================================================= -->
    <div class="profile-section-card" id="card_documents">
        <form action="<?= url('/office/company/save') ?>" method="POST" enctype="multipart/form-data" class="company-section-form" data-section="documents" data-no-ajax="true" data-native="true" novalidate>
            <?= csrf_field() ?>
            <?php if ($isExisting): ?>
                <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
            <?php endif; ?>
            <input type="hidden" name="section" value="documents">
            <input type="hidden" name="native_submit" value="1">
            <input type="hidden" name="remove_logo" id="flag_doc_remove_logo" value="0">
            <input type="hidden" name="remove_paid_stamp" id="flag_remove_paid_stamp" value="0">
            <input type="hidden" name="remove_signature" id="flag_remove_signature" value="0">
            <input type="hidden" name="remove_signature_stamp" id="flag_remove_signature_stamp" value="0">

            <div class="profile-section-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Invoice &amp; Document Branding</h2>
                        <p class="section-subtitle">Official seals, digital signatures, and stamps automatically rendered on billing invoices and payment receipts.</p>
                    </div>
                </div>
            </div>

            <div class="profile-section-body">
                <div class="doc-cards-grid">
                    <!-- Doc 1: Company Logo -->
                    <div class="doc-item-card">
                        <div class="doc-item-head">
                            <span class="doc-item-name"><i class="fa-solid fa-building"></i> Company Logo</span>
                            <span id="badgeDocLogo" class="doc-status-badge <?= !empty($logoUrl) ? 'attached' : 'empty' ?>">
                                <?= !empty($logoUrl) ? '<i class="fa-solid fa-check"></i> Attached' : '<i class="fa-solid fa-circle-info"></i> Optional' ?>
                            </span>
                        </div>
                        <div class="doc-item-thumb" onclick="document.getElementById('doc_logo_input').click()" title="<?= e(__('tooltip.click_to_upload_invoice_logo', 'Click to upload invoice logo')) ?>">
                            <input type="file" id="doc_logo_input" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocChange(this, 'logo')" onclick="event.stopPropagation()">
                            <img id="docLogoPreview" src="<?= !empty($logoUrl) ? e($logoUrl) : '' ?>" alt="Invoice Logo" style="<?= !empty($logoUrl) ? 'display: block;' : 'display: none;' ?>">
                            <div id="docLogoPlaceholder" class="doc-placeholder-icon" style="<?= !empty($logoUrl) ? 'display: none;' : 'display: flex;' ?>">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload Logo</span>
                            </div>
                            <span id="docLogoRemovedPill" class="media-removed-pill" style="display: none;">REMOVED</span>
                        </div>
                        <div class="doc-item-actions">
                            <button type="button" class="btn-doc-upload" onclick="document.getElementById('doc_logo_input').click()">
                                <i class="fa-solid fa-upload"></i> <span id="docLogoBtnText"><?= !empty($logoUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="btnRemoveDocLogo" class="btn-doc-remove" onclick="removeDocumentImage('logo')" style="<?= empty($logoUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="btnUndoDocLogo" class="btn-doc-undo" onclick="undoDocumentImage('logo', '<?= e($logoUrl ?? '') ?>')" style="display: none;">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                        <?= $fieldError('logo') ?>
                    </div>

                    <!-- Doc 2: Paid Stamp -->
                    <div class="doc-item-card">
                        <div class="doc-item-head">
                            <span class="doc-item-name"><i class="fa-solid fa-stamp"></i> Paid Stamp</span>
                            <span id="badgeDocPaidStamp" class="doc-status-badge <?= !empty($paidStampUrl) ? 'attached' : 'empty' ?>">
                                <?= !empty($paidStampUrl) ? '<i class="fa-solid fa-check"></i> Attached' : '<i class="fa-solid fa-circle-info"></i> Optional' ?>
                            </span>
                        </div>
                        <div class="doc-item-thumb" onclick="document.getElementById('paid_stamp_input').click()" title="<?= e(__('tooltip.click_to_upload_paid_stamp', 'Click to upload paid stamp')) ?>">
                            <input type="file" id="paid_stamp_input" name="paid_stamp" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocChange(this, 'paid_stamp')" onclick="event.stopPropagation()">
                            <img id="docPaidStampPreview" src="<?= !empty($paidStampUrl) ? e($paidStampUrl) : '' ?>" alt="Paid Stamp" style="<?= !empty($paidStampUrl) ? 'display: block;' : 'display: none;' ?>">
                            <div id="docPaidStampPlaceholder" class="doc-placeholder-icon" style="<?= !empty($paidStampUrl) ? 'display: none;' : 'display: flex;' ?>">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Upload Stamp</span>
                            </div>
                            <span id="docPaidStampRemovedPill" class="media-removed-pill" style="display: none;">REMOVED</span>
                        </div>
                        <div class="doc-item-actions">
                            <button type="button" class="btn-doc-upload" onclick="document.getElementById('paid_stamp_input').click()">
                                <i class="fa-solid fa-upload"></i> <span id="docPaidStampBtnText"><?= !empty($paidStampUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="btnRemoveDocPaidStamp" class="btn-doc-remove" onclick="removeDocumentImage('paid_stamp')" style="<?= empty($paidStampUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="btnUndoDocPaidStamp" class="btn-doc-undo" onclick="undoDocumentImage('paid_stamp', '<?= e($paidStampUrl ?? '') ?>')" style="display: none;">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                        <?= $fieldError('paid_stamp') ?>
                    </div>

                    <!-- Doc 3: Authorized Signature -->
                    <div class="doc-item-card">
                        <div class="doc-item-head">
                            <span class="doc-item-name"><i class="fa-solid fa-signature"></i> Authorized Signature</span>
                            <span id="badgeDocSignature" class="doc-status-badge <?= !empty($signatureUrl) ? 'attached' : 'empty' ?>">
                                <?= !empty($signatureUrl) ? '<i class="fa-solid fa-check"></i> Attached' : '<i class="fa-solid fa-circle-info"></i> Optional' ?>
                            </span>
                        </div>
                        <div class="doc-item-thumb" onclick="document.getElementById('signature_input').click()" title="<?= e(__('tooltip.click_to_upload_digital_signat', 'Click to upload digital signature')) ?>">
                            <input type="file" id="signature_input" name="signature" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocChange(this, 'signature')" onclick="event.stopPropagation()">
                            <img id="docSignaturePreview" src="<?= !empty($signatureUrl) ? e($signatureUrl) : '' ?>" alt="Signature" style="<?= !empty($signatureUrl) ? 'display: block;' : 'display: none;' ?>">
                            <div id="docSignaturePlaceholder" class="doc-placeholder-icon" style="<?= !empty($signatureUrl) ? 'display: none;' : 'display: flex;' ?>">
                                <i class="fa-solid fa-pen-nib"></i>
                                <span>Upload Signature</span>
                            </div>
                            <span id="docSignatureRemovedPill" class="media-removed-pill" style="display: none;">REMOVED</span>
                        </div>
                        <div class="doc-item-actions">
                            <button type="button" class="btn-doc-upload" onclick="document.getElementById('signature_input').click()">
                                <i class="fa-solid fa-upload"></i> <span id="docSignatureBtnText"><?= !empty($signatureUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="btnRemoveDocSignature" class="btn-doc-remove" onclick="removeDocumentImage('signature')" style="<?= empty($signatureUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="btnUndoDocSignature" class="btn-doc-undo" onclick="undoDocumentImage('signature', '<?= e($signatureUrl ?? '') ?>')" style="display: none;">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                        <?= $fieldError('signature') ?>
                    </div>

                    <!-- Doc 4: Signature + Stamp -->
                    <div class="doc-item-card">
                        <div class="doc-item-head">
                            <span class="doc-item-name"><i class="fa-solid fa-shield-heart"></i> Signature + Stamp</span>
                            <span id="badgeDocSigStamp" class="doc-status-badge <?= !empty($sigStampUrl) ? 'attached' : 'empty' ?>">
                                <?= !empty($sigStampUrl) ? '<i class="fa-solid fa-check"></i> Attached' : '<i class="fa-solid fa-circle-info"></i> Optional' ?>
                            </span>
                        </div>
                        <div class="doc-item-thumb" onclick="document.getElementById('sig_stamp_input').click()" title="<?= e(__('tooltip.click_to_upload_combined_signa', 'Click to upload combined signature & seal')) ?>">
                            <input type="file" id="sig_stamp_input" name="signature_stamp" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleDocChange(this, 'signature_stamp')" onclick="event.stopPropagation()">
                            <img id="docSigStampPreview" src="<?= !empty($sigStampUrl) ? e($sigStampUrl) : '' ?>" alt="Signature and Stamp" style="<?= !empty($sigStampUrl) ? 'display: block;' : 'display: none;' ?>">
                            <div id="docSigStampPlaceholder" class="doc-placeholder-icon" style="<?= !empty($sigStampUrl) ? 'display: none;' : 'display: flex;' ?>">
                                <i class="fa-solid fa-file-contract"></i>
                                <span>Upload Seal + Sign</span>
                            </div>
                            <span id="docSigStampRemovedPill" class="media-removed-pill" style="display: none;">REMOVED</span>
                        </div>
                        <div class="doc-item-actions">
                            <button type="button" class="btn-doc-upload" onclick="document.getElementById('sig_stamp_input').click()">
                                <i class="fa-solid fa-upload"></i> <span id="docSigStampBtnText"><?= !empty($sigStampUrl) ? 'Replace' : 'Upload' ?></span>
                            </button>
                            <button type="button" id="btnRemoveDocSigStamp" class="btn-doc-remove" onclick="removeDocumentImage('signature_stamp')" style="<?= empty($sigStampUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <button type="button" id="btnUndoDocSigStamp" class="btn-doc-undo" onclick="undoDocumentImage('signature_stamp', '<?= e($sigStampUrl ?? '') ?>')" style="display: none;">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                        <?= $fieldError('signature_stamp') ?>
                    </div>
                </div>
            </div>

            <div class="profile-section-footer">
                <button type="submit" class="btn btn-primary btn-save-section" data-no-ajax="true">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- ========================================================================= -->
<!-- PROFESSIONAL RESPONSIVE STYLES (MATCHES OFFICE DESIGN SYSTEM)             -->
<!-- ========================================================================= -->
<style>
/* Main Page Container: Uses full available width of main-content */
.company-profile-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0 0 3rem 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    box-sizing: border-box;
}

/* 1. Profile Executive Header */
.profile-executive-header {
    background: linear-gradient(135deg, #176B5B 0%, #0F4D41 100%);
    border-radius: 20px;
    padding: 1.5rem 2rem;
    color: #FFFFFF;
    box-shadow: 0 8px 24px rgba(23, 107, 91, 0.18);
    box-sizing: border-box;
    width: 100%;
}

.profile-header-main {
    display: flex;
    align-items: center;
    gap: 1.75rem;
}

.profile-header-avatar {
    width: 84px;
    height: 84px;
    border-radius: 18px;
    background: #FFFFFF;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.profile-avatar-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 12px;
}

.profile-avatar-monogram {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    background: linear-gradient(135deg, #16443B 0%, #0C2B52 100%);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
}

.profile-header-meta {
    flex: 1;
    min-width: 0;
}

.profile-title-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
    margin-bottom: 0.4rem;
}

.profile-company-name {
    font-size: 1.6rem;
    font-weight: 800;
    margin: 0;
    color: #FFFFFF;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

.profile-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.profile-status-pill.is-active {
    background: rgba(16, 185, 129, 0.2);
    color: #A7F3D0;
    border: 1px solid rgba(16, 185, 129, 0.4);
}

.profile-status-pill.is-inactive {
    background: rgba(239, 68, 68, 0.2);
    color: #FECACA;
    border: 1px solid rgba(239, 68, 68, 0.4);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.profile-tags-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 0.4rem;
}

.profile-tag-item {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.highlight-app-name {
    color: #D6B77A;
    font-weight: 700;
}

.profile-header-desc {
    margin: 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
}

/* Alerts */
.profile-alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    font-size: 0.92rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.profile-alert.alert-success {
    background: #ECFDF5;
    border: 1.5px solid #A7F3D0;
    color: #065F46;
}

.profile-alert.alert-danger {
    background: #FEF2F2;
    border: 1.5px solid #FECACA;
    color: #991B1B;
}

.alert-close-btn {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 1.35rem;
    cursor: pointer;
    color: inherit;
    line-height: 1;
    opacity: 0.6;
}

.alert-close-btn:hover {
    opacity: 1;
}

/* 2. Section Cards */
.profile-section-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    width: 100%;
    box-sizing: border-box;
}

.profile-section-card:hover {
    border-color: #CBD5E1;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
}

.profile-section-header {
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid #F1F5F9;
    background: #FAFCFE;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-icon-badge {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.section-title {
    font-size: 1.12rem;
    font-weight: 700;
    color: #0C2B52;
    margin: 0;
    line-height: 1.3;
}

.section-subtitle {
    font-size: 0.82rem;
    color: #64748B;
    margin: 2px 0 0 0;
}

.profile-section-body {
    padding: 1.5rem;
}

.profile-section-footer {
    padding: 0.9rem 1.5rem;
    background: #F8FAFD;
    border-top: 1px solid #F1F5F9;
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.btn-save-section {
    background: #176B5B;
    border: 1px solid #176B5B;
    color: #FFFFFF;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 0.6rem 1.5rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.2);
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-save-section:hover {
    background: #12584A;
    border-color: #12584A;
    color: #FFFFFF;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.3);
}

.btn-save-section:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Card 1: Brand & App Identity Structure */
.brand-identity-top {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 2rem;
    align-items: start;
}

.brand-logo-panel {
    display: flex;
    flex-direction: column;
}

.brand-names-panel {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

.brand-section-divider {
    border: none;
    border-top: 1px solid #E2E8F0;
    margin: 1.75rem 0 1.5rem 0;
}

.brand-pwa-panel {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.brand-pwa-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* Two-Column Responsive Section Grid (Contact Info + Business Tax) */
.profile-two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.75rem;
    align-items: stretch;
}

.profile-two-col-grid .profile-section-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.profile-two-col-grid .profile-section-card form {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.profile-two-col-grid .profile-section-body {
    flex: 1;
}

/* Generic Form Grids */
.two-column-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.three-column-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 160px;
    gap: 1.25rem;
}

.full-width-span {
    grid-column: 1 / -1;
}

/* Form Controls */
.control-label {
    display: block;
    font-size: 0.85rem;
    color: #1E293B;
    margin-bottom: 0.35rem;
}

.form-control {
    border: 1.5px solid #CBD5E1;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    font-size: 0.92rem;
    color: #0F172A;
    width: 100%;
    transition: all 0.2s ease;
    background: #FFFFFF;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: #176B5B;
    box-shadow: 0 0 0 3.5px rgba(23, 107, 91, 0.15);
    outline: none;
}

.form-control.is-derived {
    background: #F1F5F9;
    color: #475569;
    cursor: not-allowed;
    border-color: #CBD5E1;
}

.form-control.is-invalid {
    border-color: #EF4444 !important;
    background-color: #FEF2F2;
}

.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-helper-text {
    font-size: 0.78rem;
    color: #64748B;
    margin: 0.35rem 0 0 0;
}

.field-error-msg {
    color: #EF4444;
    font-size: 0.78rem;
    font-weight: 600;
    margin-top: 0.3rem;
    display: none;
}

/* Toggle Switch */
.app-name-toggle-card {
    background: #F8FAFD;
    border: 1.5px dashed #CBD5E1;
    border-radius: 12px;
    padding: 0.75rem 0.9rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.switch-container {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
    margin: 0;
}

.switch-container input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #CBD5E1;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 34px;
}

.switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .switch-slider {
    background-color: #176B5B;
}

input:checked + .switch-slider:before {
    transform: translateX(20px);
}

.toggle-text-wrap {
    display: flex;
    flex-direction: column;
}

.toggle-main-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0C2B52;
    cursor: pointer;
    margin: 0;
}

.toggle-sub-label {
    font-size: 0.73rem;
    color: #64748B;
}

.badge-derived {
    background: #ECFDF5;
    color: #065F46;
    border: 1px solid #A7F3D0;
    border-radius: 50px;
    padding: 2px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* Media Upload Box (Logo) */
.brand-logo-panel {
    background: #FFFFFF;
}

.avatar-preview-container {
    width: 100%;
    height: 110px;
    background: #F8FAFC;
    border: 2px dashed #CBD5E1;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
}

.avatar-preview-container:hover {
    border-color: #176B5B;
    background: #F0FDF4;
}

.avatar-image-preview {
    max-width: 85%;
    max-height: 85%;
    object-fit: contain;
}

.avatar-icon-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    color: #94A3B8;
}

.avatar-overlay-badge {
    position: absolute;
    bottom: 6px;
    right: 6px;
    background: #176B5B;
    color: #FFF;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
}

.media-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.6rem;
}

.btn-media-action {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.15s ease;
}

.btn-media-action:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.btn-media-remove {
    background: #FEF2F2;
    border: 1.5px solid #FECACA;
    color: #DC2626;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.35rem 0.7rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.btn-media-remove:hover {
    background: #FEE2E2;
}

.btn-media-undo {
    background: #EFF6FF;
    border: 1.5px solid #BFDBFE;
    color: #2563EB;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.35rem 0.7rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.media-helper-note {
    font-size: 0.73rem;
    color: #64748B;
    margin: 0.4rem 0 0 0;
    line-height: 1.35;
}

.media-removed-pill {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #EF4444;
    color: #FFFFFF;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
}

/* PWA App Icon Controls */
.pwa-source-choice-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pwa-radio-card {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    background: #F8FAFD;
    border: 1.5px solid #E2E8F0;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pwa-radio-card.is-selected {
    background: #F0FDF4;
    border-color: #176B5B;
    box-shadow: 0 0 0 2px rgba(23, 107, 91, 0.1);
}

.pwa-radio-card input {
    margin-top: 3px;
}

.radio-card-content {
    display: flex;
    flex-direction: column;
}

.radio-card-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0C2B52;
}

.radio-card-desc {
    font-size: 0.72rem;
    color: #64748B;
}

.pwa-preview-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #F8FAFD;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.75rem;
}

.pwa-icon-preview-box {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    background: #0B192C;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.pwa-preview-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 4px;
}

.pwa-preview-meta {
    flex: 1;
    min-width: 0;
}

.pwa-active-badge {
    font-size: 0.75rem;
    font-weight: 700;
    color: #176B5B;
    display: block;
}

.pwa-meta-controls {
    display: flex;
    gap: 0.4rem;
    margin-top: 0.35rem;
}

/* Document Cards Grid (Card 5) */
.doc-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

.doc-item-card {
    background: #F8FAFD;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: all 0.2s ease;
}

.doc-item-card:hover {
    border-color: #8B5CF6;
    background: #FAF8FE;
}

.doc-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.doc-item-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0C2B52;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.doc-status-badge {
    font-size: 0.68rem;
    font-weight: 700;
    border-radius: 4px;
    padding: 2px 6px;
}

.doc-status-badge.attached {
    background: #ECFDF5;
    color: #065F46;
}

.doc-status-badge.empty {
    background: #F1F5F9;
    color: #64748B;
}

.doc-item-thumb {
    height: 100px;
    background: #FFFFFF;
    border: 1.5px dashed #CBD5E1;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.doc-item-thumb img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.doc-placeholder-icon {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    color: #94A3B8;
    font-size: 1.3rem;
}

.doc-placeholder-icon span {
    font-size: 0.72rem;
    font-weight: 600;
}

.doc-item-actions {
    display: flex;
    gap: 0.35rem;
}

.btn-doc-upload {
    flex: 1;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #0C2B52;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.35rem 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.btn-doc-upload:hover {
    background: #F1F5F9;
}

.btn-doc-remove, .btn-doc-undo {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    padding: 0.35rem 0.6rem;
    border-radius: 6px;
    font-size: 0.78rem;
    cursor: pointer;
}

.btn-doc-remove {
    color: #DC2626;
    border-color: #FECACA;
    background: #FEF2F2;
}

.btn-doc-undo {
    color: #2563EB;
    border-color: #BFDBFE;
    background: #EFF6FF;
}

/* ========================================================================= */
/* RESPONSIVE BREAKPOINTS                                                    */
/* ========================================================================= */
@media (max-width: 1200px) {
    .doc-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .brand-identity-top {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .brand-names-panel {
        grid-template-columns: 1fr;
    }
    .brand-pwa-grid {
        grid-template-columns: 1fr;
    }
    .profile-two-col-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .company-profile-container {
        padding: 0 0 2rem 0;
        gap: 1.25rem;
    }
    .profile-executive-header {
        padding: 1.25rem 1rem;
        border-radius: 16px;
    }
    .profile-header-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .profile-title-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.4rem;
    }
    .profile-company-name {
        font-size: 1.35rem;
    }
    .profile-tags-row {
        gap: 0.75rem;
    }
    .two-column-form-grid,
    .three-column-form-grid {
        grid-template-columns: 1fr;
    }
    .doc-cards-grid {
        grid-template-columns: 1fr;
    }
    .profile-section-body {
        padding: 1.25rem 1rem;
    }
    .profile-section-footer {
        padding: 0.85rem 1rem;
    }
    .btn-save-section {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: CLIENT PREVIEWS & STANDARD MVC FORM SUBMISSION               -->
<!-- ========================================================================= -->
<script>
// Notification Helper
function showCompanyToast(message, type) {
    type = type || 'info';
    if (typeof window.showToursToast === 'function') {
        window.showToursToast(message, type);
    } else if (typeof window.vgToast === 'function') {
        window.vgToast(message, type === 'warning' ? 'error' : type);
    } else {
        console.log('[CompanySettings]', type, message);
    }
}

// 1. Same-as-Company Real-time Toggle & Mirroring
function onSameAsCompanyToggle(isChecked) {
    const sourceInput = document.getElementById('app_name_source_input');
    const appNameInput = document.getElementById('app_name_input');
    const companyNameInput = document.getElementById('company_name_input');
    const badge = document.getElementById('badgeDerivedAppName');

    if (isChecked) {
        if (sourceInput) sourceInput.value = 'company_name';
        if (appNameInput) {
            appNameInput.readOnly = true;
            appNameInput.classList.add('is-derived');
            if (companyNameInput) {
                appNameInput.value = companyNameInput.value.trim();
            }
        }
        if (badge) badge.style.display = 'inline-flex';
    } else {
        if (sourceInput) sourceInput.value = 'custom';
        if (appNameInput) {
            appNameInput.readOnly = false;
            appNameInput.classList.remove('is-derived');
            appNameInput.focus();
        }
        if (badge) badge.style.display = 'none';
    }
}

function onCompanyNameChanged(newVal) {
    const isSameAsCompany = document.getElementById('chk_same_as_company')?.checked;
    if (isSameAsCompany) {
        const appNameInput = document.getElementById('app_name_input');
        if (appNameInput) {
            appNameInput.value = newVal.trim();
        }
    }
}

// 2. PWA App Icon Source Toggle
function onAppIconSourceToggle(source) {
    const labelLogo = document.getElementById('labelSourceLogo');
    const labelCustom = document.getElementById('labelSourceCustom');
    const customControls = document.getElementById('customIconControls');
    const badge = document.getElementById('pwaActiveSourceBadge');

    if (source === 'custom') {
        if (labelLogo) labelLogo.classList.remove('is-selected');
        if (labelCustom) labelCustom.classList.add('is-selected');
        if (customControls) customControls.style.display = 'flex';
        if (badge) badge.textContent = 'Custom App Icon';
    } else {
        if (labelLogo) labelLogo.classList.add('is-selected');
        if (labelCustom) labelCustom.classList.remove('is-selected');
        if (customControls) customControls.style.display = 'none';
        if (badge) badge.textContent = 'Generated from Company Logo';
    }
}

// 3. Logo Upload & Removal Handlers
function handleLogoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showCompanyToast('Logo exceeds maximum size of 5MB', 'warning');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        const preview = document.getElementById('logoPreviewImg');
        const ph = document.getElementById('logoPlaceholder');
        const btnRemove = document.getElementById('btnRemoveLogo');
        const btnUndo = document.getElementById('btnUndoLogo');
        const pill = document.getElementById('logoRemovedPill');
        const flag = document.getElementById('flag_remove_logo');

        if (preview) { preview.src = dataUrl; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (flag) flag.value = '0';
        const txt = document.getElementById('logoUploadBtnText');
        if (txt) txt.textContent = 'Change Logo';

        // Update PWA preview if in company_logo mode
        const isLogoMode = document.getElementById('sourceCompanyLogo')?.checked;
        if (isLogoMode) {
            const pwaImg = document.getElementById('pwaPreviewImg');
            if (pwaImg) pwaImg.src = dataUrl;
        }
    };
    reader.readAsDataURL(file);
}

function removeLogoMedia() {
    const flag = document.getElementById('flag_remove_logo');
    if (flag) flag.value = '1';
    const inp = document.getElementById('logo_input');
    if (inp) inp.value = '';
    const btnRemove = document.getElementById('btnRemoveLogo');
    const btnUndo = document.getElementById('btnUndoLogo');
    const pill = document.getElementById('logoRemovedPill');
    if (btnRemove) btnRemove.style.display = 'none';
    if (btnUndo) btnUndo.style.display = 'inline-flex';
    if (pill) pill.style.display = 'block';
}

function undoLogoRemoval(origUrl) {
    const flag = document.getElementById('flag_remove_logo');
    if (flag) flag.value = '0';
    const btnRemove = document.getElementById('btnRemoveLogo');
    const btnUndo = document.getElementById('btnUndoLogo');
    const pill = document.getElementById('logoRemovedPill');
    if (btnRemove) btnRemove.style.display = 'inline-flex';
    if (btnUndo) btnUndo.style.display = 'none';
    if (pill) pill.style.display = 'none';
    if (origUrl) {
        const preview = document.getElementById('logoPreviewImg');
        const ph = document.getElementById('logoPlaceholder');
        if (preview) { preview.src = origUrl; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
    }
}

// 4. PWA App Icon Handlers
function handleAppIconChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showCompanyToast('App icon exceeds 5MB limit', 'warning');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        const pwaImg = document.getElementById('pwaPreviewImg');
        const btnRemove = document.getElementById('btnRemoveAppIcon');
        const btnUndo = document.getElementById('btnUndoAppIcon');
        const pill = document.getElementById('appIconRemovedPill');
        const flag = document.getElementById('flag_remove_app_icon');

        if (pwaImg) pwaImg.src = dataUrl;
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (flag) flag.value = '0';
        const txt = document.getElementById('appIconUploadBtnText');
        if (txt) txt.textContent = 'Change Icon';

        // Select custom radio
        const rCustom = document.getElementById('sourceCustom');
        if (rCustom) {
            rCustom.checked = true;
            onAppIconSourceToggle('custom');
        }
    };
    reader.readAsDataURL(file);
}

function removeAppIconMedia() {
    const flag = document.getElementById('flag_remove_app_icon');
    if (flag) flag.value = '1';
    const inp = document.getElementById('app_icon_input');
    if (inp) inp.value = '';
    const btnRemove = document.getElementById('btnRemoveAppIcon');
    const btnUndo = document.getElementById('btnUndoAppIcon');
    const pill = document.getElementById('appIconRemovedPill');
    if (btnRemove) btnRemove.style.display = 'none';
    if (btnUndo) btnUndo.style.display = 'inline-flex';
    if (pill) pill.style.display = 'block';

    const rLogo = document.getElementById('sourceCompanyLogo');
    if (rLogo) {
        rLogo.checked = true;
        onAppIconSourceToggle('company_logo');
    }
}

function undoAppIconRemoval(origUrl) {
    const flag = document.getElementById('flag_remove_app_icon');
    if (flag) flag.value = '0';
    const btnRemove = document.getElementById('btnRemoveAppIcon');
    const btnUndo = document.getElementById('btnUndoAppIcon');
    const pill = document.getElementById('appIconRemovedPill');
    if (btnRemove) btnRemove.style.display = 'inline-flex';
    if (btnUndo) btnUndo.style.display = 'none';
    if (pill) pill.style.display = 'none';
    if (origUrl) {
        const pwaImg = document.getElementById('pwaPreviewImg');
        if (pwaImg) pwaImg.src = origUrl;
        const rCustom = document.getElementById('sourceCustom');
        if (rCustom) {
            rCustom.checked = true;
            onAppIconSourceToggle('custom');
        }
    }
}

// 5. Document Image Upload & Removal Handlers
function handleDocChange(input, type) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showCompanyToast('Document exceeds 5MB size limit', 'warning');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        let previewId = '', phId = '', btnTextId = '', removeBtnId = '', undoBtnId = '', pillId = '', badgeId = '';

        if (type === 'logo') {
            previewId = 'docLogoPreview'; phId = 'docLogoPlaceholder'; btnTextId = 'docLogoBtnText';
            removeBtnId = 'btnRemoveDocLogo'; undoBtnId = 'btnUndoDocLogo'; pillId = 'docLogoRemovedPill'; badgeId = 'badgeDocLogo';
        } else if (type === 'paid_stamp') {
            previewId = 'docPaidStampPreview'; phId = 'docPaidStampPlaceholder'; btnTextId = 'docPaidStampBtnText';
            removeBtnId = 'btnRemoveDocPaidStamp'; undoBtnId = 'btnUndoDocPaidStamp'; pillId = 'docPaidStampRemovedPill'; badgeId = 'badgeDocPaidStamp';
        } else if (type === 'signature') {
            previewId = 'docSignaturePreview'; phId = 'docSignaturePlaceholder'; btnTextId = 'docSignatureBtnText';
            removeBtnId = 'btnRemoveDocSignature'; undoBtnId = 'btnUndoDocSignature'; pillId = 'docSignatureRemovedPill'; badgeId = 'badgeDocSignature';
        } else if (type === 'signature_stamp') {
            previewId = 'docSigStampPreview'; phId = 'docSigStampPlaceholder'; btnTextId = 'docSigStampBtnText';
            removeBtnId = 'btnRemoveDocSigStamp'; undoBtnId = 'btnUndoDocSigStamp'; pillId = 'docSigStampRemovedPill'; badgeId = 'badgeDocSigStamp';
        }

        const preview = document.getElementById(previewId);
        const ph = document.getElementById(phId);
        const btnRemove = document.getElementById(removeBtnId);
        const btnUndo = document.getElementById(undoBtnId);
        const pill = document.getElementById(pillId);
        const badge = document.getElementById(badgeId);

        if (preview) { preview.src = dataUrl; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (badge) {
            badge.className = 'doc-status-badge attached';
            badge.innerHTML = '<i class="fa-solid fa-check"></i> Attached';
        }
        const btnTxt = document.getElementById(btnTextId);
        if (btnTxt) btnTxt.textContent = 'Replace';
    };
    reader.readAsDataURL(file);
}

function removeDocumentImage(type) {
    let flagId = '', removeBtnId = '', undoBtnId = '', pillId = '', badgeId = '';
    if (type === 'logo') { flagId = 'flag_doc_remove_logo'; removeBtnId = 'btnRemoveDocLogo'; undoBtnId = 'btnUndoDocLogo'; pillId = 'docLogoRemovedPill'; badgeId = 'badgeDocLogo'; }
    else if (type === 'paid_stamp') { flagId = 'flag_remove_paid_stamp'; removeBtnId = 'btnRemoveDocPaidStamp'; undoBtnId = 'btnUndoDocPaidStamp'; pillId = 'docPaidStampRemovedPill'; badgeId = 'badgeDocPaidStamp'; }
    else if (type === 'signature') { flagId = 'flag_remove_signature'; removeBtnId = 'btnRemoveDocSignature'; undoBtnId = 'btnUndoDocSignature'; pillId = 'docSignatureRemovedPill'; badgeId = 'badgeDocSignature'; }
    else if (type === 'signature_stamp') { flagId = 'flag_remove_signature_stamp'; removeBtnId = 'btnRemoveDocSigStamp'; undoBtnId = 'btnUndoDocSigStamp'; pillId = 'docSigStampRemovedPill'; badgeId = 'badgeDocSigStamp'; }

    const flag = document.getElementById(flagId);
    if (flag) flag.value = '1';
    const btnRemove = document.getElementById(removeBtnId);
    const btnUndo = document.getElementById(undoBtnId);
    const pill = document.getElementById(pillId);
    const badge = document.getElementById(badgeId);

    if (btnRemove) btnRemove.style.display = 'none';
    if (btnUndo) btnUndo.style.display = 'inline-flex';
    if (pill) pill.style.display = 'block';
    if (badge) {
        badge.className = 'doc-status-badge empty';
        badge.innerHTML = '<i class="fa-solid fa-circle-info"></i> Removed';
    }
}

function undoDocumentImage(type, origUrl) {
    let flagId = '', previewId = '', phId = '', removeBtnId = '', undoBtnId = '', pillId = '', badgeId = '';
    if (type === 'logo') { flagId = 'flag_doc_remove_logo'; previewId = 'docLogoPreview'; phId = 'docLogoPlaceholder'; removeBtnId = 'btnRemoveDocLogo'; undoBtnId = 'btnUndoDocLogo'; pillId = 'docLogoRemovedPill'; badgeId = 'badgeDocLogo'; }
    else if (type === 'paid_stamp') { flagId = 'flag_remove_paid_stamp'; previewId = 'docPaidStampPreview'; phId = 'docPaidStampPlaceholder'; removeBtnId = 'btnRemoveDocPaidStamp'; undoBtnId = 'btnUndoDocPaidStamp'; pillId = 'docPaidStampRemovedPill'; badgeId = 'badgeDocPaidStamp'; }
    else if (type === 'signature') { flagId = 'flag_remove_signature'; previewId = 'docSignaturePreview'; phId = 'docSignaturePlaceholder'; removeBtnId = 'btnRemoveDocSignature'; undoBtnId = 'btnUndoDocSignature'; pillId = 'docSignatureRemovedPill'; badgeId = 'badgeDocSignature'; }
    else if (type === 'signature_stamp') { flagId = 'flag_remove_signature_stamp'; previewId = 'docSigStampPreview'; phId = 'docSigStampPlaceholder'; removeBtnId = 'btnRemoveDocSigStamp'; undoBtnId = 'btnUndoDocSigStamp'; pillId = 'docSigStampRemovedPill'; badgeId = 'badgeDocSigStamp'; }

    const flag = document.getElementById(flagId);
    if (flag) flag.value = '0';
    const btnRemove = document.getElementById(removeBtnId);
    const btnUndo = document.getElementById(undoBtnId);
    const pill = document.getElementById(pillId);
    const badge = document.getElementById(badgeId);

    if (btnRemove) btnRemove.style.display = 'inline-flex';
    if (btnUndo) btnUndo.style.display = 'none';
    if (pill) pill.style.display = 'none';

    if (origUrl) {
        const preview = document.getElementById(previewId);
        const ph = document.getElementById(phId);
        if (preview) { preview.src = origUrl; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
        if (badge) {
            badge.className = 'doc-status-badge attached';
            badge.innerHTML = '<i class="fa-solid fa-check"></i> Attached';
        }
    }
}

// 6. Standard MVC Form Submission (POST -> Save -> Redirect -> GET)
// Native browser submission: standard HTML POST without JS interception or reload
document.addEventListener('DOMContentLoaded', function () {
    const brandForm = document.querySelector('form[data-section="brand_identity"]');
    if (brandForm) {
        brandForm.addEventListener('submit', function () {
            const chkSame = document.getElementById('chk_same_as_company');
            const sourceInput = document.getElementById('app_name_source_input');
            const compInput = document.getElementById('company_name_input');
            const appInput = document.getElementById('app_name_input');

            if (chkSame && sourceInput) {
                if (chkSame.checked) {
                    sourceInput.value = 'company_name';
                    if (compInput && appInput) {
                        appInput.value = compInput.value.trim();
                    }
                } else {
                    sourceInput.value = 'custom';
                }
            }
        });
    }

    // Submit button visual feedback on native submission
    const forms = document.querySelectorAll('.company-section-form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('.btn-save-section');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving...</span>';
            }
        });
    });
});
</script>
