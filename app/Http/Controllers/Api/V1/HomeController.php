<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontBanner;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success([
            'banners' => $this->banners(),
            'categories' => $this->categories(),
            'brands' => $this->brands(),
            'campaigns' => $this->campaigns(),
            'featured_products' => $this->featuredProducts(),
        ]);
    }

    private function banners()
    {
        return StorefrontBanner::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now()))
            ->orderBy('placement')
            ->orderBy('sort_order')
            ->limit(8)
            ->get(['placement', 'title', 'subtitle', 'cta_label', 'cta_url', 'image_path'])
            ->groupBy('placement');
    }

    private function categories()
    {
        return Category::query()
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->limit(12)
            ->get(['public_id', 'name', 'slug', 'description'])
            ->map(fn (Category $category) => [
                'public_id' => $category->public_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $this->categoryImage($category->name),
            ]);
    }

    private function brands()
    {
        return Brand::query()
            ->where('status', 'published')
            ->orderBy('name')
            ->limit(12)
            ->get(['public_id', 'name', 'slug', 'description']);
    }

    private function campaigns()
    {
        return Campaign::query()
            ->where('status', 'published')
            ->latest()
            ->limit(6)
            ->get(['public_id', 'name', 'slug', 'description', 'starts_at', 'ends_at']);
    }

    private function featuredProducts()
    {
        return Product::query()
            ->with(['brand:id,public_id,name,slug', 'category:id,public_id,name,slug'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(12)
            ->get(['id', 'public_id', 'category_id', 'brand_id', 'name', 'slug', 'price', 'compare_at_price', 'stock_quantity', 'images'])
            ->map(fn (Product $product) => [
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
                'category' => $product->category ? [
                    'public_id' => $product->category->public_id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
            ]);
    }

    private function categoryImage(string $name): string
    {
        return match (true) {
            str_contains($name, 'Teknoloji'), str_contains($name, 'Elektronik') => '/images/banners/erler-teknoloji-beyaz-esya.png',
            str_contains($name, 'Beyaz'), str_contains($name, 'Ev Elektroniği') => '/images/banners/erler-teknoloji-beyaz-esya.png',
            str_contains($name, 'Mobilya'), str_contains($name, 'Yaşam') => '/images/banners/erler-mobilya-siklik.png',
            default => '/images/banners/erler-premium-alisveris.png',
        };
    }
}
