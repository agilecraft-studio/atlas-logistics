@extends('layouts.attex')

@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>@lang('fleet.driver_logs')</h5>
      </div>

      <div class="table-responsive">
        <table class="table fleet-table" id="ajax_data_table">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.driver')</th>
              <th>@lang('fleet.assigned_on')</th>
            </tr>
          </thead>
          <tbody>

          </tbody>
          <tfoot>
            <tr>
              <th>#</th>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.driver')</th>
              <th>@lang('fleet.assigned_on')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(function(){
    var table = $('#ajax_data_table').DataTable({
          "language": {
              "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
          },
         processing: true,
         serverSide: true,
         ajax: {
          url: "{{ url('admin/driver-logs-fetch') }}",
          type: 'POST',
          data:{}
         },
         columns: [
            {data: 'id',name:'id'},
            {data: 'vehicle', name: 'vehicle'},
            {data: 'driver', name: 'driver.name'},
            {name: 'date',
                        data: {
                            _: 'date.display',
                            sort: 'date.timestamp'
                        }}
        ],
        order: [[0, 'desc']]
    });
  });
</script>
@endsection
