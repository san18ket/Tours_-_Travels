<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Session;
use App\Models\User;

$testName = "Live Tracking Console Suite (/office/tracking)";
echo "======================================================\n";
echo " RUNNING SUITE: {$testName}\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

// 1. Verify View File & Structural Elements
$viewPath = dirname(__DIR__) . '/app/Views/office/tracking/index.php';
assertCondition(file_exists($viewPath), "tracking/index.php view file exists");
$content = file_get_contents($viewPath);

echo "\n--- 1. Testing Track Location Header Elements ---\n";
assertCondition(strpos($content, 'Track Location') !== false, "Track Location title is present");
assertCondition(strpos($content, 'Live tracking of your bus and trip locations') !== false, "Track Location subtitle is present");
assertCondition(strpos($content, 'track-datetime-badge') !== false, "Live Date & Time badge is present");
assertCondition(strpos($content, 'refreshTrackingBtn') !== false, "Refresh radar button is present");

echo "\n--- 2. Testing Two-Column Grid & Fleet List Structure ---\n";
assertCondition(strpos($content, 'track-main-grid') !== false, "Two-column main tracking grid is present");
assertCondition(strpos($content, 'track-search-box') !== false, "Bus search input box is present");
assertCondition(strpos($content, 'trackingBusList') !== false, "Bus items list rail is present");
assertCondition(strpos($content, 'MH-31 BX-8015') !== false, "Reference bus MH-31 BX-8015 configured");
assertCondition(strpos($content, 'MH-12 AB-5678') !== false, "Reference bus MH-12 AB-5678 configured");
assertCondition(strpos($content, 'MH-14 CD-9032') !== false, "Reference bus MH-14 CD-9032 configured");
assertCondition(strpos($content, 'MH-20 EF-7788') !== false, "Reference bus MH-20 EF-7788 configured");
assertCondition(strpos($content, 'MH-31 GH-4421') !== false, "Reference bus MH-31 GH-4421 configured");

echo "\n--- 3. Testing Trip Information Card ---\n";
assertCondition(strpos($content, 'Trip Information') !== false, "Trip Information card title is present");
assertCondition(strpos($content, 'infoBusNumber') !== false, "infoBusNumber field is present");
assertCondition(strpos($content, 'infoRoute') !== false, "infoRoute field is present");
assertCondition(strpos($content, 'infoDriverName') !== false, "infoDriverName field is present");
assertCondition(strpos($content, 'infoContact') !== false, "infoContact field is present");
assertCondition(strpos($content, 'infoStatus') !== false, "infoStatus field is present");

echo "\n--- 4. Testing Map Canvas & Telemetry Controls ---\n";
assertCondition(strpos($content, 'liveRadarMap') !== false, "liveRadarMap Leaflet canvas is present");
assertCondition(strpos($content, 'leaflet/leaflet.js') !== false, "Local Leaflet script loaded");
assertCondition(strpos($content, 'mapCurrentLocation') !== false, "Current Location telemetry field is present");
assertCondition(strpos($content, 'mapSpeed') !== false, "Speed telemetry field is present");
assertCondition(strpos($content, 'mapEta') !== false, "ETA telemetry field is present");
assertCondition(strpos($content, 'setMapType') !== false, "Map vs Satellite layer switcher is present");
assertCondition(strpos($content, 'track-map-live-badge') !== false, "Live Location floating badge is present");
assertCondition(strpos($content, 'track-map-zoom-controls') !== false, "Floating zoom & locate controls are present");

echo "\n--- 5. Testing Mobile Viewport Adaptations ---\n";
assertCondition(strpos($content, 'track-mobile-top-section') !== false, "Mobile top search & selected bus card present");
assertCondition(strpos($content, 'track-mobile-info-card') !== false, "Mobile trip info card present");
assertCondition(strpos($content, 'mob-telemetry-bottom-strip') !== false, "Mobile bottom telemetry strip present");
assertCondition(strpos($content, 'track-modal-sheet') !== false, "Mobile bus drawer selector modal present");
assertCondition(strpos($content, '@media (max-width: 960px)') !== false, "Tablet/Mobile @media query defined");
assertCondition(strpos($content, '@media (max-width: 600px)') !== false, "Small mobile @media query defined");

echo "\n--- 6. Testing Full-Width Workspace Coverage ---\n";
assertCondition(preg_match('/\.track-location-container\s*\{[^}]*width:\s*100%/s', $content) === 1, ".track-location-container width: 100% defined");
assertCondition(preg_match('/\.track-location-container\s*\{[^}]*max-width:\s*100%/s', $content) === 1, ".track-location-container max-width: 100% defined");

echo "\n--- 7. Testing HTTP Dispatch & Security Permissions ---\n";
Session::start();
Session::set('user_id', 1);
Session::set('user_role', 'admin');
$userModel = new User();
Session::set('user', $userModel->find(1));

$router = require dirname(__DIR__) . '/app/routes.php';
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']);
$res = $router->dispatch($req);

assertCondition($res->getStatusCode() === 200, "Admin granted HTTP 200 OK on /office/tracking");
$html = $res->getBody();
assertCondition(strpos($html, 'Track Location') !== false, "Response body contains Track Location title");
assertCondition(strpos($html, 'liveRadarMap') !== false, "Response body contains liveRadarMap");
assertCondition(strpos($html, 'Trip Information') !== false, "Response body contains Trip Information");

echo "\n--- 8. Testing Mobile Bottom Navbar Labels ---\n";
assertCondition(strpos($html, '<span>Trips</span>') !== false, "Bottom navbar displays concise 'Trips' label");
assertCondition(strpos($html, '<span>Trip Operations</span>') === false, "Bottom navbar does NOT display lengthy 'Trip Operations'");

// Dispatcher Role (Allowed)
Session::set('user_role', 'dispatcher');
$dispReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']);
$dispRes = $router->dispatch($dispReq);
assertCondition($dispRes->getStatusCode() === 200, "Dispatcher granted HTTP 200 OK on /office/tracking");

// Driver Role (Blocked)
Session::set('user_role', 'driver');
$drvReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/office/tracking']);
$drvRes = $router->dispatch($drvReq);
assertCondition($drvRes->getStatusCode() === 403, "Driver role blocked with HTTP 403 on /office/tracking");

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

