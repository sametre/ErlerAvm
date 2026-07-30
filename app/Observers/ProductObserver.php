<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Seo\ProductSeoGenerator;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function __construct(private ProductSeoGenerator $seoGenerator) {}

    public function saving(Product $product): void
    {
        if ($product->seo_title && $product->seo_description && $product->seo_keywords) {
            return;
        }

        $generated = $this->seoGenerator->generate($product);

        foreach ($generated as $attribute => $value) {
            if (blank($product->{$attribute})) {
                $product->{$attribute} = $value;
            }
        }
    }

    public function saved(Product $product): void
    {
        Cache::forget('sitemap_xml');
    }

    public function deleted(Product $product): void
    {
        Cache::forget('sitemap_xml');
    }
}
