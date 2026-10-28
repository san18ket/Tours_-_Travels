<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'User Accounts & Access Control',
            'description' => 'Manage system administrators, fleet dispatchers, commercial chauffeurs, and client portal credentials.',
            'image'       => 'header-users.jpg',
            'badge'       => 'Security & RBAC Active',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Users & Staff', 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/users/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg><span>Add New User</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid" id="usersKpiGrid">
        <!-- Card 1: Total Accounts -->
        <div class="booking-kpi-card <?= (empty($selectedRole) && empty($selectedStatus)) ? 'is-selected' : '' ?>" data-role="" data-status="" role="button" tabindex="0" title="Click to view all provisioned accounts">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Accounts</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totalUsers) ?>"><?= number_format($totalAll ?? (int)$totalUsers) ?></div>
            <div class="booking-kpi-sub">
                <span>All provisioned credentials</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active Administrators -->
        <div class="booking-kpi-card <?= ($selectedRole ?? '') === 'admin' ? 'is-selected' : '' ?>" data-role="admin" data-status="active" role="button" tabindex="0" title="Click to filter active administrators">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Administrators</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($activeAdmins ?? 0) ?>"><?= number_format($activeAdmins ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Root system controllers</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Active Dispatchers -->
        <div class="booking-kpi-card <?= ($selectedRole ?? '') === 'dispatcher' ? 'is-selected' : '' ?>" data-role="dispatcher" data-status="active" role="button" tabindex="0" title="Click to filter active dispatchers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Dispatchers</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalDispatchers ?? 0) ?>"><?= number_format($totalDispatchers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Fleet operations desk</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Chauffeurs on Roster -->
        <div class="booking-kpi-card <?= ($selectedRole ?? '') === 'driver' ? 'is-selected' : '' ?>" data-role="driver" data-status="active" role="button" tabindex="0" title="Click to filter active chauffeurs">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Chauffeurs on Roster</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalDrivers ?? 0) ?>"><?= number_format($totalDrivers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Driver mobile app users</span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/users') ?>" class="filter-bar-compact" id="userFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by name, username, email, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="role" id="roleInput">
            <option value="">All Roles</option>
            <option value="admin" <?= ($selectedRole ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="dispatcher" <?= ($selectedRole ?? '') === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
            <option value="driver" <?= ($selectedRole ?? '') === 'driver' ? 'selected' : '' ?>>Chauffeur</option>
            <option value="customer" <?= ($selectedRole ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
        </select>

        <select name="status" id="statusInput">
            <option value="">All Statuses</option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($selectedStatus ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="suspended" <?= ($selectedStatus ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedRole) || !empty($selectedStatus)): ?>
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
                <h2 class="mv-page-title">Users</h2>
            </div>
            <a href="<?= url('/office/users/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Add User</span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/users') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search users..." value="<?= e($search ?? '') ?>" autocomplete="off">
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
                    <label for="mvRoleSelect">Role:</label>
                    <select name="role" id="mvRoleSelect">
                        <option value="">All Roles</option>
                        <option value="admin" <?= ($selectedRole ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        <option value="dispatcher" <?= ($selectedRole ?? '') === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
                        <option value="driver" <?= ($selectedRole ?? '') === 'driver' ? 'selected' : '' ?>>Chauffeur</option>
                        <option value="customer" <?= ($selectedRole ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>
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
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $selectedRole, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($selectedRole)) $p['role'] = $selectedRole;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/users' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="usersMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? $totalUsers ?? count($users)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>" data-status="active">
                <span>Active</span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' ? 'active' : '' ?>" data-status="inactive">
                <span>Inactive</span>
            </a>
            <a href="<?= $makeStatusUrl('suspended') ?>" class="mv-pill <?= $currStatus === 'suspended' ? 'active' : '' ?>" data-status="suspended">
                <span>Suspended</span>
            </a>
        </div>
    </div>

    <!-- 5. All Users Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="usersSection">
        <div class="section-header">
            <h3 class="section-title">All System Accounts</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalUsers ?? count($users)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="usersTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 25%;">User Profile & Handle</th>
                        <th style="width: 20%;">Email & Contact</th>
                        <th style="width: 15%;">System Role</th>
                        <th class="text-center" style="width: 14%;">Joined Date</th>
                        <th class="text-center" style="width: 12%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($users as $i => $u):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($u['status'] ?? 'active');
                        $statusClass = match($status) {
                            'active' => 'completed',
                            'inactive' => 'pending',
                            'suspended' => 'cancelled',
                            default => 'pending'
                        };
                        $statusText = ucfirst($status);
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="client-cell">
                                <div class="client-info">
                                    <span class="client-name">
                                        <?= e($u['name']) ?>
                                        <?php if ((int)$u['id'] === $currentUserId): ?>
                                            <span style="display:inline-block;background:rgba(23,107,91,0.12);color:#176B5B;border-radius:4px;padding:1px 6px;font-size:10.5px;font-weight:700;margin-left:4px;">You</span>
                                        <?php endif; ?>
                                    </span>
                                    <small style="font-family:monospace;color:var(--brand-primary, #176B5B);font-weight:600;font-size:11.5px;margin-top:2px;">
                                        @<?= e($u['username']) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13px; color: #1e293b; font-weight: 500;">
                                <?= e($u['email']) ?>
                            </div>
                            <?php if (!empty($u['phone'])): ?>
                                <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                    <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($u['phone']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span style="display:inline-block;background:rgba(22,68,59,0.12);color:#16443B;border:1px solid rgba(22,68,59,0.2);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:700;">
                                    👑 Administrator
                                </span>
                            <?php elseif ($u['role'] === 'dispatcher'): ?>
                                <span style="display:inline-block;background:rgba(23,107,91,0.12);color:#176B5B;border:1px solid rgba(23,107,91,0.2);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:700;">
                                    🎯 Dispatcher
                                </span>
                            <?php elseif ($u['role'] === 'driver'): ?>
                                <span style="display:inline-block;background:rgba(2,132,199,0.1);color:#0284c7;border:1px solid rgba(2,132,199,0.2);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:700;">
                                    🚗 Chauffeur
                                </span>
                            <?php else: ?>
                                <span style="display:inline-block;background:rgba(100,116,139,0.1);color:#475569;border:1px solid rgba(100,116,139,0.2);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;">
                                    👤 Customer
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="due-date">
                                <?= format_date($u['created_at']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="userToggleWrap-<?= (int)$u['id'] ?>">
                                <?php if ((int)$u['id'] !== $currentUserId && !($u['role'] === 'admin' && $u['status'] === 'active' && $activeAdmins <= 1)): ?>
                                    <button type="button" class="cust-status-toggle <?= $u['status'] === 'active' ? 'is-active' : '' ?>"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-name="<?= e($u['name'] ?? $u['username']) ?>"
                                            role="switch"
                                            aria-checked="<?= $u['status'] === 'active' ? 'true' : 'false' ?>"
                                            onclick="toggleUserStatus(<?= (int)$u['id'] ?>, this, event)"
                                            title="Click to toggle account status">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                <?php endif; ?>
                                <span class="cust-toggle-pill <?= $statusClass ?>">
                                    <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                    <span class="toggle-label-text"><?= $statusText ?></span>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/office/users/' . $u['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="action-btn-table view" onclick="openResetModal(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['username'])) ?>')" title="Reset Password">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    <?php if ((int)$u['id'] !== $currentUserId && !($u['role'] === 'admin' && $u['status'] === 'active' && $activeAdmins <= 1)): ?>
                                    <form action="<?= url('/office/users/' . $u['id'] . '/toggle-status') ?>" method="POST" style="margin:0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="action-btn-table dispatch" title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fa-solid <?= $u['status'] === 'active' ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No user accounts found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="usersCards">
            <?php foreach ($users as $u):
                $status = strtolower($u['status'] ?? 'active');
                $statusClass = match($status) {
                    'active' => 'active',
                    'suspended' => 'cancelled',
                    default => 'pending'
                };
                $statusText = ucfirst($status);
                $initials = strtoupper(mb_substr($u['name'] ?? 'U', 0, 1));
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/users/' . $u['id'] . '/edit') ?>" class="mv-booking-card">
                    <!-- Left: User Avatar Monogram -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <?= e($initials) ?>
                        </div>
                    </div>

                    <!-- Middle: User Details -->
                    <div class="mv-details">
                        <div class="mv-code">@<?= e($u['username']) ?></div>
                        <div class="mv-tour-title" title="<?= e($u['name']) ?>">
                            <?= e($u['name']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-envelope"></i>
                            <span><?= e($u['email']) ?></span>
                        </div>
                        <?php if (!empty($u['phone'])): ?>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e($u['phone']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span style="text-transform:capitalize;"><?= e($u['role']) ?></span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Role text bottom -->
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" id="mvUserToggleWrap-<?= (int)$u['id'] ?>">
                            <?php if ((int)$u['id'] !== $currentUserId && !($u['role'] === 'admin' && $u['status'] === 'active' && $activeAdmins <= 1)): ?>
                                <button type="button" class="cust-status-toggle <?= $u['status'] === 'active' ? 'is-active' : '' ?>"
                                        data-id="<?= (int)$u['id'] ?>"
                                        data-name="<?= e($u['name'] ?? $u['username']) ?>"
                                        role="switch"
                                        aria-checked="<?= $u['status'] === 'active' ? 'true' : 'false' ?>"
                                        onclick="toggleUserStatus(<?= (int)$u['id'] ?>, this, event)"
                                        title="Toggle account status">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb"></span>
                                    </span>
                                </button>
                            <?php endif; ?>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price" style="font-size: 11px; color: #16443B; font-weight: 700; text-transform: uppercase;">
                            <?= e($u['role']) ?>
                        </div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/users/' . $u['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Profile">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="action-btn-table view" onclick="openResetModal(<?= (int)$u['id'] ?>, '<?= e(addslashes($u['username'])) ?>')" title="Reset Password">
                            <i class="fa-solid fa-key"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No user accounts found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalUsers ?? count($users);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedRole, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedRole)) $q['role'] = $selectedRole;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/users?' . http_build_query($q));
        };
    ?>
    <div id="usersPaginationWrapper">
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> account(s)</div>
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

<!-- Modal: Reset User Password -->
<div id="resetPasswordModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(11, 25, 44, 0.65); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 440px; margin: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden; border: 1px solid rgba(23,107,91,0.15);">
        <div style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <span style="font-weight: 700; color: #16443B; font-size: 1.05rem;">Reset User Password</span>
            <button type="button" onclick="closeResetModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
        </div>
        <div style="padding: 22px;">
            <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 1.25rem;">
                Set a new password for account: <strong id="resetTargetUsername" style="color: #176B5B;"></strong>
            </p>

            <form id="resetPasswordForm" method="POST" action="">
                <?= csrf_field() ?>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" for="new_password" style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters" style="padding-right: 2.75rem; width: 100%; height: 42px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding-left: 12px; box-sizing: border-box;">
                        <button type="button" onclick="toggleInputVisibility('new_password')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px;">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="new_password_confirmation" style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-control" required minlength="6" placeholder="Repeat new password" style="padding-right: 2.75rem; width: 100%; height: 42px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding-left: 12px; box-sizing: border-box;">
                        <button type="button" onclick="toggleInputVisibility('new_password_confirmation')" title="Toggle visibility" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px;">
                            👁️
                        </button>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeResetModal()" style="border-radius: 10px; height: 40px; padding: 0 16px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; height: 40px; padding: 0 20px; font-weight: 700;">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /* ========================================================================= */
    /* Users Directory Live AJAX Filter Engine (Matches Customers Architecture)   */
    /* ========================================================================= */
    (function initUserAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/users') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const roleInput = document.getElementById('roleInput');
            const mvRoleSelect = document.getElementById('mvRoleSelect');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const rVal = roleInput ? roleInput.value : (mvRoleSelect ? mvRoleSelect.value : '');
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (rVal) p.set('role', rVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, roleVal: rVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterUsersAjax(page = 1, options = {}) {
            const { params, searchVal, roleVal, statusVal, perPageVal, pageNum } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('usersSection');
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

                const newSection = doc.getElementById('usersSection');
                const currSection = document.getElementById('usersSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('usersPaginationWrapper');
                const currPagination = document.getElementById('usersPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('usersKpiGrid');
                const currKpi = document.getElementById('usersKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('usersMvStatusScroll');
                const currMvPills = document.getElementById('usersMvStatusScroll');
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

                const roleInput = document.getElementById('roleInput');
                const mvRoleSelect = document.getElementById('mvRoleSelect');
                if (roleInput && roleInput.value !== roleVal) roleInput.value = roleVal;
                if (mvRoleSelect && mvRoleSelect.value !== roleVal) mvRoleSelect.value = roleVal;

                const statusInput = document.getElementById('statusInput');
                if (statusInput && statusInput.value !== statusVal) statusInput.value = statusVal;

                const perPageInput = document.getElementById('perPageInput');
                const mvPerPageSelect = document.getElementById('mvPerPageSelect');
                if (perPageInput && perPageInput.value !== perPageVal) perPageInput.value = perPageVal;
                if (mvPerPageSelect && mvPerPageSelect.value !== perPageVal) mvPerPageSelect.value = perPageVal;

                if (!options.skipPushState) {
                    if (options.isTyping) {
                        window.history.replaceState({ searchVal, roleVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    } else {
                        window.history.pushState({ searchVal, roleVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('User Filter AJAX Error:', err);
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        function animateKpiNumbers(container) {
            if (!container) return;
            container.querySelectorAll('.booking-kpi-val[data-count]').forEach(el => {
                const target = parseInt(el.getAttribute('data-count'), 10) || 0;
                el.textContent = target.toLocaleString();
            });
        }

        // Live Dynamic KPI Cards Syncer (AJAX Real-Time Update)
        function updateUserMetrics(metrics) {
            if (!metrics) return;
            const grid = document.getElementById('usersKpiGrid');
            if (grid) {
                const totalEl = grid.querySelector('.booking-kpi-card[data-role=""][data-status=""] .booking-kpi-val');
                if (totalEl && metrics.total !== undefined) totalEl.setAttribute('data-count', metrics.total);

                const adminEl = grid.querySelector('.booking-kpi-card[data-role="admin"] .booking-kpi-val');
                if (adminEl && metrics.admins !== undefined) adminEl.setAttribute('data-count', metrics.admins);

                const dispEl = grid.querySelector('.booking-kpi-card[data-role="dispatcher"] .booking-kpi-val');
                if (dispEl && metrics.dispatchers !== undefined) dispEl.setAttribute('data-count', metrics.dispatchers);

                const driverEl = grid.querySelector('.booking-kpi-card[data-role="driver"] .booking-kpi-val');
                if (driverEl && metrics.drivers !== undefined) driverEl.setAttribute('data-count', metrics.drivers);

                animateKpiNumbers(grid);
            }

            const scrollWrap = document.getElementById('usersMvStatusScroll');
            if (scrollWrap) {
                const pillAll = scrollWrap.querySelector('.mv-pill[data-status=""] .mv-pill-cnt');
                if (pillAll && metrics.total !== undefined) pillAll.textContent = (parseInt(metrics.total, 10) || 0).toLocaleString();
            }

            const totalPill = document.getElementById('totalRecordsPill');
            if (totalPill && metrics.total !== undefined) {
                totalPill.textContent = (parseInt(metrics.total, 10) || 0).toLocaleString();
            }
        }

        // Live Search with Debounce (Desktop)
        const sInput = document.getElementById('searchInput');
        if (sInput) {
            sInput.addEventListener('input', function() {
                const val = this.value;
                const mInput = document.getElementById('mvSearchInput');
                if (mInput && mInput.value !== val) mInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterUsersAjax(1, { isTyping: true }), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterUsersAjax(1);
                }
            });
        }

        // Live Search with Debounce (Mobile)
        const mvSInput = document.getElementById('mvSearchInput');
        if (mvSInput) {
            mvSInput.addEventListener('input', function() {
                const val = this.value;
                const dInput = document.getElementById('searchInput');
                if (dInput && dInput.value !== val) dInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterUsersAjax(1, { isTyping: true }), 280);
            });
            mvSInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterUsersAjax(1);
                }
            });
        }

        // Dropdowns
        const rInput = document.getElementById('roleInput');
        if (rInput) rInput.addEventListener('change', () => filterUsersAjax(1));
        const mvRSelect = document.getElementById('mvRoleSelect');
        if (mvRSelect) mvRSelect.addEventListener('change', () => {
            if (rInput) rInput.value = mvRSelect.value;
            filterUsersAjax(1);
        });

        const stInput = document.getElementById('statusInput');
        if (stInput) stInput.addEventListener('change', () => filterUsersAjax(1));

        const ppInput = document.getElementById('perPageInput');
        if (ppInput) ppInput.addEventListener('change', function() {
            const mvSelect = document.getElementById('mvPerPageSelect');
            if (mvSelect) mvSelect.value = this.value;
            filterUsersAjax(1);
        });

        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) mvPpSelect.addEventListener('change', function() {
            const dSelect = document.getElementById('perPageInput');
            if (dSelect) dSelect.value = this.value;
            filterUsersAjax(1);
        });

        // Apply Button Click
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterUsersAjax(1);
            });
        }

        // Refresh Button Click
        const refBtn = document.getElementById('refreshBtn');
        if (refBtn) {
            refBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const s = document.getElementById('searchInput');
                const ms = document.getElementById('mvSearchInput');
                const r = document.getElementById('roleInput');
                const mr = document.getElementById('mvRoleSelect');
                const st = document.getElementById('statusInput');
                const pp = document.getElementById('perPageInput');
                const mpp = document.getElementById('mvPerPageSelect');
                if (s) s.value = '';
                if (ms) ms.value = '';
                if (r) r.value = '';
                if (mr) mr.value = '';
                if (st) st.value = '';
                if (pp) pp.value = '10';
                if (mpp) mpp.value = '10';
                filterUsersAjax(1);
            });
        }

        // Document-level event delegation
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#usersKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const role = kpiCard.getAttribute('data-role') ?? '';
                const st = kpiCard.getAttribute('data-status') ?? '';
                const roleInput = document.getElementById('roleInput');
                const stInput = document.getElementById('statusInput');
                if (roleInput) roleInput.value = role;
                if (stInput) stInput.value = st;

                document.querySelectorAll('#usersKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#usersMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterUsersAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                } else {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    const r = document.getElementById('roleInput');
                    const mr = document.getElementById('mvRoleSelect');
                    const st = document.getElementById('statusInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                    if (r) r.value = '';
                    if (mr) mr.value = '';
                    if (st) st.value = '';
                }
                filterUsersAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#usersMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#usersMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#usersKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st && !c.getAttribute('data-role'));
                });

                filterUsersAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#usersPaginationWrapper .pagination a');
            if (pageLink && !pageLink.classList.contains('disabled')) {
                e.preventDefault();
                e.stopPropagation();
                const pageAttr = pageLink.getAttribute('data-page');
                let targetPage = 1;
                if (pageAttr) {
                    targetPage = parseInt(pageAttr, 10) || 1;
                } else {
                    const href = pageLink.getAttribute('href') || '';
                    const match = href.match(/page=(\d+)/);
                    if (match) targetPage = parseInt(match[1], 10) || 1;
                }
                filterUsersAjax(targetPage);

                const sec = document.getElementById('usersSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#usersKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterUsersAjax(1, { skipPushState: true });
        });

        window.updateUserMetrics = updateUserMetrics;
        window.filterUsersAjax = filterUsersAjax;
    })();

    /* ========================================================================= */
    /* Professional User Status Toggle (AJAX with live KPI sync)                */
    /* ========================================================================= */
    function toggleUserStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/users/') ?>' + id + '/toggle-status';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: new URLSearchParams({
                '_csrf_token': csrfToken,
                'csrf_token': csrfToken
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(j => { throw new Error(j.message || ('HTTP ' + res.status)); });
            }
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                const isNowActive = (data.status === 'active');

                // Update all matching toggle buttons (table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowActive) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle status (currently Active)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle status (currently Inactive)';
                    }
                });

                // Update table status pill & dot
                const wrap = document.getElementById('userToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    const pillClass = isNowActive ? 'completed' : 'cancelled';
                    if (pill) pill.className = 'cust-toggle-pill ' + pillClass;
                    if (dot) dot.className = 'toggle-pulse-dot ' + pillClass;
                    if (text) text.textContent = data.label;
                }

                // Update mobile cards
                const mvWrap = document.getElementById('mvUserToggleWrap-' + id);
                if (mvWrap) {
                    const mText = mvWrap.querySelector('.mv-toggle-label-text');
                    if (mText) {
                        mText.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'cancelled');
                        mText.textContent = data.label;
                    }
                }

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateUserMetrics === 'function') {
                    window.updateUserMetrics(data.metrics);
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message, 'success');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error updating user status', 'error');
            } else {
                alert(err.message || 'Error updating user status');
            }
        });
    }

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

<style>
/* Status Toggle Switch (Executive Standards matching Customers) */
.cust-toggle-cell {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.cust-status-toggle {
    border: none;
    background: transparent;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-flex;
    align-items: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.cust-status-toggle .toggle-slider-track {
    width: 38px;
    height: 20px;
    background: #CBD5E1;
    border-radius: 999px;
    transition: background-color 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    display: block;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}
.cust-status-toggle.is-active .toggle-slider-track {
    background: #10B981;
}
.cust-status-toggle .toggle-slider-thumb {
    width: 16px;
    height: 16px;
    background: #FFFFFF;
    border-radius: 50%;
    position: absolute;
    top: 2px;
    left: 2px;
    transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
}
.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(18px);
}
.cust-status-toggle .toggle-spinner {
    font-size: 9px;
    color: #0284C7;
}
.cust-status-toggle.is-loading {
    pointer-events: none;
    opacity: 0.7;
}
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 4.5px;
    padding: 2px 7.5px;
    border-radius: 999px;
    font-size: 0.69rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: capitalize;
    line-height: 1.2;
}
.cust-toggle-pill.completed {
    background: rgba(16, 185, 129, 0.12);
    color: #065F46;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.cust-toggle-pill.cancelled {
    background: rgba(148, 163, 184, 0.14);
    color: #475569;
    border: 1px solid rgba(148, 163, 184, 0.28);
}
.toggle-pulse-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    display: inline-block;
}
.toggle-pulse-dot.completed {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    animation: vgPulse 2s infinite;
}
.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}
.mv-status-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}
.mv-toggle-label-text {
    font-size: 0.74rem;
    font-weight: 700;
}
.mv-toggle-label-text.completed {
    color: #059669;
}
.mv-toggle-label-text.cancelled {
    color: #64748B;
}

/* Synchronized Executive Aesthetics matching Customers */
.booking-kpi-card {
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.booking-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
    border-color: #BFDBFE;
}
.booking-kpi-card.is-selected {
    border-color: #2563EB;
    background: linear-gradient(180deg, #FFFFFF 0%, #EFF6FF 100%);
    box-shadow: 0 4px 18px rgba(37, 99, 235, 0.18);
}
.booking-kpi-sub {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.kpi-filter-tag {
    font-size: 0.72rem;
    color: #94A3B8;
    transition: transform 0.2s ease, color 0.2s ease;
}
.booking-kpi-card:hover .kpi-filter-tag,
.booking-kpi-card.is-selected .kpi-filter-tag {
    color: #2563EB;
    transform: translateX(3px);
}
.recent-bookings-section.is-filtering {
    opacity: 0.6;
    pointer-events: none;
    transition: opacity 0.15s ease;
}
.fbc-search {
    position: relative;
    display: flex;
    align-items: center;
}
.fbc-search-clear, .mv-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    line-height: 1;
}
.fbc-search-clear:hover, .mv-search-clear:hover {
    color: #0F172A;
}
</style>
