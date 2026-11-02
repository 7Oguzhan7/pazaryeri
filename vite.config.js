import { defineConfig } from 'vite';
import inertia from '@inertiajs/vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        inertia(),
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
