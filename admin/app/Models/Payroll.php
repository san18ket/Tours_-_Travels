<?php
namespace App\Models;

use App\Core\Model;

class Payroll extends Model
{
    protected string $table = 'payrolls';
    protected array $fillable = [
        'payroll_code', 'month', 'year', 'total_gross', 'total_deductions',
        'total_net', 'status', 'payment_date', 'approved_by', 'paid_by', 'notes'
    ];

    public function generatePayrollCode(int $year, int $month): string
    {
        return sprintf("PR-%04d%02d", $year, $month);
    }

    public function getPayrollsWithStats(int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                       COUNT(pi.id) as total_employees,
                       SUM(CASE WHEN pi.payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                       u.name as approver_name
                FROM `payrolls` p
                LEFT JOIN `payroll_items` pi ON p.id = pi.payroll_id
                LEFT JOIN `users` u ON p.approved_by = u.id
                GROUP BY p.id
                ORDER BY p.year DESC, p.month DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function findWithItems(int $id): ?array
    {
        $payroll = $this->find($id);
        if (!$payroll) {
            return null;
        }

        $sql = "SELECT pi.*, e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.designation, e.department
                FROM `payroll_items` pi
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE pi.payroll_id = ?
                ORDER BY e.first_name ASC, e.last_name ASC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $payroll['items'] = $stmt->fetchAll();

        return $payroll;
    }
}
