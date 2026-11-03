@extends('layouts.attex')

@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-fuel-pump me-2"></i>{{ __('fleet.addFuel') }}</h5>
        <a href="{{ route('fuel.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>@lang('fleet.back')</a>
      </div>

      @if (count($errors) > 0)
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      {!! Form::open(['route' => 'fuel.store','method'=>'post','files'=>true,'class' => 'form-reset']) !!}
      {!! Form::hidden('user_id',Auth::user()->id)!!}
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('vehicle_id',__('fleet.selectVehicle'), ['class' => 'form-label']) !!}
            <select id="vehicle_id" name="vehicle_id" class="form-select" required>
              <option value="">-</option>
              @foreach($vehicles as $vehicle)
              <option value="{{$vehicle->id}}">{{$vehicle->make_name}} - {{$vehicle->model_name}} -
                {{$vehicle->license_plate}}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            {!! Form::label('date',__('fleet.date'), ['class' => 'form-label']) !!}
            <div class="input-group">
              <span class="input-group-text"><span class="bi bi-calendar"></span></span>
              {!! Form::text('date',date("Y-m-d"),['class'=>'form-control','required']) !!}
            </div>
          </div>

          <div class="mb-3">
            {!! Form::label('start_meter',__('fleet.start_meter'), ['class' => 'form-label']) !!}
            {!! Form::number('start_meter',null,['class'=>'form-control','required']) !!}
            <div class="form-text">@lang('fleet.meter_reading')</div>
          </div>

          <div class="mb-3">
            {!! Form::label('reference',__('fleet.reference'), ['class' => 'form-label']) !!}
            {!! Form::text('reference',null,['class'=>'form-control']) !!}
          </div>

          <div class="mb-3">
            {!! Form::label('province',__('fleet.province'), ['class' => 'form-label']) !!}
            {!! Form::text('province',null,['class'=>'form-control']) !!}
          </div>
          <div class="mb-3">
            {!! Form::label('image',__('fleet.select_image'), ['class' => 'form-label']) !!}
            {!! Form::file('image',['class'=>'form-control']) !!}
          </div>
          <div class="mb-3">
            {!! Form::label('note',__('fleet.note'), ['class' => 'form-label']) !!}
            {!! Form::text('note',null,['class'=>'form-control']) !!}
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" name="complete" id="complete" value="1">
            <label class="form-check-label" for="complete">@lang('fleet.complete_fill_up')</label>
          </div>
        </div>

        <div class="col-md-6">
          <div class="border rounded p-3 mb-3">
            <h6 class="mb-3">@lang('fleet.fuel_coming_from')</h6>
            <div class="form-check">
              <input class="form-check-input fuel_from" type="radio" name="fuel_from" id="fuel_tank" value="Fuel Tank">
              <label class="form-check-label" for="fuel_tank">@lang('fleet.fuel_tank')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input fuel_from" type="radio" name="fuel_from" id="fuel_nd" value="N/D" checked>
              <label class="form-check-label" for="fuel_nd">@lang('fleet.nd')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input fuel_from" type="radio" name="fuel_from" id="r1" value="Vendor">
              <label class="form-check-label" for="r1">@lang('fleet.vendor')</label>
            </div>
            <select id="vendor_name" name="vendor_name" class="form-select mt-2" disabled>
              <option value="">-</option>
              @foreach($vendors as $vendor)
              <option value="{{$vendor->id}}"> {{$vendor->name}} </option>
              @endforeach
            </select>
          </div>

          <div class="border rounded p-3 mb-3">
            <h6 class="mb-3">@lang('fleet.fuel')</h6>
            <div class="mb-3">
              {!! Form::label('qty',__('fleet.qty').' ('. AtlasLogistics::get('fuel_unit') .')', ['class' => 'form-label']) !!}
              {!! Form::number('qty',"0.00",['class'=>'form-control','step'=>0.01,'min'=>0.01]) !!}
            </div>
            <div class="mb-3">
              {!! Form::label('cost_per_unit',__('fleet.cost_per_unit'), ['class' => 'form-label']) !!}
              <div class="input-group">
                <span class="input-group-text">{{AtlasLogistics::get('currency')}}</span>
                {!! Form::number('cost_per_unit',"0.00",['class'=>'form-control','step'=>0.01,'min'=>0.01]) !!}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        {!! Form::submit(__('fleet.add_fuel'), ['class' => 'fleet-btn']) !!}
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $("#vehicle_id").select2({placeholder: "@lang('fleet.selectVehicle')"});
    $("#vendor_name").select2({placeholder: "@lang('fleet.select_fuel_vendor')"});

    $('#date').datepicker({
      autoclose: true,
      format: 'yyyy-mm-dd'
    });

    $('.fuel_from').on('change', function() {
      if(this.value == "Vendor"){
          $('#vendor_name').prop('disabled', false);
      } else {
          $('#vendor_name').prop('disabled', true);
      }
    });

    $(".form-reset").on("submit", function() {
      $('input[type="submit"]').prop('disabled', true);
    });
  });
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datepicker.min.css')}}">
@endsection
