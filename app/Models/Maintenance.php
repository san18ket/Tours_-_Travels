<?php
namespace App\Models;

use App\Core\Model;

class Maintenance extends Model
{
    protected string $table = 'vehicle_maintenance';
    protected array $fillable = [
        'vehicle_id', 'maintenance_type', 'odometer_km', 'cost',
        'service_center', 'description', 'performed_at',
        'next_service_due_km', 'created_by'
    ];

    public function getByVehicle(int $vehicleId): array
    {
        return $this->where("`vehicle_id` = ?", [$vehicleId], 'performed_at DESC');
    }
}
