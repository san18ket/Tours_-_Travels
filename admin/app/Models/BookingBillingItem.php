<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class BookingBillingItem extends Model
{
    protected string $table = 'booking_billing_items';
    protected array $fillable = [
        'booking_id', 'invoice_id', 'charge_category', 'description',
        'quantity', 'unit', 'unit_rate', 'line_total', 'is_taxable',
        'display_order'
    ];

    /**
     * Retrieve all billing items for a booking ordered by display_order
     */
    public function getItemsByBooking(int $bookingId): array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `booking_billing_items` WHERE `booking_id` = ? ORDER BY `display_order` ASC, `id` ASC");
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all billing items for an invoice ordered by display_order
     */
    public function getItemsByInvoice(int $invoiceId): array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `booking_billing_items` WHERE `invoice_id` = ? ORDER BY `display_order` ASC, `id` ASC");
        $stmt->execute([$invoiceId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            return $rows;
        }

        // Fallback: If not linked by invoice_id directly, find via booking_id from invoice
        $stmtInv = $this->getPdo()->prepare("SELECT booking_id FROM `invoices` WHERE `id` = ? LIMIT 1");
        $stmtInv->execute([$invoiceId]);
        $bookingId = (int)$stmtInv->fetchColumn();
        if ($bookingId > 0) {
            return $this->getItemsByBooking($bookingId);
        }

        return [];
    }

    /**
     * Save/replace billing items for a booking atomically
     */
    public function saveItemsForBooking(int $bookingId, array $items, ?int $invoiceId = null): array
    {
        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            // Remove existing items for this booking
            $stmtDel = $pdo->prepare("DELETE FROM `booking_billing_items` WHERE `booking_id` = ?");
            $stmtDel->execute([$bookingId]);

            $stmtIns = $pdo->prepare("INSERT INTO `booking_billing_items` 
                (`booking_id`, `invoice_id`, `charge_category`, `description`, `quantity`, `unit`, `unit_rate`, `line_total`, `is_taxable`, `display_order`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

            $saved = [];
            $order = 1;
            foreach ($items as $item) {
                $category    = trim((string)($item['charge_category'] ?? 'other')) ?: 'other';
                $description = trim((string)($item['description'] ?? 'Charge Item')) ?: 'Charge Item';
                $quantity    = max(0.0, (float)($item['quantity'] ?? 1.0));
                $unit        = !empty($item['unit']) ? trim((string)$item['unit']) : null;
                $unitRate    = (isset($item['unit_rate']) && $item['unit_rate'] !== null && $item['unit_rate'] !== '')
                    ? max(0.0, (float)$item['unit_rate'])
                    : null;
                
                // If line_total is provided, use it; otherwise compute quantity * unitRate
                if (isset($item['line_total']) && (float)$item['line_total'] >= 0.0) {
                    $lineTotal = round((float)$item['line_total'], 2);
                } else {
                    $lineTotal = round($quantity * ($unitRate ?? 0.0), 2);
                }

                // If line_total is 0 and quantity is 0 and unitRate is 0, only keep if description is not empty and requested
                $isTaxable = isset($item['is_taxable']) ? ((int)$item['is_taxable'] ? 1 : 0) : 1;
                $itemOrder = isset($item['display_order']) ? (int)$item['display_order'] : $order;

                $stmtIns->execute([
                    $bookingId,
                    $invoiceId,
                    $category,
                    $description,
                    $quantity,
                    $unit,
                    $unitRate,
                    $lineTotal,
                    $isTaxable,
                    $itemOrder
                ]);

                $savedId = (int)$pdo->lastInsertId();
                $saved[] = [
                    'id'              => $savedId,
                    'booking_id'      => $bookingId,
                    'invoice_id'      => $invoiceId,
                    'charge_category' => $category,
                    'description'     => $description,
                    'quantity'        => $quantity,
                    'unit'            => $unit,
                    'unit_rate'       => $unitRate,
                    'line_total'      => $lineTotal,
                    'is_taxable'      => $isTaxable,
                    'display_order'   => $itemOrder,
                ];
                $order++;
            }

            if (!$inTx) {
                $pdo->commit();
            }
            return $saved;
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Link all items of a booking to an invoice ID
     */
    public function linkItemsToInvoice(int $bookingId, int $invoiceId): void
    {
        $stmt = $this->getPdo()->prepare("UPDATE `booking_billing_items` SET `invoice_id` = ?, `updated_at` = NOW() WHERE `booking_id` = ?");
        $stmt->execute([$invoiceId, $bookingId]);
    }
}
