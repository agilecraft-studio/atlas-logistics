<div class="d-flex gap-1">
    <a href="{{ url('admin/vehicle/event/'.$row->id) }}"
       class="fleet-btn fleet-btn-sm fleet-btn-ghost openBtn" data-id="{{ $row->id }}"
       data-bs-toggle="modal" data-bs-target="#myModal2" title="@lang('fleet.view_vehicle')">
        <i class="bi bi-eye"></i>
    </a>
    @can('Vehicles edit')
        <a href="{{ url('admin/vehicles/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @if($row->in_service == 1)
        <a href="{{ url('admin/vehicles/disable/'.$row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"
           title="@lang('fleet.disable')@lang('fleet.assigned_vehicle')"><i class="bi bi-x-lg"></i></a>
    @else
        <a href="{{ url('admin/vehicles/enable/'.$row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"
           title="@lang('fleet.enable')@lang('fleet.assigned_vehicle')"><i class="bi bi-check-lg"></i></a>
    @endif
    @can('Vehicles delete')
        <form method="POST" action="{{ url('admin/vehicles/'.$row->id) }}" class="d-inline"
              data-fleet-confirm="@lang('fleet.confirm_delete')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" value="{{ $row->id }}">
            <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endcan
</div>
