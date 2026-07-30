<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, LockKeyhole, ShieldCheck } from '@lucide/vue';
import StorefrontPage from '@/components/StorefrontPage.vue';

type AkbankForm = {
    action: string;
    method: string;
    fields: Record<string, string | number | null>;
};

type CardFields = {
    holder_name: string;
    number: string;
    expiry_month: string;
    expiry_year: string;
    cvv: string;
    card_type: string;
};

const props = defineProps<{
    order: {
        order_number: string;
        grand_total: string;
        currency: string;
        items: {
            name: string;
            quantity: number;
            line_total: string;
        }[];
    };
    payment: {
        merchant_oid: string;
        amount_minor: number;
        environment: string;
        payment_model: string;
    };
    akbankForm: AkbankForm;
    cardFields: CardFields;
}>();

const submitting = ref(false);

const months = Array.from({ length: 12 }, (_, index) =>
    String(index + 1).padStart(2, '0'),
);
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 16 }, (_, index) =>
    String(currentYear + index).slice(-2),
);

const visibleHiddenFields = computed(() =>
    Object.entries(props.akbankForm.fields).filter(
        ([, value]) => value !== null && value !== undefined,
    ),
);

function markSubmitting(): void {
    submitting.value = true;
}

function normalizeCardNumber(event: Event): void {
    const input = event.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '').slice(0, 19);
    input.value = digits.replace(/(.{4})/g, '$1 ').trim();
}

function normalizeSecurityCode(event: Event): void {
    const input = event.target as HTMLInputElement;
    input.value = input.value.replace(/\D/g, '').slice(0, 4);
}
</script>

<template>
    <StorefrontPage
        title="Akbank 3D Secure Ödeme"
        description="Sipariş ödemenizi güvenli Akbank Sanal POS akışıyla tamamlayın."
        eyebrow="Ödeme"
        noindex
    >
        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1fr_360px]">
            <section class="rounded-lg border bg-card p-5 shadow-sm sm:p-7">
                <div
                    class="mb-6 flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-11 items-center justify-center rounded-lg bg-primary text-white"
                        >
                            <ShieldCheck class="size-6" />
                        </span>
                        <div>
                            <h1
                                class="text-xl font-black tracking-tight sm:text-2xl"
                            >
                                Akbank 3D Secure
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Sipariş {{ props.order.order_number }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="rounded-md border bg-muted/40 px-3 py-2 text-sm font-semibold"
                    >
                        {{ props.order.grand_total }} {{ props.order.currency }}
                    </div>
                </div>

                <form
                    :action="props.akbankForm.action"
                    :method="props.akbankForm.method"
                    class="grid gap-5"
                    autocomplete="on"
                    @submit="markSubmitting"
                >
                    <input
                        v-for="[name, value] in visibleHiddenFields"
                        :key="name"
                        type="hidden"
                        :name="name"
                        :value="String(value)"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <label
                            class="grid gap-2 text-sm font-semibold md:col-span-2"
                        >
                            Kart üzerindeki ad soyad
                            <input
                                :name="props.cardFields.holder_name"
                                required
                                autocomplete="cc-name"
                                class="h-12 rounded-md border bg-background px-3 text-base transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Ad Soyad"
                            />
                        </label>

                        <label
                            class="grid gap-2 text-sm font-semibold md:col-span-2"
                        >
                            Kart numarası
                            <input
                                :name="props.cardFields.number"
                                required
                                inputmode="numeric"
                                autocomplete="cc-number"
                                maxlength="23"
                                pattern="[0-9 ]{13,23}"
                                @input="normalizeCardNumber"
                                class="h-12 rounded-md border bg-background px-3 text-base transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="0000 0000 0000 0000"
                            />
                        </label>

                        <label class="grid gap-2 text-sm font-semibold">
                            Son kullanma ayı
                            <select
                                :name="props.cardFields.expiry_month"
                                required
                                autocomplete="cc-exp-month"
                                class="h-12 rounded-md border bg-background px-3 transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                                <option value="" disabled selected>Ay</option>
                                <option
                                    v-for="month in months"
                                    :key="month"
                                    :value="month"
                                >
                                    {{ month }}
                                </option>
                            </select>
                        </label>

                        <label class="grid gap-2 text-sm font-semibold">
                            Son kullanma yılı
                            <select
                                :name="props.cardFields.expiry_year"
                                required
                                autocomplete="cc-exp-year"
                                class="h-12 rounded-md border bg-background px-3 transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                                <option value="" disabled selected>Yıl</option>
                                <option
                                    v-for="year in years"
                                    :key="year"
                                    :value="year"
                                >
                                    {{ year }}
                                </option>
                            </select>
                        </label>

                        <label class="grid gap-2 text-sm font-semibold">
                            CVV
                            <input
                                :name="props.cardFields.cvv"
                                required
                                inputmode="numeric"
                                autocomplete="cc-csc"
                                maxlength="4"
                                pattern="[0-9]{3,4}"
                                @input="normalizeSecurityCode"
                                class="h-12 rounded-md border bg-background px-3 text-base transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="000"
                            />
                        </label>

                        <label class="grid gap-2 text-sm font-semibold">
                            Kart tipi
                            <select
                                :name="props.cardFields.card_type"
                                class="h-12 rounded-md border bg-background px-3 transition outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Otomatik</option>
                                <option value="1">Visa</option>
                                <option value="2">Mastercard</option>
                                <option value="3">TROY</option>
                            </select>
                        </label>
                    </div>

                    <div
                        class="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 font-medium text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200"
                    >
                        Kart bilgileri bu form gönderildiğinde doğrudan Akbank
                        Sanal POS adresine iletilir. ERLER AVM kart numarası,
                        CVV veya 3D doğrulama kodu saklamaz.
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-primary px-5 text-sm font-black text-white transition hover:bg-primary/90 disabled:opacity-60"
                    >
                        <CreditCard class="size-4" />
                        {{
                            submitting
                                ? 'Akbank ekranına geçiliyor...'
                                : 'Ödemeyi Güvenli Tamamla'
                        }}
                    </button>
                </form>
            </section>

            <aside class="space-y-4">
                <div class="rounded-lg border bg-card p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold">Sipariş Özeti</h2>
                    <div class="divide-y">
                        <div
                            v-for="item in props.order.items"
                            :key="item.name"
                            class="flex justify-between gap-3 py-3 text-sm"
                        >
                            <div>
                                <p class="font-semibold">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ item.quantity }} adet
                                </p>
                            </div>
                            <p class="font-bold">
                                {{ item.line_total }} {{ props.order.currency }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-4 flex justify-between border-t pt-4 text-base"
                    >
                        <span class="font-bold">Toplam</span>
                        <span class="text-xl font-black text-primary"
                            >{{ props.order.grand_total }}
                            {{ props.order.currency }}</span
                        >
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5 shadow-sm">
                    <div class="mb-3 flex items-center gap-2 font-bold">
                        <LockKeyhole class="size-4 text-primary" />
                        Güvenli işlem
                    </div>
                    <dl class="grid gap-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">Sağlayıcı</dt>
                            <dd class="font-semibold">Akbank Sanal POS</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">Model</dt>
                            <dd class="font-semibold">
                                {{ props.payment.payment_model }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">Ortam</dt>
                            <dd class="font-semibold uppercase">
                                {{ props.payment.environment }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <Link
                    href="/hesabim/siparislerim"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border bg-background px-4 text-sm font-semibold transition hover:bg-muted"
                >
                    <ArrowLeft class="size-4" />
                    Siparişlerime Dön
                </Link>
            </aside>
        </div>
    </StorefrontPage>
</template>
