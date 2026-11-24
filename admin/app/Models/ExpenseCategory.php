<?php
namespace App\Models;

use App\Core\Model;

class ExpenseCategory extends Model
{
    protected string $table = 'expense_categories';
    protected array $fillable = [
        'slug', 'name', 'description', 'is_default', 'status'
    ];

    /**
     * Get all active categories ordered by default then name
     */
    public function getActiveCategories(): array
    {
        return $this->where("status = 'active'", [], 'is_default DESC, name ASC');
    }

    /**
     * Get slug to name mapping array for all active categories
     */
    public function getCategoryMap(): array
    {
        $cats = $this->getActiveCategories();
        $map = [];
        foreach ($cats as $c) {
            $map[$c['slug']] = $c['name'];
        }
        return $map;
    }

    /**
     * Get slug to name mapping array for ALL categories (active and inactive)
     * Used for historical expense displays
     */
    public function getAllCategoryMap(): array
    {
        $cats = $this->all('is_default DESC, name ASC');
        $map = [];
        foreach ($cats as $c) {
            $map[$c['slug']] = $c['name'];
        }
        return $map;
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Toggle or set status ('active' | 'inactive')
     */
    public function toggleStatus(int $id, ?string $desiredStatus = null): string
    {
        $category = $this->find($id);
        if (!$category) {
            throw new \RuntimeException('Expense category not found.');
        }

        if ($desiredStatus !== null && in_array($desiredStatus, ['active', 'inactive'], true)) {
            $newStatus = $desiredStatus;
        } else {
            $newStatus = ($category['status'] === 'active') ? 'inactive' : 'active';
        }

        $this->update($id, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $newStatus;
    }

    /**
     * Check if a category can be safely deleted.
     * Prevents deletion if:
     *  1. Marked as system default (is_default = 1)
     *  2. Financial expense records belong to it (business_expenses.category = slug)
     *
     * @return array{can_delete: bool, reason: string, is_default: bool, vouchers: int, total_spent: float}
     */
    public function canDelete(int $id): array
    {
        $category = $this->find($id);
        if (!$category) {
            return [
                'can_delete'  => false,
                'reason'      => 'Category not found.',
                'is_default'  => false,
                'vouchers'    => 0,
                'total_spent' => 0.0,
            ];
        }

        if (!empty($category['is_default'])) {
            return [
                'can_delete'  => false,
                'reason'      => "System default category '{$category['name']}' cannot be deleted. You can deactivate it instead.",
                'is_default'  => true,
                'vouchers'    => 0,
                'total_spent' => 0.0,
            ];
        }

        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total FROM `business_expenses` WHERE `category` = ?");
        $stmt->execute([$category['slug']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $vouchers = (int)($row['cnt'] ?? 0);
        $total = (float)($row['total'] ?? 0);

        if ($vouchers > 0) {
            return [
                'can_delete'  => false,
                'reason'      => "Cannot delete category '{$category['name']}' because {$vouchers} expense voucher(s) totaling ₹" . number_format($total, 2) . " are linked to it. Deactivate the category instead to preserve historical accounting records.",
                'is_default'  => false,
                'vouchers'    => $vouchers,
                'total_spent' => $total,
            ];
        }

        return [
            'can_delete'  => true,
            'reason'      => '',
            'is_default'  => false,
            'vouchers'    => 0,
            'total_spent' => 0.0,
        ];
    }
}
