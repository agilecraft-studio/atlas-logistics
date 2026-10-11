@extends('layouts.attex')
@section('page_title', 'Trip Profitability Report')
@section('breadcrumb')<li class="breadcrumb-item active">Profitability Report</li>@endsection
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<div class="card mb-3"><div class="card-body">
    <form method="GET" action="{{ route('logistics.reports.profitability') }}" class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
        <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="both" {{ $status === 'both' ? 'selected' : '' }}>Both Completed & Closed</option><option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed Only</option><option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed Only</option></select></div>
        <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary flex-grow-1">Filter</button><a href="{{ route('logistics.reports.profitability', ['from' => $from, 'to' => $to, 'status' => $status, 'export' => 'csv']) }}" class="btn btn-success"><i class="ri-file-excel-line"></i> CSV</a></div>
    </form>
</div></div>
<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h6 class="text-muted">Total Revenue</h6><h4 class="mb-0">{{ $m($total['revenue']) }} {{ $base }}</h4></div></div></div>
    <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h6 class="text-muted">Total Cost</h6><h4 class="mb-0">{{ $m($total['cost']) }} {{ $base }}</h4></div></div></div>
    <div class="col-md-3"><div class="card {{ $total['profit'] < 0 ? 'bg-danger text-white' : 'bg-success text-white' }}"><div class="card-body text-center"><h6>Total Profit</h6><h4 class="mb-0">{{ $m($total['profit']) }} {{ $base }}</h4></div></div></div>
    <div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h6 class="text-muted">Pending Approvals</h6><h4 class="mb-0 text-warning">{{ $m($total['pending']) }} {{ $base }}</h4></div></div></div>
</div>
<div class="card"><div class="card-body">
    <h5 class="card-title mb-3">Trip Details</h5>
    <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" id="profitabilityTable">
        <thead class="table-light"><tr><th>Trip</th><th>Route</th><th>Truck</th><th>Driver</th><th>Km</th><th class="text-end">Revenue</th><th class="text-end">Cost</th><th class="text-end">Profit</th><th class="text-end">Margin</th></tr></thead>
        <tbody>@forelse($rows as $r)
            <tr><td><a href="{{ route('logistics.trips.show', $r['id']) }}">{{ $r['number'] }}</a></td><td>{{ $r['route'] }}</td><td>{{ $r['truck'] }}</td><td>{{ $r['driver'] }}</td><td>{{ number_format($r['km'], 1) }}</td><td class="text-end">{{ $m($r['s']['revenue']) }}</td><td class="text-end">{{ $m($r['s']['total_cost']) }}</td><td class="text-end fw-bold {{ $r['s']['profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ $m($r['s']['profit']) }}</td><td class="text-end">{{ $r['s']['margin'] }}%</td></tr>
        @empty<tr><td colspan="9" class="text-muted text-center">No trips found for this period.</td></tr>@endforelse</tbody>
    </table></div>
</div></div>
@endsection
@push('scripts')<script>$(document).ready(function() { if ($.fn.DataTable) { $('#profitabilityTable').DataTable({ order: [[7, 'desc']], pageLength: 25 }); } });</script>@endpush