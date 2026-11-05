{{-- Configuration lives in one Master Data menu. Every screen renders inside layouts.master-data. --}}
@canany(['VehicleType list', 'VehicleGroup list', 'Parts list', 'PartsCategory list', 'ServiceItems list', 'Mechanics list', 'Vendors list', 'MasterData list', 'Settings list'])
<li class="side-nav-title">Master Data</li>

@php($masterOpen = Request::is('admin/master-data*') || Request::is('admin/vehicle-types*') || Request::is('admin/vehicle_group*') || Request::is('admin/parts*') || Request::is('admin/parts-category*') || Request::is('admin/service-item*') || Request::is('admin/mechanic*') || Request::is('admin/vendors*') || Request::is('admin/settings*') || Request::is('admin/logistics/master*'))

<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarMasterData" aria-expanded="{{ $masterOpen ? 'true' : 'false' }}" aria-controls="sidebarMasterData" class="side-nav-link {{ $masterOpen ? 'active' : '' }}">
        <i class="ri-database-2-line"></i><span> Master Data </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ $masterOpen ? 'show' : '' }}" id="sidebarMasterData">
        <ul class="side-nav-second-level">
            <li><a href="{{ route('master-data.index') }}" class="@if(Request::is('admin/master-data*')) active @endif">Overview</a></li>

            @canany(['VehicleType list', 'VehicleGroup list'])
            <li><span class="menu-group-label">Fleet Configuration</span></li>
            @can('VehicleType list')
            <li><a href="{{ route('vehicle-types.index') }}" class="@if(Request::is('admin/vehicle-types*')) active @endif">Vehicle Types</a></li>
            @endcan
            @can('VehicleGroup list')
            <li><a href="{{ route('vehicle_group.index') }}" class="@if(Request::is('admin/vehicle_group*')) active @endif">Vehicle Groups</a></li>
            @endcan
            @endcanany

            @canany(['Parts list', 'PartsCategory list', 'ServiceItems list'])
            <li><span class="menu-group-label">Inventory &amp; Services</span></li>
            @can('Parts list')
            <li><a href="{{ route('parts.index') }}" class="@if(Request::is('admin/parts') || Request::is('admin/parts/*')) active @endif">Parts</a></li>
            @endcan
            @can('PartsCategory list')
            <li><a href="{{ route('parts-category.index') }}" class="@if(Request::is('admin/parts-category*')) active @endif">Parts Categories</a></li>
            @endcan
            @can('ServiceItems list')
            <li><a href="{{ route('service-item.index') }}" class="@if(Request::is('admin/service-item*')) active @endif">Service Items</a></li>
            @endcan
            @endcanany

            @canany(['Mechanics list', 'Vendors list'])
            <li><span class="menu-group-label">Partners &amp; Resources</span></li>
            @can('Mechanics list')
            <li><a href="{{ route('mechanic.index') }}" class="@if(Request::is('admin/mechanic*')) active @endif">Mechanics</a></li>
            @endcan
            @can('Vendors list')
            <li><a href="{{ route('vendors.index') }}" class="@if(Request::is('admin/vendors*')) active @endif">Vendors</a></li>
            @endcan
            @endcanany

            @can('Settings list')
            <li><span class="menu-group-label">System Settings</span></li>
            <li><a href="{{ route('settings.index') }}" class="@if(Request::is('admin/settings*')) active @endif">General Settings</a></li>
            @endcan

            @can('MasterData list')
            <li><span class="menu-group-label">Logistics Reference</span></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'countries']) }}" class="@if(Request::is('admin/logistics/master/countries*')) active @endif">Countries</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'currencies']) }}" class="@if(Request::is('admin/logistics/master/currencies*')) active @endif">Currencies</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'exchange-rates']) }}" class="@if(Request::is('admin/logistics/master/exchange-rates*')) active @endif">Exchange Rates</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'border-posts']) }}" class="@if(Request::is('admin/logistics/master/border-posts*')) active @endif">Border Posts</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'expense-categories']) }}" class="@if(Request::is('admin/logistics/master/expense-categories*')) active @endif">Trip Expense Categories</a></li>
            <li><a href="{{ route('logistics.master.index', ['entity' => 'document-types']) }}" class="@if(Request::is('admin/logistics/master/document-types*')) active @endif">Document Types</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
