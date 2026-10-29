<?php
namespace App\Models;

use App\Core\Model;

class PayrollItem extends Model
{
    protected string $table = 'payroll_items';
    protected array $fillable = [
        'payroll_id', 'employee_id', 'payslip_number', 'basic_salary',
        'allowances_total', 'gross_salary', 'deductions_total', 'advance_deducted',
        'net_salary', 'payment_status', 'payment_method', 'payment_ref', 'paid_at', 'notes'
    ];

    public function generatePayslipNumber(int $year, int $month, int $employeeId): string
    {
        return sprintf("MAN-PAY-%04d%02d-%04d", $year, $month, $employeeId);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT pi.*, p.month, p.year, p.payroll_code,
                       e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.designation, e.department,
                       e.phone as employee_phone, e.email as employee_email,
                       ss.hra, ss.transport_allowance, ss.medical_allowance, ss.special_allowance,
                       ss.pf_deduction, ss.esi_deduction, ss.professional_tax, ss.tds_deduction, ss.other_deductions
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                LEFT JOIN `salary_structures` ss ON e.id = ss.employee_id
                WHERE pi.id = ?
                LIMIT 1";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
