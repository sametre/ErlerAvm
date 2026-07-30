<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ShoppingCartIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { formatPrice } from '@/lib/utils';

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

type CartSummary = {
    items: CartItem[];
    subtotal: string;
    count: number;
};

const open = ref(false);
const removing = ref<string | null>(null);
const page = usePage<{ cart?: CartSummary }>();

const cart = computed<CartSummary>(
    () => page.props.cart ?? { items: [], subtotal: '0.00', count: 0 },
);

function removeItem(item: CartItem): void {
    removing.value = item.public_id;

    router.delete(`/sepet/${item.public_id}`, {
        preserveScroll: true,
        onFinish: () => {
            removing.value = null;
        },
    });
}
</script>

<template>
    <Sheet v-model:open="open">
        <button
            type="button"
            class="relative flex items-center justify-center text-slate-700 transition-transform hover:scale-110 hover:text-primary active:scale-95"
            @click="open = true"
            aria-label="Sepetim"
        >
            <ShoppingCartIcon class="size-6" />
            <span class="ml-2 hidden font-medium lg:inline">Sepet</span>
            <span
                v-if="cart.count > 0"
                class="absolute -top-2 -right-2 flex size-[18px] items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white shadow-sm"
            >
                {{ cart.count }}
            </span>
        </button>
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Sepetim</SheetTitle>
            </SheetHeader>

            <div class="space-y-4 px-4">
                <div v-if="!cart.items.length" class="rounded-lg border p-5">
                    <p class="font-semibold">Sepetiniz boş</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Ürün detay sayfasından sepete ekleme yapabilirsiniz.
                    </p>
                </div>

                <div v-else class="grid gap-3">
                    <article
                        v-for="item in cart.items"
                        :key="item.public_id"
                        class="grid grid-cols-[64px_1fr_auto] gap-3 rounded-lg border bg-white p-3"
                    >
                        <Link
                            :href="item.url"
                            class="flex size-16 items-center justify-center overflow-hidden rounded-md bg-muted"
                        >
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            />
                            <ShoppingCartIcon
                                v-else
                                class="size-6 text-primary"
                            />
                        </Link>
                        <div class="min-w-0">
                            <Link
                                :href="item.url"
                                class="line-clamp-2 text-sm font-semibold"
                            >
                                {{ item.name }}
                            </Link>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ item.quantity }} adet x
                                {{ formatPrice(item.price) }} TL
                            </p>
                            <p class="mt-1 text-sm font-bold text-primary">
                                {{ formatPrice(item.line_total) }} TL
                            </p>
                        </div>
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-md border text-muted-foreground hover:text-destructive disabled:opacity-50"
                            :disabled="removing === item.public_id"
                            aria-label="Ürünü sepetten kaldır"
                            @click="removeItem(item)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </article>
                </div>

                <div class="rounded-lg border bg-muted/40 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">Ara toplam</span>
                        <span class="font-bold"
                            >{{ formatPrice(cart.subtotal) }} TL</span
                        >
                    </div>
                </div>

                <div class="grid gap-2">
                    <Link
                        href="/sepet"
                        class="inline-flex h-11 items-center justify-center rounded-md border font-semibold"
                    >
                        Sepet sayfasına git
                    </Link>
                    <Link
                        href="/odeme"
                        class="inline-flex h-11 items-center justify-center rounded-md bg-primary font-semibold text-white"
                    >
                        Ödemeye devam et
                    </Link>
                </div>

                <p class="text-xs text-muted-foreground">
                    Ödeme adımı kullanıcı girişi gerektirir. Misafir kullanıcı
                    ödeme öncesinde giriş ekranına yönlendirilir.
                </p>
            </div>
        </SheetContent>
    </Sheet>
</template>
