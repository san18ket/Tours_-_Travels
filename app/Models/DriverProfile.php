<?php
namespace App\Models;

use App\Core\Model;

class DriverProfile extends Model
{
    protected string $table = 'driver_profiles';
    protected array $fillable = [
        'user_id', 'employee_id', 'license_number', 'license_expiry', 'experience_years',
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

    public function getProfileWithDetailsByUserId(int $userId): ?array
    {
        $sql = "SELECT dp.*, u.name, u.username, u.email, u.phone, u.status as user_status,
                       e.employee_code, e.department, e.designation, e.employment_type,
                       e.joining_date, e.alternate_phone, e.address, e.emergency_contact as employee_emergency_contact
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` e ON dp.employee_id = e.id
                WHERE dp.user_id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
