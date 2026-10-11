@props(['type' => 'info'])
<div {{ $attributes->merge(['class' => 'fleet-alert-box fleet-alert-' . $type]) }}>{{ $slot }}</div>
