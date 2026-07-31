<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use App\Concerns\Commerce\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasPublicId;
    use HasSeoSlug;

    protected $fillable = ['public_id', 'name', 'slug', 'status', 'description'];

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return route('brands.show', ['brand' => $this->slug], false);
    }
}
