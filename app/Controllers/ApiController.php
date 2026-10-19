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

        $lat = (float)$request->input('latitude');
        $lng = (float)$request->input('longitude');
        $speed = (float)$request->input('speed_kmh', 0.0);
        $heading = $request->input('heading_degrees') ? (float)$request->input('heading_degrees') : null;
        $battery = $request->input('battery_level') ? (int)$request->input('battery_level') : null;

        try {
            $result = $this->gpsService->recordDriverLocation(
                (int)$driver['id'],
                $lat,
                $lng,
                $speed,
                $heading,
                $battery
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
                       v.vehicle_number, v.make_model
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `dispatches` d ON dp.id = d.driver_id AND d.status IN ('driver_accepted', 'en_route_pickup', 'trip_started')
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE dp.current_latitude IS NOT NULL AND dp.current_longitude IS NOT NULL";
        
        $stmt = $pdo->query($sql);
        $vehicles = $stmt->fetchAll();

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
