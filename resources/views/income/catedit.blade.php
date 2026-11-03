@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>{{ __('fleet.editIncomeType') }}</h5>
        <a href="{{ route('incomecategories.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>@lang('fleet.back')</a>
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

      {!! Form::open(['route' => ['incomecategories.update',$incomecategory->id],'method'=>'PATCH']) !!}
      {!! Form::hidden('id',$incomecategory->id) !!}
      <div class="row">
        <div class="mb-3 col-md-4">
          {!! Form::label('name', __('fleet.incomeType'), ['class' => 'form-label']) !!}
          {!! Form::text('name', $incomecategory->name,['class' => 'form-control','required']) !!}
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
