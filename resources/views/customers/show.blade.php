@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>{{ __('fleet.customer') }} {{ __('fleet.details') }}</h5>
        <div class="d-flex gap-2">
          <a href="{{ route('customers.edit',$customer->id) }}" class="fleet-btn" title="@lang('fleet.edit')"><i class="bi bi-pencil me-1"></i>@lang('fleet.edit')</a>
          <a href="{{ route('customers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>@lang('fleet.back')</a>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <p><b>@lang('fleet.name')</b>: {{ $customer->name }}</p>
          <p><b>@lang('fleet.email')</b>: {{ $customer->email }}</p>
          <p><b>@lang('fleet.gender')</b>: {{ $customer->gender==1?__('fleet.male'):__('fleet.female') }}</p>
        </div>
        <div class="col-md-6">
          <p><b>@lang('fleet.phone')</b>: {{ $customer->mobno }}</p>
          <p><b>@lang('fleet.address')</b>: {{ $customer->address }}</p>
        </div>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection
