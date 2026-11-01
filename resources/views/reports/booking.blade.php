@extends('layouts.attex')
@section('page_title', __('fleet.booking_report'))

@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')




@section('content')

<div class="row">

  <div class="col-md-12">

    <x-fleet.panel :title="__('fleet.booking_report')" icon="bi-journal-text">
      <div class="fleet-toolbar">
        {!! Form::open(['route' => 'reports.booking','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}

          <div class="mb-0" style="margin-right: 5px">

            {!! Form::label('year', __('fleet.year1'), ['class' => 'form-label']) !!}

            <div class="mb-0" style="margin-left:5px;">

            {!! Form::select('year', $years, $year_select,['class'=>'form-control']) !!}</div>

          </div>

          <div class="mb-0" style="margin-right: 5px">

            {!! Form::label('month', __('fleet.month'), ['class' => 'form-label']) !!}

            <div class="mb-0" style="margin-left:5px;">

            {!! Form::selectMonth('month',$month_select,['class'=>'form-control']) !!}</div>

          </div>

          <div class="mb-0" style="margin-right: 5px">

            {!! Form::label('vehicle', __('fleet.vehicles'), ['class' => 'form-label']) !!}

            <div class="mb-0" style="margin-left:5px;">

            <select id="vehicle_id" name="vehicle_id" class="form-control vehicles" style="width: 150px">

              <option value="">@lang('fleet.selectVehicle')</option>

              @foreach($vehicles as $vehicle)

              <option value="{{ $vehicle->id }}" @if($vehicle_select == $vehicle->id) selected @endif>{{$vehicle->make_name}}-{{$vehicle->model_name}}-{{$vehicle->license_plate}}</option>

              @endforeach

            </select></div>

          </div>

          <div class="mb-0" style="margin-right: 5px">

            {!! Form::label('customer_id', __('fleet.selectCustomer'), ['class' => 'form-label']) !!}

            <div class="mb-0" style="margin-left:5px;">

            <select id="customer_id" name="customer_id" class="form-control vehicles" style="width: 150px">

              <option value="">@lang('fleet.selectCustomer')</option>

              @foreach($customers as $customer)

              <option value="{{ $customer->id }}" @if($customer_select == $customer->id) selected @endif>{{$customer->name}}</option>

              @endforeach

            </select></div>

          </div>



          <button type="submit" class="fleet-btn" style="margin-right: 1px">@lang('fleet.generate_report')</button>

          <button type="submit" formaction="{{url('admin/print-booking-report')}}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer"></i> @lang('fleet.print')</button>

        {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>



<div class="row">

  <div class="col-md-12">

    <x-fleet.panel :title="__('fleet.booking_count') . ' : ' . $bookings->count()" icon="bi-table">
      <div class="table-responsive">
        <table class="fleet-table" id="myTable">

          <thead class="table-dark">

            <tr>

              <th>@lang('fleet.customer')</th>

              <th>@lang('fleet.vehicle')</th>

              <th>@lang('fleet.pickup_addr')</th>

              <th>@lang('fleet.dropoff_addr')</th>

              <th>@lang('fleet.from_date')</th>

              <th>@lang('fleet.to_date')</th>

              <th>@lang('fleet.passengers')</th>

              <th>@lang('fleet.status')</th>

            </tr>

          </thead>

          <tbody>

            @foreach($bookings as $row)

            <tr>

              <td>{{$row->customer?->name ?? "—"}}</td>

              <td>

              @if($row->vehicle_id != null)

              {{$row->vehicle?->make_name ?? "—"}} - {{$row->vehicle?->model_name ?? "—"}} - {{$row->vehicle?->license_plate ?? "—"}}

              @endif

              </td>

              <td style="width:10% !important">{!! str_replace(",", ",<br>", $row->pickup_addr) !!}</td>

              <td style="width:10% !important">{!! str_replace(",", ",<br>", $row->dest_addr) !!}</td>

              <td>{{date($date_format_setting.' g:i A',strtotime($row->pickup))}}</td>

              <td>{{ $row->dropoff ? date($date_format_setting.' g:i A', strtotime($row->dropoff)) : 'N/A' }}
              </td>

              <td>{{$row->travellers}}</td>

              <td>

                {{$row->ride_status??'Pending'}}

              </td>

            </tr>

            @endforeach

          </tbody>

          <tfoot>

            <tr>

              <th>@lang('fleet.customer')</th>

              <th>@lang('fleet.vehicle')</th>

              <th>@lang('fleet.pickup_addr')</th>

              <th>@lang('fleet.dropoff_addr')</th>

              <th>@lang('fleet.from_date')</th>

              <th>@lang('fleet.to_date')</th>

              <th>@lang('fleet.passengers')</th>

              <th>@lang('fleet.status')</th>

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

    $('#customer_id').select2();

    $('#vehicle_id').select2();

    $('#myTable tfoot th').each( function () {

      var title = $(this).text();

      $(this).html( '<input type="text" placeholder="'+title+'" />' );

    });

    var myTable = $('#myTable').DataTable( {

        dom: 'Bfrtip',

        buttons: [{

             extend: 'collection',

                text: '@lang('fleet.Export')',

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