@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>{{ __('fleet.addExpenseType') }}</h5>
        <a href="{{ route('expensecategories.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>@lang('fleet.back')</a>
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

      {!! Form::open(['route' => 'expensecategories.store','method'=>'post']) !!}
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('name', __('fleet.expenseType'), ['class' => 'form-label']) !!}
          {!! Form::text('name', null,['class' => 'form-control','required']) !!}
        </div>
      </div>
      <div class="mb-3">
        {!! Form::submit(__('fleet.save'), ['class' => 'fleet-btn']) !!}
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>
@endsection
