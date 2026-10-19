<div class="user-create-container" style="max-width: 920px; margin: 0 auto; width: 100%;">
    <!-- Top Action Bar -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
                <span class="badge badge-navy" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">Access Control Ops</span>
                <span class="badge badge-success" style="font-size: 0.72rem;"><span class="pulse-indicator"></span> Bcrypt Cost 12</span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0;">Create User Account</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.92rem; margin: 0.35rem 0 0 0;">Provision authorized office credentials with multi-step validation and role-based permissions.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="<?= url('/office/users') ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Users</span>
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem; border-left: 4px solid var(--vg-crimson-600);">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; margin-bottom: 0.35rem; color: #991B1B;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Please review the input errors below:</span>
            </div>
            <ul style="margin: 0; padding-left: 1.4rem; font-size: 0.88rem; color: #7F1D1D;">
                <?php foreach ($errors as $field => $errList): ?>
                    <?php foreach ((array)$errList as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Multi-Step Wizard Stepper Card (Like Customer Form) -->
    <div class="vg-card wizard-stepper-card" style="box-shadow: 0 4px 16px rgba(7, 15, 30, 0.05); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); margin-bottom: 1.5rem; padding: 1.25rem 1.5rem; background: #FFFFFF;">
        <!-- Progress percentage bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-navy-900);">
                Registration Progress: <span id="wizardStepLabel" style="color: var(--vg-blue-500);">Step 1 of 3</span>
            </span>
            <span id="wizardPercentLabel" style="font-size: 0.82rem; font-weight: 800; color: var(--vg-blue-500);">33% Complete</span>
        </div>
        
        <div style="height: 6px; background: #E2E8F0; border-radius: 999px; overflow: hidden; margin-bottom: 1.25rem;">
            <div id="wizardProgressBar" style="width: 33.33%; height: 100%; background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%); transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 999px;"></div>
        </div>

        <!-- Interactive 3-Step Badges -->
        <div class="stepper-indicators-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; position: relative;">
            <!-- Step 1 Indicator -->
            <button type="button" class="step-indicator-btn active" data-target-step="1" onclick="wizardGoToStep(1)">
                <div class="step-indicator-badge">
                    <span class="step-num">1</span>
                    <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="step-indicator-text">
                    <span class="step-title">Identity Source</span>
                    <span class="step-desc">Staff Association</span>
                </div>
            </button>

            <!-- Step 2 Indicator -->
            <button type="button" class="step-indicator-btn" data-target-step="2" onclick="wizardGoToStep(2)">
                <div class="step-indicator-badge">
                    <span class="step-num">2</span>
                    <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="step-indicator-text">
                    <span class="step-title">Role &amp; Status</span>
                    <span class="step-desc">System Privileges</span>
                </div>
            </button>

            <!-- Step 3 Indicator -->
            <button type="button" class="step-indicator-btn" data-target-step="3" onclick="wizardGoToStep(3)">
                <div class="step-indicator-badge">
                    <span class="step-num">3</span>
                    <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="step-indicator-text">
                    <span class="step-title">Credentials &amp; Save</span>
                    <span class="step-desc">Login Security</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Main Multi-Step Form -->
    <form action="<?= url('/office/users') ?>" method="POST" id="userForm" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <!-- Anti-autofill decoy fields to defeat aggressive browser password pre-filling -->
        <input type="text" name="fake_username_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="off">
        <input type="password" name="fake_password_remembered" tabindex="-1" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" autocomplete="new-password">

        <!-- ========================================================================= -->
        <!-- STEP 1: Identity & Staff Profile Linkage -->
        <!-- ========================================================================= -->
        <div id="wizardStepPanel1" class="wizard-step-panel">
            <div class="vg-card" style="box-shadow: 0 4px 16px rgba(7, 15, 30, 0.05); border: 1px solid var(--vg-pearl-300);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(2, 132, 199, 0.1); color: var(--vg-blue-600); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <div>
                            <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Step 1: Account Identity &amp; Staff Association</span>
                            <div style="font-size: 0.78rem; color: var(--vg-text-muted);">Link this user login to a verified staff employee or register a standalone custom user</div>
                        </div>
                    </div>

                    <!-- Custom User Toggle Checkbox on the right of header -->
                    <label for="is_custom_user" style="display: inline-flex; align-items: center; gap: 0.55rem; cursor: pointer; user-select: none; margin-bottom: 0; padding: 0.4rem 0.85rem; background: var(--vg-pearl-100); border: 1px solid var(--vg-pearl-300); border-radius: 8px; transition: all 0.2s ease;">
                        <input type="checkbox" id="is_custom_user" name="is_custom_user" value="1" <?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'checked' : '' ?> onchange="toggleUserSource(this)" style="width: 17px; height: 17px; accent-color: var(--vg-blue-600); cursor: pointer; border-radius: 4px;">
                        <span style="font-size: 0.84rem; font-weight: 700; color: var(--vg-navy-900);">Create Custom User</span>
                    </label>
                </div>

                <div class="card-body" style="padding: 1.75rem;">

                    <!-- Mode A: Employee Dropdown Selection (Visible by default) -->
                    <div id="employeeSelectContainer" style="<?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'display: none;' : 'display: block;' ?>">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="employee_id" style="font-weight: 700; font-size: 0.88rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span>Select Registered Employee <span class="required-indicator">*</span></span>
                                <span style="font-size: 0.78rem; color: var(--vg-text-muted); font-weight: normal;"><?= count($employees ?? []) ?> verified staff profile<?= count($employees ?? []) === 1 ? '' : 's' ?> available</span>
                            </label>

                            <div style="position: relative;">
                                <select id="employee_id" name="employee_id" class="form-select" onchange="onEmployeeSelect(this)" style="height: 50px; padding-left: 2.75rem; font-size: 0.94rem;">
                                    <option value="">-- Choose Employee to Link Account --</option>
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
                                            <?= e($empFullName) ?> (<?= e($emp['employee_code']) ?> · <?= e($emp['designation']) ?><?= !empty($emp['phone']) ? ' · ' . e($emp['phone']) : '' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </span>
                            </div>

                            <!-- Dynamic Employee Profile Preview Pill -->
                            <div id="employeePreviewCard" style="display: none; margin-top: 1.25rem; padding: 1rem 1.25rem; border-radius: 10px; background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.22); font-size: 0.86rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.6rem;">
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></span>
                                        <strong id="previewEmpName" style="color: var(--vg-navy-900); font-size: 0.95rem;"></strong>
                                        <span id="previewEmpCode" style="color: var(--vg-blue-600); font-family: monospace; font-weight: 700; padding: 0.2rem 0.5rem; background: rgba(2, 132, 199, 0.1); border-radius: 4px;"></span>
                                        <span id="previewEmpDesig" class="badge badge-navy" style="font-size: 0.74rem;"></span>
                                    </div>
                                    <div id="previewEmpContact" style="color: var(--vg-text-secondary); font-size: 0.82rem;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mode B: Custom Full Name Input (Hidden by default, shown when "Create Custom User" is checked) -->
                    <div id="customNameContainer" style="<?= (!empty($old['is_custom_user']) || (!empty($old['name']) && empty($old['employee_id']))) ? 'display: block;' : 'display: none;' ?>">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="name" style="font-weight: 700; font-size: 0.88rem;">
                                Full Name <span class="required-indicator">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="text" id="name" name="name" class="form-control" value="<?= e($old['name'] ?? '') ?>" placeholder="e.g. Vikramaditya Rathore" autocomplete="off" style="height: 50px; padding-left: 2.75rem; font-size: 0.94rem;">
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                            </div>
                            <small style="color: var(--vg-text-muted); font-size: 0.78rem; margin-top: 0.35rem; display: block;">Individual person name or external operator designation.</small>
                        </div>
                    </div>

                </div>

                <!-- Step 1 Footer Buttons -->
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.75rem; background: var(--vg-pearl-100); border-top: 1px solid var(--vg-pearl-200);">
                    <a href="<?= url('/office/users') ?>" class="btn btn-outline">
                        Cancel
                    </a>
                    <button type="button" class="btn btn-primary" onclick="wizardNextStep(1)" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700; padding: 0.65rem 1.5rem;">
                        <span>Next: Role &amp; Status</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: Operational Role & System Privileges -->
        <!-- ========================================================================= -->
        <div id="wizardStepPanel2" class="wizard-step-panel" style="display: none;">
            <div class="vg-card" style="box-shadow: 0 4px 16px rgba(7, 15, 30, 0.05); border: 1px solid var(--vg-pearl-300);">
                <div class="card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(217, 119, 6, 0.1); color: var(--vg-gold-600); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <div>
                            <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Step 2: Operational Role &amp; Access Status</span>
                            <div style="font-size: 0.78rem; color: var(--vg-text-muted);">Assign system permission clearance and activation state</div>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 1.75rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="role-status-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <!-- Assigned Authorization Role with "+ Add Role" button directly on its side -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <label class="form-label" for="role" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0;">
                                    Assigned Authorization Role <span class="required-indicator">*</span>
                                </label>
                                <a href="<?= url('/office/roles') ?>" target="_blank" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; font-size: 0.74rem; border-radius: 6px; font-weight: 600;" title="Open Role List &amp; Management in new tab">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Role</span>
                                </a>
                            </div>

                            <select id="role" name="role" class="form-select" required style="height: 50px; font-size: 0.94rem;">
                                <option value="">Select Operational Role...</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= e($r['slug']) ?>" <?= ($old['role'] ?? '') === $r['slug'] ? 'selected' : '' ?>>
                                            <?= e($r['name']) ?> (<?= !empty($r['description']) ? e($r['description']) : e($r['slug']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator (Full Authority &amp; System Settings)</option>
                                    <option value="dispatcher" <?= ($old['role'] ?? '') === 'dispatcher' ? 'selected' : '' ?>>Dispatcher (Trip Ops &amp; Fleet Radar)</option>
                                    <option value="driver" <?= ($old['role'] ?? '') === 'driver' ? 'selected' : '' ?>>Chauffeur / Driver (Mobile Duty Portal)</option>
                                    <option value="customer" <?= ($old['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer (Client Portal)</option>
                                <?php endif; ?>
                            </select>
                            <small style="color: var(--vg-text-muted); font-size: 0.78rem; margin-top: 0.4rem; display: block;">Determines which functional modules and operations this user can access.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="status" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.5rem;">
                                Account Status <span class="required-indicator">*</span>
                            </label>
                            <select id="status" name="status" class="form-select" required style="height: 50px; font-size: 0.94rem;">
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Immediate Sign-in Enabled)</option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Locked Account)</option>
                            </select>
                            <small style="color: var(--vg-text-muted); font-size: 0.78rem; margin-top: 0.4rem; display: block;">Inactive accounts are immediately rejected by authentication firewall.</small>
                        </div>
                    </div>
                </div>

                <!-- Step 2 Footer Buttons -->
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.75rem; background: var(--vg-pearl-100); border-top: 1px solid var(--vg-pearl-200);">
                    <button type="button" class="btn btn-outline" onclick="wizardGoToStep(1)" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Previous: Identity</span>
                    </button>
                    <button type="button" class="btn btn-primary" onclick="wizardNextStep(2)" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700; padding: 0.65rem 1.5rem;">
                        <span>Next: Credentials &amp; Security</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 3: Authentication Credentials, Password & Confirmation -->
        <!-- ========================================================================= -->
        <div id="wizardStepPanel3" class="wizard-step-panel" style="display: none;">
            <div class="vg-card" style="box-shadow: 0 4px 16px rgba(7, 15, 30, 0.05); border: 1px solid var(--vg-pearl-300);">
                <div class="card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </span>
                        <div>
                            <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Step 3: Authentication &amp; Security Credentials</span>
                            <div style="font-size: 0.78rem; color: var(--vg-text-muted);">Set unique sign-in identifier and cryptographic login password</div>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 1.75rem;">
                    <!-- Username & Official Email Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="credentials-top-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <!-- System Username replaced with Username only -->
                            <label class="form-label" for="username" style="font-weight: 700; font-size: 0.88rem;">
                                Username <span class="required-indicator">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="text" id="username" name="username" class="form-control" required value="<?= e($old['username'] ?? '') ?>" placeholder="e.g. v.rathore" pattern="^[a-zA-Z0-9_.]+$" title="Letters, numbers, underscores, and periods only" autocomplete="off" style="height: 50px; padding-left: 2.75rem; font-size: 0.94rem; font-family: monospace;">
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                            </div>
                            <small style="color: var(--vg-text-muted); font-size: 0.78rem; margin-top: 0.35rem; display: block;">Letters, numbers, underscores, and periods only.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="email" style="font-weight: 700; font-size: 0.88rem;">
                                Official Email <span class="required-indicator">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="email" id="email" name="email" class="form-control" required value="<?= e($old['email'] ?? '') ?>" placeholder="e.g. officer@voyagego.com" autocomplete="off" style="height: 50px; padding-left: 2.75rem; font-size: 0.94rem;">
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                            </div>
                            <small style="color: var(--vg-text-muted); font-size: 0.78rem; margin-top: 0.35rem; display: block;">Notification manifests and security alerts.</small>
                        </div>
                    </div>

                    <!-- Contact Phone Field -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" for="phone" style="font-weight: 700; font-size: 0.88rem;">
                            Contact Mobile Phone
                        </label>
                        <div style="position: relative;">
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($old['phone'] ?? '') ?>" placeholder="e.g. +91 98111 22334" autocomplete="off" style="height: 50px; padding-left: 2.75rem; font-size: 0.94rem;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Password Box with no autofill and professional toggle -->
                    <div style="background: var(--vg-pearl-100); padding: 1.35rem; border-radius: var(--vg-radius-md); border: 1px solid var(--vg-pearl-200); margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;" class="password-fields-grid">
                            <div class="form-group" style="margin-bottom: 0;">
                                <!-- Initial Password replaced with Password only -->
                                <label class="form-label" for="password" style="font-weight: 700; font-size: 0.88rem;">
                                    Password <span class="required-indicator">*</span>
                                </label>
                                <div style="position: relative;">
                                    <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="Min. 6 characters" autocomplete="new-password" style="height: 50px; padding-left: 2.75rem; padding-right: 2.75rem; font-size: 0.94rem;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </span>
                                    <button type="button" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--vg-text-muted); cursor: pointer; padding: 0.25rem;" title="Toggle Password Visibility">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="password_confirmation" style="font-weight: 700; font-size: 0.88rem;">
                                    Confirm Password <span class="required-indicator">*</span>
                                </label>
                                <div style="position: relative;">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="6" placeholder="Re-type password" autocomplete="new-password" style="height: 50px; padding-left: 2.75rem; padding-right: 2.75rem; font-size: 0.94rem;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--vg-text-muted); cursor: pointer; padding: 0.25rem;" title="Toggle Password Visibility">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Summary Confirmation Card -->
                    <div style="background: rgba(2, 132, 199, 0.04); border: 1px solid rgba(2, 132, 199, 0.2); border-radius: 10px; padding: 1.15rem 1.35rem;">
                        <div style="font-weight: 700; font-size: 0.86rem; color: var(--vg-navy-900); margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.45rem;">
                            <svg width="16" height="16" fill="none" stroke="var(--vg-blue-600)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pre-Flight Registration Summary</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.85rem; font-size: 0.82rem;" class="summary-columns-grid">
                            <div>
                                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">Full Name</span>
                                <strong id="summaryName" style="color: var(--vg-navy-900); font-size: 0.88rem;">—</strong>
                            </div>
                            <div>
                                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">Assigned Role</span>
                                <span id="summaryRoleBadge" class="badge badge-navy" style="font-size: 0.74rem;">—</span>
                            </div>
                            <div>
                                <span style="color: var(--vg-text-muted); display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">Account Status</span>
                                <span id="summaryStatusBadge" class="badge badge-success" style="font-size: 0.74rem;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 Footer Buttons -->
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.75rem; background: var(--vg-pearl-100); border-top: 1px solid var(--vg-pearl-200);">
                    <button type="button" class="btn btn-outline" onclick="wizardGoToStep(2)" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Previous: Role &amp; Status</span>
                    </button>
                    <button type="submit" class="btn btn-primary" style="height: 48px; padding: 0 1.85rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Create User Account</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- Stepper CSS (Matched with customer form styling) -->
<style>
.step-indicator-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    width: 100%;
}
.step-indicator-btn:hover {
    background: #F1F5F9;
    border-color: #CBD5E1;
}
.step-indicator-badge {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 50%;
    background: #CBD5E1;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}
.step-check {
    display: none;
    width: 16px;
    height: 16px;
}
.step-indicator-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.step-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--vg-navy-900);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.step-desc {
    font-size: 0.7rem;
    color: var(--vg-text-muted);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

/* Active Step State */
.step-indicator-btn.active {
    background: #EFF6FF;
    border-color: #2563EB;
    box-shadow: 0 0 0 1px #2563EB;
}
.step-indicator-btn.active .step-indicator-badge {
    background: #2563EB;
    color: #FFFFFF;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
}
.step-indicator-btn.active .step-title {
    color: #1D4ED8;
}

/* Completed Step State */
.step-indicator-btn.completed {
    background: #F0FDF4;
    border-color: #86EFAC;
}
.step-indicator-btn.completed .step-indicator-badge {
    background: #10B981;
    color: #FFFFFF;
}
.step-indicator-btn.completed .step-num {
    display: none;
}
.step-indicator-btn.completed .step-check {
    display: block;
}

/* Animations */
.wizard-step-panel {
    animation: fadeInStep 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeInStep {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .stepper-indicators-container {
        grid-template-columns: 1fr !important;
    }
    .role-status-grid,
    .credentials-top-grid,
    .password-fields-grid,
    .summary-columns-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
const totalSteps = 3;

function updateWizardProgress(step) {
    const percent = Math.round((step / totalSteps) * 100);
    const pBar = document.getElementById('wizardProgressBar');
    const pLabel = document.getElementById('wizardPercentLabel');
    const sLabel = document.getElementById('wizardStepLabel');

    if (pBar) pBar.style.width = percent + '%';
    if (pLabel) pLabel.textContent = percent + '% Complete';
    if (sLabel) sLabel.textContent = 'Step ' + step + ' of ' + totalSteps;

    // Update Indicators
    const buttons = document.querySelectorAll('.step-indicator-btn');
    buttons.forEach(btn => {
        const btnStep = parseInt(btn.getAttribute('data-target-step'), 10);
        btn.classList.remove('active');
        
        if (btnStep === step) {
            btn.classList.add('active');
            btn.classList.remove('completed');
        } else if (btnStep < step) {
            btn.classList.add('completed');
        } else {
            btn.classList.remove('completed');
        }
    });

    // Populate live summary when arriving at Step 3
    if (step === 3) {
        populateSummaryCard();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardStepPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target panel
    const targetPanel = document.getElementById('wizardStepPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateWizardProgress(targetStep);

    // Scroll smoothly to top of stepper
    const stepper = document.querySelector('.wizard-stepper-card');
    if (stepper) {
        stepper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(step) {
    const panel = document.getElementById('wizardStepPanel' + step);
    if (!panel) return true;

    if (step === 1) {
        const customToggle = document.getElementById('is_custom_user');
        const isCustom = customToggle ? customToggle.checked : false;

        if (isCustom) {
            const nameInput = document.getElementById('name');
            if (!nameInput || !nameInput.value.trim()) {
                if (nameInput) {
                    nameInput.style.borderColor = 'var(--vg-danger, #EF4444)';
                    nameInput.focus();
                    nameInput.reportValidity();
                }
                return false;
            }
            if (nameInput) nameInput.style.borderColor = '';
        } else {
            const empSelect = document.getElementById('employee_id');
            if (!empSelect || !empSelect.value) {
                if (empSelect) {
                    empSelect.style.borderColor = 'var(--vg-danger, #EF4444)';
                    empSelect.focus();
                    empSelect.reportValidity();
                }
                return false;
            }
            if (empSelect) empSelect.style.borderColor = '';
        }
    } else if (step === 2) {
        const roleSelect = document.getElementById('role');
        const statusSelect = document.getElementById('status');

        if (!roleSelect || !roleSelect.value) {
            if (roleSelect) {
                roleSelect.style.borderColor = 'var(--vg-danger, #EF4444)';
                roleSelect.focus();
                roleSelect.reportValidity();
            }
            return false;
        }
        if (roleSelect) roleSelect.style.borderColor = '';

        if (!statusSelect || !statusSelect.value) {
            if (statusSelect) {
                statusSelect.style.borderColor = 'var(--vg-danger, #EF4444)';
                statusSelect.focus();
                statusSelect.reportValidity();
            }
            return false;
        }
        if (statusSelect) statusSelect.style.borderColor = '';
    }

    return true;
}

function wizardNextStep(currentStepNum) {
    if (!wizardValidateCurrentStep(currentStepNum)) {
        return;
    }
    wizardGoToStep(currentStepNum + 1);
}

function toggleUserSource(checkbox) {
    const isCustom = checkbox.checked;
    const employeeContainer = document.getElementById('employeeSelectContainer');
    const customNameContainer = document.getElementById('customNameContainer');
    const employeeSelect = document.getElementById('employee_id');
    const nameInput = document.getElementById('name');
    const previewCard = document.getElementById('employeePreviewCard');

    if (isCustom) {
        // Mode: Custom standalone user
        if (employeeContainer) employeeContainer.style.display = 'none';
        if (customNameContainer) customNameContainer.style.display = 'block';
        if (employeeSelect) {
            employeeSelect.value = '';
            employeeSelect.required = false;
            employeeSelect.style.borderColor = '';
        }
        if (previewCard) previewCard.style.display = 'none';
        if (nameInput) {
            nameInput.required = true;
            nameInput.focus();
        }
    } else {
        // Mode: Select Employee (Default)
        if (employeeContainer) employeeContainer.style.display = 'block';
        if (customNameContainer) customNameContainer.style.display = 'none';
        if (employeeSelect) {
            employeeSelect.required = true;
        }
        if (nameInput) {
            nameInput.required = false;
            nameInput.style.borderColor = '';
        }
        if (employeeSelect && employeeSelect.value) {
            onEmployeeSelect(employeeSelect);
        }
    }
}

function onEmployeeSelect(select) {
    const opt = select.options[select.selectedIndex];
    const previewCard = document.getElementById('employeePreviewCard');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const roleSelect = document.getElementById('role');

    if (!opt || !opt.value) {
        if (previewCard) previewCard.style.display = 'none';
        return;
    }

    select.style.borderColor = '';
    const name = opt.getAttribute('data-name') || '';
    const code = opt.getAttribute('data-code') || '';
    const email = opt.getAttribute('data-email') || '';
    const phone = opt.getAttribute('data-phone') || '';
    const dept = opt.getAttribute('data-dept') || '';
    const desig = opt.getAttribute('data-desig') || '';

    // Update hidden or active name field
    if (nameInput) nameInput.value = name;

    // Auto-fill email & phone if fields are currently empty
    if (emailInput && (!emailInput.value || emailInput.getAttribute('data-autofilled') === '1')) {
        if (email) {
            emailInput.value = email;
            emailInput.setAttribute('data-autofilled', '1');
        }
    }
    if (phoneInput && (!phoneInput.value || phoneInput.getAttribute('data-autofilled') === '1')) {
        if (phone) {
            phoneInput.value = phone;
            phoneInput.setAttribute('data-autofilled', '1');
        }
    }

    // Suggest role based on employee department if not already selected
    if (roleSelect && !roleSelect.value) {
        if (dept === 'fleet_chauffeur') {
            roleSelect.value = 'driver';
        } else if (dept === 'dispatch') {
            roleSelect.value = 'dispatcher';
        } else if (dept === 'management' || dept === 'administration') {
            roleSelect.value = 'admin';
        }
    }

    // Populate preview card
    const nameEl = document.getElementById('previewEmpName');
    const codeEl = document.getElementById('previewEmpCode');
    const desigEl = document.getElementById('previewEmpDesig');
    const contactEl = document.getElementById('previewEmpContact');

    if (nameEl) nameEl.textContent = name;
    if (codeEl) codeEl.textContent = code;
    if (desigEl) desigEl.textContent = desig || dept;
    if (contactEl) contactEl.textContent = (phone ? phone : '') + (phone && email ? ' · ' : '') + (email ? email : '');

    if (previewCard) previewCard.style.display = 'block';
}

function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>`;
    } else {
        field.type = 'password';
        btn.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
    }
}

function populateSummaryCard() {
    const customToggle = document.getElementById('is_custom_user');
    const isCustom = customToggle ? customToggle.checked : false;
    let displayName = '—';

    if (isCustom) {
        displayName = document.getElementById('name')?.value || 'Custom User';
    } else {
        const empSelect = document.getElementById('employee_id');
        if (empSelect && empSelect.selectedIndex > 0) {
            displayName = empSelect.options[empSelect.selectedIndex].getAttribute('data-name') || 'Selected Employee';
        }
    }

    const roleSelect = document.getElementById('role');
    let roleText = 'Not Assigned';
    if (roleSelect && roleSelect.selectedIndex > 0) {
        roleText = roleSelect.options[roleSelect.selectedIndex].text.split('(')[0].trim();
    }

    const statusSelect = document.getElementById('status');
    const statusVal = statusSelect ? statusSelect.value : 'active';

    const sName = document.getElementById('summaryName');
    const sRole = document.getElementById('summaryRoleBadge');
    const sStatus = document.getElementById('summaryStatusBadge');

    if (sName) sName.textContent = displayName;
    if (sRole) sRole.textContent = roleText;
    if (sStatus) {
        sStatus.textContent = statusVal === 'active' ? 'Active' : 'Inactive';
        sStatus.className = statusVal === 'active' ? 'badge badge-success' : 'badge badge-danger';
    }
}

// Ensure form submission validates all required inputs across all steps
document.getElementById('userForm')?.addEventListener('submit', function(e) {
    if (!wizardValidateCurrentStep(1)) {
        e.preventDefault();
        wizardGoToStep(1);
        return false;
    }
    if (!wizardValidateCurrentStep(2)) {
        e.preventDefault();
        wizardGoToStep(2);
        return false;
    }
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    const passConfInput = document.getElementById('password_confirmation');

    if (!usernameInput || !usernameInput.value.trim() || !usernameInput.checkValidity()) {
        e.preventDefault();
        wizardGoToStep(3);
        usernameInput?.focus();
        usernameInput?.reportValidity();
        return false;
    }

    if (!emailInput || !emailInput.value.trim() || !emailInput.checkValidity()) {
        e.preventDefault();
        wizardGoToStep(3);
        emailInput?.focus();
        emailInput?.reportValidity();
        return false;
    }

    if (!passInput || !passInput.value || passInput.value.length < 6) {
        e.preventDefault();
        wizardGoToStep(3);
        passInput?.focus();
        passInput?.reportValidity();
        return false;
    }

    if (passConfInput && passConfInput.value !== passInput.value) {
        e.preventDefault();
        wizardGoToStep(3);
        alert('Password confirmation does not match the entered password.');
        passConfInput.focus();
        return false;
    }

    return true;
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const customToggle = document.getElementById('is_custom_user');
    if (customToggle) {
        toggleUserSource(customToggle);
    }
    const empSelect = document.getElementById('employee_id');
    if (empSelect && empSelect.value && (!customToggle || !customToggle.checked)) {
        onEmployeeSelect(empSelect);
    }
    <?php if (!empty($errors['username']) || !empty($errors['email']) || !empty($errors['password'])): ?>
        wizardGoToStep(3);
    <?php elseif (!empty($errors['role']) || !empty($errors['status'])): ?>
        wizardGoToStep(2);
    <?php else: ?>
        wizardGoToStep(1);
    <?php endif; ?>
});
</script>
