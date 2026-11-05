@extends('layouts.attex')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-lite.css" rel="stylesheet">
@endsection

@section('page_title', __('menu.general_settings'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('menu.general_settings')" icon="bi-gear">
      @if (count($errors) > 0)
        <div class="alert alert-danger">
          <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
          </ul>
        </div>
      @endif

      {!! Form::open(['route' => 'settings.store','files'=>true,'method'=>'post']) !!}
      <h6 class="text-uppercase text-muted fw-semibold border-bottom pb-2 mb-3"><i class="bi bi-building me-2"></i>Company profile</h6>
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('app_name',__('fleet.app_name'),['class'=>"form-label"]) !!}
          {!! Form::text('name[app_name]', AtlasLogistics::get('app_name'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('email',__('fleet.email'),['class'=>"form-label"]) !!}
          {!! Form::text('name[email]', AtlasLogistics::get('email'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('badd1',__('fleet.badd1'),['class'=>"form-label"]) !!}
          {!! Form::text('name[badd1]', AtlasLogistics::get('badd1'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('badd2',__('fleet.badd2'),['class'=>"form-label"]) !!}
          {!! Form::text('name[badd2]', AtlasLogistics::get('badd2'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('city',__('fleet.city'),['class'=>"form-label"]) !!}
          {!! Form::text('name[city]', AtlasLogistics::get('city'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('state',__('fleet.state'),['class'=>"form-label"]) !!}
          {!! Form::text('name[state]', AtlasLogistics::get('state'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('country',__('fleet.country'),['class'=>"form-label"]) !!}
          {!! Form::text('name[country]', AtlasLogistics::get('country'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('currency',__('fleet.currency'),['class'=>"form-label"]) !!}
          {!! Form::text('name[currency]',AtlasLogistics::get('currency'),['class'=>"form-control",'required','id'=>'currency']) !!}
          <span class="text-danger curr-symbol-err">Enter valid currency symbol</span>
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('tax_no',__('fleet.tax_no'),['class'=>"form-label"]) !!}
          {!! Form::text('name[tax_no]', AtlasLogistics::get('tax_no'),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          <label for="icon_img" class="form-label">@lang('fleet.icon_img')</label>
          @if(AtlasLogistics::get('icon_img')!= null)
            <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost view1 mb-2" data-bs-toggle="modal" data-bs-target="#myModal3" id="view" title="@lang('fleet.image')">
              @lang('fleet.view')
            </button>
          @endif
          <div class="input-group input-group-sm">
            {!! Form::file('icon_img',['class'=>"form-control"]) !!}
          </div>
        </div>

        <div class="mb-3 col-md-3">
          <label for="logo_img" class="form-label">@lang('fleet.logo_img')(172px X 76px)</label>
          @if(AtlasLogistics::get('logo_img')!= null)
            <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost view2 mb-2" data-bs-toggle="modal" data-bs-target="#myModal3" id="view" title="@lang('fleet.image')">
              @lang('fleet.view')
            </button>
          @endif
          <div class="input-group input-group-sm">
            {!! Form::file('logo_img',['class'=>"form-control"]) !!}
          </div>
        </div>

        <input type="hidden" name="name[language]" value="English-en">
      </div>

      <h6 class="text-uppercase text-muted fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="bi bi-sliders me-2"></i>Localisation &amp; preferences</h6>
      <div class="row">
        <div class="mb-3 col-md-3">
          {!! Form::label('dis_format',__('fleet.dis_format'),['class'=>"form-label"]) !!}
          {!! Form::select('name[dis_format]', ['km' => 'km', 'miles' => 'miles'], AtlasLogistics::get("dis_format"),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('fuel_unit',__('fleet.fuel_unit'),['class'=>"form-label"]) !!}
          {!! Form::select('name[fuel_unit]', ['gallon' => 'gallon', 'liter' => 'liter'], AtlasLogistics::get("fuel_unit"),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('date_format',__('fleet.date_format'),['class'=>"form-label"]) !!}
          {!! Form::select('name[date_format]', ['d-m-Y' => 'dd-mm-yyyy ('.date('d-m-Y').')', 'Y-m-d' => 'yyyy-mm-dd ('.date('Y-m-d').')','m-d-Y'=>'mm-dd-yyyy ('.date('m-d-Y').')'], AtlasLogistics::get("date_format"),['class'=>"form-control",'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('time_interval',__('fleet.defaultTimeInterval'),['class'=>"form-label"]) !!}
          <div class="input-group">
            {!! Form::number('name[time_interval]',AtlasLogistics::get('time_interval'),['class'=>"form-control",'required','min'=>1]) !!}
            <span class="input-group-text">day(s)</span>
          </div>
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('vehicle_interval',__('fleet.vehicle_interval'),['class'=>"form-label"]) !!}
          {!! Form::number('name[vehicle_interval]',AtlasLogistics::get('vehicle_interval'),['class'=>"form-control",'required']) !!}
          <span id="interval_merr_vehicle" class="text-danger">Maximum vehicle interval can be 1440 minutes.</span>
          <span id="interval_lerr_vehicle" class="text-danger">Vehicle interval cannot be less than 0.</span>
        </div>

        <div class="mb-3 col-md-4">
          {!! Form::label('driver_interval',__('fleet.driver_interval'),['class'=>"form-label"]) !!}
          {!! Form::number('name[driver_interval]',AtlasLogistics::get('driver_interval'),['class'=>"form-control",'required']) !!}
          <span id="interval_merr_driver" class="text-danger">Maximum driver interval can be 1440 minutes.</span>
          <span id="interval_lerr_driver" class="text-danger">Driver interval cannot be less than 0.</span>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="bi bi-receipt me-2"></i>Tax &amp; invoicing</h6>
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('tax_charge',__('fleet.tax_charge'),['class'=>"form-label"]) !!}
          <div class="row">
            <div class="col-md-8">
              {!! Form::text('udf1', null,['class' => 'form-control','id'=>'udf1','placeholder'=>__('fleet.Enter_Tax_Name')]) !!}
            </div>
            <div class="col-md-4">
              <button type="button" class="fleet-btn add_udf"> @lang('fleet.addNew')</button>
            </div>
          </div>
        </div>

        <div class="mb-3 col-md-8">
          {!! Form::label('invoice_text',__('fleet.invoice_text'),['class'=>"form-label"]) !!}
          <a data-bs-toggle="modal" data-bs-target="#myModal5"><i class="bi bi-info-circle fa-lg" aria-hidden="true" style="color: #8639dd"></i></a>
          {!! Form::textarea('name[invoice_text]', AtlasLogistics::get('invoice_text'),['class'=>"form-control",'rows'=>3]) !!}
        </div>

        @php($udfs = json_decode(AtlasLogistics::get('tax_charge')))
        @if($udfs != null)
          @foreach($udfs as $key => $value)
            <div class="row col-md-6">
              <div class="col-md-8">
                <div class="mb-3">
                  <label class="form-label text-uppercase">{{$key}}</label>
                  <div class="input-group mb-3">
                    <input type="number" name="udf[{{$key}}]" class="form-control" required value="{{$value}}" min=0 step="0.01">
                    <span class="input-group-text bi bi-percent"></span>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3" style="margin-top: 30px">
                  <button class="fleet-btn fleet-btn-sm fleet-btn-danger" type="button" onclick="this.parentElement.parentElement.parentElement.remove();">@lang('fleet.Remove')</button>
                </div>
              </div>
            </div>
          @endforeach
        @endif
        <div class="blank col-md-12"></div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="bi bi-person-badge me-2"></i>Driver portal</h6>
      <div class="row">
        <div class="mb-3 col-md-3">
          {!! Form::label('fuel_enable_driver',__('fleet.fuel_enable_driver'), ['class' => 'form-label']) !!}
          <a data-bs-toggle="modal" data-bs-target="#myModal2"><i class="bi bi-info-circle fa-lg" aria-hidden="true" style="color: #8639dd"></i></a>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="fuel_enable_driver" value="1" id="fuel_enable_driver" @if(AtlasLogistics::get('fuel_enable_driver')==1) checked @endif>
          </div>
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('income_enable_driver',__('fleet.income_enable_driver'), ['class' => 'form-label']) !!}
          <a data-bs-toggle="modal" data-bs-target="#myModal7"><i class="bi bi-info-circle fa-lg" aria-hidden="true" style="color: #8639dd"></i></a>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="income_enable_driver" value="1" id="income_enable_driver" @if(AtlasLogistics::get('income_enable_driver')==1) checked @endif>
          </div>
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('expense_enable_driver',__('fleet.expense_enable_driver'), ['class' => 'form-label']) !!}
          <a data-bs-toggle="modal" data-bs-target="#myModal8"><i class="bi bi-info-circle fa-lg" aria-hidden="true" style="color: #8639dd"></i></a>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="expense_enable_driver" value="1" id="expense_enable_driver" @if(AtlasLogistics::get('expense_enable_driver')==1) checked @endif>
          </div>
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('driver_doc_verification',__('fleet.Driver_Doc_Verfication'), ['class' => 'form-label']) !!}
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="driver_doc_verification" value="1" id="driver_doc_verification" @if(AtlasLogistics::get('driver_doc_verification')==1) checked @endif>
          </div>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="bi bi-shield-exclamation me-2"></i>Fleet &amp; compliance alerts</h6>
      <p class="text-muted small mb-3">These thresholds drive the due-soon badges, KPI counts and filters on the truck, driver and trailer profile hubs. A document type or individual document can still override the default.</p>
      <div class="row">
        <div class="mb-3 col-md-3">
          {!! Form::label('compliance_alert_days', 'Document expiry warning (days)', ['class'=>"form-label"]) !!}
          {!! Form::number('name[compliance_alert_days]', AtlasLogistics::get('compliance_alert_days') ?: 30, ['class'=>"form-control",'min'=>1,'max'=>365,'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('driver_licence_alert_days', 'Driver licence warning (days)', ['class'=>"form-label"]) !!}
          {!! Form::number('name[driver_licence_alert_days]', AtlasLogistics::get('driver_licence_alert_days') ?: 30, ['class'=>"form-control",'min'=>1,'max'=>365,'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('driver_medical_alert_days', 'Driver medical warning (days)', ['class'=>"form-label"]) !!}
          {!! Form::number('name[driver_medical_alert_days]', AtlasLogistics::get('driver_medical_alert_days') ?: 30, ['class'=>"form-control",'min'=>1,'max'=>365,'required']) !!}
        </div>

        <div class="mb-3 col-md-3">
          {!! Form::label('trailer_utilization_days', 'Trailer utilisation window (days)', ['class'=>"form-label"]) !!}
          {!! Form::number('name[trailer_utilization_days]', AtlasLogistics::get('trailer_utilization_days') ?: 90, ['class'=>"form-control",'min'=>7,'max'=>365,'required']) !!}
        </div>
      </div>

      <div class="d-flex gap-2 mt-3">
        <input type="submit" class="fleet-btn" id="save_button" value="@lang('fleet.save')" />
        <button type="button" data-bs-toggle="modal" data-bs-target="#myModal" class="fleet-btn fleet-btn-danger">@lang('fleet.clear_database')</button>
      </div>
      {!! Form::close()!!}
    </x-fleet.panel>
  </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.delete')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.confirm_clear_database')</p>
        <p class="text-danger"><strong>@lang('fleet.note'): @lang('fleet.clear_database_note')</strong></p>
      </div>
      <div class="modal-footer">
        {!! Form::open(['url' => 'admin/clear-database','method'=>'post']) !!}
        <button class="fleet-btn fleet-btn-danger" type="submit">@lang('fleet.clear_database')</button>
        {!! Form::close() !!}
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->

<!-- Modal 2-->
<div id="myModal2" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.fuel_enable_driver')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.fuel_enable_driver_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal 2-->

<!--model 3 -->
<div id="myModal3" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <img src="" class="myimg">
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">
          @lang('fleet.close')
        </button>
      </div>
    </div>
  </div>
</div>
<!--model 3 -->

<!-- Modal 2-->
<div id="myModal5" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.invoice_text')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.invoice_text_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal 2-->

<div id="myModal6" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.add_your_lang')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.add_your_lang_info')</p>
        <p>
          <a href="https://fleetdocs.Atlas Logistics.space/explore-features/settings/general-settings" target="_blank">@lang('fleet.click_here')</a>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

<div id="myModal7" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.income_enable_driver')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.income_enable_driver_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

<div id="myModal8" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.expense_enable_driver')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.expense_enable_driver_info')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-lite.js"></script>
<script>
    $(document).ready(function() {

      $('.curr-symbol-err').hide();
      $('[id^="interval_"]').hide();
      $("input[name='name[state]'], input[name='name[country]']").on('input', function() {
        var inputValue = $(this).val();
        var alphaRegex = /^[A-Za-z]+$/;
        if (!alphaRegex.test(inputValue)) {
          $(this).val(inputValue.replace(/[^A-Za-z]/g, ''));
        }
      });

      $('input[name="name[vehicle_interval]"], input[name="name[driver_interval]"]').on('input', function() {
        var inputValue = $(this).val();
        var numericValue = inputValue.replace(/[^0-9]/g, '');
        var limitedValue = numericValue.substring(0, 4);
        $(this).val(limitedValue);

        var vehicleInterval = parseInt($('input[name="name[vehicle_interval]"]').val());
        var driverInterval = parseInt($('input[name="name[driver_interval]"]').val());

        if (vehicleInterval >= 1440) {
          $('#interval_merr_vehicle').show();
          $('input[name="name[vehicle_interval]"]').val(1440);
        }
        else if (vehicleInterval < 0) {
          $('#interval_lerr_vehicle').show();
          $('input[name="name[vehicle_interval]"]').val(0);
        }
        else {
          $('#interval_merr_vehicle').hide();
          $('#interval_lerr_vehicle').hide();
        }

        if (driverInterval >= 1440) {
           $('#interval_merr_driver').show();
           $('input[name="name[driver_interval]"]').val(1440);
        }
        else if (driverInterval < 0) {
          $('#interval_lerr_driver').show();
          $('input[name="name[driver_interval]"]').val(0);
        }
        else {
          $('#interval_merr_driver').hide();
          $('#interval_lerr_driver').hide();
        }

      });

      $('#currency').on('input', function () {
            // Specify the allowed ASCII codes for currency symbols
            var allowedAsciiCodes = [36, 8364, 163, 165, 8377, 8355, 8363];

            // Get the input value
            var inputValue = $(this).val();

            // Check the ASCII code of each character in the input
            for (var i = 0; i < inputValue.length; i++) {
                var char = inputValue.charCodeAt(i);

                // Check if the character's ASCII code is not in the allowed list
                if (!allowedAsciiCodes.includes(char)) {
                    // If it's not allowed, clear the input
                    $(this).val('');
                    $('.curr-symbol-err').show();
                    return;
                }
            }
        });
    });
</script>

<script type="text/javascript">
  @if(Session::get('msg'))
    new PNotify({
        title: 'Success!',
        text: '{{ Session::get('msg') }}',
        type: 'success'
      });
  @endif

  $('.view1').click(function(){
    $('#myModal3 .modal-body .myimg').attr( "src","{{ asset('assets/images/'. AtlasLogistics::get('icon_img') ) }}");
    $('#myModal3 .modal-body .myimg').removeAttr( "height");
    $('#myModal3 .modal-body .myimg').removeAttr( "width");
  });

  $('.view2').click(function(){
    $('#myModal3 .modal-body .myimg').attr( "src","{{ asset('assets/images/'. AtlasLogistics::get('logo_img') ) }}");
    $('#myModal3 .modal-body .myimg').attr( "height","140px");
    $('#myModal3 .modal-body .myimg').attr( "width","300px");
  });

  $(".add_udf").click(function () {
    var taxMessage = @json(__('fleet.Enter_Tax_Name'));
    var field = $('#udf1').val();
    if(field == "" || field == null){
      alert(taxMessage);
    }
    else{
      $(".blank").append('<div class="row col-md-12"><div class="col-md-4"> <div class="mb-3"> <label class="form-label">'+ field.toUpperCase() +'</label> <div class="input-group mb-3"><input type="number" name="udf['+ field +']" class="form-control" placeholder="Enter '+ field +'" required min=0 step="0.01"> <span class="input-group-text bi bi-percent"></span> </div></div></div><div class="col-md-4"> <div class="mb-3" style="margin-top: 30px"><button class="fleet-btn fleet-btn-sm fleet-btn-danger" type="button" onclick="this.parentElement.parentElement.parentElement.remove();">Remove</button> </div></div></div>');
      $('#udf1').val("");
    }
  });
</script>
@endsection
