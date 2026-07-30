<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Save } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

type Page = {
    public_id: string;
    title: string;
    slug: string;
    content: string;
    status: string;
    is_active: boolean;
};

const props = defineProps<{
    page: Page;
}>();

const form = useForm({
    title: props.page.title,
    content: props.page.content || '',
    status: props.page.status,
    is_active: props.page.is_active,
});

const activeValue = computed({
    get: () => (form.is_active ? '1' : '0'),
    set: (value: string) => {
        form.is_active = value === '1';
    },
});

function submit() {
    form.patch(`/kurumsal-sayfalar/${props.page.public_id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`${props.page.title} Düzenle`" />

    <div class="mx-auto max-w-5xl space-y-6 p-4">
        <div class="flex items-center gap-4">
            <Button as-child variant="ghost" size="icon" class="rounded-full">
                <Link href="/kurumsal-sayfalar">
                    <ChevronLeft class="h-5 w-5" />
                </Link>
            </Button>
            <div>
                <p class="text-sm font-medium text-primary">Sayfa Düzenle</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ props.page.title }}
                </h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Main Content Area -->
                <div class="space-y-6 md:col-span-2">
                    <div class="rounded-lg border bg-card p-6 shadow-sm">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Sayfa Başlığı</Label>
                                <Input
                                    v-model="form.title"
                                    required
                                    maxlength="255"
                                />
                                <InputError :message="form.errors.title" />
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <Label>İçerik (HTML destekler)</Label>
                                </div>
                                <!-- Simple Textarea instead of rich editor to avoid build complexitiy initially -->
                                <textarea
                                    v-model="form.content"
                                    class="min-h-[400px] w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="<p>Sayfa içeriğini buraya HTML formatında girebilirsiniz...</p>"
                                ></textarea>
                                <p class="text-xs text-muted-foreground">
                                    İçeriği Markdown veya HTML tagları
                                    kullanarak biçimlendirebilirsiniz. Kurumsal
                                    sayfalarda standart typography classları
                                    otomatik uygulanır.
                                </p>
                                <InputError :message="form.errors.content" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Settings Area -->
                <div class="space-y-6">
                    <div
                        class="space-y-4 rounded-lg border bg-card p-6 shadow-sm"
                    >
                        <h3 class="border-b pb-2 font-medium">
                            Yayın Ayarları
                        </h3>

                        <div class="space-y-2">
                            <Label>Durum</Label>
                            <Select v-model="form.status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft"
                                        >Taslak</SelectItem
                                    >
                                    <SelectItem value="published"
                                        >Yayında</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="space-y-2">
                            <Label>Erişilebilirlik (Aktiflik)</Label>
                            <Select v-model="activeValue">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1"
                                        >Aktif (Göster)</SelectItem
                                    >
                                    <SelectItem value="0"
                                        >Pasif (Gizle)</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.is_active" />
                        </div>

                        <div class="space-y-2 pt-4">
                            <Label>URL Yolu (Salt Okunur)</Label>
                            <Input
                                :value="props.page.slug"
                                disabled
                                class="bg-muted"
                            />
                            <p class="text-xs text-muted-foreground">
                                Sistem tarafından belirlenir, değiştirilemez.
                            </p>
                        </div>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <Save class="mr-2 h-4 w-4" />
                        Değişiklikleri Kaydet
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
