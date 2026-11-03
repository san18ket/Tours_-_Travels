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
     * Get slug to name mapping array
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
     * Find category by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }
}
