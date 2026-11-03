<div class="d-flex gap-1">
    @can('VehicleGroup edit')
        <a href="{{ url('admin/vehicle_group/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')"><i class="bi bi-pencil"></i></a>
    @endcan
    @if($row->id != '1')
        @can('VehicleGroup delete')
            <form method="POST" action="{{ url('admin/vehicle_group/'.$row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $row->id }}">
                <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
            </form>
        @endcan
    @endif
</div>
