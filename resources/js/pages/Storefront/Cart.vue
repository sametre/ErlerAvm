<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ShoppingCart, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

type CartItem = {
    public_id: string;
    name: string;
    price: string;
    quantity: number;
    line_total: string;
    stock_quantity: number;
    image?: string | null;
    url: string;
};

const props = defineProps<{
    cart: {
        items: CartItem[];
        subtotal: string;
        count: number;
    };
}>();

const processing = ref<string | null>(null);

function updateQuantity(item: CartItem, quantity: number): void {
    processing.value = item.public_id;

    router.patch(
        `/sepet/${item.public_id}`,
        { quantity },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = null;
            },
        },
    );
}

function removeItem(item: CartItem): void {
    updateQuantity(item, 0);
}
</script>

<template>
    <StorefrontPage
        title="Sepet"
        description="Sepetinizdeki ürünleri, adetleri ve ödeme özetini kontrol edin."
        eyebrow="Alışveriş"
        noindex
    >
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <section class="space-y-4">
                <EmptyState
                    v-if="!props.cart.items.length"
                    title="Sepetiniz boş"
                    description="Ürün detay sayfasından sepete ürün ekleyerek alışverişe başlayabilirsiniz."
                />
                <div v-else class="grid gap-4">
                    <article
                        v-for="item in props.cart.items"
                        :key="item.public_id"
                        class="grid gap-4 rounded-lg border bg-card p-4 md:grid-cols-[96px_1fr_auto]"
                    >
                        <Link
                            :href="item.url"
                            class="flex aspect-square items-center justify-center overflow-hidden rounded-md bg-muted"
                        >
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            />
                            <ShoppingCart v-else class="size-8 text-primary" />
                        </Link>

                        <div class="min-w-0">
                            <Link
                                :href="item.url"
                                class="text-base font-semibold hover:text-primary"
                            >
                                {{ item.name }}
                            </Link>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Birim fiyat: {{ item.price }} TL
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Stok: {{ item.stock_quantity }}
                            </p>
                            <div class="mt-4 flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex size-9 items-center justify-center rounded-md border font-bold disabled:opacity-50"
                                    :disabled="
                                        processing === item.public_id ||
                                        item.quantity <= 1
                                    "
                                    @click="
                                        updateQuantity(item, item.quantity - 1)
                                    "
                                >
                                    -
                                </button>
                                <span
                                    class="flex h-9 min-w-12 items-center justify-center rounded-md border px-3 text-sm font-semibold"
                                >
                                    {{ item.quantity }}
                                </span>
                                <button
                                    type="button"
                                    class="flex size-9 items-center justify-center rounded-md border font-bold disabled:opacity-50"
                                    :disabled="
                                        processing === item.public_id ||
                                        item.quantity >= item.stock_quantity
                                    "
                                    @click="
                                        updateQuantity(item, item.quantity + 1)
                                    "
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4 md:block md:text-right"
                        >
                            <p class="text-lg font-bold text-primary">
                                {{ item.line_total }} TL
                            </p>
                            <button
                                type="button"
                                class="mt-3 inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-semibold text-muted-foreground hover:text-destructive disabled:opacity-50"
                                :disabled="processing === item.public_id"
                                @click="removeItem(item)"
                            >
                                <Trash2 class="size-4" />
                                Kaldır
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <aside class="h-fit rounded-lg border bg-card p-5">
                <ShoppingCart class="size-6 text-primary" />
                <h2 class="mt-4 font-semibold">Ödeme özeti</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Ürün adedi</span>
                        <span class="font-semibold">{{
                            props.cart.count
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Ara toplam</span>
                        <span class="font-bold"
                            >{{ props.cart.subtotal }} TL</span
                        >
                    </div>
                </div>
                <Link
                    href="/odeme"
                    class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-md bg-primary text-sm font-semibold text-white"
                    :class="
                        !props.cart.items.length
                            ? 'pointer-events-none opacity-50'
                            : ''
                    "
                >
                    Ödemeye devam et
                </Link>
                <Link
                    href="/arama"
                    class="mt-2 inline-flex h-11 w-full items-center justify-center rounded-md border text-sm font-semibold"
                >
                    Alışverişe devam et
                </Link>
            </aside>
        </div>
    </StorefrontPage>
</template>
