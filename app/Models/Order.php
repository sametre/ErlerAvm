<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'order_number',
        'user_id',
        'email',
        'status',
        'payment_status',
        'subtotal',
        'shipping_total',
        'discount_total',
        'tax_total',
        'grand_total',
        'currency',
        'billing_address',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'billing_address' => 'array',
            'shipping_address' => 'array',
        ];
    }

    protected $appends = ['status_label', 'payment_status_label'];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'payment_pending' => 'Ödeme Bekleniyor',
            'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargolandı',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            default => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Bekliyor',
            'paid' => 'Ödendi',
            'failed' => 'Başarısız',
            'refunded' => 'İade Edildi',
            default => $this->payment_status,
        };
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasOne<Shipment, $this>
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
