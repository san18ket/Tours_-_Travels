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
}
