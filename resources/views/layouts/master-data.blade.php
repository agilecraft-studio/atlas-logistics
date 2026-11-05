@extends('layouts.attex')

@section('page_title', __('menu.master_data'))

@section('content')
<div class="row g-3">
    <div class="col-xl-3 col-lg-4">
        <x-fleet.panel class="md-nav-panel">
            <div class="fleet-toolbar mb-2">
                <h5 class="mb-0"><i class="bi bi-database-gear me-2"></i>{{ __('menu.master_data') }}</h5>
                <a href="{{ route('master-data.index') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Master Data overview"><i class="bi bi-grid"></i></a>
            </div>
            @include('layouts.master-data-nav')
        </x-fleet.panel>
    </div>
    <div class="col-xl-9 col-lg-8 master-data-content">
        @yield('master-content')
    </div>
</div>

<div class="offcanvas offcanvas-end md-drawer" tabindex="-1" id="masterDataDrawer" aria-labelledby="masterDataDrawerLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="masterDataDrawerLabel">{{ __('menu.master_data') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <iframe id="masterDataDrawerFrame" title="Master Data form" src="about:blank" class="md-drawer-frame"></iframe>
    </div>
</div>
@endsection

@section('script-bottom')
<script src="{{ asset('assets/fleet/master-data.js') }}"></script>
@endsection
