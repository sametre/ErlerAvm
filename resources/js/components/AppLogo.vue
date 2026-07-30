<script setup lang="ts">
import BrandLogo from '@/components/brand/BrandLogo.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-white ring-1 ring-sidebar-border"
    >
        <BrandLogo compact class="size-7" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >ErlerAVM</span
        >
    </div>
</template>
