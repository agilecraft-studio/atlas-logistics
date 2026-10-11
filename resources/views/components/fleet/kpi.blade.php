@props(['label' => '', 'value' => '', 'icon' => 'bi-truck', 'color' => 'bg-teal'])
<article {{ $attributes->merge(['class' => 'fleet-card fleet-kpi']) }}>
<div class="fleet-kpi-top"><div class="fleet-kpi-icon {{ $color }}"><i class="bi {{ $icon }}"></i></div>
<div><div class="fleet-kpi-label">{{ $label }}</div><div class="fleet-kpi-value">{{ $value }}</div></div></div>
</article>
