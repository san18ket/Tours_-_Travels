<?php
/**
 * Live GPS Telemetry and Location Sharing Console — Dedicated Driver Portal
 * Real Geolocation Tracking with Leaflet OSM, Security Diagnostics, and Duty Verification
 * Aligned with Owner/Office Portal Design System
 */
$isOnDuty     = ($driver['duty_status'] ?? 'off_duty') === 'on_duty';
$isOnTrip     = ($driver['duty_status'] ?? 'off_duty') === 'on_trip' || !empty($activeTrip);
$isOffDuty    = !$isOnDuty && !$isOnTrip;
$hasKnownFix  = !empty($driver['current_latitude']) && !empty($driver['current_longitude']);
$lastLog      = !empty($recentLogs) ? $recentLogs[0] : null;
$lastAccuracy = $lastLog['accuracy_m'] ?? null;
$gpsUpdateUrl = url('/api/gps/update');

$actionsHtml = '<div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">'
    . '<a href="' . url('/driver/dashboard') . '" class="btn btn-outline" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-gauge" style="margin-right:6px;"></i><span>Console</span></a>'
    . '<a href="' . url('/driver/trips') . '" class="btn btn-primary" style="border-radius:10px; font-weight:700;"><i class="fa-solid fa-route" style="margin-right:6px;"></i><span>Active Trips</span></a>'
    . '</div>';
?>

<!-- Leaflet CSS & JS for OpenStreetMap Preview (Served locally from public assets) -->
<link rel="stylesheet" href="<?= asset('leaflet/leaflet.css') ?>"/>
<script src="<?= asset('leaflet/leaflet.js') ?>"></script>

<div class="bookings-page-view driver-portal-container" id="driverLocationPage">
    <!-- Security / Environment Diagnostics Alert -->
    <div id="geoWarningBanner" style="display: none; margin-bottom: 1.25rem;"></div>

    <!-- 1. Executive Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => 'Live GPS Telemetry &amp; Location Sharing',
            'description' => 'Transmit high-precision browser coordinates to fleet dispatch and verify real-time position during active duties.',
            'badge'       => $isOnTrip ? 'On Active Mission' : ($isOnDuty ? 'Driver On Duty' : 'Off Duty Standby'),
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/driver/dashboard')],
                ['label' => 'Live Location', 'url' => ''],
            ],
            'actions'     => $actionsHtml,
        ]) ?>
    </div>

    <?php if ($isOffDuty): ?>
        <!-- Off Duty Warning Alert -->
        <div class="driver-card" id="offDutyWarningCard" style="border-left: 4px solid #F59E0B; margin-bottom: 1.5rem; background: #FFFDF5; padding: 1.15rem 1.5rem;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <strong style="color: #0F172A; display: block; font-size: 0.95rem;">Location Sharing Paused — You are Off Duty</strong>
                        <span style="color: #64748B; font-size: 0.82rem;">
                            GPS telemetry transmission is disabled while your status is Off Duty to preserve privacy.
                        </span>
                    </div>
                </div>
                <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin: 0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; font-weight: 700; font-size: 0.85rem;">
                        <i class="fa-solid fa-power-off" style="margin-right: 6px;"></i> Resume Duty &amp; Enable GPS
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Primary Telemetry Live Status Card -->
    <div class="driver-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1.15rem;">
                <div id="telemetryStatusIcon" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: #64748B; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; transition: all 0.3s ease;">
                    <i class="fa-solid fa-satellite-dish" id="statusRadarIcon"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.76rem; text-transform: uppercase; font-weight: 800; color: #64748B; letter-spacing: 0.05em;">
                            Signal Status
                        </span>
                        <span id="dutyStatusBadge" class="badge <?= $isOnTrip ? 'badge-navy' : ($isOnDuty ? 'badge-success' : 'badge-pearl') ?>" style="font-size: 0.72rem; padding: 3px 8px;">
                            <?= $isOnTrip ? 'On Trip' : ($isOnDuty ? 'On Duty' : 'Off Duty') ?>
                        </span>
                    </div>
                    <div id="gpsStatusText" style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin-top: 3px;">
                        <?= $hasKnownFix ? 'GPS Lock Synchronized' : 'Ready for Telemetry Fix' ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #64748B; margin-top: 2px;">
                        Last Satellite Ping: <strong id="lastGpsSyncTime" style="color: #0F172A;"><?= $hasKnownFix ? format_datetime($driver['last_gps_at']) : 'No coordinates recorded' ?></strong>
                    </div>
                </div>
            </div>

            <!-- Active Trip Context & Main Toggle Button -->
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <?php if (!empty($activeTrip)): ?>
                    <div style="background: #EFF6FF; border: 1px solid #DBEAFE; border-radius: 10px; padding: 0.65rem 1rem; font-size: 0.85rem;">
                        <span style="color: #64748B; font-size: 0.72rem; display: block; font-weight: 600;">Active Trip Context:</span>
                        <strong style="color: #1E40AF;"><?= e($activeTrip['booking_code']) ?> &bull; <?= e($activeTrip['vehicle_number']) ?></strong>
                    </div>
                <?php endif; ?>

                <button type="button" id="toggleTrackingBtn" class="btn btn-primary" onclick="window.toggleTelemetrySharing()" style="border-radius: 10px; font-weight: 700; padding: 0.65rem 1.25rem; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-satellite-dish" id="trackingBtnIcon"></i>
                    <span id="toggleTrackingBtnText">Start Telemetry Sharing</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Desktop 2-Column Responsive Layout: Map + Telemetry Dashboard -->
    <div class="driver-location-grid" style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 1.5rem; margin-bottom: 1.5rem; align-items: stretch;">
        <!-- Left Column: Interactive Map Canvas -->
        <div class="driver-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #0F172A; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-map-location-dot" style="color: #176B5B;"></i>
                    <span>Live Driver Position Preview</span>
                </div>
                <span id="mapAccuracyBadge" class="badge badge-navy" style="font-size: 0.72rem;">
                    <?= $hasKnownFix ? 'Cached Fix' : 'Awaiting Fix' ?>
                </span>
            </div>
            <div style="padding: 0; position: relative; min-height: 440px; flex: 1;">
                <div id="driverLiveMap" style="width: 100%; height: 440px; background: #E2E8F0;"></div>
                <div id="mapAwaitingNotice" style="position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 1000; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(4px); color: #FFF; padding: 0.45rem 1rem; border-radius: 20px; font-size: 0.78rem; pointer-events: none; display: <?= $hasKnownFix ? 'none' : 'block' ?>;">
                    <i class="fa-solid fa-location-crosshairs" style="color: #10B981; margin-right: 6px;"></i> Awaiting browser GPS lock
                </div>
            </div>
        </div>

        <!-- Right Column: Telemetry Diagnostics & Fix Trigger -->
        <div class="driver-card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.15rem; padding-bottom: 0.75rem; border-bottom: 1px solid #E2E8F0;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0F172A; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-location-crosshairs" style="color: #D97706;"></i>
                        <span>Verified Coordinates</span>
                    </div>
                    <span id="syncIndicatorDot" style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #94A3B8;" title="<?= e(__('tooltip.connection_state', 'Connection State')) ?>"></span>
                </div>

                <!-- Coordinate Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem;">
                        <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Latitude</div>
                        <div id="displayLat" style="font-weight: 800; font-size: 1.1rem; color: #0F172A; margin-top: 2px;">
                            <?= $hasKnownFix ? number_format((float)$driver['current_latitude'], 6) : '—' ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem;">
                        <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Longitude</div>
                        <div id="displayLng" style="font-weight: 800; font-size: 1.1rem; color: #0F172A; margin-top: 2px;">
                            <?= $hasKnownFix ? number_format((float)$driver['current_longitude'], 6) : '—' ?>
                        </div>
                    </div>
                </div>

                <!-- Accuracy & Dynamics Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem;">
                        <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Precision Accuracy</div>
                        <div id="displayAccuracy" style="font-weight: 800; font-size: 0.92rem; color: #0F172A; margin-top: 2px;">
                            <?php if (!$hasKnownFix): ?>
                                <span style="color: #64748B; font-size: 0.8rem; font-weight: 600;">Waiting for GPS fix</span>
                            <?php elseif ($lastAccuracy !== null && (float)$lastAccuracy > 1000): ?>
                                ± <?= number_format((float)$lastAccuracy / 1000, 1) ?> km (Network)
                            <?php elseif ($lastAccuracy !== null): ?>
                                ± <?= round((float)$lastAccuracy) ?> m (GPS)
                            <?php else: ?>
                                <span style="color: #64748B; font-size: 0.8rem; font-weight: 600;">Saved Coordinates</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem;">
                        <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Speed &amp; Heading</div>
                        <div id="displaySpeedHeading" style="font-weight: 800; font-size: 0.92rem; color: #0F172A; margin-top: 2px;">
                            <?= ($hasKnownFix && !empty($lastLog['speed_kmh'])) ? number_format((float)$lastLog['speed_kmh'], 1) . ' km/h' : '0.0 km/h' ?> &bull; <?= (!empty($lastLog['heading_degrees'])) ? round((float)$lastLog['heading_degrees']) . '°' : '—' ?>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Cryptographic Verification Note -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.85rem 1rem; font-size: 0.8rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 700; color: #0F172A; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved" style="color: #10B981;"></i>
                        <span>Authenticated Driver Session</span>
                    </div>
                    <div style="color: #64748B; line-height: 1.45; font-size: 0.76rem;">
                        Telemetry coordinates are signed with CSRF token authentication. Transmission stops immediately whenever you end duty or close the app.
                    </div>
                </div>
            </div>

            <!-- Manual Coordinate Fix Request -->
            <div>
                <button type="button" id="manualSyncBtn" class="btn btn-outline" onclick="window.requestSingleGpsUpdate()" style="width: 100%; border-radius: 10px; font-weight: 700; font-size: 0.85rem; padding: 0.65rem;">
                    <i class="fa-solid fa-location-arrow" id="manualSyncBtnIcon" style="margin-right: 6px;"></i>
                    <span id="manualSyncBtnText">Request Instant GPS Fix</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Recent GPS Telemetry Breadcrumbs Table -->
    <section class="recent-bookings-section" id="locationLogsSection">
        <div class="section-header">
            <h3 class="section-title">Recent Telemetry Breadcrumb Trail</h3>
            <div class="total-records-box">
                <i class="fa-solid fa-layer-group"></i>
                <span class="total-records-label">Recorded Fixes:</span>
                <span class="total-records-badge" id="breadcrumbCountLabel"><?= count($recentLogs) ?></span>
            </div>
        </div>

        <div class="modern-table-container">
            <table class="modern-table" id="telemetryLogsTable">
                <thead>
                    <tr>
                        <th class="col-priority-1"><?= e(__('table.recorded_time', 'Recorded Time')) ?></th>
                        <th class="col-priority-1"><?= e(__('table.gps_coords', 'GPS Coordinates')) ?></th>
                        <th class="col-priority-2" data-column-priority="secondary"><?= e(__('table.precision', 'Precision')) ?></th>
                        <th class="col-priority-1">Speed (km/h)</th>
                        <th class="col-priority-3 col-optional" data-column-priority="optional"><?= e(__('table.heading', 'Heading')) ?></th>
                        <th class="text-center col-priority-2" data-column-priority="secondary" style="width: 130px;"><?= e(__('table.policy_state', 'Policy State')) ?></th>
                    </tr>
                </thead>
                <tbody id="telemetryLogsBody">
                    <?php if (empty($recentLogs)): ?>
                        <tr id="emptyLogsRow">
                            <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; color: #64748B; font-size: 0.88rem;">
                                No GPS breadcrumbs logged yet. Activate location sharing or commence a trip to stream real-time telemetry.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td class="col-priority-1">
                                    <div style="font-weight: 700; font-size: 0.86rem; color: #0F172A;">
                                        <?= format_datetime($log['recorded_at']) ?>
                                    </div>
                                </td>
                                <td class="col-priority-1">
                                    <span style="font-family: monospace; font-size: 0.84rem; color: #1E293B;">
                                        <?= number_format((float)$log['latitude'], 5) ?>, <?= number_format((float)$log['longitude'], 5) ?>
                                    </span>
                                </td>
                                <td class="col-priority-2" data-column-priority="secondary">
                                    <span style="font-size: 0.82rem; color: #64748B;">
                                        <?php if (!empty($log['accuracy_m'])): ?>
                                            <?= (float)$log['accuracy_m'] > 1000 ? ('± ' . number_format((float)$log['accuracy_m'] / 1000, 1) . ' km') : ('± ' . round((float)$log['accuracy_m']) . ' m') ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="col-priority-1">
                                    <strong><?= number_format((float)($log['speed_kmh'] ?? 0), 1) ?> km/h</strong>
                                </td>
                                <td class="col-priority-3 col-optional" data-column-priority="optional">
                                    <?= !empty($log['heading_degrees']) ? number_format((float)$log['heading_degrees'], 0) . '°' : '—' ?>
                                </td>
                                <td class="text-center col-priority-2" data-column-priority="secondary">
                                    <?php if (!empty($log['dispatch_id'])): ?>
                                        <span class="badge badge-navy" style="font-size: 0.72rem;">#<?= (int)$log['dispatch_id'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success" style="font-size: 0.72rem;">On Duty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
// =============================================================================
// GLOBAL CONFIGURATION & SUBDIRECTORY ENDPOINT RESOLUTION
// =============================================================================
const GPS_UPDATE_URL = <?= json_encode($gpsUpdateUrl) ?>;
const IS_OFF_DUTY = <?= $isOffDuty ? 'true' : 'false' ?>;
const DRIVER_NAME = <?= json_encode($user['name'] ?? 'Driver') ?>;
const HAS_INITIAL_FIX = <?= $hasKnownFix ? 'true' : 'false' ?>;
const INITIAL_LAT = <?= $hasKnownFix ? (float)$driver['current_latitude'] : 20.5937 ?>;
const INITIAL_LNG = <?= $hasKnownFix ? (float)$driver['current_longitude'] : 78.9629 ?>;

// State references
let driverMap = null;
let driverMarker = null;
let accuracyCircle = null;

// =============================================================================
// DIAGNOSTIC ALERT HELPERS
// =============================================================================
function showBannerAlert(msg, type = 'warning') {
    const notifyType = (type === 'danger' || type === 'error') ? 'error' : (type === 'warning' ? 'warning' : 'info');
    if (typeof window.vgNotify === 'function') {
        window.vgNotify(msg, notifyType);
    }
    const warningBanner = document.getElementById('geoWarningBanner');
    if (!warningBanner) return;
    warningBanner.className = 'alert alert-' + type;
    warningBanner.innerHTML = '<span>' + msg + '</span>';
    warningBanner.style.display = 'block';
}

function clearBannerAlert() {
    const warningBanner = document.getElementById('geoWarningBanner');
    if (warningBanner) warningBanner.style.display = 'none';
}

// =============================================================================
// DOM & MAP COORDINATE DISPLAY UPDATE (INDEPENDENT OF LEAFLET)
// =============================================================================
function updateLocationDisplay(coords, isServerConfirmed = false, serverData = null) {
    const lat = coords.latitude;
    const lng = coords.longitude;
    const accuracy = coords.accuracy != null && !isNaN(coords.accuracy) ? Number(coords.accuracy) : null;
    const speed = coords.speed != null ? (coords.speed * 3.6).toFixed(1) : '0.0';
    const heading = coords.heading != null && !isNaN(coords.heading) ? Math.round(coords.heading) + '°' : '—';

    // 1. Update Text Fields
    const elLat = document.getElementById('displayLat');
    const elLng = document.getElementById('displayLng');
    const elAcc = document.getElementById('displayAccuracy');
    const elSpd = document.getElementById('displaySpeedHeading');
    const awaitingNotice = document.getElementById('mapAwaitingNotice');
    const accuracyBadge = document.getElementById('mapAccuracyBadge');
    const dot = document.getElementById('syncIndicatorDot');

    if (elLat) elLat.innerText = lat.toFixed(6);
    if (elLng) elLng.innerText = lng.toFixed(6);

    let accText = 'Waiting for GPS fix';
    let badgeText = 'Awaiting Fix';
    let badgeClass = 'badge badge-pearl';

    if (accuracy !== null) {
        if (accuracy <= 50) {
            accText = '± ' + Math.round(accuracy) + ' m (High Precision GPS)';
            badgeText = 'GPS (±' + Math.round(accuracy) + 'm)';
            badgeClass = 'badge badge-success';
        } else if (accuracy <= 1000) {
            accText = '± ' + Math.round(accuracy) + ' m (Wi-Fi / Cell)';
            badgeText = 'Wi-Fi (±' + Math.round(accuracy) + 'm)';
            badgeClass = 'badge badge-navy';
        } else {
            accText = '± ' + (accuracy / 1000).toFixed(1) + ' km (Network / City Area)';
            badgeText = 'Network (±' + Math.round(accuracy / 1000) + 'km)';
            badgeClass = 'badge badge-warning';
        }
    }

    if (elAcc) elAcc.innerText = accText;
    if (elSpd) elSpd.innerText = speed + ' km/h • ' + heading;
    if (awaitingNotice) awaitingNotice.style.display = 'none';

    if (accuracyBadge) {
        accuracyBadge.innerText = badgeText;
        accuracyBadge.className = badgeClass;
    }

    if (dot) {
        dot.style.background = isServerConfirmed ? '#10B981' : '#F59E0B';
        dot.title = isServerConfirmed ? 'Connected & Verified' : 'Acquired (Syncing)';
    }

    // 2. Safely Update Map & Marker IF Leaflet Loaded
    try {
        if (driverMap && typeof L !== 'undefined') {
            if (!driverMarker) {
                driverMarker = L.marker([lat, lng]).addTo(driverMap);
            } else {
                driverMarker.setLatLng([lat, lng]);
            }

            driverMarker.bindPopup("<b>" + DRIVER_NAME + "</b><br>Live GPS Lock<br><small>" + (new Date().toLocaleTimeString()) + "</small>");

            if (accuracy && accuracy <= 5000) {
                if (!accuracyCircle) {
                    accuracyCircle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#176B5B',
                        fillColor: '#60A5FA',
                        fillOpacity: 0.18,
                        weight: 1.5
                    }).addTo(driverMap);
                } else {
                    accuracyCircle.setLatLng([lat, lng]);
                    accuracyCircle.setRadius(accuracy);
                }
            } else if (accuracyCircle) {
                driverMap.removeLayer(accuracyCircle);
                accuracyCircle = null;
            }

            let zoomLevel = 15;
            if (accuracy) {
                if (accuracy <= 100) zoomLevel = 16;
                else if (accuracy <= 1000) zoomLevel = 14;
                else if (accuracy <= 25000) zoomLevel = 11;
                else zoomLevel = 9;
            }
            driverMap.setView([lat, lng], zoomLevel);
        }
    } catch (e) {
        console.warn('Map rendering skipped:', e.message);
    }

    // 3. Prepend entry to breadcrumb trail upon server confirmation
    if (isServerConfirmed && serverData) {
        prependBreadcrumbRow({
            time: serverData.data?.formatted_time || (new Date().toLocaleTimeString()),
            lat: lat,
            lng: lng,
            accuracy: accuracy,
            speed: speed,
            heading: heading,
            policy: serverData.data?.policy,
            dispatchId: serverData.data?.dispatch_id,
            bookingCode: serverData.data?.booking_code
        });
    }
}

function prependBreadcrumbRow(entry) {
    const tbody = document.getElementById('telemetryLogsBody');
    if (!tbody) return;

    const emptyRow = document.getElementById('emptyLogsRow');
    if (emptyRow) emptyRow.remove();

    const tr = document.createElement('tr');
    const dispatchBadge = entry.dispatchId
        ? ('<span class="badge badge-navy" style="font-size: 0.72rem;">#' + entry.dispatchId + '</span>')
        : ('<span class="badge badge-success" style="font-size: 0.72rem;">On Duty</span>');

    const formattedAcc = entry.accuracy
        ? (entry.accuracy > 1000 ? ('± ' + (entry.accuracy / 1000).toFixed(1) + ' km') : ('± ' + Math.round(entry.accuracy) + ' m'))
        : '—';

    tr.innerHTML = `
        <td>
            <div style="font-weight: 700; font-size: 0.86rem; color: #0F172A;">${entry.time}</div>
        </td>
        <td>
            <span style="font-family: monospace; font-size: 0.84rem; color: #1E293B;">${entry.lat.toFixed(5)}, ${entry.lng.toFixed(5)}</span>
        </td>
        <td>
            <span style="font-size: 0.82rem; color: #64748B;">${formattedAcc}</span>
        </td>
        <td>
            <strong>${entry.speed} km/h</strong>
        </td>
        <td>
            ${entry.heading}
        </td>
        <td class="text-center">
            ${dispatchBadge}
        </td>
    `;

    if (tbody.firstChild) {
        tbody.insertBefore(tr, tbody.firstChild);
    } else {
        tbody.appendChild(tr);
    }
}

// 1. Start / Stop Continuous Telemetry Sharing
window.toggleTelemetrySharing = function () {
    const btnText = document.getElementById('toggleTrackingBtnText');
    const btnIcon = document.getElementById('trackingBtnIcon');
    const icon = document.getElementById('telemetryStatusIcon');
    const statusText = document.getElementById('gpsStatusText');

    if (IS_OFF_DUTY) {
        showBannerAlert('⚠️ Cannot transmit location while Off Duty. Please switch to On Duty first using the button above.', 'warning');
        if (statusText) {
            statusText.innerText = 'Off Duty — Location Sharing Prohibited';
            statusText.style.color = '#EF4444';
        }
        return;
    }

    if (!window.VoyageTelemetry) {
        showBannerAlert('Telemetry client library is not loaded. Please refresh the page.', 'danger');
        return;
    }

    if (window.VoyageTelemetry.isTracking) {
        window.VoyageTelemetry.stop();
        if (btnText) btnText.innerText = 'Start Telemetry Sharing';
        if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
        if (icon) {
            icon.style.background = '#F1F5F9';
            icon.style.color = '#64748B';
        }
        if (statusText) {
            statusText.innerText = 'Standby (Sharing Stopped)';
            statusText.style.color = '#0F172A';
        }
    } else {
        if (btnText) btnText.innerText = 'Acquiring GPS Signal...';
        if (btnIcon) btnIcon.className = 'fa-solid fa-spinner fa-spin';
        if (statusText) {
            statusText.innerText = 'Acquiring Browser GPS Coordinates...';
            statusText.style.color = '#0F172A';
        }

        const started = window.VoyageTelemetry.start(GPS_UPDATE_URL);
        if (!started) {
            if (btnText) btnText.innerText = 'Start Telemetry Sharing';
            if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
        }
    }
};

// 2. Request Single GPS Fix with Adaptive Two-Stage Fallback
window.requestSingleGpsUpdate = function () {
    const syncBtn = document.getElementById('manualSyncBtn');
    const syncBtnText = document.getElementById('manualSyncBtnText');
    const syncBtnIcon = document.getElementById('manualSyncBtnIcon');
    const statusText = document.getElementById('gpsStatusText');

    function restoreSingleSyncButton() {
        if (syncBtn) syncBtn.disabled = false;
        if (syncBtnText) syncBtnText.innerText = 'Request Instant GPS Fix';
        if (syncBtnIcon) syncBtnIcon.className = 'fa-solid fa-location-arrow';
    }

    if (IS_OFF_DUTY) {
        showBannerAlert('⚠️ Cannot send GPS update while Off Duty. Please switch to On Duty first using the button above.', 'warning');
        if (statusText) {
            statusText.innerText = 'Off Duty — Location Sharing Prohibited';
            statusText.style.color = '#EF4444';
        }
        return;
    }

    if (!navigator.geolocation) {
        showBannerAlert('Geolocation is not supported by your browser or device.', 'danger');
        return;
    }

    if (syncBtn) syncBtn.disabled = true;
    if (syncBtnText) syncBtnText.innerText = 'Acquiring GPS Fix...';
    if (syncBtnIcon) syncBtnIcon.className = 'fa-solid fa-spinner fa-spin';
    if (statusText) {
        statusText.innerText = 'Acquiring high-accuracy browser location...';
        statusText.style.color = '#0F172A';
    }

    function executeAttempt(highAccuracy) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                updateLocationDisplay(position.coords, false);

                if (window.VoyageTelemetry) {
                    window.VoyageTelemetry.sendLocation(position.coords, GPS_UPDATE_URL)
                        .then(data => {
                            updateLocationDisplay(position.coords, true, data);
                            if (statusText) {
                                statusText.innerText = 'GPS Fix Synchronized (' + (data.data?.policy === 'active_trip' ? 'Active Trip' : 'On Duty') + ')';
                                statusText.style.color = '#0F172A';
                            }
                        })
                        .catch(err => {
                            if (statusText) {
                                statusText.innerText = 'Server rejected fix: ' + err.message;
                                statusText.style.color = '#EF4444';
                            }
                            showBannerAlert('Telemetry transmission failed: ' + err.message, 'danger');
                        })
                        .finally(() => {
                            restoreSingleSyncButton();
                        });
                } else {
                    restoreSingleSyncButton();
                }
            },
            function (error) {
                if (error.code === 3 && highAccuracy) {
                    console.warn('High-accuracy GPS attempt timed out. Falling back to Network/Wi-Fi positioning...');
                    if (statusText) {
                        statusText.innerText = 'Satellite timeout. Acquiring Wi-Fi / Network location...';
                    }
                    executeAttempt(false);
                    return;
                }

                let msg = 'Could not acquire GPS: ' + error.message;
                if (error.code === 1) msg = 'Location access denied. Please allow location permissions in your browser address bar.';
                else if (error.code === 2) msg = 'Location unavailable. Ensure GPS hardware or Windows Location Services are enabled.';
                else if (error.code === 3) msg = 'Location request timed out. Please check Windows location services or retry.';

                if (statusText) {
                    statusText.innerText = msg;
                    statusText.style.color = '#EF4444';
                }
                showBannerAlert(msg, 'danger');
                restoreSingleSyncButton();
            },
            {
                enableHighAccuracy: highAccuracy,
                timeout: highAccuracy ? 8000 : 15000,
                maximumAge: highAccuracy ? 0 : 60000
            }
        );
    }

    executeAttempt(true);
};

// =============================================================================
// DOM CONTENT LOADED — MAP INITIALIZATION & TELEMETRY WIRING
// =============================================================================
document.addEventListener('DOMContentLoaded', function () {
    if (!navigator.geolocation) {
        showBannerAlert('⚠️ Geolocation is not supported by your current browser or device. Please switch to Chrome, Safari, Firefox, or Edge.', 'danger');
    } else if (window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        showBannerAlert('🔒 Geolocation requires an encrypted HTTPS connection. Location requests may be blocked by your browser.', 'warning');
    }

    try {
        if (typeof L !== 'undefined' && document.getElementById('driverLiveMap')) {
            L.Icon.Default.imagePath = <?= json_encode(asset('leaflet/images/') . '/') ?>;

            driverMap = L.map('driverLiveMap').setView([INITIAL_LAT, INITIAL_LNG], HAS_INITIAL_FIX ? 15 : 5);

            const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
            });

            tileLayer.on('tileerror', function (err) {
                console.warn('Leaflet tile layer network error:', err);
            });

            tileLayer.addTo(driverMap);

            if (HAS_INITIAL_FIX) {
                driverMarker = L.marker([INITIAL_LAT, INITIAL_LNG]).addTo(driverMap);
                driverMarker.bindPopup("<b>" + DRIVER_NAME + "</b><br>Last Known Position").openPopup();
            }

            window.driverMap = driverMap;
            window.driverMarker = driverMarker;
        } else {
            renderMapOfflineNotice('Leaflet library is unavailable. Live GPS telemetry continues normally.');
        }
    } catch (mapErr) {
        console.warn('Leaflet map initialization failed:', mapErr);
        renderMapOfflineNotice('Interactive map could not load. Live GPS telemetry continues normally.');
    }

    function renderMapOfflineNotice(msg) {
        const mapContainer = document.getElementById('driverLiveMap');
        if (mapContainer) {
            mapContainer.innerHTML = `
                <div style="padding: 3rem 1.5rem; text-align: center; color: #0F172A;">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 2.5rem; color: #94A3B8; margin-bottom: 0.75rem; display: block;"></i>
                    <strong style="font-size: 0.95rem;">Map Preview Offline</strong>
                    <p style="font-size: 0.82rem; color: #64748B; margin: 0.35rem 0 0.75rem;">
                        ${msg}
                    </p>
                    <span class="badge badge-success" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Live GPS acquisition remains fully operational
                    </span>
                </div>
            `;
        }
        const badge = document.getElementById('mapAccuracyBadge');
        if (badge) {
            badge.innerText = 'Map Offline';
            badge.className = 'badge badge-pearl';
        }
    }

    if (window.VoyageTelemetry) {
        window.VoyageTelemetry.onRawFix = function (coords) {
            updateLocationDisplay(coords, false);
        };

        window.VoyageTelemetry.onServerConfirmed = function (coords, serverData) {
            updateLocationDisplay(coords, true, serverData);
        };

        window.VoyageTelemetry.onError = function (errorMsg, type, raw) {
            const statusText = document.getElementById('gpsStatusText');
            if (statusText) {
                statusText.innerText = errorMsg;
                statusText.style.color = '#EF4444';
            }
            const dot = document.getElementById('syncIndicatorDot');
            if (dot) dot.style.background = '#EF4444';

            const btnText = document.getElementById('toggleTrackingBtnText');
            const btnIcon = document.getElementById('trackingBtnIcon');
            const icon = document.getElementById('telemetryStatusIcon');
            if (btnText) btnText.innerText = 'Start Telemetry Sharing';
            if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
            if (icon) {
                icon.style.background = '#F1F5F9';
                icon.style.color = '#64748B';
            }

            if (type === 'permission_denied' || type === 'insecure_context' || type === 'unsupported') {
                showBannerAlert(errorMsg, 'danger');
            }
        };

        window.VoyageTelemetry.onStateChange = function (isTracking) {
            const btnText = document.getElementById('toggleTrackingBtnText');
            const btnIcon = document.getElementById('trackingBtnIcon');
            const icon = document.getElementById('telemetryStatusIcon');
            const statusText = document.getElementById('gpsStatusText');

            if (!isTracking) {
                if (btnText) btnText.innerText = 'Start Telemetry Sharing';
                if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
                if (icon) {
                    icon.style.background = '#F1F5F9';
                    icon.style.color = '#64748B';
                }
                if (statusText) {
                    statusText.innerText = 'Standby (Sharing Stopped)';
                    statusText.style.color = '#0F172A';
                }
            } else {
                if (btnText) btnText.innerText = 'Stop Telemetry Sharing';
                if (btnIcon) btnIcon.className = 'fa-solid fa-stop';
                if (icon) {
                    icon.style.background = '#ECFDF5';
                    icon.style.color = '#10B981';
                }
                if (statusText) {
                    statusText.innerText = 'Transmitting Live GPS Telemetry';
                    statusText.style.color = '#0F172A';
                }
            }
        };
    }
});
</script>
