@canany(['Fuel list','Vendor list','Parts list','WorkOrders list','Notes list','ServiceReminders list','ServiceItems list','CompanyServices list','VehicleBreakdown list','DriverAlert list'])
<li class="side-nav-title">@lang('menu.operation')</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarOps" aria-expanded="{{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/work-orders*') || Request::is('admin/notes*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/company-services*') || Request::is('admin/vehicle-breakdown*') || Request::is('admin/driver-alert*') ? 'true' : 'false' }}" aria-controls="sidebarOps" class="side-nav-link {{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/notes*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') ? 'active' : '' }}">
        <i class="ri-settings-2-line"></i><span> @lang('menu.operation') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/fuel*') || Request::is('admin/vendors*') || Request::is('admin/parts*') || Request::is('admin/work_order*') || Request::is('admin/notes*') || Request::is('admin/service-reminder*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/company-services*') || Request::is('admin/vehicle-breakdown*') || Request::is('admin/driver-alert*') ? 'show' : '' }}" id="sidebarOps">
        <ul class="side-nav-second-level">
            @can('Fuel list')
            <li><a href="{{ route('fuel.index') }}" class="@if(Request::is('admin/fuel*')) active @endif">@lang('menu.fuel')</a></li>
            @endcan
            @can('Vendor list')
            <li><a href="{{ route('vendors.index') }}" class="@if(Request::is('admin/vendors*')) active @endif">@lang('menu.vendors')</a></li>
            @endcan
            @can('Parts list')
            <li><a href="{{ route('parts.index') }}" class="@if(Request::is('admin/parts*')) active @endif">@lang('menu.manageParts')</a></li>
            <li><a href="{{ route('work_order.index') }}" class="@if(Request::is('admin/work_order*')) active @endif">@lang('menu.work_orders')</a></li>
            <li><a href="{{ route('mechanics') }}" class="@if(Request::is('admin/mechanic*')) active @endif">@lang('menu.mechanics')</a></li>
            @endcan
            @can('WorkOrders logs')
            <li><a href="{{ url('admin/work-orders/logs') }}" class="@if(Request::is('admin/work-orders/logs')) active @endif">@lang('fleet.work_orders_logs')</a></li>
            @endcan
            @can('Notes list')
            <li><a href="{{ route('notes.index') }}" class="@if(Request::is('admin/notes*')) active @endif">@lang('menu.notes')</a></li>
            @endcan
            @can('ServiceReminders list')
            <li><a href="{{ route('service-reminder.index') }}" class="@if(Request::is('admin/service-reminder*')) active @endif">@lang('fleet.serviceReminders')</a></li>
            @endcan
            @can('ServiceItems list')
            <li><a href="{{ route('service-item.index') }}" class="@if(Request::is('admin/service-item*')) active @endif">@lang('fleet.serviceItems')</a></li>
            @endcan
            @can('CompanyServices list')
            <li><a href="{{ route('company-services.index') }}" class="@if(Request::is('admin/company-services*')) active @endif">@lang('fleet.companyServices')</a></li>
            @endcan
            @can('VehicleBreakdown list')
            <li><a href="{{ route('vehicle-breakdown.index') }}" class="@if(Request::is('admin/vehicle-breakdown*')) active @endif">@lang('menu.vehicle_breakdown')</a></li>
            @endcan
            @can('DriverAlert list')
            <li><a href="{{ route('driver-alert.index') }}" class="@if(Request::is('admin/driver-alert*')) active @endif">@lang('menu.driver_alert')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
