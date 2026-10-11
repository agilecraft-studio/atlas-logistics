@extends('layouts.attex')
@section('page_title', 'Trip Expenses')
@section('breadcrumb')<li class="breadcrumb-item active">Trip Expenses</li>@endsection
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<div class="card mb-3"><div class="card-body">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="btn-group" role="group">
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $k => $label)
                <a href="{{ route('logistics.expenses.index', ['status' => $k]) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="ms-auto text-muted">{{ $m($total) }} {{ $base }} ({{ ucfirst($status) }})</div>
    </div>
</div></div>
<div class="card"><div class="card-body">
    <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" id="expensesTable">
        <thead class="table-light"><tr><th>Date</th><th>Trip</th><th>Driver</th><th>Truck</th><th>Category</th><th class="text-end">Amount</th><th class="text-end">{{ $base }}</th><th>Paid by</th><th>Status</th><th></th></tr></thead>
        <tbody>@forelse($rows as $e)
            <tr><td>{{ $e->expense_date->format('d M Y') }}</td>
                <td><a href="{{ route('logistics.trips.show', $e->trip_id) }}">{{ optional($e->trip)->trip_number }}</a></td>
                <td>{{ optional(optional($e->trip)->driver)->name }}</td>
                <td>{{ optional(optional($e->trip)->vehicle)->license_plate }}</td>
                <td>{{ optional($e->category)->name }}</td>
                <td class="text-end">{{ $m($e->amount) }} {{ $e->currency }}</td>
                <td class="text-end">{{ $m($e->amount_base) }}</td>
                <td>{{ $e->paid_by === 'company' ? 'Company' : 'Driver cash' }}</td>
                <td><span class="badge bg-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$e->status] ?? 'secondary' }}">{{ ucfirst($e->status) }}</span></td>
                <td class="text-end text-nowrap">
                    @if($e->status === 'pending')@can('TripExpenses approve')<form method="POST" action="{{ route('logistics.expenses.approve', $e->id) }}" class="d-inline">@csrf @method('PUT')<button class="btn btn-sm btn-success">Approve</button></form>@endcan @endif
                    @if($e->status !== 'approved')@can('TripExpenses delete')<form method="POST" action="{{ route('logistics.expenses.destroy', $e->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button></form>@endcan @endif
                </td></tr>
        @empty<tr><td colspan="10" class="text-muted text-center">No expenses found.</td></tr>@endforelse</tbody>
    </table></div>
</div></div>
@endsection
@push('scripts')<script>$(document).ready(function() { if ($.fn.DataTable) { $('#expensesTable').DataTable({ order: [[0, 'desc']], pageLength: 25 }); } });</script>@endpush
