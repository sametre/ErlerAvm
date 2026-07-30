<script setup lang="ts">
import { AlertTriangle, Ban, Info, ShieldAlert } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { commerceIssues } from '@/lib/commerceIssues';
import type { CommerceIssueCode } from '@/lib/commerceIssues';

const props = withDefaults(
    defineProps<{
        code: CommerceIssueCode;
        title?: string;
        description?: string;
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);

const emit = defineEmits<{
    primary: [];
    secondary: [];
}>();

const issue = computed(() => commerceIssues[props.code]);
const title = computed(() => props.title ?? issue.value.title);
const description = computed(
    () => props.description ?? issue.value.description,
);

const severityLabel = computed(() => {
    switch (issue.value.severity) {
        case 'critical':
            return 'Kritik';
        case 'error':
            return 'Hata';
        case 'warning':
            return 'Uyarı';
        default:
            return 'Bilgi';
    }
});

const wrapperClass = computed(() => {
    switch (issue.value.severity) {
        case 'critical':
            return 'border-destructive/40 bg-destructive/5';
        case 'error':
            return 'border-destructive/30 bg-destructive/5';
        case 'warning':
            return 'border-amber-300 bg-amber-50 text-amber-950 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-100';
        default:
            return 'border-blue-200 bg-blue-50 text-blue-950 dark:border-blue-900/50 dark:bg-blue-950/20 dark:text-blue-100';
    }
});

const iconClass = computed(() => {
    switch (issue.value.severity) {
        case 'critical':
        case 'error':
            return 'text-destructive';
        case 'warning':
            return 'text-amber-600 dark:text-amber-400';
        default:
            return 'text-blue-600 dark:text-blue-400';
    }
});
</script>

<template>
    <section
        class="rounded-lg border p-5"
        :class="[wrapperClass, compact ? 'space-y-4' : 'space-y-5']"
    >
        <div class="flex items-start gap-4">
            <div
                class="flex size-10 shrink-0 items-center justify-center rounded-md bg-background/80"
            >
                <ShieldAlert
                    v-if="issue.severity === 'critical'"
                    class="size-5"
                    :class="iconClass"
                />
                <Ban
                    v-else-if="issue.severity === 'error'"
                    class="size-5"
                    :class="iconClass"
                />
                <AlertTriangle
                    v-else-if="issue.severity === 'warning'"
                    class="size-5"
                    :class="iconClass"
                />
                <Info v-else class="size-5" :class="iconClass" />
            </div>

            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-semibold">{{ title }}</h2>
                    <Badge variant="outline">{{ severityLabel }}</Badge>
                </div>
                <p class="text-sm leading-6 text-muted-foreground">
                    {{ description }}
                </p>
                <p
                    v-if="issue.supportRequired"
                    class="text-xs font-medium text-destructive"
                >
                    Bu durum loglanmalı ve destek ekibi tarafından takip
                    edilmelidir.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <Button type="button" size="sm" @click="emit('primary')">
                {{ issue.primaryActionLabel }}
            </Button>
            <Button
                v-if="issue.secondaryActionLabel"
                type="button"
                size="sm"
                variant="outline"
                @click="emit('secondary')"
            >
                {{ issue.secondaryActionLabel }}
            </Button>
        </div>
    </section>
</template>
