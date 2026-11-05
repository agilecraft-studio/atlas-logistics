<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    @include('layouts.attex.head-css')
    @vite(['resources/js/head.js'])
    <style>body { background: #f4f6f8; }</style>
</head>
<body>
    <div class="container-fluid p-3">
        @yield('master-content')
    </div>
    @include('layouts.attex.footer-script')
    @vite(['resources/js/app.js'])
</body>
</html>
