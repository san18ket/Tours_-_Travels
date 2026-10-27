<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Modern User Creation Form
 * Pixel-perfect implementation matching master form UI for desktop (lg) and responsive mobile (sm)
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header: Back Button, Title, Subtitle, and 3-Step Stepper -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/users') ?>" class="modern-back-btn" title="Back to User Management" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('user_form.create_title', [], 'Create System Account')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('user_form.create_subtitle', [], 'Provision portal credentials, role authorization, and access controls.')) ?></p>
            </div>
        </div>

        <!-- 3-Step Stepper Track -->
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Identity Linkage</span>
                </button>
                <div class="modern-step-connector" id="usrStepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Role &amp; Privileges</span>
                </button>
                <div class="modern-step-connector" id="usrStepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Account Security</span>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: __('form.validation_errors', [], 'Please check the form for errors.')) ?>;
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
    <form action="<?= url('/office/users') ?>" method="POST" id="userForm" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <!-- Anti-autofill decoy fields -->
        <input type="text" name="fake_username_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="off">
        <input type="password" name="fake_password_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="new-password">

        <!-- ========================================================================= -->
        <!-- STEP 1: Identity & Staff Profile Linkage                                  -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel1">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div class="modern-section-meta" style="flex: 1;">
                        <h2 class="modern-section-title"><?= __('user_form.step1_heading', [], 'Identity & Staff Linkage') ?></h2>
                        <p class="modern-section-sub"><?= __('user_form.step1_subheading', [], 'Attach account to an existing roster employee or provision an independent profile.') ?></p>
                    </div>

                    <!-- Custom Profile Toggle Switch -->
                    <label for="is_custom_user" style="display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; padding: 8px 16px; border-radius: 50px; background: #E8F4F0; border: 1px solid #B2D8CF; font-size: 0.86rem; font-weight: 700; color: #176B5B; margin: 0;">
                        <input type="checkbox" id="is_custom_user" name="is_custom_user" value="1" <?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'checked' : '' ?> onchange="toggleUserSource(this)" style="width: 17px; height: 17px; accent-color: #176B5B; cursor: pointer;">
                        <span><?= __('user_form.create_custom_user', [], 'Custom Profile (No Staff Link)') ?></span>
                    </label>
                </div>

                <!-- Mode A: Link to Existing Employee -->
                <div id="employeeSelectContainer" style="<?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'display: none;' : 'display: block;' ?>">
                    <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                        <label class="modern-form-label" for="employee_id">
                            <?= __('user_form.select_employee', [], 'Select Staff Member') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-tie"></i></span>
                            <select id="employee_id" name="employee_id" class="modern-select-pill" onchange="onEmployeeSelect(this)">
                                <option value=""><?= __('user_form.choose_employee', [], '— Choose verified staff profile —') ?></option>
                                <?php foreach (($employees ?? []) as $emp): ?>
                                    <?php 
                                        $empFullName = trim($emp['first_name'] . ' ' . $emp['last_name']);
                                        $isSelected = (string)($old['employee_id'] ?? '') === (string)$emp['id'];
                                    ?>
                                    <option value="<?= $emp['id'] ?>"
                                            data-name="<?= e($empFullName) ?>"
                                            data-code="<?= e($emp['employee_code']) ?>"
                                            data-email="<?= e($emp['email'] ?? '') ?>"
                                            data-phone="<?= e($emp['phone'] ?? '') ?>"
                                            data-dept="<?= e($emp['department'] ?? '') ?>"
                                            data-desig="<?= e($emp['designation'] ?? '') ?>"
                                            <?= $isSelected ? 'selected' : '' ?>>
                                        <?= e($empFullName) ?> (<?= e($emp['employee_code']) ?> &bull; <?= e($emp['designation']) ?><?= !empty($emp['phone']) ? ' &bull; ' . e($emp['phone']) : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Employee Profile Preview Pill -->
                    <div id="employeePreviewCard" style="display: none; margin-bottom: 1.75rem; padding: 1.15rem 1.5rem; border-radius: 16px; background: #E8F4F0; border: 1.5px solid #B2D8CF;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 42px; height: 42px; border-radius: 50%; background: #176B5B; color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <strong id="previewEmpName" style="color: #0C2B52; font-size: 1rem; display: block;"></strong>
                                    <span style="font-size: 0.82rem; color: #176B5B; font-weight: 600;" id="previewEmpMeta"></span>
                                </div>
                            </div>
                            <span id="previewEmpCode" style="padding: 4px 12px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-family: monospace; font-weight: 700; font-size: 0.85rem; border: 1px solid #B2D8CF;"></span>
                        </div>
                    </div>
                </div>

                <!-- Mode B: Independent Custom Profile Fields -->
                <div id="customUserContainer" style="<?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'display: block;' : 'display: none;' ?>">
                    <div class="modern-form-grid-3">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="name">
                                <?= __('user_form.full_name', [], 'Full Name') ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="name" name="name" class="modern-input-pill" value="<?= e($old['name'] ?? '') ?>" placeholder="e.g. Ramesh Kumar">
                            </div>
                            <?php if (!empty($errors['name'])): ?>
                                <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['name'][0]) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="email">
                                <?= __('user_form.official_email', [], 'Email Address') ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" id="email" name="email" class="modern-input-pill" value="<?= e($old['email'] ?? '') ?>" placeholder="e.g. ramesh@manaswitours.com">
                            </div>
                            <?php if (!empty($errors['email'])): ?>
                                <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="phone">
                                <?= __('user_form.contact_phone', [], 'Contact Phone') ?>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" id="phone" name="phone" class="modern-input-pill" value="<?= e($old['phone'] ?? '') ?>" placeholder="e.g. +91 98765 43210">
                            </div>
                            <?php if (!empty($errors['phone'])): ?>
                                <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/users') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> <?= __('form.cancel', [], 'Cancel') ?>
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: Role & Operational Privileges                                     -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel2" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title"><?= __('user_form.step2_heading', [], 'System Role & Access Privileges') ?></h2>
                        <p class="modern-section-sub"><?= __('user_form.step2_subheading', [], 'Assign access tier, operational boundaries, and system permissions.') ?></p>
                    </div>
                </div>

                <!-- Role Selector -->
                <div class="modern-form-group" style="margin-bottom: 1.75rem;">
                    <label class="modern-form-label" for="role">
                        <?= __('user_form.assigned_role', [], 'Assigned Access Role') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                        <select id="role" name="role" class="modern-select-pill" required onchange="onRoleSelectChange(this.value)">
                            <option value="driver" <?= ($old['role'] ?? 'driver') === 'driver' ? 'selected' : '' ?> data-badge="Chauffeur">🚗 <?= __('auth.chauffeur', [], 'Licensed Chauffeur / Driver') ?></option>
                            <option value="dispatcher" <?= ($old['role'] ?? '') === 'dispatcher' ? 'selected' : '' ?> data-badge="Dispatcher">🎯 <?= __('auth.dispatcher', [], 'Fleet Dispatch Controller') ?></option>
                            <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?> data-badge="Administrator">👑 <?= __('auth.admin_officer', [], 'Super Administrator') ?></option>
                            <option value="customer" <?= ($old['role'] ?? '') === 'customer' ? 'selected' : '' ?> data-badge="Client">👤 <?= __('customers.badge', [], 'Client / Customer Portal') ?></option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $r): ?>
                                    <?php if (in_array(strtolower($r['slug'] ?? ''), ['driver', 'dispatcher', 'admin', 'customer'])) continue; ?>
                                    <option value="<?= e($r['slug']) ?>" <?= ($old['role'] ?? '') === $r['slug'] ? 'selected' : '' ?> data-badge="<?= e($r['name']) ?>">
                                        <?= e($r['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Role Privilege Dossier Card -->
                <div id="roleDossierCard" style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 18px; padding: 1.5rem; margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                        <span style="font-weight: 700; color: #0C2B52; font-size: 0.95rem;" id="roleDossierTitle">
                            <i class="fa-solid fa-shield-cat" style="color: #176B5B;"></i> Role Privilege Overview
                        </span>
                        <span id="roleDossierPill" style="padding: 4px 14px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-weight: 700; font-size: 0.82rem; border: 1px solid #B2D8CF;">Chauffeur</span>
                    </div>
                    <p style="font-size: 0.88rem; color: #64748B; margin: 0 0 1rem 0;" id="roleDossierDesc">
                        Access to driver portal for route navigation, odometer start/stop verification, duty schedules, and passenger attendance.
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; font-size: 0.82rem;" class="modern-form-grid-3">
                        <div style="background: #FFFFFF; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #E2E8F0;">
                            <span style="color: #64748B; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Scope</span>
                            <strong style="color: #0C2B52;" id="roleScopeVal">Driver Portal</strong>
                        </div>
                        <div style="background: #FFFFFF; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #E2E8F0;">
                            <span style="color: #64748B; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Security</span>
                            <strong style="color: #0C2B52;">Bcrypt Cost 12</strong>
                        </div>
                        <div style="background: #FFFFFF; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #E2E8F0;">
                            <span style="color: #64748B; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: block;">Access State</span>
                            <strong style="color: #176B5B;">Instant Provisioning</strong>
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
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 3: Account Security, Username & Credentials                          -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel3" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div class="modern-section-meta" style="flex: 1;">
                        <h2 class="modern-section-title"><?= __('user_form.step3_heading', [], 'Account Security & Credentials') ?></h2>
                        <p class="modern-section-sub"><?= __('user_form.step3_subheading', [], 'Configure unique login username, security password, and preferred system language.') ?></p>
                    </div>

                    <button type="button" class="modern-btn-prev" onclick="generateUserCredentials()" style="height: 40px; padding: 0 16px; font-size: 0.82rem;">
                        <span>⚡ <?= __('user_form.generate_password', [], 'Auto-Generate') ?></span>
                    </button>
                </div>

                <!-- Username & Status & Language -->
                <div class="modern-form-grid-3">
                    <!-- Username -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="username">
                            <?= __('user_form.username', [], 'Username') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-at"></i></span>
                            <input type="text" id="username" name="username" class="modern-input-pill" required value="<?= e($old['username'] ?? '') ?>" placeholder="e.g. ramesh.kumar" style="font-family: monospace;">
                        </div>
                        <?php if (!empty($errors['username'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['username'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Account Status -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="status">
                            <?= __('user_form.account_status', [], 'Account Status') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                            <select id="status" name="status" class="modern-select-pill" required>
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= __('form.active', [], 'Active') ?></option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('common.inactive', [], 'Inactive') ?></option>
                                <option value="suspended" <?= ($old['status'] ?? '') === 'suspended' ? 'selected' : '' ?>><?= __('status.suspended', [], 'Suspended') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Preferred Language -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="language">
                            <?= __('form.preferred_language', [], 'Preferred Language') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-language"></i></span>
                            <?php $activeUserLocale = $old['language'] ?? \App\Core\Language::getLocale(); ?>
                            <select id="language" name="language" class="modern-select-pill">
                                <option value="en" <?= $activeUserLocale === 'en' ? 'selected' : '' ?>><?= __('lang.english', [], 'English') ?></option>
                                <option value="hi" <?= $activeUserLocale === 'hi' ? 'selected' : '' ?>><?= __('lang.hindi', [], 'Hindi') ?></option>
                                <option value="mr" <?= $activeUserLocale === 'mr' ? 'selected' : '' ?>><?= __('lang.marathi', [], 'Marathi') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Password and Confirmation -->
                <div class="modern-form-grid-2">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="password">
                            <?= __('user_form.password', [], 'Password') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="modern-input-pill" required minlength="6" placeholder="Min. 6 characters" autocomplete="new-password">
                        </div>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['password'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="password_confirmation">
                            <?= __('user_form.confirm_password', [], 'Confirm Password') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-shield-check"></i></span>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="modern-input-pill" required minlength="6" placeholder="Re-enter password" autocomplete="new-password">
                        </div>
                        <?php if (!empty($errors['password_confirmation'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['password_confirmation'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Live Summary Confirmation Card -->
                <div style="background: #E8F4F0; border: 1.5px solid #B2D8CF; border-radius: 16px; padding: 1.25rem 1.5rem; margin-top: 1.5rem;">
                    <div style="font-weight: 700; font-size: 0.92rem; color: #176B5B; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-clipboard-check"></i> Pre-flight Account Summary
                    </div>
                    <div class="modern-form-grid-3" style="margin-bottom: 0; gap: 1rem;">
                        <div>
                            <span style="color: #64748B; display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Account Name</span>
                            <strong id="summaryUserName" style="color: #0C2B52; font-size: 0.95rem;">—</strong>
                        </div>
                        <div>
                            <span style="color: #64748B; display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Assigned Role</span>
                            <span id="summaryUserRoleBadge" style="display: inline-block; padding: 2px 10px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-weight: 700; border: 1px solid #B2D8CF;">—</span>
                        </div>
                        <div>
                            <span style="color: #64748B; display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Account Status</span>
                            <span style="display: inline-block; padding: 2px 10px; border-radius: 50px; background: #D1FAE5; color: #065F46; font-weight: 700;">Active</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="modern-btn-submit" id="submitUserBtn">
                        <i class="fa-solid fa-user-check"></i> <?= __('user_form.create_account_btn', [], 'Create User Account') ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Wizard Logic -->
<script>
let currentUsrStep = 1;
const totalUsrSteps = 3;

function wizardGoToStep(step) {
    if (step < 1 || step > totalUsrSteps) return;

    for (let i = 1; i <= totalUsrSteps; i++) {
        const panel = document.getElementById('wizardStepPanel' + i);
        if (panel) {
            panel.style.display = (i === step) ? 'block' : 'none';
        }

        const stepBtn = document.querySelector(`.modern-step-item[data-step-target="${i}"]`);
        if (stepBtn) {
            stepBtn.classList.remove('active', 'completed');
            if (i < step) {
                stepBtn.classList.add('completed');
            } else if (i === step) {
                stepBtn.classList.add('active');
            }
        }

        if (i < totalUsrSteps) {
            const connector = document.getElementById('usrStepConn' + i);
            if (connector) {
                if (i < step) {
                    connector.classList.add('completed');
                } else {
                    connector.classList.remove('completed');
                }
            }
        }
    }

    currentUsrStep = step;
    updateUserSummaryDossier();
    window.scrollTo({ top: 100, behavior: 'smooth' });
}

function wizardNextStep(current) {
    if (!validateUserStep(current)) return;
    wizardGoToStep(current + 1);
}

function validateUserStep(step) {
    if (step === 1) {
        const isCustom = document.getElementById('is_custom_user').checked;
        if (isCustom) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            if (!name || !name.value.trim()) {
                if (typeof window.vgNotify === 'function') window.vgNotify('Full name is required for custom profile', 'error');
                if (name) name.focus();
                return false;
            }
            if (!email || !email.value.trim() || !email.value.includes('@')) {
                if (typeof window.vgNotify === 'function') window.vgNotify('Valid email address is required', 'error');
                if (email) email.focus();
                return false;
            }
        } else {
            const empSelect = document.getElementById('employee_id');
            if (!empSelect || !empSelect.value) {
                if (typeof window.vgNotify === 'function') window.vgNotify('Please select an employee profile or toggle custom profile', 'error');
                if (empSelect) empSelect.focus();
                return false;
            }
        }
    }
    return true;
}

function toggleUserSource(chk) {
    const isCustom = chk.checked;
    const empContainer = document.getElementById('employeeSelectContainer');
    const custContainer = document.getElementById('customUserContainer');

    if (empContainer) empContainer.style.display = isCustom ? 'none' : 'block';
    if (custContainer) custContainer.style.display = isCustom ? 'block' : 'none';

    updateUserSummaryDossier();
}

function onEmployeeSelect(sel) {
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('employeePreviewCard');
    const nameEl = document.getElementById('previewEmpName');
    const metaEl = document.getElementById('previewEmpMeta');
    const codeEl = document.getElementById('previewEmpCode');

    if (!sel.value || !opt) {
        if (preview) preview.style.display = 'none';
        return;
    }

    const name = opt.getAttribute('data-name') || '';
    const code = opt.getAttribute('data-code') || '';
    const desig = opt.getAttribute('data-desig') || '';
    const dept = opt.getAttribute('data-dept') || '';
    const phone = opt.getAttribute('data-phone') || '';

    if (nameEl) nameEl.textContent = name;
    if (metaEl) metaEl.textContent = (desig || dept) + (phone ? ' • ' + phone : '');
    if (codeEl) codeEl.textContent = code;
    if (preview) preview.style.display = 'block';

    // Auto-suggest username if blank
    const usernameInput = document.getElementById('username');
    if (usernameInput && !usernameInput.value) {
        usernameInput.value = name.toLowerCase().replace(/[^a-z0-9]/g, '.').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
    }

    // Auto-select corresponding role if driver
    if (dept.toLowerCase().includes('chauffeur') || dept.toLowerCase().includes('driver') || desig.toLowerCase().includes('driver')) {
        const roleSel = document.getElementById('role');
        if (roleSel) {
            roleSel.value = 'driver';
            onRoleSelectChange('driver');
        }
    }

    updateUserSummaryDossier();
}

function onRoleSelectChange(role) {
    const rolesData = {
        'driver': {
            title: '🚗 Licensed Chauffeur Access',
            pill: 'Chauffeur / Driver',
            desc: 'Access to mobile driver portal for real-time duty start/stop, passenger pickup check-in, and route assignments.',
            scope: 'Driver Portal Only'
        },
        'dispatcher': {
            title: '🎯 Fleet Dispatch Controller',
            pill: 'Dispatcher',
            desc: 'Access to live vehicle fleet radar, trip dispatching, chauffeur duty assignments, and real-time route monitoring.',
            scope: 'Dispatch Operations'
        },
        'admin': {
            title: '👑 Super Administrator Access',
            pill: 'Administrator',
            desc: 'Unrestricted access to system configuration, financial audits, staff rosters, fleet management, and permissions.',
            scope: 'Full Office Management'
        },
        'customer': {
            title: '👤 Customer / Guest Portal',
            pill: 'Client',
            desc: 'Self-service portal to view itinerary reservations, invoice receipts, and vehicle dispatch status.',
            scope: 'Customer Portal Only'
        }
    };

    const data = rolesData[role] || {
        title: 'Operational Access',
        pill: role.toUpperCase(),
        desc: 'Custom operational role with tailored permissions.',
        scope: 'Custom Scope'
    };

    const titleEl = document.getElementById('roleDossierTitle');
    const pillEl = document.getElementById('roleDossierPill');
    const descEl = document.getElementById('roleDossierDesc');
    const scopeEl = document.getElementById('roleScopeVal');

    if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-shield-cat" style="color: #176B5B;"></i> ' + data.title;
    if (pillEl) pillEl.textContent = data.pill;
    if (descEl) descEl.textContent = data.desc;
    if (scopeEl) scopeEl.textContent = data.scope;

    updateUserSummaryDossier();
}

function generateUserCredentials() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    let pwd = '';
    for (let i = 0; i < 10; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const pwdInput = document.getElementById('password');
    const pwdConfirm = document.getElementById('password_confirmation');
    if (pwdInput) pwdInput.value = pwd;
    if (pwdConfirm) pwdConfirm.value = pwd;

    if (typeof window.vgNotify === 'function') {
        window.vgNotify('Generated strong password: ' + pwd, 'info');
    }
}

function updateUserSummaryDossier() {
    const isCustom = document.getElementById('is_custom_user')?.checked;
    let name = '—';

    if (isCustom) {
        name = document.getElementById('name')?.value || 'Custom Profile';
    } else {
        const empSel = document.getElementById('employee_id');
        if (empSel && empSel.selectedIndex > 0) {
            name = empSel.options[empSel.selectedIndex].getAttribute('data-name') || 'Staff';
        }
    }

    const summaryName = document.getElementById('summaryUserName');
    if (summaryName) summaryName.textContent = name;

    const roleSel = document.getElementById('role');
    const summaryRole = document.getElementById('summaryUserRoleBadge');
    if (roleSel && summaryRole) {
        summaryRole.textContent = roleSel.options[roleSel.selectedIndex]?.text || roleSel.value;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const empSel = document.getElementById('employee_id');
    if (empSel && empSel.value) {
        onEmployeeSelect(empSel);
    }
    const roleSel = document.getElementById('role');
    if (roleSel && roleSel.value) {
        onRoleSelectChange(roleSel.value);
    }
    wizardGoToStep(1);
});
</script>
