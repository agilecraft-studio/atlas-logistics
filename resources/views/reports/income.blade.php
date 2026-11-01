@extends('layouts.attex')
@section('page_title', __('fleet.income') . ' ' . __('fleet.report'))
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.income') . ' ' . __('fleet.report')" icon="bi-cash-coin">
      <div class="fleet-toolbar">
        {!! Form::open(['url' => 'admin/reports/income','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('year', __('fleet.year1'), ['class' => 'form-label']) !!}
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
            <select id="vehicle_id" name="vehicle_id" class="form-control vehicles" style="width: 250px">
              <option value="">@lang('fleet.selectVehicle')</option>
              @foreach($vehicles as $vehicle)
              <option value="{{ $vehicle->id }}" @if($vehicle->id==$vehicle_id) selected @endif>{{$vehicle->make_name}}-{{$vehicle->model_name}}-{{$vehicle->license_plate}}</option>
              @endforeach
            </select></div>
          </div>
          <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.generate_report')</button>
          <button type="submit" formaction="{{url('admin/print-income')}}" class="fleet-btn fleet-btn-ghost" style="margin-right: 10px"><i class="bi bi-printer"></i> @lang('fleet.print')</button>
        {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.report')" icon="bi-table">
      <div class="table-responsive">
        <table class="fleet-table"  id="myTable">
            <thead class="table-dark">
              <tr>
                <th>@lang('fleet.make')</th>
                <th>@lang('fleet.model')</th>
                <th>@lang('fleet.licensePlate')</th>
                <th>@lang('fleet.incomeType')</th>
                <th>@lang('fleet.date')</th>
                <th>@lang('fleet.amount')</th>
                <th>@lang('fleet.mileage') ({{AtlasLogistics::get('dis_format')}})</th>
              </tr>
          </thead>

          <tbody>
            @foreach($income as $row)
            <tr>
              <td>
              @if($row->vehicle_id != null)
              {{$row->vehicle?->make_name ?? "—"}}
              @endif
              </td>
              <td>
              @if($row->vehicle_id != null)
              {{$row->vehicle?->model_name ?? "—"}}
              @endif
              </td>
              <td>
              @if($row->vehicle_id != null)
              {{$row->vehicle?->license_plate ?? "—"}}
              @endif
              </td>
              <td>{{$row->category->name}}</td>
              <td>{{date($date_format_setting,strtotime($row->date))}}</td>
              <td>{{AtlasLogistics::get('currency')}} {{$row->amount}}</td>
              <td>{{$row->mileage}}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>@lang('fleet.make')</th>
              <th>@lang('fleet.model')</th>
              <th>@lang('fleet.licensePlate')</th>
              <th>@lang('fleet.incomeType')</th>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.amount')</th>
              <th>@lang('fleet.mileage') ({{AtlasLogistics::get('dis_format')}})</th>
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
  $(document).ready(function() {
    // var export="@lang('fleet.Export')";
    $("#vehicle_id").select2();
    $('#myTable tfoot th').each( function () {
      var title = $(this).text();
      $(this).html( '<input type="text" placeholder="'+title+'" />' );
    });
  	var myTable =	$('#myTable').DataTable({
        dom: 'Bfrtip',
        buttons: [{
             extend: 'collection',
                text: "@lang('fleet.Export')",
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