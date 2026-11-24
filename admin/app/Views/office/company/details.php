<?php
/**
 * Professional Step-Wise Company Details Management
 * Guided multi-step wizard for both creating and editing the primary company record.
 *
 * Steps:
 * 1. Company Profile (Logo, Company Name, Owner, Status)
 * 2. Contact Details (Primary Mobile, Alt Mobile, Email, Website)
 * 3. Office Address (Address, City, State, PIN Code)
 * 4. Business & Tax (Tagline, GST Number, PAN Number)
 * 5. Documents & Review (Logo, Paid Stamp, Signature, Signature + Stamp, Review Dossier)
 */

$primaryCompany = $company ?? $primaryCompany ?? null;
$isExisting = !empty($primaryCompany);
$initialStep = !empty($activeStep) ? (int)$activeStep : 1;
if ($initialStep < 1 || $initialStep > 5) {
    $initialStep = 1;
}

// Image URLs from saved profile
$logoUrl = !empty($primaryCompany['logo']) ? storage_url($primaryCompany['logo']) : null;
$paidStampUrl = !empty($primaryCompany['paid_stamp']) ? storage_url($primaryCompany['paid_stamp']) : null;
$signatureUrl = !empty($primaryCompany['signature']) ? storage_url($primaryCompany['signature']) : null;
$sigStampUrl = !empty($primaryCompany['signature_stamp']) ? storage_url($primaryCompany['signature_stamp']) : null;

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

$companyNameVal = $val('company_name', '');
$statusVal = $val('status', 'active');
?>

<div class="company-profile-container modern-form-wrapper">

    <!-- Floating Notifications Emitter (Floating message like other pages; eliminates static banner alerts) -->
    <?php if (!empty($errors)): ?>
        <?php
            $firstErrMsg = (is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: 'Please correct the highlighted errors and try again.';
        ?>
        <script>
            (function() {
                var firstErr = <?= json_encode($firstErrMsg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                var emit = function() {
                    if (typeof window.vgNotify === 'function') {
                        window.vgNotify(firstErr, 'error');
                    } else if (typeof window.vgToast === 'function') {
                        window.vgToast(firstErr, 'error');
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

    <?php $directSuccess = $success ?? null; ?>
    <?php if (!empty($directSuccess)): ?>
        <script>
            (function() {
                var sMsg = <?= json_encode($directSuccess, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                var emit = function() {
                    if (typeof window.vgNotify === 'function') {
                        window.vgNotify(sMsg, 'success');
                    } else if (typeof window.vgToast === 'function') {
                        window.vgToast(sMsg, 'success');
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

    <!-- ========================================================================= -->
    <!-- 2. PROFESSIONAL 5-STEP STEPPER HEADER                                     -->
    <!-- ========================================================================= -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item <?= $initialStep === 1 ? 'active' : ($initialStep > 1 ? 'completed' : '') ?>" data-step-target="1" onclick="wizardGoToStep(1)" title="<?= e(__('tooltip.step_1_company_profile', 'Step 1: Company Profile')) ?>">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Company Profile</span>
                </button>
                <div class="modern-step-connector <?= $initialStep > 1 ? 'completed' : '' ?>" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item <?= $initialStep === 2 ? 'active' : ($initialStep > 2 ? 'completed' : '') ?>" data-step-target="2" onclick="wizardGoToStep(2)" title="<?= e(__('tooltip.step_2_contact_details', 'Step 2: Contact Details')) ?>">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Contact Details</span>
                </button>
                <div class="modern-step-connector <?= $initialStep > 2 ? 'completed' : '' ?>" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item <?= $initialStep === 3 ? 'active' : ($initialStep > 3 ? 'completed' : '') ?>" data-step-target="3" onclick="wizardGoToStep(3)" title="<?= e(__('tooltip.step_3_office_address', 'Step 3: Office Address')) ?>">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Office Address</span>
                </button>
                <div class="modern-step-connector <?= $initialStep > 3 ? 'completed' : '' ?>" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item <?= $initialStep === 4 ? 'active' : ($initialStep > 4 ? 'completed' : '') ?>" data-step-target="4" onclick="wizardGoToStep(4)" title="<?= e(__('tooltip.step_4_business_tax', 'Step 4: Business & Tax')) ?>">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label">Business &amp; Tax</span>
                </button>
                <div class="modern-step-connector <?= $initialStep > 4 ? 'completed' : '' ?>" id="stepConn4"></div>

                <!-- Step 5 -->
                <button type="button" class="modern-step-item <?= $initialStep === 5 ? 'active' : '' ?>" data-step-target="5" onclick="wizardGoToStep(5)" title="<?= e(__('tooltip.step_5_documents_review', 'Step 5: Documents & Review')) ?>">
                    <span class="modern-step-circle">5</span>
                    <span class="modern-step-label">Documents &amp; Save</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. MASTER STEP-WISE FORM                                                  -->
    <!-- ========================================================================= -->
    <form action="<?= url('/office/company/details/save') ?>" method="POST" enctype="multipart/form-data" class="company-section-form" data-section="all" data-no-ajax="true" data-native="true" id="companyDetailsForm" novalidate>
        <?= csrf_field() ?>
        <?php if ($isExisting): ?>
            <input type="hidden" name="id" value="<?= e($primaryCompany['id']) ?>">
        <?php endif; ?>
        <input type="hidden" name="section" value="all">
        <input type="hidden" name="native_submit" value="1">
        <input type="hidden" name="remove_logo" id="flag_remove_logo" value="0">
        <input type="hidden" name="remove_paid_stamp" id="flag_remove_paid_stamp" value="0">
        <input type="hidden" name="remove_signature" id="flag_remove_signature" value="0">
        <input type="hidden" name="remove_signature_stamp" id="flag_remove_signature_stamp" value="0">

        <!-- ===================================================================== -->
        <!-- STEP 1: COMPANY PROFILE                                               -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel1" style="<?= $initialStep === 1 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 1: Company Profile &amp; Identity</h2>
                            <p class="section-subtitle">Official registered enterprise name, brand logo, authorized person and operational status.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 1 of 5</span>
                </div>

                <div class="profile-section-body">
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
                                    <i class="fa-solid fa-trash-can"></i> <span>Remove</span>
                                </button>
                                <button type="button" id="btnUndoLogo" class="btn-media-undo" onclick="undoLogoRemoval('<?= e($logoUrl ?? '') ?>')" style="display: none;">
                                    <i class="fa-solid fa-rotate-left"></i> <span>Undo</span>
                                </button>
                            </div>
                            <p class="media-helper-note">Square or rectangular logo (PNG, JPG, WebP up to 5MB). Transparent background recommended.</p>
                            <?= $fieldError('logo') ?>
                        </div>

                        <!-- Right: Company Fields Grid -->
                        <div class="brand-names-panel">
                            <div class="form-group">
                                <label for="company_name_input" class="control-label font-bold">
                                    Company Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="company_name_input" name="company_name" value="<?= e($companyNameVal) ?>" class="<?= $inputClass('company_name', 'form-control form-control-lg font-bold') ?>" required placeholder="<?= e(__('placeholder.e_g_royal_tours_amp_travels_pv', 'e.g. Royal Tours &amp; Travels Pvt. Ltd.')) ?>" oninput="onCompanyNameChanged(this.value)">
                                <p class="form-helper-text">Official legal or trade business name used across documents, invoices, and contracts.</p>
                                <?= $fieldError('company_name') ?>
                            </div>

                            <div class="two-column-form-grid" style="margin-top: 1rem;">
                                <div class="form-group">
                                    <label for="owner_name_input" class="control-label font-bold">Owner / Contact Person</label>
                                    <input type="text" id="owner_name_input" name="owner_name" value="<?= e($val('owner_name')) ?>" class="<?= $inputClass('owner_name') ?>" placeholder="<?= e(__('placeholder.e_g_farooq_ahmad_director', 'e.g. Farooq Ahmad / Director')) ?>" oninput="onOwnerNameChanged(this.value)">
                                    <p class="form-helper-text">Authorized business representative or principal director.</p>
                                    <?= $fieldError('owner_name') ?>
                                </div>

                                <div class="form-group">
                                    <label for="status_select" class="control-label font-bold">Company Status</label>
                                    <select id="status_select" name="status" class="<?= $inputClass('status', 'form-select form-control') ?>" onchange="onStatusChanged(this.value)">
                                        <option value="active" <?= $statusVal === 'active' ? 'selected' : '' ?>>Active (Operating normally)</option>
                                        <option value="inactive" <?= $statusVal === 'inactive' ? 'selected' : '' ?>>Inactive (Suspended / Inactive)</option>
                                    </select>
                                    <p class="form-helper-text">Controls system availability and operational state.</p>
                                    <?= $fieldError('status') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <span class="step-guide-text"><i class="fa-solid fa-circle-info"></i> Fill in company name and logo to proceed.</span>
                    </div>
                    <div class="wizard-actions-right">
                        <?php if ($isExisting): ?>
                            <button type="submit" class="btn btn-outline-save btn-quick-save" title="<?= e(__('tooltip.save_current_changes_directly', 'Save current changes directly')) ?>">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                            <span>Next: Contact Details</span> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- STEP 2: CONTACT DETAILS                                               -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel2" style="<?= $initialStep === 2 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 2: Contact Details</h2>
                            <p class="section-subtitle">Official communication channels for trip dispatches, invoices, and customer communications.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 2 of 5</span>
                </div>

                <div class="profile-section-body">
                    <div class="two-column-form-grid">
                        <div class="form-group">
                            <label for="phone_input" class="control-label font-bold">Primary Mobile <span class="text-danger">*</span></label>
                            <input type="tel" id="phone_input" name="phone" value="<?= e($val('phone')) ?>" class="<?= $inputClass('phone', 'form-control font-bold') ?>" required placeholder="<?= e(__('placeholder.e_g_91_98765_43210', 'e.g. +91 98765 43210')) ?>" oninput="onPhoneChanged(this.value)">
                            <p class="form-helper-text">Mandatory 10-digit primary mobile used for billing and SMS alerts.</p>
                            <?= $fieldError('phone') ?>
                        </div>

                        <div class="form-group">
                            <label for="alt_phone_input" class="control-label font-bold">Alternate Mobile</label>
                            <input type="tel" id="alt_phone_input" name="alternate_phone" value="<?= e($val('alternate_phone')) ?>" class="<?= $inputClass('alternate_phone') ?>" placeholder="<?= e(__('placeholder.e_g_91_98765_00000', 'e.g. +91 98765 00000')) ?>">
                            <p class="form-helper-text">Secondary emergency or office landline contact number.</p>
                            <?= $fieldError('alternate_phone') ?>
                        </div>

                        <div class="form-group">
                            <label for="email_input" class="control-label font-bold">Official Email</label>
                            <input type="email" id="email_input" name="email" value="<?= e($val('email')) ?>" class="<?= $inputClass('email') ?>" placeholder="<?= e(__('placeholder.e_g_concierge_royaltours_com', 'e.g. concierge@royaltours.com')) ?>">
                            <p class="form-helper-text">Printed on official invoices and customer booking confirmations.</p>
                            <?= $fieldError('email') ?>
                        </div>

                        <div class="form-group">
                            <label for="website_input" class="control-label font-bold">Company Website</label>
                            <input type="url" id="website_input" name="website" value="<?= e($val('website')) ?>" class="<?= $inputClass('website') ?>" placeholder="<?= e(__('placeholder.e_g_https_www_royaltours_com', 'e.g. https://www.royaltours.com')) ?>">
                            <p class="form-helper-text">Official public portal or booking website URL.</p>
                            <?= $fieldError('website') ?>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(1)">
                            <i class="fa-solid fa-arrow-left"></i> <span>Previous</span>
                        </button>
                    </div>
                    <div class="wizard-actions-right">
                        <?php if ($isExisting): ?>
                            <button type="submit" class="btn btn-outline-save btn-quick-save">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(2)">
                            <span>Next: Office Address</span> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- STEP 3: OFFICE ADDRESS                                                -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel3" style="<?= $initialStep === 3 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 3: Registered Office Address</h2>
                            <p class="section-subtitle">Commercial physical address printed on GST tax invoices, contracts, and travel vouchers.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 3 of 5</span>
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
                            <input type="text" id="city_input" name="city" value="<?= e($val('city')) ?>" class="<?= $inputClass('city') ?>" required placeholder="<?= e(__('placeholder.e_g_srinagar', 'e.g. Srinagar')) ?>" oninput="onLocationChanged()">
                            <?= $fieldError('city') ?>
                        </div>

                        <div class="form-group">
                            <label for="state_input" class="control-label font-bold">State / UT</label>
                            <input type="text" id="state_input" name="state" value="<?= e($val('state')) ?>" class="<?= $inputClass('state') ?>" placeholder="<?= e(__('placeholder.e_g_jammu_and_kashmir', 'e.g. Jammu and Kashmir')) ?>" oninput="onLocationChanged()">
                            <?= $fieldError('state') ?>
                        </div>

                        <div class="form-group">
                            <label for="pin_code_input" class="control-label font-bold">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" id="pin_code_input" name="pin_code" value="<?= e($val('pin_code')) ?>" class="<?= $inputClass('pin_code', 'form-control font-mono font-bold') ?>" maxlength="6" required placeholder="<?= e(__('placeholder.e_g_190001', 'e.g. 190001')) ?>">
                            <p class="form-helper-text">Mandatory 6-digit postal code.</p>
                            <?= $fieldError('pin_code') ?>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                            <i class="fa-solid fa-arrow-left"></i> <span>Previous</span>
                        </button>
                    </div>
                    <div class="wizard-actions-right">
                        <?php if ($isExisting): ?>
                            <button type="submit" class="btn btn-outline-save btn-quick-save">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(3)">
                            <span>Next: Business &amp; Tax</span> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- STEP 4: BUSINESS & TAX                                                -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel4" style="<?= $initialStep === 4 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 4: Business &amp; Tax Information</h2>
                            <p class="section-subtitle">Tax registration identification numbers and business marketing tagline.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 4 of 5</span>
                </div>

                <div class="profile-section-body">
                    <div class="two-column-form-grid">
                        <div class="form-group full-width-span">
                            <label for="tagline_input" class="control-label font-bold">Company Tagline</label>
                            <input type="text" id="tagline_input" name="tagline" value="<?= e($val('tagline')) ?>" class="<?= $inputClass('tagline') ?>" placeholder="<?= e(__('placeholder.e_g_luxury_journeys_exceptiona', 'e.g. Luxury Journeys, Exceptional Memories')) ?>">
                            <p class="form-helper-text">Short motto or brand promise shown on invoices and email signatures.</p>
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
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(3)">
                            <i class="fa-solid fa-arrow-left"></i> <span>Previous</span>
                        </button>
                    </div>
                    <div class="wizard-actions-right">
                        <?php if ($isExisting): ?>
                            <button type="submit" class="btn btn-outline-save btn-quick-save">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Save Changes</span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="modern-btn-next" onclick="wizardNextStep(4)">
                            <span>Next: Documents &amp; Review</span> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- STEP 5: DOCUMENTS & REVIEW                                            -->
        <!-- ===================================================================== -->
        <div class="modern-step-panel" id="wizardPanel5" style="<?= $initialStep === 5 ? 'display: block;' : 'display: none;' ?>">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon-badge" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                            <i class="fa-solid fa-stamp"></i>
                        </div>
                        <div>
                            <h2 class="section-title">Step 5: Invoice &amp; Document Branding</h2>
                            <p class="section-subtitle">Official seals, digital signatures, and stamps automatically rendered on billing invoices and payment receipts.</p>
                        </div>
                    </div>
                    <span class="step-indicator-pill">Step 5 of 5</span>
                </div>

                <div class="profile-section-body">
                    <!-- Document Cards Grid -->
                    <div class="doc-cards-grid">
                        <!-- Doc 1: Paid Stamp -->
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

                        <!-- Doc 2: Authorized Signature -->
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

                        <!-- Doc 3: Signature + Stamp -->
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

                    <!-- LIVE REVIEW DOSSIER -->
                    <div class="wizard-review-container">
                        <div class="review-box-header">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Profile Summary &amp; Verification</span>
                        </div>
                        <div class="review-grid-3">
                            <div class="review-card-item">
                                <span class="review-card-lbl">Enterprise</span>
                                <strong class="review-card-val" id="revCompanyName"><?= e($companyNameVal ?: '—') ?></strong>
                                <span class="review-card-sub" id="revOwner"><?= e($val('owner_name') ? 'Owner: ' . $val('owner_name') : 'Owner not set') ?></span>
                            </div>
                            <div class="review-card-item">
                                <span class="review-card-lbl">Direct Contact</span>
                                <strong class="review-card-val" id="revPhone"><?= e($val('phone') ?: '—') ?></strong>
                                <span class="review-card-sub" id="revEmail"><?= e($val('email') ?: 'No email set') ?></span>
                            </div>
                            <div class="review-card-item">
                                <span class="review-card-lbl">Office Location</span>
                                <strong class="review-card-val" id="revCity"><?= e($val('city') ? $val('city') . ' (' . $val('pin_code') . ')' : '—') ?></strong>
                                <span class="review-card-sub" id="revState"><?= e($val('state') ?: '—') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions wizard-actions">
                    <div class="wizard-actions-left">
                        <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(4)">
                            <i class="fa-solid fa-arrow-left"></i> <span>Previous</span>
                        </button>
                    </div>
                    <div class="wizard-actions-right">
                        <button type="submit" class="modern-btn-submit" id="btnSubmitCompanyDetails">
                            <i class="fa-solid fa-check"></i>
                            <span id="btnSubmitText"><?= $isExisting ? 'Save Company Details' : 'Complete Setup & Save' ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

<!-- ========================================================================= -->
<!-- STYLES & INTERACTION SCRIPT                                               -->
<!-- ========================================================================= -->
<style>
.company-profile-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
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
    margin-bottom: -0.25rem;
}

.breadcrumb-separator {
    font-size: 0.65rem;
    color: #94A3B8;
}

.breadcrumb-active {
    color: #176B5B;
    font-weight: 700;
}

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

.profile-badge-entity {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.7rem;
    border-radius: 50px;
    font-size: 0.74rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.15);
    color: #FFFFFF;
    border: 1px solid rgba(255, 255, 255, 0.25);
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

.profile-tags-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.profile-tag-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.85);
}

.profile-tag-item i {
    color: #A7F3D0;
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
    max-width: 960px;
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

.step-indicator-pill {
    font-size: 0.76rem;
    font-weight: 700;
    color: #176B5B;
    background: #E8F4F0;
    border: 1px solid #B2D8CF;
    border-radius: 50px;
    padding: 0.25rem 0.75rem;
}

/* Form Panel Card */
.profile-section-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
    width: 100%;
}

.profile-section-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid #F1F5F9;
    background: #FAFCFE;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.section-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
}

.section-subtitle {
    font-size: 0.82rem;
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
    font-size: 0.92rem;
    padding: 0 1.35rem;
    height: 48px;
    border-radius: 50px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.btn-outline-save:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}

.modern-btn-prev {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    height: 48px;
    padding: 0 24px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 50px;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-sizing: border-box;
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
    gap: 0.5rem;
    height: 48px;
    padding: 0 32px;
    background: linear-gradient(135deg, #176B5B 0%, #125748 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 50px;
    color: #FFFFFF !important;
    font-size: 0.96rem;
    font-weight: 700;
    letter-spacing: 0.015em;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(23, 107, 91, 0.35);
    transition: all 0.25s ease;
    box-sizing: border-box;
}

.modern-btn-next:hover {
    background: linear-gradient(135deg, #1A7E6C 0%, #146151 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(23, 107, 91, 0.45);
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

/* Grids & Inputs */
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
    font-size: 2.4rem;
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
    top: 8px;
    right: 8px;
    background: #EF4444;
    color: #FFFFFF;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
}

.two-column-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}

.three-column-form-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}

.full-width-span {
    grid-column: 1 / -1;
}

.form-group {
    margin-bottom: 0.5rem;
}

.control-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.86rem;
    color: #0C2B52;
}

.font-bold {
    font-weight: 700;
}

.form-control {
    width: 100%;
    box-sizing: border-box;
    border: 1.5px solid #CBD5E1;
    border-radius: 12px;
    padding: 0.65rem 0.95rem;
    font-size: 0.9rem;
    color: #0C2B52;
    background: #FFFFFF;
    transition: all 0.15s ease;
}

.form-control:focus {
    border-color: #176B5B;
    outline: none;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15);
}

.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 0.98rem;
}

.form-helper-text {
    margin: 0.35rem 0 0 0;
    font-size: 0.75rem;
    color: #64748B;
}

.field-error-msg {
    margin-top: 0.35rem;
    font-size: 0.75rem;
    color: #DC2626;
    font-weight: 600;
}

/* Documents Grid */
.doc-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}

.doc-item-card {
    background: #F8FAFD;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.doc-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.doc-item-name {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0C2B52;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.doc-status-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
}

.doc-status-badge.attached {
    background: #DCFCE7;
    color: #15803D;
}

.doc-status-badge.empty {
    background: #F1F5F9;
    color: #64748B;
}

.doc-item-thumb {
    width: 100%;
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
    gap: 0.3rem;
    color: #94A3B8;
    font-size: 0.75rem;
}

.doc-placeholder-icon i {
    font-size: 1.5rem;
}

.doc-item-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-doc-upload {
    flex: 1;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0C2B52;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.35rem 0.5rem;
    border-radius: 7px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.btn-doc-remove, .btn-doc-undo {
    background: #FEF2F2;
    border: 1.5px solid #FECACA;
    color: #DC2626;
    font-size: 0.78rem;
    padding: 0.35rem 0.55rem;
    border-radius: 7px;
    cursor: pointer;
}

.btn-doc-undo {
    background: #EFF6FF;
    border-color: #BFDBFE;
    color: #2563EB;
}

/* Review Container */
.wizard-review-container {
    margin-top: 1.75rem;
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
    color: #176B5B;
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

/* Alerts */
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
    .brand-identity-top {
        grid-template-columns: 1fr;
    }
    .doc-cards-grid {
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
    .two-column-form-grid,
    .three-column-form-grid {
        grid-template-columns: 1fr;
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
    .company-profile-container .modern-form-actions.wizard-actions {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
        padding: 1rem 1.25rem !important;
    }
    .company-profile-container .wizard-actions-left {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
    }
    .company-profile-container .wizard-actions-left .step-guide-text {
        text-align: center !important;
        justify-content: center !important;
        font-size: 0.8rem !important;
    }
    .company-profile-container .wizard-actions-right {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        gap: 0.75rem !important;
    }
    .company-profile-container .wizard-actions-right button,
    .company-profile-container .wizard-actions-left button {
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
    .doc-cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Notification Helper (replaces window.alert)
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

// Wizard State & Step Navigation
let currentStep = <?= $initialStep ?>;
const totalSteps = 5;

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

    // Update live review dossier if on step 5
    if (activeStep === 5) {
        updateReviewDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all step panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target step panel
    const targetPanel = document.getElementById('wizardPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateStepperUI(targetStep);

    // Smooth scroll to top of form
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
        const nameField = document.getElementById('company_name_input');
        if (nameField && (!nameField.value || nameField.value.trim().length < 2)) {
            isValid = false;
            nameField.style.borderColor = '#EF4444';
            nameField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
            firstInvalid = nameField;
        }
    } else if (stepNum === 2) {
        const phoneField = document.getElementById('phone_input');
        if (phoneField) {
            const digits = (phoneField.value || '').replace(/\D/g, '');
            if (!phoneField.value.trim() || digits.length < 10) {
                isValid = false;
                phoneField.style.borderColor = '#EF4444';
                phoneField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                firstInvalid = phoneField;
            }
        }
    } else if (stepNum === 3) {
        const addrField = document.getElementById('address_input');
        const cityField = document.getElementById('city_input');
        const pinField = document.getElementById('pin_code_input');

        if (addrField && !addrField.value.trim()) {
            isValid = false;
            addrField.style.borderColor = '#EF4444';
            addrField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
            if (!firstInvalid) firstInvalid = addrField;
        }
        if (cityField && !cityField.value.trim()) {
            isValid = false;
            cityField.style.borderColor = '#EF4444';
            cityField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
            if (!firstInvalid) firstInvalid = cityField;
        }
        if (pinField) {
            const pinVal = pinField.value.trim();
            if (!pinVal || !/^[0-9]{6}$/.test(pinVal)) {
                isValid = false;
                pinField.style.borderColor = '#EF4444';
                pinField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                if (!firstInvalid) firstInvalid = pinField;
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

function updateReviewDossier() {
    const compName = document.getElementById('company_name_input')?.value.trim() || '—';
    const owner = document.getElementById('owner_name_input')?.value.trim();
    const phone = document.getElementById('phone_input')?.value.trim() || '—';
    const email = document.getElementById('email_input')?.value.trim();
    const city = document.getElementById('city_input')?.value.trim() || '—';
    const state = document.getElementById('state_input')?.value.trim();
    const pin = document.getElementById('pin_code_input')?.value.trim() || '';

    const revName = document.getElementById('revCompanyName');
    const revOwner = document.getElementById('revOwner');
    const revPhone = document.getElementById('revPhone');
    const revEmail = document.getElementById('revEmail');
    const revCity = document.getElementById('revCity');
    const revState = document.getElementById('revState');

    if (revName) revName.textContent = compName;
    if (revOwner) revOwner.textContent = owner ? 'Owner: ' + owner : 'Owner not set';
    if (revPhone) revPhone.textContent = phone;
    if (revEmail) revEmail.textContent = email || 'No email set';
    if (revCity) revCity.textContent = city + (pin ? ' (' + pin + ')' : '');
    if (revState) revState.textContent = state || '—';
}

// Live Header Feedback Handlers
function onCompanyNameChanged(val) {
    const headerTitle = document.getElementById('headerCompanyName');
    if (headerTitle) {
        headerTitle.textContent = val.trim() || 'Company Details';
    }
    const monogram = document.getElementById('headerLogoMonogram');
    if (monogram) {
        const letter = (val.trim() || 'C').charAt(0).toUpperCase();
        monogram.innerHTML = `<span>${letter}</span>`;
    }
}

function onOwnerNameChanged(val) {
    const headerOwner = document.getElementById('headerOwnerName');
    if (headerOwner) {
        headerOwner.textContent = val.trim() || 'Not specified';
    }
}

function onLocationChanged() {
    const city = document.getElementById('city_input')?.value.trim();
    const state = document.getElementById('state_input')?.value.trim();
    const headerLoc = document.getElementById('headerLocation');
    if (headerLoc) {
        headerLoc.textContent = (city ? city : '—') + (state ? ', ' + state : '');
    }
}

function onPhoneChanged(val) {
    const headerPhone = document.getElementById('headerPhone');
    if (headerPhone) {
        headerPhone.textContent = val.trim() || '—';
    }
}

function onStatusChanged(val) {
    const badge = document.getElementById('headerStatusBadge');
    const text = document.getElementById('headerStatusText');
    if (badge && text) {
        if (val === 'active') {
            badge.className = 'profile-status-pill is-active';
            text.textContent = 'Active';
        } else {
            badge.className = 'profile-status-pill is-inactive';
            text.textContent = 'Inactive';
        }
    }
}

// Logo Handlers
window.hasSavedCompanyLogo = <?= !empty($logoUrl) ? 'true' : 'false' ?>;

function handleLogoChange(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showWizardNotice('Logo exceeds maximum size of 5MB.', 'error');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('logoPreviewImg');
        const ph = document.getElementById('logoPlaceholder');
        const btnRemove = document.getElementById('btnRemoveLogo');
        const btnUndo = document.getElementById('btnUndoLogo');
        const pill = document.getElementById('logoRemovedPill');
        const headerImg = document.getElementById('headerLogoImg');
        const headerMonogram = document.getElementById('headerLogoMonogram');
        const uploadBtnText = document.getElementById('logoUploadBtnText');
        const flag = document.getElementById('flag_remove_logo');

        if (flag) flag.value = '0';
        if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
        if (ph) ph.style.display = 'none';
        if (btnRemove) btnRemove.style.display = 'inline-flex';
        if (btnUndo) btnUndo.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (uploadBtnText) uploadBtnText.textContent = 'Change Logo';

        if (headerImg) {
            headerImg.src = e.target.result;
            headerImg.style.display = 'block';
        } else if (headerMonogram) {
            headerMonogram.outerHTML = `<img src="${e.target.result}" alt="Company Logo" class="profile-avatar-img" id="headerLogoImg">`;
        }
    };
    reader.readAsDataURL(file);
}

async function removeLogoMedia() {
    const preview = document.getElementById('logoPreviewImg');
    const ph = document.getElementById('logoPlaceholder');
    const inp = document.getElementById('logo_input');
    const btnRemove = document.getElementById('btnRemoveLogo');
    const btnUndo = document.getElementById('btnUndoLogo');
    const pill = document.getElementById('logoRemovedPill');
    const uploadBtnText = document.getElementById('logoUploadBtnText');
    const flag = document.getElementById('flag_remove_logo');

    // Immediately remove from UI preview
    if (flag) flag.value = '1';
    if (inp) inp.value = '';
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
    if (ph) ph.style.display = 'flex';
    if (btnRemove) btnRemove.style.display = 'none';
    if (btnUndo) btnUndo.style.display = 'none';
    if (pill) pill.style.display = 'none';
    if (uploadBtnText) uploadBtnText.textContent = 'Upload Logo';

    const headerAvatarWrap = document.querySelector('.profile-header-avatar');
    if (headerAvatarWrap) {
        const firstChar = (document.getElementById('company_name_input')?.value || 'C').trim().charAt(0).toUpperCase() || 'C';
        headerAvatarWrap.innerHTML = `<div class="profile-avatar-monogram" id="headerLogoMonogram">${firstChar}</div>`;
    }

    if (window.hasSavedCompanyLogo) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="csrf_token"]')?.value || '';

            const res = await fetch('<?= url('/office/company/remove-logo') ?>', {
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
                window.hasSavedCompanyLogo = false;
                if (flag) flag.value = '0';
                showWizardNotice(data.message || 'Logo removed successfully.', 'success');
            } else {
                showWizardNotice((data && data.message) ? data.message : 'Unable to remove the logo. Please try again.', 'error');
            }
        } catch (err) {
            console.error('Failed to remove company logo:', err);
            showWizardNotice('Unable to remove the logo. Please try again.', 'error');
        }
    } else {
        showWizardNotice('Logo removed successfully.', 'success');
    }
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
        if (preview) { preview.src = origUrl; preview.style.display = 'block'; }
    }
}

// Document Images Handlers
function handleDocChange(input, type) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showWizardNotice('File exceeds 5MB size limit.', 'error');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        let previewId = '', phId = '', btnTextId = '', removeBtnId = '', undoBtnId = '', pillId = '', badgeId = '';

        if (type === 'paid_stamp') {
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
    if (type === 'paid_stamp') { flagId = 'flag_remove_paid_stamp'; removeBtnId = 'btnRemoveDocPaidStamp'; undoBtnId = 'btnUndoDocPaidStamp'; pillId = 'docPaidStampRemovedPill'; badgeId = 'badgeDocPaidStamp'; }
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
    if (type === 'paid_stamp') { flagId = 'flag_remove_paid_stamp'; previewId = 'docPaidStampPreview'; phId = 'docPaidStampPlaceholder'; removeBtnId = 'btnRemoveDocPaidStamp'; undoBtnId = 'btnUndoDocPaidStamp'; pillId = 'docPaidStampRemovedPill'; badgeId = 'badgeDocPaidStamp'; }
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

// Form Submission Visual Indicator
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('companyDetailsForm');
    if (form) {
        form.addEventListener('submit', function () {
            const submitBtn = document.getElementById('btnSubmitCompanyDetails');
            const submitText = document.getElementById('btnSubmitText');
            if (submitBtn) {
                submitBtn.disabled = true;
                if (submitText) submitText.textContent = 'Saving Company Details...';
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving Company Details...</span>';
            }
        });
    }

    // Initialize Stepper UI
    updateStepperUI(currentStep);

    // Remove excessive external whitespace before footer (Issue 3)
    function tuneFooterSpacing() {
        const main = document.querySelector('.main-content');
        const footer = document.querySelector('.app-footer');
        if (main && footer) {
            main.style.setProperty('flex', '0 0 auto', 'important');
            main.style.setProperty('padding-bottom', window.innerWidth <= 768 ? '1rem' : '1.5rem', 'important');
            footer.style.setProperty('margin-top', '0', 'important');
            if (window.innerWidth <= 768) {
                footer.style.setProperty('padding-top', '1rem', 'important');
                footer.style.setProperty('padding-bottom', 'calc(64px + 16px + env(safe-area-inset-bottom, 0px))', 'important');
            }
        }
    }
    tuneFooterSpacing();
    window.addEventListener('resize', tuneFooterSpacing);
});
</script>
