@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>@lang('fleet.vehicle_inspection')</h5>
        <a href="{{ url('admin/vehicle-inspection-create') }}" class="fleet-btn" title="@lang('fleet.add_vehicle_inspection')"><i class="bi bi-plus-lg"></i> @lang('fleet.add_vehicle_inspection')</a>
      </div>

      <div class="table-responsive">
        <table class="table fleet-table" id="data_table">
          <thead class="table-dark">
            <tr>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.review_by')</th>
              <th>@lang('fleet.reg_no')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
          @foreach($reviews as $r)
            <tr>
              <td>{{$r->vehicle?->make_name ?? '—'}} - {{$r->vehicle?->model_name ?? '—'}} - {{$r->vehicle?->types?->displayname ?? '—'}}</td>
              <td>{{$r->user?->name ?? '—'}}</td>
              <td>{{$r->reg_no}}</td>
              <td>
                <a href="{{url('admin/view-vehicle-inspection/'.$r->id)}}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.view')"><i class="bi bi-eye"></i></a>
              </td>
            </tr>
          @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>@lang('fleet.vehicle')</th>
              <th>@lang('fleet.review_by')</th>
              <th>@lang('fleet.reg_no')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection
