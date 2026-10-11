@props(['label' => '', 'value' => '', 'icon' => 'bi-truck', 'color' => 'bg-teal'])
<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-body d-flex align-items-center gap-2">
        <div class="fleet-kpi-icon {{ $color }}"><i class="bi {{ $icon }}"></i></div>
        <div><div class="fleet-kpi-label">{{ $label }}</div><div class="fleet-kpi-value">{{ $value }}</div></div>
    </div>
</div>
