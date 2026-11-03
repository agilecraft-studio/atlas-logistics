<div class="d-flex gap-1">
    <a href="{{ url('admin/parts-used/'.$row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.partsUsed')"><i class="bi bi-wrench-adjustable"></i></a>
    @can('WorkOrders edit')
        <a href="{{ url('admin/work_order/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')"><i class="bi bi-pencil"></i></a>
    @endcan
    @can('WorkOrders delete')
        <form method="POST" action="{{ url('admin/work_order/'.$row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" value="{{ $row->id }}">
            <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
</div>
