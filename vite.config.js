import {defineConfig} from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [
        symfonyPlugin(/* options */),
    ],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js"
            },
        },
    }
});