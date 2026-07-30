<!DOCTYPE html>
@php($storeSettings = \App\Models\StoreSetting::publicPayload())
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <meta name="format-detection" content="telephone=no">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then((registration) => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, (err) => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }

            (function() {
                const appearance = @json($appearance ?? 'system');

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="{{ $storeSettings['favicon_url'] ?? '/favicon.ico' }}" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ $storeSettings['favicon_url'] ?? '/favicon.png' }}">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="application-name" content="{{ $storeSettings['store_name'] ?? 'ErlerAVM' }}">
        <meta name="apple-mobile-web-app-title" content="{{ $storeSettings['store_name'] ?? 'ErlerAVM' }}">
        <meta name="msapplication-config" content="/browserconfig.xml">
        <meta name="msapplication-TileColor" content="#ed1c24">
        <meta name="theme-color" content="{{ $storeSettings['accent_color'] ?? '#111827' }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
