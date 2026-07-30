<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'code',
        'description',
        'type',
        'value',
        'status',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
