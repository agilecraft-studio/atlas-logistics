@extends('layouts.attex')
@section('styles')
<style type="text/css">
  .ck-editor__editable {
      min-height: 200px;
  }
</style>
@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>{{ __('menu.email_content') }}</h5>
      </div>

      <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a href="#insurance" data-bs-toggle="tab" class="nav-link active"> @lang('fleet.i_notify') </a></li>
        <li class="nav-item"><a href="#vehicle-licence" data-bs-toggle="tab" class="nav-link"> @lang('fleet.v_lic') </a></li>
        <li class="nav-item"><a href="#driver-licence" data-bs-toggle="tab" class="nav-link"> @lang('fleet.d_lic') </a></li>
        <li class="nav-item"><a href="#registration" data-bs-toggle="tab" class="nav-link"> @lang('fleet.v_reg') </a></li>
        <li class="nav-item"><a href="#reminder" data-bs-toggle="tab" class="nav-link"> @lang('fleet.serviceReminders') </a></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="insurance">
          @if (count($errors) > 0)
          <div class="alert alert-danger">
            <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
            </ul>
          </div>
          @endif
          {!! Form::open(['url' => 'admin/set-content/insurance','method'=>'post']) !!}
          <div class="mb-3">
            {!! Form::label('insurance', __('fleet.ins_content'), ['class' => 'form-label']) !!}
            <textarea name="insurance" id="ins">{{AtlasLogistics::email_msg('insurance')}}</textarea>
          </div>
          <div class="mb-3">
            <input type="submit" class="fleet-btn" value="@lang('fleet.set')" />
          </div>
          {!! Form::close()!!}
        </div>

        <div class="tab-pane fade" id="vehicle-licence">
          {!! Form::open(['url' => 'admin/set-content/vehicle-licence','method'=>'post']) !!}
          <div class="mb-3">
            {!! Form::label('vehicle_licence', __('fleet.vehicle_lic'), ['class' => 'form-label']) !!}
            <textarea name="vehicle_licence" id="vl">{{AtlasLogistics::email_msg('vehicle_licence')}}</textarea>
          </div>
          <div class="mb-3">
            <input type="submit" class="fleet-btn" value="@lang('fleet.set')" />
          </div>
          {!! Form::close()!!}
        </div>

        <div class="tab-pane fade" id="driver-licence">
          {!! Form::open(['url' => 'admin/set-content/driver-licence','method'=>'post']) !!}
          <div class="mb-3">
            {!! Form::label('driving_licence', __('fleet.driver_lic'), ['class' => 'form-label']) !!}
            <textarea name="driving_licence" id="dl">{{AtlasLogistics::email_msg('driving_licence')}}</textarea>
          </div>
          <div class="mb-3">
            <input type="submit" class="fleet-btn" value="@lang('fleet.set')" />
          </div>
          {!! Form::close()!!}
        </div>

        <div class="tab-pane fade" id="registration">
          {!! Form::open(['url' => 'admin/set-content/registration','method'=>'post']) !!}
          <div class="mb-3">
            {!! Form::label('registration', __('fleet.reg_content'), ['class' => 'form-label']) !!}
            <textarea name="registration" id="reg">{{AtlasLogistics::email_msg('registration')}}</textarea>
          </div>
          <div class="mb-3">
            <input type="submit" class="fleet-btn" value="@lang('fleet.set')" />
          </div>
          {!! Form::close()!!}
        </div>

        <div class="tab-pane fade" id="reminder">
          {!! Form::open(['url' => 'admin/set-content/reminder','method'=>'post']) !!}
          <div class="mb-3">
            {!! Form::label('service_reminder', __('fleet.service_reminder_content'), ['class' => 'form-label']) !!}
            <textarea name="service_reminder" id="sr">{{AtlasLogistics::email_msg('service_reminder')}}</textarea>
          </div>
          {!! Form::hidden('sr','1') !!}
          <div class="mb-3">
            <input type="submit" class="fleet-btn" value="@lang('fleet.set')" />
          </div>
          {!! Form::close()!!}
        </div>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection

@section('scripts')
<script>
    ClassicEditor.create( document.querySelector( '#ins' ) );
    ClassicEditor.create( document.querySelector( '#vl' ) );
    ClassicEditor.create( document.querySelector( '#dl' ) );
    ClassicEditor.create( document.querySelector( '#reg' ) );
    ClassicEditor.create( document.querySelector( '#sr' ) );
</script>
<script type="text/javascript">
$(document).ready(function() {
  @if(isset($_GET['tab']) && $_GET['tab']!="")
  $('.nav-pills a[href="#{{$_GET['tab']}}"]').tab('show')
  @endif
});
</script>
@endsection
