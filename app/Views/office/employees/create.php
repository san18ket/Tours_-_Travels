<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Modern Employee Registration Form
 * Pixel-perfect implementation matching master form UI for desktop (lg) and responsive mobile (sm)
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header: Back Button, Title, Subtitle, and 3-Step Stepper -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/employees') ?>" class="modern-back-btn" title="Back to Employee Directory" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('emp_form.register_title', [], 'Register New Employee')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('emp_form.register_subtitle', [], 'Chauffeur, dispatch coordinator, or operations staff profile.')) ?></p>
            </div>
        </div>

        <!-- 3-Step Stepper Track -->
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Personal Identity</span>
                </button>
                <div class="modern-step-connector" id="empStepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Role &amp; Contract</span>
                </button>
                <div class="modern-step-connector" id="empStepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Access &amp; Compliance</span>
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
    <form action="<?= url('/office/employees') ?>" method="POST" id="employeeForm" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <!-- Anti-autofill decoy fields to defeat aggressive browser password pre-filling -->
        <input type="text" name="fake_username_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="off">
        <input type="password" name="fake_password_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="new-password">

        <!-- ========================================================================= -->
        <!-- STEP 1: Personal & Contact Information                                   -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel1">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Personal &amp; Contact Identity</h2>
                        <p class="modern-section-sub">Staff legal full name, primary mobile, alternate phone, and residential address.</p>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="first_name">
                            <?= __('employee_form.first_name', [], 'First Name') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="first_name" name="first_name" class="modern-input-pill" required value="<?= e($old['first_name'] ?? '') ?>" placeholder="e.g. Vikramaditya" oninput="syncUsernameSuggestion()">
                        </div>
                        <?php if (!empty($errors['first_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['first_name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="last_name">
                            <?= __('employee_form.last_name', [], 'Last Name') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-tag"></i></span>
                            <input type="text" id="last_name" name="last_name" class="modern-input-pill" required value="<?= e($old['last_name'] ?? '') ?>" placeholder="e.g. Rathore" oninput="syncUsernameSuggestion()">
                        </div>
                        <?php if (!empty($errors['last_name'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['last_name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="employee_code_display">
                            Staff Roster Code
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-barcode"></i></span>
                            <input type="text" id="employee_code_display" class="modern-input-pill" value="<?= e($employeeCode) ?>" readonly style="background: #F1F5F9; color: #176B5B; font-weight: 700; font-family: monospace;">
                        </div>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="phone">
                            <?= __('employee_form.primary_phone', [], 'Primary Mobile Number') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="phone" name="phone" class="modern-input-pill" required value="<?= e($old['phone'] ?? '') ?>" placeholder="e.g. +91 98765 43210">
                        </div>
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="alternate_phone">
                            <?= __('employee_form.alternate_phone', [], 'Alternate Phone') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                            <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" value="<?= e($old['alternate_phone'] ?? '') ?>" placeholder="e.g. +91 98111 22334">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="email">
                            <?= __('employee_form.corporate_email', [], 'Corporate Email Address') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="modern-input-pill" value="<?= e($old['email'] ?? '') ?>" placeholder="e.g. v.rathore@manaswitours.com">
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <div class="modern-form-group modern-col-span-2">
                        <label class="modern-form-label" for="address">
                            <?= __('employee_form.residential_address', [], 'Residential Address') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" id="address" name="address" class="modern-input-pill" value="<?= e($old['address'] ?? '') ?>" placeholder="House/Flat No, Street, Sector, City, State, PIN">
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="emergency_contact">
                            <?= __('employee_form.emergency_contact', [], 'Emergency Contact & Phone') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-shield-heart"></i></span>
                            <input type="text" id="emergency_contact" name="emergency_contact" class="modern-input-pill" value="<?= e($old['emergency_contact'] ?? '') ?>" placeholder="e.g. Spouse / Parent: +91 99887 76655">
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/employees') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> <?= __('form.cancel', [], 'Cancel') ?>
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: Operational Role & Employment Profile                            -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel2" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Operational Role &amp; Contract</h2>
                        <p class="modern-section-sub">Position classification, employment contract type, joining date, and status.</p>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <!-- Role Dropdown -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="role">
                            <?= __('user_form.role', [], 'Operational Role') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                            <select id="role" name="role" class="modern-select-pill" required onchange="onRoleChanged(this.value)">
                                <option value="driver" <?= ($old['role'] ?? 'driver') === 'driver' ? 'selected' : '' ?> data-name="Chauffeur / Driver">🚗 <?= __('user_form.role_driver_opt', [], 'Licensed Chauffeur / Driver') ?></option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <?php if (in_array(strtolower($r['slug'] ?? ''), ['driver', 'chauffeur'])) continue; ?>
                                        <option value="<?= e($r['slug']) ?>" <?= ($old['role'] ?? '') === $r['slug'] ? 'selected' : '' ?> data-name="<?= e($r['name']) ?>">
                                             <?= e($r['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="manager" <?= ($old['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Operations Manager</option>
                                    <option value="dispatcher" <?= ($old['role'] ?? '') === 'dispatcher' ? 'selected' : '' ?>>Fleet Dispatcher</option>
                                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>System Administrator</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Employment Contract Type -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="employment_type">
                            <?= __('employee_form.employment_type', [], 'Employment Contract Type') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-file-contract"></i></span>
                            <select id="employment_type" name="employment_type" class="modern-select-pill" required>
                                <option value="full_time" <?= ($old['employment_type'] ?? 'full_time') === 'full_time' ? 'selected' : '' ?>><?= __('employee_form.type_full_time', [], 'Full-Time Regular') ?></option>
                                <option value="part_time" <?= ($old['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>><?= __('employee_form.type_part_time', [], 'Part-Time Staff') ?></option>
                                <option value="contract" <?= ($old['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>><?= __('employee_form.type_contract', [], 'Commercial Contract') ?></option>
                                <option value="probation" <?= ($old['employment_type'] ?? '') === 'probation' ? 'selected' : '' ?>><?= __('employee_form.type_probation', [], 'Probationary Period') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Joining Date -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="joining_date">
                            <?= __('employee_form.joining_date', [], 'Date of Joining') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="joining_date" name="joining_date" class="modern-input-pill" required value="<?= e($old['joining_date'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                </div>

                <div class="modern-form-grid-3">
                    <!-- Status -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="status">
                            <?= __('common.status', [], 'Roster Status') ?> <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                            <select id="status" name="status" class="modern-select-pill" required>
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= __('form.active', [], 'Active on Roster') ?></option>
                                <option value="on_leave" <?= ($old['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>><?= __('status.on_leave', [], 'On Leave') ?></option>
                                <option value="terminated" <?= ($old['status'] ?? '') === 'terminated' ? 'selected' : '' ?>><?= __('status.terminated', [], 'Terminated') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="department">
                            <?= __('employee_form.department', [], 'Department') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-building"></i></span>
                            <select id="department" name="department" class="modern-select-pill">
                                <option value="fleet_chauffeur" <?= ($old['department'] ?? 'fleet_chauffeur') === 'fleet_chauffeur' ? 'selected' : '' ?>><?= __('employee_form.dept_fleet_chauffeur', [], 'Fleet Operations & Chauffeurs') ?></option>
                                <option value="dispatch" <?= ($old['department'] ?? '') === 'dispatch' ? 'selected' : '' ?>><?= __('employee_form.dept_dispatch', [], 'Dispatch Control Center') ?></option>
                                <option value="operations" <?= ($old['department'] ?? '') === 'operations' ? 'selected' : '' ?>><?= __('employee_form.dept_operations', [], 'Tours & Operations') ?></option>
                                <option value="management" <?= ($old['department'] ?? '') === 'management' ? 'selected' : '' ?>><?= __('employee_form.dept_management', [], 'Executive Management') ?></option>
                                <option value="administration" <?= ($old['department'] ?? '') === 'administration' ? 'selected' : '' ?>><?= __('employee_form.dept_administration', [], 'HR & Administration') ?></option>
                                <option value="maintenance" <?= ($old['department'] ?? '') === 'maintenance' ? 'selected' : '' ?>><?= __('employee_form.dept_maintenance', [], 'Fleet Maintenance') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Designation -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="designation">
                            <?= __('employee_form.designation', [], 'Designation / Title') ?>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-card-clip"></i></span>
                            <input type="text" id="designation" name="designation" class="modern-input-pill" value="<?= e($old['designation'] ?? 'Chauffeur / Driver') ?>" placeholder="e.g. Senior Chauffeur">
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
        <!-- STEP 3: System Account Credentials & Chauffeur Compliance                -->
        <!-- ========================================================================= -->
        <div class="modern-step-panel" id="wizardStepPanel3" style="display: none;">
            <div class="modern-form-card">
                <div class="modern-section-header">
                    <div class="modern-section-badge">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div class="modern-section-meta">
                        <h2 class="modern-section-title">Access Credentials &amp; Compliance</h2>
                        <p class="modern-section-sub">Optional system login credentials and commercial driver license details.</p>
                    </div>
                </div>

                <!-- Toggle: Create System Account -->
                <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1.75rem;">
                    <label for="create_account" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <div>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; display: block;"><?= __('user_form.create_title', [], 'Create System Login Account') ?></span>
                            <span style="font-size: 0.82rem; color: #64748B; display: block; margin-top: 2px;">Allow this employee to sign in to the portal using username and password</span>
                        </div>
                        <input type="checkbox" id="create_account" name="create_account" value="1" <?= (!isset($old['create_account']) || !empty($old['create_account']) || !empty($old['username'])) ? 'checked' : '' ?> onchange="toggleAccountFields(this.checked)" style="width: 20px; height: 20px; accent-color: #176B5B; cursor: pointer;">
                    </label>
                </div>

                <!-- Account Credentials Container -->
                <div id="accountFieldsContainer" style="<?= (!isset($old['create_account']) || !empty($old['create_account']) || !empty($old['username'])) ? 'display: block;' : 'display: none;' ?>">
                    <div style="background: #FFFFFF; border: 1.5px solid #E6EEF8; border-radius: 18px; padding: 1.5rem; margin-bottom: 1.75rem; box-shadow: 0 4px 12px rgba(12, 43, 82, 0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <span style="font-weight: 700; font-size: 0.92rem; color: #0C2B52;"><i class="fa-solid fa-lock" style="color: #176B5B;"></i> User Authentication Credentials</span>
                            <button type="button" class="modern-btn-prev" onclick="generateCredentials()" style="height: 38px; padding: 0 16px; font-size: 0.82rem;">
                                <span>⚡ <?= __('user_form.generate_password', [], 'Auto-Generate') ?></span>
                            </button>
                        </div>

                        <!-- Username -->
                        <div class="modern-form-group" style="margin-bottom: 1.25rem;">
                            <label class="modern-form-label" for="username">
                                <?= __('user_form.username', [], 'Username') ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-at"></i></span>
                                <input type="text" id="username" name="username" class="modern-input-pill" value="<?= e($old['username'] ?? '') ?>" placeholder="e.g. v.rathore" pattern="^[a-zA-Z0-9_.]+$" style="font-family: monospace;">
                            </div>
                            <small class="modern-field-hint"><?= __('user_form.username_hint', [], 'Unique alphanumeric username for office sign-in.') ?></small>
                        </div>

                        <!-- Password & Confirmation -->
                        <div class="modern-form-grid-2">
                            <div class="modern-form-group">
                                <label class="modern-form-label" for="password">
                                    <?= __('user_form.password', [], 'Password') ?> <span class="req">*</span>
                                </label>
                                <div class="modern-input-wrapper">
                                    <span class="modern-input-icon"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" id="password" name="password" class="modern-input-pill" minlength="6" placeholder="Min. 6 characters" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-form-label" for="password_confirmation">
                                    <?= __('user_form.confirm_password', [], 'Confirm Password') ?> <span class="req">*</span>
                                </label>
                                <div class="modern-input-wrapper">
                                    <span class="modern-input-icon"><i class="fa-solid fa-shield-check"></i></span>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="modern-input-pill" minlength="6" placeholder="Re-enter password" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commercial Chauffeur Compliance Profile (Shown if driver role) -->
                <div id="chauffeurFields" style="background: #E8F4F0; border: 1.5px solid #B2D8CF; padding: 1.5rem; border-radius: 18px; margin-bottom: 1.75rem; display: <?= in_array(strtolower($old['role'] ?? 'driver'), ['driver', 'chauffeur', 'fleet_chauffeur']) ? 'block' : 'none' ?>;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <span style="font-weight: 700; color: #176B5B; font-size: 0.95rem;">
                            <i class="fa-solid fa-id-card"></i> Commercial Chauffeur Verification
                        </span>
                        <span style="padding: 4px 12px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-size: 0.78rem; font-weight: 700; border: 1px solid #B2D8CF;">Verified Fleet Driver</span>
                    </div>

                    <div class="modern-form-grid-3">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="license_number">
                                <?= __('employee_form.license_number', [], 'Commercial License (DL)') ?> <span class="req">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                                <input type="text" id="license_number" name="license_number" class="modern-input-pill" value="<?= e($old['license_number'] ?? '') ?>" placeholder="e.g. DL-0420110098765" style="font-family: monospace;">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="license_expiry">
                                <?= __('employee_form.license_expiry', [], 'License Expiry Date') ?>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <input type="date" id="license_expiry" name="license_expiry" class="modern-input-pill" value="<?= e($old['license_expiry'] ?? date('Y-m-d', strtotime('+3 years'))) ?>">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="experience_years">
                                <?= __('employee_form.experience_years', [], 'Driving Experience (Years)') ?>
                            </label>
                            <div class="modern-input-wrapper">
                                <span class="modern-input-icon"><i class="fa-solid fa-award"></i></span>
                                <input type="number" id="experience_years" name="experience_years" class="modern-input-pill" value="<?= e($old['experience_years'] ?? '5') ?>" min="0" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Pre-Submission Summary Dossier -->
                <div id="employeeSummaryCard" style="background: #F8FAFD; border: 1.5px solid #E2E8F0; border-radius: 18px; padding: 1.5rem; margin-bottom: 2rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #176B5B; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.45rem;">
                        <i class="fa-solid fa-clipboard-check"></i> Employee Onboarding Summary
                    </div>

                    <div class="modern-form-grid-3" style="margin-bottom: 0;">
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Full Name</span>
                            <div id="summaryFullName" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; margin-top: 2px;">—</div>
                        </div>
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Primary Phone</span>
                            <div id="summaryPhone" style="font-size: 0.95rem; font-weight: 700; color: #0C2B52; margin-top: 2px;">—</div>
                        </div>
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Assigned Role</span>
                            <div id="summaryRoleBadge" style="font-size: 0.95rem; font-weight: 700; color: #176B5B; margin-top: 2px;">—</div>
                        </div>
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Contract Type</span>
                            <div id="summaryContract" style="font-size: 0.95rem; font-weight: 600; color: #0C2B52; margin-top: 2px;">—</div>
                        </div>
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Portal Username</span>
                            <div id="summaryUsername" style="font-size: 0.95rem; font-weight: 600; color: #176B5B; font-family: monospace; margin-top: 2px;">—</div>
                        </div>
                        <div>
                            <span style="font-size: 0.74rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Roster Status</span>
                            <div id="summaryStatus" style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-top: 2px;">Active</div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" id="submitBtn" class="modern-btn-submit">
                        <i class="fa-solid fa-check"></i> <?= __('employee_form.save_employee', [], 'Register Employee') ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentEmpStep = 1;
const totalEmpSteps = 3;

function updateEmpStepperUI(activeStep) {
    currentEmpStep = activeStep;

    const stepButtons = document.querySelectorAll('.modern-stepper-track .modern-step-item');
    stepButtons.forEach((btn, index) => {
        const stepNum = index + 1;
        btn.classList.remove('active', 'completed');
        if (stepNum === activeStep) {
            btn.classList.add('active');
        } else if (stepNum < activeStep) {
            btn.classList.add('completed');
        }
    });

    for (let i = 1; i < totalEmpSteps; i++) {
        const conn = document.getElementById('empStepConn' + i);
        if (conn) {
            if (i < activeStep) {
                conn.classList.add('completed');
            } else {
                conn.classList.remove('completed');
            }
        }
    }

    if (activeStep === 3) {
        populateSummaryCard();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalEmpSteps) return;

    for (let i = 1; i <= totalEmpSteps; i++) {
        const panel = document.getElementById('wizardStepPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    const targetPanel = document.getElementById('wizardStepPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateEmpStepperUI(targetStep);

    const wrapper = document.querySelector('.modern-form-wrapper');
    if (wrapper) {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(step) {
    if (step === 1) {
        const fName = document.getElementById('first_name');
        const lName = document.getElementById('last_name');
        const phone = document.getElementById('phone');

        let isValid = true;
        let firstInvalid = null;

        [phone, lName, fName].forEach(field => {
            if (!field || !field.value.trim()) {
                isValid = false;
                firstInvalid = field;
                if (field) {
                    field.style.borderColor = '#EF4444';
                    field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
                }
            } else if (field) {
                field.style.borderColor = '';
                field.style.boxShadow = '';
            }
        });

        if (!isValid) {
            wizardGoToStep(1);
            if (firstInvalid) firstInvalid.focus();
            if (typeof window.vgNotify === 'function') window.vgNotify('Please fill in required fields marked with *', 'error');
            return false;
        }
    }

    if (step === 2) {
        const role = document.getElementById('role');
        const joiningDate = document.getElementById('joining_date');

        let isValid = true;
        let firstInvalid = null;

        if (!joiningDate || !joiningDate.value) {
            isValid = false;
            firstInvalid = joiningDate;
            joiningDate.style.borderColor = '#EF4444';
        } else {
            joiningDate.style.borderColor = '';
        }

        if (!role || !role.value) {
            isValid = false;
            firstInvalid = role;
            role.style.borderColor = '#EF4444';
        } else {
            role.style.borderColor = '';
        }

        if (!isValid) {
            wizardGoToStep(2);
            if (firstInvalid) firstInvalid.focus();
            if (typeof window.vgNotify === 'function') window.vgNotify('Please select role and joining date', 'error');
            return false;
        }
    }

    return true;
}

function wizardNextStep(currentStep) {
    if (!wizardValidateCurrentStep(currentStep)) {
        return;
    }
    wizardGoToStep(currentStep + 1);
}

function toggleAccountFields(isChecked) {
    const container = document.getElementById('accountFieldsContainer');
    if (container) {
        container.style.display = isChecked ? 'block' : 'none';
    }
    const usernameInput = document.getElementById('username');
    const pwdInput = document.getElementById('password');
    const pwdConfirm = document.getElementById('password_confirmation');
    if (!isChecked) {
        if (usernameInput) usernameInput.disabled = true;
        if (pwdInput) pwdInput.disabled = true;
        if (pwdConfirm) pwdConfirm.disabled = true;
    } else {
        if (usernameInput) usernameInput.disabled = false;
        if (pwdInput) {
            pwdInput.disabled = false;
            if (!pwdInput.value) generateCredentials();
        }
        if (pwdConfirm) pwdConfirm.disabled = false;
    }
    populateSummaryCard();
}

function onRoleChanged(roleSlug) {
    const chauffeurBox = document.getElementById('chauffeurFields');
    const slug = (roleSlug || '').toLowerCase();
    const isDriver = slug === 'driver' || slug === 'chauffeur' || slug === 'fleet_chauffeur';
    
    if (chauffeurBox) {
        chauffeurBox.style.display = isDriver ? 'block' : 'none';
    }
    populateSummaryCard();
}

function syncUsernameSuggestion() {
    const usernameInput = document.getElementById('username');
    if (!usernameInput || usernameInput.dataset.manuallyEdited === 'true') return;

    const fName = (document.getElementById('first_name')?.value || '').trim().toLowerCase();
    const lName = (document.getElementById('last_name')?.value || '').trim().toLowerCase();

    if (fName && lName) {
        const cleanF = fName.replace(/[^a-z0-9]/g, '');
        const cleanL = lName.replace(/[^a-z0-9]/g, '');
        if (cleanF && cleanL) {
            usernameInput.value = cleanF.charAt(0) + '.' + cleanL;
        }
    }
}

document.getElementById('username')?.addEventListener('input', function() {
    this.dataset.manuallyEdited = 'true';
});

function generateCredentials() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    let pwd = '';
    for (let i = 0; i < 10; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const pwdInput = document.getElementById('password');
    const pwdConfirm = document.getElementById('password_confirmation');
    if (pwdInput) {
        pwdInput.type = 'text';
        pwdInput.value = pwd;
    }
    if (pwdConfirm) {
        pwdConfirm.type = 'text';
        pwdConfirm.value = pwd;
    }
    populateSummaryCard();
}

function populateSummaryCard() {
    const fName = (document.getElementById('first_name')?.value || '').trim();
    const lName = (document.getElementById('last_name')?.value || '').trim();
    const phone = (document.getElementById('phone')?.value || '').trim();
    const roleSelect = document.getElementById('role');
    const contractSelect = document.getElementById('employment_type');
    const statusSelect = document.getElementById('status');
    const createAccountBox = document.getElementById('create_account');
    const createAccount = createAccountBox ? createAccountBox.checked : false;
    const username = (document.getElementById('username')?.value || '').trim();

    const fullNameEl = document.getElementById('summaryFullName');
    const phoneEl = document.getElementById('summaryPhone');
    const roleEl = document.getElementById('summaryRoleBadge');
    const contractEl = document.getElementById('summaryContract');
    const usernameEl = document.getElementById('summaryUsername');
    const statusEl = document.getElementById('summaryStatus');

    if (fullNameEl) fullNameEl.textContent = (fName || lName) ? (fName + ' ' + lName) : '—';
    if (phoneEl) phoneEl.textContent = phone || '—';
    
    if (roleEl && roleSelect && roleSelect.selectedIndex >= 0) {
        roleEl.textContent = roleSelect.options[roleSelect.selectedIndex].text.split('(')[0].trim();
    } else if (roleEl) {
        roleEl.textContent = 'None Assigned';
    }

    if (contractEl && contractSelect) {
        contractEl.textContent = contractSelect.options[contractSelect.selectedIndex].text;
    }

    if (usernameEl) {
        if (createAccount && username) {
            usernameEl.textContent = '@' + username;
            usernameEl.style.color = '#176B5B';
        } else if (createAccount) {
            usernameEl.textContent = 'Credentials required';
            usernameEl.style.color = '#EF4444';
        } else {
            usernameEl.textContent = 'No Account (HR Record Only)';
            usernameEl.style.color = '#64748B';
        }
    }

    if (statusEl && statusSelect) {
        statusEl.textContent = statusSelect.options[statusSelect.selectedIndex].text;
    }
}

// Final form submit validation
document.getElementById('employeeForm')?.addEventListener('submit', function(e) {
    if (!wizardValidateCurrentStep(1) || !wizardValidateCurrentStep(2)) {
        e.preventDefault();
        return false;
    }

    const createAccountBox = document.getElementById('create_account');
    const isCreateAccount = createAccountBox ? createAccountBox.checked : false;

    if (isCreateAccount) {
        const username = document.getElementById('username');
        const pwd = document.getElementById('password');
        const pwdConfirm = document.getElementById('password_confirmation');

        if (!username || !username.value.trim() || username.value.trim().length < 3) {
            e.preventDefault();
            wizardGoToStep(3);
            if (username) username.focus();
            return false;
        }

        if (!pwd || !pwd.value || pwd.value.length < 6) {
            e.preventDefault();
            wizardGoToStep(3);
            if (pwd) pwd.focus();
            return false;
        }

        if (pwdConfirm && pwdConfirm.value !== pwd.value) {
            e.preventDefault();
            wizardGoToStep(3);
            if (pwdConfirm) pwdConfirm.focus();
            if (typeof window.vgNotify === 'function') window.vgNotify('Passwords do not match', 'error');
            return false;
        }
    }

    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.style.pointerEvents = 'none';
        submitBtn.style.opacity = '0.85';
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registering Employee...';
    }

    return true;
});

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    const createAcc = document.getElementById('create_account');
    if (createAcc) {
        toggleAccountFields(createAcc.checked);
        if (createAcc.checked) {
            const pwdInput = document.getElementById('password');
            if (pwdInput && !pwdInput.value) {
                generateCredentials();
            }
        }
    }

    const roleSelect = document.getElementById('role');
    if (roleSelect && roleSelect.value) {
        onRoleChanged(roleSelect.value);
    }

    <?php if (!empty($errors['username']) || !empty($errors['email']) || !empty($errors['password']) || !empty($errors['password_confirmation']) || !empty($errors['license_number'])): ?>
        wizardGoToStep(3);
    <?php elseif (!empty($errors['role']) || !empty($errors['employment_type']) || !empty($errors['joining_date']) || !empty($errors['status'])): ?>
        wizardGoToStep(2);
    <?php else: ?>
        wizardGoToStep(1);
    <?php endif; ?>
});
</script>
