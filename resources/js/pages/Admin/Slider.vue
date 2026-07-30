<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
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
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import GlassCard from '@/components/Premium/GlassCard.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

type Banner = {
    public_id: string;
    placement: 'home_hero' | 'home_strip' | 'partner_logo';
    title: string;
    subtitle?: string | null;
    cta_label?: string | null;
    cta_url?: string | null;
    image_path?: string | null;
    status: 'draft' | 'published';
    sort_order: number;
    starts_at?: string | null;
    ends_at?: string | null;
};

const props = defineProps<{
    banners: Banner[];
}>();

const baseUrl = '/vitrin';
const editingBannerId = ref<string | null>(null);
const isDrawerOpen = ref(false);

const bannerForm = useForm({
    placement: 'home_hero',
    title: '',
    subtitle: '',
    cta_label: '',
    cta_url: '',
    image_path: '' as any,
    status: 'draft',
    sort_order: 0,
    starts_at: '',
    ends_at: '',
});

function isVideoMedia(path?: string | null): boolean {
    return /\.(mp4|webm|ogg)(\?.*)?$/i.test(path ?? '');
}

function openCreateDrawer(): void {
    resetBannerForm();
    isDrawerOpen.value = true;
}

function editBanner(banner: Banner): void {
    editingBannerId.value = banner.public_id;
    bannerForm.placement = banner.placement;
    bannerForm.title = banner.title;
    bannerForm.subtitle = banner.subtitle ?? '';
    bannerForm.cta_label = banner.cta_label ?? '';
    bannerForm.cta_url = banner.cta_url ?? '';
    bannerForm.image_path = null; // Do not fill input file
    bannerForm.status = banner.status;
    bannerForm.sort_order = banner.sort_order;
    bannerForm.starts_at = banner.starts_at ?? '';
    bannerForm.ends_at = banner.ends_at ?? '';
    isDrawerOpen.value = true;
}

function closeDrawer(): void {
    isDrawerOpen.value = false;
    setTimeout(() => {
        resetBannerForm();
    }, 300);
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        bannerForm.image_path = target.files[0];
    } else {
        bannerForm.image_path = null;
    }
}

function submitBanner(): void {
    if (editingBannerId.value) {
        bannerForm
            .transform((data) => ({
                ...data,
                _method: 'PATCH',
            }))
            .post(`${baseUrl}/banner/${editingBannerId.value}`, {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: closeDrawer,
            });
        return;
    }

    bannerForm.post(`${baseUrl}/banner`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: closeDrawer,
    });
}

function resetBannerForm(): void {
    editingBannerId.value = null;
    bannerForm.reset();
    bannerForm.placement = 'home_hero';
    bannerForm.status = 'draft';
    bannerForm.clearErrors();
    const fileInput = document.getElementById(
        'banner-image',
    ) as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function deleteBanner(banner: Banner): void {
    if (!window.confirm('Bu bannerı silmek istediğinize emin misiniz?')) {
        return;
    }

    useForm({}).delete(`${baseUrl}/banner/${banner.public_id}`, {
        preserveScroll: true,
    });
}

function togglePublish(banner: Banner): void {
    const newStatus = banner.status === 'published' ? 'draft' : 'published';
    useForm({ ...banner, status: newStatus }).patch(
        `${baseUrl}/banner/${banner.public_id}`,
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Slider ve Banner Yönetimi" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-600">
                    Vitrin İçerik
                </p>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Slider ve Banner Yönetimi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Ana sayfadaki hero slider ve banner bağlantıları buradan
                    yönetilir.
                </p>
            </div>
            <Button
                @click="openCreateDrawer"
                class="bg-emerald-600 text-white hover:bg-emerald-700"
            >
                <PlusIcon class="mr-2 h-5 w-5" />
                Yeni Banner
            </Button>
        </div>

        <div
            v-if="!props.banners.length"
            class="rounded-xl border bg-white p-12 text-center shadow-sm dark:border-white/10 dark:bg-[#07110f]"
        >
            <PhotoIcon
                class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600"
            />
            <h3
                class="mt-2 text-sm font-semibold text-gray-900 dark:text-white"
            >
                Henüz banner yok
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Müşterilerinize göstermek için ilk bannerınızı ekleyin.
            </p>
            <div class="mt-6">
                <Button
                    @click="openCreateDrawer"
                    class="bg-emerald-600 text-white hover:bg-emerald-700"
                >
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Banner Ekle
                </Button>
            </div>
        </div>

        <div
            v-else
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <GlassCard
                v-for="banner in props.banners"
                :key="banner.public_id"
                class="group flex flex-col overflow-hidden p-0"
            >
                <div
                    class="relative aspect-[16/9] w-full overflow-hidden bg-muted"
                >
                    <video
                        v-if="isVideoMedia(banner.image_path)"
                        :src="banner.image_path || undefined"
                        muted
                        playsinline
                        loop
                        autoplay
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <img
                        v-else-if="banner.image_path"
                        :src="banner.image_path"
                        :alt="banner.title"
                        loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div
                        v-else
                        class="absolute inset-0 flex items-center justify-center text-xs text-muted-foreground"
                    >
                        <PhotoIcon class="size-8 opacity-20" />
                    </div>

                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"
                    ></div>

                    <div class="absolute bottom-3 left-3 flex gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-white/20 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-white/20 backdrop-blur-md ring-inset"
                        >
                            {{ banner.placement }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-1 text-[10px] font-medium ring-1 backdrop-blur-md ring-inset"
                            :class="
                                banner.status === 'published'
                                    ? 'bg-emerald-500/20 text-emerald-100 ring-emerald-500/30'
                                    : 'bg-black/20 text-gray-200 ring-white/10'
                            "
                        >
                            {{
                                banner.status === 'published'
                                    ? 'Yayında'
                                    : 'Taslak'
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col justify-between p-4">
                    <div>
                        <h3
                            class="line-clamp-1 font-semibold text-gray-900 dark:text-white"
                            :title="banner.title"
                        >
                            {{ banner.title }}
                        </h3>
                        <p
                            v-if="banner.subtitle"
                            class="mt-1 line-clamp-2 text-xs text-gray-500"
                        >
                            {{ banner.subtitle }}
                        </p>
                    </div>

                    <div
                        class="mt-4 flex flex-wrap items-center justify-between gap-y-2 border-t border-gray-100 pt-3 dark:border-white/10"
                    >
                        <div class="text-xs text-gray-500">
                            Sıra: <strong>{{ banner.sort_order }}</strong>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="editBanner(banner)"
                                class="text-xs font-semibold text-emerald-600 hover:opacity-80 dark:text-emerald-400"
                            >
                                Düzenle
                            </button>
                            <button
                                type="button"
                                @click="deleteBanner(banner)"
                                class="text-xs font-semibold text-red-600 hover:opacity-80 dark:text-red-400"
                            >
                                Sil
                            </button>
                            <button
                                type="button"
                                @click="togglePublish(banner)"
                                class="flex items-center gap-1 border-l border-gray-200 pl-3 text-xs font-medium transition-colors hover:opacity-80 dark:border-white/10"
                                :class="
                                    banner.status === 'published'
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-emerald-600 dark:text-emerald-400'
                                "
                            >
                                <span v-if="banner.status === 'published'"
                                    >Taslağa Çek</span
                                >
                                <span v-else>Yayına Al</span>
                            </button>
                        </div>
                    </div>
                </div>
            </GlassCard>
        </div>
    </div>

    <!-- Drawer using Sheet -->
    <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetContent class="w-full overflow-y-auto sm:max-w-md">
            <SheetHeader>
                <SheetTitle>{{
                    editingBannerId ? 'Banner Düzenle' : 'Yeni Banner Ekle'
                }}</SheetTitle>
                <SheetDescription>
                    Vitrinde görünecek görsel ve bağlantıları buradan
                    ayarlayabilirsiniz.
                </SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submitBanner" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Yerleşim</Label>
                        <Select v-model="bannerForm.placement">
                            <SelectTrigger class="bg-background"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="home_hero"
                                    >Ana slider</SelectItem
                                >
                                <SelectItem value="home_strip"
                                    >Ara banner</SelectItem
                                >
                                <SelectItem value="partner_logo"
                                    >Marka/Partner Logosu</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="bannerForm.errors.placement" />
                    </div>

                    <div class="space-y-2">
                        <Label for="banner-title">Başlık</Label>
                        <Input
                            id="banner-title"
                            v-model="bannerForm.title"
                            required
                            class="bg-background"
                        />
                        <InputError :message="bannerForm.errors.title" />
                    </div>

                    <div class="space-y-2">
                        <Label for="banner-subtitle"
                            >Alt Açıklama (Opsiyonel)</Label
                        >
                        <Input
                            id="banner-subtitle"
                            v-model="bannerForm.subtitle"
                            class="bg-background"
                        />
                        <InputError :message="bannerForm.errors.subtitle" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Buton Metni</Label>
                            <Input
                                v-model="bannerForm.cta_label"
                                placeholder="Örn: İncele"
                                class="bg-background"
                            />
                            <InputError
                                :message="bannerForm.errors.cta_label"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Buton URL</Label>
                            <Input
                                v-model="bannerForm.cta_url"
                                placeholder="/kategori/..."
                                class="bg-background"
                            />
                            <InputError :message="bannerForm.errors.cta_url" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="banner-image">Görsel / Video Dosyası</Label>
                        <Input
                            id="banner-image"
                            type="file"
                            @change="handleFileChange"
                            accept="image/*,video/mp4,video/webm"
                            class="cursor-pointer bg-background"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Önerilen formatlar: JPG, PNG, WEBP, MP4. (Eğer yeni
                            dosya yüklenmezse eski dosya korunur)
                        </p>
                        <InputError :message="bannerForm.errors.image_path" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Sıra</Label>
                            <Input
                                v-model="bannerForm.sort_order"
                                type="number"
                                min="0"
                                class="bg-background"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Durum</Label>
                            <Select v-model="bannerForm.status">
                                <SelectTrigger class="bg-background"
                                    ><SelectValue
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft"
                                        >Taslak</SelectItem
                                    >
                                    <SelectItem value="published"
                                        >Yayında</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Başlangıç Zamanı</Label>
                            <Input
                                v-model="bannerForm.starts_at"
                                type="datetime-local"
                                class="bg-background"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Bitiş Zamanı</Label>
                            <Input
                                v-model="bannerForm.ends_at"
                                type="datetime-local"
                                class="bg-background"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-border/50 pt-4">
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
                        :disabled="bannerForm.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        {{ editingBannerId ? 'Güncelle' : 'Kaydet' }}
                    </Button>
                </div>
            </form>
        </SheetContent>
    </Sheet>
</template>
