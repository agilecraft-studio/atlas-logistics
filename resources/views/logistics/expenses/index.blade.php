@extends('layouts.fleet')
@section('fleet_title', 'Trip Expenses')
@section('fleet_subtitle', $m($total) . ' ' . $base . ' (' . ucfirst($status) . ')')
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<x-fleet.panel title="Trip Expenses" icon="bi-cash-stack">
    <div class="fleet-toolbar">
        <div class="d-flex flex-wrap gap-2">
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $k => $label)
                <a href="{{ route('logistics.expenses.index', ['status' => $k]) }}" class="fleet-btn fleet-btn-sm {{ $status === $k ? '' : 'fleet-btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="text-muted">{{ $m($total) }} {{ $base }} ({{ ucfirst($status) }})</div>
    </div>
    <x-fleet.table>
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
                <td><x-fleet.status-pill :status="$e->status">{{ ucfirst($e->status) }}</x-fleet.status-pill></td>
                <td class="text-end text-nowrap">
                    @if($e->status === 'pending')@can('TripExpenses approve')<form method="POST" action="{{ route('logistics.expenses.approve', $e->id) }}" class="d-inline">@csrf @method('PUT')<button class="fleet-btn fleet-btn-sm">Approve</button></form>@endcan @endif
                    @if($e->status !== 'approved')@can('TripExpenses delete')<form method="POST" action="{{ route('logistics.expenses.destroy', $e->id) }}" class="d-inline" data-fleet-confirm="Delete?">@csrf @method('DELETE')<button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button></form>@endcan @endif
                </td></tr>
        @empty<tr><td colspan="10" class="text-muted text-center">No expenses found.</td></tr>@endforelse</tbody>
    </x-fleet.table>
</x-fleet.panel>
@endsection
