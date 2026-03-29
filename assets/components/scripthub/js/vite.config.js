import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [vue()],
    build: {
        outDir: resolve(__dirname, '../mgr/vue-dist'),
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'scripthub-admin': resolve(__dirname, 'src/admin.js'),
            },
            external: [
                'vue',
                'pinia',
                'primevue',
                /^@vuetools\/.*/,
            ],
            output: {
                format: 'es',
                entryFileNames: '[name].min.js',
                chunkFileNames: '[name]-[hash].js',
                assetFileNames: '[name].min.[ext]',
            },
        },
        cssCodeSplit: false,
        minify: 'esbuild',
    },
})
