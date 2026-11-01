@extends('layouts.attex')
@section('page_title', __('fleet.deliquentReport'))
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.deliquentReport')" icon="bi-exclamation-octagon">
      <div class="fleet-toolbar">
        {!! Form::open(['route' => 'reports.delinquent','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('year', __('fleet.year'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::select('year', $years, $year_select,['class'=>'form-control']) !!}</div>
          </div>

          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('month', __('fleet.month'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::selectMonth('month',$month_select,['class'=>'form-control']) !!}</div>
          </div>

          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('vehicle', __('fleet.vehicles'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            <select id="vehicle_id" name="vehicle_id" class="form-control vehicles" style="width: 250px" required>
              <option value="">@lang('fleet.selectVehicle')</option>
              @foreach($vehicles as $vehicle)
              <option value="{{ $vehicle->id }}" @if($vehicle->id==$vehicle_id) selected @endif>{{$vehicle->make_name}}-{{$vehicle->model_name}}-{{$vehicle->license_plate}}</option>
              @endforeach
            </select></div>
          </div>
          <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.generate_report')</button>
          <button type="submit" formaction="{{url('admin/print-deliquent-report')}}" class="fleet-btn fleet-btn-ghost" style="margin-right: 10px"><i class="bi bi-printer"></i> @lang('fleet.print')</button>
        {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>

@if(isset($result))
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.report')" icon="bi-table">
      <div class="table-responsive">
        <table class="fleet-table"  id="myTable">
          <thead class="table-dark">
            <tr>
              <th>@lang('fleet.day')</th>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.income')</th>
            </tr>
          </thead>
          <tbody>
          @foreach($data as $row)
            <tr>
              <td>{{$row->day}}</td>
              <td>{{date($date_format_setting,strtotime($row->date))}}</td>
              <td>{{$v[$row->vehicle_id]->make_name}}-{{$v[$row->vehicle_id]->model_name}}-{{$v[$row->vehicle_id]['license_plate']}}</td>
              <td>{{AtlasLogistics::get('currency')}} {{$row->Income2}}</td>
            </tr>
          @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>@lang('fleet.day')</th>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.income')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
    $("#vehicle_id").select2();
		$('#myTable tfoot th').each( function () {
      var title = $(this).text();
      $(this).html( '<input type="text" placeholder="'+title+'" />' );
    });
    var myTable = $('#myTable').DataTable( {
        dom: 'Bfrtip',
        buttons: [{
             extend: 'collection',
                text: 'Export',
                buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                ]}
        ],
        "language": {
                 "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
              },
              // individual column search
        "initComplete": function() {
                myTable.columns().every(function () {
                  var that = this;
                  $('input', this.footer()).on('keyup change', function () {
                      that.search(this.value).draw();
                  });
                });
              }
    });
	});
</script>
@endsection