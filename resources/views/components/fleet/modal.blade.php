@props(['id' => 'fleetModal', 'title' => ''])
<div class="fleet-modal" id="{{ $id }}" tabindex="-1"><div class="fleet-modal-dialog"><div class="fleet-modal-content">
<div class="fleet-modal-header"><h5>{{ $title }}</h5><button type="button" class="fleet-modal-close" data-fleet-close>&times;</button></div>
<div class="fleet-modal-body">{{ $slot }}</div>
</div></div></div>
