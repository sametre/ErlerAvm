<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    Bell,
    Heart,
    LogOut,
    MapPin,
    PackageCheck,
    RotateCcw,
    UserRound,
} from '@lucide/vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const logoutForm = useForm({});
function logout() {
    logoutForm.post('/logout');
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-accent"
            >
                <UserRound class="size-4" />
                Hesabım
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel>Müşteri hesabı</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link href="/hesabim">
                    <UserRound class="size-4" />
                    Profilim
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/hesabim/siparislerim">
                    <PackageCheck class="size-4" />
                    Siparişlerim
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/hesabim/adreslerim">
                    <MapPin class="size-4" />
                    Adreslerim
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/hesabim/bildirimler">
                    <Bell class="size-4" />
                    Bildirimler
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/hesabim/favorilerim">
                    <Heart class="size-4" />
                    Favorilerim
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/hesabim/iade-degisim">
                    <RotateCcw class="size-4" />
                    İade ve değişim
                </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <button
                    type="button"
                    class="flex w-full items-center text-destructive focus:bg-destructive/10 focus:text-destructive"
                    @click="logout"
                >
                    <LogOut class="size-4" />
                    Çıkış yap
                </button>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
