@extends('layouts.fleet')
@section('fleet_title', 'Trip Profitability Report')
@section('fleet_subtitle', 'Revenue, cost and margin per trip')
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<x-fleet.panel title="Filters" icon="bi-funnel-fill">
    <form method="GET" action="{{ route('logistics.reports.profitability') }}" class="fleet-form row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
        <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="both" {{ $status === 'both' ? 'selected' : '' }}>Both Completed & Closed</option><option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed Only</option><option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed Only</option></select></div>
        <div class="col-md-3 d-flex gap-2"><button type="submit" class="fleet-btn flex-grow-1">Filter</button><a href="{{ route('logistics.reports.profitability', ['from' => $from, 'to' => $to, 'status' => $status, 'export' => 'csv']) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</a></div>
    </form>
</x-fleet.panel>
<section class="fleet-kpi-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <x-fleet.kpi label="Total Revenue" :value="$m($total['revenue']) . ' ' . $base" icon="bi-graph-up-arrow" color="bg-green" />
    <x-fleet.kpi label="Total Cost" :value="$m($total['cost']) . ' ' . $base" icon="bi-coin" color="bg-red" />
    <x-fleet.kpi label="Total Profit" :value="$m($total['profit']) . ' ' . $base" icon="bi-bar-chart-line-fill" color="bg-cyan" />
    <x-fleet.kpi label="Pending Approvals" :value="$m($total['pending']) . ' ' . $base" icon="bi-hourglass-split" color="bg-amber" />
</section>
<x-fleet.panel title="Trip Details" icon="bi-table">
    <x-fleet.table>
        <thead class="table-light"><tr><th>Trip</th><th>Route</th><th>Truck</th><th>Driver</th><th>Km</th><th class="text-end">Revenue</th><th class="text-end">Cost</th><th class="text-end">Profit</th><th class="text-end">Margin</th></tr></thead>
        <tbody>@forelse($rows as $r)
            <tr><td><a href="{{ route('logistics.trips.show', $r['id']) }}">{{ $r['number'] }}</a></td><td>{{ $r['route'] }}</td><td>{{ $r['truck'] }}</td><td>{{ $r['driver'] }}</td><td>{{ number_format($r['km'], 1) }}</td><td class="text-end">{{ $m($r['s']['revenue']) }}</td><td class="text-end">{{ $m($r['s']['total_cost']) }}</td><td class="text-end fw-bold {{ $r['s']['profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ $m($r['s']['profit']) }}</td><td class="text-end">{{ $r['s']['margin'] }}%</td></tr>
        @empty<tr><td colspan="9" class="text-muted text-center">No trips found for this period.</td></tr>@endforelse</tbody>
    </x-fleet.table>
</x-fleet.panel>
@endsection