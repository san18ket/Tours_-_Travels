<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--navy-900); letter-spacing: -0.02em;">User Accounts &amp; Access Control</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Manage system administrators, dispatchers, chauffeurs, and client portal credentials.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/users/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>Add New User</span>
        </a>
    </div>
</div>

<!-- 4 KPI Stat Cards -->
<div class="grid-cols-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Accounts</div>
            <div class="stat-value"><?= (int)$totalAll ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon navy">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Administrators</div>
            <div class="stat-value">
                <?= (int)$activeAdmins ?>
                <?php if ($activeAdmins === 1): ?>
                    <span class="badge badge-warning" style="font-size:0.7rem;vertical-align:middle;margin-left:4px;">Protected Core</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Dispatchers</div>
            <div class="stat-value"><?= (int)$totalDispatchers ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Chauffeurs on Roster</div>
            <div class="stat-value"><?= (int)$totalDrivers ?></div>
        </div>
    </div>
</div>

<!-- Search & Filtering Toolbar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" action="<?= url('/office/users') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label" for="search">Search Users</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Search by name, username, email, or phone..." value="<?= e($search) ?>">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" for="role">Filter by Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    <option value="dispatcher" <?= $selectedRole === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
                    <option value="driver" <?= $selectedRole === 'driver' ? 'selected' : '' ?>>Chauffeur</option>
                    <option value="customer" <?= $selectedRole === 'customer' ? 'selected' : '' ?>>Customer</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" for="status">Account Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= $selectedStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-navy">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Filter</span>
                </button>
                <?php if (!empty($search) || !empty($selectedRole) || !empty($selectedStatus)): ?>
                    <a href="<?= url('/office/users') ?>" class="btn btn-outline" title="Reset Filters">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Data Table Card -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Registered System Users (<?= (int)$totalUsers ?> matching)</span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Page <?= (int)$currentPage ?> of <?= (int)$totalPages ?></span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>User Profile</th>
                        <th>Username</th>
                        <th>Email &amp; Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
                                <div style="font-weight: 600; font-size: 1.1rem; color: var(--navy-900);">No user accounts found</div>
                                <p style="font-size: 0.88rem;">Try adjusting your search criteria or register a new user.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><span style="font-family: monospace; color: var(--text-muted); font-size: 0.85rem;">#<?= (int)$u['id'] ?></span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--navy-800); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong style="color: var(--navy-900);"><?= e($u['name']) ?></strong>
                                            <?php if ((int)$u['id'] === $currentUserId): ?>
                                                <span class="badge badge-navy" style="font-size: 0.68rem; margin-left: 4px;">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; color: var(--blue-600); font-weight: 600;">@<?= e($u['username']) ?></code>
                                </td>
                                <td>
                                    <div><?= e($u['email']) ?></div>
                                    <?php if (!empty($u['phone'])): ?>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);"><?= e($u['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge badge-navy">👑 Admin</span>
                                    <?php elseif ($u['role'] === 'dispatcher'): ?>
                                        <span class="badge badge-primary">🎯 Dispatcher</span>
                                    <?php elseif ($u['role'] === 'driver'): ?>
                                        <span class="badge badge-info">🚗 Chauffeur</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">👤 Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge badge-success"><span class="pulse-indicator"></span> Active</span>
                                    <?php elseif ($u['status'] === 'inactive'): ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?= e(ucfirst($u['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <?= format_date($u['created_at']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                                        <!-- Edit Action -->
                                        <a href="<?= url('/office/users/' . $u['id'] . '/edit') ?>" class="btn btn-outline btn-sm" title="Edit User Profile">
                                            Edit
                                        </a>

                                        <!-- Reset Password Action Trigger -->
                                        <button type="button" class="btn btn-outline btn-sm" onclick="openResetModal(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['username'])) ?>')" title="Reset Password">
                                            🔑 Reset
                                        </button>

                                        <!-- Toggle Status (Activate / Deactivate) -->
                                        <?php if ((int)$u['id'] === $currentUserId): ?>
                                            <button type="button" class="btn btn-outline btn-sm" disabled title="You cannot deactivate your own account" style="opacity: 0.5; cursor: not-allowed;">
                                                Self
                                            </button>
                                        <?php elseif ($u['role'] === 'admin' && $u['status'] === 'active' && $activeAdmins <= 1): ?>
                                            <button type="button" class="btn btn-outline btn-sm" disabled title="Sole active administrator is protected" style="opacity: 0.5; cursor: not-allowed;">
                                                Protected
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= url('/office/users/' . $u['id'] . '/toggle-status') ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to <?= $u['status'] === 'active' ? 'deactivate' : 'activate' ?> user @<?= e(addslashes($u['username'])) ?>?');">
                                                <?= csrf_field() ?>
                                                <?php if ($u['status'] === 'active'): ?>
                                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.4);" title="Deactivate user">
                                                        Deactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--success); border-color: rgba(16, 185, 129, 0.4);" title="Activate user">
                                                        Activate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-top: 1px solid var(--border-light); flex-wrap: wrap; gap: 0.75rem;">
                <div style="font-size: 0.88rem; color: var(--text-secondary);">
                    Showing page <strong><?= (int)$currentPage ?></strong> of <strong><?= (int)$totalPages ?></strong>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('/office/users?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '&role=' . urlencode($selectedRole) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-outline btn-sm">
                            &laquo; Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == $currentPage): ?>
                            <span class="btn btn-primary btn-sm" style="cursor: default;"><?= $p ?></span>
                        <?php elseif ($p == 1 || $p == $totalPages || abs($p - $currentPage) <= 2): ?>
                            <a href="<?= url('/office/users?page=' . $p . '&search=' . urlencode($search) . '&role=' . urlencode($selectedRole) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-outline btn-sm">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('/office/users?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '&role=' . urlencode($selectedRole) . '&status=' . urlencode($selectedStatus)) ?>" class="btn btn-outline btn-sm">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Reset User Password -->
<div id="resetPasswordModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(11, 25, 44, 0.65); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 1.5rem;">
    <div class="card" style="width: 100%; max-width: 440px; margin: 0; box-shadow: var(--shadow-lg);">
        <div class="card-header">
            <span class="card-title">Reset User Password</span>
            <button type="button" onclick="closeResetModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div class="card-body">
            <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 1.25rem;">
                Set a new password for account: <strong id="resetTargetUsername" style="color: var(--navy-900);"></strong>
            </p>

            <form id="resetPasswordForm" method="POST" action="">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters" style="padding-right: 2.75rem;">
                        <button type="button" onclick="toggleInputVisibility('new_password')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-control" required minlength="6" placeholder="Repeat new password" style="padding-right: 2.75rem;">
                        <button type="button" onclick="toggleInputVisibility('new_password_confirmation')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;">
                            👁️
                        </button>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeResetModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetModal(userId, username) {
    document.getElementById('resetTargetUsername').innerText = '@' + username;
    document.getElementById('resetPasswordForm').action = '<?= url('/office/users/') ?>' + userId + '/reset-password';
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirmation').value = '';
    const modal = document.getElementById('resetPasswordModal');
    modal.style.display = 'flex';
}

function closeResetModal() {
    const modal = document.getElementById('resetPasswordModal');
    modal.style.display = 'none';
}

function toggleInputVisibility(id) {
    const el = document.getElementById(id);
    if (el) {
        el.type = (el.type === 'password') ? 'text' : 'password';
    }
}
</script>
