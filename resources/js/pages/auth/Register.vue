<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { PackageCheck } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TeamInvitationAlert from '@/components/TeamInvitationAlert.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { TeamInvitationContext } from '@/types';

defineProps<{
    passwordRules?: string;
    teamInvitation?: TeamInvitationContext | null;
}>();

defineOptions({
    layout: {
        title: 'ErlerAVM hesabı oluşturun',
        description:
            'Siparişlerinizi ve üyelik bilgilerinizi tek yerden yönetin.',
    },
});
</script>

<template>
    <Head title="Kayıt ol" />

    <TeamInvitationAlert
        v-if="teamInvitation"
        :invitation="teamInvitation"
        action="register"
    />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="name">Ad soyad</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Adınız ve soyadınız"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Telefon Numarası</Label>
                <Input
                    id="phone"
                    type="tel"
                    required
                    :tabindex="2"
                    autocomplete="tel"
                    name="phone"
                    placeholder="05XX XXX XX XX"
                />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="email">E-posta adresi</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="3"
                    autocomplete="email"
                    name="email"
                    placeholder="ornek@erleravm.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Şifre</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Güçlü bir şifre belirleyin"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Şifre tekrarı</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Şifrenizi tekrar yazın"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="6"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Hesap oluştur
            </Button>
        </div>

        <div
            class="rounded-md bg-muted p-3 text-center text-sm text-muted-foreground"
        >
            Zaten hesabınız var mı?
            <TextLink
                :href="
                    teamInvitation
                        ? login.url({
                              query: {
                                  invitation: teamInvitation.code,
                              },
                          })
                        : login()
                "
                class="underline underline-offset-4"
                :tabindex="7"
                data-test="team-invitation-login-link"
            >
                Giriş yapın
            </TextLink>
        </div>
    </Form>
</template>
