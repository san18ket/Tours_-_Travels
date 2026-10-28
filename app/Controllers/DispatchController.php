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
            $dispatch = $this->dispatchService->assignBooking(
                (int)$data['booking_id'],
                (int)$data['vehicle_id'],
                (int)$data['driver_id'],
                $assignedBy
            );

            Session::setFlash('success', "Trip deployment successful! Dispatch Code: {$dispatch['dispatch_code']}");
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

            Session::setFlash('success', 'Trip assignment has been cancelled. Vehicle and chauffeur returned to available pool.');
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
                $msg = "Trip #{$id} aborted and returned vehicle & chauffeur to available pool.";

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
}
