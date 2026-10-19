<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Vehicle extends Model
{
    protected string $table = 'vehicles';
    protected array $fillable = [
        'vehicle_number', 'make_model', 'vehicle_type', 'seating_capacity',
        'fuel_type', 'status', 'odometer_km', 'registration_expiry', 'insurance_expiry'
    ];

    public function getAvailable(): array
    {
        return $this->where("`status` = 'available'", [], 'make_model ASC');
    }

    public function countFiltered(?string $search = null, ?string $type = null, ?string $status = null): int
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(vehicle_number LIKE ? OR make_model LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($type)) {
            $conditions[] = "vehicle_type = ?";
            $params[] = $type;
        }

        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) FROM `vehicles` WHERE " . implode(' AND ', $conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPaginated(?string $search = null, ?string $type = null, ?string $status = null, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(vehicle_number LIKE ? OR make_model LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($type)) {
            $conditions[] = "vehicle_type = ?";
            $params[] = $type;
        }

        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT * FROM `vehicles` WHERE " . implode(' AND ', $conditions) . " ORDER BY id DESC LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $paramIdx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($paramIdx++, $p, PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIdx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIdx++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
