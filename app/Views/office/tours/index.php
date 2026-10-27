<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header (Preserved on Large Screens >= 769px) -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('tours.title', 'Tour Packages & Itineraries'),
            'description' => __('tours.subtitle', 'Manage curated holiday packages, scenic destinations, day-wise itineraries, and fixed-rate pricing.'),
            'image'       => 'header-tours.jpg',
            'badge'       => 'Curated Journeys',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('nav.tours', 'Tours'), 'url' => ''],
            ],
            'actions'     => (auth_role() === 'admin') ? '<a href="' . url('/office/tours/create') . '" class="btn btn-primary"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>' . e(__('btn.create_tour', 'Create New Tour')) . '</span></a>' : null,
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Top KPI Summary Cards (Exact Bookings Architecture) -->
    <?php
        $publishedCount = 0;
        $draftCount = 0;
        $archivedCount = 0;
        foreach ($tours as $tItem) {
            $st = strtolower($tItem['status'] ?? '');
            if ($st === 'published') $publishedCount++;
            elseif ($st === 'draft') $draftCount++;
            elseif ($st === 'archived') $archivedCount++;
        }
        $totCount = $totalRecords ?? count($tours);
    ?>
    <div class="booking-kpi-grid">
        <!-- Card 1: Total Packages -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Total Tour Packages</span>
                <span class="booking-kpi-icon icon-teal">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($totCount) ?></div>
            <div class="booking-kpi-sub">
                <span>All curated itineraries</span>
            </div>
        </div>

        <!-- Card 2: Published Itineraries -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Published & Live</span>
                <span class="booking-kpi-icon icon-emerald">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($publishedCount) ?></div>
            <div class="booking-kpi-sub">
                <span class="pulse-dot"></span>
                <span>Open for reservations</span>
            </div>
        </div>

        <!-- Card 3: Draft Packages -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Draft Packages</span>
                <span class="booking-kpi-icon icon-amber">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format($draftCount) ?></div>
            <div class="booking-kpi-sub">
                <span>In planning & curation</span>
            </div>
        </div>

        <!-- Card 4: Curated Destinations -->
        <div class="booking-kpi-card">
            <div class="booking-kpi-top">
                <span class="booking-kpi-label">Scenic Circuits</span>
                <span class="booking-kpi-icon icon-navy">
                    <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                </span>
            </div>
            <div class="booking-kpi-val"><?= number_format(max(1, count($tours))) ?></div>
            <div class="booking-kpi-sub">
                <span>Heritage, safari & coast</span>
            </div>
        </div>
    </div>

    <!-- 3. DESKTOP ONLY: Compact Filter Bar (Exact Bookings Architecture) -->
    <form method="GET" action="<?= url('/office/tours') ?>" class="filter-bar-compact" id="tourFilterForm">
        <div class="fbc-search">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="q" id="searchInput" placeholder="Search tour title, destination, city..." value="<?= e($search ?? '') ?>" autocomplete="off">
        </div>

        <select name="category" id="catInput" onchange="document.getElementById('tourFilterForm').submit()">
            <option value=""><?= e(__('common.all_categories', 'All Categories')) ?></option>
            <option value="heritage" <?= ($selectedCategory ?? '') === 'heritage' ? 'selected' : '' ?>><?= e(__('common.heritage', 'Heritage')) ?></option>
            <option value="hill_station" <?= ($selectedCategory ?? '') === 'hill_station' ? 'selected' : '' ?>><?= e(__('common.hill_station', 'Hill Station')) ?></option>
            <option value="pilgrimage" <?= ($selectedCategory ?? '') === 'pilgrimage' ? 'selected' : '' ?>><?= e(__('common.pilgrimage', 'Pilgrimage')) ?></option>
            <option value="wildlife" <?= ($selectedCategory ?? '') === 'wildlife' ? 'selected' : '' ?>><?= e(__('common.wildlife', 'Wildlife Safari')) ?></option>
            <option value="beach" <?= ($selectedCategory ?? '') === 'beach' ? 'selected' : '' ?>><?= e(__('common.beach', 'Beach & Coastal')) ?></option>
        </select>

        <select name="status" id="statusInput" onchange="document.getElementById('tourFilterForm').submit()">
            <option value=""><?= e(__('common.all_statuses', 'All Statuses')) ?></option>
            <option value="published" <?= ($selectedStatus ?? '') === 'published' ? 'selected' : '' ?>><?= e(__('status.published', 'Published')) ?></option>
            <option value="draft" <?= ($selectedStatus ?? '') === 'draft' ? 'selected' : '' ?>><?= e(__('status.draft', 'Draft')) ?></option>
            <option value="archived" <?= ($selectedStatus ?? '') === 'archived' ? 'selected' : '' ?>><?= e(__('status.archived', 'Archived')) ?></option>
        </select>

        <select name="per_page" id="perPageInput" title="Records per page" onchange="document.getElementById('tourFilterForm').submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($perPage ?? 10) == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
            <?php endforeach; ?>
        </select>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Filter"><i class="fa-solid fa-filter"></i> <span>Apply</span></button>
            <a href="<?= url('/office/tours') ?>" class="fbc-btn" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>

        <div class="fbc-actions-end">
            <?php if (!empty($search) || !empty($selectedCategory) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/tours') ?>" class="fbc-btn fbc-btn-clear" title="Clear Filters"><i class="fa-solid fa-xmark"></i> <span>Clear</span></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- 4. MOBILE ONLY: Top App Bar, Search, Drawer & Status Scroll -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back to Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Tour Packages</h2>
            </div>
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/tours/create') ?>" class="mv-add-btn">
                <i class="fa-solid fa-plus"></i>
                <span>Add Tour</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Search Bar & Filter Toggle -->
        <div class="mv-search-bar-wrap">
            <form method="GET" action="<?= url('/office/tours') ?>" class="mv-search-form" id="mvSearchForm">
                <div class="mv-search-input-box">
                    <i class="fa-solid fa-magnifying-glass mv-search-ico"></i>
                    <input type="text" name="q" id="mvSearchInput" placeholder="Search title, city..." value="<?= e($search ?? '') ?>" autocomplete="off">
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/office/tours' . (!empty($selectedStatus) ? '?status=' . urlencode($selectedStatus) : '')) ?>" class="mv-search-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($selectedCategory)): ?>
                        <input type="hidden" name="category" value="<?= e($selectedCategory) ?>">
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
                <form method="GET" action="<?= url('/office/tours') ?>">
                    <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mv-filter-row">
                        <label for="mvCatSelect">Category:</label>
                        <select name="category" id="mvCatSelect" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="heritage" <?= ($selectedCategory ?? '') === 'heritage' ? 'selected' : '' ?>>Heritage</option>
                            <option value="hill_station" <?= ($selectedCategory ?? '') === 'hill_station' ? 'selected' : '' ?>>Hill Station</option>
                            <option value="pilgrimage" <?= ($selectedCategory ?? '') === 'pilgrimage' ? 'selected' : '' ?>>Pilgrimage</option>
                            <option value="wildlife" <?= ($selectedCategory ?? '') === 'wildlife' ? 'selected' : '' ?>>Wildlife Safari</option>
                            <option value="beach" <?= ($selectedCategory ?? '') === 'beach' ? 'selected' : '' ?>>Beach & Coastal</option>
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
            $makeStatusUrl = function(string $st) use ($search, $selectedCategory, $perPage) {
                $p = [];
                if (!empty($st)) $p['status'] = $st;
                if (!empty($selectedCategory)) $p['category'] = $selectedCategory;
                if (!empty($search)) $p['q'] = $search;
                if (!empty($perPage) && $perPage != 10) $p['per_page'] = $perPage;
                return url('/office/tours' . (!empty($p) ? '?' . http_build_query($p) : ''));
            };
        ?>
        <div class="mv-status-scroll">
            <a href="<?= $makeStatusUrl('') ?>" class="mv-pill <?= empty($currStatus) ? 'active' : '' ?>">
                <span>All</span>
                <span class="mv-pill-cnt"><?= (int)$totCount ?></span>
            </a>
            <a href="<?= $makeStatusUrl('published') ?>" class="mv-pill <?= $currStatus === 'published' ? 'active' : '' ?>">
                <span>Published</span>
            </a>
            <a href="<?= $makeStatusUrl('draft') ?>" class="mv-pill <?= $currStatus === 'draft' ? 'active' : '' ?>">
                <span>Draft</span>
            </a>
            <a href="<?= $makeStatusUrl('archived') ?>" class="mv-pill <?= $currStatus === 'archived' ? 'active' : '' ?>">
                <span>Archived</span>
            </a>
        </div>
    </div>

    <!-- 5. All Tours Section: Desktop PMS Modern Table + Mobile App Cards -->
    <section class="recent-bookings-section">
        <div class="section-header">
            <h3 class="section-title">All Curated Packages</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Total Records:</span>
                <span class="total-records-badge" id="totalRecordsPill"><?= number_format($totCount) ?></span>
            </div>
        </div>

        <!-- Modern Table for Large Screens (Exact PMS Table Architecture) -->
        <div class="modern-table-container">
            <table class="modern-table" id="toursTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th style="width: 25%;">Tour Package & Slug</th>
                        <th style="width: 15%;">Category</th>
                        <th style="width: 20%;">Route Itinerary</th>
                        <th class="text-center" style="width: 12%;">Duration</th>
                        <th class="text-center" style="width: 14%;">Base Fare</th>
                        <th class="text-center" style="width: 10%;">Status</th>
                        <th class="text-center" style="width: 55px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="toursTableBody">
                    <?php foreach ($tours as $i => $t):
                        $serial = (($currentPage ?? 1) - 1) * ($perPage ?? 10) + $i + 1;
                        $status = strtolower($t['status'] ?? 'draft');
                        $statusClass = $status === 'published' ? 'completed' : ($status === 'draft' ? 'pending' : 'cancelled');
                        $statusText = ucfirst($status);
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="project-name" style="padding: 3px 8px;"><?= $serial ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                                <?= e($t['title']) ?>
                            </div>
                            <small class="text-muted" style="font-family:monospace;font-size:11px;">
                                /tour/<?= e($t['slug']) ?>
                            </small>
                        </td>
                        <td>
                            <span style="display:inline-block;background:rgba(23,107,91,0.08);color:#16443B;border:1px solid rgba(23,107,91,0.15);border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;">
                                <?= e(str_replace('_', ' ', $t['category'] ?? 'Tour')) ?>
                            </span>
                        </td>
                        <td>
                            <span class="due-date">
                                <?= e($t['pickup_city']) ?> <span style="color:#94a3b8;margin:0 2px;">&rarr;</span> <?= e($t['destination_city']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                <?= (int)$t['duration_days'] ?> Days
                            </div>
                            <small class="text-muted" style="font-size:11px;">
                                <?= (int)$t['duration_nights'] ?> Nights
                            </small>
                        </td>
                        <td class="text-center">
                            <div style="font-weight:800;color:var(--brand-secondary, #16443B);font-size:14px;">
                                <?= format_currency((float)$t['base_price']) ?>
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
                                    <a href="<?= url('/office/bookings/create?tour_id=' . $t['id']) ?>" class="action-btn-table dispatch" title="Create Booking">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                    </a>
                                    <?php if (auth_role() === 'admin'): ?>
                                    <a href="<?= url('/office/tours/' . $t['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Tour Package">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tours)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data-message">
                                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No tour packages found</h4>
                                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards List (Exact Screen 3 Design Architecture) -->
        <div class="cards-grid" id="toursCards">
            <?php
            $presetImgs = [
                'destination-goa.jpg',
                'destination-manali.jpg',
                'destination-dubai.jpg',
                'destination-kerala.jpg',
                'destination-rajasthan.jpg',
                'explore-bus.jpg'
            ];
            foreach ($tours as $idx => $t):
                $status = strtolower($t['status'] ?? 'draft');
                $statusClass = $status === 'published' ? 'published' : ($status === 'draft' ? 'draft' : 'archived');
                $statusText = ucfirst($status);

                $titleLower = strtolower($t['title'] ?? '');
                if (str_contains($titleLower, 'manali') || str_contains($titleLower, 'himachal')) {
                    $cardImg = 'destination-manali.jpg';
                } elseif (str_contains($titleLower, 'dubai')) {
                    $cardImg = 'destination-dubai.jpg';
                } elseif (str_contains($titleLower, 'kerala')) {
                    $cardImg = 'destination-kerala.jpg';
                } elseif (str_contains($titleLower, 'rajasthan') || str_contains($titleLower, 'jaipur') || str_contains($titleLower, 'udaipur')) {
                    $cardImg = 'destination-rajasthan.jpg';
                } elseif (str_contains($titleLower, 'goa')) {
                    $cardImg = 'destination-goa.jpg';
                } else {
                    $cardImg = $presetImgs[($t['id'] ?? $idx) % count($presetImgs)];
                }
            ?>
            <div class="mv-booking-card-wrapper">
                <a href="<?= url('/office/bookings/create?tour_id=' . $t['id']) ?>" class="mv-booking-card">
                    <!-- Left: Destination Thumbnail -->
                    <div class="mv-thumb-wrap">
                        <img src="<?= asset('images/' . $cardImg) ?>" alt="<?= e($t['title']) ?>" class="mv-thumb-img" loading="lazy">
                    </div>

                    <!-- Middle: Details -->
                    <div class="mv-details">
                        <div class="mv-code"><?= strtoupper(e($t['category'] ?? 'TOUR')) ?></div>
                        <div class="mv-tour-title" title="<?= e($t['title']) ?>">
                            <?= e($t['title']) ?>
                        </div>
                        <div class="mv-date-duration">
                            <i class="fa-regular fa-clock"></i>
                            <span><?= (int)$t['duration_days'] ?> Days &bull; <?= (int)$t['duration_nights'] ?> Nights</span>
                        </div>
                        <div class="mv-passenger">
                            <i class="fa-solid fa-route"></i>
                            <span><?= e($t['pickup_city']) ?> &rarr; <?= e($t['destination_city']) ?></span>
                        </div>
                        <div class="mv-vehicle">
                            <i class="fa-solid fa-tag"></i>
                            <span>Fixed Package Pricing</span>
                        </div>
                    </div>

                    <!-- Right: Status pill top, Base price bottom -->
                    <div class="mv-right-col">
                        <span class="mv-status-badge mv-status-<?= $statusClass ?>"><?= $statusText ?></span>
                        <div class="mv-price"><?= format_currency((float)$t['base_price']) ?></div>
                    </div>
                </a>

                <!-- 3-Dots Quick Action Popup -->
                <div class="mv-card-actions">
                    <button type="button" class="mv-action-dots-btn" onclick="toggleCardActions(this, event)" title="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="card-btns-popup">
                        <a href="<?= url('/office/bookings/create?tour_id=' . $t['id']) ?>" class="action-btn-table dispatch" title="Book This Tour">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </a>
                        <?php if (auth_role() === 'admin'): ?>
                        <a href="<?= url('/office/tours/' . $t['id'] . '/edit') ?>" class="action-btn-table edit" title="Edit Package">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($tours)): ?>
            <div class="no-data-message">
                <div class="no-data-icon"><i class="fa-regular fa-folder-open"></i></div>
                <h4 style="font-weight:700;color:#1e293b;margin-bottom:6px;">No tour packages found</h4>
                <p class="text-muted" style="margin:0;">Try adjusting your search or filter parameters.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modern Pagination (Exact Bookings Architecture) -->
    <?php
        $totRec = $totCount;
        $currP = $currentPage ?? 1;
        $pSize = $perPage ?? 10;
        $from = $totRec > 0 ? (($currP - 1) * $pSize) + 1 : 0;
        $to   = min($currP * $pSize, $totRec);
        $buildPageUrl = function(int $p) use ($search, $selectedCategory, $selectedStatus, $pSize) {
            $q = [];
            if (!empty($search)) $q['q'] = $search;
            if (!empty($selectedCategory)) $q['category'] = $selectedCategory;
            if (!empty($selectedStatus)) $q['status'] = $selectedStatus;
            if (!empty($pSize) && $pSize != 10) $q['per_page'] = $pSize;
            $q['page'] = $p;
            return url('/office/tours?' . http_build_query($q));
        };
    ?>
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing <span><?= $from ?>&ndash;<?= $to ?></span> of <span><?= $totRec ?></span> package(s)</div>
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
