@extends('layouts.attex')
@section('page_title', 'Compliance Dashboard')
@section('fleet_subtitle', 'Expired and upcoming document expiries')
@section('content')
<section class="fleet-kpi-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <x-fleet.kpi label="Expired" :value="$counts['expired']" icon="bi-file-earmark-x-fill" color="bg-red" />
    <x-fleet.kpi label="Due in 7 days" :value="$counts['d7']" icon="bi-alarm-fill" color="bg-amber" />
    <x-fleet.kpi label="Due in 30 days" :value="$counts['d30']" icon="bi-calendar-event" color="bg-blue" />
    <x-fleet.kpi label="Due in 90 days" :value="$counts['d90']" icon="bi-calendar3" color="bg-cyan" />
</section>

<x-fleet.panel title="Expiring documents" icon="bi-file-earmark-check-fill">
        <form method="GET" class="fleet-toolbar">
            <div class="col-auto">
                <select name="days" class="form-select" onchange="this.form.submit()">
                    @foreach([7, 14, 30, 60, 90] as $d)
                    <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>Expired + due within {{ $d }} days</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Trucks, trailers and drivers</option>
                    <option value="vehicle" {{ $type === 'vehicle' ? 'selected' : '' }}>Trucks only</option>
                    <option value="trailer" {{ $type === 'trailer' ? 'selected' : '' }}>Trailers only</option>
                    <option value="driver" {{ $type === 'driver' ? 'selected' : '' }}>Drivers only</option>
                    <option value="company" {{ $type === 'company' ? 'selected' : '' }}>Company only</option>
                </select>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                @can('Compliance add')<a href="{{ route('logistics.documents.create') }}" class="fleet-btn"><i class="bi bi-plus-lg"></i> Add document</a>@endcan
                <a href="{{ route('logistics.documents.index') }}" class="fleet-btn fleet-btn-ghost">All documents</a>
            </div>
        </form>

        <x-fleet.table>
                <thead><tr><th>Type</th><th>Belongs to</th><th>Item</th><th>Number</th><th>Expiry</th><th>Days left</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($items as $i)
                    <tr class="{{ $i['status'] === 'expired' ? 'table-danger' : '' }}">
                        <td>{{ ['vehicle' => 'Truck', 'trailer' => 'Trailer', 'driver' => 'Driver', 'company' => 'Company'][$i['owner_type']] ?? $i['owner_type'] }}</td>
                        <td>{{ $i['owner'] }}</td>
                        <td>{{ $i['item'] }} @if($i['critical'])<span class="badge bg-dark">critical</span>@endif</td>
                        <td>{{ $i['number'] ?: '—' }}</td>
                        <td data-order="{{ $i['expiry']->format('Y-m-d') }}">{{ $i['expiry']->format('d M Y') }}</td>
                        <td data-order="{{ $i['days_left'] }}">{{ $i['days_left'] < 0 ? abs($i['days_left']) . ' days ago' : $i['days_left'] . ' days' }}</td>
                        <td>@if($i['status'] === 'expired')<x-fleet.status-pill status="expired">Expired</x-fleet.status-pill>@else<x-fleet.status-pill status="due soon">Due soon</x-fleet.status-pill>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </x-fleet.table>
        @if($items->isEmpty())<x-fleet.empty>Nothing expired or due in this window.</x-fleet.empty>@endif
</x-fleet.panel>
@endsection
