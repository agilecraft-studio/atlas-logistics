{{-- Fleet register, compliance and customers. Master data lives in menu-fleet.blade.php. --}}
@canany(['FleetRegister list', 'Trailers list', 'Vehicles list', 'Drivers list', 'Compliance list', 'VehicleInspection list', 'LogisticsReports list', 'Customer list'])
<li class="side-nav-title">Fleet</li>

@canany(['FleetRegister list', 'Trailers list', 'Vehicles list', 'Drivers list'])
@php($registerOpen = Request::is('admin/logistics/register*') || Request::is('admin/logistics/trailers*') || Request::is('admin/logistics/components*') || Request::is('admin/vehicles*') || Request::is('admin/drivers*'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarFleetRegister" aria-expanded="{{ $registerOpen ? 'true' : 'false' }}" aria-controls="sidebarFleetRegister" class="side-nav-link {{ $registerOpen ? 'active' : '' }}">
        <i class="ri-truck-line"></i><span> Fleet Register </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ $registerOpen ? 'show' : '' }}" id="sidebarFleetRegister">
        <ul class="side-nav-second-level">
            @can('FleetRegister list')
            <li><a href="{{ route('logistics.register.vehicles') }}" class="@if(Request::is('admin/logistics/register/vehicles*')) active @endif">Trucks</a></li>
            <li><a href="{{ route('logistics.register.drivers') }}" class="@if(Request::is('admin/logistics/register/drivers*')) active @endif">Drivers</a></li>
            @else
            @can('Vehicles list')
            <li><a href="{{ route('vehicles.index') }}" class="@if(Request::is('admin/vehicles*')) active @endif">Trucks</a></li>
            @endcan
            @can('Drivers list')
            <li><a href="{{ route('drivers.index') }}" class="@if(Request::is('admin/drivers*')) active @endif">Drivers</a></li>
            @endcan
            @endcan
            @can('Trailers list')
            <li><a href="{{ route('logistics.trailers.index') }}" class="@if(Request::is('admin/logistics/trailers*')) active @endif">Trailers</a></li>
            @endcan
            @can('FleetRegister list')
            <li><a href="{{ route('logistics.components.index') }}" class="@if(Request::is('admin/logistics/components*')) active @endif">Tyres &amp; Fitted Parts</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany

@canany(['Compliance list', 'VehicleInspection list'])
@php($complianceOpen = Request::is('admin/logistics/compliance*') || Request::is('admin/logistics/documents*') || Request::is('admin/vehicle-reviews*') || Request::is('admin/vehicle-inspection*') || Request::is('admin/view-vehicle-inspection*'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarCompliance" aria-expanded="{{ $complianceOpen ? 'true' : 'false' }}" aria-controls="sidebarCompliance" class="side-nav-link {{ $complianceOpen ? 'active' : '' }}">
        <i class="ri-shield-check-line"></i><span> Compliance </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ $complianceOpen ? 'show' : '' }}" id="sidebarCompliance">
        <ul class="side-nav-second-level">
            @can('Compliance list')
            <li><a href="{{ route('logistics.compliance') }}" class="@if(Request::is('admin/logistics/compliance*')) active @endif">Compliance Dashboard</a></li>
            <li><a href="{{ route('logistics.documents.index') }}" class="@if(Request::is('admin/logistics/documents*')) active @endif">Documents</a></li>
            @endcan
            @can('VehicleInspection list')
            <li><a href="{{ route('vehicle_reviews') }}" class="@if(Request::is('admin/vehicle-reviews*')) active @endif">@lang('fleet.vehicle_inspection')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany

@can('LogisticsReports list')
<li class="side-nav-item">
    <a href="{{ route('logistics.dashboard') }}" class="side-nav-link @if(Request::is('admin/logistics/dashboard*')) active @endif">
        <i class="ri-dashboard-3-line"></i><span> Fleet Dashboard </span>
    </a>
</li>
@endcan

@can('Customer list')
<li class="side-nav-item">
    <a href="{{ route('customers.index') }}" class="side-nav-link @if(Request::is('admin/customers*')) active @endif">
        <i class="ri-team-line"></i><span> @lang('menu.customers') </span>
    </a>
</li>
@endcan
@endcanany
