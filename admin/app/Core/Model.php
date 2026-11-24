<?php
namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    protected function getPdo(): PDO
    {
        return Database::getConnection();
    }

    public function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1";
        return Profiler::profileQuery($sql, function() use ($sql, $id) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        }, [$id]);
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        // Whitelist order by to prevent SQL injection in sorting
        $cleanOrder = preg_replace('/[^a-zA-Z0-9_, ]/', '', $orderBy);
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$cleanOrder}";
        return Profiler::profileQuery($sql, function() use ($sql) {
            $stmt = $this->getPdo()->query($sql);
            return $stmt->fetchAll();
        });
    }

    public function where(string $sqlCondition, array $params = [], string $orderBy = 'id DESC', ?int $limit = null, ?int $offset = null): array
    {
        $cleanOrder = preg_replace('/[^a-zA-Z0-9_, ]/', '', $orderBy);
        $limitClause = '';
        if ($limit !== null) {
            $limitClause = " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $limitClause .= " OFFSET " . (int)$offset;
            }
        }
        $sql = "SELECT * FROM `{$this->table}` WHERE {$sqlCondition} ORDER BY {$cleanOrder}{$limitClause}";
        return Profiler::profileQuery($sql, function() use ($sql, $params) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }, $params);
    }

    public function findBy(string $column, mixed $value): ?array
    {
        $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$cleanCol}` = ? LIMIT 1";
        return Profiler::profileQuery($sql, function() use ($sql, $value) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([$value]);
            $row = $stmt->fetch();
            return $row ?: null;
        }, [$value]);
    }

    public function count(string $sqlCondition = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE {$sqlCondition}";
        return (int) Profiler::profileQuery($sql, function() use ($sql, $params) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        }, $params);
    }

    public function create(array $data): int
    {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $key => $val) {
            if (empty($this->fillable) || in_array($key, $this->fillable, true)) {
                $fields[] = "`" . preg_replace('/[^a-zA-Z0-9_]/', '', $key) . "`";
                $placeholders[] = '?';
                $values[] = $val;
            }
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException('No valid fillable fields provided for creation.');
        }

        $sql = "INSERT INTO `{$this->table}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        return (int) Profiler::profileQuery($sql, function() use ($sql, $values) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($values);
            return (int) $this->getPdo()->lastInsertId();
        }, $values);
    }

    public function update(int|string $id, array $data): bool
    {
        $assignments = [];
        $values = [];

        foreach ($data as $key => $val) {
            if (empty($this->fillable) || in_array($key, $this->fillable, true)) {
                $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
                $assignments[] = "`{$cleanCol}` = ?";
                $values[] = $val;
            }
        }

        if (empty($assignments)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $assignments) . " WHERE `{$this->primaryKey}` = ?";
        return (bool) Profiler::profileQuery($sql, function() use ($sql, $values) {
            $stmt = $this->getPdo()->prepare($sql);
            return $stmt->execute($values);
        }, $values);
    }

    public function delete(int|string $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        return (bool) Profiler::profileQuery($sql, function() use ($sql, $id) {
            $stmt = $this->getPdo()->prepare($sql);
            return $stmt->execute([$id]);
        }, [$id]);
    }

    public function paginate(int $page = 1, int $perPage = 15, string $sqlCondition = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $total = $this->count($sqlCondition, $params);
        $totalPages = (int) ceil($total / $perPage);

        $cleanOrder = preg_replace('/[^a-zA-Z0-9_, ]/', '', $orderBy);
        $sql = "SELECT * FROM `{$this->table}` WHERE {$sqlCondition} ORDER BY {$cleanOrder} LIMIT {$perPage} OFFSET {$offset}";
        $items = Profiler::profileQuery($sql, function() use ($sql, $params) {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }, $params);

        return [
            'items'        => $items,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'total_pages'  => $totalPages,
            'has_next'     => $page < $totalPages,
            'has_prev'     => $page > 1,
        ];
    }
}
