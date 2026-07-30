<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(string $category): Response
    {
        $category = Category::query()
            ->where('slug', $category)
            ->where('status', 'published')
            ->firstOrFail();

        return Inertia::render('Storefront/CategoryShow', [
            'category' => $category->only(['name', 'slug', 'description']),
            'products' => $category->products()
                ->with(['brand:id,name,slug'])
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->paginate(24)
                ->through(fn (Product $product) => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'public_id' => $product->public_id,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'stock_quantity' => $product->stock_quantity,
                    'url' => $product->url,
                    'brand' => $product->brand?->only(['name', 'slug']),
                ]),
        ]);
    }
}
