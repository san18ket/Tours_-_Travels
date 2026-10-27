<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\DriverProfile;
use App\Models\Dispatch;
use App\Services\GpsTrackingService;
use Throwable;

class ApiController extends Controller
{
    private GpsTrackingService $gpsService;
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;

    public function __construct()
    {
        $this->gpsService = new GpsTrackingService();
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
    }

    /**
     * Driver live GPS submission endpoint
     */
    public function updateGps(Request $request): Response
    {
        // 1. Verify authenticated user is a driver
        if (!Session::isLoggedIn() || Session::getUserRole() !== 'driver') {
            return $this->json(['success' => false, 'error' => 'Unauthorized driver session.'], 403);
        }

        $userId = Session::getUserId();
        $driver = $this->driverModel->findByUserId($userId);
        if (!$driver) {
            return $this->json(['success' => false, 'error' => 'Chauffeur profile not found.'], 404);
        }

        $rawLat = $request->input('latitude');
        $rawLng = $request->input('longitude');

        if ($rawLat === null || $rawLng === null || !is_numeric($rawLat) || !is_numeric($rawLng)) {
            return $this->json([
                'success' => false,
                'error'   => 'Valid numerical latitude and longitude are required.'
            ], 422);
        }

        $lat = (float)$rawLat;
        $lng = (float)$rawLng;
        $speed = is_numeric($request->input('speed_kmh')) ? (float)$request->input('speed_kmh') : 0.0;
        $heading = is_numeric($request->input('heading_degrees')) ? (float)$request->input('heading_degrees') : null;
        $battery = is_numeric($request->input('battery_level')) ? (int)$request->input('battery_level') : null;
        $accuracy = is_numeric($request->input('accuracy')) 
            ? (float)$request->input('accuracy') 
            : (is_numeric($request->input('accuracy_m')) ? (float)$request->input('accuracy_m') : null);
        $recordedAt = $request->input('recorded_at') ?? $request->input('timestamp') ?? null;

        try {
            $result = $this->gpsService->recordDriverLocation(
                (int)$driver['id'],
                $lat,
                $lng,
                $speed,
                $heading,
                $battery,
                $accuracy,
                $recordedAt ? (string)$recordedAt : null
            );

            return $this->json([
                'success' => true,
                'message' => 'Telemetry synchronized',
                'data'    => $result
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Live fleet tracking positions for Office Command Center
     */
    public function fleetLive(Request $request): Response
    {
        // Verify admin or dispatcher role
        $role = Session::getUserRole();
        if (!in_array($role, ['admin', 'dispatcher'], true)) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $pdo = \App\Core\Database::getConnection();
        $sql = "SELECT dp.id as driver_id, u.name as driver_name, u.phone as driver_phone,
                       dp.current_latitude, dp.current_longitude, dp.last_gps_at, dp.duty_status,
                       d.id as dispatch_id, d.dispatch_code, d.status as dispatch_status,
                       b.booking_code, b.pickup_address, b.drop_address,
                       COALESCE(v.vehicle_number, 'VG-FLEET') as vehicle_number, 
                       COALESCE(v.make_model, 'Chauffeur Unit') as make_model
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `dispatches` d ON dp.id = d.driver_id AND d.status IN ('driver_accepted', 'en_route_pickup', 'trip_started')
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE dp.current_latitude IS NOT NULL AND dp.current_longitude IS NOT NULL";
        
        $stmt = $pdo->query($sql);
        $rawFleet = $stmt->fetchAll();
        $now = time();

        $vehicles = array_map(function ($row) use ($now) {
            $lastGps = !empty($row['last_gps_at']) ? strtotime($row['last_gps_at']) : 0;
            $diffSeconds = $lastGps > 0 ? ($now - $lastGps) : 999999;

            if ($diffSeconds < 120) {
                $connectivity = 'live';
            } elseif ($diffSeconds <= 600) {
                $connectivity = 'delayed';
            } else {
                $connectivity = 'stale';
            }

            $opStatus = 'off_duty';
            if (!empty($row['dispatch_status'])) {
                $opStatus = 'on_trip';
            } elseif (($row['duty_status'] ?? '') === 'on_duty') {
                $opStatus = 'on_duty';
            }

            $row['connectivity_status'] = $connectivity;
            $row['operational_status'] = $opStatus;
            $row['last_seen_seconds_ago'] = $diffSeconds;
            return $row;
        }, $rawFleet);

        return $this->json([
            'success'   => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'fleet'     => $vehicles,
        ]);
    }

    /**
     * Service worker connectivity heartbeat
     */
    public function syncStatus(Request $request): Response
    {
        return $this->json([
            'online'       => true,
            'server_time'  => date('Y-m-d H:i:s'),
            'app_version'  => \App\Core\Config::get('app.version', '1.0.0'),
            'authenticated' => Session::isLoggedIn(),
            'role'         => Session::getUserRole(),
        ]);
    }
}
