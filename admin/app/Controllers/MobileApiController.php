<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\Dispatch;
use App\Models\DriverProfile;
use App\Models\GpsLog;
use App\Models\User;
use App\Services\GpsTrackingService;
use App\Services\DispatchService;
use PDO;
use Throwable;

class MobileApiController extends Controller
{
    private GpsTrackingService $gpsService;
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;
    private GpsLog $gpsLogModel;
    private User $userModel;
    private DispatchService $dispatchService;

    public function __construct()
    {
        $this->gpsService = new GpsTrackingService();
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
        $this->gpsLogModel = new GpsLog();
        $this->userModel = new User();
        $this->dispatchService = new DispatchService();
    }

    /**
     * POST /api/mobile/v1/auth/login
     * Mobile Driver Authentication with Device Registration
     */
    public function login(Request $request): Response
    {
        $login = trim((string)($request->input('login') ?? $request->input('username') ?? $request->input('email') ?? ''));
        $password = (string)($request->input('password') ?? '');
        $deviceUuid = trim((string)($request->input('device_uuid') ?? ''));
        $deviceName = trim((string)($request->input('device_name') ?? 'Driver Smartphone'));
        $platform = strtolower(trim((string)($request->input('platform') ?? 'android')));
        $osVersion = trim((string)($request->input('os_version') ?? ''));
        $appVersion = trim((string)($request->input('app_version') ?? '1.0.0'));

        if (empty($login) || empty($password)) {
            return $this->json([
                'success' => false,
                'error'   => 'Login credentials (username/email and password) are required.'
            ], 422);
        }

        if (empty($deviceUuid)) {
            return $this->json([
                'success' => false,
                'error'   => 'Device UUID identifier is required for native security binding.'
            ], 422);
        }

        if (!in_array($platform, ['android', 'ios'], true)) {
            $platform = 'android';
        }

        // Find user by username or email
        $user = $this->userModel->findByUsername($login);
        if (!$user) {
            $user = $this->userModel->findByEmail($login);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            Logger::security("Mobile login failed for '{$login}'", ['ip' => $request->getClientIp()]);
            return $this->json([
                'success' => false,
                'error'   => 'Invalid driver credentials provided.'
            ], 401);
        }

        if ($user['role'] !== 'driver') {
            Logger::security("Non-driver attempted mobile driver login: user #{$user['id']}");
            return $this->json([
                'success' => false,
                'error'   => 'Access restricted: Mobile application is strictly for authorized drivers.'
            ], 403);
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return $this->json([
                'success' => false,
                'error'   => 'Driver account is inactive. Please contact operations dispatch.'
            ], 403);
        }

        $driver = $this->driverModel->findByUserId((int)$user['id']);
        if (!$driver) {
            return $this->json([
                'success' => false,
                'error'   => 'Driver operational profile not found.'
            ], 404);
        }

        $driverId = (int)$driver['id'];

        // Generate cryptographically secure tokens
        $accessToken = bin2hex(random_bytes(32));
        $refreshToken = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $accessExpires = date('Y-m-d H:i:s', time() + 86400 * 3); // 3 days access token
        $refreshExpires = date('Y-m-d H:i:s', time() + 86400 * 60); // 60 days refresh token

        $pdo = Database::getConnection();

        // Check if device is already registered for this driver
        $checkStmt = $pdo->prepare("SELECT id FROM `driver_mobile_devices` WHERE `driver_id` = ? AND `device_uuid` = ? LIMIT 1");
        $checkStmt->execute([$driverId, $deviceUuid]);
        $existingDeviceId = $checkStmt->fetchColumn();

        if ($existingDeviceId) {
            $updateStmt = $pdo->prepare("UPDATE `driver_mobile_devices` SET
                `device_name` = ?,
                `platform` = ?,
                `os_version` = ?,
                `app_version` = ?,
                `access_token` = ?,
                `access_token_expires_at` = ?,
                `refresh_token` = ?,
                `refresh_token_expires_at` = ?,
                `is_revoked` = 0,
                `last_active_at` = ?,
                `updated_at` = ?
                WHERE `id` = ?");
            $updateStmt->execute([
                $deviceName,
                $platform,
                $osVersion,
                $appVersion,
                $accessToken,
                $accessExpires,
                $refreshToken,
                $refreshExpires,
                $now,
                $now,
                $existingDeviceId
            ]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO `driver_mobile_devices`
                (`driver_id`, `device_uuid`, `device_name`, `platform`, `os_version`, `app_version`, `access_token`, `access_token_expires_at`, `refresh_token`, `refresh_token_expires_at`, `is_revoked`, `last_active_at`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)");
            $insertStmt->execute([
                $driverId,
                $deviceUuid,
                $deviceName,
                $platform,
                $osVersion,
                $appVersion,
                $accessToken,
                $accessExpires,
                $refreshToken,
                $refreshExpires,
                $now,
                $now,
                $now
            ]);
        }

        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverId);
        if ($activeTrip) {
            unset($activeTrip['total_amount'], $activeTrip['fare'], $activeTrip['customer_total'], $activeTrip['invoice_total'], $activeTrip['advance_amount'], $activeTrip['paid_amount'], $activeTrip['balance_due']);
            $activeTrip['driver_trip_amount'] = ($activeTrip['driver_trip_amount'] !== null && $activeTrip['driver_trip_amount'] !== '')
                ? (float)$activeTrip['driver_trip_amount']
                : null;
            $activeTrip['display_status'] = dispatch_status_label($activeTrip['status'] ?? 'assigned');
        }

        Logger::info("Driver #{$driverId} authenticated via mobile ({$platform})", ['device_uuid' => $deviceUuid]);

        return $this->json([
            'success' => true,
            'message' => 'Authentication successful.',
            'auth' => [
                'token_type'   => 'Bearer',
                'access_token' => $accessToken,
                'expires_at'   => $accessExpires,
                'refresh_token' => $refreshToken,
                'expires_in'   => 86400 * 3
            ],
            'driver' => [
                'id'             => $driverId,
                'user_id'        => (int)$user['id'],
                'name'           => $user['name'],
                'email'          => $user['email'],
                'phone'          => $user['phone'],
                'license_number' => $driver['license_number'],
                'duty_status'    => $driver['duty_status'] ?? 'off_duty',
                'rating'         => (float)($driver['rating'] ?? 5.0)
            ],
            'active_trip' => $activeTrip ?: null
        ]);
    }

    /**
     * POST /api/mobile/v1/auth/refresh
     * Refresh Access Token Using Valid Refresh Token
     */
    public function refresh(Request $request): Response
    {
        $refreshToken = trim((string)($request->input('refresh_token') ?? ''));

        if (empty($refreshToken)) {
            return $this->json(['success' => false, 'error' => 'Refresh token is required.'], 422);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, driver_id, refresh_token_expires_at, is_revoked 
                               FROM `driver_mobile_devices` 
                               WHERE `refresh_token` = ? LIMIT 1");
        $stmt->execute([$refreshToken]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device || (int)$device['is_revoked'] === 1) {
            return $this->json(['success' => false, 'error' => 'Invalid or revoked refresh token.'], 401);
        }

        if (strtotime($device['refresh_token_expires_at']) < time()) {
            return $this->json(['success' => false, 'error' => 'Refresh token has expired. Please sign in again.'], 401);
        }

        // Rotate access token and refresh token
        $newAccessToken = bin2hex(random_bytes(32));
        $newRefreshToken = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $accessExpires = date('Y-m-d H:i:s', time() + 86400 * 3);
        $refreshExpires = date('Y-m-d H:i:s', time() + 86400 * 60);

        $updateStmt = $pdo->prepare("UPDATE `driver_mobile_devices` SET
            `access_token` = ?,
            `access_token_expires_at` = ?,
            `refresh_token` = ?,
            `refresh_token_expires_at` = ?,
            `last_active_at` = ?,
            `updated_at` = ?
            WHERE `id` = ?");
        $updateStmt->execute([
            $newAccessToken,
            $accessExpires,
            $newRefreshToken,
            $refreshExpires,
            $now,
            $now,
            $device['id']
        ]);

        return $this->json([
            'success' => true,
            'auth' => [
                'token_type'   => 'Bearer',
                'access_token' => $newAccessToken,
                'expires_at'   => $accessExpires,
                'refresh_token' => $newRefreshToken,
                'expires_in'   => 86400 * 3
            ]
        ]);
    }

    /**
     * POST /api/mobile/v1/auth/logout
     * Revoke Current Device Session
     */
    public function logout(Request $request): Response
    {
        $deviceId = (int)$request->getAttribute('auth_device_id', 0);
        if ($deviceId > 0) {
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE `driver_mobile_devices` SET `is_revoked` = 1, `updated_at` = NOW() WHERE `id` = ?")->execute([$deviceId]);
        }

        return $this->json([
            'success' => true,
            'message' => 'Logged out successfully. Device credentials revoked.'
        ]);
    }

    /**
     * GET /api/mobile/v1/driver/me
     * Current Driver Profile & Telemetry Status
     */
    public function me(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $driver = $this->driverModel->find($driverId);

        if (!$driver) {
            return $this->json(['success' => false, 'error' => 'Driver profile not found.'], 404);
        }

        $user = $this->userModel->find((int)$driver['user_id']);
        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverId);
        if ($activeTrip) {
            unset($activeTrip['total_amount'], $activeTrip['fare'], $activeTrip['customer_total'], $activeTrip['invoice_total'], $activeTrip['advance_amount'], $activeTrip['paid_amount'], $activeTrip['balance_due']);
            $activeTrip['driver_trip_amount'] = ($activeTrip['driver_trip_amount'] !== null && $activeTrip['driver_trip_amount'] !== '')
                ? (float)$activeTrip['driver_trip_amount']
                : null;
            $activeTrip['display_status'] = dispatch_status_label($activeTrip['status'] ?? 'assigned');
        }

        return $this->json([
            'success' => true,
            'driver' => [
                'id'                => $driverId,
                'name'              => $user['name'] ?? '',
                'phone'             => $user['phone'] ?? '',
                'email'             => $user['email'] ?? '',
                'license_number'    => $driver['license_number'] ?? '',
                'license_expiry'    => $driver['license_expiry'] ?? '',
                'experience_years'  => (int)($driver['experience_years'] ?? 0),
                'emergency_contact' => $driver['emergency_contact'] ?? '',
                'duty_status'       => $driver['duty_status'] ?? 'off_duty',
                'rating'            => (float)($driver['rating'] ?? 5.0),
                'current_latitude'  => $driver['current_latitude'] ? (float)$driver['current_latitude'] : null,
                'current_longitude' => $driver['current_longitude'] ? (float)$driver['current_longitude'] : null,
                'last_gps_at'       => $driver['last_gps_at'] ?? null,
            ],
            'active_trip' => $activeTrip ?: null
        ]);
    }

    /**
     * GET /api/mobile/v1/driver/trips
     * Driver Assigned & Active Trips List
     */
    public function trips(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $tripsResult = $this->dispatchModel->getDriverAssignedTrips($driverId);
        $trips = $tripsResult['data'] ?? $tripsResult;

        $formattedTrips = array_map(function($t) {
            unset($t['total_amount'], $t['fare'], $t['customer_total'], $t['invoice_total'], $t['advance_amount'], $t['paid_amount'], $t['balance_due']);
            $driverTripAmount = ($t['driver_trip_amount'] !== null && $t['driver_trip_amount'] !== '')
                ? (float)$t['driver_trip_amount']
                : null;
            $driverPaidAmount = (float)($t['driver_paid_amount'] ?? 0.0);
            $remaining = ($driverTripAmount !== null) ? round(max(0.0, $driverTripAmount - $driverPaidAmount), 2) : null;
            $payStatus = $t['driver_payment_status'] ?? 'not_due';

            $t['driver_trip_amount'] = $driverTripAmount;
            $t['driver_paid_amount'] = $driverPaidAmount;
            $t['remaining_amount'] = $remaining;
            $t['driver_payment_status'] = $payStatus;
            $t['display_payment_status'] = driver_payment_status_label($payStatus);
            $t['display_status'] = dispatch_status_label($t['status'] ?? 'assigned');
            return $t;
        }, $trips);

        return $this->json([
            'success' => true,
            'count'   => count($formattedTrips),
            'trips'   => $formattedTrips
        ]);
    }

    /**
     * GET /api/mobile/v1/driver/trips/{id}
     * Specific Trip Details for Mobile App with strict ownership assertion
     */
    public function trip(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);

        $trip = $this->dispatchModel->findForDriverWithDetails($dispatchId, $driverId);
        if (!$trip) {
            return $this->json(['success' => false, 'error' => 'Trip assignment not found or access denied.'], 404);
        }

        unset($trip['total_amount'], $trip['fare'], $trip['customer_total'], $trip['invoice_total'], $trip['advance_amount'], $trip['paid_amount'], $trip['balance_due']);
        $driverTripAmount = ($trip['driver_trip_amount'] !== null && $trip['driver_trip_amount'] !== '')
            ? (float)$trip['driver_trip_amount']
            : null;
        $driverPaidAmount = (float)($trip['driver_paid_amount'] ?? 0.0);
        $remaining = ($driverTripAmount !== null) ? round(max(0.0, $driverTripAmount - $driverPaidAmount), 2) : null;
        $payStatus = $trip['driver_payment_status'] ?? 'not_due';

        $trip['driver_trip_amount'] = $driverTripAmount;
        $trip['driver_paid_amount'] = $driverPaidAmount;
        $trip['remaining_amount'] = $remaining;
        $trip['driver_payment_status'] = $payStatus;
        $trip['display_payment_status'] = driver_payment_status_label($payStatus);
        $trip['display_status'] = dispatch_status_label($trip['status'] ?? 'assigned');

        // Include payment history vouchers for this trip
        $tripPaymentModel = new \App\Models\DriverTripPayment();
        $trip['payment_vouchers'] = $tripPaymentModel->getPaymentsByDispatch($dispatchId);

        // Include trip evidence gallery
        $evidenceService = new \App\Services\TripEvidenceService();
        $trip['evidence'] = $evidenceService->getGroupedEvidence($dispatchId);

        return $this->json([
            'success' => true,
            'trip'    => $trip,
        ]);
    }

    /**
     * POST /api/mobile/v1/driver/trips/{id}/accept
     * Driver accepts trip assignment with token authorization & IDOR protection
     */
    public function acceptTrip(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);

        try {
            $this->dispatchService->driverAccept($dispatchId, $driverId);
            return $this->json([
                'success' => true,
                'message' => 'Trip assignment accepted successfully. Operations and guest confirmed.',
                'status'  => 'driver_accepted',
            ]);
        } catch (Throwable $e) {
            $status = str_contains($e->getMessage(), 'Unauthorized') ? 403 : 422;
            return $this->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /api/mobile/v1/driver/trips/{id}/unable-to-accept
     * Driver marks unable to accept with optional reason
     */
    public function unableToAcceptTrip(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);
        $reason = trim((string)($request->input('reason') ?? $request->input('rejection_reason') ?? 'Unavailable for duty'));

        try {
            $this->dispatchService->driverUnableToAccept($dispatchId, $driverId, $reason);
            return $this->json([
                'success' => true,
                'message' => 'Response recorded. Operations notified for reassignment.',
                'status'  => 'unable_to_accept',
            ]);
        } catch (Throwable $e) {
            $status = str_contains($e->getMessage(), 'Unauthorized') ? 403 : 422;
            return $this->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /api/mobile/v1/driver/trips/{id}/evidence
     * Upload pre-trip or post-trip inspection photos with GPS and odometer
     */
    public function uploadEvidence(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);
        $type = (string)($request->input('evidence_type') ?? 'start_vehicle');
        $fileData = null;

        if ($request->hasFile('file') || $request->hasFile('photo')) {
            $fileData = $request->getFile('file') ?: $request->getFile('photo');
        } elseif (!empty($request->input('file_base64')) || !empty($request->input('image_base64'))) {
            $fileData = (string)($request->input('file_base64') ?: $request->input('image_base64'));
        }

        if (!$fileData) {
            return $this->json(['success' => false, 'error' => 'No image file or Base64 payload provided.'], 422);
        }

        $odo = $request->input('odometer_reading') ? (int)$request->input('odometer_reading') : null;
        $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;
        $notes = $request->input('notes') ? (string)$request->input('notes') : null;

        try {
            $evidenceService = new \App\Services\TripEvidenceService();
            $stored = $evidenceService->storeEvidence(
                $dispatchId,
                $driverId,
                $type,
                $fileData,
                $odo,
                $lat,
                $lng,
                $notes
            );

            return $this->json([
                'success'  => true,
                'message'  => 'Inspection evidence uploaded successfully.',
                'evidence' => $stored,
            ]);
        } catch (\Throwable $e) {
            $status = str_contains($e->getMessage(), 'Unauthorized') ? 403 : 422;
            return $this->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /api/mobile/v1/driver/trips/{id}/start
     */
    public function startTrip(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);
        $startOdo = (int)$request->input('start_odometer', 0);
        $notes = trim((string)$request->input('notes', ''));
        $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;

        if ($startOdo <= 0) {
            return $this->json(['success' => false, 'error' => 'Valid start odometer reading required.'], 422);
        }

        // Check for evidence photos in request payload
        $evidenceData = [];
        $photoPayload = $request->getFile('photo') 
            ?: ($request->getFile('start_vehicle_photo') ?: ($request->input('photo_base64') ?: $request->input('start_vehicle_base64')));
        
        if ($photoPayload) {
            $evidenceData[] = [
                'type'      => 'start_vehicle',
                'file_data' => $photoPayload,
                'latitude'  => $lat,
                'longitude' => $lng,
            ];
        }

        $odoPhotoPayload = $request->getFile('start_odometer_photo') ?: $request->input('start_odometer_base64');
        if ($odoPhotoPayload) {
            $evidenceData[] = [
                'type'      => 'start_odometer',
                'file_data' => $odoPhotoPayload,
                'latitude'  => $lat,
                'longitude' => $lng,
            ];
        }

        try {
            $this->dispatchService->startTrip($dispatchId, $driverId, $startOdo, $notes ?: null, $evidenceData);

            // Record initial GPS breadcrumb if available
            if ($lat !== null && $lng !== null) {
                try {
                    $gpsService = new \App\Services\GpsTrackingService();
                    $gpsService->recordFix($driverId, $lat, $lng, 0.0, null, 10.0, null, date('Y-m-d H:i:s'), $dispatchId);
                } catch (\Throwable $ge) {
                    \App\Core\Logger::warning("Initial GPS fix record skipped: " . $ge->getMessage());
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Trip commenced successfully. Vehicle inspection logged and telemetry active.',
                'status'  => 'trip_started',
            ]);
        } catch (Throwable $e) {
            $status = str_contains($e->getMessage(), 'Unauthorized') ? 403 : 422;
            return $this->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /api/mobile/v1/driver/trips/{id}/complete
     */
    public function completeTrip(Request $request, array $params): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $dispatchId = (int)($params['id'] ?? 0);
        $endOdo = (int)$request->input('end_odometer', 0);
        $notes = trim((string)$request->input('notes', ''));
        $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;

        if ($endOdo <= 0) {
            return $this->json(['success' => false, 'error' => 'Valid end odometer reading required.'], 422);
        }

        // Check for ending evidence photos in request payload
        $evidenceData = [];
        $photoPayload = $request->getFile('photo') 
            ?: ($request->getFile('end_vehicle_photo') ?: ($request->input('photo_base64') ?: $request->input('end_vehicle_base64')));
        
        if ($photoPayload) {
            $evidenceData[] = [
                'type'      => 'end_vehicle',
                'file_data' => $photoPayload,
                'latitude'  => $lat,
                'longitude' => $lng,
            ];
        }

        $odoPhotoPayload = $request->getFile('end_odometer_photo') ?: $request->input('end_odometer_base64');
        if ($odoPhotoPayload) {
            $evidenceData[] = [
                'type'      => 'end_odometer',
                'file_data' => $odoPhotoPayload,
                'latitude'  => $lat,
                'longitude' => $lng,
            ];
        }

        try {
            $this->dispatchService->completeTrip($dispatchId, $driverId, $endOdo, $notes ?: null, $evidenceData);

            // Record final GPS breadcrumb if available
            if ($lat !== null && $lng !== null) {
                try {
                    $gpsService = new \App\Services\GpsTrackingService();
                    $gpsService->recordFix($driverId, $lat, $lng, 0.0, null, 10.0, null, date('Y-m-d H:i:s'), $dispatchId);
                } catch (\Throwable $ge) {
                    \App\Core\Logger::warning("Final GPS fix record skipped: " . $ge->getMessage());
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Trip completed successfully. Vehicle released and ending inspection logged.',
                'status'  => 'completed',
            ]);
        } catch (Throwable $e) {
            $status = str_contains($e->getMessage(), 'Unauthorized') ? 403 : 422;
            return $this->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /api/mobile/v1/driver/earnings
     * Driver Earnings Summary
     */
    public function earnings(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $tripPaymentModel = new \App\Models\DriverTripPayment();
        $summary = $tripPaymentModel->getDriverFinancialSummary($driverId);

        return $this->json([
            'success' => true,
            'summary' => [
                'total_trips'       => $summary['total_trips'],
                'completed_trips'   => $summary['completed_trips'],
                'total_earned'      => $summary['total_earned'],
                'total_paid'        => $summary['total_paid'],
                'total_pending'     => $summary['total_pending'],
                'unspecified_count' => $summary['unspecified_count'],
            ]
        ]);
    }

    /**
     * GET /api/mobile/v1/driver/payments
     * Driver Disbursement History (Strictly authorized driver only)
     */
    public function payments(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $tripPaymentModel = new \App\Models\DriverTripPayment();
        $payments = $tripPaymentModel->getPaymentsByDriver($driverId, 50, 0);

        $formatted = array_map(function($p) {
            return [
                'id'                => (int)$p['id'],
                'payment_reference' => $p['payment_reference'],
                'dispatch_code'     => $p['dispatch_code'] ?? null,
                'booking_code'      => $p['booking_code'] ?? null,
                'amount'            => (float)$p['amount'],
                'payment_method'    => $p['payment_method'],
                'transaction_ref'   => $p['transaction_ref'],
                'payment_date'      => $p['payment_date'],
                'status'            => $p['status'],
                'notes'             => $p['notes'],
            ];
        }, $payments);

        return $this->json([
            'success'  => true,
            'count'    => count($formatted),
            'payments' => $formatted
        ]);
    }

    /**
     * GET /api/mobile/v1/driver/notifications
     * Recent Notifications for Mobile App
     */
    public function notifications(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->json(['success' => false, 'error' => 'Driver not found.'], 404);
        }

        $notificationService = new \App\Services\NotificationService();
        $notifications = $notificationService->getDriverNotifications(
            (int)$driver['user_id'],
            !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null,
            20
        );
        $unreadCount = $notificationService->getUnreadCount(
            (int)$driver['user_id'],
            !empty($driver['employee_id']) ? (int)$driver['employee_id'] : null
        );

        return $this->json([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * GET /api/mobile/v1/tracking/status
     * Tracking State and Active Telemetry Session
     */
    public function trackingStatus(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $driver = $this->driverModel->find($driverId);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `tracking_sessions` WHERE `driver_id` = ? AND `status` = 'active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$driverId]);
        $activeSession = $stmt->fetch(PDO::FETCH_ASSOC);

        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverId);
        if ($activeTrip) {
            unset($activeTrip['total_amount'], $activeTrip['fare'], $activeTrip['customer_total'], $activeTrip['invoice_total'], $activeTrip['advance_amount'], $activeTrip['paid_amount'], $activeTrip['balance_due']);
            $activeTrip['driver_trip_amount'] = ($activeTrip['driver_trip_amount'] !== null && $activeTrip['driver_trip_amount'] !== '')
                ? (float)$activeTrip['driver_trip_amount']
                : null;
            $activeTrip['display_status'] = dispatch_status_label($activeTrip['status'] ?? 'assigned');
        }

        return $this->json([
            'success'          => true,
            'duty_status'      => $driver['duty_status'] ?? 'off_duty',
            'is_tracking'      => (bool)$activeSession,
            'active_session'   => $activeSession ?: null,
            'active_trip'      => $activeTrip ?: null,
            'last_recorded_at' => $driver['last_gps_at'] ?? null,
            'telemetry_policy' => $activeTrip ? 'active_trip' : ((($driver['duty_status'] ?? '') === 'on_duty') ? 'on_duty_standby' : 'off_duty_privacy_disabled')
        ]);
    }

    /**
     * POST /api/mobile/v1/tracking/start
     * Start Background Tracking Session
     */
    public function trackingStart(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $deviceId = (int)$request->getAttribute('auth_device_id', 0);

        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->json(['success' => false, 'error' => 'Driver not found.'], 404);
        }

        // If currently off-duty, promote to on_duty
        if (($driver['duty_status'] ?? 'off_duty') === 'off_duty') {
            $this->driverModel->update($driverId, ['duty_status' => 'on_duty']);
        }

        $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver($driverId);
        if ($activeTrip) {
            unset($activeTrip['total_amount'], $activeTrip['fare'], $activeTrip['customer_total'], $activeTrip['invoice_total'], $activeTrip['advance_amount'], $activeTrip['paid_amount'], $activeTrip['balance_due']);
            $activeTrip['driver_trip_amount'] = ($activeTrip['driver_trip_amount'] !== null && $activeTrip['driver_trip_amount'] !== '')
                ? (float)$activeTrip['driver_trip_amount']
                : null;
            $activeTrip['display_status'] = dispatch_status_label($activeTrip['status'] ?? 'assigned');
        }
        $dispatchId = $activeTrip ? (int)$activeTrip['id'] : null;

        $pdo = Database::getConnection();

        // Check for existing active session
        $existingStmt = $pdo->prepare("SELECT * FROM `tracking_sessions` WHERE `driver_id` = ? AND `status` = 'active' LIMIT 1");
        $existingStmt->execute([$driverId]);
        $session = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            $sessionUuid = bin2hex(random_bytes(16));
            $now = date('Y-m-d H:i:s');
            $insertStmt = $pdo->prepare("INSERT INTO `tracking_sessions` 
                (`driver_id`, `dispatch_id`, `device_id`, `session_uuid`, `status`, `started_at`, `points_count`, `distance_km`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, 'active', ?, 0, 0.00, ?, ?)");
            $insertStmt->execute([$driverId, $dispatchId, $deviceId ?: null, $sessionUuid, $now, $now, $now]);
            $sessionId = (int)$pdo->lastInsertId();

            $session = [
                'id'           => $sessionId,
                'session_uuid' => $sessionUuid,
                'status'       => 'active',
                'started_at'   => $now,
            ];
        }

        return $this->json([
            'success' => true,
            'message' => 'Tracking session started successfully.',
            'session' => $session,
            'policy'  => $dispatchId ? 'active_trip' : 'on_duty_standby'
        ]);
    }

    /**
     * POST /api/mobile/v1/tracking/stop
     * Stop Tracking Session
     */
    public function trackingStop(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);

        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE `tracking_sessions` SET `status` = 'completed', `ended_at` = NOW(), `updated_at` = NOW() 
                       WHERE `driver_id` = ? AND `status` = 'active'")->execute([$driverId]);

        return $this->json([
            'success' => true,
            'message' => 'Tracking session completed.'
        ]);
    }

    /**
     * POST /api/mobile/v1/gps
     * Ingest Single GPS Fix or Synchronize Batch Offline Queue
     */
    public function syncGps(Request $request): Response
    {
        $driverId = (int)$request->getAttribute('auth_driver_id', 0);
        $deviceId = (int)$request->getAttribute('auth_device_id', 0);

        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->json(['success' => false, 'error' => 'Driver not found.'], 404);
        }

        // Support both single coordinate fix and batch array
        $locations = $request->input('locations');

        if (is_array($locations) && !empty($locations)) {
            // Batch mode: sort by timestamp
            usort($locations, function ($a, $b) {
                $tA = !empty($a['timestamp']) ? strtotime((string)$a['timestamp']) : 0;
                $tB = !empty($b['timestamp']) ? strtotime((string)$b['timestamp']) : 0;
                return $tA <=> $tB;
            });

            $syncedIds = [];
            $acceptedCount = 0;
            $lastFixResult = null;

            foreach ($locations as $item) {
                $lat = isset($item['latitude']) ? (float)$item['latitude'] : null;
                $lng = isset($item['longitude']) ? (float)$item['longitude'] : null;
                $pointId = $item['point_id'] ?? $item['id'] ?? null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                $speed = isset($item['speed_kmh']) ? (float)$item['speed_kmh'] : (isset($item['speed']) ? (float)$item['speed'] : 0.0);
                $heading = isset($item['heading_degrees']) ? (float)$item['heading_degrees'] : (isset($item['heading']) ? (float)$item['heading'] : null);
                $accuracy = isset($item['accuracy_m']) ? (float)$item['accuracy_m'] : (isset($item['accuracy']) ? (float)$item['accuracy'] : null);
                $battery = isset($item['battery_level']) ? (int)$item['battery_level'] : (isset($item['battery']) ? (int)$item['battery'] : null);
                $timestamp = $item['timestamp'] ?? $item['recorded_at'] ?? null;

                try {
                    $lastFixResult = $this->gpsService->recordDriverLocation(
                        $driverId,
                        $lat,
                        $lng,
                        $speed,
                        $heading,
                        $battery,
                        $accuracy,
                        $timestamp ? (string)$timestamp : null
                    );
                    $acceptedCount++;
                    if ($pointId) {
                        $syncedIds[] = $pointId;
                    }
                } catch (Throwable $e) {
                    // Skip invalid points without breaking batch
                    continue;
                }
            }

            // Update session point counter
            if ($acceptedCount > 0) {
                $pdo = Database::getConnection();
                $pdo->prepare("UPDATE `tracking_sessions` SET `points_count` = `points_count` + ?, `updated_at` = NOW() 
                               WHERE `driver_id` = ? AND `status` = 'active'")->execute([$acceptedCount, $driverId]);
            }

            return $this->json([
                'success'         => true,
                'message'         => "Synchronized {$acceptedCount} GPS fixes successfully.",
                'processed_count' => $acceptedCount,
                'synced_ids'      => $syncedIds,
                'latest_fix'      => $lastFixResult
            ]);
        }

        // Single fix mode
        $rawLat = $request->input('latitude');
        $rawLng = $request->input('longitude');

        if ($rawLat === null || $rawLng === null || !is_numeric($rawLat) || !is_numeric($rawLng)) {
            return $this->json(['success' => false, 'error' => 'Valid numerical latitude and longitude required.'], 422);
        }

        $speed = is_numeric($request->input('speed_kmh')) ? (float)$request->input('speed_kmh') : 0.0;
        $heading = is_numeric($request->input('heading_degrees')) ? (float)$request->input('heading_degrees') : null;
        $battery = is_numeric($request->input('battery_level')) ? (int)$request->input('battery_level') : null;
        $accuracy = is_numeric($request->input('accuracy')) ? (float)$request->input('accuracy') : (is_numeric($request->input('accuracy_m')) ? (float)$request->input('accuracy_m') : null);
        $timestamp = $request->input('timestamp') ?? $request->input('recorded_at') ?? null;
        $pointId = $request->input('point_id');

        try {
            $result = $this->gpsService->recordDriverLocation(
                $driverId,
                (float)$rawLat,
                (float)$rawLng,
                $speed,
                $heading,
                $battery,
                $accuracy,
                $timestamp ? (string)$timestamp : null
            );

            // Increment session counter
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE `tracking_sessions` SET `points_count` = `points_count` + 1, `updated_at` = NOW() 
                           WHERE `driver_id` = ? AND `status` = 'active'")->execute([$driverId]);

            return $this->json([
                'success'    => true,
                'message'    => 'Telemetry synchronized',
                'synced_id'  => $pointId,
                'data'       => $result
            ]);
        } catch (Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
