<?php
/**
 * Live GPS Telemetry and Location Sharing Console — Dedicated Driver Portal
 * Real Geolocation Tracking with Leaflet OSM, Security Diagnostics, and Duty Verification
 */
$isOnDuty = ($driver['duty_status'] ?? 'off_duty') === 'on_duty';
$isOnTrip = ($driver['duty_status'] ?? 'off_duty') === 'on_trip' || !empty($activeTrip);
$isOffDuty = !$isOnDuty && !$isOnTrip;
$hasKnownFix = !empty($driver['current_latitude']) && !empty($driver['current_longitude']);
$lastLog = !empty($recentLogs) ? $recentLogs[0] : null;
$lastAccuracy = $lastLog['accuracy_m'] ?? null;
$gpsUpdateUrl = url('/api/gps/update');
?>

<!-- Leaflet CSS & JS for OpenStreetMap Preview (Served locally from public assets) -->
<link rel="stylesheet" href="<?= asset('leaflet/leaflet.css') ?>"/>
<script src="<?= asset('leaflet/leaflet.js') ?>"></script>

<!-- Security / Environment Diagnostics Alert (Insecure Context / Geolocation Support) -->
<div id="geoWarningBanner" style="display: none; margin-bottom: 1.25rem;"></div>

<?php if ($isOffDuty): ?>
    <div class="card" id="offDutyWarningCard" style="border-left: 4px solid var(--vg-gold-500); margin-bottom: 1.25rem; background: #FFFDF5;">
        <div class="card-body" style="padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <strong style="color: var(--vg-navy-950); display: block; font-size: 0.9rem;"><?= e(__('driver.location_sharing_paused')) ?></strong>
                    <span style="color: var(--vg-text-muted); font-size: 0.8rem;">
                        <?= e(__('status.off_duty')) ?>
                    </span>
                </div>
            </div>
            <form action="<?= url('/driver/duty/toggle') ?>" method="POST" style="margin: 0;">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="on_duty">
                <button type="submit" class="btn btn-primary btn-sm" style="background: var(--vg-gold-500); border-color: var(--vg-gold-500); color: var(--vg-navy-950); font-weight: 700;">
                    <i class="fa-solid fa-power-off" style="margin-right: 4px;"></i> <?= e(__('btn.go_on_duty')) ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--vg-navy-950); margin: 0 0 0.25rem; letter-spacing: -0.02em;">
            <?= e(__('sidebar.live_location')) ?>
        </h1>
        <div style="font-size: 0.85rem; color: var(--vg-text-muted);">
            <?= e(__('driver.location_sharing_active')) ?>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <button type="button" id="toggleTrackingBtn" class="btn btn-primary" onclick="window.toggleTelemetrySharing()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-satellite-dish" id="trackingBtnIcon"></i> <span id="toggleTrackingBtnText"><?= e(__('driver.location_sharing_active')) ?></span>
        </button>
    </div>
</div>

<!-- Telemetry Live Status Card -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.35rem 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div id="telemetryStatusIcon" style="width: 52px; height: 52px; border-radius: 50%; background: var(--vg-pearl-100); color: var(--vg-text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: all 0.3s ease;">
                    <i class="fa-solid fa-satellite-dish" id="statusRadarIcon"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); letter-spacing: 0.05em;">
                            <?= e(__('tracking.signal_status')) ?>
                        </span>
                        <span id="dutyStatusBadge" class="badge <?= $isOnTrip ? 'badge-navy' : ($isOnDuty ? 'badge-success' : 'badge-pearl') ?>" style="font-size: 0.68rem;">
                            <?= $isOnTrip ? e(__('status.on_trip')) : ($isOnDuty ? e(__('status.on_duty')) : e(__('status.off_duty'))) ?>
                        </span>
                    </div>
                    <div id="gpsStatusText" style="font-size: 1.15rem; font-weight: 800; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= e(__('status.gps_live')) ?>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--vg-text-muted);">
                        <?= e(__('tracking.last_ping')) ?>: <span id="lastGpsSyncTime" style="font-weight: 600; color: var(--vg-navy-950);"><?= $hasKnownFix ? format_datetime($driver['last_gps_at']) : e(__('table.no_records')) ?></span>
                    </div>
                </div>
            </div>

            <!-- Active Trip Context or Standby Policy -->
            <div>
                <?php if (!empty($activeTrip)): ?>
                    <div style="background: var(--vg-blue-50); border: 1px solid var(--vg-blue-100); border-radius: var(--vg-radius-md); padding: 0.65rem 1rem; font-size: 0.85rem;">
                        <span style="color: var(--vg-text-muted); font-size: 0.75rem; display: block;">Transmitting for Active Trip:</span>
                        <strong style="color: var(--vg-blue-600);"><?= e($activeTrip['booking_code']) ?> (<?= e($activeTrip['vehicle_number']) ?>)</strong>
                    </div>
                <?php else: ?>
                    <div style="background: var(--vg-pearl-100); border-radius: var(--vg-radius-md); padding: 0.65rem 1rem; font-size: 0.82rem; color: var(--vg-text-muted);">
                        <i class="fa-solid fa-shield-halved" style="color: var(--vg-success); margin-right: 4px;"></i> Standby Mode (On-Duty Chauffeur Broadcast)
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Map Preview and Coordinates Card -->
<div class="vg-split-2" style="margin-bottom: 1.5rem;">
    <!-- Interactive Map Canvas -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm); overflow: hidden; display: flex; flex-direction: column;">
        <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; font-size: 0.95rem; color: var(--vg-navy-950);">
                <i class="fa-solid fa-map-marked-alt" style="color: var(--vg-blue-500); margin-right: 6px;"></i> Live Position Preview
            </div>
            <span id="mapAccuracyBadge" class="badge badge-navy" style="font-size: 0.7rem;">
                <?= $hasKnownFix ? 'Cached GPS' : 'Awaiting Fix' ?>
            </span>
        </div>
        <div class="card-body" style="padding: 0; position: relative; min-height: 360px; flex: 1;">
            <div id="driverLiveMap" style="width: 100%; height: 360px; background: #E2E8F0;"></div>
            <div id="mapAwaitingNotice" style="position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 1000; background: rgba(11, 25, 44, 0.88); backdrop-filter: blur(4px); color: #FFF; padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 0.75rem; pointer-events: none; display: <?= $hasKnownFix ? 'none' : 'block' ?>;">
                <i class="fa-solid fa-location-crosshairs" style="color: var(--vg-gold-400); margin-right: 4px;"></i> Awaiting browser GPS lock
            </div>
        </div>
    </div>

    <!-- Telemetry Details & Coordinates -->
    <div class="card" style="box-shadow: var(--vg-shadow-sm);">
        <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; font-size: 0.95rem; color: var(--vg-navy-950);">
                <i class="fa-solid fa-location-crosshairs" style="color: var(--vg-gold-500); margin-right: 6px;"></i> Verified Telemetry Readings
            </div>
            <span id="syncIndicatorDot" style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #94A3B8;" title="Connection State"></span>
        </div>
        <div class="card-body" style="padding: 1.25rem;">
            <!-- Coordinate Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.75rem 0.85rem;">
                    <div style="font-size: 0.7rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Latitude</div>
                    <div id="displayLat" style="font-weight: 800; font-size: 1.05rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= $hasKnownFix ? number_format((float)$driver['current_latitude'], 6) : '—' ?>
                    </div>
                </div>

                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.75rem 0.85rem;">
                    <div style="font-size: 0.7rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Longitude</div>
                    <div id="displayLng" style="font-weight: 800; font-size: 1.05rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= $hasKnownFix ? number_format((float)$driver['current_longitude'], 6) : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Accuracy & Dynamics Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.75rem 0.85rem;">
                    <div style="font-size: 0.7rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Precision Accuracy</div>
                    <div id="displayAccuracy" style="font-weight: 800; font-size: 0.95rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?php if (!$hasKnownFix): ?>
                            <span style="color: var(--vg-text-muted); font-size: 0.82rem; font-weight: 600;">Waiting for GPS fix</span>
                        <?php elseif ($lastAccuracy !== null && (float)$lastAccuracy > 1000): ?>
                            ± <?= number_format((float)$lastAccuracy / 1000, 1) ?> km (Network)
                        <?php elseif ($lastAccuracy !== null): ?>
                            ± <?= round((float)$lastAccuracy) ?> m (GPS)
                        <?php else: ?>
                            <span style="color: var(--vg-text-muted); font-size: 0.82rem; font-weight: 600;">Saved Coordinates</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.75rem 0.85rem;">
                    <div style="font-size: 0.7rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">Speed &amp; Heading</div>
                    <div id="displaySpeedHeading" style="font-weight: 800; font-size: 0.95rem; color: var(--vg-navy-950); margin-top: 2px;">
                        <?= ($hasKnownFix && !empty($lastLog['speed_kmh'])) ? number_format((float)$lastLog['speed_kmh'], 1) . ' km/h' : '0.0 km/h' ?> • <?= (!empty($lastLog['heading_degrees'])) ? round((float)$lastLog['heading_degrees']) . '°' : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Permission & GPS Guidelines -->
            <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); padding: 0.85rem 1rem; font-size: 0.8rem; margin-bottom: 1.25rem;">
                <div style="font-weight: 700; color: var(--vg-navy-950); margin-bottom: 0.25rem;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--vg-success); margin-right: 4px;"></i> Location Privacy &amp; Cryptographic Verification
                </div>
                <div style="color: var(--vg-text-muted); line-height: 1.45;">
                    Live coordinates are authenticated via CSRF token headers and validated against your current chauffeur session. Tracking stops immediately when you end the session.
                </div>
            </div>

            <!-- Manual Coordinate Sync Button -->
            <button type="button" id="manualSyncBtn" class="btn btn-outline" onclick="window.requestSingleGpsUpdate()" style="width: 100%; font-size: 0.85rem;">
                <i class="fa-solid fa-location-arrow" id="manualSyncBtnIcon" style="margin-right: 5px;"></i> <span id="manualSyncBtnText">Request Single GPS Fix</span>
            </button>
        </div>
    </div>
</div>

<!-- Recent GPS Telemetry Breadcrumbs -->
<div class="card" style="box-shadow: var(--vg-shadow-sm); margin-bottom: 1.5rem;">
    <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.15rem 1.35rem; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-950); margin: 0;">
            <i class="fa-solid fa-shoe-prints" style="color: var(--vg-navy-700); margin-right: 6px;"></i> Recent Telemetry Log Trail
        </h2>
        <span id="breadcrumbCountLabel" style="font-size: 0.75rem; color: var(--vg-text-muted);">Last <?= count($recentLogs) ?> recorded fixes</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table" id="telemetryLogsTable" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th>Recorded Time</th>
                        <th>Coordinates</th>
                        <th>Accuracy</th>
                        <th>Speed (km/h)</th>
                        <th>Heading</th>
                        <th>Dispatch / Policy</th>
                    </tr>
                </thead>
                <tbody id="telemetryLogsBody">
                    <?php if (empty($recentLogs)): ?>
                        <tr id="emptyLogsRow">
                            <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; color: var(--vg-text-muted); font-size: 0.85rem;">
                                No GPS breadcrumbs logged yet. Activate location sharing or execute a trip to stream telemetry.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-950);">
                                        <?= format_datetime($log['recorded_at']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 0.82rem;">
                                        <?= number_format((float)$log['latitude'], 5) ?>, <?= number_format((float)$log['longitude'], 5) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: var(--vg-text-muted);">
                                        <?php if (!empty($log['accuracy_m'])): ?>
                                            <?= (float)$log['accuracy_m'] > 1000 ? ('± ' . number_format((float)$log['accuracy_m'] / 1000, 1) . ' km') : ('± ' . round((float)$log['accuracy_m']) . ' m') ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= number_format((float)($log['speed_kmh'] ?? 0), 1) ?> km/h</strong>
                                </td>
                                <td>
                                    <?= !empty($log['heading_degrees']) ? number_format((float)$log['heading_degrees'], 0) . '°' : '—' ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['dispatch_id'])): ?>
                                        <span class="badge badge-navy" style="font-size: 0.72rem;">#<?= (int)$log['dispatch_id'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-pearl" style="font-size: 0.72rem; color: var(--vg-navy-700);">On Duty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// =============================================================================
// GLOBAL CONFIGURATION & SUBDIRECTORY ENDPOINT RESOLUTION
// =============================================================================
const GPS_UPDATE_URL = <?= json_encode($gpsUpdateUrl) ?>;
const IS_OFF_DUTY = <?= $isOffDuty ? 'true' : 'false' ?>;
const DRIVER_NAME = <?= json_encode($user['name'] ?? 'Chauffeur') ?>;
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

    // 1. Update Text Fields (Always succeeds even if Map is offline)
    const elLat = document.getElementById('displayLat');
    const elLng = document.getElementById('displayLng');
    const elAcc = document.getElementById('displayAccuracy');
    const elSpd = document.getElementById('displaySpeedHeading');
    const awaitingNotice = document.getElementById('mapAwaitingNotice');
    const accuracyBadge = document.getElementById('mapAccuracyBadge');
    const dot = document.getElementById('syncIndicatorDot');

    if (elLat) elLat.innerText = lat.toFixed(6);
    if (elLng) elLng.innerText = lng.toFixed(6);

    // Format accuracy clearly based on physical resolution
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
        dot.style.background = isServerConfirmed ? 'var(--vg-success)' : 'var(--vg-gold-500)';
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

            // Avoid drawing giant 200km circles that cover entire states/regions
            if (accuracy && accuracy <= 5000) {
                if (!accuracyCircle) {
                    accuracyCircle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#2563EB',
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

            // Adaptive Map Zoom
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
        : ('<span class="badge badge-pearl" style="font-size: 0.72rem; color: var(--vg-navy-700);">On Duty</span>');

    const formattedAcc = entry.accuracy
        ? (entry.accuracy > 1000 ? ('± ' + (entry.accuracy / 1000).toFixed(1) + ' km') : ('± ' + Math.round(entry.accuracy) + ' m'))
        : '—';

    tr.innerHTML = `
        <td>
            <div style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-950);">${entry.time}</div>
        </td>
        <td>
            <span style="font-family: monospace; font-size: 0.82rem;">${entry.lat.toFixed(5)}, ${entry.lng.toFixed(5)}</span>
        </td>
        <td>
            <span style="font-size: 0.82rem; color: var(--vg-text-muted);">${formattedAcc}</span>
        </td>
        <td>
            <strong>${entry.speed} km/h</strong>
        </td>
        <td>
            ${entry.heading}
        </td>
        <td>
            ${dispatchBadge}
        </td>
    `;

    if (tbody.firstChild) {
        tbody.insertBefore(tr, tbody.firstChild);
    } else {
        tbody.appendChild(tr);
    }
}

// =============================================================================
// GLOBAL BUTTON HANDLERS (DEFINED IMMEDIATELY — INDEPENDENT OF MAP/DOM)
// =============================================================================

// 1. Start / Stop Continuous Telemetry Sharing
window.toggleTelemetrySharing = function () {
    const btnText = document.getElementById('toggleTrackingBtnText');
    const btnIcon = document.getElementById('trackingBtnIcon');
    const icon = document.getElementById('telemetryStatusIcon');
    const statusText = document.getElementById('gpsStatusText');

    // Policy check: prevent tracking while off duty
    if (IS_OFF_DUTY) {
        showBannerAlert('⚠️ Cannot transmit location while Off Duty. Please switch to On Duty first using the button above.', 'warning');
        if (statusText) {
            statusText.innerText = 'Off Duty — Location Sharing Prohibited';
            statusText.style.color = 'var(--vg-danger)';
        }
        return;
    }

    if (!window.VoyageTelemetry) {
        showBannerAlert('Telemetry client library is not loaded. Please refresh the page.', 'danger');
        return;
    }

    if (window.VoyageTelemetry.isTracking) {
        // Stop tracking
        window.VoyageTelemetry.stop();
        if (btnText) btnText.innerText = 'Start Telemetry Sharing';
        if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
        if (icon) {
            icon.style.background = 'var(--vg-pearl-100)';
            icon.style.color = 'var(--vg-text-muted)';
        }
        if (statusText) {
            statusText.innerText = 'Standby (Sharing Stopped)';
            statusText.style.color = 'var(--vg-navy-950)';
        }
    } else {
        // Start tracking: Immediate visual feedback
        if (btnText) btnText.innerText = 'Acquiring GPS Signal...';
        if (btnIcon) btnIcon.className = 'fa-solid fa-spinner fa-spin';
        if (statusText) {
            statusText.innerText = 'Acquiring Browser GPS Coordinates...';
            statusText.style.color = 'var(--vg-navy-950)';
        }

        const started = window.VoyageTelemetry.start(GPS_UPDATE_URL);
        if (!started) {
            // Geolocation unsupported or insecure context
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
        if (syncBtnText) syncBtnText.innerText = 'Request Single GPS Fix';
        if (syncBtnIcon) syncBtnIcon.className = 'fa-solid fa-location-arrow';
    }

    // Policy check: prevent tracking while off duty
    if (IS_OFF_DUTY) {
        showBannerAlert('⚠️ Cannot send GPS update while Off Duty. Please switch to On Duty first using the button above.', 'warning');
        if (statusText) {
            statusText.innerText = 'Off Duty — Location Sharing Prohibited';
            statusText.style.color = 'var(--vg-danger)';
        }
        return;
    }

    if (!navigator.geolocation) {
        showBannerAlert('Geolocation is not supported by your browser or device.', 'danger');
        return;
    }

    // Immediate button loading feedback
    if (syncBtn) syncBtn.disabled = true;
    if (syncBtnText) syncBtnText.innerText = 'Acquiring GPS Fix...';
    if (syncBtnIcon) syncBtnIcon.className = 'fa-solid fa-spinner fa-spin';
    if (statusText) {
        statusText.innerText = 'Acquiring high-accuracy browser location...';
        statusText.style.color = 'var(--vg-navy-950)';
    }

    // Stage 1: Try high accuracy; fallback to standard accuracy on timeout
    function executeAttempt(highAccuracy) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                // Immediate local coordinate display
                updateLocationDisplay(position.coords, false);

                if (window.VoyageTelemetry) {
                    window.VoyageTelemetry.sendLocation(position.coords, GPS_UPDATE_URL)
                        .then(data => {
                            updateLocationDisplay(position.coords, true, data);
                            if (statusText) {
                                statusText.innerText = 'GPS Fix Synchronized (' + (data.data?.policy === 'active_trip' ? 'Active Trip' : 'On Duty') + ')';
                                statusText.style.color = 'var(--vg-navy-950)';
                            }
                        })
                        .catch(err => {
                            if (statusText) {
                                statusText.innerText = 'Server rejected fix: ' + err.message;
                                statusText.style.color = 'var(--vg-danger)';
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
                    // Timeout on high accuracy: switch to standard Wi-Fi / IP positioning
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
                    statusText.style.color = 'var(--vg-danger)';
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
    // 1. Browser Geolocation & Environment Diagnostic Checks
    if (!navigator.geolocation) {
        showBannerAlert('⚠️ Geolocation is not supported by your current browser or device. Please switch to Chrome, Safari, Firefox, or Edge.', 'danger');
    } else if (window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        showBannerAlert('🔒 Geolocation requires an encrypted HTTPS connection. Location requests may be blocked by your browser.', 'warning');
    }

    // 2. Safe Leaflet Map Initialization with Catch & Fallback
    try {
        if (typeof L !== 'undefined' && document.getElementById('driverLiveMap')) {
            // Configure local Leaflet asset image path
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
                <div style="padding: 3rem 1.5rem; text-align: center; color: var(--vg-navy-950);">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 2.5rem; color: var(--vg-text-muted); margin-bottom: 0.75rem; display: block;"></i>
                    <strong style="font-size: 0.95rem;">Map Preview Offline</strong>
                    <p style="font-size: 0.82rem; color: var(--vg-text-muted); margin: 0.35rem 0 0.75rem;">
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

    // 3. Connect VoyageTelemetry Hooks
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
                statusText.style.color = 'var(--vg-danger)';
            }
            const dot = document.getElementById('syncIndicatorDot');
            if (dot) dot.style.background = 'var(--vg-danger)';

            // Reset button to start state on fatal errors
            const btnText = document.getElementById('toggleTrackingBtnText');
            const btnIcon = document.getElementById('trackingBtnIcon');
            const icon = document.getElementById('telemetryStatusIcon');
            if (btnText) btnText.innerText = 'Start Telemetry Sharing';
            if (btnIcon) btnIcon.className = 'fa-solid fa-satellite-dish';
            if (icon) {
                icon.style.background = 'var(--vg-pearl-100)';
                icon.style.color = 'var(--vg-text-muted)';
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
                    icon.style.background = 'var(--vg-pearl-100)';
                    icon.style.color = 'var(--vg-text-muted)';
                }
                if (statusText) {
                    statusText.innerText = 'Standby (Sharing Stopped)';
                    statusText.style.color = 'var(--vg-navy-950)';
                }
            } else {
                if (btnText) btnText.innerText = 'Stop Telemetry Sharing';
                if (btnIcon) btnIcon.className = 'fa-solid fa-stop';
                if (icon) {
                    icon.style.background = 'var(--vg-success-bg)';
                    icon.style.color = 'var(--vg-success)';
                }
                if (statusText) {
                    statusText.innerText = 'Transmitting Live GPS Telemetry';
                    statusText.style.color = 'var(--vg-navy-950)';
                }
            }
        };
    }
});
</script>
