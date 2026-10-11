@extends('layouts.attex')
@section('page_title', 'Driver Advances')
@section('breadcrumb')<li class="breadcrumb-item active">Driver Advances</li>@endsection
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<div class="row mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <h5 class="card-title mb-3">Outstanding by driver</h5>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Driver</th><th class="text-end">Advances</th><th class="text-end">{{ $base }}</th></tr></thead>
            <tbody>@forelse($byDriver as $d)
                <tr><td>{{ $d['name'] }}</td><td class="text-end">{{ $d['count'] }}</td><td class="text-end">{{ $m($d['total']) }}</td></tr>
            @empty<tr><td colspan="3" class="text-muted text-center">None.</td></tr>@endforelse</tbody>
        </table></div>
    </div></div></div>
    <div class="col-md-8"><div class="card"><div class="card-body">
        <h5 class="card-title mb-3">All advances</h5>
        <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" id="advancesTable">
            <thead class="table-light"><tr><th>Issued</th><th>Trip</th><th>Driver</th><th>Purpose</th><th class="text-end">Amount</th><th class="text-end">{{ $base }}</th><th></th></tr></thead>
            <tbody>@forelse($rows as $a)
                <tr><td>{{ $a->issued_at->format('d M Y') }}</td>
                    <td><a href="{{ route('logistics.trips.show', $a->trip_id) }}">{{ optional($a->trip)->trip_number }}</a></td>
                    <td>{{ optional($a->driver)->name }}</td>
                    <td>{{ ucfirst($a->purpose) }}</td>
                    <td class="text-end">{{ $m($a->amount) }} {{ $a->currency }}</td>
                    <td class="text-end">{{ $m($a->amount_base) }}</td>
                    <td class="text-end">@can('Advances delete')<form method="POST" action="{{ route('logistics.advances.destroy', $a->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button></form>@endcan</td></tr>
            @empty<tr><td colspan="7" class="text-muted text-center">No advances found.</td></tr>@endforelse</tbody>
        </table></div>
    </div></div></div>
</div>
@endsection
@push('scripts')<script>$(document).ready(function() { if ($.fn.DataTable) { $('#advancesTable').DataTable({ order: [[0, 'desc']], pageLength: 25 }); } });</script>@endpush
