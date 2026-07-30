<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Home, LogIn, RefreshCw } from '@lucide/vue';
import { computed } from 'vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/components/storefront/StorefrontHeader.vue';
import { Button } from '@/components/ui/button';
import { home, login } from '@/routes';

const props = defineProps<{
    status: 400 | 401 | 403 | 404 | 405 | 419 | 422 | 429 | 500 | 503;
    requestId?: string | null;
}>();

type ErrorContent = {
    title: string;
    description: string;
    eyebrow: string;
    primaryLabel: string;
    primaryHref?: ReturnType<typeof home> | ReturnType<typeof login>;
    reload?: boolean;
    emoji: string;
};

const content = computed<ErrorContent>(() => {
    switch (props.status) {
        case 400:
            return {
                eyebrow: 'Geçersiz istek',
                title: 'Bu istek işlenemedi',
                description:
                    'Bağlantı bozuk, parametre hatalı veya sepet isteği güvenli doğrulamadan geçmedi.',
                primaryLabel: 'Ana sayfaya dön',
                primaryHref: home(),
                emoji: 'Face%20with%20spiral%20eyes/3D/face_with_spiral_eyes_3d.png',
            };
        case 401:
            return {
                eyebrow: 'Giriş gerekli',
                title: 'Devam etmek için giriş yapın',
                description:
                    'Hesabım, siparişlerim, favoriler veya ödeme adımı için oturum açmanız gerekiyor.',
                primaryLabel: 'Giriş yap',
                primaryHref: login(),
                emoji: 'Locked/3D/locked_3d.png',
            };
        case 403:
            return {
                eyebrow: 'Yetkisiz erişim',
                title: 'Bu sayfaya erişim izniniz yok',
                description:
                    'Başkasına ait sipariş, admin alanı veya kısıtlı hesap işlemlerine erişemezsiniz.',
                primaryLabel: 'Ana sayfaya dön',
                primaryHref: home(),
                emoji: 'Prohibited/3D/prohibited_3d.png',
            };
        case 404:
            return {
                eyebrow: 'Sayfa bulunamadı',
                title: 'Aradığınız sayfa yayında değil',
                description:
                    'Ürün silinmiş, kampanya süresi bitmiş veya bağlantı yanlış yazılmış olabilir.',
                primaryLabel: 'Ana sayfaya dön',
                primaryHref: home(),
                emoji: 'Magnifying%20glass%20tilted%20left/3D/magnifying_glass_tilted_left_3d.png',
            };
        case 405:
            return {
                eyebrow: 'Yanlış işlem yöntemi',
                title: 'Bu işlem bu adresten yapılamaz',
                description:
                    'Form veya API isteği beklenen HTTP yöntemiyle gönderilmedi. Sayfayı yenileyip tekrar deneyin.',
                primaryLabel: 'Sayfayı yenile',
                reload: true,
                emoji: 'Stop%20sign/3D/stop_sign_3d.png',
            };
        case 419:
            return {
                eyebrow: 'Oturum süresi doldu',
                title: 'Form güvenlik süresi doldu',
                description:
                    'Özellikle checkout veya uzun süre açık kalan formlarda CSRF/session süresi bitebilir. Sayfayı yenileyip tekrar gönderin.',
                primaryLabel: 'Sayfayı yenile',
                reload: true,
                emoji: 'Hourglass%20done/3D/hourglass_done_3d.png',
            };
        case 422:
            return {
                eyebrow: 'Form doğrulanamadı',
                title: 'Bazı bilgiler eksik veya hatalı',
                description:
                    'Kayıt, adres, ödeme veya iade formunda düzeltilmesi gereken alanlar var.',
                primaryLabel: 'Sayfayı yenile',
                reload: true,
                emoji: 'Memo/3D/memo_3d.png',
            };
        case 429:
            return {
                eyebrow: 'Çok fazla istek',
                title: 'Kısa sürede çok fazla işlem yapıldı',
                description:
                    'Güvenlik nedeniyle isteğiniz sınırlandı. Birkaç dakika sonra tekrar deneyin.',
                primaryLabel: 'Tekrar dene',
                reload: true,
                emoji: 'Turtle/3D/turtle_3d.png',
            };
        case 503:
            return {
                eyebrow: 'Bakım modu',
                title: 'ErlerAVM kısa süreli bakımda',
                description:
                    'Deploy, sistem bakımı veya geçici servis kesintisi nedeniyle şu anda işlem alamıyoruz.',
                primaryLabel: 'Tekrar dene',
                reload: true,
                emoji: 'Construction/3D/construction_3d.png',
            };
        default:
            return {
                eyebrow: 'Sunucu hatası',
                title: 'Beklenmeyen bir hata oluştu',
                description:
                    'Teknik detaylar kullanıcıya gösterilmez. Hata loglandıysa destek ekibi tarafından incelenmelidir.',
                primaryLabel: 'Ana sayfaya dön',
                primaryHref: home(),
                emoji: 'Collision/3D/collision_3d.png',
            };
    }
});

const emojiUrl = computed(
    () =>
        `https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/${content.value.emoji}`,
);

const reloadPage = () => window.location.reload();
</script>

<template>
    <Head :title="`${status} - ${content.title}`">
        <meta name="robots" content="noindex, follow" />
        <meta name="description" :content="content.description" />
    </Head>

    <main class="flex min-h-svh flex-col bg-background text-foreground">
        <StorefrontHeader />
        <section
            class="mx-auto flex w-full max-w-4xl flex-1 items-center justify-center px-6 py-20"
        >
            <div class="flex flex-col items-center justify-center text-center">
                <div class="relative mb-10">
                    <div
                        class="absolute inset-0 -z-10 animate-pulse rounded-full bg-primary/20 blur-3xl"
                    ></div>
                    <img
                        :src="emojiUrl"
                        :alt="`${status} emoji`"
                        class="animate-float h-44 w-44 drop-shadow-2xl sm:h-56 sm:w-56"
                    />
                </div>

                <p
                    class="text-sm font-bold tracking-widest text-primary uppercase"
                >
                    {{ status }} - {{ content.eyebrow }}
                </p>
                <h1
                    class="mt-4 text-4xl font-extrabold tracking-tight sm:text-5xl"
                >
                    {{ content.title }}
                </h1>
                <p
                    class="mx-auto mt-6 max-w-xl text-lg leading-8 text-muted-foreground"
                >
                    {{ content.description }}
                </p>

                <p
                    v-if="requestId"
                    class="mt-4 text-xs font-medium text-muted-foreground/80"
                >
                    Destek Kodu:
                    <span class="font-mono text-muted-foreground">{{
                        requestId
                    }}</span>
                </p>

                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <Button
                        v-if="content.reload"
                        type="button"
                        size="lg"
                        class="rounded-full px-8"
                        @click="reloadPage"
                    >
                        <RefreshCw class="mr-2 size-4" />
                        {{ content.primaryLabel }}
                    </Button>
                    <Button v-else as-child size="lg" class="rounded-full px-8">
                        <Link :href="content.primaryHref ?? home()">
                            <LogIn v-if="status === 401" class="mr-2 size-4" />
                            <Home v-else class="mr-2 size-4" />
                            {{ content.primaryLabel }}
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        as-child
                        size="lg"
                        class="rounded-full px-8"
                    >
                        <Link :href="home()"> Ana sayfa </Link>
                    </Button>
                </div>
            </div>
        </section>
        <StorefrontFooter class="mt-auto" />
    </main>
</template>

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}
.animate-float {
    animation: float 4s ease-in-out infinite;
}
</style>
