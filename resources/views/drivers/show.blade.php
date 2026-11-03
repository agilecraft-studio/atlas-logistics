@extends('layouts.attex')
@section('content')
<div class="row">
    <div class="col-md-12">
      <x-fleet.panel>
        <div class="fleet-toolbar">
          <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>{{ __('fleet.driver') }} {{ __('fleet.details') }}</h5>
          <div class="d-flex gap-2">
            <a href="{{ route('drivers.edit',$driver->id) }}" class="fleet-btn fleet-btn-ghost" title="@lang('fleet.edit_driver')"><i class="bi bi-pencil-square"></i> @lang('fleet.edit')</a>
          </div>
        </div>

            <div class="row">
                <div class="col-md-6">
                    <p><b>@lang('fleet.name')</b>: {{ $driver->name }}</p>
                    <p><b>@lang('fleet.email')</b>: {{ $driver->email }}</p>
                </div>
                <div class="col-md-6">
                    <p><b>@lang('fleet.phone')</b>: {{ $driver->phone }}</p>
                    <p><b>@lang('fleet.start_date')</b>: {{ $driver->start_date }}</p>
                </div>
            </div>
      </x-fleet.panel>
    </div>
</div>
@endsection
