<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('sidebar.customers', 'Customer Directory'),
            'description' => __('nav.customers', 'Verified guest profiles, corporate client accounts, contact history, and reservation statistics.'),
            'image'       => 'header-customers.jpg',
            'badge'       => 'Verified Clients',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.customers', 'Customers'), 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/customers/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.add_customer', 'Add Customer')) . '</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <div class="booking-kpi-grid" id="customersKpiGrid">
        <!-- Card 1: Total Clients -->
        <div class="booking-kpi-card <?= empty($selectedStatus) ? 'is-selected' : '' ?>" data-status="" role="button" tabindex="0" title="Click to view all registered customers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_clients', 'Total Clients')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalAll ?? $totalCustomers ?? 0) ?>"><?= number_format($totalAll ?? $totalCustomers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered guest accounts')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 2: Active Profiles -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'active' ? 'is-selected' : '' ?>" data-status="active" role="button" tabindex="0" title="Click to filter verified active customers">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.active_profiles', 'Active Profiles')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalActive ?? 0) ?>"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'Verified & in good standing')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 3: Booking Guests -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'with_bookings' ? 'is-selected' : '' ?>" data-status="with_bookings" role="button" tabindex="0" title="Click to filter active travelers with reservations">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('table.bookings', 'Active Travelers')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalWithBookings ?? 0) ?>"><?= number_format($totalWithBookings ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Guests with past reservations')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>

        <!-- Card 4: Inactive Accounts -->
        <div class="booking-kpi-card <?= ($selectedStatus ?? '') === 'inactive' ? 'is-selected' : '' ?>" data-status="inactive" role="button" tabindex="0" title="Click to filter dormant inactive clients">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.inactive_accounts', 'Inactive Accounts')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val" data-count="<?= (int)($totalInactive ?? 0) ?>"><?= number_format($totalInactive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.archived', 'Dormant or paused clients')) ?></span>
                <span class="kpi-filter-tag"><i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/customers') ?>" class="filter-bar-compact" id="customerFilterForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
        <div class="fbc-search" id="fbcSearchWrapper">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon" id="fbcSearchIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search customers by code, name, phone, email, city..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="button" class="fbc-search-clear" id="searchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <select name="status" id="statusInput">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>><?= e(__('filter.all_status', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>><?= e(__('status.active', 'Active')) ?></option>
            <option value="with_bookings" <?= ($selectedStatus ?? '') === 'with_bookings' ? 'selected' : '' ?>><?= e(__('table.bookings', 'Active Travelers')) ?></option>
            <option value="inactive" <?= ($selectedStatus ?? '') === 'inactive' ? 'selected' : '' ?>><?= e(__('status.inactive', 'Inactive')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page">
            <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="button" class="fbc-btn" id="filterApplyBtn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <button type="button" class="fbc-btn" id="refreshBtn" title="Refresh" data-no-ajax="true"><i class="fa-solid fa-rotate-right"></i></button>
        </div>

        <div class="fbc-actions-end" id="fbcClearWrapper">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
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
                <h2 class="mv-page-title"><?= e(__('sidebar.customers', 'Customers')) ?></h2>
            </div>
            <a href="<?= url('/office/customers/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span><?= e(__('btn.add_customer', 'Add Customer')) ?></span>
            </a>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/customers') ?>" class="mv-search-form" id="mvSearchForm" data-no-ajax="true" onsubmit="event.preventDefault(); return false;">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico" id="mvSearchIcon"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search by name, code, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="button" class="mv-search-clear" id="mvSearchClearBtn" style="<?= empty($search) ? 'display:none;' : '' ?>" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="button" class="mv-filter-toggle" id="mvFilterToggleBtn" onclick="toggleMobileFilter()" title="Filter options">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </form>

            <!-- Collapsible Mobile Filter Panel -->
            <div class="mv-filter-panel" id="mvFilterPanel" style="display: none;">
                <div class="mv-filter-row">
                    <label for="mvPerPageSelect">Per Page:</label>
                    <select name="per_page" id="mvPerPageSelect">
                        <?php foreach ([10, 20, 30, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Horizontal Scrollable Status Pills -->
        <?php
            $currStatus = strtolower($selectedStatus ?? '');
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/customers' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll" id="customersMvStatusScroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>" data-status="" data-no-ajax="true">
                <span><?= e(__('filter.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalAll ?? $totalCustomers ?? count($customers)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>" data-status="active" data-no-ajax="true">
                <span><?= e(__('status.active', 'Active')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('with_bookings') ?>" class="mv-pill <?= $currStatus === 'with_bookings' ? 'active' : '' ?>" data-status="with_bookings" data-no-ajax="true">
                <span><?= e(__('table.bookings', 'Travelers')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalWithBookings ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' ? 'active' : '' ?>" data-status="inactive" data-no-ajax="true">
                <span><?= e(__('status.inactive', 'Inactive')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalInactive ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Customers Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section" id="customersSection">
        <div class="section-header">
            <h3 class="section-title"><?= e(__('sidebar.customers', 'All Customers')) ?></h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label"><?= e(__('table.total_records', 'Total Records:')) ?></span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totalCustomers ?? count($customers)) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Strict 7-Column Executive Layout) -->
        <div class="modern-table-container">
            <table class="modern-table" id="customersTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th class="text-center" style="width: 80px;"><?= e(__('table.profile', 'Profile')) ?></th>
                        <th style="min-width: 220px;"><?= e(__('table.client_details', 'Client Details')) ?></th>
                        <th style="min-width: 160px;"><?= e(__('table.mobile_no', 'Mobile No.')) ?></th>
                        <th style="min-width: 200px;"><?= e(__('table.address', 'Address')) ?></th>
                        <th class="text-center" style="width: 100px;"><?= e(__('sidebar.bookings', 'Bookings')) ?></th>
                        <th class="text-center" style="width: 110px;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 75px;"><?= e(__('table.actions', 'Actions')) ?></th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <?php foreach ($customers as $i => $c):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($c['status'] ?? 'active');
                        $statusClass = $status === 'active' ? 'completed' : 'cancelled';
                        $statusText = ucfirst($status);
                        $initial = strtoupper(mb_substr($c['name'] ?? 'C', 0, 1));
                        $avatarUrl = customer_avatar_url($c['profile_photo'] ?? $c['avatar'] ?? null);

                        $addressLine = trim((string)($c['address_line'] ?? ''));
                        $cityParts = array_filter([$c['city'] ?? null, $c['state'] ?? null]);
                        $cityState = implode(', ', $cityParts);
                        $pincode = trim((string)($c['postal_code'] ?? ''));
                        if (!empty($pincode) && !empty($cityState)) {
                            $cityState .= ' - ' . $pincode;
                        } elseif (!empty($pincode)) {
                            $cityState = $pincode;
                        }
                    ?>
                    <tr>
                        <!-- 1. Serial Number (#) -->
                        <td class="text-center">
                            <div class="cust-serial-num"><?= $serial ?></div>
                        </td>

                        <!-- 2. Profile Image / Monogram -->
                        <td class="text-center">
                            <div class="cust-table-avatar-wrap">
                                <?php if (!empty($avatarUrl)): ?>
                                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($c['name']) ?>" class="cust-table-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="cust-table-monogram" style="display: none;"><?= e($initial) ?></div>
                                <?php else: ?>
                                    <div class="cust-table-monogram"><?= e($initial) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Client Details (Salutation, Full Name, and Email - Code Removed) -->
                        <td>
                            <div class="cust-client-details-cell">
                                <div class="cust-name-row">
                                    <a href="<?= url('/office/customers/' . $c['id']) ?>" class="cust-full-name" title="View customer profile">
                                        <?= !empty($c['title']) ? e($c['title']) . '. ' : '' ?><?= e($c['name']) ?>
                                    </a>
                                </div>
                                <div class="cust-email-row">
                                    <i class="fa-regular fa-envelope cust-email-icon"></i>
                                    <?php if (!empty($c['email'])): ?>
                                        <a href="mailto:<?= e($c['email']) ?>" class="cust-email-link"><?= e($c['email']) ?></a>
                                    <?php else: ?>
                                        <span class="cust-no-email">No email on record</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- 4. Mobile No. (Primary + Optional Alternate) -->
                        <td>
                            <div class="cust-phone-cell">
                                <a href="tel:<?= e($c['phone']) ?>" class="cust-primary-phone" title="Call customer">
                                    <i class="fa-solid fa-phone cust-phone-icon"></i>
                                    <span><?= e($c['phone']) ?></span>
                                </a>
                                <?php if (!empty($c['alternate_phone'])): ?>
                                    <div class="cust-alt-phone" title="Alternate contact phone">
                                        <i class="fa-solid fa-phone-volume"></i> <?= e($c['alternate_phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 5. Address (Street, City, State, Pincode) -->
                        <td>
                            <div class="cust-address-cell">
                                <?php if (!empty($addressLine) || !empty($cityState)): ?>
                                    <?php if (!empty($addressLine)): ?>
                                        <div class="cust-address-line" title="<?= e($addressLine) ?>">
                                            <i class="fa-solid fa-location-dot cust-address-icon"></i>
                                            <span><?= e($addressLine) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($cityState)): ?>
                                        <div class="cust-address-sub" title="<?= e($cityState) ?>">
                                            <?php if (empty($addressLine)): ?><i class="fa-solid fa-location-dot cust-address-icon"></i><?php endif; ?>
                                            <span><?= e($cityState) ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="cust-no-address">No address on file</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 6. Bookings Count -->
                        <td class="text-center">
                            <div class="cust-bookings-cell">
                                <span class="cust-bookings-num"><?= (int)($c['total_bookings'] ?? 0) ?></span>
                                <span class="cust-bookings-lbl"><?= (int)($c['total_bookings'] ?? 0) === 1 ? 'Trip' : 'Trips' ?></span>
                            </div>
                        </td>

                        <!-- 7. Status Toggle Switch -->
                        <td class="text-center">
                            <div class="cust-toggle-cell" id="custToggleWrap-<?= (int)$c['id'] ?>">
                                <button type="button" 
                                        class="cust-status-toggle <?= $status === 'active' ? 'is-active' : '' ?>" 
                                        role="switch" 
                                        aria-checked="<?= $status === 'active' ? 'true' : 'false' ?>" 
                                        data-id="<?= (int)$c['id'] ?>"
                                        data-name="<?= addslashes(e($c['name'])) ?>"
                                        onclick="toggleCustomerStatus(<?= (int)$c['id'] ?>, this, event)"
                                        title="Click to toggle status (currently <?= $statusText ?>)">
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


                        <!-- 8. Actions Menu (View, Edit, Dispatch, Delete) -->
                        <td>
                            <div class="table-action-buttons">
                                <button type="button" class="action-toggle-btn" onclick="toggleActions(this, event)" title="Actions">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="action-btns-popup">
                                    <a href="<?= url('/office/customers/' . $c['id']) ?>" class="action-btn-table view" title="View Profile & Bookings">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?= url('/office/customers/' . $c['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= url('/office/bookings/create?customer_id=' . $c['id']) ?>" class="action-btn-table dispatch" title="New Booking for Customer">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                    </a>
                                    <button type="button" class="action-btn-table delete" title="Delete Customer" onclick="openDeleteModal(<?= (int)$c['id'] ?>, '<?= addslashes(e($c['name'])) ?>', event)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No customers found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Booking Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="customersCards">
            <?php foreach ($customers as $c):
                $status = strtolower($c['status'] ?? 'active');
                $statusClass = $status === 'active' ? 'active' : 'inactive';
                $statusText = ucfirst($status);
                $initial = strtoupper(mb_substr($c['name'] ?? 'C', 0, 1));
                $avatarUrl = customer_avatar_url($c['profile_photo'] ?? $c['avatar'] ?? null);
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/customers/' . $c['id']) ?>" class="mv-booking-card">
                    <!-- Left: Avatar Image or Monogram -->
                    <div class="mv-thumb-wrap">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= e($avatarUrl) ?>" alt="<?= e($c['name']) ?>" class="mv-thumb-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="mv-thumb-avatar" style="display: none;">
                                <?= e($initial) ?>
                            </div>
                        <?php else: ?>
                            <div class="mv-thumb-avatar">
                                <?= e($initial) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Middle: Name, Phone, Email, Address -->
                    <div class="mv-details">
                        <div class="mv-tour-title" title="<?= e($c['name']) ?>">
                            <?= !empty($c['title']) ? e($c['title']) . '. ' : '' ?><?= e($c['name']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= e($c['phone']) ?></span>
                        </div>
                        <?php if (!empty($c['email'])): ?>
                        <div class="mv-passenger">
                            <i class="fa-regular fa-envelope"></i>
                            <span><?= e($c['email']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php
                            $mAddressLine = trim((string)($c['address_line'] ?? ''));
                            $mCityParts = array_filter([$c['city'] ?? null, $c['state'] ?? null]);
                            $mCityState = implode(', ', $mCityParts);
                            $mPin = trim((string)($c['postal_code'] ?? ''));
                            if (!empty($mPin) && !empty($mCityState)) {
                                $mCityState .= ' - ' . $mPin;
                            } elseif (!empty($mPin)) {
                                $mCityState = $mPin;
                            }
                            $mFullAddress = !empty($mAddressLine) ? $mAddressLine . (!empty($mCityState) ? ', ' . $mCityState : '') : $mCityState;
                        ?>
                        <?php if (!empty($mFullAddress)): ?>
                        <div class="mv-passenger" style="color: #64748B;">
                            <i class="fa-solid fa-location-dot" style="color: #EF4444;"></i>
                            <span><?= e($mFullAddress) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-ticket"></i>
                            <span><?= (int)($c['total_bookings'] ?? 0) ?> Bookings Recorded</span>
                        </div>
                    </div>

                    <!-- Right: Status toggle top, Total bookings bottom -->
                    <div class="mv-right-col">
                        <div class="mv-status-toggle-wrap" onclick="event.stopPropagation()">
                            <button type="button" 
                                    class="cust-status-toggle sm <?= $status === 'active' ? 'is-active' : '' ?>" 
                                    role="switch" 
                                    aria-checked="<?= $status === 'active' ? 'true' : 'false' ?>" 
                                    data-id="<?= (int)$c['id'] ?>"
                                    data-name="<?= addslashes(e($c['name'])) ?>"
                                    onclick="toggleCustomerStatus(<?= (int)$c['id'] ?>, this, event)"
                                    title="Toggle status">
                                <span class="toggle-slider-track">
                                    <span class="toggle-slider-thumb"></span>
                                </span>
                            </button>
                            <span class="mv-toggle-label-text <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="mv-price"><?= (int)($c['total_bookings'] ?? 0) ?> Trips</div>
                    </div>

                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/customers/' . $c['id']) ?>" class="action-btn-table view" title="View Profile">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="<?= url('/office/customers/' . $c['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Customer">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="<?= url('/office/bookings/create?customer_id=' . $c['id']) ?>" class="action-btn-table dispatch" title="New Booking">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </a>
                        <button type="button" class="action-btn-table delete" title="Delete Customer" onclick="openDeleteModal(<?= (int)$c['id'] ?>, '<?= addslashes(e($c['name'])) ?>', event)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No customers found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totalCustomers ?? count($customers);
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['search'] = $search;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/customers?' . http_build_query($q));
        };
    ?>
    <div id="customersPaginationWrapper">
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> customer(s)</div>
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
                                I understand this customer will be permanently deleted.
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
                                Yes, I am completely sure and want to delete this customer.
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
    <form id="deleteCustomerForm" method="POST" style="display: none;">
        <?= csrf_field() ?>
    </form>
</div>

<script>
    /* ========================================================================= */
    /* Complete Customer Directory Live AJAX Filter Engine (Without Page Reload) */
    /* ========================================================================= */
    (function initCustomerAjaxFilter() {
        let abortCtrl = null;
        let searchTimer = null;

        const baseUrl = '<?= url('/office/customers') ?>';

        function getParams(page = 1) {
            const searchInput = document.getElementById('searchInput');
            const mvSearchInput = document.getElementById('mvSearchInput');
            const statusInput = document.getElementById('statusInput');
            const perPageInput = document.getElementById('perPageInput');
            const mvPerPageSelect = document.getElementById('mvPerPageSelect');

            const sVal = (searchInput ? searchInput.value : (mvSearchInput ? mvSearchInput.value : '')).trim();
            const stVal = statusInput ? statusInput.value : '';
            const ppVal = perPageInput ? perPageInput.value : (mvPerPageSelect ? mvPerPageSelect.value : '10');

            const p = new URLSearchParams();
            if (sVal) p.set('search', sVal);
            if (stVal) p.set('status', stVal);
            if (ppVal && ppVal !== '10') p.set('per_page', ppVal);
            if (page && page > 1) p.set('page', page);

            return { params: p, searchVal: sVal, statusVal: stVal, perPageVal: ppVal, pageNum: page };
        }

        function filterCustomersAjax(page = 1, options = {}) {
            const { params, searchVal, statusVal, perPageVal, pageNum } = getParams(page);
            const queryStr = params.toString();
            const targetUrl = queryStr ? `${baseUrl}?${queryStr}` : baseUrl;

            // Abort previous request if still in flight
            if (abortCtrl) {
                abortCtrl.abort();
            }
            abortCtrl = new AbortController();

            // Visual feedback: soft fade and spinning search icon
            const section = document.getElementById('customersSection');
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

                // 1. Update Customers Section (Table, mobile cards, empty state)
                const newSection = doc.getElementById('customersSection');
                const currSection = document.getElementById('customersSection');
                if (newSection && currSection) {
                    currSection.innerHTML = newSection.innerHTML;
                }

                // 2. Update Pagination Wrapper
                const newPagination = doc.getElementById('customersPaginationWrapper');
                const currPagination = document.getElementById('customersPaginationWrapper');
                if (newPagination && currPagination) {
                    currPagination.innerHTML = newPagination.innerHTML;
                }

                // 3. Update Top KPI Summary Grid
                const newKpi = doc.getElementById('customersKpiGrid');
                const currKpi = document.getElementById('customersKpiGrid');
                if (newKpi && currKpi) {
                    currKpi.innerHTML = newKpi.innerHTML;
                    animateKpiNumbers(currKpi);
                }

                // 4. Update Mobile Status Scroll Pills
                const newMvPills = doc.getElementById('customersMvStatusScroll');
                const currMvPills = document.getElementById('customersMvStatusScroll');
                if (newMvPills && currMvPills) {
                    currMvPills.innerHTML = newMvPills.innerHTML;
                }

                // 5. Update Desktop Clear Filter Button
                const newClearWrap = doc.getElementById('fbcClearWrapper');
                const currClearWrap = document.getElementById('fbcClearWrapper');
                if (newClearWrap && currClearWrap) {
                    currClearWrap.innerHTML = newClearWrap.innerHTML;
                }

                // 6. Sync inputs
                const searchInput = document.getElementById('searchInput');
                const mvSearchInput = document.getElementById('mvSearchInput');
                if (searchInput && searchInput.value !== searchVal) searchInput.value = searchVal;
                if (mvSearchInput && mvSearchInput.value !== searchVal) mvSearchInput.value = searchVal;

                const perPageInput = document.getElementById('perPageInput');
                const mvPerPageSelect = document.getElementById('mvPerPageSelect');
                if (perPageInput && perPageInput.value !== perPageVal) perPageInput.value = perPageVal;
                if (mvPerPageSelect && mvPerPageSelect.value !== perPageVal) mvPerPageSelect.value = perPageVal;

                // 7. Update URL in browser without full page reload
                if (!options.skipPushState) {
                    if (options.isTyping) {
                        window.history.replaceState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    } else {
                        window.history.pushState({ searchVal, statusVal, perPageVal, pageNum }, '', targetUrl);
                    }
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') return; // Cancelled by newer request
                console.error('Customer Filter AJAX Error:', err);
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify('Failed to filter customers.', 'error');
                }
            })
            .finally(() => {
                if (section) section.classList.remove('is-filtering');
                if (searchIcon) searchIcon.className = 'fa-solid fa-magnifying-glass fbc-search-icon';
                if (mvSearchIcon) mvSearchIcon.className = 'fa-solid fa-magnifying-glass mv-search-ico';
            });
        }

        // Animated Numeric Counter for KPI Cards
        function animateKpiNumbers(container) {
            if (!container) return;
            const vals = container.querySelectorAll('.booking-kpi-val[data-count]');
            vals.forEach(el => {
                const target = parseInt(el.getAttribute('data-count'), 10) || 0;
                const rawCurrent = el.textContent.replace(/[^0-9]/g, '');
                let startVal = parseInt(rawCurrent, 10);
                if (isNaN(startVal)) startVal = 0;
                if (startVal === target) {
                    el.textContent = target.toLocaleString();
                    return;
                }

                const duration = 300; // ms
                const startTime = performance.now();
                function update(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(1, elapsed / duration);
                    const ease = 1 - Math.pow(1 - progress, 3);
                    const val = Math.round(startVal + (target - startVal) * ease);
                    el.textContent = val.toLocaleString();
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        el.textContent = target.toLocaleString();
                    }
                }
                requestAnimationFrame(update);
            });
        }

        // Live Dynamic KPI Cards and Mobile Pills Syncer (AJAX Real-Time Update)
        function updateCustomerMetrics(metrics) {
            if (!metrics) return;

            const mapping = {
                '': metrics.total,
                'active': metrics.active,
                'with_bookings': metrics.with_bookings,
                'inactive': metrics.inactive
            };

            // 1. Update Desktop Top KPI Cards (#customersKpiGrid)
            const grid = document.getElementById('customersKpiGrid');
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

            // 2. Update Mobile Status Scroll Pills (#customersMvStatusScroll)
            const scrollWrap = document.getElementById('customersMvStatusScroll');
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

            // 3. Update Table Header Total Records Badge (#totalRecordsPill)
            const stInput = document.getElementById('statusInput');
            const currentFilter = (stInput ? stInput.value : '').trim();
            const totalPill = document.getElementById('totalRecordsPill');
            if (totalPill) {
                let relevantCount = metrics.total;
                if (currentFilter === 'active') {
                    relevantCount = metrics.active;
                } else if (currentFilter === 'inactive') {
                    relevantCount = metrics.inactive;
                } else if (currentFilter === 'with_bookings') {
                    relevantCount = metrics.with_bookings;
                }
                if (relevantCount !== undefined) {
                    totalPill.textContent = (parseInt(relevantCount, 10) || 0).toLocaleString();
                }
            }
        }

        // Live Search with Debounce (Desktop)
        const sInput = document.getElementById('searchInput');
        if (sInput) {
            sInput.addEventListener('input', function() {
                const val = this.value;
                const mInput = document.getElementById('mvSearchInput');
                if (mInput && mInput.value !== val) mInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    filterCustomersAjax(1, { isTyping: true });
                }, 280);
            });
            sInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        // Live Search with Debounce (Mobile)
        const mvSInput = document.getElementById('mvSearchInput');
        if (mvSInput) {
            mvSInput.addEventListener('input', function() {
                const val = this.value;
                const dInput = document.getElementById('searchInput');
                if (dInput && dInput.value !== val) dInput.value = val;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    filterCustomersAjax(1, { isTyping: true });
                }, 280);
            });
            mvSInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    filterCustomersAjax(1);
                }
            });
        }

        // Status Select Dropdown
        const stInput = document.getElementById('statusInput');
        if (stInput) {
            stInput.addEventListener('change', function() {
                filterCustomersAjax(1);
            });
        }

        // Per Page Dropdowns
        const ppInput = document.getElementById('perPageInput');
        if (ppInput) {
            ppInput.addEventListener('change', function() {
                const mvSelect = document.getElementById('mvPerPageSelect');
                if (mvSelect) mvSelect.value = this.value;
                filterCustomersAjax(1);
            });
        }

        const mvPpSelect = document.getElementById('mvPerPageSelect');
        if (mvPpSelect) {
            mvPpSelect.addEventListener('change', function() {
                const dSelect = document.getElementById('perPageInput');
                if (dSelect) dSelect.value = this.value;
                filterCustomersAjax(1);
            });
        }

        // Apply Button Click
        const applyBtn = document.getElementById('filterApplyBtn');
        if (applyBtn) {
            applyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                filterCustomersAjax(1);
            });
        }

        // Refresh Button Click
        const refBtn = document.getElementById('refreshBtn');
        if (refBtn) {
            refBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const s = document.getElementById('searchInput');
                const ms = document.getElementById('mvSearchInput');
                const st = document.getElementById('statusInput');
                const pp = document.getElementById('perPageInput');
                const mpp = document.getElementById('mvPerPageSelect');

                if (s) s.value = '';
                if (ms) ms.value = '';
                if (st) st.value = '';
                if (pp) pp.value = '10';
                if (mpp) mpp.value = '10';

                filterCustomersAjax(1);
            });
        }

        // Event delegation for dynamically swapped elements
        document.addEventListener('click', function(e) {
            // 0. Top KPI Summary Cards Click
            const kpiCard = e.target.closest('#customersKpiGrid .booking-kpi-card');
            if (kpiCard) {
                e.preventDefault();
                e.stopPropagation();
                const st = kpiCard.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                // Immediate visual feedback across desktop cards and mobile pills
                document.querySelectorAll('#customersKpiGrid .booking-kpi-card').forEach(c => c.classList.remove('is-selected'));
                kpiCard.classList.add('is-selected');

                document.querySelectorAll('#customersMvStatusScroll .mv-pill').forEach(p => {
                    p.classList.toggle('active', (p.getAttribute('data-status') ?? '') === st);
                });

                filterCustomersAjax(1);
                return;
            }

            // 1. Clear Buttons
            const clearBtn = e.target.closest('#filterClearBtn, #searchClearBtn, #mvSearchClearBtn, .fbc-btn-clear, .mv-search-clear');
            if (clearBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (clearBtn.id === 'searchClearBtn' || clearBtn.id === 'mvSearchClearBtn' || clearBtn.classList.contains('mv-search-clear')) {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                } else {
                    const s = document.getElementById('searchInput');
                    const ms = document.getElementById('mvSearchInput');
                    const st = document.getElementById('statusInput');
                    if (s) s.value = '';
                    if (ms) ms.value = '';
                    if (st) st.value = '';
                }
                filterCustomersAjax(1);
                return;
            }

            // 2. Mobile Status Pills
            const pill = e.target.closest('#customersMvStatusScroll .mv-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                const st = pill.getAttribute('data-status') ?? '';
                const stInput = document.getElementById('statusInput');
                if (stInput) stInput.value = st;

                // Immediate visual feedback
                document.querySelectorAll('#customersMvStatusScroll .mv-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                document.querySelectorAll('#customersKpiGrid .booking-kpi-card').forEach(c => {
                    c.classList.toggle('is-selected', (c.getAttribute('data-status') ?? '') === st);
                });

                filterCustomersAjax(1);
                return;
            }

            // 3. Pagination Links
            const pageLink = e.target.closest('#customersPaginationWrapper .pagination a');
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
                filterCustomersAjax(targetPage);

                // Smooth scroll to top of customer section if needed
                const sec = document.getElementById('customersSection');
                if (sec && window.scrollY > sec.offsetTop + 100) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
        });

        // Keyboard accessibility for Top KPI summary cards
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const kpiCard = document.activeElement ? document.activeElement.closest('#customersKpiGrid .booking-kpi-card') : null;
                if (kpiCard) {
                    e.preventDefault();
                    kpiCard.click();
                }
            }
        });

        // Browser Back/Forward Navigation
        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const sVal = urlParams.get('search') || '';
            const stVal = urlParams.get('status') || '';
            const ppVal = urlParams.get('per_page') || '10';
            const pgVal = parseInt(urlParams.get('page') || '1', 10);

            const s = document.getElementById('searchInput');
            const ms = document.getElementById('mvSearchInput');
            const st = document.getElementById('statusInput');
            const pp = document.getElementById('perPageInput');
            const mpp = document.getElementById('mvPerPageSelect');

            if (s) s.value = sVal;
            if (ms) ms.value = sVal;
            if (st) st.value = stVal;
            if (pp) pp.value = ppVal;
            if (mpp) mpp.value = ppVal;

            filterCustomersAjax(pgVal, { skipPushState: true });
        });

        // Initialize on DOM load
        animateKpiNumbers(document.getElementById('customersKpiGrid'));

        window.filterCustomersAjax = filterCustomersAjax;
        window.animateKpiNumbers = animateKpiNumbers;
        window.updateCustomerMetrics = updateCustomerMetrics;
    })();
    /* ========================================================================= */
    /* Professional Customer Status Toggle (AJAX with tactile feedback)          */
    /* ========================================================================= */
    function toggleCustomerStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'inactive' : 'active';
        const custName = btn.getAttribute('data-name') || 'Customer';

        // Visual loading state
        btn.classList.add('is-loading');
        const spinner = btn.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';

        const url = '<?= url('/office/customers/') ?>' + id + '/toggle-status';
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
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';

            if (data.success) {
                const isNowActive = (data.status === 'active');

                // Update all matching toggle buttons (table and mobile cards)
                document.querySelectorAll(`.cust-status-toggle[data-id="${id}"]`).forEach(b => {
                    if (isNowActive) {
                        b.classList.add('is-active');
                        b.setAttribute('aria-checked', 'true');
                        b.title = 'Click to toggle status (currently Active)';
                    } else {
                        b.classList.remove('is-active');
                        b.setAttribute('aria-checked', 'false');
                        b.title = 'Click to toggle status (currently Inactive)';
                    }
                });

                // Update table status pill
                const wrap = document.getElementById('custToggleWrap-' + id);
                if (wrap) {
                    const pill = wrap.querySelector('.cust-toggle-pill');
                    const dot = wrap.querySelector('.toggle-pulse-dot');
                    const text = wrap.querySelector('.toggle-label-text');
                    if (pill) {
                        pill.className = 'cust-toggle-pill ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (dot) {
                        dot.className = 'toggle-pulse-dot ' + (isNowActive ? 'completed' : 'cancelled');
                    }
                    if (text) text.textContent = data.label;
                }

                // Update mobile cards
                document.querySelectorAll('.mv-booking-card-wrapper').forEach(card => {
                    const mBtn = card.querySelector(`.cust-status-toggle[data-id="${id}"]`);
                    if (mBtn) {
                        const mText = card.querySelector('.mv-toggle-label-text');
                        if (mText) {
                            mText.className = 'mv-toggle-label-text ' + (isNowActive ? 'completed' : 'cancelled');
                            mText.textContent = data.label;
                        }
                    }
                });

                // Update Top KPI cards & mobile status pills live via AJAX
                if (data.metrics && typeof window.updateCustomerMetrics === 'function') {
                    window.updateCustomerMetrics(data.metrics);
                }

                // If currently filtered by active/inactive and customer status was toggled away,
                // smoothly refresh view after grace period so the active list stays in sync
                const currentFilter = (document.getElementById('statusInput') ? document.getElementById('statusInput').value : '').trim();
                if (currentFilter && ((currentFilter === 'active' && !isNowActive) || (currentFilter === 'inactive' && isNowActive))) {
                    setTimeout(() => {
                        if (typeof window.filterCustomersAjax === 'function') {
                            const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                            window.filterCustomersAjax(pg, { skipPushState: true });
                        }
                    }, 650);
                }

                // Notify user
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Customer '${custName}' set to ${data.label}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to update status.', 'error');
                } else {
                    alert(data.message || 'Failed to update status.');
                }
            }
        })
        .catch(err => {
            btn.classList.remove('is-loading');
            if (spinner) spinner.style.display = 'none';
            btn.classList.add('toggle-shake');
            setTimeout(() => btn.classList.remove('toggle-shake'), 400);

            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Failed to update status. Please try again.', 'error');
            } else {
                alert('Network error updating customer status.');
            }
        });
    }

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

    /* ========================================================================= */
    /* Two-Step Delete Modals (Matching delete.jpeg)                              */
    /* ========================================================================= */
    let currentDeleteId = null;
    let currentDeleteName = '';

    function openDeleteModal(id, name, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        // Close any open action popups
        document.querySelectorAll('.action-btns-popup.open, .card-btns-popup.open').forEach(p => p.classList.remove('open'));

        currentDeleteId = id;
        currentDeleteName = name || 'this customer';

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

        // Update modal subtexts with customer context while keeping the core reference phrasing
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

        backdrop.style.display = 'flex';
        // Trigger smooth fade-in
        setTimeout(() => {
            backdrop.classList.add('active');
        }, 10);
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

        if (chk2 && !chk2.checked) {
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

        const deleteUrl = '<?= url('/office/customers/') ?>' + currentDeleteId + '/delete';
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
                    window.vgNotify(data.message || 'Customer deleted successfully.', 'success');
                }
                if (typeof window.filterCustomersAjax === 'function') {
                    const pg = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
                    window.filterCustomersAjax(pg, { skipPushState: true });
                } else {
                    window.location.reload();
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to delete customer.', 'error');
                } else {
                    alert(data.message || 'Failed to delete customer.');
                }
                closeDeleteModal();
            }
        })
        .catch(err => {
            // Fallback to standard form post
            const form = document.getElementById('deleteCustomerForm');
            if (form) {
                form.action = deleteUrl;
                form.submit();
            } else {
                alert('An error occurred while deleting customer profile.');
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
/* ========================================================================= */
/* Sleek AJAX Filtering Animations & Loading State                           */
/* ========================================================================= */
.recent-bookings-section {
    position: relative;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), filter 0.22s ease;
}

.recent-bookings-section.is-filtering {
    opacity: 0.42;
    pointer-events: none;
    filter: grayscale(0.2);
}

.fbc-search {
    position: relative;
    display: flex;
    align-items: center;
}

.fbc-search-clear {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border-radius: 50%;
    transition: color 0.15s ease, background-color 0.15s ease;
}

.fbc-search-clear:hover {
    color: #0F172A;
    background: rgba(0, 0, 0, 0.06);
}

.mv-search-input-box {
    position: relative;
    display: flex;
    align-items: center;
}

.mv-search-clear {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

/* ========================================================================= */
/* Interactive Executive Top KPI Cards (Live AJAX Status Filtering)          */
/* ========================================================================= */
.booking-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.booking-kpi-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 18px 20px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                border-color 0.22s ease,
                background-color 0.22s ease;
}

.booking-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(15, 23, 42, 0.09), 0 4px 10px -2px rgba(15, 23, 42, 0.04);
    border-color: #CBD5E1;
}

.booking-kpi-card:active {
    transform: scale(0.985);
}

.booking-kpi-card .kpi-filter-tag {
    margin-left: auto;
    font-size: 11px;
    color: #94A3B8;
    opacity: 0;
    transform: translateX(-4px);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.booking-kpi-card:hover .kpi-filter-tag {
    opacity: 1;
    transform: translateX(0);
    color: #0284c7;
}

/* Card 1: All Clients Selected */
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

/* Card 2: Active Selected */
.booking-kpi-card[data-status="active"].is-selected {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16), 0 10px 24px rgba(16, 185, 129, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #ECFDF5 100%);
}
.booking-kpi-card[data-status="active"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #10B981;
    font-weight: 700;
}

/* Card 3: Travelers Selected */
.booking-kpi-card[data-status="with_bookings"].is-selected {
    border-color: #F59E0B;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.16), 0 10px 24px rgba(245, 158, 11, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #FFFBEB 100%);
}
.booking-kpi-card[data-status="with_bookings"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #F59E0B;
    font-weight: 700;
}

/* Card 4: Inactive Selected */
.booking-kpi-card[data-status="inactive"].is-selected {
    border-color: #64748B;
    box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.16), 0 10px 24px rgba(100, 116, 139, 0.08);
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
}
.booking-kpi-card[data-status="inactive"].is-selected .kpi-filter-tag {
    opacity: 1;
    color: #64748B;
    font-weight: 700;
}

.booking-kpi-val {
    font-size: 26px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
    margin: 8px 0 4px;
    letter-spacing: -0.02em;
    transition: color 0.2s ease;
}

.booking-kpi-sub {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #64748B;
}

/* ========================================================================= */
/* Professional iOS/SaaS Customer Status Toggle Switch Component             */
/* ========================================================================= */
.cust-toggle-cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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

.cust-status-toggle:active:not(.is-loading) .toggle-slider-thumb {
    width: 21px;
}

/* Small toggle size (for mobile cards) */
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

.cust-status-toggle.sm.is-active .toggle-slider-thumb {
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

.cust-toggle-pill.completed {
    background: #E8F7F0;
    color: #059669;
}

.cust-toggle-pill.cancelled {
    background: #F1F5F9;
    color: #64748B;
}

.toggle-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.toggle-pulse-dot.completed {
    background: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
}

.toggle-pulse-dot.cancelled {
    background: #94A3B8;
}

/* Mobile status toggle wrap */
.mv-status-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 6px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.mv-toggle-label-text {
    font-size: 0.72rem;
    font-weight: 700;
}

.mv-toggle-label-text.completed {
    color: #059669;
}

.mv-toggle-label-text.cancelled {
    color: #64748B;
}

/* ========================================================================= */
/* Customer Directory — Executive 7-Column Layout Styling                     */
/* ========================================================================= */
.cust-serial-num {

    font-weight: 700;
    color: #64748B;
    font-size: 0.88rem;
    padding: 3px 6px;
}

/* 1. Profile Avatar & Monogram */
.cust-table-avatar-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 44px;
    height: 44px;
}

.cust-table-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #176B5B;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.18);
    background: #E8F4F0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cust-table-avatar-img:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.3);
}

.cust-table-monogram {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    text-transform: uppercase;
}

/* Mobile Thumb Avatar */
.mv-thumb-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    object-fit: cover;
    border: 1.5px solid #176B5B;
}

/* 2. Client Details Cell */
.cust-client-details-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cust-name-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cust-full-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0C2B52;
    text-decoration: none;
    transition: color 0.15s ease;
}
.cust-full-name:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-code-pill {
    display: inline-flex;
    align-items: center;
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 1px 7px;
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.cust-email-row {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #64748B;
}

.cust-email-icon {
    font-size: 0.78rem;
    color: #94A3B8;
}

.cust-email-link {
    color: #475569;
    text-decoration: none;
    transition: color 0.15s ease;
}
.cust-email-link:hover {
    color: #176B5B;
    text-decoration: underline;
}

.cust-no-email {
    color: #94A3B8;
    font-style: italic;
    font-size: 0.78rem;
}

/* 3. Mobile Number Cell */
.cust-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.cust-primary-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    transition: color 0.15s ease;
}
.cust-primary-phone:hover {
    color: #176B5B;
}

.cust-phone-icon {
    font-size: 0.76rem;
    color: #176B5B;
}

.cust-alt-phone {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.cust-alt-phone i {
    font-size: 0.72rem;
    color: #94A3B8;
}

/* 4. Bookings Cell */
.cust-bookings-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cust-bookings-num {
    font-size: 1.05rem;
    font-weight: 800;
    color: #16443B;
    line-height: 1.1;
}

.cust-bookings-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* 5. Address Column Cell */
.cust-address-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #334155;
    max-width: 250px;
}

.cust-address-line {
    display: flex;
    align-items: flex-start;
    gap: 0.4rem;
    font-weight: 600;
    color: #0F172A;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
}

.cust-address-icon {
    font-size: 0.78rem;
    color: #EF4444;
    margin-top: 3px;
    flex-shrink: 0;
}

.cust-address-sub {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.3;
}

.cust-no-address {
    font-size: 0.8rem;
    color: #94A3B8;
    font-style: italic;
}

/* 6. Action Button - Delete Button in Popup */
.action-btn-table.delete {
    background-color: #FEE2E2;
    color: #DC2626;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.action-btn-table.delete:hover {
    background-color: #DC2626 !important;
    color: #FFFFFF !important;
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35);
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

/* Soft ambient blue/cyan glass wave accent on bottom edge as seen in delete.jpeg */
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

/* Glowing halo around icons in delete.jpeg */
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

/* Checkbox requirement */
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

/* Action button row matching delete.jpeg */
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
</style>
