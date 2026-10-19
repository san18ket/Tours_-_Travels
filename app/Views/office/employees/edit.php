<div style="max-width: 860px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Edit Employee: <?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Modify personal credentials, department allocations, or system user linkage.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="btn btn-outline btn-sm">View Profile</a>
            <a href="<?= url('/office/employees') ?>" class="btn btn-outline btn-sm">All Employees</a>
        </div>
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
            <span class="card-title">Employee Record [<?= e($employee['employee_code']) ?>]</span>
            <?= render_status_badge($employee['status']) ?>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/employees/' . $employee['id']) ?>" method="POST" id="editEmployeeForm">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="required-indicator">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required value="<?= e($employee['first_name']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="required-indicator">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required value="<?= e($employee['last_name']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Primary Contact Phone <span class="required-indicator">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" required value="<?= e($employee['phone']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="alternate_phone">Alternate Phone</label>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="form-control" value="<?= e($employee['alternate_phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Corporate / Personal Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= e($employee['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label" for="address">Residential Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?= e($employee['address'] ?? '') ?>">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="emergency_contact">Emergency Contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" value="<?= e($employee['emergency_contact'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--vg-pearl-200);">
                    <div class="form-group">
                        <label class="form-label" for="department">Department <span class="required-indicator">*</span></label>
                        <select id="department" name="department" class="form-select" required>
                            <option value="fleet_chauffeur" <?= $employee['department'] === 'fleet_chauffeur' ? 'selected' : '' ?>>Fleet Chauffeur (Driver)</option>
                            <option value="dispatch" <?= $employee['department'] === 'dispatch' ? 'selected' : '' ?>>Dispatch &amp; Telemetry</option>
                            <option value="operations" <?= $employee['department'] === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="management" <?= $employee['department'] === 'management' ? 'selected' : '' ?>>Management</option>
                            <option value="administration" <?= $employee['department'] === 'administration' ? 'selected' : '' ?>>Administration</option>
                            <option value="maintenance" <?= $employee['department'] === 'maintenance' ? 'selected' : '' ?>>Fleet Maintenance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="designation">Job Title / Designation <span class="required-indicator">*</span></label>
                        <input type="text" id="designation" name="designation" class="form-control" required value="<?= e($employee['designation']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="employment_type">Contract Type <span class="required-indicator">*</span></label>
                        <select id="employment_type" name="employment_type" class="form-select" required>
                            <option value="full_time" <?= $employee['employment_type'] === 'full_time' ? 'selected' : '' ?>>Full Time Permanent</option>
                            <option value="part_time" <?= $employee['employment_type'] === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= $employee['employment_type'] === 'contract' ? 'selected' : '' ?>>Contractual</option>
                            <option value="probation" <?= $employee['employment_type'] === 'probation' ? 'selected' : '' ?>>Probationary</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="joining_date">Date of Joining <span class="required-indicator">*</span></label>
                        <input type="date" id="joining_date" name="joining_date" class="form-control" required value="<?= e($employee['joining_date']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Status <span class="required-indicator">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="on_leave" <?= $employee['status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="terminated" <?= $employee['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--vg-pearl-200);">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="user_id">Linked System Account</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">No System Account Attached</option>
                            <?php foreach ($unlinkedUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= $employee['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?> (@<?= e($u['username']) ?> · <?= e($u['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="<?= url('/office/employees/' . $employee['id']) ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-navy">Save Employee Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
