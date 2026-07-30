<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import TipTapEditor from '@/components/TipTapEditor.vue';
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
import {
    PhotoIcon,
    XMarkIcon,
    LinkIcon,
    SparklesIcon,
    ArrowLeftIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

type Option = {
    id: number;
    name: string;
};

const props = defineProps<{
    product: any | null;
    categories: Option[];
    brands: Option[];
}>();

const productForm = useForm({
    name: props.product?.name || '',
    sku: props.product?.sku || '',
    category_id: props.product?.category_id
        ? String(props.product.category_id)
        : '',
    brand_id: props.product?.brand_id ? String(props.product.brand_id) : '',
    price: props.product?.price || '',
    compare_at_price: props.product?.compare_at_price || '',
    stock_quantity: props.product?.stock_quantity || 0,
    status: props.product?.status || 'draft',
    short_description: props.product?.short_description || '',
    description: props.product?.description || '',
    seo_title: props.product?.seo_title || '',
    seo_description: props.product?.seo_description || '',
    seo_keywords: props.product?.seo_keywords || '',
    image_urls: props.product?.image_urls || '',
    images: [] as File[],
    features: props.product?.features || '',
    variants: props.product?.variants || [],
});

const variantOptions = ref<{ name: string; values: string[] }[]>([]);

// Initialize variantOptions if editing a product with variants
if (productForm.variants.length > 0) {
    const firstVariant = productForm.variants[0];
    if (firstVariant.options) {
        Object.keys(firstVariant.options).forEach((key) => {
            const values = new Set<string>();
            productForm.variants.forEach((v: any) => {
                if (v.options && v.options[key]) {
                    values.add(v.options[key]);
                }
            });
            variantOptions.value.push({
                name: key,
                values: Array.from(values),
            });
        });
    }
}

function generateVariants() {
    if (variantOptions.value.length === 0) {
        productForm.variants = [];
        return;
    }

    const options = variantOptions.value.filter(
        (o) => o.name && o.values.length > 0,
    );
    if (options.length === 0) {
        productForm.variants = [];
        return;
    }

    const cartesian = (...a: any[]) =>
        a.reduce((a, b) =>
            a.flatMap((d: any) => b.map((e: any) => [d, e].flat())),
        );
    const optionValues = options.map((o) =>
        o.values.map((v) => ({ [o.name]: v })),
    );

    let combinations =
        optionValues.length > 1
            ? cartesian(...optionValues)
            : optionValues[0].map((v) => [v]);

    const newVariants = combinations.map((combo: any[]) => {
        const mergedOptions = Object.assign({}, ...combo);
        const existing = productForm.variants.find(
            (v: any) =>
                JSON.stringify(v.options) === JSON.stringify(mergedOptions),
        );

        if (existing) return existing;

        return {
            sku: '',
            price: productForm.price,
            compare_at_price: productForm.compare_at_price,
            stock_quantity: 0,
            options: mergedOptions,
        };
    });

    productForm.variants = newVariants;
}

function addVariantOption() {
    variantOptions.value.push({ name: '', values: [] });
}

function removeVariantOption(idx: number) {
    variantOptions.value.splice(idx, 1);
    generateVariants();
}

function updateVariantOptionValues(idx: number, val: string) {
    variantOptions.value[idx].values = val
        .split(',')
        .map((v) => v.trim())
        .filter((v) => v);
    generateVariants();
}

const currentImageUrls = ref<string[]>(
    props.product?.image_urls
        ? props.product.image_urls
              .split('\n')
              .filter((u: string) => u.trim() !== '')
        : [],
);
const newImageUrl = ref('');

function getObjectUrl(file: File) {
    return URL.createObjectURL(file);
}

function removeExistingImage(idx: number) {
    currentImageUrls.value.splice(idx, 1);
    updateImageUrlsField();
}

function removeNewFile(idx: number) {
    productForm.images.splice(idx, 1);
}

function addUrl() {
    if (!newImageUrl.value) return;
    currentImageUrls.value.push(newImageUrl.value);
    updateImageUrlsField();
    newImageUrl.value = '';
}

function handleFileSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        Array.from(target.files).forEach((file) => {
            productForm.images.push(file);
        });
    }
    target.value = '';
}

function updateImageUrlsField() {
    productForm.image_urls = currentImageUrls.value.join('\n');
}

const isBrandDrawerOpen = ref(false);
const brandForm = useForm({
    name: '',
    description: '',
    status: 'published',
});

function submitBrand(): void {
    brandForm.post('/stok/marka', {
        preserveScroll: true,
        onSuccess: () => {
            brandForm.reset();
            isBrandDrawerOpen.value = false;
            router.reload({ only: ['brands'] });
        },
    });
}

const isAiDrawerOpen = ref(false);
const aiError = ref<string | null>(null);
const aiForm = useForm({
    notes: '',
});

async function generateWithAi(): Promise<void> {
    aiError.value = null;
    const token =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.content ?? '';

    try {
        const response = await fetch('/stok/ai/urun-icerigi', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({
                name: productForm.name,
                sku: productForm.sku,
                brand: props.brands.find(
                    (brand) => String(brand.id) === productForm.brand_id,
                )?.name,
                category: props.categories.find(
                    (category) =>
                        String(category.id) === productForm.category_id,
                )?.name,
                notes: aiForm.notes,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            aiError.value = data.message || 'AI içerik üretimi tamamlanamadı.';
            return;
        }

        if (data.name && !productForm.name) productForm.name = data.name;
        if (data.short_description)
            productForm.short_description = data.short_description;
        if (data.description) productForm.description = data.description;
        if (data.seo_title) productForm.seo_title = data.seo_title;
        if (data.seo_description)
            productForm.seo_description = data.seo_description;
        if (data.seo_keywords) productForm.seo_keywords = data.seo_keywords;
        if (data.image_prompt) aiForm.notes = data.image_prompt;
    } catch {
        aiError.value =
            'AI servisine ulaşılamadı. Ağ bağlantısını ve sunucu loglarını kontrol edin.';
    }
}

function submitProduct(): void {
    if (props.product?.public_id) {
        productForm
            .transform((data) => ({
                ...data,
                _method: 'PATCH',
            }))
            .post(`/stok/urun/${props.product.public_id}`);
    } else {
        productForm.post('/stok/urun');
    }
}

const pageTitle = computed(() =>
    props.product ? 'Ürünü düzenle' : 'Yeni ürün ekle',
);
</script>

<template>
    <Head :title="pageTitle" />

    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/stok">
                    <ArrowLeftIcon class="h-4 w-4 text-slate-500" />
                </Link>
            </Button>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ pageTitle }}
                </h1>
                <p class="mt-1 text-slate-500">
                    Ürün bilgilerini eksiksiz doldurun. Bilgisayardan sürükle
                    bırak ile veya bağlantı üzerinden manuel görsel
                    ekleyebilirsiniz.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <Button
                type="button"
                variant="outline"
                @click="isAiDrawerOpen = true"
            >
                <SparklesIcon class="mr-2 h-4 w-4 text-amber-500" />
                AI İçerik
            </Button>
            <Button
                @click="submitProduct"
                :disabled="productForm.processing"
                class="bg-primary text-primary-foreground hover:bg-primary/90"
            >
                <CheckIcon class="mr-2 h-4 w-4" />
                {{ props.product ? 'Güncelle' : 'Kaydet' }}
            </Button>
        </div>
    </div>

    <form @submit.prevent="submitProduct" class="mt-8 flex flex-col gap-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Sol Kolon: Temel Bilgiler, Açıklama, Görseller -->
            <div class="space-y-8 lg:col-span-2">
                <!-- Temel Bilgiler -->
                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Temel Bilgiler
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>Ürün Adı</Label>
                            <Input
                                v-model="productForm.name"
                                required
                                maxlength="180"
                                class="bg-background"
                            />
                            <InputError :message="productForm.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label>Kısa Açıklama</Label>
                            <Input
                                v-model="productForm.short_description"
                                maxlength="500"
                                placeholder="Ürün kartında görünecek özet"
                                class="bg-background"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Detaylı Açıklama</Label>
                            <textarea
                                v-model="productForm.description"
                                class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-emerald-500"
                                maxlength="5000"
                            ></textarea>
                        </div>
                        <div class="space-y-2">
                            <Label>Teknik Özellikler (HTML)</Label>
                            <TipTapEditor v-model="productForm.features" />
                        </div>
                    </div>
                </div>

                <!-- Varyantlar -->
                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Varyantlar
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-4 rounded-lg border bg-muted/5 p-4">
                            <div
                                v-for="(opt, idx) in variantOptions"
                                :key="idx"
                                class="flex items-start gap-4"
                            >
                                <div class="flex-1 space-y-2">
                                    <Label>Seçenek Adı (Örn: Renk)</Label>
                                    <Input
                                        v-model="opt.name"
                                        @change="generateVariants"
                                        placeholder="Renk"
                                        class="bg-background"
                                    />
                                </div>
                                <div class="flex-[2] space-y-2">
                                    <Label
                                        >Değerler (Virgülle ayırın, Örn:
                                        Kırmızı, Mavi)</Label
                                    >
                                    <Input
                                        :value="opt.values.join(', ')"
                                        @change="
                                            (e) =>
                                                updateVariantOptionValues(
                                                    idx,
                                                    (
                                                        e.target as HTMLInputElement
                                                    ).value,
                                                )
                                        "
                                        placeholder="Kırmızı, Mavi"
                                        class="bg-background"
                                    />
                                </div>
                                <div class="pt-8">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        @click="removeVariantOption(idx)"
                                    >
                                        <XMarkIcon
                                            class="h-4 w-4 text-red-500"
                                        />
                                    </Button>
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addVariantOption"
                            >
                                + Yeni Seçenek Ekle
                            </Button>
                        </div>

                        <div
                            v-if="productForm.variants.length > 0"
                            class="overflow-hidden rounded-lg border"
                        >
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="bg-muted text-xs text-muted-foreground uppercase"
                                >
                                    <tr>
                                        <th class="px-4 py-3 font-medium">
                                            Varyant
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            SKU
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            Fiyat (TL)
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            İndirimsiz (TL)
                                        </th>
                                        <th class="px-4 py-3 font-medium">
                                            Stok
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="(
                                            variant, idx
                                        ) in productForm.variants"
                                        :key="idx"
                                        class="bg-background"
                                    >
                                        <td class="px-4 py-3 font-medium">
                                            {{
                                                Object.values(
                                                    variant.options,
                                                ).join(' / ')
                                            }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                v-model="variant.sku"
                                                class="h-8"
                                            />
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                v-model="variant.price"
                                                type="number"
                                                step="0.01"
                                                class="h-8"
                                            />
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                v-model="
                                                    variant.compare_at_price
                                                "
                                                type="number"
                                                step="0.01"
                                                class="h-8"
                                            />
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                v-model="variant.stock_quantity"
                                                type="number"
                                                class="h-8 w-24"
                                                required
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Medya ve Görseller -->
                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Medya ve Görseller
                        </h3>
                    </div>

                    <div class="space-y-4 rounded-lg border bg-muted/5 p-4">
                        <div
                            class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-5"
                        >
                            <!-- Existing Images -->
                            <div
                                v-for="(url, idx) in currentImageUrls"
                                :key="`ext-${idx}`"
                                class="group relative aspect-square overflow-hidden rounded-md border bg-white shadow-sm"
                            >
                                <img
                                    :src="url"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click.prevent="removeExistingImage(idx)"
                                    class="absolute top-1 right-1 rounded-full bg-white/90 p-1 opacity-0 shadow-sm transition group-hover:opacity-100"
                                    title="Görseli kaldır"
                                >
                                    <XMarkIcon
                                        class="size-3.5 text-slate-700"
                                    />
                                </button>
                            </div>

                            <!-- Uploaded File Previews -->
                            <div
                                v-for="(file, idx) in productForm.images"
                                :key="`new-${idx}`"
                                class="group relative aspect-square overflow-hidden rounded-md border border-emerald-200 bg-white shadow-sm"
                            >
                                <img
                                    :src="getObjectUrl(file)"
                                    class="h-full w-full object-cover opacity-90"
                                />
                                <div
                                    class="pointer-events-none absolute inset-0 bg-emerald-500/10"
                                ></div>
                                <button
                                    type="button"
                                    @click.prevent="removeNewFile(idx)"
                                    class="absolute top-1 right-1 rounded-full bg-white/90 p-1 opacity-0 shadow-sm transition group-hover:opacity-100"
                                    title="Yüklemeyi iptal et"
                                >
                                    <XMarkIcon class="size-3.5 text-red-600" />
                                </button>
                            </div>

                            <!-- Upload Button -->
                            <label
                                class="flex aspect-square cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-slate-300 text-slate-500 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-600"
                            >
                                <PhotoIcon class="size-6" />
                                <span
                                    class="mt-2 px-1 text-center text-[10px] font-medium tracking-wider uppercase"
                                    >Görsel Seç<br />(Max 6MB)</span
                                >
                                <input
                                    type="file"
                                    multiple
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileSelect"
                                />
                            </label>
                        </div>

                        <div
                            class="flex flex-col gap-2 border-t border-border/50 pt-2"
                        >
                            <Label class="text-xs text-muted-foreground"
                                >Veya link üzerinden manuel görsel ekle:</Label
                            >
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <LinkIcon
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                                    />
                                    <Input
                                        v-model="newImageUrl"
                                        placeholder="https://..."
                                        class="bg-background pl-9"
                                        @keydown.enter.prevent="addUrl"
                                    />
                                </div>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    @click="addUrl"
                                    >URL Ekle</Button
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon: Durum, Fiyat, Stok, SEO, Kategori -->
            <div class="space-y-8">
                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Organizasyon
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>Yayın Durumu</Label>
                            <Select v-model="productForm.status">
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
                        <div class="space-y-2">
                            <Label>SKU Kodu</Label>
                            <Input
                                v-model="productForm.sku"
                                maxlength="80"
                                placeholder="Barkod veya Stok Kodu"
                                class="bg-background"
                            />
                            <InputError :message="productForm.errors.sku" />
                        </div>
                        <div class="space-y-2">
                            <Label>Kategori</Label>
                            <Select v-model="productForm.category_id">
                                <SelectTrigger class="bg-background"
                                    ><SelectValue placeholder="Kategori Seçin"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Seçimsiz</SelectItem>
                                    <SelectItem
                                        v-for="category in props.categories"
                                        :key="category.id"
                                        :value="String(category.id)"
                                    >
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <Label>Marka</Label>
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-emerald-700 hover:underline"
                                    @click="isBrandDrawerOpen = true"
                                >
                                    Yeni marka
                                </button>
                            </div>
                            <Select v-model="productForm.brand_id">
                                <SelectTrigger class="bg-background"
                                    ><SelectValue placeholder="Marka Seçin"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Seçimsiz</SelectItem>
                                    <SelectItem
                                        v-for="brand in props.brands"
                                        :key="brand.id"
                                        :value="String(brand.id)"
                                    >
                                        {{ brand.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Fiyat & Stok
                        </h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-1">
                        <div class="space-y-2">
                            <Label>Satış Fiyatı (TL)</Label>
                            <Input
                                v-model="productForm.price"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                class="bg-background"
                            />
                            <InputError :message="productForm.errors.price" />
                        </div>
                        <div class="space-y-2">
                            <Label>İndirimsiz Fiyat (Opsiyonel)</Label>
                            <Input
                                v-model="productForm.compare_at_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="bg-background"
                            />
                        </div>
                        <div class="col-span-2 space-y-2 lg:col-span-1">
                            <Label>Mevcut Stok Adedi</Label>
                            <Input
                                v-model="productForm.stock_quantity"
                                type="number"
                                min="0"
                                required
                                class="bg-background"
                                :disabled="productForm.variants.length > 0"
                            />
                            <p
                                v-if="productForm.variants.length > 0"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Stok varyantlardan otomatik hesaplanır.
                            </p>
                            <InputError
                                :message="productForm.errors.stock_quantity"
                            />
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 border-b border-border/50 pb-2"
                    >
                        <h3 class="text-sm font-semibold text-foreground">
                            Arama Motoru (SEO)
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>SEO Başlık</Label>
                            <Input
                                v-model="productForm.seo_title"
                                maxlength="180"
                                class="bg-background"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>SEO Açıklama</Label>
                            <textarea
                                v-model="productForm.seo_description"
                                class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-emerald-500"
                                maxlength="320"
                            ></textarea>
                        </div>
                        <div class="space-y-2">
                            <Label>Anahtar Kelimeler</Label>
                            <Input
                                v-model="productForm.seo_keywords"
                                maxlength="500"
                                placeholder="Örn: elektronik, telefon"
                                class="bg-background"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <Sheet :open="isBrandDrawerOpen" @update:open="isBrandDrawerOpen = $event">
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Hızlı marka oluştur</SheetTitle>
                <SheetDescription>
                    Yeni marka gerçek brands tablosuna kaydedilir ve ürünlerde
                    seçilebilir.
                </SheetDescription>
            </SheetHeader>

            <form class="mt-8 space-y-5" @submit.prevent="submitBrand">
                <div class="grid gap-2">
                    <Label>Marka adı</Label>
                    <Input v-model="brandForm.name" required maxlength="180" />
                    <InputError :message="brandForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label>Açıklama</Label>
                    <textarea
                        v-model="brandForm.description"
                        maxlength="1000"
                        class="min-h-24 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-white/10 dark:bg-[#07110f] dark:text-white"
                    />
                </div>
                <div class="grid gap-2">
                    <Label>Durum</Label>
                    <Select v-model="brandForm.status">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="published">Yayında</SelectItem>
                            <SelectItem value="draft">Taslak</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex justify-end gap-2 border-t pt-5">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isBrandDrawerOpen = false"
                        >Vazgeç</Button
                    >
                    <Button type="submit" :disabled="brandForm.processing"
                        >Markayı kaydet</Button
                    >
                </div>
            </form>
        </SheetContent>
    </Sheet>
    <Sheet :open="isAiDrawerOpen" @update:open="isAiDrawerOpen = $event">
        <SheetContent class="w-full overflow-y-auto sm:max-w-lg">
            <SheetHeader>
                <SheetTitle>Gemini AI ürün atölyesi</SheetTitle>
                <SheetDescription>
                    Gemini API yapılandırıldıysa mevcut ürün formuna ürün metni,
                    SEO ve görsel brief önerisi üretir.
                </SheetDescription>
            </SheetHeader>

            <div class="mt-8 space-y-5">
                <div class="rounded-lg border bg-muted/30 p-4 text-sm">
                    <p class="font-semibold">Kullanılan gerçek form verisi</p>
                    <dl class="mt-3 grid gap-2">
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">Ürün</dt>
                            <dd class="font-medium">
                                {{ productForm.name || '-' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">SKU</dt>
                            <dd class="font-medium">
                                {{ productForm.sku || '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="grid gap-2">
                    <Label>Ürün bilgisi / görsel brief notu</Label>
                    <textarea
                        v-model="aiForm.notes"
                        maxlength="2000"
                        class="min-h-36 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-white/10 dark:bg-[#07110f] dark:text-white"
                        placeholder="Ürünün gerçek teknik özelliklerini, hedef kitleyi veya görsel isteğini yazın. Bilinmeyen özellik uydurulmaz."
                    />
                </div>

                <div
                    v-if="aiError"
                    class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"
                >
                    {{ aiError }}
                </div>

                <div
                    class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                >
                    Görsel üretimi için ayrı bir görsel sağlayıcı veya Gemini
                    görsel modeli yapılandırılmalıdır. Bu atölye şu an gerçek
                    Gemini metin cevabından ürün görsel brief'i üretir; sahte
                    görsel dosyası oluşturmaz.
                </div>

                <div class="flex justify-end gap-2 border-t pt-5">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isAiDrawerOpen = false"
                        >Kapat</Button
                    >
                    <Button
                        type="button"
                        :disabled="productForm.processing"
                        @click="generateWithAi"
                    >
                        <SparklesIcon class="mr-2 h-4 w-4" />
                        Gemini ile doldur
                    </Button>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
