@extends('layouts.attex')
@section('page_title', __('fleet.work_order_report'))
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

@section('content')

<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.work_order_report')" icon="bi-clipboard-check">
      <div class="fleet-toolbar">
        {!! Form::open(['route' => 'reports.work_order','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}
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
            {!! Form::label('user', __('fleet.vehicle'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
              <option value="">@lang('fleet.selectVehicle')</option>
              @foreach($vehicle as $v)
              <option value="{{ $v->id }}" @if($v['id'] == $vehicle_id) selected @endif>{{$v->make_name}}-{{$v->model_name}}-{{$v->license_plate}}</option>
              @endforeach
            </select></div>
          </div>
          <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.generate_report')</button>
          <button type="submit" formaction="{{url('admin/print-workOrder-report')}}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer"></i> @lang('fleet.print')</button>
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
                <th>@lang('fleet.vehicle_name')</th>
                <th>@lang('fleet.created_on')</th>
                <th>@lang('fleet.required_by')</th>
                <th>@lang('fleet.vendor_name')</th>
                <th>@lang('fleet.description')</th>
                <th>@lang('fleet.work_order_price')</th>
                <th>@lang('fleet.total') @lang('fleet.parts') @lang('fleet.cost')</th>
                <th>@lang('fleet.total_cost')</th>
                <th>@lang('fleet.meter')</th>
                <th>@lang('fleet.status')</th>
            </tr>
          </thead>

          <tbody>
            {{-- @dd($data) --}}
            @foreach($data as $row)
            <tr>
              <td>{{$row->vehicle['year']}}
                {{$row->vehicle['make']}} - {{$row->vehicle['model']}}
                <br>
                <b> @lang('fleet.vin'): </b>{{$row->vehicle['vin']}}
                <br>
                <b> @lang('fleet.plate'):</b> {{$row->vehicle['license_plate']}}
              </td>
              <td>{{ date($date_format_setting,strtotime($row->created_at)) }}</td>
              <td> {{date($date_format_setting,strtotime($row->required_by))}}</td>
              <td>{{$row->vendor->name}}</td>
              <td>{{$row->description}}</td>
              <td> {{AtlasLogistics::get('currency')}} {{$row->price}}</td>
              <td> {{AtlasLogistics::get('currency')}} {{ $row->parts->sum('total') }}</td>
              <td> {{AtlasLogistics::get('currency')}} {{ $row->price + $row->parts->sum('total') }}</td>
              <td>{{$row->meter}}</td>
              <td>{{$row->status}}</td>
              
             
               {{-- <td>{{$row->note}}</td> --}}
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>@lang('fleet.vehicle_name')</th>
              <th>@lang('fleet.created_on')</th>
              <th>@lang('fleet.required_by')</th>
              <th>@lang('fleet.vendor_name')</th>
              <th>@lang('fleet.description')</th>
              <th>@lang('fleet.work_order_price')</th>
              <th>@lang('fleet.total') @lang('fleet.parts') @lang('fleet.cost')</th>
              <th>@lang('fleet.total_cost')</th>
              <th>@lang('fleet.meter')</th>
              <th>@lang('fleet.status')</th>
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
	});
</script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#myTable tfoot th').each( function () {
      var title = $(this).text();
      $(this).html( '<input type="text" placeholder="'+title+'" />' );
    });
    var myTable = $('#myTable').DataTable({
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