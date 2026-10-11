@extends('layouts.fleet')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">@lang('fleet.incomeType')</h3>
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

        {!! Form::open(['route' => 'incomecategories.store','method'=>'post']) !!}
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('name', __('fleet.incomeType'), ['class' => 'form-label']) !!}
              {!! Form::text('name', null,['class' => 'form-control','required']) !!}
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="form-group">
          {!! Form::submit(__('fleet.save'), ['class' => 'btn btn-success']) !!}
        </div>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>
@endsection