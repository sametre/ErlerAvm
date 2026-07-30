<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import AccountLayout from '@/components/AccountLayout.vue';
import ProductList from '@/components/ProductList.vue';

defineProps<{
    wishlistProducts: any[];
}>();
</script>

<template>
    <AccountLayout
        title="Favorilerim"
        description="Beğendiğiniz ürünleri listeleyin ve stok durumlarını takip edin."
    >
        <div v-if="wishlistProducts?.length > 0" class="mt-2">
            <ProductList :products="wishlistProducts" />
        </div>

        <div
            v-else
            class="mt-2 flex min-h-[400px] flex-col items-center justify-center rounded-xl border border-dashed bg-card/50 px-6 py-12 text-center shadow-sm"
        >
            <div class="relative mb-6">
                <!-- 3D Emoji Illustration for Empty Wishlist -->
                <div
                    class="absolute inset-0 animate-pulse rounded-full bg-primary/20 blur-3xl"
                ></div>
                <img
                    src="https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Heart%20with%20ribbon/3D/heart_with_ribbon_3d.png"
                    alt="Empty Wishlist"
                    class="relative h-40 w-40 drop-shadow-2xl transition-transform duration-500 ease-out hover:scale-110"
                />
            </div>

            <h2 class="mt-6 text-2xl font-bold tracking-tight text-foreground">
                Favorilerinizde ürün yok
            </h2>

            <p
                class="mt-3 max-w-md text-base leading-relaxed text-muted-foreground"
            >
                Beğendiğiniz ürünleri kalp ikonuna tıklayarak favorilerinize
                ekleyin, daha sonra kolayca ulaşıp satın alın.
            </p>

            <div class="mt-8">
                <Link
                    href="/arama"
                    class="inline-flex h-12 items-center justify-center rounded-lg bg-primary px-8 text-base font-bold text-primary-foreground shadow-sm transition-all hover:scale-105 hover:bg-primary/90 hover:shadow-primary/25 active:scale-95"
                >
                    Ürünleri Keşfet
                </Link>
            </div>
        </div>
    </AccountLayout>
</template>
