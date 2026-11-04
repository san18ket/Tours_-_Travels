<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\EmailLog;
use PDO;

/**
 * Dedicated Repository for Email Audit Logs and Outbox
 * Encapsulates database queries, status filtering, metrics, and search.
 */
class EmailLogRepository
{
    private string $table = 'email_logs';
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Find a single log record by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Get aggregate statistics for the Email Management dashboard
     */
    public function getDashboardStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_count,
                    SUM(CASE WHEN `status` = 'sent' THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN `status` = 'failed' THEN 1 ELSE 0 END) as failed_count,
                    SUM(CASE WHEN `status` IN ('pending_retry', 'pending') THEN 1 ELSE 0 END) as pending_retry_count,
                    SUM(CASE WHEN `status` = 'skipped' THEN 1 ELSE 0 END) as skipped_count,
                    SUM(CASE WHEN `status` = 'uncertain' THEN 1 ELSE 0 END) as uncertain_count,
                    SUM(CASE WHEN `created_at` >= CURDATE() THEN 1 ELSE 0 END) as today_total,
                    SUM(CASE WHEN `created_at` >= CURDATE() AND `status` = 'sent' THEN 1 ELSE 0 END) as today_sent,
                    SUM(CASE WHEN `created_at` >= CURDATE() AND `status` = 'failed' THEN 1 ELSE 0 END) as today_failed
                FROM `{$this->table}`";

        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total'         => (int)($row['total_count'] ?? 0),
            'sent'          => (int)($row['sent_count'] ?? 0),
            'failed'        => (int)($row['failed_count'] ?? 0),
            'pending_retry' => (int)($row['pending_retry_count'] ?? 0),
            'skipped'       => (int)($row['skipped_count'] ?? 0),
            'uncertain'     => (int)($row['uncertain_count'] ?? 0),
            'today_total'   => (int)($row['today_total'] ?? 0),
            'today_sent'    => (int)($row['today_sent'] ?? 0),
            'today_failed'  => (int)($row['today_failed'] ?? 0),
        ];
    }

    /**
     * Get paginated and filtered logs
     *
     * @param array $filters ['status' => ?string, 'type' => ?string, 'search' => ?string, 'from_date' => ?string, 'to_date' => ?string]
     * @param int $page Current page (1-based)
     * @param int $perPage Items per page
     * @return array ['items' => array, 'total' => int, 'page' => int, 'per_page' => int, 'total_pages' => int]
     */
    public function getFilteredLogs(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "`status` = ?";
            $params[] = $filters['status'];
        }

        // Email type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $where[] = "`email_type` = ?";
            $params[] = $filters['type'];
        }

        // Search term (email, name, subject, or reference_id)
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $where[] = "(`recipient_email` LIKE ? OR `recipient_name` LIKE ? OR `subject` LIKE ? OR `reference_id` = ?)";
            $params[] = "%{$term}%";
            $params[] = "%{$term}%";
            $params[] = "%{$term}%";
            $params[] = is_numeric($term) ? (int)$term : 0;
        }

        // Date range
        if (!empty($filters['from_date'])) {
            $where[] = "`created_at` >= ?";
            $params[] = $filters['from_date'] . ' 00:00:00';
        }
        if (!empty($filters['to_date'])) {
            $where[] = "`created_at` <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total count
        $countSql = "SELECT COUNT(*) FROM `{$this->table}` {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Items
        $sql = "SELECT id, recipient_email, recipient_name, email_type, subject, reference_type, reference_id,
                       status, attempt_count, max_attempts, next_attempt_at, sent_at, last_attempt_at, error_message, created_at
                FROM `{$this->table}` 
                {$whereClause} 
                ORDER BY id DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Check if a duplicate exists via idempotency hash
     */
    public function hasDuplicateHash(string $idempotencyHash, int $cooldownMinutes = 0): bool
    {
        if ($cooldownMinutes > 0) {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ? 
                      AND `status` IN ('sent', 'pending', 'processing')
                      AND `created_at` >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idempotencyHash, $cooldownMinutes]);
        } else {
            $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                    WHERE `idempotency_hash` = ?
                      AND `status` IN ('sent', 'pending', 'processing')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idempotencyHash]);
        }

        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Check if an entity notification was already successfully dispatched
     */
    public function hasSentForEntity(string $emailType, string $refType, int $refId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `email_type` = ? AND `reference_type` = ? AND `reference_id` = ? AND `status` = 'sent'");
        $stmt->execute([$emailType, $refType, $refId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Get distinct email types present in history for dropdown filter
     */
    public function getDistinctTypes(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT `email_type` FROM `{$this->table}` ORDER BY `email_type` ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}

