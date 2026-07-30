<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Search } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Metric = { label: string; value: number };

type BlogPostRow = {
    public_id: string;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    excerpt?: string | null;
    cover_image_path?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    published_at?: string | null;
    updated_at?: string | null;
    url: string;
    edit_url: string;
};

const props = defineProps<{
    filters: { q: string };
    metrics: Metric[];
    posts: {
        data: BlogPostRow[];
        links?: { url: string | null; label: string; active: boolean }[];
    };
}>();

const searchQuery = ref(props.filters.q || '');
const isCreateOpen = ref(false);
const createForm = useForm({
    title: '',
    slug: '',
    status: 'draft',
    excerpt: '',
    content: '',
    seo_title: '',
    seo_description: '',
    cover_image_path: '',
});

function applySearch(): void {
    router.get(
        '/sayfalar',
        { q: searchQuery.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function createPost(): void {
    createForm.post('/sayfalar', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
}
</script>

<template>
    <Head title="Blog ve Sayfalar" />

    <div class="space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-primary">İçerik yönetimi</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Blog ve içerik sayfaları
                </h1>
                <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                    Blog kayıtları gerçek blog_posts tablosundan okunur. Medya,
                    SEO ve yayın onayı güvenli form üzerinden kaydedilir.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form
                    class="relative w-full sm:w-80"
                    @submit.prevent="applySearch"
                >
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Başlık veya slug ara..."
                        class="pl-9"
                    />
                </form>
                <Button type="button" @click="isCreateOpen = true">
                    <Plus class="mr-2 size-4" />
                    Yeni yazı
                </Button>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border bg-card">
            <div class="flex items-center gap-2 border-b p-4">
                <FileText class="size-5 text-primary" />
                <h2 class="font-semibold">İçerik listesi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-220 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Başlık</th>
                            <th class="px-4 py-3 font-medium">Slug</th>
                            <th class="px-4 py-3 font-medium">Durum</th>
                            <th class="px-4 py-3 font-medium">Yayın tarihi</th>
                            <th class="px-4 py-3 font-medium">SEO</th>
                            <th class="px-4 py-3 text-right font-medium">
                                İşlem
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="post in props.posts.data"
                            :key="post.public_id"
                            class="hover:bg-muted/50"
                        >
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ post.title }}</p>
                                <p
                                    class="mt-1 line-clamp-1 text-xs text-muted-foreground"
                                >
                                    {{ post.excerpt || '-' }}
                                </p>
                            </td>
                            <td
                                class="px-4 py-3 font-mono text-xs text-muted-foreground"
                            >
                                {{ post.slug }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        post.status === 'published'
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-slate-100 text-slate-700'
                                    "
                                >
                                    {{
                                        post.status === 'published'
                                            ? 'Yayında'
                                            : 'Taslak'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ post.published_at || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-md bg-muted px-2 py-1 text-xs"
                                >
                                    {{
                                        post.seo_title && post.seo_description
                                            ? 'Tamam'
                                            : 'Eksik'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Link :href="post.edit_url"
                                            >Düzenle</Link
                                        >
                                    </Button>
                                    <Button
                                        v-if="post.status === 'published'"
                                        as-child
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <a
                                            :href="post.url"
                                            target="_blank"
                                            rel="noreferrer"
                                            >Sitede gör</a
                                        >
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.posts.data.length">
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Henüz blog kaydı yok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div
        v-if="isCreateOpen"
        class="fixed inset-0 z-50 bg-black/30"
        @click.self="isCreateOpen = false"
    >
        <aside
            class="ml-auto flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-[#07110f]"
        >
            <header class="border-b p-5">
                <p class="text-sm font-medium text-primary">Yeni içerik</p>
                <h2 class="text-xl font-semibold">Blog yazısı oluştur</h2>
            </header>
            <form class="flex flex-1 flex-col" @submit.prevent="createPost">
                <div class="flex-1 space-y-5 overflow-y-auto p-5">
                    <div class="grid gap-2">
                        <Label>Başlık</Label>
                        <Input
                            v-model="createForm.title"
                            required
                            maxlength="255"
                        />
                        <p
                            v-if="createForm.errors.title"
                            class="text-sm text-destructive"
                        >
                            {{ createForm.errors.title }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label>Özet</Label>
                        <textarea
                            v-model="createForm.excerpt"
                            maxlength="700"
                            class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>SEO başlık</Label>
                        <Input v-model="createForm.seo_title" maxlength="180" />
                    </div>
                    <div class="grid gap-2">
                        <Label>SEO açıklama</Label>
                        <textarea
                            v-model="createForm.seo_description"
                            maxlength="320"
                            class="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                        />
                    </div>
                </div>
                <footer class="grid gap-3 border-t p-5 sm:grid-cols-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isCreateOpen = false"
                        >Vazgeç</Button
                    >
                    <Button type="submit" :disabled="createForm.processing"
                        >Oluştur</Button
                    >
                </footer>
            </form>
        </aside>
    </div>
</template>
