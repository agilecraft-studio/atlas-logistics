@props(['id' => 'fleetModal', 'title' => ''])
<div class="modal" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ $title }}</h5><button type="button" class="btn" data-fleet-close aria-label="Close">&times;</button></div>
        <div class="modal-body">{{ $slot }}</div>
    </div></div>
</div>
