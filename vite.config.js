import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: '192.168.18.4',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/manager/main.js',
                'resources/css/app.css',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
