<?php
namespace App\Models;

use App\Core\Model;

class OnlineTransaction extends Model
{
    protected string $table = 'online_transactions';
    protected array $fillable = [
        'gateway', 'order_id', 'payment_id', 'invoice_id', 'customer_id',
        'amount', 'currency', 'status', 'signature', 'error_code',
        'error_description', 'payload_json', 'webhook_event_id',
        'created_at', 'updated_at'
    ];

    public function findByOrderId(string $orderId): ?array
    {
        return $this->findBy('order_id', $orderId);
    }

    public function findByPaymentId(string $paymentId): ?array
    {
        return $this->findBy('payment_id', $paymentId);
    }

    public function allWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "ot.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = "(ot.order_id LIKE ? OR ot.payment_id LIKE ? OR inv.invoice_number LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT ot.*, inv.invoice_number, inv.total_amount as invoice_total,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       b.booking_code
                FROM `online_transactions` ot
                JOIN `invoices` inv ON ot.invoice_id = inv.id
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON ot.customer_id = c.id
                LEFT JOIN `users` u ON ot.customer_id = u.id
                WHERE {$whereClause}
                ORDER BY ot.created_at DESC, ot.id DESC
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
            $where[] = "ot.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = "(ot.order_id LIKE ? OR ot.payment_id LIKE ? OR inv.invoice_number LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT COUNT(*) 
                FROM `online_transactions` ot
                JOIN `invoices` inv ON ot.invoice_id = inv.id
                WHERE {$whereClause}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
