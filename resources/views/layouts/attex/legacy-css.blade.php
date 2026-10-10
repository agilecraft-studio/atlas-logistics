{{-- Legacy plugin CSS the old AdminLTE layout loaded. Loaded BEFORE the Vite (Attex) CSS so Attex wins on conflicts. --}}
<link rel="stylesheet" href="{{ asset('assets/css/plugins-fa-all.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins-fullcalendar.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins-fullcalendar.print.css') }}" media="print">
<link rel="stylesheet" href="{{ asset('assets/css/plugins-dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/cdn-buttons.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pnotify.custom.min.css') }}" media="all">
<style>
    tfoot input { width: 100%; padding: 3px; box-sizing: border-box; font-size: 0.6em; height: 35px !important; }
    .error { font-weight: 400 !important; color: red; }
</style>
