import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/login.js',
                'resources/js/pages/inicio.js',
                'resources/js/pages/configuracoes.js',
                'resources/js/pages/pacientes.js',
                'resources/js/pages/fichas.js',
                'resources/js/pages/colaboradores.js',
                'resources/js/pages/portal.js',
            ],
            refresh: true,
            fonts: [
                bunny('Hind', {
                    weights: [400, 500, 600],
                }),
                bunny('Montserrat', {
                    weights: [600, 700],
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
