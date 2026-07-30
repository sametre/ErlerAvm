<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Megaphone, Share2, Ticket } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Metric = { label: string; value: number };
type Coupon = {
    public_id: string;
    code: string;
    description?: string | null;
    type: 'fixed' | 'percent';
    value: string | number;
    status: 'draft' | 'active' | 'paused' | 'expired';
    usage_limit?: number | null;
    used_count?: number | null;
    starts_at?: string | null;
    ends_at?: string | null;
};

const props = defineProps<{
    metrics: Metric[];
    campaigns: {
        public_id: string;
        name: string;
        status: string;
        starts_at?: string | null;
        ends_at?: string | null;
    }[];
    coupons: Coupon[];
    welcomeCoupon?: Coupon | null;
    segments: {
        public_id: string;
        name: string;
        description?: string | null;
        status: string;
    }[];
    storeSettings: Record<string, any>;
}>();

const defaultSections = computed<string[]>(
    () =>
        props.storeSettings.homepage_sections || [
            'hero_slider',
            'featured_categories',
            'campaigns',
            'best_sellers',
            'blog',
        ],
);

const socialForm = useForm({
    store_name: props.storeSettings.store_name || 'ErlerAVM',
    logo_url: props.storeSettings.logo_url || '/erleravm.png',
    favicon_url: props.storeSettings.favicon_url || '/favicon.png',
    primary_color: props.storeSettings.primary_color || '#ed1c24',
    accent_color: props.storeSettings.accent_color || '#111827',
    contact_phone: props.storeSettings.contact_phone || '',
    contact_email: props.storeSettings.contact_email || '',
    address: props.storeSettings.address || '',
    facebook_url: props.storeSettings.facebook_url || '',
    instagram_url: props.storeSettings.instagram_url || '',
    twitter_url: props.storeSettings.twitter_url || '',
    youtube_url: props.storeSettings.youtube_url || '',
    homepage_sections: defaultSections.value,
});

const couponForm = useForm({
    code: props.welcomeCoupon?.code || 'HOSGELDIN',
    description:
        props.welcomeCoupon?.description || 'Yeni müşteri hoş geldin kuponu',
    type: props.welcomeCoupon?.type || 'fixed',
    value: props.welcomeCoupon?.value || 0,
    status: props.welcomeCoupon?.status || 'draft',
    usage_limit: props.welcomeCoupon?.usage_limit || '',
    starts_at: props.welcomeCoupon?.starts_at || '',
    ends_at: props.welcomeCoupon?.ends_at || '',
});

function saveSocialSettings(): void {
    socialForm.patch('/magaza-ayarlari', { preserveScroll: true });
}

function saveWelcomeCoupon(): void {
    if (props.welcomeCoupon?.public_id) {
        couponForm.patch(
            `/pazarlama/hos-geldin-kuponu/${props.welcomeCoupon.public_id}`,
            { preserveScroll: true },
        );
        return;
    }

    couponForm.post('/pazarlama/hos-geldin-kuponu', { preserveScroll: true });
}
</script>

<template>
    <Head title="Pazarlama" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Pazarlama merkezi</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Pazarlama ve sosyal alanlar
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                Kampanya, kupon, segment, sosyal medya ve hoş geldin kuponu
                kayıtları gerçek tablolardan yönetilir.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-5">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_420px]">
            <div class="space-y-6">
                <section class="rounded-lg border bg-card">
                    <div class="flex items-center gap-2 border-b p-4">
                        <Megaphone class="size-5 text-primary" />
                        <h2 class="font-semibold">Kampanya kayıtları</h2>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="campaign in props.campaigns"
                            :key="campaign.public_id"
                            class="flex items-center justify-between gap-4 p-4 text-sm"
                        >
                            <div>
                                <p class="font-semibold">{{ campaign.name }}</p>
                                <p class="text-muted-foreground">
                                    {{ campaign.starts_at || '-' }} /
                                    {{ campaign.ends_at || '-' }}
                                </p>
                            </div>
                            <span
                                class="rounded-md bg-muted px-2 py-1 text-xs"
                                >{{ campaign.status }}</span
                            >
                        </div>
                        <p
                            v-if="!props.campaigns.length"
                            class="p-6 text-center text-sm text-muted-foreground"
                        >
                            Kayıtlı kampanya yok.
                        </p>
                    </div>
                </section>

                <section class="rounded-lg border bg-card">
                    <div class="flex items-center gap-2 border-b p-4">
                        <Ticket class="size-5 text-primary" />
                        <h2 class="font-semibold">Kupon kayıtları</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-160 text-sm">
                            <thead class="bg-muted/60 text-left">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Kod</th>
                                    <th class="px-4 py-3 font-medium">Tip</th>
                                    <th class="px-4 py-3 font-medium">Değer</th>
                                    <th class="px-4 py-3 font-medium">
                                        Kullanım
                                    </th>
                                    <th class="px-4 py-3 font-medium">Durum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="coupon in props.coupons"
                                    :key="coupon.public_id"
                                >
                                    <td class="px-4 py-3 font-semibold">
                                        {{ coupon.code }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{
                                            coupon.type === 'percent'
                                                ? 'Yüzde'
                                                : 'Sabit'
                                        }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ coupon.value }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ coupon.used_count || 0 }} /
                                        {{ coupon.usage_limit || 'Limitsiz' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ coupon.status }}
                                    </td>
                                </tr>
                                <tr v-if="!props.coupons.length">
                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        Kayıtlı kupon yok.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border bg-card p-5">
                    <div class="mb-4 flex items-center gap-2">
                        <Share2 class="size-5 text-primary" />
                        <h2 class="font-semibold">Sosyal medya alanları</h2>
                    </div>
                    <form
                        class="grid gap-4"
                        @submit.prevent="saveSocialSettings"
                    >
                        <div class="grid gap-2">
                            <Label>Facebook</Label>
                            <Input
                                v-model="socialForm.facebook_url"
                                type="url"
                                placeholder="https://facebook.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Instagram</Label>
                            <Input
                                v-model="socialForm.instagram_url"
                                type="url"
                                placeholder="https://instagram.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Twitter / X</Label>
                            <Input
                                v-model="socialForm.twitter_url"
                                type="url"
                                placeholder="https://x.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>YouTube</Label>
                            <Input
                                v-model="socialForm.youtube_url"
                                type="url"
                                placeholder="https://youtube.com/..."
                            />
                        </div>
                        <Button type="submit" :disabled="socialForm.processing"
                            >Sosyal alanları kaydet</Button
                        >
                    </form>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Hoş geldin kuponu</h2>
                    <form
                        class="mt-4 grid gap-4"
                        @submit.prevent="saveWelcomeCoupon"
                    >
                        <div class="grid gap-2">
                            <Label>Kupon kodu</Label>
                            <Input
                                v-model="couponForm.code"
                                required
                                maxlength="80"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Açıklama</Label>
                            <textarea
                                v-model="couponForm.description"
                                maxlength="1000"
                                class="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-2">
                                <Label>Tip</Label>
                                <select
                                    v-model="couponForm.type"
                                    class="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="fixed">Sabit tutar</option>
                                    <option value="percent">Yüzde</option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Değer</Label>
                                <Input
                                    v-model="couponForm.value"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    required
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-2">
                                <Label>Durum</Label>
                                <select
                                    v-model="couponForm.status"
                                    class="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="draft">Taslak</option>
                                    <option value="active">Aktif</option>
                                    <option value="paused">Duraklatıldı</option>
                                    <option value="expired">
                                        Süresi doldu
                                    </option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Kullanım limiti</Label>
                                <Input
                                    v-model="couponForm.usage_limit"
                                    type="number"
                                    min="1"
                                    placeholder="Limitsiz"
                                />
                            </div>
                        </div>
                        <Button type="submit" :disabled="couponForm.processing"
                            >Kuponu kaydet</Button
                        >
                    </form>
                </section>
            </aside>
        </section>
    </div>
</template>
