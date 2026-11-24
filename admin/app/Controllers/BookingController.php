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
        $status = trim((string)$request->input('status', ''));
        $search = trim((string)($request->input('search') ?? $request->input('q') ?? ''));

        $conditions = [];
        $params = [];

        if (!empty($status)) {
            if ($status === 'active') {
                $conditions[] = "b.status IN ('confirmed', 'assigned', 'in_progress')";
            } else {
                $conditions[] = "b.status = ?";
                $params[] = $status;
            }
        }

        $tripCategoryId = (int)$request->input('trip_category_id', 0);
        if ($tripCategoryId > 0) {
            $conditions[] = "b.trip_category_id = ?";
            $params[] = $tripCategoryId;
        }

        if (!empty($search)) {
            $conditions[] = "(b.booking_code LIKE ? OR c.name LIKE ? OR u.name LIKE ? OR c.phone LIKE ? OR u.phone LIKE ? OR t.title LIKE ? OR tc.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
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

        $tripCategoryModel = new \App\Models\TripCategory();
        $tripCategories = $tripCategoryModel->getAllCategories();

        return $this->render('office/bookings/index', [
            'bookings'         => $bookings,
            'stats'            => $stats,
            'selectedStatus'   => $status,
            'selectedCategory' => $tripCategoryId,
            'tripCategories'   => $tripCategories,
            'search'           => $search,
            'sort'             => $sort,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'totalRecords'     => $totalRecords,
            'perPage'          => $perPage,
            'title'            => __('nav.bookings', 'Bookings & Reservations'),
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

        $financeAccountModel = new \App\Models\FinanceAccount();
        $accounts = $financeAccountModel->getActiveAccounts();

        $tripCategoryModel = new \App\Models\TripCategory();
        $tripCategories = $tripCategoryModel->getActiveCategories();

        return $this->render('office/bookings/create', [
            'tours'          => $tours,
            'customers'      => $customers,
            'accounts'       => $accounts,
            'tripCategories' => $tripCategories,
            'title'          => __('dashboard.create_booking', 'Create New Booking'),
        ], 'layouts/office');
    }

    public function store(Request $request): Response
    {
        try {
            $data = $this->validate($request->all(), [
                'customer_id'      => 'required|numeric',
                'trip_category_id' => 'required|numeric',
                'pickup_address'   => 'required|min:5|max:255',
                'drop_address'     => 'required|min:5|max:255',
                'start_date'       => 'required|date',
                'end_date'         => 'required|date',
                'passenger_count'  => 'required|numeric',
            ]);

            // Validate Trip Category existence and status
            $catId = (int)$data['trip_category_id'];
            $tripCategoryModel = new \App\Models\TripCategory();
            $category = $tripCategoryModel->find($catId);
            if (!$category) {
                throw new \App\Core\ValidationException('Selected Trip Category does not exist.');
            }
            if (($category['status'] ?? '') !== 'active') {
                throw new \App\Core\ValidationException('Selected Trip Category is currently inactive and cannot be chosen for new reservations.');
            }
            $data['trip_category_id'] = $catId;

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

            // Parse itemized billing line items (Distance, Daily, Halting, Other Charges)
            $billingItems = [];
            $itemsJson = $request->input('billing_items_json');
            if (!empty($itemsJson) && is_string($itemsJson)) {
                $decoded = json_decode($itemsJson, true);
                if (is_array($decoded)) {
                    $billingItems = $decoded;
                }
            }

            // If not provided as JSON, check individual category inputs
            if (empty($billingItems)) {
                $order = 1;
                // A. Distance Charges
                $distKm = max(0.0, (float)$request->input('billing_distance_km', 0.0));
                $distRate = ($request->input('billing_rate_per_km') !== null && $request->input('billing_rate_per_km') !== '')
                    ? max(0.0, (float)$request->input('billing_rate_per_km'))
                    : null;
                $distAmount = max(0.0, (float)$request->input('billing_distance_amount', 0.0));

                if ($distKm > 0 && $distRate !== null && $distRate > 0 && $distAmount <= 0) {
                    $distAmount = round($distKm * $distRate, 2);
                }
                if ($distAmount > 0 || $distKm > 0) {
                    $desc = "Distance Charges" . ($distKm > 0 ? " (" . number_format($distKm, 2) . " KM)" : "");
                    $billingItems[] = [
                        'charge_category' => 'distance',
                        'description'     => $desc,
                        'quantity'        => $distKm > 0 ? $distKm : 1,
                        'unit'            => 'KM',
                        'unit_rate'       => $distRate,
                        'line_total'      => $distAmount,
                        'is_taxable'      => 1,
                        'display_order'   => $order++,
                    ];
                }

                // B. Daily Charges
                $dailyDays = max(0.0, (float)$request->input('billing_daily_days', 0.0));
                $dailyRate = max(0.0, (float)$request->input('billing_rate_per_day', 0.0));
                $dailyAmount = max(0.0, (float)$request->input('billing_daily_amount', 0.0));
                if ($dailyDays > 0 && $dailyRate > 0 && $dailyAmount <= 0) {
                    $dailyAmount = round($dailyDays * $dailyRate, 2);
                }
                if ($dailyAmount > 0 || $dailyDays > 0) {
                    $desc = "Daily Vehicle Charges (" . (int)$dailyDays . " " . ((int)$dailyDays === 1 ? "Day" : "Days") . ")";
                    $billingItems[] = [
                        'charge_category' => 'daily',
                        'description'     => $desc,
                        'quantity'        => $dailyDays > 0 ? $dailyDays : 1,
                        'unit'            => 'Days',
                        'unit_rate'       => $dailyRate > 0 ? $dailyRate : null,
                        'line_total'      => $dailyAmount,
                        'is_taxable'      => 1,
                        'display_order'   => $order++,
                    ];
                }

                // C. Halting Charges
                $haltDays = max(0.0, (float)$request->input('billing_halting_days', 0.0));
                $haltRate = max(0.0, (float)$request->input('billing_halting_rate', 0.0));
                $haltAmount = max(0.0, (float)$request->input('billing_halting_amount', 0.0));
                if ($haltDays > 0 && $haltRate > 0 && $haltAmount <= 0) {
                    $haltAmount = round($haltDays * $haltRate, 2);
                }
                if ($haltAmount > 0) {
                    $desc = "Halting Charges (" . (int)$haltDays . " " . ((int)$haltDays === 1 ? "Night" : "Nights") . ")";
                    $billingItems[] = [
                        'charge_category' => 'halting',
                        'description'     => $desc,
                        'quantity'        => $haltDays > 0 ? $haltDays : 1,
                        'unit'            => 'Nights',
                        'unit_rate'       => $haltRate > 0 ? $haltRate : null,
                        'line_total'      => $haltAmount,
                        'is_taxable'      => 1,
                        'display_order'   => $order++,
                    ];
                }

                // D. Other Charges
                $otherCharges = $request->input('other_charges', []);
                if (is_string($otherCharges)) {
                    $otherCharges = json_decode($otherCharges, true) ?: [];
                }
                if (is_array($otherCharges)) {
                    foreach ($otherCharges as $oc) {
                        $amt = max(0.0, (float)($oc['amount'] ?? $oc['line_total'] ?? 0.0));
                        if ($amt > 0) {
                            $billingItems[] = [
                                'charge_category' => trim((string)($oc['category'] ?? $oc['charge_category'] ?? 'other')),
                                'description'     => trim((string)($oc['description'] ?? 'Additional Trip Charge')),
                                'quantity'        => max(1.0, (float)($oc['quantity'] ?? 1.0)),
                                'unit'            => trim((string)($oc['unit'] ?? 'Unit')),
                                'unit_rate'       => isset($oc['unit_rate']) && $oc['unit_rate'] !== '' ? max(0.0, (float)$oc['unit_rate']) : null,
                                'line_total'      => $amt,
                                'is_taxable'      => 1,
                                'display_order'   => $order++,
                            ];
                        }
                    }
                }
            }

            // Sanitize, validate and sum line items server-side
            $calculatedSubtotal = 0.0;
            $sanitizedItems = [];
            $displayIdx = 1;
            foreach ($billingItems as $item) {
                $qty = max(0.0, (float)($item['quantity'] ?? 1.0));
                $lineTotal = round(max(0.0, (float)($item['line_total'] ?? 0.0)), 2);
                if ($lineTotal <= 0 && $qty <= 0) {
                    continue;
                }
                $rate = (isset($item['unit_rate']) && $item['unit_rate'] !== null && $item['unit_rate'] !== '')
                    ? max(0.0, (float)$item['unit_rate'])
                    : null;
                $sanitizedItems[] = [
                    'charge_category' => trim((string)($item['charge_category'] ?? 'other')),
                    'description'     => trim((string)($item['description'] ?? 'Trip Service Charge')),
                    'quantity'        => $qty,
                    'unit'            => trim((string)($item['unit'] ?? 'Unit')),
                    'unit_rate'       => $rate,
                    'line_total'      => $lineTotal,
                    'is_taxable'      => !empty($item['is_taxable']) ? 1 : 0,
                    'display_order'   => $displayIdx++,
                ];
                $calculatedSubtotal += $lineTotal;
            }

            if (!empty($sanitizedItems)) {
                $data['custom_subtotal'] = round($calculatedSubtotal, 2);
                $data['billing_items'] = $sanitizedItems;
            } else {
                $data['custom_subtotal'] = max(0.0, (float)$request->input('custom_subtotal', 0.0));
            }

            if ($request->has('discount_percent')) {
                $discountPercent = max(0.0, min(100.0, (float)$request->input('discount_percent', 0.0)));
                $data['discount_amount'] = round(($data['custom_subtotal'] * $discountPercent) / 100, 2);
            } else {
                $data['discount_amount'] = max(0.0, min($data['custom_subtotal'], (float)$request->input('discount_amount', 0.0)));
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

            // Advance payment validation and parameters
            $advanceAmount = round((float)$request->input('advance_amount', 0.0), 2);
            if ($advanceAmount < 0.0) {
                throw new \RuntimeException("Advance payment cannot be a negative amount.");
            }

            $tourId = !empty($data['tour_id']) ? (int)$data['tour_id'] : null;
            $passengers = max(1, (int)($data['passenger_count'] ?? 1));
            $pricing = $this->bookingService->calculatePricing(
                $tourId,
                $passengers,
                $data['custom_subtotal'],
                $data['discount_amount'],
                $data['gst_rate']
            );

            if ($advanceAmount > ($pricing['total_amount'] + 0.01)) {
                throw new \RuntimeException("Advance payment cannot be greater than the booking amount.");
            }

            $data['advance_amount'] = $advanceAmount;
            $data['payment_method'] = $request->input('payment_method', 'cash');
            $data['received_date']  = $request->input('received_date', date('Y-m-d'));
            $data['account_id']     = (int)$request->input('account_id', 0) ?: null;
            $data['transaction_id'] = $request->input('transaction_id');
            $data['payment_notes']  = $request->input('payment_notes');

            $booking = $this->bookingService->createBooking($data);

            // Fetch auto-generated invoice details for friendly flash confirmation
            $invoiceModel = new \App\Models\Invoice();
            $invoice = $invoiceModel->findByBookingId((int)$booking['id']);
            $totalAmount = (float)($invoice['total_amount'] ?? $booking['total_amount']);
            $paidAmount = (float)($invoice['paid_amount'] ?? $advanceAmount);
            $remainingAmount = max(0.0, $totalAmount - $paidAmount);
            $invNum = $invoice['invoice_number'] ?? 'INV';

            $emailStatusNotice = '';
            if (!empty($booking['email_sent'])) {
                if (($booking['email_status'] ?? '') === 'sent') {
                    $emailStatusNotice = ' Confirmation email sent to customer.';
                } elseif (($booking['email_status'] ?? '') === 'pending') {
                    $emailStatusNotice = ' Confirmation email scheduled for delivery.';
                } else {
                    $emailStatusNotice = '';
                }
            } elseif (isset($booking['email_sent']) && !$booking['email_sent'] && !empty($booking['email_error'])) {
                $emailStatusNotice = ' (Confirmation email could not be queued; please check email settings).';
            }

            if ($advanceAmount <= 0.0) {
                $flashMsg = "Booking {$booking['booking_code']} created successfully. Invoice {$invNum} generated. ₹" . number_format($totalAmount, 2) . " remaining (Unpaid)." . $emailStatusNotice;
            } elseif ($remainingAmount <= 0.0) {
                $flashMsg = "Booking {$booking['booking_code']} created successfully. Invoice {$invNum} generated. ₹" . number_format($advanceAmount, 2) . " received in full. Invoice is fully paid." . $emailStatusNotice;
            } else {
                $flashMsg = "Booking {$booking['booking_code']} created successfully. Invoice {$invNum} generated. ₹" . number_format($advanceAmount, 2) . " received. ₹" . number_format($remainingAmount, 2) . " remaining." . $emailStatusNotice;
            }

            Session::setFlash('success', $flashMsg);
            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $flashMsg,
                    'redirect' => url('/office/bookings'),
                ]);
            }
            return $this->redirect(url('/office/bookings'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return $this->redirect(url('/office/bookings/create'));
        }
    }

    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $booking = $this->bookingModel->findWithDetails($id);

        if (!$booking) {
            Session::setFlash('danger', __('booking.not_found', 'Booking not found.'));
            return $this->redirect(url('/office/bookings'));
        }

        // Authorization ownership check for customer
        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$booking['customer_id']);
        }

        $availableVehicles = $this->vehicleModel->getAvailable();
        $availableDrivers = $this->driverModel->getAvailableDrivers();
        $financeAccountModel = new \App\Models\FinanceAccount();
        $accounts = $financeAccountModel->getActiveAccounts();

        return $this->render('office/bookings/show', [
            'booking'           => $booking,
            'availableVehicles' => $availableVehicles,
            'availableDrivers'  => $availableDrivers,
            'accounts'          => $accounts,
            'title'             => "Booking {$booking['booking_code']}",
        ], 'layouts/office');
    }

    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $booking = $this->bookingModel->findWithDetails($id);

        if (!$booking) {
            Session::setFlash('danger', __('booking.not_found', 'Booking not found.'));
            return $this->redirect(url('/office/bookings'));
        }

        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$booking['customer_id']);
        }

        $tours = $this->tourModel->getPublished();
        $customerModel = new \App\Models\Customer();
        $customers = $customerModel->where("`status` = 'active'", [], 'name ASC');
        if (empty($customers)) {
            $customers = $this->userModel->where("`role` = 'customer' AND `status` = 'active'", [], 'name ASC');
        }

        $tripCategoryModel = new \App\Models\TripCategory();
        $tripCategories = $tripCategoryModel->getActiveCategories();
        $currentCatId = !empty($booking['trip_category_id']) ? (int)$booking['trip_category_id'] : null;
        if ($currentCatId && !in_array($currentCatId, array_column($tripCategories, 'id'), true)) {
            $currCat = $tripCategoryModel->find($currentCatId);
            if ($currCat) {
                $tripCategories[] = $currCat;
            }
        }

        return $this->render('office/bookings/edit', [
            'booking'        => $booking,
            'tours'          => $tours,
            'customers'      => $customers,
            'tripCategories' => $tripCategories,
            'title'          => "Edit Booking {$booking['booking_code']}",
        ], 'layouts/office');
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            Session::setFlash('danger', __('booking.not_found', 'Booking not found.'));
            return $this->redirect(url('/office/bookings'));
        }

        if (Session::getUserRole() === 'customer') {
            $this->authorizeOwnership((int)$booking['customer_id']);
        }

        try {
            $input = $request->all();
            $data = $this->validate($input, [
                'pickup_address'   => 'required|min:5|max:255',
                'drop_address'     => 'required|min:5|max:255',
                'start_date'       => 'required|date',
                'end_date'         => 'required|date',
                'passenger_count'  => 'required|numeric',
            ]);

            $catId = (int)$request->input('trip_category_id', 0);
            if (empty($catId)) {
                throw new \App\Core\ValidationException('Please select a Trip Category for this reservation.');
            }
            $tripCategoryModel = new \App\Models\TripCategory();
            $category = $tripCategoryModel->find($catId);
            if (!$category) {
                throw new \App\Core\ValidationException('Selected Trip Category does not exist.');
            }
            if (($category['status'] ?? '') !== 'active' && (int)($booking['trip_category_id'] ?? 0) !== $catId) {
                throw new \App\Core\ValidationException('Selected Trip Category is inactive.');
            }
            $data['trip_category_id'] = $catId;

            if ($request->has('tour_id')) {
                $data['tour_id'] = $request->input('tour_id');
            }
            if ($request->has('pickup_time')) {
                $data['pickup_time'] = $request->input('pickup_time');
            }
            if ($request->has('special_requests')) {
                $data['special_requests'] = $request->input('special_requests');
            }

            $this->bookingService->updateBooking($id, $data);

            $msg = "Booking {$booking['booking_code']} updated successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/bookings/' . $id),
                ]);
            }
            return $this->redirect(url('/office/bookings/' . $id));
        } catch (\App\Core\ValidationException $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return $this->redirect(url('/office/bookings/' . $id . '/edit'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return $this->redirect(url('/office/bookings/' . $id . '/edit'));
        }
    }

    public function updateStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');
        $reason = $request->input('reason');

        try {
            $this->bookingService->updateStatus($id, $status, $reason);
            $msg = "Booking status updated to {$status}.";
            Session::setFlash('success', $msg);
            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/bookings'),
                ]);
            }
            return $this->redirect(url('/office/bookings'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return $this->redirect(url('/office/bookings'));
        }
    }

    /**
     * AJAX Toggle booking status (pending <-> confirmed)
     */
    public function toggleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }
            Session::setFlash('danger', __('booking.not_found', 'Booking not found.'));
            return $this->redirect(url('/office/bookings'));
        }

        try {
            $currentStatus = $booking['status'];
            $targetStatus = ($currentStatus === 'confirmed' || $currentStatus === 'assigned' || $currentStatus === 'in_progress')
                ? 'pending'
                : 'confirmed';

            $this->bookingService->updateStatus($id, $targetStatus, 'Status toggled via office switch button');
            $msg = "Booking #{$booking['booking_code']} status changed to " . ucfirst($targetStatus) . ".";

            if ($request->isAjax()) {
                $isConfirmed = ($targetStatus === 'confirmed');
                $customerId = (Session::getUserRole() === 'customer') ? Session::getUserId() : null;
                $stats = $this->bookingModel->getBookingStatistics($customerId);
                return $this->json([
                    'success'   => true,
                    'status'    => $targetStatus,
                    'is_active' => $isConfirmed,
                    'label'     => ucfirst(str_replace('_', ' ', $targetStatus)),
                    'message'   => $msg,
                    'metrics'   => [
                        'total'       => (int)($stats['total'] ?? 0),
                        'active'      => (int)($stats['active'] ?? 0),
                        'confirmed'   => (int)($stats['active'] ?? 0),
                        'pending'     => (int)($stats['pending'] ?? 0),
                        'completed'   => (int)($stats['completed'] ?? 0),
                        'cancelled'   => (int)($stats['cancelled'] ?? 0),
                        'revenue'     => (float)($stats['revenue'] ?? 0),
                    ],
                ]);
            }

            $msg = "Booking updated successfully.";
            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/bookings'));
        } catch (\Throwable $e) {
            $friendlyErr = \App\Core\ErrorMessageTranslator::translate($e, 'booking', 'update');
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $friendlyErr], 400);
            }
            Session::setFlash('danger', $friendlyErr);
            return $this->redirect(url('/office/bookings'));
        }
    }

    /**
     * Delete a booking manifest, linked dispatch assignments, and dissociate invoices
     */
    public function delete(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Booking manifest not found.'], 404);
            }
            Session::setFlash('danger', 'Booking manifest not found.');
            return $this->redirect(url('/office/bookings'));
        }

        // Server-side Safe Deletion Rules Check
        $safeDeletionService = new \App\Services\SafeDeletionService();
        $check = $safeDeletionService->canDeleteBooking($id, $booking);
        if (!$check['allowed']) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $check['message']], 400);
            }
            Session::setFlash('danger', $check['message']);
            return $this->redirect(url('/office/bookings'));
        }

        try {
            $pdo = \App\Core\Database::getConnection();
            $pdo->beginTransaction();

            // 0. Clean settled driver trip payments for this booking / dispatches to prevent FK RESTRICT failure
            $stmtDtp = $pdo->prepare("
                DELETE FROM `driver_trip_payments` 
                WHERE `booking_id` = ? 
                   OR `dispatch_id` IN (SELECT `id` FROM `dispatches` WHERE `booking_id` = ?)
            ");
            $stmtDtp->execute([$id, $id]);

            // 1. Delete linked dispatches
            $stmtDisp = $pdo->prepare("DELETE FROM `dispatches` WHERE `booking_id` = ?");
            $stmtDisp->execute([$id]);

            // 2. Dissociate linked invoices
            $stmtInv = $pdo->prepare("UPDATE `invoices` SET `booking_id` = NULL WHERE `booking_id` = ?");
            $stmtInv->execute([$id]);

            // 3. Delete the booking record
            $this->bookingModel->delete($id);

            $pdo->commit();

            // 4. Record audit log
            try {
                $audit = new \App\Models\AuditLog();
                $audit->record(
                    'booking_deleted',
                    'bookings',
                    $id,
                    [
                        'booking_code' => $booking['booking_code'] ?? null,
                        'customer_id'  => $booking['customer_id'] ?? null,
                        'total_amount' => $booking['total_amount'] ?? null,
                        'deleted_by'   => Session::getUserId(),
                    ],
                    null
                );
            } catch (\Throwable $ae) {
                // Ignore audit logging failure
            }

            $msg = "Booking deleted successfully.";
            Session::setFlash('success', $msg);

            $customerId = (Session::getUserRole() === 'customer') ? Session::getUserId() : null;
            $stats = $this->bookingModel->getBookingStatistics($customerId);
            $metrics = [
                'total'       => (int)($stats['total'] ?? 0),
                'active'      => (int)($stats['active'] ?? 0),
                'confirmed'   => (int)($stats['active'] ?? 0),
                'pending'     => (int)($stats['pending'] ?? 0),
                'completed'   => (int)($stats['completed'] ?? 0),
                'cancelled'   => (int)($stats['cancelled'] ?? 0),
                'revenue'     => (float)($stats['revenue'] ?? 0),
            ];

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'metrics'  => $metrics,
                    'redirect' => url('/office/bookings'),
                ]);
            }

            return $this->redirect(url('/office/bookings'));
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            \App\Core\Logger::error('Booking delete error: ' . $e->getMessage(), ['booking_id' => $id]);
            $friendlyErr = \App\Core\ErrorMessageTranslator::translate($e, 'booking', 'delete');
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $friendlyErr], 400);
            }
            Session::setFlash('danger', $friendlyErr);
            return $this->redirect(url('/office/bookings'));
        }
    }
}
