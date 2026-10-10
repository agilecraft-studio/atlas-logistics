@extends('layouts.attex')
@section('extra_css')
<style type="text/css">
.height{
  height: 200px;
}
</style>
@endsection
@section('page_title')
<span> @lang('fleet.yourAddress')
  <small class="text-muted">{{Auth::user()->name}}</small>
</span>
@endsection
@section("breadcrumb")

<li class="breadcrumb-item active">@lang('fleet.addresses')</li>
@endsection
@section('content')

<div class="row">
  @foreach($bookings as $booking)
    <div class="col-md-4">
      <div class="card">
        <div class="card-body height">
          {!! nl2br(e($booking->address)) !!}
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection