<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use App\Concerns\Commerce\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasPublicId;
    use HasSeoSlug;

    protected $fillable = ['public_id', 'name', 'slug', 'status', 'description', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): string
    {
        return route('campaigns.show', [
            'campaign' => $this->slug.'-'.$this->public_id,
        ], false);
    }
}
