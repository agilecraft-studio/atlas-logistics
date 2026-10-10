@extends('layouts.attex')
@section("breadcrumb")
<li class="breadcrumb-item">@lang('menu.settings')</li>
<li class="breadcrumb-item active">@lang('menu.general_settings')</li>
@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">@lang('menu.general_settings')
        </h3>
      </div>
      <div class="card-body">
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
        <div class="row">
          <div class="form-group col-md-4">
            {!! Form::label('app_name',__('fleet.app_name'),['class'=>"form-label"]) !!}
            {!! Form::text('name[app_name]',
            AtlasLogistics::get('app_name'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('email',__('fleet.email'),['class'=>"form-label"]) !!}
            {!! Form::text('name[email]',
            AtlasLogistics::get('email'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('badd1',__('fleet.badd1'),['class'=>"form-label"]) !!}
            {!! Form::text('name[badd1]',
            AtlasLogistics::get('badd1'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('badd2',__('fleet.badd2'),['class'=>"form-label"]) !!}
            {!! Form::text('name[badd2]',
            AtlasLogistics::get('badd2'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('city',__('fleet.city'),['class'=>"form-label"]) !!}
            {!! Form::text('name[city]',
            AtlasLogistics::get('city'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('state',__('fleet.state'),['class'=>"form-label"]) !!}
            {!! Form::text('name[state]',
            AtlasLogistics::get('state'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('country',__('fleet.country'),['class'=>"form-label"]) !!}
            {!! Form::text('name[country]',
            AtlasLogistics::get('country'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('dis_format',__('fleet.dis_format'),['class'=>"form-label"]) !!}
            {!! Form::select('name[dis_format]', ['km' => 'km', 'miles' => 'miles'], AtlasLogistics::get("dis_format"),['class'=>"form-control",'required']) !!}
          </div>

          <input type="hidden" name="name[language]" value="English-en">

          <div class="form-group col-md-4">
            {!! Form::label('currency',__('fleet.currency'),['class'=>"form-label"]) !!}
            {!! Form::text('name[currency]',
            AtlasLogistics::get('currency'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('tax_no',__('fleet.tax_no'),['class'=>"form-label"]) !!}
            {!! Form::text('name[tax_no]',
            AtlasLogistics::get('tax_no'),['class'=>"form-control",'required']) !!}
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('tax_charge',__('fleet.tax_charge')." (%)",['class'=>"form-label"]) !!}

              <div class="input-group mb-3">
                {!! Form::number('name[tax_charge]',AtlasLogistics::get('tax_charge'),['class'=>"form-control",'required','min'=>0,'step'=>"0.01"]) !!}
                <div class="input-group-append">
                  <span class="input-group-text fa fa-percent"></span>
                </div>
              </div>
          </div>

          <div class="form-group col-md-4">
            {!! Form::label('time_interval',__('fleet.defaultTimeInterval'),['class'=>"form-label"]) !!}

              <div class="input-group mb-3">
                {!! Form::number('name[time_interval]',AtlasLogistics::get('time_interval'),['class'=>"form-control",'required','min'=>1]) !!}
                <div class="input-group-append">
                  <span class="input-group-text">day(s)</span>
                </div>
              </div>
          </div>

          <div class="form-group col-md-4">
            <label for="icon_img"> @lang('fleet.icon_img')</label>
            @if(AtlasLogistics::get('icon_img')!= null)
            <button type="button" class="btn btn-success view1 btn-xs" data-bs-toggle="modal" data-bs-target="#myModal3" id="view" title="@lang('fleet.image')" style="margin-bottom: 5px">
            @lang('fleet.view')
            </button>
            @endif
            <div class="input-group input-group-sm">
            {!! Form::file('icon_img') !!}
            </div>
          </div>
          <div class="form-group col-md-4">
            <label for="logo_img"> @lang('fleet.logo_img')</label>
            @if(AtlasLogistics::get('logo_img')!= null)
            <button type="button" class="btn btn-success view2 btn-xs" data-bs-toggle="modal" data-bs-target="#myModal3" id="view" title="@lang('fleet.image')" style="margin-bottom: 5px">
            @lang('fleet.view')
            </button>
            @endif
            <div class="input-group input-group-sm">
              {!! Form::file('logo_img') !!}
            </div>
          </div>

          <div class="form-group col-md-12">
            {!! Form::label('invoice_text',__('fleet.invoice_text'),['class'=>"form-label"]) !!}
            {!! Form::textarea('name[invoice_text]',
            AtlasLogistics::get('invoice_text'),['class'=>"form-control",'size'=>'30x3']) !!}
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="col-md-2">
          <div class="form-group">
            <input type="submit"  class="form-control btn btn-success"  value="@lang('fleet.save')" />
          </div>
        </div>
      </div>
      {!! Form::close()!!}
      </div>
    </div>
  </div>
</div>


<!--model 2 -->
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
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          @lang('fleet.close')
        </button>
      </div>
    </div>
  </div>
</div>
<!--model 2 -->
@endsection

@section('script')

<script type="text/javascript">
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
</script>
@endsection