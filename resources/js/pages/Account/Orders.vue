<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Package } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import AccountLayout from '@/components/AccountLayout.vue';
import {
    translateOrderStatus,
    translatePaymentStatus,
    getOrderStatusColor,
    getPaymentStatusColor,
} from '@/lib/translations';

type OrderItem = {
    name: string;
    quantity: number;
    product_url?: string | null;
};

type Order = {
    public_id: string;
    order_number: string;
    status: string;
    payment_status: string;
    grand_total: string;
    currency: string;
    items: OrderItem[];
    created_at: string;
};

defineProps<{
    orders?: { data: Order[] };
}>();
</script>

<template>
    <AccountLayout
        title="Siparişlerim"
        description="Geçmiş ve aktif siparişlerinizi ve satın aldığınız ürünleri görüntüleyin."
    >
        <div v-if="orders?.data.length" class="space-y-6">
            <article
                v-for="order in orders.data"
                :key="order.public_id"
                class="overflow-hidden rounded-xl border bg-card shadow-sm transition-shadow hover:shadow-md"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4 border-b bg-muted/30 p-6"
                >
                    <div class="grid gap-1.5">
                        <h2
                            class="text-xl font-bold tracking-tight text-foreground"
                        >
                            {{ order.order_number }}
                        </h2>
                        <div class="mt-1 flex items-center gap-2">
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-semibold tracking-wider uppercase',
                                    getOrderStatusColor(order.status),
                                ]"
                            >
                                {{ translateOrderStatus(order.status) }}
                            </span>
                            <span class="text-muted-foreground">•</span>
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-semibold tracking-wider uppercase',
                                    getPaymentStatusColor(order.payment_status),
                                ]"
                            >
                                {{
                                    translatePaymentStatus(order.payment_status)
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-muted-foreground">
                            Toplam Tutar
                        </p>
                        <p
                            class="mt-1 text-2xl font-black tracking-tight text-primary"
                        >
                            {{ order.grand_total }} {{ order.currency }}
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <h3
                        class="mb-4 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Siparişteki Ürünler
                    </h3>
                    <ul class="grid gap-3 sm:grid-cols-2">
                        <li
                            v-for="(item, index) in order.items"
                            :key="index"
                            class="group flex items-center gap-4 rounded-lg border border-border/50 bg-background p-4 transition-colors hover:border-border"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Package class="size-6" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-foreground">
                                    {{ item.name }}
                                </p>
                                <p
                                    class="mt-0.5 text-sm font-medium text-muted-foreground"
                                >
                                    {{ item.quantity }} Adet
                                </p>
                            </div>
                            <Link
                                v-if="item.product_url"
                                :href="item.product_url"
                                class="shrink-0 text-sm font-medium text-primary hover:underline"
                            >
                                Ürüne git
                            </Link>
                        </li>
                    </ul>

                    <div class="mt-6 flex justify-end">
                        <Link
                            :href="`/hesabim/siparislerim/${order.public_id}`"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-primary px-8 text-sm font-bold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Sipariş Detaylarını Gör
                        </Link>
                    </div>
                </div>
            </article>
        </div>
        <EmptyState
            v-else
            title="Henüz sipariş yok"
            description="Mağazamızdan yaptığınız alışverişler burada listelenecektir."
        />
    </AccountLayout>
</template>
