@props(['label' => '', 'name' => '', 'error' => null])
<div {{ $attributes->merge(['class' => 'fleet-field']) }}>
@if($label)<label for="f_{{ $name }}">{{ $label }}</label>@endif
{{ $slot }}
@if($error)<div class="fleet-error">{{ $error }}</div>@endif
</div>
