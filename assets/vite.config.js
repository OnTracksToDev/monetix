import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [vue(), symfonyPlugin()],
    root: "./",
    base: "/build/",
    server: {
        port: 5173,
        strictPort: true,
    },
    build: {
        outDir: "../public/build",
        emptyOutDir: true,
        manifest: true,
        assetsDir: ".",
        chunkSizeWarningLimit: 800,
        rollupOptions: {
            input: {
                app: "./main.js",
            },
        },
    },
    publicDir: false,
});
