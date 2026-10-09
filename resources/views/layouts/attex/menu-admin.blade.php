@canany(['Users list','Roles list','Team list','Settings list','Chat list'])
<li class="side-nav-title">@lang('menu.userManagement')</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarAdmin" aria-expanded="{{ Request::is('admin/users*') || Request::is('admin/roles*') || Request::is('admin/team*') || Request::is('admin/chat*') || Request::is('admin/api-settings*') ? 'true' : 'false' }}" aria-controls="sidebarAdmin" class="side-nav-link {{ Request::is('admin/users*') || Request::is('admin/roles*') || Request::is('admin/team*') || Request::is('admin/chat*') ? 'active' : '' }}">
        <i class="ri-admin-line"></i><span> @lang('menu.userManagement') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/users*') || Request::is('admin/roles*') || Request::is('admin/team*') || Request::is('admin/chat*') || Request::is('admin/api-settings*') ? 'show' : '' }}" id="sidebarAdmin">
        <ul class="side-nav-second-level">
            @can('Users list')
            <li><a href="{{ route('users.index') }}" class="@if(Request::is('admin/users*')) active @endif">@lang('menu.users')</a></li>
            @endcan
            @can('Roles list')
            <li><a href="{{ route('roles.index') }}" class="@if(Request::is('admin/roles*')) active @endif">@lang('fleet.roles')</a></li>
            @endcan
            @can('Team list')
            <li><a href="{{ route('team.index') }}" class="@if(Request::is('admin/team*')) active @endif">@lang('fleet.team')</a></li>
            @endcan
            @can('Chat list')
            <li><a href="{{ route('chat.index') }}" class="@if(Request::is('admin/chat*')) active @endif">@lang('fleet.chat')</a></li>
            @endcan
            @can('Settings list')
            <li><a href="{{ url('admin/api-settings') }}" class="@if(Request::is('admin/api-settings*')) active @endif">@lang('menu.api_settings')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
@can('Settings list')
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="{{ Request::is('admin/settings*') || Request::is('admin/fare-settings*') || Request::is('admin/chat-settings*') || Request::is('admin/twilio-settings*') || Request::is('admin/traccar-settings*') || Request::is('admin/cancel-reason*') ? 'true' : 'false' }}" aria-controls="sidebarSettings" class="side-nav-link {{ Request::is('admin/settings*') || Request::is('admin/fare-settings*') ? 'active' : '' }}">
        <i class="ri-settings-3-line"></i><span> @lang('menu.settings') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/settings*') || Request::is('admin/fare-settings*') || Request::is('admin/chat-settings*') || Request::is('admin/twilio-settings*') || Request::is('admin/traccar-settings*') || Request::is('admin/cancel-reason*') ? 'show' : '' }}" id="sidebarSettings">
        <ul class="side-nav-second-level">
            <li><a href="{{ url('admin/settings') }}" class="@if(Request::is('admin/settings')) active @endif">@lang('menu.general_settings')</a></li>
            <li><a href="{{ url('admin/fare-settings') }}" class="@if(Request::is('admin/fare-settings*')) active @endif">@lang('menu.fare_settings')</a></li>
            <li><a href="{{ route('chat_settings.index') }}" class="@if(Request::is('admin/chat-settings*')) active @endif">@lang('fleet.chat') @lang('menu.settings')</a></li>
            <li><a href="{{ url('admin/twilio-settings') }}" class="@if(Request::is('admin/twilio-settings*')) active @endif">@lang('fleet.twilio_settings')</a></li>
            <li><a href="{{ route('traccar.settings') }}" class="@if(Request::is('admin/traccar-settings*')) active @endif">@lang('fleet.traccar_settings')</a></li>
            <li><a href="{{ route('cancel-reason.index') }}" class="@if(Request::is('admin/cancel-reason*')) active @endif">@lang('fleet.cancellation')</a></li>
        </ul>
    </div>
</li>
@endcan
@if(auth()->check() && auth()->user()->user_type != 'D')
<li class="side-nav-item">
    <a href="{{ url('admin/change-details/' . auth()->id()) }}" class="side-nav-link @if(Request::is('admin/change-details*')) active @endif">
        <i class="ri-user-settings-line"></i><span> @lang('fleet.change_details') </span>
    </a>
</li>
@endif
<li class="side-nav-item">
    <a href="{{ url('admin/addresses') }}" class="side-nav-link @if(Request::is('admin/addresses*')) active @endif">
        <i class="ri-map-pin-line"></i><span> @lang('fleet.addresses') </span>
    </a>
</li>
