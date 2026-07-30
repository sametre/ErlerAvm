<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->with(['children' => fn ($query) => $query->where('status', 'published')->orderBy('sort_order')])
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'public_id', 'name', 'slug', 'description'])
            ->map(fn (Category $category) => [
                'public_id' => $category->public_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'children' => $category->children->map(fn (Category $child) => [
                    'public_id' => $child->public_id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                ]),
            ]);

        return $this->success($categories);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $category) {
            return $this->error('Kategori bulunamadı.', 404);
        }

        $products = $category->products()
            ->with(['brand:id,public_id,name,slug'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(24);

        // Customize the pagination items
        $products->getCollection()->transform(function (Product $product) {
            return [
                'public_id' => $product->public_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'images' => $product->images ?? [],
                'brand' => $product->brand ? [
                    'public_id' => $product->brand->public_id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
            ];
        });

        return $this->success([
            'category' => [
                'public_id' => $category->public_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ],
            'products' => [
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ],
            ],
        ]);
    }
}
