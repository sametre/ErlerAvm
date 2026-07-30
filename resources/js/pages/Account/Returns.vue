<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AccountLayout from '@/components/AccountLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Package,
    RotateCcw,
    ShieldCheck,
    ChevronDown,
    CheckCircle2,
} from '@lucide/vue';

const props = defineProps<{
    returns: any[];
    eligibleOrders: any[];
}>();

const showNewReturnForm = ref(false);

const form = useForm({
    order_public_id: '',
    customer_note: '',
    items: [] as {
        order_item_id: number;
        quantity: number;
        reason: string;
        _selected: boolean;
    }[],
});

const selectedOrder = computed(() => {
    return props.eligibleOrders.find(
        (o) => o.public_id === form.order_public_id,
    );
});

function onOrderSelect() {
    if (selectedOrder.value) {
        form.items = selectedOrder.value.items.map((item: any) => ({
            order_item_id: item.id,
            quantity: item.quantity,
            reason: 'Vazgeçtim',
            _selected: false,
        }));
    } else {
        form.items = [];
    }
}

function submitReturn() {
    if (form.items.filter((i) => i._selected).length === 0) {
        alert('Lütfen iade etmek istediğiniz en az bir ürünü seçin.');
        return;
    }

    const dataToSubmit = {
        order_public_id: form.order_public_id,
        customer_note: form.customer_note,
        items: form.items
            .filter((i) => i._selected)
            .map((i) => ({
                order_item_id: i.order_item_id,
                quantity: i.quantity,
                reason: i.reason,
            })),
    };

    router.post('/hesabim/iade-degisim', dataToSubmit, {
        preserveScroll: true,
        onSuccess: () => {
            showNewReturnForm.value = false;
            form.reset();
        },
    });
}

const returnReasons = [
    'Vazgeçtim',
    'Bedeni / Ölçüsü uymadı',
    'Defolu / Hasarlı ürün',
    'Beklentimi karşılamadı',
    'Farklı ürün gönderildi',
];

const translateReturnStatus = (status: string) => {
    const map: Record<string, string> = {
        pending: 'Bekliyor',
        approved: 'Onaylandı',
        rejected: 'Reddedildi',
        completed: 'İade Edildi',
    };
    return map[status] || status;
};

const getReturnStatusColor = (status: string) => {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
        completed: 'bg-emerald-100 text-emerald-800',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AccountLayout
        title="İade ve Değişim"
        description="Satış sonrası iade ve değişim taleplerinizi yönetin."
    >
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-bold tracking-tight text-foreground">
                İade Taleplerim
            </h2>
            <Button
                v-if="!showNewReturnForm && eligibleOrders?.length"
                @click="showNewReturnForm = true"
                class="font-semibold shadow-sm"
            >
                <RotateCcw class="mr-2 size-4" />
                Yeni İade Talebi Oluştur
            </Button>
        </div>

        <div
            v-if="showNewReturnForm"
            class="mb-8 rounded-xl border bg-card p-6 shadow-sm"
        >
            <div class="mb-6 border-b pb-4">
                <h3 class="text-lg font-bold tracking-tight">
                    Yeni İade Talebi
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    İade etmek istediğiniz ürünleri ve nedenlerini seçin.
                </p>
            </div>

            <form @submit.prevent="submitReturn" class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-foreground"
                        >Sipariş Seçin</label
                    >
                    <select
                        v-model="form.order_public_id"
                        @change="onOrderSelect"
                        class="w-full rounded-md border bg-background px-3 py-2 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary"
                        required
                    >
                        <option value="" disabled>
                            Lütfen iade edilecek siparişi seçin
                        </option>
                        <option
                            v-for="order in eligibleOrders"
                            :key="order.public_id"
                            :value="order.public_id"
                        >
                            Sipariş #{{ order.order_number }} ({{
                                order.created_at
                            }})
                        </option>
                    </select>
                </div>

                <div v-if="selectedOrder" class="space-y-4">
                    <h4 class="font-semibold text-foreground">
                        İade Edilecek Ürünler
                    </h4>
                    <div class="grid gap-4">
                        <div
                            v-for="(item, index) in selectedOrder.items"
                            :key="item.id"
                            class="flex items-start gap-4 rounded-lg border p-4 transition-colors"
                            :class="
                                form.items[index]._selected
                                    ? 'border-primary bg-primary/5'
                                    : 'bg-background'
                            "
                        >
                            <div class="pt-1">
                                <input
                                    type="checkbox"
                                    v-model="form.items[index]._selected"
                                    class="size-5 rounded border-2 text-primary focus:ring-primary"
                                />
                            </div>
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Package class="size-6" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-foreground">
                                    {{ item.name }}
                                </p>
                                <p
                                    class="mt-0.5 text-sm font-medium text-muted-foreground"
                                >
                                    {{ item.unit_price }} TL
                                </p>

                                <div
                                    v-if="form.items[index]._selected"
                                    class="mt-3 grid gap-3 sm:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="mb-1.5 block text-xs font-semibold text-foreground"
                                            >İade Miktarı (Maks:
                                            {{ item.quantity }})</label
                                        >
                                        <input
                                            type="number"
                                            v-model="form.items[index].quantity"
                                            min="1"
                                            :max="item.quantity"
                                            class="w-full rounded-md border bg-background px-3 py-1.5 text-sm font-medium"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1.5 block text-xs font-semibold text-foreground"
                                            >İade Nedeni</label
                                        >
                                        <select
                                            v-model="form.items[index].reason"
                                            class="w-full rounded-md border bg-background px-3 py-1.5 text-sm font-medium"
                                        >
                                            <option
                                                v-for="reason in returnReasons"
                                                :key="reason"
                                                :value="reason"
                                            >
                                                {{ reason }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-foreground"
                            >Açıklama / Not (Opsiyonel)</label
                        >
                        <textarea
                            v-model="form.customer_note"
                            rows="3"
                            class="w-full rounded-md border bg-background px-3 py-2 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="İade talebinizle ilgili eklemek istediklerinizi yazabilirsiniz..."
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-3 border-t pt-4">
                        <Button
                            type="submit"
                            class="font-semibold"
                            :disabled="form.processing"
                        >
                            İade Talebini Gönder
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="showNewReturnForm = false"
                        >
                            İptal
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <div v-if="returns?.length" class="space-y-4">
            <article
                v-for="ret in returns"
                :key="ret.public_id"
                class="rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4 border-b pb-4"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <h3
                                class="font-bold tracking-tight text-foreground"
                            >
                                İade #{{
                                    ret.public_id.substring(0, 8).toUpperCase()
                                }}
                            </h3>
                            <span
                                :class="[
                                    'rounded-full px-2.5 py-0.5 text-xs font-bold tracking-wider uppercase',
                                    getReturnStatusColor(ret.status),
                                ]"
                            >
                                {{ translateReturnStatus(ret.status) }}
                            </span>
                        </div>
                        <p
                            class="mt-1 text-sm font-medium text-muted-foreground"
                        >
                            Sipariş:
                            <span class="text-foreground">{{
                                ret.order_number
                            }}</span>
                            • {{ ret.created_at }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4
                        class="mb-3 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        İade Edilen Ürünler
                    </h4>
                    <ul class="space-y-2">
                        <li
                            v-for="(item, index) in ret.items"
                            :key="index"
                            class="flex items-start gap-3 rounded-lg border border-border/50 bg-background p-3"
                        >
                            <Package
                                class="mt-0.5 size-5 text-muted-foreground"
                            />
                            <div>
                                <p class="text-sm font-semibold">
                                    {{ item.name }}
                                </p>
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    {{ item.quantity }} Adet • İade Nedeni:
                                    {{ item.reason }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div
                    v-if="ret.shipping_code"
                    class="mt-4 rounded-lg border border-primary/10 bg-primary/5 p-4"
                >
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="size-5 text-primary" />
                        <h4 class="font-bold text-primary">
                            Kargo Gönderi Kodu
                        </h4>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Ürünleri aşağıdaki anlaşmalı kargo kodu ile ücretsiz
                        gönderebilirsiniz:
                    </p>
                    <p
                        class="mt-2 text-2xl font-black tracking-widest text-foreground"
                    >
                        {{ ret.shipping_code }}
                    </p>
                </div>
            </article>
        </div>

        <div
            v-else-if="!showNewReturnForm"
            class="mt-2 flex min-h-[400px] flex-col items-center justify-center rounded-xl border border-dashed bg-card/50 px-6 py-12 text-center shadow-sm"
        >
            <div class="relative mb-6">
                <!-- 3D Emoji Illustration for Empty Returns -->
                <div
                    class="absolute inset-0 animate-pulse rounded-full bg-primary/20 blur-3xl"
                ></div>
                <img
                    src="https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Package/3D/package_3d.png"
                    alt="No Returns"
                    class="relative h-40 w-40 drop-shadow-2xl transition-transform duration-500 ease-out hover:scale-110"
                />
            </div>

            <h2 class="mt-6 text-2xl font-bold tracking-tight text-foreground">
                Hiç iade talebiniz yok
            </h2>

            <p
                class="mt-3 max-w-md text-base leading-relaxed text-muted-foreground"
            >
                Mevcut iade edilebilir siparişleriniz varsa yukarıdaki "Yeni
                İade Talebi Oluştur" butonuna tıklayarak işlemi
                başlatabilirsiniz.
            </p>
        </div>
    </AccountLayout>
</template>
