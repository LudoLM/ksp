import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
        symfonyPlugin(

        ),
    ],
    base: "/build/",
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './assets/src')
        }
    },
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
    },

    build: {
        manifest: true,
        emptyOutDir: true,
        outDir: './public/build',
        rollupOptions: {
            input: {
                app: "./assets/app.js"
            },
        }
    },
});
