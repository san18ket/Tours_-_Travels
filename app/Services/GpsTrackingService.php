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
     * Validate and record live GPS coordinates strictly for authorized drivers.
     * Policy 1: Active Trip - Chauffeur is actively executing or assigned to a dispatch.
     * Policy 2: On-Duty Standby - Chauffeur is on-duty available for assignment.
     * Rejection: Chauffeur is off-duty (privacy preservation) or invalid coordinates.
     */
    public function recordDriverLocation(
        int $driverProfileId,
        float $latitude,
        float $longitude,
        ?float $speed = 0.0,
        ?float $heading = null,
        ?int $battery = null,
        ?float $accuracy = null,
        ?string $recordedAt = null
    ): array
    {
        // 1. Boundary & Coordinate Realism Validation
        if ($latitude < -90.0 || $latitude > 90.0 || $longitude < -180.0 || $longitude > 180.0) {
            throw new RuntimeException("Invalid geospatial coordinates supplied: Latitude or longitude out of bounds.");
        }

        if (abs($latitude) < 0.000001 && abs($longitude) < 0.000001) {
            throw new RuntimeException("Null island (0, 0) coordinates are rejected.");
        }

        // Validate accuracy realism (reject if extreme, e.g. > 5000m)
        if ($accuracy !== null && ($accuracy < 0 || $accuracy > 5000.0)) {
            throw new RuntimeException("GPS accuracy figure is outside acceptable threshold.");
        }

        // Normalize speed and heading boundaries
        $cleanSpeed = max(0.0, min(250.0, (float)($speed ?? 0.0)));
        $cleanHeading = $heading !== null ? fmod(fmod((float)$heading, 360.0) + 360.0, 360.0) : null;
        $cleanBattery = $battery !== null ? max(0, min(100, (int)$battery)) : null;

        // 2. Fetch driver profile & verify duty status / authorization
        $driver = $this->driverModel->find($driverProfileId);
        if (!$driver) {
            throw new RuntimeException("Chauffeur profile not found.");
        }

        $dutyStatus = $driver['duty_status'] ?? 'off_duty';

        // 3. Preserve chauffeur privacy: Strictly reject off-duty location tracking
        if ($dutyStatus === 'off_duty') {
            Logger::security("GPS submission rejected: Driver profile {$driverProfileId} is off-duty.");
            throw new RuntimeException("Location sharing is disabled: Chauffeur is off duty. Switch to On Duty to transmit location.");
        }

        // 4. Device vs Server Timestamp Handling
        $now = date('Y-m-d H:i:s');
        $serverTimestamp = time();
        $deviceTime = $now;

        if (!empty($recordedAt)) {
            $parsedDeviceTime = strtotime($recordedAt);
            if ($parsedDeviceTime !== false) {
                // Reject fixes claimed to be more than 5 minutes in future (anti-tamper)
                if ($parsedDeviceTime > ($serverTimestamp + 300)) {
                    $deviceTime = $now;
                }
                // Reject fixes older than 7 days (excessively stale)
                elseif ($parsedDeviceTime < ($serverTimestamp - 7 * 86400)) {
                    throw new RuntimeException("GPS fix timestamp is excessively stale (> 7 days).");
                } else {
                    $deviceTime = date('Y-m-d H:i:s', $parsedDeviceTime);
                }
            }
        }

        // 5. Determine tracking policy and active assignment context
        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverProfileId);
        $dispatchId = null;
        $policy = 'on_duty_standby';

        if ($activeTrip) {
            $dispatchId = (int)$activeTrip['id'];
            $policy = 'active_trip';
        }

        // 6. Record breadcrumb in GPS logs table
        $logId = $this->gpsLogModel->create([
            'dispatch_id'     => $dispatchId,
            'driver_id'       => $driverProfileId,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'accuracy_m'      => $accuracy !== null ? round((float)$accuracy, 2) : null,
            'speed_kmh'       => $cleanSpeed,
            'heading_degrees' => $cleanHeading,
            'battery_level'   => $cleanBattery,
            'recorded_at'     => $deviceTime,
            'created_at'      => $now,
        ]);

        // 7. Update Driver Profile live coordinates if point is not out-of-order
        $lastGpsTime = !empty($driver['last_gps_at']) ? strtotime($driver['last_gps_at']) : 0;
        $currentFixTime = strtotime($deviceTime);
        $isLatestFix = ($currentFixTime >= $lastGpsTime);

        if ($isLatestFix) {
            $this->driverModel->update($driverProfileId, [
                'current_latitude'  => $latitude,
                'current_longitude' => $longitude,
                'last_gps_at'       => $deviceTime,
            ]);
        }

        return [
            'success'        => true,
            'policy'         => $policy,
            'dispatch_id'    => $dispatchId,
            'booking_code'   => $activeTrip['booking_code'] ?? null,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'accuracy'       => $accuracy !== null ? round((float)$accuracy, 2) : null,
            'recorded_at'    => $deviceTime,
            'received_at'    => $now,
            'is_current_fix' => $isLatestFix,
            'formatted_time' => date('h:i:s A', strtotime($deviceTime)),
            'log_id'         => $logId
        ];
    }
}
