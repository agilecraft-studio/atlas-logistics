@include('layouts.attex.legacy-css')
{{-- Atlas skin load-order contract (NEVER reorder):
     tokens.css -> Attex CSS (Vite) -> attex-brand-theme.css -> fleet-components.css --}}
<link rel="stylesheet" href="{{ asset('assets/fleet/tokens.css') }}">
@vite(['resources/scss/app.scss', 'resources/scss/icons.scss'])
<link rel="stylesheet" href="{{ asset('assets/fleet/attex-brand-theme.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fleet/fleet-components.css') }}">
@yield('css')
{{-- BC shim: old views use extra_css / styles --}}
@yield('extra_css')
@yield('styles')
