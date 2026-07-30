<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'description',
        'status',
        'filters',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }
}
