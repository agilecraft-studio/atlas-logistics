@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-key me-2"></i>{{ __('fleet.change_password') }}: <strong>{{ Auth::user()->name }}</strong></h5>
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

      {!! Form::open(["url"=>"admin/changepassword/".Auth::user()->id]) !!}
      <input type="hidden" name="id" value="{{ Auth::user()->id }}">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            {!! Form::label('password',__('fleet.password'),['class'=>"form-label"]) !!}
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <input type="submit" class="fleet-btn" value="@lang('fleet.change_password')" />
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection
