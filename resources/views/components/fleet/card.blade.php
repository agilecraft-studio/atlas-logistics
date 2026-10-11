@props(['title' => null, 'icon' => null])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title)
        <div class="card-header">
            <h3 class="card-title">@if($icon)<i class="bi {{ $icon }}"></i> @endif{{ $title }}</h3>
        </div>
    @endif
    <div class="card-body">{{ $slot }}</div>
</div>
