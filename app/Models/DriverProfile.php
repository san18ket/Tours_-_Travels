<?php
namespace App\Models;

use App\Core\Model;

class DriverProfile extends Model
{
    protected string $table = 'driver_profiles';
    protected array $fillable = [
        'user_id', 'license_number', 'license_expiry', 'experience_years',
        'emergency_contact', 'duty_status', 'rating', 'current_latitude', 'current_longitude', 'last_gps_at'
    ];

    public function findByUserId(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    public function getAllWithUserInfo(): array
    {
        $sql = "SELECT dp.*, u.name, u.email, u.phone, u.status as user_status 
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                ORDER BY u.name ASC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    public function getAvailableDrivers(): array
    {
        $sql = "SELECT dp.*, u.name, u.email, u.phone 
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                WHERE dp.duty_status IN ('on_duty', 'off_duty') AND u.status = 'active'
                ORDER BY u.name ASC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
