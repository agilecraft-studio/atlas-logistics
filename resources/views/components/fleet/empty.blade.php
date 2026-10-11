@props(['icon' => 'bi-inbox'])
<div {{ $attributes->merge(['class' => 'fleet-empty']) }}><i class="bi {{ $icon }}"></i><p>{{ $slot }}</p></div>
