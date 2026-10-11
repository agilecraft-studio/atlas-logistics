@php
    $money = function ($value) {
        return 'MWK ' . number_format((float) $value / 1000000, 1) . 'M';
    };
@endphp

<section class="fleet-kpi-grid">
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-teal"><i class="bi bi-truck"></i></div>
            <div>
                <div class="fleet-kpi-label">Active Trips</div>
                <div class="fleet-kpi-value">{{ $dashboard['active_trips'] }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-green"><i class="bi bi-truck-front-fill"></i></div>
            <div>
                <div class="fleet-kpi-label">Available Trucks</div>
                <div class="fleet-kpi-value">{{ $dashboard['available_trucks'] }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-amber"><i class="bi bi-wrench-adjustable"></i></div>
            <div>
                <div class="fleet-kpi-label">In Workshop</div>
                <div class="fleet-kpi-value">{{ $dashboard['trucks_in_workshop'] }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-blue"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <div class="fleet-kpi-label">Drivers on Duty</div>
                <div class="fleet-kpi-value">{{ $dashboard['drivers_on_duty'] }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-green"><i class="bi bi-currency-exchange"></i></div>
            <div>
                <div class="fleet-kpi-label">Revenue This Month</div>
                <div class="fleet-kpi-value">{{ $money($dashboard['revenue_this_month']) }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-red"><i class="bi bi-coin"></i></div>
            <div>
                <div class="fleet-kpi-label">Trip Costs</div>
                <div class="fleet-kpi-value">{{ $money($dashboard['trip_costs_this_month']) }}</div>
            </div>
        </div>
    </article>
    <article class="fleet-card fleet-kpi">
        <div class="fleet-kpi-top">
            <div class="fleet-kpi-icon bg-cyan"><i class="bi bi-bar-chart-line-fill"></i></div>
            <div>
                <div class="fleet-kpi-label">Net Profit</div>
                <div class="fleet-kpi-value">{{ $money($dashboard['net_profit_this_month']) }}</div>
            </div>
        </div>
    </article>
</section>
