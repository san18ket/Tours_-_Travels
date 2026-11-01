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
            $clauses[] = "`user_id` = ?";
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
     * Get paginated notifications for recipient with optional search and filter
     */
    public function getPaginatedForRecipient(?int $userId = null, ?int $employeeId = null, ?string $search = null, ?string $status = null, int $page = 1, int $perPage = 10): array
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

        if (!empty($search)) {
            $clauses[] = "(`title` LIKE ? OR `message` LIKE ? OR `data` LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($status === 'unread') {
            $clauses[] = "`is_read` = 0";
        } elseif ($status === 'read') {
            $clauses[] = "`is_read` = 1";
        } elseif ($status === 'trip_assigned') {
            $clauses[] = "`type` = 'trip_assigned'";
        }

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE " . implode(' AND ', $clauses) . " 
                ORDER BY `created_at` DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
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
     * Count filtered notifications for recipient
     */
    public function countFiltered(?int $userId = null, ?int $employeeId = null, ?string $search = null, ?string $status = null): int
    {
        if (!$userId && !$employeeId) {
            return 0;
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

        if (!empty($search)) {
            $clauses[] = "(`title` LIKE ? OR `message` LIKE ? OR `data` LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($status === 'unread') {
            $clauses[] = "`is_read` = 0";
        } elseif ($status === 'read') {
            $clauses[] = "`is_read` = 1";
        } elseif ($status === 'trip_assigned') {
            $clauses[] = "`type` = 'trip_assigned'";
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE " . implode(' AND ', $clauses);
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get KPI Summary counts for recipient
     */
    public function getKpiStats(?int $userId = null, ?int $employeeId = null): array
    {
        if (!$userId && !$employeeId) {
            return ['total_all' => 0, 'total_unread' => 0, 'total_trips' => 0, 'total_read' => 0];
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

        $baseWhere = implode(' AND ', $clauses);

        $sql = "SELECT 
                    COUNT(*) as total_all,
                    SUM(CASE WHEN `is_read` = 0 THEN 1 ELSE 0 END) as total_unread,
                    SUM(CASE WHEN `type` = 'trip_assigned' THEN 1 ELSE 0 END) as total_trips,
                    SUM(CASE WHEN `is_read` = 1 THEN 1 ELSE 0 END) as total_read
                FROM `{$this->table}` 
                WHERE {$baseWhere}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_all'    => (int)($row['total_all'] ?? 0),
            'total_unread' => (int)($row['total_unread'] ?? 0),
            'total_trips'  => (int)($row['total_trips'] ?? 0),
            'total_read'   => (int)($row['total_read'] ?? 0),
        ];
    }
}

