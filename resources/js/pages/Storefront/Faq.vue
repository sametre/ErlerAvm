<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ChevronDown,
    MessageCircleQuestion,
    Truck,
    ShieldCheck,
    CreditCard,
} from '@lucide/vue';
import StorefrontFooter from '@/components/storefront/StorefrontFooter.vue';

const props = defineProps<{
    corporateContent?: string | null;
}>();

const openFaq = ref<number | null>(0);

const activeCategory = ref<number>(0);

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const faqCategories = [
    { id: 0, name: 'Genel Sorular', icon: MessageCircleQuestion },
    { id: 1, name: 'Kargo & Teslimat', icon: Truck },
    { id: 2, name: 'İade & Değişim', icon: ShieldCheck },
    { id: 3, name: 'Ödeme Seçenekleri', icon: CreditCard },
];

const allFaqs = [
    {
        categoryId: 0,
        question: 'Üye olmadan alışveriş yapabilir miyim?',
        answer: 'Evet, sitemize üye olmadan da misafir kullanıcı olarak alışveriş yapabilirsiniz. Ancak sipariş takibinizi daha kolay yapabilmek ve kampanyalardan haberdar olmak için üye olmanızı tavsiye ederiz.',
    },
    {
        categoryId: 0,
        question: 'Şifremi unuttum, ne yapmalıyım?',
        answer: "Giriş yap sayfasındaki 'Şifremi Unuttum' bağlantısına tıklayarak sistemimize kayıtlı e-posta adresinizi giriniz. Şifre sıfırlama bağlantısı e-posta adresinize gönderilecektir.",
    },
    {
        categoryId: 0,
        question: 'Mağazalarınız nerede bulunuyor?',
        answer: "Merkez mağazamız Bursa, Karacabey'de yer almaktadır. Detaylı adres ve iletişim bilgilerine sitenin alt kısmındaki 'Mağazalarımız' bağlantısından ulaşabilirsiniz.",
    },

    {
        categoryId: 1,
        question: 'Kargom ne zaman ulaşır?',
        answer: 'Siparişleriniz genellikle 1-3 iş günü içerisinde kargoya teslim edilmektedir. Kargo firmasının teslimat süresi bulunduğunuz ile ve ilçeye göre değişmekle birlikte genellikle 1-3 iş günü sürmektedir.',
    },
    {
        categoryId: 1,
        question: 'Hangi kargo firmaları ile çalışıyorsunuz?',
        answer: "Müşterilerimize en hızlı ve güvenli hizmeti sunabilmek adına Türkiye'nin önde gelen kargo firmaları olan Yurtiçi Kargo, MNG Kargo, PTT Kargo ve Aras Kargo ile çalışmaktayız.",
    },
    {
        categoryId: 1,
        question: 'Kargo ücreti ne kadar?',
        answer: '1500 ₺ ve üzeri tüm siparişlerinizde kargo ücretsizdir. 1500 ₺ altındaki siparişlerinizde ise kargo ücreti ödeme adımında sepetinize eklenecektir.',
    },
    {
        categoryId: 1,
        question: 'Siparişimi nasıl takip edebilirim?',
        answer: 'Siparişiniz kargoya verildiğinde e-posta ve SMS ile size iletilen kargo takip numarasına tıklayarak veya sitemizdeki Hesabım > Siparişlerim bölümünden durumunu canlı olarak görüntüleyebilirsiniz.',
    },

    {
        categoryId: 2,
        question: 'İade politikanız nedir?',
        answer: 'Kullanılmamış, orijinal ambalajı bozulmamış ve yeniden satılabilir özelliğini kaybetmemiş ürünleri, teslim aldığınız tarihten itibaren 14 gün içerisinde yasal cayma hakkınızı kullanarak ücretsiz iade edebilirsiniz.',
    },
    {
        categoryId: 2,
        question: 'Siparişimi nasıl iptal edebilir veya değiştirebilirim?',
        answer: "Siparişiniz 'Hazırlanıyor' veya 'Kargoya Verildi' aşamasına geçmeden önce, Hesabım > Siparişlerim sayfasından siparişinizi iptal edebilirsiniz. Kargoya verildiyse iptal edilemez ancak ulaştıktan sonra 14 gün içinde iade edebilirsiniz.",
    },
    {
        categoryId: 2,
        question: 'İade ücreti ne zaman hesabıma yansır?',
        answer: 'İade ettiğiniz ürün depomuza ulaştıktan sonra 2-3 iş günü içerisinde kalite kontrolü yapılır. İadeniz onaylandığında ücret iadeniz bankanıza iletilir ve 1-7 iş günü içinde kartınıza yansır.',
    },

    {
        categoryId: 3,
        question: 'Hangi ödeme yöntemlerini kabul ediyorsunuz?',
        answer: 'Tüm kredi kartları ve banka kartları ile Akbank 3D Secure ödeme altyapımız üzerinden peşin veya taksitli alışveriş yapabilirsiniz. Ayrıca havale/EFT seçeneğimiz de mevcuttur.',
    },
    {
        categoryId: 3,
        question: 'Kredi kartına taksit yapıyor musunuz?',
        answer: 'Evet, anlaşmalı kredi kartlarına BDDK kuralları çerçevesinde ürün kategorisine göre değişen vade farksız veya vade farklı taksit imkanları sunmaktayız.',
    },
    {
        categoryId: 3,
        question: 'Kapıda ödeme seçeneğiniz var mı?',
        answer: 'Operasyonel ve güvenlik sebepleriyle şu an için kapıda ödeme seçeneğimiz bulunmamaktadır.',
    },
];

const faqs = computed(() => {
    return allFaqs.filter((faq) => faq.categoryId === activeCategory.value);
});

const setActiveCategory = (id: number) => {
    activeCategory.value = id;
    openFaq.value = 0;
};
</script>

<template>
    <Head title="Sıkça Sorulan Sorular" />

    <div class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-950">
        <!-- Header Section -->
        <div
            class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8 lg:py-24"
            >
                <div
                    class="mb-6 inline-flex items-center justify-center rounded-2xl bg-zinc-100 p-3 dark:bg-zinc-800"
                >
                    <MessageCircleQuestion
                        class="h-8 w-8 text-zinc-900 dark:text-zinc-100"
                    />
                </div>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl dark:text-white"
                >
                    Sıkça Sorulan Sorular
                </h1>
                <div
                    v-if="corporateContent"
                    class="prose dark:prose-invert mx-auto mt-6 max-w-3xl text-left"
                    v-html="corporateContent"
                ></div>
                <p
                    v-else
                    class="mx-auto mt-4 max-w-2xl text-lg text-zinc-500 dark:text-zinc-400"
                >
                    Ürünlerimiz, kargo süreçleri ve iade koşulları hakkında en
                    çok merak edilen soruların yanıtlarını bulun. Aradığınızı
                    bulamadınız mı?
                    <Link
                        href="/iletisim"
                        class="font-semibold text-zinc-900 underline decoration-2 underline-offset-4 transition-colors hover:text-zinc-700 dark:text-white dark:hover:text-zinc-300"
                        >Destek ekibimizle iletişime geçin.</Link
                    >
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div
            class="mx-auto flex w-full max-w-7xl flex-grow flex-col gap-12 px-4 py-12 sm:px-6 md:flex-row lg:px-8 lg:py-16"
        >
            <!-- Sidebar Categories -->
            <div class="w-full flex-shrink-0 md:w-64">
                <div class="sticky top-8">
                    <h2
                        class="mb-4 text-xs font-semibold tracking-wider text-zinc-500 uppercase dark:text-zinc-400"
                    >
                        Kategoriler
                    </h2>
                    <nav class="space-y-2">
                        <button
                            v-for="cat in faqCategories"
                            :key="cat.id"
                            @click="setActiveCategory(cat.id)"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left font-medium transition-all duration-200"
                            :class="
                                activeCategory === cat.id
                                    ? 'bg-zinc-900 text-white shadow-md dark:bg-zinc-100 dark:text-zinc-900'
                                    : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/50 dark:hover:text-white'
                            "
                        >
                            <component
                                :is="cat.icon"
                                class="h-5 w-5"
                                :class="
                                    activeCategory === cat.id
                                        ? 'text-zinc-300 dark:text-zinc-600'
                                        : 'text-zinc-400 dark:text-zinc-500'
                                "
                            />
                            {{ cat.name }}
                        </button>
                    </nav>

                    <div
                        class="mt-8 rounded-2xl border border-zinc-200 bg-zinc-100 p-6 dark:border-zinc-800 dark:bg-zinc-900/50"
                    >
                        <h3
                            class="mb-2 font-semibold text-zinc-900 dark:text-white"
                        >
                            Hâlâ yardıma mı ihtiyacınız var?
                        </h3>
                        <p
                            class="mb-4 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            Destek ekibimiz size yardımcı olmak için hazır.
                        </p>
                        <Link
                            href="/iletisim"
                            class="flex w-full items-center justify-center rounded-lg border border-zinc-200 bg-white py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700"
                        >
                            Bize Ulaşın
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Accordion FAQ -->
            <div class="flex-grow">
                <div class="space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white transition-all duration-300 dark:border-zinc-800 dark:bg-zinc-900"
                        :class="{
                            'shadow-lg ring-1 ring-zinc-900 dark:ring-zinc-100':
                                openFaq === index,
                            'shadow-sm hover:border-zinc-300 dark:hover:border-zinc-700':
                                openFaq !== index,
                        }"
                    >
                        <button
                            @click="toggleFaq(index)"
                            class="flex w-full items-center justify-between px-6 py-5 text-left focus:outline-none"
                        >
                            <span
                                class="pr-8 text-base font-semibold text-zinc-900 dark:text-white"
                            >
                                {{ faq.question }}
                            </span>
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 transition-colors group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700"
                                :class="{
                                    'bg-zinc-900 text-white group-hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:group-hover:bg-zinc-200':
                                        openFaq === index,
                                }"
                            >
                                <ChevronDown
                                    class="h-5 w-5 transition-transform duration-300 ease-out"
                                    :class="{
                                        '-rotate-180 text-white dark:text-zinc-900':
                                            openFaq === index,
                                        'text-zinc-500 dark:text-zinc-400':
                                            openFaq !== index,
                                    }"
                                />
                            </div>
                        </button>

                        <div
                            class="grid transition-all duration-300 ease-in-out"
                            :class="
                                openFaq === index
                                    ? 'grid-rows-[1fr] opacity-100'
                                    : 'grid-rows-[0fr] opacity-0'
                            "
                        >
                            <div class="overflow-hidden">
                                <div
                                    class="mt-2 border-t border-zinc-100 px-6 pt-0 pb-6 leading-relaxed text-zinc-600 dark:border-zinc-800/50 dark:text-zinc-400"
                                >
                                    <div class="pt-4">
                                        {{ faq.answer }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <StorefrontFooter />
    </div>
</template>
