<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    hoverEffect?: boolean;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    hoverEffect: true,
    class: '',
});

const cardClasses = computed(() => [
    'relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-md shadow-lg transition-all duration-300 dark:bg-black/20 dark:border-white/10',
    props.hoverEffect
        ? 'hover:-translate-y-1 hover:shadow-xl hover:bg-white/20 dark:hover:bg-white/5'
        : '',
    props.class,
]);
</script>

<template>
    <div :class="cardClasses">
        <div class="relative z-10">
            <slot />
        </div>
        <!-- Optional animated gradient background element for extra premium feel -->
        <div
            class="pointer-events-none absolute -inset-px rounded-2xl bg-gradient-to-br from-white/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
        ></div>
    </div>
</template>
