<?php
namespace App\Services;

use App\Models\Dispatch;
use App\Models\DriverProfile;
use App\Models\GpsLog;
use App\Core\Logger;
use RuntimeException;

class GpsTrackingService
{
    private Dispatch $dispatchModel;
    private DriverProfile $driverModel;
    private GpsLog $gpsLogModel;

    public function __construct()
    {
        $this->dispatchModel = new Dispatch();
        $this->driverModel = new DriverProfile();
        $this->gpsLogModel = new GpsLog();
    }

    /**
     * Validate and record live GPS coordinates strictly for authorized active assignments
     */
    public function recordDriverLocation(int $driverProfileId, float $latitude, float $longitude, ?float $speed = 0.0, ?float $heading = null, ?int $battery = null): array
    {
        // 1. Boundary & Coordinate Realism Validation
        if ($latitude < -90.0 || $latitude > 90.0 || $longitude < -180.0 || $longitude > 180.0) {
            throw new RuntimeException("Invalid geospatial coordinates supplied.");
        }

        if ($latitude == 0.0 && $longitude == 0.0) {
            throw new RuntimeException("Null island (0, 0) coordinates are rejected.");
        }

        // 2. Validate GPS submission against authorized active assignments
        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverProfileId);
        if (!$activeTrip) {
            Logger::security("Rejected GPS submission: Driver profile {$driverProfileId} does not have an active authorized trip assignment.");
            throw new RuntimeException("GPS location update rejected: No authorized active trip found for this chauffeur.");
        }

        // 3. Record in GPS logs
        $now = date('Y-m-d H:i:s');
        $logId = $this->gpsLogModel->create([
            'dispatch_id'     => $activeTrip['id'],
            'driver_id'       => $driverProfileId,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'speed_kmh'       => max(0.0, (float)$speed),
            'heading_degrees' => $heading,
            'battery_level'   => $battery,
            'recorded_at'     => $now,
        ]);

        // 4. Update Driver Profile live coordinate
        $this->driverModel->update($driverProfileId, [
            'current_latitude'  => $latitude,
            'current_longitude' => $longitude,
            'last_gps_at'       => $now,
        ]);

        return [
            'success'     => true,
            'dispatch_id' => $activeTrip['id'],
            'recorded_at' => $now,
            'log_id'      => $logId
        ];
    }
}
