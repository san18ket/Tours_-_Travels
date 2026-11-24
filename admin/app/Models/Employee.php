<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Employee extends Model
{
    protected string $table = 'employees';

    protected array $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'alternate_phone',
        'email',
        'profile_photo',
        'date_of_birth',
        'gender',
        'nationality',
        'department',
        'designation',
        'employment_type',
        'joining_date',
        'status',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'address_line',
        'city',
        'state',
        'postal_code',
        'country',
        'id_type',
        'id_number',
        'id_expiry',
        'id_document_file',
        'license_document_file',
        'notes',
        'created_at',
        'updated_at',
    ];

    /**
     * Generate luxury formatted unique employee code (EMP-XXXX)
     */
    public function generateEmployeeCode(): string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT MAX(id) FROM `employees`");
        $maxId = (int)$stmt->fetchColumn();
        $nextNum = $maxId + 1;
        $code = sprintf('EMP-%04d', $nextNum);
        while ((int)$pdo->query("SELECT COUNT(*) FROM `employees` WHERE `employee_code` = " . $pdo->quote($code))->fetchColumn() > 0) {
            $nextNum++;
            $code = sprintf('EMP-%04d', $nextNum);
        }
        return $code;
    }

    /**
     * Count employees with active filters
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
     * Get paginated employees with linked user and driver profile information
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

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT e.*, u.username as linked_username, u.role as linked_role, u.status as linked_user_status,
                       dp.id as driver_profile_id, dp.duty_status as driver_duty_status, dp.license_number
                FROM `employees` e
                LEFT JOIN `users` u ON e.user_id = u.id
                LEFT JOIN `driver_profiles` dp ON (dp.employee_id = e.id OR (e.user_id IS NOT NULL AND dp.user_id = e.user_id))
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY e.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $idx = 1;
        foreach ($params as $param) {
            $stmt->bindValue($idx++, $param);
        }
        $stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find employee with full linked information
     */
    public function findWithLinkedUser(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, u.username as linked_username, u.role as linked_role, u.status as linked_user_status,
                       dp.id as driver_profile_id, 
                       dp.license_number,
                       dp.license_expiry,
                       COALESCE(dp.experience_years, 1) as experience_years,
                       COALESCE(dp.duty_status, 'off_duty') as duty_status,
                       COALESCE(dp.rating, 5.0) as rating,
                       COALESCE(e.license_document_file, dp.license_document_file) as driver_license_doc
                FROM `employees` e
                LEFT JOIN `users` u ON e.user_id = u.id
                LEFT JOIN `driver_profiles` dp ON (dp.employee_id = e.id OR (e.user_id IS NOT NULL AND dp.user_id = e.user_id))
                WHERE e.id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Find employee with full linked details (alias for findWithLinkedUser)
     */
    public function findWithDetails(int $id): ?array
    {
        return $this->findWithLinkedUser($id);
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

    /**
     * Get aggregate counts for employee directory summary pills in a single query
     */
    public function getSummaryCounts(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_all,
                COALESCE(SUM(CASE WHEN `status` = 'active' THEN 1 ELSE 0 END), 0) as total_active,
                COALESCE(SUM(CASE WHEN `status` = 'on_leave' THEN 1 ELSE 0 END), 0) as total_on_leave,
                COALESCE(SUM(CASE WHEN `status` = 'terminated' THEN 1 ELSE 0 END), 0) as total_terminated,
                COALESCE(SUM(CASE WHEN `department` = 'fleet_chauffeur' AND `status` = 'active' THEN 1 ELSE 0 END), 0) as total_chauffeurs,
                COALESCE(SUM(CASE WHEN `department` IN ('operations', 'dispatch') AND `status` = 'active' THEN 1 ELSE 0 END), 0) as total_operations
            FROM `{$this->table}`
        ");
        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'total_all'        => (int)($data['total_all'] ?? 0),
            'total_active'     => (int)($data['total_active'] ?? 0),
            'total_on_leave'    => (int)($data['total_on_leave'] ?? 0),
            'total_terminated' => (int)($data['total_terminated'] ?? 0),
            'total_chauffeurs' => (int)($data['total_chauffeurs'] ?? 0),
            'total_operations' => (int)($data['total_operations'] ?? 0),
        ];
    }
}
