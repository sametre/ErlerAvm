<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, Truck, X } from '@lucide/vue';
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

type Metric = {
    label: string;
    value: number;
};

type OrderRow = {
    public_id: string;
    order_number: string;
    email: string;
    status: string;
    status_label: string;
    payment_status: string;
    shipping_address?: {
        name: string;
        phone: string;
        address: string;
        city: string;
        district: string;
    } | null;
    shipment?: {
        status: string;
        status_label: string;
        shipment_provider_id?: number | null;
        provider_name?: string | null;
        tracking_number?: string | null;
        tracking_url?: string | null;
        customer_notified_at?: string | null;
        admin_note?: string | null;
    } | null;
    created_at?: string | null;
};

type ShipmentProvider = {
    id: number;
    public_id: string;
    name: string;
    code: string;
    integration_mode: string;
};

const props = defineProps<{
    filters: {
        q: string | null;
    };
    metrics: Metric[];
    orders: {
        data: OrderRow[];
        links?: { url: string | null; label: string; active: boolean }[];
    };
    shipmentProviders: ShipmentProvider[];
}>();

const searchQuery = ref(props.filters?.q || '');
const selectedOrder = ref<OrderRow | null>(null);
const isDrawerOpen = ref(false);
const shipmentForm = useForm({
    shipment_provider_id: '',
    tracking_number: '',
    status: 'preparing',
    admin_note: '',
    notify_customer: true,
});

function applySearch() {
    router.get(
        '/kargo',
        { q: searchQuery.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function openShipmentDrawer(order: OrderRow): void {
    selectedOrder.value = order;
    shipmentForm.shipment_provider_id = order.shipment?.shipment_provider_id
        ? String(order.shipment.shipment_provider_id)
        : props.shipmentProviders[0]?.id
          ? String(props.shipmentProviders[0].id)
          : '';
    shipmentForm.tracking_number = order.shipment?.tracking_number || '';
    shipmentForm.status =
        order.shipment?.status ||
        (order.status === 'shipped' ? 'shipped' : 'preparing');
    shipmentForm.admin_note = order.shipment?.admin_note || '';
    shipmentForm.notify_customer = !order.shipment?.customer_notified_at;
    shipmentForm.clearErrors();
    isDrawerOpen.value = true;
}

function submitShipment(): void {
    if (!selectedOrder.value) return;

    shipmentForm.patch(`/kargo/${selectedOrder.value.public_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            router.reload({ only: ['orders', 'metrics'] });
        },
    });
}
</script>

<template>
    <Head title="Kargo Yönetimi" />

    <div class="space-y-6 p-4">
        <section
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-primary">Admin operasyon</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Kargo Yönetimi
                </h1>
                <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                    Kargo hazırlık ve gönderi operasyonları gerçek siparişlerden
                    beslenir. Kargo takip numarası ve kargo şirketi bilgileri
                    sipariş detayından güncellenir.
                </p>
            </div>

            <form @submit.prevent="applySearch" class="relative w-full md:w-80">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Sipariş no veya e-posta ara..."
                    class="pl-9"
                />
            </form>
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

        <section class="overflow-hidden rounded-lg border bg-card">
            <div class="flex items-center gap-2 border-b p-4">
                <Truck class="size-5 text-primary" />
                <h2 class="font-semibold">Kargo ve Gönderi Listesi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-220 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Sipariş</th>
                            <th class="px-4 py-3 font-medium">Müşteri</th>
                            <th class="px-4 py-3 font-medium">Adres Bilgisi</th>
                            <th class="px-4 py-3 font-medium">Durum</th>
                            <th class="px-4 py-3 font-medium">Kargo Firması</th>
                            <th class="px-4 py-3 font-medium">Takip No</th>
                            <th class="px-4 py-3 font-medium">Tarih</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="order in props.orders.data"
                            :key="order.public_id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-semibold">
                                {{ order.order_number }}
                            </td>
                            <td class="px-4 py-3">{{ order.email }}</td>
                            <td class="px-4 py-3">
                                <div
                                    class="max-w-[200px] truncate text-xs"
                                    :title="order.shipping_address?.address"
                                >
                                    {{ order.shipping_address?.district }},
                                    {{ order.shipping_address?.city }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        order.status === 'shipped'
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'
                                    "
                                >
                                    {{ order.status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ order.shipment?.provider_name || '-' }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                <a
                                    v-if="order.shipment?.tracking_url"
                                    :href="order.shipment.tracking_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="text-primary hover:underline"
                                >
                                    {{ order.shipment.tracking_number }}
                                </a>
                                <span v-else>{{
                                    order.shipment?.tracking_number || '-'
                                }}</span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ order.created_at || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openShipmentDrawer(order)"
                                    >
                                        Kargo Yönet
                                    </Button>
                                    <Button as-child variant="ghost" size="sm">
                                        <Link
                                            :href="`/siparisler/${order.public_id}`"
                                            >Detay</Link
                                        >
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.orders.data.length">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Kargo bekleyen veya kargoda olan sipariş yok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div
        v-if="isDrawerOpen && selectedOrder"
        class="fixed inset-0 z-50 bg-black/30"
        @click.self="isDrawerOpen = false"
    >
        <aside
            class="ml-auto flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-[#07110f]"
        >
            <header class="flex items-start justify-between border-b p-5">
                <div>
                    <p class="text-sm font-medium text-primary">
                        Gönderi yönetimi
                    </p>
                    <h2 class="text-xl font-semibold">
                        {{ selectedOrder.order_number }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ selectedOrder.email }}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-full p-2 text-muted-foreground hover:bg-muted hover:text-foreground"
                    @click="isDrawerOpen = false"
                    aria-label="Kapat"
                >
                    <X class="size-5" />
                </button>
            </header>

            <form class="flex flex-1 flex-col" @submit.prevent="submitShipment">
                <div class="flex-1 space-y-5 overflow-y-auto p-5">
                    <section class="rounded-lg border p-4">
                        <h3 class="font-semibold">Teslimat adresi</h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ selectedOrder.shipping_address?.address || '-' }}
                        </p>
                        <p class="mt-1 text-sm font-medium">
                            {{
                                selectedOrder.shipping_address?.district || '-'
                            }}
                            / {{ selectedOrder.shipping_address?.city || '-' }}
                        </p>
                    </section>

                    <div class="grid gap-2">
                        <Label>Kargo firması</Label>
                        <Select v-model="shipmentForm.shipment_provider_id">
                            <SelectTrigger
                                ><SelectValue placeholder="Firma seçin"
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="provider in props.shipmentProviders"
                                    :key="provider.public_id"
                                    :value="String(provider.id)"
                                >
                                    {{ provider.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="shipmentForm.errors.shipment_provider_id"
                            class="text-sm text-destructive"
                        >
                            {{ shipmentForm.errors.shipment_provider_id }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label>Takip numarası</Label>
                        <Input
                            v-model="shipmentForm.tracking_number"
                            maxlength="120"
                            required
                        />
                        <p
                            v-if="shipmentForm.errors.tracking_number"
                            class="text-sm text-destructive"
                        >
                            {{ shipmentForm.errors.tracking_number }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label>Kargo durumu</Label>
                        <Select v-model="shipmentForm.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="preparing"
                                    >Hazırlanıyor</SelectItem
                                >
                                <SelectItem value="shipped">Kargoda</SelectItem>
                                <SelectItem value="delivered"
                                    >Teslim edildi</SelectItem
                                >
                                <SelectItem value="cancelled"
                                    >İptal edildi</SelectItem
                                >
                                <SelectItem value="returned"
                                    >İade sürecinde</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label>Admin notu</Label>
                        <textarea
                            v-model="shipmentForm.admin_note"
                            maxlength="1000"
                            class="min-h-24 rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="Kargo operasyonu için iç not..."
                        />
                    </div>

                    <label
                        class="flex items-start gap-3 rounded-lg border p-4 text-sm"
                    >
                        <input
                            v-model="shipmentForm.notify_customer"
                            type="checkbox"
                            class="mt-1 size-4 rounded border-input"
                        />
                        <span>
                            <span class="block font-semibold"
                                >Müşteri hesabında bildir</span
                            >
                            <span class="text-muted-foreground">
                                Takip numarası ve kargo firması müşterinin
                                sipariş detayında gösterilir.
                            </span>
                        </span>
                    </label>
                </div>

                <footer class="grid gap-3 border-t p-5 sm:grid-cols-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isDrawerOpen = false"
                        >Vazgeç</Button
                    >
                    <Button type="submit" :disabled="shipmentForm.processing"
                        >Kargo bilgisini kaydet</Button
                    >
                </footer>
            </form>
        </aside>
    </div>
</template>
