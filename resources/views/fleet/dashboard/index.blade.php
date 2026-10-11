@extends('layouts.attex')

@section('title', 'Fleet Operations Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/css/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/atlas-dashboard/fleet-dashboard.css') }}">
@endsection

@section('content')
<div class="fleet-dashboard">
    <div class="fleet-shell">
        <aside class="fleet-sidebar">
            <div class="fleet-brand">
                <div class="fleet-brand-mark"><i class="bi bi-truck-front-fill"></i></div>
                <div class="fleet-brand-copy">
                    <strong>Atlas Logistics</strong>
                    <small>SADC Cross-Border Transport</small>
                </div>
            </div>

            <div class="fleet-nav-label">WORKSPACE</div>
            <a class="fleet-nav active" href="{{ route('logistics.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

            <div class="fleet-nav-label">OPERATIONS</div>
            @can('Trips list')
                <a class="fleet-nav" href="{{ route('logistics.trips.index') }}"><i class="bi bi-signpost-2-fill"></i><span>Trips &amp; Dispatch</span><i class="bi bi-chevron-right nav-chevron"></i></a>
            @endcan
            @can('FleetRegister list')
                <a class="fleet-nav" href="{{ route('logistics.register.vehicles') }}"><i class="bi bi-truck-front"></i><span>Fleet</span><i class="bi bi-chevron-right nav-chevron"></i></a>
                <a class="fleet-nav" href="{{ route('logistics.register.drivers') }}"><i class="bi bi-person-badge"></i><span>Drivers</span><i class="bi bi-chevron-right nav-chevron"></i></a>
            @endcan

            <div class="fleet-nav-label">FINANCE &amp; ASSETS</div>
            @can('TripExpenses list')
                <a class="fleet-nav" href="{{ route('logistics.expenses.index') }}"><i class="bi bi-cash-stack"></i><span>Expenses &amp; Finance</span><i class="bi bi-chevron-right nav-chevron"></i></a>
            @endcan

            <div class="fleet-nav-label">CONTROL CENTRE</div>
            @can('Compliance list')
                <a class="fleet-nav" href="{{ route('logistics.compliance') }}"><i class="bi bi-file-earmark-check-fill"></i><span>Documents &amp; Compliance</span><i class="bi bi-chevron-right nav-chevron"></i></a>
            @endcan
            @can('LogisticsReports list')
                <a class="fleet-nav" href="{{ route('logistics.reports.profitability') }}"><i class="bi bi-bar-chart-line-fill"></i><span>Reports</span><i class="bi bi-chevron-right nav-chevron"></i></a>
            @endcan

            <div class="fleet-sidebar-footer">
                <div>🇲🇼 &nbsp; Malawi · SADC Region</div>
                <div class="fleet-region">Moving Africa Forward</div>
            </div>
        </aside>

        <main class="fleet-main">
            <header class="fleet-topbar">
                <div class="fleet-heading">
                    <button class="fleet-menu-button" type="button" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
                    <div>
                        <h1>Fleet Operations Dashboard</h1>
                        <p>Monitor your fleet, trips, costs and performance in real time.</p>
                    </div>
                </div>
                <div class="fleet-top-actions">
                    <form method="get" action="{{ route('logistics.dashboard') }}">
                        <select name="period" class="fleet-period" aria-label="Dashboard period">
                            <option value="month" @selected(isset($currentPeriod) && $currentPeriod === 'month')>This month</option>
                            <option value="week" @selected(isset($currentPeriod) && $currentPeriod === 'week')>This week</option>
                            <option value="year" @selected(isset($currentPeriod) && $currentPeriod === 'year')>This year</option>
                        </select>
                    </form>
                    <button class="fleet-notification" type="button" aria-label="Notifications"><i class="bi bi-bell"></i></button>
                    <div class="fleet-user">
                        @php
                            $dashboardUser = auth()->user();
                            $dashboardUserName = $dashboardUser ? $dashboardUser->name : 'Administrator';
                            $dashboardUserRole = ($dashboardUser && method_exists($dashboardUser, 'getRoleNames')) ? ($dashboardUser->getRoleNames()->first() ?: 'Administrator') : 'Administrator';
                            $dashboardNameParts = preg_split('/\s+/', trim($dashboardUserName));
                            $dashboardInitials = strtoupper(substr($dashboardNameParts[0] ?? 'A', 0, 1) . substr($dashboardNameParts[1] ?? '', 0, 1));
                        @endphp
                        <div class="fleet-avatar">{{ $dashboardInitials }}</div>
                        <div class="fleet-user-copy">
                            <strong>{{ $dashboardUserName }}</strong>
                            <small>{{ $dashboardUserRole }}</small>
                        </div>
                        <i class="bi bi-chevron-down small"></i>
                    </div>
                </div>
            </header>

            <div class="fleet-content">
@php
    $alertCount = $dashboard['expiring_documents'] + $dashboard['services_due'] + $dashboard['fuel_variance_alerts'] + $dashboard['pending_expense_approvals'] + $dashboard['missing_pods'];
@endphp

@include('fleet.dashboard.partials.kpi-card')
<section class="fleet-grid-main">
    @include('fleet.dashboard.partials.revenue-chart')
    @include('fleet.dashboard.partials.status-donut')
</section>
@include('fleet.dashboard.partials.trips-table')
@include('fleet.dashboard.partials.action-required')
<section class="fleet-grid-bottom">
    @include('fleet.dashboard.partials.availability')
    @include('fleet.dashboard.partials.cost-categories')
</section>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/apexcharts/dist/apexcharts.min.js') }}"></script>
<script type="application/json" id="fleet-dashboard-data">@json($dashboard)</script>
<script src="{{ asset('vendor/atlas-dashboard/dashboard.js') }}"></script>
@endsection

