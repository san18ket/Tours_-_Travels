<!-- Live Tracking Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--vg-navy-900); letter-spacing: -0.02em; margin: 0 0 0.25rem 0;">Live Fleet Radar &amp; Telemetry</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Real-time chauffeur geolocation, trip en-route monitoring, speed telemetry, and connectivity audits.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--vg-pearl-100); padding: 0.5rem 0.85rem; border-radius: var(--vg-radius-md); border: 1px solid var(--vg-pearl-200); font-size: 0.82rem;">
            <span id="trackerPulseIndicator" class="pulse-indicator" style="background: #10B981;"></span>
            <span id="trackerStatusLabel" style="font-weight: 600; color: var(--vg-navy-900);">Radar Active</span>
            <span style="color: var(--vg-text-muted);">•</span>
            <span style="color: var(--vg-text-muted);">Auto-sync (15s): <strong id="lastTrackerSync" style="color: var(--vg-navy-900);">—</strong></span>
        </div>
        <button type="button" class="btn btn-navy btn-sm" onclick="fetchLiveFleet(true)" title="Force Instant Sync">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span>Sync Radar</span>
        </button>
    </div>
</div>

<!-- Telemetry Legend Ribbon -->
<div style="display: flex; gap: 1.25rem; margin-bottom: 1.25rem; background: #FFFFFF; padding: 0.75rem 1.25rem; border-radius: var(--vg-radius-md); border: 1px solid var(--vg-pearl-200); flex-wrap: wrap; align-items: center; font-size: 0.82rem;">
    <div style="font-weight: 700; color: var(--vg-navy-900);">Signal Classifications:</div>
    <div style="display: flex; align-items: center; gap: 0.4rem;">
        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></span>
        <span style="color: var(--vg-text-primary); font-weight: 600;">Live</span>
        <span style="color: var(--vg-text-muted);">&lt; 2 min</span>
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem;">
        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #F59E0B;"></span>
        <span style="color: var(--vg-text-primary); font-weight: 600;">Delayed / Low Signal</span>
        <span style="color: var(--vg-text-muted);">2 – 10 min</span>
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem;">
        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #94A3B8;"></span>
        <span style="color: var(--vg-text-primary); font-weight: 600;">Offline / Standby</span>
        <span style="color: var(--vg-text-muted);">&gt; 10 min</span>
    </div>
</div>

<!-- Radar Container: Split Map & Active Fleet Rail -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; min-height: 600px; @media(max-width: 960px){grid-template-columns: 1fr;}">
    <!-- Interactive Map Canvas -->
    <div class="vg-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; min-height: 550px; position: relative;">
        <!-- Leaflet Map Container -->
        <div id="fleetMap" style="width: 100%; height: 100%; min-height: 550px; flex: 1; z-index: 1;"></div>

        <!-- Stale Warning Overlay (Hidden by default) -->
        <div id="radarWarningOverlay" style="display: none; position: absolute; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 999; background: rgba(15, 23, 42, 0.9); color: #FFF; padding: 0.5rem 1rem; border-radius: 30px; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(0,0,0,0.2); backdrop-filter: blur(8px);">
            ⚠️ Telemetry sync delayed. Displaying last verified positions.
        </div>
    </div>

    <!-- Active Fleet Side Panel -->
    <div class="vg-card" style="padding: 1.25rem; display: flex; flex-direction: column; max-height: 640px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--vg-pearl-200);">
            <span class="card-title" style="font-size: 1rem;">Tracked Vehicles</span>
            <span id="activeVehicleCount" class="badge badge-navy">0 Active</span>
        </div>

        <div id="vehicleListRail" style="overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.75rem;">
            <!-- Rendered dynamically via JavaScript -->
            <div id="emptyTrackingState" style="text-align: center; padding: 2.5rem 1rem; color: var(--vg-text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📡</div>
                <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.95rem; margin-bottom: 0.25rem;">No Active Telemetry Transmissions</div>
                <p style="font-size: 0.82rem; line-height: 1.4; margin: 0;">
                    Chauffeurs transmit live GPS telemetry strictly during active assigned trips via their mobile PWA. Standby and off-duty drivers do not transmit location data to protect employee privacy.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS via CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
(function() {
    let map = null;
    let markers = {};
    let pollInterval = null;

    function initMap() {
        // Default center on New Delhi / North India center
        map = L.map('fleetMap').setView([28.6139, 77.2090], 11);

        // High-contrast clean OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        fetchLiveFleet(true);
        pollInterval = setInterval(fetchLiveFleet, 15000);
    }

    function createMarkerIcon(statusClass, plateNumber) {
        let pinColor = '#10B981'; // green
        if (statusClass === 'delayed') pinColor = '#F59E0B'; // amber
        if (statusClass === 'stale') pinColor = '#94A3B8'; // gray

        const html = `
            <div style="position: relative; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: ${pinColor}; border: 3px solid #FFFFFF; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
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

    window.fetchLiveFleet = function(isManual = false) {
        fetch('<?= url('/api/fleet/live') ?>')
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                const lastSyncEl = document.getElementById('lastTrackerSync');
                if (lastSyncEl) {
                    const now = new Date();
                    lastSyncEl.innerText = now.toLocaleTimeString();
                }

                updateFleetMarkers(data.fleet || []);
            })
            .catch(err => {
                console.warn('Live tracking sync warning:', err);
                const warn = document.getElementById('radarWarningOverlay');
                if (warn) warn.style.display = 'block';
            });
    };

    function updateFleetMarkers(fleet) {
        const rail = document.getElementById('vehicleListRail');
        const countBadge = document.getElementById('activeVehicleCount');
        if (countBadge) countBadge.innerText = fleet.length + ' Tracked';

        if (fleet.length === 0) {
            if (rail) {
                rail.innerHTML = `
                    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--vg-text-muted);">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📡</div>
                        <div style="font-weight: 700; color: var(--vg-navy-900); font-size: 0.95rem; margin-bottom: 0.25rem;">No Active Telemetry Transmissions</div>
                        <p style="font-size: 0.82rem; line-height: 1.4; margin: 0;">
                            Chauffeurs transmit live GPS telemetry strictly during active assigned trips via their mobile PWA. Standby and off-duty drivers do not transmit location data to protect employee privacy.
                        </p>
                    </div>
                `;
            }
            return;
        }

        let railHtml = '';
        const bounds = [];

        fleet.forEach(item => {
            const lat = parseFloat(item.current_latitude);
            const lng = parseFloat(item.current_longitude);
            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

            bounds.push([lat, lng]);
            const signalStatus = computeSignalStatus(item.last_gps_at);
            const key = 'drv_' + item.driver_id;

            const popupContent = `
                <div style="padding: 0.5rem; font-family: var(--vg-font-sans); min-width: 220px;">
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
                    ${item.pickup_address ? `<div style="font-size: 0.78rem; color: #64748B;">Route: ${item.pickup_address} → ${item.drop_address}</div>` : ''}
                    <div style="margin-top: 0.5rem; padding-top: 0.4rem; border-top: 1px solid #E2E8F0; font-size: 0.75rem; color: #64748B;">
                        Last GPS Ping: <strong>${item.last_gps_at ? item.last_gps_at : 'Standby'}</strong>
                    </div>
                </div>
            `;

            if (markers[key]) {
                markers[key].setLatLng([lat, lng]);
                markers[key].setIcon(createMarkerIcon(signalStatus, item.vehicle_number));
                markers[key].setPopupContent(popupContent);
            } else {
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
                <div class="vg-record-card" style="padding: 0.85rem; cursor: pointer; border-left: 4px solid ${signalStatus === 'live' ? '#10B981' : (signalStatus === 'delayed' ? '#F59E0B' : '#94A3B8')};" onclick="focusVehicle(${lat}, ${lng}, '${key}')">
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

        if (rail) rail.innerHTML = railHtml;

        // Auto-center bounds if first load and markers exist
        if (bounds.length > 0 && isManual) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
        }
    }

    window.focusVehicle = function(lat, lng, key) {
        if (map) {
            map.setView([lat, lng], 15, { animate: true });
            if (markers[key]) {
                markers[key].openPopup();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', initMap);
})();
</script>
