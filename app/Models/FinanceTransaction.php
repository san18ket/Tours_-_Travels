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
        'transaction_ref', 'notes', 'status', 'created_by', 'transacted_at'
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
                `transaction_ref`, `notes`, `status`, `created_by`, `transacted_at`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

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
                $data['status'] ?? 'completed',
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

    /**
     * Transfer funds between two company accounts atomically
     */
    public function transferFunds(array $data): array
    {
        $pdo = $this->getPdo();
        $fromId = (int)($data['from_account_id'] ?? 0);
        $toId   = (int)($data['to_account_id'] ?? 0);
        $amount = abs((float)($data['amount'] ?? 0));
        $ref    = trim((string)($data['transfer_ref'] ?? ('TRF-' . date('Ymd-His'))));
        $notes  = trim((string)($data['notes'] ?? 'Internal account transfer'));
        $userId = $data['created_by'] ?? null;
        $now    = date('Y-m-d H:i:s');

        if ($fromId <= 0 || $toId <= 0 || $fromId === $toId) {
            throw new RuntimeException("Two distinct valid accounts must be specified for transfer.");
        }
        if ($amount <= 0) {
            throw new RuntimeException("Transfer amount must be greater than zero.");
        }

        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            // Check balance
            $stmt = $pdo->prepare("SELECT current_balance, account_name FROM finance_accounts WHERE id = ? FOR UPDATE");
            $stmt->execute([$fromId]);
            $fromAcc = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$fromAcc) {
                throw new RuntimeException("Source account not found.");
            }
            if ((float)$fromAcc['current_balance'] < $amount) {
                throw new RuntimeException("Insufficient balance in {$fromAcc['account_name']}.");
            }

            $stmt->execute([$toId]);
            $toAcc = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$toAcc) {
                throw new RuntimeException("Destination account not found.");
            }

            // 1. Debit from source
            $debitId = $this->recordTransaction([
                'account_id'       => $fromId,
                'transaction_type' => 'debit',
                'amount'           => $amount,
                'category'         => 'Internal Transfer',
                'reference_type'   => 'finance_transfer',
                'reference_id'     => $toId,
                'payment_method'   => 'bank_transfer',
                'transaction_ref'  => $ref,
                'notes'            => "Transfer to {$toAcc['account_name']}. " . $notes,
                'created_by'       => $userId,
                'transacted_at'    => $now,
            ]);

            // 2. Credit to destination
            $creditId = $this->recordTransaction([
                'account_id'       => $toId,
                'transaction_type' => 'credit',
                'amount'           => $amount,
                'category'         => 'Internal Transfer',
                'reference_type'   => 'finance_transfer',
                'reference_id'     => $fromId,
                'payment_method'   => 'bank_transfer',
                'transaction_ref'  => $ref,
                'notes'            => "Transfer received from {$fromAcc['account_name']}. " . $notes,
                'created_by'       => $userId,
                'transacted_at'    => $now,
            ]);

            if (!$inTx) {
                $pdo->commit();
            }

            return [
                'debit_tx_id'  => $debitId,
                'credit_tx_id' => $creditId,
                'transfer_ref' => $ref,
                'from_account' => $fromAcc['account_name'],
                'to_account'   => $toAcc['account_name'],
            ];
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

        if (!empty($filters['status'])) {
            $where[] = "l.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(l.transaction_ref LIKE ? OR l.notes LIKE ? OR l.category LIKE ? OR a.account_name LIKE ? OR a.bank_name LIKE ?)";
            $params[] = $term;
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

        if (!empty($filters['status'])) {
            $where[] = "l.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(l.transaction_ref LIKE ? OR l.notes LIKE ? OR l.category LIKE ? OR a.account_name LIKE ? OR a.bank_name LIKE ?)";
            $params[] = $term;
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
                    SUM(CASE WHEN transaction_type = 'credit' AND category != 'Internal Transfer' AND (reference_type IS NULL OR reference_type != 'finance_transfer') THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN transaction_type = 'debit' AND category != 'Internal Transfer' AND (reference_type IS NULL OR reference_type != 'finance_transfer') THEN amount ELSE 0 END) as total_expense
                FROM `finance_ledger`
                WHERE YEAR(transacted_at) = ? AND status IN ('completed', 'active')
                GROUP BY MONTH(transacted_at)
                ORDER BY month ASC";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT l.*, a.account_name, a.account_type, a.bank_name, a.account_number, a.ifsc_code,
                       u.name as creator_name
                FROM `finance_ledger` l
                JOIN `finance_accounts` a ON l.account_id = a.id
                LEFT JOIN `users` u ON l.created_by = u.id
                WHERE l.id = ?";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateTransaction(int $id, array $data): bool
    {
        $tx = $this->find($id);
        if (!$tx) {
            return false;
        }

        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            $oldStatus = strtolower($tx['status'] ?? 'completed');
            $newStatus = isset($data['status']) ? strtolower($data['status']) : $oldStatus;

            // If status changed between completed and cancelled, adjust account balance
            if ($oldStatus !== $newStatus) {
                $accountId = (int)$tx['account_id'];
                $amount = (float)$tx['amount'];
                $isCredit = ($tx['transaction_type'] === 'credit');

                if ($oldStatus === 'completed' && in_array($newStatus, ['cancelled', 'inactive', 'pending'], true)) {
                    // Reversing transaction from active balance
                    $diff = $isCredit ? (-$amount) : (+$amount);
                    $upd = $pdo->prepare("UPDATE `finance_accounts` SET `current_balance` = `current_balance` + ?, `updated_at` = NOW() WHERE `id` = ?");
                    $upd->execute([$diff, $accountId]);
                } elseif (in_array($oldStatus, ['cancelled', 'inactive', 'pending'], true) && in_array($newStatus, ['completed', 'active'], true)) {
                    // Re-applying transaction to active balance
                    $diff = $isCredit ? (+$amount) : (-$amount);
                    $upd = $pdo->prepare("UPDATE `finance_accounts` SET `current_balance` = `current_balance` + ?, `updated_at` = NOW() WHERE `id` = ?");
                    $upd->execute([$diff, $accountId]);
                }
            }

            $sql = "UPDATE `finance_ledger` SET 
                        `category` = ?,
                        `payment_method` = ?,
                        `transaction_ref` = ?,
                        `notes` = ?,
                        `status` = ?,
                        `transacted_at` = ?
                    WHERE `id` = ?";

            $stmt = $pdo->prepare($sql);
            $res = $stmt->execute([
                $data['category'] ?? $tx['category'],
                $data['payment_method'] ?? $tx['payment_method'],
                $data['transaction_ref'] ?? $tx['transaction_ref'],
                $data['notes'] ?? $tx['notes'],
                $newStatus,
                $data['transacted_at'] ?? $tx['transacted_at'],
                $id
            ]);

            if (!$inTx) {
                $pdo->commit();
            }

            return $res;
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deleteTransaction(int $id): bool
    {
        $tx = $this->find($id);
        if (!$tx) {
            return false;
        }

        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            $status = strtolower($tx['status'] ?? 'completed');
            if (in_array($status, ['completed', 'active'], true)) {
                $accountId = (int)$tx['account_id'];
                $amount = (float)$tx['amount'];
                $isCredit = ($tx['transaction_type'] === 'credit');

                // Reverse effect: credit (-) subtracts from account, debit (+) restores to account
                $diff = $isCredit ? (-$amount) : (+$amount);
                $upd = $pdo->prepare("UPDATE `finance_accounts` SET `current_balance` = `current_balance` + ?, `updated_at` = NOW() WHERE `id` = ?");
                $upd->execute([$diff, $accountId]);
            }

            $del = $pdo->prepare("DELETE FROM `finance_ledger` WHERE `id` = ?");
            $res = $del->execute([$id]);

            if (!$inTx) {
                $pdo->commit();
            }

            return $res;
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function toggleStatus(int $id, ?string $targetStatus = null): array
    {
        $tx = $this->find($id);
        if (!$tx) {
            throw new RuntimeException("Transaction #{$id} not found.");
        }

        $currentStatus = strtolower($tx['status'] ?? 'completed');
        if (empty($targetStatus)) {
            // Cycle sequence: completed -> pending -> cancelled -> completed
            if ($currentStatus === 'completed' || $currentStatus === 'active') {
                $targetStatus = 'pending';
            } elseif ($currentStatus === 'pending') {
                $targetStatus = 'cancelled';
            } else {
                $targetStatus = 'completed';
            }
        } else {
            $targetStatus = strtolower($targetStatus);
            if (!in_array($targetStatus, ['completed', 'pending', 'cancelled'], true)) {
                $targetStatus = 'completed';
            }
        }

        $this->updateTransaction($id, [
            'status' => $targetStatus
        ]);

        $statusText = strtoupper($targetStatus);
        $label = ucfirst($targetStatus);

        return [
            'success'    => true,
            'status'     => $targetStatus,
            'status_txt' => $statusText,
            'label'      => $label,
            'message'    => "Transaction status updated to {$label}."
        ];
    }

    /**
     * Compute aggregated ledger summary totals using a single fast indexed SQL query
     */
    public function getLedgerSummaryTotals(): array
    {
        $sql = "SELECT 
                    COUNT(*) AS total_count,
                    COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) AS total_credits,
                    COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) AS total_debits
                FROM `{$this->table}`";
        $row = $this->getPdo()->query($sql)->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_count'   => (int)($row['total_count'] ?? 0),
            'total_credits' => (float)($row['total_credits'] ?? 0.0),
            'total_debits'  => (float)($row['total_debits'] ?? 0.0),
        ];
    }
}
