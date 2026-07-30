<script setup lang="ts">
import StorefrontPage from '@/components/StorefrontPage.vue';

defineProps<{
    post: {
        title: string;
        excerpt?: string | null;
        content_html?: string | null;
        cover_image_path?: string | null;
        seo_title?: string | null;
        seo_description?: string | null;
        published_at?: string | null;
    };
}>();
</script>

<template>
    <StorefrontPage
        :title="post.title"
        :description="post.excerpt || 'ErlerAVM blog yazısı.'"
        eyebrow="Blog"
    >
        <img
            v-if="post.cover_image_path"
            :src="post.cover_image_path"
            :alt="post.title"
            class="mb-6 aspect-[16/7] w-full rounded-lg object-cover"
        />
        <article class="prose dark:prose-invert max-w-none">
            <div v-if="post.content_html" v-html="post.content_html" />
            <p
                v-else
                class="text-sm leading-7 whitespace-pre-line text-muted-foreground"
            >
                Blog içeriği henüz girilmedi.
            </p>
        </article>
    </StorefrontPage>
</template>
