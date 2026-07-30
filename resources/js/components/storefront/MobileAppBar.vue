<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Heart,
    Home,
    PackageSearch,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import MobileSearchDrawer from '@/components/storefront/MobileSearchDrawer.vue';

const page = usePage();
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t bg-white/95 px-2 pt-2 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden"
        aria-label="Mobil alt navigasyon"
    >
        <Link
            href="/"
            class="flex flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium"
        >
            <Home class="size-5" />
            Ana sayfa
        </Link>
        <MobileSearchDrawer>
            <button
                type="button"
                class="flex w-full flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium"
            >
                <PackageSearch class="size-5" />
                Ürünler
            </button>
        </MobileSearchDrawer>
        <Link
            href="/sepet"
            class="flex flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium text-primary"
        >
            <ShoppingCart class="size-5" />
            Sepet
        </Link>
        <Link
            href="/hesabim/favorilerim"
            class="relative flex flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium"
        >
            <Heart class="size-5" />
            Favori
            <span
                v-if="(page.props.favorites as string[])?.length > 0"
                class="absolute top-1 right-2 flex size-3.5 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-white"
            >
                {{ (page.props.favorites as string[]).length }}
            </span>
        </Link>
        <Link
            href="/hesabim"
            class="flex flex-col items-center gap-1 px-1 py-2 text-[11px] font-medium"
        >
            <UserRound class="size-5" />
            Hesabım
        </Link>
    </nav>
</template>
