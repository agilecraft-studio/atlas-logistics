{{-- Cross-border logistics modules (systemDesign.md). --}}
@canany(['Compliance list', 'FleetRegister list', 'Trailers list', 'MasterData list'])
<li class="side-nav-title">Logistics</li>

@can('Compliance list')
<li class="side-nav-item">
    <a href="{{ route('logistics.compliance') }}" class="side-nav-link @if(Request::is('admin/logistics/compliance*')) active @endif">
        <i class="ri-alarm-warning-line"></i><span> Compliance Dashboard </span>
    </a>
</li>
@endcan

@canany(['FleetRegister list', 'Trailers list', 'Compliance list'])
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarFleetRegister" aria-expanded="{{ Request::is('admin/logistics/register*') || Request::is('admin/logistics/trailers*') || Request::is('admin/logistics/components*') || Request::is('admin/logistics/documents*') ? 'true' : 'false' }}" class="side-nav-link">
        <i class="ri-truck-line"></i><span> Fleet Register </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/logistics/register*') || Request::is('admin/logistics/trailers*') || Request::is('admin/logistics/components*') || Request::is('admin/logistics/documents*') ? 'show' : '' }}" id="sidebarFleetRegister">
        <ul class="side-nav-second-level">
            @can('FleetRegister list')
            <li><a href="{{ route('logistics.register.vehicles') }}" class="@if(Request::is('admin/logistics/register/vehicles*')) active @endif">Trucks</a></li>
            @endcan
            @can('Trailers list')
            <li><a href="{{ route('logistics.trailers.index') }}" class="@if(Request::is('admin/logistics/trailers*')) active @endif">Trailers</a></li>
            @endcan
            @can('FleetRegister list')
            <li><a href="{{ route('logistics.register.drivers') }}" class="@if(Request::is('admin/logistics/register/drivers*')) active @endif">Drivers</a></li>
            <li><a href="{{ route('logistics.components.index') }}" class="@if(Request::is('admin/logistics/components*')) active @endif">Tyres &amp; Parts</a></li>
            @endcan
            @can('Compliance list')
            <li><a href="{{ route('logistics.documents.index') }}" class="@if(Request::is('admin/logistics/documents*')) active @endif">Documents</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany

@can('MasterData list')
<li class="side-nav-item">
    <a href="{{ route('logistics.master.index', ['entity' => 'countries']) }}" class="side-nav-link @if(Request::is('admin/logistics/master*')) active @endif">
        <i class="ri-global-line"></i><span> Master Data </span>
    </a>
</li>
@endcan
@endcanany
