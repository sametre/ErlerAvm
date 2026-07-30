<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, Loader2 } from '@lucide/vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import BrandLogo from '@/components/brand/BrandLogo.vue';

const isOpen = ref(false);
const searchQuery = ref('');
const isSearching = ref(false);
const results = ref<any[]>([]);
let timeout: any = null;

watch(searchQuery, (newVal) => {
    if (newVal.length < 3) {
        results.value = [];
        return;
    }

    isSearching.value = true;

    if (timeout) clearTimeout(timeout);

    timeout = setTimeout(async () => {
        try {
            const response = await fetch(
                `/arama?q=${encodeURIComponent(newVal)}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();

            if (data && data.data) {
                results.value = data.data;
            } else {
                results.value = [];
            }
        } catch (e) {
            console.error('Search failed', e);
        } finally {
            isSearching.value = false;
        }
    }, 400); // Debounce 400ms
});

function closeDrawer() {
    isOpen.value = false;
}
</script>

<template>
    <Sheet v-model:open="isOpen">
        <SheetTrigger as-child>
            <slot />
        </SheetTrigger>
        <SheetContent
            side="bottom"
            class="flex h-[85vh] flex-col rounded-t-3xl p-0 shadow-[0_-10px_40px_rgba(0,0,0,0.15)] outline-none"
        >
            <SheetHeader class="sr-only">
                <SheetTitle>Ürün Ara</SheetTitle>
                <SheetDescription>Canlı ürün arama çekmecesi</SheetDescription>
            </SheetHeader>

            <div
                class="flex shrink-0 items-center gap-3 border-b px-4 pt-6 pb-4"
            >
                <form @submit.prevent class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Hangi ürünü arıyorsunuz?"
                        class="h-12 w-full rounded-2xl bg-muted/60 pr-10 pl-11 text-[15px] font-medium shadow-inner transition-all outline-none focus:bg-white focus:ring-2 focus:ring-primary/30"
                    />
                    <Loader2
                        v-if="isSearching"
                        class="absolute top-1/2 right-3.5 size-4 -translate-y-1/2 animate-spin text-primary"
                    />
                </form>
            </div>

            <div class="flex-1 overflow-y-auto p-3">
                <div
                    v-if="searchQuery.length > 0 && searchQuery.length < 3"
                    class="p-8 text-center text-sm font-medium text-muted-foreground"
                >
                    Arama yapmak için en az 3 harf girin.
                </div>

                <div
                    v-else-if="
                        searchQuery.length >= 3 &&
                        !isSearching &&
                        results.length === 0
                    "
                    class="p-8 text-center text-sm font-medium text-muted-foreground"
                >
                    "{{ searchQuery }}" için sonuç bulunamadı.
                </div>

                <div
                    v-else-if="results.length > 0"
                    class="flex flex-col gap-1.5"
                >
                    <Link
                        v-for="product in results"
                        :key="product.public_id"
                        :href="product.url"
                        @click="closeDrawer"
                        class="flex items-center gap-3 rounded-2xl p-2.5 transition-colors hover:bg-slate-50"
                    >
                        <div
                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border bg-slate-50"
                        >
                            <img
                                v-if="product.images?.[0]"
                                :src="product.images[0]"
                                :alt="product.name"
                                class="h-full w-full object-cover mix-blend-multiply"
                            />
                            <BrandLogo
                                v-else
                                compact
                                class="size-8 opacity-30 grayscale"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                v-if="product.brand"
                                class="mb-0.5 text-[9px] font-black tracking-wider text-slate-400 uppercase"
                            >
                                {{ product.brand.name }}
                            </p>
                            <h4
                                class="line-clamp-2 text-[13px] leading-snug font-bold text-slate-800"
                            >
                                {{ product.name }}
                            </h4>
                            <div class="mt-1 flex items-end gap-2">
                                <p class="text-sm font-black text-primary">
                                    {{ product.price }} TL
                                </p>
                                <p
                                    v-if="product.compare_at_price"
                                    class="mb-0.5 text-[10px] text-slate-400 line-through"
                                >
                                    {{ product.compare_at_price }} TL
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center gap-4 p-12 text-center opacity-50"
                >
                    <Search class="size-10 text-slate-400" />
                    <p class="text-[13px] font-medium text-slate-500">
                        Aradığınız ürünün adını yazın...
                    </p>
                </div>
            </div>

            <div
                v-if="results.length > 0"
                class="shrink-0 border-t bg-white p-4"
            >
                <Link
                    :href="`/arama?q=${searchQuery}`"
                    @click="closeDrawer"
                    class="flex h-12 w-full items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white shadow-md transition-colors hover:bg-primary"
                >
                    Tüm "{{ searchQuery }}" Sonuçlarını Gör
                </Link>
            </div>
        </SheetContent>
    </Sheet>
</template>
