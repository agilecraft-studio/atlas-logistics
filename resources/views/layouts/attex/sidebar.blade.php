<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
    <a href="{{ url('admin') }}" class="logo logo-light">
        <span class="logo-lg"><img src="{{ asset('assets/images/' . AtlasLogistics::get('logo_img')) }}" alt="logo" height="40"></span>
        <span class="logo-sm"><img src="{{ asset('assets/images/' . AtlasLogistics::get('icon_img')) }}" alt="small logo" height="32"></span>
    </a>
    <a href="{{ url('admin') }}" class="logo logo-dark">
        <span class="logo-lg"><img src="{{ asset('assets/images/' . AtlasLogistics::get('logo_img')) }}" alt="logo" height="40"></span>
        <span class="logo-sm"><img src="{{ asset('assets/images/' . AtlasLogistics::get('icon_img')) }}" alt="small logo" height="32"></span>
    </a>
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>
    <div class="button-close-fullsidebar"><i class="ri-close-fill align-middle"></i></div>
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        @if(auth()->check())
        <div class="leftbar-user">
            <a href="{{ url('admin/change-details/' . auth()->id()) }}">
                @if(auth()->user()->profile_image != null)
                <img src="{{ asset('uploads/' . auth()->user()->profile_image) }}" alt="user-image" height="42" class="rounded-circle shadow-sm">
                @else
                <img src="{{ asset('assets/images/no-user.jpg') }}" alt="user-image" height="42" class="rounded-circle shadow-sm">
                @endif
                <span class="leftbar-user-name mt-2">{{ auth()->user()->name }}</span>
            </a>
        </div>
        @endif
        <ul class="side-nav">
            <li class="side-nav-title">@lang('menu.main')</li>
            <li class="side-nav-item">
                <a href="{{ url('admin/') }}" class="side-nav-link @if(Request::is('admin') || Request::is('admin/')) active @endif">
                    <i class="ri-dashboard-line"></i><span> @lang('menu.dashboard') </span>
                </a>
            </li>
            @include('layouts.attex.menu-bookings')
            @include('layouts.attex.menu-fleet')
            @include('layouts.attex.menu-ops')
            @include('layouts.attex.menu-finance')
            @include('layouts.attex.menu-admin')
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
