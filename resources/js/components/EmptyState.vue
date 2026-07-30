<script setup lang="ts">
import { AlertCircle } from '@lucide/vue';

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="rounded-lg border bg-card p-8 text-center shadow-sm">
        <AlertCircle class="mx-auto mb-4 size-10 text-muted-foreground" />
        <h2 class="text-lg font-semibold">{{ title }}</h2>
        <p
            class="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground"
        >
            {{ description }}
        </p>
    </div>
</template>
