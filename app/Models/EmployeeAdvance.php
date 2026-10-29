<?php
namespace App\Models;

use App\Core\Model;

class EmployeeAdvance extends Model
{
    protected string $table = 'employee_advances';
    protected array $fillable = [
        'advance_code', 'employee_id', 'amount', 'reason', 'status',
        'recovered_amount', 'requested_date', 'approved_by', 'approved_at', 'notes'
    ];

    public function generateAdvanceCode(): string
    {
        $prefix = 'ADV-' . date('Ym') . '-';
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `employee_advances`");
        $next = ((int)$stmt->fetchColumn()) + 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function getAdvancesWithEmployeeDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = "a.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(a.advance_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT a.*, e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.designation, e.department,
                       u.name as approver_name
                FROM `employee_advances` a
                JOIN `employees` e ON a.employee_id = e.id
                LEFT JOIN `users` u ON a.approved_by = u.id
                WHERE {$whereSql}
                ORDER BY a.requested_date DESC, a.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAdvances(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = "a.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(a.advance_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `employee_advances` a
                JOIN `employees` e ON a.employee_id = e.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPendingAdvanceForEmployee(int $employeeId): float
    {
        $stmt = $this->getPdo()->prepare("SELECT COALESCE(SUM(amount - recovered_amount), 0) FROM `employee_advances` WHERE `employee_id` = ? AND `status` = 'approved'");
        $stmt->execute([$employeeId]);
        return max(0.0, (float)$stmt->fetchColumn());
    }
}
