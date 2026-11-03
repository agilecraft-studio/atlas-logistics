@props(['title' => null, 'icon' => null])
<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-body">
        @if($title)
            <h5 class="mb-3">
                @if($icon)<i class="bi {{ $icon }} me-2"></i>@endif{{ $title }}
            </h5>
        @endif
        {{ $slot }}
    </div>
</div>
