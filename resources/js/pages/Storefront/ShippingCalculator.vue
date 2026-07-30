<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Package,
    Scale,
    Ruler,
    MapPin,
    Truck,
    Check,
    AlertCircle,
    Info,
    Calculator,
    Zap,
    ShieldCheck,
} from '@lucide/vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';

const props = defineProps<{
    corporateContent?: string | null;
}>();

// --- Types ---
interface Dimensions {
    width: number;
    length: number;
    height: number;
}

interface CargoResult {
    id: string;
    name: string;
    logo: string;
    colorClass: string;
    bgClass: string;
    borderClass: string;
    price: number;
    estimatedDays: string;
    features: string[];
    isCheapest: boolean;
    isFastest: boolean;
}

// --- Data ---
const provinces = [
    { name: 'Adana', region: 'Mediterranean', lat: 37.0, lon: 35.3 },
    { name: 'Adıyaman', region: 'Southeastern', lat: 37.7, lon: 38.2 },
    { name: 'Afyonkarahisar', region: 'Aegean', lat: 38.7, lon: 30.5 },
    { name: 'Ağrı', region: 'Eastern', lat: 39.7, lon: 43.0 },
    { name: 'Amasya', region: 'Black Sea', lat: 40.6, lon: 35.8 },
    { name: 'Ankara', region: 'Central', lat: 39.9, lon: 32.8 },
    { name: 'Antalya', region: 'Mediterranean', lat: 36.8, lon: 30.7 },
    { name: 'Artvin', region: 'Black Sea', lat: 41.1, lon: 41.8 },
    { name: 'Aydın', region: 'Aegean', lat: 37.8, lon: 27.8 },
    { name: 'Balıkesir', region: 'Marmara', lat: 39.6, lon: 27.8 },
    { name: 'Bilecik', region: 'Marmara', lat: 40.1, lon: 29.9 },
    { name: 'Bingöl', region: 'Eastern', lat: 38.8, lon: 40.4 },
    { name: 'Bitlis', region: 'Eastern', lat: 38.3, lon: 42.1 },
    { name: 'Bolu', region: 'Black Sea', lat: 40.7, lon: 31.6 },
    { name: 'Burdur', region: 'Mediterranean', lat: 37.7, lon: 30.2 },
    { name: 'Bursa', region: 'Marmara', lat: 40.1, lon: 29.0 },
    { name: 'Çanakkale', region: 'Marmara', lat: 40.1, lon: 26.4 },
    { name: 'Çankırı', region: 'Central', lat: 40.6, lon: 33.6 },
    { name: 'Çorum', region: 'Black Sea', lat: 40.5, lon: 34.9 },
    { name: 'Denizli', region: 'Aegean', lat: 37.7, lon: 29.0 },
    { name: 'Diyarbakır', region: 'Southeastern', lat: 37.9, lon: 40.2 },
    { name: 'Edirne', region: 'Marmara', lat: 41.6, lon: 26.5 },
    { name: 'Elazığ', region: 'Eastern', lat: 38.6, lon: 39.2 },
    { name: 'Erzincan', region: 'Eastern', lat: 39.7, lon: 39.4 },
    { name: 'Erzurum', region: 'Eastern', lat: 39.9, lon: 41.2 },
    { name: 'Eskişehir', region: 'Central', lat: 39.7, lon: 30.5 },
    { name: 'Gaziantep', region: 'Southeastern', lat: 37.0, lon: 37.3 },
    { name: 'Giresun', region: 'Black Sea', lat: 40.9, lon: 38.3 },
    { name: 'Gümüşhane', region: 'Black Sea', lat: 40.4, lon: 39.4 },
    { name: 'Hakkari', region: 'Eastern', lat: 37.5, lon: 43.7 },
    { name: 'Hatay', region: 'Mediterranean', lat: 36.2, lon: 36.1 },
    { name: 'Isparta', region: 'Mediterranean', lat: 37.7, lon: 30.5 },
    { name: 'Mersin', region: 'Mediterranean', lat: 36.8, lon: 34.6 },
    { name: 'Istanbul', region: 'Marmara', lat: 41.0, lon: 28.9 },
    { name: 'Izmir', region: 'Aegean', lat: 38.4, lon: 27.1 },
    { name: 'Kars', region: 'Eastern', lat: 40.5, lon: 43.0 },
    { name: 'Kastamonu', region: 'Black Sea', lat: 41.3, lon: 33.7 },
    { name: 'Kayseri', region: 'Central', lat: 38.7, lon: 35.4 },
    { name: 'Kırklareli', region: 'Marmara', lat: 41.7, lon: 27.2 },
    { name: 'Kırşehir', region: 'Central', lat: 39.1, lon: 34.1 },
    { name: 'Kocaeli', region: 'Marmara', lat: 40.8, lon: 29.9 },
    { name: 'Konya', region: 'Central', lat: 37.8, lon: 32.4 },
    { name: 'Kütahya', region: 'Aegean', lat: 39.4, lon: 29.9 },
    { name: 'Malatya', region: 'Eastern', lat: 38.3, lon: 38.3 },
    { name: 'Manisa', region: 'Aegean', lat: 38.6, lon: 27.4 },
    { name: 'Kahramanmaraş', region: 'Mediterranean', lat: 37.5, lon: 36.9 },
    { name: 'Mardin', region: 'Southeastern', lat: 37.3, lon: 40.7 },
    { name: 'Muğla', region: 'Aegean', lat: 37.2, lon: 28.3 },
    { name: 'Muş', region: 'Eastern', lat: 38.7, lon: 41.4 },
    { name: 'Nevşehir', region: 'Central', lat: 38.6, lon: 34.7 },
    { name: 'Niğde', region: 'Central', lat: 37.9, lon: 34.6 },
    { name: 'Ordu', region: 'Black Sea', lat: 40.9, lon: 37.8 },
    { name: 'Rize', region: 'Black Sea', lat: 41.0, lon: 40.5 },
    { name: 'Sakarya', region: 'Marmara', lat: 40.7, lon: 30.3 },
    { name: 'Samsun', region: 'Black Sea', lat: 41.2, lon: 36.3 },
    { name: 'Siirt', region: 'Southeastern', lat: 37.9, lon: 41.9 },
    { name: 'Sinop', region: 'Black Sea', lat: 42.0, lon: 35.1 },
    { name: 'Sivas', region: 'Central', lat: 39.7, lon: 37.0 },
    { name: 'Tekirdağ', region: 'Marmara', lat: 40.9, lon: 27.5 },
    { name: 'Tokat', region: 'Black Sea', lat: 40.3, lon: 36.5 },
    { name: 'Trabzon', region: 'Black Sea', lat: 41.0, lon: 39.7 },
    { name: 'Tunceli', region: 'Eastern', lat: 39.1, lon: 39.5 },
    { name: 'Şanlıurfa', region: 'Southeastern', lat: 37.1, lon: 38.7 },
    { name: 'Uşak', region: 'Aegean', lat: 38.6, lon: 29.4 },
    { name: 'Van', region: 'Eastern', lat: 38.5, lon: 43.3 },
    { name: 'Yozgat', region: 'Central', lat: 39.8, lon: 34.8 },
    { name: 'Zonguldak', region: 'Black Sea', lat: 41.4, lon: 31.7 },
    { name: 'Aksaray', region: 'Central', lat: 38.3, lon: 34.0 },
    { name: 'Bayburt', region: 'Black Sea', lat: 40.2, lon: 40.2 },
    { name: 'Karaman', region: 'Central', lat: 37.1, lon: 33.2 },
    { name: 'Kırıkkale', region: 'Central', lat: 39.8, lon: 33.5 },
    { name: 'Batman', region: 'Southeastern', lat: 37.8, lon: 41.1 },
    { name: 'Şırnak', region: 'Southeastern', lat: 37.5, lon: 42.4 },
    { name: 'Bartın', region: 'Black Sea', lat: 41.6, lon: 32.3 },
    { name: 'Ardahan', region: 'Eastern', lat: 41.1, lon: 42.7 },
    { name: 'Iğdır', region: 'Eastern', lat: 39.9, lon: 44.0 },
    { name: 'Yalova', region: 'Marmara', lat: 40.6, lon: 29.2 },
    { name: 'Karabük', region: 'Black Sea', lat: 41.2, lon: 32.6 },
    { name: 'Kilis', region: 'Southeastern', lat: 36.7, lon: 37.1 },
    { name: 'Osmaniye', region: 'Mediterranean', lat: 37.0, lon: 36.2 },
    { name: 'Düzce', region: 'Black Sea', lat: 40.8, lon: 31.1 },
].sort((a, b) => a.name.localeCompare(b.name));

// --- State ---
const fromProvince = ref(
    provinces.find((p) => p.name === 'Istanbul') || provinces[0],
);
const toProvince = ref(
    provinces.find((p) => p.name === 'Ankara') || provinces[1],
);
const weight = ref<number | null>(1);
const dims = ref<Dimensions>({ width: 10, length: 10, height: 10 });
const isCalculating = ref(false);
const hasCalculated = ref(false);
const results = ref<CargoResult[]>([]);

// --- Logic ---
// Calculate Haversine distance in km
const getDistance = (
    lat1: number,
    lon1: number,
    lat2: number,
    lon2: number,
) => {
    const R = 6371; // Earth's radius in km
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLon = ((lon2 - lon1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// Calculate volumetric weight (Desi in Turkey)
const getDesi = (width: number, length: number, height: number) => {
    return (width * length * height) / 3000;
};

const calculate = () => {
    if (!weight.value || weight.value <= 0) return;

    isCalculating.value = true;
    hasCalculated.value = false;

    setTimeout(() => {
        const distance = getDistance(
            fromProvince.value.lat,
            fromProvince.value.lat,
            toProvince.value.lat,
            toProvince.value.lat,
        );
        // Add a base distance if same province
        const actualDistance = distance < 50 ? 50 : distance;

        const desi = getDesi(
            dims.value.width,
            dims.value.length,
            dims.value.height,
        );
        const billableWeight = Math.max(weight.value || 1, desi);

        // Zone multiplier based on distance
        // Zones: 0-200km (1.0), 200-600km (1.2), 600-1000km (1.4), >1000km (1.6)
        let zoneMult = 1.0;
        if (actualDistance > 1000) zoneMult = 1.6;
        else if (actualDistance > 600) zoneMult = 1.4;
        else if (actualDistance > 200) zoneMult = 1.2;

        const calculatedResults: CargoResult[] = [
            {
                id: 'aras',
                name: 'Aras Kargo',
                logo: 'A',
                colorClass: 'text-red-600 dark:text-red-500',
                bgClass: 'bg-red-50 dark:bg-red-950/30',
                borderClass: 'border-red-200 dark:border-red-900/50',
                price: Math.round((55 + billableWeight * 12) * zoneMult),
                estimatedDays: actualDistance > 600 ? '2-3 Days' : '1 Day',
                features: ['Next Day Delivery', 'SMS Tracking', 'Door-to-door'],
                isCheapest: false,
                isFastest: true,
            },
            {
                id: 'mng',
                name: 'MNG Kargo',
                logo: 'M',
                colorClass: 'text-blue-600 dark:text-blue-500',
                bgClass: 'bg-blue-50 dark:bg-blue-950/30',
                borderClass: 'border-blue-200 dark:border-blue-900/50',
                price: Math.round((48 + billableWeight * 10) * zoneMult),
                estimatedDays: actualDistance > 600 ? '2-3 Days' : '1-2 Days',
                features: ['Standard Delivery', 'Branch Pickup', 'Insured'],
                isCheapest: false,
                isFastest: false,
            },
            {
                id: 'ptt',
                name: 'PTT Kargo',
                logo: 'P',
                colorClass: 'text-yellow-600 dark:text-yellow-500',
                bgClass: 'bg-yellow-50 dark:bg-yellow-950/30',
                borderClass: 'border-yellow-200 dark:border-yellow-900/50',
                price: Math.round((35 + billableWeight * 6) * zoneMult),
                estimatedDays: actualDistance > 600 ? '3-5 Days' : '2-3 Days',
                features: ['Economical', 'All Villages', 'Gov. Backed'],
                isCheapest: true,
                isFastest: false,
            },
        ];

        // Ensure cheapest is correctly marked (just in case formulas overlap bizarrely)
        const minPrice = Math.min(...calculatedResults.map((r) => r.price));
        calculatedResults.forEach((r) => {
            r.isCheapest = r.price === minPrice;
        });

        results.value = calculatedResults.sort((a, b) => a.price - b.price);

        isCalculating.value = false;
        hasCalculated.value = true;
    }, 800); // simulate network request/complex calculation
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
    }).format(price);
};
</script>

<template>
    <Head title="Kargo Hesaplama" />

    <div
        class="flex min-h-screen flex-col bg-zinc-50 font-sans dark:bg-zinc-950"
    >
        <!-- Header -->
        <div
            class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8 lg:py-20"
            >
                <div
                    class="mb-6 inline-flex items-center justify-center rounded-2xl bg-zinc-100 p-3 dark:bg-zinc-800"
                >
                    <Calculator
                        class="h-8 w-8 text-zinc-900 dark:text-zinc-100"
                    />
                </div>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl dark:text-white"
                >
                    Kargo Hesaplama
                </h1>
                <div
                    v-if="corporateContent"
                    class="prose dark:prose-invert mx-auto mt-6 max-w-3xl text-left"
                    v-html="corporateContent"
                ></div>
                <p
                    v-else
                    class="mx-auto mt-4 max-w-2xl text-lg text-zinc-500 dark:text-zinc-400"
                >
                    Aras, MNG ve PTT Kargo fiyatlarını anında karşılaştırın.
                    Kargonuzun desi/ağırlık bilgilerini girerek en uygun ve
                    hızlı kargo seçeneklerini öğrenin.
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div
            class="mx-auto w-full max-w-7xl flex-grow px-4 py-12 sm:px-6 lg:px-8"
        >
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <!-- Calculator Form -->
                <div class="lg:col-span-5">
                    <div
                        class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <h2
                            class="mb-8 flex items-center gap-2 text-xl font-bold text-zinc-900 dark:text-white"
                        >
                            <Package class="h-5 w-5 text-zinc-500" />
                            Paket Detayları
                        </h2>

                        <form @submit.prevent="calculate" class="space-y-8">
                            <!-- Locations -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col items-center">
                                        <MapPin class="h-5 w-5 text-zinc-400" />
                                        <div
                                            class="my-1 h-10 w-0.5 bg-zinc-200 dark:bg-zinc-800"
                                        ></div>
                                        <MapPin
                                            class="h-5 w-5 text-zinc-900 dark:text-zinc-100"
                                        />
                                    </div>
                                    <div class="flex-grow space-y-4">
                                        <div>
                                            <label
                                                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                                >Gönderici İl</label
                                            >
                                            <select
                                                v-model="fromProvince"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                            >
                                                <option
                                                    v-for="p in provinces"
                                                    :key="p.name"
                                                    :value="p"
                                                >
                                                    {{ p.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                                >Alıcı İl</label
                                            >
                                            <select
                                                v-model="toProvince"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                            >
                                                <option
                                                    v-for="p in provinces"
                                                    :key="p.name"
                                                    :value="p"
                                                >
                                                    {{ p.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="h-px w-full bg-zinc-200 dark:bg-zinc-800"
                            ></div>

                            <!-- Weight & Dimensions -->
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="mb-2 flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                    >
                                        <Scale class="h-4 w-4 text-zinc-500" />
                                        Ağırlık (kg)
                                    </label>
                                    <input
                                        type="number"
                                        v-model="weight"
                                        min="0.1"
                                        step="0.1"
                                        required
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                    >
                                        <Ruler class="h-4 w-4 text-zinc-500" />
                                        Boyutlar (cm)
                                    </label>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <span
                                                class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400"
                                                >En</span
                                            >
                                            <input
                                                type="number"
                                                v-model="dims.width"
                                                min="1"
                                                required
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                            />
                                        </div>
                                        <div>
                                            <span
                                                class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400"
                                                >Boy</span
                                            >
                                            <input
                                                type="number"
                                                v-model="dims.length"
                                                min="1"
                                                required
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                            />
                                        </div>
                                        <div>
                                            <span
                                                class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400"
                                                >Yükseklik</span
                                            >
                                            <input
                                                type="number"
                                                v-model="dims.height"
                                                min="1"
                                                required
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-zinc-900 transition-colors focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                            />
                                        </div>
                                    </div>
                                    <p
                                        class="mt-2 flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        <Info class="h-3.5 w-3.5" /> Desi
                                        otomatik olarak hesaplanacaktır.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="isCalculating"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-3.5 font-semibold text-white shadow-md transition-all hover:bg-zinc-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                            >
                                <span
                                    v-if="isCalculating"
                                    class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white dark:border-zinc-900/30 dark:border-t-zinc-900"
                                ></span>
                                <span v-else>Fiyatları Hesapla</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Results -->
                <div class="lg:col-span-7">
                    <div
                        v-if="!hasCalculated && !isCalculating"
                        class="flex h-full flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-200 bg-white/50 p-12 text-center dark:border-zinc-800 dark:bg-zinc-900/50"
                    >
                        <div
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                        >
                            <Truck class="h-8 w-8 text-zinc-400" />
                        </div>
                        <h3
                            class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            Hesaplamaya Hazır
                        </h3>
                        <p class="max-w-sm text-zinc-500 dark:text-zinc-400">
                            Paket detaylarını ve illeri seçerek karşılaştırmalı
                            fiyatları anında görebilirsiniz.
                        </p>
                    </div>

                    <div
                        v-if="isCalculating"
                        class="flex h-full flex-col items-center justify-center p-12 text-center"
                    >
                        <div class="relative mb-6 h-20 w-20">
                            <div
                                class="absolute inset-0 rounded-full border-4 border-zinc-200 dark:border-zinc-800"
                            ></div>
                            <div
                                class="absolute inset-0 animate-spin rounded-full border-4 border-zinc-900 border-t-transparent dark:border-zinc-100"
                            ></div>
                            <Truck
                                class="absolute inset-0 m-auto h-6 w-6 animate-pulse text-zinc-400"
                            />
                        </div>
                        <h3
                            class="mb-2 animate-pulse text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            En uygun fiyatlar bulunuyor...
                        </h3>
                        <p class="text-zinc-500 dark:text-zinc-400">
                            Kargo firmalarından güncel veriler alınıyor
                        </p>
                    </div>

                    <div
                        v-if="hasCalculated && !isCalculating"
                        class="space-y-6"
                    >
                        <h2
                            class="mb-6 text-xl font-bold text-zinc-900 dark:text-white"
                        >
                            Mevcut Seçenekler
                        </h2>

                        <div class="space-y-4">
                            <div
                                v-for="(result, index) in results"
                                :key="result.id"
                                class="relative rounded-2xl border-2 bg-white p-6 transition-all duration-300 hover:shadow-lg dark:bg-zinc-900"
                                :class="[
                                    result.isCheapest
                                        ? 'border-zinc-900 shadow-md ring-4 ring-zinc-900/5 dark:border-zinc-100 dark:ring-zinc-100/5'
                                        : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700',
                                    {
                                        'translate-y-0 transform opacity-100':
                                            hasCalculated,
                                    },
                                ]"
                                :style="{ transitionDelay: `${index * 100}ms` }"
                            >
                                <div
                                    v-if="result.isCheapest"
                                    class="absolute -top-3.5 left-6 flex items-center gap-1 rounded-full bg-zinc-900 px-3 py-1 text-xs font-bold text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900"
                                >
                                    <Zap class="h-3.5 w-3.5" /> En Uygun
                                </div>

                                <div
                                    class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center"
                                >
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-2xl border text-2xl font-bold"
                                            :class="[
                                                result.bgClass,
                                                result.colorClass,
                                                result.borderClass,
                                            ]"
                                        >
                                            {{ result.logo }}
                                        </div>
                                        <div>
                                            <h3
                                                class="flex items-center gap-2 text-lg font-bold text-zinc-900 dark:text-white"
                                            >
                                                {{ result.name }}
                                                <span
                                                    v-if="result.isFastest"
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                                                >
                                                    <Zap class="h-3 w-3" /> En
                                                    Hızlı
                                                </span>
                                            </h3>
                                            <p
                                                class="mt-1 flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400"
                                            >
                                                <Truck class="h-4 w-4" />
                                                Teslimat Süresi:
                                                <span
                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                    >{{
                                                        result.estimatedDays ===
                                                        '1 Day'
                                                            ? '1 Gün'
                                                            : result.estimatedDays.replace(
                                                                  'Days',
                                                                  'Gün',
                                                              )
                                                    }}</span
                                                >
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between border-t border-zinc-100 pt-4 sm:flex-col sm:items-end sm:justify-center sm:border-t-0 sm:pt-0 dark:border-zinc-800"
                                    >
                                        <div
                                            class="text-sm text-zinc-500 sm:mb-1 dark:text-zinc-400"
                                        >
                                            Tahmini Tutar
                                        </div>
                                        <div
                                            class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white"
                                        >
                                            {{ formatPrice(result.price) }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="mt-6 border-t border-zinc-100 pt-5 dark:border-zinc-800/50"
                                >
                                    <ul class="flex flex-wrap gap-4">
                                        <li
                                            v-for="feat in result.features"
                                            :key="feat"
                                            class="flex items-center text-sm text-zinc-600 dark:text-zinc-400"
                                        >
                                            <Check
                                                class="mr-2 h-4 w-4 flex-shrink-0 text-emerald-500"
                                            />
                                            {{
                                                feat === 'Next Day Delivery'
                                                    ? 'Ertesi Gün Teslimat'
                                                    : feat ===
                                                        'Standard Delivery'
                                                      ? 'Standart Teslimat'
                                                      : feat === 'Economical'
                                                        ? 'Ekonomik'
                                                        : feat ===
                                                            'SMS Tracking'
                                                          ? 'SMS ile Takip'
                                                          : feat ===
                                                              'Branch Pickup'
                                                            ? 'Şubeden Teslim'
                                                            : feat ===
                                                                'All Villages'
                                                              ? 'Tüm Köylere Teslimat'
                                                              : feat ===
                                                                  'Door-to-door'
                                                                ? 'Kapıya Teslim'
                                                                : feat ===
                                                                    'Insured'
                                                                  ? 'Sigortalı'
                                                                  : 'Devlet Güvencesi'
                                            }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4">
                            <div
                                class="flex items-start gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-5 dark:border-emerald-900/30 dark:bg-emerald-950/20"
                            >
                                <Zap
                                    class="mt-0.5 h-6 w-6 flex-shrink-0 text-emerald-600 dark:text-emerald-400"
                                />
                                <div>
                                    <h4
                                        class="font-bold text-emerald-900 dark:text-emerald-300"
                                    >
                                        Harika Haber! Ücretsiz Kargo
                                    </h4>
                                    <p
                                        class="mt-1 text-sm leading-relaxed text-emerald-700 dark:text-emerald-400/80"
                                    >
                                        Eğer sipariş tutarınız
                                        <strong>1500₺ ve üzerindeyse</strong>,
                                        tüm bu kargo ücretlerini biz
                                        karşılıyoruz. Kargonuz tamamen ücretsiz
                                        olarak adresinize teslim edilir!
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-5 dark:border-blue-900/30 dark:bg-blue-950/20"
                            >
                                <ShieldCheck
                                    class="mt-0.5 h-6 w-6 flex-shrink-0 text-blue-600 dark:text-blue-400"
                                />
                                <div>
                                    <h4
                                        class="font-semibold text-blue-900 dark:text-blue-300"
                                    >
                                        Tahmini Fiyatlandırma
                                    </h4>
                                    <p
                                        class="mt-1 text-sm leading-relaxed text-blue-700 dark:text-blue-400/80"
                                    >
                                        Yukarıda gösterilen fiyatlar, kargo
                                        firmalarının standart desi hesaplama
                                        yöntemlerine göre oluşturulmuş tahmini
                                        tutarlardır. Şubedeki net ölçümlere ve
                                        güncel yakıt ek ücretlerine göre küçük
                                        değişiklikler gösterebilir.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <StorefrontFooter class="mt-auto" />
    </div>
</template>
