<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit Employee Profile
 * Master Modern Form UI Matching Reference Design System
 */
$empFullName = e($employee['first_name'] . ' ' . $employee['last_name']);
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar: Circular Back Button, Title, Subtitle, and Status Pill -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="modern-back-btn" title="Back to Employee Profile" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('emp.edit_title', ['name' => $employee['name']], 'Edit Staff Profile')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('emp.edit_subtitle', [], 'Update staff credentials, departmental role, and compliance status.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.88rem; font-weight: 700; border: 1px solid #B2D8CF;">
                <i class="fa-solid fa-user-gear"></i> <?= ucfirst(e($employee['status'])) ?> Staff
            </span>
            <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="modern-btn-cancel" style="padding: 0 16px; height: 42px; font-size: 0.85rem;">
                <i class="fa-solid fa-eye"></i> View Profile
            </a>
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

    <form action="<?= url('/office/employees/' . $employee['id']) ?>" method="POST" id="editEmployeeForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card">
            <!-- Section 1: Personal & Contact Identity -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Personal &amp; Contact Details</h2>
                    <p class="modern-section-sub">Staff legal full name, communication numbers, and residential address.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- First Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="first_name">
                        <?= __('employee_form.first_name', [], 'First Name') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="first_name" name="first_name" class="modern-input-pill" required value="<?= e($employee['first_name']) ?>">
                    </div>
                    <?php if (!empty($errors['first_name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['first_name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="last_name">
                        <?= __('employee_form.last_name', [], 'Last Name') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-tag"></i></span>
                        <input type="text" id="last_name" name="last_name" class="modern-input-pill" required value="<?= e($employee['last_name']) ?>">
                    </div>
                    <?php if (!empty($errors['last_name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['last_name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Primary Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone">
                        <?= __('employee_form.primary_phone', [], 'Primary Phone') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" id="phone" name="phone" class="modern-input-pill" required value="<?= e($employee['phone']) ?>">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Alternate Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="alternate_phone">
                        <?= __('employee_form.alternate_phone', [], 'Alternate Phone') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" value="<?= e($employee['alternate_phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Corporate Email -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email">
                        <?= __('employee_form.corporate_email', [], 'Email Address') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill" value="<?= e($employee['email'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Emergency Contact -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="emergency_contact">
                        <?= __('employee_form.emergency_contact', [], 'Emergency Contact') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-shield-heart"></i></span>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="modern-input-pill" value="<?= e($employee['emergency_contact'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Residential Address -->
            <div class="modern-form-group" style="margin-bottom: 2rem;">
                <label class="modern-form-label" for="address">
                    <?= __('employee_form.residential_address', [], 'Residential Address') ?>
                </label>
                <div class="modern-input-wrapper">
                    <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <input type="text" id="address" name="address" class="modern-input-pill" value="<?= e($employee['address'] ?? '') ?>" placeholder="Full street address, city, state, postal code">
                </div>
            </div>

            <!-- Section 2: Department & Employment Role -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Department &amp; Employment Details</h2>
                    <p class="modern-section-sub">Operational division, contract type, joining date, and roster status.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Department -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="department">
                        <?= __('employee_form.department', [], 'Department') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-building"></i></span>
                        <select id="department" name="department" class="modern-select-pill" required>
                            <option value="fleet_chauffeur" <?= $employee['department'] === 'fleet_chauffeur' ? 'selected' : '' ?>><?= __('employee_form.dept_fleet_chauffeur', [], 'Fleet Operations & Chauffeurs') ?></option>
                            <option value="dispatch" <?= $employee['department'] === 'dispatch' ? 'selected' : '' ?>><?= __('employee_form.dept_dispatch', [], 'Dispatch Control Center') ?></option>
                            <option value="operations" <?= $employee['department'] === 'operations' ? 'selected' : '' ?>><?= __('employee_form.dept_operations', [], 'Tours & Operations') ?></option>
                            <option value="management" <?= $employee['department'] === 'management' ? 'selected' : '' ?>><?= __('employee_form.dept_management', [], 'Executive Management') ?></option>
                            <option value="administration" <?= $employee['department'] === 'administration' ? 'selected' : '' ?>><?= __('employee_form.dept_administration', [], 'HR & Administration') ?></option>
                            <option value="maintenance" <?= $employee['department'] === 'maintenance' ? 'selected' : '' ?>><?= __('employee_form.dept_maintenance', [], 'Fleet Maintenance') ?></option>
                        </select>
                    </div>
                </div>

                <!-- Designation -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="designation">
                        <?= __('employee_form.designation', [], 'Designation / Title') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-card-clip"></i></span>
                        <input type="text" id="designation" name="designation" class="modern-input-pill" required value="<?= e($employee['designation']) ?>">
                    </div>
                </div>

                <!-- Employment Type -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="employment_type">
                        <?= __('employee_form.employment_type', [], 'Employment Type') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-file-contract"></i></span>
                        <select id="employment_type" name="employment_type" class="modern-select-pill" required>
                            <option value="full_time" <?= $employee['employment_type'] === 'full_time' ? 'selected' : '' ?>><?= __('employee_form.type_full_time', [], 'Full-Time Regular') ?></option>
                            <option value="part_time" <?= $employee['employment_type'] === 'part_time' ? 'selected' : '' ?>><?= __('employee_form.type_part_time', [], 'Part-Time Staff') ?></option>
                            <option value="contract" <?= $employee['employment_type'] === 'contract' ? 'selected' : '' ?>><?= __('employee_form.type_contract', [], 'Commercial Contract') ?></option>
                            <option value="probation" <?= $employee['employment_type'] === 'probation' ? 'selected' : '' ?>><?= __('employee_form.type_probation', [], 'Probationary Period') ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Joining Date -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="joining_date">
                        <?= __('employee_form.joining_date', [], 'Date of Joining') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                        <input type="date" id="joining_date" name="joining_date" class="modern-input-pill" required value="<?= e($employee['joining_date']) ?>">
                    </div>
                </div>

                <!-- Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status">
                        <?= __('common.status', [], 'Status') ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <select id="status" name="status" class="modern-select-pill" required>
                            <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>><?= __('form.active', [], 'Active on Roster') ?></option>
                            <option value="on_leave" <?= $employee['status'] === 'on_leave' ? 'selected' : '' ?>><?= __('status.on_leave', [], 'On Leave') ?></option>
                            <option value="terminated" <?= $employee['status'] === 'terminated' ? 'selected' : '' ?>><?= __('status.terminated', [], 'Terminated') ?></option>
                        </select>
                    </div>
                </div>

                <!-- Linked Account -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="user_id">
                        <?= __('employee_form.linked_account', [], 'Linked System Account') ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-lock"></i></span>
                        <select id="user_id" name="user_id" class="modern-select-pill">
                            <option value=""><?= __('employee_form.no_linked_account', [], 'No Linked Account') ?></option>
                            <?php foreach ($unlinkedUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= $employee['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?> (@<?= e($u['username']) ?> &bull; <?= e($u['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= __('form.cancel', [], 'Cancel') ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> <?= __('employee_form.save_changes', [], 'Save Changes') ?>
                </button>
            </div>
        </div>
    </form>
</div>
