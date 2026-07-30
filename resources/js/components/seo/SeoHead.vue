<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type JsonLd = Record<string, unknown> | Record<string, unknown>[];

const props = defineProps<{
    title?: string;
    description?: string;
    canonical?: string;
    image?: string;
    keywords?: string | null;
    robots?: string;
    jsonLd?: JsonLd;
}>();

const page = usePage();

const sharedSeo = computed(() => page.props.seo);
const title = computed(() => props.title ?? sharedSeo.value.defaultTitle);
const description = computed(
    () => props.description ?? sharedSeo.value.defaultDescription,
);
const canonical = computed(
    () => props.canonical ?? sharedSeo.value.canonicalUrl,
);
const image = computed(() => {
    const value = props.image ?? sharedSeo.value.logoUrl;

    try {
        return new URL(value, canonical.value).toString();
    } catch {
        return value;
    }
});
const robots = computed(
    () => props.robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
);
const jsonLdContent = computed(() =>
    props.jsonLd ? JSON.stringify(props.jsonLd).replace(/</g, '\\u003C') : null,
);
</script>

<template>
    <Head :title="title">
        <meta
            head-key="description"
            name="description"
            :content="description"
        />
        <meta head-key="robots" name="robots" :content="robots" />
        <meta head-key="googlebot" name="googlebot" :content="robots" />
        <meta head-key="bingbot" name="bingbot" :content="robots" />
        <meta head-key="yandex" name="yandex" :content="robots" />
        <meta v-if="keywords" head-key="keywords" name="keywords" :content="keywords" />
        <link head-key="canonical" rel="canonical" :href="canonical" />
        <meta
            head-key="og:site_name"
            property="og:site_name"
            :content="sharedSeo.siteName"
        />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta
            head-key="og:description"
            property="og:description"
            :content="description"
        />
        <meta head-key="og:url" property="og:url" :content="canonical" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:locale" property="og:locale" content="tr_TR" />
        <meta head-key="og:image" property="og:image" :content="image" />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            content="summary_large_image"
        />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta head-key="twitter:description" name="twitter:description" :content="description" />
        <meta head-key="twitter:image" name="twitter:image" :content="image" />
        <component
            :is="'script'"
            v-if="jsonLdContent"
            head-key="json-ld"
            type="application/ld+json"
            v-html="jsonLdContent"
        />
    </Head>
</template>
