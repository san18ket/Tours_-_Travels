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
                       e.phone as employee_phone, e.email as employee_email, e.bank_name, e.bank_account_no, e.bank_ifsc,
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

    /**
     * Get paginated payslips with employee & payroll cycle details
     */
    public function getPayslipsWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['month'])) {
            $where[] = "p.month = ?";
            $params[] = (int)$filters['month'];
        }

        if (!empty($filters['year'])) {
            $where[] = "p.year = ?";
            $params[] = (int)$filters['year'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "pi.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = "pi.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(pi.payslip_number LIKE ? OR p.payroll_code LIKE ? OR e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT pi.*, p.month, p.year, p.payroll_code, p.status as batch_status,
                       e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       e.designation, e.department, e.phone as employee_phone
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE {$whereSql}
                ORDER BY p.year DESC, p.month DESC, e.first_name ASC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPayslips(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['month'])) {
            $where[] = "p.month = ?";
            $params[] = (int)$filters['month'];
        }

        if (!empty($filters['year'])) {
            $where[] = "p.year = ?";
            $params[] = (int)$filters['year'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "pi.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = "pi.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(pi.payslip_number LIKE ? OR p.payroll_code LIKE ? OR e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get paginated salary disbursement history (paid payslips)
     */
    public function getSalaryHistory(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["pi.payment_status = 'paid'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(pi.paid_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(pi.paid_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(pi.payment_ref LIKE ? OR pi.payslip_number LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT pi.*, p.month, p.year, p.payroll_code,
                       e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       e.designation, e.department
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE {$whereSql}
                ORDER BY pi.paid_at DESC, pi.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSalaryHistory(array $filters = []): int
    {
        $where = ["pi.payment_status = 'paid'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(pi.paid_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(pi.paid_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(pi.payment_ref LIKE ? OR pi.payslip_number LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `payroll_items` pi
                JOIN `payrolls` p ON pi.payroll_id = p.id
                JOIN `employees` e ON pi.employee_id = e.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
