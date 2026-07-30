<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\CorporatePage;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    private const PAGES = [
        'kurumsal' => 'Kurumsal',
        'hakkimizda' => 'Hakkımızda',
        'vizyon-misyon' => 'Vizyon & Misyon',
        'magazalarimiz' => 'Mağazalarımız',
        'kariyer-ve-is-basvurusu' => 'Kariyer ve İş Başvurusu',
        'banka-hesaplarimiz' => 'Banka Hesaplarımız',
        'teslimat-ve-kargo' => 'Teslimat ve Kargo',
        'guvenli-alisveris' => 'Güvenli Alışveriş',
        'satis-kosullari' => 'Satış Koşulları',
        'iade-degisim-politikasi' => 'İade ve Değişim Politikası',
        'mesafeli-satis-sozlesmesi' => 'Mesafeli Satış Sözleşmesi',
        'on-bilgilendirme-formu' => 'Ön Bilgilendirme Formu',
        'gizlilik-politikasi' => 'Gizlilik Politikası',
        'kvkk-aydinlatma-metni' => 'KVKK Aydınlatma Metni',
        'cerez-politikasi' => 'Çerez Politikası',
        'uyelik-sozlesmesi' => 'Kullanım Şartları ve Üyelik Sözleşmesi',
        'odeme-secenekleri' => 'Ödeme Seçenekleri',
    ];

    public function show(string $page): Response
    {
        $storedPage = CorporatePage::query()
            ->where('slug', $page)
            ->first();

        if ($storedPage) {
            abort_unless($storedPage->status === 'published' && $storedPage->is_active, 404);

            return Inertia::render('Legal/Show', [
                'title' => $storedPage->title,
                'slug' => $storedPage->slug,
                'description' => data_get($storedPage->seo_meta, 'description') ?: $this->description($storedPage->slug),
                'content_html' => $this->sanitizeAdminHtml((string) $storedPage->content),
                'sections' => [],
            ]);
        }

        abort_unless(array_key_exists($page, self::PAGES), 404);

        return Inertia::render('Legal/Show', [
            'title' => self::PAGES[$page],
            'slug' => $page,
            'description' => $this->description($page),
            'content_html' => null,
            'sections' => $this->sections($page),
        ]);
    }

    private function description(string $page): string
    {
        return match ($page) {
            'hakkimizda' => 'Erler AVM’nin Karacabey’de başlayan ve bölgesel mağazacılığa uzanan hikayesi.',
            'misyonumuz' => 'Müşteri memnuniyeti, güvenilir marka seçimi ve ulaşılabilir ödeme seçenekleriyle sürdürülebilir mağazacılık anlayışımız.',
            default => 'Bu sayfanın içeriği şirket bilgileri, güncel mevzuat ve operasyon kararlarıyla yayınlanır.',
        };
    }

    /**
     * @return array<int, array{heading: string, body: string}>
     */
    private function sections(string $page): array
    {
        if ($page === 'hakkimizda') {
            return [
                [
                    'heading' => '1998’den bugüne güven odaklı mağazacılık',
                    'body' => '1998 yılında sadece züccaciye ürünleri satışı yapan firmamız, bugün müşterilerine en çok güven duyulan uluslararası ve yerli markaları sunan güçlü bir alışveriş deneyimine dönüşmüştür. Erler AVM, Karacabey’de teknoloji, beyaz eşya, mobilya, ev yaşam ve günlük ihtiyaç kategorilerinde müşterilerine geniş ürün seçeneği sağlar.',
                ],
                [
                    'heading' => 'Konforlu mağaza deneyimi',
                    'body' => 'Mağazalarımız müşterilerimizin alışveriş esnasındaki konforu, memnuniyeti ve güvenli karar verebilmesi düşünülerek tasarlanmıştır. Ürün seçimi, ödeme seçenekleri, satış sonrası destek ve mağaza içi yönlendirme süreçleri aynı hizmet kalitesini koruyacak şekilde yönetilir.',
                ],
                [
                    'heading' => 'Bölgenin istikrarlı büyüyen markalarından biri',
                    'body' => 'Nakit ve kredi kartlı ödeme seçeneklerimizin yanında; peşinatsız, kredi kartsız ve sözleşmeli ödeme seçenekleriyle farklı müşteri ihtiyaçlarına yanıt veriyoruz. Yaklaşık 70 çalışanımızla Erler AVM olarak Bursa’nın ve bölgenin parlak, istikrarlı ve hızlı büyüyen mağazalar zincirleri arasında yer almaktayız.',
                ],
            ];
        }

        if ($page === 'misyonumuz') {
            return [
                [
                    'heading' => 'Misyonumuz',
                    'body' => 'Misyonumuz; müşterilerimize güvenilir markaları, doğru ürün bilgisini, ulaşılabilir ödeme seçeneklerini ve satış sonrası desteği bir arada sunmaktır. Her alışverişte şeffaf iletişim, doğru fiyatlandırma ve sürdürülebilir memnuniyet hedeflenir.',
                ],
                [
                    'heading' => 'Hizmet anlayışımız',
                    'body' => 'Erler AVM olarak mağaza deneyimini sadece ürün satışı olarak görmüyoruz. Müşterinin ihtiyacını doğru anlamak, uygun ürüne yönlendirmek, teslimat ve destek süreçlerini takip etmek hizmet anlayışımızın temel parçalarıdır.',
                ],
                [
                    'heading' => 'Yerel güç, güçlü marka seçimi',
                    'body' => 'Karacabey merkezli yapımızı güçlü marka portföyüyle birleştirerek bölgedeki müşterilerimiz için güvenilir, ulaşılabilir ve kalıcı bir alışveriş noktası olmayı amaçlıyoruz.',
                ],
            ];
        }

        return [];
    }

    private function sanitizeAdminHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><span>';
        $clean = strip_tags($html, $allowedTags);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string) $clean);
        $clean = preg_replace('/javascript\s*:/i', '', (string) $clean);

        return trim((string) $clean);
    }
}
