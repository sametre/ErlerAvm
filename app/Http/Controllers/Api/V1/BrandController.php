<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->where('status', 'published')
            ->orderBy('name')
            ->get(['public_id', 'name', 'slug', 'description']);

        return $this->success($brands);
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $brand) {
            return $this->error('Marka bulunamadı.', 404);
        }

        $products = $brand->products()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(24);

        $products->getCollection()->transform(function (Product $product) use ($brand) {
            return [
                'public_id' => $product->public_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'images' => $product->images ?? [],
                'brand' => [
                    'public_id' => $brand->public_id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ],
            ];
        });

        return $this->success([
            'brand' => [
                'public_id' => $brand->public_id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
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
