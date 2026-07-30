<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, MapPin, PackageCheck } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Address = Record<string, string | number | boolean | null>;

type OrderItem = {
    public_id: string;
    product_public_id: string;
    name: string;
    sku?: string | null;
    quantity: number;
    unit_price: string;
    line_total: string;
};

type PaymentAttempt = {
    provider: string;
    merchant_oid: string;
    status: string;
    payment_amount: number;
    total_amount?: number | null;
    currency: string;
    failed_reason_code?: string | null;
    failed_reason_msg?: string | null;
    created_at?: string | null;
    completed_at?: string | null;
};

const props = defineProps<{
    order: {
        public_id: string;
        order_number: string;
        email: string;
        status: string;
        status_label: string;
        payment_status: string;
        payment_status_label: string;
        subtotal: string;
        shipping_total: string;
        discount_total: string;
        tax_total: string;
        grand_total: string;
        currency: string;
        billing_address?: Address | null;
        shipping_address?: Address | null;
        created_at?: string | null;
        updated_at?: string | null;
        customer?: { name?: string; email?: string } | null;
        items: OrderItem[];
    };
    paymentAttempts: PaymentAttempt[];
}>();

const form = useForm({
    status: props.order.status,
    payment_status: props.order.payment_status,
    admin_note: '',
});

const addressLabels: Record<string, string> = {
    type: 'Adres tipi',
    invoice_type: 'Fatura tipi',
    full_name: 'Ad soyad',
    name: 'Ad soyad',
    company_name: 'Firma adı',
    email: 'E-posta',
    phone: 'Telefon',
    city: 'İl',
    district: 'İlçe',
    neighborhood: 'Mahalle',
    address: 'Adres',
    address_line: 'Adres',
    address_line_1: 'Adres satırı 1',
    address_line_2: 'Adres satırı 2',
    postal_code: 'Posta kodu',
    tax_office: 'Vergi dairesi',
    tax_number: 'Vergi numarası',
    identity_number: 'T.C. kimlik no',
    country: 'Ülke',
};

const addressValueLabels: Record<string, string> = {
    individual: 'Bireysel',
    corporate: 'Kurumsal',
    shipping: 'Teslimat',
    billing: 'Fatura',
};

function addressLabel(key: string): string {
    return addressLabels[key] ?? key.replaceAll('_', ' ');
}

function addressValue(
    value: string | number | boolean | null,
): string | number {
    if (value === null || value === '') {
        return '-';
    }

    if (typeof value === 'boolean') {
        return value ? 'Evet' : 'Hayır';
    }

    if (typeof value === 'string') {
        return addressValueLabels[value] ?? value;
    }

    return value;
}

function updateStatus(): void {
    form.patch(`/siparisler/${props.order.public_id}/durum`, {
        preserveScroll: true,
        onSuccess: () => form.reset('admin_note'),
    });
}
</script>

<template>
    <Head :title="`Sipariş ${props.order.order_number}`" />

    <div class="space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <Button as-child variant="ghost" size="sm" class="mb-3 px-0">
                    <Link href="/siparisler">
                        <ArrowLeft class="mr-2 size-4" />
                        Siparişlere dön
                    </Link>
                </Button>
                <p class="text-sm font-medium text-primary">Sipariş detayı</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ props.order.order_number }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ props.order.email }} ·
                    {{ props.order.created_at || '-' }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4 text-right">
                <p class="text-sm text-muted-foreground">Genel toplam</p>
                <p class="text-2xl font-bold">
                    {{ props.order.grand_total }} {{ props.order.currency }}
                </p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-lg border bg-card">
                    <div class="flex items-center gap-2 border-b p-4">
                        <PackageCheck class="size-5 text-primary" />
                        <h2 class="font-semibold">Ürün kalemleri</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-160 text-sm">
                            <thead class="bg-muted/60 text-left">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Ürün</th>
                                    <th class="px-4 py-3 font-medium">SKU</th>
                                    <th class="px-4 py-3 font-medium">Adet</th>
                                    <th class="px-4 py-3 font-medium">Birim</th>
                                    <th class="px-4 py-3 font-medium">
                                        Toplam
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="item in props.order.items"
                                    :key="item.public_id"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ item.name }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ item.sku || '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ item.unit_price }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold">
                                        {{ item.line_total }}
                                    </td>
                                </tr>
                                <tr v-if="!props.order.items.length">
                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        Sipariş kalemi bulunamadı.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <section class="rounded-lg border bg-card p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <MapPin class="size-5 text-primary" />
                            <h2 class="font-semibold">Teslimat adresi</h2>
                        </div>
                        <dl class="grid gap-2 text-sm">
                            <div
                                v-for="(value, key) in props.order
                                    .shipping_address || {}"
                                :key="`shipping-${key}`"
                                class="grid grid-cols-[130px_1fr] gap-3"
                            >
                                <dt class="text-muted-foreground">
                                    {{ addressLabel(String(key)) }}
                                </dt>
                                <dd class="font-medium">
                                    {{ addressValue(value) }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-lg border bg-card p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <CreditCard class="size-5 text-primary" />
                            <h2 class="font-semibold">Fatura bilgisi</h2>
                        </div>
                        <dl class="grid gap-2 text-sm">
                            <div
                                v-for="(value, key) in props.order
                                    .billing_address || {}"
                                :key="`billing-${key}`"
                                class="grid grid-cols-[130px_1fr] gap-3"
                            >
                                <dt class="text-muted-foreground">
                                    {{ addressLabel(String(key)) }}
                                </dt>
                                <dd class="font-medium">
                                    {{ addressValue(value) }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Durum güncelle</h2>
                    <form
                        class="mt-4 grid gap-4"
                        @submit.prevent="updateStatus"
                    >
                        <div class="grid gap-2">
                            <Label>Sipariş durumu</Label>
                            <Select v-model="form.status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="payment_pending"
                                        >Ödeme bekleniyor</SelectItem
                                    >
                                    <SelectItem value="processing"
                                        >Hazırlanıyor</SelectItem
                                    >
                                    <SelectItem value="shipped"
                                        >Kargolandı</SelectItem
                                    >
                                    <SelectItem value="completed"
                                        >Tamamlandı</SelectItem
                                    >
                                    <SelectItem value="cancelled"
                                        >İptal edildi</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Ödeme durumu</Label>
                            <Select v-model="form.payment_status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending"
                                        >Bekliyor</SelectItem
                                    >
                                    <SelectItem value="paid">Ödendi</SelectItem>
                                    <SelectItem value="failed"
                                        >Başarısız</SelectItem
                                    >
                                    <SelectItem value="refunded"
                                        >İade edildi</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.payment_status" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Admin notu</Label>
                            <textarea
                                v-model="form.admin_note"
                                class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                maxlength="1000"
                            />
                            <InputError :message="form.errors.admin_note" />
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            Durumu kaydet
                        </Button>
                    </form>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Özet</h2>
                    <dl class="mt-4 grid gap-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Ara toplam</dt>
                            <dd>{{ props.order.subtotal }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Kargo</dt>
                            <dd>{{ props.order.shipping_total }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">İndirim</dt>
                            <dd>{{ props.order.discount_total }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Vergi</dt>
                            <dd>{{ props.order.tax_total }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">Ödeme denemeleri</h2>
                    <div class="mt-4 grid gap-3">
                        <div
                            v-for="attempt in props.paymentAttempts"
                            :key="attempt.merchant_oid"
                            class="rounded-md border p-3 text-sm"
                        >
                            <p class="font-semibold">
                                {{ attempt.provider }} · {{ attempt.status }}
                            </p>
                            <p class="mt-1 text-muted-foreground">
                                {{ attempt.merchant_oid }}
                            </p>
                            <p
                                v-if="attempt.failed_reason_msg"
                                class="mt-2 text-destructive"
                            >
                                {{ attempt.failed_reason_msg }}
                            </p>
                        </div>
                        <p
                            v-if="!props.paymentAttempts.length"
                            class="text-sm text-muted-foreground"
                        >
                            Ödeme denemesi bulunamadı.
                        </p>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</template>
