<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    PackageCheck,
    MapPin,
    Heart,
    Bell,
    RotateCcw,
    LogOut,
    User,
} from '@lucide/vue';
import StorefrontPage from '@/components/StorefrontPage.vue';
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
}>();

const page = usePage();
const currentPath = computed(() => {
    try {
        return new URL(page.url, window.location.origin).pathname;
    } catch {
        return page.url;
    }
});

const navigation = [
    { name: 'Hesap Özeti', href: '/hesabim', icon: User, exact: true },
    {
        name: 'Siparişlerim',
        href: '/hesabim/siparislerim',
        icon: PackageCheck,
        exact: false,
    },
    {
        name: 'Adreslerim',
        href: '/hesabim/adreslerim',
        icon: MapPin,
        exact: false,
    },
    {
        name: 'Favorilerim',
        href: '/hesabim/favorilerim',
        icon: Heart,
        exact: false,
    },
    {
        name: 'Bildirimler',
        href: '/hesabim/bildirimler',
        icon: Bell,
        exact: false,
    },
    {
        name: 'İade ve Değişim',
        href: '/hesabim/iade-degisim',
        icon: RotateCcw,
        exact: false,
    },
];

const isActive = (item: any) => {
    if (item.exact) {
        return currentPath.value === item.href;
    }
    return currentPath.value.startsWith(item.href);
};
</script>

<template>
    <StorefrontPage
        :title="title"
        :description="description"
        eyebrow="Hesabım"
        noindex
    >
        <div
            class="mt-4 flex flex-col gap-6 lg:mt-8 lg:flex-row lg:items-start lg:gap-8"
        >
            <aside class="w-full flex-shrink-0 lg:w-64">
                <nav
                    class="flex scrollbar-none gap-2 overflow-x-auto rounded-xl border bg-card p-2 pb-2 shadow-sm lg:flex-col lg:gap-1 lg:overflow-visible lg:p-4 lg:pb-4"
                >
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            isActive(item)
                                ? 'bg-primary/10 font-semibold text-primary'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                            'group flex shrink-0 items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-in-out',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            :class="[
                                isActive(item)
                                    ? 'text-primary'
                                    : 'text-muted-foreground group-hover:text-foreground',
                                'mr-2 size-4 flex-shrink-0 transition-colors lg:mr-3 lg:size-5',
                            ]"
                        />
                        {{ item.name }}
                    </Link>

                    <div class="my-2 hidden h-px bg-border lg:block"></div>

                    <Link
                        href="/cikis"
                        method="post"
                        as="button"
                        class="group flex shrink-0 items-center rounded-lg px-3 py-2.5 text-sm font-medium text-destructive transition-all duration-200 ease-in-out hover:bg-destructive/10"
                    >
                        <LogOut
                            class="mr-2 size-4 flex-shrink-0 lg:mr-3 lg:size-5"
                        />
                        <span>Çıkış Yap</span>
                    </Link>
                </nav>
            </aside>
            <main class="w-full min-w-0 flex-1">
                <slot />
            </main>
        </div>
    </StorefrontPage>
</template>
