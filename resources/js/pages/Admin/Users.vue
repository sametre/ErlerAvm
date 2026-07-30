<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Users, Shield, Key } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Label } from '@/components/ui/label';

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    roles: string[];
    created_at?: string | null;
};

type Metric = {
    label: string;
    value: number;
};

const props = defineProps<{
    metrics: Metric[];
    users: {
        data: UserRow[];
        links?: { url: string | null; label: string; active: boolean }[];
    };
    availableRoles: string[];
}>();

const isRoleDrawerOpen = ref(false);
const selectedUser = ref<UserRow | null>(null);

const roleForm = useForm({
    is_admin: false,
    roles: [] as string[],
    admin_pin: '',
    admin_pin_confirmation: '',
});

function openRoleDrawer(user: UserRow) {
    selectedUser.value = user;
    roleForm.is_admin = user.is_admin;
    roleForm.roles = [...user.roles];
    roleForm.admin_pin = '';
    roleForm.admin_pin_confirmation = '';
    roleForm.clearErrors();
    isRoleDrawerOpen.value = true;
}

function submitRoleUpdate() {
    if (!selectedUser.value) return;

    roleForm.patch(`/kullanicilar/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            roleForm.reset('admin_pin', 'admin_pin_confirmation');
            isRoleDrawerOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Kullanıcılar ve Yetkiler" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Admin operasyon</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Kullanıcılar ve Yetkiler
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                Sistemdeki tüm kullanıcıları görüntüleyebilir, yetki ve
                rollerini düzenleyebilirsiniz.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
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
                <Users class="size-5 text-primary" />
                <h2 class="font-semibold">Kullanıcı Listesi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-220 text-sm">
                    <thead class="bg-muted/60 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kullanıcı Adı</th>
                            <th class="px-4 py-3 font-medium">E-posta</th>
                            <th class="px-4 py-3 font-medium">Sistem Rolü</th>
                            <th class="px-4 py-3 font-medium">Özel Yetkiler</th>
                            <th class="px-4 py-3 font-medium">Kayıt Tarihi</th>
                            <th class="px-4 py-3 text-right font-medium">
                                İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="user in props.users.data"
                            :key="user.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td
                                class="px-4 py-3 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        user.is_admin
                                            ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400'
                                            : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    <Shield
                                        v-if="user.is_admin"
                                        class="size-3"
                                    />
                                    {{
                                        user.is_admin
                                            ? 'Yönetici (Admin)'
                                            : 'Müşteri'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-800 dark:border-emerald-800/30 dark:bg-emerald-900/30 dark:text-emerald-400"
                                    >
                                        {{ role }}
                                    </span>
                                    <span
                                        v-if="!user.roles.length"
                                        class="text-xs text-muted-foreground"
                                        >-</span
                                    >
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ user.created_at || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openRoleDrawer(user)"
                                >
                                    <Key class="mr-2 h-4 w-4" />
                                    Yetki Düzenle
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Role/Permission Management Drawer -->
    <Sheet :open="isRoleDrawerOpen" @update:open="isRoleDrawerOpen = $event">
        <SheetContent class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Yetki Yönetimi</SheetTitle>
                <SheetDescription v-if="selectedUser">
                    <span class="font-semibold text-foreground">{{
                        selectedUser.name
                    }}</span>
                    kullanıcısının yetkilerini düzenliyorsunuz.
                </SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submitRoleUpdate" class="mt-8 space-y-6">
                <!-- Temel Admin Yetkisi -->
                <div class="space-y-3 rounded-lg border bg-muted/20 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-1">
                            <Label class="text-base"
                                >Yönetici Paneli Erişimi</Label
                            >
                            <p class="text-sm text-muted-foreground">
                                Bu kullanıcı admin paneline giriş yapabilir mi?
                                (Eğer kapalıysa sadece müşteri arayüzüne
                                erişebilir)
                            </p>
                        </div>
                        <label
                            class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="roleForm.is_admin"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-primary peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700"
                            ></div>
                        </label>
                    </div>
                    <InputError :message="roleForm.errors.is_admin" />
                </div>

                <div
                    v-if="roleForm.is_admin"
                    class="space-y-3 rounded-lg border p-4"
                >
                    <div class="space-y-1">
                        <Label class="text-base">Admin PIN</Label>
                        <p class="text-sm text-muted-foreground">
                            Boş bırakırsanız mevcut PIN korunur. Yeni PIN tam 6
                            haneli olmalıdır.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="admin-pin">Yeni PIN</Label>
                            <Input
                                id="admin-pin"
                                v-model="roleForm.admin_pin"
                                name="admin_pin"
                                type="password"
                                inputmode="numeric"
                                maxlength="6"
                                autocomplete="new-password"
                                placeholder="••••••"
                            />
                            <InputError :message="roleForm.errors.admin_pin" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="admin-pin-confirmation"
                                >PIN Tekrar</Label
                            >
                            <Input
                                id="admin-pin-confirmation"
                                v-model="roleForm.admin_pin_confirmation"
                                name="admin_pin_confirmation"
                                type="password"
                                inputmode="numeric"
                                maxlength="6"
                                autocomplete="new-password"
                                placeholder="••••••"
                            />
                            <InputError
                                :message="
                                    roleForm.errors.admin_pin_confirmation
                                "
                            />
                        </div>
                    </div>
                </div>

                <!-- Spatie Rolleri -->
                <div class="space-y-4">
                    <Label class="text-base">Özel İzin Grupları</Label>
                    <p class="text-sm text-muted-foreground">
                        Kullanıcının yönetebileceği modülleri seçin.
                    </p>

                    <div class="grid gap-3">
                        <label
                            v-for="role in props.availableRoles"
                            :key="role"
                            class="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            :class="
                                roleForm.roles.includes(role)
                                    ? 'border-primary bg-primary/5 hover:bg-primary/5'
                                    : ''
                            "
                        >
                            <div class="flex items-center gap-3">
                                <Shield
                                    class="h-5 w-5"
                                    :class="
                                        roleForm.roles.includes(role)
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                                <div>
                                    <p
                                        class="font-medium"
                                        :class="
                                            roleForm.roles.includes(role)
                                                ? 'text-foreground'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{ role }} Yönetimi
                                    </p>
                                </div>
                            </div>
                            <input
                                type="checkbox"
                                v-model="roleForm.roles"
                                :value="role"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                        </label>

                        <div
                            v-if="!props.availableRoles.length"
                            class="rounded-lg border bg-muted/10 py-6 text-center text-sm text-muted-foreground"
                        >
                            Sistemde henüz tanımlı rol bulunmuyor.
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t pt-6">
                    <Button
                        type="button"
                        variant="outline"
                        class="mr-2"
                        @click="isRoleDrawerOpen = false"
                        >İptal</Button
                    >
                    <Button type="submit" :disabled="roleForm.processing"
                        >Kaydet</Button
                    >
                </div>
            </form>
        </SheetContent>
    </Sheet>
</template>
