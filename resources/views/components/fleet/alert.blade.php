@props(['type' => 'info'])
<div {{ $attributes->merge(['class' => 'alert alert-'.$type.' alert-dismissible fade show']) }}>{{ $slot }}</div>
