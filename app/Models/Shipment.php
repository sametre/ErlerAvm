<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'order_id',
        'shipment_provider_id',
        'status',
        'tracking_number',
        'tracking_url',
        'customer_notified_at',
        'shipped_at',
        'delivered_at',
        'admin_note',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'customer_notified_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'preparing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim edildi',
            'cancelled' => 'İptal edildi',
            'returned' => 'İade sürecinde',
            default => (string) $this->status,
        };
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<ShipmentProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ShipmentProvider::class, 'shipment_provider_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasMany<ShipmentMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(ShipmentMovement::class);
    }
}
