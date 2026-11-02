<?php
namespace App\Models;

use App\Core\Model;

class Refund extends Model
{
    protected string $table = 'refunds';
    protected array $fillable = [
        'refund_number', 'booking_id', 'invoice_id', 'payment_id',
        'customer_id', 'amount', 'reason', 'status', 'refund_method',
        'gateway_refund_id', 'processed_by', 'processed_at', 'notes',
        'created_at', 'updated_at'
    ];

    public function generateRefundNumber(): string
    {
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `refunds`");
        $maxId = (int)$stmt->fetchColumn();
        return sprintf('RFD-%s-%04d', date('Ym'), $maxId + 1);
    }

    public function allWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['invoice_id'])) {
            $where[] = "r.invoice_id = ?";
            $params[] = (int)$filters['invoice_id'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT r.*, inv.invoice_number, inv.total_amount as invoice_total, inv.paid_amount as invoice_paid,
                       b.booking_code,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       up.name as processed_by_name
                FROM `refunds` r
                JOIN `invoices` inv ON r.invoice_id = inv.id
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `customers` c ON r.customer_id = c.id
                LEFT JOIN `users` u ON r.customer_id = u.id
                LEFT JOIN `users` up ON r.processed_by = up.id
                WHERE {$whereClause}
                ORDER BY r.created_at DESC, r.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['invoice_id'])) {
            $where[] = "r.invoice_id = ?";
            $params[] = (int)$filters['invoice_id'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT COUNT(*) FROM `refunds` r WHERE {$whereClause}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
