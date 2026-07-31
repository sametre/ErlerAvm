<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontBanner;
use App\Models\StorefrontNavigationItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'navigationItems' => $this->navigationItems(),
            'banners' => $this->banners(),
            'categories' => $this->categories(),
            'brands' => Brand::query()
                ->where('status', 'published')
                ->orderBy('name')
                ->limit(12)
                ->get(['name', 'slug', 'description'])
                ->map(fn (Brand $brand) => [
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'url' => $brand->url,
                ]),
            'campaigns' => Campaign::query()
                ->where('status', 'published')
                ->latest()
                ->limit(6)
                ->get(['public_id', 'name', 'slug', 'description', 'starts_at', 'ends_at'])
                ->map(fn (Campaign $campaign) => [
                    'name' => $campaign->name,
                    'slug' => $campaign->slug,
                    'public_id' => $campaign->public_id,
                    'description' => $campaign->description,
                    'url' => $campaign->url,
                ]),
            'featuredProducts' => Product::query()
                ->with(['brand:id,name,slug', 'category:id,name,slug'])
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->limit(16)
                ->get(['public_id', 'category_id', 'brand_id', 'name', 'slug', 'price', 'compare_at_price', 'stock_quantity', 'images'])
                ->map(fn (Product $product) => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'public_id' => $product->public_id,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'stock_quantity' => $product->stock_quantity,
                    'images' => $product->images ?? [],
                    'url' => $product->url,
                    'brand' => $product->brand?->only(['name', 'slug']),
                    'category' => $product->category?->only(['name', 'slug']),
                ]),
        ]);
    }

    /**
     * @return Collection<int, array{name: string, slug: string, description: string|null, url: string, children: Collection<int, array{name: string, slug: string, description: string|null, url: string}>}>
     */
    private function categories(): Collection
    {
        return Category::query()
            ->with(['children' => fn ($query) => $query
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->limit(24)])
            ->where('status', 'published')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->limit(12)
            ->get(['id', 'name', 'slug', 'description'])
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'url' => $category->url,
                'image' => $this->categoryImage($category->name),
                'children' => $category->children->map(fn (Category $child) => [
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'url' => $child->url,
                ])->values(),
            ]);
    }

    /**
     * @return Collection<int|string, mixed>
     */
    private function navigationItems(): Collection
    {
        if (! Schema::hasTable('storefront_navigation_items')) {
            return collect();
        }

        return StorefrontNavigationItem::query()
            ->where('status', 'published')
            ->orderBy('location')
            ->orderBy('sort_order')
            ->get(['location', 'title', 'url'])
            ->groupBy('location');
    }

    /**
     * @return Collection<int|string, mixed>
     */
    private function banners(): Collection
    {
        if (! Schema::hasTable('storefront_banners')) {
            return collect();
        }

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
            ->limit(50)
            ->get(['placement', 'title', 'subtitle', 'cta_label', 'cta_url', 'image_path'])
            ->groupBy('placement');
    }

    private function categoryImage(string $name): string
    {
        $normalized = mb_strtolower($name);

        return match (true) {
            str_contains($normalized, 'teknoloji'),
            str_contains($normalized, 'elektronik'),
            str_contains($normalized, 'telefon'),
            str_contains($normalized, 'bilgisayar'),
            str_contains($normalized, 'tablet'),
            str_contains($normalized, 'televizyon'),
            str_contains($normalized, 'kulaklık'),
            str_contains($normalized, 'kamera'),
            str_contains($normalized, 'oyun'),
            str_contains($normalized, 'saat') => '/images/banners/erler-teknoloji-beyaz-esya.png',
            str_contains($normalized, 'beyaz'),
            str_contains($normalized, 'buzdolabı'),
            str_contains($normalized, 'çamaşır'),
            str_contains($normalized, 'bulaşık'),
            str_contains($normalized, 'fırın'),
            str_contains($normalized, 'ocak'),
            str_contains($normalized, 'kurutma'),
            str_contains($normalized, 'süpürge'),
            str_contains($normalized, 'klima'),
            str_contains($normalized, 'kahve'),
            str_contains($normalized, 'çay'),
            str_contains($normalized, 'tost') => '/images/banners/erler-teknoloji-beyaz-esya.png',
            str_contains($normalized, 'mobilya'),
            str_contains($normalized, 'yaşam'),
            str_contains($normalized, 'koltuk'),
            str_contains($normalized, 'yatak'),
            str_contains($normalized, 'oda'),
            str_contains($normalized, 'masa'),
            str_contains($normalized, 'sandalye'),
            str_contains($normalized, 'dekorasyon'),
            str_contains($normalized, 'tekstil'),
            str_contains($normalized, 'halı'),
            str_contains($normalized, 'perde'),
            str_contains($normalized, 'aydınlatma') => '/images/banners/erler-mobilya-siklik.png',
            default => '/images/banners/erler-premium-alisveris.png',
        };
    }
}
