<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'role', 'status', 'language'
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', trim($email));
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', trim($username));
    }

    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $identifier = trim($identifier);
        $sql = "SELECT * FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getDrivers(): array
    {
        return $this->where("`role` = 'driver' AND `status` = 'active'", [], 'name ASC');
    }

    public function countFiltered(?string $search = null, ?string $role = null, ?string $status = null): int
    {
        [$whereSql, $params] = $this->buildFilterConditions($search, $role, $status);
        $sql = "SELECT COUNT(*) FROM `users` {$whereSql}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPaginated(?string $search = null, ?string $role = null, ?string $status = null, int $page = 1, int $perPage = 15): array
    {
        [$whereSql, $params] = $this->buildFilterConditions($search, $role, $status);
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT `id`, `name`, `username`, `email`, `phone`, `role`, `status`, `created_at`, `updated_at` 
                FROM `users` {$whereSql} 
                ORDER BY `id` DESC 
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

    private function buildFilterConditions(?string $search, ?string $role, ?string $status): array
    {
        $clauses = [];
        $params = [];

        if (!empty($search)) {
            $term = '%' . trim($search) . '%';
            $clauses[] = "(`name` LIKE ? OR `username` LIKE ? OR `email` LIKE ? OR `phone` LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($role) && in_array($role, ['admin', 'dispatcher', 'driver', 'customer'], true)) {
            $clauses[] = "`role` = ?";
            $params[] = $role;
        }

        if (!empty($status) && in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $clauses[] = "`status` = ?";
            $params[] = $status;
        }

        $whereSql = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);
        return [$whereSql, $params];
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->getPdo()->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin' AND `status` = 'active'");
        return (int)$stmt->fetchColumn();
    }

    public function isLastActiveAdmin(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user || $user['role'] !== 'admin' || $user['status'] !== 'active') {
            return false;
        }
        return $this->countActiveAdmins() <= 1;
    }

    public function updatePassword(int $userId, string $newPasswordHash): bool
    {
        return $this->update($userId, [
            'password' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function toggleStatus(int $userId): string
    {
        $user = $this->find($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $this->update($userId, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $newStatus;
    }
}
