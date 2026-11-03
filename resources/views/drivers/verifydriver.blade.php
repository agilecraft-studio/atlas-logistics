@extends('layouts.attex')

@section('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Verify Driver Check</h5>
        <div class="d-flex gap-2">
          <a href="{{ route('drivers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left"></i> @lang('fleet.back')</a>
        </div>
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

      <form method="post" action="{{ route('update_verify_driver') }}">
        @csrf
        <input type="hidden" name="d_id" value="{{ $driver->id }}">

        <div class="row align-items-end">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label" for="status">Status</label>
              <select name="status" id="status" class="form-select" required>
                <option value=''>Select Status</option>
                <option value="1" @if($driver->is_verified == "1") selected @endif>Verified</option>
                <option value="2" @if($driver->is_verified == "2") selected @endif>Rejected</option>
                <option value="0" @if($driver->is_verified == "0") selected @endif>Not Verified</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <input type="submit" value="Verify Driver Check" class="fleet-btn">
            </div>
          </div>
        </div>
      </form>
    </x-fleet.panel>
  </div>
</div>
@endsection
