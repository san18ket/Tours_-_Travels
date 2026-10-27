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
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Accounts -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Accounts</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAll ?? (int)$totalUsers) ?></div>
            <div class="booking-kpi-sub">
                <span>All provisioned credentials</span>
            </div>
        </div>

        <!-- Card 2: Active Administrators -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Administrators</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($activeAdmins ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Root system controllers</span>
            </div>
        </div>

        <!-- Card 3: Active Dispatchers -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Active Dispatchers</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalDispatchers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Fleet operations desk</span>
            </div>
        </div>

        <!-- Card 4: Chauffeurs on Roster -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Chauffeurs on Roster</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalDrivers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span>Driver mobile app users</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/users') ?>" class="filter-bar-compact" id="userFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by name, username, email, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="role" id="roleInput" onchange="document.getElementById('userFilterForm').submit()">
            <option value="">All Roles</option>
            <option value="admin" <?= ($selectedRole ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="dispatcher" <?= ($selectedRole ?? '') === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
            <option value="driver" <?= ($selectedRole ?? '') === 'driver' ? 'selected' : '' ?>>Chauffeur</option>
            <option value="customer" <?= ($selectedRole ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
        </select>

        <select name="status" id="statusInput" onchange="document.getElementById('userFilterForm').submit()">
            <option value="">All Statuses</option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($selectedStatus ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="suspended" <?= ($selectedStatus ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('userFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/users') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedRole) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/users') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span>Clear</span></a>
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
            <form method="GET" action="<?= url('/office/users') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search users..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/users' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($selectedRole)): ?>
                        <input type="hidden" name="role" value="<?= e($selectedRole) ?>">
                    <?php endif; ?>
                    <?php if (!empty($selectedStatus)): ?>
                        <input type="hidden" name="status" value="<?= e($selectedStatus) ?>">
                    <?php endif; ?>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <form method="GET" action="<?= url('/office/users') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvRoleSelect">Role:</label>
                        <select name="role" id="mvRoleSelect" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="admin" <?= ($selectedRole ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                            <option value="dispatcher" <?= ($selectedRole ?? '') === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
                            <option value="driver" <?= ($selectedRole ?? '') === 'driver' ? 'selected' : '' ?>>Chauffeur</option>
                            <option value="customer" <?= ($selectedRole ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                        </select>
                    </div>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($selectedStatus)): ?>
                        <input type="hidden" name="status" value="<?= e($selectedStatus) ?>">
                    <?php endif; ?>
                </form>
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
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalUsers ?? count($users)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>">
                <span>Active</span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' ? 'active' : '' ?>">
                <span>Inactive</span>
            </a>
            <a href="<?= $makeStatusUrl('suspended') ?>" class="mv-pill <?= $currStatus === 'suspended' ? 'active' : '' ?>">
                <span>Suspended</span>
            </a>
        </div>
    </div>

    <!-- 5. All Users Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
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
                            <div class="status-cell">
                                <span class="status-dot <?= $statusClass ?>"></span>
                                <span class="status-badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
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
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
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
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> account(s)</div>
        <div class="pagination">
            <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page">&#10094;</a>
            <?php
                $rangeStart = max(1, $currP - 1);
                $rangeEnd   = min($totalPages, $rangeStart + 2);
                if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                    $rangeStart = max(1, $rangeEnd - 2);
                }
                for ($i = $rangeStart; $i <= $rangeEnd; $i++):
            ?>
                <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($rangeEnd < $totalPages): ?>
                <span class="dots">...</span>
                <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page">&#10095;</a>
        </div>
    </div>
    <?php endif; ?>
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
