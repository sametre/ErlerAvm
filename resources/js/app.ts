import { createApp, createSSRApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import i18n from '@/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        pinia.use(piniaPluginPersistedstate);

        const vueApp = (import.meta.env.SSR ? createSSRApp : createApp)({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(pinia)
            .use(i18n);

        if (import.meta.env.SSR) {
            return vueApp;
        }

        if (!el) {
            throw new Error('Inertia root element was not found.');
        }

        vueApp.mount(el);
    },
    layout: (name) => {
        switch (true) {
            case name === 'Error':
            case name === 'Maintenance':
            case name === 'Welcome':
            case name.startsWith('Storefront/'):
            case name.startsWith('Account/'):
            case name.startsWith('Legal/'):
            case name.startsWith('Contact/'):
            case name.startsWith('payments/'):
            case name === 'Admin/Login':
            case name === 'Admin/PinChallenge':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

if (typeof window !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
