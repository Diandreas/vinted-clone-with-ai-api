import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    resolve: {
        alias: {
            '@': '/resources/js',
            // Stub web : @capacitor-firebase/messaging n'existe que sur Android natif.
            // Le code est toujours protégé par isNative() donc ce stub n'est jamais appelé.
            '@capacitor-firebase/messaging': path.resolve(__dirname, 'resources/js/stubs/capacitor-firebase-messaging.js'),
        },
    },
});
