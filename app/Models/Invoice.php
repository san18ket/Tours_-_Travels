<?php
namespace App\Models;

use App\Core\Model;
use RuntimeException;

class Invoice extends Model
{
    protected string $table = 'invoices';
    protected array $fillable = [
        'invoice_number', 'booking_id', 'customer_id', 'customer_profile_id', 'subtotal', 'tax_rate',
        'tax_amount', 'discount_amount', 'total_amount', 'paid_amount', 'payment_status',
        'due_date', 'is_immutable', 'issued_at'
    ];

    public function findByBookingId(int $bookingId): ?array
    {
        return $this->findBy('booking_id', $bookingId);
    }

    public function findWithFullDetails(int $id): ?array
    {
        $sql = "SELECT inv.*, b.booking_code, b.pickup_address, b.drop_address, b.start_date, b.end_date,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email,
                       COALESCE(c.phone, u.phone) as customer_phone,
                       COALESCE(c.customer_code, CONCAT('LEGACY-', u.id)) as customer_code,
                       t.title as tour_title
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                LEFT JOIN `tours` t ON b.tour_id = t.id
                WHERE inv.id = ?
                LIMIT 1";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllWithDetails(): array
    {
        $sql = "SELECT inv.*, b.booking_code,
                       COALESCE(c.name, u.name) as customer_name,
                       COALESCE(c.email, u.email) as customer_email
                FROM `invoices` inv
                LEFT JOIN `bookings` b ON inv.booking_id = b.id
                LEFT JOIN `customers` c ON inv.customer_profile_id = c.id
                LEFT JOIN `users` u ON inv.customer_id = u.id
                ORDER BY inv.id DESC";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Enforce invoice immutability
     */
    public function update(int|string $id, array $data): bool
    {
        $existing = $this->find($id);
        if ($existing && !empty($existing['is_immutable'])) {
            // Only allow payment status and paid amount updates
            $allowedKeys = ['paid_amount', 'payment_status'];
            $filteredData = array_intersect_key($data, array_flip($allowedKeys));
            if (empty($filteredData)) {
                throw new RuntimeException("Invoice {$existing['invoice_number']} is finalized and immutable. Financial core cannot be modified.");
            }
            return parent::update($id, $filteredData);
        }
        return parent::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        $existing = $this->find($id);
        if ($existing && !empty($existing['is_immutable'])) {
            throw new RuntimeException("Finalized invoices cannot be deleted under accounting compliance rules.");
        }
        return parent::delete($id);
    }
}
