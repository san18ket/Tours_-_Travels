<?php
namespace App\Models;

use App\Core\Model;

class BusinessExpense extends Model
{
    protected string $table = 'business_expenses';
    protected array $fillable = [
        'expense_code', 'category', 'title', 'amount', 'expense_date',
        'payment_method', 'account_id', 'vendor_name', 'receipt_file',
        'status', 'logged_by', 'approved_by', 'notes'
    ];

    public function generateExpenseCode(): string
    {
        $prefix = 'EXP-' . date('Ym') . '-';
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `business_expenses`");
        $next = ((int)$stmt->fetchColumn()) + 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function getExpensesWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "e.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $where[] = "e.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['account_id'])) {
            $where[] = "e.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(e.expense_code LIKE ? OR e.title LIKE ? OR e.vendor_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "e.expense_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "e.expense_date <= ?";
            $params[] = $filters['date_to'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT e.*, a.account_name, u.name as logged_by_name
                FROM `business_expenses` e
                LEFT JOIN `finance_accounts` a ON e.account_id = a.id
                LEFT JOIN `users` u ON e.logged_by = u.id
                WHERE {$whereSql}
                ORDER BY e.expense_date DESC, e.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countExpenses(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(expense_code LIKE ? OR title LIKE ? OR vendor_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $stmt = $this->getPdo()->prepare("SELECT COUNT(*) FROM `business_expenses` WHERE {$whereSql}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getTotalExpensesByCategory(int $year, ?int $month = null): array
    {
        $where = ["YEAR(expense_date) = ? AND status = 'paid'"];
        $params = [$year];

        if ($month !== null) {
            $where[] = "MONTH(expense_date) = ?";
            $params[] = $month;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT category, SUM(amount) as total_amount, COUNT(*) as count
                FROM `business_expenses`
                WHERE {$whereSql}
                GROUP BY category
                ORDER BY total_amount DESC";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

