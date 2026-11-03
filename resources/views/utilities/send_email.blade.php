@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>{{ __('menu.email_notification') }}</h5>
      </div>

      {!! Form::open(['url' => 'admin/enable-mail','method'=>'post']) !!}
      <div class="row align-items-end">
        <div class="col-md-6 col-sm-12">
          <h6 class="mb-2">
            @lang('menu.email_notification')
            <span id="change" class="text-muted">
              @if(AtlasLogistics::email_msg('email')==1)
                (@lang('fleet.enable'))
              @else
                (@lang('fleet.disable'))
              @endif
            </span>
            <a data-bs-toggle="modal" data-bs-target="#myModal1"><i class="bi bi-info-circle ms-1 text-primary"></i></a>
          </h6>
        </div>
        <div class="col-md-3 col-sm-12">
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" name="email" value="1" id="email" @if(AtlasLogistics::email_msg('email')==1) checked @endif>
            <label class="form-check-label" for="email">@lang('fleet.enable')</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-12">
          <div class="mb-3">
            <button type="submit" class="fleet-btn">@lang('fleet.update')</button>
          </div>
        </div>
      </div>
      {!! Form::close() !!}
      <hr>
      {!! Form::open(['url' => 'admin/email-settings','method'=>'post']) !!}
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('users', __('fleet.select_user'), ['class' => 'form-label']) !!}
            <select class="form-select" required name="users[]" multiple size="10">
              @foreach($users as $user)
              <option value="{{$user->id}}" @if(in_array($user->id,$selected_users)) selected @endif>{{$user->name}}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('chk',__('fleet.selectNotification'), ['class' => 'form-label']) !!}
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="chk[]" id="chk_rn" value="1" @if(in_array(1,$options)) checked @endif>
              <label class="form-check-label" for="chk_rn">@lang('fleet.rn')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="chk[]" id="chk_in" value="2" @if(in_array(2,$options)) checked @endif>
              <label class="form-check-label" for="chk_in">@lang('fleet.in')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="chk[]" id="chk_vn" value="3" @if(in_array(3,$options)) checked @endif>
              <label class="form-check-label" for="chk_vn">@lang('fleet.vn')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="chk[]" id="chk_dn" value="4" @if(in_array(4,$options)) checked @endif>
              <label class="form-check-label" for="chk_dn">@lang('fleet.dn')</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="chk[]" id="chk_sn" value="5" @if(in_array(5,$options)) checked @endif>
              <label class="form-check-label" for="chk_sn">@lang('fleet.sn')</label>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <input type="submit" class="fleet-btn" value="@lang('fleet.save')" id="save"/>
      </div>
      {!! Form::close()!!}
    </x-fleet.panel>
  </div>
</div>

<!-- Modal 1-->
<div id="myModal1" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('menu.email_notification')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.email_notification_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal 1-->
@endsection

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $("#save").on("click",function(){
      if (($("input[name*='chk']:checked").length)<=0) {
          alert("You must select at least 1 Notification");
          return false;
      }else{
        return true;
      }
    });

    $('#email').change(function () {
      if($('#email').is(":checked")){
        $("#change").empty();
        $("#change").append(" (@lang('fleet.enable'))");

      }
      else{
        $("#change").empty();
        $("#change").append(" (@lang('fleet.disable'))");
      }
    });
  });
</script>
@endsection
