<?php
namespace App\Models;

use App\Core\Model;

class EmployeeAdvance extends Model
{
    protected string $table = 'employee_advances';
    protected array $fillable = [
        'advance_code', 'employee_id', 'amount', 'reason', 'status',
        'recovered_amount', 'requested_date', 'approved_by', 'approved_at',
        'notes', 'created_at', 'updated_at'
    ];

    public function generateAdvanceCode(): string
    {
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `employee_advances`");
        $maxId = (int)$stmt->fetchColumn();
        return sprintf('ADV-%s-%04d', date('Ym'), $maxId + 1);
    }

    public function allWithEmployee(): array
    {
        $sql = "SELECT a.*, e.employee_code, e.first_name, e.last_name, e.department, u.name as approved_by_name
                FROM `employee_advances` a
                JOIN `employees` e ON a.employee_id = e.id
                LEFT JOIN `users` u ON a.approved_by = u.id
                ORDER BY a.requested_date DESC, a.id DESC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function getPendingRecovery(int $employeeId): array
    {
        $sql = "SELECT * FROM `employee_advances` 
                WHERE `employee_id` = ? AND `status` = 'approved' AND (`amount` - `recovered_amount`) > 0
                ORDER BY `requested_date` ASC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
}
