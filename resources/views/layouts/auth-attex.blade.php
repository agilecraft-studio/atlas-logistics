<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.attex.title-meta', ['page_title' => $page_title ?? 'Log In'])
    @include('layouts.attex.head-css')
    @vite(['resources/js/head.js'])
</head>
<body class="authentication-bg position-relative">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header py-3 text-center bg-primary">
                            <a href="{{ url('admin') }}">
                                <span><img src="{{ asset('assets/images/' . AtlasLogistics::get('logo_img')) }}" alt="logo" height="40"></span>
                            </a>
                        </div>
                        <div class="card-body p-4">
                            @yield('content')
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">{{ AtlasLogistics::get('app_name') }} — Atlas Logistics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-alt fw-medium">
        <span><script>document.write(new Date().getFullYear())</script> &copy; {{ AtlasLogistics::get('app_name') }}</span>
    </footer>
    @vite(['resources/js/app.js'])
    @include('layouts.attex.footer-script')
</body>
</html>
