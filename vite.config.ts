import inertia from "@inertiajs/vite";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import babel from "@rolldown/plugin-babel";
import tailwindcss from "@tailwindcss/vite";
import react, { reactCompilerPreset } from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import { defineConfig, lazyPlugins } from "vite-plus";

export default defineConfig({
    // Menyiapkan pipeline aset Laravel, Inertia, React, Tailwind, dan Wayfinder.
    plugins: lazyPlugins(() => [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.tsx", "resources/js/alpine.ts"],
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react(),
        babel({
            presets: [reactCompilerPreset()],
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ]),
    server: {
        // Mengabaikan folder editor dan dependency agar perubahan di sana tidak memicu reload.
        watch: {
            ignored: [
                "**/.agents/**",
                "**/.claude/**",
                "**/.cursor/**",
                "**/.junie/**",
                "**/vendor/**",
            ],
        },
    },
    lint: {
        // Mengecualikan dependency dan berkas generated dari pemeriksaan lint aplikasi.
        ignorePatterns: [
            "vendor/**",
            "node_modules/**",
            "public/**",
            "bootstrap/ssr/**",
            "tailwind.config.js",
            "resources/js/actions/**",
            "resources/js/components/ui/*",
            "resources/js/routes/**",
            "resources/js/wayfinder/**",
        ],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        // Menetapkan gaya format dan aturan pengurutan kelas Tailwind.
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: "css",
        ignorePatterns: [
            ".github/**",
            "composer.json",
            "resources/js/components/ui/*",
            "resources/views/mail/*",
        ],
        sortTailwindcss: {
            functions: ["clsx", "cn", "cva"],
            stylesheet: "resources/css/app.css",
        },
    },
});
