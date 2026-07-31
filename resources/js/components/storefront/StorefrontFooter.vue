<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CreditCard,
    Mail,
    MapPin,
    PackageCheck,
    Phone,
    ShieldCheck,
    Truck,
} from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import BrandLogo from '@/components/brand/BrandLogo.vue';

type NavigationItem = {
    title: string;
    url: string;
};

type StoreSettings = {
    store_name?: string | null;
    contact_phone?: string | null;
    contact_email?: string | null;
    address?: string | null;
    facebook_url?: string | null;
    instagram_url?: string | null;
    twitter_url?: string | null;
    youtube_url?: string | null;
};

type SocialLink = {
    label: string;
    url: string;
};

const props = withDefaults(
    defineProps<{
        footerNavigation?: NavigationItem[];
    }>(),
    {
        footerNavigation: () => [],
    },
);

const page = usePage();
const { t } = useI18n();
const newsletterForm = useForm({
    email: '',
    source: 'footer',
});

const storeSettings = computed<StoreSettings>(
    () => ((page.props as any).storeSettings ?? {}) as StoreSettings,
);
const storeName = computed(() => {
    const configuredName = storeSettings.value.store_name?.trim();

    return configuredName && configuredName !== 'Laravel'
        ? configuredName
        : 'ERLER AVM';
});
const phoneHref = computed(() => {
    const phone = storeSettings.value.contact_phone;

    return phone ? `tel:${phone.replace(/[^\d+]/g, '')}` : undefined;
});

const socialLinks = computed(() =>
    [
        { label: 'Facebook', url: storeSettings.value.facebook_url },
        { label: 'Instagram', url: storeSettings.value.instagram_url },
        { label: 'X', url: storeSettings.value.twitter_url },
        { label: 'YouTube', url: storeSettings.value.youtube_url },
    ].filter((item): item is SocialLink => Boolean(item.url)),
);

const shoppingLinks = computed<NavigationItem[]>(() => [
    { title: 'Ürün Ara', url: '/arama' },
    { title: 'Sepetim', url: '/sepet' },
    { title: 'Kargo Hesaplama', url: '/kargo-hesaplama' },
    { title: 'Sıkça Sorulan Sorular', url: '/sikca-sorulan-sorular' },
    { title: 'Günün Fırsatları', url: '/firsatlar' },
    { title: 'Hediye Kartı', url: '/hediye-karti' },
]);

const accountLinks = computed<NavigationItem[]>(() => [
    { title: t('nav.account'), url: '/hesabim' },
    { title: t('nav.orders'), url: '/hesabim/siparislerim' },
    { title: t('nav.addresses'), url: '/hesabim/adreslerim' },
    { title: t('nav.notifications'), url: '/hesabim/bildirimler' },
    { title: t('nav.favorites'), url: '/hesabim/favorilerim' },
    { title: t('footer.return'), url: '/hesabim/iade-degisim' },
]);

const corporateLinks = computed<NavigationItem[]>(() => {
    const seen = new Set<string>();

    return [
        { title: t('footer.about'), url: '/yasal/hakkimizda' },
        { title: 'Vizyon & Misyon', url: '/yasal/vizyon-misyon' },
        { title: 'Mağazalarımız', url: '/magazalarimiz' },
        { title: 'Kariyer ve İş Başvurusu', url: '/kariyer' },
        { title: 'Kurumsal Satış', url: '/kurumsal-satis' },
        { title: t('footer.contact'), url: '/iletisim' },
        { title: 'Banka Hesaplarımız', url: '/banka-hesaplarimiz' },
        { title: 'Kargo & Teslimat', url: '/yasal/teslimat-ve-kargo' },
        { title: 'Güvenli Alışveriş', url: '/yasal/guvenli-alisveris' },
        { title: 'İade & Değişim', url: '/yasal/iade-degisim-politikasi' },
        { title: 'Ödeme Seçenekleri', url: '/yasal/odeme-secenekleri' },
        ...props.footerNavigation,
    ].filter((item) => {
        const key = `${item.title}:${item.url}`;

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);

        return true;
    });
});

const legalLinks: NavigationItem[] = [
    {
        title: 'Mesafeli Satış Sözleşmesi',
        url: '/yasal/mesafeli-satis-sozlesmesi',
    },
    { title: 'Ön Bilgilendirme Formu', url: '/yasal/on-bilgilendirme-formu' },
    { title: 'Gizlilik Politikası', url: '/yasal/gizlilik-politikasi' },
    { title: 'KVKK Aydınlatma Metni', url: '/yasal/kvkk-aydinlatma-metni' },
    { title: 'Çerez Politikası', url: '/yasal/cerez-politikasi' },
    { title: 'Üyelik Sözleşmesi', url: '/yasal/uyelik-sozlesmesi' },
    { title: 'İptal ve İade Şartları', url: '/yasal/iptal-iade-sartlari' },
];

const assuranceItems = [
    { label: 'SSL Güvenli Oturum', icon: ShieldCheck },
    { label: 'Akbank 3D Secure Ödeme', icon: CreditCard },
    { label: 'Stok Kontrollü Satış', icon: PackageCheck },
    { label: 'Hızlı Kargo Teslimatı', icon: Truck },
];

function submitNewsletter(): void {
    newsletterForm.post('/bulten-abonelik', {
        preserveScroll: true,
        onSuccess: () => newsletterForm.reset('email'),
    });
}
</script>

<template>
    <footer class="border-t border-zinc-800 bg-[#09090b] text-zinc-400">
        <!-- Bülten & Güvenceler Alanı (Üst Kısım) -->
        <div class="border-b border-zinc-800/50 bg-[#0c0c0e]">
            <div
                class="mx-auto flex max-w-screen-2xl flex-col items-center justify-between gap-12 px-6 py-12 lg:flex-row lg:px-12"
            >
                <div class="w-full lg:w-1/2">
                    <h3 class="mb-2 text-2xl font-bold text-white">
                        Kampanya ve duyuruları kaçırmayın
                    </h3>
                    <p class="mb-6 text-sm text-zinc-400">
                        Gerçek kampanya, stok ve sipariş duyuruları için e-posta
                        listenize kaydolun. Fırsatlardan ilk siz haberdar olun!
                    </p>
                    <form
                        class="flex w-full max-w-md gap-3"
                        @submit.prevent="submitNewsletter"
                    >
                        <div class="relative flex-1">
                            <input
                                v-model="newsletterForm.email"
                                type="email"
                                required
                                maxlength="255"
                                autocomplete="email"
                                class="h-12 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 text-sm text-white transition outline-none placeholder:text-zinc-500 focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500"
                                placeholder="E-posta adresiniz"
                            />
                            <p
                                v-if="newsletterForm.errors.email"
                                class="absolute -bottom-6 left-0 text-xs font-medium text-red-400"
                            >
                                {{ newsletterForm.errors.email }}
                            </p>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex h-12 shrink-0 items-center justify-center rounded-xl bg-white px-8 text-sm font-bold text-black transition hover:bg-zinc-200 disabled:opacity-60"
                            :disabled="newsletterForm.processing"
                        >
                            {{
                                newsletterForm.processing
                                    ? 'Kaydediliyor'
                                    : 'Abone Ol'
                            }}
                        </button>
                    </form>
                </div>

                <div class="grid w-full grid-cols-2 gap-4 lg:w-1/2">
                    <div
                        v-for="item in assuranceItems"
                        :key="item.label"
                        class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-800/60 bg-zinc-900/40 p-6 text-center transition-colors hover:border-zinc-700 hover:bg-zinc-800/60"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-800 text-white"
                        >
                            <component :is="item.icon" class="size-6" />
                        </div>
                        <span class="text-sm font-semibold text-zinc-300">{{
                            item.label
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ana Footer Linkleri ve İframeler -->
        <div class="mx-auto max-w-screen-2xl px-6 py-16 lg:px-12">
            <div class="grid gap-12 lg:grid-cols-[1fr_2.5fr_1fr_1fr]">
                <!-- İletişim ve Logo -->
                <section class="space-y-8">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-3"
                        aria-label="ErlerAVM ana sayfa"
                    >
                        <BrandLogo class="h-16" />
                    </Link>

                    <p class="text-sm leading-relaxed text-zinc-400">
                        ErlerAVM; teknoloji, beyaz eşya, mobilya ve ev yaşam
                        ürünlerinde gerçek stok, güvenli ödeme ve izlenebilir
                        sipariş altyapısı ile yıllardır güvenle hizmet
                        vermektedir.
                    </p>

                    <div class="grid gap-4 text-sm text-zinc-400">
                        <div
                            v-if="storeSettings.contact_phone"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-white"
                            >
                                <Phone class="size-4" />
                            </div>
                            <a
                                :href="phoneHref"
                                class="font-semibold text-white transition-colors hover:text-zinc-300"
                            >
                                {{ storeSettings.contact_phone }}
                            </a>
                        </div>
                        <div
                            v-if="storeSettings.contact_email"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-white"
                            >
                                <Mail class="size-4" />
                            </div>
                            <a
                                :href="`mailto:${storeSettings.contact_email}`"
                                class="font-medium transition-colors hover:text-white"
                            >
                                {{ storeSettings.contact_email }}
                            </a>
                        </div>
                        <div
                            v-if="storeSettings.address"
                            class="flex items-start gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-white"
                            >
                                <MapPin class="size-4" />
                            </div>
                            <span class="pt-2 leading-relaxed">{{
                                storeSettings.address
                            }}</span>
                        </div>
                    </div>
                </section>

                <!-- Linkler -->
                <section class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <h3
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            Alışveriş
                        </h3>
                        <nav class="grid gap-3 text-sm text-zinc-400">
                            <Link
                                v-for="item in shoppingLinks"
                                :key="item.url"
                                :href="item.url"
                                class="inline-block w-fit transform transition-colors duration-200 hover:translate-x-1 hover:text-white"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>

                    <div>
                        <h3
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            {{ $t('nav.account') }}
                        </h3>
                        <nav class="grid gap-3 text-sm text-zinc-400">
                            <Link
                                v-for="item in accountLinks"
                                :key="item.url"
                                :href="item.url"
                                class="inline-block w-fit transform transition-colors duration-200 hover:translate-x-1 hover:text-white"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>

                    <div>
                        <h3
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            {{ $t('footer.corporate') }}
                        </h3>
                        <nav class="grid gap-3 text-sm text-zinc-400">
                            <Link
                                v-for="item in corporateLinks"
                                :key="`${item.title}-${item.url}`"
                                :href="item.url"
                                class="inline-block w-fit transform transition-colors duration-200 hover:translate-x-1 hover:text-white"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>

                    <div>
                        <h3
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            {{ $t('footer.legal') }}
                        </h3>
                        <nav class="grid gap-3 text-sm text-zinc-400">
                            <Link
                                v-for="item in legalLinks"
                                :key="item.url"
                                :href="item.url"
                                class="inline-block w-fit transform transition-colors duration-200 hover:translate-x-1 hover:text-white"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>
                </section>

                <!-- Harita -->
                <section class="space-y-6">
                    <h3
                        class="text-sm font-bold tracking-widest text-white uppercase"
                    >
                        {{ $t('footer.store_location') }}
                    </h3>
                    <div
                        class="h-[240px] overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-lg"
                    >
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3011.530663740926!2d28.9769!3d41.0082!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDAwJzI5LjUiTiAyOMKwNTgnMzYuOCJF!5e0!3m2!1str!2str!4v1620000000000!5m2!1str!2str"
                            width="100%"
                            height="100%"
                            style="border: 0"
                            allowfullscreen="false"
                            loading="lazy"
                            class="opacity-80 grayscale transition-all duration-500 hover:opacity-100 hover:grayscale-0"
                        ></iframe>
                    </div>
                </section>

                <!-- Instagram -->
                <section class="space-y-6">
                    <h3
                        class="text-sm font-bold tracking-widest text-white uppercase"
                    >
                        {{ $t('footer.instagram') }}
                    </h3>
                    <a
                        href="https://www.instagram.com/p/C_q8-HxoZ_T/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex h-[240px] flex-col items-center justify-center gap-4 overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-fuchsia-700 via-rose-600 to-amber-400 p-8 text-center text-white shadow-lg"
                    >
                        <span class="text-4xl font-black">Instagram</span>
                        <span class="text-sm font-semibold text-white/90">
                            ErlerAVM paylaşımlarını görüntüle
                        </span>
                        <span
                            class="rounded-full bg-white px-5 py-2 text-xs font-black text-rose-600 transition-transform group-hover:scale-105"
                        >
                            Gönderiye Git
                        </span>
                    </a>
                </section>
            </div>

            <!-- Ödeme Yöntemleri ve ETBIS -->
            <div
                class="mt-16 flex flex-col items-center justify-between gap-8 border-t border-zinc-800/50 pt-10 md:flex-row"
            >
                <!-- Etbis -->
                <div class="flex items-center gap-4">
                    <a
                        href="https://www.eticaret.gov.tr/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block rounded-xl bg-white p-2 transition-opacity hover:opacity-80"
                    >
                        <!-- ETBİS Orijinal Karekod Logosu -->
                        <img
                            src="https://cdn.dsmcdn.com/web/production/etbis-qr.png"
                            alt="ETBİS"
                            class="h-16 object-contain"
                        />
                    </a>
                    <div
                        class="max-w-[200px] text-xs leading-relaxed text-zinc-500"
                    >
                        Bu site Ticaret Bakanlığı
                        <strong class="text-zinc-300">ETBİS</strong> sistemine
                        kayıtlıdır ve güvenli e-ticaret altyapısı sunar.
                    </div>
                </div>

                <!-- Ödeme Logoları -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg bg-white shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <img
                            src="/images/banks/visa.png"
                            alt="Visa"
                            class="h-6 w-auto object-contain"
                        />
                    </div>
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg bg-white shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <img
                            src="/images/banks/mastercard.svg"
                            alt="Mastercard"
                            class="h-7 w-auto object-contain"
                        />
                    </div>
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg bg-white shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <img
                            src="/images/banks/troy.svg"
                            alt="Troy"
                            class="h-6 w-auto object-contain"
                        />
                    </div>
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg bg-[#006fcf] shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <span class="text-sm font-black tracking-tight text-white">
                            AMERICAN<br />EXPRESS
                        </span>
                    </div>
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg border border-zinc-800 bg-zinc-900 shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <span
                            class="px-2 text-center text-[10px] font-black text-white"
                            >3D SECURE</span
                        >
                    </div>
                    <div
                        class="flex h-12 w-20 items-center justify-center rounded-lg border border-zinc-800 bg-zinc-900 shadow-sm transition-transform hover:-translate-y-1"
                    >
                        <span
                            class="px-2 text-center text-[10px] font-black text-white"
                            >256-BIT SSL</span
                        >
                    </div>
                </div>
            </div>

            <!-- Alt Bilgi ve Sosyal Medya -->
            <div
                class="mt-10 flex flex-col items-center justify-between gap-6 border-t border-zinc-800/50 pt-8 text-sm text-zinc-500 md:flex-row"
            >
                <p>
                    © {{ new Date().getFullYear() }} {{ storeName }}.
                    {{ $t('footer.copyright') }}
                </p>

                <div class="flex flex-wrap items-center gap-6">
                    <a
                        v-for="item in socialLinks"
                        :key="item.label"
                        :href="item.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold transition-colors hover:text-white"
                    >
                        {{ item.label }}
                    </a>
                    <div class="hidden h-4 w-[1px] bg-zinc-700 md:block"></div>
                    <Link
                        href="/yasal/gizlilik-politikasi"
                        class="transition-colors hover:text-white"
                        >Gizlilik</Link
                    >
                    <Link
                        href="/yasal/satis-kosullari"
                        class="transition-colors hover:text-white"
                        >Satış Koşulları</Link
                    >
                    <Link
                        href="/iletisim"
                        class="transition-colors hover:text-white"
                        >İletişim</Link
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
