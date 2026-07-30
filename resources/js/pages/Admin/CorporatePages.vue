<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Edit2, ExternalLink } from '@lucide/vue';
import { Button } from '@/components/ui/button';

type PageRow = {
    public_id: string;
    title: string;
    slug: string;
    status: string;
    is_active: boolean;
    url: string;
    edit_url: string;
};

const props = defineProps<{
    pages: PageRow[];
}>();
</script>

<template>
    <Head title="Kurumsal Sayfalar" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Admin operasyon</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Kurumsal Sayfalar
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                Müşterilerinize sunduğunuz sözleşmeler, kullanım koşulları ve
                iade politikaları gibi yasal ve kurumsal sayfaları buradan
                yönetebilirsiniz.
            </p>
        </section>

        <section class="overflow-hidden rounded-lg border bg-card">
            <div class="flex items-center gap-2 border-b p-4">
                <FileText class="size-5 text-primary" />
                <h2 class="font-semibold">Sayfalar</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-220 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Başlık</th>
                            <th class="px-4 py-3 font-medium">URL Yolu</th>
                            <th class="px-4 py-3 font-medium">Yayın Durumu</th>
                            <th class="px-4 py-3 font-medium">Aktif</th>
                            <th class="px-4 py-3 text-right font-medium">
                                İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="page in props.pages"
                            :key="page.public_id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-semibold">
                                {{ page.title }}
                            </td>
                            <td
                                class="px-4 py-3 font-mono text-xs text-muted-foreground"
                            >
                                /legal/{{ page.slug }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        page.status === 'published'
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                                    "
                                >
                                    {{
                                        page.status === 'published'
                                            ? 'Yayında'
                                            : 'Taslak'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        page.is_active
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                    "
                                >
                                    {{ page.is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        title="Sitede Gör"
                                    >
                                        <a
                                            :href="page.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <ExternalLink class="h-4 w-4" />
                                        </a>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Link :href="page.edit_url">
                                            <Edit2 class="mr-2 h-4 w-4" />
                                            Düzenle
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
