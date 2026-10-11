@php
    $categories = $dashboard['cost_categories'];
    $total = array_sum(array_column($categories, 'amount'));
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-bar-chart-fill"></i>Top Trip Cost Categories</h2>
        <form method="get" action="{{ route('logistics.dashboard') }}" class="fleet-cost-form">
            <select name="period" class="fleet-period" aria-label="Cost period">
                <option value="month" @selected(isset($currentPeriod) && $currentPeriod === 'month')>This month</option>
                <option value="week" @selected(isset($currentPeriod) && $currentPeriod === 'week')>This week</option>
                <option value="year" @selected(isset($currentPeriod) && $currentPeriod === 'year')>This year</option>
            </select>
        </form>
    </div>
    @forelse($categories as $category)
        <div class="fleet-cost-row">
            <span>{{ $category['name'] }}</span>
            <div class="fleet-cost-track"><div class="fleet-cost-fill" style="width:{{ $category['percent'] }}%;background:{{ $category['color'] }}"></div></div>
            <span class="cost-value">MWK {{ number_format($category['amount'] / 1000000, 1) }}M ({{ $category['percent'] }}%)</span>
        </div>
    @empty
        <div class="fleet-cost-row"><span class="text-muted">No trip cost categories to display.</span></div>
    @endforelse
</article>
