<?php
/**
 * MANASWI Tours & Travels — Single Employee / Staff Profile View
 * Faithfully redesigned to match customers/show.php executive layout with mobile-app responsiveness
 * Supports both desktop executive layout and native mobile-friendly app interface
 */

$empFullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
$initials = strtoupper(mb_substr($employee['first_name'] ?? 'E', 0, 1) . mb_substr($employee['last_name'] ?? '', 0, 1));
$avatarUrl = customer_avatar_url($employee['profile_photo'] ?? null);

$status = strtolower($employee['status'] ?? 'active');
$isActive = ($status === 'active');
$statusText = ($status === 'active') ? 'Active' : (($status === 'on_leave') ? 'On Leave' : ucfirst(str_replace('_', ' ', $status)));
$statusClass = $isActive ? 'completed' : ($status === 'on_leave' ? 'assigned' : 'cancelled');

$role = !empty($employee['linked_role']) ? $employee['linked_role'] : (!empty($employee['role']) ? $employee['role'] : ($employee['department'] === 'fleet_chauffeur' ? 'driver' : strtolower($employee['department'] ?? 'staff')));
$roleLabel = ucfirst(str_replace('_', ' ', $role));
$department = !empty($employee['department']) ? $employee['department'] : 'operations';
$departmentLabel = ucfirst(str_replace('_', ' ', $department));
$designation = !empty($employee['designation']) ? $employee['designation'] : ($employee['department'] === 'fleet_chauffeur' ? 'Commercial Chauffeur' : $roleLabel);
$empCode = $employee['employee_code'] ?? ('VG-EMP-' . str_pad($employee['id'], 4, '0', STR_PAD_LEFT));

// Format phone display
$rawPhone = preg_replace('/[^0-9]/', '', $employee['phone'] ?? '');
if (strlen($rawPhone) === 10) {
    $formattedPhone = '+91 ' . substr($rawPhone, 0, 5) . ' ' . substr($rawPhone, 5);
} elseif (!empty($employee['phone'])) {
    $formattedPhone = '+91 ' . e($employee['phone']);
} else {
    $formattedPhone = 'Not provided';
}

$rawAltPhone = preg_replace('/[^0-9]/', '', $employee['alternate_phone'] ?? '');
if (strlen($rawAltPhone) === 10) {
    $formattedAltPhone = '+91 ' . substr($rawAltPhone, 0, 5) . ' ' . substr($rawAltPhone, 5);
} elseif (!empty($employee['alternate_phone'])) {
    $formattedAltPhone = '+91 ' . e($employee['alternate_phone']);
} else {
    $formattedAltPhone = 'None';
}

// WhatsApp URL generator
$cleanPhone = preg_replace('/[^0-9]/', '', $employee['phone'] ?? '');
$waPhone = (strlen($cleanPhone) === 10) ? '91' . $cleanPhone : $cleanPhone;
$waUrl = !empty($waPhone) ? "https://wa.me/{$waPhone}?text=" . urlencode("Hello " . $empFullName . ", greetings from MANASWI Tours & Travels Office.") : "tel:" . e($employee['phone']);

// Format location (City, State)
$locationParts = array_filter([$employee['city'] ?? '', $employee['state'] ?? '']);
$locationDisplay = !empty($locationParts) ? implode(', ', $locationParts) : 'India';

$addressLine = trim((string)($employee['address_line'] ?? ($employee['address'] ?? '')));
$fullAddress = trim(implode(', ', array_filter([
    $addressLine,
    $employee['city'] ?? '',
    $employee['state'] ?? '',
    $employee['postal_code'] ?? '',
    $employee['country'] ?? 'India'
])));

// Documents resolution
$licenseDocFile = $employee['driver_license_doc'] ?? $employee['license_document_file'] ?? null;
$licenseDocUrl = customer_document_url($licenseDocFile);
$isLicensePdf = !empty($licenseDocFile) && str_ends_with(strtolower($licenseDocFile), '.pdf');

$idDocFile = $employee['id_document_file'] ?? null;
$idDocUrl = customer_document_url($idDocFile);
$isIdPdf = !empty($idDocFile) && str_ends_with(strtolower($idDocFile), '.pdf');

// Assigned Dispatches & Trips
$dispatches = $dispatches ?? [];
$hasDispatches = !empty($dispatches);
$totalTripsCount = count($dispatches);
$completedTripsCount = count(array_filter($dispatches, fn($d) => in_array($d['status'] ?? '', ['completed', 'trip_completed', 'confirmed'])));
$activeTripAssignment = null;
foreach ($dispatches as $d) {
    if (in_array($d['status'] ?? '', ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'])) {
        $activeTripAssignment = $d;
        break;
    }
}

// Statistics
$statTotalTrips = $totalTripsCount;
$statTripsTrend = $totalTripsCount > 0 ? "↑ {$totalTripsCount}" : "0";

$statExperience = !empty($employee['experience_years']) ? (int)$employee['experience_years'] . ' Yrs' : 'Full Time';
$statRating = !empty($employee['rating']) ? number_format((float)$employee['rating'], 1) : '5.0';
$dutyStatus = strtolower($employee['duty_status'] ?? 'off_duty');
$dutyStatusLabel = ucwords(str_replace('_', ' ', $dutyStatus));

$statJoiningDate = !empty($employee['joining_date']) ? format_date($employee['joining_date']) : 'Recorded';
?>

<div class="customer-view-root employee-view-root">
    <!-- 1. Top App Bar / Header Actions -->
    <div class="view-header-bar">
        <div class="view-header-right">
            <?php if (auth_role() === 'admin'): ?>
            <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" class="view-header-btn edit-btn" title="Edit Profile" aria-label="Edit Profile">
                <i class="fa-solid fa-pen"></i>
            </a>
            <?php endif; ?>
            <div class="view-more-menu-wrapper">
                <button type="button" class="view-header-btn more-btn" id="btnEmployeeMore" onclick="toggleMoreMenu(event)" title="More Options" aria-label="More Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div id="employeeMoreMenu" class="view-dropdown-menu" style="display: none;">
                    <?php if (auth_role() === 'admin'): ?>
                    <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-user-pen"></i> Edit Profile
                    </a>
                    <?php endif; ?>
                    <a href="<?= url('/office/dispatches') ?>" class="view-dropdown-item">
                        <i class="fa-solid fa-van-shuttle"></i> View Dispatches
                    </a>
                    <a href="javascript:void(0)" onclick="shareEmployeeDetails()" class="view-dropdown-item">
                        <i class="fa-solid fa-share-nodes"></i> Share Details
                    </a>
                    <a href="javascript:void(0)" onclick="window.print()" class="view-dropdown-item">
                        <i class="fa-solid fa-print"></i> Print Profile
                    </a>
                    <?php if (auth_role() === 'admin'): ?>
                    <div class="view-dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="openDeleteModal(<?= (int)$employee['id'] ?>, '<?= addslashes(e($empFullName)) ?>')" class="view-dropdown-item danger">
                        <i class="fa-regular fa-trash-can"></i> Delete Employee
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Hero Profile Card (Scenic Mountains & Modern Tour Bus Artwork matching Customers) -->
    <div class="view-hero-card">
        <div class="view-hero-scenery" style="background-image: url('<?= asset('images/customer-scenic-bus.png') ?>');" aria-hidden="true"></div>
        <div class="view-hero-overlay" aria-hidden="true"></div>

        <div class="view-hero-content">
            <!-- Left Avatar -->
            <div class="view-avatar-wrap">
                <?php if (!empty($avatarUrl)): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($empFullName) ?>" class="view-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="view-avatar-initials" style="display: none;">
                        <?= e($initials) ?>
                    </div>
                <?php else: ?>
                    <div class="view-avatar-initials">
                        <?= e($initials) ?>
                    </div>
                <?php endif; ?>
                <div class="view-avatar-camera-badge" title="Verified Staff Photo">
                    <i class="fa-solid fa-camera"></i>
                </div>
            </div>

            <!-- Middle Identity Details -->
            <div class="view-hero-info">
                <div class="view-hero-name-row">
                    <h2 class="view-hero-name"><?= e($empFullName) ?></h2>
                    
                    <!-- Live Status Toggle Switch Control (Executive & Mobile Friendly) -->
                    <div class="hero-status-toggle-wrapper" id="heroStatusToggleWrapper" title="Toggle Employee Status">
                        <button type="button" 
                                id="heroStatusToggleBtn"
                                class="cust-status-toggle <?= $isActive ? 'is-active' : '' ?>" 
                                role="switch" 
                                aria-checked="<?= $isActive ? 'true' : 'false' ?>" 
                                data-id="<?= (int)$employee['id'] ?>"
                                data-name="<?= addslashes(e($empFullName)) ?>"
                                onclick="toggleEmployeeViewStatus(<?= (int)$employee['id'] ?>, this, event)"
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

                    <span class="view-hero-code-pill"><?= e($empCode) ?></span>
                    <span class="emp-role-pill"><?= e($roleLabel) ?></span>
                </div>

                <div class="view-hero-meta-list">
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-phone"></i>
                        <a href="tel:<?= e($employee['phone']) ?>"><?= e($formattedPhone) ?></a>
                    </div>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-envelope"></i>
                        <?php if (!empty($employee['email'])): ?>
                            <a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">Not provided</span>
                        <?php endif; ?>
                    </div>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span title="<?= e($fullAddress) ?>"><?= e($locationDisplay) ?></span>
                    </div>
                    <div class="view-hero-meta-item">
                        <i class="fa-solid fa-briefcase"></i>
                        <span><?= e($designation) ?></span>
                    </div>
                </div>

                <!-- Mobile Quick Contact Action Pills -->
                <div class="view-hero-quick-actions">
                    <a href="tel:<?= e($employee['phone']) ?>" class="hero-qa-pill qa-call" title="Call Employee">
                        <i class="fa-solid fa-phone"></i>
                        <span>Call</span>
                    </a>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="hero-qa-pill qa-wa" title="WhatsApp Employee">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                    <?php if (!empty($employee['email'])): ?>
                        <a href="mailto:<?= e($employee['email']) ?>" class="hero-qa-pill qa-email" title="Send Corporate Email">
                            <i class="fa-regular fa-envelope"></i>
                            <span>Email</span>
                        </a>
                    <?php endif; ?>
                    <?php if (auth_role() === 'admin'): ?>
                    <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" class="hero-qa-pill qa-book" title="Edit Staff Profile">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit Staff</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. 4 Top KPI Metric Cards (Matching Customers Architecture) -->
    <div class="view-kpi-grid">
        <!-- KPI 1: Total Dispatches / Trips -->
        <div class="view-kpi-card" onclick="switchTab('trips')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-solid fa-van-shuttle"></i>
                </div>
                <div class="kpi-trend <?= $totalTripsCount > 0 ? 'up' : 'neutral' ?>">
                    <?php if ($totalTripsCount > 0): ?>
                        <i class="fa-solid fa-arrow-up"></i> <?= e($statTripsTrend) ?>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kpi-label">Assigned Trips</div>
            <div class="kpi-value"><?= (int)$statTotalTrips ?></div>
            <div class="kpi-footer-note">Fleet operations assigned</div>
        </div>

        <!-- KPI 2: Duty Status / Rating -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap amber">
                    <i class="fa-solid fa-star"></i>
                </div>
                <span class="kpi-tag-recent <?= $dutyStatus === 'on_duty' ? '' : 'empty' ?>"><?= e($dutyStatusLabel) ?></span>
            </div>
            <div class="kpi-label">Rating &amp; Availability</div>
            <div class="kpi-value text-compact">★ <?= e($statRating) ?> <span style="font-size: 0.85rem; color:#64748B; font-weight: 500;">/ 5.0</span></div>
            <div class="kpi-subtext"><?= $dutyStatus === 'on_duty' ? 'Ready for Dispatch' : ($dutyStatus === 'on_trip' ? 'Currently on Trip' : 'Off Duty') ?></div>
        </div>

        <!-- KPI 3: Driving Experience / Tenure -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="kpi-trend up">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>
            <div class="kpi-label">Experience &amp; Contract</div>
            <div class="kpi-value"><?= e($statExperience) ?></div>
            <div class="kpi-footer-note"><?= ucfirst(str_replace('_', ' ', $employee['employment_type'] ?? 'full_time')) ?> Contract</div>
        </div>

        <!-- KPI 4: Date of Joining -->
        <div class="view-kpi-card" onclick="switchTab('overview')">
            <div class="kpi-card-top-row">
                <div class="kpi-icon-wrap blue">
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
                <span class="kpi-tag-recent">Joined</span>
            </div>
            <div class="kpi-label">Date of Joining</div>
            <div class="kpi-value text-compact"><?= e($statJoiningDate) ?></div>
            <div class="kpi-subtext" title="Active MANASWI Member">Verified Staff Member</div>
        </div>
    </div>

    <!-- 4. Horizontal Navigation Pill Tabs (Smooth Swipe on Mobile) -->
    <div class="view-nav-tabs-wrapper">
        <div class="view-nav-tabs" role="tablist">
            <button type="button" class="view-nav-pill active" data-tab="overview" onclick="switchTab('overview')" role="tab" aria-selected="true">
                <i class="fa-solid fa-user"></i>
                <span>Overview</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="trips" onclick="switchTab('trips')" role="tab" aria-selected="false">
                <i class="fa-solid fa-van-shuttle"></i>
                <span>Trips / Dispatches</span>
                <span class="pill-count"><?= count($dispatches) ?></span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="credentials" onclick="switchTab('credentials')" role="tab" aria-selected="false">
                <i class="fa-solid fa-key"></i>
                <span>Credentials &amp; Access</span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="documents" onclick="switchTab('documents')" role="tab" aria-selected="false">
                <i class="fa-solid fa-file-shield"></i>
                <span>Documents</span>
                <span class="pill-count"><?= (!empty($licenseDocUrl) ? 1 : 0) + (!empty($idDocUrl) ? 1 : 0) ?></span>
            </button>
            <button type="button" class="view-nav-pill" data-tab="notes" onclick="switchTab('notes')" role="tab" aria-selected="false">
                <i class="fa-regular fa-clipboard"></i>
                <span>Emergency &amp; Notes</span>
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
                        <?php if (auth_role() === 'admin'): ?>
                        <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" class="section-header-link-btn" title="Edit personal details">
                            <i class="fa-solid fa-pen"></i> <span>Edit</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <!-- Row 1 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Legal Full Name</span>
                                <span class="view-kv-value bold"><?= e($empFullName) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Primary Mobile Phone</span>
                                <span class="view-kv-value bold">
                                    <a href="tel:<?= e($employee['phone']) ?>"><?= e($formattedPhone) ?></a>
                                </span>
                            </div>

                            <!-- Row 2 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Corporate Email</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($employee['email'])): ?>
                                        <a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a>
                                    <?php else: ?>
                                        <em class="text-muted">Not provided</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Alternate Phone</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($employee['alternate_phone'])): ?>
                                        <a href="tel:<?= e($employee['alternate_phone']) ?>"><?= e($formattedAltPhone) ?></a>
                                    <?php else: ?>
                                        <em class="text-muted">None</em>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- Row 3 -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Date of Birth</span>
                                <span class="view-kv-value">
                                    <?= !empty($employee['date_of_birth']) ? format_date($employee['date_of_birth']) : '<em class="text-muted">Not provided</em>' ?>
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
                                    <?= !empty($employee['gender']) ? ucfirst(e(str_replace('_', ' ', $employee['gender']))) : '<em class="text-muted">Not specified</em>' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Nationality</span>
                                <span class="view-kv-value flag-val">
                                    <span class="flag-icon" role="img" aria-label="India">🇮🇳</span>
                                    <span><?= !empty($employee['country']) ? e($employee['country']) : 'Indian' ?></span>
                                </span>
                            </div>

                            <!-- Row 5: Live Account Status Toggle & Code -->
                            <div class="view-kv-item">
                                <span class="view-kv-label">Employment Status</span>
                                <div class="view-kv-status-row">
                                    <button type="button" 
                                            id="infoStatusToggleBtn"
                                            class="cust-status-toggle sm <?= $isActive ? 'is-active' : '' ?>" 
                                            role="switch" 
                                            aria-checked="<?= $isActive ? 'true' : 'false' ?>"
                                            data-id="<?= (int)$employee['id'] ?>"
                                            data-name="<?= addslashes(e($empFullName)) ?>"
                                            onclick="toggleEmployeeViewStatus(<?= (int)$employee['id'] ?>, this, event)"
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
                                <span class="view-kv-label">Staff Code</span>
                                <span class="view-kv-value bold">
                                    <?= e($empCode) ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($addressLine)): ?>
                            <div class="view-address-detail-box">
                                <div class="address-detail-label">Complete Registered Residential Address</div>
                                <div class="address-detail-text">
                                    <?= e($addressLine) ?><br>
                                    <?= !empty($employee['city']) ? e($employee['city']) : '' ?><?= !empty($employee['state']) ? ', ' . e($employee['state']) : '' ?><?= !empty($employee['postal_code']) ? ' - ' . e($employee['postal_code']) : '' ?><br>
                                    <strong><?= e($employee['country'] ?? 'India') ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Employment & Position Details Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge blue">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <h3 class="section-title">Employment &amp; Position Details</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div class="view-kv-grid">
                            <div class="view-kv-item">
                                <span class="view-kv-label">System Role</span>
                                <span class="view-kv-value bold" style="text-transform: capitalize;">
                                    <span class="emp-role-pill" style="display: inline-block; background: rgba(23, 107, 91,0.08); color: #16443B; border: 1px solid rgba(23, 107, 91,0.2); border-radius: 6px; padding: 2px 10px; font-size: 12px; font-weight: 700;">
                                        <?= e($roleLabel) ?>
                                    </span>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Operational Department</span>
                                <span class="view-kv-value bold" style="text-transform: capitalize;"><?= e($departmentLabel) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Job Title / Designation</span>
                                <span class="view-kv-value"><?= e($designation) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Contract Type</span>
                                <span class="view-kv-value" style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $employee['employment_type'] ?? 'full_time')) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Date of Joining</span>
                                <span class="view-kv-value"><?= format_date($employee['joining_date'] ?? date('Y-m-d')) ?></span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">Fleet Duty Status</span>
                                <span class="view-kv-value">
                                    <span class="badge <?= $dutyStatus === 'on_duty' ? 'badge-success' : ($dutyStatus === 'on_trip' ? 'badge-info' : 'badge-neutral') ?>">
                                        <?= strtoupper(e($dutyStatusLabel)) ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Identity & Document Scan Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge teal">
                                <i class="fa-solid fa-file-shield"></i>
                            </div>
                            <h3 class="section-title">Identity &amp; Compliance Documents</h3>
                        </div>
                        <span class="verified-pill">
                            <i class="fa-solid fa-circle-check"></i> Verified Staff Profile
                        </span>
                    </div>
                    <div class="section-card-body">
                        <!-- Driving License Document -->
                        <div class="view-kv-grid" style="margin-bottom: 1rem;">
                            <div class="view-kv-item">
                                <span class="view-kv-label">Driving License Number</span>
                                <span class="view-kv-value bold">
                                    <?php if (!empty($employee['license_number'])): ?>
                                        <code class="doc-code"><?= e($employee['license_number']) ?></code>
                                    <?php else: ?>
                                        <em class="text-muted">Not applicable / Not recorded</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">License Expiry Date</span>
                                <span class="view-kv-value">
                                    <?= !empty($employee['license_expiry']) ? format_date($employee['license_expiry']) : '<em class="text-muted">—</em>' ?>
                                </span>
                            </div>
                        </div>

                        <!-- License Document Preview Tile -->
                        <div class="doc-scan-wrapper" style="margin-bottom: 1.5rem;">
                            <span class="view-kv-label" style="margin-bottom: 0.5rem; display: block;">Commercial Driving License Scan</span>
                            <?php if (!empty($licenseDocUrl)): ?>
                                <div class="doc-card-tile">
                                    <div class="doc-tile-left" onclick="openDocPreviewModal('<?= e($licenseDocUrl) ?>', 'Commercial Driving License', <?= $isLicensePdf ? 'true' : 'false' ?>)" style="cursor: pointer;" title="Click to view file preview">
                                        <?php if ($isLicensePdf): ?>
                                            <div class="doc-file-icon pdf">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?= e($licenseDocUrl) ?>" alt="Driving License Preview" class="doc-thumb-img">
                                        <?php endif; ?>
                                        <div class="doc-tile-info">
                                            <div class="doc-file-name" title="<?= e(basename($licenseDocFile)) ?>">
                                                <?= e(basename($licenseDocFile)) ?>
                                            </div>
                                            <div class="doc-file-status">
                                                <i class="fa-solid fa-circle-check"></i> Verified Commercial Driving License
                                            </div>
                                        </div>
                                    </div>
                                    <div class="doc-tile-actions">
                                        <button type="button" class="btn-doc-action preview" onclick="openDocPreviewModal('<?= e($licenseDocUrl) ?>', 'Commercial Driving License', <?= $isLicensePdf ? 'true' : 'false' ?>)" title="View Document">
                                            <i class="fa-solid fa-eye"></i> <span>Preview</span>
                                        </button>
                                        <a href="<?= e($licenseDocUrl) ?>" target="_blank" download class="btn-doc-action download" title="Download Document">
                                            <i class="fa-solid fa-arrow-down-to-bracket"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="doc-empty-state">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>No driving license document scan uploaded yet. <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>">Upload document</a></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Government ID Document Details -->
                        <div class="view-kv-grid" style="margin-bottom: 1rem; border-top: 1px dashed #E2E8F0; padding-top: 1rem;">
                            <div class="view-kv-item">
                                <span class="view-kv-label">Government ID Type</span>
                                <span class="view-kv-value bold">
                                    <?= !empty($employee['id_type']) ? e(ucwords(str_replace('_', ' ', $employee['id_type']))) : 'Aadhaar / National ID' ?>
                                </span>
                            </div>
                            <div class="view-kv-item">
                                <span class="view-kv-label">ID Number / Reference</span>
                                <span class="view-kv-value">
                                    <?php if (!empty($employee['id_number'])): ?>
                                        <code class="doc-code"><?= e($employee['id_number']) ?></code>
                                    <?php else: ?>
                                        <em class="text-muted">Not specified</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Government ID File Preview Tile -->
                        <div class="doc-scan-wrapper">
                            <span class="view-kv-label" style="margin-bottom: 0.5rem; display: block;">Attached Government ID Document Scan</span>
                            <?php if (!empty($idDocUrl)): ?>
                                <div class="doc-card-tile">
                                    <div class="doc-tile-left" onclick="openDocPreviewModal('<?= e($idDocUrl) ?>', 'Government Identity Document', <?= $isIdPdf ? 'true' : 'false' ?>)" style="cursor: pointer;" title="Click to view file preview">
                                        <?php if ($isIdPdf): ?>
                                            <div class="doc-file-icon pdf">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?= e($idDocUrl) ?>" alt="ID Document Preview" class="doc-thumb-img">
                                        <?php endif; ?>
                                        <div class="doc-tile-info">
                                            <div class="doc-file-name" title="<?= e(basename($idDocFile)) ?>">
                                                <?= e(basename($idDocFile)) ?>
                                            </div>
                                            <div class="doc-file-status">
                                                <i class="fa-solid fa-circle-check"></i> Verified identity scan on secure record
                                            </div>
                                        </div>
                                    </div>
                                    <div class="doc-tile-actions">
                                        <button type="button" class="btn-doc-action preview" onclick="openDocPreviewModal('<?= e($idDocUrl) ?>', 'Government Identity Document', <?= $isIdPdf ? 'true' : 'false' ?>)" title="View Document">
                                            <i class="fa-solid fa-eye"></i> <span>Preview</span>
                                        </button>
                                        <a href="<?= e($idDocUrl) ?>" target="_blank" download class="btn-doc-action download" title="Download Document">
                                            <i class="fa-solid fa-arrow-down-to-bracket"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="doc-empty-state">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>No government ID scan uploaded yet. <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>">Upload document</a></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: System Login Account & Quick Actions -->
            <div class="view-col-side">
                <!-- Attached System Login Account -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge purple">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h3 class="section-title">System Login Account</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <?php if (!empty($employee['linked_username'])): ?>
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 14px 16px; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-size: 0.95rem; font-weight: 700; color: #0F172A;">
                                        @<?= e($employee['linked_username']) ?>
                                    </span>
                                    <span class="badge badge-navy" style="text-transform: uppercase; font-size: 11px;">
                                        <?= e($employee['linked_role'] ?? 'staff') ?>
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: #64748B; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-circle" style="font-size: 8px; color: <?= ($employee['linked_user_status'] ?? 'active') === 'active' ? '#10B981' : '#EF4444' ?>;"></i>
                                    Account status: <strong style="color: #0F172A; text-transform: capitalize;"><?= e($employee['linked_user_status'] ?? 'active') ?></strong>
                                </div>
                            </div>
                            <p style="font-size: 12px; color: #64748B; margin: 0; line-height: 1.4;">
                                This employee profile is linked to an active MANASWI system login credential with operational permissions.
                            </p>
                        <?php else: ?>
                            <div class="doc-empty-state" style="padding: 1.25rem 0.75rem;">
                                <i class="fa-solid fa-user-lock" style="font-size: 1.6rem; color: #94A3B8; margin-bottom: 6px;"></i>
                                <span style="font-size: 12.5px;">No dedicated login account attached to this employee profile.</span>
                                <?php if (auth_role() === 'admin'): ?>
                                <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" style="font-size: 12px; margin-top: 6px; font-weight: 600;">Link or Create User</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Commercial Chauffeur Compliance Card -->
                <?php if ($employee['department'] === 'fleet_chauffeur' || $role === 'driver' || !empty($employee['license_number'])): ?>
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge amber">
                                <i class="fa-solid fa-id-card-clip"></i>
                            </div>
                            <h3 class="section-title">Chauffeur Compliance</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                <span style="color: #64748B;">License Ref:</span>
                                <code style="background: #E8F7F0; color: #16443B; padding: 2px 6px; border-radius: 4px; font-weight: 700;"><?= e($employee['license_number'] ?? 'Not Recorded') ?></code>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                <span style="color: #64748B;">License Expiry:</span>
                                <strong style="color: #0F172A;"><?= !empty($employee['license_expiry']) ? format_date($employee['license_expiry']) : '—' ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                <span style="color: #64748B;">Experience:</span>
                                <strong style="color: #0F172A;"><?= (int)($employee['experience_years'] ?? 1) ?> Years Driving</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                <span style="color: #64748B;">Customer Rating:</span>
                                <strong style="color: #D97706;">★ <?= number_format((float)($employee['rating'] ?? 5.0), 1) ?> / 5.0</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Emergency Contact Card -->
                <div class="view-section-card">
                    <div class="section-card-header">
                        <div class="section-title-wrap">
                            <div class="section-icon-badge" style="background: #FEE2E2; color: #DC2626;">
                                <i class="fa-solid fa-truck-medical"></i>
                            </div>
                            <h3 class="section-title">Emergency Contact</h3>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <?php if (!empty($employee['emergency_contact']) || !empty($employee['emergency_contact_phone'])): ?>
                            <div style="font-size: 13px; color: #0F172A; margin-bottom: 6px; font-weight: 700;">
                                <?= e($employee['emergency_contact_name'] ?? $employee['emergency_contact'] ?? 'Contact Person') ?>
                            </div>
                            <?php if (!empty($employee['emergency_contact_phone'])): ?>
                                <div style="font-size: 12.5px; color: #64748B; display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                                    <i class="fa-solid fa-phone" style="color: #DC2626; font-size: 10px;"></i>
                                    <a href="tel:<?= e($employee['emergency_contact_phone']) ?>" style="color: inherit; text-decoration: none; font-weight: 600;"><?= e($employee['emergency_contact_phone']) ?></a>
                                </div>
                            <?php endif; ?>
                            <a href="tel:<?= e($employee['emergency_contact_phone'] ?? $employee['emergency_contact']) ?>" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center; font-size: 12px; gap: 6px;">
                                <i class="fa-solid fa-phone"></i> Call Emergency Contact
                            </a>
                        <?php else: ?>
                            <div style="font-size: 12.5px; color: #94A3B8; font-style: italic;">
                                No emergency contact details recorded on file.
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
                            <?php if (auth_role() === 'admin'): ?>
                            <a href="<?= url('/office/employees/' . $employee['id'] . '/edit') ?>" class="quick-action-tile">
                                <div class="qa-icon-wrap blue">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                                <span class="qa-text">Edit Staff</span>
                            </a>
                            <?php endif; ?>

                            <a href="<?= url('/office/dispatches') ?>" class="quick-action-tile">
                                <div class="qa-icon-wrap blue">
                                    <i class="fa-solid fa-van-shuttle"></i>
                                </div>
                                <span class="qa-text">Dispatches</span>
                            </a>

                            <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="quick-action-tile">
                                <div class="qa-icon-wrap whatsapp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <span class="qa-text">Contact WhatsApp</span>
                            </a>

                            <button type="button" class="quick-action-tile" onclick="shareEmployeeDetails()">
                                <div class="qa-icon-wrap cyan">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </div>
                                <span class="qa-text">Share Details</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: TRIPS / DISPATCHES
         ========================================================================= -->
    <div id="tabContent-trips" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge blue">
                        <i class="fa-solid fa-van-shuttle"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Assigned Fleet Trips &amp; Dispatches</h3>
                        <p class="section-subtitle">Complete operational trip ledger, customer journeys, and vehicle dispatches</p>
                    </div>
                </div>
                <a href="<?= url('/office/dispatches') ?>" class="btn-primary-compact">
                    <i class="fa-solid fa-list-check"></i> <span>Manage Dispatches</span>
                </a>
            </div>
            <div class="section-card-body p-0">
                <?php if (empty($dispatches)): ?>
                    <div class="tab-empty-state">
                        <div class="empty-icon-circle">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <h4 class="empty-title">No Fleet Trips Dispatched Yet</h4>
                        <p class="empty-text">This employee does not currently have any active or completed chauffeur fleet trips in the log.</p>
                        <a href="<?= url('/office/dispatches') ?>" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-van-shuttle"></i> Go to Dispatch Console
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
                                        <th>Route / Assignment</th>
                                        <th>Client</th>
                                        <th>Vehicle</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dispatches as $d): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('/office/bookings/' . ($d['booking_id'] ?? 0)) ?>" class="table-code-link">
                                                    <code><?= e($d['booking_code'] ?? 'VG-BK-' . ($d['booking_id'] ?? 0)) ?></code>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="table-title-bold">
                                                    <?= e($d['pickup_address'] ?? 'Departure') ?> &rarr; <?= e($d['drop_address'] ?? 'Destination') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= e($d['customer_name'] ?? 'Guest Customer') ?>
                                            </td>
                                            <td>
                                                <span class="invoice-badge"><i class="fa-solid fa-car-side"></i> <?= e($d['vehicle_number'] ?? 'Fleet Unit') ?></span>
                                            </td>
                                            <td>
                                                <div class="table-date-meta">
                                                    <?= !empty($d['start_date']) ? format_date($d['start_date']) : '—' ?>
                                                    <?php if (!empty($d['end_date']) && $d['end_date'] !== ($d['start_date'] ?? '')): ?>
                                                        <span class="text-muted">to <?= format_date($d['end_date']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= render_status_badge($d['status'] ?? 'assigned') ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('/office/bookings/' . ($d['booking_id'] ?? 0)) ?>" class="table-action-link">
                                                    View Trip <i class="fa-solid fa-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards (Screen Width <= 768px) -->
                    <div class="mobile-bookings-cards-list">
                        <?php foreach ($dispatches as $d):
                            $dStatus = strtolower($d['status'] ?? 'assigned');
                            $bCode = $d['booking_code'] ?? ('VG-BK-' . ($d['booking_id'] ?? 0));
                            $route = ($d['pickup_address'] ?? 'Departure') . ' → ' . ($d['drop_address'] ?? 'Destination');
                            $bDates = !empty($d['start_date']) ? format_date($d['start_date']) : 'Scheduled';
                        ?>
                        <div class="mb-booking-card" onclick="location.href='<?= url('/office/bookings/' . ($d['booking_id'] ?? 0)) ?>'">
                            <div class="mb-booking-card-header">
                                <span class="mb-booking-code"><?= e($bCode) ?></span>
                                <?= render_status_badge($dStatus) ?>
                            </div>
                            <div class="mb-booking-card-body">
                                <h4 class="mb-booking-title"><?= e($route) ?></h4>
                                <div class="mb-booking-meta-row">
                                    <span><i class="fa-regular fa-calendar"></i> <?= e($bDates) ?></span>
                                    <span><i class="fa-solid fa-user"></i> <?= e($d['customer_name'] ?? 'Customer') ?></span>
                                </div>
                            </div>
                            <div class="mb-booking-card-footer">
                                <div class="mb-booking-price" style="font-size: 12px; font-weight: 600; color: #16443B;">
                                    <i class="fa-solid fa-car-side"></i> <?= e($d['vehicle_number'] ?? 'Fleet Vehicle') ?>
                                </div>
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
         TAB CONTENT: CREDENTIALS & ACCESS
         ========================================================================= -->
    <div id="tabContent-credentials" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge purple">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 class="section-title">System Credentials &amp; Access Controls</h3>
                        <p class="section-subtitle">Enterprise role mapping, security credentials, and system login status</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Attached Username</span>
                        <span class="view-kv-value bold">
                            <?= !empty($employee['linked_username']) ? '@' . e($employee['linked_username']) : '<em class="text-muted">No linked account</em>' ?>
                        </span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Assigned Security Role</span>
                        <span class="view-kv-value bold" style="text-transform: capitalize;">
                            <?= !empty($employee['linked_role']) ? e($employee['linked_role']) : e($roleLabel) ?>
                        </span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Account Access Status</span>
                        <span class="view-kv-value">
                            <span class="badge <?= ($employee['linked_user_status'] ?? 'active') === 'active' ? 'badge-success' : 'badge-neutral' ?>">
                                <?= strtoupper(e($employee['linked_user_status'] ?? 'ACTIVE')) ?>
                            </span>
                        </span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Corporate Email Identifier</span>
                        <span class="view-kv-value">
                            <?= !empty($employee['email']) ? e($employee['email']) : '<em class="text-muted">Not provided</em>' ?>
                        </span>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin: 0 0 6px 0;">Security &amp; Permissions Summary</h4>
                    <p style="font-size: 0.85rem; color: #64748B; margin: 0; line-height: 1.5;">
                        Role <strong><?= e($roleLabel) ?></strong> permits this employee to interact with assigned MANASWI office operations according to established security matrix policies. Contact administrator to modify permissions.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: DOCUMENTS
         ========================================================================= -->
    <div id="tabContent-documents" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge teal">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Compliance Documents &amp; Digital Scans</h3>
                        <p class="section-subtitle">Verified commercial driving license, government identification, and credentials</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                    <!-- Card 1: Driving License -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-weight: 700; font-size: 1rem; color: #0F172A;">Commercial Driving License</span>
                                <span class="verified-pill" style="font-size: 11px;"><i class="fa-solid fa-circle-check"></i> Verified</span>
                            </div>
                            <div style="font-size: 12.5px; color: #64748B; margin-bottom: 12px;">
                                Number: <code class="doc-code"><?= e($employee['license_number'] ?? 'Not Recorded') ?></code><br>
                                Expiry Date: <strong><?= !empty($employee['license_expiry']) ? format_date($employee['license_expiry']) : '—' ?></strong>
                            </div>
                            <?php if (!empty($licenseDocUrl)): ?>
                                <div style="height: 160px; border-radius: 10px; overflow: hidden; background: #FFFFFF; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; cursor: pointer;" onclick="openDocPreviewModal('<?= e($licenseDocUrl) ?>', 'Commercial Driving License', <?= $isLicensePdf ? 'true' : 'false' ?>)">
                                    <?php if ($isLicensePdf): ?>
                                        <i class="fa-solid fa-file-pdf" style="font-size: 3rem; color: #EF4444;"></i>
                                    <?php else: ?>
                                        <img src="<?= e($licenseDocUrl) ?>" alt="License Scan" style="width: 100%; height: 100%; object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="doc-empty-state" style="padding: 2rem 1rem; margin-bottom: 12px;">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>No driving license document scan uploaded.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($licenseDocUrl)): ?>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;" onclick="openDocPreviewModal('<?= e($licenseDocUrl) ?>', 'Commercial Driving License', <?= $isLicensePdf ? 'true' : 'false' ?>)">
                                    <i class="fa-solid fa-eye"></i> Preview Scan
                                </button>
                                <a href="<?= e($licenseDocUrl) ?>" target="_blank" download class="btn btn-primary btn-sm" style="justify-content: center;">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card 2: Government ID Document -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-weight: 700; font-size: 1rem; color: #0F172A;">Government Identity Document</span>
                                <span class="verified-pill" style="font-size: 11px;"><i class="fa-solid fa-circle-check"></i> Verified</span>
                            </div>
                            <div style="font-size: 12.5px; color: #64748B; margin-bottom: 12px;">
                                Type: <strong><?= !empty($employee['id_type']) ? e(ucwords(str_replace('_', ' ', $employee['id_type']))) : 'Aadhaar / National ID' ?></strong><br>
                                Reference: <code class="doc-code"><?= e($employee['id_number'] ?? 'Not Specified') ?></code>
                            </div>
                            <?php if (!empty($idDocUrl)): ?>
                                <div style="height: 160px; border-radius: 10px; overflow: hidden; background: #FFFFFF; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; cursor: pointer;" onclick="openDocPreviewModal('<?= e($idDocUrl) ?>', 'Government Identity Document', <?= $isIdPdf ? 'true' : 'false' ?>)">
                                    <?php if ($isIdPdf): ?>
                                        <i class="fa-solid fa-file-pdf" style="font-size: 3rem; color: #EF4444;"></i>
                                    <?php else: ?>
                                        <img src="<?= e($idDocUrl) ?>" alt="ID Scan" style="width: 100%; height: 100%; object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="doc-empty-state" style="padding: 2rem 1rem; margin-bottom: 12px;">
                                    <i class="fa-regular fa-folder-open"></i>
                                    <span>No government ID document scan uploaded.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($idDocUrl)): ?>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;" onclick="openDocPreviewModal('<?= e($idDocUrl) ?>', 'Government Identity Document', <?= $isIdPdf ? 'true' : 'false' ?>)">
                                    <i class="fa-solid fa-eye"></i> Preview Scan
                                </button>
                                <a href="<?= e($idDocUrl) ?>" target="_blank" download class="btn btn-primary btn-sm" style="justify-content: center;">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB CONTENT: EMERGENCY & NOTES
         ========================================================================= -->
    <div id="tabContent-notes" class="view-tab-pane" role="tabpanel">
        <div class="view-section-card">
            <div class="section-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge amber">
                        <i class="fa-regular fa-clipboard"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Emergency Contact &amp; Administrative Notes</h3>
                        <p class="section-subtitle">Emergency contacts, administrative instructions, and personal notes</p>
                    </div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="view-kv-grid" style="margin-bottom: 1.5rem;">
                    <div class="view-kv-item">
                        <span class="view-kv-label">Emergency Contact Name</span>
                        <span class="view-kv-value bold">
                            <?= e($employee['emergency_contact_name'] ?? $employee['emergency_contact'] ?? 'Not Recorded') ?>
                        </span>
                    </div>
                    <div class="view-kv-item">
                        <span class="view-kv-label">Emergency Phone</span>
                        <span class="view-kv-value bold">
                            <?php if (!empty($employee['emergency_contact_phone'])): ?>
                                <a href="tel:<?= e($employee['emergency_contact_phone']) ?>"><?= e($employee['emergency_contact_phone']) ?></a>
                            <?php else: ?>
                                <em class="text-muted">—</em>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($employee['notes'])): ?>
                    <div class="customer-internal-note-box">
                        <h4 style="font-size: 0.92rem; font-weight: 700; color: #0F172A; margin: 0 0 6px 0;">Administrative Remarks</h4>
                        <?= nl2br(e($employee['notes'])) ?>
                    </div>
                <?php else: ?>
                    <div class="doc-empty-state" style="padding: 2rem 1rem;">
                        <i class="fa-regular fa-clipboard" style="font-size: 2rem; color: #94A3B8; margin-bottom: 6px;"></i>
                        <span>No internal remarks or special instructions recorded for this employee.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     DOCUMENT PREVIEW LIGHTBOX MODAL (Matching Customers Architecture)
     ========================================================================= -->
<div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
    <div class="doc-lightbox-container" onclick="event.stopPropagation()">
        <div class="doc-lightbox-header">
            <div class="doc-lightbox-title-wrap">
                <span class="doc-lightbox-icon"><i class="fa-solid fa-file-shield"></i></span>
                <div>
                    <h4 class="doc-lightbox-title" id="docPreviewModalTitle">Compliance Document Preview</h4>
                    <span class="doc-lightbox-sub">Verified Document Scan</span>
                </div>
            </div>
            <div class="doc-lightbox-actions">
                <a href="javascript:void(0)" id="docLightboxDownloadBtn" target="_blank" download class="doc-lightbox-btn-download" title="Download Document">
                    <i class="fa-solid fa-download"></i> <span>Download</span>
                </a>
                <button type="button" class="doc-lightbox-close" onclick="closeDocPreviewModal()" aria-label="Close Preview">&times;</button>
            </div>
        </div>
        <div class="doc-lightbox-body" id="docPreviewModalBody">
            <div style="text-align: center; padding: 3rem; color: #64748B;">Loading document preview...</div>
        </div>
    </div>
</div>

<!-- =========================================================================
     TWO-STEP DELETE CONFIRMATION MODALS (Matches design in delete.jpeg)
     ========================================================================= -->
<div id="deleteModalBackdrop" class="vg-del-backdrop" style="display: none;" onclick="handleBackdropClick(event)">
    <!-- MODAL 1: Initial Delete with Checkbox -->
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
                    This staff profile will be permanently deleted and cannot be recovered.
                </p>

                <div class="vg-del-checkbox-wrap" id="delCheckboxContainer">
                    <label class="vg-del-checkbox-label">
                        <input type="checkbox" id="deleteStep1Checkbox" class="vg-del-checkbox-input" onchange="toggleStep1DeleteBtn()">
                        <span class="vg-del-checkbox-box">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="vg-del-checkbox-text">
                            I understand this employee will be permanently deleted.
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

    <!-- MODAL 2: "Are you sure?" Final Confirmation -->
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
                    You are about to delete employee <strong><?= e($empFullName) ?></strong>. This action cannot be undone.
                </p>

                <div class="vg-del-checkbox-wrap" id="delCheckboxContainer2">
                    <label class="vg-del-checkbox-label">
                        <input type="checkbox" id="deleteStep2Checkbox" class="vg-del-checkbox-input" onchange="toggleStep2DeleteBtn()">
                        <span class="vg-del-checkbox-box">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="vg-del-checkbox-text">
                            Yes, I am completely sure and want to delete this employee.
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="vg-del-actions">
            <button type="button" class="vg-del-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="vg-del-btn-delete disabled" id="deleteConfirmFinalBtn" onclick="confirmDeleteFinal()">
                <i class="fa-solid fa-trash-can"></i>
                <span id="deleteFinalBtnText">Delete</span>
            </button>
        </div>
    </div>
</div>

<!-- Hidden fallback delete form -->
<form id="deleteEmployeeForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
</form>

<script>
    /* ========================================================================= */
    /* Tab Switching Engine                                                      */
    /* ========================================================================= */
    function switchTab(tabId) {
        document.querySelectorAll('.view-nav-pill').forEach(btn => {
            const isTarget = btn.getAttribute('data-tab') === tabId;
            btn.classList.toggle('active', isTarget);
            btn.setAttribute('aria-selected', isTarget ? 'true' : 'false');
        });
        document.querySelectorAll('.view-tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        const targetPane = document.getElementById('tabContent-' + tabId);
        if (targetPane) targetPane.classList.add('active');
    }

    /* ========================================================================= */
    /* More Menu Dropdown                                                        */
    /* ========================================================================= */
    function toggleMoreMenu(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('employeeMoreMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        const moreMenu = document.getElementById('employeeMoreMenu');
        if (moreMenu && !e.target.closest('.view-more-menu-wrapper')) {
            moreMenu.style.display = 'none';
        }
    });

    /* ========================================================================= */
    /* Share Employee Details                                                    */
    /* ========================================================================= */
    function shareEmployeeDetails() {
        const shareData = {
            title: '<?= addslashes(e($empFullName)) ?> - MANASWI Staff Profile',
            text: 'Employee Code: <?= addslashes(e($empCode)) ?> | Role: <?= addslashes(e($roleLabel)) ?> | Phone: <?= addslashes(e($employee['phone'])) ?>',
            url: window.location.href
        };
        if (navigator.share) {
            navigator.share(shareData).catch(() => {});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Staff profile URL copied to clipboard!', 'success');
            } else {
                alert('Profile URL copied to clipboard!');
            }
        }
    }

    /* ========================================================================= */
    /* Live AJAX Employee Status Toggle Switch                                   */
    /* ========================================================================= */
    function toggleEmployeeViewStatus(id, btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!id || btn.classList.contains('is-loading')) return;

        const currentActive = btn.classList.contains('is-active');
        const targetStatus = currentActive ? 'on_leave' : 'active';
        const empName = btn.getAttribute('data-name') || 'Employee';

        document.querySelectorAll('.cust-status-toggle').forEach(b => b.classList.add('is-loading'));
        document.querySelectorAll('.toggle-spinner').forEach(s => s.style.display = 'inline-block');

        const toggleUrl = '<?= url('/office/employees/') ?>' + id + '/toggle-status';
        const csrfToken = '<?= csrf_token() ?>';

        fetch(toggleUrl, {
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
            document.querySelectorAll('.cust-status-toggle').forEach(b => b.classList.remove('is-loading'));
            document.querySelectorAll('.toggle-spinner').forEach(s => s.style.display = 'none');

            if (data.success) {
                const isNowActive = (data.status === 'active');
                const label = data.label || (isNowActive ? 'Active' : 'On Leave');

                // Update all switches
                document.querySelectorAll('.cust-status-toggle').forEach(b => {
                    b.classList.toggle('is-active', isNowActive);
                    b.setAttribute('aria-checked', isNowActive ? 'true' : 'false');
                    b.title = `Click to toggle status (currently ${label})`;
                });

                // Update Hero Pill
                const heroPill = document.getElementById('heroStatusPill');
                const heroText = document.getElementById('heroStatusLabelText');
                if (heroPill) heroPill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
                if (heroText) heroText.textContent = label;

                // Update Info Card Pill
                const infoPill = document.getElementById('infoStatusPill');
                const infoText = document.getElementById('infoStatusLabelText');
                if (infoPill) infoPill.className = 'view-hero-status-pill ' + (isNowActive ? 'active' : 'inactive');
                if (infoText) infoText.textContent = label;

                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || `Employee status updated to ${label}`, 'success');
                }
            } else {
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(data.message || 'Failed to update employee status.', 'error');
                } else {
                    alert(data.message || 'Failed to update employee status.');
                }
            }
        })
        .catch(err => {
            document.querySelectorAll('.cust-status-toggle').forEach(b => {
                b.classList.remove('is-loading');
                b.classList.add('toggle-shake');
                setTimeout(() => b.classList.remove('toggle-shake'), 400);
            });
            document.querySelectorAll('.toggle-spinner').forEach(s => s.style.display = 'none');
            if (typeof window.vgNotify === 'function') {
                window.vgNotify(err.message || 'Error updating employee status.', 'error');
            } else {
                alert(err.message || 'Error updating employee status.');
            }
        });
    }

    /* ========================================================================= */
    /* Document Preview Lightbox Modal                                           */
    /* ========================================================================= */
    function openDocPreviewModal(url, title, isPdf) {
        const modal = document.getElementById('docPreviewModal');
        const modalTitle = document.getElementById('docPreviewModalTitle');
        const modalBody = document.getElementById('docPreviewModalBody');
        const downloadBtn = document.getElementById('docLightboxDownloadBtn');

        if (modalTitle) modalTitle.textContent = title || 'Document Preview';
        if (downloadBtn) downloadBtn.href = url || '#';

        if (modalBody && url) {
            if (isPdf) {
                modalBody.innerHTML = `<iframe src="${url}#toolbar=1" class="doc-lightbox-iframe" title="Document PDF"></iframe>`;
            } else {
                modalBody.innerHTML = `<div class="doc-lightbox-img-wrap"><img src="${url}" alt="${title}" class="doc-lightbox-img"></div>`;
            }
        }

        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDocPreviewModal(e) {
        if (e && e.target && !e.target.classList.contains('doc-lightbox-backdrop') && !e.target.classList.contains('doc-lightbox-close')) {
            return;
        }
        const modal = document.getElementById('docPreviewModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /* ========================================================================= */
    /* Two-Step Delete Modals (Matching customer/index design)                   */
    /* ========================================================================= */
    let currentDeleteId = <?= (int)$employee['id'] ?>;
    let currentDeleteName = '<?= addslashes(e($empFullName)) ?>';

    function openDeleteModal(id, name) {
        currentDeleteId = id;
        currentDeleteName = name || 'this employee';

        const backdrop = document.getElementById('deleteModalBackdrop');
        const step1 = document.getElementById('deleteStep1Modal');
        const step2 = document.getElementById('deleteStep2Modal');
        const chk1 = document.getElementById('deleteStep1Checkbox');
        const btn1 = document.getElementById('deleteStep1Btn');
        const chk2 = document.getElementById('deleteStep2Checkbox');
        const finalBtn = document.getElementById('deleteConfirmFinalBtn');
        const finalBtnText = document.getElementById('deleteFinalBtnText');

        if (chk1) chk1.checked = false;
        if (btn1) btn1.classList.add('disabled');
        if (chk2) chk2.checked = false;
        if (finalBtn) {
            finalBtn.disabled = false;
            finalBtn.classList.add('disabled');
            finalBtn.style.opacity = '';
        }
        if (finalBtnText) finalBtnText.textContent = 'Delete';

        if (step1) step1.style.display = 'block';
        if (step2) step2.style.display = 'none';

        if (backdrop) {
            backdrop.style.display = 'flex';
            setTimeout(() => backdrop.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDeleteModal() {
        const backdrop = document.getElementById('deleteModalBackdrop');
        if (!backdrop) return;
        backdrop.classList.remove('active');
        setTimeout(() => {
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
            const step1 = document.getElementById('deleteStep1Modal');
            const step2 = document.getElementById('deleteStep2Modal');
            if (step1) step1.style.display = 'block';
            if (step2) step2.style.display = 'none';
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
        if (chk && btn) {
            btn.classList.toggle('disabled', !chk.checked);
        }
    }

    function toggleStep2DeleteBtn() {
        const chk = document.getElementById('deleteStep2Checkbox');
        const btn = document.getElementById('deleteConfirmFinalBtn');
        if (chk && btn) {
            btn.classList.toggle('disabled', !chk.checked);
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

        const deleteUrl = '<?= url('/office/employees/') ?>' + currentDeleteId + '/delete';
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
                window.location.href = '<?= url('/office/employees') ?>';
            } else {
                alert(data.message || 'Failed to delete employee profile.');
                closeDeleteModal();
            }
        })
        .catch(err => {
            const form = document.getElementById('deleteEmployeeForm');
            if (form) {
                form.action = deleteUrl;
                form.submit();
            } else {
                alert('An error occurred while deleting employee record.');
                closeDeleteModal();
            }
        });
    }

    // Support keyboard ESC to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDocPreviewModal();
            closeDeleteModal();
        }
    });
</script>

<style>
/* ========================================================================= */
/* Employee View Root & Executive Theme (Identical to Customer View sm.jpeg) */
/* ========================================================================= */
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

.employee-view-root {
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
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.15s ease;
}

.view-dropdown-item:hover {
    background: #F1F5F9;
    color: var(--cv-primary-blue);
}

.view-dropdown-item.danger {
    color: #DC2626;
}

.view-dropdown-item.danger:hover {
    background: #FEF2F2;
    color: #B91C1C;
}

.view-dropdown-divider {
    height: 1px;
    background: #E2E8F0;
    margin: 0.35rem 0;
}

/* 2. Hero Profile Card (Scenic bus & mountains matching Customer View) */
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

.emp-role-pill {
    background: #F1F5F9;
    color: var(--cv-slate-800);
    border: 1px solid #E2E8F0;
    border-radius: var(--cv-radius-pill);
    padding: 3px 10px;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: capitalize;
    display: inline-block;
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

.view-hero-meta-list {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.25rem;
    margin-top: 0.35rem;
    margin-bottom: 0.5rem;
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

/* 3. KPI Cards Grid */
.view-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.view-kpi-card {
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: 16px;
    padding: 1.15rem 1.25rem;
    box-shadow: var(--cv-shadow);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    position: relative;
}

.view-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--cv-shadow-hover);
    border-color: rgba(23, 107, 91, 0.25);
}

.kpi-card-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.kpi-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.kpi-icon-wrap.blue { background: #E8F7F0; color: #176B5B; }
.kpi-icon-wrap.amber { background: #FEF3C7; color: #D97706; }
.kpi-icon-wrap.teal { background: #CCFBF1; color: #0D9488; }

.kpi-trend {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.78rem;
    font-weight: 700;
}

.kpi-trend.up { color: #059669; }
.kpi-trend.neutral { color: #94A3B8; }

.kpi-tag-recent {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 9999px;
    background: #E8F7F0;
    color: #176B5B;
}

.kpi-tag-recent.empty {
    background: #F1F5F9;
    color: #64748B;
}

.kpi-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 3px;
}

.kpi-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.15;
    letter-spacing: -0.02em;
}

.kpi-value.text-compact {
    font-size: 1.15rem;
    font-weight: 700;
}

.kpi-subtext {
    font-size: 0.78rem;
    color: #64748B;
    margin-top: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.kpi-footer-note {
    font-size: 0.74rem;
    color: #94A3B8;
    margin-top: 4px;
}

/* 4. Navigation Pill Tabs */
.view-nav-tabs-wrapper {
    margin-bottom: 1.25rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.view-nav-tabs-wrapper::-webkit-scrollbar {
    display: none;
}

.view-nav-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 4px;
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: 9999px;
    width: fit-content;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.view-nav-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 8px 18px;
    border-radius: 9999px;
    border: none;
    background: transparent;
    color: #64748B;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}

.view-nav-pill:hover {
    color: #0F172A;
    background: #F8FAFC;
}

.view-nav-pill.active {
    background: #176B5B;
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(23, 107, 91, 0.28);
}

.pill-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 800;
    background: rgba(0, 0, 0, 0.08);
    color: inherit;
}

.view-nav-pill.active .pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #FFFFFF;
}

/* 5. Tab Panes & Split Layout */
.view-tab-pane {
    display: none;
}

.view-tab-pane.active {
    display: block;
    animation: fadeInTab 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.view-layout-split {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    align-items: start;
}

.view-col-main,
.view-col-side {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Section Cards */
.view-section-card {
    background: #FFFFFF;
    border: 1px solid var(--cv-card-border);
    border-radius: 18px;
    box-shadow: var(--cv-shadow);
    overflow: hidden;
}

.section-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.15rem 1.4rem;
    border-bottom: 1px solid #F1F5F9;
}

.section-card-header.compact {
    padding: 0.9rem 1.4rem;
}

.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.section-icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
}

.section-icon-badge.blue { background: #E8F7F0; color: #176B5B; }
.section-icon-badge.teal { background: #CCFBF1; color: #0D9488; }
.section-icon-badge.amber { background: #FEF3C7; color: #D97706; }
.section-icon-badge.purple { background: #F3E8FF; color: #7C3AED; }

.section-title {
    font-size: 1.02rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    letter-spacing: -0.01em;
}

.section-subtitle {
    font-size: 0.78rem;
    color: #64748B;
    margin: 2px 0 0 0;
}

.section-header-link-btn {
    font-size: 0.8rem;
    font-weight: 700;
    color: #176B5B;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.section-header-link-btn:hover {
    text-decoration: underline;
}

.verified-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 9999px;
    background: #ECFDF5;
    color: #059669;
    border: 1px solid #A7F3D0;
}

.section-card-body {
    padding: 1.25rem 1.4rem;
}

.section-card-body.p-0 {
    padding: 0;
}

/* Key Value Grid */
.view-kv-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.15rem 1.5rem;
}

.view-kv-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.view-kv-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.view-kv-value {
    font-size: 0.92rem;
    color: #1E293B;
    word-break: break-word;
}

.view-kv-value.bold {
    font-weight: 700;
}

.view-kv-value a {
    color: #176B5B;
    text-decoration: none;
}

.view-kv-value a:hover {
    text-decoration: underline;
}

.view-kv-value.address-val {
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-kv-value.flag-val {
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-kv-status-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 2px;
}

.view-address-detail-box {
    margin-top: 1.25rem;
    padding: 1rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
}

.address-detail-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 4px;
}

.address-detail-text {
    font-size: 0.88rem;
    color: #1E293B;
    line-height: 1.45;
}

.doc-code {
    background: #E8F7F0;
    color: #16443B;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.86rem;
    font-weight: 700;
}

/* Document Card Tile */
.doc-card-tile {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    gap: 12px;
}

.doc-tile-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}

.doc-thumb-img {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #CBD5E1;
}

.doc-file-icon.pdf {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: #FEE2E2;
    color: #DC2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.doc-tile-info {
    min-width: 0;
}

.doc-file-name {
    font-size: 0.86rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.doc-file-status {
    font-size: 0.74rem;
    color: #059669;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

.doc-tile-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.btn-doc-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #334155;
    transition: all 0.15s ease;
}

.btn-doc-action.preview:hover {
    background: #E8F7F0;
    border-color: #93C5FD;
    color: #176B5B;
}

.btn-doc-action.download:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.doc-empty-state {
    padding: 1.25rem;
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 12px;
    text-align: center;
    font-size: 0.85rem;
    color: #64748B;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.doc-empty-state i {
    font-size: 1.6rem;
    color: #94A3B8;
}

.doc-empty-state a {
    color: #176B5B;
    font-weight: 700;
    text-decoration: none;
}

/* Quick Actions Grid */
.quick-actions-title {
    font-size: 0.88rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.quick-action-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 14px 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    text-decoration: none;
    color: #1E293B;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    width: 100%;
}

.quick-action-tile:hover {
    background: #FFFFFF;
    border-color: #93C5FD;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.1);
    transform: translateY(-2px);
}

.qa-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    margin-bottom: 6px;
}

.qa-icon-wrap.blue { background: #E8F7F0; color: #176B5B; }
.qa-icon-wrap.whatsapp { background: #DCFCE7; color: #16A34A; }
.qa-icon-wrap.purple { background: #F3E8FF; color: #7C3AED; }
.qa-icon-wrap.cyan { background: #E0F2FE; color: #0284C7; }

.qa-text {
    font-size: 0.78rem;
    font-weight: 700;
    text-align: center;
}

/* Note box */
.customer-internal-note-box {
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    border-radius: 12px;
    padding: 1rem;
    font-size: 0.88rem;
    color: #78350F;
    line-height: 1.5;
}

/* 6. Trips Table & Mobile Cards */
.btn-primary-compact {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #176B5B;
    color: #FFFFFF;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
    transition: all 0.15s ease;
}

.btn-primary-compact:hover {
    background: #16443B;
    color: #FFFFFF;
}

.table-responsive {
    overflow-x: auto;
}

.view-table {
    width: 100%;
    border-collapse: collapse;
}

.view-table th {
    background: #F8FAFC;
    color: #64748B;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 12px 18px;
    text-align: left;
    border-bottom: 1px solid #E2E8F0;
}

.view-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #F1F5F9;
    font-size: 0.88rem;
    color: #1E293B;
}

.view-table tr:hover td {
    background: #F8FAFC;
}

.table-code-link code {
    background: #E8F7F0;
    color: #16443B;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 700;
}

.table-title-bold {
    font-weight: 700;
    color: #0F172A;
}

.table-date-meta {
    font-size: 0.82rem;
    color: #64748B;
}

.invoice-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #F1F5F9;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
}

.table-action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #176B5B;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
}

.table-action-link:hover {
    text-decoration: underline;
}

/* Mobile Bookings Cards */
.mobile-bookings-cards-list {
    display: none;
    padding: 1rem;
    flex-direction: column;
    gap: 10px;
}

.mb-booking-card {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 12px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mb-booking-card:hover {
    border-color: #93C5FD;
    background: #FFFFFF;
}

.mb-booking-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.mb-booking-code {
    font-family: monospace;
    font-size: 0.82rem;
    font-weight: 700;
    color: #16443B;
}

.mb-booking-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0 0 6px 0;
}

.mb-booking-meta-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-size: 0.8rem;
    color: #64748B;
    margin-bottom: 8px;
}

.mb-booking-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #E2E8F0;
}

.mb-booking-arrow {
    font-size: 0.78rem;
    font-weight: 700;
    color: #176B5B;
}

.tab-empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #E8F7F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0 0 6px 0;
}

.empty-text {
    font-size: 0.88rem;
    color: #64748B;
    max-width: 420px;
    margin: 0 0 1.25rem 0;
    line-height: 1.45;
}

/* 7. Status Toggle Switch (Executive Standards matching Customers) */
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
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background: #CBD5E1;
    border-radius: 9999px;
    transition: background-color 0.26s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.26s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}

.cust-status-toggle.is-active .toggle-slider-track {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35), inset 0 1px 2px rgba(255, 255, 255, 0.25);
}

.toggle-slider-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #FFFFFF;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.22);
    transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.cust-status-toggle.is-active .toggle-slider-thumb {
    transform: translateX(20px);
}

.toggle-spinner {
    font-size: 10px;
    color: #176B5B;
}

.cust-status-toggle.sm .toggle-slider-track {
    width: 36px;
    height: 20px;
}

.cust-status-toggle.sm .toggle-slider-thumb {
    width: 16px;
    height: 16px;
}

.cust-status-toggle.sm.is-active .toggle-slider-thumb {
    transform: translateX(16px);
}

.cust-status-toggle.toggle-shake {
    animation: toggleShake 0.4s ease;
}

@keyframes toggleShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}

/* 8. Document Lightbox Modal */
.doc-lightbox-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    animation: fadeInModal 0.2s ease;
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

.doc-lightbox-container {
    background: #FFFFFF;
    border-radius: 20px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
}

.doc-lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.4rem;
    border-bottom: 1px solid #E2E8F0;
}

.doc-lightbox-title-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.doc-lightbox-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #CCFBF1;
    color: #0D9488;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.doc-lightbox-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
}

.doc-lightbox-sub {
    font-size: 0.76rem;
    color: #64748B;
}

.doc-lightbox-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.doc-lightbox-btn-download {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #E8F7F0;
    color: #176B5B;
    border: 1px solid rgba(23, 107, 91, 0.25);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
}

.doc-lightbox-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #F1F5F9;
    border: none;
    color: #475569;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.doc-lightbox-body {
    padding: 1rem;
    overflow-y: auto;
    max-height: calc(90vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F8FAFC;
}

.doc-lightbox-iframe {
    width: 100%;
    height: 70vh;
    border: none;
    border-radius: 8px;
}

.doc-lightbox-img-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 1rem;
}

.doc-lightbox-img {
    max-width: 100%;
    max-height: 68vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

/* 9. Two-Step Delete Modals (Matching delete.jpeg) */
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

/* 10. Responsive Breakpoints */
@media (max-width: 991px) {
    .view-layout-split {
        grid-template-columns: 1fr;
    }
    .view-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .employee-view-root {
        padding: 0.75rem 0.5rem 2.5rem 0.5rem;
    }
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
    .view-kv-grid {
        grid-template-columns: 1fr;
    }
    .desktop-bookings-table {
        display: none;
    }
    .mobile-bookings-cards-list {
        display: flex;
    }
}

@media (max-width: 480px) {
    .view-kpi-grid {
        grid-template-columns: 1fr;
    }
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    .view-hero-name {
        font-size: 1.35rem;
    }
    .doc-card-tile {
        flex-direction: column;
        align-items: flex-start;
    }
    .doc-tile-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
