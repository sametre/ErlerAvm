<script setup lang="ts">
import BrandLogo from '@/components/brand/BrandLogo.vue';

type Banner = {
    title: string;
    image_path?: string | null;
    cta_url?: string | null;
};

const props = defineProps<{
    logos?: Banner[];
}>();
</script>

<template>
    <section class="overflow-hidden border-y border-border/50 bg-white py-8">
        <div class="mx-auto mb-6 max-w-7xl px-4 text-center">
            <h3
                class="text-sm font-bold tracking-widest text-muted-foreground uppercase"
            >
                Çözüm Ortaklarımız ve Markalar
            </h3>
        </div>
        <div class="relative flex w-full overflow-hidden">
            <div
                class="animate-marquee flex min-w-full items-center whitespace-nowrap"
            >
                <!-- Group 1 -->
                <div
                    class="flex w-full shrink-0 items-center justify-around gap-16 px-8"
                >
                    <template v-if="props.logos && props.logos.length > 0">
                        <a
                            v-for="(logo, idx) in props.logos"
                            :key="idx"
                            :href="logo.cta_url || '#'"
                            class="block"
                        >
                            <img
                                :src="logo.image_path || ''"
                                :alt="logo.title"
                                class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                            />
                        </a>
                    </template>
                    <template v-else>
                        <img
                            src="/images/banks/visa.png"
                            alt="Visa"
                            class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                        />
                        <img
                            src="/images/banks/mastercard.svg"
                            alt="Mastercard"
                            class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                        />
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            SAMSUNG
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            APPLE
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            BOSCH
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            SONY
                        </div>
                    </template>
                </div>
            </div>

            <div
                class="animate-marquee2 absolute top-0 flex min-w-full items-center whitespace-nowrap"
            >
                <!-- Group 2 (Duplicate for infinite scroll) -->
                <div
                    class="flex w-full shrink-0 items-center justify-around gap-16 px-8"
                >
                    <template v-if="props.logos && props.logos.length > 0">
                        <a
                            v-for="(logo, idx) in props.logos"
                            :key="'dup-' + idx"
                            :href="logo.cta_url || '#'"
                            class="block"
                        >
                            <img
                                :src="logo.image_path || ''"
                                :alt="logo.title"
                                class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                            />
                        </a>
                    </template>
                    <template v-else>
                        <img
                            src="/images/banks/visa.png"
                            alt="Visa"
                            class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                        />
                        <img
                            src="/images/banks/mastercard.svg"
                            alt="Mastercard"
                            class="h-10 object-contain opacity-50 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
                        />
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            SAMSUNG
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            APPLE
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            BOSCH
                        </div>
                        <div
                            class="flex h-12 w-32 items-center justify-center rounded bg-primary/10 text-xl font-black text-primary/40 transition-colors hover:text-primary"
                        >
                            SONY
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.animate-marquee {
    animation: marquee 25s linear infinite;
}
.animate-marquee2 {
    animation: marquee2 25s linear infinite;
}

@keyframes marquee {
    0% {
        transform: translateX(0%);
    }
    100% {
        transform: translateX(-100%);
    }
}
@keyframes marquee2 {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(0%);
    }
}
</style>
