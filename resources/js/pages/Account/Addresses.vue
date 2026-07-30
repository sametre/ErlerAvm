<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import AccountLayout from '@/components/AccountLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { router, useForm } from '@inertiajs/vue3';
import { CheckCircle2, MapPin, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref, computed } from 'vue';
import turkeyData from '@/lib/turkey.json';

type Address = {
    public_id: string;
    type: 'individual' | 'corporate';
    title: string;
    full_name: string;
    phone: string;
    city: string;
    district?: string | null;
    postal_code?: string | null;
    address_line: string;
    is_default: boolean;
};

defineProps<{
    addresses: Address[];
}>();

const isDialogOpen = ref(false);
const editingId = ref<string | null>(null);

const form = useForm({
    type: 'individual',
    title: '',
    full_name: '',
    phone: '',
    city: '',
    district: '',
    postal_code: '',
    address_line: '',
    is_default: false,
});

function openNewAddressModal() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'individual';
    form.is_default = false;
    isDialogOpen.value = true;
}

function resetForm() {
    isDialogOpen.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
}

const selectedCityData = computed(() =>
    turkeyData.find((c) => c.name === form.city),
);
const availableDistricts = computed(() =>
    selectedCityData.value ? selectedCityData.value.districts : [],
);

function onCityChange() {
    form.district = '';
    // Otomatik posta kodu doldurma (İl plakası + 000)
    if (selectedCityData.value && selectedCityData.value.plate) {
        form.postal_code = `${selectedCityData.value.plate}000`;
    }
}

function editAddress(address: Address) {
    editingId.value = address.public_id;
    form.clearErrors();
    form.type = address.type;
    form.title = address.title;
    form.full_name = address.full_name;
    form.phone = address.phone;
    form.city = address.city;
    form.district = address.district || '';
    form.postal_code = address.postal_code || '';
    form.address_line = address.address_line;
    form.is_default = address.is_default;
    isDialogOpen.value = true;
}

function submit() {
    if (editingId.value) {
        form.patch(`/hesabim/adreslerim/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: resetForm,
        });
        return;
    }

    form.post('/hesabim/adreslerim', {
        preserveScroll: true,
        onSuccess: resetForm,
    });
}

function destroyAddress(address: Address) {
    if (!window.confirm(`${address.title} adresini silmek istiyor musunuz?`)) {
        return;
    }

    router.delete(`/hesabim/adreslerim/${address.public_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            if (editingId.value === address.public_id) {
                resetForm();
            }
        },
    });
}
</script>

<template>
    <AccountLayout
        title="Adreslerim"
        description="Teslimat ve fatura adreslerinizi güvenli şekilde yönetin."
    >
        <div class="mb-6 flex items-center justify-between border-b pb-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">
                    Kayıtlı Adresleriniz
                </h2>
                <p class="mt-1 text-sm font-medium text-muted-foreground">
                    Siparişlerinizde hızlıca kullanabilmek için adreslerinizi
                    güncel tutun.
                </p>
            </div>
            <Button
                type="button"
                class="font-semibold shadow-sm"
                @click="openNewAddressModal"
            >
                <Plus class="mr-2 size-4" />
                Yeni Adres Ekle
            </Button>
        </div>

        <div class="space-y-4">
            <div v-if="addresses.length" class="grid gap-4 md:grid-cols-2">
                <article
                    v-for="address in addresses"
                    :key="address.public_id"
                    class="relative flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary/50 hover:shadow-md"
                    :class="
                        address.is_default
                            ? 'border-primary/30 bg-primary/5'
                            : ''
                    "
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold tracking-tight">
                                    {{ address.title }}
                                </h2>
                                <span
                                    v-if="address.is_default"
                                    class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold tracking-wider text-primary uppercase"
                                >
                                    <CheckCircle2 class="size-3.5" />
                                    Varsayılan
                                </span>
                            </div>
                            <p
                                class="mt-2 text-sm font-medium text-muted-foreground"
                            >
                                {{ address.full_name }} - {{ address.phone }}
                            </p>
                        </div>
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                        >
                            <MapPin class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-4 flex-1 text-sm leading-relaxed text-foreground"
                    >
                        {{ address.address_line }}
                    </p>
                    <p class="mt-2 text-sm font-medium text-muted-foreground">
                        {{ address.district }} / {{ address.city }}
                        <span v-if="address.postal_code">
                            - {{ address.postal_code }}
                        </span>
                    </p>

                    <div class="mt-6 flex gap-3 border-t border-border/50 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1 font-semibold"
                            @click="editAddress(address)"
                        >
                            <Pencil class="mr-2 size-4" />
                            Düzenle
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            class="flex-none font-semibold"
                            @click="destroyAddress(address)"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                </article>
            </div>

            <EmptyState
                v-else
                title="Kayıtlı adres yok"
                description="İlk teslimat veya fatura adresinizi sağ üstteki butona tıklayarak ekleyebilirsiniz."
            />
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent
                class="max-h-[90vh] overflow-y-auto sm:max-w-[700px]"
            >
                <DialogHeader>
                    <DialogTitle class="text-xl">
                        {{ editingId ? 'Adresi Düzenle' : 'Yeni Adres Ekle' }}
                    </DialogTitle>
                    <DialogDescription>
                        Sipariş ve fatura akışında bu bilgiler kullanılacaktır.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="mt-4 grid gap-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            Adres Tipi
                            <select
                                v-model="form.type"
                                class="h-11 rounded-md border bg-background px-3 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                                <option value="individual">Bireysel</option>
                                <option value="corporate">Kurumsal</option>
                            </select>
                            <span
                                v-if="form.errors.type"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.type }}
                            </span>
                        </label>

                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            Adres Başlığı
                            <Input
                                v-model="form.title"
                                maxlength="120"
                                placeholder="Örn: Ev, İş, Yazlık..."
                                class="h-11 font-medium"
                            />
                            <span
                                v-if="form.errors.title"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.title }}
                            </span>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            Ad Soyad / Yetkili
                            <Input
                                v-model="form.full_name"
                                maxlength="180"
                                class="h-11 font-medium"
                            />
                            <span
                                v-if="form.errors.full_name"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.full_name }}
                            </span>
                        </label>

                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            Telefon Numarası
                            <Input
                                v-model="form.phone"
                                maxlength="32"
                                inputmode="tel"
                                placeholder="0555 000 00 00"
                                class="h-11 font-medium"
                            />
                            <span
                                v-if="form.errors.phone"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.phone }}
                            </span>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            İl
                            <select
                                v-model="form.city"
                                @change="onCityChange"
                                class="h-11 rounded-md border bg-background px-3 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary"
                            >
                                <option value="" disabled>İl Seçiniz</option>
                                <option
                                    v-for="city in turkeyData"
                                    :key="city.name"
                                    :value="city.name"
                                >
                                    {{ city.name }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.city"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.city }}
                            </span>
                        </label>

                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            İlçe
                            <select
                                v-model="form.district"
                                :disabled="!form.city"
                                class="h-11 rounded-md border bg-background px-3 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary disabled:opacity-50"
                            >
                                <option value="" disabled>İlçe Seçiniz</option>
                                <option
                                    v-for="district in availableDistricts"
                                    :key="district"
                                    :value="district"
                                >
                                    {{ district }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.district"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.district }}
                            </span>
                        </label>

                        <label
                            class="grid gap-2 text-sm font-semibold text-foreground"
                        >
                            Posta Kodu
                            <Input
                                v-model="form.postal_code"
                                maxlength="20"
                                class="h-11 bg-muted/30 font-medium"
                            />
                            <span
                                v-if="form.errors.postal_code"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.postal_code }}
                            </span>
                        </label>
                    </div>

                    <label
                        class="grid gap-2 text-sm font-semibold text-foreground"
                    >
                        Açık Adres
                        <textarea
                            v-model="form.address_line"
                            maxlength="1000"
                            placeholder="Sokak, bina no, daire no..."
                            class="min-h-36 rounded-md border bg-background px-4 py-3 text-sm font-medium transition-colors focus:border-primary focus:ring-1 focus:ring-primary"
                        />
                        <span
                            v-if="form.errors.address_line"
                            class="text-xs font-medium text-destructive"
                        >
                            {{ form.errors.address_line }}
                        </span>
                    </label>

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 text-sm font-semibold text-foreground transition-colors hover:bg-muted/50"
                    >
                        <input
                            v-model="form.is_default"
                            type="checkbox"
                            class="size-5 rounded border-2 text-primary focus:ring-primary"
                        />
                        Varsayılan adres olarak kullan
                    </label>

                    <div class="mt-2 flex gap-3">
                        <Button
                            type="submit"
                            class="h-12 flex-1 text-sm font-bold shadow-sm"
                            :disabled="form.processing"
                        >
                            <Plus v-if="!editingId" class="mr-2 size-5" />
                            <Pencil v-else class="mr-2 size-5" />
                            {{
                                editingId ? 'Adresi Güncelle' : 'Adresi Kaydet'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-12 px-6 font-semibold"
                            @click="isDialogOpen = false"
                        >
                            Vazgeç
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </AccountLayout>
</template>
