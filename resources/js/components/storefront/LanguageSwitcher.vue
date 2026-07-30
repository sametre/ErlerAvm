<script setup lang="ts">
import { Globe } from '@lucide/vue';
import { useLocalization } from '@/composables/useLocalization';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const { locale, setLocale } = useLocalization();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <button
                class="flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-semibold text-muted-foreground transition-colors hover:bg-slate-100 hover:text-foreground"
                aria-label="Dil Değiştir"
            >
                <Globe class="size-3.5" />
                <span class="uppercase">{{ locale }}</span>
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-32">
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'tr' }"
                @click="setLocale('tr')"
            >
                🇹🇷 Türkçe
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'en' }"
                @click="setLocale('en')"
            >
                🇬🇧 English
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'pt' }"
                @click="setLocale('pt')"
            >
                🇵🇹 Português
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'es' }"
                @click="setLocale('es')"
            >
                🇪🇸 Español
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'ar' }"
                @click="setLocale('ar')"
            >
                🇸🇦 العربية
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer font-medium"
                :class="{ 'bg-primary/10 text-primary': locale === 'ru' }"
                @click="setLocale('ru')"
            >
                🇷🇺 Русский
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
