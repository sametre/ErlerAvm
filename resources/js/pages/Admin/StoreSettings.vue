<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { GripVertical, Palette, Save, Store, Power } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Metric = { label: string; value: number };
type SectionOption = { key: string; label: string };

const props = defineProps<{
    settings: Record<string, any>;
    metrics: Metric[];
    availableSections: SectionOption[];
}>();

const form = useForm({
    store_name: props.settings.store_name || 'ErlerAVM',
    logo_url: props.settings.logo_url || '/erleravm.png',
    favicon_url: props.settings.favicon_url || '/favicon.png',
    primary_color: props.settings.primary_color || '#ed1c24',
    accent_color: props.settings.accent_color || '#111827',
    contact_phone: props.settings.contact_phone || '',
    contact_email: props.settings.contact_email || '',
    address: props.settings.address || '',
    facebook_url: props.settings.facebook_url || '',
    instagram_url: props.settings.instagram_url || '',
    twitter_url: props.settings.twitter_url || '',
    youtube_url: props.settings.youtube_url || '',
    maintenance_mode_active: !!props.settings.maintenance_mode_active,
    homepage_sections: props.settings.homepage_sections || [
        'hero_slider',
        'featured_categories',
        'campaigns',
        'best_sellers',
        'blog',
    ],
});

function toggleSection(key: string): void {
    if (form.homepage_sections.includes(key)) {
        form.homepage_sections = form.homepage_sections.filter(
            (item: string) => item !== key,
        );
        return;
    }

    form.homepage_sections = [...form.homepage_sections, key];
}

function moveSection(index: number, direction: number): void {
    const nextIndex = index + direction;

    if (
        index < 0 ||
        nextIndex < 0 ||
        nextIndex >= form.homepage_sections.length
    )
        return;

    const next = [...form.homepage_sections];
    [next[index], next[nextIndex]] = [next[nextIndex], next[index]];
    form.homepage_sections = next;
}

function sectionLabel(key: string): string {
    return (
        props.availableSections.find((section) => section.key === key)?.label ||
        key
    );
}

const submit = () => {
    form.patch('/magaza-ayarlari', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Mağaza Ayarları" />

    <div class="space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-primary">Mağaza yönetimi</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Mağaza ayarları
                </h1>
                <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                    Mağaza adı, logo, favicon, renkler, sosyal bağlantılar ve
                    anasayfa yerleşimi gerçek store_settings tablosuna
                    kaydedilir.
                </p>
            </div>
            <Button type="button" @click="submit" :disabled="form.processing">
                <Save class="mr-2 size-4" />
                Ayarları kaydet
            </Button>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <form
            class="grid gap-6 xl:grid-cols-[1fr_420px]"
            @submit.prevent="submit"
        >
            <section class="space-y-6">
                <div
                    class="rounded-lg border border-destructive/20 bg-card bg-destructive/5 p-5"
                >
                    <div class="mb-5 flex items-center gap-2">
                        <Power class="size-5 text-destructive" />
                        <h2 class="font-semibold text-destructive">
                            Site Durumu
                        </h2>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <Label class="text-base">Bakım Modu</Label>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Bakım modunu aktifleştirdiğinizde, site sadece
                                yöneticiler tarafından görüntülenebilir.
                                Müşteriler "Kısa bir süreliğine bakımdayız"
                                sayfasıyla karşılaşır.
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.maintenance_mode_active"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <span
                                class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-primary peer-focus:ring-2 peer-focus:ring-primary/30 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700"
                            ></span>
                        </label>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5">
                    <div class="mb-5 flex items-center gap-2">
                        <Store class="size-5 text-primary" />
                        <h2 class="font-semibold">Kimlik ve görseller</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>Mağaza adı</Label>
                            <Input
                                v-model="form.store_name"
                                required
                                maxlength="120"
                            />
                            <p
                                v-if="form.errors.store_name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.store_name }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Logo yolu</Label>
                            <Input
                                v-model="form.logo_url"
                                maxlength="500"
                                placeholder="/erleravm.png"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Favicon yolu</Label>
                            <Input
                                v-model="form.favicon_url"
                                maxlength="500"
                                placeholder="/favicon.png"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>İletişim telefonu</Label>
                            <Input
                                v-model="form.contact_phone"
                                maxlength="60"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>İletişim e-posta</Label>
                            <Input
                                v-model="form.contact_email"
                                type="email"
                                maxlength="255"
                            />
                        </div>
                        <div class="grid gap-2 md:col-span-2">
                            <Label>Adres</Label>
                            <textarea
                                v-model="form.address"
                                maxlength="700"
                                class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5">
                    <div class="mb-5 flex items-center gap-2">
                        <Palette class="size-5 text-primary" />
                        <h2 class="font-semibold">
                            Renk ve sosyal bağlantılar
                        </h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>Ana renk</Label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.primary_color"
                                    type="color"
                                    class="h-10 w-14 rounded-md border bg-background"
                                />
                                <Input
                                    v-model="form.primary_color"
                                    required
                                    pattern="^#[0-9a-fA-F]{6}$"
                                />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label>Vurgu rengi</Label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.accent_color"
                                    type="color"
                                    class="h-10 w-14 rounded-md border bg-background"
                                />
                                <Input
                                    v-model="form.accent_color"
                                    required
                                    pattern="^#[0-9a-fA-F]{6}$"
                                />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label>Facebook</Label>
                            <Input
                                v-model="form.facebook_url"
                                type="url"
                                placeholder="https://facebook.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Instagram</Label>
                            <Input
                                v-model="form.instagram_url"
                                type="url"
                                placeholder="https://instagram.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>Twitter / X</Label>
                            <Input
                                v-model="form.twitter_url"
                                type="url"
                                placeholder="https://x.com/..."
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label>YouTube</Label>
                            <Input
                                v-model="form.youtube_url"
                                type="url"
                                placeholder="https://youtube.com/..."
                            />
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Anasayfa yerleşimi</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Aktif section’lar yukarıdan aşağıya bu sırayla
                        kullanılır.
                    </p>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="(key, index) in form.homepage_sections"
                            :key="key"
                            class="flex items-center gap-3 rounded-lg border bg-background p-3"
                        >
                            <GripVertical
                                class="size-4 text-muted-foreground"
                            />
                            <span class="min-w-0 flex-1 font-medium">{{
                                sectionLabel(key)
                            }}</span>
                            <button
                                type="button"
                                class="text-xs font-semibold text-muted-foreground hover:text-foreground"
                                @click="moveSection(key, -1)"
                                :disabled="index === 0"
                            >
                                Yukarı
                            </button>
                            <button
                                type="button"
                                class="text-xs font-semibold text-muted-foreground hover:text-foreground"
                                @click="moveSection(key, 1)"
                                :disabled="
                                    index === form.homepage_sections.length - 1
                                "
                            >
                                Aşağı
                            </button>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Section havuzu</h2>
                    <div class="mt-4 grid gap-2">
                        <label
                            v-for="section in props.availableSections"
                            :key="section.key"
                            class="flex items-center gap-3 rounded-lg border p-3 text-sm"
                        >
                            <input
                                type="checkbox"
                                class="size-4 rounded border-input"
                                :checked="
                                    form.homepage_sections.includes(section.key)
                                "
                                @change="toggleSection(section.key)"
                            />
                            <span class="font-medium">{{ section.label }}</span>
                        </label>
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
