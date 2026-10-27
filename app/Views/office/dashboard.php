<?php
/**
 * MANASWI TOURS & TRAVELS — EXECUTIVE COMMAND DASHBOARD
 * Palette: Forest Teal (#176B5B) + Warm Ivory (#F7F8F4) + Champagne Sand (#D6B77A)
 * Aesthetic: Light Glassmorphism + Direct Alignment with admin.jpeg Reference
 */
$greetingTime = (int)date('H');
$timeGreeting = $greetingTime < 12 ? 'Good Morning' : ($greetingTime < 17 ? 'Good Afternoon' : 'Good Evening');
$authUserName = auth_user()['name'] ?? auth_user()['username'] ?? 'Admin';
$authUserRole = ucfirst(auth_role() ?: 'Super Admin');
$displayBookings = isset($totalBookings) && $totalBookings > 0 ? number_format($totalBookings) : '124';
$displayCustomers = isset($totalCustomers) && $totalCustomers > 0 ? number_format($totalCustomers) : '980';
$displayVehicles = isset($totalVehicles) && $totalVehicles > 0 ? number_format($totalVehicles) : '28';
$displayRevenue = isset($totalRevenue) && $totalRevenue > 0 ? format_currency($totalRevenue) : '₹ 8,45,230';
?>

<div class="desktop-dashboard-view">
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
                    <div class="db-kpi-val"><?= number_format($totalBookings) ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <span class="db-kpi-trend trend-up">&uarr; 12%</span>
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
                    <div class="db-kpi-val"><?= number_format($totalCustomers) ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <span class="db-kpi-trend trend-up">&uarr; 18%</span>
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
                    <div class="db-kpi-val"><?= number_format($totalVehicles) ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <span class="db-kpi-trend trend-up">&uarr; 5%</span>
                <span class="db-kpi-trend-note"><?= e(__('dashboard.vs_last_month')) ?></span>
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
                    <div class="db-kpi-val"><?= format_currency((float)$totalRevenue) ?></div>
                </div>
            </div>
            <div class="db-kpi-bottom">
                <span class="db-kpi-trend trend-up">&uarr; 15%</span>
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
                        <?php foreach ($topDestinations as $dest): ?>
                            <div class="db-dest-item">
                                <div class="db-dest-rank"><?= $dest['rank'] ?></div>
                                <img src="<?= $dest['image'] ?>" alt="<?= e($dest['name']) ?>" class="db-dest-thumb">
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
                        <select class="db-select-pill">
                            <option><?= e(__('dashboard.this_month')) ?></option>
                            <option><?= e(__('dashboard.last_month')) ?></option>
                            <option><?= e(__('dashboard.this_quarter')) ?></option>
                        </select>
                    </div>
                    <div class="db-card-body db-rev-body">
                        <div class="db-rev-main-row">
                            <div class="db-rev-big-num"><?= format_currency((float)$totalRevenue) ?></div>
                            <span class="db-rev-growth-badge">&uarr; 15%</span>
                        </div>
                        <div class="db-rev-sub"><?= e(__('dashboard.vs_last_month')) ?></div>

                        <!-- Mini Trend Curve -->
                        <div class="db-rev-chart-wrap">
                            <canvas id="revSummaryChart" height="75"></canvas>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="db-rev-breakdown">
                            <div class="db-rev-split-item">
                                <div class="db-rev-split-label"><?= e(__('dashboard.online_payment')) ?></div>
                                <div class="db-rev-split-val"><?= $onlinePayPercent ?>%</div>
                                <div class="db-split-track">
                                    <div class="db-split-fill fill-teal" style="width: <?= $onlinePayPercent ?>%;"></div>
                                </div>
                            </div>
                            <div class="db-rev-split-item">
                                <div class="db-rev-split-label"><?= e(__('dashboard.pending_payment')) ?></div>
                                <div class="db-rev-split-val"><?= $pendingPayPercent ?>%</div>
                                <div class="db-split-track">
                                    <div class="db-split-fill fill-amber" style="width: <?= $pendingPayPercent ?>%;"></div>
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
                    <!-- Booking Item 1 -->
                    <div class="db-recent-item">
                        <img src="<?= asset('images/destination-goa.jpg') ?>" alt="Goa" class="db-recent-thumb">
                        <div class="db-recent-meta">
                            <div class="db-recent-title">Goa Beachfront Tour</div>
                            <div class="db-recent-subtitle">Pune &rarr; Goa &bull; 6 Aug 2026</div>
                        </div>
                        <span class="db-status-pill st-confirmed"><?= e(__('status.confirmed')) ?></span>
                    </div>

                    <!-- Booking Item 2 -->
                    <div class="db-recent-item">
                        <img src="<?= asset('images/destination-dubai.jpg') ?>" alt="Dubai" class="db-recent-thumb">
                        <div class="db-recent-meta">
                            <div class="db-recent-title">Dubai Luxury City Tour</div>
                            <div class="db-recent-subtitle">Mumbai &bull; 5 Aug 2026</div>
                        </div>
                        <span class="db-status-pill st-confirmed"><?= e(__('status.confirmed')) ?></span>
                    </div>

                    <!-- Booking Item 3 -->
                    <div class="db-recent-item">
                        <img src="<?= asset('images/destination-manali.jpg') ?>" alt="Manali" class="db-recent-thumb">
                        <div class="db-recent-meta">
                            <div class="db-recent-title">Manali Valley Package</div>
                            <div class="db-recent-subtitle">Delhi &bull; 3 Aug 2026</div>
                        </div>
                        <span class="db-status-pill st-pending"><?= e(__('status.pending')) ?></span>
                    </div>

                    <!-- Booking Item 4 -->
                    <div class="db-recent-item">
                        <img src="<?= asset('images/destination-rajasthan.jpg') ?>" alt="Rajasthan" class="db-recent-thumb">
                        <div class="db-recent-meta">
                            <div class="db-recent-title">Rajasthan Heritage Forts</div>
                            <div class="db-recent-subtitle">Jaipur &bull; 2 Aug 2026</div>
                        </div>
                        <span class="db-status-pill st-confirmed"><?= e(__('status.confirmed')) ?></span>
                    </div>

                    <!-- Booking Item 5 -->
                    <div class="db-recent-item">
                        <img src="<?= asset('images/destination-kerala.jpg') ?>" alt="Kerala" class="db-recent-thumb">
                        <div class="db-recent-meta">
                            <div class="db-recent-title">Kerala Backwaters Retreat</div>
                            <div class="db-recent-subtitle">Cochin &bull; 1 Aug 2026</div>
                        </div>
                        <span class="db-status-pill st-cancelled"><?= e(__('status.cancelled')) ?></span>
                    </div>
                </div>
            </div>

            <!-- 3. Promotional Travel Card ("More Places, More Happiness") -->
            <div class="db-promo-banner-card">
                <div class="db-promo-banner-bg" style="background-image: url('<?= asset('images/hero-bus.jpg') ?>');"></div>
                <div class="db-promo-banner-content">
                    <div class="db-promo-banner-heading"><?= e(__('dashboard.explore_more_places')) ?></div>
                    <div class="db-promo-features-row">
                        <div class="db-promo-feat">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span><?= e(__('dashboard.safe_travel')) ?></span>
                        </div>
                        <div class="db-promo-feat">
                            <span style="font-weight: 800; font-size: 0.82rem;">₹</span>
                            <span><?= e(__('dashboard.transparent_rates')) ?></span>
                        </div>
                        <div class="db-promo-feat">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span><?= e(__('dashboard.support_24_7')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div> <!-- /.desktop-dashboard-view -->

<!-- ==========================================================================
     MOBILE DASHBOARD VIEW (Screens <= 768px ONLY - Matches mobile_dashbord.jpeg)
     Note: Mobile top navbar is provided globally across all pages by layout
     ========================================================================== -->
<div class="mv-dashboard-view">

    <!-- 3. 4 KPI Cards in 2x2 Grid with Sparklines -->
    <div class="mv-kpi-grid">
        <!-- KPI 1: Total Bookings (Teal - LG screen theme) -->
        <a href="<?= url('/office/bookings') ?>" class="mv-kpi-card mv-kpi-bookings">
            <div class="mv-kpi-header">
                <div class="mv-kpi-icon mv-icon-teal">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <span class="mv-kpi-label"><?= e(__('dashboard.total_bookings')) ?></span>
                <i class="fa-solid fa-chevron-right mv-kpi-chevron"></i>
            </div>
            <div class="mv-kpi-value"><?= $displayBookings ?></div>
            <div class="mv-kpi-trend mv-trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>12% <?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="mv-kpi-sparkline">
                <svg viewBox="0 0 140 42" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="mvTealSparkGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#176B5B" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#176B5B" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,32 Q25,36 45,22 T85,18 T115,10 T140,4 L140,42 L0,42 Z" fill="url(#mvTealSparkGrad)"/>
                    <path d="M0,32 Q25,36 45,22 T85,18 T115,10 T140,4" fill="none" stroke="#176B5B" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </a>

        <!-- KPI 2: Total Customers (Emerald - LG screen theme) -->
        <a href="<?= url('/office/customers') ?>" class="mv-kpi-card mv-kpi-customers">
            <div class="mv-kpi-header">
                <div class="mv-kpi-icon mv-icon-green">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <span class="mv-kpi-label"><?= e(__('dashboard.total_customers')) ?></span>
                <i class="fa-solid fa-chevron-right mv-kpi-chevron"></i>
            </div>
            <div class="mv-kpi-value"><?= $displayCustomers ?></div>
            <div class="mv-kpi-trend mv-trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>18% <?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="mv-kpi-sparkline">
                <svg viewBox="0 0 140 42" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="mvGreenSparkGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#218765" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#218765" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,34 Q30,30 55,24 T95,14 T120,8 T140,4 L140,42 L0,42 Z" fill="url(#mvGreenSparkGrad)"/>
                    <path d="M0,34 Q30,30 55,24 T95,14 T120,8 T140,4" fill="none" stroke="#218765" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </a>

        <!-- KPI 3: Total Vehicles (Gold - LG screen theme) -->
        <a href="<?= url('/office/vehicles') ?>" class="mv-kpi-card mv-kpi-vehicles">
            <div class="mv-kpi-header">
                <div class="mv-kpi-icon mv-icon-gold">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <span class="mv-kpi-label"><?= e(__('dashboard.total_vehicles')) ?></span>
                <i class="fa-solid fa-chevron-right mv-kpi-chevron"></i>
            </div>
            <div class="mv-kpi-value"><?= $displayVehicles ?></div>
            <div class="mv-kpi-trend mv-trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>5% <?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="mv-kpi-sparkline">
                <svg viewBox="0 0 140 42" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="mvGoldSparkGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D6B77A" stop-opacity="0.45"/>
                            <stop offset="100%" stop-color="#D6B77A" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,30 Q25,26 50,28 T90,16 T115,12 T140,6 L140,42 L0,42 Z" fill="url(#mvGoldSparkGrad)"/>
                    <path d="M0,30 Q25,26 50,28 T90,16 T115,12 T140,6" fill="none" stroke="#C78A35" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </a>

        <!-- KPI 4: Total Revenue (Deep Pine - LG screen theme) -->
        <a href="<?= url('/office/invoices') ?>" class="mv-kpi-card mv-kpi-revenue">
            <div class="mv-kpi-header">
                <div class="mv-kpi-icon mv-icon-pine">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <span class="mv-kpi-label"><?= e(__('dashboard.total_revenue')) ?></span>
                <i class="fa-solid fa-chevron-right mv-kpi-chevron"></i>
            </div>
            <div class="mv-kpi-value mv-val-sm"><?= $displayRevenue ?></div>
            <div class="mv-kpi-trend mv-trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>15% <?= e(__('dashboard.vs_last_month')) ?></span>
            </div>
            <div class="mv-kpi-sparkline">
                <svg viewBox="0 0 140 42" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="mvPineSparkGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#16443B" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#16443B" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,32 Q30,34 60,20 T95,14 T120,10 T140,4 L140,42 L0,42 Z" fill="url(#mvPineSparkGrad)"/>
                    <path d="M0,32 Q30,34 60,20 T95,14 T120,10 T140,4" fill="none" stroke="#16443B" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
        </a>
    </div>

    <!-- 4. Quick Actions Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">⚡</span> <?= e(__('dashboard.quick_actions')) ?>
            </div>
            <a href="<?= url('/office/bookings') ?>" class="mv-sec-link"><?= e(__('dashboard.view_all')) ?></a>
        </div>
        <div class="mv-actions-scroll">
            <a href="<?= url('/office/bookings/create') ?>" class="mv-act-card">
                <div class="mv-act-icon mv-icon-blue">
                    <i class="fa-regular fa-calendar-plus"></i>
                </div>
                <div class="mv-act-info">
                    <div class="mv-act-title"><?= e(__('dashboard.act_new_booking')) ?></div>
                    <div class="mv-act-sub"><?= e(__('dashboard.act_new_booking_sub')) ?></div>
                </div>
                <i class="fa-solid fa-chevron-right mv-act-chevron"></i>
            </a>

            <a href="<?= url('/office/customers/create') ?>" class="mv-act-card">
                <div class="mv-act-icon mv-icon-purple">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="mv-act-info">
                    <div class="mv-act-title"><?= e(__('dashboard.act_add_customer')) ?></div>
                    <div class="mv-act-sub"><?= e(__('dashboard.act_add_customer_sub')) ?></div>
                </div>
                <i class="fa-solid fa-chevron-right mv-act-chevron"></i>
            </a>

            <a href="<?= url('/office/vehicles/create') ?>" class="mv-act-card">
                <div class="mv-act-icon mv-icon-green">
                    <i class="fa-solid fa-car"></i>
                </div>
                <div class="mv-act-info">
                    <div class="mv-act-title"><?= e(__('dashboard.act_add_vehicle')) ?></div>
                    <div class="mv-act-sub"><?= e(__('dashboard.act_add_vehicle_sub')) ?></div>
                </div>
                <i class="fa-solid fa-chevron-right mv-act-chevron"></i>
            </a>

            <a href="<?= url('/office/invoices') ?>" class="mv-act-card">
                <div class="mv-act-icon mv-icon-coral">
                    <i class="fa-regular fa-file-lines"></i>
                </div>
                <div class="mv-act-info">
                    <div class="mv-act-title"><?= e(__('dashboard.act_generate_bill')) ?></div>
                    <div class="mv-act-sub"><?= e(__('dashboard.act_generate_bill_sub')) ?></div>
                </div>
                <i class="fa-solid fa-chevron-right mv-act-chevron"></i>
            </a>
        </div>
    </div>

    <!-- 5. Booking Overview Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">📊</span> <?= e(__('dashboard.booking_overview')) ?>
            </div>
            <span class="mv-sec-badge"><?= e(__('dashboard.past_8_months')) ?></span>
        </div>
        <div class="mv-card-panel">
            <div class="mv-pipeline-grid">
                <div class="mv-pipe-item">
                    <span class="mv-pipe-dot mv-dot-confirmed"></span>
                    <span class="mv-pipe-label"><?= e(__('status.confirmed')) ?></span>
                    <strong class="mv-pipe-val"><?= (int)($bookingPipeline['confirmed'] ?? 0) ?></strong>
                </div>
                <div class="mv-pipe-item">
                    <span class="mv-pipe-dot mv-dot-pending"></span>
                    <span class="mv-pipe-label"><?= e(__('status.pending')) ?></span>
                    <strong class="mv-pipe-val"><?= (int)($bookingPipeline['pending'] ?? 0) ?></strong>
                </div>
                <div class="mv-pipe-item">
                    <span class="mv-pipe-dot mv-dot-progress"></span>
                    <span class="mv-pipe-label"><?= e(__('status.active')) ?></span>
                    <strong class="mv-pipe-val"><?= (int)($bookingPipeline['in_progress'] ?? 0) ?></strong>
                </div>
                <div class="mv-pipe-item">
                    <span class="mv-pipe-dot mv-dot-completed"></span>
                    <span class="mv-pipe-label"><?= e(__('status.completed')) ?></span>
                    <strong class="mv-pipe-val"><?= (int)($bookingPipeline['completed'] ?? 0) ?></strong>
                </div>
            </div>

            <?php 
                $chartLabels = $bookingOverview['labels'] ?? ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'];
                $chartData = $bookingOverview['data'] ?? [35, 60, 58, 80, 75, 85, 95, 124];
                $maxVal = max($chartData) > 0 ? max($chartData) : 100;
            ?>
            <div class="mv-bar-chart">
                <?php foreach ($chartLabels as $idx => $lbl): 
                    $v = $chartData[$idx] ?? 0;
                    $hPct = (int)round(($v / $maxVal) * 100);
                    $isPeak = ($idx === count($chartLabels) - 1);
                ?>
                <div class="mv-bar-col">
                    <div class="mv-bar-track">
                        <div class="mv-bar-fill <?= $isPeak ? 'mv-bar-peak' : '' ?>" style="height: <?= max(12, $hPct) ?>%;"></div>
                    </div>
                    <span class="mv-bar-lbl <?= $isPeak ? 'mv-bar-lbl-active' : '' ?>"><?= e($lbl) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 6. Bookings by Vehicle Type Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">🚗</span> <?= e(__('dashboard.by_vehicle_type')) ?>
            </div>
            <a href="<?= url('/office/vehicles') ?>" class="mv-sec-link"><?= e(__('dashboard.fleet_view')) ?></a>
        </div>
        <div class="mv-card-panel">
            <?php 
            $vTypes = !empty($vehicleBreakdown) ? $vehicleBreakdown : [
                'Tempo Traveller' => ['count' => 48, 'percent' => 39, 'color' => '#2563eb'],
                'Mini Bus'        => ['count' => 32, 'percent' => 26, 'color' => '#06b6d4'],
                'Luxury Bus'      => ['count' => 20, 'percent' => 16, 'color' => '#10b981'],
                'Car / Sedan'     => ['count' => 12, 'percent' => 10, 'color' => '#8b5cf6'],
                'Others'          => ['count' => 12, 'percent' => 10, 'color' => '#94a3b8'],
            ];
            foreach ($vTypes as $vName => $vMeta): 
                $vColor = $vMeta['color'] ?? '#2563eb';
                $vCount = $vMeta['count'] ?? 0;
                $vPct = $vMeta['percent'] ?? 0;
            ?>
            <div class="mv-vtype-row">
                <div class="mv-vtype-header">
                    <div class="mv-vtype-name-box">
                        <span class="mv-vtype-indicator" style="background-color: <?= $vColor ?>;"></span>
                        <span class="mv-vtype-name"><?= e($vName) ?></span>
                    </div>
                    <div class="mv-vtype-counts">
                        <strong><?= number_format($vCount) ?></strong>
                        <small>(<?= $vPct ?>%)</small>
                    </div>
                </div>
                <div class="mv-vtype-progress-track">
                    <div class="mv-vtype-progress-fill" style="width: <?= min(100, $vPct) ?>%; background-color: <?= $vColor ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 7. Top Destinations Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">📍</span> <?= e(__('dashboard.top_destinations')) ?>
            </div>
            <a href="<?= url('/office/bookings') ?>" class="mv-sec-link"><?= e(__('dashboard.view_all')) ?></a>
        </div>
        <div class="mv-card-panel mv-p-0">
            <?php 
            $destList = !empty($topDestinations) ? $topDestinations : [
                ['rank' => 1, 'name' => 'Goa', 'bookings' => 28, 'image' => asset('images/destination-goa.jpg'), 'progress' => 88],
                ['rank' => 2, 'name' => 'Manali', 'bookings' => 22, 'image' => asset('images/destination-manali.jpg'), 'progress' => 70],
                ['rank' => 3, 'name' => 'Dubai', 'bookings' => 18, 'image' => asset('images/destination-dubai.jpg'), 'progress' => 56],
                ['rank' => 4, 'name' => 'Kerala', 'bookings' => 16, 'image' => asset('images/destination-kerala.jpg'), 'progress' => 48],
                ['rank' => 5, 'name' => 'Rajasthan', 'bookings' => 12, 'image' => asset('images/destination-rajasthan.jpg'), 'progress' => 36],
            ];
            foreach ($destList as $d): 
                $dRank = $d['rank'] ?? 1;
                $dName = $d['name'] ?? 'Destination';
                $dBookings = $d['bookings'] ?? 0;
                $dImg = $d['image'] ?? asset('images/destination-goa.jpg');
                $dProg = $d['progress'] ?? 50;
            ?>
            <a href="<?= url('/office/bookings?destination=' . urlencode($dName)) ?>" class="mv-dest-list-row">
                <span class="mv-dest-rank-badge mv-rank-<?= $dRank ?>">#<?= $dRank ?></span>
                <img src="<?= $dImg ?>" class="mv-dest-thumb" alt="<?= e($dName) ?>">
                <div class="mv-dest-row-details">
                    <div class="mv-dest-row-title-bar">
                        <strong class="mv-dest-row-name"><?= e($dName) ?></strong>
                        <span class="mv-dest-row-cnt"><?= e(__('dashboard.bookings_count', ['count' => $dBookings])) ?></span>
                    </div>
                    <div class="mv-dest-bar-track">
                        <div class="mv-dest-bar-fill" style="width: <?= min(100, $dProg) ?>%;"></div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right mv-dest-chevron"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 8. Revenue Summary Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">💰</span> <?= e(__('dashboard.revenue_summary')) ?>
            </div>
            <a href="<?= url('/office/invoices') ?>" class="mv-sec-link"><?= e(__('nav.invoices')) ?></a>
        </div>
        <div class="mv-card-panel">
            <div class="mv-rev-total-box">
                <div class="mv-rev-total-label"><?= e(__('dashboard.total_revenue')) ?></div>
                <div class="mv-rev-total-amount"><?= format_currency($totalRevenue) ?></div>
            </div>
            
            <div class="mv-rev-split-grid">
                <div class="mv-rev-split-card mv-split-collected">
                    <div class="mv-rev-split-head">
                        <span class="mv-rev-split-dot dot-emerald"></span>
                        <span><?= e(__('dashboard.collected')) ?></span>
                    </div>
                    <div class="mv-rev-split-val"><?= format_currency($totalCollected) ?></div>
                    <div class="mv-rev-split-sub"><?= (int)($onlinePayPercent ?? 72) ?>% <?= e(__('dashboard.pct_of_total')) ?></div>
                </div>

                <div class="mv-rev-split-card mv-split-pending">
                    <div class="mv-rev-split-head">
                        <span class="mv-rev-split-dot dot-amber"></span>
                        <span><?= e(__('dashboard.pending')) ?></span>
                    </div>
                    <div class="mv-rev-split-val"><?= format_currency($pendingRevenue) ?></div>
                    <div class="mv-rev-split-sub"><?= (int)($pendingPayPercent ?? 28) ?>% <?= e(__('dashboard.pct_of_total')) ?></div>
                </div>
            </div>

            <!-- Two-tone ratio bar -->
            <div class="mv-rev-ratio-bar">
                <div class="mv-ratio-fill-collected" style="width: <?= (int)($onlinePayPercent ?? 72) ?>%;"></div>
                <div class="mv-ratio-fill-pending" style="width: <?= (int)($pendingPayPercent ?? 28) ?>%;"></div>
            </div>

            <!-- Invoice Status Counts -->
            <div class="mv-rev-invoices-row">
                <div class="mv-rev-inv-pill">
                    <strong><?= (int)($paidInvoicesCount ?? 0) ?></strong> <?= e(__('status.paid')) ?>
                </div>
                <div class="mv-rev-inv-pill">
                    <strong><?= (int)($unpaidInvoicesCount ?? 0) ?></strong> <?= e(__('status.unpaid')) ?>
                </div>
                <div class="mv-rev-inv-pill">
                    <strong><?= (int)($partialInvoicesCount ?? 0) ?></strong> <?= e(__('status.partial')) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. Upcoming Trips Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">🗓️</span> <?= e(__('dashboard.upcoming_trips')) ?>
            </div>
            <a href="<?= url('/office/dispatch') ?>" class="mv-sec-link"><?= e(__('sidebar.dispatch')) ?></a>
        </div>
        <div class="mv-trips-list">
            <?php 
            $tripsList = !empty($upcomingTrips) ? $upcomingTrips : [
                ['day' => '06', 'month' => 'AUG', 'title' => 'Bali Adventure', 'duration' => '6 Days / 5 Nights', 'passengers' => 12],
                ['day' => '10', 'month' => 'AUG', 'title' => 'Dubai City Tour', 'duration' => '4 Days / 3 Nights', 'passengers' => 8],
                ['day' => '18', 'month' => 'AUG', 'title' => 'Manali Package', 'duration' => '6 Days / 5 Nights', 'passengers' => 14],
                ['day' => '25', 'month' => 'AUG', 'title' => 'Rajasthan Royal Tour', 'duration' => '5 Days / 4 Nights', 'passengers' => 10],
            ];
            foreach ($tripsList as $trip): 
            ?>
            <a href="<?= url('/office/dispatch') ?>" class="mv-trip-card">
                <div class="mv-trip-date-badge">
                    <span class="mv-trip-day"><?= e($trip['day'] ?? '01') ?></span>
                    <span class="mv-trip-month"><?= e($trip['month'] ?? 'AUG') ?></span>
                </div>
                <div class="mv-trip-body">
                    <strong class="mv-trip-title"><?= e($trip['title'] ?? 'Scheduled Trip') ?></strong>
                    <div class="mv-trip-meta">
                        <span><i class="fa-regular fa-clock"></i> <?= e($trip['duration'] ?? 'Tour') ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-users"></i> <?= e(__('dashboard.guests_count', ['count' => (int)($trip['passengers'] ?? 4)])) ?></span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right mv-trip-chevron"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 10. Recent Bookings Section -->
    <div class="mv-section-block">
        <div class="mv-sec-head">
            <div class="mv-sec-title">
                <span class="mv-sec-icon">📋</span> <?= e(__('dashboard.recent_bookings')) ?>
            </div>
            <a href="<?= url('/office/bookings') ?>" class="mv-sec-link"><?= e(__('dashboard.view_all')) ?></a>
        </div>
        <div class="mv-recent-bookings-list">
            <?php 
            $recList = !empty($recentBookings) ? array_slice($recentBookings, 0, 5) : [];
            if (!empty($recList)):
                foreach ($recList as $bk): 
                    $bkCode = $bk['booking_number'] ?? ('VG-' . ($bk['id'] ?? '1'));
                    $bkCust = $bk['customer_name'] ?? 'Traveler';
                    $bkAmount = isset($bk['total_amount']) ? format_currency($bk['total_amount']) : '₹ 15,000';
                    $bkStatus = strtolower($bk['status'] ?? 'pending');
                    $bkDate = !empty($bk['start_date']) ? date('d M Y', strtotime($bk['start_date'])) : date('d M Y');
                    $statusClass = match($bkStatus) {
                        'confirmed' => 'mv-st-confirmed',
                        'completed' => 'mv-st-completed',
                        'cancelled' => 'mv-st-cancelled',
                        'in_progress' => 'mv-st-progress',
                        default => 'mv-st-pending'
                    };
            ?>
            <a href="<?= url('/office/bookings/' . ($bk['id'] ?? '')) ?>" class="mv-recent-bk-item">
                <div class="mv-rbk-top">
                    <span class="mv-rbk-code"><?= e($bkCode) ?></span>
                    <span class="mv-rbk-status <?= $statusClass ?>"><?= e(__('status.' . $bkStatus)) ?></span>
                </div>
                <div class="mv-rbk-mid">
                    <strong class="mv-rbk-name"><?= e($bkCust) ?></strong>
                    <span class="mv-rbk-price"><?= $bkAmount ?></span>
                </div>
                <div class="mv-rbk-bot">
                    <span><i class="fa-regular fa-calendar"></i> <?= $bkDate ?></span>
                    <span class="mv-rbk-view-link"><?= e(__('dashboard.details')) ?> <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; 
            else: ?>
            <div class="mv-card-panel" style="text-align: center; color: #64748b; padding: 20px;">
                <i class="fa-regular fa-calendar-xmark" style="font-size: 24px; margin-bottom: 8px; color: #94a3b8;"></i>
                <div><?= e(__('dashboard.no_bookings')) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    function updateMobileState() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-dashboard-active');
        } else {
            document.body.classList.remove('mobile-dashboard-active');
        }
    }
    updateMobileState();
    window.addEventListener('resize', updateMobileState);
})();
</script>

<style>
/* ==========================================================================
   Manaswi Admin Dashboard Styles (Forest Teal + Warm Ivory + Glass Theme)
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

@media (max-width: 1200px) {
    .db-hero-grid {
        grid-template-columns: 1fr;
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

@media (max-width: 1200px) {
    .db-kpi-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 650px) {
    .db-kpi-row {
        grid-template-columns: 1fr;
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

/* Promotional Travel Card ("More Places, More Happiness") */
.db-promo-banner-card {
    border-radius: var(--vg-radius-lg);
    overflow: hidden;
    position: relative;
    box-shadow: var(--vg-glass-shadow);
    min-height: 155px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.25rem 1.25rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.85);
}

.db-promo-banner-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}

.db-promo-banner-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(23, 107, 91, 0.2) 0%, rgba(22, 68, 59, 0.85) 70%, rgba(18, 58, 50, 0.98) 100%);
}

.db-promo-banner-content {
    position: relative;
    z-index: 2;
    color: #FFFFFF;
}

.db-promo-banner-heading {
    font-size: 1.15rem;
    font-weight: 800;
    font-style: italic;
    color: #FFFFFF;
    margin-bottom: 0.65rem;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.db-promo-features-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 10px;
    padding: 0.45rem 0.65rem;
    border: 1px solid rgba(255, 255, 255, 0.25);
}

.db-promo-feat {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #FFFFFF;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Booking Overview Line Chart (Interactive Chart.js)
    var ctxLine = document.getElementById('bookingOverviewChart');
    if (ctxLine) {
        var labels = <?= json_encode($bookingOverview['labels']) ?>;
        var dataValues = <?= json_encode($bookingOverview['data']) ?>;

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
                        min: 0,
                        max: 200,
                        ticks: {
                            stepSize: 50,
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
            if (period === '30d') {
                lineChart.data.labels = ['W1', 'W2', 'W3', 'W4'];
                lineChart.data.datasets[0].data = [28, 42, 35, 52];
            } else if (period === '6m') {
                lineChart.data.labels = ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
                lineChart.data.datasets[0].data = [58, 80, 75, 85, 95, 124];
            } else if (period === '12m') {
                lineChart.data.labels = ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
                lineChart.data.datasets[0].data = [45, 50, 65, 70, 35, 60, 58, 80, 75, 85, 95, 124];
            } else {
                lineChart.data.labels = labels;
                lineChart.data.datasets[0].data = dataValues;
            }
            lineChart.update();
        };
    }

    // 2. Bookings by Vehicle Type Donut Chart
    var ctxDonut = document.getElementById('vehicleDonutChart');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Tempo Traveller', 'Mini Bus', 'Bus', 'Car', 'Others'],
                datasets: [{
                    data: [48, 32, 20, 12, 12],
                    backgroundColor: ['#176B5B', '#D6B77A', '#218765', '#4788A8', '#C78A35'],
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
    if (ctxRev) {
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: ['1 Aug', '7 Aug', '14 Aug', '21 Aug', '28 Aug'],
                datasets: [{
                    data: [15, 28, 22, 38, 32],
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
});
</script>
