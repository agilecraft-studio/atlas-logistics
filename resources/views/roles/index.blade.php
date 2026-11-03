@extends('layouts.attex')
@section('styles')
<style type="text/css">
  .checkbox, #chk_all{
    width: 20px;
    height: 20px;
  }
</style>
@endsection
@section('page_title', __('fleet.users') . ' & ' . __('fleet.roles'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('fleet.users') }} &amp; {{ __('fleet.roles') }}</h5>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('users.index') }}" class="fleet-btn fleet-btn-ghost">@lang('fleet.users')</a>
          <a href="{{ route('roles.index') }}" class="fleet-btn">@lang('fleet.roles')</a>
          <a href="{{ route('roles.create') }}" class="fleet-btn" title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i> @lang('fleet.addNew')</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="fleet-table" id="data_table">
          <thead class="table-dark">
            <tr>
              <th>@lang('fleet.role')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $row)
              <tr>
                <td>{{ $row->name }}</td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('roles.edit', $row->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')">
                      <i class="bi bi-pencil"></i>
                    </a>
                    @if($row->id != 1)
                      <form method="POST" action="{{ route('roles.destroy', $row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $row->id }}">
                        <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection
