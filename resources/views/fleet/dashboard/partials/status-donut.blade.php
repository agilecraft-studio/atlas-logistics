@php
    /* Trip-status donut colors: token-driven, aligned with
       config('logistics.trip_statuses') labels (configurable reference data).
       Legend dots and fleet-charts.js receive the same list. */
    $statusTokenMap = [
        'Planned'    => 'var(--atlas-navy-600)',
        'In Transit' => 'var(--atlas-info)',
        'Delivered'  => 'var(--atlas-success)',
        'Closed'     => 'var(--chart-6)',
        'Cancelled'  => 'var(--atlas-danger)',
    ];
    $statusCycle = ['var(--atlas-info)', 'var(--atlas-amber)', 'var(--atlas-navy-600)', 'var(--atlas-success)', 'var(--atlas-danger)'];
    $statusValues = $dashboard['trip_status_values'];
    $statusLabels = $dashboard['trip_status_labels'];
    $statusColors = [];
    foreach ($statusLabels as $i => $label) {
        $statusColors[] = $statusTokenMap[$label] ?? $statusCycle[$i % count($statusCycle)];
    }
    $total = array_sum($statusValues);
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-clipboard-pulse"></i>Trip Status</h2>
    </div>
    <div class="fleet-status-wrap">
        <div id="tripStatusChart" class="fleet-donut" data-chart-colors="{{ implode(',', $statusColors) }}" aria-label="Trip status chart"></div>
        <ul class="fleet-status-list">
            @forelse($statusValues as $index => $value)
                <li>
                    <span class="legend-dot" style="background:{{ $statusColors[$index] ?? 'var(--chart-6)' }}"></span>
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
