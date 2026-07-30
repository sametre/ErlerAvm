<script setup lang="ts">
import {
    destroy,
    store,
} from '@/actions/App/Http/Controllers/Admin/ApiClientController';
import { Head, useHttp } from '@inertiajs/vue3';
import {
    Check,
    Copy,
    ExternalLink,
    KeyRound,
    Plus,
    ShieldOff,
    X,
} from '@lucide/vue';
import { ref } from 'vue';

type ApiClient = {
    public_id: string;
    name: string;
    key: string;
    abilities: string[];
    allowed_ips: string[] | null;
    created_by: string | null;
    last_used_at: string | null;
    expires_at: string | null;
    revoked_at: string | null;
    created_at: string | null;
};

type CreateResponse = {
    client: ApiClient;
    credentials: { key: string; secret: string };
    message: string;
};

const props = defineProps<{
    clients: ApiClient[];
    abilities: Record<string, string>;
    docsUrl: string;
}>();

const clients = ref([...props.clients]);
const showCreate = ref(false);
const allowedIpText = ref('');
const credentials = ref<CreateResponse['credentials'] | null>(null);
const copied = ref('');

const createHttp = useHttp<
    {
        name: string;
        abilities: string[];
        allowed_ips: string[];
        expires_at: string;
    },
    CreateResponse
>({
    name: '',
    abilities: ['catalog:read'],
    allowed_ips: [],
    expires_at: '',
});

const revokeHttp = useHttp<
    Record<string, never>,
    { client: ApiClient; message: string }
>({});

async function createClient() {
    createHttp.allowed_ips = allowedIpText.value
        .split(/[\n,]/)
        .map((ip) => ip.trim())
        .filter(Boolean);

    const response = await createHttp.post(store.url());

    if (response?.client) {
        clients.value.unshift(response.client);
        credentials.value = response.credentials;
        showCreate.value = false;
        createHttp.reset();
        createHttp.abilities = ['catalog:read'];
        allowedIpText.value = '';
    }
}

async function revokeClient(client: ApiClient) {
    if (
        !window.confirm(
            `${client.name} API istemcisini iptal etmek istediğinize emin misiniz?`,
        )
    ) {
        return;
    }

    const response = await revokeHttp.delete(destroy.url(client.public_id));

    if (response?.client) {
        const index = clients.value.findIndex(
            (item) => item.public_id === client.public_id,
        );
        clients.value[index] = response.client;
    }
}

async function copy(value: string, label: string) {
    await navigator.clipboard.writeText(value);
    copied.value = label;
    window.setTimeout(() => (copied.value = ''), 1600);
}
</script>

<template>
    <Head title="API İstemcileri">
        <meta head-key="robots" name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-[#f4f4f5] text-zinc-950">
        <header class="border-b-4 border-red-700 bg-black text-white">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-5 lg:px-8"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex size-11 items-center justify-center bg-red-700"
                    >
                        <KeyRound class="size-5" />
                    </div>
                    <div>
                        <div
                            class="text-xs font-semibold tracking-[.22em] text-red-400"
                        >
                            ERLERAVM
                        </div>
                        <div class="font-semibold">API CONTROL CENTER</div>
                    </div>
                </div>
                <a
                    :href="docsUrl"
                    target="_blank"
                    class="inline-flex items-center gap-2 border border-white/20 px-4 py-2 text-sm font-medium hover:border-red-500 hover:text-red-400"
                >
                    <ExternalLink class="size-4" /> Dokümantasyon
                </a>
            </div>
        </header>

        <main class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-10 lg:px-8">
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <p class="text-xs font-bold tracking-[.2em] text-red-700">
                        CREDENTIAL MANAGEMENT
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                        API istemcileri
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                        Harici uygulamalar için anahtar oluşturun, erişim
                        kapsamını yönetin ve kullanım durumunu izleyin.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        class="inline-flex items-center gap-2 bg-red-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-800"
                        @click="showCreate = true"
                    >
                        <Plus class="size-4" /> Yeni istemci
                    </button>
                </div>
            </div>

            <div
                v-if="credentials"
                class="rounded-xl border border-amber-400/40 bg-amber-50 p-5 dark:bg-amber-950/20"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2
                            class="font-semibold text-amber-950 dark:text-amber-100"
                        >
                            Kimlik bilgilerini şimdi kaydedin
                        </h2>
                        <p
                            class="mt-1 text-sm text-amber-800 dark:text-amber-300"
                        >
                            Secret tekrar gösterilmeyecektir.
                        </p>
                    </div>
                    <button
                        class="text-amber-800 dark:text-amber-300"
                        @click="credentials = null"
                    >
                        <X class="size-5" />
                    </button>
                </div>
                <div class="mt-4 grid gap-3">
                    <div
                        v-for="item in [
                            { label: 'API Key', value: credentials.key },
                            { label: 'API Secret', value: credentials.secret },
                        ]"
                        :key="item.label"
                        class="grid gap-2 rounded-lg border border-amber-300/50 bg-white p-3 sm:grid-cols-[110px_1fr_auto] sm:items-center dark:bg-slate-950"
                    >
                        <span
                            class="text-xs font-semibold text-muted-foreground"
                            >{{ item.label }}</span
                        >
                        <code class="overflow-x-auto text-xs">{{
                            item.value
                        }}</code>
                        <button
                            class="inline-flex items-center gap-1 text-xs font-medium"
                            @click="copy(item.value, item.label)"
                        >
                            <component
                                :is="copied === item.label ? Check : Copy"
                                class="size-3.5"
                            />
                            {{
                                copied === item.label ? 'Kopyalandı' : 'Kopyala'
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden border border-zinc-300 bg-white shadow-sm"
            >
                <div
                    v-if="clients.length === 0"
                    class="flex flex-col items-center gap-3 px-6 py-16 text-center"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-muted"
                    >
                        <KeyRound class="size-5" />
                    </div>
                    <div>
                        <h2 class="font-medium">Henüz API istemcisi yok</h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            İlk entegrasyon anahtarınızı oluşturun.
                        </p>
                    </div>
                </div>
                <div v-else class="divide-y">
                    <article
                        v-for="client in clients"
                        :key="client.public_id"
                        class="grid gap-4 p-5 lg:grid-cols-[1.2fr_1fr_1fr_auto] lg:items-center"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-medium">{{ client.name }}</h2>
                                <span
                                    :class="
                                        client.revoked_at
                                            ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                    "
                                    class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                >
                                    {{
                                        client.revoked_at
                                            ? 'İptal edildi'
                                            : 'Aktif'
                                    }}
                                </span>
                            </div>
                            <code
                                class="mt-2 block max-w-sm truncate text-xs text-muted-foreground"
                                >{{ client.key }}</code
                            >
                        </div>
                        <div class="text-sm">
                            <div class="text-xs text-muted-foreground">
                                Yetkiler
                            </div>
                            <div class="mt-1">
                                {{ client.abilities.join(', ') }}
                            </div>
                        </div>
                        <div class="text-sm">
                            <div class="text-xs text-muted-foreground">
                                Son kullanım
                            </div>
                            <div class="mt-1">
                                {{
                                    client.last_used_at
                                        ? new Date(
                                              client.last_used_at,
                                          ).toLocaleString('tr-TR')
                                        : 'Henüz kullanılmadı'
                                }}
                            </div>
                        </div>
                        <button
                            v-if="!client.revoked_at"
                            :disabled="revokeHttp.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950"
                            @click="revokeClient(client)"
                        >
                            <ShieldOff class="size-4" /> İptal et
                        </button>
                    </article>
                </div>
            </div>
        </main>

        <div
            v-if="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
            @click.self="showCreate = false"
        >
            <form
                class="w-full max-w-lg border-t-4 border-red-700 bg-white p-6 text-zinc-950 shadow-2xl"
                @submit.prevent="createClient"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Yeni API istemcisi
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Secret yalnızca bir kez gösterilir.
                        </p>
                    </div>
                    <button type="button" @click="showCreate = false">
                        <X class="size-5" />
                    </button>
                </div>
                <div class="mt-6 grid gap-5">
                    <label class="grid gap-2"
                        ><span class="text-sm font-medium">İstemci adı</span
                        ><input
                            v-model="createHttp.name"
                            required
                            maxlength="120"
                            class="rounded-lg border bg-background px-3 py-2.5 outline-none focus:ring-2 focus:ring-ring"
                            placeholder="Örn. iOS Üretim"
                        /><span
                            v-if="createHttp.errors.name"
                            class="text-xs text-red-600"
                            >{{ createHttp.errors.name }}</span
                        ></label
                    >
                    <fieldset class="grid gap-2">
                        <legend class="text-sm font-medium">Yetkiler</legend>
                        <label
                            v-for="(description, ability) in abilities"
                            :key="ability"
                            class="flex items-start gap-3 rounded-lg border p-3"
                            ><input
                                v-model="createHttp.abilities"
                                type="checkbox"
                                :value="ability"
                                class="mt-1"
                            /><span
                                ><span class="block font-mono text-xs">{{
                                    ability
                                }}</span
                                ><span
                                    class="mt-1 block text-xs text-muted-foreground"
                                    >{{ description }}</span
                                ></span
                            ></label
                        >
                    </fieldset>
                    <label class="grid gap-2"
                        ><span class="text-sm font-medium"
                            >İzin verilen IP adresleri
                            <span class="font-normal text-muted-foreground"
                                >(opsiyonel)</span
                            ></span
                        ><textarea
                            v-model="allowedIpText"
                            rows="3"
                            class="rounded-lg border bg-background px-3 py-2.5 font-mono text-sm outline-none focus:ring-2 focus:ring-ring"
                            placeholder="Her satıra bir IP"
                        /><span
                            v-if="createHttp.errors['allowed_ips.0']"
                            class="text-xs text-red-600"
                            >{{ createHttp.errors['allowed_ips.0'] }}</span
                        ></label
                    >
                    <label class="grid gap-2"
                        ><span class="text-sm font-medium"
                            >Son kullanma tarihi
                            <span class="font-normal text-muted-foreground"
                                >(opsiyonel)</span
                            ></span
                        ><input
                            v-model="createHttp.expires_at"
                            type="datetime-local"
                            class="rounded-lg border bg-background px-3 py-2.5 outline-none focus:ring-2 focus:ring-ring"
                    /></label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border px-4 py-2 text-sm"
                        @click="showCreate = false"
                    >
                        Vazgeç</button
                    ><button
                        :disabled="createHttp.processing"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        {{
                            createHttp.processing
                                ? 'Oluşturuluyor…'
                                : 'Anahtar oluştur'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
