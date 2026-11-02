<?php
namespace App\Models;

use App\Core\Model;

class FinanceTransaction extends Model
{
    protected string $table = 'finance_ledger';
    protected array $fillable = [
        'account_id', 'transaction_type', 'amount', 'balance_after',
        'category', 'reference_type', 'reference_id', 'payment_method',
        'transaction_ref', 'notes', 'created_by', 'transacted_at', 'created_at'
    ];

    public function getWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "l.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }
        if (!empty($filters['type'])) {
            $where[] = "l.transaction_type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['category'])) {
            $where[] = "l.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(l.transacted_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(l.transacted_at) <= ?";
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT l.*, a.account_name, a.account_type, u.name as created_by_name
                FROM `finance_ledger` l
                JOIN `finance_accounts` a ON l.account_id = a.id
                LEFT JOIN `users` u ON l.created_by = u.id
                WHERE {$whereClause}
                ORDER BY l.transacted_at DESC, l.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countWithDetails(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "l.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }
        if (!empty($filters['type'])) {
            $where[] = "l.transaction_type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['category'])) {
            $where[] = "l.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(l.transacted_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(l.transacted_at) <= ?";
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT COUNT(*) FROM `finance_ledger` l WHERE {$whereClause}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
