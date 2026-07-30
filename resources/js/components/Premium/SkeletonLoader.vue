<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    type?: 'text' | 'image' | 'avatar' | 'card';
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    class: '',
});

const baseClasses = 'animate-pulse bg-slate-200 dark:bg-slate-700/50';

const typeClasses = computed(() => {
    switch (props.type) {
        case 'text':
            return 'h-4 rounded';
        case 'image':
            return 'h-48 w-full rounded-xl';
        case 'avatar':
            return 'h-12 w-12 rounded-full';
        case 'card':
            return 'h-64 w-full rounded-2xl';
        default:
            return 'h-4 rounded';
    }
});
</script>

<template>
    <div :class="[baseClasses, typeClasses, props.class]"></div>
</template>
