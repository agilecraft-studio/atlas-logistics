@extends('layouts.fleet')

@section('fleet_title', 'Fleet Operations Dashboard')
@section('fleet_subtitle', 'Monitor your fleet, trips, costs and performance in real time.')
@section('fleet_topbar_actions')
<form method="get" action="{{ route('logistics.dashboard') }}">
    <select name="period" class="fleet-period" aria-label="Dashboard period" onchange="this.form.submit()">
                            <option value="month" @selected(isset($currentPeriod) && $currentPeriod === 'month')>This month</option>
                            <option value="week" @selected(isset($currentPeriod) && $currentPeriod === 'week')>This week</option>
                            <option value="year" @selected(isset($currentPeriod) && $currentPeriod === 'year')>This year</option>
                        </select>
</form>
@endsection

@section('content')
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
@endsection
@section('scripts')
<script type="application/json" id="fleet-dashboard-data">@json($dashboard)</script>
@endsection

