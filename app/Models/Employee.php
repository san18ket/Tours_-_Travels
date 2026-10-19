<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Employee extends Model
{
    protected string $table = 'employees';

    /**
     * Generate luxury formatted unique employee code (VG-EMP-XXXX)
     */
    public function generateEmployeeCode(): string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT MAX(id) FROM `employees`");
        $maxId = (int)$stmt->fetchColumn();
        $nextNum = $maxId + 1;
        return sprintf('VG-EMP-%04d', $nextNum);
    }

    /**
     * Count employees with optional search, department and status filters
     */
    public function countFiltered(?string $search = null, ?string $department = null, ?string $status = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.phone LIKE ? OR e.email LIKE ? OR e.designation LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if (!empty($department)) {
            $conditions[] = "e.department = ?";
            $params[] = $department;
        }

        if (!empty($status)) {
            $conditions[] = "e.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) FROM `employees` e WHERE " . implode(' AND ', $conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Retrieve paginated employee records with linked user account info
     */
    public function getPaginated(?string $search = null, ?string $department = null, ?string $status = null, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.phone LIKE ? OR e.email LIKE ? OR e.designation LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
        }

        if (!empty($department)) {
            $conditions[] = "e.department = ?";
            $params[] = $department;
        }

        if (!empty($status)) {
            $conditions[] = "e.status = ?";
            $params[] = $status;
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT e.*, u.username as linked_username, u.role as linked_role, u.status as linked_user_status,
                       dp.id as driver_profile_id, dp.license_number, dp.duty_status
                FROM `employees` e
                LEFT JOIN `users` u ON e.user_id = u.id
                LEFT JOIN `driver_profiles` dp ON dp.employee_id = e.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY e.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find single employee with full profile join
     */
    public function findWithDetails(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, u.username as linked_username, u.role as linked_role, u.status as linked_user_status,
                       dp.id as driver_profile_id, dp.license_number, dp.license_expiry, dp.experience_years, dp.duty_status, dp.rating
                FROM `employees` e
                LEFT JOIN `users` u ON e.user_id = u.id
                LEFT JOIN `driver_profiles` dp ON dp.employee_id = e.id
                WHERE e.id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get active chauffeurs for operations assignment
     */
    public function getActiveChauffeurs(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.phone,
                       dp.id as driver_profile_id, dp.duty_status, dp.license_number
                FROM `employees` e
                JOIN `driver_profiles` dp ON dp.employee_id = e.id
                WHERE e.status = 'active'
                ORDER BY e.first_name ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
