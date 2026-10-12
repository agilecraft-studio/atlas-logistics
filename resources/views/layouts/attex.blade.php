<!DOCTYPE html>
@if(Auth::check() && Auth::user()->getMeta('language') != null)
@php($language = Auth::user()->getMeta('language'))
@else
@php($language = AtlasLogistics::get('language'))
@endif
<html lang="en" data-bs-theme="light" data-layout="vertical" data-layout-mode="fluid" data-topbar-color="light" data-menu-color="dark" data-layout-position="fixed" data-sidenav-size="default" @if(isset($language) && $language == 'Arabic-ar') dir="rtl" @endif>
<head>
    @include('layouts.attex.title-meta', ['page_title' => $page_title ?? ''])
    @include('layouts.attex.head-css')
    @vite(['resources/js/head.js'])
</head>
<body>
    <div class="wrapper">
        @include('layouts.attex.topbar')
        @include('layouts.attex.sidebar')
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    @hasSection('page_title')
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ url('admin') }}">@lang('menu.Dashboard')</a></li>
                                        @yield('breadcrumb')
                                    </ol>
                                </div>
                                <h4 class="page-title">@yield('page_title')</h4>
                            </div>
                        </div>
                    </div>
                    @endif
                    @hasSection('fleet_topbar_actions')
                    <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center mb-3">
                        @yield('fleet_topbar_actions')
                    </div>
                    @endif
                    @include('layouts.attex.alerts')
                    @yield('content')
                </div>
            </div>
            @include('layouts.attex.footer')
        </div>
    </div>
    @include('layouts.attex.customizer')
    @include('layouts.attex.footer-script')
    @vite(['resources/js/app.js', 'resources/js/layout.js'])
</body>
</html>
