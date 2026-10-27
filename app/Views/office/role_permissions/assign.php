<?php
/**
 * Role Permission Assignment Matrix
 * Exact replication of the enterprise architecture from school-management-system.
 */
$scopeMode = (string)($scopeMode ?? 'role'); // 'role' or 'user'
$role = $role ?? null;
$roles = $roles ?? [];
$users = $users ?? [];
$user = $user ?? null;
$catalogue = $catalogue ?? [];

$isOwnerRole = (bool)($isOwnerRole ?? false);
if (!$isOwnerRole && $role && in_array(strtolower((string)($role['slug'] ?? '')), ['admin', 'owner', 'superadmin'])) {
    $isOwnerRole = true;
}
$isOwnerUser = (bool)($user && strtolower((string)($user['role'] ?? '')) === 'admin');
$isOwner = $scopeMode === 'user' ? $isOwnerUser : $isOwnerRole;

$selectedPermissionIds = array_map('intval', $selectedPermissionIds ?? []);
$selectedPermissionNames = (array)($selectedPermissionNames ?? []);
?>
<style>
:root {
    --rp-primary: #176B5B;
    --rp-primary-dark: #125748;
    --rp-cyan: #1E7A68;
    --rp-success: #10b981;
    --rp-warning: #f59e0b;
    --rp-danger: #ef4444;
    --rp-border: #e2e8f0;
    --rp-bg: #f8fafc;
    --rp-muted: #64748b;
    --rp-dark: #0f172a;
}

/* ── PAGE HEADER ── */
.rp-header {
    background: linear-gradient(135deg, var(--vg-navy-950, #030712) 0%, var(--vg-navy-900, #0a1329) 100%);
    margin: -1.5rem -1.5rem 2rem;
    padding: 30px 36px;
    color: #fff;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(3, 7, 18, 0.25);
}
.rp-header::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(23, 107, 91, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.rp-header::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(30, 122, 104, 0.18) 0%, transparent 70%);
    pointer-events: none;
}
.rp-header-inner { position: relative; z-index: 1; }
.rp-header-title { font-size: 1.75rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -.025em; }
.rp-header-title i { color: #176B5B; filter: drop-shadow(0 0 10px rgba(23, 107, 91, 0.5)); }
.rp-header-sub { color: rgba(248, 250, 252, 0.85); font-size: 0.9rem; margin: 0; }
.rp-header-btn {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #fff;
    padding: 9px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s;
    backdrop-filter: blur(4px);
}
.rp-header-btn:hover { background: rgba(255, 255, 255, 0.22); color: #fff; transform: translateY(-1px); }

/* ── SCOPE SWITCHER ── */
.rp-scope-bar {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 16px;
    padding: 10px 14px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.rp-scope-nav {
    display: inline-flex;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 12px;
    gap: 4px;
}
.rp-scope-btn {
    border: none;
    background: transparent;
    padding: 8px 18px;
    border-radius: 9px;
    font-size: 0.88rem;
    font-weight: 800;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    text-decoration: none;
}
.rp-scope-btn:hover { color: #1e293b; }
.rp-scope-btn.active {
    background: #fff;
    color: var(--vg-navy-900, #0a1329);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

/* ── STEP BADGE ── */
.rp-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 800;
    flex-shrink: 0;
}

/* ── PANEL CARDS ── */
.rp-panel {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
}
.rp-panel-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--rp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.rp-panel-title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--rp-dark);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}
.rp-panel-body { padding: 22px; }

/* ── SELECTORS ── */
.rp-select {
    height: 48px;
    border-radius: 12px;
    border: 2px solid var(--rp-border);
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--rp-dark);
    padding: 0 16px;
    transition: all 0.2s;
    background: var(--rp-bg);
    width: 100%;
}
.rp-select:focus {
    border-color: var(--rp-primary);
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
    background: #fff;
    outline: none;
}

/* ── ENTITY INFO BOX ── */
.rp-entity-info {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    border: 1px solid #bae6fd;
    border-radius: 12px;
    padding: 16px 20px;
}
.rp-entity-avatar {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    font-weight: 800;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
}

/* ── QUICK PILLS ── */
.rp-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.rp-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 99px;
    border: 1.5px solid var(--rp-border);
    background: #fff;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s;
    text-decoration: none;
}
.rp-pill:hover { background: #f0f9ff; border-color: #7dd3fc; color: var(--rp-primary); transform: translateY(-1px); }
.rp-pill.active {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
}

/* ── TOOLBAR ── */
.rp-toolbar {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 16px;
    padding: 16px 22px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
}
.rp-search-wrap { position: relative; flex: 1; min-width: 240px; }
.rp-search-wrap .rp-search-icon {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}
.rp-search-input {
    height: 42px;
    padding-left: 42px;
    border-radius: 10px;
    border: 1.5px solid var(--rp-border);
    font-size: 0.9rem;
    width: 100%;
    transition: all 0.2s;
    background: var(--rp-bg);
}
.rp-search-input:focus {
    border-color: var(--rp-primary);
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    background: #fff;
    outline: none;
}

/* ── CATEGORY SECTION ── */
.rp-cat-section { margin-bottom: 28px; }
.rp-cat-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 12px 18px;
    background: linear-gradient(135deg, #f8fafc, #f0f9ff);
    border: 1px solid #e0f2fe;
    border-radius: 12px;
}
.rp-cat-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    font-weight: 800;
    color: var(--rp-dark);
}
.rp-cat-label .cat-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);
}

/* ── CARDS GRID ── */
.rp-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
    gap: 16px;
}

/* ── PAGE CARD ── */
.rp-page-card {
    background: #fff;
    border: 2px solid var(--rp-border);
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.rp-page-card:hover {
    border-color: #7dd3fc;
    box-shadow: 0 6px 20px rgba(2, 132, 199, 0.1);
    transform: translateY(-1px);
}
.rp-page-card.access-granted {
    border-color: var(--rp-primary);
    box-shadow: 0 4px 16px rgba(2, 132, 199, 0.12);
}
.rp-page-card.access-granted .rp-card-head {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-bottom-color: #bae6fd;
}
.rp-page-card.owner-locked {
    border-color: var(--rp-warning) !important;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.12) !important;
}
.rp-page-card.owner-locked .rp-card-head {
    background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
    border-bottom-color: #fde68a !important;
}

/* ── CARD HEAD ── */
.rp-card-head {
    padding: 14px 16px;
    background: var(--rp-bg);
    border-bottom: 1px solid var(--rp-border);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    transition: background 0.2s;
}
.rp-card-title-row { display: flex; align-items: flex-start; gap: 11px; flex: 1; min-width: 0; }
.rp-main-chk {
    width: 20px; height: 20px;
    border-radius: 6px;
    border: 2px solid #94a3b8;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 3px;
    accent-color: var(--rp-primary);
}
.rp-card-page-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--rp-dark);
    line-height: 1.3;
    margin-bottom: 4px;
    cursor: pointer;
}
.rp-card-route {
    font-size: 0.7rem;
    font-family: monospace;
    color: var(--rp-muted);
    background: #f1f5f9;
    padding: 2px 7px;
    border-radius: 5px;
    display: inline-block;
}

/* ── CARD BODY ── */
.rp-card-body-inner {
    padding: 14px 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.rp-sub-title {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--rp-muted);
    margin-bottom: 7px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.rp-btn-list { display: flex; flex-direction: column; gap: 5px; }
.rp-btn-row {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 7px 10px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: var(--rp-bg);
    cursor: pointer;
    transition: all 0.15s;
}
.rp-btn-row:hover { background: #f0f9ff; border-color: #bae6fd; }
.rp-btn-row.is-checked { background: #f0fdf4; border-color: #bbf7d0; }
.rp-btn-chk {
    width: 16px; height: 16px;
    border-radius: 4px;
    border: 1.5px solid #94a3b8;
    cursor: pointer;
    flex-shrink: 0;
    accent-color: var(--rp-success);
}
.rp-btn-icon-wrap {
    width: 26px; height: 26px;
    border-radius: 7px;
    background: #fff;
    border: 1px solid var(--rp-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem;
    color: #475569;
    flex-shrink: 0;
}
.rp-btn-label { font-size: 0.82rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.rp-btn-desc { font-size: 0.68rem; color: var(--rp-muted); display: block; margin-top: 1px; }

/* ── OWNER BANNER ── */
.rp-owner-banner {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 1px solid #fde68a;
    border-left: 5px solid var(--rp-warning);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 24px;
}
.rp-owner-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 3px 10px rgba(245, 158, 11, 0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    color: #d97706;
    flex-shrink: 0;
}

/* ── STICKY ACTION BAR ── */
.rp-action-bar {
    position: sticky;
    bottom: 16px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    border: 1px solid var(--vg-pearl-300, #e2e8f0);
    border-radius: 16px;
    box-shadow: 0 10px 35px rgba(15, 23, 42, 0.12);
    padding: 14px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 28px;
    z-index: 1000;
    gap: 12px;
    flex-wrap: wrap;
}
.rp-bar-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--rp-dark);
}
.rp-bar-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

/* ── BULK BUTTONS ── */
.rp-bulk-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 9px;
    font-size: 0.8rem;
    font-weight: 700;
    border: 1.5px solid;
    cursor: pointer;
    transition: all 0.18s;
    background: #fff;
}
.rp-bulk-btn.success { border-color: #86efac; color: #15803d; }
.rp-bulk-btn.success:hover { background: #dcfce7; }
.rp-bulk-btn.danger { border-color: #fca5a5; color: #dc2626; }
.rp-bulk-btn.danger:hover { background: #fee2e2; }
</style>

<!-- HEADER BANNER -->
<?php
$assignActions = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">'
    . '<a href="' . url('/office/role-permission') . '" class="btn btn-outline btn-sm">'
    . '<i class="fa-solid fa-arrow-left me-1.5"></i>Permissions Overview</a>'
    . '<a href="' . url('/office/roles') . '" class="btn btn-outline btn-sm">'
    . '<i class="fa-solid fa-shield-halved me-1.5"></i>Roles Directory</a>'
    . '</div>';
?>
<?= render_page_header([
    'title'       => 'Role Permission Assignment Matrix',
    'subtitle'    => 'Configure granular access rights, page routes, and functional buttons for operational roles & accounts.',
    'breadcrumbs' => [
        ['label' => 'Access Control', 'url' => url('/office/roles')],
        ['label' => 'Permissions Overview', 'url' => url('/office/role-permission')],
        ['label' => 'Assignment Matrix', 'url' => ''],
    ],
    'actions'     => $assignActions,
    'image'       => 'header-roles.jpg',
    'badge'       => 'Enterprise Security Matrix',
]) ?>

<!-- SCOPE SWITCHER BAR -->
<div class="rp-scope-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="rp-step-badge">1</span>
        <span class="fw-bold text-dark">Target Scope:</span>
        <div class="rp-scope-nav">
            <a href="<?= url('/office/role-permission/assign?scope=role' . ($role ? '&role_id=' . $role['id'] : '')) ?>" 
               class="rp-scope-btn <?= $scopeMode === 'role' ? 'active' : '' ?>">
                <i class="fa-solid fa-shield-halved"></i>Role Scope
            </a>
            <a href="<?= url('/office/role-permission/assign?scope=user' . ($user ? '&user_id=' . $user['id'] : '')) ?>" 
               class="rp-scope-btn <?= $scopeMode === 'user' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-lock"></i>User Scope (Overrides)
            </a>
        </div>
    </div>
    <div class="text-muted small">
        <?= $scopeMode === 'role' ? 'Editing global baseline permissions assigned to roles.' : 'Configuring individual account permission overrides.' ?>
    </div>
</div>

<form id="permissionForm" method="POST" action="<?= url('/office/role-permission') ?>">
    <?= csrfField() ?>
    <input type="hidden" name="scope_mode" value="<?= e($scopeMode) ?>">
    <input type="hidden" name="role_id" id="selectedRoleId" value="<?= $role ? (int)$role['id'] : 0 ?>">
    <input type="hidden" name="user_id" id="selectedUserId" value="<?= $user ? (int)$user['id'] : 0 ?>">

    <!-- TARGET SELECTOR PANEL -->
    <div class="rp-panel mb-4">
        <div class="rp-panel-header">
            <div class="rp-panel-title">
                <span class="rp-step-badge">2</span>
                <span>Select Target <?= $scopeMode === 'role' ? 'Operational Role' : 'User Account' ?></span>
            </div>
            <?php if ($scopeMode === 'role'): ?>
                <a href="<?= url('/office/roles') ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-3">
                    <i class="fa-solid fa-plus me-1"></i>New Role
                </a>
            <?php endif; ?>
        </div>
        <div class="rp-panel-body">
            <?php if ($scopeMode === 'role'): ?>
                <div class="row align-items-center g-3">
                    <div class="col-lg-6">
                        <label class="form-label fw-bold small text-muted mb-1">Select Role from Registry</label>
                        <select id="roleSelector" class="rp-select" onchange="onRoleChange(this.value)">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($role && (int)$role['id'] === (int)$r['id']) ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?> (<?= e($r['slug']) ?>) &mdash; <?= (int)($r['user_count'] ?? 0) ?> users
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="rp-pills">
                            <?php foreach ($roles as $r): ?>
                                <a href="<?= url('/office/role-permission/assign?scope=role&role_id=' . $r['id']) ?>" 
                                   class="rp-pill <?= ($role && (int)$role['id'] === (int)$r['id']) ? 'active' : '' ?>">
                                    <i class="fa-solid fa-shield-halved"></i><?= e($r['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <?php if ($role): ?>
                            <div class="rp-entity-info d-flex align-items-center gap-3">
                                <div class="rp-entity-avatar">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h4 class="m-0 fw-bold text-dark fs-6" id="entityName"><?= e($role['name']) ?></h4>
                                        <span class="badge bg-primary" id="entityBadge"><?= e($role['slug']) ?></span>
                                    </div>
                                    <p class="m-0 text-muted small text-truncate" id="entityDesc"><?= e($role['description'] ?: 'No scope description defined.') ?></p>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5 text-primary" id="entityPermCount"><?= count($selectedPermissionIds) ?></div>
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Permissions</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- User Scope Selector -->
                <div class="row align-items-center g-3">
                    <div class="col-lg-6">
                        <label class="form-label fw-bold small text-muted mb-1">Select User Account</label>
                        <select id="userSelector" class="rp-select" onchange="onUserChange(this.value)">
                            <option value="">-- Choose User Account --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($user && (int)$user['id'] === (int)$u['id']) ? 'selected' : '' ?>>
                                    <?= e($u['name'] ?: $u['username']) ?> (@<?= e($u['username']) ?>) &mdash; Role: <?= e($u['role_name'] ?: $u['role']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <?php if ($user): ?>
                            <div class="rp-entity-info d-flex align-items-center gap-3">
                                <div class="rp-entity-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h4 class="m-0 fw-bold text-dark fs-6"><?= e($user['name'] ?: $user['username']) ?></h4>
                                        <span class="badge bg-success">@<?= e($user['username']) ?></span>
                                    </div>
                                    <p class="m-0 text-muted small">Inherits baseline permissions from role: <strong><?= e($user['role_name'] ?: $user['role']) ?></strong></p>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5 text-success"><?= count($selectedPermissionIds) ?></div>
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Active Rights</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- OWNER / ADMINISTRATOR BANNER -->
    <?php if ($isOwner): ?>
        <div class="rp-owner-banner">
            <div class="d-flex align-items-center gap-3">
                <div class="rp-owner-icon"><i class="fa-solid fa-crown"></i></div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Administrator Master Access Granted</h5>
                    <p class="mb-0 text-muted small">The Administrator role has permanent, unrevocable access to all 8 modules, administrative pages, export tools, and operational controls.</p>
                </div>
            </div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">100% Full Access Enforced</span>
        </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="rp-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="rp-search-wrap">
            <i class="fa-solid fa-magnifying-glass rp-search-icon"></i>
            <input type="text" id="permSearch" class="rp-search-input" placeholder="Quick search permissions (e.g. invoice, delete, dispatch, export)..." oninput="filterPermissions(this.value)">
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="rp-bulk-btn success" onclick="selectAllPermissions()" <?= $isOwner ? 'disabled' : '' ?>>
                <i class="fa-solid fa-check-double"></i>Grant All
            </button>
            <button type="button" class="rp-bulk-btn danger" onclick="deselectAllPermissions()" <?= $isOwner ? 'disabled' : '' ?>>
                <i class="fa-solid fa-xmark"></i>Revoke All
            </button>
        </div>
    </div>

    <!-- CATALOGUE ACCORDIONS & PERMISSION CARDS -->
    <div id="permissionsCatalogue">
        <?php foreach ($catalogue as $catKey => $category): ?>
            <div class="rp-cat-section" data-cat-key="<?= e($catKey) ?>">
                <!-- Category Heading -->
                <div class="rp-cat-heading">
                    <div class="rp-cat-label">
                        <div class="cat-icon-wrap">
                            <i class="fa-solid <?= e($category['category_icon']) ?>"></i>
                        </div>
                        <span><?= e($category['category_name']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-check-label small fw-bold text-muted cursor-pointer" for="chk_cat_<?= e($catKey) ?>">Select Category</label>
                        <input type="checkbox" id="chk_cat_<?= e($catKey) ?>" class="form-check-input rp-cat-chk cursor-pointer" 
                               onchange="toggleCategory('<?= e($catKey) ?>', this.checked)" <?= $isOwner ? 'checked disabled' : '' ?>>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="rp-cards-grid">
                    <?php foreach ($category['pages'] as $page): 
                        $pagePermKey = (string)($page['permission'] ?? '');
                        $pagePermId = (int)($page['permission_id'] ?? 0);
                        $pagePermVal = $pagePermId > 0 ? $pagePermId : $pagePermKey;
                        $isPageGranted = ($pagePermId > 0 && in_array($pagePermId, $selectedPermissionIds, true))
                            || in_array($pagePermKey, $selectedPermissionNames, true)
                            || $isOwner;
                    ?>
                        <div class="rp-page-card <?= $isPageGranted ? 'access-granted' : '' ?> <?= $isOwner ? 'owner-locked' : '' ?>" 
                             data-page-id="<?= e($page['id']) ?>" 
                             data-perm-name="<?= e($page['title'] . ' ' . $page['route'] . ' ' . $page['description']) ?>">
                            
                            <!-- Card Head -->
                            <div class="rp-card-head">
                                <div class="rp-card-title-row">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="<?= e($pagePermVal) ?>" 
                                           class="rp-main-chk rp-perm-chk" 
                                           id="perm_<?= e($pagePermKey) ?>" 
                                           data-page-chk="<?= e($page['id']) ?>"
                                           onchange="onPageCheckChange('<?= e($page['id']) ?>', this.checked)"
                                           <?= $isPageGranted ? 'checked' : '' ?> 
                                           <?= $isOwner ? 'disabled' : '' ?>>
                                    <div class="flex-grow-1 min-w-0">
                                        <label for="perm_<?= e($pagePermKey) ?>" class="rp-card-page-name m-0 d-block">
                                            <?= e($page['title']) ?>
                                        </label>
                                        <span class="rp-card-route"><?= e($page['route']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="rp-card-body-inner">
                                <p class="text-muted small m-0" style="line-height: 1.35;"><?= e($page['description']) ?></p>

                                <?php if (!empty($page['header_buttons']) || !empty($page['action_buttons'])): ?>
                                    <div class="mt-2">
                                        <div class="rp-sub-title">
                                            <i class="fa-solid fa-shield-halved"></i>Buttons &amp; Actions
                                        </div>
                                        <div class="rp-btn-list">
                                            <!-- Header Buttons -->
                                            <?php foreach ($page['header_buttons'] as $hBtn): 
                                                $btnPermKey = (string)($hBtn['name'] ?? '');
                                                $btnPermId = (int)($hBtn['permission_id'] ?? 0);
                                                $btnPermVal = $btnPermId > 0 ? $btnPermId : $btnPermKey;
                                                $isBtnGranted = ($btnPermId > 0 && in_array($btnPermId, $selectedPermissionIds, true))
                                                    || in_array($btnPermKey, $selectedPermissionNames, true)
                                                    || $isOwner;
                                            ?>
                                                <label class="rp-btn-row <?= $isBtnGranted ? 'is-checked' : '' ?>" for="btn_perm_<?= e($btnPermKey) ?>">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="<?= e($btnPermVal) ?>" 
                                                           id="btn_perm_<?= e($btnPermKey) ?>"
                                                           class="rp-btn-chk rp-perm-chk" 
                                                           data-parent-page="<?= e($page['id']) ?>"
                                                           onchange="onActionCheckChange(this)"
                                                           <?= $isBtnGranted ? 'checked' : '' ?> 
                                                           <?= $isOwner ? 'disabled' : '' ?>>
                                                    <div class="rp-btn-icon-wrap">
                                                        <i class="fa-solid <?= e($hBtn['icon']) ?>"></i>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <span class="rp-btn-label"><?= e($hBtn['label']) ?></span>
                                                        <span class="rp-btn-desc"><?= e($hBtn['description']) ?></span>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>

                                            <!-- Row Action Buttons -->
                                            <?php foreach ($page['action_buttons'] as $aBtn): 
                                                $btnPermKey = (string)($aBtn['name'] ?? '');
                                                $btnPermId = (int)($aBtn['permission_id'] ?? 0);
                                                $btnPermVal = $btnPermId > 0 ? $btnPermId : $btnPermKey;
                                                $isBtnGranted = ($btnPermId > 0 && in_array($btnPermId, $selectedPermissionIds, true))
                                                    || in_array($btnPermKey, $selectedPermissionNames, true)
                                                    || $isOwner;
                                            ?>
                                                <label class="rp-btn-row <?= $isBtnGranted ? 'is-checked' : '' ?>" for="btn_perm_<?= e($btnPermKey) ?>">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="<?= e($btnPermVal) ?>" 
                                                           id="btn_perm_<?= e($btnPermKey) ?>"
                                                           class="rp-btn-chk rp-perm-chk" 
                                                           data-parent-page="<?= e($page['id']) ?>"
                                                           onchange="onActionCheckChange(this)"
                                                           <?= $isBtnGranted ? 'checked' : '' ?> 
                                                           <?= $isOwner ? 'disabled' : '' ?>>
                                                    <div class="rp-btn-icon-wrap">
                                                        <i class="fa-solid <?= e($aBtn['icon']) ?>"></i>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <span class="rp-btn-label"><?= e($aBtn['label']) ?></span>
                                                        <span class="rp-btn-desc"><?= e($aBtn['description']) ?></span>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- STICKY ACTION BAR -->
    <div class="rp-action-bar">
        <div class="rp-bar-info">
            <div class="rp-bar-icon"><i class="fa-solid fa-lock-open"></i></div>
            <div>
                <span id="selectedCountDisplay" class="fs-6 fw-bold text-dark"><?= count($selectedPermissionIds) ?></span>
                <span class="text-muted small">Permissions Selected</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('/office/role-permission') ?>" class="btn btn-outline-secondary fw-bold px-3 py-2 rounded-3">
                Cancel
            </a>
            <button type="submit" id="savePermissionsBtn" class="btn btn-primary fw-bold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" style="background: var(--vg-blue-600, #0284c7);" <?= $isOwner ? 'disabled' : '' ?>>
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Permissions</span>
            </button>
        </div>
    </div>
</form>

<script>
function updateCounter() {
    const checked = document.querySelectorAll('.rp-perm-chk:checked').length;
    const counterEl = document.getElementById('selectedCountDisplay');
    const entityCounterEl = document.getElementById('entityPermCount');
    if (counterEl) counterEl.textContent = checked;
    if (entityCounterEl) entityCounterEl.textContent = checked;
}

function onPageCheckChange(pageId, isChecked) {
    const card = document.querySelector(`.rp-page-card[data-page-id="${pageId}"]`);
    if (card) {
        if (isChecked) {
            card.classList.add('access-granted');
        } else {
            card.classList.remove('access-granted');
        }
        // Also check/uncheck child action buttons
        const actionCheckboxes = card.querySelectorAll('.rp-btn-chk');
        actionCheckboxes.forEach(chk => {
            chk.checked = isChecked;
            const row = chk.closest('.rp-btn-row');
            if (row) {
                if (isChecked) row.classList.add('is-checked');
                else row.classList.remove('is-checked');
            }
        });
    }
    updateCounter();
}

function onActionCheckChange(chk) {
    const row = chk.closest('.rp-btn-row');
    if (row) {
        if (chk.checked) row.classList.add('is-checked');
        else row.classList.remove('is-checked');
    }
    // If an action button is checked, ensure parent page checkbox is also checked
    if (chk.checked) {
        const pageId = chk.getAttribute('data-parent-page');
        const parentChk = document.querySelector(`.rp-main-chk[data-page-chk="${pageId}"]`);
        if (parentChk && !parentChk.checked) {
            parentChk.checked = true;
            const card = document.querySelector(`.rp-page-card[data-page-id="${pageId}"]`);
            if (card) card.classList.add('access-granted');
        }
    }
    updateCounter();
}

function toggleCategory(catKey, isChecked) {
    const section = document.querySelector(`.rp-cat-section[data-cat-key="${catKey}"]`);
    if (!section) return;
    const checkboxes = section.querySelectorAll('.rp-perm-chk');
    checkboxes.forEach(chk => {
        chk.checked = isChecked;
        const row = chk.closest('.rp-btn-row');
        if (row) {
            if (isChecked) row.classList.add('is-checked');
            else row.classList.remove('is-checked');
        }
    });
    const cards = section.querySelectorAll('.rp-page-card');
    cards.forEach(card => {
        if (isChecked) card.classList.add('access-granted');
        else card.classList.remove('access-granted');
    });
    updateCounter();
}

function selectAllPermissions() {
    document.querySelectorAll('.rp-perm-chk').forEach(chk => {
        chk.checked = true;
    });
    document.querySelectorAll('.rp-btn-row').forEach(row => row.classList.add('is-checked'));
    document.querySelectorAll('.rp-page-card').forEach(card => card.classList.add('access-granted'));
    document.querySelectorAll('.rp-cat-chk').forEach(chk => chk.checked = true);
    updateCounter();
}

function deselectAllPermissions() {
    document.querySelectorAll('.rp-perm-chk').forEach(chk => {
        chk.checked = false;
    });
    document.querySelectorAll('.rp-btn-row').forEach(row => row.classList.remove('is-checked'));
    document.querySelectorAll('.rp-page-card').forEach(card => card.classList.remove('access-granted'));
    document.querySelectorAll('.rp-cat-chk').forEach(chk => chk.checked = false);
    updateCounter();
}

function filterPermissions(query) {
    const q = (query || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.rp-page-card');
    cards.forEach(card => {
        const text = (card.getAttribute('data-perm-name') || '').toLowerCase();
        if (!q || text.includes(q)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function onRoleChange(roleId) {
    if (!roleId) return;
    window.location.href = `<?= url('/office/role-permission/assign?scope=role&role_id=') ?>${roleId}`;
}

function onUserChange(userId) {
    if (!userId) return;
    window.location.href = `<?= url('/office/role-permission/assign?scope=user&user_id=') ?>${userId}`;
}

// Handle Form Submission via AJAX
document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('savePermissionsBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.status === 'success' || data.success) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Permissions updated successfully!', 'success');
            }
            setTimeout(() => window.location.reload(), 1200);
        } else {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(data.message || 'Error saving permissions.', 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('An unexpected error occurred while saving permissions.', 'error');
        }
    });
});
</script>
