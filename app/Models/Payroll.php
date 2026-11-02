<?php
namespace App\Models;

use App\Core\Model;

class Payroll extends Model
{
    protected string $table = 'payrolls';
    protected array $fillable = [
        'payroll_code', 'month', 'year', 'total_gross', 'total_deductions',
        'total_net', 'status', 'payment_date', 'approved_by', 'paid_by',
        'notes', 'created_at', 'updated_at'
    ];

    public function generatePayrollCode(int $month, int $year): string
    {
        return sprintf('PAY-%04d%02d', $year, $month);
    }

    public function findByPeriod(int $month, int $year): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `payrolls` WHERE `month` = ? AND `year` = ? LIMIT 1");
        $stmt->execute([$month, $year]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allWithDetails(): array
    {
        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM `payroll_items` pi WHERE pi.payroll_id = p.id) as employee_count,
                       u1.name as approved_by_name,
                       u2.name as paid_by_name
                FROM `payrolls` p
                LEFT JOIN `users` u1 ON p.approved_by = u1.id
                LEFT JOIN `users` u2 ON p.paid_by = u2.id
                ORDER BY p.year DESC, p.month DESC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
