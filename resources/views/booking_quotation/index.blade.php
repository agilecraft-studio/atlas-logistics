@extends('layouts.attex')
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')
@section('page_title', trans('fleet.manageBookingQuotations'))
@section('styles')
<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datepicker.min.css')}}">
<style type="text/css">
  .checkbox,
  #chk_all {
    width: 20px;
    height: 20px;
  }
</style>
@endsection
@section('content')
<x-fleet.panel :title="trans('fleet.manageBookingQuotations')" icon="bi-file-earmark-text">
    <div class="fleet-toolbar">
        <div class="text-muted">&nbsp;</div>
        <div class="d-flex gap-2">
            @can('BookingQuotations add')<a href="{{route('booking-quotation.create')}}" class="fleet-btn" title="@lang('fleet.add_quote')"><i class="bi bi-plus-lg"></i> @lang('fleet.add_quote')</a>@endcan
        </div>
    </div>
    <x-fleet.table>
        <thead>
          <tr>
            <th>
              <input type="checkbox" id="chk_all">
            </th>
            <th>@lang('fleet.customer')</th>
            <th>@lang('fleet.vehicle')</th>
            <th>@lang('fleet.pickup_addr')</th>
            <th>@lang('fleet.dropoff_addr')</th>
            <th>@lang('fleet.pickup')</th>
            <th>@lang('fleet.dropoff')</th>
            <th>@lang('fleet.passengers')</th>
            <th>@lang('fleet.total') @lang('fleet.amount')</th>
            <th>@lang('fleet.approve')/@lang('fleet.reject') </th>
            <th>@lang('fleet.action')</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr>
            <th>
              @can('BookingQuotations delete')<button class="fleet-btn fleet-btn-danger" id="bulk_delete" data-bs-toggle="modal"
                data-bs-target="#bulkModal" disabled title="@lang('fleet.delete')"><i
                  class="bi bi-trash"></i></button>@endcan
            </th>
            <th>@lang('fleet.customer')</th>
            <th>@lang('fleet.vehicle')</th>
            <th>@lang('fleet.pickup_addr')</th>
            <th>@lang('fleet.dropoff_addr')</th>
            <th>@lang('fleet.pickup_date')</th>
            <th>@lang('fleet.dropoff_date')</th>
            <th>@lang('fleet.passengers')</th>
            <th>@lang('fleet.total') @lang('fleet.amount')</th>
            <th>@lang('fleet.approve')/@lang('fleet.reject')</th>
            <th>@lang('fleet.action')</th>
          </tr>
        </tfoot>
      </x-fleet.table>
</x-fleet.panel>

<!-- Modal -->
<div id="bulkModal" class="modal fade" tabindex="-1" aria-labelledby="bulkModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="bulkModalLabel">@lang('fleet.delete')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/delete-quotes','method'=>'POST','id'=>'form_delete']) !!}
        <div id="bulk_hidden"></div>
        <p>@lang('fleet.confirm_bulk_delete')</p>
      </div>
      <div class="modal-footer">
        <button id="bulk_action" class="fleet-btn fleet-btn-danger" type="submit" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">@lang('fleet.delete')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.confirm_delete')</p>
      </div>
      <div class="modal-footer">
        <button id="del_btn" class="fleet-btn fleet-btn-danger" type="button" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

{{-- reject modal --}}
<div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="rejectModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="rejectModalLabel">@lang('fleet.reject') @lang('fleet.bookingQuote')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.confirm_reject')</p>
      </div>
      <div class="modal-footer">
        <button id="del_btn2" class="fleet-btn fleet-btn-danger" type="button" data-submit="">@lang('fleet.reject')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  @if(Session::get('msg'))
    new PNotify({
        title: 'Success!',
        text: '{{ Session::get('msg') }}',
        type: 'success'
      });
  @endif
</script>
<script>
var dataTableUrl='{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}';
var bookingQuotationFetch='{{ url("admin/booking-quotation-fetch") }}';
var deleteError="@lang('fleet.delete_error')";
</script>
<script src="{{asset('assets/js/booking_quotation/index.js')}}"></script>
@endsection