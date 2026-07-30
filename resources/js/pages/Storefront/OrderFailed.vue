<script setup lang="ts">
import CommerceIssueState from '@/components/CommerceIssueState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';
</script>

<template>
    <StorefrontPage
        title="Sipariş veya Ödeme Başarısız"
        description="Kart reddi, ödeme iptali veya sağlayıcı hataları burada güvenli şekilde gösterilir."
        eyebrow="Ödeme"
        noindex
    >
        <CommerceIssueState code="payment_failed" />
    </StorefrontPage>
</template>
