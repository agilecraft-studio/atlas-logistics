import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
                'resources/scss/icons.scss',
                'node_modules/daterangepicker/daterangepicker.css',
                'node_modules/quill/dist/quill.core.css',
                'node_modules/quill/dist/quill.snow.css',
                'node_modules/jquery-toast-plugin/dist/jquery.toast.min.css',
                'node_modules/select2/dist/css/select2.min.css',
                'node_modules/flatpickr/dist/flatpickr.min.css',
                'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
                'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
                'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css',
                'resources/js/app.js',
                'resources/js/head.js',
                'resources/js/layout.js',
                'node_modules/jquery/dist/jquery.js',
                'node_modules/daterangepicker/moment.min.js',
                'node_modules/jquery-toast-plugin/dist/jquery.toast.min.js',
                'node_modules/datatables.net/js/jquery.dataTables.min.js',
                'node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js',
                'node_modules/datatables.net-responsive/js/dataTables.responsive.min.js',
                'node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js',
                'node_modules/datatables.net-buttons/js/dataTables.buttons.min.js',
                'node_modules/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js',
                'node_modules/datatables.net-buttons/js/buttons.html5.min.js',
                'node_modules/datatables.net-buttons/js/buttons.print.min.js',
                'node_modules/select2/dist/js/select2.min.js',
                'node_modules/apexcharts/dist/apexcharts.min.js',
                'node_modules/flatpickr/dist/flatpickr.min.js',
                'node_modules/dropzone/dist/dropzone.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        sourcemap: false,
    },
    resolve: {
        alias: {
            $: 'jQuery',
        },
    },
});
