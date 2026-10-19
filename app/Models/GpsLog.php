<?php
namespace App\Models;

use App\Core\Model;

class GpsLog extends Model
{
    protected string $table = 'gps_logs';
    protected array $fillable = [
        'dispatch_id', 'driver_id', 'latitude', 'longitude',
        'speed_kmh', 'heading_degrees', 'battery_level', 'recorded_at'
    ];

    public function getLatestForDispatch(int $dispatchId, int $limit = 50): array
    {
        return $this->where("`dispatch_id` = ?", [$dispatchId], 'recorded_at DESC', $limit);
    }
}
