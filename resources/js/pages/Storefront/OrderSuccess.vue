<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';
</script>

<template>
    <StorefrontPage
        title="Sipariş Başarılı"
        description="Sipariş numarası ve ödeme durumu gerçek order kaydıyla gösterilecek."
        eyebrow="Sipariş"
        noindex
    >
        <EmptyState
            title="Sipariş bilgisi bekleniyor"
            description="Bu sayfa gerçek sipariş kaydı oluşturulduktan sonra sipariş numarası, kargo ve fatura bilgilerini gösterecek."
        />
    </StorefrontPage>
</template>
