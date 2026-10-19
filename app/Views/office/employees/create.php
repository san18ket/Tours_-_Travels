<div style="max-width: 860px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Register Employee Profile</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Maintain staff records, assign operational departments, and configure optional chauffeur duty links.</p>
        </div>
        <a href="<?= url('/office/employees') ?>" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Directory</span>
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
            <div style="font-weight: 700; margin-bottom: 0.25rem;">Please review the input errors below:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem;">
                <?php foreach ($errors as $field => $errList): ?>
                    <?php foreach ((array)$errList as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="vg-card">
        <div class="card-header">
            <span class="card-title">Employee Onboarding Manifest</span>
            <span class="badge badge-navy" style="font-family: monospace;"><?= e($employeeCode) ?></span>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/employees') ?>" method="POST" id="employeeForm">
                <?= csrf_field() ?>

                <!-- Section: Personal Information -->
                <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-blue-500); margin-bottom: 1rem;">
                    1. Identity &amp; Contact Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="required-indicator">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required value="<?= e($old['first_name'] ?? '') ?>" placeholder="e.g. Ramesh">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="required-indicator">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required value="<?= e($old['last_name'] ?? '') ?>" placeholder="e.g. Sharma">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Primary Mobile Phone <span class="required-indicator">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" required value="<?= e($old['phone'] ?? '') ?>" placeholder="e.g. +91 98765 43210">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="alternate_phone">Alternate Contact Phone</label>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="form-control" value="<?= e($old['alternate_phone'] ?? '') ?>" placeholder="e.g. +91 98111 22334">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Corporate / Personal Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" placeholder="e.g. r.sharma@voyagego.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label" for="address">Residential Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?= e($old['address'] ?? '') ?>" placeholder="Street, Sector, City">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="emergency_contact">Emergency Contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" value="<?= e($old['emergency_contact'] ?? '') ?>" placeholder="Name &amp; Phone">
                    </div>
                </div>

                <!-- Section: Employment & Department -->
                <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-blue-500); margin: 1.5rem 0 1rem 0; padding-top: 1rem; border-top: 1px solid var(--vg-pearl-200);">
                    2. Department &amp; Position
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="department">Department <span class="required-indicator">*</span></label>
                        <select id="department" name="department" class="form-select" required onchange="toggleChauffeurFields(this.value)">
                            <option value="">Select Operational Department</option>
                            <option value="fleet_chauffeur" <?= ($old['department'] ?? '') === 'fleet_chauffeur' ? 'selected' : '' ?>>Fleet Chauffeur (Driver)</option>
                            <option value="dispatch" <?= ($old['department'] ?? '') === 'dispatch' ? 'selected' : '' ?>>Dispatch &amp; Telemetry</option>
                            <option value="operations" <?= ($old['department'] ?? '') === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="management" <?= ($old['department'] ?? '') === 'management' ? 'selected' : '' ?>>Management</option>
                            <option value="administration" <?= ($old['department'] ?? '') === 'administration' ? 'selected' : '' ?>>Administration</option>
                            <option value="maintenance" <?= ($old['department'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Fleet Maintenance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="designation">Job Title / Designation <span class="required-indicator">*</span></label>
                        <input type="text" id="designation" name="designation" class="form-control" required value="<?= e($old['designation'] ?? '') ?>" placeholder="e.g. Senior Executive Chauffeur">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="employment_type">Employment Contract <span class="required-indicator">*</span></label>
                        <select id="employment_type" name="employment_type" class="form-select" required>
                            <option value="full_time" <?= ($old['employment_type'] ?? 'full_time') === 'full_time' ? 'selected' : '' ?>>Full Time Permanent</option>
                            <option value="part_time" <?= ($old['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= ($old['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contractual</option>
                            <option value="probation" <?= ($old['employment_type'] ?? '') === 'probation' ? 'selected' : '' ?>>Probationary</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="joining_date">Date of Joining <span class="required-indicator">*</span></label>
                        <input type="date" id="joining_date" name="joining_date" class="form-control" required value="<?= e($old['joining_date'] ?? date('Y-m-d')) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Employment Status <span class="required-indicator">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="on_leave" <?= ($old['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="terminated" <?= ($old['status'] ?? '') === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                </div>

                <!-- Section: System Account Link -->
                <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-blue-500); margin: 1.5rem 0 1rem 0; padding-top: 1rem; border-top: 1px solid var(--vg-pearl-200);">
                    3. System Account &amp; Chauffeur Credentials
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="user_id">Link Existing System Login Account (Optional)</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">No System Account (Pure HR Record)</option>
                            <?php foreach ($unlinkedUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($old['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?> (@<?= e($u['username']) ?> · <?= e($u['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--vg-text-muted); font-size: 0.78rem;">Connects office employee to their dispatcher or driver portal login.</small>
                    </div>
                </div>

                <!-- Chauffeur Specific Block (Shown if Department == fleet_chauffeur) -->
                <div id="chauffeurFields" style="background: var(--vg-pearl-100); border: 1px solid var(--vg-pearl-200); padding: 1.25rem; border-radius: var(--vg-radius-md); margin-bottom: 1.5rem; display: <?= ($old['department'] ?? '') === 'fleet_chauffeur' ? 'block' : 'none' ?>;">
                    <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.9rem; margin-bottom: 0.75rem;">
                        Commercial Chauffeur Compliance Profile
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="license_number">Driving License Number</label>
                            <input type="text" id="license_number" name="license_number" class="form-control" value="<?= e($old['license_number'] ?? '') ?>" placeholder="e.g. DL-0420110098765">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="license_expiry">License Expiry Date</label>
                            <input type="date" id="license_expiry" name="license_expiry" class="form-control" value="<?= e($old['license_expiry'] ?? date('Y-m-d', strtotime('+3 years'))) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="experience_years">Experience (Years)</label>
                            <input type="number" id="experience_years" name="experience_years" class="form-control" value="<?= e($old['experience_years'] ?? '5') ?>" min="0" max="50">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/employees') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-navy">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Register Employee</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleChauffeurFields(dept) {
    const el = document.getElementById('chauffeurFields');
    if (!el) return;
    if (dept === 'fleet_chauffeur') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}
</script>
