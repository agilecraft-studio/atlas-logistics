@extends(request()->boolean('drawer') ? 'layouts.master-data-drawer' : 'layouts.master-data')
@section('master-content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0">@lang('fleet.edit_vehicle_type')</h5>
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

      {!! Form::open(['route' => ['vehicle-types.update',$vehicle_type->id],'method'=>'PATCH','files'=>true]) !!}
      {!! Form::hidden('id',$vehicle_type->id) !!}
      {!! Form::hidden('old_type',strtolower(str_replace(' ','',$vehicle_type->vehicletype))) !!}
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('vehicletype', __('fleet.vehicle_type'), ['class' => 'form-label']) !!}
          {!! Form::text('vehicletype', $vehicle_type->vehicletype,['class' => 'form-control','required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('displayname', __('fleet.displayname'), ['class' => 'form-label']) !!}
          {!! Form::text('displayname', $vehicle_type->displayname,['class' => 'form-control','required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('seats', __('fleet.seats'), ['class' => 'form-label']) !!}
          {!! Form::number('seats', $vehicle_type->seats,['class' => 'form-control','required','min'=>1]) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('icon', __('fleet.icon_img'), ['class' => 'form-label']) !!}&nbsp;
          @if($vehicle_type->icon != null)
          <a href="{{asset('/uploads/'.$vehicle_type->icon)}}" target="blank">@lang('fleet.view')</a>
          @endif
          <br>
          {!! Form::file('icon',null,['class' => 'form-control']) !!}
        </div>

        <div class="mb-3 col-md-4">
          <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" name="isenable" value="1" id="isenable" @if($vehicle_type->isenable == 1) checked @endif>
            <label class="form-check-label" for="isenable">@lang('fleet.isenable')</label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        {!! Form::submit(__('fleet.update'), ['class' => 'fleet-btn']) !!}
      </div>
      {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>
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