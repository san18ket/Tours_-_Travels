<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <?php
    $fleetActions = '';
    if (auth_role() === 'admin') {
        $fleetActions = '<a href="' . url('/office/vehicles/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>Register New Vehicle</span></a>';
    }
    $currentTab = $_GET['tab'] ?? 'vehicles';

    $availCount = 0;
    $onTripCount = 0;
    $maintCount = 0;
    foreach ($vehicles as $vItem) {
        $st = strtolower($vItem['status'] ?? '');
        if ($st === 'available') $availCount++;
        elseif ($st === 'on_trip') $onTripCount++;
        elseif ($st === 'maintenance') $maintCount++;
    }
    ?>
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Fleet & Chauffeur Roster',
            'description' => 'Maintain commercial fleet readiness, vehicle compliance cycles, and chauffeur duty rosters.',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => 'Fleet Management', 'url' => ''],
            ],
            'actions'     => $fleetActions,
            'image'       => 'header-vehicles.jpg',
            'badge'       => count($vehicles) . ' Commercial Vehicles',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Fleet -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Commercial Fleet</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= count($vehicles) ?></div>
            <div class="booking-kpi-sub">
                <span>All registered vehicles</span>
            </div>
        </div>

        <!-- Card 2: Ready for Duty -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Available for Duty</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= $availCount ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Ready for dispatch</span>
            </div>
        </div>

        <!-- Card 3: Active Deployments -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">On Active Trip</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= $onTripCount ?></div>
            <div class="booking-kpi-sub">
                <span>In transit across routes</span>
            </div>
        </div>

        <!-- Card 4: Chauffeurs Roster -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Chauffeur Roster</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= count($drivers) ?></div>
            <div class="booking-kpi-sub">
                <span>Active chauffeurs</span>
            </div>
        </div>
    </div>

    <!-- 3. Tab Navigation & Filter Bar -->
    <div class="filter-bar-compact" style="justify-content: space-between;">
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a href="<?= url('/office/fleet?tab=vehicles') ?>" class="fbc-btn <?= $currentTab === 'vehicles' ? 'btn-primary' : '' ?>" style="<?= $currentTab === 'vehicles' ? 'background:#176B5B;color:#FFF;border-color:#176B5B;' : '' ?>">
                <i class="fa-solid fa-van-shuttle" style="<?= $currentTab === 'vehicles' ? 'color:#FFF;' : '' ?>"></i>
                <span>Commercial Vehicles (<?= count($vehicles) ?>)</span>
            </a>
            <a href="<?= url('/office/fleet?tab=drivers') ?>" class="fbc-btn <?= $currentTab === 'drivers' ? 'btn-primary' : '' ?>" style="<?= $currentTab === 'drivers' ? 'background:#176B5B;color:#FFF;border-color:#176B5B;' : '' ?>">
                <i class="fa-solid fa-id-card" style="<?= $currentTab === 'drivers' ? 'color:#FFF;' : '' ?>"></i>
                <span>Chauffeur Roster (<?= count($drivers) ?>)</span>
            </a>
            <a href="<?= url('/office/vehicles') ?>" class="fbc-btn">
                <i class="fa-solid fa-list-check"></i>
                <span>Detailed Vehicle Directory &rarr;</span>
            </a>
        </div>
        <div class="fbc-actions-end">
            <a href="<?= url('/office/fleet') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </div>

    <!-- 4. MOBILE ONLY: Top App Bar -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Fleet Roster</h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/vehicles/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Add Vehicle</span>
            </a>
            <?php endif; ?>
        </div>

        <div class="mv-status-scroll">
            <a href="<?= url('/office/fleet?tab=vehicles') ?>" class="mv-pill <?= $currentTab === 'vehicles' ? 'active' : '' ?>">
                <span>Vehicles</span>
                <span class="mv-pill-cnt"><?= count($vehicles) ?></span>
            </a>
            <a href="<?= url('/office/fleet?tab=drivers') ?>" class="mv-pill <?= $currentTab === 'drivers' ? 'active' : '' ?>">
                <span>Chauffeurs</span>
                <span class="mv-pill-cnt"><?= count($drivers) ?></span>
            </a>
            <a href="<?= url('/office/vehicles') ?>" class="mv-pill">
                <span>Directory</span>
            </a>
        </div>
    </div>

    <!-- 5. Content Section -->
    <section class="recent-bookings-section">
        <?php if ($currentTab === 'vehicles'): ?>
            <div class="section-header">
                <h3 class="section-title">Commercial Vehicles</h3>
                <div class="total-records-box">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="total-records-label">Total Records:</span>
                    <span class="total-records-badge"><?= count($vehicles) ?></span>
                </div>
            </div>

            <!-- Modern Table -->
            <div class="modern-table-container">
                <table class="modern-table" id="fleetVehiclesTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">#</th>
                            <th style="width: 22%;">Plate &amp; Registration</th>
                            <th style="width: 22%;">Make &amp; Model</th>
                            <th style="width: 16%;">Classification</th>
                            <th class="text-center" style="width: 14%;">Capacity &amp; Fuel</th>
                            <th class="text-center" style="width: 14%;">Odometer</th>
                            <th class="text-center" style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $i => $v):
                            $status = strtolower($v['status'] ?? 'available');
                            $statusClass = match($status) {
                                'available' => 'completed',
                                'on_trip' => 'in-progress',
                                'maintenance' => 'assigned',
                                default => 'cancelled'
                            };
                            $statusText = ucfirst(str_replace('_', ' ', $status));
                        ?>
                        <tr>
                            <td class="text-center">
                                <div class="project-name" style="padding: 3px 8px;"><?= $i + 1 ?></div>
                            </td>
                            <td>
                                <div class="project-name" style="font-family:monospace;letter-spacing:0.02em;font-weight:700;">
                                    <?= e($v['vehicle_number']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">
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
                                    <?= (int)$v['seating_capacity'] ?> Seats
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="cards-grid" id="fleetCards">
                <?php foreach ($vehicles as $v):
                    $status = strtolower($v['status'] ?? 'available');
                    $statusClass = match($status) {
                        'available' => 'available',
                        'on_trip' => 'on_trip',
                        'maintenance' => 'maintenance',
                        default => 'pending'
                    };
                    $statusText = ucfirst(str_replace('_', ' ', $status));
                ?>
                <div class="mv-booking-card-wrapper">
                    <div class="mv-booking-card">
                        <div class="mv-thumb-wrap">
                            <div class="mv-thumb-avatar">
                                <i class="fa-solid fa-van-shuttle" style="font-size:22px;"></i>
                            </div>
                        </div>
                        <div class="mv-details">
                            <div class="mv-code"><?= e($v['vehicle_number']) ?></div>
                            <div class="mv-tour-title"><?= e($v['make_model']) ?></div>
                            <div class="mv-date-duration">
                                <i class="fa-solid fa-car"></i>
                                <span><?= e(ucfirst(str_replace('_', ' ', $v['vehicle_type']))) ?> &bull; <?= (int)$v['seating_capacity'] ?> Seats</span>
                            </div>
                            <div class="mv-vehicle">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span><?= number_format((int)$v['odometer_km']) ?> km</span>
                            </div>
                        </div>
                        <div class="mv-right-col">
                            <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($currentTab === 'drivers'): ?>
            <div class="section-header">
                <h3 class="section-title">Chauffeur Duty Roster</h3>
                <div class="total-records-box">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="total-records-label">Total Records:</span>
                    <span class="total-records-badge"><?= count($drivers) ?></span>
                </div>
            </div>

            <!-- Drivers Modern Table -->
            <div class="modern-table-container">
                <table class="modern-table" id="fleetDriversTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">#</th>
                            <th style="width: 25%;">Chauffeur Profile</th>
                            <th style="width: 20%;">License &amp; Expiry</th>
                            <th class="text-center" style="width: 16%;">Experience &amp; Rating</th>
                            <th class="text-center" style="width: 14%;">Duty Status</th>
                            <th class="text-center" style="width: 15%;">GPS Telemetry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $i => $d):
                            $duty = strtolower($d['duty_status'] ?? 'off_duty');
                            $dutyClass = match($duty) {
                                'on_duty', 'available' => 'completed',
                                'on_trip' => 'in-progress',
                                default => 'cancelled'
                            };
                            $dutyText = ucfirst(str_replace('_', ' ', $duty));
                        ?>
                        <tr>
                            <td class="text-center">
                                <div class="project-name" style="padding: 3px 8px;"><?= $i + 1 ?></div>
                            </td>
                            <td>
                                <div class="client-cell">
                                    <div class="client-info">
                                        <span class="client-name"><?= e($d['name']) ?></span>
                                        <small class="text-muted d-block" style="font-size:11.5px;margin-top:2px;">
                                            <i class="fa-solid fa-phone" style="font-size:10px;color:var(--brand-primary, #176B5B);"></i> <?= e($d['phone']) ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-family:monospace;font-weight:700;color:var(--brand-primary, #176B5B);">
                                    <?= e($d['license_number']) ?>
                                </div>
                                <small class="text-muted" style="font-size:11px;">
                                    Exp: <?= format_date($d['license_expiry']) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                    <?= (int)$d['experience_years'] ?> Yrs
                                </div>
                                <small style="color:#d97706;font-weight:700;font-size:11.5px;">
                                    ★ <?= e($d['rating']) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="status-cell">
                                    <span class="status-dot <?= $dutyClass ?>"></span>
                                    <span class="status-badge bg-<?= $dutyClass ?>"><?= $dutyText ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($d['last_gps_at'])): ?>
                                    <span style="font-size:11.5px;font-weight:700;color:#10b981;">📡 Live Synced</span>
                                    <small class="text-muted d-block" style="font-size:10.5px;">
                                        <?= format_datetime($d['last_gps_at'], 'H:i, d M') ?>
                                    </small>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:11.5px;">Standby</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="cards-grid" id="driversCards">
                <?php foreach ($drivers as $d):
                    $duty = strtolower($d['duty_status'] ?? 'off_duty');
                    $initials = strtoupper(mb_substr($d['name'] ?? 'D', 0, 1));
                ?>
                <div class="mv-booking-card-wrapper">
                    <div class="mv-booking-card">
                        <div class="mv-thumb-wrap">
                            <div class="mv-thumb-avatar">
                                <?= e($initials) ?>
                            </div>
                        </div>
                        <div class="mv-details">
                            <div class="mv-code"><?= e($d['license_number']) ?></div>
                            <div class="mv-tour-title"><?= e($d['name']) ?></div>
                            <div class="mv-date-duration">
                                <i class="fa-solid fa-phone"></i>
                                <span><?= e($d['phone']) ?></span>
                            </div>
                            <div class="mv-passenger">
                                <i class="fa-solid fa-award"></i>
                                <span><?= (int)$d['experience_years'] ?> Yrs &bull; Rating: ★ <?= e($d['rating']) ?></span>
                            </div>
                        </div>
                        <div class="mv-right-col">
                            <span class="mv-status-badge mv-status-<?= $duty === 'on_duty' ? 'active' : 'inactive' ?>"><?= ucfirst(str_replace('_', ' ', $duty)) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
