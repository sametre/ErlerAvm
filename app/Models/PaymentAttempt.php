<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'provider',
        'merchant_oid',
        'idempotency_key',
        'user_id',
        'order_id',
        'email',
        'payment_amount',
        'total_amount',
        'currency',
        'status',
        'iframe_token',
        'payment_type',
        'provider_transaction_id',
        'authorization_code',
        'host_reference',
        'response_code',
        'response_message',
        'three_d_status',
        'masked_card',
        'card_brand',
        'installment_count',
        'failed_reason_code',
        'failed_reason_msg',
        'request_payload',
        'provider_response',
        'callback_payload',
        'notified_at',
        'redirected_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_amount' => 'integer',
            'total_amount' => 'integer',
            'installment_count' => 'integer',
            'request_payload' => 'array',
            'provider_response' => 'array',
            'callback_payload' => 'array',
            'notified_at' => 'datetime',
            'redirected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, ['paid', 'cancelled', 'refunded'], true);
    }
}
