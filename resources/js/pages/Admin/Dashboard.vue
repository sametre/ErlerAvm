<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Boxes,
    ClipboardList,
    CreditCard,
    MapPin,
    Radio,
    TrendingUp,
    Users,
    ShoppingCart,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminGlobe from '@/components/admin/AdminGlobe.vue';
import { toast } from 'vue-sonner';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

type Metric = {
    label: string;
    value: number;
    tone: 'default' | 'success' | 'warning';
};

type OrderRow = {
    public_id: string;
    order_number: string;
    email: string;
    status: string;
    payment_status: string;
    grand_total: string;
    currency: string;
    created_at: string;
};

type ProductRow = {
    public_id: string;
    name: string;
    sku?: string | null;
    stock_quantity: number;
    status: string;
};

type TrafficPoint = {
    type: 'order' | 'visitor';
    label: string;
    description: string;
    lat: number;
    lon: number;
    created_at?: string | null;
};

const props = defineProps<{
    totalRevenue: number;
    revenueSummary: {
        grossRevenue: number;
        todayRevenue: number;
        monthRevenue: number;
        paidOrders: number;
        averageOrderValue: number;
        pendingPaymentRevenue: number;
        last7Days: {
            date: string;
            label: string;
            revenue: number;
            orders: number;
        }[];
    };
    metrics: Metric[];
    operations: {
        publishedProducts: number;
        publishedMenus: number;
        publishedBanners: number;
        recentStockMovements: number;
    };
    recentOrders: OrderRow[];
    lowStockProducts: ProductRow[];
    trafficMap: {
        activeVisitors: number;
        unknownOrderLocations: number;
        points: TrafficPoint[];
        recentVisitorPaths: { path: string; last_seen_at?: string | null }[];
    };
}>();

const adminBase = computed(() => '');
const knownOrderCount = ref(
    props.metrics.find((metric) => metric.label === 'Sipariş')?.value ?? 0,
);
const newOrderAlert = ref<{ orderNumber?: string | null } | null>(null);
let intervalId: number | undefined;
let audioContext: AudioContext | null = null;

const currencyFormatter = new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0,
});

const chartSeries = computed(() => [
    {
        name: 'Ödenmiş ciro',
        data: props.revenueSummary.last7Days.map((day) => day.revenue),
    },
]);

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area' as const,
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        sparkline: { enabled: false },
    },
    colors: ['#ed1c24'], // Erler Red
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth' as const, width: 3 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100],
        },
    },
    xaxis: {
        categories: props.revenueSummary.last7Days.map((day) => day.label),
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#94a3b8' } },
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8' },
            formatter: (value: number) => `₺${(value / 1000).toFixed(1)}k`,
        },
    },
    grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
    },
    theme: { mode: 'light' as const },
}));

const sparklineSeries1 = computed(() => [
    { data: props.revenueSummary.last7Days.map((day) => day.orders) },
]);
const sparklineOptions1 = ref<ApexOptions>({
    chart: { type: 'area' as const, sparkline: { enabled: true } },
    colors: ['#10b981'], // Emerald
    stroke: { curve: 'smooth' as const, width: 2 },
    fill: { opacity: 0.2 },
});

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
    oscillator.type = 'triangle';
    oscillator.frequency.setValueAtTime(1046, audioContext.currentTime);
    oscillator.frequency.setValueAtTime(784, audioContext.currentTime + 0.12);
    oscillator.frequency.setValueAtTime(1318, audioContext.currentTime + 0.24);
    gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
    gain.gain.exponentialRampToValueAtTime(
        0.46,
        audioContext.currentTime + 0.02,
    );
    gain.gain.exponentialRampToValueAtTime(
        0.0001,
        audioContext.currentTime + 0.46,
    );
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.5);
}

async function pollOrders(): Promise<void> {
    try {
        const response = await fetch(
            `${adminBase.value}/siparis-bildirimleri`,
            { headers: { Accept: 'application/json' } },
        );
        if (!response.ok) return;
        const data = (await response.json()) as {
            count: number;
            latest_order_number?: string | null;
        };
        if (data.count > knownOrderCount.value) {
            knownOrderCount.value = data.count;
            playOrderSound();
            newOrderAlert.value = { orderNumber: data.latest_order_number };
            toast.success(
                data.latest_order_number
                    ? `Yeni sipariş geldi: ${data.latest_order_number}`
                    : 'Yeni sipariş geldi.',
            );
            if (
                'Notification' in window &&
                Notification.permission === 'granted'
            ) {
                new Notification('Yeni sipariş geldi', {
                    body:
                        data.latest_order_number ||
                        'Admin panelinde yeni sipariş var.',
                });
            }
            return;
        }
        knownOrderCount.value = data.count;
    } catch {
        return;
    }
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
    }, 15000);
});

onBeforeUnmount(() => {
    if (intervalId) window.clearInterval(intervalId);
});

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Dashboard', href: '/' }],
    }),
});
</script>

<template>
    <Head title="Yönetim Dashboard" />

    <div class="min-h-screen space-y-6 bg-slate-50 p-6">
        <!-- HEADER -->
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <p
                    class="text-xs font-bold tracking-wider text-primary uppercase"
                >
                    Erler AVM Yönetim Merkezi
                </p>
                <h1
                    class="mt-1 text-3xl font-black tracking-tight text-slate-900"
                >
                    Dashboard
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a
                    :href="`${adminBase}/stok`"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-md shadow-slate-900/10 transition hover:bg-slate-800"
                >
                    Stok Yönetimi
                </a>
            </div>
        </div>

        <!-- ORDER ALERT -->
        <aside
            v-if="newOrderAlert"
            class="fixed top-20 right-4 z-50 w-[min(360px,calc(100vw-2rem))] animate-in rounded-2xl border border-primary/20 bg-white p-5 shadow-2xl shadow-primary/20 slide-in-from-right-4"
        >
            <div class="mb-3 flex items-center gap-3">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"
                >
                    <ShoppingCart class="size-5 text-primary" />
                </span>
                <div>
                    <h2 class="text-lg font-black text-slate-900">
                        Yeni Sipariş!
                    </h2>
                    <p class="text-xs font-semibold text-primary">
                        Hemen kontrol et
                    </p>
                </div>
            </div>
            <p class="text-sm font-medium text-slate-600">
                {{
                    newOrderAlert.orderNumber
                        ? `${newOrderAlert.orderNumber} numaralı siparişi kontrol edin.`
                        : 'Yeni sipariş kayıt edildi.'
                }}
            </p>
            <div class="mt-4 flex gap-2">
                <a
                    :href="`/siparisler`"
                    class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-primary text-sm font-bold text-white shadow-md shadow-primary/20 transition hover:bg-primary/90"
                >
                    Siparişleri Aç
                </a>
                <button
                    type="button"
                    class="inline-flex h-10 flex-1 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
                    @click="newOrderAlert = null"
                >
                    Kapat
                </button>
            </div>
        </aside>

        <!-- TOP METRICS -->
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <!-- Revenue -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-emerald-50 transition-transform duration-700 group-hover:scale-150"
                ></div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">
                            Toplam Ciro
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-900">
                            {{
                                currencyFormatter.format(
                                    props.revenueSummary.grossRevenue,
                                )
                            }}
                        </p>
                    </div>
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"
                    >
                        <TrendingUp class="size-5" />
                    </span>
                </div>
                <div class="relative z-10 mt-4 flex items-center gap-2 text-sm">
                    <span class="flex items-center font-bold text-emerald-600"
                        >{{ props.revenueSummary.paidOrders }} ödenmiş
                        sipariş</span
                    >
                    <span class="font-medium text-slate-400"
                        >gerçek ödeme kaydı</span
                    >
                </div>
                <!-- Sparkline -->
                <div class="absolute bottom-0 left-0 w-full">
                    <VueApexCharts
                        type="area"
                        height="60"
                        :options="sparklineOptions1"
                        :series="sparklineSeries1"
                    />
                </div>
            </div>

            <!-- Today -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-primary/5 transition-transform duration-700 group-hover:scale-150"
                ></div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">
                            Bugünkü Ciro
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-900">
                            {{
                                currencyFormatter.format(
                                    props.revenueSummary.todayRevenue,
                                )
                            }}
                        </p>
                    </div>
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <CreditCard class="size-5" />
                    </span>
                </div>
                <div class="relative z-10 mt-4 flex items-center gap-2 text-sm">
                    <span class="flex items-center font-bold text-primary">{{
                        currencyFormatter.format(
                            props.revenueSummary.averageOrderValue,
                        )
                    }}</span>
                    <span class="font-medium text-slate-400"
                        >ortalama sepet</span
                    >
                </div>
            </div>

            <!-- Other Metrics -->
            <div
                v-for="metric in props.metrics.slice(0, 2)"
                :key="metric.label"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">
                            {{ metric.label }}
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-900">
                            {{ metric.value }}
                        </p>
                    </div>
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                    >
                        <Users
                            v-if="
                                metric.label.toLowerCase().includes('müşteri')
                            "
                            class="size-5"
                        />
                        <ClipboardList v-else class="size-5" />
                    </span>
                </div>
            </div>
        </section>

        <!-- MIDDLE SECTION: GLOBE AND CHARTS -->
        <section class="grid gap-6 xl:grid-cols-[1fr_400px]">
            <!-- Chart Area -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            Satış Analizi
                        </h2>
                        <p class="text-sm text-slate-500">
                            Son 7 günlük gelir tablosu
                        </p>
                    </div>
                    <span
                        class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700"
                        >Son 7 Gün</span
                    >
                </div>
                <div class="mt-4">
                    <VueApexCharts
                        type="area"
                        height="320"
                        :options="chartOptions"
                        :series="chartSeries"
                    />
                </div>
            </div>

            <!-- 3D Globe Area -->
            <div
                class="relative flex min-h-[400px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-[#0f172a] p-1 text-white shadow-lg"
            >
                <div class="absolute top-6 left-6 z-10">
                    <div class="mb-1 flex items-center gap-2">
                        <MapPin class="size-4 text-emerald-400" />
                        <p
                            class="text-xs font-bold tracking-widest text-emerald-400 uppercase"
                        >
                            Canlı Trafik
                        </p>
                    </div>
                    <h2 class="text-xl font-black text-white">Küresel Akış</h2>
                    <p
                        v-if="props.trafficMap.unknownOrderLocations"
                        class="mt-2 max-w-52 text-xs text-white/60"
                    >
                        {{ props.trafficMap.unknownOrderLocations }} siparişte
                        şehir eşleşmediği için haritada nokta gösterilmedi.
                    </p>
                </div>
                <div class="relative mt-4 h-full w-full flex-1">
                    <AdminGlobe
                        :active-visitors="props.trafficMap.activeVisitors"
                        :points="props.trafficMap.points"
                    />
                </div>
            </div>
        </section>

        <!-- BOTTOM SECTION: QUICK ACTIONS & TABLES -->
        <section class="grid gap-6 xl:grid-cols-3">
            <!-- Quick Actions -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-1"
            >
                <h3 class="mb-4 font-black text-slate-900">Hızlı İşlemler</h3>
                <div class="grid gap-3">
                    <a
                        :href="`${adminBase}/stok`"
                        class="group flex items-center gap-4 rounded-xl border border-slate-100 p-4 transition hover:border-primary/30 hover:bg-primary/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white"
                        >
                            <Boxes class="size-5" />
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">
                                Ürün & Stok Yönetimi
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                {{
                                    props.operations.publishedProducts
                                }}
                                yayındaki ürün
                            </p>
                        </div>
                    </a>
                    <a
                        :href="`${adminBase}/siparisler`"
                        class="group flex items-center gap-4 rounded-xl border border-slate-100 p-4 transition hover:border-blue-500/30 hover:bg-blue-500/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white"
                        >
                            <ClipboardList class="size-5" />
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">
                                Sipariş Merkezi
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                Aktif sipariş akışı
                            </p>
                        </div>
                    </a>
                    <a
                        :href="`${adminBase}/pazarlama`"
                        class="group flex items-center gap-4 rounded-xl border border-slate-100 p-4 transition hover:border-purple-500/30 hover:bg-purple-500/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-600 transition-colors group-hover:bg-purple-600 group-hover:text-white"
                        >
                            <CreditCard class="size-5" />
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">
                                Pazarlama & Bannerlar
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                {{
                                    props.operations.publishedBanners
                                }}
                                yayındaki banner
                            </p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-sm xl:col-span-2"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-6"
                >
                    <div>
                        <h2 class="font-black text-slate-900">
                            Son Siparişler
                        </h2>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Sisteme düşen en son sipariş listesi
                        </p>
                    </div>
                    <a
                        :href="`${adminBase}/siparisler`"
                        class="text-sm font-bold text-primary hover:underline"
                        >Tümünü Gör</a
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead
                            class="border-b border-slate-100 bg-slate-50 text-xs font-bold text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Sipariş No</th>
                                <th class="px-6 py-4">Müşteri</th>
                                <th class="px-6 py-4">Tutar</th>
                                <th class="px-6 py-4">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="order in props.recentOrders.slice(0, 5)"
                                :key="order.public_id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <a
                                        :href="`${adminBase}/siparisler/${order.public_id}`"
                                        class="transition hover:text-primary"
                                        >{{ order.order_number }}</a
                                    >
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ order.email }}
                                </td>
                                <td class="px-6 py-4 font-black text-slate-900">
                                    {{ order.grand_total }} {{ order.currency }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!props.recentOrders.length">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center font-medium text-slate-500"
                                >
                                    Henüz sipariş yok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</template>
