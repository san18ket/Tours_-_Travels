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

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Staff -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_staff', 'Total Staff')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalEmployees ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered staff personnel')) ?></span>
            </div>
        </div>

        <!-- Card 2: Active Employees -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.active_employees', 'Active Employees')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'On-duty & operational')) ?></span>
            </div>
        </div>

        <!-- Card 3: Fleet Chauffeurs -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.fleet_chauffeurs', 'Fleet Chauffeurs')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalChauffeurs ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Commercial drivers roster')) ?></span>
            </div>
        </div>

        <!-- Card 4: Operations & Dispatch -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.operations_dispatch', 'Operations & Dispatch')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalOperations ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.successfully_closed', 'Fleet operations desk')) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/employees') ?>" class="filter-bar-compact" id="employeeFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by name, employee code, phone, designation..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="department" id="deptInput" onchange="document.getElementById('employeeFilterForm').submit()">
            <option value=""><?= e(__('common.all_departments', 'All Departments')) ?></option>
            <option value="operations" <?= ($selectedDepartment ?? '') === 'operations' ? 'selected' : '' ?>><?= e(__('common.operations', 'Operations')) ?></option>
            <option value="dispatch" <?= ($selectedDepartment ?? '') === 'dispatch' ? 'selected' : '' ?>><?= e(__('common.dispatch', 'Dispatch')) ?></option>
            <option value="fleet_chauffeur" <?= ($selectedDepartment ?? '') === 'fleet_chauffeur' ? 'selected' : '' ?>><?= e(__('common.fleet_chauffeur', 'Fleet Chauffeur')) ?></option>
            <option value="management" <?= ($selectedDepartment ?? '') === 'management' ? 'selected' : '' ?>><?= e(__('common.management', 'Management')) ?></option>
            <option value="administration" <?= ($selectedDepartment ?? '') === 'administration' ? 'selected' : '' ?>><?= e(__('common.administration', 'Administration')) ?></option>
            <option value="maintenance" <?= ($selectedDepartment ?? '') === 'maintenance' ? 'selected' : '' ?>><?= e(__('common.maintenance', 'Maintenance')) ?></option>
        </select>

        <select name="status" id="statusInput" onchange="document.getElementById('employeeFilterForm').submit()">
            <option value=""><?= e(__('common.all_statuses', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>><?= e(__('status.active', 'Active')) ?></option>
            <option value="on_leave" <?= ($selectedStatus ?? '') === 'on_leave' ? 'selected' : '' ?>><?= e(__('status.on_leave', 'On Leave')) ?></option>
            <option value="terminated" <?= ($selectedStatus ?? '') === 'terminated' ? 'selected' : '' ?>><?= e(__('status.terminated', 'Terminated')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('employeeFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/employees') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedDepartment) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/employees') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span>Clear</span></a>
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
                <h2 class="mv-page-title">Employees</h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/employees/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Add Staff</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Search Bar & Filter Drawer -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/employees') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search staff, code, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/employees' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($selectedDepartment)): ?>
                        <input type="hidden" name="department" value="<?= e($selectedDepartment) ?>">
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
                <form method="GET" action="<?= url('/office/employees') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvDeptSelect">Department:</label>
                        <select name="department" id="mvDeptSelect" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            <option value="operations" <?= ($selectedDepartment ?? '') === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="dispatch" <?= ($selectedDepartment ?? '') === 'dispatch' ? 'selected' : '' ?>>Dispatch</option>
                            <option value="fleet_chauffeur" <?= ($selectedDepartment ?? '') === 'fleet_chauffeur' ? 'selected' : '' ?>>Fleet Chauffeur</option>
                            <option value="management" <?= ($selectedDepartment ?? '') === 'management' ? 'selected' : '' ?>>Management</option>
                            <option value="maintenance" <?= ($selectedDepartment ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
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
            $makeStatusUrl = function(string $st) use ($search, $selectedDepartment, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($selectedDepartment)) $p['department'] = $selectedDepartment;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/employees' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)($totalEmployees ?? count($employees)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>">
                <span>Active</span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('on_leave') ?>" class="mv-pill <?= $currStatus === 'on_leave' ? 'active' : '' ?>">
                <span>On Leave</span>
            </a>
            <a href="<?= $makeStatusUrl('terminated') ?>" class="mv-pill <?= $currStatus === 'terminated' ? 'active' : '' ?>">
                <span>Terminated</span>
            </a>
        </div>
    </div>

    <!-- 5. All Staff Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
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
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 16%;">Staff Code</th>
                        <th style="width: 22%;">Employee & Role</th>
                        <th style="width: 18%;">Department</th>
                        <th style="width: 18%;">Contact Information</th>
                        <th class="text-center" style="width: 14%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeesTableBody">
                    <?php foreach ($employees as $i => $e):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($e['status'] ?? 'active');
                        $statusClass = match($status) {
                            'active' => 'completed',
                            'on_leave' => 'in-progress',
                            'terminated' => 'cancelled',
                            default => 'pending'
                        };
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:monospace;letter-spacing:-0.01em;">
                                <?= e($e['employee_code']) ?>
                            </div>
                            <div style="margin-top: 3px;">
                                <small class="text-muted" style="font-size:11px;">
                                    Joined <?= format_date($e['joining_date']) ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <div class="client-cell">
                                <div class="client-info">
                                    <span class="client-name"><?= e($e['first_name'] . ' ' . $e['last_name']) ?></span>
                                    <small class="text-muted d-block" style="font-size:12px;margin-top:2px;">
                                        <?= e($e['designation']) ?> &bull; <?= ucfirst(str_replace('_', ' ', $e['employment_type'])) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="display:inline-block;background:rgba(23,107,91,0.08);color:#16443B;border:1px solid rgba(23,107,91,0.15);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;">
                                <?= e(str_replace('_', ' ', $e['department'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">
                                <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($e['phone']) ?>
                            </div>
                            <?php if (!empty($e['email'])): ?>
                                <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                    <?= e($e['email']) ?>
                                </small>
                            <?php endif; ?>
                            <?php if (!empty($e['linked_username'])): ?>
                                <small style="display:inline-block;margin-top:2px;font-size:11px;color:#10b981;font-weight:600;">
                                    @<?= e($e['linked_username']) ?> (<?= e($e['linked_role']) ?>)
                                </small>
                            <?php endif; ?>
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
                                    <a href="<?= url('/office/employees/' . $e['id']) ?>" class="action-btn-table view" title="View Profile">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (auth_role() === 'admin'): ?>
                                    <a href="<?= url('/office/employees/' . $e['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Employee">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
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

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="employeesCards">
            <?php foreach ($employees as $e):
                $status = strtolower($e['status'] ?? 'active');
                $statusClass = $status === 'active' ? 'active' : ($status === 'on_leave' ? 'pending' : 'cancelled');
                $statusText = ucfirst(str_replace('_', ' ', $status));
                $initials = strtoupper(mb_substr($e['first_name'] ?? 'E', 0, 1) . mb_substr($e['last_name'] ?? '', 0, 1));
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/employees/' . $e['id']) ?>" class="mv-booking-card">
                    <!-- Left: Staff Monogram Avatar -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <?= e($initials) ?>
                        </div>
                    </div>

                    <!-- Middle: Code, Name, Designation, Department, Phone -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($e['employee_code']) ?></div>
                        <div class="mv-tour-title" title="<?= e($e['first_name'] . ' ' . $e['last_name']) ?>">
                            <?= e($e['first_name'] . ' ' . $e['last_name']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-briefcase"></i>
                            <span><?= e($e['designation']) ?> &bull; <?= e(str_replace('_', ' ', $e['department'])) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e($e['phone']) ?></span>
                        </div>
                        <?php if (!empty($e['linked_username'])): ?>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-user-check"></i>
                            <span>@<?= e($e['linked_username']) ?> (<?= e($e['linked_role']) ?>)</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Status pill top, Employment type bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price" style="font-size: 11px; color: #64748b; font-weight: 600;">
                            <?= ucfirst(str_replace('_', ' ', $e['employment_type'] ?? 'full_time')) ?>
                        </div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
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

    <!-- Modern Pagination (Exact Bookings Architecture) -->
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
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> staff member(s)</div>
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
</script>
