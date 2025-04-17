import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['public/css/filament/app.css', 'public/js/filament/filament/app.js'],
            refresh: 'app/Livewire/**',
        }),
    ],
});
