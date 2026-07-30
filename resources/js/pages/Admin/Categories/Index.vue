<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Trash2, Image as ImageIcon } from '@lucide/vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

defineProps<{
    categories: any[];
}>();

const deleteCategory = (category: any) => {
    if (confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) {
        router.delete(`/categories/${category.public_id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Kategori Yönetimi" />

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                Kategori Yönetimi
            </h1>
            <p class="mt-1 text-slate-500">
                Mağazanızın kategori ve menü yapısını yönetin.
            </p>
        </div>
        <Button as-child>
            <Link href="/categories/create">
                <Plus class="mr-2 h-4 w-4" />
                Yeni Kategori Ekle
            </Link>
        </Button>
    </div>

    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        <Table>
            <TableHeader>
                <TableRow class="bg-slate-50/50 hover:bg-slate-50/50">
                    <TableHead class="w-24">Görsel</TableHead>
                    <TableHead>Kategori Adı</TableHead>
                    <TableHead>Üst Kategori</TableHead>
                    <TableHead>Durum</TableHead>
                    <TableHead class="text-right">Sıra</TableHead>
                    <TableHead class="text-right">İşlemler</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="category in categories" :key="category.id">
                    <TableCell>
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-100"
                        >
                            <img
                                v-if="category.image_url"
                                :src="category.image_url"
                                class="h-full w-full object-cover"
                            />
                            <ImageIcon v-else class="h-5 w-5 text-slate-400" />
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="font-medium text-slate-900">
                            {{ category.name }}
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            {{ category.slug }}
                        </div>
                    </TableCell>
                    <TableCell>
                        <span v-if="category.parent" class="text-slate-600">{{
                            category.parent.name
                        }}</span>
                        <span v-else class="text-slate-400 italic"
                            >Ana Kategori</span
                        >
                    </TableCell>
                    <TableCell>
                        <Badge
                            :variant="
                                category.status === 'published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{
                                category.status === 'published'
                                    ? 'Yayında'
                                    : 'Taslak'
                            }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right text-slate-600">
                        {{ category.sort_order }}
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Button variant="ghost" size="icon" as-child>
                                <Link
                                    :href="`/categories/${category.public_id}/edit`"
                                >
                                    <Edit class="h-4 w-4 text-slate-500" />
                                </Link>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="deleteCategory(category)"
                            >
                                <Trash2 class="h-4 w-4 text-red-500" />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="!categories.length">
                    <TableCell
                        colspan="6"
                        class="h-32 text-center text-slate-500"
                    >
                        Henüz hiç kategori eklenmemiş.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
