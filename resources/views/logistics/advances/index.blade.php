@extends('layouts.fleet')
@section('fleet_title', 'Driver Advances')
@section('fleet_subtitle', 'Outstanding advances by driver')
@section('content')
@php $m = fn ($v) => number_format((float) $v, 2); @endphp
<div class="fleet-grid-bottom">
    <x-fleet.panel title="Outstanding by driver" icon="bi-wallet2">
        <x-fleet.table>
            <thead><tr><th>Driver</th><th class="text-end">Advances</th><th class="text-end">{{ $base }}</th></tr></thead>
            <tbody>@forelse($byDriver as $d)
                <tr><td>{{ $d['name'] }}</td><td class="text-end">{{ $d['count'] }}</td><td class="text-end">{{ $m($d['total']) }}</td></tr>
            @empty<tr><td colspan="3" class="text-muted text-center">None.</td></tr>@endforelse</tbody>
        </x-fleet.table>
    </x-fleet.panel>
    <x-fleet.panel title="All advances" icon="bi-table">
        <x-fleet.table>
            <thead><tr><th>Issued</th><th>Trip</th><th>Driver</th><th>Purpose</th><th class="text-end">Amount</th><th class="text-end">{{ $base }}</th><th></th></tr></thead>
            <tbody>@forelse($rows as $a)
                <tr><td>{{ $a->issued_at->format('d M Y') }}</td>
                    <td><a href="{{ route('logistics.trips.show', $a->trip_id) }}">{{ optional($a->trip)->trip_number }}</a></td>
                    <td>{{ optional($a->driver)->name }}</td>
                    <td>{{ ucfirst($a->purpose) }}</td>
                    <td class="text-end">{{ $m($a->amount) }} {{ $a->currency }}</td>
                    <td class="text-end">{{ $m($a->amount_base) }}</td>
                    <td class="text-end">@can('Advances delete')<form method="POST" action="{{ route('logistics.advances.destroy', $a->id) }}" data-fleet-confirm="Delete?">@csrf @method('DELETE')<button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button></form>@endcan</td></tr>
            @empty<tr><td colspan="7" class="text-muted text-center">No advances found.</td></tr>@endforelse</tbody>
        </x-fleet.table>
    </x-fleet.panel>
</div>
@endsection
