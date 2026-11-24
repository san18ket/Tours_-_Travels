<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    protected array $fillable = [
        'payment_reference', 'invoice_id', 'account_id', 'amount', 'payment_method',
        'transaction_id', 'status', 'idempotency_key', 'notes', 'paid_at'
    ];

    public function findByIdempotencyKey(string $key): ?array
    {
        return $this->findBy('idempotency_key', $key);
    }

    public function getPaymentsByInvoice(int $invoiceId): array
    {
        return $this->where("`invoice_id` = ?", [$invoiceId], 'id DESC');
    }

    /**
     * Query payments joined with invoices, bookings, customers, and accounts
     */
    public function getPaymentsWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['account_id'])) {
            $where[] = "p.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(p.paid_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(p.paid_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.payment_reference LIKE ? OR p.transaction_id LIKE ? OR i.invoice_number LIKE ? OR b.booking_code LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT p.*, i.invoice_number, i.total_amount as invoice_total, i.paid_amount as invoice_paid,
                       b.id as booking_id, b.booking_code,
                       u.id as customer_id, u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                       fa.account_name, fa.bank_name
                FROM `payments` p
                JOIN `invoices` i ON p.invoice_id = i.id
                LEFT JOIN `bookings` b ON i.booking_id = b.id
                LEFT JOIN `users` u ON i.customer_id = u.id
                LEFT JOIN `finance_accounts` fa ON p.account_id = fa.id
                WHERE {$whereSql}
                ORDER BY p.paid_at DESC, p.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count payments matching filters
     */
    public function countPayments(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['account_id'])) {
            $where[] = "p.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(p.paid_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(p.paid_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.payment_reference LIKE ? OR p.transaction_id LIKE ? OR i.invoice_number LIKE ? OR b.booking_code LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `payments` p
                JOIN `invoices` i ON p.invoice_id = i.id
                LEFT JOIN `bookings` b ON i.booking_id = b.id
                LEFT JOIN `users` u ON i.customer_id = u.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
