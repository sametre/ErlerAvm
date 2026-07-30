<?php

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('seo fields are generated automatically when a product is created', function () {
    $product = Product::query()->create([
        'name' => 'Erler Akıllı Televizyon 55 İnç',
        'slug' => 'erler-akilli-televizyon-55-inc',
        'sku' => 'TV-55-001',
        'price' => 29999.90,
        'stock_quantity' => 8,
        'status' => 'published',
        'short_description' => 'Yüksek çözünürlüklü akıllı televizyon.',
        'published_at' => now(),
    ]);

    expect($product->seo_title)->toContain('Erler Akıllı Televizyon')
        ->and($product->seo_description)->toBe('Yüksek çözünürlüklü akıllı televizyon.')
        ->and($product->seo_keywords)->toContain('TV-55-001')
        ->and(mb_strlen($product->seo_title))->toBeLessThanOrEqual(60)
        ->and(mb_strlen($product->seo_description))->toBeLessThanOrEqual(155);
});

test('manually entered seo fields are preserved', function () {
    $product = Product::query()->create([
        'name' => 'Manuel SEO Ürünü',
        'slug' => 'manuel-seo-urunu',
        'price' => 999,
        'stock_quantity' => 1,
        'status' => 'draft',
        'seo_title' => 'Özel başlık',
        'seo_description' => 'Özel açıklama',
        'seo_keywords' => 'özel, kelime',
    ]);

    expect($product->seo_title)->toBe('Özel başlık')
        ->and($product->seo_description)->toBe('Özel açıklama')
        ->and($product->seo_keywords)->toBe('özel, kelime');
});

test('product changes invalidate the cached sitemap', function () {
    Cache::put('sitemap_xml', 'stale sitemap');

    Product::query()->create([
        'name' => 'Yeni Sitemap Ürünü',
        'slug' => 'yeni-sitemap-urunu',
        'price' => 100,
        'stock_quantity' => 1,
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect(Cache::get('sitemap_xml'))->toBeNull();
});

test('published product page exposes complete product structured data', function () {
    $brand = Brand::query()->create([
        'name' => 'Erler Teknoloji',
        'slug' => 'erler-teknoloji',
        'status' => 'published',
    ]);

    $product = Product::query()->create([
        'brand_id' => $brand->id,
        'name' => 'SEO Şemalı Ürün',
        'slug' => 'seo-semali-urun',
        'sku' => 'SEO-001',
        'price' => 1499.90,
        'stock_quantity' => 4,
        'status' => 'published',
        'images' => ['/erleravm.png'],
        'published_at' => now(),
    ]);

    $this->get($product->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/ProductShow')
            ->where('seoSchema.@type', 'Product')
            ->where('seoSchema.sku', 'SEO-001')
            ->where('seoSchema.brand.name', 'Erler Teknoloji')
            ->where('seoSchema.offers.priceCurrency', 'TRY')
            ->where('seoSchema.offers.availability', 'https://schema.org/InStock')
            ->where('seoSchema.image.0', asset('erleravm.png'))
        );
});

test('robots policy covers major search crawlers and advertises sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('User-agent: Googlebot')
        ->toContain('User-agent: Bingbot')
        ->toContain('User-agent: Slurp')
        ->toContain('User-agent: Yandex')
        ->toContain('Disallow: /odeme')
        ->toContain('Sitemap: https://erleravm.test/sitemap.xml');
});
