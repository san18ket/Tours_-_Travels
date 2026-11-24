<?php
namespace App\Models;

use App\Core\Model;

class Refund extends Model
{
    protected string $table = 'refunds';
    protected array $fillable = [
        'refund_number', 'booking_id', 'invoice_id', 'payment_id',
        'customer_id', 'amount', 'refund_date', 'reason', 'status', 'refund_method',
        'gateway_refund_id', 'processed_by', 'processed_at', 'notes'
    ];

    public function generateRefundNumber(): string
    {
        $prefix = 'REF-' . date('Ymd') . '-';
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

        if (!empty($filters['date_from'])) {
            $where[] = "COALESCE(r.refund_date, DATE(r.created_at)) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "COALESCE(r.refund_date, DATE(r.created_at)) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(r.refund_number LIKE ? OR b.booking_code LIKE ? OR i.invoice_number LIKE ? OR u.name LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT r.*, COALESCE(r.refund_date, DATE(r.created_at)) as refund_date,
                       b.booking_code, b.id as booking_db_id,
                       i.invoice_number, i.total_amount as invoice_total, i.paid_amount as invoice_paid,
                       COALESCE(c.name, u.name, 'Customer') as customer_name,
                       COALESCE(c.phone, u.phone, '') as customer_phone,
                       COALESCE(c.email, u.email, '') as customer_email,
                       p.payment_reference,
                       pu.name as processed_by_name,
                       fa.account_name as processed_account_name,
                       fa.id as processed_account_id,
                       fl.transaction_ref as processed_tx_ref,
                       fl.transacted_at as processed_transacted_at,
                       fl.notes as processed_tx_notes
                FROM `refunds` r
                LEFT JOIN `invoices` i ON r.invoice_id = i.id
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `customers` c ON c.id = COALESCE(b.customer_profile_id, b.customer_id, i.customer_profile_id, i.customer_id)
                LEFT JOIN `users` u ON r.customer_id = u.id
                LEFT JOIN `payments` p ON r.payment_id = p.id
                LEFT JOIN `users` pu ON r.processed_by = pu.id
                LEFT JOIN `finance_ledger` fl ON (fl.reference_type = 'refund' AND fl.reference_id = r.id)
                LEFT JOIN `finance_accounts` fa ON fl.account_id = fa.id
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

        if (!empty($filters['date_from'])) {
            $where[] = "COALESCE(r.refund_date, DATE(r.created_at)) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "COALESCE(r.refund_date, DATE(r.created_at)) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(r.refund_number LIKE ? OR b.booking_code LIKE ? OR i.invoice_number LIKE ? OR u.name LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `refunds` r
                LEFT JOIN `invoices` i ON r.invoice_id = i.id
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `customers` c ON c.id = COALESCE(b.customer_profile_id, b.customer_id, i.customer_profile_id, i.customer_id)
                LEFT JOIN `users` u ON r.customer_id = u.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findWithFullDetails(int $id): ?array
    {
        $sql = "SELECT r.*, COALESCE(r.refund_date, DATE(r.created_at)) as refund_date,
                       b.booking_code, b.id as booking_db_id,
                       b.start_date as booking_start_date, b.end_date as booking_end_date,
                       b.status as booking_status, b.pickup_address, b.drop_address,
                       b.pickup_time, b.passenger_count, b.created_at as booking_created_at,
                       t.id as tour_id, t.title as tour_title, t.category as tour_category,
                       i.invoice_number, i.total_amount as invoice_total, i.paid_amount as invoice_paid,
                       i.issued_at as invoice_issued_at, i.payment_status as invoice_payment_status,
                       i.subtotal as invoice_subtotal, i.tax_amount as invoice_tax, i.discount_amount as invoice_discount,
                       COALESCE(c.id, u.id) as customer_id_val,
                       c.customer_code,
                       COALESCE(c.name, u.name, 'Customer') as customer_name,
                       COALESCE(c.phone, u.phone, '') as customer_phone,
                       COALESCE(c.email, u.email, '') as customer_email,
                       c.address_line, c.city, c.state, c.postal_code, c.country,
                       p.payment_reference,
                       pu.name as processed_by_name,
                       fa.account_name as processed_account_name,
                       fa.account_type as processed_account_type,
                       fa.account_number as processed_account_number,
                       fa.id as processed_account_id,
                       fl.id as ledger_id,
                       fl.transaction_ref as processed_tx_ref,
                       fl.transacted_at as processed_transacted_at,
                       fl.notes as processed_tx_notes,
                       fl.amount as processed_ledger_amount,
                       dsp.dispatch_code, dsp.status as dispatch_status,
                       drv_u.name as driver_name, drv_u.phone as driver_phone,
                       vh.vehicle_number, vh.make_model as vehicle_model
                FROM `refunds` r
                LEFT JOIN `invoices` i ON r.invoice_id = i.id
                LEFT JOIN `bookings` b ON r.booking_id = b.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `customers` c ON c.id = COALESCE(b.customer_profile_id, b.customer_id, i.customer_profile_id, i.customer_id)
                LEFT JOIN `users` u ON r.customer_id = u.id
                LEFT JOIN `payments` p ON r.payment_id = p.id
                LEFT JOIN `users` pu ON r.processed_by = pu.id
                LEFT JOIN `finance_ledger` fl ON (fl.reference_type = 'refund' AND fl.reference_id = r.id)
                LEFT JOIN `finance_accounts` fa ON fl.account_id = fa.id
                LEFT JOIN `dispatches` dsp ON dsp.id = (SELECT id FROM dispatches WHERE booking_id = b.id ORDER BY id DESC LIMIT 1)
                LEFT JOIN `users` drv_u ON dsp.driver_id = drv_u.id
                LEFT JOIN `vehicles` vh ON dsp.vehicle_id = vh.id
                WHERE r.id = ?
                LIMIT 1";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getSummaryStats(): array
    {
        $sql = "SELECT 
            COUNT(*) as total_all,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) as approved_count,
            COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved_amount,
            COALESCE(SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END), 0) as processed_count,
            COALESCE(SUM(CASE WHEN status = 'processed' THEN amount ELSE 0 END), 0) as processed_amount,
            COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) as rejected_count
        FROM `refunds`";

        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'total_all' => 0,
            'pending_count' => 0,
            'pending_amount' => 0.0,
            'approved_count' => 0,
            'approved_amount' => 0.0,
            'processed_count' => 0,
            'processed_amount' => 0.0,
            'rejected_count' => 0,
        ];
    }

    public function getEligibleInvoices(): array
    {
        $sql = "SELECT i.id, i.invoice_number, i.total_amount, i.paid_amount, i.booking_id,
                       b.booking_code,
                       COALESCE(c.name, u.name, 'Customer') as customer_name,
                       COALESCE(c.phone, u.phone, '') as customer_phone,
                       COALESCE(c.email, u.email, '') as customer_email,
                       GREATEST(0.0, (i.paid_amount - COALESCE((
                           SELECT SUM(r.amount) FROM refunds r 
                           WHERE r.invoice_id = i.id AND r.status IN ('pending', 'approved')
                       ), 0.0))) as remaining_refundable
                FROM invoices i
                LEFT JOIN bookings b ON i.booking_id = b.id
                LEFT JOIN customers c ON c.id = COALESCE(b.customer_profile_id, b.customer_id, i.customer_profile_id, i.customer_id)
                LEFT JOIN users u ON u.id = i.customer_id
                WHERE i.paid_amount > 0
                HAVING remaining_refundable > 0
                ORDER BY i.id DESC
                LIMIT 100";

        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
