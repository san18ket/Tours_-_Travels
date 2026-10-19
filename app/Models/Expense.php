<?php
namespace App\Models;

use App\Core\Model;

class Expense extends Model
{
    protected string $table = 'trip_expenses';
    protected array $fillable = [
        'dispatch_id', 'expense_category', 'amount', 'receipt_number',
        'notes', 'logged_by', 'incurred_at'
    ];

    public function getByDispatch(int $dispatchId): array
    {
        return $this->where("`dispatch_id` = ?", [$dispatchId], 'incurred_at ASC');
    }

    public function getTotalForDispatch(int $dispatchId): float
    {
        $sql = "SELECT SUM(amount) as total FROM `trip_expenses` WHERE `dispatch_id` = ?";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$dispatchId]);
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0.0);
    }
}
