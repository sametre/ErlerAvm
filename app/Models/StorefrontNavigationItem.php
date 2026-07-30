<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class StorefrontNavigationItem extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'location',
        'title',
        'url',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
