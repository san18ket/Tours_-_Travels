<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Command Center') ?> - <?= e($app['name'] ?? 'VoyageGo') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('icons/icon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div id="offlineBanner" class="offline-banner">
        ⚠️ Offline Mode: Live telemetry synchronization paused. Changes will sync once connection returns.
    </div>

    <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="closeSidebar()" aria-hidden="true"></div>

    <div class="app-wrapper">
        <!-- Luxury Collapsible & Off-Canvas Category Navigation -->
        <aside class="sidebar" id="sidebar" aria-label="Main Navigation">
            <div class="sidebar-header">
                <a href="<?= url('/office/dashboard') ?>" class="brand-logo" title="VoyageGo Luxury Concierge">
                    <div class="brand-icon">VG</div>
                    <div class="brand-name">
                        <span class="brand-title">VoyageGo</span>
                        <span class="brand-subtitle">Luxury Travel Ops</span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close Navigation Drawer">&times;</button>
            </div>

            <nav class="sidebar-nav">
                <?php
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    $role = auth_role();

                    $isDashboardActive = str_contains($uri, '/office/dashboard');
                    $isCustomersActive = str_contains($uri, '/office/customers');
                    $isBookingsActive  = str_contains($uri, '/office/bookings');
                    $isEmployeesActive = str_contains($uri, '/office/employees');
                    $isVehiclesActive  = str_contains($uri, '/office/vehicles');
                    $isAccessActive    = str_contains($uri, '/office/users') || str_contains($uri, '/office/roles') || str_contains($uri, '/office/role-permission');
                    $isTripsActive     = str_contains($uri, '/office/trips') || str_contains($uri, '/office/dispatch');
                    $isTrackingActive  = str_contains($uri, '/office/tracking');
                ?>

                <!-- 1. Dashboard -->
                <div class="sidebar-category" id="cat-dashboard">
                    <button type="button" class="category-header <?= $isDashboardActive ? 'has-active open' : '' ?>" id="cat-dashboard-header" data-title="Dashboard" onclick="toggleCategory('cat-dashboard')" aria-expanded="<?= $isDashboardActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </span>
                            <span class="category-title">Dashboard</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isDashboardActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isDashboardActive ? 'expanded' : '' ?>" id="cat-dashboard-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/dashboard') ?>" class="nav-link <?= $isDashboardActive ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    <span class="nav-text">Command Center</span>
                                </span>
                                <span class="nav-badge">Live</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Customers -->
                <div class="sidebar-category" id="cat-customers">
                    <button type="button" class="category-header <?= $isCustomersActive ? 'has-active open' : '' ?>" id="cat-customers-header" data-title="Customers" onclick="toggleCategory('cat-customers')" aria-expanded="<?= $isCustomersActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </span>
                            <span class="category-title">Customers</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isCustomersActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isCustomersActive ? 'expanded' : '' ?>" id="cat-customers-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/customers') ?>" class="nav-link <?= ($uri === '/office/customers' || (str_contains($uri, '/office/customers') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                    <span class="nav-text">Customer List</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/customers/create') ?>" class="nav-link <?= str_contains($uri, '/office/customers/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    <span class="nav-text">Add Customer</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3. Bookings -->
                <div class="sidebar-category" id="cat-bookings">
                    <button type="button" class="category-header <?= $isBookingsActive ? 'has-active open' : '' ?>" id="cat-bookings-header" data-title="Bookings" onclick="toggleCategory('cat-bookings')" aria-expanded="<?= $isBookingsActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </span>
                            <span class="category-title">Bookings</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isBookingsActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isBookingsActive ? 'expanded' : '' ?>" id="cat-bookings-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/bookings') ?>" class="nav-link <?= ($uri === '/office/bookings' || (str_contains($uri, '/office/bookings') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <span class="nav-text">Booking List</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/bookings/create') ?>" class="nav-link <?= str_contains($uri, '/office/bookings/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="nav-text">Create Booking</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. Employees -->
                <div class="sidebar-category" id="cat-employees">
                    <button type="button" class="category-header <?= $isEmployeesActive ? 'has-active open' : '' ?>" id="cat-employees-header" data-title="Employees" onclick="toggleCategory('cat-employees')" aria-expanded="<?= $isEmployeesActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            </span>
                            <span class="category-title">Employees</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isEmployeesActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isEmployeesActive ? 'expanded' : '' ?>" id="cat-employees-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/employees') ?>" class="nav-link <?= ($uri === '/office/employees' || (str_contains($uri, '/office/employees') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                    <span class="nav-text">Employee List</span>
                                </span>
                            </a>
                            <?php if ($role === 'admin'): ?>
                            <a href="<?= url('/office/employees/create') ?>" class="nav-link <?= str_contains($uri, '/office/employees/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    <span class="nav-text">Add Employee</span>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 5. Vehicles -->
                <div class="sidebar-category" id="cat-vehicles">
                    <button type="button" class="category-header <?= $isVehiclesActive ? 'has-active open' : '' ?>" id="cat-vehicles-header" data-title="Vehicles" onclick="toggleCategory('cat-vehicles')" aria-expanded="<?= $isVehiclesActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                            </span>
                            <span class="category-title">Vehicles</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isVehiclesActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isVehiclesActive ? 'expanded' : '' ?>" id="cat-vehicles-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/vehicles') ?>" class="nav-link <?= ($uri === '/office/vehicles' || (str_contains($uri, '/office/vehicles') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span class="nav-text">Vehicle List</span>
                                </span>
                            </a>
                            <?php if ($role === 'admin'): ?>
                            <a href="<?= url('/office/vehicles/create') ?>" class="nav-link <?= str_contains($uri, '/office/vehicles/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="nav-text">Add Vehicle</span>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 6. Users & Permissions (Access Control) -->
                <?php if ($role === 'admin'): ?>
                <div class="sidebar-category" id="cat-access">
                    <button type="button" class="category-header <?= $isAccessActive ? 'has-active open' : '' ?>" id="cat-access-header" data-title="Users & Permissions" onclick="toggleCategory('cat-access')" aria-expanded="<?= $isAccessActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <i class="fa-solid fa-users-gear" style="font-size: 1.05rem;"></i>
                            </span>
                            <span class="category-title">Users &amp; Permissions</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isAccessActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isAccessActive ? 'expanded' : '' ?>" id="cat-access-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/users') ?>" class="nav-link <?= ($uri === '/office/users' || ($uri === '/office/users/' && !str_contains($uri, '/create') && !str_contains($uri, '/roles'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <i class="fa-solid fa-user-shield text-primary" style="font-size: 0.95rem; width: 20px; text-align: center;"></i>
                                    <span class="nav-text">Users</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/users/create') ?>" class="nav-link <?= str_contains($uri, '/office/users/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <i class="fa-solid fa-user-plus text-success" style="font-size: 0.95rem; width: 20px; text-align: center;"></i>
                                    <span class="nav-text">Create Login Account</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/roles') ?>" class="nav-link <?= str_contains($uri, '/office/roles') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <i class="fa-solid fa-shield-halved text-info" style="font-size: 0.95rem; width: 20px; text-align: center;"></i>
                                    <span class="nav-text">Roles &amp; Permissions</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/role-permission') ?>" class="nav-link <?= (str_contains($uri, '/office/role-permission') || str_contains($uri, '/office/users/roles')) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <i class="fa-solid fa-key text-warning" style="font-size: 0.95rem; width: 20px; text-align: center;"></i>
                                    <span class="nav-text">Role Permission Matrix</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 7. Trip Operations -->
                <div class="sidebar-category" id="cat-trips">
                    <button type="button" class="category-header <?= $isTripsActive ? 'has-active open' : '' ?>" id="cat-trips-header" data-title="Trip Operations" onclick="toggleCategory('cat-trips')" aria-expanded="<?= $isTripsActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            </span>
                            <span class="category-title">Trip Operations</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTripsActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isTripsActive ? 'expanded' : '' ?>" id="cat-trips-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/trips/assign') ?>" class="nav-link <?= (str_contains($uri, '/office/trips/assign') || str_contains($uri, '/office/dispatch')) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    <span class="nav-text">Assign Trip to Driver</span>
                                </span>
                            </a>
                            <a href="<?= url('/office/trips/history') ?>" class="nav-link <?= str_contains($uri, '/office/trips/history') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="nav-text">Trip History</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 8. Live Tracking -->
                <div class="sidebar-category" id="cat-tracking">
                    <button type="button" class="category-header <?= $isTrackingActive ? 'has-active open' : '' ?>" id="cat-tracking-header" data-title="Live Tracking" onclick="toggleCategory('cat-tracking')" aria-expanded="<?= $isTrackingActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                            <span class="category-title">Live Tracking</span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isTrackingActive): ?>
                                <span class="active-dot" title="Active Module"></span>
                            <?php endif; ?>
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isTrackingActive ? 'expanded' : '' ?>" id="cat-tracking-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/tracking') ?>" class="nav-link <?= str_contains($uri, '/office/tracking') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="nav-text">Live Tracker Location</span>
                                </span>
                                <span class="badge-dot" style="background:#10B981;"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-badge">
                    <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= e($user['name'] ?? 'Officer') ?></div>
                        <div class="user-role"><?= e($userRole ?? 'Office') ?></div>
                    </div>
                </div>
                <form action="<?= url('/logout') ?>" method="POST" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" title="Sign Out" style="color:#FFF;border-color:rgba(255,255,255,0.2);min-height:36px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebarCollapse()" aria-label="Toggle Navigation Sidebar" title="Toggle Sidebar">
                        <svg class="toggle-icon-sidebar" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <path class="toggle-arrow" d="M15 9l-3 3 3 3"></path>
                        </svg>
                    </button>
                </div>
                <div class="topbar-actions">
                    <span class="badge badge-success"><span class="pulse-indicator"></span> System Operational</span>
                    <span class="badge badge-navy"><?= e(ucfirst(auth_role() ?? 'User')) ?></span>
                </div>
            </header>

            <main class="main-content">
                <!-- Flash Notification Banners -->
                <?php foreach ($flashes as $type => $msg): ?>
                    <div class="alert alert-<?= e($type) ?> alert-dismissible">
                        <span><?= e($msg) ?></span>
                        <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;font-size:1.2rem;">&times;</button>
                    </div>
                <?php endforeach; ?>

                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Mobile Adaptive Bottom Navigation (Phones) -->
    <nav class="bottom-nav" aria-label="Mobile Bottom Navigation">
        <div class="bottom-nav-inner">
            <a href="<?= url('/office/dashboard') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/dashboard') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Overview</span>
            </a>
            <a href="<?= url('/office/dispatch') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/dispatch') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                <span>Dispatch</span>
            </a>
            <a href="<?= url('/office/bookings') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/bookings') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Bookings</span>
            </a>
            <a href="<?= url('/office/invoices') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/invoices') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Billing</span>
            </a>
            <button type="button" class="bottom-nav-item" onclick="toggleSidebar()" style="background:none;border:none;cursor:pointer;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span>Menu</span>
            </button>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
