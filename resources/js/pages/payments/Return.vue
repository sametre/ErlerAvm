<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock3, XCircle } from '@lucide/vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { home } from '@/routes';

const props = defineProps<{
    status: 'pending-confirmation' | 'failed';
    title: string;
    message: string;
}>();
</script>

<template>
    <Head :title="title" />

    <main class="flex min-h-svh items-center justify-center bg-background p-6">
        <section class="w-full max-w-lg space-y-8 text-center">
            <BrandLogo class="mx-auto h-16" />

            <div class="space-y-4 rounded-lg border bg-card p-8 shadow-sm">
                <div class="flex justify-center">
                    <Clock3
                        v-if="props.status === 'pending-confirmation'"
                        class="size-12 text-amber-500"
                    />
                    <XCircle v-else class="size-12 text-destructive" />
                    <CheckCircle2 class="hidden" />
                </div>
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold">{{ title }}</h1>
                    <p class="text-sm leading-6 text-muted-foreground">
                        {{ message }}
                    </p>
                </div>
                <Link
                    :href="home()"
                    class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                >
                    Ana sayfaya dön
                </Link>
            </div>
        </section>
    </main>
</template>
