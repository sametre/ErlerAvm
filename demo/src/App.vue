<script setup lang="ts">
import { computed, ref } from 'vue';

type Product = {
    brand: 'Arçelik' | 'Bosch';
    model: string;
    name: string;
    category: string;
    price: number;
    oldPrice: number;
    image: string;
    detail: string;
};

const asset = (path: string) => `${import.meta.env.BASE_URL}${path}`;

const products: Product[] = [
    { brand: 'Arçelik', model: '9100 M', name: '9 kg Çamaşır Makinesi', category: 'Çamaşır Makinesi', price: 25999, oldPrice: 28999, image: asset('products/arcelik-9100-m.webp'), detail: '9 kg · 1000 devir · Buhar destekli' },
    { brand: 'Arçelik', model: '6144', name: '4 Programlı Bulaşık Makinesi', category: 'Bulaşık Makinesi', price: 22499, oldPrice: 24999, image: asset('products/arcelik-6144.webp'), detail: '14 kişilik · HijyenMax · 48 dBA' },
    { brand: 'Bosch', model: 'KGN56XIE0N', name: 'Serie 4 No Frost Buzdolabı', category: 'Buzdolabı', price: 59990, oldPrice: 68250, image: asset('products/bosch-kgn56xie0n.webp'), detail: '193 × 70 cm · VitaFresh · Total No Frost' },
    { brand: 'Bosch', model: 'WGA142ZXTR', name: 'Serie 4 Çamaşır Makinesi', category: 'Çamaşır Makinesi', price: 32990, oldPrice: 36990, image: asset('products/bosch-wga142zxtr.webp'), detail: '9 kg · 1200 devir · EcoSilence Drive' },
    { brand: 'Bosch', model: 'SMS4IKW62T', name: 'Serie 4 Bulaşık Makinesi', category: 'Bulaşık Makinesi', price: 31490, oldPrice: 34990, image: asset('products/bosch-sms4ikw62t.webp'), detail: '6 program · Home Connect · VarioSpeed' },
];

const filter = ref<'Tümü' | 'Arçelik' | 'Bosch'>('Tümü');
const cartCount = ref(0);
const visibleProducts = computed(() => filter.value === 'Tümü' ? products : products.filter((product) => product.brand === filter.value));
const currency = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', maximumFractionDigits: 0 });
</script>

<template>
    <div class="page-shell">
        <div class="announcement">Karacabey ve çevresine hızlı teslimat · Mağazadan güvenli alışveriş</div>
        <header>
            <a class="brand" href="#"><img src="/erleravm.png" alt="Erler AVM" /></a>
            <nav><a href="#urunler">Ürünler</a><a href="#markalar">Markalar</a><a href="#iletisim">İletişim</a></nav>
            <button class="cart" type="button" aria-label="Sepet">Sepet <span>{{ cartCount }}</span></button>
        </header>

        <main>
            <section class="hero">
                <div class="hero-copy">
                    <span class="eyebrow">ERLER AVM · KARACABEY</span>
                    <h1>Evinize iyi gelen teknoloji.</h1>
                    <p>Arçelik ve Bosch’un seçili beyaz eşya modellerini Erler AVM güvencesiyle keşfedin.</p>
                    <a class="primary" href="#urunler">Ürünleri keşfet <span>→</span></a>
                </div>
                <div class="hero-card">
                    <span>HAFTANIN SEÇİMİ</span>
                    <img src="/products/bosch-kgn56xie0n.webp" alt="Bosch KGN56XIE0N buzdolabı" />
                    <div><strong>Bosch Serie 4</strong><small>Geniş hacim, yalın tasarım</small></div>
                </div>
            </section>

            <section id="markalar" class="brand-strip">
                <span>Güvendiğiniz markalar</span><strong class="arcelik">arçelik</strong><strong class="bosch">BOSCH</strong><span class="service">Yetkili satış deneyimi</span>
            </section>

            <section id="urunler" class="products-section">
                <div class="section-heading">
                    <div><span class="eyebrow">GERÇEK MODELLER</span><h2>Seçili ürünler</h2></div>
                    <div class="filters"><button v-for="item in ['Tümü', 'Arçelik', 'Bosch']" :key="item" :class="{ active: filter === item }" @click="filter = item as typeof filter">{{ item }}</button></div>
                </div>
                <div class="product-grid">
                    <article v-for="product in visibleProducts" :key="product.model" class="product-card">
                        <div class="product-image"><span class="discount">%{{ Math.round((1 - product.price / product.oldPrice) * 100) }}</span><img :src="product.image" :alt="`${product.brand} ${product.model}`" /></div>
                        <div class="product-body"><span class="product-brand">{{ product.brand }} · {{ product.model }}</span><h3>{{ product.name }}</h3><p>{{ product.detail }}</p><div class="price"><strong>{{ currency.format(product.price) }}</strong><del>{{ currency.format(product.oldPrice) }}</del></div><button type="button" @click="cartCount++">Sepete ekle</button></div>
                    </article>
                </div>
                <p class="demo-note">Demo vitrini — fiyatlar ve sepet işlemleri temsilidir.</p>
            </section>

            <section id="iletisim" class="contact"><div><span class="eyebrow">MAĞAZAMIZA BEKLERİZ</span><h2>Ürünü yakından görün.</h2><p>Runguşpaşa, Bursa Cd. No:87 · Karacabey / Bursa</p></div><a href="https://maps.google.com/?q=Runguşpaşa+Bursa+Cd+No+87+Karacabey+Bursa" target="_blank" rel="noreferrer">Yol tarifi al →</a></section>
        </main>
        <footer><img src="/erleravm.png" alt="Erler AVM" /><span>© {{ new Date().getFullYear() }} Erler AVM · Demo</span></footer>
    </div>
</template>
