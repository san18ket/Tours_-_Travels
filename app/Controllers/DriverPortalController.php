<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\DriverProfile;
use App\Models\Dispatch;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Employee;
use App\Models\GpsLog;
use App\Models\AuditLog;
use App\Services\DispatchService;
use Throwable;

class DriverPortalController extends Controller
{
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;
    private DispatchService $dispatchService;
    private Vehicle $vehicleModel;
    private User $userModel;
    private Employee $employeeModel;
    private GpsLog $gpsLogModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
        $this->dispatchService = new DispatchService();
        $this->vehicleModel = new Vehicle();
        $this->userModel = new User();
        $this->employeeModel = new Employee();
        $this->gpsLogModel = new GpsLog();
        $this->auditLog = new AuditLog();
    }

    /**
     * Resolve the authenticated driver profile with strict session security
     */
    private function getAuthenticatedDriver(): array
    {
        $userId = Session::getUserId();
        if (!$userId) {
            throw new \RuntimeException("Session expired. Please log in again.");
        }

        $driver = $this->driverModel->findByUserId($userId);
        if (!$driver) {
            throw new \RuntimeException("Driver profile record not found for your account.");
        }
        return $driver;
    }

    /**
     * MODULE 19.4: Driver Dashboard
     */
    public function dashboard(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            // Stats
            $stats = $this->dispatchModel->getDriverDashboardStats($driverProfileId);

            // Active trip assignment (if any)
            $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverProfileId);

            // Upcoming / today's assigned trips
            $assignedTrips = $this->dispatchModel->getDriverAssignedTrips($driverProfileId, null, null, null, 1, 5);

            // Recent completed trips
            $recentHistory = $this->dispatchModel->getDriverHistoryPaginatedScoped($driverProfileId, null, 'completed', null, null, 1, 5);

            // Current assigned vehicle
            $currentVehicle = $this->dispatchModel->getDriverCurrentVehicle($driverProfileId);

            return $this->render('driver/dashboard', [
                'driver'         => $driver,
                'stats'          => $stats,
                'activeTrip'     => $activeTrip,
                'assignedTrips'  => $assignedTrips,
                'recentHistory'  => $recentHistory,
                'currentVehicle' => $currentVehicle,
                'title'          => 'Chauffeur Command Dashboard',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/login'));
        }
    }

    /**
     * Backward-compatible alias for duty console
     */
    public function duty(Request $request): Response
    {
        return $this->dashboard($request);
    }

    /**
     * MODULE 19.5: My Assigned Trips
     */
    public function assignedTrips(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            $search = trim((string)$request->query('search', ''));
            $status = trim((string)$request->query('status', ''));
            $date   = trim((string)$request->query('date', ''));
            $page   = max(1, (int)$request->query('page', 1));
            $perPage = max(5, min(100, (int)$request->query('per_page', 10)));

            $allowedStatuses = ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'];
            if (!empty($status) && !in_array($status, $allowedStatuses, true)) {
                $status = null;
            }

            // Executive KPI breakdown metrics for driver trips
            $totalAll      = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, null, null);
            $totalAssigned = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'assigned', null);
            $totalAccepted = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'driver_accepted', null);
            $totalStarted  = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'trip_started', null)
                           + $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'en_route_pickup', null);

            $total = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, $search ?: null, $status ?: null, $date ?: null);
            $trips = $this->dispatchModel->getDriverAssignedTrips($driverProfileId, $search ?: null, $status ?: null, $date ?: null, $page, $perPage);

            $totalPages = max(1, (int)ceil($total / $perPage));

            return $this->render('driver/trips', [
                'driver'         => $driver,
                'trips'          => $trips,
                'total'          => $total,
                'totalAll'       => $totalAll,
                'totalAssigned'  => $totalAssigned,
                'totalAccepted'  => $totalAccepted,
                'totalStarted'   => $totalStarted,
                'page'           => $page,
                'perPage'        => $perPage,
                'totalPages'     => $totalPages,
                'search'         => $search,
                'status'         => $status,
                'date'           => $date,
                'title'          => 'My Assigned Trips',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.6: Trip Details & Trip Workflow
     */
    public function tripDetails(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            // Scope record ownership strictly to authenticated driver
            $dispatch = $this->dispatchModel->findForDriverWithDetails($dispatchId, $driverProfileId);
            if (!$dispatch) {
                Session::setFlash('danger', 'Unauthorized: Trip assignment not found or does not belong to your account.');
                return $this->redirect(url('/driver/trips'));
            }

            // Status transition history trail
            $statusHistory = $this->dispatchModel->getStatusHistory($dispatchId);

            return $this->render('driver/show', [
                'driver'        => $driver,
                'trip'          => $dispatch,
                'statusHistory' => $statusHistory,
                'title'         => 'Trip Details — ' . ($dispatch['booking_code'] ?? 'Journey'),
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/trips'));
        }
    }

    /**
     * MODULE 19.6: Driver Accept Trip Action
     */
    public function acceptTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $this->dispatchService->driverAccept($dispatchId, (int)$driver['id']);
            Session::setFlash('success', 'Trip assignment accepted! Please review pickup itinerary.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (str_contains($referer, '/driver/trips/' . $dispatchId)) {
            return $this->redirect(url('/driver/trips/' . $dispatchId));
        }
        return $this->redirect(url('/driver/trips'));
    }

    /**
     * MODULE 19.6: Driver Start Trip Action
     */
    public function startTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $odo = (int)$request->input('start_odometer', 0);
            $notes = trim((string)$request->input('driver_notes', ''));

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid starting odometer reading.');
            }

            $this->dispatchService->startTrip($dispatchId, (int)$driver['id'], $odo, $notes ?: null);
            Session::setFlash('success', 'Trip commenced! Live telemetry tracking is now active.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/trips/' . $dispatchId));
    }

    /**
     * MODULE 19.6: Driver Complete Trip Action
     */
    public function completeTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $odo = (int)$request->input('end_odometer', 0);
            $notes = trim((string)$request->input('driver_notes', ''));

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid ending odometer reading.');
            }

            $this->dispatchService->completeTrip($dispatchId, (int)$driver['id'], $odo, $notes ?: null);
            Session::setFlash('success', 'Trip marked completed successfully! Vehicle logged to available fleet.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/trips/' . $dispatchId));
    }

    /**
     * MODULE 19.7: Driver Trip History
     */
    public function history(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            $search   = trim((string)$request->query('search', ''));
            $status   = trim((string)$request->query('status', ''));
            $fromDate = trim((string)$request->query('from_date', ''));
            $toDate   = trim((string)$request->query('to_date', ''));
            $page     = max(1, (int)$request->query('page', 1));
            $perPage  = max(5, min(100, (int)$request->query('per_page', 10)));

            // Only allow valid history statuses
            if (!empty($status) && !in_array($status, ['completed', 'cancelled'], true)) {
                $status = null;
            }

            // Executive KPI breakdown metrics for driver history
            $totalHistoryAll = $this->dispatchModel->countDriverHistoryScoped($driverProfileId, null, null, null, null);
            $totalCompleted  = $this->dispatchModel->countDriverHistoryScoped($driverProfileId, null, 'completed', null, null);
            $totalCancelled  = $this->dispatchModel->countDriverHistoryScoped($driverProfileId, null, 'cancelled', null, null);

            // Total verified distance driven across completed missions
            $pdo = \App\Core\Database::getConnection();
            $stmtDist = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN end_odometer > start_odometer THEN end_odometer - start_odometer ELSE 0 END), 0) FROM `dispatches` WHERE driver_id = ? AND status = 'completed'");
            $stmtDist->execute([$driverProfileId]);
            $totalDistanceKm = (int)$stmtDist->fetchColumn();

            $total = $this->dispatchModel->countDriverHistoryScoped($driverProfileId, $search ?: null, $status ?: null, $fromDate ?: null, $toDate ?: null);
            $history = $this->dispatchModel->getDriverHistoryPaginatedScoped($driverProfileId, $search ?: null, $status ?: null, $fromDate ?: null, $toDate ?: null, $page, $perPage);

            $totalPages = max(1, (int)ceil($total / $perPage));

            return $this->render('driver/history', [
                'driver'          => $driver,
                'history'         => $history,
                'total'           => $total,
                'totalHistoryAll' => $totalHistoryAll,
                'totalCompleted'  => $totalCompleted,
                'totalCancelled'  => $totalCancelled,
                'totalDistanceKm' => $totalDistanceKm,
                'page'            => $page,
                'perPage'         => $perPage,
                'totalPages'      => $totalPages,
                'search'          => $search,
                'status'          => $status,
                'fromDate'        => $fromDate,
                'toDate'          => $toDate,
                'title'           => 'Trip History & Archive',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.8: My Vehicle
     */
    public function vehicle(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            $vehicle = $this->dispatchModel->getDriverCurrentVehicle($driverProfileId);

            return $this->render('driver/vehicle', [
                'driver'  => $driver,
                'vehicle' => $vehicle,
                'title'   => 'Assigned Fleet Vehicle',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.9: Live Location & Location Sharing
     */
    public function location(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            // Get active trip for GPS context
            $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverProfileId);

            // Recent GPS breadcrumb logs
            $recentLogs = $this->gpsLogModel->getLatestForDriver($driverProfileId, 15);

            return $this->render('driver/location', [
                'driver'     => $driver,
                'user'       => Session::getUser() ?? [],
                'activeTrip' => $activeTrip,
                'recentLogs' => $recentLogs,
                'title'      => 'Live GPS Telemetry & Location Sharing',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.10: Driver Profile
     */
    public function profile(Request $request): Response
    {
        try {
            $userId = Session::getUserId();
            $driver = $this->driverModel->getProfileWithDetailsByUserId($userId);
            if (!$driver) {
                throw new \RuntimeException('Driver profile could not be loaded.');
            }

            return $this->render('driver/profile', [
                'driver' => $driver,
                'title'  => 'My Chauffeur Profile',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.10: Update Permitted Driver Profile Fields
     */
    public function updateProfile(Request $request): Response
    {
        try {
            $userId = Session::getUserId();
            $driver = $this->driverModel->findByUserId($userId);
            if (!$driver) {
                throw new \RuntimeException('Driver profile not found.');
            }

            // Only permit safe contact and emergency fields
            $phone = trim((string)$request->input('phone', ''));
            $emergencyContact = trim((string)$request->input('emergency_contact', ''));
            $alternatePhone = trim((string)$request->input('alternate_phone', ''));
            $address = trim((string)$request->input('address', ''));

            if (empty($phone)) {
                throw new \RuntimeException('Primary mobile phone is required.');
            }

            // Update user record
            $this->userModel->update($userId, [
                'phone' => $phone
            ]);

            // Update driver profile record
            $this->driverModel->update($driver['id'], [
                'emergency_contact' => $emergencyContact ?: null
            ]);

            // Update linked employee record if present
            if (!empty($driver['employee_id'])) {
                $this->employeeModel->update((int)$driver['employee_id'], [
                    'phone'             => $phone,
                    'alternate_phone'   => $alternatePhone ?: null,
                    'emergency_contact' => $emergencyContact ?: null,
                    'address'           => $address ?: null,
                ]);
            }

            $this->auditLog->record('driver_profile_updated', 'driver_profiles', (int)$driver['id'], null, [
                'phone' => $phone
            ], $userId);

            Session::setFlash('success', 'Your contact and profile details have been saved successfully.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/profile'));
    }

    /**
     * MODULE 19.10: Update Driver Password
     */
    public function updatePassword(Request $request): Response
    {
        try {
            $userId = Session::getUserId();
            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new \RuntimeException('User account record not found.');
            }

            $currentPassword = (string)$request->input('current_password', '');
            $newPassword     = (string)$request->input('new_password', '');
            $confirmPassword = (string)$request->input('confirm_password', '');

            if (empty($currentPassword)) {
                throw new \RuntimeException('Please enter your current password.');
            }

            if (!password_verify($currentPassword, $user['password'])) {
                throw new \RuntimeException('The current password entered is incorrect.');
            }

            if (strlen($newPassword) < 8) {
                throw new \RuntimeException('New password must be at least 8 characters long.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new \RuntimeException('New password and confirmation do not match.');
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->userModel->update($userId, [
                'password' => $hashedPassword
            ]);

            $this->auditLog->record('driver_password_changed', 'users', $userId, null, null, $userId);

            Session::setFlash('success', 'Your password has been changed securely.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/profile'));
    }

    /**
     * Duty status toggle
     */
    public function toggleDuty(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            if ($driver['duty_status'] === 'on_trip') {
                Session::setFlash('danger', 'Cannot change duty status while currently assigned to an active trip.');
                return $this->redirect(url('/driver/dashboard'));
            }

            $newStatus = ($driver['duty_status'] === 'on_duty') ? 'off_duty' : 'on_duty';
            $this->driverModel->update($driver['id'], ['duty_status' => $newStatus]);

            Session::setFlash('success', 'Duty status updated: ' . strtoupper(str_replace('_', ' ', $newStatus)));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer) && !str_contains($referer, '/logout')) {
            return $this->redirect($referer);
        }
        return $this->redirect(url('/driver/dashboard'));
    }
}
