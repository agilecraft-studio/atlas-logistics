@props(['title' => null, 'icon' => null])
<article {{ $attributes->merge(['class' => 'fleet-card fleet-panel']) }}>
@if($title)<div class="fleet-panel-heading"><h2>@if($icon)<i class="bi {{ $icon }}"></i> @endif{{ $title }}</h2></div>@endif
{{ $slot }}
</article>
