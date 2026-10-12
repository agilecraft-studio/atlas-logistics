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
{{-- Shared fleet content interactions (confirm prompts, x-fleet.modal close).
     Shell/sidebar toggling is Attex-owned and was removed from this file. --}}
<script src="{{ asset('assets/fleet/fleet.js') }}"></script>
@yield('script')
{{-- BC shim: old views use script section name "script" already; also support "scripts" --}}
@yield('scripts')
@yield('script-bottom')
