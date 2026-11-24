<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Dispatch;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Services\DispatchService;
use Throwable;

class DispatchController extends Controller
{
    private Dispatch $dispatchModel;
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private DispatchService $dispatchService;

    public function __construct()
    {
        $this->dispatchModel = new Dispatch();
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->dispatchService = new DispatchService();
    }

    /**
     * Dispatch operations overview
     */
    public function index(Request $request): Response
    {
        return $this->redirect(url('/office/trips/history'));
    }

    /**
     * Display Trip Assignment console
     */
    public function createAssign(Request $request): Response
    {
        $bookingId = (int)$request->get('booking_id', 0);
        $selectedBooking = null;
        if ($bookingId > 0) {
            $selectedBooking = $this->bookingModel->findWithDetails($bookingId);
        }

        // Unassigned or confirmed bookings needing assignment
        $pendingBookings = $this->bookingModel->where("`status` IN ('confirmed', 'pending')", [], 'start_date ASC');
        $availableVehicles = $this->vehicleModel->getAvailable();
        $availableDrivers = $this->driverModel->getAvailableDrivers();

        return $this->render('office/trips/assign', [
            'selectedBooking'   => $selectedBooking,
            'pendingBookings'   => $pendingBookings,
            'availableVehicles' => $availableVehicles,
            'availableDrivers'  => $availableDrivers,
            'title'             => 'Assign Trip Duty to Driver',
        ], 'layouts/office');
    }

    /**
     * Execute trip assignment
     */
    public function assign(Request $request): Response
    {
        try {
            $data = $this->validate($request->all(), [
                'booking_id' => 'required|numeric',
                'vehicle_id' => 'required|numeric',
                'driver_id'  => 'required|numeric',
            ]);

            $assignedBy = Session::getUserId();

            // Validate and parse optional driver_trip_amount
            $rawAmount = $request->input('driver_trip_amount');
            $driverTripAmount = null;
            if ($rawAmount !== null && trim((string)$rawAmount) !== '') {
                $trimmed = trim((string)$rawAmount);
                $cleaned = str_replace([',', '₹', ' '], '', $trimmed);
                if (!is_numeric($cleaned) || (float)$cleaned < 0) {
                    throw new \RuntimeException("Driver Trip Amount must be a valid non-negative number.");
                }
                $driverTripAmount = round((float)$cleaned, 2);
            }

            $dispatch = $this->dispatchService->assignBooking(
                (int)$data['booking_id'],
                (int)$data['vehicle_id'],
                (int)$data['driver_id'],
                $assignedBy,
                $driverTripAmount
            );

            $notifInfo = $dispatch['notification_info'] ?? null;
            $emailResults = $dispatch['email_results'] ?? [];
            $emailNotes = [];
            if (!empty($emailResults['driver']['success'])) {
                $emailNotes[] = 'driver';
            }
            if (!empty($emailResults['customer']['success'])) {
                $emailNotes[] = 'customer';
            }
            $emailAlertText = !empty($emailNotes) ? ' Email alert dispatched to ' . implode(' and ', $emailNotes) . '.' : '';

            if (!empty($notifInfo['employee_name'])) {
                $recipient = $notifInfo['employee_name'];
                $phone = !empty($notifInfo['employee_phone']) ? " ({$notifInfo['employee_phone']})" : "";
                Session::setFlash('success', "Trip deployment successful! Dispatch Code: {$dispatch['dispatch_code']} — Duty notification dispatched to {$recipient}{$phone}.{$emailAlertText}");
            } else {
                Session::setFlash('success', "Trip deployment successful! Dispatch Code: {$dispatch['dispatch_code']}.{$emailAlertText}");
            }
            return $this->redirect(url("/office/trips/history"));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            $bookingId = (int)$request->input('booking_id');
            return $this->redirect(url("/office/trips/assign" . ($bookingId ? "?booking_id={$bookingId}" : "")));
        }
    }

    /**
     * Display comprehensive Trip History with search, status filters & pagination
     */
    public function history(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $status = trim((string)$request->get('status', ''));
        $fromDate = trim((string)$request->get('from_date', ''));
        $toDate = trim((string)$request->get('to_date', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalRecords = $this->dispatchModel->countHistoryFiltered($search, $status, $fromDate, $toDate);
        $trips = $this->dispatchModel->getPaginatedHistory($search, $status, $fromDate, $toDate, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));

        // Metric summaries
        $totalAll = $this->dispatchModel->count();
        $totalActive = $this->dispatchModel->count("`status` IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')");
        $totalCompleted = $this->dispatchModel->count("`status` = 'completed'");
        $totalCancelled = $this->dispatchModel->count("`status` = 'cancelled'");

        return $this->render('office/trips/history', [
            'trips'          => $trips,
            'totalRecords'   => $totalRecords,
            'totalAll'       => $totalAll,
            'totalActive'    => $totalActive,
            'totalCompleted' => $totalCompleted,
            'totalCancelled' => $totalCancelled,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'search'         => $search,
            'selectedStatus' => $status,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'perPage'        => $perPage,
            'title'          => 'Trip Operations History & Ledger',
        ], 'layouts/office');
    }

    /**
     * Cancel active or pending trip assignment
     */
    public function cancel(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        $reason = trim((string)$request->input('reason', 'Cancelled via office console'));

        try {
            $cancelledBy = Session::getUserId();
            $this->dispatchService->cancelTrip($dispatchId, $cancelledBy, $reason);

            Session::setFlash('success', 'Trip assignment has been cancelled. Vehicle and driver returned to available pool.');
            return $this->redirect(url('/office/trips/history'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/trips/history'));
        }
    }

    /**
     * Live Tracking Console with real OpenStreetMap / Leaflet radar
     */
    public function liveTracking(Request $request): Response
    {
        return $this->render('office/tracking/index', [
            'title' => 'Live Fleet Radar & GPS Tracking',
        ], 'layouts/office');
    }

    /**
     * AJAX Toggle trip execution status (Active -> Aborted/Cancelled)
     */
    public function toggleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $dispatch = $this->dispatchModel->find($id);

        if (!$dispatch) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Trip assignment not found.'], 404);
            }
            Session::setFlash('danger', 'Trip assignment not found.');
            return $this->redirect(url('/office/trips/history'));
        }

        try {
            $currentStatus = $dispatch['status'];
            $cancelledBy = Session::getUserId();

            if (in_array($currentStatus, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'], true)) {
                $this->dispatchService->cancelTrip($id, $cancelledBy, 'Aborted via office dispatch console switch');
                $newStatus = 'cancelled';
                $msg = "Trip #{$id} aborted and returned vehicle & driver to available pool.";

                if ($request->isAjax()) {
                    return $this->json([
                        'success'   => true,
                        'status'    => $newStatus,
                        'is_active' => false,
                        'label'     => 'Cancelled',
                        'message'   => $msg,
                        'metrics'   => [
                            'total'     => $this->dispatchModel->count(),
                            'active'    => $this->dispatchModel->count("`status` IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')"),
                            'completed' => $this->dispatchModel->count("`status` = 'completed'"),
                            'cancelled' => $this->dispatchModel->count("`status` = 'cancelled'"),
                        ],
                    ]);
                }

                Session::setFlash('success', $msg);
                return $this->redirect(url('/office/trips/history'));
            } else {
                $msg = "Trip #{$id} is already {$currentStatus} and cannot be toggled.";
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $msg], 422);
                }
                Session::setFlash('warning', $msg);
                return $this->redirect(url('/office/trips/history'));
            }
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/trips/history'));
        }
    }

    /**
     * Update Driver Trip Amount on existing dispatch
     */
    public function updateAmount(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $rawAmount = $request->input('driver_trip_amount');
            $driverTripAmount = null;
            if ($rawAmount !== null && trim((string)$rawAmount) !== '') {
                $trimmed = trim((string)$rawAmount);
                $cleaned = str_replace([',', '₹', ' '], '', $trimmed);
                if (!is_numeric($cleaned) || (float)$cleaned < 0) {
                    throw new \RuntimeException("Driver Trip Amount must be a valid non-negative number.");
                }
                $driverTripAmount = round((float)$cleaned, 2);
            }

            $updatedBy = Session::getUserId();
            $this->dispatchService->updateDriverTripAmount($id, $driverTripAmount, $updatedBy);

            $amountLabel = $driverTripAmount !== null ? format_currency($driverTripAmount) : 'Not specified';
            $msg = "Driver Trip Amount updated to {$amountLabel}.";

            if ($request->isAjax()) {
                return $this->json([
                    'success'            => true,
                    'message'            => $msg,
                    'driver_trip_amount' => $driverTripAmount,
                    'amount_formatted'   => $amountLabel,
                ]);
            }

            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/trips/history'));
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/trips/history'));
        }
    }

    /**
     * Remind assigned driver to respond to pending trip deployment
     */
    public function remind(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $userId = Session::getUserId() ?: 1;
            $result = $this->dispatchService->remindDriver($id, $userId);

            if ($request->isAjax()) {
                return $this->json([
                    'success'          => true,
                    'message'          => $result['message'],
                    'last_reminded_at' => $result['last_reminded_at'],
                ]);
            }

            Session::setFlash('success', $result['message']);
            return $this->redirect(url('/office/trips/history'));
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::setFlash('warning', $e->getMessage());
            return $this->redirect(url('/office/trips/history'));
        }
    }

    /**
     * Reassign trip to another driver
     */
    public function reassign(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $data = $this->validate($request->all(), [
                'driver_id' => 'required|numeric',
            ]);

            $vehicleId = $request->input('vehicle_id') ? (int)$request->input('vehicle_id') : null;
            $userId = Session::getUserId() ?: 1;

            $dispatch = $this->dispatchService->reassignDriver($id, (int)$data['driver_id'], $vehicleId, $userId);

            $msg = "Trip {$dispatch['dispatch_code']} successfully reassigned! Driver notified. Customer confirmation will be sent upon driver acceptance.";
            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'dispatch' => $dispatch]);
            }

            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/trips/history'));
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/trips/history'));
        }
    }

    /**
     * MODULE 7.8: Trip Evidence & Telemetry Inspection (Office Admin & Dispatcher)
     */
    public function tripEvidence(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $dispatch = $this->dispatchModel->find($id);

        $accept = $request->server('HTTP_ACCEPT', '');
        $wantsHtml = str_contains($accept, 'text/html');
        $isExplicitJson = $request->query('format') === 'json' ||
            $request->query('api') === '1' ||
            (!$wantsHtml && ($request->wantsJson() || $request->isJson()));

        if (!$dispatch) {
            if ($isExplicitJson) {
                return $this->json(['success' => false, 'message' => 'Trip assignment not found.'], 404);
            }
            Session::setFlash('danger', 'Trip assignment not found.');
            return $this->redirect(url('/office/trips/history'));
        }

        $evidenceService = new \App\Services\TripEvidenceService();
        $evidence = $evidenceService->getGroupedEvidence($id);

        // Fetch GPS trail summary
        $pdo = \App\Core\Database::getConnection();
        $stmtGps = $pdo->prepare("SELECT latitude, longitude, speed_kmh, heading_degrees, accuracy_m, recorded_at FROM `gps_logs` WHERE `dispatch_id` = ? ORDER BY id ASC LIMIT 500");
        $stmtGps->execute([$id]);
        $gpsTrail = $stmtGps->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch vehicle & driver info
        $vehicle = $this->vehicleModel->find((int)$dispatch['vehicle_id']);
        $driver = $this->driverModel->find((int)$dispatch['driver_id']);
        $booking = $this->bookingModel->find((int)$dispatch['booking_id']);

        if ($isExplicitJson) {
            return $this->json([
                'success'   => true,
                'dispatch'  => $dispatch,
                'vehicle'   => $vehicle,
                'driver'    => $driver,
                'booking'   => $booking,
                'evidence'  => $evidence,
                'gps_trail' => $gpsTrail,
            ]);
        }

        return $this->render('office/trips/evidence', [
            'dispatch'  => $dispatch,
            'vehicle'   => $vehicle,
            'driver'    => $driver,
            'booking'   => $booking,
            'evidence'  => $evidence,
            'gpsTrail'  => $gpsTrail,
            'title'     => 'Trip Evidence & Telemetry — ' . ($dispatch['dispatch_code'] ?? 'Trip'),
        ], 'layouts/office');
    }
}
