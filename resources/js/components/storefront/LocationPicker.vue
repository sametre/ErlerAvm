<script setup lang="ts">
import { LocateFixed, MapPin } from '@lucide/vue';
import { onMounted, ref } from 'vue';

const label = ref('Konum seç');
const status = ref<'idle' | 'loading' | 'ready' | 'error'>('idle');

onMounted(() => {
    const saved = window.localStorage.getItem('erleravm.locationLabel');

    if (saved) {
        label.value = saved;
        status.value = 'ready';
    }
});

async function detectLocation(): Promise<void> {
    if (!navigator.geolocation) {
        status.value = 'error';
        label.value = 'Konum desteklenmiyor';

        return;
    }

    status.value = 'loading';
    label.value = 'Konum alınıyor';

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            try {
                const params = new URLSearchParams({
                    latitude: String(position.coords.latitude),
                    longitude: String(position.coords.longitude),
                    localityLanguage: 'tr',
                });
                const response = await fetch(
                    `https://api.bigdatacloud.net/data/reverse-geocode-client?${params.toString()}`,
                );

                if (!response.ok) {
                    throw new Error('Reverse geocode failed');
                }

                const data = (await response.json()) as {
                    city?: string;
                    locality?: string;
                    principalSubdivision?: string;
                };
                const city = data.city || data.principalSubdivision;
                const district = data.locality;
                const nextLabel = [city, district].filter(Boolean).join(' / ');

                label.value = nextLabel || 'Konum alındı';
                status.value = 'ready';
                window.localStorage.setItem(
                    'erleravm.locationLabel',
                    label.value,
                );
            } catch {
                label.value = 'Konum çözümlenemedi';
                status.value = 'error';
            }
        },
        () => {
            label.value = 'Konum izni gerekli';
            status.value = 'error';
        },
        {
            enableHighAccuracy: false,
            maximumAge: 1000 * 60 * 30,
            timeout: 10000,
        },
    );
}
</script>

<template>
    <button
        type="button"
        class="inline-flex max-w-[220px] items-center gap-2 rounded-md px-2 py-1 text-left text-xs font-medium hover:bg-accent"
        @click="detectLocation"
    >
        <MapPin class="size-4 shrink-0 text-primary" />
        <span class="truncate">{{ label }}</span>
        <LocateFixed
            class="size-3 shrink-0"
            :class="
                status === 'loading'
                    ? 'animate-pulse text-primary'
                    : 'text-muted-foreground'
            "
        />
    </button>
</template>
