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
use App\Models\DriverTripPayment;
use App\Services\DispatchService;
use App\Services\NotificationService;
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
    private NotificationService $notificationService;
    private DriverTripPayment $driverTripPaymentModel;

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
        $this->notificationService = new NotificationService();
        $this->driverTripPaymentModel = new DriverTripPayment();
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

            // Financial Summary (Earnings, Paid, Pending)
            $financialSummary = $this->driverTripPaymentModel->getDriverFinancialSummary($driverProfileId);

            // Executive KPI breakdown metrics for driver dashboard (Real DB Metrics)
            $kpiTotalTrips        = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, null, null) + (int)($stats['completed_trips'] ?? 0);
            $kpiAwaitingResponse  = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'assigned', null);
            $kpiAccepted          = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'driver_accepted', null);
            $kpiActive            = $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'trip_started', null)
                                  + $this->dispatchModel->countDriverAssignedTrips($driverProfileId, null, 'en_route_pickup', null);
            $kpiCompleted         = (int)($stats['completed_trips'] ?? 0);
            $kpiPendingEarnings   = (float)($financialSummary['total_pending'] ?? 0);

            // Recent Notifications & Unread count for this employee/driver
            $driverUserId = (int)$driver['user_id'];
            $driverEmpId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;
            $notifications = $this->notificationService->getDriverNotifications($driverUserId, $driverEmpId, 8);
            $unreadNotifCount = $this->notificationService->getUnreadCount($driverUserId, $driverEmpId);

            return $this->render('driver/dashboard', [
                'driver'           => $driver,
                'stats'            => $stats,
                'kpiStats'         => [
                    'total_trips'       => $kpiTotalTrips,
                    'awaiting_response' => $kpiAwaitingResponse,
                    'accepted'          => $kpiAccepted,
                    'active'            => $kpiActive,
                    'completed'         => $kpiCompleted,
                    'pending_earnings'  => $kpiPendingEarnings,
                ],
                'financialSummary' => $financialSummary,
                'activeTrip'       => $activeTrip,
                'assignedTrips'    => $assignedTrips,
                'recentHistory'    => $recentHistory,
                'currentVehicle'   => $currentVehicle,
                'notifications'    => $notifications,
                'unreadNotifCount' => $unreadNotifCount,
                'title'            => 'Driver Command Dashboard',
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

            // Payment vouchers for this dispatch
            $payments = $this->driverTripPaymentModel->getPaymentsByDispatch($dispatchId);

            // Grouped trip inspection evidence
            $evidenceService = new \App\Services\TripEvidenceService();
            $evidence = $evidenceService->getGroupedEvidence($dispatchId);

            // Fetch recent GPS breadcrumbs for live active trip
            $pdo = \App\Core\Database::getConnection();
            $stmtGps = $pdo->prepare("SELECT latitude, longitude, speed_kmh, recorded_at FROM `gps_logs` WHERE `dispatch_id` = ? ORDER BY id DESC LIMIT 50");
            $stmtGps->execute([$dispatchId]);
            $gpsTrail = array_reverse($stmtGps->fetchAll(\PDO::FETCH_ASSOC));

            return $this->render('driver/show', [
                'driver'        => $driver,
                'trip'          => $dispatch,
                'statusHistory' => $statusHistory,
                'payments'      => $payments,
                'evidence'      => $evidence,
                'gpsTrail'      => $gpsTrail,
                'title'         => 'Trip Details — ' . ($dispatch['booking_code'] ?? 'Journey'),
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/trips'));
        }
    }

    /**
     * MODULE 19.6: Dedicated Evidence Upload Endpoint
     */
    public function uploadEvidence(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverId = (int)$driver['id'];

            $type = (string)($request->input('evidence_type') ?? 'start_vehicle');
            $fileData = null;
            if ($request->hasFile('evidence_file')) {
                $fileData = $request->getFile('evidence_file');
            } elseif (!empty($request->input('file_base64'))) {
                $fileData = (string)$request->input('file_base64');
            } else {
                throw new \InvalidArgumentException('No photo file or camera capture was provided.');
            }

            $odo = $request->input('odometer_reading') ? (int)$request->input('odometer_reading') : null;
            $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
            $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;
            $notes = $request->input('notes') ? (string)$request->input('notes') : null;

            $evidenceService = new \App\Services\TripEvidenceService();
            $evidence = $evidenceService->storeEvidence(
                $dispatchId,
                $driverId,
                $type,
                $fileData,
                $odo,
                $lat,
                $lng,
                $notes
            );

            if ($request->isAjax()) {
                return $this->json(['success' => true, 'evidence' => $evidence]);
            }

            Session::setFlash('success', 'Trip inspection photo uploaded successfully.');
            return $this->redirect(url('/driver/trips/' . $dispatchId));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/trips/' . $dispatchId));
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
            Session::setFlash('success', 'Trip assignment accepted! Customer confirmation has been dispatched.');
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
     * Driver Unable to Accept Action (Decline / Can't Take Duty)
     */
    public function unableToAccept(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        $reason = trim((string)$request->input('reason', ''));
        if (empty($reason)) {
            $reason = trim((string)$request->input('rejection_reason', 'Schedule conflict / Unavailable'));
        }

        try {
            $driver = $this->getAuthenticatedDriver();
            $this->dispatchService->driverUnableToAccept($dispatchId, (int)$driver['id'], $reason);
            Session::setFlash('warning', 'You have marked yourself unable to accept this trip. Dispatch operations have been notified for reassignment.');
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
     * MODULE 19.6: Driver Start Trip Action (Pre-Trip Inspection, Odometer & Photo Validation)
     */
    public function startTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverId = (int)$driver['id'];
            $odo = (int)$request->input('start_odometer', 0);
            $notes = trim((string)$request->input('driver_notes', ''));
            $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
            $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid starting odometer reading.');
            }

            // Evidence collection
            $evidenceData = [];

            // 1. Starting vehicle photo
            if ($request->hasFile('start_vehicle_photo')) {
                $evidenceData[] = [
                    'type'      => 'start_vehicle',
                    'file_data' => $request->getFile('start_vehicle_photo'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            } elseif (!empty($request->input('start_vehicle_base64'))) {
                $evidenceData[] = [
                    'type'      => 'start_vehicle',
                    'file_data' => (string)$request->input('start_vehicle_base64'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            }

            // 2. Starting odometer photo
            if ($request->hasFile('start_odometer_photo')) {
                $evidenceData[] = [
                    'type'      => 'start_odometer',
                    'file_data' => $request->getFile('start_odometer_photo'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            } elseif (!empty($request->input('start_odometer_base64'))) {
                $evidenceData[] = [
                    'type'      => 'start_odometer',
                    'file_data' => (string)$request->input('start_odometer_base64'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            }

            $this->dispatchService->startTrip($dispatchId, $driverId, $odo, $notes ?: null, $evidenceData);

            // If coordinates provided, record initial GPS breadcrumb
            if ($lat !== null && $lng !== null) {
                try {
                    $gpsService = new \App\Services\GpsTrackingService();
                    $gpsService->recordFix($driverId, $lat, $lng, 0.0, null, 10.0, null, date('Y-m-d H:i:s'), $dispatchId);
                } catch (\Throwable $ge) {
                    \App\Core\Logger::warning("Initial GPS fix record skipped: " . $ge->getMessage());
                }
            }

            Session::setFlash('success', 'Trip commenced! Pre-trip vehicle inspection recorded and live telemetry is active.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/trips/' . $dispatchId));
    }

    /**
     * MODULE 19.6: Driver Complete Trip Action (Post-Trip Inspection, End Meter & Photo Validation)
     */
    public function completeTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverId = (int)$driver['id'];
            $odo = (int)$request->input('end_odometer', 0);
            $notes = trim((string)$request->input('driver_notes', ''));
            $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
            $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid ending odometer reading.');
            }

            // Evidence collection
            $evidenceData = [];

            // 1. Ending odometer photo
            if ($request->hasFile('end_odometer_photo')) {
                $evidenceData[] = [
                    'type'      => 'end_odometer',
                    'file_data' => $request->getFile('end_odometer_photo'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            } elseif (!empty($request->input('end_odometer_base64'))) {
                $evidenceData[] = [
                    'type'      => 'end_odometer',
                    'file_data' => (string)$request->input('end_odometer_base64'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            }

            // 2. Ending vehicle photo
            if ($request->hasFile('end_vehicle_photo')) {
                $evidenceData[] = [
                    'type'      => 'end_vehicle',
                    'file_data' => $request->getFile('end_vehicle_photo'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            } elseif (!empty($request->input('end_vehicle_base64'))) {
                $evidenceData[] = [
                    'type'      => 'end_vehicle',
                    'file_data' => (string)$request->input('end_vehicle_base64'),
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ];
            }

            $this->dispatchService->completeTrip($dispatchId, $driverId, $odo, $notes ?: null, $evidenceData);

            // Record final GPS coordinate if provided
            if ($lat !== null && $lng !== null) {
                try {
                    $gpsService = new \App\Services\GpsTrackingService();
                    $gpsService->recordFix($driverId, $lat, $lng, 0.0, null, 10.0, null, date('Y-m-d H:i:s'), $dispatchId);
                } catch (\Throwable $ge) {
                    \App\Core\Logger::warning("Final GPS fix record skipped: " . $ge->getMessage());
                }
            }

            Session::setFlash('success', 'Trip marked completed successfully! Ending meter inspection logged and vehicle returned to available fleet.');
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
            $singleDate = trim((string)$request->query('date', ''));
            if (!empty($singleDate) && empty($fromDate) && empty($toDate)) {
                $fromDate = $singleDate;
                $toDate   = $singleDate;
            }
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
                'title'  => 'My Driver Profile',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.10: Update Permitted Driver Profile Fields & Profile Photo
     */
    public function updateProfile(Request $request): Response
    {
        try {
            $userId = Session::getUserId();
            $driver = $this->driverModel->findByUserId($userId);
            if (!$driver) {
                throw new \RuntimeException('Driver profile not found.');
            }

            // Safe contact and emergency fields
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

            // Profile photo upload or removal
            $employeeId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;
            $newAvatarPath = null;
            $removeAvatar = $request->input('remove_profile_photo') === '1';

            if ($request->hasFile('profile_photo')) {
                $file = $request->getFile('profile_photo');
                if ($file && $file['size'] > 0) {
                    if ($file['size'] > 3 * 1024 * 1024) {
                        throw new \RuntimeException('Profile photo must be smaller than 3MB.');
                    }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp'];
                    if (!in_array($mime, $allowedMimes, true)) {
                        throw new \RuntimeException('Only JPG, PNG and WEBP image formats are supported for profile photos.');
                    }
                    $newAvatarPath = $request->storeUploadedFile('profile_photo', 'employees/avatars');
                }
            } elseif (!empty($request->input('profile_photo_base64')) && str_starts_with((string)$request->input('profile_photo_base64'), 'data:image/')) {
                $b64 = (string)$request->input('profile_photo_base64');
                $commaPos = strpos($b64, ',');
                if ($commaPos !== false) {
                    $header = substr($b64, 0, $commaPos);
                    $dataPart = substr($b64, $commaPos + 1);
                    if (preg_match('/^data:image\/(jpeg|png|webp|jpg);base64/i', $header, $m)) {
                        $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
                        $bin = base64_decode($dataPart);
                        if ($bin !== false && strlen($bin) > 0 && strlen($bin) <= 3 * 1024 * 1024) {
                            $targetDir = dirname(__DIR__, 2) . '/public/storage/employees/avatars';
                            if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                            $filename = 'avatar_' . bin2hex(random_bytes(16)) . '.' . $ext;
                            file_put_contents($targetDir . '/' . $filename, $bin);
                            $newAvatarPath = 'employees/avatars/' . $filename;
                        }
                    }
                }
            }

            // Update linked employee record if present
            if ($employeeId) {
                $empUpdate = [
                    'phone'             => $phone,
                    'alternate_phone'   => $alternatePhone ?: null,
                    'emergency_contact' => $emergencyContact ?: null,
                    'address'           => $address ?: null,
                ];

                if ($newAvatarPath) {
                    if (!empty($driver['profile_photo'])) {
                        $this->deletePhysicalFile($driver['profile_photo']);
                    }
                    $empUpdate['profile_photo'] = $newAvatarPath;
                } elseif ($removeAvatar) {
                    if (!empty($driver['profile_photo'])) {
                        $this->deletePhysicalFile($driver['profile_photo']);
                    }
                    $empUpdate['profile_photo'] = null;
                }

                $this->employeeModel->update($employeeId, $empUpdate);

                // Update session avatar
                $sessionUser = Session::getUser() ?? [];
                if ($newAvatarPath) {
                    $sessionUser['profile_photo'] = $newAvatarPath;
                    Session::setUser($sessionUser);
                } elseif ($removeAvatar) {
                    $sessionUser['profile_photo'] = null;
                    Session::setUser($sessionUser);
                }
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
     * Delete physical file from all storage and public mirrors
     */
    private function deletePhysicalFile(string $pathVal): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $cleaned = str_replace('\\', '/', $pathVal);
        $cleaned = preg_replace('#^(\.\./|/)+#', '', $cleaned);
        $cleaned = preg_replace('#^(assets/uploads/|storage/private_uploads/|storage/|public/storage/|public/assets/uploads/)#', '', $cleaned);

        $candidateLocations = [
            $projectRoot . '/storage/private_uploads/' . $cleaned,
            $projectRoot . '/public/assets/uploads/' . $cleaned,
            $projectRoot . '/public/storage/' . $cleaned,
            $projectRoot . '/storage/uploads/' . $cleaned,
            $projectRoot . '/public/' . ltrim($cleaned, '/'),
        ];

        foreach ($candidateLocations as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
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

            // Revoke all persistent tokens on password change
            (new \App\Services\AuthService())->revokeAllUserRememberTokens($userId);

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

    /**
     * Mark single notification as read (AJAX or Form POST)
     */
    public function markNotificationRead(Request $request, array $params = []): Response
    {
        try {
            $id = (int)($params['id'] ?? $request->input('id', 0));
            $driver = $this->getAuthenticatedDriver();
            $this->notificationService->markAsRead($id, (int)$driver['user_id']);

            if ($request->isAjax() || $request->wantsJson()) {
                $driverEmpId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;
                $unread = $this->notificationService->getUnreadCount((int)$driver['user_id'], $driverEmpId);
                return $this->json([
                    'success' => true,
                    'unread_count' => $unread,
                ]);
            }

            return $this->redirect(url('/driver/dashboard#notifications'));
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * Mark all notifications as read (AJAX or Form POST)
     */
    public function markAllNotificationsRead(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverEmpId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;
            $this->notificationService->markAllAsRead((int)$driver['user_id'], $driverEmpId);

            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json([
                    'success' => true,
                    'unread_count' => 0,
                ]);
            }

            return $this->redirect(url('/driver/dashboard#notifications'));
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * MODULE 19.5: Dedicated Driver Notifications Center Page
     */
    public function notificationsPage(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverUserId = (int)$driver['user_id'];
            $driverEmpId = !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null;

            $search = trim((string)$request->get('search', ''));
            $status = trim((string)$request->get('status', ''));
            $page = max(1, (int)$request->get('page', 1));
            $perPage = in_array((int)$request->get('per_page'), [10, 25, 50], true) ? (int)$request->get('per_page') : 10;

            $kpis = $this->notificationService->getKpiStats($driverUserId, $driverEmpId);
            $totalRecords = $this->notificationService->countFiltered($driverUserId, $driverEmpId, $search, $status);
            $notifications = $this->notificationService->getPaginatedNotifications($driverUserId, $driverEmpId, $search, $status, $page, $perPage);
            $totalPages = max(1, (int)ceil($totalRecords / $perPage));
            $unreadNotifCount = $kpis['total_unread'] ?? 0;

            return $this->render('driver/notifications', [
                'driver'           => $driver,
                'notifications'    => $notifications,
                'totalRecords'     => $totalRecords,
                'totalPages'       => $totalPages,
                'page'             => $page,
                'perPage'          => $perPage,
                'search'           => $search,
                'status'           => $status,
                'kpis'             => $kpis,
                'unreadNotifCount' => $unreadNotifCount,
                'title'            => 'Driver Notifications & Dispatch Alerts',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * Driver Earnings Summary Page
     */
    public function earnings(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            $search   = trim((string)$request->get('search', ''));
            $status   = trim((string)$request->get('status', ''));
            $fromDate = trim((string)$request->get('from_date', ''));
            $toDate   = trim((string)$request->get('to_date', ''));
            $singleDate = trim((string)($request->get('date') ?? $request->query('date', '')));
            if (!empty($singleDate) && empty($fromDate) && empty($toDate)) {
                $fromDate = $singleDate;
                $toDate   = $singleDate;
            }
            $page     = max(1, (int)$request->get('page', 1));
            $perPage  = max(5, min(100, (int)$request->get('per_page', 10)));

            // Overall lifetime financial summary
            $financialSummary = $this->driverTripPaymentModel->getDriverFinancialSummary($driverProfileId);

            $pdo = \App\Core\Database::getConnection();

            // Calculate this month's earned amount for driver
            $stmtMonth = $pdo->prepare("SELECT COALESCE(SUM(driver_trip_amount), 0) FROM dispatches WHERE driver_id = ? AND status = 'completed' AND driver_trip_amount IS NOT NULL AND MONTH(COALESCE(end_time, start_time)) = MONTH(CURRENT_DATE()) AND YEAR(COALESCE(end_time, start_time)) = YEAR(CURRENT_DATE())");
            $stmtMonth->execute([$driverProfileId]);
            $financialSummary['this_month_earned'] = (float)$stmtMonth->fetchColumn();

            // Status breakdown counts for mobile status pills & KPI tags
            $stmtBreakdown = $pdo->prepare("SELECT 
                COUNT(*) as total_all,
                SUM(CASE WHEN (driver_trip_amount IS NOT NULL AND (driver_trip_amount - driver_paid_amount) > 0.009) THEN 1 ELSE 0 END) as total_due,
                SUM(CASE WHEN (driver_trip_amount IS NOT NULL AND driver_paid_amount > 0 AND (driver_trip_amount - driver_paid_amount) > 0.009) THEN 1 ELSE 0 END) as total_partial,
                SUM(CASE WHEN (driver_trip_amount IS NOT NULL AND driver_trip_amount > 0 AND (driver_trip_amount - driver_paid_amount) <= 0.009) THEN 1 ELSE 0 END) as total_paid
            FROM dispatches 
            WHERE driver_id = ? AND status = 'completed'");
            $stmtBreakdown->execute([$driverProfileId]);
            $breakdown = $stmtBreakdown->fetch(\PDO::FETCH_ASSOC) ?: [];

            $totalEarningsAll = (int)($breakdown['total_all'] ?? 0);
            $totalDue         = (int)($breakdown['total_due'] ?? 0);
            $totalPartial     = (int)($breakdown['total_partial'] ?? 0);
            $totalPaid        = (int)($breakdown['total_paid'] ?? 0);

            // Completed trips for this driver
            $where = ["d.driver_id = ?", "d.status = 'completed'"];
            $params = [$driverProfileId];

            if (!empty($search)) {
                $where[] = "(d.dispatch_code LIKE ? OR b.booking_code LIKE ? OR b.pickup_address LIKE ? OR b.drop_address LIKE ? OR v.vehicle_number LIKE ? OR c_cust.name LIKE ? OR u_cust.name LIKE ?)";
                $term = "%{$search}%";
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
            }
            if (!empty($status)) {
                if ($status === 'due' || $status === 'pending') {
                    $where[] = "(d.driver_trip_amount IS NOT NULL AND (d.driver_trip_amount - d.driver_paid_amount) > 0.009)";
                } elseif ($status === 'partially_paid' || $status === 'partial') {
                    $where[] = "(d.driver_trip_amount IS NOT NULL AND d.driver_paid_amount > 0 AND (d.driver_trip_amount - d.driver_paid_amount) > 0.009)";
                } elseif ($status === 'paid' || $status === 'fully_paid') {
                    $where[] = "(d.driver_trip_amount IS NOT NULL AND d.driver_trip_amount > 0 AND (d.driver_trip_amount - d.driver_paid_amount) <= 0.009)";
                } elseif ($status === 'amount_not_specified' || $status === 'unspecified') {
                    $where[] = "(d.driver_trip_amount IS NULL OR d.driver_trip_amount <= 0)";
                } else {
                    $where[] = "d.driver_payment_status = ?";
                    $params[] = $status;
                }
            }
            if (!empty($fromDate)) {
                $where[] = "DATE(COALESCE(d.end_time, d.start_time)) >= ?";
                $params[] = $fromDate;
            }
            if (!empty($toDate)) {
                $where[] = "DATE(COALESCE(d.end_time, d.start_time)) <= ?";
                $params[] = $toDate;
            }

            $sqlWhere = implode(' AND ', $where);

            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM dispatches d JOIN bookings b ON d.booking_id = b.id LEFT JOIN customers c_cust ON b.customer_profile_id = c_cust.id LEFT JOIN users u_cust ON b.customer_id = u_cust.id JOIN vehicles v ON d.vehicle_id = v.id WHERE {$sqlWhere}");
            $stmtCount->execute($params);
            $total = (int)$stmtCount->fetchColumn();
            $totalPages = max(1, (int)ceil($total / $perPage));
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT d.id, d.dispatch_code, d.driver_trip_amount, d.driver_paid_amount, d.driver_payment_status,
                           d.start_time, d.end_time,
                           b.booking_code, b.pickup_address, b.drop_address, b.start_date,
                           COALESCE(c_cust.name, u_cust.name) as customer_name,
                           COALESCE(c_cust.phone, u_cust.phone) as customer_phone,
                           c_cust.profile_photo as customer_avatar,
                           v.vehicle_number, v.make_model
                    FROM dispatches d
                    JOIN bookings b ON d.booking_id = b.id
                    LEFT JOIN customers c_cust ON b.customer_profile_id = c_cust.id
                    LEFT JOIN users u_cust ON b.customer_id = u_cust.id
                    JOIN vehicles v ON d.vehicle_id = v.id
                    WHERE {$sqlWhere}
                    ORDER BY d.end_time DESC, d.id DESC
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($params as $p) {
                $stmt->bindValue($pIdx++, $p);
            }
            $stmt->bindValue($pIdx++, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $trips = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Attach remaining amount & authoritative financial status
            foreach ($trips as &$t) {
                $earned = $t['driver_trip_amount'] !== null ? (float)$t['driver_trip_amount'] : null;
                $paid = (float)$t['driver_paid_amount'];
                $rem = ($earned !== null) ? round(max(0.0, $earned - $paid), 2) : null;
                $t['remaining'] = $rem;

                if ($earned === null || $earned <= 0) {
                    $computedStatus = 'amount_not_specified';
                } elseif ($rem <= 0.0) {
                    $computedStatus = 'paid';
                } elseif ($paid > 0.0) {
                    $computedStatus = 'partially_paid';
                } else {
                    $computedStatus = 'due';
                }
                $t['driver_payment_status'] = $computedStatus;
            }
            unset($t);

            return $this->render('driver/earnings', [
                'driver'           => $driver,
                'financialSummary' => $financialSummary,
                'trips'            => $trips,
                'total'            => $total,
                'totalEarningsAll' => $totalEarningsAll,
                'totalDue'         => $totalDue,
                'totalPartial'     => $totalPartial,
                'totalPaid'        => $totalPaid,
                'totalPages'       => $totalPages,
                'page'             => $page,
                'perPage'          => $perPage,
                'search'           => $search,
                'status'           => $status,
                'fromDate'         => $fromDate,
                'toDate'           => $toDate,
                'title'            => 'My Trip Earnings',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * Driver Payment History Page (Read-only disbursement receipts)
     */
    public function paymentHistory(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];

            $search   = trim((string)$request->get('search', ''));
            $fromDate = trim((string)$request->get('from_date', ''));
            $toDate   = trim((string)$request->get('to_date', ''));
            $page     = max(1, (int)$request->get('page', 1));
            $perPage  = max(5, min(100, (int)$request->get('per_page', 10)));
            $offset   = ($page - 1) * $perPage;

            $filters = ['driver_id' => $driverProfileId];
            if (!empty($search))   $filters['search']    = $search;
            if (!empty($fromDate)) $filters['from_date'] = $fromDate;
            if (!empty($toDate))   $filters['to_date']   = $toDate;

            $financialSummary = $this->driverTripPaymentModel->getDriverFinancialSummary($driverProfileId);
            $totalRecords = $this->driverTripPaymentModel->countPayments($filters);
            $totalPages = max(1, (int)ceil($totalRecords / $perPage));

            $payments = $this->driverTripPaymentModel->getPaymentsWithDetails($filters, $perPage, $offset);

            // Real calculations for KPI cards
            $pdo = \App\Core\Database::getConnection();
            $stmtPartial = $pdo->prepare("SELECT COUNT(*) FROM dispatches WHERE driver_id = ? AND (driver_payment_status = 'partially_paid' OR (driver_trip_amount IS NOT NULL AND driver_paid_amount > 0 AND (driver_trip_amount - driver_paid_amount) > 0.009))");
            $stmtPartial->execute([$driverProfileId]);
            $partiallyPaidCount = (int)$stmtPartial->fetchColumn();

            $stmtLatest = $pdo->prepare("SELECT amount, payment_date FROM driver_trip_payments WHERE driver_id = ? ORDER BY payment_date DESC, id DESC LIMIT 1");
            $stmtLatest->execute([$driverProfileId]);
            $latestPayment = $stmtLatest->fetch(\PDO::FETCH_ASSOC) ?: null;

            return $this->render('driver/payments', [
                'driver'             => $driver,
                'payments'           => $payments,
                'financialSummary'   => $financialSummary,
                'totalRecords'       => $totalRecords,
                'totalPages'         => $totalPages,
                'page'               => $page,
                'perPage'            => $perPage,
                'search'             => $search,
                'fromDate'           => $fromDate,
                'toDate'             => $toDate,
                'partiallyPaidCount' => $partiallyPaidCount,
                'latestPayment'      => $latestPayment,
                'title'              => 'My Payment History',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/dashboard'));
        }
    }

    /**
     * View Driver Payment Receipt (Driver Portal view inside driver layout)
     * Route: GET /driver/payments/receipt/{id}
     */
    public function viewReceipt(Request $request, array $params = []): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];
            $id = (int)($params['id'] ?? 0);
            $receipt = $this->driverTripPaymentModel->findWithFullReceiptDetails($id);

            if (!$receipt) {
                Session::setFlash('danger', 'Payment receipt not found.');
                return $this->redirect(url('/driver/payments'));
            }

            // Strict ownership authorization: driver cannot access another driver's receipt
            if ((int)$receipt['driver_id'] !== $driverProfileId) {
                Session::setFlash('danger', 'Unauthorized: You may only view your own payment receipts.');
                return $this->redirect(url('/driver/payments'));
            }

            return $this->render('driver/receipt_show', [
                'driver'  => $driver,
                'receipt' => $receipt,
                'title'   => "Payment Receipt {$receipt['receipt_number']}",
            ], 'layouts/driver');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Access error: ' . $e->getMessage());
            return $this->redirect(url('/driver/payments'));
        }
    }

    /**
     * Print Driver Payment Receipt (Standalone printable A4 document)
     * Route: GET /driver/payments/receipt/{id}/print
     */
    public function printReceipt(Request $request, array $params = []): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $driverProfileId = (int)$driver['id'];
            $id = (int)($params['id'] ?? 0);
            $receipt = $this->driverTripPaymentModel->findWithFullReceiptDetails($id);

            if (!$receipt) {
                return Response::make("Receipt not found.", 404);
            }

            // Strict ownership authorization
            if ((int)$receipt['driver_id'] !== $driverProfileId) {
                return Response::make("Unauthorized access to this receipt.", 403);
            }

            $stampParam = $request->get('stamp', '1');
            $showStamp = ($stampParam !== '0' && $stampParam !== 0 && $stampParam !== false);

            return $this->render('office/finance/driver_payment_receipt_print', [
                'title'      => "Print Receipt - {$receipt['receipt_number']}",
                'receipt'    => $receipt,
                'show_stamp' => $showStamp,
            ], null); // Render standalone printable document
        } catch (\Throwable $e) {
            return Response::make("Access error: " . $e->getMessage(), 403);
        }
    }
}
