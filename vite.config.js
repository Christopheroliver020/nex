import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input:
            [
                'resources/css/app.css',
                'resources/css/nex-feed.css',
                'resources/css/nex-admin.css',
                'resources/css/nex-auth.css',
                'resources/css/nex-home.css',
                'resources/css/nex-profile.css',
                'resources/css/nex-notifications.css',
                'resources/css/nex-suspended.css',
                'resources/js/app.js',
                'resources/js/nex-feed.js',
                'resources/js/nex-auth.js',
                'resources/js/nex-home.js',
                'resources/js/nex-profile.js',
            ],
            refresh: true,
        }),
    ],
});
