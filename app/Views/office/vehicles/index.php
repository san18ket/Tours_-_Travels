<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('vehicles.title', 'Commercial Fleet Vehicles'),
            'description' => __('vehicles.subtitle', 'Fleet readiness status, passenger seating capacities, fuel classifications, and compliance schedules.'),
            'image'       => 'header-vehicles.jpg',
            'badge'       => 'Commercial Fleet',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.vehicles', 'Vehicles'), 'url' => ''],
            ],
            'actions'     => (auth_role() === 'admin') ? '<a href="' . url('/office/vehicles/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.add_vehicle', 'Add Vehicle')) . '</span></a>' : null,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Fleet -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_fleet', 'Total Fleet')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalVehicles ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered fleet vehicles')) ?></span>
            </div>
        </div>

        <!-- Card 2: Available for Duty -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.available_for_duty', 'Available for Duty')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalAvailable ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'Ready for assignment')) ?></span>
            </div>
        </div>

        <!-- Card 3: On Active Trip -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.on_active_trip', 'On Active Trip')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalOnTrip ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Currently in transit')) ?></span>
            </div>
        </div>

        <!-- Card 4: In Maintenance -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('vehicles.maintenance', 'Maintenance')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalMaintenance ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.archived', 'Scheduled workshop service')) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/vehicles') ?>" class="filter-bar-compact" id="vehicleFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by registration plate, make & model..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="type" id="typeInput" onchange="document.getElementById('vehicleFilterForm').submit()">
            <option value=""><?= e(__('common.all_types', 'All Vehicle Types')) ?></option>
            <option value="sedan" <?= ($selectedType ?? '') === 'sedan' ? 'selected' : '' ?>><?= e(__('vehicles.luxury_sedan', 'Luxury Sedan')) ?></option>
            <option value="suv" <?= ($selectedType ?? '') === 'suv' ? 'selected' : '' ?>><?= e(__('vehicles.premium_suv', 'Premium SUV')) ?></option>
            <option value="tempo_traveller" <?= ($selectedType ?? '') === 'tempo_traveller' ? 'selected' : '' ?>><?= e(__('vehicles.tempo_traveller', 'Tempo Traveller')) ?></option>
            <option value="luxury_bus" <?= ($selectedType ?? '') === 'luxury_bus' ? 'selected' : '' ?>><?= e(__('vehicles.luxury_bus', 'Luxury Coach / Bus')) ?></option>
            <option value="van" <?= ($selectedType ?? '') === 'van' ? 'selected' : '' ?>><?= e(__('vehicles.executive_van', 'Executive Van')) ?></option>
        </select>

        <select name="status" id="statusInput" onchange="document.getElementById('vehicleFilterForm').submit()">
            <option value=""><?= e(__('common.all_statuses', 'All Statuses')) ?></option>
            <option value="available" <?= ($selectedStatus ?? '') === 'available' ? 'selected' : '' ?>><?= e(__('status.available', 'Available')) ?></option>
            <option value="on_trip" <?= ($selectedStatus ?? '') === 'on_trip' ? 'selected' : '' ?>><?= e(__('status.on_trip', 'On Trip')) ?></option>
            <option value="maintenance" <?= ($selectedStatus ?? '') === 'maintenance' ? 'selected' : '' ?>><?= e(__('status.maintenance', 'Maintenance')) ?></option>
            <option value="retired" <?= ($selectedStatus ?? '') === 'retired' ? 'selected' : '' ?>><?= e(__('status.retired', 'Retired')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('vehicleFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <a href="<?= url('/office/vehicles') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedType) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/vehicles') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></a>
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
                <h2 class="mv-page-title"><?= e(__('sidebar.vehicles', 'Vehicles')) ?></h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/vehicles/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span><?= e(__('sidebar.add_vehicle', 'Add Vehicle')) ?></span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/vehicles') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search plate, model..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/vehicles' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($selectedType)): ?>
                        <input type="hidden" name="type" value="<?= e($selectedType) ?>">
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
                <form method="GET" action="<?= url('/office/vehicles') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvTypeSelect">Vehicle Type:</label>
                        <select name="type" id="mvTypeSelect" onchange="this.form.submit()">
                            <option value="">All Vehicle Types</option>
                            <option value="sedan" <?= ($selectedType ?? '') === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="suv" <?= ($selectedType ?? '') === 'suv' ? 'selected' : '' ?>>SUV</option>
                            <option value="tempo_traveller" <?= ($selectedType ?? '') === 'tempo_traveller' ? 'selected' : '' ?>>Tempo Traveller</option>
                            <option value="luxury_bus" <?= ($selectedType ?? '') === 'luxury_bus' ? 'selected' : '' ?>>Coach / Bus</option>
                            <option value="van" <?= ($selectedType ?? '') === 'van' ? 'selected' : '' ?>>Executive Van</option>
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
            $makeStatusUrl = function(string $st) use ($search, $selectedType, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($selectedType)) $p['type'] = $selectedType;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/vehicles' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span><?= e(__('filter.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalVehicles ?? count($vehicles)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('available') ?>" class="mv-pill <?= $currStatus === 'available' ? 'active' : '' ?>">
                <span><?= e(__('status.available', 'Available')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalAvailable ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('on_trip') ?>" class="mv-pill <?= $currStatus === 'on_trip' ? 'active' : '' ?>">
                <span><?= e(__('status.on_trip', 'On Trip')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalOnTrip ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('maintenance') ?>" class="mv-pill <?= $currStatus === 'maintenance' ? 'active' : '' ?>">
                <span><?= e(__('status.maintenance', 'Maintenance')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalMaintenance ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Vehicles Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title"><?= e(__('sidebar.vehicles', 'All Fleet Vehicles')) ?></h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('table.total_records', 'Total Records:')) ?></span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalVehicles ?? count($vehicles)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="vehiclesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 22%;"><?= e(__('table.plate_number', 'Plate & Registration')) ?></th>
                        <th style="width: 20%;"><?= e(__('table.model', 'Make & Model')) ?></th>
                        <th style="width: 16%;"><?= e(__('table.type', 'Classification')) ?></th>
                        <th class="text-center" style="width: 14%;"><?= e(__('table.capacity', 'Capacity & Fuel')) ?></th>
                        <th class="text-center" style="width: 14%;"><?= e(__('table.odometer', 'Odometer')) ?></th>
                        <th class="text-center" style="width: 12%;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 55px;"><?= e(__('table.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody id="vehiclesTableBody">
                    <?php foreach ($vehicles as $i => $v):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($v['status'] ?? 'available');
                        $statusClass = match($status) {
                            'available' => 'completed',
                            'on_trip' => 'in-progress',
                            'maintenance' => 'assigned',
                            'retired' => 'cancelled',
                            default => 'pending'
                        };
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:monospace;letter-spacing:0.02em;font-weight:700;">
                                <?= e($v['vehicle_number']) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--dark, #1e293b); font-size: 0.95rem;">
                                <?= e($v['make_model']) ?>
                            </div>
                        </td>
                        <td>
                            <span style="display:inline-block;background:rgba(23,107,91,0.08);color:#16443B;border:1px solid rgba(23,107,91,0.15);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;">
                                <?= e(str_replace('_', ' ', $v['vehicle_type'])) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                <i class="fa-solid fa-users" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= (int)$v['seating_capacity'] ?> Seats
                            </div>
                            <small class="text-muted" style="font-size:11px;text-transform:uppercase;">
                                <?= e($v['fuel_type']) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:800;color:var(--brand-secondary, #16443B);font-size:13.5px;">
                                <?= number_format((int)$v['odometer_km']) ?> km
                            </div>
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
                                    <?php if ($v['status'] !== 'on_trip'): ?>
                                    <form action="<?= url('/office/fleet/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="margin:0;">
                                        <?= csrf_field() ?>
                                        <?php if ($v['status'] === 'available'): ?>
                                            <input type="hidden" name="status" value="maintenance">
                                            <button type="submit" class="action-btn-table edit" title="Set to Maintenance">
                                                <i class="fa-solid fa-wrench"></i>
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="action-btn-table view" title="Set Ready for Duty">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    <?php endif; ?>
                                    <a href="<?= url('/office/trips/assign?vehicle_id=' . $v['id']) ?>" class="action-btn-table dispatch" title="Assign to Trip">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No vehicles found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="vehiclesCards">
            <?php foreach ($vehicles as $v):
                $status = strtolower($v['status'] ?? 'available');
                $statusClass = match($status) {
                    'available' => 'available',
                    'on_trip' => 'on_trip',
                    'maintenance' => 'maintenance',
                    'retired' => 'retired',
                    default => 'pending'
                };
                $statusText = ucfirst(str_replace('_', ' ', $status));
            ?>
            <div class="mv-booking-card-wrapper">
                <div class="mv-booking-card">
                    <!-- Left: Vehicle Icon Avatar -->
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar">
                            <i class="fa-solid fa-van-shuttle" style="font-size:22px;"></i>
                        </div>
                    </div>

                    <!-- Middle: Plate, Model, Type, Seats, Odometer -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($v['vehicle_number']) ?></div>
                        <div class="mv-tour-title" title="<?= e($v['make_model']) ?>">
                            <?= e($v['make_model']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-car-side"></i>
                            <span><?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?> &bull; <?= strtoupper(e($v['fuel_type'])) ?></span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-users"></i>
                            <span><?= (int)$v['seating_capacity'] ?> Passenger Seats</span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span><?= number_format((int)$v['odometer_km']) ?> km recorded</span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Status toggle button bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price" style="font-size: 11.5px; color: #16443B;">
                            <?= number_format((int)$v['odometer_km']) ?> km
                        </div>
                    </div>
                </div>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <?php if ($v['status'] !== 'on_trip'): ?>
                        <form action="<?= url('/office/fleet/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="margin:0;">
                            <?= csrf_field() ?>
                            <?php if ($v['status'] === 'available'): ?>
                                <input type="hidden" name="status" value="maintenance">
                                <button type="submit" class="action-btn-table edit" title="Set to Maintenance">
                                    <i class="fa-solid fa-wrench"></i>
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="available">
                                <button type="submit" class="action-btn-table view" title="Set Ready for Duty">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                        <a href="<?= url('/office/trips/assign?vehicle_id=' . $v['id']) ?>" class="action-btn-table dispatch" title="Assign to Trip">
                            <i class="fa-solid fa-paper-plane"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($vehicles)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No vehicles found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalVehicles ?? count($vehicles);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedType, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedType)) $q['type'] = $selectedType;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/vehicles?' . http_build_query($q));
        };
    ?>
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> vehicle(s)</div>
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
