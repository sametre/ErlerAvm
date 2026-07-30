<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { BellRing, ClipboardList, Search, ShoppingCart, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toast } from 'vue-sonner';

type Metric = {
    label: string;
    value: number;
};

type OrderRow = {
    public_id: string;
    order_number: string;
    email: string;
    status: string;
    status_label: string;
    payment_status: string;
    payment_status_label: string;
    grand_total: string;
    currency: string;
    items_count: number;
    created_at?: string | null;
    url: string;
};

type NewOrderPayload = {
    public_id: string;
    order_number: string;
    email: string;
    status: string;
    status_label: string;
    payment_status: string;
    payment_status_label: string;
    grand_total: string;
    shipping_address?: Record<string, string | number | boolean | null> | null;
    created_at?: string | null;
    url: string;
    items: {
        name: string;
        sku?: string | null;
        quantity: number;
        line_total: string;
    }[];
};

const props = defineProps<{
    filters: {
        q: string | null;
    };
    metrics: Metric[];
    orders: {
        data: OrderRow[];
        links?: { url: string | null; label: string; active: boolean }[];
    };
}>();

const searchQuery = ref(props.filters?.q || '');
const knownLatestOrderId = ref(props.orders.data[0]?.public_id ?? null);
const newOrder = ref<NewOrderPayload | null>(null);
const isNewOrderDrawerOpen = ref(false);
let intervalId: number | undefined;
let audioContext: AudioContext | null = null;

const confirmForm = useForm({
    status: 'processing',
    payment_status: 'paid',
    admin_note: 'Sipariş admin panelinden hazırlamaya alındı.',
});

function applySearch() {
    router.get(
        '/siparisler',
        { q: searchQuery.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function unlockAudio(): void {
    if (!audioContext) {
        audioContext = new AudioContext();
    }

    audioContext.resume().catch(() => undefined);
}

function playOrderSound(): void {
    if (!audioContext || audioContext.state !== 'running') return;

    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
    oscillator.frequency.setValueAtTime(1174, audioContext.currentTime + 0.14);
    gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
    gain.gain.exponentialRampToValueAtTime(
        0.38,
        audioContext.currentTime + 0.02,
    );
    gain.gain.exponentialRampToValueAtTime(
        0.0001,
        audioContext.currentTime + 0.42,
    );
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.45);
}

async function pollOrders(): Promise<void> {
    try {
        const response = await fetch('/siparis-bildirimleri', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const data = (await response.json()) as {
            latest_order_id?: string | null;
            latest_order?: NewOrderPayload | null;
        };

        if (
            data.latest_order_id &&
            data.latest_order_id !== knownLatestOrderId.value &&
            data.latest_order
        ) {
            knownLatestOrderId.value = data.latest_order_id;
            newOrder.value = data.latest_order;
            isNewOrderDrawerOpen.value = true;
            playOrderSound();
            toast.success(
                `Yeni sipariş geldi: ${data.latest_order.order_number}`,
            );
            router.reload({ only: ['orders', 'metrics'] });

            if (
                'Notification' in window &&
                Notification.permission === 'granted'
            ) {
                new Notification('Yeni sipariş geldi', {
                    body: `${data.latest_order.order_number} - ${data.latest_order.grand_total}`,
                });
            }
        } else if (data.latest_order_id) {
            knownLatestOrderId.value = data.latest_order_id;
        }
    } catch {
        return;
    }
}

function approveLatestOrder(): void {
    if (!newOrder.value) return;

    confirmForm.status = 'processing';
    confirmForm.payment_status = newOrder.value.payment_status;
    confirmForm.patch(`/siparisler/${newOrder.value.public_id}/durum`, {
        preserveScroll: true,
        onSuccess: () => {
            isNewOrderDrawerOpen.value = false;
            router.reload({ only: ['orders', 'metrics'] });
        },
    });
}

onMounted(() => {
    window.addEventListener(
        'pointerdown',
        () => {
            unlockAudio();
            if (
                'Notification' in window &&
                Notification.permission === 'default'
            ) {
                Notification.requestPermission().catch(() => undefined);
            }
        },
        { once: true },
    );

    intervalId = window.setInterval(() => {
        void pollOrders();
    }, 12000);
});

onBeforeUnmount(() => {
    if (intervalId) {
        window.clearInterval(intervalId);
    }
});
</script>

<template>
    <Head title="Siparişler" />

    <div class="space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-primary">Admin operasyon</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Siparişler
                </h1>
                <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                    Ödeme durumu, sipariş kalemleri ve müşteri bilgileri gerçek
                    sipariş kayıtlarından okunur. Durum değişikliği detay
                    ekranında loglanarak yapılır.
                </p>
            </div>

            <form @submit.prevent="applySearch" class="relative w-full md:w-80">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Sipariş no veya e-posta ara..."
                    class="pl-9"
                />
            </form>
        </section>

        <section
            class="flex flex-wrap items-center gap-3 rounded-xl border border-primary/20 bg-primary/5 p-4 text-sm"
        >
            <BellRing class="size-5 text-primary" />
            <div class="min-w-0 flex-1">
                <p class="font-semibold">Canlı sipariş takibi açık</p>
                <p class="text-muted-foreground">
                    Yeni sipariş geldiğinde ses çalar ve sağ tarafta
                    müşteri/ürün özeti açılır.
                </p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border bg-card">
            <div class="flex items-center gap-2 border-b p-4">
                <ClipboardList class="size-5 text-primary" />
                <h2 class="font-semibold">Sipariş listesi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-220 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Sipariş</th>
                            <th class="px-4 py-3 font-medium">Müşteri</th>
                            <th class="px-4 py-3 font-medium">Durum</th>
                            <th class="px-4 py-3 font-medium">Ödeme</th>
                            <th class="px-4 py-3 text-center font-medium">
                                Kalem
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Tutar
                            </th>
                            <th class="px-4 py-3 font-medium">Tarih</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="order in props.orders.data"
                            :key="order.public_id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-semibold">
                                {{ order.order_number }}
                            </td>
                            <td class="px-4 py-3">{{ order.email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground"
                                >
                                    {{ order.status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        order.payment_status === 'paid'
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'
                                    "
                                >
                                    {{ order.payment_status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ order.items_count }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                {{ order.grand_total }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ order.created_at || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="order.url">Detay</Link>
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!props.orders.data.length">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Henüz sipariş kaydı yok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div
        v-if="isNewOrderDrawerOpen && newOrder"
        class="fixed inset-0 z-50 bg-black/30"
        @click.self="isNewOrderDrawerOpen = false"
    >
        <aside
            class="ml-auto flex h-full w-full max-w-xl flex-col bg-white shadow-2xl dark:bg-[#07110f]"
        >
            <header class="flex items-start justify-between border-b p-5">
                <div class="flex gap-3">
                    <span
                        class="flex size-11 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <ShoppingCart class="size-5" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-primary">
                            Yeni sipariş
                        </p>
                        <h2 class="text-xl font-bold">
                            {{ newOrder.order_number }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ newOrder.created_at || '-' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-full p-2 text-muted-foreground hover:bg-muted hover:text-foreground"
                    @click="isNewOrderDrawerOpen = false"
                    aria-label="Kapat"
                >
                    <X class="size-5" />
                </button>
            </header>

            <div class="flex-1 space-y-5 overflow-y-auto p-5">
                <section class="rounded-lg border p-4">
                    <div class="grid gap-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Müşteri</span>
                            <span class="font-semibold">{{
                                newOrder.email
                            }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Tutar</span>
                            <span class="font-semibold">{{
                                newOrder.grand_total
                            }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground"
                                >Sipariş durumu</span
                            >
                            <span class="font-semibold">{{
                                newOrder.status_label
                            }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Ödeme</span>
                            <span class="font-semibold">{{
                                newOrder.payment_status_label
                            }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border p-4">
                    <h3 class="font-semibold">Teslimat bilgisi</h3>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div
                            v-for="(value, key) in newOrder.shipping_address ||
                            {}"
                            :key="String(key)"
                            class="grid grid-cols-[120px_1fr] gap-3"
                        >
                            <dt class="text-muted-foreground">
                                {{ String(key).replaceAll('_', ' ') }}
                            </dt>
                            <dd class="font-medium">{{ value || '-' }}</dd>
                        </div>
                        <p
                            v-if="!newOrder.shipping_address"
                            class="text-sm text-muted-foreground"
                        >
                            Teslimat adresi kaydı bulunamadı.
                        </p>
                    </dl>
                </section>

                <section class="rounded-lg border">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Ürünler</h3>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="item in newOrder.items"
                            :key="`${item.sku}-${item.name}`"
                            class="p-4 text-sm"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold">{{ item.name }}</p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ item.sku || 'SKU yok' }} ·
                                        {{ item.quantity }} adet
                                    </p>
                                </div>
                                <p class="font-semibold">
                                    {{ item.line_total }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <footer class="grid gap-3 border-t p-5 sm:grid-cols-2">
                <Button as-child variant="outline">
                    <Link :href="newOrder.url">Detay ekranını aç</Link>
                </Button>
                <Button
                    type="button"
                    :disabled="confirmForm.processing"
                    @click="approveLatestOrder"
                >
                    Hazırlamaya al
                </Button>
            </footer>
        </aside>
    </div>
</template>
