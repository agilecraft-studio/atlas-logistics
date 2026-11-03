<div class="d-flex gap-1">
  @can('Customer list')
    <a href="{{ route('customers.show', $row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.view')"><i class="bi bi-eye"></i></a>
  @endcan
  @can('Customer edit')
    <a href="{{ route('customers.edit', $row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')"><i class="bi bi-pencil"></i></a>
  @endcan
  <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost changepass" data-id="{{ $row->id }}" data-bs-toggle="modal" data-bs-target="#changepass" title="@lang('fleet.change_password')"><i class="bi bi-key"></i></button>
  @can('Customer delete')
    <form method="POST" action="{{ url('admin/customers/'.$row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
      @csrf
      @method('DELETE')
      <input type="hidden" name="id" value="{{ $row->id }}">
      <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
    </form>
  @endcan
</div>
