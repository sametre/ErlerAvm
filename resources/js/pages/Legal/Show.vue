<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    title: string;
    slug: string;
    description: string;
    content_html?: string | null;
    sections: {
        heading: string;
        body: string;
    }[];
}>();
</script>

<template>
    <StorefrontPage
        :title="title"
        :description="description"
        eyebrow="Kurumsal"
    >
        <article
            v-if="content_html"
            class="prose dark:prose-invert max-w-none rounded-lg border bg-card p-6"
            v-html="content_html"
        />
        <div v-if="sections.length" class="grid gap-5">
            <article
                v-for="section in sections"
                :key="section.heading"
                class="rounded-lg border bg-card p-6"
            >
                <h2 class="text-xl font-semibold">{{ section.heading }}</h2>
                <p class="mt-3 leading-7 text-muted-foreground">
                    {{ section.body }}
                </p>
            </article>
        </div>
        <EmptyState
            v-else-if="!content_html"
            title="İçerik onayı bekleniyor"
            description="Bu sayfa yayında kalır; nihai metin şirket bilgileri ve yasal onay tamamlandıktan sonra yönetim panelinden güncellenmelidir."
        />
    </StorefrontPage>
</template>
