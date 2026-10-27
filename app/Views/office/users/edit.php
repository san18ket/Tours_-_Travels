<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit User Account
 * Master Modern Form UI Matching Reference Design System
 */
$title = str_replace(':name', $user['name'], __('user_form.edit_title', [], 'Edit User: :name'));
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar: Circular Back Button, Title, Subtitle, and Role Pill -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/users') ?>" class="modern-back-btn" title="Back to User Management" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('user.edit_title', ['name' => $user['name']], 'Edit User Account')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('user.edit_subtitle', [], 'Update credentials, assigned security role, and account activity status.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <?php if ($isSelf): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.88rem; font-weight: 700; border: 1px solid #B2D8CF;">
                    <i class="fa-solid fa-user-check"></i> Current Account
                </span>
            <?php endif; ?>
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #FFFFFF; color: #176B5B; font-size: 0.88rem; font-weight: 700; border: 1.5px solid #B2D8CF;">
                <i class="fa-solid fa-shield-halved"></i> <?= ucfirst(e($user['role'] ?? 'user')) ?>
            </span>
        </div>
    </div>

    <!-- Core Account Edit Card -->
    <div class="modern-form-card" style="margin-bottom: 2rem;">
        <div class="modern-section-header">
            <div class="modern-section-badge">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div class="modern-section-meta">
                <h2 class="modern-section-title"><?= e(__('form.record_settings', [], 'Account Profile & Permissions')) ?></h2>
                <p class="modern-section-sub">Update profile details, official email, assigned role, and account status.</p>
            </div>
        </div>

        <form action="<?= url('/office/users/' . $user['id']) ?>" method="POST" id="editUserForm" novalidate>
            <?= csrf_field() ?>

            <div class="modern-form-grid-3">
                <!-- Full Name -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="name">
                        <?= e(__('user_form.full_name', [], 'Full Name')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="name" name="name" class="modern-input-pill" required value="<?= e($user['name']) ?>">
                    </div>
                    <?php if (!empty($errors['name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Username -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="username">
                        <?= e(__('user_form.username', [], 'Username')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-at"></i></span>
                        <input type="text" id="username" name="username" class="modern-input-pill" required value="<?= e($user['username']) ?>" style="font-family: monospace;">
                    </div>
                    <?php if (!empty($errors['username'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['username'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Address -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email">
                        <?= e(__('user_form.official_email', [], 'Email Address')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill" required value="<?= e($user['email']) ?>">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone">
                        <?= e(__('user_form.contact_phone', [], 'Phone Number')) ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" id="phone" name="phone" class="modern-input-pill" value="<?= e($user['phone'] ?? '') ?>" placeholder="+91 98111 22334">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Role -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="role">
                        <?= e(__('user_form.assigned_role', [], 'Assigned Role')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user-shield"></i></span>
                        <?php if ($isLastAdmin): ?>
                            <input type="hidden" name="role" value="admin">
                            <input type="text" class="modern-input-pill" value="👑 <?= e(__('user_form.role_admin_opt', [], 'Super Administrator')) ?>" disabled style="background:#F1F5F9; color:#475569;">
                        <?php else: ?>
                            <select id="role" name="role" class="modern-select-pill" required>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>👑 <?= e(__('auth.admin_officer', [], 'Super Administrator')) ?></option>
                                <option value="dispatcher" <?= $user['role'] === 'dispatcher' ? 'selected' : '' ?>>🎯 <?= e(__('auth.dispatcher', [], 'Fleet Dispatch Controller')) ?></option>
                                <option value="driver" <?= $user['role'] === 'driver' ? 'selected' : '' ?>>🚗 <?= e(__('auth.chauffeur', [], 'Licensed Chauffeur / Driver')) ?></option>
                                <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>👤 <?= e(__('customers.badge', [], 'Client / Customer')) ?></option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status">
                        <?= e(__('user_form.account_status', [], 'Status')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <?php if ($isSelf || $isLastAdmin): ?>
                            <input type="hidden" name="status" value="active">
                            <input type="text" class="modern-input-pill" value="<?= e(__('user_form.status_active_opt', [], 'Active')) ?>" disabled style="background:#F1F5F9; color:#475569;">
                        <?php else: ?>
                            <select id="status" name="status" class="modern-select-pill" required>
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>><?= e(__('user_form.status_active_opt', [], 'Active')) ?></option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>><?= e(__('user_form.status_inactive_opt', [], 'Inactive')) ?></option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Language Row -->
            <div class="modern-form-grid-3">
                <div class="modern-form-group">
                    <label class="modern-form-label" for="language">
                        <?= e(__('form.preferred_language', [], 'Preferred Language')) ?>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-language"></i></span>
                        <?php $userLocale = $user['language'] ?? \App\Core\Language::getLocale(); ?>
                        <select id="language" name="language" class="modern-select-pill">
                            <option value="en" <?= $userLocale === 'en' ? 'selected' : '' ?>><?= e(__('lang.english', [], 'English')) ?> (English)</option>
                            <option value="hi" <?= $userLocale === 'hi' ? 'selected' : '' ?>><?= e(__('lang.hindi', [], 'Hindi')) ?> (Hindi)</option>
                            <option value="mr" <?= $userLocale === 'mr' ? 'selected' : '' ?>><?= e(__('lang.marathi', [], 'Marathi')) ?> (Marathi)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <a href="<?= url('/office/users') ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> <?= e(__('form.cancel', [], 'Cancel')) ?>
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> <?= e(__('form.save_changes', [], 'Save Changes')) ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Password Reset Action Card -->
    <div class="modern-form-card">
        <div class="modern-section-header">
            <div class="modern-section-badge" style="background: #FEF3C7; color: #D97706;">
                <i class="fa-solid fa-key"></i>
            </div>
            <div class="modern-section-meta" style="flex: 1;">
                <h2 class="modern-section-title"><?= e(__('user_form.reset_password_title', [], 'Zero Plain-Text Password Reset')) ?></h2>
                <p class="modern-section-sub"><?= e(str_replace(':username', $user['username'], __('user_form.reset_password_desc', [], 'Overwrite password credentials for @:username.'))) ?></p>
            </div>
            <span style="padding: 4px 14px; border-radius: 50px; background: #FEF3C7; color: #92400E; font-size: 0.8rem; font-weight: 700; border: 1px solid #FDE68A;">Bcrypt Cost 12</span>
        </div>

        <form action="<?= url('/office/users/' . $user['id'] . '/reset-password') ?>" method="POST" id="resetPasswordForm" novalidate>
            <?= csrf_field() ?>

            <div class="modern-form-grid-2">
                <div class="modern-form-group">
                    <label class="modern-form-label" for="reset_password">
                        <?= e(__('user_form.new_password', [], 'New Password')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="reset_password" name="password" class="modern-input-pill" required minlength="6" placeholder="Min. 6 characters" autocomplete="new-password">
                        <button type="button" onclick="toggleInput('reset_password')" title="Toggle Password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748B; font-size: 1rem;">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="reset_confirmation">
                        <?= e(__('user_form.confirm_new_password', [], 'Confirm New Password')) ?> <span class="req">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-shield-check"></i></span>
                        <input type="password" id="reset_confirmation" name="password_confirmation" class="modern-input-pill" required minlength="6" placeholder="Re-enter password" autocomplete="new-password">
                        <button type="button" onclick="toggleInput('reset_confirmation')" title="Toggle Password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748B; font-size: 1rem;">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <div></div>
                <button type="submit" class="modern-btn-submit" style="background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);">
                    <i class="fa-solid fa-rotate-right"></i> <?= e(__('user_form.reset_btn', [], 'Update Password')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInput(id) {
    const el = document.getElementById(id);
    if (el) {
        el.type = (el.type === 'password') ? 'text' : 'password';
    }
}
</script>
