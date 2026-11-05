@extends(request()->boolean('drawer') ? 'layouts.master-data-drawer' : 'layouts.master-data')
@section('master-content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0">@lang('fleet.create_mechanic')</h5>
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

        {!! Form::open(['route' => 'mechanic.store','method'=>'post','class' => 'form-reset']) !!}
        {!! Form::hidden('user_id',Auth::user()->id)!!}
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('name',__('fleet.name'), ['class' => 'form-label']) !!}
              {!! Form::text('name',null,['class'=>'form-control','required']) !!}
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('email',__('fleet.email'), ['class' => 'form-label']) !!}
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                {!! Form::email('email',null,['class'=>'form-control','required']) !!}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('contact_number',__('fleet.contact_number'), ['class' => 'form-label']) !!}
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                {!! Form::number('contact_number',null,['class'=>'form-control','required','id'=>'phone']) !!}
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('category',__('fleet.category'), ['class' => 'form-label']) !!}

              {!! Form::text('category',null,['class'=>'form-control','required']) !!}
            </div>
          </div>
        </div>
      </div>
      {{--
      <hr>
      <div class="row">
        <div class="mb-3 col-md-6 ms-3">
          {!! Form::label('udf1',__('fleet.add_udf'), ['class' => 'col-xs-5 control-label']) !!}
          <div class="row">
            <div class="col-md-8">
              {!! Form::text('udf1', null,['class' => 'form-control']) !!}
            </div>
            <div class="col-md-4">
              <button type="button" class="fleet-btn fleet-btn-ghost add_udf"> @lang('fleet.add')</button>
            </div>
          </div>
        </div>
      </div>
      <div class="blank"></div> --}}
      <div class="row">
        <div class="col-md-12 m-3">
          {!! Form::submit(__('fleet.add_mechanic'), ['class' => 'fleet-btn']) !!}
        </div>
      </div>
    </x-fleet.panel>
  </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
  $(".add_udf").click(function () {
    // alert($('#udf').val());
    var field = $('#udf1').val();
    if(field == "" || field == null){
      alert('Enter field name');
    }
    else{
      $(".blank").append('<div class="row"><div class="col-md-8">  <div class="mb-3"> <label class="form-label">'+ field.toUpperCase() +'</label> <input type="text" name="udf['+ field +']" class="form-control" placeholder="Enter '+ field +'" required></div></div><div class="col-md-4"> <div class="mb-3" style="margin-top: 30px"><button class="fleet-btn fleet-btn-danger" type="button" onclick="this.parentElement.parentElement.parentElement.remove();">Remove</button> </div></div></div>');
      $('#udf1').val("");
    }
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