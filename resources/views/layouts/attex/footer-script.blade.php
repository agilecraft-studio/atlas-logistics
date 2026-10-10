<!-- bundle -->
<script>
    window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!};
    window.AtlasToast = function(message, type) {
        type = type || 'success';
        if (window.$ && $.toast) {
            $.toast({ text: message, icon: type, position: 'top-right', hideAfter: 4000 });
        } else { alert(message); }
    };
</script>
@include('layouts.attex.legacy-scripts')
@include('layouts.attex.legacy-init')
@yield('script')
{{-- BC shim: old views use script section name "script" already; also support "scripts" --}}
@yield('scripts')
@yield('script-bottom')
