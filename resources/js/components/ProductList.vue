<script setup lang="ts">
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ShoppingCart, Heart, Check, Zap } from '@lucide/vue';
import { ref, computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import EmptyState from '@/components/EmptyState.vue';

type Product = {
    name: string;
    public_id: string;
    price: string;
    compare_at_price?: string | null;
    stock_quantity: number;
    images?: string[];
    url: string;
    brand?: { name: string; slug: string } | null;
};

defineProps<{
    products?: Product[] | { data: Product[] };
}>();

const productItems = (products?: Product[] | { data: Product[] }) =>
    Array.isArray(products) ? products : (products?.data ?? []);

const form = useForm({
    quantity: 1,
});

const processingProduct = ref<string | null>(null);
const successProduct = ref<string | null>(null);

const page = usePage();
const activeFavorites = computed(
    () => new Set((page.props.favorites as string[]) || []),
);

function toggleFavorite(product: Product): void {
    router.post(
        `/hesabim/favorilerim/${product.public_id}/toggle`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function addToCart(product: Product): void {
    if (processingProduct.value || successProduct.value) return;

    // Set UI to processing
    processingProduct.value = product.public_id;
    form.quantity = 1;

    // 1-second requested delay before API call
    setTimeout(() => {
        form.post(`/sepet/${product.public_id}/ekle`, {
            preserveScroll: true,
            onFinish: () => {
                processingProduct.value = null;
                successProduct.value = product.public_id;
                // Keep the checkmark visible for 2 seconds
                setTimeout(() => {
                    if (successProduct.value === product.public_id) {
                        successProduct.value = null;
                    }
                }, 2000);
            },
        });
    }, 1000);
}

function quickBuy(product: Product): void {
    if (processingProduct.value || successProduct.value) return;

    form.quantity = 1;
    form.post(`/sepet/${product.public_id}/ekle`, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/odeme');
        },
    });
}
</script>

<template>
    <div
        v-if="productItems(products).length"
        class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
    >
        <article
            v-for="product in productItems(products)"
            :key="product.public_id"
            class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-xl"
        >
            <button
                type="button"
                @click.prevent="toggleFavorite(product)"
                class="absolute top-2 right-2 z-10 flex size-8 items-center justify-center rounded-full bg-white/80 shadow-sm backdrop-blur transition-all hover:scale-110 active:scale-95"
                :class="
                    activeFavorites.has(product.public_id)
                        ? 'text-primary'
                        : 'text-slate-400 hover:text-red-500'
                "
                aria-label="Favorilere ekle"
            >
                <Heart
                    class="size-4.5"
                    :class="{
                        'fill-current': activeFavorites.has(product.public_id),
                    }"
                />
            </button>

            <Link
                :href="product.url"
                class="relative block overflow-hidden bg-slate-50"
            >
                <div
                    class="flex aspect-square items-center justify-center transition-transform duration-700 group-hover:scale-105"
                >
                    <img
                        v-if="product.images?.[0]"
                        :src="product.images[0]"
                        :alt="product.name"
                        loading="lazy"
                        class="h-full w-full object-cover mix-blend-multiply"
                    />
                    <BrandLogo
                        v-else
                        compact
                        class="size-10 opacity-30 grayscale"
                    />
                </div>

                <div
                    v-if="product.compare_at_price"
                    class="absolute bottom-2 left-2 rounded-md bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm"
                >
                    İNDİRİM
                </div>
            </Link>

            <div class="flex flex-1 flex-col p-3 sm:p-4">
                <div class="flex-1">
                    <p
                        v-if="product.brand"
                        class="mb-1 text-[9px] font-black tracking-wider text-slate-400 uppercase sm:text-[10px]"
                    >
                        {{ product.brand.name }}
                    </p>
                    <Link
                        :href="product.url"
                        class="line-clamp-2 min-h-[2.5rem] text-xs leading-snug font-semibold text-slate-800 transition-colors group-hover:text-primary sm:text-sm"
                    >
                        {{ product.name }}
                    </Link>
                </div>

                <div class="mt-2 flex flex-col justify-end sm:mt-3">
                    <p
                        v-if="product.compare_at_price"
                        class="text-[10px] font-semibold text-slate-400 line-through sm:text-xs"
                    >
                        {{ product.compare_at_price }} TL
                    </p>
                    <p class="text-[13px] font-black text-primary sm:text-base">
                        {{ product.price }} TL
                    </p>
                </div>

                <div class="mt-3 grid grid-cols-5 gap-2">
                    <button
                        type="button"
                        class="col-span-4 flex h-8 items-center justify-center gap-1.5 rounded-none bg-slate-900 text-[11px] font-bold text-white transition-all hover:bg-primary hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:h-9 sm:text-sm"
                        :disabled="
                            form.processing || product.stock_quantity <= 0
                        "
                        @click="addToCart(product)"
                    >
                        <template v-if="successProduct === product.public_id">
                            <Check class="size-4 text-emerald-400" />
                            <span class="text-emerald-400">Eklendi</span>
                        </template>
                        <template
                            v-else-if="processingProduct === product.public_id"
                        >
                            <span class="flex items-center gap-1">
                                <span
                                    class="size-1 animate-bounce rounded-full bg-white/80"
                                    style="animation-delay: 0ms"
                                ></span>
                                <span
                                    class="size-1 animate-bounce rounded-full bg-white/80"
                                    style="animation-delay: 150ms"
                                ></span>
                                <span
                                    class="size-1 animate-bounce rounded-full bg-white/80"
                                    style="animation-delay: 300ms"
                                ></span>
                            </span>
                        </template>
                        <template v-else>
                            <ShoppingCart class="size-3.5 sm:size-4" />
                            Sepete Ekle
                        </template>
                    </button>

                    <button
                        type="button"
                        title="Hızlı Satın Al"
                        class="col-span-1 flex h-8 items-center justify-center rounded-none border border-slate-300 bg-slate-100 text-slate-700 transition-all hover:bg-slate-200 hover:shadow-md active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:h-9"
                        :disabled="
                            form.processing || product.stock_quantity <= 0
                        "
                        @click="quickBuy(product)"
                    >
                        <Zap class="size-4" />
                    </button>
                </div>
            </div>
        </article>
    </div>
    <EmptyState
        v-else
        title="Henüz yayınlanmış ürün yok"
        description="Bu alan gerçek ürün verisi bağlandığında otomatik dolacak. Fake ürün veya sahte fiyat gösterilmez."
    />
</template>
