<?php
namespace App\Models;

use App\Core\Model;

class PayrollItem extends Model
{
    protected string $table = 'payroll_items';
    protected array $fillable = [
        'payroll_id', 'employee_id', 'payslip_number', 'basic_salary',
        'allowances_total', 'gross_salary', 'deductions_total',
        'advance_deducted', 'net_salary', 'payment_status',
        'payment_method', 'payment_ref', 'paid_at', 'notes',
        'created_at', 'updated_at'
    ];

    public function getByPayroll(int $payrollId): array
    {
        $sql = "SELECT pi.*, e.employee_code, e.first_name, e.last_name, e.department, e.designation, e.phone, e.email
                FROM `payroll_items` pi
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE pi.payroll_id = ?
                ORDER BY e.first_name ASC";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$payrollId]);
        return $stmt->fetchAll();
    }

    public function findWithFullDetails(int $id): ?array
    {
        $sql = "SELECT pi.*, p.payroll_code, p.month, p.year, p.status as batch_status,
                       e.employee_code, e.first_name, e.last_name, e.department, e.designation,
                       e.phone, e.email, e.joining_date,
                       ss.hra, ss.transport_allowance, ss.medical_allowance, ss.special_allowance,
                       ss.pf_deduction, ss.esi_deduction, ss.professional_tax, ss.tds_deduction, ss.other_deductions
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                LEFT JOIN `salary_structures` ss ON ss.employee_id = e.id
                WHERE pi.id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPayslipNumber(string $number): ?array
    {
        $sql = "SELECT pi.*, p.payroll_code, p.month, p.year,
                       e.employee_code, e.first_name, e.last_name, e.department, e.designation,
                       e.phone, e.email, e.joining_date,
                       ss.hra, ss.transport_allowance, ss.medical_allowance, ss.special_allowance,
                       ss.pf_deduction, ss.esi_deduction, ss.professional_tax, ss.tds_deduction, ss.other_deductions
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                LEFT JOIN `salary_structures` ss ON ss.employee_id = e.id
                WHERE pi.payslip_number = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$number]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
