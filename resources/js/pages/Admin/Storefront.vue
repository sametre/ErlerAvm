<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

type Campaign = {
    public_id: string;
    name: string;
    slug: string;
    description?: string | null;
    status: 'draft' | 'published';
    starts_at?: string | null;
    ends_at?: string | null;
};

type CategoryRow = {
    public_id: string;
    name: string;
    slug: string;
    status: 'draft' | 'published';
    sort_order: number;
    description?: string | null;
};

const props = defineProps<{
    campaigns: Campaign[];
    categories: CategoryRow[];
}>();

const baseUrl = computed(() => '/vitrin');
const editingCampaignId = ref<string | null>(null);

const campaignForm = useForm({
    name: '',
    description: '',
    status: 'draft',
    starts_at: '',
    ends_at: '',
});

function updateCategory(
    category: CategoryRow,
    status: 'draft' | 'published',
): void {
    useForm({
        status,
        sort_order: category.sort_order,
    }).patch(`${baseUrl.value}/kategori/${category.public_id}`, {
        preserveScroll: true,
    });
}

function submitCampaign(): void {
    if (editingCampaignId.value) {
        campaignForm.patch(
            `${baseUrl.value}/kampanya/${editingCampaignId.value}`,
            {
                preserveScroll: true,
                onSuccess: resetCampaignForm,
            },
        );

        return;
    }

    campaignForm.post(`${baseUrl.value}/kampanya`, {
        preserveScroll: true,
        onSuccess: resetCampaignForm,
    });
}

function publishCampaign(
    campaign: Campaign,
    status: 'draft' | 'published',
): void {
    useForm({
        name: campaign.name,
        description: campaign.description ?? '',
        status,
        starts_at: campaign.starts_at ?? '',
        ends_at: campaign.ends_at ?? '',
    }).patch(`${baseUrl.value}/kampanya/${campaign.public_id}`, {
        preserveScroll: true,
    });
}

function editCampaign(campaign: Campaign): void {
    editingCampaignId.value = campaign.public_id;
    campaignForm.name = campaign.name;
    campaignForm.description = campaign.description ?? '';
    campaignForm.status = campaign.status;
    campaignForm.starts_at = campaign.starts_at ?? '';
    campaignForm.ends_at = campaign.ends_at ?? '';
}

function resetCampaignForm(): void {
    editingCampaignId.value = null;
    campaignForm.reset('name', 'description', 'status', 'starts_at', 'ends_at');
    campaignForm.clearErrors();
}

function deleteCampaign(campaign: Campaign): void {
    if (!window.confirm('Bu kampanyayı silmek istediğinize emin misiniz?')) {
        return;
    }

    useForm({}).delete(`${baseUrl.value}/kampanya/${campaign.public_id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Vitrin Yönetimi" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Admin</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Menü, Mega Menü ve Slider Yönetimi
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                Ana sayfadaki üst menü, mega menü, footer bağlantıları, hero
                slider, kategori vitrini ve koleksiyon alanları buradan
                yönetilir. Yayına alınmayan kayıtlar vitrinde gösterilmez.
            </p>
        </section>

        <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
            <div class="rounded-lg border bg-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-semibold">
                        {{
                            editingCampaignId
                                ? 'Kampanya düzenle'
                                : 'Kampanya ekle'
                        }}
                    </h2>
                    <Button
                        v-if="editingCampaignId"
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="resetCampaignForm"
                    >
                        Yeni
                    </Button>
                </div>
                <form class="mt-5 grid gap-4" @submit.prevent="submitCampaign">
                    <div class="grid gap-2">
                        <Label>Kampanya adı</Label>
                        <Input
                            v-model="campaignForm.name"
                            required
                            maxlength="180"
                        />
                        <InputError :message="campaignForm.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Açıklama</Label>
                        <textarea
                            v-model="campaignForm.description"
                            class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            maxlength="1000"
                        />
                        <InputError
                            :message="campaignForm.errors.description"
                        />
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label>Durum</Label>
                            <Select v-model="campaignForm.status">
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
                            <InputError :message="campaignForm.errors.status" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Başlangıç</Label>
                            <Input
                                v-model="campaignForm.starts_at"
                                type="datetime-local"
                            />
                            <InputError
                                :message="campaignForm.errors.starts_at"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Bitiş</Label>
                            <Input
                                v-model="campaignForm.ends_at"
                                type="datetime-local"
                            />
                            <InputError
                                :message="campaignForm.errors.ends_at"
                            />
                        </div>
                    </div>
                    <Button type="submit" :disabled="campaignForm.processing">
                        {{
                            editingCampaignId
                                ? 'Kampanya güncelle'
                                : 'Kampanya kaydet'
                        }}
                    </Button>
                </form>
            </div>

            <div class="rounded-lg border bg-card">
                <div class="border-b p-4">
                    <h2 class="font-semibold">Kampanyalar</h2>
                </div>
                <div class="divide-y">
                    <div
                        v-for="campaign in props.campaigns"
                        :key="campaign.public_id"
                        class="grid gap-3 p-4 md:grid-cols-[1fr_auto]"
                    >
                        <div>
                            <p class="font-medium">{{ campaign.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                /kampanya/{{ campaign.slug }} ·
                                {{ campaign.status }}
                            </p>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-2 md:justify-end"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="editCampaign(campaign)"
                            >
                                Düzenle
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="
                                    publishCampaign(
                                        campaign,
                                        campaign.status === 'published'
                                            ? 'draft'
                                            : 'published',
                                    )
                                "
                            >
                                {{
                                    campaign.status === 'published'
                                        ? 'Taslağa al'
                                        : 'Yayına al'
                                }}
                            </Button>
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                @click="deleteCampaign(campaign)"
                            >
                                Sil
                            </Button>
                        </div>
                    </div>
                    <p
                        v-if="!props.campaigns.length"
                        class="p-4 text-sm text-muted-foreground"
                    >
                        Henüz kampanya yok.
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-lg border bg-card">
            <div class="border-b p-4">
                <h2 class="font-semibold">Kategoriler / koleksiyon vitrini</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Yayındaki kategoriler ana sayfadaki kategori ve koleksiyon
                    alanlarında otomatik görünür.
                </p>
            </div>
            <div class="divide-y">
                <div
                    v-for="category in props.categories"
                    :key="category.public_id"
                    class="grid gap-3 p-4 md:grid-cols-[1fr_auto]"
                >
                    <div>
                        <p class="font-medium">{{ category.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            /kategori/{{ category.slug }} · sıra
                            {{ category.sort_order }} · {{ category.status }}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        @click="
                            updateCategory(
                                category,
                                category.status === 'published'
                                    ? 'draft'
                                    : 'published',
                            )
                        "
                    >
                        {{
                            category.status === 'published'
                                ? 'Vitrinden kaldır'
                                : 'Vitrine al'
                        }}
                    </Button>
                </div>
                <p
                    v-if="!props.categories.length"
                    class="p-4 text-sm text-muted-foreground"
                >
                    Kategori yok. Koleksiyon alanı için önce kategori
                    oluşturulmalı.
                </p>
            </div>
        </section>
    </div>
</template>
