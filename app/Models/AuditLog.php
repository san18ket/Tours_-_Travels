<?php
namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected array $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'old_values', 'new_values', 'ip_address', 'user_agent'
    ];

    public function record(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null, ?int $userId = null): int
    {
        return $this->create([
            'user_id'     => $userId ?? \App\Core\Session::getUserId(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_SLASHES) : null,
            'new_values'  => $newValues ? json_encode($newValues, JSON_UNESCAPED_SLASHES) : null,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function getRecent(int $limit = 100): array
    {
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email, u.role as user_role
                FROM `audit_logs` a
                LEFT JOIN `users` u ON a.user_id = u.id
                ORDER BY a.id DESC
                LIMIT {$limit}";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
