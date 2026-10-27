<?php
namespace App\Models;

use App\Core\Model;

class Booking extends Model
{
    protected string $table = 'bookings';
    protected array $fillable = [
        'booking_code', 'customer_id', 'customer_profile_id', 'tour_id', 'pickup_address', 'drop_address',
        'start_date', 'end_date', 'pickup_time', 'passenger_count', 'special_requests',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'status', 'idempotency_token'
    ];

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT b.*,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.customer_code, CONCAT('LEGACY-', u.id)) as customer_code,
                       t.title as tour_title, t.category as tour_category,
                       d.id as dispatch_id, d.status as dispatch_status, d.driver_id, d.vehicle_id,
                       v.vehicle_number, v.make_model,
                       u_drv.name as driver_name, u_drv.phone as driver_phone,
                       inv.id as invoice_id, inv.invoice_number, inv.payment_status, inv.paid_amount
                FROM `bookings` b
                LEFT JOIN `customers` c ON b.customer_profile_id = c.id
                LEFT JOIN `users` u ON b.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `dispatches` d ON b.id = d.booking_id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                WHERE b.id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllDetailed(string $filter = '', array $params = []): array
    {
        $whereClause = $filter ? "WHERE {$filter}" : "";
        $sql = "SELECT b.*,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.customer_code, CONCAT('LEGACY-', u.id)) as customer_code,
                       t.title as tour_title,
                       d.id as dispatch_id, d.status as dispatch_status,
                       v.make_model, v.vehicle_number,
                       u_drv.name as driver_name,
                       inv.payment_status
                FROM `bookings` b
                LEFT JOIN `customers` c ON b.customer_profile_id = c.id
                LEFT JOIN `users` u ON b.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `dispatches` d ON b.id = d.booking_id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                {$whereClause}
                ORDER BY b.id DESC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countFilteredDetailed(string $filter = '', array $params = []): int
    {
        $whereClause = $filter ? "WHERE {$filter}" : "";
        $sql = "SELECT COUNT(*) as total
                FROM `bookings` b
                LEFT JOIN `customers` c ON b.customer_profile_id = c.id
                LEFT JOIN `users` u ON b.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                {$whereClause}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    public function getPaginatedDetailed(string $filter = '', array $params = [], int $page = 1, int $perPage = 10, string $orderBy = 'b.id DESC'): array
    {
        $whereClause = $filter ? "WHERE {$filter}" : "";
        $offset = max(0, ($page - 1) * $perPage);

        $allowedOrders = [
            'b.id DESC',
            'b.id ASC',
            'COALESCE(c.name, u.name) ASC',
            'COALESCE(c.name, u.name) DESC',
            'b.start_date ASC',
            'b.start_date DESC'
        ];
        if (!in_array($orderBy, $allowedOrders, true)) {
            $orderBy = 'b.id DESC';
        }

        $sql = "SELECT b.*,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.customer_code, CONCAT('LEGACY-', u.id)) as customer_code,
                       t.title as tour_title,
                       d.id as dispatch_id, d.status as dispatch_status,
                       v.make_model, v.vehicle_number,
                       u_drv.name as driver_name,
                       inv.payment_status
                FROM `bookings` b
                LEFT JOIN `customers` c ON b.customer_profile_id = c.id
                LEFT JOIN `users` u ON b.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `dispatches` d ON b.id = d.booking_id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `driver_profiles` dp ON d.driver_id = dp.id
                LEFT JOIN `users` u_drv ON dp.user_id = u_drv.id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                {$whereClause}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?";
        $stmt = $this->getPdo()->prepare($sql);
        $idx = 1;
        foreach ($params as $val) {
            $stmt->bindValue($idx++, $val);
        }
        $stmt->bindValue($idx++, (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue($idx++, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBookingStatistics(?int $customerId = null): array
    {
        $where = $customerId ? "WHERE customer_id = ?" : "";
        $params = $customerId ? [$customerId] : [];
        $sql = "SELECT 
                    COUNT(*) as total_count,
                    COALESCE(SUM(CASE WHEN status IN ('confirmed', 'assigned', 'in_progress') THEN 1 ELSE 0 END), 0) as active_count,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_count,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
                    COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_count,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as total_revenue
                FROM `bookings` {$where}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return [
            'total'     => (int)($row['total_count'] ?? 0),
            'active'    => (int)($row['active_count'] ?? 0),
            'completed' => (int)($row['completed_count'] ?? 0),
            'pending'   => (int)($row['pending_count'] ?? 0),
            'cancelled' => (int)($row['cancelled_count'] ?? 0),
            'revenue'   => (float)($row['total_revenue'] ?? 0),
        ];
    }
}


