<?php
namespace App\Models;

use App\Core\Model;

class BusinessExpense extends Model
{
    protected string $table = 'business_expenses';
    protected array $fillable = [
        'expense_code', 'category', 'title', 'amount', 'expense_date',
        'payment_method', 'account_id', 'vendor_name', 'receipt_file',
        'status', 'logged_by', 'approved_by', 'notes', 'created_at', 'updated_at'
    ];

    public function generateExpenseCode(): string
    {
        $stmt = $this->getPdo()->query("SELECT MAX(id) FROM `business_expenses`");
        $maxId = (int)$stmt->fetchColumn();
        return sprintf('EXP-%s-%04d', date('Ym'), $maxId + 1);
    }

    public function allWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "b.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $where[] = "b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "b.expense_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "b.expense_date <= ?";
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT b.*, fa.account_name, u.name as logged_by_name, ua.name as approved_by_name
                FROM `business_expenses` b
                LEFT JOIN `finance_accounts` fa ON b.account_id = fa.id
                LEFT JOIN `users` u ON b.logged_by = u.id
                LEFT JOIN `users` ua ON b.approved_by = ua.id
                WHERE {$whereClause}
                ORDER BY b.expense_date DESC, b.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "b.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $where[] = "b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "b.expense_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "b.expense_date <= ?";
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT COUNT(*) FROM `business_expenses` b WHERE {$whereClause}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
