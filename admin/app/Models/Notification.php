<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Notification extends Model
{
    protected string $table = 'notifications';

    protected array $fillable = [
        'user_id',
        'employee_id',
        'title',
        'message',
        'type',
        'reference_type',
        'reference_id',
        'channel',
        'data',
        'is_read',
        'read_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get recent notifications for a user or employee
     */
    public function getForRecipient(?int $userId = null, ?int $employeeId = null, int $limit = 10): array
    {
        if (!$userId && !$employeeId) {
            return [];
        }

        $clauses = [];
        $params = [];

        if ($userId && $employeeId) {
            $clauses[] = "(`user_id` = ? OR `employee_id` = ?)";
            $params[] = $userId;
            $params[] = $employeeId;
        } elseif ($userId) {
            $clauses[] = "`user_id` = ?";
            $params[] = $userId;
        } else {
            $clauses[] = "`employee_id` = ?";
            $params[] = $employeeId;
        }

        $sql = "SELECT * FROM `{$this->table}` 
                WHERE " . implode(' AND ', $clauses) . " 
                ORDER BY `created_at` DESC 
                LIMIT {$limit}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON data if present
        foreach ($rows as &$row) {
            if (!empty($row['data']) && is_string($row['data'])) {
                $row['payload'] = json_decode($row['data'], true) ?: [];
            } else {
                $row['payload'] = [];
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Count unread notifications
     */
    public function countUnread(?int $userId = null, ?int $employeeId = null): int
    {
        if (!$userId && !$employeeId) {
            return 0;
        }

        $clauses = ["`is_read` = 0"];
        $params = [];

        if ($userId && $employeeId) {
            $clauses[] = "(`user_id` = ? OR `employee_id` = ?)";
            $params[] = $userId;
            $params[] = $employeeId;
        } elseif ($userId) {
            $clauses[] = "`user_id` = ?";
            $params[] = $userId;
        } else {
            $clauses[] = "`employee_id` = ?";
            $params[] = $employeeId;
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE " . implode(' AND ', $clauses);
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(int $id, ?int $userId = null): bool
    {
        $sql = "UPDATE `{$this->table}` SET `is_read` = 1, `read_at` = NOW() WHERE `id` = ?";
        $params = [$id];

        if ($userId) {
            $sql .= " AND (`user_id` = ? OR `user_id` IS NULL)";
            $params[] = $userId;
        }

        $stmt = $this->getPdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Mark all notifications as read for a recipient
     */
    public function markAllAsRead(?int $userId = null, ?int $employeeId = null): bool
    {
        if (!$userId && !$employeeId) {
            return false;
        }

        $clauses = [];
        $params = [];

        if ($userId && $employeeId) {
            $clauses[] = "(`user_id` = ? OR `employee_id` = ?)";
            $params[] = $userId;
            $params[] = $employeeId;
        } elseif ($userId) {
            $clauses[] = "(`user_id` = ? OR `user_id` IS NULL)";
            $params[] = $userId;
        } else {
            $clauses[] = "`employee_id` = ?";
            $params[] = $employeeId;
        }

        $sql = "UPDATE `{$this->table}` SET `is_read` = 1, `read_at` = NOW() WHERE `is_read` = 0 AND " . implode(' AND ', $clauses);
        $stmt = $this->getPdo()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Build where conditions for filtered notification queries
     */
    private function buildFilterConditions(?int $userId = null, ?int $employeeId = null, string $search = '', string $status = ''): array
    {
        $clauses = [];
        $params = [];

        if ($userId && $employeeId) {
            $clauses[] = "(`user_id` = ? OR `employee_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $userId;
            $params[] = $employeeId;
        } elseif ($userId) {
            $clauses[] = "(`user_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $userId;
        } elseif ($employeeId) {
            $clauses[] = "(`employee_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $employeeId;
        }

        $cleanStatus = strtolower(trim($status));
        if ($cleanStatus === 'unread') {
            $clauses[] = "`is_read` = 0";
        } elseif ($cleanStatus === 'read') {
            $clauses[] = "`is_read` = 1";
        } elseif ($cleanStatus === 'trip_assigned' || $cleanStatus === 'trips') {
            $clauses[] = "`type` LIKE 'trip_%'";
        } elseif ($cleanStatus === 'bookings') {
            $clauses[] = "(`reference_type` = 'bookings' OR `type` LIKE 'booking_%')";
        } elseif ($cleanStatus === 'finance') {
            $clauses[] = "(`reference_type` IN ('invoices', 'payments', 'refunds', 'finance') OR `type` LIKE 'payment_%' OR `type` LIKE 'finance_%')";
        }

        $cleanSearch = trim($search);
        if ($cleanSearch !== '') {
            $clauses[] = "(`title` LIKE ? OR `message` LIKE ? OR `reference_type` LIKE ?)";
            $term = '%' . $cleanSearch . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return [
            'where'  => !empty($clauses) ? implode(' AND ', $clauses) : '1=1',
            'params' => $params
        ];
    }

    /**
     * Count notifications matching criteria
     */
    public function countFiltered(?int $userId = null, ?int $employeeId = null, string $search = '', string $status = ''): int
    {
        $cond = $this->buildFilterConditions($userId, $employeeId, $search, $status);
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE {$cond['where']}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($cond['params']);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get paginated notifications matching criteria
     */
    public function getPaginated(?int $userId = null, ?int $employeeId = null, string $search = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        $cond = $this->buildFilterConditions($userId, $employeeId, $search, $status);
        $offset = max(0, ($page - 1) * $perPage);
        $perPage = max(1, $perPage);

        $sql = "SELECT * FROM `{$this->table}` 
                WHERE {$cond['where']} 
                ORDER BY `is_read` ASC, `created_at` DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($cond['params']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (!empty($row['data']) && is_string($row['data'])) {
                $row['payload'] = json_decode($row['data'], true) ?: [];
            } else {
                $row['payload'] = [];
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Get KPI Summary counts for recipient
     */
    public function getKpiStats(?int $userId = null, ?int $employeeId = null): array
    {
        $recipientClauses = [];
        $params = [];

        if ($userId && $employeeId) {
            $recipientClauses[] = "(`user_id` = ? OR `employee_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $userId;
            $params[] = $employeeId;
        } elseif ($userId) {
            $recipientClauses[] = "(`user_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $userId;
        } elseif ($employeeId) {
            $recipientClauses[] = "(`employee_id` = ? OR (`user_id` IS NULL AND `employee_id` IS NULL))";
            $params[] = $employeeId;
        }

        $recWhere = !empty($recipientClauses) ? implode(' AND ', $recipientClauses) : '1=1';

        $sql = "SELECT 
                    COUNT(*) as total_all,
                    SUM(CASE WHEN `is_read` = 0 THEN 1 ELSE 0 END) as total_unread,
                    SUM(CASE WHEN `is_read` = 1 THEN 1 ELSE 0 END) as total_read,
                    SUM(CASE WHEN `type` LIKE 'trip_%' THEN 1 ELSE 0 END) as total_trips,
                    SUM(CASE WHEN `is_read` = 0 OR `type` = 'trip_assigned' THEN 1 ELSE 0 END) as total_urgent
                FROM `{$this->table}` 
                WHERE {$recWhere}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_all'    => (int)($res['total_all'] ?? 0),
            'total_unread' => (int)($res['total_unread'] ?? 0),
            'total_read'   => (int)($res['total_read'] ?? 0),
            'total_trips'  => (int)($res['total_trips'] ?? 0),
            'total_urgent' => (int)($res['total_urgent'] ?? 0),
        ];
    }
}
