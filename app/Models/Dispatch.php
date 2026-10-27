<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Dispatch extends Model
{
    protected string $table = 'dispatches';
    protected array $fillable = [
        'dispatch_code', 'booking_id', 'vehicle_id', 'driver_id', 'assigned_by',
        'status', 'start_odometer', 'end_odometer', 'start_time', 'end_time', 'driver_notes'
    ];

    public function findByBookingId(int $bookingId): ?array
    {
        return $this->findBy('booking_id', $bookingId);
    }

    public function getActiveAssignmentForDriver(int $driverProfileId): ?array
    {
        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       b.pickup_time, b.passenger_count, b.special_requests,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       COALESCE(c_cust.phone, u_cust.phone) as customer_phone,
                       v.vehicle_number, v.make_model, v.odometer_km as current_vehicle_odometer,
                       t.title as tour_title
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                WHERE d.driver_id = ? AND d.status IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')
                ORDER BY d.id DESC
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$driverProfileId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getDriverHistory(int $driverProfileId): array
    {
        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       v.vehicle_number, v.make_model,
                       COALESCE(c_cust.name, u_cust.name) as customer_name
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE d.driver_id = ?
                ORDER BY d.id DESC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$driverProfileId]);
        return $stmt->fetchAll();
    }

    public function getAllWithDetails(): array
    {
        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       v.vehicle_number, v.make_model,
                       COALESCE(u_drv.name, CONCAT(e_drv.first_name, ' ', e_drv.last_name)) as driver_name,
                       COALESCE(u_drv.phone, e_drv.phone) as driver_phone,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       u_admin.name as assigner_name
                FROM `dispatches` d
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                LEFT JOIN `employees` e_drv ON dp.employee_id = e_drv.id
                LEFT JOIN `users` u_admin ON d.assigned_by = u_admin.id
                ORDER BY d.id DESC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    public function countHistoryFiltered(?string $search = null, ?string $status = null, ?string $fromDate = null, ?string $toDate = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(d.dispatch_code LIKE ? OR b.booking_code LIKE ? OR v.vehicle_number LIKE ? OR u_drv.name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        }

        if (!empty($fromDate)) {
            $conditions[] = "b.start_date >= ?";
            $params[] = $fromDate;
        }

        if (!empty($toDate)) {
            $conditions[] = "b.end_date <= ?";
            $params[] = $toDate;
        }

        $sql = "SELECT COUNT(*) FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                WHERE " . implode(' AND ', $conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPaginatedHistory(?string $search = null, ?string $status = null, ?string $fromDate = null, ?string $toDate = null, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(d.dispatch_code LIKE ? OR b.booking_code LIKE ? OR v.vehicle_number LIKE ? OR u_drv.name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        }

        if (!empty($fromDate)) {
            $conditions[] = "b.start_date >= ?";
            $params[] = $fromDate;
        }

        if (!empty($toDate)) {
            $conditions[] = "b.end_date <= ?";
            $params[] = $toDate;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       b.passenger_count,
                       v.vehicle_number, v.make_model,
                       COALESCE(u_drv.name, CONCAT(e_drv.first_name, ' ', e_drv.last_name)) as driver_name,
                       COALESCE(u_drv.phone, e_drv.phone) as driver_phone,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       u_admin.name as assigner_name
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                LEFT JOIN `employees` e_drv ON dp.employee_id = e_drv.id
                LEFT JOIN `users` u_admin ON d.assigned_by = u_admin.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY d.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $paramIdx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($paramIdx++, $p, PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIdx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIdx++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusHistory(int $dispatchId): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT tsh.*, u.name as changed_by_name, u.role as changed_by_role
                FROM `trip_status_history` tsh
                LEFT JOIN `users` u ON tsh.changed_by_user_id = u.id
                WHERE tsh.dispatch_id = ?
                ORDER BY tsh.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dispatchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForDriverWithDetails(int $dispatchId, int $driverProfileId): ?array
    {
        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       b.pickup_time, b.passenger_count, b.special_requests,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       COALESCE(c_cust.phone, u_cust.phone) as customer_phone,
                       COALESCE(c_cust.email, u_cust.email) as customer_email,
                       v.vehicle_number, v.make_model, v.vehicle_type, v.fuel_type, v.seating_capacity,
                       v.odometer_km as current_vehicle_odometer, v.status as vehicle_status,
                       v.registration_expiry, v.insurance_expiry,
                       t.title as tour_title,
                       u_admin.name as assigner_name
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `users` u_admin ON d.assigned_by = u_admin.id
                WHERE d.id = ? AND d.driver_id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$dispatchId, $driverProfileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getDriverAssignedTrips(int $driverProfileId, ?string $search = null, ?string $status = null, ?string $date = null, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = ["d.driver_id = ?"];
        $params = [$driverProfileId];

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        } else {
            // Default to active assignments
            $conditions[] = "d.status IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')";
        }

        if (!empty($search)) {
            $conditions[] = "(b.booking_code LIKE ? OR d.dispatch_code LIKE ? OR b.pickup_address LIKE ? OR b.drop_address LIKE ? OR COALESCE(c_cust.name, u_cust.name) LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($date)) {
            $conditions[] = "b.start_date = ?";
            $params[] = $date;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       b.pickup_time, b.passenger_count, b.special_requests,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       COALESCE(c_cust.phone, u_cust.phone) as customer_phone,
                       v.vehicle_number, v.make_model, v.odometer_km as current_vehicle_odometer,
                       t.title as tour_title
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY CASE 
                    WHEN d.status = 'trip_started' THEN 1
                    WHEN d.status = 'driver_accepted' THEN 2
                    WHEN d.status = 'assigned' THEN 3
                    ELSE 4 END ASC, b.start_date ASC, b.pickup_time ASC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $idx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($idx++, $p, PDO::PARAM_STR);
        }
        $stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDriverAssignedTrips(int $driverProfileId, ?string $search = null, ?string $status = null, ?string $date = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["d.driver_id = ?"];
        $params = [$driverProfileId];

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        } else {
            $conditions[] = "d.status IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')";
        }

        if (!empty($search)) {
            $conditions[] = "(b.booking_code LIKE ? OR d.dispatch_code LIKE ? OR b.pickup_address LIKE ? OR b.drop_address LIKE ? OR COALESCE(c_cust.name, u_cust.name) LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($date)) {
            $conditions[] = "b.start_date = ?";
            $params[] = $date;
        }

        $sql = "SELECT COUNT(*)
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE " . implode(' AND ', $conditions);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getDriverHistoryPaginatedScoped(int $driverProfileId, ?string $search = null, ?string $status = null, ?string $fromDate = null, ?string $toDate = null, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = ["d.driver_id = ?"];
        $params = [$driverProfileId];

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = "(b.booking_code LIKE ? OR d.dispatch_code LIKE ? OR b.pickup_address LIKE ? OR b.drop_address LIKE ? OR v.vehicle_number LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($fromDate)) {
            $conditions[] = "b.start_date >= ?";
            $params[] = $fromDate;
        }

        if (!empty($toDate)) {
            $conditions[] = "b.end_date <= ?";
            $params[] = $toDate;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT d.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       b.pickup_time, b.passenger_count,
                       COALESCE(c_cust.name, u_cust.name) as customer_name,
                       v.vehicle_number, v.make_model
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `customers` c_cust ON b.customer_profile_id = c_cust.id
                LEFT JOIN `users` u_cust ON b.customer_id = u_cust.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY d.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $idx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($idx++, $p, PDO::PARAM_STR);
        }
        $stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDriverHistoryScoped(int $driverProfileId, ?string $search = null, ?string $status = null, ?string $fromDate = null, ?string $toDate = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["d.driver_id = ?"];
        $params = [$driverProfileId];

        if (!empty($status)) {
            $conditions[] = "d.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = "(b.booking_code LIKE ? OR d.dispatch_code LIKE ? OR b.pickup_address LIKE ? OR b.drop_address LIKE ? OR v.vehicle_number LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($fromDate)) {
            $conditions[] = "b.start_date >= ?";
            $params[] = $fromDate;
        }

        if (!empty($toDate)) {
            $conditions[] = "b.end_date <= ?";
            $params[] = $toDate;
        }

        $sql = "SELECT COUNT(*)
                FROM `dispatches` d
                JOIN `bookings` b ON d.booking_id = b.id
                JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE " . implode(' AND ', $conditions);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getDriverCurrentVehicle(int $driverProfileId): ?array
    {
        $pdo = Database::getConnection();
        // First check active assignment
        $sql = "SELECT v.*, d.dispatch_code, d.status as dispatch_status, d.start_time, d.start_odometer,
                       b.booking_code, b.pickup_address, b.drop_address
                FROM `dispatches` d
                JOIN `vehicles` v ON d.vehicle_id = v.id
                JOIN `bookings` b ON d.booking_id = b.id
                WHERE d.driver_id = ? AND d.status IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')
                ORDER BY d.id DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverProfileId]);
        $active = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($active) {
            $active['is_active_assignment'] = true;
            return $active;
        }

        // Otherwise get last assigned vehicle from completed trips
        $sql = "SELECT v.*, d.dispatch_code, d.status as dispatch_status, d.start_time, d.start_odometer,
                       b.booking_code, b.pickup_address, b.drop_address
                FROM `dispatches` d
                JOIN `vehicles` v ON d.vehicle_id = v.id
                JOIN `bookings` b ON d.booking_id = b.id
                WHERE d.driver_id = ?
                ORDER BY d.id DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverProfileId]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last) {
            $last['is_active_assignment'] = false;
            return $last;
        }

        return null;
    }

    public function getDriverDashboardStats(int $driverProfileId): array
    {
        $pdo = Database::getConnection();

        // Active trips
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `dispatches` WHERE driver_id = ? AND status IN ('assigned', 'driver_accepted', 'en_route_pickup', 'trip_started')");
        $stmt->execute([$driverProfileId]);
        $activeCount = (int)$stmt->fetchColumn();

        // Today's trips
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `dispatches` d JOIN `bookings` b ON d.booking_id = b.id WHERE d.driver_id = ? AND b.start_date = CURDATE()");
        $stmt->execute([$driverProfileId]);
        $todayCount = (int)$stmt->fetchColumn();

        // Completed trips
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `dispatches` WHERE driver_id = ? AND status = 'completed'");
        $stmt->execute([$driverProfileId]);
        $completedCount = (int)$stmt->fetchColumn();

        // Total distance km
        $stmt = $pdo->prepare("SELECT SUM(GREATEST(0, COALESCE(end_odometer, 0) - COALESCE(start_odometer, 0))) FROM `dispatches` WHERE driver_id = ? AND status = 'completed'");
        $stmt->execute([$driverProfileId]);
        $totalKm = (int)($stmt->fetchColumn() ?: 0);

        return [
            'active_trips'    => $activeCount,
            'today_trips'     => $todayCount,
            'completed_trips' => $completedCount,
            'total_km'        => $totalKm,
        ];
    }
}
