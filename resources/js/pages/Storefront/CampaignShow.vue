<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    campaign: { name: string; description?: string | null };
}>();
</script>

<template>
    <StorefrontPage
        :title="campaign.name"
        :description="
            campaign.description || 'Kampanya detayları ve bağlı ürünler.'
        "
        eyebrow="Kampanya"
    >
        <EmptyState
            title="Kampanya ürün bağlantısı bekliyor"
            description="Kampanya modeli hazır. Ürün-kampanya ilişkisi ve fiyat kuralı bağlanmadan sahte indirim gösterilmez."
        />
    </StorefrontPage>
</template>
