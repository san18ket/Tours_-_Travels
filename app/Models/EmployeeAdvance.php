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
                       eu.role as linked_role,
                       u.name as approver_name
                FROM `employee_advances` a
                JOIN `employees` e ON a.employee_id = e.id
                LEFT JOIN `users` eu ON e.user_id = eu.id
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

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT a.*, e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       e.first_name, e.last_name, e.designation, e.department, e.phone as employee_phone,
                       e.email as employee_email, e.joining_date,
                       eu.role as linked_role,
                       u.name as approver_name
                FROM `employee_advances` a
                JOIN `employees` e ON a.employee_id = e.id
                LEFT JOIN `users` eu ON e.user_id = eu.id
                LEFT JOIN `users` u ON a.approved_by = u.id
                WHERE a.id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function toggleStatus(int $id, ?string $targetStatus = null, ?int $approvedBy = null): array
    {
        $adv = $this->find($id);
        if (!$adv) {
            throw new \RuntimeException("Staff advance #{$id} not found.");
        }

        $currentStatus = strtolower($adv['status'] ?? 'pending');
        $validStatuses = ['approved', 'pending', 'recovered', 'rejected'];

        if (empty($targetStatus)) {
            // Cycle sequence: pending -> approved -> recovered -> rejected -> pending
            if ($currentStatus === 'pending') {
                $targetStatus = 'approved';
            } elseif ($currentStatus === 'approved') {
                $targetStatus = 'recovered';
            } elseif ($currentStatus === 'recovered') {
                $targetStatus = 'rejected';
            } else {
                $targetStatus = 'pending';
            }
        } else {
            $targetStatus = strtolower($targetStatus);
            if (!in_array($targetStatus, $validStatuses, true)) {
                $targetStatus = 'pending';
            }
        }

        $now = date('Y-m-d H:i:s');
        $updateData = [
            'status'     => $targetStatus,
            'updated_at' => $now,
        ];

        if ($targetStatus === 'approved') {
            $updateData['approved_by'] = $approvedBy ?: ($adv['approved_by'] ?: 1);
            $updateData['approved_at'] = $adv['approved_at'] ?: $now;
        } elseif ($targetStatus === 'rejected') {
            $updateData['approved_by'] = $approvedBy ?: ($adv['approved_by'] ?: 1);
        }

        $this->update($id, $updateData);

        return [
            'success'      => true,
            'id'           => $id,
            'status'       => $targetStatus,
            'status_label' => ucfirst($targetStatus),
            'message'      => "Advance {$adv['advance_code']} marked as " . ucfirst($targetStatus) . ".",
        ];
    }

    public function deleteAdvance(int $id): bool
    {
        $adv = $this->find($id);
        if (!$adv) {
            return false;
        }
        return $this->delete($id);
    }
}
