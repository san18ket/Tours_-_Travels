<?php
namespace App\Models;

use App\Core\Model;
use RuntimeException;

class FinanceAccount extends Model
{
    protected string $table = 'finance_accounts';
    protected array $fillable = [
        'account_name', 'account_number', 'bank_name', 'ifsc_code',
        'account_type', 'opening_balance', 'current_balance', 'status'
    ];

    public function getActiveAccounts(): array
    {
        return $this->where("`status` = 'active'", [], 'account_type ASC, account_name ASC');
    }

    public function getTotalLiquidBalance(): float
    {
        $stmt = $this->getPdo()->query("SELECT COALESCE(SUM(current_balance), 0) FROM `finance_accounts` WHERE `status` = 'active'");
        return (float)$stmt->fetchColumn();
    }

    public function updateBalance(int $accountId, float $deltaAmount, string $type): float
    {
        $account = $this->find($accountId);
        if (!$account) {
            throw new RuntimeException("Finance Account #{$accountId} not found.");
        }

        $current = (float)$account['current_balance'];
        $newBalance = ($type === 'credit') ? ($current + $deltaAmount) : ($current - $deltaAmount);

        $stmt = $this->getPdo()->prepare("UPDATE `finance_accounts` SET `current_balance` = ?, `updated_at` = NOW() WHERE `id` = ?");
        $stmt->execute([$newBalance, $accountId]);

        return $newBalance;
    }

    public function getAccountsWithFilters(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "a.account_type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(a.account_name LIKE ? OR a.bank_name LIKE ? OR a.account_number LIKE ? OR a.ifsc_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT a.*,
                       (SELECT COUNT(*) FROM `finance_transactions` t WHERE t.account_id = a.id) as tx_count,
                       (SELECT MAX(transacted_at) FROM `finance_transactions` t WHERE t.account_id = a.id) as last_activity
                FROM `finance_accounts` a
                WHERE {$whereSql}
                ORDER BY a.status ASC, a.account_type ASC, a.current_balance DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countAccounts(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "a.account_type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(a.account_name LIKE ? OR a.bank_name LIKE ? OR a.account_number LIKE ? OR a.ifsc_code LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM `finance_accounts` a WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
