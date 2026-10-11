@props(['title' => null, 'icon' => null])
<article {{ $attributes->merge(['class' => 'fleet-card']) }}>@if($title)<div class="fleet-card-title">@if($icon)<i class="bi {{ $icon }}"></i> @endif{{ $title }}</div>@endif{{ $slot }}</article>
