<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { LockKeyhole, ShieldCheck } from '@lucide/vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Yönetim paneli girişi">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <main class="grid min-h-svh bg-[#f7f7f8] lg:grid-cols-[0.95fr_1.05fr]">
        <section
            class="hidden bg-primary p-10 text-white lg:flex lg:flex-col lg:justify-between"
        >
            <Link :href="home()" class="inline-flex items-center gap-3">
                <span
                    class="flex size-11 items-center justify-center rounded-md bg-white"
                >
                    <BrandLogo compact class="size-9" />
                </span>
                <span class="text-lg font-semibold">ErlerAVM Yönetim</span>
            </Link>

            <div class="max-w-md space-y-5">
                <p class="text-sm font-medium text-white/70">
                    Ayrı yönetim paneli
                </p>
                <h1 class="text-3xl leading-tight font-semibold">
                    Vitrin, stok ve operasyon yönetimi müşteri alanından
                    ayrıldı.
                </h1>
                <p class="text-sm leading-6 text-white/75">
                    Bu giriş sadece yetkili ekiplerin yönetim alanına erişmesi
                    için kullanılır.
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm text-white/75">
                <ShieldCheck class="size-4" />
                Fortify oturum güvenliği ve rate limit kullanılır.
            </div>
        </section>

        <section class="flex min-h-svh items-center justify-center p-6">
            <div
                class="w-full max-w-md rounded-xl border bg-white p-6 shadow-sm md:p-8"
            >
                <div class="mb-6 text-center">
                    <BrandLogo class="mx-auto h-14" />
                    <div class="mt-5 flex justify-center">
                        <span class="rounded-md bg-primary/10 p-3 text-primary">
                            <LockKeyhole class="size-6" />
                        </span>
                    </div>
                    <h2 class="mt-4 text-2xl font-semibold tracking-tight">
                        Yönetim paneli girişi
                    </h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Vitrin ve stok yönetimi için oturum açın.
                    </p>
                </div>

                <div
                    v-if="status"
                    class="mb-4 rounded-md bg-emerald-50 p-3 text-center text-sm font-medium text-emerald-700"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="grid gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="admin-email">E-posta adresi</Label>
                        <Input
                            id="admin-email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="admin@erleravm.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="admin-password">Şifre</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm"
                            >
                                Şifremi unuttum
                            </TextLink>
                        </div>
                        <PasswordInput
                            id="admin-password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Şifreniz"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="processing">
                        <Spinner v-if="processing" />
                        Yönetim paneline gir
                    </Button>
                </Form>
            </div>
        </section>
    </main>
</template>
