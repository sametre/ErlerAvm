<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class PopupCampaign extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'image_path',
        'target_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
