<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    CreditCard,
    FileText,
    MapPin,
    ShieldCheck,
    CheckCircle2,
    PackageSearch,
} from '@lucide/vue';
import StorefrontPage from '@/components/StorefrontPage.vue';
import InputError from '@/components/InputError.vue';
import turkeyData from '@/lib/turkey.json';
import { store as checkoutStore } from '@/routes/checkout';

type CartItem = {
    public_id: string;
    name: string;
    price: string;
    quantity: number;
    line_total: string;
    stock_quantity: number;
    image?: string | null;
    url: string;
};

const props = defineProps<{
    cart: {
        items: CartItem[];
        subtotal: string;
        count: number;
    };
}>();

const form = useForm({
    shipping_full_name: '',
    shipping_phone: '',
    shipping_city: '',
    shipping_district: '',
    shipping_address_line: '',
    shipping_postal_code: '',
    billing_type: 'individual',
    billing_same_as_shipping: true,
    billing_full_name: '',
    billing_identity_number: '',
    billing_company_name: '',
    billing_tax_number: '',
    billing_tax_office: '',
    billing_city: '',
    billing_district: '',
    billing_address_line: '',
    billing_postal_code: '',
    notes: '',
});

const cities = computed(() =>
    turkeyData
        .map((city) => city.name)
        .sort((a, b) => a.localeCompare(b, 'tr')),
);

const shippingDistricts = computed(() => {
    const city = turkeyData.find((c) => c.name === form.shipping_city);
    return city
        ? [...city.districts].sort((a, b) => a.localeCompare(b, 'tr'))
        : [];
});

const billingDistricts = computed(() => {
    const city = turkeyData.find((c) => c.name === form.billing_city);
    return city
        ? [...city.districts].sort((a, b) => a.localeCompare(b, 'tr'))
        : [];
});

watch(
    () => form.shipping_city,
    () => {
        form.shipping_district = '';
    },
);

watch(
    () => form.billing_city,
    () => {
        form.billing_district = '';
    },
);

const shippingAddressSummary = computed(() => {
    return `${form.shipping_address_line}, ${form.shipping_district} / ${form.shipping_city}`;
});

const billingAddressSummary = computed(() => {
    if (form.billing_same_as_shipping) return shippingAddressSummary.value;
    return `${form.billing_address_line}, ${form.billing_district} / ${form.billing_city}`;
});

const currentStep = ref(1);

function validateStep1() {
    if (
        !form.shipping_full_name ||
        !form.shipping_phone ||
        !form.shipping_city ||
        !form.shipping_district ||
        !form.shipping_address_line
    ) {
        alert('Lütfen teslimat adresi için gerekli tüm alanları doldurunuz.');
        return false;
    }
    if (!form.billing_same_as_shipping) {
        if (
            !form.billing_city ||
            !form.billing_district ||
            !form.billing_address_line
        ) {
            alert('Lütfen fatura adresi için gerekli tüm alanları doldurunuz.');
            return false;
        }
    }
    return true;
}

function goToStep(step: number) {
    if (step === 2 && currentStep.value === 1) {
        nextStep();
    } else if (step === 3) {
        if (currentStep.value === 1 && !validateStep1()) return;
        currentStep.value = 3;
    } else {
        currentStep.value = step;
    }
}

function nextStep() {
    if (currentStep.value === 1) {
        if (!validateStep1()) return;
        currentStep.value = 2;
    } else if (currentStep.value === 2) {
        currentStep.value = 3;
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function submit(): void {
    form.post(checkoutStore.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <StorefrontPage
        title="Güvenli Ödeme"
        description="Alışverişinizi tamamlamak için adımları takip edin."
        eyebrow="Ödeme"
        noindex
    >
        <div
            v-if="!props.cart.items.length"
            class="rounded-lg border bg-card p-6 text-center"
        >
            <h2 class="text-xl font-semibold">Sepetiniz boş</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Ödeme adımına devam etmek için önce sepetinize ürün ekleyin.
            </p>
            <Link
                href="/arama"
                class="mt-5 inline-flex h-11 items-center justify-center rounded-md bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
            >
                Ürünlere git
            </Link>
        </div>

        <div v-else class="mx-auto max-w-5xl">
            <!-- Tabs -->
            <div class="mb-8 flex space-x-2 rounded-xl bg-muted p-1">
                <button
                    type="button"
                    @click="goToStep(1)"
                    class="flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm leading-5 font-medium transition-all"
                    :class="
                        currentStep === 1
                            ? 'bg-background text-primary shadow'
                            : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
                    "
                >
                    <MapPin class="size-4" />
                    Adres
                </button>
                <button
                    type="button"
                    @click="goToStep(2)"
                    class="flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm leading-5 font-medium transition-all"
                    :class="
                        currentStep === 2
                            ? 'bg-background text-primary shadow'
                            : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
                    "
                >
                    <CreditCard class="size-4" />
                    Ödeme
                </button>
                <button
                    type="button"
                    @click="goToStep(3)"
                    class="flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm leading-5 font-medium transition-all"
                    :class="
                        currentStep === 3
                            ? 'bg-background text-primary shadow'
                            : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
                    "
                >
                    <CheckCircle2 class="size-4" />
                    Onay
                </button>
            </div>

            <form
                class="grid gap-6 lg:grid-cols-[1fr_380px]"
                @submit.prevent="submit"
            >
                <section class="space-y-6">
                    <div v-show="currentStep === 1" class="space-y-6">
                        <div class="rounded-lg border bg-card p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-2">
                                <MapPin class="size-5 text-primary" />
                                <h2 class="text-lg font-bold">
                                    Teslimat Adresi
                                </h2>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="grid gap-2 text-sm">
                                    Ad soyad
                                    <input
                                        v-model="form.shipping_full_name"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                        autocomplete="name"
                                        placeholder="Adınız Soyadınız"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.shipping_full_name
                                        "
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    Telefon
                                    <input
                                        v-model="form.shipping_phone"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                        autocomplete="tel"
                                        placeholder="05XX XXX XX XX"
                                    />
                                    <InputError
                                        :message="form.errors.shipping_phone"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    İl
                                    <select
                                        v-model="form.shipping_city"
                                        class="h-11 rounded-md border bg-background px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    >
                                        <option value="" disabled>
                                            İl Seçiniz
                                        </option>
                                        <option
                                            v-for="city in cities"
                                            :key="city"
                                            :value="city"
                                        >
                                            {{ city }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.shipping_city"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    İlçe
                                    <select
                                        v-model="form.shipping_district"
                                        class="h-11 rounded-md border bg-background px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary disabled:opacity-50"
                                        :disabled="!form.shipping_city"
                                    >
                                        <option value="" disabled>
                                            İlçe Seçiniz
                                        </option>
                                        <option
                                            v-for="district in shippingDistricts"
                                            :key="district"
                                            :value="district"
                                        >
                                            {{ district }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.shipping_district"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm md:col-span-2">
                                    Açık adres
                                    <textarea
                                        v-model="form.shipping_address_line"
                                        rows="3"
                                        class="rounded-md border px-3 py-2 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                        placeholder="Mahalle, sokak, bina no vb."
                                    />
                                    <InputError
                                        :message="
                                            form.errors.shipping_address_line
                                        "
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    Posta kodu
                                    <input
                                        v-model="form.shipping_postal_code"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.shipping_postal_code
                                        "
                                    />
                                </label>
                            </div>
                        </div>

                        <div class="rounded-lg border bg-card p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-2">
                                <FileText class="size-5 text-primary" />
                                <h2 class="text-lg font-bold">
                                    Fatura Bilgileri
                                </h2>
                            </div>

                            <div class="mb-5 grid gap-3 sm:grid-cols-2">
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-md border p-4 transition"
                                    :class="
                                        form.billing_type === 'individual'
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/50'
                                    "
                                >
                                    <input
                                        v-model="form.billing_type"
                                        type="radio"
                                        value="individual"
                                        class="text-primary focus:ring-primary"
                                    />
                                    <span class="font-medium"
                                        >Bireysel fatura</span
                                    >
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-md border p-4 transition"
                                    :class="
                                        form.billing_type === 'corporate'
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/50'
                                    "
                                >
                                    <input
                                        v-model="form.billing_type"
                                        type="radio"
                                        value="corporate"
                                        class="text-primary focus:ring-primary"
                                    />
                                    <span class="font-medium"
                                        >Kurumsal fatura</span
                                    >
                                </label>
                            </div>

                            <div
                                v-if="form.billing_type === 'individual'"
                                class="grid gap-4 md:grid-cols-2"
                            >
                                <label class="grid gap-2 text-sm">
                                    Fatura adı soyadı
                                    <input
                                        v-model="form.billing_full_name"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="form.errors.billing_full_name"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    T.C. kimlik numarası
                                    <input
                                        v-model="form.billing_identity_number"
                                        maxlength="11"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.billing_identity_number
                                        "
                                    />
                                </label>
                            </div>

                            <div v-else class="grid gap-4 md:grid-cols-3">
                                <label class="grid gap-2 text-sm md:col-span-3">
                                    Firma ünvanı
                                    <input
                                        v-model="form.billing_company_name"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.billing_company_name
                                        "
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    Vergi numarası
                                    <input
                                        v-model="form.billing_tax_number"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.billing_tax_number
                                        "
                                    />
                                </label>
                                <label class="grid gap-2 text-sm md:col-span-2">
                                    Vergi dairesi
                                    <input
                                        v-model="form.billing_tax_office"
                                        class="h-11 rounded-md border px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.billing_tax_office
                                        "
                                    />
                                </label>
                            </div>

                            <label
                                class="mt-5 flex items-center gap-3 text-sm font-medium"
                            >
                                <input
                                    v-model="form.billing_same_as_shipping"
                                    type="checkbox"
                                    class="rounded text-primary focus:ring-primary"
                                />
                                Fatura adresim teslimat adresimle aynı
                            </label>

                            <div
                                v-if="!form.billing_same_as_shipping"
                                class="mt-5 grid gap-4 md:grid-cols-2"
                            >
                                <label class="grid gap-2 text-sm">
                                    Fatura ili
                                    <select
                                        v-model="form.billing_city"
                                        class="h-11 rounded-md border bg-background px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    >
                                        <option value="" disabled>
                                            İl Seçiniz
                                        </option>
                                        <option
                                            v-for="city in cities"
                                            :key="city"
                                            :value="city"
                                        >
                                            {{ city }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.billing_city"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm">
                                    Fatura ilçesi
                                    <select
                                        v-model="form.billing_district"
                                        class="h-11 rounded-md border bg-background px-3 outline-none focus:border-primary focus:ring-1 focus:ring-primary disabled:opacity-50"
                                        :disabled="!form.billing_city"
                                    >
                                        <option value="" disabled>
                                            İlçe Seçiniz
                                        </option>
                                        <option
                                            v-for="district in billingDistricts"
                                            :key="district"
                                            :value="district"
                                        >
                                            {{ district }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.billing_district"
                                    />
                                </label>
                                <label class="grid gap-2 text-sm md:col-span-2">
                                    Fatura adresi
                                    <textarea
                                        v-model="form.billing_address_line"
                                        rows="3"
                                        class="rounded-md border px-3 py-2 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.billing_address_line
                                        "
                                    />
                                </label>
                            </div>

                            <label class="mt-5 grid gap-2 text-sm">
                                Sipariş notu
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    class="rounded-md border px-3 py-2 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Eklemek istediğiniz notlar..."
                                />
                                <InputError :message="form.errors.notes" />
                            </label>
                        </div>
                    </div>

                    <div v-show="currentStep === 2" class="space-y-6">
                        <div class="rounded-lg border bg-card p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-2">
                                <CreditCard class="size-5 text-primary" />
                                <h2 class="text-lg font-bold">Ödeme Yöntemi</h2>
                            </div>

                            <div
                                class="rounded-md border border-primary bg-primary/5 p-5"
                            >
                                <h3 class="mb-2 font-semibold text-primary">
                                    Güvenli 3D Ödeme Altyapısı
                                </h3>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    Siparişi oluşturduktan sonra Akbank 3D
                                    Secure ödeme ekranına geçeceksiniz. Kart
                                    numarası ve CVV ERLER AVM sistemlerinde
                                    saklanmaz.
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        class="flex h-8 w-14 items-center justify-center rounded border bg-white text-xs font-bold text-blue-900 shadow-sm"
                                    >
                                        Visa
                                    </div>
                                    <div
                                        class="flex h-8 w-14 items-center justify-center rounded border bg-white text-xs font-bold text-orange-600 shadow-sm"
                                    >
                                        MC
                                    </div>
                                    <div
                                        class="flex h-8 w-14 items-center justify-center rounded border bg-white text-xs font-bold text-teal-600 shadow-sm"
                                    >
                                        TROY
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 3" class="space-y-6">
                        <div class="rounded-lg border bg-card p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-2">
                                <CheckCircle2 class="size-5 text-primary" />
                                <h2 class="text-lg font-bold">
                                    {{ $t('checkout.place_order') }}
                                </h2>
                            </div>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div
                                    class="space-y-3 rounded-lg bg-muted/50 p-4"
                                >
                                    <h3
                                        class="text-sm font-semibold text-primary"
                                    >
                                        Teslimat Adresi
                                    </h3>
                                    <p class="text-sm font-medium">
                                        {{ form.shipping_full_name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ shippingAddressSummary }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ form.shipping_phone }}
                                    </p>
                                </div>
                                <div
                                    class="space-y-3 rounded-lg bg-muted/50 p-4"
                                >
                                    <h3
                                        class="text-sm font-semibold text-primary"
                                    >
                                        Fatura Adresi
                                    </h3>
                                    <p class="text-sm font-medium">
                                        {{
                                            form.billing_type === 'individual'
                                                ? form.billing_full_name ||
                                                  form.shipping_full_name
                                                : form.billing_company_name
                                        }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ billingAddressSummary }}
                                    </p>
                                </div>
                                <div
                                    class="space-y-3 rounded-lg bg-muted/50 p-4 md:col-span-2"
                                >
                                    <h3
                                        class="text-sm font-semibold text-primary"
                                    >
                                        Ödeme Yöntemi
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Siparişi onayla dedikten sonra Akbank
                                        Sanal POS 3D Secure ödeme adımına
                                        geçilecektir.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="h-fit rounded-lg border bg-card p-6 shadow-sm">
                    <h2 class="border-b pb-4 text-lg font-bold">
                        {{ $t('checkout.order_summary') }}
                    </h2>
                    <div class="mt-4 divide-y">
                        <div
                            v-for="item in props.cart.items"
                            :key="item.public_id"
                            class="grid grid-cols-[auto_1fr_auto] gap-3 py-4 text-sm"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-md border bg-muted"
                            >
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    class="h-full w-full object-cover"
                                />
                                <PackageSearch
                                    v-else
                                    class="size-6 text-muted-foreground opacity-50"
                                />
                            </div>
                            <div class="flex flex-col justify-center">
                                <p class="line-clamp-2 font-semibold">
                                    {{ item.name }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Adet: {{ item.quantity }}
                                </p>
                            </div>
                            <div class="flex items-center">
                                <p class="font-bold">
                                    {{ item.line_total }} TL
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 border-t pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-muted-foreground"
                                >Ara Toplam</span
                            >
                            <span class="font-bold"
                                >{{ props.cart.subtotal }} TL</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-muted-foreground"
                                >Kargo</span
                            >
                            <span class="font-bold text-emerald-600"
                                >Ücretsiz</span
                            >
                        </div>
                        <div class="flex justify-between border-t pt-3 text-lg">
                            <span class="font-bold">Genel Toplam</span>
                            <span class="font-black text-primary"
                                >{{ props.cart.subtotal }} TL</span
                            >
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            v-if="currentStep < 3"
                            type="button"
                            @click="nextStep"
                            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-primary text-sm font-bold text-white transition hover:bg-primary/90"
                        >
                            İleri Adıma Geç
                        </button>

                        <div v-else class="grid grid-cols-[1fr_2fr] gap-3">
                            <button
                                type="button"
                                @click="prevStep"
                                class="inline-flex h-12 w-full items-center justify-center rounded-md border border-input bg-background text-sm font-semibold hover:bg-accent hover:text-accent-foreground"
                            >
                                Geri
                            </button>
                            <button
                                type="submit"
                                class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-primary text-sm font-bold text-white transition hover:bg-primary/90 disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                <ShieldCheck class="size-4" />
                                {{
                                    form.processing
                                        ? 'İşleniyor...'
                                        : 'Siparişi Onayla'
                                }}
                            </button>
                        </div>
                    </div>

                    <p
                        class="mt-4 text-center text-xs leading-5 text-muted-foreground"
                    >
                        Siparişi onayla butonuna tıkladığınızda
                        <strong>Mesafeli Satış Sözleşmesi</strong>'ni kabul
                        etmiş sayılırsınız.
                    </p>
                </aside>
            </form>
        </div>
    </StorefrontPage>
</template>
