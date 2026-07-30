<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'product_id',
        'user_id',
        'rating',
        'comment',
        'admin_reply',
        'status',
        'answered_at',
        'answered_by_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by_id');
    }
}
