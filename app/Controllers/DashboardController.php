<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;
    private Invoice $invoiceModel;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
        $this->invoiceModel = new Invoice();
        $this->auditLogModel = new AuditLog();
    }

    public function index(Request $request): Response
    {
        $totalBookings = $this->bookingModel->count();
        $activeTrips = $this->dispatchModel->count("`status` IN ('driver_accepted', 'en_route_pickup', 'trip_started')");
        $availableFleet = $this->vehicleModel->count("`status` = 'available'");
        $driversOnDuty = $this->driverModel->count("`duty_status` IN ('on_duty', 'on_trip')");

        // Financial totals
        $pdo = \App\Core\Database::getConnection();
        $revStmt = $pdo->query("SELECT SUM(total_amount) as total_rev, SUM(paid_amount) as total_paid FROM invoices");
        $revData = $revStmt->fetch();
        $totalRevenue = (float)($revData['total_rev'] ?? 0.0);
        $totalCollected = (float)($revData['total_paid'] ?? 0.0);
        $pendingRevenue = max(0.0, $totalRevenue - $totalCollected);

        // Recent Bookings & Dispatches
        $recentBookings = $this->bookingModel->getAllDetailed('', []);
        $recentBookings = array_slice($recentBookings, 0, 5);

        $activeDispatches = $this->dispatchModel->getAllWithDetails();
        $activeDispatches = array_slice($activeDispatches, 0, 5);

        $recentAudits = $this->auditLogModel->getRecent(6);

        return $this->render('office/dashboard', [
            'totalBookings'    => $totalBookings,
            'activeTrips'      => $activeTrips,
            'availableFleet'   => $availableFleet,
            'driversOnDuty'    => $driversOnDuty,
            'totalRevenue'     => $totalRevenue,
            'totalCollected'   => $totalCollected,
            'pendingRevenue'   => $pendingRevenue,
            'recentBookings'   => $recentBookings,
            'activeDispatches' => $activeDispatches,
            'recentAudits'     => $recentAudits,
            'title'            => 'Command Operations Dashboard',
        ], 'layouts/office');
    }
}
