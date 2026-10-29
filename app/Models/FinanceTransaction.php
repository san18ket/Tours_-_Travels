<?php
namespace App\Models;

use App\Core\Model;
use RuntimeException;

class FinanceTransaction extends Model
{
    protected string $table = 'finance_ledger';
    protected array $fillable = [
        'account_id', 'transaction_type', 'amount', 'balance_after',
        'category', 'reference_type', 'reference_id', 'payment_method',
        'transaction_ref', 'notes', 'created_by', 'transacted_at'
    ];

    /**
     * Record an immutable transaction entry and update account balance atomically
     */
    public function recordTransaction(array $data): int
    {
        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            $accountId = (int)$data['account_id'];
            $type = strtolower($data['transaction_type'] ?? 'debit');
            $amount = abs((float)($data['amount'] ?? 0));

            if ($amount <= 0) {
                throw new RuntimeException("Transaction amount must be greater than zero.");
            }

            // Lock account row for update
            $stmt = $pdo->prepare("SELECT `current_balance` FROM `finance_accounts` WHERE `id` = ? FOR UPDATE");
            $stmt->execute([$accountId]);
            $currentBalance = $stmt->fetchColumn();

            if ($currentBalance === false) {
                throw new RuntimeException("Specified account #{$accountId} does not exist.");
            }

            $currentBalance = (float)$currentBalance;
            $balanceAfter = ($type === 'credit') ? ($currentBalance + $amount) : ($currentBalance - $amount);

            // Update Account balance
            $upd = $pdo->prepare("UPDATE `finance_accounts` SET `current_balance` = ?, `updated_at` = NOW() WHERE `id` = ?");
            $upd->execute([$balanceAfter, $accountId]);

            // Insert into ledger
            $ins = $pdo->prepare("INSERT INTO `finance_ledger` (
                `account_id`, `transaction_type`, `amount`, `balance_after`,
                `category`, `reference_type`, `reference_id`, `payment_method`,
                `transaction_ref`, `notes`, `created_by`, `transacted_at`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $ins->execute([
                $accountId,
                $type,
                $amount,
                $balanceAfter,
                $data['category'] ?? 'General',
                $data['reference_type'] ?? null,
                $data['reference_id'] ?? null,
                $data['payment_method'] ?? 'bank_transfer',
                $data['transaction_ref'] ?? null,
                $data['notes'] ?? null,
                $data['created_by'] ?? null,
                $data['transacted_at'] ?? date('Y-m-d H:i:s')
            ]);

            $txId = (int)$pdo->lastInsertId();

            if (!$inTx) {
                $pdo->commit();
            }

            return $txId;
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getLedgerWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "l.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = "l.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }

        if (!empty($filters['category'])) {
            $where[] = "l.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(l.transaction_ref LIKE ? OR l.notes LIKE ? OR l.category LIKE ? OR a.account_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(l.transacted_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(l.transacted_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT l.*, a.account_name, a.account_type, a.bank_name,
                       u.name as creator_name
                FROM `finance_ledger` l
                JOIN `finance_accounts` a ON l.account_id = a.id
                LEFT JOIN `users` u ON l.created_by = u.id
                WHERE {$whereSql}
                ORDER BY l.transacted_at DESC, l.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countLedger(array $filters = []): int
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "l.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = "l.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }

        if (!empty($filters['category'])) {
            $where[] = "l.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(l.transaction_ref LIKE ? OR l.notes LIKE ? OR l.category LIKE ? OR a.account_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(l.transacted_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(l.transacted_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM `finance_ledger` l
                JOIN `finance_accounts` a ON l.account_id = a.id
                WHERE {$whereSql}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlyIncomeExpense(int $year): array
    {
        $sql = "SELECT 
                    MONTH(transacted_at) as month,
                    SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_expense
                FROM `finance_ledger`
                WHERE YEAR(transacted_at) = ?
                GROUP BY MONTH(transacted_at)
                ORDER BY month ASC";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}

