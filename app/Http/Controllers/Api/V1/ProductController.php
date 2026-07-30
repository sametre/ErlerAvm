<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $products = Product::query()
            ->with(['brand:id,public_id,name,slug', 'category:id,public_id,name,slug'])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('status', 'approved')], 'rating')
            ->withCount(['reviews as review_count' => fn ($query) => $query->where('status', 'approved')])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->when($validated['q'] ?? null, fn ($query, $search) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
            ))
            ->when($validated['category'] ?? null, fn ($query, $slug) => $query->whereHas('category', fn ($query) => $query->where('slug', $slug)))
            ->when($validated['brand'] ?? null, fn ($query, $slug) => $query->whereHas('brand', fn ($query) => $query->where('slug', $slug)))
            ->when(isset($validated['min_price']), fn ($query) => $query->where('price', '>=', $validated['min_price']))
            ->when(isset($validated['max_price']), fn ($query) => $query->where('price', '<=', $validated['max_price']))
            ->when($request->boolean('in_stock'), fn ($query) => $query->where('stock_quantity', '>', 0));

        match ($validated['sort'] ?? 'newest') {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            default => $products->latest('published_at'),
        };

        $paginator = $products->paginate($validated['per_page'] ?? 24)->withQueryString();
        $paginator->getCollection()->transform(fn (Product $product) => $this->summary($product));

        return $this->paginated($paginator);
    }

    public function show(string $publicId): JsonResponse
    {
        $product = Product::query()
            ->with(['brand:id,public_id,name,slug', 'category:id,public_id,name,slug'])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('status', 'approved')], 'rating')
            ->withCount(['reviews as review_count' => fn ($query) => $query->where('status', 'approved')])
            ->with(['reviews' => function ($q) {
                $q->with('user:id,name')
                    ->where('status', 'approved')
                    ->latest()
                    ->limit(10);
            }])
            ->where('public_id', $publicId)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->first();

        if (! $product) {
            return $this->error('Ürün bulunamadı.', 404);
        }

        return $this->success([
            'public_id' => $product->public_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock_quantity' => $product->stock_quantity,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'images' => $product->images ?? [],
            'brand' => $product->brand ? [
                'public_id' => $product->brand->public_id,
                'name' => $product->brand->name,
                'slug' => $product->brand->slug,
            ] : null,
            'category' => $product->category ? [
                'public_id' => $product->category->public_id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'reviews' => $product->reviews->map(fn ($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user?->name ?? 'Anonim',
                'created_at' => $review->created_at->diffForHumans(),
            ]),
            'average_rating' => round((float) ($product->average_rating ?? 0), 2),
            'review_count' => $product->review_count,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Product $product): array
    {
        return [
            'public_id' => $product->public_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock_quantity' => $product->stock_quantity,
            'images' => $product->images ?? [],
            'average_rating' => round((float) ($product->average_rating ?? 0), 2),
            'review_count' => $product->review_count,
            'brand' => $product->brand?->only(['public_id', 'name', 'slug']),
            'category' => $product->category?->only(['public_id', 'name', 'slug']),
        ];
    }
}
