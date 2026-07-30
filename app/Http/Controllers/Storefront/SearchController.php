<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Str::of((string) $request->query('q', ''))
            ->squish()
            ->limit(80, '')
            ->toString();
        $likeQuery = addcslashes($query, '\%_');

        $filters = [
            'categories' => $request->query('categories', []),
            'brands' => $request->query('brands', []),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'in_stock' => $request->boolean('in_stock', false),
        ];

        $products = Product::query()
            ->with(['brand:id,name,slug', 'category:id,name,slug'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($likeQuery): void {
                $builder->where('name', 'like', "%{$likeQuery}%")
                    ->orWhere('sku', 'like', "%{$likeQuery}%")
                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$likeQuery}%"))
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$likeQuery}%"));
            }))
            ->when(! empty($filters['categories']), fn ($builder) => $builder->whereHas('category', fn ($q) => $q->whereIn('slug', $filters['categories'])))
            ->when(! empty($filters['brands']), fn ($builder) => $builder->whereHas('brand', fn ($q) => $q->whereIn('slug', $filters['brands'])))
            ->when($filters['min_price'] !== null, fn ($builder) => $builder->where('price', '>=', $filters['min_price']))
            ->when($filters['max_price'] !== null, fn ($builder) => $builder->where('price', '<=', $filters['max_price']))
            ->when($filters['in_stock'], fn ($builder) => $builder->where('stock_quantity', '>', 0))
            ->latest('published_at')
            ->paginate(24)
            ->withQueryString()
            ->through(fn ($product) => [
                'name' => $product->name,
                'public_id' => $product->public_id,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'images' => $product->images ?? [],
                'url' => $product->url,
                'brand' => $product->brand?->only(['name', 'slug']),
                'category' => $product->category?->only(['name', 'slug']),
            ]);

        if ($request->wantsJson()) {
            return response()->json($products);
        }

        $options = [
            'categories' => Category::where('status', 'published')->orderBy('name')->get(['name', 'slug']),
            'brands' => Brand::where('status', 'published')->orderBy('name')->get(['name', 'slug']),
        ];

        return Inertia::render('Storefront/Search', [
            'query' => $query,
            'filters' => $filters,
            'options' => $options,
            'products' => $products,
        ]);
    }
}
