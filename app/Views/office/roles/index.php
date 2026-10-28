<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Role List & Operational Access',
            'description' => 'Manage security roles, operational authority levels, and assigned user counts across the fleet.',
            'image'       => 'header-roles.jpg',
            'badge'       => 'RBAC Matrix Active',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Roles & Permissions', 'url' => ''],
            ],
            'actions'     => '
                <a href="' . url('/office/role-permission') . '" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.45rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Permissions Matrix</span>
                </a>
                <button type="button" class="btn btn-primary" onclick="openAddRoleModal()" style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 700;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Role</span>
                </button>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid" id="rolesKpiGrid">
        <!-- Metric 1: Total Roles -->
        <div class="booking-kpi-card <?= empty($status) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all role tiers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Roles</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($metrics['total_roles'] ?? count($roles)) ?>"><?= number_format($metrics['total_roles'] ?? count($roles)) ?></div>
            <div class="booking-kpi-sub">
                <span>Registered role tiers</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Metric 2: Active Roles -->
        <div class="booking-kpi-card <?= ($status ?? '') === 'active' ? 'is-selected' : '' ?>" data-status="active" role="button" tabindex="0" title="Click to filter active roles">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Roles</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($metrics['active_roles'] ?? 0) ?>"><?= number_format($metrics['active_roles'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Enabled for assignment</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Metric 3: Total Assigned Users -->
        <div class="booking-kpi-card" data-status="" role="button" tabindex="0" title="All active user-to-role mappings">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Assigned Users</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($metrics['total_users_assigned'] ?? 0) ?>"><?= number_format($metrics['total_users_assigned'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Active account mappings</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Metric 4: Custom Roles -->
        <div class="booking-kpi-card <?= ($status ?? '') === 'inactive' ? 'is-selected' : '' ?>" data-status="inactive" role="button" tabindex="0" title="Click to filter inactive roles">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Custom Roles</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($metrics['custom_roles'] ?? 0) ?>"><?= number_format($metrics['custom_roles'] ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Tenant custom defined</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/roles') ?>" class="filter-bar-compact" id="roleFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by role title, slug, description..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="">All Statuses</option>
            <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active Only</option>
            <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 25, 50] as $opt): ?>
                <option value="<?= $opt ?>" <?= ((int)($perPage ?? 10) === $opt) ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($status)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span>Clear</span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Roles & Access</h2>
            </div>
            <button type="button" class="mv-add-btn" onclick="openAddRoleModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Add Role</span>
            </button>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/roles') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search roles..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 25, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $currStatus = strtolower($status ?? '');
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/roles' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="rolesMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($metrics['total_roles'] ?? count($roles)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>" data-status="active">
                <span>Active</span>
                <span class="mv-pill-cnt"><?= (int)($metrics['active_roles'] ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' ? 'active' : '' ?>" data-status="inactive">
                <span>Inactive</span>
            </a>
        </div>
    </div>

    <!-- 5. All Roles Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="rolesSection">
        <div class="section-header">
            <h3 class="section-title">All Roles &amp; Authority Levels</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalRecords ?? count($roles)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="rolesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 25%;">Role Title &amp; System Key</th>
                        <th style="width: 28%;">Description &amp; Purpose</th>
                        <th class="text-center" style="width: 12%;">Assigned Users</th>
                        <th class="text-center" style="width: 12%;">Permissions</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    <?php foreach ($roles as $i => $role):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $isActive = (int)($role['status'] ?? 1) === 1;
                        $isSystem = (int)($role['is_system'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                                <?= e($role['name']) ?>
                                <?php if ($isSystem): ?>
                                    <span style="display:inline-block;background:rgba(22,68,59,0.12);color:#16443B;border-radius:4px;padding:1px 6px;font-size:10.5px;font-weight:700;margin-left:4px;">System</span>
                                <?php endif; ?>
                            </div>
                            <small style="font-family:monospace;color:var(--brand-primary, #176B5B);font-weight:600;font-size:11.5px;margin-top:2px;">
                                <?= e($role['slug']) ?>
                            </small>
                        </td>
                        <td>
                            <div style="font-size: 13px; color: #475569; max-width: 320px; line-height: 1.4;">
                                <?= !empty($role['description']) ? e($role['description']) : '<span style="color:#94a3b8;font-style:italic;">No description provided</span>' ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="project-name" style="font-size: 12px;">
                                <i class="fa-solid fa-users" style="font-size:10px;"></i> <?= (int)($role['user_count'] ?? 0) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= url('/office/role-permission/assign?role_id=' . $role['id']) ?>" style="font-size:12px;font-weight:700;color:var(--brand-primary, #176B5B);text-decoration:none;">
                                <?= (int)($role['permissions_count'] ?? 0) ?> granted &rarr;
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="status-cell">
                                <span class="status-dot <?= $isActive ? 'completed' : 'cancelled' ?>"></span>
                                <span class="status-badge bg-<?= $isActive ? 'completed' : 'cancelled' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/office/role-permission/assign?role_id=' . $role['id']) ?>" class="action-btn-table dispatch" title="Assign Permissions">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </a>
                                    <button type="button" class="action-btn-table edit" onclick="openEditRoleModal(<?= htmlspecialchars(json_encode($role), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Role">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <?php if (!$isSystem): ?>
                                    <form action="<?= url('/office/roles/' . $role['id'] . '/delete') ?>" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this custom role?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="action-btn-table delete" title="Delete Role">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No roles found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="rolesCards">
            <?php foreach ($roles as $role):
                $isActive = (int)($role['status'] ?? 1) === 1;
                $isSystem = (int)($role['is_system'] ?? 0) === 1;
                $initials = strtoupper(mb_substr($role['name'] ?? 'R', 0, 1));
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card">
                    <!-- Left: Role Icon Avatar -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <i class="fa-solid <?= $isSystem ? 'fa-shield-halved' : 'fa-user-tag' ?>"></i>
                        </div>
                    </div>

                    <!-- Middle: Role Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($role['slug']) ?></div>
                        <div class="mv-tour-title" title="<?= e($role['name']) ?>">
                            <?= e($role['name']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-users"></i>
                            <span><?= (int)($role['user_count'] ?? 0) ?> Assigned Users</span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-key"></i>
                            <span><?= (int)($role['permissions_count'] ?? 0) ?> System Permissions</span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-circle-info"></i>
                            <span><?= !empty($role['description']) ? e($role['description']) : 'Standard RBAC Role' ?></span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Type text bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $isActive ? 'active' : 'inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                        <div class="mv-price" style="font-size: 11px; color: #16443B; font-weight: 700;">
                            <?= $isSystem ? 'SYSTEM' : 'CUSTOM' ?>
                        </div>
                    </div>
                </div>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/role-permission/assign?role_id=' . $role['id']) ?>" class="action-btn-table dispatch" title="Assign Permissions">
                            <i class="fa-solid fa-shield-halved"></i>
                        </a>
                        <button type="button" class="action-btn-table edit" onclick="openEditRoleModal(<?= htmlspecialchars(json_encode($role), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Role">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <?php if (!$isSystem): ?>
                        <form action="<?= url('/office/roles/' . $role['id'] . '/delete') ?>" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this custom role?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="action-btn-table delete" title="Delete Role">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($roles)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No roles found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalRecords ?? count($roles);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $status, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($status)) $q['status'] = $status;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/roles?' . http_build_query($q));
        };
    ?>
    <div id="rolesPaginationWrapper">
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> role(s)</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" data-page="<?= max(1, $currP - 1) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page">&#10094;</a>
            <?php
                $rangeStart = max(1, $currP - 1);
                $rangeEnd   = min($totalPages, $rangeStart + 2);
                if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                    $rangeStart = max(1, $rangeEnd - 2);
                }
                for ($i = $rangeStart; $i <= $rangeEnd; $i++):
            ?>
                <a href="<?= $buildPageUrl($i) ?>" data-page="<?= $i ?>" class="<?= $i === $currP ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($rangeEnd < $totalPages): ?>
                <span class="dots">...</span>
                <a href="<?= $buildPageUrl($totalPages) ?>" data-page="<?= $totalPages ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" data-page="<?= min($totalPages, $currP + 1) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Box: Add Role -->
<!-- ========================================================================= -->
<div id="addRoleModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(7, 15, 30, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;" onclick="handleModalBackdropClick(event)">
    <div style="background: #FFFFFF; width: 100%; max-width: 520px; border-radius: 16px; box-shadow: 0 20px 45px rgba(7, 15, 30, 0.25); border: 1px solid rgba(23,107,91,0.15); overflow: hidden; animation: modalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #176B5B 0%, #16443B 100%); color: #FFF;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 255, 255, 0.15); color: #FFF; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-plus"></i>
                </span>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #FFF;"><?= e(__('role_form.add_title', 'Create New Operational Role')) ?></h3>
                    <div style="font-size: 0.74rem; color: #B2D8CF;"><?= e(__('role_form.add_subtitle', 'Define custom authority level and role permissions')) ?></div>
                </div>
            </div>
            <button type="button" onclick="closeAddRoleModal()" style="background: none; border: none; color: #B2D8CF; font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 0.25rem;" title="<?= e(__('form.cancel', 'Cancel')) ?>">&times;</button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= url('/office/roles') ?>" method="POST" id="addRoleForm" onsubmit="return validateRoleForm()">
            <?= csrf_field() ?>

            <div style="padding: 1.5rem;">
                <!-- Role Name Input -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="roleNameInput" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.45rem; display: block; color: #1e293b;">
                        <?= e(__('role_form.role_name', 'Role Title / Label')) ?> <span class="required-indicator" style="color:#ef4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" id="roleNameInput" name="name" class="form-control" required placeholder="<?= e(__('role_form.role_name_placeholder', 'e.g. Booking Desk Supervisor')) ?>" oninput="updateSlugPreview(this.value)" autocomplete="off" style="height: 46px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 0.95rem; font-weight: 600; width: 100%; padding-left: 14px; box-sizing: border-box;">
                    </div>

                    <!-- Live Slug Preview Badge -->
                    <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem; font-size: 0.76rem; color: #64748b;">
                        <span><?= e(__('role_form.system_key', 'System Key:')) ?></span>
                        <code id="slugPreview" style="background: rgba(23, 107, 91, 0.08); color: #176B5B; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; font-family: monospace;">custom_role</code>
                    </div>
                </div>

                <!-- Description Input -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="roleDescInput" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem; display: block; color: #1e293b;">
                        <?= e(__('role_form.description', 'Role Scope & Responsibilities')) ?>
                    </label>
                    <textarea id="roleDescInput" name="description" class="form-control" rows="3" placeholder="<?= e(__('role_form.description_placeholder', 'Detail operational responsibilities...')) ?>" style="font-size: 0.88rem; width: 100%; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 10px; box-sizing: border-box;"></textarea>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding: 1.15rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline" onclick="closeAddRoleModal()" style="height: 40px; padding: 0 1.25rem; border-radius: 10px;">
                    <?= e(__('form.cancel', 'Cancel')) ?>
                </button>
                <button type="submit" id="saveRoleBtn" class="btn btn-primary" style="height: 40px; padding: 0 1.5rem; font-weight: 700; border-radius: 10px;">
                    <i class="fa-solid fa-check" style="margin-right: 4px;"></i>
                    <span><?= e(__('role_form.save_btn', 'Save Role')) ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT ROLE MODAL -->
<!-- ========================================================================= -->
<div id="editRoleModal" style="display: none; position: fixed; inset: 0; z-index: 1050; background: rgba(7, 15, 30, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="handleEditModalBackdropClick(event)">
    <div class="modal-dialog-card" style="width: 100%; max-width: 520px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 60px rgba(7, 15, 30, 0.35); border: 1px solid rgba(23,107,91,0.15); overflow: hidden; animation: modalPop 0.22s ease-out;" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(23,107,91,0.12); color: #176B5B; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #16443B;"><?= e(__('role_form.edit_title', 'Edit Role Configuration')) ?></h3>
                    <p style="margin: 0; font-size: 0.78rem; color: #64748b;"><?= e(__('role_form.edit_subtitle', 'Update role details and operational description')) ?></p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" style="border: none; background: transparent; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; font-size: 1.25rem;">
                &times;
            </button>
        </div>

        <!-- Modal Form -->
        <form id="editRoleForm" method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" id="editRoleId" name="id" value="">

            <div style="padding: 1.5rem;">
                <!-- Role Name Input -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="editRoleNameInput" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.45rem; display: block; color: #1e293b;">
                        <?= e(__('role_form.role_name', 'Role Title / Label')) ?> <span class="required-indicator" style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="editRoleNameInput" name="name" class="form-control" required style="height: 46px; font-size: 0.95rem; font-weight: 600; width: 100%; border-radius: 10px; border: 1.5px solid #cbd5e1; padding-left: 12px; box-sizing: border-box;">
                    <div id="editSystemRoleNotice" style="display: none; margin-top: 0.4rem; font-size: 0.75rem; color: #b45309;">
                        <i class="fa-solid fa-shield-halved me-1"></i><?= e(__('role_form.system_locked', 'System roles cannot be renamed')) ?>
                    </div>
                </div>

                <!-- Slug Display -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem; display: block; color: #1e293b;">
                        <?= e(__('role_form.system_key', 'System Key')) ?>
                    </label>
                    <input type="text" id="editRoleSlugDisplay" class="form-control" disabled style="height: 44px; font-family: monospace; background: #f8fafc; color: #475569; font-weight: 700; width: 100%; border-radius: 10px; border: 1.5px solid #e2e8f0; padding-left: 12px; box-sizing: border-box;">
                </div>

                <!-- Description Input -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="editRoleDescInput" style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.45rem; display: block; color: #1e293b;">
                        <?= e(__('role_form.description', 'Description')) ?>
                    </label>
                    <textarea id="editRoleDescInput" name="description" class="form-control" rows="3" style="font-size: 0.88rem; width: 100%; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 10px; box-sizing: border-box;"></textarea>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding: 1.15rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline" onclick="closeEditRoleModal()" style="height: 40px; padding: 0 1.25rem; border-radius: 10px;">
                    <?= e(__('form.cancel', 'Cancel')) ?>
                </button>
                <button type="submit" id="updateRoleBtn" class="btn btn-primary" style="height: 40px; padding: 0 1.5rem; font-weight: 700; border-radius: 10px;">
                    <i class="fa-solid fa-check" style="margin-right: 4px;"></i>
                    <span><?= e(__('role_form.save_btn', 'Save Changes')) ?></span>
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
</style>

<script>
    function toggleActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }

    function toggleCardActions(btn, e) {
        e.stopPropagation();
        const popup = btn.nextElementSibling;
        const isOpen = popup.classList.contains('open');
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        if (!isOpen) popup.classList.add('open');
    }

    function toggleMobileFilter() {
        const p = document.getElementById('mvFilterPanel');
        const b = document.getElementById('mvFilterToggleBtn');
        if (p) {
            const isHidden = p.style.display === 'none' || p.style.display === '';
            p.style.display = isHidden ? 'block' : 'none';
            if (b) b.classList.toggle('active', isHidden);
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

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
            if (nameInput) {
                if (window.VoyageValidation) window.VoyageValidation.markError(nameInput);
                else { nameInput.classList.add('is-invalid', 'vg-input-error'); nameInput.style.borderColor = '#EF4444'; }
                nameInput.focus();
            }
            return false;
        }
        if (nameInput) {
            if (window.VoyageValidation) window.VoyageValidation.clearError(nameInput);
            else { nameInput.classList.remove('is-invalid', 'vg-input-error'); nameInput.style.borderColor = ''; }
        }
        const saveBtn = document.getElementById('saveRoleBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = `<span>Saving...</span>`;
        }
        return true;
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddRoleModal();
            closeEditRoleModal();
        }
    });

    /* ========================================================================= */
    /* Roles Directory Live AJAX Filter Engine (Matches Customers Architecture)   */
    /* ========================================================================= */
    (function initRoleAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/roles') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterRolesAjax(page = 1, options = {}) {
            const { params, searchVal, statusVal, perPageVal, pageNum } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('rolesSection');
            const searchIcon = document.getElementById('fbcSearchIcon');
            const mvSearchIcon = document.getElementById('mvSearchIcon');
            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');

            if (section) section.classList.add('is-filtering');
            if (searchIcon) searchIcon.className = 'fa-solid fa-spinner fa-spin fbc-search-icon';
            if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-spinner fa-spin mv-search-ico';

            if (searchClearBtn) searchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = searchVal ? 'inline-flex' : 'none';

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

                const newSection = doc.getElementById('rolesSection');
                const currSection = document.getElementById('rolesSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('rolesPaginationWrapper');
                const currPagination = document.getElementById('rolesPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('rolesKpiGrid');
                const currKpi = document.getElementById('rolesKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('rolesMvStatusScroll');
                const currMvPills = document.getElementById('rolesMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                const searchInput = document.getElementById('searchInput');
                const mvSearchInput = document.getElementById('mvSearchInput');
                if (searchInput && searchInput.value !== searchVal) searchInput.value = searchVal;
                if (mvSearchInput && mvSearchInput.value !== searchVal) mvSearchInput.value = searchVal;

                const statusInput = document.getElementById('statusInput');
                if (statusInput && statusInput.value !== statusVal) statusInput.value = statusVal;

                const perPageInput = document.getElementById('perPageInput');
                const mvPerPageSelect = document.getElementById('mvPerPageSelect');
                if (perPageInput && perPageInput.value !== perPageVal) perPageInput.value = perPageVal;
                if (mvPerPageSelect && mvPerPageSelect.value !== perPageVal) mvPerPageSelect.value = perPageVal;

                if (!options.skipPushState) {
                    if (options.isTyping) {
                        window.history.replaceState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    } else {
                        window.history.pushState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Role Filter AJAX Error:', err);
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        function animateKpiNumbers(container) {
            const vals = container.querySelectorAll('.booking-kpi-val[data-count]');
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
        function handleSearchInput(e) {
            const val = e.target.value;
            const other = (e.target.id === 'searchInput') ? document.getElementById('mvSearchInput') : document.getElementById('searchInput');
            if (other && other.value !== val) other.value = val;

            const searchClearBtn = document.getElementById('searchClearBtn');
            const mvSearchClearBtn = document.getElementById('mvSearchClearBtn');
            if (searchClearBtn) searchClearBtn.style.display = val.trim() ? 'inline-flex' : 'none';
            if (mvSearchClearBtn) mvSearchClearBtn.style.display = val.trim() ? 'inline-flex' : 'none';

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterRolesAjax(1, { isTyping: true });
            }, 280);
        }

        const sInput = document.getElementById('searchInput');
        const mvInput = document.getElementById('mvSearchInput');
        if (sInput) sInput.addEventListener('input', handleSearchInput);
        if (mvInput) mvInput.addEventListener('input', handleSearchInput);

        // Dropdown inputs live change
        const stInput = document.getElementById('statusInput');
        if (stInput) {
            stInput.addEventListener('change', () => filterRolesAjax(1));
        }

        const ppInput = document.getElementById('perPageInput');
        if (ppInput) {
            ppInput.addEventListener('change', () => {
                const mvP = document.getElementById('mvPerPageSelect');
                if (mvP) mvP.value = ppInput.value;
                filterRolesAjax(1);
            });
        }

        const mvPpInput = document.getElementById('mvPerPageSelect');
        if (mvPpInput) {
            mvPpInput.addEventListener('change', () => {
                const pp = document.getElementById('perPageInput');
                if (pp) pp.value = mvPpInput.value;
                filterRolesAjax(1);
            });
        }

        const filterApplyBtn = document.getElementById('filterApplyBtn');
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterRolesAjax(1);
            });
        }

        // Document-level event delegation (unaffected by innerHTML replacements)
        document.addEventListener('click', function(e) {
            // 1. KPI Grid Card Click
            const kpiCard = e.target.closest('#rolesKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                const st = kpiCard.getAttribute('data-status');
                const wasSelected = kpiCard.classList.contains('is-selected');

                const statusInput = document.getElementById('statusInput');
                if (statusInput) {
                    if (wasSelected) {
                        statusInput.value = '';
                    } else {
                        statusInput.value = (st !== null) ? st : '';
                    }
                }
                filterRolesAjax(1);
                return;
            }

            // 2. Mobile status pills click
            const pill = e.target.closest('#rolesMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                const st = pill.getAttribute('data-status') || '';
                const statusInput = document.getElementById('statusInput');
                if (statusInput) statusInput.value = st;
                filterRolesAjax(1);
                return;
            }

            // 3. Pagination link click
            const pageLink = e.target.closest('#rolesPaginationWrapper .pagination a');
            if (pageLink && !pageLink.classList.contains('disabled')) {
                e.preventDefault();
                const pageNum = parseInt(pageLink.getAttribute('data-page'), 10) || 1;
                filterRolesAjax(pageNum);
                return;
            }

            // 4. Search Clear button click
            if (e.target.closest('#searchClearBtn') || e.target.closest('#mvSearchClearBtn')) {
                e.preventDefault();
                const s1 = document.getElementById('searchInput');
                const s2 = document.getElementById('mvSearchInput');
                if (s1) s1.value = '';
                if (s2) s2.value = '';
                const btn1 = document.getElementById('searchClearBtn');
                const btn2 = document.getElementById('mvSearchClearBtn');
                if (btn1) btn1.style.display = 'none';
                if (btn2) btn2.style.display = 'none';
                filterRolesAjax(1);
                return;
            }

            // 5. Filter bar Clear button click
            if (e.target.closest('#filterClearBtn')) {
                e.preventDefault();
                const s1 = document.getElementById('searchInput');
                const s2 = document.getElementById('mvSearchInput');
                const st = document.getElementById('statusInput');
                const pp = document.getElementById('perPageInput');
                const mvp = document.getElementById('mvPerPageSelect');
                if (s1) s1.value = '';
                if (s2) s2.value = '';
                if (st) st.value = '';
                if (pp) pp.value = '10';
                if (mvp) mvp.value = '10';
                filterRolesAjax(1);
                return;
            }

            // 6. Refresh button click
            if (e.target.closest('#refreshBtn')) {
                e.preventDefault();
                filterRolesAjax(1);
                return;
            }
        });

        // Browser history support (Back/Forward navigation)
        window.addEventListener('popstate', function(e) {
            if (e.state) {
                const { searchVal, statusVal, perPageVal, pageNum } = e.state;
                const s1 = document.getElementById('searchInput');
                const s2 = document.getElementById('mvSearchInput');
                const st = document.getElementById('statusInput');
                const pp = document.getElementById('perPageInput');
                const mvp = document.getElementById('mvPerPageSelect');
                if (s1) s1.value = searchVal || '';
                if (s2) s2.value = searchVal || '';
                if (st) st.value = statusVal || '';
                if (pp) pp.value = perPageVal || '10';
                if (mvp) mvp.value = perPageVal || '10';
                filterRolesAjax(pageNum || 1, { skipPushState: true });
            } else {
                location.reload();
            }
        });
    })();
</script>

<style>
/* Executive interactive cards & AJAX filter transitions */
#rolesKpiGrid .booking-kpi-card {
    cursor: pointer;
    position: relative;
    user-select: none;
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease, border-color 0.18s ease;
    border: 1.5px solid transparent;
}
#rolesKpiGrid .booking-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(22, 68, 59, 0.09);
    border-color: rgba(23, 107, 91, 0.28);
}
#rolesKpiGrid .booking-kpi-card.is-selected {
    border-color: #176B5B !important;
    background: linear-gradient(180deg, #F0FDF8 0%, #FFFFFF 100%) !important;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.14), 0 8px 20px rgba(23, 107, 91, 0.08) !important;
}
#rolesKpiGrid .booking-kpi-card.is-selected .booking-kpi-label {
    color: #176B5B !important;
    font-weight: 700;
}
#rolesKpiGrid .booking-kpi-card.is-selected .kpi-filter-tag {
    opacity: 1;
    transform: translateX(0);
    color: #176B5B;
}
.kpi-filter-tag {
    font-size: 11px;
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.18s ease, transform 0.18s ease;
}
#rolesKpiGrid .booking-kpi-card:hover .kpi-filter-tag {
    opacity: 0.7;
    transform: translateX(0);
}

/* AJAX loading state on main listing container */
#rolesSection.is-filtering {
    opacity: 0.55;
    pointer-events: none;
    transition: opacity 0.15s ease;
}

/* Inline search clear button */
.fbc-search {
    position: relative;
}
.fbc-search-clear,
.mv-search-clear {
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
    transition: color 0.15s ease;
}
.fbc-search-clear:hover,
.mv-search-clear:hover {
    color: #1e293b;
}
</style>
