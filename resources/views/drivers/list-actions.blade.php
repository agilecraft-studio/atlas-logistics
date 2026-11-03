<div class="d-flex gap-1">
    <a href="{{ route('drivers.show', $row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.view')">
        <i class="bi bi-eye"></i>
    </a>
    <a href="#" class="fleet-btn fleet-btn-sm fleet-btn-ghost changepass" data-id="{{ $row->id }}"
       data-bs-toggle="modal" data-bs-target="#changepass" title="@lang('fleet.change_password')">
        <i class="bi bi-key"></i>
    </a>
    @can('Drivers edit')
        <a href="{{ url('admin/drivers/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @if(AtlasLogistics::get('driver_doc_verification') == 1)
        <a href="{{ url('admin/drivers/verify-driver/'.$row->id) }}" target="_blank"
           class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Verify Driver Check Document">
            <i class="bi bi-shield-check"></i>
        </a>
    @endif
    @if($row->getMeta('is_active'))
        <a href="{{ url('admin/drivers/disable/'.$row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"
           title="@lang('fleet.disable_driver')"><i class="bi bi-x-lg"></i></a>
    @else
        <a href="{{ url('admin/drivers/enable/'.$row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"
           title="@lang('fleet.enable_driver')"><i class="bi bi-check-lg"></i></a>
    @endif
    @can('Drivers delete')
        <form method="POST" action="{{ url('admin/drivers/'.$row->id) }}" class="d-inline"
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
