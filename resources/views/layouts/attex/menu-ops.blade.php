@canany(['Fuel list','Vendors list','Parts list','WorkOrders list','Mechanics list','ServiceReminders list','ServiceItems list','VehicleBreakdown list','DriverAlert list'])
<li class="side-nav-title">@lang('fleet.parts')</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarOps" aria-expanded="{{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/vehicle-breakdown*') || Request::is('admin/driver-alert*') ? 'true' : 'false' }}" aria-controls="sidebarOps" class="side-nav-link {{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') ? 'active' : '' }}">
        <i class="ri-settings-2-line"></i><span> @lang('fleet.parts') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/vehicle-breakdown*') || Request::is('admin/driver-alert*') ? 'show' : '' }}" id="sidebarOps">
        <ul class="side-nav-second-level">
            @can('Fuel list')
            <li><a href="{{ route('fuel.index') }}" class="@if(Request::is('admin/fuel*')) active @endif">@lang('menu.fuel')</a></li>
            @endcan
            @can('Vendors list')
            <li><a href="{{ route('vendors.index') }}" class="@if(Request::is('admin/vendors*')) active @endif">@lang('menu.vendors')</a></li>
            @endcan
            @can('Parts list')
                @cannot('FleetRegister list')
                <li><a href="{{ route('parts.index') }}" class="@if(Request::is('admin/parts*')) active @endif">Parts Inventory</a></li>
                @endcannot
            @endcan
            @can('WorkOrders list')
            <li><a href="{{ route('work_order.index') }}" class="@if(Request::is('admin/work_order*') && !Request::is('admin/work_order/logs')) active @endif">@lang('menu.work_orders')</a></li>
            <li><a href="{{ url('admin/work_order/logs') }}" class="@if(Request::is('admin/work_order/logs')) active @endif">@lang('fleet.work_order_logs')</a></li>
            @endcan
            @can('Mechanics list')
            <li><a href="{{ route('mechanic.index') }}" class="@if(Request::is('admin/mechanic*')) active @endif">@lang('menu.mechanics')</a></li>
            @endcan
            @can('ServiceReminders list')
            <li><a href="{{ route('service-reminder.index') }}" class="@if(Request::is('admin/service-reminder*')) active @endif">@lang('fleet.serviceReminders')</a></li>
            @endcan
            @can('ServiceItems list')
            <li><a href="{{ route('service-item.index') }}" class="@if(Request::is('admin/service-item*')) active @endif">@lang('fleet.serviceItems')</a></li>
            @endcan
            @can('VehicleBreakdown list')
            <li><a href="{{ route('vehicle-breakdown.index') }}" class="@if(Request::is('admin/vehicle-breakdown*')) active @endif">@lang('fleet.vehicle_breakdown')</a></li>
            @endcan
            @can('DriverAlert list')
            <li><a href="{{ route('driver-alert.index') }}" class="@if(Request::is('admin/driver-alert*')) active @endif">@lang('fleet.driver_alert')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
