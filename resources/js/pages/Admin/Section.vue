<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';

type Metric = {
    label: string;
    value: number;
};

const props = defineProps<{
    title: string;
    description: string;
    metrics: Metric[];
    table: {
        columns: string[];
        rows: string[][];
        notice: string;
    };
}>();
</script>

<template>
    <Head :title="props.title" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Admin operasyon</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ props.title }}
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                {{ props.description }}
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <section class="rounded-lg border bg-amber-50 p-4 text-amber-900">
            <div class="flex gap-3">
                <AlertTriangle class="mt-0.5 size-5 shrink-0" />
                <p class="text-sm leading-6">{{ props.table.notice }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-190 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th
                                v-for="column in props.table.columns"
                                :key="column"
                                class="px-4 py-3 font-medium"
                            >
                                {{ column }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(row, rowIndex) in props.table.rows"
                            :key="rowIndex"
                        >
                            <td
                                v-for="(cell, cellIndex) in row"
                                :key="`${rowIndex}-${cellIndex}`"
                                class="px-4 py-3"
                            >
                                {{ cell }}
                            </td>
                        </tr>
                        <tr v-if="!props.table.rows.length">
                            <td
                                :colspan="props.table.columns.length"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                Bu bölüm için henüz gerçek kayıt yok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
