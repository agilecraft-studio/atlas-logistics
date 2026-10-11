@props(['icon' => 'bi-inbox'])
<div {{ $attributes->merge(['class' => 'alert alert-secondary']) }}><i class="bi {{ $icon }}" style="font-size:22px;"></i><p class="mb-0 ms-2">{{ $slot }}</p></div>
