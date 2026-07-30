<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentationController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $baseUrl = rtrim((string) config('api.url'), '/').'/api/v1';

        return Inertia::render('Developer/Documentation', [
            'baseUrl' => $baseUrl,
            'version' => 'v1',
            'manageUrl' => route('developer.api-clients.index'),
            'endpoints' => [
                ['method' => 'GET', 'path' => '/home', 'title' => 'Ana sayfa', 'description' => 'Banner, kategori, marka, kampanya ve öne çıkan ürünler.'],
                ['method' => 'GET', 'path' => '/products', 'title' => 'Ürünleri listele', 'description' => 'Arama, filtreleme, sıralama ve sayfalama destekli ürün kataloğu.'],
                ['method' => 'GET', 'path' => '/products/{public_id}', 'title' => 'Ürün detayı', 'description' => 'Ürün, marka, kategori, stok ve onaylı yorum bilgileri.'],
                ['method' => 'GET', 'path' => '/categories', 'title' => 'Kategoriler', 'description' => 'Yayınlanmış üst ve alt kategori ağacı.'],
                ['method' => 'GET', 'path' => '/categories/{slug}', 'title' => 'Kategori ürünleri', 'description' => 'Kategori bilgisi ve sayfalanmış ürünleri.'],
                ['method' => 'GET', 'path' => '/brands', 'title' => 'Markalar', 'description' => 'Yayınlanmış marka listesi.'],
                ['method' => 'GET', 'path' => '/brands/{slug}', 'title' => 'Marka ürünleri', 'description' => 'Marka bilgisi ve sayfalanmış ürünleri.'],
                ['method' => 'GET', 'path' => '/campaigns', 'title' => 'Kampanyalar', 'description' => 'Yayındaki kampanya listesi.'],
            ],
            'filters' => [
                ['name' => 'q', 'description' => 'Ürün adı veya SKU içinde arama'],
                ['name' => 'category', 'description' => 'Kategori slug değeri'],
                ['name' => 'brand', 'description' => 'Marka slug değeri'],
                ['name' => 'min_price / max_price', 'description' => 'Fiyat aralığı'],
                ['name' => 'in_stock', 'description' => 'Yalnız stoklu ürünler: 1'],
                ['name' => 'sort', 'description' => 'newest, price_asc, price_desc veya name'],
                ['name' => 'per_page', 'description' => 'Sayfa başına 1–100 kayıt'],
            ],
        ]);
    }
}
