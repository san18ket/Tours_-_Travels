<?php
namespace App\Models;

use App\Core\Model;

class SalaryStructure extends Model
{
    protected string $table = 'salary_structures';
    protected array $fillable = [
        'employee_id', 'basic_salary', 'hra', 'transport_allowance',
        'medical_allowance', 'special_allowance', 'pf_deduction',
        'esi_deduction', 'professional_tax', 'tds_deduction',
        'other_deductions', 'gross_salary', 'net_salary',
        'effective_date', 'status'
    ];

    public function findByEmployeeId(int $employeeId): ?array
    {
        return $this->findBy('employee_id', $employeeId);
    }

    public function getAllWithEmployeeDetails(): array
    {
        $sql = "SELECT s.*, e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.designation, e.department
                FROM `salary_structures` s
                JOIN `employees` e ON s.employee_id = e.id
                ORDER BY e.first_name ASC, e.last_name ASC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
