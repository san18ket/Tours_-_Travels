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
$customRoles  = (int)($metrics['custom_roles'] ?? 0);
$systemRoles  = max(0, $totalRoles - $customRoles);
$totalPerms   = (int)($metrics['total_permissions'] ?? 0);
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
.rp-action-btn.view:hover { background: #E8F7F0; border-color: rgba(23, 107, 91, 0.25); color: #176B5B; }
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
<div class="rp-stats" id="rolePermsKpiGrid">
    <div class="rp-stat <?= empty($type) ? 'is-selected' : '' ?>" data-type="" role="button" tabindex="0" title="Click to view all roles">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #0284c7, #0369a1);"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="rp-stat-info"><h3 data-count="<?= $totalRoles ?>"><?= number_format($totalRoles) ?></h3><p>Total Roles</p></div>
        <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
    </div>
    <div class="rp-stat <?= ($type ?? '') === 'system' ? 'is-selected' : '' ?>" data-type="system" role="button" tabindex="0" title="Click to filter system roles">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fa-solid fa-shield-check"></i></div>
        <div class="rp-stat-info"><h3 data-count="<?= $systemRoles ?>"><?= number_format($systemRoles) ?></h3><p>System Roles</p></div>
        <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
    </div>
    <div class="rp-stat <?= ($type ?? '') === 'custom' ? 'is-selected' : '' ?>" data-type="custom" role="button" tabindex="0" title="Click to filter custom roles">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fa-solid fa-sliders"></i></div>
        <div class="rp-stat-info"><h3 data-count="<?= $customRoles ?>"><?= number_format($customRoles) ?></h3><p>Custom Roles</p></div>
        <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
    </div>
    <div class="rp-stat" data-type="" role="button" tabindex="0" title="Total granted permission relationships">
        <div class="rp-stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);"><i class="fa-solid fa-key"></i></div>
        <div class="rp-stat-info"><h3 data-count="<?= $totalPerms ?>"><?= number_format($totalPerms) ?></h3><p>Permissions Assigned</p></div>
        <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
    </div>
</div>

<!-- FILTER -->
<div class="rp-filter-card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-end justify-content-between gap-3">
        <form method="GET" action="<?= url('/office/role-permission') ?>" class="row g-2 align-items-end flex-grow-1 mb-0" id="rolePermFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
            <div class="col-sm-6 col-lg-5">
                <label class="form-label small fw-bold text-muted mb-1">Search Roles</label>
                <div class="input-group position-relative">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted" id="rpSearchIcon"></i></span>
                    <input type="text" name="search" id="rpSearchInput" value="<?= e($search ?? '') ?>" class="form-control border-start-0" placeholder="Search by role name or description..." autocomplete="off">
                    <button type="button" class="fbc-search-clear" id="rpSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3 col-lg-3">
                <label class="form-label small fw-bold text-muted mb-1">Role Type</label>
                <select name="type" id="rpTypeSelect" class="form-select">
                    <option value="">All Types</option>
                    <option value="system" <?= ($type ?? '') === 'system' ? 'selected' : '' ?>>System Roles</option>
                    <option value="custom" <?= ($type ?? '') === 'custom' ? 'selected' : '' ?>>Custom Roles</option>
                </select>
            </div>
            <div class="col-sm-3 col-lg-2">
                <label class="form-label small fw-bold text-muted mb-1">Per Page</label>
                <select name="per_page" id="rpPerPageSelect" class="form-select">
                    <?php foreach ([10, 25, 50] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($limit ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-auto d-flex gap-2">
                <button type="button" class="btn btn-primary fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-1.5" id="rpFilterApplyBtn">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
                <button type="button" class="btn btn-light border text-muted fw-semibold py-2 px-3 rounded-3" id="rpResetBtn" title="Reset Filters" data-no-ajax="true">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
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
<div class="rp-table-card overflow-hidden" id="rolePermsSection">
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
    <div class="p-3 border-top bg-light d-flex flex-wrap align-items-center justify-content-between gap-3" id="rolePermsPaginationWrapper">
        <div class="d-flex flex-wrap gap-3 small text-muted">
            <span><i class="fa-solid fa-key text-success me-1"></i>Configure Permissions</span>
            <span><i class="fa-solid fa-eye text-primary me-1"></i>Inspect Matrix</span>
            <span><i class="fa-solid fa-trash-can text-danger me-1"></i>Delete Custom Role</span>
            <span><i class="fa-solid fa-shield text-secondary me-1"></i>Protected Core Role</span>
        </div>
        <div class="pagination-sub-wrap">
        <?php if ($total > $limit): ?>
            <div class="d-flex gap-1 pagination">
                <?php
                $totalPages = (int)ceil($total / $limit);
                for ($p = 1; $p <= $totalPages; $p++):
                ?>
                    <a href="<?= url('/office/role-permission?page=' . $p . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . urlencode($type) : '') . ($limit != 10 ? '&per_page=' . $limit : '')) ?>" 
                       data-page="<?= $p ?>"
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline-secondary' ?>" style="min-width: 32px;">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Role Permissions Live AJAX Filter Engine (Matches Customers Standard)     */
    /* ========================================================================= */
    (function initRolePermissionAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/role-permission') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('rpSearchInput');
            const typeSelect = document.getElementById('rpTypeSelect');
            const perPageSelect = document.getElementById('rpPerPageSelect');

            const sVal = searchInput ? searchInput.value.trim() : '';
            const tVal = typeSelect ? typeSelect.value : '';
            const ppVal = perPageSelect ? perPageSelect.value : '10';

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (tVal) p.set('type', tVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, typeVal: tVal, perPageVal: ppVal, pageNum: page };
        }

        function filterRolePermsAjax(page = 1, options = {}) {
            const { params, searchVal, typeVal, perPageVal, pageNum } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('rolePermsSection');
            const searchIcon = document.getElementById('rpSearchIcon');
            const searchClearBtn = document.getElementById('rpSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin text-muted';
            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';

            fetch(targetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml'
                },
                signal: abortCtrl.signal
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newSection = doc.getElementById('rolePermsSection');
                const currSection = document.getElementById('rolePermsSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newKpi = doc.getElementById('rolePermsKpiGrid');
                const currKpi = document.getElementById('rolePermsKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const sInput = document.getElementById('rpSearchInput');
                if (sInput && sInput.value !== searchVal) sInput.value = searchVal;

                const tSelect = document.getElementById('rpTypeSelect');
                if (tSelect && tSelect.value !== typeVal) tSelect.value = typeVal;

                const ppSelect = document.getElementById('rpPerPageSelect');
                if (ppSelect && ppSelect.value !== perPageVal) ppSelect.value = perPageVal;

                if (!options.skipPushState) {
                    if (options.isTyping) {
                        window.history.replaceState({ searchVal, typeVal, perPageVal, pageNum }, '', targetUrl);
                    } else {
                        window.history.pushState({ searchVal, typeVal, perPageVal, pageNum }, '', targetUrl);
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Role Permission Filter AJAX Error:', err);
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass text-muted';
            });
        }

        function animateKpiNumbers(container) {
            const vals = container.querySelectorAll('h3[data-count]');
            vals.forEach(el => {
                const target = parseInt(el.getAttribute('data-count'), 10) || 0;
                let current = 0;
                const duration = 400;
                const stepTime = 20;
                const steps = duration / stepTime;
                const increment = Math.ceil(target / steps) || 1;

                if (target === 0) {
                    el.textContent = '0';
                    return;
                }

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    el.textContent = current.toLocaleString();
                }, stepTime);
            });
        }

        // Search inputs debouncing (280ms)
        const sInput = document.getElementById('rpSearchInput');
        if (sInput) {
            sInput.addEventListener('input', function(e) {
                const val = e.target.value;
                const clearBtn = document.getElementById('rpSearchClearBtn');
                if (clearBtn) clearBtn.style.display = val.trim() ? 'inline-flex' : 'none';

                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    filterRolePermsAjax(1, { isTyping: true });
                }, 280);
            });
        }

        const tSelect = document.getElementById('rpTypeSelect');
        if (tSelect) {
            tSelect.addEventListener('change', () => filterRolePermsAjax(1));
        }

        const ppSelect = document.getElementById('rpPerPageSelect');
        if (ppSelect) {
            ppSelect.addEventListener('change', () => filterRolePermsAjax(1));
        }

        const applyBtn = document.getElementById('rpFilterApplyBtn');
        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterRolePermsAjax(1);
            });
        }

        // Document-level event delegation
        document.addEventListener('click', function(e) {
            // 1. KPI Card click
            const statCard = e.target.closest('#rolePermsKpiGrid .rp-stat');
            if (statCard) {
                e.preventDefault();
                const t = statCard.getAttribute('data-type');
                const wasSelected = statCard.classList.contains('is-selected');

                const tSelect = document.getElementById('rpTypeSelect');
                if (tSelect) {
                    if (wasSelected && t) {
                        tSelect.value = '';
                    } else {
                        tSelect.value = (t !== null) ? t : '';
                    }
                }
                filterRolePermsAjax(1);
                return;
            }

            // 2. Pagination link click
            const pageLink = e.target.closest('#rolePermsPaginationWrapper a[data-page]');
            if (pageLink) {
                e.preventDefault();
                const pageNum = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
                filterRolePermsAjax(pageNum);
                return;
            }

            // 3. Search Clear button
            if (e.target.closest('#rpSearchClearBtn')) {
                e.preventDefault();
                const s = document.getElementById('rpSearchInput');
                if (s) s.value = '';
                const btn = document.getElementById('rpSearchClearBtn');
                if (btn) btn.style.display = 'none';
                filterRolePermsAjax(1);
                return;
            }

            // 4. Reset Filters button
            if (e.target.closest('#rpResetBtn')) {
                e.preventDefault();
                const s = document.getElementById('rpSearchInput');
                const t = document.getElementById('rpTypeSelect');
                const pp = document.getElementById('rpPerPageSelect');
                if (s) s.value = '';
                if (t) t.value = '';
                if (pp) pp.value = '10';
                filterRolePermsAjax(1);
                return;
            }
        });

        // Browser history popstate
        window.addEventListener('popstate', function(e) {
            if (e.state) {
                const { searchVal, typeVal, perPageVal, pageNum } = e.state;
                const s = document.getElementById('rpSearchInput');
                const t = document.getElementById('rpTypeSelect');
                const pp = document.getElementById('rpPerPageSelect');
                if (s) s.value = searchVal || '';
                if (t) t.value = typeVal || '';
                if (pp) pp.value = perPageVal || '10';
                filterRolePermsAjax(pageNum || 1, { skipPushState: true });
            } else {
                location.reload();
            }
        });
    })();
</script>

<style>
/* Stat Cards Interactive & Selected State */
#rolePermsKpiGrid .rp-stat {
    cursor: pointer;
    position: relative;
    user-select: none;
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease, border-color 0.18s ease;
    border: 1.5px solid transparent;
}
#rolePermsKpiGrid .rp-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(2, 132, 199, 0.12);
    border-color: rgba(2, 132, 199, 0.35);
}
#rolePermsKpiGrid .rp-stat.is-selected {
    border-color: #0284c7 !important;
    background: linear-gradient(180deg, #F0F9FF 0%, #FFFFFF 100%) !important;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15), 0 8px 20px rgba(2, 132, 199, 0.08) !important;
}
#rolePermsKpiGrid .rp-stat.is-selected .kpi-filter-tag {
    opacity: 1;
    transform: translateX(0);
    color: #0284c7;
}
#rolePermsKpiGrid .kpi-filter-tag {
    font-size: 11px;
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.18s ease, transform 0.18s ease;
    margin-left: auto;
}
#rolePermsKpiGrid .rp-stat:hover .kpi-filter-tag {
    opacity: 0.7;
    transform: translateX(0);
}

#rolePermsSection.is-filtering {
    opacity: 0.55;
    pointer-events: none;
    transition: opacity 0.15s ease;
}

.fbc-search-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    z-index: 5;
    transition: color 0.15s ease;
}
.fbc-search-clear:hover {
    color: #1e293b;
}
</style>
