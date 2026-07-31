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
                'Kurutma Makinesi',
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
                'title' => 'Evinizin Teknolojisini Yenileyin',
                'subtitle' => 'Yeni nesil beyaz eşyalarda seçili modeller ve ErlerAVM güvencesi.',
                'cta_label' => 'Ürünleri keşfet',
                'cta_url' => '/kategori/beyaz-esya-ev-elektronigi',
                'image_path' => '/images/banners/erler-ev-teknolojileri-2026.webp',
            ],
            [
                'title' => 'Evinize Şıklık Katın',
                'subtitle' => 'Modern salon takımları, dekorasyon ve konfor bir arada.',
                'cta_label' => 'Mobilyaları incele',
                'cta_url' => '/kategori/mobilya-ev-yasam',
                'image_path' => '/images/banners/erler-yasam-alani-2026.webp',
            ],
            [
                'title' => 'Akıllı Yaşam, Güçlü Teknoloji',
                'subtitle' => 'Eğlence ve akıllı ev teknolojileriyle yaşam alanınıza enerji katın.',
                'cta_label' => 'Teknolojiyi incele',
                'cta_url' => '/kategori/teknoloji-elektronik',
                'image_path' => '/images/banners/erler-akilli-teknoloji-2026.webp',
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
                'features' => '<ul><li>9 kg yıkama kapasitesi</li><li>1000 dev./dak. sıkma</li><li>ProSmart Inverter motor</li><li>Buhar destekli yıkama</li><li>15 program ve çamaşır ekleme</li></ul>',
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
                'features' => '<ul><li>14 kişilik kapasite</li><li>4 program</li><li>HijyenMax</li><li>Yarım yük seçeneği</li><li>48 dBA ses seviyesi</li></ul>',
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
                'features' => '<ul><li>Total No Frost</li><li>VitaFresh tazelik bölmesi</li><li>BigBox dondurucu çekmecesi</li><li>LED aydınlatma</li><li>70 cm genişlik</li></ul>',
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
                'features' => '<ul><li>9 kg yıkama kapasitesi</li><li>1200 dev./dak. sıkma</li><li>EcoSilence Drive</li><li>SpeedPerfect</li><li>Allergy Plus</li></ul>',
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
                'features' => '<ul><li>6 program</li><li>Home Connect</li><li>VarioSpeed</li><li>Favori program</li><li>Uzaktan kontrol</li></ul>',
                'images' => ['/images/products/bosch-sms4ikw62t.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Çamaşır Makinesi',
                'name' => 'Bosch Serie 8 WGB254A0TR 10 kg Çamaşır Makinesi',
                'sku' => 'BSH-WGB254A0TR',
                'price' => '69990.00',
                'compare_at_price' => '75990.00',
                'stock_quantity' => 5,
                'short_description' => '10 kg, 1400 devir, i-DOS ve Home Connect özellikli Serie 8.',
                'description' => 'Otomatik deterjan dozajlayan i-DOS, Iron Steam Assist ve Mini Yük seçenekleriyle hem tam dolu hem tek parçalık yıkamalarda kontrollü bakım sağlar.',
                'features' => '<ul><li>10 kg kapasite</li><li>1400 dev./dak.</li><li>A enerji sınıfı</li><li>i-DOS otomatik dozaj</li><li>Home Connect</li><li>Iron Steam Assist</li></ul>',
                'images' => ['/images/products/bosch-wgb254a0tr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Kurutma Makinesi',
                'name' => 'Bosch Serie 6 WQG244C1TR Isı Pompalı Kurutma Makinesi',
                'sku' => 'BSH-WQG244C1TR',
                'price' => '47210.00',
                'compare_at_price' => '51990.00',
                'stock_quantity' => 7,
                'short_description' => 'Isı pompası, AutoDry ve kendini temizleyen kondenser.',
                'description' => 'AutoDry sensörleri hedeflenen kuruluk seviyesini takip eder; kendini temizleyen kondenser ve hassas kurutma sistemi bakımı kolaylaştırır.',
                'features' => '<ul><li>Isı pompalı kurutma</li><li>AutoDry sensörleri</li><li>Kendini temizleyen kondenser</li><li>Hassas Kurutma Sistemi</li><li>40 dakika hızlı program</li><li>Titreşim önleyici yan duvarlar</li></ul>',
                'images' => ['/images/products/bosch-wqg244c1tr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Buzdolabı',
                'name' => 'Bosch Serie 6 KGN86AID2N XXL No Frost Buzdolabı',
                'sku' => 'BSH-KGN86AID2N',
                'price' => '109990.00',
                'compare_at_price' => '119990.00',
                'stock_quantity' => 4,
                'short_description' => 'XXL hacim, VitaFresh ve alttan donduruculu No Frost tasarım.',
                'description' => 'Geniş aileler için tasarlanan XXL iç hacim; VitaFresh çekmeceleri, esnek raf sistemi ve No Frost teknolojisiyle düzenli saklama sunar.',
                'features' => '<ul><li>XXL iç hacim</li><li>No Frost</li><li>VitaFresh tazelik sistemi</li><li>Alttan dondurucu</li><li>LED aydınlatma</li><li>Inox görünüm</li></ul>',
                'images' => ['/images/products/bosch-kgn86aid2n.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Fırın',
                'name' => 'Bosch Serie 8 HBG7341B1 Ankastre Fırın',
                'sku' => 'BSH-HBG7341B1',
                'price' => '70730.00',
                'compare_at_price' => '76990.00',
                'stock_quantity' => 6,
                'short_description' => 'TFT dokunmatik ekran, 14 pişirme modu ve Home Connect.',
                'description' => 'Digital Control Ring ve Fırın Asistanı ile uygun ısı türü, sıcaklık ve süre seçimini kolaylaştıran siyah Serie 8 ankastre fırın.',
                'features' => '<ul><li>14 ısıtma modu</li><li>TFT dokunmatik ekran</li><li>Digital Control Ring</li><li>EcoClean Direct</li><li>Fırın Asistanı</li><li>Home Connect</li></ul>',
                'images' => ['/images/products/bosch-hbg7341b1.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Çamaşır Makinesi',
                'name' => 'Bosch Serie 8 WNC254A0TR Kurutmalı Çamaşır Makinesi',
                'sku' => 'BSH-WNC254A0TR',
                'price' => '101880.00',
                'compare_at_price' => '109990.00',
                'stock_quantity' => 3,
                'short_description' => 'Yıkama ve kurutma, AutoDry, Iron Assist ve Home Connect.',
                'description' => 'Tek gövdede yıkama ve kurutmayı birleştiren model; otomatik yük algılama, buhar desteği ve uzaktan kontrol ile alan kazandırır.',
                'features' => '<ul><li>1400 dev./dak.</li><li>Yıkama ve kurutma</li><li>AutoDry</li><li>Iron Assist</li><li>EcoSilence Drive</li><li>Home Connect</li></ul>',
                'images' => ['/images/products/bosch-wnc254a0tr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Kurutma Makinesi',
                'name' => 'Bosch Serie 8 WQB245AXTR 9 kg Kurutma Makinesi',
                'sku' => 'BSH-WQB245AXTR',
                'price' => '74500.00',
                'compare_at_price' => '79990.00',
                'stock_quantity' => 5,
                'short_description' => '9 kg kapasite, ısı pompası ve akıllı kurutma programları.',
                'description' => 'Gümüş renkli Serie 8 tasarım; sensörlü kurutma ve bağlantılı program seçenekleriyle çamaşırları aşırı ısıtmadan hedeflenen kuruluğa getirir.',
                'features' => '<ul><li>9 kg kapasite</li><li>Isı pompası</li><li>AutoDry</li><li>Home Connect</li><li>Hassas kurutma</li><li>Gümüş gövde</li></ul>',
                'images' => ['/images/products/bosch-wqb245axtr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Çamaşır Makinesi',
                'name' => 'Bosch Serie 6 WGK264ZRTR 11 kg Çamaşır Makinesi',
                'sku' => 'BSH-WGK264ZRTR',
                'price' => '59660.00',
                'compare_at_price' => '64990.00',
                'stock_quantity' => 8,
                'short_description' => '11 kg, 1400 devir ve antrasit Serie 6 tasarım.',
                'description' => 'Yüksek kapasiteli tambur, hızlı program seçenekleri ve sessiz inverter motoruyla kalabalık ailelerin yoğun yıkama ihtiyaçlarına uygundur.',
                'features' => '<ul><li>11 kg kapasite</li><li>1400 dev./dak.</li><li>EcoSilence Drive</li><li>SpeedPerfect</li><li>Yük algılama</li><li>Antrasit renk</li></ul>',
                'images' => ['/images/products/bosch-wgk264zrtr.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Fırın',
                'name' => 'Bosch Serie 8 HBG7764B1 Ankastre Fırın',
                'sku' => 'BSH-HBG7764B1',
                'price' => '104980.00',
                'compare_at_price' => '112990.00',
                'stock_quantity' => 4,
                'short_description' => 'Serie 8 siyah ankastre fırın, akıllı pişirme asistanı.',
                'description' => 'Gelişmiş dokunmatik kontrol, otomatik pişirme desteği ve bağlantılı özellikleriyle premium ankastre mutfaklar için tasarlanmıştır.',
                'features' => '<ul><li>60 × 60 cm</li><li>TFT dokunmatik ekran</li><li>Digital Control Ring</li><li>Pişirme asistanı</li><li>Otomatik kapanma</li><li>Home Connect</li></ul>',
                'images' => ['/images/products/bosch-hbg7764b1.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Bulaşık Makinesi',
                'name' => 'Bosch Serie 8 SMI8ZDS81T Yarı Ankastre Bulaşık Makinesi',
                'sku' => 'BSH-SMI8ZDS81T',
                'price' => '86410.00',
                'compare_at_price' => '92990.00',
                'stock_quantity' => 3,
                'short_description' => '60 cm yarı ankastre, paslanmaz çelik ve akıllı bağlantı.',
                'description' => 'Serie 8 yarı ankastre yapı; esnek sepet sistemi, hassas yıkama programları ve uzaktan kontrol ile modern mutfaklara uyum sağlar.',
                'features' => '<ul><li>60 cm yarı ankastre</li><li>Paslanmaz çelik panel</li><li>Home Connect</li><li>Esnek sepet sistemi</li><li>Hassas yıkama</li><li>Serie 8 tasarım</li></ul>',
                'images' => ['/images/products/bosch-smi8zds81t.webp'],
            ],
            [
                'brand' => 'Bosch',
                'category' => 'Ocak',
                'name' => 'Bosch Serie 4 PVS811B16E Davlumbaz Entegreli İndüksiyon Ocak',
                'sku' => 'BSH-PVS811B16E',
                'price' => '112850.00',
                'compare_at_price' => '119990.00',
                'stock_quantity' => 2,
                'short_description' => '80 cm çerçevesiz indüksiyon ocak ve entegre havalandırma.',
                'description' => 'İndüksiyon pişirme yüzeyi ile davlumbazı tek gövdede birleştirir; ada ve açık mutfaklarda sade, kesintisiz bir görünüm sağlar.',
                'features' => '<ul><li>80 cm genişlik</li><li>İndüksiyon teknolojisi</li><li>Entegre davlumbaz</li><li>Çerçevesiz yüzey</li><li>Dokunmatik kontrol</li><li>Serie 4</li></ul>',
                'images' => ['/images/products/bosch-pvs811b16e.webp'],
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
                    'features' => $product['features'] ?? null,
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
