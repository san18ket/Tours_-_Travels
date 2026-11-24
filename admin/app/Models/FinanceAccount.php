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
                       (SELECT COUNT(*) FROM `finance_ledger` t WHERE t.account_id = a.id) as tx_count,
                       (SELECT MAX(transacted_at) FROM `finance_ledger` t WHERE t.account_id = a.id) as last_activity
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

    public function findByIdWithStats(int $id): ?array
    {
        $account = $this->find($id);
        if (!$account) {
            return null;
        }

        $pdo = $this->getPdo();

        // Inflows
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM `finance_ledger` WHERE `account_id` = ? AND `transaction_type` = 'credit'");
        $stmt->execute([$id]);
        $account['total_inflow'] = (float)$stmt->fetchColumn();

        // Outflows
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM `finance_ledger` WHERE `account_id` = ? AND `transaction_type` = 'debit'");
        $stmt->execute([$id]);
        $account['total_outflow'] = (float)$stmt->fetchColumn();

        // Total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `finance_ledger` WHERE `account_id` = ?");
        $stmt->execute([$id]);
        $account['total_transactions'] = (int)$stmt->fetchColumn();

        // Recent 30 transactions
        $stmt = $pdo->prepare("SELECT t.*, u.name as created_by_name FROM `finance_ledger` t LEFT JOIN `users` u ON t.created_by = u.id WHERE t.`account_id` = ? ORDER BY t.`transacted_at` DESC, t.`id` DESC LIMIT 30");
        $stmt->execute([$id]);
        $account['recent_transactions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Recent transfers
        $stmt = $pdo->prepare("SELECT t.*, a2.account_name as related_account_name FROM `finance_ledger` t LEFT JOIN `finance_accounts` a2 ON t.reference_id = a2.id WHERE t.`account_id` = ? AND t.`reference_type` = 'finance_transfer' ORDER BY t.`transacted_at` DESC, t.`id` DESC LIMIT 15");
        $stmt->execute([$id]);
        $account['recent_transfers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $account;
    }
}
