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
        $code = 'CUS-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

        // Ensure collision safety
        while ($this->findBy('customer_code', $code)) {
            $nextId++;
            $code = 'CUS-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
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
     * Paginated customer listing with aggregated payment status
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
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->attachPaymentSummary($customers);

        return $customers;
    }

    /**
     * Attach aggregated financial payment status to an array of customer records
     *
     * Sets:
     * - payment_status: 'paid' | 'partially_paid' | 'unpaid' | 'no_bookings'
     * - payment_status_label: 'Paid' | 'Partially Paid' | 'Payment Due' | 'No Bookings'
     * - total_billed: float
     * - total_paid: float
     * - total_due: float
     *
     * @param array $customers (passed by reference)
     */
    public function attachPaymentSummary(array &$customers): void
    {
        if (empty($customers)) {
            return;
        }

        $custMap = [];
        $custIds = [];
        $userIds = [];

        foreach ($customers as $idx => $c) {
            $cId = (int)$c['id'];
            $uId = !empty($c['user_id']) ? (int)$c['user_id'] : null;
            $custIds[] = $cId;
            if ($uId) {
                $userIds[] = $uId;
            }
            $custMap[$cId] = $idx;
            $customers[$idx]['booking_count'] = 0;
            $customers[$idx]['total_billed'] = 0.0;
            $customers[$idx]['total_paid'] = 0.0;
            $customers[$idx]['total_due'] = 0.0;
            $customers[$idx]['payment_status'] = 'no_bookings';
            $customers[$idx]['payment_status_label'] = 'No Bookings';
        }

        $pdo = $this->getPdo();

        // 1. Fetch all bookings for these customers
        $cPlaceholders = implode(',', array_fill(0, count($custIds), '?'));
        $params = $custIds;
        $userSql = '';
        if (!empty($userIds)) {
            $uPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
            $userSql = " OR (b.customer_profile_id IS NULL AND b.customer_id IN ({$uPlaceholders}))";
            $params = array_merge($params, $userIds);
        }

        $stmtBookings = $pdo->prepare("
            SELECT b.id, b.customer_profile_id, b.customer_id, b.total_amount, b.status
            FROM `bookings` b
            WHERE b.customer_profile_id IN ({$cPlaceholders}) {$userSql}
        ");
        $stmtBookings->execute($params);
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        $bookingIds = [];
        $customerBookings = [];

        foreach ($bookings as $b) {
            $bId = (int)$b['id'];
            $bookingIds[] = $bId;
            $cProfId = !empty($b['customer_profile_id']) ? (int)$b['customer_profile_id'] : null;
            $cUserId = !empty($b['customer_id']) ? (int)$b['customer_id'] : null;

            $matchedCId = null;
            if ($cProfId && isset($custMap[$cProfId])) {
                $matchedCId = $cProfId;
            } elseif ($cUserId) {
                foreach ($customers as $c) {
                    if (!empty($c['user_id']) && (int)$c['user_id'] === $cUserId) {
                        $matchedCId = (int)$c['id'];
                        break;
                    }
                }
            }

            if ($matchedCId) {
                $customerBookings[$matchedCId][] = $b;
            }
        }

        // 2. Fetch all invoices for these customers / bookings
        $invParams = $custIds;
        $invUserSql = '';
        if (!empty($userIds)) {
            $uPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
            $invUserSql = " OR (inv.customer_profile_id IS NULL AND inv.customer_id IN ({$uPlaceholders}))";
            $invParams = array_merge($invParams, $userIds);
        }
        $invBookingSql = '';
        if (!empty($bookingIds)) {
            $bPlaceholders = implode(',', array_fill(0, count($bookingIds), '?'));
            $invBookingSql = " OR inv.booking_id IN ({$bPlaceholders})";
            $invParams = array_merge($invParams, $bookingIds);
        }

        $stmtInv = $pdo->prepare("
            SELECT inv.id, inv.booking_id, inv.customer_profile_id, inv.customer_id,
                   inv.total_amount, inv.paid_amount, inv.payment_status
            FROM `invoices` inv
            WHERE inv.customer_profile_id IN ({$cPlaceholders}) {$invUserSql} {$invBookingSql}
        ");
        $stmtInv->execute($invParams);
        $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        $customerInvoices = [];
        foreach ($invoices as $inv) {
            $invId = (int)$inv['id'];
            $cProfId = !empty($inv['customer_profile_id']) ? (int)$inv['customer_profile_id'] : null;
            $cUserId = !empty($inv['customer_id']) ? (int)$inv['customer_id'] : null;
            $bId = !empty($inv['booking_id']) ? (int)$inv['booking_id'] : null;

            $matchedCId = null;
            if ($cProfId && isset($custMap[$cProfId])) {
                $matchedCId = $cProfId;
            } elseif ($bId) {
                foreach ($customerBookings as $targetCId => $bList) {
                    foreach ($bList as $bk) {
                        if ((int)$bk['id'] === $bId) {
                            $matchedCId = $targetCId;
                            break 2;
                        }
                    }
                }
            } elseif ($cUserId) {
                foreach ($customers as $c) {
                    if (!empty($c['user_id']) && (int)$c['user_id'] === $cUserId) {
                        $matchedCId = (int)$c['id'];
                        break;
                    }
                }
            }

            if ($matchedCId) {
                $customerInvoices[$matchedCId][$invId] = $inv;
            }
        }

        // 3. Compute totals and assign status
        foreach ($customers as $idx => &$c) {
            $cId = (int)$c['id'];
            $bList = $customerBookings[$cId] ?? [];
            $invList = array_values($customerInvoices[$cId] ?? []);

            $c['booking_count'] = count($bList);

            if (empty($bList) && empty($invList)) {
                $c['payment_status'] = 'no_bookings';
                $c['payment_status_label'] = 'No Bookings';
                $c['total_billed'] = 0.0;
                $c['total_paid'] = 0.0;
                $c['total_due'] = 0.0;
                continue;
            }

            $totalBilled = 0.0;
            $totalPaid = 0.0;

            $invoicedBookingIds = [];
            foreach ($invList as $inv) {
                $totalBilled += (float)$inv['total_amount'];
                $totalPaid += (float)$inv['paid_amount'];
                if (!empty($inv['booking_id'])) {
                    $invoicedBookingIds[] = (int)$inv['booking_id'];
                }
            }

            foreach ($bList as $bk) {
                if (!in_array((int)$bk['id'], $invoicedBookingIds, true)) {
                    $bkStatus = strtolower($bk['status'] ?? '');
                    if ($bkStatus !== 'cancelled') {
                        $totalBilled += (float)$bk['total_amount'];
                    }
                }
            }

            $totalDue = max(0.0, $totalBilled - $totalPaid);
            $c['total_billed'] = $totalBilled;
            $c['total_paid'] = $totalPaid;
            $c['total_due'] = $totalDue;

            if ($totalDue <= 0.009) {
                $c['payment_status'] = 'paid';
                $c['payment_status_label'] = 'Paid';
            } elseif ($totalPaid > 0.009) {
                $c['payment_status'] = 'partially_paid';
                $c['payment_status_label'] = 'Partially Paid';
            } else {
                $c['payment_status'] = 'unpaid';
                $c['payment_status_label'] = 'Payment Due';
            }
        }
        unset($c);
    }

    /**
     * Get booking history for a specific customer profile
     */
    public function getBookingHistory(int $customerId): array
    {
        $sql = "SELECT b.id, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date, b.passenger_count, 
                       b.total_amount, b.status, b.created_at,
                       t.title as tour_title, t.image_url as tour_image,
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

