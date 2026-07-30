<script setup lang="ts">
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    order: {
        public_id: string;
        order_number: string;
        status: string;
        payment_status: string;
        grand_total: string;
        currency: string;
    };
}>();
</script>

<template>
    <StorefrontPage
        :title="`Sipariş Takip: ${order.order_number}`"
        description="Sipariş durumunuzu güvenli public_id ile takip edin."
        eyebrow="Sipariş takip"
        noindex
    >
        <dl class="grid gap-4 rounded-lg border bg-card p-6 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-muted-foreground">Durum</dt>
                <dd class="font-medium">{{ order.status }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted-foreground">Ödeme</dt>
                <dd class="font-medium">{{ order.payment_status }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted-foreground">Toplam</dt>
                <dd class="font-medium">
                    {{ order.grand_total }} {{ order.currency }}
                </dd>
            </div>
        </dl>
    </StorefrontPage>
</template>
