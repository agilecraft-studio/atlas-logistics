@extends('layouts.attex')
@section('styles')
<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datepicker.min.css')}}">
@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-bell me-2"></i>{{ __('fleet.serviceReminders') }}</h5>
      </div>
      {!! Form::open(['route' => 'service-reminder.store','method'=>'post','class' => 'form-reset']) !!}
      <div>
        @if (count($errors) > 0)
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="row">
          <div class="mb-3 col-md-6">
            {!! Form::label('vehicle_id',__('fleet.selectVehicle'), ['class' => 'form-label']) !!}
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
              <option value="">-</option>
              @foreach($vehicles as $vehicle)
              <option value="{{$vehicle->id}}">{{$vehicle->make_name}} - {{$vehicle->model_name}} -
                {{$vehicle->year}}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3 col-md-6">
            {!! Form::label('start_date', __('fleet.start_date'), ['class' => 'form-label']) !!}
            <div class="input-group date">
              <span class="input-group-text"><span
                    class="bi bi-calendar"></span></span>
              {!! Form::text('start_date',date('Y-m-d'),['class'=>'form-control','required','id'=>'start_date']) !!}
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table fleet-table">
            <thead class="table-dark">
              <tr>
                <th>
                </th>
                <th>@lang('fleet.description')</th>
                <th>@lang('fleet.service_interval')</th>
                <th>@lang('fleet.create_reminder')</th>
              </tr>
            </thead>
            <tbody>
              @foreach($services as $service)
              <tr>
                <td>
                  <input type="checkbox" name="chk[]" value="{{$service->id}}" class="flat-red">
                </td>
                <td>
                  {{$service->description}}
                </td>
                <td>
                  {{$service->overdue_time}} {{$service->overdue_unit}}
                  @if($service->overdue_meter != null)
                  @lang('fleet.or') {{$service->overdue_meter}} {{AtlasLogistics::get('dis_format')}}
                  @endif
                </td>
                <td>
                  {{$service->duesoon_time}} {{$service->duesoon_unit}} @lang('fleet.before_due')
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="col-md-12">
          {!! Form::submit(__('fleet.save'), ['class' => 'fleet-btn']) !!}
        </div>
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#vehicle_id').select2({placeholder: "@lang('fleet.selectVehicle')"});
    //Flat green color scheme for iCheck
    // $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
    //   checkboxClass: 'icheckbox_flat-green',
    //   radioClass   : 'iradio_flat-green'
    // });

  $('#start_date').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd'
  });
  });
</script>


<script>
  $(document).ready(function() {
      $(".form-reset").on("submit", function(event) {
          $('input[type="submit"]').prop('disabled', true);
      });
    });
  </script>
@endsection
