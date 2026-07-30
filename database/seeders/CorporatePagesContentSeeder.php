<?php

namespace Database\Seeders;

use App\Models\CorporatePage;
use Illuminate\Database\Seeder;

class CorporatePagesContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'hakkimizda' => [
                'title' => 'Hakkımızda',
                'seo_meta' => [
                    'title' => 'Hakkımızda | Erler AVM',
                    'description' => 'Erler AVM olarak 1998 yılından bu yana kaliteli ürünleri uygun fiyatlarla müşterilerimize sunmaktan gurur duyuyoruz. Hikayemizi keşfedin.',
                ],
                'content' => <<<'HTML'
<h2>1998’den Bugüne Güven Odaklı Mağazacılık</h2>
<p>1998 yılında sadece züccaciye ürünleri satışı yapan firmamız, bugün müşterilerine en çok güven duyulan uluslararası ve yerli markaları sunan güçlü bir alışveriş platformuna dönüşmüştür. <strong>Erler AVM</strong> olarak evini yenilemek, teknolojiyle buluşmak ve hayatını kolaylaştırmak isteyen binlerce müşterimize yıllardır kesintisiz hizmet sunuyoruz.</p>

<h3>Fark Yaratan Hizmet Anlayışımız</h3>
<p>Bizi diğerlerinden ayıran en önemli özellik, müşterilerimizle kurduğumuz samimi ve şeffaf iletişimdir. Bir ürünü satmaktan öte, doğru ihtiyaca doğru çözümü sunmayı temel felsefemiz edindik.</p>
<ul>
    <li><strong>Zengin Ürün Çeşitliliği:</strong> Elektronikten beyaz eşyaya, küçük ev aletlerinden mobilyaya kadar geniş bir ürün yelpazesi sunuyoruz.</li>
    <li><strong>Orijinal Ürün Garantisi:</strong> Tüm ürünlerimiz resmi distribütör garantili ve sıfır kapalı kutudur.</li>
    <li><strong>Sürekli Gelişim:</strong> E-ticaret dünyasındaki en son trendleri takip ediyor, alışveriş deneyiminizi iyileştirmek için altyapımıza sürekli yatırım yapıyoruz.</li>
</ul>

<h3>Geleceğe Bakış</h3>
<p>Fiziksel mağazalarımızdaki sıcak yaklaşımımızı dijital dünyaya da taşıyarak, Türkiye'nin dört bir yanındaki müşterilerimize aynı konforlu alışveriş deneyimini sunmaya devam edeceğiz.</p>
HTML
            ],
            'vizyon-misyon' => [
                'title' => 'Vizyon & Misyon',
                'seo_meta' => [
                    'title' => 'Vizyon ve Misyonumuz | Erler AVM',
                    'description' => 'Erler AVM misyonu, vizyonu ve temel değerleri. Müşteri memnuniyetini merkeze alan hizmet anlayışımız hakkında bilgi edinin.',
                ],
                'content' => <<<'HTML'
<h2>Vizyonumuz</h2>
<p>Sektördeki yenilikleri yakından takip eden, müşteri memnuniyetini en üst düzeyde tutan ve e-ticaret ekosisteminde Türkiye'nin en çok güven duyulan ve tercih edilen perakende markalarından biri olmak.</p>

<h2>Misyonumuz</h2>
<p>Müşterilerimizin günlük hayatını kolaylaştıran, onlara değer katan dünya markalarını ve kaliteli ürünleri; en uygun ödeme seçenekleri, kusursuz teslimat altyapısı ve eksiksiz satış sonrası destek ile sunmak.</p>

<h3>Temel Değerlerimiz</h3>
<ul>
    <li><strong>Şeffaflık:</strong> Müşterilerimize karşı her zaman dürüst, açık ve anlaşılır olmak.</li>
    <li><strong>Güvenilirlik:</strong> Söz verdiğimiz zamanda, söz verdiğimiz kalitede hizmet sunmak.</li>
    <li><strong>Sürekli İyileştirme:</strong> Alışveriş deneyimini kusursuzlaştırmak için her gün yeni bir şeyler öğrenmek ve uygulamak.</li>
    <li><strong>Empati:</strong> Müşterilerimizin ihtiyaçlarını kendi ihtiyacımız gibi benimsemek ve çözüm üretmek.</li>
</ul>
HTML
            ],
            'magazalarimiz' => [
                'title' => 'Mağazalarımız',
                'seo_meta' => [
                    'title' => 'Mağazalarımız | Erler AVM',
                    'description' => 'Erler AVM fiziksel mağazalarının adresleri, iletişim bilgileri ve çalışma saatleri. Bizi ziyaret edin.',
                ],
                'content' => <<<'HTML'
<h2>Fiziksel Mağazalarımızda Sizi Bekliyoruz</h2>
<p>Erler AVM'nin geniş ürün yelpazesini yakından incelemek, ürünleri deneyimlemek ve uzman satış danışmanlarımızdan bilgi almak için sizleri mağazalarımıza davet ediyoruz.</p>

<h3>Merkez Mağaza - Karacabey</h3>
<p><strong>Adres:</strong> Runguçpaşa Mahallesi, Panayır Caddesi No: 15, Karacabey / Bursa<br>
<strong>Telefon:</strong> 0224 676 XX XX<br>
<strong>E-posta:</strong> magaza@erleravm.test</p>

<h4>Çalışma Saatleri</h4>
<ul>
    <li><strong>Pazartesi - Cuma:</strong> 09:00 - 19:30</li>
    <li><strong>Cumartesi:</strong> 09:00 - 19:30</li>
    <li><strong>Pazar:</strong> Kapalı (E-ticaret sitemizden 7/24 alışveriş yapabilirsiniz.)</li>
</ul>

<p><em>Not: Yakın zamanda yeni açılacak şubelerimizle ilgili duyuruları sosyal medya hesaplarımızdan ve web sitemizden takip edebilirsiniz.</em></p>
HTML
            ],
            'kariyer-ve-is-basvurusu' => [
                'title' => 'Kariyer ve İş Başvurusu',
                'seo_meta' => [
                    'title' => 'Kariyer Fırsatları | Erler AVM',
                    'description' => 'Erler AVM ailesine katılmak ister misiniz? İnsan kaynakları politikamız ve açık pozisyonlar hakkında bilgi alın.',
                ],
                'content' => <<<'HTML'
<h2>Erler AVM Ailesine Katılın</h2>
<p>Büyüyen ekibimizde her zaman dinamik, müşteri odaklı ve gelişime açık çalışma arkadaşlarına yer var! Perakende sektörünün hızına ayak uydurabilen ve takım çalışmasına inanan biriyseniz, sizi de aramızda görmekten mutluluk duyarız.</p>

<h3>İnsan Kaynakları Politikamız</h3>
<p>Çalışanlarımızın yeteneklerini geliştirmelerine olanak tanıyan, fikirlerini özgürce ifade edebilecekleri, adil ve şeffaf bir çalışma ortamı sağlamak en büyük önceliğimizdir. Eğitim fırsatları ve kurum içi kariyer yolları ile çalışanlarımızın uzun yıllar bizimle kalmasını hedefliyoruz.</p>

<h3>Açık Pozisyonlar ve Başvuru</h3>
<p>Şu an için aktif bir iş ilanımız bulunmamaktadır ancak genel başvurularınızı her zaman değerlendirmeye alıyoruz.</p>
<p>Özgeçmişinizi (CV) <strong>ik@erleravm.test</strong> adresine göndererek veya mağazamıza şahsen gelerek başvuru formumuzu doldurabilirsiniz. Niteliklerinize uygun bir pozisyon açıldığında sizinle iletişime geçeceğiz.</p>
HTML
            ],
            'iletisim' => [
                'title' => 'İletişim',
                'seo_meta' => [
                    'title' => 'İletişim ve Müşteri Hizmetleri | Erler AVM',
                    'description' => 'Erler AVM ile iletişime geçin. Soru, öneri ve şikayetleriniz için müşteri hizmetleri kanalımız 7/24 hizmetinizde.',
                ],
                'content' => <<<'HTML'
<h2>Müşteri Hizmetlerimiz Size Yardımcı Olmaya Hazır</h2>
<p>Sipariş süreçleri, ürün detayları, kargo takibi veya aklınıza takılan diğer tüm sorular için aşağıdaki iletişim kanallarından bize ulaşabilirsiniz. Ekibimiz en kısa sürede size geri dönüş yapacaktır.</p>

<ul>
    <li><strong>Müşteri Hizmetleri Numarası:</strong> 0850 XXX XX XX</li>
    <li><strong>Destek E-posta Adresi:</strong> destek@erleravm.test</li>
    <li><strong>WhatsApp Destek Hattı:</strong> 0555 XXX XX XX (Sadece mesaj ile destek verilmektedir)</li>
</ul>

<h3>Çağrı Merkezi Çalışma Saatleri</h3>
<p>Hafta İçi: 09:00 - 18:00<br>
Cumartesi: 09:00 - 13:00</p>
HTML
            ],
            'banka-hesaplarimiz' => [
                'title' => 'Banka Hesaplarımız',
                'seo_meta' => [
                    'title' => 'Banka Hesap Bilgilerimiz | Erler AVM',
                    'description' => 'Erler AVM Havale/EFT işlemleri için kullanabileceğiniz güncel banka hesap ve IBAN numaralarımız.',
                ],
                'content' => <<<'HTML'
<h2>Havale ve EFT İşlemleri İçin Hesap Bilgilerimiz</h2>
<p>Siparişlerinizi Havale/EFT yöntemiyle ödemek isterseniz, aşağıda yer alan banka hesaplarımızı kullanabilirsiniz. Lütfen ödeme yaparken <strong>açıklama kısmına sipariş numaranızı</strong> eksiksiz bir şekilde yazmayı unutmayınız. Sipariş numaranızı yazmamanız durumunda ödemenizin onaylanması gecikebilir.</p>

<h3>Ziraat Bankası</h3>
<ul>
    <li><strong>Alıcı Ünvanı:</strong> Erler AVM Dayanıklı Tüketim Malları Tic. Ltd. Şti.</li>
    <li><strong>Şube Kodu:</strong> 1234 (Karacabey Şubesi)</li>
    <li><strong>Hesap Numarası:</strong> 12345678-5001</li>
    <li><strong>IBAN:</strong> TR12 0001 0000 0000 0000 0000 00</li>
</ul>

<h3>Garanti BBVA</h3>
<ul>
    <li><strong>Alıcı Ünvanı:</strong> Erler AVM Dayanıklı Tüketim Malları Tic. Ltd. Şti.</li>
    <li><strong>Şube Kodu:</strong> 5678</li>
    <li><strong>Hesap Numarası:</strong> 1234567</li>
    <li><strong>IBAN:</strong> TR56 0006 2000 0000 0000 0000 00</li>
</ul>

<p><em>Not: Havale/EFT ile verilen siparişlerde ödeme süresi 3 (üç) iş günüdür. Bu süre içinde ödemesi hesaba geçmeyen siparişler sistem tarafından otomatik olarak iptal edilmektedir.</em></p>
HTML
            ],
            'teslimat-ve-kargo' => [
                'title' => 'Teslimat ve Kargo',
                'seo_meta' => [
                    'title' => 'Kargo ve Teslimat Süreçleri | Erler AVM',
                    'description' => 'Erler AVM kargo seçenekleri, ücretsiz kargo fırsatları ve teslimat süreleri hakkında detaylı bilgi.',
                ],
                'content' => <<<'HTML'
<h2>Kargo Süreçlerimiz ve Ücretsiz Kargo Avantajı</h2>
<p>Erler AVM olarak satın aldığınız ürünleri size en hızlı ve en güvenli şekilde ulaştırmak için Türkiye'nin önde gelen kargo firmaları (Aras Kargo, MNG Kargo, Yurtiçi Kargo, PTT Kargo) ile çalışmaktayız.</p>

<h3>Ücretsiz Kargo Şartları</h3>
<p>Sitemizden yapacağınız <strong>1500 ₺ ve üzeri</strong> tüm alışverişlerde kargo ücreti tamamen tarafımızca karşılanır ve siparişleriniz ücretsiz olarak adresinize teslim edilir. 1500 ₺ altındaki siparişlerde ise sepet ekranında kargo ücreti hesaplanarak toplama yansıtılır.</p>

<h3>Sipariş Hazırlık ve Teslimat Süresi</h3>
<ul>
    <li>Saat 14:00'e kadar verilen siparişler, ürünler stoklarımızda ise genellikle <strong>aynı gün kargoya</strong> verilmektedir.</li>
    <li>Hafta sonu ve resmi tatil günlerinde kargo çıkışı yapılmamaktadır; bu günlerde verilen siparişler takip eden ilk iş gününde kargolanır.</li>
    <li>Teslimat süresi bulunduğunuz il veya ilçeye göre değişiklik göstermekle birlikte ortalama <strong>1-3 iş günü</strong> sürmektedir. (Mobil bölgeler veya köylere yapılan teslimatlarda bu süre uzayabilir.)</li>
</ul>

<h3>Ürün Teslim Alırken Dikkat Edilmesi Gerekenler</h3>
<p>Kargo görevlisi paketinizi teslim ettiğinde, kargo poşetinde veya kutuda yırtık, ezilme veya ıslanma gibi hasar belirtileri olup olmadığını mutlaka kontrol ediniz. Eğer pakette hasar varsa ürünü teslim almayıp kargo görevlisine <strong>"Hasar Tespit Tutanağı"</strong> tutturunuz.</p>
HTML
            ],
            'guvenli-alisveris' => [
                'title' => 'Güvenli Alışveriş',
                'seo_meta' => [
                    'title' => 'Güvenli Alışveriş Altyapısı | Erler AVM',
                    'description' => 'Erler AVM 256-bit SSL ve Akbank 3D Secure altyapısı ile güvenli alışveriş imkanı sunar.',
                ],
                'content' => <<<'HTML'
<h2>%100 Güvenli Alışveriş Altyapısı</h2>
<p>Erler AVM üzerinden yapacağınız tüm alışverişlerde, kişisel verileriniz ve ödeme bilgileriniz en yüksek güvenlik standartları ile korunur. Kredi kartı bilgileriniz sistemimizde kesinlikle <strong>saklanmaz</strong>.</p>

<h3>256-Bit SSL Sertifikası</h3>
<p>Sitemizdeki tüm veri akışı 256-bit SSL (Secure Sockets Layer) teknolojisiyle şifrelenmektedir. Bilgisayarınız/telefonunuz ile sunucularımız arasındaki bilgi transferi, üçüncü şahısların erişemeyeceği şekilde kilitli olarak iletilir.</p>

<h3>Akbank 3D Secure Doğrulama</h3>
<p>Kredi kartı ile yapacağınız ödemelerinizde <strong>Akbank Sanal POS</strong> altyapısını kullanıyoruz. Kart numarası ve CVV bilgileri Erler AVM sistemlerinde saklanmaz.</p>
<p>Tüm kartlı alışverişleriniz <strong>3D Secure (Şifreli Onay)</strong> zorunluluğu ile gerçekleşir. Ödeme esnasında bankanızda kayıtlı cep telefonu numaranıza gelen şifreyi girmeden kartınızdan asla çekim yapılmaz. Bu sayede çalıntı veya kayıp kartlarla işlem yapılması engellenir.</p>

<p>Gönül rahatlığıyla, güvenli bir şekilde alışverişinizi tamamlayabilirsiniz.</p>
HTML
            ],
            'iade-degisim-politikasi' => [
                'title' => 'İade ve Değişim Politikası',
                'seo_meta' => [
                    'title' => 'İade ve Değişim Koşulları | Erler AVM',
                    'description' => 'Satın aldığınız ürünleri 14 gün içerisinde nasıl iade edebileceğinizi veya değiştirebileceğinizi öğrenin.',
                ],
                'content' => <<<'HTML'
<h2>14 Gün İçinde Koşulsuz İade Hakkı</h2>
<p>Erler AVM'den satın aldığınız ürünleri, teslimat tarihinden itibaren <strong>14 gün içerisinde</strong> yasal cayma hakkınızı kullanarak hiçbir gerekçe göstermeksizin ve kargo ücreti ödemeksizin iade edebilirsiniz.</p>

<h3>İade Şartları Nelerdir?</h3>
<ul>
    <li>İade edilmek istenen ürünün <strong>kullanılmamış</strong>, ambalajının bozulmamış ve yeniden satılabilir özelliğini yitirmemiş olması gerekmektedir.</li>
    <li>Ürünün orijinal faturası (veya e-fatura çıktısı), tüm aksesuarları ve varsa hediye ürünleri ile birlikte eksiksiz olarak gönderilmesi zorunludur.</li>
    <li>Hijyenik sebeplerle kişisel bakım ürünleri (tıraş makinesi, epilatör vb.), iç giyim ürünleri ve yazılım programları ancak <strong>ambalajı hiç açılmamışsa</strong> iade alınabilir.</li>
    <li>Kurulumu servis tarafından yapılması gereken beyaz eşya ve televizyon gibi ürünlerin kutusu sadece yetkili servis tarafından açılmalıdır. Servis harici açılan kutularda cayma hakkı geçersiz olur.</li>
</ul>

<h3>İade Süreci Nasıl İşler?</h3>
<p>1. Üye girişi yaparak "Siparişlerim" sayfasından iade talebi oluşturun.<br>
2. Sistem tarafından size verilecek olan <strong>Ücretsiz İade Kargo Kodunu</strong> not alın.<br>
3. Ürünü sağlam bir şekilde paketleyerek anlaşmalı kargo şubesine bu kod ile teslim edin.<br>
4. Ürün depomuza ulaştıktan sonra 2-3 iş günü içerisinde kalite kontrolünden geçirilir. İade onaylandığında ücretiniz, ödeme yaptığınız yönteme göre bankanıza iade edilir.</p>
<p>Kredi kartlarına yapılan iadelerin ekstrenize yansıması bankanızın süreçlerine bağlı olarak 1 ile 7 gün sürebilir.</p>
HTML
            ],
            'odeme-secenekleri' => [
                'title' => 'Ödeme Seçenekleri',
                'seo_meta' => [
                    'title' => 'Ödeme Seçenekleri ve Taksit İmkanları | Erler AVM',
                    'description' => 'Erler AVM ödeme yöntemleri: Kredi kartına taksit, havale/EFT indirimi ve diğer ödeme seçenekleri.',
                ],
                'content' => <<<'HTML'
<h2>Esnek ve Güvenli Ödeme Seçenekleri</h2>
<p>Alışverişlerinizi kolayca tamamlayabilmeniz için size farklı ödeme alternatifleri sunuyoruz.</p>

<h3>1. Kredi Kartı veya Banka Kartı (Tek Çekim & Taksit)</h3>
<p>Bonus, World, Maximum, CardFinans, Paraf, Axess ve Sağlam Kart özellikli kredi kartlarınız ile sitemizden vade farksız taksit avantajları veya vade farklı uzun vadeli taksit imkanlarıyla güvenle alışveriş yapabilirsiniz. Taksit seçenekleri ürün kategorilerine göre (BDDK kuralları gereği) değişiklik gösterebilmektedir.</p>

<h3>2. Havale ve EFT ile Ödeme</h3>
<p>Sipariş tutarını şirketimizin Ziraat Bankası veya Garanti BBVA banka hesaplarına transfer ederek ödemenizi gerçekleştirebilirsiniz. Havale/EFT ile yapılan ödemelerde sepette <strong>ekstra indirim</strong> fırsatları sunulabilmektedir. Ödeme açıklamanıza mutlaka sipariş numaranızı ekleyiniz.</p>

<h3>3. Mağazada Ödeme</h3>
<p>Eğer siparişinizi Karacabey mağazamızdan kendiniz teslim alacaksanız, online siparişinizi oluştururken "Mağazadan Teslimat" seçeneğini seçip ödemenizi mağazamızdaki kasada nakit veya kredi kartıyla gerçekleştirebilirsiniz.</p>

<p><em>Not: Kapıda ödeme seçeneğimiz operasyonel ve güvenlik sebepleriyle geçici olarak kapalıdır.</em></p>
HTML
            ],
            'mesafeli-satis-sozlesmesi' => [
                'title' => 'Mesafeli Satış Sözleşmesi',
                'seo_meta' => [
                    'title' => 'Mesafeli Satış Sözleşmesi | Erler AVM',
                    'description' => 'E-ticaret kanunları gereği tüketicilerimiz ile aramızda akdedilen mesafeli satış sözleşmesi.',
                ],
                'content' => <<<'HTML'
<h2>MESAFELİ SATIŞ SÖZLEŞMESİ</h2>

<h3>MADDE 1 - TARAFLAR</h3>
<p><strong>1.1. SATICI BİLGİLERİ</strong><br>
Unvan: Erler AVM Dayanıklı Tüketim Malları Tic. Ltd. Şti.<br>
Adres: Runguçpaşa Mah. Panayır Cad. No:15 Karacabey / Bursa<br>
E-posta: iletisim@erleravm.test<br>
Mersis No: 0000000000000000<br>
</p>

<p><strong>1.2. ALICI BİLGİLERİ</strong><br>
Alıcı olarak adlandırılacak kişi, sisteme sipariş verirken kullandığı ad, soyad ve adres bilgileri geçerli olan tüketicidir.</p>

<h3>MADDE 2 - KONU</h3>
<p>İşbu Sözleşme'nin konusu, ALICI'nın SATICI'ya ait www.erleravm.test internet sitesinden elektronik ortamda siparişini yaptığı aşağıda nitelikleri ve satış fiyatı belirtilen ürünün satışı ve teslimi ile ilgili olarak 6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği hükümleri gereğince tarafların hak ve yükümlülüklerinin saptanmasıdır.</p>

<h3>MADDE 3 - CAYMA HAKKI</h3>
<p>ALICI, sözleşme konusu ürünün kendisine veya gösterdiği adresteki kişi/kuruluşa tesliminden itibaren 14 (ondört) gün içinde hiçbir hukuki ve cezai sorumluluk üstlenmeksizin ve hiçbir gerekçe göstermeksizin malı reddederek sözleşmeden cayma hakkına sahiptir. Cayma hakkının kullanımından kaynaklanan kargo masrafları SATICI'ya aittir.</p>

<h3>MADDE 4 - UYUŞMAZLIKLARIN ÇÖZÜMÜ</h3>
<p>İşbu sözleşmenin uygulanmasında, Gümrük ve Ticaret Bakanlığınca ilan edilen değere kadar Alıcının mal veya hizmeti satın aldığı ve ikametgahının bulunduğu yerdeki Tüketici Hakem Heyetleri ile Tüketici Mahkemeleri yetkilidir.</p>
HTML
            ],
            'on-bilgilendirme-formu' => [
                'title' => 'Ön Bilgilendirme Formu',
                'seo_meta' => [
                    'title' => 'Ön Bilgilendirme Formu | Erler AVM',
                    'description' => 'Sipariş öncesi tüketici kanunları gereğince okunması ve onaylanması gereken ön bilgilendirme formu.',
                ],
                'content' => <<<'HTML'
<h2>ÖN BİLGİLENDİRME FORMU</h2>

<h3>1. SATICI BİLGİLERİ</h3>
<p><strong>Unvan:</strong> Erler AVM Dayanıklı Tüketim Malları Tic. Ltd. Şti.<br>
<strong>Adres:</strong> Runguçpaşa Mah. Panayır Cad. No:15 Karacabey / Bursa<br>
<strong>Telefon:</strong> 0850 XXX XX XX</p>

<h3>2. SÖZLEŞME KONUSU ÜRÜNLERİN TEMEL NİTELİKLERİ VE FİYATI</h3>
<p>Ürünlerin cinsi ve türü, miktarı, marka/modeli, rengi ve tüm vergiler dâhil satış bedeli www.erleravm.test adlı internet sitesindeki sipariş onayı sayfasında belirtildiği gibidir.</p>

<h3>3. TESLİMAT VE MASRAFLAR</h3>
<p>Teslimat kargo firmaları aracılığı ile Alıcı'nın adresine yapılır. 1500 TL ve üzeri alışverişlerde teslimat masrafı Satıcı'ya aittir. 1500 TL altı siparişlerde kargo bedeli Alıcı tarafından ödenir ve sipariş toplamına eklenir.</p>

<h3>4. CAYMA HAKKI VE KULLANIMI</h3>
<p>Alıcı, ürünü teslim aldığı tarihten itibaren 14 gün içinde koşulsuz cayma hakkına sahiptir. Özel üretim ürünler, hijyen kurallarına tabi iç giyim, yazılım gibi ürünlerin ambalajı açılmışsa cayma hakkı kullanılamaz.</p>
HTML
            ],
            'gizlilik-politikasi' => [
                'title' => 'Gizlilik Politikası',
                'seo_meta' => [
                    'title' => 'Gizlilik Politikası | Erler AVM',
                    'description' => 'Erler AVM kişisel verilerinizin gizliliğine saygı duyar. Hangi verileri topladığımızı ve nasıl kullandığımızı öğrenin.',
                ],
                'content' => <<<'HTML'
<h2>GİZLİLİK POLİTİKASI</h2>
<p>Erler AVM olarak sitemizi ziyaret eden tüm kullanıcılarımızın kişisel gizliliğine ve bilgi güvenliğine önem veriyoruz.</p>

<h3>1. Toplanan Bilgiler</h3>
<p>Sitemize üye olurken veya sipariş verirken isim, soyisim, e-posta adresi, telefon numarası ve teslimat adresi gibi kimlik ve iletişim bilgilerinizi topluyoruz. Bu bilgiler yalnızca siparişinizin size sorunsuz bir şekilde ulaştırılması için kullanılır.</p>

<h3>2. Bilgilerin Kullanımı ve Korunması</h3>
<p>Kredi kartı bilgileriniz kesinlikle sistemlerimizde tutulmamakta ve kaydedilmemektedir. Ödemeleriniz doğrudan Akbank Sanal POS sistemine 256-bit SSL şifreleme üzerinden aktarılır.<br>
İletişim bilgileriniz, onay vermiş olmanız halinde kampanya ve duyurularımızdan sizi haberdar etmek amacıyla bülten gönderiminde kullanılabilir.</p>

<h3>3. Üçüncü Kişilerle Paylaşım</h3>
<p>Kişisel verileriniz, yasal zorunluluklar haricinde kesinlikle üçüncü kişi veya kurumlarla satılamaz, kiralanamaz ve paylaşılamaz. Sadece kargo teslimatı için isim, adres ve telefon bilgileriniz ilgili kargo firmasına aktarılmaktadır.</p>
HTML
            ],
            'kvkk-aydinlatma-metni' => [
                'title' => 'KVKK Aydınlatma Metni',
                'seo_meta' => [
                    'title' => 'KVKK Aydınlatma Metni | Erler AVM',
                    'description' => '6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) uyarınca hazırlanan aydınlatma metnimiz.',
                ],
                'content' => <<<'HTML'
<h2>KİŞİSEL VERİLERİN KORUNMASI (KVKK) AYDINLATMA METNİ</h2>
<p>Erler AVM Dayanıklı Tüketim Malları Tic. Ltd. Şti. (Veri Sorumlusu) olarak, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, müşterilerimize ve web sitesi kullanıcılarımıza ait kişisel verilerin işlenmesine ilişkin sizleri bilgilendirmek isteriz.</p>

<h3>Kişisel Verilerin İşlenme Amacı</h3>
<p>Sizlerden topladığımız isim, soyisim, iletişim, adres ve ödeme bilgileriniz;<br>
- Satış sözleşmelerinin kurulması ve ifası,<br>
- Ürün teslimatlarının sorunsuz gerçekleştirilmesi,<br>
- Fatura düzenlenmesi ve muhasebe kayıtlarının tutulması,<br>
- Talep ve şikayetlerin değerlendirilerek müşteri hizmetleri desteği sağlanması<br>
amaçlarıyla hukuka ve dürüstlük kurallarına uygun olarak işlenmektedir.</p>

<h3>Kişisel Verilerin Aktarılması</h3>
<p>Kişisel verileriniz, kanuni yükümlülüklerin yerine getirilmesi amacıyla yetkili kamu kurumlarıyla; ürün teslimatı için kargo firmalarıyla ve e-fatura/e-arşiv süreçleri için entegratör firmalarla mevzuata uygun şekilde paylaşılmaktadır.</p>

<h3>İlgili Kişinin Hakları</h3>
<p>KVKK 11. maddesi uyarınca veri sahibi olarak; kişisel verilerinizin işlenip işlenmediğini öğrenme, amacına uygun kullanılıp kullanılmadığını bilme, eksik veya yanlış işlenmişse düzeltilmesini isteme ve kanunda öngörülen şartlar çerçevesinde silinmesini talep etme hakkına sahipsiniz. Başvurularınızı iletisim@erleravm.test adresine yapabilirsiniz.</p>
HTML
            ],
            'cerez-politikasi' => [
                'title' => 'Çerez Politikası',
                'seo_meta' => [
                    'title' => 'Çerez (Cookie) Politikası | Erler AVM',
                    'description' => 'Sitemizde deneyiminizi iyileştirmek için kullandığımız çerezler hakkında bilgilendirme.',
                ],
                'content' => <<<'HTML'
<h2>ÇEREZ (COOKIE) POLİTİKASI</h2>
<p>İnternet sitemizden en verimli şekilde faydalanabilmeniz ve kullanıcı deneyiminizi geliştirebilmek için Çerezler (Cookies) kullanıyoruz. Çerez kullanılmasını tercih etmezseniz tarayıcınızın ayarlarından çerezleri silebilir veya engelleyebilirsiniz.</p>

<h3>Çerez Nedir?</h3>
<p>Çerezler, ziyaret ettiğiniz internet siteleri tarafından tarayıcınız aracılığıyla cihazınıza (bilgisayar, telefon vb.) depolanan küçük metin dosyalarıdır.</p>

<h3>Hangi Çerezleri Kullanıyoruz?</h3>
<ul>
    <li><strong>Zorunlu Çerezler:</strong> Sitemizin temel işlevlerini (sepete ürün ekleme, oturum açık kalma vb.) yerine getirebilmesi için kesinlikle gerekli olan çerezlerdir.</li>
    <li><strong>Performans ve Analiz Çerezleri:</strong> Sitemizin nasıl kullanıldığını analiz ederek, sayfalarımızın hızını ve tasarımını iyileştirmemize yardımcı olan çerezlerdir (örneğin Google Analytics).</li>
    <li><strong>Pazarlama Çerezleri:</strong> Size ilgi alanlarınıza uygun ürünler sunmak ve kampanyaları özelleştirmek için kullanılır.</li>
</ul>

<h3>Çerez Yönetimi</h3>
<p>İnternet tarayıcınızın ayarlar menüsünden çerez tercihlerinizi dilediğiniz zaman değiştirebilirsiniz. Ancak zorunlu çerezleri kapatmanız durumunda sitemizdeki bazı özellikler düzgün çalışmayabilir.</p>
HTML
            ],
            'uyelik-sozlesmesi' => [
                'title' => 'Kullanım Şartları ve Üyelik Sözleşmesi',
                'seo_meta' => [
                    'title' => 'Kullanım Şartları ve Üyelik Sözleşmesi | Erler AVM',
                    'description' => 'Erler AVM internet sitesi kullanıcı ve üyelik şartnameleri sözleşmesi.',
                ],
                'content' => <<<'HTML'
<h2>ÜYELİK SÖZLEŞMESİ</h2>

<h3>1. Taraflar</h3>
<p>İşbu sözleşme, www.erleravm.test alan adlı internet sitesini (kısaca "Site") işleten Erler AVM ile Site'ye üye olan kullanıcı ("Üye") arasında akdedilmiştir.</p>

<h3>2. Sözleşmenin Konusu</h3>
<p>Bu sözleşmenin konusu, Erler AVM'nin sahip olduğu Site'den Üye'nin faydalanma şartlarının ve tarafların hak ve yükümlülüklerinin belirlenmesidir.</p>

<h3>3. Üyenin Hak ve Yükümlülükleri</h3>
<p>Üye, Site'ye üye olurken verdiği kişisel ve diğer sair bilgilerin kanunlar önünde doğru olduğunu, Erler AVM'nin bu bilgilerin gerçeğe aykırılığı nedeniyle uğrayacağı tüm zararları aynen ve derhal tazmin edeceğini beyan ve taahhüt eder.</p>
<p>Üye, Erler AVM tarafından kendisine verilmiş olan şifreyi başka kişi veya kuruluşlara veremez; şifrenin kullanım hakkı bizzat Üye'ye aittir.</p>

<h3>4. Sözleşmenin Feshi</h3>
<p>İşbu sözleşme Üye'nin üyeliğini iptal etmesi veya Erler AVM tarafından üyeliğinin iptal edilmesine kadar yürürlükte kalacaktır. Erler AVM, Üye'nin sözleşme hükümlerini ihlal etmesi durumunda üyeliği tek taraflı olarak iptal etme hakkına sahiptir.</p>
HTML
            ],
        ];

        foreach ($pages as $slug => $data) {
            CorporatePage::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'seo_meta' => $data['seo_meta'],
                    'status' => 'published',
                    'is_active' => true,
                ]
            );
        }
    }
}
