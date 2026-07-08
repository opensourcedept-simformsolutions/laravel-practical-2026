import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],

    server: {
        host: "0.0.0.0",       // listen on all interfaces inside the container
        port: 5173,
        strictPort: true,
        cors: {
            origin: "*",
        },
        hmr: {
            host: "localhost",  // what the BROWSER connects to for the HMR websocket
        },
        watch: {
            usePolling: true,   // needed for file-change detection through Docker bind mounts
        },
    },
});
