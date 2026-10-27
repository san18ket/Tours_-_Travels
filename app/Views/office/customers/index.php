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
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Clients -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.total_clients', 'Total Clients')) ?></span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalCustomers ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('common.all_manifests', 'All registered guest accounts')) ?></span>
            </div>
        </div>

        <!-- Card 2: Active Profiles -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.active_profiles', 'Active Profiles')) ?></span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalActive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span><?= e(__('status.active', 'Verified & in good standing')) ?></span>
            </div>
        </div>

        <!-- Card 3: Booking Guests -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('table.bookings', 'Active Travelers')) ?></span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalWithBookings ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.in_progress', 'Guests with past reservations')) ?></span>
            </div>
        </div>

        <!-- Card 4: Inactive Accounts -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label"><?= e(__('card.inactive_accounts', 'Inactive Accounts')) ?></span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totalInactive ?? 0) ?></div>
            <div class="booking-kpi-sub">
                <span><?= e(__('status.archived', 'Dormant or paused clients')) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/customers') ?>" class="filter-bar-compact" id="customerFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search customers by code, name, phone, email, city..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="status" id="statusInput" onchange="document.getElementById('customerFilterForm').submit()">
            <option value="" <?= empty($selectedStatus) ? 'selected' : '' ?>><?= e(__('filter.all_status', 'All Statuses')) ?></option>
            <option value="active" <?= ($selectedStatus ?? '') === 'active' ? 'selected' : '' ?>><?= e(__('status.active', 'Active')) ?></option>
            <option value="inactive" <?= ($selectedStatus ?? '') === 'inactive' ? 'selected' : '' ?>><?= e(__('status.inactive', 'Inactive')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('customerFilterForm').submit()">
            <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= e(__('filter.per_page', ['count' => $opt])) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', 'Apply')) ?></span></button>
            <a href="<?= url('/office/customers') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/customers') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span><?= e(__('filter.clear', 'Clear')) ?></span></a>
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
            <form method="GET" action="<?= url('/office/customers') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="search" id="mvSearchInput" placeholder="Search by name, code, phone..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/customers' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
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
                <form method="GET" action="<?= url('/office/customers') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvPerPageSelect">Per Page:</label>
                        <select name="per_page" id="mvPerPageSelect" onchange="this.form.submit()">
                            <?php foreach ([10, 20, 30, 50] as $opt): ?>
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
            $makeStatusUrl = function(string $st) use ($search, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($search)) $p['search'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/customers' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span><?= e(__('filter.all', 'All')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalCustomers ?? count($customers)) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('active') ?>" class="mv-pill <?= $currStatus === 'active' ? 'active' : '' ?>">
                <span><?= e(__('status.active', 'Active')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalActive ?? 0) ?></span>
            </a>
            <a href="<?= $makeStatusUrl('inactive') ?>" class="mv-pill <?= $currStatus === 'inactive' ? 'active' : '' ?>">
                <span><?= e(__('status.inactive', 'Inactive')) ?></span>
                <span class="mv-pill-cnt"><?= (int)($totalInactive ?? 0) ?></span>
            </a>
        </div>
    </div>

    <!-- 5. All Customers Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
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
                        <th style="min-width: 260px;"><?= e(__('table.client_details', 'Client Details')) ?></th>
                        <th style="min-width: 170px;"><?= e(__('table.mobile_no', 'Mobile No.')) ?></th>
                        <th class="text-center" style="width: 110px;"><?= e(__('sidebar.bookings', 'Bookings')) ?></th>
                        <th class="text-center" style="width: 120px;"><?= e(__('table.status', 'Status')) ?></th>
                        <th class="text-center" style="width: 65px;"><?= e(__('table.actions', 'Actions')) ?></th>
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

                        <!-- 3. Client Details (Salutation, Full Name, Code Pill, and Email) -->
                        <td>
                            <div class="cust-client-details-cell">
                                <div class="cust-name-row">
                                    <a href="<?= url('/office/customers/' . $c['id']) ?>" class="cust-full-name" title="View customer profile">
                                        <?= !empty($c['title']) ? e($c['title']) . '. ' : '' ?><?= e($c['name']) ?>
                                    </a>
                                    <span class="cust-code-pill" title="Unique customer identifier">
                                        <?= e($c['customer_code']) ?>
                                    </span>
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

                        <!-- 5. Bookings Count -->
                        <td class="text-center">
                            <div class="cust-bookings-cell">
                                <span class="cust-bookings-num"><?= (int)($c['total_bookings'] ?? 0) ?></span>
                                <span class="cust-bookings-lbl"><?= (int)($c['total_bookings'] ?? 0) === 1 ? 'Trip' : 'Trips' ?></span>
                            </div>
                        </td>

                        <!-- 6. Status -->
                        <td class="text-center">
                            <div class="status-cell">
                                <span class="status-dot <?= $statusClass ?>"></span>
                                <span class="status-badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        </td>

                        <!-- 7. Actions Menu -->
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
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7">
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

                    <!-- Middle: Code, Name, Phone, Email -->
                    <div class="mv-details">
                        <div class="mv-code"><?= e($c['customer_code']) ?></div>
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
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-ticket"></i>
                            <span><?= (int)($c['total_bookings'] ?? 0) ?> Bookings Recorded</span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Total bookings bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
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
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> customer(s)</div>
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

<style>
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
</style>
