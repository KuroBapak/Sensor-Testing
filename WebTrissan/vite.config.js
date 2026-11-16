import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny, google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/site/home.css',
                'resources/css/site/about.css',
                'resources/css/site/license.css',
                'resources/css/site/catalog.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                google('Montserrat', {
                    alias: 'montserrat',
                    weights: [400, 500, 600, 700, 800, 900],
                    preload: [{ weight: 700 }],
                }),
                google('Inter', {
                    alias: 'inter',
                    weights: [300, 400, 500, 600, 700],
                    preload: [{ weight: 400 }],
                }),
                google('JetBrains Mono', {
                    alias: 'jetbrains-mono',
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
