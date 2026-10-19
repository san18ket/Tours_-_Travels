<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--navy-900); letter-spacing: -0.02em;">
            Edit User Profile: <?= e($user['name']) ?>
        </h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Account ID: <code>#<?= (int)$user['id'] ?></code> • Username: <code>@<?= e($user['username']) ?></code>
        </p>
    </div>
    <div>
        <a href="<?= url('/office/users') ?>" class="btn btn-outline">
            &larr; Back to Users List
        </a>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- Core Details Edit Card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">User Account Details</span>
            <div>
                <?php if ($isSelf): ?>
                    <span class="badge badge-navy">Your Current Account</span>
                <?php endif; ?>
                <?php if ($isLastAdmin): ?>
                    <span class="badge badge-warning">Protected Core Administrator</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= url('/office/users/' . $user['id']) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-row">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="<?= e($user['name']) ?>">
                        <?php if (!empty($errors['name'])): ?>
                            <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['name'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label class="form-label" for="username">Username <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required value="<?= e($user['username']) ?>">
                        <?php if (!empty($errors['username'])): ?>
                            <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['username'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Email Address -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span style="color: var(--danger);">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="+91 98111 22334" value="<?= e($user['phone'] ?? '') ?>">
                        <?php if (!empty($errors['phone'])): ?>
                            <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Role -->
                    <div class="form-group">
                        <label class="form-label" for="role">User Role <span style="color: var(--danger);">*</span></label>
                        <?php if ($isLastAdmin): ?>
                            <input type="hidden" name="role" value="admin">
                            <input type="text" class="form-control" value="👑 Administrator (Protected Sole Active Admin)" disabled style="background:#F1F5F9; color:#475569;">
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Cannot demote the only remaining active administrator.</small>
                        <?php else: ?>
                            <select id="role" name="role" class="form-control" required>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>👑 Administrator</option>
                                <option value="dispatcher" <?= $user['role'] === 'dispatcher' ? 'selected' : '' ?>>🎯 Dispatcher</option>
                                <option value="driver" <?= $user['role'] === 'driver' ? 'selected' : '' ?>>🚗 Chauffeur</option>
                                <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>👤 Customer</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label class="form-label" for="status">Account Status <span style="color: var(--danger);">*</span></label>
                        <?php if ($isSelf): ?>
                            <input type="hidden" name="status" value="active">
                            <input type="text" class="form-control" value="Active (Your Active Session)" disabled style="background:#F1F5F9; color:#475569;">
                            <small style="color: var(--text-muted); font-size: 0.75rem;">You cannot deactivate your own logged-in account.</small>
                        <?php elseif ($isLastAdmin): ?>
                            <input type="hidden" name="status" value="active">
                            <input type="text" class="form-control" value="Active (Sole Administrator Protected)" disabled style="background:#F1F5F9; color:#475569;">
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Cannot deactivate the only active administrator.</small>
                        <?php else: ?>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center; margin-top: 1rem;">
                    <a href="<?= url('/office/users') ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Reset Action Card (Separate from Profile Edit, Never Pre-filling Passwords) -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Reset User Password</span>
            <span class="badge badge-warning">Zero Plain-text Storage</span>
        </div>
        <div class="card-body">
            <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 1.25rem;">
                Set a new password for user <strong>@<?= e($user['username']) ?></strong>. Current passwords are cryptographically hashed and cannot be viewed.
            </p>

            <form action="<?= url('/office/users/' . $user['id'] . '/reset-password') ?>" method="POST" id="resetPasswordForm">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="reset_password">New Password <span style="color: var(--danger);">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="reset_password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters" style="padding-right: 2.75rem;">
                            <button type="button" onclick="toggleInput('reset_password')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;">
                                👁️
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="reset_confirmation">Confirm New Password <span style="color: var(--danger);">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="reset_confirmation" name="password_confirmation" class="form-control" required minlength="6" placeholder="Repeat new password" style="padding-right: 2.75rem;">
                            <button type="button" onclick="toggleInput('reset_confirmation')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;">
                                👁️
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-navy">
                        <span>Reset Password Now</span>
                    </button>
                </div>
            </form>
        </div>
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
