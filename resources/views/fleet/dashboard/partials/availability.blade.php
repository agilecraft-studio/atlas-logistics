@php
    $total = $dashboard['available_trucks'] + $dashboard['trucks_on_trip'] + $dashboard['trucks_in_workshop'];
    $availablePct = $total > 0 ? round($dashboard['available_trucks'] / $total * 100) : 0;
    $ontripPct = $total > 0 ? round($dashboard['trucks_on_trip'] / $total * 100) : 0;
    $workshopPct = $total > 0 ? round($dashboard['trucks_in_workshop'] / $total * 100) : 0;
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-truck"></i>Fleet Availability</h2>
    </div>
    <div class="fleet-availability">
        <div class="fleet-availability-item">
            <span class="fleet-availability-icon" style="background:#dff7e9;color:#11965f"><i class="bi bi-truck-front-fill"></i></span>
            <div><small>Available</small><strong>{{ $dashboard['available_trucks'] }}</strong><em>{{ $availablePct }}%</em></div>
        </div>
        <div class="fleet-availability-item">
            <span class="fleet-availability-icon" style="background:#e1f1ff;color:#1688e8"><i class="bi bi-truck"></i></span>
            <div><small>On Trip</small><strong>{{ $dashboard['trucks_on_trip'] }}</strong><em>{{ $ontripPct }}%</em></div>
        </div>
        <div class="fleet-availability-item">
            <span class="fleet-availability-icon" style="background:#ffe6e8;color:#e34d5d"><i class="bi bi-wrench-adjustable"></i></span>
            <div><small>In Workshop</small><strong>{{ $dashboard['trucks_in_workshop'] }}</strong><em>{{ $workshopPct }}%</em></div>
        </div>
    </div>
</article>
