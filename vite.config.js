import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                'resources/css/components/math-helper.css',
                'resources/js/components/math-helper.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            // generateSW: vite-plugin-pwa generates & bundles the SW automatically
            strategies: 'generateSW',
            registerType: 'prompt',            // kita handle sendiri via virtual:pwa-register
            injectRegister: null,
            // SW di-output ke public/ root (bukan /build/) agar scope-nya '/'
            outDir: 'public',
            // Manifest di-output bersama SW di public/ root
            includeManifestIcons: true,
            manifest: {
                name: 'CAT BAPETEN',
                short_name: 'CAT BAPETEN',
                description: 'Sistem Computer Assisted Test — Badan Pengawas Tenaga Nuklir',
                start_url: '/admin',
                scope: '/',
                display: 'standalone',
                orientation: 'any',
                background_color: '#01060F',
                theme_color: '#d97706',
                lang: 'id',
                categories: ['education', 'government'],
                icons: [
                    // Standard icons (any purpose)
                    { src: '/pwa/icon-72.png', sizes: '72x72', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-96.png', sizes: '96x96', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-128.png', sizes: '128x128', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-144.png', sizes: '144x144', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-152.png', sizes: '152x152', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-384.png', sizes: '384x384', type: 'image/png', purpose: 'any' },
                    { src: '/pwa/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
                    // Maskable icons (Android adaptive — konten dalam safe zone 60%)
                    { src: '/pwa/icon-maskable-192.png', sizes: '192x192', type: 'image/png', purpose: 'maskable' },
                    { src: '/pwa/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                // Precache semua aset JS/CSS hasil build Vite
                globPatterns: ['**/*.{js,css,woff,woff2,ttf,eot}'],
                globDirectory: 'public/build',
                // Navigasi offline → fallback ke /offline
                navigateFallback: '/offline',
                navigateFallbackDenylist: [
                    /^\/admin\/livewire/,
                    /^\/admin\/question-image-upload/,
                    /^\/admin\/bap/,
                    /^\/api/,
                    /^\/pwa/,
                ],
                runtimeCaching: [
                    {
                        // Aset Vite (fingerprint-ed) → CacheFirst
                        urlPattern: /\/build\/.+\.(js|css)$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'cat-bapeten-assets',
                            expiration: { maxEntries: 80, maxAgeSeconds: 30 * 24 * 60 * 60 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        // Font & icon Filament → CacheFirst
                        urlPattern: /\/(fonts|css\/filament)\/.+/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'cat-bapeten-fonts',
                            expiration: { maxEntries: 40, maxAgeSeconds: 60 * 24 * 60 * 60 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        // Ikon PWA → CacheFirst
                        urlPattern: /\/pwa\/.+\.png$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'cat-bapeten-icons',
                            expiration: { maxEntries: 20, maxAgeSeconds: 365 * 24 * 60 * 60 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        // Halaman admin → NetworkFirst (data always fresh, fallback ke cache)
                        urlPattern: /^https?:\/\/.+\/admin(?!\/livewire)/,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'cat-bapeten-pages',
                            expiration: { maxEntries: 20, maxAgeSeconds: 24 * 60 * 60 },
                            cacheableResponse: { statuses: [200] },
                            networkTimeoutSeconds: 10,
                        },
                    },
                ],
                // Jangan cache Livewire/API sama sekali
                cleanupOutdatedCaches: true,
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
