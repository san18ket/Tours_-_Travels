<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class CompanyProfile extends Model
{
    protected string $table = 'company_profiles';

    protected array $fillable = [
        'company_name',
        'tagline',
        'logo',
        'email',
        'phone',
        'website',
        'address',
        'status',
        'is_primary',
        'created_at',
        'updated_at'
    ];

    /**
     * Create a profile and ensure single primary logic if is_primary is set
     */
    public function create(array $data): int
    {
        if (!empty($data['is_primary'])) {
            $this->getPdo()->exec("UPDATE `{$this->table}` SET `is_primary` = 0");
        }
        return parent::create($data);
    }

    /**
     * Get the single primary company profile for this installation
     */
    public function getPrimary(): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `{$this->table}` WHERE `is_primary` = 1 LIMIT 1");
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            // Fallback to first record if none marked primary
            $stmt = $this->getPdo()->prepare("SELECT * FROM `{$this->table}` ORDER BY `id` ASC LIMIT 1");
            $stmt->execute();
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $record ?: null;
    }

    /**
     * Check if a primary company profile already exists in this installation
     */
    public function hasPrimary(): bool
    {
        $stmt = $this->getPdo()->query("SELECT COUNT(*) FROM `{$this->table}`");
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Set a specific profile as primary, demoting all other records
     */
    public function setPrimary(int $id): bool
    {
        $pdo = $this->getPdo();
        $pdo->beginTransaction();
        try {
            $pdo->exec("UPDATE `{$this->table}` SET `is_primary` = 0");
            $stmt = $pdo->prepare("UPDATE `{$this->table}` SET `is_primary` = 1 WHERE `id` = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Search and paginate company profiles
     */
    public function allPaginated(string $search = '', int $limit = 10, int $offset = 0): array
    {
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $params = [];
        $whereSql = "";
        if (!empty($search)) {
            $whereSql = "WHERE `company_name` LIKE ? OR `email` LIKE ? OR `phone` LIKE ? OR `website` LIKE ? OR `address` LIKE ?";
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term, $term];
        }

        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY `is_primary` DESC, `id` ASC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count total company profiles matching criteria
     */
    public function countTotal(string $search = ''): int
    {
        $params = [];
        $whereSql = "";
        if (!empty($search)) {
            $whereSql = "WHERE `company_name` LIKE ? OR `email` LIKE ? OR `phone` LIKE ? OR `website` LIKE ? OR `address` LIKE ?";
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term, $term];
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}` {$whereSql}";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
