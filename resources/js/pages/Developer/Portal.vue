<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head } from '@inertiajs/vue3';
import {
    ArrowRight,
    Building2,
    KeyRound,
    LockKeyhole,
    Phone,
    ShieldCheck,
} from '@lucide/vue';

defineProps<{
    loginUrl: string;
    contactPhone: string;
    contactPhoneUrl: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-zinc-100 text-zinc-950">
        <Head title="ErlerAVM API — Yönetici Girişi">
            <meta head-key="robots" name="robots" content="noindex, nofollow" />
        </Head>

        <header class="border-t-4 border-red-700 bg-black text-white">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 items-center justify-center bg-red-700 text-xl font-black"
                    >
                        E
                    </div>
                    <div>
                        <div class="text-sm font-black tracking-[.2em]">
                            ERLERAVM API
                        </div>
                        <div class="text-[10px] tracking-[.18em] text-zinc-500">
                            SECURE PARTNER PLATFORM
                        </div>
                    </div>
                </div>
                <div
                    class="hidden items-center gap-2 text-xs text-zinc-400 sm:flex"
                >
                    <ShieldCheck class="size-4 text-red-500" />
                    Yalnızca yetkili yönetici erişimi
                </div>
            </div>
        </header>

        <main
            class="mx-auto grid min-h-[calc(100vh-88px)] max-w-7xl lg:grid-cols-[1.1fr_.9fr]"
        >
            <section
                class="flex flex-col justify-between bg-black px-6 py-14 text-white lg:px-14 lg:py-20"
            >
                <div>
                    <div
                        class="inline-flex items-center gap-2 border border-zinc-800 px-3 py-1.5 text-xs font-bold tracking-[.14em] text-red-500"
                    >
                        <KeyRound class="size-3.5" /> API CONTROL CENTER
                    </div>
                    <h1
                        class="mt-8 max-w-2xl text-4xl leading-tight font-black tracking-[-.04em] sm:text-6xl"
                    >
                        Entegrasyonlarınızı<br /><span class="text-red-600"
                            >tek merkezden yönetin.</span
                        >
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-zinc-400">
                        API anahtarları, erişim kapsamları, IP kısıtları ve
                        geliştirici rehberi yalnızca yetkili ErlerAVM
                        yöneticilerine açıktır.
                    </p>
                </div>

                <div class="mt-14 border-l-4 border-red-700 bg-zinc-950 p-6">
                    <div class="flex items-start gap-4">
                        <Building2 class="mt-1 size-6 shrink-0 text-red-500" />
                        <div>
                            <h2 class="text-lg font-bold">Mağazanız var mı?</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">
                                ErlerAVM’de mağaza oluşturmak ve API
                                entegrasyonu hakkında bilgi almak için bizimle
                                iletişime geçin.
                            </p>
                            <a
                                :href="contactPhoneUrl"
                                class="mt-5 inline-flex items-center gap-3 text-xl font-black text-white transition hover:text-red-500"
                            >
                                <Phone class="size-5 text-red-500" />
                                {{ contactPhone }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center px-6 py-14 lg:px-14">
                <div class="w-full max-w-md">
                    <div
                        class="flex size-12 items-center justify-center bg-red-700 text-white"
                    >
                        <LockKeyhole class="size-5" />
                    </div>
                    <p
                        class="mt-7 text-xs font-black tracking-[.2em] text-red-700"
                    >
                        YÖNETİCİ PORTALI
                    </p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight">
                        Güvenli giriş
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-zinc-600">
                        API yönetim merkezine devam etmek için yönetici
                        hesabınızla giriş yapın.
                    </p>

                    <Form
                        :action="loginUrl"
                        method="post"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="mt-8 grid gap-5"
                    >
                        <label class="grid gap-2">
                            <Label for="api-email">E-posta adresi</Label>
                            <Input
                                id="api-email"
                                name="email"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                class="h-11 rounded-none border-zinc-300 bg-white"
                                placeholder="yonetici@erleravm.com"
                            />
                            <InputError :message="errors.email" />
                        </label>

                        <label class="grid gap-2">
                            <Label for="api-password">Şifre</Label>
                            <PasswordInput
                                id="api-password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="h-11 rounded-none border-zinc-300 bg-white"
                                placeholder="Şifreniz"
                            />
                            <InputError :message="errors.password" />
                        </label>

                        <Label
                            for="api-remember"
                            class="flex items-center gap-3 text-sm text-zinc-600"
                        >
                            <Checkbox id="api-remember" name="remember" />
                            Oturumu açık tut
                        </Label>

                        <Button
                            type="submit"
                            :disabled="processing"
                            class="h-11 rounded-none bg-red-700 font-bold hover:bg-red-800"
                        >
                            {{
                                processing
                                    ? 'Giriş yapılıyor…'
                                    : 'Yönetici girişi'
                            }}
                            <ArrowRight v-if="!processing" class="size-4" />
                        </Button>
                    </Form>

                    <div
                        class="mt-8 border-t border-zinc-300 pt-5 text-xs leading-5 text-zinc-500"
                    >
                        Bu alan kayıtlı erişim hareketlerini güvenlik amacıyla
                        izler. Yetkisiz erişim girişimleri engellenir.
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
