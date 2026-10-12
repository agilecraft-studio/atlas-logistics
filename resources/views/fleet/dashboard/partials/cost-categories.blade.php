@php
    $categories = $dashboard['cost_categories'];
    $total = array_sum(array_column($categories, 'amount'));
    /* Cost-bar colors: token palette only (no hardcoded hex).
       Known categories map per the skin remap table; unknown/configurable
       categories fall back to the chart series order by rank. */
    $costTokenMap = [
        'Fuel'          => 'var(--chart-4)',
        'Border Fees'   => 'var(--chart-3)',
        'Tolls'         => 'var(--chart-2)',
        'Repairs'       => 'var(--chart-5)',
        'Allowances'    => 'var(--chart-6)',
    ];
    $costCycle = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
@endphp

<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-bar-chart-fill"></i>Top Trip Cost Categories</h2>
    </div>
    @forelse($categories as $index => $category)
        <div class="fleet-cost-row">
            <span>{{ $category['name'] }}</span>
            <div class="fleet-cost-track"><div class="fleet-cost-fill" style="width:{{ $category['percent'] }}%;background:{{ $costTokenMap[$category['name']] ?? $costCycle[$index % count($costCycle)] }}"></div></div>
            <span class="cost-value">MWK {{ number_format($category['amount'] / 1000000, 1) }}M ({{ $category['percent'] }}%)</span>
        </div>
    @empty
        <div class="fleet-cost-row"><span class="text-muted">No trip cost categories to display.</span></div>
    @endforelse
</article>
