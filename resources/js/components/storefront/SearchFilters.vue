<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    filters: {
        categories: string[];
        brands: string[];
        min_price: number | null;
        max_price: number | null;
        in_stock: boolean;
    };
    options: {
        categories: { name: string; slug: string }[];
        brands: { name: string; slug: string }[];
    };
}>();

const emit = defineEmits(['update:filters']);

// Create a local mutable copy of filters
const localFilters = ref({
    categories: [...(props.filters?.categories || [])],
    brands: [...(props.filters?.brands || [])],
    min_price: props.filters?.min_price || null,
    max_price: props.filters?.max_price || null,
    in_stock: !!props.filters?.in_stock,
});

// Watch for changes in localFilters and emit
watch(
    localFilters,
    (newVal) => {
        if (JSON.stringify(newVal) !== JSON.stringify(props.filters)) {
            emit('update:filters', newVal);
        }
    },
    { deep: true },
);

// Sync from props if URL changes externally
watch(
    () => props.filters,
    (newVal) => {
        if (JSON.stringify(localFilters.value) !== JSON.stringify(newVal)) {
            localFilters.value = {
                categories: [...(newVal?.categories || [])],
                brands: [...(newVal?.brands || [])],
                min_price: newVal?.min_price || null,
                max_price: newVal?.max_price || null,
                in_stock: !!newVal?.in_stock,
            };
        }
    },
    { deep: true },
);

function clearFilters() {
    localFilters.value = {
        categories: [],
        brands: [],
        min_price: null,
        max_price: null,
        in_stock: false,
    };
}
</script>

<template>
    <div class="space-y-6">
        <!-- Stock Filter -->
        <div class="flex items-center justify-between">
            <label
                for="in_stock"
                class="cursor-pointer text-sm font-semibold text-slate-800"
                >Sadece Stoktakiler</label
            >
            <div
                class="relative mr-2 inline-block w-10 align-middle transition duration-200 ease-in select-none"
            >
                <input
                    type="checkbox"
                    id="in_stock"
                    v-model="localFilters.in_stock"
                    class="toggle-checkbox absolute block h-5 w-5 cursor-pointer appearance-none rounded-full border-4 bg-white transition-transform duration-200 ease-in-out"
                    :class="
                        localFilters.in_stock
                            ? 'translate-x-5 border-primary'
                            : 'translate-x-0 border-slate-300'
                    "
                />
                <label
                    for="in_stock"
                    class="toggle-label block h-5 cursor-pointer overflow-hidden rounded-full bg-slate-300 transition-colors duration-200 ease-in-out"
                    :class="localFilters.in_stock ? 'bg-primary' : ''"
                ></label>
            </div>
        </div>

        <hr class="border-slate-100" />

        <!-- Price Filter -->
        <div>
            <h4 class="mb-3 text-sm font-bold text-slate-900">Fiyat Aralığı</h4>
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span
                        class="absolute top-1/2 left-3 -translate-y-1/2 text-xs font-medium text-slate-400"
                        >₺</span
                    >
                    <input
                        type="number"
                        min="0"
                        v-model="localFilters.min_price"
                        placeholder="En Az"
                        class="w-full rounded-lg border border-slate-200 py-2 pr-3 pl-6 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    />
                </div>
                <span class="text-slate-400">-</span>
                <div class="relative flex-1">
                    <span
                        class="absolute top-1/2 left-3 -translate-y-1/2 text-xs font-medium text-slate-400"
                        >₺</span
                    >
                    <input
                        type="number"
                        min="0"
                        v-model="localFilters.max_price"
                        placeholder="En Çok"
                        class="w-full rounded-lg border border-slate-200 py-2 pr-3 pl-6 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    />
                </div>
            </div>
        </div>

        <hr class="border-slate-100" />

        <!-- Categories Filter -->
        <div v-if="options.categories?.length">
            <h4 class="mb-3 text-sm font-bold text-slate-900">Kategoriler</h4>
            <div
                class="custom-scrollbar max-h-48 space-y-2 overflow-y-auto pr-2"
            >
                <label
                    v-for="category in options.categories"
                    :key="category.slug"
                    class="group flex cursor-pointer items-start gap-2.5"
                >
                    <div
                        class="relative mt-0.5 flex shrink-0 items-center justify-center"
                    >
                        <input
                            type="checkbox"
                            :value="category.slug"
                            v-model="localFilters.categories"
                            class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 bg-white transition-all checked:border-primary checked:bg-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                        />
                        <svg
                            class="pointer-events-none absolute h-3 w-3 text-white opacity-0 transition-opacity peer-checked:opacity-100"
                            viewBox="0 0 14 10"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M1 5L4.5 8.5L13 1"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <span
                        class="line-clamp-1 text-sm leading-snug font-medium text-slate-600 transition-colors group-hover:text-slate-900"
                    >
                        {{ category.name }}
                    </span>
                </label>
            </div>
        </div>

        <hr
            v-if="options.categories?.length && options.brands?.length"
            class="border-slate-100"
        />

        <!-- Brands Filter -->
        <div v-if="options.brands?.length">
            <h4 class="mb-3 text-sm font-bold text-slate-900">Markalar</h4>
            <div
                class="custom-scrollbar max-h-48 space-y-2 overflow-y-auto pr-2"
            >
                <label
                    v-for="brand in options.brands"
                    :key="brand.slug"
                    class="group flex cursor-pointer items-start gap-2.5"
                >
                    <div
                        class="relative mt-0.5 flex shrink-0 items-center justify-center"
                    >
                        <input
                            type="checkbox"
                            :value="brand.slug"
                            v-model="localFilters.brands"
                            class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 bg-white transition-all checked:border-primary checked:bg-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                        />
                        <svg
                            class="pointer-events-none absolute h-3 w-3 text-white opacity-0 transition-opacity peer-checked:opacity-100"
                            viewBox="0 0 14 10"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M1 5L4.5 8.5L13 1"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <span
                        class="line-clamp-1 text-sm leading-snug font-medium text-slate-600 transition-colors group-hover:text-slate-900"
                    >
                        {{ brand.name }}
                    </span>
                </label>
            </div>
        </div>

        <!-- Clear Button -->
        <button
            @click="clearFilters"
            class="mt-2 w-full rounded-xl border-2 border-slate-200 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
        >
            Filtreleri Temizle
        </button>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
