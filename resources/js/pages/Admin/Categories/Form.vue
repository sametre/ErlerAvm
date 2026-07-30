<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save, UploadCloud } from '@lucide/vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref } from 'vue';

const props = defineProps<{
    category: any;
    parentCategories: any[];
}>();

const form = useForm({
    name: props.category.name || '',
    parent_id: props.category.parent_id || null,
    description: props.category.description || '',
    status: props.category.status || 'published',
    sort_order: props.category.sort_order || 0,
    image: null as File | null,
});

const imagePreview = ref(props.category.image_url || null);

const handleImageChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.image = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(input.files[0]);
    }
};

const submit = () => {
    if (props.category.public_id) {
        // Need to use POST with _method=PUT for file uploads in Inertia
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/categories/${props.category.public_id}`);
    } else {
        form.post('/categories');
    }
};
</script>

<template>
    <Head :title="category.id ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'" />

    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/categories">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{
                        category.id ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'
                    }}
                </h1>
            </div>
        </div>
        <Button @click="submit" :disabled="form.processing">
            <Save class="mr-2 h-4 w-4" />
            Kaydet
        </Button>
    </div>

    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        <div class="space-y-6 md:col-span-2">
            <div
                class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="space-y-2">
                    <Label for="name"
                        >Kategori Adı <span class="text-red-500">*</span></Label
                    >
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Örn: Televizyon"
                    />
                    <p class="text-sm text-red-500" v-if="form.errors.name">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Açıklama</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        placeholder="Kategori açıklaması..."
                    />
                    <p
                        class="text-sm text-red-500"
                        v-if="form.errors.description"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <Label>Durum</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="published"
                                    >Yayında</SelectItem
                                >
                                <SelectItem value="draft">Taslak</SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            class="text-sm text-red-500"
                            v-if="form.errors.status"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="sort_order"
                            >Sıralama (0 ilk gösterilir)</Label
                        >
                        <Input
                            id="sort_order"
                            type="number"
                            v-model="form.sort_order"
                            min="0"
                        />
                        <p
                            class="text-sm text-red-500"
                            v-if="form.errors.sort_order"
                        >
                            {{ form.errors.sort_order }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="space-y-2">
                    <Label>Kategori Görseli (İsteğe Bağlı)</Label>

                    <div
                        class="group relative cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-slate-300 p-4 text-center transition-colors hover:bg-slate-50"
                        :class="{ 'bg-slate-50': imagePreview }"
                    >
                        <input
                            type="file"
                            accept="image/*"
                            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            @change="handleImageChange"
                        />

                        <template v-if="imagePreview">
                            <img
                                :src="imagePreview"
                                class="h-48 w-full rounded-lg object-cover"
                            />
                            <div
                                class="absolute inset-0 z-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <span
                                    class="flex items-center gap-2 font-medium text-white"
                                >
                                    <UploadCloud class="h-5 w-5" /> Resmi
                                    Değiştir
                                </span>
                            </div>
                        </template>
                        <template v-else>
                            <div
                                class="flex flex-col items-center justify-center py-8 text-slate-500"
                            >
                                <div
                                    class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100"
                                >
                                    <UploadCloud
                                        class="h-6 w-6 text-slate-400"
                                    />
                                </div>
                                <span class="font-medium text-slate-700"
                                    >Görsel Yükle</span
                                >
                                <span class="mt-1 text-xs"
                                    >Sürükle bırak veya seç (Max 5MB)</span
                                >
                            </div>
                        </template>
                    </div>
                    <p class="text-sm text-red-500" v-if="form.errors.image">
                        {{ form.errors.image }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label>Üst Kategori</Label>
                    <Select
                        :model-value="form.parent_id?.toString() || 'null'"
                        @update:model-value="
                            (v) =>
                                (form.parent_id =
                                    v === 'null' ? null : parseInt(v))
                        "
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Ana Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="null"
                                >-- Ana Kategori (Üst Kategori Yok)
                                --</SelectItem
                            >
                            <SelectItem
                                v-for="parent in parentCategories"
                                :key="parent.id"
                                :value="parent.id.toString()"
                            >
                                {{ parent.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="mt-1 text-xs text-slate-500">
                        Eğer bir alt kategori ise, hangi kategorinin altında
                        olduğunu seçin.
                    </p>
                    <p
                        class="text-sm text-red-500"
                        v-if="form.errors.parent_id"
                    >
                        {{ form.errors.parent_id }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
