<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { PackageSearch, Search as SearchIcon, Filter } from '@lucide/vue';
import { ref, watch } from 'vue';
import ProductList from '@/components/ProductList.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';
import SearchFilters from '@/components/storefront/SearchFilters.vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

const props = defineProps<{
    query: string;
    filters?: any;
    options?: any;
    products?: any;
}>();

const searchQuery = ref(props.query);
const currentFilters = ref(
    props.filters || {
        categories: [],
        brands: [],
        min_price: null,
        max_price: null,
        in_stock: false,
    },
);
const isMobileFilterOpen = ref(false);

let timeout: ReturnType<typeof setTimeout>;

function executeSearch() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            '/arama',
            {
                q: searchQuery.value.trim().slice(0, 80),
                ...currentFilters.value,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 500);
}

function handleSearch() {
    executeSearch();
}

watch(
    currentFilters,
    (newFilters) => {
        // Only execute search if it differs from the current props to avoid loop
        if (JSON.stringify(newFilters) !== JSON.stringify(props.filters)) {
            executeSearch();
        }
    },
    { deep: true },
);

watch(
    () => props.filters,
    (newFilters) => {
        if (
            newFilters &&
            JSON.stringify(currentFilters.value) !== JSON.stringify(newFilters)
        ) {
            currentFilters.value = {
                categories: [...(newFilters.categories || [])],
                brands: [...(newFilters.brands || [])],
                min_price: newFilters.min_price || null,
                max_price: newFilters.max_price || null,
                in_stock: !!newFilters.in_stock,
            };
        }
    },
    { deep: true },
);
</script>

<template>
    <main class="min-h-svh bg-[#f7f7f8] text-foreground">
        <StorefrontHeader />

        <div class="bg-primary px-4 py-8 md:py-12">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-3xl font-black text-white md:text-5xl">
                    Ne aramıştınız?
                </h1>
                <p class="mt-3 text-sm text-white/80 md:text-base">
                    Binlerce ürün arasında hızlıca arama yapın ve filtreleyin.
                </p>

                <div class="relative mx-auto mt-6 max-w-2xl">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-4 flex items-center"
                    >
                        <SearchIcon class="size-6 text-primary" />
                    </div>
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="search"
                        maxlength="80"
                        inputmode="search"
                        autocomplete="off"
                        class="h-14 w-full rounded-full border border-white/70 bg-white pr-6 pl-14 text-base font-medium text-slate-950 caret-primary shadow-xl outline-none placeholder:text-slate-500 focus:ring-4 focus:ring-white/35 md:text-lg"
                        placeholder="Örn: Akıllı Telefon, Kahve Makinesi..."
                    />
                </div>
            </div>
        </div>

        <section class="mx-auto max-w-7xl px-4 py-8">
            <div class="mb-6 flex items-center justify-between border-b pb-4">
                <h2 class="text-lg font-bold md:text-xl">
                    <template v-if="props.query">
                        "<span class="text-primary">{{ props.query }}</span
                        >" için sonuçlar
                    </template>
                    <template v-else> Tüm Ürünler </template>
                </h2>

                <div class="flex items-center gap-3 md:gap-4">
                    <span
                        class="text-xs font-semibold text-muted-foreground md:text-sm"
                        >{{ props.products?.total || 0 }} ürün bulundu</span
                    >

                    <!-- Mobile Filter Button & Sheet -->
                    <Sheet v-model:open="isMobileFilterOpen">
                        <SheetTrigger as-child>
                            <button
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 shadow-sm transition-all hover:bg-slate-50 active:scale-95 lg:hidden"
                            >
                                <Filter class="size-4 text-primary" />
                                Filtrele
                            </button>
                        </SheetTrigger>
                        <SheetContent
                            side="right"
                            class="flex w-[85vw] max-w-sm flex-col overflow-y-auto p-0"
                        >
                            <SheetHeader class="shrink-0 border-b px-5 py-4">
                                <SheetTitle class="text-left font-black"
                                    >Filtreler</SheetTitle
                                >
                            </SheetHeader>
                            <div class="flex-1 overflow-y-auto p-5">
                                <SearchFilters
                                    v-model:filters="currentFilters"
                                    :options="props.options"
                                />
                            </div>
                            <div class="shrink-0 border-t bg-slate-50 p-4">
                                <button
                                    @click="isMobileFilterOpen = false"
                                    class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white shadow-md transition-colors hover:bg-primary/90"
                                >
                                    Sonuçları Göster ({{
                                        props.products?.total || 0
                                    }})
                                </button>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>

            <div
                class="grid grid-cols-1 gap-6 lg:grid-cols-[260px_1fr] lg:gap-8"
            >
                <!-- Desktop Sidebar -->
                <aside class="hidden lg:block">
                    <div
                        class="sticky top-24 rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm"
                    >
                        <h3
                            class="mb-5 border-b pb-3 text-lg font-black text-slate-800"
                        >
                            Daralt
                        </h3>
                        <SearchFilters
                            v-model:filters="currentFilters"
                            :options="props.options"
                        />
                    </div>
                </aside>

                <!-- Product Listing -->
                <div class="min-w-0">
                    <div v-if="props.products?.data?.length">
                        <ProductList :products="props.products" />
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-24 text-center"
                    >
                        <div
                            class="mb-6 flex size-20 items-center justify-center rounded-full bg-slate-100 shadow-inner"
                        >
                            <PackageSearch class="size-10 text-slate-400" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">
                            Sonuç bulunamadı
                        </h3>
                        <p class="mt-2 max-w-md text-sm text-slate-500">
                            Aramanızla eşleşen ürün bulamadık. Farklı
                            kelimelerle tekrar aramayı deneyebilir veya
                            filtreleri temizleyebilirsiniz.
                        </p>
                        <button
                            @click="
                                () => {
                                    searchQuery = '';
                                    currentFilters = {
                                        categories: [],
                                        brands: [],
                                        min_price: null,
                                        max_price: null,
                                        in_stock: false,
                                    };
                                }
                            "
                            class="mt-6 rounded-xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-primary/90 hover:shadow-lg"
                        >
                            Aramayı Sıfırla
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <StorefrontFooter />
    </main>
</template>
