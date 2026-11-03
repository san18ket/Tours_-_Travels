<?php
namespace App\Models;

use App\Core\Model;

class Refund extends Model
{
    protected string $table = 'refunds';
    protected array $fillable = [
        'refund_number', 'booking_id', 'invoice_id', 'payment_id',
        'customer_id', 'amount', 'reason', 'status', 'refund_method',
        'gateway_refund_id', 'processed_by', 'processed_at', 'notes'
    ];

    public function generateRefundNumber(): string
    {
        $prefix = 'REF-' . date('Ym') . '-';
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `refunds`");
        $next = ((int)$stmt->fetchColumn()) + 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function getRefundsWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(r.refund_number LIKE ? OR b.booking_code LIKE ? OR u.name LIKE ? OR c.name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT r.*, b.booking_code,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.email, u.email) as customer_email,
                       p.payment_reference,
                       pu.name as processed_by_name
                FROM `refunds` r
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `users` u ON r.customer_id = u.id
                LEFT JOIN `customers` c ON b.customer_id = c.id
                LEFT JOIN `payments` p ON r.payment_id = p.id
                LEFT JOIN `users` pu ON r.processed_by = pu.id
                WHERE {$whereSql}
                ORDER BY r.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countRefunds(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(r.refund_number LIKE ? OR b.booking_code LIKE ? OR u.name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `refunds` r
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `users` u ON r.customer_id = u.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}

