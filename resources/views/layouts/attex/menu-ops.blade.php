{{-- Day-to-day maintenance records. Setup tables (parts, service items, vendors, mechanics) live under Master Data. --}}
@canany(['Fuel list', 'WorkOrders list', 'ServiceReminders list'])
<li class="side-nav-title">Maintenance</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarOps" aria-expanded="{{ Request::is('admin/fuel*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') ? 'true' : 'false' }}" aria-controls="sidebarOps" class="side-nav-link {{ Request::is('admin/fuel*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') ? 'active' : '' }}">
        <i class="ri-tools-line"></i><span> Maintenance </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/fuel*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*') ? 'show' : '' }}" id="sidebarOps">
        <ul class="side-nav-second-level">
            @can('WorkOrders list')
            <li><a href="{{ route('work_order.index') }}" class="@if(Request::is('admin/work_order*') && !Request::is('admin/work_order/logs')) active @endif">@lang('menu.work_orders')</a></li>
            <li><a href="{{ url('admin/work_order/logs') }}" class="@if(Request::is('admin/work_order/logs')) active @endif">@lang('fleet.work_order_logs')</a></li>
            @endcan
            @can('ServiceReminders list')
            <li><a href="{{ route('service-reminder.index') }}" class="@if(Request::is('admin/service-reminder*')) active @endif">@lang('fleet.serviceReminders')</a></li>
            @endcan
            @can('Fuel list')
            <li><a href="{{ route('fuel.index') }}" class="@if(Request::is('admin/fuel*')) active @endif">@lang('menu.fuel')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
