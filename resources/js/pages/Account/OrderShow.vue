<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, Package, ShieldCheck } from '@lucide/vue';
import AccountLayout from '@/components/AccountLayout.vue';
import {
    getOrderStatusColor,
    getPaymentStatusColor,
    translateOrderStatus,
    translatePaymentStatus,
} from '@/lib/translations';

defineProps<{
    order: {
        public_id: string;
        order_number: string;
        status: string;
        payment_status: string;
        subtotal?: string;
        shipping_total?: string;
        discount_total?: string;
        tax_total?: string;
        grand_total: string;
        currency: string;
        items?: {
            name: string;
            sku?: string | null;
            quantity: number;
            unit_price: string;
            line_total: string;
            product_url?: string | null;
        }[];
        shipment?: {
            status: string;
            status_label: string;
            provider_name?: string | null;
            tracking_number?: string | null;
            tracking_url?: string | null;
            customer_notified_at?: string | null;
            shipped_at?: string | null;
            delivered_at?: string | null;
        } | null;
    };
    paymentUrl?: string | null;
}>();
</script>

<template>
    <AccountLayout
        :title="`Sipariş ${order.order_number}`"
        description="Sipariş, ödeme, kargo ve fatura bilgileri."
    >
        <div class="mb-6">
            <Link
                href="/hesabim/siparislerim"
                class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition-colors hover:text-primary"
            >
                <ArrowLeft class="size-4" />
                Siparişlerime Dön
            </Link>
        </div>

        <div class="grid gap-6 md:grid-cols-[1fr_300px]">
            <section class="space-y-6">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold tracking-tight">
                        Sipariş Bilgileri
                    </h2>
                    <dl class="grid gap-4 text-sm">
                        <div class="flex justify-between gap-4 border-b pb-3">
                            <dt class="text-muted-foreground">
                                Sipariş numarası
                            </dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.order_number }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b pb-3">
                            <dt class="text-muted-foreground">
                                Sipariş durumu
                            </dt>
                            <dd>
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase',
                                        getOrderStatusColor(order.status),
                                    ]"
                                >
                                    {{ translateOrderStatus(order.status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 pb-1">
                            <dt class="text-muted-foreground">Ödeme durumu</dt>
                            <dd>
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase',
                                        getPaymentStatusColor(
                                            order.payment_status,
                                        ),
                                    ]"
                                >
                                    {{
                                        translatePaymentStatus(
                                            order.payment_status,
                                        )
                                    }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="overflow-hidden rounded-xl border bg-card shadow-sm"
                >
                    <div class="border-b bg-muted/30 p-5">
                        <h2 class="font-bold tracking-tight">
                            Siparişteki Ürünler
                        </h2>
                    </div>
                    <div class="divide-y divide-border/50">
                        <div
                            v-for="item in order.items"
                            :key="`${item.sku}-${item.name}`"
                            class="flex flex-col gap-4 p-5 transition-colors hover:bg-muted/10 sm:flex-row sm:items-center"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Package class="size-8" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <Link
                                    v-if="item.product_url"
                                    :href="item.product_url"
                                    class="font-semibold text-foreground transition-colors hover:text-primary hover:underline"
                                >
                                    {{ item.name }}
                                </Link>
                                <p v-else class="font-semibold text-foreground">
                                    {{ item.name }}
                                </p>
                                <p
                                    class="mt-1 text-sm font-medium text-muted-foreground"
                                >
                                    {{ item.sku || 'SKU yok' }}
                                    <span class="mx-1">•</span>
                                    {{ item.quantity }} adet x
                                    {{ item.unit_price }} {{ order.currency }}
                                </p>
                            </div>
                            <div class="text-right sm:w-32">
                                <p class="text-lg font-bold text-primary">
                                    {{ item.line_total }} {{ order.currency }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="order.shipment"
                    class="rounded-xl border bg-card p-6 shadow-sm"
                >
                    <h2 class="mb-4 text-lg font-bold tracking-tight">
                        Kargo Takibi
                    </h2>
                    <dl class="grid gap-3 text-sm">
                        <div class="flex justify-between gap-4 border-b pb-3">
                            <dt class="text-muted-foreground">Kargo firması</dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.shipment.provider_name || '-' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b pb-3">
                            <dt class="text-muted-foreground">Durum</dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.shipment.status_label }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">
                                Takip numarası
                            </dt>
                            <dd class="font-semibold">
                                <a
                                    v-if="order.shipment.tracking_url"
                                    :href="order.shipment.tracking_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="text-primary hover:underline"
                                >
                                    {{ order.shipment.tracking_number }}
                                </a>
                                <span v-else>{{
                                    order.shipment.tracking_number || '-'
                                }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <aside class="space-y-6">
                <div
                    v-if="paymentUrl"
                    class="rounded-xl border border-primary/20 bg-primary/5 p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <span
                            class="flex size-10 items-center justify-center rounded-lg bg-primary text-white"
                        >
                            <ShieldCheck class="size-5" />
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-primary">
                                Akbank 3D Secure Ödeme
                            </h2>
                            <p
                                class="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                            >
                                Kart bilgisi saklanmaz
                            </p>
                        </div>
                    </div>
                    <p
                        class="mb-4 text-sm leading-6 font-medium text-muted-foreground"
                    >
                        Ödeme ekranında kart bilgileriniz doğrudan Akbank Sanal
                        POS sistemine gönderilir. ERLER AVM kart numarası veya
                        CVV saklamaz.
                    </p>
                    <Link
                        :href="paymentUrl"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-bold text-white transition hover:bg-primary/90"
                    >
                        <CreditCard class="size-4" />
                        Ödemeyi Tamamla
                    </Link>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold tracking-tight">
                        Sipariş Özeti
                    </h2>
                    <dl class="grid gap-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="font-medium text-muted-foreground">
                                Ara Toplam
                            </dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.subtotal || order.grand_total }}
                                {{ order.currency }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between"
                            v-if="
                                order.shipping_total &&
                                order.shipping_total !== '0.00'
                            "
                        >
                            <dt class="font-medium text-muted-foreground">
                                Kargo
                            </dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.shipping_total }} {{ order.currency }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between text-emerald-600"
                            v-if="
                                order.discount_total &&
                                order.discount_total !== '0.00'
                            "
                        >
                            <dt class="font-medium">İndirim</dt>
                            <dd class="font-semibold">
                                -{{ order.discount_total }} {{ order.currency }}
                            </dd>
                        </div>
                        <div
                            class="mt-4 flex justify-between border-t pt-4 text-base"
                        >
                            <dt class="font-bold text-foreground">
                                Genel Toplam
                            </dt>
                            <dd
                                class="text-xl font-black tracking-tight text-primary"
                            >
                                {{ order.grand_total }} {{ order.currency }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </AccountLayout>
</template>
