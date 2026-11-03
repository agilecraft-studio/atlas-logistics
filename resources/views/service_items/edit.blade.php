@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    {!! Form::open(['route' => ['service-item.update',$service->id],'method'=>'Patch']) !!}
    {!! Form::hidden('user_id',Auth::user()->id)!!}
    {!! Form::hidden('id',$service->id)!!}
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-tools me-2"></i>@lang('fleet.edit_service_item')</h5>
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

        <div class="mb-3">
          {!! Form::label('description', __('fleet.description'), ['class' => 'form-label']) !!}
          {!! Form::textarea('description', $service->description,['class' => 'form-control','required','rows'=>'3']) !!}
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3">"@lang('fleet.overdue')" @lang('fleet.reminder_settings') {{$service->overdue_unit}}</h6>
              @lang('fleet.time_interval')
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk1" @if($service->time_interval == "on") checked @endif id="chk1">
                  </div>
                </div>
                <div class="col-md-3">
                  <input type="number" name="time1" class="form-control" value="{{$service->overdue_time}}" id="time1">
                </div>
                <div class="col-md-6">
                  {!! Form::select('interval1',["day(s)"=>__('fleet.days'),
                  "week(s)"=>__('fleet.weeks'),
                  "month(s)"=>__('fleet.months'),
                  "year(s)"=>__('fleet.years')],
                  $service->overdue_unit,['class' => 'form-select']) !!}
                </div>
              </div>
              <div class="mt-3">@lang('fleet.meter_interval')</div>
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk2" @if($service->meter_interval == "on") checked @endif id="chk2">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="input-group">
                    <span class="input-group-text">{{AtlasLogistics::get('dis_format')}}</span>
                    <input type="number" name="meter1" class="form-control" value="{{$service->overdue_meter}}" id="meter1">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3">"@lang('fleet.due_soon')" @lang('fleet.reminder_settings') {{$service->duesoon_unit}}</h6>
              @lang('fleet.show_reminder_time')
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk3" @if($service->show_time == "on") checked @endif id="chk3">
                  </div>
                </div>
                <div class="col-md-3">
                  <input type="number" name="time2" class="form-control" value="{{$service->duesoon_time}}" id="time2">
                </div>
                <div class="col-md-6">
                  {!! Form::select('interval2',["day(s)"=>__('fleet.days'),
                  "week(s)"=>__('fleet.weeks'),
                  "month(s)"=>__('fleet.months'),
                  "year(s)"=>__('fleet.years')],
                  $service->duesoon_unit,['class' => 'form-select']) !!}
                </div>
              </div>
              <div class="mt-3">@lang('fleet.show_reminder_meter')</div>

              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk4" @if($service->show_meter == "on") checked @endif id="chk4">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="input-group">
                    <span class="input-group-text">{{AtlasLogistics::get('dis_format')}}</span>
                    <input type="number" name="meter2" class="form-control" value="{{$service->duesoon_meter}}" id="meter2">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-3">
          {!! Form::submit(__('fleet.update'), ['class' => 'fleet-btn']) !!}
        </div>
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
  $('#time1').on('change',function(){
    $('#chk1').prop('checked',true);
  });

  $("#time2").on('change',function(){
    $('#chk3').prop('checked',true);
  });

  $('#meter1').on('change',function(){
    $('#chk2').prop('checked',true);
  });

  $('#meter2').on('change',function(){
    $('#chk4').prop('checked',true);
  });
</script>
@endsection