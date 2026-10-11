@props(['status' => ''])
@php $s = strtolower(trim((string) $status)); @endphp
<span {{ $attributes->merge(['class' => 'fleet-pill fleet-pill-' . preg_replace('/[^a-z0-9]+/', '-', $s)]) }}>{{ $slot }}</span>
