<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'channel',
        'status',
        'target_url',
        'budget',
        'starts_at',
        'ends_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
