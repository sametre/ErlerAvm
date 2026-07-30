<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class CorporatePage extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'title',
        'slug',
        'content',
        'seo_meta',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'seo_meta' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
