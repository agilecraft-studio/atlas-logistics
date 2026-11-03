<div class="d-flex gap-1">
@can('Users edit')
<a href="{{ route('users.edit', $row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')">
    <i class="bi bi-pencil"></i>
</a>
@endcan
@if($row->id != 1)
@can('Users delete')
<form method="POST" action="{{ route('users.destroy', $row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
    @csrf
    @method('DELETE')
    <input type="hidden" name="id" value="{{ $row->id }}">
    <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endcan
@endif
</div>
