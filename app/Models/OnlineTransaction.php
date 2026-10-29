<?php
namespace App\Models;

use App\Core\Model;

class OnlineTransaction extends Model
{
    protected string $table = 'online_transactions';
    protected array $fillable = [
        'gateway', 'order_id', 'payment_id', 'invoice_id', 'customer_id',
        'amount', 'currency', 'status', 'signature', 'error_code',
        'error_description', 'payload_json', 'webhook_event_id'
    ];

    public function findByOrderId(string $orderId): ?array
    {
        return $this->findBy('order_id', $orderId);
    }

    public function findByPaymentId(string $paymentId): ?array
    {
        return $this->findBy('payment_id', $paymentId);
    }
}

