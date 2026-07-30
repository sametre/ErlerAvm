<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Cpu,
    Heart,
    Menu,
    MonitorSmartphone,
    Search,
    Sofa,
    UserRound,
    WashingMachine,
} from '@lucide/vue';
import {
    MagnifyingGlassIcon,
    HeartIcon,
    UserIcon,
    Bars3Icon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import Category3DIcon from './Category3DIcon.vue';
import AccountDropdown from '@/components/storefront/AccountDropdown.vue';
import CartDrawer from '@/components/storefront/CartDrawer.vue';
import LocationPicker from '@/components/storefront/LocationPicker.vue';
import LanguageSwitcher from '@/components/storefront/LanguageSwitcher.vue';
import MobileSearchDrawer from '@/components/storefront/MobileSearchDrawer.vue';
import StorefrontPopupCampaign from '@/components/storefront/StorefrontPopupCampaign.vue';
import SearchBar from '@/components/storefront/SearchBar.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { login, register } from '@/routes';

type NavigationItem = {
    title: string;
    url: string;
};

type MegaCategory = NavigationItem & {
    description?: string | null;
    image?: string | null;
    children?: NavigationItem[];
};

type Campaign = {
    name: string;
    url: string;
    public_id?: string;
};

const props = withDefaults(
    defineProps<{
        topNavigation?: NavigationItem[];
        megaMenuItems?: NavigationItem[];
        megaCategories?: MegaCategory[];
        campaigns?: Campaign[];
    }>(),
    {
        topNavigation: () => [],
        megaMenuItems: () => [],
        megaCategories: () => [],
        campaigns: () => [],
    },
);

const page = usePage();

const defaultCategoryMenu: MegaCategory[] = [
    {
        title: 'Teknoloji & Elektronik',
        url: '/arama?q=Teknoloji',
        description:
            'Telefon, bilgisayar, televizyon ve akıllı yaşam ürünleri.',
        image: '/images/categories/teknoloji.png',
    },
    {
        title: 'Beyaz Eşya & Ev Elektroniği',
        url: '/arama?q=Beyaz%20E%C5%9Fya',
        description: 'Büyük beyaz eşya, küçük ev aletleri ve ev elektroniği.',
        image: '/images/categories/beyaz-esya.png',
    },
    {
        title: 'Mobilya & Yaşam',
        url: '/arama?q=Mobilya',
        description: 'Salon, yatak odası, ofis ve yaşam alanı ürünleri.',
        image: '/images/categories/mobilya.png',
    },
    {
        title: 'Ev Tekstili & Dekorasyon',
        url: '/arama?q=Dekorasyon',
        description: 'Dekorasyon, tekstil ve tamamlayıcı ev ürünleri.',
        image: '/images/categories/dekorasyon.png',
    },
    {
        title: 'Kampanyalar',
        url: '/arama?q=Kampanya',
        description:
            'ErlerAVM fırsatları ve öne çıkan alışveriş koleksiyonları.',
        image: '/images/categories/kampanya.png',
    },
];

const publishedGlobalCategories = computed<MegaCategory[]>(() => {
    const pageProps = page.props as any;

    return pageProps.globalCategories?.length ? pageProps.globalCategories : [];
});

const visualMegaGroups = computed<MegaCategory[]>(() => {
    if (props.megaCategories.length) {
        return props.megaCategories;
    }

    if (publishedGlobalCategories.value.length) {
        return publishedGlobalCategories.value;
    }

    return defaultCategoryMenu;
});

const navMenuItems = computed<NavigationItem[]>(() => {
    if (props.megaMenuItems.length) {
        return props.megaMenuItems.slice(0, 5);
    }

    const categories = publishedGlobalCategories.value.length
        ? publishedGlobalCategories.value
        : defaultCategoryMenu;

    return categories.slice(0, 5).map((category) => ({
        title: category.title,
        url: category.url,
    }));
});

function includesAny(value: string, needles: string[]): boolean {
    return needles.some((needle) => value.includes(needle));
}

function groupIcon(name: string) {
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
        return MonitorSmartphone;
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
        return WashingMachine;
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
        return Sofa;
    }

    return Cpu;
}

function groupImage(name: string): string {
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
        return '/images/categories/teknoloji.png';
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
        return '/images/categories/beyaz-esya.png';
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
        ])
    ) {
        return '/images/categories/mobilya.png';
    }

    if (
        includesAny(normalized, [
            'dekorasyon',
            'tekstil',
            'halı',
            'perde',
            'aydınlatma',
        ])
    ) {
        return '/images/categories/dekorasyon.png';
    }

    return '/images/categories/kampanya.png';
}
</script>

<template>
    <StorefrontPopupCampaign v-if="page.props.popupCampaign" />

    <!-- CAMPAIGNS MARQUEE / FREE SHIPPING BANNER -->
    <div
        v-if="campaigns && campaigns.length > 0"
        class="relative z-50 overflow-hidden bg-slate-900 py-2 text-white"
    >
        <div
            class="flex w-max animate-[marquee_20s_linear_infinite] hover:[animation-play-state:paused]"
        >
            <div
                class="flex gap-8 px-4"
                v-for="i in 6"
                :key="`marquee-header-${i}`"
            >
                <Link
                    v-for="campaign in campaigns"
                    :key="`header-${campaign.public_id || campaign.name}`"
                    :href="campaign.url"
                    class="flex items-center gap-2 text-xs font-bold tracking-widest uppercase transition hover:text-emerald-400"
                >
                    <span class="text-emerald-400">⚡</span> {{ campaign.name }}
                </Link>
            </div>
        </div>
    </div>
    <div
        v-else
        class="relative z-50 overflow-hidden bg-slate-900 py-2 text-white"
    >
        <div class="flex w-max animate-[marquee_20s_linear_infinite]">
            <div
                class="flex gap-8 px-4"
                v-for="i in 10"
                :key="`marquee-default-${i}`"
            >
                <span
                    class="flex items-center gap-2 text-xs font-bold tracking-widest uppercase"
                >
                    📦 1500₺ VE ÜZERİ SİPARİŞLERDE ÜCRETSİZ KARGO FIRSATI!
                </span>
            </div>
        </div>
    </div>

    <div
        class="hidden border-b bg-white text-xs text-muted-foreground lg:block"
    >
        <div
            class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2"
        >
            <div class="flex items-center gap-3">
                <LanguageSwitcher />
                <LocationPicker />
            </div>
            <nav class="hidden items-center gap-5 md:flex">
                <Link
                    v-for="item in topNavigation"
                    :key="`${item.title}-${item.url}`"
                    :href="item.url"
                >
                    {{ item.title }}
                </Link>
                <template v-if="!topNavigation?.length">
                    <Link href="/hesabim/siparislerim">Siparişlerim</Link>
                    <Link href="/yasal/iade-degisim-politikasi">
                        İade ve Değişim
                    </Link>
                    <Link href="/iletisim">Yardım</Link>
                </template>
            </nav>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b bg-white shadow-sm">
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:hidden"
        >
            <Link href="/" class="shrink-0" aria-label="ErlerAVM ana sayfa">
                <BrandLogo class="h-8" />
            </Link>

            <div class="flex items-center gap-5 text-slate-700">
                <MobileSearchDrawer>
                    <button
                        type="button"
                        class="flex items-center justify-center transition-transform hover:scale-110 hover:text-primary active:scale-95"
                        aria-label="Arama"
                    >
                        <MagnifyingGlassIcon class="size-6" />
                    </button>
                </MobileSearchDrawer>

                <Link
                    v-if="$page.props.auth?.user"
                    href="/hesabim/favorilerim"
                    class="flex items-center justify-center transition-transform hover:scale-110 hover:text-primary active:scale-95"
                    aria-label="Favorilerim"
                >
                    <HeartIcon class="size-6" />
                </Link>

                <Link
                    :href="$page.props.auth?.user ? '/hesabim' : login()"
                    class="flex items-center justify-center transition-transform hover:scale-110 hover:text-primary active:scale-95"
                    :aria-label="$t('nav.account')"
                >
                    <UserIcon class="size-6" />
                </Link>

                <CartDrawer />

                <Sheet>
                    <SheetTrigger as-child>
                        <button
                            type="button"
                            class="flex items-center justify-center transition-transform hover:scale-110 hover:text-primary active:scale-95"
                            aria-label="Mobil menüyü aç"
                        >
                            <Bars3Icon class="size-6" />
                        </button>
                    </SheetTrigger>
                    <SheetContent
                        side="right"
                        class="w-[86vw] overflow-y-auto pb-8"
                    >
                        <SheetHeader>
                            <SheetTitle>{{ $t('nav.menu') }}</SheetTitle>
                            <SheetDescription>
                                {{ $t('nav.menu_desc') }}
                            </SheetDescription>
                        </SheetHeader>

                        <div class="space-y-5 px-4">
                            <LocationPicker />

                            <SearchBar />

                            <nav class="grid gap-2 text-sm">
                                <Link
                                    v-if="$page.props.auth?.user"
                                    href="/hesabim"
                                    class="rounded-md border p-3 font-semibold"
                                >
                                    {{ $t('nav.account') }}
                                </Link>
                                <template v-else>
                                    <Link
                                        :href="login()"
                                        class="rounded-md bg-primary p-3 text-center font-semibold text-white"
                                    >
                                        {{ $t('nav.login') }}
                                    </Link>
                                    <Link
                                        :href="register()"
                                        class="rounded-md border p-3 text-center font-semibold text-primary"
                                    >
                                        Üye ol
                                    </Link>
                                </template>
                                <Link
                                    href="/hesabim/siparislerim"
                                    class="rounded-md border p-3"
                                >
                                    Siparişlerim
                                </Link>
                                <Link
                                    href="/hesabim/favorilerim"
                                    class="rounded-md border p-3"
                                >
                                    Favorilerim
                                </Link>
                                <Link
                                    href="/hesabim/bildirimler"
                                    class="rounded-md border p-3"
                                >
                                    Bildirimler
                                </Link>
                                <Link
                                    href="/sepet"
                                    class="rounded-md border p-3"
                                >
                                    Sepetim
                                </Link>
                                <Link
                                    href="/iletisim"
                                    class="rounded-md border p-3"
                                >
                                    Yardım ve iletişim
                                </Link>
                            </nav>

                            <div class="border-t pt-4">
                                <p class="mb-2 text-sm font-semibold">
                                    Kategoriler
                                </p>
                                <nav class="grid gap-2 text-sm">
                                    <Link
                                        v-for="category in visualMegaGroups"
                                        :key="`mobile-${category.title}-${category.url}`"
                                        :href="category.url"
                                        class="rounded-md bg-accent p-3"
                                    >
                                        {{ category.title }}
                                    </Link>
                                    <p
                                        v-if="!visualMegaGroups.length"
                                        class="text-sm text-muted-foreground"
                                    >
                                        Yayında kategori yok.
                                    </p>
                                </nav>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4">
            <div
                class="hidden gap-4 py-4 lg:grid lg:grid-cols-[220px_1fr_auto]"
            >
                <Link
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="ErlerAVM ana sayfa"
                >
                    <BrandLogo class="h-12" />
                </Link>

                <SearchBar />

                <nav class="flex items-center justify-end gap-2 text-sm">
                    <AccountDropdown v-if="$page.props.auth?.user" />
                    <template v-else>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200/60 bg-slate-50 px-4 py-2 font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-100 hover:text-primary active:scale-95"
                                >
                                    <UserRound class="size-4" />
                                    Giriş Yap / Üye Ol
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-64 rounded-2xl border-slate-200/60 p-3 shadow-xl"
                            >
                                <div class="mb-3 px-1 py-1 text-center">
                                    <h4
                                        class="text-base font-black text-slate-800"
                                    >
                                        ErlerAVM'ye Hoş Geldiniz!
                                    </h4>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Siparişlerinizi takip edin,
                                        favorilerinizi kaydedin ve size özel
                                        fırsatları yakalayın.
                                    </p>
                                </div>
                                <DropdownMenuItem
                                    as-child
                                    class="rounded-xl p-0 focus:bg-transparent"
                                >
                                    <Link
                                        :href="login()"
                                        class="mb-2 flex w-full cursor-pointer items-center justify-center rounded-xl bg-primary py-3 text-sm font-bold text-white shadow-md transition-all hover:bg-primary/90 hover:shadow-lg"
                                    >
                                        Giriş Yap
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    as-child
                                    class="rounded-xl p-0 focus:bg-transparent"
                                >
                                    <Link
                                        :href="register()"
                                        class="flex w-full cursor-pointer items-center justify-center rounded-xl border-2 border-slate-200 py-2.5 text-sm font-bold text-slate-700 transition-all hover:border-primary/20 hover:bg-slate-50"
                                    >
                                        Kayıt Ol
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <Link
                        v-if="$page.props.auth?.user"
                        href="/hesabim/bildirimler"
                        class="hidden items-center gap-2 rounded-md px-3 py-2 font-medium hover:bg-accent md:inline-flex"
                    >
                        <Bell class="size-4" />
                        Bildirimler
                    </Link>
                    <Link
                        href="/hesabim/favorilerim"
                        class="hidden items-center gap-2 rounded-md px-3 py-2 font-medium hover:bg-accent md:inline-flex"
                    >
                        <Heart class="size-4" />
                        Favoriler
                    </Link>
                    <CartDrawer />
                </nav>
            </div>

            <div
                class="hidden items-center gap-3 border-t py-3 text-sm lg:flex"
            >
                <div class="group relative shrink-0">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-md bg-primary px-3 py-2 font-semibold text-white transition-colors hover:bg-primary/90"
                    >
                        <Menu class="size-4" />
                        Tüm Kategoriler
                    </button>

                    <div
                        class="invisible absolute top-full left-0 z-50 translate-y-2 pt-3 opacity-0 transition-all duration-200 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                    >
                        <div
                            class="w-[min(1200px,calc(100vw-2rem))] overflow-hidden rounded-xl border bg-white p-5 shadow-2xl"
                        >
                            <div class="grid grid-cols-5 gap-4">
                                <div
                                    v-for="category in visualMegaGroups"
                                    :key="`dropdown-${category.title}-${category.url}`"
                                    class="flex flex-col gap-3"
                                >
                                    <Link
                                        :href="category.url"
                                        class="group/item flex flex-col items-center gap-4 rounded-2xl border border-transparent bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1.5 hover:border-border/60 hover:bg-muted/50 hover:shadow-lg"
                                    >
                                        <div
                                            class="relative flex h-24 w-full shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-slate-50 transition-transform duration-300 group-hover/item:scale-105"
                                        >
                                            <img
                                                v-if="category.image"
                                                :src="category.image"
                                                :alt="category.title"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div>
                                            <h4
                                                class="text-[15px] leading-tight font-bold text-foreground transition-colors group-hover/item:text-primary"
                                            >
                                                {{ category.title }}
                                            </h4>
                                        </div>
                                    </Link>

                                    <div
                                        v-if="
                                            category.children &&
                                            category.children.length
                                        "
                                        class="flex flex-col gap-1 px-2"
                                    >
                                        <Link
                                            v-for="child in category.children"
                                            :key="child.url"
                                            :href="child.url"
                                            class="py-1 text-[13px] text-muted-foreground transition-all hover:translate-x-1 hover:text-primary"
                                        >
                                            {{ child.title }}
                                        </Link>
                                    </div>
                                </div>

                                <div
                                    v-if="!visualMegaGroups.length"
                                    class="col-span-5 p-4 text-center text-sm text-muted-foreground"
                                >
                                    Kategori bulunamadı
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Link
                    v-for="item in navMenuItems"
                    :key="`${item.title}-${item.url}`"
                    :href="item.url"
                    class="shrink-0 rounded-md px-3 py-2 font-medium hover:bg-accent hover:text-primary"
                >
                    {{ item.title }}
                </Link>
            </div>
        </div>
    </header>
</template>
