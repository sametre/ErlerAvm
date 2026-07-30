<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-[#f7f7f8] text-foreground">
        <StorefrontHeader />

        <main
            class="relative flex flex-1 flex-col items-center justify-center overflow-hidden p-6 py-16 md:p-10"
        >
            <!-- Decorative background elements -->
            <div
                class="absolute top-0 left-1/2 -z-10 h-[400px] w-[800px] -translate-x-1/2 rounded-full bg-primary/5 blur-3xl"
            ></div>

            <div
                class="w-full max-w-[420px] rounded-3xl border border-white/50 bg-white/80 p-8 shadow-2xl shadow-slate-200/50 backdrop-blur-xl"
            >
                <div class="mb-8 flex flex-col items-center gap-6">
                    <Link
                        :href="home()"
                        class="group flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm transition-transform group-hover:scale-105 group-active:scale-95"
                        >
                            <BrandLogo compact class="size-12" />
                        </div>
                    </Link>
                </div>
                <slot />
            </div>
        </main>

        <StorefrontFooter />
    </div>
</template>
