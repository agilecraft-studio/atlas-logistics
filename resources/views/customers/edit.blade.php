@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('fleet.edit_customer') }}</h5>
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

      {!! Form::open(['route' => ['customers.update',$data->id],'method'=>'PATCH']) !!}
      {!! Form::hidden('id',$data->id) !!}
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('first_name', __('fleet.firstname'), ['class' => 'form-label']) !!}
            {!! Form::text('first_name', $data->getMeta('first_name'),['class' => 'form-control','required']) !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('last_name', __('fleet.lastname'), ['class' => 'form-label']) !!}
            {!! Form::text('last_name', $data->getMeta('last_name'),['class' => 'form-control','required']) !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('phone',__('fleet.phone'), ['class' => 'form-label']) !!}
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              {!! Form::number('phone', $data->getMeta('mobno'),['class' => 'form-control','required']) !!}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('email', __('fleet.email'), ['class' => 'form-label']) !!}
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              {!! Form::email('email', $data->email,['class' => 'form-control','required']) !!}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('address', __('fleet.address'), ['class' => 'form-label']) !!}
            {!! Form::textarea('address', $data->getMeta('address'),['class' => 'form-control','rows'=>2,'required']) !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('gender', __('fleet.gender') , ['class' => 'form-label']) !!}
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="1" @if($data->gender == 1) checked @endif required>
                <label class="form-check-label" for="gender_male">@lang('fleet.male')</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="0" @if($data->gender == 0) checked @endif required>
                <label class="form-check-label" for="gender_female">@lang('fleet.female')</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        {!! Form::submit(__('fleet.update'), ['class' => 'fleet-btn']) !!}
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection
