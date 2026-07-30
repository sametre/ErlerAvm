<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Boxes,
    ChartNoAxesCombined,
    ClipboardList,
    FileText,
    Home,
    AppWindow,
    Images,
    Megaphone,
    MonitorCog,
    PackageSearch,
    Settings2,
    SlidersHorizontal,
    Star,
    Truck,
    UserCog,
    Settings,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem, NavGroup } from '@/types';

const page = usePage();

const adminHomeUrl = computed(() => '/');

const mainNavGroups = computed<NavGroup[]>(() => {
    const user = page.props.auth?.user as any;
    if (!user) return [];

    const isSuperAdmin =
        user.is_admin === true &&
        (!user.roles ||
            user.roles.length === 0 ||
            user.roles.includes('Super Admin') ||
            user.roles.includes('Admin'));
    const hasRole = (role: string) =>
        isSuperAdmin || (user.roles && user.roles.includes(role));

    const groups: NavGroup[] = [
        {
            title: 'Genel',
            items: [
                {
                    title: 'Dashboard',
                    href: '/',
                    icon: ChartNoAxesCombined,
                },
            ],
        },
    ];

    const operasyonlar = [];
    if (hasRole('Sipariş'))
        operasyonlar.push({
            title: 'Siparişler',
            href: '/siparisler',
            icon: ClipboardList,
        });
    if (hasRole('Kargo'))
        operasyonlar.push({ title: 'Kargo', href: '/kargo', icon: Truck });
    if (hasRole('Yorumlar'))
        operasyonlar.push({ title: 'Yorumlar', href: '/yorumlar', icon: Star });

    if (operasyonlar.length > 0) {
        groups.push({ title: 'Operasyonlar', items: operasyonlar });
    }

    const katalog = [];
    if (hasRole('Katalog')) {
        katalog.push({ title: 'Stok', href: '/stok', icon: Boxes });
        katalog.push({
            title: 'Kategoriler',
            href: '/categories',
            icon: AppWindow,
        });
    }

    if (katalog.length > 0) {
        groups.push({ title: 'Katalog', items: katalog });
    }

    const vitrin = [];
    if (hasRole('Vitrin')) {
        vitrin.push({ title: 'Menü', href: '/menu', icon: MonitorCog });
        vitrin.push({ title: 'Slider', href: '/slider', icon: Images });
        vitrin.push({ title: 'Afişler', href: '/vitrin', icon: AppWindow });
        vitrin.push({
            title: 'Pop-up Kampanyalar',
            href: '/vitrin/popup',
            icon: Megaphone,
        });
        vitrin.push({ title: 'Sayfalar', href: '/sayfalar', icon: FileText });
        vitrin.push({
            title: 'Kurumsal Sayfalar',
            href: '/kurumsal-sayfalar',
            icon: PackageSearch,
        });
    }
    if (hasRole('Pazarlama'))
        vitrin.push({
            title: 'Pazarlama',
            href: '/pazarlama',
            icon: Megaphone,
        });

    if (vitrin.length > 0) {
        groups.push({ title: 'Vitrin & İçerik', items: vitrin });
    }

    const yonetim = [];
    if (hasRole('Sistem')) {
        yonetim.push({
            title: 'Mağaza Ayarları',
            href: '/magaza-ayarlari',
            icon: Settings2,
        });
        yonetim.push({
            title: 'Kullanıcılar',
            href: '/kullanicilar',
            icon: UserCog,
        });
    }

    if (yonetim.length > 0) {
        groups.push({ title: 'Yönetim', items: yonetim });
    }

    return groups;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Vitrin',
        href: '/',
        icon: Home,
    },
    {
        title: 'Ayarlar',
        href: '/settings/profile',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="adminHomeUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
