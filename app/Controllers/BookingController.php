<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Services\BookingService;
use Throwable;

class BookingController extends Controller
{
    private Booking $bookingModel;
    private Tour $tourModel;
    private User $userModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->tourModel = new Tour();
        $this->userModel = new User();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->bookingService = new BookingService();
    }

    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $search = $request->input('q');

        $conditions = [];
        $params = [];

        if ($status) {
            $conditions[] = "b.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $conditions[] = "(b.booking_code LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // If customer, show only their bookings!
        if (Session::getUserRole() === 'customer') {
            $conditions[] = "b.customer_id = ?";
            $params[] = Session::getUserId();
        }

        $filterStr = implode(' AND ', $conditions);
        $page = max(1, (int)$request->input('page', 1));
        $allowedPerPage = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 100];
        $perPageRaw = (int)$request->input('per_page', 10);
        $perPage = in_array($perPageRaw, $allowedPerPage, true) ? $perPageRaw : 10;

        $sort = (string)$request->input('sort', 'created_desc');
        $orderByMap = [
            'created_desc' => 'b.id DESC',
            'created_asc'  => 'b.id ASC',
            'client_asc'   => 'COALESCE(c.name, u.name) ASC',
            'client_desc'  => 'COALESCE(c.name, u.name) DESC',
            'date_asc'     => 'b.start_date ASC',
            'date_desc'    => 'b.start_date DESC',
        ];
        $orderBy = $orderByMap[$sort] ?? 'b.id DESC';

        $totalRecords = $this->bookingModel->countFilteredDetailed($filterStr, $params);
        $bookings = $this->bookingModel->getPaginatedDetailed($filterStr, $params, $page, $perPage, $orderBy);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));

        $customerId = (Session::getUserRole() === 'customer') ? Session::getUserId() : null;
        $stats = $this->bookingModel->getBookingStatistics($customerId);

        return $this->render('office/bookings/index', [
            'bookings'       => $bookings,
            'stats'          => $stats,
            'selectedStatus' => $status,
            'search'         => $search,
            'sort'           => $sort,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'totalRecords'   => $totalRecords,
            'perPage'        => $perPage,
            'title'          => 'Bookings & Reservations',
        ], 'layouts/office');
    }

    public function create(Request $request): Response
    {
        $tours = $this->tourModel->getPublished();
        $customerModel = new \App\Models\Customer();
        $customers = $customerModel->where("`status` = 'active'", [], 'name ASC');
        if (empty($customers)) {
            $customers = $this->userModel->where("`role` = 'customer' AND `status` = 'active'", [], 'name ASC');
        }

        return $this->render('office/bookings/create', [
            'tours'     => $tours,
            'customers' => $customers,
            'title'     => 'Create New Booking',
        ], 'layouts/office');
    }

    public function store(Request $request): Response
    {
        try {
            $data = $this->validate($request->all(), [
                'customer_id'     => 'required|numeric',
                'pickup_address'  => 'required|min:5|max:255',
                'drop_address'    => 'required|min:5|max:255',
                'start_date'      => 'required|date',
                'end_date'        => 'required|date',
                'passenger_count' => 'required|numeric',
            ]);

            // Resolve whether customer_id refers to a Customer profile or legacy user
            $selectedId = (int)$data['customer_id'];
            $customerModel = new \App\Models\Customer();
            $customerProfile = $customerModel->find($selectedId);

            if ($customerProfile) {
                $data['customer_profile_id'] = (int)$customerProfile['id'];
                $data['customer_id'] = !empty($customerProfile['user_id']) ? (int)$customerProfile['user_id'] : null;
            } else {
                $data['customer_id'] = $selectedId;
                $data['customer_profile_id'] = null;
            }

            // If customer user is creating, enforce customer_id to self
            if (Session::getUserRole() === 'customer') {
                $data['customer_id'] = Session::getUserId();
            }

            $data['tour_id'] = $request->input('tour_id');
            $data['pickup_time'] = $request->input('pickup_time', '09:00:00');
            $data['special_requests'] = $request->input('special_requests');
            $data['custom_subtotal'] = (float)$request->input('custom_subtotal', 0.0);
            if ($request->has('discount_percent')) {
                $discountPercent = max(0.0, min(100.0, (float)$request->input('discount_percent', 0.0)));
                $data['discount_amount'] = round(($data['custom_subtotal'] * $discountPercent) / 100, 2);
            } else {
                $data['discount_amount'] = (float)$request->input('discount_amount', 0.0);
            }
            $data['idempotency_token'] = $request->input('idempotency_token') ?: bin2hex(random_bytes(16));

            // Dynamic Statutory GST settings
            $hasGstFlag = $request->has('include_gst');
            if ($hasGstFlag) {
                $val = $request->input('include_gst');
                $data['include_gst'] = ($val === '1' || $val === 'on' || $val === true);
            } else {
                $data['include_gst'] = false;
            }
            $data['gst_rate'] = $data['include_gst'] ? (float)$request->input('gst_rate', 5.0) : 0.0;

            $booking = $this->bookingService->createBooking($data);

            Session::setFlash('success', "Booking {$booking['booking_code']} created successfully.");
            return $this->redirect(url("/office/bookings/{$booking['id']}"));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/bookings/create'));
        }
    }

    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $booking = $this->bookingModel->findWithDetails($id);

        if (!$booking) {
            Session::setFlash('danger', 'Booking not found.');
            return $this->redirect(url('/office/bookings'));
        }

        // Authorization ownership check for customer
        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$booking['customer_id']);
        }

        $availableVehicles = $this->vehicleModel->getAvailable();
        $availableDrivers = $this->driverModel->getAvailableDrivers();

        return $this->render('office/bookings/show', [
            'booking'           => $booking,
            'availableVehicles' => $availableVehicles,
            'availableDrivers'  => $availableDrivers,
            'title'             => "Booking {$booking['booking_code']}",
        ], 'layouts/office');
    }

    public function updateStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');
        $reason = $request->input('reason');

        try {
            $this->bookingService->updateStatus($id, $status, $reason);
            Session::setFlash('success', "Booking status updated to {$status}.");
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url("/office/bookings/{$id}"));
    }
}
