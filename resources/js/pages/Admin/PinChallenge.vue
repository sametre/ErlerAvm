<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import admin from '@/routes/admin';

defineProps<{
    attemptsRemaining: number;
    lockoutSeconds: number;
    adminUrl: string;
    storefrontUrl: string;
}>();

const code = ref('');
</script>

<template>
    <Head title="Yönetici doğrulama">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <main class="flex min-h-svh items-center justify-center bg-slate-950 p-6">
        <section
            class="w-full max-w-md rounded-lg border border-white/10 bg-white p-6 shadow-2xl md:p-8"
        >
            <div class="mb-7 text-center">
                <a :href="storefrontUrl" class="inline-flex justify-center">
                    <BrandLogo class="h-14" />
                </a>

                <div class="mt-6 flex justify-center">
                    <span
                        class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <ShieldCheck class="size-6" />
                    </span>
                </div>

                <h1
                    class="mt-4 text-2xl font-semibold tracking-tight text-slate-950"
                >
                    Yönetici doğrulama
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Panel oturumunu açmak için 6 haneli güvenlik kodunu girin.
                </p>
            </div>

            <Form
                v-bind="admin.pin.store.form()"
                class="space-y-5"
                reset-on-error
                @error="code = ''"
                #default="{ errors, processing }"
            >
                <input type="hidden" name="code" :value="code" />

                <div class="flex flex-col items-center gap-3">
                    <InputOTP
                        id="admin-pin"
                        v-model="code"
                        :maxlength="6"
                        :disabled="processing || lockoutSeconds > 0"
                        autofocus
                    >
                        <InputOTPGroup>
                            <InputOTPSlot
                                v-for="index in 6"
                                :key="index"
                                :index="index - 1"
                            />
                        </InputOTPGroup>
                    </InputOTP>

                    <InputError :message="errors.code" />

                    <p
                        v-if="lockoutSeconds > 0"
                        class="text-center text-sm font-medium text-destructive"
                    >
                        Çok fazla hatalı deneme. {{ lockoutSeconds }} saniye
                        sonra tekrar deneyin.
                    </p>
                    <p v-else class="text-center text-xs text-slate-500">
                        Kalan deneme hakkı: {{ attemptsRemaining }}
                    </p>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="
                        processing || code.length !== 6 || lockoutSeconds > 0
                    "
                >
                    <Spinner v-if="processing" />
                    Paneli Aç
                </Button>
            </Form>
        </section>
    </main>
</template>
