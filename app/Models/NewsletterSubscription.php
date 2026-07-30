<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'email',
        'source',
        'subscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
        ];
    }
}
