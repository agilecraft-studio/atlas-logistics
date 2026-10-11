@props(['label' => '', 'name' => '', 'error' => null])
<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    @if($label)<label class="form-label" for="f_{{ $name }}">{{ $label }}</label>@endif
    {{ $slot }}
    @if($error)<div class="invalid-feedback">{{ $error }}</div>@endif
</div>
