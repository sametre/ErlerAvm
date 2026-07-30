<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    implementationStatus: string;
    corporateContent?: string | null;
}>();
</script>

<template>
    <StorefrontPage
        title="İletişim ve Satıcı Bilgileri"
        description="Unvan, adres, telefon, e-posta, KEP, MERSİS ve vergi bilgileri burada yayınlanmalı."
        eyebrow="Kurumsal"
    >
        <div
            v-if="corporateContent"
            class="prose dark:prose-invert mb-12 max-w-none rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm md:p-10 dark:border-zinc-800 dark:bg-zinc-900"
            v-html="corporateContent"
        ></div>
        <EmptyState
            v-if="!corporateContent"
            title="Satıcı bilgileri bekleniyor"
            description="Gerçek ticari unvan, adres, vergi dairesi/no, MERSİS ve KEP bilgileri doğrulanmadan sahte bilgi gösterilmez."
        />
    </StorefrontPage>
</template>
