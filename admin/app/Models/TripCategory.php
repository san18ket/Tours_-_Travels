<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class TripCategory extends Model
{
    protected string $table = 'trip_categories';
    protected array $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    /**
     * Get all active categories for booking dropdowns
     */
    public function getActiveCategories(): array
    {
        return $this->where("`status` = 'active'", [], 'sort_order ASC, name ASC');
    }

    /**
     * Get all categories for administration and filters
     */
    public function getAllCategories(): array
    {
        return $this->all('sort_order ASC, name ASC');
    }

    /**
     * Find category by unique slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Find category by name (case-insensitive)
     */
    public function findByName(string $name): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `{$this->table}` WHERE LOWER(`name`) = LOWER(?) LIMIT 1");
        $stmt->execute([trim($name)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Check if a category is currently assigned to any booking
     */
    public function isUsedInBookings(int $id): bool
    {
        $stmt = $this->getPdo()->prepare("SELECT COUNT(*) FROM `bookings` WHERE `trip_category_id` = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get all categories with booking usage count in a single JOIN query (eliminates N+1)
     */
    public function getAllWithUsageCount(): array
    {
        $pdo = $this->getPdo();
        $sql = "
            SELECT c.*, COUNT(b.id) as usage_count 
            FROM `{$this->table}` c 
            LEFT JOIN `bookings` b ON b.trip_category_id = c.id 
            GROUP BY c.id 
            ORDER BY c.sort_order ASC, c.name ASC
        ";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count how many bookings use this category
     */
    public function countBookingsUsing(int $id): int
    {
        $stmt = $this->getPdo()->prepare("SELECT COUNT(*) FROM `bookings` WHERE `trip_category_id` = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Generate URL/DB-safe slug from text
     */
    public static function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}

