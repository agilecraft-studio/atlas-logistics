@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('fleet.add_new') }}</h5>
        <a href="{{ route('customers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>@lang('fleet.back')</a>
      </div>

      @if (count($errors) > 0)
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      {!! Form::open(['route' => 'customers.store','method'=>'post','class' => 'form-reset']) !!}
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('first_name', __('fleet.firstname'), ['class' => 'form-label']) !!}
            {!! Form::text('first_name', null,['class' => 'form-control','required']) !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('last_name', __('fleet.lastname'), ['class' => 'form-label']) !!}
            {!! Form::text('last_name', null,['class' => 'form-control','required']) !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('phone',__('fleet.phone'), ['class' => 'form-label']) !!}
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              {!! Form::number('phone', null,['class' => 'form-control','required']) !!}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('email', __('fleet.email'), ['class' => 'form-label']) !!}
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              {!! Form::email('email', null,['class' => 'form-control','required']) !!}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('address',__('fleet.address'), ['class' => 'form-label']) !!}
            {!! Form::textarea('address', null,['class' => 'form-control','rows'=>2]) !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('gender', __('fleet.gender') , ['class' => 'form-label']) !!}
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="1" checked>
                <label class="form-check-label" for="gender_male">@lang('fleet.male')</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="0">
                <label class="form-check-label" for="gender_female">@lang('fleet.female')</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        {!! Form::submit(__('fleet.add_new'), ['class' => 'fleet-btn']) !!}
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
