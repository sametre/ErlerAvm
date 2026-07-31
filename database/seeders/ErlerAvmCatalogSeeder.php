<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontBanner;
use App\Models\StorefrontNavigationItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ErlerAvmCatalogSeeder extends Seeder
{
    /**
     * Seed core storefront categories, navigation, and first hero records.
     */
    public function run(): void
    {
        $groups = [
            'Teknoloji & Elektronik' => [
                'Televizyon',
                'Bilgisayar & Laptop',
                'Akıllı Telefonlar',
                'Tabletler',
                'Oyun Konsolları',
                'Kulaklık & Ses Sistemleri',
                'Akıllı Saatler',
            ],
            'Beyaz Eşya & Ev Elektroniği' => [
                'Buzdolabı',
                'Çamaşır Makinesi',
                'Bulaşık Makinesi',
                'Fırın',
                'Ocak',
                'Klima',
                'Küçük Ev Aletleri',
                'Elektrikli Süpürge',
                'Kahve Makinesi',
            ],
            'Mobilya & Ev Yaşam' => [
                'Koltuk Takımları',
                'Yatak & Baza',
                'Yemek Odası',
                'Yatak Odası',
                'Masa & Sandalye',
                'Ev Dekorasyonu',
                'Halı & Kilim',
                'Aydınlatma',
            ],
            'Bahçe & Yapı Market' => [
                'Bahçe Mobilyaları',
                'Bahçe Aydınlatma',
                'Saksı & Çiçek',
                'Bahçe Makineleri',
            ],
            'Kozmetik & Kişisel Bakım' => [
                'Parfüm & Deodorant',
                'Makyaj',
                'Cilt Bakımı',
                'Saç Bakımı',
                'Kişisel Bakım Aletleri',
            ],
        ];

        $groupSort = 10;

        foreach ($groups as $group => $children) {
            $parent = Category::query()->updateOrCreate(
                ['slug' => Str::slug($group)],
                [
                    'name' => $group,
                    'status' => 'published',
                    'description' => $this->groupDescription($group),
                    'sort_order' => $groupSort,
                ],
            );

            StorefrontNavigationItem::query()->updateOrCreate(
                ['location' => 'mega', 'url' => $parent->url],
                [
                    'title' => $parent->name,
                    'status' => 'published',
                    'sort_order' => $groupSort,
                ],
            );

            foreach ($children as $index => $child) {
                Category::query()->updateOrCreate(
                    ['slug' => Str::slug($child)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $child,
                        'status' => 'published',
                        'description' => $parent->name.' kategorisinde seçili ürünler.',
                        'sort_order' => $groupSort + $index + 1,
                    ],
                );
            }

            $groupSort += 100;
        }

        $this->seedNavigation();
        $this->seedHeroBanner();
        $this->seedProducts();
    }

    private function seedNavigation(): void
    {
        $items = [
            ['top', 'Siparişlerim', '/hesabim/siparislerim', 10],
            ['top', 'İade ve Değişim', '/yasal/iade-degisim-politikasi', 20],
            ['top', 'İletişim', '/iletisim', 30],
            ['footer', 'Hakkımızda', '/yasal/hakkimizda', 10],
            ['footer', 'Misyonumuz', '/yasal/misyonumuz', 20],
            ['footer', 'İletişim', '/iletisim', 30],
        ];

        foreach ($items as [$location, $title, $url, $sortOrder]) {
            StorefrontNavigationItem::query()->updateOrCreate(
                ['location' => $location, 'url' => $url],
                [
                    'title' => $title,
                    'status' => 'published',
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }

    private function seedHeroBanner(): void
    {
        $banners = [
            [
                'title' => 'Karacabey’in Premium Alışveriş Noktası',
                'subtitle' => 'Mobilya, teknoloji, beyaz eşya ve daha fazlası tek çatı altında.',
                'cta_label' => 'Keşfet',
                'cta_url' => '/arama',
                'image_path' => '/images/banners/erler-premium-alisveris.png',
            ],
            [
                'title' => 'Evinize Şıklık Katın',
                'subtitle' => 'Modern salon takımları, dekorasyon ve konfor bir arada.',
                'cta_label' => 'Mobilyaları incele',
                'cta_url' => '/kategori/mobilya-ev-yasam',
                'image_path' => '/images/banners/erler-mobilya-siklik.png',
            ],
            [
                'title' => 'Teknoloji ve Beyaz Eşya Dünyası',
                'subtitle' => 'Yeni nesil elektronik, küçük ev aletleri ve avantajlı seçenekler.',
                'cta_label' => 'Ürünleri gör',
                'cta_url' => '/kategori/beyaz-esya-ev-elektronigi',
                'image_path' => '/images/banners/erler-teknoloji-beyaz-esya.png',
            ],
            [
                'title' => 'Hızlı Teslimat, Güvenli Hizmet',
                'subtitle' => 'Karacabey ve çevresine pratik teslimat ve güven veren alışveriş deneyimi.',
                'cta_label' => 'Yol tarifi al',
                'cta_url' => '/iletisim',
                'image_path' => '/images/banners/erler-hizli-teslimat.png',
            ],
            [
                'title' => 'Sizi de Bekliyoruz',
                'subtitle' => 'Runguşpaşa, Bursa Cd. No:87 Karacabey/Bursa mağazamıza bekleriz.',
                'cta_label' => 'İletişime geç',
                'cta_url' => '/iletisim',
                'image_path' => '/images/banners/erler-sizi-bekliyoruz.png',
            ],
        ];

        foreach ($banners as $index => $banner) {
            StorefrontBanner::query()->updateOrCreate(
                ['placement' => 'home_hero', 'sort_order' => ($index + 1) * 10],
                [
                    ...$banner,
                    'status' => 'published',
                ],
            );
        }
    }

    private function seedProducts(): void
    {
        Product::query()->whereIn('sku', [
            'ERL-TEL-X1-128',
            'ERL-BEYAZ-BZD-520',
            'ERL-MOB-KLT-MOD',
        ])->delete();

        $products = [
            [
                'brand' => 'Arçelik',
                'category' => 'Çamaşır Makinesi',
                'name' => 'Arçelik 9100 M 9 kg Çamaşır Makinesi',
                'sku' => 'ARC-9100-M',
                'price' => '25999.00',
                'compare_at_price' => '28999.00',
                'stock_quantity' => 12,
                'short_description' => '9 kg kapasite, 1000 devir ve buhar destekli yıkama teknolojisi.',
                'description' => 'ProSmart Inverter motor, 15 program ve çamaşır ekleme özelliğiyle günlük yıkama ihtiyaçları için geliştirilmiştir.',
                'images' => ['/images/products/arcelik-9100-m.webp'],
            ],
            [
                'brand' => 'Arçelik',
                'category' => 'Bulaşık Makinesi',
                'name' => 'Arçelik 6144 Bulaşık Makinesi',
                'sku' => 'ARC-6144',
                'price' => '22499.00',
                'compare_at_price' => '24999.00',
                'stock_quantity' => 8,
                'short_description' => '14 kişilik kapasite, HijyenMax ve ekstra kurutma fonksiyonu.',
                'description' => 'Dört ana program, yarım yük yıkama ve 48 dBA ses seviyesiyle pratik ve hijyenik bulaşık bakımı sunar.',
                'images' => ['/images/products/arcelik-6144.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Buzdolabı',
                'name' => 'Bosch Serie 4 KGN56XIE0N No Frost Buzdolabı',
                'sku' => 'BSH-KGN56XIE0N',
                'price' => '59990.00',
                'compare_at_price' => '68250.00',
                'stock_quantity' => 6,
                'short_description' => '193 × 70 cm, VitaFresh ve Total No Frost teknolojisi.',
                'description' => 'Ayarlanabilir cam raflar, BigBox dondurucu çekmecesi ve LED aydınlatmayla geniş, düzenli saklama alanı sağlar.',
                'images' => ['/images/products/bosch-kgn56xie0n.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Çamaşır Makinesi',
                'name' => 'Bosch Serie 4 WGA142ZXTR Çamaşır Makinesi',
                'sku' => 'BSH-WGA142ZXTR',
                'price' => '32990.00',
                'compare_at_price' => '36990.00',
                'stock_quantity' => 9,
                'short_description' => '9 kg kapasite, 1200 devir ve EcoSilence Drive motor.',
                'description' => 'SpeedPerfect, Allergy Plus ve yeniden yükleme fonksiyonlarıyla sessiz ve esnek yıkama deneyimi sunar.',
                'images' => ['/images/products/bosch-wga142zxtr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Bulaşık Makinesi',
                'name' => 'Bosch Serie 4 SMS4IKW62T Bulaşık Makinesi',
                'sku' => 'BSH-SMS4IKW62T',
                'price' => '31490.00',
                'compare_at_price' => '34990.00',
                'stock_quantity' => 10,
                'short_description' => '6 program, Home Connect ve VarioSpeed desteği.',
                'description' => 'Uzaktan kontrol, sıcak ön yıkama ve favori program özellikleriyle günlük bulaşık bakımını kolaylaştırır.',
                'images' => ['/images/products/bosch-sms4ikw62t.webp'],
            ],
        ];

        foreach ($products as $product) {
            $category = Category::query()->where('slug', Str::slug($product['category']))->first();
            $brand = Brand::query()->updateOrCreate(
                ['slug' => Str::slug($product['brand'])],
                [
                    'name' => $product['brand'],
                    'status' => 'published',
                    'description' => $product['brand'].' beyaz eşya ürünleri.',
                ],
            );

            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $category?->id,
                    'brand_id' => $brand->id,
                    'name' => $product['name'],
                    'slug' => Str::slug($product['name']),
                    'price' => $product['price'],
                    'compare_at_price' => $product['compare_at_price'],
                    'stock_quantity' => $product['stock_quantity'],
                    'status' => 'published',
                    'short_description' => $product['short_description'],
                    'description' => $product['description'],
                    'images' => $product['images'],
                    'published_at' => now(),
                ],
            );
        }
    }

    private function groupDescription(string $group): string
    {
        return match ($group) {
            'Teknoloji & Elektronik' => 'Telefon, bilgisayar, televizyon, oyun ve akıllı yaşam ürünleri.',
            'Beyaz Eşya & Ev Elektroniği' => 'Büyük beyaz eşya, küçük ev aletleri ve ev elektroniği çözümleri.',
            'Mobilya & Ev Yaşam' => 'Mobilya, dekorasyon, tekstil ve yaşam alanı koleksiyonları.',
            'Bahçe & Yapı Market' => 'Bahçe mobilyaları, bitki ve dış mekan ekipmanları.',
            'Kozmetik & Kişisel Bakım' => 'Kişisel bakım, kozmetik, parfüm ve güzellik ürünleri.',
            default => 'Erler AVM kategori vitrini.',
        };
    }
}
