<script setup lang="ts">
import { ref, onMounted } from 'vue';

const showBanner = ref(false);
const showPreferences = ref(false);

const preferences = ref({
    necessary: true, // Always true
    analytics: false,
    marketing: false,
});

onMounted(() => {
    const consent = localStorage.getItem('erler_cookie_consent');
    if (!consent) {
        // Gecikmeli göster (Kutu animasyonundan sonra)
        setTimeout(() => {
            showBanner.value = true;
        }, 3000);
    } else {
        preferences.value = JSON.parse(consent);
    }
});

function saveConsent(type: 'all' | 'selected' | 'reject') {
    if (type === 'all') {
        preferences.value.analytics = true;
        preferences.value.marketing = true;
    } else if (type === 'reject') {
        preferences.value.analytics = false;
        preferences.value.marketing = false;
    }

    // Save to LocalStorage
    localStorage.setItem(
        'erler_cookie_consent',
        JSON.stringify(preferences.value),
    );

    // Mock: Send log to server (Gerçekçi "tüm kayıtları toplayalım" yapısı)
    const logData = {
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        consent: preferences.value,
        userId: null, // Gerekirse kullanıcı id eklenebilir
    };

    console.log('[Cookie System] Kayıt loglandı:', logData);

    // Gerçek senaryoda bu bir API isteği olurdu:
    // fetch('/api/cookie-logs', { method: 'POST', body: JSON.stringify(logData) });

    showBanner.value = false;
    showPreferences.value = false;
}
</script>

<template>
    <!-- Simple Banner (Bottom Left) -->
    <div
        v-if="showBanner && !showPreferences"
        class="fixed bottom-4 left-4 z-50 w-full max-w-sm transform animate-in rounded-xl border border-slate-200 bg-white p-5 shadow-2xl transition-all slide-in-from-bottom-5 fade-in"
    >
        <div class="mb-4 flex items-start gap-3">
            <div class="rounded-full bg-primary/10 p-2 text-primary">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"
                    />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">
                    Gizliliğinize Önem Veriyoruz
                </h3>
                <p class="mt-1 text-xs leading-relaxed text-slate-600">
                    Size daha iyi bir alışveriş deneyimi sunabilmek için yasal
                    düzenlemelere uygun çerezler kullanıyoruz.
                </p>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <button
                @click="saveConsent('all')"
                class="w-full rounded-lg bg-primary py-2 text-sm font-bold text-white transition hover:bg-primary/90"
            >
                Tümünü Kabul Et
            </button>
            <div class="flex gap-2">
                <button
                    @click="saveConsent('reject')"
                    class="flex-1 rounded-lg bg-slate-100 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200"
                >
                    Reddet
                </button>
                <button
                    @click="showPreferences = true"
                    class="flex-1 rounded-lg border border-slate-300 bg-white py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Ayarlar
                </button>
            </div>
        </div>
    </div>

    <!-- Detailed Preferences Modal -->
    <div
        v-if="showPreferences"
        class="fixed inset-0 z-[100] flex animate-in items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm fade-in"
    >
        <div
            class="w-full max-w-lg animate-in overflow-hidden rounded-2xl bg-white shadow-2xl zoom-in-95"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 p-5"
            >
                <h2 class="text-lg font-bold text-slate-900">
                    Çerez Tercihleri
                </h2>
                <button
                    @click="showPreferences = false"
                    class="text-slate-400 hover:text-slate-600"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="max-h-[60vh] space-y-4 overflow-y-auto bg-slate-50 p-5">
                <!-- Necessary -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900">
                            Zorunlu Çerezler
                        </h4>
                        <span
                            class="rounded-md bg-primary/10 px-2 py-1 text-xs font-bold text-primary"
                            >Zorunlu</span
                        >
                    </div>
                    <p class="text-xs text-slate-600">
                        Alışveriş sepeti, oturum yönetimi ve güvenlik için
                        sitenin çalışması adına teknik olarak zorunlu
                        çerezlerdir. Kapatılamazlar.
                    </p>
                </div>

                <!-- Analytics -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900">
                            Analitik Çerezler
                        </h4>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="preferences.analytics"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-primary peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                            ></div>
                        </label>
                    </div>
                    <p class="text-xs text-slate-600">
                        Hangi sayfaların en çok ziyaret edildiğini anlamamıza ve
                        site performansını iyileştirmemize yardımcı olan anonim
                        istatistik çerezleridir.
                    </p>
                </div>

                <!-- Marketing -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900">
                            Pazarlama ve Reklam
                        </h4>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                type="checkbox"
                                v-model="preferences.marketing"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-primary peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                            ></div>
                        </label>
                    </div>
                    <p class="text-xs text-slate-600">
                        Size özel kampanya ve ürün önerileri sunabilmemiz,
                        sosyal medya platformlarında ilgi alanınıza göre reklam
                        gösterebilmemiz için kullanılır.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 border-t border-slate-100 bg-white p-5">
                <button
                    @click="saveConsent('selected')"
                    class="flex-1 rounded-xl bg-slate-100 py-3 font-bold text-slate-700 transition hover:bg-slate-200"
                >
                    Seçimlerimi Kaydet
                </button>
                <button
                    @click="saveConsent('all')"
                    class="flex-1 rounded-xl bg-primary py-3 font-bold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90"
                >
                    Tümünü Kabul Et
                </button>
            </div>
        </div>
    </div>
</template>
