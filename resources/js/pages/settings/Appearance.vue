<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Görünüm ayarları',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Görünüm ayarları" />

    <h1 class="sr-only">Görünüm ayarları</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Görünüm ayarları"
            description="Hesabınız için tema tercihini güncelleyin"
        />
        <AppearanceTabs />
    </div>
</template>
