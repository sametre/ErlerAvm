<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SeoHead from '@/components/seo/SeoHead.vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';

withDefaults(
    defineProps<{
        title: string;
        description: string;
        seoTitle?: string | null;
        seoDescription?: string | null;
        eyebrow?: string;
        noindex?: boolean;
        canonical?: string;
        image?: string;
        keywords?: string | null;
        jsonLd?: Record<string, unknown> | Record<string, unknown>[];
    }>(),
    {
        eyebrow: 'ErlerAVM',
        noindex: false,
    },
);
</script>

<template>
    <SeoHead
        :title="seoTitle || title"
        :description="seoDescription || description"
        :canonical="canonical"
        :image="image"
        :keywords="keywords"
        :json-ld="jsonLd"
    />
    <Head v-if="noindex">
        <meta name="robots" content="noindex, follow" />
    </Head>

    <main class="min-h-svh bg-background pb-20 text-foreground lg:pb-0">
        <StorefrontHeader />

        <section class="mx-auto max-w-6xl px-4 py-6 md:px-6 md:py-10">
            <div class="mb-6 max-w-3xl space-y-2 md:mb-8 md:space-y-3">
                <p class="text-xs font-medium text-muted-foreground md:text-sm">
                    {{ eyebrow }}
                </p>
                <h1
                    class="text-2xl font-semibold tracking-tight md:text-4xl lg:text-5xl"
                >
                    {{ title }}
                </h1>
                <p
                    class="text-sm leading-relaxed text-muted-foreground md:text-base md:leading-6"
                >
                    {{ description }}
                </p>
            </div>
            <slot />
        </section>
        <StorefrontFooter />
    </main>
</template>
