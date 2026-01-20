import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import sass from 'sass-embedded';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: { transformAssetUrls }
        }),
        quasar({
            sassVariables: 'resources/js/css/quasar.variables.scss'
        }),
    ],
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                implementation: sass,
                api: 'modern-compiler',
                silenceDeprecations: ['legacy-js-api'],
            },
            sass: {
                implementation: sass,
                api: 'modern-compiler',
                silenceDeprecations: ['legacy-js-api'],
            },
        },
    },
    resolve: {
        alias: {
            'src': '/resources/js',
            'components': '/resources/js/components',
            'layouts': '/resources/js/layouts',
            'pages': '/resources/js/pages',
            'assets': '/resources/js/assets',
            'boot': '/resources/js/boot',
        },
    },
});
