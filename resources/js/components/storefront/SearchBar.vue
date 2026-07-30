<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Search, X, PackageSearch } from '@lucide/vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    placeholder?: string;
}>();

const query = ref('');
const suggestions = ref<any[]>([]);
const isLoading = ref(false);
const isOpen = ref(false);

const searchContainer = ref<HTMLElement | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout>;
let abortController: AbortController | null = null;

const performSearch = async () => {
    if (query.value.trim().length < 2) {
        suggestions.value = [];
        isLoading.value = false;
        return;
    }

    isLoading.value = true;
    isOpen.value = true;

    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    try {
        const response = await fetch(
            `/arama?q=${encodeURIComponent(query.value)}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: abortController.signal,
            },
        );
        const data = await response.json();
        suggestions.value = data.data || [];
    } catch (error: any) {
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
        }
    } finally {
        isLoading.value = false;
    }
};

watch(query, () => {
    clearTimeout(debounceTimeout);

    if (query.value.trim().length < 2) {
        suggestions.value = [];
        isLoading.value = false;
        isOpen.value = false;
        return;
    }

    isOpen.value = true;
    isLoading.value = true;

    debounceTimeout = setTimeout(() => {
        performSearch();
    }, 500);
});

const closeDropdown = () => {
    isOpen.value = false;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        closeDropdown();
    }
};

const handleClickOutside = (e: MouseEvent) => {
    if (
        searchContainer.value &&
        !searchContainer.value.contains(e.target as Node)
    ) {
        closeDropdown();
    }
};

const submitSearch = () => {
    if (query.value.trim()) {
        closeDropdown();
        router.get(`/arama?q=${encodeURIComponent(query.value.trim())}`);
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
    clearTimeout(debounceTimeout);
});
</script>

<template>
    <div class="relative w-full" ref="searchContainer">
        <form
            @submit.prevent="submitSearch"
            class="relative flex h-11 min-w-0 items-center rounded-md border-2 border-primary bg-white px-3 transition-all focus-within:ring-2 focus-within:ring-primary/20"
        >
            <Search class="size-5 shrink-0 text-primary" />
            <input
                v-model="query"
                type="search"
                class="h-full min-w-0 flex-1 bg-transparent px-3 text-sm text-foreground outline-none placeholder:text-muted-foreground"
                :placeholder="
                    props.placeholder || $t('common.search_placeholder')
                "
                @focus="query.length >= 2 ? (isOpen = true) : null"
            />
            <button
                v-if="query"
                type="button"
                @click="
                    query = '';
                    suggestions = [];
                    isOpen = false;
                "
                class="flex items-center justify-center p-1 text-muted-foreground hover:text-foreground"
            >
                <X class="size-4" />
            </button>
            <button
                type="submit"
                class="ml-2 hidden rounded-sm bg-primary px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-primary/90 sm:block"
            >
                {{ $t('nav.search') }}
            </button>
        </form>

        <!-- Dropdown Suggestions -->
        <div
            v-if="isOpen && query.length >= 2"
            class="absolute top-full right-0 left-0 z-50 mt-2 origin-top overflow-hidden rounded-xl border bg-card text-card-foreground shadow-2xl transition-all duration-200"
        >
            <!-- Siri-like Animation (Loading State) -->
            <div
                v-if="isLoading"
                class="flex flex-col items-center justify-center py-12"
            >
                <div
                    class="relative flex h-20 w-20 items-center justify-center"
                >
                    <!-- Layer 1: Expand and fade -->
                    <div
                        class="absolute h-full w-full animate-ping rounded-full bg-red-500 opacity-20"
                    ></div>
                    <!-- Layer 2: Pulse and glow -->
                    <div
                        class="absolute h-16 w-16 animate-pulse rounded-full bg-red-600 opacity-60 blur-md"
                    ></div>
                    <!-- Layer 3: Spin with gradient -->
                    <div
                        class="absolute h-12 w-12 animate-spin rounded-full bg-gradient-to-tr from-red-400 to-red-800"
                    ></div>
                    <!-- Layer 4: Core -->
                    <div
                        class="h-6 w-6 animate-pulse rounded-full bg-white shadow-lg"
                    ></div>
                </div>
                <p
                    class="mt-4 animate-pulse text-sm font-medium text-muted-foreground"
                >
                    {{ $t('common.search_searching') }}
                </p>
            </div>

            <!-- Results -->
            <div
                v-else-if="suggestions.length > 0"
                class="max-h-[60vh] overflow-y-auto p-2"
            >
                <p
                    class="px-3 py-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    {{ $t('common.search_suggestions') }}
                </p>
                <Link
                    v-for="product in suggestions"
                    :key="product.public_id"
                    :href="product.url"
                    @click="closeDropdown"
                    class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-muted"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded border bg-white"
                    >
                        <img
                            v-if="product.images?.length"
                            :src="product.images[0]"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <PackageSearch
                            v-else
                            class="size-5 text-muted-foreground opacity-50"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ product.name }}
                        </p>
                        <p
                            v-if="product.category"
                            class="text-xs text-muted-foreground"
                        >
                            {{ product.category.name }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold text-primary">
                            {{ product.price }} TL
                        </p>
                    </div>
                </Link>

                <div class="mt-2 border-t p-2">
                    <button
                        @click="submitSearch"
                        class="w-full rounded-md bg-muted py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted/80"
                    >
                        {{ $t('common.all_results') }} ({{ query }})
                    </button>
                </div>
            </div>

            <!-- No Results -->
            <div v-else class="py-12 text-center">
                <PackageSearch
                    class="mx-auto size-10 text-muted-foreground opacity-20"
                />
                <p class="mt-4 text-sm font-medium">
                    {{ $t('common.search_not_found') }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ $t('common.search_try_again') }}
                </p>
            </div>
        </div>
    </div>
</template>
