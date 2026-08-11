import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import {VitePWA} from 'vite-plugin-pwa';
export default defineConfig({
    plugins: [
        laravel({input: ['resources/css/app.css', 'resources/js/app.jsx'], refresh: true}),
        react(),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            scope: '/',
            includeAssets: ['favicon.png', 'icons/apple-touch-icon.png', 'icons/app-icon.svg'],
            manifest: {
                id: '/',
                name: 'terracosismos · Monitoreo Sísmico de Colombia',
                short_name: 'terracosismos',
                description: 'Monitoreo de sismos en Colombia con información en tiempo casi real y alertas personalizadas.',
                theme_color: '#71159d',
                background_color: '#f7f3fc',
                display: 'standalone',
                orientation: 'portrait-primary',
                scope: '/',
                start_url: '/',
                lang: 'es-CO',
                categories: ['weather', 'utilities', 'news'],
                icons: [
                    {src: '/icons/pwa-192.png', sizes: '192x192', type: 'image/png'},
                    {src: '/icons/pwa-512.png', sizes: '512x512', type: 'image/png'},
                    {src: '/icons/pwa-maskable-192.png', sizes: '192x192', type: 'image/png', purpose: 'maskable'},
                    {src: '/icons/pwa-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable'},
                    {src: '/icons/app-icon.svg', sizes: 'any', type: 'image/svg+xml', purpose: 'any'},
                ],
            },
        }),
    ],
});
