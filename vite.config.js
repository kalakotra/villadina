import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig(({ command }) => ({
    root: resolve(__dirname, 'app/client/src'),
    base: command === 'build' ? '/_resources/app/client/dist/' : '/',
    publicDir: false,
    build: {
        outDir: resolve(__dirname, 'app/client/dist'),
        emptyOutDir: true,
        manifest: 'manifest.json',
        sourcemap: false,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'app/client/src/js/main.js'),
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                // Bootstrap 5.3 still uses @import + global functions
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'legacy-js-api'],
                quietDeps: true,
            },
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://localhost:5173',
    },
}));
