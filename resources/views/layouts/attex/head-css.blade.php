@vite(['resources/scss/app.scss', 'resources/scss/icons.scss'])
@yield('css')
{{-- BC shim: old views use extra_css / styles --}}
@yield('extra_css')
@yield('styles')
