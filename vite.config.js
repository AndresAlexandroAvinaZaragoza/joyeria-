import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/productos.css',
                'resources/css/inventario.css',
                'resources/css/usuarios.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
