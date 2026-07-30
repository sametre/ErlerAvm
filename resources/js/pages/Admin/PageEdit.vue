<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BlogPost = {
    public_id: string;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    excerpt?: string | null;
    content?: string | null;
    cover_image_path?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    published_at?: string | null;
};

const props = defineProps<{ post: BlogPost }>();

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    status: props.post.status,
    excerpt: props.post.excerpt || '',
    content: props.post.content || '',
    cover_image_path: props.post.cover_image_path || '',
    cover_image: null as File | null,
    seo_title: props.post.seo_title || '',
    seo_description: props.post.seo_description || '',
});

function setCoverImage(event: Event): void {
    const input = event.target as HTMLInputElement;
    form.cover_image = input.files?.[0] ?? null;
}

function submit(): void {
    form.patch(`/sayfalar/${props.post.public_id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <Head :title="`${props.post.title} Düzenle`" />

    <div class="mx-auto max-w-6xl space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <Button as-child variant="ghost" size="sm" class="mb-3 px-0">
                    <Link href="/sayfalar">
                        <ArrowLeft class="mr-2 size-4" />
                        İçeriklere dön
                    </Link>
                </Button>
                <p class="text-sm font-medium text-primary">Blog editörü</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ props.post.title }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Yayına alma işlemi bu güvenli form üzerinden yapılır ve
                    onaylayan yönetici kaydedilir.
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4 text-sm">
                <p class="text-muted-foreground">Yayın durumu</p>
                <p class="mt-1 font-semibold">
                    {{
                        props.post.status === 'published' ? 'Yayında' : 'Taslak'
                    }}
                </p>
            </div>
        </section>

        <form
            class="grid gap-6 lg:grid-cols-[1fr_360px]"
            @submit.prevent="submit"
        >
            <section class="space-y-6">
                <div class="rounded-lg border bg-card p-5">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label>Başlık</Label>
                            <Input
                                v-model="form.title"
                                required
                                maxlength="255"
                            />
                            <p
                                v-if="form.errors.title"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Özet</Label>
                            <textarea
                                v-model="form.excerpt"
                                maxlength="700"
                                class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>İçerik</Label>
                            <textarea
                                v-model="form.content"
                                maxlength="100000"
                                class="min-h-[520px] rounded-md border bg-background px-3 py-2 font-mono text-sm"
                                placeholder="<p>İçerik...</p>"
                            />
                            <p class="text-xs text-muted-foreground">
                                Güvenlik için script ve inline event
                                attribute'ları backend tarafında temizlenir.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Yayın</h2>
                    <div class="mt-4 grid gap-4">
                        <div class="grid gap-2">
                            <Label>Durum</Label>
                            <select
                                v-model="form.status"
                                class="h-10 rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="draft">Taslak</option>
                                <option value="published">Yayında</option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>Slug</Label>
                            <Input v-model="form.slug" maxlength="255" />
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Medya</h2>
                    <div class="mt-4 grid gap-4">
                        <img
                            v-if="form.cover_image_path"
                            :src="form.cover_image_path"
                            alt=""
                            class="aspect-video w-full rounded-md border object-cover"
                        />
                        <div class="grid gap-2">
                            <Label>Kapak görsel yolu</Label>
                            <Input
                                v-model="form.cover_image_path"
                                maxlength="500"
                                placeholder="/storage/blog/yazi.jpg"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Kapak görsel yükle</Label>
                            <input
                                type="file"
                                accept="image/*"
                                class="text-sm"
                                @change="setCoverImage"
                            />
                            <p
                                v-if="form.errors.cover_image"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.cover_image }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">SEO</h2>
                    <div class="mt-4 grid gap-4">
                        <div class="grid gap-2">
                            <Label>SEO başlık</Label>
                            <Input v-model="form.seo_title" maxlength="180" />
                        </div>
                        <div class="grid gap-2">
                            <Label>SEO açıklama</Label>
                            <textarea
                                v-model="form.seo_description"
                                maxlength="320"
                                class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </div>
                    </div>
                </section>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <Save class="mr-2 size-4" />
                    Kaydet
                </Button>
            </aside>
        </form>
    </div>
</template>
