<?php
/**
 * Driver Profile Console View — Dedicated Driver Portal
 */
?>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-950); margin: 0 0 0.25rem; letter-spacing: -0.02em;">
            Chauffeur Profile &amp; Credentials
        </h1>
        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
            Manage your personal contact details, emergency information, and account security.
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Verified Chauffeur
        </span>
    </div>
</div>

<div class="vg-split-2" style="margin-bottom: 1.5rem;">
    <!-- Left Column: Chauffeur Identity & Qualifications -->
    <div>
        <!-- Profile Identity Hero Card -->
        <div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem; overflow: hidden;">
            <div style="background: linear-gradient(135deg, var(--vg-navy-950) 0%, var(--vg-navy-900) 100%); color: #FFF; padding: 1.75rem; border-bottom: 2px solid var(--vg-gold-500);">
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 64px; height: 64px; border-radius: var(--vg-radius-md); background: linear-gradient(135deg, var(--vg-blue-500), var(--vg-navy-800)); border: 2px solid var(--vg-gold-500); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; color: #FFF; box-shadow: var(--vg-shadow-gold);">
                        <?= strtoupper(substr($driver['name'] ?? 'D', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--vg-gold-400); font-weight: 700;">
                            VoyageGo Licensed Chauffeur
                        </div>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: #FFF; margin: 0.2rem 0; letter-spacing: -0.01em;">
                            <?= e($driver['name']) ?>
                        </h2>
                        <div style="font-size: 0.82rem; color: #94A3B8;">
                            Username: <strong style="color: #FFF;"><?= e($driver['username'] ?? '—') ?></strong> • Role: <strong style="color: var(--vg-gold-400);">Driver</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body" style="padding: 1.35rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.85rem; letter-spacing: 0.05em;">
                    Verified Professional Credentials (Read-Only)
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; font-size: 0.85rem;">
                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem;">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">Commercial Driving License</span>
                        <strong style="color: var(--vg-navy-950); font-size: 0.95rem;"><?= e($driver['license_number']) ?></strong>
                    </div>

                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem;">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">License Expiry</span>
                        <strong style="color: var(--vg-navy-950); font-size: 0.95rem;"><?= format_date($driver['license_expiry']) ?></strong>
                    </div>

                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem;">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">Experience Record</span>
                        <strong style="color: var(--vg-navy-950); font-size: 0.95rem;"><?= (int)($driver['experience_years'] ?? 1) ?> Years</strong>
                    </div>

                    <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem;">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">Performance Rating</span>
                        <strong style="color: #F59E0B; font-size: 0.95rem;"><i class="fa-solid fa-star"></i> <?= number_format((float)($driver['rating'] ?? 5.0), 1) ?> / 5.0</strong>
                    </div>
                </div>

                <!-- Employment Record (if linked to employees table) -->
                <?php if (!empty($driver['employee_code'])): ?>
                    <div style="margin-top: 1.25rem; border-top: 1px solid var(--vg-pearl-200); padding-top: 1rem;">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--vg-text-muted); margin-bottom: 0.75rem;">
                            Corporate Employment Record
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.82rem;">
                            <div>
                                <span style="color: var(--vg-text-muted);">Employee Code:</span> <strong><?= e($driver['employee_code']) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--vg-text-muted);">Department:</span> <strong><?= e(ucfirst($driver['department'] ?? 'Operations')) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--vg-text-muted);">Designation:</span> <strong><?= e($driver['designation'] ?? 'Chauffeur') ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--vg-text-muted);">Joining Date:</span> <strong><?= format_date($driver['joining_date']) ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Security & Password Change Card -->
        <div class="card" style="box-shadow: var(--vg-shadow-sm);">
            <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
                    <i class="fa-solid fa-key" style="color: var(--vg-gold-500); margin-right: 6px;"></i> <?= e(__('user_form.reset_password_title')) ?>
                </h3>
            </div>
            <div class="card-body" style="padding: 1.35rem;">
                <form action="<?= url('/driver/profile/password') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="current_password"><?= e(__('form.enter_password_placeholder')) ?> <span class="text-danger">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="<?= e(__('form.enter_password_placeholder')) ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="new_password"><?= e(__('user_form.new_password')) ?> <span class="text-danger">*</span></label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" placeholder="<?= e(__('form.enter_password_placeholder')) ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="confirm_password"><?= e(__('user_form.confirm_new_password')) ?> <span class="text-danger">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" placeholder="<?= e(__('form.confirm_password_placeholder')) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-shield-halved" style="margin-right: 4px;"></i> <?= e(__('user_form.reset_btn')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Editable Contact & Emergency Information -->
    <div>
        <div class="card" style="box-shadow: var(--vg-shadow-sm);">
            <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
                    <i class="fa-solid fa-address-book" style="color: var(--vg-blue-500); margin-right: 6px;"></i> <?= e(__('employee_form.step1_title')) ?>
                </h3>
            </div>
            <div class="card-body" style="padding: 1.35rem;">
                <form action="<?= url('/driver/profile') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Email (Read-Only) -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label"><?= e(__('user_form.official_email')) ?></label>
                        <input type="email" class="form-control" value="<?= e($driver['email']) ?>" readonly style="background: var(--vg-pearl-100); color: var(--vg-text-muted);">
                        <span class="form-hint"><?= e(__('user_form.email_hint')) ?></span>
                    </div>

                    <!-- Primary Phone -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="driver_phone"><?= e(__('employee_form.phone')) ?> <span class="text-danger">*</span></label>
                        <input type="text" id="driver_phone" name="phone" class="form-control" value="<?= e($driver['phone'] ?? '') ?>" required placeholder="<?= e(__('employee_form.phone_placeholder')) ?>">
                    </div>

                    <!-- Alternate Phone -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="driver_alt_phone"><?= e(__('customer_form.alt_phone') ?? 'Alternate Contact Number') ?></label>
                        <input type="text" id="driver_alt_phone" name="alternate_phone" class="form-control" value="<?= e($driver['alternate_phone'] ?? '') ?>" placeholder="<?= e(__('customer_form.alt_phone_placeholder') ?? 'Optional secondary phone') ?>">
                    </div>

                    <!-- Emergency Contact -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="emergency_contact"><?= e(__('employee_form.emergency_contact')) ?></label>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" value="<?= e($driver['emergency_contact'] ?? $driver['employee_emergency_contact'] ?? '') ?>" placeholder="<?= e(__('employee_form.emergency_placeholder')) ?>">
                    </div>

                    <!-- Address -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="driver_address"><?= e(__('employee_form.address')) ?></label>
                        <textarea id="driver_address" name="address" class="form-control" rows="3" placeholder="<?= e(__('employee_form.address_placeholder')) ?>"><?= e($driver['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 4px;"></i> <?= e(__('form.save_changes')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
