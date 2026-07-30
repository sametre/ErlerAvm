<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    Bars3Icon,
    PencilSquareIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    GlobeAltIcon,
    Bars3BottomLeftIcon,
    RectangleStackIcon,
} from '@heroicons/vue/24/outline';

type NavigationItem = {
    public_id: string;
    location: 'top' | 'mega' | 'footer';
    title: string;
    url: string;
    status: 'draft' | 'published';
    sort_order: number;
};

const props = defineProps<{
    navigationItems: NavigationItem[];
}>();

const baseUrl = '/vitrin';
const editingNavigationId = ref<string | null>(null);
const isDrawerOpen = ref(false);

const navigationForm = useForm({
    location: 'mega',
    title: '',
    url: '/',
    status: 'draft',
    sort_order: 0,
});

const groupedItems = computed(() => {
    return {
        top: props.navigationItems
            .filter((i) => i.location === 'top')
            .sort((a, b) => a.sort_order - b.sort_order),
        mega: props.navigationItems
            .filter((i) => i.location === 'mega')
            .sort((a, b) => a.sort_order - b.sort_order),
        footer: props.navigationItems
            .filter((i) => i.location === 'footer')
            .sort((a, b) => a.sort_order - b.sort_order),
    };
});

function openCreateDrawer(): void {
    resetNavigationForm();
    isDrawerOpen.value = true;
}

function openEditDrawer(item: NavigationItem): void {
    editingNavigationId.value = item.public_id;
    navigationForm.location = item.location;
    navigationForm.title = item.title;
    navigationForm.url = item.url;
    navigationForm.status = item.status;
    navigationForm.sort_order = item.sort_order;
    isDrawerOpen.value = true;
}

function closeDrawer(): void {
    isDrawerOpen.value = false;
    setTimeout(() => {
        resetNavigationForm();
    }, 300);
}

function submitNavigation(): void {
    if (editingNavigationId.value) {
        navigationForm.patch(`${baseUrl}/menu/${editingNavigationId.value}`, {
            preserveScroll: true,
            onSuccess: () => closeDrawer(),
        });
        return;
    }

    navigationForm.post(`${baseUrl}/menu`, {
        preserveScroll: true,
        onSuccess: () => closeDrawer(),
    });
}

function resetNavigationForm(): void {
    editingNavigationId.value = null;
    navigationForm.reset('title', 'url', 'sort_order', 'status');
    navigationForm.location = 'mega';
    navigationForm.clearErrors();
}

function deleteNavigation(item: NavigationItem): void {
    if (
        !window.confirm(
            'Bu menü bağlantısını silmek istediğinize emin misiniz?',
        )
    ) {
        return;
    }

    useForm({}).delete(`${baseUrl}/menu/${item.public_id}`, {
        preserveScroll: true,
    });
}

function togglePublish(item: NavigationItem): void {
    const newStatus = item.status === 'published' ? 'draft' : 'published';
    useForm({ ...item, status: newStatus }).patch(
        `${baseUrl}/menu/${item.public_id}`,
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Menü Yönetimi" />

    <div class="space-y-8 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-600">
                    Vitrin İçerik
                </p>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Menü Yönetimi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Sitenizin navigasyon bağlantılarını menü konumlarına göre
                    buradan yönetebilirsiniz.
                </p>
            </div>
            <Button
                @click="openCreateDrawer"
                class="bg-emerald-600 text-white shadow-md hover:bg-emerald-700"
            >
                <Bars3Icon class="mr-2 h-5 w-5" />
                Yeni Bağlantı
            </Button>
        </div>

        <div
            v-if="!props.navigationItems.length"
            class="rounded-xl border bg-white p-12 text-center shadow-sm dark:border-white/10 dark:bg-[#07110f]"
        >
            <GlobeAltIcon
                class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600"
            />
            <h3
                class="mt-2 text-sm font-semibold text-gray-900 dark:text-white"
            >
                Henüz menü bağlantısı yok
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Ziyaretçilerinizin sitenizde gezinebilmesi için menü öğeleri
                ekleyin.
            </p>
            <div class="mt-6">
                <Button
                    @click="openCreateDrawer"
                    class="bg-emerald-600 text-white hover:bg-emerald-700"
                >
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Bağlantı Ekle
                </Button>
            </div>
        </div>

        <div v-else class="grid gap-8 lg:grid-cols-3">
            <!-- Üst Bar Menüsü -->
            <div class="space-y-4">
                <div class="mb-2 flex items-center gap-2">
                    <div
                        class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                    >
                        <RectangleStackIcon class="size-5" />
                    </div>
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Üst Bar
                    </h2>
                    <span
                        class="ml-auto rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400"
                        >{{ groupedItems.top.length }}</span
                    >
                </div>

                <div class="space-y-3">
                    <GlassCard
                        v-for="item in groupedItems.top"
                        :key="item.public_id"
                        class="flex flex-col gap-3 !bg-white/40 p-4 dark:!bg-black/40"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    class="max-w-[200px] truncate text-xs text-gray-500"
                                    :title="item.url"
                                >
                                    {{ item.url }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                :class="
                                    item.status === 'published'
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400'
                                        : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400'
                                "
                            >
                                {{
                                    item.status === 'published'
                                        ? 'Yayında'
                                        : 'Taslak'
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-white/10"
                        >
                            <span class="text-xs text-gray-400"
                                >Sıra: {{ item.sort_order }}</span
                            >
                            <div class="flex gap-1">
                                <button
                                    @click="togglePublish(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-emerald-600 dark:hover:text-emerald-400"
                                    :title="
                                        item.status === 'published'
                                            ? 'Taslağa al'
                                            : 'Yayına al'
                                    "
                                >
                                    <CheckCircleIcon
                                        v-if="item.status !== 'published'"
                                        class="h-4 w-4"
                                    />
                                    <XCircleIcon v-else class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openEditDrawer(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-blue-600"
                                    title="Düzenle"
                                >
                                    <PencilSquareIcon class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteNavigation(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-red-600"
                                    title="Sil"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </GlassCard>
                    <div
                        v-if="!groupedItems.top.length"
                        class="rounded-xl border border-dashed border-gray-200 py-4 text-center text-sm text-gray-400 dark:border-white/10"
                    >
                        Boş
                    </div>
                </div>
            </div>

            <!-- Mega Menü -->
            <div class="space-y-4">
                <div class="mb-2 flex items-center gap-2">
                    <div
                        class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                    >
                        <Bars3Icon class="size-5" />
                    </div>
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Mega Menü
                    </h2>
                    <span
                        class="ml-auto rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400"
                        >{{ groupedItems.mega.length }}</span
                    >
                </div>

                <div class="space-y-3">
                    <GlassCard
                        v-for="item in groupedItems.mega"
                        :key="item.public_id"
                        class="flex flex-col gap-3 border-emerald-100 !bg-white/40 p-4 dark:border-emerald-500/20 dark:!bg-black/40"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    class="max-w-[200px] truncate text-xs text-gray-500"
                                    :title="item.url"
                                >
                                    {{ item.url }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                :class="
                                    item.status === 'published'
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400'
                                        : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400'
                                "
                            >
                                {{
                                    item.status === 'published'
                                        ? 'Yayında'
                                        : 'Taslak'
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-white/10"
                        >
                            <span class="text-xs text-gray-400"
                                >Sıra: {{ item.sort_order }}</span
                            >
                            <div class="flex gap-1">
                                <button
                                    @click="togglePublish(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-emerald-600 dark:hover:text-emerald-400"
                                    :title="
                                        item.status === 'published'
                                            ? 'Taslağa al'
                                            : 'Yayına al'
                                    "
                                >
                                    <CheckCircleIcon
                                        v-if="item.status !== 'published'"
                                        class="h-4 w-4"
                                    />
                                    <XCircleIcon v-else class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openEditDrawer(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-blue-600"
                                    title="Düzenle"
                                >
                                    <PencilSquareIcon class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteNavigation(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-red-600"
                                    title="Sil"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </GlassCard>
                    <div
                        v-if="!groupedItems.mega.length"
                        class="rounded-xl border border-dashed border-gray-200 py-4 text-center text-sm text-gray-400 dark:border-white/10"
                    >
                        Boş
                    </div>
                </div>
            </div>

            <!-- Footer Menüsü -->
            <div class="space-y-4">
                <div class="mb-2 flex items-center gap-2">
                    <div
                        class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400"
                    >
                        <Bars3BottomLeftIcon class="size-5" />
                    </div>
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Footer
                    </h2>
                    <span
                        class="ml-auto rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400"
                        >{{ groupedItems.footer.length }}</span
                    >
                </div>

                <div class="space-y-3">
                    <GlassCard
                        v-for="item in groupedItems.footer"
                        :key="item.public_id"
                        class="flex flex-col gap-3 !bg-white/40 p-4 dark:!bg-black/40"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    class="max-w-[200px] truncate text-xs text-gray-500"
                                    :title="item.url"
                                >
                                    {{ item.url }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                :class="
                                    item.status === 'published'
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400'
                                        : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400'
                                "
                            >
                                {{
                                    item.status === 'published'
                                        ? 'Yayında'
                                        : 'Taslak'
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-white/10"
                        >
                            <span class="text-xs text-gray-400"
                                >Sıra: {{ item.sort_order }}</span
                            >
                            <div class="flex gap-1">
                                <button
                                    @click="togglePublish(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-emerald-600 dark:hover:text-emerald-400"
                                    :title="
                                        item.status === 'published'
                                            ? 'Taslağa al'
                                            : 'Yayına al'
                                    "
                                >
                                    <CheckCircleIcon
                                        v-if="item.status !== 'published'"
                                        class="h-4 w-4"
                                    />
                                    <XCircleIcon v-else class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openEditDrawer(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-blue-600"
                                    title="Düzenle"
                                >
                                    <PencilSquareIcon class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteNavigation(item)"
                                    class="p-1.5 text-gray-400 transition hover:text-red-600"
                                    title="Sil"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </GlassCard>
                    <div
                        v-if="!groupedItems.footer.length"
                        class="rounded-xl border border-dashed border-gray-200 py-4 text-center text-sm text-gray-400 dark:border-white/10"
                    >
                        Boş
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Drawer using Sheet -->
    <Sheet :open="isDrawerOpen" @update:open="isDrawerOpen = $event">
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>{{
                    editingNavigationId ? 'Menü Düzenle' : 'Yeni Menü Ekle'
                }}</SheetTitle>
                <SheetDescription>
                    Vitrinde görünecek navigasyon menüsünü buradan
                    ayarlayabilirsiniz.
                </SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submitNavigation" class="mt-8 space-y-6">
                <div
                    class="space-y-4 rounded-lg border bg-muted/5 p-4 shadow-sm"
                >
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-3"
                    >
                        <Bars3Icon class="size-4 text-primary" />
                        <h3 class="text-sm font-medium text-foreground">
                            Görünüm ve Bağlantı
                        </h3>
                    </div>

                    <div class="space-y-2">
                        <Label>Konum</Label>
                        <Select v-model="navigationForm.location">
                            <SelectTrigger class="bg-background"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="top">Üst bar</SelectItem>
                                <SelectItem value="mega">Mega menü</SelectItem>
                                <SelectItem value="footer">Footer</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="navigationForm.errors.location" />
                    </div>

                    <div class="space-y-2">
                        <Label for="nav-title">Başlık</Label>
                        <Input
                            id="nav-title"
                            v-model="navigationForm.title"
                            required
                            class="bg-background"
                        />
                        <InputError :message="navigationForm.errors.title" />
                    </div>

                    <div class="space-y-2">
                        <Label for="nav-url">URL</Label>
                        <Input
                            id="nav-url"
                            v-model="navigationForm.url"
                            required
                            placeholder="/kategori/gida"
                            class="bg-background"
                        />
                        <InputError :message="navigationForm.errors.url" />
                    </div>
                </div>

                <div
                    class="space-y-4 rounded-lg border bg-muted/5 p-4 shadow-sm"
                >
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-3"
                    >
                        <PencilSquareIcon class="size-4 text-primary" />
                        <h3 class="text-sm font-medium text-foreground">
                            Durum ve Sıralama
                        </h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Sıra</Label>
                            <Input
                                v-model="navigationForm.sort_order"
                                type="number"
                                min="0"
                                class="bg-background"
                            />
                            <InputError
                                :message="navigationForm.errors.sort_order"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Durum</Label>
                            <Select v-model="navigationForm.status">
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
                            <InputError
                                :message="navigationForm.errors.status"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
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
                        :disabled="navigationForm.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        {{ editingNavigationId ? 'Güncelle' : 'Kaydet' }}
                    </Button>
                </div>
            </form>
        </SheetContent>
    </Sheet>
</template>
