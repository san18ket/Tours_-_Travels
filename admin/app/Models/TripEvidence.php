<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class TripEvidence extends Model
{
    protected string $table = 'trip_evidence';
    protected array $fillable = [
        'dispatch_id',
        'driver_id',
        'evidence_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'odometer_reading',
        'captured_at',
        'uploaded_at',
        'latitude',
        'longitude',
        'notes',
    ];

    /**
     * Get all evidence items for a given dispatch
     */
    public function findByDispatchId(int $dispatchId): array
    {
        return $this->where('`dispatch_id` = ?', [$dispatchId], 'id ASC');
    }

    /**
     * Get latest evidence of a specific type for a dispatch
     */
    public function findByDispatchAndType(int $dispatchId, string $type): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `{$this->table}` WHERE `dispatch_id` = ? AND `evidence_type` = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$dispatchId, $type]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get structured grouped evidence with formatted web URLs
     */
    public function getByDispatchGrouped(int $dispatchId): array
    {
        $records = $this->findByDispatchId($dispatchId);
        $grouped = [
            'start_odometer' => null,
            'start_vehicle'  => null,
            'end_odometer'   => null,
            'end_vehicle'    => null,
            'additional'     => [],
            'all'            => [],
        ];

        foreach ($records as $item) {
            $formatted = $this->formatEvidence($item);
            $grouped['all'][] = $formatted;

            if ($item['evidence_type'] === 'start_odometer') {
                $grouped['start_odometer'] = $formatted;
            } elseif ($item['evidence_type'] === 'start_vehicle') {
                $grouped['start_vehicle'] = $formatted;
            } elseif ($item['evidence_type'] === 'end_odometer') {
                $grouped['end_odometer'] = $formatted;
            } elseif ($item['evidence_type'] === 'end_vehicle') {
                $grouped['end_vehicle'] = $formatted;
            } else {
                $grouped['additional'][] = $formatted;
            }
        }

        return $grouped;
    }

    /**
     * Format an evidence item with full web URL and human-readable metadata
     */
    public function formatEvidence(array $item): array
    {
        $cleanPath = ltrim(str_replace('\\', '/', $item['file_path'] ?? ''), '/');
        // Ensure web-accessible URL
        if (str_starts_with($cleanPath, 'storage/')) {
            $item['url'] = url('/' . $cleanPath);
        } else {
            $item['url'] = url('/storage/' . $cleanPath);
        }

        $item['formatted_size'] = $this->formatBytes((int)($item['file_size'] ?? 0));
        $item['label'] = match($item['evidence_type'] ?? '') {
            'start_odometer' => 'Start Meter Photo',
            'start_vehicle'  => 'Starting Vehicle Photo',
            'end_odometer'   => 'End Meter Photo',
            'end_vehicle'    => 'Ending Vehicle Photo',
            default          => 'Trip Photo / Document'
        };

        return $item;
    }

    /**
     * Format bytes to human readable string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

