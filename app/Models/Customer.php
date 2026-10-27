<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Customer extends Model
{
    protected string $table = 'customers';

    protected array $fillable = [
        'customer_code', 'user_id', 'title', 'name', 'date_of_birth', 'gender',
        'phone', 'alternate_phone', 'email',
        'address_line', 'city', 'state', 'postal_code', 'country',
        'profile_photo', 'id_type', 'id_number', 'id_expiry', 'id_document_file', 'notes',
        'status', 'marketing_consent', 'created_at', 'updated_at'
    ];

    /**
     * Generate an elegant, unique customer reference code
     */
    public function generateCustomerCode(): string
    {
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `{$this->table}`");
        $nextId = ((int)$stmt->fetchColumn()) + 1;
        $code = 'VG-CUST-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

        // Ensure collision safety
        while ($this->findBy('customer_code', $code)) {
            $nextId++;
            $code = 'VG-CUST-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Find potential matching customer records for staff review
     * Checks phone number and email without automatically merging records
     */
    public function findPotentialDuplicates(string $phone, ?string $email = null, ?int $excludeId = null): array
    {
        $conditions = ["(`phone` = ? OR `alternate_phone` = ?)"];
        $params = [$phone, $phone];

        if (!empty($email)) {
            $conditions[] = "`email` = ?";
            $params[] = $email;
        }

        $whereClause = "(" . implode(" OR ", $conditions) . ")";
        if ($excludeId !== null) {
            $whereClause .= " AND `id` != ?";
            $params[] = $excludeId;
        }

        $sql = "SELECT `id`, `customer_code`, `name`, `phone`, `email`, `city`, `status`, `created_at` 
                FROM `{$this->table}` 
                WHERE {$whereClause} 
                ORDER BY `id` DESC 
                LIMIT 5";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Filter builder for paginated listing
     */
    private function buildFilterConditions(?string $search, ?string $status): array
    {
        $clauses = [];
        $params = [];

        if (!empty($search)) {
            $clauses[] = "(`customer_code` LIKE ? OR `name` LIKE ? OR `phone` LIKE ? OR `alternate_phone` LIKE ? OR `email` LIKE ? OR `city` LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if ($status === 'with_bookings' || $status === 'bookings') {
            $clauses[] = "EXISTS (SELECT 1 FROM `bookings` b WHERE b.customer_profile_id = c.id OR (b.customer_profile_id IS NULL AND b.customer_id = c.user_id))";
        } elseif (!empty($status) && in_array($status, ['active', 'inactive'], true)) {
            $clauses[] = "c.`status` = ?";
            $params[] = $status;
        }

        $whereSql = !empty($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$whereSql, $params];
    }

    /**
     * Count matching records
     */
    public function countFiltered(?string $search = null, ?string $status = null): int
    {
        [$whereSql, $params] = $this->buildFilterConditions($search, $status);
        $sql = "SELECT COUNT(*) FROM `{$this->table}` c {$whereSql}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Paginated customer listing
     */
    public function getPaginated(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 15): array
    {
        [$whereSql, $params] = $this->buildFilterConditions($search, $status);
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM `bookings` b WHERE b.customer_profile_id = c.id OR (b.customer_profile_id IS NULL AND b.customer_id = c.user_id)) as total_bookings
                FROM `{$this->table}` c
                {$whereSql} 
                ORDER BY c.id DESC 
                LIMIT ? OFFSET ?";
        $stmt = $this->getPdo()->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get booking history for a specific customer profile
     */
    public function getBookingHistory(int $customerId): array
    {
        $sql = "SELECT b.id, b.booking_code, b.start_date, b.end_date, b.passenger_count, 
                       b.total_amount, b.status, b.created_at,
                       t.title as tour_title,
                       inv.invoice_number, inv.payment_status
                FROM `bookings` b
                LEFT JOIN `tours` t ON b.tour_id = t.id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                WHERE b.customer_profile_id = ? 
                   OR (b.customer_profile_id IS NULL AND b.customer_id = (SELECT user_id FROM `customers` WHERE id = ?))
                ORDER BY b.id DESC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$customerId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Toggle customer active / inactive status
     */
    public function toggleStatus(int $id, ?string $desiredStatus = null): string
    {
        $customer = $this->find($id);
        if (!$customer) {
            throw new \RuntimeException('Customer record not found.');
        }

        if ($desiredStatus !== null && in_array($desiredStatus, ['active', 'inactive'], true)) {
            $newStatus = $desiredStatus;
        } else {
            $newStatus = ($customer['status'] === 'active') ? 'inactive' : 'active';
        }

        $this->update($id, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $newStatus;
    }
}

