<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class StorefrontBanner extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'placement',
        'title',
        'subtitle',
        'cta_label',
        'cta_url',
        'image_path',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
