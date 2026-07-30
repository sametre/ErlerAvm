<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    compact?: boolean;
}>();

const page = usePage();
const logoUrl = computed(
    () => (page.props as any).storeSettings?.logo_url || '/erleravm.png',
);
const storeName = computed(
    () => (page.props as any).storeSettings?.store_name || 'ErlerAVM',
);
</script>

<template>
    <img
        :src="logoUrl"
        :alt="storeName"
        :class="
            cn(
                compact
                    ? 'h-10 w-10 rounded-md object-contain'
                    : 'h-12 w-auto object-contain',
                props.class,
            )
        "
    />
</template>
