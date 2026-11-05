<?php
namespace App\Models;

use App\Core\Model;
use RuntimeException;

class Invoice extends Model
{
    protected string $table = 'invoices';
    protected array $fillable = [
        'invoice_number', 'booking_id', 'customer_id', 'customer_profile_id', 'subtotal', 'tax_rate',
        'tax_amount', 'discount_amount', 'total_amount', 'paid_amount', 'payment_status',
        'due_date', 'is_immutable', 'issued_at'
    ];

    public function findByBookingId(int $bookingId): ?array
    {
        return $this->findBy('booking_id', $bookingId);
    }

    public function findWithFullDetails(int $id): ?array
    {
        $sql = "SELECT inv.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.customer_code, CONCAT('LEGACY-', u.id)) as customer_code,
                       t.title as tour_title
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                WHERE inv.id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllWithDetails(?int $limit = null): array
    {
        $limitClause = ($limit !== null && $limit > 0) ? "LIMIT " . (int)$limit : "";
        $sql = "SELECT inv.*, b.booking_code,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                ORDER BY inv.id DESC
                {$limitClause}";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get pending receivable invoices with customer & booking details
     */
    public function getPendingInvoicesWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["(inv.total_amount - inv.paid_amount) > 0.01"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "inv.payment_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "inv.due_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "inv.due_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(inv.invoice_number LIKE ? OR b.booking_code LIKE ? OR c.name LIKE ? OR u.name LIKE ? OR c.phone LIKE ? OR u.phone LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT inv.*, (inv.total_amount - inv.paid_amount) as remaining_amount,
                       b.id as booking_id, b.booking_code,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                WHERE {$whereSql}
                ORDER BY inv.due_date ASC, inv.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count pending invoices matching filters
     */
    public function countPendingInvoices(array $filters = []): int
    {
        $where = ["(inv.total_amount - inv.paid_amount) > 0.01"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "inv.payment_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "inv.due_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "inv.due_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(inv.invoice_number LIKE ? OR b.booking_code LIKE ? OR c.name LIKE ? OR u.name LIKE ? OR c.phone LIKE ? OR u.phone LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Enforce invoice immutability
     */
    public function update(int|string $id, array $data): bool
    {
        $existing = $this->find($id);
        if ($existing && !empty($existing['is_immutable'])) {
            // Only allow payment status and paid amount updates
            $allowedKeys = ['paid_amount', 'payment_status'];
            $filteredData = array_intersect_key($data, array_flip($allowedKeys));
            if (empty($filteredData)) {
                throw new RuntimeException("Invoice {$existing['invoice_number']} is finalized and immutable. Financial core cannot be modified.");
            }
            return parent::update($id, $filteredData);
        }
        return parent::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        $existing = $this->find($id);
        if ($existing && !empty($existing['is_immutable'])) {
            throw new RuntimeException("Finalized invoices cannot be deleted under accounting compliance rules.");
        }
        return parent::delete($id);
    }
}
