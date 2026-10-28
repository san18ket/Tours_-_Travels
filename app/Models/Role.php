<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Role extends Model
{
    protected string $table = 'roles';

    /**
     * Generate unique slug from role name
     */
    public function generateSlug(string $name): string
    {
        $base = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $base);
        $slug = trim($slug, '_');

        if (empty($slug)) {
            $slug = 'custom_role';
        }

        // Ensure uniqueness
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `roles` WHERE `slug` = ?");
        $stmt->execute([$slug]);
        $count = (int)$stmt->fetchColumn();

        if ($count > 0) {
            $slug .= '_' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        return $slug;
    }

    /**
     * Check if role name is already taken
     */
    public function isRoleNameTaken(string $name, int $excludeId = 0): bool
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id FROM `roles` WHERE LOWER(`name`) = LOWER(?)";
        $params = [trim($name)];
        if ($excludeId > 0) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Count filtered roles
     */
    public function countFiltered(?string $search = null, ?string $status = null, ?string $type = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(r.name LIKE ? OR r.slug LIKE ? OR r.description LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = "r.status = ?";
            $params[] = $status;
        }

        if ($type === 'system') {
            $conditions[] = "r.is_system = 1";
        } elseif ($type === 'custom') {
            $conditions[] = "r.is_system = 0";
        }

        $sql = "SELECT COUNT(*) FROM `roles` r WHERE " . implode(' AND ', $conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Retrieve paginated roles with assigned user count and permissions count
     */
    public function getPaginated(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 10, ?string $type = null): array
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(r.name LIKE ? OR r.slug LIKE ? OR r.description LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = "r.status = ?";
            $params[] = $status;
        }

        if ($type === 'system') {
            $conditions[] = "r.is_system = 1";
        } elseif ($type === 'custom') {
            $conditions[] = "r.is_system = 0";
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM `users` u WHERE u.role = r.slug) AS user_count,
                       (SELECT COUNT(*) FROM `role_permissions` rp WHERE rp.role_id = r.id) AS permission_count 
                FROM `roles` r 
                WHERE " . implode(' AND ', $conditions) . " 
                ORDER BY r.is_system DESC, r.name ASC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve single role with comprehensive stats
     */
    public function getRoleWithStats(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM `users` u WHERE u.role = r.slug) AS user_count,
                       (SELECT COUNT(*) FROM `role_permissions` rp WHERE rp.role_id = r.id) AS permission_count 
                FROM `roles` r 
                WHERE r.id = ? 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    /**
     * Get all active assignable roles
     */
    public function getAssignableRoles(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM `users` u WHERE u.role = r.slug) AS user_count,
                       (SELECT COUNT(*) FROM `role_permissions` rp WHERE rp.role_id = r.id) AS permission_count 
                FROM `roles` r 
                WHERE r.status = 'active' 
                ORDER BY r.is_system DESC, r.name ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve permission IDs for a specific role
     */
    public function getPermissionIdsForRole(int $roleId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT permission_id FROM `role_permissions` WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Group permissions by module
     */
    public function getPermissionsGrouped(): array
    {
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT * FROM `permissions` ORDER BY module ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];
        foreach ($rows as $p) {
            $mod = $p['module'] ?: 'other';
            $grouped[$mod][] = $p;
        }
        return $grouped;
    }

    /**
     * Synchronize permissions for a role in a transaction
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = Database::getConnection();
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', $permissionIds), static fn(int $id) => $id > 0)));

        if (!empty($permissionIds)) {
            $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
            $validStmt = $pdo->prepare("SELECT id FROM `permissions` WHERE id IN ({$placeholders})");
            $validStmt->execute($permissionIds);
            $validIds = array_map('intval', $validStmt->fetchAll(PDO::FETCH_COLUMN));
            $permissionIds = array_values(array_intersect($permissionIds, $validIds));
        }

        $pdo->beginTransaction();
        try {
            $delStmt = $pdo->prepare("DELETE FROM `role_permissions` WHERE role_id = ?");
            $delStmt->execute([$roleId]);

            if (!empty($permissionIds)) {
                $insStmt = $pdo->prepare("INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, CURRENT_TIMESTAMP)");
                foreach ($permissionIds as $permId) {
                    $insStmt->execute([$roleId, $permId]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve aggregate operational metrics for top cards
     */
    public function getMetrics(): array
    {
        $pdo = Database::getConnection();

        $totalRoles = (int)$pdo->query("SELECT COUNT(*) FROM `roles`")->fetchColumn();
        $activeRoles = (int)$pdo->query("SELECT COUNT(*) FROM `roles` WHERE `status` = 'active'")->fetchColumn();
        $customRoles = (int)$pdo->query("SELECT COUNT(*) FROM `roles` WHERE `is_system` = 0")->fetchColumn();
        $totalUsersAssigned = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE `role` IN (SELECT `slug` FROM `roles`)")->fetchColumn();
        $totalPermissions = (int)$pdo->query("SELECT COUNT(*) FROM `role_permissions`")->fetchColumn();

        return [
            'total_roles'          => $totalRoles,
            'active_roles'         => $activeRoles,
            'custom_roles'         => $customRoles,
            'total_users_assigned' => $totalUsersAssigned,
            'total_permissions'    => $totalPermissions,
        ];
    }
}
