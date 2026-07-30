<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowsRightLeftIcon,
    MagnifyingGlassIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    CheckIcon,
    FunnelIcon,
    SparklesIcon,
    BuildingStorefrontIcon,
    ArchiveBoxIcon,
    ChartBarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CubeIcon,
    ExclamationTriangleIcon,
    PhotoIcon,
    XMarkIcon,
    LinkIcon,
} from '@heroicons/vue/24/outline';

type ProductRow = {
    public_id: string;
    name: string;
    sku?: string | null;
    price: string;
    compare_at_price?: string | null;
    stock_quantity: number;
    status: 'draft' | 'published';
    brand_id?: number | null;
    category_id?: number | null;
    brand?: string | null;
    category?: string | null;
    short_description?: string | null;
    description?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    seo_keywords?: string | null;
    image_urls?: string | null;
    order_items_count: number;
};

type Movement = {
    public_id: string;
    product?: string | null;
    user?: string | null;
    quantity_delta: number;
    stock_before: number;
    stock_after: number;
    reason: string;
    note?: string | null;
    created_at: string;
};

type Option = {
    id: number;
    name: string;
};

const props = defineProps<{
    products: {
        data: ProductRow[];
        links?: { url: string | null; label: string; active: boolean }[];
    };
    recentMovements: Movement[];
    categories: Option[];
    brands: Option[];
    filters: {
        q: string;
        status: 'all' | 'draft' | 'published';
        stock: 'all' | 'low' | 'out';
    };
}>();

const selectedProduct = ref<ProductRow | null>(null);
const selectedProductIds = ref<string[]>([]);

// Drawer states
const isBulkDrawerOpen = ref(false);
const isStockDrawerOpen = ref(false);
const isHistoryDrawerOpen = ref(false);
const filters = useForm({
    q: props.filters.q,
    status: props.filters.status,
    stock: props.filters.stock,
});

const bulkForm = useForm({
    product_ids: [] as string[],
    action: 'publish',
    quantity_delta: 1,
    note: '',
});

const stockForm = useForm({
    quantity_delta: 1,
    reason: 'manual_correction',
    note: '',
});

const allVisibleSelected = computed(
    () =>
        props.products.data.length > 0 &&
        props.products.data.every((product) =>
            selectedProductIds.value.includes(product.public_id),
        ),
);

const selectedProducts = computed(() =>
    props.products.data.filter((product) =>
        selectedProductIds.value.includes(product.public_id),
    ),
);

const visibleProductCount = computed(() => props.products.data.length);
const selectedVisibleCount = computed(() => selectedProducts.value.length);
const publishedVisibleCount = computed(
    () =>
        props.products.data.filter((product) => product.status === 'published')
            .length,
);
const lowStockVisibleCount = computed(
    () =>
        props.products.data.filter(
            (product) =>
                product.stock_quantity > 0 && product.stock_quantity <= 5,
        ).length,
);
const outOfStockVisibleCount = computed(
    () =>
        props.products.data.filter((product) => product.stock_quantity === 0)
            .length,
);
const hasActiveFilters = computed(
    () =>
        filters.q.trim() !== '' ||
        filters.status !== 'all' ||
        filters.stock !== 'all',
);

function resetFilters(): void {
    filters.q = '';
    filters.status = 'all';
    filters.stock = 'all';
    applyFilters();
}

function clearSelection(): void {
    selectedProductIds.value = [];
}

function productImage(product: ProductRow): string | null {
    return (
        product.image_urls
            ?.split(/[\n,]/)
            .map((image) => image.trim())
            .find(Boolean) ?? null
    );
}

function productInitials(name: string): string {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toLocaleUpperCase('tr-TR');
}

function stockTone(product: ProductRow): 'healthy' | 'low' | 'out' {
    if (product.stock_quantity === 0) {
        return 'out';
    }

    if (product.stock_quantity <= 5) {
        return 'low';
    }

    return 'healthy';
}

function stockLabel(product: ProductRow): string {
    const tone = stockTone(product);

    if (tone === 'out') {
        return 'Stok yok';
    }

    if (tone === 'low') {
        return 'Düşük stok';
    }

    return 'Stok güvenli';
}

function stockBadgeClass(product: ProductRow): string {
    const tone = stockTone(product);

    if (tone === 'out') {
        return 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300';
    }

    if (tone === 'low') {
        return 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300';
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300';
}

function stockDotClass(product: ProductRow): string {
    const tone = stockTone(product);

    if (tone === 'out') {
        return 'bg-red-500';
    }

    if (tone === 'low') {
        return 'bg-amber-500';
    }

    return 'bg-emerald-500';
}

function statusBadgeClass(product: ProductRow): string {
    return product.status === 'published'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
        : 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300';
}

function paginationLabel(label: string): string {
    return label
        .replace('&laquo;', '‹')
        .replace('&raquo;', '›')
        .replace('Previous', 'Önceki')
        .replace('Next', 'Sonraki');
}

function visitPagination(url: string | null): void {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
}

function applyFilters(): void {
    router.get(
        '/stok',
        {
            q: filters.q,
            status: filters.status,
            stock: filters.stock,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function toggleAllVisible(): void {
    const visibleIds = props.products.data.map((product) => product.public_id);

    if (allVisibleSelected.value) {
        selectedProductIds.value = selectedProductIds.value.filter(
            (id) => !visibleIds.includes(id),
        );
        return;
    }

    selectedProductIds.value = Array.from(
        new Set([...selectedProductIds.value, ...visibleIds]),
    );
}

function openBulkDrawer(): void {
    if (!selectedProductIds.value.length) return;
    bulkForm.product_ids = selectedProductIds.value;
    isBulkDrawerOpen.value = true;
}

function submitBulkAction(): void {
    bulkForm.post('/stok/toplu-islem', {
        preserveScroll: true,
        onSuccess: () => {
            selectedProductIds.value = [];
            bulkForm.reset('quantity_delta', 'note');
            isBulkDrawerOpen.value = false;
        },
    });
}

function deleteProduct(product: ProductRow): void {
    const message =
        product.order_items_count > 0
            ? 'Bu ürün sipariş geçmişinde kullanılmış. Sistem silmek yerine yayından kaldıracak. Devam edilsin mi?'
            : 'Bu ürünü silmek istediğinize emin misiniz?';

    if (!window.confirm(message)) {
        return;
    }

    useForm({}).delete(`/stok/urun/${product.public_id}`, {
        preserveScroll: true,
    });
}

function openStockDrawer(product: ProductRow): void {
    selectedProduct.value = product;
    stockForm.reset();
    isStockDrawerOpen.value = true;
}

function submitAdjustment(): void {
    if (!selectedProduct.value) return;

    stockForm.post(`/stok/${selectedProduct.value.public_id}/hareket`, {
        preserveScroll: true,
        onSuccess: () => {
            stockForm.reset('quantity_delta', 'note');
            isStockDrawerOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Stok ve Ürün Yönetimi" />

    <div class="space-y-6 p-6">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-emerald-600">Admin</p>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Stok ve Ürün Yönetimi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Kataloğunuzu yönetin, stok hareketlerini izleyin ve ürün
                    detaylarını düzenleyin.
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="isAiDrawerOpen = true">
                    <SparklesIcon class="mr-2 h-5 w-5" />
                    AI Atölyesi
                </Button>
                <Button variant="outline" @click="isBrandDrawerOpen = true">
                    <BuildingStorefrontIcon class="mr-2 h-5 w-5" />
                    Hızlı Marka
                </Button>
                <Button variant="outline" @click="isHistoryDrawerOpen = true">
                    Son Hareketler
                </Button>
                <Button
                    class="bg-emerald-600 text-white hover:bg-emerald-700"
                    as-child
                >
                    <Link href="/stok/urun/yeni">
                        <PlusIcon class="mr-2 h-5 w-5" />
                        Yeni Ürün Ekle
                    </Link>
                </Button>
            </div>
        </div>

        <div
            class="rounded-xl border bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#07110f]"
        >
            <form
                class="flex flex-col gap-3 lg:flex-row"
                @submit.prevent="applyFilters"
            >
                <div class="relative flex-1">
                    <MagnifyingGlassIcon
                        class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400"
                    />
                    <Input
                        v-model="filters.q"
                        type="search"
                        maxlength="80"
                        placeholder="Ürün adı veya SKU ara"
                        class="pl-10"
                    />
                </div>
                <div class="w-full lg:w-48">
                    <Select v-model="filters.status">
                        <SelectTrigger
                            ><SelectValue placeholder="Durum"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Tüm durumlar</SelectItem>
                            <SelectItem value="published">Yayında</SelectItem>
                            <SelectItem value="draft">Taslak</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-full lg:w-48">
                    <Select v-model="filters.stock">
                        <SelectTrigger
                            ><SelectValue placeholder="Stok"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Tüm stok</SelectItem>
                            <SelectItem value="low">Düşük stok</SelectItem>
                            <SelectItem value="out">Stok yok</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <Button type="submit" variant="secondary">
                    <FunnelIcon class="mr-2 h-4 w-4" />
                    Filtrele
                </Button>
            </form>
        </div>

        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#07110f]"
        >
            <div
                class="border-b border-slate-200 bg-slate-50/80 p-4 sm:p-5 dark:border-white/10 dark:bg-white/[0.03]"
            >
                <div
                    class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div class="flex min-w-0 gap-3">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-900/15"
                        >
                            <ChartBarIcon class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p
                                class="text-xs font-bold tracking-wide text-emerald-700 uppercase dark:text-emerald-300"
                            >
                                Akıllı Tablo
                            </p>
                            <h2
                                class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white"
                            >
                                Canlı stok ve ürün kontrolü
                            </h2>
                            <p
                                class="mt-1 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Seçim, hızlı stok hareketi, yayın durumu ve
                                sipariş geçmişi sinyalleri aynı ekranda.
                                İşlemler gerçek admin API akışlarına bağlıdır.
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-2 text-sm sm:grid-cols-2 xl:min-w-[520px] xl:grid-cols-4"
                    >
                        <div
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/[0.03]"
                        >
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Görünen
                            </p>
                            <p
                                class="mt-1 text-xl font-semibold text-slate-950 dark:text-white"
                            >
                                {{ visibleProductCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                        >
                            <p class="text-xs opacity-80">Yayında</p>
                            <p class="mt-1 text-xl font-semibold">
                                {{ publishedVisibleCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
                        >
                            <p class="text-xs opacity-80">Düşük stok</p>
                            <p class="mt-1 text-xl font-semibold">
                                {{ lowStockVisibleCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300"
                        >
                            <p class="text-xs opacity-80">Stok yok</p>
                            <p class="mt-1 text-xl font-semibold">
                                {{ outOfStockVisibleCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div
                        class="flex flex-wrap items-center gap-2 text-xs font-medium"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300"
                        >
                            <span
                                class="size-2 rounded-full bg-emerald-500"
                            ></span>
                            Anlık filtre sonucu
                        </span>
                        <span
                            v-if="hasActiveFilters"
                            class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                        >
                            Filtre aktif
                        </span>
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex items-center rounded-full px-3 py-1 text-slate-500 transition hover:bg-slate-200/70 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                            @click="resetFilters"
                        >
                            Filtreleri temizle
                        </button>
                    </div>

                    <div
                        v-if="selectedProductIds.length"
                        class="flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-900 shadow-sm sm:flex-row sm:items-center dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold">
                                {{ selectedProductIds.length }} ürün seçildi
                            </p>
                            <p
                                class="text-xs text-emerald-700/80 dark:text-emerald-200/75"
                            >
                                {{ selectedVisibleCount }} tanesi bu sayfada
                                görünüyor. Toplu yayın veya stok işlemi
                                uygulanabilir.
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="clearSelection"
                            >
                                Seçimi temizle
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                class="bg-emerald-600 text-white hover:bg-emerald-700"
                                @click="openBulkDrawer"
                            >
                                Toplu işlem uygula
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="props.products.data.length">
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full min-w-[1060px] text-left text-sm">
                        <thead
                            class="border-b border-slate-200 bg-white text-xs font-semibold tracking-wide text-slate-500 uppercase dark:border-white/10 dark:bg-[#07110f] dark:text-slate-400"
                        >
                            <tr>
                                <th class="w-12 px-5 py-4 text-center">
                                    <input
                                        type="checkbox"
                                        class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                                        :checked="allVisibleSelected"
                                        aria-label="Görünen ürünlerin tamamını seç"
                                        @change="toggleAllVisible"
                                    />
                                </th>
                                <th class="px-5 py-4">Ürün</th>
                                <th class="px-5 py-4">SKU / Kategori</th>
                                <th class="px-5 py-4 text-right">Fiyat</th>
                                <th class="px-5 py-4 text-center">
                                    Stok sağlığı
                                </th>
                                <th class="px-5 py-4 text-center">Durum</th>
                                <th class="px-5 py-4 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-white/10"
                        >
                            <tr
                                v-for="product in props.products.data"
                                :key="product.public_id"
                                class="transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.04]"
                                :class="
                                    selectedProductIds.includes(
                                        product.public_id,
                                    )
                                        ? 'bg-emerald-50/70 dark:bg-emerald-500/10'
                                        : ''
                                "
                            >
                                <td class="px-5 py-4 text-center align-top">
                                    <input
                                        v-model="selectedProductIds"
                                        type="checkbox"
                                        class="mt-4 size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                                        :value="product.public_id"
                                        :aria-label="`${product.name} ürününü seç`"
                                    />
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div
                                            class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"
                                        >
                                            <img
                                                v-if="productImage(product)"
                                                :src="productImage(product)!"
                                                :alt="product.name"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                            <span v-else>{{
                                                productInitials(product.name)
                                            }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="line-clamp-2 leading-5 font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{ product.name }}
                                            </p>
                                            <div
                                                class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 dark:bg-white/10"
                                                >
                                                    <ArchiveBoxIcon
                                                        class="size-3.5"
                                                    />
                                                    {{
                                                        product.brand ||
                                                        'Marka yok'
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        product.order_items_count >
                                                        0
                                                    "
                                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                                                >
                                                    {{
                                                        product.order_items_count
                                                    }}
                                                    sipariş
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <p
                                        class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{ product.sku || 'SKU yok' }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ product.category || 'Kategori yok' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-right align-top">
                                    <p
                                        class="font-semibold text-slate-950 dark:text-white"
                                    >
                                        {{ product.price }} TL
                                    </p>
                                    <p
                                        v-if="product.compare_at_price"
                                        class="mt-1 text-xs text-slate-400 line-through"
                                    >
                                        {{ product.compare_at_price }} TL
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-center align-top">
                                    <button
                                        type="button"
                                        class="inline-flex min-w-32 items-center justify-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                                        :class="stockBadgeClass(product)"
                                        @click="openStockDrawer(product)"
                                    >
                                        <span
                                            class="size-2 rounded-full"
                                            :class="stockDotClass(product)"
                                        ></span>
                                        {{ product.stock_quantity }} adet
                                    </button>
                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ stockLabel(product) }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-center align-top">
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusBadgeClass(product)"
                                    >
                                        {{
                                            product.status === 'published'
                                                ? 'Yayında'
                                                : 'Taslak'
                                        }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right align-top">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-purple-200 hover:bg-purple-50 hover:text-purple-700 dark:border-white/10 dark:text-slate-400 dark:hover:border-purple-500/30 dark:hover:bg-purple-500/10 dark:hover:text-purple-300"
                                            title="Stok hareketi"
                                            @click="openStockDrawer(product)"
                                        >
                                            <ArrowsRightLeftIcon
                                                class="size-4"
                                            />
                                        </button>
                                        <Link
                                            :href="`/stok/urun/${product.public_id}/duzenle`"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-white/10 dark:text-slate-400 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/10 dark:hover:text-blue-300"
                                            title="Ürünü düzenle"
                                        >
                                            <PencilSquareIcon class="size-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-white/10 dark:text-slate-400 dark:hover:border-red-500/30 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                            title="Ürünü sil veya yayından kaldır"
                                            @click="deleteProduct(product)"
                                        >
                                            <TrashIcon class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-3 p-4 lg:hidden">
                    <article
                        v-for="product in props.products.data"
                        :key="`mobile-${product.public_id}`"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/[0.03]"
                        :class="
                            selectedProductIds.includes(product.public_id)
                                ? 'border-emerald-300 bg-emerald-50/60 dark:border-emerald-500/30 dark:bg-emerald-500/10'
                                : ''
                        "
                    >
                        <div class="flex gap-3">
                            <input
                                v-model="selectedProductIds"
                                type="checkbox"
                                class="mt-3 size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                                :value="product.public_id"
                                :aria-label="`${product.name} ürününü seç`"
                            />
                            <div
                                class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"
                            >
                                <img
                                    v-if="productImage(product)"
                                    :src="productImage(product)!"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                />
                                <span v-else>{{
                                    productInitials(product.name)
                                }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="line-clamp-2 font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ product.name }}
                                </p>
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ product.sku || 'SKU yok' }} ·
                                    {{ product.category || 'Kategori yok' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Fiyat
                                </p>
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ product.price }} TL
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Durum
                                </p>
                                <span
                                    class="mt-1 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="statusBadgeClass(product)"
                                >
                                    {{
                                        product.status === 'published'
                                            ? 'Yayında'
                                            : 'Taslak'
                                    }}
                                </span>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Stok
                                </p>
                                <button
                                    type="button"
                                    class="mt-1 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                                    :class="stockBadgeClass(product)"
                                    @click="openStockDrawer(product)"
                                >
                                    <span
                                        class="size-2 rounded-full"
                                        :class="stockDotClass(product)"
                                    ></span>
                                    {{ product.stock_quantity }} adet
                                </button>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Sipariş
                                </p>
                                <p
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ product.order_items_count }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-white/10"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="openStockDrawer(product)"
                            >
                                <ArrowsRightLeftIcon class="size-4" />
                                Stok
                            </Button>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="`/stok/urun/${product.public_id}/duzenle`"
                                >
                                    <PencilSquareIcon class="mr-2 size-4" />
                                    Düzenle
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon-sm"
                                title="Sil"
                                @click="deleteProduct(product)"
                            >
                                <TrashIcon class="size-4 text-red-600" />
                            </Button>
                        </div>
                    </article>
                </div>
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center px-4 py-16 text-center"
            >
                <span
                    class="flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-300"
                >
                    <CubeIcon class="size-7" />
                </span>
                <h3
                    class="mt-4 text-base font-semibold text-slate-950 dark:text-white"
                >
                    Kayıtlı ürün bulunamadı
                </h3>
                <p
                    class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400"
                >
                    Arama veya filtreleri genişletin. Katalog boşsa yeni ürün
                    ekleyerek gerçek stok takibini başlatın.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <Button
                        v-if="hasActiveFilters"
                        type="button"
                        variant="outline"
                        @click="resetFilters"
                    >
                        Filtreleri temizle
                    </Button>
                    <Button
                        class="bg-emerald-600 text-white hover:bg-emerald-700"
                        as-child
                    >
                        <Link href="/stok/urun/yeni">
                            <PlusIcon class="mr-2 size-4" />
                            Yeni ürün ekle
                        </Link>
                    </Button>
                </div>
            </div>

            <div
                v-if="props.products.links?.length"
                class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-white/[0.03]"
            >
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Sayfada {{ visibleProductCount }} ürün gösteriliyor.
                    <span
                        v-if="outOfStockVisibleCount"
                        class="font-semibold text-red-600 dark:text-red-300"
                    >
                        {{ outOfStockVisibleCount }} ürün stok dışı.
                    </span>
                </p>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="link in props.products.links"
                        :key="`${link.label}-${link.url}`"
                        type="button"
                        class="inline-flex min-w-9 items-center justify-center rounded-md border px-3 py-1.5 text-xs font-semibold transition"
                        :class="[
                            link.active
                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300',
                            !link.url ? 'cursor-not-allowed opacity-45' : '',
                        ]"
                        :disabled="!link.url"
                        @click="visitPagination(link.url)"
                    >
                        <ChevronLeftIcon
                            v-if="paginationLabel(link.label).includes('‹')"
                            class="size-3.5"
                        />
                        <ChevronRightIcon
                            v-else-if="
                                paginationLabel(link.label).includes('›')
                            "
                            class="size-3.5"
                        />
                        <span v-else>{{ paginationLabel(link.label) }}</span>
                    </button>
                </div>
            </div>

            <div
                v-if="lowStockVisibleCount || outOfStockVisibleCount"
                class="flex gap-3 border-t border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200"
            >
                <ExclamationTriangleIcon class="mt-0.5 size-5 shrink-0" />
                <p>
                    Bu sayfada düşük veya bitmiş stok var. Satışa açık ürünlerde
                    stok hareketi ekranından gerçek giriş/çıkış kaydı oluşturun.
                </p>
            </div>
        </section>
    </div>

    <!-- 1. Ürün Ekleme/Düzenleme Drawer -->
    <!-- 2. Toplu İşlem Drawer -->
    <Sheet :open="isBulkDrawerOpen" @update:open="isBulkDrawerOpen = $event">
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Toplu İşlem Uygula</SheetTitle>
                <SheetDescription>
                    Seçili {{ selectedProductIds.length }} ürün üzerinde toplu
                    işlem gerçekleştirin.
                </SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submitBulkAction" class="mt-8 space-y-6">
                <div
                    class="space-y-4 rounded-lg border bg-muted/5 p-4 shadow-sm"
                >
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-3"
                    >
                        <ArrowsRightLeftIcon class="size-4 text-primary" />
                        <h3 class="text-sm font-medium text-foreground">
                            İşlem Detayları
                        </h3>
                    </div>

                    <div class="space-y-2">
                        <Label>İşlem Türü</Label>
                        <Select v-model="bulkForm.action">
                            <SelectTrigger class="bg-background"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="publish"
                                    >Yayına Al</SelectItem
                                >
                                <SelectItem value="draft"
                                    >Taslağa Çek</SelectItem
                                >
                                <SelectItem value="stock_increase"
                                    >Stok Ekle</SelectItem
                                >
                                <SelectItem value="stock_decrease"
                                    >Stok Düş</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div
                        class="space-y-2"
                        v-if="
                            ['stock_increase', 'stock_decrease'].includes(
                                bulkForm.action,
                            )
                        "
                    >
                        <Label>Miktar</Label>
                        <Input
                            v-model="bulkForm.quantity_delta"
                            type="number"
                            min="1"
                            placeholder="Adet"
                            class="bg-background"
                        />
                        <InputError :message="bulkForm.errors.quantity_delta" />
                    </div>

                    <div class="space-y-2">
                        <Label>İşlem Notu</Label>
                        <Input
                            v-model="bulkForm.note"
                            maxlength="500"
                            placeholder="Bu işlemle ilgili not..."
                            class="bg-background"
                        />
                    </div>
                </div>

                <div class="flex justify-end border-t border-border/50 pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="mr-2"
                        @click="isBulkDrawerOpen = false"
                        >İptal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="bulkForm.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >Uygula</Button
                    >
                </div>
            </form>
        </SheetContent>
    </Sheet>

    <!-- Hızlı Marka Drawer -->
    <!-- AI Atölyesi Drawer -->
    <!-- 3. Stok Hareketi Drawer -->
    <Sheet :open="isStockDrawerOpen" @update:open="isStockDrawerOpen = $event">
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Stok Hareketi</SheetTitle>
                <SheetDescription v-if="selectedProduct">
                    <span class="font-semibold text-gray-900 dark:text-white">{{
                        selectedProduct.name
                    }}</span>
                    ürünü için stok ayarlaması. Mevcut Stok:
                    <span class="font-bold">{{
                        selectedProduct.stock_quantity
                    }}</span>
                </SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submitAdjustment" class="mt-8 space-y-6">
                <div
                    class="space-y-4 rounded-lg border bg-muted/5 p-4 shadow-sm"
                >
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-3"
                    >
                        <ArrowsRightLeftIcon class="size-4 text-primary" />
                        <h3 class="text-sm font-medium text-foreground">
                            Hareket Bilgileri
                        </h3>
                    </div>

                    <div class="space-y-2">
                        <Label>Değişim Miktarı</Label>
                        <Input
                            v-model="stockForm.quantity_delta"
                            type="number"
                            required
                            placeholder="Örn: 5 veya -3"
                            class="bg-background"
                        />
                        <p class="text-xs text-muted-foreground">
                            Pozitif değer stoğu artırır, negatif değer stoğu
                            azaltır.
                        </p>
                        <InputError
                            :message="stockForm.errors.quantity_delta"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>İşlem Nedeni</Label>
                        <Select v-model="stockForm.reason">
                            <SelectTrigger class="bg-background"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="manual_correction"
                                    >Manuel Düzeltme</SelectItem
                                >
                                <SelectItem value="purchase_entry"
                                    >Mal Girişi</SelectItem
                                >
                                <SelectItem value="damage"
                                    >Hasar / Fire</SelectItem
                                >
                                <SelectItem value="return_entry"
                                    >İade Girişi</SelectItem
                                >
                                <SelectItem value="reservation_release"
                                    >Rezervasyon İptali</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label>Not / Açıklama</Label>
                        <Input
                            v-model="stockForm.note"
                            placeholder="İrsaliye no veya ek açıklama..."
                            class="bg-background"
                        />
                    </div>
                </div>

                <div class="flex justify-end border-t border-border/50 pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="mr-2"
                        @click="isStockDrawerOpen = false"
                        >İptal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="stockForm.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >Kaydet</Button
                    >
                </div>
            </form>
        </SheetContent>
    </Sheet>

    <!-- 4. Son Hareketler Drawer -->
    <Sheet
        :open="isHistoryDrawerOpen"
        @update:open="isHistoryDrawerOpen = $event"
    >
        <SheetContent class="w-full overflow-y-auto sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Son Stok Hareketleri</SheetTitle>
                <SheetDescription
                    >Tüm ürünlerdeki son hareket geçmişi.</SheetDescription
                >
            </SheetHeader>

            <div class="mt-6 space-y-4">
                <div
                    v-for="movement in props.recentMovements"
                    :key="movement.public_id"
                    class="border-b pb-4 last:border-0 dark:border-white/10"
                >
                    <div class="mb-1 flex items-start justify-between">
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                            >{{ movement.product || 'Bilinmeyen Ürün' }}</span
                        >
                        <span
                            class="text-sm font-bold"
                            :class="
                                movement.quantity_delta > 0
                                    ? 'text-emerald-600'
                                    : 'text-red-600'
                            "
                        >
                            {{ movement.quantity_delta > 0 ? '+' : ''
                            }}{{ movement.quantity_delta }}
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>{{ movement.reason }}</span>
                        <span>{{
                            new Date(movement.created_at).toLocaleString(
                                'tr-TR',
                            )
                        }}</span>
                    </div>
                    <div
                        class="mt-1 text-xs text-gray-400"
                        v-if="movement.note"
                    >
                        Not: {{ movement.note }}
                    </div>
                </div>
                <div
                    v-if="!props.recentMovements.length"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Kayıtlı hareket bulunamadı.
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
