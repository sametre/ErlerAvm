<script setup lang="ts">
import ProductList from '@/components/ProductList.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    category: { name: string; slug: string; description?: string | null };
    products?: unknown;
}>();
</script>

<template>
    <StorefrontPage
        :title="category.name"
        :description="
            category.description || 'Kategori ürünleri ve filtreleme alanı.'
        "
        eyebrow="Kategori"
    >
        <ProductList :products="products as never" />
    </StorefrontPage>
</template>
