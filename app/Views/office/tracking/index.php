<!-- Live Tracking Header -->
<?= render_page_header([
    'title'       => __('tracking.title', 'Live Fleet Telemetry & GPS Radar'),
    'description' => __('tracking.subtitle', 'Real-time vehicle coordinates, active tour dispatches, chauffeur duty status, and speed tracking.'),
    'image'       => 'header-tracking.jpg',
    'badge'       => 'Satellite GPS Active',
    'breadcrumbs' => [
        ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
        ['label' => __('sidebar.tracking', 'Tracking'), 'url' => ''],
    ],
    'actions'     => '
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <div class="fleet-sync-badge" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); border-radius: 999px; padding: 0.35rem 0.85rem; font-size: 0.82rem; color: #FFFFFF; display: flex; align-items: center; gap: 0.45rem;">
                <span id="trackerPulseIndicator" class="pulse-indicator" style="background: #10B981;"></span>
                <span id="trackerStatusLabel" style="font-weight: 600; color: #FFFFFF;">' . e(__('tracking.radar_active')) . '</span>
                <span style="color: rgba(255,255,255,0.6);">&bull;</span>
                <span style="color: rgba(255,255,255,0.85);">' . e(__('tracking.auto_sync')) . ': <strong id="lastTrackerSync" style="color: var(--brand-accent, #D6B77A);">&mdash;</strong></span>
            </div>
            <button type="button" id="manualSyncBtn" class="btn btn-primary" onclick="window.fetchLiveFleet(true)" title="' . e(__('tracking.sync_radar')) . '">
                <svg id="syncBtnIcon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span id="syncBtnLabel">' . e(__('tracking.sync_radar')) . '</span>
            </button>
        </div>',
]) ?>

<!-- Mobile Segmented View Switcher (Visible only on screens <= 960px) -->
<div class="fleet-mobile-segmented-bar" id="fleetMobileSegmented">
    <button type="button" class="fleet-segment-btn active" id="segBtnMap" onclick="switchMobileTrackingView('map')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
        <span><?= e(__('tracking.radar_map')) ?></span>
    </button>
    <button type="button" class="fleet-segment-btn" id="segBtnList" onclick="switchMobileTrackingView('list')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        <span><?= e(__('tracking.vehicles_count')) ?> (<span id="mobileCountBadge">0</span>)</span>
    </button>
</div>

<!-- Telemetry Legend & Filter Ribbon -->
<div class="fleet-legend-ribbon">
    <div style="font-weight: 700; color: var(--vg-navy-900);"><?= e(__('tracking.signal_status')) ?>:</div>
    <div class="legend-chip">
        <span class="legend-dot" style="background: #10B981;"></span>
        <span class="legend-text"><?= e(__('tracking.signal_live')) ?></span>
    </div>
    <div class="legend-chip">
        <span class="legend-dot" style="background: #F59E0B;"></span>
        <span class="legend-text"><?= e(__('tracking.signal_delayed')) ?></span>
    </div>
    <div class="legend-chip">
        <span class="legend-dot" style="background: #94A3B8;"></span>
        <span class="legend-text"><?= e(__('tracking.signal_offline')) ?></span>
    </div>
    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <input type="text" id="fleetFilterInput" class="form-control form-control-sm" placeholder="<?= e(__('tracking.filter_placeholder')) ?>" style="width: 180px; font-size: 0.82rem; padding: 0.35rem 0.65rem;" oninput="applyFleetFilter()">
    </div>
</div>

<!-- Radar Container: Split Map & Active Fleet Rail -->
<div class="fleet-radar-grid" id="fleetRadarGrid">
    <!-- Interactive Map Canvas Card -->
    <div class="vg-card fleet-map-card" id="fleetMapCard">
        <!-- Leaflet Map Container -->
        <div id="fleetMap" class="fleet-map-canvas"></div>

        <!-- Telemetry Sync Notification / Warning Overlay -->
        <div id="radarWarningOverlay" class="radar-warning-overlay">
            ⚠️ <?= e(__('tracking.sync_radar')) ?>
        </div>
    </div>

    <!-- Active Fleet Side Panel -->
    <div class="vg-card fleet-side-card" id="fleetSideCard">
        <div class="fleet-side-header">
            <div>
                <span class="card-title" style="font-size: 1rem; margin: 0;"><?= e(__('tracking.active_vehicles')) ?></span>
                <div style="font-size: 0.75rem; color: var(--vg-text-muted);"><?= e(__('tracking.live_feed')) ?></div>
            </div>
            <span id="activeVehicleCount" class="badge badge-navy">0 <?= e(__('status.active')) ?></span>
        </div>

        <div id="vehicleListRail" class="fleet-vehicle-rail">
            <!-- Rendered dynamically via JavaScript -->
            <div id="emptyTrackingState" class="fleet-empty-state">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📡</div>
                <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.95rem; margin-bottom: 0.25rem;"><?= e(__('tracking.no_live_drivers')) ?></div>
                <p style="font-size: 0.82rem; line-height: 1.4; margin: 0;">
                    <?= e(__('tracking.no_live_drivers')) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Embedded Responsive CSS for Fleet Radar -->
<style>
.fleet-tracking-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.fleet-tracking-title-wrap {
    min-width: 240px;
    flex: 1;
}
.fleet-tracking-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--vg-navy-900);
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem 0;
}
.fleet-tracking-subtitle {
    color: var(--vg-text-secondary);
    font-size: 0.88rem;
    margin: 0;
}
.fleet-tracking-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.fleet-sync-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--vg-pearl-100);
    padding: 0.45rem 0.85rem;
    border-radius: var(--vg-radius-md);
    border: 1px solid var(--vg-pearl-200);
    font-size: 0.82rem;
}

/* Mobile Segmented Switcher */
.fleet-mobile-segmented-bar {
    display: none;
    margin-bottom: 1rem;
    background: var(--vg-pearl-100);
    padding: 4px;
    border-radius: var(--vg-radius-md);
    border: 1px solid var(--vg-pearl-200);
    gap: 4px;
    width: 100%;
}
.fleet-segment-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    background: transparent;
    color: var(--vg-text-secondary);
    border-radius: var(--vg-radius-sm);
    cursor: pointer;
    min-height: 44px;
    touch-action: manipulation;
    transition: all 0.2s ease;
}
.fleet-segment-btn.active {
    background: #FFFFFF;
    color: var(--vg-navy-900);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

/* Legend Ribbon */
.fleet-legend-ribbon {
    display: flex;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
    background: #FFFFFF;
    padding: 0.75rem 1.25rem;
    border-radius: var(--vg-radius-md);
    border: 1px solid var(--vg-pearl-200);
    flex-wrap: wrap;
    align-items: center;
    font-size: 0.82rem;
}
.legend-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.legend-text {
    color: var(--vg-text-primary);
    font-weight: 600;
}

/* Radar Layout Grid */
.fleet-radar-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    min-height: 600px;
}
.fleet-map-card {
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 560px;
    position: relative;
    border: 1px solid var(--vg-pearl-200);
}
.fleet-map-canvas {
    width: 100%;
    height: 100%;
    min-height: 560px;
    flex: 1;
    z-index: 1;
}
.radar-warning-overlay {
    display: none;
    position: absolute;
    top: 1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 999;
    background: rgba(15, 23, 42, 0.92);
    color: #FFF;
    padding: 0.5rem 1rem;
    border-radius: 30px;
    font-size: 0.8rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    backdrop-filter: blur(8px);
    max-width: 90%;
    text-align: center;
}
.fleet-side-card {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    max-height: 700px;
    border: 1px solid var(--vg-pearl-200);
}
.fleet-side-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--vg-pearl-200);
}
.fleet-vehicle-rail {
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding-right: 2px;
}
.fleet-empty-state {
    text-align: center;
    padding: 2.5rem 1rem;
    color: var(--vg-text-muted);
}
.custom-fleet-marker {
    background: transparent;
    border: none;
}
.spin-anim {
    animation: vg-spin 0.8s linear infinite;
}
@keyframes vg-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive Adaptations */
@media (max-width: 960px) {
    .fleet-mobile-segmented-bar {
        display: flex;
    }
    .fleet-radar-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .fleet-map-card {
        min-height: 420px;
        height: 420px;
    }
    .fleet-map-canvas {
        min-height: 420px;
        height: 100%;
    }
    .fleet-side-card {
        max-height: 460px;
    }

    /* Mobile view toggle behavior */
    .fleet-radar-grid.mobile-view-map .fleet-side-card {
        display: none !important;
    }
    .fleet-radar-grid.mobile-view-list .fleet-map-card {
        display: none !important;
    }
}

@media (max-width: 600px) {
    .fleet-tracking-title {
        font-size: 1.25rem;
    }
    .fleet-tracking-actions {
        width: 100%;
        justify-content: space-between;
    }
    .fleet-sync-badge {
        flex: 1;
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }
    .fleet-legend-ribbon {
        font-size: 0.76rem;
        padding: 0.65rem 0.85rem;
        gap: 0.75rem;
    }
    .fleet-map-card {
        min-height: 380px;
        height: 380px;
    }
    .fleet-map-canvas {
        min-height: 380px;
    }
}
</style>

<!-- Locally Served, Restrictive-CSP-Compliant Leaflet (No unpkg CDN, No blocked source maps) -->
<link rel="stylesheet" href="<?= asset('leaflet/leaflet.css') ?>"/>
<script src="<?= asset('leaflet/leaflet.js') ?>"></script>

<script>
(function() {
    'use strict';

    let map = null;
    let markers = {};
    let pollInterval = null;
    let isSyncing = false;
    let currentFleetData = [];
    let lastSyncTimestamp = 0;
    let hasInitiallyFitted = false;

    // Mobile View Toggle
    window.switchMobileTrackingView = function(view) {
        const grid = document.getElementById('fleetRadarGrid');
        const btnMap = document.getElementById('segBtnMap');
        const btnList = document.getElementById('segBtnList');

        if (!grid) return;

        if (view === 'map') {
            grid.classList.remove('mobile-view-list');
            grid.classList.add('mobile-view-map');
            if (btnMap) btnMap.classList.add('active');
            if (btnList) btnList.classList.remove('active');
            if (map) {
                setTimeout(() => map.invalidateSize(), 150);
            }
        } else {
            grid.classList.remove('mobile-view-map');
            grid.classList.add('mobile-view-list');
            if (btnMap) btnMap.classList.remove('active');
            if (btnList) btnList.classList.add('active');
        }
    };

    function initMap() {
        if (typeof L === 'undefined') {
            console.warn('Leaflet failed to load.');
            const overlay = document.getElementById('radarWarningOverlay');
            if (overlay) {
                overlay.innerText = '⚠️ Leaflet map library failed to initialize.';
                overlay.style.display = 'block';
            }
            return;
        }

        // Configure local Leaflet asset image path to prevent CDN 404s
        if (L.Icon && L.Icon.Default && L.Icon.Default.imagePath === undefined) {
            L.Icon.Default.imagePath = '<?= asset('leaflet/images/') ?>/';
        }

        // Center on India / Delhi HQ by default
        map = L.map('fleetMap', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([28.6139, 77.2090], 11);

        // Clean OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Initialize segmented view on mobile (default to map view)
        if (window.innerWidth <= 960) {
            window.switchMobileTrackingView('map');
        }

        // Window resize handler to invalidate map viewport size
        window.addEventListener('resize', () => {
            if (map) map.invalidateSize();
        });

        // Trigger initial fetch
        window.fetchLiveFleet(true);

        // Setup clean polling interval (15s)
        startPolling();
    }

    function startPolling() {
        stopPolling();
        pollInterval = setInterval(() => {
            window.fetchLiveFleet(false);
        }, 15000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function createMarkerIcon(statusClass, plateNumber) {
        let pinColor = '#10B981'; // green for live
        if (statusClass === 'delayed') pinColor = '#F59E0B'; // amber for delayed
        if (statusClass === 'stale') pinColor = '#94A3B8'; // gray for offline

        const html = `
            <div style="position: relative; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                <div style="width: 26px; height: 26px; border-radius: 50%; background: ${pinColor}; border: 3px solid #FFFFFF; box-shadow: 0 4px 10px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #FFFFFF;"></div>
                </div>
            </div>
        `;

        return L.divIcon({
            className: 'custom-fleet-marker',
            html: html,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -18]
        });
    }

    function computeSignalStatus(lastGpsIso) {
        if (!lastGpsIso) return 'stale';
        const now = new Date();
        const gpsTime = new Date(lastGpsIso);
        const diffMinutes = (now - gpsTime) / (1000 * 60);

        if (diffMinutes < 2) return 'live';
        if (diffMinutes <= 10) return 'delayed';
        return 'stale';
    }

    /**
     * Primary live fleet data fetcher
     * @param {boolean} isManual True if user clicked manual refresh
     */
    window.fetchLiveFleet = function(isManual = false) {
        // Prevent overlapping requests
        if (isSyncing) return;
        isSyncing = true;

        const syncBtn = document.getElementById('manualSyncBtn');
        const syncIcon = document.getElementById('syncBtnIcon');
        const syncLabel = document.getElementById('syncBtnLabel');
        const warnOverlay = document.getElementById('radarWarningOverlay');

        if (isManual && syncBtn) {
            syncBtn.disabled = true;
            if (syncIcon) syncIcon.classList.add('spin-anim');
            if (syncLabel) syncLabel.innerText = 'Syncing...';
        }

        const requestTime = Date.now();

        fetch('<?= url('/api/fleet/live') ?>', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (res.status === 401 || res.status === 403) {
                throw new Error('AUTH_EXPIRED');
            }
            if (!res.ok) {
                throw new Error('HTTP_' + res.status);
            }
            return res.json();
        })
        .then(data => {
            // Drop older out-of-order responses
            if (requestTime < lastSyncTimestamp) {
                return;
            }
            lastSyncTimestamp = requestTime;

            if (!data.success) {
                console.warn('API returned non-success:', data);
                if (warnOverlay) {
                    warnOverlay.innerText = '⚠️ ' + (data.error || 'Unable to sync telemetry.');
                    warnOverlay.style.display = 'block';
                }
                return;
            }

            if (warnOverlay) warnOverlay.style.display = 'none';

            const lastSyncEl = document.getElementById('lastTrackerSync');
            if (lastSyncEl) {
                const now = new Date();
                lastSyncEl.innerText = now.toLocaleTimeString();
            }

            currentFleetData = data.fleet || [];
            updateFleetMarkers(currentFleetData, isManual);

            // If manual refresh, reset the polling interval timer
            if (isManual) {
                startPolling();
            }
        })
        .catch(err => {
            console.warn('Live tracking sync warning:', err);
            if (err.message === 'AUTH_EXPIRED') {
                if (warnOverlay) {
                    warnOverlay.innerHTML = '🔒 Session expired. <a href="<?= url('/login') ?>" style="color:#60A5FA;text-decoration:underline;">Click here to sign in</a>';
                    warnOverlay.style.display = 'block';
                }
                stopPolling();
            } else {
                if (warnOverlay) {
                    warnOverlay.innerText = '⚠️ Telemetry sync delayed. Displaying last verified positions.';
                    warnOverlay.style.display = 'block';
                }
            }
        })
        .finally(() => {
            isSyncing = false;
            if (syncBtn) {
                syncBtn.disabled = false;
                if (syncIcon) syncIcon.classList.remove('spin-anim');
                if (syncLabel) syncLabel.innerText = 'Sync Radar';
            }
        });
    };

    /**
     * Update map markers and vehicle panel list
     * @param {Array} fleet Array of vehicle/driver telemetry records
     * @param {boolean} isManual True if manual sync triggered
     */
    function updateFleetMarkers(fleet, isManual = false) {
        const rail = document.getElementById('vehicleListRail');
        const countBadge = document.getElementById('activeVehicleCount');
        const mobileBadge = document.getElementById('mobileCountBadge');

        if (countBadge) countBadge.innerText = fleet.length + ' Active';
        if (mobileBadge) mobileBadge.innerText = fleet.length;

        if (fleet.length === 0) {
            // Remove existing markers if fleet is empty
            Object.keys(markers).forEach(k => {
                if (map && markers[k]) {
                    map.removeLayer(markers[k]);
                }
            });
            markers = {};

            if (rail) {
                rail.innerHTML = `
                    <div class="fleet-empty-state">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📡</div>
                        <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.95rem; margin-bottom: 0.25rem;">No Active Telemetry Transmissions</div>
                        <p style="font-size: 0.82rem; line-height: 1.4; margin: 0;">
                            Chauffeurs transmit live GPS telemetry strictly during active assigned trips or on-duty status via their mobile app. Standby and off-duty drivers do not transmit location data to protect employee privacy.
                        </p>
                    </div>
                `;
            }
            return;
        }

        let railHtml = '';
        const bounds = [];
        const seenKeys = {};

        fleet.forEach(item => {
            const lat = parseFloat(item.current_latitude);
            const lng = parseFloat(item.current_longitude);
            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

            bounds.push([lat, lng]);
            const signalStatus = computeSignalStatus(item.last_gps_at);
            const key = 'drv_' + item.driver_id;
            seenKeys[key] = true;

            const popupContent = `
                <div style="padding: 0.5rem; font-family: var(--vg-font-sans, sans-serif); min-width: 220px;">
                    <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #0284C7; margin-bottom: 0.25rem;">
                        ${item.dispatch_code ? item.dispatch_code : 'Chauffeur On Duty'}
                    </div>
                    <div style="font-weight: 800; font-size: 1rem; color: #0B192C;">${item.make_model || 'Vehicle'}</div>
                    <div style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #0284C7; margin-bottom: 0.5rem;">
                        ${item.vehicle_number || 'No Plate'}
                    </div>
                    <div style="font-size: 0.82rem; color: #334155; margin-bottom: 0.25rem;">
                        Chauffeur: <strong>${item.driver_name}</strong> (${item.driver_phone || '—'})
                    </div>
                    ${item.pickup_address ? `<div style="font-size: 0.78rem; color: #64748B;">Route: ${item.pickup_address} &rarr; ${item.drop_address}</div>` : ''}
                    <div style="margin-top: 0.5rem; padding-top: 0.4rem; border-top: 1px solid #E2E8F0; font-size: 0.75rem; color: #64748B;">
                        Last GPS Ping: <strong>${item.last_gps_at ? item.last_gps_at : 'Standby'}</strong>
                    </div>
                </div>
            `;

            if (markers[key]) {
                markers[key].setLatLng([lat, lng]);
                markers[key].setIcon(createMarkerIcon(signalStatus, item.vehicle_number));
                markers[key].setPopupContent(popupContent);
            } else if (map) {
                const marker = L.marker([lat, lng], {
                    icon: createMarkerIcon(signalStatus, item.vehicle_number)
                }).addTo(map).bindPopup(popupContent);
                markers[key] = marker;
            }

            // Status Badge
            let badgeHtml = '<span class="badge badge-success" style="font-size:0.7rem;">Live</span>';
            if (signalStatus === 'delayed') badgeHtml = '<span class="badge badge-warning" style="font-size:0.7rem;">Delayed</span>';
            if (signalStatus === 'stale') badgeHtml = '<span class="badge badge-neutral" style="font-size:0.7rem;">Offline</span>';

            railHtml += `
                <div class="vg-record-card" data-driver-name="${(item.driver_name || '').toLowerCase()}" data-plate="${(item.vehicle_number || '').toLowerCase()}" style="padding: 0.85rem; cursor: pointer; border-left: 4px solid ${signalStatus === 'live' ? '#10B981' : (signalStatus === 'delayed' ? '#F59E0B' : '#94A3B8')};" onclick="focusVehicle(${lat}, ${lng}, '${key}')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">
                        <div>
                            <strong style="color: var(--vg-navy-900); font-size: 0.92rem;">${item.vehicle_number || 'Vehicle'}</strong>
                            <div style="font-size: 0.75rem; color: var(--vg-text-muted);">${item.make_model || ''}</div>
                        </div>
                        ${badgeHtml}
                    </div>
                    <div style="font-size: 0.82rem; color: var(--vg-text-secondary); margin-bottom: 0.25rem;">
                        Chauffeur: <strong>${item.driver_name}</strong>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">
                        Synced: ${item.last_gps_at ? item.last_gps_at : 'Standby'}
                    </div>
                </div>
            `;
        });

        // Prune removed markers
        Object.keys(markers).forEach(k => {
            if (!seenKeys[k]) {
                if (map && markers[k]) {
                    map.removeLayer(markers[k]);
                }
                delete markers[k];
            }
        });

        if (rail) rail.innerHTML = railHtml;

        // Auto-center bounds on first load or explicit manual refresh
        if (bounds.length > 0 && map && (isManual || !hasInitiallyFitted)) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
            hasInitiallyFitted = true;
        }
    }

    window.focusVehicle = function(lat, lng, key) {
        if (map) {
            // If on mobile and in list view, switch to map view
            if (window.innerWidth <= 960) {
                window.switchMobileTrackingView('map');
            }
            map.setView([lat, lng], 15, { animate: true });
            if (markers[key]) {
                setTimeout(() => markers[key].openPopup(), 200);
            }
        }
    };

    window.applyFleetFilter = function() {
        const input = document.getElementById('fleetFilterInput');
        if (!input) return;
        const query = input.value.trim().toLowerCase();
        const cards = document.querySelectorAll('#vehicleListRail .vg-record-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-driver-name') || '';
            const plate = card.getAttribute('data-plate') || '';
            if (query === '' || name.includes(query) || plate.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    };

    // Clean up timers on page unload
    window.addEventListener('beforeunload', () => {
        stopPolling();
    });

    document.addEventListener('DOMContentLoaded', initMap);
})();
</script>
