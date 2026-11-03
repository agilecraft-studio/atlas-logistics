@extends('layouts.attex')
@section('styles')
<style type="text/css">
.custom .nav-link.active {
    background-color: #21bc6c !important;
}
</style>
@endsection
@section('page_title', __('menu.api_settings'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('menu.api_settings')" icon="bi-sliders">
      @if (count($errors) > 0)
      <div class="alert alert-danger">
        <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
        </ul>
      </div>
      @endif

      <div>
        <ul class="nav nav-pills custom mb-3">
          <li class="nav-item"><a href="#general" data-bs-toggle="tab" class="nav-link active"> @lang('menu.general_settings') </a></li>
          <li class="nav-item"><a href="#firebase" data-bs-toggle="tab" class="nav-link"> @lang('fleet.firebase_settings') </a></li>
          <li class="nav-item"><a href="#serverkey" data-bs-toggle="tab" class="nav-link"> @lang('fleet.app_notification') </a></li>
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane active" id="general">
          {!! Form::open(['url' => 'admin/api-settings','files'=>true,'method'=>'post']) !!}

          <div class="row">
            <div class="col-md-3 mb-3">
              {!! Form::label('api',__('fleet.api'),['class'=>"form-label"]) !!}
              <div class="form-check form-switch">
                <input class="form-check-input api" type="checkbox" role="switch" id="api" name="name[api]" value="1" @if(AtlasLogistics::api('api') == "1") checked @endif>
              </div>
            </div>

            @if(AtlasLogistics::api('api') == "1")
            <div class="col-md-3 mb-3">
              {!! Form::label('anyone_register',__('fleet.anyone_register'),['class'=>"form-label"]) !!}
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="anyone_register" name="name[anyone_register]" value="1" @if(AtlasLogistics::api('anyone_register') == "1") checked @endif>
              </div>
            </div>
            @endif
          </div>

          <div class="col-md-2">
            <div class="mb-3">
              <input type="submit" class="fleet-btn api_btn" value="@lang('fleet.save')" />
            </div>
          </div>

          {!! Form::close()!!}
        </div>

        <div class="tab-pane" id="firebase">
          {!! Form::open(['url' => 'admin/firebase-settings','files'=>true,'method'=>'post']) !!}
          <div class="row">
            <div class="col-md-12 mb-3">
              {!! Form::label('db_url',__('fleet.db_url'),['class'=>"form-label"]) !!}
              <a data-bs-toggle="modal" data-bs-target="#myModal5"><i class="bi bi-info-circle fa-lg" aria-hidden="true"  style="color: #8639dd"></i></a>
              {!! Form::text('db_url',
              AtlasLogistics::api('db_url'),['class'=>"form-control",'required','placeholder'=>'for example: https://PROJECT.firebaseio.com']) !!}
            </div>
            <div class="col-md-12 mb-3" style="font-size:20px" >
              <h4>@lang('fleet.fcf') </h4>
              <h6 class="text-uppercase"><a href="{{asset('assets/samples/firebase_credentials.json')}}" download>@lang('fleet.fsf')</a></h6>
              <ul style="margin-bottom: 1rem;">
                <li>@lang('fleet.gtf')</li>
                <li>@lang('fleet.yp')</li>
                <li>@lang('fleet.ps')</li>
                <li>@lang('fleet.sa')</li>
                <li>@lang('fleet.gnpk')</li>
                <li>@lang('fleet.tgf')Fleet\Framework\Storage\Firebase\Firebase_credentials.Json @lang('fleet.rtff')</li>
              </ul>
            </div>
            <div class="col-md-6">
              <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.test')</button>
            </div>
            {!! Form::close()!!}
          </div>
        </div>

        <div class="tab-pane" id="serverkey">
          {!! Form::open(['url' => 'admin/store-key','files'=>true,'method'=>'post']) !!}
          <div class="row">
            <div class="col-md-12 mb-3">
              {!! Form::label('server_key',__('fleet.legacy_key'),['class'=>"form-label"]) !!}
              <a data-bs-toggle="modal" data-bs-target="#myModal4"><i class="bi bi-info-circle fa-lg" aria-hidden="true"  style="color: #8639dd"></i></a>
              {!! Form::text('server_key',
              AtlasLogistics::api('server_key'),['class'=>"form-control",'required']) !!}
            </div>
            <div class="col-md-6">
              <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.test')</button>
            </div>
            {!! Form::close()!!}
          </div>
        </div>
      </div>
    </x-fleet.panel>
  </div>
</div>

<!-- Modal 1-->
<div id="myModal1" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.api')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.api_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal 1-->


<!-- Modal 3-->
<div id="myModal4" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.legacy_key')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.legacy_key_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

<div id="myModal5" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.db_url')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.db_url_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
  @if(isset($_GET['tab']) && $_GET['tab']!="")
  $('.nav-pills a[href="#{{$_GET['tab']}}"]').tab('show')
  @endif
});
</script>
<script type="text/javascript">
  $(window).on('load', function() {

    @if(isset($_GET['success']) && $_GET['success'] == 1)
      new PNotify({
        title: 'Success!',
        text: 'Firebase credentials matched.',
        type: 'success',
        delay: 15000
      });
    @elseif(isset($_GET['success']) && $_GET['success'] == 0)
      new PNotify({
        title: 'Failed!',
        text: 'Firebase credentials does not matched, Try again!',
        type: 'error',
        delay: 15000
      });
    @endif

    @if(isset($_GET['key']) && $_GET['key'] == 1)
      new PNotify({
        title: 'Success!',
        text: 'Legacy server key stored successfully.',
        type: 'success',
        delay: 15000
      });
    @elseif(isset($_GET['key']) && $_GET['key'] == 0)
      new PNotify({
        title: 'Failed!',
        text: 'Legacy server key is invalid, Try again!',
        type: 'error',
        delay: 15000
      });
    @endif


  });
</script>

@endsection
