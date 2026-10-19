<!-- Header Ribbon -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0 0 0.25rem 0;">Employee Directory</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Comprehensive staff records, operational departments, and commercial chauffeur rosters.</p>
    </div>
    <?php if (auth_role() === 'admin'): ?>
    <div>
        <a href="<?= url('/office/employees/create') ?>" class="btn btn-navy" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Employee</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- KPI Metrics Ribbon -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon info" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--vg-blue-500); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Total Staff</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalEmployees) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon success" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--vg-success); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Active Employees</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalActive) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon warning" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: var(--vg-warning); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Fleet Chauffeurs</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalChauffeurs) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon purple" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8B5CF6; display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Operations &amp; Dispatch</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalOperations) ?></div>
        </div>
    </div>
</div>

<!-- Search & Filter Controls -->
<div class="vg-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="<?= url('/office/employees') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 220px; position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Search by name, employee code, phone, designation..." value="<?= e($search) ?>" style="padding-left: 2.25rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="department" class="form-select" style="flex: 1; min-width: 170px;" onchange="this.form.submit()">
                <option value="">All Departments</option>
                <option value="operations" <?= $selectedDepartment === 'operations' ? 'selected' : '' ?>>Operations</option>
                <option value="dispatch" <?= $selectedDepartment === 'dispatch' ? 'selected' : '' ?>>Dispatch</option>
                <option value="fleet_chauffeur" <?= $selectedDepartment === 'fleet_chauffeur' ? 'selected' : '' ?>>Fleet Chauffeur</option>
                <option value="management" <?= $selectedDepartment === 'management' ? 'selected' : '' ?>>Management</option>
                <option value="administration" <?= $selectedDepartment === 'administration' ? 'selected' : '' ?>>Administration</option>
                <option value="maintenance" <?= $selectedDepartment === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>

            <select name="status" class="form-select" style="flex: 1; min-width: 130px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="on_leave" <?= $selectedStatus === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                <option value="terminated" <?= $selectedStatus === 'terminated' ? 'selected' : '' ?>>Terminated</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select name="per_page" class="form-select" style="width: 110px;" onchange="this.form.submit()">
                <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
            </select>

            <button type="submit" class="btn btn-navy btn-sm" title="Apply Filters">Filter</button>
            <?php if (!empty($search) || !empty($selectedDepartment) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/employees') ?>" class="btn btn-outline btn-sm" title="Clear Filters">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Employee List Grid -->
<div class="vg-card">
    <?php if (empty($employees)): ?>
        <div style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👔</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.5rem;">No Employee Records Found</h3>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem auto;">
                <?= (!empty($search) || !empty($selectedDepartment) || !empty($selectedStatus)) ? 'No employees match your filter criteria. Try resetting your search filters.' : 'Your employee directory is empty. Register your first staff member.' ?>
            </p>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/employees/create') ?>" class="btn btn-navy">
                + Register First Employee
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Contact</th>
                        <th>System Account</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $e): ?>
                        <tr>
                            <td>
                                <span class="badge badge-neutral" style="font-family: monospace; font-size: 0.85rem; font-weight: 700; background: var(--vg-pearl-100); color: var(--vg-navy-900); border: 1px solid var(--vg-pearl-200);">
                                    <?= e($e['employee_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--vg-navy-900);">
                                    <?= e($e['first_name'] . ' ' . $e['last_name']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--vg-text-muted);">
                                    Joined <?= format_date($e['joining_date']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-navy" style="font-size: 0.78rem; text-transform: capitalize;">
                                    <?= e(str_replace('_', ' ', $e['department'])) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--vg-text-secondary); font-size: 0.9rem;">
                                    <?= e($e['designation']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted); text-transform: capitalize;">
                                    <?= e(str_replace('_', ' ', $e['employment_type'])) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--vg-navy-900);"><?= e($e['phone']) ?></div>
                                <?php if (!empty($e['email'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--vg-text-muted);"><?= e($e['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($e['linked_username'])): ?>
                                    <span class="badge badge-success" style="font-size: 0.75rem;">
                                        @<?= e($e['linked_username']) ?> (<?= e($e['linked_role']) ?>)
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.78rem; color: var(--vg-text-muted);">No login account</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= render_status_badge($e['status']) ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                    <a href="<?= url('/office/employees/' . $e['id']) ?>" class="btn btn-outline btn-sm" title="View Profile">Inspect</a>
                                    <?php if (auth_role() === 'admin'): ?>
                                    <a href="<?= url('/office/employees/' . $e['id'] . '/edit') ?>" class="btn btn-outline btn-sm" title="Edit Employee">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="vg-mobile-cards" style="padding: 1rem;">
            <?php foreach ($employees as $e): ?>
                <div class="vg-record-card">
                    <div class="vg-card-row-header">
                        <div>
                            <span class="badge badge-neutral" style="font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                                <?= e($e['employee_code']) ?>
                            </span>
                            <div style="font-weight: 800; font-size: 1.05rem; color: var(--vg-navy-900); margin-top: 0.25rem;">
                                <?= e($e['first_name'] . ' ' . $e['last_name']) ?>
                            </div>
                        </div>
                        <div>
                            <?= render_status_badge($e['status']) ?>
                        </div>
                    </div>
                    <div class="vg-card-grid">
                        <div class="vg-card-field">
                            <span class="vg-card-label">Department</span>
                            <span class="vg-card-value" style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $e['department'])) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Designation</span>
                            <span class="vg-card-value"><?= e($e['designation']) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Mobile Phone</span>
                            <span class="vg-card-value"><?= e($e['phone']) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">System Account</span>
                            <span class="vg-card-value"><?= !empty($e['linked_username']) ? '@' . e($e['linked_username']) : 'Unlinked' ?></span>
                        </div>
                    </div>
                    <div class="vg-card-actions">
                        <a href="<?= url('/office/employees/' . $e['id']) ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">View Profile</a>
                        <?php if (auth_role() === 'admin'): ?>
                        <a href="<?= url('/office/employees/' . $e['id'] . '/edit') ?>" class="btn btn-navy btn-sm" style="flex: 1; text-align: center;">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Server-side Pagination Ribbon -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-top: 1px solid var(--vg-pearl-200); flex-wrap: wrap; gap: 0.75rem;">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalEmployees) ?> total staff)
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('/office/employees?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="btn btn-outline btn-sm">‹ Previous</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="<?= url('/office/employees?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn <?= $p === $currentPage ? 'btn-navy' : 'btn-outline' ?> btn-sm"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('/office/employees?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="btn btn-outline btn-sm">Next ›</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
