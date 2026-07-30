<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    Copy,
    ExternalLink,
    KeyRound,
    Play,
    ShieldCheck,
    Terminal,
} from '@lucide/vue';
import { computed, ref } from 'vue';

type Endpoint = {
    method: string;
    path: string;
    title: string;
    description: string;
};
type Filter = { name: string; description: string };

const props = defineProps<{
    baseUrl: string;
    version: string;
    manageUrl: string | null;
    endpoints: Endpoint[];
    filters: Filter[];
}>();

const apiKey = ref('');
const apiSecret = ref('');
const selectedPath = ref('/products?per_page=3');
const responseBody = ref('');
const responseStatus = ref<number | null>(null);
const loading = ref(false);
const copied = ref('');

const curlExample = computed(
    () => `curl "${props.baseUrl}/products?per_page=10" \\
  -H "Accept: application/json" \\
  -H "X-API-Key: YOUR_API_KEY" \\
  -H "X-API-Secret: YOUR_API_SECRET"`,
);

const swiftExample = computed(
    () => `var request = URLRequest(url: URL(string: "${props.baseUrl}/products")!)
request.setValue("application/json", forHTTPHeaderField: "Accept")
request.setValue(apiKey, forHTTPHeaderField: "X-API-Key")
request.setValue(apiSecret, forHTTPHeaderField: "X-API-Secret")

let (data, response) = try await URLSession.shared.data(for: request)`,
);

const javascriptExample = computed(
    () => `const response = await fetch("${props.baseUrl}/products", {
  headers: {
    "Accept": "application/json",
    "X-API-Key": process.env.ERLER_API_KEY,
    "X-API-Secret": process.env.ERLER_API_SECRET,
  },
});

const payload = await response.json();`,
);

async function copy(value: string, label: string) {
    await navigator.clipboard.writeText(value);
    copied.value = label;
    window.setTimeout(() => (copied.value = ''), 1600);
}

async function runRequest() {
    loading.value = true;
    responseStatus.value = null;
    responseBody.value = '';

    try {
        const response = await fetch(`${props.baseUrl}${selectedPath.value}`, {
            headers: {
                Accept: 'application/json',
                'X-API-Key': apiKey.value,
                'X-API-Secret': apiSecret.value,
            },
        });
        responseStatus.value = response.status;
        responseBody.value = JSON.stringify(await response.json(), null, 2);
    } catch {
        responseBody.value =
            'İstek gönderilemedi. API adresini ve bağlantınızı kontrol edin.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-zinc-50 text-zinc-950">
        <Head title="ErlerAVM API — Geliştirici Merkezi">
            <meta head-key="robots" name="robots" content="noindex, nofollow" />
        </Head>

        <header
            class="sticky top-0 z-40 border-t-4 border-red-700 bg-black text-white shadow-xl"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8"
            >
                <a href="/" class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center bg-red-700 font-black"
                    >
                        E
                    </div>
                    <div>
                        <div class="text-sm font-bold tracking-[.2em]">
                            ERLERAVM API
                        </div>
                        <div class="text-[10px] tracking-[.16em] text-zinc-400">
                            DEVELOPER CENTER · {{ version }}
                        </div>
                    </div>
                </a>
                <nav
                    class="hidden items-center gap-6 text-xs font-semibold text-zinc-300 lg:flex"
                >
                    <a href="#baslangic" class="hover:text-red-400"
                        >BAŞLANGIÇ</a
                    >
                    <a href="#guvenlik" class="hover:text-red-400">GÜVENLİK</a>
                    <a href="#endpointler" class="hover:text-red-400"
                        >ENDPOINTLER</a
                    >
                    <a href="#hatalar" class="hover:text-red-400"
                        >HATA KODLARI</a
                    >
                    <a href="#konsol" class="hover:text-red-400">KONSOL</a>
                </nav>
                <Button
                    v-if="manageUrl"
                    as-child
                    class="rounded-none bg-red-700 hover:bg-red-800"
                >
                    <a :href="manageUrl"
                        >API anahtarları <ExternalLink class="size-4"
                    /></a>
                </Button>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden bg-black text-white">
                <div
                    class="absolute inset-y-0 right-0 hidden w-1/3 border-l border-white/10 bg-[linear-gradient(135deg,transparent_45%,rgba(185,28,28,.45)_45%,rgba(185,28,28,.45)_55%,transparent_55%)] lg:block"
                />
                <div
                    class="relative mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28"
                >
                    <Badge
                        class="rounded-none border-red-600 bg-red-700 px-3 py-1 text-white"
                        >REST · JSON · HTTPS</Badge
                    >
                    <h1
                        class="mt-7 max-w-4xl text-5xl leading-[1.02] font-black tracking-[-.05em] sm:text-7xl"
                    >
                        Ticaret verilerinizi<br /><span class="text-red-600"
                            >güvenle bağlayın.</span
                        >
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-zinc-400">
                        ErlerAVM ürün, kategori, marka ve kampanya verilerine
                        kararlı bir v1 sözleşmesiyle erişin. Mobil uygulama ve
                        kurumsal entegrasyonlar için tasarlandı.
                    </p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <Button
                            as-child
                            class="rounded-none bg-red-700 px-6 hover:bg-red-800"
                            ><a href="#baslangic">Entegrasyona başla</a></Button
                        >
                        <Button
                            as-child
                            variant="outline"
                            class="rounded-none border-zinc-700 bg-transparent px-6 text-white hover:bg-zinc-900 hover:text-white"
                            ><a href="#endpointler">API referansı</a></Button
                        >
                    </div>
                    <div
                        class="mt-14 grid max-w-3xl grid-cols-2 border border-zinc-800 sm:grid-cols-4"
                    >
                        <div
                            v-for="item in [
                                ['Sürüm', version],
                                ['Format', 'JSON'],
                                ['Limit', '120/dk'],
                                ['Durum', 'Operasyonel'],
                            ]"
                            :key="item[0]"
                            class="border-zinc-800 p-4 [&:not(:last-child)]:border-r"
                        >
                            <div
                                class="text-[10px] font-bold tracking-[.16em] text-zinc-500"
                            >
                                {{ item[0] }}
                            </div>
                            <div class="mt-2 text-sm font-semibold">
                                {{ item[1] }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                id="baslangic"
                class="mx-auto max-w-7xl px-5 py-16 lg:px-8"
            >
                <div class="mb-9 max-w-2xl">
                    <p class="text-xs font-black tracking-[.22em] text-red-700">
                        QUICK START
                    </p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Üç adımda ilk isteğiniz
                    </h2>
                </div>
                <div class="grid gap-5 lg:grid-cols-3">
                    <Card
                        v-for="(step, index) in [
                            [
                                'İstemci oluşturun',
                                'Kontrol merkezinden ad, erişim kapsamı, IP kısıtı ve son kullanma tarihi belirleyin.',
                            ],
                            [
                                'Header’ları ekleyin',
                                'API Key ve Secret değerlerini her HTTPS isteğinde güvenli header olarak gönderin.',
                            ],
                            [
                                'Yanıtı işleyin',
                                'success, data, meta ve errors alanlarını standart JSON sözleşmesine göre okuyun.',
                            ],
                        ]"
                        :key="step[0]"
                        class="rounded-none border-zinc-300 shadow-none"
                    >
                        <CardHeader
                            ><div
                                class="flex size-9 items-center justify-center bg-black font-mono text-sm text-white"
                            >
                                0{{ index + 1 }}
                            </div>
                            <CardTitle class="pt-3">{{
                                step[0]
                            }}</CardTitle></CardHeader
                        >
                        <CardContent class="leading-7 text-zinc-600">{{
                            step[1]
                        }}</CardContent>
                    </Card>
                </div>
                <Card
                    class="mt-6 rounded-none border-black bg-black text-white"
                >
                    <CardHeader
                        class="flex-row items-center justify-between border-b border-zinc-800"
                    >
                        <CardTitle class="flex items-center gap-2 text-base"
                            ><Terminal class="size-4 text-red-500" />
                            cURL</CardTitle
                        >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-zinc-300 hover:bg-zinc-900 hover:text-white"
                            @click="copy(curlExample, 'curl')"
                        >
                            <component
                                :is="copied === 'curl' ? Check : Copy"
                                class="size-4"
                            />
                            {{ copied === 'curl' ? 'Kopyalandı' : 'Kopyala' }}
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <pre
                            class="overflow-x-auto pt-6 font-mono text-sm leading-7 text-zinc-300"
                        ><code>{{ curlExample }}</code></pre>
                    </CardContent>
                </Card>
            </section>

            <section id="guvenlik" class="border-y border-zinc-300 bg-white">
                <div
                    class="mx-auto grid max-w-7xl gap-10 px-5 py-16 lg:grid-cols-[.75fr_1.25fr] lg:px-8"
                >
                    <div>
                        <ShieldCheck class="size-9 text-red-700" />
                        <h2 class="mt-5 text-3xl font-bold">
                            Kimlik doğrulama
                        </h2>
                        <p class="mt-4 leading-7 text-zinc-600">
                            Her istek iki kimlik bilgisiyle imzalanır. Secret
                            yalnızca oluşturulduğu anda gösterilir ve sistemde
                            özetlenmiş olarak saklanır.
                        </p>
                    </div>
                    <div class="grid gap-4">
                        <Card class="rounded-none shadow-none"
                            ><CardContent
                                class="grid gap-3 p-5 sm:grid-cols-[180px_1fr]"
                                ><code class="font-bold text-red-700"
                                    >X-API-Key</code
                                ><span class="text-sm text-zinc-600"
                                    >İstemciyi tanımlayan açık anahtar.</span
                                ></CardContent
                            ></Card
                        >
                        <Card class="rounded-none shadow-none"
                            ><CardContent
                                class="grid gap-3 p-5 sm:grid-cols-[180px_1fr]"
                                ><code class="font-bold text-red-700"
                                    >X-API-Secret</code
                                ><span class="text-sm text-zinc-600"
                                    >Sunucu tarafında saklanması gereken gizli
                                    anahtar.</span
                                ></CardContent
                            ></Card
                        >
                        <div
                            class="border-l-4 border-red-700 bg-zinc-100 p-5 text-sm leading-6 text-zinc-700"
                        >
                            <strong>Güvenlik kuralı:</strong> Secret değerini
                            mobil uygulama paketine veya tarayıcı koduna
                            gömmeyin. İstekleri kendi güvenli backend servisiniz
                            üzerinden iletin.
                        </div>
                    </div>
                </div>
            </section>

            <section
                id="endpointler"
                class="mx-auto max-w-7xl px-5 py-16 lg:px-8"
            >
                <p class="text-xs font-black tracking-[.22em] text-red-700">
                    API REFERENCE
                </p>
                <h2 class="mt-3 text-3xl font-bold">Endpoint kataloğu</h2>
                <div
                    class="mt-8 overflow-hidden border border-zinc-300 bg-white"
                >
                    <article
                        v-for="endpoint in endpoints"
                        :key="endpoint.path"
                        class="grid gap-3 border-b border-zinc-200 p-5 last:border-0 md:grid-cols-[80px_1fr_1.3fr] md:items-center"
                    >
                        <Badge class="w-fit rounded-none bg-black font-mono">{{
                            endpoint.method
                        }}</Badge>
                        <code class="text-sm font-semibold text-red-700">{{
                            endpoint.path
                        }}</code>
                        <div>
                            <div class="font-semibold">
                                {{ endpoint.title }}
                            </div>
                            <div class="mt-1 text-sm text-zinc-500">
                                {{ endpoint.description }}
                            </div>
                        </div>
                    </article>
                </div>
                <div class="mt-10 grid gap-8 lg:grid-cols-[.7fr_1.3fr]">
                    <div>
                        <h3 class="text-2xl font-bold">Ürün filtreleri</h3>
                        <p class="mt-3 text-zinc-600">
                            Parametreleri query string üzerinde birlikte
                            kullanabilirsiniz.
                        </p>
                    </div>
                    <div class="border border-zinc-300 bg-white">
                        <div
                            v-for="filter in filters"
                            :key="filter.name"
                            class="grid gap-2 border-b border-zinc-200 p-4 last:border-0 sm:grid-cols-[190px_1fr]"
                        >
                            <code class="text-sm font-bold text-red-700">{{
                                filter.name
                            }}</code
                            ><span class="text-sm text-zinc-600">{{
                                filter.description
                            }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="hatalar" class="bg-zinc-950 text-white">
                <div class="mx-auto max-w-7xl px-5 py-16 lg:px-8">
                    <p class="text-xs font-black tracking-[.22em] text-red-500">
                        RESPONSE CONTRACT
                    </p>
                    <h2 class="mt-3 text-3xl font-bold">
                        HTTP durum ve hata kodları
                    </h2>
                    <div
                        class="mt-8 grid gap-px bg-zinc-800 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="status in [
                                [
                                    '200',
                                    'Başarılı',
                                    'İstek işlendi ve data alanı döndürüldü.',
                                ],
                                [
                                    '401',
                                    'Kimlik doğrulanamadı',
                                    'Key veya Secret eksik ya da geçersiz.',
                                ],
                                [
                                    '403',
                                    'Yetki reddedildi',
                                    'İstemcinin gerekli erişim kapsamı yok.',
                                ],
                                [
                                    '404',
                                    'Kayıt bulunamadı',
                                    'İstenen kaynak sistemde mevcut değil.',
                                ],
                                [
                                    '422',
                                    'Doğrulama hatası',
                                    'Parametreler geçerli değil; errors alanını okuyun.',
                                ],
                                [
                                    '429',
                                    'İstek limiti',
                                    'Dakikalık limit aşıldı; Retry-After header’ını izleyin.',
                                ],
                            ]"
                            :key="status[0]"
                            class="bg-zinc-950 p-6"
                        >
                            <div
                                class="font-mono text-2xl font-black text-red-500"
                            >
                                {{ status[0] }}
                            </div>
                            <div class="mt-3 font-semibold">
                                {{ status[1] }}
                            </div>
                            <div class="mt-2 text-sm leading-6 text-zinc-400">
                                {{ status[2] }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="konsol" class="mx-auto max-w-7xl px-5 py-16 lg:px-8">
                <div class="mb-8">
                    <p class="text-xs font-black tracking-[.22em] text-red-700">
                        LIVE CONSOLE
                    </p>
                    <h2 class="mt-3 text-3xl font-bold">
                        İsteğinizi test edin
                    </h2>
                </div>
                <div class="grid gap-6 lg:grid-cols-2">
                    <Card class="rounded-none border-zinc-300 shadow-none">
                        <CardContent class="pt-6"
                            ><form
                                class="grid gap-5"
                                @submit.prevent="runRequest"
                            >
                                <label class="grid gap-2 text-sm font-semibold"
                                    >API Key<Input
                                        v-model="apiKey"
                                        required
                                        autocomplete="off"
                                        class="rounded-none font-mono"
                                        placeholder="erl_live_..."
                                /></label>
                                <label class="grid gap-2 text-sm font-semibold"
                                    >API Secret<Input
                                        v-model="apiSecret"
                                        required
                                        type="password"
                                        autocomplete="off"
                                        class="rounded-none font-mono"
                                        placeholder="Secret değeri"
                                /></label>
                                <label class="grid gap-2 text-sm font-semibold"
                                    >Endpoint<select
                                        v-model="selectedPath"
                                        class="h-10 border border-zinc-300 bg-white px-3 text-sm"
                                    >
                                        <option value="/products?per_page=3">
                                            GET /products?per_page=3
                                        </option>
                                        <option value="/categories">
                                            GET /categories
                                        </option>
                                        <option value="/brands">
                                            GET /brands
                                        </option>
                                        <option value="/campaigns">
                                            GET /campaigns
                                        </option>
                                    </select></label
                                >
                                <Button
                                    :disabled="loading"
                                    class="rounded-none bg-red-700 hover:bg-red-800"
                                    ><Play class="size-4" />{{
                                        loading
                                            ? 'Gönderiliyor…'
                                            : 'İsteği gönder'
                                    }}</Button
                                >
                            </form></CardContent
                        >
                    </Card>
                    <div
                        class="min-h-96 border-t-4 border-red-700 bg-black text-white"
                    >
                        <div
                            class="flex justify-between border-b border-zinc-800 px-5 py-3 font-mono text-xs text-zinc-500"
                        >
                            <span>response.json</span
                            ><span
                                v-if="responseStatus"
                                :class="
                                    responseStatus < 400
                                        ? 'text-emerald-400'
                                        : 'text-red-400'
                                "
                                >HTTP {{ responseStatus }}</span
                            >
                        </div>
                        <pre
                            class="max-h-[430px] overflow-auto p-5 font-mono text-xs leading-6 text-zinc-300"
                        ><code>{{ responseBody || '// Yanıt burada görünecek.' }}</code></pre>
                    </div>
                </div>
            </section>

            <section class="border-y border-zinc-300 bg-white">
                <div
                    class="mx-auto grid max-w-7xl gap-6 px-5 py-16 lg:grid-cols-2 lg:px-8"
                >
                    <Card
                        v-for="example in [
                            { label: 'Swift / iOS', value: swiftExample },
                            {
                                label: 'JavaScript / Node.js',
                                value: javascriptExample,
                            },
                        ]"
                        :key="example.label"
                        class="rounded-none border-zinc-300 shadow-none"
                    >
                        <CardHeader
                            class="flex-row items-center justify-between bg-zinc-100"
                            ><CardTitle class="text-base">{{
                                example.label
                            }}</CardTitle
                            ><Button
                                variant="ghost"
                                size="sm"
                                @click="copy(example.value, example.label)"
                                ><component
                                    :is="
                                        copied === example.label ? Check : Copy
                                    "
                                    class="size-4"
                                />{{
                                    copied === example.label
                                        ? 'Kopyalandı'
                                        : 'Kopyala'
                                }}</Button
                            ></CardHeader
                        >
                        <CardContent class="bg-black">
                            <pre
                                class="overflow-x-auto py-6 font-mono text-xs leading-6 text-zinc-300"
                            ><code>{{ example.value }}</code></pre>
                        </CardContent>
                    </Card>
                </div>
            </section>
        </main>

        <footer class="bg-black text-zinc-500">
            <div
                class="mx-auto flex max-w-7xl flex-col justify-between gap-3 px-5 py-8 text-xs sm:flex-row lg:px-8"
            >
                <span>© 2026 ERLERAVM · DEVELOPER PLATFORM</span>
                <span class="flex items-center gap-2"
                    ><KeyRound class="size-4 text-red-600" /> API Secret
                    değerlerini yalnızca sunucu tarafında saklayın.</span
                >
            </div>
        </footer>
    </div>
</template>
