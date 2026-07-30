<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->wishlistItems()
            ->with(['product.brand:id,public_id,name,slug'])
            ->latest()
            ->get()
            ->filter(fn ($item) => $item->product?->status === 'published')
            ->map(fn ($item) => $this->productPayload($item->product))
            ->values();

        return $this->success($items);
    }

    public function store(Request $request, string $publicId): JsonResponse
    {
        $product = $this->publishedProduct($publicId);
        $request->user()->wishlistItems()->firstOrCreate(['product_id' => $product->id]);

        return $this->success($this->productPayload($product), 'Ürün favorilere eklendi.', 201);
    }

    public function destroy(Request $request, string $publicId): JsonResponse
    {
        $product = Product::query()->where('public_id', $publicId)->firstOrFail();
        $request->user()->wishlistItems()->where('product_id', $product->id)->delete();

        return $this->success(null, 'Ürün favorilerden çıkarıldı.');
    }

    private function publishedProduct(string $publicId): Product
    {
        return Product::query()
            ->with('brand:id,public_id,name,slug')
            ->where('public_id', $publicId)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'public_id' => $product->public_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock_quantity' => $product->stock_quantity,
            'images' => $product->images ?? [],
            'brand' => $product->brand?->only(['public_id', 'name', 'slug']),
        ];
    }
}
