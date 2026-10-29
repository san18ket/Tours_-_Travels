<?php
/**
 * MANASWI Tours & Travels — Single Customer Profile View
 * Faithfully redesigned to match view sm.jpeg with premium mobile-app responsiveness
 * Supports both desktop executive layout and native mobile friendly app interface
 */

$customerTitle = !empty($customer['title']) ? trim($customer['title']) : '';
$customerName = (!empty($customerTitle) ? e($customerTitle) . '. ' : '') . e($customer['name']);
$initial = strtoupper(mb_substr($customer['name'] ?? 'C', 0, 1));
$avatarUrl = customer_avatar_url($customer['profile_photo'] ?? $customer['avatar'] ?? null);
$docUrl = customer_document_url($customer['id_document_file'] ?? null);
$isDocPdf = !empty($customer['id_document_file']) && str_ends_with(strtolower($customer['id_document_file']), '.pdf');

$status = strtolower($customer['status'] ?? 'active');
$isActive = ($status === 'active');
$statusText = ucfirst($status);

// Format phone display
$rawPhone = preg_replace('/[^0-9]/', '', $customer['phone'] ?? '');
if (strlen($rawPhone) === 10) {
    $formattedPhone = '+91 ' . substr($rawPhone, 0, 5) . ' ' . substr($rawPhone, 5);
} else {
    $formattedPhone = !empty($customer['phone']) ? '+91 ' . e($customer['phone']) : 'Not provided';
}

$rawAltPhone = preg_replace('/[^0-9]/', '', $customer['alternate_phone'] ?? '');
if (strlen($rawAltPhone) === 10) {
    $formattedAltPhone = '+91 ' . substr($rawAltPhone, 0, 5) . ' ' . substr($rawAltPhone, 5);
} elseif (!empty($customer['alternate_phone'])) {
    $formattedAltPhone = '+91 ' . e($customer['alternate_phone']);
} else {
    $formattedAltPhone = 'None';
}

// WhatsApp URL generator
$cleanPhone = preg_replace('/[^0-9]/', '', $customer['phone'] ?? '');
$waPhone = (strlen($cleanPhone) === 10) ? '91' . $cleanPhone : $cleanPhone;
$waUrl = !empty($waPhone) ? "https://wa.me/{$waPhone}?text=" . urlencode("Hello " . $customer['name'] . ", greetings from MANASWI Tours & Travels.") : "tel:" . e($customer['phone']);

// Format location (City, State)
$locationParts = array_filter([$customer['city'] ?? '', $customer['state'] ?? '']);
$locationDisplay = !empty($locationParts) ? implode(', ', $locationParts) : 'India';

$fullAddress = trim(implode(', ', array_filter([
    $customer['address_line'] ?? '',
    $customer['city'] ?? '',
    $customer['state'] ?? '',
    $customer['postal_code'] ?? '',
    $customer['country'] ?? 'India'
])));

// Compute or load statistics
$hasRealBookings = !empty($bookings);
$totalBookingsCount = count($bookings);
$completedTripsCount = count(array_filter($bookings, fn($b) => in_array($b['status'] ?? '', ['completed', 'confirmed'])));
$totalSpentAmount = array_sum(array_column($bookings, 'total_amount'));

// Statistics
$statTotalBookings = $totalBookingsCount;
$statBookingsTrend = $totalBookingsCount > 0 ? "↑ {$totalBookingsCount}" : "0";

$statTotalTrips = $completedTripsCount;
$statTripsTrend = $completedTripsCount > 0 ? "↑ {$completedTripsCount}" : "0";

$statTotalSpent = $totalSpentAmount;
$statSpentTrend = $totalSpentAmount > 0 ? 'Active' : '—';

if ($hasRealBookings && !empty($bookings[0]['start_date'])) {
    $statLastTripDate = format_date($bookings[0]['start_date']);
    $statLastTripTour = !empty($bookings[0]['tour_title']) ? $bookings[0]['tour_title'] : 'Custom Travel Package';
} else {
    $statLastTripDate = 'No trips yet';
    $statLastTripTour = 'Ready to schedule';
}

// Destination packages for Recent Bookings list
$recentBookingsList = [];
if ($hasRealBookings) {
    // Take up to 4 most recent bookings for the side card
    $recentSubset = array_slice($bookings, 0, 4);
    foreach ($recentSubset as $idx => $b) {
        $thumb = asset('images/destination-manali.jpg');
        $titleLower = strtolower($b['tour_title'] ?? '');
        if (!empty($b['tour_image'])) {
            $thumb = str_starts_with($b['tour_image'], 'http') ? $b['tour_image'] : asset(ltrim($b['tour_image'], '/'));
        } elseif (str_contains($titleLower, 'dubai')) {
            $thumb = asset('images/destination-dubai.jpg');
        } elseif (str_contains($titleLower, 'goa')) {
            $thumb = asset('images/destination-goa.jpg');
        } elseif (str_contains($titleLower, 'kerala')) {
            $thumb = asset('images/destination-kerala.jpg');
        } elseif (str_contains($titleLower, 'rajasthan')) {
            $thumb = asset('images/destination-rajasthan.jpg');
        } elseif ($idx % 3 === 1) {
            $thumb = asset('images/destination-dubai.jpg');
        } elseif ($idx % 3 === 2) {
            $thumb = asset('images/destination-goa.jpg');
        }

        $pax = (int)($b['passenger_count'] ?? 1);
        $dateText = format_date($b['start_date']);
        if (!empty($b['end_date']) && $b['end_date'] !== $b['start_date']) {
            $dateText .= ' – ' . format_date($b['end_date']);
        }

        $bookingTitle = !empty($b['tour_title']) 
            ? $b['tour_title'] 
            : (!empty($b['pickup_address']) && !empty($b['drop_address']) 
                ? ($b['pickup_address'] . ' → ' . $b['drop_address']) 
                : 'Custom Travel Journey');

        $rawStatus = strtolower($b['status'] ?? 'pending');
        $statusLabel = ucwords(str_replace('_', ' ', $rawStatus));

        $recentBookingsList[] = [
            'id' => $b['id'],
            'booking_code' => $b['booking_code'] ?? ('VG-BK-' . str_pad($b['id'], 4, '0', STR_PAD_LEFT)),
            'title' => $bookingTitle,
            'date_range' => $dateText,
            'passengers' => $pax . ($pax === 1 ? ' Person' : ' Guests'),
            'status' => $rawStatus,
            'status_label' => $statusLabel,
            'amount' => format_currency((float)$b['total_amount']),
            'image' => $thumb,
            'payment_status' => !empty($b['payment_status']) ? strtolower($b['payment_status']) : null,
        ];
    }
}
?>

<div class="customer-view-root">
    <!-- Top App Bar / Header Actions -->
    <div class="view-header-bar">
        <div class="view-header-right">
            <a href="<?= url('/office/customers/' . $customer['id'] . '/edit') ?>" class="view-header-btn edit-btn" title="Edit Profile" aria-label="Edit Profile">
                <i class="fa-solid fa-pen"></i>
            </a>
            <div class="view-more-menu-wrapper">
                <button type="button" class="view-header-btn more-btn" id="btnCustomerMore" onclick="toggleMoreMenu(event)" title="More Options" aria-label="More Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div id="customerMoreMenu" class="view-dropdown-menu" style="display: none;">
                    <a href="<?= url('/office/customers/' . $customer['id'] . '/edit') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-user-pen"></i> Edit Profile
                    </a>
                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="view-dropdown-item">
                        <i class="fa-regular fa-calendar-plus"></i> New Travel Booking
                    </a>
                    <a href="javascript:void(0)" onclick="shareCustomerDetails()" class="view-dropdown-item">
                        <i class="fa-solid fa-share-nodes"></i> Share Details
                    </a>
                    <a href="javascript:void(0)" onclick="window.print()" class="view-dropdown-item">
                        <i class="fa-solid fa-print"></i> Print Profile
                    </a>
                    <div class="view-dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="openDeleteModal(<?= (int)$customer['id'] ?>, '<?= addslashes(e($customer['name'])) ?>')" class="view-dropdown-item danger">
                        <i class="fa-regular fa-trash-can"></i> Delete Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Profile Card (Scenic Mountains & Modern Tour Bus Artwork matching view sm.jpeg) -->
    <div class="view-hero-card">
        <div class="view-hero-scenery" style="background-image: url('<?= asset('images/customer-scenic-bus.png') ?>');" aria-hidden="true"></div>
        <div class="view-hero-overlay" aria-hidden="true"></div>

        <div class="view-hero-content">
            <!-- Left Avatar -->
            <div class="view-avatar-wrap">
                <?php if (!empty($avatarUrl)): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($customer['name']) ?>" class="view-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="view-avatar-initials" style="display: none;">
                        <?= e($initial) ?>
                    </div>
                <?php else: ?>
                    <div class="view-avatar-initials">
                        <?= e($initial) ?>
                    </div>
                <?php endif; ?>
                <div class="view-avatar-camera-badge" title="Verified Customer Photo">
                    <i class="fa-solid fa-camera"></i>
                </div>
            </div>

            <!-- Middle Identity Details -->
            <div class="view-hero-info">
                <div class="view-hero-name-row">
                    <h2 class="view-hero-name"><?= $customerName ?></h2>
                    
                    <!-- Live Status Toggle Switch Control (Executive & Mobile Friendly) -->
                    <div class="hero-status-toggle-wrapper" id="heroStatusToggleWrapper" title="Toggle Customer Status">
                        <button type="button" 
                                id="heroStatusToggleBtn"
                                class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                role="switch" 
                                aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                data-id="<?= (int)$customer['id'] ?>"
                                data-name="<?= addslashes(e($customer['name'])) ?>"
                                onclick="toggleCustomerViewStatus(<?= (int)$customer['id'] ?>, this, event)"
                                title="Click to toggle status (currently <?= $statusText ?>)">
                            <span class="toggle-slider-track">
                                <span class="toggle-slider-thumb">
                                    <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </span>
                            </span>
                        </button>
                        <span id="heroStatusPill" class="view-hero-status-pill <?= $isActive ? 'active' : 'inactive' ?>">
                            <span class="status-pulse-dot"></span>
                            <span class="status-label-text" id="heroStatusLabelText"><?= $statusText ?></span>
                        </span>
                    </div>

                    <?php if (!empty($customer['customer_code'])): ?>
                        <span class="view-hero-code-pill"><?= e($customer['customer_code']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="view-hero-meta-list">
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-phone"></i>
                        <a href="tel:<?= e($customer['phone']) ?>"><?= e($formattedPhone) ?></a>
                    </div>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-envelope"></i>
                        <?php if (!empty($customer['email'])): ?>
                            <a href="mailto:<?= e($customer['email']) ?>"><?= e($customer['email']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">Not provided</span>
                        <?php endif; ?>
                    </div>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span title="<?= e($fullAddress) ?>"><?= e($locationDisplay) ?></span>
                    </div>
                </div>

                <!-- Mobile Quick Contact Action Pills -->
                <div class="view-hero-quick-actions">
                    <a href="tel:<?= e($customer['phone']) ?>" class="hero-qa-pill qa-call" title="Call Customer">
                        <i class="fa-solid fa-phone"></i>
                        <span>Call</span>
                    </a>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="hero-qa-pill qa-wa" title="WhatsApp Customer">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                    <?php if (!empty($customer['email'])): ?>
                        <a href="mailto:<?= e($customer['email']) ?>" class="hero-qa-pill qa-email" title="Send Email">
                            <i class="fa-regular fa-envelope"></i>
                            <span>Email</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="hero-qa-pill qa-book" title="Book Journey">
                        <i class="fa-regular fa-calendar-plus"></i>
                        <span>New Trip</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metric Cards (Matching view sm.jpeg with smooth mobile layout) -->
    <div class="view-kpi-grid">
        <!-- KPI 1: Total Bookings -->
        <div class="view-kpi-card" onclick="switchTab('bookings')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-solid fa-suitcase-rolling"></i>
                </div>
                <div class="kpi-trend <?= $totalBookingsCount > 0 ? 'up' : 'neutral' ?>">
                    <?php if ($totalBookingsCount > 0): ?>
                        <i class="fa-solid fa-arrow-up"></i> <?= e($statBookingsTrend) ?>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kpi-label">Total Bookings</div>
            <div class="kpi-value"><?= (int)$statTotalBookings ?></div>
            <div class="kpi-footer-note">All booked travels</div>
        </div>

        <!-- KPI 2: Total Trips -->
        <div class="view-kpi-card" onclick="switchTab('trips')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="kpi-trend <?= $completedTripsCount > 0 ? 'up' : 'neutral' ?>">
                    <?php if ($completedTripsCount > 0): ?>
                        <i class="fa-solid fa-arrow-up"></i> <?= e($statTripsTrend) ?>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kpi-label">Total Trips</div>
            <div class="kpi-value"><?= (int)$statTotalTrips ?></div>
            <div class="kpi-footer-note">Completed journeys</div>
        </div>

        <!-- KPI 3: Total Spent -->
        <div class="view-kpi-card" onclick="switchTab('payments')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap amber">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="kpi-trend <?= $totalSpentAmount > 0 ? 'up' : 'neutral' ?>">
                    <?php if ($totalSpentAmount > 0): ?>
                        <i class="fa-solid fa-arrow-up"></i> <?= e($statSpentTrend) ?>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kpi-label">Total Spent</div>
            <div class="kpi-value text-currency"><?= format_currency((float)$statTotalSpent) ?></div>
            <div class="kpi-footer-note">Lifetime revenue</div>
        </div>

        <!-- KPI 4: Last Trip -->
        <div class="view-kpi-card" onclick="switchTab('bookings')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
                <span class="kpi-tag-recent <?= !$hasRealBookings ? 'empty' : '' ?>"><?= $hasRealBookings ? 'Recent' : 'Status' ?></span>
            </div>
            <div class="kpi-label">Last Trip</div>
            <div class="kpi-value text-compact"><?= e($statLastTripDate) ?></div>
            <div class="kpi-subtext" title="<?= e($statLastTripTour) ?>"><?= e($statLastTripTour) ?></div>
        </div>
    </div>

    <!-- Horizontal Navigation Pill Tabs (Smooth Swipe on Mobile) -->
    <div class="view-nav-tabs-wrapper">
        <div class="view-nav-tabs" role="tablist">
            <button type="button" class="view-nav-pill active" data-tab="overview" onclick="switchTab('overview')" role="tab" aria-selected="true">
                <i class="fa-solid fa-user"></i>
                <span>Overview</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="bookings" onclick="switchTab('bookings')" role="tab" aria-selected="false">
                <i class="fa-regular fa-calendar-days"></i>
                <span>Bookings</span>
                <span class="pill-count"><?= count($bookings) ?: 3 ?></span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="trips" onclick="switchTab('trips')" role="tab" aria-selected="false">
                <i class="fa-solid fa-location-dot"></i>
                <span>Trips</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="payments" onclick="switchTab('payments')" role="tab" aria-selected="false">
                <i class="fa-solid fa-indian-rupee-sign"></i>
                <span>Payments</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="notes" onclick="switchTab('notes')" role="tab" aria-selected="false">
                <i class="fa-regular fa-clipboard"></i>
                <span>Notes</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: OVERVIEW
         ========================================================================= -->
    <div id="tabContent-overview" class="view-tab-pane active" role="tabpanel">
        <div class="view-layout-split">
            <!-- Left Column: Personal Information & Documents -->
            <div class="view-col-main">
                <!-- Personal Information Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge blue">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <h3 class="section-title">Personal Information</h3>
                        </div>
                        <a href="<?= url('/office/customers/' . $customer['id'] . '/edit') ?>" class="section-header-link-btn" title="Edit personal details">
                            <i class="fa-solid fa-pen"></i> <span>Edit</span>
                        </a>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <!-- Row 1 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Full Name</span>
                                <span class="view-kv-value bold"><?= $customerName ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Phone Number</span>
                                <span class="view-kv-value bold">
                                    <a href="tel:<?= e($customer['phone']) ?>"><?= e($formattedPhone) ?></a>
                                </span>
                            </div>

                            <!-- Row 2 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Email Address</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($customer['email'])): ?>
                                        <a href="mailto:<?= e($customer['email']) ?>"><?= e($customer['email']) ?></a>
                                    <?php else: ?>
                                        <em class="text-muted">Not provided</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Alternate Number</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($customer['alternate_phone'])): ?>
                                        <a href="tel:<?= e($customer['alternate_phone']) ?>"><?= e($formattedAltPhone) ?></a>
                                    <?php else: ?>
                                        <em class="text-muted">None</em>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- Row 3 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Date of Birth</span>
                                <span class="view-kv-value">
                                    <?= !empty($customer['date_of_birth']) ? format_date($customer['date_of_birth']) : '<em class="text-muted">Not provided</em>' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Address</span>
                                <span class="view-kv-value address-val">
                                    <i class="fa-solid fa-location-dot" style="color: #176B5B;"></i>
                                    <span><?= e($locationDisplay) ?></span>
                                </span>
                            </div>

                            <!-- Row 4 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Gender</span>
                                <span class="view-kv-value">
                                    <?= !empty($customer['gender']) ? ucfirst(e(str_replace('_', ' ', $customer['gender']))) : '<em class="text-muted">Not provided</em>' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Nationality</span>
                                <span class="view-kv-value flag-val">
                                    <span class="flag-icon" role="img" aria-label="India">🇮🇳</span>
                                    <span><?= !empty($customer['country']) ? e($customer['country']) : 'Indian' ?></span>
                                </span>
                            </div>

                            <!-- Row 5: Live Account Status Toggle & Code -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Account Status</span>
                                <div class="view-kv-status-row">
                                    <button type="button" 
                                            id="infoStatusToggleBtn"
                                            class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isActive ? 'true' : 'false' ?>"
                                            data-id="<?= (int)$customer['id'] ?>"
                                            data-name="<?= addslashes(e($customer['name'])) ?>"
                                            onclick="toggleCustomerViewStatus(<?= (int)$customer['id'] ?>, this, event)"
                                            title="Click to toggle status (currently <?= $statusText ?>)">
                                        <span class="toggle-slider-track">
                                            <span class="toggle-slider-thumb">
                                                <span class="toggle-spinner" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                            </span>
                                        </span>
                                    </button>
                                    <span id="infoStatusPill" class="view-hero-status-pill <?= $isActive ? 'active' : 'inactive' ?>">
                                        <span class="status-pulse-dot"></span>
                                        <span class="status-label-text" id="infoStatusLabelText"><?= $statusText ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Customer ID / Code</span>
                                <span class="view-kv-value bold">
                                    <?= !empty($customer['customer_code']) ? e($customer['customer_code']) : 'VG-CUST-' . str_pad($customer['id'], 4, '0', STR_PAD_LEFT) ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($customer['address_line'])): ?>
                            <div class="view-address-detail-box">
                                <div class="address-detail-label">Complete Registered Billing Address</div>
                                <div class="address-detail-text">
                                    <?= e($customer['address_line']) ?><br>
                                    <?= !empty($customer['city']) ? e($customer['city']) : '' ?><?= !empty($customer['state']) ? ', ' . e($customer['state']) : '' ?><?= !empty($customer['postal_code']) ? ' - ' . e($customer['postal_code']) : '' ?><br>
                                    <strong><?= e($customer['country'] ?? 'India') ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Verification Identity & Document Scan Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge teal">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <h3 class="section-title">Identity &amp; Government Documents</h3>
                        </div>
                        <span class="verified-pill">
                            <i class="fa-solid fa-circle-check"></i> Verified Profile
                        </span>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid" style="margin-bottom: 1rem;">
                            <div class="view-kv-item">
                                <span class="view-kv-label">Document Type</span>
                                <span class="view-kv-value bold">
                                    <?= !empty($customer['id_type']) ? e(ucwords(str_replace('_', ' ', $customer['id_type']))) : 'Aadhaar / National ID' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">ID Number / Reference</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($customer['id_number'])): ?>
                                        <code class="doc-code"><?= e($customer['id_number']) ?></code>
                                    <?php else: ?>
                                        <em class="text-muted">Not specified</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Attached File Preview Component -->
                        <div class="doc-scan-wrapper">
                            <span class="view-kv-label" style="margin-bottom: 0.5rem; display: block;">Attached Scan / File</span>
                            <?php if (!empty($docUrl)): ?>
                                <div class="doc-card-tile">
                                    <div class="doc-tile-left" onclick="openDocPreviewModal()" style="cursor: pointer;" title="Click to view file preview">
                                        <?php if ($isDocPdf): ?>
                                            <div class="doc-file-icon pdf">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?= e($docUrl) ?>" alt="Document Preview" class="doc-thumb-img">
                                        <?php endif; ?>
                                        <div class="doc-tile-info">
                                            <div class="doc-file-name" title="<?= e(basename($customer['id_document_file'])) ?>">
                                                <?= e(basename($customer['id_document_file'])) ?>
                                            </div>
                                            <div class="doc-file-status">
                                                <i class="fa-solid fa-circle-check"></i> Verified file on secure record
                                            </div>
                                        </div>
                                    </div>
                                    <div class="doc-tile-actions">
                                        <button type="button" class="btn-doc-action preview" onclick="openDocPreviewModal()" title="View Document">
                                            <i class="fa-solid fa-eye"></i> <span>Preview</span>
                                        </button>
                                        <a href="<?= e($docUrl) ?>" target="_blank" download class="btn-doc-action download" title="Download Document">
                                            <i class="fa-solid fa-arrow-down-to-bracket"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="doc-empty-state">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>No government ID scan uploaded yet. <a href="<?= url('/office/customers/' . $customer['id'] . '/edit') ?>">Upload document</a></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Bookings & Quick Actions -->
            <div class="view-col-side">
                <!-- Recent Bookings Section -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge blue">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <h3 class="section-title">Recent Bookings</h3>
                            <span class="section-badge-counter <?= !$hasRealBookings ? 'zero' : '' ?>"><?= count($bookings) ?></span>
                        </div>
                        <?php if ($hasRealBookings): ?>
                            <a href="javascript:void(0)" onclick="switchTab('bookings')" class="section-header-link">
                                View All <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="section-card-body p-0">
                        <?php if (!empty($recentBookingsList)): ?>
                            <div class="recent-bookings-list">
                                <?php foreach ($recentBookingsList as $rb): ?>
                                    <div class="recent-booking-card" onclick="location.href='<?= url('/office/bookings/' . $rb['id']) ?>'" role="button" tabindex="0" title="View details for <?= e($rb['booking_code']) ?>">
                                        <div class="booking-thumb-wrap">
                                            <img src="<?= e($rb['image']) ?>" alt="<?= e($rb['title']) ?>" class="booking-thumb-img" onerror="this.src='<?= asset('images/destination-manali.jpg') ?>'">
                                        </div>
                                        <div class="booking-card-mid">
                                            <div class="booking-card-header-mini">
                                                <span class="booking-mini-code"><?= e($rb['booking_code']) ?></span>
                                                <?php if (!empty($rb['payment_status'])): ?>
                                                    <span class="booking-pay-status pay-<?= strtolower($rb['payment_status']) ?>">
                                                        <?= ucfirst($rb['payment_status']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <h4 class="booking-card-title" title="<?= e($rb['title']) ?>"><?= e($rb['title']) ?></h4>
                                            <div class="booking-card-meta">
                                                <span class="meta-date"><i class="fa-regular fa-calendar"></i> <?= e($rb['date_range']) ?></span>
                                                <span class="meta-pax"><i class="fa-solid fa-user-group"></i> <?= e($rb['passengers']) ?></span>
                                            </div>
                                        </div>
                                        <div class="booking-card-right">
                                            <span class="booking-status-pill status-<?= strtolower(str_replace([' ', '_'], '-', $rb['status'])) ?>">
                                                <?= e($rb['status_label']) ?>
                                            </span>
                                            <div class="booking-amount"><?= e($rb['amount']) ?></div>
                                        </div>
                                        <div class="booking-card-arrow">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($bookings) > count($recentBookingsList)): ?>
                                <div class="recent-bookings-footer">
                                    <a href="javascript:void(0)" onclick="switchTab('bookings')" class="recent-view-more-btn">
                                        View All <?= count($bookings) ?> Bookings in Ledger <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Professional Empty State for No Recent Bookings -->
                            <div class="recent-bookings-empty-state">
                                <div class="empty-state-icon-wrap">
                                    <div class="empty-icon-glow"></div>
                                    <div class="empty-icon-badge">
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                    </div>
                                </div>
                                <h4 class="empty-state-heading">No Recent Bookings</h4>
                                <p class="empty-state-desc">
                                    There are currently no travel itineraries or booking records for this customer.
                                </p>
                                <div class="empty-state-actions">
                                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn-create-booking-primary">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                        <span>Create First Booking</span>
                                    </a>
                                </div>
                                <div class="empty-state-meta-hint">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Profile verified &amp; ready for reservations</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Grid (Matching view sm.jpeg) -->
                <div class="view-section-card">
                    <div class="section-card-header compact">
                        <h4 class="quick-actions-title">Quick Actions</h4>
                    </div>
                    <div class="section-card-body pt-0">
                        <div class="quick-actions-grid">
                            <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="quick-action-tile">
                                <div class="qa-icon-wrap blue">
                                    <i class="fa-regular fa-calendar-plus"></i>
                                </div>
                                <span class="qa-text">New Booking</span>
                            </a>

                            <button type="button" class="quick-action-tile" onclick="switchTab('notes'); focusNoteInput();">
                                <div class="qa-icon-wrap purple">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                                <span class="qa-text">Add Note</span>
                            </button>

                            <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="quick-action-tile">
                                <div class="qa-icon-wrap whatsapp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <span class="qa-text">Contact Customer</span>
                            </a>

                            <button type="button" class="quick-action-tile" onclick="shareCustomerDetails()">
                                <div class="qa-icon-wrap cyan">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </div>
                                <span class="qa-text">Share Details</span>
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($customer['notes'])): ?>
                <!-- Notes Preview Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge amber">
                                <i class="fa-regular fa-clipboard"></i>
                            </div>
                            <h3 class="section-title">Special Notes &amp; Preferences</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div class="customer-internal-note-box">
                            <?= nl2br(e($customer['notes'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: BOOKINGS
         ========================================================================= -->
    <div id="tabContent-bookings" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge blue">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Customer Travel Bookings Ledger</h3>
                        <p class="section-subtitle">Comprehensive history of all past and upcoming reservation schedules</p>
                    </div>
                </div>
                <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn-primary-compact">
                    <i class="fa-solid fa-plus"></i> <span>New Booking</span>
                </a>
            </div>
            <div class="section-card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="tab-empty-state">
                        <div class="empty-icon-circle">
                            <i class="fa-solid fa-suitcase-rolling"></i>
                        </div>
                        <h4 class="empty-title">No Direct Bookings Created Yet</h4>
                        <p class="empty-text">This customer currently does not have any direct active bookings recorded in the database system.</p>
                        <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Create Customer Booking
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table (Screen Width >= 769px) -->
                    <div class="desktop-bookings-table">
                        <div class="table-responsive">
                            <table class="view-table">
                                <thead>
                                    <tr>
                                        <th>Booking Ref</th>
                                        <th>Tour / Route</th>
                                        <th>Dates</th>
                                        <th>Guests</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Invoice</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $b): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="table-code-link">
                                                    <code><?= e($b['booking_code']) ?></code>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="table-title-bold"><?= !empty($b['tour_title']) ? e($b['tour_title']) : 'Custom Chauffeur Fleet Service' ?></span>
                                            </td>
                                            <td>
                                                <div class="table-date-meta">
                                                    <?= format_date($b['start_date']) ?>
                                                    <?php if (!empty($b['end_date'])): ?>
                                                        <span class="text-muted">to <?= format_date($b['end_date']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="pax-badge"><i class="fa-solid fa-user-group"></i> <?= (int)$b['passenger_count'] ?></span>
                                            </td>
                                            <td>
                                                <strong class="table-price"><?= format_currency((float)$b['total_amount']) ?></strong>
                                            </td>
                                            <td>
                                                <?= render_status_badge($b['status']) ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($b['invoice_number'])): ?>
                                                    <span class="invoice-badge"><?= e($b['invoice_number']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="table-action-link">
                                                    View <i class="fa-solid fa-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Booking Cards (Screen Width <= 768px - Native App Feel) -->
                    <div class="mobile-bookings-cards-list">
                        <?php foreach ($bookings as $b):
                            $bStatus = strtolower($b['status'] ?? 'pending');
                            $bCode = $b['booking_code'] ?? ('VG-BK-' . $b['id']);
                            $bTour = !empty($b['tour_title']) ? $b['tour_title'] : 'Custom Travel Route';
                            $bDates = format_date($b['start_date']) . (!empty($b['end_date']) ? ' - ' . format_date($b['end_date']) : '');
                            $bAmount = format_currency((float)$b['total_amount']);
                        ?>
                        <div class="mb-booking-card" onclick="location.href='<?= url('/office/bookings/' . $b['id']) ?>'">
                            <div class="mb-booking-card-header">
                                <span class="mb-booking-code"><?= e($bCode) ?></span>
                                <?= render_status_badge($bStatus) ?>
                            </div>
                            <div class="mb-booking-card-body">
                                <h4 class="mb-booking-title"><?= e($bTour) ?></h4>
                                <div class="mb-booking-meta-row">
                                    <span><i class="fa-regular fa-calendar"></i> <?= e($bDates) ?></span>
                                    <span><i class="fa-solid fa-user-group"></i> <?= (int)$b['passenger_count'] ?> Guests</span>
                                </div>
                            </div>
                            <div class="mb-booking-card-footer">
                                <div class="mb-booking-price"><?= $bAmount ?></div>
                                <span class="mb-booking-arrow">View Details <i class="fa-solid fa-chevron-right"></i></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: TRIPS
         ========================================================================= -->
    <div id="tabContent-trips" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge blue">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Trips &amp; Fleet Assignments</h3>
                        <p class="section-subtitle">Vehicle dispatched trips and live driver fleet assignments for this customer</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body p-0">
                <div class="tab-empty-state">
                    <div class="empty-icon-circle">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                    <h4 class="empty-title">Trip Management Overview</h4>
                    <p class="empty-text">Trips dispatched for active bookings will automatically reflect fleet tracking and driver assignments here.</p>
                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn btn-primary btn-sm">
                        + Schedule New Trip
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: PAYMENTS
         ========================================================================= -->
    <div id="tabContent-payments" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge amber">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Payments &amp; Billing Ledger</h3>
                        <p class="section-subtitle">Receipts, invoices, balance settlements, and payment vouchers</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="payments-summary-banner">
                    <div class="pay-stat">
                        <span class="pay-stat-label">Total Invoiced</span>
                        <span class="pay-stat-val"><?= format_currency((float)$statTotalSpent) ?></span>
                    </div>
                    <div class="pay-stat">
                        <span class="pay-stat-label">Total Received</span>
                        <span class="pay-stat-val text-success"><?= format_currency((float)$statTotalSpent) ?></span>
                    </div>
                    <div class="pay-stat">
                        <span class="pay-stat-label">Outstanding Balance</span>
                        <span class="pay-stat-val text-neutral">₹ 0.00</span>
                    </div>
                </div>
                <div class="tab-empty-state" style="padding-top: 1.5rem;">
                    <i class="fa-solid fa-receipt" style="font-size: 2.2rem; color: #94A3B8; margin-bottom: 0.5rem;"></i>
                    <p class="empty-text">All financial invoices generated via the billing module are recorded and linked to customer profile.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: NOTES
         ========================================================================= -->
    <div id="tabContent-notes" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge teal">
                        <i class="fa-regular fa-clipboard"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Customer Notes &amp; Trip Preferences</h3>
                        <p class="section-subtitle">Record dietary preferences, special requirements, and customer VIP notes</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body">
                <form action="<?= url('/office/customers/' . $customer['id']) ?>" method="POST" class="note-editor-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="name" value="<?= e($customer['name']) ?>">
                    <input type="hidden" name="phone" value="<?= e($customer['phone']) ?>">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="customerNotesInput" class="view-kv-label" style="margin-bottom: 0.4rem; display: block;">Internal Notes &amp; Instructions</label>
                        <textarea id="customerNotesInput" name="notes" rows="4" class="form-control" placeholder="Write internal remarks, customer travel habits, dietary requests, or tour preferences..."><?= e($customer['notes'] ?? '') ?></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Save Notes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     DOCUMENT PREVIEW LIGHTBOX MODAL
     ========================================================================= -->
<div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
    <div class="doc-lightbox-container" onclick="event.stopPropagation()">
        <div class="doc-lightbox-header">
            <div class="doc-lightbox-title-wrap">
                <span class="doc-lightbox-icon"><i class="fa-solid fa-file-shield"></i></span>
                <div>
                    <h4 class="doc-lightbox-title" id="docPreviewModalTitle"><?= e(basename($customer['id_document_file'] ?? 'Government ID Verification')) ?></h4>
                    <span class="doc-lightbox-sub">Verified Government Document Preview</span>
                </div>
            </div>
            <div class="doc-lightbox-actions">
                <?php if (!empty($docUrl)): ?>
                    <a href="<?= e($docUrl) ?>" target="_blank" download class="doc-lightbox-btn-download" title="Download Document">
                        <i class="fa-solid fa-download"></i> <span>Download</span>
                    </a>
                <?php endif; ?>
                <button type="button" class="doc-lightbox-close" onclick="closeDocPreviewModal()" aria-label="Close Preview">&times;</button>
            </div>
        </div>
        <div class="doc-lightbox-body" id="docPreviewModalBody">
            <?php if (!empty($docUrl)): ?>
                <?php if ($isDocPdf): ?>
                    <iframe src="<?= e($docUrl) ?>#toolbar=1" class="doc-lightbox-iframe" title="PDF Document Viewer"></iframe>
                <?php else: ?>
                    <div class="doc-lightbox-img-wrap">
                        <img src="<?= e($docUrl) ?>" alt="Document Scan" class="doc-lightbox-img">
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #64748B;">No document file uploaded.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =========================================================================
     DELETE MODAL COMPONENT (Matching delete.jpeg)
     ========================================================================= -->
<div id="deleteModalBackdrop" class="vg-del-backdrop" style="display: none;" onclick="handleBackdropClick(event)">
    <!-- MODAL 1: Initial Delete -->
    <div class="vg-del-modal" id="deleteStep1Modal" role="dialog" aria-modal="true" aria-labelledby="delModalTitle1">
        <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="Close dialog">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="vg-del-body">
            <div class="vg-del-icon-halo">
                <div class="vg-del-icon-circle">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
            </div>

            <div class="vg-del-content">
                <h3 class="vg-del-title" id="delModalTitle1">Delete</h3>
                <p class="vg-del-desc" id="delModalDesc1">
                    This item will be permanently deleted and cannot be recovered.
                </p>

                <div class="vg-del-checkbox-wrap" id="delCheckboxContainer">
                    <label class="vg-del-checkbox-label">
                        <input type="checkbox" id="deleteStep1Checkbox" class="vg-del-checkbox-input" onchange="toggleStep1DeleteBtn()">
                        <span class="vg-del-checkbox-box">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="vg-del-checkbox-text">
                            I understand this customer profile will be permanently deleted.
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="vg-del-actions">
            <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="vg-del-btn-delete disabled" id="deleteStep1Btn" onclick="goToStep2Modal()">
                <i class="fa-solid fa-trash-can"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- MODAL 2: Are you sure? Final Confirmation -->
    <div class="vg-del-modal" id="deleteStep2Modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="delModalTitle2">
        <button type="button" class="vg-del-close-btn" onclick="closeDeleteModal()" aria-label="Close dialog">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="vg-del-body">
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

            <div class="vg-del-content">
                <h3 class="vg-del-title" id="delModalTitle2">Are you sure?</h3>
                <p class="vg-del-desc" id="delModalDesc2">
                    You are about to delete this item. This action cannot be undone.
                </p>

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

        <div class="vg-del-actions">
            <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteCustomerForm" method="POST" action="<?= url('/office/customers/' . $customer['id'] . '/delete') ?>" style="display:inline-block; margin:0;" onsubmit="return handleCustomerDeleteSubmit(event)">
                <?= csrf_field() ?>
                <input type="hidden" name="confirm_delete_related" value="1">
                <button type="submit" class="vg-del-btn-delete disabled" id="deleteConfirmFinalBtn">
                    <i class="fa-solid fa-trash-can"></i>
                    <span id="deleteFinalBtnText">Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     STYLESHEET: FAITHFUL TO VIEW SM.JPEG WITH HIGH-PERFORMANCE MOBILE APP UX
     ========================================================================= -->
<style>
/* CSS Variables & Tokens for Customer Profile Page */
:root {
    --cv-bg-start: #E8F7F0;
    --cv-bg-end: #F8FAFC;
    --cv-card-bg: #FFFFFF;
    --cv-card-border: rgba(226, 232, 240, 0.90);
    --cv-shadow: 0 4px 20px rgba(23, 107, 91, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
    --cv-shadow-hover: 0 10px 25px rgba(23, 107, 91, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
    --cv-primary-blue: #176B5B;
    --cv-primary-dark: #16443B;
    --cv-teal: #176B5B;
    --cv-navy-900: #0F172A;
    --cv-slate-800: #1E293B;
    --cv-slate-600: #475569;
    --cv-slate-400: #94A3B8;
    --cv-green-600: #059669;
    --cv-green-bg: #E6F7F0;
    --cv-radius-card: 20px;
    --cv-radius-pill: 9999px;
}

.customer-view-root {
    max-width: 1220px;
    margin: 0 auto;
    padding: 1.25rem 1rem 3rem 1rem;
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--cv-slate-800);
    -webkit-font-smoothing: antialiased;
}

/* 1. Header Bar (App Bar Actions) */
.view-header-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 0.85rem;
    gap: 0.65rem;
}

.view-header-left {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

.view-header-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: var(--cv-primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
}

.view-header-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 107, 91, 0.16);
    border-color: rgba(23, 107, 91, 0.25);
    color: var(--cv-primary-dark);
}

.view-header-btn:active {
    transform: scale(0.94);
}

.view-header-title-wrap {
    min-width: 0;
}

.view-header-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--cv-slate-400);
    margin-bottom: 2px;
}

.view-header-breadcrumbs a {
    color: var(--cv-slate-600);
    text-decoration: none;
}

.view-header-breadcrumbs a:hover {
    color: var(--cv-primary-blue);
}

.view-header-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.view-header-subtitle {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.view-header-right {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-shrink: 0;
}

/* Dropdown Menu */
.view-more-menu-wrapper {
    position: relative;
}

.view-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.14);
    min-width: 220px;
    padding: 0.5rem;
    z-index: 100;
    animation: fadeInMenu 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeInMenu {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

.view-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.85rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--cv-slate-800);
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.15s ease;
}

.view-dropdown-item:hover {
    background: #F1F5F9;
    color: var(--cv-navy-900);
}

.view-dropdown-item.danger {
    color: #DC2626;
}

.view-dropdown-item.danger:hover {
    background: #FEE2E2;
}

.view-dropdown-divider {
    height: 1px;
    background: #E2E8F0;
    margin: 0.35rem 0;
}

/* 2. Hero Customer Card (Scenic bus & mountains matching view sm.jpeg) */
.view-hero-card {
    background: #FFFFFF;
    border-radius: var(--cv-radius-card);
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 8px 30px -8px rgba(23, 107, 91, 0.08), 0 2px 6px rgba(0, 0, 0, 0.02);
    position: relative;
    overflow: hidden;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
}

.view-hero-scenery {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 48%;
    max-width: 460px;
    background-size: cover;
    background-position: right center;
    background-repeat: no-repeat;
    pointer-events: none;
    z-index: 1;
    mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 0.3) 22%, rgba(0, 0, 0, 0.85) 60%, black 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 0.3) 22%, rgba(0, 0, 0, 0.85) 60%, black 100%);
}

.view-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.85) 55%, rgba(255, 255, 255, 0.15) 100%);
    pointer-events: none;
    z-index: 2;
}

.view-hero-content {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.view-avatar-wrap {
    position: relative;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.1);
    border: 3px solid #FFFFFF;
}

.view-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.view-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    font-size: 2.1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}

.view-avatar-camera-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--cv-primary-blue);
    color: #FFFFFF;
    border: 2.5px solid #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.view-hero-info {
    flex: 1;
    min-width: 0;
}

.view-hero-name-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.view-hero-name {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    line-height: 1.15;
}

.view-hero-status-pill {
    padding: 3px 10px;
    border-radius: var(--cv-radius-pill);
    font-size: 0.74rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.25s ease;
}

.view-hero-status-pill.active {
    background: var(--cv-green-bg);
    color: var(--cv-green-600);
}

.view-hero-status-pill.inactive {
    background: #F1F5F9;
    color: #64748B;
}

/* Hero Status Toggle Control Wrapper */
.hero-status-toggle-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    padding: 3px 8px 3px 4px;
    border-radius: 9999px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    transition: all 0.2s ease;
}

.hero-status-toggle-wrapper:hover {
    border-color: #CBD5E1;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1);
}

/* Master Toggle Button Element */
.cust-status-toggle {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease;
}

.cust-status-toggle:hover {
    transform: scale(1.05);
}

.cust-status-toggle:active {
    transform: scale(0.95);
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

/* Small toggle variant (for KV grid) */
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

.view-kv-status-row {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.view-hero-code-pill {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    border: 1px solid rgba(23, 107, 91, 0.25);
}

.status-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--cv-green-600);
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
    animation: pulseDot 2s infinite;
    transition: background-color 0.25s ease, box-shadow 0.25s ease;
}

.view-hero-status-pill.inactive .status-pulse-dot {
    background: #94A3B8;
    box-shadow: none;
    animation: none;
}

.view-hero-status-pill.active .status-pulse-dot {
    background: var(--cv-green-600);
    animation: pulseDot 2s infinite;
}

@keyframes pulseDot {
    0% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(5, 150, 105, 0); }
    100% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); }
}

/* Floating Toast Notification */
.cust-view-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 999999;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #FFFFFF;
    background: #0F172A;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.22);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transform: translateY(100px);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.cust-view-toast.show {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}

.cust-view-toast.success {
    background: #065F46;
    border: 1px solid #10B981;
}

.cust-view-toast.error {
    background: #991B1B;
    border: 1px solid #EF4444;
}

.view-hero-meta-list {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.25rem;
}

.view-hero-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    color: #334155;
    font-weight: 500;
}

.view-hero-meta-item i {
    color: var(--cv-primary-blue);
    font-size: 0.85rem;
    width: 15px;
    text-align: center;
}

.view-hero-meta-item a {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s ease;
}

.view-hero-meta-item a:hover {
    color: var(--cv-primary-blue);
}

/* Mobile Hero Quick Action Pills */
.view-hero-quick-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}

.hero-qa-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.85rem;
    border-radius: var(--cv-radius-pill);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.18s ease;
    border: 1px solid transparent;
    -webkit-tap-highlight-color: transparent;
}

.hero-qa-pill:active {
    transform: scale(0.95);
}

.hero-qa-pill.qa-call {
    background: #E8F7F0;
    color: #16443B;
    border-color: rgba(23, 107, 91, 0.25);
}

.hero-qa-pill.qa-wa {
    background: #DCFCE7;
    color: #15803D;
    border-color: #BBF7D0;
}

.hero-qa-pill.qa-email {
    background: #F1F5F9;
    color: #334155;
    border-color: #E2E8F0;
}

.hero-qa-pill.qa-book {
    background: #176B5B;
    color: #FFFFFF;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
}

/* 3. KPI Metric Cards Grid (Matching view sm.jpeg) */
.view-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.view-kpi-card {
    background: #FFFFFF;
    border-radius: 18px;
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    padding: 1.1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    -webkit-tap-highlight-color: transparent;
}

.view-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--cv-shadow-hover);
    border-color: rgba(23, 107, 91, 0.25);
}

.view-kpi-card:active {
    transform: scale(0.97);
}

.kpi-card-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.15rem;
}

.kpi-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.kpi-icon-wrap.blue {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
}

.kpi-icon-wrap.amber {
    background: #FEF3C7;
    color: #D97706;
}

.kpi-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
    text-transform: capitalize;
}

.kpi-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    line-height: 1.15;
}

.kpi-value.text-currency {
    font-size: 1.35rem;
}

.kpi-value.text-compact {
    font-size: 1.05rem;
    font-weight: 800;
}

.kpi-trend {
    font-size: 0.74rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.kpi-trend.up {
    color: var(--cv-green-600);
}

.kpi-tag-recent {
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    background: #E8F7F0;
    padding: 1px 6px;
    border-radius: 6px;
}

.kpi-subtext {
    font-size: 0.74rem;
    color: #64748B;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.kpi-footer-note {
    font-size: 0.68rem;
    color: #94A3B8;
    font-weight: 500;
}

/* 4. Horizontal Navigation Pill Tabs (Matching view sm.jpeg) */
.view-nav-tabs-wrapper {
    margin-bottom: 1.25rem;
}

.view-nav-tabs {
    background: #FFFFFF;
    border-radius: var(--cv-radius-pill);
    padding: 6px;
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.view-nav-tabs::-webkit-scrollbar {
    display: none;
}

.view-nav-pill {
    background: transparent;
    color: #64748B;
    font-weight: 600;
    border-radius: var(--cv-radius-pill);
    padding: 0.65rem 1.35rem;
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.88rem;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: inherit;
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
}

.view-nav-pill:hover {
    color: var(--cv-navy-900);
    background: #F8FAFC;
}

.view-nav-pill.active {
    background: var(--cv-primary-blue);
    color: #FFFFFF;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.32);
}

.pill-count {
    background: rgba(255, 255, 255, 0.25);
    font-size: 0.72rem;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
}

.view-nav-pill:not(.active) .pill-count {
    background: #E2E8F0;
    color: #475569;
}

/* 5. Section Cards & Layout Split */
.view-tab-pane {
    display: none;
}

.view-tab-pane.active {
    display: block;
    animation: fadeInTab 0.2s ease;
}

@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.view-layout-split {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 1.25rem;
    align-items: start;
}

.view-col-main, .view-col-side {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.view-section-card {
    background: #FFFFFF;
    border-radius: var(--cv-radius-card);
    border: 1px solid var(--cv-card-border);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.view-section-card:hover {
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
}

.section-card-header {
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.section-card-header.compact {
    padding: 1rem 1.4rem 0.5rem 1.4rem;
    border-bottom: none;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.section-icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    flex-shrink: 0;
}

.section-icon-badge.blue {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
}

.section-icon-badge.teal {
    background: #E6F7F0;
    color: var(--cv-teal);
}

.section-icon-badge.amber {
    background: #FEF3C7;
    color: #D97706;
}

.section-title {
    font-size: 0.96rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 0.76rem;
    color: #64748B;
    margin: 2px 0 0 0;
}

.section-header-link-btn {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    background: #E8F7F0;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}

.section-header-link-btn:hover {
    background: #D1EFE2;
    color: var(--cv-primary-dark);
}

.section-header-link {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.section-header-link:hover {
    color: var(--cv-primary-dark);
    text-decoration: underline;
}

.verified-pill {
    background: #E8F7F0;
    color: var(--cv-green-600);
    padding: 3px 10px;
    border-radius: var(--cv-radius-pill);
    font-size: 0.74rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}

.section-card-body {
    padding: 1.35rem 1.4rem;
}

.section-card-body.p-0 {
    padding: 0;
}

.section-card-body.pt-0 {
    padding-top: 0;
}

/* 6. Key-Value Grid for Personal Info */
.view-kv-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    row-gap: 1.1rem;
    column-gap: 1.25rem;
}

.view-kv-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    min-width: 0;
}

.view-kv-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
    text-transform: capitalize;
}

.view-kv-value {
    font-size: 0.95rem;
    color: var(--cv-slate-800);
    font-weight: 500;
    word-break: break-word;
}

.view-kv-value.bold {
    font-weight: 700;
    color: var(--cv-navy-900);
}

.view-kv-value a {
    color: var(--cv-navy-900);
    text-decoration: none;
    transition: color 0.15s ease;
}

.view-kv-value a:hover {
    color: var(--cv-primary-blue);
}

.view-kv-value.address-val, .view-kv-value.flag-val {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: var(--cv-navy-900);
}

.flag-icon {
    font-size: 1.1rem;
}

.doc-code {
    background: #F1F5F9;
    padding: 2px 7px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.88rem;
    color: #0F172A;
    font-weight: 600;
}

.view-address-detail-box {
    margin-top: 1.1rem;
    padding-top: 1rem;
    border-top: 1px solid #F1F5F9;
}

.address-detail-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.address-detail-text {
    font-size: 0.88rem;
    line-height: 1.5;
    color: #334155;
}

/* 7. Document Scan Component */
.doc-card-tile {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.75rem 0.9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
}

.doc-tile-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.doc-file-icon.pdf {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #FEE2E2;
    color: #DC2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.doc-thumb-img {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #CBD5E1;
    flex-shrink: 0;
}

.doc-tile-info {
    min-width: 0;
}

.doc-file-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--cv-navy-900);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
}

.doc-file-status {
    font-size: 0.72rem;
    color: var(--cv-green-600);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 1px;
}

.doc-tile-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}

.btn-doc-action {
    padding: 0.45rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}

.btn-doc-action.preview {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
}

.btn-doc-action.preview:hover {
    background: #D1EFE2;
}

.btn-doc-action.download {
    background: #F1F5F9;
    color: #475569;
    padding: 0.45rem 0.6rem;
}

.btn-doc-action.download:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.doc-empty-state {
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 12px;
    padding: 1.15rem;
    text-align: center;
    color: #64748B;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.doc-empty-state a {
    color: var(--cv-primary-blue);
    font-weight: 600;
    text-decoration: underline;
}

/* 8. Recent Bookings List & Professional Dynamic States */
.section-badge-counter {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: var(--cv-radius-pill);
    border: 1px solid #D1EFE2;
    line-height: 1;
}

.section-badge-counter.zero {
    background: #F1F5F9;
    color: #94A3B8;
    border-color: #E2E8F0;
}

.kpi-trend.neutral {
    color: #94A3B8;
}

.kpi-tag-recent.empty {
    color: #64748B;
    background: #F1F5F9;
}

.recent-bookings-list {
    display: flex;
    flex-direction: column;
}

.recent-booking-card {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1.4rem;
    border-bottom: 1px solid #F1F5F9;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    color: inherit;
    -webkit-tap-highlight-color: transparent;
}

.recent-booking-card:last-child {
    border-bottom: none;
}

.recent-booking-card:hover {
    background: #F8FAFC;
}

.recent-booking-card:active {
    background: #F1F5F9;
}

.booking-thumb-wrap {
    width: 68px;
    height: 52px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    background: #F1F5F9;
}

.booking-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.booking-card-mid {
    flex: 1;
    min-width: 0;
}

.booking-card-header-mini {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}

.booking-mini-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    background: #E8F7F0;
    padding: 1px 6px;
    border-radius: 4px;
    letter-spacing: 0.02em;
}

.booking-pay-status {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.booking-pay-status.pay-paid {
    background: #DCFCE7;
    color: #15803D;
}

.booking-pay-status.pay-partial,
.booking-pay-status.pay-partially_paid {
    background: #FEF3C7;
    color: #B45309;
}

.booking-pay-status.pay-pending,
.booking-pay-status.pay-unpaid {
    background: #FEE2E2;
    color: #B91C1C;
}

.booking-card-title {
    font-size: 0.92rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0 0 3px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.booking-card-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.74rem;
    color: #64748B;
}

.booking-card-meta i {
    color: var(--cv-primary-blue);
}

.booking-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
    flex-shrink: 0;
}

.booking-status-pill {
    background: #E8F7F0;
    color: var(--cv-green-600);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: var(--cv-radius-pill);
    display: inline-block;
}

.booking-status-pill.status-confirmed,
.booking-status-pill.status-completed {
    background: #E8F7F0;
    color: var(--cv-green-600);
}

.booking-status-pill.status-pending,
.booking-status-pill.status-draft {
    background: #FEF3C7;
    color: #D97706;
}

.booking-status-pill.status-cancelled {
    background: #FEE2E2;
    color: #EF4444;
}

.booking-status-pill.status-on-trip,
.booking-status-pill.status-trip-started,
.booking-status-pill.status-in-progress {
    background: #E0F2FE;
    color: #0284C7;
}

.booking-amount {
    font-size: 0.92rem;
    font-weight: 800;
    color: var(--cv-navy-900);
}

.booking-card-arrow {
    color: #CBD5E1;
    font-size: 0.85rem;
    margin-left: 0.25rem;
    flex-shrink: 0;
    transition: transform 0.15s ease;
}

.recent-booking-card:hover .booking-card-arrow {
    transform: translateX(3px);
    color: var(--cv-primary-blue);
}

.recent-bookings-footer {
    padding: 0.75rem 1.4rem;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
    text-align: center;
}

.recent-view-more-btn {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.recent-view-more-btn:hover {
    color: var(--cv-primary-dark);
    text-decoration: underline;
    transform: translateX(2px);
}

/* =========================================================================
   Professional Empty State for Recent Bookings
   ========================================================================= */
.recent-bookings-empty-state {
    padding: 2.25rem 1.5rem 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.empty-state-icon-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.85rem;
}

.empty-icon-glow {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(23, 107, 91, 0.18) 0%, rgba(23, 107, 91, 0) 70%);
    animation: emptyGlowPulse 3s ease-in-out infinite;
}

@keyframes emptyGlowPulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.15); opacity: 1; }
}

.empty-icon-badge {
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: linear-gradient(135deg, #E8F7F0 0%, #D1EFE2 100%);
    border: 1px solid rgba(23, 107, 91, 0.25);
    color: var(--cv-primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.08);
}

.empty-state-heading {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0 0 0.4rem 0;
    letter-spacing: -0.01em;
}

.empty-state-desc {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0 0 1.25rem 0;
    line-height: 1.55;
    max-width: 290px;
}

.empty-state-actions {
    display: flex;
    justify-content: center;
    width: 100%;
}

.btn-create-booking-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #176B5B 0%, #16443B 100%);
    color: #FFFFFF;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.65rem 1.25rem;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    -webkit-tap-highlight-color: transparent;
}

.btn-create-booking-primary:hover {
    color: #FFFFFF;
    background: linear-gradient(135deg, #16443B 0%, #0F2D27 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(23, 107, 91, 0.35);
}

.btn-create-booking-primary:active {
    transform: scale(0.97);
}

.empty-state-meta-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1.15rem;
    padding: 0.35rem 0.85rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: var(--cv-radius-pill);
    font-size: 0.72rem;
    color: #64748B;
    font-weight: 600;
}

.empty-state-meta-hint i {
    color: var(--cv-green-600);
}

/* 9. Quick Actions 4-Tile Grid (Matching view sm.jpeg) */
.quick-actions-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.quick-action-tile {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 0.95rem 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    text-decoration: none;
    color: var(--cv-slate-800);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #E2E8F0;
    -webkit-tap-highlight-color: transparent;
}

.quick-action-tile:hover {
    background: #FFFFFF;
    border-color: rgba(23, 107, 91, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.1);
}

.quick-action-tile:active {
    transform: scale(0.95);
}

.qa-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}

.qa-icon-wrap.blue {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
}

.qa-icon-wrap.purple {
    background: #F3E8FF;
    color: #9333EA;
}

.qa-icon-wrap.whatsapp {
    background: #DCFCE7;
    color: #16A34A;
}

.qa-icon-wrap.cyan {
    background: #E0F2FE;
    color: #0284C7;
}

.qa-text {
    font-size: 0.76rem;
    font-weight: 700;
    text-align: center;
    color: var(--cv-navy-900);
    line-height: 1.2;
}

.customer-internal-note-box {
    background: #FEF3C7;
    border-left: 4px solid #D97706;
    padding: 0.85rem 1rem;
    border-radius: 0 10px 10px 0;
    font-size: 0.85rem;
    line-height: 1.5;
    color: #78350F;
}

/* 10. Bookings Tab Table & Mobile Cards */
.btn-primary-compact {
    background: var(--cv-primary-blue);
    color: #FFFFFF;
    padding: 0.45rem 0.95rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}

.btn-primary-compact:hover {
    background: var(--cv-primary-dark);
}

.table-responsive {
    overflow-x: auto;
}

.view-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.view-table th {
    background: #F8FAFC;
    color: #64748B;
    font-weight: 700;
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #E2E8F0;
    text-align: left;
}

.view-table td {
    padding: 0.95rem 1.25rem;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
}

.view-table tbody tr:hover {
    background: #F8FAFC;
}

.table-code-link {
    font-weight: 700;
    color: var(--cv-primary-blue);
    text-decoration: none;
}

.table-title-bold {
    font-weight: 700;
    color: var(--cv-navy-900);
}

.table-date-meta {
    font-size: 0.82rem;
    color: #334155;
}

.pax-badge {
    background: #F1F5F9;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
}

.table-price {
    font-weight: 800;
    color: var(--cv-navy-900);
}

.invoice-badge {
    font-family: monospace;
    font-size: 0.78rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    padding: 2px 6px;
    border-radius: 4px;
}

.table-action-link {
    color: var(--cv-primary-blue);
    font-weight: 700;
    font-size: 0.82rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.mobile-bookings-cards-list {
    display: none;
}

/* Mobile Booking Card */
.mb-booking-card {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #F1F5F9;
    cursor: pointer;
    transition: background 0.15s ease;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    -webkit-tap-highlight-color: transparent;
}

.mb-booking-card:last-child {
    border-bottom: none;
}

.mb-booking-card:active {
    background: #F8FAFC;
}

.mb-booking-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mb-booking-code {
    font-family: monospace;
    font-weight: 700;
    color: var(--cv-primary-blue);
    font-size: 0.82rem;
}

.mb-booking-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0;
}

.mb-booking-meta-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    font-size: 0.76rem;
    color: #64748B;
    margin-top: 2px;
}

.mb-booking-meta-row i {
    color: var(--cv-primary-blue);
}

.mb-booking-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.45rem;
    border-top: 1px dashed #F1F5F9;
    margin-top: 0.25rem;
}

.mb-booking-price {
    font-size: 1rem;
    font-weight: 800;
    color: var(--cv-navy-900);
}

.mb-booking-arrow {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--cv-primary-blue);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.tab-empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #E8F7F0;
    color: var(--cv-primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin: 0 auto 0.75rem auto;
}

.empty-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--cv-navy-900);
    margin: 0 0 0.35rem 0;
}

.empty-text {
    font-size: 0.84rem;
    color: #64748B;
    max-width: 420px;
    margin: 0 auto 1.25rem auto;
}

/* Payments summary banner */
.payments-summary-banner {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.pay-stat {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.pay-stat-label {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748B;
}

.pay-stat-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--cv-navy-900);
}

.pay-stat-val.text-success { color: #059669; }
.pay-stat-val.text-neutral { color: #64748B; }

/* 11. Document Lightbox Modal */
.doc-lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.doc-lightbox-container {
    background: #FFFFFF;
    border-radius: 20px;
    max-width: 860px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.doc-lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #E2E8F0;
}

.doc-lightbox-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.doc-lightbox-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #E8F7F0;
    color: var(--cv-primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.doc-lightbox-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.doc-lightbox-sub {
    font-size: 0.74rem;
    color: #64748B;
}

.doc-lightbox-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.doc-lightbox-btn-download {
    background: #E8F7F0;
    color: var(--cv-primary-blue);
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.doc-lightbox-close {
    background: none;
    border: none;
    font-size: 1.75rem;
    line-height: 1;
    color: #64748B;
    cursor: pointer;
    padding: 0 0.25rem;
}

.doc-lightbox-body {
    padding: 1rem;
    overflow: auto;
    background: #0F172A;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 480px;
}

.doc-lightbox-iframe {
    width: 100%;
    height: 65vh;
    border: none;
    background: #FFFFFF;
    border-radius: 8px;
}

.doc-lightbox-img-wrap {
    max-width: 100%;
    max-height: 70vh;
    display: flex;
    justify-content: center;
}

.doc-lightbox-img {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 8px;
}

/* 12. Delete Modal Styles (Matches delete.jpeg) */
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

/* ==========================================================================
   MOBILE RESPONSIVENESS (Screens <= 768px matching view sm.jpeg & dashboard)
   ========================================================================== */
@media (max-width: 768px) {
    .customer-view-root {
        padding: 0.65rem 0.65rem 95px 0.65rem !important;
        /* 95px padding-bottom provides full clearance for the docked bottom bar */
    }

    /* 1. Header Bar on Mobile */
    .view-header-bar {
        margin-bottom: 0.85rem;
        gap: 0.65rem;
    }

    .view-header-breadcrumbs {
        display: none;
    }

    .view-header-title {
        font-size: 1.15rem;
    }

    .view-header-subtitle {
        font-size: 0.75rem;
    }

    .view-header-btn {
        width: 38px;
        height: 38px;
        font-size: 0.9rem;
    }

    /* 2. Hero Profile Card on Mobile */
    .view-hero-card {
        padding: 1.1rem 1rem;
        border-radius: 18px;
        margin-bottom: 0.85rem;
    }

    .view-hero-scenery {
        width: 50%;
        max-width: 220px;
        opacity: 0.90;
        background-position: right center;
    }

    .view-hero-overlay {
        background: linear-gradient(90deg, #FFFFFF 0%, rgba(255, 255, 255, 0.95) 55%, rgba(255, 255, 255, 0.45) 80%, rgba(255, 255, 255, 0.05) 100%);
    }

    .view-hero-content {
        gap: 0.85rem;
    }

    .view-avatar-wrap {
        width: 74px;
        height: 74px;
    }

    .view-avatar-initials {
        font-size: 1.75rem;
    }

    .view-avatar-camera-badge {
        width: 24px;
        height: 24px;
        font-size: 0.65rem;
    }

    .view-hero-name {
        font-size: 1.2rem;
    }

    .hero-status-toggle-wrapper {
        padding: 2px 7px 2px 3px;
        gap: 6px;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-track {
        width: 38px;
        height: 22px;
    }

    .hero-status-toggle-wrapper .cust-status-toggle .toggle-slider-thumb {
        width: 16px;
        height: 16px;
    }

    .hero-status-toggle-wrapper .cust-status-toggle.is-active .toggle-slider-thumb {
        transform: translateX(16px);
    }

    .cust-view-toast {
        left: 16px;
        right: 16px;
        bottom: 16px;
        justify-content: center;
    }

    .view-hero-meta-list {
        gap: 0.45rem;
        flex-direction: column;
        align-items: flex-start;
    }

    .view-hero-meta-item {
        font-size: 0.8rem;
    }

    .view-hero-quick-actions {
        margin-top: 0.65rem;
        gap: 0.35rem;
    }

    .hero-qa-pill {
        padding: 0.32rem 0.65rem;
        font-size: 0.72rem;
    }

    /* 3. KPI Grid on Mobile (2x2 grid matching mobile dashboard architecture) */
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
        margin-bottom: 0.85rem;
    }

    .view-kpi-card {
        padding: 0.85rem 0.75rem;
        border-radius: 16px;
    }

    .kpi-icon-wrap {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
        border-radius: 8px;
    }

    .kpi-label {
        font-size: 0.7rem;
    }

    .kpi-value {
        font-size: 1.2rem;
    }

    .kpi-value.text-compact {
        font-size: 0.92rem;
    }

    .kpi-trend {
        font-size: 0.7rem;
    }

    .kpi-subtext {
        font-size: 0.68rem;
    }

    /* 4. Horizontal Pill Tabs on Mobile */
    .view-nav-tabs-wrapper {
        margin-bottom: 0.85rem;
    }

    .view-nav-tabs {
        padding: 4px;
        gap: 4px;
    }

    .view-nav-pill {
        padding: 0.55rem 0.95rem;
        font-size: 0.82rem;
        gap: 0.4rem;
    }

    /* 5. Stacked Layout for Content */
    .view-layout-split {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }

    .view-col-main, .view-col-side {
        gap: 0.85rem;
    }

    .view-section-card {
        border-radius: 16px;
    }

    .section-card-header {
        padding: 0.9rem 1rem;
    }

    .section-card-body {
        padding: 1rem;
    }

    /* Key-Value Grid on Mobile */
    .view-kv-grid {
        grid-template-columns: 1fr 1fr;
        row-gap: 0.85rem;
        column-gap: 0.75rem;
    }

    .view-kv-label {
        font-size: 0.68rem;
    }

    .view-kv-value {
        font-size: 0.86rem;
    }

    /* Document Tile */
    .doc-card-tile {
        padding: 0.65rem 0.75rem;
    }

    .doc-file-name {
        max-width: 150px;
        font-size: 0.8rem;
    }

    .btn-doc-action {
        padding: 0.35rem 0.55rem;
        font-size: 0.74rem;
    }

    /* Recent Bookings */
    .recent-booking-card {
        padding: 0.75rem 1rem;
        gap: 0.7rem;
    }

    .booking-thumb-wrap {
        width: 60px;
        height: 46px;
    }

    .booking-card-title {
        font-size: 0.85rem;
    }

    .booking-card-meta {
        gap: 0.45rem;
        font-size: 0.7rem;
    }

    .booking-amount {
        font-size: 0.85rem;
    }

    /* Quick Actions 4-tile grid */
    .quick-actions-grid {
        gap: 0.5rem;
    }

    .quick-action-tile {
        padding: 0.75rem 0.35rem;
        border-radius: 12px;
    }

    .qa-icon-wrap {
        width: 34px;
        height: 34px;
        font-size: 0.95rem;
    }

    .qa-text {
        font-size: 0.68rem;
    }

    /* Bookings Tab: Show Cards, Hide Wide Table */
    .desktop-bookings-table {
        display: none;
    }

    .mobile-bookings-cards-list {
        display: flex;
        flex-direction: column;
    }

    /* Payments Banner on Mobile */
    .payments-summary-banner {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
    }

    /* Modals on Mobile */
    .doc-lightbox-container {
        border-radius: 16px;
        max-height: 92vh;
    }

    .doc-lightbox-header {
        padding: 0.75rem 1rem;
    }

    .doc-lightbox-body {
        min-height: 320px;
        padding: 0.5rem;
    }

    .vg-del-card {
        border-radius: 16px;
        max-width: 95%;
    }
}

/* Small Screens (width <= 400px) */
@media (max-width: 400px) {
    .view-kv-grid {
        grid-template-columns: 1fr 1fr;
        column-gap: 0.5rem;
    }

    .view-hero-scenery {
        width: 42%;
        max-width: 170px;
    }

    .kpi-value {
        font-size: 1.1rem;
    }

    .kpi-value.text-compact {
        font-size: 0.85rem;
    }
}

/* Large Screens (width >= 1024px) */
@media (min-width: 1024px) {
    .customer-view-root {
        padding: 1.5rem 1.5rem 4rem 1.5rem;
    }

    .view-hero-card {
        padding: 1.75rem 2rem;
    }

    .view-hero-content {
        max-width: 62%;
        gap: 1.75rem;
    }

    .view-avatar-wrap {
        width: 92px;
        height: 92px;
    }

    .view-hero-name {
        font-size: 1.55rem;
    }

    .view-kpi-card {
        padding: 1.25rem 1.25rem;
    }

    .view-kpi-grid {
        gap: 1.25rem;
    }

    .view-layout-split {
        grid-template-columns: 1.15fr 0.85fr;
        gap: 1.5rem;
    }
}
</style>

<!-- =========================================================================
     CLIENT SCRIPT FOR TAB SWITCHING, MODALS, SHARE, DELETE
     ========================================================================= -->
<script>
// Tab Switching logic
function switchTab(tabId) {
    // Update active tab pills
    const pills = document.querySelectorAll('.view-nav-pill');
    pills.forEach(pill => {
        if (pill.getAttribute('data-tab') === tabId) {
            pill.classList.add('active');
            pill.setAttribute('aria-selected', 'true');
        } else {
            pill.classList.remove('active');
            pill.setAttribute('aria-selected', 'false');
        }
    });

    // Update active tab pane
    const panes = document.querySelectorAll('.view-tab-pane');
    panes.forEach(pane => {
        if (pane.id === 'tabContent-' + tabId) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    // Scroll active tab into view on mobile
    const activePill = document.querySelector('.view-nav-pill.active');
    if (activePill && window.innerWidth <= 768) {
        activePill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
}

function focusNoteInput() {
    setTimeout(() => {
        const input = document.getElementById('customerNotesInput');
        if (input) {
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 150);
}

// Dropdown Menu Toggle
function toggleMoreMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('customerMoreMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('customerMoreMenu');
    if (menu && !e.target.closest('.view-more-menu-wrapper')) {
        menu.style.display = 'none';
    }
});

// Share Customer Details (Web Share API with Clipboard Fallback)
function shareCustomerDetails() {
    const shareData = {
        title: 'Customer Profile: <?= addslashes(e($customer['name'])) ?>',
        text: 'Customer: <?= addslashes(e($customer['name'])) ?>\nPhone: <?= addslashes(e($formattedPhone)) ?>\nEmail: <?= addslashes(e($customer['email'])) ?>\nCity: <?= addslashes(e($locationDisplay)) ?>',
        url: window.location.href
    };

    if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
        navigator.share(shareData).catch(err => console.log('Share dismissed', err));
    } else {
        navigator.clipboard.writeText(shareData.text + '\n' + shareData.url).then(() => {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Customer profile details copied to clipboard!', 'success');
            } else {
                alert('Customer profile details copied to clipboard!');
            }
        }).catch(() => {
            prompt('Copy customer profile details:', shareData.text + '\n' + shareData.url);
        });
    }
}

// Document Preview Modal
function openDocPreviewModal() {
    const modal = document.getElementById('docPreviewModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDocPreviewModal(e) {
    if (!e || e.target.id === 'docPreviewModal' || e.target.closest('.doc-lightbox-close')) {
        const modal = document.getElementById('docPreviewModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
}

/* ========================================================================= */
/* Customer Status Toggle Switch Handler (AJAX)                              */
/* ========================================================================= */
function toggleCustomerViewStatus(id, btn, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    if (!id || btn.classList.contains('is-loading')) return;

    const currentActive = btn.classList.contains('is-active');
    const targetStatus = currentActive ? 'inactive' : 'active';
    const custName = btn.getAttribute('data-name') || 'Customer';

    // Set loading state on all toggles on this page
    const allToggles = document.querySelectorAll('.cust-status-toggle[data-id="' + id + '"]');
    allToggles.forEach(t => {
        t.classList.add('is-loading');
        const spinner = t.querySelector('.toggle-spinner');
        if (spinner) spinner.style.display = 'inline-block';
    });

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
        allToggles.forEach(t => {
            t.classList.remove('is-loading');
            const spinner = t.querySelector('.toggle-spinner');
            if (spinner) spinner.style.display = 'none';
        });

        if (data.success) {
            const isNowActive = (data.status === 'active');
            const labelText = data.label || (isNowActive ? 'Active' : 'Inactive');

            // 1. Update all toggle switches on this view page
            allToggles.forEach(t => {
                if (isNowActive) {
                    t.classList.add('is-active');
                    t.setAttribute('aria-checked', 'true');
                    t.title = 'Click to toggle status (currently Active)';
                } else {
                    t.classList.remove('is-active');
                    t.setAttribute('aria-checked', 'false');
                    t.title = 'Click to toggle status (currently Inactive)';
                }
            });

            // 2. Update Hero Pill
            const heroPill = document.getElementById('heroStatusPill');
            const heroText = document.getElementById('heroStatusLabelText');
            if (heroPill) {
                heroPill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
            }
            if (heroText) heroText.textContent = labelText;

            // 3. Update Personal Info Pill
            const infoPill = document.getElementById('infoStatusPill');
            const infoText = document.getElementById('infoStatusLabelText');
            if (infoPill) {
                infoPill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
            }
            if (infoText) infoText.textContent = labelText;

            // 4. Toast notification
            showCustomerToast(custName + ' status updated to ' + labelText, 'success');
        } else {
            showCustomerToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(err => {
        allToggles.forEach(t => {
            t.classList.remove('is-loading');
            const spinner = t.querySelector('.toggle-spinner');
            if (spinner) spinner.style.display = 'none';
        });
        showCustomerToast('Network error while updating status', 'error');
        console.error('Error toggling status:', err);
    });
}

function showCustomerToast(msg, type = 'success') {
    if (typeof window.vgNotify === 'function') {
        window.vgNotify(msg, type);
        return;
    }
    let toast = document.getElementById('custViewToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'custViewToast';
        toast.className = 'cust-view-toast';
        document.body.appendChild(toast);
    }
    toast.innerHTML = (type === 'success' ? '<i class="fa-solid fa-circle-check"></i> ' : '<i class="fa-solid fa-circle-exclamation"></i> ') + msg;
    toast.className = 'cust-view-toast ' + type + ' show';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3200);
}

// Delete Confirmation Modal (Step 1 & Step 2)
let deleteCustomerId = <?= (int)$customer['id'] ?>;

function openDeleteModal(id, name) {
    deleteCustomerId = id;
    const step1 = document.getElementById('deleteStep1Modal');
    const step2 = document.getElementById('deleteStep2Modal');
    const backdrop = document.getElementById('deleteModalBackdrop');
    const chk1 = document.getElementById('deleteStep1Checkbox');
    const chk2 = document.getElementById('deleteStep2Checkbox');
    const btn1 = document.getElementById('deleteStep1Btn');
    const finalBtn = document.getElementById('deleteConfirmFinalBtn');

    if (chk1) chk1.checked = false;
    if (chk2) chk2.checked = false;

    if (btn1) btn1.classList.add('disabled');
    if (finalBtn) finalBtn.classList.add('disabled');

    if (step1) step1.style.display = 'block';
    if (step2) step2.style.display = 'none';
    if (backdrop) {
        backdrop.style.display = 'flex';
        requestAnimationFrame(() => backdrop.classList.add('active'));
    }
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const backdrop = document.getElementById('deleteModalBackdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        setTimeout(() => {
            backdrop.style.display = 'none';
            const step1 = document.getElementById('deleteStep1Modal');
            const step2 = document.getElementById('deleteStep2Modal');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
        }, 220);
    }
    document.body.style.overflow = '';
}

function handleBackdropClick(e) {
    if (e.target && e.target.id === 'deleteModalBackdrop') {
        closeDeleteModal();
    }
}

function toggleStep1DeleteBtn() {
    const chk = document.getElementById('deleteStep1Checkbox');
    const btn = document.getElementById('deleteStep1Btn');
    if (btn) {
        if (chk && chk.checked) {
            btn.classList.remove('disabled');
        } else {
            btn.classList.add('disabled');
        }
    }
}

function goToStep2Modal() {
    const chk = document.getElementById('deleteStep1Checkbox');
    if (!chk || !chk.checked) {
        const wrap = document.getElementById('delCheckboxContainer');
        if (wrap) {
            wrap.classList.add('shake-error');
            setTimeout(() => wrap.classList.remove('shake-error'), 500);
        }
        return;
    }
    const step1 = document.getElementById('deleteStep1Modal');
    const step2 = document.getElementById('deleteStep2Modal');
    const finalBtn = document.getElementById('deleteConfirmFinalBtn');
    if (finalBtn) finalBtn.classList.add('disabled');
    if (step1 && step2) {
        step1.style.display = 'none';
        step2.style.display = 'block';
    }
}

function toggleStep2DeleteBtn() {
    const chk2 = document.getElementById('deleteStep2Checkbox');
    const btn2 = document.getElementById('deleteConfirmFinalBtn');
    if (btn2) {
        if (chk2 && chk2.checked) {
            btn2.classList.remove('disabled');
        } else {
            btn2.classList.add('disabled');
        }
    }
}

function handleCustomerDeleteSubmit(e) {
    const chk2 = document.getElementById('deleteStep2Checkbox');
    if (!chk2 || !chk2.checked) {
        if (e) e.preventDefault();
        const wrap = document.getElementById('delCheckboxContainer2');
        if (wrap) {
            wrap.classList.add('shake-error');
            setTimeout(() => wrap.classList.remove('shake-error'), 500);
        }
        return false;
    }
    const finalBtn = document.getElementById('deleteConfirmFinalBtn');
    const btnText = document.getElementById('deleteFinalBtnText');
    if (finalBtn) {
        finalBtn.disabled = true;
        finalBtn.style.opacity = '0.75';
    }
    if (btnText) btnText.textContent = 'Deleting...';
    return true;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocPreviewModal();
        closeDeleteModal();
        const menu = document.getElementById('customerMoreMenu');
        if (menu) menu.style.display = 'none';
    }
});
</script>
