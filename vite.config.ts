import legacy from '@vitejs/plugin-legacy';
import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, loadEnv } from 'vite';

const resolveDetectTls = (value: string | undefined): string | boolean => {
    if (!value) {
        return false;
    }

    const normalizedValue = value.trim().toLowerCase();

    if (['0', 'false', 'no', 'off'].includes(normalizedValue)) {
        return false;
    }

    if (['1', 'true', 'yes', 'on'].includes(normalizedValue)) {
        return true;
    }

    return value.trim();
};

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            legacy({
                targets: [
                    '> 0.5%',
                    'Chrome >= 49',
                    'Edge >= 18',
                    'Firefox >= 52',
                    'Firefox ESR',
                    'Safari >= 11',
                    'iOS >= 11',
                    'Samsung >= 8',
                    'not dead',
                    'not IE 11',
                ],
                additionalLegacyPolyfills: ['regenerator-runtime/runtime'],
            }),
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.ts'],
                refresh: true,
                detectTls: resolveDetectTls(env.VITE_DEV_SERVER_DETECT_TLS),
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            inertia(),
            tailwindcss(),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            env.SKIP_WAYFINDER_GENERATE !== 'true'
                ? wayfinder({
                      formVariants: true,
                  })
                : null,
        ],
    };
});
