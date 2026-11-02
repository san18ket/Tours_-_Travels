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
        'effective_date', 'status', 'created_at', 'updated_at'
    ];

    public function getByEmployee(int $employeeId): ?array
    {
        return $this->findBy('employee_id', $employeeId);
    }

    public function allWithEmployee(): array
    {
        $sql = "SELECT s.*, e.employee_code, e.first_name, e.last_name, e.department, e.designation, e.phone
                FROM `salary_structures` s
                JOIN `employees` e ON s.employee_id = e.id
                ORDER BY e.first_name ASC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
