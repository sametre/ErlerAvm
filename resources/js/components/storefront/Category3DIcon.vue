<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMouseInElement } from '@vueuse/core';

const props = defineProps<{
    title: string;
}>();

const target = ref<HTMLElement | null>(null);
const { elementX, elementY, isOutside, elementHeight, elementWidth } =
    useMouseInElement(target);

// Kategori eşleştirme algoritması
const emoji = computed(() => {
    const name = props.title.toLocaleLowerCase('tr-TR');

    if (name.includes('telefon') || name.includes('tablet')) return '📱';
    if (name.includes('bilgisayar') || name.includes('teknoloji')) return '💻';
    if (name.includes('televizyon') || name.includes('elektronik')) return '📺';
    if (name.includes('kulaklık')) return '🎧';
    if (name.includes('kamera')) return '📷';
    if (name.includes('oyun') || name.includes('konsol')) return '🎮';
    if (name.includes('saat')) return '⌚';

    if (
        name.includes('beyaz') ||
        name.includes('buzdolabı') ||
        name.includes('çamaşır') ||
        name.includes('fırın') ||
        name.includes('ocak')
    )
        return '🧊';
    if (
        name.includes('mobilya') ||
        name.includes('koltuk') ||
        name.includes('yatak')
    )
        return '🛋️';
    if (
        name.includes('dekorasyon') ||
        name.includes('halı') ||
        name.includes('aydınlatma')
    )
        return '🖼️';
    if (
        name.includes('mutfak') ||
        name.includes('tabak') ||
        name.includes('tencere')
    )
        return '🍽️';

    if (
        name.includes('giyim') ||
        name.includes('tişört') ||
        name.includes('pantolon')
    )
        return '👕';
    if (name.includes('ayakkabı')) return '👟';
    if (
        name.includes('kozmetik') ||
        name.includes('parfüm') ||
        name.includes('makyaj')
    )
        return '💄';
    if (name.includes('spor') || name.includes('outdoor')) return '⚽';
    if (name.includes('kitap') || name.includes('kırtasiye')) return '📚';
    if (name.includes('oyuncak') || name.includes('bebek')) return '🧸';

    return '🎁'; // Kampanya / Varsayılan
});

// 3D Tilt Efekti Hesaplama
const cardTransform = computed(() => {
    if (isOutside.value || elementHeight.value === 0) {
        return 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    }

    const maxTilt = 25; // Maksimum eğim derecesi
    const x =
        (elementX.value - elementWidth.value / 2) / (elementWidth.value / 2);
    const y =
        (elementY.value - elementHeight.value / 2) / (elementHeight.value / 2);

    const rotateX = -(y * maxTilt).toFixed(2);
    const rotateY = (x * maxTilt).toFixed(2);

    return `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.15, 1.15, 1.15)`;
});

const shadowTransform = computed(() => {
    if (isOutside.value || elementHeight.value === 0) {
        return 'translateY(0) scale(1)';
    }
    const x =
        (elementX.value - elementWidth.value / 2) / (elementWidth.value / 2);
    const y =
        (elementY.value - elementHeight.value / 2) / (elementHeight.value / 2);

    return `translate(${x * -10}px, ${y * -10 + 15}px) scale(0.9)`;
});
</script>

<template>
    <div
        ref="target"
        class="relative flex h-28 w-full shrink-0 items-center justify-center"
        style="transform-style: preserve-3d"
    >
        <!-- Dinamik 3D Emoji Katmanı -->
        <div
            class="z-10 flex items-center justify-center transition-all duration-200 ease-out"
            :style="{ transform: cardTransform }"
        >
            <span
                class="pointer-events-none text-6xl drop-shadow-[0_15px_15px_rgba(0,0,0,0.2)] select-none"
                style="
                    filter: drop-shadow(0px 10px 15px rgba(0, 0, 0, 0.3))
                        drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.1));
                "
            >
                {{ emoji }}
            </span>
        </div>

        <!-- Yansıma / Gölgelendirme (Zemin Derinliği İçin) -->
        <div
            class="absolute bottom-4 h-4 w-12 rounded-[100%] bg-black/10 blur-md transition-all duration-200 ease-out"
            :style="{ transform: shadowTransform }"
        ></div>
    </div>
</template>
