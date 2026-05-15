import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/theme.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    css: {
        postcss: 'tools/config/postcss.config.js',
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
