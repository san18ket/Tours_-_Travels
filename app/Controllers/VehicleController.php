<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\Vehicle;
use App\Models\AuditLog;
use Throwable;

class VehicleController extends Controller
{
    private Vehicle $vehicleModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->vehicleModel = new Vehicle();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Commercial Vehicle Directory with search, type filter, status filter & pagination
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $type = trim((string)$request->get('type', ''));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalVehicles = $this->vehicleModel->countFiltered($search, $type, $status);
        $vehicles = $this->vehicleModel->getPaginated($search, $type, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalVehicles / $perPage));

        // Metric summaries
        $totalAll = $this->vehicleModel->count();
        $totalAvailable = $this->vehicleModel->count("`status` = 'available'");
        $totalOnTrip = $this->vehicleModel->count("`status` = 'on_trip'");
        $totalMaintenance = $this->vehicleModel->count("`status` = 'maintenance'");

        return $this->render('office/vehicles/index', [
            'vehicles'         => $vehicles,
            'totalVehicles'    => $totalVehicles,
            'totalAll'         => $totalAll,
            'totalAvailable'   => $totalAvailable,
            'totalOnTrip'      => $totalOnTrip,
            'totalMaintenance' => $totalMaintenance,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'search'           => $search,
            'selectedType'     => $type,
            'selectedStatus'   => $status,
            'perPage'          => $perPage,
            'title'            => 'Fleet Vehicles Directory',
        ], 'layouts/office');
    }

    /**
     * Display Register New Vehicle form
     */
    public function create(Request $request): Response
    {
        return $this->render('office/vehicles/create', [
            'errors' => Session::getFlash('form_errors') ?? [],
            'old'    => Session::getFlash('form_old') ?? [],
            'title'  => 'Register Commercial Vehicle',
        ], 'layouts/office');
    }

    /**
     * Store newly registered fleet vehicle
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();

            $data = $this->validate($input, [
                'vehicle_number'   => 'required|min:4|max:30',
                'make_model'       => 'required|min:3|max:100',
                'seating_capacity' => 'required|numeric',
                'fuel_type'        => 'required|in:diesel,petrol,cng,electric',
                'odometer_km'      => 'required|numeric',
            ]);

            // Infer or default vehicle classification type if omitted from form
            $seatingCap = (int)$data['seating_capacity'];
            $vehicleType = !empty($input['vehicle_type']) && in_array($input['vehicle_type'], ['sedan', 'suv', 'tempo_traveller', 'luxury_bus', 'van'], true)
                ? $input['vehicle_type']
                : (($seatingCap >= 18) ? 'luxury_bus' : (($seatingCap >= 9) ? 'tempo_traveller' : (($seatingCap <= 4) ? 'sedan' : 'suv')));

            $cleanNumber = strtoupper(trim(preg_replace('/\s+/', ' ', $data['vehicle_number'])));
            $existing = $this->vehicleModel->findBy('vehicle_number', $cleanNumber);
            if ($existing) {
                throw new ValidationException("Commercial registration number '{$cleanNumber}' already exists in the fleet.", [
                    'vehicle_number' => ['This registration plate is already registered in the fleet.']
                ]);
            }

            // Save uploaded vehicle photo (file upload or Base64 fallback)
            $uploadedPhotoPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'vehicle_photo',
                'vehicle_photo_base64',
                'vehicles/photos',
                [
                    'image/jpeg'  => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/png'   => 'png',
                    'image/x-png' => 'png',
                    'image/webp'  => 'webp',
                ]
            );

            // Save uploaded RC / compliance document (file upload or Base64 fallback)
            $uploadedDocPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'rc_document_file',
                'rc_document_file_base64',
                'vehicles/documents',
                [
                    'image/jpeg'      => 'jpg',
                    'image/pjpeg'     => 'jpg',
                    'image/png'       => 'png',
                    'image/x-png'     => 'png',
                    'image/webp'      => 'webp',
                    'application/pdf' => 'pdf',
                ]
            );

            $docType = !empty($input['document_type']) ? trim($input['document_type']) : (!empty($uploadedDocPath) ? 'rc_book' : null);

            $vehicleId = $this->vehicleModel->create([
                'vehicle_number'      => $cleanNumber,
                'make_model'          => trim($data['make_model']),
                'vehicle_type'        => $vehicleType,
                'seating_capacity'    => (int)$data['seating_capacity'],
                'fuel_type'           => $data['fuel_type'],
                'status'              => 'available',
                'odometer_km'         => (int)$data['odometer_km'],
                'registration_expiry' => !empty($input['registration_expiry']) ? $input['registration_expiry'] : null,
                'insurance_expiry'    => !empty($input['insurance_expiry']) ? $input['insurance_expiry'] : null,
                'vehicle_photo'       => $uploadedPhotoPath,
                'rc_document_file'    => $uploadedDocPath,
                'document_type'       => $docType,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record(
                'vehicle_created',
                'vehicles',
                $vehicleId,
                null,
                ['vehicle_number' => $cleanNumber, 'make_model' => $data['make_model']],
                Session::getUserId()
            );

            return $this->redirect(url('/office/vehicles'), 'success', "Vehicle {$cleanNumber} ({$data['make_model']}) registered successfully into fleet.");

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/vehicles/create'));
        } catch (Throwable $e) {
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', 'Error registering vehicle: ' . $e->getMessage());
            return $this->redirect(url('/office/vehicles/create'));
        }
    }

    /**
     * Update vehicle readiness / maintenance status
     */
    public function updateStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');

        if (!in_array($status, ['available', 'maintenance', 'retired'], true)) {
            return $this->redirect(url('/office/vehicles'), 'danger', 'Invalid vehicle status update specified.');
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return $this->redirect(url('/office/vehicles'), 'danger', 'Vehicle not found.');
        }

        if ($vehicle['status'] === 'on_trip' && $status !== 'on_trip') {
            return $this->redirect(url('/office/vehicles'), 'danger', "Cannot change status of vehicle {$vehicle['vehicle_number']} while actively executing a trip.");
        }

        $this->vehicleModel->update($id, ['status' => $status]);

        $this->auditLog->record(
            'vehicle_status_updated',
            'vehicles',
            $id,
            ['status' => $vehicle['status']],
            ['status' => $status],
            Session::getUserId()
        );

        return $this->redirect(url('/office/vehicles'), 'success', "Vehicle {$vehicle['vehicle_number']} status updated to {$status}.");
    }

    /**
     * AJAX Toggle vehicle readiness (available <-> maintenance)
     */
    public function toggleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $vehicle = $this->vehicleModel->find($id);

        if (!$vehicle) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Vehicle not found.'], 404);
            }
            Session::setFlash('danger', 'Vehicle not found.');
            return $this->redirect(url('/office/vehicles'));
        }

        if ($vehicle['status'] === 'on_trip') {
            $msg = "Cannot toggle status of vehicle {$vehicle['vehicle_number']} while actively executing a trip.";
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $msg], 422);
            }
            Session::setFlash('danger', $msg);
            return $this->redirect(url('/office/vehicles'));
        }

        try {
            $newStatus = ($vehicle['status'] === 'available') ? 'maintenance' : 'available';
            $this->vehicleModel->update($id, ['status' => $newStatus]);

            $this->auditLog->record(
                'vehicle_status_toggled',
                'vehicles',
                $id,
                ['status' => $vehicle['status']],
                ['status' => $newStatus],
                Session::getUserId()
            );

            $msg = "Vehicle {$vehicle['vehicle_number']} status updated to " . ucfirst($newStatus) . ".";

            if ($request->isAjax()) {
                $isAvailable = ($newStatus === 'available');
                return $this->json([
                    'success'   => true,
                    'status'    => $newStatus,
                    'is_active' => $isAvailable,
                    'label'     => ucfirst($newStatus),
                    'message'   => $msg,
                    'metrics'   => $this->getVehicleMetrics(),
                ]);
            }

            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/vehicles'));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/vehicles'));
        }
    }

    /**
     * Get aggregate KPI vehicle metrics for top cards and mobile pills
     */
    private function getVehicleMetrics(): array
    {
        return [
            'total'       => $this->vehicleModel->count(),
            'available'   => $this->vehicleModel->count("`status` = 'available'"),
            'on_trip'     => $this->vehicleModel->count("`status` = 'on_trip'"),
            'maintenance' => $this->vehicleModel->count("`status` = 'maintenance'"),
        ];
    }

    /**
     * Delete vehicle, linked dispatch operations, and maintenance records
     */
    public function delete(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $vehicle = $this->vehicleModel->find($id);

        if (!$vehicle) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Vehicle not found.'], 404);
            }
            Session::setFlash('danger', 'Vehicle not found.');
            return $this->redirect(url('/office/vehicles'));
        }

        if ($vehicle['status'] === 'on_trip') {
            $msg = "Cannot delete vehicle '{$vehicle['vehicle_number']}' while actively executing a trip.";
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $msg], 422);
            }
            Session::setFlash('danger', $msg);
            return $this->redirect(url('/office/vehicles'));
        }

        try {
            $pdo = \App\Core\Database::getConnection();
            $pdo->beginTransaction();

            // 1. Delete linked dispatches
            $stmtDisp = $pdo->prepare("DELETE FROM `dispatches` WHERE `vehicle_id` = ?");
            $stmtDisp->execute([$id]);

            // 2. Delete maintenance logs
            $stmtMaint = $pdo->prepare("DELETE FROM `vehicle_maintenance` WHERE `vehicle_id` = ?");
            $stmtMaint->execute([$id]);

            // 3. Delete vehicle database record
            $this->vehicleModel->delete($id);

            $pdo->commit();

            // Clean up stored files (photo and RC document)
            $this->unlinkStoredFileIfExists($vehicle['vehicle_photo'] ?? null);
            $this->unlinkStoredFileIfExists($vehicle['rc_document_file'] ?? null);

            // 4. Record Audit Log
            try {
                $this->auditLog->record(
                    'vehicle_deleted',
                    'vehicles',
                    $id,
                    [
                        'vehicle_number' => $vehicle['vehicle_number'] ?? null,
                        'make_model'     => $vehicle['make_model'] ?? null,
                        'status'         => $vehicle['status'] ?? null,
                        'deleted_by'     => Session::getUserId(),
                    ],
                    null,
                    Session::getUserId()
                );
            } catch (\Throwable $ae) {
                // Ignore audit failure
            }

            $vehiclePlate = $vehicle['vehicle_number'] ?? "#{$id}";
            $msg = "Vehicle '{$vehiclePlate}' ({$vehicle['make_model']}) was successfully deleted from the fleet.";
            Session::setFlash('success', $msg);

            $metrics = $this->getVehicleMetrics();

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'metrics'  => $metrics,
                    'redirect' => url('/office/vehicles'),
                ]);
            }

            return $this->redirect(url('/office/vehicles'));
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            \App\Core\Logger::error('Vehicle delete error: ' . $e->getMessage(), ['vehicle_id' => $id]);
            $errMsg = 'Failed to delete vehicle: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $errMsg], 500);
            }
            Session::setFlash('danger', $errMsg);
            return $this->redirect(url('/office/vehicles'));
        }
    }

    /**
     * Delete stored upload files cleanly across private and mirrored public directories
     */
    private function unlinkStoredFileIfExists(?string $pathVal): void
    {
        if (empty($pathVal)) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $cleaned = ltrim(str_replace(['\\', '//'], '/', $pathVal), '/');

        $candidateLocations = [
            $projectRoot . '/storage/private_uploads/' . $cleaned,
            $projectRoot . '/public/assets/uploads/' . $cleaned,
            $projectRoot . '/public/storage/' . $cleaned,
            $projectRoot . '/storage/uploads/' . $cleaned,
            $projectRoot . '/public/' . ltrim($cleaned, '/'),
        ];

        if (file_exists($pathVal) && is_file($pathVal)) {
            $candidateLocations[] = $pathVal;
        }

        foreach ($candidateLocations as $filePath) {
            if (!empty($filePath) && file_exists($filePath) && is_file($filePath)) {
                try {
                    @unlink($filePath);
                } catch (\Throwable $e) {
                    // Ignore file deletion error
                }
            }
        }
    }

    /**
     * Process file upload supporting both multipart $_FILES and Base64 payloads with public mirroring
     */
    private function saveUploadedFileOrBase64(Request $request, array $input, string $fileKey, string $b64Key, string $subDir, array $allowedMimes): ?string
    {
        $savedRelPath = null;
        $projectRoot = dirname(__DIR__, 2);

        // 1. Genuine multipart file upload
        if ($request->hasFile($fileKey)) {
            try {
                $savedRelPath = $request->storeUploadedFile($fileKey, $subDir);
            } catch (\Throwable $e) {
                // Ignore upload failure
            }
        }

        // 2. Base64 fallback if file was serialized by client into hidden input
        $b64Val = $input[$b64Key] ?? null;
        if (empty($savedRelPath) && !empty($b64Val) && str_starts_with($b64Val, 'data:')) {
            try {
                $commaPos = strpos($b64Val, ',');
                if ($commaPos !== false) {
                    $header = substr($b64Val, 0, $commaPos);
                    $dataPart = substr($b64Val, $commaPos + 1);

                    if (preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64/i', $header, $m)) {
                        $mime = strtolower(trim($m[1]));
                        if ($mime === 'image/jpg') $mime = 'image/jpeg';
                        if ($mime === 'application/x-pdf') $mime = 'application/pdf';

                        if (isset($allowedMimes[$mime])) {
                            $ext = $allowedMimes[$mime];
                            $binary = base64_decode($dataPart);
                            if ($binary !== false && strlen($binary) > 0 && strlen($binary) <= 10485760) {
                                $prefix = str_contains($subDir, 'photo') ? 'veh_photo_' : 'veh_doc_';
                                $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                                $savedRelPath = trim($subDir, '/') . '/' . $filename;

                                $privDir = $projectRoot . '/storage/private_uploads/' . trim($subDir, '/');
                                if (!is_dir($privDir)) @mkdir($privDir, 0777, true);
                                file_put_contents($privDir . '/' . $filename, $binary);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore fallback failure
            }
        }

        // 3. Mirror saved file to public/assets/uploads and public/storage for direct Apache delivery
        if ($savedRelPath) {
            $source = $projectRoot . '/storage/private_uploads/' . $savedRelPath;
            if (file_exists($source)) {
                $pubUploadsFile = $projectRoot . '/public/assets/uploads/' . $savedRelPath;
                $pubUploadsDir = dirname($pubUploadsFile);
                if (!is_dir($pubUploadsDir)) @mkdir($pubUploadsDir, 0777, true);
                @copy($source, $pubUploadsFile);

                $pubStorageFile = $projectRoot . '/public/storage/' . $savedRelPath;
                $pubStorageDir = dirname($pubStorageFile);
                if (!is_dir($pubStorageDir)) @mkdir($pubStorageDir, 0777, true);
                @copy($source, $pubStorageFile);
            }
        }

        return $savedRelPath;
    }
}
