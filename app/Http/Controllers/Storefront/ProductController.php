<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Seo\ProductSeoGenerator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(string $product, ProductSeoGenerator $seoGenerator): Response|RedirectResponse
    {
        [$slug, $publicId] = $this->splitSlugAndPublicId($product);

        $product = Product::query()
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'variants'])
            ->with(['reviews' => function ($q) {
                $q->with('user:id,name')
                    ->where(function ($query): void {
                        $query->where('status', 'approved');

                        if (auth()->check()) {
                            $query->orWhere('user_id', auth()->id());
                        }
                    })
                    ->latest();
            }])
            ->where('public_id', $publicId)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->firstOrFail();

        if ($product->slug !== $slug) {
            return redirect()->route('products.show', [
                'product' => $product->slug.'-'.$product->public_id,
            ], 301);
        }

        $similarProducts = Product::query()
            ->with(['brand:id,name,slug'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereKeyNot($product->id)
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->latest('published_at')
            ->limit(8)
            ->get(['id', 'public_id', 'brand_id', 'name', 'slug', 'price', 'compare_at_price', 'stock_quantity', 'images'])
            ->map(fn (Product $item) => $this->productCard($item));

        $recommendedProducts = Product::query()
            ->with(['brand:id,name,slug'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereKeyNot($product->id)
            ->when($product->brand_id, fn ($query) => $query->where('brand_id', $product->brand_id))
            ->latest('published_at')
            ->limit(8)
            ->get(['id', 'public_id', 'brand_id', 'name', 'slug', 'price', 'compare_at_price', 'stock_quantity', 'images'])
            ->map(fn (Product $item) => $this->productCard($item));

        $approvedReviews = $product->reviews->where('status', 'approved');
        $averageRating = (float) ($approvedReviews->avg('rating') ?? 0);
        $reviewCount = $approvedReviews->count();

        return Inertia::render('Storefront/ProductShow', [
            'product' => [
                'public_id' => $product->public_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'features' => $product->features,
                'seo_title' => $product->seo_title,
                'seo_description' => $product->seo_description,
                'seo_keywords' => $product->seo_keywords,
                'images' => $product->images ?? [],
                'url' => $product->url,
                'variants' => $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'public_id' => $v->public_id,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'compare_at_price' => $v->compare_at_price,
                    'stock_quantity' => $v->stock_quantity,
                    'options' => $v->options,
                ]),
                'brand' => $product->brand?->only(['name', 'slug']),
                'category' => $product->category?->only(['name', 'slug']),
                'reviews' => $product->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'admin_reply' => $review->admin_reply,
                    'status' => $review->status,
                    'user_name' => $review->user?->name ?? 'Anonim',
                    'created_at' => $review->created_at->diffForHumans(),
                ]),
                'average_rating' => $averageRating,
                'review_count' => $reviewCount,
            ],
            'seoSchema' => $seoGenerator->structuredData($product, $averageRating, $reviewCount),
            'similarProducts' => $similarProducts,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitSlugAndPublicId(string $value): array
    {
        abort_if(strlen($value) < 28, 404);

        $publicId = substr($value, -26);
        $slug = rtrim(substr($value, 0, -26), '-');

        abort_unless($slug !== '' && preg_match('/^[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}$/', $publicId), 404);

        return [$slug, $publicId];
    }

    /**
     * @return array<string, mixed>
     */
    private function productCard(Product $product): array
    {
        return [
            'name' => $product->name,
            'public_id' => $product->public_id,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock_quantity' => $product->stock_quantity,
            'images' => $product->images ?? [],
            'url' => $product->url,
            'brand' => $product->brand?->only(['name', 'slug']),
        ];
    }
}
