<?php
/**
 * Company Management — Master Edit Company Profile Form
 * Pre-populates existing installation profile with live logo replacement & removal
 */
$existingLogoUrl = !empty($company['logo']) ? storage_url($company['logo']) : null;
?>

<div class="desktop-header-wrapper" style="margin-bottom: 1.5rem;">
    <?= render_page_header([
        'title'       => __('company.edit_company', [], 'Edit Company Profile'),
        'description' => __('company.edit_company_desc', [], 'Update official company information, contact details, and brand logo for this installation.'),
        'image'       => 'header-customers.jpg',
        'badge'       => 'Brand Settings',
        'breadcrumbs' => [
            ['label' => __('sidebar.dashboard', [], 'Dashboard'), 'url' => url('/office/dashboard')],
            ['label' => __('company.management', [], 'Company Management'), 'url' => url('/office/company')],
            ['label' => __('company.edit', [], 'Edit Company'), 'url' => ''],
        ],
        'actions'     => '<a href="' . url('/office/company') . '" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <span>' . e(__('btn.back', [], 'Back to Profile')) . '</span></a>',
    ]) ?>
</div>

<div class="modern-form-wrapper" style="max-width: 900px; margin: 0 auto;">

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
    <form action="<?= url('/office/company/' . $company['id']) ?>" method="POST" id="editCompanyForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="remove_logo" id="remove_logo" value="0">

        <div class="modern-form-card" style="padding: 2rem 2.25rem;">

            <!-- ========================================================================= -->
            <!-- 1. Company Logo Upload Component                                          -->
            <!-- ========================================================================= -->
            <div class="customer-avatar-upload-card" style="margin-bottom: 2rem;">
                <div class="customer-avatar-preview-wrap" id="logoDropZone" onclick="document.getElementById('logo').click()" title="Click to upload company logo" style="border-radius: 14px; width: 96px; height: 96px; background: #F1F5F9; border: 2px dashed #CBD5E1;">
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleLogoChange(this)" onclick="event.stopPropagation()">
                    <input type="hidden" name="logo_base64" id="logo_base64" value="">
                    
                    <div class="avatar-default-icon" id="logoDefaultIcon" style="<?= $existingLogoUrl ? 'display: none;' : '' ?> font-size: 2rem; color: #64748B;">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <img id="logoPreviewImg" src="<?= e($existingLogoUrl ?: '') ?>" alt="Company Logo" style="<?= $existingLogoUrl ? 'display: block;' : 'display: none;' ?> width: 100%; height: 100%; object-fit: contain; border-radius: 12px; padding: 6px;">
                    <div class="avatar-camera-badge" title="Change Logo" style="bottom: -4px; right: -4px;">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <div class="customer-avatar-meta">
                    <div class="customer-avatar-title-row">
                        <span class="customer-avatar-label" style="font-size: 1.05rem; font-weight: 700; color: #0F172A;">Official Company Logo</span>
                        <span class="modern-badge-subtle" style="background: #E2E8F0; color: #475569;">Active Branding</span>
                    </div>
                    <p class="customer-avatar-help" style="margin: 0.35rem 0 0.75rem 0; color: #64748B; font-size: 0.88rem; line-height: 1.45;">
                        Updating this logo will instantly update your sidebar brand, login card, customer email headers, and tax invoice headers. Supported formats: PNG, JPG, WEBP (Max 5MB).
                    </p>
                    <div class="customer-avatar-actions" style="display: flex; gap: 0.65rem; align-items: center;">
                        <button type="button" class="customer-avatar-btn-upload btn btn-sm btn-outline" onclick="document.getElementById('logo').click()" style="padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 600;">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Replace Logo
                        </button>
                        <button type="button" class="customer-avatar-btn-remove btn btn-sm btn-outline-danger" id="btnRemoveLogo" style="<?= $existingLogoUrl ? 'display: inline-flex;' : 'display: none;' ?> padding: 0.45rem 0.85rem; font-size: 0.85rem; color: #DC2626; border-color: #FECACA;" onclick="clearLogoSelection()">
                            <i class="fa-solid fa-trash-can"></i> Remove
                        </button>
                    </div>
                    <?php if (!empty($errors['logo'])): ?>
                        <div class="modern-field-error" style="margin-top: 0.5rem; color: #DC2626; font-size: 0.85rem; font-weight: 600;">
                            <i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['logo'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 2. Primary Information Grid (Name & Tagline)                              -->
            <!-- ========================================================================= -->
            <div class="modern-form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <!-- Company Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="company_name" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Company Name <span class="req" style="color: #EF4444;">*</span>
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-building"></i></span>
                        <input type="text" id="company_name" name="company_name" class="modern-input-pill <?= !empty($errors['company_name']) ? 'is-invalid' : '' ?>" required value="<?= e($old['company_name'] ?? $company['company_name'] ?? '') ?>" placeholder="e.g. MANASWI Tours & Travels" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem;">
                    </div>
                    <?php if (!empty($errors['company_name'])): ?>
                        <div class="modern-field-error" style="color: #DC2626; font-size: 0.82rem; margin-top: 0.35rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['company_name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Tagline -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="tagline" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Tagline / Slogan
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-quote-left"></i></span>
                        <input type="text" id="tagline" name="tagline" class="modern-input-pill" value="<?= e($old['tagline'] ?? $company['tagline'] ?? '') ?>" placeholder="e.g. Executive Luxury Chauffeur & Fleet Operations" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem;">
                    </div>
                    <?php if (!empty($errors['tagline'])): ?>
                        <div class="modern-field-error" style="color: #DC2626; font-size: 0.82rem; margin-top: 0.35rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['tagline'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 3. Contact Details Grid (Official Email & Phone Number)                    -->
            <!-- ========================================================================= -->
            <div class="modern-form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <!-- Official Company Email -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Official Company Email <span class="req" style="color: #EF4444;">*</span>
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" required value="<?= e($old['email'] ?? $company['email'] ?? '') ?>" placeholder="e.g. contact@example.com" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem;">
                    </div>
                    <span style="display: block; font-size: 0.78rem; color: #64748B; margin-top: 0.25rem;">Configured as Reply-To address on outgoing customer notifications.</span>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error" style="color: #DC2626; font-size: 0.82rem; margin-top: 0.35rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Phone Number -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Phone Number
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-phone"></i></span>
                        <input type="text" id="phone" name="phone" class="modern-input-pill <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= e($old['phone'] ?? $company['phone'] ?? '') ?>" placeholder="e.g. +91 98765 43210" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem;">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error" style="color: #DC2626; font-size: 0.82rem; margin-top: 0.35rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 4. Web & Operations Status Grid (Website & Status)                        -->
            <!-- ========================================================================= -->
            <div class="modern-form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <!-- Website URL -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="website" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Official Website
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-globe"></i></span>
                        <input type="text" id="website" name="website" class="modern-input-pill <?= !empty($errors['website']) ? 'is-invalid' : '' ?>" value="<?= e($old['website'] ?? $company['website'] ?? '') ?>" placeholder="e.g. https://www.example.com" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem;">
                    </div>
                    <?php if (!empty($errors['website'])): ?>
                        <div class="modern-field-error" style="color: #DC2626; font-size: 0.82rem; margin-top: 0.35rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['website'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                        Profile Status
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B;"><i class="fa-solid fa-shield-halved"></i></span>
                        <select id="status" name="status" class="modern-select-pill" style="width: 100%; padding-left: 2.75rem; height: 46px; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem; background-color: #FFFFFF;">
                            <option value="active" <?= ($old['status'] ?? $company['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Operating Profile)</option>
                            <option value="inactive" <?= ($old['status'] ?? $company['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Dormant Profile)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 5. Physical Address                                                       -->
            <!-- ========================================================================= -->
            <div class="modern-form-group" style="margin-bottom: 2rem;">
                <label class="modern-form-label" for="address" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.45rem;">
                    Headquarters / Registered Business Address
                </label>
                <div class="modern-input-wrapper" style="position: relative;">
                    <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 1rem; color: #64748B;"><i class="fa-solid fa-location-dot"></i></span>
                    <textarea id="address" name="address" rows="3" class="modern-textarea" placeholder="Street, Building, City, State, Country, Postal Code" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border-radius: 10px; border: 1.5px solid #CBD5E1; font-size: 0.95rem; font-family: inherit; resize: vertical;"><?= e($old['address'] ?? $company['address'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 6. Form Action Buttons                                                    -->
            <!-- ========================================================================= -->
            <div class="modern-form-actions" style="display: flex; justify-content: flex-end; gap: 0.85rem; border-top: 1px solid #E2E8F0; padding-top: 1.5rem;">
                <a href="<?= url('/office/company') ?>" class="btn btn-secondary" style="padding: 0.65rem 1.45rem; font-weight: 600;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="btnUpdateCompany" style="padding: 0.65rem 1.85rem; font-weight: 700; background: #176B5B; border-color: #176B5B;">
                    <i class="fa-solid fa-check"></i> Save Changes &amp; Update Branding
                </button>
            </div>

        </div>
    </form>
</div>

<script>
    function handleLogoChange(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];

        if (file.size > 5 * 1024 * 1024) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Logo file size must be less than 5MB.', 'error');
            } else {
                alert('Logo file size must be less than 5MB.');
            }
            input.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var previewImg = document.getElementById('logoPreviewImg');
            var defaultIcon = document.getElementById('logoDefaultIcon');
            var removeBtn = document.getElementById('btnRemoveLogo');
            var base64Input = document.getElementById('logo_base64');
            var removeFlag = document.getElementById('remove_logo');

            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            if (defaultIcon) {
                defaultIcon.style.display = 'none';
            }
            if (removeBtn) {
                removeBtn.style.display = 'inline-flex';
            }
            if (base64Input) {
                base64Input.value = e.target.result;
            }
            if (removeFlag) {
                removeFlag.value = '0';
            }
        };
        reader.readAsDataURL(file);
    }

    function clearLogoSelection() {
        var input = document.getElementById('logo');
        var previewImg = document.getElementById('logoPreviewImg');
        var defaultIcon = document.getElementById('logoDefaultIcon');
        var removeBtn = document.getElementById('btnRemoveLogo');
        var base64Input = document.getElementById('logo_base64');
        var removeFlag = document.getElementById('remove_logo');

        if (input) input.value = '';
        if (base64Input) base64Input.value = '';
        if (removeFlag) removeFlag.value = '1';
        if (previewImg) {
            previewImg.src = '';
            previewImg.style.display = 'none';
        }
        if (defaultIcon) {
            defaultIcon.style.display = 'block';
        }
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
</script>
