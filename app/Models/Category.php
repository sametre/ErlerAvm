<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use App\Concerns\Commerce\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasPublicId;
    use HasSeoSlug;

    protected $fillable = ['public_id', 'parent_id', 'name', 'slug', 'status', 'description', 'sort_order', 'image_path'];

    protected $appends = ['image_url'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('categories.active'));
        static::deleted(fn () => Cache::forget('categories.active'));
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'http') ? $this->image_path : Storage::url($this->image_path);
    }

    public static function getCachedActive()
    {
        return Cache::rememberForever('categories.active', function () {
            return self::where('status', 'active')->orderBy('sort_order')->get();
        });
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getUrlAttribute(): string
    {
        return route('categories.show', ['category' => $this->slug], false);
    }
}
