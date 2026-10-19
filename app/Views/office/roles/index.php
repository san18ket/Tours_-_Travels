<div class="roles-index-container" style="width: 100%;">
    <!-- Top Action Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
                <span class="badge badge-navy" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">Access Control Ops</span>
                <span class="badge badge-success" style="font-size: 0.72rem;"><span class="pulse-indicator"></span> RBAC Matrix Active</span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0;">Role List &amp; Operational Access</h1>
            <p style="color: var(--vg-text-secondary); font-size: 0.92rem; margin: 0.35rem 0 0 0;">Manage security roles, operational authority levels, and assigned user counts across the fleet.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="<?= url('/office/users/roles') ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Permissions Matrix</span>
            </a>
            <button type="button" class="btn btn-primary" onclick="openAddRoleModal()" style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 700; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Role</span>
            </button>
        </div>
    </div>

    <!-- Top Operational Metrics 4-Card Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 1.75rem;" class="roles-metrics-grid">
        <!-- Metric 1: Total Roles -->
        <div class="vg-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--vg-blue-600);">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-text-muted); margin-bottom: 0.25rem;">Total Roles</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--vg-navy-900);"><?= (int)($metrics['total_roles'] ?? 0) ?></div>
                <div style="font-size: 0.74rem; color: var(--vg-text-secondary); margin-top: 0.2rem;">Registered role tiers</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(2, 132, 199, 0.1); color: var(--vg-blue-600); display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
        </div>

        <!-- Metric 2: Active Roles -->
        <div class="vg-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #10B981;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-text-muted); margin-bottom: 0.25rem;">Active Roles</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #10B981;"><?= (int)($metrics['active_roles'] ?? 0) ?></div>
                <div style="font-size: 0.74rem; color: var(--vg-text-secondary); margin-top: 0.2rem;">Enabled for assignment</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10B981; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Metric 3: Total Assigned Users -->
        <div class="vg-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--vg-gold-600);">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-text-muted); margin-bottom: 0.25rem;">Assigned Users</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--vg-gold-600);"><?= (int)($metrics['total_users_assigned'] ?? 0) ?></div>
                <div style="font-size: 0.74rem; color: var(--vg-text-secondary); margin-top: 0.2rem;">Active account mappings</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(217, 119, 6, 0.1); color: var(--vg-gold-600); display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        <!-- Metric 4: Custom Roles -->
        <div class="vg-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #8B5CF6;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-text-muted); margin-bottom: 0.25rem;">Custom Roles</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #8B5CF6;"><?= (int)($metrics['custom_roles'] ?? 0) ?></div>
                <div style="font-size: 0.74rem; color: var(--vg-text-secondary); margin-top: 0.2rem;">Tenant custom defined</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.1); color: #8B5CF6; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="vg-card" style="margin-bottom: 1.75rem; padding: 1.25rem 1.5rem;">
        <form method="GET" action="<?= url('/office/roles') ?>" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <!-- Search Keyword -->
            <div style="flex: 1; min-width: 240px; position: relative;">
                <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Search by role title, slug, or description..." class="form-control" style="height: 42px; padding-left: 2.5rem; font-size: 0.88rem;">
                <span style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            <!-- Status Filter -->
            <div style="min-width: 160px;">
                <select name="status" class="form-select" style="height: 42px; font-size: 0.88rem;">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>

            <!-- Per Page -->
            <div style="min-width: 140px;">
                <select name="per_page" class="form-select" style="height: 42px; font-size: 0.88rem;">
                    <option value="10" <?= ((int)($perPage ?? 10) === 10) ? 'selected' : '' ?>>10 Per Page</option>
                    <option value="25" <?= ((int)($perPage ?? 10) === 25) ? 'selected' : '' ?>>25 Per Page</option>
                    <option value="50" <?= ((int)($perPage ?? 10) === 50) ? 'selected' : '' ?>>50 Per Page</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="<?= url('/office/roles') ?>" class="btn btn-outline" style="height: 42px; padding: 0 1rem; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Main Table Card with Top Add Role Button -->
    <div class="vg-card" style="box-shadow: 0 4px 20px rgba(7, 15, 30, 0.06); border: 1px solid var(--vg-pearl-300);">
        <!-- Table Top Header Action -->
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(2, 132, 199, 0.1); color: var(--vg-blue-600); display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </span>
                <div>
                    <span class="card-title" style="font-size: 1.05rem; font-weight: 700;">Operational Roles Directory</span>
                    <span style="font-size: 0.78rem; color: var(--vg-text-muted); margin-left: 0.5rem;">(<?= (int)($totalRecords ?? 0) ?> total roles found)</span>
                </div>
            </div>

            <!-- "+ Add Role" Button at Top of Table -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddRoleModal()" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 1rem; border-radius: 8px; font-weight: 700; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.28);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Role</span>
            </button>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th style="min-width: 200px;">Role Name</th>
                            <th style="min-width: 150px;">System Key (Slug)</th>
                            <th style="min-width: 220px;">Description</th>
                            <th style="min-width: 120px; text-align: center;">Users</th>
                            <th style="min-width: 120px; text-align: center;">Permissions</th>
                            <th style="min-width: 100px; text-align: center;">Status</th>
                            <th style="min-width: 120px;">Created Date</th>
                            <th style="min-width: 130px; text-align: right; padding-right: 1.5rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: var(--vg-text-muted);">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🛡️</div>
                                    <div style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900);">No Operational Roles Found</div>
                                    <div style="font-size: 0.85rem; margin-top: 0.25rem;">Try adjusting search filters or create a new role using the button above.</div>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddRoleModal()" style="margin-top: 1rem;">+ Add First Custom Role</button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roles as $idx => $r): 
                                $isSystem = (bool)$r['is_system'];
                                $isOwner = in_array(strtolower($r['slug']), ['admin', 'owner', 'superadmin'], true);
                                $permCount = (int)($r['permission_count'] ?? 0);
                                $canDelete = !$isSystem && !$isOwner && (int)($r['user_count'] ?? 0) === 0;
                            ?>
                                <tr>
                                    <td style="text-align: center; color: var(--vg-text-muted); font-size: 0.82rem; font-weight: 600;">
                                        <?= (($currentPage - 1) * $perPage) + ($idx + 1) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.55rem;">
                                            <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= $r['is_system'] ? 'rgba(2, 132, 199, 0.1)' : 'rgba(139, 92, 246, 0.1)' ?>; color: <?= $r['is_system'] ? 'var(--vg-blue-600)' : '#8B5CF6' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="fa-solid <?= $isSystem ? 'fa-shield-halved' : 'fa-sliders' ?>"></i>
                                            </div>
                                            <div>
                                                <strong style="color: var(--vg-navy-900); font-size: 0.92rem; display: block;"><?= e($r['name']) ?></strong>
                                                <?php if ($r['is_system']): ?>
                                                    <span class="badge badge-navy" style="font-size: 0.65rem; padding: 0.1rem 0.35rem; margin-top: 0.15rem;">SYSTEM ROLE</span>
                                                <?php else: ?>
                                                    <span class="badge" style="font-size: 0.65rem; padding: 0.1rem 0.35rem; margin-top: 0.15rem; background: #EDE9FE; color: #7C3AED;">CUSTOM ROLE</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code style="background: rgba(7, 15, 30, 0.05); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.82rem; font-family: monospace; color: var(--vg-navy-900); font-weight: 700;">
                                            <?= e($r['slug']) ?>
                                        </code>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.84rem; color: var(--vg-text-secondary); line-height: 1.4; max-width: 240px;">
                                            <?= e($r['description'] ?? 'No operational description defined.') ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= ((int)($r['user_count'] ?? 0) > 0) ? 'badge-blue' : 'badge-neutral' ?>" style="font-size: 0.78rem; padding: 0.25rem 0.65rem; font-weight: 700;">
                                            <?= (int)($r['user_count'] ?? 0) ?> user<?= (int)($r['user_count'] ?? 0) === 1 ? '' : 's' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= url('/office/role-permission/assign?role_id=' . (int)$r['id']) ?>" 
                                           class="badge" 
                                           title="Configure Permissions Matrix"
                                           style="font-size: 0.78rem; padding: 0.25rem 0.65rem; font-weight: 700; text-decoration: none; background: <?= $permCount > 0 ? '#ede9fe' : '#f1f5f9' ?>; color: <?= $permCount > 0 ? '#6d28d9' : '#64748b' ?>; border: 1px solid <?= $permCount > 0 ? '#ddd6fe' : '#e2e8f0' ?>;">
                                            <i class="fa-solid fa-key me-1"></i><?= $permCount ?> perms
                                        </a>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($r['status'] === 'active'): ?>
                                            <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">INACTIVE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; color: var(--vg-text-muted);">
                                            <?= date('M d, Y', strtotime($r['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: right; padding-right: 1.5rem;">
                                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <!-- Assign Permissions -->
                                            <a href="<?= url('/office/role-permission/assign?role_id=' . (int)$r['id']) ?>" 
                                               class="btn btn-sm btn-outline" 
                                               title="Configure Permissions"
                                               style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #16a34a; border-color: #bbf7d0; background: #f0fdf4;">
                                                <i class="fa-solid fa-key" style="font-size: 0.82rem;"></i>
                                            </a>

                                            <!-- Edit Role -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline"
                                                    title="Edit Role Scope"
                                                    onclick="openEditRoleModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"
                                                    style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #d97706; border-color: #fde68a; background: #fefce8;">
                                                <i class="fa-solid fa-pen-to-square" style="font-size: 0.82rem;"></i>
                                            </button>

                                            <!-- Delete Role -->
                                            <?php if ($canDelete): ?>
                                                <form action="<?= url('/office/roles/' . (int)$r['id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete custom role \'<?= e($r['name']) ?>\'? This action cannot be undone.')">
                                                    <?= csrfField() ?>
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline" 
                                                            title="Delete Role"
                                                            style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                                                        <i class="fa-solid fa-trash-can" style="font-size: 0.82rem;"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="btn btn-sm btn-outline disabled" 
                                                      title="<?= $isOwner || $isSystem ? 'Core system roles cannot be deleted' : 'Cannot delete role with assigned active users' ?>"
                                                      style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; opacity: 0.35; cursor: not-allowed; background: #f1f5f9;">
                                                    <i class="fa-solid fa-shield" style="font-size: 0.82rem;"></i>
                                                </span>
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
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-top: 1px solid var(--vg-pearl-200); flex-wrap: wrap; gap: 1rem;">
                    <div style="font-size: 0.84rem; color: var(--vg-text-muted);">
                        Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (Total <strong><?= $totalRecords ?></strong> roles)
                    </div>

                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= url('/office/roles?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="btn btn-outline btn-sm">
                                &larr; Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 1): ?>
                                <a href="<?= url('/office/roles?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn btn-sm <?= $p === $currentPage ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 34px; justify-content: center;">
                                    <?= $p ?>
                                </a>
                            <?php elseif ($p === 2 && $currentPage > 3): ?>
                                <span style="padding: 0 0.25rem; color: var(--vg-text-muted);">...</span>
                            <?php elseif ($p === $totalPages - 1 && $currentPage < $totalPages - 2): ?>
                                <span style="padding: 0 0.25rem; color: var(--vg-text-muted);">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= url('/office/roles?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="btn btn-outline btn-sm">
                                Next &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Professional Modal Box: Add Role -->
<!-- ========================================================================= -->
<div id="addRoleModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(7, 15, 30, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;" onclick="handleModalBackdropClick(event)">
    <div style="background: #FFFFFF; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 45px rgba(7, 15, 30, 0.25); border: 1px solid var(--vg-pearl-300); overflow: hidden; animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, var(--vg-navy-950) 0%, var(--vg-navy-900) 100%); color: #FFF;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(2, 132, 199, 0.2); color: var(--vg-blue-400); display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </span>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #FFF;">Add Operational Role</h3>
                    <div style="font-size: 0.74rem; color: #94A3B8;">Provision new operational authorization tier</div>
                </div>
            </div>
            <button type="button" onclick="closeAddRoleModal()" style="background: none; border: none; color: #94A3B8; font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 0.25rem; transition: color 0.15s ease;" title="Close Modal">&times;</button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= url('/office/roles') ?>" method="POST" id="addRoleForm" onsubmit="return validateRoleForm()">
            <?= csrf_field() ?>

            <div style="padding: 1.5rem;">
                <!-- Role Name Input -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="roleNameInput" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.45rem;">
                        Role Name <span class="required-indicator">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" id="roleNameInput" name="name" class="form-control" required placeholder="e.g. Operations Manager, Fleet Auditor, VIP Concierge" oninput="updateSlugPreview(this.value)" autocomplete="off" style="height: 48px; padding-left: 2.6rem; font-size: 0.95rem; font-weight: 600;">
                        <span style="position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted); pointer-events: none;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                        </span>
                    </div>

                    <!-- Live Slug Preview Badge -->
                    <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem; font-size: 0.76rem; color: var(--vg-text-muted);">
                        <span>Generated System Key:</span>
                        <code id="slugPreview" style="background: rgba(2, 132, 199, 0.08); color: var(--vg-blue-600); padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; font-family: monospace;">custom_role</code>
                    </div>
                </div>

                <!-- Description Input (Optional) -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="roleDescInput" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem;">
                        Operational Description (Optional)
                    </label>
                    <textarea id="roleDescInput" name="description" class="form-control" rows="3" placeholder="Brief summary of duties, clearance, and operational responsibilities..." style="font-size: 0.88rem;"></textarea>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding: 1.15rem 1.5rem; background: var(--vg-pearl-100); border-top: 1px solid var(--vg-pearl-200);">
                <button type="button" class="btn btn-outline" onclick="closeAddRoleModal()" style="height: 42px; padding: 0 1.25rem;">
                    Cancel
                </button>
                <button type="submit" id="saveRoleBtn" class="btn btn-primary" style="height: 42px; padding: 0 1.5rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 2px 10px rgba(2, 132, 199, 0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Role</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT ROLE MODAL -->
<!-- ========================================================================= -->
<div id="editRoleModal" style="display: none; position: fixed; inset: 0; z-index: 1050; background: rgba(7, 15, 30, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="handleEditModalBackdropClick(event)">
    <div class="modal-dialog-card" style="width: 100%; max-width: 520px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 60px rgba(7, 15, 30, 0.35); border: 1px solid var(--vg-pearl-300); overflow: hidden; animation: modalPop 0.22s ease-out;" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--vg-pearl-200); background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #d97706, #b45309); color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--vg-navy-900);">Edit Operational Role</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--vg-text-muted);">Update role description or scope parameters</p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" style="border: none; background: transparent; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--vg-text-muted); font-size: 1.25rem;">
                &times;
            </button>
        </div>

        <!-- Modal Form -->
        <form id="editRoleForm" method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" id="editRoleId" name="id" value="">

            <div style="padding: 1.5rem;">
                <!-- Role Name Input -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="editRoleNameInput" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.45rem;">
                        Role Name <span class="required-indicator">*</span>
                    </label>
                    <input type="text" id="editRoleNameInput" name="name" class="form-control" required style="height: 48px; font-size: 0.95rem; font-weight: 600;">
                    <div id="editSystemRoleNotice" style="display: none; margin-top: 0.4rem; font-size: 0.75rem; color: #b45309;">
                        <i class="fa-solid fa-shield-halved me-1"></i>System role titles are locked to preserve software routing.
                    </div>
                </div>

                <!-- Slug Display -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem;">
                        System Key (Slug)
                    </label>
                    <input type="text" id="editRoleSlugDisplay" class="form-control" disabled style="height: 44px; font-family: monospace; background: #f8fafc; color: #475569; font-weight: 700;">
                </div>

                <!-- Description Input -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="editRoleDescInput" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem;">
                        Operational Description
                    </label>
                    <textarea id="editRoleDescInput" name="description" class="form-control" rows="3" style="font-size: 0.88rem;"></textarea>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding: 1.15rem 1.5rem; background: var(--vg-pearl-100); border-top: 1px solid var(--vg-pearl-200);">
                <button type="button" class="btn btn-outline" onclick="closeEditRoleModal()" style="height: 42px; padding: 0 1.25rem;">
                    Cancel
                </button>
                <button type="submit" id="updateRoleBtn" class="btn btn-primary" style="height: 42px; padding: 0 1.5rem; font-weight: 700; background: #d97706; border-color: #d97706;">
                    <i class="fa-solid fa-check me-1"></i>
                    <span>Update Role</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(8px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@media (max-width: 900px) {
    .roles-metrics-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (max-width: 600px) {
    .roles-metrics-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function openAddRoleModal() {
    const modal = document.getElementById('addRoleModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const input = document.getElementById('roleNameInput');
            if (input) input.focus();
        }, 80);
    }
}

function closeAddRoleModal() {
    const modal = document.getElementById('addRoleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        const form = document.getElementById('addRoleForm');
        if (form) form.reset();
        updateSlugPreview('');
    }
}

function handleModalBackdropClick(e) {
    if (e.target && e.target.id === 'addRoleModal') {
        closeAddRoleModal();
    }
}

function openEditRoleModal(role) {
    if (!role) return;
    const modal = document.getElementById('editRoleModal');
    const form = document.getElementById('editRoleForm');
    const nameInput = document.getElementById('editRoleNameInput');
    const slugInput = document.getElementById('editRoleSlugDisplay');
    const descInput = document.getElementById('editRoleDescInput');
    const idInput = document.getElementById('editRoleId');
    const notice = document.getElementById('editSystemRoleNotice');

    form.action = `<?= url('/office/roles/') ?>${role.id}/update`;
    idInput.value = role.id;
    nameInput.value = role.name;
    slugInput.value = role.slug;
    descInput.value = role.description || '';

    if (role.is_system && Number(role.is_system) === 1) {
        nameInput.readOnly = true;
        notice.style.display = 'block';
    } else {
        nameInput.readOnly = false;
        notice.style.display = 'none';
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditRoleModal() {
    const modal = document.getElementById('editRoleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function handleEditModalBackdropClick(e) {
    if (e.target && e.target.id === 'editRoleModal') {
        closeEditRoleModal();
    }
}

function updateSlugPreview(name) {
    const preview = document.getElementById('slugPreview');
    if (!preview) return;

    let slug = name.toLowerCase().trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');

    preview.textContent = slug || 'custom_role';
}

function validateRoleForm() {
    const nameInput = document.getElementById('roleNameInput');
    if (!nameInput || !nameInput.value.trim()) {
        alert('Please enter a role name.');
        if (nameInput) nameInput.focus();
        return false;
    }
    const saveBtn = document.getElementById('saveRoleBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span>Saving...</span>`;
    }
    return true;
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddRoleModal();
        closeEditRoleModal();
    }
});
</script>
