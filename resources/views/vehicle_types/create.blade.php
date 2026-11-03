@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0">@lang('fleet.add_vehicle_type')</h5>
      </div>
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

      {!! Form::open(['route' => 'vehicle-types.store','method'=>'post','files'=>true,'class' => 'form-reset']) !!}
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('vehicletype', __('fleet.vehicle_type'), ['class' => 'form-label']) !!}
          {!! Form::text('vehicletype', null,['class' => 'form-control','required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('displayname', __('fleet.displayname'), ['class' => 'form-label']) !!}
          {!! Form::text('displayname', null,['class' => 'form-control','required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('seats', __('fleet.seats'), ['class' => 'form-label']) !!}
          {!! Form::number('seats', null,['class' => 'form-control','required','min'=>1]) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('icon', __('fleet.icon_img'), ['class' => 'form-label']) !!}
          <br>
          {!! Form::file('icon',null,['class' => 'form-control']) !!}
        </div>

        <div class="mb-3 col-md-4">
          <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" name="isenable" value="1" id="isenable">
            <label class="form-check-label" for="isenable">@lang('fleet.isenable')</label>
          </div>
        </div>
      </div>
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="mb-3 col-md-4">
            {!! Form::submit(__('fleet.save'), ['class' => 'fleet-btn']) !!}
          </div>
        </div>
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection

@section('scripts')

<script>
  $(document).ready(function() {
      $(".form-reset").on("submit", function(event) {
          $('input[type="submit"]').prop('disabled', true);
      });
    });
  </script>

@endsection


{{-- @section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
  //Flat green color scheme for iCheck
    $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
      checkboxClass: 'icheckbox_flat-green',
      radioClass   : 'iradio_flat-green'
    });
  });
</script>
@endsection --}}