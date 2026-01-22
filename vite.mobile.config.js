import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import sass from 'sass-embedded';
import path from 'path';

export default defineConfig({
    plugins: [
        vue({
            template: { transformAssetUrls }
        }),
        quasar({
            sassVariables: 'resources/js/css/quasar.variables.scss'
        }),
    ],
    build: {
        outDir: 'dist',
        rollupOptions: {
            input: {
                index: 'mobile.html',
            },
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
            'src': path.resolve(__dirname, 'resources/js'),
            'components': path.resolve(__dirname, 'resources/js/components'),
            'layouts': path.resolve(__dirname, 'resources/js/layouts'),
            'pages': path.resolve(__dirname, 'resources/js/pages'),
            'assets': path.resolve(__dirname, 'resources/js/assets'),
            'boot': path.resolve(__dirname, 'resources/js/boot'),
        },
    },
});
