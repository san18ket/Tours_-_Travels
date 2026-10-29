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

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Interactive like Customers) -->
    <div class="booking-kpi-grid" id="vehiclesKpiGrid">
        <!-- Card 1: Total Fleet -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all fleet vehicles">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_fleet', 'Total Fleet')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totalVehicles ?? count($vehicles)) ?>"><?= number_format($totalAll ?? $totalVehicles ?? count($vehicles)) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered fleet vehicles')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Available for Duty -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'available' ? 'is-selected' : '' ?>" data-status="available" role="button" tabindex="0" title="Click to filter available vehicles">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.available_for_duty', 'Available for Duty')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAvailable ?? 0) ?>"><?= number_format($totalAvailable ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'Ready for assignment')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: On Active Trip -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'on_trip' ? 'is-selected' : '' ?>" data-status="on_trip" role="button" tabindex="0" title="Click to filter vehicles on trip">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.on_active_trip', 'On Active Trip')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalOnTrip ?? 0) ?>"><?= number_format($totalOnTrip ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Currently in transit')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: In Maintenance -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'maintenance' ? 'is-selected' : '' ?>" data-status="maintenance" role="button" tabindex="0" title="Click to filter vehicles in maintenance">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('vehicles.maintenance', 'Maintenance')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalMaintenance ?? 0) ?>"><?= number_format($totalMaintenance ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.archived', 'Workshop service')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Customers Architecture) -->
    <form method="GET" action="<?= url('/office/vehicles') ?>" class="filter-bar-compact" id="vehicleFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search by registration plate, make & model..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="type" id="typeInput">
            <option value=""><?= e(__('common.all_types', 'All Vehicle Types')) ?></option>
            <option value="sedan" <?= ($selectedType ?? '') === 'sedan' ? 'selected' : '' ?>><?= e(__('vehicles.luxury_sedan', 'Luxury Sedan')) ?></option>
            <option value="suv" <?= ($selectedType ?? '') === 'suv' ? 'selected' : '' ?>><?= e(__('vehicles.premium_suv', 'Premium SUV')) ?></option>
            <option value="tempo_traveller" <?= ($selectedType ?? '') === 'tempo_traveller' ? 'selected' : '' ?>><?= e(__('vehicles.tempo_traveller', 'Tempo Traveller')) ?></option>
            <option value="luxury_bus" <?= ($selectedType ?? '') === 'luxury_bus' ? 'selected' : '' ?>><?= e(__('vehicles.luxury_bus', 'Luxury Coach / Bus')) ?></option>
            <option value="van" <?= ($selectedType ?? '') === 'van' ? 'selected' : '' ?>><?= e(__('vehicles.executive_van', 'Executive Van')) ?></option>
        </select>

        <select name="status" id="statusInput">
            <option value=""><?= e(__('common.all_statuses', 'All Statuses')) ?></option>
            <option value="available" <?= ($selectedStatus ?? '') === 'available' ? 'selected' : '' ?>><?= e(__('status.available', 'Available')) ?></option>
            <option value="on_trip" <?= ($selectedStatus ?? '') === 'on_trip' ? 'selected' : '' ?>><?= e(__('status.on_trip', 'On Trip')) ?></option>
            <option value="maintenance" <?= ($selectedStatus ?? '') === 'maintenance' ? 'selected' : '' ?>><?= e(__('status.maintenance', 'Maintenance')) ?></option>
            <option value="retired" <?= ($selectedStatus ?? '') === 'retired' ? 'selected' : '' ?>><?= e(__('status.retired', 'Retired')) ?></option>
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
            <?php if (!empty($search) || !empty($selectedType) || !empty($selectedStatus)): ?>
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
                <h2 class="mv-page-title"><?= e(__('sidebar.vehicles', 'Fleet Vehicles')) ?></h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/vehicles/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span><?= e(__('btn.add_vehicle', 'Add Vehicle')) ?></span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Search Bar + Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/vehicles') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search plate, make, model..." value="<?= e($search ?? '') ?>" autocomplete="off">
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
                    <label for="mvTypeSelect">Classification:</label>
                    <select name="type" id="mvTypeSelect">
                        <option value="">All Vehicle Types</option>
                        <option value="sedan" <?= ($selectedType ?? '') === 'sedan' ? 'selected' : '' ?>>Luxury Sedan</option>
                        <option value="suv" <?= ($selectedType ?? '') === 'suv' ? 'selected' : '' ?>>Premium SUV</option>
                        <option value="tempo_traveller" <?= ($selectedType ?? '') === 'tempo_traveller' ? 'selected' : '' ?>>Tempo Traveller</option>
                        <option value="luxury_bus" <?= ($selectedType ?? '') === 'luxury_bus' ? 'selected' : '' ?>>Luxury Coach / Bus</option>
                        <option value="van" <?= ($selectedType ?? '') === 'van' ? 'selected' : '' ?>>Executive Van</option>
                    </select>
                </div>
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
            $makeStatusUrl = function(string $st) use ($search, $selectedType, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($selectedType)) $p['type'] = $selectedType;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/vehicles' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="vehiclesMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span><?= e(__('filter.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalVehicles ?? count($vehicles)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('available') ?>" class="mv-pill <?= $currStatus === 'available' ? 'active' : '' ?>" data-status="available" data-no-ajax="true">
                <span><?= e(__('status.available', 'Available')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalAvailable ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('on_trip') ?>" class="mv-pill <?= $currStatus === 'on_trip' ? 'active' : '' ?>" data-status="on_trip" data-no-ajax="true">
                <span><?= e(__('status.on_trip', 'On Trip')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalOnTrip ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('maintenance') ?>" class="mv-pill <?= $currStatus === 'maintenance' ? 'active' : '' ?>" data-status="maintenance" data-no-ajax="true">
                <span><?= e(__('status.maintenance', 'Maintenance')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalMaintenance ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Vehicles Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="vehiclesSection">
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
                        <th style="min-width: 170px;"><?= e(__('table.plate_number', 'Plate & Registration')) ?></th>
                        <th style="min-width: 190px;"><?= e(__('table.model', 'Make & Model')) ?></th>
                        <th style="min-width: 150px;"><?= e(__('table.type', 'Classification')) ?></th>
                        <th class="text-center" style="min-width: 140px;"><?= e(__('table.capacity', 'Capacity & Fuel')) ?></th>
                        <th class="text-center" style="min-width: 120px;"><?= e(__('table.odometer', 'Odometer')) ?></th>
                        <th class="text-center" style="min-width: 120px;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 75px;"><?= e(__('table.actions', 'Actions')) ?></th>
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
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>
                        <td>
                            <div class="project-name" style="font-family:ui-monospace, monospace;letter-spacing:0.02em;font-weight:700;color:var(--brand-primary, #176B5B);">
                                <?= e($v['vehicle_number']) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0F172A; font-size: 0.95rem;">
                                <?= e($v['make_model']) ?>
                            </div>
                        </td>
                        <td>
                            <span style="display:inline-block;background:rgba(23, 107, 91,0.08);color:#16443B;border:1px solid rgba(23, 107, 91,0.18);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;">
                                <?= e(str_replace('_', ' ', $v['vehicle_type'])) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;color:#0F172A;font-size:13px;">
                                <i class="fa-solid fa-users" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= (int)$v['seating_capacity'] ?> Seats
                            </div>
                            <small class="text-muted" style="font-size:11px;text-transform:uppercase;">
                                <?= e($v['fuel_type']) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:800;color:#0F172A;font-size:13.5px;">
                                <?= number_format((int)$v['odometer_km']) ?> km
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="vehicleToggleWrap-<?= (int)$v['id'] ?>">
                                <button type="button" 
                                        class="cust-status-toggle <?= $status === 'available' ? 'is-active' : '' ?> <?= $status === 'on_trip' ? 'is-locked' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $status === 'available' ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$v['id'] ?>"
                                        data-name="<?= addslashes(e($v['vehicle_number'])) ?>"
                                        onclick="toggleVehicleStatus(<?= (int)$v['id'] ?>, this, event)"
                                        title="Click to toggle readiness (currently <?= $statusText ?>)">
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
                        <td class="text-center">
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <?php if ($v['status'] !== 'on_trip'): ?>
                                    <form action="<?= url('/office/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="margin:0;">
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
                                    <button type="button" class="action-btn-table delete" title="Delete Vehicle" onclick="openDeleteModal(<?= (int)$v['id'] ?>, '<?= addslashes(e($v['vehicle_number'])) ?>', event)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
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

        <!-- Mobile Touch Cards List -->
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
                    <div class="mv-thumb-wrap">
                        <div class="mv-thumb-avatar" style="background:#E8F7F0;color:#176B5B;">
                            <i class="fa-solid fa-van-shuttle" style="font-size:22px;"></i>
                        </div>
                    </div>
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
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.stopPropagation()">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $status === 'available' ? 'is-active' : '' ?> <?= $status === 'on_trip' ? 'is-locked' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $status === 'available' ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$v['id'] ?>"
                                    data-name="<?= addslashes(e($v['vehicle_number'])) ?>"
                                    onclick="toggleVehicleStatus(<?= (int)$v['id'] ?>, this, event)"
                                    title="Toggle readiness">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb"></span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price" style="font-size: 11.5px; color: #16443B; margin-top: 4px;">
                            <?= number_format((int)$v['odometer_km']) ?> km
                        </div>
                    </div>
                </div>
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <?php if ($v['status'] !== 'on_trip'): ?>
                        <form action="<?= url('/office/vehicles/' . $v['id'] . '/status') ?>" method="POST" style="margin:0;">
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
                        <button type="button" class="action-btn-table delete" title="Delete Vehicle" onclick="openDeleteModal(<?= (int)$v['id'] ?>, '<?= addslashes(e($v['vehicle_number'])) ?>', event)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
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

    <!-- Modern Pagination (Exact Customers Architecture) -->
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
    <div id="vehiclesPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> vehicle(s)</div>
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
                                I understand this vehicle will be permanently deleted.
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
                                Yes, I am completely sure and want to delete this vehicle.
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

    <!-- Hidden form for standard POST fallback -->
    <form id="deleteVehicleForm" method="POST" style="display: none;">
        <?= csrf_field() ?>
    </form>
</div>

<script>
    /* ========================================================================= */
    /* Vehicles Directory Live AJAX Filter Engine (Matches Customers Architecture)*/
    /* ========================================================================= */
    (function initVehicleAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/vehicles') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const typeInput = document.getElementById('typeInput');
            const mvTypeSelect = document.getElementById('mvTypeSelect');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const tVal = typeInput ? typeInput.value : (mvTypeSelect ? mvTypeSelect.value : '');
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (tVal) p.set('type', tVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, typeVal: tVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterVehiclesAjax(page = 1) {
            const { params, searchVal } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const section = document.getElementById('vehiclesSection');
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

                const newSection = doc.getElementById('vehiclesSection');
                const currSection = document.getElementById('vehiclesSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                const newPagination = doc.getElementById('vehiclesPaginationWrapper');
                const currPagination = document.getElementById('vehiclesPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                const newKpi = doc.getElementById('vehiclesKpiGrid');
                const currKpi = document.getElementById('vehiclesKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                const newMvPills = doc.getElementById('vehiclesMvStatusScroll');
                const currMvPills = document.getElementById('vehiclesMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                bindDynamicEvents();
                try {
                    window.history.pushState({ page: page, url: targetUrl }, '', targetUrl);
                } catch(e) {}
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Vehicles AJAX filter error:', err);
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
                searchTimer = setTimeout(() => filterVehiclesAjax(1), 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterVehiclesAjax(1);
                }
            });
        }

        const mInput = document.getElementById('mvSearchInput');
        if (mInput) {
            mInput.addEventListener('input', function() {
                const val = this.value;
                if (sInput && sInput.value !== val) sInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => filterVehiclesAjax(1), 280);
            });
            mInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterVehiclesAjax(1);
                }
            });
        }

        // Clear buttons
        function clearSearch() {
            if (sInput) sInput.value = '';
            if (mInput) mInput.value = '';
            filterVehiclesAjax(1);
        }
        const sClear = document.getElementById('searchClearBtn');
        if (sClear) sClear.addEventListener('click', clearSearch);
        const mClear = document.getElementById('mvSearchClearBtn');
        if (mClear) mClear.addEventListener('click', clearSearch);

        // Dropdowns
        const typeInput = document.getElementById('typeInput');
        if (typeInput) typeInput.addEventListener('change', () => filterVehiclesAjax(1));
        const mvTypeSelect = document.getElementById('mvTypeSelect');
        if (mvTypeSelect) mvTypeSelect.addEventListener('change', () => {
            if (typeInput) typeInput.value = mvTypeSelect.value;
            filterVehiclesAjax(1);
        });
        const stInput = document.getElementById('statusInput');
        if (stInput) stInput.addEventListener('change', () => filterVehiclesAjax(1));
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) ppInput.addEventListener('change', () => filterVehiclesAjax(1));
        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) mvPpSelect.addEventListener('change', () => {
            if (ppInput) ppInput.value = mvPpSelect.value;
            filterVehiclesAjax(1);
        });

        // Apply & Refresh
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) applyBtn.addEventListener('click', () => filterVehiclesAjax(1));
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterVehiclesAjax(1);
        });

        // Live Dynamic KPI Cards and Mobile Pills Syncer (AJAX Real-Time Update)
        function updateVehicleMetrics(metrics) {
            if (!metrics) return;
            const mapping = {
                '': metrics.total,
                'available': metrics.available,
                'on_trip': metrics.on_trip,
                'maintenance': metrics.maintenance
            };
            const grid = document.getElementById('vehiclesKpiGrid');
            if (grid) {
                Object.keys(mapping).forEach(st => {
                    const card = grid.querySelector(`.booking-kpi-card[data-status="${st}"]`);
                    if (card) {
                        const valEl = card.querySelector('.booking-kpi-val');
                        if (valEl && mapping[st] !== undefined) {
                            valEl.setAttribute('data-count', mapping[st]);
                        }
                    }
                });
                animateKpiNumbers(grid);
            }
            const scrollWrap = document.getElementById('vehiclesMvStatusScroll');
            if (scrollWrap) {
                Object.keys(mapping).forEach(st => {
                    const pill = scrollWrap.querySelector(`.mv-pill[data-status="${st}"]`);
                    if (pill) {
                        const cntEl = pill.querySelector('.mv-pill-cnt');
                        if (cntEl && mapping[st] !== undefined) {
                            cntEl.textContent = (parseInt(mapping[st], 10) || 0).toLocaleString();
                        }
                    }
                });
            }
            const stInput = document.getElementById('statusInput');
            const currentFilter = (stInput ? stInput.value : '').trim();
            const totalPill = document.getElementById('totalRecordsPill');
            if (totalPill) {
                let relevantCount = metrics.total;
                if (currentFilter && mapping[currentFilter] !== undefined) {
                    relevantCount = mapping[currentFilter];
                }
                totalPill.textContent = (parseInt(relevantCount, 10) || 0).toLocaleString();
            }
        }

        // Event delegation for dynamic clicks (KPI cards, Pills, Clear, Pagination)
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#vehiclesKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#vehiclesKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#vehiclesMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterVehiclesAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                } else {
                    if (sInput) sInput.value = '';
                    if (mInput) mInput.value = '';
                    if (typeInput) typeInput.value = '';
                    if (mvTypeSelect) mvTypeSelect.value = '';
                    if (stInput) stInput.value = '';
                }
                filterVehiclesAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#vehiclesMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                document.querySelectorAll('#vehiclesMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#vehiclesKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st);
                });

                filterVehiclesAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#vehiclesPaginationWrapper .pagination a');
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
                filterVehiclesAjax(targetPage);

                const sec = document.getElementById('vehiclesSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#vehiclesKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        window.addEventListener('popstate', function() {
            filterVehiclesAjax(1);
        });

        window.updateVehicleMetrics = updateVehicleMetrics;
        window.filterVehiclesAjax = filterVehiclesAjax;
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

    function toggleVehicleStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        if (btn.classList.contains('is-locked')) {
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            alert('Cannot toggle vehicle readiness while actively on a dispatched trip.');
            return;
        }

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'maintenance' : 'available';

        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/vehicles/') ?>' + id + '/toggle-status';
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
                const isNowAvailable = (data.status === 'available');

                // Update all matching toggle buttons (table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowAvailable) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle readiness (currently Available)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle readiness (currently Maintenance)';
                    }
                });

                // Update table status pill & dot
                const wrap = document.getElementById('vehicleToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    const pillClass = isNowAvailable ? 'completed' : 'assigned';
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
                            mText.className = 'mv-toggle-label-text ' + (isNowAvailable ? 'completed' : 'assigned');
                            mText.textContent = data.label;
                        }
                    }
                });

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateVehicleMetrics === 'function') {
                    window.updateVehicleMetrics(data.metrics);
                }

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Vehicle status updated to ${data.label}`, 'success');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error toggling vehicle status', 'error');
            } else {
                alert(err.message || 'Error toggling vehicle status');
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
        currentDeleteName = name || 'this vehicle';

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

        const desc1 = document.getElementById('delModalDesc1');
        if (desc1) {
            desc1.textContent = 'This item will be permanently deleted and cannot be recovered.';
        }
        const desc2 = document.getElementById('delModalDesc2');
        if (desc2) {
            desc2.textContent = 'You are about to delete this item. This action cannot be undone.';
        }

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

        const deleteUrl = '<?= url('/office/vehicles/') ?>' + currentDeleteId + '/delete';
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
                    window.vgNotify(data.message || 'Vehicle deleted successfully.', 'success');
                }
                if (data.metrics && typeof window.updateVehicleMetrics === 'function') {
                    window.updateVehicleMetrics(data.metrics);
                }
                if (typeof window.filterVehiclesAjax === 'function') {
                    const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                    window.filterVehiclesAjax(pg, { skipPushState: true });
                } else {
                    window.location.reload();
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to delete vehicle.', 'error');
                } else {
                    alert(data.message || 'Failed to delete vehicle.');
                }
                closeDeleteModal();
            }
        })
        .catch(err => {
            // Fallback to standard form post
            const form = document.getElementById('deleteVehicleForm');
            if (form) {
                form.action = deleteUrl;
                form.submit();
            } else {
                alert('An error occurred while deleting vehicle.');
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
.cust-status-toggle.is-locked .toggle-slider-track {
    background: #218765;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.35);
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
.cust-status-toggle.is-locked .toggle-slider-thumb {
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
.cust-status-toggle.sm.is-active .toggle-slider-thumb,
.cust-status-toggle.sm.is-locked .toggle-slider-thumb {
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
.cust-toggle-pill.assigned, .cust-toggle-pill.maintenance {
    background: #FEF3C7;
    color: #B45309;
}
.cust-toggle-pill.in-progress, .cust-toggle-pill.on_trip {
    background: #DBEAFE;
    color: #16443B;
}
.cust-toggle-pill.cancelled, .cust-toggle-pill.retired {
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
.toggle-pulse-dot.assigned, .toggle-pulse-dot.maintenance {
    background: #F59E0B;
}
.toggle-pulse-dot.in-progress, .toggle-pulse-dot.on_trip {
    background: #218765;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}
.toggle-pulse-dot.cancelled, .toggle-pulse-dot.retired {
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
.mv-toggle-label-text.assigned, .mv-toggle-label-text.maintenance {
    color: #D97706;
}
.mv-toggle-label-text.in-progress, .mv-toggle-label-text.on_trip {
    color: #176B5B;
}
.mv-toggle-label-text.cancelled, .mv-toggle-label-text.retired {
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

/* Table Delete Action Button */
.action-btn-table.delete {
    color: #EF4444;
    background-color: #FEF2F2;
    border: 1px solid #FECACA;
}
.action-btn-table.delete:hover {
    background-color: #DC2626 !important;
    color: #FFFFFF !important;
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35);
}

/* Card-Specific KPI Selected Themes matching Customers */
.booking-kpi-card[data-status=""].is-selected {
    border-color: #0D9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.16), 0 10px 24px rgba(13, 148, 136, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #F0FDFA 100%);
}
.booking-kpi-card[data-status=""].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #0D9488;
    font-weight: 700;
}

.booking-kpi-card[data-status="available"].is-selected {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16), 0 10px 24px rgba(16, 185, 129, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #ECFDF5 100%);
}
.booking-kpi-card[data-status="available"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #10B981;
    font-weight: 700;
}

.booking-kpi-card[data-status="on_trip"].is-selected {
    border-color: #F59E0B;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.16), 0 10px 24px rgba(245, 158, 11, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFBEB 100%);
}
.booking-kpi-card[data-status="on_trip"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #F59E0B;
    font-weight: 700;
}

.booking-kpi-card[data-status="maintenance"].is-selected {
    border-color: #218765;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16), 0 10px 24px rgba(59, 130, 246, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #E8F7F0 100%);
}
.booking-kpi-card[data-status="maintenance"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #218765;
    font-weight: 700;
}

/* ========================================================================= */
/* Two-Step Delete Confirmation Modals (Matching delete.jpeg)                */
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
