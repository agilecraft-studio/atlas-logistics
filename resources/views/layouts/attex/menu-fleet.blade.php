@canany(['Vehicles list','VehicleType list','VehicleGroup list','VehicleInspection list'])
<li class="side-nav-title">@lang('menu.vehicles')</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarVehicles" aria-expanded="{{ Request::is('admin/vehicles*') || Request::is('admin/vehicle-types*') || Request::is('admin/vehicle_group*') || Request::is('admin/vehicle-reviews*') || Request::is('admin/vehicle-inspection*') ? 'true' : 'false' }}" aria-controls="sidebarVehicles" class="side-nav-link {{ Request::is('admin/vehicles*') || Request::is('admin/vehicle-types*') || Request::is('admin/vehicle_group*') || Request::is('admin/vehicle-reviews*') || Request::is('admin/vehicle-inspection*') ? 'active' : '' }}">
        <i class="ri-truck-line"></i><span> @lang('menu.vehicles') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/vehicles*') || Request::is('admin/vehicle-types*') || Request::is('admin/vehicle_group*') || Request::is('admin/vehicle-reviews*') || Request::is('admin/vehicle-inspection*') ? 'show' : '' }}" id="sidebarVehicles">
        <ul class="side-nav-second-level">
            @can('Vehicles list')
            <li><a href="{{ route('vehicles.index') }}" class="@if(Request::is('admin/vehicles*')) active @endif">@lang('menu.manageVehicles')</a></li>
            @endcan
            @can('VehicleType list')
            <li><a href="{{ route('vehicle-types.index') }}" class="@if(Request::is('admin/vehicle-types*')) active @endif">@lang('fleet.manage_vehicle_types')</a></li>
            @endcan
            @can('VehicleGroup list')
            <li><a href="{{ route('vehicle_group.index') }}" class="@if(Request::is('admin/vehicle_group*')) active @endif">@lang('fleet.manageGroup')</a></li>
            @endcan
            @can('VehicleInspection list')
            <li><a href="{{ url('admin/vehicle-reviews') }}" class="@if(Request::is('admin/vehicle-reviews*') || Request::is('admin/vehicle-inspection*') || Request::is('admin/view-vehicle-inspection*')) active @endif">@lang('fleet.vehicleInspection')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
@can('Drivers list')
<li class="side-nav-item">
    <a href="{{ route('drivers.index') }}" class="side-nav-link @if(Request::is('admin/drivers*')) active @endif">
        <i class="ri-contacts-line"></i><span> @lang('menu.drivers') </span>
    </a>
</li>
@endcan
@can('Customer list')
<li class="side-nav-item">
    <a href="{{ route('customers.index') }}" class="side-nav-link @if(Request::is('admin/customers*') || Request::is('admin/addresses*')) active @endif">
        <i class="ri-team-line"></i><span> @lang('menu.customers') </span>
    </a>
</li>
@endcan
