<!-- Header Ribbon -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0 0 0.25rem 0;">Commercial Vehicles</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Fleet readiness status, passenger seating capacities, fuel classifications, and compliance schedules.</p>
    </div>
    <?php if (auth_role() === 'admin'): ?>
    <div>
        <a href="<?= url('/office/vehicles/create') ?>" class="btn btn-navy" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Vehicle</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- KPI Metrics Ribbon -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon info" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--vg-blue-500); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Total Fleet</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalVehicles) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon success" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--vg-success); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Available for Duty</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalAvailable) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon purple" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8B5CF6; display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">On Active Trip</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalOnTrip) ?></div>
        </div>
    </div>

    <div class="vg-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon warning" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: var(--vg-warning); display: flex; align-items: center; justify-content: center;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--vg-text-muted); font-weight: 600; text-transform: uppercase;">Maintenance</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);"><?= number_format($totalMaintenance) ?></div>
        </div>
    </div>
</div>

<!-- Search & Filter Controls -->
<div class="vg-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="<?= url('/office/vehicles') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 220px; position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Search by registration plate or make & model..." value="<?= e($search) ?>" style="padding-left: 2.25rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--vg-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="type" class="form-select" style="flex: 1; min-width: 160px;" onchange="this.form.submit()">
                <option value="">All Vehicle Types</option>
                <option value="sedan" <?= $selectedType === 'sedan' ? 'selected' : '' ?>>Luxury Sedan</option>
                <option value="suv" <?= $selectedType === 'suv' ? 'selected' : '' ?>>Premium SUV</option>
                <option value="tempo_traveller" <?= $selectedType === 'tempo_traveller' ? 'selected' : '' ?>>Tempo Traveller</option>
                <option value="luxury_bus" <?= $selectedType === 'luxury_bus' ? 'selected' : '' ?>>Luxury Coach / Bus</option>
                <option value="van" <?= $selectedType === 'van' ? 'selected' : '' ?>>Executive Van</option>
            </select>

            <select name="status" class="form-select" style="flex: 1; min-width: 140px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="available" <?= $selectedStatus === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="on_trip" <?= $selectedStatus === 'on_trip' ? 'selected' : '' ?>>On Trip</option>
                <option value="maintenance" <?= $selectedStatus === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                <option value="retired" <?= $selectedStatus === 'retired' ? 'selected' : '' ?>>Retired</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select name="per_page" class="form-select" style="width: 110px;" onchange="this.form.submit()">
                <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
            </select>

            <button type="submit" class="btn btn-navy btn-sm" title="Apply Filters">Filter</button>
            <?php if (!empty($search) || !empty($selectedType) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/vehicles') ?>" class="btn btn-outline btn-sm" title="Clear Filters">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Vehicle Grid -->
<div class="vg-card">
    <?php if (empty($vehicles)): ?>
        <div style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🚗</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.5rem;">No Commercial Vehicles Found</h3>
            <p style="color: var(--vg-text-secondary); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.5rem auto;">
                <?= (!empty($search) || !empty($selectedType) || !empty($selectedStatus)) ? 'No vehicles match your search filter criteria. Try resetting your query.' : 'Your fleet roster is empty. Register your first commercial vehicle.' ?>
            </p>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/vehicles/create') ?>" class="btn btn-navy">
                + Register First Vehicle
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Registration &amp; Model</th>
                        <th>Type &amp; Seating</th>
                        <th>Fuel Type</th>
                        <th>Odometer Reading</th>
                        <th>Compliance Expiry</th>
                        <th>Operational Status</th>
                        <th style="text-align: right;">Readiness Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.95rem;"><?= e($v['make_model']) ?></strong>
                                <div style="font-size: 0.82rem; color: var(--vg-blue-500); font-family: monospace; font-weight: 700;">
                                    <?= e($v['vehicle_number']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--vg-navy-900);">
                                    <?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    <?= (int)$v['seating_capacity'] ?> Passenger Seats
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-neutral" style="text-transform: uppercase;">
                                    <?= e($v['fuel_type']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--vg-navy-900);"><?= number_format((int)$v['odometer_km']) ?> km</strong>
                            </td>
                            <td>
                                <div style="font-size: 0.78rem; color: var(--vg-text-secondary);">
                                    Ins: <?= !empty($v['insurance_expiry']) ? format_date($v['insurance_expiry']) : '—' ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                                    Reg: <?= !empty($v['registration_expiry']) ? format_date($v['registration_expiry']) : '—' ?>
                                </div>
                            </td>
                            <td>
                                <?= render_status_badge($v['status']) ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($v['status'] !== 'on_trip'): ?>
                                    <form action="<?= url('/office/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="display: inline-block; margin: 0;">
                                        <?= csrf_field() ?>
                                        <?php if ($v['status'] === 'available'): ?>
                                            <input type="hidden" name="status" value="maintenance">
                                            <button type="submit" class="btn btn-outline btn-sm" title="Mark for Maintenance">Service</button>
                                        <?php else: ?>
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="btn btn-success btn-sm">Set Ready</button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.78rem; color: var(--vg-text-muted); font-weight: 600;">Active Trip Duty</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="vg-mobile-cards" style="padding: 1rem;">
            <?php foreach ($vehicles as $v): ?>
                <div class="vg-record-card">
                    <div class="vg-card-row-header">
                        <div>
                            <span class="badge badge-navy" style="font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                                <?= e($v['vehicle_number']) ?>
                            </span>
                            <div style="font-weight: 800; font-size: 1.05rem; color: var(--vg-navy-900); margin-top: 0.25rem;">
                                <?= e($v['make_model']) ?>
                            </div>
                        </div>
                        <div>
                            <?= render_status_badge($v['status']) ?>
                        </div>
                    </div>
                    <div class="vg-card-grid">
                        <div class="vg-card-field">
                            <span class="vg-card-label">Type</span>
                            <span class="vg-card-value"><?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?></span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Seating</span>
                            <span class="vg-card-value"><?= (int)$v['seating_capacity'] ?> Seats</span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Odometer</span>
                            <span class="vg-card-value"><?= number_format((int)$v['odometer_km']) ?> km</span>
                        </div>
                        <div class="vg-card-field">
                            <span class="vg-card-label">Fuel</span>
                            <span class="vg-card-value"><?= strtoupper(e($v['fuel_type'])) ?></span>
                        </div>
                    </div>
                    <div class="vg-card-actions">
                        <?php if ($v['status'] !== 'on_trip'): ?>
                            <form action="<?= url('/office/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="flex: 1; margin: 0;">
                                <?= csrf_field() ?>
                                <?php if ($v['status'] === 'available'): ?>
                                    <input type="hidden" name="status" value="maintenance">
                                    <button type="submit" class="btn btn-outline btn-block btn-sm">Mark for Maintenance</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="btn btn-success btn-block btn-sm">Set Ready for Duty</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; width: 100%; font-size: 0.82rem; color: var(--vg-text-muted); font-weight: 600;">
                                On Active Trip
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Ribbon -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-top: 1px solid var(--vg-pearl-200); flex-wrap: wrap; gap: 0.75rem;">
                <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
                    Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalVehicles) ?> vehicles)
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('/office/vehicles?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="btn btn-outline btn-sm">‹ Previous</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="<?= url('/office/vehicles?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn <?= $p === $currentPage ? 'btn-navy' : 'btn-outline' ?> btn-sm"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('/office/vehicles?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="btn btn-outline btn-sm">Next ›</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
