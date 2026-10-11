@props(['title' => null, 'icon' => null])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title)
        <div class="card-header py-3 px-3">
            <h5 class="mb-0">
                @if($icon)<i class="bi {{ $icon }} me-2"></i>@endif{{ $title }}
            </h5>
        </div>
    @endif
    <div class="card-body">{{ $slot }}</div>
</div>
