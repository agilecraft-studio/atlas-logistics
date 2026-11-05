{{-- Operational hubs: maintenance and fuel each own their tabs. Setup tables live under Master Data. --}}
@canany(['Fuel list', 'WorkOrders list', 'ServiceReminders list'])
<li class="side-nav-title">Maintenance</li>
@canany(['WorkOrders list', 'ServiceReminders list'])
<li class="side-nav-item">
    <a href="{{ route('maintenance.index') }}" class="side-nav-link @if(Request::is('admin/maintenance*') || Request::is('admin/work_order*') || Request::is('admin/service-reminder*')) active @endif">
        <i class="ri-tools-line"></i><span> Maintenance &amp; Work Orders </span>
    </a>
</li>
@endcanany
@can('Fuel list')
<li class="side-nav-item">
    <a href="{{ route('fuel.index') }}" class="side-nav-link @if(Request::is('admin/fuel*')) active @endif">
        <i class="ri-gas-station-line"></i><span> Fuel Management </span>
    </a>
</li>
@endcan
@endcanany
