<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('employees.title', 'Chauffeur & Staff Directory'),
            'description' => __('employees.subtitle', 'Comprehensive staff records, operational departments, and commercial chauffeur rosters.'),
            'image'       => 'header-employees.jpg',
            'badge'       => 'Staff & Drivers',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('employees.title', 'Employees'), 'url' => ''],
            ],
            'actions'     => (auth_role() === 'admin') ? '<a href="' . url('/office/employees/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.add_employee', 'Add Employee')) . '</span></a>' : null,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Interactive like Customers) -->
    <div class="booking-kpi-grid" id="employeesKpiGrid">
        <!-- Card 1: Total Staff -->
        <div class="booking-kpi-card <?= (empty($selectedStatus) && empty($selectedDepartment)) ? 'is-selected' : '' ?>" data-status="" data-department="" data-kpi="total" role="button" tabindex="0" title="Click to view all staff members">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_staff', 'Total Staff')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totalEmployees ?? count($employees)) ?>"><?= number_format($totalAll ?? $totalEmployees ?? count($employees)) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered staff personnel')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active Employees -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'active' ? 'is-selected' : '' ?>" data-status="active" data-department="" data-kpi="active" role="button" tabindex="0" title="Click to filter active staff">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.active_employees', 'Active Employees')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalActive ?? 0) ?>"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'On-duty & operational')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: On Leave -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'on_leave' ? 'is-selected' : '' ?>" data-status="on_leave" data-department="" data-kpi="on_leave" role="button" tabindex="0" title="Click to filter staff on leave">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('status.on_leave', 'On Leave')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalOnLeave ?? 0) ?>"><?= number_format($totalOnLeave ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.temporarily_off', 'Temporarily off duty')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Fleet Chauffeurs -->
        <div class="booking-kpi-card <?= ($selectedDepartment ?? '') === 'fleet_chauffeur' ? 'is-selected' : '' ?>" data-status="" data-department="fleet_chauffeur" data-kpi="chauffeurs" role="button" tabindex="0" title="Click to filter commercial chauffeurs">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.fleet_chauffeurs', 'Fleet Chauffeurs')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalChauffeurs ?? 0) ?>"><?= number_format($totalChauffeurs ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Commercial drivers roster')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Customers Architecture) -->
    <form method="GET" action="<?= url('/office/employees') ?>" class="filter-bar-compact" id="employeeFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by name, employee code, phone, designation..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value=""><?= e(__('common.all_statuses', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>><?= e(__('status.active', 'Active')) ?></option>
            <option value="on_leave" <?= ($selectedStatus ?? '') === 'on_leave' ? 'selected' : '' ?>><?= e(__('status.on_leave', 'On Leave')) ?></option>
            <option value="terminated" <?= ($selectedStatus ?? '') === 'terminated' ? 'selected' : '' ?>><?= e(__('status.terminated', 'Terminated')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <button type="button" class="fbc-btn fbc-btn-clear" id="filterClearBtn" title="Clear Filters" data-no-ajax="true"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="<?= e(__('btn.back', 'Back')) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title"><?= e(__('employees.title', 'Staff Members')) ?></h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/employees/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span><?= e(__('btn.add_employee', 'Add Staff')) ?></span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Search Bar + Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/employees') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search name, code, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Filter Panel (Mobile Drawer) -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 25, 50, 100] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Filter Pills -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $selectedDepartment, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($selectedDepartment)) $p['department'] = $selectedDepartment;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/employees' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="employeesMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalEmployees ?? count($employees)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>" data-status="active" data-no-ajax="true">
                <span>Active</span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('on_leave') ?>" class="mv-pill <?= $currStatus === 'on_leave' ? 'active' : '' ?>" data-status="on_leave" data-no-ajax="true">
                <span>On Leave</span>
            </a>
            <a href="<?= $makeStatusUrl('terminated') ?>" class="mv-pill <?= $currStatus === 'terminated' ? 'active' : '' ?>" data-status="terminated" data-no-ajax="true">
                <span>Terminated</span>
            </a>
        </div>
    </div>

    <!-- 5. All Staff Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="employeesSection">
        <div class="section-header">
            <h3 class="section-title">All Staff Members</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalEmployees ?? count($employees)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="employeesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th class="text-center" style="width: 80px;"><?= e(__('table.profile', 'Profile')) ?></th>
                        <th style="min-width: 220px;">Employee Details</th>
                        <th style="min-width: 140px;">Employee &amp; Role</th>
                        <th style="min-width: 200px;">Address</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody id="employeesTableBody">
                    <?php foreach ($employees as $i => $e):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($e['status'] ?? 'active');
                        $statusClass = match($status) {
                            'active' => 'completed',
                            'on_leave' => 'assigned',
                            'terminated' => 'cancelled',
                            default => 'pending'
                        };
                        $statusText = ($status === 'active') ? 'Active' : (($status === 'on_leave') ? 'On Leave' : ucfirst(str_replace('_', ' ', $status)));
                        $initials = strtoupper(mb_substr($e['first_name'] ?? 'E', 0, 1) . mb_substr($e['last_name'] ?? '', 0, 1));
                        $fullName = trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
                        $avatarUrl = customer_avatar_url($e['profile_photo'] ?? null);

                        // Role calculation
                        $role = !empty($e['linked_role']) ? $e['linked_role'] : (!empty($e['role']) ? $e['role'] : ($e['department'] === 'fleet_chauffeur' ? 'driver' : strtolower($e['department'] ?? 'staff')));
                        $roleText = ucwords(str_replace('_', ' ', $role));

                        // Address resolution matching customer architecture
                        $addressLine = trim($e['address_line'] ?? '');
                        if (empty($addressLine) && !empty($e['address'])) {
                            $addressLine = trim($e['address']);
                        }
                        $city = trim($e['city'] ?? '');
                        $state = trim($e['state'] ?? '');
                        $pincode = trim($e['postal_code'] ?? '');
                        $cityStatePin = trim(implode(', ', array_filter([$city, $state])));
                        if (!empty($cityStatePin) && !empty($pincode)) {
                            $cityStatePin .= ' - ' . $pincode;
                        } elseif (!empty($pincode)) {
                            $cityStatePin = $pincode;
                        }
                    ?>
                    <tr>
                        <!-- 1. Serial Number (#) -->
                        <td class="text-center">
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>

                        <!-- 2. Profile (Avatar / Monogram like customer/index) -->
                        <td class="text-center">
                            <div class="cust-table-avatar-wrap">
                                <?php if (!empty($avatarUrl)): ?>
                                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($fullName) ?>" class="cust-table-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="cust-table-monogram" style="display: none;"><?= e($initials) ?></div>
                                <?php else: ?>
                                    <div class="cust-table-monogram"><?= e($initials) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Employee Details (Name, Email, Mobile No. only) -->
                        <td>
                            <div class="cust-client-details-cell" style="display: flex; flex-direction: column; gap: 4px;">
                                <div class="cust-name-row">
                                    <a href="<?= url('/office/employees/' . $e['id']) ?>" class="cust-full-name font-bold" title="View employee profile" style="font-weight: 700; color: #0F172A; text-decoration: none; font-size: 14px;">
                                        <?= e($fullName) ?>
                                    </a>
                                </div>
                                <div class="cust-email-row" style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748B;">
                                    <i class="fa-regular fa-envelope" style="font-size: 11px; color: #64748B;"></i>
                                    <?php if (!empty($e['email'])): ?>
                                        <a href="mailto:<?= e($e['email']) ?>" style="color: inherit; text-decoration: none;" title="Email employee"><?= e($e['email']) ?></a>
                                    <?php else: ?>
                                        <span style="color: #94A3B8; font-style: italic;">No email on record</span>
                                    <?php endif; ?>
                                </div>
                                <div class="cust-phone-row" style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748B;">
                                    <i class="fa-solid fa-phone" style="font-size: 10px; color: var(--brand-primary, #176B5B);"></i>
                                    <a href="tel:<?= e($e['phone']) ?>" style="color: #0F172A; font-weight: 600; text-decoration: none;" title="Call employee"><?= e($e['phone']) ?></a>
                                </div>
                            </div>
                        </td>

                        <!-- 4. Employee & Role (Shows only role) -->
                        <td>
                            <div class="emp-role-cell">
                                <span class="emp-role-pill" style="display: inline-block; background: rgba(23, 107, 91,0.08); color: #16443B; border: 1px solid rgba(23, 107, 91,0.2); border-radius: 6px; padding: 4px 12px; font-size: 12.5px; font-weight: 700; text-transform: capitalize;">
                                    <?= e($roleText) ?>
                                </span>
                            </div>
                        </td>

                        <!-- 5. Address -->
                        <td>
                            <div class="cust-address-cell">
                                <?php if (!empty($addressLine) || !empty($cityStatePin)): ?>
                                    <?php if (!empty($addressLine)): ?>
                                        <div class="cust-address-line" title="<?= e($addressLine) ?>">
                                            <i class="fa-solid fa-location-dot cust-address-icon"></i>
                                            <span><?= e($addressLine) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($cityStatePin)): ?>
                                        <div class="cust-address-sub" title="<?= e($cityStatePin) ?>">
                                            <?php if (empty($addressLine)): ?><i class="fa-solid fa-location-dot cust-address-icon"></i><?php endif; ?>
                                            <span><?= e($cityStatePin) ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="cust-no-address">No address on file</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 6. Status Toggle Switch -->
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="employeeToggleWrap-<?= (int)$e['id'] ?>">
                                <button type="button" 
                                        class="cust-status-toggle <?= $status === 'active' ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $status === 'active' ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$e['id'] ?>"
                                        data-name="<?= addslashes(e($fullName)) ?>"
                                        onclick="toggleEmployeeStatus(<?= (int)$e['id'] ?>, this, event)"
                                        title="Click to toggle status (currently <?= $statusText ?>)">
                                    <span class="toggle-slider-track">
                                        <span class="toggle-slider-thumb">
                                            <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                        </span>
                                    </span>
                                </button>
                                <span class="cust-toggle-pill <?= $statusClass ?>">
                                    <span class="toggle-pulse-dot <?= $statusClass ?>"></span>
                                    <span class="toggle-label-text"><?= $statusText ?></span>
                                </span>
                            </div>
                        </td>

                        <!-- 7. Action Button -->
                        <td class="text-center">
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/office/employees/' . $e['id']) ?>" class="action-btn-table view" title="View Profile">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (auth_role() === 'admin'): ?>
                                    <a href="<?= url('/office/employees/' . $e['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Employee">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="action-btn-table delete" title="Delete Employee" onclick="openDeleteModal(<?= (int)$e['id'] ?>, '<?= addslashes(e($fullName)) ?>', event)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No employees found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Touch Cards List -->
        <div class="cards-grid" id="employeesCards">
            <?php foreach ($employees as $e):
                $status = strtolower($e['status'] ?? 'active');
                $statusClass = match($status) {
                    'active' => 'completed',
                    'on_leave' => 'assigned',
                    'terminated' => 'cancelled',
                    default => 'pending'
                };
                $statusText = ($status === 'active') ? 'Active' : (($status === 'on_leave') ? 'On Leave' : ucfirst(str_replace('_', ' ', $status)));
                $initials = strtoupper(mb_substr($e['first_name'] ?? 'E', 0, 1) . mb_substr($e['last_name'] ?? '', 0, 1));
                $fullName = trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));

                $addressLine = trim($e['address_line'] ?? '');
                if (empty($addressLine) && !empty($e['address'])) {
                    $addressLine = trim($e['address']);
                }
                $city = trim($e['city'] ?? '');
                $state = trim($e['state'] ?? '');
                $pincode = trim($e['postal_code'] ?? '');
                $cityStatePin = trim(implode(', ', array_filter([$city, $state])));
                if (!empty($cityStatePin) && !empty($pincode)) {
                    $cityStatePin .= ' - ' . $pincode;
                } elseif (!empty($pincode)) {
                    $cityStatePin = $pincode;
                }
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/employees/' . $e['id']) ?>" class="mv-booking-card">
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar" style="background:#E8F7F0;color:#176B5B;overflow:hidden;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;">
                            <?php if (!empty($e['profile_photo'])): ?>
                                <img src="<?= e(url($e['profile_photo'])) ?>" alt="<?= e($fullName) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span style="display: none;"><?= e($initials) ?></span>
                            <?php else: ?>
                                <span><?= e($initials) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mv-details">
                        <div class="mv-code"><?= e($e['employee_code']) ?></div>
                        <div class="mv-tour-title" title="<?= e($fullName) ?>">
                            <?= e($fullName) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-briefcase"></i>
                            <span><?= e($e['designation'] ?: ucfirst(str_replace('_', ' ', $e['role'] ?? 'Staff'))) ?> &bull; <?= e(ucfirst(str_replace('_', ' ', $e['department']))) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e($e['phone']) ?></span>
                        </div>
                        <?php if (!empty($cityStatePin) || !empty($addressLine)): ?>
                            <div style="font-size: 11.5px; color: #64748B; margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-location-dot" style="color: #EF4444; font-size: 10px;"></i>
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;"><?= e(!empty($cityStatePin) ? $cityStatePin : $addressLine) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.stopPropagation()">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $status === 'active' ? 'is-active' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $status === 'active' ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$e['id'] ?>"
                                    data-name="<?= addslashes(e($fullName)) ?>"
                                    onclick="toggleEmployeeStatus(<?= (int)$e['id'] ?>, this, event)"
                                    title="Toggle status">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb"></span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price" style="font-size: 11.5px; color: #16443B; margin-top: 4px;">
                            <?= ucfirst(str_replace('_', ' ', $e['employment_type'])) ?>
                        </div>
                    </div>
                </a>
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/employees/' . $e['id']) ?>" class="action-btn-table view" title="View Profile">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if (auth_role() === 'admin'): ?>
                        <a href="<?= url('/office/employees/' . $e['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Employee">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <button type="button" class="action-btn-table delete" title="Delete Employee" onclick="openDeleteModal(<?= (int)$e['id'] ?>, '<?= addslashes(e($fullName)) ?>', event)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($employees)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No employees found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Customers Architecture) -->
    <?php
        $totRec = $totalEmployees ?? count($employees);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedDepartment, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedDepartment)) $q['department'] = $selectedDepartment;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/employees?' . http_build_query($q));
        };
    ?>
    <div id="employeesPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> staff member(s)</div>
            <div class="pagination">
                <a href="<?= $buildPageUrl(max(1, $currP - 1)) ?>" class="<?= $currP <= 1 ? 'disabled' : '' ?>" title="Previous Page" data-page="<?= max(1, $currP - 1) ?>" data-no-ajax="true">&#10094;</a>
                <?php
                    $rangeStart = max(1, $currP - 1);
                    $rangeEnd   = min($totalPages, $rangeStart + 2);
                    if ($rangeEnd - $rangeStart < 2 && $rangeStart > 1) {
                        $rangeStart = max(1, $rangeEnd - 2);
                    }
                    for ($i = $rangeStart; $i <= $rangeEnd; $i++):
                ?>
                    <a href="<?= $buildPageUrl($i) ?>" class="<?= $i === $currP ? 'active' : '' ?>" data-page="<?= $i ?>" data-no-ajax="true"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($rangeEnd < $totalPages): ?>
                    <span class="dots">...</span>
                    <a href="<?= $buildPageUrl($totalPages) ?>" class="<?= $currP === $totalPages ? 'active' : '' ?>" data-page="<?= $totalPages ?>" data-no-ajax="true"><?= $totalPages ?></a>
                <?php endif; ?>
                <a href="<?= $buildPageUrl(min($totalPages, $currP + 1)) ?>" class="<?= $currP >= $totalPages ? 'disabled' : '' ?>" title="Next Page" data-page="<?= min($totalPages, $currP + 1) ?>" data-no-ajax="true">&#10095;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

    <!-- ========================================================================= -->
    <!-- DELETE CONFIRMATION MODALS (Matches design in delete.jpeg)                -->
    <!-- ========================================================================= -->
    <div id="deleteModalBackdrop" class="vg-del-backdrop" style="display: none;" onclick="handleBackdropClick(event)">
        <!-- MODAL 1: Initial Delete with Checkbox (Matching delete.jpeg Top Card) -->
        <div class="vg-del-modal" id="deleteStep1Modal" role="dialog" aria-modal="true" aria-labelledby="delModalTitle1">
            <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="Close dialog">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="vg-del-body">
                <!-- Left glowing icon matching delete.jpeg top modal -->
                <div class="vg-del-icon-halo">
                    <div class="vg-del-icon-circle">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                </div>

                <!-- Right text content -->
                <div class="vg-del-content">
                    <h3 class="vg-del-title" id="delModalTitle1">Delete</h3>
                    <p class="vg-del-desc" id="delModalDesc1">
                        This item will be permanently deleted and cannot be recovered.
                    </p>

                    <!-- Checkbox requirement -->
                    <div class="vg-del-checkbox-wrap" id="delCheckboxContainer">
                        <label class="vg-del-checkbox-label">
                            <input type="checkbox" id="deleteStep1Checkbox" class="vg-del-checkbox-input" onchange="toggleStep1DeleteBtn()">
                            <span class="vg-del-checkbox-box">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="vg-del-checkbox-text">
                                I understand this employee will be permanently deleted.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action buttons (Cancel & Delete pill buttons matching delete.jpeg) -->
            <div class="vg-del-actions">
                <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="vg-del-btn-delete disabled" id="deleteStep1Btn" onclick="goToStep2Modal()">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Delete</span>
                </button>
            </div>
        </div>

        <!-- MODAL 2: "Are you sure?" Final Confirmation (Matching delete.jpeg Bottom Card) -->
        <div class="vg-del-modal" id="deleteStep2Modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="delModalTitle2">
            <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="Close dialog">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="vg-del-body">
                <!-- Left glowing triangle warning icon matching delete.jpeg bottom modal -->
                <div class="vg-del-icon-halo warning">
                    <div class="vg-del-warning-triangle">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" class="vg-del-triangle-svg">
                            <path d="M10.29 3.86L1.82 18.54A2 2 0 0 0 3.53 21.5h16.94a2 2 0 0 0 1.71-2.96L13.71 3.86a2 2 0 0 0-3.42 0z" fill="url(#vgDelTriangleGrad)"/>
                            <line x1="12" y1="9" x2="12" y2="13.5" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round"/>
                            <circle cx="12" cy="17.2" r="1.3" fill="#FFFFFF"/>
                            <defs>
                                <linearGradient id="vgDelTriangleGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FF4D6D"/>
                                    <stop offset="1" stop-color="#EF233C"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>

                <!-- Right text content -->
                <div class="vg-del-content">
                    <h3 class="vg-del-title" id="delModalTitle2">Are you sure?</h3>
                    <p class="vg-del-desc" id="delModalDesc2">
                        You are about to delete this item. This action cannot be undone.
                    </p>

                    <!-- Checkbox requirement in Modal 2 -->
                    <div class="vg-del-checkbox-wrap" id="delCheckboxContainer2">
                        <label class="vg-del-checkbox-label">
                            <input type="checkbox" id="deleteStep2Checkbox" class="vg-del-checkbox-input" onchange="toggleStep2DeleteBtn()">
                            <span class="vg-del-checkbox-box">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="vg-del-checkbox-text">
                                Yes, I am completely sure and want to delete this employee.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action buttons matching delete.jpeg bottom modal -->
            <div class="vg-del-actions">
                <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="vg-del-btn-delete disabled" id="deleteConfirmFinalBtn" onclick="confirmDeleteFinal()">
                    <i class="fa-solid fa-trash-can"></i>
                    <span id="deleteFinalBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden fallback form -->
    <form id="deleteEmployeeForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
    </form>

<script>
    /* ========================================================================= */
    /* Employees Directory Live AJAX Filter Engine (Matches Customers Model)     */
    /* ========================================================================= */
    (function initEmployeeAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;
        let activeDept = '<?= addslashes(e($selectedDepartment ?? '')) ?>';

        const baseUrl = '<?= url('/office/employees') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const dVal = activeDept;
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (dVal) p.set('department', dVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, deptVal: dVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterEmployeesAjax(page = 1, options = {}) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('employeesSection');
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

                const newSection = doc.getElementById('employeesSection');
                const currSection = document.getElementById('employeesSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('employeesPaginationWrapper');
                const currPagination = document.getElementById('employeesPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('employeesKpiGrid');
                const currKpi = document.getElementById('employeesKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('employeesMvStatusScroll');
                const currMvPills = document.getElementById('employeesMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                if (!options.skipPushState) {
                    try {
                        window.history.pushState({ page: page, url: targetUrl }, '', targetUrl);
                    } catch(e) {}
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Employees AJAX filter error:', err);
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

        // Search inputs with debounce
        const sInput = document.getElementById('searchInput');
        if (sInput) {
            sInput.addEventListener('input', function() {
                const val = this.value;
                const mInput = document.getElementById('mvSearchInput');
                if (mInput && mInput.value !== val) mInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterEmployeesAjax(1), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterEmployeesAjax(1);
                }
            });
        }

        const mInput = document.getElementById('mvSearchInput');
        if (mInput) {
            mInput.addEventListener('input', function() {
                const val = this.value;
                if (sInput && sInput.value !== val) sInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterEmployeesAjax(1), 280);
            });
            mInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterEmployeesAjax(1);
                }
            });
        }

        // Clear buttons
        function clearSearch() {
            if (sInput) sInput.value = '';
            if (mInput) mInput.value = '';
            filterEmployeesAjax(1);
        }
        const sClear = document.getElementById('searchClearBtn');
        if (sClear) sClear.addEventListener('click', clearSearch);
        const mClear = document.getElementById('mvSearchClearBtn');
        if (mClear) mClear.addEventListener('click', clearSearch);

        // Dropdowns
        const stInput = document.getElementById('statusInput');
        if (stInput) stInput.addEventListener('change', () => filterEmployeesAjax(1));
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) ppInput.addEventListener('change', () => filterEmployeesAjax(1));
        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) mvPpSelect.addEventListener('change', () => {
            if (ppInput) ppInput.value = mvPpSelect.value;
            filterEmployeesAjax(1);
        });

        // Apply & Refresh
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) applyBtn.addEventListener('click', () => filterEmployeesAjax(1));
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterEmployeesAjax(1);
        });

        // Live Dynamic KPI Cards and Mobile Pills Syncer (AJAX Real-Time Update)
        function updateEmployeeMetrics(metrics) {
            if (!metrics) return;
            const grid = document.getElementById('employeesKpiGrid');
            if (grid) {
                const totalEl = grid.querySelector('.booking-kpi-card[data-kpi="total"] .booking-kpi-val, .booking-kpi-card[data-status=""][data-department=""] .booking-kpi-val');
                if (totalEl && metrics.total !== undefined) {
                    totalEl.setAttribute('data-count', metrics.total);
                    totalEl.textContent = (parseInt(metrics.total, 10) || 0).toLocaleString();
                }

                const activeEl = grid.querySelector('.booking-kpi-card[data-kpi="active"] .booking-kpi-val, .booking-kpi-card[data-status="active"] .booking-kpi-val');
                if (activeEl && metrics.active !== undefined) {
                    activeEl.setAttribute('data-count', metrics.active);
                    activeEl.textContent = (parseInt(metrics.active, 10) || 0).toLocaleString();
                }

                const onLeaveEl = grid.querySelector('.booking-kpi-card[data-kpi="on_leave"] .booking-kpi-val, .booking-kpi-card[data-status="on_leave"] .booking-kpi-val');
                if (onLeaveEl && metrics.on_leave !== undefined) {
                    onLeaveEl.setAttribute('data-count', metrics.on_leave);
                    onLeaveEl.textContent = (parseInt(metrics.on_leave, 10) || 0).toLocaleString();
                }

                const chaufEl = grid.querySelector('.booking-kpi-card[data-kpi="chauffeurs"] .booking-kpi-val, .booking-kpi-card[data-department="fleet_chauffeur"] .booking-kpi-val');
                if (chaufEl && metrics.chauffeurs !== undefined) {
                    chaufEl.setAttribute('data-count', metrics.chauffeurs);
                    chaufEl.textContent = (parseInt(metrics.chauffeurs, 10) || 0).toLocaleString();
                }

                animateKpiNumbers(grid);
            }

            const scrollWrap = document.getElementById('employeesMvStatusScroll');
            if (scrollWrap) {
                const pillAll = scrollWrap.querySelector('.mv-pill[data-status=""] .mv-pill-cnt');
                if (pillAll && metrics.total !== undefined) pillAll.textContent = (parseInt(metrics.total, 10) || 0).toLocaleString();

                const pillAct = scrollWrap.querySelector('.mv-pill[data-status="active"] .mv-pill-cnt');
                if (pillAct && metrics.active !== undefined) pillAct.textContent = (parseInt(metrics.active, 10) || 0).toLocaleString();

                const pillLeave = scrollWrap.querySelector('.mv-pill[data-status="on_leave"] .mv-pill-cnt');
                if (pillLeave && metrics.on_leave !== undefined) pillLeave.textContent = (parseInt(metrics.on_leave, 10) || 0).toLocaleString();

                const pillTerm = scrollWrap.querySelector('.mv-pill[data-status="terminated"] .mv-pill-cnt');
                if (pillTerm && metrics.terminated !== undefined) pillTerm.textContent = (parseInt(metrics.terminated, 10) || 0).toLocaleString();
            }

            const totalPill = document.getElementById('totalRecordsPill');
            if (totalPill && metrics.total !== undefined) {
                totalPill.textContent = (parseInt(metrics.total, 10) || 0).toLocaleString();
            }
        }

        // Event delegation for dynamic clicks (KPI cards, Pills, Clear, Pagination)
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#employeesKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') || '';
                const dept = kpiCard.getAttribute('data-department') || '';
                activeDept = dept;
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#employeesKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#employeesMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterEmployeesAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                activeDept = '';
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                } else {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                    if (stInput) stInput.value = '';
                }
                filterEmployeesAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#employeesMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                activeDept = '';
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#employeesMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#employeesKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st && !c.getAttribute('data-department'));
                });

                filterEmployeesAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#employeesPaginationWrapper .pagination a');
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
                filterEmployeesAjax(targetPage);

                const sec = document.getElementById('employeesSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#employeesKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterEmployeesAjax(1);
        });

        window.updateEmployeeMetrics = updateEmployeeMetrics;
        window.filterEmployeesAjax = filterEmployeesAjax;
        window.initEmployeeAjaxFilter = initEmployeeAjaxFilter;
    })();

    // Action popups
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

    function toggleEmployeeStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'on_leave' : 'active';
        const empName = btn.getAttribute('data-name') || 'Employee';

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/employees/') ?>' + id + '/toggle-status';
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
                'csrf_token': csrfToken,
                'status': targetStatus
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
                        b.title = 'Click to toggle status (currently On Leave)';
                    }
                });

                // Update table status pill & dot
                const wrap = document.getElementById('employeeToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    const pillClass = isNowActive ? 'completed' : 'assigned';
                    if (pill) pill.className = 'cust-toggle-pill ' + pillClass;
                    if (dot) dot.className = 'toggle-pulse-dot ' + pillClass;
                    if (text) text.textContent = data.label;
                }

                // Update mobile cards
                document.querySelectorAll('.mv-booking-card-wrapper').forEach(card => {
                    const mBtn = card.querySelector(`.cust-status-toggle[data-id="${id}"]`);
                    if (mBtn) {
                        const mText = card.querySelector('.mv-toggle-label-text');
                        if (mText) {
                            mText.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'assigned');
                            mText.textContent = data.label;
                        }
                    }
                });

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateEmployeeMetrics === 'function') {
                    window.updateEmployeeMetrics(data.metrics);
                }

                // If currently filtered by active/on_leave and employee was toggled away,
                // smoothly refresh view after grace period so the active list stays in sync
                const currentFilter = (document.getElementById('statusInput') ? document.getElementById('statusInput').value : '').trim();
                if (currentFilter && ((currentFilter === 'active' && !isNowActive) || (currentFilter === 'on_leave' && isNowActive))) {
                    setTimeout(() => {
                        if (typeof window.filterEmployeesAjax === 'function') {
                            const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                            window.filterEmployeesAjax(pg, { skipPushState: true });
                        }
                    }, 650);
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Employee '${empName}' status set to ${data.label}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to update employee status.', 'error');
                } else {
                    alert(data.message || 'Failed to update employee status.');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error toggling employee status', 'error');
            } else {
                alert(err.message || 'Error toggling employee status');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-action-buttons') && !e.target.closest('.mv-card-actions')) {
            document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

    /* ========================================================================= */
    /* Two-Step Delete Modals (Matching customer/index design)                   */
    /* ========================================================================= */
    let currentDeleteId = null;
    let currentDeleteName = '';

    function openDeleteModal(id, name, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        currentDeleteId = id;
        currentDeleteName = name || 'this employee';

        const backdrop = document.getElementById('deleteModalBackdrop');
        const step1 = document.getElementById('deleteStep1Modal');
        const step2 = document.getElementById('deleteStep2Modal');
        const chk = document.getElementById('deleteStep1Checkbox');
        const btn1 = document.getElementById('deleteStep1Btn');
        const chkWrap = document.getElementById('delCheckboxContainer');
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');
        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        const finalBtnText = document.getElementById('deleteFinalBtnText');

        if (chk) chk.checked = false;
        if (btn1) btn1.classList.add('disabled');
        if (chkWrap) chkWrap.classList.remove('shake-error');
        if (chk2) chk2.checked = false;
        if (chkWrap2) chkWrap2.classList.remove('shake-error');
        if (finalBtn) {
            finalBtn.disabled = false;
            finalBtn.classList.add('disabled');
            finalBtn.style.opacity = '';
        }
        if (finalBtnText) finalBtnText.textContent = 'Delete';

        if (step1) step1.style.display = 'block';
        if (step2) step2.style.display = 'none';

        if (backdrop) {
            backdrop.style.display = 'flex';
            setTimeout(() => {
                backdrop.classList.add('active');
            }, 10);
        }
    }

    function closeDeleteModal() {
        const backdrop = document.getElementById('deleteModalBackdrop');
        if (!backdrop) return;
        backdrop.classList.remove('active');
        setTimeout(() => {
            backdrop.style.display = 'none';
            const step1 = document.getElementById('deleteStep1Modal');
            const step2 = document.getElementById('deleteStep2Modal');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
            currentDeleteId = null;
            currentDeleteName = '';
        }, 220);
    }

    function handleBackdropClick(e) {
        if (e.target && e.target.id === 'deleteModalBackdrop') {
            closeDeleteModal();
        }
    }

    function toggleStep1DeleteBtn() {
        const chk = document.getElementById('deleteStep1Checkbox');
        const btn = document.getElementById('deleteStep1Btn');
        const chkWrap = document.getElementById('delCheckboxContainer');
        if (chkWrap) chkWrap.classList.remove('shake-error');

        if (chk && btn) {
            if (chk.checked) {
                btn.classList.remove('disabled');
            } else {
                btn.classList.add('disabled');
            }
        }
    }

    function toggleStep2DeleteBtn() {
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const btn2 = document.getElementById('deleteConfirmFinalBtn');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');
        if (chkWrap2) chkWrap2.classList.remove('shake-error');

        if (chk2 && btn2) {
            if (chk2.checked) {
                btn2.classList.remove('disabled');
            } else {
                btn2.classList.add('disabled');
            }
        }
    }

    function goToStep2Modal() {
        const chk = document.getElementById('deleteStep1Checkbox');
        const chkWrap = document.getElementById('delCheckboxContainer');

        if (!chk || !chk.checked) {
            if (chkWrap) {
                chkWrap.classList.add('shake-error');
                setTimeout(() => chkWrap.classList.remove('shake-error'), 450);
            }
            return;
        }

        const step1 = document.getElementById('deleteStep1Modal');
        const step2 = document.getElementById('deleteStep2Modal');
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        if (chk2) chk2.checked = false;
        if (finalBtn) finalBtn.classList.add('disabled');

        if (step1 && step2) {
            step1.style.display = 'none';
            step2.style.display = 'block';
        }
    }

    function confirmDeleteFinal() {
        if (!currentDeleteId) return;

        const chk2 = document.getElementById('deleteStep2Checkbox');
        const chkWrap2 = document.getElementById('delCheckboxContainer2');

        if (!chk2 || !chk2.checked) {
            if (chkWrap2) {
                chkWrap2.classList.add('shake-error');
                setTimeout(() => chkWrap2.classList.remove('shake-error'), 450);
            }
            return;
        }

        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        const btnText = document.getElementById('deleteFinalBtnText');
        if (finalBtn) {
            finalBtn.disabled = true;
            finalBtn.style.opacity = '0.75';
            if (btnText) btnText.textContent = 'Deleting...';
        }

        const deleteUrl = '<?= url('/office/employees/') ?>' + currentDeleteId + '/delete';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(deleteUrl, {
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
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Employee deleted successfully.', 'success');
                }
                if (data.metrics && typeof window.updateEmployeeMetrics === 'function') {
                    window.updateEmployeeMetrics(data.metrics);
                }
                if (typeof window.filterEmployeesAjax === 'function') {
                    const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                    window.filterEmployeesAjax(pg, { skipPushState: true });
                } else {
                    window.location.reload();
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to delete employee.', 'error');
                } else {
                    alert(data.message || 'Failed to delete employee.');
                }
                closeDeleteModal();
            }
        })
        .catch(err => {
            // Fallback to standard form post
            const form = document.getElementById('deleteEmployeeForm');
            if (form) {
                form.action = deleteUrl;
                form.submit();
            } else {
                alert('An error occurred while deleting employee record.');
                closeDeleteModal();
            }
        });
    }

    // Support keyboard ESC to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const backdrop = document.getElementById('deleteModalBackdrop');
            if (backdrop && backdrop.style.display !== 'none') {
                closeDeleteModal();
            }
        }
    });
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
    justify-content: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease;
}
.cust-status-toggle:hover {
    transform: scale(1.06);
}
.cust-status-toggle:active {
    transform: scale(0.94);
}
.toggle-slider-track {
    width: 44px;
    height: 24px;
    border-radius: 9999px;
    background: #CBD5E1;
    position: relative;
    display: block;
    transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}
.cust-status-toggle.is-active .toggle-slider-track {
    background: #10B981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}
.toggle-slider-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    position: absolute;
    left: 3px;
    top: 3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}
.cust-status-toggle:active:not(.is-loading) .toggle-slider-thumb {
    width: 21px;
}
.cust-status-toggle.sm .toggle-slider-track {
    width: 36px;
    height: 20px;
}
.cust-status-toggle.sm .toggle-slider-thumb {
    width: 14px;
    height: 14px;
    left: 3px;
    top: 3px;
}
.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}
.toggle-spinner {
    font-size: 8px;
    color: #059669;
    line-height: 1;
}
.cust-status-toggle:not(.is-active) .toggle-spinner {
    color: #64748B;
}
.toggle-shake {
    animation: toggleShakeAnim 0.35s ease;
}
@keyframes toggleShakeAnim {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
.cust-toggle-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    transition: all 0.2s ease;
}
.cust-toggle-pill.completed, .cust-toggle-pill.active {
    background: #DCFCE7;
    color: #15803D;
}
.cust-toggle-pill.assigned, .cust-toggle-pill.on_leave {
    background: #FEF3C7;
    color: #B45309;
}
.cust-toggle-pill.cancelled, .cust-toggle-pill.terminated {
    background: #F1F5F9;
    color: #64748B;
}
.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.toggle-pulse-dot.completed, .toggle-pulse-dot.active {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}
.toggle-pulse-dot.assigned, .toggle-pulse-dot.on_leave {
    background: #F59E0B;
}
.toggle-pulse-dot.cancelled, .toggle-pulse-dot.terminated {
    background: #94A3B8;
}
.mv-status-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.mv-toggle-label-text {
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: capitalize;
}
.mv-toggle-label-text.completed, .mv-toggle-label-text.active {
    color: #10B981;
}
.mv-toggle-label-text.assigned, .mv-toggle-label-text.on_leave {
    color: #D97706;
}
.mv-toggle-label-text.cancelled, .mv-toggle-label-text.terminated {
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
    box-shadow: 0 10px 25px rgba(23, 107, 91, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
    border-color: rgba(23, 107, 91, 0.25);
}
.booking-kpi-card.is-selected {
    border-color: #176B5B;
    background: linear-gradient(180deg, #FFFFFF 0%, #E8F7F0 100%);
    box-shadow: 0 4px 18px rgba(23, 107, 91, 0.18);
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
    color: #176B5B;
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
}
.fbc-search-clear:hover, .mv-search-clear:hover {
    color: #EF4444;
}
.cust-serial-num {
    font-weight: 700;
    color: #64748B;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 3px 8px;
    display: inline-block;
    font-size: 0.82rem;
}
/* Customer-grade Address Cell Styles */
.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #334155;
    max-width: 250px;
}
.cust-address-line {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    font-weight: 600;
    color: #0F172A;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}
.cust-address-icon {
    font-size: 0.78rem;
    color: #EF4444;
    margin-top: 3px;
    flex-shrink: 0;
}
.cust-address-sub {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.3;
}
.cust-no-address {
    font-size: 0.8rem;
    color: #94A3B8;
    font-style: italic;
}
.cust-full-name:hover {
    color: var(--brand-primary, #176B5B) !important;
}
/* Profile Avatar & Monogram matching customers/index */
.cust-table-avatar-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 44px;
    height: 44px;
}
.cust-table-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.18);
    background: #E8F7F0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cust-table-avatar-img:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.3);
}
.cust-table-monogram {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    text-transform: uppercase;
}

/* ========================================================================= */
/* Two-Step Delete Modals (Matching customer/index design)                   */
/* ========================================================================= */
.vg-del-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

.vg-del-backdrop.active {
    opacity: 1;
}

.vg-del-modal {
    position: relative;
    width: 100%;
    max-width: 530px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.96) 65%, rgba(240, 247, 255, 0.94) 100%);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border-radius: 26px;
    border: 1.5px solid rgba(255, 255, 255, 0.95);
    box-shadow: 
        0 24px 50px -12px rgba(15, 23, 42, 0.24),
        0 8px 18px -4px rgba(15, 23, 42, 0.12),
        0 0 0 1px rgba(226, 232, 240, 0.6) inset;
    padding: 34px 38px 30px 38px;
    transform: scale(0.93) translateY(10px);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease;
    overflow: hidden;
}

.vg-del-modal::before {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -40px;
    width: 340px;
    height: 180px;
    background: radial-gradient(ellipse at center, rgba(186, 230, 253, 0.5) 0%, rgba(224, 242, 254, 0.25) 55%, transparent 75%);
    pointer-events: none;
    border-radius: 50%;
    z-index: 0;
}

.vg-del-backdrop.active .vg-del-modal {
    transform: scale(1) translateY(0);
}

.vg-del-close-btn {
    position: absolute;
    top: 20px;
    right: 22px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #64748B;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 3;
}

.vg-del-close-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.vg-del-body {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    gap: 22px;
}

.vg-del-icon-halo {
    position: relative;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(254, 205, 211, 0.95) 0%, rgba(255, 228, 230, 0.6) 55%, rgba(255, 241, 242, 0) 100%);
    box-shadow: 0 0 26px rgba(244, 63, 94, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.vg-del-icon-halo.warning {
    box-shadow: 0 0 28px rgba(244, 63, 94, 0.4);
}

.vg-del-icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(145deg, #FF4D6D 0%, #EF233C 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 1.3rem;
    box-shadow: 0 6px 16px rgba(239, 35, 60, 0.42);
}

.vg-del-warning-triangle {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 6px 14px rgba(239, 35, 60, 0.45));
}

.vg-del-content {
    flex: 1;
    min-width: 0;
}

.vg-del-title {
    font-size: 1.62rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 6px 0;
    line-height: 1.2;
    letter-spacing: -0.01em;
}

.vg-del-desc {
    font-size: 0.95rem;
    color: #64748B;
    line-height: 1.5;
    margin: 0 0 16px 0;
}

.vg-del-checkbox-wrap {
    margin-top: 14px;
    padding: 10px 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.vg-del-checkbox-wrap:hover {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.vg-del-checkbox-wrap.shake-error {
    border-color: #EF4444;
    background: #FEF2F2;
    animation: vgDelShake 0.4s ease;
}

@keyframes vgDelShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
}

.vg-del-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
    margin: 0;
}

.vg-del-checkbox-input {
    display: none;
}

.vg-del-checkbox-box {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 1.8px solid #94A3B8;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 0.72rem;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.vg-del-checkbox-input:checked + .vg-del-checkbox-box {
    background: #EF4444;
    border-color: #EF4444;
}

.vg-del-checkbox-text {
    font-size: 0.88rem;
    color: #334155;
    font-weight: 500;
}

.vg-del-actions {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 14px;
    margin-top: 26px;
}

.vg-del-btn-cancel {
    border-radius: 9999px;
    padding: 10px 26px;
    border: 1.5px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.vg-del-btn-cancel:hover {
    background: #F8FAFC;
    border-color: #94A3B8;
    color: #0F172A;
}

.vg-del-btn-delete {
    border-radius: 9999px;
    padding: 10px 28px;
    border: none;
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    color: #FFFFFF;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.42);
    transition: all 0.2s ease;
}

.vg-del-btn-delete:hover:not(.disabled) {
    background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.52);
    transform: translateY(-1px);
}

.vg-del-btn-delete.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}

@media (max-width: 640px) {
    .vg-del-backdrop {
        padding: 16px !important;
        background: rgba(15, 23, 42, 0.55) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }
    .vg-del-modal {
        width: calc(100% - 24px) !important;
        max-width: 370px !important;
        margin: 0 auto !important;
        padding: 24px 20px 20px 20px !important;
        border-radius: 22px !important;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.96) 0%, rgba(240, 247, 255, 0.92) 55%, rgba(224, 242, 254, 0.88) 100%) !important;
        backdrop-filter: blur(24px) !important;
        -webkit-backdrop-filter: blur(24px) !important;
        border: 1px solid rgba(255, 255, 255, 0.85) !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.6) inset !important;
        overflow: hidden !important;
    }
    .vg-del-modal::before {
        content: '' !important;
        position: absolute !important;
        bottom: -35px !important;
        left: -25px !important;
        width: 220px !important;
        height: 110px !important;
        background: radial-gradient(ellipse at center, rgba(186, 230, 253, 0.65) 0%, rgba(219, 234, 254, 0.35) 50%, transparent 75%) !important;
        border-radius: 50% !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }
    .vg-del-close-btn {
        top: 14px !important;
        right: 14px !important;
        width: 28px !important;
        height: 28px !important;
        font-size: 0.95rem !important;
        color: #64748B !important;
    }
    .vg-del-body {
        gap: 14px !important;
        align-items: flex-start !important;
    }
    .vg-del-icon-halo {
        width: 54px !important;
        height: 54px !important;
        flex-shrink: 0 !important;
        background: radial-gradient(circle, rgba(254, 205, 211, 0.95) 0%, rgba(255, 228, 230, 0.55) 60%, rgba(255, 241, 242, 0) 100%) !important;
        box-shadow: 0 0 20px rgba(244, 63, 94, 0.3) !important;
    }
    .vg-del-icon-circle {
        width: 42px !important;
        height: 42px !important;
        font-size: 1.15rem !important;
        background: linear-gradient(145deg, #FF4D6D 0%, #EF233C 100%) !important;
        box-shadow: 0 4px 12px rgba(239, 35, 60, 0.35) !important;
    }
    .vg-del-icon-halo.warning {
        background: radial-gradient(circle, rgba(254, 205, 211, 0.9) 0%, rgba(255, 228, 230, 0.5) 60%, rgba(255, 241, 242, 0) 100%) !important;
        box-shadow: 0 0 22px rgba(244, 63, 94, 0.35) !important;
    }
    .vg-del-warning-triangle {
        width: 42px !important;
        height: 42px !important;
        filter: drop-shadow(0 4px 10px rgba(239, 35, 60, 0.4)) !important;
    }
    .vg-del-warning-triangle svg {
        width: 28px !important;
        height: 28px !important;
    }
    .vg-del-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: #0F172A !important;
        margin: 0 0 4px 0 !important;
        line-height: 1.2 !important;
    }
    .vg-del-desc {
        font-size: 0.85rem !important;
        color: #475569 !important;
        line-height: 1.35 !important;
        margin: 0 !important;
    }
    /* Checkbox requirement styled compactly for mobile matching lg device */
    .vg-del-checkbox-wrap {
        display: block !important;
        margin-top: 10px !important;
        padding: 8px 10px !important;
        background: rgba(248, 250, 252, 0.9) !important;
        border: 1px solid #CBD5E1 !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
    }
    .vg-del-checkbox-wrap:hover {
        background: #F1F5F9 !important;
        border-color: #94A3B8 !important;
    }
    .vg-del-checkbox-wrap.shake-error {
        border-color: #EF4444 !important;
        background: #FEF2F2 !important;
    }
    .vg-del-checkbox-label {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        margin: 0 !important;
    }
    .vg-del-checkbox-box {
        width: 18px !important;
        height: 18px !important;
        border-radius: 5px !important;
        font-size: 0.68rem !important;
        flex-shrink: 0 !important;
    }
    .vg-del-checkbox-text {
        font-size: 0.78rem !important;
        color: #334155 !important;
        line-height: 1.3 !important;
        font-weight: 500 !important;
    }
    .vg-del-actions {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 10px !important;
        margin-top: 16px !important;
    }
    .vg-del-btn-cancel {
        padding: 7px 20px !important;
        font-size: 0.88rem !important;
        border-radius: 9999px !important;
        background: rgba(255, 255, 255, 0.8) !important;
        border: 1.5px solid rgba(203, 213, 225, 0.8) !important;
        color: #334155 !important;
        font-weight: 600 !important;
    }
    .vg-del-btn-delete {
        padding: 7px 20px !important;
        font-size: 0.88rem !important;
        border-radius: 9999px !important;
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
        color: #FFFFFF !important;
        border: none !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45) !important;
        gap: 6px !important;
        opacity: 1 !important;
        cursor: pointer !important;
    }
    .vg-del-btn-delete i {
        font-size: 0.82rem !important;
    }
    .vg-del-btn-delete.disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
}
</style>
