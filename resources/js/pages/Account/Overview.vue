<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    Heart,
    MapPin,
    PackageCheck,
    RotateCcw,
    ShieldCheck,
} from '@lucide/vue';
import AccountLayout from '@/components/AccountLayout.vue';

defineProps<{
    unreadNotifications?: number;
}>();
</script>

<template>
    <AccountLayout
        title="Hesabım"
        description="Profil, adres, sipariş, favori ve iade işlemleri için müşteri merkezi."
    >
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Link
                href="/hesabim/siparislerim"
                class="group flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary hover:shadow-md"
            >
                <div
                    class="mb-4 inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                >
                    <PackageCheck class="size-6" />
                </div>
                <h2 class="text-lg font-semibold">Siparişlerim</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Sipariş, ödeme ve fatura durumlarını takip edin.
                </p>
            </Link>
            <Link
                href="/hesabim/adreslerim"
                class="group flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary hover:shadow-md"
            >
                <div
                    class="mb-4 inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                >
                    <MapPin class="size-6" />
                </div>
                <h2 class="text-lg font-semibold">Adreslerim</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Teslimat ve fatura adreslerinizi yönetin.
                </p>
            </Link>
            <Link
                href="/hesabim/favorilerim"
                class="group flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary hover:shadow-md"
            >
                <div
                    class="mb-4 inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                >
                    <Heart class="size-6" />
                </div>
                <h2 class="text-lg font-semibold">Favorilerim</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Beğendiğiniz ürünleri daha sonra inceleyin.
                </p>
            </Link>
            <Link
                href="/hesabim/bildirimler"
                class="group relative flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary hover:shadow-md"
            >
                <div
                    class="mb-4 inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                >
                    <Bell class="size-6" />
                </div>
                <h2 class="text-lg font-semibold">Bildirimler</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Sipariş ve hesap bildirimlerinizi takip edin.
                </p>
                <span
                    v-if="unreadNotifications"
                    class="absolute top-6 right-6 inline-flex items-center justify-center rounded-full bg-destructive px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm"
                >
                    {{ unreadNotifications }} Yeni
                </span>
            </Link>
            <Link
                href="/hesabim/iade-degisim"
                class="group flex flex-col rounded-xl border bg-card p-6 shadow-sm transition-all hover:border-primary hover:shadow-md"
            >
                <div
                    class="mb-4 inline-flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                >
                    <RotateCcw class="size-6" />
                </div>
                <h2 class="text-lg font-semibold">İade ve değişim</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Satış sonrası taleplerinizi takip edin.
                </p>
            </Link>
        </div>

        <section class="mt-8 rounded-xl border bg-primary/5 p-6 text-primary">
            <div class="flex gap-4">
                <ShieldCheck class="mt-1 size-8 flex-shrink-0" />
                <div>
                    <h2 class="text-lg font-semibold">
                        Güvenli Müşteri Hesabı
                    </h2>
                    <p
                        class="mt-2 text-sm leading-relaxed text-muted-foreground"
                    >
                        Tüm işlemleriniz yüksek güvenlik standartlarıyla
                        korunmaktadır. Hesap bilgilerinizi güvenle yönetebilir,
                        adreslerinizi ve siparişlerinizi tek bir noktadan
                        kolayca kontrol edebilirsiniz.
                    </p>
                </div>
            </div>
        </section>
    </AccountLayout>
</template>
