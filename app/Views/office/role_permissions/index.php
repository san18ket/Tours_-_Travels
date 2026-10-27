<?php
/**
 * Role & Permission Overview Directory
 * Matches exact UX architecture of the school management reference.
 */
$limit   = (int)($limit ?? 10);
$page    = (int)($page ?? 1);
$total   = (int)($total ?? 0);
$metrics = $metrics ?? [];

$totalRoles   = (int)($metrics['total_roles'] ?? count($roles ?? []));
$systemRoles  = count(array_filter($roles ?? [], fn($r) => (int)($r['is_system'] ?? 0) === 1));
$customRoles  = (int)($metrics['custom_roles'] ?? ($totalRoles - $systemRoles));
$totalPerms   = (int)($metrics['total_permissions'] ?? array_sum(array_column($roles ?? [], 'permission_count')));
?>
<style>
.rp-index-header {
    background: linear-gradient(135deg, var(--vg-navy-950, #030712) 0%, var(--vg-navy-900, #0a1329) 100%);
    margin: -1.5rem -1.5rem 1.5rem;
    padding: 26px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(3, 7, 18, 0.25);
}
.rp-index-header::after {
    content: "";
    position: absolute;
    inset: auto -8% -72% 35%;
    height: 220px;
    background: radial-gradient(ellipse, rgba(2, 132, 199, 0.25), transparent 68%);
    pointer-events: none;
}
.rp-index-header-inner { position: relative; z-index: 1; }
.rp-index-header h1 { font-size: 1.65rem; font-weight: 800; margin: 0 0 6px; }
.rp-index-header h1 i { color: #38bdf8; filter: drop-shadow(0 0 6px rgba(56, 189, 248, 0.5)); }
.rp-index-header p { color: rgba(248, 250, 252, 0.85); margin: 0; font-size: 0.9rem; }

.rp-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.rp-stat {
    background: #fff;
    border: 1px solid var(--vg-pearl-300, #e2e8f0);
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.rp-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(2, 132, 199, 0.12);
}
.rp-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #fff;
    flex-shrink: 0;
}
.rp-stat-info h3 { font-size: 1.35rem; font-weight: 800; margin: 0 0 2px; color: var(--vg-navy-900, #0a1329); }
.rp-stat-info p { font-size: 0.775rem; color: #64748b; margin: 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }

.rp-filter-card, .rp-table-card {
    background: #fff;
    border: 1px solid var(--vg-pearl-300, #e2e8f0);
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
}

.rp-tbl th {
    font-size: 0.72rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #64748b;
    white-space: nowrap;
    padding: 14px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.rp-tbl td { padding: 14px 14px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.rp-tbl tbody tr { transition: background 0.15s; }
.rp-tbl tbody tr:hover { background: #f8fafc; }

.rp-role-avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    flex-shrink: 0;
}

/* Action buttons matching reference */
.rp-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 0.8rem;
    transition: all 0.18s;
    text-decoration: none;
    cursor: pointer;
}
.rp-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08); }
.rp-action-btn.view:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
.rp-action-btn.edit-perm:hover { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
.rp-action-btn.edit-role:hover { background: #fefce8; border-color: #fde68a; color: #d97706; }
.rp-action-btn.delete:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
.rp-action-btn.disabled { opacity: 0.38; pointer-events: none; cursor: not-allowed; }

.rp-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 700;
}

@media(max-width: 767.98px) {
    .rp-index-header { margin: -1rem -1rem 1rem -1rem; padding: 1.25rem 1rem; }
    .rp-index-header h1 { font-size: 1.35rem; }
    .rp-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
</style>

<!-- HEADER -->
<?php
$rpActions = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">'
    . '<a href="' . url('/office/roles') . '" class="btn btn-outline btn-sm">'
    . '<i class="fa-solid fa-list me-1.5"></i>Role List</a>'
    . '<a href="' . url('/office/role-permission/assign') . '" class="btn btn-primary btn-sm">'
    . '<i class="fa-solid fa-user-shield me-2"></i>Assign Permissions</a>'
    . '</div>';
?>
<?= render_page_header([
    'title'       => 'Role & Permission Directory',
    'subtitle'    => 'Review role authorizations, assigned user accounts, and granular permission matrices across all 8 business modules.',
    'breadcrumbs' => [
        ['label' => 'Access Control', 'url' => url('/office/roles')],
        ['label' => 'Permissions Directory', 'url' => ''],
    ],
    'actions'     => $rpActions,
    'image'       => 'header-roles.jpg',
    'badge'       => $totalRoles . ' Defined Roles',
]) ?>

<!-- STAT CARDS -->
<div class="rp-stats">
    <div class="rp-stat">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #0284c7, #0369a1);"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="rp-stat-info"><h3><?= $totalRoles ?></h3><p>Total Roles</p></div>
    </div>
    <div class="rp-stat">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fa-solid fa-shield-check"></i></div>
        <div class="rp-stat-info"><h3><?= $systemRoles ?></h3><p>System Roles</p></div>
    </div>
    <div class="rp-stat">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fa-solid fa-sliders"></i></div>
        <div class="rp-stat-info"><h3><?= $customRoles ?></h3><p>Custom Roles</p></div>
    </div>
    <div class="rp-stat">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);"><i class="fa-solid fa-key"></i></div>
        <div class="rp-stat-info"><h3><?= $totalPerms ?></h3><p>Permissions Assigned</p></div>
    </div>
</div>

<!-- FILTER -->
<div class="rp-filter-card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3">
        <form method="GET" action="<?= url('/office/role-permission') ?>" class="row g-2 align-items-end flex-grow-1 mb-0">
            <div class="col-sm-8 col-lg-7">
                <label class="form-label small fw-bold text-muted mb-1">Search Roles</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="search" name="search" value="<?= e($search ?? '') ?>" class="form-control border-start-0" placeholder="Search by role name or description...">
                </div>
            </div>
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="<?= url('/office/role-permission') ?>" class="btn btn-light border text-muted fw-semibold py-2 px-3 rounded-3" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
        <div class="d-flex align-items-center">
            <a href="<?= url('/office/role-permission/assign') ?>" class="btn btn-primary fw-bold px-3.5 py-2 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2 text-nowrap w-100" style="background: var(--vg-blue-600, #0284c7); text-decoration: none;">
                <i class="fa-solid fa-user-shield"></i>
                <span>Assign Permissions</span>
            </a>
        </div>
    </div>
</div>

<!-- TABLE -->
<div class="rp-table-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 rp-tbl">
            <thead>
                <tr>
                    <th class="ps-3" style="width: 50px;">#</th>
                    <th style="min-width: 180px;">Role</th>
                    <th style="min-width: 140px;">System Key (Slug)</th>
                    <th style="min-width: 240px;">Description</th>
                    <th style="text-align: center;">Users</th>
                    <th style="text-align: center;">Permissions</th>
                    <th style="text-align: center;">Type</th>
                    <th>Last Updated</th>
                    <th class="text-end pe-3" style="min-width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($roles)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-shield-halved fa-2x mb-2 d-block opacity-40"></i>
                        <div class="fw-bold">No roles found</div>
                        <div class="small">Try adjusting your search query or create a new role from the <a href="<?= url('/office/roles') ?>">Roles Directory</a>.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($roles as $index => $role):
                    $roleName      = $role['name'];
                    $initial       = strtoupper(substr((string)$role['name'], 0, 1));
                    $isSystem      = (int)($role['is_system'] ?? 0) === 1;
                    $isOwnerRole   = in_array(strtolower((string)$role['slug']), ['admin', 'owner', 'superadmin']);
                    $canDelete     = !$isOwnerRole && !$isSystem && (int)($role['user_count'] ?? 0) === 0;
                    $canEditPerms  = true;
                    $updatedAt     = !empty($role['updated_at'] ?? $role['created_at'])
                                     ? date('M d, Y', strtotime($role['updated_at'] ?? $role['created_at']))
                                     : 'N/A';
                    $avatarBg = $isOwnerRole ? '#EDE9FE' : ($isSystem ? '#E0F2FE' : '#FEF3C7');
                    $avatarColor = $isOwnerRole ? '#7C3AED' : ($isSystem ? '#0284C7' : '#D97706');
                ?>
                <tr>
                    <td class="ps-3 text-muted small fw-bold"><?= (($page - 1) * $limit) + $index + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="rp-role-avatar" style="background: <?= $avatarBg ?>; color: <?= $avatarColor ?>;"><?= e($initial) ?></span>
                            <div>
                                <span class="fw-bold text-dark d-block"><?= e($roleName) ?></span>
                                <span class="badge" style="font-size: 0.65rem; background: <?= $isSystem ? 'rgba(2, 132, 199, 0.1)' : 'rgba(139, 92, 246, 0.1)' ?>; color: <?= $isSystem ? '#0284C7' : '#7C3AED' ?>;">
                                    <?= $isSystem ? 'SYSTEM' : 'CUSTOM' ?>
                                </span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="background: #f1f5f9; padding: 2px 7px; border-radius: 5px; font-size: 0.78rem; color: #0f172a; font-weight: 700;">
                            <?= e($role['slug']) ?>
                        </code>
                    </td>
                    <td class="text-muted small">
                        <span class="d-block" style="max-width: 260px; white-space: normal; line-height: 1.35;">
                            <?= e($role['description'] ?: 'No scope description defined.') ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="rp-badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                            <i class="fa-solid fa-users me-1"></i><?= (int)($role['user_count'] ?? 0) ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <?php $permCount = (int)($role['permission_count'] ?? 0); ?>
                        <span class="rp-badge" style="background: <?= $permCount > 0 ? '#ede9fe' : '#f1f5f9' ?>; color: <?= $permCount > 0 ? '#6d28d9' : '#64748b' ?>; border: 1px solid <?= $permCount > 0 ? '#ddd6fe' : '#e2e8f0' ?>;">
                            <i class="fa-solid fa-key me-1"></i><?= $permCount ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="rp-badge" style="background: <?= $isSystem ? '#dcfce7' : '#fef3c7' ?>; color: <?= $isSystem ? '#15803d' : '#b45309' ?>; border: 1px solid <?= $isSystem ? '#bbf7d0' : '#fde68a' ?>;">
                            <i class="fa-solid <?= $isSystem ? 'fa-shield-check' : 'fa-sliders' ?> me-1"></i>
                            <?= $isSystem ? 'System' : 'Custom' ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= $updatedAt ?></td>
                    <td class="text-end pe-3">
                        <div class="d-inline-flex align-items-center gap-1">
                            <!-- Assign Permissions -->
                            <a href="<?= url('/office/role-permission/assign?role_id=' . (int)$role['id']) ?>"
                               class="rp-action-btn edit-perm"
                               title="Configure Permissions">
                                <i class="fa-solid fa-key"></i>
                            </a>

                            <!-- View Permissions in Matrix -->
                            <a href="<?= url('/office/role-permission/assign?role_id=' . (int)$role['id']) ?>"
                               class="rp-action-btn view"
                               title="Inspect Role Matrix">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <!-- Delete Role -->
                            <?php if ($canDelete): ?>
                                <form action="<?= url('/office/roles/' . (int)$role['id'] . '/delete') ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete custom role \'<?= e($roleName) ?>\'? This action cannot be undone.')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="rp-action-btn delete" title="Delete Role">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            <?php elseif ($isOwnerRole || $isSystem): ?>
                                <span class="rp-action-btn disabled" title="Protected system role — cannot be deleted">
                                    <i class="fa-solid fa-shield"></i>
                                </span>
                            <?php else: ?>
                                <span class="rp-action-btn disabled" title="Cannot delete — role has active assigned users">
                                    <i class="fa-solid fa-users"></i>
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

    <!-- Legend & Pagination -->
    <div class="p-3 border-top bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap gap-3 small text-muted">
            <span><i class="fa-solid fa-key text-success me-1"></i>Configure Permissions</span>
            <span><i class="fa-solid fa-eye text-primary me-1"></i>Inspect Matrix</span>
            <span><i class="fa-solid fa-trash-can text-danger me-1"></i>Delete Custom Role</span>
            <span><i class="fa-solid fa-shield text-secondary me-1"></i>Protected Core Role</span>
        </div>
        <?php if ($total > $limit): ?>
            <div class="d-flex gap-1">
                <?php
                $totalPages = (int)ceil($total / $limit);
                for ($p = 1; $p <= $totalPages; $p++):
                ?>
                    <a href="<?= url('/office/role-permission?page=' . $p . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" 
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline-secondary' ?>" style="min-width: 32px;">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
