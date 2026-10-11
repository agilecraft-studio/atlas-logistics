@php
    $statusColors = ['#16b981', '#f59e0b', '#1688e8', '#079ca4', '#ef4444'];
    $statusValues = $dashboard['trip_status_values'];
    $statusLabels = $dashboard['trip_status_labels'];
    $total = array_sum($statusValues);
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-clipboard-pulse"></i>Trip Status</h2>
    </div>
    <div class="fleet-status-wrap">
        <div id="tripStatusChart" class="fleet-donut" aria-label="Trip status chart"></div>
        <ul class="fleet-status-list">
            @forelse($statusValues as $index => $value)
                <li>
                    <span class="legend-dot" style="background:{{ $statusColors[$index] ?? '#7890a8' }}"></span>
                    <span>{{ $statusLabels[$index] ?? '—' }}</span>
                    <strong>{{ $value }}</strong>
                    <small>{{ $total > 0 ? round(($value / $total) * 100) : 0 }}%</small>
                </li>
            @empty
                <li>No trips to display.</li>
            @endforelse
        </ul>
    </div>
</article>
