<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    protected array $fillable = [
        'payment_reference', 'invoice_id', 'amount', 'payment_method',
        'transaction_id', 'status', 'idempotency_key', 'notes', 'paid_at'
    ];

    public function findByIdempotencyKey(string $key): ?array
    {
        return $this->findBy('idempotency_key', $key);
    }

    public function getPaymentsByInvoice(int $invoiceId): array
    {
        return $this->where("`invoice_id` = ?", [$invoiceId], 'id DESC');
    }
}
