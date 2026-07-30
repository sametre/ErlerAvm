<script setup lang="ts">
import ProductList from '@/components/ProductList.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    brand: { name: string; slug: string; description?: string | null };
    products?: unknown;
}>();
</script>

<template>
    <StorefrontPage
        :title="brand.name"
        :description="brand.description || 'Markaya ait yayınlanmış ürünler.'"
        eyebrow="Marka"
    >
        <ProductList :products="products as never" />
    </StorefrontPage>
</template>
