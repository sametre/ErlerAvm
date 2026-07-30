<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $product_id
 * @property string|null $sku
 * @property float|null $price
 * @property float|null $compare_at_price
 * @property int $stock_quantity
 * @property array|null $options
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 */
class ProductVariant extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'compare_at_price',
        'stock_quantity',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'options' => 'array',
        ];
    }

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
