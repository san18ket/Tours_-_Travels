<?php
/**
 * App Details Management - Professional Step-Wise Wizard
 * Strictly application and PWA configuration.
 *
 * Steps:
 * 1. App Identity (App Name, Default Company Name Fallback)
 * 2. App Version (Mandatory Semantic Versioning MAJOR.MINOR.PATCH)
 * 3. PWA App Icon (Clean Professional Upload Box)
 * 4. Diagnostics & Save (PWA Diagnostics, Update Policy, Live Review Dossier, Submit)
 */

$primaryCompany = $company ?? $primaryCompany ?? null;
$isExisting = !empty($primaryCompany);
$initialStep = (int)($activeStep ?? 1);
if ($initialStep < 1 || $initialStep > 4) {
    $initialStep = 1;
}

// Form values helper
$val = function(string $key, string $default = '') use ($primaryCompany, $old): string {
    if (isset($old[$key])) {
        return (string)$old[$key];
    }
    return isset($primaryCompany[$key]) ? (string)$primaryCompany[$key] : $default;
};

// Form error display helper
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

// Company Name & App Name State
$companyNameVal = $val('company_name', 'Tours & Travels');
$customAppName = $val('app_name', '');
$appNameSource = $val('app_name_source', 'company_name');
$isSameAsCompany = ($appNameSource === 'company_name' || empty($customAppName));
$effectiveAppName = !empty($customAppName) ? $customAppName : $companyNameVal;

// App Version State
$currentSavedVersion = trim((string)($primaryCompany['app_version'] ?? ''));
if ($currentSavedVersion === '' || !\App\Services\CompanySettingsService::isValidSemanticVersion($currentSavedVersion)) {
    $currentSavedVersion = \App\Services\CompanySettingsService::getAppVersion();
}
$appVersionVal = $val('app_version', $currentSavedVersion);

// PWA App Icon State
$appIconSource = $val('app_icon_source', 'company_logo');
$appIconUrl = !empty($primaryCompany['app_icon']) ? storage_url($primaryCompany['app_icon']) : null;
$pwaPreviewUrl = \App\Services\CompanySettingsService::getPwaIconPreviewUrl();
$defaultCompanyLogoUrl = \App\Services\CompanySettingsService::hasLogo() 
    ? \App\Services\CompanySettingsService::getLogoUrl() 
    : asset('icons/icon.svg');
?>

<div class="company-profile-container">

    <!-- Flash Messages -->
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
    <!-- 3. PROFESSIONAL 2-STEP STEPPER HEADER                                     -->
    <!-- ========================================================================= -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container" style="max-width: 480px; margin: 0 auto;">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item <?= $initialStep === 1 ? 'active' : ($initialStep > 1 ? 'completed' : '') ?>" data-step-target="1" onclick="wizardGoToStep(1)" title="<?= e(__('tooltip.step_1_app_configuration', 'Step 1: App Configuration')) ?>">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">App Configuration</span>
                </button>
                <div class="modern-step-connector <?= $initialStep > 1 ? 'completed' : '' ?>" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item <?= $initialStep === 2 ? 'active' : '' ?>" data-step-target="2" onclick="wizardGoToStep(2)" title="<?= e(__('tooltip.step_2_preview_save', 'Step 2: Preview & Save')) ?>">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Preview &amp; Save</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. MASTER 2-STEP FORM                                                     -->
    <!-- ========================================================================= -->
    <form action="<?= url('/office/company/app/save') ?>" method="POST" enctype="multipart/form-data" class="company-section-form" data-section="app" data-no-ajax="true" data-native="true" id="appDetailsForm" novalidate>
        <?= csrf_field() ?>
        <?php if ($isExisting): ?>
            <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
        <?php endif; ?>
        <input type="hidden" name="section" value="app">
        <input type="hidden" name="native_submit" value="1">
        <input type="hidden" name="remove_app_icon" id="flag_remove_app_icon" value="0">
        <input type="hidden" name="app_name_source" id="app_name_source_input" value="<?= e($appNameSource) ?>">
        <input type="hidden" name="app_icon_source" id="app_icon_source_input" value="<?= e($appIconSource) ?>">

        <!-- ===================================================================== -->
        <!-- STEP 1: APP CONFIGURATION (ICON, APP NAME, APP VERSION)               -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel1" style="<?= $initialStep === 1 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card" id="card_app_identity">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(14, 165, 233, 0.1); color: #0284C7;">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 1: App Configuration &amp; Branding</h2>
                            <p class="section-subtitle">Configure your application icon, public title and semantic version.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 1 of 2</span>
                </div>

                <div class="profile-section-body">
                    <div class="brand-identity-top">
                        <!-- Left: PWA App Icon Box (identical to company/details upload box) -->
                        <div class="brand-logo-panel">
                            <label class="control-label font-bold">
                                PWA App Icon
                            </label>
                            <div class="avatar-preview-container" onclick="document.getElementById('app_icon_input').click()" title="<?= e(__('tooltip.click_to_upload_pwa_app_icon', 'Click to upload PWA App Icon')) ?>">
                                <input type="file" id="app_icon_input" name="app_icon" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleAppIconChange(this)" onclick="event.stopPropagation()">
                                <div class="avatar-icon-placeholder" id="appIconPlaceholder" style="<?= (!empty($appIconUrl) || !empty($pwaPreviewUrl)) ? 'display: none;' : 'display: flex;' ?>">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                </div>
                                <img id="pwaPreviewImg" src="<?= !empty($appIconUrl) ? e($appIconUrl) : e($pwaPreviewUrl) ?>" alt="PWA App Icon Preview" class="avatar-image-preview" style="<?= (!empty($appIconUrl) || !empty($pwaPreviewUrl)) ? 'display: block;' : 'display: none;' ?>">
                                <div class="avatar-overlay-badge"><i class="fa-solid fa-camera"></i></div>
                                <span id="appIconRemovedPill" class="media-removed-pill" style="display: none;">REVERTED</span>
                            </div>
                            <div class="media-action-buttons">
                                <button type="button" class="btn-media-action" onclick="document.getElementById('app_icon_input').click()">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                    <span id="appIconUploadBtnText"><?= !empty($appIconUrl) ? 'Change Icon' : 'Upload Icon' ?></span>
                                </button>
                                <button type="button" id="btnRemoveAppIcon" class="btn-media-remove" onclick="removeAppIconMedia()" style="<?= empty($appIconUrl) ? 'display: none;' : 'display: inline-flex;' ?>">
                                    <i class="fa-solid fa-trash-can"></i> <span>Remove</span>
                                </button>
                                <button type="button" id="btnUndoAppIcon" class="btn-media-undo" onclick="undoAppIconRemoval('<?= e($appIconUrl ?? '') ?>')" style="display: none;">
                                    <i class="fa-solid fa-rotate-left"></i> <span>Undo</span>
                                </button>
                            </div>
                            <p class="media-helper-note">Square app icon (PNG, JPG, WebP up to 5MB). 512×512 recommended. Defaults to company logo if not uploaded.</p>
                            <?= $fieldError('app_icon') ?>
                        </div>

                        <!-- Right: App Name & App Version Inputs -->
                        <div class="brand-names-panel">
                            <!-- App Name Input -->
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <div class="app-name-header-row">
                                    <label for="app_name_input" class="control-label font-bold" style="margin-bottom: 0;">
                                        App Name
                                    </label>
                                    <span class="app-identity-badge <?= $isSameAsCompany ? 'is-default' : 'is-custom' ?>" id="appNameBadge">
                                        <?php if ($isSameAsCompany): ?>
                                            <i class="fa-solid fa-building"></i> <span>Default: <?= e($companyNameVal) ?></span>
                                        <?php else: ?>
                                            <i class="fa-solid fa-pen-fancy"></i> <span>Custom Override Active</span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="app-name-input-wrapper">
                                    <input type="text" id="app_name_input" name="app_name" value="<?= e($customAppName) ?>" class="<?= $inputClass('app_name', 'form-control form-control-lg' . (!$isSameAsCompany ? ' is-custom-active' : '')) ?>" placeholder="<?= e($companyNameVal) ?> (Default: Company Name)" oninput="onAppNameInput(this.value)">
                                    <button type="button" id="btnResetAppName" class="btn-input-revert" onclick="resetToCompanyName()" style="<?= empty($customAppName) ? 'display: none;' : 'display: inline-flex;' ?>" title="<?= e(__('tooltip.revert_to_company_name', 'Revert to Company Name')) ?>">
                                        <i class="fa-solid fa-rotate-left"></i> <span>Use Company Name</span>
                                    </button>
                                </div>

                                <p class="form-helper-text" id="appNameHelperText">
                                    By default, your application name is automatically set to your company name (<strong><?= e($companyNameVal) ?></strong>). Enter a different name above only if you wish to set a custom app name. Leave blank to use company name.
                                </p>
                                <?= $fieldError('app_name') ?>
                            </div>

                            <!-- App Version Input -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="app_version_input" class="control-label font-bold">
                                    App Version <span class="text-danger">*</span>
                                </label>
                                <div class="version-input-wrap">
                                    <span class="version-prefix">v</span>
                                    <input type="text" id="app_version_input" name="app_version" value="<?= e($appVersionVal) ?>" class="<?= $inputClass('app_version', 'form-control form-control-lg font-mono font-bold') ?>" required pattern="^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$" placeholder="<?= e(__('placeholder.2_4_0', '2.4.0')) ?>" oninput="onAppVersionInput(this.value)">
                                </div>
                                <p class="form-helper-text">
                                    <strong>Format:</strong> Follows semantic versioning <code>MAJOR.MINOR.PATCH</code> (Example: <code>2.4.0</code>).
                                    Signals installed PWAs and client devices to download fresh updates.
                                </p>
                                <?= $fieldError('app_version') ?>

                                <div class="version-validation-note" id="versionNoteOk" style="display: flex;">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="versionNoteOkText">Semantic version is valid. Older installed PWAs will verify against this release.</span>
                                </div>
                                <div class="version-validation-note note-invalid" id="versionNoteErr" style="display: none;">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <span id="versionNoteErrText">Please enter a valid semantic version (e.g. 2.4.0 or 3.0.1).</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <span class="step-guide-text"><i class="fa-solid fa-circle-info"></i> Step 1: Configure Icon, Name &amp; Version</span>
                    </div>
                    <div class="wizard-actions-right">
                        <?php if ($isExisting): ?>
                            <button type="submit" class="btn btn-outline-save btn-quick-save">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                            <span>Next: Preview &amp; Save</span> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- STEP 2: PREVIEW & SAVE                                                -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel2" style="<?= $initialStep === 2 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card" id="card_app_preview_save">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 2: Preview &amp; Save</h2>
                            <p class="section-subtitle">Review your progressive web app launcher appearance, verify update diagnostics, and save changes.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 2 of 2</span>
                </div>

                <div class="profile-section-body">
                    <!-- Mobile Launcher & Installation Live Preview Card -->
                    <div class="pwa-live-preview-box">
                        <div class="pwa-preview-screen">
                            <div class="mobile-mockup-frame">
                                <div class="mobile-speaker"></div>
                                <div class="mobile-screen-content">
                                    <div class="mobile-status-bar">
                                        <span class="mobile-time">9:41</span>
                                        <span class="mobile-icons"><i class="fa-solid fa-signal"></i> <i class="fa-solid fa-wifi"></i> <i class="fa-solid fa-battery-full"></i></span>
                                    </div>
                                    <div class="mobile-app-grid">
                                        <div class="mobile-app-item">
                                            <div class="launcher-app-icon-wrap">
                                                <img id="mockupIconImg" src="<?= !empty($appIconUrl) ? e($appIconUrl) : e($pwaPreviewUrl) ?>" alt="Live App Icon" class="launcher-app-icon">
                                            </div>
                                            <span class="launcher-app-name" id="mockupAppTitle"><?= e($effectiveAppName) ?></span>
                                        </div>
                                    </div>
                                    <div class="mobile-dock">
                                        <div class="dock-pill"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-info-column">
                                <div class="preview-badge-status">
                                    <span class="badge-status-dot"></span>
                                    <strong>Live Progressive Web App Launcher</strong>
                                </div>
                                <p class="preview-explanation">
                                    This live preview demonstrates how your application appears when installed onto client devices (Android, iOS, Windows, macOS).
                                </p>
                                <div class="preview-meta-chips">
                                    <div class="meta-chip">
                                        <div class="meta-chip-icon" style="background: rgba(14, 165, 233, 0.12); color: #0284C7;"><i class="fa-solid fa-tag"></i></div>
                                        <div>
                                            <span class="meta-chip-label">App Title</span>
                                            <strong class="meta-chip-val" id="revAppName"><?= e($effectiveAppName) ?></strong>
                                            <span class="meta-chip-sub" id="revAppNameSource"><?= $isSameAsCompany ? 'Inherited from Company Name' : 'Custom Override Active' ?></span>
                                        </div>
                                    </div>
                                    <div class="meta-chip">
                                        <div class="meta-chip-icon" style="background: rgba(168, 85, 247, 0.12); color: #9333EA;"><i class="fa-solid fa-code-commit"></i></div>
                                        <div>
                                            <span class="meta-chip-label">Semantic Version</span>
                                            <strong class="meta-chip-val font-mono" id="revAppVersion">v<?= e($appVersionVal) ?></strong>
                                            <span class="meta-chip-sub">PWA Update Gate Target</span>
                                        </div>
                                    </div>
                                    <div class="meta-chip">
                                        <div class="meta-chip-icon" style="background: rgba(234, 88, 12, 0.12); color: #EA580C;"><i class="fa-solid fa-shapes"></i></div>
                                        <div>
                                            <span class="meta-chip-label">Icon Source</span>
                                            <strong class="meta-chip-val" id="revAppIcon"><?= $appIconSource === 'custom' ? 'Custom Uploaded Icon' : 'Company Logo (Default)' ?></strong>
                                            <span class="meta-chip-sub">512×512 Home Screen Spec</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Notice Box -->
                    <div class="pwa-policy-notice-box" style="margin-top: 1.5rem;">
                        <div class="pwa-policy-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <div class="pwa-policy-text">
                            <strong>Mandatory Update Policy:</strong>
                            <p>
                                Installed Progressive Web Applications (PWA) must be on the latest version to maintain data consistency and security. When a new version is released, installed apps prompt users to update before accessing the dashboard or login.
                            </p>
                        </div>
                    </div>

                    <!-- Diagnostics Grid -->
                    <div class="pwa-diag-grid" style="margin-top: 1.25rem;">
                        <div class="pwa-diag-item">
                            <span class="pwa-diag-label">Active App Version:</span>
                            <strong class="pwa-diag-value font-mono">v<?= e($appVersionVal) ?></strong>
                        </div>
                        <div class="pwa-diag-item">
                            <span class="pwa-diag-label">Minimum Required Version:</span>
                            <strong class="pwa-diag-value font-mono">v<?= e($appVersionVal) ?></strong>
                        </div>
                        <div class="pwa-diag-item">
                            <span class="pwa-diag-label">Web Manifest:</span>
                            <span class="diag-status-pill pill-ok">
                                <i class="fa-solid fa-circle-check"></i> Valid (/manifest.json)
                            </span>
                        </div>
                        <div class="pwa-diag-item">
                            <span class="pwa-diag-label">Version Check API:</span>
                            <span class="diag-status-pill pill-ok">
                                <i class="fa-solid fa-bolt"></i> Live (/api/v1/app/version)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(1)">
                            <i class="fa-solid fa-arrow-left"></i> <span>Previous: Edit Settings</span>
                        </button>
                    </div>
                    <div class="wizard-actions-right">
                        <button type="submit" class="modern-btn-submit" id="btnSaveApp">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span id="btnSubmitText">Save Changes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

<!-- ========================================================================= -->
<!-- STYLES & SCRIPT                                                           -->
<!-- ========================================================================= -->
<style>
.company-profile-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    box-sizing: border-box;
}

/* Page-level Footer & Workspace Spacing (Issue 3: Remove excessive external whitespace before footer) */
.main-wrapper:has(.company-profile-container) .main-content,
.main-content:has(.company-profile-container) {
    flex: 0 0 auto !important;
    padding-bottom: 1.5rem !important;
}

.main-wrapper:has(.company-profile-container) .app-footer {
    margin-top: 0 !important;
}

.company-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #64748B;
    margin-bottom: -0.5rem;
}

.breadcrumb-separator {
    font-size: 0.65rem;
    color: #94A3B8;
}

.breadcrumb-active {
    color: #0284C7;
    font-weight: 700;
}

.app-theme-header {
    background: linear-gradient(135deg, #0B192C 0%, #1E3E62 100%) !important;
    box-shadow: 0 8px 24px rgba(11, 25, 44, 0.25) !important;
}

.profile-executive-header {
    border-radius: 20px;
    padding: 1.5rem 2rem;
    color: #FFFFFF;
    box-sizing: border-box;
    width: 100%;
}

.profile-header-main {
    display: flex;
    align-items: center;
    gap: 1.75rem;
}

.profile-header-avatar.app-avatar {
    width: 84px;
    height: 84px;
    border-radius: 20px;
    background: #0B192C;
    border: 2px solid rgba(255, 255, 255, 0.2);
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.profile-avatar-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 14px;
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

.app-version-pill {
    background: rgba(168, 85, 247, 0.25);
    color: #E9D5FF;
    border: 1px solid rgba(168, 85, 247, 0.5);
}

.pwa-active-pill {
    background: rgba(16, 185, 129, 0.2);
    color: #A7F3D0;
    border: 1px solid rgba(16, 185, 129, 0.4);
}

.profile-tags-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.profile-tag-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.85);
}

.profile-tag-item i {
    color: #38BDF8;
    font-size: 0.8rem;
}

.profile-header-desc {
    margin: 0;
    font-size: 0.86rem;
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.4;
}

/* Stepper Header Styling */
.modern-stepper-header-centered {
    width: 100%;
}

.modern-stepper-container {
    width: 100%;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 1.1rem 2rem;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
    box-sizing: border-box;
}

.modern-stepper-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    width: 100%;
    max-width: 860px;
    margin: 0 auto;
}

.modern-step-item {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 2 !important;
    padding: 0.25rem 0.5rem !important;
    transition: all 0.25s ease !important;
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
    transition: all 0.25s ease !important;
    white-space: nowrap !important;
}

.modern-step-item.active .modern-step-circle {
    background: #0284C7 !important;
    border-color: #0284C7 !important;
    color: #FFFFFF !important;
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.18), 0 4px 12px rgba(2, 132, 199, 0.25) !important;
    transform: scale(1.08) !important;
}

.modern-step-item.active .modern-step-label {
    color: #0284C7 !important;
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

.step-indicator-pill {
    font-size: 0.76rem;
    font-weight: 700;
    color: #0284C7;
    background: #E0F2FE;
    border: 1px solid #BAE6FD;
    border-radius: 50px;
    padding: 0.25rem 0.75rem;
}

/* Card Styling */
.profile-section-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
}

.profile-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.75rem;
    background: #FFFFFF;
    border-bottom: 1px solid #EDF2F7;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.section-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0C2B52;
    margin: 0;
}

.section-subtitle {
    font-size: 0.8rem;
    color: #64748B;
    margin: 0.15rem 0 0 0;
}

.profile-section-body {
    padding: 1.75rem;
}

/* Form Actions */
.wizard-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.75rem;
    background: #F8FAFD;
    border-top: 1px solid #EDF2F7;
    gap: 1rem;
}

.wizard-actions-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.wizard-actions-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.step-guide-text {
    font-size: 0.82rem;
    color: #64748B;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-outline-save {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 0 1.25rem;
    height: 46px;
    border-radius: 50px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
}

.btn-outline-save:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.modern-btn-prev {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 46px;
    padding: 0 24px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 50px;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.modern-btn-prev:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #94A3B8;
}

.modern-btn-next {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    height: 48px;
    padding: 0 32px;
    background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 50px;
    color: #FFFFFF !important;
    font-size: 0.96rem;
    font-weight: 700;
    letter-spacing: 0.015em;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.35);
    transition: all 0.25s ease;
}

.modern-btn-next:hover {
    background: linear-gradient(135deg, #0369A1 0%, #075985 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(2, 132, 199, 0.45);
}

.modern-btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    height: 48px;
    padding: 0 36px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
    border: none;
    border-radius: 50px;
    color: #FFFFFF !important;
    font-size: 0.98rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
    transition: all 0.25s ease;
}

.modern-btn-submit:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.45);
}

/* App Identity Elements */
.app-name-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.app-identity-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 50px;
    letter-spacing: 0.01em;
}

.app-identity-badge.is-default {
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #CBD5E1;
}

.app-identity-badge.is-custom {
    background: #ECFDF5;
    color: #065F46;
    border: 1px solid #A7F3D0;
}

.app-name-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.app-name-input-wrapper .form-control {
    width: 100%;
}

.app-name-input-wrapper .form-control.is-custom-active {
    border-color: #0284C7;
    background-color: #F0F9FF;
}

.btn-input-revert {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.35rem 0.7rem;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.15s ease;
}

.btn-input-revert:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

/* Version Card Elements */
.version-input-wrap {
    display: flex;
    align-items: center;
    position: relative;
    max-width: 320px;
}

.version-prefix {
    position: absolute;
    left: 14px;
    font-weight: 800;
    color: #9333EA;
    font-size: 1.1rem;
    pointer-events: none;
}

.version-input-wrap input {
    padding-left: 2.2rem;
}

.version-validation-note {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.78rem;
    color: #059669;
    margin-top: 0.6rem;
    font-weight: 600;
}

.version-validation-note.note-invalid {
    color: #DC2626;
}

/* Clean Upload Box - matching company/details Step 1 */
.brand-identity-top {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 1.75rem;
    align-items: start;
}

.brand-logo-panel {
    display: flex;
    flex-direction: column;
}

.brand-names-panel {
    display: flex;
    flex-direction: column;
}

/* Step 2 PWA Live Preview & Mobile Mockup */
.pwa-live-preview-box {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 18px;
    padding: 1.75rem;
    box-sizing: border-box;
}

.pwa-preview-screen {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 2.25rem;
    align-items: center;
}

.mobile-mockup-frame {
    width: 210px;
    height: 280px;
    background: #0B192C;
    border: 4px solid #1E293B;
    border-radius: 28px;
    padding: 10px;
    box-shadow: 0 12px 30px rgba(11, 25, 44, 0.25);
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    margin: 0 auto;
}

.mobile-speaker {
    width: 44px;
    height: 4px;
    background: #334155;
    border-radius: 4px;
    margin: 2px auto 8px auto;
}

.mobile-screen-content {
    flex: 1;
    background: linear-gradient(180deg, #1E3E62 0%, #0B192C 100%);
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    padding: 8px 10px;
    box-sizing: border-box;
}

.mobile-status-bar {
    display: flex;
    justify-content: space-between;
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 1.5rem;
}

.mobile-app-grid {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.mobile-app-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
}

.launcher-app-icon-wrap {
    width: 68px;
    height: 68px;
    border-radius: 16px;
    background: #FFFFFF;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.35);
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}

.launcher-app-icon {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 10px;
}

.launcher-app-name {
    font-size: 0.78rem;
    font-weight: 700;
    color: #FFFFFF;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.mobile-dock {
    display: flex;
    justify-content: center;
    padding-top: 6px;
}

.dock-pill {
    width: 60px;
    height: 3px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 3px;
}

.preview-info-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.preview-badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    color: #0F172A;
}

.badge-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
}

.preview-explanation {
    font-size: 0.86rem;
    color: #64748B;
    line-height: 1.5;
    margin: 0;
}

.preview-meta-chips {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 0.35rem;
}

.meta-chip {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.meta-chip-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.meta-chip-label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748B;
    letter-spacing: 0.03em;
}

.meta-chip-val {
    display: block;
    font-size: 0.95rem;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
}

.meta-chip-sub {
    display: block;
    font-size: 0.72rem;
    color: #64748B;
    margin-top: 2px;
}

.avatar-preview-container {
    width: 100%;
    height: 120px;
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
    border-color: #0284C7;
    background: #F0F9FF;
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
    font-size: 2.4rem;
    color: #94A3B8;
}

.avatar-overlay-badge {
    position: absolute;
    bottom: 6px;
    right: 6px;
    background: #0284C7;
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
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.65rem;
    flex-wrap: wrap;
    width: 100%;
}

.btn-media-action,
.btn-media-remove,
.btn-media-undo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    height: 36px;
    padding: 0 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    line-height: 1;
    border-radius: 8px;
    cursor: pointer;
    box-sizing: border-box;
    transition: all 0.2s ease;
    white-space: nowrap;
    text-decoration: none;
    font-family: inherit;
}

.btn-media-action i,
.btn-media-remove i,
.btn-media-undo i {
    font-size: 0.82rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.btn-media-action {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
}

.btn-media-action:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
    color: #0C2B52;
}

.btn-media-remove {
    background: #FEF2F2;
    border: 1.5px solid #FECACA;
    color: #DC2626;
}

.btn-media-remove:hover {
    background: #FEE2E2;
    border-color: #F87171;
    color: #B91C1C;
}

.btn-media-undo {
    background: #EFF6FF;
    border: 1.5px solid #BFDBFE;
    color: #2563EB;
}

.btn-media-undo:hover {
    background: #DBEAFE;
    border-color: #93C5FD;
}

.media-helper-note {
    font-size: 0.73rem;
    color: #64748B;
    margin: 0.4rem 0 0 0;
    line-height: 1.35;
    text-align: center;
}

.media-removed-pill {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #EF4444;
    color: #FFFFFF;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
}

/* Policy & Diagnostics */
.pwa-policy-notice-box {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    background: #F0FDF4;
    border: 1.5px solid #BBF7D0;
    border-radius: 14px;
    padding: 1rem 1.25rem;
}

.pwa-policy-icon {
    font-size: 1.25rem;
    color: #16A34A;
    margin-top: 2px;
}

.pwa-policy-text strong {
    font-size: 0.88rem;
    color: #166534;
    display: block;
    margin-bottom: 0.25rem;
}

.pwa-policy-text p {
    font-size: 0.8rem;
    color: #15803D;
    margin: 0;
    line-height: 1.4;
}

.pwa-diag-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.pwa-diag-item {
    background: #F8FAFD;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.pwa-diag-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
}

.pwa-diag-value {
    font-size: 0.95rem;
    color: #0C2B52;
}

.diag-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    width: fit-content;
}

.diag-status-pill.pill-ok {
    background: #DCFCE7;
    color: #15803D;
}

/* Review Container */
.wizard-review-container {
    background: #F8FAFD;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
}

.review-box-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 800;
    color: #0C2B52;
    margin-bottom: 1rem;
}

.review-box-header i {
    color: #0284C7;
}

.review-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.review-card-item {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.review-card-lbl {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
}

.review-card-val {
    font-size: 0.96rem;
    font-weight: 800;
    color: #0C2B52;
}

.review-card-sub {
    font-size: 0.78rem;
    color: #64748B;
}

/* Standard Inputs & Alerts */
.form-group {
    margin-bottom: 0.5rem;
}

.control-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.84rem;
    color: #0C2B52;
}

.font-bold {
    font-weight: 700;
}

.font-mono {
    font-family: monospace;
}

.form-control {
    width: 100%;
    box-sizing: border-box;
    border: 1.5px solid #CBD5E1;
    border-radius: 10px;
    padding: 0.6rem 0.85rem;
    font-size: 0.88rem;
    color: #0C2B52;
    background: #FFFFFF;
    transition: all 0.15s ease;
}

.form-control:focus {
    border-color: #0284C7;
    outline: none;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
}

.form-helper-text {
    margin: 0.35rem 0 0 0;
    font-size: 0.74rem;
    color: #64748B;
}

.field-error-msg {
    margin-top: 0.3rem;
    font-size: 0.75rem;
    color: #DC2626;
    font-weight: 600;
}

.profile-alert {
    padding: 0.9rem 1.2rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.88rem;
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.alert-success {
    background: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #065F46;
}

.alert-danger {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #991B1B;
}

.alert-close-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
}

/* Responsive */
@media (max-width: 991px) {
    .brand-identity-top {
        grid-template-columns: 1fr;
    }
    .pwa-preview-screen {
        grid-template-columns: 1fr;
    }
    .preview-meta-chips {
        grid-template-columns: 1fr;
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
    .pwa-diag-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .review-grid-3 {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .step-indicator-pill {
        display: none !important;
    }
    .profile-header-main {
        flex-direction: column;
        text-align: center;
    }
    .profile-tags-row {
        justify-content: center;
    }
    .profile-title-row {
        justify-content: center;
    }
    .media-action-buttons {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
    }
    .media-helper-note {
        text-align: center !important;
    }
    .pwa-diag-grid {
        grid-template-columns: 1fr;
    }
    .wizard-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem !important;
        padding: 1rem 1.25rem !important;
    }
    .wizard-actions-left {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
    }
    .wizard-actions-left .step-guide-text {
        text-align: center !important;
        justify-content: center !important;
        font-size: 0.8rem !important;
    }
    .wizard-actions-right {
        flex-direction: column;
        width: 100%;
        gap: 0.75rem !important;
    }
    .wizard-actions-right button,
    .wizard-actions-left button {
        width: 100% !important;
        height: 48px !important;
        min-height: 48px !important;
        max-height: 48px !important;
        padding: 0 1.25rem !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        flex: none !important;
        letter-spacing: 0.01em !important;
    }
    .app-name-input-wrapper {
        flex-direction: column;
        gap: 0.5rem;
    }
    .btn-input-revert {
        position: static;
        transform: none;
        align-self: flex-start;
    }
    .main-wrapper:has(.company-profile-container) .main-content,
    .main-content:has(.company-profile-container) {
        padding-bottom: 1rem !important;
    }
    .main-wrapper:has(.company-profile-container) .app-footer {
        margin-top: 0 !important;
        padding-top: 1rem !important;
        padding-bottom: calc(64px + 16px + env(safe-area-inset-bottom, 0px)) !important;
    }
}

@media (max-width: 480px) {
    .modern-stepper-container {
        padding: 0.75rem 0.5rem;
        border-radius: 16px;
    }
    .modern-step-item {
        padding: 0.2rem 0.25rem !important;
    }
    .modern-step-circle {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 0.82rem !important;
        margin-bottom: 0.25rem !important;
    }
    .modern-step-connector {
        margin: -0.95rem 0.2rem 0 0.2rem !important;
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

<script>
// Notification Helper
function showWizardNotice(msg, type = 'error') {
    if (typeof window.vgNotify === 'function') {
        window.vgNotify(msg, type);
    } else if (typeof window.vgToast === 'function') {
        window.vgToast(msg, type);
    } else if (typeof window.showToast === 'function') {
        window.showToast(msg, type);
    } else if (typeof window.showToursToast === 'function') {
        window.showToursToast(msg, type);
    } else {
        console.warn(msg);
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Wizard State & Step Navigation
let currentStep = <?= $initialStep ?>;
const totalSteps = 2;
const companyNameVal = <?= json_encode($companyNameVal) ?>;
const defaultCompanyLogoUrl = <?= json_encode($defaultCompanyLogoUrl) ?>;
const pwaPreviewUrl = <?= json_encode($pwaPreviewUrl) ?>;
const currentSavedVersion = <?= json_encode($currentSavedVersion) ?>;
window.hasSavedAppIcon = <?= !empty($appIconUrl) ? 'true' : 'false' ?>;
window.defaultCompanyLogoUrl = defaultCompanyLogoUrl;
window.pwaPreviewUrl = pwaPreviewUrl;

function compareSemVer(v1, v2) {
    if (!v1 || !v2) return 0;
    const clean1 = ('' + v1).trim().replace(/^v/i, '');
    const clean2 = ('' + v2).trim().replace(/^v/i, '');
    const p1 = clean1.split('.').map(n => parseInt(n, 10) || 0);
    const p2 = clean2.split('.').map(n => parseInt(n, 10) || 0);
    for (let i = 0; i < Math.max(p1.length, p2.length); i++) {
        const num1 = p1[i] || 0;
        const num2 = p2[i] || 0;
        if (num1 > num2) return 1;
        if (num1 < num2) return -1;
    }
    return 0;
}

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

    if (activeStep === 2) {
        updateAppReviewDossier();
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

    const container = document.querySelector('.company-profile-container');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(stepNum) {
    const panel = document.getElementById('wizardPanel' + stepNum);
    if (!panel) return true;

    let isValid = true;
    let firstInvalid = null;

    if (stepNum === 1) {
        // Validate App Version (Mandatory Semantic Version & Non-decreasing)
        const versionField = document.getElementById('app_version_input');
        if (versionField) {
            const semverRegex = /^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/;
            const vVal = versionField.value.trim();
            const noteErr = document.getElementById('versionNoteErr');
            const noteOk = document.getElementById('versionNoteOk');
            const noteErrText = document.getElementById('versionNoteErrText');

            if (!vVal || !semverRegex.test(vVal)) {
                isValid = false;
                versionField.style.borderColor = '#EF4444';
                versionField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                firstInvalid = versionField;
                if (noteErr) noteErr.style.display = 'flex';
                if (noteOk) noteOk.style.display = 'none';
                if (noteErrText) noteErrText.textContent = 'Please enter a valid semantic version (e.g. 2.4.0 or 3.0.1).';
                showWizardNotice('Please enter a valid semantic version (e.g. 2.4.0).', 'error');
            } else if (currentSavedVersion && currentSavedVersion !== '0.0.0' && compareSemVer(vVal, currentSavedVersion) < 0) {
                isValid = false;
                versionField.style.borderColor = '#EF4444';
                versionField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                firstInvalid = versionField;
                if (noteErr) noteErr.style.display = 'flex';
                if (noteOk) noteOk.style.display = 'none';
                if (noteErrText) noteErrText.textContent = 'Version cannot be decreased. Current version is ' + currentSavedVersion + '. Version can only be increased or kept unchanged.';
                showWizardNotice('Version cannot be decreased below ' + currentSavedVersion + '.', 'error');
            }
        }

        // Validate App Name (Optional, but if filled must be between 2 and 150 chars)
        const nameField = document.getElementById('app_name_input');
        if (nameField && nameField.value.trim() !== '') {
            const nVal = nameField.value.trim();
            if (nVal.length < 2 || nVal.length > 150) {
                isValid = false;
                nameField.style.borderColor = '#EF4444';
                nameField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                if (!firstInvalid) firstInvalid = nameField;
            }
        }
    }

    if (!isValid && firstInvalid) {
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

function updateAppReviewDossier() {
    const appInput = document.getElementById('app_name_input');
    const versionInput = document.getElementById('app_version_input');
    const sourceInput = document.getElementById('app_name_source_input');
    const iconSourceInput = document.getElementById('app_icon_source_input');
    const previewImg = document.getElementById('pwaPreviewImg');

    const appName = appInput ? (appInput.value.trim() || companyNameVal) : companyNameVal;
    const isCustomName = sourceInput && sourceInput.value === 'custom' && appInput && appInput.value.trim() !== '';
    const version = versionInput ? versionInput.value.trim() : '2.4.0';
    const isCustomIcon = iconSourceInput && iconSourceInput.value === 'custom';

    const revName = document.getElementById('revAppName');
    const revNameSub = document.getElementById('revAppNameSource');
    const revVer = document.getElementById('revAppVersion');
    const revIcon = document.getElementById('revAppIcon');
    const mockupTitle = document.getElementById('mockupAppTitle');
    const mockupVersion = document.getElementById('mockupAppVersion');
    const mockupIcon = document.getElementById('mockupIconImg');

    if (revName) revName.textContent = appName;
    if (revNameSub) revNameSub.textContent = isCustomName ? 'Custom Override Active' : 'Inherited from Company Name';
    if (revVer) revVer.textContent = 'v' + (version || '0.0.0');
    if (revIcon) revIcon.textContent = isCustomIcon ? 'Custom Uploaded Icon' : 'Company Logo (Default)';
    if (mockupTitle) mockupTitle.textContent = appName;
    if (mockupVersion) mockupVersion.textContent = 'v' + (version || '0.0.0');
    if (mockupIcon && previewImg && previewImg.src) {
        mockupIcon.src = previewImg.src;
    }
}

// App Identity Handlers
function onAppNameInput(val) {
    const clean = val.trim();
    const headerTitle = document.getElementById('headerAppName');
    const badge = document.getElementById('appNameBadge');
    const btnReset = document.getElementById('btnResetAppName');
    const sourceInput = document.getElementById('app_name_source_input');
    const appInput = document.getElementById('app_name_input');
    const mockupTitle = document.getElementById('mockupAppTitle');

    if (clean === '' || clean.toLowerCase() === companyNameVal.toLowerCase()) {
        if (headerTitle) headerTitle.textContent = companyNameVal;
        if (mockupTitle) mockupTitle.textContent = companyNameVal;
        if (badge) {
            badge.className = 'app-identity-badge is-default';
            badge.innerHTML = '<i class="fa-solid fa-building"></i> <span>Default: ' + escapeHtml(companyNameVal) + '</span>';
        }
        if (btnReset) btnReset.style.display = 'none';
        if (sourceInput) sourceInput.value = 'company_name';
        if (appInput) {
            appInput.classList.remove('is-custom-active');
            appInput.style.borderColor = '';
            appInput.style.boxShadow = '';
        }
    } else {
        if (headerTitle) headerTitle.textContent = clean;
        if (mockupTitle) mockupTitle.textContent = clean;
        if (badge) {
            badge.className = 'app-identity-badge is-custom';
            badge.innerHTML = '<i class="fa-solid fa-pen-fancy"></i> <span>Custom Override Active</span>';
        }
        if (btnReset) btnReset.style.display = 'inline-flex';
        if (sourceInput) sourceInput.value = 'custom';
        if (appInput) {
            appInput.classList.add('is-custom-active');
            appInput.style.borderColor = '';
            appInput.style.boxShadow = '';
        }
    }
}

function resetToCompanyName() {
    const appInput = document.getElementById('app_name_input');
    if (appInput) {
        appInput.value = '';
        onAppNameInput('');
        appInput.focus();
    }
}

// App Version Handlers
function onAppVersionInput(val) {
    const clean = val.trim();
    const mockupVer = document.getElementById('mockupAppVersion');
    if (mockupVer) mockupVer.textContent = 'v' + (clean || '0.0.0');

    const noteOk = document.getElementById('versionNoteOk');
    const noteErr = document.getElementById('versionNoteErr');
    const noteErrText = document.getElementById('versionNoteErrText');
    const versionField = document.getElementById('app_version_input');
    const semverRegex = /^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/;

    if (!clean || !semverRegex.test(clean)) {
        if (noteOk) noteOk.style.display = 'none';
        if (noteErr) noteErr.style.display = 'flex';
        if (noteErrText) noteErrText.textContent = 'Please enter a valid semantic version (e.g. 2.4.0 or 3.0.1).';
        if (versionField) {
            versionField.style.borderColor = '#EF4444';
            versionField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
        }
    } else if (currentSavedVersion && currentSavedVersion !== '0.0.0' && compareSemVer(clean, currentSavedVersion) < 0) {
        if (noteOk) noteOk.style.display = 'none';
        if (noteErr) noteErr.style.display = 'flex';
        if (noteErrText) noteErrText.textContent = 'Version cannot be decreased. Current version is ' + currentSavedVersion + '. Version can only be increased or kept unchanged.';
        if (versionField) {
            versionField.style.borderColor = '#EF4444';
            versionField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
        }
    } else {
        if (noteOk) noteOk.style.display = 'flex';
        if (noteErr) noteErr.style.display = 'none';
        if (versionField) {
            versionField.style.borderColor = '';
            versionField.style.boxShadow = '';
        }
    }
}

// PWA App Icon Handlers
function handleAppIconChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showWizardNotice('App icon exceeds 5MB size limit.', 'error');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('pwaPreviewImg');
        const ph = document.getElementById('appIconPlaceholder');
        const headerImg = document.getElementById('headerAppIconImg');
        const headerIconText = document.getElementById('headerIconSourceText');
        const mockupIcon = document.getElementById('mockupIconImg');
        const btnRemove = document.getElementById('btnRemoveAppIcon');
        const btnUndo = document.getElementById('btnUndoAppIcon');
        const pill = document.getElementById('appIconRemovedPill');
        const uploadBtnText = document.getElementById('appIconUploadBtnText');
        const flagRemove = document.getElementById('flag_remove_app_icon');
        const sourceInput = document.getElementById('app_icon_source_input');

        if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
        if (headerImg) headerImg.src = e.target.result;
        if (mockupIcon) mockupIcon.src = e.target.result;
        if (headerIconText) headerIconText.textContent = 'Custom App Icon';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (uploadBtnText) uploadBtnText.textContent = 'Change Icon';
        if (flagRemove) flagRemove.value = '0';
        if (sourceInput) sourceInput.value = 'custom';
    };
    reader.readAsDataURL(file);
}

async function removeAppIconMedia() {
    const flag = document.getElementById('flag_remove_app_icon');
    const sourceInput = document.getElementById('app_icon_source_input');
    const inp = document.getElementById('app_icon_input');
    const preview = document.getElementById('pwaPreviewImg');
    const ph = document.getElementById('appIconPlaceholder');
    const headerImg = document.getElementById('headerAppIconImg');
    const headerIconText = document.getElementById('headerIconSourceText');
    const mockupIcon = document.getElementById('mockupIconImg');
    const btnRemove = document.getElementById('btnRemoveAppIcon');
    const btnUndo = document.getElementById('btnUndoAppIcon');
    const pill = document.getElementById('appIconRemovedPill');
    const uploadBtnText = document.getElementById('appIconUploadBtnText');

    // Reset input file and set remove flag
    if (inp) inp.value = '';
    if (flag) flag.value = '1';
    if (sourceInput) sourceInput.value = 'company_logo';

    const fallbackUrl = window.defaultCompanyLogoUrl || window.pwaPreviewUrl || '';

    // Immediately update UI preview to default company logo
    if (preview) {
        if (fallbackUrl) {
            preview.src = fallbackUrl;
            preview.style.display = 'block';
            if (ph) ph.style.display = 'none';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            if (ph) ph.style.display = 'flex';
        }
    }
    if (headerImg && fallbackUrl) headerImg.src = fallbackUrl;
    if (mockupIcon && fallbackUrl) mockupIcon.src = fallbackUrl;
    if (headerIconText) headerIconText.textContent = 'Company Logo (Default)';
    if (btnRemove) btnRemove.style.display = 'none';
    if (btnUndo) btnUndo.style.display = 'none';
    if (pill) pill.style.display = 'none';
    if (uploadBtnText) uploadBtnText.textContent = 'Upload Icon';

    if (window.hasSavedAppIcon) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_csrf_token"]')?.value
                || document.querySelector('input[name="csrf_token"]')?.value || '';

            const res = await fetch('<?= url('/office/company/remove-app-icon') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _csrf_token: csrfToken })
            });

            const data = await res.json().catch(() => null);

            if (res.ok && data && data.success) {
                window.hasSavedAppIcon = false;
                if (flag) flag.value = '0';
                if (data.fallback_logo_url) {
                    window.defaultCompanyLogoUrl = data.fallback_logo_url;
                    if (preview) preview.src = data.fallback_logo_url;
                    if (headerImg) headerImg.src = data.fallback_logo_url;
                    if (mockupIcon) mockupIcon.src = data.fallback_logo_url;
                }
                showWizardNotice(data.message || 'PWA App icon removed successfully.', 'success');
            } else {
                let errText = (data && data.message) ? data.message : '';
                if (!errText && data && data.errors) {
                    const firstErr = Array.isArray(data.errors) ? data.errors[0] : Object.values(data.errors)[0];
                    errText = Array.isArray(firstErr) ? firstErr[0] : firstErr;
                }
                showWizardNotice(errText || 'Unable to remove the app icon. Please try again.', 'error');
            }
        } catch (err) {
            console.error('Failed to remove app icon:', err);
            showWizardNotice('Unable to remove the app icon. Please try again.', 'error');
        }
    } else {
        if (flag) flag.value = '0';
        showWizardNotice('App icon removed successfully.', 'success');
    }
}

function undoAppIconRemoval(origUrl) {
    const flag = document.getElementById('flag_remove_app_icon');
    if (flag) flag.value = '0';
    const sourceInput = document.getElementById('app_icon_source_input');
    if (sourceInput) sourceInput.value = 'custom';

    const preview = document.getElementById('pwaPreviewImg');
    const headerImg = document.getElementById('headerAppIconImg');
    const headerIconText = document.getElementById('headerIconSourceText');
    const mockupIcon = document.getElementById('mockupIconImg');
    const btnRemove = document.getElementById('btnRemoveAppIcon');
    const btnUndo = document.getElementById('btnUndoAppIcon');
    const pill = document.getElementById('appIconRemovedPill');
    const uploadBtnText = document.getElementById('appIconUploadBtnText');

    if (origUrl) {
        if (preview) { preview.src = origUrl; preview.style.display = 'block'; }
        if (headerImg) headerImg.src = origUrl;
        if (mockupIcon) mockupIcon.src = origUrl;
        if (headerIconText) headerIconText.textContent = 'Custom App Icon';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (uploadBtnText) uploadBtnText.textContent = 'Change Icon';
    }
}

// Form Submission Visual Indicator
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('appDetailsForm');
    if (form) {
        form.addEventListener('submit', function () {
            const btn = document.getElementById('btnSaveApp');
            const submitText = document.getElementById('btnSubmitText');
            if (btn) {
                btn.disabled = true;
                if (submitText) submitText.textContent = 'Saving App Settings...';
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving App Settings...</span>';
            }
        });
    }

    // Initialize Stepper UI
    updateStepperUI(currentStep);
});
</script>
