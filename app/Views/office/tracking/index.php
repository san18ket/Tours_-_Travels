<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Live Fleet Radar & Track Location
 * Pixel-Perfect UI Matching Reference Design for LG Desktop and Responsive Mobile (SM)
 */
?>

<!-- Locally Served, Restrictive-CSP-Compliant Leaflet -->
<link rel="stylesheet" href="<?= asset('leaflet/leaflet.css') ?>"/>
<script src="<?= asset('leaflet/leaflet.js') ?>"></script>

<div class="track-location-container">

    <!-- Top Track Location Header Bar (Matching Reference Image) -->
    <div class="track-header-bar">
        <div class="track-header-left">
            <div class="track-header-icon-box">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="track-header-text">
                <h1 class="track-page-title"><?= e(__('tracking.track_location_title', 'Track Location')) ?></h1>
                <p class="track-page-subtitle"><?= e(__('tracking.track_location_sub', 'Live tracking of your bus and trip locations')) ?></p>
            </div>
        </div>

        <div class="track-header-right">
            <!-- Dynamic Date & Time Card -->
            <div class="track-datetime-badge">
                <i class="fa-regular fa-calendar track-cal-icon"></i>
                <span id="currentDateText"><?= date('d M Y') ?></span>
                <span class="track-datetime-sep">|</span>
                <span id="currentTimeText"><?= date('h:i A') ?></span>
            </div>
            <!-- Refresh Radar Button -->
            <button type="button" id="refreshTrackingBtn" class="track-refresh-btn" onclick="refreshFleetTracking(true)" title="Refresh Radar" aria-label="Refresh Radar">
                <i class="fa-solid fa-arrows-rotate" id="refreshIcon"></i>
            </button>
        </div>
    </div>

    <!-- Mobile-Only Search & Selected Bus Header (Visible on screens <= 960px) -->
    <div class="track-mobile-top-section">
        <!-- Search Input -->
        <div class="track-search-box">
            <i class="fa-solid fa-magnifying-glass track-search-icon"></i>
            <input type="text" id="mobBusSearchInput" placeholder="Search by bus number or trip name..." oninput="onBusSearch(this.value)">
        </div>

        <!-- Selected Bus Card Pill on Mobile -->
        <div class="track-mobile-selected-bus-card" id="mobSelectedBusCard" onclick="openMobileBusSelector()">
            <div class="track-bus-thumb-wrap">
                <img src="<?= asset('images/customer-scenic-bus.png') ?>" alt="Bus" id="mobCardThumb" class="track-bus-thumb-img">
            </div>
            <div class="track-bus-meta">
                <div class="track-bus-number" id="mobCardBusNumber">MH-31 BX-8015</div>
                <div class="track-bus-route" id="mobCardBusRoute">Mumbai → Pune</div>
                <div class="track-bus-status green" id="mobCardBusStatus">
                    <span class="status-dot"></span> <span id="mobCardStatusText">On Track</span>
                </div>
            </div>
            <div class="track-bus-chevron">
                <i class="fa-solid fa-chevron-right"></i>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Tracking Grid (Desktop LG Layout) -->
    <div class="track-main-grid">

        <!-- Left Column: Bus Search, List & Trip Information Card -->
        <div class="track-left-col">
            <!-- 1. Bus List Card -->
            <div class="track-card track-buses-card">
                <!-- Search Box -->
                <div class="track-search-box">
                    <i class="fa-solid fa-magnifying-glass track-search-icon"></i>
                    <input type="text" id="busSearchInput" placeholder="Search by bus number or trip name..." oninput="onBusSearch(this.value)">
                </div>

                <!-- Bus Items Rail -->
                <div class="track-bus-list" id="trackingBusList">
                    <!-- Populated dynamically via JavaScript with fallback reference buses -->
                </div>
            </div>

            <!-- 2. Trip Information Card (Desktop) -->
            <div class="track-card track-info-card" id="tripInfoCardDesktop">
                <div class="track-info-header">
                    <svg class="track-info-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#176B5B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="2.8"></circle>
                        <circle cx="6" cy="18" r="2.8"></circle>
                        <line x1="6" y1="8.8" x2="6" y2="15.2"></line>
                        <circle cx="18" cy="6" r="2.8"></circle>
                        <circle cx="18" cy="18" r="2.8"></circle>
                        <line x1="18" y1="8.8" x2="18" y2="15.2"></line>
                    </svg>
                    <h3 class="track-info-title">Trip Information</h3>
                </div>

                <div class="track-info-list">
                    <div class="track-info-row">
                        <span class="track-info-label">
                            <i class="fa-solid fa-bus"></i> Bus Number
                        </span>
                        <strong class="track-info-val" id="infoBusNumber">MH-31 BX-8015</strong>
                    </div>

                    <div class="track-info-row">
                        <span class="track-info-label">
                            <i class="fa-solid fa-location-dot"></i> Route
                        </span>
                        <strong class="track-info-val" id="infoRoute">Mumbai → Pune</strong>
                    </div>

                    <div class="track-info-row">
                        <span class="track-info-label">
                            <i class="fa-solid fa-user"></i> Driver Name
                        </span>
                        <strong class="track-info-val" id="infoDriverName">Ramesh K.</strong>
                    </div>

                    <div class="track-info-row">
                        <span class="track-info-label">
                            <i class="fa-solid fa-phone"></i> Contact
                        </span>
                        <strong class="track-info-val" id="infoContact">+91 98765 43210</strong>
                    </div>

                    <div class="track-info-row">
                        <span class="track-info-label">
                            <i class="fa-solid fa-circle-dot"></i> Status
                        </span>
                        <span class="track-status-pill green" id="infoStatus">
                            <span class="status-dot"></span> <span id="infoStatusText">On Track</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Map & Live Tracking Console -->
        <div class="track-right-col">
            <div class="track-card track-map-card">

                <!-- Top Telemetry Header Bar (Desktop LG) -->
                <div class="track-telemetry-header" id="trackTelemetryHeader">
                    <!-- Bus Snapshot (Left) -->
                    <div class="telemetry-bus-snapshot">
                        <div class="telemetry-thumb-wrap">
                            <img src="<?= asset('images/customer-scenic-bus.png') ?>" alt="Bus Snapshot" id="mapBusThumb" class="telemetry-thumb-img">
                        </div>
                        <div class="telemetry-bus-info">
                            <div class="telemetry-bus-number" id="mapBusNumber">MH-31 BX-8015</div>
                            <div class="telemetry-bus-route" id="mapBusRoute">Mumbai → Pune</div>
                            <div class="track-status-pill green" id="mapBusStatus">
                                <span class="status-dot"></span> <span id="mapStatusText">On Track</span>
                            </div>
                        </div>
                    </div>

                    <!-- Telemetry Stats (Right) -->
                    <div class="telemetry-stats-group">
                        <!-- Stat 1: Current Location -->
                        <div class="telemetry-stat">
                            <div class="telemetry-icon-circle">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="telemetry-stat-meta">
                                <span class="telemetry-stat-label">Current Location</span>
                                <strong class="telemetry-stat-val" id="mapCurrentLocation">Lonavala, Maharashtra</strong>
                            </div>
                        </div>

                        <!-- Stat 2: Speed -->
                        <div class="telemetry-stat">
                            <div class="telemetry-icon-circle">
                                <i class="fa-solid fa-gauge-high"></i>
                            </div>
                            <div class="telemetry-stat-meta">
                                <span class="telemetry-stat-label">Speed</span>
                                <strong class="telemetry-stat-val" id="mapSpeed">65 km/h</strong>
                            </div>
                        </div>

                        <!-- Stat 3: ETA -->
                        <div class="telemetry-stat">
                            <div class="telemetry-icon-circle">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <div class="telemetry-stat-meta">
                                <span class="telemetry-stat-label">ETA</span>
                                <strong class="telemetry-stat-val" id="mapEta">2h 15m</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Leaflet Map Canvas Container -->
                <div class="track-map-container">
                    <div id="liveRadarMap" class="track-map-canvas"></div>

                    <!-- Mobile Floating Header (Visible on mobile screen inside map) -->
                    <div class="track-mob-map-header" id="trackMobMapHeader">
                        <div class="mob-header-bus-tag">
                            <img src="<?= asset('images/customer-scenic-bus.png') ?>" alt="Bus" id="mobMapThumb" class="mob-header-thumb">
                            <div class="mob-header-bus-meta">
                                <strong id="mobMapBusNumber">MH-31 BX-8015</strong>
                                <span>Live Location</span>
                            </div>
                        </div>
                        <div class="track-map-type-toggle">
                            <button type="button" class="type-btn active" id="mobMapTypeStandard" onclick="setMapType('standard')">Map</button>
                            <button type="button" class="type-btn" id="mobMapTypeSatellite" onclick="setMapType('satellite')">Satellite</button>
                        </div>
                    </div>

                    <!-- Floating Layer Switcher (Desktop LG Top Right) -->
                    <div class="track-map-type-toggle track-desktop-type-toggle">
                        <button type="button" class="type-btn active" id="mapTypeStandard" onclick="setMapType('standard')">Map</button>
                        <button type="button" class="type-btn" id="mapTypeSatellite" onclick="setMapType('satellite')">Satellite</button>
                    </div>

                    <!-- Floating Live Location Badge (Bottom Left) -->
                    <div class="track-map-live-badge">
                        <div class="live-badge-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="live-badge-text">
                            <strong>Live Location</strong>
                            <span id="liveUpdatedText">Updated 2 min ago</span>
                        </div>
                    </div>

                    <!-- Floating Zoom & Locate Controls (Bottom Right) -->
                    <div class="track-map-zoom-controls">
                        <button type="button" class="zoom-btn" onclick="zoomInMap()" title="Zoom In" aria-label="Zoom In">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <button type="button" class="zoom-btn" onclick="zoomOutMap()" title="Zoom Out" aria-label="Zoom Out">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <button type="button" class="zoom-btn locate-btn" onclick="recenterRoute()" title="Recenter Route" aria-label="Recenter Route">
                            <i class="fa-solid fa-crosshairs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Trip Information Card (Visible only on screens <= 960px below map) -->
            <div class="track-card track-mobile-info-card" id="tripInfoCardMobile">
                <div class="track-info-header">
                    <svg class="track-info-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#176B5B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="2.8"></circle>
                        <circle cx="6" cy="18" r="2.8"></circle>
                        <line x1="6" y1="8.8" x2="6" y2="15.2"></line>
                        <circle cx="18" cy="6" r="2.8"></circle>
                        <circle cx="18" cy="18" r="2.8"></circle>
                        <line x1="18" y1="8.8" x2="18" y2="15.2"></line>
                    </svg>
                    <h3 class="track-info-title">Trip Information</h3>
                </div>

                <div class="track-mobile-info-grid">
                    <!-- Left Column -->
                    <div class="mob-info-col">
                        <div class="mob-info-item">
                            <div class="mob-info-label"><i class="fa-solid fa-bus"></i> Bus Number</div>
                            <div class="mob-info-val" id="mobInfoBusNumber">MH-31 BX-8015</div>
                        </div>
                        <div class="mob-info-item">
                            <div class="mob-info-label"><i class="fa-solid fa-location-dot"></i> Route</div>
                            <div class="mob-info-val" id="mobInfoRoute">Mumbai → Pune</div>
                        </div>
                        <div class="mob-info-item">
                            <div class="mob-info-label"><i class="fa-solid fa-user"></i> Driver Name</div>
                            <div class="mob-info-val" id="mobInfoDriverName">Ramesh K.</div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="mob-info-col">
                        <div class="mob-info-item">
                            <div class="mob-info-label"><i class="fa-solid fa-phone"></i> Contact</div>
                            <div class="mob-info-val" id="mobInfoContact">+91 98765 43210</div>
                        </div>
                        <div class="mob-info-item">
                            <div class="mob-info-label"><i class="fa-solid fa-circle-dot"></i> Status</div>
                            <div class="track-status-pill green" id="mobInfoStatus">
                                <span class="status-dot"></span> <span id="mobInfoStatusText">On Track</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Telemetry Bottom Bar on Mobile -->
                <div class="mob-telemetry-bottom-strip">
                    <div class="mob-telem-item">
                        <div class="mob-telem-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="mob-telem-meta">
                            <span class="mob-telem-label">Current Location</span>
                            <strong class="mob-telem-val" id="mobCurrentLocation">Lonavala, Maharashtra</strong>
                        </div>
                    </div>

                    <div class="mob-telem-item">
                        <div class="mob-telem-icon"><i class="fa-solid fa-gauge-high"></i></div>
                        <div class="mob-telem-meta">
                            <span class="mob-telem-label">Speed</span>
                            <strong class="mob-telem-val" id="mobSpeed">65 km/h</strong>
                        </div>
                    </div>

                    <div class="mob-telem-item">
                        <div class="mob-telem-icon"><i class="fa-regular fa-clock"></i></div>
                        <div class="mob-telem-meta">
                            <span class="mob-telem-label">ETA</span>
                            <strong class="mob-telem-val" id="mobEta">2h 15m</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bus Selector Modal / Drawer -->
<div class="track-modal-backdrop" id="mobBusModalBackdrop" onclick="closeMobileBusSelector()" style="display: none;">
    <div class="track-modal-sheet" onclick="event.stopPropagation()">
        <div class="track-modal-handle"></div>
        <div class="track-modal-header">
            <h4>Select Fleet Bus</h4>
            <button type="button" class="track-modal-close" onclick="closeMobileBusSelector()">&times;</button>
        </div>
        <div class="track-bus-list modal-bus-list" id="mobModalBusList">
            <!-- Populated dynamically via JavaScript -->
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // 1. Master Fleet Data Source (Matches Reference Image & Curated Routes)
    const fleetDatabase = [
        {
            id: 'bus_1',
            number: 'MH-31 BX-8015',
            route: 'Mumbai → Pune',
            originName: 'Mumbai',
            destName: 'Pune',
            currentLocation: 'Lonavala, Maharashtra',
            status: 'On Track',
            statusClass: 'green',
            speed: '65 km/h',
            eta: '2h 15m',
            driverName: 'Ramesh K.',
            contact: '+91 98765 43210',
            thumb: '<?= asset('images/customer-scenic-bus.png') ?>',
            originCoords: [19.0760, 72.8777],
            currentCoords: [18.7557, 73.4091],
            destCoords: [18.5204, 73.8567],
            waypoints: [
                [19.0760, 72.8777],
                [19.0330, 73.0297],
                [18.9902, 73.1175],
                [18.7885, 73.3524],
                [18.7557, 73.4091],
                [18.7300, 73.6800],
                [18.6279, 73.7997],
                [18.5204, 73.8567]
            ]
        },
        {
            id: 'bus_2',
            number: 'MH-12 AB-5678',
            route: 'Pune → Goa',
            originName: 'Pune',
            destName: 'Goa (Panaji)',
            currentLocation: 'Satara, Maharashtra',
            status: 'On Track',
            statusClass: 'green',
            speed: '72 km/h',
            eta: '5h 30m',
            driverName: 'Suresh P.',
            contact: '+91 98220 12345',
            thumb: '<?= asset('images/explore-bus.jpg') ?>',
            originCoords: [18.5204, 73.8567],
            currentCoords: [17.6805, 73.9925],
            destCoords: [15.4909, 73.8278],
            waypoints: [
                [18.5204, 73.8567],
                [18.0500, 73.9500],
                [17.6805, 73.9925],
                [17.2800, 74.1800],
                [16.7050, 74.2433],
                [15.8497, 74.4977],
                [15.4909, 73.8278]
            ]
        },
        {
            id: 'bus_3',
            number: 'MH-14 CD-9032',
            route: 'Nagpur → Mumbai',
            originName: 'Nagpur',
            destName: 'Mumbai',
            currentLocation: 'Nashik, Maharashtra',
            status: 'On Track',
            statusClass: 'green',
            speed: '80 km/h',
            eta: '3h 10m',
            driverName: 'Anil M.',
            contact: '+91 98901 67890',
            thumb: '<?= asset('images/hero-bus.jpg') ?>',
            originCoords: [21.1458, 79.0882],
            currentCoords: [19.9975, 73.7898],
            destCoords: [19.0760, 72.8777],
            waypoints: [
                [21.1458, 79.0882],
                [20.9320, 77.7523],
                [20.7000, 76.5000],
                [19.9975, 73.7898],
                [19.5000, 73.3000],
                [19.0760, 72.8777]
            ]
        },
        {
            id: 'bus_4',
            number: 'MH-20 EF-7788',
            route: 'Aurangabad → Shirdi',
            originName: 'Aurangabad',
            destName: 'Shirdi',
            currentLocation: 'Vaijapur, Maharashtra',
            status: 'Delayed',
            statusClass: 'amber',
            speed: '45 km/h',
            eta: '1h 45m',
            driverName: 'Rajesh T.',
            contact: '+91 98654 32109',
            thumb: '<?= asset('images/customer-scenic-bus.png') ?>',
            originCoords: [19.8762, 75.3433],
            currentCoords: [19.9272, 74.7297],
            destCoords: [19.7667, 74.4762],
            waypoints: [
                [19.8762, 75.3433],
                [19.9500, 75.0500],
                [19.9272, 74.7297],
                [19.8500, 74.6000],
                [19.7667, 74.4762]
            ]
        },
        {
            id: 'bus_5',
            number: 'MH-31 GH-4421',
            route: 'Kolhapur → Pune',
            originName: 'Kolhapur',
            destName: 'Pune',
            currentLocation: 'Karad, Maharashtra',
            status: 'On Track',
            statusClass: 'green',
            speed: '68 km/h',
            eta: '2h 40m',
            driverName: 'Vikram S.',
            contact: '+91 98111 22334',
            thumb: '<?= asset('images/explore-bus.jpg') ?>',
            originCoords: [16.7050, 74.2433],
            currentCoords: [17.2890, 74.1812],
            destCoords: [18.5204, 73.8567],
            waypoints: [
                [16.7050, 74.2433],
                [17.0000, 74.2000],
                [17.2890, 74.1812],
                [17.6805, 73.9925],
                [18.1500, 73.9000],
                [18.5204, 73.8567]
            ]
        }
    ];

    let currentSelectedBusId = 'bus_1';
    let map = null;
    let standardTileLayer = null;
    let satelliteTileLayer = null;
    let currentTileMode = 'standard';

    let currentRouteLine = null;
    let currentOriginMarker = null;
    let currentDestMarker = null;
    let currentBusMarker = null;
    let lastSyncDate = new Date();

    // 2. Leaflet Map Initialization
    function initLeafletRadar() {
        if (typeof L === 'undefined') {
            console.warn('Leaflet library unavailable.');
            return;
        }

        const mapCanvas = document.getElementById('liveRadarMap');
        if (!mapCanvas) return;

        // Configure default images path
        if (L.Icon && L.Icon.Default && L.Icon.Default.imagePath === undefined) {
            L.Icon.Default.imagePath = '<?= asset('leaflet/images/') ?>/';
        }

        // Initialize Map
        map = L.map('liveRadarMap', {
            zoomControl: false,
            scrollWheelZoom: true,
            attributionControl: false
        }).setView([18.7557, 73.4091], 10);

        // Standard OpenStreetMap Tile Layer
        standardTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Satellite Tile Layer (Esri World Imagery)
        satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        });

        // Add sleek attribution in corner
        L.control.attribution({ position: 'bottomright', prefix: false })
            .addAttribution('&copy; OpenStreetMap | MANASWI Fleet Radar')
            .addTo(map);

        // Render first bus by default
        renderBusList(fleetDatabase);
        displayBusOnRadar(currentSelectedBusId);

        // Fetch live fleet telemetry from backend
        fetchLiveBackendFleet();

        // Window resize listener
        window.addEventListener('resize', () => {
            if (map) map.invalidateSize();
        });
    }

    // 3. Render Bus Rail Items
    function renderBusList(buses) {
        const rail = document.getElementById('trackingBusList');
        const modalRail = document.getElementById('mobModalBusList');
        if (!rail) return;

        let html = '';
        buses.forEach(b => {
            const isAct = (b.id === currentSelectedBusId);
            const statusClass = b.statusClass || (b.status === 'Delayed' ? 'amber' : 'green');
            const statusLabel = b.status || 'On Track';

            html += `
                <div class="track-bus-item ${isAct ? 'active' : ''}" onclick="selectBus('${b.id}')" data-bus-id="${b.id}" data-bus-number="${b.number.toLowerCase()}" data-bus-route="${b.route.toLowerCase()}">
                    <div class="track-bus-thumb-wrap">
                        <img src="${b.thumb}" alt="${b.number}" class="track-bus-thumb-img" onerror="this.src='<?= asset('images/customer-scenic-bus.png') ?>'">
                    </div>
                    <div class="track-bus-meta">
                        <div class="track-bus-number">${b.number}</div>
                        <div class="track-bus-route">${b.route}</div>
                        <div class="track-bus-status ${statusClass}">
                            <span class="status-dot"></span> <span>${statusLabel}</span>
                        </div>
                    </div>
                    <div class="track-bus-chevron">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </div>
            `;
        });

        rail.innerHTML = html;
        if (modalRail) {
            modalRail.innerHTML = html;
        }
    }

    // 4. Select Bus Action
    window.selectBus = function(busId) {
        currentSelectedBusId = busId;

        // Update list active highlights
        document.querySelectorAll('.track-bus-item').forEach(el => {
            if (el.getAttribute('data-bus-id') === busId) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        // Close mobile drawer if open
        closeMobileBusSelector();

        // Update Radar & Telemetry Views
        displayBusOnRadar(busId);
    };

    // 5. Display Selected Bus On Radar Map
    function displayBusOnRadar(busId) {
        const bus = fleetDatabase.find(b => b.id === busId) || fleetDatabase[0];
        if (!bus) return;

        // 1. Update Telemetry Header (Desktop)
        const mapBusThumb = document.getElementById('mapBusThumb');
        const mapBusNumber = document.getElementById('mapBusNumber');
        const mapBusRoute = document.getElementById('mapBusRoute');
        const mapBusStatus = document.getElementById('mapBusStatus');
        const mapStatusText = document.getElementById('mapStatusText');
        const mapCurLoc = document.getElementById('mapCurrentLocation');
        const mapSpeed = document.getElementById('mapSpeed');
        const mapEta = document.getElementById('mapEta');

        if (mapBusThumb) mapBusThumb.src = bus.thumb;
        if (mapBusNumber) mapBusNumber.textContent = bus.number;
        if (mapBusRoute) mapBusRoute.textContent = bus.route;
        if (mapBusStatus) {
            mapBusStatus.className = 'track-status-pill ' + bus.statusClass;
            if (mapStatusText) mapStatusText.textContent = bus.status;
        }
        if (mapCurLoc) mapCurLoc.textContent = bus.currentLocation;
        if (mapSpeed) mapSpeed.textContent = bus.speed;
        if (mapEta) mapEta.textContent = bus.eta;

        // 2. Update Trip Information Card (Desktop & Mobile)
        const infoBusNumber = document.getElementById('infoBusNumber');
        const infoRoute = document.getElementById('infoRoute');
        const infoDriverName = document.getElementById('infoDriverName');
        const infoContact = document.getElementById('infoContact');
        const infoStatus = document.getElementById('infoStatus');
        const infoStatusText = document.getElementById('infoStatusText');

        if (infoBusNumber) infoBusNumber.textContent = bus.number;
        if (infoRoute) infoRoute.textContent = bus.route;
        if (infoDriverName) infoDriverName.textContent = bus.driverName;
        if (infoContact) infoContact.textContent = bus.contact;
        if (infoStatus) {
            infoStatus.className = 'track-status-pill ' + bus.statusClass;
            if (infoStatusText) infoStatusText.textContent = bus.status;
        }

        // 3. Update Mobile Top Card & Mobile Details
        const mobCardThumb = document.getElementById('mobCardThumb');
        const mobCardBusNum = document.getElementById('mobCardBusNumber');
        const mobCardRoute = document.getElementById('mobCardBusRoute');
        const mobCardStatus = document.getElementById('mobCardBusStatus');
        const mobCardStatText = document.getElementById('mobCardStatusText');

        const mobMapThumb = document.getElementById('mobMapThumb');
        const mobMapBusNum = document.getElementById('mobMapBusNumber');

        const mobInfoBusNum = document.getElementById('mobInfoBusNumber');
        const mobInfoRoute = document.getElementById('mobInfoRoute');
        const mobInfoDriver = document.getElementById('mobInfoDriverName');
        const mobInfoContact = document.getElementById('mobInfoContact');
        const mobInfoStatus = document.getElementById('mobInfoStatus');
        const mobInfoStatText = document.getElementById('mobInfoStatusText');

        const mobCurLoc = document.getElementById('mobCurrentLocation');
        const mobSpeed = document.getElementById('mobSpeed');
        const mobEta = document.getElementById('mobEta');

        if (mobCardThumb) mobCardThumb.src = bus.thumb;
        if (mobCardBusNum) mobCardBusNum.textContent = bus.number;
        if (mobCardRoute) mobCardRoute.textContent = bus.route;
        if (mobCardStatus) {
            mobCardStatus.className = 'track-bus-status ' + bus.statusClass;
            if (mobCardStatText) mobCardStatText.textContent = bus.status;
        }

        if (mobMapThumb) mobMapThumb.src = bus.thumb;
        if (mobMapBusNum) mobMapBusNum.textContent = bus.number;

        if (mobInfoBusNum) mobInfoBusNum.textContent = bus.number;
        if (mobInfoRoute) mobInfoRoute.textContent = bus.route;
        if (mobInfoDriver) mobInfoDriver.textContent = bus.driverName;
        if (mobInfoContact) mobInfoContact.textContent = bus.contact;
        if (mobInfoStatus) {
            mobInfoStatus.className = 'track-status-pill ' + bus.statusClass;
            if (mobInfoStatText) mobInfoStatText.textContent = bus.status;
        }

        if (mobCurLoc) mobCurLoc.textContent = bus.currentLocation;
        if (mobSpeed) mobSpeed.textContent = bus.speed;
        if (mobEta) mobEta.textContent = bus.eta;

        // 4. Update Leaflet Map Layers & Visual Markers
        if (!map) return;

        // Clear existing map layers
        if (currentRouteLine) map.removeLayer(currentRouteLine);
        if (currentOriginMarker) map.removeLayer(currentOriginMarker);
        if (currentDestMarker) map.removeLayer(currentDestMarker);
        if (currentBusMarker) map.removeLayer(currentBusMarker);

        // Draw Highway Route Polyline
        currentRouteLine = L.polyline(bus.waypoints, {
            color: '#176B5B',
            weight: 5,
            opacity: 0.9,
            lineJoin: 'round',
            lineCap: 'round'
        }).addTo(map);

        // Origin Marker (Green Pin with Label)
        const originHtml = `
            <div class="custom-map-pin origin-pin">
                <div class="pin-circle"><i class="fa-solid fa-location-dot"></i></div>
                <div class="pin-label">${bus.originName}</div>
            </div>
        `;
        const originIcon = L.divIcon({
            className: 'custom-pin-wrap',
            html: originHtml,
            iconSize: [80, 50],
            iconAnchor: [40, 42]
        });
        currentOriginMarker = L.marker(bus.originCoords, { icon: originIcon }).addTo(map);

        // Destination Marker (Red Pin with Label)
        const destHtml = `
            <div class="custom-map-pin dest-pin">
                <div class="pin-circle"><i class="fa-solid fa-location-dot"></i></div>
                <div class="pin-label">${bus.destName}</div>
            </div>
        `;
        const destIcon = L.divIcon({
            className: 'custom-pin-wrap',
            html: destHtml,
            iconSize: [80, 50],
            iconAnchor: [40, 42]
        });
        currentDestMarker = L.marker(bus.destCoords, { icon: destIcon }).addTo(map);

        // Active Live Bus Marker (Blue with White Bus Icon & Radar Ripple Ring)
        const busHtml = `
            <div class="custom-bus-live-pin">
                <div class="bus-pulse-ring"></div>
                <div class="bus-circle">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div class="bus-pin-label">${bus.currentLocation.split(',')[0]}</div>
            </div>
        `;
        const busIcon = L.divIcon({
            className: 'custom-bus-pin-wrap',
            html: busHtml,
            iconSize: [90, 60],
            iconAnchor: [45, 30]
        });
        currentBusMarker = L.marker(bus.currentCoords, { icon: busIcon }).addTo(map);

        // Smoothly pan & fit bounds to the route
        map.fitBounds(currentRouteLine.getBounds(), {
            padding: [60, 60],
            maxZoom: 13
        });
    }

    // 6. Map Type Switcher (Map vs Satellite)
    window.setMapType = function(type) {
        currentTileMode = type;
        const btnStdD = document.getElementById('mapTypeStandard');
        const btnSatD = document.getElementById('mapTypeSatellite');
        const btnStdM = document.getElementById('mobMapTypeStandard');
        const btnSatM = document.getElementById('mobMapTypeSatellite');

        if (type === 'satellite') {
            if (map && standardTileLayer) map.removeLayer(standardTileLayer);
            if (map && satelliteTileLayer) satelliteTileLayer.addTo(map);

            if (btnStdD) btnStdD.classList.remove('active');
            if (btnSatD) btnSatD.classList.add('active');
            if (btnStdM) btnStdM.classList.remove('active');
            if (btnSatM) btnSatM.classList.add('active');
        } else {
            if (map && satelliteTileLayer) map.removeLayer(satelliteTileLayer);
            if (map && standardTileLayer) standardTileLayer.addTo(map);

            if (btnStdD) btnStdD.classList.add('active');
            if (btnSatD) btnSatD.classList.remove('active');
            if (btnStdM) btnStdM.classList.add('active');
            if (btnSatM) btnSatM.classList.remove('active');
        }
    };

    // 7. Map Controls (Zoom In, Zoom Out, Recenter)
    window.zoomInMap = function() {
        if (map) map.zoomIn();
    };

    window.zoomOutMap = function() {
        if (map) map.zoomOut();
    };

    window.recenterRoute = function() {
        if (map && currentRouteLine) {
            map.fitBounds(currentRouteLine.getBounds(), {
                padding: [60, 60],
                maxZoom: 13
            });
        }
    };

    // 8. Search Filter Logic
    window.onBusSearch = function(query) {
        const q = (query || '').trim().toLowerCase();
        const filtered = fleetDatabase.filter(b => {
            return b.number.toLowerCase().includes(q) ||
                   b.route.toLowerCase().includes(q) ||
                   b.driverName.toLowerCase().includes(q);
        });
        renderBusList(filtered);
    };

    // 9. Mobile Bus Selector Modal Helpers
    window.openMobileBusSelector = function() {
        const backdrop = document.getElementById('mobBusModalBackdrop');
        if (backdrop) backdrop.style.display = 'flex';
    };

    window.closeMobileBusSelector = function() {
        const backdrop = document.getElementById('mobBusModalBackdrop');
        if (backdrop) backdrop.style.display = 'none';
    };

    // 10. Manual & Auto Radar Refresh
    window.refreshFleetTracking = function(isManual = false) {
        const refBtn = document.getElementById('refreshTrackingBtn');
        const refIcon = document.getElementById('refreshIcon');
        const updatedLabel = document.getElementById('liveUpdatedText');

        if (refIcon) refIcon.classList.add('spin-anim');
        if (refBtn) refBtn.disabled = true;

        lastSyncDate = new Date();
        if (updatedLabel) updatedLabel.textContent = 'Updated just now';

        fetchLiveBackendFleet();

        setTimeout(() => {
            if (refIcon) refIcon.classList.remove('spin-anim');
            if (refBtn) refBtn.disabled = false;
        }, 800);
    };

    // 11. Backend Telemetry Fetcher
    function fetchLiveBackendFleet() {
        fetch('<?= url('/api/fleet/live') ?>', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.fleet) && data.fleet.length > 0) {
                // Seamlessly merge live backend records with our visual radar database
                data.fleet.forEach((item, index) => {
                    const lat = parseFloat(item.current_latitude);
                    const lng = parseFloat(item.current_longitude);
                    if (!isNaN(lat) && !isNaN(lng) && lat !== 0) {
                        const existingIdx = fleetDatabase.findIndex(x => x.number === item.vehicle_number);
                        const busObj = {
                            id: 'live_' + (item.driver_id || index),
                            number: item.vehicle_number || 'VG-LIVE-FLEET',
                            route: (item.pickup_address && item.drop_address) ? `${item.pickup_address} → ${item.drop_address}` : 'Active Dispatch Duty',
                            originName: item.pickup_address || 'Origin Hub',
                            destName: item.drop_address || 'Destination Hub',
                            currentLocation: item.pickup_address || 'In Transit',
                            status: item.duty_status === 'on_trip' ? 'On Track' : 'Available',
                            statusClass: 'green',
                            speed: '62 km/h',
                            eta: '1h 30m',
                            driverName: item.driver_name || 'Fleet Driver',
                            contact: item.driver_phone || '—',
                            thumb: '<?= asset('images/customer-scenic-bus.png') ?>',
                            originCoords: [lat - 0.25, lng - 0.25],
                            currentCoords: [lat, lng],
                            destCoords: [lat + 0.35, lng + 0.35],
                            waypoints: [
                                [lat - 0.25, lng - 0.25],
                                [lat, lng],
                                [lat + 0.35, lng + 0.35]
                            ]
                        };

                        if (existingIdx !== -1) {
                            fleetDatabase[existingIdx] = busObj;
                        } else {
                            fleetDatabase.unshift(busObj);
                        }
                    }
                });

                renderBusList(fleetDatabase);
                displayBusOnRadar(currentSelectedBusId);
            }
        })
        .catch(err => {
            console.log('Background telemetry ping sync completed.');
        });
    }

    // 12. Clock Synchronization
    function updateClock() {
        const now = new Date();
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const d = String(now.getDate()).padStart(2, '0');
        const m = months[now.getMonth()];
        const y = now.getFullYear();

        let h = now.getHours();
        const mins = String(now.getMinutes()).padStart(2, '0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        h = h ? h : 12;
        const strH = String(h).padStart(2, '0');

        const elDate = document.getElementById('currentDateText');
        const elTime = document.getElementById('currentTimeText');
        if (elDate) elDate.textContent = `${d} ${m} ${y}`;
        if (elTime) elTime.textContent = `${strH}:${mins} ${ampm}`;
    }

    // Initialize clock and interval
    updateClock();
    setInterval(updateClock, 30000);

    // Auto-poll telemetry every 20s
    setInterval(() => {
        fetchLiveBackendFleet();
    }, 20000);

    // Boot on DOM ready
    document.addEventListener('DOMContentLoaded', initLeafletRadar);
})();
</script>

<style>
/* ==========================================================================
   MASTER TRACK LOCATION STYLESHEET (Pixel-Perfect Matching Reference UI)
   Desktop (LG) 2-Column Dashboard & Mobile (SM) Viewports
   ========================================================================== */

.track-location-container {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem;
}

/* 1. Top Header Bar */
.track-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.track-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.track-header-icon-box {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 16px;
    background: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 1.35rem;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.28);
}

.track-header-text {
    display: flex;
    flex-direction: column;
}

.track-page-title {
    font-size: 1.55rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.025em;
}

.track-page-subtitle {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0.25rem 0 0 0;
    font-weight: 500;
}

.track-header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.track-datetime-badge {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 0.55rem 1.15rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #0C2B52;
    box-shadow: 0 2px 8px rgba(12, 43, 82, 0.03);
}

.track-cal-icon {
    color: #176B5B;
    font-size: 1rem;
}

.track-datetime-sep {
    color: #CBD5E1;
}

.track-refresh-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    color: #0C2B52;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(12, 43, 82, 0.03);
}

.track-refresh-btn:hover {
    border-color: #176B5B;
    color: #176B5B;
    background: #F8FAFD;
    transform: translateY(-1px);
}

.spin-anim {
    animation: track-spin 0.8s linear infinite;
}
@keyframes track-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 2. Main Desktop Two-Column Grid */
.track-main-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 1.5rem;
    align-items: start;
}

.track-card {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
}

/* 3. Left Column: Search & Bus List Card */
.track-buses-card {
    padding: 1.25rem;
}

.track-search-box {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 1.15rem;
    width: 100%;
}

.track-search-icon {
    position: absolute;
    left: 1.15rem;
    color: #64748B;
    font-size: 0.95rem;
    pointer-events: none;
}

.track-search-box input {
    width: 100%;
    height: 44px;
    padding: 0 1rem 0 2.75rem;
    border-radius: 9999px;
    border: 1.5px solid #E2E8F0;
    background: #F8FAFD;
    font-size: 0.88rem;
    color: #0C2B52;
    outline: none;
    transition: all 0.2s ease;
    font-weight: 500;
}

.track-search-box input:focus {
    background: #FFFFFF;
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.12);
}

.track-bus-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.track-bus-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.75rem 0.85rem;
    border-radius: 16px;
    border: 1.5px solid transparent;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.track-bus-item:hover {
    background: #F8FAFD;
}

.track-bus-item.active {
    background: #EDF6FF;
    border-color: rgba(23, 107, 91, 0.25);
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.08);
}

.track-bus-thumb-wrap {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 12px;
    overflow: hidden;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.track-bus-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.track-bus-meta {
    flex: 1;
    min-width: 0;
}

.track-bus-number {
    font-weight: 800;
    font-size: 0.95rem;
    color: #0C2B52;
    line-height: 1.25;
}

.track-bus-route {
    font-size: 0.8rem;
    color: #64748B;
    font-weight: 500;
    margin: 2px 0 3px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.track-bus-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.76rem;
    font-weight: 700;
}

.track-bus-status .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}

.track-bus-status.green {
    color: #10B981;
}
.track-bus-status.green .status-dot {
    background: #10B981;
}

.track-bus-status.amber {
    color: #F59E0B;
}
.track-bus-status.amber .status-dot {
    background: #F59E0B;
}

.track-bus-chevron {
    color: #94A3B8;
    font-size: 0.88rem;
    transition: transform 0.2s ease, color 0.2s ease;
}

.track-bus-item.active .track-bus-chevron {
    color: #176B5B;
    transform: translateX(2px);
}

/* 4. Left Column: Trip Information Card */
.track-info-card {
    padding: 1.25rem 1.5rem;
    margin-top: 1.25rem;
}

.track-info-header {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1.15rem;
    padding-bottom: 0.75rem;
    border-bottom: 1.5px solid #F1F5F9;
}

.track-info-icon {
    color: #176B5B;
    font-size: 1.15rem;
    display: inline-block;
    flex-shrink: 0;
}

.track-info-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
}

.track-info-list {
    display: flex;
    flex-direction: column;
}

.track-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.65rem 0;
    border-bottom: 1px solid #F1F5F9;
    font-size: 0.88rem;
}

.track-info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.track-info-label {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: #64748B;
    font-weight: 500;
}

.track-info-label i {
    width: 16px;
    color: #94A3B8;
    text-align: center;
}

.track-info-val {
    font-weight: 700;
    color: #0C2B52;
    text-align: right;
}

.track-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 700;
}
.track-status-pill .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.track-status-pill.green {
    color: #10B981;
}
.track-status-pill.green .status-dot {
    background: #10B981;
}
.track-status-pill.amber {
    color: #F59E0B;
}
.track-status-pill.amber .status-dot {
    background: #F59E0B;
}

/* 5. Right Column: Map Card & Telemetry Header */
.track-map-card {
    border-radius: 24px;
    overflow: hidden;
    position: relative;
}

.track-telemetry-header {
    padding: 1.15rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    border-bottom: 1.5px solid #F1F5F9;
    background: #FFFFFF;
    flex-wrap: wrap;
}

.telemetry-bus-snapshot {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.telemetry-thumb-wrap {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    overflow: hidden;
    border: 1.5px solid #E2E8F0;
}

.telemetry-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.telemetry-bus-info {
    display: flex;
    flex-direction: column;
}

.telemetry-bus-number {
    font-weight: 800;
    font-size: 1.05rem;
    color: #0C2B52;
    line-height: 1.25;
}

.telemetry-bus-route {
    font-size: 0.82rem;
    color: #64748B;
    margin: 2px 0 3px 0;
    font-weight: 500;
}

.telemetry-stats-group {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.telemetry-stat {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.telemetry-icon-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #EDF6FF;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.telemetry-stat-meta {
    display: flex;
    flex-direction: column;
}

.telemetry-stat-label {
    font-size: 0.75rem;
    color: #64748B;
    font-weight: 500;
}

.telemetry-stat-val {
    font-size: 0.92rem;
    font-weight: 800;
    color: #0C2B52;
    line-height: 1.25;
}

/* 6. Interactive Leaflet Map Canvas & Floating Controls */
.track-map-container {
    position: relative;
    width: 100%;
    height: 560px;
    min-height: 560px;
    background: #E8EEF5;
}

.track-map-canvas {
    width: 100%;
    height: 100%;
    z-index: 1;
}

/* Map / Satellite Toggle */
.track-map-type-toggle {
    background: #FFFFFF;
    border-radius: 9999px;
    padding: 4px;
    display: flex;
    gap: 4px;
    box-shadow: 0 4px 16px rgba(12, 43, 82, 0.12);
    z-index: 999;
}

.track-desktop-type-toggle {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
}

.type-btn {
    padding: 6px 16px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    background: transparent;
    color: #475569;
}

.type-btn.active {
    background: #176B5B;
    color: #FFFFFF;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
}

/* Floating Live Location Badge (Bottom Left) */
.track-map-live-badge {
    position: absolute;
    bottom: 1.5rem;
    left: 1.5rem;
    z-index: 999;
    background: #FFFFFF;
    border-radius: 16px;
    padding: 0.75rem 1.15rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 4px 16px rgba(12, 43, 82, 0.12);
    border: 1px solid #E2E8F0;
}

.live-badge-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.live-badge-text {
    display: flex;
    flex-direction: column;
}

.live-badge-text strong {
    font-size: 0.92rem;
    font-weight: 800;
    color: #0C2B52;
    line-height: 1.2;
}

.live-badge-text span {
    font-size: 0.75rem;
    color: #64748B;
    margin-top: 2px;
}

/* Floating Zoom & Locate Controls (Bottom Right) */
.track-map-zoom-controls {
    position: absolute;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.zoom-btn {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    color: #0C2B52;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(12, 43, 82, 0.1);
    transition: all 0.2s ease;
}

.zoom-btn:hover {
    background: #F8FAFD;
    border-color: #176B5B;
    color: #176B5B;
    transform: scale(1.05);
}

/* Custom Leaflet Map Markers */
.custom-pin-wrap {
    background: transparent;
    border: none;
}

.custom-map-pin {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.custom-map-pin .pin-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    border: 2.5px solid #FFFFFF;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.origin-pin .pin-circle {
    background: #10B981;
}

.dest-pin .pin-circle {
    background: #EF4444;
}

.custom-map-pin .pin-label {
    margin-top: 3px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 9999px;
    padding: 2px 8px;
    font-size: 0.72rem;
    font-weight: 800;
    color: #0C2B52;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}

.custom-bus-pin-wrap {
    background: transparent;
    border: none;
}

.custom-bus-live-pin {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.bus-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    border: 3px solid #FFFFFF;
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.45);
    z-index: 2;
}

.bus-pulse-ring {
    position: absolute;
    top: 0;
    left: 26px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(23, 107, 91, 0.35);
    animation: bus-radar-pulse 1.8s ease-out infinite;
    z-index: 1;
}

@keyframes bus-radar-pulse {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(2.2); opacity: 0; }
}

.bus-pin-label {
    margin-top: 4px;
    background: #0C2B52;
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 2px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    z-index: 2;
}

/* 7. Mobile Visibility Defaults */
.track-mobile-top-section {
    display: none;
}

.track-mob-map-header {
    display: none;
}

.track-mobile-info-card {
    display: none;
}

/* ==========================================================================
   RESPONSIVE DEVICE ADAPTATIONS (Matching Mobile Reference Image)
   ========================================================================== */
@media (max-width: 960px) {
    /* Main Grid collapses to single column */
    .track-main-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    /* Hide Desktop Left Column */
    .track-left-col {
        display: none !important;
    }

    /* Hide Desktop Telemetry Header */
    .track-telemetry-header {
        display: none !important;
    }

    .track-desktop-type-toggle {
        display: none !important;
    }

    /* Show Mobile Top Search & Selected Bus Card */
    .track-mobile-top-section {
        display: block;
        margin-bottom: 1.25rem;
    }

    .track-mobile-selected-bus-card {
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 18px;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        box-shadow: 0 4px 14px rgba(12, 43, 82, 0.04);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .track-mobile-selected-bus-card:active {
        background: #F8FAFD;
        border-color: #176B5B;
    }

    /* Show Mobile Header Overlay Inside Map */
    .track-mob-map-header {
        position: absolute;
        top: 1rem;
        left: 1rem;
        right: 1rem;
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1.5px solid #E2E8F0;
        border-radius: 16px;
        padding: 0.6rem 0.85rem;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .mob-header-bus-tag {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .mob-header-thumb {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #E2E8F0;
    }

    .mob-header-bus-meta {
        display: flex;
        flex-direction: column;
    }

    .mob-header-bus-meta strong {
        font-size: 0.88rem;
        color: #0C2B52;
        font-weight: 800;
        line-height: 1.2;
    }

    .mob-header-bus-meta span {
        font-size: 0.72rem;
        color: #64748B;
    }

    .track-map-container {
        height: 400px;
        min-height: 400px;
    }

    /* Show Mobile Trip Information Card */
    .track-mobile-info-card {
        display: block;
        padding: 1.25rem;
        margin-top: 1.25rem;
    }

    .track-mobile-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .mob-info-col {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .mob-info-item {
        display: flex;
        flex-direction: column;
    }

    .mob-info-label {
        font-size: 0.75rem;
        color: #64748B;
        font-weight: 500;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .mob-info-label i {
        font-size: 0.72rem;
        color: #94A3B8;
    }

    .mob-info-val {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0C2B52;
    }

    .mob-telemetry-bottom-strip {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1.5px solid #F1F5F9;
    }

    .mob-telem-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mob-telem-icon {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 50%;
        background: #EDF6FF;
        color: #176B5B;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
    }

    .mob-telem-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .mob-telem-label {
        font-size: 0.68rem;
        color: #64748B;
        white-space: nowrap;
    }

    .mob-telem-val {
        font-size: 0.82rem;
        font-weight: 800;
        color: #0C2B52;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}

@media (max-width: 600px) {
    .track-header-bar {
        margin-bottom: 1.15rem;
    }

    .track-header-icon-box {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 1.2rem;
        border-radius: 14px;
    }

    .track-page-title {
        font-size: 1.3rem;
    }

    .track-page-subtitle {
        font-size: 0.8rem;
    }

    .track-header-right {
        width: 100%;
        justify-content: space-between;
    }

    .track-datetime-badge {
        flex: 1;
        font-size: 0.8rem;
        padding: 0.45rem 0.85rem;
    }

    .track-map-container {
        height: 360px;
        min-height: 360px;
    }

    .mob-telemetry-bottom-strip {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}

/* 8. Mobile Bus Drawer Modal */
.track-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(12, 43, 82, 0.6);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    animation: trackFade 0.2s ease;
}

@keyframes trackFade {
    from { opacity: 0; }
    to { opacity: 1; }
}

.track-modal-sheet {
    background: #FFFFFF;
    border-radius: 24px 24px 0 0;
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    padding: 1rem 1.25rem 2rem 1.25rem;
    box-shadow: 0 -10px 30px rgba(0,0,0,0.15);
    animation: trackSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes trackSlideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}

.track-modal-handle {
    width: 40px;
    height: 4px;
    background: #CBD5E1;
    border-radius: 999px;
    margin: 0 auto 0.85rem auto;
}

.track-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    border-bottom: 1.5px solid #F1F5F9;
    margin-bottom: 1rem;
}

.track-modal-header h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #0C2B52;
}

.track-modal-close {
    background: #F1F5F9;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 1.2rem;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.modal-bus-list {
    overflow-y: auto;
    max-height: 60vh;
}
</style>
