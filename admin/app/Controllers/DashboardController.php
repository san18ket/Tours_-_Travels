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
use App\Models\Customer;
use App\Core\Database;
use DateTime;
use PDO;

class DashboardController extends Controller
{
    private Booking $bookingModel;
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;
    private Invoice $invoiceModel;
    private AuditLog $auditLogModel;
    private Customer $customerModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
        $this->invoiceModel = new Invoice();
        $this->auditLogModel = new AuditLog();
        $this->customerModel = new Customer();
    }

    public function index(Request $request): Response
    {
        $pdo = Database::getConnection();

        // 1. Core Operational KPIs (Actual Database Records - Optimized Single Aggregates)
        $totalBookings = (int)$this->bookingModel->count();
        $totalCustomers = (int)$this->customerModel->count();

        // Single aggregate query for fleet vehicles
        $vStmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN `status` = 'available' THEN 1 ELSE 0 END), 0) as available,
                COALESCE(SUM(CASE WHEN `status` = 'on_trip' THEN 1 ELSE 0 END), 0) as on_trip,
                COALESCE(SUM(CASE WHEN `status` = 'maintenance' THEN 1 ELSE 0 END), 0) as maintenance
            FROM `vehicles`
        ");
        $vData = $vStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalVehicles = (int)($vData['total'] ?? 0);
        $availableFleet = (int)($vData['available'] ?? 0);
        $onTripFleet = (int)($vData['on_trip'] ?? 0);
        $maintenanceFleet = (int)($vData['maintenance'] ?? 0);
        $fleetReadinessRate = $totalVehicles > 0 ? (int)round(($availableFleet / $totalVehicles) * 100) : 0;

        // Single aggregate query for driver profiles
        $dStmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN `duty_status` IN ('on_duty', 'on_trip') THEN 1 ELSE 0 END), 0) as on_duty_or_trip,
                COALESCE(SUM(CASE WHEN `duty_status` = 'on_duty' THEN 1 ELSE 0 END), 0) as on_duty
            FROM `driver_profiles`
        ");
        $dData = $dStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalDrivers = (int)($dData['total'] ?? 0);
        $driversOnDuty = (int)($dData['on_duty_or_trip'] ?? 0);
        $driversAvailable = (int)($dData['on_duty'] ?? 0);

        // 2. Real Revenue Summary for Default Period ('this_month')
        $revenueData = $this->getRevenuePeriodData($pdo, 'this_month');
        $totalRevenue = $revenueData['revenue'];
        $totalCollected = $revenueData['collected'];
        $pendingRevenue = $revenueData['pending'];
        $onlinePayPercent = $revenueData['online_pay_percent'];
        $pendingPayPercent = $revenueData['pending_pay_percent'];
        $revenueGrowth = $revenueData['growth'];
        $revenueGrowthDir = $revenueData['growth_direction'];

        // 3. KPI Trends (Real month-over-month comparison)
        $kpiTrends = $this->calculateKpiTrends($pdo);

        // 4. Dynamic Top Destinations (Real Database Records)
        $topDestinations = $this->getTopDestinations($pdo, 5);

        // 5. Dynamic Upcoming Trips (Real Database Records)
        $upcomingTrips = $this->getUpcomingTrips($pdo, 4);

        // 6. Dynamic Recent Bookings (Real Database Records)
        $recentBookings = $this->getRecentBookings($pdo, 5);

        // 7. Dynamic Booking Overview Chart (Real 8-Month Data)
        $bookingOverview = $this->getBookingOverviewData($pdo, '8m');

        // 8. Dynamic Bookings by Vehicle Type (Real Fleet/Dispatch Breakdown)
        $vehicleBreakdown = $this->getVehicleBreakdown($pdo);

        return $this->render('office/dashboard', [
            'totalBookings'        => $totalBookings,
            'totalCustomers'       => $totalCustomers,
            'totalVehicles'        => $totalVehicles,
            'totalRevenue'         => $totalRevenue,
            'totalCollected'       => $totalCollected,
            'pendingRevenue'       => $pendingRevenue,
            'availableFleet'       => $availableFleet,
            'onTripFleet'          => $onTripFleet,
            'maintenanceFleet'     => $maintenanceFleet,
            'fleetReadinessRate'   => $fleetReadinessRate,
            'totalDrivers'         => $totalDrivers,
            'driversOnDuty'        => $driversOnDuty,
            'driversAvailable'     => $driversAvailable,
            'kpiTrends'            => $kpiTrends,
            'topDestinations'      => $topDestinations,
            'upcomingTrips'        => $upcomingTrips,
            'recentBookings'       => $recentBookings,
            'bookingOverview'      => $bookingOverview,
            'vehicleBreakdown'     => $vehicleBreakdown,
            'revenueData'          => $revenueData,
            'onlinePayPercent'     => $onlinePayPercent,
            'pendingPayPercent'    => $pendingPayPercent,
            'revenueGrowth'        => $revenueGrowth,
            'revenueGrowthDir'     => $revenueGrowthDir,
            'title'                => 'Executive Command Dashboard',
        ], 'layouts/office');
    }

    /**
     * AJAX endpoint to dynamically fetch Revenue Summary for a chosen period
     */
    public function revenueSummary(Request $request): Response
    {
        $period = (string)($request->query('period') ?? 'this_month');
        $pdo = Database::getConnection();
        $data = $this->getRevenuePeriodData($pdo, $period);
        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * AJAX endpoint to dynamically fetch Booking Overview trend for a chosen period
     */
    public function bookingOverview(Request $request): Response
    {
        $period = (string)($request->query('period') ?? '8m');
        $pdo = Database::getConnection();
        $data = $this->getBookingOverviewData($pdo, $period);
        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Calculate Top Destinations based on actual booking and tour data
     */
    private function getTopDestinations(PDO $pdo, int $limit = 5): array
    {
        $stmt = $pdo->prepare("
            SELECT 
                destination,
                COUNT(*) as bookings_count
            FROM (
                SELECT 
                    TRIM(COALESCE(
                        NULLIF(t.destination_city, ''),
                        NULLIF(b.drop_address, ''),
                        NULLIF(t.title, '')
                    )) as destination
                FROM bookings b
                LEFT JOIN tours t ON b.tour_id = t.id
                WHERE b.status != 'cancelled'
            ) sub
            WHERE destination IS NOT NULL AND destination != '' AND destination != '0'
            GROUP BY destination
            ORDER BY bookings_count DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($rows)) {
            return [];
        }

        $maxCount = (int)$rows[0]['bookings_count'];
        $results = [];
        $rank = 1;

        foreach ($rows as $row) {
            $cnt = (int)$row['bookings_count'];
            $pct = $maxCount > 0 ? (int)round(($cnt / $maxCount) * 100) : 0;
            $results[] = [
                'rank'     => $rank++,
                'name'     => ucwords($row['destination']),
                'bookings' => $cnt,
                'progress' => max(10, min(100, $pct)),
            ];
        }

        return $results;
    }

    /**
     * Fetch upcoming trips based on real database records
     */
    private function getUpcomingTrips(PDO $pdo, int $limit = 4): array
    {
        $stmt = $pdo->prepare("
            SELECT 
                b.id,
                b.booking_code,
                b.start_date,
                b.end_date,
                b.pickup_address,
                b.drop_address,
                b.passenger_count,
                b.status,
                COALESCE(c.name, 'Guest Traveler') as customer_name,
                t.title as tour_title,
                t.duration_days,
                t.duration_nights
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            LEFT JOIN customers c ON b.customer_profile_id = c.id
            WHERE b.start_date >= CURDATE()
              AND b.status NOT IN ('cancelled', 'completed')
              AND b.start_date != '0000-00-00'
            ORDER BY b.start_date ASC, b.pickup_time ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        foreach ($rows as $b) {
            $startDate = $b['start_date'];
            $day = date('d', strtotime($startDate));
            $month = strtoupper(date('M', strtotime($startDate)));

            $title = !empty($b['tour_title']) 
                ? $b['tour_title'] 
                : (!empty($b['drop_address']) ? ucwords($b['drop_address']) . ' Journey' : 'Booking #' . $b['booking_code']);

            if (!empty($b['duration_days'])) {
                $duration = $b['duration_days'] . ' Days' . (!empty($b['duration_nights']) ? ' / ' . $b['duration_nights'] . ' Nights' : '');
            } elseif (!empty($b['end_date']) && $b['end_date'] !== '0000-00-00' && strtotime($b['end_date']) > strtotime($b['start_date'])) {
                $days = (int)ceil((strtotime($b['end_date']) - strtotime($b['start_date'])) / 86400) + 1;
                $duration = "{$days} Days";
            } else {
                $duration = '1 Day Tour';
            }

            $results[] = [
                'id'         => (int)$b['id'],
                'code'       => $b['booking_code'],
                'day'        => $day,
                'month'      => $month,
                'title'      => $title,
                'duration'   => $duration,
                'passengers' => max(1, (int)$b['passenger_count']),
                'customer'   => $b['customer_name'],
                'status'     => $b['status'],
            ];
        }

        return $results;
    }

    /**
     * Fetch recent bookings based on real database records
     */
    private function getRecentBookings(PDO $pdo, int $limit = 5): array
    {
        $stmt = $pdo->prepare("
            SELECT 
                b.id,
                b.booking_code,
                b.start_date,
                b.end_date,
                b.pickup_address,
                b.drop_address,
                b.total_amount,
                b.status,
                b.created_at,
                COALESCE(c.name, 'Guest Traveler') as customer_name,
                t.title as tour_title
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            LEFT JOIN customers c ON b.customer_profile_id = c.id
            ORDER BY b.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        foreach ($rows as $b) {
            $route = '';
            if (!empty($b['pickup_address']) && !empty($b['drop_address'])) {
                $route = ucwords($b['pickup_address']) . ' → ' . ucwords($b['drop_address']);
            } elseif (!empty($b['drop_address'])) {
                $route = ucwords($b['drop_address']);
            } else {
                $route = 'Custom Itinerary';
            }

            $dateDisplay = (!empty($b['start_date']) && $b['start_date'] !== '0000-00-00')
                ? date('j M Y', strtotime($b['start_date']))
                : 'Date Pending';

            $results[] = [
                'id'            => (int)$b['id'],
                'booking_code'  => $b['booking_code'],
                'title'         => !empty($b['tour_title']) ? $b['tour_title'] : $route,
                'customer'      => $b['customer_name'],
                'customer_name' => $b['customer_name'],
                'route_or_dest' => $route,
                'date'          => $dateDisplay,
                'total_amount'  => (float)$b['total_amount'],
                'status'        => strtolower($b['status'] ?? 'pending'),
                'icon'          => 'fa-route',
            ];
        }

        return $results;
    }

    /**
     * Calculate Revenue metrics dynamically for a given period
     */
    private function getRevenuePeriodData(PDO $pdo, string $period = 'this_month'): array
    {
        $now = new DateTime('now');
        switch ($period) {
            case 'last_month':
                $start = (clone $now)->modify('first day of last month')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last day of last month')->setTime(23, 59, 59);
                $prevStart = (clone $start)->modify('-1 month');
                $prevEnd = (clone $end)->modify('-1 month');
                $periodLabel = 'vs previous month';
                break;
            case 'this_quarter':
                $curQuarter = ceil($now->format('n') / 3);
                $startMonth = ($curQuarter - 1) * 3 + 1;
                $start = new DateTime($now->format('Y') . "-{$startMonth}-01 00:00:00");
                $end = (clone $start)->modify('+3 months -1 second');
                $prevStart = (clone $start)->modify('-3 months');
                $prevEnd = (clone $end)->modify('-3 months');
                $periodLabel = 'vs last quarter';
                break;
            case 'this_year':
                $start = new DateTime($now->format('Y') . "-01-01 00:00:00");
                $end = new DateTime($now->format('Y') . "-12-31 23:59:59");
                $prevStart = (clone $start)->modify('-1 year');
                $prevEnd = (clone $end)->modify('-1 year');
                $periodLabel = 'vs last year';
                break;
            case 'this_month':
            default:
                $start = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
                $end = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
                $prevStart = (clone $start)->modify('-1 month');
                $prevEnd = (clone $end)->modify('-1 month');
                $periodLabel = 'vs last month';
                break;
        }

        $sStr = $start->format('Y-m-d H:i:s');
        $eStr = $end->format('Y-m-d H:i:s');
        $sDate = $start->format('Y-m-d');
        $eDate = $end->format('Y-m-d');

        // Billed / Generated Revenue
        $stmtInv = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(paid_amount), 0) as paid FROM invoices WHERE ((issued_at BETWEEN ? AND ?) OR (issued_at IS NULL AND created_at BETWEEN ? AND ?))");
        $stmtInv->execute([$sStr, $eStr, $sStr, $eStr]);
        $invData = $stmtInv->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmtBk = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status != 'cancelled' AND ((created_at BETWEEN ? AND ?) OR (start_date BETWEEN ? AND ?))");
        $stmtBk->execute([$sStr, $eStr, $sDate, $eDate]);
        $bkTotal = (float)$stmtBk->fetchColumn();

        $stmtPay = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND paid_at BETWEEN ? AND ?");
        $stmtPay->execute([$sStr, $eStr]);
        $payPaid = (float)$stmtPay->fetchColumn();

        $revenue = max((float)($invData['total'] ?? 0), $bkTotal);
        $collected = max($payPaid, (float)($invData['paid'] ?? 0));
        $pending = max(0.0, $revenue - $collected);

        // Previous Period for Growth Comparison
        $psStr = $prevStart->format('Y-m-d H:i:s');
        $peStr = $prevEnd->format('Y-m-d H:i:s');
        $psDate = $prevStart->format('Y-m-d');
        $peDate = $prevEnd->format('Y-m-d');

        $stmtPrevInv = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE ((issued_at BETWEEN ? AND ?) OR (issued_at IS NULL AND created_at BETWEEN ? AND ?))");
        $stmtPrevInv->execute([$psStr, $peStr, $psStr, $peStr]);
        $prevInv = (float)$stmtPrevInv->fetchColumn();

        $stmtPrevBk = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status != 'cancelled' AND ((created_at BETWEEN ? AND ?) OR (start_date BETWEEN ? AND ?))");
        $stmtPrevBk->execute([$psStr, $peStr, $psDate, $peDate]);
        $prevBk = (float)$stmtPrevBk->fetchColumn();

        $prevRevenue = max($prevInv, $prevBk);

        $growth = 0;
        $growthDirection = 'neutral';
        if ($prevRevenue > 0) {
            $growth = (int)round((($revenue - $prevRevenue) / $prevRevenue) * 100);
            $growthDirection = $growth >= 0 ? 'up' : 'down';
        } elseif ($revenue > 0) {
            $growth = 100;
            $growthDirection = 'up';
        }

        // Proportions
        $onlinePayPercent = 0;
        $pendingPayPercent = 0;
        if ($revenue > 0) {
            $onlinePayPercent = (int)round(($collected / $revenue) * 100);
            $pendingPayPercent = max(0, 100 - $onlinePayPercent);
        }

        // Generate 5 curve intervals for mini chart
        $curveLabels = [];
        $curvePoints = [];
        $stepSeconds = ($end->getTimestamp() - $start->getTimestamp()) / 4;
        for ($i = 0; $i < 5; $i++) {
            $ptTime = $start->getTimestamp() + ($stepSeconds * $i);
            $curveLabels[] = date('j M', (int)$ptTime);
            $subEnd = date('Y-m-d H:i:s', (int)$ptTime);
            $subStart = date('Y-m-d H:i:s', (int)max($start->getTimestamp(), $ptTime - $stepSeconds));
            
            $stmtPt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status != 'cancelled' AND ((created_at BETWEEN ? AND ?) OR (start_date BETWEEN ? AND ?))");
            $stmtPt->execute([$subStart, $subEnd, substr($subStart, 0, 10), substr($subEnd, 0, 10)]);
            $curvePoints[] = round((float)$stmtPt->fetchColumn(), 2);
        }

        return [
            'period'              => $period,
            'period_label'        => $periodLabel,
            'total'               => $revenue,
            'revenue'             => $revenue,
            'formatted'           => format_currency($revenue),
            'revenue_formatted'   => format_currency($revenue),
            'collected'           => $collected,
            'collected_formatted' => format_currency($collected),
            'pending'             => $pending,
            'pending_formatted'   => format_currency($pending),
            'online_pct'          => $onlinePayPercent,
            'online_pay_percent'  => $onlinePayPercent,
            'pending_pct'         => $pendingPayPercent,
            'pending_pay_percent' => $pendingPayPercent,
            'growth'              => abs($growth),
            'growth_direction'    => $growthDirection,
            'dir'                 => $growthDirection,
            'curve_labels'        => $curveLabels,
            'curve_points'        => $curvePoints,
        ];
    }

    /**
     * Calculate month-over-month growth trends for KPI cards (Optimized single aggregates)
     */
    private function calculateKpiTrends(PDO $pdo): array
    {
        // Combined Bookings trend (single query for this month and last month)
        $bStmt = $pdo->query("
            SELECT 
                COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END), 0) as this_month,
                COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END), 0) as last_month
            FROM bookings 
            WHERE created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
        ");
        $bData = $bStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $bThis = (int)($bData['this_month'] ?? 0);
        $bLast = (int)($bData['last_month'] ?? 0);
        $bGrowth = $bLast > 0 ? (int)round((($bThis - $bLast) / $bLast) * 100) : ($bThis > 0 ? 100 : 0);

        // Combined Customers trend (single query for this month and last month)
        $cStmt = $pdo->query("
            SELECT 
                COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END), 0) as this_month,
                COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END), 0) as last_month
            FROM customers 
            WHERE created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
        ");
        $cData = $cStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $cThis = (int)($cData['this_month'] ?? 0);
        $cLast = (int)($cData['last_month'] ?? 0);
        $cGrowth = $cLast > 0 ? (int)round((($cThis - $cLast) / $cLast) * 100) : ($cThis > 0 ? 100 : 0);

        return [
            'bookings'  => ['growth' => abs($bGrowth), 'dir' => $bGrowth >= 0 ? 'up' : 'down'],
            'customers' => ['growth' => abs($cGrowth), 'dir' => $cGrowth >= 0 ? 'up' : 'down'],
        ];
    }

    /**
     * Dynamic Booking Overview Line Chart (Real monthly counts - Optimized single grouped query)
     */
    private function getBookingOverviewData(PDO $pdo, string $period = '8m'): array
    {
        $numMonths = match($period) {
            '12m'   => 12,
            '6m'    => 6,
            '30d'   => 1,
            default => 8,
        };

        $labels = [];
        $data = [];

        if ($period === '30d') {
            // Group by 5-day intervals or weeks
            for ($i = 4; $i >= 0; $i--) {
                $dayStart = date('Y-m-d 00:00:00', strtotime("-".($i * 6)." days"));
                $dayEnd = date('Y-m-d 23:59:59', strtotime("-".max(0, ($i * 6 - 5))." days"));
                $labels[] = date('j M', strtotime($dayStart));
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE (created_at BETWEEN ? AND ?) OR (start_date BETWEEN ? AND ?)");
                $stmt->execute([$dayStart, $dayEnd, substr($dayStart, 0, 10), substr($dayEnd, 0, 10)]);
                $data[] = (int)$stmt->fetchColumn();
            }
        } else {
            // Fetch all monthly counts in ONE single grouped query instead of running queries in a loop!
            $oldestTime = strtotime("-" . ($numMonths - 1) . " month");
            $oldestDate = date('Y-m-01 00:00:00', $oldestTime);
            $stmt = $pdo->prepare("
                SELECT DATE_FORMAT(COALESCE(start_date, created_at), '%Y-%m') as ym, COUNT(*) as cnt 
                FROM bookings 
                WHERE (created_at >= ? OR start_date >= ?)
                GROUP BY ym
            ");
            $stmt->execute([$oldestDate, substr($oldestDate, 0, 10)]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $monthlyCounts = [];
            foreach ($rows as $r) {
                if (!empty($r['ym'])) {
                    $monthlyCounts[$r['ym']] = (int)$r['cnt'];
                }
            }

            for ($i = $numMonths - 1; $i >= 0; $i--) {
                $mTime = strtotime("-{$i} month");
                $labels[] = date('M', $mTime);
                $ym = date('Y-m', $mTime);
                $data[] = (int)($monthlyCounts[$ym] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }

    /**
     * Dynamic vehicle breakdown by category from actual vehicles & dispatches
     */
    private function getVehicleBreakdown(PDO $pdo): array
    {
        $stmt = $pdo->query("
            SELECT 
                v.vehicle_type,
                COUNT(*) as count
            FROM dispatches d
            JOIN vehicles v ON d.vehicle_id = v.id
            GROUP BY v.vehicle_type
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($rows)) {
            $stmt = $pdo->query("SELECT vehicle_type, COUNT(*) as count FROM vehicles GROUP BY vehicle_type");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        if (empty($rows)) {
            return [];
        }

        $total = array_sum(array_column($rows, 'count'));
        $palette = ['#176B5B', '#D6B77A', '#218765', '#4788A8', '#C78A35', '#7890AA'];
        $results = [];
        $idx = 0;

        foreach ($rows as $r) {
            $type = ucwords(str_replace('_', ' ', (string)$r['vehicle_type']));
            $cnt = (int)$r['count'];
            $pct = $total > 0 ? (int)round(($cnt / $total) * 100) : 0;
            $results[$type] = [
                'count'   => $cnt,
                'percent' => $pct,
                'color'   => $palette[$idx % count($palette)],
            ];
            $idx++;
        }

        return $results;
    }
}
