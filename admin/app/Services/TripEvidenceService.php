<?php
namespace App\Services;

use App\Models\TripEvidence;
use App\Models\Dispatch;
use App\Core\Config;
use App\Core\Logger;
use RuntimeException;
use InvalidArgumentException;

class TripEvidenceService
{
    private TripEvidence $evidenceModel;
    private Dispatch $dispatchModel;

    public function __construct()
    {
        $this->evidenceModel = new TripEvidence();
        $this->dispatchModel = new Dispatch();
    }

    /**
     * Store inspection evidence from multipart $_FILES or Base64 payload
     */
    public function storeEvidence(
        int $dispatchId,
        int $driverId,
        string $evidenceType,
        array|string $fileData,
        ?int $odometerReading = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        ?string $capturedAt = null
    ): array {
        // Validate evidence type
        $allowedTypes = ['start_odometer', 'start_vehicle', 'end_odometer', 'end_vehicle', 'additional'];
        if (!in_array($evidenceType, $allowedTypes, true)) {
            throw new InvalidArgumentException("Invalid evidence type '{$evidenceType}'. Permitted types: " . implode(', ', $allowedTypes));
        }

        // Validate dispatch ownership
        $dispatch = $this->dispatchModel->find($dispatchId);
        if (!$dispatch) {
            throw new RuntimeException("Dispatch record #{$dispatchId} not found.");
        }
        if ((int)$dispatch['driver_id'] !== $driverId) {
            throw new RuntimeException("Unauthorized: This trip assignment does not belong to driver #{$driverId}.");
        }

        // Process file or base64
        $processed = $this->processFileData($fileData, $dispatchId, $evidenceType);

        // Extract binary data upfront into memory to guarantee persistence against move/permission issues
        $binary = $processed['binary'] ?? null;
        if ($binary === null && !empty($processed['tmp_name'])) {
            if (@is_uploaded_file($processed['tmp_name']) || @file_exists($processed['tmp_name'])) {
                $binary = @file_get_contents($processed['tmp_name']) ?: null;
                if ($binary === null) {
                    $handle = @fopen($processed['tmp_name'], 'rb');
                    if ($handle) {
                        $binary = @stream_get_contents($handle) ?: null;
                        @fclose($handle);
                    }
                }
            }
        }

        $projectRoot = dirname(__DIR__, 2);
        $subDir = dirname($processed['rel_path']); // e.g. 'trip_evidence'
        $fileName = basename($processed['rel_path']);

        // Candidate storage directories in order of preference
        $candidateDirs = [
            $projectRoot . '/public/storage/' . $subDir,
            $projectRoot . '/storage/' . $subDir,
            $projectRoot . '/storage/uploads/' . $subDir,
            $projectRoot . '/public/assets/uploads/' . $subDir,
            sys_get_temp_dir() . '/tours_travels/' . $subDir,
        ];

        $savedPath = null;
        $savedDir = null;

        // Try candidate directories until file is securely written
        foreach ($candidateDirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            if (is_dir($dir)) {
                @chmod($dir, 0777);
            }

            $targetPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            $success = false;

            // Tier 1: move_uploaded_file (if valid HTTP upload)
            if (!empty($processed['tmp_name']) && is_uploaded_file($processed['tmp_name'])) {
                if (@move_uploaded_file($processed['tmp_name'], $targetPath)) {
                    $success = true;
                }
            }

            // Tier 2: standard copy
            if (!$success && !empty($processed['tmp_name']) && file_exists($processed['tmp_name'])) {
                if (@copy($processed['tmp_name'], $targetPath)) {
                    $success = true;
                }
            }

            // Tier 3: write direct binary memory buffer
            if (!$success && $binary !== null) {
                if (@file_put_contents($targetPath, $binary, LOCK_EX) !== false) {
                    $success = true;
                }
            }

            // Tier 4: stream write
            if (!$success && $binary !== null) {
                $fp = @fopen($targetPath, 'wb');
                if ($fp !== false) {
                    $written = @fwrite($fp, $binary);
                    @fclose($fp);
                    if ($written !== false && $written > 0) {
                        $success = true;
                    }
                }
            }

            if ($success && file_exists($targetPath) && filesize($targetPath) > 0) {
                @chmod($targetPath, 0666);
                $savedPath = $targetPath;
                $savedDir = $dir;
                break;
            }
        }

        if (!$savedPath) {
            Logger::error("TripEvidenceService: Failed to write evidence photo to any candidate directory for dispatch #{$dispatchId}. Candidates: " . implode(', ', $candidateDirs));
            throw new RuntimeException("Failed to securely store uploaded evidence image.");
        }

        // Mirror the saved file across other available candidate directories (e.g. public/storage <-> storage)
        if ($binary === null && $savedPath && file_exists($savedPath)) {
            $binary = @file_get_contents($savedPath) ?: null;
        }

        if ($binary !== null) {
            foreach ($candidateDirs as $syncDir) {
                if ($syncDir !== $savedDir) {
                    if (!is_dir($syncDir)) {
                        @mkdir($syncDir, 0777, true);
                    }
                    if (is_dir($syncDir)) {
                        $syncTarget = rtrim($syncDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
                        if (!file_exists($syncTarget)) {
                            @file_put_contents($syncTarget, $binary, LOCK_EX);
                            if (file_exists($syncTarget)) {
                                @chmod($syncTarget, 0666);
                            }
                        }
                    }
                }
            }
        }

        $data = [
            'dispatch_id'       => $dispatchId,
            'driver_id'         => $driverId,
            'evidence_type'     => $evidenceType,
            'file_path'         => $processed['rel_path'],
            'original_filename' => $processed['original_filename'],
            'mime_type'         => $processed['mime_type'],
            'file_size'         => $processed['file_size'],
            'odometer_reading'  => $odometerReading,
            'captured_at'       => $capturedAt ?: date('Y-m-d H:i:s'),
            'uploaded_at'       => date('Y-m-d H:i:s'),
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'notes'             => $notes ? trim($notes) : null,
        ];

        $id = $this->evidenceModel->create($data);
        $record = $this->evidenceModel->find($id);

        Logger::info("Trip evidence #{$id} recorded for dispatch #{$dispatchId} [{$evidenceType}]");

        return $this->evidenceModel->formatEvidence($record);
    }

    /**
     * Process file data from $_FILES array or Base64 string
     */
    private function processFileData(array|string $fileData, int $dispatchId, string $type): array
    {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/pjpeg'=> 'jpg',
            'image/png'  => 'png',
            'image/x-png'=> 'png',
            'image/webp' => 'webp',
        ];
        $maxBytes = 10 * 1024 * 1024; // 10 MB

        // Case A: Standard PHP $_FILES array
        if (is_array($fileData) && isset($fileData['tmp_name'])) {
            if (!empty($fileData['error']) && $fileData['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("Upload failed with error code: " . $fileData['error']);
            }

            if (!is_uploaded_file($fileData['tmp_name']) && !file_exists($fileData['tmp_name'])) {
                throw new RuntimeException("No valid file uploaded.");
            }

            $fileSize = (int)$fileData['size'];
            if ($fileSize > $maxBytes) {
                throw new InvalidArgumentException("Evidence photo exceeds maximum size limit of 10MB.");
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = strtolower((string)$finfo->file($fileData['tmp_name']));

            if (!isset($allowedMimes[$mime])) {
                throw new InvalidArgumentException("Unsupported image format ({$mime}). Only JPG, PNG, and WebP photos are permitted.");
            }

            $ext = $allowedMimes[$mime];
            $origName = basename($fileData['name'] ?? 'evidence.' . $ext);
            $safeName = 'ev_' . $dispatchId . '_' . $type . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $relPath = 'trip_evidence/' . $safeName;

            return [
                'tmp_name'          => $fileData['tmp_name'],
                'original_filename' => $origName,
                'mime_type'         => $mime,
                'file_size'         => $fileSize,
                'rel_path'          => $relPath,
            ];
        }

        // Case B: Base64 data URL string
        if (is_string($fileData) && str_starts_with(trim($fileData), 'data:image/')) {
            $dataUrl = trim($fileData);
            $commaPos = strpos($dataUrl, ',');
            if ($commaPos === false) {
                throw new InvalidArgumentException("Invalid Base64 image payload format.");
            }

            $header = substr($dataUrl, 0, $commaPos);
            $base64Body = substr($dataUrl, $commaPos + 1);

            if (!preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64/i', $header, $matches)) {
                throw new InvalidArgumentException("Malformed image Data URL header.");
            }

            $mime = strtolower(trim($matches[1]));
            if (!isset($allowedMimes[$mime])) {
                throw new InvalidArgumentException("Unsupported image format ({$mime}). Only JPG, PNG, and WebP photos are permitted.");
            }

            $binary = base64_decode($base64Body, true);
            if ($binary === false) {
                throw new InvalidArgumentException("Failed to decode Base64 image payload.");
            }

            $fileSize = strlen($binary);
            if ($fileSize === 0) {
                throw new InvalidArgumentException("Empty image payload received.");
            }
            if ($fileSize > $maxBytes) {
                throw new InvalidArgumentException("Evidence photo exceeds maximum size limit of 10MB.");
            }

            $ext = $allowedMimes[$mime];
            $safeName = 'ev_' . $dispatchId . '_' . $type . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $relPath = 'trip_evidence/' . $safeName;

            return [
                'binary'            => $binary,
                'original_filename' => 'capture_' . $type . '.' . $ext,
                'mime_type'         => $mime,
                'file_size'         => $fileSize,
                'rel_path'          => $relPath,
            ];
        }

        throw new InvalidArgumentException("Invalid evidence payload. Must be a valid multipart file upload or Base64 data image.");
    }

    /**
     * Get grouped evidence for dispatch
     */
    public function getGroupedEvidence(int $dispatchId): array
    {
        return $this->evidenceModel->getByDispatchGrouped($dispatchId);
    }
}

