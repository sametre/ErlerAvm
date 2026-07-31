<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BadgePercent, Layers3, PackageSearch } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import EmptyState from '@/components/EmptyState.vue';
import ProductList from '@/components/ProductList.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import LogoBanner from '@/components/storefront/LogoBanner.vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';
import SurpriseBox from '@/components/storefront/SurpriseBox.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type NavigationItem = {
    location: 'top' | 'mega' | 'footer';
    title: string;
    url: string;
};

type BannerPlacement = 'home_hero' | 'home_strip' | 'partner_logo';

type Banner = {
    placement: BannerPlacement;
    title: string;
    subtitle?: string | null;
    cta_label?: string | null;
    cta_url?: string | null;
    image_path?: string | null;
};

type Category = {
    name: string;
    slug: string;
    description?: string | null;
    url: string;
    image?: string | null;
    children?: Category[];
};

type Brand = {
    name: string;
    slug: string;
    description?: string | null;
    url: string;
};

type Campaign = {
    name: string;
    slug: string;
    public_id: string;
    description?: string | null;
    url: string;
};

type Product = {
    name: string;
    public_id: string;
    price: string;
    compare_at_price?: string | null;
    stock_quantity: number;
    images?: string[];
    url: string;
    brand?: { name: string; slug: string } | null;
    category?: { name: string; slug: string } | null;
};

const props = defineProps<{
    navigationItems?: Partial<
        Record<'top' | 'mega' | 'footer', NavigationItem[]>
    >;
    banners?: Partial<Record<BannerPlacement, Banner[]>>;
    categories?: Category[];
    brands?: Brand[];
    campaigns?: Campaign[];
    featuredProducts?: Product[];
}>();

const DEFAULT_HERO_BANNERS: Banner[] = [
    {
        placement: 'home_hero',
        title: 'Evinizin Teknolojisini Yenileyin',
        subtitle:
            'Yeni nesil beyaz eşyalarda seçili modeller ve ErlerAVM güvencesi.',
        cta_label: 'Ürünleri Keşfet',
        cta_url: '/kategori/beyaz-esya-ev-elektronigi',
        image_path: '/images/banners/erler-ev-teknolojileri-2026.webp',
    },
    {
        placement: 'home_hero',
        title: 'Evinize Şıklık Katın',
        subtitle: 'Modern salon takımları, dekorasyon ve konfor bir arada.',
        cta_label: 'Mobilyaları İncele',
        cta_url: '/arama?q=Mobilya',
        image_path: '/images/banners/erler-yasam-alani-2026.webp',
    },
    {
        placement: 'home_hero',
        title: 'Akıllı Yaşam, Güçlü Teknoloji',
        subtitle:
            'Eğlence ve akıllı ev teknolojileriyle yaşam alanınıza enerji katın.',
        cta_label: 'Teknolojiyi İncele',
        cta_url: '/kategori/teknoloji-elektronik',
        image_path: '/images/banners/erler-akilli-teknoloji-2026.webp',
    },
    {
        placement: 'home_hero',
        title: 'Hızlı Teslimat, Güvenli Hizmet',
        subtitle:
            'Karacabey ve çevresine pratik teslimat ve güven veren alışveriş deneyimi.',
        cta_label: 'Yol Tarifi Al',
        cta_url: '/iletisim',
        image_path: '/images/banners/erler-hizli-teslimat.png',
    },
    {
        placement: 'home_hero',
        title: 'Sizi de Bekliyoruz',
        subtitle:
            'ErlerAVM mağaza deneyimini online alışveriş kolaylığıyla birleştirin.',
        cta_label: 'İletişime Geç',
        cta_url: '/iletisim',
        image_path: '/images/banners/erler-sizi-bekliyoruz.png',
    },
];

const LEGACY_HERO_IMAGES = new Set([
    '',
    '/erleravm.png',
    '/erleravm1.png',
    '/erleravm2.png',
    '/erleravm3.png',
]);

const categories = computed(() => props.categories ?? []);
const brands = computed(() => props.brands ?? []);
const campaigns = computed(() => props.campaigns ?? []);
const featuredProducts = computed(() => props.featuredProducts ?? []);

const topNavigation = computed(() => props.navigationItems?.top ?? []);
const megaNavigation = computed(() => props.navigationItems?.mega ?? []);
const footerNavigation = computed(() => props.navigationItems?.footer ?? []);
const rawHeroBanners = computed(() => props.banners?.home_hero ?? []);
const heroBanners = computed(() => {
    if (!rawHeroBanners.value.length) {
        return DEFAULT_HERO_BANNERS;
    }

    return rawHeroBanners.value.map((banner, index) => {
        const imagePath = banner.image_path ?? '';

        if (!LEGACY_HERO_IMAGES.has(imagePath)) {
            return banner;
        }

        return {
            ...banner,
            image_path:
                DEFAULT_HERO_BANNERS[index % DEFAULT_HERO_BANNERS.length]
                    .image_path,
        };
    });
});
const activeHeroIndex = ref(0);
const isCampaignModalOpen = ref(false);

const heroBanner = computed(
    () =>
        heroBanners.value[activeHeroIndex.value] ??
        heroBanners.value[0] ??
        null,
);
const stripBanners = computed(() => props.banners?.home_strip ?? []);

function includesAny(value: string, needles: string[]): boolean {
    return needles.some((needle) => value.includes(needle));
}

function categoryImageForName(name: string, image?: string | null): string {
    if (image) {
        return image;
    }

    const normalized = name.toLocaleLowerCase('tr-TR');

    if (
        includesAny(normalized, [
            'teknoloji',
            'elektronik',
            'telefon',
            'bilgisayar',
            'tablet',
            'televizyon',
            'kulaklık',
            'kamera',
            'oyun',
            'saat',
        ])
    ) {
        return '/images/banners/erler-teknoloji-beyaz-esya.png';
    }

    if (
        includesAny(normalized, [
            'beyaz',
            'buzdolabı',
            'çamaşır',
            'bulaşık',
            'fırın',
            'ocak',
            'kurutma',
            'süpürge',
            'klima',
            'kahve',
            'çay',
            'tost',
        ])
    ) {
        return '/images/banners/erler-teknoloji-beyaz-esya.png';
    }

    if (
        includesAny(normalized, [
            'mobilya',
            'yaşam',
            'koltuk',
            'yatak',
            'oda',
            'masa',
            'sandalye',
            'dekorasyon',
            'tekstil',
            'halı',
            'perde',
            'aydınlatma',
        ])
    ) {
        return '/images/banners/erler-mobilya-siklik.png';
    }

    return '/images/banners/erler-premium-alisveris.png';
}

function isVideoMedia(path?: string | null): boolean {
    return /\.(mp4|webm|ogg)(\?.*)?$/i.test(path ?? '');
}

const flattenedCategories = computed<Category[]>(() =>
    categories.value.flatMap((category) => [
        {
            ...category,
            image: categoryImageForName(category.name, category.image),
        },
        ...(category.children ?? []).map((child) => ({
            ...child,
            image: categoryImageForName(child.name, child.image),
            description:
                child.description ||
                `${category.name} kategorisinde seçili ürünler.`,
            children: [],
        })),
    ]),
);
const megaCategories = computed(() => categories.value.slice(0, 15));
const megaMenuItems = computed(() =>
    megaNavigation.value.length
        ? megaNavigation.value
        : megaCategories.value.slice(0, 5).map((category) => ({
              location: 'mega' as const,
              title: category.name,
              url: category.url,
          })),
);
const visualMegaCategories = computed(() =>
    megaCategories.value.map((category) => ({
        title: category.name,
        url: category.url,
        description: category.description,
        image: category.image,
        children: (category.children ?? []).map((child) => ({
            title: child.name,
            url: child.url,
        })),
    })),
);
const showcaseCategories = computed(() =>
    flattenedCategories.value.slice(0, 10),
);
const collectionCategories = computed(() =>
    categories.value.slice(0, 6).map((category) => ({
        ...category,
        image: categoryImageForName(category.name, category.image),
    })),
);
const mobileProducts = computed(() => featuredProducts.value.slice(0, 6));
let heroTimer: number | undefined;

function setHero(index: number): void {
    activeHeroIndex.value = index;
}

onMounted(() => {
    // Campaign modal pop-up on first visit
    if (campaigns.value.length > 0) {
        const hasSeenCampaigns = sessionStorage.getItem('hasSeenCampaigns');

        if (!hasSeenCampaigns) {
            setTimeout(() => {
                isCampaignModalOpen.value = true;
                sessionStorage.setItem('hasSeenCampaigns', 'true');
            }, 1000);
        }
    }

    if (heroBanners.value.length <= 1) {
        return;
    }

    heroTimer = window.setInterval(() => {
        activeHeroIndex.value =
            (activeHeroIndex.value + 1) % heroBanners.value.length;
    }, 6500);
});

onBeforeUnmount(() => {
    if (heroTimer) {
        window.clearInterval(heroTimer);
    }
});
</script>

<template>
    <SeoHead
        title="ErlerAVM"
        description="ErlerAVM kırmızı-beyaz marketplace deneyimiyle güvenli alışveriş, hızlı sipariş takibi ve gerçek e-ticaret altyapısı sunar."
        :json-ld="{
            '@context': 'https://schema.org',
            '@type': 'Organization',
            name: 'ErlerAVM',
            url: $page.props.seo.canonicalUrl,
            logo: $page.props.seo.logoUrl,
        }"
    />

    <SurpriseBox />

    <main class="min-h-svh bg-[#f7f7f8] text-foreground">
        <StorefrontHeader
            :top-navigation="topNavigation"
            :mega-menu-items="megaMenuItems"
            :mega-categories="visualMegaCategories"
            :campaigns="campaigns"
        />

        <section id="urunler" class="mx-auto px-4 pt-4 lg:hidden">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-primary">Ürünler</p>
                    <h1 class="text-xl font-bold tracking-tight">
                        Hızlı alışveriş
                    </h1>
                </div>
                <Link
                    href="/arama"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary"
                >
                    Tümü
                    <ArrowRight class="size-4" />
                </Link>
            </div>
            <ProductList :products="mobileProducts" />
        </section>

        <section
            class="relative isolate hidden h-[calc(100vh-140px)] min-h-[600px] w-full overflow-hidden bg-zinc-950 lg:block"
        >
            <!-- Background Image/Video -->
            <transition name="fade" mode="out-in">
                <div
                    :key="activeHeroIndex"
                    class="absolute inset-0 h-full w-full"
                >
                    <template v-if="heroBanner?.image_path">
                        <video
                            v-if="isVideoMedia(heroBanner.image_path)"
                            :src="heroBanner.image_path"
                            autoplay
                            muted
                            loop
                            playsinline
                            class="h-full w-full object-cover opacity-80"
                        />
                        <img
                            v-else
                            :src="heroBanner.image_path"
                            :alt="heroBanner.title"
                            class="h-full w-full object-cover opacity-80"
                        />
                    </template>
                </div>
            </transition>

            <!-- Gradient Overlay -->
            <div
                class="absolute inset-0 z-10 bg-gradient-to-r from-black/90 via-black/50 to-transparent"
            ></div>
            <div
                class="absolute inset-0 z-10 bg-gradient-to-t from-black/50 via-transparent to-transparent"
            ></div>

            <!-- Content Container -->
            <div
                class="relative z-20 mx-auto flex h-full max-w-7xl items-center px-4 sm:px-6 lg:px-8"
            >
                <div class="max-w-3xl space-y-6 text-left">
                    <transition name="slide-up" mode="out-in">
                        <div :key="activeHeroIndex" class="space-y-6">
                            <div
                                class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-semibold text-white shadow-sm backdrop-blur-md"
                            >
                                <span
                                    class="mr-2 flex h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                                ></span>
                                Kampanya
                            </div>
                            <h1
                                class="text-4xl leading-[1.1] font-black tracking-tight text-white sm:text-6xl lg:text-[5rem]"
                            >
                                {{ heroBanner?.title }}
                            </h1>
                            <p
                                v-if="heroBanner?.subtitle"
                                class="max-w-2xl text-lg leading-8 font-medium text-zinc-300 sm:text-xl"
                            >
                                {{ heroBanner.subtitle }}
                            </p>
                            <div class="flex items-center gap-4 pt-6">
                                <Link
                                    :href="heroBanner?.cta_url || '/arama'"
                                    class="group inline-flex h-14 items-center justify-center rounded-xl bg-primary px-8 text-base font-bold text-white shadow-lg shadow-primary/30 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-primary/40"
                                >
                                    {{
                                        heroBanner?.cta_label || 'Hemen İncele'
                                    }}
                                    <ArrowRight
                                        class="ml-2 size-5 transition-transform group-hover:translate-x-1"
                                    />
                                </Link>
                                <Link
                                    href="/arama"
                                    class="inline-flex h-14 items-center justify-center rounded-xl border-2 border-white/20 bg-white/5 px-8 text-base font-bold text-white backdrop-blur-sm transition-all hover:border-white/30 hover:bg-white/10"
                                >
                                    Tüm Ürünler
                                </Link>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Navigation Dots -->
            <div
                v-if="heroBanners.length > 1"
                class="absolute bottom-10 left-1/2 z-30 flex -translate-x-1/2 gap-3"
            >
                <button
                    v-for="(banner, index) in heroBanners.slice(0, 5)"
                    :key="`hero-dot-${banner.title}`"
                    type="button"
                    class="h-3 rounded-full transition-all duration-500 ease-out"
                    :class="
                        activeHeroIndex === index
                            ? 'w-12 bg-primary shadow-[0_0_10px_rgba(237,28,36,0.5)]'
                            : 'w-3 bg-white/40 hover:bg-white/80'
                    "
                    :aria-label="banner.title"
                    @click="setHero(index)"
                />
            </div>
        </section>

        <!-- Strip Banners (Marquee) - Görünür (Mobile + Desktop) -->
        <div
            v-if="stripBanners.length"
            class="w-full overflow-hidden border-y border-emerald-600/10 bg-emerald-600/5 py-6"
        >
            <div
                class="flex w-max animate-[marquee_30s_linear_infinite] hover:[animation-play-state:paused]"
            >
                <div
                    class="flex gap-4 px-2"
                    v-for="i in 4"
                    :key="`marquee-group-${i}`"
                >
                    <Link
                        v-for="(banner, index) in stripBanners"
                        :key="`${i}-${index}-${banner.title}`"
                        :href="banner.cta_url || '/arama'"
                        class="group flex w-[280px] flex-col rounded-xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:border-emerald-500 hover:shadow-md md:w-[380px] dark:border-white/10 dark:bg-[#07110f]"
                    >
                        <p
                            class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600"
                        >
                            <BadgePercent class="size-4" />
                            {{ banner.cta_label || 'Kampanya' }}
                        </p>
                        <h2
                            class="mt-2 line-clamp-1 text-lg font-bold text-gray-900 dark:text-white"
                        >
                            {{ banner.title }}
                        </h2>
                        <p
                            class="mt-1.5 line-clamp-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ banner.subtitle || 'Detayları inceleyin.' }}
                        </p>
                    </Link>
                </div>
            </div>
        </div>

        <section class="mx-auto max-w-7xl space-y-5 px-4 py-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p
                        class="flex items-center gap-2 text-sm font-medium text-primary"
                    >
                        <PackageSearch class="size-4" />
                        Kategoriler
                    </p>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Yönetilebilir kategori vitrini
                    </h2>
                </div>
                <Link href="/arama" class="text-sm font-semibold text-primary">
                    Tümünü gör
                </Link>
            </div>
            <div
                v-if="showcaseCategories.length"
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
            >
                <Link
                    v-for="category in showcaseCategories"
                    :key="category.slug"
                    :href="category.url"
                    class="group overflow-hidden rounded-lg border bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)] transition duration-200 [transform-style:preserve-3d] hover:[transform:translateY(-4px)_rotateX(3deg)] hover:border-primary hover:shadow-[0_26px_60px_rgba(220,38,38,0.18)]"
                >
                    <div
                        class="flex aspect-[7/4] items-center justify-center overflow-hidden bg-muted"
                    >
                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="category.name"
                            loading="lazy"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        />
                        <BrandLogo
                            v-else
                            compact
                            class="m-auto size-14 opacity-70"
                        />
                    </div>
                    <div class="p-4">
                        <h3 class="line-clamp-2 min-h-10 text-sm font-bold">
                            {{ category.name }}
                        </h3>
                        <p
                            class="mt-1 line-clamp-2 text-xs text-muted-foreground"
                        >
                            {{ category.description || 'Kategori ürünleri' }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-primary"
                        >
                            Ürünleri gör
                            <ArrowRight class="size-3" />
                        </span>
                    </div>
                </Link>
            </div>
            <EmptyState
                v-else
                title="Yayınlanmış kategori yok"
                description="Kategori verisi girildiğinde mega menü ve kategori kartları otomatik dolacak."
            />
        </section>

        <section class="mx-auto max-w-7xl space-y-5 px-4 py-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-primary">Kampanyalar</p>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Öne çıkan kampanyalar
                    </h2>
                </div>
            </div>
            <div v-if="campaigns.length" class="grid gap-4 md:grid-cols-3">
                <Link
                    v-for="campaign in campaigns"
                    :key="campaign.public_id"
                    :href="campaign.url"
                    class="rounded-lg border bg-white p-5 shadow-sm transition hover:border-primary hover:shadow-md"
                >
                    <p class="text-xs font-semibold text-primary">Kampanya</p>
                    <h3 class="mt-2 text-lg font-bold">{{ campaign.name }}</h3>
                    <p class="mt-2 line-clamp-2 text-sm text-muted-foreground">
                        {{
                            campaign.description ||
                            'Kampanya detaylarını inceleyin.'
                        }}
                    </p>
                </Link>
            </div>
            <EmptyState
                v-else
                title="Aktif kampanya yok"
                description="Kampanya modeli hazır. Gerçek kampanya yayına alındığında banner ve kartlar otomatik görünür."
            />
        </section>

        <section class="mx-auto max-w-7xl space-y-5 px-4 py-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-primary">Ürünler</p>
                    <h2 class="text-2xl font-bold tracking-tight">
                        En çok ilgi görenler
                    </h2>
                </div>
                <Link href="/arama" class="text-sm font-semibold text-primary">
                    Daha fazla
                </Link>
            </div>
            <ProductList :products="featuredProducts" />
        </section>

        <section class="mx-auto max-w-7xl space-y-5 px-4 py-6">
            <div>
                <p
                    class="flex items-center gap-2 text-sm font-medium text-primary"
                >
                    <Layers3 class="size-4" />
                    Koleksiyonlar
                </p>
                <h2 class="text-2xl font-bold tracking-tight">
                    Alışveriş koleksiyonları
                </h2>
            </div>
            <div
                v-if="collectionCategories.length"
                class="grid gap-4 md:grid-cols-3"
            >
                <Link
                    v-for="category in collectionCategories"
                    :key="`collection-${category.slug}`"
                    :href="category.url"
                    class="group relative min-h-64 overflow-hidden rounded-lg border bg-white p-6 text-white shadow-sm transition hover:border-primary hover:shadow-md"
                >
                    <img
                        v-if="category.image"
                        :src="category.image"
                        :alt="category.name"
                        loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/78 via-black/35 to-black/10"
                    />
                    <div
                        class="absolute top-4 right-4 flex size-16 items-center justify-center rounded-md bg-white/15 text-2xl font-black text-white backdrop-blur transition group-hover:scale-110"
                    >
                        {{ category.name.charAt(0) }}
                    </div>
                    <div
                        class="relative flex min-h-44 flex-col justify-end pr-16"
                    >
                        <p class="text-sm font-semibold text-white/80">
                            Koleksiyon
                        </p>
                        <h3 class="mt-2 text-2xl font-bold">
                            {{ category.name }}
                        </h3>
                        <p class="mt-2 line-clamp-2 text-sm text-white/75">
                            {{
                                category.description ||
                                'Seçilmiş kategori ürünleri.'
                            }}
                        </p>
                        <span
                            class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-white"
                        >
                            Koleksiyonu aç
                            <ArrowRight class="size-4" />
                        </span>
                    </div>
                </Link>
            </div>
            <EmptyState
                v-else
                title="Koleksiyon verisi bekliyor"
                description="Koleksiyon alanı kategori veya özel koleksiyon modeliyle gerçek veriye bağlanacak."
            />
        </section>

        <section class="mx-auto max-w-7xl space-y-5 px-4 py-5">
            <div>
                <p class="text-sm font-medium text-primary">Markalar</p>
                <h2 class="text-2xl font-bold tracking-tight">
                    Öne çıkan markalar
                </h2>
            </div>
            <div
                v-if="brands.length"
                class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-6"
            >
                <Link
                    v-for="brand in brands"
                    :key="brand.slug"
                    :href="brand.url"
                    class="rounded-lg border bg-white p-4 text-center text-sm font-semibold shadow-sm transition hover:border-primary"
                >
                    {{ brand.name }}
                </Link>
            </div>
            <EmptyState
                v-else
                title="Yayınlanmış marka yok"
                description="Markalar eklendiğinde bu alan otomatik dolacak."
            />
        </section>

        <LogoBanner :logos="banners?.partner_logo || []" />

        <StorefrontFooter :footer-navigation="footerNavigation" />

        <Dialog
            :open="isCampaignModalOpen"
            @update:open="isCampaignModalOpen = $event"
        >
            <DialogContent
                class="w-[95vw] overflow-hidden rounded-2xl border-0 p-0 shadow-2xl sm:max-w-md"
            >
                <DialogHeader class="sr-only">
                    <DialogTitle>Kampanyalar</DialogTitle>
                    <DialogDescription
                        >Güncel kampanyalarımız</DialogDescription
                    >
                </DialogHeader>
                <div
                    class="relative bg-emerald-600 px-6 py-8 text-center text-white"
                >
                    <h2 class="text-3xl font-black tracking-tight">
                        FIRSAT ZAMANI! 🎉
                    </h2>
                    <p class="mt-2 text-sm font-medium text-emerald-100">
                        Size özel seçili kampanyaları kaçırmayın.
                    </p>
                </div>
                <div
                    class="max-h-[50vh] space-y-3 overflow-y-auto bg-white p-6 dark:bg-[#07110f]"
                >
                    <Link
                        v-for="campaign in campaigns"
                        :key="`modal-${campaign.public_id}`"
                        :href="campaign.url"
                        class="block rounded-xl border border-gray-100 bg-gray-50/50 p-4 transition-all hover:-translate-y-0.5 hover:border-emerald-500 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.02]"
                        @click="isCampaignModalOpen = false"
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                {{ campaign.name }}
                            </h3>
                            <ArrowRight class="size-4 text-emerald-600" />
                        </div>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{
                                campaign.description ||
                                'Fırsatı yakalamak için hemen tıklayın.'
                            }}
                        </p>
                    </Link>
                </div>
                <div
                    class="border-t border-gray-100 bg-gray-50 p-4 text-center dark:border-white/10 dark:bg-white/5"
                >
                    <button
                        @click="isCampaignModalOpen = false"
                        class="text-sm font-semibold text-gray-500 transition hover:text-gray-900 dark:hover:text-white"
                    >
                        Teşekkürler, daha sonra
                    </button>
                </div>
            </DialogContent>
        </Dialog>
    </main>
</template>

<style>
@keyframes marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-50%);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}
</style>
