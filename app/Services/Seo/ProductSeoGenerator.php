<?php

namespace App\Services\Seo;

use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Support\Str;

class ProductSeoGenerator
{
    /**
     * @return array{seo_title: string, seo_description: string, seo_keywords: string}
     */
    public function generate(Product $product): array
    {
        $storeName = (string) (StoreSetting::publicPayload()['store_name'] ?? config('app.name', 'ErlerAVM'));
        $descriptionSource = $product->short_description ?: $product->description;
        $description = $descriptionSource
            ? Str::squish(strip_tags((string) $descriptionSource))
            : "{$product->name} ürününü güvenli ödeme, hızlı teslimat ve güncel fiyat avantajıyla inceleyin.";

        $keywords = collect([
            $product->name,
            $product->sku,
            $product->relationLoaded('brand') ? $product->brand?->name : null,
            $product->relationLoaded('category') ? $product->category?->name : null,
            $storeName,
            'online alışveriş',
        ])->filter()->implode(', ');

        return [
            'seo_title' => Str::limit("{$product->name} | {$storeName}", 60, ''),
            'seo_description' => Str::limit($description, 155, ''),
            'seo_keywords' => Str::limit($keywords, 255, ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function structuredData(Product $product, float $averageRating, int $reviewCount): array
    {
        $settings = StoreSetting::publicPayload();
        $images = collect($product->images ?? [])
            ->map(fn (string $image): string => $this->absoluteUrl($image))
            ->values()
            ->all();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $product->url.'#product',
            'name' => $product->name,
            'description' => $product->seo_description ?: $product->short_description,
            'url' => $product->url,
            'image' => $images,
            'sku' => $product->sku,
            'brand' => $product->brand ? [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ] : null,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'url' => $product->url,
                'priceCurrency' => 'TRY',
                'price' => (string) $product->price,
                'availability' => $product->stock_quantity > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $settings['store_name'] ?? config('app.name', 'ErlerAVM'),
                    'logo' => $this->absoluteUrl((string) ($settings['logo_url'] ?? '/erleravm.png')),
                ],
            ],
        ];

        if ($reviewCount > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($averageRating, 2),
                'reviewCount' => $reviewCount,
            ];
        }

        return array_filter($schema, fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function absoluteUrl(string $url): string
    {
        return Str::startsWith($url, ['http://', 'https://'])
            ? $url
            : asset(ltrim($url, '/'));
    }
}
