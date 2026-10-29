<!DOCTYPE html>
<html lang="<?= e(\App\Core\Language::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta() ?>
    <meta name="base-url" content="<?= url('/') ?>">
    <title><?= e($title ?? 'Admin Dashboard') ?> - <?= e($app['name'] ?? 'MANASWI Tours & Travels') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= @filemtime(dirname(__DIR__, 2) . '/public/assets/css/app.css') ?: '2.2' ?>">
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        window.VoyageTranslations = <?= json_encode(\App\Core\Language::getJsTranslations(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
</head>
<body>
    <div id="offlineBanner" class="offline-banner" style="display: none;" role="alert" aria-live="polite">
        <div class="offline-banner-content">
            <span class="offline-banner-icon" aria-hidden="true">⚠️</span>
            <span id="offlineBannerText"><?= e(trim(str_replace(['⚠️', '⚠'], '', __('js.offline_banner')))) ?></span>
        </div>
        <button type="button" class="offline-banner-close" onclick="document.getElementById('offlineBanner').style.display='none'" aria-label="Dismiss">&times;</button>
    </div>

    <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="closeSidebar()" aria-hidden="true"></div>

    <div class="app-wrapper">
        <!-- Frosted Glass Sidebar Navigation (MANASWI Tours & Travels) -->
        <aside class="sidebar" id="sidebar" aria-label="<?= e(__('sidebar.menu')) ?>">
            <div class="sidebar-header">
                <a href="<?= url('/office/dashboard') ?>" class="brand-logo" title="MANASWI Tours & Travels">
                    <img src="<?= asset('images/manaswi-logo.svg') ?>" alt="Manaswi Logo" class="brand-logo-img">
                    <div class="brand-name">
                        <span class="brand-title">MANASWI</span>
                        <span class="brand-subtitle-main"><?= e(__('app.tours_travels', 'Tours & Travels')) ?></span>
                        <span class="brand-tagline"><?= e(__('app.safe_journey_tagline', 'Safe Journey • Happy Memories')) ?></span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" onclick="closeSidebar()" aria-label="<?= e(__('sidebar.close_navigation', 'Close Navigation')) ?>">&times;</button>
            </div>

            <nav class="sidebar-nav">
                <?php
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    $role = auth_role();

                    $isDashboardActive = str_contains($uri, '/office/dashboard');
                    $isBookingsActive  = str_contains($uri, '/office/bookings');
                    $isCustomersActive = str_contains($uri, '/office/customers');
                    $isVehiclesActive  = str_contains($uri, '/office/vehicles');
                    $isDriversActive   = str_contains($uri, '/office/employees');
                    $isFinanceActive   = str_contains($uri, '/office/finance');
                    $isInvoicesActive  = str_contains($uri, '/office/invoices');
                    $isTripsActive     = str_contains($uri, '/office/trips') || str_contains($uri, '/office/dispatch');
                    $isTrackingActive  = str_contains($uri, '/office/tracking');
                    $isAccessActive    = str_contains($uri, '/office/users');
                    $isBackupsActive   = str_contains($uri, '/office/backups');
                ?>

                <!-- 1. Dashboard (Direct Link) -->
                <div class="sidebar-category" id="cat-dashboard">
                    <a href="<?= url('/office/dashboard') ?>" class="category-header direct-link <?= $isDashboardActive ? 'has-active' : '' ?>" id="cat-dashboard-header" data-title="<?= e(__('sidebar.dashboard', 'Dashboard')) ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.dashboard', 'Dashboard')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <?php if ($isDashboardActive): ?>
                                <span class="active-dot" style="display:none;"></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </div>

                <!-- 2. Bookings -->
                <div class="sidebar-category" id="cat-bookings">
                    <button type="button" class="category-header <?= $isBookingsActive ? 'has-active open' : '' ?>" id="cat-bookings-header" data-title="<?= e(__('sidebar.bookings', 'Bookings')) ?>" onclick="toggleCategory('cat-bookings')" aria-expanded="<?= $isBookingsActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.bookings', 'Bookings')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isBookingsActive ? 'expanded' : '' ?>" id="cat-bookings-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/bookings') ?>" class="nav-link <?= ($uri === '/office/bookings' || (str_contains($uri, '/office/bookings') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.booking_list', 'Booking List')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/bookings/create') ?>" class="nav-link <?= str_contains($uri, '/office/bookings/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.create_booking', 'New Booking')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3. Customers -->
                <div class="sidebar-category" id="cat-customers">
                    <button type="button" class="category-header <?= $isCustomersActive ? 'has-active open' : '' ?>" id="cat-customers-header" data-title="<?= e(__('sidebar.customers', 'Customers')) ?>" onclick="toggleCategory('cat-customers')" aria-expanded="<?= $isCustomersActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.customers', 'Customers')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isCustomersActive ? 'expanded' : '' ?>" id="cat-customers-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/customers') ?>" class="nav-link <?= ($uri === '/office/customers' || (str_contains($uri, '/office/customers') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.customer_list', 'Customer Directory')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/customers/create') ?>" class="nav-link <?= str_contains($uri, '/office/customers/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.add_customer', 'Add Customer')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. Vehicles -->
                <div class="sidebar-category" id="cat-vehicles">
                    <button type="button" class="category-header <?= $isVehiclesActive ? 'has-active open' : '' ?>" id="cat-vehicles-header" data-title="<?= e(__('sidebar.vehicles', 'Vehicles')) ?>" onclick="toggleCategory('cat-vehicles')" aria-expanded="<?= $isVehiclesActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a5 5 0 01-.916-9.916 5.002 5.002 0 019.832 0A5.002 5.002 0 0116 17m-7-5l3-3m0 0l3 3m-3-3v12"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.vehicles', 'Vehicles')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isVehiclesActive ? 'expanded' : '' ?>" id="cat-vehicles-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/vehicles') ?>" class="nav-link <?= ($uri === '/office/vehicles' || (str_contains($uri, '/office/vehicles') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.vehicle_list', 'Fleet Overview')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/vehicles/create') ?>" class="nav-link <?= str_contains($uri, '/office/vehicles/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.add_vehicle', 'Add Vehicle')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 5. Drivers (Employees) -->
                <div class="sidebar-category" id="cat-drivers">
                    <button type="button" class="category-header <?= $isDriversActive ? 'has-active open' : '' ?>" id="cat-drivers-header" data-title="<?= e(__('sidebar.employees', 'Drivers')) ?>" onclick="toggleCategory('cat-drivers')" aria-expanded="<?= $isDriversActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.employees', 'Employees')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isDriversActive ? 'expanded' : '' ?>" id="cat-drivers-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/employees') ?>" class="nav-link <?= ($uri === '/office/employees' || (str_contains($uri, '/office/employees') && !str_contains($uri, '/create'))) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.employee_list', 'Driver & Staff List')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/employees/create') ?>" class="nav-link <?= str_contains($uri, '/office/employees/create') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.add_employee', 'Add Driver / Staff')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 6. Billing & Invoices -->
                <!-- 6. Finance & Billing -->
                <div class="sidebar-category" id="cat-finance">
                    <button type="button" class="category-header <?= ($isFinanceActive || $isInvoicesActive) ? 'has-active open' : '' ?>" id="cat-finance-header" data-title="<?= e(__('sidebar.finance', 'Finance')) ?>" onclick="toggleCategory('cat-finance')" aria-expanded="<?= ($isFinanceActive || $isInvoicesActive) ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.finance', 'Finance')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= ($isFinanceActive || $isInvoicesActive) ? 'expanded' : '' ?>" id="cat-finance-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/finance') ?>" class="nav-link <?= ($uri === '/office/finance' || $uri === '/office/finance/dashboard') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.finance_overview', 'Overview')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/accounts') ?>" class="nav-link <?= str_contains($uri, '/office/finance/accounts') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.accounts', 'Accounts & Banks')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/transactions') ?>" class="nav-link <?= str_contains($uri, '/office/finance/transactions') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.transactions', 'Money Transactions')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/expenses') ?>" class="nav-link <?= str_contains($uri, '/office/finance/expenses') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.expenses', 'Expenses')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/payroll') ?>" class="nav-link <?= (str_contains($uri, '/office/finance/payroll') || str_contains($uri, '/office/finance/payslips')) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.payroll', 'Payroll')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/advances') ?>" class="nav-link <?= str_contains($uri, '/office/finance/advances') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.advances', 'Staff Advances')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/finance/refunds') ?>" class="nav-link <?= str_contains($uri, '/office/finance/refunds') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h6a3 3 0 003-3v-6a3 3 0 00-3-3h-1"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.refunds', 'Refunds')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/invoices') ?>" class="nav-link <?= $isInvoicesActive ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.invoices', 'GST Invoices')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 7. Trip Operations -->
                <div class="sidebar-category" id="cat-trips">
                    <button type="button" class="category-header <?= $isTripsActive ? 'has-active open' : '' ?>" id="cat-trips-header" data-title="<?= e(__('sidebar.trip_operations', 'Trip Operations')) ?>" onclick="toggleCategory('cat-trips')" aria-expanded="<?= $isTripsActive ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.trip_operations', 'Trips')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= $isTripsActive ? 'expanded' : '' ?>" id="cat-trips-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/trips/assign') ?>" class="nav-link <?= (str_contains($uri, '/office/trips/assign') || str_contains($uri, '/office/dispatch')) ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.assign_trip', 'Assign Trip')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/trips/history') ?>" class="nav-link <?= str_contains($uri, '/office/trips/history') ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.trip_history', 'Trip History')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 8. Live Tracking -->
                <div class="sidebar-category" id="cat-tracking">
                    <a href="<?= url('/office/tracking') ?>" class="category-header direct-link <?= $isTrackingActive ? 'has-active' : '' ?>" id="cat-tracking-header" data-title="<?= e(__('sidebar.live_tracking', 'Tracking')) ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.live_tracking', 'Tracking')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <span class="pulse-indicator"></span>
                        </span>
                    </a>
                </div>

                <!-- 9. Reports & Audits -->
                <div class="sidebar-category" id="cat-reports">
                    <a href="<?= url('/office/dashboard') ?>#reports" class="category-header direct-link" id="cat-reports-header" data-title="<?= e(__('sidebar.reports', 'Reports')) ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.reports', 'Reports')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </a>
                </div>

                <!-- 10. Settings & Access -->
                <?php if ($role === 'admin'): ?>
                <div class="sidebar-category" id="cat-settings">
                    <button type="button" class="category-header <?= ($isAccessActive || $isBackupsActive) ? 'has-active open' : '' ?>" id="cat-settings-header" data-title="<?= e(__('sidebar.settings', 'Settings')) ?>" onclick="toggleCategory('cat-settings')" aria-expanded="<?= ($isAccessActive || $isBackupsActive) ? 'true' : 'false' ?>">
                        <span class="category-label">
                            <span class="category-icon-wrap">
                                <svg class="category-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span class="category-title"><?= e(__('sidebar.settings', 'Settings')) ?></span>
                        </span>
                        <span class="category-header-right">
                            <svg class="category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    </button>
                    <div class="sidebar-category-items <?= ($isAccessActive || $isBackupsActive) ? 'expanded' : '' ?>" id="cat-settings-items">
                        <div class="category-items-inner">
                            <a href="<?= url('/office/users') ?>" class="nav-link <?= $isAccessActive ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <span class="nav-text"><?= e(__('sidebar.users', 'Staff Accounts')) ?></span>
                                </span>
                            </a>
                            <a href="<?= url('/office/backups') ?>" class="nav-link <?= $isBackupsActive ? 'active' : '' ?>">
                                <span class="nav-link-left">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                    <span class="nav-text"><?= e(__('nav.backup', 'Database Backups')) ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-badge">
                    <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= e($user['name'] ?? 'Admin') ?></div>
                        <div class="user-role"><?= e($userRole ?? 'Super Admin') ?></div>
                    </div>
                </div>
                <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" title="<?= e(__('sidebar.logout', 'Sign Out')) ?>" aria-label="<?= e(__('sidebar.logout', 'Sign Out')) ?>" style="min-height:36px; padding: 0.35rem 0.65rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Mobile Top Navbar (Matches mobile_dashbord.jpeg in LG Screen Forest Teal theme - across all pages) -->
            <?php require dirname(__DIR__) . '/components/mobile_topbar.php'; ?>

            <!-- Frosted Glass Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebarCollapse()" aria-label="Toggle Navigation" title="Toggle Navigation">
                        <svg class="toggle-icon-sidebar" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="toggle-arrow" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <svg class="toggle-icon-mobile" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <!-- Omnisearch Bar (Matches Reference Image) -->
                    <form action="<?= url('/office/bookings') ?>" method="GET" class="topbar-search-form">
                        <svg class="topbar-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="q" class="topbar-search-input" placeholder="<?= e(__('topbar.search_placeholder', 'Search bookings, customers, vehicles, drivers...')) ?>" autocomplete="off">
                    </form>
                </div>

                <div class="topbar-actions">

                    <!-- Change Language Icon Dropdown (Professional Multi-Language Switcher) -->
                    <?php require dirname(__DIR__) . '/components/language_switcher.php'; ?>

                    <!-- Fullscreen Toggle Button -->
                    <button type="button" class="topbar-icon-btn" title="<?= e(__('topbar.fullscreen', 'Toggle Fullscreen')) ?>" aria-label="<?= e(__('topbar.fullscreen', 'Toggle Fullscreen')) ?>" onclick="toggleAppFullscreen()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </button>

                    <!-- User Profile Chip with Dropdown -->
                    <div class="topbar-user-profile" id="topbarUserProfile" onclick="toggleUserDropdown(event)" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" aria-label="<?= e(__('topbar.view_profile', 'User Profile Menu')) ?>">
                        <div class="topbar-avatar-wrap">
                            <img src="<?= asset('images/admin_avatar.jpg') ?>" class="topbar-avatar-img" alt="<?= e($user['name'] ?? 'Admin') ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
                        </div>
                        <div class="topbar-user-meta">
                            <span class="topbar-user-name"><?= e($user['name'] ?? 'Admin') ?></span>
                            <span class="topbar-user-role"><?= e($userRole ?? 'Super Admin') ?></span>
                        </div>
                        <svg class="topbar-user-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"></path>
                        </svg>

                        <!-- Professional Profile Dropdown -->
                        <div class="topbar-user-menu" id="topbarUserMenu" role="menu" aria-labelledby="topbarUserProfile" onclick="event.stopPropagation()">
                            <div class="topbar-menu-user-head">
                                <div class="topbar-menu-head-avatar">
                                    <img src="<?= asset('images/admin_avatar.jpg') ?>" alt="<?= e($user['name'] ?? 'Admin') ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="topbar-avatar" style="display:none;"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
                                </div>
                                <div class="topbar-menu-head-meta">
                                    <strong class="topbar-menu-name"><?= e($user['name'] ?? 'Admin') ?></strong>
                                    <span class="topbar-menu-role-pill"><?= e($userRole ?? 'Super Admin') ?></span>
                                    <span class="topbar-menu-email"><?= e($user['email'] ?? $user['username'] ?? 'admin@manaswi.com') ?></span>
                                </div>
                            </div>
                            <div class="topbar-menu-divider"></div>
                            <a href="<?= url('/office/users') ?>" class="topbar-menu-item" role="menuitem">
                                <div class="topbar-menu-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                                    <i class="fa-solid fa-user-circle"></i>
                                </div>
                                <div class="topbar-menu-text">
                                    <span class="topbar-menu-title"><?= e(__('topbar.view_profile', 'View Profile')) ?></span>
                                    <span class="topbar-menu-sub"><?= e(__('topbar.profile_desc', 'Personal account & settings')) ?></span>
                                </div>
                                <i class="fa-solid fa-chevron-right topbar-menu-arrow"></i>
                            </a>
                            <div class="topbar-menu-divider"></div>
                            <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0; width:100%;" onsubmit="var b = this.querySelector('button'); if(b){ b.disabled = true; b.style.opacity = '0.7'; var t = b.querySelector('.topbar-menu-title'); if(t) t.textContent = '<?= e(__('topbar.sign_out_desc', 'Signing out...')) ?>'; }">
                                <?= csrf_field() ?>
                                <button type="submit" class="topbar-menu-item topbar-logout-item" role="menuitem">
                                    <div class="topbar-menu-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    </div>
                                    <div class="topbar-menu-text">
                                        <span class="topbar-menu-title" style="color: #ef4444;"><?= e(__('sidebar.logout', 'Logout')) ?></span>
                                        <span class="topbar-menu-sub"><?= e(__('topbar.sign_out_desc', 'Sign out of your session')) ?></span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Body -->
            <main class="main-content">
                <!-- Floating Toast Notifications -->
                <?php $activeFlashes = !empty($flashes) ? $flashes : \App\Core\Session::getAllFlashes(); ?>
                <?php if (!empty($activeFlashes)): ?>
                <script>
                    (function() {
                        var f = <?= json_encode($activeFlashes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                        var emit = function() {
                            if (typeof window.vgNotify === 'function') {
                                for (var k in f) {
                                    if (f.hasOwnProperty(k)) window.vgNotify(f[k], k);
                                }
                            } else {
                                setTimeout(emit, 40);
                            }
                        };
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', emit);
                        } else {
                            emit();
                        }
                    })();
                </script>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <!-- Branded Clean Light Footer -->
            <footer class="app-footer">
                <div>
                    &copy; <?= date('Y') ?> <strong>MANASWI <?= e(__('app.tours_travels', 'Tours & Travels')) ?></strong>. <?= e(__('footer.all_rights_reserved', 'All rights reserved.')) ?>
                </div>
                <div>
                    <a href="#privacy"><?= e(__('footer.privacy', 'Privacy Policy')) ?></a>
                    <a href="#terms"><?= e(__('footer.terms', 'Terms & Conditions')) ?></a>
                    <a href="#help"><?= e(__('footer.help', 'Help & Support')) ?></a>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Adaptive Bottom Navigation (Curved Notch Design matching bottom navbar.jpeg) -->
    <nav class="bottom-nav notched-bottom-nav" aria-label="<?= e(__('sidebar.menu', 'Mobile Bottom Navigation')) ?>">
        <div class="notched-nav-bg" aria-hidden="true">
            <svg viewBox="0 0 375 64" preserveAspectRatio="none">
                <path d="M 0,0 L 142,0 C 153,0 159,8 165,19 C 172,32 179,35 187.5,35 C 196,35 203,32 210,19 C 216,8 222,0 233,0 L 375,0 L 375,64 L 0,64 Z" fill="#ffffff" />
            </svg>
        </div>

        <!-- Center Elevated Notch FAB Button (Positioned at exact 50% mathematical center) -->
        <button type="button" class="bottom-nav-fab-btn" onclick="toggleSidebar()" aria-label="<?= e(__('sidebar.menu', 'Open Navigation Menu')) ?>" title="<?= e(__('sidebar.menu', 'Open Navigation Menu')) ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="bottom-nav-inner">
            <a href="<?= url('/office/dashboard') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/dashboard') ? 'active' : '' ?>">
                <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span><?= e(__('sidebar.dashboard', 'Home')) ?></span>
            </a>
            <a href="<?= url('/office/bookings') ?>" class="bottom-nav-item <?= str_contains($uri, '/office/bookings') ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span><?= e(__('sidebar.bookings', 'Bookings')) ?></span>
            </a>

            <!-- Center slot spacer (Ensures left 2 items and right 2 items are 100% symmetric) -->
            <div class="bottom-nav-center-slot" aria-hidden="true"></div>

            <a href="<?= url('/office/dispatch') ?>" class="bottom-nav-item <?= (str_contains($uri, '/office/dispatch') || str_contains($uri, '/office/trips')) ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span><?= e(__('sidebar.trips', 'Trips')) ?></span>
            </a>
            <a href="<?= url('/office/bookings') ?>" class="bottom-nav-item nav-notif-item">
                <div style="position: relative; display: inline-flex;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="nav-badge-pill">3</span>
                </div>
                <span><?= e(__('nav.notifications', 'Notifications')) ?></span>
            </a>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>?v=3.0"></script>
    <script>
        function toggleUserDropdown(e) {
            if (e) {
                e.stopPropagation();
            }
            var chip = document.getElementById('topbarUserProfile');
            if (!chip) return;
            var wasOpen = chip.classList.contains('is-open');

            // Close language dropdown if open
            var langDrop = document.getElementById('vgLanguageDropdown');
            if (langDrop) {
                langDrop.classList.remove('is-open');
                var langBtn = langDrop.querySelector('.vg-lang-btn');
                if (langBtn) langBtn.setAttribute('aria-expanded', 'false');
            }

            if (wasOpen) {
                chip.classList.remove('is-open');
                chip.setAttribute('aria-expanded', 'false');
            } else {
                chip.classList.add('is-open');
                chip.setAttribute('aria-expanded', 'true');
            }
        }

        document.addEventListener('click', function(e) {
            var chip = document.getElementById('topbarUserProfile');
            if (chip && !chip.contains(e.target)) {
                chip.classList.remove('is-open');
                chip.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var chip = document.getElementById('topbarUserProfile');
                if (chip) {
                    chip.classList.remove('is-open');
                    chip.setAttribute('aria-expanded', 'false');
                }
                var langDrop = document.getElementById('vgLanguageDropdown');
                if (langDrop) {
                    langDrop.classList.remove('is-open');
                    var langBtn = langDrop.querySelector('.vg-lang-btn');
                    if (langBtn) langBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });

        function toggleAppFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function(err) {});
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        // Mobile Topbar Dropdown Toggles (Active Across All Pages)
        function toggleMvLang(e) {
            if (e) e.stopPropagation();
            var pDrop = document.getElementById('mvProfileDropdown');
            var lDrop = document.getElementById('mvLangDropdown');
            if (pDrop) pDrop.classList.remove('is-open');
            if (lDrop) lDrop.classList.toggle('is-open');
        }

        function toggleMvProfile(e) {
            if (e) e.stopPropagation();
            var lDrop = document.getElementById('mvLangDropdown');
            var pDrop = document.getElementById('mvProfileDropdown');
            if (lDrop) lDrop.classList.remove('is-open');
            if (pDrop) pDrop.classList.toggle('is-open');
        }

        document.addEventListener('click', function(e) {
            var lDrop = document.getElementById('mvLangDropdown');
            var pDrop = document.getElementById('mvProfileDropdown');
            if (lDrop && !lDrop.contains(e.target)) lDrop.classList.remove('is-open');
            if (pDrop && !pDrop.contains(e.target)) pDrop.classList.remove('is-open');
        });
    </script>
</body>
</html>
