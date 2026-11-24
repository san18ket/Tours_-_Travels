<?php
/**
 * EXECUTIVE COMMAND DASHBOARD
 * Palette: Forest Teal (#176B5B) + Warm Ivory (#F7F8F4) + Champagne Sand (#D6B77A)
 * Aesthetic: Light Glassmorphism + Direct Alignment with Reference Architecture
 */
$greetingTime = (int)date('H');
$timeGreeting = $greetingTime < 12 ? 'Good Morning' : ($greetingTime < 17 ? 'Good Afternoon' : 'Good Evening');
$authUserName = auth_user()['name'] ?? auth_user()['username'] ?? 'Admin';
$authUserRole = ucfirst(auth_role() ?: 'Super Admin');
$displayBookings = number_format($totalBookings ?? 0);
$displayCustomers = number_format($totalCustomers ?? 0);
$displayVehicles = number_format($totalVehicles ?? 0);
$displayRevenue = format_currency((float)($totalRevenue ?? 0));
?>

<div class="db-container">
    <!-- Row 1: Hero Welcome Card (Left) & Weather Card (Right) -->
    <div class="db-hero-grid">
        <!-- Hero Welcome Banner with Tour Bus Imagery -->
        <div class="db-hero-card">
            <div class="db-hero-image-wrap">
                <img src="<?= asset('images/hero-bus.jpg') ?>" alt="Tour bus in mountains" class="db-hero-bus-img">
            </div>
            <div class="db-hero-left">
                <div class="db-hero-greeting">
                    <span class="db-wave-emoji">👋</span> <?= e(__('dashboard.welcome_back', ['name' => $authUserName])) ?>
                </div>
                <p class="db-hero-subtitle">
                    <?= e(__('dashboard.welcome_sub')) ?>
                </p>
                <a href="<?= url('/office/bookings/create') ?>" class="btn btn-primary" style="margin-top: 1rem; border-radius: 12px; gap: 0.55rem; padding: 0.55rem 1.25rem; font-weight: 700;">
                    <i class="fa-solid fa-plus"></i>
                    <span><?= e(__('dashboard.create_booking')) ?></span>
                </a>
            </div>
        </div>

        <!-- Date & Weather Card -->
        <div class="db-weather-card">
            <div class="db-weather-header">
                <div class="db-weather-cal-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="db-weather-date"><?= date('D, j M Y') ?></div>
            </div>
            <div class="db-weather-body">
                <div class="db-weather-icon-sun">
                    <i class="fa-solid fa-cloud-sun text-warning" style="font-size: 2.2rem; color: #D6B77A;"></i>
                </div>
                <div class="db-weather-temp-wrap">
                    <div class="db-weather-temp">28&deg;C</div>
                    <div class="db-weather-desc"><?= e(__('dashboard.weather_desc')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: 4 Major KPI Cards -->
    <div class="db-kpi-row">
        <!-- KPI 1: Total Bookings -->
        <div class="db-kpi-card">
            <div class="db-kpi-top">
                <div class="db-kpi-icon-pill kpi-teal">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="db-kpi-meta">
                    <div class="db-kpi-label"><?= e(__('dashboard.total_bookings')) ?></div>
                    <div class="db-kpi-val"><?= $displayBookings ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <?php $bDir = $kpiTrends['bookings']['dir'] ?? 'up'; ?>
                <span class="db-kpi-trend <?= $bDir === 'up' ? 'trend-up' : 'trend-down' ?>"><?= $bDir === 'up' ? '&uarr;' : '&darr;' ?> <?= abs($kpiTrends['bookings']['growth'] ?? 0) ?>%</span>
                <span class="db-kpi-trend-note"><?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="db-kpi-sparkline">
                <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="sparkline-svg">
                    <defs>
                        <linearGradient id="tealSpark" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#176B5B" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#176B5B" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,32 Q25,35 45,22 T90,16 T120,6 L120,40 L0,40 Z" fill="url(#tealSpark)"/>
                    <path d="M0,32 Q25,35 45,22 T90,16 T120,6" fill="none" stroke="#176B5B" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- KPI 2: Total Customers -->
        <div class="db-kpi-card">
            <div class="db-kpi-top">
                <div class="db-kpi-icon-pill kpi-green">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="db-kpi-meta">
                    <div class="db-kpi-label"><?= e(__('dashboard.total_customers')) ?></div>
                    <div class="db-kpi-val"><?= $displayCustomers ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <?php $cDir = $kpiTrends['customers']['dir'] ?? 'up'; ?>
                <span class="db-kpi-trend <?= $cDir === 'up' ? 'trend-up' : 'trend-down' ?>"><?= $cDir === 'up' ? '&uarr;' : '&darr;' ?> <?= abs($kpiTrends['customers']['growth'] ?? 0) ?>%</span>
                <span class="db-kpi-trend-note"><?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="db-kpi-sparkline">
                <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="sparkline-svg">
                    <defs>
                        <linearGradient id="greenSpark" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#218765" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#218765" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,35 Q20,38 45,26 T85,18 T120,8 L120,40 L0,40 Z" fill="url(#greenSpark)"/>
                    <path d="M0,35 Q20,38 45,26 T85,18 T120,8" fill="none" stroke="#218765" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- KPI 3: Total Vehicles -->
        <div class="db-kpi-card">
            <div class="db-kpi-top">
                <div class="db-kpi-icon-pill kpi-sand">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                </div>
                <div class="db-kpi-meta">
                    <div class="db-kpi-label"><?= e(__('dashboard.total_vehicles')) ?></div>
                    <div class="db-kpi-val"><?= $displayVehicles ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <span class="db-kpi-trend trend-up"><?= (int)($availableFleet ?? 0) ?> Active</span>
                <span class="db-kpi-trend-note"><?= (int)($onTripFleet ?? 0) ?> on trip</span>
            </div>
            <div class="db-kpi-sparkline">
                <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="sparkline-svg">
                    <defs>
                        <linearGradient id="sandSpark" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D6B77A" stop-opacity="0.45"/>
                            <stop offset="100%" stop-color="#D6B77A" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,34 Q30,36 55,25 T90,20 T120,12 L120,40 L0,40 Z" fill="url(#sandSpark)"/>
                    <path d="M0,34 Q30,36 55,25 T90,20 T120,12" fill="none" stroke="#D6B77A" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- KPI 4: Total Revenue -->
        <div class="db-kpi-card">
            <div class="db-kpi-top">
                <div class="db-kpi-icon-pill kpi-orange">
                    <span style="font-size: 1.25rem; font-weight: 800;">₹</span>
                </div>
                <div class="db-kpi-meta">
                    <div class="db-kpi-label"><?= e(__('dashboard.total_revenue')) ?></div>
                    <div class="db-kpi-val"><?= $displayRevenue ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <?php $rDir = $kpiTrends['revenue']['dir'] ?? 'up'; ?>
                <span class="db-kpi-trend <?= $rDir === 'up' ? 'trend-up' : 'trend-down' ?>"><?= $rDir === 'up' ? '&uarr;' : '&darr;' ?> <?= abs($kpiTrends['revenue']['growth'] ?? 0) ?>%</span>
                <span class="db-kpi-trend-note"><?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="db-kpi-sparkline">
                <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="sparkline-svg">
                    <defs>
                        <linearGradient id="orangeSpark" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C78A35" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#C78A35" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,36 Q25,32 50,24 T95,16 T120,8 L120,40 L0,40 Z" fill="url(#orangeSpark)"/>
                    <path d="M0,36 Q25,32 50,24 T95,16 T120,8" fill="none" stroke="#C78A35" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Row 3: Split Main Operations Section -->
    <div class="db-main-split">
        <!-- Left Content Area (Charts, Destinations, Revenue, Upcoming Trips) -->
        <div class="db-content-left">
            <!-- Row: Booking Overview Line Chart + Bookings by Vehicle Type Donut -->
            <div class="db-charts-row">
                <!-- 1. Booking Overview Line Chart -->
                <div class="db-card db-chart-card">
                    <div class="db-card-header">
                        <div class="db-card-title">
                            <div class="db-title-icon-pill">
                                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span><?= e(__('dashboard.booking_overview')) ?></span>
                        </div>
                        <div class="db-card-actions">
                            <select class="db-select-pill" id="bookingPeriodSelect" onchange="updateBookingOverview(this.value)">
                                <option value="8m"><?= e(__('dashboard.last_8_months')) ?></option>
                                <option value="30d"><?= e(__('dashboard.last_30_days')) ?></option>
                                <option value="6m"><?= e(__('dashboard.last_6_months')) ?></option>
                                <option value="12m"><?= e(__('dashboard.last_12_months')) ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="db-card-body" style="height: 250px; position: relative;">
                        <canvas id="bookingOverviewChart"></canvas>
                    </div>
                </div>

                <!-- 2. Bookings by Vehicle Type Donut Chart -->
                <div class="db-card db-donut-card">
                    <div class="db-card-header">
                        <div class="db-card-title">
                            <div class="db-title-icon-pill">
                                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                            </div>
                            <span><?= e(__('dashboard.by_vehicle_type')) ?></span>
                        </div>
                    </div>
                    <div class="db-donut-layout">
                        <!-- Donut Graphic with Centered Count -->
                        <div class="db-donut-wrap">
                            <canvas id="vehicleDonutChart" width="160" height="160"></canvas>
                            <div class="db-donut-center">
                                <div class="db-donut-center-num"><?= $totalBookings ?></div>
                                <div class="db-donut-center-lbl"><?= e(__('dashboard.total_bookings')) ?></div>
                            </div>
                        </div>
                        <!-- Donut Legend List -->
                        <div class="db-donut-legend">
                            <?php if (!empty($vehicleBreakdown)): ?>
                                <?php foreach ($vehicleBreakdown as $type => $info): ?>
                                    <div class="db-legend-item">
                                        <div class="db-legend-left">
                                            <span class="db-legend-dot" style="background-color: <?= $info['color'] ?>;"></span>
                                            <span class="db-legend-label"><?= e($type) ?></span>
                                        </div>
                                        <div class="db-legend-right">
                                            <span class="db-legend-count"><?= $info['count'] ?></span>
                                            <span class="db-legend-pct">(<?= $info['percent'] ?>%)</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="font-size: 0.82rem; color: #72827A; padding: 0.5rem 0;">No vehicle activity recorded.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row: Top Destinations + Revenue Summary + Upcoming Trips -->
            <div class="db-triple-row">
                <!-- 1. Top Destinations -->
                <div class="db-card db-dest-card">
                    <div class="db-card-header">
                        <div class="db-card-title">
                            <div class="db-title-icon-pill">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <span><?= e(__('dashboard.top_destinations')) ?></span>
                        </div>
                        <a href="<?= url('/office/bookings') ?>" class="db-view-all"><?= e(__('dashboard.view_all')) ?></a>
                    </div>
                    <div class="db-card-body db-dest-body">
                        <?php if (!empty($topDestinations)): ?>
                            <?php foreach ($topDestinations as $dest): ?>
                                <div class="db-dest-item">
                                    <div class="db-dest-rank" style="width:34px;height:34px;border-radius:10px;background:rgba(23,107,91,0.08);color:var(--vg-primary-blue,#176B5B);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.88rem;flex-shrink:0;">#<?= $dest['rank'] ?></div>
                                    <div class="db-dest-info">
                                        <div class="db-dest-name-row">
                                            <span class="db-dest-name"><?= e($dest['name']) ?></span>
                                            <span class="db-dest-count"><?= e(__('dashboard.bookings_count', ['count' => $dest['bookings']])) ?></span>
                                        </div>
                                        <div class="db-dest-progress-track">
                                            <div class="db-dest-progress-bar" style="width: <?= $dest['progress'] ?>%;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="db-empty-state" style="padding: 2.2rem 1rem; text-align: center; color: #72827A;">
                                <i class="fa-solid fa-map-location-dot" style="font-size: 2rem; opacity: 0.4; margin-bottom: 0.5rem; display: block;"></i>
                                <p style="margin: 0; font-size: 0.88rem; font-weight: 500;">No destination data yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Revenue Summary -->
                <div class="db-card db-rev-card">
                    <div class="db-card-header">
                        <div class="db-card-title">
                            <div class="db-title-icon-pill">
                                <span style="font-weight: 800; font-size: 0.95rem;">₹</span>
                            </div>
                            <span><?= e(__('dashboard.revenue_summary')) ?></span>
                        </div>
                        <select class="db-select-pill" id="revenuePeriodSelect" onchange="updateRevenueSummary(this.value)">
                            <option value="this_month"><?= e(__('dashboard.this_month')) ?></option>
                            <option value="last_month"><?= e(__('dashboard.last_month')) ?></option>
                            <option value="this_quarter"><?= e(__('dashboard.this_quarter')) ?></option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    <div class="db-card-body db-rev-body">
                        <div class="db-rev-main-row">
                            <div class="db-rev-big-num" id="revBigNum"><?= $revenueData['formatted'] ?? $displayRevenue ?></div>
                            <?php 
                            $revDir = $revenueData['dir'] ?? 'up';
                            $revGrowth = abs($revenueData['growth'] ?? 0);
                            ?>
                            <span class="db-rev-growth-badge <?= $revDir === 'down' ? 'growth-down' : '' ?>" id="revGrowthBadge">
                                <?= $revDir === 'up' ? '&uarr;' : '&darr;' ?> <?= $revGrowth ?>%
                            </span>
                        </div>
                        <div class="db-rev-sub" id="revPeriodComparisonLabel"><?= e(__('dashboard.vs_last_month')) ?></div>

                        <!-- Mini Trend Curve -->
                        <div class="db-rev-chart-wrap">
                            <canvas id="revSummaryChart" height="75"></canvas>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="db-rev-breakdown">
                            <div class="db-rev-split-item">
                                <div class="db-rev-split-label"><?= e(__('dashboard.online_payment')) ?></div>
                                <div class="db-rev-split-val" id="revOnlineVal"><?= $revenueData['online_pct'] ?? $onlinePayPercent ?>%</div>
                                <div class="db-split-track">
                                    <div class="db-split-fill fill-teal" id="revOnlineBar" style="width: <?= $revenueData['online_pct'] ?? $onlinePayPercent ?>%;"></div>
                                </div>
                            </div>
                            <div class="db-rev-split-item">
                                <div class="db-rev-split-label"><?= e(__('dashboard.pending_payment')) ?></div>
                                <div class="db-rev-split-val" id="revPendingVal"><?= $revenueData['pending_pct'] ?? $pendingPayPercent ?>%</div>
                                <div class="db-split-track">
                                    <div class="db-split-fill fill-amber" id="revPendingBar" style="width: <?= $revenueData['pending_pct'] ?? $pendingPayPercent ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Upcoming Trips -->
                <div class="db-card db-trips-card">
                    <div class="db-card-header">
                        <div class="db-card-title">
                            <div class="db-title-icon-pill">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span><?= e(__('dashboard.upcoming_trips')) ?></span>
                        </div>
                        <a href="<?= url('/office/trips/assign') ?>" class="db-view-all"><?= e(__('dashboard.view_all')) ?></a>
                    </div>
                    <div class="db-card-body db-trips-body">
                        <?php if (!empty($upcomingTrips)): ?>
                            <?php foreach ($upcomingTrips as $trip): ?>
                                <div class="db-trip-item">
                                    <div class="db-trip-date-badge">
                                        <span class="db-trip-date-day"><?= e($trip['day']) ?></span>
                                        <span class="db-trip-date-mo"><?= e($trip['month']) ?></span>
                                    </div>
                                    <div class="db-trip-details">
                                        <div class="db-trip-title"><?= e($trip['title']) ?></div>
                                        <div class="db-trip-duration"><?= e($trip['duration']) ?></div>
                                    </div>
                                    <div class="db-trip-passengers">
                                        <?= e(__('dashboard.passengers_count', ['count' => $trip['passengers']])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="db-empty-state" style="padding: 2.2rem 1rem; text-align: center; color: #72827A;">
                                <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; opacity: 0.4; margin-bottom: 0.5rem; display: block;"></i>
                                <p style="margin: 0; font-size: 0.88rem; font-weight: 500;">No upcoming trips.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column Area (Quick Actions, Recent Bookings, Promo Card) -->
        <div class="db-content-right">
            <!-- 1. Quick Actions Card -->
            <div class="db-card db-actions-card">
                <div class="db-card-header">
                    <div class="db-card-title">
                        <span style="font-size: 1.15rem; color: #D6B77A;">⚡</span>
                        <span><?= e(__('dashboard.quick_actions')) ?></span>
                    </div>
                </div>
                <div class="db-card-body db-actions-grid">
                    <a href="<?= url('/office/bookings/create') ?>" class="db-action-btn">
                        <div class="db-action-icon act-teal">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="db-action-label"><?= e(__('dashboard.act_new_booking')) ?></span>
                        <svg class="db-action-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <a href="<?= url('/office/customers/create') ?>" class="db-action-btn">
                        <div class="db-action-icon act-sand">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <span class="db-action-label"><?= e(__('dashboard.act_add_customer')) ?></span>
                        <svg class="db-action-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <a href="<?= url('/office/vehicles/create') ?>" class="db-action-btn">
                        <div class="db-action-icon act-green">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                        </div>
                        <span class="db-action-label"><?= e(__('dashboard.act_add_vehicle')) ?></span>
                        <svg class="db-action-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <a href="<?= url('/office/invoices') ?>" class="db-action-btn">
                        <div class="db-action-icon act-amber">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="db-action-label"><?= e(__('dashboard.act_generate_bill')) ?></span>
                        <svg class="db-action-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Recent Bookings Card -->
            <div class="db-card db-recent-card">
                <div class="db-card-header">
                    <div class="db-card-title">
                        <div class="db-title-icon-pill">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span><?= e(__('dashboard.recent_bookings')) ?></span>
                    </div>
                    <a href="<?= url('/office/bookings') ?>" class="db-view-all"><?= e(__('dashboard.view_all')) ?></a>
                </div>
                <div class="db-card-body db-recent-list">
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $bk): ?>
                            <div class="db-recent-item">
                                <div class="db-recent-thumb db-recent-icon-wrap" style="display:flex;align-items:center;justify-content:center;background:rgba(23,107,91,0.08);color:var(--vg-primary-blue,#176B5B);font-size:1.05rem;border-radius:10px;">
                                    <i class="fa-solid <?= e($bk['icon'] ?? 'fa-route') ?>"></i>
                                </div>
                                <div class="db-recent-meta">
                                    <div class="db-recent-title"><?= e($bk['title']) ?></div>
                                    <div class="db-recent-subtitle">
                                        <?= e($bk['customer_name']) ?><?= !empty($bk['route_or_dest']) ? ' &bull; ' . e($bk['route_or_dest']) : '' ?> &bull; <?= e($bk['date']) ?>
                                    </div>
                                </div>
                                <?php
                                $st = strtolower($bk['status'] ?? 'pending');
                                $stClass = match($st) {
                                    'confirmed' => 'st-confirmed',
                                    'completed' => 'st-confirmed',
                                    'cancelled' => 'st-cancelled',
                                    default => 'st-pending',
                                };
                                ?>
                                <span class="db-status-pill <?= $stClass ?>"><?= e(ucfirst($bk['status'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="db-empty-state" style="padding: 2.2rem 1rem; text-align: center; color: #72827A;">
                            <i class="fa-solid fa-receipt" style="font-size: 2rem; opacity: 0.4; margin-bottom: 0.5rem; display: block;"></i>
                            <p style="margin: 0; font-size: 0.88rem; font-weight: 500;">No recent bookings.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Admin Dashboard Styles (Forest Teal + Warm Ivory + Glass Theme)
   ========================================================================== */

.db-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* 1. Hero Grid */
.db-hero-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.35rem;
}

@media (max-width: 1200px) and (min-width: 992px) {
    .db-hero-grid {
        grid-template-columns: 1fr;
    }
}

/* ========================================================================= */
/* Mobile Dashboard Architecture (< 992px)                                   */
/* 1. Remove Hero & Weather section completely on mobile                     */
/* 2. Direct flow from Top Navbar into KPI Cards (Zero blank space)          */
/* 3. 2-Column KPI Grid matching /office/finance/refunds & /office/finance    */
/* ========================================================================= */
@media (max-width: 991.98px) {
    .db-container {
        gap: 1rem !important;
        padding: 0.85rem 0.85rem 0 !important;
    }

    /* Mobile dashboard spacing: Natural 12-16px gap after Recent Bookings */
    .dashboard-main-content,
    .main-content:has(.db-container),
    .main-wrapper:has(.db-container) .main-content,
    body.is-dashboard-page .main-content {
        padding-bottom: 1rem !important;
    }

    /* Footer follows content naturally and clears the fixed bottom navigation */
    .dashboard-app-footer,
    .main-wrapper:has(.db-container) .app-footer,
    body.is-dashboard-page .app-footer {
        display: flex !important;
        margin-top: 0 !important;
        padding-top: 1rem !important;
        padding-bottom: calc(64px + 16px + env(safe-area-inset-bottom, 0px)) !important;
    }

    .db-hero-grid,
    .db-container > .db-hero-grid,
    .db-hero-card,
    .db-weather-card {
        display: none !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 0 !important;
        min-height: 0 !important;
        border: none !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }
}

.db-wave-emoji {
    display: inline-block;
    animation: dbWave 2.5s infinite;
    transform-origin: 70% 70%;
}

@keyframes dbWave {
    0% { transform: rotate(0deg); }
    10% { transform: rotate(14deg); }
    20% { transform: rotate(-8deg); }
    30% { transform: rotate(14deg); }
    40% { transform: rotate(-4deg); }
    50% { transform: rotate(10deg); }
    60% { transform: rotate(0deg); }
    100% { transform: rotate(0deg); }
}

.db-weather-card {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: var(--vg-glass-blur);
    -webkit-backdrop-filter: var(--vg-glass-blur);
    border: 1px solid rgba(255, 255, 255, 0.90);
    border-radius: var(--vg-radius-xl);
    box-shadow: var(--vg-glass-shadow);
    padding: 1.35rem 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.85rem;
}

.db-weather-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--vg-text);
    font-size: 0.88rem;
    font-weight: 600;
}

.db-weather-cal-icon {
    color: var(--brand-primary);
}

.db-weather-body {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.db-weather-temp {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--brand-secondary);
    line-height: 1.1;
}

.db-weather-desc {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* 2. 4 Major KPI Cards Row */
.db-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.35rem;
}

@media (max-width: 1200px) and (min-width: 992px) {
    .db-kpi-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* 2-Column Professional KPI Grid on Mobile matching /office/finance/refunds */
@media (max-width: 991.98px) {
    .db-kpi-row {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }

    .db-kpi-card {
        padding: 0.85rem 0.95rem !important;
        border-radius: 14px !important;
        min-height: 96px !important;
        box-shadow: 0 2px 10px rgba(22, 68, 59, 0.06) !important;
    }

    .db-kpi-top {
        gap: 0.65rem !important;
        margin-bottom: 0.45rem !important;
    }

    .db-kpi-icon-pill {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
    }

    .db-kpi-icon-pill svg {
        width: 17px !important;
        height: 17px !important;
    }

    .db-kpi-label {
        font-size: 0.74rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.2 !important;
    }

    .db-kpi-val {
        font-size: 1.3rem !important;
        margin-top: 1px !important;
        line-height: 1.2 !important;
        word-break: break-word !important;
    }

    .db-kpi-bottom {
        font-size: 0.72rem !important;
        gap: 0.25rem !important;
    }

    .db-kpi-trend-note {
        font-size: 0.68rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .db-kpi-sparkline {
        height: 36px !important;
        bottom: 0 !important;
        right: 0 !important;
        opacity: 0.35 !important;
    }
}

/* Very Small Mobile Screens (<= 380px down to 320px) */
@media (max-width: 380px) {
    .db-container {
        padding: 0.65rem 0.65rem 0 !important;
    }

    .dashboard-main-content,
    .main-content:has(.db-container),
    .main-wrapper:has(.db-container) .main-content,
    body.is-dashboard-page .main-content {
        padding-bottom: 0.85rem !important;
    }

    .dashboard-app-footer,
    .main-wrapper:has(.db-container) .app-footer,
    body.is-dashboard-page .app-footer {
        padding-top: 0.85rem !important;
        padding-bottom: calc(64px + 16px + env(safe-area-inset-bottom, 0px)) !important;
    }

    .db-kpi-row {
        gap: 0.5rem !important;
    }

    .db-kpi-card {
        padding: 0.65rem 0.7rem !important;
        min-height: 90px !important;
        border-radius: 12px !important;
    }

    .db-kpi-top {
        gap: 0.45rem !important;
        margin-bottom: 0.35rem !important;
    }

    .db-kpi-icon-pill {
        width: 30px !important;
        height: 30px !important;
        border-radius: 8px !important;
    }

    .db-kpi-icon-pill svg {
        width: 15px !important;
        height: 15px !important;
    }

    .db-kpi-label {
        font-size: 0.68rem !important;
    }

    .db-kpi-val {
        font-size: 1.12rem !important;
    }

    .db-kpi-bottom {
        font-size: 0.68rem !important;
    }

    .db-kpi-trend-note {
        display: none !important;
    }
}

.db-kpi-card {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: var(--vg-glass-blur);
    -webkit-backdrop-filter: var(--vg-glass-blur);
    border: 1px solid rgba(255, 255, 255, 0.90);
    border-radius: var(--vg-radius-lg);
    box-shadow: var(--vg-glass-shadow);
    padding: 1.25rem 1.4rem;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.db-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 35px rgba(22, 68, 59, 0.12);
}

.db-kpi-top {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.65rem;
}

.db-kpi-icon-pill {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kpi-teal   { background: rgba(23, 107, 91, 0.12); color: #176B5B; }
.kpi-green  { background: rgba(33, 135, 101, 0.12); color: #218765; }
.kpi-sand   { background: rgba(214, 183, 122, 0.22); color: #B89658; }
.kpi-orange { background: rgba(199, 138, 53, 0.15); color: #C78A35; }

.db-kpi-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted);
}

.db-kpi-val {
    font-size: 1.65rem;
    font-weight: 800;
    color: var(--brand-secondary);
    line-height: 1.15;
    margin-top: 2px;
}

.db-kpi-bottom {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    z-index: 2;
}

.db-kpi-trend {
    font-weight: 700;
}

.trend-up {
    color: var(--status-success);
}

.trend-down {
    color: var(--status-danger, #D9534F);
}

.growth-down {
    background: rgba(217, 87, 87, 0.12) !important;
    color: #9E2D2D !important;
}

.db-kpi-trend-note {
    color: var(--text-muted);
}

.db-kpi-sparkline {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 55%;
    height: 50px;
    pointer-events: none;
    opacity: 0.95;
}

.sparkline-svg {
    width: 100%;
    height: 100%;
}

/* 3. Main Split Section */
.db-main-split {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.35rem;
}

@media (max-width: 1200px) {
    .db-main-split {
        grid-template-columns: 1fr;
    }
}

.db-content-left {
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
}

.db-content-right {
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
}

/* Shared Card Architecture */
.db-card {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: var(--vg-glass-blur);
    -webkit-backdrop-filter: var(--vg-glass-blur);
    border: 1px solid rgba(255, 255, 255, 0.90);
    border-radius: var(--vg-radius-lg);
    box-shadow: var(--vg-glass-shadow);
    overflow: hidden;
}

.db-card-header {
    padding: 1.1rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(22, 68, 59, 0.06);
}

.db-card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--brand-secondary);
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.db-title-icon-pill {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(23, 107, 91, 0.1);
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.db-view-all {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--brand-primary);
}

.db-view-all:hover {
    color: var(--brand-primary-hover);
    text-decoration: underline;
}

.db-select-pill {
    padding: 0.35rem 0.85rem;
    border-radius: var(--vg-radius-full);
    border: 1px solid rgba(22, 68, 59, 0.14);
    background: rgba(255, 255, 255, 0.90);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-primary);
    outline: none;
    cursor: pointer;
}

/* Charts Row (Booking Overview + Donut) */
.db-charts-row {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 1.35rem;
}

@media (max-width: 900px) {
    .db-charts-row {
        grid-template-columns: 1fr;
    }
}

/* Donut Layout */
.db-donut-layout {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.4rem;
    gap: 1.5rem;
}

.db-donut-wrap {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
}

.db-donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    pointer-events: none;
}

.db-donut-center-num {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--brand-secondary);
    line-height: 1.1;
}

.db-donut-center-lbl {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--text-muted);
}

.db-donut-legend {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
}

.db-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
}

.db-legend-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-primary);
    font-weight: 500;
}

.db-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.db-legend-right {
    display: flex;
    gap: 0.35rem;
}

.db-legend-count {
    font-weight: 700;
    color: var(--brand-secondary);
}

.db-legend-pct {
    color: var(--text-muted);
}

/* Triple Row (Top Destinations + Revenue Summary + Upcoming Trips) */
.db-triple-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.35rem;
}

@media (max-width: 1100px) {
    .db-triple-row {
        grid-template-columns: 1fr;
    }
}

/* Destinations List */
.db-dest-body {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1.15rem 1.4rem;
}

.db-dest-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.db-dest-rank {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-muted);
    width: 16px;
    text-align: center;
}

.db-dest-thumb {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.db-dest-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.db-dest-name-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
}

.db-dest-name {
    font-weight: 700;
    color: var(--brand-secondary);
}

.db-dest-count {
    color: var(--text-muted);
    font-size: 0.78rem;
}

.db-dest-progress-track {
    height: 5px;
    border-radius: 3px;
    background: rgba(23, 107, 91, 0.12);
    overflow: hidden;
}

.db-dest-progress-bar {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #176B5B 0%, #208773 100%);
}

/* Revenue Summary */
.db-rev-body {
    padding: 1.15rem 1.4rem;
    display: flex;
    flex-direction: column;
}

.db-rev-main-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.db-rev-big-num {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--brand-secondary);
    line-height: 1.1;
}

.db-rev-growth-badge {
    background: rgba(33, 135, 101, 0.12);
    color: var(--status-success);
    font-weight: 700;
    font-size: 0.78rem;
    padding: 0.15rem 0.5rem;
    border-radius: var(--vg-radius-full);
}

.db-rev-sub {
    font-size: 0.76rem;
    color: var(--text-muted);
    margin-top: 2px;
    margin-bottom: 0.5rem;
}

.db-rev-chart-wrap {
    height: 70px;
    margin-bottom: 0.75rem;
}

.db-rev-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(22, 68, 59, 0.06);
}

.db-rev-split-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 600;
}

.db-rev-split-val {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--brand-secondary);
    margin: 2px 0 4px;
}

.db-split-track {
    height: 4px;
    border-radius: 2px;
    background: rgba(22, 68, 59, 0.08);
    overflow: hidden;
}

.db-split-fill {
    height: 100%;
}

.fill-teal  { background: #176B5B; }
.fill-amber { background: #C78A35; }

/* Upcoming Trips */
.db-trips-body {
    padding: 1.15rem 1.4rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.db-trip-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.db-trip-date-badge {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: rgba(23, 107, 91, 0.1);
    color: var(--brand-primary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.db-trip-date-day {
    font-size: 0.95rem;
    font-weight: 800;
    line-height: 1;
}

.db-trip-date-mo {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
}

.db-trip-details {
    flex: 1;
}

.db-trip-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--brand-secondary);
}

.db-trip-duration {
    font-size: 0.74rem;
    color: var(--text-muted);
    margin-top: 1px;
}

.db-trip-passengers {
    font-size: 0.74rem;
    font-weight: 600;
    color: var(--brand-primary);
    background: rgba(23, 107, 91, 0.08);
    padding: 0.25rem 0.6rem;
    border-radius: var(--vg-radius-full);
    white-space: nowrap;
}

/* Quick Actions Card */
.db-actions-grid {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.db-action-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 0.85rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.18s ease;
}

.db-action-btn:hover {
    background: #FFFFFF;
    border-color: rgba(23, 107, 91, 0.25);
    transform: translateX(3px);
    box-shadow: 0 4px 12px rgba(22, 68, 59, 0.08);
}

.db-action-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.act-teal   { background: rgba(23, 107, 91, 0.12); color: #176B5B; }
.act-sand   { background: rgba(214, 183, 122, 0.22); color: #B89658; }
.act-green  { background: rgba(33, 135, 101, 0.12); color: #218765; }
.act-amber  { background: rgba(199, 138, 53, 0.15); color: #C78A35; }

.db-action-label {
    flex: 1;
    margin-left: 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--brand-secondary);
}

.db-action-chevron {
    width: 14px;
    height: 14px;
    color: var(--text-muted);
}

/* Recent Bookings Card */
.db-recent-list {
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.db-recent-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.db-recent-thumb {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}

.db-recent-meta {
    flex: 1;
    min-width: 0;
}

.db-recent-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--brand-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.db-recent-subtitle {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-top: 1px;
}

.db-status-pill {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.22rem 0.65rem;
    border-radius: var(--vg-radius-full);
}

.st-confirmed { background: rgba(33, 135, 101, 0.12); color: #165A43; }
.st-pending   { background: rgba(199, 138, 53, 0.12); color: #8C5D1A; }
.st-cancelled { background: rgba(217, 87, 87, 0.12); color: #9E2D2D; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Booking Overview Line Chart (Interactive Chart.js)
    var ctxLine = document.getElementById('bookingOverviewChart');
    if (ctxLine) {
        var labels = <?= json_encode($bookingOverview['labels'] ?? []) ?>;
        var dataValues = <?= json_encode($bookingOverview['data'] ?? []) ?>;

        var lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bookings',
                    data: dataValues,
                    borderColor: '#176B5B',
                    backgroundColor: function(context) {
                        var chart = context.chart;
                        var ctx = chart.ctx;
                        var gradient = ctx.createLinearGradient(0, 0, 0, 220);
                        gradient.addColorStop(0, 'rgba(23, 107, 91, 0.28)');
                        gradient.addColorStop(1, 'rgba(23, 107, 91, 0.00)');
                        return gradient;
                    },
                    tension: 0.42,
                    fill: true,
                    pointBackgroundColor: '#176B5B',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    borderWidth: 2.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#16443B',
                        titleFont: { size: 12, weight: '700' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#72827A', font: { size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#72827A',
                            font: { size: 11 }
                        },
                        grid: {
                            color: 'rgba(22, 68, 59, 0.06)',
                            drawBorder: false
                        }
                    }
                }
            }
        });

        window.updateBookingOverview = function(period) {
            var url = '<?= url('/office/dashboard/booking-overview') ?>?period=' + encodeURIComponent(period);
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res || !res.success || !res.data) return;
                lineChart.data.labels = res.data.labels;
                lineChart.data.datasets[0].data = res.data.data;
                lineChart.update();
            })
            .catch(function(err) {
                console.error('Failed to update booking overview:', err);
            });
        };
    }

    // 2. Bookings by Vehicle Type Donut Chart
    var ctxDonut = document.getElementById('vehicleDonutChart');
    if (ctxDonut) {
        var donutLabels = <?= json_encode($vehicleDonutLabels ?? []) ?>;
        var donutCounts = <?= json_encode($vehicleDonutCounts ?? []) ?>;
        var donutColors = <?= json_encode($vehicleDonutColors ?? []) ?>;
        if (!donutCounts || donutCounts.length === 0 || donutCounts.every(function(v){ return v === 0; })) {
            donutLabels = ['No Data'];
            donutCounts = [1];
            donutColors = ['#E2E8F0'];
        }
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutCounts,
                    backgroundColor: donutColors,
                    borderWidth: 3,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#16443B',
                        padding: 8,
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // 3. Mini Revenue Summary Curve
    var ctxRev = document.getElementById('revSummaryChart');
    var revChart = null;
    if (ctxRev) {
        var revLabels = <?= json_encode($revenueData['curve_labels'] ?? ['1', '7', '14', '21', '28']) ?>;
        var revPoints = <?= json_encode($revenueData['curve_points'] ?? [0, 0, 0, 0, 0]) ?>;
        revChart = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [{
                    data: revPoints,
                    borderColor: '#176B5B',
                    backgroundColor: 'rgba(23, 107, 91, 0.12)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#176B5B',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#72827A', font: { size: 9 } }
                    },
                    y: { display: false }
                }
            }
        });
    }

    // Revenue Period Switcher Function
    window.updateRevenueSummary = function(period) {
        var url = '<?= url('/office/dashboard/revenue-summary') ?>?period=' + encodeURIComponent(period);
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (!res || !res.success || !res.data) return;
            var d = res.data;

            var bigNumEl = document.getElementById('revBigNum');
            if (bigNumEl) bigNumEl.textContent = d.formatted;

            var growthEl = document.getElementById('revGrowthBadge');
            if (growthEl) {
                var dirArrow = d.dir === 'up' ? '↑' : '↓';
                growthEl.textContent = dirArrow + ' ' + Math.abs(d.growth) + '%';
                if (d.dir === 'down') {
                    growthEl.classList.add('growth-down');
                } else {
                    growthEl.classList.remove('growth-down');
                }
            }

            var compLabelEl = document.getElementById('revPeriodComparisonLabel');
            if (compLabelEl) {
                if (period === 'last_month') {
                    compLabelEl.textContent = 'vs previous month';
                } else if (period === 'this_quarter') {
                    compLabelEl.textContent = 'vs last quarter';
                } else if (period === 'this_year') {
                    compLabelEl.textContent = 'vs last year';
                } else {
                    compLabelEl.textContent = 'vs last month';
                }
            }

            var onlineValEl = document.getElementById('revOnlineVal');
            var onlineBarEl = document.getElementById('revOnlineBar');
            if (onlineValEl) onlineValEl.textContent = d.online_pct + '%';
            if (onlineBarEl) onlineBarEl.style.width = d.online_pct + '%';

            var pendingValEl = document.getElementById('revPendingVal');
            var pendingBarEl = document.getElementById('revPendingBar');
            if (pendingValEl) pendingValEl.textContent = d.pending_pct + '%';
            if (pendingBarEl) pendingBarEl.style.width = d.pending_pct + '%';

            if (revChart && d.curve_labels && d.curve_points) {
                revChart.data.labels = d.curve_labels;
                revChart.data.datasets[0].data = d.curve_points;
                revChart.update();
            }
        })
        .catch(function(err) {
            console.error('Failed to update revenue summary:', err);
        });
    };
});
</script>
