<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    Minus,
    Plus,
    ShieldCheck,
    ShoppingCart,
    Star,
    Truck,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import CommerceIssueState from '@/components/CommerceIssueState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';
import { formatPrice } from '@/lib/utils';

const props = defineProps<{
    product: {
        public_id: string;
        url: string;
        name: string;
        sku?: string | null;
        price: string;
        compare_at_price?: string | null;
        stock_quantity: number;
        short_description?: string | null;
        description?: string | null;
        features?: string | null;
        seo_title?: string | null;
        seo_description?: string | null;
        seo_keywords?: string | null;
        images?: string[];
        variants?: {
            id: number;
            public_id: string;
            sku: string | null;
            price: string | null;
            compare_at_price: string | null;
            stock_quantity: number;
            options: Record<string, string>;
        }[];
        brand?: { name: string; slug: string } | null;
        category?: { name: string; slug: string } | null;
        reviews?: {
            id: number;
            rating: number;
            comment: string;
            admin_reply?: string | null;
            status: string;
            user_name: string;
            created_at: string;
        }[];
        average_rating?: number;
        review_count?: number;
    };
    similarProducts?: ProductCard[];
    recommendedProducts?: ProductCard[];
    seoSchema: Record<string, unknown>;
}>();

type ProductCard = {
    public_id: string;
    name: string;
    price: string;
    compare_at_price?: string | null;
    stock_quantity: number;
    images?: string[];
    url: string;
    brand?: { name: string; slug: string } | null;
};

const quantity = ref(1);
const activeImage = ref(props.product.images?.[0] ?? null);
const similarProductsUrl = computed(() =>
    props.product.category?.slug
        ? `/kategori/${props.product.category.slug}`
        : '/arama',
);

const form = useForm({
    quantity: 1,
    variant_public_id: null as string | null,
});

const selectedOptions = ref<Record<string, string>>({});

// Initialize selected options with the first variant if available
if (props.product.variants && props.product.variants.length > 0) {
    selectedOptions.value = { ...props.product.variants[0].options };
}

const currentVariant = computed(() => {
    if (!props.product.variants || props.product.variants.length === 0) return null;
    return props.product.variants.find(v => {
        return Object.entries(selectedOptions.value).every(([key, value]) => v.options[key] === value);
    }) || null;
});

const displayPrice = computed(() => currentVariant.value?.price || props.product.price);
const displayComparePrice = computed(() => currentVariant.value?.compare_at_price || props.product.compare_at_price);
const displayStock = computed(() => currentVariant.value ? currentVariant.value.stock_quantity : props.product.stock_quantity);

const availableOptionNames = computed(() => {
    if (!props.product.variants || props.product.variants.length === 0) return [];
    const names = new Set<string>();
    props.product.variants.forEach(v => Object.keys(v.options).forEach(k => names.add(k)));
    return Array.from(names);
});

function getOptionValues(optionName: string) {
    if (!props.product.variants) return [];
    const values = new Set<string>();
    props.product.variants.forEach(v => {
        if (v.options[optionName]) values.add(v.options[optionName]);
    });
    return Array.from(values);
}

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const isZooming = ref(false);
const zoomStyle = ref({
    transformOrigin: 'center center',
    transform: 'scale(1)',
});

function handleMouseMove(e: MouseEvent) {
    if (!activeImage.value) {
        return;
    }

    const { left, top, width, height } = (
        e.currentTarget as HTMLElement
    ).getBoundingClientRect();
    const x = ((e.clientX - left) / width) * 100;
    const y = ((e.clientY - top) / height) * 100;
    zoomStyle.value = {
        transformOrigin: `${x}% ${y}%`,
        transform: 'scale(2.5)',
    };
    isZooming.value = true;
}

function handleMouseLeave() {
    isZooming.value = false;
    zoomStyle.value = {
        transformOrigin: 'center center',
        transform: 'scale(1)',
    };
}

function submitReview() {
    reviewForm.post(`/urun/${props.product.public_id}/yorum`, {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset(),
    });
}

function increment(): void {
    quantity.value = Math.min(displayStock.value, quantity.value + 1);
}

function decrement(): void {
    quantity.value = Math.max(1, quantity.value - 1);
}

function addToCart(): void {
    if (props.product.variants?.length && !currentVariant.value) {
        alert('Lütfen bir varyant seçin.');
        return;
    }
    form.quantity = quantity.value;
    form.variant_public_id = currentVariant.value?.public_id || null;
    form.post(`/sepet/${props.product.public_id}/ekle`, {
        preserveScroll: true,
    });
}

function buyNow(): void {
    if (props.product.variants?.length && !currentVariant.value) {
        alert('Lütfen bir varyant seçin.');
        return;
    }
    form.quantity = quantity.value;
    form.variant_public_id = currentVariant.value?.public_id || null;
    form.post(`/sepet/${props.product.public_id}/ekle`, {
        preserveScroll: true,
        onSuccess: () => {
            router.get('/sepet');
        },
    });
}
</script>

<template>
    <StorefrontPage
        :title="product.name"
        :description="
            product.short_description || 'Ürün detay, stok ve fiyat bilgisi.'
        "
        :seo-title="product.seo_title"
        :seo-description="product.seo_description"
        :canonical="product.url"
        :image="product.images?.[0]"
        :keywords="product.seo_keywords"
        :json-ld="seoSchema"
        eyebrow="Ürün Detayı"
    >
        <div class="mt-8 grid gap-12 lg:grid-cols-[1.2fr_1fr]">
            <div class="space-y-6">
                <div
                    class="group relative flex aspect-[4/3] cursor-zoom-in items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-[#f8f9fa] shadow-sm sm:aspect-square"
                    @mousemove="handleMouseMove"
                    @mouseleave="handleMouseLeave"
                >
                    <img
                        v-if="activeImage"
                        :src="activeImage"
                        :alt="product.name"
                        loading="eager"
                        class="h-full w-full object-contain mix-blend-multiply transition-transform duration-300 ease-out"
                        :style="zoomStyle"
                    />
                    <BrandLogo v-else class="h-32 opacity-20" />

                    <div
                        class="pointer-events-none absolute inset-0 rounded-2xl ring-1 ring-black/5 ring-inset"
                    ></div>
                </div>

                <div
                    v-if="product.images?.length"
                    class="grid grid-cols-5 gap-3"
                >
                    <button
                        v-for="image in product.images"
                        :key="image"
                        @click="activeImage = image"
                        class="relative aspect-square overflow-hidden rounded-xl border-2 bg-[#f8f9fa] transition-all duration-200"
                        :class="
                            activeImage === image
                                ? 'border-primary ring-2 ring-primary/20'
                                : 'border-transparent hover:border-gray-200'
                        "
                    >
                        <img
                            :src="image"
                            :alt="product.name"
                            loading="lazy"
                            class="h-full w-full object-cover mix-blend-multiply"
                        />
                    </button>
                </div>
            </div>

            <section class="flex flex-col justify-center space-y-8 py-4">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            v-if="product.brand"
                            class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                        >
                            {{ product.brand.name }}
                        </span>
                        <div
                            class="flex items-center gap-1 text-sm font-medium text-amber-500"
                        >
                            <Star class="size-4 fill-amber-500" />
                            <span>{{
                                product.average_rating?.toFixed(1) || '0'
                            }}</span>
                            <span class="font-normal text-muted-foreground"
                                >({{
                                    product.review_count || 0
                                }}
                                Değerlendirme)</span
                            >
                        </div>
                    </div>

                    <h1
                        class="text-3xl leading-tight font-extrabold tracking-tight text-foreground sm:text-4xl"
                    >
                        {{ product.name }}
                    </h1>

                    <div class="flex items-end gap-4">
                        <p class="text-4xl font-black text-primary">
                            {{ formatPrice(displayPrice) }} TL
                        </p>
                        <p
                            v-if="displayComparePrice"
                            class="mb-1 text-xl font-medium text-muted-foreground line-through"
                        >
                            {{ formatPrice(displayComparePrice) }} TL
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 border-y border-border/60 py-6"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"
                        >
                            <Check class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Stok Durumu</p>
                            <p
                                class="text-xs font-medium"
                                :class="
                                    displayStock > 0
                                        ? 'text-emerald-600'
                                        : 'text-destructive'
                                "
                            >
                                {{
                                    displayStock > 0
                                        ? `${displayStock} adet mevcut`
                                        : 'Tükendi'
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600"
                        >
                            <Truck class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Hızlı Kargo</p>
                            <p class="text-xs text-muted-foreground">
                                Aynı gün teslimat
                            </p>
                        </div>
                    </div>
                </div>

                <CommerceIssueState
                    v-if="displayStock <= 0"
                    code="insufficient_stock"
                    compact
                />

                <div v-else class="space-y-5">
                    <div v-if="availableOptionNames.length > 0" class="space-y-4 pt-2">
                        <div v-for="optionName in availableOptionNames" :key="optionName" class="space-y-2">
                            <label class="text-sm font-semibold text-foreground">{{ optionName }}</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="val in getOptionValues(optionName)"
                                    :key="val"
                                    type="button"
                                    @click="selectedOptions[optionName] = val; quantity = 1"
                                    class="min-w-10 rounded-md border px-3 py-1.5 text-sm transition-all"
                                    :class="selectedOptions[optionName] === val ? 'border-primary bg-primary text-primary-foreground font-bold' : 'border-input bg-background hover:border-primary/50'"
                                >
                                    {{ val }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="text-sm font-semibold text-foreground"
                            >Miktar Seçimi</label
                        >
                        <div
                            class="flex max-w-[160px] items-center overflow-hidden rounded-none border border-input bg-background shadow-sm"
                        >
                            <button
                                type="button"
                                class="flex h-12 flex-1 items-center justify-center bg-muted/50 transition-colors hover:bg-muted disabled:opacity-50"
                                :disabled="quantity <= 1 || form.processing"
                                @click="decrement"
                            >
                                <Minus class="size-4" />
                            </button>
                            <span
                                class="flex h-12 w-14 items-center justify-center bg-background text-base font-bold"
                            >
                                {{ quantity }}
                            </span>
                            <button
                                type="button"
                                class="flex h-12 flex-1 items-center justify-center bg-muted/50 transition-colors hover:bg-muted disabled:opacity-50"
                                :disabled="
                                    quantity >= displayStock ||
                                    form.processing
                                "
                                @click="increment"
                            >
                                <Plus class="size-4" />
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-3 pt-2 sm:grid-cols-2">
                        <button
                            type="button"
                            class="group relative inline-flex h-14 items-center justify-center gap-2 overflow-hidden rounded-none bg-primary px-8 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/30 transition-all hover:-translate-y-0.5 hover:shadow-xl disabled:pointer-events-none disabled:opacity-60"
                            :disabled="form.processing"
                            @click="addToCart"
                        >
                            <div
                                class="absolute inset-0 translate-y-full bg-white/20 transition-transform duration-300 ease-in-out group-hover:translate-y-0"
                            ></div>
                            <ShoppingCart class="relative z-10 size-5" />
                            <span
                                class="relative z-10 tracking-wider uppercase"
                                >{{
                                    form.processing
                                        ? 'Ekleniyor...'
                                        : 'Sepete Ekle'
                                }}</span
                            >
                        </button>

                        <button
                            type="button"
                            class="group relative inline-flex h-14 items-center justify-center gap-2 overflow-hidden rounded-none border border-zinc-200 bg-zinc-100 px-8 text-sm font-bold text-zinc-900 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-zinc-200 disabled:pointer-events-none disabled:opacity-60"
                            :disabled="form.processing"
                            @click="buyNow"
                        >
                            <Zap class="size-5 fill-amber-500 text-amber-500" />
                            <span class="tracking-wider uppercase"
                                >Hızlı Satın Al</span
                            >
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        v-if="product.description"
                        class="rounded-xl border border-border/50 bg-muted/30 p-5 text-sm leading-relaxed text-muted-foreground whitespace-pre-line"
                    >
                        {{ product.description }}
                    </div>

                    <div v-if="product.features" class="rounded-xl border border-border/50 bg-card p-5 shadow-sm">
                        <h3 class="font-bold mb-4 text-foreground">Ürün Özellikleri</h3>
                        <div class="prose prose-sm max-w-none text-muted-foreground" v-html="product.features"></div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-4 text-xs font-medium text-muted-foreground"
                >
                    <div class="flex items-center gap-1.5">
                        <ShieldCheck class="size-4" /> %100 Güvenli Alışveriş
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Truck class="size-4" /> Ücretsiz Kargo (500 TL Üzeri)
                    </div>
                </div>
            </section>
        </div>

        <!-- REVIEWS SECTION -->
        <section class="mt-24 border-t border-border/60 pt-10">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-extrabold tracking-tight">
                    Müşteri Değerlendirmeleri
                </h2>
                <p class="mt-2 text-muted-foreground">
                    Bu ürün hakkında ne düşünüyorlar?
                </p>
            </div>

            <div class="grid gap-10 lg:grid-cols-[1fr_400px]">
                <div class="order-2 lg:order-1">
                    <div v-if="product.reviews?.length" class="space-y-6">
                        <article
                            v-for="review in product.reviews"
                            :key="review.id"
                            class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div
                                class="mb-4 flex items-center justify-between gap-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"
                                    >
                                        {{ review.user_name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold">
                                            {{ review.user_name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                review.status === 'pending'
                                                    ? 'Onay bekliyor'
                                                    : review.created_at
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-0.5 text-amber-500"
                                >
                                    <Star
                                        v-for="star in 5"
                                        :key="`review-${review.id}-${star}`"
                                        class="size-4"
                                        :class="
                                            star <= review.rating
                                                ? 'fill-current'
                                                : 'text-gray-300'
                                        "
                                    />
                                </div>
                            </div>
                            <p
                                class="text-sm leading-relaxed text-foreground/80"
                            >
                                {{ review.comment }}
                            </p>
                            <div
                                v-if="review.admin_reply"
                                class="relative mt-4 rounded-xl border border-primary/10 bg-primary/5 p-4 text-sm"
                            >
                                <div
                                    class="absolute -top-2 left-4 bg-background px-2 text-xs font-bold text-primary"
                                >
                                    Mağaza Yanıtı
                                </div>
                                <p
                                    class="mt-1 leading-relaxed text-muted-foreground"
                                >
                                    {{ review.admin_reply }}
                                </p>
                            </div>
                        </article>
                    </div>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/80 bg-muted/20 py-20 text-center"
                    >
                        <div
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                        >
                            <Star class="size-8 text-muted-foreground/50" />
                        </div>
                        <h3 class="text-lg font-bold">
                            Henüz Değerlendirme Yok
                        </h3>
                        <p
                            class="mt-2 max-w-[250px] text-sm text-muted-foreground"
                        >
                            Bu ürünü ilk değerlendiren siz olun ve diğer
                            müşterilere yardımcı olun.
                        </p>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div
                        class="sticky top-8 rounded-2xl border border-border/80 bg-card p-6 shadow-sm lg:p-8"
                    >
                        <h3 class="mb-6 text-xl font-bold">
                            Yorumunuzu Bırakın
                        </h3>
                        <form
                            v-if="$page.props.auth?.user"
                            class="space-y-6"
                            @submit.prevent="submitReview"
                        >
                            <div class="space-y-3">
                                <label class="block text-sm font-semibold"
                                    >Puanınız</label
                                >
                                <div class="flex gap-2">
                                    <button
                                        v-for="star in 5"
                                        :key="`form-rating-${star}`"
                                        type="button"
                                        class="group flex size-12 items-center justify-center rounded-xl border transition-all"
                                        :class="
                                            reviewForm.rating >= star
                                                ? 'border-amber-400 bg-amber-50 text-amber-500 shadow-sm'
                                                : 'border-border bg-background text-muted-foreground hover:bg-muted'
                                        "
                                        @click="reviewForm.rating = star"
                                    >
                                        <Star
                                            class="size-6 transition-transform group-active:scale-90"
                                            :class="
                                                reviewForm.rating >= star
                                                    ? 'fill-current'
                                                    : ''
                                            "
                                        />
                                    </button>
                                </div>
                                <p
                                    v-if="reviewForm.errors.rating"
                                    class="mt-1 text-xs font-medium text-destructive"
                                >
                                    {{ reviewForm.errors.rating }}
                                </p>
                            </div>
                            <div class="space-y-3">
                                <label class="block text-sm font-semibold"
                                    >Deneyiminizi Anlatın</label
                                >
                                <textarea
                                    v-model="reviewForm.comment"
                                    required
                                    minlength="10"
                                    maxlength="1000"
                                    class="min-h-[140px] w-full resize-y rounded-xl border border-input bg-background px-4 py-3 text-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:outline-none"
                                    placeholder="Ürünün kalitesi, kargo süreci, vb. hakkında..."
                                />
                                <p
                                    v-if="reviewForm.errors.comment"
                                    class="text-xs font-medium text-destructive"
                                >
                                    {{ reviewForm.errors.comment }}
                                </p>
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-foreground px-6 text-sm font-bold text-background shadow transition-colors hover:bg-foreground/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                :disabled="reviewForm.processing"
                            >
                                {{
                                    reviewForm.processing
                                        ? 'Gönderiliyor...'
                                        : 'Yorumu Gönder'
                                }}
                            </button>
                        </form>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center rounded-xl border border-primary/20 bg-primary/5 p-8 text-center"
                        >
                            <p class="mb-4 text-sm font-medium">
                                Değerlendirme yapmak için giriş yapmalısınız.
                            </p>
                            <Link
                                href="/login"
                                class="inline-flex h-10 items-center justify-center rounded-full bg-primary px-6 text-sm font-bold text-primary-foreground shadow transition-colors hover:bg-primary/90"
                            >
                                Giriş Yap
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SIMILAR & RECOMMENDED -->
        <section
            v-if="similarProducts?.length"
            class="mt-24 border-t border-border/60 pt-10"
        >
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">
                        {{ $t('product.related') }}
                    </h2>
                    <p class="mt-1 text-muted-foreground">
                        Aynı kategoriden ilginizi çekebilir
                    </p>
                </div>
                <Link
                    :href="similarProductsUrl"
                    class="hidden items-center gap-1 text-sm font-bold text-primary hover:underline sm:flex"
                >
                    Tümünü Gör <ArrowRight class="size-4" />
                </Link>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <Link
                    v-for="item in similarProducts"
                    :key="`similar-${item.public_id}`"
                    :href="item.url"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border/50 bg-card transition-all hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <div
                        class="relative flex aspect-square items-center justify-center bg-[#f8f9fa] p-4"
                    >
                        <img
                            v-if="item.images?.[0]"
                            :src="item.images[0]"
                            :alt="item.name"
                            loading="lazy"
                            class="h-full w-full object-contain mix-blend-multiply transition-transform duration-500 group-hover:scale-110"
                        />
                        <BrandLogo v-else compact class="size-16 opacity-30" />
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p
                            v-if="item.brand"
                            class="mb-1 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            {{ item.brand.name }}
                        </p>
                        <h3
                            class="line-clamp-2 text-sm leading-tight font-bold transition-colors group-hover:text-primary"
                        >
                            {{ item.name }}
                        </h3>
                        <div
                            class="mt-auto flex items-center justify-between pt-4"
                        >
                            <p class="text-lg font-black text-foreground">
                                {{ formatPrice(item.price) }} TL
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Plus class="size-4" />
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <section
            v-if="recommendedProducts?.length"
            class="mt-16 border-t border-border/60 pt-10 pb-16"
        >
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight">
                        Tavsiye Edilenler
                    </h2>
                    <p class="mt-1 text-muted-foreground">
                        Bu ürünü inceleyenler bunlara da baktı
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <Link
                    v-for="item in recommendedProducts"
                    :key="`recommended-${item.public_id}`"
                    :href="item.url"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border/50 bg-card transition-all hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <div
                        class="relative flex aspect-square items-center justify-center bg-[#f8f9fa] p-4"
                    >
                        <img
                            v-if="item.images?.[0]"
                            :src="item.images[0]"
                            :alt="item.name"
                            loading="lazy"
                            class="h-full w-full object-contain mix-blend-multiply transition-transform duration-500 group-hover:scale-110"
                        />
                        <BrandLogo v-else compact class="size-16 opacity-30" />
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p
                            v-if="item.brand"
                            class="mb-1 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            {{ item.brand.name }}
                        </p>
                        <h3
                            class="line-clamp-2 text-sm leading-tight font-bold transition-colors group-hover:text-primary"
                        >
                            {{ item.name }}
                        </h3>
                        <div
                            class="mt-auto flex items-center justify-between pt-4"
                        >
                            <p class="text-lg font-black text-foreground">
                                {{ formatPrice(item.price) }} TL
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </section>
    </StorefrontPage>
</template>
