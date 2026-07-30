<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function show(string $brand): Response
    {
        $brand = Brand::query()
            ->where('slug', $brand)
            ->where('status', 'published')
            ->firstOrFail();

        return Inertia::render('Storefront/BrandShow', [
            'brand' => $brand->only(['name', 'slug', 'description']),
            'products' => $brand->products()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->paginate(24)
                ->through(fn (Product $product) => [
                    'name' => $product->name,
                    'public_id' => $product->public_id,
                    'price' => $product->price,
                    'url' => $product->url,
                ]),
        ]);
    }
}
