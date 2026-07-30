<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Check, X, Image as ImageIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import GlassCard from '@/components/Premium/GlassCard.vue';

const props = defineProps<{
    campaigns: any[];
}>();

const isDrawerOpen = ref(false);
const editingCampaignId = ref<string | null>(null);

const form = useForm({
    name: '',
    target_url: '',
    is_active: false,
    image: null as File | null,
});

const openCreateDrawer = () => {
    editingCampaignId.value = null;
    form.reset();
    form.clearErrors();
    isDrawerOpen.value = true;
};

const openEditDrawer = (campaign: any) => {
    editingCampaignId.value = campaign.public_id;
    form.name = campaign.name;
    form.target_url = campaign.target_url || '';
    form.is_active = campaign.is_active;
    form.image = null;
    form.clearErrors();
    isDrawerOpen.value = true;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.image = target.files[0];
    }
};

const submit = () => {
    if (editingCampaignId.value) {
        form.post(`/vitrin/popup/${editingCampaignId.value}`, {
            preserveScroll: true,
            onSuccess: () => closeDrawer(),
        });
    } else {
        form.post('/vitrin/popup', {
            preserveScroll: true,
            onSuccess: () => closeDrawer(),
        });
    }
};

const deleteCampaign = (campaign: any) => {
    if (confirm('Bu pop-up kampanyasını silmek istediğinize emin misiniz?')) {
        router.delete(`/vitrin/popup/${campaign.public_id}`);
    }
};

const toggleActive = (campaign: any) => {
    router.post(`/vitrin/popup/${campaign.public_id}`, {
        _method: 'patch',
        name: campaign.name,
        is_active: !campaign.is_active,
    });
};
</script>

<template>
    <Head title="Pop-up Kampanyalar" />

    <div class="space-y-6 p-6">
        <section>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200"
                    >
                        Pop-up Kampanyalar
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Siteye ilk girişte çıkacak görselli kampanya kutularını
                        yönetin.
                    </p>
                </div>
                <Button @click="openCreateDrawer" class="gap-2">
                    <Plus class="h-4 w-4" />
                    Yeni Ekle
                </Button>
            </div>
        </section>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="campaigns.length === 0"
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 py-16 text-center dark:border-zinc-700"
                >
                    <div
                        class="mb-4 rounded-full bg-primary/10 p-3 text-primary"
                    >
                        <ImageIcon class="h-8 w-8" />
                    </div>
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Henüz Pop-up Eklenmemiş
                    </h3>
                    <p
                        class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400"
                    >
                        Siteye ilk girişte görünecek kampanyalarınızı oluşturmak
                        için "Yeni Ekle" butonunu kullanın.
                    </p>
                    <Button
                        @click="openCreateDrawer"
                        variant="outline"
                        class="mt-6"
                    >
                        İlk Kampanyayı Ekle
                    </Button>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <GlassCard
                        v-for="campaign in campaigns"
                        :key="campaign.id"
                        class="flex flex-col overflow-hidden border border-gray-200 transition-all hover:shadow-md dark:border-zinc-800"
                        :class="{
                            'ring-2 ring-emerald-500': campaign.is_active,
                        }"
                    >
                        <div
                            class="relative aspect-video w-full bg-zinc-100 dark:bg-zinc-900"
                        >
                            <img
                                :src="campaign.image_path"
                                :alt="campaign.name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                class="absolute top-3 right-3 flex items-center gap-2"
                            >
                                <span
                                    v-if="campaign.is_active"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-md"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-white"
                                    ></span>
                                    Aktif
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-full bg-zinc-800/80 px-2.5 py-1 text-xs font-semibold text-zinc-300 shadow-sm backdrop-blur-md"
                                >
                                    Pasif
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-4">
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ campaign.name }}
                                </h3>
                                <p
                                    v-if="campaign.target_url"
                                    class="mt-1 truncate text-xs text-blue-500"
                                >
                                    {{ campaign.target_url }}
                                </p>
                            </div>

                            <div
                                class="mt-4 flex flex-wrap items-center justify-between gap-y-2 border-t border-gray-100 pt-3 dark:border-white/10"
                            >
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="openEditDrawer(campaign)"
                                        class="text-xs font-semibold text-emerald-600 hover:opacity-80 dark:text-emerald-400"
                                    >
                                        Düzenle
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteCampaign(campaign)"
                                        class="text-xs font-semibold text-red-600 hover:opacity-80 dark:text-red-400"
                                    >
                                        Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </GlassCard>
                </div>
            </div>
        </div>

        <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
            <SheetContent class="w-full overflow-y-auto sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>{{
                        editingCampaignId
                            ? 'Kampanyayı Düzenle'
                            : 'Yeni Kampanya Ekle'
                    }}</SheetTitle>
                    <SheetDescription>
                        Siteye ilk girişte açılacak görselli pop-up kampanyası.
                        Not: Sadece 1 adet kampanya aktif olabilir.
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Kampanya Adı</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                required
                                class="bg-background"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="target_url"
                                >Hedef URL (Tıklanınca Gidilecek Link)</Label
                            >
                            <Input
                                id="target_url"
                                v-model="form.target_url"
                                placeholder="/firsatlar"
                                class="bg-background"
                            />
                            <InputError :message="form.errors.target_url" />
                        </div>

                        <div class="space-y-2">
                            <Label for="image"
                                >Görsel (Önerilen: 600x600 veya 800x600
                                px)</Label
                            >
                            <Input
                                id="image"
                                type="file"
                                @change="handleFileChange"
                                accept="image/*"
                                class="cursor-pointer bg-background"
                                :required="!editingCampaignId"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Yeni görsel yüklediğinizde eski görsel otomatik
                                olarak silinir.
                            </p>
                            <InputError :message="form.errors.image" />
                        </div>

                        <div
                            class="flex items-center space-x-2 border-t border-border/50 pt-2"
                        >
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label for="is_active"
                                >Aktif Olarak Yayına Al</Label
                            >
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Aktif yaptığınızda, yayında olan başka bir pop-up
                            varsa otomatik olarak kapatılacaktır.
                        </p>
                    </div>

                    <div
                        class="flex justify-end border-t border-border/50 pt-4"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            class="mr-2"
                            @click="closeDrawer"
                        >
                            İptal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            {{ editingCampaignId ? 'Güncelle' : 'Kaydet' }}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    </div>
</template>
