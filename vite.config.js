import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        symfonyPlugin(

        ),
    ],
    base: "/build/",

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
