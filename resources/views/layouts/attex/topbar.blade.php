<!-- ========== Topbar Start ========== -->
<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">
            <div class="logo-topbar">
                <a href="{{ url('admin') }}" class="logo-light">
                    <span class="logo-lg"><img src="{{ asset('assets/images/' . AtlasLogistics::get('logo_img')) }}" alt="logo" height="40"></span>
                    <span class="logo-sm"><img src="{{ asset('assets/images/' . AtlasLogistics::get('icon_img')) }}" alt="small logo" height="32"></span>
                </a>
                <a href="{{ url('admin') }}" class="logo-dark">
                    <span class="logo-lg"><img src="{{ asset('assets/images/' . AtlasLogistics::get('logo_img')) }}" alt="logo" height="40"></span>
                    <span class="logo-sm"><img src="{{ asset('assets/images/' . AtlasLogistics::get('icon_img')) }}" alt="small logo" height="32"></span>
                </a>
            </div>
            <button class="button-toggle-menu"><i class="ri-menu-2-fill"></i></button>
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines"><span></span><span></span><span></span></div>
            </button>
        </div>
        <ul class="topbar-menu d-flex align-items-center gap-3">
            @if(auth()->check() && auth()->user()->user_type != 'D')
            <li class="d-none d-md-inline-block">
                <a class="nav-link" href="{{ url('admin/bookings/calendar') }}" title="@lang('menu.calendar')">
                    <i class="ri-calendar-event-line fs-22"></i>
                </a>
            </li>
            @endif
            <li class="d-none d-sm-inline-block">
                <a class="nav-link" data-bs-toggle="offcanvas" href="#theme-settings-offcanvas" title="Theme Settings">
                    <i class="ri-settings-3-line fs-22"></i>
                </a>
            </li>
            <li class="d-none d-sm-inline-block">
                <div class="nav-link" id="light-dark-mode" data-bs-toggle="tooltip" data-bs-placement="left" title="Theme Mode">
                    <i class="ri-moon-line fs-22"></i>
                </div>
            </li>
            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        @if(auth()->check() && auth()->user()->profile_image != null)
                        <img src="{{ asset('uploads/' . auth()->user()->profile_image) }}" alt="user-image" width="32" class="rounded-circle">
                        @else
                        <img src="{{ asset('assets/images/no-user.jpg') }}" alt="user-image" width="32" class="rounded-circle">
                        @endif
                    </span>
                    <span class="d-lg-flex flex-column gap-1 d-none">
                        <h5 class="my-0">{{ auth()->check() ? auth()->user()->name : '' }}</h5>
                        <h6 class="my-0 fw-normal">{{ auth()->check() ? (auth()->user()->getRoleNames()->first() ?? auth()->user()->user_type) : '' }}</h6>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    <div class="dropdown-header noti-title"><h6 class="text-overflow m-0">@lang('fleet.hello') {{ auth()->check() ? auth()->user()->name : '' }}</h6></div>
                    @if(auth()->check())
                    <a href="{{ url('admin/change-details/' . auth()->id()) }}" class="dropdown-item">
                        <i class="ri-account-circle-line fs-18 align-middle me-1"></i><span>@lang('fleet.my_profile')</span>
                    </a>
                    @endif
                    <form method="POST" action="{{ url('admin/logout') }}">
                        @csrf
                        <a onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item" style="cursor:pointer;">
                            <i class="ri-logout-box-line fs-18 align-middle me-1"></i><span>@lang('menu.logout')</span>
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
<!-- ========== Topbar End ========== -->
