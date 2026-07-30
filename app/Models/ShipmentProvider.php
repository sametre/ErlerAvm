<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentProvider extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'code',
        'status',
        'tracking_url_template',
        'integration_mode',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Shipment, $this>
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function trackingUrl(?string $trackingNumber): ?string
    {
        if (blank($trackingNumber) || blank($this->tracking_url_template)) {
            return null;
        }

        return str_replace('{tracking_number}', rawurlencode((string) $trackingNumber), (string) $this->tracking_url_template);
    }
}
