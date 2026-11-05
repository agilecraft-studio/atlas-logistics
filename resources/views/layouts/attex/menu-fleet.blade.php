{{-- Master data: every lookup the fleet and logistics modules are built from. --}}
@canany(['VehicleType list', 'VehicleGroup list', 'Parts list', 'PartsCategory list', 'ServiceItems list', 'Mechanics list', 'Vendors list', 'MasterData list'])
<li class="side-nav-title">Master Data</li>

@canany(['VehicleType list', 'VehicleGroup list', 'Parts list', 'PartsCategory list', 'ServiceItems list', 'Mechanics list', 'Vendors list'])
@php($fleetMasterOpen = Request::is('admin/vehicle-types*') || Request::is('admin/vehicle_group*') || Request::is('admin/parts*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/vendors*'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarMasterFleet" aria-expanded="{{ $fleetMasterOpen ? 'true' : 'false' }}" aria-controls="sidebarMasterFleet" class="side-nav-link {{ $fleetMasterOpen ? 'active' : '' }}">
        <i class="ri-list-settings-line"></i><span> Fleet &amp; Parts Setup </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ $fleetMasterOpen ? 'show' : '' }}" id="sidebarMasterFleet">
        <ul class="side-nav-second-level">
            @can('VehicleType list')
            <li><a href="{{ route('vehicle-types.index') }}" class="@if(Request::is('admin/vehicle-types*')) active @endif">@lang('fleet.manage_vehicle_types')</a></li>
            @endcan
            @can('VehicleGroup list')
            <li><a href="{{ route('vehicle_group.index') }}" class="@if(Request::is('admin/vehicle_group*')) active @endif">@lang('fleet.manageGroup')</a></li>
            @endcan
            @can('Parts list')
            <li><a href="{{ route('parts.index') }}" class="@if(Request::is('admin/parts') || Request::is('admin/parts/*') || Request::is('admin/parts/create')) active @endif">@lang('menu.manageParts')</a></li>
            @endcan
            @can('PartsCategory list')
            <li><a href="{{ route('parts-category.index') }}" class="@if(Request::is('admin/parts-category*')) active @endif">@lang('fleet.parts_category')</a></li>
            @endcan
            @can('ServiceItems list')
            <li><a href="{{ route('service-item.index') }}" class="@if(Request::is('admin/service-item*')) active @endif">@lang('fleet.serviceItems')</a></li>
            @endcan
            @can('Mechanics list')
            <li><a href="{{ route('mechanic.index') }}" class="@if(Request::is('admin/mechanic*')) active @endif">@lang('menu.mechanics')</a></li>
            @endcan
            @can('Vendors list')
            <li><a href="{{ route('vendors.index') }}" class="@if(Request::is('admin/vendors*')) active @endif">@lang('menu.vendors')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany

@can('MasterData list')
@php($logisticsMasterOpen = Request::is('admin/logistics/master*'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarMasterLogistics" aria-expanded="{{ $logisticsMasterOpen ? 'true' : 'false' }}" aria-controls="sidebarMasterLogistics" class="side-nav-link {{ $logisticsMasterOpen ? 'active' : '' }}">
        <i class="ri-global-line"></i><span> Logistics Setup </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ $logisticsMasterOpen ? 'show' : '' }}" id="sidebarMasterLogistics">
        <ul class="side-nav-second-level">
            <li><a href="{{ route('logistics.master.index', ['entity' => 'countries']) }}" class="@if(Request::is('admin/logistics/master/countries*')) active @endif">Countries</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'currencies']) }}" class="@if(Request::is('admin/logistics/master/currencies*')) active @endif">Currencies</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'exchange-rates']) }}" class="@if(Request::is('admin/logistics/master/exchange-rates*')) active @endif">Exchange Rates</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'border-posts']) }}" class="@if(Request::is('admin/logistics/master/border-posts*')) active @endif">Border Posts</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'expense-categories']) }}" class="@if(Request::is('admin/logistics/master/expense-categories*')) active @endif">Trip Expense Categories</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'document-types']) }}" class="@if(Request::is('admin/logistics/master/document-types*')) active @endif">Document Types</a></li>
        </ul>
    </div>
</li>
@endcan
@endcanany
