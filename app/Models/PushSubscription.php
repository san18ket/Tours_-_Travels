<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class PushSubscription extends Model
{
    protected string $table = 'push_subscriptions';

    protected array $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'p256dh',
        'auth',
        'content_encoding',
        'device_name',
        'last_used_at',
        'revoked_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Register or update a push subscription
     */
    public function saveSubscription(
        int $userId,
        string $endpoint,
        string $p256dh,
        string $auth,
        string $contentEncoding = 'aes128gcm',
        ?string $deviceName = null
    ): int {
        $endpointHash = hash('sha256', $endpoint);
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare("SELECT id, user_id FROM `{$this->table}` WHERE `endpoint_hash` = ? LIMIT 1");
        $stmt->execute([$endpointHash]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $updateSql = "UPDATE `{$this->table}` 
                          SET `user_id` = ?, 
                              `endpoint` = ?, 
                              `p256dh` = ?, 
                              `auth` = ?, 
                              `content_encoding` = ?, 
                              `device_name` = COALESCE(?, `device_name`), 
                              `revoked_at` = NULL, 
                              `updated_at` = NOW() 
                          WHERE `id` = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                $userId,
                $endpoint,
                $p256dh,
                $auth,
                $contentEncoding,
                $deviceName,
                $existing['id']
            ]);

            return (int)$existing['id'];
        }

        $insertSql = "INSERT INTO `{$this->table}` 
                      (`user_id`, `endpoint`, `endpoint_hash`, `p256dh`, `auth`, `content_encoding`, `device_name`, `created_at`, `updated_at`) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $userId,
            $endpoint,
            $endpointHash,
            $p256dh,
            $auth,
            $contentEncoding,
            $deviceName
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Retrieve all active (non-revoked) subscriptions for a specific user
     */
    public function getActiveForUser(int $userId): array
    {
        $stmt = $this->getPdo()->prepare(
            "SELECT * FROM `{$this->table}` 
             WHERE `user_id` = ? AND `revoked_at` IS NULL 
             ORDER BY `created_at` DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all active subscriptions for a list of user IDs
     */
    public function getActiveForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $userIds = array_map('intval', array_filter($userIds));
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->getPdo()->prepare(
            "SELECT * FROM `{$this->table}` 
             WHERE `user_id` IN ({$placeholders}) AND `revoked_at` IS NULL 
             ORDER BY `created_at` DESC"
        );
        $stmt->execute(array_values($userIds));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a user has any active subscriptions
     */
    public function hasActiveSubscription(int $userId): bool
    {
        $stmt = $this->getPdo()->prepare(
            "SELECT COUNT(*) FROM `{$this->table}` 
             WHERE `user_id` = ? AND `revoked_at` IS NULL"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Revoke a subscription by ID (optionally scoped to user)
     */
    public function revoke(int $id, ?int $userId = null): bool
    {
        $sql = "UPDATE `{$this->table}` SET `revoked_at` = NOW() WHERE `id` = ?";
        $params = [$id];

        if ($userId !== null) {
            $sql .= " AND `user_id` = ?";
            $params[] = $userId;
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Revoke a subscription by its endpoint (used when push service returns 404/410)
     */
    public function revokeByEndpoint(string $endpoint): bool
    {
        $endpointHash = hash('sha256', $endpoint);
        $stmt = $this->getPdo()->prepare(
            "UPDATE `{$this->table}` SET `revoked_at` = NOW() WHERE `endpoint_hash` = ?"
        );
        $stmt->execute([$endpointHash]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a subscription permanently (optionally scoped to user)
     */
    public function deleteSubscription(int $id, ?int $userId = null): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = ?";
        $params = [$id];

        if ($userId !== null) {
            $sql .= " AND `user_id` = ?";
            $params[] = $userId;
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update last_used_at timestamp on successful delivery
     */
    public function markUsed(int $id): void
    {
        $stmt = $this->getPdo()->prepare(
            "UPDATE `{$this->table}` SET `last_used_at` = NOW() WHERE `id` = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Purge old revoked subscriptions
     */
    public function cleanupExpired(int $days = 30): int
    {
        $stmt = $this->getPdo()->prepare(
            "DELETE FROM `{$this->table}` 
             WHERE `revoked_at` IS NOT NULL 
               AND `revoked_at` < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }
}
