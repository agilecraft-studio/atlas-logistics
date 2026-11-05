@extends(request()->boolean('drawer') ? 'layouts.master-data-drawer' : 'layouts.master-data')
@section('master-content')
<div class="row">
  <div class="col-md-12">
    {!! Form::open(['route' => 'service-item.store','method'=>'post','class' => 'form-reset']) !!}
    {!! Form::hidden('user_id',Auth::user()->id)!!}
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-tools me-2"></i>@lang('fleet.create_service_item')</h5>
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
          {!! Form::textarea('description', null,['class' => 'form-control','required','rows'=>'3']) !!}
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3">"@lang('fleet.overdue')" @lang('fleet.reminder_settings')</h6>
              @lang('fleet.time_interval')
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk1" id="chk1">
                  </div>
                </div>
                <div class="col-md-3">
                  <input type="number" name="time1" class="form-control" id="time1">
                </div>
                <div class="col-md-6">
                  <select id="interval" name="interval1" class="form-select">
                    <option value="day(s)"> @lang('fleet.days')</option>
                    <option value="week(s)"> @lang('fleet.weeks')</option>
                    <option value="month(s)"> @lang('fleet.months')</option>
                    <option value="year(s)"> @lang('fleet.years')</option>
                  </select>
                </div>
              </div>
              <div class="mt-3">@lang('fleet.meter_interval')</div>
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk2" id="chk2">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="input-group">
                    <span class="input-group-text">{{AtlasLogistics::get('dis_format')}}</span>
                    <input type="number" name="meter1" class="form-control" id="meter1">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3">"@lang('fleet.due_soon')" @lang('fleet.reminder_settings')</h6>
              @lang('fleet.show_reminder_time')
              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk3" id="chk3">
                  </div>
                </div>
                <div class="col-md-3">
                  <input type="number" name="time2" class="form-control" id="time2">
                </div>
                <div class="col-md-6">
                  <select id="interval" name="interval2" class="form-select">
                    <option value="day(s)"> @lang('fleet.days')</option>
                    <option value="week(s)"> @lang('fleet.weeks')</option>
                    <option value="month(s)"> @lang('fleet.months')</option>
                    <option value="year(s)"> @lang('fleet.years')</option>
                  </select>
                </div>
              </div>

              <div class="mt-3">@lang('fleet.show_reminder_meter')</div>

              <div class="row align-items-center mt-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="chk4" id="chk4">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="input-group">
                    <span class="input-group-text">{{AtlasLogistics::get('dis_format')}}</span>
                    <input type="number" name="meter2" class="form-control" id="meter2">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-3">
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
  $('#time1').on('change',function(){
    $("#chk1").prop('checked',true);
  });

  $('#time2').on('change',function(){
    $("#chk3").prop('checked',true);
  });

  $('#meter1').on('change',function(){
    $("#chk2").prop('checked',true);
  });

  $('#meter2').on('change',function(){
    $("#chk4").prop('checked',true);
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

