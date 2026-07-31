<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use App\Concerns\Commerce\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasPublicId;
    use HasSeoSlug;

    protected $fillable = [
        'public_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'price',
        'compare_at_price',
        'stock_quantity',
        'status',
        'short_description',
        'description',
        'features',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'images',
        'published_at',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('products.home'));
        static::deleted(fn () => Cache::forget('products.home'));
    }

    public static function getCachedHomeProducts()
    {
        return Cache::remember('products.home', 3600, function () {
            return self::where('status', 'published')->latest()->take(10)->get();
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'images' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function getUrlAttribute(): string
    {
        return route('products.show', [
            'product' => $this->slug.'-'.$this->public_id,
        ], false);
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlistedBy(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
