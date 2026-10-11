@php
    $statusMap = config('logistics.trip_statuses');
    $statusClassMap = [
        'In Transit' => 'status-green',
        'Delivered' => 'status-blue',
        'Planned' => 'status-blue',
        'Closed' => 'status-blue',
        'Cancelled' => 'status-red',
    ];
    $progressColorMap = [
        'In Transit' => 'teal',
        'At Border' => 'blue',
        'Loading' => 'blue',
        'Cancelled' => 'red',
    ];
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-truck"></i>Live Trip Overview</h2>
        <a href="{{ route('logistics.trips.index') }}" class="fleet-view-all">View All Trips <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="fleet-table-wrap">
        <table class="fleet-table">
            <thead>
                <tr>
                    <th>Trip Ref</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th>ETA</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dashboard['recent_trips'] as $trip)
                    @php
                        $status = $trip['status'];
                        $statusClass = $statusClassMap[$status] ?? 'status-blue';
                        $progress = (int) $trip['progress'];
                        $progressColor = isset($progressColorMap[$status]) ? $progressColorMap[$status] : '';
                    @endphp
                    <tr>
                        <td class="trip-ref">{{ $trip['ref'] ?? '—' }}</td>
                        <td>{{ $trip['vehicle'] ?? '—' }}</td>
                        <td>{{ $trip['driver'] ?? '—' }}</td>
                        <td>{{ $trip['route'] ?? '—' }}</td>
                        <td><span class="status-pill {{ $statusClass }}">{{ $status }}</span></td>
                        <td>{{ $trip['eta'] ?? '—' }}</td>
                        <td><span class="progress-track"><span class="progress-fill {{ $progressColor }}" style="width:{{ $progress }}%;display:block"></span></span> {{ $progress }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No trips to display.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>
