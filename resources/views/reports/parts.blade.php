@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>@lang('menu.partsReport')</h5>
      </div>

      {!! Form::open(['route' => 'reports.parts','method'=>'post','class'=>'row align-items-end']) !!}
      <div class="col-md-4">
        <div class="mb-3">
          {!! Form::label('part', __('fleet.partName'), ['class' => 'form-label']) !!}
          <select id="part" name="part" class="form-select" required>
            <option value="">Select Part</option>
            @foreach($parts as $part)
            <option value="{{ $part->id }}">{{$part->description}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <button type="submit" class="fleet-btn fleet-btn-ghost">@lang('fleet.generate_report')</button>
        </div>
      </div>
      {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>

@if(isset($result))
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0">@lang('fleet.report')</h5>
      </div>
      <div class="table-responsive">
        <table class="table fleet-table">
          <thead class="table-dark">
            <tr>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.cost')</th>
              <th>@lang('fleet.vehicles')</th>
              <th>@lang('fleet.mileage')</th>
            </tr>
          </thead>
          <tbody>
          @foreach($parts2 as $part)
            <tr>
              <td>{{ $part->date }}</td>
              <td>{{ $part->cost }}</td>
              <td>{{ $part->vehicle?->make_name ?? '—' }} {{ $part->vehicle?->model_name ?? '—' }} {{ $part->vehicle?->license_plate ?? '—' }}</td>
              <td>{{ $part->mileage}}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endif
@endsection
