import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/login.css',
                'resources/css/applications.css',
                'resources/css/application-create.css',
                'resources/js/app.js',
                'resources/js/login.js',
                'resources/js/applications.js',
                'resources/js/application-create.js',
                'resources/js/application-edit.js',
            ],
            refresh: true,
        }),
    ],
});
