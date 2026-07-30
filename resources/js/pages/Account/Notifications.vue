<script setup lang="ts">
import AccountLayout from '@/components/AccountLayout.vue';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/vue3';
import { Bell, CheckCheck, Circle } from '@lucide/vue';

type NotificationItem = {
    public_id: string;
    type: string;
    title: string;
    body?: string | null;
    action_url?: string | null;
    read_at?: string | null;
    created_at?: string | null;
};

defineProps<{
    notifications: NotificationItem[];
    unreadCount: number;
}>();

function markRead(notification: NotificationItem) {
    router.patch(
        `/hesabim/bildirimler/${notification.public_id}/okundu`,
        {},
        { preserveScroll: true },
    );
}

function markAllRead() {
    router.patch('/hesabim/bildirimler/okundu', {}, { preserveScroll: true });
}
</script>

<template>
    <AccountLayout
        title="Bildirimler"
        description="Sipariş, adres ve hesap hareketleriniz için bildirim merkezi."
    >
        <div
            class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
        >
            <p class="text-sm font-semibold text-muted-foreground">
                <span class="text-foreground">{{ unreadCount }}</span> okunmamış
                bildirim
            </p>
            <Button
                v-if="unreadCount > 0"
                type="button"
                variant="outline"
                size="sm"
                class="font-semibold transition-all hover:bg-primary hover:text-primary-foreground"
                @click="markAllRead"
            >
                <CheckCheck class="mr-2 size-4" />
                Tümünü Okundu İşaretle
            </Button>
        </div>

        <div v-if="notifications.length" class="space-y-4">
            <article
                v-for="notification in notifications"
                :key="notification.public_id"
                class="relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                :class="
                    !notification.read_at
                        ? 'border-primary/50 bg-primary/5'
                        : ''
                "
            >
                <div
                    v-if="!notification.read_at"
                    class="absolute top-0 bottom-0 left-0 w-1 bg-primary"
                ></div>
                <div class="flex items-start gap-4">
                    <span
                        class="mt-1 flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary shadow-sm"
                    >
                        <Bell class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2
                                class="text-lg font-bold tracking-tight text-foreground"
                            >
                                {{ notification.title }}
                            </h2>
                            <span
                                v-if="!notification.read_at"
                                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary"
                            >
                                <Circle class="size-2 fill-primary" />
                                Yeni
                            </span>
                        </div>
                        <p
                            v-if="notification.body"
                            class="mt-2 text-sm leading-relaxed text-muted-foreground"
                        >
                            {{ notification.body }}
                        </p>
                        <p
                            class="mt-3 text-xs font-medium text-muted-foreground/70"
                        >
                            {{ notification.created_at }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3 pl-16">
                    <Button
                        v-if="!notification.read_at"
                        type="button"
                        size="sm"
                        variant="outline"
                        class="font-semibold hover:bg-primary/10 hover:text-primary"
                        @click="markRead(notification)"
                    >
                        <CheckCheck class="mr-2 size-4" />
                        Okundu Olarak İşaretle
                    </Button>
                    <Button
                        v-if="notification.action_url"
                        as-child
                        size="sm"
                        class="font-bold shadow-sm"
                    >
                        <Link :href="notification.action_url"
                            >Detayları Gör</Link
                        >
                    </Button>
                </div>
            </article>
        </div>

        <div
            v-else
            class="mt-2 flex min-h-[400px] flex-col items-center justify-center rounded-xl border border-dashed bg-card/50 px-6 py-12 text-center shadow-sm"
        >
            <div class="relative mb-6">
                <!-- 3D Emoji Illustration for Empty Notifications -->
                <div
                    class="absolute inset-0 animate-pulse rounded-full bg-primary/20 blur-3xl"
                ></div>
                <img
                    src="https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Bell/3D/bell_3d.png"
                    alt="No Notifications"
                    class="relative h-40 w-40 drop-shadow-2xl transition-transform duration-500 ease-out hover:scale-110"
                />
            </div>

            <h2 class="mt-6 text-2xl font-bold tracking-tight text-foreground">
                Henüz bildiriminiz yok
            </h2>

            <p
                class="mt-3 max-w-md text-base leading-relaxed text-muted-foreground"
            >
                Siparişlerinizle ilgili güncellemeler, yeni kampanyalar ve hesap
                hareketleriniz olduğunda size buradan haber vereceğiz.
            </p>

            <div class="mt-8">
                <Link
                    href="/arama"
                    class="inline-flex h-12 items-center justify-center rounded-lg bg-primary px-8 text-base font-bold text-primary-foreground shadow-sm transition-all hover:scale-105 hover:bg-primary/90 hover:shadow-primary/25 active:scale-95"
                >
                    Alışverişe Başla
                </Link>
            </div>
        </div>
    </AccountLayout>
</template>
